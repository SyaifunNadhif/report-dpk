<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
  /* Custom Scrollbar */
  .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
  .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
  .no-scrollbar::-webkit-scrollbar { display: none; }
  .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

  /* Animasi Modal */
  @keyframes scaleUp { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
  .animate-scale-up { animation: scaleUp 0.2s ease-out forwards; }

  /* ========================================================
     🔥 MAGIC STICKY TABLE (FIX SAFARI/MOBILE 100% FREEZE) 🔥
     ======================================================== */
  
  #tabelUtama th, #tableDetail th { position: sticky !important; top: 0; z-index: 10; }
  
  .head-lapis-1 th { height: 46px; background-color: #dcedc8 !important; box-shadow: inset 0 -1px 0 #cbd5e1; top: 0 !important; }
  .head-lapis-2 th { top: 46px !important; height: 40px; background-color: #eff6ff !important; box-shadow: inset 0 -2px 0 #cbd5e1; }
  @media (min-width: 768px) {
      .head-lapis-1 th { height: 50px; }
      .head-lapis-2 th { top: 50px !important; height: 46px; }
  }

  .freeze-col-1 { position: sticky !important; left: 0 !important; z-index: 22 !important; box-shadow: inset -1px 0 0 #cbd5e1; background-color: #dcedc8; }
  .freeze-col-2 { position: sticky !important; left: 0 !important; z-index: 20 !important; box-shadow: inset -1px 0 0 #e2e8f0; background-color: #dcedc8; }
  @media (min-width: 768px) { .freeze-col-2 { left: 59px !important; } }

  #tabelUtama th.freeze-col-1 { z-index: 30 !important; }
  #tabelUtama th.freeze-col-2 { z-index: 29 !important; }
  #tabelUtama .head-lapis-2 th.freeze-col-1 { z-index: 32 !important; background-color: #eff6ff !important; }
  #tabelUtama .head-lapis-2 th.freeze-col-2 { z-index: 31 !important; background-color: #eff6ff !important; }
  
  tbody td.freeze-col-1, tbody td.freeze-col-2 { background-color: #ffffff; }

  .modal-head-1 th { height: 46px; background-color: #f1f5f9 !important; box-shadow: inset 0 -1px 0 #cbd5e1; top: 0 !important; }
  .modal-head-2 th { top: 46px !important; height: 40px; background-color: #eff6ff !important; box-shadow: inset 0 -2px 0 #cbd5e1; }
  @media (min-width: 768px) {
      .modal-head-1 th { height: 48px; }
      .modal-head-2 th { top: 48px !important; height: 42px; }
  }

  .mod-freeze-rek { position: sticky !important; left: 0 !important; z-index: 22 !important; box-shadow: inset -1px 0 0 #e2e8f0; background-color: #ffffff;}
  .mod-freeze-nas { position: sticky !important; left: 0 !important; z-index: 20 !important; box-shadow: inset -1px 0 0 #e2e8f0; background-color: #ffffff;}
  @media (min-width: 768px) { .mod-freeze-nas { left: 100px !important; z-index: 20 !important; } }

  #tableDetail th.mod-freeze-rek { z-index: 30 !important; background-color: #f1f5f9 !important; }
  #tableDetail th.mod-freeze-nas { z-index: 29 !important; background-color: #f1f5f9 !important; }
  #tableDetail .modal-head-2 th.mod-freeze-rek { z-index: 32 !important; background-color: #eff6ff !important; }
  #tableDetail .modal-head-2 th.mod-freeze-nas { z-index: 31 !important; background-color: #eff6ff !important; }
  
  tbody td.mod-freeze-rek, tbody td.mod-freeze-nas { background-color: #ffffff; }

  tbody tr:hover td { background-color: #f8fafc !important; }
  tbody tr:hover td.freeze-col-1, tbody tr:hover td.freeze-col-2, tbody tr:hover td.mod-freeze-rek, tbody tr:hover td.mod-freeze-nas { filter: brightness(0.98); }

  /* Form Inputs */
  .inp { border:1px solid #cbd5e1; border-radius:6px; background:#fff; outline:none; transition: border 0.2s;}
  .inp:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,0.1); }
  .lbl { font-size:9px; color:#475569; font-weight:800; margin-bottom:2px; text-transform:uppercase; letter-spacing:0.05em; display:block; white-space: nowrap;}
  @media (min-width: 768px) { .lbl { font-size:11px; margin-bottom:4px; } .inp { border-radius: 8px; } }
  
  .field { display:flex; flex-direction:column; }
  .btn-icon { display:inline-flex; align-items:center; justify-content:center; border:none; cursor:pointer; transition: transform 0.2s;}
  .btn-icon:hover { transform:translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
  input[type="date"]::-webkit-inner-spin-button, input[type="date"]::-webkit-calendar-picker-indicator { display: none; -webkit-appearance: none; }
  input[type="date"] { -moz-appearance: textfield; }
</style>

<div class="max-w-[1920px] mx-auto px-2 md:px-4 py-2 md:py-4 h-screen flex flex-col bg-slate-50 font-sans text-slate-800 overflow-hidden" style="height: 100dvh;">
  
  <div class="flex gap-3 md:gap-8 border-b border-slate-300 mb-2 md:mb-3 px-1 md:px-2 overflow-x-auto no-scrollbar shrink-0">
      <button id="tab-growth" onclick="switchTab('growth')" class="pb-1.5 md:pb-2.5 font-extrabold text-[10px] md:text-sm uppercase transition border-b-[3px] border-blue-600 text-blue-700 whitespace-nowrap">REKAP REALISASI & GROWTH</button>
      <button id="tab-promo" onclick="switchTab('promo')" class="pb-1.5 md:pb-2.5 font-extrabold text-[10px] md:text-sm uppercase transition border-b-[3px] border-transparent text-slate-400 hover:text-slate-600 whitespace-nowrap">ANALITIK PROMO VS NON-PROMO</button>
  </div>

  <div class="flex-none mb-2 flex flex-col xl:flex-row justify-between items-start xl:items-center gap-2 md:gap-3 w-full shrink-0">
      
      <div class="flex items-center justify-between w-full xl:w-auto shrink-0">
          <div class="flex flex-col gap-0.5 shrink-0" id="header-title-container"></div>
          
          <button type="button" onclick="document.getElementById('formFilterUtama').classList.toggle('hidden'); document.getElementById('formFilterUtama').classList.toggle('flex')" class="xl:hidden h-[30px] px-3 bg-white border border-slate-200 text-slate-700 rounded-lg flex items-center gap-1.5 shadow-sm transition font-bold text-[10px] whitespace-nowrap ml-2 shrink-0">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
              Filter
          </button>
      </div>

      <form id="formFilterUtama" class="hidden xl:flex bg-white p-1.5 md:p-2 rounded-lg md:rounded-xl border border-slate-200 shadow-sm flex-row items-end gap-1.5 md:gap-2 w-full xl:w-auto shrink-0 xl:ml-auto overflow-x-auto no-scrollbar transition-all duration-300" onsubmit="event.preventDefault(); fetchRekap();">
          
          <div class="field shrink-0 w-[110px] md:w-[130px]" id="wrap-closing">
              <label class="lbl">CLOSING</label>
              <input type="date" id="closing_date" class="inp w-full text-[10px] md:text-sm font-semibold h-[32px] md:h-[38px] px-1 md:px-3 text-slate-700 cursor-pointer" required onclick="try{this.showPicker()}catch(e){}">
          </div>
          
          <div class="field shrink-0 w-[110px] md:w-[130px]">
              <label class="lbl">HARIAN</label>
              <input type="date" id="harian_date" class="inp w-full text-[10px] md:text-sm font-semibold h-[32px] md:h-[38px] px-1 md:px-3 text-slate-700 cursor-pointer" required onclick="try{this.showPicker()}catch(e){}">
          </div>
          
          <div class="w-px h-6 bg-slate-200 shrink-0 mx-0.5 hidden md:block mb-1.5" id="divider-filter" style="display: none;"></div>

          <div class="field shrink-0 w-[140px] md:w-[200px]" id="wrap-area" style="display: none;">
              <label class="lbl text-pink-700">AREA / CABANG</label>
              <select id="opt_area" class="inp border-pink-200 focus:border-pink-500 bg-pink-50/30 text-[10px] md:text-sm font-bold h-[32px] md:h-[38px] px-1 md:px-2 text-pink-800 cursor-pointer w-full truncate"></select>
          </div>
          
          <div class="flex items-center gap-1.5 shrink-0 h-[32px] md:h-[38px]">
              <button type="submit" id="btn-cari" class="btn-icon h-full w-[36px] md:w-auto md:px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-md md:rounded-lg shadow-sm" title="Cari Data">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="md:w-[16px] md:h-[16px]"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                  <span class="hidden md:inline font-bold text-xs uppercase tracking-wider ml-1.5">CARI</span>
              </button>
              <button type="button" onclick="exportExcelRekap()" class="btn-icon h-full w-[36px] md:w-[40px] bg-emerald-600 hover:bg-emerald-700 text-white rounded-md md:rounded-lg shadow-sm shrink-0" title="Download Excel">
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="md:w-[18px] md:h-[18px]"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></line></svg>
              </button>
          </div>
      </form>
  </div>

  <div class="flex-1 min-h-0 flex flex-col relative gap-2 md:gap-3 pb-4 md:pb-0">
      
      <div id="content-growth" class="flex-1 min-h-0 bg-white rounded-lg md:rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col relative">
          <div id="loadingUtama" class="hidden absolute inset-0 bg-white/80 z-[100] flex flex-col items-center justify-center text-blue-600 backdrop-blur-sm transition-colors">
              <div class="animate-spin rounded-full h-8 w-8 md:h-10 md:w-10 border-4 border-current border-t-transparent mb-2 md:mb-3"></div>
              <span class="text-[10px] md:text-sm font-bold uppercase tracking-widest">Menyiapkan Data...</span>
          </div>
          
          <div class="flex-1 w-full h-full overflow-auto custom-scrollbar relative">
              <table class="w-full text-center border-separate border-spacing-0 text-slate-700 min-w-max" id="tabelUtama">
                <thead class="tracking-wider text-slate-700 font-extrabold text-[9px] md:text-sm head-lapis-1 select-none" id="headUtama"></thead>
                <tbody id="bodyUtama" class="divide-y divide-slate-100 bg-white text-[9.5px] md:text-sm"></tbody>
              </table>
          </div>
      </div>

      <div id="content-promo" class="hidden flex-1 min-h-0 flex-col gap-2 md:gap-3 overflow-y-auto custom-scrollbar pb-6 relative">
          <div id="loadingPromo" class="hidden absolute inset-0 bg-slate-50/80 z-[100] flex flex-col items-center justify-center text-pink-600 backdrop-blur-sm transition-colors rounded-xl">
              <div class="animate-spin rounded-full h-8 w-8 md:h-10 md:w-10 border-4 border-current border-t-transparent mb-2 md:mb-3"></div>
              <span class="text-[10px] md:text-sm font-bold uppercase tracking-widest">Membangun Grafik...</span>
          </div>

          <div class="flex gap-1.5 md:gap-2 items-center bg-white p-1.5 md:p-2 rounded-lg md:rounded-xl shadow-sm border border-slate-200 shrink-0 overflow-x-auto no-scrollbar">
              <span class="text-[9px] md:text-xs font-bold text-slate-500 uppercase ml-1 md:ml-2 mr-1 shrink-0"><span class="hidden md:inline">Quick</span> Filter:</span>
              <button type="button" onclick="quickDatePromo('7')" class="px-2 md:px-3 py-1 md:py-1.5 bg-pink-50 hover:bg-pink-100 text-pink-700 text-[9px] md:text-[11px] font-bold rounded-md md:rounded-lg transition shrink-0 border border-pink-100">7 HARI</button>
              <button type="button" onclick="quickDatePromo('14')" class="px-2 md:px-3 py-1 md:py-1.5 bg-pink-50 hover:bg-pink-100 text-pink-700 text-[9px] md:text-[11px] font-bold rounded-md md:rounded-lg transition shrink-0 border border-pink-100">14 HARI</button>
              <button type="button" onclick="quickDatePromo('bulan_ini')" class="px-2 md:px-3 py-1 md:py-1.5 bg-pink-50 hover:bg-pink-100 text-pink-700 text-[9px] md:text-[11px] font-bold rounded-md md:rounded-lg transition shrink-0 border border-pink-100">BULAN INI</button>
              <button type="button" onclick="quickDatePromo('awal_promo')" class="px-2 md:px-3 py-1 md:py-1.5 bg-pink-600 hover:bg-pink-700 text-white text-[9px] md:text-[11px] font-bold rounded-md md:rounded-lg shadow-sm transition shrink-0">AWAL PROMO</button>
          </div>

          <div class="grid grid-cols-2 gap-2 md:gap-4 shrink-0">
              <div class="bg-pink-50 border border-pink-200 rounded-lg md:rounded-xl shadow-sm p-3 md:p-5 text-pink-900 flex flex-col justify-center">
                  <span class="text-[9px] md:text-sm font-extrabold opacity-80 uppercase tracking-widest mb-0.5 md:mb-1 flex items-center gap-1.5"><span class="text-sm md:text-lg">🎁</span> TOTAL PROMO</span>
                  <h2 class="text-sm md:text-2xl xl:text-3xl font-black font-mono truncate mt-0.5 text-pink-700" id="txt-tot-promo">0</h2>
                  <span class="text-[8px] md:text-[10px] font-bold bg-pink-200/60 w-max px-1.5 md:px-2 py-0.5 rounded text-pink-900 mt-1" id="txt-noa-promo">0 NOA</span>
              </div>
              <div class="bg-slate-50 border border-slate-200 rounded-lg md:rounded-xl shadow-sm p-3 md:p-5 text-slate-900 flex flex-col justify-center">
                  <span class="text-[9px] md:text-sm font-extrabold opacity-80 uppercase tracking-widest mb-0.5 md:mb-1 flex items-center gap-1.5"><span class="text-sm md:text-lg">🏢</span> NON-PROMO</span>
                  <h2 class="text-sm md:text-2xl xl:text-3xl font-black font-mono truncate mt-0.5 text-slate-700" id="txt-tot-nonpromo">0</h2>
                  <span class="text-[8px] md:text-[10px] font-bold bg-slate-200/80 w-max px-1.5 md:px-2 py-0.5 rounded text-slate-800 mt-1" id="txt-noa-nonpromo">0 NOA</span>
              </div>
          </div>

          <div class="flex-1 flex flex-col xl:flex-row gap-2 md:gap-3 min-h-[350px]">
              <div class="w-full xl:w-[30%] bg-white p-3 md:p-4 rounded-lg md:rounded-xl shadow-sm border border-slate-200 flex flex-col relative h-[250px] xl:h-auto shrink-0">
                  <h3 class="text-[10px] md:text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 md:mb-3 shrink-0 text-center">Proporsi Nominal</h3>
                  <div class="flex-1 relative w-full flex items-center justify-center min-h-0">
                      <canvas id="pieChart"></canvas>
                  </div>
              </div>
              <div class="w-full xl:flex-1 bg-white p-3 md:p-4 rounded-lg md:rounded-xl shadow-sm border border-slate-200 flex flex-col relative h-[250px] xl:h-auto shrink-0">
                  <h3 class="text-[10px] md:text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 md:mb-3 shrink-0 text-center">Trend Harian Promo vs Non-Promo</h3>
                  <div class="flex-1 relative w-full min-h-0">
                      <canvas id="barChart"></canvas>
                  </div>
              </div>
          </div>
      </div>
  </div>
</div>

<div id="modalDetail" class="fixed inset-0 z-[9999] hidden items-end md:items-center justify-center p-0 sm:p-4">
  <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
  <div class="relative bg-white w-full h-[95vh] md:h-[92vh] max-w-[1700px] rounded-t-xl md:rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-scale-up">
    
    <div class="flex flex-col bg-white border-b shrink-0 w-full z-50">
        
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between px-3 py-2.5 md:px-4 md:py-3 gap-2 w-full">
            
            <div class="flex-1 min-w-0 w-full md:w-auto" id="modal-title-container"></div>
            
            <div class="flex items-center gap-1.5 md:gap-2 w-full md:w-auto mt-1 md:mt-0">
                <div class="relative flex-1 md:w-[220px]">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" id="search_nasabah" onkeyup="filterTableDetail()" class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-[11px] md:text-xs outline-none focus:border-blue-500 focus:bg-white transition-all placeholder-slate-400 font-medium" placeholder="Cari nama nasabah...">
                </div>
                
                <button type="button" onclick="document.getElementById('modalFilterBody').classList.toggle('hidden')" class="h-[28px] w-[32px] md:h-[32px] md:w-[36px] bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 rounded-lg flex items-center justify-center transition shrink-0" title="Filter Lanjutan">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                </button>
                
                <button onclick="closeModal()" class="md:hidden h-[28px] w-[32px] flex items-center justify-center rounded-lg bg-red-50 text-red-500 border border-red-100 font-bold text-lg leading-none shrink-0">&times;</button>
                <button onclick="closeModal()" class="hidden md:flex w-[32px] h-[32px] items-center justify-center rounded-lg bg-slate-100 hover:bg-red-500 hover:text-white text-slate-600 transition font-bold text-xl leading-none shrink-0">&times;</button>
            </div>
        </div>

        <div id="modalFilterBody" class="hidden flex-col md:flex-row items-center gap-2 px-3 pb-2.5 md:px-4 md:pb-3 w-full bg-white">
            <select id="filter_kankas_modal" class="inp py-1 h-[32px] w-full md:w-[160px] text-[10px] md:text-xs font-bold text-blue-800 bg-blue-50/50 border-blue-200 outline-none cursor-pointer" onchange="onKankasChange()">
                <option value="">Semua Kankas</option>
            </select>
            <select id="filter_ao_modal" class="inp py-1 h-[32px] w-full md:w-[160px] text-[10px] md:text-xs font-bold text-slate-700 bg-slate-50 border-slate-200 outline-none cursor-pointer" onchange="fetchDetail(1)">
                <option value="">Semua AO</option>
            </select>
            <div class="w-full md:w-auto flex justify-end mt-1 md:mt-0 md:ml-auto">
                <button id="btn-excel-modal" onclick="exportExcelDetail()" class="btn-icon w-full md:w-auto bg-emerald-600 hover:bg-emerald-700 text-white px-3 h-[32px] rounded-md md:rounded-lg shadow-sm text-[10px] md:text-xs font-bold uppercase tracking-wider flex justify-center items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export Excel
                </button>
            </div>
        </div>
    </div>

    <div class="flex-1 overflow-auto bg-slate-50 relative custom-scrollbar p-0 md:p-3">
        <div id="loadingModal" class="hidden absolute inset-0 bg-white/90 z-40 flex flex-col items-center justify-center text-blue-600 backdrop-blur-sm transition-colors">
            <div class="animate-spin rounded-full h-8 w-8 border-4 border-blue-200 border-t-blue-600 mb-2"></div>
            <span class="text-[10px] font-bold uppercase tracking-widest">Memuat...</span>
        </div>
        <table class="w-full text-center md:text-left text-slate-700 border-separate border-spacing-0 md:border md:border-slate-200 md:rounded-xl shadow-sm bg-white min-w-max" id="tableDetail">
            <thead class="text-slate-600 font-extrabold uppercase tracking-wider text-[9px] md:text-[11px] modal-head-1 select-none" id="headDetail"></thead>
            <tbody id="bodyDetail" class="divide-y divide-slate-100 bg-white text-[9.5px] md:text-[12px]"></tbody>
        </table>
    </div>

    <div class="px-3 py-2.5 md:px-6 md:py-3 border-t bg-white flex justify-between items-center shrink-0">
        <span id="pageInfo" class="text-[9px] md:text-xs font-bold text-slate-600 bg-slate-100 px-2 md:px-3 py-1 rounded-md md:rounded-lg">0 Data</span>
        <div class="flex gap-1 md:gap-2" id="wrap-paging">
            <button id="btnPrev" onclick="changePage(-1)" class="px-2.5 md:px-4 py-1.5 md:py-1.5 bg-white border border-slate-300 rounded-md md:rounded-lg text-[9px] md:text-xs font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-50 transition shadow-sm">« Prev</button>
            <button id="btnNext" onclick="changePage(1)" class="px-2.5 md:px-4 py-1.5 md:py-1.5 bg-white border border-slate-300 rounded-md md:rounded-lg text-[9px] md:text-xs font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-50 transition shadow-sm">Next »</button>
        </div>
    </div>

  </div>
</div>

<script>
  // --- CONFIG SATU PINTU ---
  const API_URL  = './api/kredit/'; 
  const API_KODE = './api/kode/';
  const API_DATE = './api/date/';
  
  const AWAL_PROMO_DATE = '2026-02-23';

  const nfID = new Intl.NumberFormat('id-ID');
  const fmt = n => nfID.format(Math.round(Number(n||0)));
  const apiCall = (url, opt={}) => (window.apiFetch ? window.apiFetch(url,opt) : fetch(url,opt));

  let activeTab = 'growth'; 
  let abortController;
  
  let rekapDataCache = []; 
  let rekapGtCache = null;
  
  let detailDataCache = [];
  let detailGtCache = 0;

  let promoPieChart = null;
  let promoBarChart = null;

  let currentDetailParams = {};
  let currentDetailPage = 1; let currentDetailTotalPages = 1;
  const detailLimit = 20; 
  let userKodeGlobal = '000';
  let defaultClosingGrowth = '';

  // 🔥 STATE SORTING 🔥
  let sortMainCol = '';
  let sortMainAsc = true;
  let sortDetailCol = '';
  let sortDetailAsc = true;

  const fmtSingkat = (n) => {
      let num = Math.abs(Number(n) || 0);
      let sign = n < 0 ? '-' : '';
      if (num >= 1e12) return sign + (num / 1e12).toFixed(2).replace('.', ',') + ' T';
      if (num >= 1e9) return sign + (num / 1e9).toFixed(2).replace('.', ',') + ' M';
      if (num >= 1e6) return sign + (num / 1e6).toFixed(2).replace('.', ',') + ' Jt';
      return sign + new Intl.NumberFormat('id-ID').format(Math.round(num));
  };

  // Helper Icon Sorting
  const getSortIcon = (col, currentCol, asc) => {
      if (col !== currentCol) return '<span class="opacity-30 text-[8px] md:text-[10px] ml-1.5 font-sans">↕</span>';
      return asc ? '<span class="text-blue-600 ml-1.5 text-[10px] md:text-[11px] font-sans">▲</span>' : '<span class="text-blue-600 ml-1.5 text-[10px] md:text-[11px] font-sans">▼</span>';
  };

  window.addEventListener('DOMContentLoaded', async () => {
      const user = (window.getUser && window.getUser()) || null;
      let uKode = user?.kode ? String(user.kode).padStart(3, '0') : '000';
      if(uKode === '099') uKode = '000'; 
      
      userKodeGlobal = uKode;
      window.currentUser = { kode: uKode };

      const d = await getLastHarianData(); 
      if(d) {
          defaultClosingGrowth = d.last_closing;
          document.getElementById('closing_date').value = defaultClosingGrowth;
          document.getElementById('harian_date').value  = d.last_created;
      } else {
          const now = new Date();
          defaultClosingGrowth = `${now.getFullYear() - 1}-12-31`;
          document.getElementById('closing_date').value = defaultClosingGrowth;
          document.getElementById('harian_date').value = now.toISOString().split('T')[0];
      }
      
      await populateAreaOptions(userKodeGlobal);
      switchTab('growth');
  });

  async function populateAreaOptions(userKode){
      const el = document.getElementById('opt_area');
      if(userKode !== '000'){
          try {
              const res = await apiCall(API_KODE, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({type:'kode_kantor'}) });
              const json = await res.json();
              const myKantor = (json.data||[]).find(x => String(x.kode_kantor).padStart(3,'0') === userKode);
              const nama = myKantor ? myKantor.nama_kantor : `CABANG ${userKode}`;
              el.innerHTML = `<option value="CABANG|${userKode}">${userKode} - ${nama}</option>`;
          } catch(e) {
              el.innerHTML = `<option value="CABANG|${userKode}">CABANG ${userKode}</option>`;
          }
          el.value = `CABANG|${userKode}`;
          el.disabled = true;
          return;
      }

      try {
          const res = await apiCall(API_KODE, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({type:'kode_kantor'}) });
          const json = await res.json();
          let html = `<option value="ALL|ALL">Konsolidasi (Seluruh Cabang)</option>`;
          html += `<optgroup label="Berdasarkan Koordinator Wilayah">`;
          html += `<option value="KORWIL|SEMARANG">Korwil Semarang</option>`;
          html += `<option value="KORWIL|SOLO">Korwil Solo</option>`;
          html += `<option value="KORWIL|BANYUMAS">Korwil Banyumas</option>`;
          html += `<option value="KORWIL|PEKALONGAN">Korwil Pekalongan</option>`;
          html += `</optgroup>`;
          html += `<optgroup label="Berdasarkan Cabang">`;
          (json.data||[]).filter(x => x.kode_kantor && x.kode_kantor !== '000')
              .sort((a,b) => String(a.kode_kantor).localeCompare(b.kode_kantor))
              .forEach(it => { html += `<option value="CABANG|${String(it.kode_kantor).padStart(3,'0')}">${String(it.kode_kantor).padStart(3,'0')} - ${it.nama_kantor}</option>`; });
          html += `</optgroup>`;
          el.innerHTML = html;
          el.disabled = false;
      } catch(e){ el.innerHTML = `<option value="ALL|ALL">Error Load</option>`; }
  }

  function switchTab(tab) {
      activeTab = tab;
      
      const tabGrowth = document.getElementById('tab-growth');
      const tabPromo  = document.getElementById('tab-promo');
      const titleContainer = document.getElementById('header-title-container');
      const loaderUtama = document.getElementById('loadingUtama');
      
      const wrapArea = document.getElementById('wrap-area');
      const divider = document.getElementById('divider-filter');
      const elClosing = document.getElementById('closing_date');

      if (tab === 'growth') {
          tabGrowth.className = "pb-1.5 md:pb-2.5 font-extrabold text-[10px] md:text-sm uppercase transition border-b-[3px] border-blue-600 text-blue-700 whitespace-nowrap";
          tabPromo.className  = "pb-1.5 md:pb-2.5 font-extrabold text-[10px] md:text-sm uppercase transition border-b-[3px] border-transparent text-slate-400 hover:text-slate-600 whitespace-nowrap";
          
          if(wrapArea) wrapArea.style.display = 'none';       
          if(divider) divider.style.display = 'none';
          
          elClosing.value = defaultClosingGrowth;
          loaderUtama.className = "hidden absolute inset-0 bg-white/80 z-[100] flex flex-col items-center justify-center text-blue-600 backdrop-blur-sm transition-colors";
          document.getElementById('content-promo').style.display = 'none';
          document.getElementById('content-growth').style.display = 'flex';

          titleContainer.innerHTML = `
              <h1 class="text-[14px] md:text-xl font-bold text-slate-800 flex items-center gap-1.5 mb-0.5 leading-none px-1 md:px-0 truncate">
                  <span class="p-1 md:p-1.5 bg-blue-600 rounded-md shadow-sm text-white shrink-0"><svg class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg></span>
                  <span class="truncate">Rekap Realisasi & Growth</span>
              </h1>
              <p class="text-[8px] md:text-[10px] text-slate-500 italic ml-6 md:ml-8 px-1 md:px-0 truncate">*Data Growth = Realisasi Baru - Run Off</p>
          `;
          setupHeaderUtama(userKodeGlobal); 
      } else {
          tabPromo.className  = "pb-1.5 md:pb-2.5 font-extrabold text-[10px] md:text-sm uppercase transition border-b-[3px] border-pink-600 text-pink-700 whitespace-nowrap";
          tabGrowth.className = "pb-1.5 md:pb-2.5 font-extrabold text-[10px] md:text-sm uppercase transition border-b-[3px] border-transparent text-slate-400 hover:text-slate-600 whitespace-nowrap";
          
          if(wrapArea) wrapArea.style.display = 'flex';       
          if(divider) divider.style.display = 'block';

          elClosing.value = AWAL_PROMO_DATE;
          loaderUtama.className = "hidden absolute inset-0 bg-white/80 z-[100] flex flex-col items-center justify-center text-pink-600 backdrop-blur-sm transition-colors";
          document.getElementById('content-growth').style.display = 'none';
          document.getElementById('content-promo').style.display = 'flex';

          titleContainer.innerHTML = `
              <h1 class="text-[14px] md:text-xl font-bold text-slate-800 flex items-center gap-1.5 mb-0.5 leading-none px-1 md:px-0 truncate">
                  <span class="p-1 md:p-1.5 bg-pink-600 rounded-md shadow-sm text-white shrink-0"><svg class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg></span>
                  <span class="truncate">Analitik Promo vs Non-Promo</span>
              </h1>
              <p class="text-[8px] md:text-[10px] text-slate-500 italic ml-6 md:ml-8 font-bold text-pink-700 px-1 md:px-0 truncate">*Promo Ramadhan Dan Idul Fitri</p>
          `;
      }
      fetchRekap();
  }

  function quickDatePromo(tipe) {
      const elHarian = document.getElementById('harian_date');
      const elClosing = document.getElementById('closing_date');
      
      const dateHarian = new Date(elHarian.value);
      if (isNaN(dateHarian)) return;

      if (tipe === '7') {
          dateHarian.setDate(dateHarian.getDate() - 7);
          elClosing.value = dateHarian.toISOString().split('T')[0];
      } else if (tipe === '14') {
          dateHarian.setDate(dateHarian.getDate() - 14);
          elClosing.value = dateHarian.toISOString().split('T')[0];
      } else if (tipe === '30') {
          dateHarian.setDate(dateHarian.getDate() - 30);
          elClosing.value = dateHarian.toISOString().split('T')[0];
      } else if (tipe === 'bulan_ini') {
          const firstDay = new Date(dateHarian.getFullYear(), dateHarian.getMonth(), 1);
          elClosing.value = firstDay.toISOString().split('T')[0];
      } else if (tipe === 'awal_promo') {
          elClosing.value = AWAL_PROMO_DATE;
      }
      fetchRekap();
  }

  async function getLastHarianData(){ 
      try{ const r=await apiCall(API_DATE); const j=await r.json(); return j.data||null; }catch{ return null; } 
  }

  // 🔥 RENDER THEAD UTAMA + ONCLICK SORTING 🔥
  function setupHeaderUtama(userKode) {
      if (activeTab !== 'growth') return; 

      const th = document.getElementById('headUtama');
      let thContent = `<tr>`;
      
      if (userKode === '000') {
          thContent += `
              <th class="freeze-col-1 w-[40px] md:w-[60px] min-w-[40px] md:min-w-[60px] max-w-[40px] md:max-w-[60px] border-r border-b border-slate-300 align-middle uppercase text-center hidden md:table-cell text-slate-800 cursor-pointer hover:bg-[#c5e1a5] transition" onclick="sortMainData('kode_kantor', 'string')">
                  <div class="flex items-center justify-center">KODE${getSortIcon('kode_kantor', sortMainCol, sortMainAsc)}</div>
              </th>
              <th class="freeze-col-2 min-w-[120px] md:min-w-[200px] border-r border-b border-slate-300 align-middle uppercase pl-2 md:pl-5 text-slate-800 text-left cursor-pointer hover:bg-[#c5e1a5] transition" onclick="sortMainData('nama_kantor', 'string')">
                  <div class="flex items-center">NAMA KANTOR${getSortIcon('nama_kantor', sortMainCol, sortMainAsc)}</div>
              </th>
          `;
      } else {
          thContent += `
              <th class="freeze-col-1 min-w-[120px] md:min-w-[200px] border-r border-b border-slate-300 align-middle uppercase pl-2 md:pl-5 text-slate-800 text-left border-t-0 rounded-tl-lg cursor-pointer hover:bg-[#c5e1a5] transition" onclick="sortMainData('nama_kantor', 'string')">
                  <div class="flex items-center">NAMA KANTOR${getSortIcon('nama_kantor', sortMainCol, sortMainAsc)}</div>
              </th>
          `;
      }

      thContent += `
              <th class="px-1.5 md:px-3 border-r border-b border-slate-300 align-middle text-center w-[50px] md:w-[80px] uppercase text-slate-800 bg-[#dcedc8] cursor-pointer hover:bg-[#c5e1a5] transition" onclick="sortMainData('noa_realisasi', 'number')">
                  <div class="flex items-center justify-center">NOA${getSortIcon('noa_realisasi', sortMainCol, sortMainAsc)}</div>
              </th>
              <th class="px-2 md:px-3 border-r border-b border-slate-300 align-middle text-right w-[100px] md:w-[150px] uppercase text-slate-800 bg-[#dcedc8] cursor-pointer hover:bg-[#c5e1a5] transition" onclick="sortMainData('total_realisasi', 'number')">
                  <div class="flex items-center justify-end">REALISASI${getSortIcon('total_realisasi', sortMainCol, sortMainAsc)}</div>
              </th>
              <th class="px-2 md:px-3 border-r border-b border-slate-300 align-middle text-right w-[90px] md:w-[140px] uppercase text-emerald-800 bg-[#dcedc8] cursor-pointer hover:bg-[#c5e1a5] transition" onclick="sortMainData('pelunasan', 'number')">
                  <div class="flex items-center justify-end">PELUNASAN${getSortIcon('pelunasan', sortMainCol, sortMainAsc)}</div>
              </th>
              <th class="px-2 md:px-3 border-r border-b border-slate-300 align-middle text-right w-[100px] md:w-[150px] uppercase text-blue-800 bg-[#dcedc8] cursor-pointer hover:bg-[#c5e1a5] transition" onclick="sortMainData('angsuran_murni', 'number')">
                  <div class="flex items-center justify-end">ANGSURAN${getSortIcon('angsuran_murni', sortMainCol, sortMainAsc)}</div>
              </th>
              <th class="px-2 md:px-3 border-r border-b border-slate-300 align-middle text-right w-[90px] md:w-[150px] uppercase text-orange-800 bg-[#dcedc8] cursor-pointer hover:bg-[#c5e1a5] transition" onclick="sortMainData('total_run_off', 'number')">
                  <div class="flex items-center justify-end">RUN OFF${getSortIcon('total_run_off', sortMainCol, sortMainAsc)}</div>
              </th>
              <th class="px-2 md:px-3 border-b border-slate-300 align-middle text-right w-[100px] md:w-[150px] uppercase text-slate-800 bg-[#dcedc8] cursor-pointer hover:bg-[#c5e1a5] transition" onclick="sortMainData('growth', 'number')">
                  <div class="flex items-center justify-end">GROWTH${getSortIcon('growth', sortMainCol, sortMainAsc)}</div>
              </th>
          </tr>
          <tr id="rowTotalAtas" class="text-[10px] md:text-xs font-extrabold tracking-wide head-lapis-2"></tr>
      `;
      th.innerHTML = thContent;
  }

  // 🔥 FUNGSI SORTING MAIN TABLE 🔥
  window.sortMainData = function(col, type) {
      if (!rekapDataCache || rekapDataCache.length === 0) return;

      if (sortMainCol === col) {
          sortMainAsc = !sortMainAsc;
      } else {
          sortMainCol = col;
          sortMainAsc = true;
      }

      rekapDataCache.sort((a, b) => {
          let valA = a[col];
          let valB = b[col];

          if (type === 'number') {
              valA = parseFloat(valA) || 0;
              valB = parseFloat(valB) || 0;
              return sortMainAsc ? valA - valB : valB - valA;
          } else {
              valA = String(valA || '').toLowerCase();
              valB = String(valB || '').toLowerCase();
              if (valA < valB) return sortMainAsc ? -1 : 1;
              if (valA > valB) return sortMainAsc ? 1 : -1;
              return 0;
          }
      });

      setupHeaderUtama(userKodeGlobal);
      renderTableGrowth(rekapDataCache, rekapGtCache, userKodeGlobal);
  }

  async function fetchRekap(){
      const loadG = document.getElementById('loadingUtama');
      const loadP = document.getElementById('loadingPromo');
      
      if(abortController) abortController.abort(); abortController = new AbortController();
      
      if (activeTab === 'growth') loadG.classList.remove('hidden');
      else loadP.classList.remove('hidden');

      rekapDataCache = [];
      rekapGtCache = null;
      sortMainCol = ''; // reset sort
      sortMainAsc = true;

      try {
          let reqCabang = "000"; 
          let reqKorwil = "";
          
          if (userKodeGlobal === '000' && activeTab === 'promo') {
              const areaVal = document.getElementById('opt_area').value; 
              if (areaVal) {
                  const arr = areaVal.split('|');
                  if (arr[0] === 'KORWIL') reqKorwil = arr[1];
                  else if (arr[0] === 'CABANG') reqCabang = arr[1];
                  else if (arr[0] === 'ALL') reqCabang = "000";
              }
          } else if (userKodeGlobal !== '000') {
              reqCabang = userKodeGlobal;
          }

          let payload;
          if (activeTab === 'growth') {
              payload = { 
                  type: 'rekap_realisasi_growth', 
                  closing_date: document.getElementById('closing_date').value, 
                  harian_date: document.getElementById('harian_date').value, 
                  kode_kantor: userKodeGlobal === '000' ? null : userKodeGlobal,
                  korwil: null
              };
          } else {
              payload = { 
                  type: 'chart_promo', 
                  closing_date: document.getElementById('closing_date').value,
                  harian_date: document.getElementById('harian_date').value,
                  kode_kantor: reqCabang, 
                  korwil: reqKorwil 
              };
          }

          const res = await apiCall(API_URL, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload), signal: abortController.signal });
          const json = await res.json();
          if(json.status !== 200) throw new Error(json.message);
          
          if (activeTab === 'growth') {
              rekapDataCache = Array.isArray(json.data) ? json.data : (json.data?.data || []);
              rekapGtCache = json.data?.grand_total || json.grand_total || {};
              
              setupHeaderUtama(userKodeGlobal);
              renderTableGrowth(rekapDataCache, rekapGtCache, userKodeGlobal);
          } else {
              rekapDataCache = json.data?.trend || [];
              renderChartPromo(json.data || {});
          }
          
      } catch(err) { 
          if(err.name !== 'AbortError') {
              if (activeTab === 'growth') {
                  const tb = document.getElementById('bodyUtama');
                  tb.innerHTML=`<tr><td colspan="8" class="py-10 text-center text-red-500 font-bold text-xs">${err.message}</td></tr>`; 
              } else {
                  console.error(err);
              }
          }
      } finally { 
          loadG.classList.add('hidden'); loadP.classList.add('hidden');
      }
  }

  function renderTableGrowth(rows, gt, userKode) {
      const tb = document.getElementById('bodyUtama'); const trTot = document.getElementById('rowTotalAtas');
      const colSpan = userKode === '000' ? 8 : 7;
      if(rows.length === 0){ tb.innerHTML = `<tr><td colspan="${colSpan}" class="py-10 text-center text-slate-500 text-[10px]">Tidak ada data.</td></tr>`; return; }

      let html = '';
      rows.forEach(r => {
          const noa_real  = parseInt(r.noa_realisasi) || 0;
          const realisasi = parseFloat(r.total_realisasi) || 0;
          const pelunasan = parseFloat(r.pelunasan) || 0;
          const ang_murni = parseFloat(r.angsuran_murni) || 0;
          const run_off   = parseFloat(r.total_run_off) || 0;
          const growth    = parseFloat(r.growth) || 0;
          const growthColor = growth >= 0 ? 'text-blue-700' : 'text-red-600';

          let rowHtml = `<tr class="transition h-[40px] md:h-[46px] border-b border-slate-100">`;
          
          if (userKode === '000') {
              rowHtml += `
                <td class="freeze-col-1 w-[40px] md:w-[60px] min-w-[40px] md:min-w-[60px] max-w-[40px] md:max-w-[60px] px-1.5 md:px-4 py-1.5 text-center font-mono font-bold text-slate-500 hidden md:table-cell">${r.kode_kantor}</td>
                <td class="freeze-col-2 px-2 md:px-5 py-1.5 font-bold text-slate-700 text-left truncate" title="${r.nama_kantor}">${r.nama_kantor}</td>
              `;
          } else {
              rowHtml += `
                <td class="freeze-col-1 px-2 md:px-5 py-1.5 font-bold text-slate-700 text-left truncate" title="${r.nama_kantor}">${r.nama_kantor}</td>
              `;
          }

          rowHtml += `
                <td class="px-1.5 md:px-4 py-1.5 text-center font-extrabold text-blue-600 cursor-pointer hover:bg-blue-50 hover:text-blue-800 transition border-r border-slate-100 bg-white" onclick="initModalDetail('${r.kode_kantor}', '${r.nama_kantor}')" title="Klik untuk lihat detail">${fmt(noa_real)}</td>
                <td class="px-2 md:px-4 py-1.5 text-right font-mono font-bold text-slate-800 border-r border-slate-100 bg-white">${fmt(realisasi)}</td>
                <td class="px-2 md:px-4 py-1.5 text-right font-mono font-bold text-emerald-700 border-r border-slate-100 bg-white">${fmt(pelunasan)}</td>
                <td class="px-2 md:px-4 py-1.5 text-right font-mono font-bold text-blue-700 border-r border-slate-100 bg-white">${fmt(ang_murni)}</td>
                <td class="px-2 md:px-4 py-1.5 text-right font-mono font-bold text-orange-700 border-r border-slate-100 bg-white">${fmt(run_off)}</td>
                <td class="px-2 md:px-4 py-1.5 text-right font-mono font-extrabold ${growthColor} bg-white">${fmt(growth)}</td>
            </tr>`;
          html += rowHtml;
      });
      tb.innerHTML = html;

      if(gt && Object.keys(gt).length > 0) {
          const gGrowth = parseFloat(gt.growth) || 0;
          const tGrowthColor = gGrowth >= 0 ? 'text-blue-800' : 'text-red-700';

          if (userKode === '000') {
              trTot.innerHTML = `
                  <th class="freeze-col-1 w-[40px] md:w-[60px] min-w-[40px] md:min-w-[60px] max-w-[40px] md:max-w-[60px] px-1.5 md:px-4 border-r border-blue-300 text-center text-blue-900 font-extrabold hidden md:table-cell">ALL</th>
                  <th class="freeze-col-2 px-2 md:px-5 border-r border-blue-300 text-left uppercase tracking-widest font-extrabold text-[9px] md:text-sm text-blue-900">GRAND TOTAL</th>
              `;
          } else {
              trTot.innerHTML = `
                  <th class="freeze-col-1 px-2 md:px-5 border-r border-blue-300 text-left uppercase tracking-widest font-extrabold text-[9px] md:text-sm text-blue-900">GRAND TOTAL</th>
              `;
          }

          trTot.innerHTML += `
              <th class="px-1.5 md:px-4 border-r border-blue-300 text-center font-extrabold text-blue-900 align-middle bg-[#eff6ff]">${fmt(gt.noa_realisasi)}</th>
              <th class="px-2 md:px-4 border-r border-blue-300 text-right font-mono font-bold text-blue-900 align-middle bg-[#eff6ff]" title="Rp ${fmt(gt.total_realisasi)}">${fmt(gt.total_realisasi)}</th>
              <th class="px-2 md:px-4 border-r border-blue-300 text-right font-mono font-bold text-emerald-800 align-middle bg-[#eff6ff]" title="Rp ${fmt(gt.pelunasan)}">${fmt(gt.pelunasan)}</th>
              <th class="px-2 md:px-4 border-r border-blue-300 text-right font-mono font-bold text-blue-800 align-middle bg-[#eff6ff]" title="Rp ${fmt(gt.angsuran_murni)}">${fmt(gt.angsuran_murni)}</th>
              <th class="px-2 md:px-4 border-r border-blue-300 text-right font-mono font-bold text-orange-800 align-middle bg-[#eff6ff]" title="Rp ${fmt(gt.total_run_off)}">${fmt(gt.total_run_off)}</th>
              <th class="px-2 md:px-4 text-right font-mono font-extrabold ${tGrowthColor} align-middle bg-[#eff6ff]" title="Rp ${fmt(gGrowth)}">${fmt(gGrowth)}</th>
          `;
      }
  }

  function renderChartPromo(data) {
      const totals = data.totals || {promo_nominal:0, non_promo_nominal:0, promo_noa:0, non_promo_noa:0};
      const trend = data.trend || [];

      document.getElementById('txt-tot-promo').innerText = fmtSingkat(totals.promo_nominal);
      document.getElementById('txt-tot-promo').title = "Rp " + fmt(totals.promo_nominal);
      document.getElementById('txt-noa-promo').innerText = fmt(totals.promo_noa) + ' NOA';
      
      document.getElementById('txt-tot-nonpromo').innerText = fmtSingkat(totals.non_promo_nominal);
      document.getElementById('txt-tot-nonpromo').title = "Rp " + fmt(totals.non_promo_nominal);
      document.getElementById('txt-noa-nonpromo').innerText = fmt(totals.non_promo_noa) + ' NOA';

      const ctxPie = document.getElementById('pieChart').getContext('2d');
      const ctxBar = document.getElementById('barChart').getContext('2d');

      if(promoPieChart) promoPieChart.destroy();
      if(promoBarChart) promoBarChart.destroy();

      const totalSum = totals.promo_nominal + totals.non_promo_nominal;

      promoPieChart = new Chart(ctxPie, {
          type: 'doughnut',
          data: {
              labels: ['Promo', 'Non-Promo'],
              datasets: [{
                  data: [totals.promo_nominal, totals.non_promo_nominal],
                  backgroundColor: ['#ec4899', '#cbd5e1'], 
                  borderWidth: 0,
                  hoverOffset: 4
              }]
          },
          options: {
              responsive: true, maintainAspectRatio: false,
              plugins: {
                  legend: { position: 'bottom', labels: {font:{size:9, family:'monospace'}, usePointStyle: true, padding: 8} },
                  tooltip: {
                      callbacks: {
                          label: function(ctx) {
                              let label = ctx.label || '';
                              if (label) label += ': ';
                              label += 'Rp ' + fmtSingkat(ctx.raw);
                              if(totalSum > 0) {
                                  let pct = ((ctx.raw / totalSum) * 100).toFixed(1) + '%';
                                  label += ' (' + pct + ')';
                              }
                              return label;
                          }
                      }
                  }
              }
          }
      });

      const labels = trend.map(t => t.tanggal);
      const dataPromo = trend.map(t => t.promo_nominal);
      const dataNonPromo = trend.map(t => t.non_promo_nominal);
      const dataPromoNoa = trend.map(t => t.promo_noa);
      const dataNonPromoNoa = trend.map(t => t.non_promo_noa);

      promoBarChart = new Chart(ctxBar, {
          type: 'bar',
          data: {
              labels: labels,
              datasets: [
                  { label: 'Promo', data: dataPromo, backgroundColor: '#ec4899', borderRadius: 2 },
                  { label: 'Non-Promo', data: dataNonPromo, backgroundColor: '#cbd5e1', borderRadius: 2 }
              ]
          },
          options: {
              responsive: true, maintainAspectRatio: false,
              interaction: { mode: 'index', intersect: false },
              scales: {
                  x: { stacked: false, grid: { display: false }, ticks: { font: {size: 8} } },
                  y: { 
                      stacked: false, border: { display: false },
                      ticks: {
                          font: {size: 8},
                          callback: function(value) {
                              if(value >= 1000000000) return (value/1000000000).toFixed(0) + ' M';
                              if(value >= 1000000) return (value/1000000).toFixed(0) + ' Jt';
                              return value;
                          }
                      }
                  }
              },
              plugins: {
                  legend: { position: 'bottom', labels: {font:{size:9, family:'monospace'}, usePointStyle: true, padding: 8} },
                  tooltip: { 
                      callbacks: { 
                          label: function(ctx) { 
                              let nominalText = ctx.dataset.label + ': Rp ' + fmtSingkat(ctx.raw); 
                              let noaVal = ctx.datasetIndex === 0 ? dataPromoNoa[ctx.dataIndex] : dataNonPromoNoa[ctx.dataIndex];
                              return `${nominalText} (${noaVal} NOA)`; 
                          } 
                      } 
                  }
              }
          }
      });
  }

  // --- EXPORT EXCEL ---
  window.exportExcelRekap = function() {
      if(!rekapDataCache || rekapDataCache.length === 0) return alert("Tidak ada data rekap untuk didownload.");

      let csv = "";
      if (activeTab === 'growth') {
          csv = "Kode Kantor\tNama Kantor\tNOA Realisasi\tTotal Realisasi\tPelunasan\tAngsuran Murni\tTotal Run Off\tGrowth\n";
          rekapDataCache.forEach(r => {
              csv += `'${r.kode_kantor}\t${r.nama_kantor||''}\t${r.noa_realisasi}\t${Math.round(r.total_realisasi||0)}\t${Math.round(r.pelunasan||0)}\t${Math.round(r.angsuran_murni||0)}\t${Math.round(r.total_run_off||0)}\t${Math.round(r.growth||0)}\n`;
          });
          downloadExcel(csv, 'Rekap_Realisasi_Growth.xls');
      } else {
          csv = "Tanggal\tPromo Nominal\tNon Promo Nominal\tPromo NOA\tNon Promo NOA\n";
          rekapDataCache.forEach(r => {
              csv += `${r.tanggal}\t${Math.round(r.promo_nominal||0)}\t${Math.round(r.non_promo_nominal||0)}\t${r.promo_noa}\t${r.non_promo_noa}\n`;
          });
          downloadExcel(csv, 'Trend_Harian_Promo.xls');
      }
  }

  function downloadExcel(csvContent, filename) {
      const blob = new Blob([csvContent], { type: 'application/vnd.ms-excel' });
      const a = document.createElement('a');
      a.href = window.URL.createObjectURL(blob);
      a.download = filename; 
      a.click();
  }

  // ==========================================
  // 🔥 MODAL DETAIL LOGIC + SORTING 🔥
  // ==========================================
  
  // Setup Header Modal dengan Icon Sorting
  function renderModalHeader() {
      const mHead = document.getElementById('headDetail');
      mHead.innerHTML = `
          <tr class="modal-head-1">
              <th class="mod-freeze-rek hidden md:table-cell px-2 md:px-3 py-1.5 md:py-2 border-b border-r border-slate-300 w-[100px] md:w-[120px] rounded-tl-lg text-left md:text-center cursor-pointer hover:bg-slate-200 transition select-none" onclick="sortDetailData('no_rekening', 'string')">
                  <div class="flex items-center justify-start md:justify-center">REKENING ${getSortIcon('no_rekening', sortDetailCol, sortDetailAsc)}</div>
              </th>
              <th class="mod-freeze-nas px-2 md:px-4 py-1.5 md:py-2 border-b border-r border-slate-300 w-[160px] md:w-[250px] shadow-[2px_0_4px_-2px_rgba(0,0,0,0.1)] text-left md:text-center cursor-pointer hover:bg-slate-200 transition select-none" onclick="sortDetailData('nama_nasabah', 'string')">
                  <div class="flex items-center justify-start md:justify-center">NAMA NASABAH ${getSortIcon('nama_nasabah', sortDetailCol, sortDetailAsc)}</div>
              </th>
              <th class="px-2 md:px-3 py-1.5 md:py-2 border-b border-r border-slate-300 w-[140px] md:w-[200px] text-center cursor-pointer hover:bg-slate-200 transition select-none" onclick="sortDetailData('alamat', 'string')">
                  <div class="flex items-center justify-center">ALAMAT ${getSortIcon('alamat', sortDetailCol, sortDetailAsc)}</div>
              </th>
              <th class="px-2 md:px-3 py-1.5 md:py-2 border-b border-r border-slate-300 w-[80px] md:w-[120px] text-center cursor-pointer hover:bg-slate-200 transition select-none" onclick="sortDetailData('nama_kankas', 'string')">
                  <div class="flex items-center justify-center">KANKAS ${getSortIcon('nama_kankas', sortDetailCol, sortDetailAsc)}</div>
              </th>
              <th class="px-2 md:px-3 py-1.5 md:py-2 border-b border-r border-slate-300 w-[100px] md:w-[140px] text-center cursor-pointer hover:bg-slate-200 transition select-none" onclick="sortDetailData('nama_ao', 'string')">
                  <div class="flex items-center justify-center">NAMA AO ${getSortIcon('nama_ao', sortDetailCol, sortDetailAsc)}</div>
              </th>
              <th class="px-2 md:px-3 py-1.5 md:py-2 border-b border-r border-slate-300 w-[80px] md:w-[100px] text-center cursor-pointer hover:bg-slate-200 transition select-none" onclick="sortDetailData('tgl_realisasi', 'string')">
                  <div class="flex items-center justify-center">TGL REK ${getSortIcon('tgl_realisasi', sortDetailCol, sortDetailAsc)}</div>
              </th>
              <th class="px-2 md:px-4 py-1.5 md:py-2 border-b border-slate-300 w-[100px] md:w-[160px] text-right cursor-pointer hover:bg-slate-200 transition select-none" onclick="sortDetailData('plafond', 'number')">
                  <div class="flex items-center justify-end">PLAFON ${getSortIcon('plafond', sortDetailCol, sortDetailAsc)}</div>
              </th>
          </tr>
          <tr id="rowTotalDetailAtas" class="modal-head-2"></tr>
      `;
  }

  // Fungsi Sorting Detail
  window.sortDetailData = function(col, type) {
      if (!detailDataCache || detailDataCache.length === 0) return;

      if (sortDetailCol === col) {
          sortDetailAsc = !sortDetailAsc;
      } else {
          sortDetailCol = col;
          sortDetailAsc = true;
      }

      detailDataCache.sort((a, b) => {
          let valA = a[col];
          let valB = b[col];

          if (type === 'number') {
              valA = parseFloat(valA) || 0;
              valB = parseFloat(valB) || 0;
              return sortDetailAsc ? valA - valB : valB - valA;
          } else {
              valA = String(valA || '').toLowerCase();
              valB = String(valB || '').toLowerCase();
              if (valA < valB) return sortDetailAsc ? -1 : 1;
              if (valA > valB) return sortDetailAsc ? 1 : -1;
              return 0;
          }
      });

      renderModalHeader();
      renderTableDetailBody(detailDataCache, detailGtCache);
  }

  async function initModalDetail(kode, nama) {
      if (userKodeGlobal !== '000' && userKodeGlobal !== '099' && String(kode).padStart(3, '0') !== userKodeGlobal) {
          alert(`AKSES DITOLAK!\nAnda hanya memiliki izin untuk melihat detail Cabang ${userKodeGlobal}.`);
          return;
      }

      currentDetailParams = { kode_kantor: kode, kode_kankas: null, kode_ao: null };

      const modal = document.getElementById('modalDetail');
      modal.classList.remove('hidden'); modal.classList.add('flex');
      
      const mTitle = document.getElementById('modal-title-container');
      mTitle.innerHTML = `
          <h3 class="font-bold text-slate-800 flex items-center gap-1.5 text-[12px] md:text-xl leading-none px-1 md:px-0 truncate">
              <span class="w-1.5 md:w-2 h-4 md:h-6 bg-blue-600 rounded-full hidden md:block shrink-0"></span> 
              <span class="truncate">Detail Realisasi</span>
          </h3>
          <p class="text-[9px] md:text-sm text-slate-500 mt-1 md:ml-4 font-mono font-medium leading-none px-1 md:px-0 truncate">Cabang: ${nama}</p>
      `;
      
      sortDetailCol = ''; // reset sort
      sortDetailAsc = true;
      renderModalHeader();
      
      document.getElementById('search_nasabah').value = '';
      document.getElementById('filter_kankas_modal').innerHTML = '<option value="">Semua Kankas</option>';
      document.getElementById('filter_ao_modal').innerHTML = '<option value="">Semua AO</option>';

      await Promise.all([
          loadKankasModal(kode),
          loadAOModalDropdown(kode)
      ]);

      fetchDetail(1);
  }

  // Live Search JS
  window.filterTableDetail = function() {
      const input = document.getElementById("search_nasabah");
      const filter = input.value.toLowerCase();
      const tbody = document.getElementById("bodyDetail");
      const trs = tbody.getElementsByTagName("tr");

      for (let i = 0; i < trs.length; i++) {
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

  async function loadKankasModal(kode_cabang) {
      const el = document.getElementById('filter_kankas_modal');
      if(!kode_cabang) return;
      try {
          const r = await apiCall(API_KODE, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({type: 'kode_kankas', kode_kantor: kode_cabang}) });
          const j = await r.json();
          if(j.data && Array.isArray(j.data)) {
              j.data.forEach(x => { el.add(new Option(x.deskripsi_group1 || x.kode_group1, x.kode_group1)); });
          }
      } catch(e) {}
  }

  async function loadAOModalDropdown(kode_cabang) {
      const el = document.getElementById('filter_ao_modal');
      if(!kode_cabang) return;
      try {
          const r = await apiCall(API_KODE, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({type: 'kode_ao_kredit', kode_kantor: kode_cabang}) });
          const j = await r.json();
          if(j.data && Array.isArray(j.data)) {
              j.data.forEach(x => { 
                  const rawName = x.nama_ao || x.kode_group2;
                  el.add(new Option(rawName, x.kode_group2)); 
              });
          }
      } catch(e) {}
  }

  window.onKankasChange = async function() {
      const kodeCabang = currentDetailParams.kode_kantor;
      const elAO = document.getElementById('filter_ao_modal');
      elAO.innerHTML = '<option value="">Semua AO</option>';
      elAO.value = '';
      
      await loadAOModalDropdown(kodeCabang); 
      fetchDetail(1);
  }

  function changePage(step) {
      const n = currentDetailPage + step;
      if (n > 0 && n <= currentDetailTotalPages) fetchDetail(n);
  }

  async function fetchDetail(page = 1) {
      const l = document.getElementById('loadingModal'); 
      const info = document.getElementById('pageInfo');
      
      l.classList.remove('hidden'); 
      document.getElementById('bodyDetail').innerHTML = ''; 
      document.getElementById('rowTotalDetailAtas').innerHTML = '';
      
      currentDetailParams.kode_kankas = document.getElementById('filter_kankas_modal').value;
      currentDetailParams.kode_ao = document.getElementById('filter_ao_modal').value; 

      try {
          const payload = {
              type: 'detail_realisasi_growth',
              closing_date: document.getElementById('closing_date').value,
              harian_date: document.getElementById('harian_date').value,
              kode_kantor: currentDetailParams.kode_kantor,
              kode_kankas: currentDetailParams.kode_kankas,
              kode_ao: currentDetailParams.kode_ao,
              page: page, limit: detailLimit
          };

          const res = await apiCall(API_URL, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          const json = await res.json();
          
          detailDataCache = Array.isArray(json.data) ? json.data : (json.data?.data || []);
          const meta = json.data?.pagination || json.pagination || { total_records: detailDataCache.length, total_pages: 1 };
          
          currentDetailPage = page; currentDetailTotalPages = meta.total_pages;

          if(detailDataCache.length === 0) {
              document.getElementById('bodyDetail').innerHTML = `<tr><td colspan="7" class="py-10 text-center text-slate-400 italic text-[10px] md:text-sm">Tidak ada data detail.</td></tr>`;
              info.innerText = `0 data`; return;
          }

          // Hitung Total Plafond Halaman Ini
          detailGtCache = detailDataCache.reduce((sum, r) => sum + parseFloat(r.plafond || 0), 0);

          // Reset sorting saat ganti page atau filter
          sortDetailCol = ''; 
          sortDetailAsc = true;
          renderModalHeader();

          renderTableDetailBody(detailDataCache, detailGtCache);

          const start = ((page-1)*detailLimit)+1; const end = Math.min(page*detailLimit, meta.total_records);
          info.innerText = `Hal ${page} / ${meta.total_pages} (${start}-${end} dari ${fmt(meta.total_records)})`;
          document.getElementById('btnPrev').disabled = page <= 1;
          document.getElementById('btnNext').disabled = page >= meta.total_pages;

      } catch(err){ console.error(err); } finally { l.classList.add('hidden'); }
  }

  function renderTableDetailBody(list, t_plafond) {
      const tb = document.getElementById('bodyDetail');
      const trTot = document.getElementById('rowTotalDetailAtas');
      
      let html = '';
      list.forEach(r => {
          const alamatLengkap = r.alamat || '-';
          const alamatPendek = alamatLengkap.length > 20 ? alamatLengkap.substring(0, 20) + '...' : alamatLengkap;

          html += `<tr class="transition h-[40px] md:h-[46px] group border-b border-slate-100 hover:bg-slate-50 text-left md:text-center">
                <td class="mod-freeze-rek hidden md:table-cell px-2 md:px-3 py-1.5 font-mono text-[9px] md:text-[11px] text-slate-500 border-r border-slate-100 shadow-[inset_-1px_0_0_#e2e8f0] text-left bg-white">${r.no_rekening}</td>
                <td class="mod-freeze-nas px-2 md:px-4 py-1.5 font-bold text-[9.5px] md:text-[11px] text-slate-700 truncate border-r border-slate-100 max-w-[160px] md:max-w-[250px] shadow-[2px_0_4px_-2px_rgba(0,0,0,0.1)] text-left bg-white" title="${r.nama_nasabah}">${r.nama_nasabah}</td>
                <td class="px-2 md:px-3 py-1.5 text-[9px] md:text-[11px] text-slate-500 whitespace-nowrap border-r border-slate-100 text-left bg-white" title="${alamatLengkap}">${alamatPendek}</td>
                <td class="px-2 md:px-3 py-1.5 text-center font-mono text-[9px] md:text-[11px] text-slate-500 border-r border-slate-100 bg-white">${r.nama_kankas||'-'}</td>
                <td class="px-2 md:px-3 py-1.5 text-[9px] md:text-[11px] font-bold text-blue-700 truncate border-r border-slate-100 text-left md:text-center bg-white">${r.nama_ao||'-'}</td>
                <td class="px-2 md:px-3 py-1.5 text-center font-mono text-[9px] md:text-[11px] text-slate-600 border-r border-slate-100 bg-white">${r.tgl_realisasi}</td>
                <td class="px-2 md:px-4 py-1.5 text-right font-mono font-bold text-slate-800 text-[9.5px] md:text-[12px] bg-white">${fmt(r.plafond)}</td>
            </tr>`;
      });
      tb.innerHTML = html;

      trTot.innerHTML = `
          <th class="mod-freeze-rek hidden md:table-cell px-2 md:px-3 border-r border-b border-blue-200 text-center bg-[#eff6ff]">-</th>
          <th class="mod-freeze-nas px-2 md:px-4 border-r border-b border-blue-200 uppercase tracking-widest font-extrabold text-[9px] md:text-[11px] text-blue-900 text-left md:text-center bg-[#eff6ff]">TOTAL HALAMAN INI</th>
          <th class="px-2 md:px-3 border-r border-b border-blue-200 text-center bg-[#eff6ff]">-</th>
          <th class="px-2 md:px-3 border-r border-b border-blue-200 text-center bg-[#eff6ff]">-</th>
          <th class="px-2 md:px-3 border-r border-b border-blue-200 text-center bg-[#eff6ff]">-</th>
          <th class="px-2 md:px-3 border-r border-b border-blue-200 text-center bg-[#eff6ff]">-</th>
          <th class="px-2 md:px-4 border-b border-blue-300 text-right font-mono font-extrabold text-[9.5px] md:text-[12px] text-blue-900 bg-blue-100/60">${fmt(t_plafond)}</th>
      `;

      filterTableDetail();
  }

  async function exportExcelDetail() {
      const btn = document.getElementById('btn-excel-modal'); const txt = btn.innerHTML;
      btn.innerHTML = `<span class="animate-spin inline-block h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>`;
      btn.disabled = true;

      try {
          const payload = {
              type: 'detail_realisasi_growth',
              closing_date: document.getElementById('closing_date').value,
              harian_date: document.getElementById('harian_date').value,
              kode_kantor: currentDetailParams.kode_kantor,
              kode_kankas: document.getElementById('filter_kankas_modal').value,
              kode_ao: document.getElementById('filter_ao_modal').value, 
              page: 1, limit: 10000 
          };
          
          const res = await apiCall(API_URL, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          const json = await res.json();
          const rows = Array.isArray(json.data) ? json.data : (json.data?.data || []);
          if(rows.length === 0) { alert("Tidak ada data."); return; }

          let csv = `No Rekening\tNama Nasabah\tAlamat\tKankas\tNama AO\tTgl Realisasi\tPlafond\n`;
          rows.forEach(r => {
              csv += `'${r.no_rekening}\t${r.nama_nasabah}\t${r.alamat||'-'}\t${r.nama_kankas||'-'}\t${r.nama_ao||'-'}\t${r.tgl_realisasi}\t${Math.round(r.plafond||0)}\n`;
          });
          downloadExcel(csv, `Detail_Realisasi_${currentDetailParams.kode_kantor}.xls`);

      } catch(e) { alert("Gagal export."); } finally { btn.innerHTML = txt; btn.disabled = false; }
  }

  window.closeModal = () => {
      const modal = document.getElementById('modalDetail');
      modal.classList.add('hidden'); modal.classList.remove('flex');
  }
  document.addEventListener('keydown', e => { if(e.key === 'Escape') closeModal(); });
</script>