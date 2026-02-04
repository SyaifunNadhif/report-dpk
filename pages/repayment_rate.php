<style>
  :root { --h-header: 34px; }
  .inp { border:1px solid #cbd5e1; border-radius:6px; padding:0 8px; font-size:12px; background:#fff; height:32px; transition:all 0.2s; outline:none; }
  .inp:focus { border-color:#2563eb; box-shadow:0 0 0 2px rgba(37,99,235,0.1); }
  .lbl { font-size:10px; color:#64748b; font-weight:700; margin-bottom:2px; text-transform:uppercase; letter-spacing:0.03em; }
  .field { display:flex; flex-direction:column; }
  .pill { display:inline-flex; align-items:center; padding:4px 10px; border-radius:99px; border:1px solid; font-size:11px; font-weight:600; }
  .pill-blue { background:#eff6ff; color:#1e40af; border-color:#bfdbfe; }
  .pill-green { background:#ecfdf5; color:#065f46; border-color:#a7f3d0; }
  .pill-red { background:#fef2f2; color:#b91c1c; border-color:#fecaca; }
  .pill-purple { background:#faf5ff; color:#6b21a8; border-color:#e9d5ff; }
  .custom-scrollbar::-webkit-scrollbar { width:6px; height:6px; }
  .custom-scrollbar::-webkit-scrollbar-track { background:#f1f5f9; }
  .custom-scrollbar::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:3px; }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover { background:#94a3b8; }
  .sticky-col { position:sticky; left:0; z-index:20; background:inherit; }
  .sticky-header { position:sticky; top:0; z-index:30; box-shadow:0 1px 2px rgba(0,0,0,0.05); }
  .animate-scale-up { animation: scaleUp 0.2s ease-out forwards; }
  .animate-fade-in { animation: fadeIn 0.3s ease-in; }
  @keyframes scaleUp { from{transform:scale(0.95);opacity:0} to{transform:scale(1);opacity:1} }
  @keyframes fadeIn { from{opacity:0} to{opacity:1} }
</style>

<div class="max-w-[1920px] mx-auto px-4 py-5 h-screen flex flex-col font-sans text-slate-800 bg-slate-50">
  
  <div class="mb-4 flex-none">
    <h1 class="text-2xl font-bold flex items-center gap-2 text-slate-800">
      <span class="p-1.5 bg-blue-600 text-white rounded shadow-sm">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
      </span>
      Monitoring Repayment Rate (RR)
    </h1>
  </div>

  <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 mb-4 flex-none">
    <div id="summaryWrapRR" class="hidden flex flex-wrap items-center gap-2 animate-fade-in order-2 lg:order-1">
      <span class="pill pill-blue">Target: <b id="sum_os_target" class="ml-1">0</b></span>
      <span class="pill pill-green">Lancar: <b id="sum_os_lancar" class="ml-1">0</b></span>
      <span class="pill pill-red">Ditagih: <b id="sum_os_tagih" class="ml-1">0</b></span>
      <span class="pill pill-purple">Recovery: <b id="sum_persen" class="ml-1">0%</b></span>
    </div>

    <form id="formFilterRR" class="flex flex-wrap items-end gap-2 order-1 lg:order-2 ml-auto bg-white p-2.5 rounded-lg border border-slate-200 shadow-sm">
      <div class="field">
        <label class="lbl">Closing (M-1)</label>
        <input type="date" id="closing_date" class="inp" required>
      </div>
      <div class="field">
        <label class="lbl">Actual (Harian)</label>
        <input type="date" id="harian_date" class="inp" required>
      </div>
      <div class="field">
        <label class="lbl">Kantor</label>
        <select id="opt_kantor" class="inp min-w-[160px]"><option value="">Loading...</option></select>
      </div>
      <button type="submit" id="btnFilterRR" class="h-[32px] px-4 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-bold uppercase tracking-wider transition shadow-sm flex items-center">
        Tampilkan
      </button>
    </form>
  </div>

  <div class="flex-1 min-h-0 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm relative">
    <div id="loadingRR" class="hidden absolute inset-0 bg-white/80 z-50 flex flex-col items-center justify-center text-blue-600">
        <svg class="animate-spin h-8 w-8 mb-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
        <span class="text-xs font-bold uppercase tracking-wide">Memuat Data...</span>
    </div>

    <div class="h-full overflow-auto custom-scrollbar">
      <table class="min-w-full text-xs text-center border-separate border-spacing-0 text-slate-600">
        <thead class="bg-slate-100 text-slate-600 font-bold sticky-header">
          <tr>
            <th rowspan="2" class="px-2 py-2 border-b border-r bg-slate-100 sticky left-0 z-20 min-w-[50px]">TGL</th>
            <th colspan="2" class="px-2 py-1 border-b border-r bg-blue-50 text-blue-800">TARGET (M-1)</th>
            <th colspan="2" class="px-2 py-1 border-b border-r bg-green-50 text-green-800">OTP (LANCAR)</th>
            <th colspan="2" class="px-2 py-1 border-b border-r bg-red-50 text-red-800">DITAGIH</th>
            <th colspan="5" class="px-2 py-1 border-b bg-purple-50 text-purple-800">RECOVERY / PEMBAYARAN</th>
          </tr>
          <tr class="text-[10px] uppercase">
            <th class="px-2 py-1 border-b border-r bg-blue-50 text-blue-700/70">NOA</th>
            <th class="px-2 py-1 border-b border-r bg-blue-50 text-blue-700/70">OS</th>
            <th class="px-2 py-1 border-b border-r bg-green-50 text-green-700/70">NOA</th>
            <th class="px-2 py-1 border-b border-r bg-green-50 text-green-700/70">OS</th>
            <th class="px-2 py-1 border-b border-r bg-red-50 text-red-700/70">NOA</th>
            <th class="px-2 py-1 border-b border-r bg-red-50 text-red-700/70">OS</th>
            <th class="px-2 py-1 border-b border-r bg-purple-50 text-purple-700/70">NOA</th>
            <th class="px-2 py-1 border-b border-r bg-purple-50 text-purple-700/70">LUNAS</th>
            <th class="px-2 py-1 border-b border-r bg-purple-50 text-purple-700/70">ANGSURAN</th>
            <th class="px-2 py-1 border-b border-r bg-purple-50 text-purple-700/70">TOTAL</th>
            <th class="px-2 py-1 border-b bg-purple-50 text-purple-700/70">%</th>
          </tr>
        </thead>
        <tbody id="bodyRR" class="divide-y divide-slate-100 bg-white"></tbody>
        <tfoot id="footRR" class="bg-slate-800 text-white font-bold sticky bottom-0 z-30 text-[11px]"></tfoot>
      </table>
    </div>
  </div>
</div>

<div id="modalDetailRR" class="fixed inset-0 hidden z-[9999] flex items-center justify-center p-4">
  <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModalRR()"></div>
  <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-7xl max-h-[90vh] flex flex-col animate-scale-up overflow-hidden">
    
    <div class="flex items-center justify-between px-6 py-4 border-b bg-white">
      <div>
        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
            <span class="w-2 h-6 bg-blue-600 rounded-full"></span> 
            <span id="modalTitleRR">Detail Nasabah</span>
        </h3>
        <p class="text-xs text-slate-500 mt-0.5 ml-3" id="modalSubTitleRR">...</p>
      </div>
      <div class="flex items-center gap-3">
          <button onclick="downloadExcelFull()" class="flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded text-xs font-bold shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            Excel
          </button>
          <button onclick="closeModalRR()" class="text-slate-300 hover:text-red-500 transition text-2xl leading-none">&times;</button>
      </div>
    </div>

    <div class="flex-1 overflow-auto bg-slate-50 relative min-h-[300px]">
      <div id="loadingModalRR" class="hidden absolute inset-0 bg-white/90 z-20 flex flex-col items-center justify-center text-blue-600">
         <span class="animate-spin rounded-full h-8 w-8 border-4 border-blue-500 border-t-transparent mb-2"></span>
         <span class="text-xs font-bold uppercase">Memuat Detail...</span>
      </div>
      
      <table class="w-full text-xs text-left text-slate-600 border-separate border-spacing-0" id="tableExportRR">
        <thead id="headModalRR" class="text-xs text-slate-500 uppercase bg-slate-100 sticky top-0 shadow-sm z-10"></thead>
        <tbody id="bodyModalRR" class="divide-y divide-slate-200 bg-white"></tbody>
      </table>
    </div>

    <div class="px-6 py-3 border-t bg-white flex justify-between items-center">
      <span class="text-xs font-bold text-slate-400" id="pageInfoRR">0 Data</span>
      <div class="flex gap-2">
          <button id="btnPrevRR" onclick="changePageDetail(-1)" class="px-3 py-1.5 bg-white border border-slate-300 rounded text-xs font-medium hover:bg-slate-50 disabled:opacity-50 transition shadow-sm">Prev</button>
          <button id="btnNextRR" onclick="changePageDetail(1)" class="px-3 py-1.5 bg-white border border-slate-300 rounded text-xs font-medium hover:bg-slate-50 disabled:opacity-50 transition shadow-sm">Next</button>
      </div>
    </div>
  </div>
</div>

<script>
  const API_RR_URL = './api/rr'; 
  const nfID = new Intl.NumberFormat('id-ID');
  const fmt  = n => nfID.format(Number(n||0));
  const apiCall = (url, opt={}) => (window.apiFetch ? window.apiFetch(url,opt) : fetch(url,opt));

  let abortRR;
  let currentDetailParams = {};
  let currentDetailPage = 1;
  let currentDetailTotalPages = 1;
  let currentMode = 'NORMAL'; 
  const detailLimit = 10;

  window.addEventListener('DOMContentLoaded', async () => {
    const user = (window.getUser && window.getUser()) || JSON.parse(localStorage.getItem('app_user')) || { kode: '000' };
    const uKode = String(user.kode || '000').padStart(3, '0');
    await populateKantor(uKode);

    const d = await getLastHarianData(); 
    if(d) {
        document.getElementById('closing_date').value = d.last_closing;
        document.getElementById('harian_date').value  = d.last_created;
    }
    fetchRekapRR();
  });

  async function getLastHarianData(){ try{ const r=await apiCall('./api/date/'); const j=await r.json(); return j.data||null; }catch{ return null; } }
  
  async function populateKantor(uKode) {
    const el = document.getElementById('opt_kantor'); if(!el) return;
    if (uKode !== '000' && uKode !== '099') { el.innerHTML = `<option value="${uKode}">CABANG ${uKode}</option>`; el.disabled = true; return; }
    try {
        const r = await apiCall('./api/kode/', { method: 'POST', body: JSON.stringify({ type: 'kode_kantor' }) });
        const j = await r.json();
        let h = '<option value="">KONSOLIDASI (SEMUA)</option>';
        if(j.data) j.data.filter(x => x.kode_kantor !== '000').forEach(x => { h += `<option value="${x.kode_kantor}">${x.kode_kantor} - ${x.nama_kantor}</option>`; });
        el.innerHTML = h;
    } catch { el.innerHTML = '<option value="">KONSOLIDASI (SEMUA)</option>'; }
  }

  document.getElementById('formFilterRR').addEventListener('submit', e => { e.preventDefault(); fetchRekapRR(); });

  async function fetchRekapRR(){
    const l = document.getElementById('loadingRR');
    const tb = document.getElementById('bodyRR');
    const ft = document.getElementById('footRR');
    
    if(abortRR) abortRR.abort();
    abortRR = new AbortController();

    l.classList.remove('hidden'); 
    document.getElementById('summaryWrapRR').classList.add('hidden');
    tb.innerHTML = `<tr><td colspan="11" class="py-12 text-center text-slate-400 italic">Memuat data...</td></tr>`;
    ft.innerHTML = '';

    try {
        const payload = { 
            type: 'rekap_rr', 
            closing_date: document.getElementById('closing_date').value, 
            harian_date: document.getElementById('harian_date').value, 
            kode_kantor: document.getElementById('opt_kantor').value || null 
        };
        const res = await apiCall(API_RR_URL, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload), signal: abortRR.signal });
        const json = await res.json();
        
        if(json.status !== 200) throw new Error(json.message);
        renderTableRR(json.data.data || [], json.data.grand_total);
        
    } catch(err) {
        if(err.name !== 'AbortError') tb.innerHTML=`<tr><td colspan="11" class="py-12 text-center text-red-500 font-medium">${err.message||'Gagal memuat data'}</td></tr>`;
    } finally { l.classList.add('hidden'); }
  }

  function renderTableRR(rows, gt) {
      const tb = document.getElementById('bodyRR'); const ft = document.getElementById('footRR');
      tb.innerHTML = '';
      if(rows.length === 0){ tb.innerHTML = `<tr><td colspan="11" class="py-12 text-center text-slate-400">Tidak ada data.</td></tr>`; return; }

      let h = '';
      rows.forEach(r => {
          const bg = (r.persen < 50 && r.target_os > 0) ? 'bg-red-50/20' : '';
          
          const clkAll = `<a href="javascript:void(0)" onclick="initModalDetail(${r.tgl},'ALL')" class="font-bold text-blue-600 hover:underline cursor-pointer">${fmt(r.target_os)}</a>`;
          const clkLcr = `<a href="javascript:void(0)" onclick="initModalDetail(${r.tgl},'LANCAR')" class="font-bold text-green-600 hover:underline cursor-pointer">${fmt(r.lancar_os)}</a>`;
          const clkTgh = `<a href="javascript:void(0)" onclick="initModalDetail(${r.tgl},'MENUNGGAK')" class="font-bold text-red-600 hover:underline cursor-pointer">${fmt(r.macet_os)}</a>`;
          const clkLns = `<a href="javascript:void(0)" onclick="initModalLunas(${r.tgl})" class="font-bold text-slate-600 hover:text-blue-600 hover:underline cursor-pointer">${fmt(r.lunas_os)}</a>`;

          h += `
            <tr class="hover:bg-blue-50 transition border-b group h-[34px] ${bg}">
                <td class="px-2 sticky left-0 bg-white group-hover:bg-blue-50 border-r font-bold text-slate-700">${r.tgl}</td>
                <td class="px-2 border-r text-right text-slate-500">${fmt(r.target_noa)}</td>
                <td class="px-2 border-r text-right bg-blue-50/30">${clkAll}</td>
                <td class="px-2 border-r text-right text-slate-500">${fmt(r.lancar_noa)}</td>
                <td class="px-2 border-r text-right bg-green-50/30">${clkLcr}</td>
                <td class="px-2 border-r text-right text-slate-500">${fmt(r.macet_noa)}</td>
                <td class="px-2 border-r text-right bg-red-50/30">${clkTgh}</td>
                <td class="px-2 border-r text-right text-slate-400 bg-slate-50/50">${fmt(r.lunas_noa)}</td>
                <td class="px-2 border-r text-right bg-gray-50 text-gray-800">${clkLns}</td>
                <td class="px-2 border-r text-right text-slate-400">${fmt(r.angsuran)}</td>
                <td class="px-2 border-r text-right font-bold text-purple-700 bg-purple-50/20">${fmt(r.total_bayar)}</td>
                <td class="px-2 font-bold ${r.persen>=90?'text-green-600':'text-orange-500'}">${r.persen}%</td>
            </tr>`;
      });
      tb.innerHTML = h;

      if(gt) {
        ft.innerHTML = `
            <tr class="h-[40px]">
                <td class="sticky-col px-2 bg-slate-800 border-r border-slate-600">TOTAL</td>
                <td class="border-r border-slate-600 px-2 text-right font-mono">${fmt(gt.target_noa)}</td>
                <td class="border-r border-slate-600 px-2 text-right font-mono text-blue-300">${fmt(gt.target_os)}</td>
                <td class="border-r border-slate-600 px-2 text-right font-mono text-slate-400">${fmt(gt.lancar_noa)}</td>
                <td class="border-r border-slate-600 px-2 text-right font-mono text-green-400">${fmt(gt.lancar_os)}</td>
                <td class="border-r border-slate-600 px-2 text-right font-mono text-slate-400">${fmt(gt.macet_noa)}</td>
                <td class="border-r border-slate-600 px-2 text-right font-mono text-red-400">${fmt(gt.macet_os)}</td>
                <td class="border-r border-slate-600 px-2 text-right font-mono text-slate-400 font-bold bg-slate-700">${fmt(gt.lunas_noa)}</td>
                <td class="border-r border-slate-600 px-2 text-right font-mono text-slate-400">${fmt(gt.lunas_os)}</td>
                <td class="border-r border-slate-600 px-2 text-right font-mono text-slate-400">${fmt(gt.angsuran)}</td>
                <td class="border-r border-slate-600 px-2 text-right font-mono text-purple-300">${fmt(gt.total_bayar)}</td>
                <td class="px-2 font-bold">${gt.persen}%</td>
            </tr>`;
        
        document.getElementById('summaryWrapRR').classList.remove('hidden');
        document.getElementById('sum_os_target').textContent = fmt(gt.target_os);
        document.getElementById('sum_os_lancar').textContent = fmt(gt.lancar_os);
        document.getElementById('sum_os_tagih').textContent = fmt(gt.macet_os);
        document.getElementById('sum_persen').textContent = gt.persen + '%';
      }
  }

  // --- MODAL FUNCTIONS ---
  function initModalDetail(tgl, status) {
      currentMode = 'NORMAL';
      currentDetailParams = { type: 'detail_rr', closing_date: document.getElementById('closing_date').value, harian_date: document.getElementById('harian_date').value, kode_kantor: document.getElementById('opt_kantor').value || null, tgl_tagih: tgl, status: status, limit: detailLimit };

      document.getElementById('headModalRR').innerHTML = `
          <tr>
            <th class="px-4 py-3 bg-slate-100 border-b">No Rekening</th>
            <th class="px-4 py-3 bg-slate-100 border-b">Nama Nasabah</th>
            <th class="px-4 py-3 bg-slate-100 border-b text-center text-blue-700">Nama AO</th>
            <th class="px-4 py-3 bg-slate-100 border-b text-center">Tgl JT</th>
            <th class="px-4 py-3 bg-slate-100 border-b text-right">Plafond</th>
            <th class="px-4 py-3 bg-blue-50 text-blue-700 border-b border-l text-right">Target (M-1)</th>
            <th class="px-4 py-3 bg-green-50 text-green-700 border-b border-l text-right">Actual</th>
            <th class="px-4 py-3 bg-red-50 text-red-700 border-b border-l text-right">Tot Tunggakan</th>
            <th class="px-4 py-3 bg-slate-100 border-b text-center">DPD</th>
            <th class="px-4 py-3 bg-slate-100 border-b text-center">Status</th>
          </tr>`;

      document.getElementById('modalTitleRR').textContent = `Detail Penagihan - Tgl ${tgl}`;
      document.getElementById('modalSubTitleRR').textContent = `Status: ${status}`;
      document.getElementById('modalDetailRR').classList.remove('hidden');
      loadDetailPage(1);
  }

  function initModalLunas(tgl) {
      currentMode = 'LUNAS';
      currentDetailParams = { type: 'detail_lunas_rr', closing_date: document.getElementById('closing_date').value, harian_date: document.getElementById('harian_date').value, kode_kantor: document.getElementById('opt_kantor').value || null, tgl_tagih: tgl, limit: detailLimit };

      document.getElementById('headModalRR').innerHTML = `
          <tr>
            <th class="px-4 py-3 bg-slate-100 border-b">Nama Nasabah</th>
            <th class="px-4 py-3 bg-slate-100 border-b text-center text-blue-700">Nama AO</th>
            <th class="px-4 py-3 bg-slate-100 border-b">Rek Lama (Lunas)</th>
            <th class="px-4 py-3 bg-slate-100 border-b border-l text-right">Plafond Lama</th>
            <th class="px-4 py-3 bg-blue-50 text-blue-700 border-b border-l text-right">OS M-1</th>
            <th class="px-4 py-3 bg-slate-100 border-b border-l text-center">Status</th>
            <th class="px-4 py-3 bg-green-50 text-green-700 border-b border-l text-center">Rek Baru</th>
            <th class="px-4 py-3 bg-green-50 text-green-700 border-b text-right">Plafond Baru</th>
            <th class="px-4 py-3 bg-green-50 text-green-700 border-b text-center">Tgl Realisasi</th>
          </tr>`;

      document.getElementById('modalTitleRR').textContent = `Detail Pelunasan - Tgl ${tgl}`;
      document.getElementById('modalSubTitleRR').textContent = `Cek Refinancing vs Prospek`;
      document.getElementById('modalDetailRR').classList.remove('hidden');
      loadDetailPage(1);
  }

  async function loadDetailPage(page) {
      const l = document.getElementById('loadingModalRR'); const tb = document.getElementById('bodyModalRR'); const info = document.getElementById('pageInfoRR');
      l.classList.remove('hidden'); tb.innerHTML = '';

      try {
          const payload = { ...currentDetailParams, page: page };
          const res = await apiCall(API_RR_URL, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          const json = await res.json();
          const list = json.data?.data || [];
          const meta = json.data?.pagination || { total_records:0, total_pages:1 };

          currentDetailPage = page; currentDetailTotalPages = meta.total_pages;

          if(list.length === 0) {
              tb.innerHTML = `<tr><td colspan="10" class="py-12 text-center text-slate-400 italic">Tidak ada data detail.</td></tr>`;
              info.innerText = `0 Data`;
          } else {
              let h = '';
              list.forEach(r => {
                  // Logic Nama AO (Max 2 Kata)
                  const aoName = (r.nama_ao || '-').split(' ').slice(0, 2).join(' ');

                  if(currentMode === 'NORMAL') {
                      let badge = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">${r.status_ket}</span>`;
                      if(r.status_ket==='LANCAR') badge = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700 border border-green-200">LANCAR</span>`;
                      else if(r.status_ket==='MENUNGGAK') badge = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 border border-red-200">MENUNGGAK</span>`;

                      h += `<tr class="hover:bg-slate-50 transition border-b">
                            <td class="px-4 py-2 font-mono text-xs text-slate-600">${r.no_rekening}</td>
                            <td class="px-4 py-2 font-medium text-slate-800 truncate max-w-[200px]" title="${r.nama_nasabah}">${r.nama_nasabah}</td>
                            <td class="px-4 py-2 text-center font-bold text-xs text-blue-700 bg-blue-50/10">${aoName}</td>
                            <td class="px-4 py-2 text-center font-mono text-xs text-slate-500">${r.tgl_jatuh_tempo||'-'}</td>
                            <td class="px-4 py-2 text-right text-slate-500 text-xs">${fmt(r.jml_pinjaman)}</td>
                            <td class="px-4 py-2 text-right font-bold text-blue-700 bg-blue-50/20 border-l">${fmt(r.os_m1)}</td>
                            <td class="px-4 py-2 text-right font-bold text-green-700 bg-green-50/20 border-l">${fmt(r.os_curr)}</td>
                            <td class="px-4 py-2 text-right font-bold text-red-600 bg-red-50/20 border-l">${fmt(r.totung)}</td>
                            <td class="px-4 py-2 text-center font-bold text-slate-700">${r.dpd_curr}</td>
                            <td class="px-4 py-2 text-center">${badge}</td>
                        </tr>`;
                  } else {
                      let badge = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700 border border-blue-200">PROSPEK</span>`;
                      if(r.status_lunas === 'REFINANCING') badge = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700 border border-green-200">REFINANCING</span>`;

                      h += `<tr class="hover:bg-slate-50 transition border-b">
                            <td class="px-4 py-2 font-medium text-slate-800 truncate max-w-[200px]">
                                ${r.nama_nasabah}
                                <div class="text-[10px] text-slate-400 font-mono">ID: ${r.nasabah_id}</div>
                            </td>
                            <td class="px-4 py-2 text-center font-bold text-xs text-blue-700 bg-blue-50/10">${aoName}</td>
                            <td class="px-4 py-2 font-mono text-xs text-slate-600">${r.no_rekening}</td>
                            <td class="px-4 py-2 text-right text-slate-600 bg-slate-50 border-l border-slate-200">${fmt(r.plafon_lama)}</td>
                            <td class="px-4 py-2 text-right font-bold text-blue-700 bg-blue-50/20 border-l">${fmt(r.os_lunas)}</td>
                            <td class="px-4 py-2 text-center border-l">${badge}</td>
                            <td class="px-4 py-2 font-mono text-xs text-center bg-green-50/20 border-l text-green-800 font-bold">${r.rek_baru}</td>
                            <td class="px-4 py-2 text-right bg-green-50/20 text-green-700 font-bold">${fmt(r.plafond_baru)}</td>
                            <td class="px-4 py-2 text-center bg-green-50/20 text-xs text-green-700">${r.tgl_baru}</td>
                        </tr>`;
                  }
              });
              tb.innerHTML = h;
              const start = ((page - 1) * detailLimit) + 1;
              const end = Math.min(page * detailLimit, meta.total_records);
              info.innerText = `Menampilkan ${start} - ${end} dari ${fmt(meta.total_records)} Data`;
          }
          document.getElementById('btnPrevRR').disabled = page <= 1;
          document.getElementById('btnNextRR').disabled = page >= meta.total_pages;
      } catch(err){ console.error(err); } finally { l.classList.add('hidden'); }
  }

  async function downloadExcelFull() {
      const btn = event.target.closest('button'); const txt = btn.innerHTML;
      btn.innerHTML = `<span class="animate-spin inline-block h-4 w-4 border-2 border-white border-t-transparent rounded-full mr-2"></span> Downloading...`;
      btn.disabled = true;

      try {
          const payload = { ...currentDetailParams, page: 1, limit: 10000 };
          const res = await apiCall(API_RR_URL, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          const json = await res.json();
          const rows = json.data?.data || [];

          if(rows.length === 0) { alert("Tidak ada data untuk diexport"); return; }

          let csv = "";
          if(currentMode === 'NORMAL') {
              csv = `No Rekening\tNama Nasabah\tNama AO\tTgl JT\tPlafond\tTarget (M-1)\tActual (Curr)\tTot Tunggakan\tDPD\tStatus\n`;
              rows.forEach(r => {
                  csv += `'${r.no_rekening}\t${r.nama_nasabah}\t${r.nama_ao}\t${r.tgl_jatuh_tempo}\t${Math.floor(r.jml_pinjaman)}\t${Math.floor(r.os_m1)}\t${Math.floor(r.os_curr)}\t${Math.floor(r.totung)}\t${r.dpd_curr}\t${r.status_ket}\n`;
              });
          } else {
              csv = `Nama Nasabah\tID Nasabah\tNama AO\tRek Lama\tPlafond Lama\tOS Lunas (M-1)\tStatus\tRek Baru\tPlafond Baru\tTgl Realisasi Baru\n`;
              rows.forEach(r => {
                  csv += `${r.nama_nasabah}\t'${r.nasabah_id}\t${r.nama_ao}\t'${r.no_rekening}\t${Math.floor(r.plafon_lama)}\t${Math.floor(r.os_lunas)}\t${r.status_lunas}\t'${r.rek_baru}\t${Math.floor(r.plafond_baru)}\t${r.tgl_baru}\n`;
              });
          }

          const blob = new Blob([csv], { type: 'application/vnd.ms-excel' });
          const url = window.URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.href = url;
          a.download = `RR_Detail_${currentMode}_${currentDetailParams.tgl_tagih}.xls`;
          document.body.appendChild(a); a.click(); document.body.removeChild(a);

      } catch(e) { console.error(e); alert("Gagal export data."); } 
      finally { btn.innerHTML = txt; btn.disabled = false; }
  }

  window.changePageDetail = (step) => { const n = currentDetailPage + step; if (n > 0 && n <= currentDetailTotalPages) loadDetailPage(n); }
  window.closeModalRR = () => document.getElementById('modalDetailRR').classList.add('hidden');
  document.addEventListener('keydown', e => { if(e.key === 'Escape') closeModalRR(); });
</script>