<style>
  :root { --primary: #2563eb; --bg: #f8fafc; --text: #334155; }
  /* Ganti height body agar pas di mobile browser */
  body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text); overflow: hidden; }
  
  /* Input & Button */
  .inp { 
      border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 0 0.5rem; 
      font-size: 13px; background: #fff; width: 100%; height: 38px; cursor: pointer; 
      min-width: 0; /* Penting buat layout flex */
  }
  .inp:disabled { background-color: #f1f5f9; color: #64748b; font-weight: 600; cursor: not-allowed; border-color: #e2e8f0; }
  .lbl { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 2px; display: block; }
  
  /* === DATEPICKER FIX === */
  input[type="date"] { position: relative; cursor: pointer; }
  input[type="date"]::-webkit-inner-spin-button,
  input[type="date"]::-webkit-calendar-picker-indicator {
      position: absolute; top: 0; left: 0; right: 0; bottom: 0;
      width: 100%; height: 100%; opacity: 0; cursor: pointer;
  }

  .btn-icon { width: 100%; height: 38px; border-radius: 8px; background: var(--primary); color: white; border: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: 0.2s; flex-shrink: 0; }
  .btn-icon:hover { background: #1d4ed8; }

  /* Table Wrapper */
  .table-wrapper { 
      overflow: auto; 
      height: 100%; 
      border-radius: 8px; 
      border: 1px solid #e2e8f0; 
      background: white; 
      position: relative;
      -webkit-overflow-scrolling: touch; /* Smooth scroll iOS */
  }
  
  table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 12px; }
  
  /* PENTING: Agar angka tidak turun baris */
  th, td { white-space: nowrap; }

  /* === HEADER STICKY === */
  /* Default Sticky Top untuk SEMUA Header */
  th { 
      position: sticky; top: 0; z-index: 60; 
      background: #d9ead3; color: #1e293b; font-weight: 700; 
      padding: 10px; border-bottom: 1px solid #cbd5e1; 
      text-transform: uppercase;
      box-shadow: 0 1px 0 rgba(0,0,0,0.1); 
  }
  td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle; }
  
  /* === STICKY COLUMNS (DESKTOP) === */
  
  /* Kolom 1: KODE (Selalu Sticky Kiri & Atas) */
  .sticky-col { position: sticky; left: 0; z-index: 65; background: white; border-right: 1px solid #e2e8f0; }
  th.sticky-col { z-index: 70; background: #d9ead3; }
  
  /* Kolom 2: NAMA KANTOR (Sticky Kiri & Atas di Desktop) */
  .sticky-col-2 { position: sticky; left: 60px; z-index: 64; background: white; border-right: 1px solid #e2e8f0; }
  th.sticky-col-2 { z-index: 69; background: #d9ead3; }

  /* === FOOTER STICKY === */
  tfoot { position: sticky; bottom: 0; z-index: 70; }
  tfoot td { 
      background: #eff6ff; font-weight: 700; border-top: 2px solid #bfdbfe; 
      color: #1e3a8a; box-shadow: 0 -4px 6px -1px rgba(0,0,0,0.1); 
      padding-top: 12px; padding-bottom: 12px;
  }
  /* Merged Cell Sticky Kiri */
  tfoot td.merged-total {
      position: sticky; left: 0; z-index: 75;
      text-align: center; border-right: 1px solid #bfdbfe; background: #eff6ff; 
  }

  tr:hover td { background-color: #f8fafc; }
  .hidden { display: none !important; }

  /* === RESPONSIVE FIX (MOBILE) === */
  @media (min-width: 768px) {
    .btn-icon { width: 38px; }
    .lbl { margin-bottom: 4px; }
  }

  @media (max-width: 767px) {
    /* --- FIX UTAMA BUG HEADER TERTUTUP --- */
    
    /* 1. Header (TH) Nama Kantor: Tetap Sticky ke ATAS, tapi lepas KIRI */
    th.sticky-col-2 { 
        position: sticky !important; /* Tetap Sticky */
        top: 0 !important;           /* Tetap Nempel Atas */
        left: auto !important;       /* Lepas Kiri */
        border-right: none !important;
        z-index: 60 !important;      /* Di atas body */
    }

    /* 2. Body (TD) Nama Kantor: Jadi Biasa (Static) */
    td.sticky-col-2 { 
        position: static !important; 
        border-right: none !important; 
        z-index: auto !important;
    }
    
    /* Font size & Input adjustments */
    table { font-size: 11px; }
    input[type="date"] { font-size: 11px; }
  }
</style>

<div class="max-w-7xl mx-auto px-2 md:px-4 py-4 h-[calc(100vh-80px)] md:h-[calc(100vh-120px)] flex flex-col">
  
  <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 mb-3">
    <div>
      <h1 class="text-xl md:text-2xl font-bold flex items-center gap-2 text-slate-800">
        <span class="bg-blue-600 text-white p-1 rounded">💳</span> 
        <span>Rekap Kolektibilitas</span>
      </h1>
      <p class="text-xs text-slate-500 mt-1 ml-1">*Data Posisi Harian (NOA & Baki Debet)</p>
    </div>

    <form id="formFilterKolek" class="flex flex-row items-end gap-2 w-full md:w-auto">
      
      <div class="flex-1 min-w-0 md:w-[180px] md:flex-none">
        <label class="lbl hidden md:block">Kantor</label>
        <select id="opt_kantor_kolek" class="inp"><option value="">Memuat...</option></select>
      </div>
      
      <div class="flex-1 min-w-0 md:w-[130px] md:flex-none">
        <label class="lbl hidden md:block">Tanggal</label>
        <input type="date" id="harian_date_kolek" class="inp" required>
      </div>
      
      <div class="shrink-0 md:w-auto">
        <label class="lbl hidden md:block opacity-0">Act</label> 
        <button type="submit" class="btn-icon w-[40px] md:w-[38px]" title="Cari Data">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        </button>
      </div>
    </form>
  </div>

  <div class="flex-1 min-h-0 relative flex flex-col">
    <div id="loadingKolek" class="hidden absolute inset-0 bg-white/80 z-50 flex flex-col items-center justify-center text-blue-600 font-bold text-sm backdrop-blur-sm rounded-lg">
        <div class="animate-spin h-8 w-8 border-4 border-blue-200 border-t-blue-600 rounded-full mb-2"></div>
        Memuat Data...
    </div>

    <div class="table-wrapper">
      <table id="tabelKolektibilitas">
        <thead>
          <tr>
            <th class="sticky-col text-center w-[60px]">Kode</th>
            
            <th class="sticky-col-2 text-left min-w-[180px]" id="thNamaKolek">NAMA KANTOR</th>
            
            <th class="text-right min-w-[100px]">Lancar (L)</th>
            <th class="text-right min-w-[100px]">DPK (DP)</th>
            <th class="text-right min-w-[100px]">Kurang Lancar</th>
            <th class="text-right min-w-[100px]">Diragukan (D)</th>
            <th class="text-right min-w-[100px]">Macet (M)</th>
            <th class="text-right min-w-[100px] bg-red-50 text-red-800 border-l border-red-100">Total NPL</th>
            <th class="text-right min-w-[110px] bg-blue-50 text-blue-800 border-l border-blue-100">Total Portofolio</th>
            <th class="text-right min-w-[70px]">% NPL</th>
          </tr>
        </thead>
        <tbody id="bodyKolek"></tbody>
        <tfoot id="footKolek"></tfoot>
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

  // Helper
  async function apiCall(url, options = {}) {
      const res = await fetch(url, options);
      if(!res.ok) throw new Error(`HTTP Error ${res.status}`);
      return res;
  }

  // --- INIT ---
  window.addEventListener('DOMContentLoaded', async () => {
    const user = (window.getUser && window.getUser()) || null;
    const uKode = (user?.kode ? String(user.kode).padStart(3,'0') : null);
      
      // 1. Load Dropdown
      await populateKantorKolek(uKode);

      // 2. Load Date
      const d = await getLastHarianData(); 
      if (d) {
          document.getElementById('harian_date_kolek').value = d.last_created;
      } else {
          document.getElementById('harian_date_kolek').value = new Date().toISOString().split('T')[0];
      }

      // 3. Fetch Data
      fetchKolektibilitas();
  });

  async function getLastHarianData(){
    try { const r = await apiCall(API_DATE); const j = await r.json(); return j.data || null; } catch{ return null; }
  }

  // --- POPULATE DROPDOWN ---
  async function populateKantorKolek(userKode){
    const optKantor = document.getElementById('opt_kantor_kolek');

    if(userKode !== '000' && userKode){
        optKantor.innerHTML = `<option value="${userKode}">CABANG ${userKode}</option>`;
        optKantor.value = userKode;
        optKantor.disabled = true;
        return; 
    }

    try {
        const res = await apiCall(API_KODE, { 
            method:'POST', 
            headers:{'Content-Type':'application/json'}, 
            body:JSON.stringify({type:'kode_kantor'}) 
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
      const tfoot = document.getElementById('footKolek');
      
      const harian  = document.getElementById('harian_date_kolek').value;
      const kantor  = document.getElementById('opt_kantor_kolek').value;

      document.getElementById('thNamaKolek').innerText = (kantor && kantor !== '') ? "NAMA KANKAS" : "NAMA KANTOR";

      loading.classList.remove('hidden');
      tbody.innerHTML = ''; tfoot.innerHTML = '';

      try {
          const payload = { type: 'kolektibilitas', harian_date: harian, kode_kantor: kantor };
          
          const res = await fetch(API_KOLEK, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          const json = await res.json();
          
          if(json.status && json.status !== 200) throw new Error(json.message);

          const rows = json.data?.data || [];
          const gt   = json.data?.grand_total || null;

          if (rows.length === 0) {
              tbody.innerHTML = `<tr><td colspan="10" class="text-center py-10 text-slate-400">Data Kosong</td></tr>`;
              return;
          }

          let html = '';
          rows.forEach(r => {
              html += `
                <tr class="hover:bg-blue-50 transition border-b group">
                    <td class="sticky-col text-center font-mono font-bold text-slate-500 bg-white group-hover:bg-blue-50">${r.kode_unit}</td>
                    
                    <td class="sticky-col-2 font-semibold text-slate-700 text-xs bg-white group-hover:bg-blue-50">
                        <div class="truncate" style="max-width: 200px;" title="${r.nama_unit}">${r.nama_unit}</div>
                    </td>
                    
                    <td class="text-right">${fmt(r.bd_L)} <div class="text-[10px] text-gray-400">${fmt(r.noa_L)} NOA</div></td>
                    <td class="text-right">${fmt(r.bd_DP)} <div class="text-[10px] text-gray-400">${fmt(r.noa_DP)} NOA</div></td>
                    <td class="text-right text-orange-700 bg-orange-50/20">${fmt(r.bd_KL)} <div class="text-[10px] text-orange-400">${fmt(r.noa_KL)} NOA</div></td>
                    <td class="text-right text-orange-800 bg-orange-50/40">${fmt(r.bd_D)} <div class="text-[10px] text-orange-500">${fmt(r.noa_D)} NOA</div></td>
                    <td class="text-right text-red-700 bg-red-50/20">${fmt(r.bd_M)} <div class="text-[10px] text-red-400">${fmt(r.noa_M)} NOA</div></td>
                    
                    <td class="text-right bg-red-50 text-red-800 font-bold border-l border-red-100">${fmt(r.bd_npl)}</td>
                    <td class="text-right bg-blue-50 text-blue-800 font-bold border-l border-blue-100">${fmt(r.total_bd)}</td>
                    <td class="text-right font-bold ${r.persentase_npl > 5 ? 'text-red-600' : 'text-green-600'}">${fmt2(r.persentase_npl)}%</td>
                </tr>
              `;
          });
          
          html += `<tr style="height: 80px;"><td colspan="10" class="border-none bg-transparent"></td></tr>`;
          tbody.innerHTML = html;

          if(gt) {
              const isMobile = window.innerWidth < 768;
              tfoot.innerHTML = `
                <tr>
                    <td class="merged-total text-center uppercase tracking-wide bg-blue-100 text-blue-900" colspan="${isMobile ? 2 : 1}">TOTAL</td>
                    ${!isMobile ? `<td class="sticky-col-2 bg-blue-100 border-r border-blue-200"></td>` : ''}

                    <td class="text-right font-bold">${fmt(gt.bd_L)}</td>
                    <td class="text-right font-bold">${fmt(gt.bd_DP)}</td>
                    <td class="text-right font-bold text-orange-700">${fmt(gt.bd_KL)}</td>
                    <td class="text-right font-bold text-orange-800">${fmt(gt.bd_D)}</td>
                    <td class="text-right font-bold text-red-700">${fmt(gt.bd_M)}</td>
                    <td class="text-right font-bold bg-red-200 text-red-900 border-l border-red-300">${fmt(gt.bd_npl)}</td>
                    <td class="text-right font-bold bg-blue-200 text-blue-900 border-l border-blue-300">${fmt(gt.total_bd)}</td>
                    <td class="text-right font-bold bg-slate-200 text-slate-900">${fmt2(gt.persentase_npl)}%</td>
                </tr>
              `;
          }

      } catch(e) {
          console.error(e);
          tbody.innerHTML = `<tr><td colspan="10" class="text-center py-10 text-red-500">Error: ${e.message}</td></tr>`;
      } finally {
          loading.classList.add('hidden');
      }
  }
</script>