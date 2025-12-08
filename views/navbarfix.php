<!-- NAVBAR -->

<nav id="mainNavbar" class="sticky top-0 bg-white/90 backdrop-blur border-b border-slate-200 shadow-sm">

  <div class="max-w-screen-xl mx-auto px-4">

    <!-- Top bar -->

    <div class="h-14 flex items-center justify-between">

      <a href="home" class="flex items-center gap-2">

        <img src="./img/logodpk.png" class="h-8 w-8 object-contain" alt="Logo">

        <span class="text-slate-800 text-lg font-semibold">DPK Report</span>

      </a>



      <div class="flex items-center gap-3">

        <div class="hidden sm:flex flex-col leading-tight text-right select-none">

          <span id="navUserName" class="text-slate-800 text-sm font-medium">—</span>

          <span id="navBranch" class="text-slate-500 text-[11px]">—</span>

        </div>



        <!-- Avatar -->

        <button id="dropdownProfileButton" data-dropdown="dropdownProfile" aria-haspopup="menu" aria-expanded="false"

                class="inline-flex items-center justify-center rounded-full ring-1 ring-slate-200 hover:ring-slate-300 transition">

          <img class="w-9 h-9 rounded-full object-cover" src="../img/profile.jpg" alt="Profile">

        </button>



        <!-- Hamburger (mobile) -->

        <button id="hamburger" type="button"

                class="inline-flex md:hidden items-center justify-center p-2 w-10 h-10 rounded-lg text-slate-700 hover:bg-slate-100 focus:outline-none">

          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">

            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"

                  d="M4 6h16M4 12h16M4 18h16"/>

          </svg>

        </button>

      </div>

    </div>



    <!-- Menu -->

    <div id="navbar-default" class="nb-container">

      <ul class="nb-row">

        <li class="nb-li"><a href="home" class="nb-link">Dashboard</a></li>



        <!-- NPL -->

        <li class="nb-li">

          <button data-dropdown="dropdownNPL" class="nb-parent">

            <span>NPL</span>

            <svg class="nb-caret" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">

              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.586l3.71-4.354a.75.75 0 111.14.976l-4.25 5a.75.75 0 01-1.14 0l-4.25-5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>

            </svg>

          </button>

          <div id="dropdownNPL" class="dropdown-panel">

            <ul class="nb-list">

              <li><a href="npl" class="nb-item">NPL Konsolidasi</a></li>

              <li><a href="perbandingan_npl" class="nb-item">Perbandingan NPL</a></li>

              <li><a href="recovery_npl" class="nb-item">Recovery NPL</a></li>

              <li><a href="flow_par" class="nb-item">Flow Par</a></li>

              <li><a href="flow_50_besar" class="nb-item">50 Besar Flow Par</a></li>

              <!-- <li><a href="npl_25_besar" class="nb-item">25 Besar NPL</a></li> -->

              <li><a href="potensi_npl" class="nb-item">Potensi NPL</a></li>

            </ul>

          </div>

        </li>



        <!-- PH -->

        <li class="nb-li">

          <button data-dropdown="dropdownPH" class="nb-parent">

            <span>PH</span>

            <svg class="nb-caret" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">

              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.586l3.71-4.354a.75.75 0 111.14.976l-4.25 5a.75.75 0 01-1.14 0l-4.25-5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>

            </svg>

          </button>

          <div id="dropdownPH" class="dropdown-panel">

            <ul class="nb-list">

              <li><a href="recovery_ph" class="nb-item">Recovery PH</a></li>

              <li><a href="bucket_saldo_ph" class="nb-item">Saldo PH (Bucket)</a></li>

              <li><a href="#" class="nb-item">Debitur PH LGD</a></li>

            </ul>

          </div>

        </li>



        <!-- Collection -->

        <li class="nb-li">

          <button data-dropdown="dropdownPenagihan" class="nb-parent">

            <span>Collection</span>

            <svg class="nb-caret" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">

              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.586l3.71-4.354a.75.75 0 111.14.976l-4.25 5a.75.75 0 01-1.14 0l-4.25-5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>

            </svg>

          </button>

          <div id="dropdownPenagihan" class="dropdown-panel">

            <ul class="nb-list">

              <!-- <li><a href="maping_ao_remedial" class="nb-item">Maping AO Remedial</a></li> -->

              <li><a href="maping_bucket" class="nb-item">Maping Backet</a></li>

              <li><a href="ao_remedial" class="nb-item">AO Remedial (dev)</a></li>

            </ul>

          </div>

        </li>



        <!-- Kredit -->

        <li class="nb-li">

          <button data-dropdown="dropdownCKPN" class="nb-parent">

            <span>Kredit</span>

            <svg class="nb-caret" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">

              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.586l3.71-4.354a.75.75 0 111.14.976l-4.25 5a.75.75 0 01-1.14 0l-4.25-5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>

            </svg>

          </button>

          <div id="dropdownCKPN" class="dropdown-panel">

            <ul class="nb-list">

              <li><a href="realisasi_kredit" class="nb-item">Realisasi Kredit</a></li>

              <li><a href="ckpn" class="nb-item">CKPN</a></li>

              <li><a href="ckpn_produk" class="nb-item">CKPN Produk</a></li>

              <!-- <li><a href="dashboard" class="nb-item">Dasboard</a></li> -->

              <li><a href="plan_ckpn" class="nb-item">Plan CKPN (dev)</a></li>

            </ul>

          </div>

        </li>



        <!-- Logout (mobile) -->

        <li class="nb-li md:hidden"><a href="#" id="linkLogout" class="nb-link">Logout</a></li>

      </ul>



      <!-- Profile dropdown -->

      <div id="dropdownProfile" class="dropdown-panel dropdown-profile" role="menu" aria-labelledby="dropdownProfileButton">

        <a href="account_handle" class="block px-4 py-3 hover:bg-slate-50">

          <div class="text-xs text-slate-500">Account handle</div>

          <div id="accHandle" class="text-sm font-semibold text-slate-800 underline decoration-dotted">—</div>

        </a>

        <hr class="my-1">

        <ul class="py-1 text-sm text-slate-700">

          <li><a href="#" id="linkLogoutDesk" class="block px-4 py-2 hover:bg-slate-50">Logout</a></li>

        </ul>

      </div>



    </div>

  </div>

  <div class="h-[2px] w-full bg-gradient-to-r from-blue-600 via-sky-500 to-orange-500"></div>

</nav>



<style>

/* ===== Layering ===== */

#mainNavbar{ z-index:2200 !important; position:sticky; top:0; overflow:visible; isolation:isolate; }

.nb-container{ position:relative; z-index:2201; overflow:visible !important; display:none; }

@media (min-width:768px){ .nb-container{ display:block; } }   /* desktop terlihat */

.nb-container.open{ display:block; }                            /* mobile ketika klik hamburger */



/* ===== Baris menu (horizontal) ===== */

.nb-row{

  display:flex; flex-direction:row; align-items:center; gap:.25rem;

  white-space:nowrap; padding:.4rem 0;

  overflow-x:auto; -webkit-overflow-scrolling:touch; scrollbar-width:thin;

}

.nb-row::-webkit-scrollbar{ height:6px; }

.nb-row::-webkit-scrollbar-thumb{ background:#cbd5e1; border-radius:999px; }

.nb-li{ flex:0 0 auto; }



/* Items */

.nb-link, .nb-parent{

  display:inline-flex; align-items:center; gap:.25rem;

  padding:.45rem .7rem; border-radius:.5rem; color:#334155;

}

.nb-link:hover, .nb-parent:hover{ color:#2563eb; background:#f8fafc; }

.nb-active{ color:#1d4ed8; font-weight:600; border-bottom:2px solid #2563eb; }

.nb-caret{ margin-left:.15rem; transition:transform .15s ease; }

.rot180{ transform:rotate(180deg); }



/* ===== Dropdown panel: SAME for desktop & mobile (fixed floating) ===== */

.dropdown-panel{

  display:none; position:fixed; z-index:2300; background:#fff;

  border:1px solid #e2e8f0; border-radius:.75rem;

  box-shadow: 0 12px 28px rgba(15,23,42,.12); padding:.4rem 0;

  min-width:14rem; max-width:min(90vw, 22rem);

  max-height: calc(100vh - var(--navH,56px) - 16px); overflow:auto;

}

.dropdown-panel.is-open{ display:block; }

.nb-list{ padding:.25rem 0; font-size:.9rem; color:#334155; }

.nb-item{ display:block; padding:.5rem 1rem; border-radius:.375rem; }

.nb-item:hover{ background:#f8fafc; }



/* Compact di mobile & landscape */

@media (max-width:767px){

  .nb-row{ gap:.15rem; }

  .nb-link, .nb-parent{ padding:.35rem .55rem; font-size:13px; }

  .nb-item{ padding:.4rem .75rem; }

}

@media (max-width:767px) and (orientation:landscape){

  .nb-row{ padding:.25rem 0; }

}

</style>



<!-- <script>

/* ======= MENU & DROPDOWN unified (desktop = mobile) ======= */

(function(){

  const navbar   = document.getElementById('mainNavbar');

  const container= document.getElementById('navbar-default');

  const hamburger= document.getElementById('hamburger');

  const btns     = document.querySelectorAll('[data-dropdown]');

  const panels   = document.querySelectorAll('.dropdown-panel');



  function setNavH(){

    document.documentElement.style.setProperty('--navH', (navbar?.offsetHeight || 56)+'px');

  }

  window.addEventListener('load', setNavH);

  window.addEventListener('resize', setNavH);



  // Mobile: show/hide horizontal row via hamburger

  if(hamburger && container){

    hamburger.addEventListener('click', ()=>{

      container.classList.toggle('open');

      closeAll();

    });

  }



  function closeAll(exceptId){

    panels.forEach(p=>{

      if(!exceptId || p.id !== exceptId){

        p.classList.remove('is-open');

        p.style.left = ''; p.style.top = ''; p.style.right = '';

      }

    });

    btns.forEach(b=>{

      const id = b.getAttribute('data-dropdown');

      if(!exceptId || id !== exceptId){

        b.setAttribute('aria-expanded','false');

        b.querySelector('.nb-caret')?.classList.remove('rot180');

      }

    });

  }



  // POSISI dropdown: fixed di bawah navbar, horizontal mengikuti tombol, aman dari tepi layar

  function placePanel(btn, panel){

    const btnRect = btn.getBoundingClientRect();

    const navRect = navbar?.getBoundingClientRect();

    const margin  = 8;

    const top     = (navRect?.bottom || 56) + 6;



    // sementara tampil offscreen untuk dapatkan width asli

    panel.style.visibility = 'hidden';

    panel.style.display    = 'block';

    panel.classList.add('is-open');



    const panelRect = panel.getBoundingClientRect();

    const panelW    = Math.min(Math.max(panelRect.width, 224), Math.min(window.innerWidth*0.9, 352)); // 14rem..22rem

    let left        = btnRect.left; // mulai dari kiri tombol

    // clamp supaya tidak keluar kanan/kiri

    left = Math.max(margin, Math.min(left, window.innerWidth - panelW - margin));



    // apply

    panel.style.left = left + 'px';

    panel.style.top  = top  + 'px';

    panel.style.right= 'auto';

    panel.style.minWidth = panelW + 'px';



    // restore visibility

    panel.style.visibility = '';

  }



  btns.forEach(btn=>{

    btn.addEventListener('click', (e)=>{

      e.preventDefault();

      const id = btn.getAttribute('data-dropdown');

      const panel = document.getElementById(id);

      if(!panel) return;



      const willOpen = !panel.classList.contains('is-open');

      closeAll(id);

      if(willOpen){

        placePanel(btn, panel);

      }else{

        panel.classList.remove('is-open');

      }

      btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');

      btn.querySelector('.nb-caret')?.classList.toggle('rot180', willOpen);

    });

  });



  // close outside & ESC

  document.addEventListener('click', (e)=>{

    const isBtn   = e.target.closest('[data-dropdown]');

    const isPanel = e.target.closest('.dropdown-panel');

    const isHamb  = e.target.closest('#hamburger');

    if(!isBtn && !isPanel && !isHamb) closeAll();

  });

  document.addEventListener('keydown', (e)=>{ if(e.key === 'Escape') closeAll(); });



  // Reposition saat resize/scroll

  window.addEventListener('resize', ()=> {

    const openBtn = document.querySelector('[data-dropdown][aria-expanded="true"]');

    const openId  = openBtn?.getAttribute('data-dropdown');

    const openPan = openId ? document.getElementById(openId) : null;

    if(openBtn && openPan) placePanel(openBtn, openPan);

  });

  window.addEventListener('scroll', ()=> {

    const openBtn = document.querySelector('[data-dropdown][aria-expanded="true"]');

    const openId  = openBtn?.getAttribute('data-dropdown');

    const openPan = openId ? document.getElementById(openId) : null;

    if(openBtn && openPan) placePanel(openBtn, openPan);

  });



  // default: desktop terlihat, mobile hidden

  if (window.innerWidth >= 768) container.classList.add('open');

})();

</script> -->



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



<script>

/* ======= MENU & DROPDOWN (fix toggle + close others) ======= */

(function(){

  const navbar   = document.getElementById('mainNavbar');

  const container= document.getElementById('navbar-default');

  const hamburger= document.getElementById('hamburger');

  const btns     = document.querySelectorAll('[data-dropdown]');

  const panels   = document.querySelectorAll('.dropdown-panel');



  function setNavH(){

    document.documentElement.style.setProperty('--navH', (navbar?.offsetHeight || 56)+'px');

  }

  window.addEventListener('load', setNavH);

  window.addEventListener('resize', setNavH);



  // Mobile: show/hide horizontal row via hamburger

  if(hamburger && container){

    hamburger.addEventListener('click', ()=>{

      container.classList.toggle('open');

      closeAll();

    });

  }



  function resetInline(p){

    p.style.left = ''; p.style.top = ''; p.style.right = '';

    p.style.minWidth = ''; p.style.display = ''; p.style.visibility = '';

  }



  function closeAll(exceptId){

    panels.forEach(p=>{

      if(!exceptId || p.id !== exceptId){

        p.classList.remove('is-open');

        resetInline(p);

      }

    });

    btns.forEach(b=>{

      const id = b.getAttribute('data-dropdown');

      if(!exceptId || id !== exceptId){

        b.setAttribute('aria-expanded','false');

        b.querySelector('.nb-caret')?.classList.remove('rot180');

      }

    });

  }



  // Posisi fixed di bawah navbar, nempel tombol & aman dari tepi layar

  function placePanel(btn, panel){

    const btnRect = btn.getBoundingClientRect();

    const navRect = navbar?.getBoundingClientRect();

    const margin  = 8;

    const top     = (navRect?.bottom || 56) + 6;



    // Ukur lebar natural TANPA meninggalkan display:block

    const prevDisp = panel.style.display;

    const prevVis  = panel.style.visibility;

    panel.style.visibility = 'hidden';

    panel.style.display    = 'block';

    const measuredW = panel.offsetWidth || 0;

    panel.style.display    = prevDisp || '';

    panel.style.visibility = prevVis  || '';



    const panelW = Math.min(Math.max(measuredW || 224, 224), Math.min(window.innerWidth*0.9, 352)); // 14rem..22rem

    let left = btnRect.left;

    left = Math.max(margin, Math.min(left, window.innerWidth - panelW - margin));



    panel.style.left = left + 'px';

    panel.style.top  = top  + 'px';

    panel.style.minWidth = panelW + 'px';

  }



  btns.forEach(btn=>{

    btn.addEventListener('click', (e)=>{

      e.preventDefault();

      const id = btn.getAttribute('data-dropdown');

      const panel = document.getElementById(id);

      if(!panel) return;



      const isOpen = panel.classList.contains('is-open');



      // Toggle: kalau sudah terbuka → tutup

      if (isOpen){

        panel.classList.remove('is-open');

        resetInline(panel);

        btn.setAttribute('aria-expanded','false');

        btn.querySelector('.nb-caret')?.classList.remove('rot180');

        return;

      }



      // Buka: tutup yang lain dulu, lalu posisikan & buka

      closeAll(id);

      placePanel(btn, panel);

      panel.classList.add('is-open');

      btn.setAttribute('aria-expanded','true');

      btn.querySelector('.nb-caret')?.classList.add('rot180');

    });

  });



  // Close outside & ESC

  document.addEventListener('click', (e)=>{

    const isBtn   = e.target.closest('[data-dropdown]');

    const isPanel = e.target.closest('.dropdown-panel');

    const isHamb  = e.target.closest('#hamburger');

    if(!isBtn && !isPanel && !isHamb) closeAll();

  });

  document.addEventListener('keydown', (e)=>{ if(e.key === 'Escape') closeAll(); });



  // Reposition saat resize/scroll agar panel tetap nempel tombol

  function repositionOpen(){

    const openBtn = document.querySelector('[data-dropdown][aria-expanded="true"]');

    const openId  = openBtn?.getAttribute('data-dropdown');

    const openPan = openId ? document.getElementById(openId) : null;

    if(openBtn && openPan && openPan.classList.contains('is-open')) placePanel(openBtn, openPan);

  }

  window.addEventListener('resize', repositionOpen);

  window.addEventListener('scroll', repositionOpen);



  // default: desktop terlihat, mobile hidden

  if (window.innerWidth >= 768) container.classList.add('open');

})();

</script>





