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

    function readOpen() {
        try { return JSON.parse(sessionStorage.getItem(KEY)) || []; }
        catch (e) { return []; }
    }

    function writeOpen(list) {
        try { sessionStorage.setItem(KEY, JSON.stringify(list)); }
        catch (e) { /* private mode: not worth failing over */ }
    }

    function labelOf(group) {
        var b = group.querySelector('.nav-parent span');
        return b ? b.textContent.trim() : '';
    }

    var groups = Array.prototype.slice.call(document.querySelectorAll('.nav-group'));

    // Restore previous state, but never close a group holding the current page:
    // the server already opened that one and hiding it would strand the user.
    var open = readOpen();
    groups.forEach(function (g) {
        if (g.classList.contains('is-open')) { return; }
        if (open.indexOf(labelOf(g)) !== -1) {
            g.classList.add('is-open');
            g.querySelector('.nav-parent').setAttribute('aria-expanded', 'true');
        }
    });

    groups.forEach(function (g) {
        var btn = g.querySelector('.nav-parent');
        if (!btn) { return; }
        btn.addEventListener('click', function () {
            var nowOpen = !g.classList.contains('is-open');
            g.classList.toggle('is-open', nowOpen);
            btn.setAttribute('aria-expanded', nowOpen ? 'true' : 'false');

            var list = readOpen();
            var name = labelOf(g);
            var at = list.indexOf(name);
            if (nowOpen && at === -1) { list.push(name); }
            if (!nowOpen && at !== -1) { list.splice(at, 1); }
            writeOpen(list);
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

    // Legacy pages announce things in <marquee>. Render the text as a static
    // notice instead -- scrolling text is the loudest "old" signal on the page.
    Array.prototype.forEach.call(document.querySelectorAll('.content marquee'), function (m) {
        var d = document.createElement('div');
        d.className = 'notice';
        d.textContent = (m.textContent || '').replace(/\s+/g, ' ').replace(/^[\s*]+|[\s*]+$/g, '');
        if (d.textContent) { m.parentNode.replaceChild(d, m); } else { m.remove(); }
    });
}());
