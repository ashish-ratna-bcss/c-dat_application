
(function (global) {
    'use strict';

    const STORAGE_KEY = 'cdat_sdr_resumable_uploads';
    const DEFAULT_CHUNK_SIZE = 16 * 1024 * 1024;
    const MAX_CHUNK_RETRIES = 8;
    const BASE_RETRY_MS = 1500;

    function sleep(ms) {
        return new Promise((resolve) => setTimeout(resolve, ms));
    }

    function fileKey(file) {
        return [file.name, file.size, file.lastModified].join(':');
    }

    function loadSavedSessions() {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
        } catch (e) {
            return {};
        }
    }

    function saveSession(key, data) {
        const all = loadSavedSessions();
        all[key] = Object.assign({}, data, { updatedAt: Date.now() });
        localStorage.setItem(STORAGE_KEY, JSON.stringify(all));
    }

    function clearSession(key) {
        const all = loadSavedSessions();
        delete all[key];
        localStorage.setItem(STORAGE_KEY, JSON.stringify(all));
    }

    function formatBytes(bytes) {
        if (bytes >= 1024 * 1024 * 1024) {
            return (bytes / (1024 * 1024 * 1024)).toFixed(2) + ' GB';
        }
        if (bytes >= 1024 * 1024) {
            return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
        }
        return (bytes / 1024).toFixed(2) + ' KB';
    }

    function formatDuration(seconds) {
        if (!isFinite(seconds) || seconds < 0) return '—';
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = Math.floor(seconds % 60);
        if (h > 0) return h + 'h ' + m + 'm';
        if (m > 0) return m + 'm ' + s + 's';
        return s + 's';
    }

    class SdrResumableUploader {
        constructor(apiConfig) {
            this.baseUrl = (apiConfig.public_base_url || '/document-api').replace(/\/$/, '');
            this.apiKey = apiConfig.api_key || '';
            this.batchSize = apiConfig.sdr_batch_size || 10000;
            this.chunkSize = apiConfig.sdr_chunk_size || DEFAULT_CHUNK_SIZE;
            this.cancelled = false;
            this.paused = false;
        }

        headers(json) {
            const h = json ? { 'Content-Type': 'application/json' } : {};
            if (this.apiKey) {
                h['X-API-Key'] = this.apiKey;
            }
            return h;
        }

        async request(method, path, options) {
            options = options || {};
            const url = this.baseUrl + path;
            const resp = await fetch(url, {
                method: method,
                headers: Object.assign({}, this.headers(options.json), options.headers || {}),
                body: options.body,
            });
            let data = null;
            const text = await resp.text();
            if (text) {
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    data = { detail: text };
                }
            }
            if (!resp.ok) {
                const detail = (data && (data.detail || data.message)) || ('HTTP ' + resp.status);
                const err = new Error(typeof detail === 'string' ? detail : JSON.stringify(detail));
                err.status = resp.status;
                err.data = data;
                throw err;
            }
            return data;
        }

        async initSession(file) {
            const key = fileKey(file);
            const payload = {
                filename: file.name,
                file_size: file.size,
                file_key: key,
                chunk_size: this.chunkSize,
            };
            const session = await this.request('POST', '/api/v1/uploads/sdr/init', {
                json: true,
                body: JSON.stringify(payload),
            });
            saveSession(key, {
                uploadId: session.upload_id,
                offset: session.offset,
                filename: file.name,
                fileSize: file.size,
            });
            return session;
        }

        async getStatus(uploadId) {
            return this.request('GET', '/api/v1/uploads/sdr/' + encodeURIComponent(uploadId));
        }

        async uploadChunk(uploadId, offset, chunk) {
            return this.request('PUT', '/api/v1/uploads/sdr/' + encodeURIComponent(uploadId) + '/chunk', {
                headers: {
                    'Content-Type': 'application/octet-stream',
                    'X-Upload-Offset': String(offset),
                },
                body: chunk,
            });
        }

        async complete(uploadId) {
            return this.request(
                'POST',
                '/api/v1/uploads/sdr/' + encodeURIComponent(uploadId) + '/complete?batch_size=' + this.batchSize,
                { json: true }
            );
        }

        async cancel(uploadId, fileKeyStr) {
            try {
                await this.request('DELETE', '/api/v1/uploads/sdr/' + encodeURIComponent(uploadId));
            } catch (e) {
                
            }
            if (fileKeyStr) {
                clearSession(fileKeyStr);
            }
        }

        async uploadChunkWithRetry(uploadId, offset, chunk, fileKeyStr, onStatus) {
            let attempt = 0;
            while (attempt < MAX_CHUNK_RETRIES) {
                if (this.cancelled) {
                    throw new Error('Upload cancelled.');
                }
                while (this.paused) {
                    await sleep(500);
                }
                try {
                    return await this.uploadChunk(uploadId, offset, chunk);
                } catch (err) {
                    attempt++;
                    if (this.cancelled) {
                        throw new Error('Upload cancelled.');
                    }
                    if (err.status === 409) {
                        onStatus('Offset mismatch — re-syncing with server…');
                        const status = await this.getStatus(uploadId);
                        saveSession(fileKeyStr, {
                            uploadId: status.upload_id,
                            offset: status.offset,
                            filename: status.filename,
                            fileSize: status.file_size,
                        });
                        if (status.offset !== offset) {
                            throw new Error('RESYNC:' + status.offset);
                        }
                    }
                    if (attempt >= MAX_CHUNK_RETRIES) {
                        throw err;
                    }
                    const wait = BASE_RETRY_MS * Math.pow(2, attempt - 1);
                    onStatus('Chunk failed (' + (err.message || 'network error') + '). Retry ' + attempt + '/' + MAX_CHUNK_RETRIES + ' in ' + Math.round(wait / 1000) + 's…');
                    await sleep(wait);
                }
            }
        }

        async uploadFile(file, callbacks) {
            callbacks = callbacks || {};
            const onProgress = callbacks.onProgress || function () {};
            const onStatus = callbacks.onStatus || function () {};

            this.cancelled = false;
            this.paused = false;
            const key = fileKey(file);

            onStatus('Initializing resumable upload for ' + file.name + ' (' + formatBytes(file.size) + ')…');
            let session = await this.initSession(file);
            if (typeof global !== 'undefined' && global.activeSdrUploadMeta) {
                global.activeSdrUploadMeta.uploadId = session.upload_id;
            }

            if (session.resumed && session.offset > 0) {
                onStatus('Resuming from ' + formatBytes(session.offset) + ' (' + session.progress_percent + '%)');
            }

            let offset = session.offset || 0;
            const uploadId = session.upload_id;
            const chunkSize = session.chunk_size || this.chunkSize;
            const total = file.size;
            const startedAt = Date.now();
            let lastProgressAt = startedAt;
            let lastOffset = offset;

            saveSession(key, { uploadId: uploadId, offset: offset, filename: file.name, fileSize: file.size });

            while (offset < total) {
                if (this.cancelled) {
                    throw new Error('Upload cancelled.');
                }

                const end = Math.min(offset + chunkSize, total);
                let chunk = file.slice(offset, end);

                try {
                    const result = await this.uploadChunkWithRetry(uploadId, offset, chunk, key, onStatus);
                    offset = result.bytes_received;
                    saveSession(key, { uploadId: uploadId, offset: offset, filename: file.name, fileSize: file.size });
                } catch (err) {
                    if (String(err.message || '').indexOf('RESYNC:') === 0) {
                        offset = parseInt(err.message.split(':')[1], 10) || offset;
                        onStatus('Resuming from byte ' + offset + '…');
                        continue;
                    }
                    onStatus('Upload interrupted at ' + formatBytes(offset) + '. You can retry the same file to resume.');
                    throw err;
                }

                const now = Date.now();
                const elapsed = (now - startedAt) / 1000;
                const instantElapsed = (now - lastProgressAt) / 1000;
                const bytesSinceLast = offset - lastOffset;
                const speed = instantElapsed > 0 ? bytesSinceLast / instantElapsed : 0;
                const remaining = total - offset;
                const eta = speed > 0 ? remaining / speed : Infinity;

                onProgress({
                    filename: file.name,
                    offset: offset,
                    total: total,
                    percent: Math.min(100, (offset / total) * 100),
                    speedBps: speed,
                    etaSeconds: eta,
                    elapsedSeconds: elapsed,
                });

                lastProgressAt = now;
                lastOffset = offset;
            }

            onStatus('Upload complete. Queuing SDR restore job…');
            const completed = await this.complete(uploadId);
            clearSession(key);
            onStatus('SDR job #' + completed.job_id + ' queued.');
            return completed;
        }

        pause() {
            this.paused = true;
        }

        resume() {
            this.paused = false;
        }

        abort(uploadId, fileKeyStr) {
            this.cancelled = true;
            this.paused = false;
            if (uploadId) {
                return this.cancel(uploadId, fileKeyStr);
            }
            return Promise.resolve();
        }

        listPendingLocal() {
            return loadSavedSessions();
        }
    }

    global.SdrResumableUploader = SdrResumableUploader;
    global.sdrUploadHelpers = { fileKey: fileKey, formatBytes: formatBytes, formatDuration: formatDuration, loadSavedSessions: loadSavedSessions };
})(window);
