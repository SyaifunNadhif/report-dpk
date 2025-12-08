<!-- rekap_ph_bucket.html — Fix tinggi tabel (selalu terlihat baris terakhir) + sticky TOTAL -->
<div class="max-w-7xl mx-auto px-4 py-4 h-screen flex flex-col">
  <!-- Toolbar -->
  <div class="hdr flex flex-wrap items-center gap-2 mb-3">
    <h1 class="text-xl sm:text-2xl font-bold flex items-center gap-2">
      <span>📗</span><span>Rekap Saldo PH (per Bucket Tahun)</span>
    </h1>

    <form id="formFilterRekap" class="ml-auto flex items-center gap-2">
      <label for="closing_date" class="text-sm text-slate-700 hidden sm:inline">Closing:</label>
      <input type="date" id="closing_date" class="f-inp" required>
      <button type="submit" class="f-btn" title="Tampilkan">🔍</button>
    </form>
  </div>

  <!-- Loading -->
  <div id="loadingRekap" class="hidden flex items-center gap-2 text-sm text-gray-600 mb-2">
    <svg class="animate-spin h-5 w-5 text-emerald-600" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span>Memuat rekap saldo PH…</span>
  </div>

  <!-- SCROLLER -->
  <div id="rekScroller" class="flex-1 min-h-0 overflow-hidden rounded border border-gray-200 bg-white">
    <div class="h-full overflow-auto">
      <table id="tabelRekap" class="min-w-full text-left text-gray-700">
        <thead id="rekHead" class="uppercase">
          <tr class="head-1">
            <th class="px-2.5 py-2 sticky-h freeze-1 col-kode" rowspan="2">KODE</th>
            <th class="px-2.5 py-2 sticky-h freeze-2 col-nama" rowspan="2">NAMA CABANG</th>
            <th class="px-2 py-2 text-center sticky-h grp" colspan="2">&lt;2019</th>
            <th class="px-2 py-2 text-center sticky-h grp" colspan="2">2019</th>
            <th class="px-2 py-2 text-center sticky-h grp" colspan="2">2023</th>
            <th class="px-2 py-2 text-center sticky-h grp" colspan="2">2024</th>
            <th class="px-2 py-2 text-center sticky-h grp" colspan="2">2025</th>
            <th class="px-2 py-2 text-center sticky-h grp" colspan="2">TOTAL</th>
          </tr>
          <tr class="head-2 text-xs">
            <th class="px-2 py-1 sticky-h">OSC</th><th class="px-2 py-1 sticky-h">NOA</th>
            <th class="px-2 py-1 sticky-h">OSC</th><th class="px-2 py-1 sticky-h">NOA</th>
            <th class="px-2 py-1 sticky-h">OSC</th><th class="px-2 py-1 sticky-h">NOA</th>
            <th class="px-2 py-1 sticky-h">OSC</th><th class="px-2 py-1 sticky-h">NOA</th>
            <th class="px-2 py-1 sticky-h">OSC</th><th class="px-2 py-1 sticky-h">NOA</th>
            <th class="px-2 py-1 sticky-h">OSC</th><th class="px-2 py-1 sticky-h">NOA</th>
          </tr>
        </thead>

        <!-- TOTAL tepat di bawah header (sticky) -->
        <tbody id="rekTotalRow"></tbody>

        <!-- BODY data -->
        <tbody id="rekBody"></tbody>
      </table>
    </div>
  </div>
</div>

<style>
  /* Controls */
  .f-inp{ border:1px solid #cbd5e1; border-radius:.6rem; padding:.45rem .7rem; font-size:14px; background:#fff; }
  .f-btn{ width:40px; height:40px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center;
          background:#10b981; color:#fff; box-shadow:0 6px 14px rgba(16,185,129,.25); }
  .f-btn:hover{ background:#059669; }

  *{ box-sizing:border-box; }
  body{ overflow:hidden; }

  /* ====== Tinggi scroller + spacer bawah biar baris terakhir terlihat ====== */
  #rekScroller{
    /* variabel diupdate dari JS */
    --headH: 56px;   /* total tinggi thead (2 baris) */
    --totalH: 34px;  /* tinggi baris TOTAL */
    --safeBottom: 52px; /* ruang aman bawah */
  }
  @supports(padding:max(0px)){ #rekScroller{ --safeBottom: max(52px, env(safe-area-inset-bottom)); } }

  /* ====== Tabel tampilan compact ====== */
  #tabelRekap{ table-layout:fixed; border-collapse:separate; border-spacing:0; font-size:12.5px; }
  #tabelRekap th, #tabelRekap td{ border-bottom:1px solid #eef2f7; }

  /* Header hijau + garis pemisah */
  #tabelRekap thead th{
    position:sticky; top:0; z-index:88;
    background:#d9ead3 !important; color:#0f5132;
    border-bottom:1px solid #b7d4c1;
  }
  #tabelRekap thead .grp{ border-left:1px solid #b7d4c1; }
  #tabelRekap thead th.sticky-h.freeze-1{ left:0; z-index:92; }
  #tabelRekap thead th.sticky-h.freeze-2{ left:var(--colKode); z-index:91; }
  #tabelRekap thead tr.head-2 th{ top: calc(var(--headRow1, 28px)); } /* baris 2 menempel di bawah baris 1 */

  /* Freeze kiri (body) */
  :root{ --colKode: 4.6rem; --colNama: 12.5rem; }
  #tabelRekap .col-kode{ width:var(--colKode); min-width:var(--colKode); text-align:center; }
  #tabelRekap .col-nama{ width:var(--colNama); min-width:var(--colNama); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  #tabelRekap td.freeze-1{ position:sticky; left:0; z-index:41; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06); }
  #tabelRekap td.freeze-2{ position:sticky; left:var(--colKode); z-index:40; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06); }

  /* Lebar kolom angka ringkas */
  #tabelRekap td.num, #tabelRekap th.num{ width:7.8rem; min-width:6.8rem; text-align:right; }
  #tabelRekap td.noa, #tabelRekap th.noa{ width:5.8rem; min-width:5.2rem; text-align:right; }

  /* TOTAL sticky tepat di bawah header */
  #tabelRekap tbody tr.sticky-total td{
    position:sticky; top:var(--headH); background:#eaf7ea; color:#065f46; z-index:70; border-bottom:1px solid #a7d7c1;
  }
  #tabelRekap tbody tr.sticky-total td.freeze-1{ z-index:93; }
  #tabelRekap tbody tr.sticky-total td.freeze-2{ z-index:92; }

  /* Spacer bawah agar baris akhir tidak ketutup taskbar/dock */
  #rekBody::after{ content:""; display:block; height: calc(var(--headH) + var(--totalH) + var(--safeBottom)); }

  /* Hover */
  #tabelRekap tbody tr:hover td{ background:#f9fafb; }

  /* Responsive */
  @media (max-width:640px){
    #tabelRekap{ font-size:11.5px; }
    :root{ --colNama: 10.5rem; }
    #tabelRekap td.num, #tabelRekap th.num{ width:6.6rem; min-width:6rem; }
    #tabelRekap td.noa, #tabelRekap th.noa{ width:5.2rem; min-width:4.8rem; }
  }

  /* Hilangkan celah 1px antara header & TOTAL */
  #tabelRekap thead th{ border-bottom:0 !important; }
  #tabelRekap tbody tr.sticky-total td{ top: calc(var(--headH) - 1px); }
  #rekBody::after{ height: calc(var(--headH) + var(--totalH) + var(--safeBottom) - 1px); }
</style>

<script>
  // ===== Formatter =====
  const nfID = new Intl.NumberFormat('id-ID');
  const rp = (n)=> nfID.format(Number(n||0));
  const BUCKETS = ['< 2019','2019','2023','2024','2025'];

  // ===== Sticky & scroller sizing =====
  function setHeadHeights(){
    const thead = document.getElementById('rekHead');
    const row1  = thead?.querySelector('.head-1');
    const holder= document.getElementById('rekScroller');
    if (!thead || !holder) return;
    const h1 = row1?.offsetHeight || 28;
    const hTot = document.querySelector('#tabelRekap tr.sticky-total')?.offsetHeight || 34;
    holder.style.setProperty('--headRow1', h1 + 'px');
    holder.style.setProperty('--headH', thead.offsetHeight + 'px');
    holder.style.setProperty('--totalH', hTot + 'px');
  }
  function sizeScroller(){
    const wrap = document.getElementById('rekScroller'); if(!wrap) return;
    const top = wrap.getBoundingClientRect().top;
    const GAP = 10;
    wrap.style.height = Math.max(260, window.innerHeight - top - GAP) + 'px';
  }
  window.addEventListener('resize', ()=>{ setHeadHeights(); sizeScroller(); });
  new ResizeObserver(()=> setHeadHeights()).observe(document.getElementById('rekHead'));

  // ===== Init tanggal closing (ambil terakhir dari API) =====
  (async () => {
    const last = await getLastClosing();
    if (last) closing_date.value = last;
    fetchRekap();
    setHeadHeights(); sizeScroller();
  })();

  async function getLastClosing() {
    try{
      const r = await fetch('./api/date/', { method:'POST' });
      const j = await r.json();
      return j?.data?.last_created || new Date().toISOString().slice(0,10);
    }catch{ return new Date().toISOString().slice(0,10); }
  }

  // ===== Events =====
  document.getElementById('formFilterRekap').addEventListener('submit', (e)=> {
    e.preventDefault();
    fetchRekap();
  });

  // ===== Fetch & Render =====
  let rowsRaw = [];

  function fetchRekap(){
    const closing = closing_date.value;
    if (!closing) return;
    loadingRekap.classList.remove('hidden');

    fetch('./api/hapus_buku/', {
      method:'POST',
      headers:{ 'Content-Type': 'application/json' },
      body: JSON.stringify({ type: 'saldo ph', closing_date: closing })
    })
    .then(r => r.json())
    .then(res => {
      rowsRaw = Array.isArray(res.data) ? res.data : [];
      const { total, branches } = pivotBuckets(rowsRaw);
      renderRekapTable(branches, total, closing);
    })
    .catch(() => {
      rekBody.innerHTML = `<tr><td colspan="14" class="px-4 py-3 text-red-600">Gagal memuat data.</td></tr>`;
      rekTotalRow.innerHTML = '';
    })
    .finally(()=> { loadingRekap.classList.add('hidden'); setHeadHeights(); sizeScroller(); setTimeout(()=>{ setHeadHeights(); sizeScroller(); }, 50);});
  }

  function pivotBuckets(rows){
    // Map per cabang
    const map = new Map();
    // Total konsolidasi (baris TOTAL)
    const total = initRow('TOTAL', 'KONSOLIDASI');

    rows.forEach(r => {
      const kode = r.kode_kantor;
      const nama = r.nama_kantor || r.nama_cabang || '';
      const buck = r.bucket_tahun || null;
      const noa  = Number(r.noa || 0);
      const osc  = Number(r.saldo_ph || 0);

      if (kode === 'TOTAL'){
        if (buck && BUCKETS.includes(buck)){
          total[buck].noa += noa;
          total[buck].osc += osc;
        }
        return;
      }

      if (!map.has(kode)) map.set(kode, initRow(kode, nama));
      const row = map.get(kode);
      if (buck && BUCKETS.includes(buck)){
        row[buck].noa += noa;
        row[buck].osc += osc;
      }
    });

    // hitung total per row
    const branches = [...map.values()]
      .map(r => withTotals(r))
      .sort((a,b) => Number(a.kode.replace(/\D/g,'')) - Number(b.kode.replace(/\D/g,'')));

    withTotals(total);
    return { total, branches };
  }

  function initRow(kode, nama){
    const r = { kode, nama };
    BUCKETS.forEach(b => r[b] = { noa:0, osc:0 });
    r.TOTAL = { noa:0, osc:0 };
    return r;
  }

  function withTotals(row){
    let tNoa = 0, tOsc = 0;
    BUCKETS.forEach(b => { tNoa += row[b].noa; tOsc += row[b].osc; });
    row.TOTAL.noa = tNoa; row.TOTAL.osc = tOsc;
    return row;
  }

  function renderRekapTable(branches, totalRow, closing){
    // TOTAL sticky row di bawah header
    const t = totalRow;
    rekTotalRow.innerHTML = `
      <tr class="sticky-total font-semibold">
        <td class="px-2.5 py-1.5 freeze-1 col-kode"></td>
        <td class="px-2.5 py-1.5 freeze-2 col-nama">TOTAL</td>
        ${BUCKETS.map(b => `
          <td class="px-2 py-1.5 num">${rp(t[b].osc)}</td>
          <td class="px-2 py-1.5 noa">${rp(t[b].noa)}</td>
        `).join('')}
        <td class="px-2 py-1.5 num">${rp(t.TOTAL.osc)}</td>
        <td class="px-2 py-1.5 noa">${rp(t.TOTAL.noa)}</td>
      </tr>
    `;

    // Body
    let html = '';
    for (const r of branches){
      html += `
        <tr class="border-b hover:bg-gray-50">
          <td class="px-2.5 py-1.5 freeze-1 col-kode">${String(r.kode||'-').padStart(3,'0')}</td>
          <td class="px-2.5 py-1.5 freeze-2 col-nama" title="${r.nama||'-'}">${r.nama||'-'}</td>
          ${BUCKETS.map(b => cellPair(r, b, r.kode, closing)).join('')}
          <td class="px-2 py-1.5 num">${rp(r.TOTAL.osc)}</td>
          <td class="px-2 py-1.5 noa">${rp(r.TOTAL.noa)}</td>
        </tr>`;
    }
    rekBody.innerHTML = html;

    setHeadHeights(); sizeScroller();
    setTimeout(()=>{ setHeadHeights(); sizeScroller(); }, 40);
  }

  function cellPair(r, bucket, kode, closing){
    const osc = r[bucket].osc || 0;
    const noa = r[bucket].noa || 0;
    // NOA klik ke detail hanya bila > 0
    const link = noa > 0
      ? `<a class="text-emerald-700 hover:underline"
            href="./detail_ph_bucket"
            onclick="event.preventDefault(); goDetail('${kode}','${bucket}','${closing}')">${rp(noa)}</a>`
      : `<span class="text-gray-400">${rp(noa)}</span>`;
    return `
      <td class="px-2 py-1.5 num">${rp(osc)}</td>
      <td class="px-2 py-1.5 noa">${link}</td>`;
  }

  // ===== NAV helper (store → go) =====
  function goDetail(kode_kantor, bucket, created){
    localStorage.setItem('hapus_buku_params', JSON.stringify({ kode_kantor, bucket, created }));
    window.location.href = './detail_ph_bucket';
  }
  window.goDetail = goDetail; // expose for inline onclick
</script>
