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
     CSS MAGIC STICKY TABLE (MEMBEKUKAN HEADER)
     ======================================================== */
  /* TABEL REKAP UTAMA */
  #tabelJT thead th { position: sticky; box-shadow: inset 0 -1px 0 #cbd5e1; }
  #tabelJT thead tr:nth-child(1) th { top: 0; z-index: 40; height: 44px; background-color: #f1f5f9; }
  
  /* Baris Grand Total (Lapis 2) */
  #tabelJT thead tr:nth-child(2) th { 
      top: 44px; z-index: 38; height: 42px; 
      background-color: #dbeafe !important; /* Biru Soft */
      border-bottom: 2px solid #bfdbfe;
      box-shadow: inset 0 -1px 0 #93c5fd;
  }

  /* Freeze Kolom Kiri Rekap Utama */
  .sticky-left-1 { position: sticky; left: 0; z-index: 20; background: white; box-shadow: inset -1px 0 0 #e2e8f0; }
  .sticky-left-2 { position: sticky; left: 0; z-index: 20; background: white; box-shadow: inset -1px 0 0 #e2e8f0; }
  
  @media (min-width: 768px) { /* md breakpoint */
      .sticky-left-2 { left: 60px; } /* Geser ke kanan karena ada kolom KODE di PC (Hanya utk Pusat) */
  }

  #tabelJT thead tr:nth-child(1) th.sticky-left-1 { z-index: 50; background-color: #e2e8f0; box-shadow: inset -1px -1px 0 #cbd5e1; }
  #tabelJT thead tr:nth-child(1) th.sticky-left-2 { z-index: 49; background-color: #e2e8f0; box-shadow: inset -1px -1px 0 #cbd5e1; }
  #tabelJT thead tr:nth-child(2) th.sticky-left-1 { z-index: 48; background-color: #bfdbfe !important; box-shadow: inset -1px -2px 0 #93c5fd; }
  #tabelJT thead tr:nth-child(2) th.sticky-left-2 { z-index: 47; background-color: #bfdbfe !important; box-shadow: inset -1px -2px 0 #93c5fd; }

  /* Hover Efek Utama */
  #bodyJT tr:hover td { background-color: #eff6ff !important; cursor: pointer; }
  #bodyJT tr:hover td.sticky-left-1, #bodyJT tr:hover td.sticky-left-2 { background-color: #eff6ff !important; }

  /* TABEL MODAL DETAIL */
  #tableDetailJT thead th { position: sticky; box-shadow: inset 0 -1px 0 #cbd5e1; }
  #tableDetailJT thead tr:nth-child(1) th { top: 0; z-index: 40; height: 40px; background-color: #f1f5f9; }
  #tableDetailJT thead tr:nth-child(2) th { 
      top: 40px; z-index: 39; height: 38px; 
      background-color: #dbeafe !important; 
      border-bottom: 2px solid #bfdbfe;
      box-shadow: inset 0 -1px 0 #93c5fd;
  }

  /* Freeze Kolom Kiri Modal Detail */
  .mod-sticky-1 { position: sticky; left: 0; z-index: 20; background: white; box-shadow: inset -1px 0 0 #e2e8f0; }
  .mod-sticky-2 { position: sticky; left: 90px; z-index: 20; background: white; box-shadow: inset -1px 0 0 #e2e8f0; }

  #tableDetailJT thead tr:nth-child(1) th.mod-sticky-1 { z-index: 50; background-color: #e2e8f0; box-shadow: inset -1px -1px 0 #cbd5e1; }
  #tableDetailJT thead tr:nth-child(1) th.mod-sticky-2 { z-index: 49; background-color: #e2e8f0; box-shadow: inset -1px -1px 0 #cbd5e1; }
  #tableDetailJT thead tr:nth-child(2) th.mod-sticky-1 { z-index: 48; background-color: #bfdbfe !important; box-shadow: inset -1px -2px 0 #93c5fd; }
  #tableDetailJT thead tr:nth-child(2) th.mod-sticky-2 { z-index: 47; background-color: #bfdbfe !important; box-shadow: inset -1px -2px 0 #93c5fd; }

  /* Hover Efek Detail */
  #bodyModalJT tr:hover td { background-color: #f8fafc !important; }
  #bodyModalJT tr:hover td.mod-sticky-1, #bodyModalJT tr:hover td.mod-sticky-2 { background-color: #f8fafc !important; }

  /* Badge Custom Clean */
  .badge-clean { display: inline-flex; align-items: center; justify-content: center; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: 600; text-transform: uppercase; border: 1px solid; }
</style>

<script>
    // --- AREA KONFIGURASI USER LOGIN ---
    window.currentUser = { kode_kantor: (typeof USER_KODE_KANTOR !== 'undefined') ? USER_KODE_KANTOR : '000' };
</script>

<div class="max-w-[1920px] mx-auto px-2 md:px-4 py-3 md:py-5 h-[calc(100vh-80px)] flex flex-col bg-slate-50 font-sans text-slate-800 overflow-hidden">
  
  <div class="flex-none mb-3 flex flex-col xl:flex-row justify-between xl:items-end gap-3 w-full">
      
      <div class="flex flex-col gap-2 shrink-0">
          <h1 class="text-xl md:text-2xl font-bold text-slate-800 flex items-center gap-2">
              <span class="p-1.5 md:p-2 bg-blue-600 rounded-lg text-white shadow-sm">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
              </span>
              Rekap Jatuh Tempo & Top Up
          </h1>
          
          <div id="summaryPills" class="hidden flex flex-wrap items-center gap-2">
              <div class="hidden sm:flex flex-col bg-white border border-slate-200 px-3 py-1 rounded-lg shadow-sm min-w-[110px]">
                  <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">JT Lama</span>
                  <div class="flex items-end gap-1.5">
                      <span class="text-sm font-bold text-slate-800" id="sum_noa_lama">0</span>
                      <span class="text-[10px] font-mono text-slate-400 mb-0.5" id="sum_plaf_lama">0</span>
                  </div>
              </div>
              <div class="flex flex-col bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-lg shadow-sm min-w-[110px]">
                  <span class="text-[9px] font-bold text-emerald-700 uppercase tracking-widest">Realisasi Baru</span>
                  <div class="flex items-end gap-1.5">
                      <span class="text-sm font-bold text-emerald-800" id="sum_noa_baru">0</span>
                      <span class="text-[10px] font-mono text-emerald-600 mb-0.5" id="sum_plaf_baru">0</span>
                  </div>
              </div>
              <div class="flex flex-col bg-blue-50 border border-blue-200 px-3 py-1 rounded-lg shadow-sm min-w-[110px]">
                  <span class="text-[9px] font-bold text-blue-700 uppercase tracking-widest">% Growth / Retensi</span>
                  <div class="flex items-end gap-1.5">
                      <span class="text-sm font-bold text-blue-800" id="sum_persen">0%</span>
                  </div>
              </div>
              <div class="hidden sm:flex flex-col bg-orange-50 border border-orange-200 px-3 py-1 rounded-lg shadow-sm min-w-[110px]">
                  <span class="text-[9px] font-bold text-orange-700 uppercase tracking-widest">Sisa Baki Debet</span>
                  <div class="flex items-end gap-1.5">
                      <span class="text-sm font-bold text-orange-800" id="sum_bd">0</span>
                  </div>
              </div>
          </div>
      </div>

      <form id="formFilterJT" class="bg-white p-2 md:p-2.5 rounded-xl border border-slate-200 shadow-sm flex flex-nowrap items-center gap-1.5 md:gap-3 w-full xl:w-auto shrink-0 overflow-x-auto no-scrollbar" onsubmit="event.preventDefault(); fetchRekapJT();">
          
          <div class="flex flex-col w-[85px] md:w-[120px] shrink-0">
              <label class="text-[8px] md:text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1 truncate">CLOSING</label>
              <input type="date" id="closing_date_jt" class="border border-slate-200 rounded-md px-1.5 md:px-2 py-1.5 text-[10px] md:text-xs text-slate-700 outline-none focus:border-blue-500 transition w-full" required>
          </div>
          
          <div class="flex flex-col w-[85px] md:w-[120px] shrink-0">
              <label class="text-[8px] md:text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1 truncate">HARIAN</label>
              <input type="date" id="harian_date_jt" class="border border-slate-200 rounded-md px-1.5 md:px-2 py-1.5 text-[10px] md:text-xs text-slate-700 outline-none focus:border-blue-500 transition w-full" required>
          </div>

          <div class="w-px h-6 bg-slate-200 shrink-0 mx-1 hidden md:block"></div>
          
          <div class="flex flex-col w-[80px] md:w-[120px] shrink-0">
              <label class="text-[8px] md:text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1 truncate">BULAN JT</label>
              <select id="filter_bulan" class="border border-slate-200 rounded-md px-1.5 md:px-2 py-1.5 text-[10px] md:text-xs text-slate-700 outline-none focus:border-blue-500 w-full cursor-pointer">
                  <option value="01">Januari</option><option value="02">Februari</option><option value="03">Maret</option>
                  <option value="04">April</option><option value="05">Mei</option><option value="06">Juni</option>
                  <option value="07">Juli</option><option value="08">Agustus</option><option value="09">September</option>
                  <option value="10">Oktober</option><option value="11">November</option><option value="12">Desember</option>
              </select>
          </div>

          <div class="flex flex-col w-[60px] md:w-[80px] shrink-0">
              <label class="text-[8px] md:text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1 truncate">TAHUN</label>
              <input type="number" id="filter_tahun" class="border border-slate-200 rounded-md px-1.5 md:px-2 py-1.5 text-[10px] md:text-xs text-slate-700 outline-none focus:border-blue-500 transition w-full" value="2026" required>
          </div>
          
          <div class="flex items-center shrink-0 h-[28px] md:h-[32px] mt-3.5">
              <button type="submit" class="h-full w-[32px] md:w-auto md:px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-md flex items-center justify-center transition shadow-sm font-bold text-[10px] md:text-xs uppercase tracking-wider" title="Cari Data">
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" class="md:mr-1.5"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                  <span class="hidden md:inline">CARI</span>
              </button>
              <button type="button" onclick="exportExcelRekapJT()" class="h-full w-[34px] md:w-auto md:px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md flex items-center justify-center transition shadow-sm font-bold text-[10px] md:text-xs uppercase tracking-wider ml-1.5" title="Download Excel">
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                  <span class="hidden md:inline ml-1.5">EXCEL</span>
              </button>
          </div>
      </form>
  </div>

  <div class="flex-1 overflow-hidden bg-white rounded-xl shadow-sm border border-slate-200 relative">
    <div id="loadingJT" class="hidden absolute inset-0 bg-white/80 z-[100] flex flex-col items-center justify-center text-blue-600 backdrop-blur-sm">
        <div class="animate-spin rounded-full h-8 w-8 border-4 border-blue-500 border-t-transparent mb-2"></div>
        <span class="text-xs font-bold uppercase tracking-widest">Menyiapkan Data...</span>
    </div>
    
    <div class="h-full overflow-auto custom-scrollbar relative">
      <table class="w-max min-w-full text-xs text-left text-slate-700 border-separate border-spacing-0" id="tabelJT">
        <thead class="tracking-wider" id="headJT">
          </thead>
        <tbody id="bodyJT" class="divide-y divide-slate-100 bg-white text-[10px] md:text-[11px]"></tbody>
      </table>
    </div>
  </div>

</div>

<div id="modalDetailJT" class="fixed inset-0 z-[9999] hidden items-center justify-center sm:p-4">
  <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModalJT()"></div>
  
  <div class="relative bg-white w-full h-[95vh] md:h-[92vh] max-w-[1600px] rounded-t-xl md:rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-scale-up">
    
    <div class="flex justify-between items-center px-4 py-3 md:px-5 border-b bg-slate-50 shrink-0 flex-wrap gap-2">
        <div class="flex-1 min-w-[200px]">
            <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm md:text-base" id="modalTitleJT">
                <span class="bg-blue-100 text-blue-600 p-1 md:p-1.5 rounded-lg shadow-sm text-xs">👥</span> 
                Detail Nasabah
            </h3>
            <p class="text-[9px] md:text-[10px] text-slate-500 mt-0.5 ml-1 md:ml-8 font-mono" id="modalSubTitleJT">...</p>
        </div>
        
        <div class="flex items-center gap-1.5 ml-auto shrink-0 w-full sm:w-auto mt-2 sm:mt-0 overflow-x-auto no-scrollbar">
            <select id="filter_kankas_modal" class="border border-blue-200 rounded-lg px-2 h-[30px] md:h-8 w-[100px] md:w-[130px] text-[9px] md:text-[10px] font-bold text-blue-800 bg-blue-50 outline-none focus:ring-1 focus:ring-blue-400 shrink-0" onchange="filterAODetail()">
                <option value="">Semua Kankas</option>
            </select>
            <select id="filter_ao_modal" class="border border-slate-200 rounded-lg px-2 h-[30px] md:h-8 w-[100px] md:w-[130px] text-[9px] md:text-[10px] font-bold text-slate-600 bg-white outline-none focus:ring-1 focus:ring-blue-400 shrink-0" onchange="filterAODetail()">
                <option value="">Semua AO</option>
            </select>

            <button onclick="downloadExcelDetailJT()" class="h-[30px] md:h-8 px-2 md:px-3 border-none bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition shadow-sm flex items-center justify-center font-bold text-[9px] md:text-[10px] uppercase tracking-wider shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span class="hidden sm:inline ml-1">Excel</span>
            </button>
            <button onclick="closeModalJT()" class="w-[30px] md:w-8 h-[30px] md:h-8 flex items-center justify-center rounded-lg bg-slate-200 hover:bg-red-500 hover:text-white text-slate-600 transition font-bold text-lg leading-none shrink-0">&times;</button>
        </div>
    </div>

    <div class="flex-1 overflow-auto bg-slate-50 relative custom-scrollbar p-0 md:p-2">
        <div id="loadingModalJT" class="hidden absolute inset-0 bg-white/90 z-40 flex flex-col items-center justify-center text-blue-600 backdrop-blur-sm">
            <div class="animate-spin rounded-full h-8 w-8 border-4 border-blue-500 border-t-transparent mb-2"></div>
            <span class="text-xs font-bold uppercase tracking-widest">Memuat Detail...</span>
        </div>
        
        <table class="w-max min-w-full text-xs text-left text-slate-700 border border-slate-200 md:rounded-lg shadow-sm bg-white table-fixed" id="tableDetailJT">
            <thead class="text-slate-600 font-bold uppercase tracking-wider text-[9px] md:text-[10px]">
                <tr>
                    <th class="px-2 py-2.5 border-b border-r border-slate-300 w-[90px] mod-sticky-1">Rekening</th>
                    <th class="px-3 py-2.5 border-b border-r border-slate-300 w-[160px] md:w-[220px] mod-sticky-2">Nama Nasabah</th>
                    <th class="px-3 py-2.5 border-b border-r border-slate-300 w-[150px] md:w-[200px]">Alamat</th>
                    <th class="px-2 py-2.5 border-b border-r border-slate-300 w-[90px] text-center">No HP</th>
                    <th class="px-2 py-2.5 border-b border-r border-slate-300 w-[80px] text-center">Kankas</th>
                    <th class="px-3 py-2.5 border-b border-r border-slate-300 w-[110px] text-blue-800">Nama AO</th>
                    <th class="px-3 py-2.5 border-b border-r border-slate-300 w-[90px] text-right">Plafon Lama</th>
                    <th class="px-3 py-2.5 border-b border-r border-slate-300 w-[110px] text-center">Tanggal JT</th>
                    <th class="px-3 py-2.5 border-b border-r border-slate-300 w-[90px] text-right">Sisa BD</th>
                    <th class="px-3 py-2.5 border-b border-r border-slate-300 w-[100px] text-center">Status</th>
                    <th class="px-3 py-2.5 border-b border-r border-emerald-300 w-[90px] text-right bg-emerald-50 text-emerald-900">Plafon Baru</th>
                    <th class="px-2 py-2.5 border-b border-slate-300 w-[70px] text-center">Aksi</th>
                </tr>
                <tr id="rowTotalDetailAtas"></tr>
            </thead>
            <tbody id="bodyModalJT" class="divide-y divide-slate-100 bg-white"></tbody>
        </table>
    </div>

    <div class="px-4 py-2 border-t bg-white flex justify-between items-center shrink-0">
        <span id="pageInfoJT" class="text-[9px] md:text-[10px] font-bold text-slate-500">0 Data</span>
        <div class="flex gap-2">
            <button id="btnPrevJT" onclick="changePageDetail(-1)" class="px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-[10px] font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-50 transition shadow-sm">« Prev</button>
            <button id="btnNextJT" onclick="changePageDetail(1)" class="px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-[10px] font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-50 transition shadow-sm">Next »</button>
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
  const fmt  = n => nfID.format(Number(n||0));
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
      // 1. Dapatkan Kode User Real
      const user = (window.getUser && window.getUser()) || null;
      userKodeGlobal = (user?.kode ? String(user.kode).padStart(3,'0') : '000');

      // 2. Setup Header Dinamis Rekap (Otomatis menyesuaikan dengan user)
      setupHeaderJT(userKodeGlobal);

      // 3. Fetch Date Setting
      const d = await getLastHarianData(); 
      if(d) {
          document.getElementById('closing_date_jt').value = d.last_closing;
          document.getElementById('harian_date_jt').value  = d.last_created;
          document.getElementById('filter_bulan').value = String(new Date(d.last_created).getMonth() + 1).padStart(2, '0');
      } else {
          document.getElementById('filter_bulan').value = String(new Date().getMonth() + 1).padStart(2, '0');
      }
      document.getElementById('filter_tahun').value = new Date().getFullYear();
      
      // 4. Langsung Load Data (Filter Cabang Dihapus)
      fetchRekapJT();
  });

  async function getLastHarianData(){ 
      try{ const r=await apiCall(API_DATE); const j=await r.json(); return j.data||null; }catch{ return null; } 
  }

  // --- SETUP HEADER UTAMA (Sembunyikan Kolom Kode Jika Login Bukan 000) ---
  function setupHeaderJT(userKode) {
      const th = document.getElementById('headJT');
      let thContent = `<tr>`;

      if (userKode === '000') {
          thContent += `
              <th class="sticky-left-1 w-[60px] border-r border-b border-slate-300 align-middle uppercase text-center hidden md:table-cell text-slate-700 bg-slate-100">Kode</th>
              <th class="sticky-left-2 min-w-[150px] md:min-w-[180px] border-r border-b border-slate-300 align-middle uppercase pl-4 text-slate-700 bg-slate-100">Nama Kantor</th>
          `;
      } else {
          thContent += `
              <th class="sticky-left-1 min-w-[150px] md:min-w-[180px] border-r border-b border-slate-300 align-middle uppercase pl-4 text-slate-700 bg-slate-100">Nama Kantor</th>
          `;
      }

      thContent += `
              <th class="px-3 border-r border-b border-slate-300 align-middle text-right bg-slate-50 text-slate-700">
                  <div class="text-[10px] md:text-[11px] font-bold">NOA</div>
                  <div class="text-[8px] md:text-[9px] text-slate-500 font-normal mt-0.5 font-mono">(Lama)</div>
              </th>
              <th class="px-3 border-r border-b border-slate-300 align-middle text-right bg-slate-50 text-slate-700">
                  <div class="text-[10px] md:text-[11px] font-bold">PLAFON</div>
                  <div class="text-[8px] md:text-[9px] text-slate-500 font-normal mt-0.5 font-mono">(Lama)</div>
              </th>
              <th class="px-3 border-r border-b border-emerald-200 align-middle text-right bg-emerald-50 text-emerald-800">
                  <div class="text-[10px] md:text-[11px] font-bold">NOA</div>
                  <div class="text-[8px] md:text-[9px] text-emerald-600/80 font-normal mt-0.5 font-mono">(Baru)</div>
              </th>
              <th class="px-3 border-r border-b border-emerald-200 align-middle text-right bg-emerald-50 text-emerald-800">
                  <div class="text-[10px] md:text-[11px] font-bold">PLAFON</div>
                  <div class="text-[8px] md:text-[9px] text-emerald-600/80 font-normal mt-0.5 font-mono">(Baru)</div>
              </th>
              <th class="px-3 border-r border-b border-slate-300 align-middle text-right bg-slate-50 text-slate-700">
                  <div class="text-[10px] md:text-[11px] font-bold">SISA BAKI DEBET</div>
              </th>
              <th class="px-3 border-b border-slate-300 align-middle text-right bg-slate-50 text-slate-700">
                  <div class="text-[10px] md:text-[11px] font-bold">% GROWTH</div>
              </th>
          </tr>
          <tr id="rowTotalJTAtas"></tr>
      `;
      th.innerHTML = thContent;
  }

  // --- POPULATE KANKAS MODAL ---
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
      const s=document.getElementById('summaryPills');

      if(abortJT) abortJT.abort(); abortJT = new AbortController();
      l.classList.remove('hidden'); s.classList.add('hidden');
      
      const colSpan = userKodeGlobal === '000' ? 8 : 7;
      tb.innerHTML = `<tr><td colspan="${colSpan}" class="py-16 text-center text-slate-400 italic">...</td></tr>`;
      trTot.innerHTML = '';
      rekapDataCache = null;

      try {
          // Request Cabang disesuaikan otomatis dengan userKodeGlobal (Pusat kirim null)
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
          
          // Double filter frontend untuk memastikan Cabang tidak bisa melihat yang lain
          if (userKodeGlobal !== '000') {
              rows = rows.filter(r => String(r.kode_kantor) === userKodeGlobal);
          }
          
          rekapDataCache = rows;
          renderTableJT(rows, userKodeGlobal);
          
      } catch(err) { if(err.name !== 'AbortError') tb.innerHTML=`<tr><td colspan="${userKodeGlobal === '000' ? 8 : 7}" class="py-12 text-center text-red-500 tracking-widest uppercase font-bold">${err.message}</td></tr>`; } 
      finally { l.classList.add('hidden'); }
  }

  function renderTableJT(rows, userKode) {
      const tb = document.getElementById('bodyJT'); tb.innerHTML = '';
      const trTot = document.getElementById('rowTotalJTAtas'); trTot.innerHTML = '';
      const colSpan = userKode === '000' ? 8 : 7;
      
      if(rows.length === 0){ tb.innerHTML = `<tr><td colspan="${colSpan}" class="py-12 text-center text-slate-400 italic">Tidak ada data.</td></tr>`; return; }

      let T = { noa_lama:0, plafon_lama:0, noa_baru:0, plafon_baru:0, baki_debet:0 };
      let html = '';
      
      rows.forEach(r => {
          T.noa_lama += Number(r.noa_lama); T.plafon_lama += Number(r.plafon_lama);
          T.noa_baru += Number(r.noa_baru); T.plafon_baru += Number(r.plafon_baru); T.baki_debet += Number(r.baki_debet);

          let rowHtml = `<tr class="transition h-[46px] border-b border-slate-100 group" onclick="initModalDetail('${r.kode_kantor}', '${r.nama_kantor}')">`;
          
          if (userKode === '000') {
              rowHtml += `
                <td class="sticky-left-1 px-4 py-1.5 text-center font-mono text-slate-500 hidden md:table-cell shadow-[inset_-1px_0_0_#e2e8f0] z-20">${r.kode_kantor}</td>
                <td class="sticky-left-2 px-4 py-1.5 font-semibold text-slate-700 truncate shadow-[inset_-1px_0_0_#e2e8f0] z-20" title="${r.nama_kantor}">${r.nama_kantor}</td>
              `;
          } else {
              rowHtml += `
                <td class="sticky-left-1 px-4 py-1.5 font-semibold text-slate-700 truncate shadow-[inset_-1px_0_0_#e2e8f0] z-20" title="${r.nama_kantor}">${r.nama_kantor}</td>
              `;
          }

          rowHtml += `
                <td class="px-4 py-1.5 text-right font-bold text-slate-700 border-r border-slate-100">${fmt(r.noa_lama)}</td>
                <td class="px-4 py-1.5 text-right font-mono text-slate-600 border-r border-slate-100">${fmt(r.plafon_lama)}</td>
                <td class="px-4 py-1.5 text-right font-bold text-emerald-700 border-r border-emerald-50 bg-emerald-50/40">${fmt(r.noa_baru)}</td>
                <td class="px-4 py-1.5 text-right font-mono text-emerald-700 border-r border-emerald-50 bg-emerald-50/40">${fmt(r.plafon_baru)}</td>
                <td class="px-4 py-1.5 text-right font-mono text-slate-600 border-r border-slate-100">${fmt(r.baki_debet)}</td>
                <td class="px-4 py-1.5 text-right font-bold ${r.persentase >= 100 ? 'text-emerald-600' : 'text-orange-500'}">${r.persentase}%</td>
            </tr>`;
            
          html += rowHtml;
      });
      tb.innerHTML = html;

      // Inject Grand Total ke Bawah Header
      if(userKode === '000' && rows.length > 0) {
          const gp = (T.plafon_lama > 0) ? (T.plafon_baru / T.plafon_lama * 100) : 0;
          trTot.innerHTML = `
              <th class="sticky-left-1 px-4 border-r border-blue-300 text-center text-blue-900 hidden md:table-cell">-</th>
              <th class="sticky-left-2 px-4 border-r border-blue-300 text-left uppercase tracking-widest font-bold text-blue-900">GRAND TOTAL</th>
              <th class="px-4 border-r border-blue-300 text-right font-bold text-[11px] md:text-xs text-blue-900 align-middle">${fmt(T.noa_lama)}</th>
              <th class="px-4 border-r border-blue-300 text-right font-mono font-bold text-[11px] md:text-xs text-blue-900 align-middle">${fmt(T.plafon_lama)}</th>
              <th class="px-4 border-r border-blue-300 text-right font-bold text-[11px] md:text-xs text-emerald-800 align-middle">${fmt(T.noa_baru)}</th>
              <th class="px-4 border-r border-blue-300 text-right font-mono font-bold text-[11px] md:text-xs text-emerald-800 align-middle">${fmt(T.plafon_baru)}</th>
              <th class="px-4 border-r border-blue-300 text-right font-mono font-bold text-[11px] md:text-xs text-blue-900 align-middle">${fmt(T.baki_debet)}</th>
              <th class="px-4 text-right font-bold text-[11px] md:text-xs ${gp >= 100 ? 'text-emerald-700' : 'text-orange-700'} align-middle">${gp.toFixed(2)}%</th>
          `;
      } else {
          // Jika login sbg Cabang, tidak perlu munculkan grand total karena nilainya sama dengan baris datanya.
          trTot.innerHTML = ``;
      }

      document.getElementById('summaryPills').classList.remove('hidden');
      document.getElementById('sum_noa_lama').textContent = fmt(T.noa_lama);
      document.getElementById('sum_plaf_lama').textContent = fmt(T.plafon_lama);
      document.getElementById('sum_noa_baru').textContent = fmt(T.noa_baru);
      document.getElementById('sum_plaf_baru').textContent = fmt(T.plafon_baru);
      document.getElementById('sum_persen').textContent = ((T.plafon_lama>0)?(T.plafon_baru/T.plafon_lama*100):0).toFixed(2) + '%';
      document.getElementById('sum_bd').textContent = fmt(T.baki_debet);
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
      a.download = `Rekap_JatuhTempo_${document.getElementById("tahun_jt").value}.xls`; 
      a.click();
  }

  // --- MODAL & FILTER LOGIC (DENGAN SECURITY AUTHORIZATION) ---
  function initModalDetail(kode, nama) {
      // Keamanan Akses: Hanya bisa lihat cabangnya sendiri (kecuali Pusat)
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
      
      document.getElementById('modalTitleJT').innerHTML = `<span class="bg-blue-100 text-blue-600 p-1 md:p-1.5 rounded-lg shadow-sm text-xs">👥</span> Detail Nasabah - ${nama}`;
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
              tb.innerHTML = `<tr><td colspan="12" class="py-12 text-center text-slate-400 italic">Tidak ada data detail.</td></tr>`;
              info.innerText = `0 data`;
              return;
          }

          // Sort Terdekat
          list.sort((a, b) => new Date(a.tgl_jatuh_tempo) - new Date(b.tgl_jatuh_tempo));

          let t_plafon_lama = 0, t_plafon_baru = 0, t_sisa_bd = 0;
          let html = '';

          list.forEach(r => {
              t_plafon_lama += parseFloat(r.plafond_lama||0);
              t_plafon_baru += parseFloat(r.plafond_baru||0);
              
              const alamat = r.alamat || '-';
              const hp = r.no_hp || '-';
              const kankas = r.kankas || '-';
              const aoName = (r.nama_ao || '-').split(' ').slice(0, 2).join(' ');

              // PERBAIKAN LOGIKA STATUS (Bug Fixed!)
              let statStr = (r.keterangan_status || '').toUpperCase();
              let isClear = statStr.includes("SUDAH") || statStr === "LUNAS" || statStr === "LUNAS (POTENSI)";
              let isDrop = statStr.includes("DROP");

              if(!isClear) t_sisa_bd += parseFloat(r.baki_debet_lama||0);

              // Badge Clean Elegan
              let badgeClass = "text-slate-600 border-slate-300 bg-slate-50";
              if(statStr.includes("SUDAH")) badgeClass = "text-emerald-700 border-emerald-300 bg-emerald-50/80";
              else if(statStr === "LUNAS" || statStr === "LUNAS (POTENSI)") badgeClass = "text-blue-700 border-blue-300 bg-blue-50/80";
              else if(statStr.includes("TOP UP")) badgeClass = "text-purple-700 border-purple-300 bg-purple-50/80";
              else if(statStr.includes("BELUM")) badgeClass = "text-rose-700 border-rose-300 bg-rose-50/80";

              // Logika Nominal Baru
              let nomBaru = '-';
              if (r.plafond_baru > 0) {
                  nomBaru = `<div class="font-bold text-emerald-700 text-[10px] md:text-[11px]">${fmt(r.plafond_baru)}</div><div class="text-[8px] md:text-[9px] text-emerald-600 font-mono">${r.tgl_realisasi_baru||''}</div>`;
              }

              // Sisa BD 
              let sisaBdVisual = isClear ? '-' : fmt(r.baki_debet_lama);

              // Tgl JT Logic
              const jtDate = new Date(r.tgl_jatuh_tempo);
              const diffDays = Math.ceil((jtDate - actDate) / (1000 * 60 * 60 * 24));
              let strJt = `<div class="font-mono text-slate-700">${r.tgl_jatuh_tempo}</div>`;
              
              if (!isClear) {
                  if (diffDays < 0) strJt += `<div class="text-[8px] md:text-[9px] text-rose-600 font-bold mt-0.5 bg-rose-50 rounded inline-block px-1">Lewat ${Math.abs(diffDays)} Hari</div>`;
                  else if (diffDays === 0) strJt += `<div class="text-[8px] md:text-[9px] text-orange-600 font-bold mt-0.5 bg-orange-50 rounded inline-block px-1">HARI INI!</div>`;
                  else strJt += `<div class="text-[8px] md:text-[9px] text-slate-500 mt-0.5 font-medium">Kurang ${diffDays} Hari</div>`;
              }

              // Aksi Button
              let isLocked = statStr.includes("SUDAH") || isDrop;
              const btnAksi = isLocked 
                  ? `<span class="text-[9px] md:text-[10px] font-bold text-slate-400">LOCKED</span>`
                  : `<button class="bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded text-[9px] md:text-[10px] font-bold shadow-sm transition w-full">PROSPEK</button>`;

              html += `<tr class="transition h-[40px] group border-b border-slate-100">
                    <td class="mod-sticky-1 px-2 py-1.5 font-mono text-[10px] text-slate-500 border-r border-slate-100 shadow-[inset_-1px_0_0_#e2e8f0]">${r.no_rekening_lama}</td>
                    <td class="mod-sticky-2 px-3 py-1.5 font-semibold text-[10px] text-slate-700 truncate border-r border-slate-100 max-w-[160px] md:max-w-[200px] shadow-[inset_-1px_0_0_#e2e8f0]" title="${r.nama_nasabah}">${r.nama_nasabah}</td>
                    <td class="px-3 py-1.5 text-[9px] md:text-[10px] text-slate-500 truncate border-r border-slate-100 max-w-[140px] md:max-w-[180px]" title="${alamat}">${alamat}</td>
                    <td class="px-2 py-1.5 text-center font-mono text-slate-600 text-[10px] border-r border-slate-100">${hp}</td>
                    <td class="px-2 py-1.5 text-center font-mono text-[9px] text-slate-500 border-r border-slate-100">${kankas}</td>
                    
                    <td class="px-3 py-1.5 text-[9px] md:text-[10px] font-bold text-blue-700 truncate border-r border-slate-100">${aoName}</td>
                    <td class="px-3 py-1.5 text-right font-mono text-[10px] text-slate-600 border-r border-slate-100">${fmt(r.plafond_lama)}</td>
                    <td class="px-3 py-1.5 text-center border-r border-slate-100">${strJt}</td>
                    <td class="px-3 py-1.5 text-right font-mono font-bold text-[10px] md:text-[11px] text-slate-800 border-r border-slate-100">${sisaBdVisual}</td>
                    <td class="px-2 py-1.5 text-center border-r border-slate-100"><span class="badge-clean ${badgeClass}">${r.keterangan_status}</span></td>
                    <td class="px-3 py-1.5 text-right bg-emerald-50/30 border-r border-slate-100">${nomBaru}</td>
                    <td class="px-2 py-1.5 text-center">${btnAksi}</td>
                </tr>`;
          });
          tb.innerHTML = html;

          // Inject Total Modal ke Bawah Thead
          trTot.innerHTML = `
              <th class="mod-sticky-1 px-2 border-r border-b border-blue-200 uppercase tracking-widest text-center text-blue-900">-</th>
              <th class="mod-sticky-2 px-3 border-r border-b border-blue-200 uppercase tracking-widest text-blue-900">TOTAL HALAMAN INI</th>
              <th class="px-3 border-r border-b border-blue-200 text-center">-</th>
              <th class="px-2 border-r border-b border-blue-200 text-center">-</th>
              <th class="px-2 border-r border-b border-blue-200 text-center">-</th>
              <th class="px-3 border-r border-b border-blue-200 text-center">-</th>
              <th class="px-3 border-r border-b border-blue-200 text-right font-mono text-blue-900">${fmt(t_plafon_lama)}</th>
              <th class="px-3 border-r border-b border-blue-200 text-center">-</th>
              <th class="px-3 border-r border-b border-blue-300 text-right font-mono text-blue-900 bg-blue-100/50">${fmt(t_sisa_bd)}</th>
              <th class="px-3 border-r border-b border-blue-200 text-center">-</th>
              <th class="px-3 border-r border-b border-blue-200 text-right font-mono text-emerald-800 bg-emerald-50/50">${fmt(t_plafon_baru)}</th>
              <th class="px-2 border-b border-blue-200 text-center">-</th>
          `;

          const start = ((page-1)*detailLimit)+1; const end = Math.min(page*detailLimit, meta.total_records);
          info.innerText = `Hal ${page} / ${meta.total_pages} (${start}-${end} dari ${fmt(meta.total_records)})`;
          
          document.getElementById('btnPrevJT').disabled = page <= 1;
          document.getElementById('btnNextJT').disabled = page >= meta.total_pages;

      } catch(err){ console.error(err); } finally { l.classList.add('hidden'); }
  }

  // --- EXPORT EXCEL ---
  async function downloadExcelJT() {
      const btn = event.target.closest('button'); const txt = btn.innerHTML;
      btn.innerHTML = `<span class="animate-spin inline-block h-3 w-3 border-2 border-white border-t-transparent rounded-full mr-1"></span>...`;
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
</script>