<style>
  /* Custom Scrollbar untuk Table Wrapper */
  .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
  .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

  /* Animasi Modal */
  @keyframes scaleUp { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
  .animate-scale-up { animation: scaleUp 0.2s ease-out forwards; }

  /* CSS MAGIC STICKY (MEMBEKUKAN KOLOM & HEADER) */
  #tabelMigrasiSC thead th { position: sticky; box-shadow: inset 0 -1px 0 #cbd5e1; }
  
  /* Lapis 1 (Header Utama) */
  #tabelMigrasiSC thead tr:nth-child(1) th { top: 0; z-index: 40; height: 38px; background-color: #f8fafc;}
  #tabelMigrasiSC thead tr:nth-child(1) th.sticky-left { z-index: 50; left: 0; box-shadow: inset -1px -1px 0 #cbd5e1; background-color: #f1f5f9; }
  
  /* Lapis 2 (NOA, OS) */
  #tabelMigrasiSC thead tr:nth-child(2) th { top: 38px; z-index: 39; height: 32px; background-color: #f8fafc;}
  
  /* Lapis 3 (Grand Total - Beda Warna & Freeze) */
  #tabelMigrasiSC thead tr:nth-child(3) th { top: 70px; z-index: 38; height: 42px; box-shadow: inset 0 -2px 0 #93c5fd; background-color: #dbeafe !important; }
  #tabelMigrasiSC thead tr:nth-child(3) th.sticky-left { z-index: 48; left: 0; box-shadow: inset -1px -2px 0 #93c5fd; background-color: #bfdbfe !important; }

  /* Freeze Kiri Body */
  .sticky-left { position: sticky; left: 0; }
  #bodyMatrix td.sticky-left { z-index: 20; background-color: #ffffff; box-shadow: inset -1px 0 0 #e2e8f0; }
  #bodyMatrix tr:hover td.sticky-left { background-color: #f8fafc !important; }
  #bodyMatrix tr:hover td { background-color: #f8fafc; }
</style>

<div class="max-w-[1920px] mx-auto px-3 md:px-4 py-4 md:py-5 h-[calc(100vh-80px)] flex flex-col bg-slate-50 font-sans text-slate-800 overflow-hidden">
  
  <div class="flex-none mb-3">
    <h1 class="text-xl md:text-2xl font-bold text-slate-800 flex items-center gap-2">
        <span class="p-1.5 md:p-2 bg-blue-600 rounded-lg text-white shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
        </span>
        Monitoring Migrasi SC
    </h1>
  </div>

  <div class="flex-none mb-3 bg-white p-3 md:p-3 rounded-xl border border-slate-200 shadow-sm flex flex-col xl:flex-row xl:items-center gap-3 xl:gap-5 w-full">
      
      <form id="formFilterMigrasi" class="flex flex-wrap items-end gap-2 md:gap-3 w-full xl:w-auto shrink-0" onsubmit="event.preventDefault(); fetchMatrix();">
          
          <div class="flex flex-col w-[calc(50%-4px)] md:w-[130px]">
              <label class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1 truncate">CLOSING (M-1)</label>
              <input type="date" id="closing_date" class="border border-slate-200 rounded-md px-2 py-1.5 text-xs text-slate-700 outline-none focus:border-blue-500 transition w-full" required onclick="try{this.showPicker()}catch(e){}">
          </div>
          
          <div class="flex flex-col w-[calc(50%-4px)] md:w-[130px]">
              <label class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1 truncate">ACTUAL (HARIAN)</label>
              <input type="date" id="harian_date" class="border border-slate-200 rounded-md px-2 py-1.5 text-xs text-slate-700 outline-none focus:border-blue-500 transition w-full" required onclick="try{this.showPicker()}catch(e){}">
          </div>
          
          <div class="flex flex-col flex-1 min-w-[130px] md:w-[180px]">
              <label class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1 truncate">CABANG</label>
              <select id="opt_kantor" class="border border-slate-200 rounded-md px-2 py-1.5 text-xs text-slate-700 outline-none focus:border-blue-500 truncate w-full cursor-pointer" onchange="fetchMatrix()">
                  <option>Loading...</option>
              </select>
          </div>
          
          <div class="flex items-center gap-1.5 shrink-0 h-[30px] mb-[1px]">
              <button type="submit" class="h-full px-3 md:px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-md flex items-center justify-center transition shadow-sm" title="Cari Data">
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" class="md:mr-1.5"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                  <span class="hidden md:inline font-bold text-[10px] uppercase tracking-wider">CARI</span>
              </button>
              <button type="button" onclick="exportExcelRekapMigrasi()" class="h-full px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md flex items-center justify-center transition shadow-sm" title="Download Excel">
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                  <span class="hidden md:inline font-bold text-[10px] uppercase tracking-wider ml-1.5">EXCEL</span>
              </button>
          </div>
      </form>

      <div class="w-full h-px bg-slate-200 xl:w-px xl:h-8 block"></div>

      <div id="summaryCheck" class="hidden flex flex-wrap items-center gap-4 xl:gap-6 shrink-0 w-full xl:w-auto">
          
          <div class="flex flex-col">
              <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">FLOW SC (0-30) ➔ FE/BE</span>
              <div class="flex items-center gap-1.5">
                  <span class="text-[10px] font-bold bg-red-100 text-red-600 px-1.5 py-0.5 rounded" id="flowBadPct">0.00%</span>
                  <span class="text-xs font-bold text-red-600 font-mono tracking-tight" id="flowBadVal">0</span>
              </div>
          </div>

          <div class="w-px h-6 bg-slate-200"></div>

          <div class="flex flex-col">
              <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">FLOW FE/BE ➔ (0-30)</span>
              <div class="flex items-center gap-1.5">
                  <span class="text-[10px] font-bold bg-green-100 text-green-700 px-1.5 py-0.5 rounded" id="flowGoodPct">0.00%</span>
                  <span class="text-xs font-bold text-green-700 font-mono tracking-tight" id="flowGoodVal">0</span>
              </div>
          </div>

          <div class="hidden sm:block w-px h-6 bg-slate-200"></div>
          <div class="hidden sm:flex flex-col">
              <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">GROWTH (ACTUAL - M1)</span>
              <div class="flex items-center gap-1.5" id="growthContainer"></div>
          </div>

      </div>
  </div>

  <div class="flex-1 overflow-hidden bg-white rounded-xl shadow-sm border border-slate-200 relative">
    <div id="loadingMatrix" class="hidden absolute inset-0 bg-white/80 z-[100] flex flex-col items-center justify-center text-blue-600 backdrop-blur-sm">
        <div class="animate-spin rounded-full h-8 w-8 border-4 border-blue-500 border-t-transparent mb-2"></div>
        <span class="text-xs font-bold uppercase tracking-widest">Menyiapkan Matriks...</span>
    </div>
    
    <div class="h-full overflow-auto custom-scrollbar relative">
      <table class="w-max min-w-full text-xs text-center border-separate border-spacing-0 text-slate-600" id="tabelMigrasiSC">
        <thead class="text-slate-800 font-bold tracking-wider">
          <tr>
            <th rowspan="2" class="sticky-left px-3 text-left w-[100px] md:w-[130px] uppercase align-middle bg-slate-50 border-r border-slate-200">DPD M-1</th>
            <th colspan="2" class="px-2 uppercase border-r border-slate-200 align-middle bg-slate-50">Posisi M-1</th>
            <th rowspan="2" class="px-2 border-r border-green-200 w-[75px] md:w-[90px] text-green-700 bg-[#f0fdf4] align-middle">0</th>
            <th rowspan="2" class="px-2 border-r border-yellow-200 w-[75px] md:w-[90px] text-yellow-700 bg-[#fefce8] align-middle">1 - 7</th>
            <th rowspan="2" class="px-2 border-r border-yellow-200 w-[75px] md:w-[90px] text-yellow-700 bg-[#fefce8] align-middle">8 - 14</th>
            <th rowspan="2" class="px-2 border-r border-yellow-200 w-[75px] md:w-[90px] text-yellow-800 bg-[#fef9c3] align-middle">15 - 21</th>
            <th rowspan="2" class="px-2 border-r border-orange-200 w-[75px] md:w-[90px] text-orange-700 bg-[#fff7ed] align-middle">22 - 30</th>
            <th rowspan="2" class="px-2 border-r border-red-200 w-[75px] md:w-[90px] text-red-700 bg-[#fef2f2] align-middle">FE (31-90)</th>
            <th rowspan="2" class="px-2 border-r border-red-200 w-[75px] md:w-[90px] text-red-800 bg-[#fee2e2] align-middle">BE (>90)</th>
            <th rowspan="2" class="px-2 border-r border-slate-200 w-[75px] md:w-[90px] uppercase align-middle bg-slate-50">ANGSURAN</th>
            <th rowspan="2" class="px-2 border-r border-slate-200 w-[75px] md:w-[90px] uppercase align-middle bg-slate-50">PELUNASAN</th>
            <th rowspan="2" class="px-2 w-[80px] md:w-[100px] uppercase align-middle bg-slate-100">TOT RUN OFF</th>
          </tr>
          <tr class="text-[9px] md:text-[10px]">
            <th class="px-1 border-r border-slate-200 w-[40px] md:w-[50px] uppercase align-middle bg-slate-50">NOA</th>
            <th class="px-1 border-r border-slate-200 w-[85px] md:w-[100px] uppercase align-middle bg-slate-50">OS</th>
          </tr>
          <tr id="rowTotalMigrasiAtas"></tr>
        </thead>
        <tbody id="bodyMatrix" class="divide-y divide-slate-100 bg-white"></tbody>
      </table>
    </div>
  </div>
</div>

<div id="modalDetail" class="fixed inset-0 hidden z-[9999] flex items-end md:items-center justify-center sm:p-4">
  <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
  <div class="relative bg-white w-full h-[92vh] max-w-[1600px] rounded-t-xl md:rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-scale-up">
    
    <div class="flex justify-between items-center px-4 py-3 md:px-5 border-b bg-slate-50 shrink-0 flex-wrap gap-2">
        <div class="flex-1 min-w-[200px]">
            <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm md:text-base">
                <span class="bg-blue-100 text-blue-600 p-1 md:p-1.5 rounded-lg shadow-sm text-xs">📄</span> 
                Detail Nasabah 
                <span id="badgeMigrasi" class="text-[9px] md:text-[10px] bg-blue-600 text-white px-2 py-0.5 rounded-full shadow-sm ml-1 font-mono">...</span>
            </h3>
        </div>
        
        <div class="flex items-center gap-2 ml-auto shrink-0 w-full sm:w-auto mt-2 sm:mt-0">
            <select id="opt_kankas_modal" class="border border-blue-200 rounded-lg px-2 h-[30px] md:h-8 flex-1 sm:w-[130px] text-[10px] font-bold text-blue-800 bg-blue-50 outline-none focus:ring-1 focus:ring-blue-400" onchange="loadDetail()">
                <option value="">Semua Kankas</option>
            </select>

            <button onclick="exportExcelDetailMigrasi()" class="h-[30px] md:h-8 px-3 border-none bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition shadow-sm flex items-center justify-center font-bold text-[9px] md:text-[10px] uppercase tracking-wider">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span class="hidden sm:inline ml-1">Excel</span>
            </button>
            <button onclick="closeModal()" class="w-[30px] md:w-8 h-[30px] md:h-8 flex items-center justify-center rounded-lg bg-slate-200 hover:bg-red-500 hover:text-white text-slate-600 transition font-bold text-lg leading-none">&times;</button>
        </div>
    </div>

    <div class="flex-1 overflow-auto bg-slate-50 relative custom-scrollbar p-0 md:p-2">
        <div id="loadingDetail" class="hidden absolute inset-0 bg-white/90 z-40 flex flex-col items-center justify-center text-blue-600 backdrop-blur-sm">
            <div class="animate-spin rounded-full h-8 w-8 border-4 border-blue-500 border-t-transparent mb-2"></div>
            <span class="text-xs font-bold uppercase tracking-widest">Memuat Detail...</span>
        </div>
        
        <table class="w-max min-w-full text-xs text-left text-slate-700 border border-slate-200 md:rounded-lg shadow-sm bg-white table-fixed" id="tableExportMigrasi">
            <thead id="headModalRR" class="bg-slate-100 text-slate-600 font-bold sticky top-0 z-30 shadow-sm text-[9px] md:text-[10px] uppercase tracking-wider">
                </thead>
            <tbody id="bodyDetail" class="divide-y divide-slate-100 bg-white"></tbody>
        </table>
    </div>

    <div class="px-4 py-2 border-t bg-white flex justify-between items-center shrink-0">
        <span id="pageInfo" class="text-[9px] md:text-[10px] font-bold text-slate-500">0 Data</span>
        <div class="flex gap-2">
            <button id="btnPrev" onclick="changePageDetail(-1)" class="px-3 py-1 bg-white border border-slate-300 rounded text-[10px] font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-50 transition shadow-sm">« Prev</button>
            <button id="btnNext" onclick="changePageDetail(1)" class="px-3 py-1 bg-white border border-slate-300 rounded text-[10px] font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-50 transition shadow-sm">Next »</button>
        </div>
    </div>
  </div>
</div>

<style>
  /* Freeze Modal Columns */
  #bodyDetail tr:hover td { background-color: #f8fafc; }
  #bodyDetail tr:hover td.sticky { background-color: #f8fafc !important; }
</style>

<script>
// --- KONFIGURASI ---
const API_ENDPOINT = './api/bucket_fe/'; 
const API_KODE = './api/kode/';
const API_DATE = './api/date/'; 
const fmt = n => new Intl.NumberFormat('id-ID').format(Number(n||0));
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
    tb.innerHTML = `<tr><td colspan="13" class="py-16 text-center text-slate-400 italic">...</td></tr>`;
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
        tb.innerHTML = `<tr><td colspan="13" class="py-12 text-center text-red-500 font-bold">Gagal memuat matriks</td></tr>`;
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

    // Pemburukan
    goodBuckets.forEach(src => { badBuckets.forEach(tgt => { if(data.matrix[src] && data.matrix[src][tgt]) flowBadOS += parseFloat(data.matrix[src][tgt].os || 0); }); });
    // Perbaikan
    badBuckets.forEach(src => { goodBuckets.forEach(tgt => { if(data.matrix[src] && data.matrix[src][tgt]) flowGoodOS += parseFloat(data.matrix[src][tgt].os || 0); }); });

    const flowBadPct = totalGoodM1 > 0 ? (flowBadOS / totalGoodM1) * 100 : 0;
    const flowGoodPct = totalBadM1 > 0 ? (flowGoodOS / totalBadM1) * 100 : 0;

    // --- RENDER HEADER SUMMARY ---
    document.getElementById('summaryCheck').classList.remove('hidden');

    const isUp = diffVal >= 0;
    const colorGrowth = isUp ? 'text-green-700' : 'text-red-700';
    const bgGrowth = isUp ? 'bg-green-100' : 'bg-red-100';
    
    document.getElementById('growthContainer').innerHTML = `
        <span class="text-[10px] font-bold ${bgGrowth} ${colorGrowth} px-1.5 py-0.5 rounded">${Math.abs(diffPct).toFixed(2)}%</span>
        <span class="text-[11px] font-bold font-mono ${colorGrowth} tracking-tight">${fmt(diffVal)}</span>
    `;

    document.getElementById('flowBadPct').innerText = flowBadPct.toFixed(2) + '%';
    document.getElementById('flowBadVal').innerText = fmt(flowBadOS);

    document.getElementById('flowGoodPct').innerText = flowGoodPct.toFixed(2) + '%';
    document.getElementById('flowGoodVal').innerText = fmt(flowGoodOS);

    // --- RENDER TOTAL STICKY (WARNA BIRU SOFT & FULL BLOK KIRI) ---
    let tf = `<th class="sticky-left px-3 text-left uppercase tracking-widest align-middle text-blue-900">TOTAL</th>
              <th class="border-r border-blue-300 px-2 text-right font-mono align-middle text-blue-900">${fmt(GT.m1.noa)}</th>
              <th class="border-r border-blue-300 px-2 text-right font-mono text-[10px] md:text-[11px] text-blue-900 align-middle">${fmt(GT.m1.os)}</th>`;
    
    BUCKETS.forEach(b => { 
        tf += `<th class="border-r border-blue-300 p-0 align-middle"><div class="flex flex-col justify-center h-full py-1.5"><span class="text-[8px] md:text-[9px] text-blue-700 font-mono">${fmt(GT.buckets[b].noa)}</span><span class="text-[9px] md:text-[10px] text-blue-900 font-bold font-mono">${fmt(GT.buckets[b].os)}</span></div></th>` 
    });
    
    tf += `<th class="border-r border-blue-300 text-right px-2 font-mono font-bold text-emerald-700 align-middle">${fmt(GT.angsuran)}</th>
           <th class="border-r border-blue-300 p-0 align-middle"><div class="flex flex-col justify-center h-full py-1.5"><span class="text-[8px] md:text-[9px] text-blue-700 font-mono">${fmt(GT.lunas.noa)}</span><span class="text-[9px] md:text-[10px] text-blue-900 font-bold font-mono">${fmt(GT.lunas.os)}</span></div></th>
           <th class="border-blue-300 text-right px-2 font-mono font-bold text-red-600 align-middle">${fmt(GT.runoff_total.os)}</th>`;
    
    document.getElementById('rowTotalMigrasiAtas').innerHTML = tf;

    // --- RENDER TABEL BODY ---
    const tb = document.getElementById('bodyMatrix'); 
    let h = '';
    
    // Baris Realisasi
    h += `<tr class="bg-emerald-50/40 hover:bg-emerald-100 transition border-b border-emerald-100 group">
            <td class="sticky-left px-3 py-2 text-left font-bold text-emerald-800 bg-emerald-50 group-hover:bg-emerald-100 border-r border-emerald-200 text-[10px] md:text-[11px] align-middle shadow-[inset_-1px_0_0_#a7f3d0]">REALISASI BARU</td>
            <td class="border-r border-emerald-100 text-slate-400 align-middle">-</td><td class="border-r border-emerald-100 text-slate-400 align-middle">-</td>
            <td class="border-r border-emerald-100 p-0 align-middle"><div class="cursor-pointer py-1.5 transition" onclick="openDetail('REALISASI','0')"><div class="font-bold text-blue-600 text-[9px] font-mono">${fmt(real.noa)}</div><div class="font-bold text-emerald-800 text-[10px] md:text-[11px] font-mono">${fmt(real.os)}</div><div class="text-[8px] font-bold text-emerald-600 mt-0.5">100%</div></div></td>
            <td colspan="6" class="text-[9px] italic text-slate-400 border-r border-emerald-100 align-middle">Detail Tersebar</td>
            <td class="border-r border-emerald-100 text-slate-400 align-middle">-</td><td class="border-r border-emerald-100 text-slate-400 align-middle">-</td><td class="text-slate-400 align-middle">-</td>
          </tr>`;

    // Loop Matrix Bucket
    BUCKETS.forEach((f, i) => {
        const m1 = data.summary_m1[f];
        h += `<tr class="hover:bg-slate-50 transition border-b border-slate-200 h-[44px] group">
                <td class="sticky-left px-3 text-left font-bold text-slate-700 bg-white group-hover:bg-slate-50 border-r border-slate-200 text-[10px] md:text-[11px] align-middle">${f}</td>
                <td class="border-r border-slate-200 text-right px-2 font-mono text-[9px] font-bold text-slate-400 align-middle">${fmt(m1.noa_m1)}</td>
                <td class="border-r border-slate-200 text-right px-2 font-mono font-bold text-slate-800 text-[10px] align-middle">${fmt(m1.os_m1)}</td>`;
        
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

            h += `<td class="border-r border-slate-200 p-0 align-middle ${bgClass}"><div class="h-full flex flex-col justify-center py-1 ${cursor} transition" ${clickEv}>
                    <span class="text-[8px] font-bold text-slate-400 font-mono">${fmt(c.noa)}</span>
                    <span class="text-[9px] md:text-[10px] font-bold font-mono ${textClass}">${fmt(c.os)}</span>
                    ${c.os > 0 ? `<span class="text-[8px] font-bold ${textClass} opacity-70">${pctVal}</span>` : ''}
                </div></td>`;
        });
        
        const l = data.matrix[f]['O'];
        h += `<td class="border-r border-slate-200 align-middle"><div class="font-mono font-bold text-green-600 text-[10px]">${fmt(ar)}</div></td>
              <td class="border-r border-slate-200 p-0 align-middle"><div class="h-full flex flex-col justify-center cursor-pointer hover:bg-blue-50 transition py-1" onclick="openDetail('${f}','O')"><span class="text-[8px] font-bold text-blue-500 font-mono">${fmt(l.noa)}</span><span class="text-[9px] md:text-[10px] font-bold text-slate-700 font-mono">${fmt(l.pelunasan)}</span></div></td>
              <td class="font-mono font-bold text-red-600 text-[10px] align-middle">${fmt(ar + l.pelunasan)}</td></tr>`;
    });
    tb.innerHTML = h;
}

/* EXPORT EXCEL REKAP UTAMA MIGRASI */
window.exportExcelRekapMigrasi = function() {
    if(!rekapDataCache || !rekapDataCache.matrix) return alert("Tidak ada data rekap untuk didownload.");

    let csv = "DPD M-1\tNOA M-1\tOS M-1\tNOA 0\tOS 0\tNOA 1-7\tOS 1-7\tNOA 8-14\tOS 8-14\tNOA 15-21\tOS 15-21\tNOA 22-30\tOS 22-30\tNOA FE\tOS FE\tNOA BE\tOS BE\tAngsuran\tNOA Lunas\tOS Lunas\tTotal Run Off\n";
    
    // Baris Realisasi Baru
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

// --- MODAL DETAIL (FREEZE KIRI & TEKS HP BIASA) ---
function openDetail(f,t){ 
    modalState={from:f,to:t,page:1,limit:50}; 
    document.getElementById('modalDetail').classList.remove('hidden'); 
    
    let badgeText = `${f} ➔ ${t}`;
    if (f === 'REALISASI') badgeText = `NEW REALISASI`;
    else if (t === 'O') badgeText = `LUNAS / RUN OFF (${f})`;
    
    document.getElementById('badgeMigrasi').innerText = badgeText; 
    
    document.getElementById('headModalRR').innerHTML = `
        <tr>
            <th class="px-2 py-2.5 border-b border-r border-slate-200 w-[100px] sticky left-0 bg-slate-100 z-40 shadow-[1px_0_0_#cbd5e1]">Rekening</th>
            <th class="px-3 py-2.5 border-b border-r border-slate-200 w-[180px] md:w-[220px] sticky left-[100px] bg-slate-100 z-40 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.1)]">Nama Nasabah</th>
            <th class="px-3 py-2.5 border-b border-r border-slate-200 w-[150px] md:w-[200px]">Alamat</th>
            <th class="px-3 py-2.5 border-b border-r border-slate-200 w-[90px] text-center">No HP</th>
            <th class="px-2 py-2.5 border-b border-r border-slate-200 w-[80px] text-center">Kankas</th>
            <th class="px-3 py-2.5 border-b border-r border-slate-200 w-[100px] text-right">OS M-1</th>
            <th class="px-3 py-2.5 border-b border-r border-green-200 w-[100px] text-right bg-green-50 text-green-700">OS Current</th>
            <th class="px-2 py-2.5 border-b border-r border-slate-200 w-[80px] text-center">Status</th>
            <th class="px-2 py-2.5 border-b border-r border-slate-200 w-[40px] text-center">Kol</th>
            <th class="px-3 py-2.5 border-b border-r border-red-200 w-[90px] text-right bg-red-50 text-red-700">Tgk Pokok</th>
            <th class="px-3 py-2.5 border-b border-r border-orange-200 w-[90px] text-right bg-orange-50 text-orange-700">Tgk Bunga</th>
            <th class="px-3 py-2.5 border-b border-r border-slate-200 w-[100px] text-right">Tabungan</th>
            <th class="px-2 py-2.5 border-b border-slate-200 w-[70px] text-center">Stat Tab</th>
        </tr>`;

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
            tb.innerHTML='<tr><td colspan="13" class="p-12 text-center text-slate-400 italic">Tidak ada data.</td></tr>'; 
            document.getElementById('pageInfo').innerText='0 Data'; 
            return;
        }
        
        let h=''; 
        d.forEach(x=>{
            const textHp = x.no_hp ? `<span class="font-mono text-slate-600">${x.no_hp}</span>` : `<span class="text-slate-400">-</span>`;

            let statTabungan = `<span class="text-red-500 font-bold text-[9px]">Belum Aman</span>`;
            if(x.status_tabungan === 'Aman') statTabungan = `<span class="text-green-600 font-bold text-[9px]">Aman</span>`;

            let bgStat = 'bg-blue-50 text-blue-700 border-blue-200';
            if(x.status_migrasi === 'Lunas') bgStat = 'bg-slate-100 text-slate-600 border-slate-300';
            else if(x.status_migrasi === 'New') bgStat = 'bg-emerald-50 text-emerald-700 border-emerald-200';

            // Freeze kolom No Rekening & Nama
            h+=`<tr class="hover:bg-slate-50/50 border-b border-slate-100 transition h-[36px] group">
                  <td class="px-2 py-1.5 font-mono text-[10px] text-slate-500 sticky left-0 bg-white group-hover:bg-slate-50 z-20 shadow-[1px_0_0_#f1f5f9] border-r border-slate-100">${x.no_rekening}</td>
                  <td class="px-3 py-1.5 font-semibold text-[10px] text-slate-700 truncate sticky left-[100px] bg-white group-hover:bg-slate-50 z-20 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.1)] border-r border-slate-100" title="${x.nama_nasabah}">${x.nama_nasabah}</td>
                  <td class="px-3 py-1.5 text-slate-500 truncate max-w-[150px] md:max-w-[200px] text-[9px] border-r border-slate-100" title="${x.alamat||''}">${x.alamat||'-'}</td>
                  <td class="px-3 py-1.5 text-center border-r border-slate-100 text-[10px]">${textHp}</td>
                  <td class="px-2 py-1.5 text-center font-mono text-[9px] text-slate-500 border-r border-slate-100">${x.kankas||'-'}</td>
                  <td class="px-3 py-1.5 text-right font-mono text-[10px] text-slate-400 border-r border-slate-100">${fmt(x.os_m1)}</td>
                  <td class="px-3 py-1.5 text-right font-mono font-bold text-[10px] text-green-700 bg-green-50/30 border-r border-green-100">${fmt(x.baki_debet)}</td>
                  <td class="px-2 py-1.5 text-center border-r border-slate-100"><span class="${bgStat} px-1.5 py-0.5 rounded text-[8px] font-bold border">${x.status_migrasi}</span></td>
                  <td class="px-2 py-1.5 text-center font-bold text-[10px] text-slate-600 border-r border-slate-100">${x.kolektibilitas||'-'}</td>
                  <td class="px-3 py-1.5 text-right font-mono font-bold text-[10px] text-red-600 bg-red-50/30 border-r border-red-100">${fmt(x.tunggakan_pokok)}</td>
                  <td class="px-3 py-1.5 text-right font-mono font-bold text-[10px] text-orange-600 bg-orange-50/30 border-r border-orange-100">${fmt(x.tunggakan_bunga)}</td>
                  <td class="px-3 py-1.5 text-right font-mono font-bold text-[10px] text-emerald-600 bg-emerald-50/10 border-r border-slate-100">${fmt(x.tabungan)}</td>
                  <td class="px-2 py-1.5 text-center">${statTabungan}</td>
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
    btn.innerHTML = `<span class="animate-spin inline-block h-3 w-3 border-2 border-white border-t-transparent rounded-full mr-1"></span>...`;
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