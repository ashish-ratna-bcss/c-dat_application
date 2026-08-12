/* Sidebar navigation behaviour.
 *
 * Parent entries are <button>s, so expanding one cannot navigate anywhere --
 * that was the complaint with the old Spry menu, whose parents pointed at
 * home.php and threw you back to the dashboard.
 *
 * Open groups are remembered across pages so the menu does not collapse every
 * time you run a search.
 */
(function () {
    'use strict';

    var KEY = 'cdat.nav.open';

    // One group open at a time. With 12 groups and 42 links, letting them all
    // stay open pushed the lower half of the menu below the fold and the
    // sidebar became a long scroll.
    function readOpen() {
        try { return sessionStorage.getItem(KEY) || ''; }
        catch (e) { return ''; }
    }

    function writeOpen(name) {
        try {
            if (name) { sessionStorage.setItem(KEY, name); }
            else { sessionStorage.removeItem(KEY); }
        } catch (e) { /* private mode: not worth failing over */ }
    }

    function setOpen(g, openIt) {
        g.classList.toggle('is-open', openIt);
        var b = g.querySelector('.nav-parent');
        if (b) { b.setAttribute('aria-expanded', openIt ? 'true' : 'false'); }
    }

    function labelOf(group) {
        var b = group.querySelector('.nav-parent span');
        return b ? b.textContent.trim() : '';
    }

    var groups = Array.prototype.slice.call(document.querySelectorAll('.nav-group'));

    // Restore the previously open group -- unless the server already opened the
    // one holding the current page, which always wins: closing it would hide
    // the entry for the page you are looking at.
    var serverOpen = groups.filter(function (g) { return g.classList.contains('is-open'); })[0];
    if (serverOpen) {
        groups.forEach(function (g) { if (g !== serverOpen) { setOpen(g, false); } });
        writeOpen(labelOf(serverOpen));
    } else {
        var remembered = readOpen();
        groups.forEach(function (g) { setOpen(g, remembered !== '' && labelOf(g) === remembered); });
    }

    groups.forEach(function (g) {
        var btn = g.querySelector('.nav-parent');
        if (!btn) { return; }
        btn.addEventListener('click', function () {
            var nowOpen = !g.classList.contains('is-open');
            // Opening one closes the rest; clicking the open one closes it.
            groups.forEach(function (other) { setOpen(other, other === g && nowOpen); });
            writeOpen(nowOpen ? labelOf(g) : '');
        });
    });

    // Small screens: slide the sidebar in over the content.
    var burger = document.querySelector('.burger');
    var scrim = document.querySelector('.scrim');

    function setNav(openIt) {
        document.body.classList.toggle('nav-open', openIt);
        if (scrim) { scrim.hidden = !openIt; }
        if (burger) { burger.setAttribute('aria-expanded', openIt ? 'true' : 'false'); }
    }

    if (burger) {
        burger.addEventListener('click', function () {
            setNav(!document.body.classList.contains('nav-open'));
        });
    }
    if (scrim) { scrim.addEventListener('click', function () { setNav(false); }); }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { setNav(false); }
    });

    /* ---- menu search ------------------------------------------------
     * Filters the sidebar as you type. A group survives if its own label
     * matches (all its children stay visible) or if any child matches (only
     * the matching children show). Groups are force-opened while filtering,
     * otherwise the hits would be inside collapsed sections.
     */
    var search = document.getElementById('navSearch');
    var clearBtn = document.querySelector('.nav-search-clear');
    var empty = document.getElementById('navSearchHint');

    if (search) {
        // Remember each label so highlighting can be undone cleanly.
        var targets = [];
        Array.prototype.forEach.call(
            document.querySelectorAll('.nav-item span, .nav-parent span, .nav-sub a'),
            function (el) { targets.push({ el: el, text: el.textContent }); }
        );

        function paint(el, text, from, len) {
            if (from < 0) { el.textContent = text; return; }
            el.textContent = '';
            el.appendChild(document.createTextNode(text.slice(0, from)));
            var mk = document.createElement('mark');
            mk.className = 'hit';
            mk.textContent = text.slice(from, from + len);
            el.appendChild(mk);
            el.appendChild(document.createTextNode(text.slice(from + len)));
        }

        function reset() {
            targets.forEach(function (t) { t.el.textContent = t.text; });
            Array.prototype.forEach.call(document.querySelectorAll('.nav li'),
                function (li) { li.hidden = false; });
            groups.forEach(function (g) { g.classList.remove('is-filtered'); });
            if (empty) { empty.hidden = true; }
        }

        function idx(el, q) {
            var t = targets.filter(function (x) { return x.el === el; })[0];
            return t ? t.text.toLowerCase().indexOf(q) : -1;
        }

        function apply() {
            var q = search.value.trim().toLowerCase();
            if (clearBtn) { clearBtn.hidden = q === ''; }
            reset();
            if (q === '') { return; }

            var shown = 0;

            // plain top-level links
            Array.prototype.forEach.call(document.querySelectorAll('.nav > li'), function (li) {
                if (li.classList.contains('nav-group')) { return; }
                var a = li.querySelector('.nav-item span');
                if (!a) { return; }
                var at = idx(a, q);
                li.hidden = at < 0;
                if (at >= 0) { shown++; paint(a, a.textContent, at, q.length); }
            });

            groups.forEach(function (g) {
                var pspan = g.querySelector('.nav-parent span');
                var pAt = pspan ? idx(pspan, q) : -1;
                var kids = Array.prototype.slice.call(g.querySelectorAll('.nav-sub li'));
                var any = false;

                kids.forEach(function (li) {
                    var a = li.querySelector('a');
                    var at = a ? idx(a, q) : -1;
                    // a matching parent keeps all of its children visible
                    var keep = pAt >= 0 || at >= 0;
                    li.hidden = !keep;
                    if (at >= 0) { paint(a, a.textContent, at, q.length); }
                    if (keep) { any = true; }
                });

                var keepGroup = pAt >= 0 || any;
                g.hidden = !keepGroup;
                g.classList.toggle('is-filtered', keepGroup);
                if (pAt >= 0 && pspan) { paint(pspan, pspan.textContent, pAt, q.length); }
                if (keepGroup) { shown++; }
            });

            if (empty) { empty.hidden = shown !== 0; }
        }

        search.addEventListener('input', apply);
        search.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { search.value = ''; apply(); search.blur(); }
            if (e.key === 'Enter') {
                var first = document.querySelector('.nav li:not([hidden]) .nav-sub li:not([hidden]) a, .nav > li:not([hidden]) > .nav-item');
                if (first) { window.location.href = first.getAttribute('href'); }
            }
        });
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                search.value = ''; apply(); search.focus();
            });
        }
        // "/" focuses the menu search, unless the user is typing in a field.
        document.addEventListener('keydown', function (e) {
            var t = e.target || {};
            var typing = /^(INPUT|TEXTAREA|SELECT)$/.test(t.tagName || '') || t.isContentEditable;
            if (e.key === '/' && !typing) { e.preventDefault(); search.focus(); }
        });
    }

    /* ---- quick links picker ------------------------------------------
     * Tick pages, they become tiles on the dashboard. A native
     * <select multiple> was the obvious fit, but with 42 options it shows six
     * rows at a time and needs ctrl-click to add a second one -- and one
     * mis-click wipes the whole selection. Checkboxes with a search box do the
     * same job without that trap, and the chips above the list show the order
     * the tiles will appear in.
     */
    var qlModal = document.getElementById('qlModal');
    if (qlModal) {
        var qlList   = document.getElementById('qlList');
        var qlPicked = document.getElementById('qlPicked');
        var qlCount  = document.getElementById('qlCount');
        var qlSearch = document.getElementById('qlSearch');
        var qlMsg    = document.getElementById('qlMsg');
        var qlSave   = document.getElementById('qlSave');
        var qlNoHit  = document.getElementById('qlNoHit');
        var boxes    = Array.prototype.slice.call(qlList.querySelectorAll('input[type=checkbox]'));
        var MAX      = 12;
        var order    = [];      // urls, in the order the tiles will appear
        var lastFocus = null;

        boxes.forEach(function (b) { if (b.checked) { order.push(b.value); } });

        function boxFor(url) {
            return boxes.filter(function (b) { return b.value === url; })[0];
        }

        function render() {
            qlPicked.textContent = '';
            order.forEach(function (url, i) {
                var b = boxFor(url);
                if (!b) { return; }
                var chip = document.createElement('span');
                chip.className = 'ql-chip';
                chip.appendChild(document.createTextNode(b.getAttribute('data-label')));

                var up = document.createElement('button');
                up.type = 'button'; up.textContent = '‹';
                up.title = 'Move earlier'; up.disabled = i === 0;
                up.addEventListener('click', function () { move(i, i - 1); });

                var down = document.createElement('button');
                down.type = 'button'; down.textContent = '›';
                down.title = 'Move later'; down.disabled = i === order.length - 1;
                down.addEventListener('click', function () { move(i, i + 1); });

                var rm = document.createElement('button');
                rm.type = 'button'; rm.textContent = '×';
                rm.title = 'Remove';
                rm.addEventListener('click', function () {
                    b.checked = false;
                    order.splice(order.indexOf(url), 1);
                    render();
                });

                chip.appendChild(up); chip.appendChild(down); chip.appendChild(rm);
                qlPicked.appendChild(chip);
            });

            var full = order.length >= MAX;
            qlCount.textContent = order.length + ' of ' + MAX + ' chosen';
            qlCount.classList.toggle('is-full', full);
            // At the limit, disable what is not already ticked rather than
            // letting the click succeed and then rejecting it.
            boxes.forEach(function (b) { b.disabled = full && !b.checked; });
        }

        function move(from, to) {
            if (to < 0 || to >= order.length) { return; }
            var v = order.splice(from, 1)[0];
            order.splice(to, 0, v);
            render();
        }

        boxes.forEach(function (b) {
            b.addEventListener('change', function () {
                var at = order.indexOf(b.value);
                if (b.checked && at === -1) { order.push(b.value); }
                if (!b.checked && at !== -1) { order.splice(at, 1); }
                render();
            });
        });

        function filter() {
            var q = qlSearch.value.trim().toLowerCase();
            var shown = 0;
            Array.prototype.forEach.call(qlList.querySelectorAll('.ql-sect'), function (sect) {
                var group = (sect.getAttribute('data-group') || '').toLowerCase();
                var groupHit = q !== '' && group.indexOf(q) !== -1;
                var any = false;
                Array.prototype.forEach.call(sect.querySelectorAll('.ql-opt'), function (opt) {
                    var text = opt.textContent.trim().toLowerCase();
                    var hit = q === '' || groupHit || text.indexOf(q) !== -1;
                    opt.hidden = !hit;
                    if (hit) { any = true; shown++; }
                });
                sect.hidden = !any;
            });
            qlNoHit.hidden = shown !== 0;
        }

        function open() {
            lastFocus = document.activeElement;
            qlModal.hidden = false;
            document.body.style.overflow = 'hidden';
            qlMsg.textContent = ''; qlMsg.classList.remove('is-error');
            render();
            qlSearch.focus();
        }

        function close() {
            qlModal.hidden = true;
            document.body.style.overflow = '';
            qlSearch.value = ''; filter();
            if (lastFocus && lastFocus.focus) { lastFocus.focus(); }
        }

        Array.prototype.forEach.call(document.querySelectorAll('[data-ql-open]'), function (el) {
            el.addEventListener('click', open);
        });
        Array.prototype.forEach.call(qlModal.querySelectorAll('[data-ql-close]'), function (el) {
            el.addEventListener('click', close);
        });
        qlSearch.addEventListener('input', filter);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !qlModal.hidden) { close(); }
        });

        qlSave.addEventListener('click', function () {
            qlSave.disabled = true;
            qlMsg.classList.remove('is-error');
            qlMsg.textContent = 'Saving…';

            var body = new FormData();
            body.append('action', 'save');
            body.append('csrf', window.CDAT_CSRF || '');
            body.append('urls', JSON.stringify(order));

            fetch(window.CDAT_QLAPI || 'quick_links_api.php', {
                method: 'POST', body: body, credentials: 'same-origin'
            }).then(function (r) { return r.json(); }).then(function (data) {
                qlSave.disabled = false;
                if (!data.ok) {
                    qlMsg.textContent = data.error || 'Could not save.';
                    qlMsg.classList.add('is-error');
                    return;
                }
                // The dashboard shows what was just chosen, so redraw it.
                if (document.getElementById('qlPanel')) { window.location.reload(); return; }
                qlMsg.textContent = 'Saved.';
                var badge = document.querySelector('.ql-badge');
                var btn = document.querySelector('.ql-open');
                if (!badge && btn && order.length) {
                    badge = document.createElement('span');
                    badge.className = 'ql-badge';
                    btn.appendChild(badge);
                }
                if (badge) {
                    badge.textContent = order.length;
                    badge.hidden = order.length === 0;
                }
                setTimeout(close, 550);
            }).catch(function (err) {
                qlSave.disabled = false;
                qlMsg.textContent = 'Could not save: ' + (err.message || err);
                qlMsg.classList.add('is-error');
            });
        });

        render();
    }

    // Legacy pages announce things in <marquee>, an element browsers only still
    // honour out of goodwill. Rebuild it as a notice whose text scrolls by CSS
    // animation -- same effect, and it survives the element being dropped.
    Array.prototype.forEach.call(document.querySelectorAll('.content marquee'), function (m) {
        var text = (m.textContent || '').replace(/\s+/g, ' ').replace(/^[\s*]+|[\s*]+$/g, '');
        if (!text) { m.remove(); return; }
        var d = document.createElement('div');
        d.className = 'notice';
        var s = document.createElement('span');
        s.textContent = text;
        d.appendChild(s);
        m.parentNode.replaceChild(d, m);
    });

    // Global Form SPA Interceptor
    document.addEventListener('submit', function (e) {
        if (!e.target || e.target.tagName !== 'FORM') return;
        var form = e.target;
        
        // Exclude specific forms (file uploads, exports, get requests, login)
        if (form.getAttribute('enctype') && form.getAttribute('enctype').includes('multipart/form-data')) return;
        if (form.method && form.method.toLowerCase() === 'get') return;
        if (form.classList.contains('no-ajax') || form.hasAttribute('data-no-ajax')) return;
        if (form.action && (form.action.includes('login.php') || form.action.includes('logout.php') || form.action.includes('admin_upload.php'))) return;

        // Ensure we actually capture the submit button that was clicked.
        var btn = e.submitter || form.querySelector('input[type="submit"][name="BTN_SUM"], button[type="submit"], input[type="submit"]');
        
        // Exclude export buttons
        if (btn && btn.value && (btn.value.toLowerCase().includes('export') || btn.value.toLowerCase().includes('download'))) return;

        // If it passed all filters, it's a standard data search form! Intercept it for SPA.
        e.preventDefault();

        if (btn && !btn.disabled && !btn.classList.contains('no-loading')) {
            var width = btn.offsetWidth;
            if (width > 0) btn.style.minWidth = width + 'px';
            
            if (btn.tagName === 'INPUT') {
                btn.dataset.original = btn.value;
                btn.value = 'Loading...';
            } else {
                btn.dataset.original = btn.textContent;
                btn.textContent = 'Loading...';
            }
            btn.disabled = true;
        }

        // Find or create the global results container
        var resultsContainer = document.getElementById('global-ajax-results');
        if (!resultsContainer) {
            resultsContainer = document.createElement('div');
            resultsContainer.id = 'global-ajax-results';
            resultsContainer.className = 'sum-ajax-results';
            resultsContainer.setAttribute('aria-live', 'polite');
            // Insert after search section or form wrapper
            var wrapper = form.closest('.sum-search-card')
                || form.closest('.sum-page')
                || form.closest('table[width="1323"]')
                || form.closest('table')
                || form.parentNode;
            if (wrapper && wrapper.parentNode) {
                wrapper.parentNode.insertBefore(resultsContainer, wrapper.nextSibling);
            } else {
                form.parentNode.appendChild(resultsContainer);
            }
        }
        
        resultsContainer.innerHTML = '<div class="sum-loading">Loading data, please wait&hellip;</div>';

        // Add the clicked submit button to FormData if it has a name
        var formData = new FormData(form);
        if (btn && btn.name) {
            formData.append(btn.name, btn.dataset.original || btn.value);
        }

        fetch(form.action || window.location.href, {
            method: form.method || 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(response) {
            return response.text();
        })
        .then(function(html) {
            resultsContainer.innerHTML = html;
            var sumPage = document.querySelector('.sum-page');
            if (sumPage) { sumPage.classList.add('sum-page--has-results'); }
            // Re-initialize any new datatables that were just injected!
            if (typeof window.initGlobalDatatables === 'function') {
                window.initGlobalDatatables();
            }
            if (typeof window.initSearchableSelects === 'function') {
                window.initSearchableSelects(resultsContainer);
            }
            if (typeof window.initDatePickers === 'function') {
                window.initDatePickers(resultsContainer);
            }
            if (typeof window.initFileUploads === 'function') {
                window.initFileUploads(resultsContainer);
            }
        })
        .catch(function(err) {
            resultsContainer.innerHTML = '<div class="sum-empty-state">An error occurred while searching.</div>';
            console.error("AJAX Error:", err);
        })
        .finally(function() {
            if (btn) {
                btn.disabled = false;
                if (btn.tagName === 'INPUT') btn.value = btn.dataset.original;
                else btn.textContent = btn.dataset.original;
            }
        });
    });

    // Global Form Validations (Auto-apply to all phone inputs)
    var phoneInputs = document.querySelectorAll('input[name="PHONE_NO"], input[name="PHONE"], input[name="NUMBER"]');
    Array.prototype.forEach.call(phoneInputs, function(input) {
        if (!input.hasAttribute('pattern')) input.setAttribute('pattern', '^\\+?[0-9]+$');
        if (!input.hasAttribute('minlength')) input.setAttribute('minlength', '7');
        if (!input.hasAttribute('maxlength')) input.setAttribute('maxlength', '15');
        if (!input.hasAttribute('title')) input.setAttribute('title', 'Please enter a valid phone number (numbers and optional + only)');
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9+]/g, '');
        });
    });

    // Global Form Validations (Auto-apply to all IMEI inputs)
    var imeiInputs = document.querySelectorAll('input[name*="IMEI"]');
    Array.prototype.forEach.call(imeiInputs, function(input) {
        if (!input.hasAttribute('pattern')) input.setAttribute('pattern', '^[0-9]{15}$');
        if (!input.hasAttribute('minlength')) input.setAttribute('minlength', '15');
        if (!input.hasAttribute('maxlength')) input.setAttribute('maxlength', '15');
        if (!input.hasAttribute('title')) input.setAttribute('title', 'IMEI must be exactly 15 digits');
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });

    // Global Form Validations (Auto-apply to all Date inputs)
    var dateInputs = document.querySelectorAll('input[name*="DATE"], input[name*="_DT"]');
    Array.prototype.forEach.call(dateInputs, function(input) {
        if (!input.hasAttribute('pattern')) input.setAttribute('pattern', '^\\d{4}-\\d{2}-\\d{2}$');
        if (!input.hasAttribute('title')) input.setAttribute('title', 'Date must be in yyyy-mm-dd format');
        // Let them type dashes as well
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9-]/g, '');
        });
    });

    // Global Datatables Initialization
    window.initGlobalDatatables = function() {
        if (window.simpleDatatables) {
            // Target legacy result tables, but avoid deeply nested or layout tables without thead
            var tables = document.querySelectorAll('table[border="1"], table#myTable');
            Array.prototype.forEach.call(tables, function(table) {
                if (table.closest('.datatable-wrapper') || table.closest('.dataTable-wrapper')) return;

                var hasThead = table.querySelector('thead');
                var hasTbody = table.querySelector('tbody');

                // Auto-fix legacy tables that are missing thead tags (browsers auto-insert tbody)
                if (!hasThead) {
                    var rows = Array.prototype.slice.call(table.querySelectorAll('tr'));
                    if (rows.length > 0 && rows[0].querySelector('th')) {
                        var thead = document.createElement('thead');
                        thead.appendChild(rows[0]); // Moves the th row out of tbody
                        
                        if (!hasTbody) {
                            hasTbody = document.createElement('tbody');
                            for (var i = 1; i < rows.length; i++) {
                                hasTbody.appendChild(rows[i]);
                            }
                            table.appendChild(hasTbody);
                        }
                        table.insertBefore(thead, table.firstChild);
                        hasThead = true;
                    }
                }

                if (hasThead && hasTbody) {
                    // Prevent crash if table is entirely empty of data rows
                    var tbodyElement = table.querySelector('tbody');
                    var tbodyRows = tbodyElement ? tbodyElement.querySelectorAll('tr') : [];
                    if (tbodyRows.length === 0 && tbodyElement) {
                        var colCount = table.querySelector('thead tr').children.length;
                        var emptyTr = document.createElement('tr');
                        var emptyTd = document.createElement('td');
                        emptyTd.colSpan = colCount;
                        emptyTd.textContent = "No entries found";
                        emptyTd.style.textAlign = "center";
                        emptyTr.appendChild(emptyTd);
                        tbodyElement.appendChild(emptyTr);
                    }

                    try {
                        var cloneTable = table.cloneNode(true);
                        var exportName = table.getAttribute('data-export-name') || 'export_data.csv';
                        var sumPanel = table.closest('.sum-table-panel');
                        var sumActions = sumPanel
                            ? (sumPanel.querySelector('#sum-table-actions') || sumPanel.querySelector('.sum-table-panel__actions'))
                            : null;

                        var btnContainer = document.createElement('div');
                        btnContainer.className = 'cdat-dt-toolbar';

                        var exportBtn = document.createElement('button');
                        exportBtn.type = 'button';
                        exportBtn.className = 'cdat-dt-btn cdat-dt-btn--export';
                        exportBtn.textContent = 'Export CSV';
                        exportBtn.onclick = function(e) {
                            e.preventDefault();
                            exportTableToCSV(cloneTable, exportName);
                        };

                        var printBtn = document.createElement('button');
                        printBtn.type = 'button';
                        printBtn.className = 'cdat-dt-btn cdat-dt-btn--print';
                        printBtn.textContent = 'Print';
                        printBtn.onclick = function(e) {
                            e.preventDefault();
                            window.print();
                        };

                        btnContainer.appendChild(exportBtn);
                        btnContainer.appendChild(printBtn);

                        if (sumActions) {
                            sumActions.innerHTML = '';
                            sumActions.appendChild(btnContainer);
                        } else {
                            table.parentNode.insertBefore(btnContainer, table);
                        }

                        var dt = new simpleDatatables.DataTable(table, {
                            perPage: 15,
                            perPageSelect: [15, 50, 100, 300, 500, 1000, ["Full", 0]],
                            searchable: true,
                            fixedHeader: false
                        });
                        // Keep a handle so print can expand to all rows
                        table._cdatDt = dt;
                        window._cdatDataTables = window._cdatDataTables || [];
                        window._cdatDataTables.push(dt);

                        printBtn.onclick = function(e) {
                            e.preventDefault();
                            preparePrintThenPrint();
                        };

                    } catch(e) {
                        console.warn("Could not initialize DataTable on", table, e);
                    }
                }
            });
        }
    };
    
    function exportTableToCSV(tableNode, filename) {
        var csv = [];
        var rows = tableNode.querySelectorAll('tr');
        for (var i = 0; i < rows.length; i++) {
            var row = [], cols = rows[i].querySelectorAll('td, th');
            for (var j = 0; j < cols.length; j++) {
                var data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/"/g, '""');
                row.push('"' + data + '"');
            }
            csv.push(row.join(','));
        }
        var csvFile = new Blob([csv.join('\n')], {type: 'text/csv'});
        var downloadLink = document.createElement('a');
        downloadLink.download = filename;
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = 'none';
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }

    /* Expand all DataTable pages before print so every row is on the page */
    var _cdatPrintRestore = [];
    var _cdatPrintHeaderRestore = [];

    function flattenHeadersForPrint() {
        // beforeprint + Print button can both run; don't wipe a prior snapshot
        if (_cdatPrintHeaderRestore.length) return;
        var ths = document.querySelectorAll(
            '.sum-table-panel thead th, .datatable-table thead th, table[border="1"] thead th'
        );
        Array.prototype.forEach.call(ths, function(th) {
            var btn = th.querySelector('button, a, .datatable-sorter');
            if (!btn) return;
            _cdatPrintHeaderRestore.push({ th: th, html: th.innerHTML });
            var label = (btn.textContent || th.textContent || '').replace(/\s+/g, ' ').trim();
            th.textContent = label;
        });
    }

    function restoreHeadersAfterPrint() {
        _cdatPrintHeaderRestore.forEach(function(item) {
            try { item.th.innerHTML = item.html; } catch (err) { /* ignore */ }
        });
        _cdatPrintHeaderRestore = [];
    }

    function expandTablesForPrint() {
        _cdatPrintRestore = [];
        flattenHeadersForPrint();
        document.body.classList.add('cdat-printing');
        var tables = document.querySelectorAll('table');
        Array.prototype.forEach.call(tables, function(table) {
            var dt = table._cdatDt;
            if (!dt) { return; }
            var prev = (dt.options && dt.options.perPage) || 15;
            var total = 0;
            try {
                if (dt.data && dt.data.data) { total = dt.data.data.length; }
                else if (typeof dt.data === 'object' && Array.isArray(dt.data)) { total = dt.data.length; }
            } catch (err) { total = 0; }
            if (!total) { total = 9999; }
            _cdatPrintRestore.push({ dt: dt, perPage: prev });
            try {
                // Clear any active search so all rows print
                if (typeof dt.search === 'function') {
                    dt.search('');
                } else if (dt.input) {
                    dt.input.value = '';
                    if (typeof dt.search === 'function') { dt.search(''); }
                }
                if (typeof dt.setPerPage === 'function') {
                    dt.setPerPage(total);
                } else if (dt.options) {
                    dt.options.perPage = total;
                    if (typeof dt.update === 'function') { dt.update(); }
                    else if (typeof dt.refresh === 'function') { dt.refresh(); }
                }
            } catch (err) { /* leave as-is */ }
        });
    }

    function restoreTablesAfterPrint() {
        document.body.classList.remove('cdat-printing');
        restoreHeadersAfterPrint();
        _cdatPrintRestore.forEach(function(item) {
            try {
                if (typeof item.dt.setPerPage === 'function') {
                    item.dt.setPerPage(item.perPage);
                } else if (item.dt.options) {
                    item.dt.options.perPage = item.perPage;
                    if (typeof item.dt.update === 'function') { item.dt.update(); }
                }
            } catch (err) { /* ignore */ }
        });
        _cdatPrintRestore = [];
    }

    function preparePrintThenPrint() {
        expandTablesForPrint();
        // Let the DOM paint all rows before the print dialog
        setTimeout(function() {
            window.print();
        }, 120);
    }

    window.addEventListener('beforeprint', expandTablesForPrint);
    window.addEventListener('afterprint', restoreTablesAfterPrint);

    // ── Searchable select (all dropdowns in content) ──────────────
    window.initSearchableSelects = function(root) {
        var scope = root || document;
        var selects = scope.querySelectorAll('select');
        Array.prototype.forEach.call(selects, function(select) {
            if (select.dataset.ssReady === '1') return;
            if (select.closest('.sum-ss')) return;
            if (select.multiple) return;
            if (select.dataset.noSearchable === '1') return;
            if (select.closest('.datatable-wrapper') || select.closest('.dataTable-wrapper')) return;
            if (select.closest('.datatable-dropdown') || select.closest('.dataTable-dropdown')) return;
            if (select.classList.contains('datatable-selector')) return;
            if (!select.closest('.content') && !select.closest('.sum-page') && !select.closest('.sum-search-card') && !select.closest('.sum-entry-card')) {
                return;
            }
            enhanceSearchableSelect(select);
        });
    };

    function enhanceSearchableSelect(select) {
        select.dataset.ssReady = '1';
        select.classList.add('sum-ss__native');

        var placeholder = select.getAttribute('data-placeholder') || '';
        if (!placeholder) {
            var firstOpt = select.options[0];
            if (firstOpt && firstOpt.value === '') {
                placeholder = (firstOpt.textContent || '').trim() || 'Select…';
                firstOpt.setAttribute('data-placeholder', '1');
            } else {
                placeholder = 'Select…';
            }
        }

        var wrap = document.createElement('div');
        wrap.className = 'sum-ss';
        wrap.setAttribute('data-ss-for', select.id || select.name || '');

        var trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'sum-ss__trigger';
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');

        var valueSpan = document.createElement('span');
        valueSpan.className = 'sum-ss__value';
        var chevron = document.createElement('span');
        chevron.className = 'sum-ss__chevron';
        chevron.setAttribute('aria-hidden', 'true');
        trigger.appendChild(valueSpan);
        trigger.appendChild(chevron);

        var panel = document.createElement('div');
        panel.className = 'sum-ss__panel';
        panel.setAttribute('role', 'listbox');

        var usableOptions = Array.prototype.slice.call(select.options).filter(function(opt) {
            return !opt.hasAttribute('data-placeholder') && !opt.disabled && opt.value !== '';
        });
        var searchInput = null;
        if (usableOptions.length > 6) {
            var searchWrap = document.createElement('div');
            searchWrap.className = 'sum-ss__search-wrap';
            searchInput = document.createElement('input');
            searchInput.type = 'search';
            searchInput.className = 'sum-ss__search';
            searchInput.placeholder = 'Search…';
            searchInput.setAttribute('autocomplete', 'off');
            searchInput.setAttribute('aria-label', 'Search options');
            searchWrap.appendChild(searchInput);
            panel.appendChild(searchWrap);
        }

        var list = document.createElement('div');
        list.className = 'sum-ss__list';
        panel.appendChild(list);

        select.parentNode.insertBefore(wrap, select);
        wrap.appendChild(select);
        wrap.appendChild(trigger);
        wrap.appendChild(panel);

        var optionButtons = [];
        var activeIndex = -1;

        function optionNodes() {
            return Array.prototype.slice.call(select.options).filter(function(opt) {
                if (opt.hasAttribute('data-placeholder')) return false;
                if (opt.disabled && opt.value === '') return false;
                return true;
            });
        }

        function syncLabel() {
            var opt = select.options[select.selectedIndex];
            var isEmpty = !opt || opt.value === '' || opt.hasAttribute('data-placeholder');
            valueSpan.textContent = isEmpty ? placeholder : opt.textContent;
            trigger.classList.toggle('is-placeholder', isEmpty);
            optionButtons.forEach(function(btn) {
                btn.classList.toggle('is-selected', btn.dataset.value === select.value);
            });
        }

        function renderList(filter) {
            var q = (filter || '').toLowerCase().trim();
            list.innerHTML = '';
            optionButtons = [];
            activeIndex = -1;
            var matches = 0;
            optionNodes().forEach(function(opt) {
                if (opt.value === '' && opt.hasAttribute('data-placeholder')) return;
                var text = opt.textContent || '';
                if (q && text.toLowerCase().indexOf(q) === -1) return;
                matches++;
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'sum-ss__option';
                btn.textContent = text;
                btn.dataset.value = opt.value;
                if (opt.value === select.value) btn.classList.add('is-selected');
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    select.value = opt.value;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    syncLabel();
                    closePanel();
                });
                list.appendChild(btn);
                optionButtons.push(btn);
            });
            if (matches === 0) {
                var empty = document.createElement('div');
                empty.className = 'sum-ss__empty';
                empty.textContent = 'No matches';
                list.appendChild(empty);
            }
        }

        function openPanel() {
            document.querySelectorAll('.sum-ss.is-open').forEach(function(other) {
                if (other !== wrap) other.classList.remove('is-open');
            });
            document.querySelectorAll('.sum-dp.is-open').forEach(function(other) {
                other.classList.remove('is-open');
            });
            wrap.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');
            if (searchInput) searchInput.value = '';
            renderList('');
            setTimeout(function() {
                if (searchInput) searchInput.focus();
            }, 0);
        }

        function closePanel() {
            wrap.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
            activeIndex = -1;
        }

        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            if (wrap.classList.contains('is-open')) closePanel();
            else openPanel();
        });

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                renderList(searchInput.value);
            });
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    e.preventDefault();
                    closePanel();
                    trigger.focus();
                    return;
                }
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (!optionButtons.length) return;
                    activeIndex = (activeIndex + 1) % optionButtons.length;
                    optionButtons.forEach(function(b, i) { b.classList.toggle('is-active', i === activeIndex); });
                    optionButtons[activeIndex].scrollIntoView({ block: 'nearest' });
                    return;
                }
                if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (!optionButtons.length) return;
                    activeIndex = activeIndex <= 0 ? optionButtons.length - 1 : activeIndex - 1;
                    optionButtons.forEach(function(b, i) { b.classList.toggle('is-active', i === activeIndex); });
                    optionButtons[activeIndex].scrollIntoView({ block: 'nearest' });
                    return;
                }
                if (e.key === 'Enter' && activeIndex >= 0 && optionButtons[activeIndex]) {
                    e.preventDefault();
                    optionButtons[activeIndex].click();
                }
            });
        }

        select.addEventListener('change', syncLabel);
        syncLabel();
    }

    // ── Date picker component ──────────────────────────────────────
    var MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    var DOW = ['Su','Mo','Tu','We','Th','Fr','Sa'];

    window.initDatePickers = function(root) {
        var scope = root || document;
        var inputs = scope.querySelectorAll(
            'input[data-date-picker="1"], input[type="date"],' +
            'input[name*="DATE"], input[name*="_DT"], input[id*="datepicker"]'
        );
        Array.prototype.forEach.call(inputs, function(input) {
            if (input.dataset.dpReady === '1') return;
            if (input.closest('.sum-dp')) return;
            if (input.type === 'hidden' || input.type === 'submit' || input.type === 'button') return;
            if (input.type === 'text' && !input.hasAttribute('data-date-picker')
                && !(input.name && /(DATE|_DT)/i.test(input.name))
                && !(input.id && /datepicker/i.test(input.id))) {
                return;
            }
            enhanceDatePicker(input);
        });
    };

    function pad2(n) { return n < 10 ? '0' + n : String(n); }

    function parseYmd(str) {
        if (!str) return null;
        var m = String(str).match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if (!m) return null;
        var d = new Date(Number(m[1]), Number(m[2]) - 1, Number(m[3]));
        if (d.getFullYear() !== Number(m[1]) || d.getMonth() !== Number(m[2]) - 1 || d.getDate() !== Number(m[3])) {
            return null;
        }
        return d;
    }

    function formatYmd(d) {
        return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
    }

    function enhanceDatePicker(input) {
        input.dataset.dpReady = '1';
        if (input.type === 'date') {
            try { input.type = 'text'; } catch (err) { /* ignore */ }
        }
        input.classList.add('sum-date-input');
        if (!input.getAttribute('placeholder')) input.setAttribute('placeholder', 'yyyy-mm-dd');
        if (!input.getAttribute('pattern')) input.setAttribute('pattern', '^\\d{4}-\\d{2}-\\d{2}$');
        if (!input.getAttribute('autocomplete')) input.setAttribute('autocomplete', 'off');
        input.setAttribute('inputmode', 'numeric');

        var wrap = document.createElement('div');
        wrap.className = 'sum-dp';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);

        var triggerBtn = document.createElement('button');
        triggerBtn.type = 'button';
        triggerBtn.className = 'sum-dp__icon';
        triggerBtn.setAttribute('aria-label', 'Open calendar');
        triggerBtn.innerHTML = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>';
        wrap.appendChild(triggerBtn);

        var panel = document.createElement('div');
        panel.className = 'sum-dp__panel';
        panel.setAttribute('role', 'dialog');
        panel.setAttribute('aria-label', 'Choose date');
        wrap.appendChild(panel);

        var view = parseYmd(input.value) || new Date();
        view = new Date(view.getFullYear(), view.getMonth(), 1);

        function closePanel() {
            wrap.classList.remove('is-open');
        }

        function openPanel() {
            document.querySelectorAll('.sum-dp.is-open').forEach(function(other) {
                if (other !== wrap) other.classList.remove('is-open');
            });
            document.querySelectorAll('.sum-ss.is-open').forEach(function(other) {
                other.classList.remove('is-open');
            });
            var current = parseYmd(input.value);
            if (current) view = new Date(current.getFullYear(), current.getMonth(), 1);
            wrap.classList.add('is-open');
            render();
        }

        function render() {
            var selected = parseYmd(input.value);
            var today = new Date();
            var y = view.getFullYear();
            var m = view.getMonth();
            var firstDow = new Date(y, m, 1).getDay();
            var daysInMonth = new Date(y, m + 1, 0).getDate();

            var html = '';
            html += '<div class="sum-dp__head">';
            html += '<button type="button" class="sum-dp__nav" data-nav="-1" aria-label="Previous month">&lsaquo;</button>';
            html += '<div class="sum-dp__title">' + MONTHS[m] + ' ' + y + '</div>';
            html += '<button type="button" class="sum-dp__nav" data-nav="1" aria-label="Next month">&rsaquo;</button>';
            html += '</div>';
            html += '<div class="sum-dp__dow">';
            DOW.forEach(function(d) { html += '<span>' + d + '</span>'; });
            html += '</div><div class="sum-dp__grid">';

            var i;
            for (i = 0; i < firstDow; i++) {
                html += '<span class="sum-dp__day is-empty"></span>';
            }
            for (var day = 1; day <= daysInMonth; day++) {
                var cls = 'sum-dp__day';
                if (selected && selected.getFullYear() === y && selected.getMonth() === m && selected.getDate() === day) {
                    cls += ' is-selected';
                }
                if (today.getFullYear() === y && today.getMonth() === m && today.getDate() === day) {
                    cls += ' is-today';
                }
                html += '<button type="button" class="' + cls + '" data-day="' + day + '">' + day + '</button>';
            }
            html += '</div>';
            html += '<div class="sum-dp__foot">';
            html += '<button type="button" class="sum-dp__today" data-today="1">Today</button>';
            html += '<button type="button" class="sum-dp__clear" data-clear="1">Clear</button>';
            html += '</div>';
            panel.innerHTML = html;
        }

        panel.addEventListener('click', function(e) {
            var t = e.target.closest('button');
            if (!t) return;
            e.preventDefault();
            if (t.dataset.nav) {
                view = new Date(view.getFullYear(), view.getMonth() + Number(t.dataset.nav), 1);
                render();
                return;
            }
            if (t.dataset.today) {
                var now = new Date();
                input.value = formatYmd(now);
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
                closePanel();
                return;
            }
            if (t.dataset.clear) {
                input.value = '';
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
                closePanel();
                return;
            }
            if (t.dataset.day) {
                var picked = new Date(view.getFullYear(), view.getMonth(), Number(t.dataset.day));
                input.value = formatYmd(picked);
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
                closePanel();
            }
        });

        triggerBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (wrap.classList.contains('is-open')) closePanel();
            else openPanel();
        });

        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9-]/g, '');
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closePanel();
        });
    }

    document.addEventListener('click', function(e) {
        var openSelects = document.querySelectorAll('.sum-ss.is-open');
        Array.prototype.forEach.call(openSelects, function(wrap) {
            if (!wrap.contains(e.target)) {
                wrap.classList.remove('is-open');
                var btn = wrap.querySelector('.sum-ss__trigger');
                if (btn) btn.setAttribute('aria-expanded', 'false');
            }
        });
        var openDates = document.querySelectorAll('.sum-dp.is-open');
        Array.prototype.forEach.call(openDates, function(wrap) {
            if (!wrap.contains(e.target)) {
                wrap.classList.remove('is-open');
            }
        });
    });

    // ── File / image upload component ──────────────────────────────
    window.initFileUploads = function(root) {
        var scope = root || document;
        var inputs = scope.querySelectorAll(
            'input[type="file"][data-file-upload="1"],' +
            '.sum-page input[type="file"],' +
            '.sum-entry-form input[type="file"],' +
            '.sum-search-card input[type="file"]'
        );
        Array.prototype.forEach.call(inputs, function(input) {
            if (input.dataset.fuReady === '1') return;
            if (input.closest('.sum-fu')) return;
            if (input.dataset.noFileUpload === '1') return;
            if (input.style.display === 'none') return;
            enhanceFileUpload(input);
        });
    };

    function formatBytes(n) {
        if (!n && n !== 0) return '';
        if (n < 1024) return n + ' B';
        if (n < 1024 * 1024) return (n / 1024).toFixed(1) + ' KB';
        return (n / (1024 * 1024)).toFixed(1) + ' MB';
    }

    function enhanceFileUpload(input) {
        input.dataset.fuReady = '1';
        input.classList.add('sum-fu__native');

        var mode = input.getAttribute('data-upload-mode') ||
            ((input.accept || '').indexOf('image') !== -1 ? 'image' : 'file');
        var hint = input.getAttribute('data-upload-hint') ||
            (mode === 'image'
                ? 'Drag & drop an image, or browse'
                : (input.multiple ? 'Drag & drop files, or browse' : 'Drag & drop a file, or browse'));
        var browseLabel = mode === 'image' ? 'Choose image' : 'Choose file' + (input.multiple ? 's' : '');

        var wrap = document.createElement('div');
        wrap.className = 'sum-fu' + (mode === 'image' ? ' sum-fu--image' : '');

        var drop = document.createElement('div');
        drop.className = 'sum-fu__drop';
        drop.setAttribute('tabindex', '0');
        drop.setAttribute('role', 'button');
        drop.setAttribute('aria-label', browseLabel);

        var icon = document.createElement('div');
        icon.className = 'sum-fu__icon';
        icon.setAttribute('aria-hidden', 'true');
        icon.innerHTML = mode === 'image'
            ? '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10.5" r="1.5"/><path d="m21 15-4.5-4.5L9 18"/></svg>'
            : '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V7m0 0 3.5 3.5M12 7 8.5 10.5"/><path d="M20 16.5V18a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-1.5"/></svg>';

        var title = document.createElement('div');
        title.className = 'sum-fu__title';
        title.textContent = browseLabel;

        var hintEl = document.createElement('div');
        hintEl.className = 'sum-fu__hint';
        hintEl.textContent = hint;

        var browseBtn = document.createElement('button');
        browseBtn.type = 'button';
        browseBtn.className = 'sum-fu__browse';
        browseBtn.textContent = 'Browse';

        drop.appendChild(icon);
        drop.appendChild(title);
        drop.appendChild(hintEl);
        drop.appendChild(browseBtn);

        var preview = document.createElement('div');
        preview.className = 'sum-fu__preview';
        preview.hidden = true;

        var meta = document.createElement('div');
        meta.className = 'sum-fu__meta';

        var clearBtn = document.createElement('button');
        clearBtn.type = 'button';
        clearBtn.className = 'sum-fu__clear';
        clearBtn.textContent = 'Remove';
        clearBtn.hidden = true;

        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);
        wrap.appendChild(drop);
        wrap.appendChild(preview);
        wrap.appendChild(meta);
        wrap.appendChild(clearBtn);

        var objectUrls = [];

        function revokeUrls() {
            objectUrls.forEach(function(u) {
                try { URL.revokeObjectURL(u); } catch (err) {}
            });
            objectUrls = [];
        }

        function setFiles(fileList) {
            if (!fileList) return;
            try {
                var dt = new DataTransfer();
                Array.prototype.forEach.call(fileList, function(f) { dt.items.add(f); });
                if (!input.multiple && dt.files.length > 1) {
                    var only = new DataTransfer();
                    only.items.add(dt.files[0]);
                    input.files = only.files;
                } else {
                    input.files = dt.files;
                }
            } catch (err) {
                // Older browsers: rely on native picker only
            }
            input.dispatchEvent(new Event('change', { bubbles: true }));
            syncUI();
        }

        function syncUI() {
            revokeUrls();
            preview.innerHTML = '';
            meta.textContent = '';
            var files = input.files ? Array.prototype.slice.call(input.files) : [];
            var has = files.length > 0;
            wrap.classList.toggle('has-files', has);
            clearBtn.hidden = !has;
            preview.hidden = !has;
            if (!has) return;

            files.forEach(function(file) {
                if (mode === 'image' && file.type && file.type.indexOf('image/') === 0) {
                    var url = URL.createObjectURL(file);
                    objectUrls.push(url);
                    var img = document.createElement('img');
                    img.className = 'sum-fu__thumb';
                    img.src = url;
                    img.alt = file.name;
                    preview.appendChild(img);
                } else {
                    var chip = document.createElement('div');
                    chip.className = 'sum-fu__filechip';
                    chip.textContent = file.name;
                    preview.appendChild(chip);
                }
            });

            if (files.length === 1) {
                meta.textContent = files[0].name + (files[0].size ? ' · ' + formatBytes(files[0].size) : '');
            } else {
                var total = files.reduce(function(s, f) { return s + (f.size || 0); }, 0);
                meta.textContent = files.length + ' files' + (total ? ' · ' + formatBytes(total) : '');
            }
        }

        function openPicker() {
            input.click();
        }

        drop.addEventListener('click', function(e) {
            if (e.target === clearBtn) return;
            openPicker();
        });
        browseBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openPicker();
        });
        drop.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openPicker();
            }
        });

        ['dragenter', 'dragover'].forEach(function(evt) {
            drop.addEventListener(evt, function(e) {
                e.preventDefault();
                e.stopPropagation();
                wrap.classList.add('is-dragover');
            });
        });
        ['dragleave', 'drop'].forEach(function(evt) {
            drop.addEventListener(evt, function(e) {
                e.preventDefault();
                e.stopPropagation();
                wrap.classList.remove('is-dragover');
            });
        });
        drop.addEventListener('drop', function(e) {
            if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
                setFiles(e.dataTransfer.files);
            }
        });

        input.addEventListener('change', syncUI);
        clearBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            input.value = '';
            try {
                var empty = new DataTransfer();
                input.files = empty.files;
            } catch (err) {}
            input.dispatchEvent(new Event('change', { bubbles: true }));
            syncUI();
        });

        syncUI();
    }

    // Call it on initial page load
    window.initGlobalDatatables();
    window.initSearchableSelects();
    window.initDatePickers();
    window.initFileUploads();

}());
