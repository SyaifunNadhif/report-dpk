<div class="max-w-7xl mx-auto px-4 py-4 h-screen flex flex-col font-sans bg-slate-50">
  
  <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-3 shrink-0">
    <div class="flex items-start justify-between w-full md:w-auto">
      <div>
        <h1 class="text-xl md:text-2xl font-bold flex items-center gap-2 text-slate-800">
          <span>🔥</span><span>25 Debitur Terbesar NPL</span>
        </h1>
        <p class="text-[10px] md:text-xs text-slate-500 mt-1 ml-1 font-medium">
          *Berdasarkan posisi Nominatif Closing Bulan Lalu
        </p>
      </div>

      <button id="btnToggleNplFilter" class="md:hidden flex items-center gap-1.5 px-3 py-1.5 border border-gray-300 rounded-lg bg-white text-sm font-semibold text-slate-700 shadow-sm hover:bg-gray-50 focus:outline-none transition">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
          Filter
      </button>
    </div>

    <div id="filterPanelNpl" class="hidden md:block bg-white border border-gray-200 rounded-xl p-3 shadow-sm w-full md:w-auto transition-all origin-top">
      <form id="formFilterTopNpl" class="flex flex-row items-center gap-3 w-full">
        <div class="flex flex-col flex-1 md:w-[250px]">
            <label class="text-[10px] font-extrabold text-slate-500 uppercase ml-1 mb-1 tracking-wider">PILIH KANTOR</label>
            <select id="selCabangNpl" class="inp font-medium text-slate-700 shadow-sm truncate h-9">
              <option value="">konsolidasi</option>
            </select>
        </div>
        
        <div class="flex items-end h-full pt-5">
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white h-9 w-10 md:w-11 rounded-lg font-bold shadow-sm flex items-center justify-center transition" title="Tampilkan Data">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" x2="16.65" y2="16.65"></line></svg>
          </button>
        </div>
      </form>
    </div>
  </div>

  <div id="loadingTop" class="hidden flex items-center gap-2 text-sm text-blue-600 font-bold mb-2 ml-1">
    <div class="animate-spin h-4 w-4 border-2 border-blue-200 border-t-blue-600 rounded-full"></div>
    <span>Memuat data...</span>
  </div>

  <div id="nplScroller" class="flex-1 min-h-0 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm relative">
    <div class="h-full overflow-auto" id="nplScrollInner">
      <table id="tabelTopNpl" class="min-w-full text-[12px] text-left text-gray-700 border-separate border-spacing-0">
        <thead class="uppercase">
          <tr id="nplHead">
            <th class="px-3 py-2 sticky top-0 z-40 col-namakantor border-b border-r">NAMA KANTOR</th>
            <th class="px-3 py-2 sticky top-0 z-50 freeze-1 col-norek border-b border-r">NO REKENING</th>
            <th class="px-3 py-2 sticky top-0 z-40 freeze-2 col-debitur border-b border-r">NAMA DEBITUR</th>
            <th class="px-3 py-2 text-right sticky top-0 z-30 col-amt border-b border-r">PLAFOND</th>
            <th class="px-3 py-2 text-right sticky top-0 z-30 col-amt border-b border-r">BAKI DEBET</th>
            <th class="px-3 py-2 text-right sticky top-0 z-30 col-pct border-b border-r" title="Kontribusi terhadap Total NPL (%)">%</th>
            <th class="px-3 py-2 text-right sticky top-0 z-30 col-amt border-b border-r">T.POKOK</th>
            <th class="px-3 py-2 text-right sticky top-0 z-30 col-amt border-b border-r">T.BUNGA</th>
            <th class="px-3 py-2 text-center sticky top-0 z-30 col-kol border-b border-r">KOLEK</th>
            <th class="px-3 py-2 text-center sticky top-0 z-30 col-kol border-b border-r">UPDATE</th>
            <th class="px-3 py-2 text-right sticky top-0 z-30 col-amt border-b border-r">ANGS POKOK</th>
            <th class="px-3 py-2 text-right sticky top-0 z-30 col-amt border-b border-r">ANGS BUNGA</th>
            <th class="px-3 py-2 text-center sticky top-0 z-30 col-date border-b">TGL TRANS</th>
          </tr>
        </thead>
        <tbody id="tbTotalNpl"></tbody>
        <tbody id="bodyTopNpl"></tbody>
      </table>
      <div class="bottom-spacer" style="height: 60px;"></div>
    </div>
  </div>
</div>

<style>
  .inp { border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 0 0.75rem; font-size: 13px; background: #fff; width: 100%; outline: none; transition: border 0.2s; }
  .inp:focus { border-color: #2563eb; ring: 2px solid #bfdbfe; }
  
  #tabelTopNpl thead th { background: #d9ead3 !important; color: #1e293b; font-weight: 700; border-color: #cbd5e1; }
  
  .freeze-1 { position: sticky; left: 0; background: #fff; border-right: 1px solid #e2e8f0; }
  .freeze-2 { position: sticky; left: 7.5rem; background: #fff; border-right: 1px solid #e2e8f0; box-shadow: 2px 0 5px rgba(0,0,0,0.03); }
  
  .col-namakantor { width: 10rem; }
  .col-norek { width: 7.5rem; }
  .col-debitur { width: 13rem; overflow: hidden; text-overflow: ellipsis; }
  .col-amt { width: 8.5rem; text-align: right; }
  .col-pct { width: 4.5rem; text-align: right; }
  .col-kol { width: 5rem; text-align: center; }
  .col-date { width: 7rem; text-align: center; }

  #tbTotalNpl tr td { 
    position: sticky; top: var(--headH, 36px); z-index: 25; 
    background: #f0f7ff; color: #1e40af; font-weight: 700; border-bottom: 2px solid #bfdbfe; 
  }
  #tbTotalNpl tr td.freeze-1 { z-index: 26; background: #f0f7ff; }
  #tbTotalNpl tr td.freeze-2 { z-index: 26; background: #f0f7ff; }

  @media (max-width: 640px) {
    .freeze-1 { display: none !important; }
    .freeze-2 { left: 0 !important; width: 8.5rem; min-width: 8.5rem; white-space: normal; line-height: 1.2; }
    .col-amt { width: 7.5rem; }
    .col-pct { width: 4rem; }
  }
</style>

<script>
  let StateDate = { closing: '', harian: '' };
  const nfID = new Intl.NumberFormat('id-ID');
  const fmt = n => nfID.format(Number(n||0));
  const selCabang = document.getElementById('selCabangNpl');

  document.getElementById('btnToggleNplFilter').addEventListener('click', function() {
      document.getElementById('filterPanelNpl').classList.toggle('hidden');
  });

  selCabang.addEventListener('change', () => { document.getElementById('formFilterTopNpl').requestSubmit(); });

  window.addEventListener('DOMContentLoaded', async () => {
    try {
        const d = await (await fetch('./api/date/')).json();
        if (d.data) { StateDate.closing = d.data.last_closing; StateDate.harian = d.data.last_created; }
    } catch(e) {}

    const user = (window.getUser && window.getUser()) || null;
    const uKode = user?.kode_kantor ? String(user.kode_kantor).padStart(3,'0') : (user?.kode ? String(user.kode).padStart(3,'0') : '000');

    await populateKantorOptions(uKode);
    fetchTop25Npl(uKode === '000' ? '' : uKode);
    setHeadHeight();
  });

  async function populateKantorOptions(userKode) {
    if (userKode !== '000') {
      selCabang.innerHTML = `<option value="${userKode}">CABANG ${userKode}</option>`;
      selCabang.disabled = true;
      return;
    }
    try {
        const res = await fetch('./api/kode/', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({type:'kode_kantor'}) });
        const json = await res.json();
        let html = `<option value="">konsolidasi</option>`;
        (json.data || []).filter(x => x.kode_kantor !== '000').sort((a,b) => String(a.kode_kantor).localeCompare(b.kode_kantor)).forEach(it => {
            html += `<option value="${it.kode_kantor}">${it.kode_kantor} - ${it.nama_kantor}</option>`;
        });
        selCabang.innerHTML = html;
    } catch(e) { selCabang.innerHTML = `<option value="">konsolidasi</option>`; }
  }

  document.getElementById("formFilterTopNpl").addEventListener("submit", (e) => {
    e.preventDefault();
    if(window.innerWidth < 768) document.getElementById('filterPanelNpl').classList.add('hidden');
    fetchTop25Npl(selCabang.value);
  });

  async function fetchTop25Npl(kode) {
    const loading = document.getElementById('loadingTop');
    loading.classList.remove('hidden');
    try {
      const res = await fetch("./api/npl/", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ type: "25 NPL Terbesar", closing_date: StateDate.closing, harian_date: StateDate.harian, kode_cabang: kode })
      });
      const j = await res.json();
      renderTable(j.data || []);
    } catch { renderTable([]); }
    finally { loading.classList.add('hidden'); setTimeout(setHeadHeight, 50); }
  }

  function renderTable(rows) {
    const tb = document.getElementById('bodyTopNpl');
    const ttot = document.getElementById('tbTotalNpl');
    tb.innerHTML = ''; ttot.innerHTML = '';

    const sum = k => rows.reduce((s, r) => s + Number(r[k] || 0), 0);
    const totalPlaf = sum('jml_pinjaman');
    const totalBaki = sum('baki_debet');
    const totalPersen = sum('persen_npl'); // Ambil dari BE baru
    
    ttot.innerHTML = `
      <tr>
        <td class="px-3 py-2 border-r"></td>
        <td class="px-3 py-2 freeze-1 border-r"></td>
        <td class="px-3 py-2 freeze-2 font-bold border-r">TOTAL</td>
        <td class="px-3 py-2 text-right border-r">${fmt(totalPlaf)}</td>
        <td class="px-3 py-2 text-right border-r">${fmt(totalBaki)}</td>
        <td class="px-3 py-2 text-right font-bold text-blue-900 border-r">${totalPersen.toFixed(1)}%</td>
        <td class="px-3 py-2 text-right border-r">${fmt(sum('tunggakan_pokok'))}</td>
        <td class="px-3 py-2 text-right border-r">${fmt(sum('tunggakan_bunga'))}</td>
        <td colspan="2" class="border-r"></td>
        <td class="px-3 py-2 text-right border-r">${fmt(sum('total_pokok'))}</td>
        <td class="px-3 py-2 text-right border-r">${fmt(sum('total_bunga'))}</td>
        <td class="px-3 py-2"></td>
      </tr>`;

    if(rows.length === 0) {
        tb.innerHTML = `<tr><td colspan="13" class="py-10 text-center text-slate-400 font-medium">Data tidak ditemukan.</td></tr>`;
        return;
    }

    rows.forEach(r => {
      const pNpl = Number(r.persen_npl || 0);
      const pctColor = pNpl >= 5 ? 'text-blue-700 font-bold' : 'text-slate-500';

      tb.insertAdjacentHTML('beforeend', `
        <tr class="hover:bg-slate-50 border-b transition">
          <td class="px-3 py-2 truncate border-r border-slate-100" title="${r.nama_kantor}">${r.nama_kantor}</td>
          <td class="px-3 py-2 col-norek freeze-1 font-mono text-slate-500 border-r border-slate-100">${r.no_rekening}</td>
          <td class="px-3 py-2 col-debitur freeze-2 font-semibold text-slate-700 border-r border-slate-100">${r.nama_nasabah}</td>
          <td class="px-3 py-2 text-right border-r border-slate-100">${fmt(r.jml_pinjaman)}</td>
          <td class="px-3 py-2 text-right font-bold text-blue-700 border-r border-slate-100">${fmt(r.baki_debet)}</td>
          <td class="px-3 py-2 text-right ${pctColor} border-r border-slate-100">${pNpl.toFixed(1)}%</td>
          <td class="px-3 py-2 text-right border-r border-slate-100">${fmt(r.tunggakan_pokok)}</td>
          <td class="px-3 py-2 text-right border-r border-slate-100">${fmt(r.tunggakan_bunga)}</td>
          <td class="px-3 py-2 text-center font-bold text-slate-400 border-r border-slate-100">${r.kolek_closing||''}</td>
          <td class="px-3 py-2 text-center font-bold text-red-600 border-r border-slate-100">${r.kolek_harian||''}</td>
          <td class="px-3 py-2 text-right border-r border-slate-100">${fmt(r.total_pokok)}</td>
          <td class="px-3 py-2 text-right border-r border-slate-100">${fmt(r.total_bunga)}</td>
          <td class="px-3 py-2 text-center text-slate-500">${r.tgl_trans || "-"}</td>
        </tr>`);
    });
  }

  function setHeadHeight() {
    const h = document.getElementById('nplHead')?.offsetHeight || 36;
    document.getElementById('nplScroller')?.style.setProperty('--headH', h + 'px');
  }
  window.addEventListener('resize', setHeadHeight);
</script>