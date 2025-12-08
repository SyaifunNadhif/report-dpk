<!-- 📊 FLOW PAR — rekap (tetap) + modal sederhana ala Recovery PH -->
<div class="max-w-7xl mx-auto px-4 py-6 h-screen flex flex-col">
  <div class="hdr flex flex-wrap items-start gap-2 mb-3">
    <h1 class="title text-2xl font-bold flex items-center gap-2">
      <span>📊</span><span>Rekap Flow PAR</span>
    </h1>

    <!-- Filter -->
    <form id="filterForm" class="ml-auto">
      <div id="filterFP" class="flex items-center gap-2">
        <label for="closing_date" class="lbl text-sm text-slate-700">Closing:</label>
        <input type="date" id="closing_date" class="inp" required>
        <label for="harian_date" class="lbl text-sm text-slate-700">Harian:</label>
        <input type="date" id="harian_date" class="inp" required>
        <button type="submit" id="btnFilterFP" class="btn-icon" title="Terapkan">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="7"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
        </button>
      </div>
    </form>
  </div>

  <!-- SCROLLER TABEL REKAP -->
  <div id="fpScroller" class="flex-1 min-h-0 overflow-hidden rounded border border-gray-200 bg-white">
    <div class="h-full overflow-auto">
      <table id="tabelFlowPar" class="min-w-full text-sm text-left text-gray-700">
        <thead class="uppercase">
          <tr id="fpHead1" class="text-xs">
            <th class="px-4 py-2 sticky-fp freeze-1 col1 col-kode sortable" data-key="kode_cabang" data-type="text">Kode Kantor</th>
            <th class="px-4 py-2 sticky-fp freeze-2 col2 col-nama  sortable" data-key="nama_kantor" data-type="text">Nama Kantor</th>
            <th class="px-3 py-2 text-center sticky-fp col-noa sortable" data-key="noa_flow" data-type="num">NOA</th>
            <th class="pl-3 pr-8 md:pr-10 py-2 text-right sticky-fp col-bd sortable" data-key="baki_debet_flow" data-type="num">Baki Debet</th>
          </tr>
        </thead>
        <tbody id="fpTotalRow"></tbody>
        <tbody id="fpBody"></tbody>
      </table>
    </div>
  </div>
</div>

<!-- =============== MODAL SEDERHANA (meniru Recovery PH) =============== -->
<div id="modalDebiturFlowPar"
     class="fixed inset-0 hidden bg-gray-900/55 backdrop-blur-sm items-center justify-center"
     style="z-index:100000;">
  <div id="modalCardFP"
       class="bg-white rounded-lg shadow max-w-6xl w-[96vw] sm:w-[92vw] md:w-[1100px] max-h-[90vh] overflow-hidden">
    <div class="flex items-center justify-between p-4 border-b">
      <h3 id="modalTitleFlowPar" class="modal-title">Daftar Debitur Flow PAR</h3>
      <div class="flex items-center gap-2">
        <a href="update_flowpar"
           onclick="storeFlowParData()"
           class="bg-blue-600 text-white px-3 py-2 rounded text-xs sm:text-sm hover:bg-blue-700">Update Progres</a>
        <button id="btnCloseFP" class="text-gray-500 hover:text-gray-700 text-xl" aria-label="Tutup">✕</button>
      </div>
    </div>

    <!-- Body: p-4 biasa; scroll dipindah ke #modalScroll agar sticky bekerja -->
    <div id="modalBody" class="p-4">
      <!-- Kontainer scroll gabungan (X + Y) -->
      <div id="modalScroll" class="overflow-auto max-h-[72vh]">
        <table id="modalTableFP" class="w-full text-sm text-left text-gray-800 bg-white rounded">
          <thead id="modalHeadFP" class="bg-gray-100 text-gray-700">
            <tr>
              <!-- Freeze 2 kolom kiri di HEADER -->
              <th class="px-4 py-2 col-norek freeze-1 sortable" data-key="no_rekening" data-type="text">No Rekening</th>
              <th class="px-4 py-2 col-nama  freeze-2 sortable" data-key="nama_nasabah" data-type="text">Nama Nasabah</th>
              <th class="px-4 py-2 text-right col-bd sortable" data-key="baki_debet" data-type="num">Baki Debet</th>
              <th class="px-4 py-2 text-right col-tp sortable" data-key="tunggakan_pokok" data-type="num">Tunggakan Pokok</th>
              <th class="px-4 py-2 text-right col-tb sortable" data-key="tunggakan_bunga" data-type="num">Tunggakan Bunga</th>
              <th class="px-4 py-2 text-right col-sa sortable" data-key="saldo_akhir" data-type="num">Saldo Tab.</th>
              <th class="px-4 py-2 text-center col-jt sortable" data-key="tgl_jatuh_tempo" data-type="date">JT</th>
              <th class="px-4 py-2 text-center col-hari sortable" data-key="hari_menunggak" data-type="num">DPD</th>
              <!-- ✅ Kolom baru -->
              <th class="px-4 py-2 text-center col-dpdtp sortable" data-key="hari_menunggak_pokok" data-type="num">DPD TP</th>
              <th class="px-4 py-2 text-center col-dpdtb sortable" data-key="hari_menunggak_bunga" data-type="num">DPD TB</th>
              <!-- ===== -->
              <th class="px-4 py-2 text-right col-ap sortable" data-key="angsuran_pokok" data-type="num">Angs. Pokok</th>
              <th class="px-4 py-2 text-right col-ab sortable" data-key="angsuran_bunga" data-type="num">Angs. Bunga</th>
              <th class="px-4 py-2 col-tgl sortable" data-key="tgl_trans" data-type="date">Tgl Transaksi</th>
              <th class="px-4 py-2 col-komit">Komitmen</th>
            </tr>
          </thead>
          <!-- TOTAL sticky (biru) tepat di bawah thead -->
          <tbody id="modalTotalRow"></tbody>
          <!-- DATA -->
          <tbody id="modalBodyRows">
            <tr><td class="px-4 py-2 text-gray-500" colspan="14">Mengambil data debitur...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<style>
/* ===== Gutters TBODY desktop/tablet (mobile tetap) ===== */
@media (min-width: 641px){

  /* Rekap (tabel utama) */
  #tabelFlowPar tbody#fpBody tr > td:first-child,
  #tabelFlowPar tbody#fpTotalRow tr > td:first-child{
    padding-left: 1rem;
  }
  #tabelFlowPar tbody#fpBody tr > td:last-child,
  #tabelFlowPar tbody#fpTotalRow tr > td:last-child{
    padding-right: 1.1rem;
  }
  #tabelFlowPar tbody#fpBody tr > td{
    padding-top: 0.66rem;
    padding-bottom: 0.66rem;
  }

  /* Modal (daftar debitur) */
  #modalTableFP tbody#modalBodyRows tr > td:first-child,
  #modalTableFP tbody#modalTotalRow tr > td:first-child{
    padding-left: 1rem;
  }
  #modalTableFP tbody#modalBodyRows tr > td:last-child,
  #modalTableFP tbody#modalTotalRow tr > td:last-child{
    padding-right: 1rem;
  }
  #modalTableFP tbody#modalBodyRows tr > td{
    padding-top: 0.6rem;
    padding-bottom: 0.6rem;
  }
}

/* Kosmetik halus */
@media (min-width: 1024px){
  #fpScroller .h-full{
    box-shadow: inset 0 0 0 1px rgba(0,0,0,.03);
    border-radius: .5rem;
  }
}

/* ==== Lebar kolom & spacing rekap ==== */
#fpScroller{ --col2: 14rem; --colNum: 8.5rem; }
#tabelFlowPar .col2, #tabelFlowPar th.col-nama, #tabelFlowPar td.col-nama{
  width: var(--col2); min-width: var(--col2); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
@media (min-width: 641px){
  #tabelFlowPar .col-noa{ width: var(--colNum); min-width: var(--colNum); }
  #tabelFlowPar .col-bd { width: var(--colNum); min-width: var(--colNum); }
}
@media (max-width:1024px){ #fpScroller{ --col2: 11.5rem; } }
@media (max-width:640px){ #fpScroller{ --col2: 9.25rem; } }

/* kontrol */
.inp{ border:1px solid #cbd5e1; border-radius:.6rem; padding:.5rem .75rem; font-size:14px; }
.btn-icon{ width:42px; height:42px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center;
           background:#2563eb; color:#fff; box-shadow:0 6px 14px rgba(37,99,235,.25); }
.btn-icon:hover{ background:#1e40af; }

.hdr{ row-gap:.5rem; }
@media (max-width:640px){
  .title{ font-size:1.25rem; }
  .hdr{ flex-direction:column; align-items:flex-start; }
  #filterFP{ width:100%; gap:.5rem; }
  .lbl{ display:none; }
  .inp{ flex:1 1 0; min-width:0; font-size:13px; padding:.45rem .6rem; }
  .btn-icon{ width:40px; height:40px; }
}

/* rekap sticky left + header */
body{ overflow:hidden; }
#fpScroller{ --col1:6rem; --headH:40px; --totalH:36px; --safe:40px; }
#tabelFlowPar .col1{ width:var(--col1); min-width:var(--col1); }
#tabelFlowPar .col2{ width:var(--col2); min-width:var(--col2); }
#tabelFlowPar .freeze-1{ position:sticky; left:0; z-index:41; box-shadow:1px 0 0 rgba(0,0,0,.06); }
#tabelFlowPar .freeze-2{ position:sticky; left:var(--col1); z-index:40; box-shadow:1px 0 0 rgba(0,0,0,.06); }
#tabelFlowPar thead th{ position:sticky; top:0; background:#d9ead3; z-index:88; }
#tabelFlowPar thead th.freeze-1{ left:0; z-index:91 !important; }
#tabelFlowPar thead th.freeze-2{ left:var(--col1); z-index:90 !important; }

#fpTotalRow tr.total-row td{ position:sticky; top:var(--headH); background:#eaf2ff; color:#1e40af; border-bottom:1px solid #c7d2fe; z-index:70; }
#fpTotalRow tr.total-row td.freeze-1{ z-index:91; }
#fpTotalRow tr.total-row td.freeze-2{ z-index:90; }

#tabelFlowPar tbody tr:hover td{ background:#f9fafb; }
#tabelFlowPar #fpBody::after{ content:""; display:block; height: calc(var(--headH) + var(--totalH) + var(--safe)); }

/* Padding umum rekap */
#tabelFlowPar th, #tabelFlowPar td{ padding: calc(.5rem - 2px) calc(.6rem - 2px); }
@media (max-width:640px){
  #tabelFlowPar{ font-size:12px; }
  #tabelFlowPar thead th{ font-size:11px; }
  #tabelFlowPar th, #tabelFlowPar td{ padding:.5rem .6rem; }
  #tabelFlowPar th.col-kode, #tabelFlowPar td.col-kode{ display:none; }
  #fpScroller{ --col1:0px; }
  #tabelFlowPar .freeze-2, #tabelFlowPar thead th.freeze-2{ left:0 !important; }
}

/* ===== MODAL TABLE: STICKY HEADER & TOTAL (BIRU) + FREEZE KIRI ===== */
#modalScroll{ --modalHeadH: 44px; --colRek: 9rem; --colNama: 12rem; }

#modalTableFP{
  border-collapse:separate;
  border-spacing:0;
  table-layout:fixed;
  width:100%;
  min-width:1590px; /* tambah 2 kolom DPD TP/TB */
}
#modalTableFP th, #modalTableFP td{
  padding: calc(.55rem - 2px) calc(.7rem - 2px);
  border-bottom:1px solid #eef2f7;
  white-space:nowrap;
  background:#ffffff;
}
#modalTableFP td.text-right, #modalTableFP th.text-right{ text-align:right; }

/* Lebar per-kolom (modal) */
#modalTableFP .col-norek { width: var(--colRek);  min-width: var(--colRek);  }
#modalTableFP .col-nama  { width: var(--colNama); min-width: var(--colNama); overflow:hidden; text-overflow:ellipsis; }
#modalTableFP .col-bd,
#modalTableFP .col-tp,
#modalTableFP .col-tb,
#modalTableFP .col-ap,
#modalTableFP .col-ab,
#modalTableFP .col-sa    { width: 10rem; min-width: 10rem; }
#modalTableFP .col-jt    { width: 6.5rem; min-width: 6.5rem; text-align:center; }
#modalTableFP .col-hari  { width: 7rem;  min-width: 7rem;  text-align:center; }
#modalTableFP .col-dpdtp { width: 7rem;  min-width: 7rem;  text-align:center; } /* NEW */
#modalTableFP .col-dpdtb { width: 7rem;  min-width: 7rem;  text-align:center; } /* NEW */
#modalTableFP .col-tgl   { width: 9rem;  min-width: 9rem;  }
#modalTableFP .col-komit { width: 14rem; min-width: 14rem; overflow:hidden; text-overflow:ellipsis; }

/* Sticky header modal */
#modalTableFP thead th{
  position: sticky;
  top: 0;
  z-index: 31;
  background: #f1f59;
  border-bottom: 1px solid #e2e8f0;
}

/* Sort style (rekap & modal) */
#tabelFlowPar thead th.sortable,
#modalTableFP thead th.sortable{ cursor:pointer; user-select:none; }
#tabelFlowPar thead th.sortable::after,
#modalTableFP thead th.sortable::after{
  content:""; margin-left:.4rem; display:inline-block; width:0; height:0; border-left:4px solid transparent; border-right:4px solid transparent;
  border-top:6px solid #64748b; opacity:.7; transform: translateY(2px);
}
#tabelFlowPar thead th.sortable.sorted-asc::after,
#modalTableFP thead th.sortable.sorted-asc::after{ border-top-color:#1e40af; transform: rotate(180deg) translateY(-2px); opacity:1; }
#tabelFlowPar thead th.sortable.sorted-desc::after,
#modalTableFP thead th.sortable.sorted-desc::after{ border-top-color:#1e40af; opacity:1; }

/* Freeze kolom 1 & 2 modal */
#modalTableFP .freeze-1{ position: sticky; left: 0; z-index: 41; box-shadow: 1px 0 0 rgba(0,0,0,.06); }
#modalTableFP .freeze-2{ position: sticky; left: var(--colRek); z-index: 40; box-shadow: 1px 0 0 rgba(0,0,0,.06); }
#modalTableFP thead th.freeze-1{ z-index: 51; }
#modalTableFP thead th.freeze-2{ z-index: 50; }

/* Sticky baris TOTAL modal */
#modalTotalRow tr.total-row td{
  position: sticky;
  top: var(--modalHeadH);
  z-index: 30;
  background: #eaf2ff;
  color: #1e40af;
  border-bottom: 1px solid #c7d2fe;
  font-weight: 600;
}
#modalTotalRow tr.total-row td.freeze-1{ z-index: 51; }
#modalTotalRow tr.total-row td.freeze-2{ z-index: 50; }

/* Hover & overdue tint (row) */
#modalTableFP tbody#modalBodyRows tr:hover td{ background:#f9fafb; }
#modalTableFP tbody#modalBodyRows tr.overdue td{ background:#fee2e2; }

/* Highlight per-sel untuk DPD TP/TB >= 90 */
#modalTableFP td.hot90{ background:#fee2e2 !important; }

@media (max-width:640px){
  #modalCardFP{ width:92vw; max-height:80vh; }
  #modalScroll{ max-height:72vh; --colRek: 0rem; --colNama: 8rem; }
  #modalTableFP{ min-width:1180px; } /* lebih kecil tapi tetap muat kolom baru lewat scroll X */
  #modalTableFP th, #modalTableFP td{ padding:.42rem .5rem; }
  #modalTableFP .col-norek{ display:none; }
  #modalTableFP .freeze-2{ left: 0 !important; }
  #modalTableFP .col-bd,
  #modalTableFP .col-tp,
  #modalTableFP .col-tb,
  #modalTableFP .col-ap,
  #modalTableFP .col-ab,
  #modalTableFP .col-sa { width: 7.5rem; min-width: 7.5rem; }
  #modalTableFP .col-jt,
  #modalTableFP .col-hari,
  #modalTableFP .col-dpdtp,
  #modalTableFP .col-dpdtb { width: 5.5rem; min-width: 5.5rem; }
  #modalTableFP .col-tgl  { width: 7rem;  min-width: 7rem;  }
  #modalTableFP .col-komit{ width: 7rem;  min-width: 7rem;  }
}
</style>

<script>
/* ===== Helpers ===== */
const fmtNom = n => new Intl.NumberFormat("id-ID").format(+n||0);
const fmtInt = n => new Intl.NumberFormat("id-ID",{maximumFractionDigits:0}).format(+n||0);
const esc     = s => String(s??'').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'","&#39;");
const escAttr = s => String(s??'').replaceAll('"','&quot;').replaceAll("'","&#39;");
const num = v => Number(v||0);

function startOfDay(d){ const x=new Date(d); x.setHours(0,0,0,0); return x; }
function endOfMonth(dateLike){
  const d = new Date(dateLike);
  if (isNaN(d)) return null;
  return startOfDay(new Date(d.getFullYear(), d.getMonth()+1, 0));
}
function isSameMonth(dateLike, refDate){
  const d = new Date(dateLike); if(isNaN(d)) return false;
  return d.getMonth()===refDate.getMonth() && d.getFullYear()===refDate.getFullYear();
}

/* JT display rule */
function formatJTByRule(jt){
  if(!jt) return '-';
  const d = new Date(jt); if(isNaN(d)) return '-';
  const today = startOfDay(new Date());
  const due   = startOfDay(d);
  if(due < today){
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth()+1).padStart(2,'0');
    const dd = String(d.getDate()).padStart(2,'0');
    return `${yyyy}-${mm}-${dd}`;
  }
  return String(d.getDate());
}

/* Hari Menunggak (fallback bila API kosong) */
function calcHariMenunggak(jt){
  if(!jt) return 0;
  const d = new Date(jt); if(isNaN(d)) return 0;
  const today = startOfDay(new Date());
  const due   = startOfDay(d);
  const days = Math.floor((today - due) / 86400000);
  return days > 0 ? days : 0;
}

/* ===== Sticky rekap ===== */
function setFPSticky(){
  const h = document.getElementById('fpHead1')?.offsetHeight || 40;
  const holder = document.getElementById('fpScroller');
  holder.style.setProperty('--headH', h + 'px');
  const totH = document.querySelector('#fpTotalRow tr')?.offsetHeight || 36;
  holder.style.setProperty('--totalH', totH + 'px');
}
function sizeFPScroller(){
  const wrap = document.getElementById('fpScroller');
  const rectTop = wrap.getBoundingClientRect().top;
  wrap.style.height = Math.max(260, window.innerHeight - rectTop - 18) + 'px';
}
window.addEventListener('resize', ()=>{ setFPSticky(); sizeFPScroller(); });

/* ===== Default tanggal (ambil dari API) ===== */
(async ()=>{
  try{
    const res = await fetch('./api/flow_par/', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ type:'Last Created Nominatif' })
    });
    const j = await res.json();
    if(j?.data){
      closing_date.value = j.data.last_closing;
      harian_date.value  = j.data.last_created;
      fetchFlowPar(j.data.last_closing, j.data.last_created);
    }
  }catch{}
})();

/* ===== Filter ===== */
document.getElementById("filterForm").addEventListener("submit", (e)=>{
  e.preventDefault();
  fetchFlowPar(closing_date.value, harian_date.value);
});

/* ===== State rekap (untuk sort) ===== */
let __FP_REKAP_ROWS__ = [];
let __FP_REKAP_TOTAL__ = null;
let __FP_REKAP_SORT__  = null; // {key, dir:'asc'|'desc', type}

/* Render body rekap (dengan sort) */
function renderRekapBody(){
  const tBody  = document.getElementById("fpBody");
  const rows = [...__FP_REKAP_ROWS__];

  if(__FP_REKAP_SORT__){
    const {key, dir, type} = __FP_REKAP_SORT__;
    const toVal = (d)=>{
      if(type==='num')  return num(d[key]);
      if(type==='date'){ const t=d[key]?new Date(d[key]).getTime():NaN; return isNaN(t)?-8640000000000000:t; }
      return String(d[key] ?? '').toLowerCase();
    };
    rows.sort((a,b)=>{
      const va = toVal(a), vb = toVal(b);
      if(va<vb) return dir==='asc' ? -1 : 1;
      if(va>vb) return dir==='asc' ? 1 : -1;
      return 0;
    });
  }

  tBody.innerHTML = rows.map(d=>`
    <tr class="border-b">
      <td class="px-4 py-3 freeze-1 col1 col-kode">${esc(String(d.kode_cabang??'').padStart(3,'0'))}</td>
      <td class="px-4 py-3 freeze-2 col2 col-nama" title="${esc(d.nama_kantor)}">${esc(d.nama_kantor)}</td>
      <td class="px-3 py-3 text-center col-noa">
        <a href="#" class="text-blue-600 hover:underline"
           onclick="event.preventDefault(); loadDebiturFlowPar('${escAttr(d.kode_cabang)}', '${escAttr(document.getElementById('closing_date')?.value||'')}', '${escAttr(document.getElementById('harian_date')?.value||'')}')">
           ${fmtInt(d.noa_flow)}
        </a>
      </td>
      <td class="pl-3 pr-8 md:pr-10 py-3 text-right col-bd">${fmtNom(d.baki_debet_flow)}</td>
    </tr>
  `).join('') || `<tr><td colspan="4" class="px-4 py-3 text-red-600">Tidak ada data.</td></tr>`;
}

/* Pasang handler sort rekap */
function attachRekapSortHandlers(){
  const ths = document.querySelectorAll('#tabelFlowPar thead th.sortable');
  ths.forEach(th=>{
    th.addEventListener('click', ()=>{
      const key  = th.dataset.key;
      const type = th.dataset.type || 'text';

      // reset kelas panah
      ths.forEach(x=>x.classList.remove('sorted-asc','sorted-desc'));

      if(__FP_REKAP_SORT__ && __FP_REKAP_SORT__.key===key){
        __FP_REKAP_SORT__.dir = (__FP_REKAP_SORT__.dir==='asc'?'desc':'asc');
      }else{
        __FP_REKAP_SORT__ = {key, dir:'asc', type};
      }

      th.classList.add(__FP_REKAP_SORT__.dir==='asc' ? 'sorted-asc' : 'sorted-desc');
      renderRekapBody();
    });
  });
}

/* ===== Fetch data rekap ===== */
let fpAbort=null;
function fetchFlowPar(closing_date, harian_date){
  if(fpAbort) fpAbort.abort();
  fpAbort = new AbortController();

  const tTotal = document.getElementById("fpTotalRow");
  const tBody  = document.getElementById("fpBody");
  tTotal.innerHTML = '';
  tBody.innerHTML  = `<tr><td colspan="4" class="px-4 py-3 text-gray-500">Memuat...</td></tr>`;

  fetch("./api/flow_par/", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ type: "Flow Par", closing_date, harian_date }),
    signal: fpAbort.signal
  })
  .then(r=>r.json())
  .then(res=>{
    const data = Array.isArray(res.data) ? res.data : [];
    const isTotal = d => (String(d.nama_kantor||'').toUpperCase().includes('TOTAL') ||
                          String(d.kode_cabang||'').toUpperCase()==='TOTAL');
    __FP_REKAP_TOTAL__ = data.find(isTotal) || null;
    __FP_REKAP_ROWS__  = data.filter(d => !isTotal(d));

    // render total tetap dulu
    if(__FP_REKAP_TOTAL__){
      tTotal.innerHTML = `
        <tr class="total-row font-semibold text-sm">
          <td class="px-4 py-2 freeze-1 col1 col-kode">TOTAL</td>
          <td class="px-4 py-2 freeze-2 col2 col-nama">TOTAL</td>
          <td class="px-3 py-2 text-center col-noa">${fmtInt(__FP_REKAP_TOTAL__.noa_flow)}</td>
          <td class="pl-3 pr-8 md:pr-10 py-2 text-right col-bd">${fmtNom(__FP_REKAP_TOTAL__.baki_debet_flow)}</td>
        </tr>`;
    }else{
      tTotal.innerHTML = '';
    }

    // render body
    renderRekapBody();

    setFPSticky(); sizeFPScroller();
    setTimeout(()=>{ setFPSticky(); sizeFPScroller(); }, 50);
  })
  .catch(err=>{
    if(err.name!=='AbortError'){
      tTotal.innerHTML = '';
      tBody.innerHTML  = `<tr><td colspan="4" class="px-4 py-3 text-red-600">Gagal memuat data.</td></tr>`;
    }
  });
}

/* ===== Modal (data + sort + merah JT<=EOM + DPD TP/TB) ===== */
let FP_ACTIVE_KODE = null;
let __FP_LIST__ = [];      // data mentah dari API (sudah diperkaya)
let __FP_SORT__ = null;    // {key, dir:'asc'|'desc'}

/* ► Pakai hari_menunggak dari API bila tersedia; jika kosong, fallback hitung dari JT */
function enhanceList(raw){
  return raw.map(d=>{
    const hasApiHari = (d.hari_menunggak ?? d.hari_menunggak === 0);
    let hari = hasApiHari ? Number(d.hari_menunggak) : calcHariMenunggak(d.tgl_jatuh_tempo);
    if(!Number.isFinite(hari)) hari = 0;
    if(hari < 0) hari = 0;
    return {...d, hari_menunggak: hari};
  });
}

function openModalFlowPar(){
  const o=document.getElementById("modalDebiturFlowPar");
  o.classList.remove("hidden");
  o.classList.add("items-center","justify-center","flex");
  setTimeout(setModalSticky, 0);
}
function closeModalFlowPar(){
  const o=document.getElementById("modalDebiturFlowPar");
  o.classList.add("hidden");
  o.classList.remove("flex");
}
document.getElementById("btnCloseFP").onclick = closeModalFlowPar;
document.getElementById("modalDebiturFlowPar").onclick = (e)=>{ if(!e.target.closest('#modalCardFP')) closeModalFlowPar(); };
document.addEventListener('keydown', (e)=>{ if(e.key==='Escape' && !document.getElementById("modalDebiturFlowPar").classList.contains('hidden')) closeModalFlowPar(); });

function setModalSticky(){
  const head = document.querySelector('#modalTableFP thead tr');
  const scroll = document.getElementById('modalScroll');
  const h = (head?.offsetHeight || 44);
  if(scroll) scroll.style.setProperty('--modalHeadH', h + 'px');
}
window.addEventListener('resize', setModalSticky);

/* ==== RENDER MODAL DENGAN LOGIKA MERAH: JT <= AKHIR BULAN ACUAN + DPD TP/TB ==== */
function renderModal(list){
  const tbody = document.getElementById("modalBodyRows");
  const ttot  = document.getElementById("modalTotalRow");

  if(!list.length){
    tbody.innerHTML = `<tr><td class="px-4 py-2 text-red-600" colspan="14">Tidak ada data.</td></tr>`;
    ttot.innerHTML = '';
    setModalSticky();
    return;
  }

  // Tentukan bulan acuan dari Harian Date (kalau kosong, pakai hari ini)
  const harianStr = document.getElementById('harian_date')?.value;
  const ref = harianStr ? new Date(harianStr) : new Date();
  const eom = endOfMonth(ref) || endOfMonth(new Date());

  // Totals
  const totals = list.reduce((a,d)=>(Object.assign(a,{
    rows:a.rows+1,
    bd:a.bd+num(d.baki_debet),
    tp:a.tp+num(d.tunggakan_pokok),
    tb:a.tb+num(d.tunggakan_bunga),
    ap:a.ap+num(d.angsuran_pokok),
    ab:a.ab+num(d.angsuran_bunga),
    sa:a.sa+num(d.saldo_akhir)
  })), {rows:0,bd:0,tp:0,tb:0,ap:0,ab:0,sa:0});

  // Rows
  tbody.innerHTML = list.map(d=>{
    const jt = d.tgl_jatuh_tempo ? startOfDay(new Date(d.tgl_jatuh_tempo)) : null;
    const jtDisplay = formatJTByRule(d.tgl_jatuh_tempo);
    const merahBaris = jt && (jt.getTime() <= eom.getTime());

    // DPD TP/TB
    const dpdTP = Number.isFinite(+d.hari_menunggak_pokok) ? +d.hari_menunggak_pokok : 0;
    const dpdTB = Number.isFinite(+d.hari_menunggak_bunga) ? +d.hari_menunggak_bunga : 0;
    const hotTP = dpdTP >= 90 ? 'hot90' : '';
    const hotTB = dpdTB >= 90 ? 'hot90' : '';

    return `
      <tr class="${merahBaris ? 'overdue' : ''}">
        <td class="px-4 py-2 col-norek freeze-1">${esc(d.no_rekening)}</td>
        <td class="px-4 py-2 col-nama  freeze-2" title="${esc(d.nama_nasabah)}">${esc(d.nama_nasabah)}</td>
        <td class="px-4 py-2 text-right col-bd">${fmtNom(d.baki_debet)}</td>
        <td class="px-4 py-2 text-right col-tp">${fmtNom(d.tunggakan_pokok)}</td>
        <td class="px-4 py-2 text-right col-tb">${fmtNom(d.tunggakan_bunga)}</td>
        <td class="px-4 py-2 text-right col-sa"><strong>${fmtNom(d.saldo_akhir)}</strong></td>
        <td class="px-4 py-2 text-center col-jt" title="${esc(d.tgl_jatuh_tempo||'-')}">${jtDisplay}</td>
        <td class="px-4 py-2 text-center col-hari">${fmtInt(d.hari_menunggak)}</td>
        <td class="px-4 py-2 text-center col-dpdtp ${hotTP}">${fmtInt(dpdTP)}</td>
        <td class="px-4 py-2 text-center col-dpdtb ${hotTB}">${fmtInt(dpdTB)}</td>
        <td class="px-4 py-2 text-right col-ap">${fmtNom(d.angsuran_pokok||0)}</td>
        <td class="px-4 py-2 text-right col-ab">${fmtNom(d.angsuran_bunga||0)}</td>
        <td class="px-4 py-2 col-tgl">${d.tgl_trans ? formatTanggal(d.tgl_trans) : '-'}</td>
        <td class="px-4 py-2 col-komit" title="${esc(d.komitmen||'-')}">${esc(d.komitmen || '-')}</td>
      </tr>`;
  }).join('');

  // Total row (sticky)
  ttot.innerHTML = `
    <tr class="total-row">
      <td class="px-4 py-2 freeze-1 col-norek">TOTAL</td>
      <td class="px-4 py-2 freeze-2 col-nama">TOTAL (${totals.rows} debitur)</td>
      <td class="px-4 py-2 text-right col-bd">${fmtNom(totals.bd)}</td>
      <td class="px-4 py-2 text-right col-tp">${fmtNom(totals.tp)}</td>
      <td class="px-4 py-2 text-right col-tb">${fmtNom(totals.tb)}</td>
      <td class="px-4 py-2 text-right col-sa">${fmtNom(totals.sa)}</td>
      <td class="px-4 py-2 col-jt"></td>
      <td class="px-4 py-2 col-hari"></td>
      <td class="px-4 py-2 col-dpdtp"></td>
      <td class="px-4 py-2 col-dpdtb"></td>
      <td class="px-4 py-2 text-right col-ap">${fmtNom(totals.ap)}</td>
      <td class="px-4 py-2 text-right col-ab">${fmtNom(totals.ab)}</td>
      <td class="px-4 py-2 col-tgl"></td>
      <td class="px-4 py-2 col-komit"></td>
    </tr>`;

  setModalSticky();
}

function applySort(){
  if(!__FP_SORT__){ renderModal(__FP_LIST__); return; }
  const {key, dir, type} = __FP_SORT__;
  const list = [...__FP_LIST__];

  const toVal = (d)=>{
    if(type==='num') return num(d[key]);
    if(type==='date'){
      const v = d[key];
      const t = v ? new Date(v).getTime() : NaN;
      return isNaN(t) ? -8640000000000000 : t;
    }
    return String(d[key] ?? '').toLowerCase();
  };

  list.sort((a,b)=>{
    const va = toVal(a), vb = toVal(b);
    if(va<vb) return dir==='asc' ? -1 : 1;
    if(va>vb) return dir==='asc' ? 1 : -1;
    return 0;
  });

  renderModal(list);
}

function attachSortHandlers(){
  const ths = document.querySelectorAll('#modalTableFP thead th.sortable');
  ths.forEach(th=>{
    th.addEventListener('click', ()=>{
      const key = th.dataset.key;
      const type= th.dataset.type || 'text';
      ths.forEach(x=>x.classList.remove('sorted-asc','sorted-desc'));
      if(__FP_SORT__ && __FP_SORT__.key===key){
        __FP_SORT__.dir = (__FP_SORT__.dir==='asc'?'desc':'asc');
      }else{
        __FP_SORT__ = {key, dir:'asc', type};
      }
      th.classList.add(__FP_SORT__.dir==='asc'?'sorted-asc':'sorted-desc');
      applySort();
    });
  });
}

/* ===== Load modal data ===== */
function loadDebiturFlowPar(kodeKantor, closingDate, harianDate){
  FP_ACTIVE_KODE = String(kodeKantor).padStart(3,'0');
  openModalFlowPar();

  document.getElementById("modalTitleFlowPar").textContent = `Daftar Debitur – Kode Kantor ${FP_ACTIVE_KODE}`;
  const tbody = document.getElementById("modalBodyRows");
  const ttot  = document.getElementById("modalTotalRow");
  tbody.innerHTML = `<tr><td class="px-4 py-2 text-gray-500" colspan="14">Mengambil data debitur...</td></tr>`;
  ttot.innerHTML = ``;

  fetch("./api/flow_par/", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ type:"KL Baru", kode_kantor:kodeKantor, closing_date:closingDate, harian_date:harianDate })
  })
  .then(r=>r.json())
  .then(res=>{
    const raw = Array.isArray(res.data) ? res.data : [];
    __FP_LIST__ = enhanceList(raw);
    __FP_SORT__ = null; // reset sort setiap buka modal
    attachSortHandlers();
    applySort(); // render awal (tanpa sort)
  })
  .catch(()=>{
    tbody.innerHTML = `<tr><td class="px-4 py-2 text-red-600" colspan="14">Gagal mengambil data debitur.</td></tr>`;
    setModalSticky();
  });

  // untuk tombol "Update Progres"
  window.__FP_ACTIVE_DATES__ = {
    closing: document.getElementById('closing_date')?.value || '',
    harian : document.getElementById('harian_date')?.value || ''
  };
}
window.loadDebiturFlowPar = loadDebiturFlowPar;

function storeFlowParData(){
  try{
    const dates = window.__FP_ACTIVE_DATES__ || {};
    sessionStorage.setItem('flowpar_update', JSON.stringify({
      kode_kantor: FP_ACTIVE_KODE || '',
      closing_date: dates.closing || '',
      harian_date : dates.harian  || ''
    }));
  }catch{}
}
function formatTanggal(tgl){
  const d = new Date(tgl); if(isNaN(d)) return '-';
  return `${String(d.getDate()).padStart(2,'0')}-${String(d.getMonth()+1).padStart(2,'0')}-${d.getFullYear()}`;
}

/* Inisialisasi sort rekap setelah DOM siap */
document.addEventListener('DOMContentLoaded', attachRekapSortHandlers);
</script>
