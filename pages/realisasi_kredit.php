<style>
  :root { --primary: #2563eb; --bg: #f8fafc; --text: #334155; }
  body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text); overflow: hidden; }
  
  /* Input & Button */
  .inp { border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 0.4rem 0.75rem; font-size: 13px; background: #fff; width: 100%; height: 38px; cursor: pointer; }
  .inp:disabled { background-color: #f1f5f9; color: #64748b; font-weight: 600; cursor: not-allowed; border-color: #e2e8f0; }
  .lbl { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px; display: block; }
  
  /* 1. DATEPICKER FIX: 
     Icon bawaan disembunyikan agar terlihat bersih, 
     tapi trigger klik ditangani via JavaScript (showPicker) */
  input[type="date"]::-webkit-inner-spin-button,
  input[type="date"]::-webkit-calendar-picker-indicator { display: none; -webkit-appearance: none; }
  input[type="date"] { -moz-appearance: textfield; }

  .btn-icon { width: 38px; height: 38px; border-radius: 8px; background: var(--primary); color: white; border: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: 0.2s; }
  .btn-icon:hover { background: #1d4ed8; }

  /* 2. TABLE WRAPPER */
  .table-wrapper { 
      overflow: auto; 
      height: 100%; 
      border-radius: 8px; 
      border: 1px solid #e2e8f0; 
      background: white; 
      position: relative;
      /* Padding bottom dihapus agar sticky footer pas di bawah */
      padding-bottom: 0px; 
  }
  
  table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 12px; }
  
  /* Header Sticky */
  th { position: sticky; top: 0; z-index: 40; background: #d9ead3; color: #1e293b; font-weight: 700; padding: 12px 10px; border-bottom: 1px solid #cbd5e1; text-transform: uppercase; }
  td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle; }
  
  /* Sticky Kolom Kiri */
  .sticky-col { position: sticky; left: 0; z-index: 30; background: white; border-right: 1px solid #e2e8f0; }
  th.sticky-col { z-index: 50; background: #d9ead3; }
  
  /* Sticky Footer (Grand Total) - DIPERBAIKI */
  .sticky-total td { 
      position: sticky; 
      bottom: 0; 
      z-index: 60; /* Z-Index sangat tinggi agar menimpa data saat scroll */
      background: #eff6ff; 
      font-weight: 800; 
      border-top: 2px solid #60a5fa; /* Border atas lebih tegas */
      box-shadow: 0 -5px 15px -5px rgba(0,0,0,0.15); /* Shadow "mengambang" ke atas */
      padding-top: 15px; /* Ditinggikan visualnya */
      padding-bottom: 15px;
      font-size: 13px;
  }
  
  tr:hover td { background-color: #f8fafc; }
  .hidden { display: none !important; }
  .cell-action { cursor: pointer; color: var(--primary); font-weight: 600; text-decoration: none; }
  .cell-action:hover { text-decoration: underline; }
</style>

<script>
    const user = (window.getUser && window.getUser()) || null;
    const userKode = (user?.kode ? String(user.kode).padStart(3,'0') : '000');
    window.currentUser = { kode: userKode };
    console.log("Login detected:", userKode);
</script>

<div class="max-w-7xl mx-auto px-4 py-4 h-[calc(100vh-120px)] flex flex-col">
  
  <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 mb-3">
    <div>
      <h1 class="text-2xl font-bold flex items-center gap-2 text-slate-800">
        <span class="bg-blue-600 text-white p-1 rounded">💳</span> 
        <span>Rekap Realisasi & Growth</span>
      </h1>
      <p class="text-xs text-slate-500 mt-1">*Data Growth = Realisasi Baru - Run Off (Pelunasan/Penurunan)</p>
    </div>

    <form id="formFilterRealisasi" class="flex flex-wrap items-end gap-2 bg-white p-2 rounded-lg border border-slate-200 shadow-sm">
      <div style="width: 110px;">
        <label class="lbl">Closing</label>
        <input type="date" id="closing_date_realisasi" class="inp" required onclick="try{this.showPicker()}catch(e){}">
      </div>
      <div style="width: 110px;">
        <label class="lbl">Harian</label>
        <input type="date" id="harian_date_realisasi" class="inp" required onclick="try{this.showPicker()}catch(e){}">
      </div>
      <div style="min-width: 180px;">
        <label class="lbl">Kantor</label>
        <select id="opt_kantor_realisasi" class="inp"><option value="">Memuat...</option></select>
      </div>
      <button type="submit" class="btn-icon" title="Cari Data">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
      </button>
    </form>
  </div>

  <div class="flex-1 min-h-0 relative flex flex-col">
    <div id="loadingRealisasi" class="hidden absolute inset-0 bg-white/80 z-50 flex flex-col items-center justify-center text-blue-600 font-bold text-sm">
        <div class="animate-spin h-8 w-8 border-4 border-blue-200 border-t-blue-600 rounded-full mb-2"></div>
        Memuat Data...
    </div>

    <div class="table-wrapper">
      <table id="tabelRealisasi">
        <thead>
          <tr>
            <th class="sticky-col text-center w-[60px]">Kode</th>
            <th class="sticky-col" style="left:60px; min-width:180px;" id="thNamaUnit">NAMA KANTOR</th>
            <th class="text-right">NOA Realisasi</th>
            <th class="text-right bg-blue-50 text-blue-800">Total Realisasi</th>
            <th class="text-right bg-orange-50 text-orange-800">Total Run Off</th>
            <th class="text-right bg-green-50 text-green-800">Growth</th>
          </tr>
        </thead>
        <tbody id="bodyRealisasi"></tbody>
        <tfoot id="footRealisasi"></tfoot>
      </table>
    </div>
  </div>

</div>

<div id="modalDetailRealisasi" class="fixed inset-0 hidden bg-slate-900/60 backdrop-blur-sm z-[6000] items-center justify-center flex">
  <div class="bg-white rounded-xl shadow-2xl w-[95%] max-w-5xl max-h-[90vh] flex flex-col overflow-hidden animate-scale-up">
    <div class="flex justify-between items-center p-4 border-b bg-white">
      <h3 id="modalTitleRealisasi" class="font-bold text-lg text-slate-800">Detail Realisasi</h3>
      <button onclick="closeModalRealisasi()" class="text-slate-400 hover:text-red-500 text-2xl">&times;</button>
    </div>
    
    <div class="flex-1 overflow-auto bg-slate-50 p-0 relative">
        <div id="loadingModal" class="hidden absolute inset-0 bg-white/90 z-20 flex items-center justify-center text-blue-600 font-bold">Loading Detail...</div>
        <table class="w-full text-xs text-left text-slate-700">
            <thead class="bg-slate-100 font-bold uppercase text-slate-600 sticky top-0 shadow-sm">
                <tr>
                    <th class="px-4 py-3">Rekening</th>
                    <th class="px-4 py-3">Nasabah</th>
                    <th class="px-4 py-3 text-right">Plafond</th>
                    <th class="px-4 py-3 text-center">Tgl Realisasi</th>
                    <th class="px-4 py-3">Kankas</th>
                    <th class="px-4 py-3 text-blue-700">AO</th>
                </tr>
            </thead>
            <tbody id="modalBodyRealisasi" class="divide-y divide-slate-200 bg-white"></tbody>
        </table>
    </div>
    
    <div class="p-3 border-t bg-white flex justify-end">
        <button onclick="closeModalRealisasi()" class="px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-900 text-xs font-bold">Tutup</button>
    </div>
  </div>
</div>

<script>
  // --- CONFIG ---
  const API_KREDIT = './api/kredit/'; 
  const API_KODE   = './api/kode/';
  const API_DATE   = './api/date/';
  
  const nf = new Intl.NumberFormat('id-ID');
  const fmt = n => nf.format(Number(n)||0);

  // Helper
  async function apiCall(url, options = {}) {
      const res = await fetch(url, options);
      if(!res.ok) throw new Error(`HTTP Error ${res.status}`);
      return res;
  }

  // --- INIT PAGE ---
  window.addEventListener('DOMContentLoaded', async () => {
      const user = (window.getUser && window.getUser()) || null;
      const userKode = (user?.kode ? String(user.kode).padStart(3,'0') : '000');

      await populateKantorOptionsRealisasi(userKode);

      const d = await getLastHarianData(); 
      if (d) {
          document.getElementById('closing_date_realisasi').value = d.last_closing;
          document.getElementById('harian_date_realisasi').value  = d.last_created;
      } else {
          document.getElementById('harian_date_realisasi').value = new Date().toISOString().split('T')[0];
      }

      fetchRealisasiData();
  });

  async function getLastHarianData(){
    try {
        const r = await apiCall(API_DATE); 
        const j = await r.json(); 
        return j.data || null;
    } catch{ return null; }
  }

  // --- POPULATE DROPDOWN (LOGIC ASLI) ---
  async function populateKantorOptionsRealisasi(userKode){
    const optKantor = document.getElementById('opt_kantor_realisasi');

    // JIKA USER CABANG (BUKAN 000) -> LOCK
    if(userKode !== '000'){
        optKantor.innerHTML = `<option value="${userKode}">CABANG ${userKode}</option>`;
        optKantor.value = userKode;
        optKantor.disabled = true;
        return; 
    }

    // JIKA USER PUSAT (000) -> OPEN
    try {
        const res = await apiCall(API_KODE, { 
            method:'POST', 
            headers:{'Content-Type':'application/json'}, 
            body:JSON.stringify({type:'kode_kantor'}) 
        });
        const json = await res.json();
        const list = Array.isArray(json.data) ? json.data : [];
        
        let html = `<option value="">KONSOLIDASI (SEMUA)</option>`;
        
        list.filter(x => x.kode_kantor && x.kode_kantor !== '000')
            .sort((a,b) => String(a.kode_kantor).localeCompare(b.kode_kantor))
            .forEach(it => {
               html += `<option value="${String(it.kode_kantor).padStart(3,'0')}">${String(it.kode_kantor).padStart(3,'0')} - ${it.nama_kantor}</option>`;
            });
        
        optKantor.innerHTML = html;
        optKantor.disabled = false;
    } catch(e){
        optKantor.innerHTML = `<option value="">Error Load</option>`;
    }
  }

  // --- EVENT LISTENER ---
  document.getElementById('formFilterRealisasi').addEventListener('submit', e => { e.preventDefault(); fetchRealisasiData(); });

  // --- FETCH DATA UTAMA ---
  async function fetchRealisasiData() {
      const loading = document.getElementById('loadingRealisasi');
      const tbody = document.getElementById('bodyRealisasi');
      const tfoot = document.getElementById('footRealisasi');
      
      const closing = document.getElementById('closing_date_realisasi').value;
      const harian  = document.getElementById('harian_date_realisasi').value;
      const kantor  = document.getElementById('opt_kantor_realisasi').value;

      document.getElementById('thNamaUnit').innerText = (kantor && kantor !== '') ? "NAMA KANKAS" : "NAMA KANTOR";

      loading.classList.remove('hidden');
      tbody.innerHTML = ''; tfoot.innerHTML = '';

      try {
          const payload = {
              type: 'Realisasi Kredit', 
              closing_date: closing,
              harian_date: harian,
              kode_kantor: kantor
          };

          const res = await fetch(API_KREDIT, {
              method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)
          });
          const json = await res.json();
          if(json.status !== 200) throw new Error(json.message);

          const rows = json.data?.data || [];
          const gt   = json.data?.grand_total || { noa_realisasi:0, total_realisasi:0, total_run_off:0, growth:0 };

          if (rows.length === 0) {
              tbody.innerHTML = `<tr><td colspan="6" class="text-center py-10 text-slate-400">Data Kosong</td></tr>`;
              return;
          }

          let html = '';
          rows.forEach(r => {
              const paramKankas = (kantor && kantor !== '') ? r.kode_unit : ''; 
              const paramCabang = (kantor && kantor !== '') ? kantor : r.kode_unit;

              html += `
                <tr class="hover:bg-blue-50 transition border-b">
                    <td class="sticky-col text-center font-mono font-bold text-slate-500">${r.kode_unit}</td>
                    <td class="sticky-col font-semibold text-slate-700 text-xs" style="left:60px;">${r.nama_unit}</td>
                    
                    <td class="text-right">
                        <span class="cell-action" 
                           onclick="showDetailRealisasi('${paramCabang}', '${paramKankas}', '${r.nama_unit}')">
                           ${fmt(r.noa_realisasi)}
                        </span>
                    </td>
                    
                    <td class="text-right bg-blue-50/30 text-blue-800 font-bold">${fmt(r.total_realisasi)}</td>
                    <td class="text-right bg-orange-50/30 text-orange-800 font-bold">${fmt(r.total_run_off)}</td>
                    <td class="text-right bg-green-50/30 text-green-800 font-bold">${fmt(r.growth)}</td>
                </tr>
              `;
          });
          
          tbody.innerHTML = html;

          // Footer Sticky Bottom - DIPERBAIKI CLASSNYA
          tfoot.innerHTML = `
            <tr class="sticky-total">
                <td class="sticky-col text-center" colspan="2">GRAND TOTAL</td>
                <td class="text-right">${fmt(gt.noa_realisasi)}</td>
                <td class="text-right bg-blue-100 text-blue-900">${fmt(gt.total_realisasi)}</td>
                <td class="text-right bg-orange-100 text-orange-900">${fmt(gt.total_run_off)}</td>
                <td class="text-right bg-green-100 text-green-900">${fmt(gt.growth)}</td>
            </tr>
          `;

      } catch(e) {
          console.error(e);
          tbody.innerHTML = `<tr><td colspan="6" class="text-center py-10 text-red-500">Error: ${e.message}</td></tr>`;
      } finally {
          loading.classList.add('hidden');
      }
  }

  // --- MODAL DETAIL ---
  async function showDetailRealisasi(kode_cabang, kode_kankas, nama_unit) {
      const harian = document.getElementById('harian_date_realisasi').value;
      const modal = document.getElementById('modalDetailRealisasi');
      const title = document.getElementById('modalTitleRealisasi');
      const body  = document.getElementById('modalBodyRealisasi');
      const loader= document.getElementById('loadingModal');

      modal.classList.remove('hidden');
      title.innerText = `Detail Realisasi - ${nama_unit}`;
      body.innerHTML = '';
      loader.classList.remove('hidden');

      try {
          const payload = {
              type: 'Detail Realisasi Kredit',
              harian_date: harian,
              kode_kantor: kode_cabang, 
              kode_kankas: kode_kankas
          };

          const res = await fetch(API_KREDIT, {
              method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)
          });
          const json = await res.json();
          const rows = json.data || [];

          if(rows.length === 0) {
              body.innerHTML = `<tr><td colspan="6" class="text-center py-8 text-slate-400">Tidak ada data detail.</td></tr>`;
              return;
          }

          let html = '';
          rows.forEach(r => {
              html += `
                <tr class="hover:bg-blue-50 border-b">
                    <td class="px-4 py-2 font-mono text-slate-500">${r.no_rekening}</td>
                    <td class="px-4 py-2 font-semibold text-slate-700">${r.nama_nasabah}</td>
                    <td class="px-4 py-2 text-right font-bold text-slate-800">${fmt(r.plafond)}</td>
                    <td class="px-4 py-2 text-center text-slate-600">${r.tgl_realisasi}</td>
                    <td class="px-4 py-2 text-slate-600 text-xs">${r.nama_kankas || '-'}</td>
                    <td class="px-4 py-2 text-blue-700 font-bold text-xs">${r.nama_ao || '-'}</td>
                </tr>
              `;
          });
          body.innerHTML = html;

      } catch(e) {
          body.innerHTML = `<tr><td colspan="6" class="text-center text-red-500 py-4">Gagal: ${e.message}</td></tr>`;
      } finally {
          loader.classList.add('hidden');
      }
  }

  function closeModalRealisasi() {
      document.getElementById('modalDetailRealisasi').classList.add('hidden');
  }
  document.addEventListener('keydown', e => { if(e.key === 'Escape') closeModalRealisasi(); });
</script>