<!-- BUCKET DPD (Mekar + Sub-bucket chooser) -->
<div class="max-w-7xl mx-auto px-4 py-6">
  <h1 class="text-2xl font-bold mb-4">🧺 Rekap Bucket DPD</h1>

  <!-- Filter -->
  <form id="formFilterBucket" class="flex flex-wrap items-center gap-3 mb-6">
    <label for="closing_date_bucket" class="text-sm">Tanggal Closing:</label>
    <input type="date" id="closing_date_bucket" class="border rounded px-3 py-1 text-sm bg-gray-100" required>

    <label for="harian_date_bucket" class="text-sm">Tanggal Harian:</label>
    <input type="date" id="harian_date_bucket" class="border rounded px-3 py-1 text-sm" required>

    <button type="submit" class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
      🔍 Tampilkan
    </button>
  </form>

  <!-- Loading -->
  <div id="loadingBucket" class="hidden flex items-center gap-2 text-sm text-gray-600 mb-4">
    <svg class="animate-spin h-5 w-5 text-blue-600" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span>Memuat rekap bucket…</span>
  </div>

  <!-- Tabel -->
  <div class="overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-700 bg-white rounded shadow" id="tabelBucket">
      <thead class="text-xs text-gray-700 uppercase bg-gray-100">
        <tr>
          <th class="px-4 py-2 align-top">Kode</th>
          <th class="px-4 py-2 align-top">Nama Cabang</th>
          <th class="px-4 py-2 text-center" colspan="2">0–30</th>
          <th class="px-4 py-2 text-center" colspan="2">31–90</th>
          <th class="px-4 py-2 text-center" colspan="2">91–180</th>
          <th class="px-4 py-2 text-center" colspan="2">181–360</th>
          <th class="px-4 py-2 text-center" colspan="2">&gt;360</th>
          <th class="px-4 py-2 text-center" colspan="2">TOTAL ↑</th>
        </tr>
        <tr>
          <th></th><th></th>
          <th class="px-4 py-1 text-right">OSC</th>
          <th class="px-4 py-1 text-right">NOA</th>
          <th class="px-4 py-1 text-right">OSC</th>
          <th class="px-4 py-1 text-right">NOA</th>
          <th class="px-4 py-1 text-right">OSC</th>
          <th class="px-4 py-1 text-right">NOA</th>
          <th class="px-4 py-1 text-right">OSC</th>
          <th class="px-4 py-1 text-right">NOA</th>
          <th class="px-4 py-1 text-right">OSC</th>
          <th class="px-4 py-1 text-right">NOA</th>
          <th class="px-4 py-1 text-right">OSC</th>
          <th class="px-4 py-1 text-right">NOA</th>
        </tr>
        <!-- TOTAL KONSOLIDASI di bawah header -->
        <tr id="totalBucketRow" class="bg-blue-50 text-blue-800 font-semibold">
          <td class="px-4 py-2"></td>
          <td class="px-4 py-2">TOTAL KONSOLIDASI</td>
          <td class="px-4 py-2 text-right" id="ttl_osc_0_30"></td>
          <td class="px-4 py-2 text-right" id="ttl_noa_0_30"></td>
          <td class="px-4 py-2 text-right" id="ttl_osc_31_90"></td>
          <td class="px-4 py-2 text-right" id="ttl_noa_31_90"></td>
          <td class="px-4 py-2 text-right" id="ttl_osc_91_180"></td>
          <td class="px-4 py-2 text-right" id="ttl_noa_91_180"></td>
          <td class="px-4 py-2 text-right" id="ttl_osc_181_360"></td>
          <td class="px-4 py-2 text-right" id="ttl_noa_181_360"></td>
          <td class="px-4 py-2 text-right" id="ttl_osc_gt_360"></td>
          <td class="px-4 py-2 text-right" id="ttl_noa_gt_360"></td>
          <td class="px-4 py-2 text-right" id="ttl_osc_total"></td>
          <td class="px-4 py-2 text-right" id="ttl_noa_total"></td>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<!-- Modal Sub-bucket -->
<div id="modalSubBucket" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm items-center justify-center">
  <div class="bg-white rounded-lg shadow max-w-3xl w-full">
    <div class="flex items-center justify-between p-4 border-b">
      <h3 id="subTitle" class="text-lg font-semibold">Sub-bucket</h3>
      <button onclick="closeSubModal()" class="text-gray-500 hover:text-gray-700 text-xl">✕</button>
    </div>
    <div id="subBody" class="p-4"></div>
  </div>
</div>

<script>
  /* ===== Helpers ===== */
  const nf = new Intl.NumberFormat('id-ID');
  const fmt = n => nf.format(Number(n || 0));
  function setText(id,val){ const el=document.getElementById(id); if(el) el.textContent = val; }

  // Sub-bucket yang ditampilkan di modal
  const SUB_BUCKETS = {
    '0_30': [
      {key:'0',       label:'0',       noa:'noa_0',       osc:'baki_0'},
      {key:'1_30',    label:'1–30',    noa:'noa_1_30',    osc:'baki_1_30'},
    ],
    '31_90': [
      {key:'31_60',   label:'31–60',   noa:'noa_31_60',   osc:'baki_31_60'},
      {key:'61_90',   label:'61–90',   noa:'noa_61_90',   osc:'baki_61_90'},
    ],
    '91_180': [
      {key:'91_120',  label:'91–120',  noa:'noa_91_120',  osc:'baki_91_120'},
      {key:'121_150', label:'121–150', noa:'noa_121_150', osc:'baki_121_150'},
      {key:'151_180', label:'151–180', noa:'noa_151_180', osc:'baki_151_180'},
    ],
    '181_360': [
      {key:'181_210', label:'181–210', noa:'noa_181_210', osc:'baki_181_210'},
      {key:'211_240', label:'211–240', noa:'noa_211_240', osc:'baki_211_240'},
      {key:'241_270', label:'241–270', noa:'noa_241_270', osc:'baki_241_270'},
      {key:'271_300', label:'271–300', noa:'noa_271_300', osc:'baki_271_300'},
      {key:'301_330', label:'301–330', noa:'noa_301_330', osc:'baki_301_330'},
      {key:'331_360', label:'331–360', noa:'noa_331_360', osc:'baki_331_360'},
    ],
    'gt_360': [
      {key:'gt_360',  label:'>360',    noa:'noa_gt_360',  osc:'baki_gt_360'},
    ]
  };

  // Konversi key sub-bucket → {min, max}
  function keyToRange(key) {
    switch (key) {
      case '0':        return {min: 0,   max: 0};
      case '1_30':     return {min: 1,   max: 30};
      case '31_60':    return {min: 31,  max: 60};
      case '61_90':    return {min: 61,  max: 90};
      case '91_120':   return {min: 91,  max: 120};
      case '121_150':  return {min: 121, max: 150};
      case '151_180':  return {min: 151, max: 180};
      case '181_210':  return {min: 181, max: 210};
      case '211_240':  return {min: 211, max: 240};
      case '241_270':  return {min: 241, max: 270};
      case '271_300':  return {min: 271, max: 300};
      case '301_330':  return {min: 301, max: 330};
      case '331_360':  return {min: 331, max: 360};
      case 'gt_360':   return {min: 361, max: null};
      default:         return null;
    }
  }

  let bucketRows = [];
  let bucketTotal = null;

  // INIT tanggal: closing = last_closing (dikunci), harian = last_created
  (async () => {
    const d = await getLastDates();
    if (!d) return;

    const closingEl = document.getElementById('closing_date_bucket');
    closingEl.value = d.last_closing;        // default bulan kemarin
    // closingEl.setAttribute('disabled','');   // tidak bisa diubah

    document.getElementById('harian_date_bucket').value = d.last_created;

    fetchBucket(d.last_closing, d.last_created);
  })();

  async function getLastDates() {
    try {
      const r = await fetch('./api/date/', { method: 'GET' });
      const j = await r.json();
      return j.data || null;
    } catch { return null; }
  }

  document.getElementById('formFilterBucket').addEventListener('submit', (e) => {
    e.preventDefault();
    const closing = document.getElementById('closing_date_bucket').value;
    const harian  = document.getElementById('harian_date_bucket').value;
    fetchBucket(closing, harian);
  });

  async function fetchBucket(closing_date, harian_date) {
    document.getElementById('loadingBucket').classList.remove('hidden');
    try {
      const res = await fetch('./api/bucket/', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ type:'Backet', closing_date, harian_date })
      });
      const json = await res.json();
      const data = json.data || [];

      bucketTotal = data.find(x => (x.nama_cabang || '').toUpperCase()==='TOTAL' || x.kode_cabang===null) || null;
      bucketRows  = data.filter(x => x.kode_cabang !== null);

      // default urut 001 → 028
      bucketRows.sort((a,b) => parseInt(a.kode_cabang||'999',10) - parseInt(b.kode_cabang||'999',10));

      renderTotalHeader(bucketTotal);
      renderTable(bucketRows);
    } catch (e) {
      console.error('Gagal fetch bucket:', e);
      const tbody = document.querySelector('#tabelBucket tbody');
      tbody.innerHTML = `<tr><td colspan="14" class="px-4 py-3 text-red-600">Gagal memuat data bucket.</td></tr>`;
    } finally {
      document.getElementById('loadingBucket').classList.add('hidden');
    }
  }

  function renderTotalHeader(t) {
    if (!t) return;
    setText('ttl_osc_0_30',   fmt(t.baki_0_30));    setText('ttl_noa_0_30',   fmt(t.noa_0_30));
    setText('ttl_osc_31_90',  fmt(t.baki_31_90));   setText('ttl_noa_31_90',  fmt(t.noa_31_90));
    setText('ttl_osc_91_180', fmt(t.baki_91_180));  setText('ttl_noa_91_180', fmt(t.noa_91_180));
    setText('ttl_osc_181_360',fmt(t.baki_181_360)); setText('ttl_noa_181_360',fmt(t.noa_181_360));
    setText('ttl_osc_gt_360', fmt(t.baki_gt_360));  setText('ttl_noa_gt_360', fmt(t.noa_gt_360));
    setText('ttl_osc_total',  fmt(t.baki_total));   setText('ttl_noa_total',  fmt(t.noa_total));
  }

  function renderTable(rows) {
    const tbody = document.querySelector('#tabelBucket tbody');
    const closing = document.getElementById('closing_date_bucket').value;
    const harian  = document.getElementById('harian_date_bucket').value;

    let html = '';
    for (const r of rows) {
      const k = r.kode_cabang || '-';
      const n = r.nama_cabang || '-';

      const open = (bucketKey) =>
        `openSubBuckets('${k}','${bucketKey}','${closing}','${harian}')`;

      html += `
        <tr class="border-b hover:bg-gray-50">
          <td class="px-4 py-3 text-center">${k}</td>
          <td class="px-4 py-3">${n}</td>

          <td class="px-4 py-3 text-right">${fmt(r.baki_0_30)}</td>
          <td class="px-4 py-3 text-right">
            <a href="#" class="text-blue-600 hover:underline" onclick="event.preventDefault(); ${open('0_30')}">
              ${fmt(r.noa_0_30)}
            </a>
          </td>

          <td class="px-4 py-3 text-right">${fmt(r.baki_31_90)}</td>
          <td class="px-4 py-3 text-right">
            <a href="#" class="text-blue-600 hover:underline" onclick="event.preventDefault(); ${open('31_90')}">
              ${fmt(r.noa_31_90)}
            </a>
          </td>

          <td class="px-4 py-3 text-right">${fmt(r.baki_91_180)}</td>
          <td class="px-4 py-3 text-right">
            <a href="#" class="text-blue-600 hover:underline" onclick="event.preventDefault(); ${open('91_180')}">
              ${fmt(r.noa_91_180)}
            </a>
          </td>

          <td class="px-4 py-3 text-right">${fmt(r.baki_181_360)}</td>
          <td class="px-4 py-3 text-right">
            <a href="#" class="text-blue-600 hover:underline" onclick="event.preventDefault(); ${open('181_360')}">
              ${fmt(r.noa_181_360)}
            </a>
          </td>

          <td class="px-4 py-3 text-right">${fmt(r.baki_gt_360)}</td>
          <td class="px-4 py-3 text-right">
            <a href="#" class="text-blue-600 hover:underline" onclick="event.preventDefault(); ${open('gt_360')}">
              ${fmt(r.noa_gt_360)}
            </a>
          </td>

          <td class="px-4 py-3 text-right">${fmt(r.baki_total)}</td>
          <td class="px-4 py-3 text-right">${fmt(r.noa_total)}</td>
        </tr>`;
    }
    tbody.innerHTML = html;
  }

  /* ===== Modal Sub-bucket ===== */
  function openSubBuckets(kode_kantor, bucket_key, closing_date, _harian_date) {
    const row = bucketRows.find(r => r.kode_cabang === kode_kantor);
    if (!row) return;

    const items = SUB_BUCKETS[bucket_key] || [];
    const modal = document.getElementById('modalSubBucket');
    const title = document.getElementById('subTitle');
    const body  = document.getElementById('subBody');

    title.textContent = `Sub-bucket ${kode_kantor} • ${row.nama_cabang} • ${labelParent(bucket_key)}`;

    let html = `<div class="grid grid-cols-2 sm:grid-cols-3 gap-3">`;
    for (const it of items) {
      const noa = Number(row[it.noa] || 0);
      const osc = Number(row[it.osc] || 0);
      const disabled = noa === 0 ? 'pointer-events-none opacity-50' : '';
      html += `
        <div class="border rounded-lg p-3">
          <div class="text-xs text-gray-500 mb-1">${it.label}</div>
          <div class="flex justify-between"><div class="text-gray-600">OSC</div><div class="font-semibold">${fmt(osc)}</div></div>
          <div class="flex justify-between">
            <div class="text-gray-600">NOA</div>
            <div>
              <a href="./detail_bucket"
                 class="text-blue-600 hover:underline ${disabled}"
                 onclick="event.preventDefault(); goDetailBucket('${kode_kantor}','${it.key}','${closing_date}')">
                 ${fmt(noa)}
              </a>
            </div>
          </div>
        </div>`;
    }
    html += `</div>`;

    body.innerHTML = html;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  }

  function closeSubModal(){
    const modal = document.getElementById('modalSubBucket');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }

  function labelParent(key){
    return ({ '0_30':'0–30','31_90':'31–90','91_180':'91–180','181_360':'181–360','gt_360':'>360' })[key] || key;
  }

  // Simpan PARAM untuk halaman detail (pakai min/max, TANPA bucket)
  function goDetailBucket(kode_kantor, sub_key, closing_date) {
    const range = keyToRange(sub_key);
    if (!range) return;

    localStorage.setItem('bucket_detail_params', JSON.stringify({
      type: 'detail_bucket',
      closing_date,
      kode_kantor,
      min: range.min,
      ...(range.max !== null ? { max: range.max } : {})
    }));

    window.location.href = './detail_bucket';
  }
</script>
