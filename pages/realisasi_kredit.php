<style>
  :root { --primary: #2563eb; --bg: #f8fafc; --text: #334155; }
  body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text); overflow: hidden; }
  
  /* Input & UI */
  .inp { border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 0 0.6rem; font-size: 12px; background: #fff; width: 100%; height: 38px; outline: none; transition: 0.2s;}
  .inp:focus { border-color: var(--primary); box-shadow: 0 0 0 2px rgba(37,99,235,0.1); }
  .lbl { font-size: 9px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 2px; display: block; }
  
  /* Table Logic - FIX BUG GESER */
  .table-wrapper { flex: 1; overflow: auto; border-radius: 12px; border: 1px solid #e2e8f0; background: white; position: relative; }
  table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 12px; table-layout: fixed; }
  
  /* Header Sticky */
  th.head-row { 
    position: sticky; top: 0; z-index: 100; 
    background: #d9ead3 !important; color: #1e293b; font-weight: 700; 
    padding: 12px 10px; border-bottom: 1px solid #cbd5e1; text-transform: uppercase; 
  }
  
  /* Total Sticky */
  .row-total-sticky td { 
    position: sticky; top: 43px; z-index: 90; 
    background: #eff6ff !important; font-weight: 800; color: #1e40af; 
    border-bottom: 2px solid #60a5fa; padding: 10px; font-size: 13px;
  }

  td { padding: 10px; border-bottom: 1px solid #f1f5f9; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

  /* STICKY COLUMN */
  .sticky-col-1 { position: sticky; left: 0; z-index: 40; background: inherit; border-right: 1px solid #e2e8f0; width: 60px; min-width: 60px; text-align: center; }
  .sticky-col-2 { position: sticky; left: 60px; z-index: 39; background: inherit; border-right: 1px solid #e2e8f0; width: 180px; min-width: 180px; }

  /* Z-INDEX LAYER */
  th.head-row.sticky-col-1 { z-index: 110; }
  th.head-row.sticky-col-2 { z-index: 109; }
  .row-total-sticky td.sticky-col-1 { z-index: 95; }
  .row-total-sticky td.sticky-col-2 { z-index: 94; }

  @media (max-width: 768px) {
      .sticky-col-1 { display: none; } 
      .sticky-col-2 { left: 0 !important; width: 140px; min-width: 140px; }
      #filterPanel { display: none; flex-direction: column; width: 100%; }
      #filterPanel.active { display: flex; padding: 10px; background: white; border-radius: 12px; margin-bottom: 10px; border: 1px solid #e2e8f0; }
  }

  .btn-icon { height: 38px; border-radius: 8px; background: var(--primary); color: white; border: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; padding: 0 12px; }
  .cell-action { cursor: pointer; color: var(--primary); font-weight: 700; text-decoration: underline; }
  
  /* Modal Animation */
  .modal-backdrop { background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); transition: all 0.3s; }
  .modal-content { animation: scaleIn 0.2s ease-out; }
  @keyframes scaleIn { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
</style>

<div class="max-w-7xl mx-auto px-2 md:px-4 py-4 h-[calc(100vh-80px)] flex flex-col">
  
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 mb-4 shrink-0">
    <div class="flex items-center justify-between w-full lg:w-auto">
      <div>
        <h1 class="text-xl md:text-2xl font-bold flex items-center gap-2 text-slate-800">
          <span class="bg-blue-600 text-white p-1.5 rounded-lg shadow-sm">💳</span> 
          <span>Rekap Realisasi</span>
        </h1>
        <p class="text-[10px] md:text-xs text-slate-500 font-medium">*Klik NOA untuk lihat detail debitur</p>
      </div>
      <button onclick="id('filterPanel').classList.toggle('active')" class="lg:hidden h-9 px-3 bg-white border border-slate-300 rounded-lg text-xs font-bold flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
          FILTER
      </button>
    </div>

    <div id="filterPanel" class="lg:flex lg:items-end gap-2 shrink-0 transition-all">
      <form id="formFilter" class="flex flex-col lg:flex-row items-stretch lg:items-end gap-2 w-full">
        <div class="grid grid-cols-2 gap-2">
            <div><label class="lbl">Closing</label><input type="date" id="tgl_closing" class="inp"></div>
            <div><label class="lbl">Harian</label><input type="date" id="tgl_harian" class="inp"></div>
        </div>
        <div class="lg:w-[200px]"><label class="lbl">Kantor</label><select id="opt_kantor" class="inp"></select></div>
        <div class="flex gap-2 mt-2 lg:mt-0">
          <button type="submit" class="btn-icon flex-1 lg:px-6">CARI</button>
          <button type="button" onclick="alert('Export ready!')" class="btn-icon bg-emerald-600 px-3 text-white">EXCEL</button>
        </div>
      </form>
    </div>
  </div>

  <div class="table-wrapper custom-scrollbar">
    <div id="loading" class="hidden absolute inset-0 bg-white/80 z-[200] flex flex-col items-center justify-center text-blue-600 font-bold">
        <div class="animate-spin h-8 w-8 border-4 border-blue-200 border-t-blue-600 rounded-full mb-2"></div>
        MEMUAT...
    </div>
    <table>
      <thead>
        <tr>
          <th class="head-row sticky-col-1">Kode</th>
          <th class="head-row sticky-col-2 text-left">Nama Kantor</th>
          <th class="head-row text-right" style="width:80px">NOA</th>
          <th class="head-row text-right" style="width:140px">Realisasi</th>
          <th class="head-row text-right" style="width:140px">Run Off</th>
          <th class="head-row text-right" style="width:140px">Growth</th>
        </tr>
      </thead>
      <tbody id="totalRow"></tbody>
      <tbody id="bodyData"></tbody>
    </table>
  </div>
</div>

<div id="modalDetail" class="fixed inset-0 hidden modal-backdrop items-center justify-center z-[9999] px-2 md:px-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl h-[85vh] flex flex-col overflow-hidden modal-content">
      <div class="flex items-center justify-between p-4 border-b bg-slate-50">
          <div>
              <h3 class="font-bold text-slate-800 text-lg">Detail Realisasi Debitur</h3>
              <p class="text-xs text-slate-500 mt-0.5" id="modalSubTitle">Nama Unit: -</p>
          </div>
          <button onclick="closeModal()" class="w-9 h-9 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-red-500 hover:border-red-100 transition shadow-sm font-bold text-xl">✕</button>
      </div>
      
      <div class="flex-1 overflow-auto p-0 custom-scrollbar relative">
          <div id="loadingModal" class="hidden absolute inset-0 bg-white/90 z-20 flex flex-col items-center justify-center text-blue-600 font-bold uppercase tracking-widest text-xs">
              <div class="animate-spin h-8 w-8 border-4 border-blue-200 border-t-blue-600 rounded-full mb-3"></div>
              Memuat Rincian...
          </div>
          <table class="w-full text-xs text-left border-separate border-spacing-0">
              <thead class="sticky top-0 bg-slate-100 z-10 shadow-sm font-bold uppercase text-slate-500 text-[10px]">
                  <tr>
                      <th class="px-4 py-3 border-b border-r border-slate-200 text-center w-12">No</th>
                      <th class="px-4 py-3 border-b border-r border-slate-200">Nama Nasabah</th>
                      <th class="px-4 py-3 border-b border-r border-slate-200 text-center">No Rekening</th>
                      <th class="px-4 py-3 border-b border-r border-slate-200 text-center">Tgl Realisasi</th>
                      <th class="px-4 py-3 border-b text-right bg-emerald-50 text-emerald-700">Plafond</th>
                  </tr>
              </thead>
              <tbody id="modalTableBody" class="divide-y divide-slate-100"></tbody>
          </table>
      </div>
      
      <div class="p-4 border-t bg-slate-50 flex justify-between items-center">
          <span class="text-xs font-medium text-slate-500" id="modalTotalCount">Total: 0 Debitur</span>
          <button onclick="closeModal()" class="px-6 py-2 bg-slate-800 text-white rounded-xl text-xs font-bold hover:bg-slate-900 transition shadow-md">Tutup</button>
      </div>
  </div>
</div>

<script>
  const id = (x) => document.getElementById(x);
  const nf = new Intl.NumberFormat('id-ID');
  const fmt = n => nf.format(Number(n)||0);

  window.addEventListener('DOMContentLoaded', async () => {
      await populateKantor();
      const user = (window.getUser && window.getUser()) || null;
      const uKode = (user?.kode ? String(user.kode).padStart(3,'0') : '000');
      if (uKode !== '000') { id('opt_kantor').value = uKode; id('opt_kantor').disabled = true; }

      const d = await fetch('./api/date/').then(r => r.json());
      if (d.data) { id('tgl_closing').value = d.data.last_closing; id('tgl_harian').value = d.data.last_created; }
      fetchData();
  });

  async function populateKantor() {
      const res = await fetch('./api/kode/', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({type:'kode_kantor'}) }).then(r => r.json());
      let h = '<option value="">KONSOLIDASI (SEMUA)</option>';
      (res.data || []).filter(x => x.kode_kantor !== '000').forEach(x => { h += `<option value="${x.kode_kantor}">${x.kode_kantor} - ${x.nama_kantor}</option>`; });
      id('opt_kantor').innerHTML = h;
  }

  id('formFilter').onsubmit = (e) => { 
      e.preventDefault(); 
      if(window.innerWidth < 1024) id('filterPanel').classList.remove('active');
      fetchData(); 
  };

  async function fetchData() {
      id('loading').classList.remove('hidden');
      const payload = { 
          type: 'Realisasi Kredit', 
          closing_date: id('tgl_closing').value, 
          harian_date: id('tgl_harian').value, 
          kode_kantor: id('opt_kantor').value 
      };

      try {
          const res = await fetch('./api/kredit/', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload) }).then(r => r.json());
          const list = res.data?.data || [];
          const gt = res.data?.grand_total || {};

          id('totalRow').innerHTML = `
            <tr class="row-total-sticky">
              <td class="sticky-col-1">ALL</td>
              <td class="sticky-col-2">TOTAL DATA</td>
              <td class="text-right">${fmt(gt.noa_realisasi)}</td>
              <td class="text-right">${fmt(gt.total_realisasi)}</td>
              <td class="text-right">${fmt(gt.total_run_off)}</td>
              <td class="text-right">${fmt(gt.growth)}</td>
            </tr>`;

          id('bodyData').innerHTML = list.map(r => `
            <tr class="bg-white">
              <td class="sticky-col-1 text-slate-400 font-mono">${r.kode_unit}</td>
              <td class="sticky-col-2 font-bold text-slate-700">${r.nama_unit}</td>
              <td class="text-right"><span class="cell-action" onclick="showDetail('${r.kode_unit}', '${r.nama_unit}')">${fmt(r.noa_realisasi)}</span></td>
              <td class="text-right font-bold text-blue-700 bg-blue-50/20">${fmt(r.total_realisasi)}</td>
              <td class="text-right text-orange-700 bg-orange-50/20">${fmt(r.total_run_off)}</td>
              <td class="text-right font-bold text-green-700 bg-green-50/20">${fmt(r.growth)}</td>
            </tr>`).join('');

      } catch(e) { id('bodyData').innerHTML = '<tr><td colspan="6" class="text-center py-20 text-red-500">Gagal Memuat Data</td></tr>'; }
      finally { id('loading').classList.add('hidden'); }
  }

  /* MODAL LOGIC - INI YANG TADI KETINGGALAN BRO */
  async function showDetail(kodeUnit, namaUnit) {
      const modal = id('modalDetail');
      const tbody = id('modalTableBody');
      const loader = id('loadingModal');
      
      modal.classList.replace('hidden', 'flex');
      id('modalSubTitle').innerText = `Unit: ${namaUnit} (${kodeUnit})`;
      tbody.innerHTML = '';
      loader.classList.remove('hidden');

      // Tentukan param kankas vs cabang
      const optKantor = id('opt_kantor').value;
      const payload = { 
          type: 'Detail Realisasi Kredit', 
          harian_date: id('tgl_harian').value, 
          kode_kantor: optKantor || kodeUnit,
          kode_kankas: optKantor ? kodeUnit : ''
      };

      try {
          const res = await fetch('./api/kredit/', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload) }).then(r => r.json());
          const rows = res.data || [];
          id('modalTotalCount').innerText = `Total: ${rows.length} Debitur`;
          
          if(rows.length === 0) {
              tbody.innerHTML = '<tr><td colspan="5" class="py-12 text-center text-slate-400">Tidak ada detail realisasi.</td></tr>';
          } else {
              tbody.innerHTML = rows.map((d, i) => `
                <tr class="hover:bg-slate-50 transition">
                  <td class="px-4 py-3 border-b text-center text-slate-400 font-mono">${i+1}</td>
                  <td class="px-4 py-3 border-b font-bold text-slate-700">${d.nama_nasabah}</td>
                  <td class="px-4 py-3 border-b text-center font-mono text-slate-500">${d.no_rekening}</td>
                  <td class="px-4 py-3 border-b text-center text-slate-600">${d.tgl_realisasi}</td>
                  <td class="px-4 py-3 border-b text-right font-bold text-emerald-700 bg-emerald-50/30">Rp ${fmt(d.plafond)}</td>
                </tr>`).join('');
          }
      } catch(e) { tbody.innerHTML = '<tr><td colspan="5" class="py-12 text-center text-red-500 font-bold">Gagal mengambil data.</td></tr>'; }
      finally { loader.classList.add('hidden'); }
  }

  function closeModal() { id('modalDetail').classList.replace('flex', 'hidden'); }
</script>