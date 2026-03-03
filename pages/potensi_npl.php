<style>
  :root { --primary: #2563eb; --bg: #f8fafc; --text: #334155; }
  
  body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text); overflow: hidden; }
  
  /* === INPUTS === */
  .inp { 
      border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 0 0.5rem; 
      font-size: 13px; background: #fff; width: 100%; height: 38px; 
      min-width: 0; transition: all 0.2s; outline: none; color: #334155;
  }
  .inp:focus { border-color: var(--primary); }
  .inp:disabled { background-color: #f1f5f9; color: #64748b; font-weight: 600; cursor: not-allowed; border-color: #e2e8f0; }
  
  input[type="date"] { position: relative; cursor: pointer; }
  input[type="date"]::-webkit-inner-spin-button,
  input[type="date"]::-webkit-calendar-picker-indicator {
      position: absolute; top: 0; left: 0; right: 0; bottom: 0;
      width: 100%; height: 100%; opacity: 0; cursor: pointer;
  }

  /* === ICON BUTTONS CLEAN === */
  .btn-icon { 
      width: 38px; height: 38px; border-radius: 8px; 
      background: var(--primary); color: white; border: none; cursor: pointer; 
      display: inline-flex; align-items: center; justify-content: center; 
      transition: 0.2s; flex-shrink: 0; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
  }
  .btn-icon:hover { background: #1d4ed8; transform: translateY(-1px); }

  /* === TABLE SCROLLER REKAP === */
  #poScroller {
      --col1: 60px;   
      --col2: 200px;  
      --po_headH: 60px; 
      position: relative; border: 1px solid #e2e8f0; border-radius: 8px; background: white;
      height: 100%; overflow: auto; -webkit-overflow-scrolling: touch; 
  }

  table { border-collapse: separate; border-spacing: 0; width: 100%; font-size: 11px; }
  th, td { white-space: nowrap; padding: 8px 10px; vertical-align: middle; }
  
  /* HEADER STYLES REKAP (2 BARIS) */
  #tabelPotensi thead th { 
      position: sticky; z-index: 60; background: #f1f5f9; color: #475569; 
      font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; font-size: 10px;
      border-bottom: 1px solid #cbd5e1; border-right: 1px solid #e2e8f0;
  }
  #tabelPotensi thead tr:first-child th { top: 0; border-bottom: 1px solid #cbd5e1; }
  #tabelPotensi thead tr:last-child th { top: 30px; }

  /* STICKY COLUMNS LOGIC */
  .sticky-left-1 { position: sticky; left: 0; z-index: 45; background: #fff; border-right: 1px solid #f1f5f9; width: var(--col1); min-width: var(--col1); max-width: var(--col1); text-align: center; }
  .sticky-left-2 { position: sticky; left: var(--col1); z-index: 44; background: #fff; border-right: 1px solid #e2e8f0; width: var(--col2); min-width: var(--col2); max-width: var(--col2); overflow: hidden; text-overflow: ellipsis; }

  #tabelPotensi thead th.sticky-left-1 { z-index: 70; background: #f1f5f9; }
  #tabelPotensi thead th.sticky-left-2 { z-index: 69; background: #f1f5f9; }

  /* TOTAL ROW STICKY */
  #poTotalRow td { position: sticky; top: var(--po_headH); z-index: 50; background: #eff6ff; color: #1e40af; font-weight: 700; border-bottom: 2px solid #bfdbfe; border-right: 1px solid #bfdbfe; box-shadow: 0 4px 6px -2px rgba(0,0,0,0.05); }
  #poTotalRow td.sticky-left-1 { z-index: 59; background: #eff6ff; }
  #poTotalRow td.sticky-left-2 { z-index: 58; background: #eff6ff; }

  #poBody td { background-color: #fff; border-bottom: 1px solid #f1f5f9; border-right: 1px solid #f1f5f9; color: #334155; }
  #poBody tr:hover td { background-color: #f8fafc; }

  /* MODAL DETAIL SCROLLER */
  #modalScroll { --colRek: 130px; --colNama: 180px; }
  #modalTablePO { width: 100%; min-width: 1800px; }
  #modalTablePO th { position: sticky; top: 0; z-index: 30; background: #f8fafc; padding: 10px 12px; border-bottom: 1px solid #e2e8f0; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; }
  #modalTablePO td { padding: 8px 12px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 11px; }
  
  .modal-freeze-1 { position: sticky; left: 0; z-index: 35; background: #fff; border-right: 1px solid #e2e8f0; width: var(--colRek); }
  .modal-freeze-2 { position: sticky; left: var(--colRek); z-index: 34; background: #fff; border-right: 1px solid #e2e8f0; width: var(--colNama); }
  
  #modalTablePO th.modal-freeze-1 { z-index: 40; background: #f8fafc; }
  #modalTablePO th.modal-freeze-2 { z-index: 39; background: #f8fafc; }
  
  .modal-total-row td { position: sticky; top: 36px; z-index: 25; background: #eff6ff; color: #1e40af; font-weight: bold; border-bottom: 1px solid #bfdbfe; }
  .modal-total-row td.modal-freeze-1 { z-index: 38; background: #eff6ff; }
  .modal-total-row td.modal-freeze-2 { z-index: 37; background: #eff6ff; }
  
  /* Status Warna-Warni */
  .status-aman { color: #16a34a; font-weight: bold; background: #dcfce7; padding: 3px 8px; border-radius: 6px; }
  .status-jt { color: #ca8a04; font-weight: bold; background: #fef08a; padding: 3px 8px; border-radius: 6px; }
  .status-flow { color: #dc2626; font-weight: bold; background: #fee2e2; padding: 3px 8px; border-radius: 6px; }

  /* MOBILE RESPONSIVE */
  @media (max-width: 767px) {
      #filterForm { flex-wrap: wrap; justify-content: flex-end; gap: 8px; }
      .filter-box { flex: 1 1 30%; min-width: 100px; }
      #opt_kantor_rec { font-size: 11px; padding: 0 4px; }
      #closing_date, #harian_date { font-size: 11px; padding: 0 4px; text-align: center; width: 100%; }

      .sticky-left-1 { display: none !important; }
      .sticky-left-2 { left: 0 !important; z-index: 45 !important; min-width: 140px; max-width: 160px; white-space: normal; line-height: 1.2; }
      #tabelPotensi thead th.sticky-left-2 { z-index: 70 !important; }
      #poTotalRow td.sticky-left-2 { z-index: 65 !important; }
      #modalScroll { --colRek: 0px; --colNama: 120px; }
      .modal-freeze-1 { display: none; }
      .modal-freeze-2 { left: 0; }
  }
</style>

<div class="max-w-full mx-auto px-3 md:px-4 py-4 h-[calc(100vh-80px)] md:h-[calc(100vh-120px)] flex flex-col font-sans bg-slate-50">

  <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-4 shrink-0">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-xl md:text-2xl font-bold flex items-center gap-2 text-slate-800">
            <span class="bg-blue-600 text-white p-1.5 rounded-lg text-sm md:text-base shadow-sm">⚠️</span> 
            <span>Potensi NPL</span>
            <span id="badgeUnit" class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-800 text-[10px] uppercase font-bold rounded tracking-wider">MEMUAT...</span>
        </h1>
        <p class="text-[10px] md:text-xs text-slate-500 mt-1 ml-1 font-medium">*Rekap Potensi NPL Terperinci (Aman, JT, Flow)</p>
      </div>
      <div id="loadingMini" class="hidden md:hidden animate-spin h-5 w-5 border-2 border-blue-600 border-t-transparent rounded-full"></div>
    </div>

    <form id="filterForm" class="flex flex-row flex-wrap md:flex-nowrap items-end gap-2 md:gap-3 w-full md:w-auto">
      <div class="filter-box flex flex-col md:w-[170px]">
          <label class="text-[9px] md:text-[10px] font-bold text-slate-500 uppercase ml-1 mb-1 tracking-wider">Kantor</label>
          <select id="opt_kantor_rec" class="inp font-medium text-slate-700 shadow-sm"><option value="">Memuat...</option></select>
      </div>
      <div class="filter-box flex flex-col md:w-[130px]">
          <label class="text-[9px] md:text-[10px] font-bold text-slate-500 uppercase ml-1 mb-1 tracking-wider">Closing Date</label>
          <input type="date" id="closing_date" class="inp shadow-sm" required>
      </div>
      <div class="filter-box flex flex-col md:w-[130px]">
          <label class="text-[9px] md:text-[10px] font-bold text-slate-500 uppercase ml-1 mb-1 tracking-wider">Actual Date</label>
          <input type="date" id="harian_date" class="inp shadow-sm" required>
      </div>
      
      <div class="filter-actions flex items-center gap-2 shrink-0">
        <button type="submit" class="btn-icon" title="Cari Data">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        </button>
        <button type="button" onclick="exportPotensiExcel()" class="btn-icon bg-green-600 hover:bg-green-700" title="Download Excel">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
        </button>
      </div>
    </form>
  </div>

  <div class="flex-1 min-h-0 relative flex flex-col">
    <div id="loadingPO" class="hidden absolute inset-0 bg-white/80 z-[100] flex flex-col items-center justify-center text-blue-600 font-bold backdrop-blur-sm rounded-lg">
       <div class="animate-spin h-10 w-10 border-4 border-blue-200 border-t-blue-600 rounded-full mb-3"></div>
       <span class="text-sm tracking-wide">MEMUAT DATA...</span>
    </div>

    <div id="poScroller" class="table-wrapper shadow-sm">
      <table id="tabelPotensi">
        <thead id="poHead1">
          <tr>
            <th class="sticky-left-1" rowspan="2">KODE</th>
            <th class="sticky-left-2" id="thNamaPO" rowspan="2">NAMA KANTOR</th>
            <th class="text-center bg-blue-50 border-b border-blue-200" colspan="2">TOTAL POTENSI NPL</th>
            <th class="text-center bg-green-50 border-b border-green-200" colspan="2">AMAN</th>
            <th class="text-center bg-yellow-50 border-b border-yellow-200" colspan="2">JATUH TEMPO</th>
            <th class="text-center bg-red-50 border-b border-red-200" colspan="2">FLOW (KL/D/M)</th>
            <th class="text-center bg-orange-50 border-b border-orange-200" colspan="2">POTENSI FLow NPL</th>
          </tr>
          <tr>
            <th class="text-center w-[60px] bg-blue-50 cursor-pointer hover:bg-blue-100 transition" onclick="doSort('total_noa')">NOA ⬍</th>
            <th class="text-right min-w-[120px] bg-blue-50 cursor-pointer hover:bg-blue-100 transition" onclick="doSort('total_baki')">BAKI DEBET ⬍</th>
            
            <th class="text-center w-[60px] bg-green-50">NOA</th><th class="text-right min-w-[120px] bg-green-50">BAKI DEBET</th>
            <th class="text-center w-[60px] bg-yellow-50">NOA</th><th class="text-right min-w-[120px] bg-yellow-50">BAKI DEBET</th>
            <th class="text-center w-[60px] bg-red-50">NOA</th><th class="text-right min-w-[120px] bg-red-50">BAKI DEBET</th>
            <th class="text-center w-[60px] bg-orange-50">NOA</th><th class="text-right min-w-[120px] bg-orange-50">BAKI DEBET</th>
          </tr>
        </thead>
        <tbody id="poTotalRow"></tbody>
        <tbody id="poBody"></tbody>
      </table>
    </div>
  </div>
</div>

<div id="modalDebiturPotensi" class="fixed inset-0 hidden bg-slate-900/60 backdrop-blur-sm items-center justify-center z-[9999] px-2 md:px-4">
  <div id="modalCardPO" class="bg-white rounded-xl shadow-2xl flex flex-col w-full max-w-[1600px] h-[95vh] overflow-hidden animate-scale-up">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between p-3 md:p-4 border-b border-slate-100 bg-slate-50 shrink-0 gap-3">
      <div>
        <h3 class="font-bold text-slate-800 text-base md:text-xl flex items-center gap-2">
            📄 <span id="modalTitlePotensi" class="truncate max-w-[250px] md:max-w-none">Detail Potensi NPL</span>
        </h3>
        <p class="text-[10px] md:text-xs text-slate-500 mt-1" id="modalSubtitlePO">Posisi: -</p>
      </div>
      
      <div class="flex items-center gap-2">
          <select id="modalFilterStatus" class="inp !h-9 !py-0 text-xs w-[130px] md:w-[150px] font-medium text-slate-700 shadow-sm" onchange="renderDetailRows()">
              <option value="ALL">Semua Status</option>
              <option value="AMAN">Aman / Lunas</option>
              <option value="JATUH TEMPO">Jatuh Tempo</option>
              <option value="FLOW KOLEK">Flow Kolek</option>
              <option value="MASIH POTENSI">Masih Potensi</option>
          </select>
          
          <select id="modalFilterKankas" class="inp !h-9 !py-0 text-xs w-[130px] md:w-[150px] font-medium text-slate-700 shadow-sm" onchange="fetchDetailPotensiNpl()">
              <option value="">Semua Kankas</option>
          </select>

          <button onclick="exportDetailPotensiExcel()" class="flex items-center justify-center w-9 h-9 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow-sm transition" title="Export Excel Detail">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
          </button>

          <button id="btnClosePO" class="flex items-center justify-center w-9 h-9 bg-slate-200 hover:bg-red-100 text-slate-600 hover:text-red-600 rounded-lg shadow-sm transition font-bold">✕</button>
      </div>
    </div>
    
    <div class="flex-1 overflow-auto bg-white relative" id="modalScroll">
        <table id="modalTablePO">
            <thead>
                <tr>
                    <th class="modal-freeze-1 text-center">No Rekening</th>
                    <th class="modal-freeze-2 text-left">Nama Nasabah</th>
                    <th class="text-left w-[200px]">Alamat</th>
                    <th class="text-center w-[120px]">Status Saat Ini</th>
                    <th class="text-center w-[60px]">Kol C</th>
                    <th class="text-right w-[110px]">BD Closing</th>
                    <th class="text-center w-[60px]">Kol H</th>
                    <th class="text-right w-[110px]">BD Harian</th>
                    <th class="text-right w-[100px]">Tungg. Pokok</th>
                    <th class="text-right w-[100px]">Tungg. Bunga</th>
                    <th class="text-right w-[110px]">Saldo Tab</th>
                    <th class="text-center w-[80px]">JT</th>
                    <th class="text-center w-[50px]">DPD</th>
                    <th class="text-center w-[50px]">DPD P</th>
                    <th class="text-center w-[50px]">DPD B</th>
                    <th class="text-right w-[100px]">Angs. Pokok</th>
                    <th class="text-right w-[100px]">Angs. Bunga</th>
                    <th class="text-center w-[90px]">Tgl Trans</th>
                </tr>
            </thead>
            <tbody id="modalTotalRowPO"></tbody> 
            <tbody id="modalBodyRowsPO"></tbody> 
        </table>
    </div>
  </div>
</div>

<div id="modalPeringatan" class="fixed inset-0 hidden bg-slate-900/60 backdrop-blur-sm items-center justify-center z-[9999] px-4">
  <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm overflow-hidden animate-scale-up">
    <div class="bg-red-50 p-4 border-b border-red-100 flex items-center gap-3">
      <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-xl shrink-0">
        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
      </div>
      <h3 class="font-bold text-red-800 text-lg">Akses Ditolak</h3>
    </div>
    <div class="p-6 text-center text-slate-600 text-sm">
      <p>Anda login sebagai <span class="font-bold text-blue-600 px-1 bg-blue-50 rounded" id="warnUserLvl">Cabang</span>.</p>
      <p class="mt-2">Anda tidak memiliki izin untuk melihat detail data milik <span class="font-bold text-red-600 px-1 bg-red-50 rounded" id="warnTargetLvl">Unit</span>.</p>
    </div>
    <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-end">
      <button onclick="closeModalPeringatan()" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded text-xs font-bold transition shadow-sm">Mengerti</button>
    </div>
  </div>
</div>

<script>
  // =========================================================
  // UTILS & FORMATTER
  // =========================================================
  const nfID = new Intl.NumberFormat('id-ID');
  const fmtNom = n => nfID.format(Number(n||0));
  const fmtInt = n => new Intl.NumberFormat("id-ID",{maximumFractionDigits:0}).format(+n||0);
  const num = v => Number(v||0);
  const kodeNum = v => Number(String(v??'').replace(/\D/g,'')||0);
  const formatDate = (s) => { if(!s) return '-'; const d=new Date(s); return isNaN(d)?'-': `${String(d.getDate()).padStart(2,'0')}/${String(d.getMonth()+1).padStart(2,'0')}/${d.getFullYear()}`; };

  function startOfDay(d){ const x=new Date(d); x.setHours(0,0,0,0); return x; }
  function formatJTByRule(jt){ 
      if(!jt) return '-'; 
      const d = new Date(jt); 
      if(isNaN(d)) return '-'; 
      const today = startOfDay(new Date()); 
      const due = startOfDay(d); 
      if(due < today){ 
          const yyyy = d.getFullYear(); 
          const mm = String(d.getMonth()+1).padStart(2,'0'); 
          const dd = String(d.getDate()).padStart(2,'0'); 
          return `${yyyy}-${mm}-${dd}`; 
      } 
      return String(d.getDate()); 
  }

  // --- STATE GLOBAL ---
  window.poDataRaw = [];
  window.poGtRaw = null;
  let detailPoRaw = []; 
  let sortState = { col: null, dir: 1 };
  let currentFilter = { closing:'', harian:'' };
  let currentDetailKode = ''; 
  let poAbort;
  window.currentUserKode = '000';

  function updatePoStickyHeader() {
      const thead = document.getElementById('poHead1');
      const scroller = document.getElementById('poScroller');
      if(thead && scroller) {
          scroller.style.setProperty('--po_headH', (thead.offsetHeight - 1) + 'px');
      }
  }
  window.addEventListener('resize', updatePoStickyHeader);

  // =========================================================
  // INIT PAGE & USER LOGIN (ANTI BOCOR)
  // =========================================================
  window.addEventListener('DOMContentLoaded', async () => {
      // 1. TANGKAP USER LOGIN DENGAN BENAR
      let k = '000';
      try {
          if (typeof window.getUser === 'function' && window.getUser()) {
              let u = window.getUser();
              k = u.kode || u.kode_kantor || u.kode_cabang || '000';
          } else if (localStorage.getItem('app_user')) {
              let u = JSON.parse(localStorage.getItem('app_user'));
              k = u.kode || u.kode_kantor || u.kode_cabang || '000';
          }
      } catch(e) {}
      
      window.currentUserKode = String(k).padStart(3, '0');
      document.getElementById('badgeUnit').innerText = (window.currentUserKode === '000') ? 'KONSOLIDASI' : `CABANG ${window.currentUserKode}`;

      // 2. KUNCI DROPDOWN CABANG
      await populateKantorOptionsPO(window.currentUserKode);

      // 3. SET DEFAULT DATE & FETCH
      try { 
          const res = await fetch('./api/date/');
          const j = await res.json();
          if(j?.data){
              document.getElementById('closing_date').value = j.data.last_closing;
              document.getElementById('harian_date').value  = j.data.last_created;
              currentFilter = { closing: j.data.last_closing, harian: j.data.last_created };
              fetchPotensiData();
          }
      } catch(e) { 
          const today = new Date().toISOString().split('T')[0];
          document.getElementById('closing_date').value = today;
          document.getElementById('harian_date').value = today;
          currentFilter = { closing: today, harian: today };
          fetchPotensiData();
      }
  });

  // --- FUNGSI LOCK DROPDOWN KANTOR ---
  async function populateKantorOptionsPO(userKode){
      const optKantor = document.getElementById('opt_kantor_rec');

      // JIKA YANG LOGIN CABANG -> LANGSUNG KUNCI MATI
      if(userKode && userKode !== '000'){
          optKantor.innerHTML = `<option value="${userKode}">CABANG ${userKode}</option>`;
          optKantor.value = userKode;
          optKantor.disabled = true;
          optKantor.classList.add('bg-slate-100', 'cursor-not-allowed');
          return; // Stop eksekusi agar tidak fetch semua cabang
      }

      // JIKA PUSAT -> BUKA SEMUA OPSI
      try {
          const res = await fetch('./api/kode/', { 
              method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({type:'kode_kantor'}) 
          });
          const json = await res.json();
          let list = json.data || [];
          
          let html = `<option value="">KONSOLIDASI (SEMUA)</option>`;
          list.filter(x => x.kode_kantor !== '000').sort((a,b) => String(a.kode_kantor).localeCompare(b.kode_kantor)).forEach(it => {
              html += `<option value="${String(it.kode_kantor).padStart(3,'0')}">${String(it.kode_kantor).padStart(3,'0')} - ${it.nama_kantor}</option>`;
          });
          optKantor.innerHTML = html;
          optKantor.disabled = false;
      } catch(e){
          optKantor.innerHTML = `<option value="">Error Load</option>`;
      }
  }

  // --- FILTER SUBMIT ---
  document.getElementById('filterForm').addEventListener('submit', e => {
    e.preventDefault();
    currentFilter.closing = document.getElementById('closing_date').value;
    currentFilter.harian  = document.getElementById('harian_date').value;
    sortState = { col:null, dir:1 }; 
    fetchPotensiData();
  });

  // =========================================================
  // FETCH REKAP POTENSI NPL
  // =========================================================
  async function fetchPotensiData(){
    const loading = document.getElementById('loadingPO');
    const loadingMini = document.getElementById('loadingMini');
    loading.classList.remove('hidden'); loadingMini.classList.remove('hidden');

    if(poAbort) poAbort.abort();
    poAbort = new AbortController();

    const tbody = document.getElementById('poBody');
    const ttotal = document.getElementById('poTotalRow');
    tbody.innerHTML = ''; ttotal.innerHTML = '';

    const kantor = document.getElementById('opt_kantor_rec').value || '';
    document.getElementById('thNamaPO').innerText = (kantor !== '') ? "NAMA KANKAS" : "NAMA KANTOR";

    try {
        const payload = { 
            type: 'Potensi NPL', 
            closing_date: currentFilter.closing, 
            harian_date: currentFilter.harian,
            kode_kantor: kantor
        };

        const res = await fetch('./api/npl/', {
            method: 'POST', headers: {'Content-Type':'application/json'},
            body: JSON.stringify(payload),
            signal: poAbort.signal
        });
        const json = await res.json();
        
        let data = [];
        let totalRow = null;

        if(json.data && json.data.data && json.data.grand_total) {
            data = json.data.data; 
            totalRow = json.data.grand_total;
        } else if (Array.isArray(json.data)) {
            data = json.data;
            totalRow = data.find(d => String(d.nama_cabang||d.nama_kantor||'').toUpperCase().includes('TOTAL'));
            data = data.filter(d => d !== totalRow);
        } else if (json.data && Array.isArray(json.data.data)) {
             data = json.data.data;
             totalRow = json.data.grand_total;
        }

        window.poGtRaw = totalRow;
        window.poDataRaw = data;
        window.poDataRaw.sort((a,b) => kodeNum(a.kode_cabang) - kodeNum(b.kode_cabang));

        renderTotal(totalRow);
        renderRows(window.poDataRaw);

    } catch(err){
        if(err.name !== 'AbortError') {
            console.error(err);
            tbody.innerHTML = `<tr><td colspan="12" class="p-8 text-center text-red-500 font-bold bg-red-50">Gagal memuat data rekap.</td></tr>`;
        }
    } finally {
        loading.classList.add('hidden'); loadingMini.classList.add('hidden');
        setTimeout(updatePoStickyHeader, 50);
    }
  }

  // --- RENDER TOTAL BARIS (SEMUA NOA BISA DIKLIK BERDASARKAN STATUS) ---
  function renderTotal(tot){
      const el = document.getElementById('poTotalRow');
      if(!tot) return;

      const dropVal = document.getElementById('opt_kantor_rec').value || '';
      const targetKode = dropVal !== '' ? dropVal : '000'; 

      const getLink = (angka, status, colorClass) => {
          if (num(angka) > 0) {
              return `<a href="#" class="${colorClass} font-bold hover:underline" onclick="event.preventDefault(); checkAccessAndOpenModal('${targetKode}', '${status}')">${fmtInt(angka)}</a>`;
          }
          return `<span class="text-slate-400">${fmtInt(angka)}</span>`;
      };

      el.innerHTML = `
        <tr class="row-total">
            <td class="sticky-left-1 font-mono font-bold text-slate-500 text-xs">ALL</td>
            <td class="sticky-left-2 font-bold text-slate-800 text-xs md:text-sm uppercase text-blue-900">${tot.nama_cabang || tot.nama_kantor || 'TOTAL KONSOLIDASI'}</td>
            
            <td class="text-center bg-blue-100">${getLink(tot.total_noa, 'ALL', 'text-blue-700 hover:text-blue-900')}</td>
            <td class="text-right font-bold text-blue-700 bg-blue-100 pr-4">${fmtNom(tot.total_baki)}</td>
            
            <td class="text-center bg-green-100">${getLink(tot.noa_aman, 'AMAN', 'text-green-700 hover:text-green-900')}</td>
            <td class="text-right font-bold text-green-700 bg-green-100 pr-4">${fmtNom(tot.baki_aman)}</td>
            
            <td class="text-center bg-yellow-100">${getLink(tot.noa_jt, 'JATUH TEMPO', 'text-yellow-700 hover:text-yellow-900')}</td>
            <td class="text-right font-bold text-yellow-700 bg-yellow-100 pr-4">${fmtNom(tot.baki_jt)}</td>
            
            <td class="text-center bg-red-100">${getLink(tot.noa_flow, 'FLOW KOLEK', 'text-red-700 hover:text-red-900')}</td>
            <td class="text-right font-bold text-red-700 bg-red-100 pr-4">${fmtNom(tot.baki_flow)}</td>
            
            <td class="text-center bg-orange-100">${getLink(tot.noa_potensi, 'MASIH POTENSI', 'text-orange-700 hover:text-orange-900')}</td>
            <td class="text-right font-bold text-orange-700 bg-orange-100 pr-4">${fmtNom(tot.baki_potensi)}</td>
        </tr>
      `;
  }

  function renderRows(rows){
      const tbody = document.getElementById('poBody');
      if(rows.length === 0){ tbody.innerHTML = `<tr><td colspan="12" class="p-8 text-center text-slate-400">Tidak ada data.</td></tr>`; return; }
      
      tbody.innerHTML = rows.map(r => {
          const rawKode = r.kode_cabang || r.kode_unit || '';
          const kode = String(rawKode).padStart(3,'0');
          const nama = r.nama_cabang || r.nama_kantor || '-';
          
          const getLink = (angka, status, colorClass) => {
              if (num(angka) > 0) {
                  return `<a href="#" class="${colorClass} font-bold hover:underline" onclick="event.preventDefault(); checkAccessAndOpenModal('${kode}', '${status}')">${fmtInt(angka)}</a>`;
              }
              return `<span class="text-slate-300">${fmtInt(angka)}</span>`;
          };

          return `
            <tr class="transition border-b">
                <td class="sticky-left-1 font-mono font-bold text-slate-500 text-xs">${kode}</td>
                <td class="sticky-left-2 font-semibold text-slate-700 text-xs md:text-sm"><div class="truncate" title="${nama}">${nama}</div></td>
                
                <td class="text-center bg-blue-50/30">${getLink(r.total_noa, 'ALL', 'text-blue-600')}</td>
                <td class="text-right text-slate-700 font-bold bg-blue-50/30 pr-4">${fmtNom(r.total_baki)}</td>
                
                <td class="text-center bg-green-50/30">${getLink(r.noa_aman, 'AMAN', 'text-green-600')}</td>
                <td class="text-right text-slate-600 bg-green-50/30 pr-4">${fmtNom(r.baki_aman)}</td>
                
                <td class="text-center bg-yellow-50/30">${getLink(r.noa_jt, 'JATUH TEMPO', 'text-yellow-600')}</td>
                <td class="text-right text-slate-600 bg-yellow-50/30 pr-4">${fmtNom(r.baki_jt)}</td>
                
                <td class="text-center bg-red-50/30">${getLink(r.noa_flow, 'FLOW KOLEK', 'text-red-600')}</td>
                <td class="text-right text-slate-700 font-bold bg-red-50/30 pr-4">${fmtNom(r.baki_flow)}</td>
                
                <td class="text-center bg-orange-50/30">${getLink(r.noa_potensi, 'MASIH POTENSI', 'text-orange-600')}</td>
                <td class="text-right text-slate-600 bg-orange-50/30 pr-4">${fmtNom(r.baki_potensi)}</td>
            </tr>
          `;
      }).join('');

      tbody.innerHTML += `<tr style="height: 60px;"><td colspan="12" class="border-none bg-transparent"></td></tr>`;
  }

  // --- SORTING REKAP ---
  window.doSort = function(col) {
      sortState = { col: col, dir: sortState.col === col ? -sortState.dir : 1 };
      const sorted = [...window.poDataRaw].sort((a,b) => {
          const valA = num(a[col]);
          const valB = num(b[col]);
          return (valA - valB) * sortState.dir;
      });
      renderRows(sorted);
  };

  // --- EXPORT EXCEL REKAP UTAMA ---
  function exportPotensiExcel() {
      const rows = window.poDataRaw || [];
      const gt = window.poGtRaw || null;
      if(rows.length === 0) { alert("Tidak ada data rekap untuk diexport!"); return; }

      let table = `<table border="1">
          <thead>
              <tr>
                  <th rowspan="2" style="background-color:#eff6ff;">KODE</th>
                  <th rowspan="2" style="background-color:#eff6ff;">NAMA KANTOR</th>
                  <th colspan="2" style="background-color:#dbeafe;">TOTAL POTENSI</th>
                  <th colspan="2" style="background-color:#dcfce7;">AMAN</th>
                  <th colspan="2" style="background-color:#fef08a;">JATUH TEMPO</th>
                  <th colspan="2" style="background-color:#fee2e2;">FLOW KOLEK</th>
                  <th colspan="2" style="background-color:#ffedd5;">MASIH POTENSI</th>
              </tr>
              <tr>
                  <th>NOA</th><th>BAKI DEBET</th>
                  <th>NOA</th><th>BAKI DEBET</th>
                  <th>NOA</th><th>BAKI DEBET</th>
                  <th>NOA</th><th>BAKI DEBET</th>
                  <th>NOA</th><th>BAKI DEBET</th>
              </tr>
          </thead>
          <tbody>`;
      
      if(gt) {
          table += `<tr>
              <td style="font-weight:bold;"></td>
              <td style="font-weight:bold;">${gt.nama_cabang || gt.nama_kantor || 'GRAND TOTAL'}</td>
              <td style="font-weight:bold;">${gt.total_noa}</td>
              <td style="font-weight:bold;">${gt.total_baki}</td>
              <td style="font-weight:bold;">${gt.noa_aman}</td>
              <td style="font-weight:bold;">${gt.baki_aman}</td>
              <td style="font-weight:bold;">${gt.noa_jt}</td>
              <td style="font-weight:bold;">${gt.baki_jt}</td>
              <td style="font-weight:bold;">${gt.noa_flow}</td>
              <td style="font-weight:bold;">${gt.baki_flow}</td>
              <td style="font-weight:bold;">${gt.noa_potensi}</td>
              <td style="font-weight:bold;">${gt.baki_potensi}</td>
          </tr>`;
      }

      rows.forEach(r => {
          const kode = r.kode_cabang || r.kode_unit || '-';
          const nama = r.nama_cabang || r.nama_kantor || '-';
          table += `<tr>
              <td style="mso-number-format:'\\@'">${kode}</td>
              <td>${nama}</td>
              <td>${r.total_noa}</td>
              <td>${r.total_baki}</td>
              <td>${r.noa_aman}</td>
              <td>${r.baki_aman}</td>
              <td>${r.noa_jt}</td>
              <td>${r.baki_jt}</td>
              <td>${r.noa_flow}</td>
              <td>${r.baki_flow}</td>
              <td>${r.noa_potensi}</td>
              <td>${r.baki_potensi}</td>
          </tr>`;
      });
      table += `</tbody></table>`;

      const tgl = document.getElementById('harian_date').value;
      const blob = new Blob([table], { type: 'application/vnd.ms-excel' });
      const a = document.createElement('a');
      a.href = window.URL.createObjectURL(blob);
      a.download = `Rekap_Potensi_NPL_${tgl}.xls`;
      document.body.appendChild(a); a.click(); document.body.removeChild(a);
  }

  // =========================================================
  // LOGIC MODAL DETAIL & SECURITY
  // =========================================================
  function closeModalPeringatan() {
      const modal = document.getElementById('modalPeringatan');
      modal.classList.add('hidden');
      modal.classList.remove('flex');
  }

  window.checkAccessAndOpenModal = function(targetKode, statusFilter = 'ALL') {
      const userKode = window.currentUserKode;
      const targetCabang = targetKode.length >= 3 ? targetKode.substring(0,3) : targetKode; 
      
      // PROTEKSI: CEGAH CABANG A MELIHAT DATA CABANG B
      if (userKode !== '000' && userKode !== targetCabang) {
          document.getElementById('warnUserLvl').innerText = `Unit ${userKode}`;
          document.getElementById('warnTargetLvl').innerText = `Unit ${targetCabang}`;
          const modalWarn = document.getElementById('modalPeringatan');
          modalWarn.classList.remove('hidden');
          modalWarn.classList.add('flex');
          return;
      }
      openModalDetail(targetKode, statusFilter);
  };

  async function openModalDetail(kode, statusFilter = 'ALL'){
      const targetCabang = kode.length >= 3 ? kode.substring(0,3) : kode;
      const targetKankas = kode.length > 3 ? kode : '';
      
      currentDetailKode = targetCabang; 
      
      const modal = document.getElementById('modalDebiturPotensi');
      const title = document.getElementById('modalTitlePotensi');
      const sub   = document.getElementById('modalSubtitlePO');
      
      modal.classList.remove('hidden'); modal.classList.add('flex');
      let titleLabel = kode === '000' ? 'KONSOLIDASI' : kode;
      title.innerHTML = `Detail Potensi <span class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-800 text-xs rounded font-mono border border-blue-200">${titleLabel}</span>`;
      sub.innerText = `Posisi: ${formatDate(currentFilter.closing)} vs ${formatDate(currentFilter.harian)}`;
      
      // AUTO SELECT STATUS DROPDOWN BERDASARKAN ANGKA YG DIKLIK
      const statDropdown = document.getElementById('modalFilterStatus');
      if (statDropdown) statDropdown.value = statusFilter;

      // POPULATE DROPDOWN KANKAS DI DALAM MODAL
      const selKankas = document.getElementById('modalFilterKankas');
      selKankas.innerHTML = '<option value="">Semua Kankas</option>';
      
      if(targetCabang !== '000') {
          selKankas.classList.remove('hidden');
          try {
              const r = await fetch('./api/kode/', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({type:'kode_kankas', kode_kantor:targetCabang}) });
              const j = await r.json();
              (j.data||[]).forEach(k => { 
                  const isSelected = (targetKankas === k.kode_group1) ? 'selected' : '';
                  selKankas.innerHTML += `<option value="${k.kode_group1}" ${isSelected}>${k.kode_group1} - ${k.deskripsi_group1}</option>`; 
              });
          } catch(e){}
      } else {
          selKankas.classList.add('hidden');
      }

      fetchDetailPotensiNpl();
  }

  async function fetchDetailPotensiNpl() {
      const tbody = document.getElementById('modalBodyRowsPO');
      const ttot  = document.getElementById('modalTotalRowPO');
      const kankas = document.getElementById('modalFilterKankas')?.value || ''; 

      tbody.innerHTML = `<tr><td colspan="18" class="p-12 text-center"><div class="animate-spin h-8 w-8 border-4 border-slate-200 border-t-blue-600 rounded-full mx-auto mb-3"></div><span class="text-slate-500 font-medium">Sedang mengambil data detail...</span></td></tr>`;
      ttot.innerHTML = '';

      try {
          console.log("Request Detail Potensi NPL:", currentDetailKode, "Kankas:", kankas);
          
          const payload = { 
              type: 'Debitur Potensi NPL', // STRING INI SUDAH BENAR & SINKRON DENGAN BACKEND
              kode_kantor: currentDetailKode === '000' ? '' : currentDetailKode, 
              kode_kankas: kankas,             
              closing_date: currentFilter.closing, 
              harian_date: currentFilter.harian 
          };
          
          const res = await fetch('./api/npl/', {
              method:'POST', headers:{'Content-Type':'application/json'},
              body: JSON.stringify(payload)
          });
          const json = await res.json();
          detailPoRaw = Array.isArray(json.data) ? json.data : []; 
          
          console.log("Berhasil load detail:", detailPoRaw.length, "baris");

          // Render memanggil function yang bisa menyaring Dropdown Status
          renderDetailRows(); 

      } catch(e){
          console.error("Error saat fetch Detail Potensi:", e); 
          tbody.innerHTML = `<tr><td colspan="18" class="p-10 text-center text-red-500">Gagal memuat data detail. ${e.message}</td></tr>`;
      }
  }

  // --- RENDER ROWS DETAIL DI DALAM MODAL (DENGAN FILTER STATUS) ---
  window.renderDetailRows = function() {
      const tbody = document.getElementById('modalBodyRowsPO');
      const ttot  = document.getElementById('modalTotalRowPO');
      
      const statusEl = document.getElementById('modalFilterStatus');
      const statusFilter = statusEl ? statusEl.value : 'ALL';

      // Lakukan Penyaringan Data Array Javascript sesuai value Dropdown Status
      let filteredData = detailPoRaw;
      if(statusFilter !== 'ALL') {
          filteredData = detailPoRaw.filter(d => {
              if (statusFilter === 'AMAN') return d.status_potensi === 'AMAN' || d.status_potensi === 'LUNAS / AMAN';
              return d.status_potensi === statusFilter;
          });
      }

      if(filteredData.length === 0){ 
          tbody.innerHTML = `<tr><td colspan="18" class="p-10 text-center text-slate-400">Data tidak ditemukan pada filter ini.</td></tr>`; 
          ttot.innerHTML = '';
          return; 
      }

      let totals = { bd_c:0, bd_h:0, tp:0, tb:0, sa:0, ap:0, ab:0 };

      const rowsHtml = filteredData.map(d => {
          totals.bd_c += num(d.baki_debet_closing);
          totals.bd_h += num(d.baki_debet_harian);
          totals.tp   += num(d.tunggakan_pokok);
          totals.tb   += num(d.tunggakan_bunga);
          totals.sa   += num(d.saldo_akhir); // Menjumlahkan Saldo Tabungan
          totals.ap   += num(d.angsuran_pokok);
          totals.ab   += num(d.angsuran_bunga);
          
          let spanStatus = '';
          if(d.status_potensi === 'AMAN' || d.status_potensi === 'LUNAS / AMAN') {
              spanStatus = `<span class="status-aman">${d.status_potensi}</span>`;
          } else if(d.status_potensi === 'FLOW KOLEK') {
              spanStatus = `<span class="status-flow">${d.status_potensi}</span>`;
          } else if(d.status_potensi === 'JATUH TEMPO') {
              spanStatus = `<span class="status-jt">${d.status_potensi}</span>`;
          } else {
              spanStatus = `<span class="text-orange-600 font-bold bg-orange-100 px-2 py-0.5 rounded">${d.status_potensi}</span>`;
          }

          return `
            <tr class="hover:bg-slate-50 border-b">
                <td class="modal-freeze-1 font-mono text-slate-600 text-xs">${d.no_rekening}</td>
                <td class="modal-freeze-2 font-medium text-xs text-slate-700" title="${d.nama_nasabah}">${d.nama_nasabah}</td>
                
                <td class="text-left text-xs text-slate-600 truncate max-w-[200px]" title="${d.alamat || '-'}">${d.alamat || '-'}</td>
                <td class="text-center text-[10px]">${spanStatus}</td>
                <td class="text-center text-[10px] text-slate-500">${d.kolek_closing || '-'}</td>
                <td class="text-right text-xs text-slate-600">${fmtNom(d.baki_debet_closing)}</td>
                
                <td class="text-center text-[10px] text-red-600 font-bold bg-red-50/50">${d.kolek_harian || '-'}</td>
                <td class="text-right text-xs font-bold text-red-700 bg-red-50/50">${fmtNom(d.baki_debet_harian)}</td>
                
                <td class="text-right text-xs">${fmtNom(d.tunggakan_pokok)}</td>
                <td class="text-right text-xs">${fmtNom(d.tunggakan_bunga)}</td>
                <td class="text-right text-xs font-semibold text-green-700">${fmtNom(d.saldo_akhir)}</td>
                
                <td class="text-center text-xs">${formatJTByRule(d.jt_harian)}</td>
                <td class="text-center text-xs font-bold text-red-600 bg-red-50/50">${fmtInt(d.hm_harian)}</td>
                <td class="text-center text-xs">${fmtInt(d.hmp_harian)}</td>
                <td class="text-center text-xs">${fmtInt(d.hmb_harian)}</td>
                <td class="text-right text-xs text-slate-500">${fmtNom(d.angsuran_pokok)}</td>
                <td class="text-right text-xs text-slate-500">${fmtNom(d.angsuran_bunga)}</td>
                <td class="text-center text-xs text-slate-500">${d.tgl_trans_terakhir ? formatDate(d.tgl_trans_terakhir) : '-'}</td>
            </tr>
          `;
      }).join('');

      ttot.innerHTML = `
        <tr class="modal-total-row">
            <td class="modal-freeze-1">TOTAL</td>
            <td class="modal-freeze-2">${filteredData.length} Debitur</td>
            <td class="text-center">-</td>
            <td class="text-center">-</td>
            <td class="text-center">-</td>
            <td class="text-right">${fmtNom(totals.bd_c)}</td>
            <td class="text-center">-</td>
            <td class="text-right text-red-700 bg-red-50/50">${fmtNom(totals.bd_h)}</td>
            <td class="text-right">${fmtNom(totals.tp)}</td>
            <td class="text-right">${fmtNom(totals.tb)}</td>
            <td class="text-right text-green-700">${fmtNom(totals.sa)}</td>
            <td colspan="4"></td>
            <td class="text-right">${fmtNom(totals.ap)}</td>
            <td class="text-right">${fmtNom(totals.ab)}</td>
            <td></td>
        </tr>
      `;
      tbody.innerHTML = rowsHtml;
  }

  // --- EXPORT EXCEL DETAIL DARI MODAL ---
  function exportDetailPotensiExcel() {
      const statusEl = document.getElementById('modalFilterStatus');
      const statusFilter = statusEl ? statusEl.value : 'ALL';
      
      let filteredData = detailPoRaw;
      if(statusFilter !== 'ALL') {
          filteredData = detailPoRaw.filter(d => {
              if (statusFilter === 'AMAN') return d.status_potensi === 'AMAN' || d.status_potensi === 'LUNAS / AMAN';
              return d.status_potensi === statusFilter;
          });
      }

      if(filteredData.length === 0) { alert("Tidak ada detail untuk diexport!"); return; }

      let table = `<table border="1">
          <thead>
              <tr>
                  <th style="background-color:#f1f5f9;">NO REKENING</th>
                  <th style="background-color:#f1f5f9;">NAMA NASABAH</th>
                  <th style="background-color:#f1f5f9;">ALAMAT</th>
                  <th style="background-color:#f1f5f9;">STATUS SAAT INI</th>
                  <th style="background-color:#f1f5f9;">KOL CLOSING</th>
                  <th style="background-color:#f1f5f9;">BD CLOSING</th>
                  <th style="background-color:#fee2e2;">KOL HARIAN</th>
                  <th style="background-color:#fee2e2;">BD HARIAN</th>
                  <th style="background-color:#f1f5f9;">TUNGG POKOK</th>
                  <th style="background-color:#f1f5f9;">TUNGG BUNGA</th>
                  <th style="background-color:#dcfce7;">SALDO TABUNGAN</th>
                  <th style="background-color:#f1f5f9;">JT</th>
                  <th style="background-color:#fee2e2;">DPD</th>
                  <th style="background-color:#f1f5f9;">DPD TP</th>
                  <th style="background-color:#f1f5f9;">DPD TB</th>
                  <th style="background-color:#f1f5f9;">ANGS. POKOK</th>
                  <th style="background-color:#f1f5f9;">ANGS. BUNGA</th>
                  <th style="background-color:#f1f5f9;">TGL TRANS</th>
              </tr>
          </thead>
          <tbody>`;

      filteredData.forEach(d => {
          table += `<tr>
              <td style="mso-number-format:'\\@'">${d.no_rekening}</td>
              <td>${d.nama_nasabah}</td>
              <td>${d.alamat || ''}</td>
              <td>${d.status_potensi || ''}</td>
              <td>${d.kolek_closing || ''}</td>
              <td>${d.baki_debet_closing}</td>
              <td style="background-color:#fef2f2;">${d.kolek_harian || ''}</td>
              <td style="background-color:#fef2f2;">${d.baki_debet_harian}</td>
              <td>${d.tunggakan_pokok}</td>
              <td>${d.tunggakan_bunga}</td>
              <td style="background-color:#dcfce7;">${d.saldo_akhir || 0}</td>
              <td>${d.jt_harian || ''}</td>
              <td style="background-color:#fef2f2;">${d.hm_harian || 0}</td>
              <td>${d.hmp_harian || 0}</td>
              <td>${d.hmb_harian || 0}</td>
              <td>${d.angsuran_pokok}</td>
              <td>${d.angsuran_bunga}</td>
              <td>${d.tgl_trans_terakhir || ''}</td>
          </tr>`;
      });
      table += `</tbody></table>`;

      const blob = new Blob([table], { type: 'application/vnd.ms-excel' });
      const a = document.createElement('a');
      a.href = window.URL.createObjectURL(blob);
      
      const kankasEl = document.getElementById('modalFilterKankas');
      const valKankas = kankasEl ? kankasEl.value : '';
      const downloadName = valKankas ? valKankas : currentDetailKode;
      
      a.download = `Detail_Potensi_NPL_${downloadName}_${statusFilter}.xls`;
      document.body.appendChild(a); a.click(); document.body.removeChild(a);
  }

  const closePoModal = () => { document.getElementById('modalDebiturPotensi').classList.add('hidden'); document.getElementById('modalDebiturPotensi').classList.remove('flex'); };
  document.getElementById('btnClosePO').onclick = closePoModal;
  document.addEventListener('keydown', e => { if(e.key === 'Escape') closePoModal(); });
</script>