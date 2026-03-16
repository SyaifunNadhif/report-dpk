<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="max-w-[1400px] mx-auto px-4 py-6 bg-gray-50 min-h-screen font-sans">
  
  <div class="flex flex-col md:flex-row justify-between items-end mb-6">
    <div>
      <h1 class="text-2xl md:text-3xl font-extrabold text-gray-800 tracking-tight">📊 Executive Dashboard</h1>
      <p class="text-sm text-gray-500 mt-1 font-medium">Pusat Komando Portofolio & Kinerja Bisnis</p>
    </div>

    <form id="formFilterMaster" class="flex flex-wrap items-end gap-3 mt-4 md:mt-0 bg-white p-3 rounded-xl shadow-sm border border-gray-200">
      <div class="flex flex-col">
        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Closing M-1</label>
        <input type="date" id="filter_closing" class="border-b-2 border-transparent hover:border-gray-300 px-1 py-1 text-sm outline-none focus:border-blue-500 transition-colors font-medium">
      </div>
      <div class="flex flex-col">
        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Harian/Actual</label>
        <input type="date" id="filter_harian" class="border-b-2 border-transparent hover:border-gray-300 px-1 py-1 text-sm outline-none focus:border-blue-500 transition-colors font-medium">
      </div>
      <div class="flex flex-col">
        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Area/Cabang</label>
        <select id="filter_kantor" class="border-b-2 border-transparent hover:border-gray-300 px-1 py-1 text-sm min-w-[180px] outline-none focus:border-blue-500 bg-transparent transition-colors font-bold text-gray-700">
          </select>
      </div>
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-bold transition-all flex items-center gap-2 shadow-md transform active:scale-95">
        Tampilkan
      </button>
    </form>
  </div>

  <div id="loadingDash" class="hidden flex flex-col justify-center items-center py-32">
    <div class="animate-spin rounded-full h-14 w-14 border-t-4 border-b-4 border-blue-600 mb-4"></div>
    <span class="text-gray-500 font-semibold animate-pulse">Loading data dari database...</span>
  </div>

  <div id="contentDash" class="hidden space-y-6 overflow-x-hidden">
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 relative">
        <div class="absolute top-0 left-0 w-1.5 h-full bg-blue-500 rounded-l-2xl"></div>
        <p class="text-[11px] text-gray-500 font-bold uppercase tracking-wider mb-1">Total Baki Debet (OS)</p>
        <h3 id="kpi_os" class="text-3xl font-black text-gray-900 tracking-tight">Rp 0</h3>
        <div class="mt-3 flex flex-wrap items-center gap-2 text-sm flex-col items-start" id="kpi_os_pill"></div>
      </div>
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 relative">
        <div class="absolute top-0 left-0 w-1.5 h-full bg-red-500 rounded-l-2xl"></div>
        <p class="text-[11px] text-gray-500 font-bold uppercase tracking-wider mb-1">Total OSC NPL</p>
        <h3 id="kpi_npl" class="text-3xl font-black text-red-600 tracking-tight">Rp 0</h3>
        <div class="mt-3 flex flex-wrap items-center gap-2 text-sm" id="kpi_npl_pill"></div>
      </div>
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 relative">
        <div class="absolute top-0 left-0 w-1.5 h-full bg-green-500 rounded-l-2xl"></div>
        <p class="text-[11px] text-gray-500 font-bold uppercase tracking-wider mb-1">Repayment Rate (RR)</p>
        <h3 id="kpi_rr" class="text-3xl font-black text-green-600 tracking-tight">0%</h3>
        <div class="mt-3 flex flex-wrap items-center gap-2 text-sm" id="kpi_rr_pill"></div>
      </div>
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 relative">
        <div class="absolute top-0 left-0 w-1.5 h-full bg-purple-500 rounded-l-2xl"></div>
        <p class="text-[11px] text-gray-500 font-bold uppercase tracking-wider mb-1">Total DPK (Depo + Tab)</p>
        <h3 id="kpi_dpk" class="text-3xl font-black text-purple-700 tracking-tight">Rp 0</h3>
        <div class="mt-3 flex flex-wrap items-center gap-2 text-sm" id="kpi_dpk_pill"></div>
      </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-4">
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 lg:col-span-2 flex flex-col">
        <div class="flex justify-between items-center mb-2 border-b border-gray-100 pb-3">
          <h3 class="font-bold text-gray-800 flex items-center gap-2">
            <span class="text-red-500">📈</span> Tren Pergerakan NPL (%)
          </h3>
            <select id="filter_tren" class="border border-gray-200 rounded-md px-2 py-1 text-xs font-semibold text-gray-600 outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer bg-white shadow-sm">
                <option value="tahunan">Periode Tahunan</option>
                <option value="bulanan">Periode Bulanan</option>
                <option value="mingguan">Periode Mingguan</option>
                <option value="30_hari">30 Hari Terakhir</option>
                <option value="14_hari">14 Hari Terakhir</option>
                <option value="7_hari" selected>7 Hari Terakhir</option>
            </select>
        </div>
        <div class="relative flex-grow min-h-[220px] w-full mt-2">
          <div id="loadingChartTren" class="absolute inset-0 flex justify-center items-center bg-white bg-opacity-80 z-10 hidden">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-red-500"></div>
          </div>
          <canvas id="canvasTrenNPL"></canvas>
        </div>
      </div>

      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col">
        <div class="flex items-center justify-between mb-3 border-b border-gray-100 pb-2">
          <h3 class="font-bold text-gray-800 flex items-center gap-2 text-sm">
            <span class="text-indigo-500">📦</span> Realisasi by Produk
          </h3>
        </div>
        <div id="box_realisasi_produk" class="space-y-3 flex-grow"></div>
      </div>
    </div>

    <div class="grid lg:grid-cols-12 gap-4 mt-6">
      
      <div class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100 lg:col-span-3 flex flex-col">
        <h3 class="font-bold text-gray-800 mb-3 border-b border-gray-100 pb-2 text-[12px] flex items-center gap-1.5 leading-tight shrink-0">
          <span>🔄</span> Realisasi vs Run Off
        </h3>
        <div id="box_runoff_realisasi" class="space-y-3 flex-grow mb-3"></div>
        
        <div class="mt-auto pt-3 border-t border-gray-50 flex items-center justify-center gap-4 text-[10px] font-bold text-gray-500 shrink-0">
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-1.5 rounded-full bg-green-400"></span> Realisasi
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-1.5 rounded-full bg-red-400"></span> Run Off
            </div>
        </div>
      </div>

      <div class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100 lg:col-span-3 flex flex-col">
        <h3 class="font-bold text-gray-800 mb-3 border-b border-gray-100 pb-2 text-[12px] flex items-center gap-1.5 leading-tight shrink-0">
          <span>🛡️</span> Flow NPL vs Recovery
        </h3>
        <div id="box_flow_recovery" class="space-y-3 flex-grow mb-3"></div>
        
        <div class="mt-auto pt-3 border-t border-gray-50 flex items-center justify-center gap-4 text-[10px] font-bold text-gray-500 shrink-0">
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-1.5 rounded-full bg-red-400"></span> Flow NPL
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-1.5 rounded-full bg-green-400"></span> Recovery
            </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 lg:col-span-6 relative flex flex-col">
        <div id="loadingChartRunoff" class="absolute inset-0 flex justify-center items-center bg-white bg-opacity-90 z-10 hidden rounded-3xl">
           <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
        </div>
        <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-3 shrink-0">
          <div>
            <h3 class="font-bold text-gray-800 flex items-center gap-2 text-lg">
              <span class="text-blue-500">📊</span> Tren Realisasi vs Run Off
            </h3>
            <span class="text-xs text-gray-400 font-medium" id="label_runoff_date">Berdasarkan Tanggal: -</span>
          </div>
          
          <select id="filter_tren_runoff" class="border border-gray-200 rounded-md px-3 py-1.5 text-sm font-semibold text-gray-600 outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer bg-white shadow-sm">
              <option value="tahunan">Periode Tahunan</option>
              <option value="bulanan">Periode Bulanan</option>
              <option value="mingguan">Periode Mingguan</option>
              <option value="30_hari">30 Hari Terakhir</option>
              <option value="14_hari">14 Hari Terakhir</option>
              <option value="7_hari" selected>7 Hari Terakhir</option>
          </select>
        </div>
        <div class="relative w-full flex-grow min-h-[250px]">
          <canvas id="canvasTrenRunoff"></canvas>
        </div>
      </div>

    </div>

    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 mt-10">
      <div class="flex items-center gap-3 mb-6 border-b border-gray-100 pb-4">
        <div class="bg-yellow-100 p-2 rounded-lg"><span class="text-3xl">🏆</span></div>
        <div>
          <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">5 Best Performance</h2>
          <p class="text-sm text-gray-500 font-medium">Jajaran Cabang dan Pegawai Terbaik</p>
        </div>
      </div>
      <div class="grid lg:grid-cols-4 gap-5">
        <div class="space-y-5">
          <div><h3 class="font-bold text-gray-800 mb-3 text-[13px] flex items-center gap-2"><span class="text-blue-500">📈</span> Top Realisasi Cabang</h3><div id="best_realisasi" class="space-y-3"></div></div>
          <div class="pt-4 border-t border-dashed border-gray-200"><h3 class="font-bold text-gray-800 mb-3 text-[13px] flex items-center gap-2"><span class="text-orange-500">🥇</span> Top Realisasi AO</h3><div id="best_realisasi_ao" class="space-y-3"></div></div>
        </div>
        <div class="space-y-5">
          <div><h3 class="font-bold text-gray-800 mb-3 text-[13px] flex items-center gap-2"><span class="text-red-500">🛡️</span> Top NPL Terendah (Terbaik)</h3><div id="best_npl" class="space-y-3"></div></div>
          <div class="pt-4 border-t border-dashed border-gray-200"><h3 class="font-bold text-gray-800 mb-3 text-[13px] flex items-center gap-2"><span class="text-yellow-500">🏆</span> Top Repayment Rate (RR)</h3><div id="best_rr" class="space-y-3"></div></div>
        </div>
        <div class="space-y-5">
            <h3 class="font-bold text-gray-800 mb-3 text-[13px] flex items-center gap-2"><span class="text-teal-500">🎉</span> NPL Membaik (Penurunan)</h3>
            <div id="best_npl_turun" class="space-y-3"></div>
        </div>
        <div class="bg-[#1e293b] p-5 rounded-2xl shadow-md h-fit border border-gray-700">
           <h3 class="font-bold text-yellow-300 mb-4 text-lg border-b border-gray-600 pb-3 flex items-center gap-2"><span class="text-2xl">💡</span> Key Insights</h3>
           <div id="dynamic_insights" class="space-y-4 text-sm text-gray-300 font-medium"></div>
        </div>
      </div>
    </div>

    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 mt-8">
      <div class="flex items-center gap-3 mb-6 border-b border-gray-100 pb-4">
        <div class="bg-red-100 p-2 rounded-lg"><span class="text-3xl">🚨</span></div>
        <div>
          <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Kredit Non Perform</h2>
          <p class="text-sm text-gray-500 font-medium">Peringatan Kinerja & Cabang Terburuk</p>
        </div>
      </div>
      <div class="grid md:grid-cols-3 gap-6">
        <div><h3 class="font-bold text-gray-800 mb-3 text-[13px] flex items-center gap-2"><span class="text-red-500">🚨</span> Top NPL Terburuk (Highest)</h3><div id="list_npl_top" class="space-y-3"></div></div>
        <div><h3 class="font-bold text-gray-800 mb-3 text-[13px] flex items-center gap-2"><span class="text-orange-500">⚠️</span> NPL Memburuk (Naik)</h3><div id="list_npl_naik" class="space-y-3"></div></div>
        <div><h3 class="font-bold text-gray-800 mb-3 text-[13px] flex items-center gap-2"><span class="text-gray-500">📉</span> Bottom Realisasi Cabang</h3><div id="list_realisasi_bottom" class="space-y-3"></div></div>
      </div>
    </div>

    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 mt-8">
      <div class="flex items-center gap-3 mb-6 border-b border-gray-100 pb-4">
        <div class="bg-purple-100 p-2 rounded-lg"><span class="text-3xl">💰</span></div>
        <div>
          <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Dana Pihak Ketiga (DPK)</h2>
          <p class="text-sm text-gray-500 font-medium">Rekapitulasi Deposito & Tabungan</p>
        </div>
      </div>
      <div class="space-y-8">
        <div>
          <h3 class="font-extrabold text-gray-800 mb-4 tracking-tight flex items-center gap-2 text-lg"><span>🏦</span> Deposito</h3>
          <div class="grid md:grid-cols-4 gap-6">
            <div><h3 class="font-bold text-gray-700 mb-3 text-xs uppercase tracking-wider">Top Saldo Deposito</h3><div id="list_dep_saldo_top" class="space-y-2.5"></div></div>
            <div><h3 class="font-bold text-gray-700 mb-3 text-xs uppercase tracking-wider">Bottom Saldo Deposito</h3><div id="list_dep_saldo_bot" class="space-y-2.5"></div></div>
            <div><h3 class="font-bold text-green-700 mb-3 text-xs uppercase tracking-wider">Deposito Baru Masuk</h3><div id="list_dep_baru" class="space-y-2.5"></div></div>
            <div><h3 class="font-bold text-red-700 mb-3 text-xs uppercase tracking-wider">Deposito Cair</h3><div id="list_dep_cair" class="space-y-2.5"></div></div>
          </div>
        </div>
        <div class="border-t border-dashed border-gray-200"></div>
        <div>
          <h3 class="font-extrabold text-gray-800 mb-4 tracking-tight flex items-center gap-2 text-lg"><span>💳</span> Tabungan</h3>
          <div class="grid md:grid-cols-4 gap-6">
            <div><h3 class="font-bold text-gray-700 mb-3 text-xs uppercase tracking-wider">Top Saldo Tabungan</h3><div id="list_tab_saldo_top" class="space-y-2.5"></div></div>
            <div><h3 class="font-bold text-gray-700 mb-3 text-xs uppercase tracking-wider">Bottom Saldo Tabungan</h3><div id="list_tab_saldo_bot" class="space-y-2.5"></div></div>
            <div><h3 class="font-bold text-blue-700 mb-3 text-xs uppercase tracking-wider">Tabungan Baru Masuk</h3><div id="list_tab_baru" class="space-y-2.5"></div></div>
            <div><h3 class="font-bold text-red-700 mb-3 text-xs uppercase tracking-wider">Tabungan Cair</h3><div id="list_tab_cair" class="space-y-2.5"></div></div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<style>
  .bar-fill { transition: height 1s cubic-bezier(0.4, 0, 0.2, 1), width 1s ease-in-out; }
  .custom-scrollbar::-webkit-scrollbar { width: 4px; }
  .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
  .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<script>
  // ==========================================
  // 1. FORMATTER HELPERS
  // ==========================================
  const nf = new Intl.NumberFormat('id-ID');
  const fmt = n => nf.format(Number(n || 0));
  
  const fmtB = n => {
    let num = Number(n||0); let absNum = Math.abs(num);
    if(absNum >= 1e12) return (num/1e12).toFixed(3) + ' T'; 
    if(absNum >= 1e9) return (num/1e9).toFixed(2) + ' M';   
    if(absNum >= 1e6) return (num/1e6).toFixed(1) + ' Jt';  
    return fmt(num);
  };
  
  const pct = x => (x == null ? '0%' : `${(+x).toFixed(2)}%`);
  
  const getDeltaHTML = (val, isPercent = false, invertGoodBad = false, useParentSize = false) => {
    let numVal = Number(val || 0);
    if(numVal === 0) return `<span class="text-gray-400 font-normal ${useParentSize ? '' : 'text-sm'}">Tetap 0</span>`;
    let isGood = invertGoodBad ? numVal < 0 : numVal > 0;
    let color = isGood ? 'text-green-600' : 'text-red-600';
    let icon = numVal > 0 ? '▲' : '▼';
    let displayVal = isPercent ? pct(Math.abs(numVal)) : fmtB(Math.abs(numVal));
    let sizeClass = useParentSize ? '' : 'text-sm';
    return `<span class="${color} font-black ${sizeClass}">${icon} ${displayVal}</span>`;
  };

  function getTodayRealtime() {
    let d = new Date();
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
  }
  
  function getYesterdayRealtime() {
    let d = new Date();
    d.setDate(d.getDate() - 1);
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
  }

  let chartTrenInstance = null;
  let chartRunoffInstance = null; 
  let initialHarianDate = null; 

  const apiCall = (url, opt={}) => (window.apiFetch ? window.apiFetch(url, opt) : fetch(url, opt));

  // ==========================================
  // 2. INIT & DATA FETCHING
  // ==========================================
  async function getLastHarianData() {
    try { const r = await apiCall('./api/date/'); const j = await r.json(); return j.data || null; } catch { return null; }
  }

  async function populateKantorOptions(userKode) {
    const optKantor = document.getElementById('filter_kantor');
    try {
      if(userKode && userKode !== '000'){
        optKantor.innerHTML = `<option value="${userKode}">${userKode}</option>`; optKantor.value = userKode; optKantor.disabled = true; return;
      }
      const res = await apiCall('./api/kode/', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({type:'kode_kantor'}) });
      const j = await res.json();
      let html = `<option value="000">Konsolidasi</option><option value="SEMARANG">Korwil Semarang</option><option value="SOLO">Korwil Solo</option><option value="BANYUMAS">Korwil Banyumas</option><option value="PEKALONGAN">Korwil Pekalongan</option>`;
      if(j.data) j.data.filter(x => x.kode_kantor !== '000').forEach(k => html += `<option value="${k.kode_kantor}">${k.kode_kantor} - ${k.nama_kantor}</option>`);
      optKantor.innerHTML = html; optKantor.disabled = false;
    } catch(e) {}
  }

  window.addEventListener('DOMContentLoaded', async () => {
    const d = await getLastHarianData(); 
    if(d) {
      document.getElementById('filter_closing').value = d.last_closing;
      document.getElementById('filter_harian').value  = d.last_created;
    } else {
      document.getElementById('filter_closing').value = '2026-02-28';
      document.getElementById('filter_harian').value = '2026-03-10';
    }

    initialHarianDate = document.getElementById('filter_harian').value;

    const user = (window.getUser && window.getUser()) || null;
    const userKode = (user?.kode ? String(user.kode).padStart(3,'0') : null);
    await populateKantorOptions(userKode);

    fetchDashboardUtama();
    fetchTrenNPL();
    fetchTrenRunoff(); 
  });

  document.getElementById('formFilterMaster').addEventListener('submit', e => {
    e.preventDefault();
    fetchDashboardUtama();
    fetchTrenNPL();
    fetchTrenRunoff(); 
  });

  document.getElementById('filter_tren').addEventListener('change', () => { fetchTrenNPL(); });
  document.getElementById('filter_tren_runoff').addEventListener('change', () => { fetchTrenRunoff(); });


  // ==========================================
  // 3. FETCH & RENDER KHUSUS TREN RUN OFF
  // ==========================================
  async function fetchTrenRunoff(isRetry = false, targetDateOverride = null) {
    const loadingChart = document.getElementById('loadingChartRunoff');
    if (!isRetry) loadingChart.classList.remove('hidden');

    let kantor = document.getElementById('filter_kantor').value;
    let currFilterDate = document.getElementById('filter_harian').value;
    
    let isDefaultDate = (currFilterDate === initialHarianDate);
    let targetRealtimeDate = isDefaultDate ? getTodayRealtime() : currFilterDate;
    
    let baseDate = targetDateOverride || targetRealtimeDate;
    document.getElementById('label_runoff_date').innerText = `Berdasarkan Tanggal: ${baseDate}`;

    const payload = { 
      type: 'tren_runoff_realisasi', 
      harian_date: baseDate,
      periode: document.getElementById('filter_tren_runoff').value 
    };

    if(kantor !== '000') { 
        if(['SEMARANG','SOLO','BANYUMAS','PEKALONGAN'].includes(kantor)) payload.korwil = kantor; 
        else payload.kode_kantor = kantor; 
    }

    try {
      let res = await apiCall('./api/dashboard/', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
      let json = await res.json();
      
      let dataToRender = Array.isArray(json.data) ? json.data : (json.data?.tren_runoff_realisasi || []);
      let isAllZero = dataToRender.length > 0 && dataToRender.every(d => d.total_realisasi === 0 && d.total_runoff === 0);

      if ((dataToRender.length === 0 || isAllZero) && !isRetry && isDefaultDate) {
          return fetchTrenRunoff(true, getYesterdayRealtime()); 
      }

      renderChartRunoff(dataToRender);
    } catch(e) { 
      renderChartRunoff([]);
    } finally {
      if (!isRetry) loadingChart.classList.add('hidden');
    }
  }

  function renderChartRunoff(dataArray) {
    const canvas = document.getElementById('canvasTrenRunoff'); if(!canvas) return; const ctx = canvas.getContext('2d');
    if(chartRunoffInstance) chartRunoffInstance.destroy();
    
    if(!dataArray || dataArray.length === 0) {
      ctx.clearRect(0, 0, canvas.width, canvas.height); ctx.font = "14px Arial"; ctx.fillStyle = "#9ca3af"; ctx.textAlign = "center";
      ctx.fillText("Data tidak tersedia", canvas.width/2, canvas.height/2); return;
    }

    const labels = dataArray.map(d => d.label); 
    const dataRealisasi = dataArray.map(d => Number(d.total_realisasi) || 0); 
    const dataRunoff = dataArray.map(d => Number(d.total_runoff) || 0); 
    const dataLunas = dataArray.map(d => Number(d.total_lunas) || 0);
    const dataNoaLunas = dataArray.map(d => Number(d.noa_lunas) || 0);
    const dataAngsuran = dataArray.map(d => Number(d.total_angsuran) || 0);
    const dataNoaAngsuran = dataArray.map(d => Number(d.noa_angsuran) || 0);
    const dataGrowth = dataArray.map(d => Number(d.growth) || 0);

    let gradReal = ctx.createLinearGradient(0, 0, 0, 300); gradReal.addColorStop(0, 'rgba(16, 185, 129, 0.2)'); gradReal.addColorStop(1, 'rgba(16, 185, 129, 0.0)');
    let gradRunoff = ctx.createLinearGradient(0, 0, 0, 300); gradRunoff.addColorStop(0, 'rgba(239, 68, 68, 0.2)'); gradRunoff.addColorStop(1, 'rgba(239, 68, 68, 0.0)');
    
    chartRunoffInstance = new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [
          { label: 'Realisasi', data: dataRealisasi, borderColor: '#10b981', backgroundColor: gradReal, borderWidth: 3, pointBackgroundColor: '#ffffff', pointBorderColor: '#10b981', pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 6, fill: true, tension: 0.4 },
          { label: 'Run Off', data: dataRunoff, borderColor: '#ef4444', backgroundColor: gradRunoff, borderWidth: 3, pointBackgroundColor: '#ffffff', pointBorderColor: '#ef4444', pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 6, fill: true, tension: 0.4 }
        ]
      },
      options: {
        responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 10, font: {family: 'sans-serif', size: 12, weight: 'bold'} } },
          tooltip: {
            backgroundColor: 'rgba(17, 24, 39, 0.95)', padding: 12, titleFont: { size: 13, family: 'sans-serif' }, bodyFont: { size: 12, family: 'sans-serif' },
            callbacks: {
              label: function(c) { return `${c.dataset.label}: Rp ${fmtB(c.raw)}`; },
              afterBody: function(c) {
                if (c.length > 0) { 
                  let idx = c[0].dataIndex;
                  let lunas = dataLunas[idx]; let noaLunas = dataNoaLunas[idx];
                  let angsuran = dataAngsuran[idx]; let noaAngsuran = dataNoaAngsuran[idx];
                  let g = dataGrowth[idx]; 
                  let lines = [];
                  lines.push('------------------------');
                  lines.push(`Detail Run Off:`);
                  lines.push(`  • Lunas: Rp ${fmtB(lunas)} (${fmt(noaLunas)} NOA)`);
                  lines.push(`  • Angsuran: Rp ${fmtB(angsuran)} (${fmt(noaAngsuran)} NOA)`);
                  lines.push('');
                  lines.push(`Growth: ${g >= 0 ? '▲ Naik' : '▼ Turun'} (Rp ${fmtB(Math.abs(g))})`);
                  return lines;
                }
              }
            }
          }
        },
        scales: { x: { grid: { display: false } }, y: { grid: { borderDash: [4,4], color: '#f3f4f6' }, ticks: { callback: function(val) { return fmtB(val); } } } }
      }
    });
  }

  // ==========================================
  // 4. FETCH API DASHBOARD UTAMA
  // ==========================================
  async function fetchTrenNPL() {
    const loadingChart = document.getElementById('loadingChartTren'); loadingChart.classList.remove('hidden');
    let kantor = document.getElementById('filter_kantor').value;
    const payload = { type: 'test tren npl', harian_date: document.getElementById('filter_harian').value, periode: document.getElementById('filter_tren').value };
    if(kantor !== '000') { if(['SEMARANG','SOLO','BANYUMAS','PEKALONGAN'].includes(kantor)) payload.korwil = kantor; else payload.kode_kantor = kantor; } else payload.kode_kantor = "000";
    try {
      const res = await apiCall('./api/dashboard/', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
      const json = await res.json(); renderChartJS(Array.isArray(json.data) ? json.data : (json.data?.tren_npl || []));
    } catch(e) {} finally { loadingChart.classList.add('hidden'); }
  }

  function renderChartJS(dataArray) {
    const canvas = document.getElementById('canvasTrenNPL'); const ctx = canvas.getContext('2d');
    if(chartTrenInstance) chartTrenInstance.destroy();
    if(!dataArray || dataArray.length === 0) {
      ctx.clearRect(0, 0, canvas.width, canvas.height); ctx.font = "14px Arial"; ctx.fillStyle = "#9ca3af"; ctx.textAlign = "center";
      ctx.fillText("Data tren tidak tersedia untuk periode ini", canvas.width/2, canvas.height/2); return;
    }
    const labels = dataArray.map(d => d.label || d.tanggal); const dataNominal = dataArray.map(d => Number(d.npl_amt)); const dataPersen = dataArray.map(d => parseFloat(Number(d.npl_persen).toFixed(2))); 
    let minVal = Math.min(...dataPersen); let maxVal = Math.max(...dataPersen); let padding = (maxVal - minVal) === 0 ? 0.5 : (maxVal - minVal) * 0.3; let yMin = Math.max(0, minVal - padding); 
    let gradient = ctx.createLinearGradient(0, 0, 0, 300); gradient.addColorStop(0, 'rgba(239, 68, 68, 0.3)'); gradient.addColorStop(1, 'rgba(239, 68, 68, 0.0)');
    chartTrenInstance = new Chart(ctx, {
      type: 'line',
      data: { labels: labels, datasets: [{ label: 'Persentase NPL', data: dataPersen, borderColor: '#ef4444', backgroundColor: gradient, borderWidth: 3, pointBackgroundColor: '#ffffff', pointBorderColor: '#ef4444', pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 6, fill: true, tension: 0.4 }] },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(17, 24, 39, 0.9)', callbacks: { label: function(context) { return `NPL: ${Number(context.parsed.y).toFixed(2)}%  (Rp ${fmtB(dataNominal[context.dataIndex])})`; } } } }, scales: { x: { grid: { display: false } }, y: { min: parseFloat(yMin.toFixed(2)), max: parseFloat((maxVal + padding).toFixed(2)), grid: { borderDash: [4, 4], color: '#f3f4f6' }, ticks: { callback: function(value) { return Number(value).toFixed(2) + '%'; } } } } }
    });
  }

  async function fetchDashboardUtama() {
    document.getElementById('loadingDash').classList.remove('hidden'); document.getElementById('contentDash').classList.add('hidden');
    
    let kantor = document.getElementById('filter_kantor').value;
    let currDate = document.getElementById('filter_harian').value;
    let targetRealisasiDate = (currDate === initialHarianDate) ? getTodayRealtime() : currDate;

    const payload = { 
      type: 'executive dashboard', 
      closing_date: document.getElementById('filter_closing').value, 
      harian_date: currDate,
      harian_date_realisasi: targetRealisasiDate
    };
    
    if(kantor !== '000') { if(['SEMARANG','SOLO','BANYUMAS','PEKALONGAN'].includes(kantor)) payload.korwil = kantor; else payload.kode_kantor = kantor; }
    
    try {
      const res = await apiCall('./api/dashboard/', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
      const json = await res.json(); if(json.status === 200 && json.data) renderDashboardUtama(json.data, kantor); else alert("Gagal memuat: " + (json.message || "Data Kosong"));
    } catch(e) {} finally { document.getElementById('loadingDash').classList.add('hidden'); document.getElementById('contentDash').classList.remove('hidden'); }
  }

  function renderDashboardUtama(d, kantorMode) {
    try {
      const rrG = d.repayment_rate?.grand_total || {}; const tNpl = d.tren_npl || []; let osCurr = rrG.os_total || 0; let osPrev = 0;
      if(tNpl.length > 0) {
        const last = tNpl[tNpl.length - 1]; const prev = tNpl.length > 1 ? tNpl[tNpl.length - 2] : last; osPrev = prev.total_kredit || 0; 
        document.getElementById('kpi_npl').textContent = `Rp ${fmtB(last.npl_amt)}`;
        document.getElementById('kpi_npl_pill').innerHTML = `<div class="inline-flex gap-1 bg-gray-100 px-2 py-1 rounded font-bold">Closing: <span class="text-gray-800">${pct(prev.npl_persen)}</span></div><div class="inline-flex gap-1 bg-red-50 text-red-700 px-2 py-1 rounded font-bold border border-red-100">Act: ${pct(last.npl_persen)}</div>${getDeltaHTML(last.npl_persen - prev.npl_persen, true, true)}`;
      }
      
      let noaOs = d.top_bottom_realisasi?.grand_total?.noa_realisasi || 0;
      document.getElementById('kpi_os').textContent = `Rp ${fmtB(osCurr)}`;
      document.getElementById('kpi_os_pill').innerHTML = `
        <div class="flex items-center gap-2">
            <div class="inline-flex gap-1 bg-gray-100 px-2 py-1 rounded font-bold">Closing: <span class="text-gray-800">Rp ${fmtB(osPrev)}</span></div>
            ${getDeltaHTML(osCurr - osPrev, false, false)}
        </div>
        
      `;

      document.getElementById('kpi_rr').textContent = pct(rrG.rr_persen_curr);
      document.getElementById('kpi_rr_pill').innerHTML = `<div class="inline-flex gap-1 bg-gray-100 px-2 py-1 rounded font-bold">Closing: <span class="text-gray-800">${pct(rrG.rr_persen_prev)}</span></div>${getDeltaHTML(rrG.delta_rr, true, false)}`;
      
      const depG = d.perkembangan_deposito?.grand_total || {}; const tabG = d.perkembangan_tabungan?.grand_total || {};
      const dpkCurr = (depG.saldo_curr||0) + (tabG.saldo_curr||0); const dpkPrev = (depG.saldo_prev||0) + (tabG.saldo_prev||0);
      document.getElementById('kpi_dpk').textContent = `Rp ${fmtB(dpkCurr)}`;
      document.getElementById('kpi_dpk_pill').innerHTML = `<div class="inline-flex gap-1 bg-gray-100 px-2 py-1 rounded font-bold">Closing: <span class="text-gray-800">Rp ${fmtB(dpkPrev)}</span></div>${getDeltaHTML(dpkCurr - dpkPrev, false, false)}`;
    } catch(e) {}

    // RENDER REALISASI BY PRODUK
    try {
        let prods = d.realisasi_by_produk?.detail_produk || [];
        renderUniversalList('box_realisasi_produk', prods, 'nama_produk', 'total_realisasi', 'noa_realisasi', 'bg-indigo-400', false, 'NOA');
    } catch(e) {}

    // RENDER KORWIL BAR DENGAN SCROLL DINAMIS
    try {
      let hideGrandTotal = (kantorMode !== '000');
      let isKorwilFilter = ['SEMARANG','SOLO','BANYUMAS','PEKALONGAN'].includes(kantorMode);

      // 🔥 Manajemen Scrollbar Dinamis
      const boxRunoff = document.getElementById('box_runoff_realisasi');
      const boxFlow = document.getElementById('box_flow_recovery');

      if (isKorwilFilter) {
          boxRunoff.style.maxHeight = '200px';
          boxRunoff.classList.add('overflow-y-auto', 'custom-scrollbar', 'pr-1');
          boxFlow.style.maxHeight = '200px';
          boxFlow.classList.add('overflow-y-auto', 'custom-scrollbar', 'pr-1');
      } else {
          boxRunoff.style.maxHeight = 'none';
          boxRunoff.classList.remove('overflow-y-auto', 'custom-scrollbar', 'pr-1');
          boxFlow.style.maxHeight = 'none';
          boxFlow.classList.remove('overflow-y-auto', 'custom-scrollbar', 'pr-1');
      }
      
      let runoffData = [...(d.runoff_vs_realisasi?.detail_korwil || [])]; 
      if(d.runoff_vs_realisasi?.grand_total && !hideGrandTotal) {
          runoffData.push(d.runoff_vs_realisasi.grand_total);
      }
      renderKorwilCompare('box_runoff_realisasi', runoffData, 'realisasi', 'total_runoff', 'bg-green-400', 'bg-red-400');
      
      let flowData = [...(d.flow_vs_recovery_npl?.detail_korwil || [])]; 
      if(d.flow_vs_recovery_npl?.grand_total && !hideGrandTotal) {
          flowData.push(d.flow_vs_recovery_npl.grand_total);
      }
      renderKorwilCompare('box_flow_recovery', flowData, 'flow_npl', 'total_recovery', 'bg-red-400', 'bg-green-400');
    } catch(e) {}

    try {
      renderUniversalList('best_realisasi', d.top_bottom_realisasi?.top_cabang, 'nama_cabang', 'total_realisasi', 'noa_realisasi', 'bg-blue-500', false, 'NOA');
      renderUniversalList('best_realisasi_ao', d.top_bottom_realisasi?.top_ao, 'nama_ao', 'total_realisasi', 'noa_realisasi', 'bg-indigo-500', false, 'NOA');
      renderUniversalList('best_npl', d.top_bottom_npl?.bottom, 'nama_cabang', 'npl_persen', 'npl_amt', 'bg-emerald-400', true, 'Rp');
      renderUniversalList('best_rr', d.repayment_rate?.top_rr, 'nama_cabang', 'rr_persen_curr', 'os_total', 'bg-green-500', true, 'Rp');
      renderUniversalList('best_npl_turun', d.kenaikan_penurunan_npl?.top_penurunan, 'nama_cabang', 'delta_npl', 'npl_persen_curr', 'bg-teal-400', true, 'NPL Now');

      const tReal = d.top_bottom_realisasi?.top_cabang[0]; const tAo = d.top_bottom_realisasi?.top_ao[0]; const tRR = d.repayment_rate?.top_rr[0]; const tNplBest = d.top_bottom_npl?.bottom[0]; const tTurun = d.kenaikan_penurunan_npl?.top_penurunan[0];
      let html = '';
      if(tReal) html += `<div class="mb-4"><span class="text-blue-400 font-bold">1. Realisasi Tertinggi:</span> <span class="text-white">${tReal.nama_cabang.replace('Kc. ','')} (${fmtB(tReal.total_realisasi)})</span></div>`;
      if(tAo) html += `<div class="mb-4"><span class="text-indigo-400 font-bold">2. AO Terbaik:</span> <span class="text-white">${tAo.nama_ao} (${fmtB(tAo.total_realisasi)})</span></div>`;
      if(tRR) html += `<div class="mb-4"><span class="text-green-400 font-bold">3. RR Terbaik:</span> <span class="text-white">${tRR.nama_cabang.replace('Kc. ','')} (${pct(tRR.rr_persen_curr)})</span></div>`;
      if(tNplBest) html += `<div class="mb-4"><span class="text-emerald-400 font-bold">4. NPL Terbaik:</span> <span class="text-white">${tNplBest.nama_cabang.replace('Kc. ','')} (${pct(tNplBest.npl_persen)})</span></div>`;
      if(tTurun) html += `<div class="mb-4"><span class="text-teal-400 font-bold">5. Penurunan NPL Terbesar:</span> <span class="text-white">${tTurun.nama_cabang.replace('Kc. ','')} (Δ ${pct(Math.abs(tTurun.delta_npl))})</span></div>`;
      document.getElementById('dynamic_insights').innerHTML = html;
    } catch(e) {}

    try {
      renderUniversalList('list_npl_top', d.top_bottom_npl?.top, 'nama_cabang', 'npl_persen', 'npl_amt', 'bg-red-500', true, 'Rp');
      renderUniversalList('list_npl_naik', d.kenaikan_penurunan_npl?.top_kenaikan, 'nama_cabang', 'delta_npl', 'npl_persen_curr', 'bg-orange-500', true, 'NPL Now');
      renderUniversalList('list_realisasi_bottom', [...(d.top_bottom_realisasi?.bottom_cabang || [])].reverse(), 'nama_cabang', 'total_realisasi', 'noa_realisasi', 'bg-orange-400', false, 'NOA');
    } catch(e) {}

    try {
      const dp = d.perkembangan_deposito || {}; const tb = d.perkembangan_tabungan || {};
      renderUniversalList('list_dep_saldo_top', dp.top_saldo, 'nama_cabang', 'saldo_curr', 'noa_curr', 'bg-yellow-500', false, 'Rek');
      renderUniversalList('list_dep_saldo_bot', [...(dp.bottom_saldo || [])].reverse(), 'nama_cabang', 'saldo_curr', 'noa_curr', 'bg-yellow-400', false, 'Rek');
      renderUniversalList('list_dep_baru', dp.top_baru, 'nama_cabang', 'saldo_baru', 'noa_tambah', 'bg-emerald-500', false, 'Rek Baru');
      renderUniversalList('list_dep_cair', dp.top_pencairan, 'nama_cabang', 'saldo_cair', 'noa_kurang', 'bg-red-400', false, 'Rek Cair');
      renderUniversalList('list_tab_saldo_top', tb.top_saldo, 'nama_cabang', 'saldo_curr', 'noa_curr', 'bg-teal-500', false, 'Rek');
      renderUniversalList('list_tab_saldo_bot', [...(tb.bottom_saldo || [])].reverse(), 'nama_cabang', 'saldo_curr', 'noa_curr', 'bg-teal-400', false, 'Rek');
      renderUniversalList('list_tab_baru', tb.top_baru, 'nama_cabang', 'saldo_baru', 'noa_tambah', 'bg-blue-500', false, 'Rek Baru');
      renderUniversalList('list_tab_cair', tb.top_pencairan, 'nama_cabang', 'saldo_cair', 'noa_kurang', 'bg-red-400', false, 'Rek Cair');
    } catch(e) {}
  }

  function renderKorwilCompare(elId, dataArray, keyA, keyB, colorA, colorB) {
    const box = document.getElementById(elId); box.innerHTML = ''; if(!dataArray || !dataArray.length) return;
    let maxVal = Math.max(...dataArray.flatMap(o => [Number(o[keyA]), Number(o[keyB])])); if(maxVal === 0) maxVal = 1;
    dataArray.forEach(k => {
      let vA = Number(k[keyA]); let vB = Number(k[keyB]); let pctA = (vA / maxVal) * 100; let pctB = (vB / maxVal) * 100;
      let titleClass = k.nama_korwil.includes("KONSOLIDASI") ? "text-gray-900 font-black" : "text-gray-700 font-bold";
      box.innerHTML += `<div class="mb-2"><div class="flex justify-between text-[11px] ${titleClass} mb-0.5"><span>${k.nama_korwil}</span></div><div class="flex flex-col gap-0.5 relative"><div class="w-full bg-gray-100 h-1.5 rounded-r-full flex relative"><div class="${colorA} h-1.5 rounded-r-full bar-fill z-10" style="width: ${pctA}%"></div><span class="absolute right-0 -top-3.5 text-[9px] text-gray-500 font-medium">${fmtB(vA)}</span></div><div class="w-full bg-gray-100 h-1.5 rounded-r-full flex relative"><div class="${colorB} h-1.5 rounded-r-full bar-fill z-10" style="width: ${pctB}%"></div><span class="absolute right-0 -bottom-3.5 text-[9px] text-gray-500 font-medium">${fmtB(vB)}</span></div></div></div>`;
    });
  }

  function renderUniversalList(elId, dataArray, nameKey, valKey, subKey, colorClass, isPercent, subLabel = 'Rp') {
    const box = document.getElementById(elId); box.innerHTML = '';
    if(!dataArray || !Array.isArray(dataArray) || dataArray.length === 0) { box.innerHTML = `<p class="text-[11px] text-gray-400 italic py-2 text-center">Tidak ada data.</p>`; return; }
    let maxVal = Math.max(...dataArray.map(o => Math.abs(Number(o[valKey]) || 0))); if(maxVal === 0) maxVal = 1;
    dataArray.forEach(item => {
      let val = Number(item[valKey] || 0); let sub = Number(item[subKey] || 0); let wPct = Math.abs((val / maxVal) * 100);
      let displayVal = isPercent ? pct(Math.abs(val)) : fmtB(Math.abs(val));
      let displaySub = subLabel === 'Rp' ? `Rp ${fmtB(sub)}` : (subLabel === 'NPL Now' ? `NPL saat ini: ${pct(sub)}` : `${fmt(sub)} ${subLabel}`);
      let name = (item[nameKey] || '-').replace(/Kc\. /gi, '');
      box.innerHTML += `<div class="mb-3 group cursor-default relative z-0"><div class="flex justify-between items-end mb-1.5 relative z-10"><div class="flex flex-col w-2/3"><span class="text-xs font-bold text-gray-800 truncate" title="${name}">${name}</span><span class="text-[10px] text-gray-500 font-medium leading-tight">${displaySub}</span></div><span class="text-xs font-black text-gray-900">${val < 0 ? '-' : ''}${displayVal}</span></div><div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden relative z-0"><div class="${colorClass} h-1.5 rounded-full bar-fill" style="width: ${Math.max(2, wPct)}%"></div></div></div>`;
    });
  }
</script>