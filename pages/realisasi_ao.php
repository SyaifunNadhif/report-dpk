<div class="max-w-[1920px] mx-auto px-2 md:px-4 py-4 md:py-6 h-[calc(100vh-80px)] flex flex-col font-sans text-slate-800 bg-slate-50">
  
  <div class="flex-none mb-3 md:mb-4">
    
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-3 md:gap-4 mb-3 md:mb-4 border-b border-slate-200 pb-3 md:pb-4">
        
        <div class="shrink-0">
            <h1 class="title text-lg md:text-2xl font-bold flex items-center gap-2 text-slate-800 mb-2">
                <span class="p-1.5 md:p-2 bg-blue-600 text-white rounded-lg shadow-sm text-sm">
                    <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </span>
                <span id="judulMonitoring">Monitoring Realisasi Kredit</span>
            </h1>
            
            <div id="summaryWrap" class="flex flex-wrap items-center gap-2 text-xs md:text-sm animate-fade-in hidden">
                <div class="px-3 py-1 bg-blue-50 text-blue-800 rounded-full border border-blue-100 font-semibold shadow-sm">
                    Total NOA: <span id="sum_noa" class="font-bold text-blue-900 ml-1">0</span>
                </div>
                <div class="px-3 py-1 bg-emerald-50 text-emerald-800 rounded-full border border-emerald-100 font-semibold shadow-sm">
                    Total Realisasi: <span id="sum_realisasi" class="font-bold text-emerald-900 ml-1">Rp 0</span>
                </div>
            </div>
        </div>

        <form id="formFilterTop50" class="flex flex-wrap lg:ml-auto items-end gap-1.5 md:gap-2 bg-white p-2 md:p-3 rounded-xl border border-slate-200 shadow-sm shrink-0 w-full lg:w-auto" onsubmit="event.preventDefault(); fetchTop50Data();">
            
            <div class="flex flex-col w-[30%] md:w-auto shrink-0">
                <label for="tgl_awal" class="lbl">Tgl Awal</label>
                <input type="date" id="tgl_awal" class="inp" required onclick="try{this.showPicker()}catch(e){}">
            </div>
            <div class="flex flex-col w-[30%] md:w-auto shrink-0">
                <label for="tgl_akhir" class="lbl">Tgl Akhir</label>
                <input type="date" id="tgl_akhir" class="inp" required onclick="try{this.showPicker()}catch(e){}">
            </div>

            <div class="flex flex-col flex-1 min-w-[80px] md:min-w-[160px]">
                <label for="filter_kantor" class="lbl">Kantor</label>
                <select id="filter_kantor" class="inp truncate" onchange="onBranchChange()">
                    <option value="">Memuat...</option>
                </select>
            </div>

            <div class="flex flex-col flex-1 min-w-[80px] md:min-w-[160px]">
                <label for="filter_ao" class="lbl">AO / Marketing</label>
                <select id="filter_ao" class="inp truncate" onchange="fetchTop50Data()">
                    <option value="">Semua AO</option>
                </select>
            </div>

            <div class="flex items-center gap-1.5 md:gap-2 mt-auto w-full sm:w-auto shrink-0">
                <button type="submit" class="btn-icon bg-blue-600 hover:bg-blue-700 text-white shadow-sm flex-1 sm:flex-none" title="Cari Data">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    
                </button>

                <button type="button" onclick="exportTop50Excel()" class="btn-icon bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm flex-1 sm:flex-none px-3" title="Download Excel">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    
                </button>
            </div>
        </form>
    </div>
  </div>

  <div id="loadingTop50" class="hidden flex items-center justify-center gap-2 text-xs md:text-sm text-blue-600 font-bold mb-2 py-4 flex-none uppercase tracking-widest">
    <div class="animate-spin h-6 w-6 border-4 border-blue-200 border-t-blue-600 rounded-full"></div>
    <span>Memuat Realisasi...</span>
  </div>

  <div id="top50Scroller" class="flex-1 min-h-0 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm relative">
    <div id="top50ScrollerInner" class="h-full overflow-auto custom-scrollbar">
      <table id="tabelTop50" class="w-max min-w-full text-xs text-left text-slate-700 border-separate border-spacing-0 table-fixed">
        <thead class="uppercase bg-slate-100 text-slate-600 font-bold sticky top-0 z-30 shadow-sm text-[10px] md:text-xs tracking-wider">
          <tr>
            <th class="px-2 md:px-4 py-3 sticky left-0 z-40 bg-slate-100 border-b border-r border-slate-200 text-center w-[40px] md:w-[50px]">NO</th>
            
            <th class="px-3 md:px-4 py-3 sticky left-[40px] md:left-[50px] z-40 bg-slate-100 border-b border-r border-slate-200 w-[180px] md:w-[250px] shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] cursor-pointer hover:bg-slate-200 hover:text-blue-700 transition select-none" onclick="sortTop50('nama_nasabah')" title="Klik untuk urutkan Nama">
                NAMA NASABAH <span class="text-[10px] ml-1 opacity-50">⇅</span>
            </th>
            
            <th class="px-2 md:px-4 py-3 bg-slate-100 border-b border-slate-200 border-r text-center w-[50px] md:w-[60px]">CAB</th>
            <th class="px-3 md:px-4 py-3 bg-slate-100 border-b border-slate-200 border-r text-center text-blue-700 w-[120px] md:w-[150px]">AO</th>
            <th class="px-3 md:px-4 py-3 bg-slate-100 border-b border-slate-200 border-r text-center w-[110px] md:w-[130px]">NO REK</th>
            
            <th class="px-3 md:px-4 py-3 bg-emerald-50 text-emerald-800 border-b border-emerald-200 border-r cursor-pointer hover:bg-emerald-100 transition select-none w-[120px] md:w-[140px] text-right" onclick="sortTop50('plafond')" title="Klik untuk urutkan Plafond">
                PLAFOND <span class="text-[10px] ml-1 opacity-50">⇅</span>
            </th>
            
            <th class="px-3 md:px-4 py-3 bg-slate-100 border-b border-slate-200 border-r text-center w-[90px] md:w-[100px]">REALISASI</th>
            <th class="px-3 md:px-4 py-3 bg-slate-100 border-b border-slate-200 border-r text-center w-[90px] md:w-[100px]">JT TEMPO</th>
            <th class="px-3 md:px-4 py-3 bg-slate-100 border-b border-slate-200 w-[200px] md:w-[300px]">ALAMAT</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white"></tbody>
      </table>
    </div>
  </div>
</div>

<style>
  /* INPUT & RESPONSIVE */
  .inp { border:1px solid #cbd5e1; border-radius:8px; padding:0 8px; font-size:11px; background:#fff; height:36px; transition:all 0.2s; outline:none; color: #334155; }
  .inp:focus { border-color:#2563eb; box-shadow:0 0 0 2px rgba(37,99,235,0.1); }
  .inp:disabled { background: #f1f5f9; color: #64748b; font-weight: 700; cursor: not-allowed; }
  .lbl { font-size:9px; color:#64748b; font-weight:700; margin-bottom:2px; text-transform:uppercase; letter-spacing:0.05em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;}
  
  @media (min-width: 768px) {
      .inp { font-size: 13px; height: 38px; padding: 0 12px;}
      .lbl { font-size: 10px; margin-bottom: 4px; }
  }

  /* HIDE DATEPICKER ICON */
  input[type="date"]::-webkit-inner-spin-button, input[type="date"]::-webkit-calendar-picker-indicator { display: none; -webkit-appearance: none; }
  input[type="date"] { -moz-appearance: textfield; }

  .btn-icon { height:36px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; border:none; cursor:pointer; transition:all 0.2s; }
  .btn-icon:hover { transform:translateY(-1px); }
  @media (min-width: 768px) { .btn-icon { height: 38px; width: 38px; } }

  /* SCROLLBAR */
  .custom-scrollbar::-webkit-scrollbar { width:6px; height:6px; }
  .custom-scrollbar::-webkit-scrollbar-track { background:#f1f5f9; border-radius: 8px;}
  .custom-scrollbar::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:8px; }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover { background:#94a3b8; }
  
  .animate-fade-in { animation: fadeIn 0.3s ease-in forwards; }
  @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
  
  /* TABLE HOVER */
  tbody tr:hover td { background-color: #f8fafc; }
  tbody tr:hover td.sticky { background-color: #f8fafc; }
</style>

<script>
  /* ===== CONFIG ===== */
  const API_KREDIT_URL = './api/kredit/'; 
  const API_KODE_URL   = './api/kode/'; 
  const API_DATE_URL   = './api/date/';
  
  let top50DataRaw = [];
  let top50SortKey = 'plafond';
  let top50SortAsc = false;

  const fmtRupiah = n => new Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 }).format(+n||0);
  const fmtDateIndo = d => { 
      if(!d || d === '0000-00-00') return "-"; 
      const date = new Date(d); 
      return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }); 
  };

  /* ===== INITIALIZATION ===== */
  window.addEventListener('DOMContentLoaded', async () => {
    // 1. Ambil User Login
    const user = (window.getUser && window.getUser()) || null;
    const userKode = (user && user.kode) ? String(user.kode).padStart(3,'0') : '000';
    window.currentUser = { kode: userKode };

    // 2. Load Dropdown Kantor (Kunci jika Cabang)
    await populateKantor(userKode);

    // 3. Ambil Tanggal Default
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

    // 4. Load AO & Fetch Data
    await loadAODropdown();
    fetchTop50Data();
  });

  async function getLastHarianData() {
    try { const res = await fetch(API_DATE_URL); const json = await res.json(); return json.data || null; } catch { return null; }
  }

  /* ===== 1. POPULATE KANTOR ===== */
  async function populateKantor(userKode) {
      const el = document.getElementById('filter_kantor');

      if (userKode !== '000' && userKode !== '099') {
          el.innerHTML = `<option value="${userKode}">CABANG ${userKode}</option>`;
          el.value = userKode;
          el.disabled = true;
          return;
      }

      try {
          const r = await fetch(API_KODE_URL, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ type: 'kode_kantor' }) });
          const j = await r.json();
          let h = '<option value="">KONSOLIDASI</option>';
          if(j.data) {
              j.data.filter(x => x.kode_kantor !== '000').forEach(x => { 
                  h += `<option value="${x.kode_kantor}">${x.kode_kantor} - ${x.nama_kantor}</option>`; 
              });
          }
          el.innerHTML = h;
          el.disabled = false;
      } catch { el.innerHTML = '<option value="">KONSOLIDASI</option>'; }
  }

  /* ===== 2. LOGIC GANTI CABANG -> LOAD AO -> REFRESH DATA ===== */
  async function onBranchChange() {
      await loadAODropdown();
      fetchTop50Data();
  }

  async function loadAODropdown() {
      const elAO = document.getElementById('filter_ao');
      const branch = document.getElementById('filter_kantor').value;
      
      elAO.innerHTML = '<option value="">Memuat AO...</option>';
      elAO.disabled = true;
      
      try {
          const payload = { type: 'kode_ao_kredit', kode_kantor: branch };
          const r = await fetch(API_KODE_URL, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload) });
          const j = await r.json();
          
          let h = '<option value="">Semua AO</option>';
          if(j.data && Array.isArray(j.data)) {
              j.data.forEach(x => { 
                  const rawName = x.nama_ao || x.kode_group2;
                  const shortName = rawName.split(' ').slice(0,2).join(' ');
                  h += `<option value="${x.kode_group2}">${x.kode_group2} - ${shortName}</option>`; 
              });
          }
          elAO.innerHTML = h;
      } catch(err) { 
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
    const judul   = document.getElementById('judulMonitoring');
    
    // LOGIC JUDUL: Tambah "(Top 50)" jika Konsolidasi
    if(!kode_kantor || kode_kantor === '') {
        judul.innerText = "Monitoring Realisasi Kredit (Top 50)";
    } else {
        judul.innerText = "Monitoring Realisasi Kredit";
    }

    loading.classList.remove("hidden");
    summary.classList.add("hidden"); 
    tbody.innerHTML = `<tr><td colspan="9" class="text-center py-16 text-slate-400 italic">Sedang menyusun data...</td></tr>`;

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
      sortTop50(top50SortKey, true); // Render & Sort Auto
    })
    .catch(err => {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center py-12 text-red-500 font-bold tracking-widest uppercase">Gagal memuat data</td></tr>`;
    })
    .finally(() => loading.classList.add("hidden"));
  }

  function renderTop50Table(data) {
    const tbody = document.querySelector("#tabelTop50 tbody");
    const summary = document.getElementById('summaryWrap');
    tbody.innerHTML = "";

    if (data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="9" class="text-center py-16 text-slate-500 font-medium">Tidak ada data realisasi pada periode ini.</td></tr>`;
      document.getElementById('sum_noa').textContent = "0";
      document.getElementById('sum_realisasi').textContent = "Rp 0";
      summary.classList.remove('hidden');
      return;
    }

    // HITUNG SUMMARY
    let totalNoa = data.length;
    let totalNominal = data.reduce((acc, row) => acc + (parseFloat(row.plafond) || 0), 0);

    document.getElementById('sum_noa').textContent = new Intl.NumberFormat('id-ID').format(totalNoa);
    document.getElementById('sum_realisasi').textContent = "Rp " + fmtRupiah(totalNominal);
    summary.classList.remove('hidden');

    let html = '';
    // Styling width disamakan dengan THEAD agar sinkron di HP
    data.forEach((row, index) => {
      const aoName = (row.nama_ao || '-').split(' ').slice(0, 2).join(' ');

      html += `
        <tr class="border-b border-slate-100 transition hover:bg-blue-50/50 group h-[40px]">
          <td class="px-2 md:px-4 py-2 text-center sticky left-0 z-20 bg-white group-hover:bg-blue-50/50 border-r border-slate-100 font-mono text-xs text-slate-500">
            ${index + 1}
          </td>
          <td class="px-3 md:px-4 py-2 sticky left-[40px] md:left-[50px] z-20 bg-white group-hover:bg-blue-50/50 border-r border-slate-100 font-semibold text-slate-700 truncate" title="${row.nama_nasabah}">
            ${row.nama_nasabah || "-"}
          </td>
          <td class="px-2 md:px-4 py-2 border-r border-slate-100 text-center font-mono text-xs text-slate-500">
            ${row.kode_cabang || "-"}
          </td>
          <td class="px-3 md:px-4 py-2 border-r border-slate-100 text-center text-xs font-bold text-blue-700 truncate" title="${aoName}">
            ${aoName}
          </td>
          <td class="px-3 md:px-4 py-2 border-r border-slate-100 font-mono text-xs text-slate-500 text-center">
            ${row.no_rekening || "-"}
          </td>
          <td class="px-3 md:px-4 py-2 border-r border-emerald-100 text-right font-bold text-emerald-800 bg-emerald-50/30">
            ${fmtRupiah(row.plafond)}
          </td>
          <td class="px-3 md:px-4 py-2 border-r border-slate-100 text-center text-xs text-slate-600">
            ${fmtDateIndo(row.tgl_realisasi)}
          </td>
          <td class="px-3 md:px-4 py-2 border-r border-slate-100 text-center text-xs text-slate-500">
            ${fmtDateIndo(row.tgl_jatuh_tempo)}
          </td>
          <td class="px-3 md:px-4 py-2 text-xs text-slate-500 truncate" title="${row.alamat}">
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
  
  /* ===== 5. EXPORT EXCEL (RAW NUMBER) ===== */
  window.exportTop50Excel = function() {
      if(top50DataRaw.length === 0) return alert("Tidak ada data untuk didownload.");

      let table = `<table border="1">
          <thead>
              <tr>
                  <th style="background:#f1f5f9;">NO</th>
                  <th style="background:#f1f5f9;">NAMA NASABAH</th>
                  <th style="background:#f1f5f9;">CABANG</th>
                  <th style="background:#f1f5f9;">AO</th>
                  <th style="background:#f1f5f9;">NO REKENING</th>
                  <th style="background:#dcfce7;">PLAFOND</th>
                  <th style="background:#f1f5f9;">TGL REALISASI</th>
                  <th style="background:#f1f5f9;">JATUH TEMPO</th>
                  <th style="background:#f1f5f9;">ALAMAT</th>
              </tr>
          </thead>
          <tbody>`;

      top50DataRaw.forEach((row, index) => {
          table += `<tr>
              <td>${index + 1}</td>
              <td>${row.nama_nasabah || ''}</td>
              <td style="mso-number-format:'\\@'">${row.kode_cabang || ''}</td>
              <td>${row.nama_ao || ''}</td>
              <td style="mso-number-format:'\\@'">${row.no_rekening || ''}</td>
              <td>${Number(row.plafond || 0)}</td>
              <td>${row.tgl_realisasi || ''}</td>
              <td>${row.tgl_jatuh_tempo || ''}</td>
              <td>${row.alamat || ''}</td>
          </tr>`;
      });
      table += `</tbody></table>`;

      const blob = new Blob([table], { type: 'application/vnd.ms-excel' });
      const a = document.createElement('a'); a.href = URL.createObjectURL(blob);
      
      const tglAwal = document.getElementById("tgl_awal").value;
      const tglAkhir = document.getElementById("tgl_akhir").value;
      const jdl = document.getElementById('judulMonitoring').innerText.replace(/\s+/g, '_');
      
      a.download = `${jdl}_${tglAwal}_sd_${tglAkhir}.xls`; a.click();
  }

</script>