<style>
  :root { --primary: #4f46e5; --bg: #f8fafc; --text: #334155; }
  body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text); overflow: hidden; }
  
  .inp { 
      box-sizing: border-box;
      border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 0 0.5rem; 
      font-size: 12px; background: #fff; height: 36px; cursor: pointer; 
      outline: none; transition: border 0.2s; min-width: 0; font-weight: 600;
  }
  .inp:focus { border-color: var(--primary); box-shadow: 0 0 0 2px #c7d2fe; }
  .inp:disabled { background-color: #f1f5f9; color: #64748b; cursor: not-allowed; border-color: #e2e8f0; }
  
  input[type="date"] { position: relative; cursor: pointer; }
  input[type="date"]::-webkit-inner-spin-button,
  input[type="date"]::-webkit-calendar-picker-indicator {
      position: absolute; top: 0; left: 0; right: 0; bottom: 0;
      width: 100%; height: 100%; opacity: 0; cursor: pointer;
  }

  #usiaScroller { 
      --usia_headH: 40px; 
      overflow: auto; height: 100%; border-radius: 8px; 
      border: 1px solid #e2e8f0; background: white; position: relative;
      -webkit-overflow-scrolling: touch; 
  }
  
  table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 12px; }
  th, td { white-space: nowrap; padding: 8px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
  tr:hover td { background-color: #f8fafc; }

  /* Header Usia Kredit */
  thead th { 
      position: sticky; top: 0; z-index: 60; 
      background: #e2e8f0; color: #1e293b; font-weight: 800; 
      text-transform: uppercase; border-bottom: 2px solid #cbd5e1;
      font-size: 11px; letter-spacing: 0.05em;
  }
  
  .col-kategori { position: sticky; left: 0; z-index: 45; background: white; border-right: 1px solid #e2e8f0; box-shadow: 2px 0 5px rgba(0,0,0,0.03); min-width: 150px; font-weight: bold;}
  thead th.col-kategori { z-index: 70; background: #e2e8f0; }

  .sticky-total td { 
      position: sticky; top: var(--usia_headH); z-index: 55; 
      background: #f4f7fb; font-weight: 800; border-bottom: 2px solid #bfdbfe; 
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); 
  }
  .sticky-total td.col-kategori { z-index: 65; background: #f4f7fb; border-right: 1px solid #bfdbfe; }

  @media (max-width: 767px) {
      .col-kategori { left: 0 !important; z-index: 45 !important; min-width: 120px; white-space: normal; line-height: 1.2; }
      thead th.col-kategori { z-index: 70 !important; }
      .sticky-total td.col-kategori { z-index: 65 !important; }
  }
</style>

<div class="max-w-[1600px] mx-auto px-3 md:px-4 py-4 h-[calc(100vh-80px)] md:h-[calc(100vh-120px)] flex flex-col">
  
  <div class="flex flex-col xl:flex-row xl:items-end justify-between gap-3 mb-4 shrink-0">
    <div class="flex items-start justify-between w-full xl:w-auto">
        <div>
            <h1 class="text-xl md:text-2xl font-bold flex items-center gap-2 text-slate-800">
                <span class="bg-indigo-600 text-white p-1.5 rounded-lg text-sm shadow-sm">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </span> 
                <span>Rekap Usia Kredit</span>
            </h1>
            <p class="text-[10px] md:text-xs text-slate-500 mt-1 ml-1 font-medium" id="lbl_filter_aktif">*Posisi Harian: Menunggu data...</p>
        </div>

        <button id="btnToggleUsiaFilter" class="xl:hidden flex items-center gap-1.5 px-3 py-1.5 border border-gray-300 rounded-lg bg-white text-sm font-semibold text-slate-700 shadow-sm transition">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
            Filter
        </button>
    </div>

    <div id="panelFilterUsia" class="hidden xl:block bg-white border border-gray-200 rounded-xl p-3 shadow-sm w-full xl:w-auto transition-all">
        <form id="formFilterUsia" class="flex flex-col md:flex-row items-end gap-2 md:gap-3 w-full">
            
            <div class="flex flex-col w-full md:w-[130px]">
                <label class="text-[9px] font-extrabold text-slate-500 uppercase ml-1 mb-1 tracking-wider">TANGGAL</label>
                <input type="date" id="harian_date_usia" class="inp shadow-sm text-slate-700" required>
            </div>

            <div class="flex flex-col w-full md:w-[200px]">
                <label class="text-[9px] font-extrabold text-slate-500 uppercase ml-1 mb-1 tracking-wider">CABANG</label>
                <select id="opt_kantor_usia" class="inp text-slate-700 shadow-sm truncate" onchange="handleCabangChange()">
                    <option value="">ALL | SEMUA CABANG</option>
                </select>
            </div>

            <div class="flex flex-col w-full md:w-[160px]">
                <label id="lbl_sub_usia" class="text-[9px] font-extrabold text-slate-500 uppercase ml-1 mb-1 tracking-wider">KORWIL</label>
                <select id="opt_sub_usia" class="inp text-slate-700 shadow-sm truncate" onchange="triggerAutoRefresh()">
                    <option value="">ALL KORWIL</option>
                    <option value="SEMARANG">SEMARANG</option>
                    <option value="SOLO">SOLO</option>
                    <option value="BANYUMAS">BANYUMAS</option>
                    <option value="PEKALONGAN">PEKALONGAN</option>
                </select>
            </div>
            
            <div class="flex gap-2 shrink-0 mt-2 md:mt-0 w-full md:w-auto">
                <button type="submit" class="flex-1 md:flex-none bg-indigo-600 hover:bg-indigo-700 text-white h-9 px-4 rounded-lg font-bold text-sm shadow-sm flex items-center justify-center gap-2 transition">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <span class="hidden md:inline">CARI</span>
                </button>
                <button type="button" onclick="exportUsiaExcel()" class="bg-emerald-600 hover:bg-emerald-700 text-white h-9 px-3 md:w-11 rounded-lg shadow-sm flex items-center justify-center transition" title="Download Excel">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    <span class="md:hidden ml-1 font-bold text-xs">EXCEL</span>
                </button>
            </div>
        </form>
    </div>
  </div>

  <div class="flex-1 min-h-0 relative flex flex-col bg-white rounded-xl shadow-sm border border-slate-200">
    <div id="loadingUsia" class="hidden absolute inset-0 bg-white/80 z-[100] flex flex-col items-center justify-center text-indigo-600 font-bold text-sm backdrop-blur-sm rounded-xl">
        <div class="animate-spin h-8 w-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full mb-2"></div>
        Kalkulasi Umur Kredit...
    </div>

    <div class="table-wrapper" id="usiaScroller">
      <table id="tabelUsiaKredit">
        <thead id="theadUsia">
          <tr>
            <th class="col-kategori text-left">KATEGORI UMUR</th>
            <th class="text-right min-w-[100px] text-blue-800">TOTAL PORTOFOLIO</th>
            <th class="text-right min-w-[100px] text-green-700">LANCAR (L)</th>
            <th class="text-right min-w-[100px] text-slate-600">DPK (DP)</th>
            <th class="text-right min-w-[100px] text-red-600">NPL (KL, D, M)</th>
            <th class="text-right min-w-[110px] border-l border-slate-300 text-orange-600">LEWAT JATUH TEMPO</th>
            <th class="text-center min-w-[80px] border-l border-slate-300">% NPL</th>
          </tr>
        </thead>
        <tbody id="totalUsia"></tbody>
        <tbody id="bodyUsia"></tbody>
      </table>
    </div>
  </div>
</div>

<script>
  const API_KREDIT = './api/kredit/'; 
  const API_KODE   = './api/kode/';
  const API_DATE   = './api/date/';

  const nf = new Intl.NumberFormat('id-ID');
  const fmt = n => nf.format(Number(n||0));
  const fmt2 = x => (x == null || x === '' ? '0.00' : Number(x).toFixed(2));

  window.usiaDataRaw = [];
  window.usiaGtRaw = null;

  document.getElementById('btnToggleUsiaFilter').addEventListener('click', function() {
      document.getElementById('panelFilterUsia').classList.toggle('hidden');
  });

  function updateStickyHeaderUsia() {
      const thead = document.getElementById('theadUsia');
      const scroller = document.getElementById('usiaScroller');
      if(thead && scroller) {
          scroller.style.setProperty('--usia_headH', (thead.offsetHeight - 1) + 'px');
      }
  }
  window.addEventListener('resize', updateStickyHeaderUsia);

  // ==========================================
  // LOGIC PEMBATASAN USER & UI DINAMIS
  // ==========================================
  window.addEventListener('DOMContentLoaded', async () => {
    // 1. Cek Data User
    const user = (window.getUser && window.getUser()) || null;
    let uKode = user?.kode ? String(user.kode).padStart(3,'0') : '000';
    if(uKode === '099') uKode = '000'; 

    const isPusat = (uKode === '000');
    const optKantor = document.getElementById('opt_kantor_usia');

    // 2. Setup Dropdown Cabang
    if (isPusat) {
        await loadCabangUsia();
        optKantor.value = ""; // Default ALL CABANG
    } else {
        optKantor.innerHTML = `<option value="${uKode}">CABANG ${uKode}</option>`;
        optKantor.value = uKode;
        optKantor.disabled = true; // Kunci Dropdown
        optKantor.classList.add('bg-slate-50');
    }

    // 3. Trigger perubahan UI untuk Sub-Dropdown (Korwil vs Kankas)
    // Parameter true disisipkan agar waktu render awal TIDAK fetch data double
    await handleCabangChange(true);

    // 4. Set Tanggal Default
    try { 
        const r = await fetch(API_DATE); 
        const j = await r.json();
        if (j.data) document.getElementById('harian_date_usia').value = j.data.last_created;
    } catch {
        document.getElementById('harian_date_usia').value = new Date().toISOString().split('T')[0];
    }
    
    // 5. Langsung Tarik Data
    fetchUsiaKredit();
  });

  // ==========================================
  // FUNGSI PERUBAHAN DROPDOWN DINAMIS (Auto-Refresh)
  // ==========================================
  async function handleCabangChange(isInit = false) {
      const cabangVal = document.getElementById('opt_kantor_usia').value;
      const lblSub = document.getElementById('lbl_sub_usia');
      const optSub = document.getElementById('opt_sub_usia');

      if (cabangVal === "" || cabangVal === "000") {
          // Jika ALL CABANG -> Dropdown 2 jadi KORWIL
          lblSub.innerText = "KORWIL";
          optSub.innerHTML = `
              <option value="">ALL KORWIL</option>
              <option value="SEMARANG">SEMARANG</option>
              <option value="SOLO">SOLO</option>
              <option value="BANYUMAS">BANYUMAS</option>
              <option value="PEKALONGAN">PEKALONGAN</option>
          `;
      } else {
          // Jika PILIH SPESIFIK CABANG -> Dropdown 2 jadi KANKAS
          lblSub.innerText = "KANKAS";
          optSub.innerHTML = '<option value="">ALL KANKAS</option>'; // Placeholder
          await loadKankasUsia(cabangVal);
      }

      // Jika bukan rendering awal, tembak auto-refresh!
      if (!isInit) {
          triggerAutoRefresh();
      }
  }

  function triggerAutoRefresh() {
      // Sembunyikan panel filter khusus di versi Mobile
      if(window.innerWidth < 1280) document.getElementById('panelFilterUsia').classList.add('hidden');
      fetchUsiaKredit();
  }

  // ==========================================
  // FETCH DROPDOWNS DATA
  // ==========================================
  async function loadCabangUsia() {
    const optKantor = document.getElementById('opt_kantor_usia');
    try {
        const res = await fetch(API_KODE, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({type:'kode_kantor'}) });
        const json = await res.json();
        let html = `<option value="">ALL | SEMUA CABANG</option>`;
        (json.data || []).filter(x => x.kode_kantor && x.kode_kantor !== '000')
            .sort((a,b) => String(a.kode_kantor).localeCompare(b.kode_kantor))
            .forEach(it => {
                html += `<option value="${String(it.kode_kantor).padStart(3,'0')}">${String(it.kode_kantor).padStart(3,'0')} - ${it.nama_kantor}</option>`;
            });
        optKantor.innerHTML = html;
    } catch(e){ optKantor.innerHTML = `<option value="">Error Load</option>`; }
  }

  async function loadKankasUsia(kodeCabang) {
      const optSub = document.getElementById('opt_sub_usia');
      try {
          const payload = { type: 'kode_kankas', kode_kantor: kodeCabang };
          const r = await fetch(API_KODE, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload) });
          const j = await r.json();
          
          let h = '<option value="">ALL KANKAS</option>';
          if(j.data && Array.isArray(j.data)) {
              j.data.forEach(x => { h += `<option value="${x.kode_group1}">${x.deskripsi_group1 || x.kode_group1}</option>`; });
          }
          optSub.innerHTML = h;
      } catch(err) { }
  }

  // ==========================================
  // FETCH & RENDER DATA
  // ==========================================
  // Trigger ketika menekan tombol submit form (CARI Tanggal)
  document.getElementById('formFilterUsia').addEventListener('submit', e => { 
      e.preventDefault(); 
      triggerAutoRefresh(); 
  });

  async function fetchUsiaKredit() {
      const loading = document.getElementById('loadingUsia');
      
      const cabangVal = document.getElementById('opt_kantor_usia').value;
      const subVal = document.getElementById('opt_sub_usia').value;

      // Routing parameter API (Cerdas menentukan isi sub dropdown)
      let reqKorwil = "";
      let reqKankas = "";

      if (cabangVal === "" || cabangVal === "000") {
          reqKorwil = subVal; // Sub Dropdown bertindak sebagai Korwil
      } else {
          reqKankas = subVal; // Sub Dropdown bertindak sebagai Kankas
      }

      const payload = { 
          type: "usia_kredit",
          harian_date: document.getElementById('harian_date_usia').value,
          kode_kantor: cabangVal,
          korwil: reqKorwil,
          kode_kankas: reqKankas
      };

      loading.classList.remove('hidden');
      
      try {
          const res = await fetch(API_KREDIT, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          const json = await res.json();
          
          if(json.status !== 200) throw new Error(json.message);

          window.usiaDataRaw = json.data?.data || [];
          window.usiaGtRaw = json.data?.grand_total || null;
          const meta = json.data?.meta || {};

          // Update Label Header
          document.getElementById('lbl_filter_aktif').innerHTML = `Filter: <span class="font-bold text-indigo-700">${meta.filter_aktif || 'ALL'}</span> | Per Tgl: <span class="font-bold text-slate-700">${meta.tanggal || payload.harian_date}</span>`;

          renderUsiaTotal(window.usiaGtRaw);
          renderUsiaTable(window.usiaDataRaw);

          setTimeout(updateStickyHeaderUsia, 50);
      } catch(e) { 
          document.getElementById('bodyUsia').innerHTML = `<tr><td colspan="7" class="text-center py-10 text-red-500 font-bold uppercase tracking-widest">${e.message || 'Error Load Data'}</td></tr>`;
          document.getElementById('totalUsia').innerHTML = '';
      } finally { loading.classList.add('hidden'); }
  }

  function renderUsiaTotal(gt) {
      const tbodyTotal = document.getElementById('totalUsia');
      tbodyTotal.innerHTML = '';
      if (!gt) return;
      tbodyTotal.innerHTML = `
        <tr class="sticky-total">
            <td class="col-kategori text-left text-slate-800 uppercase tracking-widest">GRAND TOTAL</td>
            <td class="text-right font-black text-blue-800 text-sm">${fmt(gt.total_os)} <div class="text-[10px] text-blue-600/70 font-semibold">${fmt(gt.total_noa)} NOA</div></td>
            <td class="text-right font-black text-green-700 text-sm">${fmt(gt.os_lancar)}</td>
            <td class="text-right font-black text-slate-700 text-sm">${fmt(gt.os_dpk)}</td>
            <td class="text-right font-black text-red-600 text-sm">${fmt(gt.os_npl)}</td>
            <td class="text-right font-black text-orange-600 text-sm border-l border-slate-300">${fmt(gt.os_lewat_jt)}</td>
            <td class="text-center font-black text-slate-800 text-sm border-l border-slate-300">${fmt2(gt.persen_npl)}%</td>
        </tr>`;
  }

  function renderUsiaTable(rows) {
      const tbody = document.getElementById('bodyUsia');
      tbody.innerHTML = '';
      if (rows.length === 0) {
          tbody.innerHTML = `<tr><td colspan="7" class="text-center py-12 text-slate-400 font-medium">Data tidak ditemukan pada tanggal tersebut.</td></tr>`;
          return;
      }
      
      let html = '';
      rows.forEach(r => {
          html += `
            <tr class="transition border-b h-[56px]">
                <td class="col-kategori font-bold text-slate-700 uppercase tracking-wide text-xs">${r.kategori}</td>
                <td class="text-right font-bold text-blue-700">${fmt(r.total_os)} <div class="text-[10px] text-slate-400 font-medium">${fmt(r.total_noa)} NOA</div></td>
                <td class="text-right font-semibold text-green-600">${fmt(r.os_lancar)}</td>
                <td class="text-right font-semibold text-slate-600">${fmt(r.os_dpk)}</td>
                <td class="text-right font-semibold text-red-500">${fmt(r.os_npl)}</td>
                <td class="text-right font-semibold text-orange-500 border-l border-slate-100">${fmt(r.os_lewat_jt)}</td>
                <td class="text-center font-extrabold border-l border-slate-100 ${r.persen_npl > 5 ? 'text-red-600 bg-red-50/30' : 'text-emerald-600'}">${fmt2(r.persen_npl)}%</td>
            </tr>`;
      });
      tbody.innerHTML = html;
  }

  function exportUsiaExcel() {
      const rows = window.usiaDataRaw || [];
      const gt = window.usiaGtRaw || null;
      if(rows.length === 0) return alert("Data Kosong!");

      let csv = "KATEGORI UMUR\tTOTAL PORTOFOLIO (OS)\tTOTAL NOA\tLANCAR (OS)\tDPK (OS)\tNPL (OS)\tLEWAT JATUH TEMPO (OS)\t% NPL\n";
      
      if(gt) {
          csv += `GRAND TOTAL\t${gt.total_os}\t${gt.total_noa}\t${gt.os_lancar}\t${gt.os_dpk}\t${gt.os_npl}\t${gt.os_lewat_jt}\t${gt.persen_npl}\n`;
      }
      
      rows.forEach(r => { 
          csv += `${r.kategori}\t${r.total_os}\t${r.total_noa}\t${r.os_lancar}\t${r.os_dpk}\t${r.os_npl}\t${r.os_lewat_jt}\t${r.persen_npl}\n`; 
      });
      
      const blob = new Blob([csv], { type: 'application/vnd.ms-excel' });
      const a = document.createElement('a');
      a.href = window.URL.createObjectURL(blob);
      a.download = `Rekap_Usia_Kredit_${document.getElementById('harian_date_usia').value}.xls`;
      a.click();
  }
</script>