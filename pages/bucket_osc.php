<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Bucket OSC + CKPN + Flow Rate</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Kontrol */
    .inp{ border:1px solid #cbd5e1; border-radius:.6rem; padding:.5rem .75rem; font-size:14px; background:#fff; }
    .btn-icon{ width:42px; height:42px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center;
               background:#2563eb; color:#fff; box-shadow:0 6px 14px rgba(37,99,235,.25); }
    .btn-icon[disabled]{ opacity:.6; cursor:not-allowed; }
    .btn-icon:hover{ background:#1e40af; }
    .hdr{ row-gap:.5rem; }
    @media (max-width:640px){
      .title{ font-size:1.25rem; }
      .hdr{ flex-direction:column; align-items:flex-start; }
      #filterKOLEK{ width:100%; gap:.5rem; }
      .lbl{ display:none; }
      .inp{ flex:1 1 0; min-width:0; font-size:13px; padding:.45rem .6rem; }
      .btn-icon{ width:40px; height:40px; }
    }

    /* Tabel: sticky header (2 baris) + freeze kolom 1 */
    #tabelKolek{ --head1: 32px; } /* akan diset ulang via JS */
    #tabelKolek thead th{ position:sticky; top:0; background:#d9ead3; z-index:30; }
    #tabelKolek thead tr:nth-child(2) th{ top: var(--head1); z-index:29; }
    #tabelKolek .freeze-1{ position:sticky; left:0; z-index:41; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06); }
    #tabelKolek tbody tr:hover td{ background:#f9fafb; }
    #tabelKolek td.text-right, #tabelKolek th.text-right{ text-align:right; }

    /* Warna baris subtotal & grand total */
    #tabelKolek tr.row-total td{ background:#eef4ff; color:#1e40af; font-weight:600; }
    #tabelKolek tr.row-grand td{ background:#e8f7ee; color:#065f46; font-weight:700; }

    /* Badge inc_pct */
    .inc-bad{ display:inline-block; min-width:3.5rem; padding:.15rem .4rem; border-radius:.35rem; text-align:right; }
    .inc-pos{ background:#fee2e2; color:#b91c1c; }  /* >0 lebih buruk → merah */
    .inc-neg{ background:#dcfce7; color:#166534; }  /* <0 lebih baik → hijau */
    .muted{ color:#94a3b8; }

    /* Mobile tweaks */
    @media (max-width:640px){
      #tabelKolek{ font-size:12px; }
      #tabelKolek th, #tabelKolek td{ padding:.5rem .5rem; }
      #tabelKolek .freeze-1{
        min-width:12rem; max-width:12rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
      }
    }
  </style>
</head>
<body class="bg-slate-50">
  <div class="max-w-7xl mx-auto px-4 py-6 h-screen flex flex-col">
    <!-- Header & Filter -->
    <div class="hdr flex flex-wrap items-start gap-2 mb-3">
      <h1 class="title text-2xl font-bold flex items-center gap-2">
        <span>📊</span><span>Bucket OSC + CKPN</span>
      </h1>

      <form id="filterForm" class="ml-auto">
        <div id="filterKOLEK" class="flex items-center gap-2">
          <label for="closing_date" class="lbl text-sm text-slate-700">Closing:</label>
          <input type="date" id="closing_date" class="inp" required>

          <label for="harian_date" class="lbl text-sm text-slate-700">Harian:</label>
          <input type="date" id="harian_date" class="inp" required>

          <label for="kode_kantor" class="lbl text-sm text-slate-700">Kantor:</label>
          <select id="kode_kantor" class="inp"></select>

          <button type="submit" id="btnFilter" class="btn-icon" title="Terapkan">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="7"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
          </button>
        </div>
      </form>
    </div>

    <!-- ====== KONTEN: TABEL DI ATAS, FLOW RATE DI BAWAH ====== -->
    <div class="flex-1 min-h-0">
      <!-- Tabel -->
      <div id="kolekScroller" class="overflow-hidden rounded border border-gray-200 bg-white">
        <div class="h-full overflow-auto">
          <table id="tabelKolek" class="min-w-full text-sm text-left text-gray-800">
            <thead class="uppercase">
              <tr id="headKOLEK" class="text-xs">
                <th class="px-3 py-2 freeze-1 w-[18rem] min-w-[18rem]">dpd_name</th>
                <th class="px-3 py-2 text-center" colspan="3">M-1</th>
                <th class="px-3 py-2 text-center" colspan="3">Actual</th>
                <th class="px-3 py-2 text-center" colspan="2">Inc</th>
                <th class="px-3 py-2 text-right">% (inc_pct)</th>
              </tr>
              <tr class="text-[11px] bg-[#eef5e9]">
                <th class="px-3 py-2 freeze-1"></th>
                <th class="px-3 py-2 text-right">noa</th>
                <th class="px-3 py-2 text-right">osc</th>
                <th class="px-3 py-2 text-right">ckpn</th>
                <th class="px-3 py-2 text-right">noa</th>
                <th class="px-3 py-2 text-right">osc</th>
                <th class="px-3 py-2 text-right">ckpn</th>
                <th class="px-3 py-2 text-right">noa</th>
                <th class="px-3 py-2 text-right">osc</th>
                <th class="px-3 py-2"></th>
              </tr>
            </thead>
            <tbody id="tbodyKOLEK"></tbody>
          </table>
        </div>
      </div>

      <!-- FLOW RATE di bawah -->
      <section class="mt-4 rounded border border-gray-200 bg-white p-3">
        <h3 class="font-semibold text-slate-800 mb-2">FLOW RATE</h3>
        <div class="overflow-auto">
          <table id="tabelFR" class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
              <tr>
                <th class="px-3 py-2 text-left">FR</th>
                <th class="px-3 py-2 text-right">%</th>
                <th class="px-3 py-2 text-right">FR APPETITE</th>
                <th class="px-3 py-2 text-right">INC</th>
              </tr>
            </thead>
            <tbody id="tbodyFR"></tbody>
          </table>
        </div>
      </section>
    </div>
  </div>

  <script>
    // ==== Helpers
    const rupiah = n => new Intl.NumberFormat("id-ID").format(+n||0);
    const fmtInt = n => new Intl.NumberFormat("id-ID",{maximumFractionDigits:0}).format(+n||0);
    const fmtPct = v => (v===null || v===undefined) ? "-" :
                        new Intl.NumberFormat("id-ID",{maximumFractionDigits:2, minimumFractionDigits:0}).format(+v||0) + "%";
    const esc = s => String(s??'').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'","&#39;");
    const ymd = d => d.toISOString().split('T')[0];

    // ==== Init dropdown kantor
    (function initKantor(){
      const sel = document.getElementById('kode_kantor');
      sel.innerHTML = `<option value="">Konsolidasi</option>`;
      for(let i=0;i<=28;i++){
        const v = String(i).padStart(3,'0');
        sel.insertAdjacentHTML('beforeend', `<option value="${v}">${v}</option>`);
      }
      sel.value = "004";
    })();

    // ==== Set default tanggal dari API (POST) kalau ada, fallback ke kalkulasi lokal
    (async function initDates(){
      const cd = document.getElementById('closing_date');
      const hd = document.getElementById('harian_date');
      try{
        const r = await fetch('./api/date/', {method:'GET'});
        const j = await r.json();
        const d = j?.data;
        if(d?.last_closing && d?.last_created){
          cd.value = d.last_closing;
          hd.value = d.last_created;
        }else{ setLocalDefaults(cd, hd); }
      }catch{ setLocalDefaults(cd, hd); }
      loadKolek(); // initial load setelah set tanggal
    })();

    function setLocalDefaults(cdEl, hdEl){
      const now = new Date();
      const closing = new Date(now.getFullYear(), now.getMonth(), 0, 12);
      const harian  = new Date(now.getFullYear(), now.getMonth(), now.getDate()-1, 12);
      cdEl.value = ymd(closing);
      hdEl.value = ymd(harian);
    }

    // ==== Fetch & Render
    let aborter = null;
    document.getElementById('filterForm').addEventListener('submit', e=>{
      e.preventDefault(); loadKolek();
    });

    function toggleBtn(disabled){
      const b = document.getElementById('btnFilter');
      if(b) b.disabled = disabled;
    }

    async function loadKolek(){
      if(aborter) aborter.abort();
      aborter = new AbortController();
      toggleBtn(true);

      const closing_date = document.getElementById('closing_date').value;
      const harian_date  = document.getElementById('harian_date').value;
      const kcValRaw     = document.getElementById('kode_kantor').value;
      const kode_kantor  = kcValRaw || null; // null = konsolidasi

      try{
        // Panggil dua tipe: OSC & CKPN
        const bodyBase = { closing_date, harian_date, kode_kantor };
        const [oscRes, ckpnRes] = await Promise.all([
          fetch("./api/kolek/", {
            method: "POST",
            headers: {"Content-Type":"application/json"},
            body: JSON.stringify({ type:"bucket osc", ...bodyBase }),
            signal: aborter.signal
          }).then(r=>r.json()),
          fetch("./api/kolek/", {
            method: "POST",
            headers: {"Content-Type":"application/json"},
            body: JSON.stringify({ type:"bucket ckpn", ...bodyBase }),
            signal: aborter.signal
          }).then(r=>r.json())
        ]);

        const dataOSC  = oscRes?.data || {};
        const dataCKPN = ckpnRes?.data || {};

        // Gabungkan per dpd_code (tambahkan ckpn_m1 & ckpn_curr ke rows OSC)
        const ckMap = new Map();
        if(Array.isArray(dataCKPN.rows)){
          for(const r of dataCKPN.rows){
            ckMap.set(r.dpd_code, r);
          }
        }

        const merged = { ...dataOSC };
        merged.rows = Array.isArray(dataOSC.rows) ? dataOSC.rows.map(r=>{
          const ck = ckMap.get(r.dpd_code);
          return {
            ...r,
            ckpn_m1: ck?.ckpn_m1 ?? null,
            ckpn_curr: ck?.ckpn_curr ?? null,
            // ckpn_inc tersedia tapi tidak ditampilkan kolomnya (sesuai minta cuma m-1 & actual)
          };
        }) : [];

        renderTable(merged);
        renderFR(dataOSC?.flow_rate || []);
        adjustHeadHeight();
      }catch(err){
        if(err.name!=='AbortError') console.error(err);
      }finally{
        toggleBtn(false);
      }
    }

    function renderTable(data){
      const tbody = document.getElementById('tbodyKOLEK');
      tbody.innerHTML = "";

      const rows = Array.isArray(data?.rows) ? [...data.rows] : [];
      if(!rows.length){
        tbody.innerHTML = `<tr><td class="px-3 py-3 text-sm text-red-600">Tidak ada data.</td></tr>`;
        return;
      }

      // Tarik GRAND_TOTAL ke paling atas
      const idxGT = rows.findIndex(r=>r.dpd_code==='GRAND_TOTAL');
      if(idxGT>=0){ const gt = rows.splice(idxGT,1)[0]; rows.unshift(gt); }

      rows.forEach(r=>{
        const isTotal = r.dpd_code?.startsWith('TOTAL_');
        const isGrand = r.dpd_code==='GRAND_TOTAL';

        const trClass = isGrand ? 'row-grand' : (isTotal ? 'row-total' : '');
        const name = esc(r.dpd_name ?? r.dpd_code ?? '');

        const noa_m1 = r.noa_m1==null ? '-' : fmtInt(r.noa_m1);
        const os_m1  = r.os_m1==null  ? '-' : rupiah(r.os_m1);
        const ck_m1  = r.ckpn_m1==null? '-' : rupiah(r.ckpn_m1);

        const noa_c  = r.noa_curr==null? '-' : fmtInt(r.noa_curr);
        const os_c   = r.os_curr==null ? '-' : rupiah(r.os_curr);
        const ck_c   = r.ckpn_curr==null? '-' : rupiah(r.ckpn_curr);

        const inc_n  = r.inc_noa==null ? '-' : fmtInt(r.inc_noa);
        const inc_o  = r.inc_os==null  ? '-' : rupiah(r.inc_os);

        const pct = r.inc_pct;
        const pctStr = fmtPct(pct);
        const pctCls = (pct===null || pct===undefined) ? 'muted'
                      : (pct>0 ? 'inc-pos' : (pct<0 ? 'inc-neg' : ''));
        const pctHtml = (pct===null || pct===undefined)
          ? `<span class="muted">-</span>`
          : `<span class="inc-bad ${pctCls}">${pctStr}</span>`;

        tbody.insertAdjacentHTML('beforeend', `
          <tr class="border-b ${trClass}">
            <td class="px-3 py-2 freeze-1" title="${name}">${name}</td>
            <td class="px-3 py-2 text-right">${noa_m1}</td>
            <td class="px-3 py-2 text-right">${os_m1}</td>
            <td class="px-3 py-2 text-right">${ck_m1}</td>
            <td class="px-3 py-2 text-right">${noa_c}</td>
            <td class="px-3 py-2 text-right">${os_c}</td>
            <td class="px-3 py-2 text-right">${ck_c}</td>
            <td class="px-3 py-2 text-right">${inc_n}</td>
            <td class="px-3 py-2 text-right">${inc_o}</td>
            <td class="px-3 py-2 text-right">${pctHtml}</td>
          </tr>
        `);
      });
    }

    function renderFR(list){
      const tb = document.getElementById('tbodyFR');
      tb.innerHTML = '';
      if(!Array.isArray(list) || !list.length){
        tb.innerHTML = `<tr><td class="px-3 py-2 text-sm text-slate-500" colspan="4">Tidak ada data.</td></tr>`;
        return;
      }
      list.forEach(r=>{
        const act = r.actual_pct;
        const app = r.appetite_pct;
        const inc = r.inc_pct;

        const incCls = (inc===null||inc===undefined) ? 'muted'
                     : (inc>0 ? 'inc-pos' : (inc<0 ? 'inc-neg' : ''));
        const incHtml = (inc===null||inc===undefined)
          ? `<span class="muted">-</span>`
          : `<span class="inc-bad ${incCls}">${fmtPct(inc)}</span>`;

        tb.insertAdjacentHTML('beforeend', `
          <tr class="border-b">
            <td class="px-3 py-2">${esc(r.label||r.code)}</td>
            <td class="px-3 py-2 text-right">${fmtPct(act)}</td>
            <td class="px-3 py-2 text-right">${fmtPct(app)}</td>
            <td class="px-3 py-2 text-right">${incHtml}</td>
          </tr>
        `);
      });
    }

    // Sesuaikan top baris header ke-2 (dinamis sesuai tinggi baris 1)
    function adjustHeadHeight(){
      const head1 = document.getElementById('headKOLEK');
      if(!head1) return;
      const h = head1.getBoundingClientRect().height || 32;
      document.getElementById('tabelKolek').style.setProperty('--head1', h + 'px');
    }
    window.addEventListener('resize', adjustHeadHeight);
  </script>
</body>
</html>
