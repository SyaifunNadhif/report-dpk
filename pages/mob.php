<div class="max-w-7xl mx-auto px-4 py-6 h-screen flex flex-col">
  
  <div class="mb-6">
    <h1 class="text-2xl font-bold flex items-center gap-2 text-gray-800">
      <span>📊</span><span>Analisa MOB Vintage</span>
    </h1>
  </div>

  <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 mb-4">
    <div class="text-xs text-gray-500 italic order-2 lg:order-1">
      *klik bucket untuk mengetahui debitur.<br>
    </div>

    <form id="formFilterMob" class="flex flex-wrap items-end gap-2 order-1 lg:order-2 ml-auto">
      <div class="field">
        <label class="lbl">Cabang</label>
        <select id="opt_kantor_mob" class="inp min-w-[180px]">
            <option value="">Memuat...</option>
        </select>
      </div>
      <div class="field">
        <label class="lbl">Posisi Data</label>
        <input type="date" id="harian_date_mob" class="inp" required onclick="try{this.showPicker()}catch(e){}">
      </div>
      <button type="submit" id="btnFilterMob" class="btn-icon" title="Cari Data">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5">
          <circle cx="11" cy="11" r="7"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
      </button>
    </form>
  </div>

  <div id="mobScroller" class="flex-1 min-h-0 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm relative">
    
    <div id="loadingMob" class="hidden absolute inset-0 bg-white/80 z-50 flex flex-col items-center justify-center text-sm text-blue-600 font-medium">
        <svg class="animate-spin h-8 w-8 mb-2 text-blue-500" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        <span>Sedang memuat Matrix MOB...</span>
    </div>

    <div class="h-full overflow-auto pb-0 relative">
      <table id="tabelMob" class="min-w-full text-xs text-center text-gray-700 border-collapse border-spacing-0">
        <thead class="uppercase font-bold bg-gray-100 text-gray-800 sticky top-0 z-30 shadow-sm">
          <tr>
            <th rowspan="2" class="px-3 py-3 border-r border-b sticky left-0 bg-gray-100 z-40 w-[80px] align-middle shadow-[1px_0_0_0_#e5e7eb]">Cabang</th>
            <th rowspan="2" class="px-3 py-3 border-r border-b sticky left-[80px] bg-gray-100 z-40 w-[100px] align-middle shadow-[1px_0_0_0_#e5e7eb]">Bulan<br>Realisasi</th>
            <th rowspan="2" class="px-2 py-3 border-r border-b bg-blue-50 align-middle text-blue-900">MOB</th>
            <th rowspan="2" class="px-3 py-3 border-r border-b bg-blue-50 text-right min-w-[110px] align-middle text-blue-900">Total<br>Plafond</th>
            
            <th colspan="8" class="py-2 border-b bg-gray-200 text-gray-700 tracking-wider">DPD (Days Past Due)</th>
          </tr>
          
          <tr>
            <th class="px-2 py-2 border-r border-b bg-green-100 text-green-800 min-w-[100px]">0</th>
            <th class="px-2 py-2 border-r border-b bg-yellow-50 min-w-[100px]">1 - 7</th>
            <th class="px-2 py-2 border-r border-b bg-yellow-50 min-w-[100px]">8 - 14</th>
            <th class="px-2 py-2 border-r border-b bg-yellow-100 min-w-[100px]">15 - 21</th>
            <th class="px-2 py-2 border-r border-b bg-yellow-100 min-w-[100px]">22 - 30</th>
            <th class="px-2 py-2 border-r border-b bg-orange-100 text-orange-800 min-w-[100px]">31 - 60</th>
            <th class="px-2 py-2 border-r border-b bg-red-50 text-red-800 min-w-[100px]">61 - 90</th>
            <th class="px-2 py-2 border-b bg-red-100 text-red-900 min-w-[100px]">&gt; 90</th>
          </tr>
        </thead>
        <tbody id="bodyMob" class="divide-y divide-gray-200"></tbody>
        
        <tfoot id="footMob" class="sticky-footer"></tfoot>
      </table>
    </div>
  </div>
</div>

<div id="modalDetailMob" class="fixed inset-0 hidden z-[9999] flex items-center justify-center p-4">
  <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeModalMob()"></div>
  <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-7xl max-h-[90vh] flex flex-col animate-scale-up">
    <div class="flex items-center justify-between p-5 border-b bg-white rounded-t-xl">
      <div>
        <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
            📄 Detail Debitur MOB
            <span id="badgeBucketDetail" class="px-2 py-0.5 rounded text-xs bg-blue-600 text-white">Bucket ?</span>
        </h3>
        <p class="text-sm text-gray-500" id="subTitleDetail">Loading...</p>
      </div>
      <button onclick="closeModalMob()" class="text-gray-400 hover:text-gray-700 transition-colors">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
      </button>
    </div>
    <div class="flex-1 overflow-auto bg-gray-50 relative min-h-[300px]">
      <table class="w-full text-xs text-left text-gray-700 whitespace-nowrap">
        <thead class="text-xs text-gray-600 font-bold uppercase bg-gray-200 sticky top-0 shadow-sm z-10">
          <tr>
            <th class="px-4 py-3 border-b border-gray-300">No Rekening</th>
            <th class="px-4 py-3 border-b border-gray-300">Nama Nasabah</th>
            <th class="px-4 py-3 text-right border-b border-gray-300">Plafon</th>
            <th class="px-4 py-3 text-right border-b border-gray-300">Baki Debet</th>
            <th class="px-4 py-3 text-right border-b border-gray-300 bg-red-50 text-red-700">Total Tunggakan</th>
            <th class="px-4 py-3 text-center border-b border-gray-300 bg-orange-50 text-orange-800">HM Pokok</th>
            <th class="px-4 py-3 text-center border-b border-gray-300 bg-orange-50 text-orange-800">HM Bunga</th>
            <th class="px-4 py-3 text-center border-b border-gray-300 bg-green-50 text-green-800">Tgl Trans</th>
            <th class="px-4 py-3 text-right border-b border-gray-300 bg-green-50 text-green-800">Total Bayar</th>
            <th class="px-4 py-3 text-center border-b border-gray-300">Kolek</th>
            <th class="px-4 py-3 text-center border-b border-gray-300">Cabang</th>
          </tr>
        </thead>
        <tbody id="bodyModalDetail" class="divide-y divide-gray-200 bg-white"></tbody>
      </table>
      <div id="loadingModal" class="hidden absolute inset-0 bg-white/90 flex flex-col items-center justify-center text-gray-500 z-20">
         <span class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-500 border-t-transparent mb-2"></span>
         <span>Mengambil data detail...</span>
      </div>
    </div>
    <div class="p-4 border-t bg-white rounded-b-xl flex justify-between items-center gap-2">
      <span class="text-xs text-gray-500 font-medium" id="pageInfoDetail">Menampilkan 0 data</span>
      <div class="flex items-center gap-2">
          <button id="btnPrevDetail" class="px-3 py-1.5 bg-white border border-gray-300 rounded hover:bg-gray-50 text-xs font-medium disabled:opacity-50 disabled:cursor-not-allowed transition"> &larr; Prev </button>
          <button id="btnNextDetail" class="px-3 py-1.5 bg-white border border-gray-300 rounded hover:bg-gray-50 text-xs font-medium disabled:opacity-50 disabled:cursor-not-allowed transition"> Next &rarr; </button>
          <div class="h-4 w-px bg-gray-300 mx-2"></div>
          <button onclick="closeModalMob()" class="px-4 py-1.5 bg-gray-800 text-white rounded hover:bg-gray-900 text-xs font-medium transition">Tutup</button>
      </div>
    </div>
  </div>
</div>

<style>
  .inp { border:1px solid #cbd5e1; border-radius:.5rem; padding:.4rem .75rem; font-size:13px; background:#fff; cursor: pointer; }
  .inp:disabled { background-color: #f1f5f9; color: #64748b; cursor: not-allowed; }
  .lbl { font-size:11px; color:#64748b; font-weight: 600; display: block; margin-bottom: 2px; text-transform: uppercase; }
  
  /* FIX DATEPICKER ICON */
  input[type="date"]::-webkit-inner-spin-button,
  input[type="date"]::-webkit-calendar-picker-indicator { display: none; -webkit-appearance: none; }
  
  .btn-icon { width:38px; height:38px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; background:#2563eb; color:#fff; transition:0.2s; border:none; cursor:pointer;}
  .btn-icon:hover { background:#1d4ed8; }
  .field { display: flex; flex-direction: column; }
  
  @keyframes scaleUp { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
  .animate-scale-up { animation: scaleUp 0.2s ease-out forwards; }
  
  /* Hover efek sel */
  .cell-hover:hover { background-color: #e0f2fe !important; cursor: pointer; transform: scale(1.02); transition: 0.1s; z-index: 5; position: relative; box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: 1px solid #3b82f6; }

  /* CSS KHUSUS FOOTER FLOATING */
  .sticky-footer {
      position: sticky;
      bottom: 0;
      z-index: 50;
  }
  .sticky-footer tr td {
      background-color: #1f2937; /* Gray-800 */
      color: white;
      font-weight: 700;
      border-top: 3px solid #3b82f6; /* Aksen biru di atas */
      box-shadow: 0 -4px 10px rgba(0,0,0,0.2); /* Efek bayangan ke atas */
      padding-top: 12px;
      padding-bottom: 12px;
      font-size: 11px;
  }
</style>

<script>
  // --- CONFIG ---
  const API_URL = './api/kredit/'; 
  const nfID = new Intl.NumberFormat('id-ID');
  const fmt  = n => nfID.format(Number(n||0));
  const apiCall = (url, opt={}) => (window.apiFetch ? window.apiFetch(url,opt) : fetch(url,opt));

  let abortMain;
  let detailParams = {}; 
  let detailPage = 1;
  const detailLimit = 10;
  const optKantorMob = document.getElementById('opt_kantor_mob');

  // --- INIT ---
  window.addEventListener('DOMContentLoaded', async () => {
    const user = (window.getUser && window.getUser()) || null;
    const userKode = (user?.kode ? String(user.kode).padStart(3,'0') : '000');

    await populateKantorOptionsMob(userKode);

    const d = await getLastHarianData(); 
    document.getElementById('harian_date_mob').value = d ? d.last_created : new Date().toISOString().split('T')[0];

    fetchRekapMob();
  });

  async function populateKantorOptionsMob(userKode){
    if(userKode !== '000'){
        optKantorMob.innerHTML = `<option value="${userKode}">CABANG ${userKode}</option>`;
        optKantorMob.value = userKode;
        optKantorMob.disabled = true;
        return;
    }
    try {
        const res = await apiCall('./api/kode/', { 
            method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({type:'kode_kantor'}) 
        });
        const json = await res.json();
        const list = Array.isArray(json.data) ? json.data : [];
        let html = `<option value="">Konsolidasi (Semua Cabang)</option>`;
        list.filter(x => x.kode_kantor && x.kode_kantor !== '000')
            .sort((a,b) => String(a.kode_kantor).localeCompare(b.kode_kantor))
            .forEach(it => {
               html += `<option value="${String(it.kode_kantor).padStart(3,'0')}">${String(it.kode_kantor).padStart(3,'0')} - ${it.nama_kantor}</option>`;
            });
        optKantorMob.innerHTML = html;
        optKantorMob.disabled = false;
    } catch(e){
        optKantorMob.innerHTML = `<option value="">Error Load</option>`;
    }
  }

  async function getLastHarianData(){
    try{
        const r=await apiCall('./api/date/'); return (await r.json()).data;
    }catch{ return null; }
  }

  document.getElementById('formFilterMob').addEventListener('submit', e => {
    e.preventDefault();
    fetchRekapMob();
  });

  // --- 1. FETCH REKAP MOB ---
  async function fetchRekapMob(){
    const loading = document.getElementById('loadingMob');
    const tbody   = document.getElementById('bodyMob');
    const tfoot   = document.getElementById('footMob');
    const harian  = document.getElementById('harian_date_mob').value;
    const kode    = optKantorMob.value || null; 

    if(abortMain) abortMain.abort();
    abortMain = new AbortController();

    loading.classList.remove('hidden');
    tbody.innerHTML = '';
    tfoot.innerHTML = '';

    try {
        const payload = { 
            type: "mob_vintage",
            harian_date: harian,
            kode_kantor: kode
        };
        
        const res = await apiCall(API_URL, {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify(payload),
            signal: abortMain.signal
        });
        const json = await res.json();
        
        if(json.status !== 200) throw new Error(json.message);

        const rawData = json.data.data || [];
        const bucketsKey = json.data.buckets_order || ['0', '1 - 7', '8 - 14', '15 - 21', '22 - 30', '31 - 60', '61 - 90', '> 90'];

        if(rawData.length === 0){
            tbody.innerHTML = `<tr><td colspan="12" class="py-10 text-gray-400">Data Kosong</td></tr>`;
            return;
        }

        // --- AGGREGATION LOGIC ---
        let displayData = [];
        
        if (!kode) { // KONSOLIDASI (Gabung Data)
            const grouped = {};
            rawData.forEach(row => {
                const key = row.bulan_realisasi;
                if (!grouped[key]) {
                    grouped[key] = {
                        kode_cabang: "ALL", 
                        bulan_realisasi: key,
                        mob: row.mob,
                        total_plafond: 0,
                        buckets: {}
                    };
                    bucketsKey.forEach(b => grouped[key].buckets[b] = { os: 0, noa: 0, pct: 0 });
                }
                
                // Sum Total Values
                grouped[key].total_plafond += parseFloat(row.total_plafond || 0);
                
                // Sum Bucket Values
                bucketsKey.forEach(b => {
                    const srcBucket = row.buckets[b] || { os:0, noa:0 };
                    grouped[key].buckets[b].os  += parseFloat(srcBucket.os || 0);
                    grouped[key].buckets[b].noa += parseInt(srcBucket.noa || 0);
                });
            });

            // Convert to Array
            displayData = Object.values(grouped).sort((a,b) => a.bulan_realisasi.localeCompare(b.bulan_realisasi));
            
        } else {
            // PER CABANG (Data Asli)
            displayData = rawData;
        }

        // --- PERBAIKAN: HITUNG PERSENTASE UNTUK KEDUA KONDISI ---
        // (Sebelumnya hanya dihitung saat konsolidasi, makanya filter cabang undefined)
        displayData.forEach(row => {
            const pembagi = parseFloat(row.total_plafond) > 0 ? parseFloat(row.total_plafond) : 1;
            bucketsKey.forEach(b => {
                if(!row.buckets[b]) row.buckets[b] = { os:0, noa:0, pct:0 }; // Safety check
                
                // Hitung Ulang % (Pastikan selalu ada value)
                row.buckets[b].pct = ((parseFloat(row.buckets[b].os) / pembagi) * 100).toFixed(2);
            });
        });

        // --- RENDER TABLE ---
        let html = '';
        let grandTotal = { plafond: 0, buckets: {} };
        bucketsKey.forEach(b => grandTotal.buckets[b] = { os:0, noa:0 });

        displayData.forEach(r => {
            // Accumulate Grand Total
            grandTotal.plafond += parseFloat(r.total_plafond || 0);

            let cells = '';
            bucketsKey.forEach(key => {
                const bData = r.buckets[key] || { pct:0, noa:0, os:0 };
                
                // Add Bucket to Grand Total
                grandTotal.buckets[key].os  += parseFloat(bData.os || 0);
                grandTotal.buckets[key].noa += parseInt(bData.noa || 0);

                // Styling
                let bgClass = 'bg-transparent';
                if(key !== '0' && parseFloat(bData.pct) > 0) bgClass = 'bg-red-50 text-red-600';
                if(key === '0' && parseFloat(bData.pct) > 90) bgClass = 'bg-green-50 text-green-700';

                const cabangParam = (!kode) ? '' : r.kode_cabang;

                cells += `
                    <td class="px-2 py-2 border-r text-[11px] ${bgClass} cell-hover transition"
                        onclick="openModalMob('${cabangParam}', '${r.bulan_realisasi}', '${key}')">
                        <div class="font-bold">${bData.pct}%</div>
                        <div class="text-[10px] text-gray-700 font-mono mt-0.5 whitespace-nowrap">${fmt(bData.os)}</div>
                        <div class="text-[9px] text-gray-400 font-normal">(${bData.noa})</div>
                    </td>
                `;
            });

            const labelCabang = (!kode) ? "KONSOLIDASI" : r.kode_cabang;

            html += `
                <tr class="hover:bg-gray-50 border-b group">
                    <td class="px-3 py-2 border-r font-mono text-gray-500 sticky left-0 bg-white group-hover:bg-gray-50 shadow-[1px_0_0_0_#e5e7eb] z-10">${labelCabang}</td>
                    <td class="px-3 py-2 border-r font-medium sticky left-[80px] bg-white group-hover:bg-gray-50 shadow-[1px_0_0_0_#e5e7eb] z-10">${r.bulan_realisasi}</td>
                    <td class="px-2 py-2 border-r font-bold text-blue-600 bg-blue-50/20">${r.mob}</td>
                    <td class="px-3 py-2 border-r text-right font-mono text-[11px] text-gray-600">${fmt(r.total_plafond)}</td>
                    ${cells}
                </tr>
            `;
        });
        tbody.innerHTML = html;

        // --- RENDER FOOTER (GRAND TOTAL) ---
        let footerCells = '';
        bucketsKey.forEach(key => {
            const bTot = grandTotal.buckets[key];
            const pembagiTotal = grandTotal.plafond > 0 ? grandTotal.plafond : 1;
            const pctTotal = ((bTot.os / pembagiTotal) * 100).toFixed(2);

            footerCells += `
                <td class="px-2 py-3 border-r border-gray-600 text-center">
                    <div class="font-bold text-white">${pctTotal}%</div>
                    <div class="text-[10px] text-gray-300 font-mono mt-0.5 whitespace-nowrap">${fmt(bTot.os)}</div>
                    <div class="text-[9px] opacity-70">(${bTot.noa})</div>
                </td>
            `;
        });

        tfoot.innerHTML = `
            <tr>
                <td class="px-3 py-3 border-r border-gray-600 sticky left-0 bg-gray-800 z-50 shadow-[1px_0_0_0_#4b5563]" colspan="2">GRAND TOTAL</td>
                <td class="px-2 py-3 border-r border-gray-600 text-center">-</td>
                <td class="px-3 py-3 border-r border-gray-600 text-right font-mono text-white">${fmt(grandTotal.plafond)}</td>
                ${footerCells}
            </tr>
        `;

    } catch(err) {
        if(err.name !== 'AbortError') tbody.innerHTML = `<tr><td colspan="12" class="py-10 text-red-500">${err.message}</td></tr>`;
    } finally {
        loading.classList.add('hidden');
    }
  }

  // --- 2. MODAL LOGIC ---
  async function openModalMob(cabang, bulan, bucket){
      detailParams = {
          type: "detail_mob_debitur",
          harian_date: document.getElementById('harian_date_mob').value,
          kode_kantor: cabang, 
          bulan_realisasi: bulan,
          bucket_label: bucket
      };
      detailPage = 1;

      document.getElementById('modalDetailMob').classList.remove('hidden');
      document.getElementById('badgeBucketDetail').innerText = `Bucket: ${bucket}`;
      const txtCabang = cabang ? `Cabang ${cabang}` : "SEMUA CABANG";
      document.getElementById('subTitleDetail').innerText = `${txtCabang} • Realisasi ${bulan}`;
      
      fetchDetailMob();
  }

  async function fetchDetailMob(){
      const loader = document.getElementById('loadingModal');
      const tbody  = document.getElementById('bodyModalDetail');
      const info   = document.getElementById('pageInfoDetail');
      const btnPrev = document.getElementById('btnPrevDetail');
      const btnNext = document.getElementById('btnNextDetail');

      loader.classList.remove('hidden');
      tbody.innerHTML = '';

      try {
          const payload = { ...detailParams, page: detailPage };
          const res = await apiCall(API_URL, {
              method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)
          });
          const json = await res.json();
          
          const list = json.data?.data || [];
          const totalRecords = json.data?.total_records || 0;
          const totalPages   = json.data?.total_pages || 1;

          if(list.length === 0){
              tbody.innerHTML = `<tr><td colspan="11" class="py-8 text-center text-gray-400">Tidak ada data detail.</td></tr>`;
              info.innerText = `0 Data`;
              btnPrev.disabled = true; btnNext.disabled = true;
              return;
          }

          let html = '';
          list.forEach(row => {
              html += `
                <tr class="hover:bg-blue-50/30 border-b transition">
                    <td class="px-4 py-2 font-mono text-gray-600 text-xs">${row.no_rekening}</td>
                    <td class="px-4 py-2 font-medium text-gray-900 truncate max-w-[150px]" title="${row.nama_nasabah}">${row.nama_nasabah}</td>
                    <td class="px-4 py-2 text-right text-gray-500">${fmt(row.plafond)}</td>
                    <td class="px-4 py-2 text-right font-bold text-gray-800">${fmt(row.os)}</td>
                    <td class="px-4 py-2 text-right text-red-600 bg-red-50/50">${fmt(row.totung)}</td>
                    <td class="px-4 py-2 text-center text-orange-700 bg-orange-50/50 font-mono">${row.hari_menunggak_pokok}</td>
                    <td class="px-4 py-2 text-center text-orange-700 bg-orange-50/50 font-mono">${row.hari_menunggak_bunga}</td>
                    <td class="px-4 py-2 text-center text-green-700 bg-green-50/50 text-[10px]">${row.tgl_trans || '-'}</td>
                    <td class="px-4 py-2 text-right text-green-700 bg-green-50/50 font-bold">${fmt(row.transaksi)}</td>
                    <td class="px-4 py-2 text-center font-bold text-xs">${row.kolektibilitas}</td>
                    <td class="px-4 py-2 text-center text-xs text-gray-500">${row.kode_cabang}</td>
                </tr>
              `;
          });
          tbody.innerHTML = html;

          info.innerText = `Hal ${detailPage} dari ${totalPages} (${totalRecords} Data)`;
          
          btnPrev.disabled = detailPage <= 1;
          btnNext.disabled = detailPage >= totalPages;
          
          btnPrev.onclick = () => { detailPage--; fetchDetailMob(); };
          btnNext.onclick = () => { detailPage++; fetchDetailMob(); };

      } catch(e){
          console.error(e);
          tbody.innerHTML = `<tr><td colspan="11" class="py-4 text-center text-red-500">Error mengambil data.</td></tr>`;
      } finally {
          loader.classList.add('hidden');
      }
  }

  window.closeModalMob = function(){
      document.getElementById('modalDetailMob').classList.add('hidden');
  }
  document.addEventListener('keydown', e => { if(e.key === 'Escape') closeModalMob(); });
</script>