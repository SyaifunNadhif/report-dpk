<style>
  /* Custom Scrollbar */
  .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
  .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
  
  /* Sembunyikan Scrollbar Filter di Mobile */
  .no-scrollbar::-webkit-scrollbar { display: none; }
  .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

  /* Animasi Modal */
  @keyframes scaleUp { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
  .animate-scale-up { animation: scaleUp 0.2s ease-out forwards; }

  /* ========================================================
     CSS MAGIC STICKY TABLE (Responsive Mobile & Desktop)
     ======================================================== */
  #tabelJT thead th { position: sticky; box-shadow: inset 0 -1px 0 #cbd5e1; }
  
  /* DEFAULT (MOBILE VIEW) */
  #tabelJT thead tr:nth-child(1) th { top: 0; z-index: 40; height: 44px; background-color: #f1f5f9; }
  #tabelJT thead tr:nth-child(2) th { top: 44px; z-index: 38; height: 40px; background-color: #dbeafe !important; border-bottom: 2px solid #bfdbfe; box-shadow: inset 0 -1px 0 #93c5fd; }
  
  /* DESKTOP VIEW (MD) */
  @media (min-width: 768px) {
      #tabelJT thead tr:nth-child(1) th { height: 52px; }
      #tabelJT thead tr:nth-child(2) th { top: 52px; height: 46px; }
      .sticky-left-2 { left: 80px; } /* Kolom Kode muncul di PC */
  }

  /* Freeze Kolom Kiri Rekap Utama */
  .sticky-left-1 { position: sticky; left: 0; z-index: 20; background: white; box-shadow: inset -1px 0 0 #e2e8f0; }
  .sticky-left-2 { position: sticky; left: 0; z-index: 20; background: white; box-shadow: inset -1px 0 0 #e2e8f0; }

  #tabelJT thead tr:nth-child(1) th.sticky-left-1 { z-index: 50; background-color: #e2e8f0; box-shadow: inset -1px -1px 0 #cbd5e1; border-top-left-radius: 8px;}
  #tabelJT thead tr:nth-child(1) th.sticky-left-2 { z-index: 49; background-color: #e2e8f0; box-shadow: inset -1px -1px 0 #cbd5e1; }
  #tabelJT thead tr:nth-child(2) th.sticky-left-1 { z-index: 48; background-color: #bfdbfe !important; box-shadow: inset -1px -2px 0 #93c5fd; }
  #tabelJT thead tr:nth-child(2) th.sticky-left-2 { z-index: 47; background-color: #bfdbfe !important; box-shadow: inset -1px -2px 0 #93c5fd; }

  #bodyJT tr:hover td { background-color: #eff6ff !important; cursor: pointer; }
  #bodyJT tr:hover td.sticky-left-1, #bodyJT tr:hover td.sticky-left-2 { background-color: #eff6ff !important; }

  /* TABEL MODAL DETAIL */
  #tableDetailJT thead th { position: sticky; box-shadow: inset 0 -1px 0 #cbd5e1; }
  
  /* DEFAULT (MOBILE VIEW) MODAL */
  #tableDetailJT thead tr:nth-child(1) th { top: 0; z-index: 40; height: 42px; background-color: #f1f5f9; }
  #tableDetailJT thead tr:nth-child(2) th { top: 42px; z-index: 39; height: 40px; background-color: #dbeafe !important; border-bottom: 2px solid #bfdbfe; box-shadow: inset 0 -1px 0 #93c5fd; }
  .mod-sticky-2 { left: 90px; }

  /* DESKTOP VIEW (MD) MODAL */
  @media (min-width: 768px) {
      #tableDetailJT thead tr:nth-child(1) th { height: 46px; }
      #tableDetailJT thead tr:nth-child(2) th { top: 46px; height: 44px; }
      .mod-sticky-2 { left: 120px; }
  }

  .mod-sticky-1 { position: sticky; left: 0; z-index: 20; background: white; box-shadow: inset -1px 0 0 #e2e8f0; }
  .mod-sticky-2 { position: sticky; z-index: 20; background: white; box-shadow: inset -1px 0 0 #e2e8f0; }

  #tableDetailJT thead tr:nth-child(1) th.mod-sticky-1 { z-index: 50; background-color: #e2e8f0; box-shadow: inset -1px -1px 0 #cbd5e1; border-top-left-radius: 8px;}
  #tableDetailJT thead tr:nth-child(1) th.mod-sticky-2 { z-index: 49; background-color: #e2e8f0; box-shadow: inset -1px -1px 0 #cbd5e1; }
  #tableDetailJT thead tr:nth-child(2) th.mod-sticky-1 { z-index: 48; background-color: #bfdbfe !important; box-shadow: inset -1px -1px 0 #93c5fd; }
  #tableDetailJT thead tr:nth-child(2) th.mod-sticky-2 { z-index: 47; background-color: #bfdbfe !important; box-shadow: inset -1px -1px 0 #93c5fd; }

  #bodyModalJT tr:hover td { background-color: #f8fafc !important; }
  #bodyModalJT tr:hover td.mod-sticky-1, #bodyModalJT tr:hover td.mod-sticky-2 { background-color: #f8fafc !important; }

  /* Form Inputs */
  .inp { border:1px solid #cbd5e1; border-radius:6px; padding:0 8px; background:#fff; outline:none; transition: border 0.2s;}
  .inp:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,0.1); }
  @media (min-width: 768px) { .inp { border-radius:8px; padding:0 12px; } }
  
  .lbl { font-size:9px; color:#475569; font-weight:800; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.05em; display:block; white-space: nowrap;}
  @media (min-width: 768px) { .lbl { font-size:11px; } }
  
  .field { display:flex; flex-direction:column; }
  .btn-icon { display:inline-flex; align-items:center; justify-content:center; border:none; cursor:pointer; transition: transform 0.2s;}
  .btn-icon:hover { transform:translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }

  input[type="date"]::-webkit-inner-spin-button, input[type="date"]::-webkit-calendar-picker-indicator { display: none; -webkit-appearance: none; }
  input[type="date"] { -moz-appearance: textfield; }
  .badge-clean { display: inline-flex; align-items: center; justify-content: center; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: 700; text-transform: uppercase; border: 1px solid; letter-spacing: 0.5px;}
  @media (min-width: 768px) { .badge-clean { padding: 4px 8px; border-radius: 6px; font-size: 10px; } }
</style>

<script>
    window.currentUser = { kode_kantor: (typeof USER_KODE_KANTOR !== 'undefined') ? USER_KODE_KANTOR : '000' };
</script>

<div class="max-w-[1920px] mx-auto px-2 md:px-4 py-3 md:py-6 h-[calc(100vh-80px)] flex flex-col bg-slate-50 font-sans text-slate-800 overflow-hidden">
  
  <div class="flex-none mb-3 md:mb-4 flex flex-col xl:flex-row justify-between xl:items-start gap-3 md:gap-4 w-full">
      
      <div class="flex flex-col gap-1 shrink-0">
          <h1 class="text-lg md:text-2xl font-bold text-slate-800 flex items-center gap-2 mb-0.5">
              <span class="p-1.5 md:p-2 bg-blue-600 rounded-lg text-white shadow-sm">
                  <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
              </span>
              Rekap Jatuh Tempo & Top Up
          </h1>
          <p class="text-[11px] md:text-sm text-slate-500 italic ml-[34px] md:ml-[44px]">*Data Jatuh Tempo Kelek L</p>
      </div>

      <form id="formFilterJT" class="bg-white p-2.5 md:p-3 rounded-xl border border-slate-200 shadow-sm flex flex-wrap md:flex-nowrap items-end gap-2 md:gap-3 w-full xl:w-auto shrink-0 xl:ml-auto" onsubmit="event.preventDefault(); fetchRekapJT();">
          
          <div class="field w-[calc(50%-4px)] md:w-[140px] shrink-0">
              <label class="lbl">CLOSING</label>
              <input type="date" id="closing_date_jt" class="inp text-xs md:text-sm font-semibold h-[34px] md:h-[38px] text-slate-700 cursor-pointer w-full" required onclick="try{this.showPicker()}catch(e){}">
          </div>
          
          <div class="field w-[calc(50%-4px)] md:w-[140px] shrink-0">
              <label class="lbl">HARIAN</label>
              <input type="date" id="harian_date_jt" class="inp text-xs md:text-sm font-semibold h-[34px] md:h-[38px] text-slate-700 cursor-pointer w-full" required onclick="try{this.showPicker()}catch(e){}">
          </div>

          <div class="w-px h-6 bg-slate-200 shrink-0 mx-1 hidden md:block mt-auto mb-2"></div>
          
          <div class="field flex-1 min-w-[90px] md:w-[140px] shrink-0">
              <label class="lbl">BULAN JT</label>
              <select id="filter_bulan" class="inp text-xs md:text-sm font-semibold h-[34px] md:h-[38px] text-slate-700 cursor-pointer w-full">
                  <option value="01">Januari</option><option value="02">Februari</option><option value="03">Maret</option>
                  <option value="04">April</option><option value="05">Mei</option><option value="06">Juni</option>
                  <option value="07">Juli</option><option value="08">Agustus</option><option value="09">September</option>
                  <option value="10">Oktober</option><option value="11">November</option><option value="12">Desember</option>
              </select>
          </div>

          <div class="field w-[65px] md:w-[100px] shrink-0">
              <label class="lbl">TAHUN</label>
              <input type="number" id="filter_tahun" class="inp text-xs md:text-sm font-semibold h-[34px] md:h-[38px] text-slate-700 w-full" value="2026" required>
          </div>
          
          <div class="flex items-center gap-1.5 shrink-0 h-[34px] md:h-[38px]">
              <button type="submit" class="btn-icon h-full w-[34px] md:w-auto md:px-5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-sm text-sm font-bold uppercase tracking-wider" title="Cari Data">
                  <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" class="md:mr-1.5"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                  <span class="hidden md:inline">CARI</span>
              </button>
              <button type="button" onclick="exportExcelRekapJT()" class="btn-icon h-full w-[34px] md:w-auto md:px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-sm" title="Download Excel">
                  <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="md:w-[18px] md:h-[18px]"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                  <span class="hidden md:inline font-bold text-sm uppercase tracking-wider ml-1.5">EXCEL</span>
              </button>
          </div>
      </form>
  </div>

  <div class="flex-1 min-h-0 overflow-hidden bg-white rounded-xl shadow-sm border border-slate-200 relative">
    <div id="loadingJT" class="hidden absolute inset-0 bg-white/80 z-[100] flex flex-col items-center justify-center text-blue-600 backdrop-blur-sm">
        <div class="animate-spin rounded-full h-8 w-8 md:h-10 md:w-10 border-4 border-blue-500 border-t-transparent mb-3"></div>
        <span class="text-[10px] md:text-sm font-bold uppercase tracking-widest">Menyiapkan Data...</span>
    </div>
    
    <div class="h-full overflow-auto custom-scrollbar relative">
      <table class="w-max min-w-full text-center border-separate border-spacing-0 text-slate-700 table-fixed" id="tabelJT">
        <thead class="tracking-wider bg-slate-50 text-slate-800 font-bold text-[10px] md:text-sm" id="headJT">
          </thead>
        <tbody id="bodyJT" class="divide-y divide-slate-100 bg-white text-xs md:text-sm"></tbody>
      </table>
    </div>
  </div>

</div>

<div id="modalDetailJT" class="fixed inset-0 z-[9999] hidden items-end md:items-center justify-center p-0 sm:p-4">
  <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModalJT()"></div>
  
  <div class="relative bg-white w-full h-[95vh] md:h-[92vh] max-w-[1700px] rounded-t-xl md:rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-scale-up">
    
    <div class="flex justify-between items-center px-3 py-3 md:px-5 md:py-4 border-b bg-slate-50 shrink-0 flex-wrap gap-2">
        <div class="flex-1 min-w-[200px]">
            <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm md:text-base" id="modalTitleJT">
                <span class="bg-blue-100 text-blue-600 p-1 md:p-1.5 rounded-lg shadow-sm text-xs">👥</span> 
                Detail Nasabah
            </h3>
            <p class="text-[10px] md:text-xs text-slate-500 mt-0.5 ml-1 md:ml-8 font-mono" id="modalSubTitleJT">...</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-1.5 ml-auto shrink-0 w-full sm:w-auto mt-2 sm:mt-0 overflow-x-auto no-scrollbar">
            <select id="filter_kankas_modal" class="inp px-2 md:px-3 h-[34px] md:h-10 flex-1 sm:w-[160px] text-xs md:text-sm font-bold text-blue-800 bg-blue-50 outline-none shrink-0 cursor-pointer" onchange="filterAODetail()">
                <option value="">Semua Kankas</option>
            </select>
            <select id="filter_ao_modal" class="inp px-2 md:px-3 h-[34px] md:h-10 flex-1 sm:w-[160px] text-xs md:text-sm font-bold text-slate-700 bg-white outline-none shrink-0 cursor-pointer" onchange="filterAODetail()">
                <option value="">Semua AO</option>
            </select>

            <button onclick="downloadExcelDetailJT()" class="btn-icon bg-emerald-600 hover:bg-emerald-700 text-white px-3 md:px-4 h-[34px] md:h-10 rounded-lg shadow-sm text-xs md:text-sm font-bold uppercase tracking-wider shrink-0">
                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span class="hidden sm:inline ml-1.5">Excel</span>
            </button>
            <button onclick="closeModalJT()" class="w-[34px] md:w-10 h-[34px] md:h-10 flex items-center justify-center rounded-xl bg-slate-200 hover:bg-red-500 hover:text-white text-slate-600 transition font-bold text-xl md:text-2xl leading-none shrink-0">&times;</button>
        </div>
    </div>

    <div class="flex-1 overflow-auto bg-slate-50 relative custom-scrollbar p-0 md:p-3">
        <div id="loadingModalJT" class="hidden absolute inset-0 bg-white/90 z-40 flex flex-col items-center justify-center text-blue-600 backdrop-blur-sm">
            <div class="animate-spin rounded-full h-8 w-8 md:h-10 md:w-10 border-4 border-blue-500 border-t-transparent mb-3"></div>
            <span class="text-[10px] md:text-sm font-bold uppercase tracking-widest">Memuat Detail...</span>
        </div>
        
        <table class="w-max min-w-full text-left text-slate-700 border border-slate-200 md:rounded-xl shadow-sm bg-white table-fixed" id="tableDetailJT">
            <thead class="text-slate-600 font-extrabold uppercase tracking-wider text-[9px] md:text-xs">
                <tr>
                    <th class="px-2 md:px-3 py-2.5 md:py-4 border-b border-r border-slate-300 w-[90px] md:w-[120px] mod-sticky-1 rounded-tl-lg md:rounded-tl-xl text-blue-900 bg-[#f1f5f9]">Rekening</th>
                    <th class="px-3 md:px-4 py-2.5 md:py-4 border-b border-r border-slate-300 w-[150px] md:w-[240px] mod-sticky-2 text-blue-900 bg-[#f1f5f9]">Nama Nasabah</th>
                    <th class="px-3 md:px-4 py-2.5 md:py-4 border-b border-r border-slate-300 w-[140px] md:w-[200px]">Alamat</th>
                    <th class="px-2 md:px-3 py-2.5 md:py-4 border-b border-r border-slate-300 w-[90px] md:w-[130px] text-center">No HP</th>
                    <th class="px-2 md:px-3 py-2.5 md:py-4 border-b border-r border-slate-300 w-[90px] md:w-[130px] text-center">Kankas</th>
                    <th class="px-3 md:px-4 py-2.5 md:py-4 border-b border-r border-slate-300 w-[120px] md:w-[160px] text-blue-800">Nama AO</th>
                    <th class="px-3 md:px-4 py-2.5 md:py-4 border-b border-r border-slate-300 w-[90px] md:w-[140px] text-right">Plafon Lama</th>
                    <th class="px-2 md:px-3 py-2.5 md:py-4 border-b border-r border-slate-300 w-[80px] md:w-[120px] text-center">Tanggal JT</th>
                    <th class="px-3 md:px-4 py-2.5 md:py-4 border-b border-r border-slate-300 w-[90px] md:w-[140px] text-right">Sisa BD</th>
                    <th class="px-2 md:px-4 py-2.5 md:py-4 border-b border-r border-slate-300 w-[80px] md:w-[120px] text-center">Status</th>
                    <th class="px-3 md:px-4 py-2.5 md:py-4 border-b border-r border-emerald-300 w-[90px] md:w-[140px] text-right bg-emerald-50 text-emerald-900">Plafon Baru</th>
                    <th class="px-2 md:px-3 py-2.5 md:py-4 border-b border-slate-300 w-[70px] md:w-[90px] text-center">Aksi</th>
                </tr>
                <tr id="rowTotalDetailAtas"></tr>
            </thead>
            <tbody id="bodyModalJT" class="divide-y divide-slate-100 bg-white text-[10px] md:text-sm"></tbody>
        </table>
    </div>

    <div class="px-3 py-3 md:px-6 md:py-4 border-t bg-white flex justify-between items-center shrink-0">
        <span id="pageInfoJT" class="text-[10px] md:text-sm font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg">0 Data</span>
        <div class="flex gap-1.5 md:gap-2">
            <button id="btnPrevJT" onclick="changePageDetail(-1)" class="px-3 md:px-4 py-1.5 md:py-2 bg-white border border-slate-300 rounded-lg text-[10px] md:text-sm font-bold text-slate-600 hover:bg-slate-50 hover:border-slate-400 disabled:opacity-50 transition shadow-sm">« Prev</button>
            <button id="btnNextJT" onclick="changePageDetail(1)" class="px-3 md:px-4 py-1.5 md:py-2 bg-white border border-slate-300 rounded-lg text-[10px] md:text-sm font-bold text-slate-600 hover:bg-slate-50 hover:border-slate-400 disabled:opacity-50 transition shadow-sm">Next »</button>
        </div>
    </div>
  </div>
</div>

<script>
  // --- CONFIG & GLOBAL VARS ---
  const API_JT_URL = './api/jt/'; 
  const API_KODE   = './api/kode/';
  const API_DATE   = './api/date/';
  const nfID = new Intl.NumberFormat('id-ID');
  const fmt  = n => nfID.format(Math.round(Number(n||0)));
  const apiCall = (url, opt={}) => (window.apiFetch ? window.apiFetch(url,opt) : fetch(url,opt));

  let abortJT;
  let currentDetailParams = {};
  let currentDetailPage = 1;
  let currentDetailTotalPages = 1;
  const detailLimit = 20; 
  let userKodeGlobal = '000'; 
  let rekapDataCache = null;

  // --- INIT ---
  window.addEventListener('DOMContentLoaded', async () => {
      const user = (window.getUser && window.getUser()) || null;
      userKodeGlobal = (user?.kode ? String(user.kode).padStart(3,'0') : '000');

      setupHeaderJT(userKodeGlobal);

      const d = await getLastHarianData(); 
      if(d) {
          document.getElementById('closing_date_jt').value = d.last_closing;
          document.getElementById('harian_date_jt').value  = d.last_created;
          document.getElementById('filter_bulan').value = String(new Date(d.last_created).getMonth() + 1).padStart(2, '0');
      } else {
          const now = new Date();
          document.getElementById('closing_date_jt').value = `${now.getFullYear() - 1}-12-31`;
          document.getElementById('harian_date_jt').value = now.toISOString().split('T')[0];
          document.getElementById('filter_bulan').value = String(now.getMonth() + 1).padStart(2, '0');
      }
      document.getElementById('filter_tahun').value = new Date().getFullYear();
      
      fetchRekapJT();
  });

  async function getLastHarianData(){ 
      try{ const r=await apiCall(API_DATE); const j=await r.json(); return j.data||null; }catch{ return null; } 
  }

  // --- SETUP HEADER UTAMA (RESPONSIVE & FONT KECIL NAMA KANTOR) ---
  function setupHeaderJT(userKode) {
      const th = document.getElementById('headJT');
      let thContent = `<tr>`;

      if (userKode === '000') {
          thContent += `
              <th class="sticky-left-1 w-[60px] md:w-[80px] border-r border-b border-slate-300 align-middle uppercase text-center hidden md:table-cell text-slate-700 bg-slate-100 text-[10px] md:text-sm border-t-0 rounded-tl-lg">Kode</th>
              <th class="sticky-left-2 min-w-[120px] md:min-w-[200px] border-r border-b border-slate-300 align-middle uppercase pl-3 md:pl-5 text-slate-700 bg-slate-100 text-[10px] md:text-sm">Nama Kantor</th>
          `;
      } else {
          thContent += `
              <th class="sticky-left-1 min-w-[140px] md:min-w-[250px] border-r border-b border-slate-300 align-middle uppercase pl-3 md:pl-5 text-slate-700 bg-slate-100 text-[10px] md:text-sm border-t-0 rounded-tl-lg">Nama Kantor</th>
          `;
      }

      thContent += `
              <th class="px-2 md:px-4 border-r border-b border-slate-300 align-middle text-right bg-slate-50 text-slate-700 w-[70px] md:w-[140px]">
                  <div class="text-[10px] md:text-sm font-bold">NOA</div>
                  <div class="text-[8px] md:text-[11px] text-slate-500 font-normal md:mt-1 font-mono">(Lama)</div>
              </th>
              <th class="px-2 md:px-4 border-r border-b border-slate-300 align-middle text-right bg-slate-50 text-slate-700 w-[90px] md:w-[160px]">
                  <div class="text-[10px] md:text-sm font-bold">PLAFON</div>
                  <div class="text-[8px] md:text-[11px] text-slate-500 font-normal md:mt-1 font-mono">(Lama)</div>
              </th>
              <th class="px-2 md:px-4 border-r border-b border-emerald-200 align-middle text-right bg-emerald-50 text-emerald-800 w-[70px] md:w-[140px]">
                  <div class="text-[10px] md:text-sm font-bold">NOA</div>
                  <div class="text-[8px] md:text-[11px] text-emerald-600/80 font-normal md:mt-1 font-mono">(Baru)</div>
              </th>
              <th class="px-2 md:px-4 border-r border-b border-emerald-200 align-middle text-right bg-emerald-50 text-emerald-800 w-[90px] md:w-[160px]">
                  <div class="text-[10px] md:text-sm font-bold">PLAFON</div>
                  <div class="text-[8px] md:text-[11px] text-emerald-600/80 font-normal md:mt-1 font-mono">(Baru)</div>
              </th>
              <th class="px-2 md:px-4 border-r border-b border-slate-300 align-middle text-right bg-slate-50 text-slate-700 w-[90px] md:w-[160px]">
                  <div class="text-[10px] md:text-sm font-bold">SISA BAKI DEBET</div>
              </th>
              <th class="px-2 md:px-4 border-b border-slate-300 align-middle text-center bg-slate-50 text-slate-700 w-[70px] md:w-[120px]">
                  <div class="text-[10px] md:text-sm font-bold">% GROWTH</div>
              </th>
          </tr>
          <tr id="rowTotalJTAtas"></tr>
      `;
      th.innerHTML = thContent;
  }

  async function loadKankasModal(kode_cabang) {
      const el = document.getElementById('filter_kankas_modal');
      el.innerHTML = '<option value="">Semua Kankas</option>';
      if(!kode_cabang) return;
      try {
          const r = await apiCall(API_KODE, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({type: 'kode_kankas', kode_kantor: kode_cabang}) });
          const j = await r.json();
          if(j.data && Array.isArray(j.data)) {
              j.data.forEach(x => { el.add(new Option(x.deskripsi_group1 || x.kode_group1, x.kode_group1)); });
          }
      } catch(e) {}
  }

  // --- FETCH REKAP UTAMA ---
  async function fetchRekapJT(){
      const l=document.getElementById('loadingJT'); const tb=document.getElementById('bodyJT'); const trTot=document.getElementById('rowTotalJTAtas');

      if(abortJT) abortJT.abort(); abortJT = new AbortController();
      l.classList.remove('hidden'); 
      
      const colSpan = userKodeGlobal === '000' ? 8 : 7;
      tb.innerHTML = `<tr><td colspan="${colSpan}" class="py-16 md:py-20 text-center text-slate-400 italic text-xs md:text-base">Sedang mengambil data...</td></tr>`;
      trTot.innerHTML = '';
      rekapDataCache = null;

      try {
          const reqCabang = (userKodeGlobal === '000') ? null : userKodeGlobal;

          const payload = { 
              type: 'rekap prospek jatuh tempo', 
              closing_date: document.getElementById('closing_date_jt').value, 
              harian_date: document.getElementById('harian_date_jt').value, 
              bulan: document.getElementById('filter_bulan').value, 
              tahun: document.getElementById('filter_tahun').value, 
              kode_kantor: reqCabang 
          };
          const res = await apiCall(API_JT_URL, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload), signal: abortJT.signal });
          const json = await res.json();
          if(json.status !== 200) throw new Error(json.message);
          
          let rows = json.data.rekap_per_cabang || [];
          
          if (userKodeGlobal !== '000') {
              rows = rows.filter(r => String(r.kode_kantor) === userKodeGlobal);
          }
          
          rekapDataCache = rows;
          renderTableJT(rows, userKodeGlobal);
          
      } catch(err) { if(err.name !== 'AbortError') tb.innerHTML=`<tr><td colspan="${userKodeGlobal === '000' ? 8 : 7}" class="py-12 md:py-16 text-center text-red-500 tracking-widest uppercase font-bold text-[10px] md:text-sm">${err.message}</td></tr>`; } 
      finally { l.classList.add('hidden'); }
  }

  function renderTableJT(rows, userKode) {
      const tb = document.getElementById('bodyJT'); tb.innerHTML = '';
      const trTot = document.getElementById('rowTotalJTAtas'); trTot.innerHTML = '';
      const colSpan = userKode === '000' ? 8 : 7;
      
      if(rows.length === 0){ tb.innerHTML = `<tr><td colspan="${colSpan}" class="py-16 md:py-20 text-center text-slate-500 text-xs md:text-base">Tidak ada data.</td></tr>`; return; }

      let T = { noa_lama:0, plafon_lama:0, noa_baru:0, plafon_baru:0, baki_debet:0 };
      let html = '';
      
      rows.forEach(r => {
          T.noa_lama += Number(r.noa_lama); T.plafon_lama += Number(r.plafon_lama);
          T.noa_baru += Number(r.noa_baru); T.plafon_baru += Number(r.plafon_baru); T.baki_debet += Number(r.baki_debet);

          let rowHtml = `<tr class="transition h-[42px] md:h-[52px] border-b border-slate-100 group" onclick="initModalDetail('${r.kode_kantor}', '${r.nama_kantor}')">`;
          
          // 🔥 FIX: Nama Kantor Font Dikecilkan (text-[10px] md:text-sm), Angka Dibesarkan
          if (userKode === '000') {
              rowHtml += `
                <td class="sticky-left-1 px-2 md:px-4 py-1.5 md:py-2 text-center font-mono font-bold text-slate-500 hidden md:table-cell shadow-[inset_-1px_0_0_#e2e8f0] z-20 text-[10px] md:text-sm">${r.kode_kantor}</td>
                <td class="sticky-left-2 px-3 md:px-5 py-1.5 md:py-2 font-bold text-slate-700 truncate shadow-[inset_-1px_0_0_#e2e8f0] z-20 text-[10px] md:text-sm" title="${r.nama_kantor}">${r.nama_kantor}</td>
              `;
          } else {
              rowHtml += `
                <td class="sticky-left-1 px-3 md:px-5 py-1.5 md:py-2 font-bold text-slate-700 truncate shadow-[inset_-1px_0_0_#e2e8f0] z-20 text-[10px] md:text-sm" title="${r.nama_kantor}">${r.nama_kantor}</td>
              `;
          }

          rowHtml += `
                <td class="px-2 md:px-4 py-1.5 md:py-2 text-right font-extrabold text-slate-800 border-r border-slate-100 text-xs md:text-base">${fmt(r.noa_lama)}</td>
                <td class="px-2 md:px-4 py-1.5 md:py-2 text-right font-mono font-medium text-slate-600 border-r border-slate-100 text-[11px] md:text-base">${fmt(r.plafon_lama)}</td>
                <td class="px-2 md:px-4 py-1.5 md:py-2 text-right font-extrabold text-emerald-800 border-r border-emerald-50 bg-emerald-50/40 text-xs md:text-base">${fmt(r.noa_baru)}</td>
                <td class="px-2 md:px-4 py-1.5 md:py-2 text-right font-mono font-bold text-emerald-700 border-r border-emerald-50 bg-emerald-50/40 text-[11px] md:text-base">${fmt(r.plafon_baru)}</td>
                <td class="px-2 md:px-4 py-1.5 md:py-2 text-right font-mono font-medium text-slate-600 border-r border-slate-100 text-[11px] md:text-base">${fmt(r.baki_debet)}</td>
                <td class="px-2 md:px-4 py-1.5 md:py-2 text-center font-extrabold text-xs md:text-base ${r.persentase >= 100 ? 'text-emerald-600' : 'text-orange-500'}">${r.persentase}%</td>
            </tr>`;
            
          html += rowHtml;
      });
      tb.innerHTML = html;

      if(userKode === '000' && rows.length > 0) {
          const gp = (T.plafon_lama > 0) ? (T.plafon_baru / T.plafon_lama * 100) : 0;
          trTot.innerHTML = `
              <th class="sticky-left-1 px-2 md:px-4 border-r border-blue-300 text-center text-blue-900 hidden md:table-cell">-</th>
              <th class="sticky-left-2 px-3 md:px-5 border-r border-blue-300 text-left uppercase tracking-widest font-extrabold text-[10px] md:text-sm text-blue-900">GRAND TOTAL</th>
              <th class="px-2 md:px-4 border-r border-blue-300 text-right font-extrabold text-[11px] md:text-base text-blue-900 align-middle">${fmt(T.noa_lama)}</th>
              <th class="px-2 md:px-4 border-r border-blue-300 text-right font-mono font-bold text-[10px] md:text-base text-blue-900 align-middle">${fmt(T.plafon_lama)}</th>
              <th class="px-2 md:px-4 border-r border-blue-300 text-right font-extrabold text-[11px] md:text-base text-emerald-800 align-middle">${fmt(T.noa_baru)}</th>
              <th class="px-2 md:px-4 border-r border-blue-300 text-right font-mono font-bold text-[10px] md:text-base text-emerald-800 align-middle">${fmt(T.plafon_baru)}</th>
              <th class="px-2 md:px-4 border-r border-blue-300 text-right font-mono font-bold text-[10px] md:text-base text-blue-900 align-middle">${fmt(T.baki_debet)}</th>
              <th class="px-2 md:px-4 text-center font-extrabold text-xs md:text-lg ${gp >= 100 ? 'text-emerald-700' : 'text-orange-700'} align-middle">${gp.toFixed(2)}%</th>
          `;
      } else {
          trTot.innerHTML = ``;
      }
  }

  // --- EXPORT EXCEL REKAP UTAMA ---
  window.exportExcelRekapJT = function() {
      if(!rekapDataCache || rekapDataCache.length === 0) return alert("Tidak ada data rekap untuk didownload.");

      let csv = "Kode\tNama Kantor\tNOA Lama\tPlafon Lama\tNOA Baru\tPlafon Baru\tSisa Baki Debet\t% Growth\n";
      rekapDataCache.forEach(r => {
          csv += `'${r.kode_kantor}\t${r.nama_kantor||''}\t${r.noa_lama}\t${Math.round(r.plafon_lama)}\t${r.noa_baru}\t${Math.round(r.plafon_baru)}\t${Math.round(r.baki_debet)}\t${r.persentase}%\n`;
      });

      const blob = new Blob([csv], { type: 'application/vnd.ms-excel' });
      const a = document.createElement('a');
      a.href = window.URL.createObjectURL(blob);
      a.download = `Rekap_JatuhTempo_${document.getElementById("filter_tahun").value}.xls`; 
      a.click();
  }

  // --- MODAL & FILTER LOGIC ---
  function initModalDetail(kode, nama) {
      if (userKodeGlobal !== '000' && String(kode) !== userKodeGlobal) {
          alert(`AKSES DITOLAK!\nAnda tidak memiliki izin untuk melihat detail Cabang ${kode}.`);
          return;
      }

      currentDetailParams = {
          type: 'detail prospek jatuh tempo',
          closing_date: document.getElementById('closing_date_jt').value,
          harian_date: document.getElementById('harian_date_jt').value,
          bulan: document.getElementById('filter_bulan').value,
          tahun: document.getElementById('filter_tahun').value,
          kode_kantor: kode,
          kode_kankas: null,
          kode_ao: null,
          limit: detailLimit
      };
      
      const selAO = document.getElementById('filter_ao_modal');
      selAO.innerHTML = '<option value="">Semua AO</option>';

      const modal = document.getElementById('modalDetailJT');
      modal.classList.remove('hidden'); modal.classList.add('flex');
      
      document.getElementById('modalTitleJT').innerHTML = `<span class="bg-blue-100 text-blue-600 p-1 md:p-1.5 rounded-lg shadow-sm text-xs md:text-sm">👥</span> Detail Nasabah - ${nama}`;
      document.getElementById('modalSubTitleJT').textContent = `Periode JT: ${currentDetailParams.bulan}/${currentDetailParams.tahun}`;
      
      loadKankasModal(kode);
      loadDetailPage(1);
  }

  function filterAODetail() {
      currentDetailParams.kode_ao = document.getElementById('filter_ao_modal').value;
      currentDetailParams.kode_kankas = document.getElementById('filter_kankas_modal').value;
      loadDetailPage(1);
  }

  async function loadDetailPage(page) {
      const l = document.getElementById('loadingModalJT'); const tb = document.getElementById('bodyModalJT'); const info = document.getElementById('pageInfoJT');
      const trTot = document.getElementById('rowTotalDetailAtas');
      
      l.classList.remove('hidden'); tb.innerHTML = ''; trTot.innerHTML = '';
      const actDate = new Date(document.getElementById('harian_date_jt').value);

      try {
          const payload = { ...currentDetailParams, page: page };
          const res = await apiCall(API_JT_URL, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          const json = await res.json();
          
          const list = json.data?.data || [];
          const aoList = json.data?.ao_list || []; 
          const meta = json.data?.pagination || { total_records:0, total_pages:1 };

          currentDetailPage = page; currentDetailTotalPages = meta.total_pages;

          const selAO = document.getElementById('filter_ao_modal');
          if (selAO.options.length === 1 && aoList.length > 0) {
              aoList.forEach(ao => { selAO.add(new Option(ao.nama_ao, ao.kode_group2)); });
          }

          if(list.length === 0) {
              tb.innerHTML = `<tr><td colspan="12" class="py-16 md:py-20 text-center text-slate-400 italic text-xs md:text-base">Tidak ada data detail.</td></tr>`;
              info.innerText = `0 data`;
              return;
          }

          list.sort((a, b) => new Date(a.tgl_jatuh_tempo) - new Date(b.tgl_jatuh_tempo));

          let t_plafon_lama = 0, t_plafon_baru = 0, t_sisa_bd = 0;
          let html = '';

          list.forEach(r => {
              t_plafon_lama += parseFloat(r.plafond_lama||0);
              t_plafon_baru += parseFloat(r.plafond_baru||0);
              
              const alamatLengkap = r.alamat || '-';
              const alamatPendek = alamatLengkap.length > 25 ? alamatLengkap.substring(0, 25) + '...' : alamatLengkap;
              const hp = r.no_hp ? `<span class="font-mono text-slate-600">${r.no_hp}</span>` : `<span class="text-slate-400">-</span>`;
              const kankas = r.kankas || '-';
              const aoName = (r.nama_ao || '-').split(' ').slice(0, 2).join(' ');

              let statStr = (r.keterangan_status || '').toUpperCase();
              let isClear = statStr.includes("SUDAH") || statStr === "LUNAS" || statStr === "LUNAS (POTENSI)";
              let isDrop = statStr.includes("DROP");

              if(!isClear) t_sisa_bd += parseFloat(r.baki_debet_lama||0);

              let badgeClass = "text-slate-600 border-slate-300 bg-slate-50";
              if(statStr.includes("SUDAH")) badgeClass = "text-emerald-700 border-emerald-300 bg-emerald-50/80";
              else if(statStr === "LUNAS" || statStr === "LUNAS (POTENSI)") badgeClass = "text-blue-700 border-blue-300 bg-blue-50/80";
              else if(statStr.includes("TOP UP")) badgeClass = "text-purple-700 border-purple-300 bg-purple-50/80";
              else if(statStr.includes("BELUM")) badgeClass = "text-rose-700 border-rose-300 bg-rose-50/80";

              let nomBaru = '-';
              if (r.plafond_baru > 0) {
                  nomBaru = `<div class="font-bold text-emerald-700 text-sm">${fmt(r.plafond_baru)}</div><div class="text-[8px] md:text-[9px] text-emerald-600 font-mono mt-0.5">${r.tgl_realisasi_baru||''}</div>`;
              }

              let sisaBdVisual = isClear ? '-' : fmt(r.baki_debet_lama);

              const jtDate = new Date(r.tgl_jatuh_tempo);
              const diffDays = Math.ceil((jtDate - actDate) / (1000 * 60 * 60 * 24));
              let strJt = `<div class="font-mono text-[10px] md:text-sm text-slate-700">${r.tgl_jatuh_tempo}</div>`;
              
              if (!isClear) {
                  if (diffDays < 0) strJt += `<div class="text-[8px] md:text-[9px] text-rose-600 font-bold mt-1 bg-rose-50 rounded inline-block px-1.5 py-0.5">Lewat ${Math.abs(diffDays)} Hari</div>`;
                  else if (diffDays === 0) strJt += `<div class="text-[8px] md:text-[9px] text-orange-600 font-bold mt-1 bg-orange-50 rounded inline-block px-1.5 py-0.5">HARI INI!</div>`;
                  else strJt += `<div class="text-[8px] md:text-[10px] text-slate-500 mt-1 font-medium">Kurang ${diffDays} Hari</div>`;
              }

              let isLocked = statStr.includes("SUDAH") || isDrop;
              const btnAksi = isLocked 
                  ? `<span class="text-[9px] md:text-xs font-bold text-slate-400">LOCKED</span>`
                  : `<button class="bg-blue-600 hover:bg-blue-700 text-white px-2 md:px-3 py-1 md:py-1.5 rounded md:rounded-lg text-[9px] md:text-xs font-bold shadow-sm transition w-full uppercase tracking-widest">PROSPEK</button>`;

              html += `<tr class="transition h-[42px] md:h-[52px] group border-b border-slate-100">
                    <td class="mod-sticky-1 px-2 md:px-3 py-1.5 md:py-2 font-mono text-[9px] md:text-sm text-slate-500 bg-white border-r border-slate-100 shadow-[inset_-1px_0_0_#e2e8f0]">${r.no_rekening_lama}</td>
                    <td class="mod-sticky-2 px-3 md:px-4 py-1.5 md:py-2 font-bold text-[10px] md:text-sm text-slate-700 bg-white truncate border-r border-slate-100 max-w-[150px] md:max-w-[280px] shadow-[inset_-1px_0_0_#e2e8f0]" title="${r.nama_nasabah}">${r.nama_nasabah}</td>
                    <td class="px-3 md:px-4 py-1.5 md:py-2 text-[9px] md:text-sm text-slate-500 whitespace-nowrap border-r border-slate-100" title="${alamatLengkap}">${alamatPendek}</td>
                    <td class="px-2 md:px-3 py-1.5 md:py-2 text-center border-r border-slate-100 text-[9px] md:text-sm">${hp}</td>
                    <td class="px-2 md:px-3 py-1.5 md:py-2 text-center font-mono text-[9px] md:text-sm text-slate-500 border-r border-slate-100">${kankas}</td>
                    
                    <td class="px-3 md:px-4 py-1.5 md:py-2 text-[9px] md:text-sm font-bold text-blue-700 truncate border-r border-slate-100">${aoName}</td>
                    <td class="px-3 md:px-4 py-1.5 md:py-2 text-right font-medium text-[10px] md:text-sm text-slate-600 border-r border-slate-100">${fmt(r.plafond_lama)}</td>
                    <td class="px-2 md:px-3 py-1.5 md:py-2 text-center border-r border-slate-100">${strJt}</td>
                    <td class="px-3 md:px-4 py-1.5 md:py-2 text-right font-mono font-bold text-[10px] md:text-sm text-slate-800 border-r border-slate-100 bg-slate-50/50">${sisaBdVisual}</td>
                    <td class="px-2 md:px-4 py-1.5 md:py-2 text-center border-r border-slate-100"><span class="badge-clean ${badgeClass}">${r.keterangan_status}</span></td>
                    <td class="px-3 md:px-4 py-1.5 md:py-2 text-right bg-emerald-50/30 border-r border-slate-100">${nomBaru}</td>
                    <td class="px-2 md:px-3 py-1.5 md:py-2 text-center">${btnAksi}</td>
                </tr>`;
          });
          tb.innerHTML = html;

          trTot.innerHTML = `
              <th class="mod-sticky-1 px-2 md:px-3 border-r border-b border-blue-200 uppercase tracking-widest text-center text-blue-900 bg-[#eff6ff]">-</th>
              <th class="mod-sticky-2 px-3 md:px-4 border-r border-b border-blue-200 uppercase tracking-widest font-extrabold text-[10px] md:text-sm text-blue-900 bg-[#eff6ff]">TOTAL HALAMAN INI</th>
              <th class="px-3 md:px-4 border-r border-b border-blue-200 text-center bg-[#eff6ff]">-</th>
              <th class="px-2 md:px-3 border-r border-b border-blue-200 text-center bg-[#eff6ff]">-</th>
              <th class="px-2 md:px-3 border-r border-b border-blue-200 text-center bg-[#eff6ff]">-</th>
              <th class="px-3 md:px-4 border-r border-b border-blue-200 text-center bg-[#eff6ff]">-</th>
              <th class="px-3 md:px-4 border-r border-b border-blue-200 text-right font-mono font-bold text-[11px] md:text-sm text-blue-900 bg-[#eff6ff]">${fmt(t_plafon_lama)}</th>
              <th class="px-2 md:px-3 border-r border-b border-blue-200 text-center bg-[#eff6ff]">-</th>
              <th class="px-3 md:px-4 border-r border-b border-blue-300 text-right font-mono font-bold text-[11px] md:text-sm text-blue-900 bg-blue-100/50">${fmt(t_sisa_bd)}</th>
              <th class="px-2 md:px-4 border-r border-b border-blue-200 text-center bg-[#eff6ff]">-</th>
              <th class="px-3 md:px-4 border-r border-b border-blue-200 text-right font-mono font-bold text-[11px] md:text-sm text-emerald-800 bg-emerald-50/50">${fmt(t_plafon_baru)}</th>
              <th class="px-2 md:px-3 border-b border-blue-200 text-center bg-[#eff6ff]">-</th>
          `;

          const start = ((page-1)*detailLimit)+1; const end = Math.min(page*detailLimit, meta.total_records);
          info.innerText = `Hal ${page} / ${meta.total_pages} (${start}-${end} dari ${fmt(meta.total_records)})`;
          
          document.getElementById('btnPrevJT').disabled = page <= 1;
          document.getElementById('btnNextJT').disabled = page >= meta.total_pages;

      } catch(err){ console.error(err); } finally { l.classList.add('hidden'); }
  }

  // --- EXPORT EXCEL ---
  async function downloadExcelDetailJT() {
      const btn = event.target.closest('button'); const txt = btn.innerHTML;
      btn.innerHTML = `<span class="animate-spin inline-block h-3 w-3 md:h-4 md:w-4 border-2 border-white border-t-transparent rounded-full mr-1 md:mr-2"></span>...`;
      btn.disabled = true;

      try {
          const payload = { ...currentDetailParams, page: 1, limit: 10000 };
          const res = await apiCall(API_JT_URL, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          const json = await res.json();
          const rows = json.data?.data || [];

          if(rows.length === 0) { alert("Tidak ada data."); return; }

          rows.sort((a, b) => new Date(a.tgl_jatuh_tempo) - new Date(b.tgl_jatuh_tempo));

          let csv = `No Rekening\tNama Nasabah\tAlamat\tNo HP\tKankas\tNama AO\tPlafond Lama\tSisa Baki Debet\tTgl JT\tStatus\tPlafond Baru\tTgl Realisasi Baru\n`;
          rows.forEach(r => {
              const alamat = r.alamat || '-';
              const hp = r.no_hp || '-';
              const kankas = r.kankas || '-';
              
              let statStr = (r.keterangan_status || '').toUpperCase();
              let isClear = statStr.includes("SUDAH") || statStr === "LUNAS" || statStr === "LUNAS (POTENSI)";
              let sisaBdEx = isClear ? 0 : Math.round(r.baki_debet_lama||0);

              csv += `'${r.no_rekening_lama}\t${r.nama_nasabah}\t${alamat}\t'${hp}\t${kankas}\t${r.nama_ao}\t${Math.round(r.plafond_lama||0)}\t${sisaBdEx}\t${r.tgl_jatuh_tempo}\t${r.keterangan_status}\t${Math.round(r.plafond_baru||0)}\t${r.tgl_realisasi_baru||'-'}\n`;
          });

          const blob = new Blob([csv], { type: 'application/vnd.ms-excel' });
          const url = window.URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.href = url;
          a.download = `Detail_JT_${currentDetailParams.kode_kantor}_${currentDetailParams.bulan}.xls`;
          document.body.appendChild(a); a.click(); document.body.removeChild(a);

      } catch(e) { alert("Gagal export."); } finally { btn.innerHTML = txt; btn.disabled = false; }
  }

  window.changePageDetail = (step) => { const n = currentDetailPage + step; if (n > 0 && n <= currentDetailTotalPages) loadDetailPage(n); }
  window.closeModalJT = () => {
      const modal = document.getElementById('modalDetailJT');
      modal.classList.add('hidden'); modal.classList.remove('flex');
  }
  document.addEventListener('keydown', e => { if(e.key === 'Escape') closeModalJT(); });
</script>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          c     