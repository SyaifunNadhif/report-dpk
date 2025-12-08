<!-- search_debitur_kredit.php -->
<div class="max-w-7xl mx-auto px-4 py-6 h-screen flex flex-col">
  <div class="flex items-center justify-between mb-2">
    <h1 class="text-2xl font-bold flex items-center gap-2">
      <span>🔎</span><span>Search Debitur Kredit</span>
    </h1>
  </div>

  <div class="toolbar-compact mb-3">
    <div id="summaryBar" class="bar-left text-gray-700">
      <span class="pill pill-compact">NOA: <b id="sumNoa">0</b></span>
      <span class="pill pill-compact">BD (ACT): <b id="sumBD">0</b></span>
      <span class="pill pill-compact">CKPN ACT: <b id="sumCK">0</b></span>
    </div>

    <form id="formFilterSearch" class="bar-right" autocomplete="off">
      <div class="fld">
        <label for="kode_kantor" class="lbl">Kantor:</label>
        <select id="kode_kantor" class="f-inp min-w-[200px] mobile-kode"><option>Memuat...</option></select>
      </div>
      <div class="fld">
        <label class="lbl" for="kolekFilter">Kolek:</label>
        <select id="kolekFilter" class="f-inp">
          <option value="">Semua</option>
          <option value="L">L</option>
          <option value="DP">DP</option>
          <option value="KL">KL</option>
          <option value="D">D</option>
          <option value="M">M</option>
          <option value="LUNAS">Lunas</option>
        </select>
      </div>

      <div class="fld fld-hidden"><label class="lbl" for="closing_date">Closing:</label><input type="date" id="closing_date" class="f-inp"></div>
      <div class="fld fld-hidden"><label class="lbl" for="harian_date">Harian:</label><input type="date" id="harian_date" class="f-inp"></div>

      <div class="fld">
        <label class="lbl" for="q">Cari:</label>
        <input id="q" class="f-inp w-[240px] short-mobile" placeholder="No Rek / Nama / AO / Key Name">
      </div>
    </form>
  </div>

  <div id="loadingBar" class="hidden flex items-center gap-2 text-sm text-gray-600 mb-2">
    <svg class="animate-spin h-5 w-5 text-blue-600" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span>Mencari data debitur...</span>
  </div>

  <div id="wrapTable" class="flex-1 min-h-0 overflow-hidden rounded border border-gray-200 bg-white">
    <div class="h-full overflow-auto">
      <table id="tabelSearch" class="min-w-full text-left text-gray-700">
        <thead>
          <tr id="headRow" class="uppercase">
            <th class="px-3 py-2 th freeze-1 col-debitur" data-sort="nama_nasabah">NAMA DEBITUR</th>
            <th class="px-3 py-2 th freeze-2 col-norek"   data-sort="no_rekening">NO REKENING</th>
            <th class="px-3 py-2 th col-ao"               data-sort="ao_remedial">AO Remedial</th>
            <th class="px-3 py-2 th col-plan"             data-sort="plan_bucket">Plan Bucket</th>
            <th class="px-3 py-2 th col-prod text-center" data-sort="product_code">Kode Prod</th>
            <th class="px-3 py-2 th col-bucketM1"         data-sort="bucket">Bucket M-1</th>
            <th class="px-3 py-2 th col-bucket"           data-sort="bucket_update">Bucket (Act)</th>
            <th class="px-3 py-2 th col-kolek"            data-sort="kolek_update">Kolek</th>

            <th class="px-3 py-2 th text-right col-num"   data-sort="hari_menunggak">DPD</th>
            <th class="px-3 py-2 th text-right col-amt"   data-sort="saldo_bank">OSC (Bank)</th>
            <th class="px-3 py-2 th text-right col-amt"   data-sort="baki_debet_update">BD (ACT)</th>
            <th class="px-3 py-2 th text-right col-num"   data-sort="pd_percent">PD%</th>
            <th class="px-3 py-2 th text-right col-amt"   data-sort="ckpn_m1">CKPN M-1</th>
            <th class="px-3 py-2 th text-right col-amt"   data-sort="ckpn_actual">CKPN ACT</th>
            <th class="px-3 py-2 th text-right col-amt"   data-sort="inc_ckpn">Δ CKPN</th>
            <th class="px-3 py-2 th text-right col-amt"   data-sort="angsuran_pokok">ANGS POKOK</th>
            <th class="px-3 py-2 th text-right col-amt"   data-sort="angsuran_bunga">ANGS BUNGA</th>
            <th class="px-3 py-2 th col-date"             data-sort="tgl_trans_last_angsuran">TGL ANGS</th>
          </tr>
        </thead>
        <tbody id="bodyTotal"></tbody>
        <tbody id="bodyRows"></tbody>
      </table>
    </div>
  </div>
</div>

<style>
  .toolbar-compact{ display:flex; align-items:center; justify-content:space-between; gap:.6rem; flex-wrap:nowrap; }
  .bar-left{ display:flex; align-items:center; gap:.35rem; flex:1 1 auto; min-width:0; flex-wrap:wrap; }
  .bar-right{ display:flex; align-items:end; gap:.5rem; margin-left:auto; }
  .bar-right .fld{ display:flex; align-items:center; gap:.35rem; }
  .lbl{ font-size:12px; color:#334155; white-space:nowrap; }
  .pill{ display:inline-flex; align-items:center; gap:.25rem; background:#f1f5f9; border:1px solid #e2e8f0; border-radius:.6rem; padding:.2rem .45rem; }
  .pill-compact{ font-size:clamp(9px,1.7vw,12px); line-height:1; }
  .f-inp{ border:1px solid #cbd5e1; border-radius:.6rem; padding:.4rem .6rem; font-size:13.5px; background:#fff; height:36px; }
  .fld-hidden{ display:none !important; }

  #wrapTable{ --colDeb: 12rem; --colNorek: 9rem; --theadH: 38px; --totalH: 32px; --safeB: 32px; }
  @supports(padding:max(0px)){ #wrapTable{ --safeB:max(32px, env(safe-area-inset-bottom)); } }

  #tabelSearch{ table-layout: fixed; border-collapse:separate; border-spacing:0; font-size:13.1px; }
  #tabelSearch thead .th{ position:sticky; top:0; background:#e0f2fe; font-size:11.5px; z-index:80; cursor:pointer; user-select:none; }
  #tabelSearch thead .th.sorting:after{ content:""; margin-left:.3rem; }
  #tabelSearch thead .th.asc:after { content:"▲"; }
  #tabelSearch thead .th.desc:after{ content:"▼"; }
  #tabelSearch thead .freeze-1{ left:0; z-index:120; }
  #tabelSearch thead .freeze-2{ left:var(--colDeb); z-index:119; }
  #tabelSearch th, #tabelSearch td{ border-bottom:1px solid #eef2f7; }
  #tabelSearch tbody tr:hover td{ background:#f9fafb; }

  .col-debitur{ width:var(--colDeb); min-width:var(--colDeb); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .col-norek{ width:var(--colNorek); min-width:var(--colNorek); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  #tabelSearch td.freeze-1{ position:sticky; left:0; z-index:100; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06); }
  #tabelSearch td.freeze-2{ position:sticky; left:var(--colDeb); z-index:99;  background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06); }

  .col-ao{ width:10rem; min-width:8.5rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .col-plan{ width:8.2rem; min-width:7.6rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .col-prod{ width:6rem; min-width:5.4rem; text-align:center; }
  .col-bucketM1,.col-bucket{ width:7.6rem; min-width:7rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .col-kolek{ width:3.8rem; min-width:3.4rem; text-align:center; }
  .col-num{ width:4rem; min-width:3.4rem; text-align:right; }
  .col-amt{ width:8rem; min-width:7.2rem; text-align:right; }
  .col-date{ width:10ch; min-width:8.5ch; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-variant-numeric: tabular-nums; }

  #tabelSearch tbody tr.sticky-total td{ position:sticky; top:var(--theadH); background:#eaf2ff; color:#1e40af; z-index:110; border-bottom:1px solid #c7d2fe; font-size:12px; }
  #tabelSearch tbody tr.sticky-total td.freeze-1{ z-index:130; }
  #tabelSearch tbody tr.sticky-total td.freeze-2{ z-index:129; }
  #bodyRows::after{ content:""; display:block; height: calc(var(--theadH) + var(--totalH) + var(--safeB)); }

  /* ===== Mobile ===== */
  @media (max-width:640px){
    .toolbar-compact{ align-items:start; }
    .bar-right{
      display:grid;
      grid-template-columns: minmax(4.6rem,5.2rem) minmax(4.6rem,5.2rem) minmax(7.5rem,10rem);
      gap:.4rem; width:auto; justify-items:end;
    }
    .lbl{ display:none !important; }
    .f-inp{ height:34px; font-size:12.1px; padding:.34rem .5rem; }
    .short-mobile{ width:100% !important; }

    /* potong tampilan kode_kantor agar hemat ruang */
    .mobile-kode{ width:5.2ch !important; min-width:5.2ch !important; padding-left:.45rem; padding-right:.45rem; text-align:center; }

    #tabelSearch{ font-size:11.1px; }
    #tabelSearch thead .th{ font-size:10.1px; }

    #tabelSearch thead .col-debitur{ left:0 !important; width:9rem !important; min-width:9rem !important; }
    #tabelSearch td.col-debitur{ width:9rem !important; min-width:9rem !important; max-width:9rem !important; }
    .col-amt{ width:7rem; min-width:6.5rem; }
    .col-num{ width:3.3rem; min-width:3.1rem; }
    .col-plan,.col-bucketM1,.col-bucket{ width:6.8rem; min-width:6.4rem; }
    .col-ao{ width:8rem; min-width:7.5rem; }
    .col-prod{ width:5rem; min-width:4.8rem; }
    .col-date{ width:9ch; min-width:8ch; }

    #tabelSearch .col-norek{ display:none !important; }
  }

  /* Print & spacing fix */
  @page { size: legal landscape; margin: 10mm; }
  @media print{
    :root{ color-scheme: light; } *{ -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    body *{ visibility:hidden !important; }
    #tabelSearch, #tabelSearch *{ visibility:visible !important; }
    #wrapTable{ overflow:visible !important; height:auto !important; border:none !important; }
    #tabelSearch{ position:absolute !important; left:0; top:0; width:100% !important; font-size:11px; }
    #tabelSearch thead{ display:table-header-group; }
    #tabelSearch tbody tr.sticky-total td,
    #tabelSearch td.freeze-1, #tabelSearch td.freeze-2,
    #tabelSearch thead .th{ position:static !important; left:auto !important; z-index:auto !important; box-shadow:none !important; }
    #bodyRows::after{ display:none !important; }
  }
  #tabelSearch thead .th{ border-bottom:0 !important; }
  #tabelSearch tbody tr.sticky-total td{ top: calc(var(--theadH) - 1px) !important; }
  #bodyRows::after{ height: calc(var(--theadH) + var(--totalH) + var(--safeB) - 1px) !important; }
</style>

<script>
  /* ===== Helpers ===== */
  const nfID = new Intl.NumberFormat('id-ID');
  const rp   = n => nfID.format(Number(n||0));
  const pct2 = x => (x==null?'0%':`${(+x).toFixed(2)}%`);
  const isMobile = () => window.matchMedia('(max-width:640px)').matches;
  const apiCall  = (url,opt={}) => (window.apiFetch?window.apiFetch(url,opt):fetch(url,opt));
  const midEllipsis = (s,n=16)=>{ s=String(s??''); if(s.length<=n) return s; const keep=n-1,f=Math.ceil(keep/2),b=Math.floor(keep/2); return s.slice(0,f)+'…'+s.slice(-b); };
  const fmtDate = v => { if(!v) return '-'; const d=new Date(v); if(isNaN(d)) return v; return `${String(d.getDate()).padStart(2,'0')}-${String(d.getMonth()+1).padStart(2,'0')}-${d.getFullYear()}`; };

  /* ===== State ===== */
  let ALL_ROWS=[], VIEW_ROWS=[];
  let BASE={ kode_kantor:'001', closing_date:'', harian_date:'', q:'' };
  let lastReqId=0, aborter=null, debounceTimer;

  // Sorting state
  const SORT = { key:'', asc:true }; // isi key default kalau perlu, contoh: 'hari_menunggak'

  const kodeEl=document.getElementById('kode_kantor');
  const kolekEl=document.getElementById('kolekFilter');
  const closingEl=document.getElementById('closing_date');
  const harianEl=document.getElementById('harian_date');
  const qEl=document.getElementById('q');
  const bodyRows=document.getElementById('bodyRows');
  const bodyTotal=document.getElementById('bodyTotal');
  const loadingBar=document.getElementById('loadingBar');
  const headRow = document.getElementById('headRow');

  function sizeScroller(){
    const wrap=document.getElementById('wrapTable'); if(!wrap) return;
    const top=wrap.getBoundingClientRect().top;
    wrap.style.height=Math.max(260, window.innerHeight-top-10)+'px';
  }
  function syncSticky(){
    const h=headRow?.offsetHeight||38;
    document.getElementById('wrapTable').style.setProperty('--theadH', h+'px');
  }
  window.addEventListener('resize', ()=>{ sizeScroller(); syncSticky(); applyMobileKantorText(); });

  // === NEW: Ambil closing_date dari account_handle (POST), harian_date dari kalender (GET)
  async function getLastDate(){
    try{
      const [rAH, rCal] = await Promise.all([
        apiCall('./api/date/', {
          method:'POST',
          headers:{'Content-Type':'application/json'},
          body: JSON.stringify({ type:'account_handle' }) // sumber closing_date
        }),
        apiCall('./api/date/', { method:'GET' }) // sumber harian_date (kalender)
      ]);

      const jAH  = await rAH.json().catch(()=>({}));
      const jCal = await rCal.json().catch(()=>({}));

      // Prefer field yang biasa kita kirim; siapkan fallback agar robust
      const closing_date =
        jAH?.data?.account_handle_date ||
        jAH?.data?.closing_date ||
        jAH?.data?.last_created || // kalau controller mengembalikan MAX(created)
        null;

      const harian_date =
        jCal?.data?.today ||
        jCal?.data?.current_date ||
        jCal?.data?.last_created ||
        new Date().toISOString().slice(0,10);

      return { closing_date, harian_date };
    }catch{
      return null;
    }
  }

  function applyMobileKantorText(){
    const mobile=isMobile();
    [...kodeEl.options].forEach(o=>{
      const code=o.value?.toString().padStart(3,'0'); if(!code) return;
      if(mobile){ o.textContent=code; }
      else if(o.dataset.full){ o.textContent=o.dataset.full; }
    });
  }
  async function loadKantorOptions(){
    const USER=(window.getUser&&window.getUser())||null;
    const kodeLogin=USER?.kode?String(USER.kode).padStart(3,'0'):'000';
    const isHQ=kodeLogin==='000';
    if(!isHQ){
      kodeEl.innerHTML=`<option value="${kodeLogin}">${kodeLogin}</option>`;
      kodeEl.value=kodeLogin; kodeEl.disabled=true; BASE.kode_kantor=kodeLogin; applyMobileKantorText(); return;
    }
    try{
      const r=await apiCall('./api/kode/',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({type:'kode_kantor'})});
      const j=await r.json();
      const list=(Array.isArray(j.data)?j.data:[])
        .filter(x=>x.kode_kantor&&x.kode_kantor!=='000')
        .sort((a,b)=>String(a.kode_kantor).localeCompare(String(b.kode_kantor)));
      let html='';
      list.forEach(it=>{
        const code=String(it.kode_kantor).padStart(3,'0');
        const name=it.nama_kantor||it.nama_cabang||'';
        const full=`${code} — ${name}`;
        html+=`<option value="${code}" data-full="${full}">${full}</option>`;
      });
      if(!html) html=`<option value="001" data-full="001">001</option>`;
      kodeEl.innerHTML=html; kodeEl.value='001'; BASE.kode_kantor='001'; kodeEl.disabled=false;
      applyMobileKantorText();
    }catch{
      kodeEl.innerHTML=`<option value="001">001</option>`; kodeEl.value='001'; BASE.kode_kantor='001'; kodeEl.disabled=false; applyMobileKantorText();
    }
  }

  function filterByKolek(rows){
    const opt=String(kolekEl.value||'').toUpperCase();
    if(!opt) return rows;
    if(opt==='LUNAS'){ return rows.filter(r => String(r.kolek_update||'').toUpperCase()==='LUNAS'); }
    return rows.filter(r => String(r.kolek_update||'').toUpperCase()===opt);
  }

  // ===== Sorting =====
  function compare(a,b,key){
    // tanggal
    if(key==='tgl_trans_last_angsuran'){
      const A=a[key]?new Date(a[key]).getTime():0;
      const B=b[key]?new Date(b[key]).getTime():0;
      return A-B;
    }
    // string
    if(['nama_nasabah','no_rekening','ao_remedial','plan_bucket','product_code','bucket','bucket_m1','bucket_update','kolek_update'].includes(key)){
      const A=String(a[key] ?? '').toUpperCase();
      const B=String(b[key] ?? '').toUpperCase();
      return A.localeCompare(B);
    }
    // numeric
    const A=Number(a[key] ?? 0), B=Number(b[key] ?? 0);
    return A-B;
  }
  function sortRows(rows){
    if(!SORT.key) return rows;
    const arr=[...rows].sort((a,b)=> compare(a,b,SORT.key));
    return SORT.asc ? arr : arr.reverse();
  }
  function setSortIndicator(){
    // reset
    headRow.querySelectorAll('.th').forEach(th=> th.classList.remove('asc','desc','sorting'));
    if(!SORT.key) return;
    const th=[...headRow.children].find(t => t.dataset.sort===SORT.key);
    if(th){ th.classList.add('sorting'); th.classList.add(SORT.asc?'asc':'desc'); }
  }
  headRow.addEventListener('click', (e)=>{
    const key=e.target?.dataset?.sort; if(!key) return;
    if(SORT.key===key){ SORT.asc=!SORT.asc; }
    else { SORT.key=key; SORT.asc=true; }
    setSortIndicator();
    applyFiltersAndRender();
  });

  // ===== Fetch =====
  async function fetchSearch(){
    if(aborter) try{aborter.abort();}catch{}
    aborter=new AbortController(); const reqId=++lastReqId;

    const payload={ kode_kantor:BASE.kode_kantor, type:'search_detail_handle', closing_date:BASE.closing_date, harian_date:BASE.harian_date, q:BASE.q||'' };

    loadingBar.classList.remove('hidden');
    bodyRows.innerHTML=`<tr><td colspan="18" class="px-4 py-3 text-gray-500">Memuat data...</td></tr>`;
    bodyTotal.innerHTML='';

    try{
      const res=await apiCall('./api/kunjungan/',{method:'POST',headers:{'Content-Type':'application/json','Cache-Control':'no-cache'},body:JSON.stringify(payload),signal:aborter.signal});
      const json=await res.json();
      if(reqId!==lastReqId) return;

      ALL_ROWS=(json?.status===200)?(json?.data?.rows||[]):[];
      applyFiltersAndRender();
    }catch(e){
      if(e.name==='AbortError') return;
      bodyRows.innerHTML=`<tr><td colspan="18" class="px-4 py-3 text-red-600">Gagal memuat data.</td></tr>`;
      setPills(0,0,0);
    }finally{
      if(reqId===lastReqId) loadingBar.classList.add('hidden');
    }
  }

  function applyFiltersAndRender(){
    const filtered=filterByKolek(ALL_ROWS);
    VIEW_ROWS=sortRows(filtered);
    renderRows(VIEW_ROWS);
  }

  function setPills(noa, bd, ck){
    document.getElementById('sumNoa').textContent=nfID.format(noa);
    document.getElementById('sumBD').textContent =rp(bd);
    document.getElementById('sumCK').textContent =rp(ck);
  }
  function isDeviasi(d){
    const ck0=Number(d.ckpn_actual||0)===0;
    const k=String(d.kolek_update||'').toUpperCase();
    return ck0 && k!=='L' && k!=='LUNAS';
  }

  function renderRows(list){
    if(!Array.isArray(list)||!list.length){
      bodyRows.innerHTML=`<tr><td colspan="18" class="px-4 py-3 text-gray-500">Tidak ada data.</td></tr>`;
      setPills(0,0,0); syncSticky(); sizeScroller(); return;
    }
    const sum = key => list.reduce((s,r)=> s + Number(r[key]||0), 0);
    const totalBD = sum('baki_debet_update');
    const totalCK = sum('ckpn_actual');
    setPills(list.length, totalBD, totalCK);

    const tOSC = sum('saldo_bank');
    const tC1  = sum('ckpn_m1');
    const tCA  = totalCK;
    const tINC = sum('inc_ckpn');
    const tAP  = sum('angsuran_pokok');
    const tAB  = sum('angsuran_bunga');

    bodyTotal.innerHTML=`
      <tr class="sticky-total font-semibold">
        <td class="px-3 py-2 freeze-1 col-debitur">TOTAL</td>
        <td class="px-3 py-2 freeze-2 col-norek"></td>
        <td class="px-3 py-2 col-ao"></td>
        <td class="px-3 py-2 col-plan"></td>
        <td class="px-3 py-2 col-prod text-center"></td>
        <td class="px-3 py-2 col-bucketM1"></td>
        <td class="px-3 py-2 col-bucket"></td>
        <td class="px-3 py-2 col-kolek text-center"></td>
        <td class="px-3 py-2 text-right col-num"></td>
        <td class="px-3 py-2 text-right col-amt">${rp(tOSC)}</td>
        <td class="px-3 py-2 text-right col-amt">${rp(totalBD)}</td>
        <td class="px-3 py-2 text-right col-num"></td>
        <td class="px-3 py-2 text-right col-amt">${rp(tC1)}</td>
        <td class="px-3 py-2 text-right col-amt">${rp(tCA)}</td>
        <td class="px-3 py-2 text-right col-amt">${rp(tINC)}</td>
        <td class="px-3 py-2 text-right col-amt">${rp(tAP)}</td>
        <td class="px-3 py-2 text-right col-amt">${rp(tAB)}</td>
        <td class="px-3 py-2 col-date"></td>
      </tr>`;

    let html='';
    for(const d of list){
      const deb=d.nama_nasabah||'-';
      const norek=d.no_rekening||'-';
      const ao=d.ao_remedial||(d.key_name||'-');
      const plan=d.plan_bucket||'-';
      const prodCode=(d.product_code==null?'-':d.product_code);
      const buckM1=d.bucket||d.bucket_m1||'-';
      const buck=d.bucket_update||d.bucket||'-';
      const kol=d.kolek_update||'-';
      const dpd=(d.hari_menunggak===0||d.hari_menunggak)?d.hari_menunggak:'-';
      const osc=Number(d.saldo_bank||0);
      const bd=Number(d.baki_debet_update||0);
      const pd=d.pd_percent==null?null:Number(d.pd_percent);
      const ckM1=Number(d.ckpn_m1||0);
      const ckA=Number(d.ckpn_actual||0);
      const inc=Number(d.inc_ckpn||0);
      const aP=Number(d.angsuran_pokok||0);
      const aB=Number(d.angsuran_bunga||0);
      const tAng=d.tgl_trans_last_angsuran?fmtDate(d.tgl_trans_last_angsuran):'-';
      const dev=isDeviasi(d);

      html+=`
        <tr class="border-b hover:bg-gray-50">
          <td class="px-3 py-2 freeze-1 col-debitur" title="${deb}">${midEllipsis(deb, isMobile()?18:26)}</td>
          <td class="px-3 py-2 freeze-2 col-norek"   title="${norek}">${isMobile()?midEllipsis(norek,12):norek}</td>
          <td class="px-3 py-2 col-ao" title="${ao}">${midEllipsis(ao, isMobile()?18:26)}</td>
          <td class="px-3 py-2 col-plan">${plan}</td>
          <td class="px-3 py-2 col-prod text-center">${prodCode}</td>
          <td class="px-3 py-2 col-bucketM1">${buckM1}</td>
          <td class="px-3 py-2 col-bucket">${buck}</td>
          <td class="px-3 py-2 col-kolek text-center">${kol}</td>
          <td class="px-3 py-2 text-right col-num">${dpd}</td>
          <td class="px-3 py-2 text-right col-amt">${rp(osc)}</td>
          <td class="px-3 py-2 text-right col-amt">${rp(bd)}</td>
          <td class="px-3 py-2 text-right col-num">${pd==null?'-':pct2(pd)}</td>
          <td class="px-3 py-2 text-right col-amt">${ckM1?rp(ckM1):'-'}</td>
          <td class="px-3 py-2 text-right col-amt">
            ${ckA?rp(ckA):'-'}${dev?` <span class="badge-dev" title="CKPN ACT=0 & Kolek≠L → indikasi deviasi">Deviasi</span>`:''}
          </td>
          <td class="px-3 py-2 text-right col-amt">${(ckA||ckM1)?rp(inc):'-'}</td>
          <td class="px-3 py-2 text-right col-amt">${aP?rp(aP):'-'}</td>
          <td class="px-3 py-2 text-right col-amt">${aB?rp(aB):'-'}</td>
          <td class="px-3 py-2 col-date">${tAng}</td>
        </tr>`;
    }
    bodyRows.innerHTML=html;

    syncSticky(); sizeScroller();
    setTimeout(()=>{ syncSticky(); sizeScroller(); }, 60);
  }

  // Events
  kodeEl.addEventListener('change', ()=>{ if(kodeEl.disabled) return; BASE.kode_kantor=kodeEl.value||'001'; fetchSearch(); });
  kolekEl.addEventListener('change', applyFiltersAndRender);
  qEl.addEventListener('input', ()=>{ clearTimeout(debounceTimer); debounceTimer=setTimeout(()=>{ BASE.q=qEl.value.trim(); fetchSearch(); }, 260); });

  // === NEW: Init pakai hasil getLastDate() (closing_date dari account_handle, harian_date dari kalender)
  window.addEventListener('DOMContentLoaded', async ()=>{
    await loadKantorOptions();

    const d=await getLastDate();
    if(d){
      BASE.closing_date = d.closing_date || BASE.closing_date;
      BASE.harian_date  = d.harian_date  || BASE.harian_date;
      closingEl.value   = BASE.closing_date;
      harianEl.value    = BASE.harian_date;
    }

    try{
      const u=new URL(location.href); const q0=u.searchParams.get('q');
      if(q0){ qEl.value=q0; BASE.q=q0; }
    }catch{}

    setSortIndicator(); // tampilkan panah jika SORT.key di-set
    fetchSearch();
  });
</script>

