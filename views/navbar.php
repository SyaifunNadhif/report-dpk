<!-- NAVBAR -->
<nav id="mainNavbar" class="sticky top-0 bg-white/90 backdrop-blur border-b border-slate-200 shadow-sm">
  <div class="max-w-screen-xl mx-auto px-4">
    <!-- Top bar -->
    <div class="h-14 flex items-center justify-between">
      <a href="home" class="flex items-center gap-2">
        <img src="./img/logodpk.png" class="h-8 w-8 object-contain" alt="Logo">
        <span class="text-slate-800 text-lg font-semibold">ATLAS</span>
      </a>

      <div class="flex items-center gap-3">
        <!-- ✅ user box: selalu flex; sembunyikan via media query (lihat CSS) -->
        <div class="flex flex-col leading-tight text-right select-none nb-userbox">
          <span id="navUserName" class="text-slate-800 text-sm font-medium">—</span>
          <span id="navBranch" class="text-slate-500 text-[11px]">—</span>
        </div>

        <!-- Profile (desktop only; disembunyikan di mobile via CSS) -->
        <button id="dropdownProfileButton" data-dropdown="dropdownProfile" aria-haspopup="menu" aria-expanded="false"
                class="inline-flex items-center justify-center rounded-full ring-1 ring-slate-200 hover:ring-slate-300 transition nb-tap"
                style="width:2.25rem;height:2.25rem">
          <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true">
            <circle cx="12" cy="8" r="4" fill="none" stroke="currentColor" stroke-width="1.8"></circle>
            <path d="M4 20a8 8 0 0 1 16 0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
          </svg>
        </button>

        <!-- Hamburger (mobile) -->
        <button id="hamburger" type="button" aria-label="Toggle menu" aria-expanded="false"
                class="md:hidden burger-btn nb-tap">
          <svg class="icon-burger" viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" fill="none" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/>
          </svg>
          <svg class="icon-x" viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" fill="none" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" d="M6 6l12 12M18 6l-12 12"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Menu -->
    <div id="navbar-default" class="nb-container">
      <ul class="nb-row">
        <li class="nb-li"><a href="home" class="nb-link nb-tap nb-active">Dashboard</a></li>

        <!-- NPL -->
        <li class="nb-li">
          <button data-dropdown="dropdownNPL" class="nb-parent nb-tap">
            <span>NPL</span>
            <svg class="nb-caret" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.586l3.71-4.354a.75.75 0 111.14.976l-4.25 5a.75.75 0 01-1.14 0l-4.25-5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
          </button>
          <div id="dropdownNPL" class="dropdown-panel">
            <ul class="nb-list">
              <li><a href="npl" class="nb-item nb-tap">NPL Konsolidasi</a></li>
              <li><a href="perbandingan_npl" class="nb-item nb-tap">Perbandingan NPL</a></li>
              <li><a href="recovery_npl" class="nb-item nb-tap">Recovery NPL</a></li>
              <li><a href="flow_par" class="nb-item nb-tap">Flow Par</a></li>
              <li><a href="flow_50_besar" class="nb-item nb-tap">50 Besar Flow Par</a></li>
              <li><a href="npl_25_besar" class="nb-item nb-tap">25 NPL Besar</a></li>
              <li><a href="potensi_npl" class="nb-item nb-tap">Potensi NPL</a></li>
            </ul>
          </div>
        </li>

        <!-- PH -->
        <li class="nb-li">
          <button data-dropdown="dropdownPH" class="nb-parent nb-tap">
            <span>PH</span>
            <svg class="nb-caret" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.586l3.71-4.354a.75.75 0 111.14.976l-4.25 5a.75.75 0 01-1.14 0l-4.25-5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
          </button>
          <div id="dropdownPH" class="dropdown-panel">
            <ul class="nb-list">
              <li><a href="recovery_ph" class="nb-item nb-tap">Recovery PH</a></li>
              <li><a href="bucket_saldo_ph" class="nb-item nb-tap">Saldo PH (Bucket)</a></li>
              <li><a href="ph_lgd" class="nb-item nb-tap">Debitur PH LGD</a></li>
            </ul>
          </div>
        </li>

        <!-- Collection -->
        <li class="nb-li">
          <button data-dropdown="dropdownPenagihan" class="nb-parent nb-tap">
            <span>Collection</span>
            <svg class="nb-caret" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.586l3.71-4.354a.75.75 0 111.14.976l-4.25 5a.75.75 0 01-1.14 0l-4.25-5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
          </button>
          <div id="dropdownPenagihan" class="dropdown-panel">
            <ul class="nb-list">
              <!-- <li><a href="maping_bucket" class="nb-item nb-tap">Maping Backet</a></li> -->
              <li><a href="migrasi_bucket" class="nb-item nb-tap">Migrasi Bucket</a></li>
              <li><a href="search_debitur" class="nb-item nb-tap">Seach Debitur Kredit</a></li>
              <li><a href="monitoring_ao" class="nb-item nb-tap">Monitoring Ao Remedial</a></li>
              <li><a href="actual_kredit" class="nb-item nb-tap">Actual Kredit</a></li>
            </ul>
          </div>
        </li>

        <!-- Kredit -->
        <!-- <li class="nb-li">
          <button data-dropdown="dropdownCKPN" class="nb-parent nb-tap">
            <span>Kredit</span>
            <svg class="nb-caret" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.586l3.71-4.354a.75.75 0 111.14.976l-4.25 5a.75.75 0 01-1.14 0l-4.25-5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
          </button>
          <div id="dropdownCKPN" class="dropdown-panel">
            <ul class="nb-list">
              <li><a href="realisasi_kredit" class="nb-item nb-tap">Realisasi Kredit</a></li>
              <li><a href="ckpn" class="nb-item nb-tap">CKPN</a></li>
              <li><a href="ckpn_produk" class="nb-item nb-tap">CKPN Produk</a></li>
              <li><a href="actual_kredit" class="nb-item nb-tap">Actual Kredit</a></li>
            </ul>
          </div>
        </li> -->

        <li class="nb-li">
          <button data-dropdown="dropdownPemasaran" class="nb-parent nb-tap">
            <span>Pemasaran</span>
            <svg class="nb-caret" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.586l3.71-4.354a.75.75 0 111.14.976l-4.25 5a.75.75 0 01-1.14 0l-4.25-5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
          </button>
          <div id="dropdownPemasaran" class="dropdown-panel">
            <ul class="nb-list">
              <li><a href="realisasi_kredit" class="nb-item nb-tap">Realisasi Kredit</a></li>
              <li><a href="realisasi_ao" class="nb-item nb-tap">Realisasi Kredit AO</a></li>
              <li><a href="repayment_rate" class="nb-item nb-tap">Repayment Rate</a></li>
              <li><a href="migrasi_bucket_sc" class="nb-item nb-tap">Migrasi Bucket SC</a></li>
              <li><a href="mob" class="nb-item nb-tap">MOB 6 Bulan</a></li>
              <li><a href="pipelane_ao_jt" class="nb-item nb-tap">Pipelane AO Kredit</a></li>
              <li><a href="jatuh_tempo" class="nb-item nb-tap">Jatuh Tempo and Refinacing</a></li>
            </ul>
          </div>
        </li>

        <!-- ===== MOBILE-ONLY: Profile dimasukkan ke menu (di bawah Kredit) ===== -->
        <li class="nb-li md:hidden">
          <button data-dropdown="dropdownProfileMobile" class="nb-parent nb-tap">
            <span>Profile</span>
            <svg class="nb-caret" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.586l3.71-4.354a.75.75 0 111.14.976l-4.25 5a.75.75 0 01-1.14 0l-4.25-5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
          </button>
          <div id="dropdownProfileMobile" class="dropdown-panel">
            <ul class="nb-list">
              <li>
                <a href="account_handle" class="nb-item nb-tap flex-col items-start">
                  <span class="text-xs text-slate-500">Account handle</span>
                  <span id="accHandleMobile" class="text-sm font-semibold text-slate-800 underline decoration-dotted">—</span>
                </a>
              </li>
              <li><a href="#" id="linkHistoryMobile" class="nb-item nb-tap">History Kunjungan</a></li>
              <li><a href="#" id="linkLogoutMobile" class="nb-item nb-tap">Logout</a></li>
            </ul>
          </div>
        </li>
      </ul>

      <!-- DESKTOP-ONLY: Profile dropdown -->
      <div id="dropdownProfile" class="dropdown-panel dropdown-profile" role="menu" aria-labelledby="dropdownProfileButton">
        <a href="account_handle" class="block px-4 py-3 hover:bg-slate-50 nb-tap">
          <div class="text-xs text-slate-500">Account handle</div>
          <div id="accHandle" class="text-sm font-semibold text-slate-800 underline decoration-dotted">—</div>
        </a>
        <a href="#" id="linkHistoryDesk" class="block px-4 py-2 hover:bg-slate-50 text-sm text-slate-700 nb-tap">History Kunjungan</a>
        <hr class="my-1">
        <ul class="py-1 text-sm text-slate-700">
          <li><a href="#" id="linkLogoutDesk" class="block px-4 py-2 hover:bg-slate-50 nb-tap">Logout</a></li>
        </ul>
      </div>

    </div>
  </div>
  <div class="h-[2px] w-full bg-gradient-to-r from-blue-600 via-sky-500 to-orange-500"></div>
</nav>

<style>
/* ===== Layering ===== */
#mainNavbar{ z-index:2200 !important; position:sticky; top:0; overflow-x:clip; overflow-y:visible; isolation:isolate; }
.nb-container{ position:relative; z-index:2201; display:none; }
@media (min-width:768px){ .nb-container{ display:block; } }
.nb-container.open{ display:block; }

/* ===== Desktop: horizontal ===== */
.nb-row{
  display:flex; flex-direction:row; align-items:center; gap:.25rem;
  white-space:nowrap; padding:.4rem 0;
  overflow-x:auto; -webkit-overflow-scrolling:touch; scrollbar-width:none;
  overscroll-behavior-x: contain; touch-action: pan-x;
}
.nb-row::-webkit-scrollbar{ display:none; }
.nb-li{ flex:0 0 auto; }

.nb-link, .nb-parent{
  display:inline-flex; align-items:center; gap:.25rem; cursor:pointer;
  padding:.45rem .7rem; border-radius:.5rem; color:#334155; text-decoration:none;
  -webkit-tap-highlight-color:transparent; touch-action: manipulation;
}
.nb-link:hover, .nb-parent:hover{ color:#2563eb; background:#f8fafc; }
.nb-active{ color:#1d4ed8; font-weight:600; border-bottom:2px solid #2563eb; }
.nb-caret{ margin-left:.15rem; transition:transform .15s ease; }
.rot180{ transform:rotate(180deg); }

/* Dropdown (desktop overlay) */
.dropdown-panel{
  display:none; position:fixed; z-index:2300; background:#fff;
  border:1px solid #e2e8f0; border-radius:.75rem;
  box-shadow:0 12px 28px rgba(15,23,42,.12); padding:.4rem 0;
  min-width:14rem; max-width:min(92vw, 22rem);
  max-height: calc(100vh - var(--navH,56px) - 16px); overflow:auto;
}
.dropdown-panel.is-open{ display:block; }
.nb-list{ padding:.25rem 0; font-size:.9rem; color:#334155; list-style:none; margin:0; }
.nb-item{ display:block; padding:.5rem 1rem; border-radius:.375rem; text-decoration:none; color:#334155; }
.nb-item:hover{ background:#f8fafc; }

/* Hamburger */
.burger-btn{ display:inline-flex; align-items:center; justify-content:center; width:2.5rem; height:2.5rem; border:0; background:transparent; border-radius:.5rem; color:#334155; }
.burger-btn:active{ background:#f1f5f9; }
.burger-btn .icon-x{ display:none; }
.burger-btn.is-open .icon-burger{ display:none; }
.burger-btn.is-open .icon-x{ display:block; }
@media (min-width:768px){ #hamburger{ display:none !important; } }

/* ==== MOBILE (<=767px) ==== */
@media (max-width:767px){
  /* sembunyikan user box di mobile TANPA bergantung sm:flex */
  .nb-userbox{ display:none !important; }

  #dropdownProfileButton{ display:none !important; } /* icon profil hanya desktop */

  .nb-row{
    flex-direction:column; align-items:stretch; gap:.35rem;
    padding:.6rem; border:1px solid #e2e8f0; border-radius:.75rem;
    background:#f8fafc; white-space:normal; overflow:visible;
  }
  .nb-li{ width:100%; }
  .nb-link, .nb-parent{ width:100%; justify-content:flex-start; border-radius:.6rem; padding:.55rem .8rem; font-size:14px; }
  .nb-link:hover, .nb-parent:hover{ background:#eef2ff; color:#1d4ed8; }
  .nb-active{ color:#fff; background:#2563eb; border-bottom:0; }
  .nb-caret{ margin-left:auto; }

  .dropdown-panel{ position:static !important; display:none; margin:.25rem 0 0 .5rem; border:1px solid #e2e8f0; border-radius:.5rem; box-shadow:none; max-width:100%; min-width:0; }
  .dropdown-panel.is-open{ display:block; }

  /* Popover profil desktop tidak dipakai di mobile */
  .dropdown-profile{ display:none !important; }
}
</style>

<script>
/* ===== NAVBAR JS ===== */
(function(){
  const navbar    = document.getElementById('mainNavbar');
  const container = document.getElementById('navbar-default');
  const hamburger = document.getElementById('hamburger');
  const profileBtn= document.getElementById('dropdownProfileButton'); // desktop only
  const profilePan= document.getElementById('dropdownProfile');       // desktop only

  const allBtns   = Array.from(document.querySelectorAll('[data-dropdown]'));
  const menuBtns  = allBtns.filter(b => b !== profileBtn);
  const panels    = Array.from(document.querySelectorAll('.dropdown-panel'));
  const isMobile  = () => window.innerWidth < 768;
  const CLAMP = 10, DRAG_TOL = 8;

  function setNavH(){ document.documentElement.style.setProperty('--navH', (navbar?.offsetHeight || 56)+'px'); }
  window.addEventListener('load', setNavH); window.addEventListener('resize', setNavH);

  function setMenuOpen(open){
    if (isMobile()){
      container.classList.toggle('open', open);
      hamburger.classList.toggle('is-open', open);
      hamburger.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (!open) closeAll();
    } else {
      container.classList.add('open');
      hamburger.classList.remove('is-open');
      hamburger.setAttribute('aria-expanded','false');
    }
  }
  setMenuOpen(!isMobile());

  hamburger?.addEventListener('click', (e)=>{
    if (!isMobile()) return;
    e.preventDefault(); e.stopPropagation();
    setMenuOpen(!container.classList.contains('open'));
  });

  function closeAll(exceptId){
    panels.forEach(p=>{
      if(!exceptId || p.id !== exceptId){
        p.classList.remove('is-open');
        p.style.left=''; p.style.top=''; p.style.minWidth='';
        const b = document.querySelector(`[data-dropdown="${p.id}"]`);
        b?.setAttribute('aria-expanded','false');
        b?.querySelector('.nb-caret')?.classList.remove('rot180');
      }
    });
  }
  function placePanel(btn, panel){
    const btnRect = btn.getBoundingClientRect();
    const navRect = navbar?.getBoundingClientRect();
    const gap = 6, top = (navRect?.bottom || 56) + gap;

    panel.style.visibility='hidden'; panel.style.display='block';
    const w = Math.max(224, Math.min(panel.offsetWidth || 224, Math.min(352, window.innerWidth - CLAMP*2)));
    panel.style.display=''; panel.style.visibility='';

    let left = btnRect.right - w;
    left = Math.max(CLAMP, Math.min(left, window.innerWidth - w - CLAMP));

    panel.style.left = left+'px';
    panel.style.top  = top +'px';
    panel.style.minWidth = w+'px';
  }

  function bindMenu(btn){
    const id = btn.getAttribute('data-dropdown');
    const panel = document.getElementById(id);
    if(!panel) return;

    let sx=0, sy=0, moved=false;
    const onDown = (e)=>{ sx=e.clientX ?? (e.touches?.[0]?.clientX||0); sy=e.clientY ?? (e.touches?.[0]?.clientY||0); moved=false; e.stopPropagation(); };
    const onMove = (e)=>{ const x=e.clientX ?? (e.touches?.[0]?.clientX||0), y=e.clientY ?? (e.touches?.[0]?.clientY||0); if(Math.hypot(x-sx,y-sy)>DRAG_TOL) moved=true; };
    const onUp   = (e)=>{
      e.preventDefault(); e.stopPropagation(); if(moved) return;
      const open = panel.classList.contains('is-open');
      if (open){
        panel.classList.remove('is-open'); btn.setAttribute('aria-expanded','false'); btn.querySelector('.nb-caret')?.classList.remove('rot180');
      } else {
        closeAll(id);
        if (isMobile()){ panel.classList.add('is-open'); }
        else { placePanel(btn, panel); panel.classList.add('is-open'); }
        btn.setAttribute('aria-expanded','true'); btn.querySelector('.nb-caret')?.classList.add('rot180');
      }
    };

    btn.addEventListener('pointerdown', onDown, {passive:true});
    btn.addEventListener('pointermove', onMove, {passive:true});
    btn.addEventListener('pointerup',   onUp,   {passive:false});
    panel.addEventListener('pointerdown', e=>e.stopPropagation(), {passive:true});
    panel.addEventListener('click', e=>e.stopPropagation());
  }
  menuBtns.forEach(bindMenu);

  if (profileBtn && profilePan){
    let sx=0, sy=0, moved=false;
    const openProfile = ()=>{ closeAll('dropdownProfile'); placePanel(profileBtn, profilePan); profilePan.classList.add('is-open'); profileBtn.setAttribute('aria-expanded','true'); };
    const closeProfile= ()=>{ profilePan.classList.remove('is-open'); profilePan.style.left=''; profilePan.style.top=''; profilePan.style.minWidth=''; profileBtn.setAttribute('aria-expanded','false'); };
    const onDown = (e)=>{ sx=e.clientX ?? (e.touches?.[0]?.clientX||0); sy=e.clientY ?? (e.touches?.[0]?.clientY||0); moved=false; e.stopPropagation(); };
    const onMove = (e)=>{ const x=e.clientX ?? (e.touches?.[0]?.clientX||0), y=e.clientY ?? (e.touches?.[0]?.clientY||0); if(Math.hypot(x-sx,y-sy)>DRAG_TOL) moved=true; };
    const onUp   = (e)=>{ e.preventDefault(); e.stopPropagation(); if(moved) return; if(profilePan.classList.contains('is-open')) closeProfile(); else openProfile(); };

    profileBtn.addEventListener('pointerdown', onDown, {passive:true});
    profileBtn.addEventListener('pointermove', onMove, {passive:true});
    profileBtn.addEventListener('pointerup',   onUp,   {passive:false});
    profilePan.addEventListener('pointerdown', e=>e.stopPropagation(), {passive:true});
    profilePan.addEventListener('click', e=>e.stopPropagation());
    window.addEventListener('resize', ()=>{ if(profilePan.classList.contains('is-open')) placePanel(profileBtn, profilePan); });
  }

  document.addEventListener('pointerdown', (e)=>{
    const isBtn = e.target.closest?.('[data-dropdown]');
    const isPanel = e.target.closest?.('.dropdown-panel');
    const isHam = e.target.closest?.('#hamburger');
    if(!isBtn && !isPanel && !isHam) closeAll();
  }, {passive:true});

  window.addEventListener('load', ()=>{
    const src = document.getElementById('accHandle');
    const dst = document.getElementById('accHandleMobile');
    if (src && dst) dst.textContent = src.textContent || '—';
  });
})();
</script>

<!-- FE-only fallback: isi navbar dari localStorage/JWT bila skrip auth belum sempat nulis -->
<script>
(() => {
  const TOKEN_KEY='dpk_token', USER_KEY='dpk_user';
  function parseJwt(t){ try{const p=String(t).split('.'); if(p.length<2) return null;
    const b64=p[1].replace(/-/g,'+').replace(/_/g,'/'); const json=decodeURIComponent(atob(b64).split('').map(c=>'%'+('00'+c.charCodeAt(0).toString(16)).slice(-2)).join('')); return JSON.parse(json);
  }catch{return null;} }
  function getUser(){
    try{ if(window.__USER) return window.__USER; const raw=localStorage.getItem(USER_KEY); if(raw) return JSON.parse(raw);}catch{}
    const tok=(localStorage.getItem(TOKEN_KEY)||'').trim(); if(!tok) return null;
    const p=parseJwt(tok)||{}; return { full_name:p.full_name||p.name||p.nama||null, branch_name:p.branch_name||p.branch||p.cabang||null,
      employee_id:p.employee_id||p.emp_id||p.nik||null, id:p.sub||p.user_id||null, kode:p.kode||p.branch_code||null,
      account_handle:p.handle||p.username||p.email||null };
  }
  function paint(u){
    if(!u) return false;
    const name=document.getElementById('navUserName'), br=document.getElementById('navBranch'), nav=document.getElementById('mainNavbar'), acc=document.getElementById('accHandle');
    let ok=false;
    if(name){ name.textContent=u.full_name||'-'; ok=true; }
    if(br){ br.textContent=u.branch_name||'-'; ok=true; }
    if(nav){ nav.dataset.userId=u.id??''; nav.dataset.employeeId=u.employee_id??''; nav.dataset.kode=u.kode??''; ok=true; }
    if(acc){ acc.textContent=(u.account_handle||u.username||u.email||u.employee_id||u.kode||'-'); ok=true; }
    return ok;
  }
  const user=getUser(); if(!paint(user)){
    let n=0; const t=setInterval(()=>{ if(paint(user)||++n>40) clearInterval(t); },100);
    document.addEventListener('DOMContentLoaded', ()=>paint(user), {once:true});
    const mo=new MutationObserver(()=>{ if(paint(user)) mo.disconnect(); });
    mo.observe(document.documentElement,{childList:true,subtree:true});
  }
})();
</script>
