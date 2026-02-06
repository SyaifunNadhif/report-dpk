<style>
  :root { --primary: #2563eb; --bg: #f8fafc; --text: #334155; }
  body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text); overflow: hidden; }
  
  /* Input & Button */
  .inp { border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 0.4rem 0.75rem; font-size: 13px; background: #fff; width: 100%; height: 38px; cursor: pointer; }
  .inp:disabled { background-color: #f1f5f9; color: #64748b; font-weight: 600; cursor: not-allowed; border-color: #e2e8f0; }
  .lbl { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px; display: block; }
  
  /* DATEPICKER FIX */
  input[type="date"]::-webkit-inner-spin-button,
  input[type="date"]::-webkit-calendar-picker-indicator { display: none; -webkit-appearance: none; }
  input[type="date"] { -moz-appearance: textfield; }

  .btn-icon { width: 38px; height: 38px; border-radius: 8px; background: var(--primary); color: white; border: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: 0.2s; }
  .btn-icon:hover { background: #1d4ed8; }

  /* Table Wrapper */
  .table-wrapper { 
      overflow: auto; 
      height: 100%; 
      border-radius: 8px; 
      border: 1px solid #e2e8f0; 
      background: white; 
      position: relative;
      padding-bottom: 0; /* Reset padding */
  }
  
  table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 12px; }
  
  /* Header Sticky */
  th { position: sticky; top: 0; z-index: 40; background: #d9ead3; color: #1e293b; font-weight: 700; padding: 10px; border-bottom: 1px solid #cbd5e1; text-transform: uppercase; }
  td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle; }
  
  /* Sticky Kolom Kiri */
  .sticky-col { position: sticky; left: 0; z-index: 30; background: white; border-right: 1px solid #e2e8f0; }
  th.sticky-col { z-index: 50; background: #d9ead3; }
  
  /* FOOTER STICKY (Floating Grand Total) */
  tfoot td { 
      position: sticky; 
      bottom: 0; 
      z-index: 60; 
      background: #eff6ff; 
      font-weight: 700; 
      border-top: 2px solid #60a5fa; /* Border lebih tegas */
      color: #1e3a8a;
      box-shadow: 0 -4px 10px -2px rgba(0,0,0,0.15); /* Shadow ke atas */
      padding-top: 15px; 
      padding-bottom: 15px;
  }
  
  /* Merged Cell Sticky Kiri di Footer */
  tfoot td.merged-total {
      position: sticky; left: 0; z-index: 65;
      text-align: center; border-right: 1px solid #93c5fd; background: #eff6ff; 
  }

  tr:hover td { background-color: #f8fafc; }
  .hidden { display: none !important; }
</style>

<script>
    // --- SIMULASI USER LOGIN ---
    // Ganti ini sesuai sistem loginmu nanti
    const user = (window.getUser && window.getUser()) || null;
    const userKode = (user?.kode ? String(user.kode).padStart(3,'0') : '000'); 
    window.currentUser = { kode: userKode };
    console.log("Login User:", userKode);
</script>

<div class="max-w-7xl mx-auto px-4 py-4 h-[calc(100vh-120px)] flex flex-col">
  
  <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 mb-3">
    <div>
      <h1 class="text-2xl font-bold flex items-center gap-2 text-slate-800">
        <span class="bg-blue-600 text-white p-1 rounded">💳</span> 
        <span>Rekap NPL</span>
      </h1>
      <p class="text-xs text-slate-500 mt-1">*Data NPL = Kolektibilitas Macet (KL, D, M)</p>
    </div>

    <form id="formFilterNpl" class="flex flex-wrap items-end gap-2 bg-white p-2 rounded-lg border border-slate-200 shadow-sm">
      <div style="width: 110px;">
        <label class="lbl">Closing</label>
        <input type="date" id="closing_date_npl" class="inp" required onclick="try{this.showPicker()}catch(e){}">
      </div>
      <div style="width: 110px;">
        <label class="lbl">Harian</label>
        <input type="date" id="harian_date_npl" class="inp" required onclick="try{this.showPicker()}catch(e){}">
      </div>
      <div style="min-width: 180px;">
        <label class="lbl">Kantor</label>
        <select id="opt_kantor_npl" class="inp"><option value="">Memuat...</option></select>
      </div>
      <button type="submit" class="btn-icon" title="Cari Data">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
      </button>
    </form>
  </div>

  <div class="flex-1 min-h-0 relative flex flex-col">
    <div id="loadingNpl" class="hidden absolute inset-0 bg-white/80 z-50 flex flex-col items-center justify-center text-blue-600 font-bold text-sm">
        <div class="animate-spin h-8 w-8 border-4 border-blue-200 border-t-blue-600 rounded-full mb-2"></div>
        Memuat Data...
    </div>

    <div class="table-wrapper">
      <table id="tabelNpl">
        <thead>
          <tr>
            <th class="sticky-col text-center w-[60px]">Kode</th>
            <th class="sticky-col" style="left:60px; min-width:180px;" id="thNamaNpl">NAMA KANTOR</th>
            <th class="text-right">NPL Closing</th>
            <th class="text-right">NPL Harian</th>
            <th class="text-right">Selisih</th>
            <th class="text-right">% Closing</th>
            <th class="text-right">% Harian</th>
            <th class="text-right">% Selisih</th>
          </tr>
        </thead>
        <tbody id="bodyNpl"></tbody>
        <tfoot id="footNpl"></tfoot>
      </table>
    </div>
  </div>

</div>

<script>
  // --- CONFIG ---
  const API_NPL  = './api/npl/'; 
  const API_KODE = './api/kode/';
  const API_DATE = './api/date/';
  const nf = new Intl.NumberFormat('id-ID');
  const fmt = n => nf.format(Number(n)||0);
  const fmt2 = x => (x == null || x === '' ? '0.00' : Number(x).toFixed(2));

  // Helper
  async function apiCall(url, options = {}) {
      const res = await fetch(url, options);
      if(!res.ok) throw new Error(`HTTP Error ${res.status}`);
      return res;
  }

  // --- INIT ---
  window.addEventListener('DOMContentLoaded', async () => {
      // Ambil kode user yang login
      const uKode = window.currentUser.kode;
      
      // Load Dropdown dulu (Sesuai User)
      await populateKantorOptionsNpl(uKode);

      // Load Tanggal Default
      const d = await getLastHarianData(); 
      if (d) {
          document.getElementById('closing_date_npl').value = d.last_closing;
          document.getElementById('harian_date_npl').value  = d.last_created;
      } else {
          document.getElementById('harian_date_npl').value = new Date().toISOString().split('T')[0];
      }

      // Fetch Data
      fetchNplData();
  });

  async function getLastHarianData(){
    try { const r = await apiCall(API_DATE); const j = await r.json(); return j.data || null; } catch{ return null; }
  }

  // --- POPULATE DROPDOWN (LOGIC USER LOGIN & LOCK) ---
  async function populateKantorOptionsNpl(userKode){
    const optKantor = document.getElementById('opt_kantor_npl');
    
    try {
        // Ambil Data Kode Kantor dari API
        const res = await apiCall(API_KODE, { 
            method:'POST', 
            headers:{'Content-Type':'application/json'}, 
            body:JSON.stringify({type:'kode_kantor'}) 
        });
        const json = await res.json();
        const list = Array.isArray(json.data) ? json.data : [];

        let html = '';

        // KONDISI 1: JIKA USER PUSAT (000) -> LOAD SEMUA
        if(userKode === '000'){
            html += `<option value="">KONSOLIDASI (SEMUA)</option>`;
            list.filter(x => x.kode_kantor && x.kode_kantor !== '000')
                .sort((a,b) => String(a.kode_kantor).localeCompare(b.kode_kantor))
                .forEach(it => {
                   html += `<option value="${String(it.kode_kantor).padStart(3,'0')}">${String(it.kode_kantor).padStart(3,'0')} - ${it.nama_kantor}</option>`;
                });
            
            optKantor.innerHTML = html;
            optKantor.disabled = false; // Buka akses
        } 
        // KONDISI 2: JIKA USER CABANG (MISAL 002) -> LOCK
        else {
            // Cari nama cabang user tersebut
            const myBranch = list.find(k => k.kode_kantor === userKode);
            const branchName = myBranch ? myBranch.nama_kantor : `CABANG ${userKode}`;
            
            // Set opsi tunggal
            html = `<option value="${userKode}" selected>${userKode} - ${branchName}</option>`;
            optKantor.innerHTML = html;
            optKantor.value = userKode; // Paksa value
            optKantor.disabled = true;  // Kunci dropdown
        }

    } catch(e){
        console.error("Gagal load kantor:", e);
        // Fallback aman jika API error
        optKantor.innerHTML = `<option value="${userKode}">${userKode}</option>`;
        optKantor.value = userKode;
        if(userKode !== '000') optKantor.disabled = true;
    }
  }

  // --- FETCH DATA ---
  document.getElementById('formFilterNpl').addEventListener('submit', e => { e.preventDefault(); fetchNplData(); });

  async function fetchNplData() {
      const loading = document.getElementById('loadingNpl');
      const tbody = document.getElementById('bodyNpl');
      const tfoot = document.getElementById('footNpl');
      
      const closing = document.getElementById('closing_date_npl').value;
      const harian  = document.getElementById('harian_date_npl').value;
      const kantor  = document.getElementById('opt_kantor_npl').value;

      document.getElementById('thNamaNpl').innerText = (kantor && kantor !== '') ? "NAMA KANKAS" : "NAMA KANTOR";

      loading.classList.remove('hidden');
      tbody.innerHTML = ''; tfoot.innerHTML = '';

      try {
          const payload = { type: 'NPL', closing_date: closing, harian_date: harian, kode_kantor: kantor };
          const res = await fetch(API_NPL, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          const json = await res.json();
          if(json.status && json.status !== 200) throw new Error(json.message);

          const rows = json.data?.data || [];
          const gt   = json.data?.grand_total || { npl_closing:0, npl_harian:0, selisih_npl:0, npl_closing_persen:0, npl_harian_persen:0, selisih_npl_persen:0 };

          if (rows.length === 0) {
              tbody.innerHTML = `<tr><td colspan="8" class="text-center py-10 text-slate-400">Data Kosong</td></tr>`;
              return;
          }

          let html = '';
          rows.forEach(r => {
              const n = styleVal(r.selisih_npl);
              const p = stylePct(r.selisih_npl_persen);
              
              html += `
                <tr class="hover:bg-blue-50 transition border-b">
                    <td class="sticky-col text-center font-mono font-bold text-slate-500">${r.kode_unit}</td>
                    <td class="sticky-col font-semibold text-slate-700 text-xs" style="left:60px;">${r.nama_unit}</td>
                    <td class="text-right">${fmt(r.npl_closing)}</td>
                    <td class="text-right font-bold text-blue-800">${fmt(r.npl_harian)}</td>
                    <td class="text-right ${n.cls}">${n.txt}</td>
                    <td class="text-right text-slate-600">${fmt2(r.npl_closing_persen)}%</td>
                    <td class="text-right font-bold text-blue-800">${fmt2(r.npl_harian_persen)}%</td>
                    <td class="text-right ${p.cls}">${p.txt}</td>
                </tr>
              `;
          });
          
          // === SPACER ROW (Agar Data Bawah Tidak Ketutup Footer Sticky) ===
          html += `<tr style="height: 60px;"><td colspan="8" class="border-none bg-transparent"></td></tr>`;
          
          tbody.innerHTML = html;

          // === FOOTER STICKY ===
          const gtVal = styleVal(gt.selisih_npl);
          const gtPct = stylePct(gt.selisih_npl_persen);

          tfoot.innerHTML = `
            <tr>
                <td colspan="2" class="merged-total text-center uppercase tracking-wide">GRAND TOTAL</td>
                <td class="text-right font-bold">${fmt(gt.npl_closing)}</td>
                <td class="text-right font-bold bg-blue-100 text-blue-900">${fmt(gt.npl_harian)}</td>
                <td class="text-right font-bold ${gtVal.cls}">${gtVal.txt}</td>
                <td class="text-right font-bold">${fmt2(gt.npl_closing_persen)}%</td>
                <td class="text-right font-bold bg-blue-100 text-blue-900">${fmt2(gt.npl_harian_persen)}%</td>
                <td class="text-right font-bold ${gtPct.cls}">${gtPct.txt}</td>
            </tr>
          `;

      } catch(e) {
          console.error(e);
          tbody.innerHTML = `<tr><td colspan="8" class="text-center py-10 text-red-500">Error: ${e.message}</td></tr>`;
      } finally {
          loading.classList.add('hidden');
      }
  }

  // --- Helper ---
  function styleVal(v) {
      const n = Number(v||0);
      if(n < 0) return { txt: `(${fmt(Math.abs(n))})`, cls:'text-green-600 font-bold' };
      if(n > 0) return { txt: `+${fmt(n)}`, cls:'text-red-600 font-bold' };
      return { txt: '-', cls:'text-slate-400' };
  }
  function stylePct(v) {
      const n = Number(v||0);
      if(n < 0) return { txt: `▼ ${fmt2(Math.abs(n))}%`, cls:'text-green-600 font-bold' };
      if(n > 0) return { txt: `▲ ${fmt2(n)}%`, cls:'text-red-600 font-bold' };
      return { txt: '0.00%', cls:'text-slate-400' };
  }
</script>