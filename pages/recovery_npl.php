<!-- 💰 Recovery NPL (sticky header + freeze kiri + spacer fix yang ramping) -->
<div class="max-w-7xl mx-auto px-4 py-4 h-screen flex flex-col">
  <!-- Header / Toolbar -->
  <div class="hdr flex flex-wrap items-start gap-2 mb-3">
    <h1 class="title text-2xl font-bold flex items-center gap-2">
      <span>💰</span><span>Recovery NPL</span>
    </h1>

    <!-- Filter -->
    <form id="formFilterRecovery" class="ml-auto">
      <div id="filterREC" class="flex items-center gap-2">
        <label for="closing_date_recovery" class="lbl text-sm text-slate-700">Closing:</label>
        <input type="date" id="closing_date_recovery" class="inp" required>

        <label for="harian_date_recovery" class="lbl text-sm text-slate-700">Harian:</label>
        <input type="date" id="harian_date_recovery" class="inp" required>

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
  <div id="loadingRecovery" class="hidden flex items-center gap-2 text-sm text-gray-600 mb-2">
    <svg class="animate-spin h-5 w-5 text-blue-600" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span>Memuat data Recovery NPL...</span>
  </div>

  <!-- SCROLLER -->
  <div id="recScroller" class="flex-1 min-h-0 overflow-hidden rounded border border-gray-200 bg-white">
    <div class="h-full overflow-auto">
      <table id="tabelRecovery" class="min-w-full text-sm text-left text-gray-700">
        <thead class="uppercase">
          <tr id="recHead1" class="text-xs">
            <th class="px-4 py-2 sticky-rec freeze-1 col1 col-kode">KODE CABANG</th>
            <th class="px-4 py-2 sticky-rec freeze-2 col2 col-nama">NAMA KANTOR</th>
            <th class="px-4 py-2 text-right sticky-rec">NOA LUNAS</th>
            <th class="px-4 py-2 text-right sticky-rec">BAKI DEBET LUNAS</th>
            <th class="px-4 py-2 text-right sticky-rec">NOA BACKFLOW</th>
            <th class="px-4 py-2 text-right sticky-rec">BAKI DEBET BACKFLOW</th>
            <th class="px-4 py-2 text-right sticky-rec" id="sortTotalNoa">TOTAL NOA ⬍</th>
            <th class="px-4 py-2 text-right sticky-rec" id="sortTotalBaki">TOTAL BAKI ⬍</th>
          </tr>
        </thead>

        <!-- TOTAL tepat di bawah header -->
        <tbody id="recoveryTotalRow"></tbody>

        <!-- BODY data -->
        <tbody id="recoveryBody"></tbody>
      </table>
    </div>
  </div>
</div>

<!-- ===== MODAL: Recovery (lebih responsif + kolom tidak terlalu lebar) ===== -->
<div id="modalDebiturRecovery"
     class="fixed inset-0 hidden bg-gray-900/55 backdrop-blur-sm items-center justify-center"
     style="z-index:100000;">
  <div id="modalCardREC"
       class="bg-white rounded-lg shadow overflow-hidden"
       style="width:min(1100px,94vw); max-height:86vh; font-size:clamp(12px,1.05vw,14px);">
    <!-- Header -->
    <div class="flex items-center justify-between p-3 md:p-4 border-b">
      <h3 id="modalTitleRecovery" class="font-semibold"
          style="font-size:clamp(16px,1.6vw,20px);">Detail Debitur Recovery</h3>
      <button id="btnCloseRecovery" class="text-gray-500 hover:text-gray-700 text-xl" aria-label="Tutup">✕</button>
    </div>

    <!-- Body: vertikal scroll + horizontal scroll -->
    <div class="p-2 md:p-4 overflow-y-auto" style="max-height:calc(86vh - 56px);">
      <div class="rmodal-x overflow-x-auto -mx-2 md:mx-0" style="-webkit-overflow-scrolling:touch;">
        <div id="modalBodyRecovery">
          <p class="text-sm text-gray-500">Memuat data debitur...</p>
        </div>
      </div>
    </div>
  </div>
</div>


<style>
  /* ==== Controls / toolbar ==== */
  .inp{ border:1px solid #cbd5e1; border-radius:.6rem; padding:.5rem .75rem; font-size:14px; background:#fff; }
  .btn-icon{ width:42px; height:42px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center;
             background:#2563eb; color:#fff; box-shadow:0 6px 14px rgba(37,99,235,.25); }
  .btn-icon:hover{ background:#1e40af; }
  .hdr{ row-gap:.5rem; }

  @media (max-width:640px){
    .title{ font-size:1.25rem; }
    .hdr{ flex-direction:column; align-items:flex-start; }
    #filterREC{ width:100%; gap:.5rem; }
    .lbl{ display:none; }
    .inp{ flex:0 0 auto; width:200px; max-width:70vw; font-size:13px; padding:.45rem .6rem; }
    .btn-icon{ width:40px; height:40px; }
  }

  /* ==== Table: sticky header + freeze kiri ==== */
  body{ overflow:hidden; }
  #recScroller{ --rec_col1:5rem; --rec_col2:16rem; --rec_head:40px; --rec_totalH:36px; }

  #tabelRecovery{ font-size: clamp(11px, 1.6vw, 14px); }
  #tabelRecovery thead th{ font-size: clamp(10px, 1.4vw, 12px); }

  #tabelRecovery .col1{ width:var(--rec_col1); min-width:var(--rec_col1); }
  #tabelRecovery .col2{ width:var(--rec_col2); min-width:var(--rec_col2); }
  #tabelRecovery th.col-nama, #tabelRecovery td.col-nama{
    max-width:var(--rec_col2); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
  }

  #tabelRecovery .freeze-1{ position:sticky; left:0; z-index:41; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06); }
  #tabelRecovery .freeze-2{ position:sticky; left:var(--rec_col1); z-index:40; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06); }

  #tabelRecovery thead th{ position:sticky; }
  #tabelRecovery thead th.sticky-rec{ top:0; background:#d9ead3; z-index:88; }
  #tabelRecovery thead th.freeze-1{ left:0; z-index:90; background:#d9ead3; }
  #tabelRecovery thead th.freeze-2{ left:var(--rec_col1); z-index:89; background:#d9ead3; }

  #tabelRecovery tbody tr.sticky-total td{
    position:sticky; top:var(--rec_head);
    background:#eaf2ff; color:#1e40af; z-index:70; border-bottom:1px solid #c7d2fe;
  }
  #tabelRecovery tbody tr.sticky-total td.freeze-1{ z-index:91; }
  #tabelRecovery tbody tr.sticky-total td.freeze-2{ z-index:90; }

  #tabelRecovery tbody tr:hover td{ background:#f9fafb; }
  #recoveryBody::after{ content:""; display:block; height: calc(var(--rec_head) + var(--rec_totalH) + 20px); }

  /* ===== Mobile tweaks ===== */
  @media (max-width:640px){
    #tabelRecovery th.col-kode, #tabelRecovery td.col-kode{ display:none; }
    #recScroller{ --rec_col1:0px; --rec_col2:10.5rem; }
    #tabelRecovery .freeze-2, #tabelRecovery thead th.freeze-2{ left:0 !important; }
  }

  /* ===== Modal table helpers (biar tidak terpotong & tetap rapi) ===== */
  .modal-table{ table-layout:fixed; width:100%; }
  .modal-table th, .modal-table td{ white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

  /* tablet */
  @media (max-width:768px){
    #modalCardREC{ width:94vw !important; }
  }
  /* mobile */
  @media (max-width:640px){
    #modalCardREC{ width:94vw !important; max-height:80vh; font-size:12px !important; }
    #modalBodyRecovery{ max-height:72vh !important; }
  }
  /* very small phones */
  @media (max-width:380px){
    #modalCardREC{ width:96vw !important; }
  }
</style>

<style>
  /* ===== Modal table ===== */
  .rmodal-table{ table-layout:fixed; width:100%; border-collapse:separate; border-spacing:0; }
  .rmodal-table thead th{
    position:sticky; top:0; z-index:2;
    background:#f3f4f6; /* abu header */
  }
  .rmodal-table th, .rmodal-table td{
    padding:.5rem .6rem;
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    line-height:1.35;
  }

  /* Lebar kolom pakai clamp agar tidak kelebaran & tetap bisa digeser */
  .c-rek  { width:clamp(110px, 26vw, 160px); }
  .c-nama { width:clamp(160px, 34vw, 260px); }   /* nama diperkecil di mobile */
  .c-rp   { width:clamp(110px, 26vw, 150px); text-align:right; }
  .c-kol  { width:clamp(70px, 18vw, 96px);  text-align:center; }
  .c-date { width:clamp(120px, 28vw, 150px); text-align:center; }

  /* garis halus */
  .rmodal-table tbody tr{ border-bottom:1px solid #e5e7eb; }
  .rmodal-table tbody tr:hover td{ background:#f9fafb; }

  /* Tablet */
  @media (max-width:768px){
    #modalCardREC{ width:94vw; }
  }
  /* Mobile */
  @media (max-width:640px){
    #modalCardREC{ width:94vw; max-height:82vh; font-size:12px; }
    .rmodal-table th, .rmodal-table td{ padding:.45rem .5rem; }
  }
  /* HP kecil sekali */
  @media (max-width:380px){
    #modalCardREC{ width:96vw; }
    .rmodal-table{ font-size:11px; }
  }
</style>

<script>
  const nfID = new Intl.NumberFormat('id-ID');
  const fmt  = n => nfID.format(Number(n||0));
  const num  = v => Number(v||0);
  const kodeNum = v => Number(String(v??'').replace(/\D/g,'')||0);

  let recoveryDataRaw = [], recoveryTotal = null;
  let sortState = { column:null, direction: 1 };
  let abortCtrl;
  let currentFilter = { closing:'', harian:'' };

  /* ===== Sticky & scroller ===== */
  function setRecSticky(){
    const h   = document.getElementById('recHead1')?.offsetHeight || 40;
    const tot = document.querySelector('#tabelRecovery tr.sticky-total')?.offsetHeight || 36;
    const holder = document.getElementById('recScroller');
    holder.style.setProperty('--rec_head', h + 'px');
    holder.style.setProperty('--rec_totalH', tot + 'px');
  }
  function sizeRecScroller(){
    const wrap = document.getElementById('recScroller');
    if(!wrap) return;
    const rectTop = wrap.getBoundingClientRect().top;
    wrap.style.height = Math.max(260, window.innerHeight - rectTop - 8) + 'px';
  }
  window.addEventListener('resize', ()=>{ setRecSticky(); sizeRecScroller(); });

  /* ===== INIT ===== */
  (async () => {
    const d = await getLastHarianData();
    if(!d) return;
    closing_date_recovery.value = d.last_closing;
    harian_date_recovery.value  = d.last_created;
    currentFilter = { closing:d.last_closing, harian:d.last_created };
    fetchRecoveryData(d.last_closing, d.last_created);
    setRecSticky(); sizeRecScroller();
  })();

  async function getLastHarianData(){
    try{ const r = await fetch('./api/date/'); const j = await r.json(); return j.data||null; }
    catch{ return null; }
  }

  formFilterRecovery.addEventListener('submit', e=>{
    e.preventDefault();
    const closing = closing_date_recovery.value;
    const harian  = harian_date_recovery.value;
    currentFilter = { closing, harian };
    sortState = { column:null, direction:1 };
    fetchRecoveryData(closing, harian);
  });

  async function fetchRecoveryData(closing_date, harian_date){
    loadingRecovery.classList.remove('hidden');
    if(abortCtrl) abortCtrl.abort();
    abortCtrl = new AbortController();

    recoveryTotalRow.innerHTML = `<tr><td colspan="8" class="px-4 py-3 text-gray-500">Memuat...</td></tr>`;
    recoveryBody.innerHTML = '';

    try{
      const res = await fetch('./api/npl/', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ type:'Recovery NPL', closing_date, harian_date }),
        signal: abortCtrl.signal
      });
      const json = await res.json();
      const data = Array.isArray(json.data) ? json.data : [];

      recoveryTotal = data.find(d => (d.nama_kantor||'').toUpperCase().includes('TOTAL')) || null;
      recoveryDataRaw = data.filter(d => !(d.nama_kantor||'').toUpperCase().includes('TOTAL'));

      recoveryDataRaw.sort((a,b)=> kodeNum(a.kode_cabang) - kodeNum(b.kode_cabang));
      renderAll(recoveryDataRaw);
    }catch(err){
      if(err.name!=='AbortError')
        recoveryTotalRow.innerHTML = `<tr><td colspan="8" class="px-4 py-3 text-red-600">Gagal memuat data</td></tr>`;
    }finally{
      loadingRecovery.classList.add('hidden');
    }
  }

  function renderAll(rows){
    const tot = recoveryTotal;
    const totNoa  = num(tot?.noa_lunas) + num(tot?.noa_backflow);
    const totBaki = num(tot?.baki_debet_lunas) + num(tot?.baki_debet_backflow);

    recoveryTotalRow.innerHTML = tot ? `
      <tr class="sticky-total font-semibold text-sm">
        <td class="px-4 py-2 freeze-1 col1 col-kode"></td>
        <td class="px-4 py-2 freeze-2 col2 col-nama"><span class="ttl-mobile">TOTAL</span></td>
        <td class="px-4 py-2 text-right text-blue-800">${fmt(tot.noa_lunas)}</td>
        <td class="px-4 py-2 text-right text-blue-800">${fmt(tot.baki_debet_lunas)}</td>
        <td class="px-4 py-2 text-right text-blue-800">${fmt(tot.noa_backflow)}</td>
        <td class="px-4 py-2 text-right text-blue-800">${fmt(tot.baki_debet_backflow)}</td>
        <td class="px-4 py-2 text-right text-blue-800">${fmt(totNoa)}</td>
        <td class="px-4 py-2 text-right text-blue-800">${fmt(totBaki)}</td>
      </tr>` : '';

    recoveryBody.innerHTML = rows.map(r=>{
      const total_noa  = num(r.noa_lunas) + num(r.noa_backflow);
      const total_baki = num(r.baki_debet_lunas) + num(r.baki_debet_backflow);
      const kode = r.kode_cabang || '-';
      const nama = r.nama_kantor || '-';
      return `
        <tr class="border-b hover:bg-gray-50">
          <td class="px-4 py-3 text-center freeze-1 col1 col-kode">${String(kode).padStart(3,'0')}</td>
          <td class="px-4 py-3 freeze-2 col2 col-nama">${nama}</td>
          <td class="px-4 py-3 text-right">
            <a href="#" data-type="lunas" data-kode="${kode}" class="text-blue-600 hover:underline">${fmt(r.noa_lunas)}</a>
          </td>
          <td class="px-4 py-3 text-right">${fmt(r.baki_debet_lunas)}</td>
          <td class="px-4 py-3 text-right">
            <a href="#" data-type="backflow" data-kode="${kode}" class="text-blue-600 hover:underline">${fmt(r.noa_backflow)}</a>
          </td>
          <td class="px-4 py-3 text-right">${fmt(r.baki_debet_backflow)}</td>
          <td class="px-4 py-3 text-right">${fmt(total_noa)}</td>
          <td class="px-4 py-3 text-right">${fmt(total_baki)}</td>
        </tr>`;
    }).join('');

    setRecSticky(); sizeRecScroller();
    setTimeout(()=>{ setRecSticky(); sizeRecScroller(); }, 50);
  }

  function sortRowsByState(rows){
    if(!sortState.column)
      return [...rows].sort((a,b)=> kodeNum(a.kode_cabang) - kodeNum(b.kode_cabang));
    return [...rows].sort((a,b)=>{
      const aNoa = num(a.noa_lunas)+num(a.noa_backflow);
      const bNoa = num(b.noa_lunas)+num(b.noa_backflow);
      const aBk  = num(a.baki_debet_lunas)+num(a.baki_debet_backflow);
      const bBk  = num(b.baki_debet_lunas)+num(b.baki_debet_backflow);
      return sortState.column==='total_noa'
        ? sortState.direction*(aNoa-bNoa)
        : sortState.direction*(aBk-bBk);
    });
  }

  sortTotalNoa.addEventListener('click', ()=>{
    sortState = { column:'total_noa', direction: sortState.column==='total_noa' ? -sortState.direction : -1 };
    renderAll(sortRowsByState(recoveryDataRaw));
  });
  sortTotalBaki.addEventListener('click', ()=>{
    sortState = { column:'total_baki', direction: sortState.column==='total_baki' ? -sortState.direction : -1 };
    renderAll(sortRowsByState(recoveryDataRaw));
  });

  /* ===== Modal ===== */
  tabelRecovery.addEventListener('click', e=>{
    const a = e.target.closest('a[data-type]');
    if(!a) return;
    e.preventDefault();
    loadDebiturRecovery(a.getAttribute('data-type'), a.getAttribute('data-kode'),
                        currentFilter.closing, currentFilter.harian);
  });

  btnCloseRecovery.onclick = ()=> closeRecModal();
  function closeRecModal(){
    modalDebiturRecovery.classList.add('hidden');
    modalDebiturRecovery.classList.remove('flex');
  }

function loadDebiturRecovery(type, kodeKantor, closingDate, harianDate){
    const overlay = document.getElementById('modalDebiturRecovery');
    const title   = document.getElementById('modalTitleRecovery');
    const body    = document.getElementById('modalBodyRecovery');

    overlay.classList.remove('hidden'); overlay.classList.add('flex');
    title.textContent = `Debitur ${type==='lunas'?'Lunas':'Backflow'} — Kode Kantor ${String(kodeKantor).padStart(3,'0')}`;
    body.innerHTML = `<p class="text-sm text-gray-500">Memuat data debitur...</p>`;

    fetch('./api/npl/', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ type, kode_kantor:kodeKantor, closing_date:closingDate, harian_date:harianDate })
    })
    .then(r=>r.json())
    .then(res=>{
      const list = Array.isArray(res.data) ? res.data : [];
      if(!list.length){ body.innerHTML = `<p class="text-red-600 font-semibold">Tidak ada data debitur.</p>`; return; }

      const rows = list.map(d=>`
        <tr>
          <td class="px-3 py-2">${d.no_rekening ?? '-'}</td>
          <td class="px-3 py-2">${d.nama_nasabah ?? '-'}</td>
          <td class="px-3 py-2 text-right">${new Intl.NumberFormat('id-ID').format(+d.baki_debet||0)}</td>
          <td class="px-3 py-2 text-center">${d.kolek ?? '-'}</td>
          <td class="px-3 py-2 text-center">${d.kolek_update ?? '-'}</td>
          <td class="px-3 py-2 text-right">${d.angsuran_pokok ? new Intl.NumberFormat('id-ID').format(+d.angsuran_pokok) : '-'}</td>
          <td class="px-3 py-2 text-right">${d.angsuran_bunga ? new Intl.NumberFormat('id-ID').format(+d.angsuran_bunga) : '-'}</td>
          <td class="px-3 py-2 text-right">${d.angsuran_denda ? new Intl.NumberFormat('id-ID').format(+d.angsuran_denda) : '-'}</td>
          <td class="px-3 py-2 text-center">${d.tgl_trans ? formatTanggal(d.tgl_trans) : '-'}</td>
        </tr>
      `).join('');

      body.innerHTML = `
        <table class="rmodal-table w-full text-sm text-left text-gray-800 bg-white rounded shadow min-w-[720px]">
          <colgroup>
            <col class="c-rek"><col class="c-nama"><col class="c-rp"><col class="c-kol"><col class="c-date">
            <col class="c-rp"><col class="c-rp"><col class="c-rp"><col class="c-date">
          </colgroup>
          <thead>
            <tr>
              <th>No Rekening</th>
              <th>Nama Nasabah</th>
              <th>Baki Debet</th>
              <th>Kolek</th>
              <th>Kolek Update</th>
              <th>Angs. Pokok</th>
              <th>Angs. Bunga</th>
              <th>Angs. Denda</th>
              <th>Tgl Bayar</th>
            </tr>
          </thead>
          <tbody>${rows}</tbody>
        </table>`;
    })
    .catch(()=> body.innerHTML = `<p class="text-red-600">Gagal mengambil data debitur.</p>`);

    // Tutup
    const close = ()=>{ overlay.classList.add('hidden'); overlay.classList.remove('flex'); };
    document.getElementById('btnCloseRecovery').onclick = close;
    overlay.onclick = (e)=>{ if(!e.target.closest('#modalCardREC')) close(); };
    const onEsc = (e)=>{ if(e.key==='Escape'){ close(); document.removeEventListener('keydown', onEsc); } };
    document.addEventListener('keydown', onEsc);
  }

  function formatTanggal(tgl){
    const d = new Date(tgl); if(isNaN(d)) return '-';
    return `${String(d.getDate()).padStart(2,'0')}-${String(d.getMonth()+1).padStart(2,'0')}-${d.getFullYear()}`;
  }
</script>
