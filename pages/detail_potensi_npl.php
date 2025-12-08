<!-- detail_potensi_npl.php — 1 file, role-aware via NavAuth.getUser(), mobile rapi -->
<div class="max-w-7xl mx-auto px-4 py-6 h-screen flex flex-col">
  <!-- Header (tanpa Back) -->
  <div class="flex items-center justify-between mb-2">
    <h1 class="text-2xl font-bold flex items-center gap-2">
      <span>⚠️</span><span>Detail Potensi NPL</span>
    </h1>
  </div>

  <!-- BAR: Summary (kiri) + Filter (kanan) -->
  <div class="toolbar-compact mb-3">
    <!-- Ringkas total (Left) -->
    <div id="summaryTotals" class="bar-left text-sm text-gray-700">
      <span class="pill pill-noa">NOA: <b id="sumNoa">0</b></span>
      <span class="pill pill-bdm1">BD M-1: <b id="sumBdc">0</b></span>
      <span class="pill pill-bdact">BD Act: <b id="sumBdh">0</b></span>
    </div>

    <!-- Filter (Right) -->
    <form id="formFilterDetail" class="bar-right">
      <div class="fld">
        <label for="kode_kantor" class="lbl">Kantor:</label>
        <select id="kode_kantor" class="f-inp min-w-[180px]">
          <option value="">Memuat...</option>
        </select>
      </div>

      <div class="fld hidden">
        <label class="lbl" for="closing_date_detail">Closing:</label>
        <input type="date" id="closing_date_detail" class="f-inp" required>
      </div>

      <div class="fld hidden">
        <label class="lbl" for="harian_date_detail">Harian:</label>
        <input type="date" id="harian_date_detail" class="f-inp" required>
      </div>

      <div class="fld">
        <label class="lbl" for="filterKolek">Kolek Act:</label>
        <select id="filterKolek" class="f-inp">
          <option value="">Semua</option>
          <option value="Lunas">Lunas</option>
          <option value="L">L</option>
          <option value="DP">DP</option>
          <option value="KL">KL</option>
          <option value="D">D</option>
          <option value="M">M</option>
        </select>
      </div>
      <button type="button" id="btnExport" class="f-btn" title="Export Excel">⬇️</button>
    </form>
  </div>

  <!-- Loading -->
  <div id="loadingDetail" class="hidden flex items-center gap-2 text-sm text-gray-600 mb-2">
    <svg class="animate-spin h-5 w-5 text-blue-600" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span>Memuat detail debitur...</span>
  </div>

  <!-- SCROLLER -->
  <div id="dpScroller" class="flex-1 min-h-0 overflow-hidden rounded border border-gray-200 bg-white">
    <div class="h-full overflow-auto">
      <table id="tabelDetailNPL" class="min-w-full text-sm text-left text-gray-700">
        <thead class="uppercase">
          <tr id="dpHead1" class="text-xs">
            <th class="px-3 py-2 sticky-dp freeze-1 col-norek">NO REKENING</th>
            <th class="px-3 py-2 sticky-dp freeze-2 col-debitur">NAMA DEBITUR</th>
            <th class="px-3 py-2 text-right sticky-dp sort col-kolek" data-sort="kolek_harian">Kol</th>
            <th class="px-3 py-2 text-right sticky-dp sort col-amt" data-sort="baki_debet_harian">OSC</th>
            <th class="px-3 py-2 text-right sticky-dp col-num">DPD</th>
            <th class="px-3 py-2 text-right sticky-dp col-num">HM P</th>
            <th class="px-3 py-2 text-right sticky-dp col-num">HM B</th>
            <th class="px-3 py-2 sticky-dp col-date">Jatuh Tempo</th>
            <th class="px-3 py-2 sticky-dp col-dd">Tgl JT</th>
            <th class="px-3 py-2 text-right sticky-dp col-amt">ANGS POKOK</th>
            <th class="px-3 py-2 text-right sticky-dp col-amt">ANGS BUNGA</th>
            <th class="px-3 py-2 sticky-dp col-date">Tgl ANGS</th>
          </tr>
        </thead>
        <tbody id="dpTotalRow"></tbody>
        <tbody id="dpBody"></tbody>
      </table>
    </div>
  </div>
</div>

<style>
  /* ========== Toolbar compact ========== */
  .toolbar-compact{ display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:nowrap; }
  .bar-left{ display:flex; align-items:center; gap:.5rem; flex:1 1 auto; min-width:0; flex-wrap:wrap; }
  .pill{ display:inline-block; background:#f1f5f9; border:1px solid #e2e8f0; border-radius:.6rem; padding:.35rem .6rem; }
  .bar-right{ display:flex; align-items:end; gap:.6rem; margin-left:auto; flex:0 0 auto; }
  .bar-right .fld{ display:flex; align-items:center; gap:.4rem; }
  .lbl{ font-size:12.5px; color:#334155; white-space:nowrap; }

  /* Mobile: stack rapi */
  @media (max-width:640px){
    .toolbar-compact{ flex-direction:column; align-items:stretch; gap:.6rem; }
    .bar-left{ width:100%; gap:.35rem; }
    .bar-right{ display:grid; grid-template-columns:1fr 1fr auto; gap:.5rem .6rem; align-items:center; width:100%; }
    .bar-right .fld{ min-width:0; }
    .lbl{ font-size:11.5px; }
    /* Sembunyikan NOA & BD M-1 di mobile, tampilkan hanya BD Act */
    .pill-noa, .pill-bdm1{ display:none !important; }
  }

  /* Controls */
  .f-inp{ border:1px solid #cbd5e1; border-radius:.6rem; padding:.45rem .7rem; font-size:14px; background:#fff; height:38px; }
  .f-btn{ width:40px; height:40px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center; background:#2563eb; color:#fff; box-shadow:0 6px 14px rgba(37,99,235,.25); }
  .f-btn:hover{ background:#1e40af; }

  *{ box-sizing:border-box; }
  body{ overflow:hidden; }

  /* ====== Tabel ====== */
  #dpScroller{ --dp_colNorek: 7rem; --dp_colDeb: 10rem; --dp_head: 40px; --dp_totalH:36px; --dp_safe:36px; --dp_gapX: 10px; }
  @supports(padding:max(0px)){ #dpScroller{ --dp_safe:max(36px, env(safe-area-inset-bottom)); } }

  #tabelDetailNPL{ font-size: 13.5px; table-layout: fixed; border-collapse:separate; border-spacing:0; }
  #tabelDetailNPL thead th{ font-size: 12px; background:#d9ead3 !important; position:sticky; top:0; z-index:88; }
  #tabelDetailNPL thead th.freeze-1{ left:0; z-index:90; }
  #tabelDetailNPL thead th.freeze-2{ left:var(--dp_colNorek); z-index:89; }
  #tabelDetailNPL .col-norek{ width:var(--dp_colNorek); min-width:var(--dp_colNorek); }
  #tabelDetailNPL .col-debitur{ width:var(--dp_colDeb); min-width:var(--dp_colDeb); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  #tabelDetailNPL .col-kolek{ width:5rem; min-width:4.8rem; }
  #tabelDetailNPL .col-amt{ width:8.5rem; min-width:8rem; }
  #tabelDetailNPL .col-num{ width:4.2rem; min-width:4rem; }
  #tabelDetailNPL .col-date{ width:9ch; min-width:8ch; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-variant-numeric: tabular-nums; }
  #tabelDetailNPL .col-dd{ width:3.2ch; min-width:3ch; text-align:center; font-variant-numeric: tabular-nums; }
  #tabelDetailNPL th, #tabelDetailNPL td{ border-bottom:1px solid #eef2f7; }
  #tabelDetailNPL td{ padding-left: calc(.75rem - var(--dp_gapX)/2); padding-right: calc(.75rem - var(--dp_gapX)/2); }
  #tabelDetailNPL .freeze-1{ position:sticky; left:0; z-index:41; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06); }
  #tabelDetailNPL .freeze-2{ position:sticky; left:var(--dp_colNorek); z-index:40; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06); }
  #tabelDetailNPL tbody tr.sticky-total td{ position:sticky; top:var(--dp_head); background:#eaf2ff; color:#1e40af; z-index:70; border-bottom:1px solid #c7d2fe; }
  #tabelDetailNPL tbody tr.sticky-total td.freeze-1{ z-index:91; }
  #tabelDetailNPL tbody tr.sticky-total td.freeze-2{ z-index:90; }
  #tabelDetailNPL tbody tr:hover td{ background:#f9fafb; }
  #dpBody::after{ content:""; display:block; height: calc(var(--dp_head) + var(--dp_totalH) + var(--dp_safe)); }

  /* ===== Mobile tweaks ===== */
  @media (max-width:640px){
    #tabelDetailNPL{ font-size:11.5px; }
    #tabelDetailNPL thead th{ font-size:10.5px; }
    #tabelDetailNPL th, #tabelDetailNPL td{ padding:.28rem .36rem; }

    /* 1) Sembunyikan kolom NO REKENING */
    #tabelDetailNPL th.col-norek, #tabelDetailNPL td.col-norek{ display:none !important; }

    /* 2) NAMA DEBITUR jadi freeze paling kiri, lebar 8rem */
    #tabelDetailNPL thead th.col-debitur{ left:0 !important; z-index:90; width:8rem !important; min-width:8rem !important; }
    #tabelDetailNPL td.col-debitur{
      position:sticky; left:0 !important; z-index:41; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06);
      width:8rem !important; min-width:8rem !important; max-width:8rem !important; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    }

    /* 3) Kompres kolom angka */
    #tabelDetailNPL .col-amt{ width:7.4rem; min-width:7rem; }
    #tabelDetailNPL .col-num{ width:3.8rem; min-width:3.6rem; }
    #tabelDetailNPL .col-date{ width:8ch; min-width:7ch; }
  }

  /* Sembunyikan field Closing & Harian via CSS (fallback selain JS) */
  #formFilterDetail > .fld:has(#closing_date_detail),
  #formFilterDetail > .fld:has(#harian_date_detail){ display:none !important; }

  /* ==== PRINT: hanya tabel (Legal Landscape) ==== */
  @page { size: legal landscape; margin: 10mm; }
  @media print {
    :root { color-scheme: light; }
    *{ -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    body *{ visibility: hidden !important; }
    #tabelDetailNPL, #tabelDetailNPL *{ visibility: visible !important; }
    #tabelDetailNPL{ position:absolute !important; left:0; top:0; width:100% !important; }
    #dpScroller{ overflow:visible !important; height:auto !important; border:none !important; box-shadow:none !important; }
    #tabelDetailNPL thead{ display: table-header-group; }
    #tabelDetailNPL tfoot{ display: table-footer-group; }
    #tabelDetailNPL thead th{ position:static !important; background:#e5e7eb !important; box-shadow:none !important; }
    #tabelDetailNPL .freeze-1, #tabelDetailNPL .freeze-2, #tabelDetailNPL tbody tr.sticky-total td{ position:static !important; left:auto !important; z-index:auto !important; box-shadow:none !important; }
    #dpBody::after{ display:none !important; }
    #tabelDetailNPL .col-norek, #tabelDetailNPL .col-debitur, #tabelDetailNPL .col-kolek, #tabelDetailNPL .col-amt, #tabelDetailNPL .col-num, #tabelDetailNPL .col-date, #tabelDetailNPL .col-dd{
      width:auto !important; min-width:0 !important; max-width:none !important; white-space:nowrap;
    }
    #tabelDetailNPL{ font-size:11px; }
  }

  /* ====== TOTAL row mepet header ====== */
  #tabelDetailNPL thead th{ border-bottom: 0 !important; }
  #tabelDetailNPL tbody tr.sticky-total td{ top: calc(var(--dp_head) - 1px) !important; }
  #dpBody::after{ height: calc(var(--dp_head) + var(--dp_totalH) + var(--dp_safe) - 1px) !important; }
</style>

<script>
  // ===== Helpers =====
  const nfID = new Intl.NumberFormat('id-ID');
  const rp   = n => nfID.format(Number(n||0));
  const isMobile = () => window.matchMedia('(max-width:640px)').matches;

  // Hide field "Closing" & "Harian"
  (() => {
    const cd = document.getElementById('closing_date_detail');
    const hd = document.getElementById('harian_date_detail');
    [cd, hd].forEach(el => { if (!el) return; el.required = false; const wrap = el.closest('.fld'); if (wrap) wrap.style.display = 'none'; });
  })();

  // text utils
  function midEllipsis(str, maxChars){ const s = String(str ?? ''); if (s.length <= maxChars) return s; const keep=maxChars-1, f=Math.ceil(keep/2), b=Math.floor(keep/2); return s.slice(0,f)+'…'+s.slice(-b); }
  const short   = (s,n=18)=>{ const t=String(s??''); return t.length<=n?t:t.slice(0,n).trimEnd()+'…'; };
  const safeNum = (v)=>(v===0||v)?v:'-';
  const fmtTanggal=(tgl)=>{ if(!tgl) return '-'; const d=new Date(tgl); return isNaN(d)?tgl:`${String(d.getDate()).padStart(2,'0')}-${String(d.getMonth()+1).padStart(2,'0')}-${d.getFullYear()}`; };
  const fmtHariSaja=(tgl)=>{ if(!tgl) return '-'; const d=new Date(tgl); if(isNaN(d)) return tgl; const dd=String(d.getDate()).padStart(2,'0'); const full=`${dd}-${String(d.getMonth()+1).padStart(2,'0')}-${d.getFullYear()}`; return `<span title="${full}">${dd}</span>`; };

  // ===== State =====
  let allRows = [];
  let sortKey = 'baki_debet_harian';
  let sortAsc = false;

  // Sticky + scroller
  function setDPSticky(){
    const h   = document.getElementById('dpHead1')?.offsetHeight || 40;
    const tot = document.querySelector('#tabelDetailNPL tr.sticky-total')?.offsetHeight || 36;
    const holder = document.getElementById('dpScroller');
    holder.style.setProperty('--dp_head', h + 'px');
    holder.style.setProperty('--dp_totalH', tot + 'px');
  }
  function sizeDPScroller(){
    const wrap = document.getElementById('dpScroller'); if(!wrap) return;
    const rectTop = wrap.getBoundingClientRect().top; wrap.style.height = Math.max(260, window.innerHeight - rectTop - 10) + 'px';
  }
  window.addEventListener('resize', ()=>{ setDPSticky(); sizeDPScroller(); });

  // Prefill params dari localStorage
  const saved = localStorage.getItem('potensi_npl_params');
  let baseParams = saved ? JSON.parse(saved) : {};
  if (baseParams.closing_date) document.getElementById('closing_date_detail').value = baseParams.closing_date;
  if (baseParams.harian_date)  document.getElementById('harian_date_detail').value  = baseParams.harian_date;

  // Ambil user login dari NavAuth (tanpa token untuk request kita)
  const USER = (window.getUser && window.getUser()) || null;
  const USER_CODE = USER?.kode ? String(USER.kode).padStart(3,'0') : '000';
  const IS_HQ = USER_CODE === '000';

  // Load kode kantor (fetch biasa, filter sesuai role)
  const kodeSelect = document.getElementById('kode_kantor');
  async function loadKodeKantorOptions(selectedCode) {
    try {
      const res = await fetch('./api/kode/', {
        method: 'POST', headers:{ 'Content-Type':'application/json' },
        body: JSON.stringify({ type: 'kode_kantor' })
      });
      const json = await res.json();
      const list = Array.isArray(json.data) ? json.data : [];
      kodeSelect.innerHTML = '<option value="">-- Pilih Cabang --</option>';

      const filtered = list
        .filter(it => it.kode_kantor && it.kode_kantor !== '000')
        .filter(it => IS_HQ ? true : String(it.kode_kantor).padStart(3,'0') === USER_CODE)
        .sort((a,b) => String(a.kode_kantor).localeCompare(String(b.kode_kantor)));

      filtered.forEach(it => {
        const code = String(it.kode_kantor).padStart(3,'0');
        const name = it.nama_kantor || it.nama_cabang || '';
        const opt  = document.createElement('option');
        opt.value = code; opt.textContent = `${code} — ${name}`;
        kodeSelect.appendChild(opt);
      });

      if (IS_HQ) {
        if (selectedCode) kodeSelect.value = String(selectedCode).padStart(3,'0');
        if (!kodeSelect.value && kodeSelect.options.length > 1) kodeSelect.selectedIndex = 1;
        kodeSelect.disabled = false;
      } else {
        kodeSelect.value = USER_CODE;   // cabang: paksa kode login
        kodeSelect.disabled = true;     // kunci dropdown
      }

      baseParams.kode_kantor = kodeSelect.value;
      localStorage.setItem('potensi_npl_params', JSON.stringify(baseParams));

      if (document.getElementById('closing_date_detail').value &&
          document.getElementById('harian_date_detail').value) fetchDetail();
    } catch {
      kodeSelect.innerHTML = '<option value="">(Gagal memuat cabang)</option>';
    }
  }
  loadKodeKantorOptions(baseParams?.kode_kantor);

  // Submit/Change handlers
  document.getElementById('formFilterDetail').addEventListener('submit', function(e){
    e.preventDefault();
    baseParams = {
      kode_kantor: kodeSelect.value,
      closing_date: document.getElementById('closing_date_detail').value,
      harian_date:  document.getElementById('harian_date_detail').value
    };
    localStorage.setItem('potensi_npl_params', JSON.stringify(baseParams));
    fetchDetail();
  });
  kodeSelect.addEventListener('change', () => {
    if (kodeSelect.disabled) return; // user cabang: abaikan
    baseParams.kode_kantor = kodeSelect.value;
    localStorage.setItem('potensi_npl_params', JSON.stringify(baseParams));
    if (document.getElementById('closing_date_detail').value &&
        document.getElementById('harian_date_detail').value) fetchDetail();
  });
  document.getElementById('filterKolek').addEventListener('change', () => {
    renderDetail(sortedViewFiltered());
  });

  // INIT tanggal dari API date (tanpa token)
  (async () => {
    const d = await getLastHarianData();
    if (d && !document.getElementById('closing_date_detail').value) {
      closing_date_detail.value = d.last_closing;
      harian_date_detail.value  = d.last_created;
    }
  })();
  async function getLastHarianData() {
    try {
      const res = await fetch('./api/date/', { method: 'GET' });
      const json = await res.json();
      return json.data || null;
    } catch { return null; }
  }

  // Fetch detail (tanpa token)
  function fetchDetail() {
    const { kode_kantor, closing_date, harian_date } = baseParams || {};
    if (!kode_kantor || !closing_date || !harian_date) return;
    loadingDetail.classList.remove('hidden');

    fetch("./api/npl/", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ type: "Debitur Potensi NPL", kode_kantor, closing_date, harian_date })
    })
    .then(r => r.json())
    .then(res => {
      allRows = Array.isArray(res.data) ? res.data : [];
      sortKey = 'baki_debet_harian'; sortAsc = false;
      renderDetail(sortedViewFiltered());
    })
    .catch(() => {
      dpBody.innerHTML = `<tr><td colspan="12" class="px-4 py-3 text-red-600">Gagal memuat data.</td></tr>`;
      dpTotalRow.innerHTML = '';
      setTotals(0,0,0);
    })
    .finally(() => loadingDetail.classList.add('hidden'));
  }

  // Sorting
  document.querySelector('#tabelDetailNPL thead').addEventListener('click', e=>{
    const k = e.target?.dataset?.sort; if(!k) return;
    if(sortKey === k) sortAsc = !sortAsc; else { sortKey = k; sortAsc = false; }
    renderDetail(sortedViewFiltered());
  });
  function sortedView(rows = allRows) {
    const key = sortKey, asc = sortAsc;
    return [...rows].sort((a, b) => {
      if (key === 'kolek_harian'){
        const A = String(a.kolek_harian || ''), B = String(b.kolek_harian || '');
        return asc ? A.localeCompare(B) : B.localeCompare(A);
      }
      const A = Number(a[key]) || 0, B = Number(b[key]) || 0;
      return asc ? A - B : B - A;
    });
  }
  function sortedViewFiltered(){
    const opt = document.getElementById('filterKolek').value;
    const rows = !opt ? allRows : allRows.filter(d => (d.kolek_harian || 'Lunas') === opt);
    return sortedView(rows);
  }

  // Render
  function renderDetail(rows) {
    const tBDH = rows.reduce((a,r)=> a + Number(r.baki_debet_harian||0), 0);
    const tPok = rows.reduce((a,r)=> a + Number(r.angsuran_pokok||0), 0);
    const tBng = rows.reduce((a,r)=> a + Number(r.angsuran_bunga||0), 0);

    dpTotalRow.innerHTML = `
      <tr class="sticky-total font-semibold text-sm">
        <td class="px-3 py-2 freeze-1 col-norek"></td>
        <td class="px-3 py-2 freeze-2 col-debitur"></td>
        <td class="px-3 py-2 text-right col-kolek"></td>
        <td class="px-3 py-2 text-right col-amt" title="${rp(tBDH)}">${rp(tBDH)}</td>
        <td class="px-3 py-2 text-right col-num"></td>
        <td class="px-3 py-2 text-right col-num"></td>
        <td class="px-3 py-2 text-right col-num"></td>
        <td class="px-3 py-2 col-date"></td>
        <td class="px-3 py-2 col-dd"></td>
        <td class="px-3 py-2 text-right col-amt" title="${rp(tPok)}">${rp(tPok)}</td>
        <td class="px-3 py-2 text-right col-amt" title="${rp(tBng)}">${rp(tBng)}</td>
        <td class="px-3 py-2 col-date"></td>
      </tr>`;

    const maxNorek   = isMobile() ? 10 : 12;
    const maxDebitur = isMobile() ? 14 : 18;

    let html = '';
    for (const d of rows) {
      const norekFull = d.no_rekening || '-';
      const debFull   = d.nama_nasabah || '-';
      const bdh       = Number(d.baki_debet_harian || 0);
      const jtClose   = d.jt_closing ? fmtTanggal(d.jt_closing) : '-';
      const tglReal   = fmtHariSaja(d.tgl_realisasi);
      const tglBayar  = d.tgl_trans_terakhir ? fmtTanggal(d.tgl_trans_terakhir) : '-';

      html += `
        <tr class="border-b hover:bg-gray-50">
          <td class="px-3 py-2 freeze-1 col-norek"   title="${norekFull}">${midEllipsis(norekFull, maxNorek)}</td>
          <td class="px-3 py-2 freeze-2 col-debitur" title="${debFull}">${short(debFull, maxDebitur)}</td>
          <td class="px-3 py-2 text-right col-kolek">${d.kolek_harian || 'Lunas'}</td>
          <td class="px-3 py-2 text-right col-amt">${rp(bdh)}</td>
          <td class="px-3 py-2 text-right col-num">${safeNum(d.hm_harian)}</td>
          <td class="px-3 py-2 text-right col-num">${safeNum(d.hmp_harian)}</td>
          <td class="px-3 py-2 text-right col-num">${safeNum(d.hmb_harian)}</td>
          <td class="px-3 py-2 col-date">${jtClose}</td>
          <td class="px-3 py-2 col-dd">${tglReal}</td>
          <td class="px-3 py-2 text-right col-amt">${d.angsuran_pokok ? rp(d.angsuran_pokok) : '-'}</td>
          <td class="px-3 py-2 text-right col-amt">${d.angsuran_bunga ? rp(d.angsuran_bunga) : '-'}</td>
          <td class="px-3 py-2 col-date">${tglBayar}</td>
        </tr>`;
    }
    dpBody.innerHTML = html;

    updateSummary(rows);
    setDPSticky(); sizeDPScroller();
    setTimeout(()=>{ setDPSticky(); sizeDPScroller(); }, 50);
  }

  function updateSummary(rows) {
    const noa = rows.length;
    const totalBdc = rows.reduce((s, r) => s + Number(r.baki_debet_closing || 0), 0);
    const totalBdh = rows.reduce((s, r) => s + Number(r.baki_debet_harian || 0), 0);
    setTotals(noa, totalBdc, totalBdh);
  }
  function setTotals(noa, bdc, bdh) {
    document.getElementById('sumNoa').textContent = nfID.format(noa);
    document.getElementById('sumBdc').textContent = nfID.format(bdc);
    document.getElementById('sumBdh').textContent = nfID.format(bdh);
  }
  
  // ===== EXPORT: Excel via SheetJS (fallback CSV) =====
  (function setupExportExcel(){
    const btn = document.getElementById('btnExport');
    if(!btn) return;
    btn.addEventListener('click', exportDetailToExcel);

    function exportDetailToExcel(){
      const rows = typeof sortedViewFiltered === 'function' ? sortedViewFiltered() : (window.allRows || []);
      if (!rows || !rows.length){ alert('Tidak ada data untuk diexport.'); return; }

      const HEADERS = ['NO REKENING','NAMA DEBITUR','Kol','OSC','DPD','HM P','HM B','Jatuh Tempo','Tgl JT (DD)','ANGS POKOK','ANGS BUNGA','Tgl ANGS'];
      const toDate = (v) => v ? new Date(v) : '';
      const toDay  = (v) => v ? (new Date(v)).getDate() : '';
      const sum = (key) => rows.reduce((s, r)=> s + Number(r[key] || 0), 0);

      const data = rows.map(d => ({
        'NO REKENING' : d.no_rekening || '-',
        'NAMA DEBITUR': d.nama_nasabah || '-',
        'Kol'         : d.kolek_harian || 'Lunas',
        'OSC'         : Number(d.baki_debet_harian || 0),
        'DPD'         : (d.hm_harian === 0 || d.hm_harian) ? Number(d.hm_harian) : '',
        'HM P'        : (d.hmp_harian === 0 || d.hmp_harian) ? Number(d.hmp_harian) : '',
        'HM B'        : (d.hmb_harian === 0 || d.hmb_harian) ? Number(d.hmb_harian) : '',
        'Jatuh Tempo' : toDate(d.jt_closing),
        'Tgl JT (DD)' : toDay(d.tgl_realisasi),
        'ANGS POKOK'  : Number(d.angsuran_pokok || 0),
        'ANGS BUNGA'  : Number(d.angsuran_bunga || 0),
        'Tgl ANGS'    : toDate(d.tgl_trans_terakhir)
      }));
      data.unshift({
        'NO REKENING':'TOTAL','NAMA DEBITUR':'','Kol':'',
        'OSC':sum('baki_debet_harian'),'DPD':'','HM P':'','HM B':'',
        'Jatuh Tempo':'','Tgl JT (DD)':'','ANGS POKOK':sum('angsuran_pokok'),
        'ANGS BUNGA':sum('angsuran_bunga'),'Tgl ANGS':''
      });

      const filename = `detail_potensi_npl_${(window.baseParams?.kode_kantor||'ALL')}_${(window.baseParams?.harian_date||'')}`;

      if (window.XLSX){
        try {
          const ws = XLSX.utils.json_to_sheet(data, { header: HEADERS, skipHeader:false });
          ws['!cols'] = [{wch:14},{wch:22},{wch:6},{wch:14},{wch:6},{wch:6},{wch:6},{wch:12},{wch:10},{wch:14},{wch:14},{wch:12}];
          const wb = XLSX.utils.book_new(); XLSX.utils.book_append_sheet(wb, ws, 'Detail NPL');
          XLSX.writeFile(wb, `${filename}.xlsx`, { cellDates:true });
          return;
        } catch (e) { console.warn('Gagal export XLSX, fallback CSV.', e); }
      }
      const csv = toCSV(data, HEADERS);
      downloadBlob(new Blob(['\ufeff' + csv], {type:'text/csv;charset=utf-8;'}), `${filename}.csv`);
    }

    function toCSV(rows, headers){
      const esc = (v) => { if (v == null) return ''; let s = v instanceof Date ? v.toISOString().slice(0,10) : String(v); if (/[",\n,;]/.test(s)) s = '"' + s.replace(/"/g,'""') + '"'; return s; };
      const lines = [headers.join(',')]; rows.forEach(r => lines.push(headers.map(h => esc(r[h])).join(','))); return lines.join('\r\n');
    }
    function downloadBlob(blob, filename){ const url = URL.createObjectURL(blob); const a = document.createElement('a'); a.href = url; a.download = filename; document.body.appendChild(a); a.click(); setTimeout(()=>{ URL.revokeObjectURL(url); a.remove(); }, 0); }
  })();

  // PRINT hotkey
  (function setupPrintHotkey(){
    function before(){ document.body.classList.add('print-mode'); }
    function after(){ document.body.classList.remove('print-mode'); }
    window.addEventListener('beforeprint', before);
    window.addEventListener('afterprint',  after);
    document.addEventListener('keydown', (e) => {
      const isCtrlP = (e.ctrlKey || e.metaKey) && String(e.key).toLowerCase() === 'p';
      if (!isCtrlP) return;
      e.preventDefault(); before(); setTimeout(() => window.print(), 30);
    });
  })();

  // Observer header → --dp_head
  (() => {
    const headRow = document.getElementById('dpHead1');
    const holder  = document.getElementById('dpScroller');
    if (!headRow || !holder) return;
    const apply = () => { const h = headRow.offsetHeight || 40; holder.style.setProperty('--dp_head', h + 'px'); };
    window.addEventListener('load', apply);
    new ResizeObserver(apply).observe(headRow);
  })();
</script>

<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
