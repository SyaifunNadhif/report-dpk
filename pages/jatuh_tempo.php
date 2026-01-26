<div class="max-w-7xl mx-auto px-4 py-6 h-screen flex flex-col">
  
  <div class="mb-4">
    <h1 class="text-2xl font-bold flex items-center gap-2 text-gray-800">
      <span>💳</span><span>Rekap Jatuh Tempo & Top Up</span>
    </h1>
  </div>

  <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 mb-4">
    
    <div id="summaryWrapJT" class="flex flex-wrap items-center gap-2 text-sm animate-fade-in order-2 lg:order-1">
      <span class="pill pill-blue">JT: <b id="sum_noa_lama">0</b> NOA (<b id="sum_plaf_lama">0</b>)</span>
      <span class="pill pill-green">Top Up: <b id="sum_noa_baru">0</b> NOA (<b id="sum_plaf_baru">0</b>)</span>
      <span class="pill pill-purple">Retensi: <b id="sum_persen">0%</b></span>
      <span class="pill pill-amber">Sisa BD: <b id="sum_bd">0</b></span>
    </div>

    <form id="formFilterJT" class="flex flex-wrap items-end gap-2 order-1 lg:order-2 ml-auto bg-white p-2 rounded-lg border border-gray-200 shadow-sm">
      
      <div class="field">
        <label class="lbl">Closing</label>
        <input type="date" id="closing_date_jt" class="inp py-1" required>
      </div>
      
      <div class="field">
        <label class="lbl">Harian</label>
        <input type="date" id="harian_date_jt" class="inp py-1" required>
      </div>
      
      <div class="field">
        <label class="lbl">Bulan</label>
        <select id="filter_bulan" class="inp py-1 min-w-[100px]">
            <option value="01">Januari</option>
            <option value="02">Februari</option>
            <option value="03">Maret</option>
            <option value="04">April</option>
            <option value="05">Mei</option>
            <option value="06">Juni</option>
            <option value="07">Juli</option>
            <option value="08">Agustus</option>
            <option value="09">September</option>
            <option value="10">Oktober</option>
            <option value="11">November</option>
            <option value="12">Desember</option>
        </select>
      </div>

      <div class="flex items-end gap-1">
          <div class="field">
            <label class="lbl">Tahun</label>
            <input type="number" id="filter_tahun" class="inp py-1 w-[70px]" placeholder="YYYY">
          </div>
          
          <button type="submit" id="btnFilterJT" class="h-[34px] w-[34px] flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white rounded-md transition shadow-sm mb-[1px]" title="Cari Data">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5">
              <circle cx="11" cy="11" r="7"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
          </button>
      </div>

    </form>
  </div>

  <div id="jtScroller" class="flex-1 min-h-0 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm relative">
    <div id="loadingJT" class="hidden absolute inset-0 bg-white/80 z-50 flex flex-col items-center justify-center text-sm text-blue-600 font-medium">
        <svg class="animate-spin h-8 w-8 mb-2 text-blue-500" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        <span>Sedang memuat data...</span>
    </div>

    <div class="h-full overflow-auto">
      <table id="tabelJT" class="min-w-full text-sm text-left text-gray-700 border-separate border-spacing-0">
        <thead class="uppercase text-xs text-gray-600 font-bold bg-gray-100 sticky top-0 z-30 shadow-sm">
          <tr>
            <th class="px-4 py-3 sticky left-0 bg-gray-100 z-40 border-b border-r w-[60px] md:w-[80px]">Kode</th>
            
            <th class="px-4 py-3 bg-gray-100 border-b border-r min-w-[200px] 
                       md:sticky md:left-[80px] md:z-40 md:shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                Nama Kantor
            </th>
            
            <th class="px-4 py-3 bg-blue-50 text-right text-blue-800 border-b min-w-[100px]">NOA<br><small>(Lama)</small></th>
            <th class="px-4 py-3 bg-blue-50 text-right text-blue-800 border-b min-w-[140px]">Plafon<br><small>(Lama)</small></th>
            <th class="px-4 py-3 bg-green-50 text-right text-green-800 border-b min-w-[100px]">NOA<br><small>(Baru)</small></th>
            <th class="px-4 py-3 bg-green-50 text-right text-green-800 border-b min-w-[140px]">Plafon<br><small>(Baru)</small></th>
            <th class="px-4 py-3 bg-gray-50 text-right border-b min-w-[140px]">Sisa<br>Baki Debet</th>
            <th class="px-4 py-3 bg-gray-50 text-right border-b min-w-[80px]">% Growth</th>
          </tr>
        </thead>
        <tbody id="bodyJT" class="divide-y divide-gray-100"></tbody>
      </table>
    </div>
  </div>
</div>

<div id="modalDetailJT" class="fixed inset-0 hidden z-[9999] flex items-center justify-center p-4">
  <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeModalJT()"></div>
  
  <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-6xl max-h-[90vh] flex flex-col animate-scale-up">
    <div class="flex items-center justify-between p-5 border-b bg-white rounded-t-xl">
      <div>
        <h3 class="text-xl font-bold text-gray-800" id="modalTitleJT">Detail Nasabah</h3>
        <p class="text-sm text-gray-500" id="modalSubTitleJT">List debitur jatuh tempo</p>
      </div>
      <button onclick="closeModalJT()" class="text-gray-400 hover:text-gray-700 transition-colors">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
      </button>
    </div>

    <div class="flex-1 overflow-auto bg-gray-50 relative min-h-[300px]">
      <table class="w-full text-sm text-left text-gray-700">
        <thead class="text-xs text-gray-500 uppercase bg-gray-100 sticky top-0 shadow-sm z-10">
          <tr>
            <th class="px-4 py-3 bg-gray-100">No Rekening</th>
            <th class="px-4 py-3 bg-gray-100">Nama Nasabah</th>
            <th class="px-4 py-3 text-right bg-gray-100">Plafon Lama</th>
            <th class="px-4 py-3 text-right bg-green-50 text-green-700 border-l border-green-100">Plafon Baru</th>
            <th class="px-4 py-3 text-right bg-gray-100">Sisa BD</th>
            <th class="px-4 py-3 text-center bg-gray-100">Tgl JT</th>
            <th class="px-4 py-3 text-center bg-gray-100">Status</th>
            <th class="px-4 py-3 bg-gray-100">Ket</th>
          </tr>
        </thead>
        <tbody id="bodyModalJT" class="divide-y divide-gray-200 bg-white"></tbody>
      </table>
      
      <div id="loadingModalJT" class="hidden absolute inset-0 bg-white/90 flex flex-col items-center justify-center text-gray-500 z-20">
         <span class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-500 border-t-transparent mb-2"></span>
         <span>Mengambil data detail...</span>
      </div>
    </div>

    <div class="p-4 border-t bg-white rounded-b-xl flex flex-wrap justify-between items-center gap-2">
      <span class="text-xs text-gray-500 font-medium order-2 sm:order-1" id="pageInfoJT">Menampilkan 0 - 0 dari 0 data</span>
      <div class="flex items-center gap-2 order-1 sm:order-2 ml-auto sm:ml-0">
          <button id="btnPrevJT" onclick="changePageDetail(-1)" class="px-3 py-1.5 bg-white border border-gray-300 rounded hover:bg-gray-50 text-xs font-medium disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center gap-1 shadow-sm">
            <span>&larr;</span> Prev
          </button>
          <button id="btnNextJT" onclick="changePageDetail(1)" class="px-3 py-1.5 bg-white border border-gray-300 rounded hover:bg-gray-50 text-xs font-medium disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center gap-1 shadow-sm">
            Next <span>&rarr;</span>
          </button>
          <div class="h-4 w-px bg-gray-300 mx-2"></div>
          <button onclick="closeModalJT()" class="px-4 py-1.5 bg-gray-800 text-white rounded hover:bg-gray-900 text-xs font-medium transition shadow-sm">Tutup</button>
      </div>
    </div>
  </div>
</div>

<style>
  /* Styling Form Inputs */
  .inp { border:1px solid #cbd5e1; border-radius:.375rem; padding-left:.5rem; padding-right:.5rem; font-size:13px; background:#fff; transition: all 0.2s; }
  .inp:focus { outline:none; border-color:#2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,0.1); }
  .lbl { font-size:10px; color:#64748b; font-weight: 700; display: block; margin-bottom: 2px; text-transform: uppercase; letter-spacing: 0.02em; }
  .field { display: flex; flex-direction: column; }

  /* Pills */
  .pill { display:inline-block; padding:4px 10px; border-radius:99px; border:1px solid; font-size:11px; font-weight:600; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
  .pill-blue { background:#eff6ff; color:#1e40af; border-color:#bfdbfe; }
  .pill-green { background:#ecfdf5; color:#065f46; border-color:#a7f3d0; }
  .pill-purple { background:#faf5ff; color:#6b21a8; border-color:#e9d5ff; }
  .pill-amber { background:#fff7ed; color:#9a3412; border-color:#fed7aa; }

  /* Animations */
  @keyframes scaleUp { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
  .animate-scale-up { animation: scaleUp 0.2s ease-out forwards; }
  .animate-fade-in { animation: fadeIn 0.3s ease-in; }
  @keyframes fadeIn { from{opacity:0} to{opacity:1} }
</style>

<script>
  // --- CONFIG ---
  const API_JT_URL = './api/jt/'; 
  const nfID = new Intl.NumberFormat('id-ID');
  const fmt  = n => nfID.format(Number(n||0));
  const apiCall = (url, opt={}) => (window.apiFetch ? window.apiFetch(url,opt) : fetch(url,opt));

  let abortJT;
  
  // -- STATE UNTUK MODAL DETAIL --
  let currentDetailParams = {}; // Menyimpan filter saat ini (kode kantor, bulan, dll)
  let currentDetailPage = 1;
  let currentDetailTotalPages = 1;
  const detailLimit = 10; // Sesuai request (load 10 per halaman)

  // --- INIT ---
  window.addEventListener('DOMContentLoaded', async () => {
    const d = await getLastHarianData(); 
    if(d) {
        document.getElementById('closing_date_jt').value = d.last_closing;
        document.getElementById('harian_date_jt').value  = d.last_created;
        const dateObj = new Date(d.last_created);
        document.getElementById('filter_bulan').value = String(dateObj.getMonth() + 1).padStart(2, '0');
    } else {
        const now = new Date();
        document.getElementById('filter_bulan').value = String(now.getMonth() + 1).padStart(2, '0');
    }
    document.getElementById('filter_tahun').value = new Date().getFullYear();

    fetchRekapJT();
  });

  async function getLastHarianData(){
    try{
        const r=await apiCall('./api/date/'); 
        const j=await r.json(); 
        return j.data||null;
    }catch{ return null; }
  }

  document.getElementById('formFilterJT').addEventListener('submit', e => {
    e.preventDefault();
    fetchRekapJT();
  });

  // --- 1. FETCH REKAP (Tabel Utama) ---
  async function fetchRekapJT(){
    const loading = document.getElementById('loadingJT');
    const tbody   = document.getElementById('bodyJT');
    const summary = document.getElementById('summaryWrapJT');
    
    const user = (window.getUser && window.getUser()) || null;
    const userKode = (user?.kode ? String(user.kode).padStart(3,'0') : '000');

    const closing = document.getElementById('closing_date_jt').value;
    const harian  = document.getElementById('harian_date_jt').value;
    const bulan   = document.getElementById('filter_bulan').value;
    const tahun   = document.getElementById('filter_tahun').value;

    if(abortJT) abortJT.abort();
    abortJT = new AbortController();

    loading.classList.remove('hidden');
    summary.classList.add('hidden');
    tbody.innerHTML = `<tr><td colspan="8" class="py-10 text-center text-gray-500 italic">Memuat data rekapitulasi...</td></tr>`;

    try {
        const kodeFilter = (userKode !== '000') ? userKode : null;

        const payload = { 
            type: 'rekap prospek jatuh tempo', 
            closing_date: closing, 
            harian_date: harian,
            bulan: bulan,
            tahun: tahun,
            kode_kantor: kodeFilter 
        };
        
        const res = await apiCall(API_JT_URL, {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify(payload),
            signal: abortJT.signal
        });
        const json = await res.json();
        
        if(json.status !== 200) throw new Error(json.message);

        let rows = json.data.rekap_per_cabang || [];
        if (userKode !== '000') {
            rows = rows.filter(r => r.kode_kantor === userKode);
        }

        renderTableJT(rows, userKode);
        
    } catch(err) {
        if(err.name !== 'AbortError') {
            tbody.innerHTML=`<tr><td colspan="8" class="py-10 text-center text-red-500 font-medium">${err.message||'Gagal memuat data'}</td></tr>`;
        }
    } finally {
        loading.classList.add('hidden');
    }
  }

  function renderTableJT(rows, userKode) {
      const tbody = document.getElementById('bodyJT');
      tbody.innerHTML = '';

      if(rows.length === 0){
          tbody.innerHTML = `<tr><td colspan="8" class="py-10 text-center text-gray-400">Tidak ada data ditemukan.</td></tr>`;
          return;
      }

      let T = { noa_lama:0, plafon_lama:0, noa_baru:0, plafon_baru:0, baki_debet:0 };
      
      let html = '';
      rows.forEach(r => {
          T.noa_lama    += Number(r.noa_lama);
          T.plafon_lama += Number(r.plafon_lama);
          T.noa_baru    += Number(r.noa_baru);
          T.plafon_baru += Number(r.plafon_baru);
          T.baki_debet  += Number(r.baki_debet);

          const clickHtml = `<a href="javascript:void(0)" onclick="initModalDetail('${r.kode_kantor}', '${r.nama_kantor}')" 
                             class="font-bold text-blue-600 hover:text-blue-800 hover:underline cursor-pointer transition">
                             ${fmt(r.noa_lama)}
                             </a>`;

          html += `
            <tr class="hover:bg-blue-50 transition border-b group">
                <td class="px-4 py-3 text-center font-mono text-gray-500 text-xs sticky left-0 bg-white z-10 border-r">${r.kode_kantor}</td>
                <td class="px-4 py-3 font-medium text-gray-800 border-r md:sticky md:left-[80px] md:bg-white md:z-10 md:shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] bg-transparent">
                    ${r.nama_kantor}
                </td>
                <td class="px-4 py-3 text-right bg-blue-50/40 group-hover:bg-blue-100/50">${clickHtml}</td>
                <td class="px-4 py-3 text-right bg-blue-50/40 group-hover:bg-blue-100/50 text-gray-600">${fmt(r.plafon_lama)}</td>
                <td class="px-4 py-3 text-right bg-green-50/40 group-hover:bg-green-100/50 font-semibold text-gray-800">${fmt(r.noa_baru)}</td>
                <td class="px-4 py-3 text-right bg-green-50/40 group-hover:bg-green-100/50 text-gray-600">${fmt(r.plafon_baru)}</td>
                <td class="px-4 py-3 text-right font-mono text-gray-700">${fmt(r.baki_debet)}</td>
                <td class="px-4 py-3 text-right font-bold ${r.persentase >= 100 ? 'text-green-600' : 'text-orange-500'}">
                    ${r.persentase}%
                </td>
            </tr>
          `;
      });

      if(userKode === '000' && rows.length > 1) {
          const grandPersen = (T.plafon_lama > 0) ? (T.plafon_baru / T.plafon_lama * 100) : 0;
          html += `
            <tr class="bg-gray-100 font-bold border-t-2 border-gray-300 text-gray-900 shadow-sm">
                <td class="px-4 py-3 text-center sticky left-0 bg-gray-100 z-10 border-r">-</td>
                <td class="px-4 py-3 border-r md:sticky md:left-[80px] md:bg-gray-100 md:z-10 md:shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">GRAND TOTAL</td>
                <td class="px-4 py-3 text-right">${fmt(T.noa_lama)}</td>
                <td class="px-4 py-3 text-right">${fmt(T.plafon_lama)}</td>
                <td class="px-4 py-3 text-right">${fmt(T.noa_baru)}</td>
                <td class="px-4 py-3 text-right">${fmt(T.plafon_baru)}</td>
                <td class="px-4 py-3 text-right">${fmt(T.baki_debet)}</td>
                <td class="px-4 py-3 text-right">${grandPersen.toFixed(2)}%</td>
            </tr>
          `;
      }

      tbody.innerHTML = html;

      document.getElementById('summaryWrapJT').classList.remove('hidden');
      document.getElementById('sum_noa_lama').textContent = fmt(T.noa_lama);
      document.getElementById('sum_plaf_lama').textContent = fmt(T.plafon_lama);
      document.getElementById('sum_noa_baru').textContent = fmt(T.noa_baru);
      document.getElementById('sum_plaf_baru').textContent = fmt(T.plafon_baru);
      const gp = (T.plafon_lama > 0) ? (T.plafon_baru / T.plafon_lama * 100) : 0;
      document.getElementById('sum_persen').textContent = gp.toFixed(2) + '%';
      document.getElementById('sum_bd').textContent = fmt(T.baki_debet);
  }

  // --- 2. MODAL LOGIC (SERVER SIDE PAGINATION) ---
  
  // Fungsi dipanggil saat klik angka NOA
  function initModalDetail(kode, nama) {
      const closing = document.getElementById('closing_date_jt').value;
      const harian  = document.getElementById('harian_date_jt').value;
      const bulan   = document.getElementById('filter_bulan').value;
      const tahun   = document.getElementById('filter_tahun').value;

      // Set Parameter Global untuk Detail
      currentDetailParams = {
          type: 'detail prospek jatuh tempo',
          closing_date: closing,
          harian_date: harian,
          bulan: bulan,
          tahun: tahun,
          kode_kantor: kode,
          limit: detailLimit
      };

      document.getElementById('modalTitleJT').textContent = `Detail Nasabah - ${nama}`;
      document.getElementById('modalSubTitleJT').textContent = `Periode Jatuh Tempo: ${bulan}/${tahun}`;
      document.getElementById('modalDetailJT').classList.remove('hidden');
      
      // Load Halaman 1
      loadDetailPage(1);
  }

  async function loadDetailPage(page) {
      const loader = document.getElementById('loadingModalJT');
      const tbody = document.getElementById('bodyModalJT');
      const btnPrev = document.getElementById('btnPrevJT');
      const btnNext = document.getElementById('btnNextJT');
      const info = document.getElementById('pageInfoJT');

      loader.classList.remove('hidden');
      tbody.innerHTML = ''; // Kosongkan tabel saat loading

      try {
          // Tambahkan page ke payload
          const payload = { ...currentDetailParams, page: page };
          
          const res = await apiCall(API_JT_URL, {
              method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)
          });
          const json = await res.json();
          
          // Ambil Data & Metadata Pagination dari JSON
          const list = json.data?.data || [];
          const meta = json.data?.pagination || { total_records:0, total_pages:1 };

          currentDetailPage = page;
          currentDetailTotalPages = meta.total_pages;

          // Render Row
          if(list.length === 0) {
              tbody.innerHTML = `<tr><td colspan="8" class="py-8 text-center text-gray-400">Tidak ada data detail.</td></tr>`;
              info.innerText = `0 data`;
              btnPrev.disabled = true;
              btnNext.disabled = true;
          } else {
              let html = '';
              list.forEach(row => {
                  let badge = `<span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold bg-gray-100 text-gray-600">${row.keterangan_status}</span>`;
                  if(row.keterangan_status === 'SUDAH TOP UP') 
                      badge = `<span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold bg-green-100 text-green-700 border border-green-200">SUDAH TOP UP</span>`;
                  else if(row.keterangan_status === 'BELUM LUNAS') 
                      badge = `<span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold bg-red-100 text-red-700 border border-red-200">BELUM LUNAS</span>`;
                  else if(row.keterangan_status === 'LUNAS (POTENSI)') 
                      badge = `<span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold bg-blue-100 text-blue-700 border border-blue-200">LUNAS (POTENSI)</span>`;

                  const plafonBaru = row.plafond_baru > 0 
                                     ? `<span class="font-bold text-green-700">${fmt(row.plafond_baru)}</span>` 
                                     : '<span class="text-gray-300">-</span>';

                  html += `
                    <tr class="hover:bg-gray-50 border-b transition">
                        <td class="px-4 py-2 font-mono text-gray-600 text-xs">${row.no_rekening_lama}</td>
                        <td class="px-4 py-2 font-medium text-gray-900">${row.nama_nasabah}</td>
                        <td class="px-4 py-2 text-right text-gray-500">${fmt(row.plafond_lama)}</td>
                        <td class="px-4 py-2 text-right bg-green-50/30 border-l border-green-50">${plafonBaru}</td>
                        <td class="px-4 py-2 text-right font-semibold text-gray-800">${fmt(row.baki_debet_lama)}</td>
                        <td class="px-4 py-2 text-center text-gray-500 text-xs">${row.tgl_jatuh_tempo}</td>
                        <td class="px-4 py-2 text-center">${badge}</td>
                        <td class="px-4 py-2 text-center text-xs text-gray-400 font-mono">${row.kode_group2 || '-'}</td>
                    </tr>
                  `;
              });
              tbody.innerHTML = html;

              // Update Footer Info
              const startRec = ((page - 1) * detailLimit) + 1;
              const endRec = Math.min(page * detailLimit, meta.total_records);
              info.innerText = `Menampilkan ${startRec} - ${endRec} dari ${meta.total_records} data`;

              // Update Buttons
              btnPrev.disabled = page <= 1;
              btnNext.disabled = page >= meta.total_pages;
              
              // Styling Button Disabled
              const disableClass = "opacity-50 cursor-not-allowed";
              if(btnPrev.disabled) btnPrev.classList.add(...disableClass.split(" ")); else btnPrev.classList.remove(...disableClass.split(" "));
              if(btnNext.disabled) btnNext.classList.add(...disableClass.split(" ")); else btnNext.classList.remove(...disableClass.split(" "));
          }

      } catch(err){
          console.error(err);
          tbody.innerHTML = `<tr><td colspan="8" class="py-8 text-center text-red-500">Gagal mengambil data detail.</td></tr>`;
      } finally {
          loader.classList.add('hidden');
      }
  }

  // Fungsi Paginasi (Next / Prev)
  window.changePageDetail = function(step) {
      const nextPage = currentDetailPage + step;
      if (nextPage > 0 && nextPage <= currentDetailTotalPages) {
          loadDetailPage(nextPage);
      }
  }

  window.closeModalJT = function(){
      document.getElementById('modalDetailJT').classList.add('hidden');
  }
  
  document.addEventListener('keydown', e => { if(e.key === 'Escape') closeModalJT(); });
</script>