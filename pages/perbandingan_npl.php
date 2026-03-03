<style>
  :root { --primary: #2563eb; --bg: #f8fafc; --text: #334155; }
  body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text); overflow: hidden; }
  
  /* Input & Button */
  .inp { 
      border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 0 0.5rem; 
      font-size: 13px; background: #fff; height: 36px; cursor: pointer; 
      outline: none; transition: border 0.2s; min-width: 0;
  }
  .inp:focus { border-color: var(--primary); }
  .inp:disabled { background-color: #f1f5f9; color: #64748b; font-weight: 600; cursor: not-allowed; border-color: #e2e8f0; }
  
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

  /* Table Wrapper */
  #nplScroller { 
      --npl_headH: 40px; 
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

  /* === TOTAL ROW STICKY (DI BAWAH HEADER) === */
  .sticky-total td { 
      position: sticky; 
      top: var(--npl_headH); 
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
     MOBILE RESPONSIVE (1 BARIS)
     ========================================= */
  @media (max-width: 767px) {
      /* Form 1 Baris Sejajar */
      #formFilterNpl {
          flex-direction: row; flex-wrap: nowrap;
          width: 100%; gap: 6px; align-items: center;
      }
      
      #opt_kantor_npl { flex: 1 1 auto; min-width: 0; font-size: 11px; padding: 0 4px; }
      #closing_date_npl, #harian_date_npl { flex: 0 0 85px; width: 85px; font-size: 10px; padding: 0 2px; text-align: center; }
      
      .btn-icon { width: 34px; height: 34px; border-radius: 6px; }

      /* Tabel Tweaks Mobile */
      table { font-size: 11px; }
      th, td { padding: 6px 8px; }

      /* Sembunyikan KODE */
      .col-kode { display: none !important; }
      
      /* Freeze NAMA KANTOR mentok kiri */
      .col-nama { left: 0 !important; z-index: 45 !important; min-width: 140px; max-width: 160px; white-space: normal; line-height: 1.2; }
      thead th.col-nama { z-index: 70 !important; }
      .sticky-total td.col-nama { z-index: 65 !important; }
  }
</style>

<div class="max-w-7xl mx-auto px-3 md:px-4 py-4 h-[calc(100vh-80px)] md:h-[calc(100vh-120px)] flex flex-col">
  
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-3">
    <div>
      <h1 class="text-xl md:text-2xl font-bold flex items-center gap-2 text-slate-800">
        <span class="bg-blue-600 text-white p-1 rounded text-sm md:text-base">💳</span> 
        <span>Rekap NPL</span>
      </h1>
      <p class="text-[10px] md:text-xs text-slate-500 mt-0.5 ml-1">*NPL m-1 vs Actual</p>
    </div>

    <form id="formFilterNpl" class="flex gap-2">
      <select id="opt_kantor_npl" class="inp" title="Pilih Kantor"><option value="">Memuat...</option></select>
      
      <input type="date" id="closing_date_npl" class="inp" required title="Tanggal Closing">
      <input type="date" id="harian_date_npl" class="inp" required title="Tanggal Harian">
      
      <button type="submit" class="btn-icon" title="Cari Data">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
      </button>
      <button type="button" onclick="exportNplExcel()" class="btn-icon bg-green-600 hover:bg-green-700" title="Export Excel">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
      </button>
    </form>
  </div>

  <div class="flex-1 min-h-0 relative flex flex-col">
    <div id="loadingNpl" class="hidden absolute inset-0 bg-white/80 z-[100] flex flex-col items-center justify-center text-blue-600 font-bold text-sm backdrop-blur-sm rounded-lg">
        <div class="animate-spin h-8 w-8 border-4 border-blue-200 border-t-blue-600 rounded-full mb-2"></div>
        Memuat Data...
    </div>

    <div class="table-wrapper" id="nplScroller">
      <table id="tabelNpl">
        <thead id="theadNpl">
          <tr>
            <th class="col-kode">Kode</th>
            <th class="col-nama" id="thNamaNpl">NAMA KANTOR</th>
            <th class="text-right">NPL Closing</th>
            <th class="text-right">NPL Harian</th>
            <th class="text-right">Selisih</th>
            <th class="text-right">% Closing</th>
            <th class="text-right">% Harian</th>
            <th class="text-right">% Selisih</th>
          </tr>
        </thead>
        <tbody id="totalNpl"></tbody>
        <tbody id="bodyNpl"></tbody>
      </table>
    </div>
  </div>

</div>

<div id="modalDetailNpl" class="fixed inset-0 hidden bg-slate-900/60 backdrop-blur-sm z-[6000] items-center justify-center flex">
  <div class="bg-white rounded-xl shadow-2xl w-[95%] max-w-5xl max-h-[90vh] flex flex-col overflow-hidden animate-scale-up">
    <div class="flex justify-between items-center p-4 border-b bg-white">
      <h3 id="modalTitleRealisasi" class="font-bold text-lg text-slate-800">Detail NPL</h3>
      <button onclick="closeModalRealisasi()" class="text-slate-400 hover:text-red-500 text-2xl">&times;</button>
    </div>
    <div class="flex-1 overflow-auto bg-slate-50 p-0 relative">
        <div id="loadingModal" class="hidden absolute inset-0 bg-white/90 z-20 flex items-center justify-center text-blue-600 font-bold">Loading Detail...</div>
        <table class="w-full text-xs text-left text-slate-700">
            <thead class="bg-slate-100 font-bold uppercase text-slate-600 sticky top-0 shadow-sm">
                <tr>
                    <th class="px-4 py-3">Rekening</th>
                    <th class="px-4 py-3">Nasabah</th>
                    <th class="px-4 py-3 text-right">Plafond</th>
                    <th class="px-4 py-3 text-center">Tgl Realisasi</th>
                    <th class="px-4 py-3">Kankas</th>
                    <th class="px-4 py-3 text-blue-700">AO</th>
                </tr>
            </thead>
            <tbody id="modalBodyRealisasi" class="divide-y divide-slate-200 bg-white"></tbody>
        </table>
    </div>
    <div class="p-3 border-t bg-white flex justify-end">
        <button onclick="closeModalRealisasi()" class="px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-900 text-xs font-bold">Tutup</button>
    </div>
  </div>
</div>

<script>
  // --- CONFIG ---
  const API_NPL  = './api/npl/'; 
  const API_KODE = './api/kode/';
  const API_DATE = './api/date/';
  
  const nf = new Intl.NumberFormat('id-ID');
  const fmt = n => nf.format(Number(n)||0);
  const fmt2 = x => (x == null || x === '' ? '0.00' : Number(x).toFixed(2));

  // Variabel Global untuk Export
  window.nplDataRaw = [];
  window.nplGtRaw = null;

  // Header Dinamis Height
  function updateNplStickyHeader() {
      const thead = document.getElementById('theadNpl');
      const scroller = document.getElementById('nplScroller');
      if(thead && scroller) {
          scroller.style.setProperty('--npl_headH', (thead.offsetHeight - 1) + 'px');
      }
  }
  window.addEventListener('resize', updateNplStickyHeader);

  // Helper API
  async function apiCall(url, options = {}) {
      const res = await fetch(url, options);
      if(!res.ok) throw new Error(`HTTP Error ${res.status}`);
      return res;
  }

  // --- INIT ---
  window.addEventListener('DOMContentLoaded', async () => {
      // AMBIL USER LOGIN (Prioritas window.getUser -> localstorage -> '000')
      let userObj = null;
      if(typeof window.getUser === 'function') userObj = window.getUser();
      else if(localStorage.getItem('app_user')) {
          try { userObj = JSON.parse(localStorage.getItem('app_user')); } catch(e){}
      }
      
      const uKode = userObj?.kode ? String(userObj.kode).padStart(3,'0') : '000';
      window.currentUser = { kode: uKode };
      
      // 1. POPULATE DROPDOWN
      await populateKantorOptionsNpl(uKode); 

      // 2. SET DEFAULT DATE
      const d = await getLastHarianData(); 
      if (d) {
          document.getElementById('closing_date_npl').value = d.last_closing;
          document.getElementById('harian_date_npl').value  = d.last_created;
      } else {
          const today = new Date().toISOString().split('T')[0];
          document.getElementById('closing_date_npl').value = today;
          document.getElementById('harian_date_npl').value = today;
      }

      // 3. FETCH DATA
      fetchNplData();
  });

  async function getLastHarianData(){
    try { const r = await apiCall(API_DATE); const j = await r.json(); return j.data || null; } catch{ return null; }
  }

  // --- POPULATE DROPDOWN (USER LOGIC) ---
  async function populateKantorOptionsNpl(userKode){
    const optKantor = document.getElementById('opt_kantor_npl');

    // KONDISI 1: JIKA USER CABANG (Kode BUKAN 000 dan ADA ISINYA)
    if(userKode && userKode !== '000'){
        optKantor.innerHTML = `<option value="${userKode}">CABANG ${userKode}</option>`;
        optKantor.value = userKode;
        optKantor.disabled = true; // KUNCI!
        return; 
    }

    // KONDISI 2: JIKA USER PUSAT (000)
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
        
        optKantor.innerHTML = html;
        optKantor.disabled = false; // BUKA KUNCI!
    } catch(e){
        optKantor.innerHTML = `<option value="">Error Load</option>`;
    }
  }

  // --- FETCH DATA ---
  document.getElementById('formFilterNpl').addEventListener('submit', e => { e.preventDefault(); fetchNplData(); });

  async function fetchNplData() {
      const loading = document.getElementById('loadingNpl');
      const tbody = document.getElementById('bodyNpl');
      const tbodyTotal = document.getElementById('totalNpl');
      
      const closing = document.getElementById('closing_date_npl').value;
      const harian  = document.getElementById('harian_date_npl').value;
      const kantor  = document.getElementById('opt_kantor_npl').value;

      document.getElementById('thNamaNpl').innerText = (kantor && kantor !== '') ? "NAMA KANKAS" : "NAMA KANTOR";

      loading.classList.remove('hidden');
      tbody.innerHTML = ''; tbodyTotal.innerHTML = '';

      try {
          const payload = { type: 'NPL', closing_date: closing, harian_date: harian, kode_kantor: kantor };
          const res = await fetch(API_NPL, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          const json = await res.json();
          if(json.status && json.status !== 200) throw new Error(json.message);

          const rows = json.data?.data || json.data || [];
          const gt   = json.data?.grand_total || null;

          // Simpan untuk Export
          window.nplDataRaw = rows;
          window.nplGtRaw = gt;

          if (rows.length === 0) {
              tbody.innerHTML = `<tr><td colspan="8" class="text-center py-10 text-slate-400">Data Kosong</td></tr>`;
              return;
          }

          // === 1. TOTAL ROW (DI BAWAH HEADER) ===
          if(gt) {
              const gtVal = styleVal(gt.selisih_npl);
              const gtPct = stylePct(gt.selisih_npl_persen);
              tbodyTotal.innerHTML = `
                <tr class="sticky-total">
                    <td class="col-kode font-bold uppercase"></td>
                    <td class="col-nama font-bold uppercase text-left">GRAND TOTAL</td>
                    <td class="text-right font-bold">${fmt(gt.npl_closing)}</td>
                    <td class="text-right font-bold text-blue-800">${fmt(gt.npl_harian)}</td>
                    <td class="text-right font-bold ${gtVal.cls}">${gtVal.txt}</td>
                    <td class="text-right font-bold">${fmt2(gt.npl_closing_persen)}%</td>
                    <td class="text-right font-bold text-blue-800">${fmt2(gt.npl_harian_persen)}%</td>
                    <td class="text-right font-bold ${gtPct.cls}">${gtPct.txt}</td>
                </tr>
              `;
          }

          // === 2. DATA ROWS ===
          let html = '';
          rows.forEach(r => {
              const n = styleVal(r.selisih_npl);
              const p = stylePct(r.selisih_npl_persen);
              
              html += `
                <tr class="hover:bg-blue-50 transition border-b">
                    <td class="col-kode text-center font-mono font-bold text-slate-500">${r.kode_unit || r.kode_cabang || '-'}</td>
                    <td class="col-nama font-semibold text-slate-700 text-xs">
                        <div class="truncate" title="${r.nama_unit || r.nama_kantor || '-'}">${r.nama_unit || r.nama_kantor || '-'}</div>
                    </td>
                    <td class="text-right">${fmt(r.npl_closing)}</td>
                    <td class="text-right font-bold text-blue-800">${fmt(r.npl_harian)}</td>
                    <td class="text-right ${n.cls}">${n.txt}</td>
                    <td class="text-right text-slate-600">${fmt2(r.npl_closing_persen)}%</td>
                    <td class="text-right font-bold text-blue-800">${fmt2(r.npl_harian_persen)}%</td>
                    <td class="text-right ${p.cls}">${p.txt}</td>
                </tr>
              `;
          });
          
          tbody.innerHTML = html;

          setTimeout(updateNplStickyHeader, 50);

      } catch(e) {
          console.error(e);
          tbody.innerHTML = `<tr><td colspan="8" class="text-center py-10 text-red-500">Error: ${e.message}</td></tr>`;
      } finally {
          loading.classList.add('hidden');
      }
  }

  // --- EXPORT EXCEL ---
  function exportNplExcel() {
      const rows = window.nplDataRaw || [];
      const gt = window.nplGtRaw || null;
      if(rows.length === 0) { alert("Tidak ada data!"); return; }

      let csv = "KODE\tNAMA KANTOR\tNPL CLOSING\tNPL HARIAN\tSELISIH NPL\t% CLOSING\t% HARIAN\t% SELISIH\n";
      
      if(gt) {
          csv += `\tGRAND TOTAL\t${gt.npl_closing}\t${gt.npl_harian}\t${gt.selisih_npl}\t${gt.npl_closing_persen}%\t${gt.npl_harian_persen}%\t${gt.selisih_npl_persen}%\n`;
      }

      rows.forEach(r => {
          csv += `'${r.kode_unit || r.kode_cabang || '-'}\t${r.nama_unit || r.nama_kantor || '-'}\t${r.npl_closing}\t${r.npl_harian}\t${r.selisih_npl}\t${r.npl_closing_persen}%\t${r.npl_harian_persen}%\t${r.selisih_npl_persen}%\n`;
      });

      const tgl = document.getElementById('harian_date_npl').value;
      const blob = new Blob([csv], { type: 'application/vnd.ms-excel' });
      const a = document.createElement('a');
      a.href = window.URL.createObjectURL(blob);
      a.download = `Rekap_NPL_${tgl}.xls`;
      document.body.appendChild(a); a.click(); document.body.removeChild(a);
  }

  // --- Helper ---
  function styleVal(v) {
      const n = Number(v||0);
      if(n < 0) return { txt: `(${fmt(Math.abs(n))})`, cls:'text-green-600 font-bold' };
      if(n > 0) return { txt: `+${fmt(n)}`, cls:'text-red-600 font-bold' };
      return { txt: '-', cls:'text-slate-400' };
  }
  function stylePct(v) {
      const n = Number(v||0);
      if(n < 0) return { txt: `▼ ${fmt2(Math.abs(n))}%`, cls:'text-green-600 font-bold' };
      if(n > 0) return { txt: `▲ ${fmt2(n)}%`, cls:'text-red-600 font-bold' };
      return { txt: '0.00%', cls:'text-slate-400' };
  }

  function closeModalRealisasi() { document.getElementById('modalDetailNpl').classList.add('hidden'); }
</script>