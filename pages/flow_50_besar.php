<!-- 📈 50 Besar Flow PAR — kolom Norek & Debitur dipersempit (+ mid-ellipsis) -->
<div class="max-w-7xl mx-auto px-4 py-4 h-screen flex flex-col">
  <!-- Toolbar -->
  <div class="flex flex-wrap items-start gap-2 mb-3">
    <h1 class="text-2xl font-bold flex items-center gap-2">
      <span>📈</span><span>50 Besar Flow PAR</span>
    </h1>

    <form id="formFilterTopPar" class="ml-auto">
      <div class="flex items-center gap-2">
        <label for="closing_date_top_par" class="text-sm text-slate-700 hidden sm:inline">Closing:</label>
        <input type="date" id="closing_date_top_par" class="f-inp" required>
        <label for="harian_date_top_par" class="text-sm text-slate-700 hidden sm:inline">Harian:</label>
        <input type="date" id="harian_date_top_par" class="f-inp" required>
        <button type="submit" class="f-btn" title="Tampilkan">🔍</button>
      </div>
    </form>
  </div>

  <!-- Loading -->
  <div id="loadingTopPar" class="hidden flex items-center gap-2 text-sm text-gray-600 mb-2">
    <svg class="animate-spin h-5 w-5 text-blue-600" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span>Memuat data 50 Terbesar...</span>
  </div>

  <!-- SCROLLER -->
  <div id="tpScroller" class="flex-1 min-h-0 overflow-hidden rounded border border-gray-200 bg-white">
    <div class="h-full overflow-auto">
      <table id="tabelTopPar" class="min-w-full text-sm text-left text-gray-700">
        <thead class="uppercase">
          <tr id="tpHead1" class="text-xs">
            <th class="px-3 py-2 sticky-tp freeze-1 col-cabang">NAMA KANTOR</th>
            <th class="px-3 py-2 sticky-tp freeze-2 col-norek">NO REKENING</th>
            <th class="px-3 py-2 sticky-tp col-debitur">NAMA DEBITUR</th>
            <th class="px-3 py-2 sticky-tp col-alamat">ALAMAT</th>
            <th class="px-3 py-2 text-right sticky-tp sort col-amt" data-sort="baki_debet">BAKI DEBET ⬍</th>
            <th class="px-3 py-2 text-right sticky-tp sort col-amt" data-sort="tunggakan_pokok">TUNGG. POKOK ⬍</th>
            <th class="px-3 py-2 text-right sticky-tp sort col-amt" data-sort="tunggakan_bunga">TUNGG. BUNGA ⬍</th>
          </tr>
        </thead>
        <tbody id="tpTotalRow"></tbody>
        <tbody id="tpBody"></tbody>
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

  #tpScroller{
    /* Desktop sizes (lebih kecil dari sebelumnya) */
    --tp_colCabang: 11rem;
    --tp_colNorek : 7.5rem;   /* Norek dipersempit */
    --tp_head: 40px; --tp_totalH:36px; --tp_safe:36px;

    /* Mobile left offsets */
    --tp_m_colNorek: 7.2rem;  /* Norek lebih sempit di HP */
  }
  @supports(padding:max(0px)){ #tpScroller{ --tp_safe:max(36px, env(safe-area-inset-bottom)); } }

  #tabelTopPar{ font-size: clamp(11.5px, 1.6vw, 14px); table-layout: fixed; }
  #tabelTopPar thead th{ font-size: clamp(10px, 1.4vw, 12px); background:#d9ead3 !important; position:sticky; top:0; z-index:88; }
  #tabelTopPar thead th.freeze-1{ left:0; z-index:90; }
  #tabelTopPar thead th.freeze-2{ left:var(--tp_colCabang); z-index:89; }

  /* Lebar desktop */
  #tabelTopPar .col-cabang{ width:var(--tp_colCabang); min-width:var(--tp_colCabang); }
  #tabelTopPar .col-norek{  width:var(--tp_colNorek);  min-width:var(--tp_colNorek); }
  #tabelTopPar .col-debitur{ max-width:14rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; } /* dikecilkan */
  #tabelTopPar .col-alamat { max-width:20ch; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  #tabelTopPar .col-amt    { width:9rem; min-width:9rem; }  /* angka ikut dipersempit */

  /* Freeze body kiri */
  #tabelTopPar .freeze-1{ position:sticky; left:0; z-index:41; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06); }
  #tabelTopPar .freeze-2{ position:sticky; left:var(--tp_colCabang); z-index:40; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06); }

  /* TOTAL row sticky */
  #tabelTopPar tbody tr.sticky-total td{
    position:sticky; top:var(--tp_head); background:#eaf2ff; color:#1e40af; z-index:70; border-bottom:1px solid #c7d2fe;
  }
  #tabelTopPar tbody tr.sticky-total td.freeze-1{ z-index:91; }
  #tabelTopPar tbody tr.sticky-total td.freeze-2{ z-index:90; }

  #tabelTopPar tbody tr:hover td{ background:#f9fafb; }
  #tpBody::after{ content:""; display:block; height: calc(var(--tp_head) + var(--tp_totalH) + var(--tp_safe)); }

  /* ===== MOBILE (<=640px) ===== */
  @media (max-width:640px){
    #tabelTopPar th, #tabelTopPar td{ padding:.32rem .4rem; }
    #tabelTopPar{ font-size:11px; }
    #tabelTopPar thead th{ font-size:10.5px; }

    /* Hide kolom panjang */
    #tabelTopPar th.col-cabang, #tabelTopPar td.col-cabang,
    #tabelTopPar th.col-alamat, #tabelTopPar td.col-alamat{ display:none; }

    /* Header sticky kiri utk NOREK + DEBITUR */
    #tabelTopPar thead th.col-norek{ left:0 !important; z-index:90; }
    #tabelTopPar thead th.col-debitur{ left:var(--tp_m_colNorek) !important; z-index:89; }

    /* Body sticky + batas lebar konten */
    #tabelTopPar td.col-norek{
      position:sticky; left:0; z-index:41; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06);
      width:var(--tp_m_colNorek); min-width:var(--tp_m_colNorek);
    }
    #tabelTopPar td.col-debitur{
      position:sticky; left:var(--tp_m_colNorek); z-index:40; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06);
      max-width:10.5rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; /* makin pendek di HP */
    }

    /* angka makin sempit */
    #tabelTopPar .col-amt{ width:8rem; min-width:7.2rem; }

    /* TOTAL: norek & debitur tetap freeze */
    #tabelTopPar tbody tr.sticky-total td.col-norek{ left:0; background:#eaf2ff; z-index:91; }
    #tabelTopPar tbody tr.sticky-total td.col-debitur{ left:var(--tp_m_colNorek); background:#eaf2ff; z-index:90; }
  }
</style>

<script>
  // ===== Helpers =====
  const nfID = new Intl.NumberFormat('id-ID');
  const rp   = n => nfID.format(Number(n||0));
  const isMobile = () => window.matchMedia('(max-width:640px)').matches;

  // mid-ellipsis utk string panjang (mis. norek)
  function midEllipsis(str, maxChars){
    const s = String(str ?? '');
    if (s.length <= maxChars) return s;
    const keep = maxChars - 1;                     // sisakan 1 utk '…'
    const front = Math.ceil(keep/2), back = Math.floor(keep/2);
    return s.slice(0, front) + '…' + s.slice(-back);
  }
  // short text biasa (nama/alamat)
  const short = (s, n=20) => {
    const t = String(s ?? '');
    return t.length <= n ? t : t.slice(0, n).trimEnd() + '…';
  };

  // ===== State =====
  let topParData = [];
  let sortKey = 'baki_debet';
  let sortAsc = false;

  // Sticky + scroller
  function setTPSticky(){
    const h   = document.getElementById('tpHead1')?.offsetHeight || 40;
    const tot = document.querySelector('#tabelTopPar tr.sticky-total')?.offsetHeight || 36;
    const holder = document.getElementById('tpScroller');
    holder.style.setProperty('--tp_head', h + 'px');
    holder.style.setProperty('--tp_totalH', tot + 'px');
  }
  function sizeTPScroller(){
    const wrap = document.getElementById('tpScroller');
    if(!wrap) return;
    const rectTop = wrap.getBoundingClientRect().top;
    wrap.style.height = Math.max(260, window.innerHeight - rectTop - 10) + 'px';
  }
  window.addEventListener('resize', ()=>{ setTPSticky(); sizeTPScroller(); });

  // INIT
  (async () => {
    const d = await getLastHarianData();
    if (!d) return;
    closing_date_top_par.value = d.last_closing;
    harian_date_top_par.value  = d.last_created;
    fetchTopPar(d.last_closing, d.last_created);
  })();

  async function getLastHarianData() {
    try {
      const res = await fetch('./api/date/', { method: 'GET' });
      const json = await res.json();
      return json.data || null;
    } catch { return null; }
  }

  formFilterTopPar.addEventListener('submit', e => {
    e.preventDefault();
    fetchTopPar(closing_date_top_par.value, harian_date_top_par.value);
  });

  function fetchTopPar(closing_date, harian_date) {
    loadingTopPar.classList.remove('hidden');
    fetch("./api/flow_par/", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ type: "50 Besar", closing_date, harian_date })
    })
    .then(res => res.json())
    .then(res => {
      topParData = Array.isArray(res.data) ? res.data : [];
      sortKey = 'baki_debet'; sortAsc = false;
      renderTopPar(sortedView());
    })
    .catch(() => {
      document.querySelector("#tpBody").innerHTML =
        `<tr><td colspan="7" class="px-4 py-3 text-red-600">Gagal memuat data.</td></tr>`;
      document.querySelector("#tpTotalRow").innerHTML = '';
    })
    .finally(() => loadingTopPar.classList.add("hidden"));
  }

  function sortedView() {
    const key = sortKey, asc = sortAsc;
    return [...topParData].sort((a, b) => {
      const A = Number(a[key]) || 0;
      const B = Number(b[key]) || 0;
      return asc ? A - B : B - A;
    });
  }

  document.querySelector('#tabelTopPar thead').addEventListener('click', e=>{
    const k = e.target?.dataset?.sort;
    if(!k) return;
    if(sortKey === k) sortAsc = !sortAsc; else { sortKey = k; sortAsc = false; }
    renderTopPar(sortedView());
  });

  function renderTopPar(rows) {
    // TOTAL
    const tBaki = rows.reduce((a,r)=> a + Number(r.baki_debet||0), 0);
    const tTPok = rows.reduce((a,r)=> a + Number(r.tunggakan_pokok||0), 0);
    const tTBng = rows.reduce((a,r)=> a + Number(r.tunggakan_bunga||0), 0);
    // <span class="font-bold">50</span>

    tpTotalRow.innerHTML = `
      <tr class="sticky-total font-semibold text-sm">
        <td class="px-3 py-2 freeze-1 col-cabang"></td>
        <td class="px-3 py-2 freeze-2 col-norek"></td>
        
        <td class="px-3 py-2 col-debitur"></td>
        <td class="px-3 py-2 col-alamat"></td>
        <td class="px-3 py-2 text-right col-amt">${rp(tBaki)}</td>
        <td class="px-3 py-2 text-right col-amt">${rp(tTPok)}</td>
        <td class="px-3 py-2 text-right col-amt">${rp(tTBng)}</td>
      </tr>`;

    // batas karakter adaptif
    const maxNorek   = isMobile() ? 10 : 12;
    const maxDebitur = isMobile() ? 14 : 18;

    // BODY
    let html = '';
    for (const r of rows) {
      const norekFull = r.no_rekening || '-';
      const debFull   = r.nama_nasabah || '-';
      html += `
        <tr class="border-b hover:bg-gray-50">
          <td class="px-3 py-2 freeze-1 col-cabang">${r.nama_kantor||'-'}</td>
          <td class="px-3 py-2 freeze-2 col-norek" title="${norekFull}">${midEllipsis(norekFull, maxNorek)}</td>
          <td class="px-3 py-2 col-debitur" title="${debFull}">${short(debFull, maxDebitur)}</td>
          <td class="px-3 py-2 col-alamat" title="${r.alamat||'-'}">${short(r.alamat, 20)}</td>
          <td class="px-3 py-2 text-right col-amt">${rp(r.baki_debet)}</td>
          <td class="px-3 py-2 text-right col-amt">${rp(r.tunggakan_pokok)}</td>
          <td class="px-3 py-2 text-right col-amt">${rp(r.tunggakan_bunga)}</td>
        </tr>`;
    }
    tpBody.innerHTML = html;

    setTPSticky(); sizeTPScroller();
    setTimeout(()=>{ setTPSticky(); sizeTPScroller(); }, 50);
  }
</script>
