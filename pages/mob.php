<div class="w-full h-screen flex flex-col bg-gray-50 overflow-hidden">
  
  <div class="flex-none px-6 py-4 bg-white border-b shadow-sm z-20">
    <div class="max-w-7xl mx-auto w-full flex flex-wrap items-center gap-4">
        <h1 class="text-xl font-bold text-gray-800 flex items-center gap-2">
            <span>📊</span> <span>Rekap Control MOB</span>
        </h1>

        <form id="formFilterMob" class="ml-auto flex items-end gap-2 text-sm">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Kode Kantor</label>
                <input type="text" id="kode_kantor_mob" class="border border-gray-300 rounded px-2 py-1.5 w-24 focus:outline-blue-500" placeholder="Semua">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Posisi Data</label>
                <input type="date" id="harian_date_mob" class="border border-gray-300 rounded px-2 py-1.5 focus:outline-blue-500" required>
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white p-1.5 rounded transition shadow-sm" title="Tampilkan Data">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </button>
        </form>
    </div>
  </div>

  <div class="flex-1 relative w-full max-w-7xl mx-auto p-4 min-h-0">
      
      <div id="mobScroller" class="w-full h-full bg-white border border-gray-300 rounded-lg shadow-sm flex flex-col">
          
          <div id="loadingMob" class="hidden absolute inset-0 bg-white/80 z-[60] flex items-center justify-center backdrop-blur-sm">
             <div class="flex flex-col items-center gap-2 text-blue-600">
                <svg class="animate-spin h-8 w-8" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span class="text-sm font-semibold">Sedang memproses data...</span>
             </div>
          </div>

          <div id="infoRangeMob" class="hidden px-4 py-2 bg-blue-50 text-xs text-blue-800 border-b border-blue-100 flex-none font-medium">
             📅 Menampilkan Realisasi: <span id="txtRange" class="font-bold">-</span>
          </div>

          <div class="flex-1 overflow-auto relative bg-white"> 
              <table id="tabelMob" class="w-full text-sm text-left text-gray-700 border-separate border-spacing-0">
                <thead class="text-gray-600 uppercase text-xs font-semibold">
                  <tr>
                     <th colspan="4" class="sticky top-0 z-50 bg-gray-100 border-b border-gray-300 text-center py-2 shadow-sm h-[34px]">
                        INFO REALISASI
                     </th>
                     <th id="thBucketGroup" class="sticky top-0 z-50 bg-gray-50 border-b border-gray-300 text-center py-2 shadow-sm border-l h-[34px]">
                        BUCKET DPD
                     </th>
                  </tr>
                  <tr id="mobHead2">
                    <th class="sticky top-[34px] z-40 left-0 bg-gray-100 px-4 py-3 border-b border-r min-w-[120px]">BULAN</th>
                    <th class="sticky top-[34px] z-40 left-[120px] bg-gray-100 px-2 py-3 border-b border-r text-center w-[60px]">MOB</th>
                    <th class="sticky top-[34px] z-30 bg-gray-100 px-4 py-3 border-b text-right min-w-[140px]">PLAFOND</th>
                    <th class="sticky top-[34px] z-30 bg-gray-50 px-4 py-3 border-b border-r text-right min-w-[140px]">OS (NOW)</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    </tbody>
              </table>
          </div>
      </div>
  </div>
</div>

<style>
  /* ===== CSS STICKY & FREEZE PANE ===== */
  
  /* 1. Freeze Columns (Body) */
  /* Note: z-index harus lebih rendah dari thead (50/40) tapi lebih tinggi dari normal td (0) */
  #tabelMob .col-bulan { 
      position: sticky;
      left: 0; 
      z-index: 20; /* Fixed z-index so it floats above normal cells but below headers */
      border-right: 1px solid #e5e7eb;
      background-color: inherit; /* Inherit color from row (zebra) */
  }
  
  #tabelMob .col-mob { 
      position: sticky;
      left: 120px; 
      z-index: 20; 
      border-right: 1px solid #e5e7eb;
      text-align: center;
      font-weight: bold;
      background-color: inherit;
  }

  /* 2. Freeze Headers */
  /* Ensure headers have solid background to cover body content when scrolling */
  #tabelMob thead th {
      background-clip: padding-box; /* Prevents border overlap issues */
  }
  
  /* Corner Case: Header cells that are also Left-Sticky */
  /* Specific styling for the top-left headers to ensure they stay on top of everything */
  #mobHead2 th:nth-child(1), 
  #mobHead2 th:nth-child(2) {
      z-index: 45 !important; /* Higher than normal headers (30) but lower than group headers (50) */
  }

  /* Row Coloring logic for sticky columns */
  #tabelMob tbody tr:nth-child(even) { background-color: #f9fafb; }
  #tabelMob tbody tr:nth-child(odd) { background-color: #ffffff; }
  
  /* Hover Effect logic for sticky columns */
  #tabelMob tbody tr:hover td { background-color: #eff6ff !important; }

  /* Cell Styles */
  .cell-pct { display: block; font-weight: 700; color: #1d4ed8; font-size: 13px; }
  .cell-os { display: block; font-size: 10px; color: #64748b; margin-top: 1px; }
  .cell-zero { color: #e5e7eb; text-align: center; font-size: 16px; font-weight: 300; }
</style>

<script>
  /* ===== JS LOGIC (SAMA PERSIS) ===== */
  const fmtRupiah = n => new Intl.NumberFormat("id-ID").format(+n||0);
  const fmtPercent = n => (+n||0).toFixed(2) + '%';
  const fmtMonth = s => {
      if(!s) return "-";
      const [y, m] = s.split('-');
      const months = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Ags","Sep","Okt","Nov","Des"];
      return `${months[parseInt(m)-1]} ${y}`;
  };

  document.addEventListener("DOMContentLoaded", async () => {
    const dateInfo = await getLastHarianData();
    const today = new Date().toISOString().split('T')[0];
    document.getElementById("harian_date_mob").value = dateInfo?.last_created || today;
    handleFilterSubmit();
  });

  async function getLastHarianData() {
    try {
      const res = await fetch('./api/date/'); 
      const json = await res.json();
      return json.data || null;
    } catch { return null; }
  }

  document.getElementById("formFilterMob").addEventListener("submit", (e) => {
    e.preventDefault();
    handleFilterSubmit();
  });

  function handleFilterSubmit() {
    const harian_date = document.getElementById("harian_date_mob").value;
    const kode_kantor = document.getElementById("kode_kantor_mob").value;
    fetchMobData(harian_date, kode_kantor);
  }

  function fetchMobData(harian_date, kode_kantor) {
    const loading = document.getElementById("loadingMob");
    const tbody = document.querySelector("#tabelMob tbody");
    const infoBox = document.getElementById("infoRangeMob");
    
    loading.classList.remove("hidden");
    tbody.innerHTML = ""; 

    fetch("./api/kredit/", { 
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ type: "mob_vintage", harian_date, kode_kantor })
    })
    .then(res => res.json())
    .then(res => {
        const data = res.data;
        document.getElementById("txtRange").innerText = `${data.range_realisasi.start} s/d ${data.range_realisasi.end}`;
        infoBox.classList.remove("hidden");
        renderMobTable(data.data, data.buckets_order);
    })
    .catch(err => {
        tbody.innerHTML = `<tr><td colspan="10" class="text-center py-10 text-red-500 font-medium">Gagal memuat: ${err.message}</td></tr>`;
    })
    .finally(() => loading.classList.add("hidden"));
  }

  function renderMobTable(rows, bucketsOrder) {
    const tbody = document.querySelector("#tabelMob tbody");
    const headerRow2 = document.getElementById("mobHead2");
    const thGroup = document.getElementById("thBucketGroup");

    // 1. UPDATE HEADER DINAMIS
    thGroup.colSpan = bucketsOrder.length; 
    
    while (headerRow2.children.length > 4) { headerRow2.removeChild(headerRow2.lastChild); }

    bucketsOrder.forEach(bucketLabel => {
        const th = document.createElement("th");
        // FIXED: Added z-30 here to ensure bucket headers stay above body but below group header
        th.className = "sticky top-[34px] z-30 bg-blue-50 px-2 py-3 border-b border-gray-200 text-center min-w-[90px] text-blue-800 border-l";
        th.innerText = bucketLabel;
        headerRow2.appendChild(th);
    });

    // 2. RENDER ROWS
    if(!rows || rows.length === 0) {
        tbody.innerHTML = `<tr><td colspan="${4+bucketsOrder.length}" class="text-center py-10 text-gray-400">Data tidak ditemukan.</td></tr>`;
        return;
    }

    rows.sort((a,b) => b.bulan_realisasi.localeCompare(a.bulan_realisasi));

    rows.forEach(row => {
        const tr = document.createElement("tr");
        
        let html = `
            <td class="px-4 py-3 col-bulan border-r font-semibold text-gray-800">
                ${fmtMonth(row.bulan_realisasi)}
            </td>
            <td class="px-2 py-3 col-mob border-r font-mono text-gray-600">
                ${row.mob}
            </td>
            <td class="px-4 py-3 text-right font-medium text-gray-600">
                ${fmtRupiah(row.total_plafond)}
            </td>
            <td class="px-4 py-3 text-right font-medium text-gray-800 border-r bg-gray-50/50">
                ${fmtRupiah(row.total_os)}
            </td>
        `;

        bucketsOrder.forEach(bucketKey => {
            const bData = row.buckets[bucketKey] || { os: 0, pct: 0 };
            const isZero = bData.pct === 0;
            
            html += `<td class="px-2 py-2 text-center border-l border-gray-100 align-middle">
                ${isZero 
                    ? `<span class="cell-zero">-</span>` 
                    : `<span class="cell-pct">${fmtPercent(bData.pct)}</span><span class="cell-os">${fmtRupiah(bData.os)}</span>`
                }
            </td>`;
        });

        tr.innerHTML = html;
        tbody.appendChild(tr);
    });
  }
</script>