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
     🔥 MAGIC STICKY TABLE (ANTI BOCOR / OVERLAP) 🔥
     ======================================================== */
  
  /* Kunci Background Murni Biar Gak Tembus Pandang Saat Scroll */
  #tabelMigrasiSC, #tableExportMigrasi { border-collapse: separate; border-spacing: 0; }
  #tabelMigrasiSC th, #tabelMigrasiSC td, #tableExportMigrasi th, #tableExportMigrasi td { background-clip: padding-box; background-color: #fff; }
  
  /* --- Tabel Utama Migrasi --- */
  #tabelMigrasiSC thead th { position: sticky !important; z-index: 40 !important; box-shadow: inset 0 -1px 0 #cbd5e1; }
  
  /* Lapis 1 (Header Utama) */
  .mig-row-1 th { top: 0 !important; height: 46px; background-color: #f8fafc !important;}
  .mig-row-1 th.sticky-left { z-index: 60 !important; left: 0 !important; box-shadow: inset -1px -1px 0 #cbd5e1; background-color: #dcedc8 !important; } 
  
  /* Lapis 2 (Grand Total) */
  .mig-row-tot th { top: 46px !important; z-index: 45 !important; height: 50px; box-shadow: inset 0 -2px 0 #93c5fd; background-color: #eff6ff !important; }
  .mig-row-tot th.sticky-left { z-index: 62 !important; left: 0 !important; box-shadow: inset -1px -2px 0 #93c5fd; background-color: #e2e8f0 !important; }

  /* Freeze Kiri Body Utama */
  #bodyMatrix td { position: relative; z-index: 1 !important; }
  .sticky-left { position: sticky !important; left: 0 !important; }
  #bodyMatrix td.sticky-left { z-index: 20 !important; background-color: #ffffff !important; box-shadow: inset -1px 0 0 #e2e8f0; }
  
  /* Hover Effects Utama */
  #bodyMatrix tr:hover td { background-color: #f8fafc !important; cursor: pointer; }
  #bodyMatrix tr:hover td.sticky-left { background-color: #f8fafc !important; filter: brightness(0.98); }

  /* ========================================================
     🔥 TABEL MODAL DETAIL MIGRASI (FIX FREEZE & OVERLAP) 🔥
     ======================================================== */
  #tableExportMigrasi thead th { height: 46px; background-color: #f1f5f9 !important; box-shadow: inset 0 -1px 0 #cbd5e1, 0 1px 0 #cbd5e1; top: 0 !important; position: sticky !important; z-index: 40 !important; }
  @media (min-width: 768px) { #tableExportMigrasi thead th { height: 48px; } }

  /* Body Data Normal */
  #tableExportMigrasi tbody td { position: relative; z-index: 1 !important; }

  /* Kunci Lebar & Z-Index Modal Sticky (Responsif Hide Rekening) */
  .mod-freeze-rek { position: sticky !important; left: 0 !important; z-index: 60 !important; box-shadow: inset -1px 0 0 #cbd5e1; background-color: #e2e8f0 !important; min-width: 100px; max-width: 100px;}
  .mod-freeze-nas { position: sticky !important; left: 0 !important; z-index: 60 !important; box-shadow: inset -1px 0 0 #cbd5e1; background-color: #e2e8f0 !important; min-width: 160px; max-width: 160px;}
  
  .mod-td-rek { position: sticky !important; left: 0 !important; z-index: 20 !important; background-color: #fff !important; box-shadow: inset -1px 0 0 #e2e8f0; min-width: 100px; max-width: 100px;}
  .mod-td-nas { position: sticky !important; left: 0 !important; z-index: 20 !important; background-color: #fff !important; box-shadow: inset -1px 0 0 #e2e8f0; min-width: 160px; max-width: 160px;}
  
  @media (min-width: 768px) { 
      .mod-freeze-rek, .mod-td-rek { min-width: 120px; max-width: 120px; }
      /* Nasabah geser karena ada rekening di kiri */
      .mod-freeze-nas { left: 120px !important; min-width: 250px; max-width: 250px; } 
      .mod-td-nas { left: 120px !important; min-width: 250px; max-width: 250px; box-shadow: 2px 0 4px -2px rgba(0,0,0,0.1); } 
  }

  /* Hover Effect Modal Detail */
  #bodyDetail tr:hover td { background-color: #f8fafc !important; }
  #bodyDetail tr:hover td.mod-td-rek, #bodyDetail tr:hover td.mod-td-nas { filter: brightness(0.98); }

  /* Form Inputs */
  .inp { border:1px solid #cbd5e1; border-radius:6px; padding:0 8px; background:#fff; outline:none; transition: border 0.2s;}
  .inp:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,0.1); }
  .lbl { font-size:9px; color:#475569; font-weight:800; margin-bottom:2px; text-transform:uppercase; letter-spacing:0.05em; display:block; white-space: nowrap;}
  @media (min-width: 768px) { .lbl { font-size:11px; margin-bottom:4px; } .inp { border-radius: 8px; padding:0 12px; } }
  .field { display:flex; flex-direction:column; }
  
  .btn-icon { display:inline-flex; align-items:center; justify-content:center; border:none; cursor:pointer; transition: transform 0.2s;}
  .btn-icon:hover { transform:translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
  input[type="date"]::-webkit-inner-spin-button, input[type="date"]::-webkit-calendar-picker-indicator { display: none; -webkit-appearance: none; }
  input[type="date"] { -moz-appearance: textfield; }
</style>

<div class="max-w-[1920px] mx-auto px-2 md:px-4 py-4 md:py-6 h-[calc(100vh-60px)] md:h-[calc(100vh-80px)] flex flex-col font-sans text-slate-800 bg-slate-50 overflow-hidden">
  
  <div class="flex-none mb-3 md:mb-4 flex flex-col xl:flex-row justify-between items-start gap-3 md:gap-4 w-full shrink-0">
    
    <div class="flex items-start justify-between w-full xl:w-auto shrink-0">
        <div class="flex flex-col gap-1.5 w-full">
            <h1 class="text-lg md:text-2xl font-bold text-slate-800 flex items-center gap-1.5 md:gap-2 mb-0.5">
                <span class="p-1 md:p-2 bg-blue-600 rounded-lg text-white shadow-sm">
                    <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </span>
                Monitoring Migrasi SC
            </h1>
            <p class="text-[9px] md:text-[11px] text-rose-600 font-bold tracking-wide italic ml-8 md:ml-12">*Semua Nominal dlm Ribuan (Rp/1000)</p>

            <div id="summaryCheck" class="hidden flex-wrap items-center gap-2 md:gap-3 ml-1">
                <div class="flex flex-col bg-white border border-slate-200 px-2 md:px-3 py-1.5 rounded-lg shadow-sm min-w-[140px]">
                    <span class="text-[9px] md:text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">FLOW SC ➔ FE/BE</span>
                    <div class="flex items-center gap-1.5 md:gap-2">
                        <span class="text-[10px] md:text-xs font-bold bg-red-100 text-red-600 px-1.5 py-0.5 rounded" id="flowBadPct">0.00%</span>
                        <span class="text-[11px] md:text-sm font-bold text-red-600 font-mono tracking-tight" id="flowBadVal">0</span>
                    </div>
                </div>

                <div class="flex flex-col bg-white border border-slate-200 px-2 md:px-3 py-1.5 rounded-lg shadow-sm min-w-[140px]">
                    <span class="text-[9px] md:text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">FLOW FE/BE ➔ SC</span>
                    <div class="flex items-center gap-1.5 md:gap-2">
                        <span class="text-[10px] md:text-xs font-bold bg-green-100 text-green-700 px-1.5 py-0.5 rounded" id="flowGoodPct">0.00%</span>
                        <span class="text-[11px] md:text-sm font-bold text-green-700 font-mono tracking-tight" id="flowGoodVal">0</span>
                    </div>
                </div>

                <div class="flex flex-col bg-white border border-slate-200 px-2 md:px-3 py-1.5 rounded-lg shadow-sm min-w-[140px]">
                    <span class="text-[9px] md:text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">GROWTH (ACT - M1)</span>
                    <div class="flex items-center gap-1.5 md:gap-2" id="growthContainer"></div>
                </div>
            </div>
        </div>

        <button type="button" onclick="toggleFilter('filterWrapperMigrasi')" class="xl:hidden h-[30px] px-3 bg-white border border-slate-200 text-slate-700 rounded-lg flex items-center gap-1.5 shadow-sm transition font-bold text-[10px] whitespace-nowrap ml-2 mt-1 shrink-0">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
            Filter
        </button>
    </div>

    <div id="filterWrapperMigrasi" class="hidden xl:flex w-full xl:w-auto transition-all duration-300 shrink-0 xl:ml-auto mt-2 xl:mt-0">
        <form id="formFilterMigrasi" class="flex flex-row items-end gap-1.5 md:gap-2 bg-white p-2 md:p-3 rounded-lg md:rounded-xl border border-slate-200 shadow-sm w-full overflow-x-auto no-scrollbar" onsubmit="event.preventDefault(); fetchMatrix();">
            
            <div class="field shrink min-w-[80px] md:min-w-[130px]">
                <label class="lbl text-blue-700">CLOSING (M-1)</label>
                <input type="date" id="closing_date" class="inp w-full text-[10px] md:text-sm font-semibold h-[30px] md:h-[38px] px-1 md:px-3 text-slate-700 cursor-pointer" required onclick="try{this.showPicker()}catch(e){}">
            </div>
            
            <div class="field shrink min-w-[80px] md:min-w-[130px]">
                <label class="lbl">ACTUAL (HARIAN)</label>
                <input type="date" id="harian_date" class="inp w-full text-[10px] md:text-sm font-semibold h-[30px] md:h-[38px] px-1 md:px-3 text-slate-700 cursor-pointer" required onclick="try{this.showPicker()}catch(e){}">
            </div>
            
            <div class="w-px h-6 bg-slate-200 shrink-0 mx-0.5 hidden md:block mb-1.5"></div>

            <div class="field flex-1 shrink min-w-[100px] md:min-w-[200px] transition-opacity duration-300">
                <label class="lbl text-slate-600">CABANG</label>
                <select id="opt_kantor" class="inp border-slate-200 focus:border-blue-500 bg-slate-50/50 text-[10px] md:text-sm font-bold h-[30px] md:h-[38px] px-1 md:px-2 text-slate-700 cursor-pointer w-full truncate" onchange="fetchMatrix()">
                    <option>Loading...</option>
                </select>
            </div>
            
            <div class="flex items-center gap-1 md:gap-1.5 shrink-0 h-[30px] md:h-[38px] mb-px mt-2 md:mt-0">
                <button type="submit" class="btn-icon h-full px-3 md:px-5 bg-blue-600 hover:bg-blue-700 text-white rounded-md md:rounded-lg shadow-sm text-[10px] md:text-sm font-bold uppercase tracking-wider" title="Cari Data">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" class="md:mr-1.5 md:w-[16px] md:h-[16px]"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <span class="hidden md:inline">CARI</span>
                </button>
                <button type="button" onclick="exportExcelRekapMigrasi()" class="btn-icon h-full w-[36px] md:w-[42px] bg-emerald-600 hover:bg-emerald-700 text-white rounded-md md:rounded-lg shadow-sm" title="Download Excel">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="md:w-[18px] md:h-[18px]"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></line></svg>
                </button>
            </div>
        </form>
    </div>

  </div>

  <div class="flex-1 min-h-0 overflow-hidden bg-white rounded-xl shadow-sm border border-slate-200 relative flex flex-col">
    <div id="loadingMatrix" class="hidden absolute inset-0 bg-white/80 z-[100] flex flex-col items-center justify-center text-blue-600 backdrop-blur-sm">
        <div class="animate-spin rounded-full h-8 w-8 md:h-10 md:w-10 border-4 border-blue-500 border-t-transparent mb-2 md:mb-3"></div>
        <span class="text-[10px] md:text-sm font-bold uppercase tracking-widest">Menyiapkan Matriks...</span>
    </div>
    
    <div class="flex-1 w-full h-full overflow-auto custom-scrollbar relative">
      <table class="w-max min-w-full text-center border-separate border-spacing-0 text-slate-700 table-fixed" id="tabelMigrasiSC">
        <thead class="text-slate-800 font-bold tracking-wider text-[9px] md:text-xs">
          <tr class="mig-row-1">
            <th class="sticky-left px-2 md:px-3 text-left w-[90px] md:w-[120px] uppercase align-middle bg-slate-50 border-r border-slate-200 text-blue-900">DPD M-1</th>
            <th class="px-2 md:px-3 border-r border-slate-200 w-[90px] md:w-[130px] uppercase align-middle bg-slate-50 text-blue-900">POSISI M-1</th>
            <th class="px-2 md:px-3 border-r border-green-200 w-[90px] md:w-[130px] text-green-700 bg-[#f0fdf4] align-middle">0</th>
            <th class="px-2 md:px-3 border-r border-yellow-200 w-[90px] md:w-[130px] text-yellow-700 bg-[#fefce8] align-middle">1 - 7</th>
            <th class="px-2 md:px-3 border-r border-yellow-200 w-[90px] md:w-[130px] text-yellow-700 bg-[#fefce8] align-middle">8 - 14</th>
            <th class="px-2 md:px-3 border-r border-yellow-200 w-[90px] md:w-[130px] text-yellow-800 bg-[#fef9c3] align-middle">15 - 21</th>
            <th class="px-2 md:px-3 border-r border-orange-200 w-[90px] md:w-[130px] text-orange-700 bg-[#fff7ed] align-middle">22 - 30</th>
            <th class="px-2 md:px-3 border-r border-red-200 w-[90px] md:w-[130px] text-red-700 bg-[#fef2f2] align-middle">FE (31-90)</th>
            <th class="px-2 md:px-3 border-r border-red-200 w-[90px] md:w-[130px] text-red-800 bg-[#fee2e2] align-middle">BE (>90)</th>
            <th class="px-2 md:px-3 border-r border-slate-200 w-[90px] md:w-[130px] uppercase align-middle bg-slate-50 text-blue-700">ANGSURAN</th>
            <th class="px-2 md:px-3 border-r border-slate-200 w-[90px] md:w-[130px] uppercase align-middle bg-slate-50 text-blue-700">PELUNASAN</th>
            <th class="px-2 md:px-3 w-[100px] md:w-[140px] uppercase align-middle bg-slate-100 text-red-600">TOT RUN OFF</th>
          </tr>
          <tr id="rowTotalMigrasiAtas" class="mig-row-tot text-sm md:text-base font-extrabold tracking-wide"></tr>
        </thead>
        <tbody id="bodyMatrix" class="divide-y divide-slate-100 bg-white text-[10px] md:text-sm"></tbody>
      </table>
    </div>
  </div>
</div>

<div id="modalDetail" class="fixed inset-0 hidden z-[9999] flex items-end md:items-center justify-center p-0 md:p-4">
  <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
  <div class="relative bg-white w-full h-[95vh] md:h-[92vh] max-w-[1700px] rounded-t-xl md:rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-scale-up">
    
    <div class="flex flex-col bg-white border-b shrink-0 w-full z-50">
        <div class="flex flex-row items-center justify-between px-3 py-2.5 md:px-4 md:py-3 gap-2 w-full overflow-x-auto no-scrollbar">
            
            <div class="flex-1 min-w-[180px] shrink-0">
                <h3 class="font-bold text-slate-800 flex items-center gap-1.5 md:gap-2 text-[12px] md:text-xl leading-none">
                    <span class="w-1.5 md:w-2 h-4 md:h-6 bg-blue-600 rounded-full hidden md:block"></span>  
                    <span class="truncate">Detail Nasabah</span> 
                    <span id="badgeMigrasi" class="text-[9px] md:text-sm bg-blue-600 text-white px-2 py-0.5 md:px-2.5 rounded-md md:rounded-full shadow-sm ml-1 md:ml-2 font-mono shrink-0">...</span>
                </h3>
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
                
                <button onclick="closeModal()" class="w-[30px] h-[30px] md:w-[34px] md:h-[34px] flex items-center justify-center rounded-lg bg-red-50 hover:bg-red-500 hover:text-white text-red-500 transition font-bold text-lg md:text-xl leading-none shrink-0">&times;</button>
            </div>
        </div>

        <div id="modalFilterWrapper" class="hidden md:flex flex-row items-center justify-end gap-1.5 md:gap-2 px-3 pb-2.5 md:px-4 md:pb-3 w-full bg-white overflow-x-auto no-scrollbar transition-all border-t border-slate-100 md:border-none">
            <select id="opt_kankas_modal" class="inp px-1 md:px-2 h-[30px] md:h-[34px] w-[85px] md:w-[130px] text-[9px] md:text-xs font-bold text-blue-800 bg-blue-50/50 border-blue-200 outline-none shrink-0 cursor-pointer" onchange="loadDetail()">
                <option value="">Kankas</option>
            </select>

            <select id="opt_ao_modal" class="inp px-1 md:px-2 h-[30px] md:h-[34px] w-[85px] md:w-[130px] text-[9px] md:text-xs font-bold text-slate-700 bg-slate-50 border-slate-200 outline-none shrink-0 cursor-pointer" onchange="loadDetail()">
                <option value="">Semua AO</option>
            </select>
            
            <button onclick="exportExcelDetailMigrasi()" class="btn-icon bg-emerald-600 hover:bg-emerald-700 text-white px-2.5 md:px-3 h-[30px] md:h-[34px] rounded-lg shadow-sm shrink-0 flex items-center justify-center gap-1.5 ml-auto md:ml-0">
                <svg class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span class="text-[10px] md:text-xs font-bold uppercase tracking-wider hidden sm:inline">Export</span>
            </button>
        </div>
    </div>

    <div class="flex-1 overflow-auto bg-slate-50 relative p-0 md:p-3 custom-scrollbar">
        <div id="loadingDetail" class="hidden absolute inset-0 bg-white/90 z-40 flex flex-col items-center justify-center text-blue-600 backdrop-blur-sm">
            <div class="animate-spin rounded-full h-8 w-8 md:h-10 md:w-10 border-4 border-blue-500 border-t-transparent mb-2 md:mb-3"></div>
            <span class="text-[10px] md:text-sm font-bold uppercase tracking-widest">Memuat Detail...</span>
        </div>
        
        <table class="w-max min-w-full text-center md:text-left text-slate-700 border-separate border-spacing-0 md:border border-slate-200 md:rounded-xl shadow-sm bg-white table-fixed" id="tableExportMigrasi">
            <thead id="headModalMigrasi" class="bg-slate-100 text-slate-600 font-extrabold uppercase tracking-wider select-none text-[9px] md:text-xs">
                </thead>
            <tbody id="bodyDetail" class="divide-y divide-slate-100 bg-white text-[9.5px] md:text-[12px]"></tbody>
        </table>
    </div>

    <div class="px-3 py-2.5 md:px-6 md:py-4 border-t bg-white flex justify-between items-center shrink-0">
        <span id="pageInfo" class="text-[9px] md:text-xs font-bold text-slate-600 bg-slate-100 px-2 md:px-3 py-1 rounded-md md:rounded-lg">0 Data</span>
        <div class="flex gap-1.5 md:gap-2">
            <button id="btnPrev" onclick="changePageDetail(-1)" class="px-3 md:px-4 py-1.5 md:py-2 bg-white border border-slate-300 rounded-md md:rounded-lg text-[9px] md:text-sm font-bold text-slate-600 hover:bg-slate-50 hover:border-slate-400 disabled:opacity-50 transition shadow-sm">« Prev</button>
            <button id="btnNext" onclick="changePageDetail(1)" class="px-3 md:px-4 py-1.5 md:py-2 bg-white border border-slate-300 rounded-md md:rounded-lg text-[9px] md:text-sm font-bold text-slate-600 hover:bg-slate-50 hover:border-slate-400 disabled:opacity-50 transition shadow-sm">Next »</button>
        </div>
    </div>
  </div>
</div>

<script>
// --- KONFIGURASI PINTU ---
const API_ENDPOINT = './api/bucket_fe/'; 
const API_KODE = './api/kode/';
const API_DATE = './api/date/'; 
const nfID = new Intl.NumberFormat('id-ID');

// 🔥 FORMAT DIBAGI 1000 (Tabel Utama) & MURNI (Detail) 🔥
const fmtK = n => nfID.format(Math.round(Number(n||0) / 1000));
const fmt  = n => nfID.format(Math.round(Number(n||0)));

const apiCall = (u,o) => window.apiFetch ? window.apiFetch(u,o) : fetch(u,o);
const BUCKETS = ['0','1-7','8-14','15-21','22-30','FE','BE'];

let modalState = {from:'', to:'', page:1, limit:50};
let rekapDataCache = null; 

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
    const uKode = (user?.kode ? String(user.kode).padStart(3,'0') : '000');
    
    await populateKantor(uKode);
    
    const d = await getLastHarianData();
    if(d){ 
        document.getElementById('closing_date').value = d.last_closing; 
        document.getElementById('harian_date').value = d.last_created; 
    } else {
        const now = new Date().toISOString().split('T')[0];
        document.getElementById('closing_date').value = now;
        document.getElementById('harian_date').value = now;
    }
    
    fetchMatrix();
});

async function getLastHarianData(){ 
    try{ const r = await apiCall(API_DATE); const j = await r.json(); return j.data; }
    catch{ return null; }
}

async function populateKantor(uKode){
    const el = document.getElementById('opt_kantor');
    if(uKode !== '000'){
        el.innerHTML = `<option value="${uKode}">CABANG ${uKode}</option>`; 
        el.value = uKode;
        el.disabled = true; 
        return;
    }
    try{ 
        const r = await apiCall(API_KODE,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({type:'kode_kantor'})}); 
        const j = await r.json();
        let h = '<option value="">KONSOLIDASI (SEMUA)</option>'; 
        (j.data||[]).filter(x => x.kode_kantor !== '000')
            .sort((a,b) => a.kode_kantor.localeCompare(b.kode_kantor))
            .forEach(x => h += `<option value="${x.kode_kantor}">${x.kode_kantor} - ${x.nama_kantor}</option>`); 
        el.innerHTML = h; 
    } catch{}
}

// 🔥 PERBAIKAN 1: Hapus validasi branch kosong agar tetap menarik data kankas
async function loadKankasModalDropdown() {
    const elKankas = document.getElementById('opt_kankas_modal');
    const branch = document.getElementById('opt_kantor').value;
    elKankas.innerHTML = '<option value="">Semua Kankas</option>';
    
    try {
        const r = await apiCall(API_KODE, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({type: 'kode_kankas', kode_kantor: branch}) });
        const j = await r.json();
        let h = '<option value="">Semua Kankas</option>';
        if(j.data && Array.isArray(j.data)) {
            j.data.forEach(x => { 
                const namaKankas = x.deskripsi_group1 || x.kode_group1;
                h += `<option value="${x.kode_group1}">${namaKankas}</option>`; 
            });
        }
        elKankas.innerHTML = h;
    } catch(err) { console.error("Gagal load kankas dropdown", err); }
}

async function loadAOModalDropdown(kode_cabang) {
    const elAO = document.getElementById('opt_ao_modal');
    elAO.innerHTML = '<option value="">Semua AO</option>';
    if(!kode_cabang) return;

    try {
        const payload = { type: 'kode_ao_kredit', kode_kantor: kode_cabang };
        const r = await apiCall(API_KODE, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload) });
        const j = await r.json();
        
        let h = '<option value="">Semua AO</option>';
        if(j.data && Array.isArray(j.data)) {
            j.data.forEach(x => { 
                const rawName = x.nama_ao || x.kode_group2;
                h += `<option value="${x.kode_group2}">${rawName}</option>`; 
            });
        }
        elAO.innerHTML = h;
    } catch(err) { }
}

// --- CORE LOGIC FETCH MATRIX ---
async function fetchMatrix(){
    const l = document.getElementById('loadingMatrix'); 
    const tb = document.getElementById('bodyMatrix');
    const trTotal = document.getElementById('rowTotalMigrasiAtas');

    l.classList.remove('hidden');
    tb.innerHTML = `<tr><td colspan="12" class="py-20 text-center text-slate-400 italic text-[10px] md:text-sm">Sedang mengambil data...</td></tr>`;
    trTotal.innerHTML = '';
    rekapDataCache = null;

    try{
        const pl = { 
            type: "rekap_migrasi_bucket", 
            closing_date: document.getElementById('closing_date').value, 
            harian_date: document.getElementById('harian_date').value, 
            kode_kantor: document.getElementById('opt_kantor').value || null 
        };
        const r = await apiCall(API_ENDPOINT, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(pl) }); 
        const j = await r.json();
        
        rekapDataCache = j.data; 
        renderMatrix(j.data);
    } catch(e){ 
        console.error(e); 
        tb.innerHTML = `<tr><td colspan="12" class="py-16 text-center text-red-500 font-bold uppercase tracking-widest text-[10px] md:text-sm">Gagal memuat matriks</td></tr>`;
    } 
    finally{ l.classList.add('hidden'); }
}

function renderMatrix(data){
    const GT = data.grand_total;
    const real = data.realisasi;
    
    // --- 1. HITUNG GROWTH ---
    const osM1 = parseFloat(GT.m1.os || 0);
    let totalOsCurrent = 0;
    Object.values(GT.buckets).forEach(b => totalOsCurrent += parseFloat(b.os || 0));
    const osCurr = totalOsCurrent + parseFloat(real.os || 0);

    const diffVal = osCurr - osM1;
    const diffPct = osM1 > 0 ? (diffVal / osM1) * 100 : 0;
    
    // --- 2. HITUNG FLOW ---
    const goodBuckets = ['0', '1-7', '8-14', '15-21', '22-30'];
    const badBuckets = ['FE', 'BE'];

    let flowBadOS = 0;   let totalGoodM1 = 0;
    let flowGoodOS = 0;  let totalBadM1 = 0;

    goodBuckets.forEach(b => { if(data.summary_m1[b]) totalGoodM1 += parseFloat(data.summary_m1[b].os_m1 || 0); });
    badBuckets.forEach(b => { if(data.summary_m1[b]) totalBadM1 += parseFloat(data.summary_m1[b].os_m1 || 0); });

    goodBuckets.forEach(src => { badBuckets.forEach(tgt => { if(data.matrix[src] && data.matrix[src][tgt]) flowBadOS += parseFloat(data.matrix[src][tgt].os || 0); }); });
    badBuckets.forEach(src => { goodBuckets.forEach(tgt => { if(data.matrix[src] && data.matrix[src][tgt]) flowGoodOS += parseFloat(data.matrix[src][tgt].os || 0); }); });

    const flowBadPct = totalGoodM1 > 0 ? (flowBadOS / totalGoodM1) * 100 : 0;
    const flowGoodPct = totalBadM1 > 0 ? (flowGoodOS / totalBadM1) * 100 : 0;

    // --- RENDER HEADER SUMMARY ---
    document.getElementById('summaryCheck').classList.remove('hidden');
    document.getElementById('summaryCheck').classList.add('flex');

    const isUp = diffVal >= 0;
    const colorGrowth = isUp ? 'text-green-700' : 'text-red-700';
    const bgGrowth = isUp ? 'bg-green-100' : 'bg-red-100';
    
    document.getElementById('growthContainer').innerHTML = `
        <span class="text-[9px] md:text-[10px] font-bold ${bgGrowth} ${colorGrowth} px-1.5 py-0.5 rounded">${Math.abs(diffPct).toFixed(2)}%</span>
        <span class="text-[11px] md:text-sm font-bold font-mono ${colorGrowth} tracking-tight">${fmtK(diffVal)}</span>
    `;

    document.getElementById('flowBadPct').innerText = flowBadPct.toFixed(2) + '%';
    document.getElementById('flowBadVal').innerText = fmtK(flowBadOS);

    document.getElementById('flowGoodPct').innerText = flowGoodPct.toFixed(2) + '%';
    document.getElementById('flowGoodVal').innerText = fmtK(flowGoodOS);

    // --- RENDER TOTAL STICKY (GABUNG NOA DIBANWAH OS PAKAI FMTK) 🔥 ---
    let tf = `<th class="sticky-left px-2 md:px-3 text-left uppercase tracking-widest align-middle text-blue-900 bg-[#eff6ff] text-[11px] md:text-[13px] shadow-[inset_-1px_0_0_#93c5fd]">TOTAL</th>
              <th class="border-r border-blue-300 px-2 md:px-3 text-center align-middle bg-[#eff6ff]">
                 <div class="text-blue-900 font-extrabold text-[11px] md:text-[13px] mb-0.5">${fmtK(GT.m1.os)}</div>
                 <div class="text-[8px] md:text-[10px] text-blue-500 font-normal">NOA: <span class="font-bold text-blue-700">${fmt(GT.m1.noa)}</span></div>
              </th>`;
    
    BUCKETS.forEach(b => { 
        tf += `<th class="border-r border-blue-300 px-2 md:px-3 text-center align-middle bg-[#eff6ff]">
                  <div class="text-blue-900 font-extrabold text-[11px] md:text-[13px] mb-0.5">${fmtK(GT.buckets[b].os)}</div>
                  <div class="text-[8px] md:text-[10px] text-blue-500 font-normal">NOA: <span class="font-bold text-blue-700">${fmt(GT.buckets[b].noa)}</span></div>
               </th>` 
    });
    
    tf += `<th class="border-r border-blue-300 text-center px-2 md:px-3 align-middle bg-[#eff6ff]">
              <div class="text-blue-700 font-extrabold text-[11px] md:text-[13px] align-middle pt-1 md:pt-2">${fmtK(GT.angsuran)}</div>
           </th>
           <th class="border-r border-blue-300 px-2 md:px-3 text-center align-middle bg-[#eff6ff]">
              <div class="text-blue-900 font-extrabold text-[11px] md:text-[13px] mb-0.5">${fmtK(GT.lunas.os)}</div>
              <div class="text-[8px] md:text-[10px] text-blue-500 font-normal">NOA: <span class="font-bold text-blue-700">${fmt(GT.lunas.noa)}</span></div>
           </th>
           <th class="text-center px-2 md:px-3 align-middle bg-[#eff6ff]">
              <div class="text-red-600 font-extrabold text-[11px] md:text-[13px] align-middle pt-1 md:pt-2">${fmtK(GT.runoff_total.os)}</div>
           </th>`;
    
    const rowTot = document.getElementById('rowTotalMigrasiAtas');
    if(rowTot) rowTot.innerHTML = tf;

    // --- RENDER TABEL BODY ---
    const tb = document.getElementById('bodyMatrix'); 
    let h = '';
    
    // Baris Realisasi
    h += `<tr class="bg-emerald-50/40 hover:bg-emerald-100 transition border-b border-emerald-100 h-[48px] md:h-[52px]">
            <td class="sticky-left px-2 md:px-3 text-left font-bold text-emerald-800 bg-emerald-50 border-r border-emerald-200 text-[10px] md:text-sm align-middle leading-tight min-w-[90px] md:min-w-[130px] truncate shadow-[inset_-1px_0_0_#a7f3d0]">REALISASI BARU</td>
            <td class="border-r border-emerald-100 text-slate-400 align-middle text-center">-</td>
            <td class="border-r border-emerald-100 px-2 md:px-3 align-middle text-center">
                <div class="cursor-pointer transition flex flex-col justify-center h-full" onclick="openDetail('REALISASI','0')">
                    <div class="font-bold text-emerald-800 text-[10.5px] md:text-sm mb-0.5">${fmtK(real.os)}</div>
                    <div class="text-[8px] md:text-[10px] text-emerald-600 font-medium">NOA: <span class="font-bold text-emerald-800">${fmt(real.noa)}</span></div>
                </div>
            </td>
            <td colspan="6" class="text-[9px] md:text-xs italic text-slate-400 border-r border-emerald-100 align-middle text-center">Detail Tersebar di Bucket DPD</td>
            <td class="border-r border-emerald-100 text-slate-400 align-middle text-center">-</td>
            <td class="border-r border-emerald-100 text-slate-400 align-middle text-center">-</td>
            <td class="text-slate-400 align-middle text-center">-</td>
          </tr>`;

    // Loop Matrix Bucket
    BUCKETS.forEach((f, i) => {
        const m1 = data.summary_m1[f];
        h += `<tr class="hover:bg-slate-50 transition border-b border-slate-200 h-[48px] md:h-[52px]">
                <td class="sticky-left px-2 md:px-3 text-left font-bold text-slate-700 bg-white border-r border-slate-200 text-[11px] md:text-sm align-middle min-w-[90px] md:min-w-[130px] truncate shadow-[inset_-1px_0_0_#e2e8f0]">${f}</td>
                <td class="border-r border-slate-200 text-center px-2 md:px-3 align-middle bg-slate-50/30">
                    <div class="font-bold text-slate-800 text-[10.5px] md:text-sm mb-0.5">${fmtK(m1.os_m1)}</div>
                    <div class="text-[8px] md:text-[10px] text-slate-500">NOA: <span class="font-bold text-slate-700">${fmt(m1.noa_m1)}</span></div>
                </td>`;
        
        let ar = 0;
        BUCKETS.forEach((t, j) => {
            const c = data.matrix[f][t]; 
            ar += parseFloat(c.angsuran || 0);
            
            let bgClass = ''; let textClass = 'text-slate-800'; let noaClass = 'text-slate-500';

            if(c.os > 0 && m1.os_m1 > 0){
                if (j > i) { 
                    bgClass = 'bg-red-50/70 border-red-100'; textClass = 'text-red-700'; noaClass = 'text-red-600'; 
                } else if (j < i) {
                    bgClass = 'bg-emerald-50/70 border-emerald-100'; textClass = 'text-emerald-700'; noaClass = 'text-emerald-600';
                } else {
                    bgClass = 'bg-blue-50/40 border-blue-100'; textClass = 'text-blue-800'; noaClass = 'text-blue-600'; 
                }
            }

            let clickEv = (c.os > 0) ? `onclick="openDetail('${f}','${t}')"` : '';
            let cursor = (c.os > 0) ? 'cursor-pointer hover:brightness-95 hover:shadow-inner' : '';

            h += `<td class="border-r border-slate-200 px-2 md:px-3 align-middle text-center ${bgClass}">
                    <div class="h-full flex flex-col justify-center ${cursor} transition" ${clickEv}>
                        <div class="font-bold text-[10.5px] md:text-sm ${textClass} mb-0.5">${fmtK(c.os)}</div>
                        <div class="text-[8px] md:text-[10px] ${noaClass} font-medium">NOA: <span class="font-bold">${fmt(c.noa)}</span></div>
                    </div>
                  </td>`;
        });
        
        const l = data.matrix[f]['O'];
        h += `<td class="border-r border-slate-200 align-middle px-2 md:px-3 text-center bg-slate-50/30">
                <div class="font-bold text-blue-700 text-[10.5px] md:text-sm align-top pt-2 md:pt-3">${fmtK(ar)}</div>
              </td>
              <td class="border-r border-slate-200 px-2 md:px-3 text-center align-middle bg-slate-50/30">
                <div class="h-full flex flex-col justify-center cursor-pointer hover:bg-blue-50 transition" onclick="openDetail('${f}','O')">
                    <div class="font-bold text-blue-800 text-[10.5px] md:text-sm mb-0.5">${fmtK(l.pelunasan)}</div>
                    <div class="text-[8px] md:text-[10px] text-blue-500">NOA: <span class="font-bold text-blue-700">${fmt(l.noa)}</span></div>
                </div>
              </td>
              <td class="text-center bg-red-50/10 align-middle px-2 md:px-3">
                <div class="font-bold text-red-600 text-[10.5px] md:text-sm align-top pt-2 md:pt-3">${fmtK(ar + parseFloat(l.pelunasan || 0))}</div>
              </td>
            </tr>`;
    });
    tb.innerHTML = h;
}

/* EXPORT EXCEL REKAP UTAMA (Nilai Asli) */
window.exportExcelRekapMigrasi = function() {
    if(!rekapDataCache || !rekapDataCache.matrix) return alert("Tidak ada data rekap untuk didownload.");

    let csv = "DPD M-1\tNOA M-1\tOS M-1\tNOA 0\tOS 0\tNOA 1-7\tOS 1-7\tNOA 8-14\tOS 8-14\tNOA 15-21\tOS 15-21\tNOA 22-30\tOS 22-30\tNOA FE\tOS FE\tNOA BE\tOS BE\tAngsuran\tNOA Lunas\tOS Lunas\tTotal Run Off\n";
    
    const real = rekapDataCache.realisasi;
    csv += `Realisasi Baru\t-\t-\t${real.noa}\t${Math.round(real.os)}\t-\t-\t-\t-\t-\t-\t-\t-\t-\t-\t-\t-\t-\t-\t-\t-\n`;

    BUCKETS.forEach(f => {
        const m1 = rekapDataCache.summary_m1[f];
        let rowStr = `'${f}\t${m1.noa_m1}\t${Math.round(m1.os_m1)}\t`;
        
        let ar = 0;
        BUCKETS.forEach(t => {
            const c = rekapDataCache.matrix[f][t];
            ar += parseFloat(c.angsuran || 0);
            rowStr += `${c.noa}\t${Math.round(c.os)}\t`;
        });
        
        const l = rekapDataCache.matrix[f]['O'];
        const totalRunOff = ar + parseFloat(l.pelunasan || 0);
        
        rowStr += `${Math.round(ar)}\t${l.noa}\t${Math.round(l.pelunasan)}\t${Math.round(totalRunOff)}\n`;
        csv += rowStr;
    });

    const GT = rekapDataCache.grand_total;
    csv += `TOTAL\t${GT.m1.noa}\t${Math.round(GT.m1.os)}\t`;
    BUCKETS.forEach(b => { csv += `${GT.buckets[b].noa}\t${Math.round(GT.buckets[b].os)}\t`; });
    csv += `${Math.round(GT.angsuran)}\t${GT.lunas.noa}\t${Math.round(GT.lunas.os)}\t${Math.round(GT.runoff_total.os)}\n`;

    const blob = new Blob([csv], { type: 'application/vnd.ms-excel' });
    const a = document.createElement('a');
    a.href = window.URL.createObjectURL(blob);
    a.download = `Rekap_Migrasi_SC_${document.getElementById("harian_date").value}.xls`; 
    a.click();
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
    
    // 🔥 Pesan default dicomment, langsung tembak no WA aja 🔥
    const waUrl = `https://wa.me/${formatted}`;
    
    return `
        <a href="${waUrl}" target="_blank" class="inline-flex items-center gap-1 md:gap-1.5 px-2 md:px-3 py-1 bg-emerald-50 hover:bg-emerald-500 hover:text-white text-emerald-600 rounded-md md:rounded-lg border border-emerald-200 transition font-bold text-[9px] md:text-xs" title="Hubungi via WhatsApp">
            <svg viewBox="0 0 24 24" width="12" height="12" fill="currentColor" class="md:w-[14px] md:h-[14px]"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.319-.883-.665-1.479-1.488-1.653-1.787-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
            <span class="ml-1 md:ml-1.5">WA</span>
        </a>
    `;
}

// 🔥 PERBAIKAN 2: Panggil fungsi load Kankas bersamaan dengan AO saat klik open detail
function openDetail(f,t){ 
    modalState={from:f,to:t,page:1,limit:50}; 
    document.getElementById('modalDetail').classList.remove('hidden'); 
    document.getElementById('search_nasabah').value = '';
    
    let badgeText = `${f} ➔ ${t}`;
    if (f === 'REALISASI') badgeText = `NEW REALISASI`;
    else if (t === 'O') badgeText = `LUNAS / RUN OFF (${f})`;
    
    document.getElementById('badgeMigrasi').innerText = badgeText; 
    
    const branch = document.getElementById('opt_kantor').value;
    
    // Panggil dua dropdown filter secara pararel, lalu render tabelnya
    Promise.all([
        loadKankasModalDropdown(),
        loadAOModalDropdown(branch)
    ]).then(() => {
        renderModalHeaderMigrasi();
        loadDetail(); 
    });
}

// 🔥 HEADER MODAL SESUAI MODE (HILANGKAN OS M-1) 🔥
function renderModalHeaderMigrasi() {
    const mHead = document.getElementById('headModalMigrasi');
    if (modalState.to !== 'O') {
        mHead.innerHTML = `
            <tr>
                <th class="mod-freeze-rek hidden md:table-cell px-2 md:px-3 py-1.5 md:py-2 border-b border-r border-slate-300 w-[100px] md:w-[120px] rounded-tl-xl text-blue-900 text-left md:text-center">Rekening</th>
                <th class="mod-freeze-nas px-2 md:px-4 py-1.5 md:py-2 border-b border-r border-slate-300 w-[160px] md:w-[250px] text-blue-900 text-left md:text-center">Nama Nasabah</th>
                <th class="px-2 md:px-4 py-1.5 md:py-2 border-b border-r border-slate-300 w-[180px] md:w-[250px] text-center">Alamat</th>
                <th class="px-2 md:px-3 py-1.5 md:py-2 border-b border-r border-slate-300 w-[110px] md:w-[130px] text-center">No HP (WA)</th>
                <th class="px-2 md:px-3 py-1.5 md:py-2 border-b border-r border-slate-300 w-[100px] md:w-[120px] text-center">Kankas</th>
                <th class="px-2 md:px-4 py-1.5 md:py-2 border-b border-r border-slate-300 w-[110px] md:w-[140px] text-center text-blue-700">AO</th>
                <th class="px-2 md:px-4 py-1.5 md:py-2 border-b border-r border-green-200 w-[110px] md:w-[140px] text-right bg-green-50 text-green-700">OS Current</th>
                <th class="px-2 md:px-3 py-1.5 md:py-2 border-b border-r border-slate-300 w-[100px] md:w-[110px] text-center">Status</th>
                <th class="px-2 md:px-3 py-1.5 md:py-2 border-b border-r border-slate-300 w-[50px] md:w-[60px] text-center">Kol</th>
                <th class="px-2 md:px-4 py-1.5 md:py-2 border-b border-r border-red-200 w-[100px] md:w-[120px] text-right bg-red-50 text-red-700">Tgk Pokok</th>
                <th class="px-2 md:px-4 py-1.5 md:py-2 border-b border-r border-orange-200 w-[100px] md:w-[120px] text-right bg-orange-50 text-orange-700">Tgk Bunga</th>
                <th class="px-2 md:px-4 py-1.5 md:py-2 border-b border-r border-slate-300 w-[110px] md:w-[130px] text-right">Tabungan</th>
                <th class="px-2 md:px-3 py-1.5 md:py-2 border-b border-slate-200 w-[90px] md:w-[100px] text-center">Stat Tab</th>
            </tr>
        `;
    } else {
        mHead.innerHTML = `
            <tr>
                <th class="mod-freeze-nas-lunas px-2 md:px-4 py-1.5 md:py-2 border-b border-r border-slate-300 text-left md:text-center text-blue-900 rounded-tl-xl">Nama Nasabah</th>
                <th class="px-2 md:px-4 py-1.5 md:py-2 border-b border-r border-slate-300 w-[180px] md:w-[280px] text-center">Alamat</th>
                <th class="px-2 md:px-4 py-1.5 md:py-2 border-b border-r border-slate-300 w-[100px] md:w-[150px] text-center text-blue-700">AO</th>
                <th class="px-2 md:px-3 py-1.5 md:py-2 border-b border-r border-slate-300 w-[100px] md:w-[130px] text-center">Rek Lama</th>
                <th class="px-2 md:px-4 py-1.5 md:py-2 border-b border-r border-slate-300 w-[110px] md:w-[130px] text-right">Plafond Lama</th>
                <th class="px-2 md:px-4 py-1.5 md:py-2 border-b border-r border-slate-300 w-[100px] md:w-[130px] text-center">Status</th>
                <th class="px-2 md:px-3 py-1.5 md:py-2 bg-green-50 text-green-700 border-b border-r border-green-200 w-[100px] md:w-[130px] text-center">Rek Baru</th>
                <th class="px-2 md:px-4 py-1.5 md:py-2 bg-green-50 text-green-700 border-b border-r border-green-200 w-[110px] md:w-[130px] text-right">Plafond Baru</th>
                <th class="px-2 md:px-3 py-1.5 md:py-2 bg-green-50 text-green-700 border-b border-green-200 w-[90px] md:w-[120px] text-center">Tgl Realisasi</th>
            </tr>
        `;
    }
}

window.filterTableDetail = function() {
    const input = document.getElementById("search_nasabah");
    const filter = input.value.toLowerCase();
    const tbody = document.getElementById("bodyDetail");
    const trs = tbody.getElementsByTagName("tr");

    for (let i = 0; i < trs.length; i++) {
        const tdName = modalState.to !== 'O' ? trs[i].getElementsByTagName("td")[1] : trs[i].getElementsByTagName("td")[0];
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

async function loadDetail(){
    const l=document.getElementById('loadingDetail'); const tb=document.getElementById('bodyDetail'); 
    const info = document.getElementById('pageInfo');
    l.classList.remove('hidden'); tb.innerHTML='';
    try{
        const kankasModal = document.getElementById('opt_kankas_modal').value;
        const aoModal = document.getElementById('opt_ao_modal').value;

        const pl={
            type: modalState.to === 'O' ? 'detail_lunas_migrasi' : 'detail_migrasi_bucket',
            closing_date:document.getElementById('closing_date').value,
            harian_date:document.getElementById('harian_date').value,
            kode_kantor:document.getElementById('opt_kantor').value||null,
            kode_kankas:kankasModal,
            kode_ao:aoModal,
            from_bucket:modalState.from,
            to_bucket:modalState.to,
            page:modalState.page,
            limit:modalState.limit
        };
        const r=await apiCall(API_ENDPOINT,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(pl)}); 
        const j=await r.json(); 
        
        const d=j.data.data||[]; 
        const m=j.data.pagination || { total_records:0, total_pages:1 };
        
        if(d.length===0){
            tb.innerHTML='<tr><td colspan="14" class="py-20 text-center text-slate-400 italic text-[10px] md:text-sm">Tidak ada data detail.</td></tr>'; 
            info.innerText='0 Data'; 
            return;
        }
        
        let h = '';
        d.forEach(x=>{
            const aoName = (x.nama_ao || '-').split(' ').slice(0, 2).join(' ');
            
            // 🔥 ALAMAT MAX 25 KARAKTER SAJA 🔥
            const alamatLengkap = x.alamat || '-';
            const alamatPendek = alamatLengkap.length > 25 ? alamatLengkap.substring(0, 25) + '...' : alamatLengkap;

            if(modalState.to !== 'O') {
                const btnWa = createWABtn(x.no_hp);
                let statTabungan = `<span class="text-red-500 font-bold text-[9px] md:text-xs">Belum Aman</span>`;
                if(x.status_tabungan === 'Aman') statTabungan = `<span class="text-green-600 font-bold text-[9px] md:text-xs">Aman</span>`;

                let bgStat = 'bg-blue-50 text-blue-700 border-blue-200';
                if(x.status_migrasi === 'Lunas') bgStat = 'bg-slate-100 text-slate-600 border-slate-300';
                else if(x.status_migrasi === 'New') bgStat = 'bg-emerald-50 text-emerald-700 border-emerald-200';

                // 🔥 NILAI ASLI MURNI (TANPA DIBAGI 1000) 🔥
                h+=`<tr class="transition border-b border-slate-100 h-[40px] md:h-[48px]">
                      <td class="mod-td-rek hidden md:table-cell px-2 md:px-3 py-1.5 md:py-2 font-mono text-[9.5px] md:text-[11px] text-slate-500">${x.no_rekening}</td>
                      <td class="mod-td-nas px-2 md:px-4 py-1.5 md:py-2 font-bold text-[9.5px] md:text-[11px] text-slate-700 truncate" title="${x.nama_nasabah}">${x.nama_nasabah}</td>
                      <td class="px-2 md:px-4 py-1.5 md:py-2 text-slate-500 text-[9.5px] md:text-[11px] border-r border-slate-100 whitespace-nowrap text-center" title="${alamatLengkap}">${alamatPendek}</td>
                      <td class="px-2 md:px-3 py-1.5 md:py-2 text-center border-r border-slate-100">${btnWa}</td>
                      <td class="px-2 md:px-3 py-1.5 md:py-2 text-center font-mono text-[9px] md:text-[11px] text-slate-500 border-r border-slate-100">${x.kankas||'-'}</td>
                      <td class="px-2 md:px-4 py-1.5 md:py-2 text-center font-bold text-[9.5px] md:text-[11px] text-blue-700 border-r border-slate-100 truncate">${aoName}</td>
                      <td class="px-2 md:px-4 py-1.5 md:py-2 text-right font-mono font-bold text-[9.5px] md:text-sm text-green-700 bg-green-50/30 border-r border-green-100">${fmt(x.baki_debet)}</td>
                      <td class="px-2 md:px-3 py-1.5 md:py-2 text-center border-r border-slate-100"><span class="${bgStat} px-1.5 md:px-2.5 py-0.5 md:py-1 rounded-md md:rounded-lg text-[9px] md:text-[10px] font-bold border uppercase tracking-wider">${x.status_migrasi}</span></td>
                      <td class="px-2 md:px-3 py-1.5 md:py-2 text-center font-bold text-[9.5px] md:text-sm text-slate-600 border-r border-slate-100">${x.kolektibilitas||'-'}</td>
                      <td class="px-2 md:px-4 py-1.5 md:py-2 text-right font-mono font-bold text-[9.5px] md:text-sm text-red-600 bg-red-50/30 border-r border-red-100">${fmt(x.tunggakan_pokok)}</td>
                      <td class="px-2 md:px-4 py-1.5 md:py-2 text-right font-mono font-bold text-[9.5px] md:text-sm text-orange-600 bg-orange-50/30 border-r border-orange-100">${fmt(x.tunggakan_bunga)}</td>
                      <td class="px-2 md:px-4 py-1.5 md:py-2 text-right font-mono font-bold text-[9.5px] md:text-sm text-emerald-600 bg-emerald-50/10 border-r border-slate-100">${fmt(x.tabungan)}</td>
                      <td class="px-2 md:px-3 py-1.5 md:py-2 text-center">${statTabungan}</td>
                    </tr>`;
            } else {
                let badge = `<span class="inline-flex items-center px-1.5 md:px-2.5 py-0.5 md:py-1 rounded text-[9px] md:text-xs font-bold bg-blue-100 text-blue-700 border border-blue-200">PROSPEK</span>`;
                if(x.status_lunas === 'REFINANCING / Top Up') badge = `<span class="inline-flex items-center px-1.5 md:px-2.5 py-0.5 md:py-1 rounded text-[9px] md:text-xs font-bold bg-green-100 text-green-700 border border-green-200">REFINANCING</span>`;

                h += `<tr class="transition border-b border-slate-100 h-[40px] md:h-[48px]">
                      <td class="mod-td-nas px-2 md:px-4 py-1.5 md:py-2 font-bold text-slate-700 truncate shadow-[2px_0_4px_-2px_rgba(0,0,0,0.1)] text-[9.5px] md:text-sm">
                          ${x.nama_nasabah}
                          <div class="text-[8px] md:text-xs text-slate-400 font-mono mt-0.5 font-normal">ID: ${x.nasabah_id}</div>
                      </td>
                      <td class="px-2 md:px-4 py-1.5 md:py-2 border-r border-slate-100 text-slate-500 text-[9.5px] md:text-sm truncate text-center" title="${alamatLengkap}">${alamatPendek}</td>
                      <td class="px-2 md:px-4 py-1.5 md:py-2 border-r border-slate-100 text-center font-bold text-[9.5px] md:text-sm text-blue-700 truncate">${aoName}</td>
                      <td class="px-2 md:px-3 py-1.5 md:py-2 border-r border-slate-100 font-mono text-[9.5px] md:text-sm text-center text-slate-600">${x.no_rekening}</td>
                      <td class="px-2 md:px-4 py-1.5 md:py-2 border-r border-slate-100 text-right font-medium text-slate-600 bg-slate-50/50 text-[9.5px] md:text-sm">${fmt(x.plafon_lama)}</td>
                      <td class="px-2 md:px-4 py-1.5 md:py-2 border-r border-slate-100 text-center">${badge}</td>
                      <td class="px-2 md:px-3 py-1.5 md:py-2 border-r border-green-100 font-mono text-[9.5px] md:text-sm text-center bg-green-50/30 text-green-800 font-bold">${x.rek_baru}</td>
                      <td class="px-2 md:px-4 py-1.5 md:py-2 border-r border-green-100 text-right bg-green-50/30 text-green-700 font-bold text-[9.5px] md:text-sm">${fmt(x.plafond_baru)}</td>
                      <td class="px-2 md:px-3 py-1.5 md:py-2 text-center bg-green-50/30 text-[9.5px] md:text-sm font-medium text-green-700">${x.tgl_baru}</td>
                  </tr>`;
            }
        }); 
        tb.innerHTML=h; 
        
        info.innerText=`Hal ${modalState.page} dari ${m.total_pages} (${fmt(m.total_records)} Data)`;
        
        const p=document.getElementById('btnPrev'); const n=document.getElementById('btnNext');
        p.disabled=modalState.page<=1; n.disabled=modalState.page>=m.total_pages;
    } catch(e){
        console.error(e);
        tb.innerHTML = `<tr><td colspan="14" class="py-16 text-center text-red-500 font-bold uppercase tracking-widest text-[10px] md:text-sm">Gagal memuat detail</td></tr>`;
    } 
    finally{l.classList.add('hidden');}
}

/* EXPORT EXCEL DETAIL MIGRASI (HILANGKAN OS M-1) */
async function exportExcelDetailMigrasi() {
    const btn = event.target.closest('button'); const txt = btn.innerHTML;
    btn.innerHTML = `<span class="animate-spin inline-block h-4 w-4 border-2 border-white border-t-transparent rounded-full md:mr-2"></span>...`;
    btn.disabled = true;

    try {
        const kankasModal = document.getElementById('opt_kankas_modal').value;
        const aoModal = document.getElementById('opt_ao_modal').value;
        const pl={
            type: modalState.to === 'O' ? 'detail_lunas_migrasi' : 'detail_migrasi_bucket',
            closing_date:document.getElementById('closing_date').value,
            harian_date:document.getElementById('harian_date').value,
            kode_kantor:document.getElementById('opt_kantor').value||null,
            kode_kankas:kankasModal,
            kode_ao: aoModal,
            from_bucket:modalState.from,
            to_bucket:modalState.to,
            page: 1,
            limit: 10000 
        };
        const r=await apiCall(API_ENDPOINT,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(pl)}); 
        const j=await r.json(); 
        const rows=j.data.data||[]; 
        
        if(rows.length === 0) { alert("Tidak ada data detail untuk diexport"); return; }

        let csv = "";
        if (modalState.to !== 'O') {
            csv = `No Rekening\tNama Nasabah\tAlamat\tNo HP\tKankas\tAO\tOS Current\tStatus Migrasi\tKol\tTunggakan Pokok\tTunggakan Bunga\tTotal Tunggakan\tTabungan\tStatus Tabungan\n`;
            rows.forEach(x => {
                csv += `'${x.no_rekening}\t${x.nama_nasabah}\t${x.alamat||''}\t'${x.no_hp||''}\t${x.kankas||''}\t${x.nama_ao||''}\t${Math.round(x.baki_debet)}\t${x.status_migrasi}\t${x.kolektibilitas||''}\t${Math.round(x.tunggakan_pokok)}\t${Math.round(x.tunggakan_bunga)}\t${Math.round(x.totung)}\t${Math.round(x.tabungan)}\t${x.status_tabungan}\n`;
            });
        } else {
            csv = `Nama Nasabah\tID Nasabah\tAlamat\tNama AO\tRek Lama\tPlafond Lama\tStatus\tRek Baru\tPlafond Baru\tTgl Realisasi Baru\n`;
            rows.forEach(x => {
                csv += `${x.nama_nasabah}\t'${x.nasabah_id}\t${x.alamat||''}\t${x.nama_ao||''}\t'${x.no_rekening}\t${Math.round(x.plafon_lama)}\t${x.status_lunas}\t'${x.rek_baru}\t${Math.round(x.plafond_baru)}\t${x.tgl_baru}\n`;
            });
        }

        const blob = new Blob([csv], { type: 'application/vnd.ms-excel' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Detail_Migrasi_${modalState.from}_to_${modalState.to}.xls`;
        document.body.appendChild(a); a.click(); document.body.removeChild(a);

    } catch(e) { console.error(e); alert("Gagal export data."); } 
    finally { btn.innerHTML = txt; btn.disabled = false; }
}

function changePageDetail(step) {
    modalState.page += step;
    loadDetail();
}

function closeModal(){document.getElementById('modalDetail').classList.add('hidden');}
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeModal();});
</script>