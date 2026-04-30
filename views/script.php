<!-- <script>
  (() => {
    // ===== AUTH (Update SSO Integration) =====
    window.NavAuth = window.NavAuth || {};
    const NA = window.NavAuth;

    // Base PATH saja: '' (root) atau '/report-dpk'
    function getBasePath() {
      const baseTag = document.querySelector('base')?.getAttribute('href');
      if (baseTag) {
        const u = new URL(baseTag, location.origin);
        return u.pathname.replace(/\/+$/, '') || '';
      }
      if (window.BASE_APP) {
        const u = new URL(window.BASE_APP, location.origin);
        return u.pathname.replace(/\/+$/, '') || '';
      }
      if (location.pathname === '/report-dpk' || location.pathname.startsWith('/report-dpk/')) {
        return '/report-dpk';
      }
      return '';
    }

    function getBaseApp() {
      return location.origin + getBasePath();
    }

    function absUrl(path = '/') {
      const base = getBasePath();
      const p = ('/' + String(path)).replace(/\/{2,}/g, '/'); 
      return base + p;
    }

    const BASE_APP = window.BASE_APP || getBaseApp();
    
    // --- 1. UPDATE URL API KE SSO ---
    const isLocal = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
    const API_SSO_BASE = isLocal ? 'http://localhost/rest_api_sso' : 'https://apisso.bkkjateng.co.id';
    const API_WHOAMI = `${API_SSO_BASE}/api/auth/whoami`;

    const TOKEN_KEY  = 'dpk_token';
    const USER_KEY   = 'dpk_user';

    NA.getToken  = NA.getToken  || function(){ return localStorage.getItem(TOKEN_KEY) || ''; };
    NA.setUser   = NA.setUser   || function(u){ localStorage.setItem(USER_KEY, JSON.stringify(u)); };
    NA.getUserLS = NA.getUserLS || function(){ try {return JSON.parse(localStorage.getItem(USER_KEY)||'null');}catch{return null;} };
    
    // --- 2. UPDATE FUNGSI CLEAR AUTH (Hapus Cookie Juga) ---
    NA.clearAuth = NA.clearAuth || function(){ 
        localStorage.removeItem(TOKEN_KEY); 
        localStorage.removeItem(USER_KEY); 
        // Hapus cookie SSO
        document.cookie = "sso_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        if(!isLocal) {
            document.cookie = "sso_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=.bkkjateng.co.id;";
        }
    };
    NA.goLogin   = NA.goLogin   || function(){ location.href = `${BASE_APP}/login`; };

    if (!Object.getOwnPropertyDescriptor(window,'AUTH_TOKEN')) {
      Object.defineProperty(window, 'AUTH_TOKEN', { get: () => NA.getToken() });
    }
    window.getUser       = window.getUser       || (() => window.__USER || NA.getUserLS());
    window.getEmployeeId = window.getEmployeeId || (() => (window.getUser()?.employee_id ?? null));
    
    // --- 3. UPDATE API FETCH GLOBAL (Tambah Bearer) ---
    if (!window.apiFetch) {
      window.apiFetch = async (url, options = {}) => {
        const token = NA.getToken();
        if (!token) { NA.goLogin(); throw new Error('Unauthorized'); }
        const headers = Object.assign({}, options.headers || {});
        
        // Pastikan ada Bearer
        headers['Authorization'] = token.startsWith('Bearer') ? token : `Bearer ${token}`;
        
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
        
        // --- 4. PERBAIKAN CEK WHOAMI (Pakai Bearer) ---
        try {
          const r = await fetch(API_WHOAMI, { 
              headers: { 'Authorization': token.startsWith('Bearer') ? token : `Bearer ${token}` } 
          });
          
          if (!r.ok) throw new Error(`HTTP ${r.status}`);
          const j = await r.json();
          if (j?.status !== 200 || !j?.data) throw new Error('Data invalid');
          
          // Sisipkan ulang logik role 'dev' (Jaga-jaga localStorage hilang/ketimpa)
          user = j.data; 
          if (user.job_position === "Divisi Operasional" || user.unit_kerja === "Divisi Operasional") {
              user.role = "dev";
          } else {
              user.role = "user"; 
          }
          
          NA.setUser(user); 
          window.__USER = user;
        } catch (err) {
          console.error("Gagal verifikasi token dari Dashboard:", err);
          NA.clearAuth(); 
          if (!onLogin) NA.goLogin(); 
          return;
        }

        // --- Render UI Data User ---
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
</script> -->