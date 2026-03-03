<style>
  :root { --primary: #2563eb; --bg: #f8fafc; --text: #334155; }
  
  html, body { height: 100%; margin: 0; overflow: hidden; background: var(--bg); color: var(--text); font-family: 'Inter', sans-serif; }
  
  .inp { border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 0 0.7rem; font-size: 13px; background: #fff; height: 38px; outline: none; transition: 0.2s; }
  .inp:focus { border-color: var(--primary); }

  .btn-icon { 
      width: 38px; height: 38px; border-radius: 10px; 
      background: var(--primary); color: white; border: none; cursor: pointer; 
      display: inline-flex; align-items: center; justify-content: center; 
      transition: 0.2s; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); 
  }
  .btn-icon:hover { background: #1d4ed8; transform: translateY(-1px); }

  /* KONTENER UTAMA HALAMAN */
  .page-container {
      /* UBAH ANGKA INI JIKA BAGIAN BAWAH MASIH TERPOTONG */
      /* Semakin besar angka pengurangnya, semakin tinggi tabel terangkat ke atas */
      height: calc(100vh - 90px); 
      display: flex;
      flex-direction: column;
      padding: 1rem;
      box-sizing: border-box;
  }

  /* KONTENER TABEL DENGAN SCROLL */
  #lgdScroller {
      flex: 1; 
      position: relative; 
      border: 1px solid #e2e8f0; 
      border-radius: 12px; 
      background: white; 
      overflow: auto; 
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
      /* TRIK JITU: Beri ruang kosong di dalam scroll agar baris terakhir bisa di-scroll lebih jauh ke atas */
      padding-bottom: 60px; 
  }

  table { border-collapse: separate; border-spacing: 0; width: 100%; font-size: 12px; }
  th, td { white-space: nowrap; padding: 12px 16px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; border-right: 1px solid #f1f5f9; }
  
  /* STICKY HEADER UTAMA */
  #tabelLGD thead th { 
      position: sticky; top: 0; z-index: 80; background: #f8fafc; color: #64748b; 
      font-weight: 700; text-transform: uppercase; font-size: 10px; border-bottom: 2px solid #e2e8f0; 
  }
  
  /* TOTAL KONSOLIDASI STICKY (Nempel persis di bawah header) */
  #lgdTotalRow td { 
      position: sticky; top: 38px; z-index: 75; 
      background: #eff6ff !important; color: #1e40af; font-weight: 800; border-bottom: 2px solid #bfdbfe; 
  }

  /* FREEZE COLUMNS DESKTOP */
  .sticky-left-1 { position: sticky; left: 0; z-index: 45; background: #fff; border-right: 1px solid #f1f5f9; width: 65px; text-align: center; }
  .sticky-left-2 { position: sticky; left: 65px; z-index: 44; background: #fff; border-right: 1px solid #e2e8f0; width: 220px; }

  #tabelLGD thead th.sticky-left-1 { z-index: 95; }
  #tabelLGD thead th.sticky-left-2 { z-index: 94; }
  #lgdTotalRow td.sticky-left-1 { z-index: 85; background: #eff6ff !important; }
  #lgdTotalRow td.sticky-left-2 { z-index: 84; background: #eff6ff !important; }

  /* RESPONSIVE: HIDE KODE SAAT MOBILE */
  @media (max-width: 767px) {
      .sticky-left-1 { display: none !important; }
      .sticky-left-2 { left: 0 !important; width: 160px; }
      #lgdTotalRow td.sticky-left-2 { z-index: 84 !important; }
  }

  #lgdBody tr:hover td { background-color: #f8fafc; }

  /* MODAL STYLE */
  #modalTableLGD { width: 100%; min-width: 1600px; }
  #modalTableLGD th { position: sticky; top: 0; z-index: 30; background: #f8fafc; font-weight: 700; border-bottom: 1px solid #cbd5e1; }
</style>

<div class="page-container">

  <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-4 shrink-0">
    <div>
      <h1 class="text-xl md:text-2xl font-bold flex items-center gap-2 text-slate-800">
          <span class="bg-blue-600 text-white p-2 rounded-xl shadow-lg text-sm">📉</span> 
          <span>Rekap LGD</span>
          <span id="badgeUnitLGD" class="ml-2 px-2 py-1 bg-blue-100 text-blue-700 text-[10px] uppercase font-bold rounded-lg tracking-wider">MEMUAT...</span>
      </h1>
      <p class="text-[11px] text-slate-500 mt-1">*Loss Given Default (Hapus Buku vs Recovery)</p>
    </div>

    <form id="filterFormLGD" class="flex flex-row flex-wrap items-end gap-3">
      <div class="flex flex-col w-[150px]">
          <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">Posisi Data</label>
          <input type="date" id="end_date_lgd" class="inp shadow-sm" required>
      </div>
      <div class="flex gap-2">
        <button type="submit" class="btn-icon" title="Cari Data">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        </button>
        <button type="button" onclick="exportLGDExcel()" class="btn-icon bg-emerald-600 hover:bg-emerald-700" title="Download Rekap">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
        </button>
      </div>
    </form>
  </div>

  <div id="lgdScroller">
    <div id="loadingLGD" class="hidden absolute inset-0 bg-white/80 z-[100] flex flex-col items-center justify-center text-blue-600 backdrop-blur-sm">
       <div class="animate-spin h-10 w-10 border-4 border-blue-200 border-t-blue-600 rounded-full mb-2"></div>
       <span class="text-sm font-bold tracking-widest uppercase">Memuat Data...</span>
    </div>

    <table id="tabelLGD">
      <thead>
        <tr>
          <th class="sticky-left-1">KODE</th>
          <th class="sticky-left-2 text-left">NAMA KANTOR</th>
          <th class="text-center w-[80px]">NOA</th>
          <th class="text-right">BAKI DEBET HB</th>
          <th class="text-right text-emerald-700">REC. NOMINAL</th>
          <th class="text-right">REC. NPV</th>
          <th class="text-right">SISA SALDO</th>
          <th class="text-right">RR (%)</th>
          <th class="text-right">LGD (%)</th>
        </tr>
        <tr id="lgdTotalRow"></tr> 
      </thead>
      <tbody id="lgdBody"></tbody>
    </table>
  </div>
</div>

<div id="modalLGD" class="fixed inset-0 hidden bg-slate-900/60 backdrop-blur-sm items-center justify-center z-[9999] px-2 md:px-4">
  <div id="modalCardLGD" class="bg-white rounded-2xl shadow-2xl flex flex-col w-full max-w-[1600px] h-[92vh] overflow-hidden animate-scale-up">
    <div class="flex items-center justify-between p-4 border-b bg-slate-50">
      <div>
        <h3 class="font-bold text-slate-800 text-lg">📄 Detail LGD Belum Lunas</h3>
        <p class="text-[11px] text-slate-500 mt-0.5 font-mono" id="lblModalSubtitleLGD"></p>
      </div>
      <div class="flex items-center gap-2">
          <button onclick="exportDetailLGDExcel()" class="flex items-center gap-2 px-4 h-10 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-sm transition">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
              <span class="text-xs font-bold uppercase tracking-wide">Excel Detail</span>
          </button>
          <button onclick="closeModalLGD()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-200 hover:bg-red-500 hover:text-white text-slate-600 transition font-bold">✕</button>
      </div>
    </div>
    <div class="flex-1 overflow-auto p-3 bg-white">
        <table id="modalTableLGD">
            <thead>
                <tr>
                    <th class="p-3 border text-center">No Rekening</th>
                    <th class="p-3 border text-left cursor-pointer hover:bg-blue-50" onclick="sortModal('nama_nasabah')">Nama Nasabah ⇅</th>
                    <th class="p-3 border text-right cursor-pointer hover:bg-blue-50" onclick="sortModal('balance_hapus_buku')">BD Hapus Buku ⇅</th>
                    <th class="p-3 border text-center">Tahun PH</th>
                    <th class="p-3 border text-center">Bunga</th>
                    <th class="p-3 border text-right text-emerald-700">Rec. Nominal</th>
                    <th class="p-3 border text-right">Rec. NPV</th>
                    <th class="p-3 border text-right">RR (%)</th>
                    <th class="p-3 border text-right font-bold text-red-600">LGD (%)</th>
                    <th class="p-3 border text-right">Sisa Saldo</th>
                    <th class="p-3 border text-right">Pokok Lalu</th>
                    <th class="p-3 border text-right text-blue-600">Pokok Berjalan</th>
                </tr>
            </thead>
            <tbody id="modalBodyLGD"></tbody>
        </table>
    </div>
  </div>
</div>

<div id="modalWarnLGD" class="fixed inset-0 hidden bg-slate-900/60 backdrop-blur-sm items-center justify-center z-[10000] px-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden p-6 text-center animate-scale-up border-t-4 border-red-500">
    <div class="w-16 h-16 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">⚠️</div>
    <h3 class="font-bold text-slate-800 text-xl mb-2">Akses Ditolak</h3>
    <p class="text-slate-500 text-sm mb-6">Anda login sebagai Cabang <span class="font-bold text-blue-600" id="warnUserLGD"></span>. Anda tidak diijinkan membuka detail nasabah milik <span class="font-bold text-red-500" id="warnTargetLGD"></span>.</p>
    <button onclick="document.getElementById('modalWarnLGD').classList.add('hidden')" class="w-full py-3 bg-slate-800 hover:bg-slate-900 text-white rounded-xl font-bold transition">Mengerti</button>
  </div>
</div>

<script>
  // UTILS: Format angka bulat tanpa desimal
  const nfLGD = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
  const fmtLGD = n => nfLGD.format(Number(n||0));
  const numLGD = v => Number(v||0);
  
  let lgdDataList = [];
  let detailLgdRaw = [];
  let currentEndDate = '';
  let sortState = { col: '', dir: 1 };

  // --- AMBIL USER LOGIN ---
  window.addEventListener('DOMContentLoaded', async () => {
      const user = (window.getUser && window.getUser()) || null;
      const uKode = user?.kode ? String(user.kode).padStart(3,'0') : '000';
      window.currentUser = { kode: uKode };
      
      document.getElementById('badgeUnitLGD').innerText = (uKode === '000') ? 'KONSOLIDASI' : `CABANG: ${uKode}`;

      const today = new Date().toISOString().split('T')[0];
      document.getElementById('end_date_lgd').value = today;
      currentEndDate = today;
      fetchLGD();
  });

  document.getElementById('filterFormLGD').addEventListener('submit', e => {
      e.preventDefault();
      currentEndDate = document.getElementById('end_date_lgd').value;
      fetchLGD();
  });

  async function fetchLGD() {
      const loading = document.getElementById('loadingLGD');
      loading.classList.remove('hidden');
      try {
          const res = await fetch('./api/hapus_buku/', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ type: 'get lgd', end_date: currentEndDate })
          });
          const json = await res.json();
          lgdDataList = Array.isArray(json.data) ? json.data : [];
          renderLGD(lgdDataList);
      } catch(e) {
          document.getElementById('lgdBody').innerHTML = '<tr><td colspan="9" class="text-center p-8 text-red-500 font-bold">Gagal memuat rekap.</td></tr>';
      } finally {
          loading.classList.add('hidden');
      }
  }

  function renderLGD(rows) {
      const tbody = document.getElementById('lgdBody');
      const ttotal = document.getElementById('lgdTotalRow');
      tbody.innerHTML = ''; ttotal.innerHTML = '';

      if(rows.length === 0) return;

      // 1. PINDAHKAN TOTAL KONSOLIDASI KE THEAD (STICKY)
      const totalObj = rows.find(r => (r.nama_kantor || '').toUpperCase().includes('KONSOLIDASI') || r.kode_kantor === 'TOTAL');
      if(totalObj) {
          ttotal.innerHTML = `
            <td class="sticky-left-1 font-bold">ALL</td>
            <td class="sticky-left-2 uppercase font-bold">${totalObj.nama_kantor}</td>
            <td class="text-center font-bold">${fmtLGD(totalObj.noa)}</td>
            <td class="text-right font-bold">${fmtLGD(totalObj.total_balance_ph)}</td>
            <td class="text-right text-emerald-700 font-bold">${fmtLGD(totalObj.total_recovery_nominal)}</td>
            <td class="text-right font-bold">${fmtLGD(totalObj.total_recovery_npv)}</td>
            <td class="text-right font-bold">${fmtLGD(totalObj.sisa_saldo_nominal)}</td>
            <td class="text-right font-bold">${numLGD(totalObj.persen_rr).toFixed(2)}%</td>
            <td class="text-right font-bold text-red-600">${numLGD(totalObj.persen_lgd).toFixed(2)}%</td>`;
      }

      // 2. RENDER BARIS CABANG
      const branches = rows.filter(r => r !== totalObj);
      tbody.innerHTML = branches.map(r => {
          const kode = String(r.kode_kantor || '').padStart(3,'0');
          const isOk = (window.currentUser.kode === '000' || window.currentUser.kode === kode);
          const linkCls = (numLGD(r.noa) > 0) ? (isOk ? "text-blue-600 font-bold cursor-pointer hover:underline" : "text-slate-400 font-bold cursor-pointer") : "text-slate-300";

          return `
            <tr>
                <td class="sticky-left-1 font-mono text-slate-400">${r.kode_kantor || ''}</td>
                <td class="sticky-left-2 ${linkCls}" onclick="handleLGDDetailSecurity('${kode}', '${r.nama_kantor}', ${r.noa})">${r.nama_kantor}</td>
                <td class="text-center">${fmtLGD(r.noa)}</td>
                <td class="text-right">${fmtLGD(r.total_balance_ph)}</td>
                <td class="text-right text-emerald-600 font-medium">${fmtLGD(r.total_recovery_nominal)}</td>
                <td class="text-right text-slate-500">${fmtLGD(r.total_recovery_npv)}</td>
                <td class="text-right text-slate-500">${fmtLGD(r.sisa_saldo_nominal)}</td>
                <td class="text-right font-semibold">${numLGD(r.persen_rr).toFixed(2)}%</td>
                <td class="text-right font-bold text-red-500">${numLGD(r.persen_lgd).toFixed(2)}%</td>
            </tr>`;
      }).join('');
  }

  // --- SECURITY CHECK ---
  window.handleLGDDetailSecurity = function(kode, nama, noa) {
      if(numLGD(noa) === 0) return;
      if(window.currentUser.kode !== '000' && window.currentUser.kode !== kode) {
          document.getElementById('warnUserLGD').innerText = window.currentUser.kode;
          document.getElementById('warnTargetLGD').innerText = nama;
          document.getElementById('modalWarnLGD').classList.remove('hidden');
          document.getElementById('modalWarnLGD').classList.add('flex');
          return;
      }
      openDetailLGD(kode, nama);
  };

  async function openDetailLGD(kode, nama) {
      const modal = document.getElementById('modalLGD');
      const tbody = document.getElementById('modalBodyLGD');
      document.getElementById('lblModalSubtitleLGD').innerText = `${kode} - ${nama} | POSISI: ${currentEndDate}`;
      modal.classList.remove('hidden'); modal.classList.add('flex');
      tbody.innerHTML = '<tr><td colspan="12" class="text-center p-12 uppercase text-xs font-bold tracking-widest text-slate-400">Sedang Menyiapkan Data...</td></tr>';
      
      try {
          const res = await fetch('./api/hapus_buku/', {
              method:'POST', headers:{'Content-Type':'application/json'},
              body: JSON.stringify({ type: 'detail lgd blm lunas', end_date: currentEndDate, kode_kantor: kode })
          });
          const json = await res.json();
          detailLgdRaw = Array.isArray(json.data) ? json.data : [];
          renderDetail(detailLgdRaw);
      } catch(e) { tbody.innerHTML = '<tr><td colspan="12" class="text-center p-8 text-red-500 font-bold">Gagal memuat detail.</td></tr>'; }
  }

  function renderDetail(list) {
      document.getElementById('modalBodyLGD').innerHTML = list.map(d => `
        <tr class="hover:bg-slate-50 border-b">
            <td class="p-3 border text-center font-mono text-slate-500">${d.no_rekening}</td>
            <td class="p-3 border font-semibold text-slate-700">${d.nama_nasabah}</td>
            <td class="p-3 border text-right">${fmtLGD(d.balance_hapus_buku)}</td>
            <td class="p-3 border text-center">${d.tahun_ph}</td>
            <td class="p-3 border text-center">${d.suku_bunga_efektif}%</td>
            <td class="p-3 border text-right text-emerald-600 font-bold">${fmtLGD(d.total_recovery_nominal)}</td>
            <td class="p-3 border text-right">${fmtLGD(d.jumlah_recovery_npv)}</td>
            <td class="p-3 border text-right">${numLGD(d.recovery_rate_npv).toFixed(2)}%</td>
            <td class="p-3 border text-right font-bold text-red-600 bg-red-50">${numLGD(d.lgd_persen).toFixed(2)}%</td>
            <td class="p-3 border text-right font-bold text-blue-700">${fmtLGD(d.sisa_saldo_nominal)}</td>
            <td class="p-3 border text-right text-slate-400">${fmtLGD(d.pokok_bulan_lalu)}</td>
            <td class="p-3 border text-right text-blue-600 font-medium">${fmtLGD(d.pokok_bulan_berjalan)}</td>
        </tr>`).join('');
  }

  window.sortModal = function(col) {
      sortState.dir = (sortState.col === col) ? -sortState.dir : 1; sortState.col = col;
      detailLgdRaw.sort((a,b) => {
          let vA = a[col], vB = b[col];
          if(!isNaN(Number(vA))) { vA = Number(vA); vB = Number(vB); }
          return (vA > vB ? 1 : -1) * sortState.dir;
      });
      renderDetail(detailLgdRaw);
  };

  function closeModalLGD() { document.getElementById('modalLGD').classList.add('hidden'); document.getElementById('modalLGD').classList.remove('flex'); }
  function closeModalWarnLGD() { document.getElementById('modalWarnLGD').classList.add('hidden'); }
  
  function exportLGDExcel() {
      if(lgdDataList.length === 0) return;
      let table = `<table border="1"><thead><tr style="background:#f1f5f9"><th>KODE</th><th>KANTOR</th><th>NOA</th><th>BAKI DEBET HB</th><th>REC NOMINAL</th><th>REC NPV</th><th>SISA SALDO</th><th>RR %</th><th>LGD %</th></tr></thead><tbody>`;
      lgdDataList.forEach(r => table += `<tr><td style="mso-number-format:'\\@'">${r.kode_kantor||''}</td><td>${r.nama_kantor}</td><td>${r.noa}</td><td>${r.total_balance_ph}</td><td>${r.total_recovery_nominal}</td><td>${r.total_recovery_npv}</td><td>${r.sisa_saldo_nominal}</td><td>${r.persen_rr}</td><td>${r.persen_lgd}</td></tr>`);
      const blob = new Blob([table + `</tbody></table>`], { type: 'application/vnd.ms-excel' });
      const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = `Rekap_LGD_${currentEndDate}.xls`; a.click();
  }

  function exportDetailLGDExcel() {
      if(detailLgdRaw.length === 0) return;
      let table = `<table border="1"><thead><tr><th>NO REKENING</th><th>NAMA NASABAH</th><th>BD HB</th><th>THN PH</th><th>BUNGA</th><th>REC NOMINAL</th><th>REC NPV</th><th>RR %</th><th>LGD %</th><th>SISA SALDO</th><th>POKOK LALU</th><th>POKOK BERJALAN</th></tr></thead><tbody>`;
      detailLgdRaw.forEach(d => table += `<tr><td style="mso-number-format:'\\@'">${d.no_rekening}</td><td>${d.nama_nasabah}</td><td>${d.balance_hapus_buku}</td><td>${d.tahun_ph}</td><td>${d.suku_bunga_efektif}</td><td>${d.total_recovery_nominal}</td><td>${d.jumlah_recovery_npv}</td><td>${d.recovery_rate_npv}</td><td>${d.lgd_persen}</td><td>${d.sisa_saldo_nominal}</td><td>${d.pokok_bulan_lalu}</td><td>${d.pokok_bulan_berjalan}</td></tr>`);
      const blob = new Blob([table + `</tbody></table>`], { type: 'application/vnd.ms-excel' });
      const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = `Detail_LGD_${currentEndDate}.xls`; a.click();
  }
</script>