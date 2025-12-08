<!-- KOLEKTIBILITAS (sticky, freeze, mobile, abort fetch) -->
<div class="max-w-7xl mx-auto px-4 py-6 h-screen flex flex-col">
  <div class="flex flex-wrap items-start gap-2 mb-3">
    <h1 class="text-2xl font-bold flex items-center gap-2">
      <span>📈</span><span>Rekap Kolektibilitas</span>
    </h1>

    <!-- Filter -->
    <form id="formFilterKolektibilitas" class="ml-auto">
      <div id="filterKolek" class="flex items-center gap-2">
        <label for="harian_date_kolek" class="lbl text-sm text-slate-700">Tanggal Harian:</label>
        <input type="date" id="harian_date_kolek" class="inp" required>
        <button type="submit" id="btnKolek" class="btn-icon" title="Tampilkan">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="7"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
        </button>
      </div>
    </form>
  </div>

  <!-- Loading -->
  <div id="loadingKolek" class="hidden flex items-center gap-2 text-sm text-gray-600 mb-2">
    <svg class="animate-spin h-5 w-5 text-blue-600" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span>Memuat data kolektibilitas...</span>
  </div>

  <!-- Scroller tabel -->
  <div id="kolScroller" class="flex-1 min-h-0 overflow-hidden rounded border border-gray-200 bg-white">
    <div class="h-full overflow-auto">
      <table id="tabelKolektibilitas" class="min-w-full text-sm text-left text-gray-700">
        <thead class="uppercase">
          <tr id="kolHead1" class="text-xs">
            <th class="px-4 py-2 sticky-kol freeze-1 col1 col-kode">Kode Kantor</th>
            <th class="px-4 py-2 align-top freeze-2 col2 col-nama">Nama Kantor</th>
            <th class="px-4 py-2 text-right sticky-kol">L<br><small class="text-gray-600">NOA / BD</small></th>
            <th class="px-4 py-2 text-right sticky-kol">DP<br><small class="text-gray-600">NOA / BD</small></th>
            <th class="px-4 py-2 text-right sticky-kol">KL<br><small class="text-gray-600">NOA / BD</small></th>
            <th class="px-4 py-2 text-right sticky-kol">D<br><small class="text-gray-600">NOA / BD</small></th>
            <th class="px-4 py-2 text-right sticky-kol">M<br><small class="text-gray-600">NOA / BD</small></th>
            <th class="px-4 py-2 text-right sticky-kol">Total NPL<br><small class="text-gray-600">NOA / BD</small></th>
            <th class="px-4 py-2 text-right sticky-kol">Total<br><small class="text-gray-600">NOA / BD</small></th>
            <th class="px-4 py-2 text-right sticky-kol">% NPL</th>
          </tr>
        </thead>
        <tbody id="tbodyKolek"></tbody>
      </table>
    </div>
  </div>
</div>

<style>
  /* Kontrol & tombol */
  .inp{ border:1px solid #cbd5e1; border-radius:.6rem; padding:.5rem .75rem; font-size:14px; background:#fff; }
  .btn-icon{ width:42px; height:42px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center;
             background:#2563eb; color:#fff; box-shadow:0 6px 14px rgba(37,99,235,.25); }
  .btn-icon:hover{ background:#1e40af; }
  .btn-icon[disabled]{ opacity:.6; cursor:not-allowed; }
  .lbl{ font-size:13px; color:#334155; }

  /* Header responsif */
  .hdr{ row-gap:.5rem; }
  @media (max-width:640px){
    #filterKolek{ width:100%; gap:.5rem; }
    .lbl{ display:none; }
    .inp{ flex:1 1 0; min-width:0; font-size:13px; padding:.45rem .6rem; }
    .btn-icon{ width:40px; height:40px; }
  }

  /* Tabel sticky + freeze */
  body{ overflow:hidden; }
  #kolScroller{ --kol_col1:6rem; --kol_col2:18rem; --kol_headH:40px; --kol_totalH:36px; --kol_safe:28px; }
  @supports(padding:max(0px)){ #kolScroller{ --kol_safe:max(28px, env(safe-area-inset-bottom)); } }

  #tabelKolektibilitas .col1{ width:var(--kol_col1); min-width:var(--kol_col1); }
  #tabelKolektibilitas .col2{ width:var(--kol_col2); min-width:var(--kol_col2); }

  #tabelKolektibilitas .freeze-1{ position:sticky; left:0; z-index:41; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06); }
  #tabelKolektibilitas .freeze-2{ position:sticky; left:var(--kol_col1); z-index:40; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06); }

  #tabelKolektibilitas thead th{ position:sticky; top:0; background:#d9ead3; z-index:88; }
  #tabelKolektibilitas thead th.freeze-1{ left:0; z-index:91 !important; background:#d9ead3; }
  #tabelKolektibilitas thead th.freeze-2{ left:var(--kol_col1); z-index:90 !important; background:#d9ead3; }

  /* Baris TOTAL sticky di bawah header */
  #tabelKolektibilitas tbody tr.sticky-total td{
    position:sticky; top:var(--kol_headH); z-index:70; background:#eaf2ff; color:#1e40af; border-bottom:1px solid #c7d2fe;
  }
  #tabelKolektibilitas tbody tr.sticky-total td.freeze-1{ z-index:91; }
  #tabelKolektibilitas tbody tr.sticky-total td.freeze-2{ z-index:90; }

  #tabelKolektibilitas tbody tr:hover td{ background:#f9fafb; }

  /* Spacer bawah agar baris terakhir tidak ketutup */
  #tabelKolektibilitas tbody::after{
    content:""; display:block; height: calc(var(--kol_headH) + var(--kol_totalH) + var(--kol_safe));
  }

  /* Mobile tweaks */
  @media (max-width:640px){
    #tabelKolektibilitas{ font-size:12px; }
    #tabelKolektibilitas thead th{ font-size:11px; }
    #tabelKolektibilitas th, #tabelKolektibilitas td{ padding:.5rem .5rem; }

    /* Sembunyikan kolom KODE di mobile; freeze Nama jadi kiri */
    #tabelKolektibilitas th.col-kode, #tabelKolektibilitas td.col-kode{ display:none; }
    #kolScroller{ --kol_col1:0px; }
    #tabelKolektibilitas .freeze-2, #tabelKolektibilitas thead th.freeze-2{ left:0 !important; }
  }

  /* === Fix gap kiri di MOBILE untuk Kolektibilitas === */
@media (max-width:640px){
  /* tarik scroller keluar sedikit agar rata kiri-kanan */
  #kolScroller{ margin-left:-8px; margin-right:-8px; }

  /* pastikan kolom KODE benar2 nol dan disembunyikan */
  #tabelKolektibilitas th.col-kode,
  #tabelKolektibilitas td.col-kode{ display:none !important; }
  #tabelKolektibilitas .col1{ width:0 !important; min-width:0 !important; }
  #kolScroller{ --kol_col1:0px; } /* offset freeze mengikuti 0 */

  /* kolom NAMA jadi paling kiri (freeze tepat left:0) */
  #tabelKolektibilitas .freeze-2,
  #tabelKolektibilitas thead th.freeze-2{ left:0 !important; }

  /* rapatkan padding biar tidak terlihat mengambang */
  #tabelKolektibilitas{ border-collapse:separate; border-spacing:0; }
  #tabelKolektibilitas th, #tabelKolektibilitas td{
    padding-left:.375rem;  /* ~6px */
    padding-right:.5rem;   /* 8px */
  }
}


/* ==== Lebar kolom 'Nama Kantor' (col2) ==== */
#kolScroller{ --kol_col2: 13rem; }                 /* default desktop: dari 16rem → 13rem */
#tabelKolektibilitas .col2,
#tabelKolektibilitas th.col-nama,
#tabelKolektibilitas td.col-nama{
  width: var(--kol_col2);
  min-width: var(--kol_col2);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;                          /* potong nama panjang */
}

/* Tablet */
@media (max-width:1023px){
  #kolScroller{ --kol_col2: 11.5rem; }
}

/* Mobile */
@media (max-width:640px){
  #kolScroller{ --kol_col2: 9.5rem; }              /* makin ramping di HP */
}

</style>

<script>
  const kolekApiUrl = './api/kredit/';
  let kolekAbort = null;

  // Helpers
  const fmtInt = n => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(+n||0);
  const fmtRp  = n => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(+n||0);

  function setKolSticky(){
    const h = document.getElementById('kolHead1')?.offsetHeight || 40;
    const t = document.querySelector('#tabelKolektibilitas tr.sticky-total')?.offsetHeight || 36;
    const holder = document.getElementById('kolScroller');
    holder.style.setProperty('--kol_headH', h + 'px');
    holder.style.setProperty('--kol_totalH', t + 'px');
  }
  function sizeKolScroller(){
    const wrap = document.getElementById('kolScroller');
    const rectTop = wrap.getBoundingClientRect().top;
    wrap.style.height = Math.max(260, window.innerHeight - rectTop - 18) + 'px';
  }
  window.addEventListener('resize', ()=>{ setKolSticky(); sizeKolScroller(); });

  // Init
  (async () => {
    try{
      const r = await fetch('./api/date/'); const j = await r.json();
      const last = j?.data?.last_created || new Date().toISOString().slice(0,10);
      document.getElementById('harian_date_kolek').value = last;
      await fetchKolektibilitasData(last);
      setKolSticky(); sizeKolScroller();
    }catch(e){ console.error(e); }
  })();

  document.getElementById('formFilterKolektibilitas').addEventListener('submit', (e)=>{
    e.preventDefault();
    fetchKolektibilitasData(document.getElementById('harian_date_kolek').value);
  });

  function setFilterDisabled(dis){ document.getElementById('btnKolek').disabled = !!dis; }

  async function fetchKolektibilitasData(harian_date){
    if(kolekAbort) kolekAbort.abort();
    kolekAbort = new AbortController();

    document.getElementById('loadingKolek').classList.remove('hidden');
    setFilterDisabled(true);

    try{
      const res = await fetch(kolekApiUrl, {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ type:'kolektibilitas', harian_date }),
        signal: kolekAbort.signal
      });
      const json = await res.json();
      const rows = (json?.data || []).filter(d => d?.kode_cabang !== null); // buang null

      renderKolektibilitasTable(rows);
    }catch(err){
      if(err.name !== 'AbortError') console.error('fetch kolektibilitas error', err);
      renderKolektibilitasTable([]);
    }finally{
      document.getElementById('loadingKolek').classList.add('hidden');
      setFilterDisabled(false);
      setKolSticky(); sizeKolScroller(); setTimeout(()=>{ setKolSticky(); sizeKolScroller(); }, 50);
    }
  }

  function renderKolektibilitasTable(data){
    const tb = document.getElementById('tbodyKolek');
    tb.innerHTML = '';

    // Hitung total
    const T = {
      noa_L:0, bd_L:0, noa_DP:0, bd_DP:0, noa_KL:0, bd_KL:0,
      noa_D:0, bd_D:0, noa_M:0, bd_M:0, noa_npl:0, bd_npl:0,
      total_noa:0, total_bd:0
    };

    // Bangun baris data
    const buf = [];

    // TOTAL sticky disisipkan paling atas (dikosongkan dulu, diisi setelah loop)
    buf.push(`
      <tr class="sticky-total font-semibold text-sm text-blue-800">
        <td class="px-4 py-2 freeze-1 col1 col-kode"></td>
        <td class="px-4 py-2 freeze-2 col2 col-nama"><span class="ttl-mobile">TOTAL</span></td>
        <td class="px-4 py-2 text-right" data-ttl="L"></td>
        <td class="px-4 py-2 text-right" data-ttl="DP"></td>
        <td class="px-4 py-2 text-right" data-ttl="KL"></td>
        <td class="px-4 py-2 text-right" data-ttl="D"></td>
        <td class="px-4 py-2 text-right" data-ttl="M"></td>
        <td class="px-4 py-2 text-right" data-ttl="NPL"></td>
        <td class="px-4 py-2 text-right" data-ttl="TOTAL"></td>
        <td class="px-4 py-2 text-right" data-ttl="PERSEN"></td>
      </tr>
    `);

    data.forEach(r=>{
      T.noa_L+=+r.noa_L||0; T.bd_L+=+r.bd_L||0;
      T.noa_DP+=+r.noa_DP||0; T.bd_DP+=+r.bd_DP||0;
      T.noa_KL+=+r.noa_KL||0; T.bd_KL+=+r.bd_KL||0;
      T.noa_D+=+r.noa_D||0; T.bd_D+=+r.bd_D||0;
      T.noa_M+=+r.noa_M||0; T.bd_M+=+r.bd_M||0;
      T.noa_npl+=+r.noa_npl||0; T.bd_npl+=+r.bd_npl||0;
      T.total_noa+=+r.total_noa||0; T.total_bd+=+r.total_bd||0;

      buf.push(`
        <tr class="border-b hover:bg-gray-50">
          <td class="px-4 py-3 text-center freeze-1 col1 col-kode">${r.kode_cabang ?? '-'}</td>
          <td class="px-4 py-3 freeze-2 col2 col-nama">${r.nama_kantor ?? '-'}</td>

          <td class="px-4 py-3 text-right">
            ${fmtInt(r.noa_L)}<br><span class="text-xs text-gray-500">${fmtRp(r.bd_L)}</span>
          </td>
          <td class="px-4 py-3 text-right">
            ${fmtInt(r.noa_DP)}<br><span class="text-xs text-gray-500">${fmtRp(r.bd_DP)}</span>
          </td>
          <td class="px-4 py-3 text-right">
            ${fmtInt(r.noa_KL)}<br><span class="text-xs text-gray-500">${fmtRp(r.bd_KL)}</span>
          </td>
          <td class="px-4 py-3 text-right">
            ${fmtInt(r.noa_D)}<br><span class="text-xs text-gray-500">${fmtRp(r.bd_D)}</span>
          </td>
          <td class="px-4 py-3 text-right">
            ${fmtInt(r.noa_M)}<br><span class="text-xs text-gray-500">${fmtRp(r.bd_M)}</span>
          </td>
          <td class="px-4 py-3 text-right">
            ${fmtInt(r.noa_npl)}<br><span class="text-xs text-gray-500">${fmtRp(r.bd_npl)}</span>
          </td>
          <td class="px-4 py-3 text-right">
            ${fmtInt(r.total_noa)}<br><span class="text-xs text-gray-500">${fmtRp(r.total_bd)}</span>
          </td>
          <td class="px-4 py-3 text-right">${persen(r.bd_npl, r.total_bd)}</td>
        </tr>
      `);
    });

    tb.innerHTML = buf.join('');

    // Isi sel TOTAL (pakai data-ttl)
    const persenNpl = persenRaw(T.bd_npl, T.total_bd);
    const ttlCells = {
      L:     `${fmtInt(T.noa_L)}<br><span class="text-xs text-gray-500">${fmtRp(T.bd_L)}</span>`,
      DP:    `${fmtInt(T.noa_DP)}<br><span class="text-xs text-gray-500">${fmtRp(T.bd_DP)}</span>`,
      KL:    `${fmtInt(T.noa_KL)}<br><span class="text-xs text-gray-500">${fmtRp(T.bd_KL)}</span>`,
      D:     `${fmtInt(T.noa_D)}<br><span class="text-xs text-gray-500">${fmtRp(T.bd_D)}</span>`,
      M:     `${fmtInt(T.noa_M)}<br><span class="text-xs text-gray-500">${fmtRp(T.bd_M)}</span>`,
      NPL:   `${fmtInt(T.noa_npl)}<br><span class="text-xs text-gray-500">${fmtRp(T.bd_npl)}</span>`,
      TOTAL: `${fmtInt(T.total_noa)}<br><span class="text-xs text-gray-500">${fmtRp(T.total_bd)}</span>`,
      PERSEN: persenNpl
    };
    tb.querySelectorAll('[data-ttl]').forEach(el=>{
      const k = el.getAttribute('data-ttl');
      el.innerHTML = ttlCells[k] ?? '';
    });
  }

  // Helper persentase
  const persenRaw = (num, den) => (den? ((+num||0)*100/(+den||0)).toFixed(2):'0.00') + '%';
  const persen    = (num, den) => persenRaw(num, den);
</script>
