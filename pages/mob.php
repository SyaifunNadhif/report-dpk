<style>
  /* Custom Scrollbar */
  .custom-scrollbar::-webkit-scrollbar { height: 8px; width: 8px; }
  .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
  
  /* Animasi Modal */
  @keyframes scaleUp { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
  .animate-scale-up { animation: scaleUp 0.2s ease-out forwards; }

  /* Sembunyikan Scrollbar Bawaan untuk Filter di Mobile */
  .no-scrollbar::-webkit-scrollbar { display: none; }
  .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

  /* Hover efek sel Matrix MOB */
  .cell-hover:hover { background-color: #e0f2fe !important; cursor: pointer; transform: scale(1.02); transition: 0.1s; z-index: 5; position: relative; box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: 1px solid #3b82f6; }

  /* CSS MAGIC STICKY TABLE (Disesuaikan untuk Font Besar) */
  #tabelMob thead th { position: sticky; box-shadow: inset 0 -1px 0 #cbd5e1; }
  
  /* Lapis 1 (Header Utama) */
  #tabelMob thead tr:nth-child(1) th { top: 0; z-index: 40; height: 46px; background-color: #f8fafc; }
  
  /* Lapis 2 (NOA, OS) */
  #tabelMob thead tr:nth-child(2) th { top: 46px; z-index: 39; height: 38px; background-color: #f8fafc; }
  
  /* Lapis 3 (Grand Total - Beda Warna & Terkunci di bawah header) */
  #tabelMob thead tr:nth-child(3) th { 
      top: 84px; z-index: 38; height: 52px; 
      box-shadow: inset 0 -2px 0 #93c5fd; 
      background-color: #eff6ff !important; 
  }

  /* Kolom Paling Kiri (Bulan Real) Freeze */
  .sticky-left { position: sticky; left: 0; }
  #tabelMob thead tr:nth-child(1) th.sticky-left { z-index: 50; box-shadow: inset -1px -1px 0 #cbd5e1; background-color: #f1f5f9; border-top-left-radius: 8px;}
  #tabelMob thead tr:nth-child(3) th.sticky-left { z-index: 48; box-shadow: inset -1px -2px 0 #93c5fd; background-color: #dbeafe !important; }
  #bodyMatrix td.sticky-left { z-index: 20; background-color: #ffffff; box-shadow: inset -1px 0 0 #e2e8f0; }
  #bodyMatrix tr:hover td.sticky-left { background-color: #f8fafc !important; }
  #bodyMatrix tr:hover td { background-color: #f8fafc; }

  /* CSS form filter */
  .inp { border:1px solid #cbd5e1; border-radius:6px; padding:0 12px; background:#fff; outline:none; transition: border 0.2s;}
  .inp:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,0.1); }
  .lbl { font-size:11px; color:#475569; font-weight:800; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.05em; display:block; white-space: nowrap;}
  .field { display:flex; flex-direction:column; }
  .btn-icon { display:inline-flex; align-items:center; justify-content:center; border:none; cursor:pointer; transition: transform 0.2s;}
  .btn-icon:hover { transform:translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }

  /* Hilangkan Date Icon Default */
  input[type="date"]::-webkit-inner-spin-button, input[type="date"]::-webkit-calendar-picker-indicator { display: none; -webkit-appearance: none; }
  input[type="date"] { -moz-appearance: textfield; }
  
  /* Freeze Kiri Modal Detail */
  #bodyModalDetail tr:hover td { background-color: #f8fafc; }
  #bodyModalDetail tr:hover td.sticky { background-color: #f8fafc !important; }
</style>

<div class="max-w-[1920px] mx-auto px-2 md:px-4 py-4 md:py-6 h-[calc(100vh-80px)] flex flex-col bg-slate-50 font-sans text-slate-800 overflow-hidden">
  
  <div class="flex-none mb-4 flex flex-col xl:flex-row justify-between items-start gap-4 w-full">
      
      <div class="flex flex-col gap-1.5 shrink-0 w-full xl:w-auto">
        <h1 class="text-xl md:text-2xl font-bold text-slate-800 flex items-center gap-2 mb-1">
            <span class="p-1.5 md:p-2 bg-blue-600 rounded-lg text-white shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </span>
            Analisa MOB Vintage
        </h1>
        <p class="text-sm text-slate-500 italic ml-10 md:ml-[44px]">*MOB = Migrasi Keadaan Kredit Baru (6 Bulan Terakhir)</p>
      </div>

      <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm overflow-x-auto no-scrollbar flex items-center w-full xl:w-auto shrink-0 xl:ml-auto">
          <form id="formFilterMob" class="flex flex-nowrap items-end gap-2 md:gap-3 shrink-0 min-w-max w-full sm:w-auto" onsubmit="event.preventDefault(); fetchRekapMob();">
              
              <div class="field shrink-0 w-[130px] md:w-[150px]">
                  <label class="lbl">POSISI DATA</label>
                  <input type="date" id="harian_date_mob" class="inp text-sm font-semibold h-[38px]" required onclick="try{this.showPicker()}catch(e){}">
              </div>
              
              <div class="field shrink-0 w-[160px] md:w-[200px]">
                  <label class="lbl">CABANG</label>
                  <select id="opt_kantor_mob" class="inp text-sm font-semibold h-[38px] truncate cursor-pointer" onchange="fetchRekapMob()">
                      <option>Loading...</option>
                  </select>
              </div>
              
              <div class="flex items-center gap-1.5 shrink-0 h-[38px] mb-px">
                  <button type="submit" class="btn-icon h-full px-5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-sm text-sm font-bold uppercase tracking-wider" title="Cari Data">
                      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" class="md:mr-1.5"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                      <span class="hidden md:inline">CARI</span>
                  </button>
                  <button type="button" onclick="exportExcelRekapMob()" class="btn-icon h-full w-[42px] bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-sm" title="Download Excel">
                      <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                  </button>
              </div>
          </form>
          
          <div class="text-xs text-slate-400 italic hidden sm:block ml-4 pr-2 whitespace-nowrap border-l border-slate-100 pl-4">
              *Klik persentase untuk detail.
          </div>
      </div>
  </div>

  <div class="flex-1 overflow-hidden bg-white rounded-xl shadow-sm border border-slate-200 relative">
    <div id="loadingMob" class="hidden absolute inset-0 bg-white/80 z-[100] flex flex-col items-center justify-center text-blue-600 backdrop-blur-sm">
        <div class="animate-spin rounded-full h-10 w-10 border-4 border-blue-500 border-t-transparent mb-3"></div>
        <span class="text-sm font-bold uppercase tracking-widest">Menyiapkan Matriks...</span>
    </div>
    
    <div class="h-full overflow-auto custom-scrollbar relative">
      <table class="w-max min-w-full text-center border-separate border-spacing-0 text-slate-700 table-fixed" id="tabelMob">
        <thead class="text-slate-800 font-bold tracking-wider text-xs md:text-sm">
          
          <tr>
            <th rowspan="2" class="sticky-left px-3 text-left w-[120px] md:w-[130px] uppercase align-middle bg-slate-50 border-r border-slate-200 text-blue-900">Bulan Real</th>
            <th rowspan="2" class="px-2 py-2 border-r border-blue-200 bg-blue-50 text-blue-800 align-middle">MOB</th>
            <th rowspan="2" class="px-4 py-2 border-r border-blue-200 bg-blue-50 text-blue-800 text-right w-[150px] md:w-[160px] align-middle">Tot Plafond</th>
            <th colspan="8" class="py-2 border-b border-slate-200 bg-slate-100 text-slate-700 tracking-wider uppercase">DPD (Days Past Due) / Ember</th>
          </tr>
          
          <tr class="text-[10px] md:text-[11px]">
            <th class="px-2 py-1.5 border-r border-slate-200 bg-[#f0fdf4] text-green-800 w-[90px] md:w-[110px]">0</th>
            <th class="px-2 py-1.5 border-r border-slate-200 bg-[#fefce8] text-yellow-800 w-[90px] md:w-[110px]">1 - 7</th>
            <th class="px-2 py-1.5 border-r border-slate-200 bg-[#fefce8] text-yellow-800 w-[90px] md:w-[110px]">8 - 14</th>
            <th class="px-2 py-1.5 border-r border-slate-200 bg-[#fef9c3] text-yellow-800 w-[90px] md:w-[110px]">15 - 21</th>
            <th class="px-2 py-1.5 border-r border-slate-200 bg-[#fff7ed] text-orange-800 w-[90px] md:w-[110px]">22 - 30</th>
            <th class="px-2 py-1.5 border-r border-slate-200 bg-[#ffedd5] text-orange-800 w-[90px] md:w-[110px]">31 - 60</th>
            <th class="px-2 py-1.5 border-r border-slate-200 bg-[#fef2f2] text-red-800 w-[90px] md:w-[110px]">61 - 90</th>
            <th class="px-2 py-1.5 border-slate-200 bg-[#fee2e2] text-red-900 w-[90px] md:w-[110px]">&gt; 90</th>
          </tr>
          
          <tr id="rowTotalMobAtas" class="text-sm md:text-base font-extrabold tracking-wide"></tr>

        </thead>
        <tbody id="bodyMatrix" class="divide-y divide-slate-100 bg-white text-sm"></tbody>
      </table>
    </div>
  </div>
</div>

<div id="modalDetailMob" class="fixed inset-0 hidden z-[9999] flex items-end md:items-center justify-center p-2 md:p-4">
  <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModalMob()"></div>
  <div class="relative bg-white w-full h-[92vh] max-w-[1700px] rounded-t-xl md:rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-scale-up">
    
    <div class="flex justify-between items-center px-4 py-4 md:px-6 border-b bg-slate-50 shrink-0 flex-wrap gap-3">
        <div class="flex-1 min-w-[250px]">
            <h3 class="font-bold text-slate-800 flex items-center gap-2 text-base md:text-xl">
                <span class="w-2 h-6 bg-blue-600 rounded-full hidden md:block"></span> 
                Detail Debitur MOB 
                <span id="badgeBucketDetail" class="text-xs md:text-sm bg-blue-600 text-white px-2.5 py-0.5 rounded-full shadow-sm ml-2 font-mono">Bucket ?</span>
            </h3>
            <p class="text-xs md:text-sm text-slate-500 mt-1 md:ml-4 font-mono font-medium" id="subTitleDetail">Loading...</p>
        </div>
        
        <div class="flex items-center gap-2 md:gap-3 ml-auto shrink-0 w-full sm:w-auto mt-2 sm:mt-0">
            <select id="opt_kankas_modal" class="inp px-3 h-10 flex-1 sm:w-[160px] text-xs md:text-sm font-bold text-blue-800 bg-blue-50 outline-none" onchange="fetchDetailMob()">
                <option value="">Semua Kankas</option>
            </select>

            <button onclick="exportExcelDetailMob()" class="h-10 px-4 border-none bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition shadow-sm flex items-center justify-center font-bold text-xs md:text-sm uppercase tracking-wider">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span class="hidden sm:inline ml-1.5">Excel</span>
            </button>
            <button onclick="closeModalMob()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-200 hover:bg-red-500 hover:text-white text-slate-600 transition font-bold text-2xl leading-none">&times;</button>
        </div>
    </div>

    <div class="flex-1 overflow-auto bg-slate-50 relative custom-scrollbar p-0 md:p-3">
        <div id="loadingModal" class="hidden absolute inset-0 bg-white/90 z-40 flex flex-col items-center justify-center text-blue-600 backdrop-blur-sm">
            <div class="animate-spin rounded-full h-10 w-10 border-4 border-blue-500 border-t-transparent mb-3"></div>
            <span class="text-sm font-bold uppercase tracking-widest">Memuat Detail...</span>
        </div>
        
        <table class="w-max min-w-full text-sm text-left text-slate-700 border border-slate-200 md:rounded-xl shadow-sm bg-white table-fixed" id="tableExportMob">
            <thead class="bg-slate-100 text-slate-600 font-extrabold sticky top-0 z-30 shadow-sm text-xs uppercase tracking-wider">
                <tr>
                    <th class="px-3 py-4 border-b border-r border-slate-200 w-[140px] sticky left-0 bg-slate-100 z-40 shadow-[1px_0_0_#cbd5e1] rounded-tl-xl text-blue-900">Rekening</th>
                    <th class="px-4 py-4 border-b border-r border-slate-200 w-[240px] md:w-[280px] sticky left-[140px] bg-slate-100 z-40 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.1)] text-blue-900">Nama Nasabah</th>
                    <th class="px-4 py-4 border-b border-r border-slate-200 w-[150px]">Alamat</th>
                    <th class="px-3 py-4 border-b border-r border-slate-200 w-[130px] text-center">No HP</th>
                    <th class="px-3 py-4 border-b border-r border-slate-200 w-[120px] text-center">Kankas</th>
                    <th class="px-4 py-4 border-b border-r border-slate-200 w-[140px] text-center bg-blue-50/50 text-blue-800">Tgl Realisasi</th>
                    <th class="px-4 py-4 border-b border-r border-slate-200 w-[140px] text-right">Plafond</th>
                    <th class="px-4 py-4 border-b border-r border-slate-200 w-[140px] text-right text-blue-700">Baki Debet</th>
                    <th class="px-3 py-4 border-b border-r border-slate-200 w-[60px] text-center">Kol</th>
                    <th class="px-4 py-4 border-b border-r border-red-200 w-[130px] text-right bg-red-50 text-red-800">Tot Tunggakan</th>
                    <th class="px-3 py-4 border-b border-r border-orange-200 w-[80px] text-center bg-orange-50 text-orange-800">HM PK</th>
                    <th class="px-3 py-4 border-b border-r border-orange-200 w-[80px] text-center bg-orange-50 text-orange-800">HM BG</th>
                    <th class="px-4 py-4 border-b border-r border-green-200 w-[130px] text-center bg-green-50 text-green-800">Tgl Trans</th>
                    <th class="px-4 py-4 border-b border-r border-green-200 w-[130px] text-right bg-green-50 text-green-800">Total Bayar</th>
                    <th class="px-4 py-4 border-b border-r border-slate-200 w-[140px] text-right">Tabungan</th>
                    <th class="px-3 py-4 border-b border-slate-200 w-[100px] text-center">Stat Tab</th>
                </tr>
            </thead>
            <tbody id="bodyModalDetail" class="divide-y divide-slate-100 bg-white text-sm"></tbody>
        </table>
    </div>

    <div class="px-4 py-4 md:px-6 border-t bg-white flex justify-between items-center shrink-0">
        <span id="pageInfoDetail" class="text-xs md:text-sm font-bold text-slate-600 bg-slate-100 px-3 py-1 rounded-lg">0 Data</span>
        <div class="flex gap-2">
            <button id="btnPrevDetail" onclick="changePageDetailMob(-1)" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 hover:border-slate-400 disabled:opacity-50 transition shadow-sm">« Prev</button>
            <button id="btnNextDetail" onclick="changePageDetailMob(1)" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 hover:border-slate-400 disabled:opacity-50 transition shadow-sm">Next »</button>
        </div>
    </div>
  </div>
</div>

<script>
// --- CONFIG ---
const API_URL = './api/kredit/'; 
const API_KODE = './api/kode/';
const API_DATE = './api/date/'; 
const nfID = new Intl.NumberFormat('id-ID');
const fmt  = n => nfID.format(Math.round(Number(n||0)));
const apiCall = (url, opt={}) => (window.apiFetch ? window.apiFetch(url,opt) : fetch(url,opt));

let abortMainMob;
let detailParamsMob = {}; 
let detailPageMob = 1;
const optKantorMob = document.getElementById('opt_kantor_mob');
let rekapDataCacheMob = null; 

// --- INIT ---
window.addEventListener('DOMContentLoaded', async () => {
    const user = (window.getUser && window.getUser()) || null;
    const userKode = (user?.kode ? String(user.kode).padStart(3,'0') : '000');

    await populateKantorOptionsMob(userKode);
    await loadKankasModalDropdownMob();

    const d = await getLastHarianData(); 
    document.getElementById('harian_date_mob').value = d ? d.last_created : new Date().toISOString().split('T')[0];

    fetchRekapMob();
});

async function getLastHarianData(){
    try{ const r=await apiCall(API_DATE); return (await r.json()).data; }
    catch{ return null; }
}

async function populateKantorOptionsMob(userKode){
    if(userKode !== '000'){
        optKantorMob.innerHTML = `<option value="${userKode}">CABANG ${userKode}</option>`;
        optKantorMob.value = userKode;
        optKantorMob.disabled = true;
        return;
    }
    try {
        const res = await apiCall(API_KODE, { 
            method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({type:'kode_kantor'}) 
        });
        const json = await res.json();
        const list = Array.isArray(json.data) ? json.data : [];
        let html = `<option value="">KONSOLIDASI (SEMUA)</option>`;
        list.filter(x => x.kode_kantor && x.kode_kantor !== '000')
            .sort((a,b) => String(a.kode_kantor).localeCompare(b.kode_kantor))
            .forEach(it => {
               html += `<option value="${String(it.kode_kantor).padStart(3,'0')}">${String(it.kode_kantor).padStart(3,'0')} - ${it.nama_kantor}</option>`;
            });
        optKantorMob.innerHTML = html;
        optKantorMob.disabled = false;
    } catch(e){ optKantorMob.innerHTML = `<option value="">Error Load</option>`; }
}

async function loadKankasModalDropdownMob() {
    const elKankas = document.getElementById('opt_kankas_modal');
    const branch = optKantorMob.value;
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

optKantorMob.addEventListener('change', () => { loadKankasModalDropdownMob(); });

// --- 1. FETCH REKAP MOB ---
async function fetchRekapMob(){
    const loading = document.getElementById('loadingMob');
    const tbody  = document.getElementById('bodyMatrix');
    const harian  = document.getElementById('harian_date_mob').value;
    const kode    = optKantorMob.value || null; 

    if(abortMainMob) abortMainMob.abort();
    abortMainMob = new AbortController();

    loading.classList.remove('hidden');
    tbody.innerHTML = `<tr><td colspan="11" class="py-20 text-center text-slate-400 italic text-base">Sedang mengambil data...</td></tr>`;
    rekapDataCacheMob = null;

    try {
        const payload = { 
            type: "mob_vintage",
            harian_date: harian,
            kode_kantor: kode
        };
        
        const res = await apiCall(API_URL, {
            method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload), signal: abortMainMob.signal
        });
        const json = await res.json();
        
        if(json.status !== 200) throw new Error(json.message);

        const rawData = json.data.data || [];
        const bucketsKey = json.data.buckets_order || ['0', '1 - 7', '8 - 14', '15 - 21', '22 - 30', '31 - 60', '61 - 90', '> 90'];

        if(rawData.length === 0){
            tbody.innerHTML = `<tr><td colspan="11" class="py-20 text-center text-slate-400 italic text-base">Tidak ada data.</td></tr>`;
            document.getElementById('rowTotalMobAtas').innerHTML = '';
            return;
        }

        // --- AGGREGATION LOGIC (HILANGKAN CABANG, GABUNG SEMUA) ---
        let displayData = [];
        
        if (!kode) { // KONSOLIDASI
            const grouped = {};
            rawData.forEach(row => {
                const key = row.bulan_realisasi;
                if (!grouped[key]) {
                    grouped[key] = { bulan_realisasi: key, mob: row.mob, total_plafond: 0, buckets: {} };
                    bucketsKey.forEach(b => grouped[key].buckets[b] = { os: 0, noa: 0, pct: 0 });
                }
                grouped[key].total_plafond += parseFloat(row.total_plafond || 0);
                bucketsKey.forEach(b => {
                    const srcBucket = row.buckets[b] || { os:0, noa:0 };
                    grouped[key].buckets[b].os  += parseFloat(srcBucket.os || 0);
                    grouped[key].buckets[b].noa += parseInt(srcBucket.noa || 0);
                });
            });
            displayData = Object.values(grouped).sort((a,b) => b.bulan_realisasi.localeCompare(a.bulan_realisasi)); // Sort Descending Bulan
        } else {
            // Meskipun per cabang, kita buang visual cabangnya
            displayData = rawData.sort((a,b) => b.bulan_realisasi.localeCompare(a.bulan_realisasi));
        }

        // Hitung Ulang % (Semua Kondisi)
        displayData.forEach(row => {
            const pembagi = parseFloat(row.total_plafond) > 0 ? parseFloat(row.total_plafond) : 1;
            bucketsKey.forEach(b => {
                if(!row.buckets[b]) row.buckets[b] = { os:0, noa:0, pct:0 }; 
                row.buckets[b].pct = ((parseFloat(row.buckets[b].os) / pembagi) * 100).toFixed(2);
            });
        });

        rekapDataCacheMob = { data: displayData, buckets: bucketsKey };

        let html = '';
        let grandTotal = { plafond: 0, buckets: {} };
        bucketsKey.forEach(b => grandTotal.buckets[b] = { os:0, noa:0 });

        displayData.forEach(r => {
            grandTotal.plafond += parseFloat(r.total_plafond || 0);
            let cells = '';
            
            bucketsKey.forEach(key => {
                const bData = r.buckets[key] || { pct:0, noa:0, os:0 };
                grandTotal.buckets[key].os  += parseFloat(bData.os || 0);
                grandTotal.buckets[key].noa += parseInt(bData.noa || 0);

                let bgClass = 'bg-transparent';
                if(key !== '0' && parseFloat(bData.pct) > 0) bgClass = 'bg-red-50/70 text-red-700 border-red-100';
                if(key === '0' && parseFloat(bData.pct) > 90) bgClass = 'bg-emerald-50/70 text-emerald-700 border-emerald-100';

                const cabangParam = (!kode) ? '' : r.kode_cabang;
                const clickEv = (parseFloat(bData.os) > 0) ? `onclick="openModalMob('${cabangParam}', '${r.bulan_realisasi}', '${key}')"` : '';
                const cursor = (parseFloat(bData.os) > 0) ? 'cell-hover' : '';

                cells += `
                    <td class="px-2 py-2 border-r border-slate-200 text-xs md:text-sm ${bgClass} p-0 align-middle">
                        <div class="h-full flex flex-col justify-center py-2 md:py-2.5 ${cursor} transition" ${clickEv}>
                            <span class="font-extrabold font-mono">${bData.pct}%</span>
                            <span class="text-[11px] md:text-xs font-bold mt-1 whitespace-nowrap opacity-90">${parseFloat(bData.os)>0 ? fmt(bData.os) : '-'}</span>
                            <span class="text-[10px] md:text-[11px] font-semibold opacity-70 mt-0.5">(${bData.noa} NOA)</span>
                        </div>
                    </td>`;
            });

            html += `
                <tr class="hover:bg-slate-50 border-b border-slate-200 group h-[52px] md:h-[60px]">
                    <td class="sticky-left px-4 py-2 text-left font-bold text-xs md:text-sm text-slate-700 bg-white group-hover:bg-slate-50 border-r border-slate-200 align-middle shadow-[inset_-1px_0_0_#e2e8f0] z-10">${r.bulan_realisasi}</td>
                    <td class="px-3 py-2 border-r border-blue-200 text-center font-bold text-xs md:text-sm text-blue-700 bg-blue-50/30 align-middle">${r.mob}</td>
                    <td class="px-4 py-2 border-r border-blue-200 text-right font-mono font-bold text-sm md:text-base text-blue-800 bg-blue-50/10 align-middle">${fmt(r.total_plafond)}</td>
                    ${cells}
                </tr>`;
        });
        tbody.innerHTML = html;

        // --- RENDER TOTAL STICKY (DI BAWAH THEAD) ---
        let tf = `<th class="sticky-left px-4 text-left uppercase tracking-widest align-middle text-blue-900 z-50 bg-[#eff6ff]">TOTAL</th>
                  <th class="border-r border-blue-300 px-3 text-center align-middle text-blue-900 bg-[#eff6ff]">-</th>
                  <th class="border-r border-blue-300 px-4 text-right font-mono font-bold text-base md:text-lg text-blue-900 align-middle bg-[#eff6ff]">${fmt(grandTotal.plafond)}</th>`;
        
        bucketsKey.forEach(b => { 
            const bTot = grandTotal.buckets[b];
            const pembagiTotal = grandTotal.plafond > 0 ? grandTotal.plafond : 1;
            const pctTotal = ((bTot.os / pembagiTotal) * 100).toFixed(2);
            tf += `<th class="border-r border-blue-300 p-0 align-middle bg-[#eff6ff]">
                      <div class="flex flex-col justify-center h-full py-2">
                          <span class="text-xs md:text-sm text-blue-800 font-bold font-mono">${pctTotal}%</span>
                          <span class="text-sm md:text-base text-blue-900 font-extrabold font-mono mt-1 whitespace-nowrap">${fmt(bTot.os)}</span>
                          <span class="text-[10px] md:text-xs text-blue-600/80 font-bold mt-0.5">(${bTot.noa} NOA)</span>
                      </div>
                   </th>` 
        });
        document.getElementById('rowTotalMobAtas').innerHTML = tf;

    } catch(err) {
        if(err.name !== 'AbortError') tbody.innerHTML = `<tr><td colspan="11" class="py-16 text-center text-red-500 font-bold tracking-widest uppercase text-sm">Error: ${err.message}</td></tr>`;
    } finally {
        loading.classList.add('hidden');
    }
}

// EXPORT EXCEL REKAP
window.exportExcelRekapMob = function() {
    if(!rekapDataCacheMob || !rekapDataCacheMob.data) return alert("Tidak ada data rekap untuk didownload.");

    const rows = rekapDataCacheMob.data;
    const bk = rekapDataCacheMob.buckets;
    
    let csv = "Bulan Realisasi\tMOB\tTotal Plafond\t";
    bk.forEach(b => csv += `% ${b}\tOS ${b}\tNOA ${b}\t`);
    csv += "\n";

    rows.forEach(r => {
        csv += `'${r.bulan_realisasi}\t${r.mob}\t${Math.round(r.total_plafond)}\t`;
        bk.forEach(b => {
            const d = r.buckets[b];
            csv += `${d.pct}%\t${Math.round(d.os)}\t${d.noa}\t`;
        });
        csv += "\n";
    });

    const blob = new Blob([csv], { type: 'application/vnd.ms-excel' });
    const a = document.createElement('a');
    a.href = window.URL.createObjectURL(blob);
    a.download = `Rekap_MOB_Vintage_${document.getElementById("harian_date_mob").value}.xls`; 
    a.click();
}

// --- 2. MODAL DETAIL LOGIC ---
async function openModalMob(cabang, bulan, bucket){
    detailParamsMob = {
        type: "detail_mob_debitur",
        harian_date: document.getElementById('harian_date_mob').value,
        kode_kantor: cabang, 
        bulan_realisasi: bulan,
        bucket_label: bucket
    };
    detailPageMob = 1;

    document.getElementById('modalDetailMob').classList.remove('hidden');
    document.getElementById('badgeBucketDetail').innerText = `Bucket ${bucket}`;
    const txtCabang = cabang ? `Cabang ${cabang}` : "SEMUA CABANG";
    document.getElementById('subTitleDetail').innerText = `${txtCabang} • Real ${bulan}`;
    
    fetchDetailMob();
}

async function fetchDetailMob(){
    const loader = document.getElementById('loadingModal');
    const tbody  = document.getElementById('bodyModalDetail');
    const info   = document.getElementById('pageInfoDetail');
    const btnPrev = document.getElementById('btnPrevDetail');
    const btnNext = document.getElementById('btnNextDetail');

    loader.classList.remove('hidden');
    tbody.innerHTML = '';

    try {
        const kankasModal = document.getElementById('opt_kankas_modal').value;
        const payload = { ...detailParamsMob, kode_kankas: kankasModal, page: detailPageMob };
        
        const res = await apiCall(API_URL, {
            method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)
        });
        const json = await res.json();
        
        const list = json.data?.data || [];
        const totalRecords = json.data?.total_records || 0;
        const totalPages   = json.data?.total_pages || 1;

        if(list.length === 0){
            tbody.innerHTML = `<tr><td colspan="16" class="py-20 text-center text-slate-400 italic text-base">Tidak ada data detail.</td></tr>`;
            info.innerText = `0 Data`;
            btnPrev.disabled = true; btnNext.disabled = true;
            return;
        }

        let html = '';
        list.forEach(row => {
            const textHp = row.no_hp ? `<span class="font-mono text-slate-600">${row.no_hp}</span>` : `<span class="text-slate-400">-</span>`;

            // LOGIKA TABUNGAN: Aman jika Tabungan >= 1.5 * Totung
            let statTabungan = `<span class="text-red-500 font-bold text-xs">Belum Aman</span>`;
            if(parseFloat(row.tabungan) >= (1.5 * parseFloat(row.totung))) {
                statTabungan = `<span class="text-green-600 font-bold text-xs">Aman</span>`;
            }

            // 🔥 Pemotongan Alamat max 25 karakter pakai JS
            let alamatLengkap = row.alamat || '-';
            let alamatPendek = alamatLengkap.length > 25 ? alamatLengkap.substring(0, 25) + '...' : alamatLengkap;

            // Freeze kolom No Rekening & Nama
            html += `
                <tr class="hover:bg-slate-50/50 border-b border-slate-100 transition h-[48px] group">
                    <td class="px-3 py-2 font-mono text-sm text-slate-600 sticky left-0 bg-white group-hover:bg-slate-50 z-20 shadow-[1px_0_0_#f1f5f9] border-r border-slate-100">${row.no_rekening}</td>
                    <td class="px-4 py-2 font-bold text-sm text-slate-700 truncate sticky left-[140px] bg-white group-hover:bg-slate-50 z-20 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.1)] border-r border-slate-100" title="${row.nama_nasabah}">${row.nama_nasabah}</td>
                    <td class="px-4 py-2 text-slate-500 text-sm border-r border-slate-100 whitespace-nowrap" title="${alamatLengkap}">${alamatPendek}</td>
                    <td class="px-3 py-2 text-center border-r border-slate-100 text-sm">${textHp}</td>
                    <td class="px-3 py-2 text-center font-mono text-xs md:text-sm text-slate-500 border-r border-slate-100">${row.kankas||'-'}</td>
                    <td class="px-4 py-2 text-center font-mono text-sm text-blue-700 bg-blue-50/30 border-r border-blue-100">${row.tgl_realisasi}</td>
                    <td class="px-4 py-2 text-right font-mono text-sm text-slate-500 border-r border-slate-100">${fmt(row.plafond)}</td>
                    <td class="px-4 py-2 text-right font-mono font-bold text-sm text-blue-700 border-r border-slate-100 bg-slate-50/50">${fmt(row.os)}</td>
                    <td class="px-3 py-2 text-center font-bold text-sm text-slate-600 border-r border-slate-100">${row.kolektibilitas||'-'}</td>
                    <td class="px-4 py-2 text-right font-mono font-bold text-sm text-red-600 bg-red-50/30 border-r border-red-100">${fmt(row.totung)}</td>
                    <td class="px-3 py-2 text-center font-mono text-sm text-orange-700 bg-orange-50/30 border-r border-orange-100">${row.hari_menunggak_pokok}</td>
                    <td class="px-3 py-2 text-center font-mono text-sm text-orange-700 bg-orange-50/30 border-r border-orange-100">${row.hari_menunggak_bunga}</td>
                    <td class="px-4 py-2 text-center font-mono text-sm text-green-700 bg-green-50/30 border-r border-green-100">${row.tgl_trans || '-'}</td>
                    <td class="px-4 py-2 text-right font-mono font-bold text-sm text-green-700 bg-green-50/30 border-r border-green-100">${fmt(row.transaksi)}</td>
                    <td class="px-4 py-2 text-right font-mono font-bold text-sm text-emerald-600 bg-emerald-50/10 border-r border-slate-100">${fmt(row.tabungan)}</td>
                    <td class="px-3 py-2 text-center">${statTabungan}</td>
                </tr>
            `;
        });
        tbody.innerHTML = html;

        info.innerText = `Hal ${detailPageMob} dari ${totalPages} (${fmt(totalRecords)} Data)`;
        
        btnPrev.disabled = detailPageMob <= 1;
        btnNext.disabled = detailPageMob >= totalPages;

    } catch(e){
        console.error(e);
        tbody.innerHTML = `<tr><td colspan="16" class="py-16 text-center text-red-500 font-bold uppercase tracking-widest text-sm">Gagal mengambil detail.</td></tr>`;
    } finally {
        loader.classList.add('hidden');
    }
}

window.changePageDetailMob = function(step) {
    detailPageMob += step;
    fetchDetailMob();
}

window.exportExcelDetailMob = async function() {
    const btn = event.target.closest('button'); const txt = btn.innerHTML;
    btn.innerHTML = `<span class="animate-spin inline-block h-4 w-4 border-2 border-white border-t-transparent rounded-full mr-2"></span>...`;
    btn.disabled = true;

    try {
        const kankasModal = document.getElementById('opt_kankas_modal').value;
        const payload = { ...detailParamsMob, kode_kankas: kankasModal, page: 1, limit: 10000 };
        
        const res = await apiCall(API_URL, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
        const json = await res.json();
        const rows = json.data?.data || [];
        
        if(rows.length === 0) { alert("Tidak ada data detail untuk diexport"); return; }

        let csv = `No Rekening\tNama Nasabah\tAlamat\tNo HP\tKankas\tTgl Realisasi\tPlafond\tBaki Debet\tKol\tTot Tunggakan\tHM Pokok\tHM Bunga\tTgl Transaksi\tTotal Bayar\tTabungan\tStatus Tabungan\n`;
        rows.forEach(x => {
            let statTabungan = (parseFloat(x.tabungan) >= (1.5 * parseFloat(x.totung))) ? 'Aman' : 'Belum Aman';
            csv += `'${x.no_rekening}\t${x.nama_nasabah}\t${x.alamat||''}\t'${x.no_hp||''}\t${x.kankas||''}\t${x.tgl_realisasi}\t${Math.round(x.plafond)}\t${Math.round(x.os)}\t${x.kolektibilitas||''}\t${Math.round(x.totung)}\t${x.hari_menunggak_pokok}\t${x.hari_menunggak_bunga}\t${x.tgl_trans||''}\t${Math.round(x.transaksi)}\t${Math.round(x.tabungan)}\t${statTabungan}\n`;
        });

        const blob = new Blob([csv], { type: 'application/vnd.ms-excel' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Detail_MOB_${detailParamsMob.bulan_realisasi}_Bucket_${detailParamsMob.bucket_label}.xls`;
        document.body.appendChild(a); a.click(); document.body.removeChild(a);

    } catch(e) { console.error(e); alert("Gagal export data."); } 
    finally { btn.innerHTML = txt; btn.disabled = false; }
}

window.closeModalMob = function(){ document.getElementById('modalDetailMob').classList.add('hidden'); }
document.addEventListener('keydown', e => { if(e.key === 'Escape') closeModalMob(); });
</script>