<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="max-w-[1400px] mx-auto px-2 md:px-4 py-4 md:py-6 bg-gray-50 min-h-screen font-sans">
  
  <div class="flex flex-col md:flex-row justify-between md:items-end mb-4 md:mb-6 gap-3">
    <div>
      <h1 class="text-xl md:text-3xl font-extrabold text-gray-800 tracking-tight flex items-center gap-2">📊 Executive Dashboard</h1>
      <p class="text-xs md:text-sm text-gray-500 mt-0.5 md:mt-1 font-medium">Pusat Komando Portofolio & Kinerja Bisnis</p>
    </div>

    <form id="formFilterMaster" class="flex flex-col md:flex-row items-end gap-2.5 md:gap-3 bg-white p-2.5 md:p-3 rounded-xl shadow-sm border border-gray-200 w-full md:w-auto">
      
      <div class="flex w-full md:w-auto gap-2 shrink-0">
          <div class="flex flex-col flex-1 min-w-0 md:w-[130px]">
            <label class="text-[9px] md:text-[10px] font-bold text-gray-500 uppercase tracking-wider">Closing M-1</label>
            <input type="date" id="filter_closing" class="border-b-2 border-transparent hover:border-gray-300 px-1 py-1 text-[10px] md:text-sm outline-none focus:border-blue-500 transition-colors font-semibold cursor-pointer w-full bg-transparent">
          </div>
          <div class="flex flex-col flex-1 min-w-0 md:w-[130px]">
            <label class="text-[9px] md:text-[10px] font-bold text-gray-500 uppercase tracking-wider">Harian/Actual</label>
            <input type="date" id="filter_harian" class="border-b-2 border-transparent hover:border-gray-300 px-1 py-1 text-[10px] md:text-sm outline-none focus:border-blue-500 transition-colors font-semibold cursor-pointer w-full bg-transparent">
          </div>
      </div>

      <div class="flex w-full md:w-auto items-end gap-2 shrink-0 mt-0.5 md:mt-0">
          <div class="flex flex-col flex-1 min-w-0 md:w-[180px]">
            <label class="text-[9px] md:text-[10px] font-bold text-gray-500 uppercase tracking-wider">Area/Cabang</label>
            <select id="filter_kantor" class="border-b-2 border-transparent hover:border-gray-300 px-1 py-1 text-[10px] md:text-sm outline-none focus:border-blue-500 bg-transparent transition-colors font-bold text-gray-700 cursor-pointer w-full truncate"></select>
          </div>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white w-[34px] md:w-auto h-[32px] md:h-[36px] md:px-5 rounded-lg text-sm font-bold transition-all flex items-center justify-center gap-2 shadow-md transform active:scale-95 shrink-0 mb-[1px]">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="md:hidden"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <span class="hidden md:inline">Tampilkan</span>
          </button>
      </div>

    </form>
  </div>

  <div id="loadingDash" class="hidden flex flex-col justify-center items-center py-32">
    <div class="animate-spin rounded-full h-10 w-10 md:h-14 md:w-14 border-t-4 border-b-4 border-blue-600 mb-4"></div>
    <span class="text-xs md:text-sm text-gray-500 font-semibold animate-pulse">Loading data dari database...</span>
  </div>

  <div id="contentDash" class="hidden space-y-4 md:space-y-6 overflow-x-hidden">
    
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 md:gap-4">
      <div class="bg-white p-3.5 md:p-4 rounded-xl md:rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden col-span-2 md:col-span-1">
        <div class="absolute top-0 left-0 w-1.5 h-full bg-blue-500 rounded-l-2xl"></div>
        <p class="text-[9px] md:text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-0.5 md:mb-1 truncate">Total Baki Debet (OS)</p>
        <h3 id="kpi_os" class="text-lg md:text-xl xl:text-2xl font-black text-gray-900 tracking-tight whitespace-nowrap mb-1.5 md:mb-2.5">Rp 0</h3>
        <div id="kpi_os_pill"></div>
      </div>
      <div class="bg-white p-3.5 md:p-4 rounded-xl md:rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden col-span-1">
        <div class="absolute top-0 left-0 w-1.5 h-full bg-cyan-500 rounded-l-2xl"></div>
        <p class="text-[9px] md:text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-0.5 md:mb-1 truncate">Saldo Bank</p>
        <h3 id="kpi_saldobank" class="text-lg md:text-xl xl:text-2xl font-black text-cyan-600 tracking-tight whitespace-nowrap mb-1.5 md:mb-2.5">Rp 0</h3>
        <div id="kpi_saldobank_pill"></div>
      </div>
      <div class="bg-white p-3.5 md:p-4 rounded-xl md:rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden col-span-1">
        <div class="absolute top-0 left-0 w-1.5 h-full bg-red-500 rounded-l-2xl"></div>
        <p class="text-[9px] md:text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-0.5 md:mb-1 truncate">Total OSC NPL</p>
        <h3 id="kpi_npl" class="text-lg md:text-xl xl:text-2xl font-black text-red-600 tracking-tight whitespace-nowrap mb-1.5 md:mb-2.5">Rp 0</h3>
        <div id="kpi_npl_pill"></div>
      </div>
      <div class="bg-white p-3.5 md:p-4 rounded-xl md:rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden col-span-1">
        <div class="absolute top-0 left-0 w-1.5 h-full bg-green-500 rounded-l-2xl"></div>
        <p class="text-[9px] md:text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-0.5 md:mb-1 truncate">Repayment Rate (RR)</p>
        <h3 id="kpi_rr" class="text-lg md:text-xl xl:text-2xl font-black text-green-600 tracking-tight whitespace-nowrap mb-1.5 md:mb-2.5">Rp 0</h3>
        <div id="kpi_rr_pill"></div>
      </div>
      <div class="bg-white p-3.5 md:p-4 rounded-xl md:rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden col-span-1 md:col-span-1">
        <div class="absolute top-0 left-0 w-1.5 h-full bg-purple-500 rounded-l-2xl"></div>
        <p class="text-[9px] md:text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-0.5 md:mb-1 truncate">Total DPK (H-1)</p>
        <h3 id="kpi_dpk" class="text-lg md:text-xl xl:text-2xl font-black text-purple-700 tracking-tight whitespace-nowrap mb-1.5 md:mb-2.5">Rp 0</h3>
        <div id="kpi_dpk_pill"></div>
      </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-3 md:gap-4">
      <div class="bg-white p-4 md:p-5 rounded-xl md:rounded-2xl shadow-sm border border-gray-100 lg:col-span-2 flex flex-col">
        <div class="flex flex-col md:flex-row justify-between md:items-center gap-2 md:gap-0 mb-2 border-b border-gray-100 pb-3">
          <h3 class="font-bold text-gray-800 flex items-center gap-1.5 md:gap-2 text-[13px] md:text-base">
            <span class="text-blue-500">📈</span> Tren Portofolio Kredit
          </h3>
          <div class="flex flex-wrap md:flex-nowrap gap-1.5 md:gap-2 w-full md:w-auto">
            <select id="filter_tren_tipe" class="border border-gray-200 rounded-md px-2 py-1 text-[10px] md:text-xs font-semibold text-gray-600 outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer bg-white shadow-sm flex-1 md:flex-none">
                <option value="nom">Nominal (Rp)</option>
                <option value="pct">Persentase (%)</option>
                <option value="npl" selected>NPL</option>
            </select>
            <select id="filter_tren" class="border border-gray-200 rounded-md px-2 py-1 text-[10px] md:text-xs font-semibold text-gray-600 outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer bg-white shadow-sm flex-1 md:flex-none">
                <option value="tahunan">Periode Tahunan</option>
                <option value="bulanan" selected>Periode Bulanan</option>
                <option value="mingguan">Periode Mingguan</option>
                <option value="30_hari">30 Hari Terakhir</option>
                <option value="14_hari">14 Hari Terakhir</option>
                <option value="7_hari">7 Hari Terakhir</option>
            </select>
          </div>
        </div>
        <div class="relative flex-grow min-h-[220px] w-full mt-2">
          <div id="loadingChartTren" class="absolute inset-0 flex justify-center items-center bg-white bg-opacity-80 z-10 hidden">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
          </div>
          <canvas id="canvasTrenPortofolio"></canvas>
        </div>
      </div>

      <div class="bg-white p-4 md:p-5 rounded-xl md:rounded-2xl shadow-sm border border-gray-100 flex flex-col">
        <div class="flex items-center justify-between mb-3 border-b border-gray-100 pb-2">
          <h3 class="font-bold text-gray-800 flex items-center gap-1.5 md:gap-2 text-[13px] md:text-sm">
            <span class="text-indigo-500">📦</span> Realisasi by Produk
          </h3>
          <div class="text-right flex flex-col items-end">
            <span class="text-[8px] md:text-[9px] text-gray-400 font-bold uppercase tracking-wider">Total Realisasi</span>
            <span id="label_total_realisasi_produk" class="text-xs md:text-sm font-black text-indigo-600">Rp 0</span>
          </div>
        </div>
        <div id="box_realisasi_produk" class="space-y-3 flex-grow"></div>
      </div>
    </div>

    <div class="grid lg:grid-cols-12 gap-3 md:gap-4 mt-4 md:mt-6">
      
      <div class="bg-white p-4 md:p-5 rounded-xl md:rounded-3xl shadow-sm border border-gray-100 lg:col-span-3 flex flex-col">
        <h3 class="font-bold text-gray-800 mb-2.5 md:mb-3 border-b border-gray-100 pb-2 text-[11px] md:text-[12px] flex items-center gap-1.5 leading-tight shrink-0">
          <span>🔄</span> Realisasi vs Run Off
        </h3>
        <div id="box_runoff_realisasi" class="space-y-3 flex-grow mb-3"></div>
        
        <div class="mt-auto pt-2 md:pt-3 border-t border-gray-50 flex items-center justify-center gap-3 md:gap-4 text-[9px] md:text-[10px] font-bold text-gray-500 shrink-0">
            <div class="flex items-center gap-1 md:gap-1.5">
                <span class="w-2.5 h-1.5 md:w-3 md:h-1.5 rounded-full bg-green-400"></span> Realisasi
            </div>
            <div class="flex items-center gap-1 md:gap-1.5">
                <span class="w-2.5 h-1.5 md:w-3 md:h-1.5 rounded-full bg-red-400"></span> Run Off
            </div>
        </div>
      </div>

      <div class="bg-white p-4 md:p-5 rounded-xl md:rounded-3xl shadow-sm border border-gray-100 lg:col-span-3 flex flex-col">
        <h3 class="font-bold text-gray-800 mb-2.5 md:mb-3 border-b border-gray-100 pb-2 text-[11px] md:text-[12px] flex items-center gap-1.5 leading-tight shrink-0">
          <span>🛡️</span> Flow NPL vs Recovery
        </h3>
        <div id="box_flow_recovery" class="space-y-3 flex-grow mb-3"></div>
        
        <div class="mt-auto pt-2 md:pt-3 border-t border-gray-50 flex items-center justify-center gap-3 md:gap-4 text-[9px] md:text-[10px] font-bold text-gray-500 shrink-0">
            <div class="flex items-center gap-1 md:gap-1.5">
                <span class="w-2.5 h-1.5 md:w-3 md:h-1.5 rounded-full bg-red-400"></span> Flow NPL
            </div>
            <div class="flex items-center gap-1 md:gap-1.5">
                <span class="w-2.5 h-1.5 md:w-3 md:h-1.5 rounded-full bg-green-400"></span> Recovery
            </div>
        </div>
      </div>

      <div class="bg-white p-4 md:p-6 rounded-xl md:rounded-3xl shadow-sm border border-gray-100 lg:col-span-6 relative flex flex-col">
        <div id="loadingChartRunoff" class="absolute inset-0 flex justify-center items-center bg-white bg-opacity-90 z-10 hidden rounded-xl md:rounded-3xl">
           <div class="animate-spin rounded-full h-6 w-6 md:h-8 md:w-8 border-b-2 border-blue-500"></div>
        </div>
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-3 md:mb-4 border-b border-gray-100 pb-2 md:pb-3 shrink-0 gap-2 md:gap-0">
          <div>
            <h3 class="font-bold text-gray-800 flex items-center gap-1.5 md:gap-2 text-[13px] md:text-lg">
              <span class="text-blue-500">📊</span> Tren Realisasi vs Run Off
            </h3>
            <span class="text-[9px] md:text-xs text-gray-400 font-medium" id="label_runoff_date">Berdasarkan Tanggal: -</span>
          </div>
          
          <select id="filter_tren_runoff" class="border border-gray-200 rounded-md px-2 md:px-3 py-1 md:py-1.5 text-[10px] md:text-sm font-semibold text-gray-600 outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer bg-white shadow-sm w-full md:w-auto">
              <option value="tahunan">Periode Tahunan</option>
              <option value="bulanan" selected>Periode Bulanan</option>
              <option value="mingguan">Periode Mingguan</option>
              <option value="30_hari">30 Hari Terakhir</option>
              <option value="14_hari">14 Hari Terakhir</option>
              <option value="7_hari">7 Hari Terakhir</option>
          </select>
        </div>
        <div class="relative w-full flex-grow min-h-[220px] md:min-h-[250px]">
          <canvas id="canvasTrenRunoff"></canvas>
        </div>
      </div>

    </div>

        <div class="bg-white p-4 md:p-6 rounded-xl md:rounded-3xl shadow-sm border border-gray-100 mt-6 md:mt-8">
      <div class="flex items-center gap-2.5 md:gap-3 mb-4 md:mb-6 border-b border-gray-100 pb-3 md:pb-4">
        <div class="bg-red-100 p-1.5 md:p-2 rounded-lg"><span class="text-xl md:text-3xl">🚨</span></div>
        <div>
          <h2 class="text-lg md:text-2xl font-extrabold text-gray-900 tracking-tight">Kredit Non Perform</h2>
          <p class="text-[10px] md:text-sm text-gray-500 font-medium">Peringatan Kinerja & Cabang Terburuk</p>
        </div>
      </div>
      <div class="grid md:grid-cols-3 gap-4 md:gap-6">
        <div><h3 class="font-bold text-gray-800 mb-2.5 md:mb-3 text-[11px] md:text-[13px] flex items-center gap-1.5 md:gap-2"><span class="text-red-500">🚨</span> Top NPL Terburuk (Highest)</h3><div id="list_npl_top" class="space-y-3"></div></div>
        <div><h3 class="font-bold text-gray-800 mb-2.5 md:mb-3 text-[11px] md:text-[13px] flex items-center gap-1.5 md:gap-2"><span class="text-orange-500">⚠️</span> NPL Memburuk (Naik)</h3><div id="list_npl_naik" class="space-y-3"></div></div>
        <div><h3 class="font-bold text-gray-800 mb-2.5 md:mb-3 text-[11px] md:text-[13px] flex items-center gap-1.5 md:gap-2"><span class="text-gray-500">📉</span> Bottom Realisasi Cabang</h3><div id="list_realisasi_bottom" class="space-y-3"></div></div>
      </div>
    </div>

    <div class="bg-white p-4 md:p-6 rounded-xl md:rounded-3xl shadow-sm border border-gray-100 mt-6 md:mt-10">
      <div class="flex items-center gap-2.5 md:gap-3 mb-4 md:mb-6 border-b border-gray-100 pb-3 md:pb-4">
        <div class="bg-yellow-100 p-1.5 md:p-2 rounded-lg"><span class="text-xl md:text-3xl">🏆</span></div>
        <div>
          <h2 class="text-lg md:text-2xl font-extrabold text-gray-900 tracking-tight">5 Best Performance</h2>
          <p class="text-[10px] md:text-sm text-gray-500 font-medium">Jajaran Cabang dan Pegawai Terbaik</p>
        </div>
      </div>
      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5">
        <div class="space-y-4 md:space-y-5">
          <div><h3 class="font-bold text-gray-800 mb-2.5 md:mb-3 text-[11px] md:text-[13px] flex items-center gap-1.5 md:gap-2"><span class="text-blue-500">📈</span> Top Realisasi Cabang</h3><div id="best_realisasi" class="space-y-3"></div></div>
          <div class="pt-3 md:pt-4 border-t border-dashed border-gray-200"><h3 class="font-bold text-gray-800 mb-2.5 md:mb-3 text-[11px] md:text-[13px] flex items-center gap-1.5 md:gap-2"><span class="text-orange-500">🥇</span> Top Realisasi AO</h3><div id="best_realisasi_ao" class="space-y-3"></div></div>
        </div>
        <div class="space-y-4 md:space-y-5">
          <div><h3 class="font-bold text-gray-800 mb-2.5 md:mb-3 text-[11px] md:text-[13px] flex items-center gap-1.5 md:gap-2"><span class="text-red-500">🛡️</span> Top NPL Terendah (Terbaik)</h3><div id="best_npl" class="space-y-3"></div></div>
          <div class="pt-3 md:pt-4 border-t border-dashed border-gray-200"><h3 class="font-bold text-gray-800 mb-2.5 md:mb-3 text-[11px] md:text-[13px] flex items-center gap-1.5 md:gap-2"><span class="text-yellow-500">🏆</span> Top Repayment Rate (RR)</h3><div id="best_rr" class="space-y-3"></div></div>
        </div>
        <div class="space-y-4 md:space-y-5">
            <h3 class="font-bold text-gray-800 mb-2.5 md:mb-3 text-[11px] md:text-[13px] flex items-center gap-1.5 md:gap-2"><span class="text-teal-500">🎉</span> NPL Membaik (Penurunan)</h3>
            <div id="best_npl_turun" class="space-y-3"></div>
        </div>
        <div class="bg-[#1e293b] p-4 md:p-5 rounded-xl md:rounded-2xl shadow-md h-fit border border-gray-700">
           <h3 class="font-bold text-yellow-300 mb-3 md:mb-4 text-sm md:text-lg border-b border-gray-600 pb-2 md:pb-3 flex items-center gap-1.5 md:gap-2"><span class="text-lg md:text-2xl">💡</span> Key Insights</h3>
           <div id="dynamic_insights" class="space-y-3 md:space-y-4 text-[11px] md:text-sm text-gray-300 font-medium"></div>
        </div>
      </div>
    </div>



    <div class="bg-white p-4 md:p-6 rounded-xl md:rounded-3xl shadow-sm border border-gray-100 mt-6 md:mt-8">
      <div class="flex items-center gap-2.5 md:gap-3 mb-4 md:mb-6 border-b border-gray-100 pb-3 md:pb-4">
        <div class="bg-purple-100 p-1.5 md:p-2 rounded-lg"><span class="text-xl md:text-3xl">💰</span></div>
        <div>
          <h2 class="text-lg md:text-2xl font-extrabold text-gray-900 tracking-tight">Dana Pihak Ketiga (DPK)</h2>
          <p class="text-[10px] md:text-sm text-gray-500 font-medium">Rekapitulasi Deposito & Tabungan (H-1)</p>
        </div>
      </div>
      <div class="space-y-6 md:space-y-8">
        <div>
          <h3 class="font-extrabold text-gray-800 mb-3 md:mb-4 tracking-tight flex items-center gap-1.5 md:gap-2 text-[13px] md:text-lg"><span>🏦</span> Deposito (H-1)</h3>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
            <div><h3 class="font-bold text-gray-700 mb-2 md:mb-3 text-[9px] md:text-xs uppercase tracking-wider">Top Saldo Deposito</h3><div id="list_dep_saldo_top" class="space-y-2 md:space-y-2.5"></div></div>
            <div><h3 class="font-bold text-gray-700 mb-2 md:mb-3 text-[9px] md:text-xs uppercase tracking-wider">Bottom Saldo Deposito</h3><div id="list_dep_saldo_bot" class="space-y-2 md:space-y-2.5"></div></div>
            <div><h3 class="font-bold text-green-700 mb-2 md:mb-3 text-[9px] md:text-xs uppercase tracking-wider">Deposito Baru Masuk</h3><div id="list_dep_baru" class="space-y-2 md:space-y-2.5"></div></div>
            <div><h3 class="font-bold text-red-700 mb-2 md:mb-3 text-[9px] md:text-xs uppercase tracking-wider">Deposito Keluar</h3><div id="list_dep_cair" class="space-y-2 md:space-y-2.5"></div></div>
          </div>
        </div>
        <div class="border-t border-dashed border-gray-200"></div>
        <div>
          <h3 class="font-extrabold text-gray-800 mb-3 md:mb-4 tracking-tight flex items-center gap-1.5 md:gap-2 text-[13px] md:text-lg"><span>💳</span> Tabungan (H-1)</h3>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
            <div><h3 class="font-bold text-gray-700 mb-2 md:mb-3 text-[9px] md:text-xs uppercase tracking-wider">Top Saldo Tabungan</h3><div id="list_tab_saldo_top" class="space-y-2 md:space-y-2.5"></div></div>
            <div><h3 class="font-bold text-gray-700 mb-2 md:mb-3 text-[9px] md:text-xs uppercase tracking-wider">Bottom Saldo Tabungan</h3><div id="list_tab_saldo_bot" class="space-y-2 md:space-y-2.5"></div></div>
            <div><h3 class="font-bold text-blue-700 mb-2 md:mb-3 text-[9px] md:text-xs uppercase tracking-wider">Tabungan Baru Masuk</h3><div id="list_tab_baru" class="space-y-2 md:space-y-2.5"></div></div>
            <div><h3 class="font-bold text-red-700 mb-2 md:mb-3 text-[9px] md:text-xs uppercase tracking-wider">Tabungan Keluar</h3><div id="list_tab_cair" class="space-y-2 md:space-y-2.5"></div></div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<style>
  .bar-fill { transition: height 1s cubic-bezier(0.4, 0, 0.2, 1), width 1s ease-in-out; }
  .custom-scrollbar::-webkit-scrollbar { width: 4px; }
  .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
  .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<script>
  // ==========================================
  // 1. FORMATTER HELPERS
  // ==========================================
  const nf = new Intl.NumberFormat('id-ID');
  const fmt = n => nf.format(Number(n || 0));
  
  const fmtB = n => {
    let num = Number(n||0); let absNum = Math.abs(num);
    if(absNum >= 1e12) return (num/1e12).toFixed(3) + ' T'; 
    if(absNum >= 1e9) return (num/1e9).toFixed(2) + ' M';   
    if(absNum >= 1e6) return (num/1e6).toFixed(1) + ' Jt';  
    return fmt(num);
  };
  
  const pct = x => (x == null ? '0%' : `${(+x).toFixed(2)}%`);
  
  const getDeltaHTML = (val, isPercent = false, invertGoodBad = false, tight = false) => {
    let numVal = Number(val || 0);
    let sizeClass = tight ? 'text-[9px] md:text-[11px]' : 'text-xs md:text-sm';
    if(numVal === 0) return `<span class="text-gray-400 font-bold ${sizeClass}">Tetap 0</span>`;
    
    let isGood = invertGoodBad ? numVal < 0 : numVal > 0;
    let color = isGood ? 'text-green-600' : 'text-red-600';
    let icon = numVal > 0 ? '▲' : '▼';
    let displayVal = isPercent ? pct(Math.abs(numVal)) : fmtB(Math.abs(numVal));
    return `<span class="${color} font-black ${sizeClass}">${icon} ${displayVal}</span>`;
  };

  function getTodayRealtime() {
    let d = new Date();
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
  }
  
  function getYesterdayRealtime() {
    let d = new Date();
    d.setDate(d.getDate() - 1);
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
  }

  let chartTrenInstance = null;
  let chartRunoffInstance = null; 
  let initialHarianDate = null; 
  let trenPortoDataGlobal = [];

  const apiCall = (url, opt={}) => (window.apiFetch ? window.apiFetch(url, opt) : fetch(url, opt));

  // ==========================================
  // 2. INIT & DATA FETCHING
  // ==========================================
  async function getLastHarianData() {
    try { const r = await apiCall('./api/date/'); const j = await r.json(); return j.data || null; } catch { return null; }
  }

  async function populateKantorOptions(userKode) {
    const optKantor = document.getElementById('filter_kantor');
    try {
      if(userKode && userKode !== '000'){
        optKantor.innerHTML = `<option value="${userKode}">${userKode}</option>`; optKantor.value = userKode; optKantor.disabled = true; return;
      }
      const res = await apiCall('./api/kode/', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({type:'kode_kantor'}) });
      const j = await res.json();
      let html = `<option value="000">Konsolidasi</option><option value="SEMARANG">Korwil Semarang</option><option value="SOLO">Korwil Solo</option><option value="BANYUMAS">Korwil Banyumas</option><option value="PEKALONGAN">Korwil Pekalongan</option>`;
      if(j.data) j.data.filter(x => x.kode_kantor !== '000').forEach(k => html += `<option value="${k.kode_kantor}">${k.kode_kantor} - ${k.nama_kantor || k.nama_cabang || ''}</option>`);
      optKantor.innerHTML = html; optKantor.disabled = false;
    } catch(e) {
        optKantor.innerHTML=`<option value="000">Konsolidasi (Semua Cabang)</option>`; optKantor.disabled = false;
    }
  }

  window.addEventListener('DOMContentLoaded', async () => {
    const user = (window.getUser && window.getUser()) || null;
    const uKode = (user?.kode ? String(user.kode).padStart(3,'0') : null);
    await populateKantorOptions(uKode);

    const d = await getLastHarianData(); 
    if(d) {
      document.getElementById('filter_closing').value = d.last_closing;
      document.getElementById('filter_harian').value  = d.last_created;
    } else {
      document.getElementById('filter_closing').value = '2026-02-28';
      document.getElementById('filter_harian').value = '2026-03-28';
    }

    initialHarianDate = document.getElementById('filter_harian').value;

    fetchDashboardUtama();
    Promise.all([
        fetchTrenPortofolio(),
        fetchTrenRunoff()
    ]);
  });

  document.getElementById('formFilterMaster').addEventListener('submit', e => {
    e.preventDefault();
    fetchDashboardUtama();
    Promise.all([
        fetchTrenPortofolio(),
        fetchTrenRunoff()
    ]);
  });

  document.getElementById('filter_tren').addEventListener('change', () => { fetchTrenPortofolio(); });
  document.getElementById('filter_tren_tipe').addEventListener('change', () => { renderChartPortofolio(); });
  document.getElementById('filter_tren_runoff').addEventListener('change', () => { fetchTrenRunoff(); });


  // ==========================================
  // 3. FETCH & RENDER TREN PORTOFOLIO KREDIT
  // ==========================================
  async function fetchTrenPortofolio() {
    const loadingChart = document.getElementById('loadingChartTren'); 
    loadingChart.classList.remove('hidden');
    let kantor = document.getElementById('filter_kantor').value;
    
    const payload = { 
        type: 'tren_portofolio_kredit', 
        harian_date: document.getElementById('filter_harian').value, 
        periode: document.getElementById('filter_tren').value 
    };
    
    if(kantor !== '000') { 
        if(['SEMARANG','SOLO','BANYUMAS','PEKALONGAN'].includes(kantor)) payload.korwil = kantor; 
        else payload.kode_kantor = kantor; 
    } else {
        payload.kode_kantor = "000";
    }

    try {
      const res = await apiCall('./api/dashboard/', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
      const json = await res.json(); 
      trenPortoDataGlobal = Array.isArray(json.data) ? json.data : (json.data?.tren_portofolio || []);
      renderChartPortofolio();
    } catch(e) {
      trenPortoDataGlobal = [];
      renderChartPortofolio();
    } finally { 
      loadingChart.classList.add('hidden'); 
    }
  }

  function renderChartPortofolio() {
    const canvas = document.getElementById('canvasTrenPortofolio'); 
    const ctx = canvas.getContext('2d');
    if(chartTrenInstance) chartTrenInstance.destroy();
    
    if(!trenPortoDataGlobal || trenPortoDataGlobal.length === 0) {
      ctx.clearRect(0, 0, canvas.width, canvas.height); 
      ctx.font = "14px Arial"; ctx.fillStyle = "#9ca3af"; ctx.textAlign = "center";
      ctx.fillText("Data tren tidak tersedia untuk periode ini", canvas.width/2, canvas.height/2); 
      return;
    }

    const tipe = document.getElementById('filter_tren_tipe').value;
    const labels = trenPortoDataGlobal.map(d => d.label || d.tanggal); 
    
    let datasets = [];
    let yAxisCallback;

    if (tipe === 'nom') {
        const dataTotal = trenPortoDataGlobal.map(d => Number(d.osc_total)); 
        const dataRR = trenPortoDataGlobal.map(d => Number(d.osc_rr)); 
        const dataNPL = trenPortoDataGlobal.map(d => Number(d.osc_npl)); 
        
        let gradTotal = ctx.createLinearGradient(0, 0, 0, 300); gradTotal.addColorStop(0, 'rgba(59, 130, 246, 0.1)'); gradTotal.addColorStop(1, 'rgba(59, 130, 246, 0.0)');
        let gradRR = ctx.createLinearGradient(0, 0, 0, 300); gradRR.addColorStop(0, 'rgba(16, 185, 129, 0.1)'); gradRR.addColorStop(1, 'rgba(16, 185, 129, 0.0)');
        let gradNPL = ctx.createLinearGradient(0, 0, 0, 300); gradNPL.addColorStop(0, 'rgba(239, 68, 68, 0.1)'); gradNPL.addColorStop(1, 'rgba(239, 68, 68, 0.0)');

        datasets = [
            { label: 'OSC Total', data: dataTotal, borderColor: '#3b82f6', backgroundColor: gradTotal, borderWidth: 3, pointBackgroundColor: '#ffffff', pointBorderColor: '#3b82f6', pointRadius: 3, pointHoverRadius: 5, fill: true, tension: 0.4 },
            { label: 'OSC RR', data: dataRR, borderColor: '#10b981', backgroundColor: gradRR, borderWidth: 3, pointBackgroundColor: '#ffffff', pointBorderColor: '#10b981', pointRadius: 3, pointHoverRadius: 5, fill: true, tension: 0.4 },
            { label: 'OSC NPL', data: dataNPL, borderColor: '#ef4444', backgroundColor: gradNPL, borderWidth: 3, pointBackgroundColor: '#ffffff', pointBorderColor: '#ef4444', pointRadius: 3, pointHoverRadius: 5, fill: true, tension: 0.4 }
        ];
        yAxisCallback = function(value) { return fmtB(value); };
    } else if (tipe === 'pct') {
        const dataRRPct = trenPortoDataGlobal.map(d => parseFloat(Number(d.rr_persen).toFixed(2))); 
        const dataNPLPct = trenPortoDataGlobal.map(d => parseFloat(Number(d.npl_persen).toFixed(2))); 
        
        let gradRR = ctx.createLinearGradient(0, 0, 0, 300); gradRR.addColorStop(0, 'rgba(16, 185, 129, 0.2)'); gradRR.addColorStop(1, 'rgba(16, 185, 129, 0.0)');
        let gradNPL = ctx.createLinearGradient(0, 0, 0, 300); gradNPL.addColorStop(0, 'rgba(239, 68, 68, 0.2)'); gradNPL.addColorStop(1, 'rgba(239, 68, 68, 0.0)');

        datasets = [
            { label: 'RR (%)', data: dataRRPct, borderColor: '#10b981', backgroundColor: gradRR, borderWidth: 3, pointBackgroundColor: '#ffffff', pointBorderColor: '#10b981', pointRadius: 3, pointHoverRadius: 5, fill: true, tension: 0.4 },
            { label: 'NPL (%)', data: dataNPLPct, borderColor: '#ef4444', backgroundColor: gradNPL, borderWidth: 3, pointBackgroundColor: '#ffffff', pointBorderColor: '#ef4444', pointRadius: 3, pointHoverRadius: 5, fill: true, tension: 0.4 }
        ];
        yAxisCallback = function(value) { return value + '%'; };
    } else if (tipe === 'npl') {
        const dataNPLPct = trenPortoDataGlobal.map(d => parseFloat(Number(d.npl_persen).toFixed(2))); 
        let gradNPL = ctx.createLinearGradient(0, 0, 0, 300); 
        gradNPL.addColorStop(0, 'rgba(239, 68, 68, 0.2)'); 
        gradNPL.addColorStop(1, 'rgba(239, 68, 68, 0.0)');

        datasets = [
            { label: 'NPL (%)', data: dataNPLPct, borderColor: '#ef4444', backgroundColor: gradNPL, borderWidth: 3, pointBackgroundColor: '#ffffff', pointBorderColor: '#ef4444', pointRadius: 4, pointHoverRadius: 6, fill: true, tension: 0.4 }
        ];
        yAxisCallback = function(value) { return value + '%'; };
    }

    const labelPlugin = {
        id: 'alwaysShowLabels',
        afterDatasetsDraw(chart) {
            const periode = document.getElementById('filter_tren').value;
            if (periode !== 'bulanan') return;

            const { ctx, data } = chart;
            ctx.save();
            ctx.font = window.innerWidth < 768 ? 'bold 9px sans-serif' : 'bold 11px sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';

            const numPoints = data.labels.length;
            const numDatasets = data.datasets.length;

            for (let i = 0; i < numPoints; i++) {
                let points = [];
                for (let j = 0; j < numDatasets; j++) {
                    const meta = chart.getDatasetMeta(j);
                    if (!meta.hidden && meta.data[i]) {
                        const val = data.datasets[j].data[i];
                        const text = (tipe === 'nom') ? fmtB(val) : val + '%';
                        const pos = meta.data[i].tooltipPosition();
                        points.push({ index: j, text: text, color: data.datasets[j].borderColor, x: pos.x, y: pos.y });
                    }
                }

                points.sort((a, b) => a.y !== b.y ? a.y - b.y : a.index - b.index);

                for (let k = 0; k < points.length; k++) {
                    let desiredY = points[k].y - 12; 
                    if (k > 0) {
                        if (Math.abs(desiredY - points[k-1].drawY) < 16) {
                            desiredY = points[k].y + 14; 
                            if (Math.abs(desiredY - points[k-1].drawY) < 16) {
                                desiredY = points[k-1].drawY + 16;
                            }
                        }
                    }
                    points[k].drawY = desiredY;
                    ctx.fillStyle = points[k].color; 
                    ctx.fillText(points[k].text, points[k].x, points[k].drawY);
                }
            }
            ctx.restore();
        }
    };

    chartTrenInstance = new Chart(ctx, {
      type: 'line',
      data: { labels: labels, datasets: datasets },
      options: { 
          layout: { padding: { top: 30, bottom: 10, left: window.innerWidth < 768 ? -5 : 10, right: 10 } },
          responsive: true, 
          maintainAspectRatio: false, 
          interaction: { mode: 'index', intersect: false },
          plugins: { 
              legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 10, font: {family: 'sans-serif', size: window.innerWidth < 768 ? 9 : 11, weight: 'bold'} } }, 
              tooltip: { 
                  backgroundColor: 'rgba(17, 24, 39, 0.95)', 
                  padding: 10,
                  usePointStyle: true,
                  callbacks: { 
                      labelColor: function(context) {
                          return { borderColor: context.dataset.borderColor, backgroundColor: context.dataset.borderColor };
                      },
                      label: function(c) { 
                          const raw = c.raw;
                          const dataObj = trenPortoDataGlobal[c.dataIndex];
                          let text = '';
                          
                          if (tipe === 'nom') {
                              text = `${c.dataset.label}: Rp ${fmtB(raw)}`;
                              if (c.datasetIndex === 0 && dataObj.gap_osc_total) {
                                  let isGood = dataObj.gap_osc_total > 0;
                                  text += ` (${isGood ? '🟢 ▲' : '🔴 ▼'} ${fmtB(Math.abs(dataObj.gap_osc_total))})`;
                              }
                              if (c.datasetIndex === 1 && dataObj.gap_osc_rr) {
                                  let isGood = dataObj.gap_osc_rr > 0;
                                  text += ` (${isGood ? '🟢 ▲' : '🔴 ▼'} ${fmtB(Math.abs(dataObj.gap_osc_rr))})`;
                              }
                              if (c.datasetIndex === 2 && dataObj.gap_osc_npl) {
                                  let isGood = dataObj.gap_osc_npl < 0; 
                                  text += ` (${isGood ? '🟢 ▼' : '🔴 ▲'} ${fmtB(Math.abs(dataObj.gap_osc_npl))})`;
                              }
                          } else if (tipe === 'pct') {
                              text = `${c.dataset.label}: ${Number(raw).toFixed(2)}%`;
                              if (c.datasetIndex === 0 && dataObj.gap_rr_persen) {
                                  let isGood = dataObj.gap_rr_persen > 0;
                                  text += ` (${isGood ? '🟢 ▲' : '🔴 ▼'} ${Math.abs(dataObj.gap_rr_persen)}%)`;
                              }
                              if (c.datasetIndex === 1 && dataObj.gap_npl_persen) {
                                  let isGood = dataObj.gap_npl_persen < 0; 
                                  text += ` (${isGood ? '🟢 ▼' : '🔴 ▲'} ${Math.abs(dataObj.gap_npl_persen)}%)`;
                              }
                          } else if (tipe === 'npl') {
                              text = `NPL: ${Number(raw).toFixed(2)}% (Rp ${fmtB(dataObj.osc_npl)})`;
                              if (dataObj.gap_npl_persen) {
                                  let isGood = dataObj.gap_npl_persen < 0; 
                                  text += ` (${isGood ? '🟢 ▼' : '🔴 ▲'} ${Math.abs(dataObj.gap_npl_persen)}%)`;
                              }
                          }
                          return text;
                      } 
                  } 
              } 
          }, 
          scales: { 
              x: { grid: { display: false }, ticks: {font: {size: window.innerWidth < 768 ? 8 : 10}} }, 
              y: { beginAtZero: false, grid: { borderDash: [4, 4], color: '#f3f4f6' }, ticks: { font: {size: window.innerWidth < 768 ? 8 : 10}, callback: yAxisCallback } } 
          } 
      },
      plugins: [labelPlugin] 
    });
  }

  // ==========================================
  // 4. FETCH & RENDER KHUSUS TREN RUN OFF
  // ==========================================
  async function fetchTrenRunoff(isRetry = false, targetDateOverride = null) {
    const loadingChart = document.getElementById('loadingChartRunoff');
    if (!isRetry) loadingChart.classList.remove('hidden');

    let kantor = document.getElementById('filter_kantor').value;
    let currFilterDate = document.getElementById('filter_harian').value;
    
    let isDefaultDate = (currFilterDate === initialHarianDate);
    let targetRealtimeDate = isDefaultDate ? getTodayRealtime() : currFilterDate;
    
    let baseDate = targetDateOverride || targetRealtimeDate;
    document.getElementById('label_runoff_date').innerText = `Berdasarkan Tanggal: ${baseDate}`;

    const payload = { 
      type: 'tren_runoff_realisasi', 
      harian_date: baseDate,
      periode: document.getElementById('filter_tren_runoff').value 
    };

    if(kantor !== '000') { 
        if(['SEMARANG','SOLO','BANYUMAS','PEKALONGAN'].includes(kantor)) payload.korwil = kantor; 
        else payload.kode_kantor = kantor; 
    }

    try {
      let res = await apiCall('./api/dashboard/', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
      let json = await res.json();
      
      let dataToRender = Array.isArray(json.data) ? json.data : (json.data?.tren_runoff_realisasi || []);
      let isAllZero = dataToRender.length > 0 && dataToRender.every(d => d.total_realisasi === 0 && d.total_runoff === 0);

      if ((dataToRender.length === 0 || isAllZero) && !isRetry && isDefaultDate) {
          return fetchTrenRunoff(true, getYesterdayRealtime()); 
      }

      renderChartRunoff(dataToRender);
    } catch(e) { 
      renderChartRunoff([]);
    } finally {
      if (!isRetry) loadingChart.classList.add('hidden');
    }
  }

  function renderChartRunoff(dataArray) {
    const canvas = document.getElementById('canvasTrenRunoff'); if(!canvas) return; const ctx = canvas.getContext('2d');
    if(chartRunoffInstance) chartRunoffInstance.destroy();
    
    if(!dataArray || dataArray.length === 0) {
      ctx.clearRect(0, 0, canvas.width, canvas.height); ctx.font = "14px Arial"; ctx.fillStyle = "#9ca3af"; ctx.textAlign = "center";
      ctx.fillText("Data tidak tersedia", canvas.width/2, canvas.height/2); return;
    }

    const labels = dataArray.map(d => d.label); 
    const dataRealisasi = dataArray.map(d => Number(d.total_realisasi) || 0); 
    const dataRunoff = dataArray.map(d => Number(d.total_runoff) || 0); 
    const dataLunas = dataArray.map(d => Number(d.total_lunas) || 0);
    const dataNoaLunas = dataArray.map(d => Number(d.noa_lunas) || 0);
    const dataAngsuran = dataArray.map(d => Number(d.total_angsuran) || 0);
    const dataNoaAngsuran = dataArray.map(d => Number(d.noa_angsuran) || 0);
    const dataGrowth = dataArray.map(d => Number(d.growth) || 0);

    let gradReal = ctx.createLinearGradient(0, 0, 0, 300); gradReal.addColorStop(0, 'rgba(16, 185, 129, 0.2)'); gradReal.addColorStop(1, 'rgba(16, 185, 129, 0.0)');
    let gradRunoff = ctx.createLinearGradient(0, 0, 0, 300); gradRunoff.addColorStop(0, 'rgba(239, 68, 68, 0.2)'); gradRunoff.addColorStop(1, 'rgba(239, 68, 68, 0.0)');
    
    const labelPluginRunoff = {
        id: 'alwaysShowLabelsRunoff',
        afterDatasetsDraw(chart) {
            const periode = document.getElementById('filter_tren_runoff').value;
            if (periode !== 'bulanan') return;

            const { ctx, data } = chart;
            ctx.save();
            ctx.font = window.innerWidth < 768 ? 'bold 9px sans-serif' : 'bold 11px sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';

            const numPoints = data.labels.length;
            const numDatasets = data.datasets.length;

            for (let i = 0; i < numPoints; i++) {
                let points = [];
                for (let j = 0; j < numDatasets; j++) {
                    const meta = chart.getDatasetMeta(j);
                    if (!meta.hidden && meta.data[i]) {
                        const val = data.datasets[j].data[i];
                        const text = fmtB(val);
                        const pos = meta.data[i].tooltipPosition();
                        points.push({ index: j, text: text, color: data.datasets[j].borderColor, x: pos.x, y: pos.y });
                    }
                }

                points.sort((a, b) => a.y !== b.y ? a.y - b.y : a.index - b.index);

                for (let k = 0; k < points.length; k++) {
                    let desiredY = points[k].y - 12; 
                    if (k > 0) {
                        if (Math.abs(desiredY - points[k-1].drawY) < 16) {
                            desiredY = points[k].y + 14; 
                            if (Math.abs(desiredY - points[k-1].drawY) < 16) {
                                desiredY = points[k-1].drawY + 16;
                            }
                        }
                    }
                    points[k].drawY = desiredY;
                    ctx.fillStyle = points[k].color; 
                    ctx.fillText(points[k].text, points[k].x, points[k].drawY);
                }
            }
            ctx.restore();
        }
    };

    chartRunoffInstance = new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [
          { label: 'Realisasi', data: dataRealisasi, borderColor: '#10b981', backgroundColor: gradReal, borderWidth: 3, pointBackgroundColor: '#ffffff', pointBorderColor: '#10b981', pointBorderWidth: 2, pointRadius: 3, pointHoverRadius: 5, fill: true, tension: 0.4 },
          { label: 'Run Off', data: dataRunoff, borderColor: '#ef4444', backgroundColor: gradRunoff, borderWidth: 3, pointBackgroundColor: '#ffffff', pointBorderColor: '#ef4444', pointBorderWidth: 2, pointRadius: 3, pointHoverRadius: 5, fill: true, tension: 0.4 }
        ]
      },
      options: {
        layout: { padding: { top: 30, bottom: 15, left: window.innerWidth < 768 ? -5 : 10, right: 10 } },
        responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 10, font: {family: 'sans-serif', size: window.innerWidth < 768 ? 9 : 12, weight: 'bold'} } },
          tooltip: {
            backgroundColor: 'rgba(17, 24, 39, 0.95)', padding: 12, titleFont: { size: 13, family: 'sans-serif' }, bodyFont: { size: 12, family: 'sans-serif' },
            usePointStyle: true,
            callbacks: {
              labelColor: function(context) {
                  return { borderColor: context.dataset.borderColor, backgroundColor: context.dataset.borderColor };
              },
              label: function(c) { return `${c.dataset.label}: Rp ${fmtB(c.raw)}`; },
              afterBody: function(c) {
                if (c.length > 0) { 
                  let idx = c[0].dataIndex;
                  let lunas = dataLunas[idx]; let noaLunas = dataNoaLunas[idx];
                  let angsuran = dataAngsuran[idx]; let noaAngsuran = dataNoaAngsuran[idx];
                  let g = dataGrowth[idx]; 
                  let lines = [];
                  lines.push('------------------------');
                  lines.push(`Detail Run Off:`);
                  lines.push(`  • Lunas: Rp ${fmtB(lunas)} (${fmt(noaLunas)} NOA)`);
                  lines.push(`  • Angsuran: Rp ${fmtB(angsuran)} (${fmt(noaAngsuran)} NOA)`);
                  lines.push('');
                  let isGood = g >= 0;
                  lines.push(`Growth: ${isGood ? '🟢 ▲' : '🔴 ▼'} Rp ${fmtB(Math.abs(g))}`);
                  return lines;
                }
              }
            }
          }
        },
        scales: { 
            x: { grid: { display: false }, ticks: {font: {size: window.innerWidth < 768 ? 8 : 10}} }, 
            y: { grid: { borderDash: [4,4], color: '#f3f4f6' }, ticks: { font: {size: window.innerWidth < 768 ? 8 : 10}, callback: function(val) { return fmtB(val); } } } 
        }
      },
      plugins: [labelPluginRunoff]
    });
  }

  // ==========================================
  // Helper Mundur 1 Hari
  // ==========================================
  function getH1Date(dateString) {
    let d = new Date(dateString);
    d.setDate(d.getDate() - 1);
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
  }

  // ==========================================
  // 5. FETCH API MODULAR (WIDGET-BASED / PARALEL)
  // ==========================================
  async function fetchWidgetData(type, isH1 = false) {
    let kantor = document.getElementById('filter_kantor').value;
    let currDate = document.getElementById('filter_harian').value;
    
    // 🔥 TRIK SAKTI H-1 Pindah ke sini
    if (isH1) {
        currDate = getH1Date(currDate);
    }

    let targetRealisasiDate = (currDate === initialHarianDate) ? getTodayRealtime() : currDate;

    const payload = { 
      type: type, 
      closing_date: document.getElementById('filter_closing').value, 
      harian_date: currDate,
      harian_date_realisasi: targetRealisasiDate
    };
    
    if(kantor !== '000') { 
        if(['SEMARANG','SOLO','BANYUMAS','PEKALONGAN'].includes(kantor)) payload.korwil = kantor; 
        else payload.kode_kantor = kantor; 
    }

    try {
      const res = await apiCall('./api/dashboard/', { 
          method: 'POST', 
          headers: { 'Content-Type': 'application/json' }, 
          body: JSON.stringify(payload) 
      });
      const json = await res.json();
      return json.data || null;
    } catch(e) {
      console.error(`Gagal memuat widget ${type}:`, e);
      return null;
    }
  }

  function fetchDashboardUtama() {
    // 1. Langsung tampilkan kerangka dashboard, sembunyikan spinner raksasa
    document.getElementById('loadingDash').classList.add('hidden'); 
    document.getElementById('contentDash').classList.remove('hidden');

    let kantorMode = document.getElementById('filter_kantor').value;

    // 2. TEMBAK SEMUA REQUEST SECARA BERSAMAAN (PARALEL)
    const pSaldoBank    = fetchWidgetData('saldo_bank');
    const pRealProduk   = fetchWidgetData('realisasi_by_produk');
    const pTrenNpl      = fetchWidgetData('test tren npl');
    const pRrCabang     = fetchWidgetData('test rr cabang');
    const pRunoffKorwil = fetchWidgetData('test runoff korwil');
    const pFlowKorwil   = fetchWidgetData('test flow recovery npl');
    const pTopReal      = fetchWidgetData('test top realisasi');
    const pTopNpl       = fetchWidgetData('test top bottom npl');
    const pDeltaNpl     = fetchWidgetData('test delta npl');
    
    // 🔥 Panggil fungsi dengan parameter tambahan `true` khusus DPK agar dikurangi 1 hari
    const pDeposito     = fetchWidgetData('test perkembangan deposito');
    const pTabungan     = fetchWidgetData('test perkembangan tabungan', true);

    // =========================================================
    // 3. RENDER WIDGET BEGITU DATANYA SELESAI (TIDAK SALING TUNGGU)
    // =========================================================

    // WIDGET A: SALDO BANK
    pSaldoBank.then(sb => {
      if(!sb) return;
      document.getElementById('kpi_saldobank').textContent = `Rp ${fmtB(sb.actual)}`;
      document.getElementById('kpi_saldobank_pill').innerHTML = `
        <div class="flex items-center gap-1.5 md:gap-2">
            <div class="bg-gray-100 px-1.5 md:px-2 py-0.5 rounded font-bold text-[9px] md:text-[11px] text-gray-600 whitespace-nowrap">Closing: <span class="text-gray-900">Rp ${fmtB(sb.closing)}</span></div>
            <div class="whitespace-nowrap">${getDeltaHTML(sb.delta, false, false, true)}</div>
        </div>`;
    });

    // WIDGET B: REALISASI BY PRODUK
    pRealProduk.then(rpRaw => {
      if(!rpRaw) return;
      let rp = rpRaw?.realisasi_by_produk || rpRaw || {};
      let prods = rp.detail_produk || [];
      let grandTotal = rp.grand_total?.total_realisasi || 0;
      document.getElementById('label_total_realisasi_produk').textContent = `Rp ${fmtB(grandTotal)}`;
      renderUniversalList('box_realisasi_produk', prods, 'nama_produk', 'total_realisasi', 'noa_realisasi', 'bg-indigo-400', false, 'NOA');
    });

    // WIDGET C: KPI UTAMA (OS, NPL, RR)
    Promise.all([pTrenNpl, pRrCabang]).then(([tNplRaw, rrRaw]) => {
      let tNpl = Array.isArray(tNplRaw) ? tNplRaw : (tNplRaw?.tren_npl || tNplRaw?.tren_portofolio || []);
      let rrData = rrRaw?.repayment_rate || rrRaw || {};
      
      let osPrev = 0;
      if(tNpl && tNpl.length > 0) {
        const last = tNpl[tNpl.length - 1]; 
        const prev = tNpl.length > 1 ? tNpl[tNpl.length - 2] : last; 
        osPrev = prev.total_kredit || prev.osc_total || 0; 
        
        document.getElementById('kpi_npl').textContent = `Rp ${fmtB(last.npl_amt || last.osc_npl)}`;
        document.getElementById('kpi_npl_pill').innerHTML = `
            <div class="flex items-center gap-1 md:gap-2 mb-1.5">
                <div class="bg-gray-100 px-1.5 md:px-2 py-0.5 rounded font-bold text-[9px] md:text-[11px] text-gray-600 whitespace-nowrap">Closing: <span class="text-gray-900">${pct(prev.npl_persen)}</span></div>
                <div class="bg-red-50 text-red-700 border border-red-100 px-1.5 md:px-2 py-0.5 rounded font-bold text-[9px] md:text-[11px] whitespace-nowrap">Act: ${pct(last.npl_persen)}</div>
            </div>
            <div class="whitespace-nowrap">${getDeltaHTML(last.npl_persen - prev.npl_persen, true, true, true)}</div>`;
      }

      if(rrData && rrData.grand_total) {
        const rrG = rrData.grand_total;
        let osCurr = rrG.os_total || 0;
        
        document.getElementById('kpi_os').textContent = `Rp ${fmtB(osCurr)}`;
        document.getElementById('kpi_os_pill').innerHTML = `
          <div class="flex items-center gap-1.5 md:gap-2">
              <div class="bg-gray-100 px-1.5 md:px-2 py-0.5 rounded font-bold text-[9px] md:text-[11px] text-gray-600 whitespace-nowrap">Closing: <span class="text-gray-900">Rp ${fmtB(osPrev)}</span></div>
              <div class="whitespace-nowrap">${getDeltaHTML(osCurr - osPrev, false, false, true)}</div>
          </div>`;

        document.getElementById('kpi_rr').textContent = `Rp ${fmtB(rrG.os_lancar)}`;
        document.getElementById('kpi_rr_pill').innerHTML = `
          <div class="flex items-center gap-1 md:gap-2 mb-1.5">
              <div class="bg-gray-100 px-1.5 md:px-2 py-0.5 rounded font-bold text-[9px] md:text-[11px] text-gray-600 whitespace-nowrap">Closing: <span class="text-gray-900">${pct(rrG.rr_persen_prev)}</span></div>
              <div class="bg-green-50 text-green-700 border border-green-100 px-1.5 md:px-2 py-0.5 rounded font-bold text-[9px] md:text-[11px] whitespace-nowrap">Act: ${pct(rrG.rr_persen_curr)}</div>
          </div>
          <div class="whitespace-nowrap">${getDeltaHTML(rrG.delta_rr, true, false, true)}</div>`;
      }
    });

    // WIDGET D: GRAFIK KORWIL (Bar Horizontal)
    Promise.all([pRunoffKorwil, pFlowKorwil]).then(([roRaw, flowRaw]) => {
      let ro = roRaw?.runoff_vs_realisasi || roRaw || null;
      let flow = flowRaw?.flow_vs_recovery_npl || flowRaw || null;

      let hideGrandTotal = (kantorMode !== '000');
      let isKorwilFilter = ['SEMARANG','SOLO','BANYUMAS','PEKALONGAN'].includes(kantorMode);

      const boxRunoff = document.getElementById('box_runoff_realisasi');
      const boxFlow = document.getElementById('box_flow_recovery');

      if (isKorwilFilter) {
          boxRunoff.style.maxHeight = '200px'; boxRunoff.classList.add('overflow-y-auto', 'custom-scrollbar', 'pr-1');
          boxFlow.style.maxHeight = '200px'; boxFlow.classList.add('overflow-y-auto', 'custom-scrollbar', 'pr-1');
      } else {
          boxRunoff.style.maxHeight = 'none'; boxRunoff.classList.remove('overflow-y-auto', 'custom-scrollbar', 'pr-1');
          boxFlow.style.maxHeight = 'none'; boxFlow.classList.remove('overflow-y-auto', 'custom-scrollbar', 'pr-1');
      }
      
      if(ro && ro.detail_korwil) {
        let runoffData = [...ro.detail_korwil]; 
        if(ro.grand_total && !hideGrandTotal) runoffData.push(ro.grand_total);
        renderKorwilCompare('box_runoff_realisasi', runoffData, 'realisasi', 'total_runoff', 'bg-green-400', 'bg-red-400');
      }

      if(flow && flow.detail_korwil) {
        let flowData = [...flow.detail_korwil]; 
        if(flow.grand_total && !hideGrandTotal) flowData.push(flow.grand_total);
        renderKorwilCompare('box_flow_recovery', flowData, 'flow_npl', 'total_recovery', 'bg-red-400', 'bg-green-400');
      }
    });

    // WIDGET E: INSIGHTS & TOP/BOTTOM LIST
    Promise.all([pTopReal, pTopNpl, pDeltaNpl, pRrCabang]).then(([realRaw, nplRaw, deltaRaw, rrRaw]) => {
      let real = realRaw?.top_bottom_realisasi || realRaw || {};
      let npl = nplRaw?.top_bottom_npl || nplRaw || {};
      let delta = deltaRaw?.kenaikan_penurunan_npl || deltaRaw || {};
      let rr = rrRaw?.repayment_rate || rrRaw || {};

      if(real.top_cabang) {
        renderUniversalList('best_realisasi', real.top_cabang, 'nama_cabang', 'total_realisasi', 'noa_realisasi', 'bg-blue-500', false, 'NOA');
        let topAOCustom = (real.top_ao || []).map(ao => {
            let namaCustom = ao.nama_ao;
            if (ao.nama_cabang && ao.nama_cabang.toLowerCase() !== 'unknown') {
                let cabangShort = ao.nama_cabang.replace(/Kc\. /gi, '');
                namaCustom = `[${cabangShort}] - ${ao.nama_ao}`;
            }
            return { ...ao, nama_custom: namaCustom };
        });
        renderUniversalList('best_realisasi_ao', topAOCustom, 'nama_custom', 'total_realisasi', 'noa_realisasi', 'bg-indigo-500', false, 'NOA');
        renderUniversalList('list_realisasi_bottom', [...(real.bottom_cabang || [])].reverse(), 'nama_cabang', 'total_realisasi', 'noa_realisasi', 'bg-orange-400', false, 'NOA');
      }

      if(npl.bottom) {
        renderUniversalList('best_npl', npl.bottom, 'nama_cabang', 'npl_persen', 'npl_amt', 'bg-emerald-400', true, 'Rp');
        renderUniversalList('list_npl_top', npl.top, 'nama_cabang', 'npl_persen', 'npl_amt', 'bg-red-500', true, 'Rp');
      }

      if(rr.top_rr) {
        renderUniversalList('best_rr', rr.top_rr, 'nama_cabang', 'rr_persen_curr', 'os_total', 'bg-green-500', true, 'Rp');
      }

      if(delta.top_penurunan) {
        renderUniversalList('best_npl_turun', delta.top_penurunan, 'nama_cabang', 'delta_npl', 'npl_persen_curr', 'bg-teal-400', true, 'NPL Now');
        renderUniversalList('list_npl_naik', delta.top_kenaikan, 'nama_cabang', 'delta_npl', 'npl_persen_curr', 'bg-orange-500', true, 'NPL Now');
      }

      const tReal = real?.top_cabang?.[0]; 
      const tAo = real?.top_ao?.[0]; 
      const tRR = rr?.top_rr?.[0]; 
      const tNplBest = npl?.bottom?.[0]; 
      const tTurun = delta?.top_penurunan?.[0];
      
      let html = '';
      if(tReal) html += `<div class="mb-3 md:mb-4"><span class="text-blue-400 font-bold">1. Realisasi Tertinggi:</span> <span class="text-white block md:inline mt-0.5 md:mt-0">${tReal.nama_cabang.replace('Kc. ','')} (${fmtB(tReal.total_realisasi)})</span></div>`;
      if(tAo) html += `<div class="mb-3 md:mb-4"><span class="text-indigo-400 font-bold">2. AO Terbaik:</span> <span class="text-white block md:inline mt-0.5 md:mt-0">${tAo.nama_ao} (${fmtB(tAo.total_realisasi)})</span></div>`;
      if(tRR) html += `<div class="mb-3 md:mb-4"><span class="text-green-400 font-bold">3. RR Terbaik:</span> <span class="text-white block md:inline mt-0.5 md:mt-0">${tRR.nama_cabang.replace('Kc. ','')} (${pct(tRR.rr_persen_curr)})</span></div>`;
      if(tNplBest) html += `<div class="mb-3 md:mb-4"><span class="text-emerald-400 font-bold">4. NPL Terbaik:</span> <span class="text-white block md:inline mt-0.5 md:mt-0">${tNplBest.nama_cabang.replace('Kc. ','')} (${pct(tNplBest.npl_persen)})</span></div>`;
      if(tTurun) html += `<div class="mb-3 md:mb-4"><span class="text-teal-400 font-bold">5. Penurunan Terbesar:</span> <span class="text-white block md:inline mt-0.5 md:mt-0">${tTurun.nama_cabang.replace('Kc. ','')} (Δ ${pct(Math.abs(tTurun.delta_npl))})</span></div>`;
      document.getElementById('dynamic_insights').innerHTML = html;
    });

    // WIDGET F: DANA PIHAK KETIGA (DPK)
    // 🔥 PERBAIKAN EXTRA AMAN UNTUK DEPOSITO & TABUNGAN 🔥
    Promise.all([pDeposito, pTabungan]).then(([depRaw, tabRaw]) => {
      let dep = depRaw?.perkembangan_deposito || depRaw || {};
      let tab = tabRaw?.perkembangan_tabungan || tabRaw || {};

      const depG = dep.grand_total || {}; 
      const tabG = tab.grand_total || {};
      const dpkCurr = (depG.saldo_curr||0) + (tabG.saldo_curr||0); 
      const dpkPrev = (depG.saldo_prev||0) + (tabG.saldo_prev||0);
      
      document.getElementById('kpi_dpk').textContent = `Rp ${fmtB(dpkCurr)}`;
      document.getElementById('kpi_dpk_pill').innerHTML = `
        <div class="flex items-center gap-1.5 md:gap-2">
            <div class="bg-gray-100 px-1.5 md:px-2 py-0.5 rounded font-bold text-[9px] md:text-[11px] text-gray-600 whitespace-nowrap">Closing: <span class="text-gray-900">Rp ${fmtB(dpkPrev)}</span></div>
            <div class="whitespace-nowrap">${getDeltaHTML(dpkCurr - dpkPrev, false, false, true)}</div>
        </div>`;

      if(Object.keys(dep).length > 0) {
        renderUniversalList('list_dep_saldo_top', dep.top_saldo, 'nama_cabang', 'saldo_curr', 'noa_curr', 'bg-yellow-500', false, 'Rek');
        renderUniversalList('list_dep_saldo_bot', [...(dep.bottom_saldo || [])].reverse(), 'nama_cabang', 'saldo_curr', 'noa_curr', 'bg-yellow-400', false, 'Rek');
        renderUniversalList('list_dep_baru', dep.top_baru, 'nama_cabang', 'saldo_baru', 'noa_tambah', 'bg-emerald-500', false, 'Rek Baru');
        renderUniversalList('list_dep_cair', dep.top_pencairan, 'nama_cabang', 'saldo_cair', 'noa_kurang', 'bg-red-400', false, 'Rek Cair');
      }

      if(Object.keys(tab).length > 0) {
        renderUniversalList('list_tab_saldo_top', tab.top_saldo, 'nama_cabang', 'saldo_curr', 'noa_curr', 'bg-teal-500', false, 'Rek');
        renderUniversalList('list_tab_saldo_bot', [...(tab.bottom_saldo || [])].reverse(), 'nama_cabang', 'saldo_curr', 'noa_curr', 'bg-teal-400', false, 'Rek');
        renderUniversalList('list_tab_baru', tab.top_baru, 'nama_cabang', 'saldo_baru', 'noa_tambah', 'bg-blue-500', false, 'Rek Baru');
        renderUniversalList('list_tab_cair', tab.top_pencairan, 'nama_cabang', 'saldo_cair', 'noa_kurang', 'bg-red-400', false, 'Rek Cair');
      }
    });
  }

  // ==========================================
  // HELPER RENDERING
  // ==========================================
  function renderKorwilCompare(elId, dataArray, keyA, keyB, colorA, colorB) {
    const box = document.getElementById(elId); box.innerHTML = ''; if(!dataArray || !dataArray.length) return;
    let maxVal = Math.max(...dataArray.flatMap(o => [Number(o[keyA]), Number(o[keyB])])); if(maxVal === 0) maxVal = 1;
    dataArray.forEach(k => {
      let vA = Number(k[keyA]); let vB = Number(k[keyB]); let pctA = (vA / maxVal) * 100; let pctB = (vB / maxVal) * 100;
      let titleClass = k.nama_korwil.includes("KONSOLIDASI") ? "text-gray-900 font-black" : "text-gray-700 font-bold";
      box.innerHTML += `<div class="mb-2 md:mb-3"><div class="flex justify-between text-[10px] md:text-[11px] ${titleClass} mb-1"><span>${k.nama_korwil}</span></div><div class="flex flex-col gap-1 md:gap-0.5 relative"><div class="w-full bg-gray-100 h-1.5 md:h-2 rounded-r-full flex relative"><div class="${colorA} h-1.5 md:h-2 rounded-r-full bar-fill z-10" style="width: ${pctA}%"></div><span class="absolute right-0 -top-3.5 md:-top-4 text-[9px] md:text-[10px] text-gray-500 font-medium">${fmtB(vA)}</span></div><div class="w-full bg-gray-100 h-1.5 md:h-2 rounded-r-full flex relative"><div class="${colorB} h-1.5 md:h-2 rounded-r-full bar-fill z-10" style="width: ${pctB}%"></div><span class="absolute right-0 -bottom-3.5 md:-bottom-4 text-[9px] md:text-[10px] text-gray-500 font-medium">${fmtB(vB)}</span></div></div></div>`;
    });
  }

  function renderUniversalList(elId, dataArray, nameKey, valKey, subKey, colorClass, isPercent, subLabel = 'Rp') {
    const box = document.getElementById(elId); box.innerHTML = '';
    if(!dataArray || !Array.isArray(dataArray) || dataArray.length === 0) { box.innerHTML = `<p class="text-[10px] md:text-[11px] text-gray-400 italic py-2 text-center">Tidak ada data.</p>`; return; }
    let maxVal = Math.max(...dataArray.map(o => Math.abs(Number(o[valKey]) || 0))); if(maxVal === 0) maxVal = 1;
    dataArray.forEach(item => {
      let val = Number(item[valKey] || 0); let sub = Number(item[subKey] || 0); let wPct = Math.abs((val / maxVal) * 100);
      let displayVal = isPercent ? pct(Math.abs(val)) : fmtB(Math.abs(val));
      let displaySub = subLabel === 'Rp' ? `Rp ${fmtB(sub)}` : (subLabel === 'NPL Now' ? `NPL saat ini: ${pct(sub)}` : `${fmt(sub)} ${subLabel}`);
      let name = (item[nameKey] || '-').replace(/Kc\. /gi, '');
      box.innerHTML += `<div class="mb-2.5 md:mb-3 group cursor-default relative z-0"><div class="flex justify-between items-end mb-1 md:mb-1.5 relative z-10"><div class="flex flex-col w-2/3"><span class="text-[11px] md:text-xs font-bold text-gray-800 truncate" title="${name}">${name}</span><span class="text-[9px] md:text-[10px] text-gray-500 font-medium leading-tight">${displaySub}</span></div><span class="text-[11px] md:text-xs font-black text-gray-900">${val < 0 ? '-' : ''}${displayVal}</span></div><div class="w-full bg-gray-100 h-1.5 md:h-2 rounded-full overflow-hidden relative z-0"><div class="${colorClass} h-1.5 md:h-2 rounded-full bar-fill" style="width: ${Math.max(2, wPct)}%"></div></div></div>`;
    });
  }
</script>