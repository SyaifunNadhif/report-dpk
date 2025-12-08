<!-- 📊 Migrasi Bucket (DPD) — thead sticky, tbody scroll di modal, horizontal scroll, TglTagih + sorting -->
<div class="max-w-7xl mx-auto px-4 py-5" id="MB_root">
  <h1 id="MB_title" class="font-bold mb-3 flex items-center gap-2">
    <span>📊</span><span>Migrasi Bucket (DPD)</span>
  </h1>

  <!-- Filter -->
  <form id="MB_formFilter" class="filter-wrap mb-3">
    <div class="field">
      <label class="text-xs" for="MB_closing">Closing</label>
      <input type="date" id="MB_closing" class="border rounded px-2 py-1 text-sm h-8" required>
    </div>
    <div class="field">
      <label class="text-xs" for="MB_harian">Harian</label>
      <input type="date" id="MB_harian" class="border rounded px-2 py-1 text-sm h-8" required>
    </div>
    <div class="field">
      <label class="text-xs" for="MB_optKantor">Cabang</label>
      <select id="MB_optKantor" class="border rounded px-2 py-1 text-sm h-8 min-w-[200px]">
        <option value="">Konsolidasi (Semua Cabang)</option>
      </select>
    </div>
    <button id="MB_btnFilter" type="submit" class="btn-icon h-8 w-9" title="Filter" aria-label="Terapkan filter">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <circle cx="11" cy="11" r="7" stroke-width="2"></circle>
        <line x1="21" y1="21" x2="16.65" y2="16.65" stroke-width="2" stroke-linecap="round"></line>
      </svg>
    </button>
  </form>

  <!-- Ringkasan -->
  <div id="MB_summary" class="space-y-1.5 mb-2 hidden">
    <div class="flex flex-wrap items-center gap-2 text-[13px]">
      <span id="MB_chip_m1"        class="pill pill-blue">Grand OS M-1: <b id="MB_grand_m1">0</b></span>
      <span id="MB_chip_actual"    class="pill pill-green">Actual: <b id="MB_os_actual_an">0</b></span>
      <span id="MB_chip_realisasi" class="pill pill-purple">Realisasi OS: <b id="MB_realisasi_os">0</b></span>
      <span id="MB_chip_lunas"     class="pill pill-sky">Lunas (O): <b id="MB_total_lunas">0</b></span>
      <span id="MB_chip_runoff"    class="pill pill-emerald">Run Off: <b id="MB_total_runoff">0</b></span>
      <span class="text-[11px] text-gray-500 ml-1">* OS tampil dalam <b>ribuan</b></span>
    </div>
  </div>

  <!-- Loading -->
  <div id="MB_loading" class="hidden flex items-center gap-2 text-sm text-gray-600 mb-2">
    <svg class="animate-spin h-4 w-4 text-blue-600" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="CurrentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span>Memuat data migrasi bucket...</span>
  </div>

  <!-- Matriks -->
  <div id="MB_tblWrap" class="overflow-auto border rounded shadow-sm relative bg-white" style="max-height:68vh;">
    <table id="MB_table" class="min-w-full text-center table-fixed">
      <thead id="MB_thead" class="bg-gray-100 text-gray-800"></thead>
      <tbody id="MB_tbody" class="text-gray-900"></tbody>
    </table>
  </div>
</div>

<!-- Modal Detail -->
<div id="MB_modal" class="fixed inset-0 hidden bg-black/50 z-[99999]">
  <div id="MB_modalCard" class="bg-white max-w-[min(1200px,96vw)] w-[96vw] mx-auto mt-8 rounded shadow-2xl overflow-hidden">
    <div class="px-3 py-2 border-b flex items-center justify-between sticky top-0 bg-white z-[100000]">
      <h3 id="MB_modalTitle" class="font-semibold text-sm">Detail Debitur</h3>
      <button id="MB_modalClose" class="text-slate-600 hover:text-slate-900 text-xl leading-none">&times;</button>
    </div>

    <!-- Ringkasan total (tidak sticky) -->
    <div id="MB_modalTotals" class="px-3 py-2 bg-gray-50 border-b text-[12px] grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2"></div>

    <!-- Isi tabel (thead fixed, tbody scroll & bisa geser kanan) -->
    <div id="MB_modalTableWrap" class="px-3 pb-3 overflow-x-auto">
      <table id="MB_modalTable" class="text-xs table-fixed border">
        <thead id="MB_modalThead" class="bg-gray-100"></thead>
        <tbody id="MB_modalTbody"></tbody>
      </table>
    </div>
  </div>
</div>

<style>
  :root { --colFrom:8.4rem; --col2:6.2rem; --colN:5.6rem; }
  @media (max-width:1280px){ :root { --colFrom:7.8rem; --col2:5.8rem; --colN:5.1rem; } }
  @media (max-width:1024px){ :root { --colFrom:7.2rem; --col2:5.4rem; --colN:4.7rem; } }
  @media (max-width:768px){  :root { --colFrom:6.8rem; --col2:5.1rem; --colN:4.4rem; } }
  @media (max-width:640px){  :root { --colFrom:6.4rem; --col2:4.8rem; --colN:4.1rem; } }

  /* Font tabel matriks */
  #MB_table{font-size:.80rem; line-height:1.1;}
  @media (max-width:1280px){ #MB_table{font-size:.78rem} }
  @media (max-width:1024px){ #MB_table{font-size:.74rem} }
  @media (max-width:768px){  #MB_table{font-size:.70rem} }
  @media (max-width:640px){  #MB_table{font-size:.62rem} }
  .from-shrink{ font-size:.92em; }

  #MB_title{font-size:1.26rem}
  @media (max-width:768px){ #MB_title{font-size:1.12rem} }
  @media (max-width:640px){ #MB_title{font-size:1.00rem} }

  /* Pills & Filter */
  .pill{display:inline-block;padding:5px 8px;border-radius:8px;border:1px solid}
  .pill-blue{background:#eff6ff;color:#1e40af;border-color:#bfdbfe}
  .pill-emerald{background:#ecfdf5;color:#065f46;border-color:#a7f3d0}
  .pill-purple{background:#faf5ff;color:#6b21a8;border-color:#e9d5ff}
  .pill-sky{background:#e0f2fe;color:#075985;border-color:#bae6fd}
  .pill-green{background:#e8fff2;color:#065f46;border-color:#bbf7d0}
  .filter-wrap{display:flex;flex-wrap:wrap;align-items:end;gap:10px}
  .field{display:flex;align-items:center;gap:6px}
  .btn-icon{display:flex;align-items:center;justify-content:center;background:#2563eb;color:#fff;border-radius:8px}
  .btn-icon:hover{background:#1d4ed8}

  /* Lebar kolom matriks */
  .col-from{width:var(--colFrom);min-width:var(--colFrom);max-width:var(--colFrom)}
  .col-6  {width:var(--col2);   min-width:var(--col2);   max-width:var(--col2)}
  .col-N  {width:var(--colN);   min-width:var(--colN);   max-width:var(--colN)}

  /* Angka (ribuan) */
  .num-wrap{min-width:0;max-width:100%;display:flex;justify-content:flex-end;align-items:flex-end;}
  .num{display:inline-block;white-space:nowrap;font-variant-numeric:tabular-nums;font-feature-settings:"tnum";line-height:1.05;
       font-size:clamp(.62rem,calc(.92rem - .02rem*(var(--d,7)-7)),.92rem);}
  @media (max-width:640px){ .num{font-size:clamp(.46rem,calc(.76rem - .025rem*(var(--d,7)-7)),.76rem)} }
  @media (max-width:480px){ .num{font-size:clamp(.44rem,calc(.72rem - .025rem*(var(--d,7)-7)),.72rem)} }
  @media (max-width:360px){ .num{font-size:clamp(.42rem,calc(.68rem - .025rem*(var(--d,7)-7)),.68rem)} }

  .cell-sub{display:block;font-size:.64rem;color:#6b7280;margin-top:2px;line-height:1.05}
  .cell-sub:empty{display:none}
  .cell-link{color:inherit;text-decoration:underline;text-decoration-style:dotted;cursor:pointer}
  .nowrap{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

  /* Sticky header matriks utama */
  #MB_tblWrap{ isolation:isolate; }
  #MB_tblWrap thead th{
    position:sticky; top:0;
    z-index:10; background:#f3f4f6;
    border-bottom:1px solid #e5e7eb; box-shadow:0 1px 0 0 #e5e7eb;
  }
  thead .sticky-col-1{ z-index:60 !important; left:0; background:#f3f4f6; box-shadow:2px 0 0 0 #e5e7eb;}
  thead .sticky-col-2{ z-index:50 !important; left:var(--colFrom); background:#f3f4f6; box-shadow:2px 0 0 0 #e5e7eb;}
  tbody .sticky-col-1{ position:sticky; left:0;             z-index:40; background:#fff; box-shadow:2px 0 0 0 #e5e7eb;}
  tbody .sticky-col-2{ position:sticky; left:var(--colFrom); z-index:30; background:#fff; box-shadow:2px 0 0 0 #e5e7eb;}

  thead th, tbody td{ padding:.34rem .38rem; }
  @media (max-width:640px){ thead th, tbody td{ padding:.26rem .32rem; } }

  .th-sub{ font-size:.62rem; }
  @media (max-width:640px){ .th-sub{ font-size:.56rem; } }

  /* Ringkasan mobile */
  @media (max-width:640px){
    #MB_chip_m1, #MB_chip_realisasi, #MB_chip_lunas{ display:none; }
    #MB_summary .pill{ padding:4px 7px; font-size:12px; }
    .cell-sub{ display:none; }
  }
  /* Filter mobile grid */
  @media (max-width:640px){
    .filter-wrap{display:grid;grid-template-columns:1fr 1fr;gap:8px 10px;align-items:end}
    .field{flex-direction:column;align-items:stretch;gap:4px}
    .field label{display:none}
    #MB_formFilter input,#MB_formFilter select{width:100%}
    .filter-wrap .field:nth-child(1){grid-column:1}
    .filter-wrap .field:nth-child(2){grid-column:2}
    .filter-wrap .field:nth-child(3){grid-column:1}
    .filter-wrap #MB_btnFilter{grid-column:2;justify-self:start;height:32px;min-width:38px}
  }

  /* Flow coloring */
  .flow-worse   { background:#fee2e2; }
  .flow-worse:hover{ background:#fecaca; }
  .flow-better  { background:#ecfdf5; }
  .flow-better:hover{ background:#d1fae5; }

  /* ===== Modal detail: THEAD fixed, TBODY scroll, horizontal scroll ===== */

  #MB_modalTableWrap{ overflow-x:auto; -webkit-overflow-scrolling:touch; }

  #MB_modalTable{ table-layout:fixed; border-collapse:collapse; width:max-content; min-width:100%; }
  #MB_modalTable thead, #MB_modalTable tbody{ display:block; width:max-content; }
  #MB_modalTable thead tr, #MB_modalTable tbody tr{ display:table; table-layout:fixed; width:max-content; }

  #MB_modalThead tr th{ position:sticky; top:0; z-index:2; background:#f3f4f6; }

  #MB_modalTbody{ max-height:72vh; overflow-y:auto; overflow-x:visible; }
  #MB_modalTbody tr.sticky-total td{
    position:sticky; top:0; z-index:1; background:#fffbeb; box-shadow:0 1px 0 0 #e5e7eb; font-weight:600;
  }

  /* === Kunci lebar kolom detail ===
     Semua kolom dikurangi 1.5rem, KECUALI Alamat (tidak dikurangi) */
  #MB_modalTable{
    --w-sm: 6.5rem;   /* KC, KOL, HM, HMP, dll */
    --w-md: 9.0rem;   /* Norek, OS, Angs, dsb */
    --w-lg: 14.0rem;  /* Nama */
    --w-lgA: 18.0rem; /* Alamat (tidak dishrink) */
    --shrink: 1.5rem; /* pengurang global */
  }
  /* apply shrink */
  .col-sm{ min-width:calc(var(--w-sm) - var(--shrink)); max-width:calc(var(--w-sm) - var(--shrink)); }
  .col-md{ min-width:calc(var(--w-md) - var(--shrink)); max-width:calc(var(--w-md) - var(--shrink)); }
  .col-lg{ min-width:calc(var(--w-lg) - var(--shrink)); max-width:calc(var(--w-lg) - var(--shrink)); }
  /* alamat tidak dishrink */
  .col-lgA{ min-width:var(--w-lgA); max-width:var(--w-lgA); }

  /* konten sel rapi & anti mletot */
  #MB_modalTable th, #MB_modalTable td{
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
  }

  /* indikator sort di thead */
  .th-sort{cursor:pointer;user-select:none;}
  .th-sort:after{content:" ⬍";font-size:.8em;color:#9ca3af;}
  .th-sort.asc:after{content:" ▲";color:#374151;}
  .th-sort.desc:after{content:" ▼";color:#374151;}
</style>

<script>
(() => {
  // ===== helpers =====
  const nfID   = new Intl.NumberFormat('id-ID');
  const SCALE  = 1000;
  const fmtK = n => nfID.format(Math.round(Number(n||0)/SCALE));
  const num  = v => Number(v||0);
  const pct2 = v => (v==null || isNaN(v) || Number(v)===0 ? '' : `${Number(v).toFixed(2)}%`);
  const pick = (o, keys, d=0) => { for (const k of keys){ if (o && o[k]!=null) return o[k]; } return d; };
  const $ = s => document.querySelector(s);
  const digitLen = s => String(s).replace(/[^\d]/g,'').length;
  const cut = (s,n)=>{ s=String(s||''); return s.length<=n ? s : (s.slice(0,n).trimEnd()+'…'); };

  function numHTML(val){
    const full  = nfID.format(Number(val||0));
    const short = fmtK(val);
    const d = Math.max(digitLen(short), 1);
    return `<span class="num-wrap" title="${full}"><span class="num" style="--d:${d}">${short}</span></span>`;
  }
  const dashHTML = v => Number(v||0)>0 ? numHTML(v) : '–';

  const DPD_LABEL = {
    A:'A_DPD 0', B:'B_DPD 1-30', C:'C_DPD 31-60', D:'D_DPD 61-90',
    E:'E_DPD 91-120', F:'F_DPD 121-150', G:'G_DPD 151-180', H:'H_DPD 181-210',
    I:'I_DPD 211-240', J:'J_DPD 241-270', K:'K_DPD 271-300', L:'L_DPD 301-330',
    M:'M_DPD 331-360', N:'N_DPD >360', O:'O_LUNAS'
  };
  const shortDPD = c => (DPD_LABEL[c]||c).split('_').slice(1).join(' ');

  const BUCKET_ORDER = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N'];
  const idxBucket = b => (b==='O' ? -1 : BUCKET_ORDER.indexOf(String(b||'').toUpperCase()));

  const elClosing  = $('#MB_closing');
  const elHarian   = $('#MB_harian');
  const elKantor   = $('#MB_optKantor');
  const elHead     = $('#MB_thead');
  const elBody     = $('#MB_tbody');
  const elLoad     = $('#MB_loading');
  const elSummary  = $('#MB_summary');

  const elMod      = $('#MB_modal');
  const elModTitle = $('#MB_modalTitle');
  const elModTotals= $('#MB_modalTotals');
  const elModThead = $('#MB_modalThead');
  const elModTbody = $('#MB_modalTbody');

  document.getElementById('MB_modalClose').onclick = ()=> elMod.classList.add('hidden');
  elMod.addEventListener('click', e=>{ if(!e.target.closest('#MB_modalCard')) elMod.classList.add('hidden'); });
  window.addEventListener('keydown', e=>{ if(e.key==='Escape') elMod.classList.add('hidden'); });

  let ABORT, ABORT_DETAIL;
  let gIsKonsol = true;

  (async function init(){
    // ambil default closing/harian dari API
    const d = await getLastDates();
    if (d){ elClosing.value=d.last_closing; elHarian.value=d.last_created; }
    await populateKantor();

    const user = (window.getUser && window.getUser()) || {};
    const kodeLogin = String(user?.kode||'').padStart(3,'0');
    if (kodeLogin && kodeLogin!=='000'){
      elKantor.value = kodeLogin; elKantor.disabled = true;
      elKantor.classList.add('bg-gray-100','text-gray-500','cursor-not-allowed');
    }
    if (elClosing.value && elHarian.value){
      fetchBucket(elClosing.value, elHarian.value, elKantor.disabled ? elKantor.value : (elKantor.value || null));
    }
  })();

  document.getElementById('MB_formFilter').addEventListener('submit', e=>{
    e.preventDefault();
    fetchBucket(elClosing.value, elHarian.value, elKantor.disabled ? elKantor.value : (elKantor.value || null));
  });
  elKantor.addEventListener('change', ()=>{
    if (!elKantor.disabled && elClosing.value && elHarian.value){
      fetchBucket(elClosing.value, elHarian.value, elKantor.value || null);
    }
  });

  async function getLastDates(){ try{ const r=await fetch('./api/date/'); const j=await r.json(); return j.data||null; }catch{ return null; } }
  async function populateKantor(){
    try{
      const r = await fetch('./api/kode/', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({type:'kode_kantor'})});
      const j = await r.json();
      const list = Array.isArray(j.data)?j.data:[];
      let html = `<option value="">Konsolidasi (Semua Cabang)</option>`;
      list.filter(x=>x.kode_kantor && x.kode_kantor!=='000')
          .sort((a,b)=> String(a.kode_kantor).localeCompare(String(b.kode_kantor)))
          .forEach(it=>{
            const code=String(it.kode_kantor).padStart(3,'0');
            const name=it.nama_kantor||it.nama_cabang||'';
            html += `<option value="${code}">${code} — ${name}</option>`;
          });
      elKantor.innerHTML = html;
    }catch{
      elKantor.innerHTML = `<option value="">Konsolidasi (Semua Cabang)</option>`;
    }
  }

  async function fetchBucket(closing_date, harian_date, kode_kantor){
    if (ABORT) ABORT.abort();
    ABORT = new AbortController();
    gIsKonsol = !kode_kantor;
    elLoad.classList.remove('hidden'); elSummary.classList.add('hidden');
    elHead.innerHTML=''; elBody.innerHTML = `<tr id="MB_row_loading"><td class="py-4 text-center text-gray-500">Memuat data...</td></tr>`;

    try{
      const payload = { type:'migrasi bucket', closing_date, harian_date };
      if (kode_kantor) payload.kode_kantor = kode_kantor;

      const f = (window.apiFetch || fetch);
      const r = await f('./api/kolek/', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload), signal:ABORT.signal });
      const j = await r.json();
      if (j.status !== 200) throw new Error(j.message||'Gagal memuat data');

      renderBucket(j.data || {});
    }catch(e){
      elBody.innerHTML = `<tr id="MB_row_error"><td class="py-4 text-center text-red-600">${e.message||'Gagal memuat data'}</td></tr>`;
    }finally{
      elLoad.classList.add('hidden');
    }
  }

  function renderBucket(data){
    const orderTo = (Array.isArray(data.order_to) && data.order_to.length)
      ? data.order_to : ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O'];

    const matrixArr = Array.isArray(data.matrix) ? data.matrix : [];
    const mtx = {};
    for (const it of matrixArr){
      const f = String(pick(it,['from_bucket','from','dpd_from','bucket_m1'],'')).toUpperCase();
      const t = String(pick(it,['to_bucket','to','dpd_to','bucket_curr'],'')).toUpperCase();
      if (!f || !t) continue;
      if (!mtx[f]) mtx[f] = {};
      mtx[f][t] = {
        os:  Number(pick(it,['os','os_curr','saldo','amount','nilai'],0)),
        noa: Number(pick(it,['noa','count','jumlah'],0)),
        pct: pick(it,['actual_pct','pct'],null)
      };
    }
    const fromTotals = data.from_totals || {};
    const real = data.realisasi || {};
    const rTot = real.total || {noa:0, os:0};
    const rByB = real.by_bucket || {};

    /* ===== HEADER ===== */
    let head = `<tr id="MB_headRow">
      <th class="border px-2 py-2 sticky-col-1 col-from from-shrink">DPD M-1</th>
      <th class="border px-2 py-2 sticky-col-2 col-6">
        OS M-1<br><span class="th-sub text-gray-500">(×1.000)</span>
      </th>`;
    for (const t of orderTo){
      head += `<th class="border px-2 py-2 col-N">→ ${t}<br><span class="th-sub text-gray-500">${shortDPD(t)}</span></th>`;
    }
    head += `<th class="border px-2 py-2 col-N">Run Off<br><span class="th-sub text-gray-500">(Angs + Lunas • ×1.000)</span></th></tr>`;
    elHead.innerHTML = head;

    // akumulasi per kolom tujuan
    const totalByTo = Object.fromEntries(orderTo.map(t=>[t,0]));
    for (const t of orderTo){ totalByTo[t] += num((rByB[t]||{}).os || 0); }
    let grand_m1_os = 0, grand_lunas = 0, grand_runoff=0;

    // rows by FROM (tanpa O)
    const fromOrder = orderTo.filter(x=>x!=='O');
    const rowsHtml = [];
    for (const f of fromOrder){
      const ft = fromTotals[f] || {};
      const os_m1  = num(pick(ft,['os_m1','saldo_m1','prev_os'],0));
      grand_m1_os += os_m1;

      let sumNonO = 0, lunas=0;
      const cellsHtml = [];
      for (const t of orderTo){
        const c = (mtx[f] && mtx[f][t]) || {os:0,noa:0,pct:null};
        totalByTo[t] += num(c.os);
        if (t==='O') lunas += num(c.os); else sumNonO += num(c.os);

        // kelas flow
        let flowCls = '';
        const fi = idxBucket(f), ti = idxBucket(t);
        if (t==='RUNOFF' || t==='O') flowCls = 'flow-better';
        else if (fi>=0 && ti>=0){ flowCls = (ti>fi) ? 'flow-worse' : (ti<fi ? 'flow-better' : ''); }

        const sub = [];
        if (Number(c.noa||0)>0) sub.push(new Intl.NumberFormat('id-ID').format(c.noa)+' NOA');
        if (c.pct!=null && !isNaN(c.pct) && Number(c.pct)!==0) sub.push(Number(c.pct).toFixed(2)+'%');

        cellsHtml.push(`<td class="border px-2 py-2 text-right col-N ${flowCls}">
          ${linkCell(f,t,c.os)}<span class="cell-sub">${sub.join(' • ')}</span>
        </td>`);
      }
      grand_lunas += lunas;
      const runoff = Math.max(0, os_m1 - sumNonO);
      grand_runoff += runoff;

      rowsHtml.push(`<tr class="bg-white hover:bg-gray-50">
          <td class="border px-3 py-2 text-left sticky-col-1 col-from from-shrink">${DPD_LABEL[f]||f}</td>
          <td class="border px-2 py-2 text-right sticky-col-2 col-6">${dashHTML(os_m1)}</td>
          ${cellsHtml.join('')}
          <td class="border px-2 py-2 text-right col-N flow-better">${linkCell(f,'RUNOFF',runoff)}</td>
        </tr>`);
    }

    // TOTAL + REALISASI
    const totalActualAN = orderTo.filter(t=>t!=='O').reduce((s,t)=> s + num(totalByTo[t]||0), 0);
    let totalRow = `<tr id="MB_row_total" class="bg-yellow-50 font-semibold">
      <td class="border px-3 py-2 text-left sticky-col-1 col-from from-shrink">TOTAL</td>
      <td class="border px-2 py-2 text-right sticky-col-2 col-6">${dashHTML(grand_m1_os)}</td>`;
    for (const t of orderTo){ totalRow += `<td class="border px-2 py-2 text-right col-N">${dashHTML(totalByTo[t])}</td>`; }
    totalRow += `<td class="border px-2 py-2 text-right col-N">${dashHTML(grand_runoff)}</td></tr>`;

    let realRow = `<tr id="MB_row_realisasi" class="bg-indigo-50 font-semibold">
      <td class="border px-3 py-2 text-left sticky-col-1 col-from from-shrink">Realisasi (akun baru)</td>
      <td class="border px-2 py-2 text-center sticky-col-2 col-6">–</td>`;
    for (const t of orderTo){
      const robj = rByB[t] || {noa:0, os:0};
      realRow += `<td class="border px-2 py-2 text-right col-N">${numHTML(robj.os)}<span class="cell-sub">${new Intl.NumberFormat('id-ID').format(robj.noa)} NOA</span></td>`;
    }
    realRow += `<td class="border px-2 py-2 text-center col-N">–</td></tr>`;

    elBody.innerHTML = totalRow + realRow + rowsHtml.join('');

    // summary
    $('#MB_grand_m1').textContent      = fmtK(grand_m1_os);
    $('#MB_os_actual_an').textContent  = fmtK(totalActualAN);
    $('#MB_realisasi_os').textContent  = fmtK(num(rTot.os||0));
    $('#MB_total_lunas').textContent   = fmtK(grand_lunas);
    $('#MB_total_runoff').textContent  = fmtK(grand_runoff);
    elSummary.classList.remove('hidden');
  }

  function linkCell(from_bucket, to_bucket, val){
    const n = Number(val||0);
    if (n<=0) return '–';
    if (to_bucket === 'RUNOFF' || gIsKonsol) return numHTML(n);
    return `<a href="#" class="cell-link" onclick="return MB_openDetail('${from_bucket}','${to_bucket}')">${numHTML(n)}</a>`;
  }

  // ===== Modal Detail (thead sticky + tbody scroll + sorting + TglTagih + horizontal scroll) =====
  window.MB_openDetail = async function(from_bucket, to_bucket){
    if (ABORT_DETAIL) ABORT_DETAIL.abort();
    ABORT_DETAIL = new AbortController();

    const closing = elClosing.value, harian = elHarian.value;
    const kode = elKantor.disabled ? elKantor.value : (elKantor.value || null);

    elModTitle.textContent = `Detail Debitur — ${from_bucket} → ${to_bucket}`;
    elMod.classList.remove('hidden');

    try{
      const payload = { type:'detail debutir migrasi', closing_date:closing, harian_date:harian, from_bucket, to_bucket };
      if (kode) payload.kode_kantor = kode;
      const f = (window.apiFetch || fetch);
      const r = await f('./api/kolek/', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload), signal:ABORT_DETAIL.signal });
      const j = await r.json();
      const list0 = Array.isArray(j?.data) ? j.data : (Array.isArray(j) ? j : []);
      if (!list0.length){
        elModTotals.innerHTML = '';
        elModThead.innerHTML = '';
        elModTbody.innerHTML = `<tr><td class="px-2 py-2">Tidak ada data.</td></tr>`;
        return false;
      }

      // derive TglTagih (1–31 dari tgl_jatuh_tempo)
      const list = list0.map(d=>{
        const t = d?.tgl_jatuh_tempo ? new Date(d.tgl_jatuh_tempo) : null;
        return { ...d, tgl_tagih: t && !isNaN(t) ? t.getDate() : null };
      });

      const sum = k => list.reduce((s,d)=> s + Number(d?.[k]||0), 0);
      const nf = new Intl.NumberFormat('id-ID');
      const total = {
        noa: list.length,
        os_m1: sum('os_m1'),
        os_curr: sum('os_curr'),
        angs_p: sum('angsuran_pokok'),
        angs_b: sum('angsuran_bunga'),
        ckpn: sum('ckpn_actual'),
        ckpn_m1: sum('ckpn_m1'),
        tung_p: sum('tunggakan_pokok'),
        tung_b: sum('tunggakan_bunga'),
      };

      // ringkasan kotak atas
      elModTotals.innerHTML = `
        <div class="px-2 py-1 bg-white border rounded"><b>NOA</b><div>${nf.format(total.noa)}</div></div>
        <div class="px-2 py-1 bg-white border rounded"><b>OS M-1</b><div>${nf.format(total.os_m1)}</div></div>
        <div class="px-2 py-1 bg-white border rounded"><b>OS Actual</b><div>${nf.format(total.os_curr)}</div></div>
        <div class="px-2 py-1 bg-white border rounded"><b>Angs. Pokok</b><div>${nf.format(total.angs_p)}</div></div>
        <div class="px-2 py-1 bg-white border rounded"><b>Angs. Bunga</b><div>${nf.format(total.angs_b)}</div></div>
        <div class="px-2 py-1 bg-white border rounded"><b>CKPN</b><div>${nf.format(total.ckpn)}</div></div>
      `;

      // === DEF kolom (Alamat pakai size 'lgA' agar TIDAK dishrink, lainnya shrink 1.5rem)
      const cols = [
        ['kode_cabang','KC','sm','text'],
        ['no_rekening','Norek','md','text'],
        ['nama_nasabah','Nama','lg','text'],   // shrink -1.5rem + truncate 20
        ['alamat','Alamat','lgA','text'],      // TIDAK dishrink + truncate 30
        ['kolektibilitas','KOL','sm','text'],
        ['tunggakan_pokok','TPok','md','num'],
        ['tunggakan_bunga','TBng','md','num'],
        ['hari_menunggak','HM','sm','num'],
        ['hari_menunggak_pokok','HMP','sm','num'],
        ['hari_menunggak_bunga','HMB','sm','num'],
        ['tgl_jatuh_tempo','JtTmp','md','text'],
        ['tgl_tagih','TglTagih','sm','num'],
        ['os_m1','OS_M1','md','num'],
        ['os_curr','OS_Act','md','num'],
        ['angsuran_pokok','AngsP','md','num'],
        ['angsuran_bunga','AngsB','md','num'],
        ['ckpn_actual','CKPN','md','num'],
        ['ckpn_m1','ckpn_m1','md','num']
      ];

      // THEAD (sortable)
      elModThead.innerHTML = `<tr>${
        cols.map(([key,label,sz,type]) =>
          `<th class="px-2 py-2 border col-${sz} nowrap th-sort" data-key="${key}" data-type="${type||'text'}" title="${label}">${label}</th>`
        ).join('')
      }</tr>`;

      // TOTAL row (sticky di tbody)
      const totalCells = cols.map(([key,label,sz])=>{
        let v = '';
        if (key==='kode_cabang') v = 'TOTAL';
        else if (key==='no_rekening') v = nf.format(total.noa)+' Akun';
        else if (['os_m1','os_curr','angsuran_pokok','angsuran_bunga','ckpn_actual','ckpn_m1','tunggakan_pokok','tunggakan_bunga'].includes(key)){
          const mapKey = ({os_m1:'os_m1',os_curr:'os_curr',angsuran_pokok:'angs_p',angsuran_bunga:'angs_b',
                           ckpn_actual:'ckpn',ckpn_m1:'ckpn_m1',tunggakan_pokok:'tung_p',tunggakan_bunga:'tung_b'})[key];
          v = nf.format(total[mapKey]);
        }
        return `<td class="px-2 py-1 border col-${sz} nowrap">${String(v)}</td>`;
      }).join('');

      // DATA rows (truncate Nama 20, Alamat 30)
      const rowHtml = d=>{
        let h = '<tr class="border-b hover:bg-gray-50">';
        for (const [key,label,sz,type] of cols){
          let v = d[key]; if (v==null) v='';
          // truncate rules
          if (key==='nama_nasabah') v = cut(v,20);
          if (key==='alamat')       v = cut(v,30);
          const raw = (type==='num') ? Number(d[key]||0) : String(d[key]||'');
          const shown = (type==='num') ? nf.format(raw) : String(v);
          h += `<td class="px-2 py-1 border col-${sz} nowrap" data-key="${key}" data-raw="${raw}" title="${String(d[key]??'')}">${String(shown)}</td>`;
        }
        h += '</tr>';
        return h;
      };

      // RENDER TBODY: total (sticky) + data
      elModTbody.innerHTML = `<tr class="sticky-total bg-yellow-50">${totalCells}</tr>` + list.map(rowHtml).join('');

      // SORTING
      const thEls = elModThead.querySelectorAll('th.th-sort');
      let sortState = { key:null, dir:1 };
      thEls.forEach(th=>{
        th.addEventListener('click', ()=>{
          const key  = th.dataset.key;
          const type = th.dataset.type || 'text';
          thEls.forEach(t=>t.classList.remove('asc','desc'));
          sortState.dir = (sortState.key===key ? -sortState.dir : 1);
          sortState.key = key;
          th.classList.add(sortState.dir===1 ? 'asc' : 'desc');

          const rows = Array.from(elModTbody.querySelectorAll('tr')).slice(1); // skip TOTAL
          rows.sort((ra, rb)=>{
            const a = ra.querySelector(`td[data-key="${key}"]`)?.getAttribute('data-raw');
            const b = rb.querySelector(`td[data-key="${key}"]`)?.getAttribute('data-raw');
            if (type==='num'){
              return sortState.dir * (Number(a||0) - Number(b||0));
            } else {
              return sortState.dir * String(a||'').localeCompare(String(b||''), 'id', {numeric:true,sensitivity:'base'});
            }
          });
          rows.forEach(r=>elModTbody.appendChild(r));
        });
      });

    }catch(e){
      console.error('MB_openDetail error:', e);
      if (e.name!=='AbortError') {
        elModTotals.innerHTML = '';
        elModThead.innerHTML = '';
        elModTbody.innerHTML = `<tr><td class="px-2 py-2">Gagal mengambil data.</td></tr>`;
      }
    }
    return false;
  };
})();
</script>
