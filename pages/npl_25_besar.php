<!-- 25 NPL Terbesar — dropdown role-aware (ikut kode_kantor user; 000 bisa semua) -->
<div class="max-w-7xl mx-auto px-4 py-6 h-screen flex flex-col">
  <div class="flex items-center justify-between mb-2">
    <h1 class="text-2xl font-bold flex items-center gap-2">
      <span>🔥</span><span>25 Debitur Terbesar NPL</span>
    </h1>
  </div>

  <!-- Toolbar -->
  <div class="toolbar-compact mb-3" id="toolbarNpl">
    <div class="bar-left text-sm text-gray-700"></div>

    <!-- kanan: HANYA dropdown Kantor + tombol search -->
    <form id="formFilterTopNpl" class="bar-right">
      <label class="pill">
        <span>Kantor:</span>
        <select id="selCabangNpl" class="pill-select">
          <option value="">konsolidasi</option>
        </select>
      </label>
      <button type="submit" class="f-btn" title="Tampilkan">🔍</button>
    </form>
  </div>

  <!-- Loading -->
  <div id="loadingTop" class="hidden flex items-center gap-2 text-sm text-gray-600 mb-2">
    <svg class="animate-spin h-5 w-5 text-blue-600" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span>Memuat data…</span>
  </div>

  <!-- Tabel -->
  <div id="nplScroller" class="flex-1 min-h-0 overflow-hidden rounded border border-gray-200 bg-white">
    <div class="h-full overflow-auto" id="nplScrollInner">
      <table id="tabelTopNpl" class="min-w-full text-[12.5px] text-left text-gray-700">
        <thead class="uppercase">
          <tr id="nplHead" class="text-[11px]">
            <th class="px-2.5 py-1.5 sticky th-namakantor col-namakantor border-r">NAMA KANTOR</th>
            <th class="px-2.5 py-1.5 sticky th-norek freeze-1 col-norek border-r">NO REKENING</th>
            <th class="px-2.5 py-1.5 sticky th-debitur freeze-2 lock-x col-debitur border-r">NAMA DEBITUR</th>

            <th class="px-2 py-1.5 text-right sticky th-amt col-amt border-r">PLAFOND</th>
            <th class="px-2 py-1.5 text-right sticky th-amt col-amt border-r">BAKI DEBET</th>
            <th class="px-2 py-1.5 text-right sticky th-amt col-amt border-r">T.POKOK</th>
            <th class="px-2 py-1.5 text-right sticky th-amt col-amt border-r">T.BUNGA</th>

            <!-- KOLEK (closing) disembunyikan; KOLEK UPDATE tampil -->
            <th class="px-2.5 py-1.5 sticky th-kol-closing col-kol-closing border-r">KOLEK</th>
            <th class="px-2.5 py-1.5 sticky th-kol-update  col-kol-update  border-r">KOLEK UPDATE</th>

            <th class="px-2 py-1.5 text-right sticky th-amt col-amt border-r">ANGS POKOK</th>
            <th class="px-2 py-1.5 text-right sticky th-amt col-amt border-r">ANGS BUNGA</th>
            <th class="px-2.5 py-1.5 sticky th-date col-date border-r">TGL TRANS</th>
            <th class="px-2.5 py-1.5 sticky th-aksi col-aksi border-r text-center">AKSI</th>
          </tr>
        </thead>
        <tbody id="tbTotalNpl"></tbody>
        <tbody id="bodyTopNpl"></tbody>
      </table>

      <!-- spacer agar baris paling bawah tidak kepotong -->
      <div class="bottom-spacer" aria-hidden="true"></div>
    </div>
  </div>
</div>

<style>
  *{ box-sizing:border-box; }
  html, body{ height:100%; }
  body{ overflow:hidden; }
  #nplScroller .h-full{ overscroll-behavior-x: contain; }

  /* ===== ruang ekstra bawah + scroll padding ===== */
  #nplScrollInner{
    padding-bottom: 64px;      /* naikkan dari 24px → 64px */
    scroll-padding-bottom: 80px;/* saat scroll-into-view, sisakan ruang 80px di bawah */
  }
  .bottom-spacer{ height: 72px; } /* spacer fisik di akhir konten */

  .toolbar-compact{ display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:nowrap; }
  .bar-left{ display:flex; align-items:center; gap:.5rem; flex:1 1 auto; min-width:0; flex-wrap:wrap; }
  .bar-right{ display:flex; align-items:center; gap:.6rem; margin-left:auto; flex:0 0 auto; }
  .pill{ display:inline-flex; align-items:center; gap:.45rem; padding:.22rem .55rem; border-radius:999px; background:#eef2ff; border:1px solid #c7d2fe; color:#1e40af; }
  .pill-select{ border:0; background:transparent; outline:none; font-weight:700; color:#1e40af; min-width:12rem; padding-right:.2rem; }
  .f-btn{ width:38px; height:38px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center; background:#2563eb; color:#fff; box-shadow:0 6px 14px rgba(37,99,235,.25); }
  .f-btn:hover{ background:#1e40af; }

  #nplScroller{ --colNorek: 7.5rem; --colDeb: 12rem; --colDeb_m: 7.5rem; --headH: 32px; }
  #tabelTopNpl{ table-layout:fixed; border-collapse:separate; border-spacing:0; }
  #tabelTopNpl th, #tabelTopNpl td{ border-bottom:1px solid #eef2f7; }

  #tabelTopNpl thead th{
    position:sticky; top:0; z-index:88;
    background:#d9ead3 !important; color:#0f5132;
    border-bottom:1px solid #b7d4c1; user-select:none;
  }
  #tabelTopNpl thead th.border-r{ border-right:1px solid #b7d4c1; }
  #tabelTopNpl thead .freeze-1{ left:0; z-index:90; }
  #tabelTopNpl thead .freeze-2{ left:var(--colNorek); z-index:89; }

  #tabelTopNpl .col-namakantor{ width:14rem; min-width:12rem; }
  #tabelTopNpl .col-norek{ width:var(--colNorek); min-width:var(--colNorek); }
  #tabelTopNpl .col-debitur{ width:var(--colDeb); min-width:var(--colDeb); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  #tabelTopNpl .col-amt{ width:7.6rem; min-width:7rem; text-align:right; font-variant-numeric: tabular-nums; }
  #tabelTopNpl .col-date{ width:9.5ch; min-width:8ch; white-space:nowrap; font-variant-numeric: tabular-nums; text-align:left; }
  #tabelTopNpl .col-aksi{ width:9.5rem; min-width:9rem; text-align:center; }

  #tabelTopNpl td.freeze-1{ position:sticky; left:0; z-index:41; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06); }
  #tabelTopNpl td.freeze-2{ position:sticky; left:var(--colNorek); z-index:40; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06); }

  #tabelTopNpl tbody tr.sticky-total td{
    position:sticky; top:var(--headH); z-index:70;
    background:#eaf2ff; color:#1e40af; border-bottom:1px solid #c7d2fe; font-weight:600;
  }
  #tabelTopNpl tbody tr.sticky-total td.freeze-1{ z-index:91; }
  #tabelTopNpl tbody tr.sticky-total td.freeze-2{ z-index:90; }

  /* ===== HIDE SESUAI PERMINTAAN ===== */
  /* Sembunyikan NAMA KANTOR & AKSI */
  #tabelTopNpl th.th-namakantor, #tabelTopNpl td.col-namakantor{ display:none !important; }
  #tabelTopNpl th.th-aksi,       #tabelTopNpl td.col-aksi      { display:none !important; }
  /* Sembunyikan KOLEK (closing) — KOLEK UPDATE tetap tampil */
  #tabelTopNpl th.th-kol-closing, #tabelTopNpl td.col-kol-closing{ display:none !important; }

  @media (max-width:640px){
    .toolbar-compact{ display:grid; grid-template-columns: 1fr; gap:.65rem; }
    .bar-right{ width:100%; display:grid; grid-template-columns: 1fr auto; gap:.5rem .6rem; align-items:center; }
    .pill-select{ min-width:0; width:100%; font-size:12.5px; }
    .f-btn{ width:34px; height:34px; justify-self:end; border-radius:.8rem; }

    #tabelTopNpl{ font-size:11.5px; }
    #tabelTopNpl thead th{ font-size:10.5px; }
    #tabelTopNpl th, #tabelTopNpl td{ padding:.32rem .4rem; }

    /* opsional: hide NO REKENING di mobile biar ringkas */
    #tabelTopNpl th.th-norek, #tabelTopNpl td.col-norek{ display:none !important; }

    #tabelTopNpl .col-debitur{ width:var(--colDeb_m); min-width:var(--colDeb_m); white-space:normal; word-break:break-word; }
    #tabelTopNpl thead .th-debitur,
    #tabelTopNpl tbody td.freeze-2,
    #tabelTopNpl tbody tr.sticky-total td.freeze-2{ left:0 !important; right:auto !important; box-shadow:1px 0 0 rgba(0,0,0,.06); }

    #tabelTopNpl .lock-x{ touch-action: pan-y; -ms-touch-action: pan-y; }
    #tabelTopNpl .col-amt{ width:6.6rem; min-width:6.2rem; }
  }
</style>

<script>
  /* ===== State tanggal (tanpa input) ===== */
  const StateDate = { closing: '', harian: '' };

  /* ===== Helpers ===== */
  const nfID = new Intl.NumberFormat('id-ID');
  const fmt = n => nfID.format(Number(n||0));
  const selCabang  = document.getElementById('selCabangNpl');

  async function apiCall(url, options={}) {
    if (window.apiFetch) return window.apiFetch(url, options);
    const token = localStorage.getItem('dpk_token');
    const headers = Object.assign({}, options.headers||{});
    if (token) headers['Authorization'] = token;
    return fetch(url, { ...options, headers });
  }

  /* ===== Init role-aware ===== */
  window.addEventListener('DOMContentLoaded', async () => {
    // ambil last closing & harian → simpan ke state
    const d = await getLastHarianData();
    if (!d) return;
    StateDate.closing = d.last_closing || '';
    StateDate.harian  = d.last_created || '';

    // ambil user; prefer kode_kantor, fallback kode
    const user = (window.getUser && window.getUser()) || null;
    const userKode = user?.kode_kantor
      ? String(user.kode_kantor).padStart(3,'0')
      : (user?.kode ? String(user.kode).padStart(3,'0') : '000');

    await populateKantorOptions(userKode);
    await fetchTop25Npl(StateDate.closing, StateDate.harian, (userKode && userKode!=='000') ? userKode : '');

    // ukur tinggi scroller pertama kali
    sizeScroller();
    setHeadHeight();

    // Recalc saat viewport berubah (mobile chrome bar naik/turun)
    if (window.visualViewport) {
      visualViewport.addEventListener('resize', sizeScroller);
      visualViewport.addEventListener('scroll', sizeScroller);
    }
  });

  async function getLastHarianData(){
    try{ const r = await apiCall('./api/date/'); const j = await r.json(); return j.data||null; }
    catch{ return null; }
  }

  async function populateKantorOptions(userKode){
    try{
      if(userKode && userKode!=='000'){
        selCabang.innerHTML = `<option value="${userKode}">${userKode}</option>`;
        selCabang.value = userKode;
        selCabang.disabled = true;
        selCabang.style.pointerEvents = 'none';
        selCabang.addEventListener('mousedown', e=>e.preventDefault());
        selCabang.addEventListener('keydown',  e=>e.preventDefault());
        return;
      }
      const res=await apiCall('./api/kode/',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({type:'kode_kantor'})
      });
      const json=await res.json(); const list=Array.isArray(json.data)?json.data:[];
      let html=`<option value="">konsolidasi</option>`;
      list.filter(x=>x.kode_kantor && x.kode_kantor!=='000')
          .sort((a,b)=> String(a.kode_kantor).localeCompare(String(b.kode_kantor)))
          .forEach(it=>{
            const code=String(it.kode_kantor).padStart(3,'0');
            const name=it.nama_kantor||it.nama_cabang||'';
            html+=`<option value="${code}">${code} — ${name}</option>`;
          });
      selCabang.innerHTML=html;
      selCabang.disabled=false;
      selCabang.style.pointerEvents = 'auto';
    }catch{
      selCabang.innerHTML=`<option value="${userKode && userKode!=='000' ? userKode : ''}">${userKode && userKode!=='000' ? userKode : 'konsolidasi'}</option>`;
      selCabang.disabled = (userKode && userKode!=='000');
      selCabang.style.pointerEvents = (userKode && userKode!=='000') ? 'none' : 'auto';
    }
  }

  // 000 boleh ganti cabang → refetch; non-000 terkunci
  selCabang.addEventListener('change', ()=>{
    const user = (window.getUser && window.getUser()) || null;
    const userKode = user?.kode_kantor
      ? String(user.kode_kantor).padStart(3,'0')
      : (user?.kode ? String(user.kode).padStart(3,'0') : '000');
    const kode = (userKode!=='000') ? userKode : (selCabang.value || '');
    if (StateDate.closing && StateDate.harian) {
      fetchTop25Npl(StateDate.closing, StateDate.harian, kode);
    }
  });

  document.getElementById("formFilterTopNpl").addEventListener("submit", function (e) {
    e.preventDefault();
    const user = (window.getUser && window.getUser()) || null;
    const userKode = user?.kode_kantor
      ? String(user.kode_kantor).padStart(3,'0')
      : (user?.kode ? String(user.kode).padStart(3,'0') : '000');
    const kode = (userKode!=='000') ? userKode : (selCabang.value || '');
    fetchTop25Npl(StateDate.closing, StateDate.harian, kode);
  });

  /* ===== Fetch + Render ===== */
  async function fetchTop25Npl(closing_date, harian_date, kode_cabang){
    const user = (window.getUser && window.getUser()) || null;
    const userKode = user?.kode_kantor
      ? String(user.kode_kantor).padStart(3,'0')
      : (user?.kode ? String(user.kode).padStart(3,'0') : '000');
    const enforcedKode = (userKode!=='000') ? userKode : (kode_cabang||'');

    if (userKode!=='000') { selCabang.value = userKode; selCabang.disabled = true; }

    document.getElementById('loadingTop').classList.remove('hidden');
    try{
      const res = await apiCall("./api/npl/",{
        method:"POST",
        headers:{ "Content-Type":"application/json" },
        body: JSON.stringify({
          type: "25 NPL Terbesar",
          closing_date, harian_date,
          kode_cabang: enforcedKode
        })
      });
      const j = await res.json();
      renderTable(j.data || []);
    }catch{ renderTable([]); }
    finally{
      document.getElementById('loadingTop').classList.add('hidden');
      // pastikan tinggi scroller disesuaikan lagi setelah render
      requestAnimationFrame(()=>{ sizeScroller(); setHeadHeight(); });
      setTimeout(()=>{ sizeScroller(); setHeadHeight(); }, 80);
    }
  }

  function renderTable(rows){
    const tb   = document.getElementById('bodyTopNpl');
    const ttot = document.getElementById('tbTotalNpl');
    tb.innerHTML=''; ttot.innerHTML='';

    const sum = k => rows.reduce((s,r)=> s + Number(r[k]||0), 0);
    const tPlaf = sum('jml_pinjaman');
    const tBaki = sum('baki_debet');
    const tTPok = sum('tunggakan_pokok');
    const tTBng = sum('tunggakan_bunga');
    const tAngP = sum('total_pokok');
    const tAngB = sum('total_bunga');

    ttot.innerHTML = `
      <tr class="sticky-total">
        <td class="px-2.5 py-1.5 col-namakantor"></td>
        <td class="px-2.5 py-1.5 freeze-1 col-norek"></td>
        <td class="px-2.5 py-1.5 freeze-2 lock-x col-debitur">TOTAL</td>
        <td class="px-2 py-1.5 text-right col-amt">${fmt(tPlaf)}</td>
        <td class="px-2 py-1.5 text-right col-amt">${fmt(tBaki)}</td>
        <td class="px-2 py-1.5 text-right col-amt">${fmt(tTPok)}</td>
        <td class="px-2 py-1.5 text-right col-amt">${fmt(tTBng)}</td>
        <td class="px-2.5 py-1.5 col-kol-closing"></td>
        <td class="px-2.5 py-1.5 col-kol-update"></td>
        <td class="px-2 py-1.5 text-right col-amt">${fmt(tAngP)}</td>
        <td class="px-2 py-1.5 text-right col-amt">${fmt(tAngB)}</td>
        <td class="px-2.5 py-1.5 col-date"></td>
        <td class="px-2.5 py-1.5 col-aksi text-center"></td>
      </tr>`;

    const closing_date = StateDate.closing, harian_date = StateDate.harian;

    for(const r of rows){
      const btns = `
        <div class="flex flex-wrap gap-1 justify-center">
          <button class="px-2 py-1 rounded border hover:bg-gray-50"
            onclick="openDetailModal('${r.no_rekening}','${r.kode_cabang||''}')">Detail</button>
          <button class="px-2 py-1 rounded bg-blue-600 text-white hover:bg-blue-700"
            onclick="openPlanModal('${r.no_rekening}','${r.kode_cabang||''}','${closing_date}','${harian_date}','${(r.nama_nasabah||'').replace(/'/g, "\\'")}')">Kelola</button>
        </div>`;
      const aksiCell = `<td class="px-2.5 py-1.5 col-aksi text-center">${btns}</td>`;

      tb.insertAdjacentHTML('beforeend', `
        <tr class="border-b hover:bg-gray-50">
          <td class="px-2.5 py-1.5 col-namakantor" title="${r.nama_kantor||''}">${r.nama_kantor||''}</td>
          <td class="px-2.5 py-1.5 freeze-1 col-norek" title="${r.no_rekening||'-'}">${shortMid(r.no_rekening,12)}</td>
          <td class="px-2.5 py-1.5 freeze-2 lock-x col-debitur" title="${r.nama_nasabah||'-'}">${shortTail(r.nama_nasabah,24)}</td>

          <td class="px-2 py-1.5 text-right col-amt">${fmt(r.jml_pinjaman)}</td>
          <td class="px-2 py-1.5 text-right col-amt">${fmt(r.baki_debet)}</td>
          <td class="px-2 py-1.5 text-right col-amt">${fmt(r.tunggakan_pokok)}</td>
          <td class="px-2 py-1.5 text-right col-amt">${fmt(r.tunggakan_bunga)}</td>

          <!-- KOLEK (closing) disembunyikan via CSS; UPDATE tampil -->
          <td class="px-2.5 py-1.5 col-kol-closing text-center">${r.kolek_closing || ""}</td>
          <td class="px-2.5 py-1.5 col-kol-update  text-center">${r.kolek_harian  || ""}</td>

          <td class="px-2 py-1.5 text-right col-amt">${fmt(r.total_pokok)}</td>
          <td class="px-2 py-1.5 text-right col-amt">${fmt(r.total_bunga)}</td>
          <td class="px-2.5 py-1.5 col-date">${r.tgl_trans || "-"}</td>
          ${aksiCell}
        </tr>`);
    }

    // setelah isi, recalibrate tinggi
    requestAnimationFrame(sizeScroller);
  }

  /* ===== Layout helpers ===== */
  function sizeScroller(){
    const wrap = document.getElementById('nplScroller');
    if(!wrap) return;
    const rect = wrap.getBoundingClientRect();
    const top = rect.top;
    const vpH = (window.visualViewport && window.visualViewport.height) ? window.visualViewport.height : window.innerHeight;

    // tambah ruang ekstra supaya baris terakhir tidak ketutupan
    const EXTRA_BOTTOM = 0; // tinggi container, ruang ekstra diatasi oleh padding/spacer
    const h = Math.max(320, Math.floor(vpH - top - EXTRA_BOTTOM));
    wrap.style.height = h + 'px';
  }

  function setHeadHeight(){
    const h = document.getElementById('nplHead')?.offsetHeight || 32;
    document.getElementById('nplScroller')?.style.setProperty('--headH', h + 'px');
  }

  window.addEventListener('resize', ()=>{ sizeScroller(); setHeadHeight(); });

  // Amati perubahan layout (toolbar berubah tinggi, dsb.)
  const toolbar = document.getElementById('toolbarNpl');
  if (window.ResizeObserver && toolbar) {
    const ro = new ResizeObserver(()=> sizeScroller());
    ro.observe(toolbar);
  }

  // Fonts/images loaded → recalc
  if (document.fonts && document.fonts.ready) {
    document.fonts.ready.then(()=>{ sizeScroller(); setHeadHeight(); });
  }
  window.addEventListener('load', ()=>{ sizeScroller(); setHeadHeight(); });

  /* ===== Utils ===== */
  function shortTail(s, n=18){ s=String(s||''); return s.length<=n? s : s.slice(0,n).trimEnd()+'…'; }
  function shortMid(s, n=12){ s=String(s||''); if(s.length<=n) return s; const k=n-1, f=Math.ceil(k/2), b=Math.floor(k/2); return s.slice(0,f)+'…'+s.slice(-b); }

  /* ===== Dummy modal hooks (opsional) ===== */
  function openDetailModal(){ document.getElementById('modalDetail')?.classList.remove('hidden');}
  function closeDetailModal(){ document.getElementById('modalDetail')?.classList.add('hidden'); }
  function openPlanModal(){ document.getElementById('modalPlan')?.classList.remove('hidden'); }
  function closePlanModal(){ document.getElementById('modalPlan')?.classList.add('hidden'); }
</script>
