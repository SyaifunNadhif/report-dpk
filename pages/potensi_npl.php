<!-- ⚠️ Potensi NPL — sticky header + freeze kiri + TOTAL nempel + responsive (web & mobile) -->
<div class="max-w-7xl mx-auto px-4 py-4 h-screen flex flex-col">
  <!-- Toolbar -->
  <div class="hdr flex flex-wrap items-start gap-2 mb-3">
    <h1 class="title text-2xl font-bold flex items-center gap-2">
      <span>⚠️</span><span>Potensi NPL</span>
    </h1>

    <form id="formFilterPotensi" class="ml-auto">
      <div class="flex items-center gap-2">
        <label for="closing_date_potensi" class="text-sm text-slate-700 hidden sm:inline">Closing:</label>
        <input type="date" id="closing_date_potensi" class="f-inp" required>
        <label for="harian_date_potensi" class="text-sm text-slate-700 hidden sm:inline">Harian:</label>
        <input type="date" id="harian_date_potensi" class="f-inp" required>
        <button type="submit" class="f-btn" title="Tampilkan">🔍</button>
      </div>
    </form>
  </div>

  <!-- Loading -->
  <div id="loadingPotensi" class="hidden flex items-center gap-2 text-sm text-gray-600 mb-2">
    <svg class="animate-spin h-5 w-5 text-blue-600" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span>Memuat rekap Potensi NPL...</span>
  </div>

  <!-- SCROLLER -->
  <div id="poScroller" class="flex-1 min-h-0 overflow-hidden rounded border border-gray-200 bg-white">
    <div class="h-full overflow-auto">
      <table id="tabelPotensi" class="min-w-full text-sm text-left text-gray-700">
        <thead class="uppercase">
          <tr id="poHead1" class="text-xs">
            <th class="px-3 py-2 sticky-po freeze-1 col1 col-kode">KODE CABANG</th>
            <th class="px-3 py-2 sticky-po freeze-2 col2 col-nama">NAMA CABANG</th>
            <th class="px-3 py-2 text-right sticky-po col-noa" data-sort="noa">NOA ⬍</th>
            <th class="px-3 py-2 text-right sticky-po col-baki" data-sort="baki_debet">BAKI DEBET ⬍</th>
          </tr>
        </thead>

        <!-- TOTAL tepat di bawah header (sticky) -->
        <tbody id="poTotalRow"></tbody>

        <!-- BODY data -->
        <tbody id="poBody"></tbody>
      </table>
    </div>
  </div>
</div>

<style>
  /* Controls */
  .f-inp{ border:1px solid #cbd5e1; border-radius:.6rem; padding:.45rem .7rem; font-size:14px; background:#fff; }
  .f-btn{ width:40px; height:40px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center;
          background:#2563eb; color:#fff; box-shadow:0 6px 14px rgba(37,99,235,.25); }
  .f-btn:hover{ background:#1e40af; }
  @media (max-width:640px){ .f-inp{ width:180px; max-width:65vw; font-size:13px; } }

  *{ box-sizing:border-box; }
  body{ overflow:hidden; }

  /* Vars: lebar kolom & spacer (desktop defaults) */
  #poScroller{
    --po_col1: 5.25rem;   /* KODE (desktop) */
    --po_col2: 15.5rem;   /* NAMA (desktop) */
    --po_head: 40px;
    --po_totalH: 36px;
    --po_safe: 41px;      /* spacer bawah */
    /* Mobile default (di-override di media query) */
    --po_m_colNama: 11rem;
  }
  @supports(padding:max(0px)){ #poScroller{ --po_safe:max(42px, env(safe-area-inset-bottom)); } }

  /* Header sticky fix — biar nggak putih saat scroll */
  #tabelPotensi thead th{
    position:sticky; top:0; z-index:88; background:#d9ead3 !important;
    font-size: clamp(10px, 1.35vw, 12px);
  }
  #tabelPotensi thead th.freeze-1{ left:0; z-index:90; }
  #tabelPotensi thead th.freeze-2{ left:var(--po_col1); z-index:89; }

  /* Tabel */
  #tabelPotensi{ table-layout:fixed; font-size: clamp(11.5px, 1.6vw, 14px); border-collapse:separate; border-spacing:0; }
  #tabelPotensi th, #tabelPotensi td{ border-bottom:1px solid #eef2f7; }

  /* Kolom desktop */
  #tabelPotensi .col1{ width:var(--po_col1); min-width:var(--po_col1); }
  #tabelPotensi .col2{ width:var(--po_col2); min-width:var(--po_col2); }
  #tabelPotensi th.col-nama, #tabelPotensi td.col-nama{
    max-width:var(--po_col2); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
  }
  #tabelPotensi .col-noa{ width:8rem;  min-width:7rem; }
  #tabelPotensi .col-baki{ width:11rem; min-width:9.5rem; }

  /* Freeze kiri (body) */
  #tabelPotensi .freeze-1{ position:sticky; left:0; z-index:41; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06); }
  #tabelPotensi .freeze-2{ position:sticky; left:var(--po_col1); z-index:40; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06); }

  /* TOTAL sticky tepat di bawah header */
  #tabelPotensi tbody tr.sticky-total td{
    position:sticky; top:var(--po_head); background:#eaf2ff; color:#1e40af; z-index:70; border-bottom:1px solid #c7d2fe;
  }
  #tabelPotensi tbody tr.sticky-total td.freeze-1{ z-index:91; }
  #tabelPotensi tbody tr.sticky-total td.freeze-2{ z-index:90; }

  /* Hover */
  #tabelPotensi tbody tr:hover td{ background:#f9fafb; }

  /* Spacer bawah agar baris akhir tidak ketutup */
  #poBody::after{ content:""; display:block; height: calc(var(--po_head) + var(--po_totalH) + var(--po_safe)); }

  /* ===== MOBILE (<=640px) ===== */
  @media (max-width:640px){
    #tabelPotensi{ font-size:11px; }
    #tabelPotensi thead th{ font-size:10.5px; }
    #tabelPotensi th, #tabelPotensi td{ padding:.32rem .4rem; } /* agak dipersempit */

    /* Hitung lebar otomatis agar NOA & BAKI selalu muat */
    #poScroller{
      --po_m_noa: 6.4rem;              /* min yang nyaman untuk NOA */
      --po_m_baki: 8.2rem;             /* min yang nyaman untuk BAKI */
      --po_m_gap: 2rem;                /* kira2 total padding/scrollbar/gap */
      /* NAMA = total width - NOA - BAKI - gap, minimal 7rem */
      --po_m_colNama: max(7rem, calc(100% - var(--po_m_noa) - var(--po_m_baki) - var(--po_m_gap)));
    }

    /* Hide KODE, NAMA tetap freeze paling kiri */
    #tabelPotensi th.col-kode, #tabelPotensi td.col-kode{ display:none; }
    /* Header: geser freeze-2 (NAMA) ke kiri penuh */
    #tabelPotensi thead th.freeze-2{ left:0 !important; }

    /* Body & Header: NAMA jadi freeze kiri + lebar dinamis */
    #tabelPotensi td.col-nama, #tabelPotensi th.col-nama{
      position:sticky; left:0; z-index:41; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06);
      width:var(--po_m_colNama); min-width:var(--po_m_colNama); max-width:var(--po_m_colNama);
      overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
    }

    /* Angka: pakai lebar variabel biar konsisten */
    #tabelPotensi .col-noa{ width:var(--po_m_noa);  min-width:var(--po_m_noa);  white-space:nowrap; }
    #tabelPotensi .col-baki{ width:var(--po_m_baki); min-width:var(--po_m_baki); white-space:nowrap; }

    /* TOTAL: nama tetap freeze di mobile */
    #tabelPotensi tbody tr.sticky-total td.col-nama{ left:0; background:#eaf2ff; z-index:91; }
  }
</style>

<script>
  // ===== Formatter global =====
  const nfID = new Intl.NumberFormat('id-ID');
  const formatRupiah = (n) => nfID.format(Number(n || 0));
  const formatNumber = (n) => nfID.format(Number(n || 0));

  // ===== Sticky & scroller =====
  function setPoSticky(){
    const h   = document.getElementById('poHead1')?.offsetHeight || 40;
    const tot = document.querySelector('#tabelPotensi tr.sticky-total')?.offsetHeight || 36;
    const holder = document.getElementById('poScroller');
    holder.style.setProperty('--po_head', h + 'px');
    holder.style.setProperty('--po_totalH', tot + 'px');
  }
  function sizePoScroller(){
    const wrap = document.getElementById('poScroller'); if(!wrap) return;
    const rectTop = wrap.getBoundingClientRect().top;
    const GAP = 10;
    wrap.style.height = Math.max(260, window.innerHeight - rectTop - GAP) + 'px';
  }
  window.addEventListener('resize', ()=>{ setPoSticky(); sizePoScroller(); });

  // ===== INIT tanggal dari API date =====
  (async () => {
    const d = await getLastHarianData();
    if (!d) return;
    closing_date_potensi.value = d.last_closing;
    harian_date_potensi.value  = d.last_created;
    fetchPotensiData(d.last_closing, d.last_created);
    setPoSticky(); sizePoScroller();
  })();

  async function getLastHarianData() {
    try {
      const r = await fetch('./api/date/', { method: 'GET' });
      const j = await r.json();
      return j.data || null;
    } catch { return null; }
  }

  // ===== STATE =====
  let potensiRows = [];
  let potensiTotal = null;
  let sortState = { col: null, dir: 1 }; // dir: 1 asc, -1 desc

  // ===== EVENTS =====
  formFilterPotensi.addEventListener("submit", function (e) {
    e.preventDefault();
    fetchPotensiData(closing_date_potensi.value, harian_date_potensi.value);
  });

  // Header sort (delegation)
  document.querySelector('#tabelPotensi thead').addEventListener('click', (e)=>{
    const key = e.target?.dataset?.sort;
    if(!key) return;
    if (sortState.col === key) sortState.dir = -sortState.dir; else { sortState.col = key; sortState.dir = -1; } // default desc
    const rows = [...potensiRows].sort((a,b)=>{
      const A = Number(a[key]||0), B = Number(b[key]||0);
      return sortState.dir * (A - B);
    });
    renderPotensiTable(rows);
  });

  // ===== FETCH REKAP =====
  function fetchPotensiData(closing_date, harian_date) {
    loadingPotensi.classList.remove("hidden");

    fetch("./api/npl/", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ type: "Potensi NPL", closing_date, harian_date })
    })
    .then(r => r.json())
    .then(res => {
      const data = res.data || [];
      const isTotal = x => (x.nama_cabang || x.nama_kantor || '').toUpperCase().includes('TOTAL');
      potensiTotal = data.find(isTotal) || null;
      potensiRows  = data.filter(x => !isTotal(x));

      // default urut kode 001 → 028
      potensiRows.sort((a,b) => Number(String(a.kode_cabang||'999').replace(/\D/g,'')) - Number(String(b.kode_cabang||'999').replace(/\D/g,'')));

      renderPotensiTable(potensiRows);
    })
    .catch(() => {
      document.querySelector("#poBody").innerHTML = `<tr><td colspan="4" class="px-4 py-3 text-red-600">Gagal memuat data.</td></tr>`;
      document.querySelector("#poTotalRow").innerHTML = '';
    })
    .finally(() => loadingPotensi.classList.add("hidden"));
  }

  // ===== RENDER (TOTAL nempel di bawah header; NOA klik ke detail) =====
  function renderPotensiTable(rows) {
    const closing = closing_date_potensi.value;
    const harian  = harian_date_potensi.value;

    // TOTAL sticky row
    if (potensiTotal) {
      const tNoa  = Number(potensiTotal.noa || 0);
      const tBaki = Number(potensiTotal.baki_debet || 0);
      poTotalRow.innerHTML = `
        <tr class="sticky-total font-semibold text-sm">
          <td class="px-3 py-2 freeze-1 col1 col-kode"></td>
          <td class="px-3 py-2 freeze-2 col2 col-nama"><span class="font-bold">TOTAL</span></td>
          <td class="px-3 py-2 text-right text-blue-800 col-noa">${formatNumber(tNoa)}</td>
          <td class="px-3 py-2 text-right text-blue-800 col-baki">${formatRupiah(tBaki)}</td>
        </tr>`;
    } else {
      poTotalRow.innerHTML = '';
    }

    // BODY
    let html = '';
    for (const r of rows) {
      const kode = String(r.kode_cabang || '-').padStart(3,'0');
      const nama = r.nama_cabang || r.nama_kantor || '-';
      const noa  = Number(r.noa || 0);
      const baki = Number(r.baki_debet || 0);

      html += `
        <tr class="border-b hover:bg-gray-50">
          <td class="px-3 py-2 text-center freeze-1 col1 col-kode">${kode}</td>
          <td class="px-3 py-2 freeze-2 col2 col-nama" title="${nama}">${nama}</td>
          <td class="px-3 py-2 text-right col-noa">
            <a class="text-blue-600 hover:underline"
               href="./detail_potensi_npl"
               onclick="event.preventDefault(); goDetailPotensi('${kode}','${closing}','${harian}')">
               ${formatNumber(noa)}
            </a>
          </td>
          <td class="px-3 py-2 text-right col-baki">${formatRupiah(baki)}</td>
        </tr>`;
    }
    poBody.innerHTML = html;

    setPoSticky(); sizePoScroller();
    setTimeout(()=>{ setPoSticky(); sizePoScroller(); }, 50);
  }

  // ===== NAV helper (store → go) =====
  function goDetailPotensi(kode_kantor, closing_date, harian_date) {
    localStorage.setItem('potensi_npl_params', JSON.stringify({ kode_kantor, closing_date, harian_date }));
    window.location.href = './detail_potensi_npl';
  }
</script>
