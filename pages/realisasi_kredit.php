<style>
  :root { --primary: #2563eb; --bg: #f8fafc; --text: #334155; }
  body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text); overflow: hidden; }
  
  /* Input & Button Responsive */
  .inp { border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 0.4rem 0.5rem; font-size: 12px; background: #fff; width: 100%; height: 38px; cursor: pointer; transition: 0.2s;}
  .inp:focus { border-color: var(--primary); box-shadow: 0 0 0 2px rgba(37,99,235,0.1); }
  .inp:disabled { background-color: #f1f5f9; color: #475569; font-weight: 700; cursor: not-allowed; border-color: #e2e8f0; }
  .lbl { font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 2px; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;}
  
  @media (min-width: 768px) {
      .inp { padding: 0.4rem 0.75rem; font-size: 13px; }
      .lbl { font-size: 11px; margin-bottom: 4px; }
  }

  /* DATEPICKER FIX */
  input[type="date"]::-webkit-inner-spin-button,
  input[type="date"]::-webkit-calendar-picker-indicator { display: none; -webkit-appearance: none; }
  input[type="date"] { -moz-appearance: textfield; }

  .btn-icon { height: 38px; border-radius: 8px; background: var(--primary); color: white; border: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: 0.2s; font-weight: bold; font-size: 12px; text-transform: uppercase; padding: 0 12px; gap: 4px;}
  .btn-icon:hover { background: #1d4ed8; transform: translateY(-1px); }
  .btn-icon.bg-emerald-600:hover { background: #047857; }

  /* TABLE WRAPPER */
  .table-wrapper { 
      overflow: auto; 
      height: 100%; 
      border-radius: 8px; 
      border: 1px solid #e2e8f0; 
      background: white; 
      position: relative;
  }
  
  table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 12px; }
  
  /* Header Sticky Lapis 1 */
  th.head-row { position: sticky; top: 0; z-index: 50; background: #d9ead3; color: #1e293b; font-weight: 700; padding: 12px 10px; border-bottom: 2px solid #cbd5e1; text-transform: uppercase; }
  
  /* Total Sticky Lapis 2 (Nempel di bawah header) */
  #tabelRealisasi .sticky-total th { 
      position: sticky; 
      top: 41px; /* Sesuai tinggi header atasnya */
      z-index: 45; 
      background: #eff6ff !important; 
      font-weight: 800; 
      color: #1e40af;
      border-bottom: 2px solid #60a5fa; 
      padding-top: 10px; 
      padding-bottom: 10px;
      font-size: 13px;
      box-shadow: 0 4px 6px -2px rgba(0,0,0,0.05);
  }

  td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle; white-space: nowrap; }
  
  /* === RESPONSIVE STICKY COLUMN LOGIC === */
  .sticky-col { position: sticky; z-index: 30; background: white; border-right: 1px solid #e2e8f0; }
  th.head-row.sticky-col { z-index: 60; background: #d9ead3; }
  .sticky-total th.sticky-col { z-index: 55; background: #eff6ff !important; border-right: 1px solid #bfdbfe; }

  /* MOBILE VIEW (Default) */
  .col-kode { display: none; } /* Hide kode di mobile */
  .col-nama { left: 0 !important; min-width: 140px; }

  /* DESKTOP VIEW */
  @media (min-width: 768px) {
      .col-kode { display: table-cell; left: 0; width: 60px; min-width: 60px; }
      .col-nama { left: 60px !important; min-width: 180px; }
  }
  
  tr:hover td { background-color: #f8fafc; }
  .hidden { display: none !important; }
  .cell-action { cursor: pointer; color: var(--primary); font-weight: 600; text-decoration: none; transition: 0.2s;}
  .cell-action:hover { color: #1d4ed8; text-decoration: underline; }

  /* Animasi Modal */
  @keyframes scaleUp { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
  .animate-scale-up { animation: scaleUp 0.2s ease-out forwards; }
</style>

<script>
    // Inisialisasi User Login
    const user = (window.getUser && window.getUser()) || null;
    const userKode = (user?.kode ? String(user.kode).padStart(3,'0') : '000');
    window.currentUser = { kode: userKode };
</script>

<div class="max-w-7xl mx-auto px-2 md:px-4 py-4 h-[calc(100vh-120px)] flex flex-col">
  
  <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-3 mb-3">
    <div class="shrink-0 pl-1 md:pl-0">
      <h1 class="text-xl md:text-2xl font-bold flex items-center gap-2 text-slate-800">
        <span class="bg-blue-600 text-white p-1.5 rounded-lg shadow-sm text-sm">💳</span> 
        <span>Rekap Realisasi Kredit</span>
      </h1>
      <p class="text-[10px] md:text-xs text-slate-500 mt-1 md:mt-1.5 font-medium">*Run off : nom actual - nom m-1</p>
    </div>

    <form id="formFilterRealisasi" class="flex w-full lg:w-auto items-end gap-1.5 md:gap-2 bg-white p-2 rounded-xl border border-slate-200 shadow-sm shrink-0">
      
      <div class="w-[22%] md:w-[120px] shrink-0">
        <label class="lbl">Closing</label>
        <input type="date" id="closing_date_realisasi" class="inp" required onclick="try{this.showPicker()}catch(e){}">
      </div>
      
      <div class="w-[22%] md:w-[120px] shrink-0">
        <label class="lbl">Harian</label>
        <input type="date" id="harian_date_realisasi" class="inp" required onclick="try{this.showPicker()}catch(e){}">
      </div>
      
      <div class="flex-1 min-w-[80px] md:min-w-[180px]">
        <label class="lbl">Kantor</label>
        <select id="opt_kantor_realisasi" class="inp truncate"><option value="">Memuat...</option></select>
      </div>
      
      <div class="flex gap-1.5 shrink-0 mt-auto">
        <button type="submit" class="btn-icon w-9 md:w-[40px] px-0" title="Cari Data">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        </button>
        <button type="button" onclick="exportExcelRealisasi()" class="btn-icon bg-emerald-600 w-9 md:w-auto px-0 md:px-3" title="Download Excel Rekap">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
          <span class="hidden md:inline">EXCEL</span>
        </button>
      </div>

    </form>
  </div>

  <div class="flex-1 min-h-0 relative flex flex-col">
    <div id="loadingRealisasi" class="hidden absolute inset-0 bg-white/80 z-[100] flex flex-col items-center justify-center text-blue-600 font-bold text-sm tracking-widest uppercase">
        <div class="animate-spin h-10 w-10 border-4 border-blue-200 border-t-blue-600 rounded-full mb-3"></div>
        Memuat Data...
    </div>

    <div class="table-wrapper">
      <table id="tabelRealisasi">
        <thead>
          <tr>
            <th class="head-row col-kode sticky-col text-center">Kode</th>
            <th class="head-row col-nama sticky-col" id="thNamaUnit">NAMA KANTOR</th>
            <th class="head-row text-right">NOA Realisasi</th>
            <th class="head-row text-right">Total Realisasi</th>
            <th class="head-row text-right">Total Run Off</th>
            <th class="head-row text-right">Growth</th>
          </tr>
          <tr class="sticky-total" id="rowTotalAtas">
            </tr>
        </thead>
        <tbody id="bodyRealisasi"></tbody>
      </table>
    </div>
  </div>

</div>

<div id="modalDetailRealisasi" class="fixed inset-0 hidden bg-slate-900/60 backdrop-blur-sm z-[6000] items-center justify-center flex p-2 md:p-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl h-[90vh] flex flex-col overflow-hidden animate-scale-up">
    
    <div class="flex justify-between items-center p-3 md:p-4 border-b bg-slate-50 shrink-0">
      <h3 id="modalTitleRealisasi" class="font-bold text-base md:text-lg text-slate-800">Detail Realisasi</h3>
      <div class="flex items-center gap-2">
        <button onclick="exportExcelDetailRealisasi()" class="btn-icon bg-emerald-600 h-8 md:h-9 px-3 text-xs font-bold uppercase">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
          <span class="hidden md:inline ml-1">Excel</span>
        </button>
        <button onclick="closeModalRealisasi()" class="w-8 md:w-9 h-8 md:h-9 flex items-center justify-center rounded-lg bg-slate-200 hover:bg-red-500 hover:text-white text-slate-600 transition font-bold text-lg md:text-xl leading-none">&times;</button>
      </div>
    </div>
    
    <div class="flex-1 overflow-auto bg-slate-50 relative p-2 md:p-3">
        <div id="loadingModal" class="hidden absolute inset-0 bg-white/90 z-20 flex flex-col items-center justify-center text-blue-600 font-bold uppercase tracking-widest text-xs">
            <div class="animate-spin h-8 w-8 border-4 border-blue-200 border-t-blue-600 rounded-full mb-3"></div>
            Memuat Detail...
        </div>
        <table class="w-max min-w-full text-xs text-left text-slate-700 bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm table-fixed" id="tableModalContent">
            <thead class="bg-slate-100 font-bold uppercase text-slate-600 sticky top-0 shadow-sm z-10">
                <tr>
                    <th class="px-3 md:px-4 py-3 border-b border-r border-slate-200 w-28 md:w-32">Rekening</th>
                    <th class="px-3 md:px-4 py-3 border-b border-r border-slate-200 w-40 md:w-48">Nasabah</th>
                    <th class="px-3 md:px-4 py-3 border-b border-r border-slate-200 w-28 md:w-32 text-right">Plafond</th>
                    <th class="px-3 md:px-4 py-3 border-b border-r border-slate-200 w-28 md:w-32 text-center">Tgl Realisasi</th>
                    <th class="px-3 md:px-4 py-3 border-b border-r border-slate-200 w-32 md:w-48">Kankas</th>
                    <th class="px-3 md:px-4 py-3 border-b border-slate-200 w-28 md:w-32 text-blue-700">AO</th>
                </tr>
            </thead>
            <tbody id="modalBodyRealisasi" class="divide-y divide-slate-100 bg-white"></tbody>
        </table>
    </div>
  </div>
</div>

<script>
  // --- CONFIG ---
  const API_KREDIT = './api/kredit/'; 
  const API_KODE   = './api/kode/';
  const API_DATE   = './api/date/';
  
  const nf = new Intl.NumberFormat('id-ID');
  const fmt = n => nf.format(Number(n)||0);

  async function apiCall(url, options = {}) {
      const res = await fetch(url, options);
      if(!res.ok) throw new Error(`HTTP Error ${res.status}`);
      return res;
  }

  // --- INIT PAGE ---
  window.addEventListener('DOMContentLoaded', async () => {
      // 1. Load Semua Daftar Kantor Terlebih Dahulu
      await populateKantorOptionsRealisasi();

      // 2. Cek Siapa yang Login & Kunci Dropdown Jika Cabang
      const user = (window.getUser && window.getUser()) || null;
      const userKode = (user && user.kode) ? String(user.kode).padStart(3,'0') : '000';
      window.currentUser = { kode: userKode };
      
      const optKantor = document.getElementById('opt_kantor_realisasi');
      if (userKode !== '000') {
          optKantor.value = userKode; // Set value sesuai cabang
          optKantor.disabled = true;  // Kunci dropdown
      }

      // 3. Ambil Tanggal Default
      const d = await getLastHarianData(); 
      if (d) {
          document.getElementById('closing_date_realisasi').value = d.last_closing;
          document.getElementById('harian_date_realisasi').value  = d.last_created;
      } else {
          document.getElementById('harian_date_realisasi').value = new Date().toISOString().split('T')[0];
      }

      // 4. Tarik Data Utama
      fetchRealisasiData();
  });

  async function getLastHarianData(){
    try { const r = await apiCall(API_DATE); const j = await r.json(); return j.data || null; } catch{ return null; }
  }

  // --- POPULATE DROPDOWN (TARIK SEMUA DATA) ---
  async function populateKantorOptionsRealisasi(){
    const optKantor = document.getElementById('opt_kantor_realisasi');
    try {
        const res = await apiCall(API_KODE, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({type:'kode_kantor'}) });
        const json = await res.json();
        const list = Array.isArray(json.data) ? json.data : [];
        
        let html = `<option value="">KONSOLIDASI (SEMUA)</option>`;
        list.filter(x => x.kode_kantor && x.kode_kantor !== '000')
            .sort((a,b) => String(a.kode_kantor).localeCompare(b.kode_kantor))
            .forEach(it => { html += `<option value="${String(it.kode_kantor).padStart(3,'0')}">${String(it.kode_kantor).padStart(3,'0')} - ${it.nama_kantor}</option>`; });
        
        optKantor.innerHTML = html;
    } catch(e){
        optKantor.innerHTML = `<option value="">Error Load</option>`;
    }
  }

  document.getElementById('formFilterRealisasi').addEventListener('submit', e => { e.preventDefault(); fetchRealisasiData(); });

  // --- FETCH DATA UTAMA ---
  async function fetchRealisasiData() {
      const loading = document.getElementById('loadingRealisasi');
      const tbody = document.getElementById('bodyRealisasi');
      const trTotal = document.getElementById('rowTotalAtas'); 
      
      const closing = document.getElementById('closing_date_realisasi').value;
      const harian  = document.getElementById('harian_date_realisasi').value;
      
      // Ambil value dari dropdown meskipun statusnya disabled
      const optKantor = document.getElementById('opt_kantor_realisasi');
      const kantor = optKantor.disabled ? optKantor.value : (optKantor.value || '');

      document.getElementById('thNamaUnit').innerText = (kantor && kantor !== '') ? "NAMA KANKAS" : "NAMA KANTOR";

      loading.classList.remove('hidden');
      tbody.innerHTML = ''; trTotal.innerHTML = '';

      try {
          const payload = { type: 'Realisasi Kredit', closing_date: closing, harian_date: harian, kode_kantor: kantor };
          const res = await fetch(API_KREDIT, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          const json = await res.json();
          if(json.status !== 200) throw new Error(json.message);

          const rows = json.data?.data || [];
          const gt   = json.data?.grand_total || { noa_realisasi:0, total_realisasi:0, total_run_off:0, growth:0 };

          if (rows.length === 0) {
              tbody.innerHTML = `<tr><td colspan="6" class="text-center py-10 text-slate-400">Data Kosong</td></tr>`;
              return;
          }

          // Render Grand Total di THEAD (Sticky Atas Lapis 2)
          trTotal.innerHTML = `
            <th class="col-kode sticky-col text-center uppercase">ALL</th>
            <th class="col-nama sticky-col text-left uppercase pl-2">GRAND TOTAL</th>
            <th class="text-right">${fmt(gt.noa_realisasi)}</th>
            <th class="text-right bg-blue-100">${fmt(gt.total_realisasi)}</th>
            <th class="text-right bg-orange-100">${fmt(gt.total_run_off)}</th>
            <th class="text-right bg-green-100">${fmt(gt.growth)}</th>
          `;

          // Render Data List
          let html = '';
          rows.forEach(r => {
              const paramKankas = (kantor && kantor !== '') ? r.kode_unit : ''; 
              const paramCabang = (kantor && kantor !== '') ? kantor : r.kode_unit;
              const unitKode = String(r.kode_unit || '').replace(kantor, '').trim() || r.kode_unit;

              html += `
                <tr>
                    <td class="col-kode sticky-col text-center font-mono text-slate-500">${unitKode}</td>
                    <td class="col-nama sticky-col font-semibold text-slate-700 truncate" title="${r.nama_unit}">${r.nama_unit}</td>
                    <td class="text-right">
                        <span class="cell-action" onclick="showDetailRealisasi('${paramCabang}', '${paramKankas}', '${r.nama_unit}')">${fmt(r.noa_realisasi)}</span>
                    </td>
                    <td class="text-right bg-blue-50/50 text-blue-900 font-medium">${fmt(r.total_realisasi)}</td>
                    <td class="text-right bg-orange-50/50 text-orange-900 font-medium">${fmt(r.total_run_off)}</td>
                    <td class="text-right bg-green-50/50 text-green-900 font-bold">${fmt(r.growth)}</td>
                </tr>
              `;
          });
          tbody.innerHTML = html;

      } catch(e) {
          tbody.innerHTML = `<tr><td colspan="6" class="text-center py-10 text-red-500 font-bold">Gagal: ${e.message}</td></tr>`;
      } finally {
          loading.classList.add('hidden');
      }
  }

  // --- MODAL DETAIL ---
  let currentDetailUnit = '';
  async function showDetailRealisasi(kode_cabang, kode_kankas, nama_unit) {
      currentDetailUnit = nama_unit;
      const harian = document.getElementById('harian_date_realisasi').value;
      const modal = document.getElementById('modalDetailRealisasi');
      const title = document.getElementById('modalTitleRealisasi');
      const body  = document.getElementById('modalBodyRealisasi');
      const loader= document.getElementById('loadingModal');

      modal.classList.remove('hidden');
      title.innerHTML = `Detail Realisasi <span class="bg-blue-100 text-blue-800 text-[10px] md:text-xs px-2 py-0.5 rounded font-mono ml-2 border border-blue-200">${nama_unit}</span>`;
      body.innerHTML = '';
      loader.classList.remove('hidden');

      try {
          const payload = { type: 'Detail Realisasi Kredit', harian_date: harian, kode_kantor: kode_cabang, kode_kankas: kode_kankas };
          const res = await fetch(API_KREDIT, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          const json = await res.json();
          const rows = json.data || [];

          if(rows.length === 0) {
              body.innerHTML = `<tr><td colspan="6" class="text-center py-10 text-slate-400">Tidak ada rincian nasabah.</td></tr>`;
              return;
          }

          let html = '';
          rows.forEach(r => {
              html += `
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-3 md:px-4 py-2 border-r border-slate-100 font-mono text-slate-500">${r.no_rekening}</td>
                    <td class="px-3 md:px-4 py-2 border-r border-slate-100 font-semibold text-slate-700 truncate" title="${r.nama_nasabah}">${r.nama_nasabah}</td>
                    <td class="px-3 md:px-4 py-2 border-r border-slate-100 text-right font-bold text-slate-800">${fmt(r.plafond)}</td>
                    <td class="px-3 md:px-4 py-2 border-r border-slate-100 text-center text-slate-600">${r.tgl_realisasi}</td>
                    <td class="px-3 md:px-4 py-2 border-r border-slate-100 text-slate-600 text-xs truncate" title="${r.nama_kankas||'-'}">${r.nama_kankas || '-'}</td>
                    <td class="px-3 md:px-4 py-2 text-blue-700 font-bold text-xs truncate" title="${r.nama_ao||'-'}">${r.nama_ao || '-'}</td>
                </tr>
              `;
          });
          body.innerHTML = html;
      } catch(e) {
          body.innerHTML = `<tr><td colspan="6" class="text-center text-red-500 font-bold py-8">Gagal menarik data detail.</td></tr>`;
      } finally {
          loader.classList.add('hidden');
      }
  }

  function closeModalRealisasi() { document.getElementById('modalDetailRealisasi').classList.add('hidden'); }
  
  // --- DOWNLOAD EXCEL REKAP UTAMA (ANGKA MURNI) ---
  window.exportExcelRealisasi = function() {
      const tbody = document.getElementById('bodyRealisasi');
      if(!tbody.innerHTML || tbody.innerText.includes('Data Kosong')) return alert("Tidak ada data untuk didownload");

      let html = `<table border="1"><thead><tr>`;
      
      // Header: Gunakan textContent agar text pada display:none (Kode di mobile) tetap terbaca
      const ths = document.querySelectorAll('#tabelRealisasi thead tr:first-child th');
      ths.forEach(th => {
          html += `<th style="background:#f1f5f9">${th.textContent.trim()}</th>`;
      });
      html += `</tr></thead><tbody>`;

      // Grand Total
      const trTotal = document.getElementById('rowTotalAtas');
      if(trTotal && trTotal.children.length > 0) {
          const tths = trTotal.querySelectorAll('th');
          html += `<tr>`;
          html += `<td style="background:#eff6ff; font-weight:bold; text-align:center;">${tths[0].textContent.trim()}</td>`;
          html += `<td style="background:#eff6ff; font-weight:bold;">${tths[1].textContent.trim()}</td>`;
          html += `<td style="background:#eff6ff; font-weight:bold;">${tths[2].textContent.replace(/\./g, '').trim()}</td>`;
          html += `<td style="background:#eff6ff; font-weight:bold;">${tths[3].textContent.replace(/\./g, '').trim()}</td>`;
          html += `<td style="background:#eff6ff; font-weight:bold;">${tths[4].textContent.replace(/\./g, '').trim()}</td>`;
          html += `<td style="background:#eff6ff; font-weight:bold;">${tths[5].textContent.replace(/\./g, '').trim()}</td>`;
          html += `</tr>`;
      }

      // Data Rows
      const rows = tbody.querySelectorAll('tr');
      rows.forEach(tr => {
          html += `<tr>`;
          const tds = tr.querySelectorAll('td');
          html += `<td style="mso-number-format:'\\@'">${tds[0].textContent.trim()}</td>`;
          html += `<td>${tds[1].textContent.trim()}</td>`;
          html += `<td>${tds[2].textContent.replace(/\./g, '').trim()}</td>`; // NOA
          html += `<td>${tds[3].textContent.replace(/\./g, '').trim()}</td>`; // Realisasi
          html += `<td>${tds[4].textContent.replace(/\./g, '').trim()}</td>`; // Runoff
          html += `<td>${tds[5].textContent.replace(/\./g, '').trim()}</td>`; // Growth
          html += `</tr>`;
      });
      
      html += `</tbody></table>`;

      const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
      const a = document.createElement('a'); a.href = URL.createObjectURL(blob);
      const tgl = document.getElementById('harian_date_realisasi').value;
      const kntr = document.getElementById('opt_kantor_realisasi').value || 'KONSOLIDASI';
      a.download = `Rekap_Realisasi_${kntr}_${tgl}.xls`; a.click();
  };

  // --- DOWNLOAD EXCEL DETAIL MODAL (ANGKA MURNI) ---
  window.exportExcelDetailRealisasi = function() {
      const rows = document.querySelectorAll('#modalBodyRealisasi tr');
      if(rows.length === 0 || rows[0].innerText.includes('Tidak ada data')) return alert("Tidak ada data detail");

      let html = `<table border="1"><thead><tr>`;
      html += `<th style="background:#f1f5f9">NO REKENING</th>`;
      html += `<th style="background:#f1f5f9">NAMA NASABAH</th>`;
      html += `<th style="background:#f1f5f9">PLAFOND</th>`;
      html += `<th style="background:#f1f5f9">TGL REALISASI</th>`;
      html += `<th style="background:#f1f5f9">KANKAS</th>`;
      html += `<th style="background:#f1f5f9">AO</th>`;
      html += `</tr></thead><tbody>`;

      rows.forEach(tr => {
          html += `<tr>`;
          const tds = tr.querySelectorAll('td');
          html += `<td style="mso-number-format:'\\@'">${tds[0].textContent.trim()}</td>`;
          html += `<td>${tds[1].textContent.trim()}</td>`;
          html += `<td>${tds[2].textContent.replace(/\./g, '').trim()}</td>`; // Plafond murni
          html += `<td>${tds[3].textContent.trim()}</td>`;
          html += `<td>${tds[4].textContent.trim()}</td>`;
          html += `<td>${tds[5].textContent.trim()}</td>`;
          html += `</tr>`;
      });
      html += `</tbody></table>`;

      const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
      const a = document.createElement('a'); a.href = URL.createObjectURL(blob);
      const tgl = document.getElementById('harian_date_realisasi').value;
      a.download = `Detail_Realisasi_${currentDetailUnit}_${tgl}.xls`; a.click();
  };

</script>