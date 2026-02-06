<style>
  /* Base Setup */
  :root { --primary: #2563eb; --bg: #f8fafc; --text: #334155; }
  .pipe-container { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; color: var(--text); padding: 20px; background-color: var(--bg); min-height: 100vh; display: flex; flex-direction: column; }
  
  /* Inputs & Buttons */
  .inp { border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 10px; height: 36px; font-size: 13px; width: 100%; outline: none; background: white; transition: 0.2s; color: #1e293b; }
  .inp:focus { border-color: var(--primary); box-shadow: 0 0 0 2px rgba(37,99,235,0.1); }
  .inp:disabled { background-color: #f1f5f9; color: #64748b; cursor: not-allowed; font-weight: 600; border-color: #e2e8f0; }
  .lbl { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px; display: block; }
  
  .btn { height: 36px; padding: 0 16px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; justify-content: center; gap: 6px; color: white; transition: 0.2s; }
  .btn-primary { background: var(--primary); } .btn-primary:hover { background: #1d4ed8; }
  .btn-success { background: #16a34a; } .btn-success:hover { background: #15803d; }
  
  /* Pills Summary */
  .pill-group { display: flex; flex-wrap: wrap; gap: 8px; }
  .pill { display: flex; flex-direction: column; padding: 8px 14px; border-radius: 8px; border: 1px solid; background: white; min-width: 130px; box-shadow: 0 1px 2px rgba(0,0,0,0.03); flex: 1; }
  .pill-label { font-size: 10px; font-weight: 700; text-transform: uppercase; opacity: 0.8; margin-bottom: 2px; }
  .pill-val { font-size: 16px; font-weight: 800; color: #1e293b; line-height: 1.2; }
  .pill-nom { font-size: 11px; font-family: monospace; font-weight: 600; color: #64748b; }
  
  /* Table Layout */
  .card { background: white; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); overflow: hidden; display: flex; flex-direction: column; flex: 1; position: relative; }
  .table-wrapper { overflow: auto; height: 100%; width: 100%; }
  table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 12px; text-align: left; }
  th { background: #f8fafc; color: #475569; font-weight: 700; padding: 10px 12px; border-bottom: 1px solid #e2e8f0; white-space: nowrap; position: sticky; top: 0; z-index: 20; text-transform: uppercase; font-size: 10px; }
  td { padding: 8px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; color: #334155; }
  
  /* Sticky Columns */
  .sticky-col { position: sticky; left: 0; z-index: 15; background: white; border-right: 1px solid #f1f5f9; }
  th.sticky-col { z-index: 30; background: #f8fafc; border-right: 1px solid #e2e8f0; }
  tbody tr:hover td { background-color: #eff6ff !important; cursor: pointer; }

  /* Helpers */
  .hidden { display: none !important; }
  .sub-val { display: block; font-size: 10px; margin-top: 2px; font-family: monospace; font-weight: 500; color: #64748b; }
  .badge { padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: uppercase; }

  /* Modal */
  .modal-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); z-index: 9999; display: none; align-items: center; justify-content: center; backdrop-filter: blur(2px); }
  .modal-active { display: flex; }
  .modal-box { background: white; width: 95%; max-width: 1400px; max-height: 90vh; border-radius: 12px; display: flex; flex-direction: column; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); }
</style>

<script>
    // --- AREA KONFIGURASI USER LOGIN ---
    // Di aplikasi asli (PHP), biasanya data user dilempar ke JS.
    // Jika tidak ada data user, kita default ke '000' (Admin Pusat).
    // Ganti logika ini sesuai session PHP kamu.
    
    window.currentUser = {
        // Contoh: Ambil dari variable global JS jika ada, atau default 000
        kode_kantor: (typeof USER_KODE_KANTOR !== 'undefined') ? USER_KODE_KANTOR : '000' 
    };
    
    // Debug: Cek di console browser
    console.log("Login sebagai Kantor:", window.currentUser.kode_kantor);
</script>

<div class="pipe-container">
  
  <div class="mb-4 flex flex-col xl:flex-row xl:items-end justify-between gap-4">
    <div style="flex: 1;">
      <h1 class="text-2xl font-bold flex items-center gap-2 mb-3" style="color:#1e293b;">
        <span style="background:#2563eb; color:white; padding:4px 8px; border-radius:6px;">📊</span>
        <span>Rekap Jatuh Tempo & Top Up</span>
      </h1>
      
      <div id="summaryPills" class="pill-group hidden">
        <div class="pill" style="border-color:#bfdbfe; background:#eff6ff; color:#1e40af;">
            <span class="pill-label">Target JT</span>
            <span class="pill-val" id="sum_target">0</span>
            <span class="pill-nom" id="sum_target_nom">Rp 0</span>
        </div>
        <div class="pill" style="border-color:#bbf7d0; background:#f0fdf4; color:#166534;">
            <span class="pill-label">Sudah Ambil</span>
            <span class="pill-val" id="sum_sudah">0</span>
            <span class="pill-nom" id="sum_sudah_nom">Rp 0</span>
        </div>
        <div class="pill" style="border-color:#e9d5ff; background:#faf5ff; color:#6b21a8;">
            <span class="pill-label">Potensi (Siap)</span>
            <span class="pill-val" id="sum_potensi">0</span>
            <span class="pill-nom" id="sum_potensi_nom">Rp 0</span>
        </div>
        <div class="pill" style="border-color:#fecaca; background:#fef2f2; color:#991b1b;">
            <span class="pill-label">Drop (Macet)</span>
            <span class="pill-val" id="sum_drop">0</span>
            <span class="pill-nom" id="sum_drop_nom">Rp 0</span>
        </div>
      </div>
    </div>

    <form id="formFilter" class="card" style="flex-direction:row; flex-wrap:wrap; align-items:flex-end; gap:10px; padding:12px; min-width:200px; flex:none;">
      <div style="width: 110px;">
        <label class="lbl">Closing (M-1)</label>
        <input type="date" id="closing_date" class="inp" disabled title="Terkunci: Akhir Tahun Lalu">
      </div>
      <div style="width: 110px;">
        <label class="lbl">Actual</label>
        <input type="date" id="harian_date" class="inp" readonly>
      </div>
      <div style="width: 70px;">
        <label class="lbl">Tahun</label>
        <input type="number" id="tahun_jt" class="inp" value="2026">
      </div>
      <div style="flex-grow: 1; min-width: 180px;">
        <label class="lbl">Kantor Cabang</label>
        <select id="opt_kantor" class="inp">
            <option value="">Memuat data...</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Tampilkan</button>
    </form>
  </div>

  <div class="card">
    <div id="loadingRekap" class="hidden" style="position:absolute; inset:0; background:rgba(255,255,255,0.8); z-index:50; display:flex; align-items:center; justify-content:center; flex-direction:column; font-weight:600; color:#2563eb;">
        <div style="width:32px; height:32px; border:4px solid #bfdbfe; border-top:4px solid #2563eb; border-radius:50%; animation:spin 1s linear infinite;"></div>
        <span style="margin-top:8px;">Memuat Data...</span>
    </div>
    
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th class="sticky-col text-center w-[50px]">Kode</th>
            <th class="sticky-col" style="left:50px; z-index:31; width:140px;">Nama Kantor</th>
            
            <th class="text-right">Target JT<br><span class="sub-val" style="font-weight:400;">NOA | Plafon</span></th>
            <th class="text-center bg-green-50 text-green-800 border-l border-green-200">Sudah Ambil<br><span class="sub-val text-green-600">NOA | Nominal</span></th>
            <th class="text-center bg-blue-50 text-blue-800 border-l border-blue-200">Lunas<br><span class="sub-val text-blue-600">NOA | Plafon</span></th>
            <th class="text-center bg-purple-50 text-purple-800">Top Up<br><span class="sub-val text-purple-600">NOA | Sisa OS</span></th>
            <th class="text-center bg-orange-50 text-orange-800">Retensi<br><span class="sub-val text-orange-600">NOA | Sisa OS</span></th>
            <th class="text-center bg-red-50 text-red-800 border-l border-red-200">Drop<br><span class="sub-val text-red-600">NOA | Sisa OS</span></th>
          </tr>
        </thead>
        <tbody id="bodyRekap"></tbody>
        <tfoot id="footRekap" style="background:#f8fafc; font-weight:700; border-top:2px solid #e2e8f0;"></tfoot>
      </table>
    </div>
  </div>

</div>

<div id="modalDetail" class="modal-overlay" onclick="closeModal()">
  <div class="modal-box" onclick="event.stopPropagation()">
    <div style="padding:16px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
      <div>
        <h3 style="font-size:18px; font-weight:700; color:#1e293b;">Detail Nasabah</h3>
        <p style="font-size:12px; color:#64748b;" id="detailSubTitle">...</p>
      </div>
      <div style="display:flex; gap:8px;">
        <select id="filter_status_modal" class="inp" style="width:130px;" onchange="changeFilter()">
            <option value="">Semua Status</option>
            <option value="sudah">✅ Sudah Ambil</option>
            <option value="lunas">🔵 Lunas</option>
            <option value="topup">🟣 Top Up</option>
            <option value="retensi">🟠 Retensi</option>
            <option value="drop">⛔ Drop</option>
        </select>
        <select id="filter_ao_modal" class="inp" style="width:130px;" onchange="changeFilter()">
            <option value="">Semua AO</option>
        </select>
        <button onclick="downloadExcel()" class="btn btn-success">Excel</button>
        <button onclick="closeModal()" style="font-size:24px; color:#94a3b8; background:none; border:none; margin-left:4px;">&times;</button>
      </div>
    </div>

    <div id="modalStats" style="background:#f8fafc; padding:10px 16px; border-bottom:1px solid #e2e8f0; font-size:11px; font-weight:600; color:#475569; display:flex; gap:16px; overflow-x:auto; white-space:nowrap;"></div>

    <div style="flex:1; overflow:auto; position:relative;">
      <div id="loadingDetail" class="hidden" style="position:absolute; inset:0; background:rgba(255,255,255,0.9); z-index:20; display:flex; align-items:center; justify-content:center; color:#2563eb; font-weight:bold;">Loading...</div>
      <table style="width:100%;">
        <thead style="position:sticky; top:0; z-index:10; background:white; box-shadow:0 1px 2px rgba(0,0,0,0.05);">
          <tr>
            <th class="border-b w-[100px]">Rekening</th>
            <th class="border-b min-w-[180px]">Nasabah</th>
            <th class="border-b text-blue-700">AO</th>
            <th class="border-b text-right">Plafon Awal</th>
            <th class="border-b text-center">JT</th>
            <th class="border-b text-right text-blue-700">Sisa OS</th>
            <th class="border-b text-center">Status</th>
            <th class="border-b text-right bg-green-50 text-green-800 border-l border-green-100">Baru</th>
            <th class="border-b text-center">Aksi</th>
          </tr>
        </thead>
        <tbody id="bodyDetail"></tbody>
      </table>
    </div>

    <div style="padding:12px 16px; border-top:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
      <span id="pageInfo" style="font-size:12px; font-weight:600; color:#64748b;">0 Data</span>
      <div style="display:flex; gap:8px;">
        <button id="btnPrev" onclick="changePage(-1)" class="btn" style="background:white; border:1px solid #cbd5e1; color:#334155;">Prev</button>
        <button id="btnNext" onclick="changePage(1)" class="btn" style="background:white; border:1px solid #cbd5e1; color:#334155;">Next</button>
      </div>
    </div>
  </div>
</div>

<style>@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>

<script>
  // --- CONFIG ---
  const API_URL  = './api/pipelane/'; 
  const API_KODE = './api/kode/'; 
  const API_DATE = './api/date/';
  const nf = new Intl.NumberFormat('id-ID');
  const fmt = n => nf.format(Number(n||0));

  let state = { cabang:'', ao:'', status:'', page:1, limit:10, totalPages:1 };

  // --- INIT ---
  window.addEventListener('DOMContentLoaded', async () => {
      const now = new Date();
      // 1. Closing Date (Tahun Lalu - Locked)
      const lastYear = now.getFullYear() - 1;
      document.getElementById('closing_date').value = `${lastYear}-12-31`;
      
      // 2. Fetch Actual Date
      try {
          const r = await fetch(API_DATE);
          const j = await r.json();
          document.getElementById('harian_date').value = (j && j.data && j.data.last_created) ? j.data.last_created : now.toISOString().split('T')[0];
      } catch(e) { 
          document.getElementById('harian_date').value = now.toISOString().split('T')[0];
      }

      // 3. Load Kantor sesuai Login & 4. Load Data
      await populateKantor();
      fetchRekap();
  });

  async function apiCall(url, payload) {
      // Helper untuk fetch
      const res = await fetch(url, { 
          method: 'POST', 
          headers: {'Content-Type':'application/json'}, 
          body: JSON.stringify(payload) 
      });
      return await res.json();
  }

  // --- POPULATE KANTOR (LOGIC LOGIN & LOCK) ---
  async function populateKantor() {
      // AMBIL USER DARI WINDOW (Global Context)
      const user = (window.getUser && window.getUser()) || null;
      const userKode = (user?.kode ? String(user.kode).padStart(3,'0') : '000'); // Default 000 jika null

      try {
          const res = await apiCall(API_KODE, { type: 'kode_kantor' });
          const sel = document.getElementById('opt_kantor');
          
          let h = '';

          // KONDISI 1: PUSAT (000) -> BISA PILIH SEMUA
          if (userKode === '000') {
              h += '<option value="">SEMUA CABANG</option>';
              if(res.data) {
                  res.data.filter(x => x.kode_kantor !== '000').forEach(x => {
                      h += `<option value="${x.kode_kantor}">${x.kode_kantor} - ${x.nama_kantor}</option>`;
                  });
              }
              sel.innerHTML = h;
              sel.disabled = false; // Enable
          } 
          // KONDISI 2: CABANG -> KUNCI
          else {
              const myBranch = res.data ? res.data.find(k => k.kode_kantor === userKode) : null;
              const branchName = myBranch ? myBranch.nama_kantor : `CABANG ${userKode}`;
              
              // Set opsi tunggal
              h = `<option value="${userKode}" selected>${userKode} - ${branchName}</option>`;
              sel.innerHTML = h;
              sel.value = userKode; // Paksa Value
              sel.disabled = true;  // Disable
          }
      } catch(e) { 
          console.error("Gagal load kantor", e);
          // Fallback jika API gagal
          document.getElementById('opt_kantor').innerHTML = `<option value="${userKode}">${userKode}</option>`;
      }
  }

  document.getElementById('formFilter').addEventListener('submit', e => { e.preventDefault(); fetchRekap(); });

  // --- FETCH REKAP ---
  async function fetchRekap() {
      const l = document.getElementById('loadingRekap');
      const tb = document.getElementById('bodyRekap');
      const tf = document.getElementById('footRekap');
      const pills = document.getElementById('summaryPills');
      
      l.classList.remove('hidden'); pills.classList.add('hidden');
      tb.innerHTML = ''; tf.innerHTML = '';

      // Tentukan cabang request: Ambil value dari dropdown (entah itu enabled atau disabled/locked)
      const reqCabang = document.getElementById('opt_kantor').value;

      try {
          const payload = {
              type: 'rekap_pipeline',
              closing_date: document.getElementById('closing_date').value,
              harian_date: document.getElementById('harian_date').value,
              tahun_jt: document.getElementById('tahun_jt').value,
              kode_kantor: reqCabang // Kirim kode (kosong = semua)
          };

          const json = await apiCall(API_URL, payload);
          const rows = json.data || [];

          if(rows.length === 0) {
              tb.innerHTML = `<tr><td colspan="8" class="text-center py-10 text-gray-400 italic">Data tidak ditemukan.</td></tr>`;
              return;
          }

          let T = { 
              tgt_noa:0, tgt_nom:0, 
              sdh_noa:0, sdh_nom:0, 
              pot_noa:0, pot_nom:0,
              drop_noa:0, drop_nom:0,
              lun_noa:0, lun_nom:0, top_noa:0, top_nom:0, ret_noa:0, ret_nom:0
          };

          let html = '';
          rows.forEach(r => {
              // Accumulate
              T.tgt_noa += +r.noa_target; T.tgt_nom += +r.plafon_closing;
              T.sdh_noa += +r.noa_sudah;  T.sdh_nom += +r.nominal_sudah;
              T.lun_noa += +r.noa_lunas;  T.lun_nom += +r.nominal_lunas;
              T.top_noa += +r.noa_topup;  T.top_nom += +r.os_topup;
              T.ret_noa += +r.noa_retensi;T.ret_nom += +r.os_retensi;
              T.drop_noa += +r.noa_drop;  T.drop_nom += +r.os_drop;

              const namaK = r.nama_kantor || r.kode_cabang;

              html += `
                <tr class="cell-hover bg-white hover:bg-blue-50 transition border-b group" onclick="openModal('${r.kode_cabang}', '${namaK}')">
                    <td class="px-3 py-2 border-r font-mono text-gray-500 text-center sticky left-0 bg-white group-hover:bg-blue-50 z-20">${r.kode_cabang}</td>
                    <td class="px-3 py-2 border-r font-medium text-gray-700 sticky left-[60px] bg-white group-hover:bg-blue-50 z-20 truncate max-w-[160px]" title="${namaK}">${namaK}</td>
                    
                    <td class="px-3 py-2 border-r text-right">
                        <div class="font-bold text-gray-800">${fmt(r.noa_target)}</div>
                        <div class="text-[10px] text-gray-500 font-mono">${fmt(r.plafon_closing)}</div>
                    </td>
                    
                    <td class="px-3 py-2 border-r text-center bg-green-50/50 text-green-700 border-l border-green-100">
                        <div class="font-bold">${fmt(r.noa_sudah)}</div>
                        <div class="text-[10px] text-green-600 font-mono font-bold">${fmt(r.nominal_sudah)}</div>
                    </td>
                    
                    <td class="px-3 py-2 border-r text-center bg-blue-50/50 text-blue-700 border-l border-blue-100">
                        <div class="font-bold">${fmt(r.noa_lunas)}</div>
                        <div class="text-[10px] text-blue-600 font-mono">${fmt(r.nominal_lunas)}</div>
                    </td>
                    <td class="px-3 py-2 border-r text-center bg-purple-50/50 text-purple-700">
                        <div class="font-bold">${fmt(r.noa_topup)}</div>
                        <div class="text-[10px] text-purple-600 font-mono">${fmt(r.os_topup)}</div>
                    </td>
                    <td class="px-3 py-2 border-r text-center bg-orange-50/50 text-orange-700">
                        <div class="font-bold">${fmt(r.noa_retensi)}</div>
                        <div class="text-[10px] text-orange-600 font-mono">${fmt(r.os_retensi)}</div>
                    </td>
                    
                    <td class="px-3 py-2 border-r text-center bg-red-50/50 text-red-700 border-l border-red-100">
                        <div class="font-bold">${fmt(r.noa_drop)}</div>
                        <div class="text-[10px] text-red-600 font-mono">${fmt(r.os_drop)}</div>
                    </td>
                </tr>
              `;
          });
          tb.innerHTML = html;

          // Footer
          tf.innerHTML = `
            <tr class="bg-gray-50">
                <td class="px-3 py-3 border-r sticky left-0 bg-gray-100 z-30 text-center border-t-2 border-gray-300" colspan="2">GRAND TOTAL</td>
                <td class="px-3 py-3 border-r text-right border-t-2 border-gray-300"><div>${fmt(T.tgt_noa)}</div><div class="text-[10px] font-mono">${fmt(T.tgt_nom)}</div></td>
                
                <td class="px-3 py-3 border-r text-center bg-green-100 text-green-900 border-t-2 border-green-300">
                    <div>${fmt(T.sdh_noa)}</div><div class="text-[10px] font-mono font-bold">${fmt(T.sdh_nom)}</div>
                </td>
                
                <td class="px-3 py-3 border-r text-center bg-blue-100 text-blue-900 border-t-2 border-blue-300">
                    <div>${fmt(T.lun_noa)}</div><div class="text-[10px] font-mono font-bold">${fmt(T.lun_nom)}</div>
                </td>
                <td class="px-3 py-3 border-r text-center bg-purple-100 text-purple-900 border-t-2 border-purple-300">
                    <div>${fmt(T.top_noa)}</div><div class="text-[10px] font-mono font-bold">${fmt(T.top_nom)}</div>
                </td>
                <td class="px-3 py-3 border-r text-center bg-orange-100 text-orange-900 border-t-2 border-orange-300">
                    <div>${fmt(T.ret_noa)}</div><div class="text-[10px] font-mono font-bold">${fmt(T.ret_nom)}</div>
                </td>
                
                <td class="px-3 py-3 border-r text-center bg-red-100 text-red-900 border-t-2 border-red-300">
                    <div>${fmt(T.drop_noa)}</div><div class="text-[10px] font-mono font-bold">${fmt(T.drop_nom)}</div>
                </td>
            </tr>
          `;

          // Hitung Total Potensi
          T.pot_noa = T.lun_noa + T.top_noa + T.ret_noa;
          T.pot_nom = T.lun_nom + T.top_nom + T.ret_nom;

          // Update Pills
          document.getElementById('sum_target').innerText = fmt(T.tgt_noa);
          document.getElementById('sum_target_nom').innerText = 'Rp ' + fmt(T.tgt_nom);
          
          document.getElementById('sum_sudah').innerText = fmt(T.sdh_noa);
          document.getElementById('sum_sudah_nom').innerText = 'Rp ' + fmt(T.sdh_nom);
          
          document.getElementById('sum_potensi').innerText = fmt(T.pot_noa);
          document.getElementById('sum_potensi_nom').innerText = 'Rp ' + fmt(T.pot_nom);
          
          document.getElementById('sum_drop').innerText = fmt(T.drop_noa);
          document.getElementById('sum_drop_nom').innerText = 'Rp ' + fmt(T.drop_nom);

          pills.classList.remove('hidden');

      } catch(e) { console.error(e); } finally { l.classList.add('hidden'); }
  }

  // --- DETAIL MODAL ---
  function openModal(cabang, nama) {
      state.cabang = cabang; state.ao = ''; state.status = ''; state.page = 1;
      document.getElementById('modalDetail').classList.add('modal-active');
      document.getElementById('detailSubTitle').innerText = `${nama} | JT ${document.getElementById('tahun_jt').value}`;
      document.getElementById('filter_ao_modal').innerHTML = '<option value="">Semua AO</option>';
      fetchDetail();
  }

  function changeFilter() {
      state.status = document.getElementById('filter_status_modal').value;
      state.ao = document.getElementById('filter_ao_modal').value;
      state.page = 1;
      fetchDetail();
  }

  function changePage(step) {
      const next = state.page + step;
      if(next > 0 && next <= state.totalPages) { state.page = next; fetchDetail(); }
  }

  async function fetchDetail() {
      const l=document.getElementById('loadingDetail'), tb=document.getElementById('bodyDetail');
      l.classList.remove('hidden'); tb.innerHTML='';

      try {
          const payload = {
              type: 'detail_pipeline',
              closing_date: document.getElementById('closing_date').value,
              harian_date: document.getElementById('harian_date').value,
              tahun_jt: document.getElementById('tahun_jt').value,
              kode_kantor: state.cabang,
              kode_ao: state.ao,
              filter_status: state.status,
              page: state.page,
              limit: state.limit
          };

          const json = await apiCall(API_URL, payload);
          const rows = json.data?.data || [];
          const stats = json.data?.stats || {};
          const aoList = json.data?.list_ao || [];

          // Update Stats Bar di Modal
          document.getElementById('modalStats').innerHTML = `
             <div class="flex gap-6">
                 <div>Total: <span class="font-bold text-gray-800">${fmt(stats.total_data)}</span></div>
                 <div class="text-green-700">Sudah: <span class="font-bold">${fmt(stats.cnt_sudah)}</span></div>
                 <div class="text-blue-700">Lunas: <span class="font-bold">${fmt(stats.cnt_lunas)}</span></div>
                 <div class="text-purple-700">TopUp: <span class="font-bold">${fmt(stats.cnt_topup)}</span></div>
                 <div class="text-orange-700">Retensi: <span class="font-bold">${fmt(stats.cnt_retensi)}</span></div>
                 <div class="text-red-700">Drop: <span class="font-bold">${fmt(stats.cnt_drop)}</span></div>
             </div>
          `;

          // Isi Filter AO jika belum ada
          const selAO = document.getElementById('filter_ao_modal');
          if(selAO.options.length === 1 && aoList.length > 0) {
              aoList.forEach(ao => {
                  let opt = document.createElement('option'); opt.value = ao.kode_group2; opt.text = ao.nama_ao; selAO.add(opt);
              });
              selAO.value = state.ao;
          }

          if(rows.length === 0) {
              tb.innerHTML = `<tr><td colspan="9" class="text-center py-10 text-gray-400 italic">Tidak ada data detail.</td></tr>`;
              document.getElementById('pageInfo').innerText = '0 Data';
              return;
          }

          state.totalPages = json.data?.pagination?.total_pages || 1;
          document.getElementById('pageInfo').innerText = `Hal ${state.page} / ${state.totalPages}`;

          let html = '';
          rows.forEach(r => {
              const aoName = (r.nama_ao || '-').split(' ')[0];
              const nomBaru = r.plafon_baru > 0 
                  ? `<div class="font-bold text-green-700 text-[11px]">${fmt(r.plafon_baru)}</div><div class="text-[9px] text-green-600">${r.tgl_baru}</div>` 
                  : '-';
              
              // Badge Styles (Tailwind)
              let badgeClass = "bg-gray-100 text-gray-600";
              if(r.status_ket.includes("SUDAH")) badgeClass = "bg-green-100 text-green-800";
              if(r.status_ket.includes("LUNAS")) badgeClass = "bg-blue-100 text-blue-800";
              if(r.status_ket.includes("TOP UP")) badgeClass = "bg-purple-100 text-purple-800";
              if(r.status_ket.includes("RETENSI")) badgeClass = "bg-orange-100 text-orange-800";
              if(r.status_ket.includes("DROP")) badgeClass = "bg-red-100 text-red-800";

              const btn = r.enable 
                  ? `<button class="bg-blue-600 hover:bg-blue-700 text-white h-[24px] px-2 rounded text-[10px] shadow-sm">PROSPEK</button>`
                  : `<span class="text-[10px] font-bold text-gray-300">LOCKED</span>`;

              html += `
                <tr class="hover:bg-gray-50 border-b transition">
                    <td class="px-4 py-2 font-mono text-[11px] text-gray-500">${r.no_rekening}</td>
                    <td class="px-4 py-2 font-medium text-gray-700 text-xs truncate max-w-[180px]" title="${r.nama_nasabah}">${r.nama_nasabah}</td>
                    <td class="px-4 py-2 text-xs text-blue-700 font-bold">${aoName}</td>
                    <td class="px-4 py-2 text-right text-[11px] text-gray-500 font-mono">${fmt(r.plafon_awal)}</td>
                    <td class="px-4 py-2 text-center font-mono text-[11px] text-gray-500">${r.tgl_jatuh_tempo}</td>
                    <td class="px-4 py-2 text-right font-bold text-blue-700 text-[11px] font-mono">${fmt(r.os_actual)}</td>
                    <td class="px-4 py-2 text-center"><span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase ${badgeClass}">${r.status_ket}</span></td>
                    <td class="px-4 py-2 text-right bg-green-50/30 border-l border-green-50">${nomBaru}</td>
                    <td class="px-4 py-2 text-center">${btn}</td>
                </tr>
              `;
          });
          tb.innerHTML = html;

          document.getElementById('btnPrev').onclick = () => changePage(-1);
          document.getElementById('btnNext').onclick = () => changePage(1);

      } catch(e) { console.error(e); } finally { l.classList.add('hidden'); }
  }

  // --- DOWNLOAD ---
  async function downloadExcel() {
      try {
          const btn = event.target.closest('button'); 
          const originalText = btn.innerHTML;
          btn.innerHTML = `<span class="animate-spin inline-block h-3 w-3 border-2 border-white border-t-transparent rounded-full mr-1"></span> Loading...`;
          btn.disabled = true;

          const payload = {
              type: 'detail_pipeline',
              closing_date: document.getElementById('closing_date').value,
              harian_date: document.getElementById('harian_date').value,
              tahun_jt: document.getElementById('tahun_jt').value,
              kode_kantor: state.cabang, kode_ao: state.ao, filter_status: state.status, page: 1, limit: 10000 
          };
          const json = await apiCall(API_URL, payload);
          const rows = json.data?.data || [];
          
          if(rows.length===0) { alert('Data kosong'); btn.innerHTML=originalText; btn.disabled=false; return; }

          let csv = "No Rekening\tNama Nasabah\tAO\tPlafon Awal\tTgl JT\tSisa OS\tStatus\tTgl Realisasi Baru\tPlafon Baru\n";
          rows.forEach(r => {
              csv += `'${r.no_rekening}\t${r.nama_nasabah}\t${r.nama_ao}\t${r.plafon_awal}\t${r.tgl_jatuh_tempo}\t${r.os_actual}\t${r.status_ket}\t${r.tgl_baru}\t${r.plafon_baru}\n`;
          });

          const blob = new Blob([csv], { type: 'application/vnd.ms-excel' });
          const a = document.createElement('a'); a.href = URL.createObjectURL(blob);
          a.download = `Pipeline_JT_${state.cabang}.xls`; a.click();
          
          btn.innerHTML=originalText; btn.disabled=false;
      } catch(e) { alert('Gagal export'); }
  }

  function closeModal() { document.getElementById('modalDetail').classList.remove('modal-active'); }
  document.addEventListener('keydown', e => { if(e.key === 'Escape') closeModal(); });
</script>