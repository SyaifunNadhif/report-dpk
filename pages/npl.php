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
  #kolScroller { 
      --kol_headH: 40px; 
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
      top: var(--kol_headH); 
      z-index: 55; 
      background: #f4f7fb; 
      font-weight: 700; 
      border-bottom: 2px solid #bfdbfe; 
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); 
  }
  /* Total Freeze Kiri */
  .sticky-total td.col-kode { z-index: 65; background: #f4f7fb; border-right: none; }
  .sticky-total td.col-nama { z-index: 64; background: #f4f7fb; border-right: 1px solid #bfdbfe; }

  .hidden { display: none !important; }

  /* =========================================
     MOBILE RESPONSIVE (1 BARIS BERSIH)
     ========================================= */
  @media (max-width: 767px) {
      /* Form 1 Baris Sejajar */
      #formFilterKolek {
          flex-direction: row; flex-wrap: nowrap;
          width: 100%; gap: 6px; align-items: center;
      }
      
      #opt_kantor_kolek { flex: 1 1 auto; min-width: 0; font-size: 11px; padding: 0 4px; }
      #harian_date_kolek { flex: 0 0 95px; width: 95px; font-size: 11px; padding: 0 2px; text-align: center; }
      
      .btn-icon { width: 34px; height: 34px; border-radius: 6px; }

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
</style>

<div class="max-w-7xl mx-auto px-3 md:px-4 py-4 h-[calc(100vh-80px)] md:h-[calc(100vh-120px)] flex flex-col">
  
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-3">
    <div>
      <h1 class="text-xl md:text-2xl font-bold flex items-center gap-2 text-slate-800">
        <span class="bg-blue-600 text-white p-1 rounded text-sm md:text-base">💳</span> 
        <span>Rekap Kolektibilitas</span>
      </h1>
      <p class="text-[10px] md:text-xs text-slate-500 mt-0.5 ml-1">*Posisi Harian (NOA & Baki Debet)</p>
    </div>

    <form id="formFilterKolek" class="flex gap-2">
      <select id="opt_kantor_kolek" class="inp" title="Pilih Kantor">
          <option value="">Memuat...</option>
      </select>
      
      <input type="date" id="harian_date_kolek" class="inp" required title="Pilih Tanggal">
      
      <button type="submit" class="btn-icon" title="Cari Data">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
      </button>

      <button type="button" onclick="exportKolekExcel()" class="btn-icon bg-green-600 hover:bg-green-700" title="Export ke Excel">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
      </button>
    </form>
  </div>

  <div class="flex-1 min-h-0 relative flex flex-col">
    <div id="loadingKolek" class="hidden absolute inset-0 bg-white/80 z-[100] flex flex-col items-center justify-center text-blue-600 font-bold text-sm backdrop-blur-sm rounded-lg">
        <div class="animate-spin h-8 w-8 border-4 border-blue-200 border-t-blue-600 rounded-full mb-2"></div>
        Memuat Data...
    </div>

    <div class="table-wrapper" id="kolScroller">
      <table id="tabelKolektibilitas">
        <thead id="theadKolek">
          <tr>
            <th class="col-kode">Kode</th>
            <th class="col-nama" id="thNamaKolek">NAMA KANTOR</th>
            
            <th class="text-right min-w-[90px]">Lancar (L)</th>
            <th class="text-right min-w-[90px]">DPK (DP)</th>
            <th class="text-right min-w-[90px]">Kurang Lancar</th>
            <th class="text-right min-w-[90px]">Diragukan (D)</th>
            <th class="text-right min-w-[90px]">Macet (M)</th>
            
            <th class="text-right min-w-[100px] border-l border-slate-200">Total NPL</th>
            <th class="text-right min-w-[110px] border-l border-slate-200">Total Portofolio</th>
            <th class="text-right min-w-[70px]">% NPL</th>
          </tr>
        </thead>
        <tbody id="totalKolek"></tbody>
        <tbody id="bodyKolek"></tbody>
      </table>
    </div>
  </div>

</div>

<script>
  // --- CONFIG ---
  const API_KOLEK = './api/kredit/'; 
  const API_KODE  = './api/kode/';
  const API_DATE  = './api/date/';

  const nf = new Intl.NumberFormat('id-ID');
  const fmt = n => nf.format(Number(n)||0);
  const fmt2 = x => (x == null || x === '' ? '0.00' : Number(x).toFixed(2));

  // Simpan data mentah untuk Export Excel
  window.kolekDataRaw = [];
  window.kolekGtRaw = null;

  // Set Tinggi Dinamis Header untuk Sticky Total Row
  function updateStickyHeader() {
      const thead = document.getElementById('theadKolek');
      const scroller = document.getElementById('kolScroller');
      if(thead && scroller) {
          scroller.style.setProperty('--kol_headH', (thead.offsetHeight - 1) + 'px');
      }
  }
  window.addEventListener('resize', updateStickyHeader);

  // Helper API
  async function apiCall(url, options = {}) {
      const res = await fetch(url, options);
      if(!res.ok) throw new Error(`HTTP Error ${res.status}`);
      return res;
  }

  // --- INIT ---
  window.addEventListener('DOMContentLoaded', async () => {
      // AMBIL USER LOGIN
      const user = (window.getUser && window.getUser()) || null;
      // Beri fallback default '000' (Pusat) jika tidak terdeteksi agar tidak null error
      const uKode = user?.kode ? String(user.kode).padStart(3,'0') : '000';
      window.currentUser = { kode: uKode };
      
      // 1. Load Dropdown
      await populateKantorKolek(uKode);

      // 2. Load Date
      const d = await getLastHarianData(); 
      if (d) {
          document.getElementById('harian_date_kolek').value = d.last_created;
      } else {
          document.getElementById('harian_date_kolek').value = new Date().toISOString().split('T')[0];
      }

      // 3. Fetch Data Awal
      fetchKolektibilitas();
  });

  async function getLastHarianData(){
    try { const r = await apiCall(API_DATE); const j = await r.json(); return j.data || null; } catch{ return null; }
  }

  // --- POPULATE DROPDOWN (LOGIC USER FIXED) ---
  async function populateKantorKolek(userKode){
    const optKantor = document.getElementById('opt_kantor_kolek');

    // JIKA KODE ADA ISINYA DAN BUKAN '000' (Berarti Cabang) -> KUNCI DROPDOWN
    if(userKode && userKode !== '000'){
        optKantor.innerHTML = `<option value="${userKode}">CABANG ${userKode}</option>`;
        optKantor.value = userKode;
        optKantor.disabled = true; // KUNCI!
        return; 
    }

    // JIKA KODE '000' ATAU PUSAT -> BUKA KONSOLIDASI
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
        optKantor.disabled = false;
    } catch(e){
        optKantor.innerHTML = `<option value="">Error Load</option>`;
    }
  }

  // --- FETCH DATA ---
  document.getElementById('formFilterKolek').addEventListener('submit', e => { e.preventDefault(); fetchKolektibilitas(); });

  async function fetchKolektibilitas() {
      const loading = document.getElementById('loadingKolek');
      const tbody = document.getElementById('bodyKolek');
      const tbodyTotal = document.getElementById('totalKolek');
      
      const harian  = document.getElementById('harian_date_kolek').value;
      const kantor  = document.getElementById('opt_kantor_kolek').value;

      document.getElementById('thNamaKolek').innerText = (kantor && kantor !== '') ? "NAMA KANKAS" : "NAMA KANTOR";

      loading.classList.remove('hidden');
      tbody.innerHTML = ''; tbodyTotal.innerHTML = '';

      try {
          const payload = { type: 'kolektibilitas', harian_date: harian, kode_kantor: kantor };
          const res = await fetch(API_KOLEK, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          const json = await res.json();
          
          if(json.status && json.status !== 200) throw new Error(json.message);

          const rows = json.data?.data || json.data || [];
          const gt   = json.data?.grand_total || null;

          // Simpan raw data untuk export
          window.kolekDataRaw = rows;
          window.kolekGtRaw = gt;

          if (rows.length === 0) {
              tbody.innerHTML = `<tr><td colspan="10" class="text-center py-10 text-slate-400">Data Kosong</td></tr>`;
              return;
          }

          // === 1. RENDER GRAND TOTAL (DI BAWAH HEADER) ===
          if(gt) {
              tbodyTotal.innerHTML = `
                <tr class="sticky-total">
                    <td class="col-kode font-bold text-blue-800"></td>
                    <td class="col-nama font-bold text-blue-800 text-left">GRAND TOTAL</td>
                    
                    <td class="text-right font-bold text-blue-700">${fmt(gt.bd_L)} <div class="text-[10px] text-slate-500 font-normal">${fmt(gt.noa_L)} NOA</div></td>
                    <td class="text-right font-bold text-slate-700">${fmt(gt.bd_DP)} <div class="text-[10px] text-slate-500 font-normal">${fmt(gt.noa_DP)} NOA</div></td>
                    <td class="text-right font-bold text-orange-500">${fmt(gt.bd_KL)} <div class="text-[10px] text-slate-500 font-normal">${fmt(gt.noa_KL)} NOA</div></td>
                    <td class="text-right font-bold text-orange-600">${fmt(gt.bd_D)} <div class="text-[10px] text-slate-500 font-normal">${fmt(gt.noa_D)} NOA</div></td>
                    <td class="text-right font-bold text-red-600">${fmt(gt.bd_M)} <div class="text-[10px] text-slate-500 font-normal">${fmt(gt.noa_M)} NOA</div></td>
                    
                    <td class="text-right font-bold text-red-700 border-l border-slate-200">${fmt(gt.bd_npl)} <div class="text-[10px] text-slate-500 font-normal">${fmt(gt.noa_npl)} NOA</div></td>
                    <td class="text-right font-bold text-blue-800 border-l border-slate-200">${fmt(gt.total_bd)} <div class="text-[10px] text-slate-500 font-normal">${fmt(gt.total_noa)} NOA</div></td>
                    <td class="text-right font-bold text-slate-800">${fmt2(gt.persentase_npl)}%</td>
                </tr>
              `;
          }

          // === 2. RENDER DATA ROWS (CLEAN UI) ===
          let html = '';
          rows.forEach(r => {
              html += `
                <tr class="hover:bg-blue-50 transition border-b">
                    <td class="col-kode text-center font-mono text-slate-500">${r.kode_unit}</td>
                    <td class="col-nama font-semibold text-slate-700 text-xs">
                        <div class="truncate" title="${r.nama_unit}">${r.nama_unit}</div>
                    </td>
                    
                    <td class="text-right text-blue-700">${fmt(r.bd_L)} <div class="text-[10px] text-slate-400">${fmt(r.noa_L)} NOA</div></td>
                    <td class="text-right text-slate-700">${fmt(r.bd_DP)} <div class="text-[10px] text-slate-400">${fmt(r.noa_DP)} NOA</div></td>
                    <td class="text-right text-orange-500">${fmt(r.bd_KL)} <div class="text-[10px] text-slate-400">${fmt(r.noa_KL)} NOA</div></td>
                    <td class="text-right text-orange-600">${fmt(r.bd_D)} <div class="text-[10px] text-slate-400">${fmt(r.noa_D)} NOA</div></td>
                    <td class="text-right text-red-600">${fmt(r.bd_M)} <div class="text-[10px] text-slate-400">${fmt(r.noa_M)} NOA</div></td>
                    
                    <td class="text-right font-bold text-red-700 border-l border-slate-100">${fmt(r.bd_npl)} <div class="text-[10px] text-slate-400 font-normal">${fmt(r.noa_npl)} NOA</div></td>
                    <td class="text-right font-bold text-blue-800 border-l border-slate-100">${fmt(r.total_bd)} <div class="text-[10px] text-slate-400 font-normal">${fmt(r.total_noa)} NOA</div></td>
                    <td class="text-right font-bold ${r.persentase_npl > 5 ? 'text-red-600' : 'text-green-600'}">${fmt2(r.persentase_npl)}%</td>
                </tr>
              `;
          });
          
          tbody.innerHTML = html;
          
          // Kalkulasi offset Sticky Total agar pas setelah render
          setTimeout(updateStickyHeader, 50);

      } catch(e) {
          console.error(e);
          tbody.innerHTML = `<tr><td colspan="10" class="text-center py-10 text-red-500">Error: ${e.message}</td></tr>`;
      } finally {
          loading.classList.add('hidden');
      }
  }

  // --- EXPORT EXCEL (PISAH KOLOM NOA & OS) ---
  function exportKolekExcel() {
      const rows = window.kolekDataRaw || [];
      const gt = window.kolekGtRaw || null;
      
      if(rows.length === 0) {
          alert("Tidak ada data untuk diexport!");
          return;
      }

      // Header CSV
      let csv = "KODE\tNAMA KANTOR\tLANCAR (NOA)\tLANCAR (OS)\tDPK (NOA)\tDPK (OS)\tKURANG LANCAR (NOA)\tKURANG LANCAR (OS)\tDIRAGUKAN (NOA)\tDIRAGUKAN (OS)\tMACET (NOA)\tMACET (OS)\tTOTAL NPL (NOA)\tTOTAL NPL (OS)\tTOTAL PORTOFOLIO (NOA)\tTOTAL PORTOFOLIO (OS)\t% NPL\n";

      // Baris Total
      if(gt) {
          csv += `\tGRAND TOTAL\t${gt.noa_L}\t${gt.bd_L}\t${gt.noa_DP}\t${gt.bd_DP}\t${gt.noa_KL}\t${gt.bd_KL}\t${gt.noa_D}\t${gt.bd_D}\t${gt.noa_M}\t${gt.bd_M}\t${gt.noa_npl}\t${gt.bd_npl}\t${gt.total_noa}\t${gt.total_bd}\t${gt.persentase_npl}\n`;
      }

      // Baris Data
      rows.forEach(r => {
          csv += `'${r.kode_unit}\t${r.nama_unit}\t${r.noa_L}\t${r.bd_L}\t${r.noa_DP}\t${r.bd_DP}\t${r.noa_KL}\t${r.bd_KL}\t${r.noa_D}\t${r.bd_D}\t${r.noa_M}\t${r.bd_M}\t${r.noa_npl}\t${r.bd_npl}\t${r.total_noa}\t${r.total_bd}\t${r.persentase_npl}\n`;
      });

      // Export file
      const tgl = document.getElementById('harian_date_kolek').value;
      const blob = new Blob([csv], { type: 'application/vnd.ms-excel' });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `Rekap_Kolektibilitas_${tgl}.xls`;
      document.body.appendChild(a); 
      a.click(); 
      document.body.removeChild(a);
  }
</script>