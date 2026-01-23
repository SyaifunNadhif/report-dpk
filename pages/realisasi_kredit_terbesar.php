<div class="max-w-7xl mx-auto px-4 py-4 h-screen flex flex-col">
  <div class="hdr flex flex-wrap items-start gap-2 mb-2">
    <h1 class="title text-2xl font-bold flex items-center gap-2">
      <span>🏆</span> <span>50 Besar Realisasi</span>
    </h1>

    <form id="formFilterTop50" class="ml-auto" aria-label="Filter Top 50">
      <div id="filterTop50" class="flex items-center gap-2">
        
        <div class="flex flex-col">
            <label for="tgl_awal" class="lbl">Tgl Awal:</label>
            <input type="date" id="tgl_awal" class="inp" required>
        </div>

        <div class="flex flex-col">
            <label for="tgl_akhir" class="lbl">Tgl Akhir:</label>
            <input type="date" id="tgl_akhir" class="inp" required>
        </div>

        <button type="submit" class="btn-icon mt-auto" aria-label="Filter" style="margin-bottom: 2px;">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="7"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
        </button>
      </div>
    </form>
  </div>

  <div id="loadingTop50" class="hidden flex items-center gap-2 text-sm text-gray-600 mb-2">
    <svg class="animate-spin h-5 w-5 text-blue-600" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span>Sedang memuat data Top 50...</span>
  </div>

  <div id="top50Scroller" class="flex-1 min-h-0 overflow-hidden rounded border border-gray-200 bg-white">
    <div id="top50ScrollerInner" class="h-full overflow-auto">
      <table id="tabelTop50" class="min-w-full text-sm text-left text-gray-700">
        <thead class="uppercase">
          <tr id="top50Head1" class="text-xs">
            <th class="px-4 py-2 sticky-t50 freeze-1 col1 text-center">NO</th>
            <th class="px-4 py-2 sticky-t50 freeze-2 col2 cursor-pointer" onclick="sortTop50('nama_nasabah')">NAMA NASABAH</th>
            
            <th class="px-4 py-2 sticky-t50 text-center">CAB</th>
            <th class="px-4 py-2 sticky-t50">NO REKENING</th>
            <th class="px-4 py-2 sticky-t50 text-right cursor-pointer" onclick="sortTop50('plafond')">PLAFOND</th>
            <th class="px-4 py-2 sticky-t50 text-center">TGL REALISASI</th>
            <th class="px-4 py-2 sticky-t50 text-center">JATUH TEMPO</th>
            <th class="px-4 py-2 sticky-t50 min-w-[200px]">ALAMAT</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<style>
  /* ===== Style Dasar (Sama dengan Referensi) ===== */
  .inp{ border:1px solid #cbd5e1; border-radius:.6rem; padding:.4rem .75rem; font-size:14px; background:#fff; }
  .lbl{ font-size:11px; color:#64748b; font-weight: 600; margin-bottom: 2px; }
  .btn-icon{
    width:38px; height:38px; border-radius:8px;
    display:inline-flex; align-items:center; justify-content:center;
    background:#2563eb; color:#fff; box-shadow:0 4px 6px rgba(37,99,235,.2);
    border: none; cursor: pointer; transition: all 0.2s;
  }
  .btn-icon:hover{ background:#1e40af; transform: translateY(-1px); }

  /* ===== Responsive Header ===== */
  .hdr{ row-gap:.5rem; align-items: flex-end; }
  @media (max-width:640px){
    .title{ font-size:1.25rem; margin-bottom: 0.5rem; } 
    .hdr{ flex-direction:column; align-items:flex-start; }
    #filterTop50{ width:100%; justify-content: space-between; }
    .inp{ width: 100%; }
    .flex-col { flex: 1; }
  }

  /* ===== TABEL FREEZE LOGIC (Top 50 Spec) ===== */
  body{ overflow:hidden; }
  
  /* Atur Lebar Kolom Freeze Disini */
  #top50Scroller{ --t50_col1:3.5rem; --t50_col2:14rem; }

  #tabelTop50 .col1{ width:var(--t50_col1); min-width:var(--t50_col1); }
  #tabelTop50 .col2{ width:var(--t50_col2); min-width:var(--t50_col2); }

  /* Freeze Logic */
  #tabelTop50 .freeze-1{ position:sticky; left:0; z-index:41; background:#fff; border-right: 1px solid #e2e8f0; }
  #tabelTop50 .freeze-2{ position:sticky; left:var(--t50_col1); z-index:40; background:#fff; box-shadow:2px 0 4px rgba(0,0,0,.05); }

  /* Header Colors */
  #tabelTop50 thead th{ position:sticky; top:0; background:#f1f5f9; z-index:30; font-weight: 700; color: #334155; border-bottom: 2px solid #e2e8f0; }
  
  /* Layering Fix untuk Header Freeze */
  #tabelTop50 thead th.freeze-1{ z-index:51 !important; background:#f1f5f9; }
  #tabelTop50 thead th.freeze-2{ z-index:50 !important; background:#f1f5f9; }

  /* Row Hover */
  #tabelTop50 tbody tr:hover td{ background:#f8fafc; }
  #tabelTop50 tbody tr:hover td.freeze-1,
  #tabelTop50 tbody tr:hover td.freeze-2 { background:#f1f5f9; }

  /* Typography */
  #tabelTop50 td { padding: 0.75rem 1rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
  .font-mono-num { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }

  /* Mobile Tweaks */
  @media (max-width:640px){
    #tabelTop50{ font-size:12px; }
    #top50Scroller{ --t50_col1:3rem; --t50_col2:10rem; }
    #tabelTop50 td, #tabelTop50 th { padding: 0.5rem; }
  }
</style>

<script>
  /* ===== STATE ===== */
  let top50DataRaw = [];
  let top50SortKey = 'plafond';
  let top50SortAsc = false;

  const fmtRupiah = n => new Intl.NumberFormat("id-ID", { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(+n||0);
  const fmtDateIndo = d => {
      if(!d) return "-";
      const date = new Date(d);
      return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
  };

  /* ===== INIT DENGAN API DATE ===== */
  (async () => {
    // 1. Ambil data tanggal terakhir dari server
    const dateInfo = await getLastHarianData();
    if (!dateInfo) return; // Stop kalau API mati

    const { last_created } = dateInfo; // Kita ambil tanggal data terakhir (snapshot)

    // 2. Set Tgl Akhir = last_created
    const tglAkhirEl = document.getElementById("tgl_akhir");
    const tglAwalEl = document.getElementById("tgl_awal");

    tglAkhirEl.value = last_created;

    // 3. Hitung Tgl Awal (Tanggal 1 di bulan yang sama dengan last_created)
    // Supaya otomatis range-nya 1 bulan berjalan sesuai data server
    const d = new Date(last_created);
    const firstDay = new Date(d.getFullYear(), d.getMonth(), 1);
    
    // Format YYYY-MM-DD manual biar aman timezone
    const year = firstDay.getFullYear();
    const month = String(firstDay.getMonth() + 1).padStart(2, '0');
    const day = String(firstDay.getDate()).padStart(2, '0');
    tglAwalEl.value = `${year}-${month}-${day}`;

    // 4. Langsung tarik data Top 50
    fetchTop50Data(tglAwalEl.value, last_created);
  })();

  // Fungsi ambil tanggal server (Sama persis requestmu)
  async function getLastHarianData() {
    try {
      const res = await fetch('./api/date/', { method: 'GET' });
      const json = await res.json();
      return json.data || null;
    } catch { return null; }
  }

  /* ===== EVENT LISTENER ===== */
  document.getElementById("formFilterTop50").addEventListener("submit", function (e) {
    e.preventDefault();
    const tgl_awal = document.getElementById("tgl_awal").value;
    const tgl_akhir = document.getElementById("tgl_akhir").value;
    fetchTop50Data(tgl_awal, tgl_akhir);
  });

  /* ===== FETCH DATA TOP 50 ===== */
  function fetchTop50Data(tgl_awal, tgl_akhir) {
    const loading = document.getElementById("loadingTop50");
    const tbody = document.querySelector("#tabelTop50 tbody");
    
    loading.classList.remove("hidden");
    tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-gray-400">Sedang memuat...</td></tr>`;

    fetch("./api/kredit/", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ 
          type: "50 besar realisasi", 
          tgl_awal: tgl_awal, 
          tgl_akhir: tgl_akhir 
      })
    })
    .then(res => res.json())
    .then(res => {
      top50DataRaw = Array.isArray(res.data) ? res.data : [];
      renderTop50Table(top50DataRaw);
    })
    .catch(err => {
        console.error(err);
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-red-500">Gagal memuat data.</td></tr>`;
    })
    .finally(() => loading.classList.add("hidden"));
  }

  /* ===== RENDER TABLE ===== */
  function renderTop50Table(data) {
    const tbody = document.querySelector("#tabelTop50 tbody");
    tbody.innerHTML = "";

    if (data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="8" class="text-center py-10 text-gray-500 font-semibold">Tidak ada data realisasi pada periode ini.</td></tr>`;
      return;
    }

    data.forEach((row, index) => {
      const rowHtml = `
        <tr class="border-b transition hover:bg-blue-50">
          <td class="px-4 py-3 text-center freeze-1 col1 font-bold text-gray-500 bg-white">
            ${index + 1}
          </td>
          <td class="px-4 py-3 freeze-2 col2 font-semibold text-gray-800 bg-white whitespace-nowrap overflow-hidden text-ellipsis" title="${row.nama_nasabah}">
            ${row.nama_nasabah || "-"}
          </td>
          <td class="px-4 py-3 text-center">
            <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs font-mono">${row.kode_cabang || "-"}</span>
          </td>
          <td class="px-4 py-3 font-mono-num text-gray-600 whitespace-nowrap">
            ${row.no_rekening || "-"}
          </td>
          <td class="px-4 py-3 text-right font-bold text-blue-600 whitespace-nowrap">
            ${fmtRupiah(row.plafond)}
          </td>
          <td class="px-4 py-3 text-center whitespace-nowrap">
            ${fmtDateIndo(row.tgl_realisasi)}
          </td>
          <td class="px-4 py-3 text-center whitespace-nowrap text-gray-500">
            ${fmtDateIndo(row.tgl_jatuh_tempo)}
          </td>
          <td class="px-4 py-3 text-xs text-gray-500 min-w-[250px] leading-snug">
            ${row.alamat || "-"}
          </td>
        </tr>
      `;
      tbody.insertAdjacentHTML('beforeend', rowHtml);
    });
  }

  /* ===== SORTING CLIENT SIDE ===== */
  function sortTop50(key) {
    if (top50SortKey === key) { top50SortAsc = !top50SortAsc; }
    else { top50SortKey = key; top50SortAsc = true; } 

    // Custom logic: Plafond desc default
    if(key === 'plafond' && top50SortKey !== key) top50SortAsc = false; 

    const sorted = [...top50DataRaw].sort((a,b)=>{
      let valA = a[key];
      let valB = b[key];

      if(key === 'plafond') {
          valA = parseFloat(valA) || 0;
          valB = parseFloat(valB) || 0;
      } else {
          valA = (valA || '').toString().toLowerCase();
          valB = (valB || '').toString().toLowerCase();
      }

      if (valA < valB) return top50SortAsc ? -1 : 1;
      if (valA > valB) return top50SortAsc ? 1 : -1;
      return 0;
    });
    renderTop50Table(sorted);
  }
  window.sortTop50 = sortTop50;
</script>