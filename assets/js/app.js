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

    // Legacy pages announce things in <marquee>. Render the text as a static
    // notice instead -- scrolling text is the loudest "old" signal on the page.
    Array.prototype.forEach.call(document.querySelectorAll('.content marquee'), function (m) {
        var d = document.createElement('div');
        d.className = 'notice';
        d.textContent = (m.textContent || '').replace(/\s+/g, ' ').replace(/^[\s*]+|[\s*]+$/g, '');
        if (d.textContent) { m.parentNode.replaceChild(d, m); } else { m.remove(); }
    });
}());
