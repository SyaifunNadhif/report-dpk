<!-- <script src="../path/to/flowbite/dist/flowbite.min.js"></script> -->

<!-- Auth -->

<script>

  (() => {
    // ===== AUTH (tanpa perubahan) =====
    window.NavAuth = window.NavAuth || {};
    const NA = window.NavAuth;

    // Base PATH saja: '' (root) atau '/e-pipelane'
    function getBasePath() {
      // 1) Hormati <base href="...">
      const baseTag = document.querySelector('base')?.getAttribute('href');
      if (baseTag) {
        const u = new URL(baseTag, location.origin);
        return u.pathname.replace(/\/+$/, '') || '';
      }

      // 2) Hormati window.BASE_APP kalau ada
      if (window.BASE_APP) {
        const u = new URL(window.BASE_APP, location.origin);
        return u.pathname.replace(/\/+$/, '') || '';

      }

      // 3) Deteksi prefix yang kita kenal (ubah/daftarkan jika perlu)
      //    Kalau URL sekarang berada di bawah /e-pipelane → pakai itu, selain itu anggap root.
      if (location.pathname === '/e-pipelane' || location.pathname.startsWith('/e-pipelane/')) {
        return '/e-pipelane';
      }

      // Default: root

      return '';

    }

    // Base URL lengkap (origin + base path)

    function getBaseApp() {

      return location.origin + getBasePath();

    }

    // Join helper: pastikan selalu absolute (diawali '/'), auto-prepend base path

    function absUrl(path = '/') {

      const base = getBasePath();

      const p = ('/' + String(path)).replace(/\/{2,}/g, '/'); // pastikan leading '/', kompres //

      return base + p;

    }

    const BASE_APP = window.BASE_APP || getBaseApp();
    const API_WHOAMI = './api/auth/whoami';
    const TOKEN_KEY  = 'dpk_token';
    const USER_KEY   = 'dpk_user';

    NA.getToken  = NA.getToken  || function(){ return localStorage.getItem(TOKEN_KEY) || ''; };
    NA.setUser   = NA.setUser   || function(u){ localStorage.setItem(USER_KEY, JSON.stringify(u)); };
    NA.getUserLS = NA.getUserLS || function(){ try {return JSON.parse(localStorage.getItem(USER_KEY)||'null');}catch{return null;} };
    NA.clearAuth = NA.clearAuth || function(){ localStorage.removeItem(TOKEN_KEY); localStorage.removeItem(USER_KEY); };
    NA.goLogin   = NA.goLogin   || function(){ location.href = `${BASE_APP}/login`; };

    if (!Object.getOwnPropertyDescriptor(window,'AUTH_TOKEN')) {

      Object.defineProperty(window, 'AUTH_TOKEN', { get: () => NA.getToken() });

    }
    window.getUser       = window.getUser       || (() => window.__USER || NA.getUserLS());
    window.getEmployeeId = window.getEmployeeId || (() => (window.getUser()?.employee_id ?? null));
    if (!window.apiFetch) {
      window.apiFetch = async (url, options = {}) => {
        const token = NA.getToken();
        if (!token) { NA.goLogin(); throw new Error('Unauthorized'); }
        const headers = Object.assign({}, options.headers || {});
        headers['Authorization'] = token;
        const u = window.getUser();
        if (u?.employee_id) headers['X-Employee-Id'] = u.employee_id;
        if (options.body && !headers['Content-Type']) headers['Content-Type'] = 'application/json';
        const res = await fetch(url, { ...options, headers });
        if (res.status === 401 || res.status === 403) { NA.clearAuth(); NA.goLogin(); }
        return res;
      };
    }

    if (!window.__NAVBAR_AUTH_INIT__) {
      window.__NAVBAR_AUTH_INIT__ = true;
      (async () => {
        const token = NA.getToken();
        const onLogin = location.pathname.endsWith('/login') || location.search.includes('url=login');
        if (!token) { if (!onLogin) NA.goLogin(); return; }
        let user = NA.getUserLS();
        try {
          const r = await fetch(API_WHOAMI, { headers: { 'Authorization': token } });
          const j = await r.json();
          if (!r.ok || j?.status !== 200 || !j?.data) throw 0;
          user = j.data; NA.setUser(user); window.__USER = user;
        } catch {
          NA.clearAuth(); if (!onLogin) NA.goLogin(); return;
        }

        document.getElementById('navUserName')?.replaceChildren(document.createTextNode(user.full_name || '-'));
        document.getElementById('navBranch')?.replaceChildren(document.createTextNode(user.branch_name || '-'));

        const NAV = document.getElementById('mainNavbar');
        if (NAV) {
          NAV.dataset.userId = user.id ?? '';
          NAV.dataset.employeeId = user.employee_id ?? '';
          NAV.dataset.kode = user.kode ?? '';

        }

        const acc = user.account_handle || user.handle || user.username || user.email || user.employee_id || user.kode || '-';
        const accEl = document.getElementById('accHandle'); if (accEl) accEl.textContent = acc;

        ['linkLogout','linkLogoutDesk'].forEach(id=>{
          const el = document.getElementById(id);
          if (el) el.addEventListener('click', e => { e.preventDefault(); NA.clearAuth(); NA.goLogin(); });
        });

        const slug = location.pathname.split('/').pop() || new URLSearchParams(location.search).get('url') || 'home';
        document.querySelectorAll('#navbar-default a[href]').forEach(a => {
          const href = a.getAttribute('href'); if (href && href === slug) a.classList.add('nb-active');
        });
      })();
    }
  })();

</script>



