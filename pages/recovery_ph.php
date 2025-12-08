<div class="max-w-7xl mx-auto px-4 py-6 h-screen flex flex-col">
  <div class="hdr flex flex-wrap items-start gap-2 mb-3">
    <h1 class="title text-2xl font-bold flex items-center gap-2">
      <span>📊</span><span>Recovery PH</span>
    </h1>

    <!-- Filter -->
    <form id="filterForm" class="ml-auto">
      <div id="filterPH" class="flex items-center gap-2">
        <label for="start_date" class="lbl text-sm text-slate-700">Dari:</label>
        <input type="date" id="start_date" class="inp" required>

        <label for="end_date" class="lbl text-sm text-slate-700">Sampai:</label>
        <input type="date" id="end_date" class="inp" required>

        <!-- tombol ikon bulat -->
        <button type="submit" id="btnFilterPH" class="btn-icon" title="Terapkan">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="7"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
        </button>
      </div>
    </form>
  </div>

  <!-- SCROLLER TABEL -->
  <div id="phScroller" class="flex-1 min-h-0 overflow-hidden rounded border border-gray-200 bg-white">
    <div class="h-full overflow-auto">
      <table id="tabelRecovery" class="min-w-full text-sm text-left text-gray-700">
        <thead class="uppercase">
          <tr id="phHead1" class="text-xs">
            <th class="px-4 py-2 sticky-ph freeze-1 col1 col-kode">Kode Kantor</th>
            <th class="px-4 py-2 sticky-ph freeze-2 col2 col-nama">Nama Kantor</th>
            <th class="px-4 py-2 text-right sticky-ph">Pokok</th>
            <th class="px-4 py-2 text-right sticky-ph">Bunga</th>
            <th class="px-4 py-2 text-right sticky-ph">Total</th>
            <th class="px-4 py-2 text-right sticky-ph">NOA</th>
          </tr>
        </thead>
        <tbody id="tbodyPH"></tbody>
      </table>
    </div>
  </div>
</div>

<!-- MODAL (z-index sangat tinggi) -->
<div id="modalDebitur"
     class="fixed inset-0 hidden bg-gray-900/55 backdrop-blur-sm items-center justify-center"
     style="z-index:100000;">
  <div id="modalCardPH"
       class="bg-white rounded-lg shadow max-w-5xl w-[94vw] sm:w-[90vw] md:w-[860px] max-h-[90vh] overflow-hidden">
    <div class="flex items-center justify-between p-4 border-b">
      <h3 id="modalTitle" class="modal-title">Daftar Debitur</h3>
      <button id="btnClosePH" class="text-gray-500 hover:text-gray-700 text-xl" aria-label="Tutup">✕</button>
    </div>
    <div id="modalBody" class="p-4 overflow-y-auto max-h-[70vh]"></div>
  </div>
</div>

<style>

  /* ==== Lebar kolom 'Nama Kantor' (col2) & potong teks ==== */
#phScroller{ --col2: 14rem; }  /* semula 18rem → 14rem (desktop) */

#tabelRecovery .col2,
#tabelRecovery th.col-nama,
#tabelRecovery td.col-nama{
  width: var(--col2);
  min-width: var(--col2);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;   /* nama panjang dipotong */
}

/* Tablet */
@media (max-width:1024px){
  #phScroller{ --col2: 11.5rem; }
}

/* Mobile */
@media (max-width:640px){
  #phScroller{ --col2: 9.25rem; }  /* makin ramping di HP */
}

  /* === Kontrol & tombol === */
  .inp{ border:1px solid #cbd5e1; border-radius:.6rem; padding:.5rem .75rem; font-size:14px; background:#fff; }
  .btn-icon{ width:42px; height:42px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center;
             background:#2563eb; color:#fff; box-shadow:0 6px 14px rgba(37,99,235,.25); }
  .btn-icon[disabled]{ opacity:.6; cursor:not-allowed; }
  .btn-icon:hover{ background:#1e40af; }

  /* === Header responsif === */
  .hdr{ row-gap:.5rem; }
  @media (max-width:640px){
    .title{ font-size:1.25rem; }
    .hdr{ flex-direction:column; align-items:flex-start; }
    #filterPH{ width:100%; gap:.5rem; }
    .lbl{ display:none; }               /* label disembunyikan */
    .inp{ flex:1 1 0; min-width:0; font-size:13px; padding:.45rem .6rem; }
    .btn-icon{ width:40px; height:40px; }
  }

  /* === TABEL: sticky header + freeze kiri === */
  body{ overflow:hidden; }
  #phScroller{ --col1:6rem; --col2:18rem; --headH:40px; --totalH:36px; --safe:28px; }
  @supports (padding:max(0px)){ #phScroller{ --safe:max(28px, env(safe-area-inset-bottom)); } }

  #tabelRecovery .col1{ width:var(--col1); min-width:var(--col1); }
  #tabelRecovery .col2{ width:var(--col2); min-width:var(--col2); }

  #tabelRecovery .freeze-1{ position:sticky; left:0; z-index:41; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06); }
  #tabelRecovery .freeze-2{ position:sticky; left:var(--col1); z-index:40; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06); }
  #tabelRecovery thead th{ position:sticky; top:0; background:#d9ead3; z-index:88; }
  #tabelRecovery thead th.freeze-1{ left:0; z-index:91 !important; background:#d9ead3; }
  #tabelRecovery thead th.freeze-2{ left:var(--col1); z-index:90 !important; background:#d9ead3; }

  /* TOTAL sticky */
  #tabelRecovery tbody tr.total-row td{
    position:sticky; top:var(--headH); background:#eaf2ff; color:#1e40af; border-bottom:1px solid #c7d2fe; z-index:70;
  }
  #tabelRecovery tbody tr.total-row td.freeze-1{ z-index:91; }
  #tabelRecovery tbody tr.total-row td.freeze-2{ z-index:90; }

  #tabelRecovery tbody tr:hover td{ background:#f9fafb; }

  /* Spacer bawah agar baris terakhir tidak ketutup */
  #tabelRecovery tbody::after{ content:""; display:block; height: calc(var(--headH) + var(--totalH) + var(--safe)); }

  /* === Mobile tweaks === */
  @media (max-width:640px){
    #tabelRecovery{ font-size:12px; }
    #tabelRecovery thead th{ font-size:11px; }
    #tabelRecovery th, #tabelRecovery td{ padding:.5rem .5rem; }

    /* KODE di-hide; BUNGA TETAP TAMPIL */
    #tabelRecovery th.col-kode, #tabelRecovery td.col-kode{ display:none; }
    #phScroller{ --col1:0px; }
    #tabelRecovery .freeze-2, #tabelRecovery thead th.freeze-2{ left:0 !important; }
  }

  /* === Modal typography & ukuran mobile === */
  #modalCardPH{ font-size: clamp(11px, 1.2vw, 14px); }
  #modalCardPH .modal-title{ font-size: clamp(16px, 2.0vw, 20px); font-weight:700; }

  @media (max-width:640px){
    #modalCardPH{ width:92vw; max-height:80vh; }   /* diperkecil di mobile */
    #modalBody{ max-height:72vh; }
  }

  /* ==== MOBILE: rapetin kolom NAMA KANTOR di semua tabel ==== */
  @media (max-width:640px){
    /* lebar kolom nama jadi ~7.75rem, sesuaikan kalau mau lebih kecil */
    #tabelCkpn .col2, #tabelKolektibilitas .col2, #tabelRealisasi .col2, #tabelRecovery .col2,
    #tabelCkpn th.col-nama, #tabelKolektibilitas th.col-nama, #tabelRealisasi th.col-nama, #tabelRecovery th.col-nama,
    #tabelCkpn td.col-nama, #tabelKolektibilitas td.col-nama, #tabelRealisasi td.col-nama, #tabelRecovery td.col-nama{
      width: 7.75rem;
      min-width: 7.75rem;
      max-width: 7.75rem;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;    /* nama panjang dipotong */
      padding-right: .25rem;      /* kasih ruang ke angka kanan */
    }

    /* kalau kolom KODE disembunyikan di mobile, freeze-2 wajib nempel kiri */
    #tabelCkpn .freeze-2, #tabelRealisasi .freeze-2, #tabelRecovery .freeze-2, #tabelKolektibilitas .freeze-2,
    #tabelCkpn thead th.freeze-2, #tabelRealisasi thead th.freeze-2, #tabelRecovery thead th.freeze-2, #tabelKolektibilitas thead th.freeze-2{
      left: 0 !important;
    }
  }

  /* === Modal: versi compact di mobile (<=640px) === */
@media (max-width:640px){
  /* kartu modal yang kita pakai di semua halaman */
  #modalCardPH,
  #modalCardRealisasi,
  #modalDebitur .rounded-lg{             /* fallback untuk modal tanpa ID kartu */
    width: 86vw !important;              /* lebih kecil */
    max-height: 78vh !important;         /* lebih pendek */
    border-radius: 12px !important;
  }

  /* judul & header modal */
  #modalCardPH .modal-title,
  #modalCardRealisasi .modal-title,
  #modalDebitur .modal-title{
    font-size: 14px !important;
    line-height: 1.2;
  }
  #modalCardPH .border-b,
  #modalCardRealisasi .border-b,
  #modalDebitur .border-b{
    padding: .5rem .75rem !important;
  }
  /* tombol close */
  #modalCardPH .text-xl,
  #modalCardRealisasi .text-xl,
  #modalDebitur .text-xl{ font-size: 18px !important; }

  /* body modal */
  #modalCardPH .p-4,
  #modalCardRealisasi .p-4,
  #modalDebitur .p-4{
    padding: .5rem .75rem !important;
  }
  #modalCardPH #modalBody,
  #modalCardRealisasi #modalBodyRealisasi,
  #modalDebitur #modalBody{
    max-height: 72vh !important;         /* ruang scroll konten */
  }

  /* tabel di dalam modal: font & padding diperkecil */
  #modalCardPH table,
  #modalCardRealisasi table,
  #modalDebitur table{
    font-size: 11px !important;
  }
  #modalCardPH thead th,  #modalCardPH tbody td,
  #modalCardRealisasi thead th, #modalCardRealisasi tbody td,
  #modalDebitur thead th, #modalDebitur tbody td{
    padding: .35rem .5rem !important;
    line-height: 1.1 !important;
    white-space: nowrap;                  /* hemat tempat */
  }

  /* kolom nama nasabah bisa dipotong ellipsis agar angka-angka muat */
  #modalCardPH tbody td:nth-child(2),
  #modalCardRealisasi tbody td:nth-child(2),
  #modalDebitur tbody td:nth-child(2){
    max-width: 11rem;                     /* atur bila masih kepanjangan */
    overflow: hidden;
    text-overflow: ellipsis;
  }

  /* angka rata kanan tetap rapat */
  #modalCardPH td.text-right,
  #modalCardRealisasi td.text-right,
  #modalDebitur td.text-right{ padding-right: .4rem !important; }
}

/* Pastikan modal selalu di atas navbar */
#modalDebitur,
#modalDetailRealisasi{
  z-index: 100000 !important;
}


</style>

<script>
  // ===== Default tanggal (timezone-safe)
  const today = new Date();
  const startDef = new Date(today.getFullYear(), today.getMonth(), 1, 12);
  const endDef   = new Date(today.getFullYear(), today.getMonth(), today.getDate()-1, 12);
  const ymd = d => d.toISOString().split('T')[0];
  start_date.value = ymd(startDef); end_date.value = ymd(endDef);

  // ===== Sticky & scroller
  function setPHSticky(){
    const h   = document.getElementById('phHead1')?.offsetHeight || 40;
    const tot = document.querySelector('#tabelRecovery tr.total-row')?.offsetHeight || 36;
    const holder = document.getElementById('phScroller');
    holder.style.setProperty('--headH', h + 'px');
    holder.style.setProperty('--totalH', tot + 'px');
  }
  function sizePHScroller(){
    const wrap = document.getElementById('phScroller');
    const rectTop = wrap.getBoundingClientRect().top;
    wrap.style.height = Math.max(260, window.innerHeight - rectTop - 18) + 'px';
  }
  window.addEventListener('resize', ()=>{ setPHSticky(); sizePHScroller(); });

  // ===== Network: batalkan request lama jika ada (biar cepat & hemat)
  let phAbortCtrl = null;
  let phModalAbort = null;

  function disableFilter(disabled){
    const btn = document.getElementById('btnFilterPH');
    if(!btn) return;
    btn.disabled = disabled;
  }

  document.getElementById("filterForm").addEventListener("submit", (e)=>{
    e.preventDefault();
    fetchData(start_date.value, end_date.value);
  });

  fetchData(ymd(startDef), ymd(endDef));

  function fetchData(start_date, end_date){
    if(phAbortCtrl) phAbortCtrl.abort();
    phAbortCtrl = new AbortController();

    disableFilter(true);

    fetch("./api/hapus_buku/", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ type: "recovery", start_date, end_date }),
      signal: phAbortCtrl.signal
    })
    .then(r=>r.json())
    .then(res=>{
      const tbody = document.getElementById("tbodyPH");
      tbody.innerHTML = "";
      const data = Array.isArray(res.data) ? res.data : [];

      const totalRow = data.find(d => d.kode_kantor === "TOTAL");
      if(totalRow){
        tbody.insertAdjacentHTML('beforeend', `
          <tr class="total-row font-semibold text-sm">
            <td class="px-4 py-2 freeze-1 col1 col-kode">TOTAL</td>
            <td class="px-4 py-2 freeze-2 col2 col-nama"><span class="ttl-mobile">TOTAL</span></td>
            <td class="px-4 py-2 text-right">${rupiah(totalRow.total_pokok)}</td>
            <td class="px-4 py-2 text-right">${rupiah(totalRow.total_bunga)}</td>
            <td class="px-4 py-2 text-right">${rupiah(totalRow.total_ph)}</td>
            <td class="px-4 py-2 text-right">${fmtInt(totalRow.noa)}</td>
          </tr>
        `);
      }

      data.filter(d => d.kode_kantor !== "TOTAL").forEach(d=>{
        tbody.insertAdjacentHTML('beforeend', `
          <tr class="border-b">
            <td class="px-4 py-3 freeze-1 col1 col-kode">${esc(d.kode_kantor)}</td>
            <td class="px-4 py-3 freeze-2 col2 col-nama" title="${esc(d.nama_kantor)}">${esc(d.nama_kantor)}</td>
            <td class="px-4 py-3 text-right">${rupiah(d.total_pokok)}</td>
            <td class="px-4 py-3 text-right">${rupiah(d.total_bunga)}</td>
            <td class="px-4 py-3 text-right">${rupiah(d.total_ph)}</td>
            <td class="px-4 py-3 text-right">
              <a href="#" class="text-blue-600 hover:underline"
                onclick="event.preventDefault(); loadDebitur('${escAttr(d.kode_kantor)}','${start_date}','${end_date}')">
                ${fmtInt(d.noa)}
              </a>
            </td>
          </tr>
        `);

      });

      setPHSticky(); sizePHScroller();
      setTimeout(()=>{ setPHSticky(); sizePHScroller(); }, 50);
    })
    .catch(err=>{ if(err.name!=='AbortError') console.error(err); })
    .finally(()=> disableFilter(false));
  }

  // ===== Modal
  function loadDebitur(kodeKantor, start, end){
    if(phModalAbort) phModalAbort.abort();
    phModalAbort = new AbortController();

    const overlay = document.getElementById("modalDebitur");
    const title   = document.getElementById("modalTitle");
    const body    = document.getElementById("modalBody");

    overlay.classList.remove("hidden"); overlay.classList.add("items-center","justify-center","flex");
    title.textContent = `Daftar Debitur – Kode Kantor ${kodeKantor}`;
    body.innerHTML = `<p class="text-sm text-gray-500">Mengambil data debitur...</p>`;

    fetch("./api/hapus_buku/detail", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ type:"debitur", kode_kantor:kodeKantor, start_date:start, end_date:end }),
      signal: phModalAbort.signal
    })
    .then(r=>r.json())
    .then(res=>{
      const list = Array.isArray(res.data) ? res.data : [];
      if(!list.length){ body.innerHTML = `<p class="text-red-600 font-semibold">Tidak ada data.</p>`; return; }

      let html = `
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left text-gray-800 bg-white rounded">
            <thead class="bg-gray-100 text-gray-700">
              <tr>
                <th class="px-4 py-2">No Rekening</th>
                <th class="px-4 py-2">Nama Nasabah</th>
                <th class="px-4 py-2">Tanggal Transaksi</th>
                <th class="px-4 py-2 text-right">Pokok</th>
                <th class="px-4 py-2 text-right">Bunga</th>
                <th class="px-4 py-2 text-right">Total</th>
              </tr>
            </thead>
            <tbody>`;
      list.forEach(d=>{
        html += `
          <tr class="border-b">
            <td class="px-4 py-2">${esc(d.no_rekening)}</td>
            <td class="px-4 py-2">${esc(d.nama_nasabah)}</td>
            <td class="px-4 py-2">${esc(d.tanggal_transaksi)}</td>
            <td class="px-4 py-2 text-right">${rupiah(d.pokok)}</td>
            <td class="px-4 py-2 text-right">${rupiah(d.bunga)}</td>
            <td class="px-4 py-2 text-right">${rupiah(d.total)}</td>
          </tr>`;
      });
      html += `</tbody></table></div>`;
      body.innerHTML = html;
    })
    .catch(err=>{ if(err.name!=='AbortError') body.innerHTML = `<p class="text-red-600">Gagal mengambil data.</p>`; });

    // close handlers
    const close = ()=>{ overlay.classList.add("hidden"); overlay.classList.remove("flex"); };
    document.getElementById("btnClosePH").onclick = close;
    overlay.onclick = (e)=>{ if(!e.target.closest('#modalCardPH')) close(); };
    const escKey = (e)=>{ if(e.key==='Escape'){ close(); document.removeEventListener('keydown', escKey); } };
    document.addEventListener('keydown', escKey);
  }
  window.loadDebitur = loadDebitur;

  // ===== Helpers
  const rupiah = n => new Intl.NumberFormat("id-ID").format(+n||0);
  const fmtInt = n => new Intl.NumberFormat("id-ID",{maximumFractionDigits:0}).format(+n||0);
  const esc = s => String(s??'').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'","&#39;");
  const escAttr = s => String(s??'').replaceAll('"','&quot;').replaceAll("'","&#39;");
</script>
