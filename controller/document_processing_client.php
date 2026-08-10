<?php
/**
 * document_processing_client.php
 * HTTP client for the Document Processing Service (CDR/SDR pipeline).
 */

class DocumentProcessingClient
{
    private string $baseUrl;
    private string $apiKey;
    private int $pollInterval;
    private int $maxPollSeconds;

    public function __construct(array $apiConfig)
    {
        $this->baseUrl = rtrim($apiConfig['base_url'] ?? 'http://127.0.0.1:8088', '/');
        $this->apiKey = $apiConfig['api_key'] ?? '';
        $this->pollInterval = (int)($apiConfig['poll_interval_seconds'] ?? 2);
        $this->maxPollSeconds = (int)($apiConfig['max_poll_seconds'] ?? 1800);
    }

    public function submitDocument(string $module, string $filePath, string $fileName, int $batchSize, ?string $operator = null): array
    {
        if (!is_readable($filePath)) {
            throw new RuntimeException('Uploaded file is not readable.');
        }

        $mime = mime_content_type($filePath) ?: 'application/octet-stream';
        $fields = [
            'module' => $module,
            'file' => new CURLFile($filePath, $mime, $fileName),
        ];
        if ($operator !== null && $operator !== '' && strtolower($operator) !== 'all') {
            $fields['operator'] = $operator;
        }

        if ($operator !== null) {
            $fields['operator'] = $operator;
        }

        $url = $this->baseUrl . '/api/v1/documents?batch_size=' . max(1, $batchSize);
        $timeout = $module === 'sdr' ? 86400 : 3600;
        $response = $this->request('POST', $url, $fields, $timeout);

        if (!isset($response['job_id'])) {
            throw new RuntimeException($response['detail'] ?? $response['message'] ?? 'Document service did not return a job ID.');
        }

        return $response;
    }

    public function getJobStatus(int $jobId): array
    {
        $url = $this->baseUrl . '/api/v1/documents/' . $jobId;
        return $this->request('GET', $url);
    }

    public function waitForJob(int $jobId): array
    {
        $deadline = time() + $this->maxPollSeconds;
        $last = [];

        while (time() < $deadline) {
            $last = $this->getJobStatus($jobId);
            $status = strtolower((string)($last['status'] ?? ''));
            if (in_array($status, ['completed', 'failed', 'validated', 'pending_verification'], true)) {
                return $last;
            }
            sleep($this->pollInterval);
        }

        $last['timed_out'] = true;
        return $last;
    }

    private function request(string $method, string $url, ?array $postFields = null, int $timeout = 300): array
    {
        $ch = curl_init($url);
        $headers = ['Accept: application/json'];
        if ($this->apiKey !== '') {
            $headers[] = 'X-API-Key: ' . $this->apiKey;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        if ($postFields !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        }

        $body = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        curl_close($ch);

        if ($body === false) {
            // "Couldn't connect to server" on its own sends people hunting through
            // the upload code, when the cause is simply that the import service is
            // not running. Say so, and say how to start it.
            $offline = in_array($curlErrno, [CURLE_COULDNT_CONNECT,
                                             CURLE_OPERATION_TIMEDOUT,
                                             CURLE_COULDNT_RESOLVE_HOST], true);
            if ($offline) {
                throw new RuntimeException(
                    'The document processing service is not reachable at ' . $this->baseUrl . '. '
                    . 'It runs separately from the web app and must be started before uploading: '
                    . 'run scripts/run_cdr_import_service.ps1 and leave that window open, '
                    . 'then retry the upload. (' . ($curlError ?: 'connection failed') . ')'
                );
            }
            throw new RuntimeException('Document service request failed: ' . ($curlError ?: 'unknown error'));
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Invalid response from document service (HTTP ' . $httpCode . ').');
        }

        if ($httpCode >= 400) {
            $detail = $decoded['detail'] ?? $decoded['message'] ?? ('HTTP ' . $httpCode);
            if (is_array($detail)) {
                $detail = json_encode($detail);
            }
            throw new RuntimeException((string)$detail);
        }

        return $decoded;
    }
}
