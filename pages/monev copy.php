<style>
  /* Custom Scrollbar */
  .custom-scrollbar::-webkit-scrollbar { height: 8px; width: 6px; }
  .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
  .no-scrollbar::-webkit-scrollbar { display: none; }
  .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

  /* ========================================================
     CSS MAGIC STICKY TABLE (MEMBEKUKAN HEADER & KOLOM KIRI)
     ======================================================== */
  #tabelMonev thead th { position: sticky; box-shadow: inset 0 -1px 0 #cbd5e1, inset -1px 0 0 #cbd5e1; }
  
  /* Lapis 1 (Header Bulan/Minggu) */
  #tabelMonev thead tr:nth-child(1) th { top: 0; z-index: 40; height: 38px; }
  
  /* Lapis 2 (Header Komitmen/Realisasi) */
  #tabelMonev thead tr:nth-child(2) th { top: 38px; z-index: 39; height: 36px; }

  /* Freeze Kolom Kiri (Indikator) */
  .sticky-left-1 { position: sticky; left: 0; z-index: 20; background: white; box-shadow: inset -1px 0 0 #cbd5e1; }

  /* Z-Index Header Freeze Kiri */
  #tabelMonev thead tr:nth-child(1) th.sticky-left-1, 
  #tabelMonev thead tr:nth-child(2) th.sticky-left-1 { z-index: 50; background-color: #f1f5f9; }

  /* Hover Body */
  #bodyMonev tr.data-row:hover td { background-color: #eff6ff !important; }
  #bodyMonev tr.data-row:hover td.sticky-left-1 { background-color: #eff6ff !important; }

  /* Input Styling Custom */
  .input-komitmen { width: 100%; border: 1px solid #cbd5e1; border-radius: 4px; padding: 4px 6px; font-size: 10px; font-family: monospace; text-align: right; outline: none; transition: all 0.2s; background: white; }
  .input-komitmen:focus { border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,0.1); }
  .input-komitmen.text-left { text-align: left; font-family: sans-serif; }

  .box-realisasi { width: 100%; padding: 4px 6px; font-size: 10px; font-family: monospace; text-align: right; background: #ecfdf5; color: #065f46; font-weight: bold; border-radius: 4px; border: 1px dashed #a7f3d0; }
  .box-realisasi.text-left { text-align: left; font-family: sans-serif; background: #f8fafc; color:#334155; border: 1px dashed #cbd5e1; font-weight: normal;}
</style>

<div class="max-w-[1920px] mx-auto px-2 md:px-4 py-3 md:py-5 h-[calc(100vh-80px)] flex flex-col bg-slate-50 font-sans text-slate-800 overflow-hidden">
  
  <div class="flex-none mb-3 flex flex-col xl:flex-row justify-between xl:items-end gap-3 w-full">
      <div class="flex flex-col gap-1.5 shrink-0">
          <h1 class="text-xl md:text-2xl font-bold text-slate-800 flex items-center gap-2">
              <span class="p-1.5 md:p-2 bg-blue-600 rounded-lg text-white shadow-sm">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
              </span>
              Form MONEV Cabang
          </h1>
          <p class="text-[10px] md:text-xs text-slate-500 font-medium">Monitoring Komitmen dan Realisasi Pencapaian Mingguan</p>
      </div>

      <form id="formFilterMonev" class="bg-white p-2 md:p-2.5 rounded-xl border border-slate-200 shadow-sm flex flex-nowrap items-center gap-1.5 md:gap-3 w-full xl:w-auto shrink-0 overflow-x-auto no-scrollbar" onsubmit="event.preventDefault(); renderTable();">
          
          <div class="flex flex-col w-[100px] shrink-0">
              <label class="text-[8px] md:text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">BULAN MONEV</label>
              <select id="filter_bulan" class="border border-slate-200 rounded-md px-1.5 md:px-2 py-1.5 text-[10px] md:text-xs text-slate-700 outline-none focus:border-blue-500 cursor-pointer w-full" onchange="renderTable()">
                  <option value="1">Januari</option><option value="2">Februari</option><option value="3" selected>Maret</option>
                  <option value="4">April</option><option value="5">Mei</option><option value="6">Juni</option>
                  <option value="7">Juli</option><option value="8">Agustus</option><option value="9">September</option>
                  <option value="10">Oktober</option><option value="11">November</option><option value="12">Desember</option>
              </select>
          </div>

          <div class="flex flex-col w-[70px] shrink-0">
              <label class="text-[8px] md:text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">TAHUN</label>
              <input type="number" id="filter_tahun" class="border border-slate-200 rounded-md px-1.5 md:px-2 py-1.5 text-[10px] md:text-xs text-slate-700 outline-none focus:border-blue-500 w-full" value="2026" onchange="renderTable()">
          </div>
          
          <div class="flex flex-col w-[150px] shrink-0" id="wrapper_cabang">
              <label class="text-[8px] md:text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">CABANG</label>
              <select id="filter_cabang" class="border border-slate-200 rounded-md px-1.5 md:px-2 py-1.5 text-[10px] md:text-xs text-slate-700 outline-none focus:border-blue-500 cursor-pointer w-full">
                  <option value="001">Cab. Utama</option>
                  <option value="002" selected>Cab. Rembang</option>
              </select>
          </div>

          <div class="w-px h-6 bg-slate-200 shrink-0 mx-1 hidden md:block"></div>

          <div class="flex items-center gap-1.5 shrink-0 h-[28px] md:h-[34px] mt-3.5">
              <button type="button" class="h-full px-3 md:px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md flex items-center justify-center transition shadow-sm font-bold text-[10px] md:text-xs uppercase tracking-wider" onclick="simpanKomitmen()">
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" class="md:mr-1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                  <span class="hidden md:inline">SIMPAN KOMITMEN</span>
              </button>
          </div>
      </form>
  </div>

  <div class="flex-1 overflow-hidden bg-white rounded-xl shadow-sm border border-slate-200 relative">
    <div class="h-full overflow-auto custom-scrollbar relative">
      <table class="w-max min-w-full text-xs text-left border-separate border-spacing-0 text-slate-700" id="tabelMonev">
        <thead class="tracking-wider bg-slate-100 text-slate-700 font-bold uppercase text-[9px] md:text-[10px]" id="headMonev">
          </thead>
        <tbody id="bodyMonev" class="divide-y divide-slate-100 bg-white">
          </tbody>
      </table>
    </div>
  </div>

</div>

<script>
  // --- MASTER DATA PARAMETER MONEV (Sesuai Excel User) ---
  // is_auto_realisasi: true (dihitung db), false (diketik manual oleh cabang di kolom realisasi)
  const masterParams = [
      { kategori: 'INFO UMUM', items: [
          { kode: '0001', nama: 'Kepala Cabang', tipe: 'text', is_auto_realisasi: false },
          { kode: '0002', nama: 'Penyebab Kredit Tidak Tercapai', tipe: 'text', is_auto_realisasi: false },
          { kode: '0003', nama: 'Penyebab NPL Tidak Tercapai', tipe: 'text', is_auto_realisasi: false },
      ]},
      { kategori: 'KREDIT', items: [
          { kode: '10601', nama: 'Baki Debet Kredit', tipe: 'rp', is_auto_realisasi: true },
          { kode: '0005', nama: 'Pencairan Kredit Rp', tipe: 'rp', is_auto_realisasi: true },
          { kode: '0006', nama: 'Jumlah NOA (Kredit)', tipe: 'number', is_auto_realisasi: true },
      ]},
      { kategori: 'DANA PIHAK KETIGA (DPK)', items: [
          { kode: '0007', nama: 'DAMAS Rp', tipe: 'rp', is_auto_realisasi: true },
          { kode: '0008', nama: 'DAMAS NOA', tipe: 'number', is_auto_realisasi: true },
          { kode: '0009', nama: 'Deposito Rp', tipe: 'rp', is_auto_realisasi: true },
          { kode: '0010', nama: 'Deposito NOA', tipe: 'number', is_auto_realisasi: true },
          { kode: '0011', nama: 'Tabungan Rp', tipe: 'rp', is_auto_realisasi: true },
          { kode: '0012', nama: 'Tabungan NOA', tipe: 'number', is_auto_realisasi: true },
          { kode: '0013', nama: '% CASA', tipe: 'percent', is_auto_realisasi: true },
      ]},
      { kategori: 'PENYELESAIAN NPL', items: [
          { kode: '0014', nama: 'Penyelesaian PAR 1-2 Rp', tipe: 'rp', is_auto_realisasi: true },
          { kode: '0015', nama: 'Penyelesaian PAR 1-2 NOA', tipe: 'number', is_auto_realisasi: true },
          { kode: '0016', nama: 'Penyelesaian PAR 3-4 Rp', tipe: 'rp', is_auto_realisasi: true },
          { kode: '0017', nama: 'Penyelesaian PAR 3-4 NOA', tipe: 'number', is_auto_realisasi: true },
          { kode: '0018', nama: 'Penyelesaian KL Rp', tipe: 'rp', is_auto_realisasi: true },
          { kode: '0019', nama: 'Penyelesaian KL NOA', tipe: 'number', is_auto_realisasi: true },
          { kode: '0020', nama: 'Recovery KL-D-M Rp', tipe: 'rp', is_auto_realisasi: true },
          { kode: '0021', nama: 'Recovery KL-D-M NOA', tipe: 'number', is_auto_realisasi: true },
          { kode: '0022', nama: 'Penyelesaian PH Rp', tipe: 'rp', is_auto_realisasi: true },
          { kode: '0023', nama: 'Penyelesaian PH NOA', tipe: 'number', is_auto_realisasi: true },
          { kode: '0024', nama: 'Pemulihan CKPN Rp', tipe: 'rp', is_auto_realisasi: true },
          { kode: '0025', nama: 'Pemulihan CKPN NOA', tipe: 'number', is_auto_realisasi: true },
          { kode: '0026', nama: 'Proyeksi NPL %', tipe: 'percent', is_auto_realisasi: true },
      ]},
      { kategori: 'PIPELINE', items: [
          { kode: '0027', nama: 'Pipeline Lunas Lancar Rp', tipe: 'rp', is_auto_realisasi: true },
          { kode: '0028', nama: 'Pipeline Lunas Lancar NOA', tipe: 'number', is_auto_realisasi: true },
          { kode: '0029', nama: 'Pipeline Jatuh Tempo Rp', tipe: 'rp', is_auto_realisasi: true },
          { kode: '0030', nama: 'Pipeline Jatuh Tempo NOA', tipe: 'number', is_auto_realisasi: true },
          { kode: '0031', nama: 'Pipeline Debitur Baru Rp', tipe: 'rp', is_auto_realisasi: true },
          { kode: '0032', nama: 'Pipeline Debitur Baru NOA', tipe: 'number', is_auto_realisasi: true },
          { kode: '0033', nama: 'Total Pipeline Rp', tipe: 'rp', is_auto_realisasi: true },
          { kode: '0034', nama: 'Total Pipeline NOA', tipe: 'number', is_auto_realisasi: true },
          { kode: '0035', nama: 'Backflow / BTC Rp', tipe: 'rp', is_auto_realisasi: true },
          { kode: '0036', nama: 'Backflow / BTC NOA', tipe: 'number', is_auto_realisasi: true },
      ]},
      { kategori: 'STRATEGI & KINERJA', items: [
          { kode: '0037', nama: 'Narasi Strategi NPL Minggu Ini', tipe: 'text', is_auto_realisasi: false },
          { kode: '0038', nama: 'Pendapatan', tipe: 'rp', is_auto_realisasi: true },
          { kode: '0039', nama: 'Laba (Rugi)', tipe: 'rp', is_auto_realisasi: true },
          { kode: '0040', nama: 'Jumlah Pegawai', tipe: 'number', is_auto_realisasi: false },
          { kode: '0041', nama: 'Produktivitas per Orang', tipe: 'rp', is_auto_realisasi: true },
      ]}
  ];

  const namaBulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

  window.addEventListener('DOMContentLoaded', () => {
      // Setup default bulan berjalan
      const dt = new Date();
      document.getElementById('filter_bulan').value = dt.getMonth() + 1;
      document.getElementById('filter_tahun').value = dt.getFullYear();
      
      renderTable();
  });

  // --- RENDER TABLE DINAMIS ---
  function renderTable() {
      const bln = parseInt(document.getElementById('filter_bulan').value);
      const thn = parseInt(document.getElementById('filter_tahun').value);
      
      // Hitung bulan lalu
      let blnLalu = bln - 1;
      let namaBulanLalu = namaBulan[blnLalu];
      if (blnLalu === 0) {
          namaBulanLalu = "Desember"; // Jika januari, prev month = desember
      }
      let namaBulanIni = namaBulan[bln];

      // 1. RENDER HEADER (Dinamis Nama Bulan)
      const thead = document.getElementById('headMonev');
      thead.innerHTML = `
          <tr>
            <th rowspan="2" class="sticky-left-1 px-4 border-r border-b border-slate-300 align-middle text-left bg-slate-100 min-w-[200px] w-[250px]">KATEGORI / PARAMETER REALISASI</th>
            <th class="px-2 py-2 border-r border-b border-slate-300 align-middle text-center bg-slate-200 text-slate-600">CLOSING<br>${namaBulanLalu.toUpperCase()}</th>
            <th colspan="2" class="px-2 py-2 border-r border-b border-blue-200 align-middle text-center bg-blue-100 text-blue-800">MINGGU I</th>
            <th colspan="2" class="px-2 py-2 border-r border-b border-blue-200 align-middle text-center bg-blue-100 text-blue-800">MINGGU II</th>
            <th colspan="2" class="px-2 py-2 border-r border-b border-blue-200 align-middle text-center bg-blue-100 text-blue-800">MINGGU III</th>
            <th colspan="2" class="px-2 py-2 border-r border-b border-blue-200 align-middle text-center bg-blue-100 text-blue-800">MINGGU IV</th>
            <th colspan="2" class="px-2 py-2 border-b border-purple-200 align-middle text-center bg-purple-100 text-purple-800">CLOSING<br>${namaBulanIni.toUpperCase()}</th>
          </tr>
          <tr>
            <th class="px-2 py-1.5 border-r border-b border-slate-300 bg-slate-50 text-center w-[120px]">ACTUAL (DB)</th>
            
            <th class="px-2 py-1.5 border-r border-b border-blue-200 bg-white text-center w-[110px]">KOMITMEN</th>
            <th class="px-2 py-1.5 border-r border-b border-blue-200 bg-emerald-50/50 text-center w-[110px] text-emerald-700">REALISASI</th>

            <th class="px-2 py-1.5 border-r border-b border-blue-200 bg-white text-center w-[110px]">KOMITMEN</th>
            <th class="px-2 py-1.5 border-r border-b border-blue-200 bg-emerald-50/50 text-center w-[110px] text-emerald-700">REALISASI</th>

            <th class="px-2 py-1.5 border-r border-b border-blue-200 bg-white text-center w-[110px]">KOMITMEN</th>
            <th class="px-2 py-1.5 border-r border-b border-blue-200 bg-emerald-50/50 text-center w-[110px] text-emerald-700">REALISASI</th>

            <th class="px-2 py-1.5 border-r border-b border-blue-200 bg-white text-center w-[110px]">KOMITMEN</th>
            <th class="px-2 py-1.5 border-r border-b border-blue-200 bg-emerald-50/50 text-center w-[110px] text-emerald-700">REALISASI</th>

            <th class="px-2 py-1.5 border-r border-b border-purple-200 bg-white text-center w-[110px]">KOMITMEN</th>
            <th class="px-2 py-1.5 border-b border-purple-200 bg-emerald-50/50 text-center w-[110px] text-emerald-700">REALISASI</th>
          </tr>
      `;

      // 2. RENDER BODY
      const tbody = document.getElementById('bodyMonev');
      let html = '';

      masterParams.forEach(group => {
          // Baris Grup Kategori
          html += `
            <tr class="bg-slate-100/80">
              <td class="sticky-left-1 px-4 py-2 font-bold text-[10px] md:text-[11px] text-slate-800 border-b border-slate-200 shadow-[inset_-1px_0_0_#cbd5e1]">${group.kategori}</td>
              <td colspan="11" class="border-b border-slate-200"></td>
            </tr>
          `;

          // Baris Item (Parameter)
          group.items.forEach(item => {
              
              // Fungsi helper untuk merender cell input
              const renderCell = (isKomitmen, weekId) => {
                  let inputId = `${isKomitmen?'k':'r'}_${item.kode}_${weekId}`;
                  
                  if (isKomitmen) {
                      // Komitmen selalu diisi manual
                      if(item.tipe === 'text') {
                          return `<input type="text" id="${inputId}" class="input-komitmen text-left" placeholder="Ketik disini...">`;
                      } else if(item.tipe === 'percent') {
                          return `<div class="flex items-center"><input type="number" step="0.01" id="${inputId}" class="input-komitmen"><span class="ml-1 text-slate-400">%</span></div>`;
                      } else {
                          return `<input type="text" id="${inputId}" class="input-komitmen" placeholder="0" oninput="formatRupiahUi(this)" onblur="formatRupiahUi(this)">`;
                      }
                  } else {
                      // Realisasi
                      if(item.is_auto_realisasi) {
                          // Jika auto, tampilkan box hijau / abu-abu (Readonly visual)
                          let simTitle = item.tipe === 'rp' ? '0' : (item.tipe==='percent' ? '0%' : '0');
                          return `<div class="box-realisasi" id="${inputId}" title="Auto Generate dari Sistem">${simTitle}</div>`;
                      } else {
                          // Jika manual realisasinya (seperti Narasi), kasih form input biasa
                          return `<input type="text" id="${inputId}" class="input-komitmen text-left bg-emerald-50/30 border-emerald-200" placeholder="Hasil realisasi...">`;
                      }
                  }
              };

              html += `
                <tr class="data-row transition border-b border-slate-100 h-[40px]">
                  <td class="sticky-left-1 px-4 py-1.5 border-r border-slate-200 shadow-[inset_-1px_0_0_#e2e8f0]">
                      <div class="flex items-center gap-2">
                        <span class="text-slate-400 font-mono text-[9px] w-6 shrink-0">${item.kode}</span>
                        <span class="font-medium text-slate-700 leading-tight">${item.nama}</span>
                      </div>
                  </td>
                  
                  <td class="px-2 py-1.5 border-r border-slate-200 bg-slate-50/50">
                      ${renderCell(false, 'M1')}
                  </td>

                  <td class="px-2 py-1.5 border-r border-blue-100 bg-white">${renderCell(true, 'W1')}</td>
                  <td class="px-2 py-1.5 border-r border-slate-200 bg-emerald-50/30">${renderCell(false, 'W1')}</td>

                  <td class="px-2 py-1.5 border-r border-blue-100 bg-white">${renderCell(true, 'W2')}</td>
                  <td class="px-2 py-1.5 border-r border-slate-200 bg-emerald-50/30">${renderCell(false, 'W2')}</td>

                  <td class="px-2 py-1.5 border-r border-blue-100 bg-white">${renderCell(true, 'W3')}</td>
                  <td class="px-2 py-1.5 border-r border-slate-200 bg-emerald-50/30">${renderCell(false, 'W3')}</td>

                  <td class="px-2 py-1.5 border-r border-blue-100 bg-white">${renderCell(true, 'W4')}</td>
                  <td class="px-2 py-1.5 border-r border-slate-200 bg-emerald-50/30">${renderCell(false, 'W4')}</td>

                  <td class="px-2 py-1.5 border-r border-purple-100 bg-white">${renderCell(true, 'M')}</td>
                  <td class="px-2 py-1.5 border-b border-slate-100 bg-emerald-50/30">${renderCell(false, 'M')}</td>
                </tr>
              `;
          });
      });

      tbody.innerHTML = html;
  }

  // --- HELPER FORMAT ANGKA DI INPUTAN ---
  function formatRupiahUi(input) {
      let val = input.value.replace(/[^,\d]/g, '').toString();
      let split = val.split(',');
      let sisa = split[0].length % 3;
      let rupiah = split[0].substr(0, sisa);
      let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

      if (ribuan) {
          let separator = sisa ? '.' : '';
          rupiah += separator + ribuan.join('.');
      }
      rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
      input.value = rupiah;
  }

  // --- SIMULASI SIMPAN DATA ---
  function simpanKomitmen() {
      // Disini nanti logic fetch POST ke backend untuk menyimpan komitmen
      const btn = event.target.closest('button');
      const oriTxt = btn.innerHTML;
      btn.innerHTML = `<span class="animate-spin inline-block h-3 w-3 border-2 border-white border-t-transparent rounded-full mr-2"></span> MENYIMPAN...`;
      
      setTimeout(() => {
          btn.innerHTML = oriTxt;
          alert("✅ Komitmen MONEV berhasil disimpan untuk bulan ini!");
      }, 1000);
  }

</script>