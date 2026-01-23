<div class="max-w-7xl mx-auto px-4 py-4 h-screen flex flex-col">
  
  <div class="hdr flex flex-wrap items-start gap-2 mb-2">
    <h1 class="title text-2xl font-bold flex items-center gap-2">
      <span>📈</span> <span>Analisa Transaksi VA</span>
    </h1>

    <form id="formFilterVa" class="ml-auto" aria-label="Filter VA">
      <div id="filterVa" class="flex items-center gap-2 flex-wrap justify-end">
        
        <div class="flex flex-col">
            <label for="kode_kantor_va" class="lbl">Kode Kantor:</label>
            <input type="text" id="kode_kantor_va" class="inp w-24" placeholder="Semua">
        </div>

        <div class="flex flex-col">
            <label for="awal_date_va" class="lbl">Tgl Awal:</label>
            <input type="date" id="awal_date_va" class="inp" required>
        </div>

        <div class="flex flex-col">
            <label for="akhir_date_va" class="lbl">Tgl Akhir:</label>
            <input type="date" id="akhir_date_va" class="inp" required>
        </div>

        <button type="submit" class="btn-icon mt-auto" aria-label="Filter" style="margin-bottom: 2px;">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="7"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
        </button>
      </div>
    </form>
  </div>

  <div id="loadingVa" class="hidden flex items-center gap-2 text-sm text-gray-600 mb-2">
    <svg class="animate-spin h-5 w-5 text-blue-600" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span>Memuat data transaksi VA...</span>
  </div>

  <div id="vaScroller" class="flex-1 min-h-0 overflow-hidden rounded border border-gray-200 bg-white flex flex-col">
    <div id="vaScrollerInner" class="h-full overflow-auto scroll-smooth">
      
      <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 border-b border-gray-200 sticky-chart">
         <div class="bg-white p-3 rounded shadow-sm border h-60">
             <canvas id="chartAmount"></canvas>
         </div>
         <div class="bg-white p-3 rounded shadow-sm border h-60">
             <canvas id="chartTrx"></canvas>
         </div>
      </div>

      <table id="tabelVa" class="min-w-full text-sm text-left text-gray-700 border-collapse">
        <thead class="uppercase text-xs font-semibold text-gray-600">
          
          <tr id="vaHead1">
            <th class="px-4 py-2 sticky-rlz freeze-1 col1 bg-gray-100 border-r z-[51]" rowspan="2">NO</th>
            <th class="px-4 py-2 sticky-rlz freeze-2 col2 bg-gray-100 border-r z-[50]" rowspan="2">BULAN</th>
            
            <th class="px-4 py-2 sticky-rlz text-center bg-blue-50 text-blue-800 border-b border-r" colspan="2">BANK MANDIRI</th>
            <th class="px-4 py-2 sticky-rlz text-center bg-orange-50 text-orange-800 border-b border-r" colspan="2">BANK PERMATA</th>
            <th class="px-4 py-2 sticky-rlz text-center bg-gray-200 text-gray-800 border-b" colspan="2">TOTAL</th>
          </tr>

          <tr id="vaHead2">
             <th class="px-4 py-2 sticky-rlz top-2 bg-blue-50 text-right border-r text-blue-800">AMOUNT</th>
             <th class="px-4 py-2 sticky-rlz top-2 bg-blue-50 text-right border-r text-blue-800">TRX</th>
             
             <th class="px-4 py-2 sticky-rlz top-2 bg-orange-50 text-right border-r text-orange-800">AMOUNT</th>
             <th class="px-4 py-2 sticky-rlz top-2 bg-orange-50 text-right border-r text-orange-800">TRX</th>
             
             <th class="px-4 py-2 sticky-rlz top-2 bg-gray-200 text-right border-r text-gray-800">AMOUNT</th>
             <th class="px-4 py-2 sticky-rlz top-2 bg-gray-200 text-right text-gray-800">TRX</th>
          </tr>

        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
            </tbody>
        
        <tfoot class="sticky-footer bg-gray-100 font-bold text-gray-800 shadow-inner">
             </tfoot>
      </table>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
  /* ===== CSS COPY DARI REALISASI KREDIT (Modified for VA) ===== */
  .inp{ border:1px solid #cbd5e1; border-radius:.6rem; padding:.4rem .75rem; font-size:13px; background:#fff; }
  .lbl{ font-size:11px; color:#64748b; font-weight: 600; margin-bottom: 2px; }
  .btn-icon{ width:38px; height:38px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; background:#2563eb; color:#fff; cursor:pointer; border:none; transition:0.2s; }
  .btn-icon:hover{ background:#1e40af; }

  /* Responsive */
  .hdr{ row-gap:.5rem; align-items: flex-end; }
  @media (max-width:640px){
    .hdr{ flex-direction:column; align-items:flex-start; }
    #filterVa{ width:100%; justify-content: space-between; }
    .inp{ width: 100%; }
  }

  /* ===== FREEZE & STICKY LOGIC ===== */
  #vaScroller{ --va_col1:3.5rem; --va_col2:8rem; } /* Lebar No & Bulan */

  #tabelVa .col1{ width:var(--va_col1); min-width:var(--va_col1); text-align:center; }
  #tabelVa .col2{ width:var(--va_col2); min-width:var(--va_col2); }

  /* Freeze Columns (Left) */
  #tabelVa .freeze-1{ position:sticky; left:0; z-index:41; background:inherit; border-right:1px solid #e2e8f0; }
  #tabelVa .freeze-2{ position:sticky; left:var(--va_col1); z-index:40; background:inherit; border-right:1px solid #e2e8f0; box-shadow:2px 0 5px -2px rgba(0,0,0,0.1); }

  /* Sticky Header (Top) */
  #tabelVa thead th.sticky-rlz { position:sticky; top:0; z-index:45; }
  
  /* Fix Layering Header vs Freeze Column */
  #tabelVa thead th.freeze-1 { z-index:51 !important; }
  #tabelVa thead th.freeze-2 { z-index:50 !important; }

  /* Sticky Footer (Bottom) */
  #tabelVa tfoot td { position:sticky; bottom:0; z-index:45; background:#f1f5f9; padding: 0.75rem 1rem; border-top: 2px solid #e2e8f0; }
  #tabelVa tfoot td.freeze-1 { z-index:51; left:0; }
  #tabelVa tfoot td.freeze-2 { z-index:50; left:var(--va_col1); }

  /* Row Styling */
  #tabelVa tbody tr:nth-child(even) { background-color: #f8fafc; }
  #tabelVa tbody tr:nth-child(odd) { background-color: #ffffff; }
  #tabelVa tbody tr:hover td { background-color: #eff6ff !important; }

  /* Chart Container Fix */
  /* Agar chart tidak ketutup sticky header saat scroll awal, chart ikut scroll normal */
</style>

<script>
  /* ===== UTILS ===== */
  const fmtNum = n => new Intl.NumberFormat("id-ID").format(+n||0);

  /* ===== DUMMY DATA ===== */
  const dummyVaData = [
      { no:1, bulan:"Januari",   m_amt:10859, m_trx:3549, p_amt:494, p_trx:201 },
      { no:2, bulan:"Februari",  m_amt:10946, m_trx:3712, p_amt:397, p_trx:159 },
      { no:3, bulan:"Maret",     m_amt:12930, m_trx:4061, p_amt:578, p_trx:239 },
      { no:4, bulan:"April",     m_amt:11408, m_trx:4079, p_amt:606, p_trx:201 },
      { no:5, bulan:"Mei",       m_amt:12861, m_trx:4556, p_amt:542, p_trx:223 },
      { no:6, bulan:"Juni",      m_amt:6574,  m_trx:1702, p_amt:411, p_trx:115 },
      { no:7, bulan:"Juli",      m_amt:16984, m_trx:5327, p_amt:1492, p_trx:278 },
      { no:8, bulan:"Agustus",   m_amt:14793, m_trx:5320, p_amt:459, p_trx:264 },
      { no:9, bulan:"September", m_amt:14966, m_trx:5616, p_amt:456, p_trx:257 },
      { no:10,bulan:"Oktober",   m_amt:19014, m_trx:6009, p_amt:513, p_trx:277 },
      { no:11,bulan:"November",  m_amt:18417, m_trx:6198, p_amt:951, p_trx:296 },
      { no:12,bulan:"Desember",  m_amt:21871, m_trx:6729, p_amt:475, p_trx:258 },
  ];

  /* ===== INIT ===== */
  document.addEventListener("DOMContentLoaded", () => {
    // 1. Set Default Date
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), 0, 1); // Jan 1st
    document.getElementById("akhir_date_va").value = today.toISOString().split('T')[0];
    document.getElementById("awal_date_va").value = firstDay.toISOString().split('T')[0];

    // 2. Load Data
    handleFilterVa();
  });

  document.getElementById("formFilterVa").addEventListener("submit", (e)=>{
      e.preventDefault();
      handleFilterVa();
  });

  function handleFilterVa(){
      const loading = document.getElementById("loadingVa");
      loading.classList.remove("hidden");

      // Simulasi delay filter
      setTimeout(()=>{
          // Di sini nanti logika filter tanggal/cabang asli
          renderVaTable(dummyVaData);
          renderVaCharts(dummyVaData);
          loading.classList.add("hidden");
      }, 500);
  }

  /* ===== RENDER TABLE ===== */
  function renderVaTable(data) {
      const tbody = document.querySelector("#tabelVa tbody");
      const tfoot = document.querySelector("#tabelVa tfoot");
      tbody.innerHTML = ""; tfoot.innerHTML = "";

      let t_m_amt=0, t_m_trx=0, t_p_amt=0, t_p_trx=0, t_g_amt=0, t_g_trx=0;

      data.forEach(row => {
          const tot_amt = row.m_amt + row.p_amt;
          const tot_trx = row.m_trx + row.p_trx;

          t_m_amt += row.m_amt; t_m_trx += row.m_trx;
          t_p_amt += row.p_amt; t_p_trx += row.p_trx;
          t_g_amt += tot_amt;   t_g_trx += tot_trx;

          const tr = `
            <tr class="transition">
                <td class="px-4 py-3 freeze-1 col1 text-gray-500 font-mono border-r">${row.no}</td>
                <td class="px-4 py-3 freeze-2 col2 font-medium text-gray-800 border-r">${row.bulan}</td>
                
                <td class="px-4 py-3 text-right font-mono text-blue-700">${fmtNum(row.m_amt)}</td>
                <td class="px-4 py-3 text-right font-mono text-blue-600 border-r bg-blue-50/10">${fmtNum(row.m_trx)}</td>
                
                <td class="px-4 py-3 text-right font-mono text-orange-700">${fmtNum(row.p_amt)}</td>
                <td class="px-4 py-3 text-right font-mono text-orange-600 border-r bg-orange-50/10">${fmtNum(row.p_trx)}</td>
                
                <td class="px-4 py-3 text-right font-bold text-gray-800 bg-gray-50">${fmtNum(tot_amt)}</td>
                <td class="px-4 py-3 text-right font-bold text-gray-800 bg-gray-50">${fmtNum(tot_trx)}</td>
            </tr>
          `;
          tbody.insertAdjacentHTML('beforeend', tr);
      });

      // Render Footer (Sticky)
      tfoot.innerHTML = `
        <tr>
            <td class="freeze-1 col1 border-r text-center">ALL</td>
            <td class="freeze-2 col2 border-r">GRAND TOTAL</td>
            <td class="text-right text-blue-800">${fmtNum(t_m_amt)}</td>
            <td class="text-right text-blue-800 border-r">${fmtNum(t_m_trx)}</td>
            <td class="text-right text-orange-800">${fmtNum(t_p_amt)}</td>
            <td class="text-right text-orange-800 border-r">${fmtNum(t_p_trx)}</td>
            <td class="text-right text-gray-900 bg-gray-200">${fmtNum(t_g_amt)}</td>
            <td class="text-right text-gray-900 bg-gray-200">${fmtNum(t_g_trx)}</td>
        </tr>
      `;

      // Kalkulasi tinggi header row 2 agar sticky pas
      adjustStickyHeaders();
  }

  function adjustStickyHeaders(){
      const row1Height = document.getElementById('vaHead1').offsetHeight;
      const thsRow2 = document.querySelectorAll('#vaHead2 th');
      thsRow2.forEach(th => {
          th.style.top = row1Height + 'px';
      });
  }
  window.addEventListener('resize', adjustStickyHeaders);

  /* ===== RENDER CHARTS ===== */
  let chart1 = null, chart2 = null;
  function renderVaCharts(data){
      const ctx1 = document.getElementById('chartAmount');
      const ctx2 = document.getElementById('chartTrx');
      const labels = data.map(d => d.bulan);

      const cfg = (lbl, color) => ({
        label: lbl, borderColor: color, backgroundColor: color,
        tension: 0.3, pointRadius: 3, borderWidth: 2
      });

      // Chart Amount
      if(chart1) chart1.destroy();
      chart1 = new Chart(ctx1, {
          type: 'line',
          data: {
              labels,
              datasets: [
                  { ...cfg('Mandiri', '#2563eb'), data: data.map(d=>d.m_amt) },
                  { ...cfg('Permata', '#f97316'), data: data.map(d=>d.p_amt) }
              ]
          },
          options: { responsive:true, maintainAspectRatio:false, plugins:{ title:{ display:true, text:'Tren Nominal (Juta)' } } }
      });

      // Chart Trx
      if(chart2) chart2.destroy();
      chart2 = new Chart(ctx2, {
          type: 'bar',
          data: {
              labels,
              datasets: [
                  { ...cfg('Mandiri', '#2563eb'), data: data.map(d=>d.m_trx), backgroundColor:'#93c5fd' },
                  { ...cfg('Permata', '#f97316'), data: data.map(d=>d.p_trx), backgroundColor:'#fdba74' }
              ]
          },
          options: { responsive:true, maintainAspectRatio:false, plugins:{ title:{ display:true, text:'Tren Transaksi (Trx)' } } }
      });
  }
</script>