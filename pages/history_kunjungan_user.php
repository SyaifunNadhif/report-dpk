<input type="hidden" id="target_no_rekening" value="<?php echo $_POST['no_rekening'] ?? ''; ?>">

<div class="max-w-7xl mx-auto px-4 py-6 h-screen flex flex-col">
  <div class="hdr flex flex-wrap items-center gap-4 mb-3 border-b pb-3">
    <button onclick="history.back()" class="btn-icon bg-gray-600 hover:bg-gray-700 w-10 h-10" title="Kembali">
      <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M19 12H5M12 19l-7-7 7-7"/>
      </svg>
    </button>
    
    <div>
      <h1 class="text-2xl font-bold flex items-center gap-2 text-gray-800">
        <span>📅</span><span>Riwayat Kunjungan</span>
      </h1>
      <div id="infoNasabah" class="text-sm text-gray-500 mt-1 font-mono hidden">
        <span id="lblNorek" class="font-bold text-blue-700">Loading...</span> — <span id="lblNama">Loading...</span>
      </div>
    </div>

    <button id="btnRefresh" class="ml-auto btn-icon" title="Refresh Data">
      <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M23 4v6h-6"></path>
        <path d="M1 20v-6h6"></path>
        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
      </svg>
    </button>
  </div>

  <div id="loadingHist" class="hidden flex items-center gap-2 text-sm text-gray-600 mb-2">
    <svg class="animate-spin h-5 w-5 text-blue-600" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span>Memuat riwayat kunjungan...</span>
  </div>

  <div id="errHist" class="hidden mb-2 p-3 rounded border border-red-200 text-red-700 bg-red-50 text-sm"></div>

  <div id="histScroller" class="flex-1 min-h-0 overflow-hidden rounded border border-gray-200 bg-white shadow-sm">
    <div class="h-full overflow-auto">
      <table id="tabelHist" class="min-w-full text-sm text-left text-gray-700">
        <thead class="uppercase bg-gray-100 text-gray-600">
          <tr id="histHead1" class="text-xs">
            <th class="px-3 py-3 sticky-hist th-base text-center col-no">No</th>
            <th class="px-3 py-3 sticky-hist th-base col-tgl freeze-col">Tanggal</th>
            <th class="px-3 py-3 sticky-hist th-base col-petugas">Petugas</th>
            <th class="px-3 py-3 sticky-hist th-base col-tindakan">Kode / Tindakan / Lokasi</th>
            <th class="px-3 py-3 sticky-hist th-base col-hasil">Hasil / Janji Bayar</th>
            <th class="px-3 py-3 sticky-hist th-base col-ket">Keterangan</th>
            <th class="px-3 py-3 sticky-hist th-base text-center col-foto">Foto</th>
            <th class="px-3 py-3 sticky-hist th-base text-center col-map">Map</th>
          </tr>
        </thead>
        <tbody id="tbodyHist" class="divide-y divide-gray-100"></tbody>
      </table>
    </div>
  </div>
</div>

<div id="photoModal" class="fixed inset-0 z-[9999] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
  
  <div class="fixed inset-0 bg-gray-900 bg-opacity-90 transition-opacity backdrop-blur-sm" onclick="closeModal()"></div>

  <div class="fixed inset-0 z-10 overflow-y-auto">
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
      
      <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl border border-gray-600">
        
        <div class="bg-gray-50 px-4 pt-4 pb-3 sm:px-6 flex justify-between items-center border-b">
          <h3 class="text-lg font-bold leading-6 text-gray-900" id="modal-title">Bukti Foto Kunjungan</h3>
          <button type="button" onclick="closeModal()" class="rounded-full p-1 bg-gray-200 text-gray-500 hover:bg-red-100 hover:text-red-600 transition-colors focus:outline-none">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="bg-black flex justify-center items-center p-1 md:p-4 min-h-[300px]">
          <img id="modalImg" src="" alt="Foto Kunjungan" class="max-h-[80vh] w-auto max-w-full object-contain rounded shadow-lg border border-gray-700">
        </div>

        <div class="bg-gray-100 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t">
          <button type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto" onclick="closeModal()">
            Tutup
          </button>
        </div>

      </div>
    </div>
  </div>
</div>

<style>
  /* =================== Layout & Columns =================== */
  #histScroller {
    --colNo: 3.5rem;
    --colTgl: 10rem;
    --colPetugas: 12rem;
    --colTindakan: 14rem; /* Diperlebar sedikit untuk Kode Tindakan */
    --colHasil: 12rem;
    --colKet: 14rem;
    --colFoto: 4rem;
    --colMap: 4rem;
  }

  /* Sticky Header & Column */
  #tabelHist thead th.sticky-hist { position: sticky; top: 0; background: #d9ead3; z-index: 88; border-bottom: 2px solid #b6d7a8; }
  #tabelHist thead th.freeze-col { position: sticky; left: 0; z-index: 95; box-shadow: 1px 0 0 rgba(0,0,0,.08); }
  #tabelHist td.freeze-col { position: sticky; left: 0; z-index: 41; background: #fff; box-shadow: 1px 0 0 rgba(0,0,0,.08); font-weight: 600; color: #1e40af; }

  /* General Table */
  #tabelHist { table-layout: fixed; }
  #tabelHist th, #tabelHist td { padding: .65rem .75rem; vertical-align: top; }
  #tabelHist tbody tr:hover td { background-color: #f8fafc; }
  
  .col-no { width: var(--colNo); min-width: var(--colNo); }
  .col-tgl { width: var(--colTgl); min-width: var(--colTgl); }
  .col-petugas { width: var(--colPetugas); min-width: var(--colPetugas); word-break: break-word; }
  .col-tindakan { width: var(--colTindakan); min-width: var(--colTindakan); }
  .col-hasil { width: var(--colHasil); min-width: var(--colHasil); }
  .col-ket { width: var(--colKet); min-width: var(--colKet); word-break: break-word; }
  .col-foto { width: var(--colFoto); min-width: var(--colFoto); }
  .col-map { width: var(--colMap); min-width: var(--colMap); }

  /* Badge & Button */
  .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: 600; margin-right: 4px; }
  .bg-green-100 { background-color: #dcfce7; color: #166534; }
  .bg-blue-100 { background-color: #dbeafe; color: #1e40af; }
  .bg-purple-100 { background-color: #f3e8ff; color: #6b21a8; }
  
  .btn-icon { width: 38px; height: 38px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; background: #2563eb; color: #fff; transition: all 0.2s; }
  .btn-icon:hover { background: #1d4ed8; transform: translateY(-1px); }

  /* Mobile */
  @media (max-width: 640px) {
    #tabelHist { font-size: 13px; }
    .col-no, .col-petugas { display: none; }
    .col-tgl { width: 7rem; min-width: 7rem; }
    .col-tindakan { width: 10rem; min-width: 10rem; }
    .col-hasil { width: 9rem; min-width: 9rem; }
    .col-ket { width: 10rem; min-width: 10rem; }
  }
</style>

<script>
(() => {
  const API_URL = './api/kunjungan/'; 
  
  // Helpers
  const nf = new Intl.NumberFormat('id-ID');
  const fmtMoney = n => 'Rp ' + nf.format(Number(n||0));
  const escapeHtml = s => String(s||'').replace(/&/g,'&').replace(/</g,'<').replace(/>/g,'>');
  
  const fmtDate = (s) => {
    if(!s) return '-';
    try {
      const d = new Date(s);
      if(isNaN(d)) return s;
      const dd = String(d.getDate()).padStart(2,'0');
      const mm = String(d.getMonth()+1).padStart(2,'0');
      const yy = String(d.getFullYear()).slice(-2);
      const H  = String(d.getHours()).padStart(2,'0');
      const i  = String(d.getMinutes()).padStart(2,'0');
      return `${dd}/${mm}/${yy} <span class="text-gray-400 text-xs ml-1">${H}:${i}</span>`;
    } catch { return s; }
  };

  const getStoredToken = () => (window.AUTH_TOKEN || localStorage.getItem('dpk_token') || '').trim();
  const getKodeKantor = () => (window.NavAuth?.branchCode || localStorage.getItem('kode_kantor') || '001').toString().padStart(3,'0');

  let targetNoRekening = document.getElementById('target_no_rekening')?.value;
  if(!targetNoRekening) {
    const params = new URLSearchParams(window.location.search);
    targetNoRekening = params.get('no_rekening') || '';
  }

  // ================== FUNGSI MODAL FOTO ==================
  window.openModal = (url) => {
    const modal = document.getElementById('photoModal');
    const img   = document.getElementById('modalImg');
    img.src = url;
    modal.classList.remove('hidden');
  };

  window.closeModal = () => {
    const modal = document.getElementById('photoModal');
    const img   = document.getElementById('modalImg');
    modal.classList.add('hidden');
    setTimeout(() => { img.src = ''; }, 200); // Clear src after close
  };
  
  // Close on Escape key
  document.addEventListener('keydown', (e) => {
    if(e.key === 'Escape') window.closeModal();
  });

  // ================== MAIN FETCH ==================
  async function fetchHistory() {
    const tbody   = document.getElementById('tbodyHist');
    const loading = document.getElementById('loadingHist');
    const errEl   = document.getElementById('errHist');
    
    loading.classList.remove('hidden');
    errEl.classList.add('hidden');
    tbody.innerHTML = ''; 

    if(!targetNoRekening) {
      loading.classList.add('hidden');
      errEl.textContent = 'Nomor Rekening tidak ditemukan.';
      errEl.classList.remove('hidden');
      return;
    }

    try {
      const headers = { 'Content-Type':'application/json' };
      // headers['Authorization'] = 'Bearer ' + getStoredToken(); 

      const body = {
        kode_kantor: getKodeKantor(),
        type: 'history_kunjungan_rekening',
        no_rekening: targetNoRekening
      };

      const res = await fetch(API_URL, { 
        method: 'POST', 
        headers, 
        body: JSON.stringify(body) 
      });

      const json = await res.json().catch(() => ({}));
      if (!res.ok || json.status !== 200) throw new Error(json?.message || 'Gagal memuat data.');

      const rows = Array.isArray(json?.data?.rows) ? json.data.rows : [];
      renderTable(rows);

    } catch (err) {
      errEl.textContent = err.message;
      errEl.classList.remove('hidden');
      tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-gray-400">Gagal memuat data</td></tr>`;
    } finally {
      loading.classList.add('hidden');
    }
  }

  function renderTable(rows) {
    const tbody = document.getElementById('tbodyHist');
    
    if(rows.length === 0) {
      tbody.innerHTML = `<tr><td colspan="8" class="text-center py-8 text-gray-500 italic">Belum ada riwayat kunjungan.</td></tr>`;
      return;
    }

    if(rows[0]) {
      document.getElementById('lblNorek').textContent = rows[0].no_rekening;
      document.getElementById('lblNama').textContent  = rows[0].nama_nasabah || 'Tanpa Nama';
      document.getElementById('infoNasabah').classList.remove('hidden');
    }

    let html = '';
    rows.forEach((r, i) => {
      // 1. Tanggal
      const tgl = fmtDate(r.tgl_kunjungan);
      
      // 2. Tindakan + Kode Tindakan
      const kodeTindakan = r.kode_tindakan ? `<span class="badge bg-purple-100">${escapeHtml(r.kode_tindakan)}</span>` : '';
      const tindakan     = r.jenis_tindakan || '-';
      const lokasi       = r.lokasi_tindakan || '-';
      const person       = r.orang_ditemui   || '-';
      
      // 3. Hasil
      let hasil = '';
      if(Number(r.nominal_janji_bayar) > 0) {
        hasil += `<div class="font-bold text-emerald-600">${fmtMoney(r.nominal_janji_bayar)}</div>`;
        if(r.tanggal_janji_bayar) hasil += `<div class="text-xs text-gray-500">Janji: ${r.tanggal_janji_bayar}</div>`;
      } else {
        hasil = '<span class="text-gray-400">-</span>';
      }

      // 4. Map
      let btnMap = '<span class="text-gray-300">-</span>';
      if(r.koordinat && r.koordinat.includes(',')) {
        // Fix url map
        const urlMap = `http://maps.google.com/maps?q=${r.koordinat}`;
        btnMap = `
          <a href="${urlMap}" target="_blank" class="text-blue-600 hover:text-blue-800" title="Lihat Peta">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/></svg>
          </a>`;
      }

      // 5. Foto (Fix Path & Modal)
      let btnFoto = '<span class="text-gray-300">-</span>';
      if(r.nama_foto) {
        // ✅ PERBAIKAN PATH FOTO SESUAI GAMBAR FOLDER (API/img/kunjungan)
        const urlFoto = `./API/img/kunjungan/${r.nama_foto}`; 
        
        // Menggunakan onclick="openModal(...)"
        btnFoto = `
          <button onclick="openModal('${urlFoto}')" class="text-purple-600 hover:text-purple-800 transition-transform hover:scale-110" title="Lihat Foto">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
              <circle cx="8.5" cy="8.5" r="1.5"></circle>
              <polyline points="21 15 16 10 5 21"></polyline>
            </svg>
          </button>`;
      }

      html += `
        <tr class="border-b bg-white">
          <td class="col-no text-center text-gray-500">${i + 1}</td>
          <td class="col-tgl freeze-col border-r border-gray-100">${tgl}</td>
          <td class="col-petugas text-xs">${escapeHtml(r.petugas)}</td>
          <td class="col-tindakan">
            <div>${kodeTindakan} <span class="text-sm font-medium text-gray-700">${escapeHtml(tindakan)}</span></div>
            <div class="text-xs mt-1 text-gray-500">${escapeHtml(lokasi)} • ${escapeHtml(person)}</div>
          </td>
          <td class="col-hasil">${hasil}</td>
          <td class="col-ket text-xs italic text-gray-600">"${escapeHtml(r.keterangan || '-')}"</td>
          <td class="col-foto text-center">${btnFoto}</td>
          <td class="col-map text-center">${btnMap}</td>
        </tr>
      `;
    });

    tbody.innerHTML = html;
  }

  document.getElementById('btnRefresh').addEventListener('click', fetchHistory);
  fetchHistory();

})();
</script>