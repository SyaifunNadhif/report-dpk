<style>
  .custom-scrollbar::-webkit-scrollbar { height: 8px; width: 6px; }
  .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
  .no-scrollbar::-webkit-scrollbar { display: none; }
  .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

  /* STICKY TABLE */
  #tabelMonev thead th { position: sticky; top: 0; z-index: 40; box-shadow: inset 0 -1px 0 #cbd5e1, inset -1px 0 0 #cbd5e1; height: 40px; }
  .sticky-left-1 { position: sticky; left: 0; z-index: 20; background: white; box-shadow: inset -1px 0 0 #cbd5e1; }
  #tabelMonev thead th.sticky-left-1 { z-index: 50; background-color: #f1f5f9; }

  #bodyMonev tr.data-row:hover td { background-color: #eff6ff !important; }
  #bodyMonev tr.data-row:hover td.sticky-left-1 { background-color: #eff6ff !important; }

  /* BOX STYLING */
  .input-komitmen { width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 8px; font-size: 11px; font-family: monospace; text-align: right; outline: none; transition: all 0.2s; background: white; }
  .input-komitmen:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
  .input-komitmen.text-left { text-align: left; font-family: sans-serif; }
  
  /* Style Saat Input Terkunci */
  .input-locked { background: #f1f5f9 !important; border: 1px solid #e2e8f0 !important; color: #64748b !important; cursor: not-allowed; font-weight: bold; }

  .box-realisasi { width: 100%; padding: 6px 8px; font-size: 11px; font-family: monospace; text-align: right; background: #ecfdf5; color: #065f46; font-weight: bold; border-radius: 6px; border: 1px dashed #a7f3d0; }
  .box-realisasi.text-left { text-align: left; font-family: sans-serif; background: #f8fafc; color:#334155; border: 1px dashed #cbd5e1; font-weight: normal;}
  
  .box-referensi { width: 100%; padding: 6px 8px; font-size: 11px; font-family: monospace; text-align: right; background: #f8fafc; color: #64748b; font-weight: bold; border-radius: 6px; border: 1px solid #e2e8f0; }
  .box-referensi.text-left { text-align: left; font-family: sans-serif; font-weight: normal;}
</style>

<div class="max-w-[1500px] mx-auto px-2 md:px-4 py-3 md:py-5 h-[calc(100vh-80px)] flex flex-col bg-slate-50 font-sans text-slate-800 overflow-hidden">
  
  <div class="flex-none mb-3 flex flex-col md:flex-row justify-between md:items-end gap-3 w-full">
      <div class="flex flex-col gap-1.5 shrink-0">
          <h1 class="text-xl md:text-2xl font-bold text-slate-800 flex items-center gap-2">
              <span class="p-1.5 md:p-2 bg-blue-600 rounded-lg text-white shadow-sm">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
              </span>
              Input MONEV Mingguan
          </h1>
          <p class="text-[10px] md:text-xs text-slate-500 font-medium">Monitoring Komitmen dan Realisasi Pencapaian Cabang</p>
      </div>

      <form id="formFilterMonev" class="bg-white p-2.5 rounded-xl border border-slate-200 shadow-sm flex flex-wrap items-center gap-2 md:gap-3 w-full md:w-auto shrink-0" onsubmit="event.preventDefault(); renderTable();">
          
          <div class="flex flex-col w-[110px] shrink-0">
              <label class="text-[8px] md:text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">BULAN</label>
              <select id="filter_bulan" class="border border-slate-200 rounded-md px-2 py-1.5 text-xs text-slate-700 outline-none focus:border-blue-500 cursor-pointer w-full font-medium" onchange="renderTable()">
                  <option value="1">Januari</option><option value="2">Februari</option><option value="3" selected>Maret</option>
                  <option value="4">April</option><option value="5">Mei</option><option value="6">Juni</option>
                  <option value="7">Juli</option><option value="8">Agustus</option><option value="9">September</option>
                  <option value="10">Oktober</option><option value="11">November</option><option value="12">Desember</option>
              </select>
          </div>

          <div class="flex flex-col w-[75px] shrink-0">
              <label class="text-[8px] md:text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">TAHUN</label>
              <input type="number" id="filter_tahun" class="border border-slate-200 rounded-md px-2 py-1.5 text-xs text-slate-700 outline-none focus:border-blue-500 w-full font-medium" value="2026" onchange="renderTable()">
          </div>

          <div class="w-px h-6 bg-slate-200 shrink-0 mx-0.5 hidden md:block"></div>

          <div class="flex flex-col w-[130px] shrink-0">
              <label class="text-[8px] md:text-[9px] font-bold text-blue-600 uppercase tracking-widest mb-1">MINGGU KE</label>
              <select id="filter_minggu" class="border border-blue-300 bg-blue-50 rounded-md px-2 py-1.5 text-xs text-blue-800 outline-none focus:border-blue-500 cursor-pointer w-full font-bold shadow-sm" onchange="renderTable()">
                  <option value="W1">Minggu I (Satu)</option>
                  <option value="W2">Minggu II (Dua)</option>
                  <option value="W3">Minggu III (Tiga)</option>
                  <option value="W4">Minggu IV (Empat)</option>
                  <option value="C">Closing Bulan Ini</option>
              </select>
          </div>
          
          <div class="w-px h-6 bg-slate-200 shrink-0 mx-0.5 hidden md:block"></div>

          <div class="flex flex-col w-[130px] shrink-0">
              <label class="text-[8px] md:text-[9px] font-bold text-rose-600 uppercase tracking-widest mb-1 flex items-center gap-1">
                  STATUS DB <span class="bg-rose-100 text-rose-600 px-1 rounded text-[7px]">SIMULASI</span>
              </label>
              <select id="simulasi_status" class="border border-rose-300 bg-rose-50 rounded-md px-2 py-1.5 text-xs text-rose-800 outline-none cursor-pointer w-full font-bold shadow-sm" onchange="renderTable()">
                  <option value="DRAFT">DRAFT (Buka)</option>
                  <option value="LOCKED">TERKUNCI (Tutup)</option>
              </select>
          </div>

          <div class="flex items-center gap-1.5 shrink-0 h-[34px] mt-[18px]" id="btnContainer">
              </div>
      </form>
  </div>

  <div class="flex-none bg-white p-3 md:p-4 rounded-xl border border-slate-200 shadow-sm flex flex-wrap md:flex-nowrap gap-4 md:gap-6 mb-3 w-full items-center">
      <div class="flex flex-col flex-1 min-w-[200px]">
          <label class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg> Pimpinan Cabang
          </label>
          <input type="text" id="identitas_pinca" class="border border-slate-200 rounded-md px-3 py-1.5 text-sm text-slate-800 font-bold focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none w-full transition placeholder-slate-300" placeholder="Ketik Nama Pinca...">
      </div>
      <div class="hidden md:block w-px bg-slate-200 h-10 self-center"></div>
      <div class="flex flex-col flex-1 min-w-[200px]">
          <label class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg> Kabid Pemasaran
          </label>
          <input type="text" id="identitas_pemasaran" class="border border-slate-200 rounded-md px-3 py-1.5 text-sm text-slate-800 font-medium focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none w-full transition placeholder-slate-300" placeholder="Ketik Nama Kabid Pemasaran...">
      </div>
      <div class="hidden md:block w-px bg-slate-200 h-10 self-center"></div>
      <div class="flex flex-col flex-1 min-w-[200px]">
          <label class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> Kabid Operasional
          </label>
          <input type="text" id="identitas_ops" class="border border-slate-200 rounded-md px-3 py-1.5 text-sm text-slate-800 font-medium focus:border-purple-500 focus:ring-1 focus:ring-purple-500 outline-none w-full transition placeholder-slate-300" placeholder="Ketik Nama Kabid Operasional...">
      </div>
  </div>

  <div class="flex-1 overflow-hidden bg-white rounded-xl shadow-sm border border-slate-200 relative">
    <div class="h-full overflow-auto custom-scrollbar relative">
      <table class="w-full min-w-[900px] text-xs text-left border-separate border-spacing-0 text-slate-700" id="tabelMonev">
        <thead class="tracking-wider bg-slate-100 text-slate-700 font-bold uppercase text-[10px]" id="headMonev">
          </thead>
        <tbody id="bodyMonev" class="divide-y divide-slate-100 bg-white">
          </tbody>
      </table>
    </div>
  </div>

</div>

<script>
  // --- MASTER DATA PARAMETER MONEV ---
  // Parameter 0001 (Kepala Cabang) dihapus dari matriks karena sudah pindah ke atas jadi inputan.
  const masterParams = [
      { kategori: 'KREDIT', items: [
          { kode: '10601', nama: 'Baki Debet Kredit', tipe: 'rp', is_auto_realisasi: true },
          { kode: '0005', nama: 'Pencairan Kredit Rp', tipe: 'rp', is_auto_realisasi: true },
          { kode: '0002', nama: 'Penyebab Pencairan Tidak Tercapai', tipe: 'text', is_auto_realisasi: false }, // Kondisional
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
          { kode: '0003', nama: 'Penyebab NPL Tidak Tercapai', tipe: 'text', is_auto_realisasi: false }, // Kondisional
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
      const dt = new Date();
      document.getElementById('filter_bulan').value = dt.getMonth() + 1;
      document.getElementById('filter_tahun').value = dt.getFullYear();
      
      // Simulasi narik data nama dari database (Misal dari minggu lalu)
      setTimeout(() => {
          document.getElementById('identitas_pinca').value = "Bpk. Ahmad Subarjo";
          document.getElementById('identitas_pemasaran').value = "Bpk. Budi Santoso";
          document.getElementById('identitas_ops').value = "Ibu Siti Aminah";
      }, 500);

      renderTable();
  });

  function renderTable() {
      const bln = parseInt(document.getElementById('filter_bulan').value);
      const minggu = document.getElementById('filter_minggu').value;
      const isLocked = document.getElementById('simulasi_status').value === 'LOCKED'; 
      
      let blnLalu = bln - 1; let namaBulanLalu = namaBulan[blnLalu];
      if (blnLalu === 0) { namaBulanLalu = "Desember"; }
      let namaBulanIni = namaBulan[bln];

      // 1. ATUR TOMBOL HEADER DAN FORM IDENTITAS
      const btnContainer = document.getElementById('btnContainer');
      
      // Mengunci Input Identitas jika status Locked
      ['identitas_pinca', 'identitas_pemasaran', 'identitas_ops'].forEach(id => {
          const el = document.getElementById(id);
          if (isLocked) {
              el.readOnly = true;
              el.classList.add('bg-slate-100', 'text-slate-500', 'cursor-not-allowed', 'border-transparent');
              el.classList.remove('focus:ring-1');
          } else {
              el.readOnly = false;
              el.classList.remove('bg-slate-100', 'text-slate-500', 'cursor-not-allowed', 'border-transparent');
              el.classList.add('focus:ring-1');
          }
      });

      if (isLocked) {
          btnContainer.innerHTML = `
            <div class="h-full px-4 bg-slate-200 text-slate-500 rounded-md flex items-center justify-center font-bold text-[10px] md:text-xs uppercase tracking-wider cursor-not-allowed border border-slate-300">
                🔒 DATA TERKUNCI (LOCKED)
            </div>
          `;
      } else {
          btnContainer.innerHTML = `
            <button type="button" class="h-full px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md flex items-center justify-center transition shadow-sm font-bold text-[10px] md:text-xs uppercase tracking-wider" onclick="simpanKomitmen()">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" class="mr-1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                SIMPAN KOMITMEN
            </button>
          `;
      }

      // 2. ATUR NAMA KOLOM HEADER
      let lblPrev = "", lblTarget = "", lblActual = "";
      if (minggu === 'W1') {
          lblPrev = `CLOSING ${namaBulanLalu.toUpperCase()}`;
          lblTarget = "KOMITMEN MINGGU I";
          lblActual = "REALISASI MINGGU I";
      } else if (minggu === 'W2') {
          lblPrev = "REALISASI MINGGU I";
          lblTarget = "KOMITMEN MINGGU II";
          lblActual = "REALISASI MINGGU II";
      } else if (minggu === 'W3') {
          lblPrev = "REALISASI MINGGU II";
          lblTarget = "KOMITMEN MINGGU III";
          lblActual = "REALISASI MINGGU III";
      } else if (minggu === 'W4') {
          lblPrev = "REALISASI MINGGU III";
          lblTarget = "KOMITMEN MINGGU IV";
          lblActual = "REALISASI MINGGU IV";
      } else if (minggu === 'C') {
          lblPrev = "REALISASI MINGGU IV";
          lblTarget = `KOMITMEN CLOSING ${namaBulanIni.toUpperCase()}`;
          lblActual = `REALISASI CLOSING ${namaBulanIni.toUpperCase()}`;
      }

      const thead = document.getElementById('headMonev');
      thead.innerHTML = `
          <tr>
            <th class="sticky-left-1 px-4 py-2.5 border-r border-b border-slate-300 align-middle text-left bg-slate-100 min-w-[250px] w-[30%] shadow-[inset_-1px_0_0_#cbd5e1]">KATEGORI / PARAMETER</th>
            
            <th class="px-4 py-2.5 border-r border-b border-slate-300 align-middle text-center bg-slate-200 text-slate-600 w-[20%]">
                <div class="text-[9px] text-slate-400 mb-0.5 font-medium">REFERENSI LALU</div>
                ${lblPrev}
            </th>
            
            <th class="px-4 py-2.5 border-r border-b border-blue-200 align-middle text-center bg-blue-100 text-blue-800 w-[25%]">
                <div class="text-[9px] text-blue-500 mb-0.5 font-medium">INPUT TARGET CABANG</div>
                ${lblTarget}
            </th>
            
            <th class="px-4 py-2.5 border-b border-emerald-200 align-middle text-center bg-emerald-100 text-emerald-800 w-[25%]">
                <div class="text-[9px] text-emerald-600 mb-0.5 font-medium">PENCAPAIAN ACTUAL (SISTEM)</div>
                ${lblActual}
            </th>
          </tr>
      `;

      // 3. RENDER BODY
      const tbody = document.getElementById('bodyMonev');
      let html = '';

      masterParams.forEach(group => {
          html += `
            <tr class="bg-slate-100/80">
              <td class="sticky-left-1 px-4 py-2 font-bold text-[11px] text-blue-900 border-b border-slate-200 shadow-[inset_-1px_0_0_#cbd5e1] uppercase">${group.kategori}</td>
              <td colspan="3" class="border-b border-slate-200"></td>
            </tr>
          `;

          group.items.forEach(item => {
              
              let isAlasanGagal = (item.kode === '0002' || item.kode === '0003');

              let simTarget = 1000;
              let simActual = (isAlasanGagal) ? 800 : 1200; 
              let isTercapai = simActual >= simTarget;

              const renderRefCell = () => {
                  if (isAlasanGagal) return `<div class="text-[10px] text-slate-400 italic text-center">-</div>`;
                  
                  let simTitle = item.tipe === 'rp' ? '0' : (item.tipe==='percent' ? '0%' : (item.tipe==='text' ? '-' : '0'));
                  let cls = item.tipe === 'text' ? 'text-left' : '';
                  return `<div class="box-referensi ${cls}" title="Data Minggu Sebelumnya">${simTitle}</div>`;
              };

              const renderKomitmenCell = () => {
                  let inputId = `k_${item.kode}_${minggu}`;
                  let statusAttr = isLocked ? 'readonly disabled' : '';
                  let statusCls  = isLocked ? 'input-locked' : 'border-blue-200 bg-blue-50/20';

                  if (isAlasanGagal) {
                      return `<div class="text-[9px] text-slate-400 italic text-center py-1.5 bg-slate-50/50 rounded-md border border-dashed border-slate-200">Menunggu hasil aktual pencapaian...</div>`;
                  }

                  if(item.tipe === 'text') {
                      return `<input type="text" id="${inputId}" class="input-komitmen text-left ${statusCls}" placeholder="Ketik rencana / narasi..." ${statusAttr}>`;
                  } else if(item.tipe === 'percent') {
                      return `<div class="flex items-center"><input type="number" step="0.01" id="${inputId}" class="input-komitmen ${statusCls}" ${statusAttr}><span class="ml-1.5 text-blue-600 font-bold">%</span></div>`;
                  } else {
                      return `<input type="text" id="${inputId}" class="input-komitmen ${statusCls}" placeholder="0" oninput="formatRupiahUi(this)" onblur="formatRupiahUi(this)" ${statusAttr}>`;
                  }
              };

              const renderRealisasiCell = () => {
                  let inputId = `r_${item.kode}_${minggu}`;
                  let statusAttr = isLocked ? 'readonly disabled' : '';
                  
                  if (isAlasanGagal) {
                      if (isTercapai) {
                          return `<div class="text-[10px] text-emerald-600 font-bold text-center py-1.5 bg-emerald-50 rounded-md border border-emerald-200 shadow-sm">✅ TERCAPAI</div>`;
                      } else {
                          return `<input type="text" id="${inputId}" class="input-komitmen text-left bg-rose-50 border-rose-300 text-rose-800 placeholder-rose-400 focus:border-rose-500 focus:ring-1 focus:ring-rose-400" placeholder="Wajib isi: Alasan tidak tercapai..." ${statusAttr}>`;
                      }
                  }

                  if(item.is_auto_realisasi) {
                      let simTitle = item.tipe === 'rp' ? '0' : (item.tipe==='percent' ? '0%' : '0');
                      return `<div class="box-realisasi" id="${inputId}" title="Auto Generate dari Database">${simTitle}</div>`;
                  } else {
                      return `<input type="text" id="${inputId}" class="input-komitmen text-left bg-emerald-50/50 border-emerald-300 text-emerald-800 font-medium" placeholder="Evaluasi / Capaian..." ${statusAttr}>`;
                  }
              };

              let rowCls = isAlasanGagal ? 'bg-rose-50/20' : '';

              html += `
                <tr class="data-row transition border-b border-slate-100 h-[46px] ${rowCls}">
                  <td class="sticky-left-1 px-4 py-2 border-r border-slate-200 shadow-[inset_-1px_0_0_#e2e8f0] ${isAlasanGagal ? 'bg-rose-50/20' : 'bg-white'}">
                      <div class="flex items-center gap-2.5">
                        <span class="text-slate-400 font-mono text-[9px] w-8 shrink-0 bg-slate-50 px-1 py-0.5 rounded border border-slate-200 text-center">${item.kode}</span>
                        <span class="${isAlasanGagal ? 'font-bold text-rose-700' : 'font-medium text-slate-700'} leading-tight">${item.nama}</span>
                      </div>
                  </td>
                  
                  <td class="px-3 py-2 border-r border-slate-200 bg-slate-50/50 align-middle">${renderRefCell()}</td>
                  <td class="px-3 py-2 border-r border-blue-100 bg-white align-middle">${renderKomitmenCell()}</td>
                  <td class="px-3 py-2 border-b border-emerald-100 bg-emerald-50/20 align-middle">${renderRealisasiCell()}</td>
                </tr>
              `;
          });
      });

      tbody.innerHTML = html;
  }

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

  function simpanKomitmen() {
      const minggu = document.getElementById('filter_minggu').options[document.getElementById('filter_minggu').selectedIndex].text;
      const btn = event.target.closest('button');
      const oriTxt = btn.innerHTML;
      
      btn.innerHTML = `<span class="animate-spin inline-block h-3 w-3 border-2 border-white border-t-transparent rounded-full mr-2"></span> MENYIMPAN...`;
      btn.disabled = true;
      
      setTimeout(() => {
          btn.innerHTML = oriTxt;
          btn.disabled = false;
          alert(`✅ Data untuk ${minggu} berhasil disimpan!`);
      }, 1000);
  }
</script>