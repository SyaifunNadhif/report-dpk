<div class="w-full h-[calc(100vh-80px)] bg-slate-50 flex justify-center font-sans text-slate-800 px-2 md:px-4 py-4 md:py-6">
  
  <div class="w-full max-w-6xl flex flex-col h-full">
    
    <div class="flex-none mb-3 md:mb-4">
      <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-3 md:gap-4 mb-3 md:mb-4 border-b border-slate-200 pb-3 md:pb-4">
          
          <div class="shrink-0">
              <h1 class="title text-lg md:text-2xl font-bold flex items-center gap-2 text-slate-800 mb-2">
                  <span class="p-1.5 md:p-2 bg-blue-600 text-white rounded-lg shadow-sm text-sm">
                      <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                  </span>
                  <span id="judulMonitoring">Rekap Realisasi Per AO</span>
              </h1>
              
              <div id="summaryWrap" class="flex flex-wrap items-center gap-2 text-xs md:text-sm animate-fade-in hidden">
                  <div class="px-3 py-1 bg-blue-50 text-blue-800 rounded-full border border-blue-100 font-semibold shadow-sm">
                      Total AO: <span id="sum_ao" class="font-bold text-blue-900 ml-1">0</span>
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

              <div class="flex items-center gap-1.5 md:gap-2 mt-auto w-full sm:w-auto shrink-0">
                  <button type="submit" class="btn-icon bg-blue-600 hover:bg-blue-700 text-white shadow-sm flex-1 sm:flex-none px-4 flex items-center gap-2" title="Cari Data">
                      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                      <span class="font-bold text-xs uppercase tracking-wider hidden md:inline">Cari</span>
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

    <div class="flex-1 min-h-0 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm relative">
      <div id="top50ScrollerInner" class="h-full overflow-auto custom-scrollbar">
        <table id="tabelTop50" class="w-full text-xs text-left text-slate-700 border-separate border-spacing-0">
          <thead class="uppercase bg-slate-50 text-slate-600 font-bold sticky top-0 z-30 shadow-sm text-[10px] md:text-xs tracking-wider">
            <tr>
              <th class="px-3 md:px-4 py-3 sticky left-0 z-40 bg-slate-50 border-b border-r border-slate-200 text-center w-[50px]">NO</th>
              <th class="px-3 md:px-4 py-3 bg-slate-50 border-b border-r border-slate-200 text-center w-[90px]">CABANG</th>
              <th class="px-3 md:px-4 py-3 bg-slate-50 border-b border-r border-slate-200 text-center w-[120px]">KODE AO</th>
              <th class="px-3 md:px-4 py-3 bg-slate-50 border-b border-r border-slate-200 w-auto">NAMA AO (MARKETING)</th>
              <th class="px-3 md:px-4 py-3 bg-slate-50 border-b border-r border-slate-200 text-center w-[120px]">TOTAL NOA</th>
              <th class="px-4 py-3 bg-emerald-50 text-emerald-800 border-b border-emerald-200 text-right w-[200px]">TOTAL REALISASI</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white"></tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<style>
  .inp { border:1px solid #cbd5e1; border-radius:8px; padding:0 8px; font-size:11px; background:#fff; height:36px; transition:all 0.2s; outline:none; color: #334155; }
  .inp:focus { border-color:#2563eb; box-shadow:0 0 0 2px rgba(37,99,235,0.1); }
  .inp:disabled { background: #f1f5f9; color: #64748b; font-weight: 700; cursor: not-allowed; }
  .lbl { font-size:9px; color:#64748b; font-weight:700; margin-bottom:2px; text-transform:uppercase; letter-spacing:0.05em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;}
  
  @media (min-width: 768px) {
      .inp { font-size: 13px; height: 38px; padding: 0 12px;}
      .lbl { font-size: 10px; margin-bottom: 4px; }
  }
  input[type="date"]::-webkit-inner-spin-button, input[type="date"]::-webkit-calendar-picker-indicator { display: none; -webkit-appearance: none; }
  input[type="date"] { -moz-appearance: textfield; }

  .btn-icon { height:36px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; border:none; cursor:pointer; transition:all 0.2s; }
  .btn-icon:hover { transform:translateY(-1px); }
  @media (min-width: 768px) { .btn-icon { height: 38px; } }

  .custom-scrollbar::-webkit-scrollbar { width:6px; height:6px; }
  .custom-scrollbar::-webkit-scrollbar-track { background:#f1f5f9; border-radius: 8px;}
  .custom-scrollbar::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:8px; }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover { background:#94a3b8; }
  
  .animate-fade-in { animation: fadeIn 0.3s ease-in forwards; }
  @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
  tbody tr:hover td { background-color: #f8fafc; }
</style>

<script>
  /* ===== CONFIG ===== */
  const API_KREDIT_URL = './api/kredit/'; 
  const API_KODE_URL   = './api/kode/'; 
  
  let top50DataRaw = [];

  const fmtRupiah = n => new Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 }).format(+n||0);

  function getYesterdayDate() {
      const today = new Date();
      today.setDate(today.getDate() - 1);
      const yyyy = today.getFullYear();
      const mm = String(today.getMonth() + 1).padStart(2, '0');
      const dd = String(today.getDate()).padStart(2, '0');
      return `${yyyy}-${mm}-${dd}`;
  }

  function getFirstDayOfMonth() {
      const today = new Date();
      today.setDate(today.getDate() - 1);
      const yyyy = today.getFullYear();
      const mm = String(today.getMonth() + 1).padStart(2, '0');
      return `${yyyy}-${mm}-01`;
  }

  /* ===== INITIALIZATION ===== */
  window.addEventListener('DOMContentLoaded', async () => {
    const user = (window.getUser && window.getUser()) || null;
    const userKode = (user && user.kode) ? String(user.kode).padStart(3,'0') : '000';
    window.currentUser = { kode: userKode };

    await populateKantor(userKode);

    document.getElementById("tgl_akhir").value = getYesterdayDate();
    document.getElementById("tgl_awal").value  = getFirstDayOfMonth();

    fetchTop50Data();
  });

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
          let h = '<option value="konsolidasi">KONSOLIDASI (SEMUA)</option>';
          if(j.data) {
              j.data.filter(x => x.kode_kantor !== '000').forEach(x => { 
                  h += `<option value="${x.kode_kantor}">${x.kode_kantor} - ${x.nama_kantor}</option>`; 
              });
          }
          el.innerHTML = h;
          el.disabled = false;
      } catch { el.innerHTML = '<option value="konsolidasi">KONSOLIDASI (SEMUA)</option>'; }
  }

  function onBranchChange() { fetchTop50Data(); }

  /* ===== 3. FETCH & RENDER TABLE ===== */
  function fetchTop50Data() {
    const tgl_awal  = document.getElementById("tgl_awal").value;
    const tgl_akhir = document.getElementById("tgl_akhir").value;
    const kode_kantor = document.getElementById("filter_kantor").value;

    const loading = document.getElementById("loadingTop50");
    const tbody   = document.querySelector("#tabelTop50 tbody");
    const summary = document.getElementById('summaryWrap');
    const judul   = document.getElementById('judulMonitoring');
    
    if(!kode_kantor || kode_kantor === 'konsolidasi' || kode_kantor === '000') {
        judul.innerText = "Top 10 AO Terbaik (Konsolidasi)";
    } else {
        judul.innerText = `Rekap AO Cabang ${kode_kantor}`;
    }

    loading.classList.remove("hidden");
    summary.classList.add("hidden"); 
    tbody.innerHTML = `<tr><td colspan="6" class="text-center py-16 text-slate-400 italic">Sedang menyusun data...</td></tr>`;

    const payload = { 
        type: "top realisasi", 
        closing_date: tgl_awal, 
        harian_date: tgl_akhir,
        kode_kantor: kode_kantor
    };

    fetch(API_KREDIT_URL, {
      method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(res => {
      top50DataRaw = Array.isArray(res.data) ? res.data : [];
      renderTop50Table(top50DataRaw);
    })
    .catch(err => {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-12 text-red-500 font-bold tracking-widest uppercase">Gagal memuat data</td></tr>`;
    })
    .finally(() => loading.classList.add("hidden"));
  }

  function renderTop50Table(data) {
    const tbody = document.querySelector("#tabelTop50 tbody");
    const summary = document.getElementById('summaryWrap');
    tbody.innerHTML = "";

    if (data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6" class="text-center py-16 text-slate-500 font-medium">Tidak ada data realisasi pada periode ini.</td></tr>`;
      document.getElementById('sum_ao').textContent = "0";
      document.getElementById('sum_realisasi').textContent = "Rp 0";
      summary.classList.remove('hidden');
      return;
    }

    let totalAO = data.length;
    let totalNominal = data.reduce((acc, row) => acc + (parseFloat(row.total_realisasi) || 0), 0);

    document.getElementById('sum_ao').textContent = new Intl.NumberFormat('id-ID').format(totalAO);
    document.getElementById('sum_realisasi').textContent = "Rp " + fmtRupiah(totalNominal);
    summary.classList.remove('hidden');

    let html = '';
    data.forEach((row, index) => {
      html += `
        <tr class="transition hover:bg-blue-50/50 group h-[40px]">
          <td class="px-3 md:px-4 py-2 text-center sticky left-0 z-20 bg-white group-hover:bg-blue-50/50 border-b border-r border-slate-100 font-mono text-xs text-slate-500">
            ${index + 1}
          </td>
          <td class="px-3 md:px-4 py-2 text-center border-b border-r border-slate-100 font-mono text-xs text-slate-500">
            ${row.kode_cabang || "-"}
          </td>
          <td class="px-3 md:px-4 py-2 text-center border-b border-r border-slate-100 font-mono text-xs font-bold text-slate-700">
            ${row.kode_ao || "-"}
          </td>
          <td class="px-3 md:px-4 py-2 border-b border-r border-slate-100 font-semibold text-blue-700 truncate max-w-[250px]">
            ${row.nama_ao || "-"}
          </td>
          <td class="px-3 md:px-4 py-2 text-center border-b border-r border-slate-100 font-bold text-slate-700">
            ${new Intl.NumberFormat('id-ID').format(row.total_noa || 0)} <span class="text-[9px] text-slate-400 font-normal ml-1">Debitur</span>
          </td>
          <td class="px-4 py-2 text-right border-b border-emerald-100 font-bold text-emerald-800 bg-emerald-50/30">
            ${fmtRupiah(row.total_realisasi)}
          </td>
        </tr>
      `;
    });
    tbody.innerHTML = html;
  }

  /* ===== 5. EXPORT EXCEL ===== */
  window.exportTop50Excel = function() {
      if(top50DataRaw.length === 0) return alert("Tidak ada data untuk didownload.");

      let table = `<table border="1">
          <thead>
              <tr>
                  <th style="background:#f1f5f9;">NO</th>
                  <th style="background:#f1f5f9;">CABANG</th>
                  <th style="background:#f1f5f9;">KODE AO</th>
                  <th style="background:#f1f5f9;">NAMA AO</th>
                  <th style="background:#f1f5f9;">TOTAL NOA</th>
                  <th style="background:#dcfce7;">TOTAL REALISASI</th>
              </tr>
          </thead>
          <tbody>`;

      top50DataRaw.forEach((row, index) => {
          table += `<tr>
              <td>${index + 1}</td>
              <td style="mso-number-format:'\\@'">${row.kode_cabang || ''}</td>
              <td style="mso-number-format:'\\@'">${row.kode_ao || ''}</td>
              <td>${row.nama_ao || ''}</td>
              <td>${row.total_noa || 0}</td>
              <td>${Number(row.total_realisasi || 0)}</td>
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