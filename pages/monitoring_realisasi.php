<div class="max-w-[1920px] mx-auto px-4 py-6 h-screen flex flex-col font-sans text-slate-800 bg-slate-50">
  
  <div class="flex-none mb-4">
    
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 mb-4 border-b border-slate-200 pb-4">
        
        <div>
            <h1 class="title text-2xl font-bold flex items-center gap-2 text-slate-800 mb-2">
                <span class="p-1.5 bg-blue-600 text-white rounded shadow-sm text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </span>
                <span>Monitoring Realisasi Kredit</span>
            </h1>
            
            <div id="summaryWrap" class="flex items-center gap-3 text-sm animate-fade-in hidden">
                <div class="px-3 py-1 bg-blue-50 text-blue-800 rounded-full border border-blue-100 font-semibold shadow-sm">
                    Total NOA: <span id="sum_noa" class="font-bold text-blue-900 ml-1">0</span>
                </div>
                <div class="px-3 py-1 bg-green-50 text-green-800 rounded-full border border-green-100 font-semibold shadow-sm">
                    Total Realisasi: <span id="sum_realisasi" class="font-bold text-green-900 ml-1">Rp 0</span>
                </div>
            </div>
        </div>

        <form id="formFilterTop50" class="flex flex-wrap items-end gap-2 ml-auto" onsubmit="event.preventDefault(); fetchTop50Data();">
            
            <div class="flex flex-col">
                <label for="tgl_awal" class="lbl">Tgl Awal</label>
                <input type="date" id="tgl_awal" class="inp" required>
            </div>
            <div class="flex flex-col">
                <label for="tgl_akhir" class="lbl">Tgl Akhir</label>
                <input type="date" id="tgl_akhir" class="inp" required>
            </div>

            <div class="flex flex-col">
                <label for="filter_kantor" class="lbl">Kantor Cabang</label>
                <select id="filter_kantor" class="inp min-w-[150px]" onchange="onBranchChange()">
                    <option value="">Konsolidasi (Top 50)</option>
                    </select>
            </div>

            <div class="flex flex-col">
                <label for="filter_ao" class="lbl">AO / Marketing</label>
                <select id="filter_ao" class="inp min-w-[150px]" onchange="fetchTop50Data()">
                    <option value="">Semua AO</option>
                    </select>
            </div>

            <button type="submit" class="btn-icon bg-blue-600 hover:bg-blue-700 text-white shadow-sm" title="Cari Data (Klik untuk update tanggal)">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="11" cy="11" r="7"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </button>
        </form>
    </div>
  </div>

  <div id="loadingTop50" class="hidden flex items-center justify-center gap-2 text-sm text-blue-600 mb-2 py-4 flex-none">
    <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span class="font-medium">Mengambil data realisasi...</span>
  </div>

  <div id="top50Scroller" class="flex-1 min-h-0 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm relative">
    <div id="top50ScrollerInner" class="h-full overflow-auto custom-scrollbar">
      <table id="tabelTop50" class="min-w-full text-sm text-left text-slate-700 border-separate border-spacing-0">
        <thead class="uppercase bg-slate-100 text-slate-600 font-bold sticky top-0 z-30 shadow-sm text-xs">
          <tr>
            <th class="px-4 py-3 sticky left-0 z-40 bg-slate-100 border-b border-r text-center w-[50px]">NO</th>
            
            <th class="px-4 py-3 sticky left-[50px] z-40 bg-slate-100 border-b border-r min-w-[220px] shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] cursor-pointer hover:bg-slate-200 hover:text-blue-700 transition select-none" onclick="sortTop50('nama_nasabah')" title="Klik untuk urutkan Nama">
                NAMA NASABAH <span class="text-[10px] ml-1 opacity-50">⇅</span>
            </th>
            
            <th class="px-4 py-3 bg-slate-100 border-b text-center w-[60px]">CAB</th>
            <th class="px-4 py-3 bg-slate-100 border-b text-center text-blue-700 min-w-[120px]">AO</th>
            <th class="px-4 py-3 bg-slate-100 border-b text-center font-mono">NO REKENING</th>
            
            <th class="px-4 py-3 bg-slate-100 border-b text-right cursor-pointer hover:bg-slate-200 hover:text-blue-700 transition select-none min-w-[140px]" onclick="sortTop50('plafond')" title="Klik untuk urutkan Plafond">
                PLAFOND <span class="text-[10px] ml-1 opacity-50">⇅</span>
            </th>
            
            <th class="px-4 py-3 bg-slate-100 border-b text-center min-w-[100px]">REALISASI</th>
            <th class="px-4 py-3 bg-slate-100 border-b text-center min-w-[100px]">JATUH TEMPO</th>
            <th class="px-4 py-3 bg-slate-100 border-b min-w-[250px]">ALAMAT</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white"></tbody>
      </table>
    </div>
  </div>
</div>

<style>
  .inp { border:1px solid #cbd5e1; border-radius:6px; padding:0 8px; font-size:12px; background:#fff; height:34px; transition:all 0.2s; outline:none; color: #334155; }
  .inp:focus { border-color:#2563eb; box-shadow:0 0 0 2px rgba(37,99,235,0.1); }
  .lbl { font-size:10px; color:#64748b; font-weight:700; margin-bottom:2px; text-transform:uppercase; letter-spacing:0.03em; }
  .btn-icon { width:34px; height:34px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; border:none; cursor:pointer; transition:all 0.2s; }
  .btn-icon:hover { transform:translateY(-1px); }
  .custom-scrollbar::-webkit-scrollbar { width:6px; height:6px; }
  .custom-scrollbar::-webkit-scrollbar-track { background:#f1f5f9; }
  .custom-scrollbar::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:3px; }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover { background:#94a3b8; }
  .animate-fade-in { animation: fadeIn 0.3s ease-in forwards; }
  @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
  @media (max-width:640px) { #formFilterTop50 { width:100%; justify-content:space-between; } .inp { width:100%; } }
</style>

<script>
  /* ===== CONFIG ===== */
  const API_KREDIT_URL = './api/kredit/'; // URL Controller Kredit
  const API_KODE_URL   = './api/kode/';   // URL Controller Kode
  
  let top50DataRaw = [];
  let top50SortKey = 'plafond';
  let top50SortAsc = false;

  const fmtRupiah = n => new Intl.NumberFormat("id-ID", { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(+n||0);
  const fmtDateIndo = d => { 
      if(!d || d === '0000-00-00') return "-"; 
      const date = new Date(d); 
      return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }); 
  };

  /* ===== INITIALIZATION ===== */
  (async () => {
    // 1. Ambil Tanggal Terakhir dari DB
    const dateInfo = await getLastHarianData();
    if (dateInfo) {
        const { last_created } = dateInfo;
        const d = new Date(last_created);
        const firstDay = new Date(d.getFullYear(), d.getMonth(), 1); 
        
        document.getElementById("tgl_akhir").value = last_created;
        document.getElementById("tgl_awal").value  = firstDay.toISOString().split('T')[0];
    } else {
        const now = new Date();
        document.getElementById("tgl_akhir").value = now.toISOString().split('T')[0];
        document.getElementById("tgl_awal").value  = now.toISOString().split('T')[0];
    }

    // 2. Populate Dropdown
    await populateKantor();
    await loadAODropdown(); // Load AO awal

    // 3. Load Data
    fetchTop50Data();
  })();

  async function getLastHarianData() {
    try { const res = await fetch('./api/date/', { method: 'GET' }); const json = await res.json(); return json.data || null; } catch { return null; }
  }

  /* ===== 1. POPULATE KANTOR ===== */
  async function populateKantor() {
      const el = document.getElementById('filter_kantor');
      const user = (window.getUser && window.getUser()) || JSON.parse(localStorage.getItem('app_user')) || { kode: '000' };
      const uKode = String(user.kode || '000').padStart(3, '0');

      if (uKode !== '000' && uKode !== '099') {
          el.innerHTML = `<option value="${uKode}">CABANG ${uKode}</option>`;
          el.disabled = true;
          return;
      }

      try {
          const r = await fetch(API_KODE_URL, { method: 'POST', body: JSON.stringify({ type: 'kode_kantor' }) });
          const j = await r.json();
          let h = '<option value="">Konsolidasi (Top 50)</option>';
          if(j.data) {
              j.data.filter(x => x.kode_kantor !== '000').forEach(x => { 
                  h += `<option value="${x.kode_kantor}">${x.kode_kantor} - ${x.nama_kantor}</option>`; 
              });
          }
          el.innerHTML = h;
      } catch { el.innerHTML = '<option value="">Konsolidasi (Top 50)</option>'; }
  }

  /* ===== 2. LOGIC GANTI CABANG -> LOAD AO -> REFRESH DATA ===== */
  async function onBranchChange() {
      // 1. Load Dropdown AO dulu (await biar selesai dulu)
      await loadAODropdown();
      // 2. Setelah dropdown AO terisi, baru refresh data tabel
      fetchTop50Data();
  }

  async function loadAODropdown() {
      const elAO = document.getElementById('filter_ao');
      const branch = document.getElementById('filter_kantor').value;
      
      elAO.innerHTML = '<option value="">Memuat...</option>';
      elAO.disabled = true;
      
      try {
          const payload = { type: 'kode_ao_kredit', kode_kantor: branch };
          const r = await fetch(API_KODE_URL, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload) });
          const j = await r.json();
          
          let h = '<option value="">Semua AO</option>';
          if(j.data && Array.isArray(j.data)) {
              j.data.forEach(x => { 
                  // Nama AO disingkat max 2 kata
                  const rawName = x.nama_ao || x.kode_group2;
                  const shortName = rawName.split(' ').slice(0,2).join(' ');
                  h += `<option value="${x.kode_group2}">${x.kode_group2} - ${shortName}</option>`; 
              });
          }
          elAO.innerHTML = h;
      } catch(err) { 
          console.error("Gagal load AO:", err);
          elAO.innerHTML = '<option value="">Semua AO</option>'; 
      } finally {
          elAO.disabled = false;
      }
  }

  /* ===== 3. FETCH & RENDER TABLE ===== */
  function fetchTop50Data() {
    const tgl_awal  = document.getElementById("tgl_awal").value;
    const tgl_akhir = document.getElementById("tgl_akhir").value;
    const kode_kantor = document.getElementById("filter_kantor").value;
    const kode_ao     = document.getElementById("filter_ao").value;

    const loading = document.getElementById("loadingTop50");
    const tbody   = document.querySelector("#tabelTop50 tbody");
    const summary = document.getElementById('summaryWrap');
    
    loading.classList.remove("hidden");
    summary.classList.add("hidden"); 
    tbody.innerHTML = `<tr><td colspan="9" class="text-center py-12 text-slate-400 italic">Sedang memuat data...</td></tr>`;

    const payload = { 
        type: "top realisasi", 
        closing_date: tgl_awal, 
        harian_date: tgl_akhir,
        kode_kantor: kode_kantor,
        kode_ao: kode_ao
    };

    fetch(API_KREDIT_URL, {
      method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(res => {
      top50DataRaw = Array.isArray(res.data) ? res.data : [];
      sortTop50(top50SortKey, true); // Render & Sort
    })
    .catch(err => {
        console.error(err);
        tbody.innerHTML = `<tr><td colspan="9" class="text-center py-12 text-red-500 font-medium">Gagal memuat data. Periksa koneksi.</td></tr>`;
    })
    .finally(() => loading.classList.add("hidden"));
  }

  function renderTop50Table(data) {
    const tbody = document.querySelector("#tabelTop50 tbody");
    const summary = document.getElementById('summaryWrap');
    tbody.innerHTML = "";

    if (data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="9" class="text-center py-12 text-slate-500 font-medium bg-slate-50">Tidak ada data realisasi pada periode ini.</td></tr>`;
      
      // Reset Summary
      document.getElementById('sum_noa').textContent = "0";
      document.getElementById('sum_realisasi').textContent = "Rp 0";
      summary.classList.remove('hidden');
      return;
    }

    // HITUNG SUMMARY
    let totalNoa = data.length;
    let totalNominal = data.reduce((acc, row) => acc + (parseFloat(row.plafond) || 0), 0);

    document.getElementById('sum_noa').textContent = new Intl.NumberFormat('id-ID').format(totalNoa);
    document.getElementById('sum_realisasi').textContent = fmtRupiah(totalNominal);
    summary.classList.remove('hidden');

    let html = '';
    data.forEach((row, index) => {
      const aoName = (row.nama_ao || '-').split(' ').slice(0, 2).join(' ');

      html += `
        <tr class="border-b border-slate-100 transition hover:bg-blue-50 group h-[38px]">
          <td class="px-4 py-2 text-center sticky left-0 z-20 bg-white group-hover:bg-blue-50 border-r border-slate-200 font-mono text-xs text-slate-500">
            ${index + 1}
          </td>
          <td class="px-4 py-2 sticky left-[50px] z-20 bg-white group-hover:bg-blue-50 border-r border-slate-200 font-semibold text-slate-700 whitespace-nowrap overflow-hidden text-ellipsis max-w-[250px]" title="${row.nama_nasabah}">
            ${row.nama_nasabah || "-"}
          </td>
          <td class="px-4 py-2 text-center">
            <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-xs font-mono font-bold">${row.kode_cabang || "-"}</span>
          </td>
          <td class="px-4 py-2 text-center text-xs font-bold text-blue-700 bg-blue-50/30 whitespace-nowrap">
            ${aoName}
          </td>
          <td class="px-4 py-2 font-mono text-xs text-slate-500 whitespace-nowrap text-center">
            ${row.no_rekening || "-"}
          </td>
          <td class="px-4 py-2 text-right font-bold text-slate-800 whitespace-nowrap bg-green-50/20 border-l border-green-100">
            ${fmtRupiah(row.plafond)}
          </td>
          <td class="px-4 py-2 text-center whitespace-nowrap text-xs text-slate-600">
            ${fmtDateIndo(row.tgl_realisasi)}
          </td>
          <td class="px-4 py-2 text-center whitespace-nowrap text-xs text-slate-500">
            ${fmtDateIndo(row.tgl_jatuh_tempo)}
          </td>
          <td class="px-4 py-2 text-xs text-slate-500 min-w-[250px] leading-snug truncate max-w-[300px]" title="${row.alamat}">
            ${row.alamat || "-"}
          </td>
        </tr>
      `;
    });
    tbody.innerHTML = html;
  }

  /* ===== 4. CLIENT SIDE SORTING ===== */
  function sortTop50(key, force = false) {
    if (!force) {
        if (top50SortKey === key) { top50SortAsc = !top50SortAsc; }
        else { top50SortKey = key; top50SortAsc = true; } 
        if(key === 'plafond' && top50SortKey !== key) top50SortAsc = false; 
    }

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