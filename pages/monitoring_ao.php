<div class="max-w-7xl mx-auto px-4 py-6 h-screen flex flex-col">
  
  <div class="hdr flex flex-wrap items-end gap-3 mb-4">
    <div class="mr-auto">
      <h1 class="text-2xl font-bold flex items-center gap-2 text-gray-800">
        <span>📊</span><span>Monitoring Kunjungan AO</span>
      </h1>
      <p class="text-xs text-gray-500 mt-1">Evaluasi kinerja kunjungan petugas per periode.</p>
    </div>

    <form id="filterMon" class="flex flex-wrap items-end gap-2">
      <div>
        <label for="tgl_closing" class="text-xs text-gray-500 font-semibold block mb-1">Closing</label>
        <input type="date" id="tgl_closing" class="border rounded px-2 py-1 text-sm bg-white shadow-sm">
      </div>
      <div>
        <label for="tgl_harian" class="text-xs text-gray-500 font-semibold block mb-1">Posisi</label>
        <input type="date" id="tgl_harian" class="border rounded px-2 py-1 text-sm bg-white shadow-sm">
      </div>

      <div>
        <label for="opt_kantor" class="text-xs text-gray-500 font-semibold block mb-1">Filter Unit</label>
        <select id="opt_kantor" class="border rounded px-2 py-1 text-sm bg-white shadow-sm min-w-[180px]">
          <option value="000">Loading...</option>
        </select>
      </div>

      <button type="submit" id="btnShow" class="btn-icon bg-blue-600 text-white hover:bg-blue-700 shadow-md h-[30px] w-[30px]" title="Tampilkan">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
      </button>
    </form>
  </div>

  <div id="kpiWrap" class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-4">
    <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm flex flex-col items-center justify-center">
      <span class="text-[10px] text-gray-500 uppercase font-bold">Total AO</span>
      <span id="sum_ao" class="text-lg font-bold text-gray-800 mt-1">-</span>
    </div>
    <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm flex flex-col items-center justify-center">
      <span class="text-[10px] text-gray-500 uppercase font-bold">Tot. Kelolaan</span>
      <span id="sum_kelolaan" class="text-lg font-bold text-blue-600 mt-1">-</span>
    </div>
    <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm flex flex-col items-center justify-center">
      <span class="text-[10px] text-gray-500 uppercase font-bold">Acc. Visited</span>
      <span id="sum_visited" class="text-lg font-bold text-emerald-600 mt-1">-</span>
    </div>
    <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm flex flex-col items-center justify-center">
      <span class="text-[10px] text-gray-500 uppercase font-bold">Frekuensi</span>
      <span id="sum_freq" class="text-lg font-bold text-purple-600 mt-1">-</span>
    </div>
    <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm flex flex-col items-center justify-center bg-gray-50">
      <span class="text-[10px] text-gray-500 uppercase font-bold">Avg Coverage</span>
      <span id="sum_avg" class="text-lg font-bold text-gray-800 mt-1">-</span>
    </div>
  </div>

  <div id="loadingMon" class="hidden flex items-center gap-2 text-sm text-gray-600 mb-2 justify-center">
    <svg class="animate-spin h-5 w-5 text-blue-600" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
    <span>Memuat data...</span>
  </div>
  <div id="errMon" class="hidden mb-2 p-2 text-center text-red-600 text-sm bg-red-50 rounded border border-red-200"></div>

  <div id="monScroller" class="flex-1 min-h-0 overflow-hidden rounded border border-gray-200 bg-white shadow-sm">
    <div class="h-full overflow-auto">
      <table id="tabelMon" class="min-w-full text-sm text-left text-gray-700">
        <thead class="uppercase bg-gray-100 text-gray-600">
          <tr class="text-xs">
            <th class="px-3 py-3 sticky-top th-base text-center col-no">No</th>
            <th class="px-3 py-3 sticky-top th-base text-center col-kode">Kantor</th>
            <th class="px-3 py-3 sticky-top th-base col-nama freeze-col">Nama AO / Petugas</th>
            <th class="px-3 py-3 sticky-top th-base text-right col-num">Kelolaan (Acc)</th>
            <th class="px-3 py-3 sticky-top th-base text-right col-num">Visited (Acc)</th>
            <th class="px-3 py-3 sticky-top th-base text-right col-num">Frekuensi (X)</th>
            <th class="px-3 py-3 sticky-top th-base col-cov">Coverage Ratio</th>
          </tr>
        </thead>
        <tbody id="tbodyMon" class="divide-y divide-gray-100"></tbody>
      </table>
    </div>
  </div>
</div>

<style>
  #monScroller { --colNo: 3.5rem; --colKode: 5rem; --colNama: 14rem; --colNum: 8rem; --colCov: 12rem; }
  #tabelMon thead th.sticky-top { position: sticky; top: 0; background: #eef2f6; z-index: 80; border-bottom: 2px solid #e2e8f0; font-weight: 700; }
  #tabelMon thead th.freeze-col { position: sticky; left: 0; z-index: 90; box-shadow: 2px 0 5px rgba(0,0,0,0.05); }
  #tabelMon td.freeze-col { position: sticky; left: 0; z-index: 40; background: #fff; box-shadow: 2px 0 5px rgba(0,0,0,0.05); font-weight: 600; color: #1e40af; }
  #tabelMon { table-layout: fixed; }
  #tabelMon th, #tabelMon td { padding: .75rem .75rem; vertical-align: middle; }
  #tabelMon tbody tr:hover td { background-color: #f8fafc; }
  .col-no { width: var(--colNo); min-width: var(--colNo); }
  .col-kode { width: var(--colKode); min-width: var(--colKode); }
  .col-nama { width: var(--colNama); min-width: var(--colNama); }
  .col-num { width: var(--colNum); min-width: var(--colNum); }
  .col-cov { width: var(--colCov); min-width: var(--colCov); }
  .btn-icon { display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; }
  
  @media (max-width: 640px) {
    #tabelMon { font-size: 12px; }
    .col-no { display: none; }
    .col-kode { width: 4rem; min-width: 4rem; }
    .col-nama { width: 10rem; min-width: 10rem; }
  }
</style>

<script>
(() => {
  const API_URL = './api/kunjungan/'; 
  const nf = new Intl.NumberFormat('id-ID');
  const fmt = n => nf.format(Number(n||0));
  const escapeHtml = s => String(s||'').replace(/&/g,'&').replace(/</g,'<').replace(/>/g,'>');
  const getTodayDate = () => { const d=new Date(); return d.toISOString().split('T')[0]; };
  
  // ===========================================
  // PERBAIKAN DI SINI (Default jadi '000')
  // ===========================================
  const getKodeKantor = () => {
    // Cek NavAuth atau LocalStorage
    let kode = window.NavAuth?.branchCode || localStorage.getItem('kode_kantor');
    
    // Jika tidak ditemukan atau null, anggap PUSAT ('000')
    if (!kode || kode === 'null' || kode === 'undefined') {
      return '000';
    }
    return kode.toString().padStart(3,'0');
  };

  const apiCall = (url,opt={}) => (window.apiFetch?window.apiFetch(url,opt):fetch(url,opt));

  // ========= INIT =========
  (async () => {
    document.getElementById('tgl_harian').value = getTodayDate();
    
    // 1. Load Tanggal Closing Default
    try {
      const r = await fetch('./api/date/'); const j = await r.json();
      if(j?.data?.last_closing) document.getElementById('tgl_closing').value = j.data.last_closing;
      else {
        const d=new Date(); d.setDate(0); document.getElementById('tgl_closing').value=d.toISOString().split('T')[0];
      }
    } catch {}

    // 2. Populate Dropdown Kantor (Tunggu sampai selesai)
    const userKode = getKodeKantor();
    await populateKantorOptions(userKode);

    // 3. Auto Fetch
    fetchMonitoring(); 
  })();

  // ========= POPULATE DROPDOWN =========
  async function populateKantorOptions(userKode){
    const opt = document.getElementById('opt_kantor');
    try{
      // Jika user BUKAN Pusat (misal 001), kunci dropdown
      if(userKode && userKode !== '000'){
        opt.innerHTML=`<option value="${userKode}">${userKode}</option>`;
        opt.value = userKode; 
        opt.disabled = true; 
        return;
      }
      
      // Jika user PUSAT (000), ambil daftar cabang
      const res = await apiCall('./api/kode/',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({type:'kode_kantor'})});
      const json = await res.json(); 
      const list = Array.isArray(json.data) ? json.data : [];

      let html = `<option value="000">Konsolidasi (Semua)</option>`;
      html += `<option disabled>───────────────</option>`;
      html += `<option value="SEMARANG">Korwil Semarang</option>`;
      html += `<option value="SOLO">Korwil Solo</option>`;
      html += `<option value="BANYUMAS">Korwil Banyumas</option>`;
      html += `<option value="PEKALONGAN">Korwil Pekalongan</option>`;
      html += `<option disabled>───────────────</option>`;

      list.filter(x => x.kode_kantor && x.kode_kantor !== '000')
          .sort((a,b) => String(a.kode_kantor).localeCompare(b.kode_kantor))
          .forEach(it => {
             html += `<option value="${it.kode_kantor}">${it.kode_kantor} - ${it.nama_kantor||''}</option>`;
          });
      
      opt.innerHTML = html;
      opt.disabled = false;
      
      // PAKSA SELECT KE 000 JIKA USER PUSAT
      opt.value = '000'; 

    } catch(e) {
      console.error(e);
      opt.innerHTML = `<option value="000">Konsolidasi</option>`;
      opt.value = '000';
    }
  }

  // ========= FETCH LOGIC =========
  async function fetchMonitoring() {
    const tbody   = document.getElementById('tbodyMon');
    const loading = document.getElementById('loadingMon');
    const errEl   = document.getElementById('errMon');
    
    const account_handle = document.getElementById('tgl_closing').value;
    const harian_date    = document.getElementById('tgl_harian').value;
    
    // Ambil nilai LANGSUNG dari Dropdown (karena dropdown sudah diset valuenya di populate)
    const kode_kantor = document.getElementById('opt_kantor').value;

    if(!account_handle || !harian_date) return;

    loading.classList.remove('hidden'); errEl.classList.add('hidden'); tbody.innerHTML = ''; resetSummary();

    try {
      const body = { type: "monitoring_ao", account_handle, harian_date, kode_kantor };
      
      const res = await fetch(API_URL, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) });
      const json = await res.json().catch(() => ({}));
      if (!res.ok || json.status !== 200) throw new Error(json?.message || 'Gagal memuat data.');

      const data = json.data || {};
      renderSummary(data.summary || {});
      renderTable(data.rows || []);

    } catch (err) {
      errEl.textContent = err.message; errEl.classList.remove('hidden');
      tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-red-500">Gagal memuat data</td></tr>`;
    } finally { loading.classList.add('hidden'); }
  }

  function renderSummary(s) {
    document.getElementById('sum_ao').textContent       = fmt(s.total_ao);
    document.getElementById('sum_kelolaan').textContent = fmt(s.total_kelolaan);
    document.getElementById('sum_visited').textContent  = fmt(s.total_acc_visited);
    document.getElementById('sum_freq').textContent     = fmt(s.total_frekuensi);
    document.getElementById('sum_avg').textContent      = (s.avg_coverage || 0) + '%';
  }

  function resetSummary() {
    ['sum_ao','sum_kelolaan','sum_visited','sum_freq','sum_avg'].forEach(id => { document.getElementById(id).textContent = '-'; });
  }

  function renderTable(rows) {
    const tbody = document.getElementById('tbodyMon');
    if(rows.length === 0) { tbody.innerHTML = `<tr><td colspan="7" class="text-center py-8 text-gray-400 italic">Tidak ada data ditemukan.</td></tr>`; return; }

    let html = '';
    rows.forEach((r, i) => {
      const cov = parseFloat(r.coverage_ratio || 0);
      let barColor = 'bg-red-500'; if(cov >= 80) barColor = 'bg-emerald-500'; else if(cov >= 50) barColor = 'bg-yellow-500';

      html += `
        <tr class="border-b bg-white transition-colors hover:bg-gray-50">
          <td class="col-no text-center text-gray-500 py-3">${i + 1}</td>
          <td class="col-kode text-center font-mono text-gray-600 font-semibold py-3">${escapeHtml(r.kode_kantor)}</td>
          <td class="col-nama freeze-col border-r border-gray-100 py-3 font-medium text-gray-800">${escapeHtml(r.nama_ao)}</td>
          <td class="col-num text-right font-medium text-gray-700 py-3">${fmt(r.total_kelolaan)}</td>
          <td class="col-num text-right font-medium text-blue-600 py-3">${fmt(r.acc_visited)}</td>
          <td class="col-num text-right font-medium text-purple-600 py-3">${fmt(r.frekuensi_kunjungan)}</td>
          <td class="col-cov py-3">
            <div class="flex items-center gap-2">
              <span class="text-xs font-bold w-12 text-right ${cov>=50?'text-gray-800':'text-red-600'}">${cov.toFixed(2)}%</span>
              <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full ${barColor} transition-all duration-500" style="width: ${Math.min(cov, 100)}%"></div>
              </div>
            </div>
          </td>
        </tr>`;
    });
    tbody.innerHTML = html;
  }

  document.getElementById('filterMon').addEventListener('submit', (e) => { e.preventDefault(); fetchMonitoring(); });
})();
</script>