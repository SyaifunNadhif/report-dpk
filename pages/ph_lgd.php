<!-- list_ph_lgd — Kolom urut + LAST PAY setelah TOTAL; total sticky; mobile hide Nama Kantor & No Rek -->
<div class="max-w-7xl mx-auto px-4 py-6 h-screen flex flex-col">
  <!-- Header -->
  <div class="flex items-center justify-between mb-2">
    <h1 class="text-2xl font-bold flex items-center gap-2">
      <span>📘</span><span>List PH LGD</span>
    </h1>
    <a href="bucket_saldo_ph" class="text-emerald-700 hover:underline">← Back</a>
  </div>

  <!-- Toolbar -->
  <div class="toolbar-compact mb-3">
    <div class="bar-left text-sm text-gray-700">
      <label class="pill">
        <span>Kantor:</span>
        <select id="selCabang" class="pill-select">
          <option value="">konsolidasi</option>
        </select>
      </label>

      <label class="pill">
        <span>Bucket:</span>
        <select id="selBucket" class="pill-select">
          <option value="">-</option>
          <option>2023</option>
          <option>2024</option>
          <option>2025</option>
        </select>
      </label>

      <span class="pill">
        <span>Created:</span>
        <input id="created" type="date" class="pill-select" />
      </span>
    </div>

    <form id="formFilter" class="bar-right">
      <div class="fld">
        <label class="lbl" for="q">Cari:</label>
        <input id="q" class="f-inp" placeholder="Nama / No Rek / Alamat" />
      </div>
      <button type="button" id="btnExport" class="f-btn" title="Export Excel">⬇️</button>
    </form>
  </div>

  <!-- Loading -->
  <div id="loading" class="hidden flex items-center gap-2 text-sm text-gray-600 mb-2">
    <svg class="animate-spin h-5 w-5 text-emerald-600" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span>Memuat data…</span>
  </div>

  <!-- Tabel -->
  <div id="lgdScroller" class="flex-1 min-h-0 overflow-hidden rounded border border-gray-200 bg-white">
    <div class="h-full overflow-auto">
      <table id="tabelPHLGD" class="min-w-full text-[12.5px] text-left text-gray-700">
        <thead class="uppercase">
          <tr id="lgdHead" class="text-[11px]">
            <!-- Urutan persis seperti diminta -->
            <th class="px-2.5 py-1.5 sticky th-namakantor col-namakantor border-r sort" data-sort="nama_kantor" data-type="text">NAMA KANTOR</th>
            <th class="px-2.5 py-1.5 sticky th-norek freeze-1 col-norek border-r sort" data-sort="no_rekening" data-type="text">NO REKENING</th>
            <th class="px-2.5 py-1.5 sticky th-debitur freeze-2 lock-x col-debitur border-r sort" data-sort="nama_nasabah" data-type="text">NAMA DEBITUR</th>
            <th class="px-2.5 py-1.5 sticky th-alamat col-alamat border-r sort" data-sort="alamat" data-type="text">ALAMAT</th>
            <th class="px-2.5 py-1.5 sticky th-year col-year border-r sort" data-sort="tahun_ph" data-type="num">TAHUN PH</th>

            <th class="px-2 py-1.5 text-right sticky th-amt col-amt border-r sort" data-sort="jml_hapus_buku" data-type="num">JML PH</th>
            <th class="px-2 py-1.5 text-right sticky th-amt col-amt border-r sort" data-sort="bayar_pokok" data-type="num">BYR POKOK</th>
            <th class="px-2 py-1.5 text-right sticky th-amt col-amt border-r sort" data-sort="bayar_bunga" data-type="num">BYR BUNGA</th>
            <th class="px-2 py-1.5 text-right sticky th-amt col-amt border-r sort" data-sort="bayar_total" data-type="num">BYR TOTAL</th>
            <!-- NEW: LAST PAY (setelah BYR TOTAL) -->
            <th class="px-2.5 py-1.5 sticky th-date col-date border-r sort" data-sort="last_payment_date" data-type="date">LAST PAY</th>

            <th class="px-2 py-1.5 text-right sticky th-amt col-amt border-r sort" data-sort="sisa_saldo" data-type="num">SISA SALDO</th>
            <th class="px-2 py-1.5 text-right sticky th-amt col-amt border-r sort" data-sort="recovery" data-type="num">RECOVERY</th>
            <th class="px-2 py-1.5 text-right sticky th-amt col-amt border-r sort" data-sort="rr" data-type="num">RR (%)</th>
            <th class="px-2 py-1.5 text-right sticky th-amt col-amt border-r sort" data-sort="jml_angsuran_bulan_ini" data-type="num">ANGS Lalu</th>
          </tr>
        </thead>

        <!-- TOTAL sticky tepat di bawah header -->
        <tbody id="tbTotal"></tbody>

        <tbody id="lgdBody"></tbody>
      </table>
    </div>
  </div>
</div>

<style>
  *{ box-sizing:border-box; }
  html, body{ overflow-x:hidden; }
  body{ overflow-y:hidden; }
  #lgdScroller .h-full{ overscroll-behavior-x: contain; }

  .toolbar-compact{ display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:nowrap; }
  .bar-left{ display:flex; align-items:center; gap:.5rem; flex:1 1 auto; min-width:0; flex-wrap:wrap; }
  .bar-right{ display:flex; align-items:end; gap:.6rem; margin-left:auto; flex:0 0 auto; }
  .bar-right .fld{ display:flex; align-items:center; gap:.4rem; }
  .lbl{ font-size:12.5px; color:#334155; white-space:nowrap; }
  .pill{ display:inline-flex; align-items:center; gap:.45rem; padding:.22rem .55rem; border-radius:999px; background:#effdf6; border:1px solid #c7f1df; color:#065f46; }
  .pill-select{ border:0; background:transparent; outline:none; font-weight:700; color:#065f46; min-width:12rem; padding-right:.2rem; }

  .f-inp{ border:1px solid #cbd5e1; border-radius:.6rem; padding:.38rem .6rem; font-size:13px; background:#fff; height:36px; }
  .f-btn{ width:38px; height:38px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center; background:#10b981; color:#fff; box-shadow:0 6px 14px rgba(16,185,129,.25); }
  .f-btn:hover{ background:#059669; }

  #lgdScroller{
    --colNorek: 7.5rem;
    --colDeb:   12rem;
    --colDeb_m: 7.5rem;
    --headH:    32px;
  }
  #tabelPHLGD{ table-layout:fixed; border-collapse:separate; border-spacing:0; }
  #tabelPHLGD th, #tabelPHLGD td{ border-bottom:1px solid #eef2f7; }

  /* Header hijau + sticky */
  #tabelPHLGD thead th{
    position:sticky; top:0; z-index:88;
    background:#d9ead3 !important; color:#0f5132;
    border-bottom:1px solid #b7d4c1; user-select:none;
  }
  #tabelPHLGD thead th.border-r{ border-right:1px solid #b7d4c1; }
  #tabelPHLGD thead .freeze-1{ left:0; z-index:90; }
  #tabelPHLGD thead .freeze-2{ left:var(--colNorek); z-index:89; }

  /* Lebar kolom */
  #tabelPHLGD .col-namakantor{ width:14rem; min-width:12rem; }
  #tabelPHLGD .col-norek{ width:var(--colNorek); min-width:var(--colNorek); }
  #tabelPHLGD .col-debitur{ width:var(--colDeb); min-width:var(--colDeb); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  #tabelPHLGD .col-alamat{ width:18rem; min-width:14rem; }
  #tabelPHLGD .col-year{ width:7ch; min-width:6ch; text-align:center; }
  #tabelPHLGD .col-amt{ width:7.6rem; min-width:7rem; text-align:right; font-variant-numeric: tabular-nums; }
  #tabelPHLGD .col-date{ width:9.5ch; min-width:8ch; white-space:nowrap; font-variant-numeric: tabular-nums; text-align:left; }

  /* Freeze body cells */
  #tabelPHLGD td.freeze-1{ position:sticky; left:0; z-index:41; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06); }
  #tabelPHLGD td.freeze-2{ position:sticky; left:var(--colNorek); z-index:40; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06); }

  /* TOTAL sticky tepat di bawah header */
  #tabelPHLGD tbody tr.sticky-total td{
    position:sticky; top:var(--headH); z-index:70;
    background:#eaf7ea; color:#065f46; border-bottom:1px solid #a7d7c1;
  }
  #tabelPHLGD tbody tr.sticky-total td.freeze-1{ z-index:91; }
  #tabelPHLGD tbody tr.sticky-total td.freeze-2{ z-index:90; }

  th.sort{ cursor:pointer; }
  th.sort::after{
    content:""; display:inline-block; margin-left:.35rem; width:0; height:0;
    border-left:4px solid transparent; border-right:4px solid transparent; border-top:6px solid #64748b; opacity:.65; transform:translateY(2px);
  }
  th.sort.sorted-asc::after{ border-top-color:#065f46; transform: rotate(180deg) translateY(-2px); opacity:1; }
  th.sort.sorted-desc::after{ border-top-color:#065f46; opacity:1; }

  /* ===== Mobile ===== */
  @media (max-width:640px){
    .toolbar-compact{ display:grid; grid-template-columns: 1fr; gap:.65rem; }
    .bar-left{ display:grid; grid-template-columns: 1fr 1fr; gap:.5rem; width:100%; }
    .bar-left .pill{ width:100%; }
    .pill-select{ min-width:0; width:100%; font-size:12.5px; }
    .bar-right{ width:100%; display:grid; grid-template-columns: 1fr auto; gap:.5rem .6rem; align-items:center; }
    .f-btn{ width:34px; height:34px; justify-self:end; border-radius:.8rem; }

    #tabelPHLGD{ font-size:11.5px; }
    #tabelPHLGD thead th{ font-size:10.5px; }
    #tabelPHLGD th, #tabelPHLGD td{ padding:.32rem .4rem; }

    /* Hide Nama Kantor + No Rekening */
    #tabelPHLGD th.th-namakantor, #tabelPHLGD td.col-namakantor{ display:none !important; }
    #tabelPHLGD th.th-norek,      #tabelPHLGD td.col-norek     { display:none !important; }

    /* Nama Debitur sticky kiri 7.5rem */
    #tabelPHLGD .col-debitur{ width:var(--colDeb_m); min-width:var(--colDeb_m); white-space:normal; word-break:break-word; }
    #tabelPHLGD thead .th-debitur,
    #tabelPHLGD tbody td.freeze-2,
    #tabelPHLGD tbody tr.sticky-total td.freeze-2{
      left:0 !important; right:auto !important;
      box-shadow: 1px 0 0 rgba(0,0,0,.06);
    }

    #tabelPHLGD .lock-x{ touch-action: pan-y; -ms-touch-action: pan-y; }
    #tabelPHLGD .col-amt{ width:6.6rem; min-width:6.2rem; }
    #tabelPHLGD .col-alamat{ width:12rem; min-width:10rem; }
  }
</style>

<script>
  /* ===== Config ===== */
  const API_URL = './api/hapus_buku/';

  /* ===== Helpers ===== */
  const nfID = new Intl.NumberFormat('id-ID');
  const fmt = n => nfID.format(Number(n||0));
  const pct2 = x => (Number(x||0)*100).toFixed(2); // RR% tampilan
  const fmtDate = s => {
    if(!s) return '-';
    const d = new Date(s); if(isNaN(d)) return s;
    return `${String(d.getDate()).padStart(2,'0')}-${String(d.getMonth()+1).padStart(2,'0')}-${d.getFullYear()}`;
  };

  function sizeScroller(){
    const wrap = document.getElementById('lgdScroller'); if(!wrap) return;
    const top = wrap.getBoundingClientRect().top;
    wrap.style.height = Math.max(260, window.innerHeight - top - 10) + 'px';
  }
  function setHeadH(){
    const h = document.getElementById('lgdHead')?.offsetHeight || 32;
    document.getElementById('lgdScroller').style.setProperty('--headH', h + 'px');
  }
  window.addEventListener('resize', ()=>{ sizeScroller(); setHeadH(); });

  /* ===== State ===== */
  let RAW = [];
  let VIEW = [];
  let sortKey = 'jml_hapus_buku';
  let sortDir = 'desc';

  /* ===== Dropdowns ===== */
  const selCabang  = document.getElementById('selCabang');
  const selBucket  = document.getElementById('selBucket');
  const createdInp = document.getElementById('created');

  // Ambil last_created dari API date; fallback ke EOM bulan lalu
  (async () => {
    const d = await getLastHarianData();
    if (d && d.last_created) {
      createdInp.value = d.last_created;            // set dari API
    } else {
      const prevEOM = new Date();                   // fallback
      prevEOM.setDate(1); prevEOM.setHours(0,0,0,0);
      prevEOM.setDate(0);
      createdInp.value = prevEOM.toISOString().slice(0,10);
    }
    await loadKantorOptions();
    fetchList();                                    // pertama kali fetch
  })();

    async function getLastHarianData() {
    try {
        const r = await fetch('./api/date/', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        // body: JSON.stringify({}) // kirim payload kosong (ubah kalau perlu)
        // cache: 'no-store' // aktifkan jika ingin selalu fresh
        });
        if (!r.ok) throw new Error('HTTP ' + r.status);

        const j = await r.json();
        // kalau API baliknya { data: {...} } ambil .data, kalau langsung object pakai j
        return (j && (j.data ?? j)) || null;
    } catch (err) {
        console.error('getLastHarianData failed:', err);
        return null;
    }
    }


  async function loadKantorOptions(){
    try{
      const r = await fetch('./api/kode/', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ type:'kode_kantor' })
      });
      const j = await r.json();
      const list = Array.isArray(j.data) ? j.data : [];
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
    }catch{}
  }

  [selCabang, selBucket, createdInp].forEach(el=> el.addEventListener('change', fetchList));
  document.getElementById('q').addEventListener('input', debounce(applyFiltersAndRender, 180));

  document.querySelector('#tabelPHLGD thead').addEventListener('click', (e)=>{
    const th = e.target.closest('th.sort'); if(!th) return;
    const newKey = th.dataset.sort;
    if(sortKey === newKey){ sortDir = (sortDir==='asc')?'desc':'asc'; }
    else { sortKey = newKey; sortDir = (th.dataset.type==='text')?'asc':'desc'; }
    applyFiltersAndRender();
  });

  /* ===== Fetch ===== */
  function fetchList(){
    const kode_kantor = selCabang.value || null;
    const bucket      = selBucket.value || null;
    const created     = createdInp.value;
    if(!created){ alert('Tanggal created belum diisi'); return; }

    document.getElementById('loading').classList.remove('hidden');

    fetch(API_URL, {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ type:'detail debitur ph lgd', created, kode_kantor, bucket })
    })
    .then(r=>r.json())
    .then(res=>{
      RAW = Array.isArray(res.data) ? res.data : [];
      // fallback kalkulasi bila API tidak lengkap
      RAW.forEach(d=>{
        const jph = +d.jml_hapus_buku || 0;
        const byT = +d.bayar_total || 0;
        const byP = +d.bayar_pokok || 0;
        const sPH = +d.saldo_hapus_buku || 0;
        if(d.sisa_saldo == null) d.sisa_saldo = String(sPH - byP);
        if(d.recovery   == null) d.recovery   = String(byT);
        if(d.rr         == null) d.rr         = jph ? String((+d.recovery)/jph) : "0";
      });
      applyFiltersAndRender();
    })
    .catch(()=>{ RAW=[]; applyFiltersAndRender(); })
    .finally(()=> document.getElementById('loading').classList.add('hidden'));
  }

  /* ===== Filter + Sort + Render ===== */
  function applyFiltersAndRender(){
    const q = (document.getElementById('q').value || '').trim().toLowerCase();
    VIEW = RAW.filter(r=>{
      if(!q) return true;
      return (
        String(r.no_rekening||'').toLowerCase().includes(q) ||
        String(r.nama_nasabah||'').toLowerCase().includes(q) ||
        String(r.alamat||'').toLowerCase().includes(q)
      );
    });

    const ths = document.querySelectorAll('#tabelPHLGD thead th.sort');
    ths.forEach(t => t.classList.remove('sorted-asc','sorted-desc'));
    const activeTh = [...ths].find(t => t.dataset.sort === sortKey);
    if (activeTh) activeTh.classList.add(sortDir==='asc' ? 'sorted-asc' : 'sorted-desc');

    VIEW.sort((a,b)=>{
      const type = (activeTh?.dataset?.type)||'num';
      if(type==='text'){
        const A = String(a[sortKey]||'').toLowerCase();
        const B = String(b[sortKey]||'').toLowerCase();
        return (sortDir==='asc') ? (A.localeCompare(B)) : (B.localeCompare(A));
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

    renderTotals(VIEW);
    renderRows(VIEW);
    sizeScroller(); setHeadH();
    setTimeout(()=>{ sizeScroller(); setHeadH(); }, 40);
  }

  function renderTotals(rows){
    const sum = (k)=> rows.reduce((s,r)=> s + Number(r[k]||0), 0);
    const jmlPH   = sum('jml_hapus_buku');
    const byrP    = sum('bayar_pokok');
    const byrB    = sum('bayar_bunga');
    const byrT    = sum('bayar_total');
    const sisa    = sum('sisa_saldo');
    const recv    = sum('recovery');
    const rrTot   = jmlPH ? (recv / jmlPH) : 0;

    document.getElementById('tbTotal').innerHTML = `
      <tr class="sticky-total font-semibold">
        <td class="px-2.5 py-1.5 col-namakantor"></td>
        <td class="px-2.5 py-1.5 freeze-1 col-norek"></td>
        <td class="px-2.5 py-1.5 freeze-2 lock-x col-debitur">TOTAL</td>
        <td class="px-2.5 py-1.5 col-alamat"></td>
        <td class="px-2.5 py-1.5 col-year"></td>

        <td class="px-2 py-1.5 text-right col-amt">${fmt(jmlPH)}</td>
        <td class="px-2 py-1.5 text-right col-amt">${fmt(byrP)}</td>
        <td class="px-2 py-1.5 text-right col-amt">${fmt(byrB)}</td>
        <td class="px-2 py-1.5 text-right col-amt">${fmt(byrT)}</td>
        <td class="px-2.5 py-1.5 col-date"></td> <!-- LAST PAY total: kosong -->
        <td class="px-2 py-1.5 text-right col-amt">${fmt(sisa)}</td>
        <td class="px-2 py-1.5 text-right col-amt">${fmt(recv)}</td>
        <td class="px-2 py-1.5 text-right col-amt">${pct2(rrTot)}</td>
        <td class="px-2 py-1.5 text-right col-amt">${fmt(sum('jml_angsuran_bulan_ini'))}</td>
      </tr>`;
  }

  function renderRows(rows){
    const tb = document.getElementById('lgdBody');
    let html = '';
    for(const d of rows){
      html += `
        <tr class="border-b hover:bg-gray-50">
          <td class="px-2.5 py-1.5 col-namakantor" title="${d.nama_kantor||''}">${d.nama_kantor||''}</td>
          <td class="px-2.5 py-1.5 freeze-1 col-norek" title="${d.no_rekening||'-'}">${shortMid(d.no_rekening,12)}</td>
          <td class="px-2.5 py-1.5 freeze-2 lock-x col-debitur" title="${d.nama_nasabah||'-'}">${shortTail(d.nama_nasabah,18)}</td>
          <td class="px-2.5 py-1.5 col-alamat" title="${d.alamat||''}">${shortTail(d.alamat,48)}</td>
          <td class="px-2.5 py-1.5 col-year">${d.tahun_ph ?? ''}</td>

          <td class="px-2 py-1.5 text-right col-amt">${fmt(d.jml_hapus_buku)}</td>
          <td class="px-2 py-1.5 text-right col-amt">${fmt(d.bayar_pokok)}</td>
          <td class="px-2 py-1.5 text-right col-amt">${fmt(d.bayar_bunga)}</td>
          <td class="px-2 py-1.5 text-right col-amt">${fmt(d.bayar_total)}</td>
          <td class="px-2.5 py-1.5 col-date">${fmtDate(d.last_payment_date)}</td> <!-- NEW: LAST PAY -->
          <td class="px-2 py-1.5 text-right col-amt">${fmt(d.sisa_saldo)}</td>
          <td class="px-2 py-1.5 text-right col-amt">${fmt(d.recovery)}</td>
          <td class="px-2 py-1.5 text-right col-amt">${pct2(d.rr)}</td>
          <td class="px-2 py-1.5 text-right col-amt">${fmt(d.jml_angsuran_bulan_ini)}</td>
        </tr>`;
    }
    tb.innerHTML = html || `<tr><td colspan="15" class="px-4 py-3 text-red-600">Tidak ada data.</td></tr>`;
  }

  /* ===== Export ===== */
  document.getElementById('btnExport').addEventListener('click', ()=>{
    const rows = (VIEW.length?VIEW:RAW).map(d=>({
      'NAMA KANTOR' : d.nama_kantor || '',
      'NO REKENING' : d.no_rekening || '',
      'NAMA DEBITUR': d.nama_nasabah || '',
      'ALAMAT'      : d.alamat || '',
      'TAHUN PH'    : Number(d.tahun_ph||0),
      'JML PH'      : Number(d.jml_hapus_buku||0),
      'BYR POKOK'   : Number(d.bayar_pokok||0),
      'BYR BUNGA'   : Number(d.bayar_bunga||0),
      'BYR TOTAL'   : Number(d.bayar_total||0),
      'LAST PAY'    : d.last_payment_date ? new Date(d.last_payment_date) : '',  // NEW order
      'SISA SALDO'  : Number(d.sisa_saldo||0),
      'RECOVERY'    : Number(d.recovery||0),
      'RR (%)'      : Number(pct2(d.rr)),
      'ANGS Lalu'   : Number(d.jml_angsuran_bulan_ini||0)
    }));
    if(!rows.length){ alert('Tidak ada data.'); return; }

    if (window.XLSX){
      const ws = XLSX.utils.json_to_sheet(rows);
      ws['!cols'] = [{wch:18},{wch:16},{wch:24},{wch:40},{wch:10},{wch:14},{wch:12},{wch:12},{wch:12},{wch:12},{wch:14},{wch:12},{wch:10},{wch:12}];
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, 'PH LGD');
      XLSX.writeFile(wb, `ph_lgd_${createdInp.value}_${selCabang.value||'konsolidasi'}_${selBucket.value||'-'}.xlsx`, {cellDates:true});
    } else {
      const headers = Object.keys(rows[0]);
      const csv = [headers.join(',')].concat(
        rows.map(r => headers.map(h => {
          const v = r[h];
          if (v instanceof Date) return v.toISOString().slice(0,10);
          const s = (v==null) ? '' : String(v);
          return /[",\n]/.test(s) ? `"${s.replace(/"/g,'""')}"` : s;
        }).join(','))
      ).join('\r\n');
      const url = URL.createObjectURL(new Blob(['\ufeff'+csv],{type:'text/csv;charset=utf-8'}));
      const a = document.createElement('a'); a.href = url; a.download = 'ph_lgd.csv'; a.click();
      setTimeout(()=>URL.revokeObjectURL(url),0);
    }
  });

  /* ===== Utils ===== */
  function debounce(fn, ms=150){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a),ms); }; }
  function shortTail(s, n=18){ s=String(s||''); return s.length<=n? s : s.slice(0,n).trimEnd()+'…'; }
  function shortMid(s, n=12){ s=String(s||''); if(s.length<=n) return s; const k=n-1, f=Math.ceil(k/2), b=Math.floor(k/2); return s.slice(0,f)+'…'+s.slice(-b); }
</script>

<!-- SheetJS untuk Export -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
