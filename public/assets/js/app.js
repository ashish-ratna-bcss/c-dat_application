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
    var SCROLL_KEY = 'cdat.nav.scroll';

    function saveNavScroll() {
        var navEl = document.querySelector('.sidebar nav');
        if (!navEl) { return; }
        try { sessionStorage.setItem(SCROLL_KEY, String(navEl.scrollTop)); }
        catch (e) { /* private mode */ }
    }

    function restoreNavScroll() {
        var navEl = document.querySelector('.sidebar nav');
        if (!navEl) { return; }
        try {
            var saved = sessionStorage.getItem(SCROLL_KEY);
            if (saved !== null && saved !== '') {
                navEl.scrollTop = parseInt(saved, 10) || 0;
            }
        } catch (e) { /* private mode */ }
        var active = navEl.querySelector('.is-active');
        if (active) {
            active.scrollIntoView({ block: 'nearest' });
        }
    }

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
            requestAnimationFrame(restoreNavScroll);
        });
    });

    // Each menu link is a normal <a href> — the browser loads a fresh PHP page.
    // Remember sidebar scroll so Additional pages (100+ links) does not jump to top.
    var navEl = document.querySelector('.sidebar nav');
    if (navEl) {
        var scrollTimer;
        navEl.addEventListener('scroll', function () {
            clearTimeout(scrollTimer);
            scrollTimer = setTimeout(saveNavScroll, 80);
        }, { passive: true });

        navEl.addEventListener('click', function (e) {
            if (e.target.closest('a[href]')) {
                saveNavScroll();
            }
        });

        requestAnimationFrame(function () {
            requestAnimationFrame(restoreNavScroll);
        });
    }

    // Load sidebar pages into the main pane so switching JRMS (and other)
    // menu items does not rebuild the whole chrome.
    function sameOrigin(href) {
        try {
            var u = new URL(href, window.location.href);
            return u.origin === window.location.origin;
        } catch (err) {
            return false;
        }
    }

    function setActiveNav(url) {
        var path;
        try { path = new URL(url, window.location.href).pathname; }
        catch (err) { return; }
        Array.prototype.forEach.call(document.querySelectorAll('.sidebar nav a[href]'), function (a) {
            var aPath;
            try { aPath = new URL(a.href, window.location.href).pathname; }
            catch (e2) { return; }
            var on = aPath === path;
            a.classList.toggle('is-active', on);
            if (on) a.setAttribute('aria-current', 'page');
            else a.removeAttribute('aria-current');
        });
    }

    var mainNavAbort = null;
    function loadMain(url, push) {
        var pane = document.querySelector('.content .cdat-page') || document.querySelector('.content .card') || document.getElementById('main');
        if (!pane) {
            window.location.href = url;
            return;
        }
        if (typeof abortSearch === 'function') { abortSearch(); }
        if (mainNavAbort) { mainNavAbort.abort(); }
        mainNavAbort = new AbortController();
        var thisAbort = mainNavAbort;
        pane.innerHTML = '<div class="sum-loading">Loading&hellip;</div>';
        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
            signal: thisAbort.signal
        }).then(function (r) {
            var title = r.headers.get('X-CDAT-Title');
            return r.text().then(function (html) { return { html: html, title: title }; });
        }).then(function (data) {
            if (thisAbort.signal.aborted) { return; }
            if (/^\s*<(!DOCTYPE|html[\s>])/i.test(data.html)) {
                window.location.href = url;
                return;
            }
            pane.innerHTML = data.html;
            if (data.title) {
                var t = data.title;
                var h1 = document.querySelector('.page-title h1');
                if (h1) h1.textContent = t;
                document.title = t + ' \u2014 CDAT';
            }
            if (push) { history.pushState({ cdatMain: 1 }, '', url); }
            setActiveNav(url);
            if (typeof window.initSearchableSelects === 'function') window.initSearchableSelects(pane);
            if (typeof window.initDatePickers === 'function') window.initDatePickers(pane);
            if (typeof window.initFileUploads === 'function') window.initFileUploads(pane);
            if (typeof window.initGlobalDatatables === 'function') window.initGlobalDatatables();
            if (typeof window.initUserManagement === 'function') window.initUserManagement(pane);
        }).catch(function (err) {
            if (err && err.name === 'AbortError') { return; }
            window.location.href = url;
        });
    }

    document.addEventListener('click', function (e) {
        if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        var a = e.target.closest && e.target.closest('.sidebar nav a[href], .side-foot a[href]');
        if (!a) return;
        var href = a.getAttribute('href') || '';
        if (href === '' || href === '#' || href.indexOf('javascript:') === 0) return;
        if (!sameOrigin(href)) return;
        var path;
        try { path = new URL(a.href, window.location.href).pathname; }
        catch (err) { return; }
        if (/\/(logout|login|data-upload)(\/|$)/i.test(path)) return;
        e.preventDefault();
        saveNavScroll();
        if (typeof setNav === 'function' && window.matchMedia('(max-width: 991.98px)').matches) {
            setNav(false);
        }
        loadMain(a.href, true);
    });

    window.addEventListener('popstate', function () {
        loadMain(window.location.href, false);
    });

    // Small screens: slide the sidebar in over the content.
    var burger = document.querySelector('.burger');
    var scrim = document.querySelector('.scrim');

    function setNav(openIt) {
        document.body.classList.toggle('nav-open', openIt);
        if (scrim) {
            scrim.hidden = !openIt;
            scrim.classList.toggle('d-none', !openIt);
        }
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
            Array.prototype.forEach.call(document.querySelectorAll('.cdat-nav li'),
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
            Array.prototype.forEach.call(document.querySelectorAll('.cdat-nav > li'), function (li) {
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
                var first = document.querySelector('.cdat-nav li:not([hidden]) .nav-sub li:not([hidden]) a, .cdat-nav > li:not([hidden]) > .nav-item');
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

    // One search at a time. A second submit, or a sidebar click, aborts the
    // in-flight fetch so php -S (single-threaded) can serve the next page
    // instead of leaving two "Loading" states stacked.
    var searchAbort = null;
    var searchBtn = null;

    function firstEmptyRequired(form) {
        var els = form.querySelectorAll('input, select, textarea');
        for (var i = 0; i < els.length; i++) {
            var el = els[i];
            if (el.disabled) continue;
            var type = (el.type || '').toLowerCase();
            if (type === 'hidden' || type === 'submit' || type === 'button' || type === 'reset' || type === 'image' || type === 'file') continue;
            if (!el.required && !el.hasAttribute('required')) continue;
            if (String(el.value || '').trim() === '') return el;
        }
        return null;
    }

    function showSearchHint(form, message) {
        var resultsContainer = document.getElementById('global-ajax-results');
        if (!resultsContainer) {
            resultsContainer = form.parentNode && form.parentNode.querySelector
                ? form.parentNode.querySelector('#global-ajax-results')
                : null;
        }
        if (!resultsContainer) {
            var wrapper = form.closest('.sum-search-card') || form.parentNode;
            resultsContainer = document.createElement('div');
            resultsContainer.id = 'global-ajax-results';
            resultsContainer.className = 'sum-ajax-results';
            if (wrapper && wrapper.parentNode) {
                wrapper.parentNode.insertBefore(resultsContainer, wrapper.nextSibling);
            }
        }
        if (resultsContainer) {
            resultsContainer.innerHTML = '<div class="sum-empty-state">' + message + '</div>';
        }
    }

    function restoreSearchBtn() {
        if (!searchBtn) { return; }
        searchBtn.disabled = false;
        if (searchBtn.dataset.original) {
            if (searchBtn.tagName === 'INPUT') searchBtn.value = searchBtn.dataset.original;
            else searchBtn.textContent = searchBtn.dataset.original;
        }
        searchBtn = null;
    }

    function abortSearch() {
        if (searchAbort) {
            searchAbort.abort();
            searchAbort = null;
        }
        restoreSearchBtn();
        var pending = document.querySelector('#global-ajax-results .sum-loading');
        if (pending && pending.parentNode) {
            pending.parentNode.innerHTML = '';
        }
    }

    window.addEventListener('pagehide', abortSearch);
    window.addEventListener('beforeunload', abortSearch);
    document.addEventListener('click', function (e) {
        var a = e.target && e.target.closest ? e.target.closest('a[href]') : null;
        if (!a) { return; }
        var href = a.getAttribute('href') || '';
        if (href === '' || href === '#' || href.indexOf('javascript:') === 0) { return; }
        abortSearch();
    });

    // Global Form SPA Interceptor
    document.addEventListener('submit', function (e) {
        if (!e.target || e.target.tagName !== 'FORM') return;
        var form = e.target;
        
        // Exclude specific forms (file uploads, exports, get requests, login)
        if (form.getAttribute('enctype') && form.getAttribute('enctype').includes('multipart/form-data')) return;
        if (form.method && form.method.toLowerCase() === 'get') return;
        if (form.classList.contains('no-ajax') || form.hasAttribute('data-no-ajax')) return;
        if (form.action && (form.action.includes('login.php') || form.action.includes('logout.php'))) return;

        // Ensure we actually capture the submit button that was clicked.
        var btn = e.submitter || form.querySelector('input[type="submit"][name="BTN_SUM"], button[type="submit"], input[type="submit"]');
        
        // Exclude export buttons
        if (btn && btn.value && (btn.value.toLowerCase().includes('export') || btn.value.toLowerCase().includes('download'))) return;

        var missing = firstEmptyRequired(form);
        if (missing) {
            e.preventDefault();
            showSearchHint(form, 'Fill in the required fields and try again.');
            if (typeof missing.focus === 'function') {
                try { missing.focus(); } catch (err) { /* ignore */ }
            }
            return;
        }

        // If it passed all filters, it's a standard data search form! Intercept it for SPA.
        e.preventDefault();
        abortSearch();

        if (btn && !btn.classList.contains('no-loading')) {
            searchBtn = btn;
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
        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfMeta && csrfMeta.content) {
            formData.append('csrf_token', csrfMeta.content);
        }

        searchAbort = new AbortController();
        var thisAbort = searchAbort;

        fetch(form.action || window.location.href, {
            method: form.method || 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfMeta && csrfMeta.content ? csrfMeta.content : ''
            },
            signal: thisAbort.signal
        })
        .then(function(response) {
            return response.text();
        })
        .then(function(html) {
            if (thisAbort.signal.aborted) { return; }
            var tmp = document.createElement('div');
            tmp.innerHTML = html;
            if (tmp.querySelector('.sum-search-card, .sum-search-form')) {
                var empty = tmp.querySelector('.sum-empty-state');
                resultsContainer.innerHTML = empty
                    ? empty.outerHTML
                    : '<div class="sum-empty-state">Fill in the required fields and try again.</div>';
                return;
            }
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
            if (err && err.name === 'AbortError') { return; }
            resultsContainer.innerHTML = '<div class="sum-empty-state">An error occurred while searching.</div>';
            console.error("AJAX Error:", err);
        })
        .finally(function() {
            if (thisAbort.signal.aborted) { return; }
            if (searchAbort === thisAbort) { searchAbort = null; }
            restoreSearchBtn();
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
                if (table.getAttribute('data-no-datatable') === '1') return;

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
        if (usableOptions.length >= 6) {
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
        if (!input.getAttribute('autocomplete')) input.setAttribute('autocomplete', 'off');
        input.setAttribute('readonly', 'readonly');
        input.setAttribute('inputmode', 'none');

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
        var mode = 'days';
        var yearPageStart = null;

        var YEAR_MIN = 1950;
        var YEAR_MAX = new Date().getFullYear() + 10;

        function closePanel() {
            wrap.classList.remove('is-open');
            mode = 'days';
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
            mode = 'days';
            wrap.classList.add('is-open');
            render();
        }

        function setValue(d) {
            input.value = d ? formatYmd(d) : '';
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function render() {
            var selected = parseYmd(input.value);
            var today = new Date();
            var y = view.getFullYear();
            var m = view.getMonth();
            var html = '';

            html += '<div class="sum-dp__head">';
            html += '<button type="button" class="sum-dp__nav" data-nav="-1" aria-label="Previous">&lsaquo;</button>';
            html += '<div class="sum-dp__selects">';
            if (mode === 'days') {
                html += '<button type="button" class="sum-dp__switch" data-mode="months">' + MONTHS[m] + '</button>';
                html += '<button type="button" class="sum-dp__switch" data-mode="years">' + y + '</button>';
            } else if (mode === 'months') {
                html += '<button type="button" class="sum-dp__switch" data-mode="years">' + y + '</button>';
            } else {
                var start = yearPageStart != null ? yearPageStart : (y - (y % 12));
                html += '<span class="sum-dp__range">' + start + ' – ' + Math.min(start + 11, YEAR_MAX) + '</span>';
            }
            html += '</div>';
            html += '<button type="button" class="sum-dp__nav" data-nav="1" aria-label="Next">&rsaquo;</button>';
            html += '</div>';

            if (mode === 'months') {
                html += '<div class="sum-dp__month-grid">';
                MONTHS.forEach(function(name, idx) {
                    var cls = 'sum-dp__pick' + (idx === m ? ' is-selected' : '');
                    html += '<button type="button" class="' + cls + '" data-pick-month="' + idx + '">' + name.slice(0, 3) + '</button>';
                });
                html += '</div>';
            } else if (mode === 'years') {
                if (yearPageStart == null) yearPageStart = y - (y % 12);
                html += '<div class="sum-dp__year-grid">';
                for (var i = 0; i < 12; i++) {
                    var yr = yearPageStart + i;
                    if (yr < YEAR_MIN || yr > YEAR_MAX) {
                        html += '<span class="sum-dp__pick is-empty"></span>';
                        continue;
                    }
                    var cls = 'sum-dp__pick' + (yr === y ? ' is-selected' : '');
                    html += '<button type="button" class="' + cls + '" data-pick-year="' + yr + '">' + yr + '</button>';
                }
                html += '</div>';
            } else {
                var firstDow = new Date(y, m, 1).getDay();
                var daysInMonth = new Date(y, m + 1, 0).getDate();
                html += '<div class="sum-dp__dow">';
                DOW.forEach(function(d) { html += '<span>' + d + '</span>'; });
                html += '</div><div class="sum-dp__grid">';
                var d;
                for (d = 0; d < firstDow; d++) {
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
            }

            html += '<div class="sum-dp__foot">';
            html += '<button type="button" class="sum-dp__today" data-today="1">Today</button>';
            html += '<button type="button" class="sum-dp__clear" data-clear="1">Clear</button>';
            html += '</div>';
            panel.innerHTML = html;
        }

        wrap.addEventListener('mousedown', function(e) {
            e.stopPropagation();
        });
        wrap.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        panel.addEventListener('click', function(e) {
            e.stopPropagation();
            var t = e.target.closest('button');
            if (!t) return;
            e.preventDefault();

            if (t.dataset.mode) {
                mode = t.dataset.mode;
                if (mode === 'years') yearPageStart = view.getFullYear() - (view.getFullYear() % 12);
                render();
                return;
            }
            if (t.dataset.nav) {
                var step = Number(t.dataset.nav);
                if (mode === 'years') {
                    yearPageStart = (yearPageStart != null ? yearPageStart : (view.getFullYear() - (view.getFullYear() % 12))) + (step * 12);
                    if (yearPageStart < YEAR_MIN - 11) yearPageStart = YEAR_MIN - (YEAR_MIN % 12);
                    if (yearPageStart > YEAR_MAX) yearPageStart = YEAR_MAX - (YEAR_MAX % 12);
                    render();
                    return;
                }
                if (mode === 'months') {
                    view = new Date(view.getFullYear() + step, view.getMonth(), 1);
                    render();
                    return;
                }
                view = new Date(view.getFullYear(), view.getMonth() + step, 1);
                render();
                return;
            }
            if (t.dataset.pickMonth != null) {
                view = new Date(view.getFullYear(), Number(t.dataset.pickMonth), 1);
                mode = 'days';
                render();
                return;
            }
            if (t.dataset.pickYear != null) {
                view = new Date(Number(t.dataset.pickYear), view.getMonth(), 1);
                mode = 'months';
                render();
                return;
            }
            if (t.dataset.today) {
                var now = new Date();
                setValue(now);
                closePanel();
                return;
            }
            if (t.dataset.clear) {
                setValue(null);
                closePanel();
                return;
            }
            if (t.dataset.day) {
                setValue(new Date(view.getFullYear(), view.getMonth(), Number(t.dataset.day)));
                closePanel();
            }
        });

        function togglePanel(e) {
            e.preventDefault();
            e.stopPropagation();
            if (wrap.classList.contains('is-open')) closePanel();
            else openPanel();
        }

        triggerBtn.addEventListener('mousedown', togglePanel);
        input.addEventListener('mousedown', function(e) {
            e.preventDefault();
            togglePanel(e);
        });
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                closePanel();
            } else if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                togglePanel(e);
            } else if (e.key.length === 1 || e.key === 'Backspace' || e.key === 'Delete') {
                e.preventDefault();
            }
        });
    }

    document.addEventListener('mousedown', function(e) {
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
            if (!wrap.contains(e.target)) wrap.classList.remove('is-open');
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

    function usersCellExportText(td) {
        if (td.classList.contains('no-export') || td.querySelector('button')) {
            return '';
        }
        return td.textContent.replace(/\s+/g, ' ').trim();
    }

    window.initUserManagement = function (root) {
        root = root || document;
        var table = root.querySelector('#admin_users_table');
        if (!table) {
            return;
        }

        var search = root.querySelector('#users-table-search');
        if (search && !search.dataset.cdatUsersBound) {
            search.dataset.cdatUsersBound = '1';
            search.addEventListener('input', function () {
                var q = this.value.toLowerCase().trim();
                Array.prototype.forEach.call(table.tBodies[0].rows, function (row) {
                    row.style.display = row.textContent.toLowerCase().indexOf(q) !== -1 ? '' : 'none';
                });
            });
        }

        var exportBtn = root.querySelector('#users-export-excel');
        if (exportBtn && !exportBtn.dataset.cdatUsersBound) {
            exportBtn.dataset.cdatUsersBound = '1';
            exportBtn.addEventListener('click', function () {
                var html = '<table border="1"><thead><tr>';
                Array.prototype.forEach.call(table.tHead.rows[0].cells, function (th) {
                    if (th.classList.contains('no-export')) return;
                    html += '<th>' + th.textContent + '</th>';
                });
                html += '</tr></thead><tbody>';
                Array.prototype.forEach.call(table.tBodies[0].rows, function (row) {
                    if (row.style.display === 'none') return;
                    html += '<tr>';
                    Array.prototype.forEach.call(row.cells, function (td) {
                        if (td.classList.contains('no-export')) return;
                        html += '<td>' + usersCellExportText(td) + '</td>';
                    });
                    html += '</tr>';
                });
                html += '</tbody></table>';
                var blob = new Blob([html], { type: 'application/vnd.ms-excel;charset=utf-8' });
                var link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'users_' + Date.now() + '.xls';
                link.click();
                URL.revokeObjectURL(link.href);
            });
        }

        var printBtn = root.querySelector('#users-print');
        if (printBtn && !printBtn.dataset.cdatUsersBound) {
            printBtn.dataset.cdatUsersBound = '1';
            printBtn.addEventListener('click', function () {
                window.print();
            });
        }

        var successToast = root.querySelector('#users-success-toast');
        if (successToast && window.bootstrap && !successToast.dataset.cdatUsersShown) {
            successToast.dataset.cdatUsersShown = '1';
            bootstrap.Toast.getOrCreateInstance(successToast, { delay: 3500, autohide: true }).show();
        }

        var autoOpen = root.querySelector('[data-users-open-modal]');
        if (autoOpen && window.bootstrap) {
            var modalId = autoOpen.getAttribute('data-users-open-modal') === 'edit'
                ? 'editUserModal'
                : 'createUserModal';
            var modalEl = root.querySelector('#' + modalId) || document.getElementById(modalId);
            if (modalEl) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        }
    };

    if (!window._cdatEditUserModalBound) {
        window._cdatEditUserModalBound = true;
        document.addEventListener('show.bs.modal', function (event) {
            if (!event.target || event.target.id !== 'editUserModal') return;
            var btn = event.relatedTarget;
            if (!btn || !btn.classList || !btn.classList.contains('sum-users-edit-btn')) return;
            var modal = event.target;
            var ds = btn.dataset;
            var id = modal.querySelector('#edit_user_id');
            var hiddenUser = modal.querySelector('#edit_username_hidden');
            var displayUser = modal.querySelector('#edit_username_display');
            var fullname = modal.querySelector('#edit_fullname');
            var role = modal.querySelector('#edit_role');
            var password = modal.querySelector('#edit_password');
            if (id) id.value = ds.userId || '';
            if (hiddenUser) hiddenUser.value = ds.username || '';
            if (displayUser) displayUser.value = ds.username || '';
            if (fullname) fullname.value = ds.fullname || '';
            if (role) role.value = ds.role || 'user';
            if (password) password.value = '';
        });
    }

    // Call it on initial page load
    window.initGlobalDatatables();
    window.initSearchableSelects();
    window.initDatePickers();
    window.initFileUploads();
    window.initUserManagement();

}());

(function () {
    'use strict';
    var origFetch = window.fetch;
    if (typeof origFetch !== 'function') {
        return;
    }
    window.fetch = function (url, opts) {
        opts = opts || {};
        var meta = document.querySelector('meta[name="csrf-token"]');
        var token = meta && meta.content ? meta.content : '';
        if (token && opts.body instanceof FormData) {
            if (!opts.body.has('csrf_token')) {
                opts.body.append('csrf_token', token);
            }
        }
        if (token) {
            var headers = new Headers(opts.headers || {});
            if (!headers.has('X-CSRF-TOKEN')) {
                headers.set('X-CSRF-TOKEN', token);
            }
            opts.headers = headers;
        }
        return origFetch.call(this, url, opts);
    };
}());
