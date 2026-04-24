<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
  :root { --primary: #0284c7; --bg: #f8fafc; --text: #334155; }
  body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text); overflow-x: hidden; }
  
  .inp { 
      box-sizing: border-box; border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 0 0.5rem; 
      font-size: 13px; background: #fff; height: 42px; cursor: pointer; outline: none; transition: border 0.2s; font-weight: 600;
  }
  .inp:focus { border-color: var(--primary); box-shadow: 0 0 0 2px #bae6fd; }
  
  .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
  .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

  table { width: 100%; border-collapse: collapse; font-size: 12px; }
  th { background-color: #f8fafc; color: #1e293b; font-weight: 800; padding: 12px 10px; border-bottom: 2px solid #e2e8f0; text-transform: uppercase; font-size: 11px; }
  td { padding: 12px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; font-weight: 700; color: #334155; }
  tr:hover td { background-color: #f0f9ff; }
  
  .card-shadow { box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); }
  
  .tab-btn { padding: 8px 24px; border-radius: 8px; font-size: 13px; font-weight: 800; color: #64748b; transition: all 0.2s; border: 1px solid transparent; cursor: pointer; }
  .tab-btn.active { background-color: #0284c7; color: #fff; box-shadow: 0 4px 6px -1px rgba(2, 132, 199, 0.4); border-color: #0284c7; }
  .tab-btn:hover:not(.active) { color: #0f172a; background-color: #e2e8f0; }

  .local-loader { position: absolute; inset: 0; background: rgba(255,255,255,0.7); z-index: 50; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(2px); border-radius: inherit; }
  .local-loader.hidden { display: none; }

  /* CSS ApexCharts */
  .apexcharts-tooltip { z-index: 99999 !important; background: transparent !important; border: none !important; box-shadow: none !important; }
</style>

<div class="max-w-[1600px] mx-auto px-3 md:px-4 py-4 flex flex-col gap-5">
  
  <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 bg-white p-4 rounded-xl card-shadow border border-slate-100">
    <div>
        <h1 class="text-xl md:text-2xl font-bold flex items-center gap-2 text-slate-800">
            <span class="bg-blue-600 text-white p-1.5 rounded-lg text-sm shadow-sm">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </span> 
            <span>Layanan Digital</span>
        </h1>
        <p class="text-xs text-slate-500 mt-1 ml-1 font-medium" id="lbl_periode_aktif">Menunggu data sinkronisasi...</p>
    </div>

    <form id="formFilterGlobal" class="flex flex-col md:flex-row items-end gap-3 w-full xl:w-auto">
        <div class="flex flex-col flex-1 md:w-[140px]">
            <label class="text-[10px] font-extrabold text-slate-500 uppercase ml-1 mb-1 tracking-wider">CLOSING M-1</label>
            <input type="date" id="closing_date" class="inp text-slate-700 shadow-sm" required>
        </div>
        <div class="flex flex-col flex-1 md:w-[140px]">
            <label class="text-[10px] font-extrabold text-slate-500 uppercase ml-1 mb-1 tracking-wider">HARIAN / ACTUAL</label>
            <input type="date" id="harian_date" class="inp text-slate-700 shadow-sm" required>
        </div>

        <div class="flex flex-col w-full md:w-[220px]">
            <label class="text-[10px] font-extrabold text-slate-500 uppercase ml-1 mb-1 tracking-wider">AREA / CABANG</label>
            <select id="opt_area" class="inp text-blue-700 shadow-sm">
                <option value="KONSOLIDASI" class="font-bold">Konsolidasi</option>
                <optgroup label="Berdasarkan Korwil" class="text-slate-400">
                    <option value="KORWIL_SEMARANG" class="text-slate-700">Korwil Semarang</option>
                    <option value="KORWIL_SOLO" class="text-slate-700">Korwil Solo</option>
                    <option value="KORWIL_BANYUMAS" class="text-slate-700">Korwil Banyumas</option>
                    <option value="KORWIL_PEKALONGAN" class="text-slate-700">Korwil Pekalongan</option>
                </optgroup>
                <optgroup label="Berdasarkan Cabang" id="opt_cabang_list" class="text-slate-400"></optgroup>
            </select>
        </div>
        
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white h-[42px] px-6 rounded-lg font-bold text-sm shadow-md flex items-center justify-center transition w-full md:w-auto">
            Tampilkan
        </button>
    </form>
  </div>

  <div class="relative rounded-xl min-h-[100px]">
      <div id="loadSummary" class="local-loader hidden"><div class="animate-spin h-8 w-8 border-4 border-blue-200 border-t-blue-600 rounded-full"></div></div>
      <div id="summaryCardsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3">
          </div>
  </div>

  <div class="flex justify-center md:justify-start mt-2">
      <div class="flex gap-2 bg-slate-200/60 p-1.5 rounded-2xl shadow-inner overflow-x-auto custom-scrollbar">
          <button onclick="changeChannel('VA')" id="tab_VA" class="tab-btn active">Virtual Account (VA)</button>
          <button onclick="changeChannel('BRANCHLESS')" id="tab_BRANCHLESS" class="tab-btn">Branchless</button>
          <button onclick="changeChannel('QRIS')" id="tab_QRIS" class="tab-btn">QRIS</button>
      </div>
  </div>

  <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 mt-2">
      <div class="xl:col-span-7 bg-white rounded-xl card-shadow p-5 flex flex-col relative h-[430px] border border-slate-100">
          <div id="loadTrend" class="local-loader hidden"><div class="animate-spin h-8 w-8 border-4 border-blue-200 border-t-blue-600 rounded-full"></div></div>
          <div class="flex justify-between items-center mb-2 border-b border-slate-100 pb-2">
              <h2 class="font-bold text-slate-800" id="titleTrend">Tren Transaksi VA</h2>
              <select id="trendPeriode" class="inp h-8 text-[11px] w-[140px]" onchange="fetchTrend()">
                  <option value="bulanan">6 Bulan Terakhir</option>
                  <option value="7_hari">7 Hari Terakhir</option>
                  <option value="30_hari">30 Hari Terakhir</option>
                  <option value="tahunan">Tahunan</option>
              </select>
          </div>
          <div id="chartTrend" class="w-full mt-2"></div>
      </div>

      <div class="xl:col-span-5 bg-white rounded-xl card-shadow p-5 flex flex-col relative h-[430px] border border-slate-100">
          <div id="loadDist" class="local-loader hidden"><div class="animate-spin h-8 w-8 border-4 border-blue-200 border-t-blue-600 rounded-full"></div></div>
          
          <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-2">
              <h2 class="font-bold text-slate-800" id="titleDistribusi">Distribusi per Wilayah (VA)</h2>
          </div>

          <div class="flex-1 flex flex-col md:flex-row gap-4">
              <div class="flex-1 overflow-y-auto custom-scrollbar pr-2 flex flex-col gap-4 h-full" id="listTop5"></div>
              
              <div class="w-full md:w-[220px] flex items-center justify-center shrink-0 h-full pb-2">
                  <div id="chartDonut" class="w-full"></div>
              </div>
          </div>
      </div>
  </div>

  <div class="bg-white rounded-xl card-shadow flex flex-col overflow-hidden mt-2 border border-slate-100 relative min-h-[200px]">
      <div id="loadTable" class="local-loader hidden"><div class="animate-spin h-8 w-8 border-4 border-blue-200 border-t-blue-600 rounded-full"></div></div>
      
      <div class="p-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
          <h2 class="text-base font-black text-slate-800">Breakdown Transaksi Area</h2>
      </div>

      <div class="overflow-x-auto custom-scrollbar max-h-[500px]">
          <table class="w-full text-left">
              <thead class="sticky top-0 z-10">
                  <tr>
                      <th class="w-[250px] pl-4">NAMA AREA</th>
                      <th class="text-right">NOMINAL BULAN INI</th>
                      <th class="text-right">NOMINAL LALU</th>
                      <th class="text-center w-[100px]">GROWTH (RP)</th>
                      <th class="text-right">TRX BULAN INI</th>
                      <th class="text-right">TRX LALU</th>
                      <th class="text-center w-[100px] pr-4">GROWTH (TRX)</th>
                  </tr>
              </thead>
              <tbody id="bodyBreakdown" class="divide-y divide-slate-100">
                  </tbody>
          </table>
      </div>
  </div>

</div>

<script>
  const API_URL = './api/transaksi/'; 
  const API_KODE = './api/kode/';
  const API_DATE = './api/date/';

  const nf = new Intl.NumberFormat('id-ID');
  const fmt = n => nf.format(Number(n||0));
  
  let chartTrendObj = null;
  let chartDonutObj = null;
  let currentActiveChannel = 'VA'; 

  const showLoad = (id) => document.getElementById(id)?.classList.remove('hidden');
  const hideLoad = (id) => document.getElementById(id)?.classList.add('hidden');

  async function getLastHarianData() {
      try { const r = await fetch(API_DATE); const j = await r.json(); return j.data || null; } 
      catch { return null; }
  }

  // ==========================================
  // INISIALISASI AWAL
  // ==========================================
  window.addEventListener('DOMContentLoaded', async () => {
    const user = (window.getUser && window.getUser()) || null;
    let uKode = user?.kode ? String(user.kode).padStart(3,'0') : '000';
    if(uKode === '099') uKode = '000'; 

    if (uKode === '000') {
        await loadCabangList();
        document.getElementById('opt_area').value = "KONSOLIDASI"; 
    } else {
        document.getElementById('opt_area').innerHTML = `<option value="${uKode}">CABANG ${uKode}</option>`;
        document.getElementById('opt_area').disabled = true; 
    }

    const dateData = await getLastHarianData();
    let hDate = new Date();
    
    if (dateData && dateData.last_created) {
        hDate = new Date(dateData.last_created);
        document.getElementById('harian_date').value = dateData.last_created;
    } else {
        document.getElementById('harian_date').value = hDate.toISOString().split('T')[0];
    }
    
    if (dateData && dateData.closing_date) {
        document.getElementById('closing_date').value = dateData.closing_date;
    } else {
        let pDate = new Date(hDate.getFullYear(), hDate.getMonth(), 0);
        let dd = String(pDate.getDate()).padStart(2, '0');
        let mm = String(pDate.getMonth() + 1).padStart(2, '0');
        let yyyy = pDate.getFullYear();
        document.getElementById('closing_date').value = `${yyyy}-${mm}-${dd}`;
    }
    
    initCharts();
    runFullSync(); 
  });

  async function loadCabangList() {
    try {
        const res = await fetch(API_KODE, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({type:'kode_kantor'}) });
        const json = await res.json();
        let html = '';
        (json.data || []).filter(x => x.kode_kantor && x.kode_kantor !== '000').sort((a,b) => a.kode_kantor.localeCompare(b.kode_kantor)).forEach(it => {
            html += `<option value="${String(it.kode_kantor).padStart(3,'0')}" class="text-slate-700">${String(it.kode_kantor).padStart(3,'0')} - ${it.nama_kantor}</option>`;
        });
        document.getElementById('opt_cabang_list').innerHTML = html;
    } catch(e){}
  }

  function parseAreaValue() {
      const val = document.getElementById('opt_area').value;
      let kode_kantor = ""; let korwil = "";
      if (val.startsWith('KORWIL_')) { korwil = val.replace('KORWIL_', ''); } 
      else if (val !== 'KONSOLIDASI') { kode_kantor = val; }
      return { kode_kantor, korwil };
  }

  document.getElementById('formFilterGlobal').addEventListener('submit', e => { 
      e.preventDefault(); 
      runFullSync(); 
  });

  function changeChannel(ch) {
      currentActiveChannel = ch;
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      document.getElementById('tab_' + ch).classList.add('active');

      const namaCh = ch === 'VA' ? 'VA' : (ch === 'BRANCHLESS' ? 'Branchless' : 'QRIS');
      document.getElementById('titleTrend').innerText = `Tren Transaksi ${namaCh}`;
      document.getElementById('titleDistribusi').innerText = `Distribusi per Wilayah (${namaCh})`;

      fetchTrend();
      fetchDistribusi();
      fetchBreakdown();
  }

  async function runFullSync() {
      fetchSummaryCards();
      fetchTrend();
      fetchDistribusi();
      fetchBreakdown();
  }

  // ==========================================
  // 1. SUMMARY CARDS 
  // ==========================================
  async function fetchSummaryCards() {
      showLoad('loadSummary');
      const area = parseAreaValue();
      const payload = { 
          type: "summary_cards_transaksi",
          harian_date: document.getElementById('harian_date').value,
          closing_date: document.getElementById('closing_date').value,
          kode_kantor: area.kode_kantor,
          korwil: area.korwil
      };
      try {
          const res = await fetch(API_URL, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          const j = await res.json();
          if(j.status === 200) {
              document.getElementById('lbl_periode_aktif').innerHTML = `Periode: <span class="text-blue-700 font-bold">${j.data.meta.closing_date} s/d ${j.data.meta.harian_date}</span>`;
              const container = document.getElementById('summaryCardsContainer');
              container.innerHTML = '';
              
              const filteredCards = j.data.cards.filter(c => 
                  !c.title.toUpperCase().includes('SEMUA CHANNEL') && 
                  !c.title.toUpperCase().includes('TOTAL DIGITAL')
              );

              filteredCards.forEach((c, idx) => {
                  const isUp = parseFloat(c.growth) >= 0;
                  const bColor = isUp ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700';
                  const arrow = isUp ? '▲' : '▼';
                  
                  let bTop = 'border-t-4 border-t-slate-200';
                  if (c.title.includes('VA') && !c.title.includes('BANK')) bTop = 'border-t-4 border-t-blue-600';
                  else if (c.title.includes('MANDIRI')) bTop = 'border-t-4 border-t-blue-400';

                  const pLabel = c.prev_label || 'Bulan Lalu';
                  const pNominal = c.prev_nominal || 'Rp -';

                  container.innerHTML += `
                      <div class="bg-white rounded-xl card-shadow p-3.5 flex flex-col justify-between ${bTop}">
                          <div>
                              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">${c.title}</p>
                              <h3 class="text-xl font-black text-slate-800 leading-tight">${c.value}</h3>
                              <p class="text-[10px] font-bold text-slate-500 mt-0.5">${c.subtitle}</p>
                          </div>
                          <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-2">
                              <span class="${bColor} px-2 py-0.5 rounded font-bold text-[11px]">${arrow} ${Math.abs(c.growth)}%</span>
                              <div class="text-right leading-tight">
                                  <span class="text-[9px] text-slate-400">${pLabel}</span><br>
                                  <span class="text-[10px] font-bold text-slate-600">${pNominal}</span>
                              </div>
                          </div>
                      </div>`;
              });
          }
      } catch (e) {} finally { hideLoad('loadSummary'); }
  }

  // ==========================================
  // 2. GRAFIK TREN & PIE
  // ==========================================
  function initCharts() {
      // 🔥 FIX TREN CHART: Fix pixel height & padding bottom ditarik ke dalam
      chartTrendObj = new ApexCharts(document.querySelector("#chartTrend"), {
          series: [], 
          chart: { 
              type: 'area', 
              height: 340, // Paksa tinggi fix 340px agar tidak overflow flex container
              parentHeightOffset: 0,
              toolbar: { show: false } 
          },
          colors: ['#0284c7'], 
          dataLabels: { enabled: false }, 
          legend: { show: false }, 
          stroke: { curve: 'smooth', width: 3 },
          fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.05, stops: [0, 100] } },
          grid: { 
              padding: { bottom: 15, left: 10, right: 10 } // Memberi bantalan agar label x-axis masuk
          },
          xaxis: { 
              categories: [], 
              labels: { 
                  style: { fontSize: '10px' }, 
                  offsetY: -5 // Menarik text bulan ke atas sedikit agar aman
              },
              tooltip: { enabled: false }
          },
          yaxis: { labels: { formatter: (val) => val >= 1000000000 ? (val/1000000000).toFixed(1)+' M' : (val >= 1000000 ? (val/1000000).toFixed(0)+' Jt' : val) } },
          tooltip: {
              theme: 'light',
              y: {
                  formatter: function(val, opts) {
                      const trx = opts.w.config.series[opts.seriesIndex].trx ? opts.w.config.series[opts.seriesIndex].trx[opts.dataPointIndex] : 0;
                      return `Rp ${nf.format(val)} <span style="color:#64748b; font-size:11px; margin-left:8px; font-weight:normal;">(${nf.format(trx)} Trx)</span>`; 
                  },
                  title: { formatter: () => '' }
              }
          }
      });
      chartTrendObj.render();

      // 🔥 FIX PIE CHART: Fix height
      chartDonutObj = new ApexCharts(document.querySelector("#chartDonut"), {
          series: [], 
          chart: { 
              type: 'donut', 
              height: 330, // Paksa tinggi fix
              parentHeightOffset: 0 
          }, 
          labels: [],
          colors: ['#8b5cf6', '#0ea5e9', '#10b981', '#f59e0b', '#f43f5e', '#64748b'],
          plotOptions: { donut: { size: '70%' } }, 
          dataLabels: { enabled: false }, 
          legend: { 
              show: true, 
              position: 'bottom', 
              fontSize: '9.5px',
              fontFamily: 'Inter',
              offsetY: -5,
              markers: { width: 8, height: 8, radius: 2 },
              itemMargin: { horizontal: 5, vertical: 2 } 
          },
          tooltip: {
              custom: function({series, seriesIndex, dataPointIndex, w}) {
                  const val = series[seriesIndex];
                  const name = w.globals.labels[seriesIndex];
                  const color = w.globals.colors[seriesIndex];
                  const trx = w.config.customTrx ? w.config.customTrx[seriesIndex] : 0;
                  return `
                    <div style="padding: 6px 10px; background: #fff; border: 1px solid #e2e8f0; border-radius: 4px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); transform: translateY(-20px);">
                        <div style="font-size: 10px; font-weight: 800; color: ${color}; margin-bottom: 2px;">${name}</div>
                        <div style="font-size: 12px; font-weight: 900; color: #1e293b;">Rp ${nf.format(val)}</div>
                        <div style="font-size: 10px; font-weight: 600; color: #64748b;">${nf.format(trx)} Transaksi</div>
                    </div>`;
              }
          }
      });
      chartDonutObj.render();
  }

  async function fetchTrend() {
      showLoad('loadTrend');
      const area = parseAreaValue();
      const payload = { type: "tren_nominal_va", harian_date: document.getElementById('harian_date').value, kode_kantor: area.kode_kantor, korwil: area.korwil, periode: document.getElementById('trendPeriode').value, channel: currentActiveChannel };
      try {
          const r = await fetch(API_URL, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          const j = await r.json();
          if(j.status === 200 && j.data.chart_nominal) {
              chartTrendObj.updateOptions({ xaxis: { categories: j.data.chart_nominal.labels } });
              chartTrendObj.updateSeries(j.data.chart_nominal.series);
          }
      } catch(e){} finally { hideLoad('loadTrend'); }
  }

  async function fetchDistribusi() {
      showLoad('loadDist');
      const area = parseAreaValue();
      const payload = { type: "distribusi_va", harian_date: document.getElementById('harian_date').value, closing_date: document.getElementById('closing_date').value, kode_kantor: area.kode_kantor, korwil: area.korwil, channel: currentActiveChannel };
      
      try {
          const r = await fetch(API_URL, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          const j = await r.json();
          if(j.status === 200) {
              const d = j.data;
              
              chartDonutObj.updateOptions({ labels: d.donut_chart.labels, customTrx: d.donut_chart.trx });
              chartDonutObj.updateSeries(d.donut_chart.series);

              const listC = document.getElementById('listTop5'); listC.innerHTML = '';
              const colors = ['bg-violet-500', 'bg-sky-500', 'bg-emerald-500', 'bg-amber-500', 'bg-rose-500'];
              if(d.top_5.length === 0) { listC.innerHTML = '<p class="text-xs text-slate-400">Data kosong</p>'; hideLoad('loadDist'); return; }
              const maxNom = d.top_5[0].nominal;
              
              d.top_5.forEach((t, i) => {
                  const wPct = (t.nominal / maxNom) * 100;
                  const cColor = colors[i % colors.length];
                  let fNom = t.nominal >= 1000000000 ? (t.nominal/1000000000).toFixed(2)+' M' : (t.nominal >= 1000000 ? (t.nominal/1000000).toFixed(1)+' jt' : nf.format(t.nominal));
                  listC.innerHTML += `
                      <div class="flex flex-col text-xs">
                          <div class="flex justify-between items-end mb-1">
                              <div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full ${cColor} text-white flex items-center justify-center text-[9px] font-bold">${i+1}</span><span class="font-bold text-slate-700">${t.label}</span></div>
                              <div class="text-right leading-none"><span class="font-black text-slate-800">Rp ${fNom}</span><br><span class="text-[9px] text-slate-400">${nf.format(t.trx)} Trx</span></div>
                          </div>
                          <div class="w-full bg-slate-100 rounded-full h-1.5"><div class="${cColor} h-1.5 rounded-full" style="width: ${wPct}%"></div></div>
                      </div>`;
              });
          }
      } catch(e){} finally { hideLoad('loadDist'); }
  }

  // ==========================================
  // 3. TABEL BREAKDOWN 
  // ==========================================
  async function fetchBreakdown() {
      showLoad('loadTable');
      const area = parseAreaValue();
      const payload = { type: "detail_breakdown_transaksi", harian_date: document.getElementById('harian_date').value, closing_date: document.getElementById('closing_date').value, kode_kantor: area.kode_kantor, korwil: area.korwil, channel: currentActiveChannel };
      
      try {
          const res = await fetch(API_URL, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          const j = await res.json();
          const tbody = document.getElementById('bodyBreakdown');
          tbody.innerHTML = '';

          if(j.status !== 200 || !j.data.data.length) { tbody.innerHTML = `<tr><td colspan="7" class="text-center py-6">Data kosong.</td></tr>`; hideLoad('loadTable'); return; }

          const dt = j.data.data;
          const optAreaVal = document.getElementById('opt_area').value;
          const isKonsolidasi = optAreaVal === 'KONSOLIDASI'; 
          const isSpecificKorwil = optAreaVal.startsWith('KORWIL_');

          const rHtml = (nama, cN, pN, gN, cT, pT, gT, isBold=false, isChild=false) => {
              const bg = isBold ? 'bg-slate-50 font-bold' : (isChild ? 'text-slate-600 bg-slate-50/20' : 'font-bold text-slate-700');
              const pad = isChild ? 'pl-10 relative before:absolute before:w-3 before:h-px before:bg-slate-300 before:left-5 before:top-1/2' : 'pl-4';
              const c_gN = gN > 0 ? `<span class="text-emerald-600">▲ ${gN}%</span>` : (gN < 0 ? `<span class="text-red-600">▼ ${Math.abs(gN)}%</span>` : '-');
              const c_gT = gT > 0 ? `<span class="text-emerald-600">▲ ${gT}%</span>` : (gT < 0 ? `<span class="text-red-600">▼ ${Math.abs(gT)}%</span>` : '-');
              
              return `<tr class="${bg}"><td class="${pad}">${nama}</td><td class="text-right text-blue-700">${fmt(cN)}</td><td class="text-right text-[10px] text-slate-400">${fmt(pN)}</td><td class="text-center text-[11px] font-bold bg-slate-50/50">${c_gN}</td><td class="text-right text-indigo-700">${fmt(cT)}</td><td class="text-right text-[10px] text-slate-400">${fmt(pT)}</td><td class="text-center text-[11px] font-bold bg-slate-50/50 pr-4">${c_gT}</td></tr>`;
          };

          const gt = j.data.grand_total;
          tbody.innerHTML += rHtml('GRAND TOTAL', gt.curr_nom, gt.prev_nom, gt.growth_nom, gt.curr_trx, gt.prev_trx, gt.growth_trx, true);

          if (isKonsolidasi) {
              dt.forEach(kw => {
                  tbody.innerHTML += rHtml(kw.korwil, kw.curr_nom, kw.prev_nom, kw.growth_nom, kw.curr_trx, kw.prev_trx, kw.growth_trx);
              });
          } else if (isSpecificKorwil) {
              dt.forEach(kw => {
                  kw.cabang.forEach(cb => {
                      tbody.innerHTML += rHtml(cb.nama, cb.curr_nom, cb.prev_nom, cb.growth_nom, cb.curr_trx, cb.prev_trx, cb.growth_trx);
                  });
              });
          } else {
              dt.forEach(kk => {
                  tbody.innerHTML += rHtml(kk.nama, kk.curr_nom, kk.prev_nom, kk.growth_nom, kk.curr_trx, kk.prev_trx, kk.growth_trx);
              });
          }
      } catch(e){} finally { hideLoad('loadTable'); }
  }
</script>