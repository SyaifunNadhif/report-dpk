<style>
  /* Custom Scrollbar untuk Table Wrapper */
  .custom-scrollbar::-webkit-scrollbar { height: 8px; width: 8px; }
  .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
  .no-scrollbar::-webkit-scrollbar { display: none; }
  .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

  /* Animasi Modal */
  @keyframes scaleUp { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
  .animate-scale-up { animation: scaleUp 0.2s ease-out forwards; }

  /* CSS MAGIC STICKY */
  #tabelMigrasiSC thead th { position: sticky; box-shadow: inset 0 -1px 0 #cbd5e1; }
  
  /* Lapis 1 (Header Utama) */
  #tabelMigrasiSC thead tr:nth-child(1) th { top: 0; z-index: 40; height: 46px; background-color: #f8fafc;}
  #tabelMigrasiSC thead tr:nth-child(1) th.sticky-left { z-index: 50; left: 0; box-shadow: inset -1px -1px 0 #cbd5e1; background-color: #f8fafc; border-top-left-radius: 10px;} 
  
  /* Lapis 2 (NOA, OS) */
  #tabelMigrasiSC thead tr:nth-child(2) th { top: 46px; z-index: 39; height: 38px; background-color: #f8fafc;}
  
  /* Lapis 3 (Grand Total - Beda Warna & Freeze) */
  #tabelMigrasiSC thead tr:nth-child(3) th { top: 84px; z-index: 38; height: 50px; box-shadow: inset 0 -2px 0 #93c5fd; background-color: #eff6ff !important; }
  #tabelMigrasiSC thead tr:nth-child(3) th.sticky-left { z-index: 48; left: 0; box-shadow: inset -1px -2px 0 #93c5fd; background-color: #dbeafe !important; }

  /* Freeze Kiri Body */
  .sticky-left { position: sticky; left: 0; }
  #bodyMatrix td.sticky-left { z-index: 20; background-color: #ffffff; box-shadow: inset -1px 0 0 #e2e8f0; }
  #bodyMatrix tr:hover td.sticky-left { background-color: #f8fafc !important; }
  #bodyMatrix tr:hover td { background-color: #f8fafc; cursor: pointer; }

  /* CSS form filter */
  .inp { border:1px solid #cbd5e1; border-radius:6px; padding:0 10px; background:#fff; outline:none; transition: border 0.2s;}
  .inp:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,0.1); }
  .lbl { font-size:10px; color:#475569; font-weight:800; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.05em; display:block; white-space: nowrap;}
  .field { display:flex; flex-direction:column; }
  .btn-icon { display:inline-flex; align-items:center; justify-content:center; border:none; cursor:pointer; transition: transform 0.2s;}
  .btn-icon:hover { transform:translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }

  /* Hilangkan Date Icon Default */
  input[type="date"]::-webkit-inner-spin-button, input[type="date"]::-webkit-calendar-picker-indicator { display: none; -webkit-appearance: none; }
  input[type="date"] { -moz-appearance: textfield; }

  /* Freeze Modal Columns */
  #bodyDetail tr:hover td { background-color: #f8fafc; }
  #bodyDetail tr:hover td.sticky { background-color: #f8fafc !important; }
</style>

<div class="max-w-[1920px] mx-auto px-2 md:px-4 py-4 md:py-6 h-[calc(100vh-80px)] flex flex-col font-sans text-slate-800 bg-slate-50 overflow-hidden">
  
  <div class="flex-none mb-4 flex flex-col xl:flex-row justify-between items-start gap-4 w-full">
    
    <div class="flex flex-col gap-3 shrink-0 w-full xl:w-auto">
      <h1 class="text-xl md:text-2xl font-bold text-slate-800 flex items-center gap-2 mb-1">
          <span class="p-1.5 md:p-2 bg-blue-600 rounded-lg text-white shadow-sm">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
          </span>
          Monitoring Migrasi SC
      </h1>

      <div id="summaryCheck" class="hidden flex-wrap items-center gap-3">
          <div class="flex flex-col bg-white border border-slate-200 px-3 py-1.5 rounded-lg shadow-sm min-w-[160px]">
              <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">FLOW SC ➔ FE/BE</span>
              <div class="flex items-center gap-2">
                  <span class="text-xs font-bold bg-red-100 text-red-600 px-1.5 py-0.5 rounded" id="flowBadPct">0.00%</span>
                  <span class="text-sm font-bold text-red-600 font-mono tracking-tight" id="flowBadVal">0</span>
              </div>
          </div>

          <div class="flex flex-col bg-white border border-slate-200 px-3 py-1.5 rounded-lg shadow-sm min-w-[160px]">
              <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">FLOW FE/BE ➔ SC</span>
              <div class="flex items-center gap-2">
                  <span class="text-xs font-bold bg-green-100 text-green-700 px-1.5 py-0.5 rounded" id="flowGoodPct">0.00%</span>
                  <span class="text-sm font-bold text-green-700 font-mono tracking-tight" id="flowGoodVal">0</span>
              </div>
          </div>

          <div class="flex flex-col bg-white border border-slate-200 px-3 py-1.5 rounded-lg shadow-sm min-w-[160px]">
              <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">GROWTH (ACTUAL - M1)</span>
              <div class="flex items-center gap-2" id="growthContainer"></div>
          </div>
      </div>
    </div>

    <form id="formFilterMigrasi" class="flex flex-wrap md:flex-nowrap items-end gap-2 bg-white p-3 rounded-xl border border-slate-200 shadow-sm shrink-0 xl:ml-auto w-full md:w-auto overflow-x-auto no-scrollbar" onsubmit="event.preventDefault(); fetchMatrix();">
        
        <div class="field shrink-0 w-[130px] md:w-[150px]">
            <label class="lbl">CLOSING (M-1)</label>
            <input type="date" id="closing_date" class="inp text-sm font-semibold h-[36px]" required onclick="try{this.showPicker()}catch(e){}">
        </div>
        
        <div class="field shrink-0 w-[130px] md:w-[150px]">
            <label class="lbl">ACTUAL (HARIAN)</label>
            <input type="date" id="harian_date" class="inp text-sm font-semibold h-[36px]" required onclick="try{this.showPicker()}catch(e){}">
        </div>
        
        <div class="field shrink-0 w-[160px] md:w-[200px]">
            <label class="lbl">CABANG</label>
            <select id="opt_kantor" class="inp text-sm font-semibold h-[36px] truncate cursor-pointer" onchange="fetchMatrix()">
                <option>Loading...</option>
            </select>
        </div>
        
        <div class="flex items-center gap-1.5 shrink-0 h-[36px] mb-px">
            <button type="submit" class="btn-icon h-full px-5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-sm text-sm font-bold uppercase tracking-wider" title="Cari Data">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" class="md:mr-1.5"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <span class="hidden md:inline">CARI</span>
            </button>
            <button type="button" onclick="exportExcelRekapMigrasi()" class="btn-icon h-full w-[42px] bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-sm" title="Download Excel">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            </button>
        </div>
    </form>
  </div>

  <div class="flex-1 min-h-0 overflow-hidden bg-white rounded-xl shadow-sm border border-slate-200 relative">
    <div id="loadingMatrix" class="hidden absolute inset-0 bg-white/80 z-[100] flex flex-col items-center justify-center text-blue-600 backdrop-blur-sm">
        <div class="animate-spin rounded-full h-10 w-10 border-4 border-blue-500 border-t-transparent mb-3"></div>
        <span class="text-sm font-bold uppercase tracking-widest">Menyiapkan Matriks...</span>
    </div>
    
    <div class="h-full overflow-auto custom-scrollbar relative">
      <table class="w-max min-w-full text-center border-separate border-spacing-0 text-slate-700 table-fixed" id="tabelMigrasiSC">
        <thead class="text-slate-800 font-bold tracking-wider text-xs md:text-sm">
          <tr>
            <th rowspan="2" class="sticky-left px-3 text-left w-[120px] md:w-[150px] uppercase align-middle bg-slate-50 border-r border-slate-200 text-blue-900">DPD M-1</th>
            <th colspan="2" class="px-2 uppercase border-r border-slate-200 align-middle bg-slate-50 text-blue-900">Posisi M-1</th>
            <th rowspan="2" class="px-2 border-r border-green-200 w-[100px] md:w-[130px] text-green-700 bg-[#f0fdf4] align-middle">0</th>
            <th rowspan="2" class="px-2 border-r border-yellow-200 w-[100px] md:w-[130px] text-yellow-700 bg-[#fefce8] align-middle">1 - 7</th>
            <th rowspan="2" class="px-2 border-r border-yellow-200 w-[100px] md:w-[130px] text-yellow-700 bg-[#fefce8] align-middle">8 - 14</th>
            <th rowspan="2" class="px-2 border-r border-yellow-200 w-[100px] md:w-[130px] text-yellow-800 bg-[#fef9c3] align-middle">15 - 21</th>
            <th rowspan="2" class="px-2 border-r border-orange-200 w-[100px] md:w-[130px] text-orange-700 bg-[#fff7ed] align-middle">22 - 30</th>
            <th rowspan="2" class="px-2 border-r border-red-200 w-[100px] md:w-[130px] text-red-700 bg-[#fef2f2] align-middle">FE (31-90)</th>
            <th rowspan="2" class="px-2 border-r border-red-200 w-[100px] md:w-[130px] text-red-800 bg-[#fee2e2] align-middle">BE (>90)</th>
            <th rowspan="2" class="px-2 border-r border-slate-200 w-[100px] md:w-[130px] uppercase align-middle bg-slate-50 text-blue-700">ANGSURAN</th>
            <th rowspan="2" class="px-2 border-r border-slate-200 w-[100px] md:w-[130px] uppercase align-middle bg-slate-50 text-blue-700">PELUNASAN</th>
            <th rowspan="2" class="px-2 w-[110px] md:w-[140px] uppercase align-middle bg-slate-100 text-red-600">TOT RUN OFF</th>
          </tr>
          <tr class="text-[10px] md:text-xs">
            <th class="px-2 border-r border-slate-200 w-[50px] md:w-[60px] uppercase align-middle bg-slate-50 text-blue-900">NOA</th>
            <th class="px-2 border-r border-slate-200 w-[110px] md:w-[140px] uppercase align-middle bg-slate-50 text-blue-900">OS</th>
          </tr>
          <tr id="rowTotalMigrasiAtas" class="text-sm md:text-base font-extrabold tracking-wide"></tr>
        </thead>
        <tbody id="bodyMatrix" class="divide-y divide-slate-100 bg-white text-sm"></tbody>
      </table>
    </div>
  </div>
</div>

<div id="modalDetail" class="fixed inset-0 hidden z-[9999] flex items-end md:items-center justify-center p-2 md:p-4">
  <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
  <div class="relative bg-white w-full h-[92vh] max-w-[1700px] rounded-t-xl md:rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-scale-up">
    
    <div class="flex justify-between items-center px-4 py-4 md:px-6 border-b bg-slate-50 shrink-0 flex-wrap gap-3">
        <div class="flex-1 min-w-[250px]">
            <h3 class="font-bold text-slate-800 flex items-center gap-2 text-base md:text-xl">
                <span class="w-2 h-6 bg-blue-600 rounded-full hidden md:block"></span>  
                Detail Nasabah 
                <span id="badgeMigrasi" class="text-xs md:text-sm bg-blue-600 text-white px-2.5 py-0.5 rounded-full shadow-sm ml-2 font-mono">...</span>
            </h3>
        </div>
        
        <div class="flex items-center gap-2 md:gap-3 ml-auto shrink-0 w-full sm:w-auto mt-2 sm:mt-0">
            <select id="opt_kankas_modal" class="inp px-3 h-10 flex-1 sm:w-[160px] text-xs md:text-sm font-bold text-blue-800 bg-blue-50 outline-none" onchange="loadDetail()">
                <option value="">Semua Kankas</option>
            </select>

            <button onclick="exportExcelDetailMigrasi()" class="h-10 px-4 border-none bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition shadow-sm flex items-center justify-center font-bold text-xs md:text-sm uppercase tracking-wider">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span class="hidden sm:inline ml-1.5">Excel</span>
            </button>
            <button onclick="closeModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-200 hover:bg-red-500 hover:text-white text-slate-600 transition font-bold text-2xl leading-none">&times;</button>
        </div>
    </div>

    <div class="flex-1 overflow-auto bg-slate-50 relative custom-scrollbar p-0 md:p-3">
        <div id="loadingDetail" class="hidden absolute inset-0 bg-white/90 z-40 flex flex-col items-center justify-center text-blue-600 backdrop-blur-sm">
            <div class="animate-spin rounded-full h-10 w-10 border-4 border-blue-500 border-t-transparent mb-3"></div>
            <span class="text-sm font-bold uppercase tracking-widest">Memuat Detail...</span>
        </div>
        
        <table class="w-max min-w-full text-sm text-left text-slate-700 border border-slate-200 md:rounded-xl shadow-sm bg-white table-fixed" id="tableExportMigrasi">
            <thead id="headModalRR" class="bg-slate-100 text-slate-600 font-extrabold sticky top-0 z-30 shadow-sm text-xs uppercase tracking-wider">
                <tr>
                    <th class="px-3 py-4 border-b border-r border-slate-200 w-[140px] sticky left-0 bg-slate-100 z-40 shadow-[1px_0_0_#cbd5e1] rounded-tl-xl text-blue-900">Rekening</th>
                    <th class="px-4 py-4 border-b border-r border-slate-200 w-[240px] sticky left-[140px] bg-slate-100 z-40 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.1)] text-blue-900">Nama Nasabah</th>
                    <th class="px-4 py-4 border-b border-r border-slate-200 w-[180px]">Alamat</th>
                    <th class="px-3 py-4 border-b border-r border-slate-200 w-[130px] text-center">No HP (WA)</th>
                    <th class="px-3 py-4 border-b border-r border-slate-200 w-[120px] text-center">Kankas</th>
                    <th class="px-4 py-4 border-b border-r border-slate-200 w-[130px] text-right text-blue-700">OS M-1</th>
                    <th class="px-4 py-4 border-b border-r border-green-200 w-[130px] text-right bg-green-50 text-green-700">OS Current</th>
                    <th class="px-3 py-4 border-b border-r border-slate-200 w-[110px] text-center">Status</th>
                    <th class="px-3 py-4 border-b border-r border-slate-200 w-[60px] text-center">Kol</th>
                    <th class="px-4 py-4 border-b border-r border-red-200 w-[120px] text-right bg-red-50 text-red-700">Tgk Pokok</th>
                    <th class="px-4 py-4 border-b border-r border-orange-200 w-[120px] text-right bg-orange-50 text-orange-700">Tgk Bunga</th>
                    <th class="px-4 py-4 border-b border-r border-slate-200 w-[130px] text-right">Tabungan</th>
                    <th class="px-3 py-4 border-b border-slate-200 w-[100px] text-center">Stat Tab</th>
                </tr>
            </thead>
            <tbody id="bodyDetail" class="divide-y divide-slate-100 bg-white text-sm"></tbody>
        </table>
    </div>

    <div class="px-4 py-4 md:px-6 border-t bg-white flex justify-between items-center shrink-0">
        <span id="pageInfo" class="text-xs md:text-sm font-bold text-slate-600 bg-slate-100 px-3 py-1 rounded-lg">0 Data</span>
        <div class="flex gap-2">
            <button id="btnPrev" onclick="changePageDetail(-1)" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 hover:border-slate-400 disabled:opacity-50 transition shadow-sm">« Prev</button>
            <button id="btnNext" onclick="changePageDetail(1)" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 hover:border-slate-400 disabled:opacity-50 transition shadow-sm">Next »</button>
        </div>
    </div>
  </div>
</div>

<script>
// --- KONFIGURASI ---
const API_ENDPOINT = './api/bucket_fe/'; 
const API_KODE = './api/kode/';
const API_DATE = './api/date/'; 
const nfID = new Intl.NumberFormat('id-ID');
const fmt = n => nfID.format(Math.round(Number(n||0)));
const apiCall = (u,o) => window.apiFetch ? window.apiFetch(u,o) : fetch(u,o);
const BUCKETS = ['0','1-7','8-14','15-21','22-30','FE','BE'];
let modalState = {from:'', to:'', page:1, limit:50};

let rekapDataCache = null; 

// --- INIT ---
window.addEventListener('DOMContentLoaded', async () => {
    const user = (window.getUser && window.getUser()) || null;
    const uKode = (user?.kode ? String(user.kode).padStart(3,'0') : '000');
    
    await populateKantor(uKode);
    await loadKankasModalDropdown();
    
    const d = await getLastHarianData();
    if(d){ 
        document.getElementById('closing_date').value = d.last_closing; 
        document.getElementById('harian_date').value = d.last_created; 
    }
    
    fetchMatrix();
});

async function getLastHarianData(){ 
    try{ const r = await apiCall(API_DATE); const j = await r.json(); return j.data; }
    catch{ return null; }
}

async function populateKantor(uKode){
    const el = document.getElementById('opt_kantor');
    if(uKode !== '000'){
        el.innerHTML = `<option value="${uKode}">CABANG ${uKode}</option>`; 
        el.value = uKode;
        el.disabled = true; 
        return;
    }
    try{ 
        const r = await apiCall(API_KODE,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({type:'kode_kantor'})}); 
        const j = await r.json();
        let h = '<option value="">KONSOLIDASI (SEMUA)</option>'; 
        (j.data||[]).filter(x => x.kode_kantor !== '000')
            .sort((a,b) => a.kode_kantor.localeCompare(b.kode_kantor))
            .forEach(x => h += `<option value="${x.kode_kantor}">${x.kode_kantor} - ${x.nama_kantor}</option>`); 
        el.innerHTML = h; 
    } catch{}
}

async function loadKankasModalDropdown() {
    const elKankas = document.getElementById('opt_kankas_modal');
    const branch = document.getElementById('opt_kantor').value;
    elKankas.innerHTML = '<option value="">Semua Kankas</option>';
    if(!branch || branch === '') return;

    try {
        const r = await apiCall(API_KODE, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({type: 'kode_kankas', kode_kantor: branch}) });
        const j = await r.json();
        let h = '<option value="">Semua Kankas</option>';
        if(j.data && Array.isArray(j.data)) {
            j.data.forEach(x => { h += `<option value="${x.kode_group1}">${x.deskripsi_group1 || x.kode_group1}</option>`; });
        }
        elKankas.innerHTML = h;
    } catch(err) { }
}

// --- CORE LOGIC ---
async function fetchMatrix(){
    const l = document.getElementById('loadingMatrix'); 
    const tb = document.getElementById('bodyMatrix');
    const trTotal = document.getElementById('rowTotalMigrasiAtas');

    l.classList.remove('hidden');
    tb.innerHTML = `<tr><td colspan="13" class="py-20 text-center text-slate-400 italic text-base">Sedang mengambil data...</td></tr>`;
    trTotal.innerHTML = '';
    rekapDataCache = null;

    try{
        const pl = { 
            type: "rekap_migrasi_bucket", 
            closing_date: document.getElementById('closing_date').value, 
            harian_date: document.getElementById('harian_date').value, 
            kode_kantor: document.getElementById('opt_kantor').value || null 
        };
        const r = await apiCall(API_ENDPOINT, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(pl) }); 
        const j = await r.json();
        
        rekapDataCache = j.data; 
        renderMatrix(j.data);
    } catch(e){ 
        console.error(e); 
        tb.innerHTML = `<tr><td colspan="13" class="py-16 text-center text-red-500 font-bold uppercase tracking-widest text-sm">Gagal memuat matriks</td></tr>`;
    } 
    finally{ l.classList.add('hidden'); }
}

function renderMatrix(data){
    const GT = data.grand_total;
    const real = data.realisasi;
    
    // --- 1. HITUNG GROWTH ---
    const osM1 = parseFloat(GT.m1.os || 0);
    let totalOsCurrent = 0;
    Object.values(GT.buckets).forEach(b => totalOsCurrent += parseFloat(b.os || 0));
    const osCurr = totalOsCurrent + parseFloat(real.os || 0);

    const diffVal = osCurr - osM1;
    const diffPct = osM1 > 0 ? (diffVal / osM1) * 100 : 0;
    
    // --- 2. HITUNG FLOW PEMBURUKAN & PERBAIKAN ---
    const goodBuckets = ['0', '1-7', '8-14', '15-21', '22-30'];
    const badBuckets = ['FE', 'BE'];

    let flowBadOS = 0;   let totalGoodM1 = 0;
    let flowGoodOS = 0;  let totalBadM1 = 0;

    goodBuckets.forEach(b => { if(data.summary_m1[b]) totalGoodM1 += parseFloat(data.summary_m1[b].os_m1 || 0); });
    badBuckets.forEach(b => { if(data.summary_m1[b]) totalBadM1 += parseFloat(data.summary_m1[b].os_m1 || 0); });

    goodBuckets.forEach(src => { badBuckets.forEach(tgt => { if(data.matrix[src] && data.matrix[src][tgt]) flowBadOS += parseFloat(data.matrix[src][tgt].os || 0); }); });
    badBuckets.forEach(src => { goodBuckets.forEach(tgt => { if(data.matrix[src] && data.matrix[src][tgt]) flowGoodOS += parseFloat(data.matrix[src][tgt].os || 0); }); });

    const flowBadPct = totalGoodM1 > 0 ? (flowBadOS / totalGoodM1) * 100 : 0;
    const flowGoodPct = totalBadM1 > 0 ? (flowGoodOS / totalBadM1) * 100 : 0;

    // --- RENDER HEADER SUMMARY ---
    document.getElementById('summaryCheck').classList.remove('hidden');
    document.getElementById('summaryCheck').classList.add('flex');

    const isUp = diffVal >= 0;
    const colorGrowth = isUp ? 'text-green-700' : 'text-red-700';
    const bgGrowth = isUp ? 'bg-green-100' : 'bg-red-100';
    
    document.getElementById('growthContainer').innerHTML = `
        <span class="text-xs font-bold ${bgGrowth} ${colorGrowth} px-2 py-0.5 rounded">${Math.abs(diffPct).toFixed(2)}%</span>
        <span class="text-sm font-bold font-mono ${colorGrowth} tracking-tight">${fmt(diffVal)}</span>
    `;

    document.getElementById('flowBadPct').innerText = flowBadPct.toFixed(2) + '%';
    document.getElementById('flowBadVal').innerText = fmt(flowBadOS);

    document.getElementById('flowGoodPct').innerText = flowGoodPct.toFixed(2) + '%';
    document.getElementById('flowGoodVal').innerText = fmt(flowGoodOS);

    // --- RENDER TOTAL STICKY ---
    let tf = `<th class="sticky-left px-3 text-left uppercase tracking-widest align-middle text-blue-900 bg-[#eff6ff]">TOTAL</th>
              <th class="border-r border-blue-300 px-3 text-right font-mono align-middle text-blue-900 bg-[#eff6ff]">${fmt(GT.m1.noa)}</th>
              <th class="border-r border-blue-300 px-3 text-right font-mono text-sm md:text-base text-blue-900 align-middle bg-[#eff6ff]">${fmt(GT.m1.os)}</th>`;
    
    BUCKETS.forEach(b => { 
        tf += `<th class="border-r border-blue-300 p-0 align-middle bg-[#eff6ff]"><div class="flex flex-col justify-center h-full py-1.5"><span class="text-[10px] md:text-[11px] text-blue-700 font-mono font-medium">${fmt(GT.buckets[b].noa)}</span><span class="text-xs md:text-sm text-blue-900 font-bold font-mono">${fmt(GT.buckets[b].os)}</span></div></th>` 
    });
    
    tf += `<th class="border-r border-blue-300 text-right px-3 font-mono font-bold text-blue-700 align-middle bg-[#eff6ff]">${fmt(GT.angsuran)}</th>
           <th class="border-r border-blue-300 p-0 align-middle bg-[#eff6ff]"><div class="flex flex-col justify-center h-full py-1.5"><span class="text-[10px] md:text-[11px] text-blue-700 font-mono font-medium">${fmt(GT.lunas.noa)}</span><span class="text-xs md:text-sm text-blue-900 font-bold font-mono">${fmt(GT.lunas.os)}</span></div></th>
           <th class="border-blue-300 text-right px-3 font-mono font-bold text-red-600 align-middle bg-[#eff6ff]">${fmt(GT.runoff_total.os)}</th>`;
    
    document.getElementById('rowTotalMigrasiAtas').innerHTML = tf;

    // --- RENDER TABEL BODY ---
    const tb = document.getElementById('bodyMatrix'); 
    let h = '';
    
    // Baris Realisasi
    h += `<tr class="bg-emerald-50/40 hover:bg-emerald-100 transition border-b border-emerald-100 group">
            <td class="sticky-left px-3 py-3 text-left font-bold text-emerald-800 bg-emerald-50 group-hover:bg-emerald-100 border-r border-emerald-200 text-xs md:text-sm align-middle shadow-[inset_-1px_0_0_#a7f3d0]">REALISASI BARU</td>
            <td class="border-r border-emerald-100 text-slate-400 align-middle text-center">-</td><td class="border-r border-emerald-100 text-slate-400 align-middle text-center">-</td>
            <td class="border-r border-emerald-100 p-0 align-middle"><div class="cursor-pointer py-2 transition" onclick="openDetail('REALISASI','0')"><div class="font-bold text-blue-600 text-[11px] md:text-xs font-mono">${fmt(real.noa)}</div><div class="font-bold text-emerald-800 text-sm md:text-base font-mono">${fmt(real.os)}</div><div class="text-[10px] font-bold text-emerald-600 mt-0.5">100%</div></div></td>
            <td colspan="6" class="text-xs italic text-slate-400 border-r border-emerald-100 align-middle">Detail Tersebar</td>
            <td class="border-r border-emerald-100 text-slate-400 align-middle text-center">-</td><td class="border-r border-emerald-100 text-slate-400 align-middle text-center">-</td><td class="text-slate-400 align-middle text-center">-</td>
          </tr>`;

    // Loop Matrix Bucket
    BUCKETS.forEach((f, i) => {
        const m1 = data.summary_m1[f];
        h += `<tr class="hover:bg-slate-50 transition border-b border-slate-200 h-[52px] group">
                <td class="sticky-left px-3 text-left font-bold text-slate-700 bg-white group-hover:bg-slate-50 border-r border-slate-200 text-xs md:text-sm align-middle">${f}</td>
                <td class="border-r border-slate-200 text-right px-2 font-mono text-[11px] md:text-xs font-medium text-slate-500 align-middle">${fmt(m1.noa_m1)}</td>
                <td class="border-r border-slate-200 text-right px-3 font-mono font-bold text-slate-800 text-xs md:text-sm align-middle">${fmt(m1.os_m1)}</td>`;
        
        let ar = 0;
        BUCKETS.forEach((t, j) => {
            const c = data.matrix[f][t]; 
            ar += parseFloat(c.angsuran || 0);
            
            let bgClass = ''; let textClass = 'text-slate-800'; let pctVal = '0%';

            if(c.os > 0 && m1.os_m1 > 0){
                pctVal = ((c.os / m1.os_m1) * 100).toFixed(1) + '%';
                if (j > i) { 
                    bgClass = 'bg-red-50/70 border-red-100'; textClass = 'text-red-700'; // Pemburukan
                } else if (j < i) {
                    bgClass = 'bg-emerald-50/70 border-emerald-100'; textClass = 'text-emerald-700'; // Perbaikan
                } else {
                    bgClass = 'bg-blue-50/40 border-blue-100'; textClass = 'text-blue-800'; // Tetap
                }
            }

            let clickEv = (c.os > 0) ? `onclick="openDetail('${f}','${t}')"` : '';
            let cursor = (c.os > 0) ? 'cursor-pointer hover:brightness-95 hover:shadow-inner' : '';

            h += `<td class="border-r border-slate-200 p-0 align-middle ${bgClass}"><div class="h-full flex flex-col justify-center py-1.5 ${cursor} transition" ${clickEv}>
                    <span class="text-[10px] md:text-[11px] font-semibold text-slate-500 font-mono">${fmt(c.noa)}</span>
                    <span class="text-xs md:text-sm font-bold font-mono ${textClass}">${fmt(c.os)}</span>
                    ${c.os > 0 ? `<span class="text-[9px] md:text-[10px] font-bold ${textClass} opacity-70">${pctVal}</span>` : ''}
                </div></td>`;
        });
        
        const l = data.matrix[f]['O'];
        h += `<td class="border-r border-slate-200 align-middle px-2"><div class="font-mono font-bold text-blue-700 text-xs md:text-sm">${fmt(ar)}</div></td>
              <td class="border-r border-slate-200 p-0 align-middle"><div class="h-full flex flex-col justify-center cursor-pointer hover:bg-blue-50 transition py-1.5 px-2" onclick="openDetail('${f}','O')"><span class="text-[10px] md:text-[11px] font-semibold text-blue-500 font-mono">${fmt(l.noa)}</span><span class="text-xs md:text-sm font-bold text-blue-800 font-mono">${fmt(l.pelunasan)}</span></div></td>
              <td class="font-mono font-bold text-red-600 text-xs md:text-sm align-middle px-2">${fmt(ar + l.pelunasan)}</td></tr>`;
    });
    tb.innerHTML = h;
}

/* EXPORT EXCEL REKAP UTAMA MIGRASI */
window.exportExcelRekapMigrasi = function() {
    if(!rekapDataCache || !rekapDataCache.matrix) return alert("Tidak ada data rekap untuk didownload.");

    let csv = "DPD M-1\tNOA M-1\tOS M-1\tNOA 0\tOS 0\tNOA 1-7\tOS 1-7\tNOA 8-14\tOS 8-14\tNOA 15-21\tOS 15-21\tNOA 22-30\tOS 22-30\tNOA FE\tOS FE\tNOA BE\tOS BE\tAngsuran\tNOA Lunas\tOS Lunas\tTotal Run Off\n";
    
    const real = rekapDataCache.realisasi;
    csv += `Realisasi Baru\t-\t-\t${real.noa}\t${Math.round(real.os)}\t-\t-\t-\t-\t-\t-\t-\t-\t-\t-\t-\t-\t-\t-\t-\t-\n`;

    BUCKETS.forEach(f => {
        const m1 = rekapDataCache.summary_m1[f];
        let rowStr = `'${f}\t${m1.noa_m1}\t${Math.round(m1.os_m1)}\t`;
        
        let ar = 0;
        BUCKETS.forEach(t => {
            const c = rekapDataCache.matrix[f][t];
            ar += parseFloat(c.angsuran || 0);
            rowStr += `${c.noa}\t${Math.round(c.os)}\t`;
        });
        
        const l = rekapDataCache.matrix[f]['O'];
        const totalRunOff = ar + parseFloat(l.pelunasan || 0);
        
        rowStr += `${Math.round(ar)}\t${l.noa}\t${Math.round(l.pelunasan)}\t${Math.round(totalRunOff)}\n`;
        csv += rowStr;
    });

    const GT = rekapDataCache.grand_total;
    csv += `TOTAL\t${GT.m1.noa}\t${Math.round(GT.m1.os)}\t`;
    BUCKETS.forEach(b => { csv += `${GT.buckets[b].noa}\t${Math.round(GT.buckets[b].os)}\t`; });
    csv += `${Math.round(GT.angsuran)}\t${GT.lunas.noa}\t${Math.round(GT.lunas.os)}\t${Math.round(GT.runoff_total.os)}\n`;

    const blob = new Blob([csv], { type: 'application/vnd.ms-excel' });
    const a = document.createElement('a');
    a.href = window.URL.createObjectURL(blob);
    a.download = `Rekap_Migrasi_SC_${document.getElementById("harian_date").value}.xls`; 
    a.click();
}

function formatWA(phone) {
    if (!phone) return null;
    let cleaned = phone.replace(/\D/g, ''); 
    if (cleaned.startsWith('0')) { cleaned = '62' + cleaned.substring(1); } 
    else if (cleaned.startsWith('8')) { cleaned = '62' + cleaned; }
    if (cleaned.length < 10) return null;
    return cleaned;
}

function createWABtn(phone, nama, norek, totung) {
    const formatted = formatWA(phone);
    if (!formatted) return `<span class="text-slate-400 font-mono text-xs">${phone || '-'}</span>`;
    
    const msg = `Yth. Bapak/Ibu *${nama}*,\n\nKami menginformasikan bahwa terdapat tagihan angsuran kredit pada rekening *${norek}* dengan total tunggakan sebesar *Rp ${fmt(totung)}*.\n\nMohon untuk segera melakukan pembayaran angsuran.\n\n_(Jika Bapak/Ibu sudah melakukan pembayaran, mohon abaikan pesan ini)_\n\nTerima kasih.`;
    const waUrl = `https://wa.me/${formatted}?text=${encodeURIComponent(msg)}`;
    
    return `
        <a href="${waUrl}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 hover:bg-emerald-500 hover:text-white text-emerald-600 rounded-lg border border-emerald-200 transition font-bold text-xs" title="Kirim Pesan WhatsApp">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.319-.883-.665-1.479-1.488-1.653-1.787-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
            WA (${formatted.substring(0,5)}...)
        </a>
    `;
}

function openDetail(f,t){ 
    modalState={from:f,to:t,page:1,limit:50}; 
    document.getElementById('modalDetail').classList.remove('hidden'); 
    
    let badgeText = `${f} ➔ ${t}`;
    if (f === 'REALISASI') badgeText = `NEW REALISASI`;
    else if (t === 'O') badgeText = `LUNAS / RUN OFF (${f})`;
    
    document.getElementById('badgeMigrasi').innerText = badgeText; 
    
    loadDetail(); 
}

async function loadDetail(){
    const l=document.getElementById('loadingDetail'); const tb=document.getElementById('bodyDetail'); 
    l.classList.remove('hidden'); tb.innerHTML='';
    try{
        const kankasModal = document.getElementById('opt_kankas_modal').value;

        const pl={
            type:'detail_migrasi_bucket',
            closing_date:document.getElementById('closing_date').value,
            harian_date:document.getElementById('harian_date').value,
            kode_kantor:document.getElementById('opt_kantor').value||null,
            kode_kankas:kankasModal,
            from_bucket:modalState.from,
            to_bucket:modalState.to,
            page:modalState.page,
            limit:modalState.limit
        };
        const r=await apiCall(API_ENDPOINT,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(pl)}); 
        const j=await r.json(); 
        const d=j.data.data||[]; 
        const m=j.data.pagination;
        
        if(d.length===0){
            tb.innerHTML='<tr><td colspan="13" class="py-20 text-center text-slate-400 italic text-base">Tidak ada data detail.</td></tr>'; 
            document.getElementById('pageInfo').innerText='0 Data'; 
            return;
        }
        
        let h=''; 
        d.forEach(x=>{
            const btnWa = createWABtn(x.no_hp, x.nama_nasabah, x.no_rekening, x.totung);

            let statTabungan = `<span class="text-red-500 font-bold text-xs">Belum Aman</span>`;
            if(x.status_tabungan === 'Aman') statTabungan = `<span class="text-green-600 font-bold text-xs">Aman</span>`;

            let bgStat = 'bg-blue-50 text-blue-700 border-blue-200';
            if(x.status_migrasi === 'Lunas') bgStat = 'bg-slate-100 text-slate-600 border-slate-300';
            else if(x.status_migrasi === 'New') bgStat = 'bg-emerald-50 text-emerald-700 border-emerald-200';

            // 🔥 Logika JS untuk memotong alamat tepat 25 karakter
            let alamatLengkap = x.alamat || '-';
            let alamatPendek = alamatLengkap.length > 25 ? alamatLengkap.substring(0, 25) + '...' : alamatLengkap;

            h+=`<tr class="hover:bg-slate-50/50 border-b border-slate-100 transition h-[48px] group">
                  <td class="px-3 py-2 font-mono text-sm text-slate-600 sticky left-0 bg-white group-hover:bg-slate-50 z-20 shadow-[1px_0_0_#f1f5f9] border-r border-slate-100">${x.no_rekening}</td>
                  <td class="px-4 py-2 font-bold text-sm text-slate-700 truncate sticky left-[140px] bg-white group-hover:bg-slate-50 z-20 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.1)] border-r border-slate-100" title="${x.nama_nasabah}">${x.nama_nasabah}</td>
                  <td class="px-4 py-2 text-slate-500 text-sm border-r border-slate-100 whitespace-nowrap" title="${alamatLengkap}">${alamatPendek}</td>
                  <td class="px-3 py-2 text-center border-r border-slate-100">${btnWa}</td>
                  <td class="px-3 py-2 text-center font-mono text-xs md:text-sm text-slate-500 border-r border-slate-100">${x.kankas||'-'}</td>
                  <td class="px-4 py-2 text-right font-medium text-sm text-slate-500 border-r border-slate-100">${fmt(x.os_m1)}</td>
                  <td class="px-4 py-2 text-right font-mono font-bold text-sm text-green-700 bg-green-50/30 border-r border-green-100">${fmt(x.baki_debet)}</td>
                  <td class="px-3 py-2 text-center border-r border-slate-100"><span class="${bgStat} px-2.5 py-1 rounded-lg text-[10px] font-bold border uppercase tracking-wider">${x.status_migrasi}</span></td>
                  <td class="px-3 py-2 text-center font-bold text-sm text-slate-600 border-r border-slate-100">${x.kolektibilitas||'-'}</td>
                  <td class="px-4 py-2 text-right font-mono font-bold text-sm text-red-600 bg-red-50/30 border-r border-red-100">${fmt(x.tunggakan_pokok)}</td>
                  <td class="px-4 py-2 text-right font-mono font-bold text-sm text-orange-600 bg-orange-50/30 border-r border-orange-100">${fmt(x.tunggakan_bunga)}</td>
                  <td class="px-4 py-2 text-right font-mono font-bold text-sm text-emerald-600 bg-emerald-50/10 border-r border-slate-100">${fmt(x.tabungan)}</td>
                  <td class="px-3 py-2 text-center">${statTabungan}</td>
                </tr>`;
        }); 
        tb.innerHTML=h; 
        document.getElementById('pageInfo').innerText=`Hal ${modalState.page} dari ${m.total_pages} (${fmt(m.total_records)} Data)`;
        
        const p=document.getElementById('btnPrev'); const n=document.getElementById('btnNext');
        p.onclick=()=>{modalState.page--;loadDetail()}; n.onclick=()=>{modalState.page++;loadDetail()};
        p.disabled=modalState.page<=1; n.disabled=modalState.page>=m.total_pages;
    } catch(e){console.error(e);} 
    finally{l.classList.add('hidden');}
}

/* EXPORT EXCEL DETAIL MIGRASI */
async function exportExcelDetailMigrasi() {
    const btn = event.target.closest('button'); const txt = btn.innerHTML;
    btn.innerHTML = `<span class="animate-spin inline-block h-4 w-4 border-2 border-white border-t-transparent rounded-full mr-2"></span>...`;
    btn.disabled = true;

    try {
        const kankasModal = document.getElementById('opt_kankas_modal').value;
        const pl={
            type:'detail_migrasi_bucket',
            closing_date:document.getElementById('closing_date').value,
            harian_date:document.getElementById('harian_date').value,
            kode_kantor:document.getElementById('opt_kantor').value||null,
            kode_kankas:kankasModal,
            from_bucket:modalState.from,
            to_bucket:modalState.to,
            page: 1,
            limit: 10000 // Tarik semua untuk excel
        };
        const r=await apiCall(API_ENDPOINT,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(pl)}); 
        const j=await r.json(); 
        const rows=j.data.data||[]; 
        
        if(rows.length === 0) { alert("Tidak ada data detail untuk diexport"); return; }

        let csv = `No Rekening\tNama Nasabah\tAlamat\tNo HP\tKankas\tOS M-1\tOS Current\tStatus Migrasi\tKol\tTunggakan Pokok\tTunggakan Bunga\tTotal Tunggakan\tTabungan\tStatus Tabungan\n`;
        rows.forEach(x => {
            csv += `'${x.no_rekening}\t${x.nama_nasabah}\t${x.alamat||''}\t'${x.no_hp||''}\t${x.kankas||''}\t${Math.round(x.os_m1)}\t${Math.round(x.baki_debet)}\t${x.status_migrasi}\t${x.kolektibilitas||''}\t${Math.round(x.tunggakan_pokok)}\t${Math.round(x.tunggakan_bunga)}\t${Math.round(x.totung)}\t${Math.round(x.tabungan)}\t${x.status_tabungan}\n`;
        });

        const blob = new Blob([csv], { type: 'application/vnd.ms-excel' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Detail_Migrasi_${modalState.from}_to_${modalState.to}.xls`;
        document.body.appendChild(a); a.click(); document.body.removeChild(a);

    } catch(e) { console.error(e); alert("Gagal export data."); } 
    finally { btn.innerHTML = txt; btn.disabled = false; }
}

function changePageDetail(step) {
    modalState.page += step;
    loadDetail();
}

function closeModal(){document.getElementById('modalDetail').classList.add('hidden');}
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeModal();});
</script>