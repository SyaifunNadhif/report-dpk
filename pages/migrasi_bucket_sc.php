<style>
  /* Custom Scrollbar untuk Table Wrapper */
  .custom-scrollbar::-webkit-scrollbar { height: 8px; width: 8px; }
  .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
  .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
  
  /* Sticky Column Styles */
  .sticky-col { position: sticky; left: 0; z-index: 40; background: white; border-right: 1px solid #e2e8f0; }
  
  /* Animasi Modal */
  @keyframes scaleUp { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
  .animate-scale-up { animation: scaleUp 0.2s ease-out forwards; }
</style>

<div class="max-w-[1920px] mx-auto px-4 py-6 h-screen flex flex-col bg-slate-50 font-sans text-slate-800 overflow-hidden">
  
  <div class="flex-none px-5 py-3 flex flex-col xl:flex-row justify-between items-center gap-4 bg-white border-b border-slate-200 shadow-sm z-20">
    
    <div class="flex items-center gap-4 w-full xl:w-auto">
        <h1 class="text-lg font-bold text-slate-800 flex items-center gap-2 whitespace-nowrap">
            <span class="p-1.5 bg-blue-600 rounded text-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            </span>
            Migrasi (SC)
        </h1>
        
        <div class="h-6 w-px bg-slate-300 hidden md:block"></div>

        <div class="flex flex-wrap items-center gap-2">
            <div class="flex items-center gap-1 bg-slate-100 rounded px-2 py-1 border border-slate-200">
                <input type="date" id="closing_date" class="bg-transparent border-none text-xs font-bold focus:ring-0 p-0 text-slate-600 w-[95px]" title="Tgl Closing">
                <span class="text-slate-400 text-[10px]">➜</span>
                <input type="date" id="harian_date" class="bg-transparent border-none text-xs font-bold focus:ring-0 p-0 text-slate-600 w-[95px]" title="Tgl Harian">
            </div>
            
            <select id="opt_kantor" class="text-xs border border-slate-200 rounded px-2 py-1.5 bg-white focus:ring-1 focus:ring-blue-500 outline-none w-[160px] cursor-pointer hover:bg-slate-50"><option>Loading...</option></select>
            
            <button onclick="fetchMatrix()" class="bg-blue-600 hover:bg-blue-700 text-white p-1.5 rounded transition shadow-sm" title="Refresh Data">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </button>
        </div>
    </div>

    <div id="summaryCheck" class="hidden flex items-center gap-6 ml-auto border-l border-slate-200 pl-6">
        
        <div class="flex flex-col items-end">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Pertumbuhan Kredit</span>
            <div class="flex items-center gap-2" id="growthContainer">
                </div>
        </div>

        <div class="flex flex-col items-end">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Flow SC (0-30) ➜ FE</span>
            <div class="flex items-center gap-2 text-red-600">
                <span class="text-xs font-bold bg-red-100 px-1.5 rounded" id="flowPct">0%</span>
                <span class="text-sm font-bold font-mono" id="flowVal">0</span>
            </div>
        </div>

    </div>
  </div>

  <div class="flex-1 overflow-hidden bg-white relative">
    <div id="loadingMatrix" class="hidden absolute inset-0 bg-white/80 z-50 flex items-center justify-center"><div class="animate-spin rounded-full h-8 w-8 border-4 border-blue-500 border-t-transparent"></div></div>
    
    <div class="h-full overflow-auto custom-scrollbar">
      <table class="w-full text-xs text-center border-separate border-spacing-0 text-slate-600">
        <thead class="bg-slate-50 text-slate-800 sticky top-0 z-30 font-bold shadow-sm">
          <tr>
            <th rowspan="2" class="px-3 py-2 border-b border-r bg-slate-100 sticky left-0 z-40 text-left min-w-[140px] shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">DPD M-1</th>
            <th colspan="2" class="px-2 py-1 border-b border-r bg-slate-100">Posisi M-1</th>
            <th rowspan="2" class="px-2 py-1 border-b border-r min-w-[100px] bg-green-50 text-green-700">0</th>
            <th rowspan="2" class="px-2 py-1 border-b border-r min-w-[100px] bg-yellow-50 text-yellow-700">1 - 7</th>
            <th rowspan="2" class="px-2 py-1 border-b border-r min-w-[100px] bg-yellow-50 text-yellow-700">8 - 14</th>
            <th rowspan="2" class="px-2 py-1 border-b border-r min-w-[100px] bg-yellow-100 text-yellow-800">15 - 21</th>
            <th rowspan="2" class="px-2 py-1 border-b border-r min-w-[100px] bg-orange-50 text-orange-700">22 - 30</th>
            <th rowspan="2" class="px-2 py-1 border-b border-r min-w-[100px] bg-red-50 text-red-700">FE (31-90)</th>
            <th rowspan="2" class="px-2 py-1 border-b border-r min-w-[100px] bg-red-100 text-red-800">BE (>90)</th>
            <th rowspan="2" class="px-2 py-1 border-b border-r min-w-[90px] bg-slate-50">ANGSURAN</th>
            <th rowspan="2" class="px-2 py-1 border-b border-r min-w-[90px] bg-slate-50">PELUNASAN</th>
            <th rowspan="2" class="px-2 py-1 border-b min-w-[100px] bg-slate-100">TOTAL RUN OFF</th>
          </tr>
          <tr class="text-[10px]">
            <th class="px-1 border-b border-r bg-slate-50">NOA</th>
            <th class="px-1 border-b border-r bg-slate-50">OS</th>
          </tr>
        </thead>
        <tbody id="bodyMatrix" class="divide-y divide-slate-100 bg-white"></tbody>
        <tfoot id="footMatrix" class="bg-slate-800 text-white font-bold sticky bottom-0 z-30 shadow-[0_-2px_5px_rgba(0,0,0,0.1)]"></tfoot>
      </table>
    </div>
  </div>
</div>

<div id="modalDetail" class="fixed inset-0 hidden z-[9999] flex items-end md:items-center justify-center sm:p-4">
  <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeModal()"></div>
  <div class="relative bg-white w-full h-[90vh] max-w-7xl rounded-lg shadow-xl flex flex-col overflow-hidden animate-scale-up">
    <div class="flex justify-between items-center px-6 py-4 border-b bg-slate-50">
        <h3 class="font-bold text-slate-700 flex items-center gap-2"><span class="bg-blue-100 text-blue-600 p-1 rounded text-xs">📄</span> Detail Nasabah <span id="badgeMigrasi" class="text-xs bg-slate-200 px-2 rounded-full">...</span></h3>
        <button onclick="closeModal()" class="text-slate-400 hover:text-red-500 text-xl font-bold">&times;</button>
    </div>
    <div class="flex-1 overflow-auto bg-white relative">
        <div id="loadingDetail" class="hidden absolute inset-0 bg-white/80 z-20 flex items-center justify-center"><div class="animate-spin rounded-full h-8 w-8 border-4 border-blue-500 border-t-transparent"></div></div>
        <table class="w-full text-xs text-left text-slate-600">
            <thead class="bg-slate-100 text-slate-700 font-bold sticky top-0 z-10 shadow-sm">
                <tr>
                    <th class="px-4 py-3 border-b">No Rekening</th>
                    <th class="px-4 py-3 border-b">Nama Nasabah</th>
                    <th class="px-4 py-3 text-right border-b">OS M-1</th>
                    <th class="px-4 py-3 text-right border-b bg-green-50 text-green-700">OS Current</th>
                    <th class="px-4 py-3 text-center border-b">Status</th>
                    <th class="px-4 py-3 text-center border-b">Kol</th>
                    <th class="px-4 py-3 text-right border-b">Tgk Pokok</th>
                    <th class="px-4 py-3 text-right border-b">Tgk Bunga</th>
                </tr>
            </thead>
            <tbody id="bodyDetail" class="divide-y divide-slate-100"></tbody>
        </table>
    </div>
    <div class="px-6 py-3 border-t bg-slate-50 flex justify-between items-center">
        <span id="pageInfo" class="text-xs font-medium text-slate-500">0 Data</span>
        <div class="flex gap-2">
            <button id="btnPrev" class="px-3 py-1 bg-white border rounded text-xs hover:bg-slate-100">Prev</button>
            <button id="btnNext" class="px-3 py-1 bg-white border rounded text-xs hover:bg-slate-100">Next</button>
        </div>
    </div>
  </div>
</div>

<script>
// --- KONFIGURASI ---
const API_ENDPOINT = './api/bucket_fe/'; 
const API_DATE = './api/date/'; 
const fmt = n => new Intl.NumberFormat('id-ID').format(Number(n||0));
const apiCall = (u,o) => window.apiFetch ? window.apiFetch(u,o) : fetch(u,o);
const BUCKETS = ['0','1-7','8-14','15-21','22-30','FE','BE'];
let modalState = {from:'', to:'', page:1, limit:50};

// --- INIT ---
window.addEventListener('DOMContentLoaded', async () => {
    const user = (window.getUser && window.getUser()) || null;
    const uKode = (user?.kode ? String(user.kode).padStart(3,'0') : '000');
    
    await populateKantor(uKode);
    
    const d = await getLastHarianData();
    if(d){ 
        document.getElementById('closing_date').value = d.last_closing; 
        document.getElementById('harian_date').value = d.last_created; 
    }
    
    fetchMatrix();
});

// --- HELPER FUNCTION ---
async function getLastHarianData(){ 
    try{ const r = await apiCall(API_DATE); const j = await r.json(); return j.data; }
    catch{ return null; }
}

async function populateKantor(uKode){
    const el = document.getElementById('opt_kantor');
    if(uKode !== '000'){
        el.innerHTML = `<option value="${uKode}">CABANG ${uKode}</option>`; 
        el.disabled = true; 
        return;
    }
    try{ 
        const r = await apiCall('./api/kode/',{method:'POST',body:JSON.stringify({type:'kode_kantor'})}); 
        const j = await r.json();
        let h = '<option value="">KONSOLIDASI (SEMUA)</option>'; 
        (j.data||[]).filter(x => x.kode_kantor !== '000')
            .sort((a,b) => a.kode_kantor.localeCompare(b.kode_kantor))
            .forEach(x => h += `<option value="${x.kode_kantor}">${x.kode_kantor} - ${x.nama_kantor}</option>`); 
        el.innerHTML = h; 
    } catch{}
}

// --- CORE LOGIC ---
async function fetchMatrix(){
    const l = document.getElementById('loadingMatrix'); 
    l.classList.remove('hidden');
    try{
        const pl = { 
            type: "rekap_migrasi_bucket", 
            closing_date: document.getElementById('closing_date').value, 
            harian_date: document.getElementById('harian_date').value, 
            kode_kantor: document.getElementById('opt_kantor').value || null 
        };
        const r = await apiCall(API_ENDPOINT, { method:'POST', body:JSON.stringify(pl) }); 
        const j = await r.json();
        renderMatrix(j.data);
    } catch(e){ console.error(e); } 
    finally{ l.classList.add('hidden'); }
}

function renderMatrix(data){
    const GT = data.grand_total;
    const real = data.realisasi;
    
    // --- 1. HITUNG GROWTH (Actual - M1) ---
    const osM1 = parseFloat(GT.m1.os || 0);
    // Hitung total OS Current dari buckets (Assuming API GT buckets is aggregated)
    let totalOsCurrent = 0;
    Object.values(GT.buckets).forEach(b => totalOsCurrent += parseFloat(b.os || 0));
    // Tambah realisasi (Jaga-jaga jika belum masuk bucket 0)
    const osCurr = totalOsCurrent + parseFloat(real.os || 0);

    const diffVal = osCurr - osM1;
    const diffPct = osM1 > 0 ? (diffVal / osM1) * 100 : 0;
    
    // --- 2. HITUNG FLOW PEMBURUKAN (0-30 -> FE/BE) ---
    const srcBuckets = ['0', '1-7', '8-14', '15-21', '22-30'];
    const badBuckets = ['FE', 'BE'];

    let flowBadOS = 0;
    let totalSrcM1 = 0;

    // Denominator: Total OS M-1 pada bucket 0-30
    srcBuckets.forEach(b => {
        if(data.summary_m1[b]) totalSrcM1 += parseFloat(data.summary_m1[b].os_m1 || 0);
    });

    // Numerator: Jumlah yang pindah ke FE/BE
    srcBuckets.forEach(src => {
        badBuckets.forEach(tgt => {
            if(data.matrix[src] && data.matrix[src][tgt]){
                flowBadOS += parseFloat(data.matrix[src][tgt].os || 0);
            }
        });
    });

    const flowBadPct = totalSrcM1 > 0 ? (flowBadOS / totalSrcM1) * 100 : 0;

    // --- RENDER HEADER SUMMARY ---
    document.getElementById('summaryCheck').classList.remove('hidden');

    // Render Growth
    const isUp = diffVal >= 0;
    const colorGrowth = isUp ? 'text-green-600' : 'text-red-600';
    const iconGrowth = isUp ? '▲' : '▼';
    const bgGrowth = isUp ? 'bg-green-100' : 'bg-red-100';
    
    document.getElementById('growthContainer').innerHTML = `
        <span class="text-xs font-bold ${bgGrowth} ${colorGrowth} px-1.5 rounded">${iconGrowth} ${Math.abs(diffPct).toFixed(2)}%</span>
        <span class="text-sm font-bold font-mono ${colorGrowth}">${fmt(diffVal)}</span>
    `;

    // Render Flow Pemburukan
    document.getElementById('flowPct').innerText = flowBadPct.toFixed(2) + '%';
    document.getElementById('flowVal').innerText = fmt(flowBadOS);


    // --- RENDER TABEL ---
    const tb = document.getElementById('bodyMatrix'); 
    let h = '';
    
    // Baris Realisasi
    h += `<tr class="bg-green-50/50 hover:bg-green-50 transition border-b"><td class="sticky-col px-3 py-2 text-left font-bold text-green-700 bg-green-50/20 border-r text-[11px] shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">REALISASI BARU</td><td class="border-r">-</td><td class="border-r">-</td><td class="border-r p-0"><div class="cursor-pointer hover:bg-green-100 py-3" onclick="openDetail('REALISASI','0')"><div class="font-bold text-blue-600 text-[10px]">${fmt(real.noa)}</div><div class="font-bold text-slate-800 text-[11px]">${fmt(real.os)}</div><div class="text-[9px] font-bold text-green-600 mt-0.5">100%</div></div></td><td colspan="6" class="text-[10px] italic text-slate-400 border-r">Detail Tersebar</td><td class="border-r">-</td><td class="border-r">-</td><td>-</td></tr>`;

    // Loop Matrix Bucket
    BUCKETS.forEach((f, i) => {
        const m1 = data.summary_m1[f];
        h += `<tr class="hover:bg-slate-50 transition border-b h-[50px]"><td class="sticky-col px-3 text-left font-semibold text-slate-700 border-r text-[11px] shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">${f}</td><td class="border-r text-right px-2 font-mono text-[10px] font-bold text-slate-500 bg-slate-50">${fmt(m1.noa_m1)}</td><td class="border-r text-right px-2 font-mono font-bold text-slate-800 text-[11px] bg-slate-50">${fmt(m1.os_m1)}</td>`;
        
        let ar = 0;
        BUCKETS.forEach((t, j) => {
            const c = data.matrix[f][t]; 
            ar += parseFloat(c.angsuran || 0);
            
            // Logic Warna & Persentase Cell
            let bgClass = ''; 
            let textClass = 'text-slate-800';
            let pctVal = '0%';

            if(c.os > 0 && m1.os_m1 > 0){
                pctVal = ((c.os / m1.os_m1) * 100).toFixed(1) + '%';
                if (j > i) { 
                    bgClass = 'bg-red-100 border-red-200'; textClass = 'text-red-800'; // Pemburukan
                } else if (j < i) {
                    bgClass = 'bg-green-50'; textClass = 'text-green-800'; // Perbaikan
                } else {
                    bgClass = 'bg-blue-50'; textClass = 'text-blue-800'; // Tetap
                }
            }

            let clickEv = (c.os > 0) ? `onclick="openDetail('${f}','${t}')"` : '';
            let cursor = (c.os > 0) ? 'cursor-pointer hover:brightness-95' : '';

            h += `<td class="border-r p-0 ${bgClass}"><div class="h-full flex flex-col justify-center py-2 ${cursor}" ${clickEv}>
                    <span class="text-[10px] font-bold text-slate-500">${fmt(c.noa)}</span>
                    <span class="text-[11px] font-bold ${textClass}">${fmt(c.os)}</span>
                    ${c.os > 0 ? `<span class="text-[9px] font-bold ${textClass} opacity-80 mt-0.5">${pctVal}</span>` : ''}
                </div></td>`;
        });
        
        const l = data.matrix[f]['O'];
        h += `<td class="border-r bg-slate-50"><div class="font-mono font-bold text-green-700 text-[11px]">${fmt(ar)}</div></td><td class="border-r bg-slate-50 p-0"><div class="h-full flex flex-col justify-center cursor-pointer hover:bg-blue-100" onclick="openDetail('${f}','O')"><span class="text-[10px] font-bold text-blue-600">${fmt(l.noa)}</span><span class="text-[11px] font-bold text-slate-800">${fmt(l.pelunasan)}</span></div></td><td class="bg-slate-100 font-mono font-bold text-slate-900 text-[11px]">${fmt(ar + l.pelunasan)}</td></tr>`;
    });
    tb.innerHTML = h;

    // Footer
    let tf = `<tr class="h-[50px] text-[11px]"><td class="sticky-col px-3 text-left bg-slate-800 text-white border-r border-slate-600">GRAND TOTAL</td><td class="bg-slate-800 text-white font-mono font-bold text-right px-2 border-r border-slate-600">${fmt(GT.m1.noa)}</td><td class="bg-slate-800 text-white font-mono font-bold text-right px-2 border-r border-slate-600 text-xs">${fmt(GT.m1.os)}</td>`;
    BUCKETS.forEach(b => { tf += `<td class="bg-slate-800 border-r border-slate-600 p-0"><div class="flex flex-col justify-center h-full"><span class="text-[10px] font-bold text-blue-300">${fmt(GT.buckets[b].noa)}</span><span class="text-[11px] font-bold text-white">${fmt(GT.buckets[b].os)}</span></div></td>` });
    tf += `<td class="bg-slate-800 text-green-400 font-mono font-bold text-right px-2 border-r border-slate-600">${fmt(GT.angsuran)}</td><td class="bg-slate-800 border-r border-slate-600 p-0"><div class="flex flex-col justify-center h-full"><span class="text-[10px] font-bold text-blue-300">${fmt(GT.lunas.noa)}</span><span class="text-[11px] font-bold text-white">${fmt(GT.lunas.os)}</span></div></td><td class="bg-slate-900 text-white font-mono font-bold text-right px-2 shadow-inner">${fmt(GT.runoff_total.os)}</td></tr>`;
    document.getElementById('footMatrix').innerHTML = tf;
}

// --- MODAL DETAIL ---
function openDetail(f,t){ modalState={from:f,to:t,page:1,limit:50}; document.getElementById('modalDetail').classList.remove('hidden'); document.getElementById('badgeMigrasi').innerText=`${f} ➝ ${t}`; loadDetail(); }

async function loadDetail(){
    const l=document.getElementById('loadingDetail'); const tb=document.getElementById('bodyDetail'); l.classList.remove('hidden'); tb.innerHTML='';
    try{
        const pl={type:'detail_migrasi_bucket',closing_date:document.getElementById('closing_date').value,harian_date:document.getElementById('harian_date').value,kode_kantor:document.getElementById('opt_kantor').value||null,from_bucket:modalState.from,to_bucket:modalState.to,page:modalState.page,limit:modalState.limit};
        const r=await apiCall(API_ENDPOINT,{method:'POST',body:JSON.stringify(pl)}); const j=await r.json(); const d=j.data.data||[]; const m=j.data.pagination;
        
        if(d.length===0){tb.innerHTML='<tr><td colspan="8" class="p-8 text-center text-slate-400 italic">Tidak ada data.</td></tr>'; document.getElementById('pageInfo').innerText='0 Data'; return;}
        
        let h=''; 
        d.forEach(x=>{
            h+=`<tr class="hover:bg-blue-50 border-b transition text-[11px]"><td class="px-4 py-2 font-mono text-slate-600">${x.no_rekening}</td><td class="px-4 py-2 font-medium text-slate-800 truncate max-w-[200px]" title="${x.nama_nasabah}">${x.nama_nasabah}</td><td class="px-4 py-2 text-right font-mono text-slate-500">${fmt(x.os_m1)}</td><td class="px-4 py-2 text-right font-mono font-bold text-green-700 bg-green-50/30">${fmt(x.baki_debet)}</td><td class="px-4 py-2 text-center"><span class="bg-blue-50 text-blue-700 px-2 py-0.5 rounded text-[10px] font-medium border border-blue-100">${x.status_migrasi}</span></td><td class="px-4 py-2 text-center font-bold text-slate-600">${x.kolektibilitas||'-'}</td><td class="px-4 py-2 text-right font-mono text-red-600">${fmt(x.tunggakan_pokok)}</td><td class="px-4 py-2 text-right font-mono text-orange-600">${fmt(x.tunggakan_bunga)}</td></tr>`;
        }); 
        tb.innerHTML=h; 
        document.getElementById('pageInfo').innerText=`Hal ${modalState.page}/${m.total_pages} (${fmt(m.total_records)})`;
        
        const p=document.getElementById('btnPrev'); const n=document.getElementById('btnNext');
        p.onclick=()=>{modalState.page--;loadDetail()}; n.onclick=()=>{modalState.page++;loadDetail()};
        p.disabled=modalState.page<=1; n.disabled=modalState.page>=m.total_pages;
    } catch(e){console.error(e);} 
    finally{l.classList.add('hidden');}
}

function closeModal(){document.getElementById('modalDetail').classList.add('hidden');}
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeModal();});
</script>