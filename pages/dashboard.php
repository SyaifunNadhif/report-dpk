<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="max-w-[1400px] mx-auto px-4 py-6 bg-gray-50 min-h-screen font-sans">
  <div class="flex flex-col md:flex-row justify-between items-end mb-6">
    <div>
      <h1 class="text-2xl md:text-3xl font-bold text-gray-800 tracking-tight">📊 Executive Dashboard</h1>
      <p class="text-sm text-gray-500 mt-1">Pusat Komando Portofolio & Kinerja Bisnis</p>
    </div>

    <form id="formFilterMaster" class="flex flex-wrap items-end gap-3 mt-4 md:mt-0 bg-white p-3 rounded-xl shadow-sm border border-gray-200">
      <div class="flex flex-col">
        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Closing M-1</label>
        <input type="date" id="filter_closing" class="border-b-2 border-transparent hover:border-gray-300 px-1 py-1 text-sm outline-none focus:border-blue-500 transition-colors">
      </div>
      <div class="flex flex-col">
        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Harian/Actual</label>
        <input type="date" id="filter_harian" class="border-b-2 border-transparent hover:border-gray-300 px-1 py-1 text-sm outline-none focus:border-blue-500 transition-colors">
      </div>
      <div class="flex flex-col">
        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Area/Cabang</label>
        <select id="filter_kantor" class="border-b-2 border-transparent hover:border-gray-300 px-1 py-1 text-sm min-w-[180px] outline-none focus:border-blue-500 bg-transparent transition-colors font-semibold text-gray-700">
          <option value="000">Konsolidasi</option>
          <option value="SEMARANG">Korwil Semarang</option>
          <option value="SOLO">Korwil Solo</option>
          <option value="BANYUMAS">Korwil Banyumas</option>
          <option value="PEKALONGAN">Korwil Pekalongan</option>
        </select>
      </div>
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-bold transition-all flex items-center gap-2 shadow-md hover:shadow-lg transform active:scale-95">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
        Tampilkan
      </button>
    </form>
  </div>

  <div id="loadingDash" class="hidden flex flex-col justify-center items-center py-32">
    <div class="animate-spin rounded-full h-14 w-14 border-t-4 border-b-4 border-blue-600 mb-4"></div>
    <span class="text-gray-500 font-semibold animate-pulse">Menyedot jutaan data dari database...</span>
  </div>

  <div id="contentDash" class="hidden space-y-6">
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1 h-full bg-blue-500"></div>
        <p class="text-xs text-gray-500 font-bold uppercase tracking-wide">Total Baki Debet (OS)</p>
        <h3 id="kpi_os" class="text-2xl font-black text-gray-800 mt-1">Rp 0</h3>
        <p id="kpi_os_noa" class="text-xs mt-1 font-semibold text-blue-600 bg-blue-50 inline-block px-2 py-0.5 rounded">0 NOA</p>
      </div>
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1 h-full bg-red-500"></div>
        <p class="text-xs text-gray-500 font-bold uppercase tracking-wide">Total OSC NPL</p>
        <h3 id="kpi_npl" class="text-2xl font-black text-red-600 mt-1">Rp 0</h3>
        <div class="flex justify-between items-center mt-1">
          <p id="kpi_npl_pct" class="text-sm font-bold bg-red-100 text-red-700 px-2 py-0.5 rounded">0%</p>
          <p id="kpi_npl_delta" class="text-xs font-bold">Δ 0%</p>
        </div>
      </div>
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1 h-full bg-green-500"></div>
        <p class="text-xs text-gray-500 font-bold uppercase tracking-wide">Repayment Rate (RR)</p>
        <h3 id="kpi_rr" class="text-2xl font-black text-green-600 mt-1">0%</h3>
        <p id="kpi_rr_delta" class="text-xs mt-1 font-bold">Δ 0%</p>
      </div>
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1 h-full bg-purple-500"></div>
        <p class="text-xs text-gray-500 font-bold uppercase tracking-wide">Total DPK (Depo + Tab)</p>
        <h3 id="kpi_dpk" class="text-2xl font-black text-purple-700 mt-1">Rp 0</h3>
        <div class="flex justify-between items-center mt-1">
          <p id="kpi_dpk_noa" class="text-xs font-semibold text-purple-600 bg-purple-50 px-2 py-0.5 rounded">0 NOA</p>
          <p id="kpi_dpk_delta" class="text-[11px] font-bold">Δ Rp 0</p>
        </div>
      </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-4">
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 lg:col-span-2 flex flex-col">
        <div class="flex justify-between items-center mb-2 border-b pb-3">
          <h3 class="font-bold text-gray-800 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" /></svg>
            Tren Pergerakan NPL (%)
          </h3>
            <select id="filter_tren" class="border border-gray-300 rounded-md px-2 py-1 text-xs font-semibold text-gray-700 outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer bg-gray-50">
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
          <h3 class="font-bold text-gray-800 mb-2 border-b pb-2 text-sm">🔄 Run Off vs Realisasi (Korwil)</h3>
          <div id="box_runoff_realisasi" class="space-y-3 mb-4"></div>
        </div>
        <div>
          <h3 class="font-bold text-gray-800 mb-2 border-b pb-2 text-sm">🛡️ Flow NPL vs Recovery (Korwil)</h3>
          <div id="box_flow_recovery" class="space-y-3"></div>
        </div>
      </div>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <h3 class="font-bold text-gray-800 mb-3 border-b pb-2 text-blue-600 text-sm">📈 Top Realisasi Cabang</h3>
        <div id="list_realisasi_top" class="space-y-3"></div>
      </div>
      <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <h3 class="font-bold text-gray-800 mb-3 border-b pb-2 text-emerald-600 text-sm">🛡️ Top NPL Terbaik</h3>
        <div id="list_npl_bottom" class="space-y-3"></div>
      </div>
      <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <h3 class="font-bold text-gray-800 mb-3 border-b pb-2 text-indigo-600 text-sm">🥇 Top Realisasi AO</h3>
        <div id="list_realisasi_ao" class="space-y-3"></div>
      </div>
      <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <h3 class="font-bold text-gray-800 mb-3 border-b pb-2 text-green-600 text-sm">🏆 Top Repayment Rate (RR)</h3>
        <div id="list_rr_top" class="space-y-3"></div>
      </div>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <h3 class="font-bold text-gray-800 mb-3 border-b pb-2 text-red-600 text-sm">🚨 Top NPL Terburuk</h3>
        <div id="list_npl_top" class="space-y-3"></div>
      </div>
      <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <h3 class="font-bold text-gray-800 mb-3 border-b pb-2 text-orange-500 text-sm">📉 Bottom Realisasi Cabang</h3>
        <div id="list_realisasi_bottom" class="space-y-3"></div>
      </div>
      <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <h3 class="font-bold text-gray-800 mb-3 border-b pb-2 text-red-500 text-sm">⚠️ NPL Memburuk (Naik)</h3>
        <div id="list_npl_naik" class="space-y-3"></div>
      </div>
      <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <h3 class="font-bold text-gray-800 mb-3 border-b pb-2 text-green-500 text-sm">🎉 NPL Membaik (Turun)</h3>
        <div id="list_npl_turun" class="space-y-3"></div>
      </div>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white p-4 rounded-xl shadow-sm border-t-4 border-yellow-400">
        <h3 class="font-bold text-gray-800 mb-3 border-b pb-2 text-[13px]">🏦 Top Saldo Deposito</h3>
        <div id="list_dep_saldo_top" class="space-y-3"></div>
      </div>
      <div class="bg-white p-4 rounded-xl shadow-sm border-t-4 border-yellow-400">
        <h3 class="font-bold text-gray-800 mb-3 border-b pb-2 text-[13px]">📉 Bottom Saldo Deposito</h3>
        <div id="list_dep_saldo_bot" class="space-y-3"></div>
      </div>
      <div class="bg-white p-4 rounded-xl shadow-sm border-t-4 border-yellow-400">
        <h3 class="font-bold text-gray-800 mb-3 border-b pb-2 text-[13px]">💰 Deposito Baru Masuk</h3>
        <div id="list_dep_baru" class="space-y-3"></div>
      </div>
      <div class="bg-white p-4 rounded-xl shadow-sm border-t-4 border-yellow-400">
        <h3 class="font-bold text-gray-800 mb-3 border-b pb-2 text-[13px]">💸 Deposito Cair/Keluar</h3>
        <div id="list_dep_cair" class="space-y-3"></div>
      </div>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white p-4 rounded-xl shadow-sm border-t-4 border-teal-500">
        <h3 class="font-bold text-gray-800 mb-3 border-b pb-2 text-[13px]">💳 Top Saldo Tabungan</h3>
        <div id="list_tab_saldo_top" class="space-y-3"></div>
      </div>
      <div class="bg-white p-4 rounded-xl shadow-sm border-t-4 border-teal-500">
        <h3 class="font-bold text-gray-800 mb-3 border-b pb-2 text-[13px]">📉 Bottom Saldo Tabungan</h3>
        <div id="list_tab_saldo_bot" class="space-y-3"></div>
      </div>
      <div class="bg-white p-4 rounded-xl shadow-sm border-t-4 border-teal-500">
        <h3 class="font-bold text-gray-800 mb-3 border-b pb-2 text-[13px]">💵 Tabungan Baru Masuk</h3>
        <div id="list_tab_baru" class="space-y-3"></div>
      </div>
      <div class="bg-white p-4 rounded-xl shadow-sm border-t-4 border-teal-500">
        <h3 class="font-bold text-gray-800 mb-3 border-b pb-2 text-[13px]">💸 Tabungan Cair/Keluar</h3>
        <div id="list_tab_cair" class="space-y-3"></div>
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
  
  const fmtB = n => {
    let num = Number(n||0);
    let absNum = Math.abs(num);
    if(absNum >= 1e12) return (num/1e12).toFixed(2) + ' T'; 
    if(absNum >= 1e9) return (num/1e9).toFixed(2) + ' M';   
    if(absNum >= 1e6) return (num/1e6).toFixed(1) + ' Jt';  
    return fmt(num);
  };
  
  const pct = x => (x == null ? '0%' : `${(+x).toFixed(2)}%`);
  
  const getDeltaHTML = (val, isPercent = false, invertGoodBad = false) => {
    let numVal = Number(val || 0);
    if(numVal === 0) return `<span class="text-gray-400">Tetap 0</span>`;
    let isGood = invertGoodBad ? numVal < 0 : numVal > 0;
    let color = isGood ? 'text-green-600' : 'text-red-600';
    let icon = numVal > 0 ? '▲' : '▼';
    let displayVal = isPercent ? pct(Math.abs(numVal)) : fmtB(Math.abs(numVal));
    return `<span class="${color}">${icon} ${displayVal}</span>`;
  };

  // Variable Global untuk instance Chart.js
  let chartTrenInstance = null;

  // ==========================================
  // 2. INIT & EVENT LISTENERS
  // ==========================================
  window.addEventListener('DOMContentLoaded', async () => {
    document.getElementById('filter_closing').value = '2026-02-28';
    document.getElementById('filter_harian').value = '2026-03-10';
    
    try {
      const res = await fetch('./api/kode/', { method: 'POST', body: JSON.stringify({type:'kode_kantor'}) });
      const j = await res.json();
      if(j.data) {
        const sel = document.getElementById('filter_kantor');
        j.data.filter(x => x.kode_kantor !== '000').forEach(k => {
          sel.innerHTML += `<option value="${k.kode_kantor}">${k.kode_kantor} - ${k.nama_kantor}</option>`;
        });
      }
    } catch(e) { console.log('Gagal load cabang'); }

    // Jalankan kedua fetch saat pertama load
    fetchDashboardUtama();
    fetchTrenNPL();
  });

  // Tombol Filter (Refresh SEMUA)
  document.getElementById('formFilterMaster').addEventListener('submit', e => {
    e.preventDefault();
    fetchDashboardUtama();
    fetchTrenNPL();
  });

  // Dropdown Tren NPL (Refresh HANYA CHART TREN)
  document.getElementById('filter_tren').addEventListener('change', () => {
    fetchTrenNPL();
  });

  // ==========================================
  // 3. FETCH API KHUSUS TREN NPL (CHART)
  // ==========================================
  async function fetchTrenNPL() {
    const loadingChart = document.getElementById('loadingChartTren');
    loadingChart.classList.remove('hidden');

    let kantor = document.getElementById('filter_kantor').value;
    
    // Payload sesuai instruksi
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
      const res = await fetch('./api/dashboard/', { 
        method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
      });
      const json = await res.json();
      
      const dataTren = Array.isArray(json.data) ? json.data : (json.data?.tren_npl || []);
      renderChartJS(dataTren);

    } catch(e) {
      console.error("Gagal load Tren NPL", e);
    } finally {
      loadingChart.classList.add('hidden');
    }
  }

  // ==========================================
  // 4. FETCH API DASHBOARD UTAMA
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
      const res = await fetch('./api/dashboard/', { 
        method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
      });
      const json = await res.json();
      
      if(json.status === 200 && json.data) {
        renderDashboardUtama(json.data);
      } else {
        alert("Gagal memuat: " + (json.message || "Data Kosong"));
      }
    } catch(e) {
      console.error(e);
    } finally {
      document.getElementById('loadingDash').classList.add('hidden');
      document.getElementById('contentDash').classList.remove('hidden');
    }
  }

  // ==========================================
  // 5. RENDER CHART.JS (PAKAI % NPL)
  // ==========================================
  function renderChartJS(dataArray) {
    const ctx = document.getElementById('canvasTrenNPL').getContext('2d');
    
    // Hancurkan chart lama jika ada
    if(chartTrenInstance) {
      chartTrenInstance.destroy();
    }

    if(!dataArray || dataArray.length === 0) return;

    const labels = dataArray.map(d => d.label || d.tanggal);
    const dataNominal = dataArray.map(d => Number(d.npl_amt));
    // KUNCI PERUBAHAN: Sumbu Y dipetakan ke Persen NPL
    const dataPersen = dataArray.map(d => Number(d.npl_persen)); 

    let gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(239, 68, 68, 0.5)'); 
    gradient.addColorStop(1, 'rgba(239, 68, 68, 0.0)');

    chartTrenInstance = new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Persentase NPL',
          data: dataPersen,            // SUMBU Y MENGGUNAKAN PERSEN NPL
          borderColor: '#ef4444',      
          backgroundColor: gradient,   
          borderWidth: 3,
          pointBackgroundColor: '#ffffff',
          pointBorderColor: '#ef4444',
          pointBorderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6,
          fill: true,
          tension: 0.4                 // Bikin garisnya melengkung smooth
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: 'rgba(17, 24, 39, 0.9)',
            titleFont: { size: 13, family: 'sans-serif' },
            bodyFont: { size: 12, family: 'sans-serif' },
            padding: 10,
            cornerRadius: 8,
            callbacks: {
              // TOOLTIP LENGKAP SAAT DI-HOVER
              label: function(context) {
                let pctVal = context.raw; // % NPL
                let nomVal = dataNominal[context.dataIndex]; // Rupiah NPL
                return `NPL: ${pctVal}%  (Rp ${fmtB(nomVal)})`; 
              }
            }
          }
        },
        scales: {
          x: { 
            grid: { display: false } 
          },
          y: { 
            // beginAtZero: dihapus agar garis naik-turunnya lebih terlihat ekstrem
            grid: { borderDash: [4, 4], color: '#e5e7eb' },
            ticks: {
              callback: function(value) { return value + '%'; } // Tambahkan lambang % di Y-Axis
            }
          }
        }
      }
    });
  }

  // ==========================================
  // 6. RENDER LIST & KOMPONEN LAINNYA
  // ==========================================
  function renderDashboardUtama(d) {
    // --- A. KPI CARDS ---
    try {
      const rrG = d.repayment_rate?.grand_total || {};
      document.getElementById('kpi_os').textContent = `Rp ${fmtB(rrG.os_total)}`;
      
      const tNpl = d.tren_npl || [];
      if(tNpl.length > 0) {
        const last = tNpl[tNpl.length - 1];
        const prev = tNpl.length > 1 ? tNpl[tNpl.length - 2] : last;
        document.getElementById('kpi_npl').textContent = `Rp ${fmtB(last.npl_amt)}`;
        document.getElementById('kpi_npl_pct').textContent = pct(last.npl_persen);
        document.getElementById('kpi_npl_delta').innerHTML = getDeltaHTML(last.npl_persen - prev.npl_persen, true, true);
      }

      document.getElementById('kpi_rr').textContent = pct(rrG.rr_persen_curr);
      document.getElementById('kpi_rr_delta').innerHTML = getDeltaHTML(rrG.delta_rr, true, false);

      const depG = d.perkembangan_deposito?.grand_total || {};
      const tabG = d.perkembangan_tabungan?.grand_total || {};
      document.getElementById('kpi_dpk').textContent = `Rp ${fmtB((depG.saldo_curr||0) + (tabG.saldo_curr||0))}`;
      document.getElementById('kpi_dpk_noa').textContent = `${fmt((depG.noa_curr||0) + (tabG.noa_curr||0))} NOA`;
      document.getElementById('kpi_dpk_delta').innerHTML = getDeltaHTML((depG.delta_saldo||0) + (tabG.delta_saldo||0), false, false);
    } catch(e) {}

    // --- B. MINI BAR KORWIL ---
    try {
      renderKorwilCompare('box_runoff_realisasi', d.runoff_vs_realisasi?.detail_korwil, 'realisasi', 'total_runoff', 'bg-blue-500', 'bg-orange-500');
      renderKorwilCompare('box_flow_recovery', d.flow_vs_recovery_npl?.detail_korwil, 'flow_npl', 'total_recovery', 'bg-red-500', 'bg-green-500');
    } catch(e) {}

    // --- C. LIST REALISASI & NPL (SUDAH DI SWAP) ---
    try {
      // Row 3 List
      renderUniversalList('list_realisasi_top', d.top_bottom_realisasi?.top_cabang, 'nama_cabang', 'total_realisasi', 'noa_realisasi', 'bg-blue-500', false, 'NOA');
      renderUniversalList('list_npl_bottom', d.top_bottom_npl?.bottom, 'nama_cabang', 'npl_persen', 'npl_amt', 'bg-emerald-400', true, 'Rp');
      renderUniversalList('list_realisasi_ao', d.top_bottom_realisasi?.top_ao, 'nama_ao', 'total_realisasi', 'noa_realisasi', 'bg-indigo-500', false, 'NOA');
      renderUniversalList('list_rr_top', d.repayment_rate?.top_rr, 'nama_cabang', 'rr_persen_curr', 'os_total', 'bg-green-500', true, 'Rp');
      
      // Row 4 List
      renderUniversalList('list_npl_top', d.top_bottom_npl?.top, 'nama_cabang', 'npl_persen', 'npl_amt', 'bg-red-500', true, 'Rp');
      renderUniversalList('list_realisasi_bottom', d.top_bottom_realisasi?.bottom_cabang, 'nama_cabang', 'total_realisasi', 'noa_realisasi', 'bg-orange-400', false, 'NOA');
      renderUniversalList('list_npl_naik', d.kenaikan_penurunan_npl?.top_kenaikan, 'nama_cabang', 'delta_npl', 'npl_persen_curr', 'bg-red-400', true, 'NPL Now');
      renderUniversalList('list_npl_turun', d.kenaikan_penurunan_npl?.top_penurunan, 'nama_cabang', 'delta_npl', 'npl_persen_curr', 'bg-green-400', true, 'NPL Now');
    } catch(e) {}

    // --- D. LIST DEPOSITO & TABUNGAN ---
    try {
      const dp = d.perkembangan_deposito || {};
      renderUniversalList('list_dep_saldo_top', dp.top_saldo, 'nama_cabang', 'saldo_curr', 'noa_curr', 'bg-yellow-500', false, 'Rek');
      renderUniversalList('list_dep_saldo_bot', dp.bottom_saldo, 'nama_cabang', 'saldo_curr', 'noa_curr', 'bg-yellow-300', false, 'Rek');
      renderUniversalList('list_dep_baru', dp.top_baru, 'nama_cabang', 'saldo_baru', 'noa_tambah', 'bg-green-500', false, 'Rek Baru');
      renderUniversalList('list_dep_cair', dp.top_pencairan, 'nama_cabang', 'saldo_cair', 'noa_kurang', 'bg-red-400', false, 'Rek Cair');
      
      const tb = d.perkembangan_tabungan || {};
      renderUniversalList('list_tab_saldo_top', tb.top_saldo, 'nama_cabang', 'saldo_curr', 'noa_curr', 'bg-teal-500', false, 'Rek');
      renderUniversalList('list_tab_saldo_bot', tb.bottom_saldo, 'nama_cabang', 'saldo_curr', 'noa_curr', 'bg-teal-300', false, 'Rek');
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
      
      box.innerHTML += `
        <div class="mb-2">
          <div class="flex justify-between text-[11px] font-bold text-gray-700 mb-0.5">
            <span>${k.nama_korwil}</span>
          </div>
          <div class="flex flex-col gap-0.5 relative">
            <div class="w-full bg-gray-100 h-1.5 rounded-r-full flex relative">
              <div class="${colorA} h-1.5 rounded-r-full bar-fill z-10" style="width: ${pctA}%"></div>
              <span class="absolute right-0 -top-3.5 text-[9px] text-gray-500">${fmtB(vA)}</span>
            </div>
            <div class="w-full bg-gray-100 h-1.5 rounded-r-full flex relative">
              <div class="${colorB} h-1.5 rounded-r-full bar-fill z-10" style="width: ${pctB}%"></div>
              <span class="absolute right-0 -bottom-3.5 text-[9px] text-gray-500">${fmtB(vB)}</span>
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
      box.innerHTML = `<p class="text-[11px] text-gray-400 italic py-2">Tidak ada data.</p>`;
      return;
    }

    let maxVal = Math.max(...dataArray.map(o => Math.abs(Number(o[valKey]) || 0)));
    if(maxVal === 0) maxVal = 1;

    dataArray.forEach(item => {
      let val = Number(item[valKey] || 0); let sub = Number(item[subKey] || 0);
      let wPct = Math.abs((val / maxVal) * 100);
      let displayVal = isPercent ? pct(val) : fmtB(Math.abs(val));
      
      let displaySub = '';
      if(subLabel === 'Rp') displaySub = `Rp ${fmtB(sub)}`;
      else if(subLabel === 'NPL Now') displaySub = `NPL saat ini: ${pct(sub)}`;
      else displaySub = `${fmt(sub)} ${subLabel}`;

      let name = (item[nameKey] || '-').replace(/Kc\. /gi, '');
      
      box.innerHTML += `
        <div class="mb-3 group cursor-default">
          <div class="flex justify-between items-end mb-1">
            <div class="flex flex-col w-2/3">
              <span class="text-xs font-bold text-gray-700 truncate group-hover:text-blue-600 transition-colors" title="${name}">${name}</span>
              <span class="text-[10px] text-gray-500 leading-tight">${displaySub}</span>
            </div>
            <span class="text-xs font-black text-gray-900">${val < 0 ? '-' : ''}${displayVal}</span>
          </div>
          <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden">
            <div class="${colorClass} h-1.5 rounded-full bar-fill" style="width: ${Math.max(2, wPct)}%"></div>
          </div>
        </div>
      `;
    });
  }
</script>