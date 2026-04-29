<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
  /* Custom Scrollbar */
  .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
  .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
  .no-scrollbar::-webkit-scrollbar { display: none; }
  .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

  /* Animasi */
  @keyframes scaleUp { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
  .animate-scale-up { animation: scaleUp 0.2s ease-out forwards; }

  /* ========================================================
     🔥 CSS MAGIC STICKY TABLE REKAP (MATCHING SCREENSHOT)
     ======================================================== */
  #tabelRekap { border-collapse: separate; border-spacing: 0; }
  #tabelRekap thead th { position: sticky; box-shadow: inset 0 -1px 0 #cbd5e1; top: 0; z-index: 40; background-color: #f8fafc; }
  
  /* Baris Total Keseluruhan (Biru Muda Solid) */
  #rowTotalAtas th { 
      top: 42px; z-index: 38; 
      background-color: #dbeafe !important; 
      border-bottom: 2px solid #bfdbfe;
      box-shadow: inset 0 -1px 0 #93c5fd;
      padding-top: 10px; padding-bottom: 10px;
  }
  @media (min-width: 768px) { #rowTotalAtas th { top: 46px; } }

  /* Freeze Kolom Kiri Rekap */
  .sticky-left-1 { position: sticky; left: 0; z-index: 20; box-shadow: inset -1px 0 0 #e2e8f0; }
  .sticky-left-2 { position: sticky; left: 0; z-index: 20; box-shadow: inset -1px 0 0 #e2e8f0; }
  @media (min-width: 640px) { .sticky-left-2 { left: 70px; } } 

  #tabelRekap thead tr:nth-child(1) th.sticky-left-1 { z-index: 50; box-shadow: inset -1px -1px 0 #cbd5e1; background-color: #f8fafc; }
  #tabelRekap thead tr:nth-child(1) th.sticky-left-2 { z-index: 49; box-shadow: inset -1px -1px 0 #cbd5e1; background-color: #f8fafc; }
  #rowTotalAtas th.sticky-left-1 { z-index: 48; background-color: #dbeafe !important; box-shadow: inset -1px -2px 0 #93c5fd; }
  #rowTotalAtas th.sticky-left-2 { z-index: 47; background-color: #dbeafe !important; box-shadow: inset -1px -2px 0 #93c5fd; }

  #bodyRekap tr:hover td { filter: brightness(0.96); cursor: pointer; }

  /* ========================================================
     🔥 CSS MAGIC STICKY MODAL DETAIL
     ======================================================== */
  #tableExportModal { border-collapse: separate; border-spacing: 0; }
  #tableExportModal thead th { position: sticky; box-shadow: inset 0 -1px 0 #cbd5e1; top: 0; z-index: 40; background-color: #f1f5f9; }

  /* Freeze Kiri Modal */
  .mod-sticky-1 { position: sticky; left: 0; z-index: 20; background: white; box-shadow: inset -1px 0 0 #e2e8f0; min-width: 90px; max-width: 90px;}
  .mod-sticky-2 { position: sticky; left: 90px; z-index: 20; background: white; box-shadow: inset -1px 0 0 #e2e8f0; min-width: 180px; max-width: 200px;}

  @media (min-width: 768px) {
      .mod-sticky-1 { min-width: 100px; max-width: 100px; }
      .mod-sticky-2 { left: 100px; min-width: 220px; max-width: 250px; }
  }

  #tableExportModal thead th.mod-sticky-1 { z-index: 50; background-color: #e2e8f0; box-shadow: inset -1px -1px 0 #cbd5e1; }
  #tableExportModal thead th.mod-sticky-2 { z-index: 49; background-color: #e2e8f0; box-shadow: inset -1px -1px 0 #cbd5e1; }

  #bodyDetail tr:hover td { background-color: #f8fafc !important; }
  #bodyDetail tr:hover td.mod-sticky-1, #bodyDetail tr:hover td.mod-sticky-2 { filter: brightness(0.98); }

  /* Form Inp */
  .inp { border:1px solid #cbd5e1; border-radius:8px; padding:0 12px; background:#fff; outline:none; transition: border 0.2s;}
  .inp:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,0.1); }
  .lbl { font-size:10px; color:#475569; font-weight:800; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.05em; display:block; white-space: nowrap;}
  .field { display:flex; flex-direction:column; }
  .btn-icon { display:inline-flex; align-items:center; justify-content:center; border:none; cursor:pointer; transition: transform 0.2s;}
  .btn-icon:hover { transform:translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
  input[type="date"]::-webkit-inner-spin-button, input[type="date"]::-webkit-calendar-picker-indicator { display: none; -webkit-appearance: none; }
  input[type="date"] { -moz-appearance: textfield; }
</style>

<div class="max-w-[1920px] mx-auto px-2 md:px-4 py-3 md:py-5 h-[calc(100vh-60px)] md:h-[calc(100vh-80px)] flex flex-col bg-slate-50 font-sans text-slate-800 overflow-hidden">
  
  <div class="flex-none mb-3 md:mb-4 flex flex-col xl:flex-row justify-between xl:items-end gap-3 w-full shrink-0">
      <div class="flex justify-between items-center w-full xl:w-auto">
          <div class="flex flex-col gap-1">
              <h1 class="text-lg md:text-2xl font-bold text-slate-800 flex items-center gap-2">
                  <span class="p-1.5 md:p-2 bg-blue-600 rounded-lg text-white shadow-sm">
                      <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                  </span>
                  Rekapitulasi Prospek
              </h1>
              <p class="text-[10px] md:text-xs text-slate-500 font-medium ml-8 md:ml-10">Monitoring Status Follow Up, Closing & Rejected</p>
          </div>
          
          <button type="button" onclick="toggleFilter('formFilter')" class="xl:hidden h-[32px] px-3 bg-white border border-slate-200 text-slate-700 rounded-lg flex items-center gap-1.5 shadow-sm transition font-bold text-[10px] md:text-xs whitespace-nowrap">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
              Filter
          </button>
      </div>

      <form id="formFilter" class="hidden xl:flex bg-white p-2.5 md:p-3 rounded-xl border border-slate-200 shadow-sm flex-wrap md:flex-nowrap items-end gap-2 md:gap-3 w-full xl:w-auto shrink-0 xl:ml-auto transition-all duration-300" onsubmit="event.preventDefault(); fetchRekap();">
          
          <div class="field flex-1 md:flex-none min-w-[120px] max-w-[160px]">
              <label class="lbl text-slate-600">MULAI PROSPEK</label>
              <input type="date" id="closing_date" class="inp text-[11px] md:text-sm font-semibold h-[34px] md:h-[38px] cursor-pointer w-full text-slate-700" required onclick="try{this.showPicker()}catch(e){}">
          </div>
          
          <div class="field flex-1 md:flex-none min-w-[120px] max-w-[160px]">
              <label class="lbl text-slate-600">SAMPAI DENGAN</label>
              <input type="date" id="harian_date" class="inp text-[11px] md:text-sm font-semibold h-[34px] md:h-[38px] cursor-pointer w-full text-slate-700" required onclick="try{this.showPicker()}catch(e){}">
          </div>

          <div class="field flex-1 min-w-[120px]">
              <label class="lbl text-slate-600">JENIS PRODUK</label>
              <select id="opt_produk" class="inp border-slate-200 bg-slate-50 text-[11px] md:text-sm font-bold h-[34px] md:h-[38px] text-slate-700 cursor-pointer w-full" onchange="fetchRekap()">
                  <option value="">SEMUA PRODUK</option>
                  <option value="tabungan">Tabungan</option>
                  <option value="deposito">Deposito</option>
                  <option value="kredit">Kredit</option>
                  <option value="aset">Aset</option>
              </select>
          </div>
          
          <div class="field flex-1 min-w-[160px] md:min-w-[200px]">
              <label class="lbl text-slate-600">AREA / CABANG</label>
              <select id="opt_kantor" class="inp border-slate-200 bg-slate-50 text-[11px] md:text-sm font-bold h-[34px] md:h-[38px] text-slate-700 cursor-pointer w-full" onchange="fetchRekap()">
                  <option value="">Loading...</option>
              </select>
          </div>
          
          <div class="flex items-center gap-1.5 shrink-0 h-[34px] md:h-[38px] w-full md:w-auto mt-1 md:mt-0">
              <button type="submit" class="btn-icon bg-blue-600 hover:bg-blue-700 text-white rounded-lg flex-1 md:flex-none md:w-[44px] h-full shadow-sm" title="Cari Data">
                  <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" class="md:w-5 md:h-5"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                  <span class="ml-1.5 text-xs font-bold uppercase tracking-wider md:hidden">CARI</span>
              </button>
              <button type="button" onclick="exportExcelRekap()" class="btn-icon bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg flex-1 md:flex-none md:w-[44px] h-full shadow-sm" title="Download Excel">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="md:w-5 md:h-5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                  <span class="ml-1.5 text-xs font-bold uppercase tracking-wider md:hidden">EXCEL</span>
              </button>
          </div>
      </form>
  </div>

  <div class="flex-1 overflow-hidden bg-white rounded-xl shadow-sm border border-slate-200 relative">
    <div id="loadingRekap" class="hidden absolute inset-0 bg-white/80 z-[100] flex flex-col items-center justify-center text-blue-600 backdrop-blur-sm">
        <div class="animate-spin rounded-full h-8 w-8 md:h-10 md:w-10 border-4 border-blue-500 border-t-transparent mb-2 md:mb-3"></div>
        <span class="text-[10px] md:text-sm font-bold uppercase tracking-widest">Menarik Data Prospek...</span>
    </div>
    
    <div class="h-full overflow-auto custom-scrollbar relative">
      <table class="w-max min-w-full text-center border-separate border-spacing-0 text-slate-700 table-fixed" id="tabelRekap">
        <thead class="tracking-wider bg-[#f8fafc] text-slate-800 font-bold text-[10px] md:text-sm">
          <tr>
            <th class="sticky-left-1 w-[70px] border-r border-b border-slate-200 hidden sm:table-cell py-2 md:py-3 text-slate-700">KODE</th>
            <th class="sticky-left-2 w-[180px] md:w-[250px] border-r border-b border-slate-200 text-left pl-3 md:pl-4 py-2 md:py-3 text-slate-700">AREA / PRODUK</th>
            <th class="w-[120px] md:w-[140px] border-r border-b border-slate-200 text-slate-800 py-2 md:py-3">TOTAL PROSPEK</th>
            <th class="w-[100px] md:w-[130px] border-r border-b border-blue-200 text-blue-700 py-2 md:py-3">OPEN</th>
            <th class="w-[100px] md:w-[130px] border-r border-b border-purple-200 text-purple-700 py-2 md:py-3">FOLLOW UP</th>
            <th class="w-[100px] md:w-[130px] border-r border-b border-emerald-200 text-emerald-700 py-2 md:py-3">CLOSING</th>
            <th class="w-[100px] md:w-[130px] border-b border-rose-200 text-rose-700 py-2 md:py-3">REJECTED</th>
          </tr>
          <tr id="rowTotalAtas"></tr>
        </thead>
        <tbody id="bodyRekap" class="divide-y divide-slate-100 bg-white text-[11px] md:text-sm font-medium"></tbody>
      </table>
    </div>
  </div>

</div>

<div id="modalDetail" class="fixed inset-0 z-[9999] hidden items-end md:items-center justify-center p-0 md:p-4">
  <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
  
  <div class="relative bg-white w-full h-[95vh] md:h-[92vh] max-w-[1700px] rounded-t-xl md:rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-scale-up">
    
    <div class="flex flex-col md:flex-row justify-between md:items-center px-3 py-3 md:px-5 border-b bg-slate-50 shrink-0 gap-3">
        <div class="flex-1 shrink-0">
            <h3 class="font-bold text-slate-800 flex items-center gap-1.5 md:gap-2 text-[13px] md:text-lg">
                <span class="w-1.5 h-4 md:h-5 bg-blue-600 rounded-full hidden md:block"></span> 
                Detail Prospek
            </h3>
            <p class="text-[9px] md:text-[11px] text-slate-500 mt-0.5 md:ml-3 font-mono font-medium uppercase tracking-wide" id="detailSubTitle">...</p>
        </div>
        
        <div class="flex items-center gap-1.5 md:gap-2 overflow-x-auto no-scrollbar pb-1 md:pb-0 shrink-0 w-full md:w-auto">
            
            <div class="relative w-[130px] md:w-[160px] shrink-0">
                <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" id="search_detail" onkeyup="filterTableDetail()" class="w-full pl-7 pr-2 h-[30px] md:h-[34px] bg-white border border-slate-200 rounded-lg text-[10px] md:text-xs outline-none focus:border-blue-500 transition-all font-medium" placeholder="Cari...">
            </div>

            <select id="filter_produk_modal" class="inp px-2 h-[30px] md:h-[34px] w-[100px] md:w-[120px] text-[10px] md:text-xs font-bold text-slate-700 bg-white outline-none shrink-0" onchange="changeFilterDetail()">
                <option value="">Semua Produk</option>
                <option value="tabungan">Tabungan</option>
                <option value="deposito">Deposito</option>
                <option value="kredit">Kredit</option>
                <option value="aset">Aset</option>
            </select>

            <select id="filter_status_modal" class="inp px-2 h-[30px] md:h-[34px] w-[110px] md:w-[130px] text-[10px] md:text-xs font-bold text-slate-700 bg-white outline-none shrink-0" onchange="changeFilterDetail()">
                <option value="">Semua Status</option>
                <option value="open">Open</option>
                <option value="follow up">Follow Up</option>
                <option value="closing">Closing</option>
                <option value="rejected">Rejected</option>
            </select>

            <button onclick="downloadExcelDetail()" class="btn-icon bg-emerald-600 hover:bg-emerald-700 text-white w-[30px] md:w-[34px] h-[30px] md:h-[34px] rounded-lg shadow-sm shrink-0" title="Export Excel">
                <svg class="w-4 h-4 md:w-4 md:h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            </button>
            <button onclick="closeModal()" class="w-[30px] h-[30px] md:w-[34px] md:h-[34px] flex items-center justify-center rounded-lg bg-rose-100 hover:bg-rose-500 hover:text-white text-rose-600 transition font-bold text-xl leading-none shrink-0">&times;</button>
        </div>
    </div>

    <div class="flex-1 overflow-auto bg-slate-50 relative custom-scrollbar p-0 md:p-3">
        <div id="loadingDetail" class="hidden absolute inset-0 bg-white/90 z-40 flex flex-col items-center justify-center text-blue-600 backdrop-blur-sm">
            <div class="animate-spin rounded-full h-8 w-8 border-4 border-blue-500 border-t-transparent mb-2 md:mb-3"></div>
            <span class="text-[10px] md:text-xs font-bold uppercase tracking-widest">Memuat Detail...</span>
        </div>
        
        <table class="w-max min-w-full text-sm text-left text-slate-700 border-separate border-spacing-0 md:border border-slate-200 md:rounded-xl shadow-sm bg-white table-fixed" id="tableExportModal">
            <thead class="text-slate-600 font-bold uppercase tracking-wider text-[9px] md:text-[11px]">
                <tr>
                    <th class="px-2 md:px-3 py-2 md:py-3 border-b border-r border-slate-300 mod-sticky-1 text-center bg-[#f1f5f9] md:rounded-tl-xl">TANGGAL</th>
                    <th class="px-2 md:px-4 py-2 md:py-3 border-b border-r border-slate-300 mod-sticky-2 text-blue-900 bg-[#f1f5f9]">NAMA NASABAH</th>
                    <th class="px-2 md:px-3 py-2 md:py-3 border-b border-r border-slate-300 w-[120px] md:w-[150px] text-center text-blue-800">NO REKENING</th>
                    <th class="px-2 md:px-3 py-2 md:py-3 border-b border-r border-slate-300 w-[100px] md:w-[120px] text-center">NO HP</th>
                    <th class="px-2 md:px-4 py-2 md:py-3 border-b border-r border-slate-300 w-[160px] md:w-[220px]">ALAMAT</th>
                    <th class="px-2 md:px-3 py-2 md:py-3 border-b border-r border-slate-300 w-[120px] md:w-[150px] text-center">JENIS USAHA</th>
                    <th class="px-2 md:px-3 py-2 md:py-3 border-b border-r border-slate-300 w-[90px] md:w-[120px] text-center">PRODUK</th>
                    <th class="px-2 md:px-3 py-2 md:py-3 border-b border-r border-slate-300 w-[100px] md:w-[120px] text-center">STATUS</th>
                    <th class="px-2 md:px-3 py-2 md:py-3 border-b border-r border-slate-300 w-[120px] md:w-[160px] text-blue-800">NAMA AO</th>
                    <th class="px-2 md:px-3 py-2 md:py-3 border-b border-r border-slate-300 w-[120px] md:w-[160px] text-purple-800">REFERRAL</th>
                    <th class="px-2 md:px-4 py-2 md:py-3 border-b border-slate-300 w-[200px] md:w-[300px]">CATATAN</th>
                </tr>
            </thead>
            <tbody id="bodyDetail" class="divide-y divide-slate-100 bg-white text-[10px] md:text-xs"></tbody>
        </table>
    </div>

    <div class="px-3 py-2.5 md:px-5 md:py-3 border-t bg-white flex justify-between items-center shrink-0">
        <span id="pageInfo" class="text-[10px] md:text-xs font-bold text-slate-600 bg-slate-100 px-2 md:px-3 py-1 rounded-lg">0 Data</span>
        <div class="flex gap-1.5 md:gap-2">
            <button id="btnPrev" onclick="changePage(-1)" class="px-3 py-1.5 md:px-4 md:py-2 bg-white border border-slate-300 rounded-lg text-[10px] md:text-sm font-bold text-slate-600 hover:bg-slate-50 hover:border-slate-400 disabled:opacity-50 transition shadow-sm">« Prev</button>
            <button id="btnNext" onclick="changePage(1)" class="px-3 py-1.5 md:px-4 md:py-2 bg-white border border-slate-300 rounded-lg text-[10px] md:text-sm font-bold text-slate-600 hover:bg-slate-50 hover:border-slate-400 disabled:opacity-50 transition shadow-sm">Next »</button>
        </div>
    </div>
  </div>
</div>

<script>
  // --- KONFIGURASI API ---
  const API_URL  = './api/prospek/'; 
  const API_KODE = './api/kode/';
  const API_DATE = './api/date/';

  const nf = new Intl.NumberFormat('id-ID');
  const fmt = n => nf.format(Math.round(Number(n||0)));

  let abortRekap;
  let rekapCache = null; 
  let state = { kode_kantor: '000', jenis_produk: '', status: '', page: 1, limit: 50, totalPages: 1 };
  let uKodeGlobal = '000';

  // --- TOGGLE FILTER MOBILE ---
  function toggleFilter(id) {
      const el = document.getElementById(id);
      if(el.classList.contains('hidden')) {
          el.classList.remove('hidden'); el.classList.add('flex');
      } else {
          el.classList.add('hidden'); el.classList.remove('flex');
      }
  }

  // --- INIT & LOGIC PEMBATASAN USER ---
  window.addEventListener('DOMContentLoaded', async () => {
      const user = (window.getUser && window.getUser()) || null;
      let uKode = user?.kode ? String(user.kode).padStart(3,'0') : '000';
      if(uKode === '099') uKode = '000'; 
      uKodeGlobal = uKode;

      const isPusat = (uKode === '000');
      const optKantor = document.getElementById('opt_kantor');

      if (isPusat) {
          await loadCabangDropdown();
          optKantor.value = "000"; 
      } else {
          optKantor.innerHTML = `<option value="${uKode}">CABANG ${uKode}</option>`;
          optKantor.value = uKode;
          optKantor.disabled = true; 
          optKantor.classList.add('bg-slate-100');
      }

      // Default Date Tgl 1 Bulan Lalu s/d Hari ini
      const now = new Date();
      const firstDay = new Date(now.getFullYear(), now.getMonth() - 1, 1);
      document.getElementById('closing_date').value = firstDay.toISOString().split('T')[0];
      
      try { 
          const r = await fetch(API_DATE); 
          const j = await r.json();
          if (j.data && j.data.last_created) {
              document.getElementById('harian_date').value = j.data.last_created;
          } else {
              document.getElementById('harian_date').value = now.toISOString().split('T')[0];
          }
      } catch {
          document.getElementById('harian_date').value = now.toISOString().split('T')[0];
      }

      fetchRekap();
  });

  async function apiCall(url, payload, signal = null) {
      const opt = { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) };
      if (signal) opt.signal = signal;
      const res = await fetch(url, opt);
      return await res.json();
  }

  // --- LOAD DROPDOWN GABUNGAN CABANG ---
  async function loadCabangDropdown() {
      const el = document.getElementById('opt_kantor');
      try {
          const r = await apiCall(API_KODE, { type: 'kode_kantor' });
          let h = `<option value="000" class="font-bold text-blue-700">KONSOLIDASI (SEMUA)</option>
                   <optgroup label="Korwil">
                      <option value="Korwil Semarang">Korwil Semarang</option>
                      <option value="Korwil Solo">Korwil Solo</option>
                      <option value="Korwil Banyumas">Korwil Banyumas</option>
                      <option value="Korwil Pekalongan">Korwil Pekalongan</option>
                   </optgroup>
                   <optgroup label="Cabang">`;
          
          if(r.data && Array.isArray(r.data)) {
              r.data.filter(x => x.kode_kantor !== '000' && x.kode_kantor !== '099')
                    .sort((a,b) => a.kode_kantor.localeCompare(b.kode_kantor))
                    .forEach(x => {
                        h += `<option value="${x.kode_kantor}">${x.kode_kantor} - ${x.nama_kantor}</option>`;
                    });
          }
          h += `</optgroup>`;
          el.innerHTML = h;
      } catch (e) { console.error("Gagal load cabang dropdown", e); }
  }

  // --- FETCH REKAP UTAMA ---
  async function fetchRekap() {
      const l = document.getElementById('loadingRekap');
      const tb = document.getElementById('bodyRekap');
      const trTot = document.getElementById('rowTotalAtas');
      
      if(abortRekap) abortRekap.abort();
      abortRekap = new AbortController();

      l.classList.remove('hidden');
      tb.innerHTML = `<tr><td colspan="7" class="text-center py-20 text-slate-400 italic text-[11px] md:text-sm">Sedang menarik data...</td></tr>`;
      trTot.innerHTML = '';
      rekapCache = null;

      const currentKc = document.getElementById('opt_kantor').value;
      const currentProduk = document.getElementById('opt_produk').value;
      
      state.kode_kantor = currentKc;
      state.jenis_produk = currentProduk;

      try {
          const payload = {
              type: 'rekap_prospek', 
              closing_date: document.getElementById('closing_date').value,
              harian_date: document.getElementById('harian_date').value,
              kode_kantor: currentKc,
              jenis_produk: currentProduk
          };

          const json = await apiCall(API_URL, payload, abortRekap.signal);
          const matrix = json.data?.matrix || [];
          const total = json.data?.total || {};

          if(matrix.length === 0) {
              tb.innerHTML = `<tr><td colspan="7" class="text-center py-20 text-slate-400 italic text-[11px] md:text-sm">Tidak ada data.</td></tr>`;
              return;
          }
          rekapCache = { matrix, total }; 

          const isCabangMode = currentKc !== '000' && !currentKc.toLowerCase().includes('korwil');

          let html = '';
          matrix.forEach(r => {
              const arg = `'${r.kode}', ${isCabangMode}`;
              const t_rej = r.total_rejected || r.total_reject || 0;
              
              // Formatting Teks Nama Label (000 jadi Pusat, DUMMY jadi italic)
              let kodeLabel = r.kode.toUpperCase();
              let namaLabel = r.nama_label.toUpperCase();
              
              if (kodeLabel === '000') {
                  namaLabel = 'KANTOR PUSAT';
              } else if (namaLabel.includes('DUMMY')) {
                  namaLabel = `<span class="italic text-slate-400">${namaLabel}</span>`;
              }

              // 🔥 Highlight Tanda Merah Pudar Jika Grand Total = 0
              let bgClass = "bg-white hover:bg-slate-50";
              let tdLeftClass = "bg-white group-hover:bg-slate-50 text-slate-700";
              let textDataClass = "font-bold";
              
              if (r.grand_total === 0) {
                  bgClass = "bg-red-50/50 hover:bg-red-100/50 text-red-700";
                  tdLeftClass = "bg-red-50/50 group-hover:bg-red-100/50 text-red-700";
                  textDataClass = "font-bold text-red-700";
              }

              html += `
                <tr class="transition h-[40px] md:h-[48px] group border-b border-slate-100 ${bgClass}">
                    <td class="sticky-left-1 px-2 md:px-3 py-1.5 md:py-2 border-r border-slate-100 font-mono text-center hidden sm:table-cell shadow-[inset_-1px_0_0_#e2e8f0] z-20 ${tdLeftClass}">${kodeLabel}</td>
                    <td class="sticky-left-2 px-3 md:px-4 py-1.5 md:py-2 border-r border-slate-100 font-semibold truncate text-left shadow-[inset_-1px_0_0_#e2e8f0] z-20 ${tdLeftClass}" title="${r.nama_label}">${namaLabel}</td>
                    
                    <td class="px-2 md:px-4 py-1.5 md:py-2 border-r border-slate-200 ${r.grand_total === 0 ? '' : 'bg-slate-50/50 text-slate-800 hover:bg-slate-100/50'} font-extrabold cursor-pointer" onclick="handleCellClick(${arg}, '')"><span class="${textDataClass}">${fmt(r.grand_total)}</span></td>

                    <td class="px-2 md:px-4 py-1.5 md:py-2 border-r border-blue-100 ${r.grand_total === 0 ? '' : 'bg-blue-50/20 text-blue-700 hover:bg-blue-100'} cursor-pointer" onclick="handleCellClick(${arg}, 'open')"><span class="${textDataClass}">${fmt(r.total_open)}</span></td>
                    <td class="px-2 md:px-4 py-1.5 md:py-2 border-r border-purple-100 ${r.grand_total === 0 ? '' : 'bg-purple-50/20 text-purple-700 hover:bg-purple-100'} cursor-pointer" onclick="handleCellClick(${arg}, 'follow up')"><span class="${textDataClass}">${fmt(r.total_follow_up)}</span></td>
                    <td class="px-2 md:px-4 py-1.5 md:py-2 border-r border-emerald-100 ${r.grand_total === 0 ? '' : 'bg-emerald-50/20 text-emerald-700 hover:bg-emerald-100'} cursor-pointer" onclick="handleCellClick(${arg}, 'closing')"><span class="${textDataClass}">${fmt(r.total_closing)}</span></td>
                    <td class="px-2 md:px-4 py-1.5 md:py-2 border-b border-rose-100 ${r.grand_total === 0 ? '' : 'bg-rose-50/20 text-rose-700 hover:bg-rose-100'} cursor-pointer" onclick="handleCellClick(${arg}, 'rejected')"><span class="${textDataClass}">${fmt(t_rej)}</span></td>
                </tr>`;
          });
          tb.innerHTML = html;

          // Grand Total Lapis 2
          const argTot = `'-', ${isCabangMode}`;
          const tot_rej = total.total_rejected || total.total_reject || 0;
          
          trTot.innerHTML = `
              <th class="sticky-left-1 px-2 md:px-3 border-r border-blue-200 hidden sm:table-cell py-1.5 md:py-0 text-slate-800 text-center">-</th>
              <th class="sticky-left-2 px-3 md:px-4 border-r border-blue-200 text-left text-blue-900 font-extrabold tracking-widest py-1.5 md:py-0">TOTAL KESELURUHAN</th>
              
              <th class="px-2 md:px-4 border-r border-blue-200 font-extrabold text-slate-900 cursor-pointer hover:brightness-95 py-1.5 md:py-0" onclick="handleCellClick(${argTot}, '')">${fmt(total.grand_total)}</th>

              <th class="px-2 md:px-4 border-r border-blue-200 font-extrabold text-blue-700 cursor-pointer hover:brightness-95 py-1.5 md:py-0" onclick="handleCellClick(${argTot}, 'open')">${fmt(total.total_open)}</th>
              <th class="px-2 md:px-4 border-r border-blue-200 font-extrabold text-purple-700 cursor-pointer hover:brightness-95 py-1.5 md:py-0" onclick="handleCellClick(${argTot}, 'follow up')">${fmt(total.total_follow_up)}</th>
              <th class="px-2 md:px-4 border-r border-blue-200 font-extrabold text-emerald-700 cursor-pointer hover:brightness-95 py-1.5 md:py-0" onclick="handleCellClick(${argTot}, 'closing')">${fmt(total.total_closing)}</th>
              <th class="px-2 md:px-4 border-b border-blue-200 font-extrabold text-rose-700 cursor-pointer hover:brightness-95 py-1.5 md:py-0" onclick="handleCellClick(${argTot}, 'rejected')">${fmt(tot_rej)}</th>
          `;

      } catch(e) { 
          if(e.name!=='AbortError') { console.error(e); tb.innerHTML = `<tr><td colspan="7" class="text-center py-10 text-red-500">Gagal memuat data</td></tr>`; }
      } finally { l.classList.add('hidden'); }
  }

  // --- LOGIKA KLIK MATRIX KE DETAIL ---
  function handleCellClick(rowKode, isCabangMode, statusClicked) {
      const globalKantor = document.getElementById('opt_kantor').value; 
      const globalProduk = document.getElementById('opt_produk').value;

      if (rowKode === '-') {
          state.kode_kantor = globalKantor; 
          state.jenis_produk = globalProduk; 
      } 
      else if (isCabangMode) {
          state.kode_kantor = globalKantor; 
          state.jenis_produk = rowKode;     
      } 
      else {
          state.kode_kantor = rowKode;      
          state.jenis_produk = globalProduk;
      }

      state.status = statusClicked;
      state.page = 1;

      // Sync filter UI di dalam modal detail
      document.getElementById('filter_produk_modal').value = state.jenis_produk;
      document.getElementById('filter_status_modal').value = state.status;

      let prodText = state.jenis_produk ? state.jenis_produk.toUpperCase() : "SEMUA PRODUK";
      let statText = state.status ? state.status.toUpperCase() : "SEMUA STATUS";
      document.getElementById('detailSubTitle').innerText = `${prodText} • ${statText} (Kode: ${state.kode_kantor})`;

      const modal = document.getElementById('modalDetail');
      modal.classList.remove('hidden'); modal.classList.add('flex');
      
      document.getElementById('search_detail').value = '';
      fetchDetail();
  }

  function changeFilterDetail() {
      state.jenis_produk = document.getElementById('filter_produk_modal').value;
      state.status = document.getElementById('filter_status_modal').value;
      state.page = 1;

      let prodText = state.jenis_produk ? state.jenis_produk.toUpperCase() : "SEMUA PRODUK";
      let statText = state.status ? state.status.toUpperCase() : "SEMUA STATUS";
      document.getElementById('detailSubTitle').innerText = `${prodText} • ${statText} (Kode: ${state.kode_kantor})`;

      fetchDetail();
  }

  // Pencarian manual via Input Search Detail
  function filterTableDetail() {
      const input = document.getElementById("search_detail");
      const filter = input.value.toLowerCase();
      const tbody = document.getElementById("bodyDetail");
      const trs = tbody.getElementsByTagName("tr");

      for (let i = 0; i < trs.length; i++) {
          const tdName = trs[i].getElementsByTagName("td")[1];
          if (tdName) {
              const txtValue = tdName.textContent || tdName.innerText;
              if (txtValue.toLowerCase().indexOf(filter) > -1) trs[i].style.display = "";
              else trs[i].style.display = "none";
          }
      }
  }

  async function fetchDetail() {
      const l = document.getElementById('loadingDetail');
      const tb = document.getElementById('bodyDetail');
      l.classList.remove('hidden'); tb.innerHTML='';
      
      try {
          const payload = {
              type: 'detail_prospek',
              closing_date: document.getElementById('closing_date').value,
              harian_date: document.getElementById('harian_date').value,
              kode_kantor: state.kode_kantor,
              jenis_produk: state.jenis_produk,
              status: state.status,
              page: state.page, 
              limit: state.limit
          };

          const json = await apiCall(API_URL, payload);
          const rows = json.data?.data || [];
          
          if(rows.length === 0) {
              tb.innerHTML = `<tr><td colspan="11" class="text-center py-20 text-slate-400 italic text-[11px] md:text-sm">Tidak ada detail prospek.</td></tr>`;
              document.getElementById('pageInfo').innerText = '0 Data';
              return;
          }

          state.totalPages = json.data?.pagination?.total_pages || 1;
          document.getElementById('pageInfo').innerText = `Hal ${state.page} / ${state.totalPages}`;

          let html = '';
          rows.forEach(r => {
              const alamatPendek = r.alamat && r.alamat.length > 35 ? r.alamat.substring(0, 35) + '...' : (r.alamat||'-');
              const catatan = r.catatan || '-';
              const noRek = r.no_rekening || '-';
              
              let badgeClass = "bg-slate-100 text-slate-600 border-slate-300";
              const s = (r.status||'').toLowerCase();
              if(s === 'open') badgeClass = "bg-blue-50 text-blue-700 border-blue-200";
              else if(s === 'follow up') badgeClass = "bg-purple-50 text-purple-700 border-purple-200";
              else if(s === 'closing') badgeClass = "bg-emerald-50 text-emerald-700 border-emerald-200";
              else if(s === 'rejected' || s === 'reject') badgeClass = "bg-rose-50 text-rose-700 border-rose-200";

              const statLabel = s === 'reject' ? 'REJECTED' : (r.status_label || s).toUpperCase();

              html += `
                <tr class="transition h-[40px] md:h-[48px] hover:bg-slate-50 border-b border-slate-100">
                    <td class="mod-sticky-1 px-2 md:px-3 py-1.5 md:py-2 font-mono text-[9px] md:text-xs text-slate-500 bg-white border-r border-slate-100 shadow-[inset_-1px_0_0_#e2e8f0] text-center">${r.tanggal_prospek}</td>
                    <td class="mod-sticky-2 px-2 md:px-4 py-1.5 md:py-2 font-bold text-[10px] md:text-sm text-slate-700 bg-white truncate border-r border-slate-100 shadow-[inset_-1px_0_0_#e2e8f0]" title="${r.nama}">${r.nama}</td>
                    
                    <td class="px-2 md:px-3 py-1.5 md:py-2 text-center font-mono font-bold text-blue-700 border-r border-slate-100 bg-blue-50/30">${noRek}</td>
                    <td class="px-2 md:px-3 py-1.5 md:py-2 text-center font-mono text-slate-600 border-r border-slate-100">${r.no_hp || '-'}</td>
                    <td class="px-2 md:px-4 py-1.5 md:py-2 text-[10px] md:text-xs text-slate-500 whitespace-nowrap border-r border-slate-100" title="${r.alamat||'-'}">${alamatPendek}</td>
                    <td class="px-2 md:px-3 py-1.5 md:py-2 text-center text-[10px] md:text-xs text-slate-600 border-r border-slate-100">${r.jenis_usaha || '-'}</td>
                    <td class="px-2 md:px-3 py-1.5 md:py-2 text-center font-bold text-[10px] md:text-xs text-slate-700 border-r border-slate-100">${r.produk_label || '-'}</td>
                    
                    <td class="px-2 md:px-3 py-1.5 md:py-2 text-center border-r border-slate-100"><span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] md:text-[10px] font-bold uppercase border ${badgeClass}">${statLabel}</span></td>
                    <td class="px-2 md:px-3 py-1.5 md:py-2 text-[10px] md:text-xs font-bold text-blue-700 truncate border-r border-slate-100">${r.nama_ao || '-'}</td>
                    <td class="px-2 md:px-3 py-1.5 md:py-2 text-[10px] md:text-xs font-bold text-purple-700 truncate border-r border-slate-100">${r.nama_referral || '-'}</td>
                    
                    <td class="px-2 md:px-4 py-1.5 md:py-2 text-[10px] md:text-xs text-slate-500 border-b border-slate-100 break-words" style="word-break: break-word;">${catatan}</td>
                </tr>`;
          });
          tb.innerHTML = html;

          document.getElementById('btnPrev').disabled = state.page <= 1;
          document.getElementById('btnNext').disabled = state.page >= state.totalPages;

      } catch(e) { console.error(e); } finally { l.classList.add('hidden'); }
  }

  function changePage(step) {
      const next = state.page + step;
      if(next > 0 && next <= state.totalPages) { state.page = next; fetchDetail(); }
  }

  function closeModal() { 
      const modal = document.getElementById('modalDetail');
      modal.classList.add('hidden'); modal.classList.remove('flex');
  }

  // --- EXPORT EXCEL REKAP ---
  window.exportExcelRekap = function() {
      if(!rekapCache || !rekapCache.matrix) return alert("Data kosong");
      let csv = "KODE\tAREA/PRODUK\tGRAND TOTAL\tOPEN\tFOLLOW UP\tCLOSING\tREJECTED\n";
      rekapCache.matrix.forEach(r => {
          const trej = r.total_rejected || r.total_reject || 0;
          csv += `'${r.kode}\t${r.nama_label}\t${r.grand_total}\t${r.total_open}\t${r.total_follow_up}\t${r.total_closing}\t${trej}\n`;
      });
      const t = rekapCache.total;
      const ttrej = t.total_rejected || t.total_reject || 0;
      csv += `${t.kode}\t${t.nama_label}\t${t.grand_total}\t${t.total_open}\t${t.total_follow_up}\t${t.total_closing}\t${ttrej}\n`;

      const blob = new Blob([csv], { type: 'application/vnd.ms-excel' });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a'); a.href = url;
      a.download = `Rekap_Prospek.xls`; a.click();
  }

  // --- EXPORT EXCEL DETAIL ---
  window.downloadExcelDetail = async function() {
      const btn = event.target.closest('button'); const txt = btn.innerHTML;
      btn.innerHTML = `<span class="animate-spin inline-block h-3 w-3 border-2 border-white border-t-transparent rounded-full mr-1"></span>...`;
      btn.disabled = true;

      try {
          const payload = {
              type: 'detail_prospek',
              closing_date: document.getElementById('closing_date').value, harian_date: document.getElementById('harian_date').value,
              kode_kantor: state.kode_kantor, jenis_produk: state.jenis_produk, status: state.status, 
              page: 1, limit: 10000 
          };
          const json = await apiCall(API_URL, payload);
          let rows = json.data?.data || [];
          if(rows.length===0) { alert('Data kosong'); btn.innerHTML=txt; btn.disabled=false; return; }

          let csv = "TANGGAL\tNAMA NASABAH\tNO REKENING\tNO HP\tALAMAT\tJENIS USAHA\tKETERANGAN USAHA\tPRODUK\tSTATUS\tNAMA AO\tREFERRAL\tCATATAN\n";
          rows.forEach(r => {
              const cat = r.catatan ? r.catatan.replace(/\n/g, ' ') : ''; 
              const statLabel = r.status.toLowerCase() === 'reject' ? 'REJECTED' : (r.status_label || r.status).toUpperCase();
              csv += `${r.tanggal_prospek}\t${r.nama||''}\t'${r.no_rekening||''}\t'${r.no_hp||''}\t${r.alamat||''}\t${r.jenis_usaha||''}\t${r.keterangan_usaha||''}\t${r.produk_label||''}\t${statLabel}\t${r.nama_ao||''}\t${r.nama_referral||''}\t${cat}\n`;
          });

          const blob = new Blob([csv], { type: 'application/vnd.ms-excel' });
          const url = window.URL.createObjectURL(blob);
          const a = document.createElement('a'); a.href = url;
          a.download = `Detail_Prospek_${state.kode_kantor}.xls`;
          document.body.appendChild(a); a.click(); document.body.removeChild(a);
      } catch(e) { alert('Gagal export'); } finally { btn.innerHTML=txt; btn.disabled=false; }
  }

  document.addEventListener('keydown', e => { if(e.key === 'Escape') closeModal(); });
</script>