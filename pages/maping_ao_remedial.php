<!-- 📋 Mapping AO Remedial — My List -->
<div class="max-w-7xl mx-auto px-4 py-6">
  <h1 class="text-2xl font-bold mb-4">📋 Mapping AO Remedial — My List</h1>

  <!-- Loading -->
  <div id="loadingMa" class="hidden flex items-center gap-2 text-sm text-gray-600 mb-4">
    <svg class="animate-spin h-5 w-5 text-blue-600" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span>Memuat data Mapping Account…</span>
  </div>

  <div id="errMa" class="hidden mb-3 p-3 rounded border border-red-200 text-red-700 bg-red-50 text-sm"></div>

  <!-- Tabel -->
  <div class="overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-700 bg-white rounded shadow" id="tabelMa">
      <thead class="text-xs text-gray-700 uppercase bg-gray-100">
        <tr>
          <th class="px-4 py-2">No</th>
          <th class="px-4 py-2">No Rekening</th>
          <th class="px-4 py-2">Nama Debitur</th>
          <th class="px-4 py-2">Alamat</th>
          <th class="px-4 py-2 text-right">Baki Debet</th>
          <th class="px-4 py-2 text-center">Tgl Realisasi (dd)</th>
          <th class="px-4 py-2 text-right">Tunggakan Pokok</th>
          <th class="px-4 py-2 text-right">Tunggakan Bunga</th>
          <th class="px-4 py-2 text-right">CKPN</th>
          <th class="px-4 py-2">Bucket</th>
          <th class="px-4 py-2 text-right">Plan CKPN</th>
          <th class="px-4 py-2 text-right">Pemulihan Pembentukan</th>
          <th class="px-4 py-2 text-right">BD Harian</th>
          <th class="px-4 py-2 text-center">DPD Harian</th>
          <th class="px-4 py-2 text-right">Angs. Pokok</th>
          <th class="px-4 py-2 text-right">Angs. Bunga</th>
          <th class="px-4 py-2 text-center">Tgl Trans (dd/mm/yy)</th>
        </tr>
      </thead>
      <tbody id="bodyMa"></tbody>

      <tfoot class="bg-gray-100">
        <tr>
          <td colspan="4" class="px-4 py-2 font-semibold">Total</td>
          <td id="tot_bd" class="px-4 py-2 text-right font-semibold">-</td>
          <td class="px-4 py-2"></td>
          <td id="tot_tp" class="px-4 py-2 text-right font-semibold">-</td>
          <td id="tot_tb" class="px-4 py-2 text-right font-semibold">-</td>
          <td id="tot_ckpn" class="px-4 py-2 text-right font-semibold">-</td>
          <td class="px-4 py-2"></td>
          <td id="tot_plan_ckpn" class="px-4 py-2 text-right font-semibold">-</td>
          <td id="tot_pemulihan" class="px-4 py-2 text-right font-semibold">-</td>
          <td id="tot_bd_harian" class="px-4 py-2 text-right font-semibold">-</td>
          <td class="px-4 py-2"></td>
          <td id="tot_ap" class="px-4 py-2 text-right font-semibold">-</td>
          <td id="tot_ab" class="px-4 py-2 text-right font-semibold">-</td>
          <td class="px-4 py-2"></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<script>
  // ========== Helpers ==========
  const fmt = n => Number(n||0).toLocaleString('id-ID');

  // Ambil token dari navbar / localStorage (tanpa "Bearer")
  function getToken() {
    const t = (window.AUTH_TOKEN || localStorage.getItem('dpk_token') || '').trim();
    return t.replace(/^Bearer\s+/i, '');
  }

  // Bangun URL absolut dari path relatif (anti nyasar ke root)
  const API_BUCKET = new URL('./api/bucket/', window.location.href).toString();

  // ========== INIT seperti pola flow_par ==========
  (async () => {
    // tidak perlu tanggal untuk mapping; langsung fetch
    await fetchMapping();
  })();

  // ========== Fetch ==========
  function fetchMapping() {
    const url   = API_BUCKET;              // contoh: http://localhost/e-pipelane/api/bucket/
    const token = getToken();

    const tbody = document.getElementById('bodyMa');
    const errEl = document.getElementById('errMa');
    if (errEl) { errEl.classList.add('hidden'); errEl.textContent=''; }
    if (tbody)  tbody.innerHTML = `<tr><td colspan="17" class="px-2 py-4 text-center text-gray-500">Memuat...</td></tr>`;

    // log debug
    console.log('[MAPING] href   =', location.href);
    console.log('[MAPING] target =', url);
    console.log('[MAPING] token? =', token ? 'YA' : 'TIDAK');

    fetch(url, {
      method : 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': token         // TANPA "Bearer"
      },
      body: JSON.stringify({ type: 'maping_account' })
    })
    .then(res => res.json().catch(()=>({})).then(j => ({ ok:res.ok, status:res.status, json:j })))
    .then(({ ok, status, json }) => {
      console.log('[MAPING] status =', status);
      console.log('[MAPING] json   =', json);

      if (!ok) throw new Error(json?.message || ('HTTP '+status));
      const rows = Array.isArray(json?.data) ? json.data : [];
      renderTable(rows);
    })
    .catch(err => {
      console.error('[MAPING] ERROR =', err);
      if (tbody) tbody.innerHTML = '';
      if (errEl) { errEl.textContent = err?.message || 'Gagal memuat data'; errEl.classList.remove('hidden'); }
    });
  }

  // ========== Render ==========
  function renderTable(rows){
    const tbody = document.getElementById('bodyMa');
    if (!tbody) return;

    if (!Array.isArray(rows) || rows.length === 0) {
      tbody.innerHTML = `<tr><td colspan="17" class="px-2 py-6 text-center text-gray-500">Tidak ada data</td></tr>`;
      setTotals([]);
      return;
    }

    tbody.innerHTML = rows.map((r,i)=>`
      <tr class="border-b hover:bg-gray-50">
        <td class="px-2 py-1 border text-center">${i+1}</td>
        <td class="px-2 py-1 border font-mono whitespace-nowrap">${r.no_rekening||''}</td>
        <td class="px-2 py-1 border">${r.nama_debitur||''}</td>
        <td class="px-2 py-1 border">${r.alamat||''}</td>
        <td class="px-2 py-1 border text-right">${fmt(r.baki_debet)}</td>
        <td class="px-2 py-1 border text-center">${r.tgl_realisasi||''}</td>
        <td class="px-2 py-1 border text-right">${fmt(r.tunggakan_pokok)}</td>
        <td class="px-2 py-1 border text-right">${fmt(r.tunggakan_bunga)}</td>
        <td class="px-2 py-1 border text-right">${fmt(r.ckpn)}</td>
        <td class="px-2 py-1 border">${r.bucket||''}</td>
        <td class="px-2 py-1 border text-right">${fmt(r.plan_ckpn)}</td>
        <td class="px-2 py-1 border text-right">${fmt(r.pemulihan_pembentukan)}</td>
        <td class="px-2 py-1 border text-right">${fmt(r.baki_debet_harian)}</td>
        <td class="px-2 py-1 border text-center">${r.hari_menunggak_harian ?? ''}</td>
        <td class="px-2 py-1 border text-right">${fmt(r.angsuran_pokok)}</td>
        <td class="px-2 py-1 border text-right">${fmt(r.angsuran_bunga)}</td>
        <td class="px-2 py-1 border text-center">${r.tgl_trans || ''}</td>
      </tr>
    `).join('');

    setTotals(rows);
  }

  function setTotals(rows){
    const sum = (k) => rows.reduce((a,r)=>a+(Number(r?.[k])||0),0);
    const set = (id,v)=>{ const el=document.getElementById(id); if(el) el.textContent = fmt(v); };
    set('tot_bd',            sum('baki_debet'));
    set('tot_tp',            sum('tunggakan_pokok'));
    set('tot_tb',            sum('tunggakan_bunga'));
    set('tot_ckpn',          sum('ckpn'));
    set('tot_plan_ckpn',     sum('plan_ckpn'));
    set('tot_pemulihan',     sum('pemulihan_pembentukan'));
    set('tot_bd_harian',     sum('baki_debet_harian'));
    set('tot_ap',            sum('angsuran_pokok'));
    set('tot_ab',            sum('angsuran_bunga'));
  }

  // tombol refresh (opsional)
  document.getElementById('btnRefreshMa')?.addEventListener('click', fetchMapping);
</script>


