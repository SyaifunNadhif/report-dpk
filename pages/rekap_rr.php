<style>
  .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
  .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
  .no-scrollbar::-webkit-scrollbar { display: none; }
  .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

  /* STICKY TABLE SETTINGS */
  #tabelRR thead th { position: sticky; box-shadow: inset 0 -1px 0 #cbd5e1; }
  #tabelRR thead tr:nth-child(1) th { top: 0; z-index: 40; height: 38px; }
  #tabelRR thead tr:nth-child(2) th { top: 38px; z-index: 39; height: 36px; }
  #tabelRR thead tr:nth-child(3) th { top: 74px; z-index: 38; height: 42px; background-color: #dbeafe !important; border-bottom: 2px solid #bfdbfe; box-shadow: inset 0 -1px 0 #93c5fd; }

  .sticky-left-1 { position: sticky; left: 0; z-index: 20; background: white; box-shadow: inset -1px 0 0 #e2e8f0; }
  .sticky-left-2 { position: sticky; left: 0; z-index: 20; background: white; box-shadow: inset -1px 0 0 #e2e8f0; }
  @media (min-width: 768px) { .sticky-left-2 { left: 60px; } }

  #tabelRR thead tr:nth-child(1) th.sticky-left-1, #tabelRR thead tr:nth-child(2) th.sticky-left-1 { z-index: 50; background-color: #f8fafc; box-shadow: inset -1px -1px 0 #cbd5e1; }
  #tabelRR thead tr:nth-child(1) th.sticky-left-2, #tabelRR thead tr:nth-child(2) th.sticky-left-2 { z-index: 49; background-color: #f8fafc; box-shadow: inset -1px -1px 0 #cbd5e1; }
  #tabelRR thead tr:nth-child(3) th.sticky-left-1 { z-index: 48; background-color: #bfdbfe !important; box-shadow: inset -1px -2px 0 #93c5fd; }
  #tabelRR thead tr:nth-child(3) th.sticky-left-2 { z-index: 47; background-color: #bfdbfe !important; box-shadow: inset -1px -2px 0 #93c5fd; }

  #bodyRekap tr:hover td { background-color: #eff6ff !important; cursor: pointer; }
  #bodyRekap tr:hover td.sticky-left-1, #bodyRekap tr:hover td.sticky-left-2 { background-color: #eff6ff !important; }
</style>

<script>
    window.currentUser = { kode_kantor: (typeof USER_KODE_KANTOR !== 'undefined') ? USER_KODE_KANTOR : '000' };
</script>

<div class="max-w-[1920px] mx-auto px-2 md:px-4 py-3 md:py-5 h-[calc(100vh-80px)] flex flex-col bg-slate-50 font-sans text-slate-800 overflow-hidden">
  
  <div class="flex-none mb-3 flex flex-col xl:flex-row justify-between xl:items-end gap-3 w-full">
      <div class="flex flex-col gap-2 shrink-0">
          <h1 class="text-xl md:text-2xl font-bold text-slate-800 flex items-center gap-2">
              <span class="p-1.5 md:p-2 bg-blue-600 rounded-lg text-white shadow-sm">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
              </span>
              Rekap Repayment Rate (RR)
          </h1>
          
          <div id="summaryPills" class="hidden flex flex-wrap items-center gap-2">
              <div class="flex flex-col bg-white border border-slate-200 px-3 py-1 rounded-lg shadow-sm min-w-[120px]">
                  <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">RR M-1 (Lancar)</span>
                  <div class="flex items-end gap-1.5">
                      <span class="text-sm font-bold text-slate-800" id="sum_m1_noa">0</span>
                      <span class="text-[10px] font-mono text-slate-400 mb-0.5" id="sum_m1_os">0</span>
                  </div>
              </div>
              <div class="flex flex-col bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-lg shadow-sm min-w-[120px]">
                  <span class="text-[9px] font-bold text-emerald-700 uppercase tracking-widest">RR ACTUAL (Lancar)</span>
                  <div class="flex items-end gap-1.5">
                      <span class="text-sm font-bold text-emerald-800" id="sum_cur_noa">0</span>
                      <span class="text-[10px] font-mono text-emerald-600 mb-0.5" id="sum_cur_os">0</span>
                  </div>
              </div>
              <div class="flex flex-col bg-blue-50 border border-blue-200 px-3 py-1 rounded-lg shadow-sm min-w-[110px]">
                  <span class="text-[9px] font-bold text-blue-700 uppercase tracking-widest">% Capaian OS</span>
                  <div class="flex items-end gap-1.5">
                      <span class="text-sm font-bold text-blue-800" id="sum_pct_os">0%</span>
                  </div>
              </div>
              <div class="flex flex-col bg-rose-50 border border-rose-200 px-3 py-1 rounded-lg shadow-sm min-w-[110px]" id="box_delta">
                  <span class="text-[9px] font-bold text-rose-700 uppercase tracking-widest">DELTA %</span>
                  <div class="flex items-end gap-1.5">
                      <span class="text-sm font-bold text-rose-800" id="sum_delta_pct">0%</span>
                  </div>
              </div>
          </div>
      </div>

      <form id="formFilter" class="bg-white p-2 md:p-2.5 rounded-xl border border-slate-200 shadow-sm flex flex-nowrap items-center gap-1.5 md:gap-3 w-full xl:w-auto shrink-0 overflow-x-auto no-scrollbar" onsubmit="event.preventDefault(); fetchRekap();">
          
          <div class="flex flex-col w-[110px] md:w-[130px] shrink-0">
              <label class="text-[8px] md:text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1 truncate">CLOSING (M-1)</label>
              <input type="date" id="closing_date" class="border border-slate-200 rounded-md md:rounded-lg px-2 md:px-3 py-1.5 md:py-2 text-[10px] md:text-xs text-slate-700 outline-none focus:border-blue-500 transition w-full" required>
          </div>
          
          <div class="flex flex-col w-[110px] md:w-[130px] shrink-0">
              <label class="text-[8px] md:text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1 truncate">ACTUAL (HARIAN)</label>
              <input type="date" id="harian_date" class="border border-slate-200 rounded-md md:rounded-lg px-2 md:px-3 py-1.5 md:py-2 text-[10px] md:text-xs text-slate-700 outline-none focus:border-blue-500 transition w-full" required>
          </div>
          
          <div class="flex items-center gap-1 md:gap-1.5 shrink-0 h-[28px] md:h-[34px] mb-px mt-3.5">
              <button type="submit" class="h-full w-[34px] md:w-auto md:px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-md md:rounded-lg flex items-center justify-center transition shadow-sm font-bold text-[10px] md:text-xs uppercase tracking-wider" title="Cari Data">
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" class="md:mr-1.5"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                  <span class="hidden md:inline">CARI</span>
              </button>
              <button type="button" onclick="exportExcelRekap()" class="h-full w-[34px] md:w-auto md:px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md md:rounded-lg flex items-center justify-center transition shadow-sm font-bold text-[10px] md:text-xs uppercase tracking-wider" title="Download Excel">
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                  <span class="hidden md:inline ml-1.5">EXCEL</span>
              </button>
          </div>
      </form>
  </div>

  <div class="flex-1 overflow-hidden bg-white rounded-xl shadow-sm border border-slate-200 relative">
    <div id="loadingRekap" class="hidden absolute inset-0 bg-white/80 z-[100] flex flex-col items-center justify-center text-blue-600 backdrop-blur-sm">
        <div class="animate-spin rounded-full h-8 w-8 border-4 border-blue-500 border-t-transparent mb-2"></div>
        <span class="text-xs font-bold uppercase tracking-widest">Memuat Data RR...</span>
    </div>
    
    <div class="h-full overflow-auto custom-scrollbar relative">
      <table class="w-max min-w-full text-xs text-center border-separate border-spacing-0 text-slate-700" id="tabelRR">
        <thead class="tracking-wider bg-slate-50 text-slate-600 font-bold uppercase" id="headRR">
          </thead>
        <tbody id="bodyRekap" class="divide-y divide-slate-100 bg-white text-[10px] md:text-[11px]"></tbody>
      </table>
    </div>
  </div>

</div>

<script>
  const API_URL  = './api/rr/'; 
  const API_DATE = './api/date/';
  const nf = new Intl.NumberFormat('id-ID');
  const fmt = n => nf.format(Math.round(Number(n||0)));

  let abortRekap;
  let rekapDataCache = null; 
  let userKodeGlobal = '000'; 

  window.addEventListener('DOMContentLoaded', async () => {
      const user = (window.getUser && window.getUser()) || null;
      userKodeGlobal = (user?.kode ? String(user.kode).padStart(3,'0') : '000');

      setupHeaderRR(userKodeGlobal);

      const now = new Date();
      try {
          const r = await fetch(API_DATE); const j = await r.json();
          const d = j.data || null;
          if (d) {
              document.getElementById('closing_date').value = d.last_closing;
              document.getElementById('harian_date').value = d.last_created;
          } else {
              document.getElementById('closing_date').value = `${now.getFullYear() - 1}-12-31`;
              document.getElementById('harian_date').value = now.toISOString().split('T')[0];
          }
      } catch(e) { 
          document.getElementById('closing_date').value = `${now.getFullYear() - 1}-12-31`;
          document.getElementById('harian_date').value = now.toISOString().split('T')[0]; 
      }

      fetchRekap();
  });

  async function apiCall(url, payload, signal = null) {
      const opt = { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) };
      if (signal) opt.signal = signal;
      const res = await fetch(url, opt);
      return await res.json();
  }

  // Set Header Sesuai Format Excel (Baki Debet | NOA | %)
  function setupHeaderRR(userKode) {
      const th = document.getElementById('headRR');
      let thHtml = `<tr>`;

      if (userKode === '000') {
          thHtml += `
            <th rowspan="2" class="sticky-left-1 w-[60px] border-r border-b border-slate-200 align-middle bg-slate-50 text-center" id="lblKode">KODE</th>
            <th rowspan="2" class="sticky-left-2 min-w-[150px] md:min-w-[180px] border-r border-b border-slate-200 align-middle text-left pl-4 bg-slate-50" id="lblNama">NAMA KANTOR</th>
          `;
      } else {
          thHtml += `
            <th rowspan="2" class="sticky-left-1 min-w-[150px] md:min-w-[180px] border-r border-b border-slate-200 align-middle text-left pl-4 bg-slate-50" id="lblNama">NAMA KANKAS</th>
          `;
      }

      thHtml += `
            <th colspan="3" class="px-3 py-2 border-r border-b border-slate-300 align-middle bg-[#a8d08d] text-slate-800 text-[10px] md:text-[11px]">M-1</th>
            <th colspan="3" class="px-3 py-2 border-r border-b border-slate-300 align-middle bg-[#a8d08d] text-slate-800 text-[10px] md:text-[11px]">ACTUAL (REALISASI BARU)</th>
            <th colspan="3" class="px-3 py-2 border-b border-slate-300 align-middle bg-[#a8d08d] text-slate-800 text-[10px] md:text-[11px]">DELTA</th>
          </tr>
          <tr>
            <th class="px-3 py-1.5 border-r border-b border-slate-200 bg-slate-50">BAKI DEBET</th>
            <th class="px-3 py-1.5 border-r border-b border-slate-200 bg-slate-50">NOA</th>
            <th class="px-3 py-1.5 border-r border-b border-slate-200 bg-slate-50">%</th>
            
            <th class="px-3 py-1.5 border-r border-b border-slate-200 bg-slate-50">BAKI DEBET</th>
            <th class="px-3 py-1.5 border-r border-b border-slate-200 bg-slate-50">NOA</th>
            <th class="px-3 py-1.5 border-r border-b border-slate-200 bg-slate-50">%</th>

            <th class="px-3 py-1.5 border-r border-b border-slate-200 bg-slate-50">BAKI DEBET</th>
            <th class="px-3 py-1.5 border-r border-b border-slate-200 bg-slate-50">NOA</th>
            <th class="px-3 py-1.5 border-b border-slate-200 bg-slate-50">%</th>
          </tr>
          <tr id="rowTotalRRAtas"></tr>
      `;
      th.innerHTML = thHtml;
  }

  async function fetchRekap() {
      const l = document.getElementById('loadingRekap');
      const tb = document.getElementById('bodyRekap');
      const trTot = document.getElementById('rowTotalRRAtas');
      const pills = document.getElementById('summaryPills');
      
      if(abortRekap) abortRekap.abort();
      abortRekap = new AbortController();

      l.classList.remove('hidden'); pills.classList.add('hidden');
      
      const colSpan = userKodeGlobal === '000' ? 11 : 10;
      tb.innerHTML = `<tr><td colspan="${colSpan}" class="text-center py-16 text-slate-400 italic">...</td></tr>`;
      trTot.innerHTML = '';
      rekapDataCache = null;

      try {
          const payload = {
              type: 'rr',
              closing_date: document.getElementById('closing_date').value,
              harian_date: document.getElementById('harian_date').value,
              kode_kantor: userKodeGlobal 
          };

          const json = await apiCall(API_URL, payload, abortRekap.signal);
          
          if(json.status !== 200) throw new Error(json.message);

          const rows = json.data?.data || [];
          const meta = json.data?.meta || {};
          const gt = json.data?.grand_total || {};

          if(document.getElementById('lblKode')) document.getElementById('lblKode').innerText = meta.label_kode || 'KODE';
          if(document.getElementById('lblNama')) document.getElementById('lblNama').innerText = meta.label_nama || 'NAMA KANTOR';

          if(rows.length === 0) {
              tb.innerHTML = `<tr><td colspan="${colSpan}" class="text-center py-12 text-slate-400 italic">Tidak ada data.</td></tr>`;
              return;
          }
          rekapDataCache = rows; 

          let html = '';
          rows.forEach(r => {
              // Styling Delta & Persen
              const dNoaClass = r.delta_noa < 0 ? 'text-rose-600' : 'text-slate-700';
              const dOsClass  = r.delta_os < 0 ? 'text-rose-600' : 'text-slate-700';
              const dPctClass = r.delta_pct < 0 ? 'text-rose-600' : 'text-slate-700';
              
              const pM1Class  = r.m1_pct >= 90 ? 'text-emerald-700' : 'text-orange-600';
              const pCurClass = r.cur_pct >= 90 ? 'text-emerald-700' : 'text-orange-600';

              let rowHtml = `<tr class="transition h-[42px] border-b border-slate-100">`;
              
              if (userKodeGlobal === '000') {
                  rowHtml += `
                    <td class="sticky-left-1 px-3 py-1.5 border-r border-slate-100 font-mono text-slate-500 z-20 shadow-[inset_-1px_0_0_#e2e8f0]">${r.kode}</td>
                    <td class="sticky-left-2 px-4 py-1.5 border-r border-slate-100 font-semibold text-slate-700 text-left truncate z-20 shadow-[inset_-1px_0_0_#e2e8f0]" title="${r.nama}">${r.nama}</td>
                  `;
              } else {
                  rowHtml += `
                    <td class="sticky-left-1 px-4 py-1.5 border-r border-slate-100 font-semibold text-slate-700 text-left truncate z-20 shadow-[inset_-1px_0_0_#e2e8f0]" title="${r.nama}">${r.nama}</td>
                  `;
              }

              // MAPPING: M-1 Lancar, Actual Lancar
              rowHtml += `
                    <td class="px-3 py-1.5 border-r border-slate-100 text-right font-mono text-slate-600">${fmt(r.m1_lancar_os)}</td>
                    <td class="px-3 py-1.5 border-r border-slate-100 text-center font-bold text-slate-700">${fmt(r.m1_lancar_noa)}</td>
                    <td class="px-3 py-1.5 border-r border-slate-100 text-center font-bold ${pM1Class}">${r.m1_pct}%</td>
                    
                    <td class="px-3 py-1.5 border-r border-emerald-100 text-right font-mono text-emerald-800 bg-emerald-50/30">${fmt(r.cur_lancar_os)}</td>
                    <td class="px-3 py-1.5 border-r border-emerald-100 text-center font-bold text-emerald-800 bg-emerald-50/30">${fmt(r.cur_lancar_noa)}</td>
                    <td class="px-3 py-1.5 border-r border-emerald-100 text-center font-bold ${pCurClass} bg-emerald-50/30">${r.cur_pct}%</td>
                    
                    <td class="px-3 py-1.5 border-r border-rose-100 text-right font-mono font-bold ${dOsClass} bg-rose-50/30">${fmt(r.delta_os)}</td>
                    <td class="px-3 py-1.5 border-r border-rose-100 text-center font-bold ${dNoaClass} bg-rose-50/30">${fmt(r.delta_noa)}</td>
                    <td class="px-3 py-1.5 text-center font-bold ${dPctClass} bg-rose-50/30">${r.delta_pct}%</td>
                </tr>`;
              html += rowHtml;
          });
          tb.innerHTML = html;

          // Cek Warna Grand Total
          const gtDNoaClass = gt.delta_noa < 0 ? 'text-rose-700' : 'text-blue-900';
          const gtDOsClass  = gt.delta_os < 0 ? 'text-rose-700' : 'text-blue-900';
          const gtDPctClass = gt.delta_pct < 0 ? 'text-rose-700' : 'text-blue-900';

          // Inject Grand Total ke Bawah Thead
          let gtHtml = '';
          if (userKodeGlobal === '000') {
              gtHtml += `
                  <th class="sticky-left-1 px-3 border-r border-blue-200 text-center text-blue-900">-</th>
                  <th class="sticky-left-2 px-4 border-r border-blue-200 text-left text-blue-900 uppercase tracking-widest font-bold">GRAND TOTAL</th>
              `;
          } else {
              gtHtml += `
                  <th class="sticky-left-1 px-4 border-r border-blue-200 text-left text-blue-900 uppercase tracking-widest font-bold">TOTAL CABANG</th>
              `;
          }

          gtHtml += `
              <th class="px-3 border-r border-blue-200 text-right align-middle font-mono font-bold text-[11px] md:text-xs text-blue-900">${fmt(gt.m1_lancar_os)}</th>
              <th class="px-3 border-r border-blue-200 text-center align-middle font-bold text-[11px] md:text-xs text-blue-900">${fmt(gt.m1_lancar_noa)}</th>
              <th class="px-3 border-r border-blue-200 text-center align-middle font-bold text-[11px] md:text-xs text-blue-900">${gt.m1_pct}%</th>
              
              <th class="px-3 border-r border-blue-200 text-right align-middle font-mono font-bold text-[11px] md:text-xs text-emerald-800 bg-emerald-100/50">${fmt(gt.cur_lancar_os)}</th>
              <th class="px-3 border-r border-blue-200 text-center align-middle font-bold text-[11px] md:text-xs text-emerald-800 bg-emerald-100/50">${fmt(gt.cur_lancar_noa)}</th>
              <th class="px-3 border-r border-blue-200 text-center align-middle font-bold text-[11px] md:text-xs text-blue-900 bg-emerald-100/50">${gt.cur_pct}%</th>
              
              <th class="px-3 border-r border-blue-200 text-right align-middle font-mono font-bold text-[11px] md:text-xs ${gtDOsClass}">${fmt(gt.delta_os)}</th>
              <th class="px-3 border-r border-blue-200 text-center align-middle font-bold text-[11px] md:text-xs ${gtDNoaClass}">${fmt(gt.delta_noa)}</th>
              <th class="px-3 text-center align-middle font-bold text-[11px] md:text-xs ${gtDPctClass}">${gt.delta_pct}%</th>
          `;
          trTot.innerHTML = gtHtml;

          // Update Summary Pills
          document.getElementById('sum_m1_noa').innerText  = fmt(gt.m1_lancar_noa);
          document.getElementById('sum_m1_os').innerText   = 'Rp ' + fmt(gt.m1_lancar_os);
          
          document.getElementById('sum_cur_noa').innerText = fmt(gt.cur_lancar_noa);
          document.getElementById('sum_cur_os').innerText  = 'Rp ' + fmt(gt.cur_lancar_os);
          
          document.getElementById('sum_pct_os').innerText  = gt.cur_pct + '%';
          
          const pillDelta = document.getElementById('sum_delta_pct');
          pillDelta.innerText = gt.delta_pct + '%';
          
          const boxDelta = document.getElementById('box_delta');
          if(gt.delta_pct < 0) {
              boxDelta.className = "flex flex-col bg-rose-50 border border-rose-200 px-3 py-1 rounded-lg shadow-sm min-w-[110px]";
              pillDelta.className = "text-sm font-bold text-rose-800";
              pillDelta.previousElementSibling.className = "text-[9px] font-bold text-rose-700 uppercase tracking-widest";
          } else {
              boxDelta.className = "flex flex-col bg-blue-50 border border-blue-200 px-3 py-1 rounded-lg shadow-sm min-w-[110px]";
              pillDelta.className = "text-sm font-bold text-blue-800";
              pillDelta.previousElementSibling.className = "text-[9px] font-bold text-blue-700 uppercase tracking-widest";
          }

          pills.classList.remove('hidden');

      } catch(e) { if(e.name!=='AbortError') console.error(e); } finally { l.classList.add('hidden'); }
  }

  // --- EXPORT EXCEL REKAP UTAMA ---
  window.exportExcelRekap = function() {
      if(!rekapDataCache || rekapDataCache.length === 0) return alert("Tidak ada data rekap untuk didownload.");

      const lblKode = document.getElementById('lblKode') ? document.getElementById('lblKode').innerText : 'Kode';
      const lblNama = document.getElementById('lblNama') ? document.getElementById('lblNama').innerText : 'Nama';

      let csv = "";
      if (userKodeGlobal === '000') {
          csv = `${lblKode}\t${lblNama}\tM-1 Baki Debet\tM-1 NOA\tM-1 %\tActual Baki Debet\tActual NOA\tActual %\tDelta Baki Debet\tDelta NOA\tDelta %\n`;
      } else {
          csv = `${lblNama}\tM-1 Baki Debet\tM-1 NOA\tM-1 %\tActual Baki Debet\tActual NOA\tActual %\tDelta Baki Debet\tDelta NOA\tDelta %\n`;
      }
      
      rekapDataCache.forEach(r => {
          if (userKodeGlobal === '000') {
              csv += `'${r.kode}\t${r.nama||''}\t`;
          } else {
              csv += `${r.nama||''}\t`;
          }
          csv += `${Math.round(r.m1_lancar_os)}\t${r.m1_lancar_noa}\t${r.m1_pct}%\t${Math.round(r.cur_lancar_os)}\t${r.cur_lancar_noa}\t${r.cur_pct}%\t${Math.round(r.delta_os)}\t${r.delta_noa}\t${r.delta_pct}%\n`;
      });

      const blob = new Blob([csv], { type: 'application/vnd.ms-excel' });
      const a = document.createElement('a');
      a.href = window.URL.createObjectURL(blob);
      a.download = `Rekap_RR_${userKodeGlobal}_${document.getElementById("harian_date").value}.xls`; 
      a.click();
  }

</script>