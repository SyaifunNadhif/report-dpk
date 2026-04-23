<style>
  :root { --primary: #059669; --bg: #f8fafc; --text: #334155; }
  body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text); overflow: hidden; }
  
  .inp { 
      box-sizing: border-box;
      border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 0 0.5rem; 
      font-size: 12px; background: #fff; height: 36px; cursor: pointer; 
      outline: none; transition: border 0.2s; min-width: 0; font-weight: 600;
  }
  .inp:focus { border-color: var(--primary); box-shadow: 0 0 0 2px #a7f3d0; }
  .inp:disabled { background-color: #f1f5f9; color: #64748b; cursor: not-allowed; border-color: #e2e8f0; }
  
  input[type="date"] { position: relative; cursor: pointer; }
  input[type="date"]::-webkit-inner-spin-button,
  input[type="date"]::-webkit-calendar-picker-indicator {
      position: absolute; top: 0; left: 0; right: 0; bottom: 0;
      width: 100%; height: 100%; opacity: 0; cursor: pointer;
  }

  /* Scroller Tabel Utama */
  #progScroller { 
      --prog_headH: 40px; 
      overflow: auto; height: 100%; border-radius: 8px; 
      border: 1px solid #e2e8f0; background: white; position: relative;
      -webkit-overflow-scrolling: touch; 
  }
  
  table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 12px; }
  th, td { white-space: nowrap; padding: 8px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
  tr:hover td { background-color: #f8fafc; }

  /* Header Utama */
  thead th { 
      position: sticky; top: 0; z-index: 60; 
      background: #e2e8f0; color: #1e293b; font-weight: 800; 
      text-transform: uppercase; border-bottom: 2px solid #cbd5e1;
      font-size: 11px; letter-spacing: 0.05em;
  }
  .col-kategori { position: sticky; left: 0; z-index: 45; background: white; border-right: 1px solid #e2e8f0; box-shadow: 2px 0 5px rgba(0,0,0,0.03); min-width: 150px; font-weight: bold;}
  thead th.col-kategori { z-index: 70; background: #e2e8f0; }

  .sticky-total td { 
      position: sticky; top: var(--prog_headH); z-index: 55; 
      background: #f4f7fb; font-weight: 800; border-bottom: 2px solid #bfdbfe; 
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); 
  }
  .sticky-total td.col-kategori { z-index: 65; background: #f4f7fb; border-right: 1px solid #bfdbfe; }

  /* Animasi Modal */
  @keyframes scaleUp { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
  .animate-scale-up { animation: scaleUp 0.2s ease-out forwards; }

  /* 🔥 FIX BUG OVERLAP: Sticky Header Modal Detail */
  #tableDetailProg th, #tableDetailProg td { background-color: #fff; } /* Solid background */
  #tableDetailProg thead th { position: sticky; top: 0; z-index: 40; background-color: #f1f5f9; box-shadow: inset 0 -1px 0 #cbd5e1; }
  .mod-freeze-rek { position: sticky; left: 0; z-index: 42 !important; background-color: #f1f5f9 !important; box-shadow: inset -1px 0 0 #cbd5e1; min-width: 100px; max-width: 100px;}
  .mod-freeze-nas { position: sticky; left: 100px; z-index: 41 !important; background-color: #f1f5f9 !important; box-shadow: inset -1px 0 0 #cbd5e1; min-width: 200px; max-width: 250px;}
  .mod-td-rek { position: sticky; left: 0; z-index: 32 !important; background-color: #fff !important; box-shadow: inset -1px 0 0 #f1f5f9;}
  .mod-td-nas { position: sticky; left: 100px; z-index: 31 !important; background-color: #fff !important; box-shadow: inset -1px 0 0 #f1f5f9; }
  tbody.mod-body tr:hover td { background-color: #f8fafc !important; }
  tbody.mod-body tr:hover td.mod-td-rek, tbody.mod-body tr:hover td.mod-td-nas { filter: brightness(0.97); }

  @media (max-width: 767px) {
      .col-kategori { left: 0 !important; z-index: 45 !important; min-width: 120px; white-space: normal; line-height: 1.2; }
      thead th.col-kategori { z-index: 70 !important; }
      .sticky-total td.col-kategori { z-index: 65 !important; }
      .mod-freeze-nas, .mod-td-nas { left: 0 !important; box-shadow: inset -1px 0 0 #f1f5f9; min-width: 150px;}
      .mod-freeze-rek, .mod-td-rek { display: none !important; }
  }
</style>

<div class="max-w-[1600px] mx-auto px-3 md:px-4 py-4 h-[calc(100vh-80px)] md:h-[calc(100vh-120px)] flex flex-col relative z-10">
  
  <div class="flex flex-col xl:flex-row xl:items-end justify-between gap-3 mb-4 shrink-0">
    <div class="flex items-start justify-between w-full xl:w-auto">
        <div>
            <h1 class="text-xl md:text-2xl font-bold flex items-center gap-2 text-slate-800">
                <span class="bg-emerald-600 text-white p-1.5 rounded-lg text-sm shadow-sm">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                </span> 
                <span>Aging Kredit (%)</span>
            </h1>
            <p class="text-[10px] md:text-xs text-slate-500 mt-1 ml-1 font-medium" id="lbl_filter_aktif">*Posisi Harian: Menunggu data...</p>
        </div>

        <button id="btnToggleProgFilter" class="xl:hidden flex items-center gap-1.5 px-3 py-1.5 border border-gray-300 rounded-lg bg-white text-sm font-semibold text-slate-700 shadow-sm transition">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
            Filter
        </button>
    </div>

    <div id="panelFilterProg" class="hidden xl:block bg-white border border-gray-200 rounded-xl p-3 shadow-sm w-full xl:w-auto transition-all">
        <form id="formFilterProg" class="flex flex-col md:flex-row items-end gap-2 md:gap-3 w-full">
            <div class="flex flex-col w-full md:w-[130px]">
                <label class="text-[9px] font-extrabold text-slate-500 uppercase ml-1 mb-1 tracking-wider">TANGGAL</label>
                <input type="date" id="harian_date_prog" class="inp shadow-sm text-slate-700" required>
            </div>

            <div class="flex flex-col w-full md:w-[200px]" id="wrap-cabang">
                <label class="text-[9px] font-extrabold text-slate-500 uppercase ml-1 mb-1 tracking-wider">CABANG</label>
                <select id="opt_kantor_prog" class="inp text-slate-700 shadow-sm truncate" onchange="handleCabangChange()">
                    <option value="">ALL | SEMUA CABANG</option>
                </select>
            </div>

            <div class="flex flex-col w-full md:w-[160px]">
                <label id="lbl_sub_prog" class="text-[9px] font-extrabold text-slate-500 uppercase ml-1 mb-1 tracking-wider">KORWIL</label>
                <select id="opt_sub_prog" class="inp text-slate-700 shadow-sm truncate" onchange="triggerAutoRefresh()">
                    <option value="">ALL KORWIL</option>
                    <option value="SEMARANG">SEMARANG</option>
                    <option value="SOLO">SOLO</option>
                    <option value="BANYUMAS">BANYUMAS</option>
                    <option value="PEKALONGAN">PEKALONGAN</option>
                </select>
            </div>
            
            <div class="flex gap-2 shrink-0 mt-2 md:mt-0 w-full md:w-auto">
                <button type="submit" class="flex-1 md:flex-none bg-emerald-600 hover:bg-emerald-700 text-white h-9 px-4 rounded-lg font-bold text-sm shadow-sm flex items-center justify-center gap-2 transition">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <span class="hidden md:inline">CARI</span>
                </button>
                <button type="button" onclick="exportProgExcel()" class="bg-indigo-600 hover:bg-indigo-700 text-white h-9 px-3 md:w-11 rounded-lg shadow-sm flex items-center justify-center transition" title="Download Excel Rekap">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    <span class="md:hidden ml-1 font-bold text-xs">EXCEL</span>
                </button>
            </div>
        </form>
    </div>
  </div>

  <div class="flex-1 min-h-0 relative flex flex-col bg-white rounded-xl shadow-sm border border-slate-200">
    <div id="loadingProg" class="hidden absolute inset-0 bg-white/80 z-[100] flex flex-col items-center justify-center text-emerald-600 font-bold text-sm backdrop-blur-sm rounded-xl">
        <div class="animate-spin h-8 w-8 border-4 border-emerald-200 border-t-emerald-600 rounded-full mb-2"></div>
        Menganalisa Progress...
    </div>

    <div class="table-wrapper custom-scrollbar" id="progScroller">
      <table id="tabelProgKredit">
        <thead id="theadProg">
          <tr>
            <th class="col-kategori text-left">PROGRESS JALAN (%)</th>
            <th class="text-right min-w-[100px] text-blue-800">TOTAL PORTOFOLIO</th>
            <th class="text-right min-w-[120px] text-emerald-700">LANCAR & DPK (L, DP)</th>
            <th class="text-right min-w-[120px] text-red-600">NPL (KL, D, M)</th>
            <th class="text-center min-w-[80px] border-l border-slate-300">% NPL KONTRIBUSI</th>
          </tr>
        </thead>
        <tbody id="totalProg"></tbody>
        <tbody id="bodyProg"></tbody>
      </table>
    </div>
  </div>
</div>

<div id="modalDetailProg" class="fixed inset-0 hidden z-[9999] flex items-end md:items-center justify-center p-0 sm:p-4">
  <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModalProg()"></div>
  <div class="relative bg-white w-full h-[95vh] md:h-[92vh] max-w-[1600px] rounded-t-xl md:rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-scale-up">
    
    <div class="flex flex-col bg-white border-b shrink-0 w-full z-50">
        <div class="flex flex-row items-center justify-between px-3 py-2.5 md:px-4 md:py-3 gap-2 w-full">
            <div class="flex-1 min-w-0">
              <h3 class="font-bold text-slate-800 flex items-center gap-1.5 text-[12px] md:text-xl leading-none truncate">
                  <span class="w-1.5 md:w-2 h-4 md:h-6 bg-emerald-600 rounded-full hidden md:block shrink-0"></span> 
                  <span id="mdlTitleProg" class="truncate">Detail Debitur</span>
              </h3>
              <p class="text-[9px] md:text-sm text-slate-500 mt-1 md:ml-4 font-mono font-medium leading-none truncate" id="mdlSubTitleProg">...</p>
            </div>
            
            <div class="flex flex-row items-center gap-1.5 md:gap-2 shrink-0">
                <select id="opt_ao_prog_modal" class="inp px-1 md:px-2 h-[32px] md:h-[36px] w-[120px] md:w-[180px] text-[10px] md:text-xs font-bold text-slate-700 bg-slate-50 border-slate-200 outline-none shrink-0 cursor-pointer" onchange="loadDetailProgPage(1)">
                    <option value="">SEMUA AO</option>
                </select>

                <button onclick="exportDetailProgExcel()" class="btn-icon bg-emerald-600 hover:bg-emerald-700 text-white px-2.5 md:px-4 h-[32px] md:h-[36px] rounded-lg shadow-sm shrink-0 transition flex items-center gap-1">
                    <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span class="hidden md:inline font-bold text-xs uppercase tracking-wider">Export Detail</span>
                </button>
                <button onclick="closeModalProg()" class="w-[32px] h-[32px] md:w-[36px] md:h-[36px] flex items-center justify-center rounded-lg bg-red-50 hover:bg-red-500 hover:text-white text-red-500 transition font-bold text-xl leading-none shrink-0">&times;</button>
            </div>
        </div>
    </div>

    <div class="flex-1 overflow-auto bg-slate-50 relative custom-scrollbar">
      <div id="loadingModalProg" class="hidden absolute inset-0 bg-white/90 z-40 flex flex-col items-center justify-center text-emerald-600 backdrop-blur-sm">
         <div class="animate-spin h-8 w-8 md:h-10 md:w-10 border-4 border-emerald-200 border-t-emerald-600 rounded-full mb-2"></div>
         <span class="text-[10px] md:text-sm font-bold uppercase tracking-widest">Memuat Detail...</span>
      </div>
      
      <table class="w-max min-w-full text-left text-slate-700 table-fixed" id="tableDetailProg">
        <thead class="text-[9px] md:text-xs text-slate-600 uppercase tracking-wider select-none">
            <tr>
                <th class="mod-freeze-rek px-2 md:px-3 hidden md:table-cell">REKENING</th>
                <th class="mod-freeze-nas px-2 md:px-4">NAMA NASABAH</th>
                <th class="px-2 md:px-4 min-w-[200px] max-w-[300px] truncate">ALAMAT</th>
                <th class="px-2 md:px-3 text-center w-[100px]">NO HP (WA)</th>
                <th class="px-2 md:px-3 text-center w-[120px]">KANKAS</th>
                <th class="px-2 md:px-4 text-blue-700 text-center w-[150px] truncate">NAMA AO</th>
                <th class="px-2 md:px-3 text-center w-[100px]">TGL CAIR</th>
                <th class="px-2 md:px-3 text-center w-[100px] text-orange-600">TGL JT</th>
                <th class="px-2 md:px-4 text-right w-[130px]">PLAFOND</th>
                <th class="px-2 md:px-4 text-right w-[130px] text-blue-800">OS (BAKI DEBET)</th>
                <th class="px-2 md:px-3 text-center w-[80px]">KOLEK</th>
                <th class="px-2 md:px-3 text-center w-[100px] text-emerald-700">% JALAN</th>
            </tr>
        </thead>
        <tbody id="bodyModalProg" class="mod-body text-[9.5px] md:text-xs"></tbody>
      </table>
    </div>

    <div class="px-3 py-2.5 md:px-5 md:py-4 border-t bg-white flex justify-between items-center shrink-0">
      <span class="text-[9px] md:text-sm font-bold text-slate-600 bg-slate-100 px-2 md:px-3 py-1 rounded-md md:rounded-lg" id="pageInfoProg">0 Data</span>
      <div class="flex gap-1 md:gap-2">
          <button id="btnPrevProg" onclick="changePageProg(-1)" class="px-2.5 md:px-4 py-1.5 md:py-2 bg-white border border-slate-300 rounded-md md:rounded-lg text-[9px] md:text-sm font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-50 transition shadow-sm">« Prev</button>
          <button id="btnNextProg" onclick="changePageProg(1)" class="px-2.5 md:px-4 py-1.5 md:py-2 bg-white border border-slate-300 rounded-md md:rounded-lg text-[9px] md:text-sm font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-50 transition shadow-sm">Next »</button>
      </div>
    </div>
  </div>
</div>

<script>
  const API_KREDIT = './api/kredit/'; 
  const API_KODE   = './api/kode/';
  const API_DATE   = './api/date/';

  const nf = new Intl.NumberFormat('id-ID');
  const fmt = n => nf.format(Number(n||0));
  const fmt2 = x => (x == null || x === '' ? '0.00' : Number(x).toFixed(2));

  window.progDataRaw = [];
  window.progGtRaw = null;

  // Variables untuk Pagination Detail
  let currentProgDetailParams = {};
  let currentProgPage = 1;
  let currentProgTotalPages = 1;

  document.getElementById('btnToggleProgFilter').addEventListener('click', function() {
      document.getElementById('panelFilterProg').classList.toggle('hidden');
  });

  function updateStickyHeaderProg() {
      const thead = document.getElementById('theadProg');
      const scroller = document.getElementById('progScroller');
      if(thead && scroller) {
          scroller.style.setProperty('--prog_headH', (thead.offsetHeight - 1) + 'px');
      }
  }
  window.addEventListener('resize', updateStickyHeaderProg);

  // ==========================================
  // INISIALISASI
  // ==========================================
  window.addEventListener('DOMContentLoaded', async () => {
    const user = (window.getUser && window.getUser()) || null;
    let uKode = user?.kode ? String(user.kode).padStart(3,'0') : '000';
    if(uKode === '099') uKode = '000'; 

    const isPusat = (uKode === '000');
    const optKantor = document.getElementById('opt_kantor_prog');

    if (isPusat) {
        await loadCabangProg();
        optKantor.value = ""; 
    } else {
        optKantor.innerHTML = `<option value="${uKode}">CABANG ${uKode}</option>`;
        optKantor.value = uKode;
        optKantor.disabled = true; 
        optKantor.classList.add('bg-slate-50');
    }

    await handleCabangChange(true);

    try { 
        const r = await fetch(API_DATE); 
        const j = await r.json();
        if (j.data) document.getElementById('harian_date_prog').value = j.data.last_created;
    } catch {
        document.getElementById('harian_date_prog').value = new Date().toISOString().split('T')[0];
    }
    
    fetchProgKredit();
  });

  // ==========================================
  // FUNGSI DROPDOWN
  // ==========================================
  async function handleCabangChange(isInit = false) {
      const cabangVal = document.getElementById('opt_kantor_prog').value;
      const lblSub = document.getElementById('lbl_sub_prog');
      const optSub = document.getElementById('opt_sub_prog');

      if (cabangVal === "" || cabangVal === "000") {
          lblSub.innerText = "KORWIL";
          optSub.innerHTML = `
              <option value="">ALL KORWIL</option>
              <option value="SEMARANG">SEMARANG</option>
              <option value="SOLO">SOLO</option>
              <option value="BANYUMAS">BANYUMAS</option>
              <option value="PEKALONGAN">PEKALONGAN</option>
          `;
      } else {
          lblSub.innerText = "KANKAS";
          optSub.innerHTML = '<option value="">ALL KANKAS</option>';
          await loadKankasProg(cabangVal);
      }

      if (!isInit) triggerAutoRefresh();
  }

  function triggerAutoRefresh() {
      if(window.innerWidth < 1280) document.getElementById('panelFilterProg').classList.add('hidden');
      fetchProgKredit();
  }

  async function loadCabangProg() {
    const optKantor = document.getElementById('opt_kantor_prog');
    try {
        const res = await fetch(API_KODE, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({type:'kode_kantor'}) });
        const json = await res.json();
        let html = `<option value="">ALL | SEMUA CABANG</option>`;
        (json.data || []).filter(x => x.kode_kantor && x.kode_kantor !== '000')
            .sort((a,b) => String(a.kode_kantor).localeCompare(b.kode_kantor))
            .forEach(it => {
                html += `<option value="${String(it.kode_kantor).padStart(3,'0')}">${String(it.kode_kantor).padStart(3,'0')} - ${it.nama_kantor}</option>`;
            });
        optKantor.innerHTML = html;
    } catch(e){ optKantor.innerHTML = `<option value="">Error Load</option>`; }
  }

  async function loadKankasProg(kodeCabang) {
      const optSub = document.getElementById('opt_sub_prog');
      try {
          const payload = { type: 'kode_kankas', kode_kantor: kodeCabang };
          const r = await fetch(API_KODE, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload) });
          const j = await r.json();
          let h = '<option value="">ALL KANKAS</option>';
          if(j.data && Array.isArray(j.data)) {
              j.data.forEach(x => { h += `<option value="${x.kode_group1}">${x.deskripsi_group1 || x.kode_group1}</option>`; });
          }
          optSub.innerHTML = h;
      } catch(err) { }
  }

  document.getElementById('formFilterProg').addEventListener('submit', e => { 
      e.preventDefault(); 
      triggerAutoRefresh(); 
  });

  // ==========================================
  // FETCH & RENDER DATA UTAMA
  // ==========================================
  async function fetchProgKredit() {
      const loading = document.getElementById('loadingProg');
      const cabangVal = document.getElementById('opt_kantor_prog').value;
      const subVal = document.getElementById('opt_sub_prog').value;

      let reqKorwil = "";
      let reqKankas = "";

      if (cabangVal === "" || cabangVal === "000") {
          reqKorwil = subVal; 
      } else {
          reqKankas = subVal; 
      }

      const payload = { 
          type: "progress_kredit", 
          harian_date: document.getElementById('harian_date_prog').value,
          kode_kantor: cabangVal,
          korwil: reqKorwil,
          kode_kankas: reqKankas
      };

      loading.classList.remove('hidden');
      
      try {
          const res = await fetch(API_KREDIT, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          const json = await res.json();
          if(json.status !== 200) throw new Error(json.message);

          window.progDataRaw = json.data?.data || [];
          window.progGtRaw = json.data?.grand_total || null;
          const meta = json.data?.meta || {};

          document.getElementById('lbl_filter_aktif').innerHTML = `Filter: <span class="font-bold text-emerald-700">${meta.filter_aktif || 'ALL'}</span> | Per Tgl: <span class="font-bold text-slate-700">${meta.tanggal || payload.harian_date}</span>`;

          renderProgTotal(window.progGtRaw);
          renderProgTable(window.progDataRaw);
          setTimeout(updateStickyHeaderProg, 50);
      } catch(e) { 
          document.getElementById('bodyProg').innerHTML = `<tr><td colspan="5" class="text-center py-10 text-red-500 font-bold uppercase tracking-widest">${e.message || 'Error Load Data'}</td></tr>`;
          document.getElementById('totalProg').innerHTML = '';
      } finally { loading.classList.add('hidden'); }
  }

  function renderProgTotal(gt) {
      const tbodyTotal = document.getElementById('totalProg');
      tbodyTotal.innerHTML = '';
      if (!gt) return;
      
      // 🔥 FIX: Total Portofolio di-text mati (gak bisa di-klik)
      tbodyTotal.innerHTML = `
        <tr class="sticky-total">
            <td class="col-kategori text-left text-slate-800 uppercase tracking-widest">GRAND TOTAL</td>
            <td class="text-right font-black text-blue-800 text-sm">${fmt(gt.total_os)} <div class="text-[10px] text-slate-500 font-semibold">${fmt(gt.total_noa)} NOA</div></td>
            <td class="text-right font-black text-emerald-700 text-sm"><a href="#" onclick="openModalProg('ALL', 'PERFORMING'); return false;" class="hover:underline">${fmt(gt.os_performing)}</a> <div class="text-[10px] text-slate-500 font-semibold">${fmt(gt.noa_performing)} NOA</div></td>
            <td class="text-right font-black text-red-600 text-sm"><a href="#" onclick="openModalProg('ALL', 'NPL'); return false;" class="hover:underline">${fmt(gt.os_npl)}</a> <div class="text-[10px] text-slate-500 font-semibold">${fmt(gt.noa_npl)} NOA</div></td>
            <td class="text-center font-black text-slate-800 text-sm border-l border-slate-300 align-top pt-3">${fmt2(gt.persen_npl)}%</td>
        </tr>`;
  }

  function renderProgTable(rows) {
      const tbody = document.getElementById('bodyProg');
      tbody.innerHTML = '';
      if (rows.length === 0) {
          tbody.innerHTML = `<tr><td colspan="5" class="text-center py-12 text-slate-400 font-medium">Data tidak ditemukan.</td></tr>`;
          return;
      }
      
      let html = '';
      rows.forEach(r => {
          let k = 'ALL';
          if (r.kategori.includes('Lewat')) k = 'Lewat JT';
          else if (r.kategori.includes('Error')) k = 'Error';
          else if (r.kategori.includes('0% - 25%')) k = '0-25';
          else if (r.kategori.includes('26% - 50%')) k = '26-50';
          else if (r.kategori.includes('51% - 75%')) k = '51-75';
          else if (r.kategori.includes('76% - 95%')) k = '76-95';
          else if (r.kategori.includes('96% - 100%')) k = '96-100';

          // 🔥 FIX: Total Portofolio di-text mati (gak bisa di-klik)
          html += `
            <tr class="transition border-b h-[56px]">
                <td class="col-kategori font-bold text-slate-700 tracking-wide text-xs">${r.kategori}</td>
                <td class="text-right font-bold text-blue-700">${fmt(r.total_os)} <div class="text-[10px] text-slate-400 font-medium">${fmt(r.total_noa)} NOA</div></td>
                <td class="text-right font-semibold text-emerald-600"><a href="#" onclick="openModalProg('${k}', 'PERFORMING'); return false;" class="hover:underline">${fmt(r.os_performing)}</a> <div class="text-[10px] text-slate-400 font-medium">${fmt(r.noa_performing)} NOA</div></td>
                <td class="text-right font-semibold text-red-500"><a href="#" onclick="openModalProg('${k}', 'NPL'); return false;" class="hover:underline">${fmt(r.os_npl)}</a> <div class="text-[10px] text-slate-400 font-medium">${fmt(r.noa_npl)} NOA</div></td>
                <td class="text-center font-extrabold border-l border-slate-100 align-top pt-4 ${r.persen_npl > 5 ? 'text-red-600 bg-red-50/30' : 'text-emerald-600'}">${fmt2(r.persen_npl)}%</td>
            </tr>`;
      });
      tbody.innerHTML = html;
  }

  // ==========================================
  // LOGIC MODAL DETAIL, FILTER AO & PAGINATION
  // ==========================================
  async function loadAODetailProg(kodeCabang) {
      const optAO = document.getElementById('opt_ao_prog_modal');
      optAO.innerHTML = '<option value="">SEMUA AO</option>';
      if (!kodeCabang || kodeCabang === '000') return; 

      try {
          const payload = { type: 'kode_ao_kredit', kode_kantor: kodeCabang };
          const r = await fetch(API_KODE, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload) });
          const j = await r.json();
          let h = '<option value="">SEMUA AO</option>';
          if(j.data && Array.isArray(j.data)) {
              j.data.forEach(x => {
                  const rawName = x.nama_ao || x.kode_group2;
                  h += `<option value="${x.kode_group2}">${rawName}</option>`;
              });
          }
          optAO.innerHTML = h;
      } catch(err) {}
  }

  async function openModalProg(kategoriParam, statusParam) {
      const cabangVal = document.getElementById('opt_kantor_prog').value;
      const subVal = document.getElementById('opt_sub_prog').value;

      let reqKorwil = "";
      let reqKankas = "";

      if (cabangVal === "" || cabangVal === "000") {
          reqKorwil = subVal; 
      } else {
          reqKankas = subVal; 
      }

      currentProgDetailParams = { 
          type: "detail_progress_kredit", 
          harian_date: document.getElementById('harian_date_prog').value,
          kode_kantor: cabangVal,
          korwil: reqKorwil,
          kode_kankas: reqKankas,
          kategori: kategoriParam,
          status: statusParam,
          limit: 20
      };

      document.getElementById('mdlTitleProg').textContent = `Detail Debitur (${statusParam === 'ALL' ? 'Semua Status' : statusParam})`;
      document.getElementById('mdlSubTitleProg').textContent = `Range Progress: ${kategoriParam}`;
      document.getElementById('modalDetailProg').classList.remove('hidden');

      // 🔥 BARU: Fetch Dropdown AO dan kosongkan value lama
      document.getElementById('opt_ao_prog_modal').value = "";
      await loadAODetailProg(cabangVal);

      loadDetailProgPage(1);
  }

  function closeModalProg() {
      document.getElementById('modalDetailProg').classList.add('hidden');
  }

  async function loadDetailProgPage(page) {
      const loading = document.getElementById('loadingModalProg'); 
      const tbody = document.getElementById('bodyModalProg'); 
      const info = document.getElementById('pageInfoProg');
      
      loading.classList.remove('hidden'); 
      tbody.innerHTML = '';

      try {
          // Tangkap nilai Dropdown AO
          const aoVal = document.getElementById('opt_ao_prog_modal').value;

          const payload = { 
              ...currentProgDetailParams, 
              kode_ao: aoVal, // 🔥 BARU: Inject AO ke Payload
              page: page 
          };
          
          const res = await fetch(API_KREDIT, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          const json = await res.json();
          
          if(json.status !== 200) throw new Error(json.message || "Gagal memuat detail");
          
          const rows = json.data?.data || [];
          const meta = json.data?.pagination || { total_records:0, total_pages:1 };

          currentProgPage = page; 
          currentProgTotalPages = meta.total_pages;

          if(rows.length === 0) {
              tbody.innerHTML = `<tr><td colspan="12" class="py-20 text-center text-slate-500 italic">Tidak ada data debitur.</td></tr>`;
              info.innerText = `0 Data`;
          } else {
              let html = '';
              rows.forEach(r => {
                  const badgeStat = r.status_ket === 'PERFORMING' ? 'text-emerald-600 bg-emerald-50' : 'text-red-600 bg-red-50';
                  
                  // 🔥 FIX: WA No Link, Teks murni aja
                  const textWa = r.no_hp || '-';

                  html += `
                    <tr>
                        <td class="mod-td-rek hidden md:table-cell px-2 md:px-3 font-mono text-[9.5px] md:text-xs text-slate-500 border-r border-slate-100">${r.no_rekening}</td>
                        <td class="mod-td-nas px-2 md:px-4 font-bold text-slate-700 truncate border-r border-slate-100" title="${r.nama_nasabah}">${r.nama_nasabah}</td>
                        <td class="px-2 md:px-4 text-slate-500 truncate max-w-[200px] border-r border-slate-100" title="${r.alamat || '-'}">${r.alamat || '-'}</td>
                        <td class="px-2 md:px-3 text-center border-r border-slate-100 font-mono text-slate-500">${textWa}</td>
                        <td class="px-2 md:px-3 text-center font-mono text-slate-600 border-r border-slate-100">${r.kankas || '-'}</td>
                        <td class="px-2 md:px-4 text-blue-700 font-bold truncate border-r border-slate-100">${r.nama_ao || '-'}</td>
                        <td class="px-2 md:px-3 text-center text-slate-600 border-r border-slate-100">${r.tgl_realisasi}</td>
                        <td class="px-2 md:px-3 text-center font-bold text-orange-600 border-r border-slate-100">${r.tgl_jatuh_tempo}</td>
                        <td class="px-2 md:px-4 text-right font-medium text-slate-600 border-r border-slate-100">${fmt(r.jml_pinjaman)}</td>
                        <td class="px-2 md:px-4 text-right font-bold text-blue-800 border-r border-slate-100">${fmt(r.baki_debet)}</td>
                        <td class="px-2 md:px-3 text-center font-bold border-r border-slate-100 ${r.kolektibilitas === 'L' ? 'text-emerald-600' : 'text-red-600'}">${r.kolektibilitas}</td>
                        <td class="px-2 md:px-3 text-center font-extrabold ${badgeStat}">${fmt2(r.persen_jalan)}%</td>
                    </tr>`;
              });
              tbody.innerHTML = html;

              const start = ((page - 1) * 20) + 1;
              const end = Math.min(page * 20, meta.total_records);
              info.innerText = `Menampilkan ${start}-${end} dari ${fmt(meta.total_records)} Data`;
          }
          document.getElementById('btnPrevProg').disabled = page <= 1;
          document.getElementById('btnNextProg').disabled = page >= meta.total_pages;
      } catch(err){ 
          tbody.innerHTML = `<tr><td colspan="12" class="py-16 text-center text-red-500 font-bold">${err.message}</td></tr>`;
      } finally { loading.classList.add('hidden'); }
  }

  function changePageProg(step) {
      const n = currentProgPage + step; 
      if (n > 0 && n <= currentProgTotalPages) loadDetailProgPage(n); 
  }

  async function exportDetailProgExcel() {
      const btn = event.target.closest('button'); const txt = btn.innerHTML;
      btn.innerHTML = `Mengekspor...`; btn.disabled = true;

      try {
          const aoVal = document.getElementById('opt_ao_prog_modal').value;
          const payload = { ...currentProgDetailParams, kode_ao: aoVal, page: 1, limit: 15000 }; 
          const res = await fetch(API_KREDIT, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          const json = await res.json();
          
          if(json.status !== 200) throw new Error(json.message);
          const rows = json.data?.data || [];
          if(rows.length === 0) { alert("Tidak ada data untuk diexport"); return; }

          let csv = "No Rekening\tNama Nasabah\tAlamat\tNo HP\tKankas\tNama AO\tTgl Realisasi\tTgl Jatuh Tempo\tPlafond\tOS (Baki Debet)\tKolektibilitas\tStatus\t% Jalan\n";
          rows.forEach(r => {
              csv += `'${r.no_rekening}\t${r.nama_nasabah}\t${r.alamat||''}\t'${r.no_hp||''}\t${r.kankas||''}\t${r.nama_ao||''}\t${r.tgl_realisasi}\t${r.tgl_jatuh_tempo}\t${Math.round(r.jml_pinjaman)}\t${Math.round(r.baki_debet)}\t${r.kolektibilitas}\t${r.status_ket}\t${r.persen_jalan}\n`;
          });

          const blob = new Blob([csv], { type: 'application/vnd.ms-excel' });
          const a = document.createElement('a');
          a.href = window.URL.createObjectURL(blob);
          a.download = `Detail_Prog_${currentProgDetailParams.kategori}_${currentProgDetailParams.harian_date}.xls`;
          document.body.appendChild(a); a.click(); document.body.removeChild(a);
      } catch(e) { alert("Gagal export: " + e.message); } 
      finally { btn.innerHTML = txt; btn.disabled = false; }
  }

  function exportProgExcel() {
      const rows = window.progDataRaw || [];
      const gt = window.progGtRaw || null;
      if(rows.length === 0) return alert("Data Kosong!");

      let csv = "PROGRESS JALAN (%)\tTOTAL PORTOFOLIO (OS)\tTOTAL PORTO (NOA)\tLANCAR & DPK (OS)\tLANCAR & DPK (NOA)\tNPL (OS)\tNPL (NOA)\t% NPL KONTRIBUSI\n";
      if(gt) csv += `GRAND TOTAL\t${gt.total_os}\t${gt.total_noa}\t${gt.os_performing}\t${gt.noa_performing}\t${gt.os_npl}\t${gt.noa_npl}\t${gt.persen_npl}\n`;
      rows.forEach(r => { csv += `${r.kategori}\t${r.total_os}\t${r.total_noa}\t${r.os_performing}\t${r.noa_performing}\t${r.os_npl}\t${r.noa_npl}\t${r.persen_npl}\n`; });
      
      const blob = new Blob([csv], { type: 'application/vnd.ms-excel' });
      const a = document.createElement('a');
      a.href = window.URL.createObjectURL(blob);
      a.download = `Progress_Kemajuan_Kredit_${document.getElementById('harian_date_prog').value}.xls`;
      a.click();
  }
</script>