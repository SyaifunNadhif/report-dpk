<!-- 💰 REKAP CKPN PER PRODUK (freeze bucket + compact detail + bottom spacer fix) -->
<div class="max-w-7xl mx-auto px-4 py-4 h-screen flex flex-col">
  <div class="flex flex-wrap items-start gap-2 mb-2">
    <h1 class="text-2xl font-bold flex items-center gap-2">
      <span>💰</span><span>Rekap CKPN per Produk</span>
    </h1>

    <!-- Filter -->
    <form id="formFilterCkpnProduk" class="ml-auto filterbar">
      <div class="flex items-center gap-2">
        <label for="harian_date_ckpn_prod" class="text-[13px] text-slate-700 f-label">Tanggal Harian:</label>
        <input type="date" id="harian_date_ckpn_prod" class="border rounded-lg px-3 py-2 text-[14px]">
        <label for="selCabang" class="text-[13px] text-slate-700 f-label">Cabang:</label>
        <select id="selCabang" class="border rounded-lg px-3 py-2 text-[14px] min-w-[220px]">
          <option value="">Konsolidasi</option>
        </select>
        <button type="submit" class="w-10 h-10 rounded-full inline-flex items-center justify-center bg-blue-600 text-white hover:bg-blue-700 shadow-md" title="Tampilkan">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
        </button>
      </div>
    </form>
  </div>

  <!-- Loading -->
  <div id="loadingCkpnProduk" class="hidden flex items-center gap-2 text-sm text-gray-600 mb-2">
    <svg class="animate-spin h-5 w-5 text-blue-600" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
    <span>Memuat data CKPN produk...</span>
  </div>

  <!-- Scroller tabel utama -->
  <div id="cpScroller" class="flex-1 min-h-0 overflow-hidden rounded border border-gray-200 bg-white">
    <div id="cpScrollerInner" class="h-full overflow-auto">
      <table id="tabelCkpnProduk" class="min-w-full text-sm text-left text-gray-700">
        <thead class="uppercase">
          <tr id="cpHead1" class="text-xs">
            <th class="px-4 py-2 sticky-cp freeze-1 col1 col-kode">KODE</th>
            <th class="px-4 py-2 sticky-cp freeze-2 col2 col-nama">NAMA PRODUK</th>
            <th class="px-4 py-2 text-right sticky-cp col-noa">NOA</th>
            <th class="px-4 py-2 text-right sticky-cp col-ead">EAD</th>
            <th class="px-4 py-2 text-right sticky-cp col-ind">CKPN INDIVIDUAL</th>
            <th class="px-4 py-2 text-right sticky-cp col-coll">CKPN KOLEKTIF</th>
            <th class="px-4 py-2 text-right sticky-cp col-tot">CKPN TOTAL</th>
            <th class="px-2 py-2 text-center sticky-cp col-det">DETAIL</th>
          </tr>
        </thead>
        <tbody class="darsi pb-5"></tbody>
      </table>
    </div>
  </div>
</div>

<style>
  body{ overflow:hidden; }

  /* ==== Toolbar filter (dibuat horizontal & tidak ketutup) ==== */
  .filterbar{ position:relative; z-index: 15; }   /* di atas konten lain */
  @media (max-width:640px){
    #formFilterCkpnProduk > div{
      flex-wrap: nowrap;
      overflow-x: auto;                 /* bisa geser kalau sempit */
      -webkit-overflow-scrolling: touch;
      gap: .5rem;
      white-space: nowrap;
      padding-bottom: .25rem;
    }
    /* Sembunyikan label di mobile, sisakan kontrolnya */
    .f-label{ display:none; }
    /* Biar muat di layar kecil */
    #harian_date_ckpn_prod{ width: 9.5rem; }
    #selCabang{ min-width: 12rem; }
  }

  /* ===== Sticky & sizing (tabel utama) ===== */
  #cpScroller{
    --cp_col1:6rem; --cp_col2:18rem; --cp_head:40px; --cp_totalH:36px; --cp_spacer:96px;
  }
  @supports(padding:max(0px)){ #cpScroller{ --cp_spacer:max(var(--cp_spacer), env(safe-area-inset-bottom)); } }

  #tabelCkpnProduk .col1{ width:var(--cp_col1); min-width:var(--cp_col1); }
  #tabelCkpnProduk .col2{ width:var(--cp_col2); min-width:var(--cp_col2); }

  #tabelCkpnProduk .freeze-1{ position:sticky; left:0; z-index:41; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06); }
  #tabelCkpnProduk .freeze-2{ position:sticky; left:var(--cp_col1); z-index:40; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06); }

  #tabelCkpnProduk thead th{ position:sticky; }
  #tabelCkpnProduk thead th.sticky-cp{ top:0; background:#d9ead3; z-index:88; }
  #tabelCkpnProduk thead th.freeze-1{ left:0; z-index:90; background:#d9ead3; }
  #tabelCkpnProduk thead th.freeze-2{ left:var(--cp_col1); z-index:89; background:#d9ead3; }

  #tabelCkpnProduk tbody tr.cp-total td{
    position:sticky; top:var(--cp_head);
    background:#eaf2ff; color:#1e40af; z-index:70; border-bottom:1px solid #c7d2fe;
  }
  #tabelCkpnProduk tbody tr.cp-total td.freeze-1{ z-index:91; }
  #tabelCkpnProduk tbody tr.cp-total td.freeze-2{ z-index:90; }

  #tabelCkpnProduk tbody tr:hover td{ background:#f9fafb; }

  #tabelCkpnProduk tbody.darsi::after{
    content:""; display:block;
    height: calc(var(--cp_head) + var(--cp_totalH) + var(--cp_spacer));
  }

  .toggle-btn{ display:inline-flex; align-items:center; gap:.45rem; padding:.35rem .65rem; border:1px solid #e2e8f0; border-radius:999px; background:#fff; color:#334155; }
  .toggle-btn:hover{ background:#f8fafc; }
  .toggle-arrow{ transition:transform .15s ease; }
  .toggle-open .toggle-arrow{ transform:rotate(90deg); }

  .detail-wrap{ overflow-x:auto; position:relative; padding:6px 8px; }
  .nested{ background:#f9fafb; table-layout:fixed; width:100%; border-collapse:separate; border-spacing:0; }
  .nested thead{ background:#eaf2ff; }
  .nested th, .nested td{ padding:.26rem .75rem; line-height:1.2; }
  .nested .n-total td{ background:#dbeafe; font-weight:600; }

  /* Freeze kolom Bucket pada detail */
  table[id^="tblDetail_"] thead th.bkt,
  table[id^="tblDetail_"] tbody td.bkt{
    position:sticky; left:0; z-index:2; background:#f9fafb; box-shadow:1px 0 0 rgba(0,0,0,.06);
  }
  table[id^="tblDetail_"] thead th.bkt{ background:#eaf2ff; z-index:3; }

  /* MOBILE: sembunyikan PD & EAD di detail agar ringkas */
  @media (max-width:640px){
    #tabelCkpnProduk{ font-size:12px; }
    #tabelCkpnProduk thead th{ font-size:11px; }
    #tabelCkpnProduk th, #tabelCkpnProduk td{ padding:.5rem .5rem; }

    #tabelCkpnProduk th.col-kode, #tabelCkpnProduk td.col-kode,
    #tabelCkpnProduk th.col-noa,  #tabelCkpnProduk td.col-noa,
    #tabelCkpnProduk th.col-ead,  #tabelCkpnProduk td.col-ead,
    #tabelCkpnProduk th.col-ind,  #tabelCkpnProduk td.col-ind,
    #tabelCkpnProduk th.col-coll, #tabelCkpnProduk td.col-coll{ display:none; }

    #cpScroller{ --cp_col1:0px; }
    #tabelCkpnProduk .freeze-2, #tabelCkpnProduk thead th.freeze-2{ left:0 !important; }

    table[id^="tblDetail_"] .col-pd,
    table[id^="tblDetail_"] .col-ead{ display:none; }  /* << EAD di-hide di mobile */
  }

  /* === 1) Nama Produk: sempit + ellipsis ============================== */
#tabelCkpnProduk th.col-nama,
#tabelCkpnProduk td.col-nama{
  max-width: var(--cp_col2);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Desktop (tetap lega) */
#cpScroller{ --cp_col2: 18rem; }

/* Mobile: kecilkan lebar kolom NAMA PRODUK biar kolom kanan muat */
@media (max-width:640px){
  #cpScroller{ --cp_col2: 10.5rem; }      /* ≈168px */
}
@media (max-width:420px){
  #cpScroller{ --cp_col2: 9.25rem; }      /* ≈148px untuk layar sangat kecil */
}

/* === 2) Detail: TOTAL lebih kontras + bucket freeze kuat ============= */
.nested .n-total td{
  background:#cfe8ff !important;   /* lebih kontras dari #dbeafe */
  font-weight:700;
  border-top:1px solid #b6d2fb;
}

/* Bucket tetap freeze + di-ellipsis jika panjang */
.nested thead th.bkt,
.nested tbody td.bkt{
  position: sticky;
  left: 0;
  z-index: 2;                      /* body */
  background:#f9fafb;
  box-shadow: 1px 0 0 rgba(0,0,0,.06);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.nested thead th.bkt{ z-index: 3; background:#eaf2ff; }

/* Mobile: batasi lebar teks bucket supaya kolom kanan tetap terlihat */
@media (max-width:640px){
  .nested tbody td.bkt{ max-width: 9.5rem; }   /* ~152px */
}

/* === 3) Font responsif (semua device nyaman dibaca) ================= */
#tabelCkpnProduk{
  font-size: clamp(11px, 1.6vw, 14px);
}
#tabelCkpnProduk thead th{
  font-size: clamp(10px, 1.4vw, 12px);
}
.nested{
  font-size: clamp(10px, 1.5vw, 13px);
}

</style>

<script>
  const ckpnApiUrl = './api/ckpn/';
  const doFetch = (url, opts) => (window.apiFetch ? window.apiFetch(url, opts) : fetch(url, opts));

  const nfID = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
  const nfPct= new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const fmt  = n => nfID.format(Number(n||0));
  const pct2 = x => nfPct.format(Number(x||0)) + '%';
  const num  = v => Number.parseFloat(v||0) || 0;

  const BUCKET_ORDER = ['ASSET BAIK (0-7 non-restruk)','0-30','31-90','91-180','181-360','>360'];

  function setCPStickyOffsets(){
    const h = document.getElementById('cpHead1')?.offsetHeight || 40;
    const totalH = document.querySelector('#tabelCkpnProduk tr.cp-total')?.offsetHeight || 36;
    const holder = document.getElementById('cpScroller');
    holder.style.setProperty('--cp_head', h + 'px');
    holder.style.setProperty('--cp_totalH', totalH + 'px');
  }
  function sizeCPScroller(){
    const wrap = document.getElementById('cpScroller');
    if(!wrap) return;
    const rectTop = wrap.getBoundingClientRect().top;
    const GAP = 18;
    wrap.style.height = Math.max(260, window.innerHeight - rectTop - GAP) + 'px';
  }
  function setBottomSpacer(){
    const px = (window.innerWidth < 640) ? 140 : 110;
    document.getElementById('cpScroller')?.style.setProperty('--cp_spacer', px + 'px');
  }
  function setLayoutVars(){ setCPStickyOffsets(); sizeCPScroller(); setBottomSpacer(); }
  window.addEventListener('resize', setLayoutVars);

  (async () => {
    try{
      const r = await fetch('./api/date/'); const j = await r.json();
      document.getElementById('harian_date_ckpn_prod').value = j?.data?.last_created || new Date().toISOString().slice(0,10);
    }catch{
      document.getElementById('harian_date_ckpn_prod').value = new Date().toISOString().slice(0,10);
    }
    await populateCabangOptions();
    fetchCkpnProduk();
    setLayoutVars();
  })();

  async function populateCabangOptions(){
    const sel = document.getElementById('selCabang');
    try{
      const res = await fetch('./api/kode/', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({type:'kode_kantor'}) });
      const json = await res.json();
      const list = Array.isArray(json.data) ? json.data : [];
      let html = `<option value="">Konsolidasi</option>`;
      list.filter(x=>x.kode_kantor && x.kode_kantor!=='000')
          .sort((a,b)=> String(a.kode_kantor).localeCompare(String(b.kode_kantor)))
          .forEach(it=>{
            const code = String(it.kode_kantor).padStart(3,'0');
            const name = it.nama_kantor || it.nama_cabang || '';
            html += `<option value="${code}">${code} — ${name}</option>`;
          });
      sel.innerHTML = html;
    }catch{
      sel.innerHTML = `<option value="">Konsolidasi</option>`;
    }
  }

  document.getElementById('formFilterCkpnProduk').addEventListener('submit', (e)=>{ e.preventDefault(); fetchCkpnProduk(); });

  async function fetchCkpnProduk(){
    const date = document.getElementById('harian_date_ckpn_prod').value;
    const kode = document.getElementById('selCabang').value || null;
    document.getElementById('loadingCkpnProduk').classList.remove('hidden');
    try{
      const res = await doFetch(ckpnApiUrl, { method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ type:'rekap ckpn produk', harian_date:date, kode_cabang:kode })});
      const json = await res.json();
      renderTable(json?.data?.produk || []);
    }catch(err){
      console.error('fetch ckpn produk error', err);
      renderTable([]);
    }finally{
      document.getElementById('loadingCkpnProduk').classList.add('hidden');
    }
  }

  function renderTable(rows){
    const tbody = document.querySelector('#tabelCkpnProduk tbody');
    tbody.innerHTML = '';

    let TTL_NOA=0, TTL_EAD=0, TTL_INDV=0, TTL_COLL=0;
    rows.forEach(p=>{ TTL_NOA+=num(p.noa); TTL_EAD+=num(p.ead); TTL_INDV+=num(p.ckpn_individual); TTL_COLL+=num(p.ckpn_collective); });
    const TTL_TOT = TTL_INDV + TTL_COLL;

    tbody.insertAdjacentHTML('beforeend', `
      <tr class="cp-total font-semibold text-sm">
        <td class="px-4 py-2 freeze-1 col1 col-kode"></td>
        <td class="px-4 py-2 freeze-2 col2 col-nama"><span class="ttl-mobile">TOTAL</span></td>
        <td class="px-4 py-2 text-right col-noa">${fmt(TTL_NOA)}</td>
        <td class="px-4 py-2 text-right col-ead">${fmt(TTL_EAD)}</td>
        <td class="px-4 py-2 text-right col-ind">${fmt(TTL_INDV)}</td>
        <td class="px-4 py-2 text-right col-coll">${fmt(TTL_COLL)}</td>
        <td class="px-4 py-2 text-right col-tot">${fmt(TTL_TOT)}</td>
        <td class="px-2 py-2 text-center col-det"></td>
      </tr>
    `);

    rows.sort((a,b)=> String(a.product_code||'').localeCompare(String(b.product_code||'')));

    for(const p of rows){
      const pc   = p.product_code || '-';
      const np   = p.nama_produk || '-';
      const noa  = num(p.noa);
      const ead  = num(p.ead);
      const indv = num(p.ckpn_individual);
      const coll = num(p.ckpn_collective);
      const tot  = indv + coll;
      const detId = `det_${pc}`;

      tbody.insertAdjacentHTML('beforeend', `
        <tr class="border-b hover:bg-gray-50">
          <td class="px-4 py-3 font-medium freeze-1 col1 col-kode">${pc}</td>
          <td class="px-4 py-3 font-medium freeze-2 col2 col-nama">${np}</td>
          <td class="px-4 py-3 text-right col-noa">${fmt(noa)}</td>
          <td class="px-4 py-3 text-right col-ead">${fmt(ead)}</td>
          <td class="px-4 py-3 text-right col-ind">${fmt(indv)}</td>
          <td class="px-4 py-3 text-right col-coll">${fmt(coll)}</td>
          <td class="px-4 py-3 text-right font-semibold col-tot">${fmt(tot)}</td>
          <td class="px-2 py-3 text-center col-det">
            <button class="toggle-btn" data-target="${detId}" aria-expanded="false">
              <svg class="toggle-arrow" width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01-.02-1.06L10.586 10 7.19 6.29a.75.75 0 111.06-1.06l4.5 4.5a.75.75 0 010 1.06l-4.5 4.5a.75.75 0 01-1.06 0z" clip-rule="evenodd"/>
              </svg>
            </button>
          </td>
        </tr>
        <tr id="${detId}" class="hidden">
          <td colspan="8" class="p-0">${renderDetailTable(p.detail, pc)}</td>
        </tr>
      `);
    }

    tbody.querySelectorAll('.toggle-btn').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        const id = btn.getAttribute('data-target');
        const row = document.getElementById(id);
        const willOpen = row.classList.contains('hidden');
        row.classList.toggle('hidden');
        btn.classList.toggle('toggle-open', willOpen);
        btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
      });
    });

    setLayoutVars();
    setTimeout(setLayoutVars, 50);
  }

  /* Detail table with FREEZE bucket + compact rows */
  function renderDetailTable(detail = {}, code = 'x') {
    const keys = [
      ...BUCKET_ORDER.filter(k => detail?.[k] != null),
      ...Object.keys(detail || {}).filter(k => !BUCKET_ORDER.includes(k))
    ];

    let tNoa = 0, tEad = 0, tCk = 0, sumPdEad = 0;
    for (const k of keys) {
      const it = detail[k] || {};
      const noa = num(it.noa), ead = num(it.ead_sum), pd = num(it.pd_percent), ck = num(it.ckpn_collective);
      tNoa += noa; tEad += ead; tCk += ck; sumPdEad += ead * pd;
    }
    const tPd = tEad ? (sumPdEad / tEad) : 0;

    return `
      <div class="detail-wrap">
        <table id="tblDetail_${code}" class="nested min-w-full text-xs text-gray-700 rounded border">
          <colgroup>
            <col style="width:32%">
            <col style="width:12%">
            <col style="width:22%" class="col-ead"><!-- beri class utk hide di mobile -->
            <col style="width:10%" class="col-pd">
            <col style="width:14%">
          </colgroup>
          <thead>
            <tr>
              <th class="bkt text-left">Bucket</th>
              <th class="text-right">NOA</th>
              <th class="text-right col-ead">EAD</th>
              <th class="text-right col-pd">PD</th>
              <th class="text-right">CKPN Kolektif</th>
            </tr>
          </thead>
          <tbody>
            <tr class="n-total border-t">
              <td class="bkt">TOTAL</td>
              <td class="text-right">${fmt(tNoa)}</td>
              <td class="text-right col-ead">${fmt(tEad)}</td>
              <td class="text-right col-pd">${pct2(tPd)}</td>
              <td class="text-right">${fmt(tCk)}</td>
            </tr>
            ${keys.map(k => {
              const it = detail[k] || {};
              return `
                <tr class="border-t">
                  <td class="bkt">${k}</td>
                  <td class="text-right">${fmt(it.noa)}</td>
                  <td class="text-right col-ead">${fmt(it.ead_sum)}</td>
                  <td class="text-right col-pd">${pct2(it.pd_percent)}</td>
                  <td class="text-right">${fmt(it.ckpn_collective)}</td>
                </tr>`;
            }).join("")}
          </tbody>
        </table>
      </div>
    `;
  }
</script>
