<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
  /* Custom Scrollbar */
  .custom-scrollbar::-webkit-scrollbar { height: 8px; width: 8px; }
  .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
  .no-scrollbar::-webkit-scrollbar { display: none; }
  .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

  /* Animasi Modal */
  @keyframes scaleUp { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
  .animate-scale-up { animation: scaleUp 0.2s ease-out forwards; }

  /* ========================================================
     🔥 MAGIC STICKY TABLE UTAMA (MOB) 🔥
     ======================================================== */
  #tabelMob { border-collapse: separate; border-spacing: 0; }
  #tabelMob th, #tabelMob td { background-clip: padding-box; background-color: #fff; }
  
  #tabelMob thead th { position: sticky !important; z-index: 40; box-shadow: inset 0 -1px 0 #cbd5e1; }
  
  /* Lapis 1 & 2 (Header Utama) */
  .mob-row-1 th { top: 0 !important; height: 40px; background-color: #f8fafc !important; }
  .mob-row-2 th { top: 40px !important; height: 34px; background-color: #f8fafc !important; }
  
  /* Freeze Kolom Kiri Header Utama */
  .mob-row-1 th.sticky-left { z-index: 60 !important; left: 0 !important; box-shadow: inset -1px -1px 0 #cbd5e1; background-color: #dcedc8 !important; border-top-left-radius: 8px; } 
  
  /* Lapis 3 (Grand Total) */
  .mob-row-tot th { top: 74px !important; z-index: 45 !important; height: 42px; box-shadow: inset 0 -2px 0 #93c5fd; background-color: #eff6ff !important; cursor: default; }
  .mob-row-tot th.sticky-left { z-index: 62 !important; left: 0 !important; box-shadow: inset -1px -2px 0 #93c5fd; background-color: #dbeafe !important; }

  @media (min-width: 768px) {
      .mob-row-1 th { height: 46px; }
      .mob-row-2 th { top: 46px !important; height: 38px; }
      .mob-row-tot th { top: 84px !important; height: 50px; }
  }

  /* Freeze Kiri Body Utama */
  #bodyMatrix td { position: relative; z-index: 10 !important; }
  .sticky-left { position: sticky !important; left: 0 !important; }
  #bodyMatrix td.sticky-left { z-index: 30 !important; background-color: #ffffff !important; box-shadow: inset -1px 0 0 #e2e8f0; font-weight: bold; }
  
  /* Hover Effects Utama */
  .cell-hover:hover { background-color: #e0f2fe !important; cursor: pointer; transform: scale(1.03); transition: 0.1s; z-index: 35 !important; position: relative; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border: 1px solid #3b82f6; border-radius: 6px; }
  #bodyMatrix tr:hover td { background-color: #f8fafc !important; }
  #bodyMatrix tr:hover td.sticky-left { background-color: #f8fafc !important; filter: brightness(0.98); }

  /* ========================================================
     🔥 TABEL MODAL DETAIL MOB (FIX FREEZE & OVERLAP) 🔥
     ======================================================== */
  #tableExportMob { border-collapse: separate; border-spacing: 0; }
  #tableExportMob th, #tableExportMob td { background-clip: padding-box; background-color: #fff; }
  
  /* Header Z-Index tinggi */
  #tableExportMob thead th { height: 46px; background-color: #f1f5f9 !important; box-shadow: inset 0 -1px 0 #cbd5e1, 0 1px 0 #cbd5e1; top: 0 !important; position: sticky !important; z-index: 40 !important; }
  @media (min-width: 768px) { #tableExportMob thead th { height: 48px; } }

  /* Body Data Normal nyungsep ke bawah */
  #bodyModalDetail td { position: relative; z-index: 10 !important; }

  /* Kunci Lebar Modal Sticky (Responsif) - Pakai class yang sama untuk TH dan TD */
  .mod-td-rek { position: sticky !important; left: 0 !important; z-index: 30 !important; background-color: #fff !important; box-shadow: inset -1px 0 0 #e2e8f0; min-width: 100px; max-width: 100px;}
  .mod-td-nas { position: sticky !important; left: 0 !important; z-index: 30 !important; background-color: #fff !important; box-shadow: 2px 0 4px -2px rgba(0,0,0,0.1); min-width: 160px; max-width: 160px;}
  .mod-td-nas-lunas { position: sticky !important; left: 0 !important; z-index: 30 !important; background-color: #fff !important; box-shadow: 2px 0 4px -2px rgba(0,0,0,0.1); min-width: 160px; max-width: 160px;}
  
  @media (min-width: 768px) { 
      .mod-td-rek { min-width: 120px; max-width: 120px; }
      /* Di PC, Nasabah geser ke kanan karena ada Rekening di sebelahnya */
      .mod-td-nas { left: 120px !important; min-width: 250px; max-width: 250px; } 
      .mod-td-nas-lunas { left: 0 !important; min-width: 250px; max-width: 250px; }
  }

  /* Perpotongan Header & Kiri (Z-Index Paling Dewa) */
  #tableExportMob thead th.mod-td-rek, 
  #tableExportMob thead th.mod-td-nas, 
  #tableExportMob thead th.mod-td-nas-lunas { 
      z-index: 60 !important; background-color: #e2e8f0 !important; 
  }

  /* Hover Effect Modal Detail */
  #bodyModalDetail tr:hover td { background-color: #f8fafc !important; }
  #bodyModalDetail tr:hover td.mod-td-rek, 
  #bodyModalDetail tr:hover td.mod-td-nas, 
  #bodyModalDetail tr:hover td.mod-td-nas-lunas { filter: brightness(0.98); }

  /* Form Inputs */
  .inp { border:1px solid #cbd5e1; border-radius:6px; padding:0 8px; background:#fff; outline:none; transition: border 0.2s;}
  .inp:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,0.1); }
  .inp:disabled { background-color: #f1f5f9; color: #64748b; font-weight: 700; cursor: not-allowed; }
  .lbl { font-size:9px; color:#475569; font-weight:800; margin-bottom:2px; text-transform:uppercase; letter-spacing:0.05em; display:block; white-space: nowrap;}
  @media (min-width: 768px) { .lbl { font-size:11px; margin-bottom:4px; } .inp { border-radius: 8px; padding:0 12px; } }
  .field { display:flex; flex-direction:column; }
  .btn-icon { display:inline-flex; align-items:center; justify-content:center; border:none; cursor:pointer; transition: transform 0.2s;}
  .btn-icon:hover { transform:translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
  input[type="date"]::-webkit-inner-spin-button, input[type="date"]::-webkit-calendar-picker-indicator { display: none; -webkit-appearance: none; }
  input[type="date"] { -moz-appearance: textfield; }
</style>

<div class="max-w-[1920px] w-full mx-auto px-2 md:px-4 py-4 md:py-6 h-[calc(100vh-60px)] md:h-[calc(100vh-80px)] flex flex-col font-sans text-slate-800 bg-slate-50 overflow-hidden">
  
  <div class="flex-none mb-3 md:mb-4 flex flex-col xl:flex-row justify-between items-start gap-3 md:gap-4 w-full shrink-0">
      
      <div class="flex items-center justify-between w-full xl:w-auto shrink-0">
          <div class="flex flex-col gap-1.5 w-full">
              <h1 class="text-lg md:text-2xl font-bold text-slate-800 flex items-center gap-1.5 md:gap-2 mb-0.5">
                  <span class="p-1 md:p-2.5 bg-blue-600 rounded-lg text-white shadow-sm">
                      <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                  </span>
                  Analisa MOB Vintage
              </h1>
              <p class="text-[9px] md:text-xs text-rose-600 font-bold italic ml-8 md:ml-[42px] leading-tight">
                  *Geser tabel ke kanan untuk data lengkap. Klik nominal untuk detail.
              </p>
          </div>
          
          <button type="button" onclick="toggleFilter('filterWrapperMob')" class="xl:hidden h-[30px] px-3 bg-white border border-slate-200 text-slate-700 rounded-lg flex items-center gap-1.5 shadow-sm transition font-bold text-[10px] md:text-xs whitespace-nowrap ml-2 shrink-0">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
              Filter
          </button>
      </div>

      <div id="filterWrapperMob" class="hidden xl:flex w-full xl:w-auto mt-2 xl:mt-0 transition-all duration-300 shrink-0 xl:ml-auto">
          <form id="formFilterMob" class="flex flex-row items-end gap-1.5 md:gap-2 bg-white p-2 md:p-2.5 rounded-lg md:rounded-xl border border-slate-200 shadow-sm w-full overflow-x-auto no-scrollbar" onsubmit="event.preventDefault(); fetchRekapMob();">
              
              <div class="field shrink min-w-[100px] md:min-w-[140px]">
                  <label class="lbl text-blue-700">POSISI DATA</label>
                  <input type="date" id="harian_date_mob" class="inp w-full text-[10px] md:text-sm font-semibold h-[30px] md:h-[38px] px-1 md:px-3 cursor-pointer" required onclick="try{this.showPicker()}catch(e){}">
              </div>
              
              <div class="w-px h-6 bg-slate-200 shrink-0 mx-0.5 hidden md:block mb-1.5"></div>

              <div class="field flex-1 shrink min-w-[120px] md:min-w-[200px]">
                  <label class="lbl text-slate-600">CABANG</label>
                  <select id="opt_kantor_mob" class="inp text-[10px] md:text-sm font-semibold h-[30px] md:h-[38px] px-1 md:px-2 truncate cursor-pointer w-full" onchange="fetchRekapMob()">
                      <option>Loading...</option>
                  </select>
              </div>
              
              <div class="flex items-center gap-1 md:gap-1.5 shrink-0 h-[30px] md:h-[38px] mb-px">
                  <button type="submit" class="btn-icon h-full px-3 md:px-5 bg-blue-600 hover:bg-blue-700 text-white rounded-md md:rounded-lg shadow-sm text-[10px] md:text-sm font-bold uppercase tracking-wider" title="Cari Data">
                      <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" class="md:mr-1.5 md:w-[16px] md:h-[16px]"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                      <span class="hidden md:inline">CARI</span>
                  </button>
                  <button type="button" onclick="exportExcelRekapMob()" class="btn-icon h-full w-[36px] md:w-[42px] bg-emerald-600 hover:bg-emerald-700 text-white rounded-md md:rounded-lg shadow-sm" title="Download Excel">
                      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="md:w-[18px] md:h-[18px]"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></line></svg>
                  </button>
              </div>
          </form>
      </div>

  </div>

  <div class="flex-1 min-h-0 overflow-hidden bg-white rounded-xl shadow-sm border border-slate-200 relative flex flex-col">
    <div id="loadingMob" class="hidden absolute inset-0 bg-white/80 z-[100] flex flex-col items-center justify-center text-blue-600 font-bold uppercase tracking-widest text-[10px] md:text-sm backdrop-blur-sm">
        <div class="animate-spin h-8 w-8 md:h-10 md:w-10 border-4 border-blue-200 border-t-blue-600 rounded-full mb-2 md:mb-3"></div>
        <span>Menyiapkan Matriks...</span>
    </div>
    
    <div class="flex-1 w-full h-full overflow-auto custom-scrollbar relative">
      <table class="w-max min-w-full text-center border-separate border-spacing-0 text-slate-700 table-fixed" id="tabelMob">
        <thead class="text-slate-800 font-bold tracking-wider text-[9px] md:text-xs select-none">
          <tr class="mob-row-1">
            <th rowspan="2" class="sticky-left px-3 text-left min-w-[90px] md:min-w-[130px] uppercase align-middle border-r border-slate-200 text-blue-900">Bulan Real</th>
            <th rowspan="2" class="px-2 py-2 border-r border-slate-200 bg-[#f8fafc] text-blue-800 align-middle">MOB</th>
            <th rowspan="2" class="px-3 md:px-4 py-2 border-r border-slate-200 bg-[#f8fafc] text-blue-800 text-right w-[140px] md:w-[180px] align-middle">Tot Plafond</th>
            <th colspan="8" class="py-2 border-b border-slate-200 bg-slate-100 text-slate-700 uppercase tracking-widest">DPD (Days Past Due) / Ember</th>
          </tr>
          <tr class="mob-row-2 text-[9px] md:text-[11px]">
            <th class="px-2 py-1.5 border-r border-slate-200 bg-[#f0fdf4] text-green-800 w-[110px] md:w-[140px]">0</th>
            <th class="px-2 py-1.5 border-r border-slate-200 bg-[#fefce8] text-yellow-800 w-[110px] md:w-[140px]">1 - 7</th>
            <th class="px-2 py-1.5 border-r border-slate-200 bg-[#fefce8] text-yellow-800 w-[110px] md:w-[140px]">8 - 14</th>
            <th class="px-2 py-1.5 border-r border-slate-200 bg-[#fef9c3] text-yellow-800 w-[110px] md:w-[140px]">15 - 21</th>
            <th class="px-2 py-1.5 border-r border-slate-200 bg-[#fff7ed] text-orange-800 w-[110px] md:w-[140px]">22 - 30</th>
            <th class="px-2 py-1.5 border-r border-slate-200 bg-[#ffedd5] text-orange-800 w-[110px] md:w-[140px]">31 - 60</th>
            <th class="px-2 py-1.5 border-r border-slate-200 bg-[#fef2f2] text-red-800 w-[110px] md:w-[140px]">61 - 90</th>
            <th class="px-2 py-1.5 border-slate-200 bg-[#fee2e2] text-red-900 w-[110px] md:w-[140px]">&gt; 90</th>
          </tr>
          <tr id="rowTotalMobAtas" class="mob-row-tot text-[10px] md:text-sm font-extrabold tracking-wide"></tr>
        </thead>
        <tbody id="bodyMatrix" class="divide-y divide-slate-100 bg-white text-[10px] md:text-sm"></tbody>
      </table>
    </div>
  </div>
</div>

<div id="modalDetailMob" class="fixed inset-0 hidden z-[9999] flex items-end md:items-center justify-center p-0 md:p-4">
  <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModalMob()"></div>
  <div class="relative bg-white w-full h-[95vh] md:h-[92vh] max-w-[1700px] rounded-t-xl md:rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-scale-up">
    
    <div class="flex flex-col bg-white border-b shrink-0 w-full z-50">
        <div class="flex flex-row items-center justify-between px-3 py-2.5 md:px-4 md:py-3 gap-2 w-full overflow-x-auto no-scrollbar">
            
            <div class="flex-1 min-w-[180px] shrink-0">
                <h3 class="font-bold text-slate-800 flex items-center gap-1.5 md:gap-2 text-[12px] md:text-xl leading-none">
                    <span class="w-1.5 md:w-2 h-4 md:h-6 bg-blue-600 rounded-full hidden md:block"></span> 
                    <span class="truncate">Detail Debitur MOB</span> 
                    <span id="badgeBucketDetail" class="text-[9px] md:text-sm bg-blue-600 text-white px-2 py-0.5 md:px-2.5 rounded-md md:rounded-full shadow-sm ml-1 font-mono shrink-0">Bucket ?</span>
                </h3>
                <p class="text-[9px] md:text-[11px] text-slate-500 mt-1 md:ml-4 font-mono font-medium leading-none truncate" id="subTitleDetail">Loading...</p>
            </div>
            
            <div class="flex flex-row items-center gap-1.5 md:gap-2 shrink-0 ml-auto">
                <div class="relative w-[130px] md:w-[200px] shrink-0">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" id="search_nasabah" onkeyup="filterTableDetail()" class="w-full pl-8 pr-3 py-1.5 h-[30px] md:h-[34px] bg-slate-50 border border-slate-200 rounded-lg text-[10px] md:text-xs outline-none focus:border-blue-500 focus:bg-white transition-all placeholder-slate-400 font-medium" placeholder="Cari nama...">
                </div>
                
                <button type="button" onclick="toggleFilter('modalFilterWrapper')" class="md:hidden h-[30px] w-[32px] bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 rounded-lg flex items-center justify-center transition shrink-0" title="Filter Lanjutan">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                </button>
                
                <button onclick="closeModalMob()" class="w-[30px] h-[30px] md:w-[34px] md:h-[34px] flex items-center justify-center rounded-lg bg-red-50 hover:bg-red-500 hover:text-white text-red-500 transition font-bold text-lg md:text-xl leading-none shrink-0">&times;</button>
            </div>
        </div>

        <div id="modalFilterWrapper" class="hidden md:flex flex-row items-center justify-end gap-1.5 md:gap-2 px-3 pb-2.5 md:px-4 md:pb-3 w-full bg-white overflow-x-auto no-scrollbar transition-all border-t border-slate-100 md:border-none">
            <select id="opt_kankas_modal" class="inp px-1 md:px-2 h-[30px] md:h-[34px] w-[95px] md:w-[130px] text-[10px] md:text-xs font-bold text-blue-800 bg-blue-50/50 border-blue-200 outline-none shrink-0 cursor-pointer" onchange="fetchDetailMob()">
                <option value="">Kankas</option>
            </select>
            
            <button onclick="exportExcelDetailMob()" class="btn-icon bg-emerald-600 hover:bg-emerald-700 text-white px-2.5 md:px-3 h-[30px] md:h-[34px] rounded-lg shadow-sm shrink-0 flex items-center justify-center gap-1.5 ml-auto md:ml-0">
                <svg class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span class="text-[10px] md:text-xs font-bold uppercase tracking-wider hidden sm:inline">Export</span>
            </button>
        </div>
    </div>

    <div class="flex-1 overflow-auto bg-slate-50 relative p-0 md:p-3 custom-scrollbar">
        <div id="loadingModal" class="hidden absolute inset-0 bg-white/90 z-40 flex flex-col items-center justify-center text-blue-600 backdrop-blur-sm">
            <div class="animate-spin rounded-full h-8 w-8 md:h-10 md:w-10 border-4 border-blue-500 border-t-transparent mb-2 md:mb-3"></div>
            <span class="text-[10px] md:text-sm font-bold uppercase tracking-widest">Memuat Detail...</span>
        </div>
        
        <table class="w-max min-w-full text-center md:text-left text-slate-700 border-separate border-spacing-0 md:border border-slate-200 md:rounded-xl shadow-sm bg-white table-fixed" id="tableExportMob">
            <thead id="headModalMigrasi" class="bg-slate-100 text-slate-600 font-extrabold select-none text-[9px] md:text-xs uppercase tracking-wider">
                </thead>
            <tbody id="bodyModalDetail" class="divide-y divide-slate-100 bg-white text-[9.5px] md:text-[12px]"></tbody>
        </table>
    </div>

    <div class="px-3 py-2.5 md:px-6 md:py-4 border-t bg-white flex justify-between items-center shrink-0">
        <span id="pageInfoDetail" class="text-[9px] md:text-xs font-bold text-slate-600 bg-slate-100 px-2 md:px-3 py-1 rounded-md md:rounded-lg">0 Data</span>
        <div class="flex gap-1.5 md:gap-2">
            <button id="btnPrevDetail" onclick="changePageDetailMob(-1)" class="px-3 md:px-4 py-1.5 md:py-2 bg-white border border-slate-300 rounded-md md:rounded-lg text-[9px] md:text-sm font-bold text-slate-600 hover:bg-slate-50 hover:border-slate-400 disabled:opacity-50 transition shadow-sm">« Prev</button>
            <button id="btnNextDetail" onclick="changePageDetailMob(1)" class="px-3 md:px-4 py-1.5 md:py-2 bg-white border border-slate-300 rounded-md md:rounded-lg text-[9px] md:text-sm font-bold text-slate-600 hover:bg-slate-50 hover:border-slate-400 disabled:opacity-50 transition shadow-sm">Next »</button>
        </div>
    </div>
  </div>
</div>

<script>
// --- CONFIG ---
const API_URL = './api/kredit/'; 
const API_KODE = './api/kode/';
const API_DATE = './api/date/'; 
const nfID = new Intl.NumberFormat('id-ID');

// 🔥 FORMAT MURNI (Nggak dibagi 1000 lagi) 🔥
const fmt = n => nfID.format(Math.round(Number(n||0)));

const apiCall = (url, opt={}) => (window.apiFetch ? window.apiFetch(url,opt) : fetch(url,opt));

let abortMainMob;
let detailParamsMob = {}; 
let detailPageMob = 1;
const optKantorMob = document.getElementById('opt_kantor_mob');
let rekapDataCacheMob = null; 

// 🔥 FUNGSI TOGGLE FILTER (BISA DIPAKAI MAIN & MODAL) 🔥
function toggleFilter(id) {
    const el = document.getElementById(id);
    if(el.classList.contains('hidden')) {
        el.classList.remove('hidden');
        el.classList.add('flex');
    } else {
        el.classList.add('hidden');
        el.classList.remove('flex');
    }
}

// --- INIT ---
window.addEventListener('DOMContentLoaded', async () => {
    const user = (window.getUser && window.getUser()) || null;
    const userKode = (user?.kode ? String(user.kode).padStart(3,'0') : '000');

    await populateKantorOptionsMob(userKode);
    await loadKankasModalDropdownMob();

    const d = await getLastHarianData(); 
    document.getElementById('harian_date_mob').value = d ? d.last_created : new Date().toISOString().split('T')[0];

    fetchRekapMob();
});

async function getLastHarianData(){
    try{ const r=await apiCall(API_DATE); return (await r.json()).data; }
    catch{ return null; }
}

async function populateKantorOptionsMob(userKode){
    if(userKode !== '000'){
        optKantorMob.innerHTML = `<option value="${userKode}">CABANG ${userKode}</option>`;
        optKantorMob.value = userKode;
        optKantorMob.disabled = true;
        return;
    }
    try {
        const res = await apiCall(API_KODE, { 
            method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({type:'kode_kantor'}) 
        });
        const json = await res.json();
        const list = Array.isArray(json.data) ? json.data : [];
        let html = `<option value="">KONSOLIDASI (SEMUA)</option>`;
        list.filter(x => x.kode_kantor && x.kode_kantor !== '000')
            .sort((a,b) => String(a.kode_kantor).localeCompare(b.kode_kantor))
            .forEach(it => {
               html += `<option value="${String(it.kode_kantor).padStart(3,'0')}">${String(it.kode_kantor).padStart(3,'0')} - ${it.nama_kantor}</option>`;
            });
        optKantorMob.innerHTML = html;
        optKantorMob.disabled = false;
    } catch(e){ optKantorMob.innerHTML = `<option value="">Error Load</option>`; }
}

async function loadKankasModalDropdownMob() {
    const elKankas = document.getElementById('opt_kankas_modal');
    const branch = optKantorMob.value;
    elKankas.innerHTML = '<option value="">Semua Kankas</option>';
    if(!branch || branch === '') return;

    try {
        const r = await apiCall(API_KODE, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({type: 'kode_kankas', kode_kantor: branch}) });
        const j = await r.json();
        let h = '<option value="">Semua Kankas</option>';
        if(j.data && Array.isArray(j.data)) {
            j.data.forEach(x => { h += `<option value="${x.kode_group1}">${x.deskripsi_group1 || x.kode_group1}</option>`; });
        }
        elKankas.innerHTML = h;
    } catch(err) { }
}

optKantorMob.addEventListener('change', () => { loadKankasModalDropdownMob(); });

// --- 1. FETCH REKAP MOB ---
async function fetchRekapMob(){
    const loading = document.getElementById('loadingMob');
    const tbody  = document.getElementById('bodyMatrix');
    const harian  = document.getElementById('harian_date_mob').value;
    const kode    = optKantorMob.value || null; 

    if(abortMainMob) abortMainMob.abort();
    abortMainMob = new AbortController();

    loading.classList.remove('hidden');
    tbody.innerHTML = `<tr><td colspan="11" class="py-20 text-center text-slate-400 italic text-[10px] md:text-sm">Sedang mengambil data...</td></tr>`;
    rekapDataCacheMob = null;

    try {
        const payload = { 
            type: "mob_vintage",
            harian_date: harian,
            kode_kantor: kode
        };
        
        const res = await apiCall(API_URL, {
            method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload), signal: abortMainMob.signal
        });
        const json = await res.json();
        
        if(json.status !== 200) throw new Error(json.message);

        const rawData = json.data.data || [];
        const bucketsKey = json.data.buckets_order || ['0', '1 - 7', '8 - 14', '15 - 21', '22 - 30', '31 - 60', '61 - 90', '> 90'];

        if(rawData.length === 0){
            tbody.innerHTML = `<tr><td colspan="11" class="py-20 text-center text-slate-400 italic text-[10px] md:text-sm">Tidak ada data.</td></tr>`;
            document.getElementById('rowTotalMobAtas').innerHTML = '';
            return;
        }

        // --- AGGREGATION LOGIC (HILANGKAN CABANG, GABUNG SEMUA) ---
        let displayData = [];
        
        if (!kode) { // KONSOLIDASI
            const grouped = {};
            rawData.forEach(row => {
                const key = row.bulan_realisasi;
                if (!grouped[key]) {
                    grouped[key] = { bulan_realisasi: key, mob: row.mob, total_plafond: 0, buckets: {} };
                    bucketsKey.forEach(b => grouped[key].buckets[b] = { os: 0, noa: 0, pct: 0 });
                }
                grouped[key].total_plafond += parseFloat(row.total_plafond || 0);
                bucketsKey.forEach(b => {
                    const srcBucket = row.buckets[b] || { os:0, noa:0 };
                    grouped[key].buckets[b].os  += parseFloat(srcBucket.os || 0);
                    grouped[key].buckets[b].noa += parseInt(srcBucket.noa || 0);
                });
            });
            displayData = Object.values(grouped).sort((a,b) => b.bulan_realisasi.localeCompare(a.bulan_realisasi)); // Sort Descending Bulan
        } else {
            // Meskipun per cabang, kita buang visual cabangnya
            displayData = rawData.sort((a,b) => b.bulan_realisasi.localeCompare(a.bulan_realisasi));
        }

        // Hitung Ulang % (Semua Kondisi)
        displayData.forEach(row => {
            const pembagi = parseFloat(row.total_plafond) > 0 ? parseFloat(row.total_plafond) : 1;
            bucketsKey.forEach(b => {
                if(!row.buckets[b]) row.buckets[b] = { os:0, noa:0, pct:0 }; 
                row.buckets[b].pct = ((parseFloat(row.buckets[b].os) / pembagi) * 100).toFixed(2);
            });
        });

        rekapDataCacheMob = { data: displayData, buckets: bucketsKey };

        let html = '';
        let grandTotal = { plafond: 0, buckets: {} };
        bucketsKey.forEach(b => grandTotal.buckets[b] = { os:0, noa:0 });

        displayData.forEach(r => {
            grandTotal.plafond += parseFloat(r.total_plafond || 0);
            let cells = '';
            
            bucketsKey.forEach(key => {
                const bData = r.buckets[key] || { pct:0, noa:0, os:0 };
                grandTotal.buckets[key].os  += parseFloat(bData.os || 0);
                grandTotal.buckets[key].noa += parseInt(bData.noa || 0);

                let bgClass = 'bg-transparent'; let textClass = 'text-slate-800';
                if(key !== '0' && parseFloat(bData.pct) > 0) { bgClass = 'bg-red-50/70 border-red-100'; textClass = 'text-red-700'; }
                if(key === '0' && parseFloat(bData.pct) > 90) { bgClass = 'bg-emerald-50/70 border-emerald-100'; textClass = 'text-emerald-700'; }

                const cabangParam = (!kode) ? '' : r.kode_cabang;
                const clickEv = (parseFloat(bData.os) > 0) ? `onclick="openModalMob('${cabangParam}', '${r.bulan_realisasi}', '${key}')"` : '';
                const cursor = (parseFloat(bData.os) > 0) ? 'cell-hover' : '';

                // 🔥 OS DAN NOA DIGABUNG (Angka Utuh, Murni) 🔥
                cells += `
                    <td class="px-2 md:px-3 py-1.5 md:py-2 border-r border-slate-200 align-middle ${bgClass}">
                        <div class="flex flex-col justify-center h-full ${cursor} transition px-1" ${clickEv}>
                            <div class="font-bold text-[10px] md:text-xs ${textClass} leading-tight mb-0.5">${parseFloat(bData.os)>0 ? fmt(bData.os) : '-'}</div>
                            <div class="text-[8px] md:text-[9.5px] text-slate-500 font-medium leading-tight">NOA: <span class="font-bold text-slate-700">${bData.noa}</span> <span class="mx-0.5 opacity-50">|</span> <span class="font-bold ${textClass}">${bData.pct}%</span></div>
                        </div>
                    </td>`;
            });

            html += `
                <tr class="hover:bg-slate-50 border-b border-slate-200 group h-[52px] md:h-[60px]">
                    <td class="sticky-left px-3 md:px-4 py-2 text-left font-bold text-[10.5px] md:text-sm text-slate-700 bg-white border-r border-slate-200 align-middle shadow-[inset_-1px_0_0_#e2e8f0] z-10 min-w-[90px] md:min-w-[130px] truncate">${r.bulan_realisasi}</td>
                    <td class="px-2 md:px-3 py-2 border-r border-blue-200 text-center font-bold text-[10.5px] md:text-sm text-blue-700 bg-blue-50/30 align-middle">${r.mob}</td>
                    <td class="px-3 md:px-4 py-2 border-r border-blue-200 text-right font-mono font-bold text-[11px] md:text-sm text-blue-800 bg-blue-50/10 align-middle leading-tight">${fmt(r.total_plafond)}</td>
                    ${cells}
                </tr>`;
        });
        tbody.innerHTML = html;

        // --- RENDER TOTAL STICKY (DI BAWAH THEAD) ---
        let tf = `<th class="sticky-left px-3 md:px-4 text-left uppercase tracking-widest align-middle text-blue-900 z-50 bg-[#eff6ff] text-[10px] md:text-xs shadow-[inset_-1px_0_0_#93c5fd]">TOTAL</th>
                  <th class="border-r border-blue-300 px-2 md:px-3 text-center align-middle text-blue-900 bg-[#eff6ff]">-</th>
                  <th class="border-r border-blue-300 px-3 md:px-4 text-right font-mono font-bold text-[11px] md:text-[14px] text-blue-900 align-middle bg-[#eff6ff] leading-tight">${fmt(grandTotal.plafond)}</th>`;
        
        bucketsKey.forEach(b => { 
            const bTot = grandTotal.buckets[b];
            const pembagiTotal = grandTotal.plafond > 0 ? grandTotal.plafond : 1;
            const pctTotal = ((bTot.os / pembagiTotal) * 100).toFixed(2);
            tf += `<th class="border-r border-blue-300 align-middle bg-[#eff6ff] px-2 md:px-3">
                      <div class="flex flex-col justify-center h-full py-1.5 md:py-2">
                          <div class="text-[10px] md:text-xs text-blue-900 font-bold leading-tight mb-0.5">${fmt(bTot.os)}</div>
                          <div class="text-[8px] md:text-[9.5px] text-blue-600 font-medium leading-tight">NOA: <span class="font-bold text-blue-800">${bTot.noa}</span> <span class="mx-0.5 opacity-50">|</span> <span class="font-bold">${pctTotal}%</span></div>
                      </div>
                   </th>` 
        });
        document.getElementById('rowTotalMobAtas').innerHTML = tf;

    } catch(err) {
        if(err.name !== 'AbortError') tbody.innerHTML = `<tr><td colspan="11" class="py-16 text-center text-red-500 font-bold tracking-widest uppercase text-[10px] md:text-sm">Error: ${err.message}</td></tr>`;
    } finally {
        loading.classList.add('hidden');
    }
}

// EXPORT EXCEL REKAP
window.exportExcelRekapMob = function() {
    if(!rekapDataCacheMob || !rekapDataCacheMob.data) return alert("Tidak ada data rekap untuk didownload.");

    const rows = rekapDataCacheMob.data;
    const bk = rekapDataCacheMob.buckets;
    
    let csv = "Bulan Realisasi\tMOB\tTotal Plafond\t";
    bk.forEach(b => csv += `% ${b}\tOS ${b}\tNOA ${b}\t`);
    csv += "\n";

    rows.forEach(r => {
        csv += `'${r.bulan_realisasi}\t${r.mob}\t${Math.round(r.total_plafond)}\t`;
        bk.forEach(b => {
            const d = r.buckets[b];
            csv += `${d.pct}%\t${Math.round(d.os)}\t${d.noa}\t`;
        });
        csv += "\n";
    });

    const blob = new Blob([csv], { type: 'application/vnd.ms-excel' });
    const a = document.createElement('a');
    a.href = window.URL.createObjectURL(blob);
    a.download = `Rekap_MOB_Vintage_${document.getElementById("harian_date_mob").value}.xls`; 
    a.click();
}

// --- 2. MODAL DETAIL LOGIC ---
async function openModalMob(cabang, bulan, bucket){
    detailParamsMob = {
        type: "detail_mob_debitur",
        harian_date: document.getElementById('harian_date_mob').value,
        kode_kantor: cabang, 
        bulan_realisasi: bulan,
        bucket_label: bucket
    };
    detailPageMob = 1;

    document.getElementById('modalDetailMob').classList.remove('hidden');
    document.getElementById('badgeBucketDetail').innerText = `Bucket ${bucket}`;
    const txtCabang = cabang ? `Cabang ${cabang}` : "SEMUA CABANG";
    document.getElementById('subTitleDetail').innerText = `${txtCabang} • Real ${bulan}`;
    
    document.getElementById('search_nasabah').value = '';
    
    renderModalHeaderMigrasi();
    fetchDetailMob();
}

function renderModalHeaderMigrasi() {
    const mHead = document.getElementById('headModalMigrasi');
    
    // Terapkan Class yang solid dan ber-Z-Index tinggi
    mHead.innerHTML = `
        <tr>
            <th class="mod-td-rek hidden md:table-cell px-2 md:px-3 py-1.5 md:py-2 border-b border-r border-slate-300 rounded-tl-xl text-blue-900 text-left md:text-center">Rekening</th>
            <th class="mod-td-nas px-2 md:px-4 py-1.5 md:py-2 border-b border-r border-slate-300 text-blue-900 text-left md:text-center">Nama Nasabah</th>
            <th class="px-2 md:px-4 py-1.5 md:py-2 border-b border-r border-slate-300 w-[180px] md:w-[250px] text-center">Alamat</th>
            <th class="px-2 md:px-3 py-1.5 md:py-2 border-b border-r border-slate-300 w-[110px] md:w-[130px] text-center">No HP (WA)</th>
            <th class="px-2 md:px-3 py-1.5 md:py-2 border-b border-r border-slate-300 w-[100px] md:w-[120px] text-center">Kankas</th>
            <th class="px-2 md:px-4 py-1.5 md:py-2 border-b border-r border-slate-300 w-[110px] md:w-[140px] text-center text-blue-700">Tgl Realisasi</th>
            <th class="px-2 md:px-4 py-1.5 md:py-2 border-b border-r border-slate-300 w-[110px] md:w-[140px] text-right text-blue-700">Plafond</th>
            <th class="px-2 md:px-4 py-1.5 md:py-2 border-b border-r border-green-200 w-[110px] md:w-[140px] text-right bg-green-50 text-green-700">OS Current</th>
            <th class="px-2 md:px-3 py-1.5 md:py-2 border-b border-r border-slate-300 w-[50px] md:w-[60px] text-center">Kol</th>
            <th class="px-2 md:px-4 py-1.5 md:py-2 border-b border-r border-red-200 w-[100px] md:w-[130px] text-right bg-red-50 text-red-800">Tot Tunggakan</th>
            <th class="px-2 md:px-3 py-1.5 md:py-2 border-b border-r border-orange-200 w-[70px] md:w-[80px] text-center bg-orange-50 text-orange-800">HM PK</th>
            <th class="px-2 md:px-3 py-1.5 md:py-2 border-b border-r border-orange-200 w-[70px] md:w-[80px] text-center bg-orange-50 text-orange-800">HM BG</th>
            <th class="px-2 md:px-4 py-1.5 md:py-2 border-b border-r border-green-200 w-[100px] md:w-[120px] text-center bg-green-50 text-green-800">Tgl Trans</th>
            <th class="px-2 md:px-4 py-1.5 md:py-2 border-b border-r border-green-200 w-[110px] md:w-[140px] text-right bg-green-50 text-green-800">Total Bayar</th>
            <th class="px-2 md:px-4 py-1.5 md:py-2 border-b border-r border-slate-300 w-[110px] md:w-[140px] text-right">Tabungan</th>
            <th class="px-2 md:px-3 py-1.5 md:py-2 border-b border-slate-200 w-[90px] md:w-[100px] text-center">Stat Tab</th>
        </tr>
    `;
}

// Live Search Nasabah Detail MOB
window.filterTableDetail = function() {
    const input = document.getElementById("search_nasabah");
    const filter = input.value.toLowerCase();
    const tbody = document.getElementById("bodyModalDetail");
    const trs = tbody.getElementsByTagName("tr");

    for (let i = 0; i < trs.length; i++) {
        // Td index 1 adalah Nama Nasabah di modal MOB (karena Rekening di-hidden di Mobile, tp di DOM tetep urutan ke-1 (index 1))
        const tdName = trs[i].getElementsByTagName("td")[1];
        if (tdName) {
            const txtValue = tdName.textContent || tdName.innerText;
            if (txtValue.toLowerCase().indexOf(filter) > -1) {
                trs[i].style.display = "";
            } else {
                trs[i].style.display = "none";
            }
        }
    }
}

async function fetchDetailMob(){
    const loader = document.getElementById('loadingModal');
    const tbody  = document.getElementById('bodyModalDetail');
    const info   = document.getElementById('pageInfoDetail');
    const btnPrev = document.getElementById('btnPrevDetail');
    const btnNext = document.getElementById('btnNextDetail');

    loader.classList.remove('hidden');
    tbody.innerHTML = '';

    try {
        const kankasModal = document.getElementById('opt_kankas_modal').value;
        const payload = { ...detailParamsMob, kode_kankas: kankasModal, page: detailPageMob };
        
        const res = await apiCall(API_URL, {
            method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)
        });
        const json = await res.json();
        
        if(json.status !== 200) throw new Error(json.message);

        const list = json.data?.data || [];
        const totalRecords = json.data?.total_records || 0;
        const totalPages   = json.data?.total_pages || 1;

        if(list.length === 0){
            tbody.innerHTML = `<tr><td colspan="16" class="py-20 text-center text-slate-400 italic text-[10px] md:text-sm">Tidak ada data detail.</td></tr>`;
            info.innerText = `0 Data`;
            btnPrev.disabled = true; btnNext.disabled = true;
            return;
        }

        let html = '';
        list.forEach(row => {
            // 🔥 WA DIRECT POLOSAN 🔥
            const textHp = createWABtn(row.no_hp);

            // LOGIKA TABUNGAN: Aman jika Tabungan >= 1.5 * Totung
            let statTabungan = `<span class="text-red-500 font-bold text-[9px] md:text-xs">Belum Aman</span>`;
            if(parseFloat(row.tabungan) >= (1.5 * parseFloat(row.totung))) {
                statTabungan = `<span class="text-green-600 font-bold text-[9px] md:text-xs">Aman</span>`;
            }

            // 🔥 Pemotongan Alamat max 25 karakter pakai JS
            let alamatLengkap = row.alamat || '-';
            let alamatPendek = alamatLengkap.length > 25 ? alamatLengkap.substring(0, 25) + '...' : alamatLengkap;

            // 🔥 NOMINAL DETAIL TETAP ASLI (MURNI), TIDAK DIBAGI 1000 🔥
            // 🔥 KUNCI FREEZE MODAL DI BODY (class mod-td-rek & mod-td-nas) 🔥
            html += `
                <tr class="hover:bg-slate-50/50 border-b border-slate-100 transition h-[40px] md:h-[48px] group">
                    <td class="mod-td-rek hidden md:table-cell px-2 md:px-3 py-1.5 md:py-2 font-mono text-[9.5px] md:text-[11px] text-slate-500 border-r border-slate-100">${row.no_rekening}</td>
                    <td class="mod-td-nas px-2 md:px-4 py-1.5 md:py-2 font-bold text-[9.5px] md:text-[11px] text-slate-700 truncate border-r border-slate-100" title="${row.nama_nasabah}">${row.nama_nasabah}</td>
                    <td class="px-2 md:px-4 py-1.5 md:py-2 text-slate-500 text-[9.5px] md:text-[11px] border-r border-slate-100 whitespace-nowrap text-center" title="${alamatLengkap}">${alamatPendek}</td>
                    <td class="px-2 md:px-3 py-1.5 md:py-2 text-center border-r border-slate-100">${textHp}</td>
                    <td class="px-2 md:px-3 py-1.5 md:py-2 text-center font-mono text-[9px] md:text-[11px] text-slate-500 border-r border-slate-100">${row.kankas||'-'}</td>
                    <td class="px-2 md:px-4 py-1.5 md:py-2 text-center font-mono text-[9.5px] md:text-[11px] text-blue-700 bg-blue-50/30 border-r border-blue-100">${row.tgl_realisasi}</td>
                    <td class="px-2 md:px-4 py-1.5 md:py-2 text-right font-medium text-[9.5px] md:text-[12px] text-slate-500 border-r border-slate-100">${fmt(row.plafond)}</td>
                    <td class="px-2 md:px-4 py-1.5 md:py-2 text-right font-mono font-bold text-[9.5px] md:text-[13px] text-blue-700 border-r border-slate-100 bg-slate-50/50">${fmt(row.os)}</td>
                    <td class="px-2 md:px-3 py-1.5 md:py-2 text-center font-bold text-[9.5px] md:text-sm text-slate-600 border-r border-slate-100">${row.kolektibilitas||'-'}</td>
                    <td class="px-2 md:px-4 py-1.5 md:py-2 text-right font-mono font-bold text-[9.5px] md:text-sm text-red-600 bg-red-50/30 border-r border-red-100">${fmt(row.totung)}</td>
                    <td class="px-2 md:px-3 py-1.5 md:py-2 text-center font-mono text-[9.5px] md:text-sm text-orange-700 bg-orange-50/30 border-r border-orange-100">${row.hari_menunggak_pokok}</td>
                    <td class="px-2 md:px-3 py-1.5 md:py-2 text-center font-mono text-[9.5px] md:text-sm text-orange-700 bg-orange-50/30 border-r border-orange-100">${row.hari_menunggak_bunga}</td>
                    <td class="px-2 md:px-4 py-1.5 md:py-2 text-center font-mono text-[9.5px] md:text-[11px] text-green-700 bg-green-50/30 border-r border-green-100">${row.tgl_trans || '-'}</td>
                    <td class="px-2 md:px-4 py-1.5 md:py-2 text-right font-mono font-bold text-[9.5px] md:text-[12px] text-green-700 bg-green-50/30 border-r border-green-100">${fmt(row.transaksi)}</td>
                    <td class="px-2 md:px-4 py-1.5 md:py-2 text-right font-mono font-bold text-[9.5px] md:text-[12px] text-emerald-600 bg-emerald-50/10 border-r border-slate-100">${fmt(row.tabungan)}</td>
                    <td class="px-2 md:px-3 py-1.5 md:py-2 text-center">${statTabungan}</td>
                </tr>
            `;
        });
        tbody.innerHTML = html;

        info.innerText = `Hal ${detailPageMob} dari ${totalPages} (${fmt(totalRecords)} Data)`;
        
        btnPrev.disabled = detailPageMob <= 1;
        btnNext.disabled = detailPageMob >= totalPages;

        // Re-apply live search filter in case user changed pages while searching
        filterTableDetail();

    } catch(e){
        console.error(e);
        tbody.innerHTML = `<tr><td colspan="16" class="py-16 text-center text-red-500 font-bold uppercase tracking-widest text-[10px] md:text-sm">Gagal mengambil detail.</td></tr>`;
    } finally {
        loader.classList.add('hidden');
    }
}

window.changePageDetailMob = function(step) {
    detailPageMob += step;
    fetchDetailMob();
}

window.exportExcelDetailMob = async function() {
    const btn = event.target.closest('button'); const txt = btn.innerHTML;
    btn.innerHTML = `<span class="animate-spin inline-block h-3.5 w-3.5 md:h-5 md:w-5 border-2 border-white border-t-transparent rounded-full md:mr-2"></span><span class="hidden sm:inline">...</span>`;
    btn.disabled = true;

    try {
        const kankasModal = document.getElementById('opt_kankas_modal').value;
        const payload = { ...detailParamsMob, kode_kankas: kankasModal, page: 1, limit: 10000 };
        
        const res = await apiCall(API_URL, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
        const json = await res.json();
        const rows = json.data?.data || [];
        
        if(rows.length === 0) { alert("Tidak ada data detail untuk diexport"); return; }

        let csv = `No Rekening\tNama Nasabah\tAlamat\tNo HP\tKankas\tTgl Realisasi\tPlafond\tBaki Debet\tKol\tTot Tunggakan\tHM Pokok\tHM Bunga\tTgl Transaksi\tTotal Bayar\tTabungan\tStatus Tabungan\n`;
        rows.forEach(x => {
            let statTabungan = (parseFloat(x.tabungan) >= (1.5 * parseFloat(x.totung))) ? 'Aman' : 'Belum Aman';
            csv += `'${x.no_rekening}\t${x.nama_nasabah}\t${x.alamat||''}\t'${x.no_hp||''}\t${x.kankas||''}\t${x.tgl_realisasi}\t${Math.round(x.plafond)}\t${Math.round(x.os)}\t${x.kolektibilitas||''}\t${Math.round(x.totung)}\t${x.hari_menunggak_pokok}\t${x.hari_menunggak_bunga}\t${x.tgl_trans||''}\t${Math.round(x.transaksi)}\t${Math.round(x.tabungan)}\t${statTabungan}\n`;
        });

        const blob = new Blob([csv], { type: 'application/vnd.ms-excel' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Detail_MOB_${detailParamsMob.bulan_realisasi}_Bucket_${detailParamsMob.bucket_label}.xls`;
        document.body.appendChild(a); a.click(); document.body.removeChild(a);

    } catch(e) { console.error(e); alert("Gagal export data."); } 
    finally { btn.innerHTML = txt; btn.disabled = false; }
}

function formatWA(phone) {
    if (!phone) return null;
    let cleaned = phone.replace(/\D/g, ''); 
    if (cleaned.startsWith('0')) { cleaned = '62' + cleaned.substring(1); } 
    else if (cleaned.startsWith('8')) { cleaned = '62' + cleaned; }
    if (cleaned.length < 10) return null;
    return cleaned;
}

function createWABtn(phone) {
    const formatted = formatWA(phone);
    if (!formatted) return `<span class="text-slate-400 font-mono text-[9px] md:text-xs">${phone || '-'}</span>`;
    
    // 🔥 Pesan WA dihilangkan, direct WA Murni 🔥
    const waUrl = `https://wa.me/${formatted}`;
    
    return `
        <a href="${waUrl}" target="_blank" class="inline-flex items-center gap-1 md:gap-1.5 px-2 md:px-3 py-1 bg-emerald-50 hover:bg-emerald-500 hover:text-white text-emerald-600 rounded-md md:rounded-lg border border-emerald-200 transition font-bold text-[9px] md:text-xs" title="Hubungi via WhatsApp">
            <svg viewBox="0 0 24 24" width="12" height="12" fill="currentColor" class="md:w-[14px] md:h-[14px]"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.319-.883-.665-1.479-1.488-1.653-1.787-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
            <span class="hidden sm:inline ml-1.5">WA</span>
        </a>
    `;
}

window.closeModalMob = function(){ document.getElementById('modalDetailMob').classList.add('hidden'); }
document.addEventListener('keydown', e => { if(e.key === 'Escape') closeModalMob(); });
</script>