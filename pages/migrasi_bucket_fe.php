<div class="max-w-[1920px] mx-auto px-4 py-6 h-screen flex flex-col bg-gray-50 font-sans">
  
  <div class="mb-5 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div class="flex items-center gap-3">
      <div class="p-2.5 bg-blue-600 rounded-lg text-white shadow-lg shadow-blue-200">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
      </div>
      <div>
        <h1 class="text-xl font-bold text-gray-800 tracking-tight">Analisa Migrasi Bucket (Flow Rate)</h1>
        <p class="text-xs text-gray-500 font-medium">Monitoring pergerakan saldo nasabah M-1 ke Current</p>
      </div>
    </div>
    
    <div id="summaryCheck" class="hidden flex flex-wrap items-center gap-5 text-xs bg-white px-5 py-2.5 rounded-xl border border-gray-200 shadow-sm">
        <div class="text-right">
            <span class="block text-gray-400 font-bold text-[9px] uppercase tracking-wider">Total OS M-1</span>
            <span class="font-mono font-bold text-gray-700 text-sm" id="valM1">0</span>
        </div>
        <div class="h-8 w-px bg-gray-100"></div>
        <div class="text-right">
            <span class="block text-gray-400 font-bold text-[9px] uppercase tracking-wider">Total OS Current</span>
            <span class="font-mono font-bold text-blue-600 text-sm" id="valCurr">0</span>
        </div>
        <div class="h-8 w-px bg-gray-100"></div>
        <div class="text-right">
            <span class="block text-gray-400 font-bold text-[9px] uppercase tracking-wider">Net Growth</span>
            <span class="font-mono font-bold text-gray-800 text-sm" id="valGrowth">0</span>
        </div>
        <div class="h-8 w-px bg-gray-100"></div>
        <div class="text-right bg-yellow-50 px-3 py-1 rounded-lg border border-yellow-100">
            <span class="block text-yellow-700 font-bold text-[9px] uppercase tracking-wider">Gap (Est. Top Up)</span>
            <span class="font-mono font-bold text-yellow-600 text-sm" id="valCheck" title="Selisih Growth vs (Realisasi - RunOff)">0</span>
        </div>
    </div>
  </div>

  <div class="flex flex-wrap items-end gap-3 mb-4 bg-white p-4 rounded-xl border border-gray-200 shadow-sm w-fit">
      <div class="field">
        <label class="lbl">Kantor Cabang</label>
        <select id="opt_kantor" class="inp min-w-[220px] cursor-pointer"><option value="">Memuat Data...</option></select>
      </div>
      <div class="field">
        <label class="lbl">Tgl Closing (M-1)</label>
        <input type="date" id="closing_date" class="inp w-[140px] cursor-pointer">
      </div>
      <div class="field">
        <label class="lbl">Tgl Laporan (Current)</label>
        <input type="date" id="harian_date" class="inp w-[140px] cursor-pointer">
      </div>
      <button onclick="fetchMatrix()" class="btn-primary mb-[1px]" title="Refresh Data">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
        <span class="ml-1.5 text-xs font-bold">Tampilkan</span>
      </button>
  </div>

  <div class="flex-1 min-h-0 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg relative flex flex-col">
    
    <div id="loadingMatrix" class="hidden absolute inset-0 bg-white/90 backdrop-blur-[1px] z-50 flex flex-col items-center justify-center text-blue-600 font-medium">
        <div class="animate-spin rounded-full h-10 w-10 border-4 border-blue-100 border-t-blue-600 mb-3 shadow-md"></div>
        <span class="animate-pulse text-sm font-semibold tracking-wide">MENGOLAH DATA...</span>
    </div>

    <div class="flex-1 overflow-auto custom-scrollbar">
      <table id="tabelMatrix" class="min-w-full text-xs text-center text-gray-700 border-separate border-spacing-0">
        <thead class="uppercase font-bold bg-gray-50 text-gray-800 sticky top-0 z-30 shadow-sm">
          <tr>
            <th rowspan="2" class="px-4 py-3 border-r border-b sticky left-0 bg-gray-100 z-40 w-[160px] text-left align-middle shadow-[4px_0_10px_-2px_rgba(0,0,0,0.05)]">
                <span class="block text-[10px] text-gray-400 font-normal mb-0.5">Dari Bucket</span>
                <span class="text-sm text-gray-900 font-extrabold">POSISI M-1</span>
            </th>
            
            <th colspan="2" class="px-2 py-1 border-r border-b bg-blue-50 text-blue-900 border-t-4 border-t-blue-500">SALDO AWAL (M-1)</th>
            
            <th rowspan="2" class="px-2 py-1 border-r border-b bg-green-50 text-green-800 min-w-[120px] border-t-4 border-t-green-500">0</th>
            <th rowspan="2" class="px-2 py-1 border-r border-b bg-yellow-50 text-yellow-800 min-w-[120px] border-t-4 border-t-yellow-400">1 - 7</th>
            <th rowspan="2" class="px-2 py-1 border-r border-b bg-yellow-50 text-yellow-800 min-w-[120px] border-t-4 border-t-yellow-400">8 - 14</th>
            <th rowspan="2" class="px-2 py-1 border-r border-b bg-yellow-100 text-yellow-800 min-w-[120px] border-t-4 border-t-yellow-500">15 - 21</th>
            <th rowspan="2" class="px-2 py-1 border-r border-b bg-orange-50 text-orange-800 min-w-[120px] border-t-4 border-t-orange-500">22 - 30</th>
            <th rowspan="2" class="px-2 py-1 border-r border-b bg-red-50 text-red-800 min-w-[120px] border-t-4 border-t-red-600">BE (>30)</th>

            <th rowspan="2" class="px-2 py-1 border-r border-b bg-gray-100 text-gray-600 min-w-[100px] border-t-4 border-t-gray-400">ANGSURAN</th>
            <th rowspan="2" class="px-2 py-1 border-r border-b bg-gray-100 text-gray-600 min-w-[100px] border-t-4 border-t-gray-400">PELUNASAN</th>
            <th rowspan="2" class="px-2 py-1 border-b bg-gray-200 text-gray-800 min-w-[100px] border-t-4 border-t-gray-500">TOTAL RUN OFF</th>
          </tr>

          <tr class="text-[10px]">
            <th class="px-2 py-1 border-r border-b bg-blue-50/50 min-w-[70px]">NOA</th>
            <th class="px-2 py-1 border-r border-b bg-blue-50/50 min-w-[120px]">OS</th>
          </tr>
        </thead>
        
        <tbody id="bodyMatrix" class="divide-y divide-gray-100 bg-white text-[11px]"></tbody>
        
        <tfoot id="footMatrix" class="bg-gray-900 text-white font-bold sticky bottom-0 z-30 text-[11px] shadow-[0_-4px_15px_rgba(0,0,0,0.2)]"></tfoot>
      </table>
    </div>
  </div>
</div>

<div id="modalDetail" class="fixed inset-0 hidden z-[9999] flex items-center justify-center p-4">
  <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
  <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-7xl max-h-[90vh] flex flex-col animate-scale-up border border-gray-200">
    
    <div class="flex items-center justify-between p-5 border-b bg-gray-50 rounded-t-xl">
      <div>
        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-3">
            <span class="p-1.5 bg-blue-100 text-blue-600 rounded">📄</span> Detail Nasabah
            <span id="badgeMigrasi" class="px-2.5 py-0.5 rounded-full text-xs font-mono bg-gray-800 text-white shadow-sm border border-gray-600">...</span>
        </h3>
        <p class="text-xs text-gray-500 mt-1 ml-1">Klik header kolom untuk mengurutkan (coming soon)</p>
      </div>
      <button onclick="closeModal()" class="w-8 h-8 flex items-center justify-center rounded-full bg-white text-gray-400 hover:text-red-500 hover:bg-red-50 transition shadow-sm border border-gray-200">
        ✕
      </button>
    </div>

    <div class="flex-1 overflow-auto bg-white relative min-h-[300px] custom-scrollbar">
      <table class="w-full text-xs text-left text-gray-700 whitespace-nowrap">
        <thead class="text-xs text-gray-500 font-bold uppercase bg-gray-100 sticky top-0 shadow-sm z-10">
          <tr>
            <th class="px-4 py-3 border-b">No Rekening</th>
            <th class="px-4 py-3 border-b">Nama Nasabah</th>
            <th class="px-4 py-3 text-right border-b">OS M-1</th>
            <th class="px-4 py-3 text-right border-b bg-green-50 text-green-700 border-l border-green-100">OS Current</th>
            <th class="px-4 py-3 text-center border-b">Status</th>
            <th class="px-4 py-3 text-center border-b">DPD</th>
            <th class="px-4 py-3 border-b">Produk</th>
          </tr>
        </thead>
        <tbody id="bodyDetail" class="divide-y divide-gray-100"></tbody>
      </table>
      
      <div id="loadingDetail" class="hidden absolute inset-0 bg-white/90 flex flex-col items-center justify-center text-gray-500 z-20">
         <span class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-500 border-t-transparent mb-2"></span>
         <span class="text-xs font-medium">Mengambil data detail...</span>
      </div>
    </div>

    <div class="p-3 border-t bg-gray-50 rounded-b-xl flex justify-between items-center gap-2">
      <span class="text-xs text-gray-500 font-medium bg-white px-3 py-1 rounded border border-gray-200 shadow-sm" id="pageInfo">0 Data</span>
      <div class="flex items-center gap-2">
          <button id="btnPrev" class="px-3 py-1.5 bg-white border border-gray-300 rounded-md hover:bg-gray-100 text-xs font-medium disabled:opacity-50 transition shadow-sm">Prev</button>
          <button id="btnNext" class="px-3 py-1.5 bg-white border border-gray-300 rounded-md hover:bg-gray-100 text-xs font-medium disabled:opacity-50 transition shadow-sm">Next</button>
      </div>
    </div>
  </div>
</div>

<style>
  .inp { border:1px solid #d1d5db; border-radius:8px; padding:7px 10px; font-size:13px; outline:none; transition: all 0.2s; background-color: #fff; }
  .inp:focus { border-color:#3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
  .lbl { font-size:10px; font-weight:700; color:#64748b; display:block; margin-bottom:4px; text-transform: uppercase; letter-spacing: 0.03em; }
  
  .btn-primary { 
      background-color:#2563eb; color:white; border-radius:8px; padding: 0 14px; height: 36px;
      display:flex; align-items:center; justify-content:center; border:none; cursor:pointer; transition: all 0.2s;
      box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
  }
  .btn-primary:hover { background-color:#1d4ed8; transform: translateY(-1px); box-shadow: 0 4px 6px rgba(37, 99, 235, 0.3); }
  .btn-primary:active { transform: translateY(0); }

  .sticky-col { position: sticky; left: 0; background-color: #f9fafb; z-index: 20; border-right: 2px solid #e5e7eb; font-weight: 600; box-shadow: 4px 0 10px -2px rgba(0,0,0,0.05); }
  
  /* Cell Layout */
  .cell-single {
      height: 54px; padding: 4px 8px; 
      display: flex; flex-direction: column; justify-content: center; align-items: flex-end; 
      line-height: 1.3; transition: all 0.15s;
  }
  .cell-clickable { cursor: pointer; }
  .cell-clickable:hover { background-color: #eff6ff; z-index: 5; position: relative; box-shadow: inset 0 0 0 2px #bfdbfe; }

  /* Badges */
  .badge-pct { font-size: 9px; padding: 1px 5px; border-radius: 4px; margin-top: 3px; font-weight: 700; letter-spacing: 0.02em; }
  .pct-good { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
  .pct-warn { background-color: #fef9c3; color: #854d0e; border: 1px solid #fde047; }
  .pct-bad  { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
  
  /* Scrollbar */
  .custom-scrollbar::-webkit-scrollbar { width: 8px; height: 8px; }
  .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; }
  .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; border: 2px solid #f8fafc; }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

  @keyframes scaleUp { from { transform: scale(0.98); opacity: 0; } to { transform: scale(1); opacity: 1; } }
  .animate-scale-up { animation: scaleUp 0.15s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
</style>

<script>
  // --- CONFIG ---
  const API_ENDPOINT = './api/bucket_fe/'; 
  const API_DATE     = './api/date/'; 
  
  const fmt = n => new Intl.NumberFormat('id-ID').format(Number(n||0));
  const apiCall = (url, opt) => window.apiFetch ? window.apiFetch(url,opt) : fetch(url,opt);
  
  const BUCKETS = ['0', '1-7', '8-14', '15-21', '22-30', 'BE'];
  
  // State
  let modalState = { from:'', to:'', page:1, limit:100 };

  // --- INIT ---
  window.addEventListener('DOMContentLoaded', async () => {
      const user = (window.getUser && window.getUser()) || null;
      const userKode = (user?.kode ? String(user.kode).padStart(3,'0') : '000');
      await populateKantor(userKode);

      const dates = await getLastHarianData();
      if(dates) {
          document.getElementById('closing_date').value = dates.last_closing;
          document.getElementById('harian_date').value  = dates.last_created;
      } else {
          const today = new Date();
          document.getElementById('harian_date').value = today.toISOString().split('T')[0];
          document.getElementById('closing_date').value = new Date(today.getFullYear(), today.getMonth(), 0).toISOString().split('T')[0];
      }

      fetchMatrix();
  });

  async function getLastHarianData(){
    try{
        const r = await apiCall(API_DATE); 
        const j = await r.json(); 
        return j.data || null;
    } catch { return null; }
  }

  async function populateKantor(uKode){
      const el = document.getElementById('opt_kantor');
      if(uKode !== '000'){ el.innerHTML=`<option value="${uKode}">CABANG ${uKode}</option>`; el.disabled=true; return; }
      try {
          const r = await apiCall('./api/kode/', {method:'POST', body:JSON.stringify({type:'kode_kantor'})});
          const j = await r.json();
          let h = `<option value="">KONSOLIDASI (SEMUA)</option>`;
          (j.data||[]).filter(x=>x.kode_kantor!=='000')
                     .sort((a,b)=>a.kode_kantor.localeCompare(b.kode_kantor))
                     .forEach(x=> h+=`<option value="${x.kode_kantor}">${x.kode_kantor} - ${x.nama_kantor}</option>`);
          el.innerHTML = h;
      } catch(e){}
  }

  // --- FETCH REKAP ---
  async function fetchMatrix(){
      const loading = document.getElementById('loadingMatrix');
      const tbody   = document.getElementById('bodyMatrix');
      const tfoot   = document.getElementById('footMatrix');
      
      loading.classList.remove('hidden');
      tbody.innerHTML = ''; tfoot.innerHTML = '';

      try {
          const payload = {
              type: "rekap_migrasi_bucket",
              closing_date: document.getElementById('closing_date').value,
              harian_date: document.getElementById('harian_date').value,
              kode_kantor: document.getElementById('opt_kantor').value || null
          };
          
          const r = await apiCall(API_ENDPOINT, {method:'POST', body:JSON.stringify(payload)});
          const j = await r.json();
          
          if(j.status !== 200) throw new Error(j.message);
          
          renderMatrix(j.data);

      } catch(e){
          console.error(e);
          tbody.innerHTML = `<tr><td colspan="20" class="py-10 text-red-500 font-bold text-center bg-red-50">Gagal memuat data: ${e.message}</td></tr>`;
      } finally {
          loading.classList.add('hidden');
      }
  }

  function renderMatrix(data) {
      const tbody = document.getElementById('bodyMatrix');
      const GT    = data.grand_total;
      let html    = '';

      // 1. REALISASI ROW
      const real = data.realisasi || {noa:0, os:0};
      html += `
        <tr class="bg-green-50/70 border-b border-green-100 h-[60px]">
            <td class="sticky-col px-4 text-left font-bold text-green-800 border-r border-green-200 border-b-2">REALISASI BARU</td>
            <td class="border-r bg-gray-50">-</td><td class="border-r bg-gray-50">-</td>
            <td class="border-r bg-white p-0">
                <div class="cell-single cell-clickable" onclick="openDetail('REALISASI','0')">
                    <span class="text-[10px] text-blue-600 font-bold">${fmt(real.noa)}</span>
                    <span class="text-xs text-gray-800 font-bold">${fmt(real.os)}</span>
                    <span class="badge-pct bg-blue-100 text-blue-700 border-blue-200">New</span>
                </div>
            </td>
            <td colspan="5" class="bg-gray-50 text-center text-[10px] text-gray-400 italic border-r">Detail tersebar</td>
            <td class="bg-gray-100 border-r">-</td><td class="bg-gray-100 border-r">-</td><td class="bg-gray-200">-</td>
        </tr>
      `;

      // 2. MIGRATION ROWS
      BUCKETS.forEach((from, idx) => {
          const m1 = data.summary_m1[from] || {noa_m1:0, os_m1:0};
          
          let tr = `<tr class="border-b border-gray-100 hover:bg-gray-50 transition h-[58px]">
                <td class="sticky-col px-4 text-left font-semibold text-gray-700 border-r text-xs">${from}</td>
                <td class="border-r bg-blue-50/20 text-right px-2 font-mono text-[10px] font-bold text-gray-500">${fmt(m1.noa_m1)}</td>
                <td class="border-r bg-blue-50/20 text-right px-2 font-mono font-bold text-gray-800 text-xs">${fmt(m1.os_m1)}</td>`;

          let angsRow = 0;

          // Bucket Cells
          BUCKETS.forEach((to, tIdx) => {
              const cell = data.matrix[from][to] || {noa:0, os:0, angsuran:0};
              // Akumulasi angsuran dari backend (yang ditaruh per cell)
              angsRow += parseFloat(cell.angsuran || 0);

              let pctHtml = '';
              let isClickable = false;
              let bgClass = "bg-white";

              if (cell.os > 0) {
                  isClickable = true;
                  const pct = m1.os_m1 > 0 ? (cell.os / m1.os_m1 * 100).toFixed(1) : 0;
                  
                  let cl = 'pct-warn';
                  if(tIdx < idx) cl = 'pct-good'; 
                  if(tIdx > idx) cl = 'pct-bad';  
                  if(from==='0' && to==='0') cl = 'pct-good';
                  
                  pctHtml = `<span class="badge-pct ${cl}">${pct}%</span>`;
                  if (from === to) bgClass = "bg-yellow-50"; 
              }

              let clk = isClickable ? `class="cell-single cell-clickable ${bgClass}" onclick="openDetail('${from}','${to}')"` : `class="cell-single ${bgClass}"`;

              tr += `<td class="border-r p-0 border-b border-gray-100">
                    <div ${clk}>
                        <span class="text-[10px] text-blue-600 font-bold leading-none mb-0.5">${fmt(cell.noa)}</span>
                        <span class="text-xs text-gray-800 font-semibold leading-none mb-0.5">${fmt(cell.os)}</span>
                        ${pctHtml}
                    </div>
                </td>`;
          });

          // Run Off Columns
          const lunas = data.matrix[from]['O'] || {noa:0, pelunasan:0};
          
          tr += `<td class="border-r bg-gray-50 p-0">
                <div class="cell-single items-center justify-center">
                    <span class="text-xs text-green-700 font-mono font-bold">${fmt(angsRow)}</span>
                </div>
             </td>
             <td class="border-r bg-gray-50 p-0">
                <div class="cell-single cell-clickable items-center justify-center" onclick="openDetail('${from}','O')">
                    <span class="text-[10px] text-blue-600 font-bold leading-none mb-0.5">${fmt(lunas.noa)}</span>
                    <span class="text-xs text-gray-800 font-bold">${fmt(lunas.pelunasan)}</span>
                </div>
             </td>
             <td class="bg-gray-100 p-0 border-b border-gray-200">
                <div class="cell-single items-center justify-center">
                    <span class="text-xs text-gray-900 font-bold font-mono">${fmt(angsRow + parseFloat(lunas.pelunasan))}</span>
                </div>
             </td></tr>`;
          html += tr;
      });
      tbody.innerHTML = html;

      // 3. FOOTER (GRAND TOTAL)
      // Gunakan data dari backend 'grand_total'
      let tf = `<tr class="h-[60px] text-xs">
            <td class="sticky-col px-4 text-left bg-gray-800 text-white border-r border-gray-700">GRAND TOTAL</td>
            <td class="border-r border-gray-700 bg-gray-800 text-white text-right px-2 font-mono font-bold">${fmt(GT.m1.noa)}</td>
            <td class="border-r border-gray-700 bg-gray-800 text-white text-right px-2 font-mono font-bold text-xs">${fmt(GT.m1.os)}</td>`;
      
      BUCKETS.forEach(b => {
          const gb = GT.buckets[b];
          tf += `<td class="border-r border-gray-700 bg-gray-800 p-0">
                <div class="cell-single items-end pr-2 justify-center">
                    <span class="text-[10px] text-blue-300 font-bold mb-0.5">${fmt(gb.noa)}</span>
                    <span class="text-xs text-white font-bold">${fmt(gb.os)}</span>
                </div>
            </td>`;
      });

      tf += `<td class="border-r border-gray-700 bg-gray-800 text-right px-2 font-mono text-green-400 flex flex-col justify-center h-[60px] font-bold">${fmt(GT.angsuran)}</td>
             <td class="border-r border-gray-700 bg-gray-800 p-0">
                <div class="cell-single items-end pr-2 justify-center">
                    <span class="text-[10px] text-blue-300 font-bold mb-0.5">${fmt(GT.lunas.noa)}</span>
                    <span class="text-xs text-white font-bold">${fmt(GT.lunas.os)}</span>
                 </div>
             </td>
             <td class="bg-gray-900 text-right px-2 font-mono text-white font-bold flex flex-col justify-center h-[60px] shadow-inner">${fmt(GT.runoff_total.os)}</td></tr>`;
      
      document.getElementById('footMatrix').innerHTML = tf;

      // 4. CHECK SUMMARY (Validasi)
      updateCheck(GT, real);
  }

  function updateCheck(GT, real) {
      // Total Current = Sum(Bucket GT OS) + Realisasi
      let curOS = parseFloat(real.os);
      BUCKETS.forEach(b => curOS += GT.buckets[b].os);
      
      const m1OS = parseFloat(GT.m1.os);
      const growth = curOS - m1OS; // Net Growth
      
      // Hitung Balik TopUp: Growth - Realisasi + RunOff (Angsuran + Lunas)
      const runOffTotal = parseFloat(GT.runoff_total.os);
      const checkVal = growth - parseFloat(real.os) + runOffTotal;

      document.getElementById('summaryCheck').classList.remove('hidden');
      document.getElementById('valM1').innerText = fmt(m1OS);
      document.getElementById('valCurr').innerText = fmt(curOS);
      
      const elGrowth = document.getElementById('valGrowth');
      elGrowth.innerText = fmt(growth);
      elGrowth.className = growth >= 0 ? "font-mono font-bold text-green-600 text-sm" : "font-mono font-bold text-red-600 text-sm";

      document.getElementById('valCheck').innerText = fmt(checkVal);
  }

  // --- DETAIL & PAGINATION ---
  function openDetail(f,t){
      modalState = {from:f, to:t, page:1, limit:100};
      document.getElementById('modalDetail').classList.remove('hidden');
      document.getElementById('badgeMigrasi').innerText = `${f} ➝ ${t}`;
      loadDetail();
  }
  
  async function loadDetail(){
      const loading = document.getElementById('loadingDetail');
      const tbody = document.getElementById('bodyDetail');
      
      loading.classList.remove('hidden');
      tbody.innerHTML = '';
      
      try{
          const pl = {
              type:'detail_migrasi_bucket', 
              closing_date:document.getElementById('closing_date').value,
              harian_date:document.getElementById('harian_date').value,
              kode_kantor:document.getElementById('opt_kantor').value || null,
              from_bucket:modalState.from, to_bucket:modalState.to,
              page:modalState.page, limit:modalState.limit
          };
          
          const r = await apiCall(API_ENDPOINT, {method:'POST', body:JSON.stringify(pl)});
          const j = await r.json();
          const d = j.data.data||[];
          const meta = j.data.pagination;

          if(d.length===0){ 
              tbody.innerHTML = `<tr><td colspan="7" class="p-8 text-center text-gray-400 italic">Tidak ada data nasabah.</td></tr>`;
              document.getElementById('pageInfo').innerText='0 Data'; 
              return; 
          }
          
          let h='';
          d.forEach(x=>{
              h+=`
              <tr class="hover:bg-blue-50 border-b transition">
                <td class="px-4 py-2 font-mono text-gray-600">${x.no_rekening}</td>
                <td class="px-4 py-2 font-medium text-gray-800 truncate max-w-[200px]" title="${x.nama_nasabah}">${x.nama_nasabah}</td>
                <td class="px-4 py-2 text-right text-gray-500 font-mono">${fmt(x.os_m1)}</td>
                <td class="px-4 py-2 text-right font-bold text-green-700 bg-green-50/30 font-mono">${fmt(x.baki_debet)}</td>
                <td class="px-4 py-2 text-center">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-blue-100 text-blue-800">
                        ${x.status_migrasi}
                    </span>
                </td>
                <td class="px-4 py-2 text-center font-mono text-xs">${x.hari_menunggak}</td>
                <td class="px-4 py-2 font-mono text-xs text-gray-500">${x.kode_produk}</td>
              </tr>`;
          });
          tbody.innerHTML=h;
          
          document.getElementById('pageInfo').innerText = `Hal ${modalState.page} / ${meta.total_pages} (${fmt(meta.total_records)} Data)`;
          
          const bPrev = document.getElementById('btnPrev');
          const bNext = document.getElementById('btnNext');
          
          bPrev.onclick = ()=>{ modalState.page--; loadDetail(); };
          bNext.onclick = ()=>{ modalState.page++; loadDetail(); };
          
          bPrev.disabled = modalState.page <= 1;
          bNext.disabled = modalState.page >= meta.total_pages;
          
      } catch(e){ console.error(e); }
      finally { loading.classList.add('hidden'); }
  }
  
  function closeModal(){ document.getElementById('modalDetail').classList.add('hidden'); }
  document.addEventListener('keydown', e => { if(e.key === 'Escape') closeModal(); });
</script>