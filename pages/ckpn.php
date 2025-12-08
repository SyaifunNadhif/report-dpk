<div class="max-w-7xl mx-auto px-4 py-4 h-screen flex flex-col">
  <!-- Header + toolbar -->
  <div id="ckpnHeader" class="flex flex-col sm:flex-row items-start sm:items-center gap-2 mb-3">
    <h1 class="ckpn-title text-xl font-bold flex items-center gap-2">
      <span>💰</span><span>Rekap CKPN Harian</span>
    </h1>

    <!-- Filter di kanan -->
    <form id="formFilterCkpn"
          class="ckpn-filter self-end sm:self-auto sm:ml-auto flex items-center gap-2">
      <label for="harian_date_ckpn"
             class="lbl hidden sm:inline-block text-sm text-gray-700">Tanggal Harian:</label>

      <input type="date" id="harian_date_ckpn"
             class="border rounded px-3 py-1 text-sm w-44 sm:w-56" required>

      <button type="submit"
              class="icon-btn w-10 h-10 rounded-full inline-flex items-center justify-center
                     bg-blue-600 text-white hover:bg-blue-700 shadow-md"
              title="Tampilkan">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="7"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
      </button>
    </form>
  </div>

  <!-- Loading -->
  <div id="loadingCkpn" class="hidden flex items-center gap-2 text-sm text-gray-600 mb-2">
    <svg class="animate-spin h-5 w-5 text-blue-600" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span>Memuat data CKPN...</span>
  </div>

  <!-- Scroller -->
  <div id="scroller" class="flex-1 min-h-0 overflow-hidden rounded border border-gray-200 bg-white">
    <div id="scrollerInner" class="h-full overflow-auto">
      <table id="tabelCkpn" class="min-w-full text-sm text-left text-gray-700">
        <thead class="uppercase">
          <tr id="headerRow1" class="text-xs">
            <th class="px-4 py-2 sticky-1 freeze-1 col1 col-kode" rowspan="2">KODE KANTOR</th>
            <th class="px-4 py-2 sticky-1 freeze-2 col2 col-nama" rowspan="2">NAMA KANTOR</th>

            <th class="px-4 py-2 text-right sticky-1 col-totalnoa" rowspan="2">TOTAL NOA</th>
            <th class="px-4 py-2 text-right sticky-1 col-noaasset" rowspan="2">NOA ASET BAIK</th>

            <th class="px-4 py-2 text-center sticky-1" colspan="2">INDIVIDUAL</th>
            <th class="px-4 py-2 text-center sticky-1" colspan="2">COLLECTIVE</th>
            <th class="px-4 py-2 text-right sticky-1" rowspan="2">CKPN TOTAL</th>
          </tr>
          <tr id="headerRow2" class="text-xs">
            <th class="px-4 py-2 text-right sticky-2">NOA</th>
            <th class="px-4 py-2 text-right sticky-2">NILAI CKPN</th>
            <th class="px-4 py-2 text-right sticky-2">NOA</th>
            <th class="px-4 py-2 text-right sticky-2">NILAI CKPN</th>
          </tr>
        </thead>
        <tbody><!-- render via JS --></tbody>
      </table>
    </div>
  </div>
</div>

<style>
  body { overflow: hidden; }

  /* ====== Lebar & freeze 2 kolom kiri (desktop) ====== */
  :root{
    --col1w: 5rem;     /* KODE KANTOR */
    --col2w: 16rem;    /* NAMA KANTOR */
  }

  #tabelCkpn .col1{ width:var(--col1w); min-width:var(--col1w); }
  #tabelCkpn .col2{ width:var(--col2w); min-width:var(--col2w); }

  /* Truncate Nama Kantor supaya tidak makan tempat */
  #tabelCkpn th.col-nama,
  #tabelCkpn td.col-nama{
    max-width: var(--col2w);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  /* Freeze kiri */
  #tabelCkpn .freeze-1{ position: sticky; left: 0; z-index: 41; background:#fff; box-shadow: 1px 0 0 rgba(0,0,0,.06); }
  #tabelCkpn .freeze-2{ position: sticky; left: var(--col1w); z-index: 40; background:#fff; box-shadow: 1px 0 0 rgba(0,0,0,.06); }

  /* Sticky header 2 baris */
  #tabelCkpn thead th{ position: sticky; }
  #tabelCkpn thead th.sticky-1{ top:0;                          background:#d9ead3; z-index: 88; }
  #tabelCkpn thead th.sticky-2{ top:var(--head1,40px);          background:#fce5cd; z-index: 87; }
  #tabelCkpn thead th.sticky-1.freeze-1,
  #tabelCkpn thead th.sticky-1.freeze-2{ background:#d9ead3; }
  #tabelCkpn thead th.sticky-2.freeze-1,
  #tabelCkpn thead th.sticky-2.freeze-2{ background:#fce5cd; }

  /* TOTAL sticky */
  #tabelCkpn tbody tr.sticky-total td{
    position: sticky;
    top: calc(var(--head1,40px) + var(--head2,32px));
    z-index: 70;
    background:#eff6ff;
  }
  #tabelCkpn tbody tr.sticky-total td.freeze-1{ z-index: 91; background:#eff6ff; }
  #tabelCkpn tbody tr.sticky-total td.freeze-2{ z-index: 90; background:#eff6ff; }

  #tabelCkpn tbody::after{
    content:"";
    display:block;
    height: calc(var(--head1,40px) + var(--head2,32px) + var(--totalH,36px) + 12px);
  }
  #tabelCkpn tbody tr:hover td{ background:#f9fafb; }

  /* ===== Toolbar ===== */
  .icon-btn{
    width:40px; height:40px; border-radius:9999px;
    display:inline-flex; align-items:center; justify-content:center;
    background:#2563eb; color:#fff; box-shadow:0 2px 6px rgba(37,99,235,.25);
    transition:background .15s ease;
  }
  .icon-btn:hover{ background:#1e40af; }

  /* ====== MOBILE ====== */
  @media (max-width: 640px){
    /* Header & filter DISEJAJARKAN di mobile */
    #ckpnHeader{ flex-direction: row !important; align-items: center; gap:8px; flex-wrap: wrap; }
    #ckpnHeader .ckpn-filter{ margin-left:auto; }
    #ckpnHeader .ckpn-filter .lbl{ display:none; }
    #ckpnHeader .ckpn-filter input[type="date"]{
      width: 180px;            /* pendek */
      max-width: 60vw;
      padding: .45rem .6rem;
      font-size: 13px;
    }
    .icon-btn{ width:38px; height:38px; }

    /* Tabel lebih rapat */
    #tabelCkpn{ font-size:12px; }
    #tabelCkpn thead th{ font-size:11px; }
    #tabelCkpn th, #tabelCkpn td{ padding: .5rem .5rem; }

    /* Sembunyikan KODE + dua kolom total (sesuai permintaan) */
    #tabelCkpn th.col-kode, #tabelCkpn td.col-kode{ display:none; }
    #tabelCkpn th.col-totalnoa, #tabelCkpn td.col-totalnoa{ display:none; }
    #tabelCkpn th.col-noaasset, #tabelCkpn td.col-noaasset{ display:none; }

    /* Perkecil lebar Nama Kantor agar kolom kanan muat */
    :root{ --col1w:0px; --col2w:10.5rem; }   /* ~168px */
    /* makin kecil utk layar sangat sempit */
    @media (max-width:420px){ :root{ --col2w:9.25rem; } }  /* ~148px */

    /* Freeze: nama jadi di paling kiri */
    #tabelCkpn .freeze-2, #tabelCkpn thead th.freeze-2{ left:0 !important; }

    /* Label TOTAL tampil di kolom nama */
    .ttl-mobile{ display:inline; font-weight:600; }
  }

  @media (min-width: 641px){
    .ttl-mobile{ display:none; }
  }

  /* urutan z-index header */
  #tabelCkpn thead th.sticky-1.freeze-1 { z-index: 91 !important; }
  #tabelCkpn thead th.sticky-1.freeze-2 { z-index: 90 !important; }
  #tabelCkpn thead th.sticky-2.freeze-1 { z-index: 89 !important; }
  #tabelCkpn thead th.sticky-2.freeze-2 { z-index: 88 !important; }
</style>


<script>
  const ckpnApiUrl = './api/ckpn/';
  const doFetch = (url, opts) => (window.apiFetch ? window.apiFetch(url, opts) : fetch(url, opts));

  // ===== Helpers =====
  const num  = (v)=> Number.parseFloat(v||0) || 0;
  const safe = (v)=> v ?? '-';
  const fmtRp = (n)=> new Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 }).format(num(n));
  const fmtInt= (n)=> new Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 }).format(num(n));

  function computeTotals(rows){
    const sum = (k) => rows.reduce((a,r)=> a + num(r[k]), 0);
    return {
      noa_total: sum('noa_total'),
      noa_individual: sum('noa_individual'),
      nilai_ckpn_individual: sum('nilai_ckpn_individual'),
      noa_asset_baik: sum('noa_asset_baik'),
      noa_kolektif: sum('noa_kolektif'),
      nilai_ckpn_kolektif: sum('nilai_ckpn_kolektif'),
      nilai_ckpn_total: sum('nilai_ckpn_total')
    };
  }

  // Offset sticky & tinggi scroller
  function setStickyOffsets() {
    const h1 = document.getElementById('headerRow1')?.offsetHeight || 40;
    const h2 = document.getElementById('headerRow2')?.offsetHeight || 32;
    const total = document.querySelector('tr.sticky-total')?.offsetHeight || 36;
    const holder = document.getElementById('scroller');
    holder.style.setProperty('--head1', h1 + 'px');
    holder.style.setProperty('--head2', h2 + 'px');
    holder.style.setProperty('--totalH', total + 'px');
  }

  function sizeScroller(){
    const wrap = document.getElementById('scroller');
    if(!wrap) return;
    const rectTop = wrap.getBoundingClientRect().top;
    const avail = window.innerHeight - rectTop - 6; /* buffer */
    wrap.style.height = (avail > 240 ? avail : 240) + 'px';
  }

  // ===== Fetch & render =====
  async function getLastHarianData() {
    try {
      const res = await fetch('./api/date/', { method: 'GET' });
      const json = await res.json();
      return json.data || null;
    } catch (e) { return null; }
  }

  async function fetchCkpnData(harian_date) {
    document.getElementById("loadingCkpn")?.classList.remove("hidden");
    try {
      const res = await doFetch(ckpnApiUrl, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ type: "ckpn cabang", harian_date })
      });
      const json = await res.json();
      const rows = Array.isArray(json.data) ? json.data : [];
      const totalRow = rows.find(r => r.kode_cabang === null || (r.nama_kantor || '').toUpperCase().includes('TOTAL'));
      const branchRows = rows.filter(r => r && r.kode_cabang !== null);
      renderCkpnTable(branchRows, totalRow);
    } catch (err) {
      console.error(err);
      renderCkpnTable([], null);
    } finally {
      document.getElementById("loadingCkpn")?.classList.add("hidden");
    }
  }

  function renderCkpnTable(data, totalRow) {
    const tbody = document.querySelector("#tabelCkpn tbody");
    tbody.innerHTML = "";

    // Urutkan by kode cabang
    data.sort((a,b)=>{
      const x = (a.kode_cabang||'').toString();
      const y = (b.kode_cabang||'').toString();
      return x.localeCompare(y, 'id', { numeric:true });
    });

    // TOTAL sticky
    const ttl = totalRow || computeTotals(data);
    tbody.insertAdjacentHTML('beforeend', `
      <tr class="sticky-total font-semibold text-sm text-blue-800">
        <td class="px-4 py-2 freeze-1 col1 col-kode">TOTAL</td>
        <td class="px-4 py-2 freeze-2 col2 col-nama"><span class="ttl-mobile">TOTAL </span></td>
        <td class="px-4 py-2 text-right col-totalnoa"    id="ttl_noa_total"></td>
        <td class="px-4 py-2 text-right col-noaasset"   id="ttl_noa_asset_baik"></td>
        <td class="px-4 py-2 text-right" id="ttl_noa_individual"></td>
        <td class="px-4 py-2 text-right" id="ttl_ckpn_individual"></td>
        <td class="px-4 py-2 text-right" id="ttl_noa_kolektif"></td>
        <td class="px-4 py-2 text-right" id="ttl_ckpn_kolektif"></td>
        <td class="px-4 py-2 text-right" id="ttl_ckpn_total"></td>
      </tr>
    `);

    // Baris cabang
    data.forEach(row => {
      tbody.insertAdjacentHTML('beforeend', `
        <tr class="border-b">
          <td class="px-4 py-3 text-center freeze-1 col1 col-kode">${safe(row.kode_cabang)}</td>
          <td class="px-4 py-3 freeze-2 col2 col-nama">${safe(row.nama_kantor)}</td>
          <td class="px-4 py-3 text-right col-totalnoa">${fmtInt(row.noa_total)}</td>
          <td class="px-4 py-3 text-right col-noaasset">${fmtInt(row.noa_asset_baik)}</td>
          <td class="px-4 py-3 text-right">${fmtInt(row.noa_individual)}</td>
          <td class="px-4 py-3 text-right">${fmtRp(row.nilai_ckpn_individual)}</td>
          <td class="px-4 py-3 text-right">${fmtInt(row.noa_kolektif)}</td>
          <td class="px-4 py-3 text-right">${fmtRp(row.nilai_ckpn_kolektif)}</td>
          <td class="px-4 py-3 text-right font-semibold">${fmtRp(row.nilai_ckpn_total)}</td>
        </tr>
      `);
    });

    // Isi total
    document.getElementById("ttl_noa_total").textContent        = fmtInt(ttl.noa_total);
    document.getElementById("ttl_noa_individual").textContent   = fmtInt(ttl.noa_individual);
    document.getElementById("ttl_ckpn_individual").textContent  = fmtRp(ttl.nilai_ckpn_individual);
    document.getElementById("ttl_noa_kolektif").textContent     = fmtInt(ttl.noa_kolektif);
    document.getElementById("ttl_ckpn_kolektif").textContent    = fmtRp(ttl.nilai_ckpn_kolektif);
    document.getElementById("ttl_noa_asset_baik").textContent   = fmtInt(ttl.noa_asset_baik);
    document.getElementById("ttl_ckpn_total").textContent       = fmtRp(ttl.nilai_ckpn_total);

    // Recompute layout after render
    setStickyOffsets();
    sizeScroller();
    setTimeout(()=>{ setStickyOffsets(); sizeScroller(); }, 50);
  }

  // ===== Init =====
  window.addEventListener('load', async ()=>{
    const dateInfo = await getLastHarianData();
    const defDate  = dateInfo?.last_created || new Date().toISOString().slice(0,10);
    document.getElementById("harian_date_ckpn").value = defDate;
    setStickyOffsets(); sizeScroller();
    fetchCkpnData(defDate);
  });
  window.addEventListener('resize', ()=>{ setStickyOffsets(); sizeScroller(); });

  document.getElementById("formFilterCkpn").addEventListener("submit", function (e) {
    e.preventDefault();
    const harian_date = document.getElementById("harian_date_ckpn").value;
    fetchCkpnData(harian_date);
  });
</script>
