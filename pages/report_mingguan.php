<style>
  .custom-scrollbar::-webkit-scrollbar { height: 8px; width: 6px; }
  .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
  .no-scrollbar::-webkit-scrollbar { display: none; }
  .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

  /* STICKY TABLE */
  #tabelReport thead th { position: sticky; box-shadow: inset 0 -1px 0 #cbd5e1, inset -1px 0 0 #e2e8f0; background-clip: padding-box; }
  #tabelReport thead tr:nth-child(1) th { top: 0; z-index: 50; height: 38px; background-color: #f1f5f9; }
  #tabelReport thead tr:nth-child(2) th { top: 38px; z-index: 49; height: 36px; background-color: #ffffff; }

  .sticky-left-1 { position: sticky; left: 0; z-index: 30; background-color: #ffffff; box-shadow: inset -1px 0 0 #cbd5e1; }
  .sticky-left-2 { position: sticky; left: 45px; z-index: 30; background-color: #ffffff; box-shadow: inset -1px 0 0 #cbd5e1; }
  
  #tabelReport thead tr:nth-child(1) th.sticky-left-1, #tabelReport thead tr:nth-child(1) th.sticky-left-2 { z-index: 60; background-color: #f1f5f9; }
  #tabelReport thead tr:nth-child(2) th.sticky-left-1, #tabelReport thead tr:nth-child(2) th.sticky-left-2 { z-index: 59; background-color: #f8fafc; }

  #bodyReport tr.data-row:hover td { background-color: #eff6ff !important; cursor: pointer; }
  #bodyReport tr.data-row:hover td.sticky-left-1, #bodyReport tr.data-row:hover td.sticky-left-2 { background-color: #eff6ff !important; }

  .pct-blue { color: #1d4ed8; background-color: #eff6ff !important; font-weight: 800; }
  .pct-green { color: #047857; background-color: #ecfdf5 !important; font-weight: 800; }
  .pct-yellow { color: #b45309; background-color: #fffbeb !important; font-weight: 700; }
  .pct-red { color: #be123c; background-color: #fff1f2 !important; font-weight: 700; }
</style>

<div class="max-w-[1920px] mx-auto px-2 md:px-4 py-3 md:py-5 h-[calc(100vh-80px)] flex flex-col bg-slate-50 font-sans text-slate-800 overflow-hidden">
  
  <div class="flex-none mb-3 flex flex-col xl:flex-row justify-between xl:items-end gap-3 w-full">
      <div class="flex flex-col gap-1.5 shrink-0">
          <h1 class="text-xl md:text-2xl font-bold text-slate-800 flex items-center gap-2">
              <span class="p-1.5 md:p-2 bg-purple-600 rounded-lg text-white shadow-sm">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
              </span>
              Report Komitmen Mingguan
          </h1>
          <p class="text-[10px] md:text-xs text-slate-500 font-medium">Perbandingan Kinerja Antar Cabang per Wilayah</p>
      </div>

      <form class="bg-white p-2 md:p-2.5 rounded-xl border border-slate-200 shadow-sm flex flex-nowrap items-center gap-1.5 md:gap-3 w-full xl:w-auto shrink-0 overflow-x-auto no-scrollbar" onsubmit="event.preventDefault(); renderTable();">
          
          <div class="flex flex-col w-[110px] shrink-0">
              <label class="text-[8px] md:text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">BULAN</label>
              <select id="filter_bulan" class="border border-slate-200 rounded-md px-2 py-1.5 text-xs text-slate-700 outline-none focus:border-purple-500 cursor-pointer w-full font-medium" onchange="renderTable()">
                  <option value="1">Januari</option><option value="2">Februari</option><option value="3" selected>Maret</option>
                  <option value="4">April</option><option value="5">Mei</option>
              </select>
          </div>

          <div class="flex flex-col w-[100px] shrink-0">
              <label class="text-[8px] md:text-[9px] font-bold text-purple-600 uppercase tracking-widest mb-1">MINGGU KE</label>
              <select id="filter_minggu" class="border border-purple-300 bg-purple-50 rounded-md px-2 py-1.5 text-xs text-purple-800 outline-none focus:border-purple-500 cursor-pointer w-full font-bold shadow-sm" onchange="renderTable()">
                  <option value="W1" selected>Minggu I</option>
                  <option value="W2">Minggu II</option>
                  <option value="W3">Minggu III</option>
                  <option value="W4">Minggu IV</option>
                  <option value="C">Closing</option>
              </select>
          </div>

          <div class="w-px h-6 bg-slate-200 shrink-0 mx-1 hidden md:block"></div>

          <div class="flex flex-col w-[150px] shrink-0">
              <label class="text-[8px] md:text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">KORWIL</label>
              <select id="filter_korwil" class="border border-slate-200 rounded-md px-2 py-1.5 text-xs text-slate-700 outline-none focus:border-purple-500 cursor-pointer w-full font-medium" onchange="renderTable()">
                  <option value="ALL">SEMUA KORWIL</option>
                  <option value="SEMARANG">KORWIL SEMARANG</option>
                  <option value="SOLO" selected>KORWIL SOLO</option>
                  <option value="BANYUMAS">KORWIL BANYUMAS</option>
                  <option value="PEKALONGAN">KORWIL PEKALONGAN</option>
              </select>
          </div>
          
          <div class="flex items-center gap-1.5 shrink-0 h-[28px] md:h-[34px] mb-px mt-3.5">
              <button type="button" class="h-full px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md flex items-center justify-center transition shadow-sm font-bold text-[10px] md:text-xs uppercase tracking-wider">
                  <span class="hidden md:inline">DOWNLOAD EXCEL</span>
              </button>
          </div>
      </form>
  </div>

  <div class="flex-1 overflow-hidden bg-white rounded-xl shadow-sm border border-slate-200 relative flex flex-col">
    <div class="flex-none bg-slate-50 px-4 py-2 border-b border-slate-200 flex items-center gap-4 text-[9px] md:text-[10px] font-bold text-slate-500 uppercase tracking-widest shrink-0">
        <span>Keterangan % Target:</span>
        <div class="flex items-center gap-1"><div class="w-3 h-3 bg-[#eff6ff] border border-[#1d4ed8] rounded-sm"></div> <span class="text-blue-700">> 100%</span></div>
        <div class="flex items-center gap-1"><div class="w-3 h-3 bg-[#ecfdf5] border border-[#047857] rounded-sm"></div> <span class="text-emerald-700">100%</span></div>
        <div class="flex items-center gap-1"><div class="w-3 h-3 bg-[#fffbeb] border border-[#b45309] rounded-sm"></div> <span class="text-amber-700">75% - 99%</span></div>
        <div class="flex items-center gap-1"><div class="w-3 h-3 bg-[#fff1f2] border border-[#be123c] rounded-sm"></div> <span class="text-rose-700">< 75%</span></div>
    </div>

    <div class="flex-1 overflow-auto custom-scrollbar relative">
      <table class="w-max min-w-full text-xs text-right border-separate border-spacing-0 text-slate-700" id="tabelReport">
        <thead class="tracking-wider bg-slate-100 text-slate-700 font-bold uppercase text-[9px] md:text-[10px]">
          <tr>
            <th rowspan="2" class="sticky-left-1 px-3 border-r border-b border-slate-300 align-middle text-center bg-slate-200 w-[45px]">KODE</th>
            <th rowspan="2" class="sticky-left-2 px-4 border-r border-b border-slate-300 align-middle text-left bg-slate-200 min-w-[160px]">NAMA CABANG</th>
            
            <th colspan="3" class="px-2 py-2 border-r border-b border-slate-300 align-middle text-center bg-blue-50 text-blue-800">PENCAIRAN KREDIT</th>
            <th colspan="3" class="px-2 py-2 border-r border-b border-slate-300 align-middle text-center bg-white text-slate-700">BAKI DEBET</th>
            <th colspan="3" class="px-2 py-2 border-r border-b border-slate-300 align-middle text-center bg-emerald-50 text-emerald-800">TABUNGAN</th>
            <th colspan="3" class="px-2 py-2 border-b border-slate-300 align-middle text-center bg-white text-slate-700">DEPOSITO</th>
          </tr>
          <tr>
            <th class="px-2 py-1.5 border-r border-b border-slate-200 text-center w-[90px] bg-blue-50/50">Komitmen</th>
            <th class="px-2 py-1.5 border-r border-b border-slate-200 text-center w-[90px] bg-blue-50/50">Realisasi</th>
            <th class="px-2 py-1.5 border-r border-b border-slate-300 text-center w-[55px] bg-blue-50/50">%</th>

            <th class="px-2 py-1.5 border-r border-b border-slate-200 text-center w-[90px] bg-white">Komitmen</th>
            <th class="px-2 py-1.5 border-r border-b border-slate-200 text-center w-[90px] bg-white">Realisasi</th>
            <th class="px-2 py-1.5 border-r border-b border-slate-300 text-center w-[55px] bg-white">%</th>

            <th class="px-2 py-1.5 border-r border-b border-slate-200 text-center w-[90px] bg-emerald-50/50">Komitmen</th>
            <th class="px-2 py-1.5 border-r border-b border-slate-200 text-center w-[90px] bg-emerald-50/50">Realisasi</th>
            <th class="px-2 py-1.5 border-r border-b border-slate-300 text-center w-[55px] bg-emerald-50/50">%</th>

            <th class="px-2 py-1.5 border-r border-b border-slate-200 text-center w-[90px] bg-white">Komitmen</th>
            <th class="px-2 py-1.5 border-r border-b border-slate-200 text-center w-[90px] bg-white">Realisasi</th>
            <th class="px-2 py-1.5 border-b border-slate-300 text-center w-[55px] bg-white">%</th>
          </tr>
        </thead>
        <tbody id="bodyReport" class="divide-y divide-slate-100 bg-white">
          </tbody>
      </table>
    </div>
  </div>

</div>

<script>
  // --- DATA CABANG ---
  const masterCabang = [
      { kode: '001', nama: 'Kc. Utama', korwil: 'SEMARANG' },
      { kode: '002', nama: 'Kc. Rembang', korwil: 'SEMARANG' },
      { kode: '003', nama: 'Kc. Pati', korwil: 'SEMARANG' },
      { kode: '004', nama: 'Kc. Demak', korwil: 'SEMARANG' },
      { kode: '005', nama: 'Kc. Kendal', korwil: 'SEMARANG' },
      { kode: '006', nama: 'Kc. Salatiga', korwil: 'SEMARANG' },
      { kode: '007', nama: 'Kc. Kab. Semarang', korwil: 'SEMARANG' },

      { kode: '008', nama: 'Kc. Solo', korwil: 'SOLO' },
      { kode: '009', nama: 'Kc. Boyolali', korwil: 'SOLO' },
      { kode: '010', nama: 'Kc. Klaten', korwil: 'SOLO' },
      { kode: '011', nama: 'Kc. Sukoharjo', korwil: 'SOLO' },
      { kode: '012', nama: 'Kc. Wonogiri', korwil: 'SOLO' },
      { kode: '013', nama: 'Kc. Karanganyar', korwil: 'SOLO' },
      { kode: '014', nama: 'Kc. Sragen', korwil: 'SOLO' },

      { kode: '015', nama: 'Kc. Purwokerto', korwil: 'BANYUMAS' },
      { kode: '016', nama: 'Kc. Cilacap', korwil: 'BANYUMAS' },
      { kode: '017', nama: 'Kc. Purbalingga', korwil: 'BANYUMAS' },
      { kode: '018', nama: 'Kc. Banjarnegara', korwil: 'BANYUMAS' },
      
      { kode: '022', nama: 'Kc. Pekalongan', korwil: 'PEKALONGAN' },
      { kode: '023', nama: 'Kc. Tegal', korwil: 'PEKALONGAN' },
      { kode: '024', nama: 'Kc. Brebes', korwil: 'PEKALONGAN' },
  ];

  const nf = new Intl.NumberFormat('id-ID');
  const fmt = n => nf.format(Math.round(Number(n||0)));

  window.addEventListener('DOMContentLoaded', () => {
      renderTable();
  });

  function getPctClass(pct) {
      if (pct > 100) return 'pct-blue';
      if (pct === 100) return 'pct-green';
      if (pct >= 75 && pct < 100) return 'pct-yellow';
      if (pct < 75) return 'pct-red';
      return '';
  }

  function renderTable() {
      const selectedKorwil = document.getElementById('filter_korwil').value;
      const tbody = document.getElementById('bodyReport');
      let html = '';

      // Filter cabang berdasarkan dropdown Korwil
      let listCabang = masterCabang;
      if (selectedKorwil !== 'ALL') {
          listCabang = masterCabang.filter(c => c.korwil === selectedKorwil);
      }

      // Penampung Grand Total Korwil
      let gt = { pk: 0, pr: 0, bk: 0, br: 0, tk: 0, tr: 0, dk: 0, dr: 0 };

      listCabang.forEach(c => {
          // --- GENERATE DUMMY DATA ---
          // Pencairan
          let pk = Math.floor(Math.random() * 500000000 + 100000000); // Komitmen
          let pr = pk * (Math.random() * 0.4 + 0.7); // Realisasi (70% - 110%)
          let ppct = Math.round((pr / pk) * 100);

          // Baki Debet
          let bk = Math.floor(Math.random() * 5000000000 + 1000000000); 
          let br = bk * (Math.random() * 0.2 + 0.85); 
          let bpct = Math.round((br / bk) * 100);

          // Tabungan
          let tk = Math.floor(Math.random() * 2000000000 + 500000000); 
          let tr = tk * (Math.random() * 0.5 + 0.6); 
          let tpct = Math.round((tr / tk) * 100);

          // Deposito
          let dk = Math.floor(Math.random() * 3000000000 + 500000000); 
          let dr = dk * (Math.random() * 0.3 + 0.8); 
          let dpct = Math.round((dr / dk) * 100);

          // Akumulasi Total
          gt.pk += pk; gt.pr += pr; gt.bk += bk; gt.br += br;
          gt.tk += tk; gt.tr += tr; gt.dk += dk; gt.dr += dr;

          html += `
            <tr class="data-row transition border-b border-slate-100 h-[38px]">
              <td class="sticky-left-1 px-3 border-r border-slate-200 text-center font-mono text-slate-500 bg-white shadow-[inset_-1px_0_0_#e2e8f0]">${c.kode}</td>
              <td class="sticky-left-2 px-4 border-r border-slate-300 text-left font-bold text-slate-700 bg-white shadow-[inset_-1px_0_0_#e2e8f0]">${c.nama}</td>
              
              <td class="px-2 py-1.5 border-r border-slate-100 text-slate-500">${fmt(pk)}</td>
              <td class="px-2 py-1.5 border-r border-slate-100 font-bold text-slate-700">${fmt(pr)}</td>
              <td class="px-2 py-1.5 border-r border-slate-300 border-l border-l-slate-100 text-center ${getPctClass(ppct)}">${ppct}%</td>

              <td class="px-2 py-1.5 border-r border-slate-100 text-slate-500 bg-slate-50/50">${fmt(bk)}</td>
              <td class="px-2 py-1.5 border-r border-slate-100 font-bold text-slate-700 bg-slate-50/50">${fmt(br)}</td>
              <td class="px-2 py-1.5 border-r border-slate-300 border-l border-l-slate-100 text-center ${getPctClass(bpct)}">${bpct}%</td>

              <td class="px-2 py-1.5 border-r border-slate-100 text-slate-500">${fmt(tk)}</td>
              <td class="px-2 py-1.5 border-r border-slate-100 font-bold text-slate-700">${fmt(tr)}</td>
              <td class="px-2 py-1.5 border-r border-slate-300 border-l border-l-slate-100 text-center ${getPctClass(tpct)}">${tpct}%</td>

              <td class="px-2 py-1.5 border-r border-slate-100 text-slate-500 bg-slate-50/50">${fmt(dk)}</td>
              <td class="px-2 py-1.5 border-r border-slate-100 font-bold text-slate-700 bg-slate-50/50">${fmt(dr)}</td>
              <td class="px-2 py-1.5 border-b border-slate-300 border-l border-l-slate-100 text-center ${getPctClass(dpct)}">${dpct}%</td>
            </tr>
          `;
      });

      // --- BARIS GRAND TOTAL KORWIL ---
      let gppct = gt.pk > 0 ? Math.round((gt.pr / gt.pk) * 100) : 0;
      let gbpct = gt.bk > 0 ? Math.round((gt.br / gt.bk) * 100) : 0;
      let gtpct = gt.tk > 0 ? Math.round((gt.tr / gt.tk) * 100) : 0;
      let gdpct = gt.dk > 0 ? Math.round((gt.dr / gt.dk) * 100) : 0;

      let lblTotal = selectedKorwil === 'ALL' ? 'TOTAL KESELURUHAN' : `TOTAL ${selectedKorwil}`;

      html += `
          <tr class="h-[40px] bg-slate-100">
              <td class="sticky-left-1 border-r border-b border-slate-300 bg-slate-200"></td>
              <td class="sticky-left-2 px-4 border-r border-b border-slate-300 text-left font-bold text-slate-800 bg-slate-200 shadow-[inset_-1px_0_0_#cbd5e1] uppercase tracking-wider">${lblTotal}</td>
              
              <td class="px-2 py-1.5 border-r border-b border-blue-200 text-blue-900 font-bold bg-blue-100/50">${fmt(gt.pk)}</td>
              <td class="px-2 py-1.5 border-r border-b border-blue-200 text-blue-900 font-bold bg-blue-100/50">${fmt(gt.pr)}</td>
              <td class="px-2 py-1.5 border-r border-b border-blue-300 text-center ${getPctClass(gppct)} border-l border-l-blue-200">${gppct}%</td>

              <td class="px-2 py-1.5 border-r border-b border-slate-300 text-slate-800 font-bold bg-slate-200/50">${fmt(gt.bk)}</td>
              <td class="px-2 py-1.5 border-r border-b border-slate-300 text-slate-800 font-bold bg-slate-200/50">${fmt(gt.br)}</td>
              <td class="px-2 py-1.5 border-r border-b border-slate-400 text-center ${getPctClass(gbpct)} border-l border-l-slate-300">${gbpct}%</td>

              <td class="px-2 py-1.5 border-r border-b border-emerald-200 text-emerald-900 font-bold bg-emerald-100/50">${fmt(gt.tk)}</td>
              <td class="px-2 py-1.5 border-r border-b border-emerald-200 text-emerald-900 font-bold bg-emerald-100/50">${fmt(gt.tr)}</td>
              <td class="px-2 py-1.5 border-r border-b border-emerald-300 text-center ${getPctClass(gtpct)} border-l border-l-emerald-200">${gtpct}%</td>

              <td class="px-2 py-1.5 border-r border-b border-slate-300 text-slate-800 font-bold bg-slate-200/50">${fmt(gt.dk)}</td>
              <td class="px-2 py-1.5 border-r border-b border-slate-300 text-slate-800 font-bold bg-slate-200/50">${fmt(gt.dr)}</td>
              <td class="px-2 py-1.5 border-b border-slate-400 text-center ${getPctClass(gdpct)} border-l border-l-slate-300">${gdpct}%</td>
          </tr>
      `;

      tbody.innerHTML = html;
  }
</script>