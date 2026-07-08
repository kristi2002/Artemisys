$(document).ready(function () {

    // ===== SIDEBAR TOGGLE (mobile) =====
    $('#sidebarToggle').on('click', function () {
        $('#sidebar').toggleClass('show');
        if ($('#sidebar').hasClass('show')) {
            $('<div class="sidebar-overlay show"></div>').appendTo('body').on('click', function () {
                $('#sidebar').removeClass('show');
                $(this).remove();
            });
        } else {
            $('.sidebar-overlay').remove();
        }
    });

    // ===== SIDEBAR SUBMENU TOGGLE =====
    $('.submenu-toggle').on('click', function (e) {
        e.preventDefault();
        $(this).closest('.has-submenu').toggleClass('open');
    });

});


/* =========================================================================
 * PJAX — navigazione senza refresh.
 * Sostituisce solo #pjax-main; la sidebar (fixed) non viene ricaricata,
 * quindi mantiene la sua posizione di scroll e non c'è "flash" di pagina.
 * ========================================================================= */
(function () {
    var MAIN = 'pjax-main';
    if (!document.getElementById(MAIN)) return; // pagine senza layout (login) → nav normale

    // Base delle route dell'app (es. "/index.php/") per capire cosa intercettare
    var BASE_PATH = (function () {
        var b = (typeof BASE_URL !== 'undefined') ? BASE_URL : '/index.php/';
        try { return new URL(b, location.href).pathname; }
        catch (e) { return '/index.php/'; }
    })();

    // ---- Barra di caricamento sottile in alto ----
    var bar = document.createElement('div');
    bar.style.cssText = 'position:fixed;top:0;left:0;height:3px;width:0;z-index:9999;' +
        'background:#1e40af;transition:width .2s ease,opacity .3s ease;opacity:0;';
    document.addEventListener('DOMContentLoaded', function () { document.body.appendChild(bar); });
    var barTimer;
    function barStart() { clearInterval(barTimer); bar.style.opacity = '1'; var w = 8;
        bar.style.width = '8%'; barTimer = setInterval(function () { w = Math.min(w + Math.random() * 10, 90); bar.style.width = w + '%'; }, 200); }
    function barDone() { clearInterval(barTimer); bar.style.width = '100%';
        setTimeout(function () { bar.style.opacity = '0'; setTimeout(function () { bar.style.width = '0'; }, 300); }, 200); }

    // ---- Un link è "interno" e navigabile via PJAX? ----
    function pjaxable(a) {
        if (!a || !a.getAttribute) return false;
        var href = a.getAttribute('href');
        if (!href) return false;
        if (a.target === '_blank' || a.hasAttribute('download') || a.hasAttribute('data-no-pjax')) return false;
        if (/^(#|mailto:|tel:|javascript:)/i.test(href)) return false;
        var u;
        try { u = new URL(a.href, location.href); } catch (e) { return false; }
        if (u.origin !== location.origin) return false;
        // solo route dell'app; escludi il logout (cambia layout/sessione)
        if (u.pathname.indexOf('auth/logout') !== -1) return false;
        return u.pathname.indexOf(BASE_PATH) === 0 || u.pathname.indexOf('/index.php') !== -1;
    }

    // ---- Esegue gli <script> presenti nel nuovo contenuto ----
    // (gli script inseriti via innerHTML NON vengono eseguiti dal browser;
    //  inoltre facciamo in modo che i listener 'DOMContentLoaded' partano lo stesso)
    function runScripts(root) {
        var origAdd = document.addEventListener.bind(document);
        var deferred = [];
        document.addEventListener = function (type, cb, opts) {
            if (type === 'DOMContentLoaded') { deferred.push(cb); return; }
            return origAdd(type, cb, opts);
        };
        var scripts = Array.prototype.slice.call(root.querySelectorAll('script'));
        scripts.forEach(function (old) {
            var s = document.createElement('script');
            if (old.type) s.type = old.type;
            if (old.src) s.src = old.src; else s.textContent = old.textContent;
            old.parentNode.replaceChild(s, old);
        });
        document.addEventListener = origAdd; // ripristina
        deferred.forEach(function (cb) {
            try { (typeof cb === 'function') ? cb() : (cb && cb.handleEvent && cb.handleEvent()); }
            catch (e) { console.error(e); }
        });
    }

    // ---- Carica una URL e sostituisce il contenuto ----
    var reqId = 0;
    function load(url, push) {
        var myId = ++reqId;
        barStart();
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                // se il server ha reindirizzato (es. login), naviga davvero
                if (r.redirected && r.url && new URL(r.url).pathname.indexOf('auth/login') !== -1) {
                    window.location = r.url; throw 'redirect';
                }
                return r.text();
            })
            .then(function (html) {
                if (myId !== reqId) return; // una nav più recente ha vinto
                var doc = new DOMParser().parseFromString(html, 'text/html');
                var next = doc.getElementById(MAIN);
                if (!next) { window.location = url; return; } // layout diverso → nav piena
                var cur = document.getElementById(MAIN);
                cur.innerHTML = next.innerHTML;

                // titolo
                var t = doc.querySelector('title'); if (t) document.title = t.textContent;

                // stato "active" nella sidebar (la sidebar non viene ricaricata)
                var act = doc.querySelector('#sidebar .sidebar-menu a.active');
                document.querySelectorAll('#sidebar .sidebar-menu a.active')
                    .forEach(function (a) { a.classList.remove('active'); });
                if (act) {
                    var h = act.getAttribute('href');
                    var m = document.querySelector('#sidebar .sidebar-menu a[href="' + (window.CSS && CSS.escape ? CSS.escape(h) : h) + '"]');
                    if (m) m.classList.add('active');
                }

                runScripts(cur);
                if (push) history.pushState({ pjax: true }, '', url);
                window.scrollTo({ top: 0, left: 0, behavior: 'auto' });

                // chiudi la sidebar mobile se aperta
                var sb = document.getElementById('sidebar');
                if (sb) sb.classList.remove('show');
                var ov = document.querySelector('.sidebar-overlay'); if (ov) ov.remove();

                // pulizia di eventuali modali/backdrop rimasti aperti
                document.querySelectorAll('.modal-backdrop').forEach(function (b) { b.remove(); });
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');

                barDone();
            })
            .catch(function (e) {
                if (e === 'redirect') return;
                clearInterval(barTimer); bar.style.opacity = '0';
                window.location = url; // fallback: navigazione normale
            });
    }

    // ---- Intercetta i click sui link interni ----
    document.addEventListener('click', function (e) {
        if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        var a = e.target.closest && e.target.closest('a');
        if (!pjaxable(a)) return;
        e.preventDefault();
        var url = a.href;
        if (url === location.href) { load(url, false); return; } // stessa pagina → ricarica contenuto
        load(url, true);
    });

    // ---- Back/forward del browser ----
    window.addEventListener('popstate', function () { load(location.href, false); });

    // marca lo stato iniziale così il primo "back" funziona
    history.replaceState({ pjax: true }, '', location.href);
})();
