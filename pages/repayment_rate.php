<div class="max-w-[1920px] mx-auto px-2 md:px-4 py-4 md:py-6 h-[calc(100vh-80px)] flex flex-col font-sans text-slate-800 bg-slate-50">
  
  <div class="flex-none mb-4 flex flex-col xl:flex-row justify-between items-start xl:items-end gap-4 w-full">
    
    <div class="flex flex-col gap-1.5 shrink-0 w-full xl:w-auto">
      <h1 class="text-xl md:text-2xl font-bold flex items-center gap-2 text-slate-800">
        <span class="p-1.5 md:p-2 bg-blue-600 text-white rounded-lg shadow-sm text-sm">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
        </span>
        <span>Monitoring Repayment Rate</span>
      </h1>
      </div>

    <form id="formFilterRR" class="flex flex-wrap md:flex-nowrap items-end gap-2 bg-white p-2 md:p-2.5 rounded-xl border border-slate-200 shadow-sm shrink-0 xl:ml-auto w-full md:w-auto overflow-x-auto no-scrollbar">
      
      <div class="field shrink-0 w-[120px] md:w-[140px]">
        <label class="lbl">Closing (M-1)</label>
        <input type="date" id="closing_date" class="inp text-sm font-semibold text-slate-700" required onclick="try{this.showPicker()}catch(e){}">
      </div>
      
      <div class="field shrink-0 w-[120px] md:w-[140px]">
        <label class="lbl">Actual (Harian)</label>
        <input type="date" id="harian_date" class="inp text-sm font-semibold text-slate-700" required onclick="try{this.showPicker()}catch(e){}">
      </div>
      
      <div class="field shrink-0 w-[150px] md:w-[180px]">
        <label class="lbl">Cabang</label>
        <select id="opt_kantor" class="inp text-sm font-semibold text-slate-700 truncate" onchange="fetchRekapRR()">
            <option value="">Loading...</option>
        </select>
      </div>

      <div class="flex items-center gap-1.5 shrink-0 h-[38px]">
        <button type="submit" class="btn-icon bg-blue-600 hover:bg-blue-700 px-4 md:px-5 h-full text-white rounded-lg shadow-sm" title="Cari Data">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          <span class="ml-1.5 text-sm font-bold uppercase tracking-wider hidden sm:inline">Cari</span>
        </button>
        
        <button type="button" onclick="exportExcelRekapRR()" class="btn-icon bg-emerald-600 hover:bg-emerald-700 px-3 md:px-4 h-full text-white rounded-lg shadow-sm" title="Download Excel Rekap">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
        </button>
      </div>
    </form>
  </div>

  <div class="flex-1 min-h-0 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm relative">
    
    <div id="loadingRR" class="hidden absolute inset-0 bg-white/80 z-[100] flex flex-col items-center justify-center text-blue-600 font-bold uppercase tracking-widest text-sm backdrop-blur-sm">
        <div class="animate-spin h-10 w-10 border-4 border-blue-200 border-t-blue-600 rounded-full mb-3"></div>
        <span>Menyiapkan Matriks...</span>
    </div>

    <div class="h-full overflow-auto custom-scrollbar relative">
      <table class="min-w-full text-center border-separate border-spacing-0 text-slate-700 table-fixed" id="tabelRekapRR">
        <thead class="uppercase bg-slate-100 text-slate-600 font-bold sticky top-0 z-50 text-[11px] md:text-xs tracking-wider">
          <tr>
            <th rowspan="2" class="head-row px-2 py-3 border-b border-r border-slate-200 bg-slate-100 sticky left-0 z-50 w-[80px] shadow-[1px_0_0_#e2e8f0]">TGL</th>
            <!-- <th class="head-row px-3 py-2 border-b border-r border-blue-200 bg-blue-50 text-blue-800">TARGET (M-1)</th>
            <th class="head-row px-3 py-2 border-b border-r border-green-200 bg-green-50 text-green-800">OTP (LANCAR)</th>
            <th class="head-row px-3 py-2 border-b border-r border-red-200 bg-red-50 text-red-800">DITAGIH</th> -->
            <th colspan="3" class="head-row px-3 py-2 border-b border-slate-200 bg-slate-100 text-purple-800">TOTAL OUTSTANDING</th>
            <th colspan="4" class="head-row px-3 py-2 border-b border-purple-200 bg-purple-50 text-purple-800">RECOVERY / PEMBAYARAN</th>
          </tr>
          <tr class="text-[10px] md:text-[11px]">
            <th class="head-row px-3 py-2 border-b border-r border-blue-200 bg-blue-50 text-blue-700 w-[180px]">TARGET (M-1)</th>
            <th class="head-row px-3 py-2 border-b border-r border-green-200 bg-green-50 text-green-700 w-[180px]">OTP (LANCAR)</th>
            <th class="head-row px-3 py-2 border-b border-r border-red-200 bg-red-50 text-red-700 w-[180px]">DITAGIH/th>
            <th class="head-row px-3 py-2 border-b border-r border-purple-100 bg-purple-50 text-purple-700 w-[160px]">LUNAS</th>
            <th class="head-row px-3 py-2 border-b border-r border-purple-100 bg-purple-50 text-purple-700 w-[160px]">ANGSURAN</th>
            <th class="head-row px-3 py-2 border-b border-r border-purple-200 bg-purple-50 text-purple-700 w-[160px]">TOTAL BAYAR</th>
            <th class="head-row px-2 py-2 border-b border-purple-200 bg-purple-50 text-purple-700 w-[80px]">%</th>
          </tr>
          <tr class="sticky-total font-bold text-sm" id="rowTotalRRAtas"></tr>
        </thead>
        <tbody id="bodyRR" class="divide-y divide-slate-100 bg-white group-tbody text-xs md:text-sm"></tbody>
      </table>
    </div>
  </div>
</div>

<div id="modalDetailRR" class="fixed inset-0 hidden z-[9999] flex items-center justify-center p-2 md:p-4">
  <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModalRR()"></div>
  <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-[1600px] h-[92vh] flex flex-col animate-scale-up overflow-hidden">
    
    <div class="flex items-center justify-between px-4 py-4 border-b bg-slate-50 shrink-0 flex-wrap gap-3">
      <div class="flex-1 min-w-[250px]">
        <h3 class="text-base md:text-xl font-bold text-slate-800 flex items-center gap-2">
            <span class="w-2 h-6 bg-blue-600 rounded-full hidden md:block"></span> 
            <span id="modalTitleRR">Detail Nasabah</span>
        </h3>
        <p class="text-xs md:text-sm text-slate-500 mt-1 md:ml-4 font-mono font-medium" id="modalSubTitleRR">...</p>
      </div>
      
      <div class="flex items-center gap-2 ml-auto shrink-0">
          <select id="opt_kankas_modal" class="inp py-1 h-9 md:h-10 w-[140px] md:w-[160px] text-xs md:text-sm font-bold text-blue-800 bg-blue-50 border-blue-200" onchange="loadDetailPage(1)">
              <option value="">Semua Kankas</option>
          </select>

          <button onclick="downloadExcelFull()" class="btn-icon bg-emerald-600 hover:bg-emerald-700 text-white px-3 md:px-4 h-9 md:h-10 text-xs md:text-sm font-bold uppercase tracking-wider rounded-lg">
            <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            <span class="hidden md:inline ml-1.5">Excel</span>
          </button>
          <button onclick="closeModalRR()" class="w-9 md:w-10 h-9 md:h-10 flex items-center justify-center rounded-xl bg-slate-200 hover:bg-red-500 hover:text-white text-slate-600 transition font-bold text-xl md:text-2xl leading-none">&times;</button>
      </div>
    </div>

    <div class="flex-1 overflow-auto bg-slate-50 relative p-0 md:p-4 custom-scrollbar">
      <div id="loadingModalRR" class="hidden absolute inset-0 bg-white/90 z-40 flex flex-col items-center justify-center text-blue-600 backdrop-blur-sm">
         <div class="animate-spin h-10 w-10 border-4 border-blue-200 border-t-blue-600 rounded-full mb-3"></div>
         <span class="text-sm font-bold uppercase tracking-widest">Memuat Detail...</span>
      </div>
      
      <table class="w-max min-w-full text-sm text-left text-slate-700 border border-slate-200 md:rounded-xl shadow-sm bg-white table-fixed" id="tableExportRR">
        <thead id="headModalRR" class="text-xs text-slate-600 uppercase bg-slate-100 sticky top-0 z-30 font-bold tracking-wider"></thead>
        <tbody id="bodyModalRR" class="divide-y divide-slate-100 bg-white modal-tbody"></tbody>
      </table>
    </div>

    <div class="px-4 py-4 md:px-5 border-t bg-white flex justify-between items-center shrink-0">
      <span class="text-xs md:text-sm font-bold text-slate-600 bg-slate-100 px-3 py-1 rounded-lg" id="pageInfoRR">0 Data</span>
      <div class="flex gap-2">
          <button id="btnPrevRR" onclick="changePageDetail(-1)" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 hover:border-slate-400 disabled:opacity-50 transition shadow-sm">« Prev</button>
          <button id="btnNextRR" onclick="changePageDetail(1)" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 hover:border-slate-400 disabled:opacity-50 transition shadow-sm">Next »</button>
      </div>
    </div>
  </div>
</div>

<style>
  /* Base Style */
  .inp { border:1px solid #cbd5e1; border-radius:8px; padding:0 12px; background:#fff; outline:none; transition: border 0.2s;}
  .inp:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,0.1); }
  .inp:disabled { background-color: #f1f5f9; color: #64748b; font-weight: 700; cursor: not-allowed; }
  .lbl { font-size:11px; color:#475569; font-weight:800; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.05em; display:block; white-space: nowrap;}
  .field { display:flex; flex-direction:column; }
  
  .btn-icon { display:inline-flex; align-items:center; justify-content:center; border:none; cursor:pointer; transition: transform 0.2s;}
  .btn-icon:hover { transform:translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }

  /* HIDE DATEPICKER ICON */
  input[type="date"]::-webkit-inner-spin-button, input[type="date"]::-webkit-calendar-picker-indicator { display: none; -webkit-appearance: none; }
  input[type="date"] { -moz-appearance: textfield; }

  /* Custom Scrollbar */
  .no-scrollbar::-webkit-scrollbar { display: none; }
  .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
  .custom-scrollbar::-webkit-scrollbar { width:8px; height:8px; }
  .custom-scrollbar::-webkit-scrollbar-track { background:#f1f5f9; border-radius: 8px;}
  .custom-scrollbar::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:8px; }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover { background:#94a3b8; }
  
  /* Animations */
  .animate-scale-up { animation: scaleUp 0.2s ease-out forwards; }
  @keyframes scaleUp { from{transform:scale(0.97);opacity:0} to{transform:scale(1);opacity:1} }
  
  /* REKAP TABLE: Hover & Sticky Z-Index */
  .group-tbody tr:hover td { background-color: #f8fafc; }
  .group-tbody tr:hover td.sticky { background-color: #f8fafc !important; }

  /* 🔥 CSS STICKY MODEREN 🔥 */
  /* Ganti logika z-index brittle pixel ke thead murni */
  #tabelRekapRR thead {
      position: sticky;
      top: 0;
      z-index: 40; /* Z-index dasar untuk kepala tabel */
  }

  /* Logika Sticky Kiri untuk TGL */
  #tabelRekapRR thead th.head-row.sticky.left-0 {
      z-index: 50; /* Z-index tertinggi untuk perempatan atas-kiri */
      position: sticky;
      left: 0;
  }

  /* Logika Baris TOTAL yang di JS diisi */
  #tabelRekapRR .sticky-total th {
      /* position: sticky; */ /* Hapus di sini */
      /* top: 80px; */ /* Hapus di sini (pemicu nabrak tgl 1) */
      z-index: 35; /* Di bawah Z-index thead utama */
      background: #f1f5f9 !important; 
      color: #1e293b; 
      border-bottom: 2px solid #cbd5e1; 
      padding-top: 12px;
      padding-bottom: 12px;
      box-shadow: 0 4px 6px -2px rgba(0,0,0,0.05);
  }

  /* Logika Sticky Kiri untuk TOTAL perempatan bawah-kiri */
  #tabelRekapRR .sticky-total th.sticky.left-0 { 
      z-index: 45; /* Di atas th .sticky-total standar, tapi di bawah th.head-row TGL */
      position: sticky; /* Keep Melayang Kiri */
      left: 0; /* Keep Melayang Kiri */
      background: #e2e8f0 !important; 
      border-right: 1px solid #cbd5e1;
  }

  /* 🔥 Hapus Media Query Brittle Pixel 🔥 */
  /* @media (min-width: 768px) { #tabelRekapRR .sticky-total th { top: 88px; } } <- DELETE THIS */

  /* MODAL TABLE: Sticky Columns CSS Logic (Sama Persis) */
  .modal-tbody tr:hover td { background-color: #f8fafc; }
  .modal-tbody tr:hover td.sticky { background-color: #f8fafc !important; }
  #headModalRR th.sticky.left-0 { z-index: 40; }
  #headModalRR th.sticky.left-\[130px\] { z-index: 40; }
</style>

<script>
  /* CONFIGURATION */
  const API_RR_URL = './api/rr'; 
  const API_KODE_URL = './api/kode/'; 
  const nfID = new Intl.NumberFormat('id-ID');
  const fmt  = n => nfID.format(Number(n||0));
  
  let rekapDataRaw = [];
  let rekapGtRaw = null;

  const apiCall = async (url, opt = {}) => {
      const res = await fetch(url, opt);
      try {
          const json = await res.json();
          return { ok: res.ok, status: res.status, json: json };
      } catch (e) {
          throw new Error("Gagal parsing JSON.");
      }
  };

  let abortRR;
  let currentDetailParams = {};
  let currentDetailPage = 1;
  let currentDetailTotalPages = 1;
  let currentMode = 'NORMAL'; 
  const detailLimit = 20;

  window.addEventListener('DOMContentLoaded', async () => {
    const user = (window.getUser && window.getUser()) || null;
    const uKode = (user && user.kode) ? String(user.kode).padStart(3, '0') : '000';
    
    await populateKantor(uKode);

    const d = await getLastHarianData(); 
    if(d) {
        document.getElementById('closing_date').value = d.last_closing;
        document.getElementById('harian_date').value  = d.last_created;
    } else {
        const now = new Date().toISOString().split('T')[0];
        document.getElementById('closing_date').value = now;
        document.getElementById('harian_date').value  = now;
    }
    
    fetchRekapRR();
  });

  async function getLastHarianData(){ 
      try{ const r = await fetch('./api/date/'); const j = await r.json(); return j.data||null; }catch{ return null; } 
  }
  
  async function populateKantor(uKode) {
    const el = document.getElementById('opt_kantor'); if(!el) return;
    if (uKode !== '000' && uKode !== '099') { 
        el.innerHTML = `<option value="${uKode}">CABANG ${uKode}</option>`; 
        el.value = uKode;
        el.disabled = true; 
        return; 
    }
    try {
        const r = await fetch(API_KODE_URL, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ type: 'kode_kantor' }) });
        const j = await r.json();
        let h = '<option value="">SEMUA CABANG</option>';
        if(j.data) j.data.filter(x => x.kode_kantor !== '000').forEach(x => { h += `<option value="${x.kode_kantor}">${x.kode_kantor} - ${x.nama_kantor}</option>`; });
        el.innerHTML = h;
    } catch { el.innerHTML = '<option value="">SEMUA CABANG</option>'; }
  }

  async function loadKankasModalDropdown() {
      const elKankas = document.getElementById('opt_kankas_modal');
      const branch = document.getElementById('opt_kantor').value;
      
      elKankas.innerHTML = '<option value="">Semua Kankas</option>';
      if(!branch || branch === '') return;

      try {
          const payload = { type: 'kode_kankas', kode_kantor: branch };
          const r = await fetch(API_KODE_URL, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload) });
          const j = await r.json();
          
          let h = '<option value="">Semua Kankas</option>';
          if(j.data && Array.isArray(j.data)) {
              j.data.forEach(x => { h += `<option value="${x.kode_group1}">${x.deskripsi_group1 || x.kode_group1}</option>`; });
          }
          elKankas.innerHTML = h;
      } catch(err) { }
  }

  document.getElementById('formFilterRR').addEventListener('submit', e => { e.preventDefault(); fetchRekapRR(); });

  async function fetchRekapRR(){
    const l = document.getElementById('loadingRR');
    const tb = document.getElementById('bodyRR');
    const trTotal = document.getElementById('rowTotalRRAtas'); 
    
    if(abortRR) abortRR.abort();
    abortRR = new AbortController();

    l.classList.remove('hidden'); 
    tb.innerHTML = `<tr><td colspan="8" class="py-20 text-center text-slate-400 italic text-base">Sedang mengambil data...</td></tr>`;
    trTotal.innerHTML = '';
    rekapDataRaw = [];
    rekapGtRaw = null;

    try {
        const payload = { 
            type: 'rekap_rr', 
            closing_date: document.getElementById('closing_date').value, 
            harian_date: document.getElementById('harian_date').value, 
            kode_kantor: document.getElementById('opt_kantor').value || null
        };
        
        const res = await apiCall(API_RR_URL, { 
            method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload), signal: abortRR.signal 
        });

        if(!res.ok || res.json.status !== 200) throw new Error(res.json.message || "Gagal memuat data");
        
        rekapDataRaw = res.json.data.data || [];
        rekapGtRaw = res.json.data.grand_total;

        renderTableRR(rekapDataRaw, rekapGtRaw);
        
    } catch(err) {
        if(err.name !== 'AbortError') {
            tb.innerHTML=`<tr><td colspan="8" class="py-16 text-center text-red-500 font-bold uppercase tracking-widest text-sm">Error: ${err.message}</td></tr>`;
        }
    } finally { l.classList.add('hidden'); }
  }

  /* 🔥 LOGIC RENDER TABEL (NOA DIBUNGKUS KE DALAM OS - SAMA PERSIS) 🔥 */
  function renderTableRR(rows, gt) {
      const tb = document.getElementById('bodyRR'); 
      const trTotal = document.getElementById('rowTotalRRAtas');
      
      tb.innerHTML = '';
      if(rows.length === 0){ tb.innerHTML = `<tr><td colspan="8" class="py-20 text-center text-slate-500 text-base">Tidak ada data penagihan.</td></tr>`; return; }

      if(gt) {
        trTotal.innerHTML = `
            <th class="px-2 sticky left-0 text-center uppercase tracking-widest shadow-[1px_0_0_#cbd5e1] text-sm">TOTAL</th>
            
            <th class="border-r border-slate-300 px-3 py-2 text-right">
                <div class="text-blue-800 font-extrabold text-sm md:text-base mb-0.5">${fmt(gt.target_os)}</div>
                <div class="text-[10px] text-slate-500 font-normal">NOA: <span class="font-bold text-slate-700">${fmt(gt.target_noa)}</span></div>
            </th>
            
            <th class="border-r border-slate-300 px-3 py-2 text-right">
                <div class="text-green-700 font-extrabold text-sm md:text-base mb-0.5">${fmt(gt.lancar_os)}</div>
                <div class="text-[10px] text-slate-500 font-normal">NOA: <span class="font-bold text-slate-700">${fmt(gt.lancar_noa)}</span></div>
            </th>
            
            <th class="border-r border-slate-300 px-3 py-2 text-right">
                <div class="text-red-600 font-extrabold text-sm md:text-base mb-0.5">${fmt(gt.macet_os)}</div>
                <div class="text-[10px] text-slate-500 font-normal">NOA: <span class="font-bold text-slate-700">${fmt(gt.macet_noa)}</span></div>
            </th>
            
            <th class="border-r border-slate-300 px-3 py-2 text-right">
                <div class="text-slate-700 font-extrabold text-sm md:text-base mb-0.5">${fmt(gt.lunas_os)}</div>
                <div class="text-[10px] text-slate-500 font-normal">NOA: <span class="font-bold text-slate-700">${fmt(gt.lunas_noa)}</span></div>
            </th>
            
            <th class="border-r border-slate-300 px-3 py-2 text-right">
                <div class="text-slate-700 font-extrabold text-sm md:text-base mb-0.5">${fmt(gt.angsuran)}</div>
            </th>
            
            <th class="border-r border-slate-300 px-3 py-2 text-right">
                <div class="text-purple-700 font-extrabold text-sm md:text-base mb-0.5">${fmt(gt.total_bayar)}</div>
            </th>
            
            <th class="px-2 text-center text-blue-700 font-black text-base md:text-xl">${gt.persen}%</th>
        `;
      }

      let h = '';
      rows.forEach(r => {
          const bg = (r.persen < 50 && r.target_os > 0) ? 'bg-red-50/20' : '';
          
          const clkAll = `<a href="javascript:void(0)" onclick="initModalDetail('${r.tgl}','ALL')" class="font-bold text-blue-700 hover:text-blue-800 hover:underline cursor-pointer block mb-0.5 text-sm">${fmt(r.target_os)}</a>`;
          const clkLcr = `<a href="javascript:void(0)" onclick="initModalDetail('${r.tgl}','LANCAR')" class="font-bold text-green-600 hover:text-green-700 hover:underline cursor-pointer block mb-0.5 text-sm">${fmt(r.lancar_os)}</a>`;
          const clkTgh = `<a href="javascript:void(0)" onclick="initModalDetail('${r.tgl}','MENUNGGAK')" class="font-bold text-red-600 hover:text-red-700 hover:underline cursor-pointer block mb-0.5 text-sm">${fmt(r.macet_os)}</a>`;
          const clkLns = `<a href="javascript:void(0)" onclick="initModalLunas('${r.tgl}')" class="font-bold text-slate-700 hover:text-blue-700 hover:underline cursor-pointer block mb-0.5 text-sm">${fmt(r.lunas_os)}</a>`;

          h += `
            <tr class="transition border-b border-slate-100 group h-[52px] ${bg}">
                <td class="px-2 py-2 sticky left-0 bg-white border-r border-slate-100 font-mono font-bold text-slate-700 text-center shadow-[1px_0_0_#f1f5f9] text-xs md:text-sm">${r.tgl}</td>
                
                <td class="px-3 py-2 border-r border-slate-100 text-right bg-blue-50/30 hover:bg-blue-100 transition">
                    ${clkAll}
                    <div class="text-[10px] text-slate-500">NOA: <span class="font-bold text-slate-600">${fmt(r.target_noa)}</span></div>
                </td>
                
                <td class="px-3 py-2 border-r border-slate-100 text-right bg-green-50/30 hover:bg-green-100 transition">
                    ${clkLcr}
                    <div class="text-[10px] text-slate-500">NOA: <span class="font-bold text-slate-600">${fmt(r.lancar_noa)}</span></div>
                </td>
                
                <td class="px-3 py-2 border-r border-slate-100 text-right bg-red-50/30 hover:bg-red-100 transition">
                    ${clkTgh}
                    <div class="text-[10px] text-slate-500">NOA: <span class="font-bold text-slate-600">${fmt(r.macet_noa)}</span></div>
                </td>
                
                <td class="px-3 py-2 border-r border-slate-100 text-right bg-slate-50 hover:bg-slate-100 transition">
                    ${clkLns}
                    <div class="text-[10px] text-slate-500">NOA: <span class="font-bold text-slate-600">${fmt(r.lunas_noa)}</span></div>
                </td>
                
                <td class="px-3 py-2 border-r border-slate-100 text-right font-bold text-slate-600 text-sm align-top pt-3">${fmt(r.angsuran)}</td>
                
                <td class="px-3 py-2 border-r border-slate-100 text-right font-extrabold text-purple-700 bg-purple-50/20 text-sm align-top pt-3">${fmt(r.total_bayar)}</td>
                
                <td class="px-2 py-2 font-extrabold text-center text-sm md:text-lg align-middle ${r.persen>=90?'text-green-600':'text-orange-500'}">${r.persen}%</td>
            </tr>`;
      });
      tb.innerHTML = h;
  }

  // ==========================================
  // EXPORT EXCEL (RAW NUMBER) - SAMA PERSIS
  // ==========================================
  window.exportExcelRekapRR = function() {
      if(!rekapDataRaw || rekapDataRaw.length === 0) return alert("Tidak ada data rekap untuk didownload.");

      let csv = "Tanggal\tTarget NOA\tTarget OS\tLancar NOA\tLancar OS\tDitagih NOA\tDitagih OS\tLunas NOA\tLunas OS\tAngsuran\tTotal Bayar\tPersen Recovery\n";
      
      rekapDataRaw.forEach(r => {
          csv += `${r.tgl}\t${r.target_noa}\t${Math.round(r.target_os)}\t${r.lancar_noa}\t${Math.round(r.lancar_os)}\t${r.macet_noa}\t${Math.round(r.macet_os)}\t${r.lunas_noa}\t${Math.round(r.lunas_os)}\t${Math.round(r.angsuran)}\t${Math.round(r.total_bayar)}\t${r.persen}%\n`;
      });

      if(rekapGtRaw) {
          csv += `TOTAL\t${rekapGtRaw.target_noa}\t${Math.round(rekapGtRaw.target_os)}\t${rekapGtRaw.lancar_noa}\t${Math.round(rekapGtRaw.lancar_os)}\t${rekapGtRaw.macet_noa}\t${Math.round(rekapGtRaw.macet_os)}\t${rekapGtRaw.lunas_noa}\t${Math.round(rekapGtRaw.lunas_os)}\t${Math.round(rekapGtRaw.angsuran)}\t${Math.round(rekapGtRaw.total_bayar)}\t${rekapGtRaw.persen}%\n`;
      }

      const blob = new Blob([csv], { type: 'application/vnd.ms-excel' });
      const a = document.createElement('a');
      a.href = window.URL.createObjectURL(blob);
      
      const tglAwal = document.getElementById("closing_date").value;
      const tglAkhir = document.getElementById("harian_date").value;
      a.download = `Rekap_RR_${tglAwal}_sd_${tglAkhir}.xls`; 
      a.click();
  }

  // ==========================================
  // MODAL DETAIL LOGIC (SAMA PERSIS)
  // ==========================================
  function formatWA(phone) {
      if (!phone) return null;
      let cleaned = phone.replace(/\D/g, ''); 
      if (cleaned.startsWith('0')) { cleaned = '62' + cleaned.substring(1); } 
      else if (cleaned.startsWith('8')) { cleaned = '62' + cleaned; }
      if (cleaned.length < 10) return null;
      return cleaned;
  }

  function createWABtn(phone, nama, norek, totung) {
      const formatted = formatWA(phone);
      if (!formatted) return `<span class="text-slate-400 font-mono text-xs md:text-sm">${phone || '-'}</span>`;
      
      const msg = `Yth. Bapak/Ibu *${nama}*,\n\nKami menginformasikan bahwa terdapat tagihan angsuran kredit pada rekening *${norek}* dengan total tunggakan sebesar *Rp ${fmt(totung)}*.\n\nMohon untuk segera melakukan pembayaran angsuran.\n\n_(Jika Bapak/Ibu sudah melakukan pembayaran, mohon abaikan pesan ini)_\n\nTerima kasih.`;
      const waUrl = `https://wa.me/${formatted}?text=${encodeURIComponent(msg)}`;
      
      return `
          <a href="${waUrl}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-500 hover:text-white text-emerald-600 rounded-lg border border-emerald-200 transition font-bold text-xs" title="Kirim Pesan WhatsApp">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.319-.883-.665-1.479-1.488-1.653-1.787-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
              WA (${formatted.substring(0,5)}...)
          </a>
      `;
  }

  async function initModalDetail(tgl, status) {
      currentMode = 'NORMAL';
      const branch = document.getElementById('opt_kantor').value || null;
      
      await loadKankasModalDropdown();
      const kankas = document.getElementById('opt_kankas_modal').value || null; 
      
      currentDetailParams = { 
          type: 'detail_rr', 
          closing_date: document.getElementById('closing_date').value, 
          harian_date: document.getElementById('harian_date').value, 
          kode_kantor: branch, 
          kode_kankas: kankas,
          tgl_tagih: tgl, 
          status: status, 
          limit: detailLimit 
      };

      document.getElementById('headModalRR').innerHTML = `
          <tr>
            <th class="px-3 py-4 border-b border-r border-slate-200 w-[130px] sticky left-0 bg-slate-100 z-30 shadow-[1px_0_0_#cbd5e1] text-sm text-slate-700">Rekening</th>
            <th class="px-4 py-4 border-b border-r border-slate-200 w-[250px] sticky left-[130px] bg-slate-100 z-30 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.1)] text-sm text-slate-700">Nama Nasabah</th>
            <th class="px-4 py-4 border-b border-r border-slate-200 w-[280px] text-sm text-slate-700">Alamat</th>
            <th class="px-3 py-4 border-b border-r border-slate-200 w-[130px] text-center text-sm text-slate-700">No HP (WA)</th>
            <th class="px-3 py-4 border-b border-r border-slate-200 w-[120px] text-center text-sm text-slate-700">Kankas</th>
            <th class="px-4 py-4 border-b border-r border-slate-200 w-[150px] text-center text-blue-700 text-sm">AO</th>
            <th class="px-3 py-4 border-b border-r border-slate-200 w-[100px] text-center text-sm text-slate-700">Tgl JT</th>
            <th class="px-4 py-4 border-b border-r border-slate-200 w-[130px] text-right text-sm text-slate-700">Plafond</th>
            <th class="px-4 py-4 bg-blue-50 text-blue-700 border-b border-r border-blue-200 w-[130px] text-right text-sm">Trgt (M-1)</th>
            <th class="px-4 py-4 bg-green-50 text-green-700 border-b border-r border-green-200 w-[130px] text-right text-sm">Actual</th>
            <th class="px-4 py-4 bg-red-50 text-red-700 border-b border-r border-red-200 w-[130px] text-right text-sm">Tunggakan</th>
            <th class="px-3 py-4 border-b border-r border-slate-200 w-[70px] text-center text-sm text-slate-700">DPD</th>
            <th class="px-4 py-4 border-b border-r border-slate-200 w-[140px] text-right text-sm text-slate-700">Tabungan</th>
            <th class="px-3 py-4 border-b border-r border-slate-200 w-[100px] text-center text-sm text-slate-700">Stat Tab</th>
            <th class="px-4 py-4 border-b border-slate-200 w-[110px] text-center text-sm text-slate-700">Status</th>
          </tr>`;

      document.getElementById('modalTitleRR').textContent = `Detail Penagihan (Tgl ${tgl})`;
      document.getElementById('modalSubTitleRR').textContent = `Status: ${status}`;
      document.getElementById('modalDetailRR').classList.remove('hidden');
      loadDetailPage(1);
  }

  async function initModalLunas(tgl) {
      currentMode = 'LUNAS';
      const branch = document.getElementById('opt_kantor').value || null;

      await loadKankasModalDropdown();
      const kankas = document.getElementById('opt_kankas_modal').value || null;

      currentDetailParams = { 
          type: 'detail_lunas_rr', 
          closing_date: document.getElementById('closing_date').value, 
          harian_date: document.getElementById('harian_date').value, 
          kode_kantor: branch, 
          kode_kankas: kankas,
          tgl_tagih: tgl, 
          limit: detailLimit 
      };

      document.getElementById('headModalRR').innerHTML = `
          <tr>
            <th class="px-4 py-4 border-b border-r border-slate-200 w-[250px] sticky left-0 bg-slate-100 z-30 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.1)] text-sm text-slate-700">Nama Nasabah</th>
            <th class="px-4 py-4 border-b border-r border-slate-200 w-[280px] text-sm text-slate-700">Alamat</th>
            <th class="px-4 py-4 border-b border-r border-slate-200 w-[150px] text-center text-blue-700 text-sm">AO</th>
            <th class="px-3 py-4 border-b border-r border-slate-200 w-[130px] text-center text-sm text-slate-700">Rek Lama</th>
            <th class="px-4 py-4 border-b border-r border-slate-200 w-[130px] text-right text-sm text-slate-700">Plafond Lama</th>
            <th class="px-4 py-4 bg-blue-50 text-blue-700 border-b border-r border-blue-200 w-[130px] text-right text-sm">OS M-1</th>
            <th class="px-4 py-4 border-b border-r border-slate-200 w-[130px] text-center text-sm text-slate-700">Status</th>
            <th class="px-3 py-4 bg-green-50 text-green-700 border-b border-r border-green-200 w-[130px] text-center text-sm">Rek Baru</th>
            <th class="px-4 py-4 bg-green-50 text-green-700 border-b border-r border-green-200 w-[130px] text-right text-sm">Plafond Baru</th>
            <th class="px-3 py-4 bg-green-50 text-green-700 border-b border-green-200 w-[120px] text-center text-sm">Tgl Realisasi</th>
          </tr>`;

      document.getElementById('modalTitleRR').textContent = `Detail Pelunasan (Tgl ${tgl})`;
      document.getElementById('modalSubTitleRR').textContent = `Cek Refinancing vs Prospek`;
      document.getElementById('modalDetailRR').classList.remove('hidden');
      loadDetailPage(1);
  }

  async function loadDetailPage(page) {
      const l = document.getElementById('loadingModalRR'); const tb = document.getElementById('bodyModalRR'); const info = document.getElementById('pageInfoRR');
      l.classList.remove('hidden'); tb.innerHTML = '';

      try {
          const kankasModal = document.getElementById('opt_kankas_modal').value;
          currentDetailParams.kode_kankas = kankasModal;

          const payload = { ...currentDetailParams, page: page };
          const res = await apiCall(API_RR_URL, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          
          if(!res.ok || res.json.status !== 200) throw new Error(res.json.message || "Gagal memuat detail");
          
          const list = res.json.data?.data || [];
          const meta = res.json.data?.pagination || { total_records:0, total_pages:1 };

          currentDetailPage = page; currentDetailTotalPages = meta.total_pages;

          if(list.length === 0) {
              tb.innerHTML = `<tr><td colspan="15" class="py-20 text-center text-slate-500 italic text-base">Tidak ada data detail.</td></tr>`;
              info.innerText = `0 Data`;
          } else {
              let h = '';
              list.forEach(r => {
                  const aoName = (r.nama_ao || '-').split(' ').slice(0, 2).join(' ');

                  if(currentMode === 'NORMAL') {
                      let badge = `<span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">${r.status_ket}</span>`;
                      if(r.status_ket==='LANCAR') badge = `<span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-green-100 text-green-700 border border-green-200">LANCAR</span>`;
                      else if(r.status_ket==='MENUNGGAK') badge = `<span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-red-100 text-red-700 border border-red-200">MENUNGGAK</span>`;
                      
                      let statTabungan = `<span class="text-red-500 font-bold text-xs">Belum Aman</span>`;
                      if(r.status_tabungan === 'Aman') statTabungan = `<span class="text-green-600 font-bold text-xs">Aman</span>`;

                      const btnWa = createWABtn(r.no_hp, r.nama_nasabah, r.no_rekening, r.totung);

                      h += `<tr class="transition border-b border-slate-100 h-[48px]">
                            <td class="px-3 py-2 border-r border-slate-100 font-mono text-sm text-slate-600 sticky left-0 bg-white z-20 shadow-[1px_0_0_#f1f5f9]">${r.no_rekening}</td>
                            <td class="px-4 py-2 border-r border-slate-100 font-bold text-slate-700 truncate sticky left-[130px] bg-white z-20 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.1)] text-sm" title="${r.nama_nasabah}">${r.nama_nasabah}</td>
                            <td class="px-4 py-2 border-r border-slate-100 text-slate-500 truncate text-sm" title="${r.alamat||''}">${r.alamat||'-'}</td>
                            <td class="px-3 py-2 border-r border-slate-100 text-center">${btnWa}</td>
                            <td class="px-3 py-2 border-r border-slate-100 text-center font-mono text-slate-500 text-xs md:text-sm">${r.kankas||'-'}</td>
                            <td class="px-4 py-2 border-r border-slate-100 text-center font-bold text-sm text-blue-700 truncate">${aoName}</td>
                            <td class="px-3 py-2 border-r border-slate-100 text-center font-mono text-sm text-slate-500">${r.tgl_jatuh_tempo||'-'}</td>
                            <td class="px-4 py-2 border-r border-slate-100 text-right font-medium text-slate-600 text-sm">${fmt(r.jml_pinjaman)}</td>
                            <td class="px-4 py-2 border-r border-blue-100 text-right font-bold text-blue-700 bg-blue-50/30 text-sm">${fmt(r.os_m1)}</td>
                            <td class="px-4 py-2 border-r border-green-100 text-right font-bold text-green-700 bg-green-50/30 text-sm">${fmt(r.os_curr)}</td>
                            <td class="px-4 py-2 border-r border-red-100 text-right font-bold text-red-600 bg-red-50/30 text-sm">${fmt(r.totung)}</td>
                            <td class="px-3 py-2 border-r border-slate-100 text-center font-bold text-slate-700 text-sm">${r.dpd_curr}</td>
                            <td class="px-4 py-2 border-r border-slate-100 text-right font-bold text-emerald-600 bg-emerald-50/10 text-sm">${fmt(r.tabungan)}</td>
                            <td class="px-3 py-2 border-r border-slate-100 text-center">${statTabungan}</td>
                            <td class="px-4 py-2 text-center">${badge}</td>
                        </tr>`;
                  } else {
                      let badge = `<span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-blue-100 text-blue-700 border border-blue-200">PROSPEK</span>`;
                      if(r.status_lunas === 'REFINANCING / Top Up') badge = `<span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold bg-green-100 text-green-700 border border-green-200">REFINANCING</span>`;

                      h += `<tr class="transition border-b border-slate-100 h-[48px]">
                            <td class="px-4 py-2 border-r border-slate-100 font-bold text-slate-700 truncate sticky left-0 bg-white z-20 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.1)] text-sm">
                                ${r.nama_nasabah}
                                <div class="text-xs text-slate-400 font-mono mt-0.5 font-normal">ID: ${r.nasabah_id}</div>
                            </td>
                            <td class="px-4 py-2 border-r border-slate-100 text-slate-500 truncate text-sm" title="${r.alamat||''}">${r.alamat||'-'}</td>
                            <td class="px-4 py-2 border-r border-slate-100 text-center font-bold text-sm text-blue-700 truncate">${aoName}</td>
                            <td class="px-3 py-2 border-r border-slate-100 font-mono text-sm text-center text-slate-600">${r.no_rekening}</td>
                            <td class="px-4 py-2 border-r border-slate-100 text-right font-medium text-slate-600 bg-slate-50/50 text-sm">${fmt(r.plafon_lama)}</td>
                            <td class="px-4 py-2 border-r border-blue-100 text-right font-bold text-blue-700 bg-blue-50/30 text-sm">${fmt(r.os_lunas)}</td>
                            <td class="px-4 py-2 border-r border-slate-100 text-center">${badge}</td>
                            <td class="px-3 py-2 border-r border-green-100 font-mono text-sm text-center bg-green-50/30 text-green-800 font-bold">${r.rek_baru}</td>
                            <td class="px-4 py-2 border-r border-green-100 text-right bg-green-50/30 text-green-700 font-bold text-sm">${fmt(r.plafond_baru)}</td>
                            <td class="px-3 py-2 text-center bg-green-50/30 text-sm font-medium text-green-700">${r.tgl_baru}</td>
                        </tr>`;
                  }
              });
              tb.innerHTML = h;
              const start = ((page - 1) * detailLimit) + 1;
              const end = Math.min(page * detailLimit, meta.total_records);
              info.innerText = `Hal ${page} dari ${meta.total_pages} (${fmt(meta.total_records)} Data)`;
          }
          document.getElementById('btnPrevRR').disabled = page <= 1;
          document.getElementById('btnNextRR').disabled = page >= meta.total_pages;
      } catch(err){ 
          console.error(err); 
          tb.innerHTML = `<tr><td colspan="15" class="py-16 text-center text-red-500 font-bold tracking-widest uppercase text-sm">Gagal memuat detail</td></tr>`;
      } finally { l.classList.add('hidden'); }
  }

  async function downloadExcelFull() {
      const btn = event.target.closest('button'); const txt = btn.innerHTML;
      btn.innerHTML = `<span class="animate-spin inline-block h-5 w-5 border-2 border-white border-t-transparent rounded-full mr-2"></span>...`;
      btn.disabled = true;

      try {
          const kankasModal = document.getElementById('opt_kankas_modal').value;
          const payload = { ...currentDetailParams, kode_kankas: kankasModal, page: 1, limit: 10000 };
          const res = await apiCall(API_RR_URL, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          
          if(!res.ok || res.json.status !== 200) throw new Error(res.json.message || "Export gagal");
          
          const rows = res.json.data?.data || [];
          if(rows.length === 0) { alert("Tidak ada data untuk diexport"); return; }

          let csv = "";
          if(currentMode === 'NORMAL') {
              csv = `No Rekening\tNama Nasabah\tAlamat\tNo HP\tKankas\tNama AO\tTgl JT\tPlafond\tTarget (M-1)\tActual (Curr)\tTot Tunggakan\tDPD\tSaldo Tabungan\tStatus Tabungan\tStatus Tagih\n`;
              rows.forEach(r => {
                  csv += `'${r.no_rekening}\t${r.nama_nasabah}\t${r.alamat||''}\t'${r.no_hp||''}\t${r.kankas||''}\t${r.nama_ao}\t${r.tgl_jatuh_tempo}\t${Math.round(r.jml_pinjaman)}\t${Math.round(r.os_m1)}\t${Math.round(r.os_curr)}\t${Math.round(r.totung)}\t${r.dpd_curr}\t${Math.round(r.tabungan)}\t${r.status_tabungan}\t${r.status_ket}\n`;
              });
          } else {
              csv = `Nama Nasabah\tID Nasabah\tAlamat\tNama AO\tRek Lama\tPlafond Lama\tOS Lunas (M-1)\tStatus\tRek Baru\tPlafond Baru\tTgl Realisasi Baru\n`;
              rows.forEach(r => {
                  csv += `${r.nama_nasabah}\t'${r.nasabah_id}\t${r.alamat||''}\t${r.nama_ao}\t'${r.no_rekening}\t${Math.round(r.plafon_lama)}\t${Math.round(r.os_lunas)}\t${r.status_lunas}\t'${r.rek_baru}\t${Math.round(r.plafond_baru)}\t${r.tgl_baru}\n`;
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