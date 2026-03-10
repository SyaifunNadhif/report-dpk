<style>
  .custom-scrollbar::-webkit-scrollbar { height: 8px; width: 6px; }
  .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
  .no-scrollbar::-webkit-scrollbar { display: none; }
  .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

  /* Animasi Modal */
  @keyframes scaleUp { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
  .animate-scale-up { animation: scaleUp 0.2s ease-out forwards; }

  /* ========================================================
     CSS MAGIC STICKY TABLE (BUG FIX: HEADER TEMBUS)
     ======================================================== */
  /* Pastikan seluruh header punya background solid dan z-index tinggi */
  #tabelRekapMonev thead { position: sticky; top: 0; z-index: 60; }
  #tabelRekapMonev thead th { position: sticky; box-shadow: inset 0 -1px 0 #cbd5e1, inset -1px 0 0 #e2e8f0; background-clip: padding-box; }
  
  /* Lapis 1 (Bulan/Minggu) */
  #tabelRekapMonev thead tr:nth-child(1) th { top: 0; z-index: 50; height: 38px; background-color: #f1f5f9; }
  
  /* Lapis 2 (Target/Actual/%) - HARUS punya background solid */
  #tabelRekapMonev thead tr:nth-child(2) th { top: 38px; z-index: 49; height: 36px; background-color: #ffffff; }

  /* Freeze Kolom Kiri */
  .sticky-left-1 { position: sticky; left: 0; z-index: 30; background-color: #ffffff; box-shadow: inset -1px 0 0 #cbd5e1; background-clip: padding-box; }
  
  /* Pertemuan Sudut Kiri Atas (Paling Tinggi Z-Index nya) */
  #tabelRekapMonev thead tr:nth-child(1) th.sticky-left-1 { z-index: 60; background-color: #f1f5f9; }
  #tabelRekapMonev thead tr:nth-child(2) th.sticky-left-1 { z-index: 59; background-color: #f8fafc; }

  /* Hover Efek */
  #bodyRekapMonev tr.data-row:hover td { background-color: #eff6ff !important; cursor: pointer;}
  #bodyRekapMonev tr.data-row:hover td.sticky-left-1 { background-color: #eff6ff !important; }

  /* Helper Class untuk Indikator Warna Persentase */
  .pct-blue { color: #1d4ed8; background-color: #eff6ff !important; font-weight: 800; }
  .pct-green { color: #047857; background-color: #ecfdf5 !important; font-weight: 800; }
  .pct-yellow { color: #b45309; background-color: #fffbeb !important; font-weight: 700; }
  .pct-red { color: #be123c; background-color: #fff1f2 !important; font-weight: 700; }
</style>

<script>
    window.currentUser = { kode_kantor: (typeof USER_KODE_KANTOR !== 'undefined') ? USER_KODE_KANTOR : '000' };
</script>

<div class="max-w-[1920px] mx-auto px-2 md:px-4 py-3 md:py-5 h-[calc(100vh-80px)] flex flex-col bg-slate-50 font-sans text-slate-800 overflow-hidden">
  
  <div class="flex-none mb-3 flex flex-col xl:flex-row justify-between xl:items-end gap-3 w-full">
      <div class="flex flex-col gap-1.5 shrink-0">
          <h1 class="text-xl md:text-2xl font-bold text-slate-800 flex items-center gap-2">
              <span class="p-1.5 md:p-2 bg-blue-600 rounded-lg text-white shadow-sm">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
              </span>
              Laporan Rekap MONEV
          </h1>
          <p class="text-[10px] md:text-xs text-slate-500 font-medium">Matriks Komitmen vs Realisasi 1 Bulan Penuh</p>
      </div>

      <form id="formFilterRekap" class="bg-white p-2 md:p-2.5 rounded-xl border border-slate-200 shadow-sm flex flex-nowrap items-center gap-1.5 md:gap-3 w-full xl:w-auto shrink-0 overflow-x-auto no-scrollbar" onsubmit="event.preventDefault(); renderRekapTable();">
          
          <div class="flex flex-col w-[110px] shrink-0">
              <label class="text-[8px] md:text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">BULAN MONEV</label>
              <select id="filter_bulan" class="border border-slate-200 rounded-md px-2 py-1.5 text-xs text-slate-700 outline-none focus:border-blue-500 cursor-pointer w-full font-medium" onchange="renderRekapTable()">
                  <option value="1">Januari</option><option value="2">Februari</option><option value="3" selected>Maret</option>
                  <option value="4">April</option><option value="5">Mei</option><option value="6">Juni</option>
                  <option value="7">Juli</option><option value="8">Agustus</option><option value="9">September</option>
                  <option value="10">Oktober</option><option value="11">November</option><option value="12">Desember</option>
              </select>
          </div>

          <div class="flex flex-col w-[75px] shrink-0">
              <label class="text-[8px] md:text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">TAHUN</label>
              <input type="number" id="filter_tahun" class="border border-slate-200 rounded-md px-2 py-1.5 text-xs text-slate-700 outline-none focus:border-blue-500 w-full font-medium" value="2026" onchange="renderRekapTable()">
          </div>

          <div class="flex flex-col w-[150px] shrink-0" id="wrapper_cabang">
              <label class="text-[8px] md:text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">CABANG (PILIH)</label>
              <select id="filter_cabang" class="border border-slate-200 rounded-md px-2 py-1.5 text-xs text-slate-700 outline-none focus:border-blue-500 cursor-pointer w-full font-medium" onchange="renderRekapTable()">
                  <option value="001">Cab. Utama</option>
                  <option value="002" selected>Cab. Rembang</option>
              </select>
          </div>
          
          <div class="flex items-center gap-1.5 shrink-0 h-[28px] md:h-[34px] mb-px mt-3.5">
              <button type="button" onclick="exportExcelMonev()" class="h-full w-[34px] md:w-auto md:px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md md:rounded-lg flex items-center justify-center transition shadow-sm font-bold text-[10px] md:text-xs uppercase tracking-wider" title="Download Excel">
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                  <span class="hidden md:inline ml-1.5">EXCEL</span>
              </button>
          </div>
      </form>
  </div>

  <div class="flex-1 overflow-hidden bg-white rounded-xl shadow-sm border border-slate-200 relative flex flex-col">
    <div class="flex-none bg-slate-50 px-4 py-2 border-b border-slate-200 flex items-center gap-4 text-[9px] md:text-[10px] font-bold text-slate-500 uppercase tracking-widest shrink-0">
        <span>Legenda % Capaian:</span>
        <div class="flex items-center gap-1"><div class="w-3 h-3 bg-[#eff6ff] border border-[#1d4ed8] rounded-sm"></div> <span class="text-blue-700">> 100%</span></div>
        <div class="flex items-center gap-1"><div class="w-3 h-3 bg-[#ecfdf5] border border-[#047857] rounded-sm"></div> <span class="text-emerald-700">100%</span></div>
        <div class="flex items-center gap-1"><div class="w-3 h-3 bg-[#fffbeb] border border-[#b45309] rounded-sm"></div> <span class="text-amber-700">75% - 99%</span></div>
        <div class="flex items-center gap-1"><div class="w-3 h-3 bg-[#fff1f2] border border-[#be123c] rounded-sm"></div> <span class="text-rose-700">< 75%</span></div>
    </div>

    <div class="flex-1 overflow-auto custom-scrollbar relative">
      <table class="w-max min-w-full text-xs text-right border-separate border-spacing-0 text-slate-700" id="tabelRekapMonev">
        <thead class="tracking-wider bg-slate-100 text-slate-700 font-bold uppercase text-[9px] md:text-[10px]" id="headRekapMonev">
          </thead>
        <tbody id="bodyRekapMonev" class="divide-y divide-slate-100 bg-white">
          </tbody>
      </table>
    </div>
  </div>

</div>

<div id="modalNarasi" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModalNarasi()"></div>
    
    <div class="relative bg-white w-full max-w-3xl rounded-xl md:rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-scale-up">
        <div class="flex justify-between items-center px-4 py-3 md:px-5 border-b bg-slate-50 shrink-0">
            <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm md:text-base" id="modalTitleNarasi">
                <span class="bg-blue-100 text-blue-600 p-1 md:p-1.5 rounded-lg shadow-sm text-xs">📝</span> 
                Evaluasi Kualitatif & Narasi
            </h3>
            <button onclick="closeModalNarasi()" class="w-[30px] md:w-8 h-[30px] md:h-8 flex items-center justify-center rounded-lg bg-slate-200 hover:bg-red-500 hover:text-white text-slate-600 transition font-bold text-lg leading-none">&times;</button>
        </div>

        <div class="p-4 md:p-6 overflow-auto custom-scrollbar max-h-[70vh] flex flex-col gap-4 bg-slate-50" id="modalBodyNarasi">
            </div>
        
        <div class="px-4 py-3 border-t bg-white flex justify-end shrink-0">
            <button onclick="closeModalNarasi()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition shadow-sm">TUTUP</button>
        </div>
    </div>
</div>

<script>
  // --- PISAHKAN PARAMETER ANGKA DAN TEKS ---
  // Parameter Tabel (Angka Saja)
  const masterParamsTable = [
      { kategori: 'KREDIT & KINERJA', items: [
          { kode: '10601', nama: 'Baki Debet Kredit', tipe: 'rp' },
          { kode: '0005', nama: 'Pencairan Kredit Rp', tipe: 'rp' },
          { kode: '0006', nama: 'Jumlah NOA (Kredit)', tipe: 'number' },
          { kode: '0038', nama: 'Pendapatan', tipe: 'rp' },
          { kode: '0039', nama: 'Laba (Rugi)', tipe: 'rp' },
          { kode: '0041', nama: 'Produktivitas per Orang', tipe: 'rp' },
      ]},
      { kategori: 'DANA PIHAK KETIGA (DPK)', items: [
          { kode: '0007', nama: 'DAMAS Rp', tipe: 'rp' },
          { kode: '0008', nama: 'DAMAS NOA', tipe: 'number' },
          { kode: '0009', nama: 'Deposito Rp', tipe: 'rp' },
          { kode: '0010', nama: 'Deposito NOA', tipe: 'number' },
          { kode: '0011', nama: 'Tabungan Rp', tipe: 'rp' },
          { kode: '0012', nama: 'Tabungan NOA', tipe: 'number' },
      ]},
      { kategori: 'PENYELESAIAN NPL', items: [
          { kode: '0014', nama: 'Penyelesaian PAR 1-2 Rp', tipe: 'rp' },
          { kode: '0016', nama: 'Penyelesaian PAR 3-4 Rp', tipe: 'rp' },
          { kode: '0018', nama: 'Penyelesaian KL Rp', tipe: 'rp' },
          { kode: '0020', nama: 'Recovery KL-D-M Rp', tipe: 'rp' },
          { kode: '0022', nama: 'Penyelesaian PH Rp', tipe: 'rp' },
          { kode: '0024', nama: 'Pemulihan CKPN Rp', tipe: 'rp' },
          { kode: '0026', nama: 'Proyeksi NPL %', tipe: 'percent' },
      ]},
      { kategori: 'PIPELINE', items: [
          { kode: '0027', nama: 'Pipeline Lunas Lancar Rp', tipe: 'rp' },
          { kode: '0029', nama: 'Pipeline Jatuh Tempo Rp', tipe: 'rp' },
          { kode: '0031', nama: 'Pipeline Debitur Baru Rp', tipe: 'rp' },
          { kode: '0033', nama: 'Total Pipeline Rp', tipe: 'rp' },
      ]}
  ];

  // Parameter Narasi (Teks & Non-Matriks) untuk di Modal
  const masterParamsText = [
      { kode: '0001', nama: 'Kepala Cabang' },
      { kode: '0002', nama: 'Penyebab Kredit Tidak Tercapai' },
      { kode: '0003', nama: 'Penyebab NPL Tidak Tercapai' },
      { kode: '0037', nama: 'Narasi Strategi NPL Minggu Ini' },
      { kode: '0040', nama: 'Jumlah Pegawai' } // Diselipkan disini karena dia bukan target rupiah
  ];

  const namaBulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
  const nf = new Intl.NumberFormat('id-ID');
  const fmt = n => nf.format(Math.round(Number(n||0)));

  window.addEventListener('DOMContentLoaded', () => {
      const uKode = (window.currentUser && window.currentUser.kode_kantor) || '000';
      if(uKode !== '000') { document.getElementById('wrapper_cabang').style.display = 'none'; }

      const dt = new Date();
      document.getElementById('filter_bulan').value = dt.getMonth() + 1;
      document.getElementById('filter_tahun').value = dt.getFullYear();
      
      renderRekapTable();
  });

  // --- LOGIKA WARNA PERSENTASE ---
  function getPctClass(pct) {
      if (pct > 100) return 'pct-blue';
      if (pct === 100) return 'pct-green';
      if (pct >= 75 && pct < 100) return 'pct-yellow';
      if (pct < 75) return 'pct-red';
      return '';
  }

  function renderRekapTable() {
      const bln = parseInt(document.getElementById('filter_bulan').value);
      let blnLalu = bln - 1; let namaBulanLalu = namaBulan[blnLalu];
      if (blnLalu === 0) { namaBulanLalu = "Desember"; }
      let namaBulanIni = namaBulan[bln];

      // 1. RENDER HEADER DENGAN TOMBOL KLIK MODAL
      const thead = document.getElementById('headRekapMonev');
      thead.innerHTML = `
          <tr>
            <th rowspan="2" class="sticky-left-1 px-4 border-r border-b border-slate-300 align-middle text-left bg-slate-100 min-w-[200px] w-[250px]">KATEGORI / PARAMETER REALISASI</th>
            <th rowspan="2" class="px-3 border-r border-b border-slate-300 align-middle text-right bg-slate-200 text-slate-600">CLOSING<br>${namaBulanLalu.toUpperCase()}</th>
            
            <th colspan="3" class="border-r border-b border-slate-300 align-middle text-center bg-white text-blue-700 hover:bg-blue-50 cursor-pointer transition p-0" onclick="openModalNarasi('MINGGU I', 'W1')" title="Klik untuk lihat Narasi M1">
                <div class="flex items-center justify-center gap-1.5 w-full h-full py-2">MINGGU I <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></div>
            </th>
            <th colspan="3" class="border-r border-b border-slate-300 align-middle text-center bg-slate-50 text-blue-700 hover:bg-blue-100 cursor-pointer transition p-0" onclick="openModalNarasi('MINGGU II', 'W2')" title="Klik untuk lihat Narasi M2">
                <div class="flex items-center justify-center gap-1.5 w-full h-full py-2">MINGGU II <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></div>
            </th>
            <th colspan="3" class="border-r border-b border-slate-300 align-middle text-center bg-white text-blue-700 hover:bg-blue-50 cursor-pointer transition p-0" onclick="openModalNarasi('MINGGU III', 'W3')" title="Klik untuk lihat Narasi M3">
                <div class="flex items-center justify-center gap-1.5 w-full h-full py-2">MINGGU III <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></div>
            </th>
            <th colspan="3" class="border-r border-b border-slate-300 align-middle text-center bg-slate-50 text-blue-700 hover:bg-blue-100 cursor-pointer transition p-0" onclick="openModalNarasi('MINGGU IV', 'W4')" title="Klik untuk lihat Narasi M4">
                <div class="flex items-center justify-center gap-1.5 w-full h-full py-2">MINGGU IV <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></div>
            </th>
            
            <th colspan="3" class="border-r border-b border-blue-200 align-middle text-center bg-blue-50 text-blue-800 hover:bg-blue-100 cursor-pointer transition p-0" onclick="openModalNarasi('CLOSING ${namaBulanIni.toUpperCase()}', 'C')" title="Klik untuk lihat Narasi Closing">
                <div class="flex items-center justify-center gap-1.5 w-full h-full py-2">CLOSING<br>${namaBulanIni.toUpperCase()} <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></div>
            </th>
            <th colspan="2" class="px-2 py-2 border-b border-purple-200 align-middle text-center bg-purple-50 text-purple-800">PERBANDINGAN<br>BULAN INI VS LALU</th>
          </tr>
          <tr>
            <th class="px-2 py-1.5 border-r border-b border-slate-200 text-center w-[80px] bg-white">Target</th>
            <th class="px-2 py-1.5 border-r border-b border-slate-200 text-center w-[80px] bg-white">Actual</th>
            <th class="px-2 py-1.5 border-r border-b border-slate-300 text-center w-[50px] bg-white">%</th>

            <th class="px-2 py-1.5 border-r border-b border-slate-200 text-center w-[80px] bg-slate-50">Target</th>
            <th class="px-2 py-1.5 border-r border-b border-slate-200 text-center w-[80px] bg-slate-50">Actual</th>
            <th class="px-2 py-1.5 border-r border-b border-slate-300 text-center w-[50px] bg-slate-50">%</th>

            <th class="px-2 py-1.5 border-r border-b border-slate-200 text-center w-[80px] bg-white">Target</th>
            <th class="px-2 py-1.5 border-r border-b border-slate-200 text-center w-[80px] bg-white">Actual</th>
            <th class="px-2 py-1.5 border-r border-b border-slate-300 text-center w-[50px] bg-white">%</th>

            <th class="px-2 py-1.5 border-r border-b border-slate-200 text-center w-[80px] bg-slate-50">Target</th>
            <th class="px-2 py-1.5 border-r border-b border-slate-200 text-center w-[80px] bg-slate-50">Actual</th>
            <th class="px-2 py-1.5 border-r border-b border-slate-300 text-center w-[50px] bg-slate-50">%</th>

            <th class="px-2 py-1.5 border-r border-b border-blue-200 text-center w-[80px] bg-blue-50/50">Target</th>
            <th class="px-2 py-1.5 border-r border-b border-blue-200 text-center w-[80px] bg-blue-50/50">Actual</th>
            <th class="px-2 py-1.5 border-r border-b border-blue-300 text-center w-[50px] bg-blue-50/50">%</th>

            <th class="px-2 py-1.5 border-r border-b border-purple-200 text-center w-[80px] bg-purple-50/50">Growth</th>
            <th class="px-2 py-1.5 border-b border-purple-200 text-center w-[60px] bg-purple-50/50">% Growth</th>
          </tr>
      `;

      // 2. RENDER BODY (Hanya Numerik)
      const tbody = document.getElementById('bodyRekapMonev');
      let htmlTabel = '';

      masterParamsTable.forEach(group => {
          htmlTabel += `
            <tr class="bg-slate-100/80 text-left">
              <td class="sticky-left-1 px-4 py-2 font-bold text-[11px] text-blue-900 border-b border-slate-200 shadow-[inset_-1px_0_0_#cbd5e1] uppercase">${group.kategori}</td>
              <td colspan="18" class="border-b border-slate-200"></td>
            </tr>
          `;

          group.items.forEach(item => {
              // --- MOCK DATA GENERATOR ---
              let isPct = item.tipe === 'percent';
              let baseVal = isPct ? Math.random() * 100 : (item.tipe === 'rp' ? (Math.random() * 500000000 + 10000000) : Math.floor(Math.random() * 100 + 5));
              let valM1 = baseVal;
              
              let w1k = valM1 * 1.05; let w1r = valM1 * (Math.random() * 0.2 + 0.9);
              let w2k = w1k * 1.05;   let w2r = w1r * (Math.random() * 0.2 + 0.9);
              let w3k = w2k * 1.05;   let w3r = w2r * (Math.random() * 0.2 + 0.9);
              let w4k = w3k * 1.05;   let w4r = w3r * (Math.random() * 0.2 + 0.9);
              let cmk = w4k * 1.05;   let cmr = w4r * (Math.random() * 0.2 + 0.9);

              let pct1 = w1k > 0 ? Math.round((w1r/w1k)*100) : 0;
              let pct2 = w2k > 0 ? Math.round((w2r/w2k)*100) : 0;
              let pct3 = w3k > 0 ? Math.round((w3r/w3k)*100) : 0;
              let pct4 = w4k > 0 ? Math.round((w4r/w4k)*100) : 0;
              let pctc = cmk > 0 ? Math.round((cmr/cmk)*100) : 0;

              let deltaVal = cmr - valM1;
              let deltaPct = valM1 > 0 ? ((cmr - valM1)/valM1)*100 : 0;
              let deltaCls = deltaVal < 0 ? 'text-rose-600' : 'text-emerald-700';

              let displayFmt = val => isPct ? val.toFixed(2) + '%' : fmt(val);

              htmlTabel += `
                <tr class="data-row transition border-b border-slate-100 h-[38px]">
                  <td class="sticky-left-1 px-4 py-1.5 border-r border-slate-200 shadow-[inset_-1px_0_0_#e2e8f0] text-left bg-white">
                      <span class="font-medium text-slate-700">${item.nama}</span>
                  </td>
                  
                  <td class="px-3 py-1.5 border-r border-slate-300 font-bold text-slate-600 bg-slate-50/50">${displayFmt(valM1)}</td>
                  
                  <td class="px-2 py-1.5 border-r border-slate-100 text-slate-500">${displayFmt(w1k)}</td>
                  <td class="px-2 py-1.5 border-r border-slate-100 font-bold text-slate-700">${displayFmt(w1r)}</td>
                  <td class="px-2 py-1.5 border-r border-slate-300 border-l border-l-slate-100 ${getPctClass(pct1)} bg-white">${pct1}%</td>

                  <td class="px-2 py-1.5 border-r border-slate-100 text-slate-500 bg-slate-50/50">${displayFmt(w2k)}</td>
                  <td class="px-2 py-1.5 border-r border-slate-100 font-bold text-slate-700 bg-slate-50/50">${displayFmt(w2r)}</td>
                  <td class="px-2 py-1.5 border-r border-slate-300 border-l border-l-slate-100 ${getPctClass(pct2)} bg-slate-50/50">${pct2}%</td>

                  <td class="px-2 py-1.5 border-r border-slate-100 text-slate-500">${displayFmt(w3k)}</td>
                  <td class="px-2 py-1.5 border-r border-slate-100 font-bold text-slate-700">${displayFmt(w3r)}</td>
                  <td class="px-2 py-1.5 border-r border-slate-300 border-l border-l-slate-100 ${getPctClass(pct3)} bg-white">${pct3}%</td>

                  <td class="px-2 py-1.5 border-r border-slate-100 text-slate-500 bg-slate-50/50">${displayFmt(w4k)}</td>
                  <td class="px-2 py-1.5 border-r border-slate-100 font-bold text-slate-700 bg-slate-50/50">${displayFmt(w4r)}</td>
                  <td class="px-2 py-1.5 border-r border-slate-300 border-l border-l-slate-100 ${getPctClass(pct4)} bg-slate-50/50">${pct4}%</td>

                  <td class="px-2 py-1.5 border-r border-blue-100 text-blue-600 bg-blue-50/30">${displayFmt(cmk)}</td>
                  <td class="px-2 py-1.5 border-r border-blue-100 font-bold text-blue-800 bg-blue-50/30">${displayFmt(cmr)}</td>
                  <td class="px-2 py-1.5 border-r border-blue-300 border-l border-l-blue-100 ${getPctClass(pctc)} text-[11px] bg-blue-50/30">${pctc}%</td>

                  <td class="px-2 py-1.5 border-r border-purple-100 font-bold ${deltaCls} bg-purple-50/30">${displayFmt(deltaVal)}</td>
                  <td class="px-2 py-1.5 border-b border-purple-100 font-bold ${deltaCls} bg-purple-50/30">${deltaPct.toFixed(1)}%</td>
                </tr>
              `;
          });
      });

      tbody.innerHTML = htmlTabel;
  }

  // --- MODAL LOGIC UNTUK NARASI ---
  function openModalNarasi(weekLabel, weekCode) {
      document.getElementById('modalTitleNarasi').innerHTML = `<span class="bg-blue-100 text-blue-600 p-1 md:p-1.5 rounded-lg shadow-sm text-xs">📝</span> Evaluasi Kualitatif - ${weekLabel}`;
      
      let htmlNarasi = '';
      masterParamsText.forEach(item => {
          // Simulasi Data Text dari Backend berdasarkan weekCode
          let valText = '';
          if (item.kode === '0001') valText = 'Bpk. Ahmad Subarjo';
          else if (item.kode === '0040') valText = '12 Orang';
          else valText = `Evaluasi dari cabang terkait parameter ini pada periode ${weekLabel} masih dalam proses penyusunan...`;

          htmlNarasi += `
              <div class="flex flex-col bg-white border border-slate-200 shadow-sm rounded-lg p-4">
                  <span class="text-[10px] font-bold text-blue-700 uppercase tracking-widest mb-2 pb-1 border-b border-slate-100">${item.nama}</span>
                  <p class="text-xs text-slate-700 leading-relaxed font-medium whitespace-pre-wrap">${valText}</p>
              </div>
          `;
      });

      document.getElementById('modalBodyNarasi').innerHTML = htmlNarasi;
      
      const modal = document.getElementById('modalNarasi');
      modal.classList.remove('hidden'); modal.classList.add('flex');
  }

  function closeModalNarasi() {
      const modal = document.getElementById('modalNarasi');
      modal.classList.add('hidden'); modal.classList.remove('flex');
  }

  // Esc untuk tutup modal
  document.addEventListener('keydown', e => { if(e.key === 'Escape') closeModalNarasi(); });

  function exportExcelMonev() {
      alert("Simulasi Export Excel... Data akan dikonversi ke CSV/XLS.");
  }
</script>