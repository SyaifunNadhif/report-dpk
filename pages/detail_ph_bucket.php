<!-- detail_ph_bucket — Mobile: no_rekening hidden, Nama Debitur sticky kiri 7.5rem -->
<div class="max-w-7xl mx-auto px-4 py-6 h-screen flex flex-col">
  <!-- Header + Back -->
  <div class="flex items-center justify-between mb-2">
    <h1 class="text-2xl font-bold flex items-center gap-2">
      <span>📗</span><span>Detail PH Bucket</span>
    </h1>
    <a href="bucket_saldo_ph" class="text-emerald-700 hover:underline">← Back</a>
  </div>

  <!-- Toolbar -->
  <div class="toolbar-compact mb-3">
    <div class="bar-left text-sm text-gray-700">
      <label class="pill">
        <span>Cabang:</span>
        <select id="selCabang" class="pill-select">
          <option>Memuat…</option>
        </select>
      </label>
      <label class="pill">
        <span>Bucket:</span>
        <select id="selBucket" class="pill-select"></select>
      </label>
      <span class="chip hidden-chip">Created: <b id="sumCreated">-</b></span>
      <span class="chip hidden-chip">NOA: <b id="sumNoa">0</b></span>
      <span class="chip hidden-chip">Saldo PH: <b id="sumSaldo">0</b></span>
      <span class="chip hidden-chip">Bayar Total: <b id="sumBayar">0</b></span>
    </div>

    <form id="formFilterDetail" class="bar-right">
      <div class="fld">
        <label class="lbl" for="onlyPaid">Filter:</label>
        <label class="toggle"><input type="checkbox" id="onlyPaid">Payment</label>
      </div>
      <div class="fld">
        <label class="lbl" for="q">Cari:</label>
        <input id="q" class="f-inp" placeholder="Nama / No Rek" />
      </div>
      <button type="button" id="btnExport" class="f-btn" title="Export Excel">⬇️</button>
    </form>
  </div>

  <!-- Loading -->
  <div id="loadingDetail" class="hidden flex items-center gap-2 text-sm text-gray-600 mb-2">
    <svg class="animate-spin h-5 w-5 text-emerald-600" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span>Memuat detail debitur...</span>
  </div>

  <!-- SCROLLER -->
  <div id="dpScroller" class="flex-1 min-h-0 overflow-hidden rounded border border-gray-200 bg-white">
    <div class="h-full overflow-auto">
      <table id="tabelPHDetail" class="min-w-full text-[12.5px] text-left text-gray-700">
        <thead class="uppercase">
          <tr id="dpHead1" class="text-[11px]">
            <th class="px-2.5 py-1.5 sticky-dp freeze-1 col-norek border-r sort" data-sort="no_rekening" data-type="text">NO REKENING</th>
            <th class="px-2.5 py-1.5 sticky-dp freeze-2 lock-x col-debitur border-r sort" data-sort="nama_nasabah" data-type="text">NAMA DEBITUR</th>

            <th class="px-2 py-1.5 text-right sticky-dp col-amt border-r sort" data-sort="saldo_hapus_buku" data-type="num">OSC</th>
            <th class="px-2 py-1.5 text-right sticky-dp col-amt border-r sort" data-sort="bayar_total" data-type="num">BAYAR TOTAL</th>
            <th class="px-2 py-1.5 text-right sticky-dp col-amt border-r sort" data-sort="bayar_pokok" data-type="num">BAYAR POKOK</th>
            <th class="px-2 py-1.5 text-right sticky-dp col-amt border-r sort" data-sort="bayar_bunga" data-type="num">BAYAR BUNGA</th>
            <th class="px-2 py-1.5 sticky-dp col-date border-r sort" data-sort="last_payment_date" data-type="date">LAST PAYMENT</th>
            <th class="px-2 py-1.5 text-right sticky-dp col-amt border-r sort" data-sort="jml_angsuran_bulan_ini" data-type="num">ANGS BLN LALU</th>
            <th class="px-2 py-1.5 text-right sticky-dp col-amt border-r sort" data-sort="sisa_pokok_calc" data-type="num">SISA (PH-POKOK)</th>
          </tr>
        </thead>
        <tbody id="dpTotalRow"></tbody>
        <tbody id="dpBody"></tbody>
      </table>
    </div>
  </div>
</div>

<style>
  *{ box-sizing:border-box; }
  html, body{ overflow-x:hidden; }
  body{ overflow-y:hidden; }
  #dpScroller .h-full{ overscroll-behavior-x: contain; }

  /* ===== Toolbar ===== */
  .toolbar-compact{ display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:nowrap; }
  .bar-left{ display:flex; align-items:center; gap:.5rem; flex:1 1 auto; min-width:0; flex-wrap:wrap; }
  .bar-right{ display:flex; align-items:end; gap:.6rem; margin-left:auto; flex:0 0 auto; }
  .bar-right .fld{ display:flex; align-items:center; gap:.4rem; }
  .lbl{ font-size:12.5px; color:#334155; white-space:nowrap; }
  .chip{ display:inline-block; background:#effdf6; border:1px solid #c7f1df; color:#065f46; border-radius:.6rem; padding:.22rem .55rem; }
  .hidden-chip{ display:none !important; }
  .toggle{ display:inline-flex; align-items:center; gap:.35rem; user-select:none; }

  .pill{ display:inline-flex; align-items:center; gap:.45rem; padding:.22rem .55rem; border-radius:999px; background:#effdf6; border:1px solid #c7f1df; color:#065f46; }
  .pill-select{ border:0; background:transparent; outline:none; font-weight:700; color:#065f46; min-width:12rem; padding-right:.2rem; }

  .f-inp{ border:1px solid #cbd5e1; border-radius:.6rem; padding:.38rem .6rem; font-size:13px; background:#fff; height:36px; }
  .f-btn{ width:38px; height:38px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center; background:#10b981; color:#fff; box-shadow:0 6px 14px rgba(16,185,129,.25); }
  .f-btn:hover{ background:#059669; }

  /* ===== Tabel ===== */
  #dpScroller{
    --dp_colNorek: 7.5rem;
    --dp_colDeb:   12rem;
    --dp_colDeb_m: 7.5rem;
    --dp_head:     32px;
  }

  #tabelPHDetail{ table-layout:fixed; border-collapse:separate; border-spacing:0; }
  #tabelPHDetail th, #tabelPHDetail td{ border-bottom:1px solid #eef2f7; }

  /* Header hijau + sticky */
  #tabelPHDetail thead th{
    position:sticky; top:0; z-index:88;
    background:#d9ead3 !important; color:#0f5132;
    border-bottom:1px solid #b7d4c1; user-select:none;
  }
  #tabelPHDetail thead th.border-r{ border-right:1px solid #b7d4c1; }
  #tabelPHDetail thead th.freeze-1{ left:0; z-index:90; }
  #tabelPHDetail thead th.freeze-2{ left:var(--dp_colNorek); z-index:89; }

  /* Lebar & freeze (body) */
  #tabelPHDetail .col-norek{ width:var(--dp_colNorek); min-width:var(--dp_colNorek); }
  #tabelPHDetail .col-debitur{ width:var(--dp_colDeb); min-width:var(--dp_colDeb); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  #tabelPHDetail .col-amt{ width:7.4rem; min-width:7rem; text-align:right; }
  #tabelPHDetail .col-date{ width:8.7ch; min-width:8ch; white-space:nowrap; font-variant-numeric: tabular-nums; }

  #tabelPHDetail td.freeze-1{ position:sticky; left:0; z-index:41; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06); }
  #tabelPHDetail td.freeze-2{ position:sticky; left:var(--dp_colNorek); z-index:40; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06); }

  /* TOTAL nempel ke bawah header */
  #tabelPHDetail tbody tr.sticky-total td{ position:sticky; top:var(--dp_head); z-index:70; background:#eaf7ea; color:#065f46; border-bottom:1px solid #a7d7c1; }
  #tabelPHDetail tbody tr.sticky-total td.freeze-1{ z-index:91; }
  #tabelPHDetail tbody tr.sticky-total td.freeze-2{ z-index:90; }

  #tabelPHDetail tbody tr:hover td{ background:#f9fafb; }
  .zero{ color:#9ca3af; }

  /* Panah sort */
  th.sort{ cursor:pointer; }
  th.sort::after{
    content:""; display:inline-block; margin-left:.35rem; width:0; height:0;
    border-left:4px solid transparent; border-right:4px solid transparent; border-top:6px solid #64748b; opacity:.65; transform:translateY(2px);
  }
  th.sort.sorted-asc::after{ border-top-color:#065f46; transform: rotate(180deg) translateY(-2px); opacity:1; }
  th.sort.sorted-desc::after{ border-top-color:#065f46; opacity:1; }

  /* ===== Mobile ===== */
  @media (max-width:640px){
    /* Cabang + Bucket satu baris */
    .toolbar-compact{ display:grid; grid-template-columns: 1fr; gap:.65rem; }
    .bar-left{ display:grid; grid-template-columns: 1fr 1fr; gap:.5rem; width:100%; }
    .bar-left .pill{ width:100%; }
    .pill-select{ min-width:0; width:100%; font-size:12.5px; }
    .bar-right{ width:100%; display:grid; grid-template-columns: 1fr 1fr auto; gap:.5rem .6rem; align-items:center; }
    .f-btn{ width:34px; height:34px; justify-self:end; border-radius:.8rem; }

    #tabelPHDetail{ font-size:11.5px; }
    #tabelPHDetail thead th{ font-size:10.5px; }
    #tabelPHDetail th, #tabelPHDetail td{ padding:.32rem .4rem; }

    /* 1) Sembunyikan NO REKENING */
    #tabelPHDetail th.col-norek, #tabelPHDetail td.col-norek{ display:none !important; }

    /* 2) NAMA DEBITUR sticky PALING KIRI (head + total + isi), lebar 7.5rem */
    #tabelPHDetail .col-debitur{ width:var(--dp_colDeb_m); min-width:var(--dp_colDeb_m); white-space:normal; word-break:break-word; }
    #tabelPHDetail thead th.freeze-2,
    #tabelPHDetail tbody td.freeze-2,
    #tabelPHDetail tbody tr.sticky-total td.freeze-2{
      left:0 !important;               /* <- kiri pol */
      right:auto !important;
      box-shadow: 1px 0 0 rgba(0,0,0,.06); /* garis di kanan kolom sticky */
    }

    /* Kunci gesture horizontal saat menyentuh kolom sticky */
    #tabelPHDetail .lock-x{ touch-action: pan-y; -ms-touch-action: pan-y; }

    /* Padatkan angka */
    #tabelPHDetail .col-amt{ width:6.6rem; min-width:6.2rem; }
    #tabelPHDetail .col-date{ width:8ch; min-width:7ch; }
  }
</style>

<script>
  /* ===== Helpers ===== */
  const nfID = new Intl.NumberFormat('id-ID');
  const fmt = n => nfID.format(Number(n||0));
  const fmtDate = (s) => {
    if (!s) return '-';
    const d = new Date(s);
    if (isNaN(d)) return s;
    const dd = String(d.getDate()).padStart(2,'0');
    const mm = String(d.getMonth()+1).padStart(2,'0');
    const yy = d.getFullYear();
    return `${dd}-${mm}-${yy}`;
  };

  function setDPSticky(){
    const h = document.getElementById('dpHead1')?.offsetHeight || 32;
    document.getElementById('dpScroller').style.setProperty('--dp_head', h + 'px');
  }
  function sizeDPScroller(){
    const wrap = document.getElementById('dpScroller'); if(!wrap) return;
    const top = wrap.getBoundingClientRect().top;
    wrap.style.height = Math.max(260, window.innerHeight - top - 10) + 'px';
  }
  window.addEventListener('resize', ()=>{ setDPSticky(); sizeDPScroller(); });

  /* ===== State ===== */
  let RAW = [];
  let VIEW = [];
  let sortKey = 'saldo_hapus_buku';
  let sortDir = 'desc';

  /* ===== Dropdowns ===== */
  const BUCKETS = ['< 2019','2019','2023','2024','2025'];
  const selCabang  = document.getElementById('selCabang');
  const selBucket  = document.getElementById('selBucket');
  const sumCreated = document.getElementById('sumCreated');

  function loadBucketOptions(initial){
    selBucket.innerHTML = BUCKETS.map(b => `<option value="${b}">${b.replace('< ','<')}</option>`).join('');
    if (initial && BUCKETS.includes(initial)) selBucket.value = initial;
    else selBucket.selectedIndex = 0;
  }
  async function loadKantorOptions(initialCode){
    try{
      const r = await fetch('./api/kode/', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ type:'kode_kantor' })
      });
      const j = await r.json();
      const list = Array.isArray(j.data) ? j.data : [];
      selCabang.innerHTML = '';
      list
        .filter(it => it.kode_kantor && it.kode_kantor !== '000')
        .sort((a,b)=> String(a.kode_kantor).localeCompare(String(b.kode_kantor)))
        .forEach(it=>{
          const code = String(it.kode_kantor).padStart(3,'0');
          const name = it.nama_kantor || it.nama_cabang || '';
          const opt = document.createElement('option');
          opt.value = code; opt.textContent = `${name}`;
          selCabang.appendChild(opt);
        });
      const want = initialCode ? String(initialCode).padStart(3,'0') : '';
      if (want && [...selCabang.options].some(o=>o.value===want)) selCabang.value = want;
      else if (selCabang.options.length) selCabang.selectedIndex = 0;
    }catch{
      selCabang.innerHTML = '<option value="">(Gagal memuat cabang)</option>';
    }
  }

  const params = JSON.parse(localStorage.getItem('hapus_buku_params') || '{}');
  sumCreated.textContent = params.created || '-';

  (async () => {
    await loadKantorOptions(params.kode_kantor);
    loadBucketOptions(params.bucket);
    params.kode_kantor = selCabang.value;
    params.bucket      = selBucket.value;
    localStorage.setItem('hapus_buku_params', JSON.stringify(params));
    fetchDetail();
  })();

  selCabang.addEventListener('change', ()=>{
    params.kode_kantor = selCabang.value;
    localStorage.setItem('hapus_buku_params', JSON.stringify(params));
    fetchDetail();
  });
  selBucket.addEventListener('change', ()=>{
    params.bucket = selBucket.value;
    localStorage.setItem('hapus_buku_params', JSON.stringify(params));
    fetchDetail();
  });

  /* ===== Fetch ===== */
  function fetchDetail(){
    const { kode_kantor, bucket, created } = params || {};
    if(!kode_kantor || !bucket || !created) return;
    document.getElementById('loadingDetail').classList.remove('hidden');

    fetch('./api/hapus_buku/', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ type:'detail debitur bucket', kode_kantor, bucket, created })
    })
    .then(r=>r.json())
    .then(res=>{
      RAW = Array.isArray(res.data) ? res.data : [];
      RAW.forEach(r => r.sisa_pokok_calc = Number(r.saldo_hapus_buku||0) - Number(r.bayar_pokok||0));
      applyFiltersAndRender();
    })
    .catch(()=>{
      document.getElementById('dpBody').innerHTML = `<tr><td colspan="9" class="px-4 py-3 text-red-600">Gagal memuat data.</td></tr>`;
      document.getElementById('dpTotalRow').innerHTML = '';
      setSummary([]);
    })
    .finally(()=> document.getElementById('loadingDetail').classList.add('hidden'));
  }

  /* ===== Filter & Sort ===== */
  document.getElementById('onlyPaid').addEventListener('change', applyFiltersAndRender);
  document.getElementById('q').addEventListener('input', debounce(applyFiltersAndRender, 180));

  document.querySelector('#tabelPHDetail thead').addEventListener('click', (e)=>{
    const th = e.target.closest('th.sort'); if(!th) return;
    const newKey = th.dataset.sort;
    if(sortKey === newKey){
      sortDir = (sortDir === 'asc') ? 'desc' : 'asc';
    } else {
      sortKey = newKey;
      const type = th.dataset.type || 'num';
      sortDir = (type === 'text') ? 'asc' : 'desc';
    }
    applyFiltersAndRender();
  });

  function applyFiltersAndRender(){
    const onlyPaid = document.getElementById('onlyPaid').checked;
    const q = (document.getElementById('q').value || '').trim().toLowerCase();

    VIEW = RAW.filter(r=>{
      if (onlyPaid && Number(r.bayar_total || 0) <= 0) return false;
      if (!q) return true;
      const norek = String(r.no_rekening||'').toLowerCase();
      const nama  = String(r.nama_nasabah||'').toLowerCase();
      return norek.includes(q) || nama.includes(q);
    });

    const ths = document.querySelectorAll('#tabelPHDetail thead th.sort');
    ths.forEach(t => t.classList.remove('sorted-asc','sorted-desc'));
    const activeTh = [...ths].find(t => t.dataset.sort === sortKey);
    if (activeTh) activeTh.classList.add(sortDir==='asc' ? 'sorted-asc' : 'sorted-desc');

    VIEW.sort((a,b)=>{
      const type = (activeTh?.dataset?.type)||'num';
      if(type==='text'){
        const A = String(a[sortKey]||'').toLowerCase();
        const B = String(b[sortKey]||'').toLowerCase();
        if (A<B) return (sortDir==='asc')?-1:1;
        if (A>B) return (sortDir==='asc')?1:-1;
        return 0;
      }
      if(type==='date'){
        const ta = a[sortKey] ? new Date(a[sortKey]).getTime() : 0;
        const tb = b[sortKey] ? new Date(b[sortKey]).getTime() : 0;
        return (sortDir==='asc') ? (ta - tb) : (tb - ta);
      }
      const na = Number(a[sortKey]||0);
      const nb = Number(b[sortKey]||0);
      return (sortDir==='asc') ? (na - nb) : (nb - na);
    });

    renderTable(VIEW);
    setSummary(VIEW);
    lockHorizontalPanOnSticky();
  }

  /* ===== Render ===== */
  function renderTable(rows){
    const tSaldo = rows.reduce((s,r)=> s + Number(r.saldo_hapus_buku||0), 0);
    const tByrT  = rows.reduce((s,r)=> s + Number(r.bayar_total||0), 0);
    const tByrP  = rows.reduce((s,r)=> s + Number(r.bayar_pokok||0), 0);
    const tByrB  = rows.reduce((s,r)=> s + Number(r.bayar_bunga||0), 0);
    const tSisa  = tSaldo - tByrP;
    const tAngsL = rows.reduce((s,r)=> s + Number(r.jml_angsuran_bulan_ini||0), 0);

    document.getElementById('dpTotalRow').innerHTML = `
      <tr class="sticky-total font-semibold">
        <td class="px-2.5 py-1.5 freeze-1 col-norek"></td>
        <td class="px-2.5 py-1.5 freeze-2 lock-x col-debitur">TOTAL</td>
        <td class="px-2 py-1.5 text-right col-amt">${fmt(tSaldo)}</td>
        <td class="px-2 py-1.5 text-right col-amt">${fmt(tByrT)}</td>
        <td class="px-2 py-1.5 text-right col-amt">${fmt(tByrP)}</td>
        <td class="px-2 py-1.5 text-right col-amt">${fmt(tByrB)}</td>
        <td class="px-2 py-1.5 col-date"></td>
        <td class="px-2 py-1.5 text-right col-amt">${fmt(tAngsL)}</td>
        <td class="px-2 py-1.5 text-right col-amt">${fmt(tSisa)}</td>
      </tr>`;

    let html = '';
    for (const d of rows){
      const sisa = Number(d.sisa_pokok_calc || 0);
      html += `
        <tr class="border-b hover:bg-gray-50">
          <td class="px-2.5 py-1.5 freeze-1 col-norek" title="${d.no_rekening||'-'}">${shortMid(d.no_rekening, 12)}</td>
          <td class="px-2.5 py-1.5 freeze-2 lock-x col-debitur" title="${d.nama_nasabah||'-'}">${shortTail(d.nama_nasabah, 18)}</td>
          <td class="px-2 py-1.5 text-right col-amt">${fmt(d.saldo_hapus_buku||0)}</td>
          <td class="px-2 py-1.5 text-right col-amt ${Number(d.bayar_total||0) ? '' : 'zero'}">${fmt(d.bayar_total||0)}</td>
          <td class="px-2 py-1.5 text-right col-amt ${Number(d.bayar_pokok||0) ? '' : 'zero'}">${fmt(d.bayar_pokok||0)}</td>
          <td class="px-2 py-1.5 text-right col-amt ${Number(d.bayar_bunga||0) ? '' : 'zero'}">${fmt(d.bayar_bunga||0)}</td>
          <td class="px-2 py-1.5 col-date">${fmtDate(d.last_payment_date)}</td>
          <td class="px-2 py-1.5 text-right col-amt ${Number(d.jml_angsuran_bulan_ini||0) ? '' : 'zero'}">${fmt(d.jml_angsuran_bulan_ini||0)}</td>
          <td class="px-2 py-1.5 text-right col-amt ${sisa ? '' : 'zero'}">${fmt(sisa)}</td>
        </tr>`;
    }
    document.getElementById('dpBody').innerHTML = html;

    setDPSticky(); sizeDPScroller();
    setTimeout(()=>{ setDPSticky(); sizeDPScroller(); }, 40);
  }

  function setSummary(rows){
    const noa   = rows.length;
    const saldo = rows.reduce((s,r)=> s + Number(r.saldo_hapus_buku||0), 0);
    const bayar = rows.reduce((s,r)=> s + Number(r.bayar_total||0), 0);
    document.getElementById('sumNoa').textContent   = fmt(noa);
    document.getElementById('sumSaldo').textContent = fmt(saldo);
    document.getElementById('sumBayar').textContent = fmt(bayar);
  }

  /* ===== Export Excel ===== */
  document.getElementById('btnExport').addEventListener('click', ()=>{
    const rows = (VIEW.length ? VIEW : RAW).map(d => ({
      'NO REKENING'    : d.no_rekening || '',
      'NAMA DEBITUR'   : d.nama_nasabah || '',
      'SALDO PH'       : Number(d.saldo_hapus_buku||0),
      'BAYAR TOTAL'    : Number(d.bayar_total||0),
      'BAYAR POKOK'    : Number(d.bayar_pokok||0),
      'BAYAR BUNGA'    : Number(d.bayar_bunga||0),
      'LAST PAYMENT'   : d.last_payment_date ? new Date(d.last_payment_date) : '',
      'ANGS BLN LALU'  : Number(d.jml_angsuran_bulan_ini||0),
      'SISA (PH-POKOK)': Number(d.saldo_hapus_buku||0) - Number(d.bayar_pokok||0)
    }));
    if(!rows.length){ alert('Tidak ada data.'); return; }

    const headers = Object.keys(rows[0]);
    if (window.XLSX){
      const ws = XLSX.utils.json_to_sheet(rows, { header: headers });
      ws['!cols'] = [{wch:14},{wch:24},{wch:14},{wch:14},{wch:14},{wch:14},{wch:12},{wch:14},{wch:14}];
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, 'Detail PH');
      const p = JSON.parse(localStorage.getItem('hapus_buku_params')||'{}');
      const name = `detail_ph_bucket_${(p.kode_kantor||'')}_${(p.bucket||'')}_${(p.created||'')}.xlsx`;
      XLSX.writeFile(wb, name, {cellDates:true});
    } else {
      const csv = [headers.join(',')].concat(
        rows.map(r => headers.map(h => {
          const v = r[h];
          if (v instanceof Date) return v.toISOString().slice(0,10);
          const s = (v==null) ? '' : String(v);
          return /[",\n]/.test(s) ? `"${s.replace(/"/g,'""')}"` : s;
        }).join(','))
      ).join('\r\n');
      const url = URL.createObjectURL(new Blob(['\ufeff'+csv],{type:'text/csv;charset=utf-8'}));
      const a = document.createElement('a'); a.href = url; a.download = 'detail_ph_bucket.csv'; a.click();
      setTimeout(()=>URL.revokeObjectURL(url),0);
    }
  });

  /* ===== Anti-geser (Safari/iOS) ===== */
  function lockHorizontalPanOnSticky(){
    if(!window.matchMedia('(max-width:640px)').matches) return;
    const els = document.querySelectorAll('#tabelPHDetail .lock-x');
    els.forEach(el=>{
      el._lockBound && el.removeEventListener('touchmove', el._lockBound, {passive:false});
      const fn = (e)=>{
        const t = e.touches && e.touches[0]; if(!t) return;
        const sx = el._sx ?? (el._sx = t.clientX);
        const sy = el._sy ?? (el._sy = t.clientY);
        const dx = Math.abs(t.clientX - sx);
        const dy = Math.abs(t.clientY - sy);
        if(dx > dy) e.preventDefault();
      };
      el._lockBound = fn;
      el.addEventListener('touchstart', (e)=>{ const t=e.touches[0]; el._sx=t.clientX; el._sy=t.clientY; }, {passive:true});
      el.addEventListener('touchmove', fn, {passive:false});
      el.addEventListener('touchend', ()=>{ el._sx=el._sy=null; }, {passive:true});
    });
  }
  window.addEventListener('load', lockHorizontalPanOnSticky);
  window.addEventListener('resize', lockHorizontalPanOnSticky);

  /* ===== Utils ===== */
  function debounce(fn, ms=150){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a),ms); }; }
  function shortTail(s, n=18){ s=String(s||''); return s.length<=n? s : s.slice(0,n).trimEnd()+'…'; }
  function shortMid(s, n=12){ s=String(s||''); if(s.length<=n) return s; const k=n-1, f=Math.ceil(k/2), b=Math.floor(k/2); return s.slice(0,f)+'…'+s.slice(-b); }
</script>

<!-- SheetJS -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
