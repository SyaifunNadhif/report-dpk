<div class="max-w-7xl mx-auto px-4 py-5" id="MB_root">
  
  <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 mb-4">
    <div class="shrink-0">
      <h1 id="MB_title" class="font-bold flex items-center gap-2 text-slate-800 text-xl md:text-2xl">
        <span class="bg-blue-600 text-white p-1.5 rounded-lg shadow-sm text-sm">📊</span>
        <span>Migrasi Bucket (DPD)</span>
      </h1>
      <p class="text-[11px] text-slate-500 mt-1.5 ml-1 font-medium">*Laporan pergerakan DPD M-1 ke Actual</p>
    </div>

    <form id="MB_formFilter" class="bg-white p-2 md:p-3 rounded-xl border border-slate-200 shadow-sm flex flex-wrap items-end gap-3 shrink-0">
      <div class="field flex flex-col gap-1">
        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider ml-1" for="MB_closing">Closing</label>
        <input type="date" id="MB_closing" class="border border-slate-300 rounded-lg px-2 text-sm h-9 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
      </div>
      <div class="field flex flex-col gap-1">
        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider ml-1" for="MB_harian">Harian</label>
        <input type="date" id="MB_harian" class="border border-slate-300 rounded-lg px-2 text-sm h-9 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
      </div>
      <div class="field flex flex-col gap-1">
        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider ml-1" for="MB_optKantor">Cabang</label>
        <select id="MB_optKantor" class="border border-slate-300 rounded-lg px-2 text-sm h-9 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition min-w-[180px]">
          <option value="">Konsolidasi (Semua Cabang)</option>
        </select>
      </div>
      <div class="flex gap-2 mt-auto">
          <button id="MB_btnFilter" type="submit" class="btn-icon h-9 w-10 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-sm transition flex items-center justify-center" title="Terapkan Filter">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65" stroke-linecap="round"></line>
            </svg>
          </button>
          <button type="button" onclick="MB_exportRekap()" class="btn-icon h-9 px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-sm transition flex items-center justify-center gap-2" title="Download Excel Rekap">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
          </button>
      </div>
    </form>
  </div>

  <div id="MB_summary" class="space-y-1.5 mb-3 hidden">
    <div class="flex flex-wrap items-center gap-2 text-[12px] font-medium">
      <span id="MB_chip_m1"        class="pill pill-blue shadow-sm">Grand OS M-1: <b id="MB_grand_m1">0</b></span>
      <span id="MB_chip_actual"    class="pill pill-green shadow-sm">Actual: <b id="MB_os_actual_an">0</b></span>
      <span id="MB_chip_realisasi" class="pill pill-purple shadow-sm">Realisasi OS: <b id="MB_realisasi_os">0</b></span>
      <span id="MB_chip_lunas"     class="pill pill-sky shadow-sm">Lunas (O): <b id="MB_total_lunas">0</b></span>
      <span id="MB_chip_runoff"    class="pill pill-emerald shadow-sm">Run Off: <b id="MB_total_runoff">0</b></span>
      <span class="text-[11px] text-slate-400 ml-1 italic">* OS tampil dalam <b>ribuan</b></span>
    </div>
  </div>

  <div id="MB_loading" class="hidden flex items-center gap-2 text-sm text-blue-600 font-bold mb-3 tracking-wider">
    <svg class="animate-spin h-5 w-5 text-blue-600" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="CurrentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span>MEMUAT MATRIKS...</span>
  </div>

  <div id="MB_tblWrap" class="overflow-auto border border-slate-200 rounded-xl shadow-sm relative bg-white" style="max-height:68vh;">
    <table id="MB_table" class="min-w-full text-center table-fixed text-xs">
      <thead id="MB_thead" class="bg-slate-50 text-slate-600 font-semibold uppercase text-[10px] tracking-wider"></thead>
      <tbody id="MB_tbody" class="text-slate-700"></tbody>
    </table>
  </div>
</div>

<div id="MB_modal" class="fixed inset-0 hidden bg-slate-900/60 backdrop-blur-sm z-[99999] flex items-center justify-center px-4">
  <div id="MB_modalCard" class="bg-white max-w-[min(1500px,96vw)] w-[96vw] h-[90vh] flex flex-col rounded-2xl shadow-2xl overflow-hidden animate-scale-up">
    
    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between shrink-0">
      <div>
        <h3 id="MB_modalTitle" class="font-bold text-slate-800 text-lg flex items-center gap-2">Detail Debitur</h3>
        <p id="MB_modalSubtitle" class="text-[11px] text-slate-500 font-mono mt-0.5"></p>
      </div>
      <div class="flex items-center gap-2">
        <button onclick="MB_exportDetail()" class="flex items-center gap-2 px-4 h-9 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-sm transition">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
          <span class="text-xs font-bold uppercase tracking-wide">Excel Detail</span>
        </button>
        <button id="MB_modalClose" class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-200 hover:bg-red-500 hover:text-white text-slate-600 transition font-bold text-xl leading-none">&times;</button>
      </div>
    </div>

    <div id="MB_modalTotals" class="px-5 py-3 bg-white border-b border-slate-100 text-[12px] flex flex-wrap gap-3 shrink-0"></div>

    <div id="MB_modalTableWrap" class="flex-1 px-4 py-3 overflow-auto bg-slate-50 relative">
      <table id="MB_modalTable" class="w-max min-w-full text-xs text-left border border-slate-200 rounded-lg bg-white overflow-hidden shadow-sm table-fixed">
        <thead id="MB_modalThead" class="bg-slate-100 text-slate-600 uppercase text-[10px] tracking-wider"></thead>
        <tbody id="MB_modalTbody" class="text-slate-700"></tbody>
      </table>
    </div>
  </div>
</div>

<style>
  /* Animasi Modal */
  @keyframes scaleUp { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
  .animate-scale-up { animation: scaleUp 0.2s ease-out forwards; }

  :root { --colFrom:8.4rem; --col2:6.8rem; --colN:5.6rem; }
  
  /* Pills */
  .pill { padding: 4px 10px; border-radius: 6px; border: 1px solid; display: inline-block; }
  .pill-blue { background: #eff6ff; color: #1e40af; border-color: #bfdbfe; }
  .pill-emerald { background: #ecfdf5; color: #065f46; border-color: #a7f3d0; }
  .pill-purple { background: #faf5ff; color: #6b21a8; border-color: #e9d5ff; }
  .pill-sky { background: #e0f2fe; color: #075985; border-color: #bae6fd; }
  .pill-green { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }

  /* Matriks Base Setup (FIX STICKY BUG OVERLAP) */
  #MB_table { table-layout: fixed; border-collapse: separate; border-spacing: 0; min-width: 100%; width: max-content; }
  
  .col-from { width: var(--colFrom); min-width: var(--colFrom); max-width: var(--colFrom); }
  .col-6 { width: var(--col2); min-width: var(--col2); max-width: var(--col2); }
  .col-N { width: var(--colN); min-width: var(--colN); max-width: var(--colN); }

  /* Z-INDEX LAYERING FIX */
  #MB_table thead th { position: sticky; top: 0; z-index: 20; background: #f8fafc; border-bottom: 2px solid #cbd5e1; border-right: 1px solid #e2e8f0; padding: 10px 6px; }
  
  .sticky-col-1 { position: sticky; left: 0; z-index: 10; background: #fff; border-right: 1px solid #cbd5e1; }
  .sticky-col-2 { position: sticky; left: var(--colFrom); z-index: 10; background: #fff; border-right: 2px solid #cbd5e1; }
  
  #MB_table thead th.sticky-col-1 { z-index: 30; background: #f8fafc; }
  #MB_table thead th.sticky-col-2 { z-index: 30; background: #f8fafc; }

  /* FREEZE BARIS TOTAL DI BAWAH THEAD */
  #MB_row_total td { 
      position: sticky; 
      top: 41px; /* Posisi nempel pas di bawah thead */
      z-index: 25; 
      background: #eff6ff !important; 
      border-bottom: 2px solid #bfdbfe; 
      box-shadow: 0 2px 4px -2px rgba(0,0,0,0.1);
  }
  #MB_row_total td.sticky-col-1 { z-index: 35 !important; background: #eff6ff !important; }
  #MB_row_total td.sticky-col-2 { z-index: 35 !important; background: #eff6ff !important; border-right: 2px solid #bfdbfe; }

  #MB_tbody tr { background: #fff; }
  #MB_tbody tr.bg-indigo-50 { background: #f8fafc !important; }
  #MB_tbody tr:hover td { background: #f1f5f9; }
  
  #MB_tbody td { padding: 8px 6px; border-bottom: 1px solid #f1f5f9; border-right: 1px solid #f1f5f9; }

  /* Angka & Link */
  .num-wrap { display: flex; justify-content: flex-end; }
  .num { font-variant-numeric: tabular-nums; font-size: 13px; }
  .cell-sub { display: block; font-size: 9px; color: #64748b; margin-top: 2px; }
  .cell-sub:empty { display: none; }
  .cell-link { color: inherit; font-weight: 700; cursor: pointer; transition: 0.2s; }
  .cell-link:hover { color: #2563eb; text-decoration: underline; }

  .flow-worse { background: #fef2f2; color: #b91c1c; }
  .flow-better { background: #f0fdf4; color: #15803d; }

  /* Modal Tabel Kerapian */
  #MB_modalThead th { position: sticky; top: 0; z-index: 10; background: #f1f5f9; border-bottom: 2px solid #cbd5e1; padding: 10px 12px; }
  #MB_modalTbody tr.sticky-total td { position: sticky; top: 34px; z-index: 9; background: #eff6ff; box-shadow: 0 1px 0 0 #bfdbfe; font-weight: 700; color: #1e40af; }
  #MB_modalTable td { padding: 8px 12px; border-right: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; }

  #MB_modalTable{
    --w-sm: 6.5rem;   
    --w-md: 9.5rem;   
    --w-lg: 14.0rem;  
    --w-lgA: 18.0rem; 
    --shrink: 1.5rem; 
  }
  .col-sm{ min-width:calc(var(--w-sm) - var(--shrink)); max-width:calc(var(--w-sm) - var(--shrink)); }
  .col-md{ min-width:calc(var(--w-md) - var(--shrink)); max-width:calc(var(--w-md) - var(--shrink)); }
  .col-lg{ min-width:calc(var(--w-lg) - var(--shrink)); max-width:calc(var(--w-lg) - var(--shrink)); }
  .col-lgA{ min-width:var(--w-lgA); max-width:var(--w-lgA); }

  #MB_modalTable th, #MB_modalTable td{ white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

  .th-sort { cursor: pointer; transition: 0.2s; }
  .th-sort:hover { background: #e2e8f0; }
  .th-sort:after { content: " ⬍"; font-size: 10px; color: #9ca3af; margin-left: 4px; }
  .th-sort.asc:after { content: " ▲"; color: #2563eb; }
  .th-sort.desc:after { content: " ▼"; color: #2563eb; }
</style>

<script>
(() => {
  // ===== HELPERS =====
  const nfID = new Intl.NumberFormat('id-ID');
  const SCALE = 1000;
  const fmtK = n => nfID.format(Math.round(Number(n||0)/SCALE));
  const num = v => Number(v||0);
  const pick = (o, keys, d=0) => { for(const k of keys){ if(o && o[k]!=null) return o[k]; } return d; };
  const $ = s => document.querySelector(s);
  const digitLen = s => String(s).replace(/[^\d]/g,'').length;
  const cut = (s,n) => { s=String(s||''); return s.length<=n ? s : (s.slice(0,n).trimEnd()+'…'); };

  function numHTML(val){
    const full = nfID.format(Number(val||0));
    const short = fmtK(val);
    const d = Math.max(digitLen(short), 1);
    return `<span class="num-wrap" title="${full}"><span class="num" style="--d:${d}">${short}</span></span>`;
  }
  const dashHTML = v => Number(v||0)>0 ? numHTML(v) : '<span class="text-slate-300">–</span>';

  const DPD_LABEL = {
    A:'A_DPD 0', B:'B_DPD 1-30', C:'C_DPD 31-60', D:'D_DPD 61-90',
    E:'E_DPD 91-120', F:'F_DPD 121-150', G:'G_DPD 151-180', H:'H_DPD 181-210',
    I:'I_DPD 211-240', J:'J_DPD 241-270', K:'K_DPD 271-300', L:'L_DPD 301-330',
    M:'M_DPD 331-360', N:'N_DPD >360', O:'O_LUNAS'
  };
  const shortDPD = c => (DPD_LABEL[c]||c).split('_').slice(1).join(' ');
  const BUCKET_ORDER = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N'];
  const idxBucket = b => (b==='O' ? -1 : BUCKET_ORDER.indexOf(String(b||'').toUpperCase()));

  const elClosing = $('#MB_closing');
  const elHarian = $('#MB_harian');
  const elKantor = $('#MB_optKantor');
  const elHead = $('#MB_thead');
  const elBody = $('#MB_tbody');
  const elLoad = $('#MB_loading');
  const elSummary = $('#MB_summary');

  const elMod = $('#MB_modal');
  const elModTitle = $('#MB_modalTitle');
  const elModSub = $('#MB_modalSubtitle');
  const elModTotals = $('#MB_modalTotals');
  const elModThead = $('#MB_modalThead');
  const elModTbody = $('#MB_modalTbody');

  let currentDetailData = []; 
  let currentFromRaw = '';
  let currentToRaw = '';

  document.getElementById('MB_modalClose').onclick = () => elMod.classList.add('hidden');
  elMod.addEventListener('click', e => { if(!e.target.closest('#MB_modalCard')) elMod.classList.add('hidden'); });
  window.addEventListener('keydown', e => { if(e.key==='Escape') elMod.classList.add('hidden'); });

  let ABORT, ABORT_DETAIL;
  let gIsKonsol = true;

  // INIT
  (async function init(){
    const d = await getLastDates();
    if(d){ elClosing.value=d.last_closing; elHarian.value=d.last_created; }
    await populateKantor();

    const user = (window.getUser && window.getUser()) || {};
    const kodeLogin = String(user?.kode||'').padStart(3,'0');
    if(kodeLogin && kodeLogin!=='000'){
      elKantor.value = kodeLogin; elKantor.disabled = true;
      elKantor.classList.add('bg-slate-100','text-slate-500','cursor-not-allowed');
    }
    if(elClosing.value && elHarian.value){
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
            html += `<option value="${code}">${code} — ${it.nama_kantor||it.nama_cabang||''}</option>`;
          });
      elKantor.innerHTML = html;
    }catch{
      elKantor.innerHTML = `<option value="">Konsolidasi (Semua Cabang)</option>`;
    }
  }

  async function fetchBucket(closing_date, harian_date, kode_kantor){
    if(ABORT) ABORT.abort();
    ABORT = new AbortController();
    gIsKonsol = !kode_kantor;
    elLoad.classList.remove('hidden'); elSummary.classList.add('hidden');
    elHead.innerHTML=''; elBody.innerHTML = `<tr><td class="py-10 text-center text-slate-400 font-medium">Sedang memproses data matriks...</td></tr>`;

    try{
      const payload = { type:'migrasi bucket', closing_date, harian_date };
      if(kode_kantor) payload.kode_kantor = kode_kantor;

      const f = (window.apiFetch || fetch);
      const r = await f('./api/kolek/', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload), signal:ABORT.signal });
      const j = await r.json();
      if(j.status !== 200) throw new Error(j.message||'Gagal memuat data');
      renderBucket(j.data || {});
    }catch(e){
      elBody.innerHTML = `<tr><td class="py-10 text-center text-red-500 font-bold">${e.message||'Gagal memuat data'}</td></tr>`;
    }finally{
      elLoad.classList.add('hidden');
    }
  }

  function renderBucket(data){
    const orderTo = (Array.isArray(data.order_to) && data.order_to.length) ? data.order_to : ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O'];
    const matrixArr = Array.isArray(data.matrix) ? data.matrix : [];
    const mtx = {};
    for(const it of matrixArr){
      const f = String(pick(it,['from_bucket','from','dpd_from','bucket_m1'],'')).toUpperCase();
      const t = String(pick(it,['to_bucket','to','dpd_to','bucket_curr'],'')).toUpperCase();
      if(!f || !t) continue;
      if(!mtx[f]) mtx[f] = {};
      mtx[f][t] = { os: Number(pick(it,['os','os_curr','saldo','amount'],0)), noa: Number(pick(it,['noa','count','jumlah'],0)), pct: pick(it,['actual_pct','pct'],null) };
    }
    const fromTotals = data.from_totals || {};
    const real = data.realisasi || {};
    const rTot = real.total || {noa:0, os:0};
    const rByB = real.by_bucket || {};

    let head = `<tr id="MB_headRow">
      <th class="sticky-col-1 col-from font-bold tracking-wide">DPD M-1</th>
      <th class="sticky-col-2 col-6">OS M-1 <span class="block text-slate-400 font-normal mt-0.5">(×1.000)</span></th>`;
    for(const t of orderTo) head += `<th class="col-N">→ ${t} <span class="block text-slate-400 font-normal mt-0.5">${shortDPD(t)}</span></th>`;
    head += `<th class="col-N bg-slate-100">Run Off <span class="block text-slate-400 font-normal mt-0.5">(Lunas/Angs)</span></th></tr>`;
    elHead.innerHTML = head;

    const totalByTo = Object.fromEntries(orderTo.map(t=>[t,0]));
    let grand_m1_os = 0, grand_lunas = 0, grand_runoff=0;
    const fromOrder = orderTo.filter(x=>x!=='O');
    const rowsHtml = [];

    for(const f of fromOrder){
      const ft = fromTotals[f] || {};
      const os_m1 = num(pick(ft,['os_m1','saldo_m1'],0));
      grand_m1_os += os_m1;

      let sumNonO = 0, lunas=0;
      const cellsHtml = [];
      for(const t of orderTo){
        const c = (mtx[f] && mtx[f][t]) || {os:0,noa:0,pct:null};
        totalByTo[t] += num(c.os);
        if(t==='O') lunas += num(c.os); else sumNonO += num(c.os);

        let flowCls = '';
        const fi = idxBucket(f), ti = idxBucket(t);
        if(t==='RUNOFF' || t==='O') flowCls = 'flow-better';
        else if(fi>=0 && ti>=0){ flowCls = (ti>fi) ? 'flow-worse' : (ti<fi ? 'flow-better' : ''); }

        const sub = [];
        if(Number(c.noa||0)>0) sub.push(nfID.format(c.noa)+' NOA');
        if(c.pct!=null && !isNaN(c.pct) && Number(c.pct)!==0) sub.push(Number(c.pct).toFixed(2)+'%');

        cellsHtml.push(`<td class="text-right col-N ${flowCls}">${linkCell(f,t,c.os)}<span class="cell-sub">${sub.join(' • ')}</span></td>`);
      }
      grand_lunas += lunas;
      const runoff = Math.max(0, os_m1 - sumNonO);
      grand_runoff += runoff;

      rowsHtml.push(`<tr>
          <td class="text-left sticky-col-1 col-from font-bold text-slate-700">${DPD_LABEL[f]||f}</td>
          <td class="text-right sticky-col-2 col-6 text-slate-800">${dashHTML(os_m1)}</td>
          ${cellsHtml.join('')}
          <td class="text-right col-N bg-emerald-50 text-emerald-700">${linkCell(f,'RUNOFF',runoff)}</td>
        </tr>`);
    }

    let totalRow = `<tr id="MB_row_total" class="text-blue-900">
      <td class="text-left sticky-col-1 col-from font-bold">TOTAL</td>
      <td class="text-right sticky-col-2 col-6 font-bold">${dashHTML(grand_m1_os)}</td>`;
    for(const t of orderTo) totalRow += `<td class="text-right col-N font-bold">${dashHTML(totalByTo[t])}</td>`;
    totalRow += `<td class="text-right col-N font-bold">${dashHTML(grand_runoff)}</td></tr>`;

    let realRow = `<tr id="MB_row_realisasi" class="bg-indigo-50 text-slate-700">
      <td class="text-left sticky-col-1 col-from font-bold">Realisasi (Baru)</td>
      <td class="text-center sticky-col-2 col-6 font-bold">–</td>`;
    for (const t of orderTo){
      const robj = rByB[t] || {noa:0, os:0};
      realRow += `<td class="text-right col-N">${linkCell('REALISASI', t, robj.os)}<span class="cell-sub">${nfID.format(robj.noa)} NOA</span></td>`;
    }
    realRow += `<td class="text-center col-N">–</td></tr>`;

    // Posisi Baris Total dimasukkan paling atas di dalam Body
    elBody.innerHTML = totalRow + realRow + rowsHtml.join('');

    $('#MB_grand_m1').textContent = fmtK(grand_m1_os);
    $('#MB_os_actual_an').textContent = fmtK(orderTo.filter(t=>t!=='O').reduce((s,t)=>s+num(totalByTo[t]),0));
    $('#MB_realisasi_os').textContent = fmtK(num(rTot.os));
    $('#MB_total_lunas').textContent = fmtK(grand_lunas);
    $('#MB_total_runoff').textContent = fmtK(grand_runoff);
    elSummary.classList.remove('hidden');
  }

  // MENGIRIM KODE MURNI (A, B, C, DLL) KE BACKEND!
  function linkCell(from_bucket, to_bucket, val){
    const n = Number(val||0);
    if(n<=0) return '<span class="text-slate-300">–</span>';
    if(to_bucket === 'RUNOFF' || gIsKonsol) return numHTML(n);
    return `<a href="#" class="cell-link" onclick="return MB_openDetail('${from_bucket}','${to_bucket}')">${numHTML(n)}</a>`;
  }

  // ===== MODAL DETAIL MURNI (TANPA CKPN) =====
  window.MB_openDetail = async function(from_raw, to_raw){
    if(ABORT_DETAIL) ABORT_DETAIL.abort();
    ABORT_DETAIL = new AbortController();

    currentFromRaw = from_raw; currentToRaw = to_raw;
    const closing = elClosing.value, harian = elHarian.value;
    const kode = elKantor.disabled ? elKantor.value : (elKantor.value || null);

    // Judul Modal Pakai Label Panjang
    const fLabel = DPD_LABEL[from_raw] || from_raw;
    const tLabel = DPD_LABEL[to_raw] || to_raw;

    elModTitle.innerHTML = `Detail Migrasi <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-md font-mono border border-blue-200 ml-2">${fLabel} ➔ ${tLabel}</span>`;
    $('#MB_modalSubtitle').textContent = `Posisi: ${closing} vs ${harian}`;
    elMod.classList.remove('hidden');

    elModTotals.innerHTML = ''; elModThead.innerHTML = '';
    elModTbody.innerHTML = `<tr><td class="p-12 text-center text-slate-400 font-bold uppercase tracking-widest"><div class="animate-spin h-8 w-8 border-4 border-slate-200 border-t-blue-600 rounded-full mx-auto mb-3"></div>Menarik Data Nasabah...</td></tr>`;

    try{
      // PAYLOAD MENGIRIM HURUF MURNI SESUAI REQUEST (A, B, O, REALISASI)
      const payload = { type:'detail debutir migrasi', closing_date:closing, harian_date:harian, from_bucket: from_raw, to_bucket: to_raw };
      if(kode) payload.kode_kantor = kode;
      
      const f = (window.apiFetch || fetch);
      const r = await f('./api/kolek/', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload), signal:ABORT_DETAIL.signal });
      const j = await r.json();
      currentDetailData = Array.isArray(j?.data) ? j.data : [];
      
      if(!currentDetailData.length) {
        elModTbody.innerHTML = `<tr><td class="px-4 py-8 text-center text-slate-400">Tidak ada debitur pada kriteria ini.</td></tr>`;
        return false;
      }

      currentDetailData = currentDetailData.map(d=>{
        const t = d?.tgl_jatuh_tempo ? new Date(d.tgl_jatuh_tempo) : null;
        return { ...d, tgl_tagih: t && !isNaN(t) ? t.getDate() : null };
      });

      renderDetailTable(currentDetailData);
    }catch(e){
      if(e.name!=='AbortError') elModTbody.innerHTML = `<tr><td class="px-4 py-8 text-center text-red-500 font-bold">Gagal menarik data.</td></tr>`;
    }
    return false;
  };

  function renderDetailTable(list) {
      const sum = k => list.reduce((s,d)=> s + Number(d?.[k]||0), 0);
      const nf = new Intl.NumberFormat('id-ID');
      
      const total = {
        noa: list.length,
        os_m1: sum('os_m1'),
        os_curr: sum('os_curr'),
        angs_p: sum('angsuran_pokok'),
        angs_b: sum('angsuran_bunga')
      };

      // Mini Cards untuk Totals Modal
      elModTotals.innerHTML = `
        <div class="px-3 py-1.5 bg-blue-50 text-blue-900 border border-blue-100 rounded-lg min-w-[100px]"><span class="block text-[10px] uppercase font-bold text-blue-600 mb-0.5">Total NOA</span><b class="text-sm">${nf.format(total.noa)}</b></div>
        <div class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg min-w-[130px] shadow-sm"><span class="block text-[10px] uppercase font-bold text-slate-500 mb-0.5">Total OS M-1</span><b class="text-sm">${nf.format(total.os_m1)}</b></div>
        <div class="px-3 py-1.5 bg-emerald-50 text-emerald-900 border border-emerald-200 rounded-lg min-w-[130px] shadow-sm"><span class="block text-[10px] uppercase font-bold text-emerald-600 mb-0.5">Total OS Actual</span><b class="text-sm">${nf.format(total.os_curr)}</b></div>
        <div class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg min-w-[120px] shadow-sm"><span class="block text-[10px] uppercase font-bold text-slate-500 mb-0.5">Angs. Pokok</span><b class="text-sm">${nf.format(total.angs_p)}</b></div>
        <div class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg min-w-[120px] shadow-sm"><span class="block text-[10px] uppercase font-bold text-slate-500 mb-0.5">Angs. Bunga</span><b class="text-sm">${nf.format(total.angs_b)}</b></div>
      `;

      // Hapus CKPN
      const cols = [
        ['kode_cabang','KC','sm','text'],
        ['no_rekening','Norek','md','text'],
        ['nama_nasabah','Nama Nasabah','lg','text'],
        ['alamat','Alamat','lgA','text'],
        ['kolektibilitas','KOL','sm','text'],
        ['tunggakan_pokok','T.Pokok','md','num'],
        ['tunggakan_bunga','T.Bunga','md','num'],
        ['hari_menunggak','HM','sm','num'],
        ['hari_menunggak_pokok','HMP','sm','num'],
        ['hari_menunggak_bunga','HMB','sm','num'],
        ['tgl_jatuh_tempo','JtTmp','md','text'],
        ['tgl_tagih','TglTg','sm','num'],
        ['os_m1','OS M-1','md','num'],
        ['os_curr','OS Act','md','num'],
        ['angsuran_pokok','AngsP','md','num'],
        ['angsuran_bunga','AngsB','md','num']
      ];

      elModThead.innerHTML = `<tr>${
        cols.map(([key,label,sz,type]) =>
          `<th class="px-2 py-2 border-r border-slate-200 col-${sz} nowrap th-sort" data-key="${key}" data-type="${type||'text'}" title="${label}">${label}</th>`
        ).join('')
      }</tr>`;

      const totalCells = cols.map(([key,label,sz])=>{
        let v = '';
        if (key==='kode_cabang') v = 'TOTAL';
        else if (key==='no_rekening') v = nf.format(total.noa)+' Akun';
        else if (['os_m1','os_curr','angsuran_pokok','angsuran_bunga','tunggakan_pokok','tunggakan_bunga'].includes(key)){
          const mapKey = ({os_m1:'os_m1',os_curr:'os_curr',angsuran_pokok:'angs_p',angsuran_bunga:'angs_b',tunggakan_pokok:'tung_p',tunggakan_bunga:'tung_b'})[key];
          v = nf.format(total[mapKey]);
        }
        return `<td class="px-2 py-1 border-r border-blue-200 col-${sz} nowrap">${String(v)}</td>`;
      }).join('');

      const rowHtml = d=>{
        let h = '<tr class="border-b border-slate-100 hover:bg-blue-50/40 transition">';
        for (const [key,label,sz,type] of cols){
          let v = d[key]; if (v==null) v='';
          if (key==='nama_nasabah') v = cut(v,20);
          if (key==='alamat')       v = cut(v,30);
          
          const raw = (type==='num') ? Number(d[key]||0) : String(d[key]||'');
          const shown = (type==='num') ? nf.format(raw) : String(v);
          
          let alignClass = type === 'num' ? 'text-right' : 'text-left';
          if(key === 'kode_cabang' || key === 'kolektibilitas') alignClass = 'text-center';

          h += `<td class="px-2 py-1 border-r border-slate-100 col-${sz} ${alignClass} nowrap" data-key="${key}" data-raw="${raw}" title="${String(d[key]??'')}">${String(shown)}</td>`;
        }
        h += '</tr>';
        return h;
      };

      elModTbody.innerHTML = `<tr class="sticky-total">${totalCells}</tr>` + list.map(rowHtml).join('');

      // INIT SORTING
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

          const rows = Array.from(elModTbody.querySelectorAll('tr')).slice(1);
          rows.sort((ra, rb)=>{
            const a = ra.querySelector(`td[data-key="${key}"]`)?.getAttribute('data-raw');
            const b = rb.querySelector(`td[data-key="${key}"]`)?.getAttribute('data-raw');
            if (type==='num'){
              return sortState.dir * (Number(a||0) - Number(b||0));
            } else {
              return sortState.dir * String(a||'').localeCompare(String(b||''), 'id', {numeric:true});
            }
          });
          rows.forEach(r=>elModTbody.appendChild(r));
        });
      });
  }

  // --- EXPORT REKAP UTAMA MATRIX ---
  window.MB_exportRekap = function() {
      let html = `<table border="1"><thead><tr>`;
      const ths = document.querySelectorAll('#MB_headRow th');
      ths.forEach(th => {
          let txt = th.innerText.replace(/\n/g, ' ').replace(/\(×1.000\)/g, '').trim();
          html += `<th style="background:#f1f5f9">${txt}</th>`;
      });
      html += `</tr></thead><tbody>`;

      const rows = document.querySelectorAll('#MB_tbody tr');
      rows.forEach(tr => {
          html += `<tr>`;
          const tds = tr.querySelectorAll('td');
          tds.forEach(td => {
              let val = '';
              const numWrap = td.querySelector('.num-wrap');
              if(numWrap) {
                  val = numWrap.getAttribute('title').replace(/\./g, ''); // Ambil angka asli tanpa titik
              } else if (td.innerText.trim() === '–') {
                  val = '0';
              } else {
                  val = td.innerText.replace(/\n/g, ' ').split('•')[0].trim();
              }
              let bg = '';
              if(tr.id === 'MB_row_total') bg = 'background:#eff6ff; font-weight:bold;';
              else if(tr.id === 'MB_row_realisasi') bg = 'background:#f8fafc; font-weight:bold;';
              html += `<td style="${bg} mso-number-format:'\\@'">${val}</td>`;
          });
          html += `</tr>`;
      });
      html += `</tbody></table>`;

      const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
      const a = document.createElement('a'); a.href = URL.createObjectURL(blob);
      a.download = `Rekap_Migrasi_Bucket_${elClosing.value}_vs_${elHarian.value}.xls`; a.click();
  };

  // --- DOWNLOAD EXCEL DETAIL MURNI TANPA BAGI 1000 ---
  window.MB_exportDetail = function() {
      if(!currentDetailData || currentDetailData.length === 0) {
          alert("Tidak ada data detail untuk di-download.");
          return;
      }
      
      let html = `<table border="1"><thead><tr>
        <th style="background:#f1f5f9">KODE CABANG</th>
        <th style="background:#f1f5f9">NO REKENING</th>
        <th style="background:#f1f5f9">NAMA NASABAH</th>
        <th style="background:#f1f5f9">ALAMAT</th>
        <th style="background:#f1f5f9">KOL</th>
        <th style="background:#f1f5f9">TUNGG. POKOK</th>
        <th style="background:#f1f5f9">TUNGG. BUNGA</th>
        <th style="background:#fee2e2">HM</th>
        <th style="background:#f1f5f9">HMP</th>
        <th style="background:#f1f5f9">HMB</th>
        <th style="background:#f1f5f9">JATUH TEMPO</th>
        <th style="background:#f1f5f9">TGL TAGIH</th>
        <th style="background:#fef08a">OS M-1</th>
        <th style="background:#dcfce7">OS ACTUAL</th>
        <th style="background:#f1f5f9">ANGS. POKOK</th>
        <th style="background:#f1f5f9">ANGS. BUNGA</th>
      </tr></thead><tbody>`;

      currentDetailData.forEach(d => {
          html += `<tr>
              <td style="mso-number-format:'\\@'">${d.kode_cabang||''}</td>
              <td style="mso-number-format:'\\@'">${d.no_rekening||''}</td>
              <td>${d.nama_nasabah||''}</td>
              <td>${d.alamat||''}</td>
              <td>${d.kolektibilitas||''}</td>
              <td>${Number(d.tunggakan_pokok||0)}</td>
              <td>${Number(d.tunggakan_bunga||0)}</td>
              <td>${Number(d.hari_menunggak||0)}</td>
              <td>${Number(d.hari_menunggak_pokok||0)}</td>
              <td>${Number(d.hari_menunggak_bunga||0)}</td>
              <td>${d.tgl_jatuh_tempo||''}</td>
              <td>${d.tgl_tagih||''}</td>
              <td>${Number(d.os_m1||0)}</td>
              <td>${Number(d.os_curr||0)}</td>
              <td>${Number(d.angsuran_pokok||0)}</td>
              <td>${Number(d.angsuran_bunga||0)}</td>
          </tr>`;
      });
      html += '</tbody></table>';

      const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
      const a = document.createElement('a'); a.href = URL.createObjectURL(blob);
      a.download = `Detail_Migrasi_${currentFromRaw}_ke_${currentToRaw}.xls`; a.click();
  };

})();
</script>