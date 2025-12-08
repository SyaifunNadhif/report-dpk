<!-- ============== REALISASI KREDIT (fixed mobile header freeze) ============== -->
<div class="max-w-7xl mx-auto px-4 py-4 h-screen flex flex-col">
  <!-- Header bar -->
  <div class="hdr flex flex-wrap items-start gap-2 mb-2">
    <h1 class="title text-2xl font-bold flex items-center gap-2">
      <span>💳</span> <span>Rekap Realisasi Kredit</span>
    </h1>

    <!-- Filter tanggal -->
    <form id="formFilterRealisasi" class="ml-auto" aria-label="Filter realisasi">
      <div id="filterRealisasi" class="flex items-center gap-2">
        <label for="closing_date_realisasi" class="lbl">Tanggal Closing:</label>
        <input type="date" id="closing_date_realisasi" class="inp" required aria-label="Tanggal Closing">

        <label for="harian_date_realisasi" class="lbl">Tanggal Harian:</label>
        <input type="date" id="harian_date_realisasi" class="inp" required aria-label="Tanggal Harian">

        <!-- ikon saja -->
        <button type="submit" class="btn-icon" aria-label="Filter">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="7"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
        </button>
      </div>
    </form>
  </div>

  <!-- Loading -->
  <div id="loadingRealisasi" class="hidden flex items-center gap-2 text-sm text-gray-600 mb-2">
    <svg class="animate-spin h-5 w-5 text-blue-600" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span>Memuat data realisasi kredit...</span>
  </div>

  <!-- SCROLLER ala CKPN -->
  <div id="realisasiScroller" class="flex-1 min-h-0 overflow-hidden rounded border border-gray-200 bg-white">
    <div id="realisasiScrollerInner" class="h-full overflow-auto">
      <table id="tabelRealisasi" class="min-w-full text-sm text-left text-gray-700">
        <thead class="uppercase">
          <!-- id penting utk hitung tinggi header -->
          <tr id="rlzHead1" class="text-xs">
            <th id="thKode"     class="px-4 py-2 sticky-rlz freeze-1 col1 col-kode">KODE KANTOR</th>
            <th id="thNama"     class="px-4 py-2 sticky-rlz freeze-2 col2 col-nama">NAMA KANTOR</th>
            <th id="thNoa"      class="px-4 py-2 text-right sticky-rlz cursor-pointer" onclick="sortRealisasi('noa_realisasi')">NOA</th>
            <th id="thRealisasi"class="px-4 py-2 text-right sticky-rlz cursor-pointer" onclick="sortRealisasi('total_realisasi')">REALISASI</th>
            <th id="thRunoff"   class="px-4 py-2 text-right sticky-rlz cursor-pointer" onclick="sortRealisasi('total_run_off')">RUN OFF</th>
            <th id="thGrowth"   class="px-4 py-2 text-right sticky-rlz cursor-pointer" onclick="sortRealisasi('growth')">GROWTH</th>
          </tr>
        </thead>
        <tbody><!-- render via JS --></tbody>
      </table>
    </div>
  </div>
</div>

<!-- MODAL -->
<div id="modalDetailRealisasi"
     class="fixed inset-0 hidden bg-gray-900/50 backdrop-blur-sm items-center justify-center"
     style="z-index: 6000;">
  <div id="modalCardRealisasi" class="bg-white rounded-lg shadow max-w-5xl w-[94vw] sm:w-[90vw] md:w-[860px] max-h-[90vh] overflow-hidden">
    <div class="flex items-center justify-between p-4 border-b">
      <h3 id="modalTitleRealisasi" class="modal-title">Daftar Debitur Realisasi Kredit</h3>
      <button id="btnCloseRealisasi" class="text-gray-500 hover:text-gray-700 text-xl" aria-label="Tutup">✕</button>
    </div>
    <div class="p-4 overflow-y-auto max-h-[70vh]" id="modalBodyRealisasi"></div>
  </div>
</div>

<style>
  /* ===== Kontrol & tombol ===== */
  .inp{ border:1px solid #cbd5e1; border-radius:.6rem; padding:.5rem .75rem; font-size:14px; background:#fff; }
  .lbl{ font-size:13px; color:#334155; }
  .btn-icon{
    width:42px; height:42px; border-radius:999px;
    display:inline-flex; align-items:center; justify-content:center;
    background:#2563eb; color:#fff; box-shadow:0 6px 14px rgba(37,99,235,.25);
  }
  .btn-icon:hover{ background:#1e40af; }

  /* ===== Header responsif ===== */
  .hdr{ row-gap:.5rem; }
  @media (max-width:640px){
    .title{ font-size:1.25rem; } /* perkecil judul */
    .hdr{ flex-direction:column; align-items:flex-start; }
    /* filter pindah di bawah judul dan horizontal */
    #filterRealisasi{ width:100%; gap:.5rem; }
    /* sembunyikan label biar ringkas */
    .lbl{ display:none; }
    .inp{ flex:1 1 0; min-width:0; font-size:13px; padding:.45rem .6rem; }
    .btn-icon{ width:40px; height:40px; }
  }

  /* ===== TABEL ala CKPN ===== */
  body{ overflow:hidden; }
  #realisasiScroller{ --rlz_col1:5rem; --rlz_col2:16rem; }

  #tabelRealisasi .col1{ width:var(--rlz_col1); min-width:var(--rlz_col1); }
  #tabelRealisasi .col2{ width:var(--rlz_col2); min-width:var(--rlz_col2); }

  #tabelRealisasi .freeze-1{ position:sticky; left:0; z-index:41; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06); }
  #tabelRealisasi .freeze-2{ position:sticky; left:var(--rlz_col1); z-index:40; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06); }

  /* Sticky header + warna hijau CKPN */
  #tabelRealisasi thead th{ position:sticky; }
  #tabelRealisasi thead th.sticky-rlz{ top:0; background:#d9ead3; z-index:88; }
  #tabelRealisasi thead th.sticky-rlz.freeze-1,
  #tabelRealisasi thead th.sticky-rlz.freeze-2{ background:#d9ead3; }

  /* === FIX LAYERING HEADER (seperti CKPN) === */
  #tabelRealisasi thead th.sticky-rlz.freeze-1{ z-index:91 !important; }
  #tabelRealisasi thead th.sticky-rlz.freeze-2{ z-index:90 !important; }

  /* TOTAL sticky (beda body) */
  #tabelRealisasi tbody tr.sticky-total td{
    position:sticky; top:var(--rlz_head,40px); z-index:70; background:#eff6ff;
  }
  #tabelRealisasi tbody tr.sticky-total td.freeze-1{ z-index:91; }
  #tabelRealisasi tbody tr.sticky-total td.freeze-2{ z-index:90; }

  /* Spacer bawah (aman dr taskbar/footer) */
  #realisasiScroller{ --rlz_safe:28px; }
  @supports (padding:max(0px)) { #realisasiScroller{ --rlz_safe:max(28px, env(safe-area-inset-bottom)); } }
  #tabelRealisasi tbody::after{
    content:""; display:block;
    height: calc(var(--rlz_head,40px) + var(--rlz_totalH,36px) + var(--rlz_safe));
  }

  #tabelRealisasi tbody tr:hover td{ background:#f9fafb; }

  /* ===== Mobile table ===== */
  @media (max-width:640px){
    #tabelRealisasi{ font-size:12px; }
    #tabelRealisasi thead th{ font-size:11px; }
    #tabelRealisasi th, #tabelRealisasi td{ padding:.5rem .5rem; }

    /* hide KODE di mobile + freeze Nama (left:0) */
    #tabelRealisasi th.col-kode, #tabelRealisasi td.col-kode{ display:none; }
    #realisasiScroller{ --rlz_col1:0px; }
    #tabelRealisasi .freeze-2, #tabelRealisasi thead th.freeze-2{ left:0 !important; }
  }

  /* ===== Modal typography + hide kolom di mobile ===== */
  #modalCardRealisasi{ font-size: clamp(11px, 1.2vw, 14px); }
  #modalCardRealisasi .modal-title{ font-size: clamp(16px, 2.0vw, 20px); font-weight:700; }
  @media (max-width:640px){
    /* sembunyikan Realisasi & Jatuh Tempo di tabel modal agar ringkas */
    #modalCardRealisasi .col-real, #modalCardRealisasi .col-jt{ display:none; }
    #modalCardRealisasi{ font-size:12px; }
    #modalCardRealisasi .modal-title{ font-size:16px; }
  }
</style>

<script>
  /* ===== STATE ===== */
  let realisasiDataRaw = [];
  let realisasiTotal   = null;
  let realisasiSortKey = '';
  let realisasiSortAsc = false;

  const fmtID = n => new Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 }).format(+n||0);

  /* ===== Sticky & scroller ===== */
  function setRealisasiStickyOffsets(){
    const h = document.getElementById('rlzHead1')?.offsetHeight || 40;
    const total = document.querySelector('#tabelRealisasi tr.sticky-total')?.offsetHeight || 36;
    const holder = document.getElementById('realisasiScroller');
    holder.style.setProperty('--rlz_head', h + 'px');
    holder.style.setProperty('--rlz_totalH', total + 'px');
  }
  function sizeRealisasiScroller(){
    const wrap = document.getElementById('realisasiScroller');
    if(!wrap) return;
    const rectTop = wrap.getBoundingClientRect().top;
    const GAP = 18;
    wrap.style.height = (Math.max(240, window.innerHeight - rectTop - GAP)) + 'px';
  }
  window.addEventListener('resize', () => { setRealisasiStickyOffsets(); sizeRealisasiScroller(); });

  /* ===== INIT ===== */
  (async () => {
    const dateInfo = await getLastHarianData();
    if (!dateInfo) return;
    const { last_created, last_closing } = dateInfo;
    document.getElementById("closing_date_realisasi").value = last_closing;
    document.getElementById("harian_date_realisasi").value = last_created;
    fetchRealisasiData(last_closing, last_created);
    setRealisasiStickyOffsets(); sizeRealisasiScroller();
  })();

  async function getLastHarianData() {
    try {
      const res = await fetch('./api/date/', { method: 'GET' });
      const json = await res.json();
      return json.data || null;
    } catch { return null; }
  }

  document.getElementById("formFilterRealisasi").addEventListener("submit", function (e) {
    e.preventDefault();
    const closing_date = document.getElementById("closing_date_realisasi").value;
    const harian_date  = document.getElementById("harian_date_realisasi").value;
    fetchRealisasiData(closing_date, harian_date);
  });

  function fetchRealisasiData(closing_date, harian_date) {
    const loading = document.getElementById("loadingRealisasi");
    loading.classList.remove("hidden");

    fetch("./api/kredit/", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ type: "Realisasi Kredit", closing_date, harian_date })
    })
    .then(res => res.json())
    .then(res => {
      const data = res.data || [];
      realisasiTotal = data.find(d => (d.nama_kantor||'').toUpperCase().includes("TOTAL"));
      realisasiDataRaw = data.filter(d => !(d.nama_kantor||'').toUpperCase().includes("TOTAL"));
      renderRealisasiTable(realisasiDataRaw);
    })
    .finally(() => loading.classList.add("hidden"));
  }

  function renderRealisasiTable(data) {
    const tbody = document.querySelector("#tabelRealisasi tbody");
    tbody.innerHTML = "";

    /* TOTAL sticky paling atas */
    if (realisasiTotal) {
      tbody.insertAdjacentHTML('beforeend', `
        <tr class="sticky-total font-semibold text-sm text-blue-800">
          <td class="px-4 py-2 freeze-1 col1 col-kode">TOTAL</td>
          <td class="px-4 py-2 freeze-2 col2 col-nama"><span class="ttl-mobile">TOTAL</span></td>
          <td class="px-4 py-2 text-right">${fmtID(realisasiTotal.noa_realisasi)}</td>
          <td class="px-4 py-2 text-right">${fmtID(realisasiTotal.total_realisasi)}</td>
          <td class="px-4 py-2 text-right">${fmtID(realisasiTotal.total_run_off)}</td>
          <td class="px-4 py-2 text-right">${fmtID(realisasiTotal.growth)}</td>
        </tr>
      `);
    }

    /* Baris data */
    data.forEach(row => {
      tbody.insertAdjacentHTML('beforeend', `
        <tr class="border-b">
          <td class="px-4 py-3 text-center freeze-1 col1 col-kode">${row.kode_cabang || "-"}</td>
          <td class="px-4 py-3 freeze-2 col2 col-nama">${row.nama_kantor || "-"}</td>
          <td class="px-4 py-3 text-right">
            <a href="#" class="text-blue-600 hover:underline" onclick="showDetailRealisasi('${row.kode_cabang}')">
              ${fmtID(row.noa_realisasi)}
            </a>
          </td>
          <td class="px-4 py-3 text-right">${fmtID(row.total_realisasi)}</td>
          <td class="px-4 py-3 text-right">${fmtID(row.total_run_off)}</td>
          <td class="px-4 py-3 text-right">${fmtID(row.growth)}</td>
        </tr>
      `);
    });

    setRealisasiStickyOffsets();
    sizeRealisasiScroller();
    setTimeout(()=>{ setRealisasiStickyOffsets(); sizeRealisasiScroller(); }, 50);
  }

  function sortRealisasi(key) {
    if (realisasiSortKey === key) { realisasiSortAsc = !realisasiSortAsc; }
    else { realisasiSortKey = key; realisasiSortAsc = false; }

    const sorted = [...realisasiDataRaw].sort((a,b)=>{
      const A = parseFloat(a[key])||0, B = parseFloat(b[key])||0;
      return realisasiSortAsc ? A - B : B - A;
    });
    renderRealisasiTable(sorted);
  }

  /* ============ MODAL ============ */
  async function showDetailRealisasi(kode_kantor) {
    const harian_date = document.getElementById("harian_date_realisasi").value;
    if (!harian_date || !kode_kantor) return alert("Tanggal dan kode kantor harus diisi");

    const overlay   = document.getElementById("modalDetailRealisasi");
    const modalTitle= document.getElementById("modalTitleRealisasi");
    const modalBody = document.getElementById("modalBodyRealisasi");

    overlay.classList.remove("hidden"); overlay.classList.add("flex");
    modalTitle.textContent = `Daftar Debitur Realisasi - Kode Kantor ${kode_kantor}`;
    modalBody.innerHTML = `<p class="text-sm text-gray-500">Mengambil data debitur...</p>`;

    try {
      const res = await fetch("./api/kredit/", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ type: "Detail Realisasi Kredit", kode_kantor, harian_date })
      });
      const json = await res.json();
      const list = json.data || [];

      if (!list.length) {
        modalBody.innerHTML = `<p class="text-red-600 font-semibold">Tidak ada data ditemukan.</p>`;
        registerModalClosers();
        return;
      }

      let html = `
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left text-gray-800 bg-white rounded">
            <thead class="bg-gray-100 text-gray-700">
              <tr>
                <th class="px-4 py-2">No Rekening</th>
                <th class="px-4 py-2">Nama Nasabah</th>
                <th class="px-4 py-2 text-right">Plafond</th>
                <th class="px-4 py-2">Alamat</th>
                <th class="px-4 py-2 col-real">Realisasi</th>
                <th class="px-4 py-2 col-jt">Jatuh Tempo</th>
              </tr>
            </thead>
            <tbody>
      `;
      list.forEach(d=>{
        html += `
          <tr class="border-b">
            <td class="px-4 py-2">${d.no_rekening || "-"}</td>
            <td class="px-4 py-2">${d.nama_nasabah || "-"}</td>
            <td class="px-4 py-2 text-right">${fmtID(d.plafond)}</td>
            <td class="px-4 py-2">${d.alamat || "-"}</td>
            <td class="px-4 py-2 col-real">${d.tgl_realisasi || "-"}</td>
            <td class="px-4 py-2 col-jt">${d.tgl_jatuh_tempo || "-"}</td>
          </tr>`;
      });
      html += `</tbody></table></div>`;
      modalBody.innerHTML = html;

      registerModalClosers();
    } catch (e) {
      modalBody.innerHTML = `<p class="text-red-600">Gagal mengambil data: ${e.message}</p>`;
      registerModalClosers();
    }

    function registerModalClosers(){
      const overlay = document.getElementById("modalDetailRealisasi");
      overlay.onclick = (ev)=>{ if(!ev.target.closest('#modalCardRealisasi')) closeModalRealisasi(); };
      document.getElementById('btnCloseRealisasi').onclick = closeModalRealisasi;
      const escCloser = (e)=>{ if(e.key === 'Escape'){ closeModalRealisasi(); document.removeEventListener('keydown', escCloser);} };
      document.addEventListener('keydown', escCloser);
    }
  }

  function closeModalRealisasi() {
    const overlay = document.getElementById("modalDetailRealisasi");
    overlay.classList.add("hidden"); overlay.classList.remove("flex");
  }

  // expose
  window.showDetailRealisasi = showDetailRealisasi;
  window.closeModalRealisasi = closeModalRealisasi;
</script>
