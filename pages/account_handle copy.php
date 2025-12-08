<!-- ========== MAPPING ACCOUNT (My List) — sticky header + sticky total + sticky nama + responsive ========== -->
<div class="max-w-7xl mx-auto px-4 py-6 h-screen flex flex-col">
  <!-- Header + Filter -->
  <div class="hdr flex flex-wrap items-start gap-2 mb-3">
    <h1 class="text-2xl font-bold flex items-center gap-2">
      <span>📋</span><span>Mapping Account — My List</span>
    </h1>

    <form id="filterMaping" class="ml-auto">
      <div id="filterMA" class="flex items-center gap-2">
        <label for="closing_date_map" class="lbl text-sm text-slate-700">Closing:</label>
        <input type="date" id="closing_date_map" class="inp" required>

        <label for="harian_date_map" class="lbl text-sm text-slate-700">Harian:</label>
        <input type="date" id="harian_date_map" class="inp" required>

        <!-- Tampilkan (ikon) -->
        <button type="submit" id="btnShowMA" class="btn-icon" title="Tampilkan">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="7"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
        </button>

        <!-- Export CSV (ikon) -->
        <button type="button" id="btnExportMa" class="btn-icon bg-emerald-600 hover:bg-emerald-700" title="Export CSV">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="7 10 12 15 17 10"></polyline>
            <line x1="12" y1="15" x2="12" y2="3"></line>
          </svg>
        </button>
      </div>
    </form>
  </div>

  <!-- Loading -->
  <div id="loadingMap" class="hidden flex items-center gap-2 text-sm text-gray-600 mb-2">
    <svg class="animate-spin h-5 w-5 text-blue-600" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span>Memuat data Mapping Account...</span>
  </div>

  <div id="errMa" class="hidden mb-2 p-3 rounded border border-red-200 text-red-700 bg-red-50 text-sm"></div>

  <!-- Scroller tabel -->
  <div id="maScroller" class="flex-1 min-h-0 overflow-hidden rounded border border-gray-200 bg-white">
    <div class="h-full overflow-auto">
      <table id="tabelMa" class="min-w-full text-sm text-left text-gray-700">
        <thead class="uppercase">
          <tr id="maHead1" class="text-xs">
            <th class="px-3 py-2 sticky-ma th-base col-no">No</th>
            <th class="px-3 py-2 sticky-ma th-base col-norek">No Rekening</th>
            <!-- ✅ Sticky kiri untuk Nama Debitur -->
            <th class="px-3 py-2 sticky-ma th-base col-nama freeze-name text-left">Nama Debitur</th>
            <th class="px-3 py-2 sticky-ma th-base col-alamat">Alamat</th>
            <th class="px-3 py-2 sticky-ma th-base text-right col-bd">Baki Debet</th>
            <th class="px-3 py-2 sticky-ma th-base text-center col-jt">JT</th>
            <th class="px-3 py-2 sticky-ma th-base text-right col-tp">T.Pokok</th>
            <th class="px-3 py-2 sticky-ma th-base text-right col-tb">T.Bunga</th>
            <th class="px-3 py-2 sticky-ma th-base text-right col-ckpn">CKPN</th>
            <th class="px-3 py-2 sticky-ma th-base col-bucket">Bucket</th>
            <th class="px-3 py-2 sticky-ma th-base text-right col-plan">Plan CKPN</th>
            <th class="px-3 py-2 sticky-ma th-base text-right col-pem">Pemulihan Pembentukan</th>
            <th class="px-3 py-2 sticky-ma th-base text-right col-bdh">BD Harian</th>
            <th class="px-3 py-2 sticky-ma th-base text-center col-dpd">DPD Harian</th>
            <th class="px-3 py-2 sticky-ma th-base text-right col-ap">Angs. Pokok</th>
            <th class="px-3 py-2 sticky-ma th-base text-right col-ab">Angs. Bunga</th>
            <th class="px-3 py-2 sticky-ma th-base text-center col-tgl">Tgl Trans (dd/mm/yy)</th>
            <th class="px-3 py-2 sticky-ma th-base text-center col-aksi" title="Create Kunjungan">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
              </svg>
            </th>
          </tr>
        </thead>
        <tbody id="tbodyMa"></tbody>
      </table>
    </div>
  </div>
</div>

<!-- ===== Floating Action Button: Create Kunjungan (di luar account handle) TANPA MODAL ===== -->
<button id="fabCreateOutHandle"
        class="fixed bottom-5 right-5 md:bottom-6 md:right-6 w-14 h-14 rounded-full shadow-lg bg-blue-600 hover:bg-blue-700 text-white flex items-center justify-center z-[1000]"
        title="Create Kunjungan (di luar account handle)">
  <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
    <line x1="12" y1="5" x2="12" y2="19"></line>
    <line x1="5" y1="12" x2="19" y2="12"></line>
  </svg>
</button>

<style>
  /* =================== Variables per-breakpoint =================== */
  /* Desktop (>=1024) */
  #maScroller{
    --colNo: 3.5rem;
    --colNorek: 10rem;
    --colNama: 9rem;          /* lebar Nama Debitur */
    --colAlamat: 12rem;       /* sesuai permintaan */
    --colNum: 8.25rem;        /* kolom angka seragam */
    --colJT: 6.25rem;
    --colDPD: 6.25rem;
    --colBucket: 7.25rem;
    --safeBottom: 88px;
  }
  /* Tablet (641–1023) */
  @media (max-width:1023px){
    #maScroller{
      --colNo: 3.2rem;
      --colNorek: 9rem;
      --colNama: 9rem;
      --colAlamat: 12rem;
      --colNum: 7.25rem;
      --colJT: 6rem;
      --colDPD: 6rem;
      --colBucket: 7rem;
      --safeBottom: 88px;
    }
  }
  /* Mobile (<=640) */
  @media (max-width:640px){
    #maScroller{ --safeBottom: 96px; }
  }

  /* =================== Kontrol & tombol =================== */
  .inp{ border:1px solid #cbd5e1; border-radius:.6rem; padding:.5rem .75rem; font-size:14px; background:#fff; }
  .btn-icon{ width:42px; height:42px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center;
             background:#2563eb; color:#fff; box-shadow:0 6px 14px rgba(37,99,235,.25); }
  .btn-icon:hover{ background:#1e40af; }
  .btn-icon[disabled]{ opacity:.6; cursor:not-allowed; }
  .lbl{ font-size:13px; color:#334155; }
  .hdr{ row-gap:.5rem; }

  /* =================== Sticky header =================== */
  #tabelMa thead th.sticky-ma{
    position:sticky; top:0; background:#d9ead3; z-index:88;
  }

  /* =================== Sticky Nama Debitur (kiri) =================== */
  #tabelMa thead th.freeze-name{
    position: sticky;
    left: 0;
    z-index: 95;
    box-shadow: 1px 0 0 rgba(0,0,0,.06);
    text-align:left;
  }
  #tabelMa td.freeze-name{
    position: sticky;
    left: 0;
    z-index: 41;
    background:#fff;
    box-shadow: 1px 0 0 rgba(0,0,0,.06);
    text-align:left;
  }
  #tabelMa td.freeze-name button{ text-align:left; }

  /* =================== TOTAL sticky di bawah header =================== */
  #tabelMa tbody tr.sticky-total td{
    position:sticky; top:var(--ma_headH, 40px); z-index:70;
    background:#eaf2ff; color:#1e40af; border-bottom:1px solid #c7d2fe;
  }
  /* ⬇️ TOTAL + Nama Debitur: sticky top + sticky left + layer & warna benar */
  #tabelMa tbody tr.sticky-total td.freeze-name{
    position: sticky;
    top: var(--ma_headH, 40px);
    left: 0;
    z-index: 92;                    /* di bawah header(95), di atas baris biasa */
    background: #eaf2ff !important; /* samakan dengan total */
    color: #1e40af;
    box-shadow: 1px 0 0 rgba(0,0,0,.06);
  }

  /* Hover row */
  #tabelMa tbody tr.data-row:hover td{ background:#f9fafb; }

  /* Spacer bawah agar baris terakhir tidak ketutup sticky & FAB */
  #tabelMa tbody::after{
    content:""; display:block; height: calc(var(--ma_headH, 40px) + var(--ma_totalH, 36px) + var(--ma_safe, var(--safeBottom, 28px)));
  }
  @supports(padding:max(0px)){ #maScroller{ --ma_safe:max(var(--safeBottom, 28px), env(safe-area-inset-bottom)); } }

  /* =================== Tabel & responsif =================== */
  body{ overflow:hidden; }
  #tabelMa{ table-layout: fixed; font-size:14px; }
  #tabelMa th, #tabelMa td{ padding:.55rem .5rem; }

  /* Lebar kolom */
  #tabelMa .col-no     { width: var(--colNo);     min-width: var(--colNo);     text-align:center; }
  #tabelMa .col-norek  { width: var(--colNorek);  min-width: var(--colNorek);  }
  #tabelMa .col-nama   { width: var(--colNama);   min-width: var(--colNama);   white-space:normal; word-break:break-word; text-align:left; }
  #tabelMa .col-alamat { width: var(--colAlamat); min-width: var(--colAlamat); white-space:normal; word-break:break-word; }
  #tabelMa .col-bucket { width: var(--colBucket); min-width: var(--colBucket); }
  #tabelMa .col-aksi   { width: 3.25rem; min-width: 3.25rem; text-align:center; }

  /* Kolom angka seragam */
  #tabelMa .col-bd, .col-tp, .col-tb, .col-ckpn, .col-plan, .col-pem, .col-bdh, .col-ap, .col-ab{
    width: var(--colNum); min-width: var(--colNum);
  }
  #tabelMa .col-jt   { width: var(--colJT);  min-width: var(--colJT);  text-align:center; }
  #tabelMa .col-dpd  { width: var(--colDPD); min-width: var(--colDPD); text-align:center; }
  #tabelMa .col-tgl  { width: 9rem; min-width: 9rem; text-align:center; }

  /* TBODY gutters biar tidak mepet (desktop/tablet) */
  @media (min-width: 641px){
    #tabelMa tbody tr > td:first-child{ padding-left: 1rem; }
    #tabelMa tbody tr > td:last-child { padding-right: 1rem; }
  }

  /* Tablet */
  @media (max-width:1023px){
    #tabelMa{ font-size:13px; }
    #tabelMa th{ font-size:12px; }
  }

  /* ============ Mobile (<=640) ============ */
  @media (max-width:640px){
    #filterMA{ width:100%; gap:.5rem; }
    .lbl{ display:none; }
    .inp{ flex:1 1 0; min-width:0; font-size:13px; padding:.45rem .6rem; }
    .btn-icon{ width:40px; height:40px; }

    #tabelMa{ font-size:13px; }
    #tabelMa th{ font-size:11px; }
    #tabelMa th, #tabelMa td{ padding:.7rem .6rem; }     /* tinggi baris lebih lega */
    #tabelMa tbody tr.data-row td{ line-height:1.55; }

    /* Sembunyikan No & No Rekening di mobile */
    #tabelMa th.col-no,    #tabelMa td.col-no{ display:none !important; }
    #tabelMa th.col-norek, #tabelMa td.col-norek{ display:none !important; }

    /* Nama & Alamat full multiline */
    #tabelMa td.col-nama, #tabelMa td.col-alamat{ white-space:normal; word-break:break-word; text-align:left; }

    /* Sticky Nama Debitur & TOTAL (nama) tetap kiri 0 */
    #tabelMa td.freeze-name, #tabelMa thead th.freeze-name,
    #tabelMa tbody tr.sticky-total td.freeze-name{ left:0 !important; }
  }
</style>

<script>
(() => {
  // ========= Endpoint tujuan (server-render) =========
  const CREATE_URL  = './kunjungan';
  const HISTORY_URL = './history_kunjungan_user';

  // ========= Helpers =========
  const fmt = n => Number(n||0).toLocaleString('id-ID');
  const isMobile = () => window.innerWidth <= 640;

  const getStoredToken = () =>
    (window.AUTH_TOKEN || localStorage.getItem('dpk_token') || '').trim();

  const getAuthFields = () => {
    const stored = getStoredToken();
    const raw    = stored.replace(/^Bearer\s+/i,'').trim();
    const bearer = raw ? `Bearer ${raw}` : '';
    return { token: raw, authorization: bearer };
  };

  function postNavigate(url, data) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    form.style.display = 'none';

    const payload = { ...(data||{}), ...getAuthFields() };
    Object.entries(payload).forEach(([k,v])=>{
      const input = document.createElement('input');
      input.type  = 'hidden';
      input.name  = k;
      input.value = v ?? '';
      form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
  }

  // ========= State filter =========
  let CURRENT_CLOSING = '';
  let CURRENT_HARIAN  = '';

  // ========= INIT tanggal default + fetch =========
  (async () => {
    try{
      const r = await fetch('./api/date/'); const j = await r.json();
      const d = j?.data;
      if (!d) return;
      document.getElementById('closing_date_map').value = d.last_closing;
      document.getElementById('harian_date_map').value  = d.last_created;
      CURRENT_CLOSING = d.last_closing;
      CURRENT_HARIAN  = d.last_created;
      await fetchMaping(d.last_closing, d.last_created);
      setMaSticky(); sizeMaScroller();
    }catch{}
  })();

  // Resize sync
  window.addEventListener('resize', ()=>{ setMaSticky(); sizeMaScroller(); });

  // Filter submit
  document.getElementById('filterMaping').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const closing_date = document.getElementById('closing_date_map').value;
    const harian_date  = document.getElementById('harian_date_map').value;
    CURRENT_CLOSING = closing_date;
    CURRENT_HARIAN  = harian_date;
    await fetchMaping(closing_date, harian_date);
    setMaSticky(); sizeMaScroller();
  });

  // Loading helpers
  const showLoading = (b)=>{ document.getElementById('loadingMap')?.classList.toggle('hidden', !b); };

  function sizeMaScroller(){
    const wrap = document.getElementById('maScroller');
    const rectTop = wrap.getBoundingClientRect().top;
    wrap.style.height = Math.max(260, window.innerHeight - rectTop - 18) + 'px';
  }
  function setMaSticky(){
    const h = document.getElementById('maHead1')?.offsetHeight || 40;
    const t = document.querySelector('#tabelMa tr.sticky-total')?.offsetHeight || 36;
    const holder = document.getElementById('maScroller');
    holder.style.setProperty('--ma_headH', h + 'px');
    holder.style.setProperty('--ma_totalH', t + 'px');
  }

  // Fetch data
  async function fetchMaping(closing_date, harian_date){
    const tbody   = document.getElementById('tbodyMa');
    const errEl   = document.getElementById('errMa');
    showLoading(true); errEl.classList.add('hidden'); errEl.textContent='';
    tbody.innerHTML = '';

    try{
      const headers = { 'Content-Type':'application/json' };
      const t = getStoredToken(); if (t) headers['Authorization'] = t;

      const res = await fetch('./api/bucket/', {
        method:'POST', headers,
        body: JSON.stringify({ type:'maping_account', closing_date, harian_date })
      });
      const json = await res.json().catch(()=> ({}));
      if (!res.ok) throw new Error(json?.message || ('HTTP '+res.status));

      const rows = Array.isArray(json?.data) ? json.data : [];
      renderTable(rows);
    }catch(err){
      errEl.textContent = err?.message || 'Gagal memuat data Mapping Account';
      errEl.classList.remove('hidden');
      renderTable([]);
    }finally{
      showLoading(false);
      setMaSticky(); sizeMaScroller(); setTimeout(()=>{ setMaSticky(); sizeMaScroller(); }, 60);
    }
  }

  // Render & TOTAL sticky
  function renderTable(rows){
    const tb = document.getElementById('tbodyMa');
    tb.innerHTML = '';

    // Totals
    const T = { bd:0, tp:0, tb:0, ckpn:0, plan:0, pemulihan:0, bd_harian:0, ap:0, ab:0 };

    const dataBuf = [];

    // TOTAL sticky row (placeholder)
    dataBuf.push(`
      <tr class="sticky-total font-semibold text-sm text-blue-800">
        <td class="px-3 py-2 col-no"></td>
        <td class="px-3 py-2 col-norek"></td>
        <td class="px-3 py-2 col-nama freeze-name">TOTAL</td>
        <td class="px-3 py-2 col-alamat"></td>
        <td class="px-3 py-2 text-right col-bd"    data-ttl="bd"></td>
        <td class="px-3 py-2 text-center col-jt"></td>
        <td class="px-3 py-2 text-right col-tp"    data-ttl="tp"></td>
        <td class="px-3 py-2 text-right col-tb"    data-ttl="tb"></td>
        <td class="px-3 py-2 text-right col-ckpn"  data-ttl="ckpn"></td>
        <td class="px-3 py-2 col-bucket"></td>
        <td class="px-3 py-2 text-right col-plan"  data-ttl="plan"></td>
        <td class="px-3 py-2 text-right col-pem"   data-ttl="pemulihan"></td>
        <td class="px-3 py-2 text-right col-bdh"   data-ttl="bd_harian"></td>
        <td class="px-3 py-2 text-center col-dpd"></td>
        <td class="px-3 py-2 text-right col-ap"    data-ttl="ap"></td>
        <td class="px-3 py-2 text-right col-ab"    data-ttl="ab"></td>
        <td class="px-3 py-2 text-center col-tgl"></td>
        <td class="px-3 py-2 text-center col-aksi"></td>
      </tr>
    `);

    // Data rows
    rows.forEach((r, i)=>{
      const no_rek  = r.no_rekening || '';
      const nama    = r.nama_debitur || '';
      const alamatF = (r.alamat || '');
      const alamatM = isMobile() ? alamatF : alamatF;

      T.bd += +r.baki_debet||0;
      T.tp += +r.tunggakan_pokok||0;
      T.tb += +r.tunggakan_bunga||0;
      T.ckpn += +r.ckpn||0;
      T.plan += +r.plan_ckpn||0;
      T.pemulihan += +r.pemulihan_pembentukan||0;
      T.bd_harian += +r.baki_debet_harian||0;
      T.ap += +r.angsuran_pokok||0;
      T.ab += +r.angsuran_bunga||0;

      dataBuf.push(`
        <tr class="data-row border-b">
          <td class="px-3 py-2 text-center col-no">${i+1}</td>
          <td class="px-3 py-2 font-mono col-norek">${escapeHtml(no_rek)}</td>

          <!-- ✅ Nama debitur sticky kiri -->
          <td class="px-3 py-2 col-nama freeze-name">
            <button class="text-blue-700 hover:underline" title="Lihat history kunjungan"
              onclick="goHistory('${escapeHtml(no_rek)}')">${escapeHtml(nama)}</button>
          </td>

          <td class="px-3 py-2 col-alamat">${escapeHtml(alamatM)}</td>
          <td class="px-3 py-2 text-right col-bd">${fmt(r.baki_debet)}</td>
          <td class="px-3 py-2 text-center col-jt">${r.tgl_realisasi||''}</td>
          <td class="px-3 py-2 text-right col-tp">${fmt(r.tunggakan_pokok)}</td>
          <td class="px-3 py-2 text-right col-tb">${fmt(r.tunggakan_bunga)}</td>
          <td class="px-3 py-2 text-right col-ckpn">${fmt(r.ckpn)}</td>
          <td class="px-3 py-2 col-bucket">${r.bucket||''}</td>
          <td class="px-3 py-2 text-right col-plan">${fmt(r.plan_ckpn)}</td>
          <td class="px-3 py-2 text-right col-pem">${fmt(r.pemulihan_pembentukan)}</td>
          <td class="px-3 py-2 text-right col-bdh">${fmt(r.baki_debet_harian)}</td>
          <td class="px-3 py-2 text-center col-dpd">${r.hari_menunggak_harian ?? ''}</td>
          <td class="px-3 py-2 text-right col-ap">${fmt(r.angsuran_pokok)}</td>
          <td class="px-3 py-2 text-right col-ab">${fmt(r.angsuran_bunga)}</td>
          <td class="px-3 py-2 text-center col-tgl">${r.tgl_trans || ''}</td>

          <!-- Create kunjungan -->
          <td class="px-3 py-2 text-center col-aksi">
            <button class="inline-flex items-center justify-center w-8 h-8 rounded bg-blue-600 hover:bg-blue-700 text-white"
              title="Create Kunjungan" onclick="goCreate('${escapeHtml(no_rek)}')">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
              </svg>
            </button>
          </td>
        </tr>
      `);
    });

    tb.innerHTML = dataBuf.join('');

    // Isi sel TOTAL
    const ttl = {
      bd: fmt(T.bd), tp: fmt(T.tp), tb: fmt(T.tb), ckpn: fmt(T.ckpn),
      plan: fmt(T.plan), pemulihan: fmt(T.pemulihan),
      bd_harian: fmt(T.bd_harian), ap: fmt(T.ap), ab: fmt(T.ab),
    };
    tb.querySelectorAll('[data-ttl]').forEach(el=>{
      const k = el.getAttribute('data-ttl'); el.textContent = ttl[k] ?? '';
    });
  }

  function escapeHtml(s){
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  // ====== Export CSV ======
  document.getElementById('btnExportMa').addEventListener('click', ()=>{
    const tbl = document.getElementById('tabelMa');
    const headers = Array.from(tbl.tHead.rows[0].cells).map(th=>th.textContent.trim());
    const rows = Array.from(document.querySelectorAll('#tbodyMa tr.data-row'))
      .map(tr => Array.from(tr.cells).map(td => td.textContent.trim()));
    const csv = [headers, ...rows]
      .map(r => r.map(v => `"${String(v).replace(/"/g,'""')}"`).join(','))
      .join('\n');
    const blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `mapping_account_${new Date().toISOString().slice(0,10)}.csv`;
    a.click();
  });

  // ====== Navigasi POST (bawa token dalam body) ======
  function getDatesForPost(){
    const closing = document.getElementById('closing_date_map')?.value || '';
    const harian  = document.getElementById('harian_date_map')?.value  || '';
    return { closing, harian };
  }

  window.goCreate = (no_rek)=>{
    const { closing, harian } = getDatesForPost();
    postNavigate(CREATE_URL, {
      no_rekening : no_rek,
      closing_date: closing,
      harian_date : harian
    });
  };
  window.goHistory = (no_rek)=>{
    const { closing, harian } = getDatesForPost();
    postNavigate(HISTORY_URL, {
      no_rekening : no_rek,
      closing_date: closing,
      harian_date : harian
    });
  };

  // ====== FAB: langsung POST tanpa modal (out_handle) ======
  document.getElementById('fabCreateOutHandle')?.addEventListener('click', ()=>{
    const { closing, harian } = getDatesForPost();
    postNavigate(CREATE_URL, {
      closing_date: closing,
      harian_date : harian,
      out_handle  : '1'
    });
  });

  // hitung tinggi scroller & sticky
  function afterRenderSync(){
    setMaSticky(); sizeMaScroller();
    setTimeout(()=>{ setMaSticky(); sizeMaScroller(); }, 60);
  }
  window.addEventListener('load', afterRenderSync);
})();
</script>
