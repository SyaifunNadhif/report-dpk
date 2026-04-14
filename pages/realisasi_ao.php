<style>
  :root { --primary: #2563eb; --bg: #f8fafc; --text: #334155; }
  body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text); overflow: hidden; }
  
  /* === INPUTS & UI === */
  .inp { 
      box-sizing: border-box; border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 0 0.5rem; 
      font-size: 13px; background: #fff; width: 100%; height: 38px; outline: none; transition: all 0.2s;
  }
  .inp:focus { border-color: var(--primary); box-shadow: 0 0 0 2px rgba(37,99,235,0.1); }
  .inp:disabled { background-color: #f1f5f9; color: #64748b; font-weight: 700; cursor: not-allowed; }
  .lbl { font-size: 9px; font-weight: 800; color: #475569; text-transform: uppercase; margin-left: 2px; margin-bottom: 2px; }

  /* === TABLE SCROLLER === */
  #top50Scroller { position: relative; border: 1px solid #e2e8f0; border-radius: 12px; background: white; height: 100%; overflow: auto; }
  table { border-collapse: separate; border-spacing: 0; width: 100%; font-size: 11px; }
  th, td { white-space: nowrap; padding: 10px 12px; vertical-align: middle; }
  thead th { position: sticky; top: 0; z-index: 60; background: #f8fafc; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; font-size: 10px; border-bottom: 1px solid #e2e8f0; border-right: 1px solid #f1f5f9; }

  .ao-link { color: #2563eb; font-weight: 700; cursor: pointer; text-decoration: underline decoration-blue-200; }
  .ao-link:hover { color: #1e40af; background: #eff6ff; border-radius: 4px; }

  /* === WEB STYLE FILTER === */
  #filterPanelAO { display: flex; align-items: center; gap: 8px; background: white; padding: 6px 12px; border: 1px solid #e2e8f0; border-radius: 12px; }
  .filter-group { display: flex; flex-direction: column; }
  .filter-group .inp-mini { height: 34px; width: 130px; font-size: 12px; font-weight: 600; }

  /* MOBILE RESPONSIVE */
  @media (max-width: 1024px) {
      #headerRow { flex-direction: column; align-items: flex-start !important; gap: 12px; }
      #filterPanelAO { display: none; width: 100%; flex-direction: column; align-items: stretch; padding: 15px; }
      #filterPanelAO.active { display: flex; }
      .filter-group .inp-mini { width: 100%; }
      #btnToggleFilter { display: flex !important; }
  }
</style>

<div class="w-full h-[calc(100vh-80px)] bg-slate-50 flex justify-center font-sans text-slate-800 px-3 md:px-4 py-4">
  <div class="w-full max-w-7xl flex flex-col h-full">
    
    <div id="headerRow" class="flex flex-row items-center justify-between mb-4 shrink-0">
      <div class="flex items-center justify-between w-full lg:w-auto">
          <div class="flex items-center gap-3">
              <div class="p-2 bg-blue-600 text-white rounded-lg shadow-lg">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
              </div>
              <div>
                  <h1 class="text-xl font-black text-slate-800 leading-tight">Rekap Realisasi Per AO</h1>
                  <p class="text-[10px] text-slate-500 font-medium">*Monitoring Pencairan Kredit</p>
              </div>
          </div>
          <button id="btnToggleFilter" onclick="toggleFilter()" class="hidden h-9 px-3 bg-white border border-slate-300 rounded-lg text-xs font-bold items-center gap-2">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
              Filter
          </button>
      </div>

      <div id="filterPanelAO">
          <div class="filter-group">
              <label class="lbl">Closing</label>
              <input type="date" id="tgl_awal" class="inp inp-mini">
          </div>
          <div class="filter-group">
              <label class="lbl">Harian</label>
              <input type="date" id="tgl_akhir" class="inp inp-mini">
          </div>
          <div class="filter-group">
              <label class="lbl">Kantor</label>
              <select id="filter_kantor" class="inp inp-mini !w-[160px]"></select>
          </div>
          <div class="flex gap-1.5 mt-auto">
              <button onclick="fetchTopData(1)" class="h-[34px] px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold text-xs flex items-center gap-2 transition">
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                  CARI
              </button>
              <button onclick="exportFullData()" class="h-[34px] w-10 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg flex items-center justify-center transition">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
              </button>
          </div>
      </div>
    </div>

    <div id="top50Scroller" class="shadow-sm flex flex-col relative">
        <div id="loadingAO" class="hidden absolute inset-0 bg-white/80 z-50 flex flex-col items-center justify-center text-blue-600 font-bold backdrop-blur-sm">
            <div class="animate-spin h-8 w-8 border-4 border-blue-200 border-t-blue-600 rounded-full mb-2"></div>
            <span class="text-[10px] uppercase tracking-widest">Memproses...</span>
        </div>

        <div class="flex-1 overflow-auto">
            <table id="tabelAO">
                <thead>
                    <tr>
                        <th class="text-center w-10">NO</th>
                        <th class="w-48 text-left">NAMA KANTOR</th>
                        <th>NAMA AO (MARKETING)</th>
                        <th class="text-center w-24">NOA</th>
                        <th class="text-right w-44 bg-emerald-50 text-emerald-800">TOTAL REALISASI</th>
                    </tr>
                </thead>
                <tbody id="tbodyAO" class="divide-y divide-slate-100"></tbody>
            </table>
        </div>

        <div id="paginationWrap" class="p-3 bg-slate-50 border-t border-slate-200 flex items-center justify-between hidden">
            <span class="text-[10px] text-slate-500 font-bold uppercase" id="pageInfo">Hal 1 / 1</span>
            <div class="flex gap-1">
                <button id="btnPrev" onclick="changePage(-1)" class="h-8 px-3 bg-white border border-slate-300 rounded-lg text-[10px] font-black disabled:opacity-30">PREV</button>
                <button id="btnNext" onclick="changePage(1)" class="h-8 px-3 bg-white border border-slate-300 rounded-lg text-[10px] font-black disabled:opacity-30">NEXT</button>
            </div>
        </div>
    </div>
  </div>
</div>

<div id="modalDetail" class="fixed inset-0 hidden bg-slate-900/60 backdrop-blur-sm items-center justify-center z-[9999] px-2">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[85vh] flex flex-col overflow-hidden">
        <div class="p-4 border-b flex justify-between items-center bg-slate-50">
            <h3 class="font-bold text-slate-800" id="modalSub">Detail Realisasi</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-red-500 font-bold text-xl">✕</button>
        </div>
        <div class="flex-1 overflow-auto">
            <table class="w-full text-xs text-left border-separate border-spacing-0">
                <thead class="sticky top-0 bg-white shadow-sm font-bold uppercase text-slate-500 text-[10px]">
                    <tr>
                        <th class="px-4 py-3 border-b text-center">No</th>
                        <th class="px-4 py-3 border-b">Nama Nasabah</th>
                        <th class="px-4 py-3 border-b text-center">Rekening</th>
                        <th class="px-4 py-3 border-b text-center">Tanggal</th>
                        <th class="px-4 py-3 border-b text-right bg-emerald-50">Plafond</th>
                    </tr>
                </thead>
                <tbody id="modalBody" class="divide-y divide-slate-100"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
  let currentPage = 1, totalPage = 1;
  const id = (x) => document.getElementById(x);
  const fmt = (n) => new Intl.NumberFormat("id-ID").format(+n||0);

  window.addEventListener('DOMContentLoaded', async () => {
    // 1. Set Tanggal Default
    const d = new Date();
    id("tgl_akhir").value = d.toISOString().split('T')[0];
    d.setDate(1);
    id("tgl_awal").value = d.toISOString().split('T')[0];

    // 2. Integrasi Login User & Dropdown
    await populateKantor();
    
    // 3. Fetch Data Awal
    fetchTopData(1);
  });

  function toggleFilter() { id('filterPanelAO').classList.toggle('active'); }

  async function populateKantor() {
      const el = id('filter_kantor');
      
      // Ambil user login (Sesuaikan dengan cara brother menyimpan data user)
      const user = (window.getUser && window.getUser()) || JSON.parse(localStorage.getItem('user')) || { kode: '000' };
      const userKode = String(user.kode || '000').padStart(3, '0');

      try {
          const r = await fetch('./api/kode/', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ type: 'kode_kantor' }) });
          const j = await r.json();
          const listKantor = j.data || [];

          if (userKode === '000') {
              // PUSAT: Bisa akses semua
              let h = '<option value="000">KONSOLIDASI (SEMUA)</option>';
              listKantor.filter(x => x.kode_kantor !== '000').forEach(x => { 
                  h += `<option value="${x.kode_kantor}">${x.kode_kantor} - ${x.nama_kantor}</option>`; 
              });
              el.innerHTML = h;
              el.disabled = false;
          } else {
              // CABANG: Hanya flag cabang dia sendiri
              const cabangUser = listKantor.find(x => String(x.kode_kantor).padStart(3, '0') === userKode);
              if (cabangUser) {
                  el.innerHTML = `<option value="${cabangUser.kode_kantor}">${cabangUser.kode_kantor} - ${cabangUser.nama_kantor}</option>`;
              } else {
                  el.innerHTML = `<option value="${userKode}">CABANG ${userKode}</option>`;
              }
              el.disabled = true; // Kunci dropdown
          }
      } catch (e) { 
          el.innerHTML = '<option value="000">KONSOLIDASI</option>'; 
      }
  }

  function fetchTopData(page) {
    currentPage = page;
    id("loadingAO").classList.remove("hidden");
    const tbody = id("tbodyAO");
    
    if(window.innerWidth < 1024) id('filterPanelAO').classList.remove('active');
    tbody.innerHTML = `<tr><td colspan="5" class="text-center py-20 text-slate-400">Loading data...</td></tr>`;

    const payload = { 
        type: "top realisasi", 
        closing_date: id("tgl_awal").value, 
        harian_date: id("tgl_akhir").value,
        kode_kantor: id("filter_kantor").value,
        page: page
    };

    fetch('./api/kredit/', { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify(payload) })
    .then(res => res.json())
    .then(res => {
      const list = res.data?.data || [];
      const pag = res.data?.pagination || {};
      renderTable(list, (page - 1) * 10);
      updatePaginationUI(pag);
    })
    .finally(() => id("loadingAO").classList.add("hidden"));
  }

  function renderTable(data, startIdx) {
    const tbody = id("tbodyAO");
    tbody.innerHTML = data.length ? "" : `<tr><td colspan="5" class="text-center py-12 text-slate-500 font-bold">DATA KOSONG</td></tr>`;
    data.forEach((row, i) => {
      tbody.insertAdjacentHTML('beforeend', `
          <tr class="hover:bg-blue-50/40 transition">
              <td class="text-center font-mono text-slate-400 border-r border-slate-50">${startIdx + i + 1}</td>
              <td class="font-bold text-slate-500 border-r border-slate-50 uppercase text-[10px]">${row.kode_cabang} - ${row.nama_kantor || 'CABANG'}</td>
              <td class="border-r border-slate-50 font-bold text-slate-700">
                  <span class="ao-link" onclick="openDetailAO('${row.kode_ao}', '${row.nama_ao}')">${row.nama_ao}</span>
              </td>
              <td class="text-center font-black text-slate-600 border-r border-slate-50">${row.total_noa}</td>
              <td class="text-right font-black text-emerald-700 bg-emerald-50/30">Rp ${fmt(row.total_realisasi)}</td>
          </tr>`);
    });
  }

  async function openDetailAO(kodeAO, namaAO) {
      id('modalDetail').classList.replace('hidden', 'flex');
      id('modalSub').innerText = `AO: ${namaAO} (${kodeAO})`;
      const body = id('modalBody');
      body.innerHTML = `<tr><td colspan="5" class="text-center py-12 italic">Loading...</td></tr>`;

      try {
          const r = await fetch('./api/kredit/', { method: "POST", headers: { "Content-Type": "application/json" }, 
            body: JSON.stringify({ type: "detail realisasi ao", kode_ao: kodeAO, closing_date: id('tgl_awal').value, harian_date: id('tgl_akhir').value, kode_kantor: id('filter_kantor').value }) 
          });
          const j = await r.json();
          body.innerHTML = (j.data || []).map((d, i) => `
              <tr class="border-b transition hover:bg-slate-50">
                  <td class="px-4 py-3 text-center text-slate-400 font-mono">${i+1}</td>
                  <td class="px-4 py-3 font-bold text-slate-700">${d.nama_nasabah}</td>
                  <td class="px-4 py-3 text-center font-mono text-slate-500">${d.no_rekening}</td>
                  <td class="px-4 py-3 text-center">${d.tanggal_realisasi}</td>
                  <td class="px-4 py-3 text-right font-bold text-emerald-700 bg-emerald-50/20">Rp ${fmt(d.plafond)}</td>
              </tr>`).join('');
      } catch { body.innerHTML = `<tr><td colspan="5" class="text-center text-red-500">Error load detail</td></tr>`; }
  }

  async function exportFullData() {
      const btn = document.querySelector('button[onclick="exportFullData()"]');
      const original = btn.innerHTML; btn.innerHTML = '...'; btn.disabled = true;
      try {
          const r = await fetch('./api/kredit/', { method: "POST", headers: { "Content-Type": "application/json" }, 
            body: JSON.stringify({ type: "top realisasi", closing_date: id('tgl_awal').value, harian_date: id('tgl_akhir').value, kode_kantor: id('filter_kantor').value, limit: 1000 }) 
          });
          const res = await r.json();
          const list = res.data?.data || [];
          let csv = 'KANTOR,KODE AO,NAMA AO,NOA,REALISASI\n';
          list.forEach(row => { csv += `${row.kode_cabang},'${row.kode_ao},${row.nama_ao},${row.total_noa},${row.total_realisasi}\n`; });
          const blob = new Blob([csv], { type: 'text/csv' });
          const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = `REKAP_AO.csv`; a.click();
      } finally { btn.innerHTML = original; btn.disabled = false; }
  }

  function updatePaginationUI(p) {
      const wrap = id('paginationWrap');
      if(!p.is_konsolidasi) { wrap.classList.add('hidden'); return; }
      wrap.classList.remove('hidden');
      totalPage = p.total_page;
      id('pageInfo').innerText = `Hal ${p.current_page} / ${p.total_page}`;
      id('btnPrev').disabled = p.current_page <= 1;
      id('btnNext').disabled = p.current_page >= p.total_page;
  }

  function changePage(dir) { let t = currentPage + dir; if(t >= 1 && t <= totalPage) fetchTopData(t); }
  function closeModal() { id('modalDetail').classList.replace('flex', 'hidden'); }
</script>