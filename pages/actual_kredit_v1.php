<!-- 📊 Bucket DPD — OSC & CKPN (M-1 vs Actual) [v4: kolom mobile 2–3rem, grid 10 (4/6), INC color KOL + hijau / − merah] -->
<div class="max-w-7xl mx-auto px-4 py-6">
  <h1 class="text-2xl font-bold mb-4">📊 Bucket DPD — OSC & CKPN (M-1 vs Actual)</h1>

  <!-- Filter (mobile: 2 baris) -->
  <form id="formFilterKolek" class="filter-wrap mb-3">
    <div class="field">
      <label class="text-sm" for="closing_date_kolek">Closing:</label>
      <input type="date" id="closing_date_kolek" class="ctrl border rounded px-3 py-1 text-sm" required>
    </div>
    <div class="field">
      <label class="text-sm" for="harian_date_kolek">Harian:</label>
      <input type="date" id="harian_date_kolek" class="ctrl border rounded px-3 py-1 text-sm" required>
    </div>
    <div class="field">
      <label class="text-sm" for="opt_kantor_kolek">Cabang:</label>
      <select id="opt_kantor_kolek" class="ctrl border rounded px-3 py-1 text-sm min-w-[220px]">
        <option value="">Konsolidasi (Semua Cabang)</option>
      </select>
    </div>
    <button id="btnFilterKolek" type="submit" class="btn-icon" title="Filter" aria-label="Terapkan filter">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <circle cx="11" cy="11" r="7" stroke-width="2"></circle>
        <line x1="21" y1="21" x2="16.65" y2="16.65" stroke-width="2" stroke-linecap="round"></line>
      </svg>
    </button>
  </form>

  <!-- Loading (di atas tabel) -->
  <div id="loadingKolek" class="hidden flex items-center gap-2 text-sm text-gray-600 mb-2">
    <svg class="animate-spin h-5 w-5 text-green-600" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span>Memuat data...</span>
  </div>

  <!-- Tabel utama -->
  <div class="hscroll">
    <table class="wide-bucket text-sm border border-gray-300 text-center bg-white rounded">
      <colgroup>
        <col class="w-name">
        <col class="w-num"><col class="w-num">
        <col class="w-num"><col class="w-num">
        <col class="w-num"><col class="w-num"><col class="w-pct">
        <col class="w-num"><col class="w-num"><col class="w-inc">
      </colgroup>
      <thead class="thead-green sticky-desktop">
        <tr>
          <th class="border px-2 py-2 align-middle" rowspan="2">Bucket DPD</th>
          <th class="border px-2 py-2" colspan="2">M-1</th>
          <th class="border px-2 py-2" colspan="2">Actual</th>
          <th class="border px-2 py-2" colspan="3">INC</th>
          <th class="border px-2 py-2" colspan="3">CKPN</th>
        </tr>
        <tr>
          <th class="border px-2 py-2">NOA</th>
          <th class="border px-2 py-2">OSC</th>
          <th class="border px-2 py-2">NOA</th>
          <th class="border px-2 py-2">OSC</th>
          <th class="border px-2 py-2">NOA</th>
          <th class="border px-2 py-2">OSC</th>
          <th class="border px-2 py-2">%</th>
          <th class="border px-2 py-2">M-1</th>
          <th class="border px-2 py-2">Actual</th>
          <th class="border px-2 py-2">INC</th>
        </tr>
      </thead>
      <tbody id="bodyKolek" class="text-gray-900"></tbody>
    </table>
  </div>

  <!-- Pair grid: FlowRate (4) + KOL (6) -->
  <div id="pairWrap" class="mt-5 grid grid-cols-1 gap-4 hidden lg:grid-cols-10">
    <!-- FLOW RATE (span 4) -->
    <div class="card lg:col-span-4">
      <div class="card-header">FLOW RATE</div>
      <div class="hscroll">
        <table class="wide-bucket text-sm border border-gray-300 text-center bg-white rounded">
          <colgroup>
            <col class="w-fr"><col class="w-pct"><col class="w-pct"><col class="w-inc">
          </colgroup>
          <thead class="thead-green">
            <tr>
              <th class="border px-2 py-2">FR</th>
              <th class="border px-2 py-2">%</th>
              <th class="border px-2 py-2">FR APPETITE</th>
              <th class="border px-2 py-2">INC</th>
            </tr>
          </thead>
          <tbody id="bodyFlowRate"></tbody>
        </table>
      </div>

      <div class="metrics grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3 text-sm">
        <div class="metric">
          <div class="metric-label">Repayment Rate</div>
          <div id="pmRepay" class="metric-value"></div>
        </div>
        <div class="metric">
          <div class="metric-label">DPD 30+</div>
          <div id="pm30" class="metric-value"></div>
        </div>
        <div class="metric">
          <div class="metric-label">DPD 90+</div>
          <div id="pm90" class="metric-value"></div>
        </div>
      </div>
    </div>

    <!-- KOL (span 6) -->
    <div class="card lg:col-span-6">
      <div class="card-header">KOL — M-1 vs Actual</div>
      <div class="hscroll">
        <table class="wide-bucket text-sm border border-gray-300 text-center bg-white rounded">
          <colgroup>
            <col class="w-name"><col class="w-num"><col class="w-num"><col class="w-num"><col class="w-num"><col class="w-inc">
          </colgroup>
          <thead class="thead-green">
            <tr>
              <th class="border px-2 py-2">KOL</th>
              <th class="border px-2 py-2">M-1 NOA</th>
              <th class="border px-2 py-2">M-1 OS</th>
              <th class="border px-2 py-2">Actual NOA</th>
              <th class="border px-2 py-2">Actual OS</th>
              <th class="border px-2 py-2">Inc OS</th>
            </tr>
          </thead>
          <tbody id="bodyKol"></tbody>
        </table>
      </div>

      <div class="metrics grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3 text-sm">
        <div class="metric">
          <div class="metric-label">% NPL M-1</div>
          <div id="nplM1" class="metric-value"></div>
        </div>
        <div class="metric">
          <div class="metric-label">% NPL Actual</div>
          <div id="nplAct" class="metric-value"></div>
        </div>
        <div class="metric">
          <div class="metric-label">Δ % NPL</div>
          <div id="nplInc" class="metric-value"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  /* ===== Skala teks responsif (base) ===== */
  html { font-size: 16px; }
  @media (max-width: 1023px){ html { font-size: 15px; } }
  @media (max-width: 640px){ html { font-size: 14px; } }

  /* ===== Utilities kecil ===== */
  .card{ padding:1rem; background:#fff; border-radius:.5rem; box-shadow:0 1px 2px rgba(0,0,0,.05); }
  .card-header{ font-weight:600; margin-bottom:.5rem; }
  .metric{ padding:.75rem; border:1px solid #e5e7eb; border-radius:.5rem; background:#fff; }
  .metric-label{ font-size:.75rem; color:#6b7280; margin-bottom:.25rem; }
  .metric-value{ font-weight:600; }

  /* ===== filter / controls ===== */
  .filter-wrap{display:flex; flex-wrap:wrap; align-items:end; gap:12px;}
  .field{display:flex; align-items:center; gap:8px;}
  .ctrl{max-width:100%; font-size:16px;}
  .btn-icon{display:flex; align-items:center; justify-content:center; background:#16a34a; color:#fff; padding:8px; border-radius:8px;}
  .btn-icon:hover{background:#15803d;}

  /* ===== table look ===== */
  .thead-green{ background:#dcfce7; color:#14532d; }
  .thead-green th{ border-color:#86efac !important; }
  .neg{ color:#dc2626; }
  .pos{ color:#059669; }
  .subhead{font-weight:700;}
  tbody tr:nth-child(odd){ background:#fff; }
  tbody tr:nth-child(even){ background:#fafafa; }

  /* rapikan angka dan mencegah wrap, kecuali kolom pertama boleh multi-line */
  table th, table td{ white-space:nowrap; }
  table td:first-child{ white-space:normal; text-align:left; }

  /* ===== kontrol lebar kolom (desktop default) ===== */
  .w-name{ width:2rem; }  /* nama bucket/KOL */
  .w-fr{   width:2rem; }  /* label FR */
  .w-num{  width:2rem; }   /* angka */
  .w-pct{  width:2rem; } /* persentase */
  .w-inc{  width:2rem; }   /* kolom INC */

  /* ===== shared horizontal scroll frame ===== */
  .hscroll{ overflow-x:auto; -webkit-overflow-scrolling:touch; overscroll-behavior-x:contain; background:#fff; border-radius:.375rem; }
  .wide-bucket{ width:100%; border-collapse:separate; border-spacing:0; }

  /* Desktop (>=1024px): tidak scroll, sticky header aktif */
  @media (min-width:1024px){
    .hscroll{ overflow-x:visible; }
    .wide-bucket{ min-width:0; }
    .sticky-desktop{ position:sticky; top:0; z-index:10; }
  }

  /* Tablet & Mobile: min-width tabel diperkecil, kolom 2–3rem */
  @media (max-width:1023px){
    .wide-bucket{ min-width:780px; font-size:.85rem; }
    .wide-bucket thead th{ padding:.4rem .45rem; font-weight:700; }
    .wide-bucket tbody td{ padding:.4rem .45rem; font-weight:600; }

    /* width kolom di mobile: 2–3rem untuk angka/% */
    .w-name{ width:2rem; }  /* nama/KOL */
    .w-fr{   width:2rem; }
    .w-num{  width:2rem; }
    .w-pct{  width:2rem; }
    .w-inc{  width:2rem; }

    .sticky-desktop{ position:static !important; }
  }

  /* Filter: 2 kolom rapi di HP */
  @media (max-width:640px){
    .filter-wrap{ display:grid; grid-template-columns: 1fr 1fr; gap:8px 10px; align-items:end; }
    .filter-wrap .field{flex-direction:column; align-items:stretch; gap:4px;}
    .filter-wrap .field label{display:none;}
    .filter-wrap .field:nth-child(1){grid-column:1;}
    .filter-wrap .field:nth-child(2){grid-column:2;}
    .filter-wrap .field:nth-child(3){grid-column:1;}
    #btnFilterKolek{grid-column:2; height:42px; min-width:46px; justify-self:start;}
  }
</style>

<script>
  // ===== helpers =====
  const nfID = new Intl.NumberFormat('id-ID');
  const fmt  = n => nfID.format(Number(n||0));
  const pct2 = x => (Number.isFinite(+x) ? `${(+x).toFixed(2)}%` : '0%');
  const apiCall = (url,opt={}) => (window.apiFetch?window.apiFetch(url,opt):fetch(url,opt));
  const byCode = (arr, key='dpd_code') => Object.fromEntries((arr||[]).map(r=>[String(r[key]||''), r]));

  // ===== role-aware dropdown =====
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

  // ===== init =====
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

  // submit & change
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
    tbody.innerHTML=`<tr><td colspan=\"11\" class=\"py-4 text-gray-500\">Memuat data...</td></tr>`;

    if(abortAll) abortAll.abort();
    abortAll=new AbortController();

    const base = {closing_date, harian_date};
    if(kode_kantor) base.kode_kantor = kode_kantor;

    try{
      const p1 = apiCall('./api/kolek/', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({...base, type:'bucket osc'}), signal:abortAll.signal});
      const p2 = apiCall('./api/kolek/', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({...base, type:'bucket ckpn'}), signal:abortAll.signal});
      const p3 = apiCall('./api/kolek/', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({...base, type:'kolek m1 and actual'}), signal:abortAll.signal});

      const [oscJ, ckpnJ, kolJ] = await Promise.all([p1,p2,p3].map(x=>x.then(r=>r.json())));

      if((oscJ.status!==200)||(ckpnJ.status!==200)||(kolJ.status!==200)){
        document.getElementById('bodyKolek').innerHTML=`<tr><td colspan=\"11\" class=\"py-4 text-red-600\">Gagal memuat data</td></tr>`;
        return;
      }

      renderMainTable(oscJ.data, ckpnJ.data);
      renderFlowRateGrid(oscJ.data);
      renderKolGrid(kolJ.data);

      pair.classList.remove('hidden');
    }catch(e){
      if(e.name!=='AbortError'){
        document.getElementById('bodyKolek').innerHTML=`<tr><td colspan=\"11\" class=\"py-4 text-red-600\">Gagal memuat data</td></tr>`;
      }
    }finally{ loading.classList.add('hidden'); }
  }

  // ===== Render: Tabel Utama =====
  function renderMainTable(oscData, ckpnData){
    const tbody=document.getElementById('bodyKolek');
    const rowsOsc = Array.isArray(oscData?.rows) ? oscData.rows : [];
    const mapCkpn = byCode(Array.isArray(ckpnData?.rows) ? ckpnData.rows : []);

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

      const ck = mapCkpn[code] || {};
      const incNoa = r.inc_noa, incOs = r.inc_os, incPct = r.inc_pct;

      const clsIncOs  = (Number(incOs||0) > 0) ? 'neg' : (Number(incOs||0) < 0 ? 'pos' : '');
      const clsIncPct = (Number(incPct||0) > 0) ? 'neg' : (Number(incPct||0) < 0 ? 'pos' : '');
      const clsCkInc  = (Number(ck.ckpn_inc||0) > 0) ? 'neg' : (Number(ck.ckpn_inc||0) < 0 ? 'pos' : '');

      return `
      <tr class="${trClass}">
        <td class="border px-2 py-2 text-left">${name}</td>
        <td class="border px-2 py-2">${fmt(r.noa_m1)}</td>
        <td class="border px-2 py-2 text-right">${fmt(r.os_m1)}</td>
        <td class="border px-2 py-2">${fmt(r.noa_curr)}</td>
        <td class="border px-2 py-2 text-right">${fmt(r.os_curr)}</td>
        <td class="border px-2 py-2">${incNoa==null?'':fmt(incNoa)}</td>
        <td class="border px-2 py-2 text-right ${clsIncOs}">${incOs==null?'':fmt(incOs)}</td>
        <td class="border px-2 py-2 ${clsIncPct}">${incPct==null?'':pct2(incPct)}</td>
        <td class="border px-2 py-2 text-right">${ck.ckpn_m1==null?'':fmt(ck.ckpn_m1)}</td>
        <td class="border px-2 py-2 text-right">${ck.ckpn_curr==null?'':fmt(ck.ckpn_curr)}</td>
        <td class="border px-2 py-2 text-right ${clsCkInc}">${ck.ckpn_inc==null?'':fmt(ck.ckpn_inc)}</td>
      </tr>`;
    }).join('');

    tbody.innerHTML = html || `<tr><td colspan=\"11\" class=\"py-4 text-gray-500\">Tidak ada data</td></tr>`;
  }

  // ===== Render: Flow Rate =====
  function renderFlowRateGrid(oscData){
    const body=document.getElementById('bodyFlowRate');
    const fr = Array.isArray(oscData?.flow_rate) ? oscData.flow_rate : [];

    body.innerHTML = fr.map(x=>{
      const cls = (Number(x.inc_pct||0)>0)?'neg':(Number(x.inc_pct||0)<0?'pos':'');
      return `
        <tr>
          <td class="border px-2 py-2 text-left">${x.label||x.code}</td>
          <td class="border px-2 py-2">${pct2(x.actual_pct)}</td>
          <td class="border px-2 py-2">${pct2(x.appetite_pct)}</td>
          <td class="border px-2 py-2 ${cls}">${pct2(x.inc_pct)}</td>
        </tr>`;
    }).join('') || `<tr><td colspan=\"4\" class=\"py-3 text-gray-500\">Tidak ada data</td></tr>`;

    // portfolio metrics
    const pm = oscData?.portfolio_metrics || {};
    const rr = pm.repayment_rate || {};
    const d30= pm.dpd_30_plus || {};
    const d90= pm.dpd_90_plus || {};
    const rrCls  = (+rr.inc_pct>0)?'neg':(+rr.inc_pct<0?'pos':'');
    const d30Cls = (+d30.inc_pct>0)?'neg':(+d30.inc_pct<0?'pos':'');
    const d90Cls = (+d90.inc_pct>0)?'neg':(+d90.inc_pct<0?'pos':'');
    document.getElementById('pmRepay').innerHTML =
      `${pct2(rr.m1_pct)} → ${pct2(rr.actual_pct)} <span class="${rrCls}">(${(rr.inc_pct>=0?'+':'')}${Math.abs(+rr.inc_pct||0).toFixed(2)}%)</span>`;
    document.getElementById('pm30').innerHTML =
      `${pct2(d30.m1_pct)} → ${pct2(d30.actual_pct)} <span class="${d30Cls}">(${(d30.inc_pct>=0?'+':'')}${Math.abs(+d30.inc_pct||0).toFixed(2)}%)</span>`;
    document.getElementById('pm90').innerHTML =
      `${pct2(d90.m1_pct)} → ${pct2(d90.actual_pct)} <span class="${d90Cls}">(${(d90.inc_pct>=0?'+':'')}${Math.abs(+d90.inc_pct||0).toFixed(2)}%)</span>`;
  }

  // ===== Render: KOL =====
  function renderKolGrid(kolData){
    const body=document.getElementById('bodyKol');
    const rows = Array.isArray(kolData?.data?.rows) ? kolData.data.rows : [];
    const totals = kolData?.data?.total_osc || {};
    const npl    = kolData?.data?.data?.npl || kolData?.data?.npl || {};

    const order = ['Realisasi','L','DP','KL','D','M','Lunas'];
    const sorted = rows.slice().sort((a,b)=> order.indexOf(a.kol) - order.indexOf(b.kol));

    const tr = sorted.map(r=>{
      // + hijau, − merah
      const cls = (Number(r.inc_os||0)>0)?'pos':(Number(r.inc_os||0)<0?'neg':'');
      return `
        <tr>
          <td class="border px-2 py-2 text-left">${r.kol}</td>
          <td class="border px-2 py-2">${fmt(r.m1_noa)}</td>
          <td class="border px-2 py-2 text-right">${fmt(r.m1_os)}</td>
          <td class="border px-2 py-2">${fmt(r.act_noa)}</td>
          <td class="border px-2 py-2 text-right">${fmt(r.act_os)}</td>
          <td class="border px-2 py-2 text-right ${cls}">${r.inc_os==null?'':fmt(r.inc_os)}</td>
        </tr>`;
    }).join('');

    const totalCls = (+totals.inc_os>0)?'pos':(+totals.inc_os<0?'neg':'');
    const totalRow = `
      <tr class="subhead">
        <td class="border px-2 py-2 text-left">TOTAL</td>
        <td class="border px-2 py-2">${fmt(totals.m1_noa)}</td>
        <td class="border px-2 py-2 text-right">${fmt(totals.m1_os)}</td>
        <td class="border px-2 py-2">${fmt(totals.act_noa)}</td>
        <td class="border px-2 py-2 text-right">${fmt(totals.act_os)}</td>
        <td class="border px-2 py-2 text-right ${totalCls}">${fmt(totals.inc_os||0)}</td>
      </tr>`;

    body.innerHTML = (tr+totalRow) || `<tr><td colspan=\"6\" class=\"py-3 text-gray-500\">Tidak ada data</td></tr>`;

    // NPL cards
    document.getElementById('nplM1').textContent = pct2(npl.m1_pct);
    document.getElementById('nplAct').textContent= pct2(npl.actual_pct);
    const incAbs = Math.abs(+npl.inc_pct||0).toFixed(2);
    const incCls = (+npl.inc_pct>0)?'neg':(+npl.inc_pct<0?'pos':'');
    document.getElementById('nplInc').innerHTML  = `<span class="${incCls}">${(npl.inc_pct>=0?'+':'-')}${incAbs}%</span>`;
  }
</script>
