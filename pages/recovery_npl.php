<style>
  :root { --primary: #2563eb; --bg: #f8fafc; --text: #334155; }
  body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text); overflow: hidden; }
  
  /* === INPUT & CONTROLS === */
  .inp { 
      border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 0 0.5rem; 
      font-size: 13px; background: #fff; height: 36px; cursor: pointer; 
      outline: none; transition: border 0.2s; min-width: 0;
  }
  .inp:focus { border-color: var(--primary); }
  
  /* === DATEPICKER FIX === */
  input[type="date"] { position: relative; cursor: pointer; }
  input[type="date"]::-webkit-inner-spin-button,
  input[type="date"]::-webkit-calendar-picker-indicator {
      position: absolute; top: 0; left: 0; right: 0; bottom: 0;
      width: 100%; height: 100%; opacity: 0; cursor: pointer;
  }

  .btn-icon { 
      width: 36px; height: 36px; border-radius: 8px; background: var(--primary); 
      color: white; border: none; cursor: pointer; display: inline-flex; 
      align-items: center; justify-content: center; transition: 0.2s; flex-shrink: 0; 
  }
  .btn-icon:hover { background: #1d4ed8; }

  /* === TABLE SCROLLER === */
  #recScroller { 
      --rec_headH: 40px; 
      overflow: auto; 
      height: 100%; 
      border-radius: 8px; 
      border: 1px solid #e2e8f0; 
      background: white; 
      position: relative;
      -webkit-overflow-scrolling: touch; 
  }
  
  table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 12px; }
  th, td { white-space: nowrap; padding: 8px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
  tr:hover td { background-color: #f8fafc; }

  /* === HEADER STICKY === */
  thead th { 
      position: sticky; top: 0; z-index: 60; 
      background: #d9ead3; color: #1e293b; font-weight: 700; 
      text-transform: uppercase; border-bottom: 1px solid #cbd5e1; 
  }
  
  /* === STICKY COLUMNS (DESKTOP) === */
  .col-kode { position: sticky; left: 0; z-index: 45; background: white; border-right: 1px solid #e2e8f0; width: 60px; min-width: 60px; text-align: center; }
  thead th.col-kode { z-index: 70; background: #d9ead3; }
  
  .col-nama { position: sticky; left: 60px; z-index: 44; background: white; border-right: 1px solid #e2e8f0; box-shadow: 2px 0 5px rgba(0,0,0,0.03); min-width: 180px; }
  thead th.col-nama { z-index: 69; background: #d9ead3; }

  /* === TOTAL ROW STICKY (Nempel Tepat di Bawah Header) === */
  .sticky-total td { 
      position: sticky; 
      top: var(--rec_headH); 
      z-index: 55; 
      background: #f4f7fb; 
      font-weight: 700; 
      border-bottom: 2px solid #bfdbfe; 
      color: #1e3a8a;
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); 
  }
  .sticky-total td.col-kode { z-index: 65; background: #f4f7fb; border-right: none; }
  .sticky-total td.col-nama { z-index: 64; background: #f4f7fb; border-right: 1px solid #bfdbfe; }

  .hidden { display: none !important; }

  /* =========================================
     MOBILE RESPONSIVE (1 BARIS BERSIH)
     ========================================= */
  @media (max-width: 767px) {
      /* Form 1 Baris Sejajar, Tanpa Dropdown */
      #formFilterRecovery {
          flex-direction: row; flex-wrap: nowrap;
          width: 100%; gap: 6px; align-items: flex-end; justify-content: flex-end;
      }
      
      .filter-box { flex: 1 1 auto; min-width: 0; }
      #closing_date_recovery, #harian_date_recovery { width: 100%; font-size: 11px; padding: 0 4px; text-align: center; }
      .btn-icon { width: 36px; height: 36px; border-radius: 6px; }

      /* Tabel Tweaks Mobile */
      table { font-size: 11px; }
      th, td { padding: 6px 8px; }

      /* Sembunyikan Kolom KODE sepenuhnya */
      .col-kode { display: none !important; }
      
      /* Freeze NAMA KANTOR mentok kiri */
      .col-nama { left: 0 !important; z-index: 45 !important; min-width: 140px; max-width: 160px; white-space: normal; line-height: 1.2; }
      thead th.col-nama { z-index: 70 !important; }
      .sticky-total td.col-nama { z-index: 65 !important; }
  }

  /* Modal Styles */
  .modal-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 12px; }
  .modal-table th { position: sticky; top: 0; background: #f1f5f9; z-index: 10; padding: 8px; text-align: left; font-weight: 600; color: #475569; }
  .modal-table td { padding: 8px; border-bottom: 1px solid #e2e8f0; color: #334155; }
</style>

<script>
    let uObj = null;
    if(typeof window.getUser === 'function') uObj = window.getUser();
    else if(localStorage.getItem('app_user')) { try { uObj = JSON.parse(localStorage.getItem('app_user')); } catch(e){} }
    window.currentUser = { kode: (uObj?.kode ? String(uObj.kode).padStart(3,'0') : '000') };
</script>

<div class="max-w-7xl mx-auto px-3 md:px-4 py-4 h-[calc(100vh-80px)] md:h-[calc(100vh-120px)] flex flex-col font-sans bg-slate-50">
  
  <div class="flex flex-col md:flex-row md:items-end justify-between gap-3 mb-3 shrink-0">
    <div>
      <h1 class="text-xl md:text-2xl font-bold flex items-center gap-2 text-slate-800">
        <span class="bg-blue-600 text-white p-1 rounded text-sm md:text-base shadow-sm">💰</span> 
        <span>Recovery NPL</span>
        <span id="badgeUnit" class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-800 text-[10px] uppercase font-bold rounded">MEMUAT...</span>
      </h1>
      <p class="text-[10px] md:text-xs text-slate-500 mt-0.5 ml-1">*Posisi Closing vs Actual Harian</p>
    </div>

    <form id="formFilterRecovery" class="flex items-end gap-2">
      <div class="filter-box flex flex-col min-w-[110px]">
          <label class="text-[9px] md:text-[10px] font-bold text-slate-500 uppercase ml-1 mb-1 tracking-wider">Closing Date</label>
          <input type="date" id="closing_date_recovery" class="inp" required>
      </div>
      
      <div class="filter-box flex flex-col min-w-[110px]">
          <label class="text-[9px] md:text-[10px] font-bold text-slate-500 uppercase ml-1 mb-1 tracking-wider">Actual Date</label>
          <input type="date" id="harian_date_recovery" class="inp" required>
      </div>
      
      <button type="submit" class="btn-icon shadow-sm" title="Terapkan Filter">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
      </button>

      <button type="button" onclick="exportRecoveryExcel()" class="btn-icon bg-green-600 hover:bg-green-700 shadow-sm" title="Export Rekap Excel">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
      </button>
    </form>
  </div>

  <div class="flex-1 min-h-0 relative flex flex-col">
    <div id="loadingRecovery" class="hidden absolute inset-0 bg-white/80 z-[100] flex flex-col items-center justify-center text-blue-600 font-bold backdrop-blur-sm rounded-lg">
       <div class="animate-spin h-8 w-8 border-4 border-blue-200 border-t-blue-600 rounded-full mb-2"></div>
       <span>Memuat Data...</span>
    </div>

    <div id="recScroller" class="table-wrapper">
      <table id="tabelRecovery">
        <thead id="theadRec">
          <tr>
            <th class="col-kode">Kode</th>
            <th class="col-nama" id="thNamaRec">NAMA KANTOR</th>
            <th class="text-right">NOA Lunas</th>
            <th class="text-right">Baki Lunas</th>
            <th class="text-right">NOA Backflow</th>
            <th class="text-right">Baki Backflow</th>
            <th class="text-right cursor-pointer hover:bg-green-200" id="sortTotalNoa" title="Urutkan">Tot NOA ⬍</th>
            <th class="text-right cursor-pointer hover:bg-green-200" id="sortTotalBaki" title="Urutkan">Tot Baki ⬍</th>
          </tr>
        </thead>
        <tbody id="recoveryTotalRow"></tbody>
        <tbody id="recoveryBody"></tbody>
      </table>
    </div>
  </div>
</div>

<div id="modalDebiturRecovery" class="fixed inset-0 hidden bg-slate-900/60 backdrop-blur-sm items-center justify-center z-[9999] px-2 md:px-4">
  <div class="bg-white rounded-xl shadow-2xl flex flex-col w-full max-w-[1100px] h-[85vh] md:max-h-[85vh] overflow-hidden animate-scale-up">
    <div class="flex items-center justify-between p-3 md:p-4 border-b border-slate-100 bg-slate-50 shrink-0">
      <div>
        <h3 class="font-bold text-slate-800 text-base md:text-xl flex items-center gap-2">
            📄 <span id="modalTitleRecovery" class="truncate max-w-[200px] md:max-w-none">Detail Debitur</span>
        </h3>
        <p class="text-[10px] md:text-xs text-slate-500 mt-1" id="modalSubtitleRecovery">Daftar rekening</p>
      </div>
      <button id="btnCloseRecovery" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-200 hover:bg-red-100 hover:text-red-600 transition">✕</button>
    </div>
    <div class="flex-1 overflow-auto bg-white relative">
        <div id="modalBodyRecovery" class="min-w-full inline-block align-middle p-2 md:p-4"></div>
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
      <p class="mt-2">Anda tidak memiliki izin untuk melihat detail data nasabah milik <span class="font-bold text-red-600 px-1 bg-red-50 rounded" id="warnTargetLvl">Unit</span>.</p>
    </div>
    <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-end">
      <button onclick="closeModalPeringatan()" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded text-xs font-bold transition">Mengerti</button>
    </div>
  </div>
</div>

<script>
  // --- CONFIG ---
  const API_NPL  = './api/npl/'; 
  const API_DATE = './api/date/';

  const nfID = new Intl.NumberFormat('id-ID');
  const fmt  = n => nfID.format(Number(n||0));
  const num  = v => Number(v||0);
  const kodeNum = v => Number(String(v??'').replace(/\D/g,'')||0);
  const formatDate = (s) => { if(!s) return '-'; const d=new Date(s); return isNaN(d)?'-': `${String(d.getDate()).padStart(2,'0')}/${String(d.getMonth()+1).padStart(2,'0')}/${d.getFullYear()}`; };

  // --- STATE PENTING UNTUK EXPORT & SORTING ---
  let recoveryDataRaw = [];
  let recoveryGtRaw = null;
  let sortState = { column: null, direction: 1 };
  let abortCtrl;

  // --- FUNGSI AMBIL USER REALTIME (ANTI BUG NULL) ---
  function getAppUser() {
      let uObj = null;
      if(typeof window.getUser === 'function') uObj = window.getUser();
      if(!uObj && localStorage.getItem('app_user')) {
          try { uObj = JSON.parse(localStorage.getItem('app_user')); } catch(e){}
      }
      return (uObj && uObj.kode) ? String(uObj.kode).padStart(3, '0') : '000';
  }

  function updateRecStickyHeader() {
      const thead = document.getElementById('theadRec');
      const scroller = document.getElementById('recScroller');
      if(thead && scroller) {
          scroller.style.setProperty('--rec_headH', (thead.offsetHeight - 1) + 'px');
      }
  }
  window.addEventListener('resize', updateRecStickyHeader);

  // --- INIT ---
  window.addEventListener('DOMContentLoaded', async () => {
    // Load Tanggal Default
    try {
        const r = await fetch(API_DATE); 
        const j = await r.json(); 
        const d = j.data;
        if(d) {
            document.getElementById('closing_date_recovery').value = d.last_closing;
            document.getElementById('harian_date_recovery').value  = d.last_created;
        }
    } catch(e) {
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('closing_date_recovery').value = today;
        document.getElementById('harian_date_recovery').value = today;
    }
    
    // Fetch Data
    fetchRecoveryData();
  });

  // --- FILTER SUBMIT ---
  document.getElementById('formFilterRecovery').addEventListener('submit', e => {
    e.preventDefault();
    sortState = { column:null, direction:1 }; 
    fetchRecoveryData();
  });

  // --- FETCH DATA UTAMA ---
  async function fetchRecoveryData(){
    const loading = document.getElementById('loadingRecovery');
    loading.classList.remove('hidden');
    
    if(abortCtrl) abortCtrl.abort();
    abortCtrl = new AbortController();

    const tbody = document.getElementById('recoveryBody');
    const ttotal = document.getElementById('recoveryTotalRow');
    
    const closing_date = document.getElementById('closing_date_recovery').value;
    const harian_date  = document.getElementById('harian_date_recovery').value;
    
    // SISTEM OTOMATIS: 000 = narik konsolidasi. Selain 000 = narik cabang tersebut.
    const myKode = getAppUser();
    document.getElementById('badgeUnit').innerText = (myKode === '000') ? 'KONSOLIDASI' : `CABANG ${myKode}`;
    
    const kantor = (myKode === '000') ? '' : myKode; 

    document.getElementById('thNamaRec').innerText = (kantor !== '') ? "NAMA KANKAS" : "NAMA KANTOR";

    tbody.innerHTML = ''; 
    ttotal.innerHTML = ``;

    try {
      const payload = { type:'Recovery NPL', closing_date, harian_date, kode_kantor: kantor };
      const res = await fetch(API_NPL, {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify(payload),
        signal: abortCtrl.signal
      });
      const json = await res.json();
      
      let data = [];
      let totalRow = null;

      if(json.data && json.grand_total) {
          data = json.data.data || json.data; 
          totalRow = json.grand_total;
      } else if (Array.isArray(json.data)) {
          data = json.data;
          totalRow = data.find(d => (d.nama_kantor||d.nama_unit||'').toUpperCase().includes('TOTAL')) || null;
          data = data.filter(d => !(d.nama_kantor||d.nama_unit||'').toUpperCase().includes('TOTAL'));
      }

      // Simpan Ke Variabel Global Agar Bisa Di-Export Excel Nanti
      recoveryGtRaw = totalRow;
      recoveryDataRaw = data;
      recoveryDataRaw.sort((a,b)=> kodeNum(a.kode_cabang || a.kode_unit) - kodeNum(b.kode_cabang || b.kode_unit));

      renderTotal(totalRow);
      renderRows(recoveryDataRaw);

    } catch(err) {
      if(err.name !== 'AbortError') {
          console.error(err);
          tbody.innerHTML = `<tr><td colspan="8" class="p-4 text-center text-red-500 font-bold">Gagal memuat data.</td></tr>`;
      }
    } finally {
      loading.classList.add('hidden');
      setTimeout(updateRecStickyHeader, 50);
    }
  }

  function renderTotal(tot) {
     const el = document.getElementById('recoveryTotalRow');
     if(!tot) { el.innerHTML = ''; return; }
     
     const tNoa = num(tot.noa_lunas) + num(tot.noa_backflow);
     const tBak = num(tot.baki_debet_lunas) + num(tot.baki_debet_backflow);

     el.innerHTML = `
        <tr class="sticky-total">
            <td class="col-kode font-bold uppercase"></td>
            <td class="col-nama font-bold uppercase text-left">GRAND TOTAL</td>
            <td class="text-right">${fmt(tot.noa_lunas)}</td>
            <td class="text-right">${fmt(tot.baki_debet_lunas)}</td>
            <td class="text-right">${fmt(tot.noa_backflow)}</td>
            <td class="text-right">${fmt(tot.baki_debet_backflow)}</td>
            <td class="text-right text-blue-800">${fmt(tNoa)}</td>
            <td class="text-right text-blue-800">${fmt(tBak)}</td>
        </tr>
     `;
  }

  function renderRows(rows) {
     const tbody = document.getElementById('recoveryBody');
     if(rows.length === 0) {
         tbody.innerHTML = `<tr><td colspan="8" class="p-8 text-center text-slate-400">Tidak ada data recovery.</td></tr>`;
         return;
     }

     tbody.innerHTML = rows.map(r => {
        const tNoa = num(r.noa_lunas) + num(r.noa_backflow);
        const tBak = num(r.baki_debet_lunas) + num(r.baki_debet_backflow);
        const rawKode = r.kode_cabang || r.kode_unit || '';
        const kode = String(rawKode).padStart(3,'0');
        const nama = r.nama_kantor || r.nama_unit || '-';

        return `
            <tr class="border-b transition hover:bg-blue-50">
                <td class="col-kode font-mono font-bold text-slate-500 text-xs">${kode}</td>
                <td class="col-nama font-semibold text-slate-700 text-xs">
                    <div class="truncate" title="${nama}">${nama}</div>
                </td>
                
                <td class="text-right">
                    ${ num(r.noa_lunas) > 0 
                       ? `<a href="#" class="text-blue-600 font-bold hover:underline" data-act="view" data-type="lunas" data-kode="${kode}">${fmt(r.noa_lunas)}</a>` 
                       : `<span class="text-slate-300">-</span>` 
                    }
                </td>
                <td class="text-right text-slate-600">${num(r.baki_debet_lunas)>0 ? fmt(r.baki_debet_lunas) : '-'}</td>
                
                <td class="text-right">
                    ${ num(r.noa_backflow) > 0 
                       ? `<a href="#" class="text-orange-600 font-bold hover:underline" data-act="view" data-type="backflow" data-kode="${kode}">${fmt(r.noa_backflow)}</a>` 
                       : `<span class="text-slate-300">-</span>` 
                    }
                </td>
                <td class="text-right text-slate-600">${num(r.baki_debet_backflow)>0 ? fmt(r.baki_debet_backflow) : '-'}</td>
                
                <td class="text-right font-bold text-blue-800 bg-blue-50/30">${fmt(tNoa)}</td>
                <td class="text-right font-bold text-blue-800 bg-blue-50/30">${fmt(tBak)}</td>
            </tr>
        `;
     }).join('');

     // Spacer buat scroll mobile biar gak ketutup
     tbody.innerHTML += `<tr style="height: 60px;"><td colspan="8" class="border-none bg-transparent"></td></tr>`;
  }

  // --- SORTING ---
  const doSort = (colKey) => {
    sortState = { column: colKey, direction: sortState.column === colKey ? -sortState.direction : 1 };
    const sorted = [...recoveryDataRaw].sort((a,b) => {
        let valA, valB;
        if(colKey === 'total_noa') {
            valA = num(a.noa_lunas) + num(a.noa_backflow);
            valB = num(b.noa_lunas) + num(b.noa_backflow);
        } else {
            valA = num(a.baki_debet_lunas) + num(a.baki_debet_backflow);
            valB = num(b.baki_debet_lunas) + num(b.baki_debet_backflow);
        }
        return (valA - valB) * sortState.direction;
    });
    document.getElementById('sortTotalNoa').innerText = `Tot NOA ${colKey==='total_noa' ? (sortState.direction>0?'⬆':'⬇') : '⬍'}`;
    document.getElementById('sortTotalBaki').innerText = `Tot Baki ${colKey==='total_baki' ? (sortState.direction>0?'⬆':'⬇') : '⬍'}`;
    renderRows(sorted);
  };
  document.getElementById('sortTotalNoa').onclick = () => doSort('total_noa');
  document.getElementById('sortTotalBaki').onclick = () => doSort('total_baki');

  // --- EXPORT EXCEL (HTML STRING FIX) ---
  function exportRecoveryExcel() {
      const rows = window.recoveryDataRaw || [];
      const gt = window.recoveryGtRaw || null;
      
      if(rows.length === 0) { 
          alert("Tidak ada data untuk diexport!"); 
          return; 
      }

      // Gunakan String Table HTML agar formatnya sempurna dibaca Excel
      let table = `<table border="1">
          <thead>
              <tr>
                  <th style="background-color:#d9ead3;">KODE</th>
                  <th style="background-color:#d9ead3;">NAMA KANTOR</th>
                  <th style="background-color:#d9ead3;">NOA LUNAS</th>
                  <th style="background-color:#d9ead3;">BAKI DEBET LUNAS</th>
                  <th style="background-color:#d9ead3;">NOA BACKFLOW</th>
                  <th style="background-color:#d9ead3;">BAKI DEBET BACKFLOW</th>
                  <th style="background-color:#d9ead3;">TOTAL NOA</th>
                  <th style="background-color:#d9ead3;">TOTAL BAKI DEBET</th>
              </tr>
          </thead>
          <tbody>`;
      
      if(gt) {
          const tNoa = num(gt.noa_lunas) + num(gt.noa_backflow);
          const tBak = num(gt.baki_debet_lunas) + num(gt.baki_debet_backflow);
          table += `<tr>
              <td style="font-weight:bold;"></td>
              <td style="font-weight:bold;">GRAND TOTAL</td>
              <td style="font-weight:bold;">${gt.noa_lunas}</td>
              <td style="font-weight:bold;">${gt.baki_debet_lunas}</td>
              <td style="font-weight:bold;">${gt.noa_backflow}</td>
              <td style="font-weight:bold;">${gt.baki_debet_backflow}</td>
              <td style="font-weight:bold;">${tNoa}</td>
              <td style="font-weight:bold;">${tBak}</td>
          </tr>`;
      }

      rows.forEach(r => {
          const tNoa = num(r.noa_lunas) + num(r.noa_backflow);
          const tBak = num(r.baki_debet_lunas) + num(r.baki_debet_backflow);
          const kode = r.kode_cabang || r.kode_unit || '-';
          const nama = r.nama_kantor || r.nama_unit || '-';
          
          table += `<tr>
              <td style="mso-number-format:'\\@'">${kode}</td>
              <td>${nama}</td>
              <td>${r.noa_lunas}</td>
              <td>${r.baki_debet_lunas}</td>
              <td>${r.noa_backflow}</td>
              <td>${r.baki_debet_backflow}</td>
              <td>${tNoa}</td>
              <td>${tBak}</td>
          </tr>`;
      });
      
      table += `</tbody></table>`;

      const tgl = document.getElementById('harian_date_recovery').value;
      const blob = new Blob([table], { type: 'application/vnd.ms-excel' });
      const a = document.createElement('a');
      a.href = window.URL.createObjectURL(blob);
      a.download = `Rekap_Recovery_NPL_${tgl}.xls`;
      document.body.appendChild(a); 
      a.click(); 
      document.body.removeChild(a);
  }

  // --- MODAL DEBITUR & PERINGATAN (ACCESS DENIED) ---
  function closeModalPeringatan() {
      const modal = document.getElementById('modalPeringatan');
      modal.classList.add('hidden');
      modal.classList.remove('flex');
  }

  document.getElementById('tabelRecovery').addEventListener('click', e => {
      const link = e.target.closest('a[data-act="view"]');
      if(!link) return;
      e.preventDefault();

      const targetKode = String(link.dataset.kode).padStart(3,'0');
      const myKode = getAppUser();

      // PENGAMANAN AKSES (HANYA BISA BUKA MILIKNYA SENDIRI ATAU PUSAT)
      if (myKode !== '000' && myKode !== targetKode) {
          document.getElementById('warnUserLvl').innerText = `Unit ${myKode}`;
          document.getElementById('warnTargetLvl').innerText = `Unit ${targetKode}`;
          const modalWarn = document.getElementById('modalPeringatan');
          modalWarn.classList.remove('hidden');
          modalWarn.classList.add('flex');
          return;
      }

      openModalDebitur(link.dataset.type, targetKode);
  });

  async function openModalDebitur(type, kode){
      const modal = document.getElementById('modalDebiturRecovery');
      const title = document.getElementById('modalTitleRecovery');
      const sub   = document.getElementById('modalSubtitleRecovery');
      const body  = document.getElementById('modalBodyRecovery');

      const closing = document.getElementById('closing_date_recovery').value;
      const harian  = document.getElementById('harian_date_recovery').value;

      modal.classList.remove('hidden');
      modal.classList.add('flex');
      
      const labelType = type === 'lunas' ? 'Lunas' : 'Backflow';
      title.innerHTML = `${labelType} <span class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-800 text-xs rounded font-mono">${kode}</span>`;
      sub.innerText = `Posisi: ${formatDate(closing)} vs ${formatDate(harian)}`;
      
      body.innerHTML = `<div class="p-10 text-center"><div class="animate-spin h-8 w-8 border-4 border-slate-200 border-t-blue-600 rounded-full mx-auto mb-2"></div>Memuat detail...</div>`;

      try {
          const res = await fetch(API_NPL, {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ type, kode_kantor: kode, closing_date: closing, harian_date: harian })
          });
          const json = await res.json();
          const list = Array.isArray(json.data) ? json.data : [];

          if(list.length === 0) {
              body.innerHTML = `<div class="p-10 text-center text-slate-400 text-sm">Data detail tidak ditemukan.</div>`;
              return;
          }

          let tableHtml = `
            <table class="modal-table min-w-[800px]">
                <thead>
                    <tr>
                        <th class="w-[120px]">No Rek</th>
                        <th class="w-[200px]">Nama Nasabah</th>
                        <th class="text-right">Baki Debet</th>
                        <th class="text-center w-[50px]">Kol</th>
                        <th class="text-center w-[50px]">Upd</th>
                        <th class="text-center">Tgl Bayar</th>
                        <th class="text-right">Pokok</th>
                        <th class="text-right">Bunga</th>
                    </tr>
                </thead>
                <tbody>`;
          list.forEach(item => {
              tableHtml += `
                <tr class="hover:bg-slate-50">
                    <td class="font-mono text-slate-600 text-xs">${item.no_rekening}</td>
                    <td class="font-medium text-xs md:text-sm">
                        <div class="truncate max-w-[180px]" title="${item.nama_nasabah}">${item.nama_nasabah}</div>
                    </td>
                    <td class="text-right text-xs">${fmt(item.baki_debet)}</td>
                    <td class="text-center text-[10px] bg-red-50 text-red-700 rounded">${item.kolek||'-'}</td>
                    <td class="text-center text-[10px] bg-green-50 text-green-700 rounded font-bold">${item.kolek_update||'-'}</td>
                    <td class="text-center text-xs">${formatDate(item.tgl_trans)}</td>
                    <td class="text-right text-slate-500 text-xs">${fmt(item.angsuran_pokok)}</td>
                    <td class="text-right text-slate-500 text-xs">${fmt(item.angsuran_bunga)}</td>
                </tr>`;
          });
          tableHtml += `</tbody></table>`;
          body.innerHTML = tableHtml;
      } catch(e) {
          body.innerHTML = `<div class="p-10 text-center text-red-500 text-sm">Gagal mengambil detail.<br><small>${e.message}</small></div>`;
      }
  }

  const closeModal = () => {
      document.getElementById('modalDebiturRecovery').classList.add('hidden');
      document.getElementById('modalDebiturRecovery').classList.remove('flex');
  };
  document.getElementById('btnCloseRecovery').onclick = closeModal;
  document.getElementById('modalDebiturRecovery').onclick = (e) => {
      if(e.target.id === 'modalDebiturRecovery') closeModal();
  };
  document.addEventListener('keydown', e => { if(e.key === 'Escape') closeModal(); });
</script>