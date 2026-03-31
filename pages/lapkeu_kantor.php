<style>
  :root { --primary: #2563eb; --bg: #f8fafc; --text: #334155; }
  
  /* Styling Baris Berdasarkan Level */
  .row-level-1 { background-color: #e2e8f0 !important; font-weight: 800; cursor: pointer; }
  .row-level-2 { background-color: #f1f5f9 !important; font-weight: 700; cursor: pointer; }
  .row-level-3 { background-color: #f8fafc !important; font-weight: 600; cursor: pointer; }
  .row-detail { display: table-row; }
  .hidden-row { display: none; }
  
  .caret { display: inline-block; transition: transform 0.2s; margin-right: 8px; color: #64748b; }
  .rotate { transform: rotate(90deg); }

  .rekap-card { background: white; border-radius: 10px; padding: 12px 16px; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
  .val-plus { color: #059669; font-weight: 800; font-family: 'JetBrains Mono', monospace; }
  .val-minus { color: #dc2626; font-weight: 800; font-family: 'JetBrains Mono', monospace; }
  
  .table-container { border: 1px solid #e2e8f0; border-radius: 12px; background: white; overflow: hidden; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
  
  /* Input & Button Styling (Mirip Screenshot) */
  .inp-modern { border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 12px; font-size: 13px; font-weight: 600; background: #fff; width: 100%; height: 40px; color: #334155; transition: all 0.2s; outline: none; }
  .inp-modern:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
  .inp-modern:disabled { background-color: #f1f5f9; color: #64748b; cursor: not-allowed; }
</style>

<div class="max-w-7xl mx-auto px-4 py-4 h-screen flex flex-col space-y-4 bg-[#f8fafc]">
  
  <div class="flex flex-col md:flex-row justify-between items-end gap-4 shrink-0 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
    <div class="flex items-center gap-3">
      <div class="bg-blue-600 text-white p-2.5 rounded-xl shadow-md">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"></path><path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path><path d="M9 21v-4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v4"></path></svg>
      </div> 
      <div class="flex flex-col">
        <span class="text-xl font-black text-slate-800 tracking-tight leading-tight">LAPORAN KEUANGAN</span>
        <span id="badgeUnit" class="text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded w-fit uppercase font-bold tracking-wider mt-0.5">Memuat...</span>
      </div>
    </div>

    <form id="filterForm" class="flex flex-wrap items-end gap-3">
      <div class="flex flex-col w-[160px]">
        <label class="text-[10px] font-bold text-blue-900 uppercase ml-1 mb-1.5 tracking-wider">Laporan</label>
        <select id="type_report" class="inp-modern" onchange="fetchRekap()">
          <option value="neraca detail kantor">NERACA</option>
          <option value="laba rugi detail kantor">LABA RUGI</option>
        </select>
      </div>

      <div class="flex flex-col w-[220px]">
        <label class="text-[10px] font-bold text-blue-900 uppercase ml-1 mb-1.5 tracking-wider">Cabang</label>
        <select id="opt_kantor_rec" class="inp-modern"><option value="">Memuat...</option></select>
      </div>

      <div class="flex flex-col w-[150px]">
        <label class="text-[10px] font-bold text-blue-900 uppercase ml-1 mb-1.5 tracking-wider">Actual (Harian)</label>
        <input type="date" id="harian_date" class="inp-modern text-center">
      </div>
      
      <div class="flex gap-2">
        <button type="submit" class="h-[40px] px-5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-bold shadow-md transition flex items-center gap-2" title="Cari Data">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          CARI
        </button>
        <button type="button" onclick="exportToExcel()" class="h-[40px] w-[40px] bg-[#10b981] hover:bg-[#059669] text-white rounded-lg shadow-md transition flex items-center justify-center" title="Export Excel">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
        </button>
      </div>
    </form>
  </div>

  <div id="rekapContainer" class="grid grid-cols-1 md:grid-cols-3 gap-4 shrink-0"></div>

  <div class="flex-1 min-h-0 table-container relative bg-white">
    <div id="loadingFP" class="hidden absolute inset-0 bg-white/80 z-50 flex flex-col items-center justify-center">
       <div class="animate-spin h-10 w-10 border-4 border-blue-200 border-t-blue-600 rounded-full mb-2"></div>
       <span class="text-xs font-bold text-blue-600">MENYUSUN DATA...</span>
    </div>

    <div class="h-full overflow-auto">
      <table class="w-full text-left border-separate border-spacing-0" id="tabelLapKeu">
        <thead class="sticky top-0 z-40 bg-slate-50">
          <tr>
            <th class="p-3.5 text-[11px] font-bold text-slate-500 uppercase border-b border-slate-200 w-[120px]">Kode</th>
            <th class="p-3.5 text-[11px] font-bold text-slate-500 uppercase border-b border-slate-200">Uraian Perkiraan</th>
            <th class="p-3.5 text-[11px] font-bold text-slate-500 uppercase border-b border-slate-200 text-right">Saldo (IDR)</th>
          </tr>
        </thead>
        <tbody id="lapBody"></tbody>
      </table>
    </div>
  </div>
</div>

<script>
  const API_LAP = './api/lapkeu';
  const API_KODE = './api/kode/';
  
  let rawDataResult = [];
  window.currentUser = { kode: '000' };

  // Format Rounding (Bulat Sempurna)
  const fmtNom = n => new Intl.NumberFormat('id-ID').format(Math.round(Number(n||0)));

  // 🔥 FUNGSI SAKTI H-1 MURNI JAVASCRIPT 🔥
  function getYesterdayDate() {
      const today = new Date();
      today.setDate(today.getDate() - 1); // Otomatis mundur 1 hari
      
      const yyyy = today.getFullYear();
      const mm = String(today.getMonth() + 1).padStart(2, '0');
      const dd = String(today.getDate()).padStart(2, '0');
      return `${yyyy}-${mm}-${dd}`;
  }

  window.addEventListener('DOMContentLoaded', async () => {
      const user = (window.getUser && window.getUser()) || null;
      const uKode = (user?.kode ? String(user.kode).padStart(3,'0') : '000');
      window.currentUser.kode = uKode;

      document.getElementById('badgeUnit').innerText = (uKode === '000') ? 'KONSOLIDASI PUSAT' : `CABANG ${uKode}`;

      // 1. Setup Dropdown Kantor
      await populateKantorOptionsFP(uKode);

      // 2. Set Tanggal Paksa ke H-1 (Nggak peduli API ngomong apa)
      document.getElementById('harian_date').value = getYesterdayDate();

      // 3. Tarik Data Laporan
      fetchRekap();
  });

async function populateKantorOptionsFP(userKode) {
      const optKantor = document.getElementById('opt_kantor_rec');
      
      // JIKA USER CABANG (Misal 001) -> Kunci Dropdown
      if (userKode && userKode !== '000') {
          // Walau dikunci, kita coba tarik API biar dapet nama aslinya
          try {
              const res = await fetch(API_KODE, { 
                  method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({type:'kode_kantor'}) 
              });
              const j = await res.json();
              const myKantor = (j.data || []).find(k => String(k.kode_kantor).padStart(3,'0') === userKode);
              const nama = myKantor ? myKantor.nama_kantor : `CABANG ${userKode}`;
              optKantor.innerHTML = `<option value="${userKode}">${userKode} - ${nama}</option>`;
          } catch(e) {
              optKantor.innerHTML = `<option value="${userKode}">${userKode} - CABANG ${userKode}</option>`;
          }
          optKantor.value = userKode;
          optKantor.disabled = true;
          return; 
      }
      
      // JIKA USER PUSAT (000) -> Bebas Pilih
      try {
          const res = await fetch(API_KODE, { 
              method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({type:'kode_kantor'}) 
          });
          const json = await res.json();
          let list = Array.isArray(json.data) ? json.data : [];
          
          let html = `<option value="konsolidasi">KONSOLIDASI (SEMUA)</option>`;
          html += `<option value="000">000 - PUSAT</option>`;
          
          list.filter(x => x.kode_kantor && x.kode_kantor !== '000')
              .sort((a,b) => String(a.kode_kantor).localeCompare(String(b.kode_kantor)))
              .forEach(it => {
                  const code = String(it.kode_kantor).padStart(3,'0');
                  const nama = it.nama_kantor || `CABANG ${code}`;
                  html += `<option value="${code}">${code} - ${nama}</option>`;
              });
              
          optKantor.innerHTML = html;
          optKantor.disabled = false;
      } catch(e) {
          // Fallback kalau API mati
          optKantor.innerHTML = `<option value="konsolidasi">KONSOLIDASI (SEMUA)</option><option value="000">000 - PUSAT</option>`;
          optKantor.disabled = false;
      }
  }

  async function fetchRekap() {
      const loader = document.getElementById('loadingFP');
      const tbody = document.getElementById('lapBody');
      loader.classList.remove('hidden');
      tbody.innerHTML = '';

      const payload = {
          type: document.getElementById('type_report').value,
          kode_kantor: document.getElementById('opt_kantor_rec').value,
          harian_date: document.getElementById('harian_date').value
      };

      try {
          const res = await fetch(API_LAP, {
              method: 'POST',
              headers: {'Content-Type':'application/json'},
              body: JSON.stringify(payload)
          });
          const json = await res.json();
          rawDataResult = json.data || [];
          
          renderTable(rawDataResult);
          renderSummary(rawDataResult, payload.type);
      } catch (e) {
          console.error(e);
          tbody.innerHTML = `<tr><td colspan="3" class="text-center p-10 text-red-500 font-bold">Gagal memuat data laporan!</td></tr>`;
      } finally {
          loader.classList.add('hidden');
      }
  }

  function renderTable(data) {
    const tbody = document.getElementById('lapBody');
    tbody.innerHTML = data.map(d => {
      const kode = d.kode_perk;
      const len = kode.length;
      let cls = 'row-detail';
      let indent = `&nbsp;`.repeat((len - 1) * 4);
      let isParent = (len <= 3);
      
      if (len === 1) cls = 'row-level-1';
      else if (len <= 2) cls = 'row-level-2';
      else if (len === 3) cls = 'row-level-3';

      let hiddenCls = (len > 3) ? 'hidden-row' : '';
      let toggleIcon = isParent ? `<span class="caret">▶</span>` : '';

      return `
        <tr class="${cls} ${hiddenCls} transition-colors hover:bg-slate-50" data-kode="${kode}" data-len="${len}" onclick="toggleRow('${kode}')">
          <td class="p-3 font-mono text-[11.5px] border-b border-slate-100 text-slate-500">${kode}</td>
          <td class="p-3 text-[12px] border-b border-slate-100 text-slate-700">${indent}${toggleIcon}${d.nama_perkiraan}</td>
          <td class="p-3 text-[13px] border-b border-slate-100 text-right font-bold ${d.total_saldo < 0 ? 'text-red-600' : 'text-slate-800'}">
            ${fmtNom(d.total_saldo)}
          </td>
        </tr>
      `;
    }).join('');
  }

  window.toggleRow = function(parentKode) {
    const allRows = document.querySelectorAll('#lapBody tr');
    const clickedRow = document.querySelector(`tr[data-kode="${parentKode}"]`);
    const clickedIcon = clickedRow.querySelector('.caret');
    
    if(!clickedIcon) return;

    const isOpening = !clickedIcon.classList.contains('rotate');
    clickedIcon.classList.toggle('rotate');

    allRows.forEach(row => {
      const rowKode = row.getAttribute('data-kode');
      if (rowKode.startsWith(parentKode) && rowKode !== parentKode) {
         if (isOpening) {
             if (rowKode.length <= parentKode.length + 2 || (parentKode.length < 3 && rowKode.length <= 3)) {
                 row.classList.remove('hidden-row');
             }
         } else {
             row.classList.add('hidden-row');
             const icon = row.querySelector('.caret');
             if(icon) icon.classList.remove('rotate');
         }
      }
    });
  }
  

  function renderSummary(data, type) {
    const container = document.getElementById('rekapContainer');
    const getVal = (p) => Math.round(Number(data.find(x => x.kode_perk === p)?.total_saldo || 0));
    
    if (type.includes('neraca')) {
      container.innerHTML = `
        <div class="rekap-card"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Aktiva (1)</p><p class="text-2xl mt-1 val-plus">Rp ${fmtNom(getVal('1'))}</p></div>
        <div class="rekap-card"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Kewajiban (2)</p><p class="text-2xl mt-1 text-blue-600 font-bold font-mono">Rp ${fmtNom(getVal('2'))}</p></div>
        <div class="rekap-card"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Ekuitas (3)</p><p class="text-2xl mt-1 text-orange-500 font-bold font-mono">Rp ${fmtNom(getVal('3'))}</p></div>
      `;
    } else {
      const pdt = getVal('4'); const bya = getVal('5');
      container.innerHTML = `
        <div class="rekap-card"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Pendapatan (4)</p><p class="text-2xl mt-1 val-plus">Rp ${fmtNom(pdt)}</p></div>
        <div class="rekap-card"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Biaya (5)</p><p class="text-2xl mt-1 val-minus">Rp ${fmtNom(bya)}</p></div>
        <div class="rekap-card"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Laba Rugi Berjalan</p><p class="text-2xl mt-1 ${(pdt-bya) >=0 ? 'val-plus' : 'val-minus'}">Rp ${fmtNom(pdt-bya)}</p></div>
      `;
    }
  }

  function exportToExcel() {
    if(rawDataResult.length === 0) return alert("Data kosong!");
    let table = `<table border="1"><thead><tr style="background:#f1f5f9"><th>Kode</th><th>Uraian</th><th>Saldo</th></tr></thead><tbody>`;
    rawDataResult.forEach(d => {
      let roundedSaldo = Math.round(Number(d.total_saldo || 0));
      table += `<tr><td style="mso-number-format:'\\@'">${d.kode_perk}</td><td>${d.nama_perkiraan}</td><td>${roundedSaldo}</td></tr>`;
    });
    table += `</tbody></table>`;
    const blob = new Blob([table], { type: 'application/vnd.ms-excel' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    
    const ddl = document.getElementById('opt_kantor_rec');
    const namaKantor = ddl.options[ddl.selectedIndex].text.replace(/ /g, '_');
    
    a.download = `Laporan_Keuangan_${namaKantor}_${document.getElementById('harian_date').value}.xls`;
    a.click();
  }

  document.getElementById('filterForm').onsubmit = e => { e.preventDefault(); fetchRekap(); };
</script>