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
    <span class="text-gray-500 font-semibold animate-pulse">Menyedot data dari database...</span>
  </div>

  <div id="contentDash" class="hidden space-y-6 overflow-x-hidden">
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 relative">
        <div class="absolute top-0 left-0 w-1.5 h-full bg-blue-500 rounded-l-2xl"></div>
        <p class="text-[11px] text-gray-500 font-bold uppercase tracking-wider mb-1">Total Baki Debet (OS)</p>
        <h3 id="kpi_os" class="text-3xl font-black text-gray-900 tracking-tight">Rp 0</h3>
        <div class="mt-3 flex flex-wrap items-center gap-2 text-sm" id="kpi_os_pill">
           </div>
      </div>
      
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 relative">
        <div class="absolute top-0 left-0 w-1.5 h-full bg-red-500 rounded-l-2xl"></div>
        <p class="text-[11px] text-gray-500 font-bold uppercase tracking-wider mb-1">Total OSC NPL</p>
        <h3 id="kpi_npl" class="text-3xl font-black text-red-600 tracking-tight">Rp 0</h3>
        <div class="mt-3 flex flex-wrap items-center gap-2 text-sm" id="kpi_npl_pill">
          </div>
      </div>
      
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 relative">
        <div class="absolute top-0 left-0 w-1.5 h-full bg-green-500 rounded-l-2xl"></div>
        <p class="text-[11px] text-gray-500 font-bold uppercase tracking-wider mb-1">Repayment Rate (RR)</p>
        <h3 id="kpi_rr" class="text-3xl font-black text-green-600 tracking-tight">0%</h3>
        <div class="mt-3 flex flex-wrap items-center gap-2 text-sm" id="kpi_rr_pill">
          </div>
      </div>
      
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 relative">
        <div class="absolute top-0 left-0 w-1.5 h-full bg-purple-500 rounded-l-2xl"></div>
        <p class="text-[11px] text-gray-500 font-bold uppercase tracking-wider mb-1">Total DPK (Depo + Tab)</p>
        <h3 id="kpi_dpk" class="text-3xl font-black text-purple-700 tracking-tight">Rp 0</h3>
        <div class="mt-3 flex flex-wrap items-center gap-2 text-sm" id="kpi_dpk_pill">
          </div>
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
                <option value="30_hari">30 Hari Terakhir</option>
                <option value="14_hari">14 Hari Terakhir</option>
                <option value="7_hari">7 Hari Terakhir</option>
            </select>
        </div>
        <div class="relative flex-grow min-h-[220px] w-full mt-2">
          <div id="loadingChartTren" class="absolute inset-0 flex justify-center items-center bg-white bg-opacity-80 z-10 hidden">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-red-500"></div>
          </div>
          <canvas id="canvasTrenNPL"></canvas>
        </div>
      </div>

      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
        <div>
          <h3 class="font-bold text-gray-800 mb-2 border-b border-gray-100 pb-2 text-sm flex items-center gap-2">
            <span>🔄</span> Realisasi vs Run Off (Korwil)
          </h3>
          <div id="box_runoff_realisasi" class="space-y-3 mb-4"></div>
        </div>
        <div>
          <h3 class="font-bold text-gray-800 mb-2 border-b border-gray-100 pb-2 text-sm flex items-center gap-2">
            <span>🛡️</span> Flow NPL vs Recovery (Korwil)
          </h3>
          <div id="box_flow_recovery" class="space-y-3"></div>
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
          <div>
            <h3 class="font-bold text-gray-800 mb-3 text-[13px] flex items-center gap-2"><span class="text-blue-500">📈</span> Top Realisasi Cabang</h3>
            <div id="best_realisasi" class="space-y-3"></div>
          </div>
          <div class="pt-4 border-t border-dashed border-gray-200">
            <h3 class="font-bold text-gray-800 mb-3 text-[13px] flex items-center gap-2"><span class="text-orange-500">🥇</span> Top Realisasi AO</h3>
            <div id="best_realisasi_ao" class="space-y-3"></div>
          </div>
        </div>

        <div class="space-y-5">
          <div>
            <h3 class="font-bold text-gray-800 mb-3 text-[13px] flex items-center gap-2"><span class="text-red-500">🛡️</span> Top NPL Terendah (Terbaik)</h3>
            <div id="best_npl" class="space-y-3"></div>
          </div>
          <div class="pt-4 border-t border-dashed border-gray-200">
            <h3 class="font-bold text-gray-800 mb-3 text-[13px] flex items-center gap-2"><span class="text-yellow-500">🏆</span> Top Repayment Rate (RR)</h3>
            <div id="best_rr" class="space-y-3"></div>
          </div>
        </div>

        <div class="space-y-5">
            <h3 class="font-bold text-gray-800 mb-3 text-[13px] flex items-center gap-2"><span class="text-teal-500">🎉</span> NPL Membaik (Penurunan)</h3>
            <div id="best_npl_turun" class="space-y-3"></div>
        </div>

        <div class="bg-[#1e293b] p-5 rounded-2xl shadow-md h-fit border border-gray-700">
           <h3 class="font-bold text-yellow-300 mb-4 text-lg border-b border-gray-600 pb-3 flex items-center gap-2">
              <span class="text-2xl">💡</span> Key Insights
           </h3>
           <div id="dynamic_insights" class="space-y-4 text-sm text-gray-300 font-medium">
              </div>
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
        <div>
          <h3 class="font-bold text-gray-800 mb-3 text-[13px] flex items-center gap-2"><span class="text-red-500">🚨</span> Top NPL Terburuk (Highest)</h3>
          <div id="list_npl_top" class="space-y-3"></div>
        </div>
        <div>
          <h3 class="font-bold text-gray-800 mb-3 text-[13px] flex items-center gap-2"><span class="text-orange-500">⚠️</span> NPL Memburuk (Naik)</h3>
          <div id="list_npl_naik" class="space-y-3"></div>
        </div>
        <div>
          <h3 class="font-bold text-gray-800 mb-3 text-[13px] flex items-center gap-2"><span class="text-gray-500">📉</span> Bottom Realisasi Cabang</h3>
          <div id="list_realisasi_bottom" class="space-y-3"></div>
        </div>
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
          <h3 class="font-extrabold text-gray-800 mb-4 tracking-tight flex items-center gap-2 text-lg">
            <span>🏦</span> Deposito
          </h3>
          <div class="grid md:grid-cols-4 gap-6">
            <div>
              <h3 class="font-bold text-gray-700 mb-3 text-xs uppercase tracking-wider">Top Saldo Deposito</h3>
              <div id="list_dep_saldo_top" class="space-y-2.5"></div>
            </div>
            <div>
              <h3 class="font-bold text-gray-700 mb-3 text-xs uppercase tracking-wider">Bottom Saldo Deposito</h3>
              <div id="list_dep_saldo_bot" class="space-y-2.5"></div>
            </div>
            <div>
              <h3 class="font-bold text-green-700 mb-3 text-xs uppercase tracking-wider">Deposito Baru Masuk</h3>
              <div id="list_dep_baru" class="space-y-2.5"></div>
            </div>
            <div>
              <h3 class="font-bold text-red-700 mb-3 text-xs uppercase tracking-wider">Deposito Cair</h3>
              <div id="list_dep_cair" class="space-y-2.5"></div>
            </div>
          </div>
        </div>

        <div class="border-t border-dashed border-gray-200"></div>

        <div>
          <h3 class="font-extrabold text-gray-800 mb-4 tracking-tight flex items-center gap-2 text-lg">
            <span>💳</span> Tabungan
          </h3>
          <div class="grid md:grid-cols-4 gap-6">
            <div>
              <h3 class="font-bold text-gray-700 mb-3 text-xs uppercase tracking-wider">Top Saldo Tabungan</h3>
              <div id="list_tab_saldo_top" class="space-y-2.5"></div>
            </div>
            <div>
              <h3 class="font-bold text-gray-700 mb-3 text-xs uppercase tracking-wider">Bottom Saldo Tabungan</h3>
              <div id="list_tab_saldo_bot" class="space-y-2.5"></div>
            </div>
            <div>
              <h3 class="font-bold text-blue-700 mb-3 text-xs uppercase tracking-wider">Tabungan Baru Masuk</h3>
              <div id="list_tab_baru" class="space-y-2.5"></div>
            </div>
            <div>
              <h3 class="font-bold text-red-700 mb-3 text-xs uppercase tracking-wider">Tabungan Cair</h3>
              <div id="list_tab_cair" class="space-y-2.5"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<style>
  .bar-fill { transition: height 1s cubic-bezier(0.4, 0, 0.2, 1), width 1s ease-in-out; }
</style>

<script>
  // ==========================================
  // 1. FORMATTER HELPERS
  // ==========================================
  const nf = new Intl.NumberFormat('id-ID');
  const fmt = n => nf.format(Number(n || 0));
  
  // Format Angka: T=3 digit koma, M=2 digit koma
  const fmtB = n => {
    let num = Number(n||0);
    let absNum = Math.abs(num);
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

  let chartTrenInstance = null;

  // ==========================================
  // 2. INIT & DATA FETCHING OTOMATIS
  // ==========================================
  
  // Helper fungsi untuk fetch (menggunakan apiFetch bawaan atau standar fetch)
  const apiCall = (url, opt={}) => (window.apiFetch ? window.apiFetch(url, opt) : fetch(url, opt));

  async function getLastHarianData() {
    try {
      const r = await apiCall('./api/date/'); 
      const j = await r.json(); 
      return j.data || null;
    } catch { return null; }
  }

  async function populateKantorOptions(userKode) {
    const optKantor = document.getElementById('filter_kantor');
    try {
      // Jika user LOGIN sebagai CABANG (bukan 000/Pusat) -> Lock dropdown
      if(userKode && userKode !== '000'){
        optKantor.innerHTML = `<option value="${userKode}">${userKode}</option>`;
        optKantor.value = userKode; 
        optKantor.disabled = true; 
        return;
      }

      // Jika Pusat, ambil list dari API
      const res = await apiCall('./api/kode/', { 
        method: 'POST', 
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({type:'kode_kantor'}) 
      });
      const j = await res.json();
      
      let html = `
        <option value="000">Konsolidasi (Nasional)</option>
        <option value="SEMARANG">Korwil Semarang</option>
        <option value="SOLO">Korwil Solo</option>
        <option value="BANYUMAS">Korwil Banyumas</option>
        <option value="PEKALONGAN">Korwil Pekalongan</option>
      `;

      if(j.data) {
        j.data.filter(x => x.kode_kantor !== '000').forEach(k => {
          html += `<option value="${k.kode_kantor}">${k.kode_kantor} - ${k.nama_kantor}</option>`;
        });
      }
      optKantor.innerHTML = html;
      optKantor.disabled = false;
    } catch(e) { 
      console.log('Gagal load cabang'); 
    }
  }

  window.addEventListener('DOMContentLoaded', async () => {
    // 1. Dapatkan Data Tanggal Terakhir
    const d = await getLastHarianData(); 
    if(d) {
      document.getElementById('filter_closing').value = d.last_closing;
      document.getElementById('filter_harian').value  = d.last_created;
    } else {
      document.getElementById('filter_closing').value = '2026-02-28';
      document.getElementById('filter_harian').value = '2026-03-10';
    }

    // 2. Cek User Login
    const user = (window.getUser && window.getUser()) || null;
    const userKode = (user?.kode ? String(user.kode).padStart(3,'0') : null);
    
    // 3. Render Option Cabang sesuai User
    await populateKantorOptions(userKode);

    // 4. Langsung Tarik Data Dashboard & Chart
    fetchDashboardUtama();
    fetchTrenNPL();
  });

  document.getElementById('formFilterMaster').addEventListener('submit', e => {
    e.preventDefault();
    fetchDashboardUtama();
    fetchTrenNPL();
  });

  document.getElementById('filter_tren').addEventListener('change', () => {
    fetchTrenNPL();
  });

  // ==========================================
  // 3. RENDER CHART.JS 
  // ==========================================
  function renderChartJS(dataArray) {
    const canvas = document.getElementById('canvasTrenNPL');
    const ctx = canvas.getContext('2d');
    
    if(chartTrenInstance) {
      chartTrenInstance.destroy();
    }

    if(!dataArray || dataArray.length === 0) {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.font = "14px Arial";
      ctx.fillStyle = "#9ca3af";
      ctx.textAlign = "center";
      ctx.fillText("Data tren tidak tersedia untuk periode ini", canvas.width/2, canvas.height/2);
      return;
    }

    const labels = dataArray.map(d => d.label || d.tanggal);
    const dataNominal = dataArray.map(d => Number(d.npl_amt));
    const dataPersen = dataArray.map(d => parseFloat(Number(d.npl_persen).toFixed(2))); 

    let minVal = Math.min(...dataPersen);
    let maxVal = Math.max(...dataPersen);
    let selisih = maxVal - minVal;
    let padding = selisih === 0 ? 0.5 : selisih * 0.3; 
    let yMin = Math.max(0, minVal - padding); 

    let gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(239, 68, 68, 0.3)'); 
    gradient.addColorStop(1, 'rgba(239, 68, 68, 0.0)');

    chartTrenInstance = new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Persentase NPL',
          data: dataPersen,
          borderColor: '#ef4444', 
          backgroundColor: gradient,   
          borderWidth: 3,
          pointBackgroundColor: '#ffffff',
          pointBorderColor: '#ef4444',
          pointBorderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6,
          fill: true,
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: 'rgba(17, 24, 39, 0.9)',
            callbacks: {
              label: function(context) {
                let pctVal = Number(context.parsed.y).toFixed(2); 
                let nomVal = dataNominal[context.dataIndex]; 
                return `NPL: ${pctVal}%  (Rp ${fmtB(nomVal)})`; 
              }
            }
          }
        },
        scales: {
          x: { grid: { display: false } },
          y: { 
            min: parseFloat(yMin.toFixed(2)), 
            max: parseFloat((maxVal + padding).toFixed(2)),
            grid: { borderDash: [4, 4], color: '#f3f4f6' },
            ticks: {
              callback: function(value) { return Number(value).toFixed(2) + '%'; } 
            }
          }
        }
      }
    });
  }

  // ==========================================
  // 4. FETCH API TREN NPL
  // ==========================================
  async function fetchTrenNPL() {
    const loadingChart = document.getElementById('loadingChartTren');
    loadingChart.classList.remove('hidden');

    let kantor = document.getElementById('filter_kantor').value;
    const payload = { 
      type: 'test tren npl', 
      harian_date: document.getElementById('filter_harian').value,
      periode: document.getElementById('filter_tren').value 
    };

    if(kantor !== '000') {
      if(['SEMARANG','SOLO','BANYUMAS','PEKALONGAN'].includes(kantor)) payload.korwil = kantor;
      else payload.kode_kantor = kantor;
    } else {
      payload.kode_kantor = "000";
    }

    try {
      const res = await apiCall('./api/dashboard/', { 
        method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
      });
      const json = await res.json();
      const dataTren = Array.isArray(json.data) ? json.data : (json.data?.tren_npl || []);
      renderChartJS(dataTren);
    } catch(e) {} finally {
      loadingChart.classList.add('hidden');
    }
  }

  // ==========================================
  // 5. FETCH API DASHBOARD UTAMA
  // ==========================================
  async function fetchDashboardUtama() {
    document.getElementById('loadingDash').classList.remove('hidden');
    document.getElementById('contentDash').classList.add('hidden');

    const payload = { 
      type: 'executive dashboard', 
      closing_date: document.getElementById('filter_closing').value, 
      harian_date: document.getElementById('filter_harian').value
    };
    
    let kantor = document.getElementById('filter_kantor').value;
    if(kantor !== '000') {
      if(['SEMARANG','SOLO','BANYUMAS','PEKALONGAN'].includes(kantor)) payload.korwil = kantor;
      else payload.kode_kantor = kantor;
    }

    try {
      const res = await apiCall('./api/dashboard/', { 
        method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
      });
      const json = await res.json();
      
      if(json.status === 200 && json.data) {
        renderDashboardUtama(json.data);
      } else {
        alert("Gagal memuat: " + (json.message || "Data Kosong"));
      }
    } catch(e) {} finally {
      document.getElementById('loadingDash').classList.add('hidden');
      document.getElementById('contentDash').classList.remove('hidden');
    }
  }

  // ==========================================
  // 6. RENDER DASHBOARD UTAMA
  // ==========================================
  function renderDashboardUtama(d) {
    try {
      const rrG = d.repayment_rate?.grand_total || {};
      const tNpl = d.tren_npl || [];
      let osCurr = rrG.os_total || 0;
      let osPrev = 0;
      
      if(tNpl.length > 0) {
        const last = tNpl[tNpl.length - 1];
        const prev = tNpl.length > 1 ? tNpl[tNpl.length - 2] : last;
        osPrev = prev.total_kredit || 0; 

        document.getElementById('kpi_npl').textContent = `Rp ${fmtB(last.npl_amt)}`;
        let deltaNpl = getDeltaHTML(last.npl_persen - prev.npl_persen, true, true);
        
        document.getElementById('kpi_npl_pill').innerHTML = `
          <div class="inline-flex gap-1 bg-gray-100 px-2 py-1 rounded font-bold">Closing: <span class="text-gray-800">${pct(prev.npl_persen)}</span></div>
          <div class="inline-flex gap-1 bg-red-50 text-red-700 px-2 py-1 rounded font-bold border border-red-100">Act: ${pct(last.npl_persen)}</div>
          ${deltaNpl}
        `;
      }

      // 🔥 FIX: KPI OS - TANPA NOA DI SINI. HANYA CLOSING & SELISIH
      document.getElementById('kpi_os').textContent = `Rp ${fmtB(osCurr)}`;
      let deltaOs = osCurr - osPrev;
      let deltaOsHTML = getDeltaHTML(deltaOs, false, false);

      document.getElementById('kpi_os_pill').innerHTML = `
        <div class="inline-flex gap-1 bg-gray-100 px-2 py-1 rounded font-bold">Closing: <span class="text-gray-800">Rp ${fmtB(osPrev)}</span></div>
        ${deltaOsHTML}
      `;

      // KPI RR 
      document.getElementById('kpi_rr').textContent = pct(rrG.rr_persen_curr);
      let deltaRR = getDeltaHTML(rrG.delta_rr, true, false);
      
      document.getElementById('kpi_rr_pill').innerHTML = `
        <div class="inline-flex gap-1 bg-gray-100 px-2 py-1 rounded font-bold">Closing: <span class="text-gray-800">${pct(rrG.rr_persen_prev)}</span></div>
        ${deltaRR}
      `;

      // KPI DPK 
      const depG = d.perkembangan_deposito?.grand_total || {};
      const tabG = d.perkembangan_tabungan?.grand_total || {};
      const dpkCurr = (depG.saldo_curr||0) + (tabG.saldo_curr||0);
      const dpkPrev = (depG.saldo_prev||0) + (tabG.saldo_prev||0);
      const deltaDpk = dpkCurr - dpkPrev;

      document.getElementById('kpi_dpk').textContent = `Rp ${fmtB(dpkCurr)}`;
      let deltaDpkHTML = getDeltaHTML(deltaDpk, false, false);
      
      document.getElementById('kpi_dpk_pill').innerHTML = `
        <div class="inline-flex gap-1 bg-gray-100 px-2 py-1 rounded font-bold">Closing: <span class="text-gray-800">Rp ${fmtB(dpkPrev)}</span></div>
        ${deltaDpkHTML}
      `;
      
    } catch(e) {}

    // --- B. MINI BAR KORWIL ---
    try {
      let runoffData = [...(d.runoff_vs_realisasi?.detail_korwil || [])];
      if(d.runoff_vs_realisasi?.grand_total) {
        runoffData.push(d.runoff_vs_realisasi.grand_total);
      }
      renderKorwilCompare('box_runoff_realisasi', runoffData, 'realisasi', 'total_runoff', 'bg-blue-400', 'bg-orange-400');
      
      let flowData = [...(d.flow_vs_recovery_npl?.detail_korwil || [])];
      if(d.flow_vs_recovery_npl?.grand_total) {
        flowData.push(d.flow_vs_recovery_npl.grand_total);
      }
      renderKorwilCompare('box_flow_recovery', flowData, 'flow_npl', 'total_recovery', 'bg-red-400', 'bg-green-400');
    } catch(e) {}

    // --- C. SECTION 5 BEST PERFORMANCE ---
    try {
      const topRealData = d.top_bottom_realisasi?.top_cabang || [];
      const topAOData = d.top_bottom_realisasi?.top_ao || []; 
      const topNPLData = d.top_bottom_npl?.bottom || []; 
      const topRRData = d.repayment_rate?.top_rr || [];
      const topTurunData = d.kenaikan_penurunan_npl?.top_penurunan || [];

      renderUniversalList('best_realisasi', topRealData, 'nama_cabang', 'total_realisasi', 'noa_realisasi', 'bg-blue-500', false, 'NOA');
      renderUniversalList('best_realisasi_ao', topAOData, 'nama_ao', 'total_realisasi', 'noa_realisasi', 'bg-indigo-500', false, 'NOA');
      renderUniversalList('best_npl', topNPLData, 'nama_cabang', 'npl_persen', 'npl_amt', 'bg-emerald-400', true, 'Rp');
      renderUniversalList('best_rr', topRRData, 'nama_cabang', 'rr_persen_curr', 'os_total', 'bg-green-500', true, 'Rp');
      renderUniversalList('best_npl_turun', topTurunData, 'nama_cabang', 'delta_npl', 'npl_persen_curr', 'bg-teal-400', true, 'NPL Now');

      const tReal = topRealData[0];
      const tAo   = topAOData[0];
      const tRR   = topRRData[0];
      const tNplBest = topNPLData[0]; 
      const tTurun= topTurunData[0];

      let insightsHTML = '';
      if(tReal) {
        insightsHTML += `<div class="mb-4"><span class="text-blue-400 font-bold">1. Realisasi Tertinggi:</span> <span class="text-white">${tReal.nama_cabang.replace('Kc. ','')} (${fmtB(tReal.total_realisasi)})</span></div>`;
      }
      if(tAo) {
        insightsHTML += `<div class="mb-4"><span class="text-indigo-400 font-bold">2. AO Terbaik:</span> <span class="text-white">${tAo.nama_ao} (${fmtB(tAo.total_realisasi)})</span></div>`;
      }
      if(tRR) {
        insightsHTML += `<div class="mb-4"><span class="text-green-400 font-bold">3. RR Terbaik:</span> <span class="text-white">${tRR.nama_cabang.replace('Kc. ','')} (${pct(tRR.rr_persen_curr)})</span></div>`;
      }
      if(tNplBest) {
        insightsHTML += `<div class="mb-4"><span class="text-emerald-400 font-bold">4. Kualitas Kredit Terbaik:</span> <span class="text-white">${tNplBest.nama_cabang.replace('Kc. ','')} (${pct(tNplBest.npl_persen)})</span></div>`;
      }
      if(tTurun) {
        insightsHTML += `<div class="mb-4"><span class="text-teal-400 font-bold">5. Penurunan NPL Terbesar:</span> <span class="text-white">${tTurun.nama_cabang.replace('Kc. ','')} (Δ ${pct(Math.abs(tTurun.delta_npl))})</span></div>`;
      }
      document.getElementById('dynamic_insights').innerHTML = insightsHTML;

    } catch(e) {}

    // --- D. DALAM PERHATIAN (NPL) ---
    try {
      renderUniversalList('list_npl_top', d.top_bottom_npl?.top, 'nama_cabang', 'npl_persen', 'npl_amt', 'bg-red-500', true, 'Rp');
      renderUniversalList('list_npl_naik', d.kenaikan_penurunan_npl?.top_kenaikan, 'nama_cabang', 'delta_npl', 'npl_persen_curr', 'bg-orange-500', true, 'NPL Now');
      
      let botRealArr = [...(d.top_bottom_realisasi?.bottom_cabang || [])].reverse();
      renderUniversalList('list_realisasi_bottom', botRealArr, 'nama_cabang', 'total_realisasi', 'noa_realisasi', 'bg-orange-400', false, 'NOA');
    } catch(e) {}

    // --- E. DPK ---
    try {
      const dp = d.perkembangan_deposito || {};
      renderUniversalList('list_dep_saldo_top', dp.top_saldo, 'nama_cabang', 'saldo_curr', 'noa_curr', 'bg-yellow-500', false, 'Rek');
      let botDepSaldo = [...(dp.bottom_saldo || [])].reverse();
      renderUniversalList('list_dep_saldo_bot', botDepSaldo, 'nama_cabang', 'saldo_curr', 'noa_curr', 'bg-yellow-400', false, 'Rek');
      renderUniversalList('list_dep_baru', dp.top_baru, 'nama_cabang', 'saldo_baru', 'noa_tambah', 'bg-emerald-500', false, 'Rek Baru');
      renderUniversalList('list_dep_cair', dp.top_pencairan, 'nama_cabang', 'saldo_cair', 'noa_kurang', 'bg-red-400', false, 'Rek Cair');
      
      const tb = d.perkembangan_tabungan || {};
      renderUniversalList('list_tab_saldo_top', tb.top_saldo, 'nama_cabang', 'saldo_curr', 'noa_curr', 'bg-teal-500', false, 'Rek');
      let botTabSaldo = [...(tb.bottom_saldo || [])].reverse();
      renderUniversalList('list_tab_saldo_bot', botTabSaldo, 'nama_cabang', 'saldo_curr', 'noa_curr', 'bg-teal-400', false, 'Rek');
      renderUniversalList('list_tab_baru', tb.top_baru, 'nama_cabang', 'saldo_baru', 'noa_tambah', 'bg-blue-500', false, 'Rek Baru');
      renderUniversalList('list_tab_cair', tb.top_pencairan, 'nama_cabang', 'saldo_cair', 'noa_kurang', 'bg-red-400', false, 'Rek Cair');
    } catch(e) {}
  }

  function renderKorwilCompare(elId, dataArray, keyA, keyB, colorA, colorB) {
    const box = document.getElementById(elId);
    box.innerHTML = '';
    if(!dataArray || !dataArray.length) return;
    
    let maxVal = Math.max(...dataArray.flatMap(o => [Number(o[keyA]), Number(o[keyB])]));
    if(maxVal === 0) maxVal = 1;

    dataArray.forEach(k => {
      let vA = Number(k[keyA]); let vB = Number(k[keyB]);
      let pctA = (vA / maxVal) * 100; let pctB = (vB / maxVal) * 100;
      
      let isKonsol = k.nama_korwil.includes("KONSOLIDASI");
      let titleClass = isKonsol ? "text-gray-900 font-black" : "text-gray-700 font-bold";
      
      box.innerHTML += `
        <div class="mb-2">
          <div class="flex justify-between text-[11px] ${titleClass} mb-0.5">
            <span>${k.nama_korwil}</span>
          </div>
          <div class="flex flex-col gap-0.5 relative">
            <div class="w-full bg-gray-100 h-1.5 rounded-r-full flex relative">
              <div class="${colorA} h-1.5 rounded-r-full bar-fill z-10" style="width: ${pctA}%"></div>
              <span class="absolute right-0 -top-3.5 text-[9px] text-gray-500 font-medium">${fmtB(vA)}</span>
            </div>
            <div class="w-full bg-gray-100 h-1.5 rounded-r-full flex relative">
              <div class="${colorB} h-1.5 rounded-r-full bar-fill z-10" style="width: ${pctB}%"></div>
              <span class="absolute right-0 -bottom-3.5 text-[9px] text-gray-500 font-medium">${fmtB(vB)}</span>
            </div>
          </div>
        </div>
      `;
    });
  }

  function renderUniversalList(elId, dataArray, nameKey, valKey, subKey, colorClass, isPercent, subLabel = 'Rp') {
    const box = document.getElementById(elId);
    box.innerHTML = '';
    
    if(!dataArray || !Array.isArray(dataArray) || dataArray.length === 0) {
      box.innerHTML = `<p class="text-[11px] text-gray-400 italic py-2 text-center">Tidak ada data.</p>`;
      return;
    }

    let maxVal = Math.max(...dataArray.map(o => Math.abs(Number(o[valKey]) || 0)));
    if(maxVal === 0) maxVal = 1;

    dataArray.forEach(item => {
      let val = Number(item[valKey] || 0); let sub = Number(item[subKey] || 0);
      let wPct = Math.abs((val / maxVal) * 100);
      
      let displayVal = isPercent ? pct(Math.abs(val)) : fmtB(Math.abs(val));
      
      let displaySub = '';
      if(subLabel === 'Rp') displaySub = `Rp ${fmtB(sub)}`;
      else if(subLabel === 'NPL Now') displaySub = `NPL saat ini: ${pct(sub)}`;
      else displaySub = `${fmt(sub)} ${subLabel}`;

      let name = (item[nameKey] || '-').replace(/Kc\. /gi, '');
      
      box.innerHTML += `
        <div class="mb-3 group cursor-default relative z-0">
          <div class="flex justify-between items-end mb-1.5 relative z-10">
            <div class="flex flex-col w-2/3">
              <span class="text-xs font-bold text-gray-800 truncate" title="${name}">${name}</span>
              <span class="text-[10px] text-gray-500 font-medium leading-tight">${displaySub}</span>
            </div>
            <span class="text-xs font-black text-gray-900">${val < 0 ? '-' : ''}${displayVal}</span>
          </div>
          <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden relative z-0">
            <div class="${colorClass} h-1.5 rounded-full bar-fill" style="width: ${Math.max(2, wPct)}%"></div>
          </div>
        </div>
      `;
    });
  }
</script>