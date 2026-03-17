<style>
  .custom-scrollbar::-webkit-scrollbar { height: 8px; width: 8px; }
  .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
  .no-scrollbar::-webkit-scrollbar { display: none; }
  .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

  /* STICKY TABLE SETTINGS - Disesuaikan untuk Font Lebih Besar */
  #tabelRR thead th { position: sticky; box-shadow: inset 0 -1px 0 #cbd5e1; }
  #tabelRR thead tr:nth-child(1) th { top: 0; z-index: 40; height: 46px; }
  #tabelRR thead tr:nth-child(2) th { top: 46px; z-index: 39; height: 42px; }
  /* Row ke-3 (Grand Total) */
  #tabelRR thead tr:nth-child(3) th { 
      top: 88px; z-index: 38; height: 50px; 
      background-color: #eff6ff !important; /* Biru sangat muda */
      border-bottom: 2px solid #bfdbfe; 
      box-shadow: inset 0 -1px 0 #93c5fd; 
  }

  /* Kolom Kiri Sticky */
  .sticky-left-1 { position: sticky; left: 0; z-index: 20; background: white; box-shadow: inset -1px 0 0 #e2e8f0; }
  .sticky-left-2 { position: sticky; left: 0; z-index: 20; background: white; box-shadow: inset -1px 0 0 #e2e8f0; }
  @media (min-width: 768px) { .sticky-left-2 { left: 80px; } }

  /* 🔥 FIX WARNA IJO: Pertemuan Sticky Atas & Kiri dipaksa Ijo biar ga putih */
  #tabelRR thead tr:nth-child(1) th.sticky-left-1, #tabelRR thead tr:nth-child(2) th.sticky-left-1 { z-index: 50; background-color: #dcedc8 !important; box-shadow: inset -1px -1px 0 #cbd5e1; }
  #tabelRR thead tr:nth-child(1) th.sticky-left-2, #tabelRR thead tr:nth-child(2) th.sticky-left-2 { z-index: 49; background-color: #dcedc8 !important; box-shadow: inset -1px -1px 0 #cbd5e1; }
  
  /* Grand total perpotongan */
  #tabelRR thead tr:nth-child(3) th.sticky-left-1 { z-index: 48; background-color: #eff6ff !important; box-shadow: inset -1px -2px 0 #93c5fd; }
  #tabelRR thead tr:nth-child(3) th.sticky-left-2 { z-index: 47; background-color: #eff6ff !important; box-shadow: inset -1px -2px 0 #93c5fd; }

  #bodyRekap tr:hover td { background-color: #f8fafc !important; cursor: pointer; }
  #bodyRekap tr:hover td.sticky-left-1, #bodyRekap tr:hover td.sticky-left-2 { background-color: #f8fafc !important; }
</style>

<script>
    window.currentUser = { kode_kantor: (typeof USER_KODE_KANTOR !== 'undefined') ? USER_KODE_KANTOR : '000' };
</script>

<div class="max-w-[1920px] mx-auto px-4 py-5 h-[calc(100vh-80px)] flex flex-col bg-slate-50 font-sans text-slate-800 overflow-hidden">
  
  <div class="flex-none mb-4 flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 w-full">
      
      <div class="flex flex-col gap-1 shrink-0">
          <div class="flex items-center gap-3">
              <span class="p-2.5 bg-blue-600 rounded-lg text-white shadow-sm">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
              </span>
              <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Rekap Repayment Rate (RR)</h1>
              <span class="bg-blue-100 text-blue-800 text-[10px] font-bold px-2.5 py-1 rounded border border-blue-200 uppercase tracking-widest mt-1">Konsolidasi</span>
          </div>
          <p class="text-sm text-slate-500 italic ml-14">*RR = Total Baki Debet (Hari Menunggak = 0) / Seluruh Baki Debet</p>
      </div>

      <form id="formFilter" class="flex flex-wrap md:flex-nowrap items-end gap-3 shrink-0" onsubmit="event.preventDefault(); fetchRekap();">
          <div class="flex flex-col w-[140px]">
              <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5 ml-1">CLOSING DATE (M-1)</label>
              <input type="date" id="closing_date" class="border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-700 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition w-full shadow-sm" required>
          </div>
          
          <div class="flex flex-col w-[140px]">
              <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5 ml-1">ACTUAL DATE</label>
              <input type="date" id="harian_date" class="border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-700 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition w-full shadow-sm" required>
          </div>
          
          <div class="flex items-center gap-2 h-[38px]">
              <button type="submit" class="h-full w-[42px] md:w-auto md:px-5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg flex items-center justify-center transition shadow-sm font-bold text-sm" title="Cari Data">
                  <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" class="md:mr-2"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                  <span class="hidden md:inline">CARI</span>
              </button>
              <button type="button" onclick="exportExcelRekap()" class="h-full w-[42px] md:w-auto md:px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg flex items-center justify-center transition shadow-sm" title="Download Excel">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
              </button>
          </div>
      </form>
  </div>

  <div class="flex-1 overflow-hidden bg-white rounded-xl shadow-sm border border-slate-200 relative">
    <div id="loadingRekap" class="hidden absolute inset-0 bg-white/80 z-[100] flex flex-col items-center justify-center text-blue-600 backdrop-blur-sm">
        <div class="animate-spin rounded-full h-10 w-10 border-4 border-blue-500 border-t-transparent mb-3"></div>
        <span class="text-sm font-bold uppercase tracking-widest">Memuat Data RR...</span>
    </div>
    
    <div class="h-full overflow-auto custom-scrollbar relative">
      <table class="w-max min-w-full text-sm text-center border-separate border-spacing-0 text-slate-700" id="tabelRR">
        <thead class="tracking-wider bg-slate-50 text-slate-700 font-bold" id="headRR">
          </thead>
        <tbody id="bodyRekap" class="divide-y divide-slate-100 bg-white text-sm"></tbody>
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

  function setupHeaderRR(userKode) {
      const th = document.getElementById('headRR');
      let thHtml = `<tr>`;

      if (userKode === '000') {
          thHtml += `
            <th rowspan="2" class="sticky-left-1 w-[80px] border-r border-b border-white align-middle bg-[#dcedc8] text-slate-800 text-center" id="lblKode">KODE CABANG</th>
            <th rowspan="2" class="sticky-left-2 min-w-[200px] border-r border-b border-white align-middle text-left pl-5 bg-[#dcedc8] text-slate-800" id="lblNama">NAMA CABANG</th>
          `;
      } else {
          thHtml += `
            <th rowspan="2" class="sticky-left-1 min-w-[200px] border-r border-b border-white align-middle text-left pl-5 bg-[#dcedc8] text-slate-800" id="lblNama">NAMA KANKAS</th>
          `;
      }

      thHtml += `
            <th colspan="3" class="px-4 py-2 border-r border-b border-white align-middle bg-[#dcedc8] text-slate-800 text-sm">M-1</th>
            <th colspan="3" class="px-4 py-2 border-r border-b border-white align-middle bg-[#dcedc8] text-slate-800 text-sm">ACTUAL (REALISASI BARU)</th>
            <th colspan="3" class="px-4 py-2 border-b border-white align-middle bg-[#dcedc8] text-slate-800 text-sm">DELTA</th>
          </tr>
          <tr>
            <th class="px-4 py-2 border-r border-b border-slate-200 bg-[#eef2f6]">BAKI DEBET</th>
            <th class="px-4 py-2 border-r border-b border-slate-200 bg-[#eef2f6]">NOA</th>
            <th class="px-4 py-2 border-r border-b border-slate-200 bg-[#eef2f6]">%</th>
            
            <th class="px-4 py-2 border-r border-b border-slate-200 bg-[#eef2f6]">BAKI DEBET</th>
            <th class="px-4 py-2 border-r border-b border-slate-200 bg-[#eef2f6]">NOA</th>
            <th class="px-4 py-2 border-r border-b border-slate-200 bg-[#eef2f6]">%</th>

            <th class="px-4 py-2 border-r border-b border-slate-200 bg-[#eef2f6]">BAKI DEBET</th>
            <th class="px-4 py-2 border-r border-b border-slate-200 bg-[#eef2f6]">NOA</th>
            <th class="px-4 py-2 border-b border-slate-200 bg-[#eef2f6]">%</th>
          </tr>
          <tr id="rowTotalRRAtas"></tr>
      `;
      th.innerHTML = thHtml;
  }

  function getTrafficLightColor(pct) {
      if (pct < 50) return 'text-rose-600 font-bold';     
      if (pct < 60) return 'text-amber-500 font-bold';    
      return 'text-blue-700 font-bold';                
  }

  async function fetchRekap() {
      const l = document.getElementById('loadingRekap');
      const tb = document.getElementById('bodyRekap');
      const trTot = document.getElementById('rowTotalRRAtas');
      
      if(abortRekap) abortRekap.abort();
      abortRekap = new AbortController();

      l.classList.remove('hidden'); 
      
      const colSpan = userKodeGlobal === '000' ? 11 : 10;
      tb.innerHTML = `<tr><td colspan="${colSpan}" class="text-center py-20 text-slate-400 italic text-base">Sedang mengambil data...</td></tr>`;
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

          if(document.getElementById('lblKode')) document.getElementById('lblKode').innerText = meta.label_kode || 'KODE CABANG';
          if(document.getElementById('lblNama')) document.getElementById('lblNama').innerText = meta.label_nama || 'NAMA CABANG';

          if(rows.length === 0) {
              tb.innerHTML = `<tr><td colspan="${colSpan}" class="text-center py-20 text-slate-400 italic text-base">Tidak ada data.</td></tr>`;
              return;
          }
          rekapDataCache = rows; 

          let html = '';
          rows.forEach(r => {
              const dNoaClass = r.delta_noa < 0 ? 'text-rose-600' : 'text-slate-700';
              const dOsClass  = r.delta_os_lancar < 0 ? 'text-rose-600' : 'text-slate-700';
              const dPctClass = r.delta_pct < 0 ? 'text-rose-600' : 'text-slate-700';
              
              const pM1Class  = getTrafficLightColor(r.m1_pct);
              const pCurClass = getTrafficLightColor(r.cur_pct);

              let rowHtml = `<tr class="transition h-[52px] border-b border-slate-100 hover:bg-slate-50">`;
              
              if (userKodeGlobal === '000') {
                  rowHtml += `
                    <td class="sticky-left-1 px-4 py-2 border-r border-slate-100 font-semibold text-blue-700 z-20 shadow-[inset_-1px_0_0_#e2e8f0] text-center">${r.kode}</td>
                    <td class="sticky-left-2 px-5 py-2 border-r border-slate-100 font-semibold text-slate-700 text-left truncate z-20 shadow-[inset_-1px_0_0_#e2e8f0]" title="${r.nama}">${r.nama}</td>
                  `;
              } else {
                  rowHtml += `
                    <td class="sticky-left-1 px-5 py-2 border-r border-slate-100 font-semibold text-slate-700 text-left truncate z-20 shadow-[inset_-1px_0_0_#e2e8f0]" title="${r.nama}">${r.nama}</td>
                  `;
              }

              rowHtml += `
                    <td class="px-4 py-2 border-r border-slate-100 text-right font-medium text-slate-700">${fmt(r.m1_lancar_os)}</td>
                    <td class="px-4 py-2 border-r border-slate-100 text-center font-bold text-slate-800">${fmt(r.m1_all_noa)}</td>
                    <td class="px-4 py-2 border-r border-slate-100 text-center ${pM1Class}">${r.m1_pct}%</td>
                    
                    <td class="px-4 py-2 border-r border-slate-100 text-right font-medium text-blue-800">${fmt(r.cur_lancar_os)}</td>
                    <td class="px-4 py-2 border-r border-slate-100 text-center font-bold text-blue-800">${fmt(r.cur_all_noa)}</td>
                    <td class="px-4 py-2 border-r border-slate-100 text-center ${pCurClass}">${r.cur_pct}%</td>
                    
                    <td class="px-4 py-2 border-r border-slate-100 text-right font-medium ${dOsClass}">${fmt(r.delta_os_lancar)}</td>
                    <td class="px-4 py-2 border-r border-slate-100 text-center font-bold ${dNoaClass}">${fmt(r.delta_noa)}</td>
                    <td class="px-4 py-2 text-center font-bold ${dPctClass}">${r.delta_pct}%</td>
                </tr>`;
              html += rowHtml;
          });
          tb.innerHTML = html;

          const gtDNoaClass = gt.delta_noa < 0 ? 'text-rose-700' : 'text-blue-900';
          const gtDOsClass  = gt.delta_os_lancar < 0 ? 'text-rose-700' : 'text-blue-900';
          const gtDPctClass = gt.delta_pct < 0 ? 'text-rose-700' : 'text-blue-900';

          const gtM1Color  = getTrafficLightColor(gt.m1_pct);
          const gtCurColor = getTrafficLightColor(gt.cur_pct);

          let gtHtml = '';
          if (userKodeGlobal === '000') {
              gtHtml += `
                  <th class="sticky-left-1 px-4 border-r border-blue-200 text-center text-blue-900 bg-[#eff6ff] !important">-</th>
                  <th class="sticky-left-2 px-5 border-r border-blue-200 text-left text-blue-900 tracking-wide font-extrabold text-base bg-[#eff6ff] !important">GRAND TOTAL</th>
              `;
          } else {
              gtHtml += `
                  <th class="sticky-left-1 px-5 border-r border-blue-200 text-left text-blue-900 tracking-wide font-extrabold text-base bg-[#eff6ff] !important">TOTAL CABANG</th>
              `;
          }

          gtHtml += `
              <th class="px-4 border-r border-blue-200 text-right align-middle font-bold text-base text-blue-900 bg-[#eff6ff]">${fmt(gt.m1_lancar_os)}</th>
              <th class="px-4 border-r border-blue-200 text-center align-middle font-bold text-base text-blue-900 bg-[#eff6ff]">${fmt(gt.m1_all_noa)}</th>
              <th class="px-4 border-r border-blue-200 text-center align-middle font-bold text-base ${gtM1Color} bg-[#eff6ff]">${gt.m1_pct}%</th>
              
              <th class="px-4 border-r border-blue-200 text-right align-middle font-bold text-base text-blue-900 bg-[#eff6ff]">${fmt(gt.cur_lancar_os)}</th>
              <th class="px-4 border-r border-blue-200 text-center align-middle font-bold text-base text-blue-900 bg-[#eff6ff]">${fmt(gt.cur_all_noa)}</th>
              <th class="px-4 border-r border-blue-200 text-center align-middle font-bold text-base ${gtCurColor} bg-[#eff6ff]">${gt.cur_pct}%</th>
              
              <th class="px-4 border-r border-blue-200 text-right align-middle font-bold text-base ${gtDOsClass} bg-[#eff6ff]">${fmt(gt.delta_os_lancar)}</th>
              <th class="px-4 border-r border-blue-200 text-center align-middle font-bold text-base ${gtDNoaClass} bg-[#eff6ff]">${fmt(gt.delta_noa)}</th>
              <th class="px-4 text-center align-middle font-bold text-base ${gtDPctClass} bg-[#eff6ff]">${gt.delta_pct}%</th>
          `;
          trTot.innerHTML = gtHtml;

      } catch(e) { if(e.name!=='AbortError') console.error(e); } finally { l.classList.add('hidden'); }
  }

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
          csv += `${Math.round(r.m1_lancar_os)}\t${r.m1_all_noa}\t${r.m1_pct}%\t${Math.round(r.cur_lancar_os)}\t${r.cur_all_noa}\t${r.cur_pct}%\t${Math.round(r.delta_os_lancar)}\t${r.delta_noa}\t${r.delta_pct}%\n`;
      });

      const blob = new Blob([csv], { type: 'application/vnd.ms-excel' });
      const a = document.createElement('a');
      a.href = window.URL.createObjectURL(blob);
      a.download = `Rekap_RR_${userKodeGlobal}_${document.getElementById("harian_date").value}.xls`; 
      a.click();
  }
</script>