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
  
  input[type="date"] { position: relative; cursor: pointer; }
  input[type="date"]::-webkit-inner-spin-button,
  input[type="date"]::-webkit-calendar-picker-indicator {
      position: absolute; top: 0; left: 0; right: 0; bottom: 0;
      width: 100%; height: 100%; opacity: 0; cursor: pointer;
  }

  /* Scroller Tabel Utama */
  #progScroller { 
      overflow: auto; height: 100%; border-radius: 8px; 
      border: 1px solid #e2e8f0; background: white; position: relative;
  }
  
  table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 12px; }
  th, td { white-space: nowrap; padding: 8px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
  tr:hover td { background-color: #f8fafc; }

  /* --- HEADER TABEL UTAMA --- */
  thead th { 
      position: sticky; z-index: 60; 
      background: #e2e8f0; color: #1e293b; font-weight: 800; 
      text-transform: uppercase; 
      font-size: 11px; letter-spacing: 0.05em; text-align: center;
      /* Menggunakan box-shadow agar border tidak bocor saat di-scroll */
      box-shadow: inset 0 -2px 0 #cbd5e1; 
  }
  
  /* Multilevel Header Fix - Patok tinggi baris pertama 38px */
  thead tr:nth-child(1) th { top: 0; height: 38px; }
  thead tr:nth-child(2) th { top: 38px; } 

  .col-kategori { position: sticky; left: 0; z-index: 45; background: white; border-right: 1px solid #e2e8f0; min-width: 60px; font-weight: bold; text-align: center;}
  thead th.col-kategori { z-index: 70; background: #e2e8f0; box-shadow: inset -1px -2px 0 #cbd5e1; }

  /* Row Grand Total - Sesuaikan top-nya dengan total tinggi 2 header di atas (sekitar 82px) */
  .sticky-total td { 
      position: sticky; top: 82px; z-index: 55; 
      background: #f4f7fb; font-weight: 800; border-bottom: 2px solid #bfdbfe; 
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); text-align: right;
  }
  .sticky-total td.col-kategori { z-index: 65; background: #f4f7fb; border-right: 1px solid #bfdbfe; text-align: center; }

  /* Animasi Modal */
  @keyframes scaleUp { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
  .animate-scale-up { animation: scaleUp 0.2s ease-out forwards; }

  /* --- TABEL DETAIL MODAL --- */
  #tableDetailProg th, #tableDetailProg td { background-color: #fff; } 
  #tableDetailProg thead th { position: sticky; top: 0; z-index: 40; background-color: #f1f5f9; box-shadow: inset 0 -2px 0 #cbd5e1; height: 38px;}
  .mod-freeze-rek { position: sticky; left: 0; z-index: 42 !important; background-color: #f1f5f9 !important; box-shadow: inset -1px 0 0 #cbd5e1; min-width: 100px; max-width: 100px;}
  .mod-freeze-nas { position: sticky; left: 100px; z-index: 41 !important; background-color: #f1f5f9 !important; box-shadow: inset -1px 0 0 #cbd5e1; min-width: 200px; max-width: 250px;}
  .mod-td-rek { position: sticky; left: 0; z-index: 32 !important; background-color: #fff !important; box-shadow: inset -1px 0 0 #f1f5f9;}
  .mod-td-nas { position: sticky; left: 100px; z-index: 31 !important; background-color: #fff !important; box-shadow: inset -1px 0 0 #f1f5f9; }
  tbody.mod-body tr:hover td { background-color: #f8fafc !important; }
  tbody.mod-body tr:hover td.mod-td-rek, tbody.mod-body tr:hover td.mod-td-nas { filter: brightness(0.97); }

  @media (max-width: 767px) {
      .col-kategori { left: 0 !important; z-index: 45 !important; min-width: 60px; }
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
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                </span> 
                <span>Otp Bucket FE (31 - 90)</span>
            </h1>
             <p class="text-[9px] md:text-xs text-rose-600 font-bold italic ml-8 md:ml-[42px] leading-tight">
                 *Berdasarkan Tanggal Jatuh Tempo
              </p>
        </div>

        <button id="btnToggleProgFilter" class="xl:hidden flex items-center gap-1.5 px-3 py-1.5 border border-gray-300 rounded-lg bg-white text-sm font-semibold text-slate-700 shadow-sm transition">
            Filter
        </button>
    </div>

    <div id="panelFilterProg" class="hidden xl:block bg-white border border-gray-200 rounded-xl p-3 shadow-sm w-full xl:w-auto transition-all">
        <form id="formFilterProg" class="flex flex-col md:flex-row items-end gap-2 md:gap-3 w-full">
            <div class="flex flex-col w-full md:w-[130px]">
                <label class="text-[9px] font-extrabold text-slate-500 uppercase ml-1 mb-1 tracking-wider">CLOSING (M-1)</label>
                <input type="date" id="closing_date_otp" class="inp shadow-sm text-slate-700" required>
            </div>

            <div class="flex flex-col w-full md:w-[130px]">
                <label class="text-[9px] font-extrabold text-slate-500 uppercase ml-1 mb-1 tracking-wider">HARIAN (ACTUAL)</label>
                <input type="date" id="harian_date_otp" class="inp shadow-sm text-slate-700" required>
            </div>

            <div class="flex flex-col w-full md:w-[120px]">
                <label class="text-[9px] font-extrabold text-slate-500 uppercase ml-1 mb-1 tracking-wider">TYPE BUCKET</label>
                <select id="type_bucket_otp" class="inp text-slate-700 shadow-sm" onchange="triggerAutoRefresh()">
                    <option value="fe_all">GABUNGAN</option>
                    <option value="31-60">31 - 60</option>
                    <option value="61-90">61 - 90</option>
                </select>
            </div>

            <div class="flex flex-col w-full md:w-[180px]" id="wrap-cabang">
                <label class="text-[9px] font-extrabold text-slate-500 uppercase ml-1 mb-1 tracking-wider">CABANG</label>
                <select id="opt_kantor_otp" class="inp text-slate-700 shadow-sm truncate" onchange="handleCabangChange()">
                    <option value="">ALL | SEMUA CABANG</option>
                </select>
            </div>

            <div class="flex flex-col w-full md:w-[150px]">
                <label id="lbl_sub_otp" class="text-[9px] font-extrabold text-slate-500 uppercase ml-1 mb-1 tracking-wider">KORWIL</label>
                <select id="opt_sub_otp" class="inp text-slate-700 shadow-sm truncate" onchange="triggerAutoRefresh()">
                    <option value="">ALL KORWIL</option>
                    <option value="SEMARANG">SEMARANG</option>
                    <option value="SOLO">SOLO</option>
                    <option value="BANYUMAS">BANYUMAS</option>
                    <option value="PEKALONGAN">PEKALONGAN</option>
                </select>
            </div>
            
            <div class="flex gap-2 shrink-0 mt-2 md:mt-0 w-full md:w-auto">
                <button type="submit" class="flex-1 md:flex-none bg-emerald-600 hover:bg-emerald-700 text-white h-9 px-4 rounded-lg font-bold text-sm shadow-sm flex items-center justify-center gap-2 transition">
                    <span class="hidden md:inline">CARI</span>
                </button>
                <button type="button" onclick="exportProgExcel()" class="bg-indigo-600 hover:bg-indigo-700 text-white h-9 px-3 md:w-11 rounded-lg shadow-sm flex items-center justify-center transition" title="Export Rekap">
                    <span class="md:hidden ml-1 font-bold text-xs">EXCEL</span>
                </button>
            </div>
        </form>
    </div>
  </div>

  <div class="flex-1 min-h-0 relative flex flex-col bg-white rounded-xl shadow-sm border border-slate-200">
    <div id="loadingProg" class="hidden absolute inset-0 bg-white/80 z-[100] flex flex-col items-center justify-center text-emerald-600 font-bold text-sm backdrop-blur-sm rounded-xl">
        <div class="animate-spin h-8 w-8 border-4 border-emerald-200 border-t-emerald-600 rounded-full mb-2"></div>
        Mengkalkulasi Roll Rate...
    </div>

    <div class="table-wrapper custom-scrollbar" id="progScroller">
      <table id="tabelProgKredit">
        <thead id="theadProg">
          <tr>
            <th class="col-kategori border-r border-slate-300" rowspan="2" style="vertical-align: middle;">TGL</th>
            <th class="border-r border-slate-300 text-blue-800" rowspan="2" style="vertical-align: middle;">TARGET (M-1)</th>
            <th class="border-b border-slate-300" colspan="5" style="vertical-align: middle;">STATUS MIGRASI (M)</th>
          </tr>
          <tr>
              <th class="border-r border-slate-300 text-emerald-600 py-2 leading-tight">BTC<br><span class="text-[9px] font-medium">(LANCAR)</span></th>
              <th class="border-r border-slate-300 text-teal-600 py-2 leading-tight">BACKFLOW<br><span class="text-[9px] font-medium">(MEMBAIK)</span></th>
              <th class="border-r border-slate-300 text-orange-500 py-2 leading-tight">STAY<br><span class="text-[9px] font-medium">(TETAP)</span></th>
              <th class="text-red-600 py-2 leading-tight">MIGRASI<br><span class="text-[9px] font-medium">(MEMBURUK)</span></th>
              <th class="border-r border-slate-300 text-slate-500 py-2 leading-tight">RUN OFF<br><span class="text-[9px] font-medium">(LUNAS)</span></th>
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
                <select id="opt_kankas_prog_modal" class="inp px-1 md:px-2 h-[32px] md:h-[36px] w-[120px] md:w-[180px] text-[10px] md:text-xs font-bold text-slate-700 bg-slate-50 border-slate-200 cursor-pointer" onchange="loadDetailProgPage(1)">
                    <option value="">SEMUA KANKAS</option>
                </select>

                <button onclick="exportDetailProgExcel()" class="btn-icon bg-indigo-600 hover:bg-indigo-700 text-white px-2.5 md:px-4 h-[32px] md:h-[36px] rounded-lg shadow-sm shrink-0 transition flex items-center gap-1">
                    <span class="font-bold text-xs uppercase tracking-wider">Export Excel</span>
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
        <thead class="text-[10px] md:text-xs text-slate-600 uppercase tracking-wider select-none">
            <tr>
                <th class="mod-freeze-rek px-2 md:px-3 hidden md:table-cell py-3" style="vertical-align: middle;">REKENING</th>
                <th class="mod-freeze-nas px-2 md:px-4 py-3" style="vertical-align: middle;">NAMA NASABAH</th>
                <th class="px-2 md:px-3 text-center w-[120px]" style="vertical-align: middle;">KANKAS</th>
                <th class="px-2 md:px-3 text-center w-[80px] text-orange-600" style="vertical-align: middle;">TGL JT</th>
                <th class="px-2 md:px-4 text-right w-[130px] text-blue-800" style="vertical-align: middle;">OS (CURR)</th>
                <th class="px-2 md:px-4 text-right w-[110px] text-emerald-600" style="vertical-align: middle;">TABUNGAN</th>
                <th class="px-2 md:px-4 text-right w-[110px] text-red-500" style="vertical-align: middle;">TOT. TUNGGAKAN</th>
                <th class="px-2 md:px-3 text-center w-[80px]" style="vertical-align: middle;">DPD (M)</th>
                <th class="px-2 md:px-3 text-center w-[130px] text-emerald-700" style="vertical-align: middle;">STATUS MIGRASI</th>
            </tr>
        </thead>
        <tbody id="bodyModalProg" class="mod-body text-[9.5px] md:text-xs"></tbody>
      </table>
    </div>

    <div class="px-3 py-2.5 md:px-5 md:py-4 border-t bg-white flex justify-between items-center shrink-0">
      <span class="text-[9px] md:text-sm font-bold text-slate-600 bg-slate-100 px-2 md:px-3 py-1 rounded-md md:rounded-lg" id="pageInfoProg">0 Data</span>
      <div class="flex gap-1 md:gap-2">
          <button id="btnPrevProg" onclick="changePageProg(-1)" class="px-2.5 md:px-4 py-1.5 md:py-2 bg-white border border-slate-300 rounded-md md:rounded-lg text-[9px] md:text-sm font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-50 shadow-sm">« Prev</button>
          <button id="btnNextProg" onclick="changePageProg(1)" class="px-2.5 md:px-4 py-1.5 md:py-2 bg-white border border-slate-300 rounded-md md:rounded-lg text-[9px] md:text-sm font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-50 shadow-sm">Next »</button>
      </div>
    </div>
  </div>
</div>

<script>
  const API_RR     = './api/rr/'; 
  const API_KODE   = './api/kode/';

  const nf = new Intl.NumberFormat('id-ID');
  const fmt = n => nf.format(Number(n||0));
  const fmt2 = x => (x == null || x === '' ? '0.00' : Number(x).toFixed(2));

  window.progDataRaw = [];
  window.progGtRaw = null;

  let currentProgDetailParams = {};
  let currentProgPage = 1;
  let currentProgTotalPages = 1;

  document.getElementById('btnToggleProgFilter').addEventListener('click', function() {
      document.getElementById('panelFilterProg').classList.toggle('hidden');
  });

  // ==========================================
  // INISIALISASI
  // ==========================================
  window.addEventListener('DOMContentLoaded', async () => {
    const today = new Date();
    document.getElementById('harian_date_otp').value = today.toISOString().split('T')[0];
    
    const lastMonth = new Date(today.getFullYear(), today.getMonth(), 0);
    const pad = (n) => n < 10 ? '0'+n : n;
    document.getElementById('closing_date_otp').value = `${lastMonth.getFullYear()}-${pad(lastMonth.getMonth()+1)}-${pad(lastMonth.getDate())}`;

    const user = (window.getUser && window.getUser()) || null;
    let uKode = user?.kode ? String(user.kode).padStart(3,'0') : '000';
    if(uKode === '099') uKode = '000'; 

    const optKantor = document.getElementById('opt_kantor_otp');

    if (uKode === '000') {
        await loadCabangProg();
        optKantor.value = ""; 
    } else {
        optKantor.innerHTML = `<option value="${uKode}">CABANG ${uKode}</option>`;
        optKantor.value = uKode;
        optKantor.disabled = true; 
        optKantor.classList.add('bg-slate-50');
    }

    await handleCabangChange(true);
    fetchProgKredit();
  });

  async function handleCabangChange(isInit = false) {
      const cabangVal = document.getElementById('opt_kantor_otp').value;
      const lblSub = document.getElementById('lbl_sub_otp');
      const optSub = document.getElementById('opt_sub_otp');

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
    const optKantor = document.getElementById('opt_kantor_otp');
    try {
        const res = await fetch(API_KODE, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({type:'kode_kantor'}) });
        const json = await res.json();
        let html = `<option value="">ALL | SEMUA CABANG</option>`;
        (json.data || []).filter(x => x.kode_kantor && x.kode_kantor !== '000').forEach(it => {
            html += `<option value="${String(it.kode_kantor).padStart(3,'0')}">${String(it.kode_kantor).padStart(3,'0')} - ${it.nama_kantor}</option>`;
        });
        optKantor.innerHTML = html;
    } catch(e){}
  }

  async function loadKankasProg(kodeCabang) {
      const optSub = document.getElementById('opt_sub_otp');
      try {
          const payload = { type: 'kode_kankas', kode_kantor: kodeCabang };
          const r = await fetch(API_KODE, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload) });
          const j = await r.json();
          let h = '<option value="">ALL KANKAS</option>';
          if(j.data && Array.isArray(j.data)) j.data.forEach(x => { h += `<option value="${x.kode_group1}">${x.deskripsi_group1 || x.kode_group1}</option>`; });
          optSub.innerHTML = h;
      } catch(err) {}
  }

  document.getElementById('formFilterProg').addEventListener('submit', e => { 
      e.preventDefault(); 
      triggerAutoRefresh(); 
  });

  // ==========================================
  // FETCH REKAP DATA
  // ==========================================
  async function fetchProgKredit() {
      const loading = document.getElementById('loadingProg');
      const cabangVal = document.getElementById('opt_kantor_otp').value;
      const subVal = document.getElementById('opt_sub_otp').value;
      
      let reqKorwil = ""; let reqKankas = "";
      if (cabangVal === "" || cabangVal === "000") reqKorwil = subVal; 
      else reqKankas = subVal; 

      const payload = { 
          type: "otp_fe", 
          closing_date: document.getElementById('closing_date_otp').value,
          harian_date: document.getElementById('harian_date_otp').value,
          type_bucket: document.getElementById('type_bucket_otp').value,
          kode_kantor: cabangVal,
          korwil: reqKorwil,
          kode_kankas: reqKankas
      };

      loading.classList.remove('hidden');
      
      try {
          const res = await fetch(API_RR, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          const json = await res.json();
          if(json.status !== 200) throw new Error(json.message);

          window.progDataRaw = json.data?.data || [];
          window.progGtRaw = json.data?.grand_total || null;

          renderProgTotal(window.progGtRaw);
          renderProgTable(window.progDataRaw);
      } catch(e) { 
          document.getElementById('bodyProg').innerHTML = `<tr><td colspan="7" class="text-center py-10 text-red-500 font-bold uppercase tracking-widest">${e.message || 'Error Load Data'}</td></tr>`;
          document.getElementById('totalProg').innerHTML = '';
      } finally { loading.classList.add('hidden'); }
  }

  function renderProgTotal(gt) {
      const tbodyTotal = document.getElementById('totalProg');
      tbodyTotal.innerHTML = '';
      if (!gt) return;
      
      tbodyTotal.innerHTML = `
        <tr class="sticky-total">
            <td class="col-kategori text-center text-slate-800 uppercase tracking-widest text-[11px] leading-tight pt-3">TOTAL</td>
            <td class="text-right font-black text-blue-800 border-l border-slate-300">
                <a href="#" onclick="openModalProg('ALL', 'ALL'); return false;" class="hover:underline">${fmt(gt.m1_os)}</a>
                <div class="text-[10px] text-slate-500 font-semibold">${fmt(gt.m1_noa)} NOA</div>
            </td>
            

            
            <td class="border-l border-slate-300 border-b-2 border-b-emerald-500">
                <a href="#" onclick="openModalProg('ALL', 'BTC'); return false;" class="text-emerald-600 hover:underline hover:text-emerald-800">${fmt(gt.btc_os)}</a>
                <div class="text-[10px] text-emerald-600/70 font-semibold">${fmt(gt.btc_noa)} NOA | ${fmt2(gt.btc_pct)}%</div>
            </td>

            <td class="border-l border-slate-300 border-b-2 border-b-teal-400">
                <a href="#" onclick="openModalProg('ALL', 'BACKFLOW'); return false;" class="text-teal-600 hover:underline hover:text-teal-800">${fmt(gt.backflow_os)}</a>
                <div class="text-[10px] text-teal-600/70 font-semibold">${fmt(gt.backflow_noa)} NOA | ${fmt2(gt.backflow_pct)}%</div>
            </td>

            <td class="border-l border-slate-300 border-b-2 border-b-orange-400">
                <a href="#" onclick="openModalProg('ALL', 'STAY'); return false;" class="text-orange-500 hover:underline hover:text-orange-700">${fmt(gt.stay_os)}</a>
                <div class="text-[10px] text-orange-500/70 font-semibold">${fmt(gt.stay_noa)} NOA | ${fmt2(gt.stay_pct)}%</div>
            </td>

            <td class="border-l border-slate-300 border-b-2 border-b-red-500">
                <a href="#" onclick="openModalProg('ALL', 'MIGRASI'); return false;" class="text-red-600 hover:underline hover:text-red-800">${fmt(gt.migrasi_os)}</a>
                <div class="text-[10px] text-red-600/70 font-semibold">${fmt(gt.migrasi_noa)} NOA | ${fmt2(gt.migrasi_pct)}%</div>
            </td>

            <td class="border-l border-slate-300 border-b-2 border-b-slate-400">
                <a href="#" onclick="openModalProg('ALL', 'RUNOFF'); return false;" class="text-slate-600 hover:underline hover:text-slate-800">${fmt(gt.runoff_os)}</a>
                <div class="text-[10px] text-slate-400 font-semibold">${fmt(gt.runoff_noa)} NOA | ${fmt2(gt.runoff_pct)}%</div>
            </td>
        </tr>`;
  }

  function renderProgTable(rows) {
      const tbody = document.getElementById('bodyProg');
      tbody.innerHTML = '';
      if (rows.length === 0) return tbody.innerHTML = `<tr><td colspan="7" class="text-center py-12 text-slate-400 font-medium">Data tidak ditemukan.</td></tr>`;
      
      let html = '';
      rows.forEach(r => {
          if(r.m1_os <= 0) return; // Skip yang targetnya 0
          html += `
            <tr class="transition border-b h-[50px]">
                <td class="col-kategori font-bold text-slate-700 text-sm">${r.tgl}</td>
                
                <td class="text-right font-bold text-blue-700 border-l border-slate-100">
                    <a href="#" onclick="openModalProg(${r.tgl}, 'ALL'); return false;" class="hover:underline">${fmt(r.m1_os)}</a>
                    <div class="text-[10px] text-slate-400 font-medium">${fmt(r.m1_noa)} NOA</div>
                </td>

                <td class="text-right font-semibold text-emerald-600 border-l border-slate-100 bg-emerald-50/20">
                    <a href="#" onclick="openModalProg(${r.tgl}, 'BTC'); return false;" class="hover:underline">${fmt(r.btc_os)}</a>
                    <div class="text-[9px] text-emerald-600/60 font-medium">${fmt(r.btc_noa)} NOA | ${fmt2(r.btc_pct)}%</div>
                </td>

                <td class="text-right font-semibold text-teal-600 border-l border-slate-100 bg-teal-50/20">
                    <a href="#" onclick="openModalProg(${r.tgl}, 'BACKFLOW'); return false;" class="hover:underline">${fmt(r.backflow_os)}</a>
                    <div class="text-[9px] text-teal-600/60 font-medium">${fmt(r.backflow_noa)} NOA | ${fmt2(r.backflow_pct)}%</div>
                </td>

                <td class="text-right font-semibold text-orange-500 border-l border-slate-100 bg-orange-50/20">
                    <a href="#" onclick="openModalProg(${r.tgl}, 'STAY'); return false;" class="hover:underline">${fmt(r.stay_os)}</a>
                    <div class="text-[9px] text-orange-500/60 font-medium">${fmt(r.stay_noa)} NOA | ${fmt2(r.stay_pct)}%</div>
                </td>

                <td class="text-right font-semibold text-red-500 border-l border-slate-100 bg-red-50/20">
                    <a href="#" onclick="openModalProg(${r.tgl}, 'MIGRASI'); return false;" class="hover:underline">${fmt(r.migrasi_os)}</a>
                    <div class="text-[9px] text-red-500/60 font-medium">${fmt(r.migrasi_noa)} NOA | ${fmt2(r.migrasi_pct)}%</div>
                </td>

                <td class="text-right font-semibold text-slate-600 border-l border-slate-100 bg-slate-50/50">
                    <a href="#" onclick="openModalProg(${r.tgl}, 'RUNOFF'); return false;" class="hover:underline">${fmt(r.runoff_os)}</a>
                    <div class="text-[9px] text-slate-400 font-medium">${fmt(r.runoff_noa)} NOA | ${fmt2(r.runoff_pct)}%</div>
                </td>
            </tr>`;
      });
      tbody.innerHTML = html;
  }

  // ==========================================
  // MODAL DETAIL & EXPORT
  // ==========================================
  async function loadKankasDetailProg(kodeCabang) {
      const optKankas = document.getElementById('opt_kankas_prog_modal');
      optKankas.innerHTML = '<option value="">SEMUA KANKAS</option>';
      if (!kodeCabang || kodeCabang === '000') return; 

      try {
          const payload = { type: 'kode_kankas', kode_kantor: kodeCabang };
          const r = await fetch(API_KODE, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload) });
          const j = await r.json();
          let h = '<option value="">SEMUA KANKAS</option>';
          if(j.data) j.data.forEach(x => { h += `<option value="${x.kode_group1}">${x.deskripsi_group1 || x.kode_group1}</option>`; });
          optKankas.innerHTML = h;
          
          // Auto select if main filter has Kankas selected
          const mainKankas = document.getElementById('opt_sub_otp').value;
          if(mainKankas && document.getElementById('lbl_sub_otp').innerText === "KANKAS") {
              optKankas.value = mainKankas;
          }
      } catch(err) {}
  }

  async function openModalProg(tglParam, statusParam) {
      const cabangVal = document.getElementById('opt_kantor_otp').value;
      const subVal = document.getElementById('opt_sub_otp').value;
      const typeB = document.getElementById('type_bucket_otp').value;

      let reqKorwil = ""; let reqKankas = "";
      if (cabangVal === "" || cabangVal === "000") reqKorwil = subVal; 
      else reqKankas = subVal; 

      currentProgDetailParams = { 
          type: "detail_otp_fe", 
          closing_date: document.getElementById('closing_date_otp').value,
          harian_date: document.getElementById('harian_date_otp').value,
          type_bucket: typeB,
          kode_kantor: cabangVal,
          korwil: reqKorwil,
          kode_kankas: reqKankas,
          status: statusParam,
          limit: 20
      };

      if(tglParam !== 'ALL') currentProgDetailParams.tgl_tagih = tglParam;

      document.getElementById('mdlTitleProg').textContent = `Detail Debitur Status: ${statusParam}`;
      document.getElementById('mdlSubTitleProg').textContent = `Tanggal JT: ${tglParam === 'ALL' ? 'Semua Tgl' : tglParam} | Bucket: ${typeB.toUpperCase()}`;
      document.getElementById('modalDetailProg').classList.remove('hidden');

      document.getElementById('opt_kankas_prog_modal').value = "";
      await loadKankasDetailProg(cabangVal);
      loadDetailProgPage(1);
  }

  function closeModalProg() { document.getElementById('modalDetailProg').classList.add('hidden'); }

  async function loadDetailProgPage(page) {
      const loading = document.getElementById('loadingModalProg'); 
      const tbody = document.getElementById('bodyModalProg'); 
      const info = document.getElementById('pageInfoProg');
      
      loading.classList.remove('hidden'); 
      tbody.innerHTML = '';

      try {
          const kankasVal = document.getElementById('opt_kankas_prog_modal').value;
          const payload = { ...currentProgDetailParams, kode_kankas: kankasVal || currentProgDetailParams.kode_kankas, page: page };
          
          const res = await fetch(API_RR, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          const json = await res.json();
          
          if(json.status !== 200) throw new Error(json.message || "Gagal memuat detail");
          
          const rows = json.data?.data || [];
          const meta = json.data?.pagination || { total_records:0, total_pages:1 };

          currentProgPage = page; 
          currentProgTotalPages = meta.total_pages;

          if(rows.length === 0) {
              tbody.innerHTML = `<tr><td colspan="9" class="py-20 text-center text-slate-500 italic">Tidak ada data debitur.</td></tr>`;
              info.innerText = `0 Data`;
          } else {
              let html = '';
              rows.forEach(r => {
                  let badge = 'bg-slate-100 text-slate-600';
                  if(r.status_ket.includes('RUNOFF')) badge = 'bg-slate-200 text-slate-700';
                  else if(r.status_ket.includes('BTC')) badge = 'bg-emerald-100 text-emerald-700';
                  else if(r.status_ket.includes('BACKFLOW')) badge = 'bg-teal-100 text-teal-700';
                  else if(r.status_ket.includes('STAY')) badge = 'bg-orange-100 text-orange-600';
                  else if(r.status_ket.includes('MIGRASI')) badge = 'bg-red-100 text-red-600';

                  // Styling Tabungan & Totung
                  let txtTabungan = fmt(r.tabungan);
                  if (r.status_tabungan === 'Aman') txtTabungan = `<span class="text-emerald-600 font-bold">${txtTabungan}</span>`;
                  else if (r.tabungan > 0) txtTabungan = `<span class="text-orange-500 font-medium">${txtTabungan}</span>`;

                  html += `
                    <tr>
                        <td class="mod-td-rek hidden md:table-cell px-2 md:px-3 font-mono text-slate-500 border-r border-slate-100">${r.no_rekening}</td>
                        <td class="mod-td-nas px-2 md:px-4 font-bold text-slate-700 truncate border-r border-slate-100">${r.nama_nasabah}</td>
                        <td class="px-2 md:px-3 text-center font-mono text-slate-600 border-r border-slate-100">${r.kankas || '-'}</td>
                        <td class="px-2 md:px-3 text-center font-bold text-orange-600 border-r border-slate-100">${r.tgl_jatuh_tempo}</td>
                        <td class="px-2 md:px-4 text-right font-bold text-blue-800 border-r border-slate-100">${fmt(r.os_curr)}</td>
                        <td class="px-2 md:px-4 text-right font-medium border-r border-slate-100">${txtTabungan}</td>
                        <td class="px-2 md:px-4 text-right font-bold text-red-500 border-r border-slate-100">${fmt(r.totung)}</td>
                        <td class="px-2 md:px-3 text-center font-bold border-r border-slate-100">${r.dpd_curr} Hr</td>
                        <td class="px-2 md:px-3 text-center font-extrabold border-r border-slate-100"><span class="px-2 py-0.5 rounded-md ${badge}">${r.status_ket}</span></td>
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
          tbody.innerHTML = `<tr><td colspan="9" class="py-16 text-center text-red-500 font-bold">${err.message}</td></tr>`;
      } finally { loading.classList.add('hidden'); }
  }

  function changePageProg(step) {
      const n = currentProgPage + step; 
      if (n > 0 && n <= currentProgTotalPages) loadDetailProgPage(n); 
  }

  // ==========================================
  // EXPORT EXCEL
  // ==========================================
  async function exportDetailProgExcel() {
      const btn = event.target.closest('button'); const txt = btn.innerHTML;
      btn.innerHTML = `<span class="font-bold text-xs uppercase">Loading...</span>`; btn.disabled = true;

      try {
          const kankasVal = document.getElementById('opt_kankas_prog_modal').value;
          const payload = { ...currentProgDetailParams, kode_kankas: kankasVal || currentProgDetailParams.kode_kankas, page: 1, limit: 20000 }; 
          
          const res = await fetch(API_RR, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          const json = await res.json();
          if(json.status !== 200) throw new Error(json.message);
          
          const rows = json.data?.data || [];
          if(rows.length === 0) { alert("Tidak ada data untuk diexport"); return; }

          let tableHtml = `<table border="1">
            <tr>
              <th>No Rekening</th><th>Nama Nasabah</th><th>Alamat</th><th>No HP</th>
              <th>Kankas</th><th>Tgl Realisasi</th><th>Tgl Jatuh Tempo</th>
              <th>OS (Baki Debet Curr)</th><th>Tabungan</th><th>Tot. Tunggakan</th>
              <th>DPD (Curr)</th><th>Status Migrasi</th>
            </tr>`;
          
          rows.forEach(r => {
              tableHtml += `<tr>
                <td style="mso-number-format:'\\@';">${r.no_rekening}</td>
                <td>${r.nama_nasabah}</td>
                <td>${r.alamat || ''}</td>
                <td style="mso-number-format:'\\@';">${r.no_hp || ''}</td>
                <td>${r.kankas || ''}</td>
                <td>${r.tgl_realisasi || ''}</td>
                <td>${r.tgl_jatuh_tempo || ''}</td>
                <td>${r.os_curr}</td>
                <td>${r.tabungan}</td>
                <td>${r.totung}</td>
                <td>${r.dpd_curr}</td>
                <td>${r.status_ket}</td>
              </tr>`;
          });
          tableHtml += `</table>`;

          const blob = new Blob([tableHtml], { type: 'application/vnd.ms-excel' });
          const a = document.createElement('a');
          a.href = window.URL.createObjectURL(blob);
          const fTgl = currentProgDetailParams.tgl_tagih ? `_TGL${currentProgDetailParams.tgl_tagih}` : '';
          a.download = `Detail_OTP_${currentProgDetailParams.status}${fTgl}.xls`;
          document.body.appendChild(a); a.click(); document.body.removeChild(a);
      } catch(e) { alert("Gagal export: " + e.message); } 
      finally { btn.innerHTML = txt; btn.disabled = false; }
  }

  function exportProgExcel() {
      const rows = window.progDataRaw || [];
      const gt = window.progGtRaw || null;
      if(rows.length === 0 || !gt) return alert("Data Kosong!");

      let tableHtml = `<table border="1">
        <tr>
            <th rowspan="2">TGL JATUH TEMPO</th>
            <th rowspan="2">TARGET (M-1) OS</th>
            <th rowspan="2">TARGET (M-1) NOA</th>
            <th colspan="10">STATUS MIGRASI (M)</th>
        </tr>
        <tr>
            <th>BTC (OS)</th><th>BTC (NOA)</th>
            <th>BACKFLOW (OS)</th><th>BACKFLOW (NOA)</th>
            <th>STAY (OS)</th><th>STAY (NOA)</th>
            <th>MIGRASI (OS)</th><th>MIGRASI (NOA)</th>
            <th>RUN OFF (OS)</th><th>RUN OFF (NOA)</th>
        </tr>`;

      // Grand Total Row
      tableHtml += `<tr>
        <td><b>TOTAL</b></td>
        <td><b>${gt.m1_os}</b></td><td><b>${gt.m1_noa}</b></td>
        <td>${gt.btc_os}</td><td>${gt.btc_noa}</td>
        <td>${gt.backflow_os}</td><td>${gt.backflow_noa}</td>
        <td>${gt.stay_os}</td><td>${gt.stay_noa}</td>
        <td>${gt.migrasi_os}</td><td>${gt.migrasi_noa}</td>
        <td>${gt.runoff_os}</td><td>${gt.runoff_noa}</td>
      </tr>`;

      // Data Rows
      rows.forEach(r => {
          if(r.m1_os <= 0) return;
          tableHtml += `<tr>
            <td>${r.tgl}</td>
            <td>${r.m1_os}</td><td>${r.m1_noa}</td>
            <td>${r.btc_os}</td><td>${r.btc_noa}</td>
            <td>${r.backflow_os}</td><td>${r.backflow_noa}</td>
            <td>${r.stay_os}</td><td>${r.stay_noa}</td>
            <td>${r.migrasi_os}</td><td>${r.migrasi_noa}</td>
            <td>${r.runoff_os}</td><td>${r.runoff_noa}</td>
          </tr>`;
      });
      tableHtml += `</table>`;

      const blob = new Blob([tableHtml], { type: 'application/vnd.ms-excel' });
      const a = document.createElement('a');
      a.href = window.URL.createObjectURL(blob);
      a.download = `Rekap_OTP_Migration_${document.getElementById('harian_date_otp').value}.xls`;
      document.body.appendChild(a); a.click(); document.body.removeChild(a);
  }
</script>