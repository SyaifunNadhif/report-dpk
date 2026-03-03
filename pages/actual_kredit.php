<div class="max-w-7xl mx-auto px-4 py-5 font-sans" id="BD_root">
  
  <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 mb-4">
    <div class="shrink-0">
      <h1 class="text-xl md:text-2xl font-bold flex items-center gap-2 text-slate-800">
        <span class="bg-blue-600 text-white p-1.5 rounded-lg shadow-sm text-sm">📊</span>
        <span>Bucket DPD (OSC)</span>
      </h1>
      <p class="text-[11px] text-slate-500 mt-1.5 ml-1 font-medium">*Perbandingan M-1 vs Actual</p>
    </div>

    <form id="formFilterKolek" class="bg-white p-2 md:p-3 rounded-xl border border-slate-200 shadow-sm flex flex-wrap items-end gap-3 shrink-0">
      <div class="field flex flex-col gap-1">
        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider ml-1" for="closing_date_kolek">Closing</label>
        <input type="date" id="closing_date_kolek" class="inp" required>
      </div>
      <div class="field flex flex-col gap-1">
        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider ml-1" for="harian_date_kolek">Harian</label>
        <input type="date" id="harian_date_kolek" class="inp" required>
      </div>
      <div class="field flex flex-col gap-1">
        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider ml-1" for="opt_kantor_kolek">Cabang</label>
        <select id="opt_kantor_kolek" class="inp min-w-[200px]">
          <option value="">Konsolidasi (Semua Cabang)</option>
        </select>
      </div>
      <div class="flex gap-2 mt-auto">
        <button id="btnFilterKolek" type="submit" class="btn-icon h-9 w-10 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-sm transition flex items-center justify-center" title="Terapkan Filter">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65" stroke-linecap="round"></line>
          </svg>
        </button>
        <button type="button" onclick="exportSemuaLaporan()" class="btn-icon h-9 px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-sm transition flex items-center justify-center gap-2" title="Download Excel Semua Laporan">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
          <span class="text-xs font-bold uppercase tracking-wide hidden md:inline">Rekap</span>
        </button>
      </div>
    </form>
  </div>

  <div id="loadingKolek" class="hidden flex items-center gap-2 text-sm text-blue-600 font-bold mb-3 tracking-wider">
    <svg class="animate-spin h-5 w-5 text-blue-600" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span>MEMUAT DATA...</span>
  </div>

  <div class="hscroll border border-slate-200 rounded-xl shadow-sm bg-white overflow-hidden" id="bucketTblWrap">
    <table id="tblBucket" class="wide-bucket text-sm text-center">
      <colgroup>
        <col class="w-name">
        <col class="w-num"><col class="w-num">
        <col class="w-num"><col class="w-num">
        <col class="w-num"><col class="w-num"><col class="w-pct">
      </colgroup>
      <thead class="thead-blue sticky-desktop">
        <tr>
          <th class="border-b border-r border-blue-200 px-3 py-3 align-middle bg-blue-50" rowspan="2">Bucket DPD</th>
          <th class="border-b border-r border-blue-200 px-3 py-2 bg-blue-50" colspan="2">M-1</th>
          <th class="border-b border-r border-blue-200 px-3 py-2 bg-blue-50" colspan="2">Actual</th>
          <th class="border-b border-blue-200 px-3 py-2 bg-emerald-50 text-emerald-800" colspan="3">INC (Pertumbuhan)</th>
        </tr>
        <tr>
          <th class="border-b border-r border-blue-200 px-3 py-2 bg-blue-50">NOA</th>
          <th class="border-b border-r border-blue-200 px-3 py-2 bg-blue-50">OSC</th>
          <th class="border-b border-r border-blue-200 px-3 py-2 bg-blue-50">NOA</th>
          <th class="border-b border-r border-blue-200 px-3 py-2 bg-blue-50">OSC</th>
          <th class="border-b border-r border-emerald-200 px-3 py-2 bg-emerald-50 text-emerald-800">NOA</th>
          <th class="border-b border-r border-emerald-200 px-3 py-2 bg-emerald-50 text-emerald-800">OSC</th>
          <th class="border-b border-emerald-200 px-3 py-2 bg-emerald-50 text-emerald-800">%</th>
        </tr>
      </thead>
      <tbody id="bodyKolek" class="text-slate-700 divide-y divide-slate-100"></tbody>
    </table>
  </div>

  <div id="pairWrap" class="mt-5 grid grid-cols-1 gap-5 hidden lg:grid-cols-10">
    
    <div class="card lg:col-span-4 flex flex-col">
      <div class="card-header flex items-center gap-2">
        <span class="bg-indigo-100 text-indigo-700 p-1 rounded">🌊</span> Flow Rate
      </div>
      <div class="hscroll border border-slate-200 rounded-lg">
        <table id="tblFlowRate" class="wide-bucket text-sm text-center">
          <colgroup>
            <col class="w-fr"><col class="w-pct"><col class="w-pct"><col class="w-inc">
          </colgroup>
          <thead class="bg-indigo-50 text-indigo-900 border-b border-indigo-100">
            <tr>
              <th class="border-r border-indigo-100 px-2 py-2">FR</th>
              <th class="border-r border-indigo-100 px-2 py-2">%</th>
              <th class="border-r border-indigo-100 px-2 py-2">FR APPETITE</th>
              <th class="px-2 py-2">INC</th>
            </tr>
          </thead>
          <tbody id="bodyFlowRate" class="divide-y divide-slate-100"></tbody>
        </table>
      </div>

      <div class="metrics grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4 text-sm mt-auto">
        <div class="metric bg-slate-50 hover:bg-white transition">
          <div class="metric-label">Repayment Rate</div>
          <div id="pmRepay" class="metric-value"></div>
        </div>
        <div class="metric bg-slate-50 hover:bg-white transition">
          <div class="metric-label">DPD 30+</div>
          <div id="pm30" class="metric-value"></div>
        </div>
        <div class="metric bg-slate-50 hover:bg-white transition">
          <div class="metric-label">DPD 90+</div>
          <div id="pm90" class="metric-value"></div>
        </div>
      </div>
    </div>

    <div class="card lg:col-span-6 flex flex-col">
      <div class="card-header flex items-center gap-2">
        <span class="bg-orange-100 text-orange-700 p-1 rounded">🔥</span> KOL (M-1 vs Actual)
      </div>
      <div class="hscroll border border-slate-200 rounded-lg">
        <table id="tblKOL" class="wide-bucket text-sm text-center">
          <colgroup>
            <col class="w-name"><col class="w-num"><col class="w-num"><col class="w-num"><col class="w-num"><col class="w-inc">
          </colgroup>
          <thead class="bg-orange-50 text-orange-900 border-b border-orange-100">
            <tr>
              <th class="border-r border-orange-100 px-2 py-2">KOL</th>
              <th class="border-r border-orange-100 px-2 py-2">M-1 NOA</th>
              <th class="border-r border-orange-100 px-2 py-2">M-1 OS</th>
              <th class="border-r border-orange-100 px-2 py-2">Actual NOA</th>
              <th class="border-r border-orange-100 px-2 py-2">Actual OS</th>
              <th class="px-2 py-2">Inc OS</th>
            </tr>
          </thead>
          <tbody id="bodyKol" class="divide-y divide-slate-100"></tbody>
        </table>
      </div>

      <div class="metrics grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4 text-sm mt-auto">
        <div class="metric bg-slate-50 hover:bg-white transition">
          <div class="metric-label">% NPL M-1</div>
          <div id="nplM1" class="metric-value text-slate-800"></div>
        </div>
        <div class="metric bg-slate-50 hover:bg-white transition">
          <div class="metric-label">% NPL Actual</div>
          <div id="nplAct" class="metric-value text-slate-800"></div>
        </div>
        <div class="metric bg-slate-50 hover:bg-white transition">
          <div class="metric-label">Δ % NPL</div>
          <div id="nplInc" class="metric-value"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  /* ===== Skala teks responsif ===== */
  html { font-size: 16px; }
  @media (max-width: 1023px){ html { font-size: 15px; } }
  @media (max-width: 640px){ html { font-size: 14px; } }

  /* ===== Form & Controls ===== */
  .inp { border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 0 0.6rem; font-size: 13px; background: #fff; height: 36px; outline: none; transition: 0.2s; width: 100%;}
  .inp:focus { border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,0.1); }
  .inp:disabled { background-color: #f1f5f9; color: #64748b; font-weight: bold; cursor: not-allowed; }

  /* ===== Utilities soft design ===== */
  .card{ padding:1.25rem; background:#fff; border-radius:.75rem; box-shadow:0 1px 3px rgba(0,0,0,.1); border: 1px solid #e2e8f0;}
  .card-header{ font-weight:700; font-size: 1rem; color: #334155; margin-bottom:.75rem; text-transform: uppercase; letter-spacing: 0.05em;}
  .metric{ padding:.75rem 1rem; border:1px solid #e2e8f0; border-radius:.5rem; }
  .metric-label{ font-size:.65rem; font-weight: 700; text-transform: uppercase; color:#64748b; margin-bottom:.25rem; letter-spacing: 0.05em;}
  .metric-value{ font-weight:700; font-size: 1.1rem;}

  /* ===== table look ===== */
  .thead-blue{ background:#eff6ff; color:#1e40af; }
  .neg{ color:#dc2626; font-weight: 600;} /* Merah */
  .pos{ color:#059669; font-weight: 600;} /* Hijau */
  .subhead{ font-weight:700; background: #f8fafc !important; }
  
  tbody tr:hover td { background-color: #f8fafc; }

  table th, table td{ white-space:nowrap; }
  table td:first-child{ white-space:normal; text-align:left; font-weight: 600; color: #475569;}

  /* ===== kontrol lebar kolom ===== */
  .w-name{ width:2rem; }  
  .w-fr{   width:2rem; }  
  .w-num{  width:2rem; }   
  .w-pct{  width:2rem; } 
  .w-inc{  width:2rem; }   

  /* ===== shared horizontal scroll frame ===== */
  .hscroll{ overflow-x:auto; -webkit-overflow-scrolling:touch; overscroll-behavior-x:contain; }
  .wide-bucket{ width:100%; border-collapse:separate; border-spacing:0; }

  @media (min-width:1024px){
    .hscroll{ overflow-x:visible; }
    .wide-bucket{ min-width:0; }
    .sticky-desktop{ position:sticky; top:0; z-index:10; }
  }

  @media (max-width:1023px){
    .wide-bucket{ min-width:780px; font-size:.85rem; }
    .wide-bucket thead th{ padding:.5rem .5rem; }
    .wide-bucket tbody td{ padding:.5rem .5rem; }
    .sticky-desktop{ position:static !important; }
  }

  @media (max-width:640px){
    .filter-wrap{ display:grid; grid-template-columns: 1fr 1fr; gap:8px 10px; align-items:end; }
    .filter-wrap .field{flex-direction:column; align-items:stretch; gap:4px;}
    .filter-wrap .field label{display:none;}
    .filter-wrap .field:nth-child(1){grid-column:1;}
    .filter-wrap .field:nth-child(2){grid-column:2;}
    .filter-wrap .field:nth-child(3){grid-column:1;}
  }
</style>

<script>
  const nfID = new Intl.NumberFormat('id-ID');
  const fmt  = n => nfID.format(Number(n||0));
  const pct2 = x => (Number.isFinite(+x) ? `${(+x).toFixed(2)}%` : '0%');
  const apiCall = (url,opt={}) => (window.apiFetch?window.apiFetch(url,opt):fetch(url,opt));
  const byCode = (arr, key='dpd_code') => Object.fromEntries((arr||[]).map(r=>[String(r[key]||''), r]));

  const selKantor = document.getElementById('opt_kantor_kolek');

  async function populateKantorOptions(userKode){
    try{
      if(userKode && userKode!=='000'){
        selKantor.innerHTML=`<option value="${userKode}">${userKode}</option>`;
        selKantor.value=userKode; selKantor.disabled=true; return;
      }
      const res=await apiCall('./api/kode/',{ method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({type:'kode_kantor'}) });
      const json=await res.json(); const list=Array.isArray(json.data)?json.data:[];
      let html=`<option value="">Konsolidasi (Semua Cabang)</option>`;
      list.filter(x=>x.kode_kantor && x.kode_kantor!=='000')
          .sort((a,b)=> String(a.kode_kantor).localeCompare(String(b.kode_kantor)))
          .forEach(it=>{ const code=String(it.kode_kantor).padStart(3,'0'); const name=it.nama_kantor||it.nama_cabang||''; html+=`<option value="${code}">${code} — ${name}</option>`; });
      selKantor.innerHTML=html; selKantor.disabled=false;
    }catch{ selKantor.innerHTML=`<option value="">Konsolidasi (Semua Cabang)</option>`; selKantor.disabled=false; }
  }

  window.addEventListener('DOMContentLoaded', async () => {
    const d = await getLastHarianData();
    if(!d) return;

    document.getElementById('closing_date_kolek').value=d.last_closing;
    document.getElementById('harian_date_kolek').value=d.last_created;

    const user=(window.getUser&&window.getUser())||null;
    const userKode=(user?.kode?String(user.kode).padStart(3,'0'):null);
    await populateKantorOptions(userKode);

    fetchAll(d.last_closing, d.last_created, (userKode&&userKode!=='000')?userKode:null);
  });

  async function getLastHarianData(){
    try{const r=await apiCall('./api/date/'); const j=await r.json(); return j.data||null;}catch{return null;}
  }

  document.getElementById('formFilterKolek').addEventListener('submit', e=>{
    e.preventDefault();
    const closing=document.getElementById('closing_date_kolek').value;
    const harian =document.getElementById('harian_date_kolek').value;
    const kode   =selKantor.value || null;
    fetchAll(closing,harian,kode);
  });

  selKantor.addEventListener('change', ()=>{
    const closing=document.getElementById('closing_date_kolek').value;
    const harian =document.getElementById('harian_date_kolek').value;
    const kode   =selKantor.value || null;
    if(closing&&harian) fetchAll(closing,harian,kode);
  });

  let abortAll;
  async function fetchAll(closing_date, harian_date, kode_kantor){
    const loading=document.getElementById('loadingKolek');
    const tbody  =document.getElementById('bodyKolek');
    const pair   =document.getElementById('pairWrap');

    loading.classList.remove('hidden');
    pair.classList.add('hidden');
    tbody.innerHTML=`<tr><td colspan=\"8\" class=\"py-8 text-slate-400 text-center\">Menyiapkan Matriks...</td></tr>`;

    if(abortAll) abortAll.abort();
    abortAll=new AbortController();

    const base = {closing_date, harian_date};
    if(kode_kantor) base.kode_kantor = kode_kantor;

    try{
      const p1 = apiCall('./api/kolek/', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({...base, type:'bucket osc'}), signal:abortAll.signal});
      const p3 = apiCall('./api/kolek/', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({...base, type:'kolek m1 and actual'}), signal:abortAll.signal});

      // ckpn request dihapus
      const [oscJ, kolJ] = await Promise.all([p1,p3].map(x=>x.then(r=>r.json())));

      if((oscJ.status!==200)||(kolJ.status!==200)){
        document.getElementById('bodyKolek').innerHTML=`<tr><td colspan=\"8\" class=\"py-4 text-red-600 text-center font-bold\">Gagal memuat data</td></tr>`;
        return;
      }

      renderMainTable(oscJ.data);
      renderFlowRateGrid(oscJ.data);
      renderKolGrid(kolJ.data);

      pair.classList.remove('hidden');
    }catch(e){
      if(e.name!=='AbortError'){
        document.getElementById('bodyKolek').innerHTML=`<tr><td colspan=\"8\" class=\"py-4 text-red-600 text-center font-bold\">Gagal memuat data</td></tr>`;
      }
    }finally{ loading.classList.add('hidden'); }
  }

  // ===== Render Utama (Tanpa CKPN) =====
  function renderMainTable(oscData){
    const tbody=document.getElementById('bodyKolek');
    const rowsOsc = Array.isArray(oscData?.rows) ? oscData.rows : [];

    const html = rowsOsc.map(r=>{
      const code = String(r.dpd_code||'').toUpperCase();
      let name = r.dpd_name || code;
      if(code==='GRAND_TOTAL') name='GRAND TOTAL';
      if(code==='TOTAL_SC')    name='TOTAL SC';
      if(code==='TOTAL_FE')    name='TOTAL FE';
      if(code==='TOTAL_BE')    name='TOTAL BE';
      if(code==='O')           name='O_Lunas';
      if(code==='REALISASI')   name='Realisasi';

      const isTotal = /^(TOTAL|GRAND_TOTAL)/.test(code);
      const trClass = isTotal ? 'subhead' : '';

      const incNoa = r.inc_noa, incOs = r.inc_os, incPct = r.inc_pct;
      const clsIncOs  = (Number(incOs||0) > 0) ? 'neg' : (Number(incOs||0) < 0 ? 'pos' : '');
      const clsIncPct = (Number(incPct||0) > 0) ? 'neg' : (Number(incPct||0) < 0 ? 'pos' : '');

      return `
      <tr class="${trClass}">
        <td class="border-r border-slate-100 px-3 py-2.5">${name}</td>
        <td class="border-r border-slate-100 px-3 py-2.5">${fmt(r.noa_m1)}</td>
        <td class="border-r border-blue-100 px-3 py-2.5 text-right font-semibold">${fmt(r.os_m1)}</td>
        <td class="border-r border-slate-100 px-3 py-2.5">${fmt(r.noa_curr)}</td>
        <td class="border-r border-blue-100 px-3 py-2.5 text-right font-semibold">${fmt(r.os_curr)}</td>
        <td class="border-r border-slate-100 px-3 py-2.5">${incNoa==null?'':fmt(incNoa)}</td>
        <td class="border-r border-slate-100 px-3 py-2.5 text-right ${clsIncOs}">${incOs==null?'':fmt(incOs)}</td>
        <td class="px-3 py-2.5 ${clsIncPct}">${incPct==null?'':pct2(incPct)}</td>
      </tr>`;
    }).join('');

    tbody.innerHTML = html || `<tr><td colspan=\"8\" class=\"py-4 text-slate-400 text-center\">Tidak ada data</td></tr>`;
  }

  // ===== Render Flow Rate =====
  function renderFlowRateGrid(oscData){
    const body=document.getElementById('bodyFlowRate');
    const fr = Array.isArray(oscData?.flow_rate) ? oscData.flow_rate : [];

    body.innerHTML = fr.map(x=>{
      const cls = (Number(x.inc_pct||0)>0)?'neg':(Number(x.inc_pct||0)<0?'pos':'');
      return `
        <tr>
          <td class="border-r border-slate-100 px-2 py-2 text-left font-semibold text-slate-600">${x.label||x.code}</td>
          <td class="border-r border-slate-100 px-2 py-2">${pct2(x.actual_pct)}</td>
          <td class="border-r border-slate-100 px-2 py-2 text-slate-400">${pct2(x.appetite_pct)}</td>
          <td class="px-2 py-2 ${cls}">${pct2(x.inc_pct)}</td>
        </tr>`;
    }).join('') || `<tr><td colspan=\"4\" class=\"py-3 text-slate-400\">Tidak ada data</td></tr>`;

    const pm = oscData?.portfolio_metrics || {};
    const rr = pm.repayment_rate || {};
    const d30= pm.dpd_30_plus || {};
    const d90= pm.dpd_90_plus || {};
    
    // RR LOGIC REVERSED: Naik (Positif) = Hijau, Turun = Merah
    const rrCls  = (+rr.inc_pct > 0) ? 'pos' : (+rr.inc_pct < 0 ? 'neg' : 'text-slate-500');
    // DPD LOGIC NORMAL: Naik (Positif) = Merah, Turun = Hijau
    const d30Cls = (+d30.inc_pct > 0) ? 'neg' : (+d30.inc_pct < 0 ? 'pos' : 'text-slate-500');
    const d90Cls = (+d90.inc_pct > 0) ? 'neg' : (+d90.inc_pct < 0 ? 'pos' : 'text-slate-500');
    
    document.getElementById('pmRepay').innerHTML =
      `<span class="text-slate-500 font-normal">${pct2(rr.m1_pct)} ➔ </span><span class="text-slate-800">${pct2(rr.actual_pct)}</span> <br><span class="${rrCls} text-xs mt-1 block">(${(rr.inc_pct>=0?'+':'')}${Math.abs(+rr.inc_pct||0).toFixed(2)}%)</span>`;
    document.getElementById('pm30').innerHTML =
      `<span class="text-slate-500 font-normal">${pct2(d30.m1_pct)} ➔ </span><span class="text-slate-800">${pct2(d30.actual_pct)}</span> <br><span class="${d30Cls} text-xs mt-1 block">(${(d30.inc_pct>=0?'+':'')}${Math.abs(+d30.inc_pct||0).toFixed(2)}%)</span>`;
    document.getElementById('pm90').innerHTML =
      `<span class="text-slate-500 font-normal">${pct2(d90.m1_pct)} ➔ </span><span class="text-slate-800">${pct2(d90.actual_pct)}</span> <br><span class="${d90Cls} text-xs mt-1 block">(${(d90.inc_pct>=0?'+':'')}${Math.abs(+d90.inc_pct||0).toFixed(2)}%)</span>`;
  }

  // ===== Render KOL =====
  function renderKolGrid(kolData){
    const body=document.getElementById('bodyKol');
    const rows = Array.isArray(kolData?.data?.rows) ? kolData.data.rows : [];
    const totals = kolData?.data?.total_osc || {};
    const npl    = kolData?.data?.data?.npl || kolData?.data?.npl || {};

    const order = ['Realisasi','L','DP','KL','D','M','Lunas'];
    const sorted = rows.slice().sort((a,b)=> order.indexOf(a.kol) - order.indexOf(b.kol));

    const tr = sorted.map(r=>{
      const cls = (Number(r.inc_os||0)>0)?'pos':(Number(r.inc_os||0)<0?'neg':'');
      return `
        <tr>
          <td class="border-r border-slate-100 px-2 py-2 text-left font-semibold text-slate-600">${r.kol}</td>
          <td class="border-r border-slate-100 px-2 py-2">${fmt(r.m1_noa)}</td>
          <td class="border-r border-orange-100 px-2 py-2 text-right">${fmt(r.m1_os)}</td>
          <td class="border-r border-slate-100 px-2 py-2">${fmt(r.act_noa)}</td>
          <td class="border-r border-orange-100 px-2 py-2 text-right">${fmt(r.act_os)}</td>
          <td class="px-2 py-2 text-right ${cls}">${r.inc_os==null?'':fmt(r.inc_os)}</td>
        </tr>`;
    }).join('');

    const totalCls = (+totals.inc_os>0)?'pos':(+totals.inc_os<0?'neg':'');
    const totalRow = `
      <tr class="subhead text-orange-900 border-t-2 border-orange-100">
        <td class="border-r border-orange-100 px-2 py-3 text-left">TOTAL</td>
        <td class="border-r border-orange-100 px-2 py-3">${fmt(totals.m1_noa)}</td>
        <td class="border-r border-orange-200 px-2 py-3 text-right">${fmt(totals.m1_os)}</td>
        <td class="border-r border-orange-100 px-2 py-3">${fmt(totals.act_noa)}</td>
        <td class="border-r border-orange-200 px-2 py-3 text-right">${fmt(totals.act_os)}</td>
        <td class="px-2 py-3 text-right ${totalCls}">${fmt(totals.inc_os||0)}</td>
      </tr>`;

    body.innerHTML = (tr+totalRow) || `<tr><td colspan=\"6\" class=\"py-3 text-slate-400\">Tidak ada data</td></tr>`;

    // NPL LOGIC NORMAL (Naik Merah)
    document.getElementById('nplM1').textContent = pct2(npl.m1_pct);
    document.getElementById('nplAct').textContent= pct2(npl.actual_pct);
    const incAbs = Math.abs(+npl.inc_pct||0).toFixed(2);
    const incCls = (+npl.inc_pct>0)?'neg':(+npl.inc_pct<0?'pos':'');
    document.getElementById('nplInc').innerHTML  = `<span class="${incCls}">${(npl.inc_pct>=0?'+':'-')}${incAbs}%</span>`;
  }

  // --- EXPORT 3 TABEL KE 1 EXCEL ---
  window.exportSemuaLaporan = function() {
      const closing = document.getElementById('closing_date_kolek').value;
      const harian = document.getElementById('harian_date_kolek').value;
      let html = "";

      // 1. Ekstrak Tabel Bucket (Tanpa CKPN)
      html += "<h3>1. BUCKET DPD (OSC)</h3>";
      html += "<table border='1'><thead><tr>";
      document.querySelectorAll('#tblBucket thead tr:first-child th').forEach(th => html += `<th style="background:#eff6ff">${th.innerText}</th>`);
      html += "</tr><tr>";
      document.querySelectorAll('#tblBucket thead tr:last-child th').forEach(th => html += `<th style="background:#eff6ff">${th.innerText}</th>`);
      html += "</tr></thead><tbody>";
      document.querySelectorAll('#tblBucket tbody tr').forEach(tr => {
          html += "<tr>";
          tr.querySelectorAll('td').forEach(td => html += `<td>${td.innerText.replace(/\./g, '')}</td>`);
          html += "</tr>";
      });
      html += "</tbody></table><br><br>";

      // 2. Ekstrak Tabel Flow Rate
      html += "<h3>2. FLOW RATE</h3>";
      html += "<table border='1'><thead><tr>";
      document.querySelectorAll('#tblFlowRate thead th').forEach(th => html += `<th style="background:#eef2ff">${th.innerText}</th>`);
      html += "</tr></thead><tbody>";
      document.querySelectorAll('#tblFlowRate tbody tr').forEach(tr => {
          html += "<tr>";
          tr.querySelectorAll('td').forEach(td => html += `<td>${td.innerText}</td>`);
          html += "</tr>";
      });
      html += "</tbody></table><br><br>";

      // 3. Ekstrak Tabel KOL
      html += "<h3>3. KOL (M-1 vs ACTUAL)</h3>";
      html += "<table border='1'><thead><tr>";
      document.querySelectorAll('#tblKOL thead th').forEach(th => html += `<th style="background:#fff7ed">${th.innerText}</th>`);
      html += "</tr></thead><tbody>";
      document.querySelectorAll('#tblKOL tbody tr').forEach(tr => {
          html += "<tr>";
          tr.querySelectorAll('td').forEach(td => html += `<td>${td.innerText.replace(/\./g, '')}</td>`);
          html += "</tr>";
      });
      html += "</tbody></table>";

      const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
      const a = document.createElement('a'); a.href = URL.createObjectURL(blob);
      a.download = `Laporan_Bucket_KOL_FR_${closing}_vs_${harian}.xls`; a.click();
  };

</script>