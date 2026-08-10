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
}());
