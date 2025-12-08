<!-- 📊 REKAP NPL (sticky header + freeze kiri + responsive toolbar) -->
<div class="max-w-7xl mx-auto px-4 py-4 h-screen flex flex-col">
  <!-- Header / Toolbar -->
  <div class="hdr flex flex-wrap items-start gap-2 mb-3">
    <h1 class="title text-2xl font-bold flex items-center gap-2">
      <span>📊</span><span>Rekap NPL</span>
    </h1>

    <!-- Filter -->
    <form id="formFilterNpl" class="ml-auto sm:ml-auto">
      <div id="filterNPL" class="flex items-center gap-2">
        <label for="closing_date_npl" class="lbl text-sm text-slate-700">Closing:</label>
        <input type="date" id="closing_date_npl" class="inp" required>

        <label for="harian_date_npl" class="lbl text-sm text-slate-700">Harian:</label>
        <input type="date" id="harian_date_npl" class="inp" required>

        <button type="submit" class="btn-icon" title="Terapkan">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="7"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
        </button>
      </div>
    </form>
  </div>

  <!-- Loading -->
  <div id="loadingNpl" class="hidden flex items-center gap-2 text-sm text-gray-600 mb-2">
    <svg class="animate-spin h-5 w-5 text-green-600" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span>Memuat data NPL...</span>
  </div>

  <!-- Scroller -->
  <div id="nplScroller" class="flex-1 min-h-0 overflow-hidden rounded border border-gray-200 bg-white">
    <div class="h-full overflow-auto">
      <table id="tabelNpl" class="min-w-full text-sm text-left text-gray-700">
        <thead class="uppercase">
          <tr id="nplHead1" class="text-xs">
            <th class="px-4 py-2 sticky-npl freeze-1 col1 col-kode">KODE KANTOR</th>
            <th class="px-4 py-2 sticky-npl freeze-2 col2 col-nama">NAMA KANTOR</th>
            <th class="px-4 py-2 text-right sticky-npl" data-sort="npl_closing">NPL CLOSING</th>
            <th class="px-4 py-2 text-right sticky-npl" data-sort="npl_harian">NPL HARIAN</th>
            <th class="px-4 py-2 text-right sticky-npl" data-sort="selisih_npl">SELISIH</th>
            <th class="px-4 py-2 text-right sticky-npl" data-sort="npl_closing_persen">% CLOSING</th>
            <th class="px-4 py-2 text-right sticky-npl" data-sort="npl_harian_persen">% HARIAN</th>
            <th class="px-4 py-2 text-right sticky-npl" data-sort="selisih_npl_persen">% SELISIH</th>
          </tr>
        </thead>
        <!-- TOTAL tepat di bawah header (sticky) -->
        <tbody id="nplTotalRow"></tbody>
        <!-- Baris cabang -->
        <tbody id="nplBody"></tbody>
      </table>
    </div>
  </div>
</div>

<style>
  /* ===== Toolbar ===== */
  .inp{ border:1px solid #cbd5e1; border-radius:.6rem; padding:.5rem .75rem; font-size:14px; background:#fff; }
  .btn-icon{ width:42px; height:42px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center;
             background:#2563eb; color:#fff; box-shadow:0 6px 14px rgba(37,99,235,.25); }
  .btn-icon:hover{ background:#1e40af; }
  .hdr{ row-gap:.5rem; }
  @media (max-width:640px){
    .title{ font-size:1.25rem; }
    .hdr{ flex-direction:column; align-items:flex-start; }
    #filterNPL{ width:100%; gap:.5rem; }
    .lbl{ display:none; }
    .inp{ flex:0 0 auto; width:200px; max-width:70vw; font-size:13px; padding:.45rem .6rem; }
    .btn-icon{ width:40px; height:40px; }
  }

  /* ===== Tabel: sticky header + freeze kiri ===== */
  body{ overflow:hidden; }
  #nplScroller{ --npl_col1:5rem; --npl_col2:16rem; --npl_head:40px; --npl_totalH:36px; --npl_safe:28px; }
  @supports(padding:max(0px)){ #nplScroller{ --npl_safe:max(28px, env(safe-area-inset-bottom)); } }

  #tabelNpl{ font-size: clamp(11px, 1.6vw, 14px); }
  #tabelNpl thead th{ font-size: clamp(10px, 1.4vw, 12px); }

  #tabelNpl .col1{ width:var(--npl_col1); min-width:var(--npl_col1); }
  #tabelNpl .col2{ width:var(--npl_col2); min-width:var(--npl_col2); }
  /* Nama Kantor jangan melebar */
  #tabelNpl th.col-nama, #tabelNpl td.col-nama{
    max-width:var(--npl_col2); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
  }

  /* Freeze kiri */
  #tabelNpl .freeze-1{ position:sticky; left:0; z-index:41;  box-shadow:1px 0 0 rgba(0,0,0,.06); background:#fff; }
  #tabelNpl .freeze-2{ position:sticky; left:var(--npl_col1); z-index:40;  box-shadow:1px 0 0 rgba(0,0,0,.06); background:#fff; }

  /* Sticky header */
  #tabelNpl thead th{ position:sticky; }
  #tabelNpl thead th.sticky-npl{ top:0; background:#d9ead3; z-index:88; }
  #tabelNpl thead th.freeze-1{ left:0; z-index:90; background:#d9ead3; }
  #tabelNpl thead th.freeze-2{ left:var(--npl_col1); z-index:89; background:#d9ead3; }

  /* Sticky TOTAL tepat di bawah header */
  #tabelNpl tbody tr.sticky-total td{
    position:sticky; top:var(--npl_head); background:#eaf2ff; color:#1e40af; z-index:70; border-bottom:1px solid #c7d2fe;
  }
  #tabelNpl tbody tr.sticky-total td.freeze-1{ z-index:91; }
  #tabelNpl tbody tr.sticky-total td.freeze-2{ z-index:90; }

  /* Hilangkan spacer default semua tbody */
  #tabelNpl tbody::after{ content:""; display:block; height:0; }
  /* Spacer hanya untuk BODY supaya baris terakhir tidak ketutup */
  #tabelNpl #nplBody::after{
    content:""; display:block;
    height: calc(var(--npl_head) + var(--npl_totalH) + var(--npl_safe));
  }

  #tabelNpl tbody tr:hover td{ background:#f9fafb; }

  /* ===== Mobile tweaks ===== */
  @media (max-width:640px){
    /* sembunyikan kolom KODE di header + total + body */
    #tabelNpl th.col-kode, #tabelNpl td.col-kode{ display:none; }

    /* geser kolom Nama ke paling kiri dan PERKECIL lebarnya */
    #nplScroller{ --npl_col1:0px; --npl_col2: 9.5rem; } /* 9–11rem aman, bisa disetel lagi */
    #tabelNpl .freeze-2, #tabelNpl thead th.freeze-2{ left:0 !important; }
  }
</style>

<script>
  const nfID  = new Intl.NumberFormat('id-ID');
  const fmt   = n => nfID.format(Number(n || 0));
  const fmt2  = x => (x == null || x === '' ? '0.00' : Number(x).toFixed(2));

  let nplDataRaw = [], nplTotal = null, nplSortKey = '', nplSortAsc = false, abortCtrl;

  /* ===== Sticky & scroller ===== */
  function setNplStickyOffsets(){
    const h = document.getElementById('nplHead1')?.offsetHeight || 40;
    const total = document.querySelector('#tabelNpl tr.sticky-total')?.offsetHeight || 36;
    const holder = document.getElementById('nplScroller');
    holder.style.setProperty('--npl_head', h + 'px');
    holder.style.setProperty('--npl_totalH', total + 'px');
  }
  function sizeNplScroller(){
    const wrap = document.getElementById('nplScroller');
    if(!wrap) return;
    const rectTop = wrap.getBoundingClientRect().top;
    wrap.style.height = Math.max(260, window.innerHeight - rectTop - 18) + 'px';
  }
  window.addEventListener('resize', ()=>{ setNplStickyOffsets(); sizeNplScroller(); });

  /* ===== Init ===== */
  (async () => {
    const d = await getLastHarianData();
    if (!d) return;
    closing_date_npl.value = d.last_closing;
    harian_date_npl.value  = d.last_created;
    fetchNplData(d.last_closing, d.last_created);
    setNplStickyOffsets(); sizeNplScroller();
  })();

  async function getLastHarianData() {
    try { const r = await fetch('./api/date/'); const j = await r.json(); return j.data || null; }
    catch { return null; }
  }

  document.getElementById('formFilterNpl').addEventListener('submit', e => {
    e.preventDefault();
    fetchNplData(closing_date_npl.value, harian_date_npl.value);
  });

  // Header sort (delegation)
  document.querySelector('#tabelNpl thead').addEventListener('click', e => {
    const key = e.target?.dataset?.sort; if (!key) return; sortNpl(key);
  });

  /* ===== Render helpers (warna selisih) ===== */
  const selisihNominal = (n) => {
    const v = Number(n || 0);
    if (v < 0) return { text: `-${fmt(Math.abs(v))}`, cls: 'text-green-700' };
    if (v > 0) return { text: `${fmt(v)}`,           cls: 'text-red-600'  };
    return { text: fmt(0), cls: 'text-gray-700' };
  };
  const selisihPersen = (p) => {
    const v = Number(p || 0);
    if (v < 0) return { text: `-${fmt2(Math.abs(v))}%`, cls: 'text-green-700' };
    if (v > 0) return { text: `${fmt2(v)}%`,            cls: 'text-red-600'  };
    return { text: `${fmt2(0)}%`, cls: 'text-gray-700' };
  };

  /* ===== Fetch + render ===== */
  async function fetchNplData(closing_date, harian_date) {
    loadingNpl.classList.remove('hidden');

    if (abortCtrl) abortCtrl.abort();
    abortCtrl = new AbortController();

    nplTotalRow.innerHTML = `<tr><td colspan="8" class="px-4 py-3 text-gray-500">Memuat...</td></tr>`;
    nplBody.innerHTML = '';

    try {
      const res = await fetch('./api/npl/', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ type: 'NPL', closing_date, harian_date }),
        signal: abortCtrl.signal
      });
      const json = await res.json();
      const data = Array.isArray(json.data) ? json.data : [];

      nplTotal = data.find(d => (d.nama_kantor||'').toUpperCase().includes('TOTAL')) || null;
      nplDataRaw = data.filter(d => !(d.nama_kantor||'').toUpperCase().includes('TOTAL'));

      nplSortKey = 'npl_harian'; nplSortAsc = false;
      renderAll(nplDataRaw);
    } catch (err) {
      if (err.name !== 'AbortError')
        nplTotalRow.innerHTML = `<tr><td colspan="8" class="px-4 py-3 text-red-600">Gagal memuat data</td></tr>`;
    } finally {
      loadingNpl.classList.add('hidden');
    }
  }

  function renderAll(rows){
    nplTotalRow.innerHTML = nplTotal ? totalRowHTML(nplTotal) : '';
    nplBody.innerHTML = rows.map(r => rowHTML(r)).join('');
    setNplStickyOffsets(); sizeNplScroller();
    setTimeout(()=>{ setNplStickyOffsets(); sizeNplScroller(); }, 50);
  }

  function rowHTML(r){
    const n = selisihNominal(r.selisih_npl);
    const p = selisihPersen(r.selisih_npl_persen);
    return `
      <tr class="border-b hover:bg-gray-50">
        <td class="px-4 py-3 text-center freeze-1 col1 col-kode">${r.kode_cabang || '-'}</td>
        <td class="px-4 py-3 freeze-2 col2 col-nama">${r.nama_kantor || '-'}</td>
        <td class="px-4 py-3 text-right">${fmt(r.npl_closing)}</td>
        <td class="px-4 py-3 text-right">${fmt(r.npl_harian)}</td>
        <td class="px-4 py-3 text-right ${n.cls}">${n.text}</td>
        <td class="px-4 py-3 text-right">${fmt2(r.npl_closing_persen)}%</td>
        <td class="px-4 py-3 text-right">${fmt2(r.npl_harian_persen)}%</td>
        <td class="px-4 py-3 text-right ${p.cls}">${p.text}</td>
      </tr>`;
  }

  function totalRowHTML(t){
    const n = selisihNominal(t.selisih_npl);
    const p = selisihPersen(t.selisih_npl_persen);
    return `
      <tr class="sticky-total font-semibold text-sm">
        <td class="px-4 py-2 freeze-1 col1 col-kode">TOTAL</td>
        <td class="px-4 py-2 freeze-2 col2 col-nama"><span class="ttl-mobile">TOTAL</span></td>
        <td class="px-4 py-2 text-right">${fmt(t.npl_closing)}</td>
        <td class="px-4 py-2 text-right">${fmt(t.npl_harian)}</td>
        <td class="px-4 py-2 text-right ${n.cls}">${n.text}</td>
        <td class="px-4 py-2 text-right">${fmt2(t.npl_closing_persen)}%</td>
        <td class="px-4 py-2 text-right">${fmt2(t.npl_harian_persen)}%</td>
        <td class="px-4 py-2 text-right ${p.cls}">${p.text}</td>
      </tr>`;
  }

  function sortNpl(key){
    if (nplSortKey === key) nplSortAsc = !nplSortAsc;
    else { nplSortKey = key; nplSortAsc = false; }
    const sorted = [...nplDataRaw].sort((a,b)=>{
      const A = Number(a[key] || 0), B = Number(b[key] || 0);
      return nplSortAsc ? A - B : B - A;
    });
    renderAll(sorted);
  }
</script>
