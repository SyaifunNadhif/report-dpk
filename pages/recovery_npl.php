<style>
  :root { --primary: #2563eb; --bg: #f8fafc; --text: #334155; }
  
  /* Input & Controls */
  .inp { 
    border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 0 0.5rem; 
    font-size: 13px; background: #fff; width: 100%; height: 38px;
    /* Agar di HP text tanggal tidak kepotong */
    min-width: 0; 
  }
  
  /* Label kecil di atas input */
  .lbl { 
    font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; 
    margin-bottom: 2px; display: block; white-space: nowrap;
  }
  
  .btn-icon { 
    width: 100%; height: 38px; border-radius: 8px; 
    background: var(--primary); color: white; border: none; cursor: pointer; 
    display: inline-flex; align-items: center; justify-content: center; 
    transition: 0.2s; 
  }
  .btn-icon:hover { background: #1d4ed8; }

  /* === TABLE SCROLLER & STICKY CONFIG === */
  #recScroller {
      --rec_col1: 50px;  /* Lebar Kolom Kode */
      --rec_col2: 160px; /* Lebar Kolom Nama */
      position: relative;
      border: 1px solid #e2e8f0; border-radius: 8px; background: white;
      height: 100%; overflow: auto;
      -webkit-overflow-scrolling: touch;
  }

  table { border-collapse: separate; border-spacing: 0; width: 100%; font-size: 12px; }
  th, td { white-space: nowrap; padding: 8px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
  
  /* Header Styles */
  thead th { 
      position: sticky; top: 0; z-index: 80; 
      background: #d9ead3; color: #1e293b; font-weight: 700; text-transform: uppercase; 
      height: 40px; border-bottom: 1px solid #cbd5e1; font-size: 11px;
  }

  /* Sticky Columns Logic */
  .sticky-left-1 { position: sticky; left: 0; z-index: 85; background: #fff; border-right: 1px solid #e2e8f0; width: var(--rec_col1); text-align: center; }
  .sticky-left-2 { position: sticky; left: var(--rec_col1); z-index: 84; background: #fff; border-right: 1px solid #e2e8f0; width: var(--rec_col2); max-width: var(--rec_col2); overflow: hidden; text-overflow: ellipsis; }
  
  /* Header Sticky Priority */
  thead th.sticky-left-1 { z-index: 90; background: #d9ead3; }
  thead th.sticky-left-2 { z-index: 89; background: #d9ead3; }

  /* Total Row Sticky */
  .row-total td { 
      position: sticky; top: 40px; z-index: 75; 
      background: #eff6ff; color: #1e3a8a; font-weight: bold; 
      border-bottom: 2px solid #bfdbfe; box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  }
  .row-total td.sticky-left-1 { z-index: 88; background: #eff6ff; }
  .row-total td.sticky-left-2 { z-index: 87; background: #eff6ff; }

  /* Hover Effects */
  tbody tr:hover td { background-color: #f8fafc; }
  
  /* === MODAL STYLES === */
  .modal-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 12px; }
  .modal-table th { position: sticky; top: 0; background: #f1f5f9; z-index: 10; padding: 8px; text-align: left; font-weight: 600; color: #475569; }
  .modal-table td { padding: 8px; border-bottom: 1px solid #e2e8f0; color: #334155; }

  /* === RESPONSIVE === */
  @media (min-width: 768px) {
      .btn-icon { width: 42px; }
      .lbl { font-size: 11px; margin-bottom: 4px; }
      /* Kembalikan ukuran input normal di desktop */
      .inp { padding: 0.4rem 0.75rem; }
  }
  
  @media (max-width: 767px) {
      /* Mobile: Matikan sticky kolom ke-2 (Nama) biar layar lega */
      .sticky-left-2, thead th.sticky-left-2, .row-total td.sticky-left-2 {
          position: static !important; border-right: none; width: auto; max-width: none;
      }
      /* Geser sticky kolom 1 (Kode) tetap di kiri */
      .sticky-left-1 { border-right: 1px solid #e2e8f0; }
      
      /* Input Date di Mobile fontnya dikecilin dikit biar muat */
      input[type="date"] { font-size: 11px; }
  }
</style>

<div class="max-w-7xl mx-auto px-2 md:px-3 py-3 h-[100dvh] flex flex-col font-sans bg-slate-50">
  
  <div class="flex flex-col md:flex-row md:items-end justify-between gap-3 mb-3 shrink-0">
    
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-lg md:text-2xl font-bold flex items-center gap-2 text-slate-800">
            <span class="bg-blue-600 text-white p-1 rounded text-sm md:text-base">💰</span> 
            <span>Recovery NPL</span>
        </h1>
        <p class="text-[10px] text-slate-500 mt-1 ml-1 md:block">*Posisi Closing vs Harian</p>
      </div>
      <div id="loadingMini" class="hidden md:hidden animate-spin h-5 w-5 border-2 border-blue-600 border-t-transparent rounded-full"></div>
    </div>

    <form id="formFilterRecovery" class="grid grid-cols-[1fr_1fr_40px] md:flex md:items-end gap-2 bg-white p-2 rounded-lg border border-slate-200 shadow-sm w-full md:w-auto">
      
      <div class="md:w-[130px] min-w-0">
        <label class="lbl">Closing</label>
        <input type="date" id="closing_date_recovery" class="inp" required>
      </div>
      
      <div class="md:w-[130px] min-w-0">
        <label class="lbl">Harian</label>
        <input type="date" id="harian_date_recovery" class="inp" required>
      </div>

      <div class="md:w-auto flex items-end">
        <label class="lbl md:invisible hidden md:block">Act</label> <button type="submit" class="btn-icon" title="Terapkan Filter">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        </button>
      </div>
    </form>
  </div>

  <div class="flex-1 min-h-0 relative flex flex-col">
    
    <div id="loadingRecovery" class="hidden absolute inset-0 bg-white/80 z-[60] flex flex-col items-center justify-center text-blue-600 font-bold backdrop-blur-sm rounded-lg">
       <div class="animate-spin h-8 w-8 border-4 border-blue-200 border-t-blue-600 rounded-full mb-2"></div>
       <span>Memuat Data...</span>
    </div>

    <div id="recScroller">
      <table id="tabelRecovery">
        <thead>
          <tr>
            <th class="sticky-left-1">Kode</th>
            <th class="sticky-left-2 text-left">NAMA KANTOR</th>
            <th class="text-right">NOA Lunas</th>
            <th class="text-right">Baki Lunas</th>
            <th class="text-right">NOA Backflow</th>
            <th class="text-right">Baki Backflow</th>
            <th class="text-right cursor-pointer hover:bg-green-200 whitespace-nowrap" id="sortTotalNoa">Tot NOA ⬍</th>
            <th class="text-right cursor-pointer hover:bg-green-200 whitespace-nowrap" id="sortTotalBaki">Tot Baki ⬍</th>
          </tr>
        </thead>
        
        <tbody id="recoveryTotalRow"></tbody>

        <tbody id="recoveryBody"></tbody>
      </table>
    </div>
  </div>

</div>

<div id="modalDebiturRecovery" class="fixed inset-0 hidden bg-slate-900/60 backdrop-blur-sm items-center justify-center z-[9999] px-2 md:px-4">
  <div id="modalCardREC" class="bg-white rounded-xl shadow-2xl flex flex-col w-full max-w-[1100px] h-[85vh] md:max-h-[85vh] overflow-hidden">
    
    <div class="flex items-center justify-between p-3 md:p-4 border-b border-slate-100 bg-slate-50 shrink-0">
      <div>
        <h3 class="font-bold text-slate-800 text-base md:text-xl flex items-center gap-2">
            📄 <span id="modalTitleRecovery" class="truncate max-w-[200px] md:max-w-none">Detail Debitur</span>
        </h3>
        <p class="text-[10px] md:text-xs text-slate-500 mt-1" id="modalSubtitleRecovery">Daftar rekening</p>
      </div>
      <button id="btnCloseRecovery" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-200 hover:bg-red-100 hover:text-red-600 transition">✕</button>
    </div>

    <div class="flex-1 overflow-auto bg-white relative">
        <div id="modalBodyRecovery" class="min-w-full inline-block align-middle p-2 md:p-4">
            </div>
    </div>
  </div>
</div>

<script>
  // --- UTILS ---
  const nfID = new Intl.NumberFormat('id-ID');
  const fmt  = n => nfID.format(Number(n||0));
  const num  = v => Number(v||0);
  const kodeNum = v => Number(String(v??'').replace(/\D/g,'')||0);
  const formatDate = (s) => { if(!s) return '-'; const d=new Date(s); return isNaN(d)?'-': `${String(d.getDate()).padStart(2,'0')}/${String(d.getMonth()+1).padStart(2,'0')}/${d.getFullYear()}`; };

  // --- STATE ---
  let recoveryDataRaw = [];
  let sortState = { column: null, direction: 1 };
  let currentFilter = { closing:'', harian:'' };
  let abortCtrl;

  // --- MAIN INIT ---
  window.addEventListener('DOMContentLoaded', async () => {
    // 1. Ambil User Login
    const user = (window.getUser && window.getUser()) || JSON.parse(localStorage.getItem('app_user')) || { kode: '000' };
    window.currentUserKode = String(user.kode || '000').padStart(3, '0');
    
    // 2. Load Tanggal Default
    const d = await getLastHarianData();
    if(d) {
        document.getElementById('closing_date_recovery').value = d.last_closing;
        document.getElementById('harian_date_recovery').value  = d.last_created;
        currentFilter = { closing:d.last_closing, harian:d.last_created };
        
        // 3. Fetch Data Awal
        fetchRecoveryData(d.last_closing, d.last_created);
    }
  });

  async function getLastHarianData(){
    try { const r = await fetch('./api/date/'); const j = await r.json(); return j.data||null; } catch { return null; }
  }

  // --- FILTER HANDLER ---
  document.getElementById('formFilterRecovery').addEventListener('submit', e => {
    e.preventDefault();
    const closing = document.getElementById('closing_date_recovery').value;
    const harian  = document.getElementById('harian_date_recovery').value;
    currentFilter = { closing, harian };
    sortState = { column:null, direction:1 }; 
    fetchRecoveryData(closing, harian);
  });

  // --- FETCH DATA ---
  async function fetchRecoveryData(closing_date, harian_date){
    const loading = document.getElementById('loadingRecovery');
    const loadingMini = document.getElementById('loadingMini');
    
    loading.classList.remove('hidden');
    loadingMini.classList.remove('hidden'); // Munculkan loading mini di mobile header
    
    if(abortCtrl) abortCtrl.abort();
    abortCtrl = new AbortController();

    const tbody = document.getElementById('recoveryBody');
    const ttotal = document.getElementById('recoveryTotalRow');
    
    tbody.innerHTML = ''; 
    ttotal.innerHTML = `<tr><td colspan="8" class="p-4 text-center text-slate-400 italic">Memuat data...</td></tr>`;

    try {
      const res = await fetch('./api/npl/', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ type:'Recovery NPL', closing_date, harian_date }),
        signal: abortCtrl.signal
      });
      const json = await res.json();
      const data = Array.isArray(json.data) ? json.data : [];

      // Pisahkan Baris Total & Data Cabang
      const totalRow = data.find(d => (d.nama_kantor||'').toUpperCase().includes('TOTAL')) || null;
      recoveryDataRaw = data.filter(d => !(d.nama_kantor||'').toUpperCase().includes('TOTAL'));

      recoveryDataRaw.sort((a,b)=> kodeNum(a.kode_cabang) - kodeNum(b.kode_cabang));

      renderTotal(totalRow);
      renderRows(recoveryDataRaw);

    } catch(err) {
      if(err.name !== 'AbortError') {
         ttotal.innerHTML = `<tr><td colspan="8" class="p-4 text-center text-red-500 font-bold">Gagal memuat data.</td></tr>`;
      }
    } finally {
      loading.classList.add('hidden');
      loadingMini.classList.add('hidden');
    }
  }

  function renderTotal(tot) {
     const el = document.getElementById('recoveryTotalRow');
     if(!tot) { el.innerHTML = ''; return; }
     
     const tNoa = num(tot.noa_lunas) + num(tot.noa_backflow);
     const tBak = num(tot.baki_debet_lunas) + num(tot.baki_debet_backflow);

     el.innerHTML = `
        <tr class="row-total">
            <td class="sticky-left-1"></td>
            <td class="sticky-left-2 text-center md:text-left text-xs md:text-sm">TOTAL KONSOLIDASI</td>
            <td class="text-right">${fmt(tot.noa_lunas)}</td>
            <td class="text-right">${fmt(tot.baki_debet_lunas)}</td>
            <td class="text-right">${fmt(tot.noa_backflow)}</td>
            <td class="text-right">${fmt(tot.baki_debet_backflow)}</td>
            <td class="text-right">${fmt(tNoa)}</td>
            <td class="text-right">${fmt(tBak)}</td>
        </tr>
     `;
  }

  function renderRows(rows) {
     const tbody = document.getElementById('recoveryBody');
     if(rows.length === 0) {
         tbody.innerHTML = `<tr><td colspan="8" class="p-8 text-center text-slate-400">Tidak ada data recovery.</td></tr>`;
         return;
     }

     tbody.innerHTML = rows.map(r => {
        const tNoa = num(r.noa_lunas) + num(r.noa_backflow);
        const tBak = num(r.baki_debet_lunas) + num(r.baki_debet_backflow);
        const kode = String(r.kode_cabang||'').padStart(3,'0');

        return `
            <tr class="border-b transition">
                <td class="sticky-left-1 font-mono font-bold text-slate-500 text-xs">${kode}</td>
                <td class="sticky-left-2 font-semibold text-slate-700 text-xs md:text-sm">
                    <div class="truncate">${r.nama_kantor}</div>
                </td>
                
                <td class="text-right">
                    ${ num(r.noa_lunas) > 0 
                       ? `<a href="#" class="text-blue-600 font-bold hover:bg-blue-100 px-1 rounded transition" data-act="view" data-type="lunas" data-kode="${kode}">${fmt(r.noa_lunas)}</a>` 
                       : `<span class="text-slate-300">-</span>` 
                    }
                </td>
                <td class="text-right text-slate-600 text-xs">${num(r.baki_debet_lunas)>0 ? fmt(r.baki_debet_lunas) : '-'}</td>
                
                <td class="text-right">
                    ${ num(r.noa_backflow) > 0 
                       ? `<a href="#" class="text-orange-600 font-bold hover:bg-orange-100 px-1 rounded transition" data-act="view" data-type="backflow" data-kode="${kode}">${fmt(r.noa_backflow)}</a>` 
                       : `<span class="text-slate-300">-</span>` 
                    }
                </td>
                <td class="text-right text-slate-600 text-xs">${num(r.baki_debet_backflow)>0 ? fmt(r.baki_debet_backflow) : '-'}</td>
                
                <td class="text-right font-medium bg-slate-50">${fmt(tNoa)}</td>
                <td class="text-right font-medium bg-slate-50">${fmt(tBak)}</td>
            </tr>
        `;
     }).join('');
  }

  // --- SORTING ---
  const doSort = (colKey) => {
    sortState = { column: colKey, direction: sortState.column === colKey ? -sortState.direction : 1 };
    
    const sorted = [...recoveryDataRaw].sort((a,b) => {
        let valA, valB;
        if(colKey === 'total_noa') {
            valA = num(a.noa_lunas) + num(a.noa_backflow);
            valB = num(b.noa_lunas) + num(b.noa_backflow);
        } else {
            valA = num(a.baki_debet_lunas) + num(a.baki_debet_backflow);
            valB = num(b.baki_debet_lunas) + num(b.baki_debet_backflow);
        }
        return (valA - valB) * sortState.direction;
    });
    
    document.getElementById('sortTotalNoa').innerText = `Tot NOA ${colKey==='total_noa' ? (sortState.direction>0?'⬆':'⬇') : '⬍'}`;
    document.getElementById('sortTotalBaki').innerText = `Tot Baki ${colKey==='total_baki' ? (sortState.direction>0?'⬆':'⬇') : '⬍'}`;

    renderRows(sorted);
  };

  document.getElementById('sortTotalNoa').onclick = () => doSort('total_noa');
  document.getElementById('sortTotalBaki').onclick = () => doSort('total_baki');

  // --- MODAL & AUTH LOGIC ---
  document.getElementById('tabelRecovery').addEventListener('click', e => {
      const link = e.target.closest('a[data-act="view"]');
      if(!link) return;
      e.preventDefault();

      const targetKode = String(link.dataset.kode).padStart(3,'0');
      const userKode   = window.currentUserKode; 

      // 🔐 HAK AKSES CHECK
      if (userKode !== '000' && userKode !== targetKode) {
          alert(`⛔ AKSES DITOLAK\n\nAnda login sebagai Cabang ${userKode}.\nAnda tidak diizinkan melihat detail Cabang ${targetKode}.`);
          return;
      }

      openModalDebitur(link.dataset.type, targetKode);
  });

  async function openModalDebitur(type, kode){
      const modal = document.getElementById('modalDebiturRecovery');
      const title = document.getElementById('modalTitleRecovery');
      const sub   = document.getElementById('modalSubtitleRecovery');
      const body  = document.getElementById('modalBodyRecovery');

      modal.classList.remove('hidden');
      modal.classList.add('flex');
      
      const labelType = type === 'lunas' ? 'Lunas' : 'Backflow';
      title.innerHTML = `${labelType} <span class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-800 text-xs rounded font-mono">${kode}</span>`;
      sub.innerText = `Posisi: ${formatDate(currentFilter.closing)} vs ${formatDate(currentFilter.harian)}`;
      
      body.innerHTML = `<div class="p-10 text-center"><div class="animate-spin h-8 w-8 border-4 border-slate-200 border-t-blue-600 rounded-full mx-auto mb-2"></div>Memuat detail...</div>`;

      try {
          const res = await fetch('./api/npl/', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ 
                type: type, 
                kode_kantor: kode, 
                closing_date: currentFilter.closing, 
                harian_date: currentFilter.harian 
            })
          });
          const json = await res.json();
          const list = Array.isArray(json.data) ? json.data : [];

          if(list.length === 0) {
              body.innerHTML = `<div class="p-10 text-center text-slate-400 text-sm">Data detail tidak ditemukan.</div>`;
              return;
          }

          let tableHtml = `
            <table class="modal-table min-w-[800px]">
                <thead>
                    <tr>
                        <th class="w-[120px]">No Rek</th>
                        <th class="w-[200px]">Nama Nasabah</th>
                        <th class="text-right">Baki Debet</th>
                        <th class="text-center w-[50px]">Kol</th>
                        <th class="text-center w-[50px]">Upd</th>
                        <th class="text-center">Tgl Bayar</th>
                        <th class="text-right">Pokok</th>
                        <th class="text-right">Bunga</th>
                    </tr>
                </thead>
                <tbody>
          `;
          
          list.forEach(item => {
              tableHtml += `
                <tr class="hover:bg-slate-50">
                    <td class="font-mono text-slate-600 text-xs">${item.no_rekening}</td>
                    <td class="font-medium text-xs md:text-sm">
                        <div class="truncate max-w-[180px]" title="${item.nama_nasabah}">${item.nama_nasabah}</div>
                    </td>
                    <td class="text-right text-xs">${fmt(item.baki_debet)}</td>
                    <td class="text-center text-[10px] bg-red-50 text-red-700 rounded">${item.kolek||'-'}</td>
                    <td class="text-center text-[10px] bg-green-50 text-green-700 rounded font-bold">${item.kolek_update||'-'}</td>
                    <td class="text-center text-xs">${formatDate(item.tgl_trans)}</td>
                    <td class="text-right text-slate-500 text-xs">${fmt(item.angsuran_pokok)}</td>
                    <td class="text-right text-slate-500 text-xs">${fmt(item.angsuran_bunga)}</td>
                </tr>
              `;
          });
          
          tableHtml += `</tbody></table>`;
          body.innerHTML = tableHtml;

      } catch(e) {
          body.innerHTML = `<div class="p-10 text-center text-red-500 text-sm">Gagal mengambil detail.<br><small>${e.message}</small></div>`;
      }
  }

  const closeModal = () => {
      document.getElementById('modalDebiturRecovery').classList.add('hidden');
      document.getElementById('modalDebiturRecovery').classList.remove('flex');
  };
  document.getElementById('btnCloseRecovery').onclick = closeModal;
  document.getElementById('modalDebiturRecovery').onclick = (e) => {
      if(e.target.id === 'modalDebiturRecovery') closeModal();
  };
  document.addEventListener('keydown', e => { if(e.key === 'Escape') closeModal(); });

</script>