<style>
  :root { --primary: #2563eb; --bg: #f8fafc; --text: #334155; }
  body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text); overflow: hidden; }
  
  .inp { 
      box-sizing: border-box;
      border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 0 0.5rem; 
      font-size: 13px; background: #fff; height: 36px; cursor: pointer; 
      outline: none; transition: border 0.2s; min-width: 0;
  }
  .inp:focus { border-color: var(--primary); ring: 2px solid #bfdbfe; }
  .inp:disabled { background-color: #f8fafc; color: #64748b; font-weight: 600; cursor: not-allowed; border-color: #e2e8f0; }
  
  input[type="date"] { position: relative; cursor: pointer; }
  input[type="date"]::-webkit-inner-spin-button,
  input[type="date"]::-webkit-calendar-picker-indicator {
      position: absolute; top: 0; left: 0; right: 0; bottom: 0;
      width: 100%; height: 100%; opacity: 0; cursor: pointer;
  }

  #kolScroller { 
      --kol_headH: 40px; 
      overflow: auto; height: 100%; border-radius: 8px; 
      border: 1px solid #e2e8f0; background: white; position: relative;
      -webkit-overflow-scrolling: touch; 
  }
  
  table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 12px; }
  th, td { white-space: nowrap; padding: 8px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
  tr:hover td { background-color: #f8fafc; }

  /* Style untuk Header yang bisa di-sort */
  thead th { 
      position: sticky; top: 0; z-index: 60; 
      background: #d9ead3; color: #1e293b; font-weight: 700; 
      text-transform: uppercase; border-bottom: 1px solid #cbd5e1;
  }
  .sort-link { cursor: pointer; user-select: none; transition: background 0.2s; }
  .sort-link:hover { background: #cfe3c8 !important; }
  
  .col-kode { position: sticky; left: 0; z-index: 45; background: white; border-right: 1px solid #e2e8f0; width: 60px; min-width: 60px; text-align: center; }
  thead th.col-kode { z-index: 70; background: #d9ead3; }
  
  .col-nama { position: sticky; left: 60px; z-index: 44; background: white; border-right: 1px solid #e2e8f0; box-shadow: 2px 0 5px rgba(0,0,0,0.03); min-width: 180px; }
  thead th.col-nama { z-index: 69; background: #d9ead3; }

  .sticky-total td { 
      position: sticky; top: var(--kol_headH); z-index: 55; 
      background: #f4f7fb; font-weight: 700; border-bottom: 2px solid #bfdbfe; 
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); 
  }
  .sticky-total td.col-kode { z-index: 65; background: #f4f7fb; }
  .sticky-total td.col-nama { z-index: 64; background: #f4f7fb; border-right: 1px solid #bfdbfe; }

  @media (max-width: 767px) {
      .col-kode { display: none !important; }
      .col-nama { left: 0 !important; z-index: 45 !important; min-width: 140px; max-width: 160px; white-space: normal; line-height: 1.2; }
      thead th.col-nama { z-index: 70 !important; }
      .sticky-total td.col-nama { z-index: 65 !important; }
  }
</style>

<div class="max-w-7xl mx-auto px-3 md:px-4 py-4 h-[calc(100vh-80px)] md:h-[calc(100vh-120px)] flex flex-col">
  
  <div class="flex flex-col md:flex-row md:items-start justify-between gap-3 mb-3 shrink-0">
    <div class="flex items-start justify-between w-full md:w-auto">
        <div>
            <h1 class="text-xl md:text-2xl font-bold flex items-center gap-2 text-slate-800">
                <span class="bg-blue-600 text-white p-1 rounded text-sm md:text-base shadow-sm">💳</span> 
                <span>Rekap Kolektibilitas</span>
            </h1>
            <p class="text-[10px] md:text-xs text-slate-500 mt-0.5 ml-1">*Posisi Harian (NOA & Saldo)</p>
        </div>

        <button id="btnToggleKolekFilter" class="md:hidden flex items-center gap-1.5 px-3 py-1.5 border border-gray-300 rounded-lg bg-white text-sm font-semibold text-slate-700 shadow-sm transition">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
            Filter
        </button>
    </div>

    <div id="panelFilterKolek" class="hidden md:block bg-white border border-gray-200 rounded-xl p-3 shadow-sm w-full md:w-auto transition-all">
        <form id="formFilterKolek" class="flex flex-col md:flex-row items-end gap-3 w-full">
            
            <div class="flex gap-2 w-full md:w-auto">
                <div class="flex flex-col w-1/2 md:w-[130px]">
                    <label class="text-[9px] font-extrabold text-slate-500 uppercase ml-1 mb-1 tracking-wider">TIPE SALDO</label>
                    <select id="hitung_berdasarkan" class="inp font-bold text-blue-700 shadow-sm auto-refresh">
                        <option value="baki_debet">BAKI DEBET</option>
                        <option value="saldo_bank">SALDO BANK</option>
                    </select>
                </div>
                <div class="flex flex-col w-1/2 md:w-[130px]">
                    <label class="text-[9px] font-extrabold text-slate-500 uppercase ml-1 mb-1 tracking-wider">TANGGAL</label>
                    <input type="date" id="harian_date_kolek" class="inp shadow-sm" required>
                </div>
            </div>

            <div class="flex gap-2 w-full md:w-auto md:flex-1 items-end">
                <div class="flex flex-col flex-1 md:w-[220px]">
                    <label class="text-[9px] font-extrabold text-slate-500 uppercase ml-1 mb-1 tracking-wider">CABANG</label>
                    <select id="opt_kantor_kolek" class="inp font-medium text-slate-700 shadow-sm truncate auto-refresh"><option value="">Memuat...</option></select>
                </div>
                
                <div class="flex gap-2 shrink-0">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white h-9 px-4 rounded-lg font-bold text-sm shadow-sm flex items-center justify-center gap-2 transition">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <span>CARI</span>
                    </button>
                    <button type="button" onclick="exportKolekExcel()" class="bg-emerald-600 hover:bg-emerald-700 text-white h-9 w-11 rounded-lg shadow-sm flex items-center justify-center transition">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    </button>
                </div>
            </div>
        </form>
    </div>
  </div>

  <div class="flex-1 min-h-0 relative flex flex-col">
    <div id="loadingKolek" class="hidden absolute inset-0 bg-white/80 z-[100] flex flex-col items-center justify-center text-blue-600 font-bold text-sm backdrop-blur-sm rounded-lg">
        <div class="animate-spin h-8 w-8 border-4 border-blue-200 border-t-blue-600 rounded-full mb-2"></div>
        Memuat Data...
    </div>

    <div class="table-wrapper" id="kolScroller">
      <table id="tabelKolektibilitas">
        <thead id="theadKolek">
          <tr>
            <th class="col-kode sort-link" onclick="sortTable('kode_unit')">Kode <span id="icon_kode_unit">↕</span></th>
            <th class="col-nama sort-link" onclick="sortTable('nama_unit')">NAMA KANTOR <span id="icon_nama_unit">↕</span></th>
            <th class="text-right min-w-[100px] sort-link" onclick="sortTable('bd_L')">Lancar (L) <span id="icon_bd_L">↕</span></th>
            <th class="text-right min-w-[100px] sort-link" onclick="sortTable('bd_DP')">DPK (DP) <span id="icon_bd_DP">↕</span></th>
            <th class="text-right min-w-[100px] sort-link" onclick="sortTable('bd_KL')">Kurang Lancar <span id="icon_bd_KL">↕</span></th>
            <th class="text-right min-w-[100px] sort-link" onclick="sortTable('bd_D')">Diragukan (D) <span id="icon_bd_D">↕</span></th>
            <th class="text-right min-w-[100px] sort-link" onclick="sortTable('bd_M')">Macet (M) <span id="icon_bd_M">↕</span></th>
            <th class="text-right min-w-[110px] border-l border-slate-200 sort-link" onclick="sortTable('bd_npl')">Total NPL <span id="icon_bd_npl">↕</span></th>
            <th class="text-right min-w-[120px] border-l border-slate-200 sort-link" onclick="sortTable('total_bd')">Portofolio <span id="icon_total_bd">↕</span></th>
            <th class="text-right min-w-[70px] sort-link" onclick="sortTable('persentase_npl')">% NPL <span id="icon_persentase_npl">↕</span></th>
          </tr>
        </thead>
        <tbody id="totalKolek"></tbody>
        <tbody id="bodyKolek"></tbody>
      </table>
    </div>
  </div>
</div>

<script>
  const API_KOLEK = './api/kredit/'; 
  const API_KODE  = './api/kode/';
  const API_DATE  = './api/date/';

  const nf = new Intl.NumberFormat('id-ID');
  const fmt = n => nf.format(Number(n||0));
  const fmt2 = x => (x == null || x === '' ? '0.00' : Number(x).toFixed(2));

  window.kolekDataRaw = [];
  window.kolekGtRaw = null;
  let currentSort = { col: null, dir: 'asc' }; // 'asc' atau 'desc'

  document.getElementById('btnToggleKolekFilter').addEventListener('click', function() {
      document.getElementById('panelFilterKolek').classList.toggle('hidden');
  });

  document.querySelectorAll('.auto-refresh').forEach(el => {
      el.addEventListener('change', () => fetchKolektibilitas());
  });

  function updateStickyHeader() {
      const thead = document.getElementById('theadKolek');
      const scroller = document.getElementById('kolScroller');
      if(thead && scroller) {
          scroller.style.setProperty('--kol_headH', (thead.offsetHeight - 1) + 'px');
      }
  }
  window.addEventListener('resize', updateStickyHeader);

  // --- LOGIKA SORTING ---
  function sortTable(column) {
      if (!window.kolekDataRaw || window.kolekDataRaw.length === 0) return;

      // Toggle arah jika kolom sama, jika beda balik ke 'desc' (biasanya NPL dicari yang tertinggi dulu)
      if (currentSort.col === column) {
          currentSort.dir = currentSort.dir === 'asc' ? 'desc' : 'asc';
      } else {
          currentSort.col = column;
          currentSort.dir = 'desc'; 
      }

      // Reset semua icon
      document.querySelectorAll('.sort-link span').forEach(s => s.innerText = '↕');
      // Set icon aktif
      document.getElementById('icon_' + column).innerText = currentSort.dir === 'asc' ? '↑' : '↓';

      // Jalankan sorting
      window.kolekDataRaw.sort((a, b) => {
          let valA = a[column];
          let valB = b[column];

          // Jika string (nama unit), gunakan localeCompare
          if (typeof valA === 'string') {
              return currentSort.dir === 'asc' 
                ? valA.localeCompare(valB) 
                : valB.localeCompare(valA);
          }

          // Jika angka
          return currentSort.dir === 'asc' ? valA - valB : valB - valA;
      });

      renderTableOnly(window.kolekDataRaw);
  }

  window.addEventListener('DOMContentLoaded', async () => {
    const user = (window.getUser && window.getUser()) || null;
    const uKode = user?.kode ? String(user.kode).padStart(3,'0') : '000';
    await populateKantorKolek(uKode);
    try { 
        const r = await fetch(API_DATE); 
        const j = await r.json();
        if (j.data) document.getElementById('harian_date_kolek').value = j.data.last_created;
    } catch {
        document.getElementById('harian_date_kolek').value = new Date().toISOString().split('T')[0];
    }
    fetchKolektibilitas();
  });

  async function populateKantorKolek(userKode){
    const optKantor = document.getElementById('opt_kantor_kolek');
    if(userKode && userKode !== '000'){
        optKantor.innerHTML = `<option value="${userKode}">CABANG ${userKode}</option>`;
        optKantor.value = userKode;
        optKantor.disabled = true;
        return; 
    }
    try {
        const res = await fetch(API_KODE, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({type:'kode_kantor'}) });
        const json = await res.json();
        const list = json.data || [];
        let html = `<option value="">ALL | SEMUA CABANG</option>`;
        list.filter(x => x.kode_kantor && x.kode_kantor !== '000').sort((a,b) => String(a.kode_kantor).localeCompare(b.kode_kantor)).forEach(it => {
            html += `<option value="${String(it.kode_kantor).padStart(3,'0')}">${String(it.kode_kantor).padStart(3,'0')} - ${it.nama_kantor}</option>`;
        });
        optKantor.innerHTML = html;
    } catch(e){ optKantor.innerHTML = `<option value="">Error Load</option>`; }
  }

  document.getElementById('formFilterKolek').addEventListener('submit', e => { 
      e.preventDefault(); 
      if(window.innerWidth < 768) document.getElementById('panelFilterKolek').classList.add('hidden');
      fetchKolektibilitas(); 
  });

  async function fetchKolektibilitas() {
      const loading = document.getElementById('loadingKolek');
      const harian = document.getElementById('harian_date_kolek').value;
      const kantor = document.getElementById('opt_kantor_rec')?.value || document.getElementById('opt_kantor_kolek').value;
      const mode   = document.getElementById('hitung_berdasarkan').value;

      loading.classList.remove('hidden');
      
      try {
          const payload = { type: 'kolektibilitas', harian_date: harian, kode_kantor: kantor, hitung_berdasarkan: mode };
          const res = await fetch(API_KOLEK, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          const json = await res.json();
          
          window.kolekDataRaw = json.data?.data || json.data || [];
          window.kolekGtRaw = json.data?.grand_total || null;

          // Render Total dan Body
          renderTotalOnly(window.kolekGtRaw);
          renderTableOnly(window.kolekDataRaw);

          setTimeout(updateStickyHeader, 50);
      } catch(e) { 
          document.getElementById('bodyKolek').innerHTML = `<tr><td colspan="10" class="text-center py-10 text-red-500">Error Load Data</td></tr>`;
      } finally { loading.classList.add('hidden'); }
  }

  function renderTotalOnly(gt) {
      const tbodyTotal = document.getElementById('totalKolek');
      tbodyTotal.innerHTML = '';
      if (!gt) return;
      tbodyTotal.innerHTML = `
        <tr class="sticky-total">
            <td class="col-kode"></td>
            <td class="col-nama text-left">GRAND TOTAL</td>
            <td class="text-right font-bold text-blue-700">${fmt(gt.bd_L)} <div class="text-[10px] text-slate-500 font-normal">${fmt(gt.noa_L)} NOA</div></td>
            <td class="text-right font-bold text-slate-700">${fmt(gt.bd_DP)} <div class="text-[10px] text-slate-500 font-normal">${fmt(gt.noa_DP)} NOA</div></td>
            <td class="text-right font-bold text-orange-500">${fmt(gt.bd_KL)} <div class="text-[10px] text-slate-500 font-normal">${fmt(gt.noa_KL)} NOA</div></td>
            <td class="text-right font-bold text-orange-600">${fmt(gt.bd_D)} <div class="text-[10px] text-slate-500 font-normal">${fmt(gt.noa_D)} NOA</div></td>
            <td class="text-right font-bold text-red-600">${fmt(gt.bd_M)} <div class="text-[10px] text-slate-500 font-normal">${fmt(gt.noa_M)} NOA</div></td>
            <td class="text-right font-bold text-red-700 border-l border-slate-200">${fmt(gt.bd_npl)} <div class="text-[10px] text-slate-500 font-normal">${fmt(gt.noa_npl)} NOA</div></td>
            <td class="text-right font-bold text-blue-800 border-l border-slate-200">${fmt(gt.total_bd)} <div class="text-[10px] text-slate-500 font-normal">${fmt(gt.total_noa)} NOA</div></td>
            <td class="text-right font-bold text-slate-800">${fmt2(gt.persentase_npl)}%</td>
        </tr>`;
  }

  function renderTableOnly(rows) {
      const tbody = document.getElementById('bodyKolek');
      tbody.innerHTML = '';
      if (rows.length === 0) {
          tbody.innerHTML = `<tr><td colspan="10" class="text-center py-10 text-slate-400">Data Kosong</td></tr>`;
          return;
      }
      let html = '';
      rows.forEach(r => {
          html += `
            <tr class="transition border-b">
                <td class="col-kode text-center font-mono text-slate-500">${r.kode_unit}</td>
                <td class="col-nama font-semibold text-slate-700 text-xs truncate" title="${r.nama_unit}">${r.nama_unit}</td>
                <td class="text-right text-blue-700">${fmt(r.bd_L)} <div class="text-[10px] text-slate-400">${fmt(r.noa_L)} NOA</div></td>
                <td class="text-right text-slate-700">${fmt(r.bd_DP)} <div class="text-[10px] text-slate-400">${fmt(r.noa_DP)} NOA</div></td>
                <td class="text-right text-orange-500">${fmt(r.bd_KL)} <div class="text-[10px] text-slate-400">${fmt(r.noa_KL)} NOA</div></td>
                <td class="text-right text-orange-600">${fmt(r.bd_D)} <div class="text-[10px] text-slate-400">${fmt(r.noa_D)} NOA</div></td>
                <td class="text-right text-red-600">${fmt(r.bd_M)} <div class="text-[10px] text-slate-400">${fmt(r.noa_M)} NOA</div></td>
                <td class="text-right font-bold text-red-700 border-l border-slate-100">${fmt(r.bd_npl)} <div class="text-[10px] text-slate-400 font-normal">${fmt(r.noa_npl)} NOA</div></td>
                <td class="text-right font-bold text-blue-800 border-l border-slate-100">${fmt(r.total_bd)} <div class="text-[10px] text-slate-400 font-normal">${fmt(r.total_noa)} NOA</div></td>
                <td class="text-right font-bold ${r.persentase_npl > 5 ? 'text-red-600' : 'text-green-600'}">${fmt2(r.persentase_npl)}%</td>
            </tr>`;
      });
      tbody.innerHTML = html;
  }

  function exportKolekExcel() {
      const rows = window.kolekDataRaw || [];
      const gt = window.kolekGtRaw || null;
      if(rows.length === 0) return alert("Data Kosong!");
      let csv = "KODE\tNAMA KANTOR\tLANCAR (OS)\tDPK (OS)\tKL (OS)\tD (OS)\tM (OS)\tTOTAL NPL (OS)\tTOTAL PORTO (OS)\t% NPL\n";
      if(gt) csv += `\tGRAND TOTAL\t${gt.bd_L}\t${gt.bd_DP}\t${gt.bd_KL}\t${gt.bd_D}\t${gt.bd_M}\t${gt.bd_npl}\t${gt.total_bd}\t${gt.persentase_npl}\n`;
      rows.forEach(r => { csv += `'${r.kode_unit}\t${r.nama_unit}\t${r.bd_L}\t${r.bd_DP}\t${r.bd_KL}\t${r.bd_D}\t${r.bd_M}\t${r.bd_npl}\t${r.total_bd}\t${r.persentase_npl}\n`; });
      const blob = new Blob([csv], { type: 'application/vnd.ms-excel' });
      const a = document.createElement('a');
      a.href = window.URL.createObjectURL(blob);
      a.download = `Kolektibilitas_${document.getElementById('harian_date_kolek').value}.xls`;
      a.click();
  }
</script>