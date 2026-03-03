<div class="max-w-full mx-auto px-4 py-5 h-[calc(100vh-80px)] flex flex-col bg-slate-50 font-sans" id="SD_root">
  
  <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-4 shrink-0">
    <div>
      <h1 class="text-xl md:text-2xl font-bold flex items-center gap-2 text-slate-800">
        <span class="bg-blue-600 text-white p-1.5 rounded-lg shadow-sm text-sm">🔎</span>
        <span>Search Debitur Kredit</span>
      </h1>
      
      <div class="flex items-center gap-3 mt-3">
        <div class="px-3 py-1.5 bg-blue-50 border border-blue-200 rounded-lg shadow-sm flex items-center gap-2">
            <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">NOA:</span>
            <span class="text-sm font-bold text-blue-900" id="SD_sumNoa">0</span>
        </div>
        <div class="px-3 py-1.5 bg-emerald-50 border border-emerald-200 rounded-lg shadow-sm flex items-center gap-2">
            <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">BD (ACT):</span>
            <span class="text-sm font-bold text-emerald-900" id="SD_sumBd">0</span>
        </div>
      </div>
    </div>

    <form id="SD_formFilter" class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm flex flex-wrap items-end gap-3 shrink-0">
      <div class="field flex flex-col gap-1">
        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider ml-1">Kantor</label>
        <select id="SD_optKantor" class="inp min-w-[150px]">
          <option value="">Semua</option>
        </select>
      </div>
      <div class="field flex flex-col gap-1">
        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider ml-1">Kolek</label>
        <select id="SD_optKolek" class="inp min-w-[90px]">
          <option value="Semua">Semua</option>
          <option value="L">L</option>
          <option value="DP">DP</option>
          <option value="KL">KL</option>
          <option value="D">D</option>
          <option value="M">M</option>
        </select>
      </div>
      <div class="field flex flex-col gap-1">
        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider ml-1">Cari</label>
        <input type="text" id="SD_search" placeholder="No Rek / Nama" class="inp w-[160px]">
      </div>
      <div class="field flex flex-col gap-1">
        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider ml-1">Totung</label>
        <input type="number" id="SD_totung" placeholder="kosong = > 0" class="inp w-[130px]">
      </div>
      
      <div class="flex items-center gap-2 mt-auto">
        <button type="submit" class="btn-icon h-9 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-sm transition flex items-center gap-2 font-bold text-xs uppercase tracking-wide">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          Cari
        </button>
        <button type="button" onclick="SD_exportExcelAll()" class="btn-icon h-9 px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-sm transition flex items-center gap-2" title="Download Semua Data">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
        </button>
      </div>
    </form>
  </div>

  <div class="flex-1 min-h-0 relative flex flex-col bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
    
    <div id="SD_loading" class="hidden absolute inset-0 bg-white/80 z-50 flex flex-col items-center justify-center text-blue-600 backdrop-blur-sm">
       <div class="animate-spin h-10 w-10 border-4 border-blue-200 border-t-blue-600 rounded-full mb-3"></div>
       <span class="text-xs font-bold tracking-widest uppercase">Mencari Data...</span>
    </div>

    <div class="flex-1 overflow-auto relative">
      <table id="SD_table" class="min-w-full text-left text-xs table-fixed">
        <thead class="bg-slate-100 text-slate-600 uppercase text-[10px] tracking-wider sticky top-0 z-10 shadow-sm">
          <tr>
            <th class="px-4 py-3 border-b border-r border-slate-200 w-48">Nama Debitur</th>
            <th class="px-4 py-3 border-b border-r border-slate-200 w-32 text-center">No Rekening</th>
            <th class="px-4 py-3 border-b border-r border-slate-200 w-24 text-center">Produk</th>
            <th class="px-4 py-3 border-b border-r border-slate-200 w-24 text-center">Plan Bucket</th>
            <th class="px-4 py-3 border-b border-r border-slate-200 w-24 text-center">Bucket Act</th>
            <th class="px-4 py-3 border-b border-r border-slate-200 w-16 text-center">Kolek</th>
            <th class="px-4 py-3 border-b border-r border-slate-200 w-16 text-center">DPD</th>
            <th class="px-4 py-3 border-b border-r border-slate-200 w-16 text-center">HMP</th>
            <th class="px-4 py-3 border-b border-r border-slate-200 w-16 text-center">HMB</th>
            <th class="px-4 py-3 border-b border-r border-slate-200 w-24 text-center">Jatuh Tempo</th>
            <th class="px-4 py-3 border-b border-r border-slate-200 w-32 text-right bg-emerald-50">Baki Debet</th>
            <th class="px-4 py-3 border-b border-r border-slate-200 w-28 text-right bg-red-50">Totung</th>
            <th class="px-4 py-3 border-b border-slate-200 w-32 text-right">Saldo Tabungan</th>
          </tr>
        </thead>
        <tbody id="SD_tbody" class="text-slate-700 divide-y divide-slate-100">
          <tr><td colspan="13" class="px-4 py-12 text-center text-slate-400">Silakan masukkan filter dan klik Cari.</td></tr>
        </tbody>
      </table>
    </div>

    <div class="bg-slate-50 border-t border-slate-200 p-3 flex items-center justify-between shrink-0">
        <span class="text-xs text-slate-500 font-medium" id="SD_pageInfo">Menampilkan 0 data</span>
        <div class="flex items-center gap-2">
            <button onclick="SD_changePage(-1)" id="SD_btnPrev" class="px-3 py-1.5 bg-white border border-slate-300 rounded hover:bg-slate-100 text-slate-600 disabled:opacity-50 disabled:cursor-not-allowed transition text-xs font-bold shadow-sm">« Prev</button>
            <span class="text-xs font-bold text-slate-700 px-2" id="SD_pageCurrent">Hal 1 dari 1</span>
            <button onclick="SD_changePage(1)" id="SD_btnNext" class="px-3 py-1.5 bg-white border border-slate-300 rounded hover:bg-slate-100 text-slate-600 disabled:opacity-50 disabled:cursor-not-allowed transition text-xs font-bold shadow-sm">Next »</button>
        </div>
    </div>

  </div>
</div>

<style>
  .inp { border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 0 0.6rem; font-size: 12px; background: #fff; height: 36px; outline: none; transition: 0.2s; }
  .inp:focus { border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,0.1); }
  .inp:disabled { background-color: #f1f5f9; color: #64748b; font-weight: bold; cursor: not-allowed; }
  .btn-icon:hover { transform: translateY(-1px); }
  
  #SD_tbody tr:hover { background-color: #f8fafc; }
</style>

<script>
  const nf = new Intl.NumberFormat('id-ID');
  
  // STATE
  let SD_currentPage = 1;
  const SD_limit = 50;
  let SD_totalPage = 1;

  // INIT
  window.addEventListener('DOMContentLoaded', async () => {
      await populateKantorSD();

      const user = (window.getUser && window.getUser()) || {};
      const kodeLogin = String(user?.kode||'').padStart(3,'0');
      
      const optKantor = document.getElementById('SD_optKantor');
      if (kodeLogin && kodeLogin !== '000') {
          optKantor.value = kodeLogin; 
          optKantor.disabled = true;
      }
      
      // Auto fetch pertama kali
      fetchDataSD(1);
  });

  document.getElementById('SD_formFilter').addEventListener('submit', (e) => {
      e.preventDefault();
      fetchDataSD(1); // Kembali ke halaman 1 saat search baru
  });

  async function populateKantorSD() {
      try {
          const r = await fetch('./api/kode/', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({type:'kode_kantor'})});
          const j = await r.json();
          const list = Array.isArray(j.data) ? j.data : [];
          let html = `<option value="">Semua</option>`;
          list.filter(x => x.kode_kantor && x.kode_kantor !== '000')
              .sort((a,b) => String(a.kode_kantor).localeCompare(String(b.kode_kantor)))
              .forEach(it => {
                  const code = String(it.kode_kantor).padStart(3,'0');
                  html += `<option value="${code}">${code} — ${it.nama_kantor||''}</option>`;
              });
          document.getElementById('SD_optKantor').innerHTML = html;
      } catch(e) {}
  }

  function getFilterPayloadSD(page, limit) {
      const optKantor = document.getElementById('SD_optKantor');
      return {
          type: "cari debitur",
          kode_kantor: optKantor.disabled ? optKantor.value : (optKantor.value || ""),
          kolek: document.getElementById('SD_optKolek').value,
          search: document.getElementById('SD_search').value,
          totung: document.getElementById('SD_totung').value,
          page: page,
          limit: limit
      };
  }

  async function fetchDataSD(pageTarget) {
      SD_currentPage = pageTarget;
      
      const loading = document.getElementById('SD_loading');
      loading.classList.remove('hidden');

      const payload = getFilterPayloadSD(SD_currentPage, SD_limit);

      try {
          const res = await fetch('./api/flow_par/', { // ENDPOINT SESUAI REQUEST
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(payload)
          });
          const json = await res.json();
          
          if (json.status === 200 && json.data) {
              renderTableSD(json.data.data);
              updateSummarySD(json.data.summary);
              updatePaginationSD(json.data.pagination);
          } else {
              throw new Error(json.message || "Gagal memuat data");
          }
      } catch(e) {
          document.getElementById('SD_tbody').innerHTML = `<tr><td colspan="13" class="px-4 py-12 text-center text-red-500 font-bold">${e.message}</td></tr>`;
      } finally {
          loading.classList.add('hidden');
      }
  }

  function renderTableSD(list) {
      const tbody = document.getElementById('SD_tbody');
      if (!list || list.length === 0) {
          tbody.innerHTML = `<tr><td colspan="13" class="px-4 py-12 text-center text-slate-400">Tidak ada debitur ditemukan.</td></tr>`;
          return;
      }

      tbody.innerHTML = list.map(d => {
          return `
            <tr class="transition">
              <td class="px-4 py-2 border-b border-r border-slate-100 font-semibold truncate" title="${d.nama_nasabah||''}">${d.nama_nasabah||'-'}</td>
              <td class="px-4 py-2 border-b border-r border-slate-100 text-center font-mono text-slate-500">${d.no_rekening||'-'}</td>
              <td class="px-4 py-2 border-b border-r border-slate-100 text-center">${d.kode_produk||'-'}</td>
              <td class="px-4 py-2 border-b border-r border-slate-100 text-center">${d.plan_bucket||'-'}</td>
              <td class="px-4 py-2 border-b border-r border-slate-100 text-center text-blue-600 font-bold">${d.bucket_actual||'-'}</td>
              <td class="px-4 py-2 border-b border-r border-slate-100 text-center">${d.kolek||'-'}</td>
              <td class="px-4 py-2 border-b border-r border-slate-100 text-center">${d.dpd||'0'}</td>
              <td class="px-4 py-2 border-b border-r border-slate-100 text-center">${d.hmp||'0'}</td>
              <td class="px-4 py-2 border-b border-r border-slate-100 text-center">${d.hmb||'0'}</td>
              <td class="px-4 py-2 border-b border-r border-slate-100 text-center">${d.tgl_jatuh_tempo||'-'}</td>
              <td class="px-4 py-2 border-b border-r border-slate-100 text-right text-emerald-700 font-bold bg-emerald-50/30">${nf.format(d.baki_debet||0)}</td>
              <td class="px-4 py-2 border-b border-r border-slate-100 text-right text-red-600 font-bold bg-red-50/30">${nf.format(d.totung||0)}</td>
              <td class="px-4 py-2 border-b border-slate-100 text-right text-slate-600">${nf.format(d.saldo_tabungan||0)}</td>
            </tr>
          `;
      }).join('');
  }

  function updateSummarySD(summary) {
      if(!summary) return;
      document.getElementById('SD_sumNoa').innerText = nf.format(summary.noa || 0);
      document.getElementById('SD_sumBd').innerText = nf.format(summary.bd_act || 0);
  }

  function updatePaginationSD(pag) {
      if(!pag) return;
      SD_totalPage = pag.total_page || 1;
      SD_currentPage = pag.current_page || 1;
      
      const totalData = pag.total_data || 0;
      document.getElementById('SD_pageInfo').innerText = `Total: ${nf.format(totalData)} Debitur`;
      document.getElementById('SD_pageCurrent').innerText = `Hal ${SD_currentPage} dari ${SD_totalPage}`;

      document.getElementById('SD_btnPrev').disabled = (SD_currentPage <= 1);
      document.getElementById('SD_btnNext').disabled = (SD_currentPage >= SD_totalPage);
  }

  window.SD_changePage = function(dir) {
      const newPage = SD_currentPage + dir;
      if (newPage >= 1 && newPage <= SD_totalPage) {
          fetchDataSD(newPage);
      }
  };

  // --- DOWNLOAD EXCEL (MENGAMBIL SEMUA DATA TANPA LIMIT HALAMAN) ---
  window.SD_exportExcelAll = async function() {
      const btn = document.querySelector('button[title="Download Semua Data"]');
      const originalHtml = btn.innerHTML;
      btn.innerHTML = `<svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>`;
      btn.disabled = true;

      // Ambil payload filter, tapi tembak LIMIT sangat besar agar semua data ter-download
      const payload = getFilterPayloadSD(1, 999999);

      try {
          const res = await fetch('./api/flow_par/', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(payload)
          });
          const json = await res.json();
          
          if (json.status === 200 && json.data && json.data.data) {
              const list = json.data.data;
              if(list.length === 0) {
                  alert("Tidak ada data untuk didownload.");
                  return;
              }

              let table = `<table border="1">
                  <thead>
                      <tr>
                          <th style="background:#f1f5f9;">NAMA NASABAH</th>
                          <th style="background:#f1f5f9;">NO REKENING</th>
                          <th style="background:#f1f5f9;">NO TABUNGAN</th>
                          <th style="background:#f1f5f9;">PRODUK</th>
                          <th style="background:#f1f5f9;">PLAN BUCKET</th>
                          <th style="background:#dbeafe;">BUCKET ACTUAL</th>
                          <th style="background:#f1f5f9;">KOLEK</th>
                          <th style="background:#f1f5f9;">DPD</th>
                          <th style="background:#f1f5f9;">HMP</th>
                          <th style="background:#f1f5f9;">HMB</th>
                          <th style="background:#f1f5f9;">JATUH TEMPO</th>
                          <th style="background:#dcfce7;">BAKI DEBET</th>
                          <th style="background:#fee2e2;">TOTUNG</th>
                          <th style="background:#f1f5f9;">SALDO TABUNGAN</th>
                      </tr>
                  </thead>
                  <tbody>`;

              list.forEach(d => {
                  table += `<tr>
                      <td>${d.nama_nasabah||''}</td>
                      <td style="mso-number-format:'\\@'">${d.no_rekening||''}</td>
                      <td style="mso-number-format:'\\@'">${d.norek_tabungan||''}</td>
                      <td>${d.kode_produk||''}</td>
                      <td>${d.plan_bucket||''}</td>
                      <td>${d.bucket_actual||''}</td>
                      <td>${d.kolek||''}</td>
                      <td>${d.dpd||'0'}</td>
                      <td>${d.hmp||'0'}</td>
                      <td>${d.hmb||'0'}</td>
                      <td>${d.tgl_jatuh_tempo||''}</td>
                      <td>${Number(d.baki_debet||0)}</td>
                      <td>${Number(d.totung||0)}</td>
                      <td>${Number(d.saldo_tabungan||0)}</td>
                  </tr>`;
              });
              table += `</tbody></table>`;

              const blob = new Blob([table], { type: 'application/vnd.ms-excel' });
              const a = document.createElement('a'); a.href = URL.createObjectURL(blob);
              const tgl = new Date().toISOString().split('T')[0];
              a.download = `Search_Debitur_Kredit_${tgl}.xls`; a.click();
          } else {
              alert("Gagal menarik data untuk Excel.");
          }
      } catch(e) {
          alert("Terjadi kesalahan saat download Excel.");
      } finally {
          btn.innerHTML = originalHtml;
          btn.disabled = false;
      }
  };
</script>