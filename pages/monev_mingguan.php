<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="max-w-[1600px] mx-auto px-4 py-6 bg-gray-50 min-h-screen font-sans">
  
  <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-6 gap-4">
    <div>
      <h1 class="text-2xl md:text-3xl font-extrabold text-gray-800 tracking-tight flex items-center gap-2">
        <span>📋</span> Monev Realisasi Mingguan
      </h1>
      <p class="text-sm text-gray-500 mt-1 font-medium ml-9">Monitoring & Evaluasi Komitmen Cabang</p>
    </div>

    <form id="formFilterMaster" class="bg-white p-2.5 rounded-2xl shadow-sm border border-gray-200 flex flex-wrap items-center gap-2 w-full xl:w-auto">
      <div class="flex items-center gap-3 px-2 border-r border-gray-100">
        <div class="flex flex-col">
          <label class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Tahun</label>
          <select id="filter_tahun" class="border border-gray-200 rounded px-2 py-1 text-sm font-bold outline-none focus:border-blue-500 cursor-pointer">
              <option value="2026">2026</option>
              <option value="2025">2025</option>
          </select>
        </div>
        <div class="flex flex-col">
          <label class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Bulan</label>
          <select id="filter_bulan" class="border border-gray-200 rounded px-2 py-1 text-sm font-bold outline-none focus:border-blue-500 cursor-pointer">
              <option value="01">Januari</option>
              <option value="02">Februari</option>
              <option value="03" selected>Maret</option>
              <option value="04">April</option>
          </select>
        </div>
      </div>
      
      <div class="flex flex-col px-2 border-r border-gray-100">
        <label class="text-[9px] font-bold text-blue-600 uppercase tracking-wider mb-0.5">Minggu Aktif</label>
        <select id="filter_minggu" class="border border-blue-200 bg-blue-50 text-blue-700 rounded px-2 py-1 text-sm font-bold outline-none focus:ring-1 focus:ring-blue-500 cursor-pointer">
            <option value="1">Minggu 1</option>
            <option value="2" selected>Minggu 2</option>
            <option value="3">Minggu 3</option>
            <option value="4">Minggu 4</option>
        </select>
      </div>

      <div class="flex flex-col px-2 flex-grow min-w-[250px]">
        <label class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Area/Cabang</label>
        <select id="filter_kantor" class="border border-gray-200 rounded px-2 py-1 text-sm font-bold text-gray-700 outline-none focus:border-blue-500 cursor-pointer w-full">
            <option value="001">[Semarang] 001 - Kc. Utama</option>
            <option value="015" selected>[Banyumas] 015 - Kc. Wonosobo</option>
            <option value="022">[Pekalongan] 022 - Kc. Tegal</option>
        </select>
      </div>

      <div class="px-1">
        <button type="submit" class="bg-[#2563eb] hover:bg-blue-700 text-white px-6 py-2 rounded-xl text-sm font-bold transition-all shadow-md active:scale-95">
            Tampilkan
        </button>
      </div>
    </form>
  </div>

  <div id="contentDash" class="space-y-6 overflow-x-hidden">
    
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div>
            <h2 class="text-xl font-black text-[#1e293b] tracking-tight uppercase" id="title_monev_detail">MONEV MARET 2026</h2>
            <div class="grid grid-cols-[100px_10px_1fr] text-[13px] text-gray-500 font-medium mt-3 gap-y-1.5">
                <span>Kanwil</span><span>:</span><span class="font-bold text-gray-800" id="lbl_kanwil">Banyumas</span>
                <span>Kantor</span><span>:</span><span class="font-bold text-gray-800" id="lbl_kantor">Kc. Wonosobo</span>
                <span>Kode Kantor</span><span>:</span><span class="font-bold text-gray-800" id="lbl_kode">015</span>
                <span class="mt-1">Minggu Aktif</span><span class="mt-1">:</span>
                <span class="mt-1 font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-100 w-fit" id="lbl_minggu">Minggu 2</span>
            </div>
        </div>
        
        <div class="flex flex-col items-end">
            <div class="text-[11px] text-gray-400 mb-3 text-right font-medium">
                <p>Update terakhir: <span class="text-gray-600" id="lbl_last_update">-</span></p>
                <p class="mt-1">Status Input: <span class="bg-yellow-50 text-yellow-600 font-bold px-2 py-0.5 rounded border border-yellow-200">Draft</span></p>
            </div>
            <div class="flex gap-2">
                <button type="button" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg text-[13px] font-bold transition-all shadow-sm">
                    Simpan Draft
                </button>
                <button type="button" class="bg-[#2563eb] hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-[13px] font-bold shadow-md transition-all">
                    Submit Final
                </button>
                <button type="button" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-3 py-2 rounded-lg text-[13px] font-bold transition-all shadow-sm flex items-center gap-1.5">
                    🖨️ Cetak
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-[#3b82f6] text-white p-4 rounded-2xl shadow-sm relative overflow-hidden">
            <h3 class="text-2xl font-black tracking-tight" id="kpi_komitmen">0</h3>
            <p class="text-[11px] font-bold opacity-90 mt-1 uppercase tracking-wider">Komitmen Minggu Ini</p>
        </div>
        <div class="bg-[#38bdf8] text-white p-4 rounded-2xl shadow-sm relative overflow-hidden">
            <h3 class="text-2xl font-black tracking-tight" id="kpi_realisasi_mg">0</h3>
            <p class="text-[11px] font-bold opacity-90 mt-1 uppercase tracking-wider">Realisasi Minggu Ini</p>
        </div>
        <div class="bg-[#10b981] text-white p-4 rounded-2xl shadow-sm relative overflow-hidden">
            <h3 class="text-2xl font-black tracking-tight" id="kpi_gap">0</h3>
            <p class="text-[11px] font-bold opacity-90 mt-1 uppercase tracking-wider">Gap Minggu Ini</p>
        </div>
        <div class="bg-[#047857] text-white p-4 rounded-2xl shadow-sm relative overflow-hidden">
            <h3 class="text-2xl font-black tracking-tight" id="kpi_realisasi_bln">0</h3>
            <p class="text-[11px] font-bold opacity-90 mt-1 uppercase tracking-wider">Realisasi Bulan</p>
        </div>
        <div class="bg-[#1e293b] text-white p-4 rounded-2xl shadow-sm relative overflow-hidden">
            <h3 class="text-2xl font-black text-yellow-400 tracking-tight" id="kpi_rbb">0,00%</h3>
            <p class="text-[11px] font-bold opacity-90 mt-1 uppercase tracking-wider">% Capaian RBB</p>
        </div>
        <div class="bg-[#ef4444] text-white p-4 rounded-2xl shadow-sm relative overflow-hidden">
            <h3 class="text-2xl font-black tracking-tight" id="kpi_proyeksi_npl">0,00%</h3>
            <p class="text-[11px] font-bold opacity-90 mt-1 uppercase tracking-wider">Proyeksi NPL</p>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mt-6">
        
        <div class="flex border-b border-gray-100 px-2 bg-white overflow-x-auto custom-scrollbar">
            <button class="tab-btn active px-6 py-4 text-[13px] font-bold text-blue-600 border-b-2 border-blue-600 shrink-0 transition-colors" data-target="tab_kredit">KREDIT DAN DANA</button>
            <button class="tab-btn px-6 py-4 text-[13px] font-bold text-gray-500 border-b-2 border-transparent hover:text-gray-800 shrink-0 transition-colors" data-target="tab_recovery">RECOVERY</button>
            <button class="tab-btn px-6 py-4 text-[13px] font-bold text-gray-500 border-b-2 border-transparent hover:text-gray-800 shrink-0 transition-colors" data-target="tab_refinancing">PIPELINE (REFINANCING)</button>
            <button class="tab-btn px-6 py-4 text-[13px] font-bold text-gray-500 border-b-2 border-transparent hover:text-gray-800 shrink-0 transition-colors" data-target="tab_labarugi">STRATEGI & KINERJA</button>
        </div>

        <div class="p-5 overflow-x-auto custom-scrollbar">
            <table class="w-full text-xs border-collapse min-w-[1200px]">
                <thead>
                    <tr class="bg-gray-50 text-[#1e293b] border border-gray-200">
                        <th rowspan="2" class="border border-gray-200 p-3 w-[260px] text-left align-middle font-black uppercase tracking-wider text-[11px]">Indikator</th>
                        <th colspan="2" class="border border-gray-200 p-2 text-center font-black week-header" data-head="1">Minggu I</th>
                        <th colspan="2" class="border border-gray-200 p-2 text-center font-black week-header" data-head="2">Minggu II</th>
                        <th colspan="2" class="border border-gray-200 p-2 text-center font-black week-header" data-head="3">Minggu III</th>
                        <th colspan="2" class="border border-gray-200 p-2 text-center font-black week-header" data-head="4">Minggu IV</th>
                        <th rowspan="2" class="border border-gray-200 p-2 text-center align-middle font-bold w-[110px] bg-slate-100">RBB Bulan</th>
                        <th rowspan="2" class="border border-gray-200 p-2 text-center align-middle font-bold w-[110px] bg-slate-100">Realisasi Bln</th>
                        <th rowspan="2" class="border border-gray-200 p-2 text-center align-middle font-bold w-[80px] bg-slate-100">% Capaian</th>
                    </tr>
                    <tr class="bg-gray-50 text-gray-500 text-[10px] uppercase tracking-wider font-bold">
                        <th class="border border-gray-200 p-2 text-center week-subhead komitmen-head" data-head="1">Komitmen</th>
                        <th class="border border-gray-200 p-2 text-center week-subhead realisasi-head text-blue-600" data-head="1">Realisasi</th>
                        <th class="border border-gray-200 p-2 text-center week-subhead komitmen-head" data-head="2">Komitmen</th>
                        <th class="border border-gray-200 p-2 text-center week-subhead realisasi-head text-blue-600" data-head="2">Realisasi</th>
                        <th class="border border-gray-200 p-2 text-center week-subhead komitmen-head" data-head="3">Komitmen</th>
                        <th class="border border-gray-200 p-2 text-center week-subhead realisasi-head text-blue-600" data-head="3">Realisasi</th>
                        <th class="border border-gray-200 p-2 text-center week-subhead komitmen-head" data-head="4">Komitmen</th>
                        <th class="border border-gray-200 p-2 text-center week-subhead realisasi-head text-blue-600" data-head="4">Realisasi</th>
                    </tr>
                </thead>
                
                <tbody id="tab_kredit" class="tab-content"></tbody>
                <tbody id="tab_recovery" class="tab-content hidden"></tbody>
                <tbody id="tab_refinancing" class="tab-content hidden"></tbody>
                <tbody id="tab_labarugi" class="tab-content hidden"></tbody>

            </table>
        </div>
    </div>

    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 mt-6 mb-10">
        <h3 class="font-bold text-gray-800 mb-4 border-b border-gray-100 pb-3 text-sm flex items-center gap-2">
            <span class="text-blue-500">✍️</span> Pengesahan Pejabat Cabang
        </h3>
        <div class="grid md:grid-cols-3 gap-6">
            <div class="flex flex-col">
                <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Kepala Cabang</label>
                <input type="text" id="pejabat_kacab" class="border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500 font-bold bg-blue-50/30" placeholder="Nama Kepala Cabang...">
                <span class="text-[10px] text-gray-400 mt-1 italic">*Auto-fill dari user yang login</span>
            </div>
            <div class="flex flex-col">
                <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Kabid Pemasaran</label>
                <input type="text" id="pejabat_pemasaran" class="border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500 font-medium" placeholder="Nama Kabid Pemasaran...">
            </div>
            <div class="flex flex-col">
                <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Kabid Operasional</label>
                <input type="text" id="pejabat_operasional" class="border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500 font-medium" placeholder="Nama Kabid Operasional...">
            </div>
        </div>
    </div>

  </div>
</div>

<style>
  /* Styling Input Mirip Excel */
  .input-monev {
      width: 100%;
      background: transparent;
      border: 1px solid transparent;
      border-radius: 4px;
      padding: 6px 8px;
      text-align: right;
      font-weight: 700;
      color: #1e293b;
      outline: none;
      transition: all 0.2s ease;
  }
  .input-monev:focus { 
      background: #ffffff;
      border-color: #3b82f6; 
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2); 
  }
  .input-monev:disabled {
      color: #94a3b8;
      cursor: not-allowed;
  }
  .input-monev.text-left { text-align: left; font-weight: 500;}
  
  .custom-scrollbar::-webkit-scrollbar { height: 8px; width: 8px; }
  .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
  .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<script>
  // ==========================================
  // 1. DATABASE SCHEMA (PEJABAT DIHAPUS DARI SINI)
  // ==========================================
  const monevSchema = [
      // KREDIT & DPK
      { tab: 'kredit', id: 'baki_debet', label: 'Baki Debet Kredit', unit: 'Rp', type: 'rp' },
      { tab: 'kredit', id: 'pencairan', label: 'Pencairan Kredit Rp', unit: 'Rp', type: 'rp' },
      { tab: 'kredit', id: 'noa_kredit', label: 'Jumlah NOA (Kredit)', unit: 'NOA', type: 'number' },
      { tab: 'kredit', id: 'sebab_kredit', label: 'Penyebab Kredit Tidak Tercapai', unit: '', type: 'reason' },
      
      { tab: 'kredit', id: 'damas_rp', label: 'DAMAS Rp', unit: 'Rp', type: 'rp' },
      { tab: 'kredit', id: 'damas_noa', label: 'DAMAS NOA', unit: 'NOA', type: 'number' },
      { tab: 'kredit', id: 'deposito_rp', label: 'Deposito Rp', unit: 'Rp', type: 'rp' },
      { tab: 'kredit', id: 'deposito_noa', label: 'Deposito NOA', unit: 'NOA', type: 'number' },
      { tab: 'kredit', id: 'tabungan_rp', label: 'Tabungan Rp', unit: 'Rp', type: 'rp' },
      { tab: 'kredit', id: 'tabungan_noa', label: 'Tabungan NOA', unit: 'NOA', type: 'number' },
      { tab: 'kredit', id: 'persen_casa', label: '% CASA', unit: '%', type: 'percent' },

      // RECOVERY
      { tab: 'recovery', id: 'peny_par12_rp', label: 'Penyelesaian PAR 1-2 Rp', unit: 'Rp', type: 'rp' },
      { tab: 'recovery', id: 'peny_par12_noa', label: 'Penyelesaian PAR 1-2 NOA', unit: 'NOA', type: 'number' },
      { tab: 'recovery', id: 'peny_par34_rp', label: 'Penyelesaian PAR 3-4 Rp', unit: 'Rp', type: 'rp' },
      { tab: 'recovery', id: 'peny_par34_noa', label: 'Penyelesaian PAR 3-4 NOA', unit: 'NOA', type: 'number' },
      { tab: 'recovery', id: 'rec_kldm_rp', label: 'Recovery KL-D-M Rp', unit: 'Rp', type: 'rp' },
      { tab: 'recovery', id: 'rec_kldm_noa', label: 'Recovery KL-D-M NOA', unit: 'NOA', type: 'number' },
      { tab: 'recovery', id: 'peny_ph_rp', label: 'Penyelesaian PH Rp', unit: 'Rp', type: 'rp' },
      { tab: 'recovery', id: 'peny_ph_noa', label: 'Penyelesaian PH NOA', unit: 'NOA', type: 'number' },
      { tab: 'recovery', id: 'proyeksi_npl', label: 'Proyeksi NPL %', unit: '%', type: 'percent' },
      { tab: 'recovery', id: 'sebab_npl', label: 'Penyebab NPL Tidak Tercapai', unit: '', type: 'reason' },

      // PIPELINE / REFINANCING
      { tab: 'refinancing', id: 'pipe_lunas_rp', label: 'Pipeline Lunas Lancar Rp', unit: 'Rp', type: 'rp' },
      { tab: 'refinancing', id: 'pipe_lunas_noa', label: 'Pipeline Lunas Lancar NOA', unit: 'NOA', type: 'number' },
      { tab: 'refinancing', id: 'pipe_jt_rp', label: 'Pipeline Jatuh Tempo Rp', unit: 'Rp', type: 'rp' },
      { tab: 'refinancing', id: 'pipe_jt_noa', label: 'Pipeline Jatuh Tempo NOA', unit: 'NOA', type: 'number' },
      { tab: 'refinancing', id: 'pipe_baru_rp', label: 'Pipeline Debitur Baru Rp', unit: 'Rp', type: 'rp' },
      { tab: 'refinancing', id: 'pipe_baru_noa', label: 'Pipeline Debitur Baru NOA', unit: 'NOA', type: 'number' },
      { tab: 'refinancing', id: 'pipe_tot_rp', label: 'Total Pipeline Rp', unit: 'Rp', type: 'rp' },
      { tab: 'refinancing', id: 'pipe_tot_noa', label: 'Total Pipeline NOA', unit: 'NOA', type: 'number' },
      { tab: 'refinancing', id: 'pipe_btc_rp', label: 'Backflow/BTC Rp', unit: 'Rp', type: 'rp' },
      { tab: 'refinancing', id: 'pipe_btc_noa', label: 'Backflow/BTC NOA', unit: 'NOA', type: 'number' },

      // STRATEGI KINERJA (Pejabat sudah tidak ada disini)
      { tab: 'labarugi', id: 'narasi_kinerja', label: 'Narasi Strategi NPL', unit: '', type: 'text' },
      { tab: 'labarugi', id: 'pendapatan', label: 'Pendapatan', unit: 'Rp', type: 'rp' },
      { tab: 'labarugi', id: 'laba_rugi', label: 'Laba (Rugi)', unit: 'Rp', type: 'rp' },
      { tab: 'labarugi', id: 'jml_pegawai', label: 'Jumlah Pegawai', unit: 'Orang', type: 'number' },
      { tab: 'labarugi', id: 'prod_per_orang', label: 'Produktivitas per orang', unit: 'Rp', type: 'rp' },
  ];

  // ==========================================
  // 2. ENGINE RENDER TABEL DINAMIS
  // ==========================================
  function renderMonevTable(activeWeek = 2) { 
      const tabs = ['kredit', 'recovery', 'refinancing', 'labarugi'];
      
      // Highlight Header table
      document.querySelectorAll('.week-header, .week-subhead').forEach(el => {
          if (el.dataset.head == activeWeek) {
              el.classList.add('bg-blue-50', 'border-blue-200');
              el.classList.remove('bg-gray-50', 'border-gray-200', 'bg-[#f8fafc]');
              if(el.classList.contains('komitmen-head')) el.classList.add('text-blue-800');
          } else {
              el.classList.remove('bg-blue-50', 'border-blue-200', 'text-blue-800');
              el.classList.add('border-gray-200');
              if(el.classList.contains('komitmen-head')) el.classList.add('bg-[#f8fafc]');
          }
      });

      tabs.forEach(tabKey => {
          let html = '';
          let tabTitle = tabKey === 'kredit' ? 'KREDIT DAN DANA' : (tabKey === 'recovery' ? 'RECOVERY' : (tabKey === 'refinancing' ? 'PIPELINE (REFINANCING)' : 'STRATEGI & KINERJA'));
          html += `<tr><td colspan="12" class="bg-[#0f172a] text-white p-2.5 font-bold uppercase tracking-widest text-[10px]">${tabTitle}</td></tr>`;
          
          const items = monevSchema.filter(x => x.tab === tabKey);
          
          items.forEach(item => {
              let unitHtml = item.unit ? `<br><span class="text-[9px] text-gray-400 font-semibold">${item.unit}</span>` : '';
              let isReason = item.type === 'reason'; 
              let isText = item.type === 'text';
              
              let trClass = 'hover:bg-blue-50/20 border-b border-gray-200 transition-colors';
              if(isReason) trClass += ' bg-gray-50/30'; 
              
              html += `<tr class="${trClass}">`;
              html += `<td class="p-2.5 font-bold text-gray-800 border-x border-gray-200 align-top">${item.label}${unitHtml}</td>`;
              
              for(let w=1; w<=4; w++) {
                  let isActive = (w == activeWeek);
                  
                  if (isReason) {
                      html += `<td class="p-2 border border-gray-200 text-center text-gray-300 bg-gray-50">-</td>`;
                      if (isActive) {
                          html += `
                          <td class="p-1.5 border border-blue-200 bg-blue-50/30 align-top">
                              <textarea class="input-monev reason-input text-left resize-none h-[45px] leading-snug text-[11px]" 
                                  data-id="${item.id}" data-minggu="${w}" 
                                  placeholder="Isi narasi komitmen..."></textarea>
                          </td>`;
                      } else {
                          html += `<td class="p-2 border border-gray-200 text-center text-gray-300 bg-gray-50">-</td>`;
                      }

                  } else {
                      let tdKomitmen = isActive ? 'p-1.5 border border-blue-200 bg-blue-50/30 align-top' : 'p-1.5 border border-gray-200 bg-gray-50 align-top';
                      let inputAttr = isActive ? `data-minggu="${w}" id="komit_${item.id}_w${w}"` : `data-minggu="${w}" disabled`;
                      
                      html += `<td class="${tdKomitmen}">`;
                      if (isText) {
                          html += `<textarea class="input-monev text-left resize-none h-[40px] text-[11px]" ${inputAttr}></textarea>`;
                      } else {
                          html += `<input type="text" class="input-monev komitmen-val" data-target="${item.id}" ${inputAttr} value="${item.type === 'percent' ? '0,00' : '0'}">`;
                      }
                      html += `</td>`;
                      
                      let realBg = isActive ? 'bg-blue-50/10' : 'bg-gray-50/50';
                      html += `<td class="p-2.5 border border-gray-200 text-gray-600 font-bold text-right ${realBg}" id="real_${item.id}_w${w}">0</td>`;
                  }
              }
              
              if (isReason || isText) {
                  html += `<td class="p-2.5 border border-gray-200 bg-slate-50/50"></td>
                           <td class="p-2.5 border border-gray-200 bg-slate-50/50"></td>
                           <td class="p-2.5 border border-gray-200 bg-slate-50/50"></td>`;
              } else {
                  html += `<td class="p-2.5 border border-gray-200 text-right font-bold text-gray-700 bg-slate-50/50" id="rbb_${item.id}">0</td>
                           <td class="p-2.5 border border-gray-200 text-right font-bold text-gray-700 bg-slate-50/50" id="realbln_${item.id}">0</td>
                           <td class="p-2.5 border border-gray-200 text-center font-black text-red-500 bg-slate-50/50" id="cap_${item.id}">0%</td>`;
              }
              html += `</tr>`;
          });
          
          document.getElementById(`tab_${tabKey}`).innerHTML = html;
      });

      initMonevInputs();
  }


  // ==========================================
  // 3. LOGIKA TAB NAVIGATION
  // ==========================================
  const tabBtns = document.querySelectorAll('.tab-btn');
  const tabContents = document.querySelectorAll('.tab-content');

  tabBtns.forEach(btn => {
      btn.addEventListener('click', () => {
          tabBtns.forEach(t => {
              t.classList.remove('text-blue-600', 'border-blue-600', 'active');
              t.classList.add('text-gray-500', 'border-transparent');
          });
          btn.classList.add('text-blue-600', 'border-blue-600', 'active');
          btn.classList.remove('text-gray-500', 'border-transparent');

          tabContents.forEach(c => c.classList.add('hidden'));
          document.getElementById(btn.dataset.target).classList.remove('hidden');
      });
  });

  // ==========================================
  // 4. AUTO-FORMAT RIBUAN & SIMULASI LOGIKA PENYEBAB
  // ==========================================
  function initMonevInputs() {
      const inputs = document.querySelectorAll('.input-monev[type="text"]:not(:disabled)');
      inputs.forEach(input => {
          input.addEventListener('focus', function(e) {
              if(this.value === '0' || this.value === '0,00') this.value = '';
              else this.value = this.value.replace(/\./g, ''); 
          });

          input.addEventListener('blur', function(e) {
              if(this.value === '') { this.value = '0'; return; }
              let num = parseInt(this.value.replace(/\D/g, ''));
              if(!isNaN(num)) {
                  this.value = new Intl.NumberFormat('id-ID').format(num);
              } else {
                  this.value = '0';
              }
              evaluatePenyebab();
          });
      });
  }

  function evaluatePenyebab() {
      const reasonInputs = document.querySelectorAll('.reason-input');
      reasonInputs.forEach(ta => {
          let isTargetTercapai = false; 
          if (isTargetTercapai) {
              ta.value = 'Target Tercapai';
              ta.disabled = true;
              ta.classList.add('bg-gray-100', 'text-green-600', 'font-bold', 'text-center');
              ta.classList.remove('text-left');
          } else {
              ta.disabled = false;
              ta.classList.remove('bg-gray-100', 'text-green-600', 'font-bold', 'text-center');
              ta.classList.add('text-left');
          }
      });
  }

  // ==========================================
  // 5. AUTO-FILL KANTOR & PEJABAT
  // ==========================================
  function updateHeaderLabels() {
      let selectElement = document.getElementById('filter_kantor');
      let text = selectElement.options[selectElement.selectedIndex]?.text || '';
      let val = selectElement.value;
      
      if(val === '000' || !val) {
          document.getElementById('lbl_kanwil').innerText = 'KONSOLIDASI';
          document.getElementById('lbl_kantor').innerText = 'NASIONAL';
          document.getElementById('lbl_kode').innerText = '000';
      } else {
          let match = text.match(/\[(.*?)\]\s*(\d+)\s*-\s*(.*)/);
          if (match) {
              document.getElementById('lbl_kanwil').innerText = match[1];
              document.getElementById('lbl_kode').innerText = match[2];
              document.getElementById('lbl_kantor').innerText = match[3];
          }
      }
  }

  function autoFillPejabat() {
      // Dummy check user login, di real system gunakan session API / window.getUser()
      const user = window.getUser ? window.getUser() : { nama: "Pak Bos (Dummy User)" };
      
      if (user && user.nama) {
          document.getElementById('pejabat_kacab').value = user.nama;
      }
  }

  // ==========================================
  // 6. EVENT LISTENERS INIT
  // ==========================================
  document.getElementById('filter_kantor').addEventListener('change', updateHeaderLabels);

  document.getElementById('formFilterMaster').addEventListener('submit', e => {
      e.preventDefault();
      
      let bln = document.getElementById('filter_bulan');
      let nmBulan = bln.options[bln.selectedIndex].text;
      let thn = document.getElementById('filter_tahun').value;
      let mgg = document.getElementById('filter_minggu').value;
      
      document.getElementById('title_monev_detail').innerText = `MONEV ${nmBulan.toUpperCase()} ${thn}`;
      document.getElementById('lbl_minggu').innerText = `Minggu ${mgg}`;
      
      renderMonevTable(mgg);
      
      alert("Filter diterapkan! \nMinggu Aktif pindah ke: Minggu " + mgg);
  });

  window.addEventListener('DOMContentLoaded', () => {
      updateHeaderLabels();
      autoFillPejabat(); 
      renderMonevTable(2); 
      evaluatePenyebab();
  });

</script>
