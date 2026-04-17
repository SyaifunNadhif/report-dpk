<style>
  /* Custom Scrollbar */
  .custom-scrollbar::-webkit-scrollbar { height: 8px; width: 8px; }
  .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
  
  /* Sembunyikan Scrollbar Filter di Mobile */
  .no-scrollbar::-webkit-scrollbar { display: none; }
  .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

  /* Animasi Modal */
  @keyframes scaleUp { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
  .animate-scale-up { animation: scaleUp 0.2s ease-out forwards; }

  /* ========================================================
     CSS MAGIC STICKY TABLE REKAP UTAMA (Disesuaikan Font Besar)
     ======================================================== */
  #tabelPipeline thead th { position: sticky; box-shadow: inset 0 -1px 0 #cbd5e1; }
  
  /* Lapis 1 (Header Utama) */
  #tabelPipeline thead tr:nth-child(1) th { top: 0; z-index: 40; height: 50px; }
  
  /* Lapis 2 (Sub-Header Lunas/Topup/Retensi) */
  #tabelPipeline thead tr:nth-child(2) th { top: 50px; z-index: 39; height: 42px; }
  
  /* Lapis 3 (Grand Total - Biru Soft) */
  #tabelPipeline thead tr:nth-child(3) th { 
      top: 92px; z-index: 38; height: 56px; 
      background-color: #dbeafe !important; 
      border-bottom: 2px solid #bfdbfe;
      box-shadow: inset 0 -1px 0 #93c5fd;
  }

  /* Freeze Kolom Kiri Rekap */
  .sticky-left-1 { position: sticky; left: 0; z-index: 20; background: white; box-shadow: inset -1px 0 0 #e2e8f0; }
  .sticky-left-2 { position: sticky; left: 0; z-index: 20; background: white; box-shadow: inset -1px 0 0 #e2e8f0; }
  
  @media (min-width: 640px) { .sticky-left-2 { left: 70px; } } 

  /* Z-Index Header Freeze Kiri Rekap */
  #tabelPipeline thead tr:nth-child(1) th.sticky-left-1 { z-index: 50; box-shadow: inset -1px -1px 0 #cbd5e1; background-color: #f1f5f9; }
  #tabelPipeline thead tr:nth-child(1) th.sticky-left-2 { z-index: 49; box-shadow: inset -1px -1px 0 #cbd5e1; background-color: #f1f5f9; }
  #tabelPipeline thead tr:nth-child(3) th.sticky-left-1 { z-index: 48; background-color: #bfdbfe !important; box-shadow: inset -1px -2px 0 #93c5fd; }
  #tabelPipeline thead tr:nth-child(3) th.sticky-left-2 { z-index: 47; background-color: #bfdbfe !important; box-shadow: inset -1px -2px 0 #93c5fd; }

  /* Hover Body Rekap */
  #bodyRekap tr:hover td { background-color: #eff6ff !important; cursor: pointer; }
  #bodyRekap tr:hover td.sticky-left-1, #bodyRekap tr:hover td.sticky-left-2 { background-color: #eff6ff !important; }

  /* ========================================================
     CSS MAGIC STICKY MODAL DETAIL
     ======================================================== */
  #tableExportModal thead th { position: sticky; box-shadow: inset 0 -1px 0 #cbd5e1; }
  #tableExportModal thead tr:nth-child(1) th { top: 0; z-index: 40; height: 46px; background-color: #f1f5f9; }
  #tableExportModal thead tr:nth-child(2) th { top: 46px; z-index: 39; height: 42px; background-color: #dbeafe !important; box-shadow: inset 0 -1px 0 #93c5fd; border-bottom: 2px solid #bfdbfe;}

  /* Freeze Kiri Modal */
  .mod-sticky-1 { position: sticky; left: 0; z-index: 20; background: white; box-shadow: inset -1px 0 0 #e2e8f0; }
  .mod-sticky-2 { position: sticky; left: 100px; z-index: 20; background: white; box-shadow: inset -1px 0 0 #e2e8f0; }

  #tableExportModal thead tr:nth-child(1) th.mod-sticky-1 { z-index: 50; background-color: #e2e8f0; box-shadow: inset -1px -1px 0 #cbd5e1; }
  #tableExportModal thead tr:nth-child(1) th.mod-sticky-2 { z-index: 49; background-color: #e2e8f0; box-shadow: inset -1px -1px 0 #cbd5e1; }
  #tableExportModal thead tr:nth-child(2) th.mod-sticky-1 { z-index: 48; background-color: #bfdbfe !important; box-shadow: inset -1px -1px 0 #93c5fd; }
  #tableExportModal thead tr:nth-child(2) th.mod-sticky-2 { z-index: 47; background-color: #bfdbfe !important; box-shadow: inset -1px -1px 0 #93c5fd; }

  #bodyDetail tr:hover td { background-color: #f8fafc !important; }
  #bodyDetail tr:hover td.mod-sticky-1, #bodyDetail tr:hover td.mod-sticky-2 { background-color: #f8fafc !important; }

  /* Form Inp */
  .inp { border:1px solid #cbd5e1; border-radius:8px; padding:0 12px; background:#fff; outline:none; transition: border 0.2s;}
  .inp:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,0.1); }
  .lbl { font-size:11px; color:#475569; font-weight:800; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.05em; display:block; white-space: nowrap;}
  .field { display:flex; flex-direction:column; }
  .btn-icon { display:inline-flex; align-items:center; justify-content:center; border:none; cursor:pointer; transition: transform 0.2s;}
  .btn-icon:hover { transform:translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
</style>

<div class="max-w-[1920px] mx-auto px-2 md:px-4 py-3 md:py-5 h-[calc(100vh-80px)] flex flex-col bg-slate-50 font-sans text-slate-800 overflow-hidden">
  
  <div class="flex-none mb-4 flex flex-col xl:flex-row justify-between xl:items-start gap-4 w-full">
      
      <div class="flex flex-col gap-3 shrink-0">
          <h1 class="text-xl md:text-2xl font-bold text-slate-800 flex items-center gap-2">
              <span class="p-1.5 md:p-2 bg-blue-600 rounded-lg text-white shadow-sm">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08-.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              </span>
              Rekomendasi Pipeline Kredit
              <button onclick="openInfoModal()" class="text-slate-400 hover:text-blue-600 transition rounded-full p-1 focus:outline-none focus:ring-2 focus:ring-blue-200" title="Informasi Status">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              </button>
          </h1>
          
          <div id="summaryPills" class="hidden flex-wrap items-center gap-2">
              <div class="flex flex-col bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-lg shadow-sm min-w-[130px]">
                  <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-widest mb-0.5">Sudah Ambil</span>
                  <div class="flex items-end gap-1.5">
                      <span class="text-sm md:text-base font-bold text-emerald-800" id="sum_sudah">0</span>
                      <span class="text-[11px] md:text-xs font-mono text-emerald-600 mb-0.5" id="sum_sudah_nom">0</span>
                  </div>
              </div>
              <div class="flex flex-col bg-blue-50 border border-blue-200 px-3 py-1.5 rounded-lg shadow-sm min-w-[130px]">
                  <span class="text-[10px] font-bold text-blue-700 uppercase tracking-widest mb-0.5">Potensi Siap</span>
                  <div class="flex items-end gap-1.5">
                      <span class="text-sm md:text-base font-bold text-blue-800" id="sum_potensi">0</span>
                      <span class="text-[11px] md:text-xs font-mono text-blue-600 mb-0.5" id="sum_potensi_nom">0</span>
                  </div>
              </div>
          </div>
      </div>

      <form id="formFilter" class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm flex flex-wrap md:flex-nowrap items-end gap-2 md:gap-3 w-full xl:w-auto shrink-0 xl:ml-auto overflow-x-auto no-scrollbar" onsubmit="event.preventDefault(); fetchRekap();">
          <input type="hidden" id="closing_date" disabled>
          
          <div class="field shrink-0 w-[130px] md:w-[150px]">
              <label class="lbl">POSISI (ACTUAL)</label>
              <input type="date" id="harian_date" class="inp text-sm font-semibold h-[38px] text-slate-700 bg-slate-50 cursor-not-allowed" readonly required>
          </div>
          
          <div class="field shrink-0 w-[100px] md:w-[120px]">
              <label class="lbl">TAHUN JT</label>
              <input type="number" id="tahun_jt" class="inp text-sm font-semibold h-[38px] text-slate-700" value="2026" required>
          </div>
          
          <div class="flex items-center gap-1.5 shrink-0 h-[38px] mb-px">
              <button type="submit" class="btn-icon bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 md:px-5 h-full shadow-sm" title="Cari Data">
                  <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                  <span class="ml-1.5 text-sm font-bold uppercase tracking-wider hidden sm:inline">CARI</span>
              </button>
              <button type="button" onclick="exportExcelRekapPipeline()" class="btn-icon bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg px-3 md:px-4 h-full shadow-sm" title="Download Excel">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
              </button>
          </div>
      </form>
  </div>

  <div class="flex-1 overflow-hidden bg-white rounded-xl shadow-sm border border-slate-200 relative">
    <div id="loadingRekap" class="hidden absolute inset-0 bg-white/80 z-[100] flex flex-col items-center justify-center text-blue-600 backdrop-blur-sm">
        <div class="animate-spin rounded-full h-10 w-10 border-4 border-blue-500 border-t-transparent mb-3"></div>
        <span class="text-sm font-bold uppercase tracking-widest">Menyiapkan Pipeline...</span>
    </div>
    
    <div class="h-full overflow-auto custom-scrollbar relative">
      <table class="w-max min-w-full text-center border-separate border-spacing-0 text-slate-700 table-fixed" id="tabelPipeline">
        <thead class="tracking-wider bg-slate-50 text-slate-800 font-bold text-xs md:text-sm" id="headPipeline">
            </thead>
        <tbody id="bodyRekap" class="divide-y divide-slate-100 bg-white"></tbody>
      </table>
    </div>
  </div>

</div>

<div id="infoModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeInfoModal()"></div>
    <div class="relative bg-white w-full max-w-md rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-scale-up border border-slate-200">
        <div class="flex justify-between items-center px-5 py-4 border-b bg-slate-50">
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <span class="w-1.5 h-5 bg-blue-600 rounded-full"></span>
                Panduan Status Pipeline
            </h3>
            <button onclick="closeInfoModal()" class="text-slate-400 hover:text-red-500 transition text-2xl leading-none">&times;</button>
        </div>
        <div class="p-5 space-y-4 text-sm text-slate-600 bg-white">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 px-2 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded text-xs font-bold w-[80px] text-center shrink-0">LUNAS</span>
                <p>Nasabah yang telah lunas. Anda dapat melanjutkan aksi <strong>PROSPEK</strong> pada nasabah ini.</p>
            </div>
            <div class="flex items-start gap-3">
                <span class="mt-0.5 px-2 py-1 bg-purple-50 text-purple-700 border border-purple-200 rounded text-xs font-bold w-[80px] text-center shrink-0">TOP UP</span>
                <p>Nasabah berpotensi dengan proporsi sisa Baki Debet (BD) <strong>&le; 50%</strong>.</p>
            </div>
            <div class="flex items-start gap-3">
                <span class="mt-0.5 px-2 py-1 bg-orange-50 text-orange-700 border border-orange-200 rounded text-xs font-bold w-[80px] text-center shrink-0">RETENSI</span>
                <p>Nasabah yang masih berjalan dengan sisa Baki Debet (BD) <strong>&gt; 50%</strong>.</p>
            </div>
        </div>
        <div class="px-5 py-3 border-t bg-slate-50 flex justify-end">
            <button onclick="closeInfoModal()" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-bold shadow-sm transition">Mengerti</button>
        </div>
    </div>
</div>

<div id="modalDetail" class="fixed inset-0 z-[9999] hidden items-end md:items-center justify-center p-2 md:p-4">
  <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
  
  <div class="relative bg-white w-full h-[95vh] md:h-[92vh] max-w-[1700px] rounded-t-xl md:rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-scale-up">
    
    <div class="flex justify-between items-center px-4 py-4 md:px-6 border-b bg-slate-50 shrink-0 flex-wrap gap-3">
        <div class="flex-1 min-w-[250px]">
            <h3 class="font-bold text-slate-800 flex items-center gap-2 text-base md:text-xl">
                <span class="w-2 h-6 bg-blue-600 rounded-full hidden md:block"></span> 
                Detail Nasabah Pipeline 
            </h3>
            <p class="text-xs md:text-sm text-slate-500 mt-1 md:ml-4 font-mono font-medium" id="detailSubTitle">...</p>
        </div>
        
        <div class="flex items-center gap-2 md:gap-3 ml-auto shrink-0 w-full sm:w-auto mt-2 sm:mt-0 overflow-x-auto no-scrollbar">
            <select id="filter_kankas_modal" class="inp px-3 h-10 w-[120px] md:w-[150px] text-xs md:text-sm font-bold text-blue-800 bg-blue-50 outline-none shrink-0" onchange="changeFilter()">
                <option value="">Semua Kankas</option>
            </select>
            <select id="filter_status_modal" class="inp px-3 h-10 w-[130px] md:w-[160px] text-xs md:text-sm font-bold text-blue-800 bg-blue-50 outline-none shrink-0" onchange="changeFilter()">
                <option value="">Semua Status</option>
                <option value="sudah">✅ Sudah Ambil</option>
                <option value="lunas">🔵 Lunas</option>
                <option value="topup">🟣 BD <= 50%</option>
                <option value="retensi">🟠 BD > 50%</option>
                <option value="drop">⛔ Drop</option>
            </select>
            <select id="filter_ao_modal" class="inp px-3 h-10 w-[120px] md:w-[160px] text-xs md:text-sm font-bold text-slate-700 bg-white outline-none shrink-0" onchange="changeFilter()">
                <option value="">Semua AO</option>
            </select>

            <button onclick="downloadExcelDetail()" class="btn-icon bg-emerald-600 hover:bg-emerald-700 text-white px-3 md:px-4 h-10 rounded-lg shadow-sm text-xs md:text-sm font-bold uppercase tracking-wider shrink-0">
                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span class="hidden sm:inline ml-1.5">Excel</span>
            </button>
            <button onclick="closeModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-200 hover:bg-red-500 hover:text-white text-slate-600 transition font-bold text-2xl leading-none shrink-0">&times;</button>
        </div>
    </div>

    <div id="modalStats" class="bg-slate-100 border-b border-slate-200 px-4 py-3 text-xs md:text-sm font-mono font-medium text-slate-600 overflow-x-auto no-scrollbar whitespace-nowrap shrink-0"></div>

    <div class="flex-1 overflow-auto bg-slate-50 relative custom-scrollbar p-0 md:p-3">
        <div id="loadingDetail" class="hidden absolute inset-0 bg-white/90 z-40 flex flex-col items-center justify-center text-blue-600 backdrop-blur-sm">
            <div class="animate-spin rounded-full h-10 w-10 border-4 border-blue-500 border-t-transparent mb-3"></div>
            <span class="text-sm font-bold uppercase tracking-widest">Memuat Detail...</span>
        </div>
        
        <table class="w-max min-w-full text-sm text-left text-slate-700 border border-slate-200 md:rounded-xl shadow-sm bg-white table-fixed" id="tableExportModal">
            <thead class="text-slate-600 font-bold uppercase tracking-wider text-[10px] md:text-xs">
                <tr>
                    <th class="px-3 py-4 border-b border-r border-slate-300 w-[120px] mod-sticky-1 rounded-tl-xl text-blue-900 bg-[#f1f5f9]">REKENING</th>
                    <th class="px-4 py-4 border-b border-r border-slate-300 w-[220px] md:w-[280px] mod-sticky-2 text-blue-900 bg-[#f1f5f9]">NAMA NASABAH</th>
                    <th class="px-4 py-4 border-b border-r border-slate-300 w-[200px] md:w-[250px]">ALAMAT</th>
                    <th class="px-3 py-4 border-b border-r border-slate-300 w-[120px] text-center">NO HP</th>
                    <th class="px-3 py-4 border-b border-r border-slate-300 w-[120px] text-center">KANKAS</th>
                    <th class="px-4 py-4 border-b border-r border-slate-300 w-[160px] text-blue-800">NAMA AO</th>
                    <th class="px-4 py-4 border-b border-r border-slate-300 w-[130px] text-right">PLAFON AWAL</th>
                    <th class="px-3 py-4 border-b border-r border-slate-300 w-[120px] text-center">TANGGAL JT</th>
                    <th class="px-4 py-4 border-b border-r border-blue-300 w-[130px] text-right bg-blue-50 text-blue-900">SISA OS</th>
                    <th class="px-4 py-4 border-b border-r border-slate-300 w-[140px] text-center">STATUS</th>
                    <th class="px-4 py-4 border-b border-r border-emerald-300 w-[140px] text-right bg-emerald-50 text-emerald-900">NOMINAL BARU</th>
                    <th class="px-3 py-4 border-b border-slate-300 w-[90px] text-center">AKSI</th>
                </tr>
                <tr id="rowTotalDetailAtas"></tr>
            </thead>
            <tbody id="bodyDetail" class="divide-y divide-slate-100 bg-white"></tbody>
        </table>
    </div>

    <div class="px-4 py-4 md:px-6 border-t bg-white flex justify-between items-center shrink-0">
        <span id="pageInfo" class="text-xs md:text-sm font-bold text-slate-600 bg-slate-100 px-3 py-1 rounded-lg">0 Data</span>
        <div class="flex gap-2">
            <button id="btnPrev" onclick="changePage(-1)" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 hover:border-slate-400 disabled:opacity-50 transition shadow-sm">« Prev</button>
            <button id="btnNext" onclick="changePage(1)" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 hover:border-slate-400 disabled:opacity-50 transition shadow-sm">Next »</button>
        </div>
    </div>
  </div>
</div>

<script>
  // --- CONFIG & GLOBAL VARS ---
  const API_URL  = './api/pipelane/'; 
  const API_KODE = './api/kode/'; 
  const API_DATE = './api/date/';
  const nf = new Intl.NumberFormat('id-ID');
  const fmt = n => nf.format(Math.round(Number(n||0)));

  let state = { cabang:'', kankas:'', ao:'', status:'', page:1, limit:20, totalPages:1 };
  let abortRekap;
  let rekapDataCache = null; 
  let userKodeGlobal = '000'; 

  // --- INIT ---
  window.addEventListener('DOMContentLoaded', async () => {
      const user = (window.getUser && window.getUser()) || null;
      userKodeGlobal = (user?.kode ? String(user.kode).padStart(3,'0') : '000');

      setupHeaderPipeline(userKodeGlobal);

      const now = new Date();
      document.getElementById('closing_date').value = `${now.getFullYear() - 1}-12-31`;
      try {
          const r = await fetch(API_DATE); const j = await r.json();
          document.getElementById('harian_date').value = (j && j.data && j.data.last_created) ? j.data.last_created : now.toISOString().split('T')[0];
      } catch(e) { document.getElementById('harian_date').value = now.toISOString().split('T')[0]; }

      fetchRekap();
  });

  async function apiCall(url, payload, signal = null) {
      const opt = { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) };
      if (signal) opt.signal = signal;
      const res = await fetch(url, opt);
      return await res.json();
  }

  // --- SETUP HEADER REKAP UTAMA (Disesuaikan Sesuai Permintaan) ---
  function setupHeaderPipeline(userKode) {
      const th = document.getElementById('headPipeline');
      let thHtml = '';

      // Lapis 1
      thHtml += `<tr>`;
      if (userKode === '000') {
          thHtml += `
            <th rowspan="2" class="sticky-left-1 w-[70px] bg-slate-100 text-slate-800 border-r border-b border-slate-200 align-middle uppercase hidden sm:table-cell text-center text-sm">Kode</th>
            <th rowspan="2" class="sticky-left-2 min-w-[160px] md:min-w-[200px] bg-slate-100 text-slate-800 border-r border-b border-slate-200 align-middle text-left uppercase pl-5 text-sm">Nama Kantor</th>
          `;
      } else {
          thHtml += `
            <th rowspan="2" class="sticky-left-1 min-w-[160px] md:min-w-[240px] bg-slate-100 text-slate-800 border-r border-b border-slate-200 align-middle text-left uppercase pl-5 text-sm">Nama Kantor</th>
          `;
      }

      thHtml += `
            <th rowspan="2" class="px-4 border-r border-b border-slate-200 align-middle text-right bg-slate-50 text-slate-700 w-[150px]">
                <div class="text-xs md:text-sm font-bold">TARGET PIPELINE</div>
                <div class="text-[10px] md:text-[11px] text-slate-400 font-normal mt-1 font-mono">NOA | Plafon</div>
            </th>
            <th colspan="3" class="text-center bg-blue-50 text-blue-900 border-r border-b border-blue-200 uppercase font-bold text-xs md:text-sm" style="padding: 8px;">Rekomendasi Pipeline AO</th>
            <th rowspan="2" class="px-4 border-r border-b border-rose-200 align-middle bg-rose-50 text-rose-800 w-[130px]">
                <div class="text-xs md:text-sm font-bold">DROP</div>
                <div class="text-[10px] md:text-[11px] text-rose-600/80 font-normal mt-1 font-mono">NOA | Sisa OS</div>
            </th>
            <th colspan="3" class="text-center bg-emerald-50 text-emerald-900 border-b border-emerald-200 uppercase font-bold text-xs md:text-sm" style="padding: 8px;">SUDAH AMBIL</th>
          </tr>`;

      // Lapis 2
      thHtml += `
          <tr>
            <th class="px-4 text-center bg-blue-50 text-blue-800 border-r border-b border-blue-200 w-[140px]">
                <div class="text-xs md:text-sm font-bold">LUNAS</div>
                <div class="text-[10px] md:text-[11px] text-blue-600/80 font-normal mt-1 font-mono">NOA | Plafon</div>
            </th>
            <th class="px-4 text-center bg-purple-50 text-purple-800 border-r border-b border-purple-200 w-[160px]">
                <div class="text-xs md:text-sm font-bold">BD <= 50%</div>
                <div class="text-[10px] md:text-[11px] text-purple-600/80 font-normal mt-1 font-mono">NOA | Sisa OS</div>
            </th>
            <th class="px-4 text-center bg-orange-50 text-orange-800 border-r border-b border-orange-200 w-[160px]">
                <div class="text-xs md:text-sm font-bold">BD > 50%</div>
                <div class="text-[10px] md:text-[11px] text-orange-600/80 font-normal mt-1 font-mono">NOA | Sisa OS</div>
            </th>
            <th class="px-3 text-center bg-emerald-50 text-emerald-800 border-r border-b border-emerald-200 w-[70px] text-xs">NOA</th>
            <th class="px-4 text-right bg-emerald-50 text-emerald-800 border-r border-b border-emerald-200 w-[130px] text-xs">NOMINAL</th>
            <th class="px-3 text-center bg-emerald-50 text-emerald-800 border-b border-emerald-200 w-[70px] text-xs">%</th>
          </tr>
          <tr id="rowTotalPipelineAtas"></tr>
      `;
      th.innerHTML = thHtml;
  }

  // --- FETCH REKAP UTAMA ---
  async function fetchRekap() {
      const l = document.getElementById('loadingRekap');
      const tb = document.getElementById('bodyRekap');
      const trTot = document.getElementById('rowTotalPipelineAtas');
      const pills = document.getElementById('summaryPills');
      
      if(abortRekap) abortRekap.abort();
      abortRekap = new AbortController();

      l.classList.remove('hidden'); pills.classList.add('hidden');
      
      const colSpan = userKodeGlobal === '000' ? 10 : 9;
      tb.innerHTML = `<tr><td colspan="${colSpan}" class="text-center py-20 text-slate-400 italic text-base">Sedang mengambil data...</td></tr>`;
      trTot.innerHTML = '';
      rekapDataCache = null;

      try {
          const reqCabang = (userKodeGlobal === '000') ? null : userKodeGlobal;

          const payload = {
              type: 'rekap_pipeline',
              closing_date: document.getElementById('closing_date').value,
              harian_date: document.getElementById('harian_date').value,
              tahun_jt: document.getElementById('tahun_jt').value,
              kode_kantor: reqCabang 
          };

          const json = await apiCall(API_URL, payload, abortRekap.signal);
          let rows = json.data || [];

          if (userKodeGlobal !== '000') {
              rows = rows.filter(r => String(r.kode_cabang) === userKodeGlobal);
          }

          if(rows.length === 0) {
              tb.innerHTML = `<tr><td colspan="${colSpan}" class="text-center py-20 text-slate-400 italic text-base">Tidak ada data.</td></tr>`;
              return;
          }
          rekapDataCache = rows; 

          let T = { tgt_noa:0, tgt_nom:0, sdh_noa:0, sdh_nom:0, lun_noa:0, lun_nom:0, top_noa:0, top_nom:0, ret_noa:0, ret_nom:0, drop_noa:0, drop_nom:0 };
          let html = '';

          rows.forEach(r => {
              T.tgt_noa += +r.noa_target; T.tgt_nom += +r.plafon_closing;
              T.sdh_noa += +r.noa_sudah;  T.sdh_nom += +r.nominal_sudah;
              T.lun_noa += +r.noa_lunas;  T.lun_nom += +r.nominal_lunas;
              T.top_noa += +r.noa_topup;  T.top_nom += +r.os_topup;
              T.ret_noa += +r.noa_retensi;T.ret_nom += +r.os_retensi;
              T.drop_noa += +r.noa_drop;  T.drop_nom += +r.os_drop;

              const namaK = r.nama_kantor || r.kode_cabang;

              // Kalkulasi Persentase per cabang
              const divisorRealPerCabang = r.plafon_closing > 0 ? r.plafon_closing : 1;
              const pctRealPerCabang = ((r.nominal_sudah / divisorRealPerCabang) * 100).toFixed(1);

              let rowHtml = `<tr onclick="openModal('${r.kode_cabang}', '${namaK}')" class="transition h-[52px] group border-b border-slate-100">`;
              
              if (userKodeGlobal === '000') {
                  rowHtml += `
                    <td class="sticky-left-1 px-3 py-2 border-r border-slate-100 font-mono text-slate-500 text-center hidden sm:table-cell bg-white group-hover:bg-slate-50 shadow-[inset_-1px_0_0_#e2e8f0] z-20 text-sm">${r.kode_cabang}</td>
                    <td class="sticky-left-2 px-4 py-2 border-r border-slate-100 font-semibold text-slate-700 truncate bg-white group-hover:bg-slate-50 shadow-[inset_-1px_0_0_#e2e8f0] z-20 text-sm" title="${namaK}">${namaK}</td>
                  `;
              } else {
                  rowHtml += `
                    <td class="sticky-left-1 px-4 py-2 border-r border-slate-100 font-semibold text-slate-700 truncate bg-white group-hover:bg-slate-50 shadow-[inset_-1px_0_0_#e2e8f0] z-20 text-sm" title="${namaK}">${namaK}</td>
                  `;
              }

              rowHtml += `
                    <td class="px-4 py-2 border-r border-slate-100 text-right"><div class="font-extrabold text-slate-800 text-sm md:text-base">${fmt(r.noa_target)}</div><div class="text-[10px] md:text-xs text-slate-400 font-mono mt-0.5">${fmt(r.plafon_closing)}</div></td>
                    
                    <td class="px-4 py-2 border-r border-blue-100 text-center bg-blue-50/40"><div class="font-extrabold text-blue-700 text-sm md:text-base">${fmt(r.noa_lunas)}</div><div class="text-[10px] md:text-xs text-blue-600 font-mono mt-0.5">${fmt(r.nominal_lunas)}</div></td>
                    <td class="px-4 py-2 border-r border-purple-100 text-center bg-purple-50/40"><div class="font-extrabold text-purple-700 text-sm md:text-base">${fmt(r.noa_topup)}</div><div class="text-[10px] md:text-xs text-purple-600 font-mono mt-0.5">${fmt(r.os_topup)}</div></td>
                    <td class="px-4 py-2 border-r border-orange-100 text-center bg-orange-50/40"><div class="font-extrabold text-orange-700 text-sm md:text-base">${fmt(r.noa_retensi)}</div><div class="text-[10px] md:text-xs text-orange-600 font-mono mt-0.5">${fmt(r.os_retensi)}</div></td>
                    
                    <td class="px-4 py-2 text-center bg-rose-50/40 border-r border-rose-100"><div class="font-extrabold text-rose-700 text-sm md:text-base">${fmt(r.noa_drop)}</div><div class="text-[10px] md:text-xs text-rose-600 font-mono mt-0.5">${fmt(r.os_drop)}</div></td>
                    
                    <td class="px-3 py-2 border-r border-emerald-100 text-center bg-emerald-50/40"><div class="font-extrabold text-emerald-700 text-sm md:text-base">${fmt(r.noa_sudah)}</div></td>
                    <td class="px-4 py-2 border-r border-emerald-100 text-right bg-emerald-50/40"><div class="font-extrabold text-emerald-700 text-sm md:text-base">${fmt(r.nominal_sudah)}</div></td>
                    <td class="px-3 py-2 border-r border-emerald-100 text-center bg-emerald-50/40"><div class="font-extrabold text-emerald-600 text-sm md:text-base">${pctRealPerCabang}%</div></td>
                </tr>`;
              html += rowHtml;
          });
          tb.innerHTML = html;

          // Kalkulasi Persentase Capaian Realisasi Grand Total
          const divisorReal = T.tgt_nom > 0 ? T.tgt_nom : 1;
          const pctReal = ((T.sdh_nom / divisorReal) * 100).toFixed(1);

          // Inject Grand Total ke Bawah Thead
          if (userKodeGlobal === '000') {
              trTot.innerHTML = `
                  <th class="sticky-left-1 px-3 border-r border-blue-200 text-center text-blue-900 hidden sm:table-cell bg-[#eff6ff]">-</th>
                  <th class="sticky-left-2 px-4 border-r border-blue-200 text-left text-blue-900 uppercase tracking-widest font-extrabold text-sm md:text-base bg-[#eff6ff]">TOTAL </th>
              `;
          } else {
              trTot.innerHTML = `
                  <th class="sticky-left-1 px-4 border-r border-blue-200 text-left text-blue-900 uppercase tracking-widest font-extrabold text-sm md:text-base bg-[#eff6ff]">TOTAL </th>
              `;
          }

          trTot.innerHTML += `
              <th class="px-4 border-r border-blue-200 text-right align-middle py-2 bg-[#eff6ff]"><div class="font-extrabold text-blue-900 text-sm md:text-base">${fmt(T.tgt_noa)}</div><div class="text-[11px] md:text-xs text-blue-700 font-mono mt-0.5 font-bold">${fmt(T.tgt_nom)}</div></th>
              
              <th class="px-4 border-r border-blue-200 text-center align-middle py-2 bg-[#eff6ff]"><div class="font-extrabold text-blue-800 text-sm md:text-base">${fmt(T.lun_noa)}</div><div class="text-[11px] md:text-xs text-blue-700 font-mono mt-0.5 font-bold">${fmt(T.lun_nom)}</div></th>
              <th class="px-4 border-r border-blue-200 text-center align-middle py-2 bg-[#eff6ff]"><div class="font-extrabold text-purple-800 text-sm md:text-base">${fmt(T.top_noa)}</div><div class="text-[11px] md:text-xs text-purple-700 font-mono mt-0.5 font-bold">${fmt(T.top_nom)}</div></th>
              <th class="px-4 border-r border-blue-200 text-center align-middle py-2 bg-[#eff6ff]"><div class="font-extrabold text-orange-800 text-sm md:text-base">${fmt(T.ret_noa)}</div><div class="text-[11px] md:text-xs text-orange-700 font-mono mt-0.5 font-bold">${fmt(T.ret_nom)}</div></th>
              
              <th class="px-4 border-r border-blue-200 text-center align-middle py-2 bg-[#eff6ff]"><div class="font-extrabold text-rose-800 text-sm md:text-base">${fmt(T.drop_noa)}</div><div class="text-[11px] md:text-xs text-rose-700 font-mono mt-0.5 font-bold">${fmt(T.drop_nom)}</div></th>
              
              <th class="px-3 border-r border-blue-200 text-center align-middle py-2 bg-[#eff6ff]"><div class="font-extrabold text-emerald-800 text-sm md:text-base">${fmt(T.sdh_noa)}</div></th>
              <th class="px-4 border-r border-blue-200 text-right align-middle py-2 bg-[#eff6ff]"><div class="font-extrabold text-emerald-800 text-sm md:text-base">${fmt(T.sdh_nom)}</div></th>
              <th class="px-3 text-center align-middle py-2 bg-[#eff6ff]"><div class="font-extrabold text-emerald-700 text-sm md:text-base">${pctReal}%</div></th>
          `;

          // Update Pills (Disamakan dengan nama kolom baru)
          T.pot_noa = T.lun_noa + T.top_noa + T.ret_noa;
          T.pot_nom = T.lun_nom + T.top_nom + T.ret_nom;

          document.getElementById('sum_sudah').innerText = fmt(T.sdh_noa);
          document.getElementById('sum_sudah_nom').innerText = `Rp ${fmt(T.sdh_nom)} (${pctReal}%)`;
          
          document.getElementById('sum_potensi').innerText = fmt(T.pot_noa);
          document.getElementById('sum_potensi_nom').innerText = 'Rp ' + fmt(T.pot_nom);

          pills.classList.remove('hidden');
          pills.classList.add('flex');

      } catch(e) { if(e.name!=='AbortError') console.error(e); } finally { l.classList.add('hidden'); }
  }

  // --- EXPORT EXCEL REKAP UTAMA ---
  window.exportExcelRekapPipeline = function() {
      if(!rekapDataCache || rekapDataCache.length === 0) return alert("Tidak ada data rekap untuk didownload.");

      let csv = "Kode\tNama Kantor\tTarget Pipeline NOA\tTarget Pipeline Plafon\tLunas NOA\tLunas Plafon\tTop Up NOA\tTop Up Sisa OS\tRetensi NOA\tRetensi Sisa OS\tDrop NOA\tDrop Sisa OS\tSudah Ambil NOA\tSudah Ambil Nominal\n";
      
      rekapDataCache.forEach(r => {
          csv += `'${r.kode_cabang}\t${r.nama_kantor||''}\t${r.noa_target}\t${Math.round(r.plafon_closing)}\t${r.noa_lunas}\t${Math.round(r.nominal_lunas)}\t${r.noa_topup}\t${Math.round(r.os_topup)}\t${r.noa_retensi}\t${Math.round(r.os_retensi)}\t${r.noa_drop}\t${Math.round(r.os_drop)}\t${r.noa_sudah}\t${Math.round(r.nominal_sudah)}\n`;
      });

      const blob = new Blob([csv], { type: 'application/vnd.ms-excel' });
      const a = document.createElement('a');
      a.href = window.URL.createObjectURL(blob);
      a.download = `Rekap_Pipeline_JT_${document.getElementById("tahun_jt").value}.xls`; 
      a.click();
  }

  // --- MODAL DETAIL NASABAH ---
  function openModal(cabang, nama) {
      if (userKodeGlobal !== '000' && String(cabang) !== userKodeGlobal) {
          alert(`AKSES DITOLAK!\nAnda tidak memiliki izin untuk melihat detail Cabang ${cabang}.`);
          return;
      }

      state.cabang = cabang; state.kankas = ''; state.ao = ''; state.status = ''; state.page = 1;
      const modal = document.getElementById('modalDetail');
      modal.classList.remove('hidden'); modal.classList.add('flex');
      
      document.getElementById('detailSubTitle').innerText = `${nama} • Tahun JT ${document.getElementById('tahun_jt').value}`;
      document.getElementById('filter_ao_modal').innerHTML = '<option value="">Semua AO</option>';
      loadKankasModal(cabang);
      fetchDetail();
  }

  async function loadKankasModal(kode_cabang) {
      const el = document.getElementById('filter_kankas_modal');
      el.innerHTML = '<option value="">Semua Kankas</option>';
      if(!kode_cabang) return;
      try {
          const payload = { type: 'kode_kankas', kode_kantor: kode_cabang };
          const json = await apiCall(API_KODE, payload);
          if(json.data && Array.isArray(json.data)) {
              json.data.forEach(x => { el.add(new Option(x.deskripsi_group1 || x.kode_group1, x.kode_group1)); });
          }
      } catch(e) {}
  }

  function changeFilter() {
      state.status = document.getElementById('filter_status_modal').value;
      state.ao = document.getElementById('filter_ao_modal').value;
      state.kankas = document.getElementById('filter_kankas_modal').value;
      state.page = 1;
      fetchDetail();
  }

  function changePage(step) {
      const next = state.page + step;
      if(next > 0 && next <= state.totalPages) { state.page = next; fetchDetail(); }
  }

  async function fetchDetail() {
      const l=document.getElementById('loadingDetail'), tb=document.getElementById('bodyDetail');
      const trTot = document.getElementById('rowTotalDetailAtas');
      l.classList.remove('hidden'); tb.innerHTML=''; trTot.innerHTML='';
      
      const actDate = new Date(document.getElementById('harian_date').value);

      try {
          const payload = {
              type: 'detail_pipeline',
              closing_date: document.getElementById('closing_date').value,
              harian_date: document.getElementById('harian_date').value,
              tahun_jt: document.getElementById('tahun_jt').value,
              kode_kantor: state.cabang, kode_kankas: state.kankas, kode_ao: state.ao, filter_status: state.status,
              page: state.page, limit: state.limit
          };

          const json = await apiCall(API_URL, payload);
          const rows = json.data?.data || [];
          const stats = json.data?.stats || {};
          const aoList = json.data?.list_ao || [];

          let totBaru = 0, totBaseLunasSudah = 0;
          let t_plafon_awal = 0, t_sisa_os = 0, t_nom_baru = 0; 

          rows.forEach(r => {
              const isClear = r.status_ket.toUpperCase().includes("SUDAH") || r.status_ket.toUpperCase() === "LUNAS" || r.status_ket.toUpperCase() === "LUNAS (POTENSI)";
              
              totBaru += parseFloat(r.plafon_baru || 0);
              if (isClear) totBaseLunasSudah += parseFloat(r.plafon_awal || 0);

              t_plafon_awal += parseFloat(r.plafon_awal||0);
              if(!isClear) t_sisa_os += parseFloat(r.os_actual||0);
              t_nom_baru += parseFloat(r.plafon_baru||0);
          });
          const pctBaru = totBaseLunasSudah > 0 ? ((totBaru / totBaseLunasSudah) * 100).toFixed(2) : 0;

          document.getElementById('modalStats').innerHTML = `
              <div class="flex gap-4 md:gap-8 px-2 items-center">
                 <div>Total: <span class="font-bold text-slate-800 text-sm">${fmt(stats.total_data)}</span></div>
                 <div class="text-emerald-600">Sudah: <span class="font-bold text-sm">${fmt(stats.cnt_sudah)}</span></div>
                 <div class="text-blue-600">Lunas: <span class="font-bold text-sm">${fmt(stats.cnt_lunas)}</span></div>
                 <div class="text-purple-600">TopUp: <span class="font-bold text-sm">${fmt(stats.cnt_topup)}</span></div>
                 <div class="text-orange-600">Retensi: <span class="font-bold text-sm">${fmt(stats.cnt_retensi)}</span></div>
                 <div class="text-rose-600">Drop: <span class="font-bold text-sm">${fmt(stats.cnt_drop)}</span></div>
                 
                 <div class="ml-auto bg-emerald-50 text-emerald-800 px-3 py-1 rounded border border-emerald-200">
                     % Realisasi Baru: <span class="font-bold font-mono text-sm">${pctBaru}%</span>
                 </div>
              </div>`;

          const selAO = document.getElementById('filter_ao_modal');
          if(selAO.options.length === 1 && aoList.length > 0) {
              aoList.forEach(ao => { selAO.add(new Option(ao.nama_ao, ao.kode_group2)); });
              selAO.value = state.ao;
          }

          if(rows.length === 0) {
              tb.innerHTML = `<tr><td colspan="12" class="text-center py-20 text-slate-400 italic text-base">Tidak ada data nasabah.</td></tr>`;
              document.getElementById('pageInfo').innerText = '0 Data';
              return;
          }

          state.totalPages = json.data?.pagination?.total_pages || 1;
          document.getElementById('pageInfo').innerText = `Hal ${state.page} / ${state.totalPages}`;

          rows.sort((a, b) => new Date(a.tgl_jatuh_tempo) - new Date(b.tgl_jatuh_tempo));

          trTot.innerHTML = `
              <th class="mod-sticky-1 px-3 border-r border-b border-blue-200 uppercase tracking-widest text-center bg-[#eff6ff]">-</th>
              <th class="mod-sticky-2 px-4 border-r border-b border-blue-200 uppercase tracking-widest font-extrabold text-sm bg-[#eff6ff]">TOTAL HALAMAN INI</th>
              <th class="px-4 border-r border-b border-blue-200 text-center bg-[#eff6ff]">-</th>
              <th class="px-3 border-r border-b border-blue-200 text-center bg-[#eff6ff]">-</th>
              <th class="px-3 border-r border-b border-blue-200 text-center bg-[#eff6ff]">-</th>
              <th class="px-4 border-r border-b border-blue-200 text-center bg-[#eff6ff]">-</th>
              <th class="px-4 border-r border-b border-blue-200 text-right font-mono font-bold text-sm text-blue-900 bg-[#eff6ff]">${fmt(t_plafon_awal)}</th>
              <th class="px-3 border-r border-b border-blue-200 text-center bg-[#eff6ff]">-</th>
              <th class="px-4 border-r border-b border-blue-300 text-right font-mono font-bold text-sm text-blue-900 bg-blue-100/50">${fmt(t_sisa_os)}</th>
              <th class="px-4 border-r border-b border-blue-200 text-center bg-[#eff6ff]">-</th>
              <th class="px-4 border-r border-b border-blue-200 text-right font-mono font-bold text-sm text-emerald-800 bg-emerald-50/50">${fmt(t_nom_baru)}</th>
              <th class="px-3 border-b border-blue-200 text-center bg-[#eff6ff]">-</th>
          `;

          let html = '';
          rows.forEach(r => {
              const aoName = (r.nama_ao || '-').split(' ').slice(0,2).join(' ');
              const alamatLengkap = r.alamat || '-';
              const alamatPendek = alamatLengkap.length > 25 ? alamatLengkap.substring(0, 25) + '...' : alamatLengkap;
              const noHp = r.no_hp ? `<span class="font-mono text-slate-600">${r.no_hp}</span>` : `<span class="text-slate-400">-</span>`;
              const kankas = r.kankas || '-';
              
              let statStr = (r.status_ket || '').toUpperCase();
              let isClear = statStr.includes("SUDAH") || statStr === "LUNAS" || statStr === "LUNAS (POTENSI)";
              let isDrop = statStr.includes("DROP");

              let sisaOsVisual = isClear ? '-' : fmt(r.os_actual);
              
              let nomBaru = '-';
              if (parseFloat(r.plafon_baru) > 0) {
                  nomBaru = `<div class="font-bold text-emerald-700 text-sm">${fmt(r.plafon_baru)}</div><div class="text-[10px] md:text-[11px] text-emerald-600 font-mono mt-0.5">${r.tgl_baru || ''}</div>`;
              }

              // MODIFIKASI 1: "LUNAS" TIDAK LAGI DILOCK (BISA PROSPEK)
              let isLocked = statStr.includes("SUDAH") || isDrop;
              const btnAksi = isLocked 
                  ? `<span class="text-xs font-bold text-slate-400">LOCKED</span>`
                  : `<button class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm transition w-full uppercase tracking-widest">PROSPEK</button>`;

              let badgeClass = "text-slate-600 border-slate-300 bg-slate-50";
              if(statStr.includes("SUDAH")) badgeClass = "text-emerald-700 border-emerald-300 bg-emerald-50/80";
              else if(statStr === "LUNAS" || statStr === "LUNAS (POTENSI)") badgeClass = "text-blue-700 border-blue-300 bg-blue-50/80";
              else if(statStr.includes("TOP UP")) badgeClass = "text-purple-700 border-purple-300 bg-purple-50/80";
              else if(statStr.includes("RETENSI")) badgeClass = "text-orange-700 border-orange-300 bg-orange-50/80";
              else if(statStr.includes("DROP")) badgeClass = "text-rose-700 border-rose-300 bg-rose-50/80";

              // MODIFIKASI 2 & 3: TAMBAHAN KETERANGAN PERSENTASE BD UNTUK LABEL RETENSI DAN TOP UP
              let displayStatus = r.status_ket;
              if (statStr.includes("RETENSI")) displayStatus = "RETENSI (BD > 50%)";
              else if (statStr.includes("TOP UP")) displayStatus = "TOP UP (BD <= 50%)";

              const jtDate = new Date(r.tgl_jatuh_tempo);
              const diffTime = jtDate - actDate;
              const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
              
              let strJt = `<div class="font-mono text-sm text-slate-700">${r.tgl_jatuh_tempo}</div>`;
              if (!isClear) {
                  if (diffDays < 0) {
                      strJt += `<div class="text-[10px] text-rose-600 font-bold mt-1 bg-rose-50 rounded inline-block px-1.5 py-0.5">Lewat ${Math.abs(diffDays)} Hari</div>`;
                  } else if (diffDays === 0) {
                      strJt += `<div class="text-[10px] text-orange-600 font-bold mt-1 bg-orange-50 rounded inline-block px-1.5 py-0.5">HARI INI!</div>`;
                  } else if (diffDays <= 30) {
                      strJt += `<div class="text-[10px] text-orange-500 font-bold mt-1">Kurang ${diffDays} Hari</div>`;
                  } else {
                      strJt += `<div class="text-[10px] text-slate-400 mt-1">${diffDays} Hari lagi</div>`;
                  }
              }

              html += `
                <tr class="transition h-[52px] group border-b border-slate-100">
                    <td class="mod-sticky-1 px-3 py-2 font-mono text-sm text-slate-500 bg-white border-r border-slate-100 shadow-[inset_-1px_0_0_#e2e8f0]">${r.no_rekening}</td>
                    <td class="mod-sticky-2 px-4 py-2 font-bold text-sm text-slate-700 bg-white truncate border-r border-slate-100 max-w-[220px] md:max-w-[280px] shadow-[inset_-1px_0_0_#e2e8f0]" title="${r.nama_nasabah}">${r.nama_nasabah}</td>
                    <td class="px-4 py-2 text-sm text-slate-500 whitespace-nowrap border-r border-slate-100" title="${alamatLengkap}">${alamatPendek}</td>
                    <td class="px-3 py-2 text-center border-r border-slate-100 text-sm">${noHp}</td>
                    <td class="px-3 py-2 text-center font-mono text-xs md:text-sm text-slate-500 border-r border-slate-100">${kankas}</td>
                    
                    <td class="px-4 py-2 text-sm font-bold text-blue-700 truncate border-r border-slate-100">${aoName}</td>
                    <td class="px-4 py-2 text-right font-medium text-sm text-slate-600 border-r border-slate-100">${fmt(r.plafon_awal)}</td>
                    <td class="px-3 py-2 text-center border-r border-slate-100">${strJt}</td>
                    <td class="px-4 py-2 text-right font-mono font-bold text-sm text-blue-700 bg-blue-50/30 border-r border-blue-100">${sisaOsVisual}</td>
                    <td class="px-4 py-2 text-center border-r border-slate-100"><span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] md:text-xs font-bold uppercase tracking-widest border ${badgeClass}">${displayStatus}</span></td>
                    <td class="px-4 py-2 text-right bg-emerald-50/30 border-r border-slate-100">${nomBaru}</td>
                    <td class="px-3 py-2 text-center">${btnAksi}</td>
                </tr>`;
          });
          tb.innerHTML = html;

          document.getElementById('btnPrev').disabled = state.page <= 1;
          document.getElementById('btnNext').disabled = state.page >= state.totalPages;

      } catch(e) { console.error(e); } finally { l.classList.add('hidden'); }
  }

  // --- EXPORT EXCEL DETAIL ---
  window.downloadExcelDetail = async function() {
      const btn = event.target.closest('button'); const txt = btn.innerHTML;
      btn.innerHTML = `<span class="animate-spin inline-block h-4 w-4 border-2 border-white border-t-transparent rounded-full mr-2"></span>...`;
      btn.disabled = true;

      try {
          const payload = {
              type: 'detail_pipeline',
              closing_date: document.getElementById('closing_date').value,
              harian_date: document.getElementById('harian_date').value,
              tahun_jt: document.getElementById('tahun_jt').value,
              kode_kantor: state.cabang, kode_kankas: state.kankas, kode_ao: state.ao, filter_status: state.status, 
              page: 1, limit: 10000 
          };
          const json = await apiCall(API_URL, payload);
          let rows = json.data?.data || [];
          
          if(rows.length===0) { alert('Data kosong'); btn.innerHTML=txt; btn.disabled=false; return; }

          rows.sort((a, b) => new Date(a.tgl_jatuh_tempo) - new Date(b.tgl_jatuh_tempo));

          let csv = "No Rekening\tNama Nasabah\tAlamat\tNo HP\tKankas\tNama AO\tPlafon Awal\tTgl JT\tSisa OS\tStatus\tTgl Realisasi Baru\tNominal Baru\n";
          rows.forEach(r => {
              const isClear = r.status_ket.toUpperCase().includes("SUDAH") || r.status_ket.toUpperCase() === "LUNAS" || r.status_ket.toUpperCase() === "LUNAS (POTENSI)";
              const sisaOsEx = isClear ? 0 : Math.round(r.os_actual);
              const alamatEx = r.alamat || '-';
              const hpEx = r.no_hp || '-';
              const kankasEx = r.kankas || '-';

              csv += `'${r.no_rekening}\t${r.nama_nasabah}\t${alamatEx}\t'${hpEx}\t${kankasEx}\t${r.nama_ao}\t${Math.round(r.plafon_awal)}\t${r.tgl_jatuh_tempo}\t${sisaOsEx}\t${r.status_ket}\t${r.tgl_baru||'-'}\t${Math.round(r.plafon_baru||0)}\n`;
          });

          const blob = new Blob([csv], { type: 'application/vnd.ms-excel' });
          const url = window.URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.href = url;
          a.download = `Detail_Pipeline_JT_${state.cabang}.xls`;
          document.body.appendChild(a); a.click(); document.body.removeChild(a);

      } catch(e) { alert('Gagal export'); } finally { btn.innerHTML=txt; btn.disabled=false; }
  }

  function closeModal() { 
      const modal = document.getElementById('modalDetail');
      modal.classList.add('hidden'); 
      modal.classList.remove('flex');
  }

  // --- MODAL INFO HANDLERS ---
  function openInfoModal() {
      const m = document.getElementById('infoModal');
      m.classList.remove('hidden'); m.classList.add('flex');
  }
  
  function closeInfoModal() {
      const m = document.getElementById('infoModal');
      m.classList.add('hidden'); m.classList.remove('flex');
  }

  document.addEventListener('keydown', e => { 
      if(e.key === 'Escape') {
          closeModal();
          closeInfoModal();
      }
  });
</script>