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

  /* Magic Sticky Table */
  table thead th { position: sticky; box-shadow: inset 0 -1px 0 #cbd5e1; }
  .head-lapis-1 th { top: 0; z-index: 40; height: 50px; }
  .head-lapis-2 th { top: 50px; z-index: 38; height: 50px; border-bottom: 2px solid #cbd5e1; box-shadow: inset 0 -1px 0 #cbd5e1; }
  .head-mod-1 th { top: 0; z-index: 40; height: 46px; }
  .head-mod-2 th { top: 46px; z-index: 39; height: 44px; border-bottom: 2px solid #cbd5e1; box-shadow: inset 0 -1px 0 #cbd5e1; }

  .freeze-col-1 { position: sticky; left: 0; z-index: 20; box-shadow: inset -1px 0 0 #e2e8f0; }
  .freeze-col-2 { position: sticky; left: 0; z-index: 20; box-shadow: inset -1px 0 0 #e2e8f0; }
  @media (min-width: 768px) { .freeze-col-2 { left: 80px; } .mod-freeze-2 { left: 120px; } }

  .head-lapis-1 th.freeze-col-1, .head-mod-1 th.freeze-col-1 { z-index: 50; border-top-left-radius: 8px; }
  .head-lapis-1 th.freeze-col-2, .head-mod-1 th.mod-freeze-2 { z-index: 49; }
  .head-lapis-2 th.freeze-col-1, .head-mod-2 th.freeze-col-1 { z-index: 48; }
  .head-lapis-2 th.freeze-col-2, .head-mod-2 th.mod-freeze-2 { z-index: 47; }

  tbody tr:hover td { cursor: pointer; background-color: #f8fafc !important; }
  tbody tr:hover td.freeze-col-1, tbody tr:hover td.freeze-col-2, tbody tr:hover td.mod-freeze-2 { filter: brightness(0.98); }

  /* Form Inputs */
  .inp { border:1px solid #cbd5e1; border-radius:8px; background:#fff; outline:none; transition: border 0.2s;}
  .inp:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,0.1); }
  .lbl { font-size:10px; color:#475569; font-weight:800; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.05em; display:block; white-space: nowrap;}
  @media (min-width: 768px) { .lbl { font-size:11px; } }
  .field { display:flex; flex-direction:column; }
  .btn-icon { display:inline-flex; align-items:center; justify-content:center; border:none; cursor:pointer; transition: transform 0.2s;}
  .btn-icon:hover { transform:translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
  input[type="date"]::-webkit-inner-spin-button, input[type="date"]::-webkit-calendar-picker-indicator { display: none; -webkit-appearance: none; }
  input[type="date"] { -moz-appearance: textfield; }
</style>

<script>
    window.currentUser = { kode_kantor: (typeof USER_KODE_KANTOR !== 'undefined') ? USER_KODE_KANTOR : '000' };
</script>

<div class="max-w-[1920px] mx-auto px-2 md:px-4 py-4 md:py-6 h-[calc(100vh-80px)] flex flex-col bg-slate-50 font-sans text-slate-800 overflow-hidden">
  
  <div class="flex gap-4 md:gap-8 border-b border-slate-300 mb-4 px-2 overflow-x-auto no-scrollbar shrink-0">
      <button id="tab-growth" onclick="switchTab('growth')" class="pb-2 md:pb-3 font-extrabold text-xs md:text-sm uppercase transition border-b-[3px] border-blue-600 text-blue-700 whitespace-nowrap">REKAP REALISASI & GROWTH</button>
      <button id="tab-promo" onclick="switchTab('promo')" class="pb-2 md:pb-3 font-extrabold text-xs md:text-sm uppercase transition border-b-[3px] border-transparent text-slate-400 hover:text-slate-600 whitespace-nowrap">ANALITIK PROMO VS NON-PROMO</button>
  </div>

  <div class="flex-none mb-4 flex flex-col xl:flex-row justify-between xl:items-start gap-4 w-full">
      <div class="flex flex-col gap-1.5 shrink-0" id="header-title-container"></div>

      <form id="formFilterUtama" class="bg-white p-2 md:p-3 rounded-xl border border-slate-200 shadow-sm flex flex-wrap md:flex-nowrap items-end gap-2 w-full xl:w-auto shrink-0 xl:ml-auto overflow-x-auto no-scrollbar" onsubmit="event.preventDefault(); fetchRekap();">
          
          <div class="field w-[calc(50%-4px)] md:w-[130px] shrink-0" id="wrap-closing">
              <label class="lbl">CLOSING</label>
              <input type="date" id="closing_date" class="inp w-full text-[11px] md:text-sm font-semibold h-[34px] md:h-[38px] px-2 md:px-3 text-slate-700 cursor-pointer" required onclick="try{this.showPicker()}catch(e){}">
          </div>
          
          <div class="field w-[calc(50%-4px)] md:w-[130px] shrink-0">
              <label class="lbl">HARIAN</label>
              <input type="date" id="harian_date" class="inp w-full text-[11px] md:text-sm font-semibold h-[34px] md:h-[38px] px-2 md:px-3 text-slate-700 cursor-pointer" required onclick="try{this.showPicker()}catch(e){}">
          </div>

          <div class="w-px h-6 bg-slate-200 shrink-0 mx-1 hidden md:block mt-auto mb-2" id="divider-filter" style="display: none;"></div>

          <div class="field flex-1 min-w-[160px] md:w-[220px] shrink-0 transition-opacity duration-300" id="wrap-area" style="display: none;">
              <label class="lbl text-pink-700">AREA / CABANG</label>
              <select id="opt_area" class="inp border-pink-200 focus:border-pink-500 bg-pink-50/30 text-[11px] md:text-sm font-bold h-[34px] md:h-[38px] px-2 text-pink-800 cursor-pointer w-full truncate"></select>
          </div>
          
          <div class="flex items-center gap-1.5 shrink-0 h-[34px] md:h-[38px] mt-1 md:mt-0 w-full md:w-auto">
              <button type="submit" id="btn-cari" class="btn-icon h-full flex-1 md:w-[80px] bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-sm">
                  <span class="font-bold text-xs md:text-sm uppercase tracking-wider">CARI</span>
              </button>
              <button type="button" onclick="exportExcelRekap()" class="btn-icon h-full px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-sm" title="Download Excel">
                  <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="md:w-[18px] md:h-[18px]"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></line></svg>
              </button>
          </div>
      </form>
  </div>

  <div class="flex-1 min-h-0 flex flex-col relative gap-4">
      
      <div id="content-growth" class="flex-1 min-h-0 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col relative">
          <div id="loadingUtama" class="hidden absolute inset-0 bg-white/80 z-[100] flex flex-col items-center justify-center text-blue-600 backdrop-blur-sm transition-colors">
              <div class="animate-spin rounded-full h-10 w-10 border-4 border-current border-t-transparent mb-3"></div>
              <span class="text-sm font-bold uppercase tracking-widest">Menyiapkan Data...</span>
          </div>
          
          <div class="h-full overflow-auto custom-scrollbar relative flex items-start justify-start">
              <table class="w-full text-center border-separate border-spacing-0 text-slate-700 table-fixed min-w-full" id="tabelUtama">
                <thead class="tracking-wider text-slate-700 font-extrabold text-xs md:text-sm head-lapis-1" id="headUtama"></thead>
                <tbody id="bodyUtama" class="divide-y divide-slate-100 bg-white text-xs md:text-sm"></tbody>
              </table>
          </div>
      </div>

      <div id="content-promo" class="hidden flex-1 min-h-0 flex-col gap-3 md:gap-4 overflow-y-auto custom-scrollbar pb-6 relative">
          <div id="loadingPromo" class="hidden absolute inset-0 bg-slate-50/80 z-[100] flex flex-col items-center justify-center text-pink-600 backdrop-blur-sm transition-colors rounded-xl">
              <div class="animate-spin rounded-full h-10 w-10 border-4 border-current border-t-transparent mb-3"></div>
              <span class="text-sm font-bold uppercase tracking-widest">Membangun Grafik...</span>
          </div>

          <div class="flex gap-2 items-center bg-white p-2 rounded-xl shadow-sm border border-slate-200 shrink-0 overflow-x-auto no-scrollbar">
              <span class="text-xs font-bold text-slate-500 uppercase ml-2 mr-1 shrink-0"><span class="hidden md:inline">Quick</span> Filter:</span>
              <button type="button" onclick="quickDatePromo('7')" class="px-3 py-1.5 bg-pink-50 hover:bg-pink-100 text-pink-700 text-[10px] md:text-xs font-bold rounded-lg transition shrink-0 border border-pink-100">7 HARI</button>
              <button type="button" onclick="quickDatePromo('14')" class="px-3 py-1.5 bg-pink-50 hover:bg-pink-100 text-pink-700 text-[10px] md:text-xs font-bold rounded-lg transition shrink-0 border border-pink-100">14 HARI</button>
              <button type="button" onclick="quickDatePromo('bulan_ini')" class="px-3 py-1.5 bg-pink-50 hover:bg-pink-100 text-pink-700 text-[10px] md:text-xs font-bold rounded-lg transition shrink-0 border border-pink-100">BULAN INI</button>
              <button type="button" onclick="quickDatePromo('awal_promo')" class="px-3 py-1.5 bg-pink-600 hover:bg-pink-700 text-white text-[10px] md:text-xs font-bold rounded-lg shadow-sm transition shrink-0">AWAL PROMO</button>
          </div>

          <div class="grid grid-cols-2 gap-3 md:gap-4 shrink-0">
              <div class="bg-pink-50 border border-pink-200 rounded-xl shadow-sm p-4 md:p-6 text-pink-900 flex flex-col justify-center">
                  <span class="text-[10px] md:text-sm font-extrabold opacity-80 uppercase tracking-widest mb-1 flex items-center gap-2"><span class="text-base md:text-lg">🎁</span> TOTAL PROMO</span>
                  <h2 class="text-lg md:text-3xl xl:text-4xl font-black font-mono truncate mt-1 text-pink-700" id="txt-tot-promo">0</h2>
                  <span class="text-[9px] md:text-xs font-bold bg-pink-200/60 w-max px-2 py-0.5 rounded text-pink-900 mt-1.5" id="txt-noa-promo">0 NOA</span>
              </div>
              <div class="bg-slate-50 border border-slate-200 rounded-xl shadow-sm p-4 md:p-6 text-slate-900 flex flex-col justify-center">
                  <span class="text-[10px] md:text-sm font-extrabold opacity-80 uppercase tracking-widest mb-1 flex items-center gap-2"><span class="text-base md:text-lg">🏢</span> TOTAL NON-PROMO</span>
                  <h2 class="text-lg md:text-3xl xl:text-4xl font-black font-mono truncate mt-1 text-slate-700" id="txt-tot-nonpromo">0</h2>
                  <span class="text-[9px] md:text-xs font-bold bg-slate-200/80 w-max px-2 py-0.5 rounded text-slate-800 mt-1.5" id="txt-noa-nonpromo">0 NOA</span>
              </div>
          </div>

          <div class="flex-1 flex flex-col xl:flex-row gap-3 md:gap-4 min-h-[350px]">
              <div class="w-full xl:w-[30%] bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col relative h-[300px] xl:h-auto shrink-0">
                  <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4 shrink-0 text-center">Proporsi Nominal</h3>
                  <div class="flex-1 relative w-full flex items-center justify-center min-h-0">
                      <canvas id="pieChart"></canvas>
                  </div>
              </div>
              <div class="w-full xl:flex-1 bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col relative h-[350px] xl:h-auto shrink-0">
                  <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4 shrink-0 text-center">Trend Harian Promo vs Non-Promo</h3>
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
    <div class="flex justify-between items-center px-3 py-3 md:px-5 md:py-4 border-b bg-slate-50 shrink-0 flex-wrap gap-2">
        <div class="flex-1 min-w-[200px]" id="modal-title-container"></div>
        <div class="flex flex-wrap items-center gap-1.5 ml-auto shrink-0 w-full sm:w-auto mt-2 sm:mt-0 overflow-x-auto no-scrollbar">
            <select id="filter_kankas_modal" class="inp px-2 md:px-3 h-[34px] md:h-10 flex-1 sm:w-[160px] text-xs md:text-sm font-bold text-blue-800 bg-blue-50 outline-none shrink-0 cursor-pointer" onchange="fetchDetail(1)"><option value="">Semua Kankas</option></select>
            <select id="filter_ao_modal" class="inp px-2 md:px-3 h-[34px] md:h-10 flex-1 sm:w-[160px] text-xs md:text-sm font-bold text-slate-700 bg-white outline-none shrink-0 cursor-pointer" onchange="fetchDetail(1)"><option value="">Semua AO</option></select>
            <button id="btn-excel-modal" onclick="exportExcelDetail()" class="btn-icon bg-emerald-600 hover:bg-emerald-700 text-white px-3 md:px-4 h-[34px] md:h-10 rounded-lg shadow-sm text-xs md:text-sm font-bold uppercase tracking-wider shrink-0"><span class="hidden sm:inline ml-1.5">Excel</span></button>
            <button onclick="closeModal()" class="w-[34px] md:w-10 h-[34px] md:h-10 flex items-center justify-center rounded-xl bg-slate-200 hover:bg-red-500 hover:text-white text-slate-600 transition font-bold text-xl md:text-2xl leading-none shrink-0">&times;</button>
        </div>
    </div>
    <div class="flex-1 overflow-auto bg-slate-50 relative custom-scrollbar p-0 md:p-3">
        <div id="loadingModal" class="hidden absolute inset-0 bg-white/90 z-40 flex flex-col items-center justify-center text-blue-600 backdrop-blur-sm transition-colors"></div>
        <table class="w-max min-w-full text-left text-slate-700 border border-slate-200 md:rounded-xl shadow-sm bg-white table-fixed" id="tableDetail">
            <thead class="text-slate-600 font-extrabold uppercase tracking-wider text-[10px] md:text-xs head-mod-1" id="headDetail"></thead>
            <tbody id="bodyDetail" class="divide-y divide-slate-100 bg-white text-[10px] md:text-sm"></tbody>
        </table>
    </div>
    <div class="px-3 py-3 md:px-6 md:py-4 border-t bg-white flex justify-between items-center shrink-0">
        <span id="pageInfo" class="text-[10px] md:text-sm font-bold text-slate-600 bg-slate-100 px-3 py-1 rounded-lg">0 Data</span>
        <div class="flex gap-1.5 md:gap-2" id="wrap-paging">
            <button id="btnPrev" onclick="changePage(-1)" class="px-3 md:px-4 py-1.5 md:py-2 bg-white border border-slate-300 rounded-lg text-[10px] md:text-sm font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-50 transition shadow-sm">« Prev</button>
            <button id="btnNext" onclick="changePage(1)" class="px-3 md:px-4 py-1.5 md:py-2 bg-white border border-slate-300 rounded-lg text-[10px] md:text-sm font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-50 transition shadow-sm">Next »</button>
        </div>
    </div>
  </div>
</div>

<script>
  // --- CONFIG SATU PINTU ---
  const API_URL  = './api/kredit/'; 
  const API_KODE = './api/kode/';
  const API_DATE = './api/date/';
  
  // DEFAULT AWAL PROMO (Sesuai Arahan)
  const AWAL_PROMO_DATE = '2026-02-23';

  const nfID = new Intl.NumberFormat('id-ID');
  const fmt = n => nfID.format(Math.round(Number(n||0)));
  const apiCall = (url, opt={}) => (window.apiFetch ? window.apiFetch(url,opt) : fetch(url,opt));

  let activeTab = 'growth'; 
  let abortController;
  let rekapDataCache = null; 
  
  let promoPieChart = null;
  let promoBarChart = null;

  let currentDetailParams = {};
  let currentDetailPage = 1; let currentDetailTotalPages = 1;
  const detailLimit = 20; 
  let userKodeGlobal = window.currentUser.kode_kantor || '000';
  let defaultClosingGrowth = '';

  // --- INIT ---
  window.addEventListener('DOMContentLoaded', async () => {
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
          el.innerHTML = `<option value="CABANG|${userKode}">CABANG ${userKode}</option>`;
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

  // --- TAB SWITCHER LOGIC ---
  function switchTab(tab) {
      activeTab = tab;
      
      const tabGrowth = document.getElementById('tab-growth');
      const tabPromo  = document.getElementById('tab-promo');
      const titleContainer = document.getElementById('header-title-container');
      const loaderUtama = document.getElementById('loadingUtama');
      const btnCari = document.getElementById('btn-cari');
      
      const wrapArea = document.getElementById('wrap-area');
      const divider = document.getElementById('divider-filter');

      // Ambil elemen Closing Date
      const elClosing = document.getElementById('closing_date');

      if (tab === 'growth') {
          // --- TAB GROWTH ---
          tabGrowth.className = "pb-2 md:pb-3 font-extrabold text-xs md:text-sm uppercase transition border-b-[3px] border-blue-600 text-blue-700 whitespace-nowrap";
          tabPromo.className  = "pb-2 md:pb-3 font-extrabold text-xs md:text-sm uppercase transition border-b-[3px] border-transparent text-slate-400 hover:text-slate-600 whitespace-nowrap";
          
          // Sembunyikan Filter Area (Tabel Growth tanpa filter area)
          if(wrapArea) wrapArea.style.display = 'none';       
          if(divider) divider.style.display = 'none';
          
          // Kembalikan Closing Date ke tanggal normal bulan lalu
          elClosing.value = defaultClosingGrowth;

          btnCari.className = "btn-icon h-full flex-1 md:w-[80px] bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-sm";
          loaderUtama.className = "hidden absolute inset-0 bg-white/80 z-[100] flex flex-col items-center justify-center text-blue-600 backdrop-blur-sm transition-colors";

          document.getElementById('content-promo').style.display = 'none';
          document.getElementById('content-growth').style.display = 'flex';

          titleContainer.innerHTML = `
              <h1 class="text-xl md:text-2xl font-bold text-slate-800 flex items-center gap-2 mb-0.5">
                  <span class="p-1.5 md:p-2 bg-blue-600 rounded-lg text-white shadow-sm"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg></span>
                  Rekap Realisasi & Growth
              </h1>
              <p class="text-[11px] md:text-sm text-slate-500 italic ml-10 md:ml-[44px]">*Data Growth = Realisasi Baru - Run Off</p>
          `;
          setupHeaderUtama(userKodeGlobal); 
      } else {
          // --- TAB PROMO ---
          tabPromo.className  = "pb-2 md:pb-3 font-extrabold text-xs md:text-sm uppercase transition border-b-[3px] border-pink-600 text-pink-700 whitespace-nowrap";
          tabGrowth.className = "pb-2 md:pb-3 font-extrabold text-xs md:text-sm uppercase transition border-b-[3px] border-transparent text-slate-400 hover:text-slate-600 whitespace-nowrap";
          
          // Munculkan Filter Area
          if(wrapArea) wrapArea.style.display = 'flex';       
          if(divider) divider.style.display = 'block';

          // Set Closing Date default Promo: 2026-02-23 (bisa diedit user)
          elClosing.value = AWAL_PROMO_DATE;

          btnCari.className = "btn-icon h-full flex-1 md:w-[80px] bg-pink-600 hover:bg-pink-700 text-white rounded-lg shadow-sm";
          loaderUtama.className = "hidden absolute inset-0 bg-white/80 z-[100] flex flex-col items-center justify-center text-pink-600 backdrop-blur-sm transition-colors";

          document.getElementById('content-growth').style.display = 'none';
          document.getElementById('content-promo').style.display = 'flex';

          titleContainer.innerHTML = `
              <h1 class="text-xl md:text-2xl font-bold text-slate-800 flex items-center gap-2 mb-0.5">
                  <span class="p-1.5 md:p-2 bg-pink-600 rounded-lg text-white shadow-sm"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg></span>
                  Analitik Promo vs Non-Promo
              </h1>
              <p class="text-[11px] md:text-sm text-slate-500 italic ml-10 md:ml-[44px] font-bold text-pink-700">*Promo Kredit Ramadhan Dan Idul Fitri (000011)</p>
          `;
      }
      fetchRekap();
  }

  // --- LOGIKA QUICK FILTER PROMO ---
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
          // Tanggal 1 bulan ini
          const firstDay = new Date(dateHarian.getFullYear(), dateHarian.getMonth(), 1);
          elClosing.value = firstDay.toISOString().split('T')[0];
      } else if (tipe === 'awal_promo') {
          // Hardcode 23 Feb 2026
          elClosing.value = AWAL_PROMO_DATE;
      }
      
      fetchRekap();
  }

  async function getLastHarianData(){ 
      try{ const r=await apiCall(API_DATE); const j=await r.json(); return j.data||null; }catch{ return null; } 
  }

  function setupHeaderUtama(userKode) {
      if (activeTab !== 'growth') return; 

      const th = document.getElementById('headUtama');
      let thContent = `<tr>`;
      th.className = "tracking-wider text-slate-800 font-extrabold text-xs md:text-sm head-lapis-1";
      
      if (userKode === '000') {
          thContent += `
              <th class="freeze-col-1 w-[60px] md:w-[80px] border-r border-b border-slate-300 align-middle uppercase text-center hidden md:table-cell text-slate-800 bg-[#dcedc8]">KODE</th>
              <th class="freeze-col-2 min-w-[150px] md:min-w-[250px] border-r border-b border-slate-300 align-middle uppercase pl-4 md:pl-5 text-slate-800 text-left bg-[#dcedc8]">NAMA KANTOR</th>
          `;
      } else {
          thContent += `<th class="freeze-col-1 min-w-[160px] md:min-w-[250px] border-r border-b border-slate-300 align-middle uppercase pl-4 md:pl-5 text-slate-800 text-left border-t-0 rounded-tl-lg bg-[#dcedc8]">NAMA KANTOR</th>`;
      }

      thContent += `
              <th class="px-3 md:px-4 border-r border-b border-slate-300 align-middle text-center w-[120px] md:w-[150px] uppercase text-slate-800 bg-[#dcedc8]">NOA REALISASI</th>
              <th class="px-3 md:px-4 border-r border-b border-slate-300 align-middle text-right w-[140px] md:w-[180px] uppercase text-slate-800 bg-[#dcedc8]">TOT REALISASI</th>
              <th class="px-3 md:px-4 border-r border-b border-slate-300 align-middle text-right w-[140px] md:w-[160px] uppercase text-emerald-800 bg-[#dcedc8]">PELUNASAN</th>
              <th class="px-3 md:px-4 border-r border-b border-slate-300 align-middle text-right w-[140px] md:w-[180px] uppercase text-blue-800 bg-[#dcedc8]">ANGSURAN MURNI</th>
              <th class="px-3 md:px-4 border-r border-b border-slate-300 align-middle text-right w-[140px] md:w-[180px] uppercase text-orange-800 bg-[#dcedc8]">TOT RUN OFF</th>
              <th class="px-3 md:px-4 border-b border-slate-300 align-middle text-right w-[140px] md:w-[180px] uppercase text-slate-800 bg-[#dcedc8]">GROWTH</th>
          </tr>
          <tr id="rowTotalAtas" class="text-sm md:text-base font-extrabold tracking-wide head-lapis-2 bg-[#eff6ff]"></tr>
      `;
      th.innerHTML = thContent;
  }

  // --- FETCH API UTAMA ---
  async function fetchRekap(){
      const loadG = document.getElementById('loadingUtama');
      const loadP = document.getElementById('loadingPromo');
      
      if(abortController) abortController.abort(); abortController = new AbortController();
      
      if (activeTab === 'growth') loadG.classList.remove('hidden');
      else loadP.classList.remove('hidden');

      rekapDataCache = null;

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
                  closing_date: document.getElementById('closing_date').value, // Kirim dari form input yang diedit/diklik
                  harian_date: document.getElementById('harian_date').value,
                  kode_kantor: reqCabang, 
                  korwil: reqKorwil 
              };
          }

          const res = await apiCall(API_URL, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload), signal: abortController.signal });
          const json = await res.json();
          if(json.status !== 200) throw new Error(json.message);
          
          if (activeTab === 'growth') {
              let rows = Array.isArray(json.data) ? json.data : (json.data?.data || []);
              let gt = json.data?.grand_total || json.grand_total || {};
              rekapDataCache = rows;
              renderTableGrowth(rows, gt, userKodeGlobal);
          } else {
              rekapDataCache = json.data?.trend || [];
              renderChartPromo(json.data || {});
          }
          
      } catch(err) { 
          if(err.name !== 'AbortError') {
              if (activeTab === 'growth') {
                  const tb = document.getElementById('bodyUtama');
                  tb.innerHTML=`<tr><td colspan="8" class="py-16 text-center text-red-500 font-bold text-sm">${err.message}</td></tr>`; 
              } else {
                  console.error(err);
              }
          }
      } finally { 
          loadG.classList.add('hidden'); loadP.classList.add('hidden');
      }
  }

  // --- RENDER TABLE GROWTH ---
  function renderTableGrowth(rows, gt, userKode) {
      const tb = document.getElementById('bodyUtama'); const trTot = document.getElementById('rowTotalAtas');
      const colSpan = userKode === '000' ? 8 : 7;
      if(rows.length === 0){ tb.innerHTML = `<tr><td colspan="${colSpan}" class="py-20 text-center text-slate-500 text-base">Tidak ada data.</td></tr>`; return; }

      let html = '';
      rows.forEach(r => {
          const noa_real  = parseInt(r.noa_realisasi) || 0;
          const realisasi = parseFloat(r.total_realisasi) || 0;
          const pelunasan = parseFloat(r.pelunasan) || 0;
          const ang_murni = parseFloat(r.angsuran_murni) || 0;
          const run_off   = parseFloat(r.total_run_off) || 0;
          const growth    = parseFloat(r.growth) || 0;
          const growthColor = growth >= 0 ? 'text-blue-700' : 'text-red-600';

          let rowHtml = `<tr class="transition h-[52px] border-b border-slate-100 group hover:bg-slate-50" onclick="initModalDetail('${r.kode_kantor}', '${r.nama_kantor}')">`;
          
          if (userKode === '000') {
              rowHtml += `
                <td class="freeze-col-1 px-2 md:px-4 py-2 text-center font-mono font-bold text-slate-500 hidden md:table-cell bg-white">${r.kode_kantor}</td>
                <td class="freeze-col-2 px-4 md:px-5 py-2 font-bold text-slate-700 truncate bg-white" title="${r.nama_kantor}">${r.nama_kantor}</td>
              `;
          } else {
              rowHtml += `
                <td class="freeze-col-1 px-4 md:px-5 py-2 font-bold text-slate-700 truncate bg-white" title="${r.nama_kantor}">${r.nama_kantor}</td>
              `;
          }

          rowHtml += `
                <td class="px-3 md:px-4 py-2 text-center font-extrabold text-blue-800 border-r border-slate-100 text-sm md:text-base">${fmt(noa_real)}</td>
                <td class="px-3 md:px-4 py-2 text-right font-mono font-bold text-slate-800 border-r border-slate-100 text-sm md:text-base">${fmt(realisasi)}</td>
                <td class="px-3 md:px-4 py-2 text-right font-mono font-bold text-emerald-700 border-r border-slate-100 text-sm md:text-base">${fmt(pelunasan)}</td>
                <td class="px-3 md:px-4 py-2 text-right font-mono font-bold text-blue-700 border-r border-slate-100 text-sm md:text-base">${fmt(ang_murni)}</td>
                <td class="px-3 md:px-4 py-2 text-right font-mono font-bold text-orange-700 border-r border-slate-100 text-sm md:text-base">${fmt(run_off)}</td>
                <td class="px-3 md:px-4 py-2 text-right font-mono font-extrabold ${growthColor} text-sm md:text-base">${fmt(growth)}</td>
            </tr>`;
          html += rowHtml;
      });
      tb.innerHTML = html;

      if(rows.length > 0) {
          const gGrowth = parseFloat(gt.growth) || 0;
          const tGrowthColor = gGrowth >= 0 ? 'text-blue-800' : 'text-red-700';

          if (userKode === '000') {
              trTot.innerHTML = `
                  <th class="freeze-col-1 px-2 md:px-4 border-r border-blue-300 text-center text-blue-900 font-extrabold hidden md:table-cell bg-[#eff6ff]">ALL</th>
                  <th class="freeze-col-2 px-4 md:px-5 border-r border-blue-300 text-left uppercase tracking-widest font-extrabold text-xs md:text-sm text-blue-900 bg-[#eff6ff]">GRAND TOTAL</th>
              `;
          } else {
              trTot.innerHTML = `
                  <th class="freeze-col-1 px-4 md:px-5 border-r border-blue-300 text-left uppercase tracking-widest font-extrabold text-xs md:text-sm text-blue-900 bg-[#eff6ff]">GRAND TOTAL</th>
              `;
          }

          trTot.innerHTML += `
              <th class="px-3 md:px-4 border-r border-blue-300 text-center font-extrabold text-sm md:text-base text-blue-900 align-middle bg-[#eff6ff]">${fmt(gt.noa_realisasi)}</th>
              <th class="px-3 md:px-4 border-r border-blue-300 text-right font-mono font-bold text-sm md:text-base text-blue-900 align-middle bg-[#eff6ff]">${fmt(gt.total_realisasi)}</th>
              <th class="px-3 md:px-4 border-r border-blue-300 text-right font-mono font-bold text-sm md:text-base text-emerald-800 align-middle bg-[#eff6ff]">${fmt(gt.pelunasan)}</th>
              <th class="px-3 md:px-4 border-r border-blue-300 text-right font-mono font-bold text-sm md:text-base text-blue-800 align-middle bg-[#eff6ff]">${fmt(gt.angsuran_murni)}</th>
              <th class="px-3 md:px-4 border-r border-blue-300 text-right font-mono font-bold text-sm md:text-base text-orange-800 align-middle bg-[#eff6ff]">${fmt(gt.total_run_off)}</th>
              <th class="px-3 md:px-4 text-right font-mono font-extrabold text-sm md:text-base ${tGrowthColor} align-middle bg-[#eff6ff]">${fmt(gGrowth)}</th>
          `;
      }
  }

  // --- RENDER DASHBOARD CHART PROMO ---
  function renderChartPromo(data) {
      const totals = data.totals || {promo_nominal:0, non_promo_nominal:0, promo_noa:0, non_promo_noa:0};
      const trend = data.trend || [];

      document.getElementById('txt-tot-promo').innerText = fmt(totals.promo_nominal);
      document.getElementById('txt-noa-promo').innerText = fmt(totals.promo_noa) + ' Nasabah (NOA)';
      
      document.getElementById('txt-tot-nonpromo').innerText = fmt(totals.non_promo_nominal);
      document.getElementById('txt-noa-nonpromo').innerText = fmt(totals.non_promo_noa) + ' Nasabah (NOA)';

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
                  legend: { position: 'bottom', labels: {font:{size:11, family:'monospace'}, usePointStyle: true} },
                  tooltip: {
                      callbacks: {
                          label: function(ctx) {
                              let label = ctx.label || '';
                              if (label) label += ': ';
                              label += 'Rp ' + fmt(ctx.raw);
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
                  { label: 'Promo', data: dataPromo, backgroundColor: '#ec4899', borderRadius: 4 },
                  { label: 'Non-Promo', data: dataNonPromo, backgroundColor: '#cbd5e1', borderRadius: 4 }
              ]
          },
          options: {
              responsive: true, maintainAspectRatio: false,
              interaction: { mode: 'index', intersect: false },
              scales: {
                  x: { stacked: false, grid: { display: false } },
                  y: { 
                      stacked: false, border: { display: false },
                      ticks: {
                          callback: function(value) {
                              if(value >= 1000000000) return (value/1000000000).toFixed(1) + ' Miliar';
                              if(value >= 1000000) return (value/1000000).toFixed(1) + ' Juta';
                              return value;
                          }
                      }
                  }
              },
              plugins: {
                  legend: { position: 'bottom', labels: {font:{size:11, family:'monospace'}, usePointStyle: true} },
                  tooltip: { 
                      callbacks: { 
                          label: function(ctx) { 
                              let nominalText = ctx.dataset.label + ': Rp ' + fmt(ctx.raw); 
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

  // --- MODAL LOGIC (HANYA UNTUK GROWTH) ---
  function initModalDetail(kode, nama) {
      if (userKodeGlobal !== '000' && String(kode) !== userKodeGlobal) {
          alert(`AKSES DITOLAK!\nAnda tidak memiliki izin untuk melihat detail Cabang ${kode}.`);
          return;
      }

      currentDetailParams = { kode_kantor: kode, kode_kankas: null, kode_ao: null };

      const selAO = document.getElementById('filter_ao_modal');
      selAO.innerHTML = '<option value="">Semua AO</option>';

      const modal = document.getElementById('modalDetail');
      modal.classList.remove('hidden'); modal.classList.add('flex');
      
      const mTitle = document.getElementById('modal-title-container');
      const mHead = document.getElementById('headDetail');

      mTitle.innerHTML = `
          <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm md:text-base"><span class="bg-blue-100 text-blue-600 p-1 md:p-1.5 rounded-lg shadow-sm text-xs">👥</span> Detail Realisasi</h3>
          <p class="text-[10px] md:text-xs text-slate-500 mt-0.5 ml-1 md:ml-8 font-mono">Cabang: ${nama}</p>
      `;
      
      mHead.innerHTML = `
          <tr class="bg-[#f1f5f9] text-blue-900">
              <th class="px-3 md:px-4 py-2.5 md:py-4 border-b border-r border-slate-300 w-[120px] mod-sticky-1 rounded-tl-lg md:rounded-tl-xl bg-[#f1f5f9]">REKENING</th>
              <th class="px-4 md:px-5 py-2.5 md:py-4 border-b border-r border-slate-300 w-[240px] md:w-[300px] mod-freeze-2 bg-[#f1f5f9]">NAMA NASABAH</th>
              <th class="px-3 md:px-4 py-2.5 md:py-4 border-b border-r border-slate-300 w-[200px] md:w-[250px]">ALAMAT</th>
              <th class="px-3 md:px-4 py-2.5 md:py-4 border-b border-r border-slate-300 w-[140px] text-center">KANKAS</th>
              <th class="px-3 md:px-4 py-2.5 md:py-4 border-b border-r border-slate-300 w-[160px]">NAMA AO</th>
              <th class="px-3 md:px-4 py-2.5 md:py-4 border-b border-r border-slate-300 w-[120px] text-center">TGL REALISASI</th>
              <th class="px-4 md:px-5 py-2.5 md:py-4 border-b border-slate-300 w-[160px] md:w-[200px] text-right">PLAFON</th>
          </tr>
          <tr id="rowTotalDetailAtas" class="head-mod-2 bg-[#eff6ff]"></tr>
      `;
      
      loadKankasModal(kode);
      fetchDetail(1);
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

  function changePage(step) {
      const n = currentDetailPage + step;
      if (n > 0 && n <= currentDetailTotalPages) fetchDetail(n);
  }

  async function fetchDetail(page = 1) {
      const l = document.getElementById('loadingModal'); const tb = document.getElementById('bodyDetail'); const info = document.getElementById('pageInfo');
      const trTot = document.getElementById('rowTotalDetailAtas');
      
      l.classList.remove('hidden'); tb.innerHTML = ''; trTot.innerHTML = '';
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
          
          let list = Array.isArray(json.data) ? json.data : (json.data?.data || []);
          
          const selAO = document.getElementById('filter_ao_modal');
          if (selAO.options.length === 1 && list.length > 0) {
              const uniqueAOs = [...new Map(list.filter(x=>x.nama_ao).map(item => [item['nama_ao'], item])).values()];
              uniqueAOs.forEach(ao => { selAO.add(new Option(ao.nama_ao, ao.nama_ao)); }); 
          }

          const meta = json.data?.pagination || json.pagination || { total_records: list.length, total_pages: 1 };
          currentDetailPage = page; currentDetailTotalPages = meta.total_pages;

          if(list.length === 0) {
              tb.innerHTML = `<tr><td colspan="7" class="py-20 text-center text-slate-400 italic text-base">Tidak ada data detail.</td></tr>`;
              info.innerText = `0 data`; return;
          }

          let t_plafond = 0; let html = '';
          list.forEach(r => {
              t_plafond += parseFloat(r.plafond||0);
              const alamatLengkap = r.alamat || '-';
              const alamatPendek = alamatLengkap.length > 25 ? alamatLengkap.substring(0, 25) + '...' : alamatLengkap;

              html += `<tr class="transition h-[52px] group border-b border-slate-100 hover:bg-slate-50">
                    <td class="mod-sticky-1 px-3 md:px-4 py-2 font-mono text-sm text-slate-500 bg-white border-r border-slate-100 shadow-[inset_-1px_0_0_#e2e8f0] group-hover:bg-slate-50">${r.no_rekening}</td>
                    <td class="mod-sticky-2 px-4 md:px-5 py-2 font-bold text-sm text-slate-700 bg-white truncate border-r border-slate-100 max-w-[240px] md:max-w-[300px] shadow-[inset_-1px_0_0_#e2e8f0] group-hover:bg-slate-50" title="${r.nama_nasabah}">${r.nama_nasabah}</td>
                    <td class="px-3 md:px-4 py-2 text-sm text-slate-500 whitespace-nowrap border-r border-slate-100" title="${alamatLengkap}">${alamatPendek}</td>
                    <td class="px-3 md:px-4 py-2 text-center font-mono text-xs md:text-sm text-slate-500 border-r border-slate-100">${r.nama_kankas||'-'}</td>
                    <td class="px-3 md:px-4 py-2 text-sm font-bold text-blue-700 truncate border-r border-slate-100">${r.nama_ao||'-'}</td>
                    <td class="px-3 md:px-4 py-2 text-center font-mono text-xs md:text-sm text-slate-600 border-r border-slate-100">${r.tgl_realisasi}</td>
                    <td class="px-4 md:px-5 py-2 text-right font-mono font-bold text-slate-800 text-sm md:text-base">${fmt(r.plafond)}</td>
                </tr>`;
          });
          tb.innerHTML = html;

          trTot.innerHTML = `
              <th class="mod-sticky-1 px-3 md:px-4 border-r border-b border-blue-200 uppercase tracking-widest text-center text-blue-900 bg-[#eff6ff]">-</th>
              <th class="mod-sticky-2 px-4 md:px-5 border-r border-b border-blue-200 uppercase tracking-widest font-extrabold text-[10px] md:text-sm text-blue-900 bg-[#eff6ff]">TOTAL HALAMAN INI</th>
              <th class="px-3 md:px-4 border-r border-b border-blue-200 text-center bg-[#eff6ff]">-</th>
              <th class="px-3 md:px-4 border-r border-b border-blue-200 text-center bg-[#eff6ff]">-</th>
              <th class="px-3 md:px-4 border-r border-b border-blue-200 text-center bg-[#eff6ff]">-</th>
              <th class="px-3 md:px-4 border-r border-b border-blue-200 text-center bg-[#eff6ff]">-</th>
              <th class="px-4 md:px-5 border-b border-blue-300 text-right font-mono font-extrabold text-sm md:text-base text-blue-900 bg-blue-100/60">${fmt(t_plafond)}</th>
          `;

          const start = ((page-1)*detailLimit)+1; const end = Math.min(page*detailLimit, meta.total_records);
          info.innerText = `Hal ${page} / ${meta.total_pages} (${start}-${end} dari ${fmt(meta.total_records)})`;
          document.getElementById('btnPrev').disabled = page <= 1;
          document.getElementById('btnNext').disabled = page >= meta.total_pages;

      } catch(err){ console.error(err); } finally { l.classList.add('hidden'); }
  }

  async function exportExcelDetail() {
      const btn = document.getElementById('btn-excel-modal'); const txt = btn.innerHTML;
      btn.innerHTML = `<span class="animate-spin inline-block h-3 w-3 md:h-4 md:w-4 border-2 border-white border-t-transparent rounded-full mr-1 md:mr-2"></span>...`;
      btn.disabled = true;

      try {
          const payload = {
              type: 'detail_realisasi_growth',
              closing_date: document.getElementById('closing_date').value,
              harian_date: document.getElementById('harian_date').value,
              kode_kantor: currentDetailParams.kode_kantor,
              kode_kankas: currentDetailParams.kode_kankas,
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