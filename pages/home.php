<!-- 📊 Migrasi Kolektibilitas — mobile 2 baris filter, grafik padat, pengurangan = batang hijau -->
<div class="max-w-7xl mx-auto px-4 py-6">
  <h1 class="text-2xl font-bold mb-4">📊 Migrasi Kolektibilitas</h1>

  <!-- Filter (mobile: 2 baris) -->
  <form id="formFilterMigrasi" class="filter-wrap mb-4">
    <div class="field">
      <label class="text-sm" for="closing_date_migrasi">Closing:</label>
      <input type="date" id="closing_date_migrasi" class="border rounded px-3 py-1 text-sm" required>
    </div>
    <div class="field">
      <label class="text-sm" for="harian_date_migrasi">Harian:</label>
      <input type="date" id="harian_date_migrasi" class="border rounded px-3 py-1 text-sm" required>
    </div>
    <div class="field">
      <label class="text-sm" for="opt_kantor">Cabang:</label>
      <select id="opt_kantor" class="border rounded px-3 py-1 text-sm min-w-[220px]">
        <option value="">Konsolidasi (Semua Cabang)</option>
      </select>
    </div>
    <button id="btnFilter" type="submit" class="btn-icon" title="Filter">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <circle cx="11" cy="11" r="7" stroke-width="2"></circle>
        <line x1="21" y1="21" x2="16.65" y2="16.65" stroke-width="2" stroke-linecap="round"></line>
      </svg>
    </button>
  </form>

  <!-- Ringkasan -->
  <div id="summaryWrap" class="space-y-2 mb-4 hidden">
    <div class="summary-row flex flex-wrap items-center gap-2 text-sm">
      <span class="pill pill-blue"><span class="lbl-npl-prev">% NPL Closing</span>: <b id="npl_prev_pct">0%</b></span>
      <span class="pill pill-green"><span class="lbl-npl-now">% NPL Harian</span>: <b id="npl_now_pct">0%</b></span>
      <span class="pill pill-purple">Δ % NPL: <b id="npl_delta_pct">0%</b></span>

      <!-- disembunyikan di mobile -->
      <span class="pill pill-red sm-hidden">Flow Par: <b id="pb_total">0</b></span>
      <span class="pill pill-amber sm-hidden">Run Off: <b id="runoff_total">0</b></span>
      <span class="pill pill-rose sm-hidden">Growth: <b id="growth_val">0</b></span>
    </div>
  </div>

  <!-- 2 Grafik -->
  <div id="chartsWrap" class="grid md:grid-cols-2 gap-4 mb-4 hidden">
    <!-- KIRI -->
    <div class="p-4 bg-white rounded shadow">
      <h3 class="font-semibold mb-2">OSC vs NPL — M-1 vs Actual</h3>
      <div id="chartOscNpl" class="svgbox"></div>
      <div class="text-sm mt-1">
        <b>Selisih OSC NPL:</b> <span id="selisih_osc_npl" class="font-semibold"></span>
        <span class="text-xs text-gray-500">(actual − M-1)</span>
      </div>
    </div>

    <!-- KANAN -->
    <div class="p-4 bg-white rounded shadow">
      <div class="flex items-center justify-between mb-2">
        <h3 class="font-semibold">Realisasi, Run Off, Flow Par, & Pengurangan OSC NPL</h3>
        <span id="badgeGrowth" class="pill text-xs"></span>
      </div>
      <div id="chartChange" class="svgbox"></div>
      <div class="flex flex-wrap gap-4 mt-2 text-xs">
        <span class="inline-flex items-center gap-1"><span class="legend" style="background:#6366f1"></span> Realisasi</span>
        <span class="inline-flex items-center gap-1"><span class="legend" style="background:#fb923c"></span> Run Off</span>
        <span class="inline-flex items-center gap-1"><span class="legend" style="background:#f472b6"></span> Flow Par</span>
        <span class="inline-flex items-center gap-1"><span class="legend" style="background:#10b981"></span> Pengurangan OSC NPL</span>
      </div>
    </div>
  </div>

  <!-- Tabel -->
  <div class="overflow-x-auto">
    <table class="min-w-full text-sm border border-gray-300 text-center">
      <thead class="bg-gray-100 text-gray-800 sticky top-0">
        <tr>
          <th class="border px-2 py-2">Kolek M-1</th>
          <th class="border px-2 py-2">Osc M-1</th>
          <th class="border px-2 py-2">→ L</th>
          <th class="border px-2 py-2">→ DP</th>
          <th class="border px-2 py-2">→ KL</th>
          <th class="border px-2 py-2">→ D</th>
          <th class="border px-2 py-2">→ M</th>
          <th class="border px-2 py-2">Run Off</th>
          <th class="border px-2 py-2">Lunas</th>
          <th class="border px-2 py-2">Angsuran</th> <!-- ganti label -->
        </tr>
      </thead>
      <tbody id="bodyMigrasi" class="text-gray-900"></tbody>
    </table>
  </div>

  <!-- Loading -->
  <div id="loadingMigrasi" class="hidden flex items-center gap-2 text-sm text-gray-600 mt-3">
    <svg class="animate-spin h-5 w-5 text-blue-600" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span>Memuat data migrasi...</span>
  </div>
</div>

<style>
  .svgbox svg{width:100%;height:260px;display:block}
  .legend{width:12px;height:12px;border-radius:3px;display:inline-block}
  .pill{display:inline-block;padding:6px 10px;border-radius:8px;border:1px solid}
  .pill-red{background:#fef2f2;color:#991b1b;border-color:#fecaca}
  .pill-blue{background:#eff6ff;color:#1e40af;border-color:#bfdbfe}
  .pill-green{background:#ecfdf5;color:#065f46;border-color:#a7f3d0}
  .pill-purple{background:#faf5ff;color:#6b21a8;border-color:#e9d5ff}
  .pill-amber{background:#fff7ed;color:#9a3412;border-color:#fed7aa}
  .pill-rose{background:#fff1f2;color:#9f1239;border-color:#fecdd3}
  .pill-ok{background:#ecfdf5;color:#065f46;border-color:#a7f3d0}
  .pill-bad{background:#fff1f2;color:#9f1239;border-color:#fecdd3}

  .filter-wrap{display:flex; flex-wrap:wrap; align-items:end; gap:12px;}
  .field{display:flex; align-items:center; gap:8px;}
  .btn-icon{display:flex; align-items:center; justify-content:center; background:#2563eb; color:#fff; padding:8px; border-radius:8px;}
  .btn-icon:hover{background:#1d4ed8;}

  /* MOBILE */
  .sm-hidden{}
  @media (max-width:640px){
    /* Filter: 2 baris grid (tidak ketutup) */
    .filter-wrap{
      display:grid; grid-template-columns: 1fr 1fr; gap:8px 10px; align-items:end;
    }
    .filter-wrap .field{flex-direction:column; align-items:stretch; gap:4px;}
    .filter-wrap .field label{display:none;} /* hide judul */
    .filter-wrap .field:nth-child(1){grid-column:1;} /* Closing */
    .filter-wrap .field:nth-child(2){grid-column:2;} /* Harian  */
    .filter-wrap .field:nth-child(3){grid-column:1;} /* Cabang  */
    .filter-wrap #btnFilter{grid-column:2; height:42px; min-width:46px; justify-self:start;}

    /* Ringkasan 1 baris, bisa geser */
    .summary-row{flex-wrap:nowrap; overflow-x:auto; gap:.5rem; -webkit-overflow-scrolling:touch;}
    .pill{font-weight:600;} /* mobile: bold badges */

    /* Grafik: padat (tinggi turun) */
    .svgbox svg{height:320px}

    /* Tabel: lebih rapat + tebal agar kebaca */
    table.min-w-full{font-size:.9rem}
    thead th{padding:.5rem .5rem; font-weight:700}
    tbody td{padding:.5rem .5rem; font-weight:600}
    .sm-hidden{display:none!important;}
  }
</style>

<script>
  // ==========================================================================
  // 1. HELPER FUNCTIONS (Format Angka, SVG, & Deteksi Mobile)
  // ==========================================================================
  const nfID = new Intl.NumberFormat('id-ID');
  const fmt  = n => nfID.format(Number(n||0));
  const num  = v => Number(v||0);
  const pct2 = x => (x==null?'0%':`${(+x).toFixed(2)}%`);
  const apiCall = (url,opt={}) => (window.apiFetch?window.apiFetch(url,opt):fetch(url,opt));

  // Cek apakah layar kecil (Mobile)
  const isMobile = () => window.matchMedia('(max-width:640px)').matches;

  // SVG Helpers
  const svgNS='http://www.w3.org/2000/svg';
  const mk=(t,a={})=>{const e=document.createElementNS(svgNS,t);for(const k in a)e.setAttribute(k,a[k]);return e;};
  const add=(p,...n)=>n.forEach(x=>p.appendChild(x));
  const label=(x,y,s,fill='#374151',size=12,anchor='middle',weight=400)=>{
    const t=mk('text',{x,y,'text-anchor':anchor,'font-size':size,fill});
    if(weight!==400)t.setAttribute('font-weight',weight);
    t.textContent=s;return t;
  };

  // Helper: Ubah label ringkasan saat mobile
  function applyMobileSummaryLabels(){
    try{
      const mobile = isMobile();
      const elPrev = document.querySelector('.lbl-npl-prev');
      const elNow  = document.querySelector('.lbl-npl-now');
      if (elPrev) elPrev.textContent = mobile ? 'NPL M-1' : '% NPL Closing';
      if (elNow)  elNow.textContent  = mobile ? 'NPL Act'  : '% NPL Harian';
    }catch(_e){}
  }

  // ==========================================================================
  // 2. CHART RENDERING LOGIC
  // ==========================================================================
  
  // Chart 1: Perbandingan OSC & NPL (M-1 vs Actual)
  function chartOscNpl(elId, totalClosing, nplClosing, totalHarian, nplHarian){
    const mob = isMobile();
    const W   = mob ? 640 : 520;
    const H   = mob ? 320 : 260;
    const baseY=H-44;

    const groupGap= mob ? 140 : 135;
    const barW    = mob ? 60  : 38;
    const intraGap= mob ? 20  : 12;
    const fontVal = mob ? 15  : 12;
    const fontAxis= mob ? 13  : 12;
    const weight  = mob ? 600 : 400;

    const max=Math.max(num(totalClosing),num(totalHarian),1);
    const k=(H-92)/max;

    const svg=mk('svg',{viewBox:`0 0 ${W} ${H}`});
    svg.appendChild(mk('line',{x1:40,y1:baseY,x2:W-20,y2:baseY,stroke:'#e5e7eb'}));

    const drawGroup=(gx,labelText,total,npl)=>{
      const t=num(total)*k;
      const x1=gx-(barW/2+intraGap/2);
      svg.appendChild(mk('rect',{x:x1,y:baseY-t,width:barW,height:Math.max(1,t),rx:10,fill:'#60a5fa'}));
      add(svg,label(x1+barW/2,baseY-t-8, fmt(total), '#111827', fontVal,'middle',weight));

      const n=num(npl)*k;
      const x2=gx+(barW/2+intraGap/2)-barW;
      svg.appendChild(mk('rect',{x:x2,y:baseY-n,width:barW,height:Math.max(1,n),rx:10,fill:'#059669'}));
      add(svg,label(x2+barW/2,baseY-n-8, fmt(npl), '#111827', fontVal,'middle',weight));

      add(svg,label(gx,H-12,labelText,'#374151',fontAxis,'middle',weight));
    };

    const startX = mob ? 170 : 150;
    drawGroup(startX,'M-1',totalClosing,nplClosing);
    drawGroup(startX+groupGap,'Actual', totalHarian, nplHarian);

    const host=document.getElementById(elId);
    host.innerHTML=''; host.appendChild(svg);
  }

  // Chart 2: Komposisi Perubahan (Realisasi, RunOff, FlowPar, Pengurangan)
  function chartChange(elId, realisasi, flowpar, runoff, backflow, lunas, angsuran){
    const mob = isMobile();
    const W   = mob ? 680 : 640;
    const H   = mob ? 320 : 260;
    const baseY=H-44;

    const rightPad = mob ? 210 : 180;
    const gapFixed = mob ? 92  : 90;
    const barW     = mob ? 68  : 52;
    const fontVal  = mob ? 15  : 12;
    const fontAxis = mob ? 13  : 12;
    const weight   = mob ? 600 : 400;

    const r=num(realisasi), p=num(flowpar), o=num(runoff);
    const b=num(backflow),  l=num(lunas),   a=num(angsuran);
    const pengAbs = Math.abs(b+l+a); // Nilai absolut pengurangan

    const max = Math.max(r,o,p,pengAbs,1);
    const k   = (H-92)/max;

    const svg=mk('svg',{viewBox:`0 0 ${W} ${H}`});
    svg.appendChild(mk('line',{x1:40,y1:baseY,x2:W-20,y2:baseY,stroke:'#e5e7eb'}));

    // Koordinat X Batang
    const left = 70;
    const x0 = left;
    const x1 = x0 + gapFixed;
    const x2 = x1 + gapFixed;
    const x3 = x2 + gapFixed;

    const drawBar=(x,val,color,txt)=>{
      const h=val*k;
      svg.appendChild(mk('rect',{x:x-barW/2,y:baseY-h,width:barW,height:Math.max(1,h),rx:12,fill:color}));
      add(svg,label(x,baseY-h-10,fmt(val),'#111827',fontVal,'middle',weight));
      add(svg,label(x,H-12,txt,'#374151',fontAxis,'middle',weight));
    };

    drawBar(x0, r, '#6366f1', 'Realisasi');
    drawBar(x1, o, '#fb923c', 'Run Off');
    drawBar(x2, p, '#f472b6', 'Flow Par');
    drawBar(x3, pengAbs, '#10b981', 'Pengurangan'); // Hijau

    // Keterangan Samping (Detail Pengurangan)
    const infoX = W - rightPad + 12;
    let   infoY = H/2 - (mob?30:24);
    const write = (t,bold=false)=>{
      const n=mk('text',{'font-size':mob?13:11,x:infoX,y:infoY,fill:'#374151','text-anchor':'start'});
      if(bold) n.setAttribute('font-weight','600');
      n.textContent=t; svg.appendChild(n); infoY += (mob?18:14);
    };
    write('Total Pengurangan OSC NPL:', true);
    write('— Backflow: -'+fmt(Math.abs(b)));
    write('— Lunas NPL: -'+fmt(Math.abs(l)));
    write('— Angsuran NPL: -'+fmt(Math.abs(a)));

    const host=document.getElementById(elId);
    host.innerHTML=''; host.appendChild(svg);
  }

  // ==========================================================================
  // 3. MAIN LOGIC & DATA FETCHING
  // ==========================================================================
  
  let abortMigrasi;
  const optKantor = document.getElementById('opt_kantor');

  // Init saat halaman siap
  window.addEventListener('DOMContentLoaded', async () => {
    applyMobileSummaryLabels();

    const d = await getLastHarianData(); 
    if(!d) return;

    document.getElementById('closing_date_migrasi').value = d.last_closing;
    document.getElementById('harian_date_migrasi').value  = d.last_created;

    const user = (window.getUser && window.getUser()) || null;
    const userKode = (user?.kode ? String(user.kode).padStart(3,'0') : null);
    
    await populateKantorOptions(userKode);
    fetchMigrasiData(d.last_closing, d.last_created, (userKode && userKode!=='000') ? userKode : null);
  });

  async function getLastHarianData(){
    try{
        const r=await apiCall('./api/date/'); 
        const j=await r.json(); 
        return j.data||null;
    }catch{ return null; }
  }

  // --- Fungsi Populate Dropdown (Korwil + Cabang) ---
  async function populateKantorOptions(userKode){
    try{
      // A. Jika user LOGIN sebagai CABANG (bukan 000/Pusat) -> Lock dropdown
      if(userKode && userKode!=='000'){
        optKantor.innerHTML=`<option value="${userKode}">${userKode}</option>`;
        optKantor.value=userKode; 
        optKantor.disabled=true; 
        return;
      }

      // B. Jika user PUSAT -> Ambil semua cabang
      const res = await apiCall('./api/kode/', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ type: 'kode_kantor' })
});
const json = await res.json();
const list = Array.isArray(json.data) ? json.data : [];

let html = `<option value="">Konsolidasi (Semua Cabang)</option>`;

// 1. Masukkan opsi KORWIL (Langsung tambah, tanpa optgroup)
html += `
  <option value="SEMARANG">Korwil Semarang</option>
  <option value="SOLO">Korwil Solo</option>
  <option value="BANYUMAS">Korwil Banyumas</option>
  <option value="PEKALONGAN">Korwil Pekalongan</option>
`;


// 3. Masukkan opsi CABANG (Looping)
list.filter(x => x.kode_kantor && x.kode_kantor !== '000')
    .sort((a, b) => String(a.kode_kantor).localeCompare(String(b.kode_kantor)))
    .forEach(it => {
        const code = String(it.kode_kantor).padStart(3, '0');
        const name = it.nama_kantor || it.nama_cabang || '';
        html += `<option value="${code}">${code} — ${name}</option>`;
    });

optKantor.innerHTML = html;
      optKantor.disabled=false;

    } catch(e){
      // Fallback error
      optKantor.innerHTML=`<option value="">Konsolidasi (Semua Cabang)</option>`; 
      optKantor.disabled=false;
    }
  }

  // Event Listener: Ubah Dropdown
  optKantor.addEventListener('change', ()=>{
    const closing = document.getElementById('closing_date_migrasi').value;
    const harian  = document.getElementById('harian_date_migrasi').value;
    const val     = optKantor.value || null;
    if(closing && harian) fetchMigrasiData(closing, harian, val);
  });

  // Event Listener: Submit Form (Tombol Filter)
  document.getElementById('formFilterMigrasi').addEventListener('submit', e=>{
    e.preventDefault();
    const closing = document.getElementById('closing_date_migrasi').value;
    const harian  = document.getElementById('harian_date_migrasi').value;
    const val     = optKantor.value || null;
    fetchMigrasiData(closing, harian, val);
  });

  // --- Fungsi Utama Fetch Data ---
  async function fetchMigrasiData(closing_date, harian_date, selected_value){
    const loading = document.getElementById('loadingMigrasi');
    const tbody   = document.getElementById('bodyMigrasi');

    loading.classList.remove('hidden');
    tbody.innerHTML = `<tr><td colspan="10" class="py-4 text-gray-500">Memuat data...</td></tr>`;
    document.getElementById('summaryWrap').classList.add('hidden');
    document.getElementById('chartsWrap').classList.add('hidden');

    if(abortMigrasi) abortMigrasi.abort();
    abortMigrasi = new AbortController();

    try {
      const payload = { type:'Migrasi Kolek', closing_date, harian_date };

      // LOGIKA PENTING: Bedakan Korwil vs Cabang
      const listKorwil = ['SEMARANG', 'SOLO', 'BANYUMAS', 'PEKALONGAN'];
      
      if (selected_value) {
          if (listKorwil.includes(selected_value)) {
              // Kirim sebagai korwil
              payload.korwil = selected_value;
          } else {
              // Kirim sebagai kode_kantor
              payload.kode_kantor = selected_value;
          }
      }

      const res = await apiCall('./api/kredit/', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify(payload),
        signal: abortMigrasi.signal
      });
      
      const result = await res.json();
      
      if(result.status !== 200){
        tbody.innerHTML=`<tr><td colspan="10" class="py-4 text-red-600">${result.message||'Gagal memuat data'}</td></tr>`;
        return;
      }

      const data = Array.isArray(result.data) ? result.data : [];
      const totalApi = data.find(r => (r.kolek_closed||'').toUpperCase() === 'TOTAL') || {};

      // 1. Render Summary Header
      document.getElementById('npl_prev_pct').textContent  = pct2(totalApi.npl_prev_pct);
      document.getElementById('npl_now_pct').textContent   = pct2(totalApi.npl_now_pct);
      document.getElementById('npl_delta_pct').textContent = pct2(totalApi.npl_delta_pct);
      applyMobileSummaryLabels();

      const realisasi = num(totalApi.realisasi_bulan_ini || 0);

      // Hitung Flow Par (Total Migrasi L->NPL + DP->NPL)
      const rowL  = data.find(r => (r.kolek_closed||'').toUpperCase() === 'L') || {};
      const rowDP = data.find(r => (r.kolek_closed||'').toUpperCase() === 'DP') || {};
      const flowParCalc = (num(rowL.migrasi_KL) + num(rowL.migrasi_D) + num(rowL.migrasi_M))
                        + (num(rowDP.migrasi_KL) + num(rowDP.migrasi_D) + num(rowDP.migrasi_M));
      document.getElementById('pb_total').textContent = fmt(flowParCalc);

      // 2. Render Tabel
      const order = ['L','DP','KL','D','M'];
      // Filter baris 'TOTAL' agar tidak double render
      const rows = data.filter(r => (r.kolek_closed||'').toUpperCase() !== 'TOTAL')
                       .sort((a,b) => {
                          const ia = order.indexOf(String(a.kolek_closed).toUpperCase()); 
                          const ib = order.indexOf(String(b.kolek_closed).toUpperCase()); 
                          return (ia===-1?99:ia) - (ib===-1?99:ib);
                       });

      const tot = { osc:0, L:0, DP:0, KL:0, D:0, M:0, runOff:0, lunas:0, runOffMurni:0 };

      // Baris Realisasi (Header Tabel)
      let html = `
        <tr class="bg-orange-50 font-semibold">
          <td class="border px-3 py-2 text-left">Realisasi</td>
          <td class="border px-3 py-2 text-center">-</td>
          <td class="border px-3 py-2 text-right">${fmt(realisasi)}</td>
          <td class="border px-3 py-2 text-right">0</td>
          <td class="border px-3 py-2 text-right">0</td>
          <td class="border px-3 py-2 text-right">0</td>
          <td class="border px-3 py-2 text-right">0</td>
          <td class="border px-3 py-2 text-right">0</td>
          <td class="border px-3 py-2 text-right">0</td>
          <td class="border px-3 py-2 text-right">0</td>
        </tr>`;

      // Loop Data Per Kolek
      for(const it of rows){
        const oscM1 = num(it.saldo_closed||0);
        const mL=num(it.migrasi_L), mDP=num(it.migrasi_DP), mKL=num(it.migrasi_KL), mD=num(it.migrasi_D), mM=num(it.migrasi_M);
        const runOffR    = num(it.pembayaran||0);
        const lunasR     = num(it.lunas_osc||0);
        const runOffAsli = num(it.run_off_asli||0);

        tot.osc+=oscM1; tot.L+=mL; tot.DP+=mDP; tot.KL+=mKL; tot.D+=mD; tot.M+=mM;
        tot.runOff+=runOffR; tot.lunas+=lunasR; tot.runOffMurni+=runOffAsli;

        html+=`
          <tr class="bg-white hover:bg-gray-50">
            <td class="border px-3 py-2 text-left">${it.kolek_closed}</td>
            <td class="border px-3 py-2 text-right">${fmt(oscM1)}</td>
            <td class="border px-3 py-2 text-right">${fmt(mL)}</td>
            <td class="border px-3 py-2 text-right">${fmt(mDP)}</td>
            <td class="border px-3 py-2 text-right">${fmt(mKL)}</td>
            <td class="border px-3 py-2 text-right">${fmt(mD)}</td>
            <td class="border px-3 py-2 text-right">${fmt(mM)}</td>
            <td class="border px-3 py-2 text-right">${fmt(Math.abs(runOffR))}</td> 
            <td class="border px-3 py-2 text-right">${fmt(lunasR)}</td>
            <td class="border px-3 py-2 text-right">${fmt(Math.abs(runOffAsli))}</td>
          </tr>`;
      }

      // Baris Total Bawah
      const totalL_WithRealisasi = tot.L + realisasi;
      html+=`
        <tr class="bg-yellow-100 font-semibold text-gray-900">
          <td class="border px-3 py-2 text-left">TOTAL</td>
          <td class="border px-3 py-2 text-right">${fmt(tot.osc)}</td>
          <td class="border px-3 py-2 text-right">${fmt(totalL_WithRealisasi)}</td>
          <td class="border px-3 py-2 text-right">${fmt(tot.DP)}</td>
          <td class="border px-3 py-2 text-right">${fmt(tot.KL)}</td>
          <td class="border px-3 py-2 text-right">${fmt(tot.D)}</td>
          <td class="border px-3 py-2 text-right">${fmt(tot.M)}</td>
          <td class="border px-3 py-2 text-right">${fmt(Math.abs(tot.runOff))}</td> 
          <td class="border px-3 py-2 text-right">${fmt(tot.lunas)}</td>
          <td class="border px-3 py-2 text-right">${fmt(Math.abs(tot.runOffMurni))}</td>
        </tr>`;
      tbody.innerHTML = html;

      // 3. Render Charts
      // Chart Kiri (OSC NPL)
      chartOscNpl('chartOscNpl', totalApi.total_prev||0, totalApi.npl_prev||0, totalApi.total_now||0, totalApi.npl_now||0);

      // Hitung Growth & Badge
      const diffNpl = num(totalApi.npl_now||0) - num(totalApi.npl_prev||0);
      const diffEl  = document.getElementById('selisih_osc_npl');
      diffEl.textContent = (diffNpl>=0?'+':'') + fmt(diffNpl);
      diffEl.style.color = diffNpl>0 ? '#dc2626' : '#059669';

      const growth = realisasi - tot.runOff; 
      document.getElementById('runoff_total').textContent = fmt(Math.abs(tot.runOff)); 
      document.getElementById('growth_val').textContent   = (growth>=0?'+':'') + fmt(growth);
      
      const badge = document.getElementById('badgeGrowth');
      badge.textContent = `Growth: ${(growth>=0?'+':'')}${fmt(growth)}`;
      badge.className   = 'pill ' + (growth>=0 ? 'pill-ok':'pill-bad');

      // Ambil data untuk Chart Kanan (Flow)
      const flow_par       = num(totalApi.flow_par||0);
      const backflow_total = num(totalApi.backflow_total||0);
      const lunas_npl      = num(totalApi.lunas_npl||0);
      const angsuran_npl   = num(totalApi.angsuran_npl||0);

      // Simpan state untuk resize
      window.__lastTotalApi = {
        ...totalApi,
        realisasi_bulan_ini: realisasi,
        runoff_total: tot.runOff,
        flow_par: flow_par,
        backflow_total: backflow_total,
        lunas_npl: lunas_npl,
        angsuran_npl: angsuran_npl
      };

      // Chart Kanan
      chartChange('chartChange', realisasi, flow_par, tot.runOff, backflow_total, lunas_npl, angsuran_npl);

      // Tampilkan kontainer
      document.getElementById('summaryWrap').classList.remove('hidden');
      document.getElementById('chartsWrap').classList.remove('hidden');

    } catch(e) {
      if(e.name !== 'AbortError'){
        tbody.innerHTML=`<tr><td colspan="10" class="py-4 text-red-600">Gagal memuat data</td></tr>`;
      }
    } finally { 
      loading.classList.add('hidden'); 
    }
  }

  // --- Resize Handler (Agar chart responsif saat layar diputar/diubah) ---
  window.addEventListener('resize', ()=>{
    applyMobileSummaryLabels();
    const t = window.__lastTotalApi || {};
    if(Object.keys(t).length){
      chartOscNpl('chartOscNpl', t.total_prev||0, t.npl_prev||0, t.total_now||0, t.npl_now||0);
      chartChange('chartChange', t.realisasi_bulan_ini||0, t.flow_par||0, t.runoff_total||0, t.backflow_total||0, t.lunas_npl||0, t.angsuran_npl||0);
    }
  });
</script>