<!-- 🧭 AO REMEDIAL — HEADER 3-LAPIS (Judul → Sub Bucket → NOA/OSC) -->
<div class="max-w-[100vw] px-4 py-6">
  <div class="flex items-center justify-between mb-3">
    <h1 class="text-2xl font-bold">🧭 AO Remedial — Account Mapping</h1>

    <!-- Dropdown Cabang (seperti CKPN Produk) -->
    <form id="formAoFilter" class="flex flex-wrap items-center gap-3">
      <label for="opt_kantor" class="text-sm">Cabang:</label>
      <select id="opt_kantor" class="border rounded px-3 py-1 text-sm min-w-[220px]">
        <option value="">Konsolidasi (Semua Cabang)</option>
      </select>
      <button class="bg-blue-600 text-white px-3 py-1.5 rounded text-sm hover:bg-blue-700">
        Terapkan
      </button>
    </form>
  </div>

  <div class="table-wrap">
    <table class="ao-table" id="tblAoRemedial">
      <thead>
        <tr class="r1" id="hdrRow1"></tr>
        <tr class="r2" id="hdrRow2"></tr>
        <tr class="r3" id="hdrRow3"></tr>
      </thead>
      <tbody id="aoBody"></tbody>
      <tfoot>
        <tr class="grand">
          <td class="sticky-left">GRAND TOTAL</td>
          <!-- filled by JS -->
        </tr>
      </tfoot>
    </table>
  </div>

  <div class="mt-6 table-wrap">
    <table class="ao-table" id="tblPlanActual">
        <thead>
        <tr class="pa-r1">
            <th class="sticky-left th-ao" rowspan="4"><span class="vtext">AO REMEDIAL</span></th>
            <th class="th-pa-title" colspan="20">PLAN VS ACTUAL</th>
        </tr>
        <tr class="pa-r2">
            <th class="th-group thg-plan"   colspan="10">PLAN</th>
            <th class="th-group thg-actual" colspan="10">ACTUAL</th>
        </tr>
        <tr class="pa-r3">
            <th class="th-bucket tbg-btc"    colspan="2">BTC</th>
            <th class="th-bucket tbg-back"   colspan="2">BACK FLOW</th>
            <th class="th-bucket tbg-stay"   colspan="2">STAY</th>
            <th class="th-bucket tbg-flow"   colspan="2">FLOW</th>
            <th class="th-bucket tbg-lunas"  colspan="2">LUNAS</th>

            <th class="th-bucket tbg-btc"    colspan="2">BTC</th>
            <th class="th-bucket tbg-back"   colspan="2">BACK FLOW</th>
            <th class="th-bucket tbg-stay"   colspan="2">STAY</th>
            <th class="th-bucket tbg-flow"   colspan="2">FLOW</th>
            <th class="th-bucket tbg-lunas"  colspan="2">LUNAS</th>
        </tr>
        <tr class="pa-r4">
            <!-- PLAN sublabels -->
            <th class="th-sub">NOA</th><th class="th-sub">OSC</th>
            <th class="th-sub">NOA</th><th class="th-sub">OSC</th>
            <th class="th-sub">NOA</th><th class="th-sub">OSC</th>
            <th class="th-sub">NOA</th><th class="th-sub">OSC</th>
            <th class="th-sub">NOA</th><th class="th-sub">OSC</th>
            <!-- ACTUAL sublabels -->
            <th class="th-sub">NOA</th><th class="th-sub">OSC</th>
            <th class="th-sub">NOA</th><th class="th-sub">OSC</th>
            <th class="th-sub">NOA</th><th class="th-sub">OSC</th>
            <th class="th-sub">NOA</th><th class="th-sub">OSC</th>
            <th class="th-sub">NOA</th><th class="th-sub">OSC</th>
        </tr>
        </thead>
        <tbody id="paBody"></tbody>
        <tfoot>
        <tr class="grand">
            <td class="sticky-left">GRAND TOTAL</td>
            <!-- 20 cells (10 pairs) diisi JS -->
        </tr>
        </tfoot>
    </table>
    </div>

    <div class="mt-6 table-wrap">
    <table class="ao-table" id="tblCkpn">
        <thead>
        <!-- Row-1: Title -->
        <tr class="ck-r1">
            <th class="sticky-left th-ao" rowspan="4"><span class="vtext">AO REMEDIAL</span></th>
            <th class="th-ck-title" colspan="22">CKPN</th>
        </tr>

        <!-- Row-2: Plan / Actual groups -->
        <tr class="ck-r2">
            <th class="th-group thg-plan-ck"   colspan="11">PLAN CKPN</th>
            <th class="th-group thg-actual-ck" colspan="11">ACTUAL CKPN</th>
        </tr>

        <!-- Row-3: Sub buckets -->
        <tr class="ck-r3">
            <!-- PLAN -->
            <th class="th-bucket tbg-btc"   colspan="2">BTC</th>
            <th class="th-bucket tbg-back"  colspan="2">BACK FLOW</th>
            <th class="th-bucket tbg-stay"  colspan="2">STAY</th>
            <th class="th-bucket tbg-flow"  colspan="2">FLOW</th>
            <th class="th-bucket tbg-lunas" colspan="2">LUNAS</th>
            <th class="th-bucket tbg-total" colspan="1">TOTAL</th>

            <!-- ACTUAL -->
            <th class="th-bucket tbg-btc"   colspan="2">BTC</th>
            <th class="th-bucket tbg-back"  colspan="2">BACK FLOW</th>
            <th class="th-bucket tbg-stay"  colspan="2">STAY</th>
            <th class="th-bucket tbg-flow"  colspan="2">FLOW</th>
            <th class="th-bucket tbg-lunas" colspan="2">LUNAS</th>
            <th class="th-bucket tbg-total" colspan="1">TOTAL</th>
        </tr>

        <!-- Row-4: NOA / CKPN -->
        <tr class="ck-r4">
            <!-- PLAN (5 buckets x 2) + TOTAL(ckpn only) -->
            <th class="th-sub">NOA</th><th class="th-sub">CKPN</th>
            <th class="th-sub">NOA</th><th class="th-sub">CKPN</th>
            <th class="th-sub">NOA</th><th class="th-sub">CKPN</th>
            <th class="th-sub">NOA</th><th class="th-sub">CKPN</th>
            <th class="th-sub">NOA</th><th class="th-sub">CKPN</th>
            <th class="th-sub">CKPN</th>
            <!-- ACTUAL (5 buckets x 2) + TOTAL(ckpn only) -->
            <th class="th-sub">NOA</th><th class="th-sub">CKPN</th>
            <th class="th-sub">NOA</th><th class="th-sub">CKPN</th>
            <th class="th-sub">NOA</th><th class="th-sub">CKPN</th>
            <th class="th-sub">NOA</th><th class="th-sub">CKPN</th>
            <th class="th-sub">NOA</th><th class="th-sub">CKPN</th>
            <th class="th-sub">CKPN</th>
        </tr>
        </thead>

        <tbody id="ckBody"></tbody>

        <tfoot>
        <tr class="grand">
            <td class="sticky-left">GRAND TOTAL</td>
            <!-- 22 cells diisi oleh JS (11 plan + 11 actual) -->
        </tr>
        </tfoot>
    </table>
    </div>

</div>



<style>
  :root{
    --c-soft:#3b82f6;   /* biru */
    --c-acct:#ef4444;   /* merah */
    --c-re:#facc15;     /* kuning */
    --c-be:#f59e0b;     /* oranye */
    --c-total:#ef4444;  /* merah */
    --c-ao:#16a34a;     /* hijau */
    --bd:#e5e7eb; --txt:#374151;
  }
  .table-wrap{overflow:auto; border:1px solid var(--bd); border-radius:10px; background:#fff}
  .ao-table{border-collapse:separate; border-spacing:0; min-width:1400px; width:max-content}
  th,td{border:1px solid var(--bd); padding:6px 8px; background:#fff; color:var(--txt); white-space:nowrap; font:13px/1.25 system-ui,Segoe UI,Roboto,Arial}

  /* Sticky header 3 baris */
  thead th{position:sticky; z-index:5}
  thead .r1 th{top:0;    z-index:7}
  thead .r2 th{top:36px; z-index:6}
  thead .r3 th{top:72px; z-index:5}

  /* Grup judul (baris-1) */
  .th-group{color:#fff; text-align:center; font-weight:800}
  .thg-soft{background:var(--c-soft)}
  .thg-acct{background:var(--c-acct)}
  .thg-re{background:var(--c-re); color:#1f2937}
  .thg-be{background:var(--c-be); color:#1f2937}
  .thg-total{background:var(--c-total)}

  /* Sub bucket (baris-2) */
  .th-bucket{font-weight:700; text-align:center}
  .tbg-soft{background:#dbeafe}   /* biru muda */
  .tbg-acct{background:#fee2e2}   /* merah muda */
  .tbg-re{background:#fef9c3}     /* kuning muda */
  .tbg-be{background:#ffedd5}     /* oranye muda */
  .tbg-total{background:#fecaca}  /* merah muda 2 */

  /* NOA/OSC (baris-3) */
  .th-sub{background:#f8fafc; text-align:center; font-size:12px}

  /* Kolom kiri AO */
  .sticky-left{position:sticky; left:0; z-index:8; background:#fff}
  .th-ao{background:var(--c-ao); color:#fff; width:40px; min-width:40px; padding:0}
  .vtext{writing-mode:vertical-rl; transform:rotate(180deg); display:inline-block; padding:10px 0; letter-spacing:.5px; font-weight:800}
  .ao-name{color:#000 !important; font-weight:700}

  tbody td:first-child, tfoot td:first-child{background:#f8fafc}
  tbody tr:hover td{background:#f9fafb}
  tfoot .grand td{background:#fef3c7; font-weight:800}
  .num{text-align:right}
</style>

<style>
  /* === PLAN vs ACTUAL: sticky header tanpa menimpa warna === */
#tblPlanActual { --pa-h: 36px; } /* tinggi per baris header; ubah ke 38 jika perlu */

/* jangan pakai rule global 'thead .pa-rX th' */
#tblPlanActual thead th{
  position: sticky;
  /* TIDAK ada background di sini supaya warna kelas tetap muncul */
  box-sizing: border-box;
  background-clip: padding-box; /* hindari garis 1px saat scroll */
}

/* offset & layering 4 baris header (r1 paling atas) */
#tblPlanActual thead .pa-r1 th{ top: 0;                           height: var(--pa-h); z-index: 9; }
#tblPlanActual thead .pa-r2 th{ top: calc(var(--pa-h) * 1);       height: var(--pa-h); z-index: 8; }
#tblPlanActual thead .pa-r3 th{ top: calc(var(--pa-h) * 2);       height: var(--pa-h); z-index: 7; }
#tblPlanActual thead .pa-r4 th{ top: calc(var(--pa-h) * 3);       height: var(--pa-h); z-index: 6; }

/* kolom kiri "AO REMEDIAL" selalu di atas */
#tblPlanActual .sticky-left{ position: sticky; left: 0; z-index: 120;  background-clip:padding-box; }
#tblPlanActual .th-ao{ z-index: 150; }  /* header hijau vertikal */

/* warna header kamu tetap dipakai */
.th-pa-title{ background:#fde047; color:#1f2937; text-align:center; font-weight:800; border:1px solid #e5e7eb; }
.thg-plan{   background:#fbbf24; color:#1f2937; }
.thg-actual{ background:#f59e0b; color:#1f2937; }
.tbg-btc{   background:#dbeafe; }
.tbg-back{  background:#dcfce7; }
.tbg-stay{  background:#ffe4d6; }
.tbg-flow{  background:#fcd9b6; }
.tbg-lunas{ background:#f3f4f6; }


  /* pakai class dari tabel atas: .table-wrap, .ao-table, .th-sub, .sticky-left, .th-ao, .vtext, .grand, .num, dll */
</style>

<style>
  /* === Sticky offsets khusus tabel CKPN (4 baris header) === */
  :root { --ckHeaderH: 36px; }
  #tblCkpn thead .ck-r1 th{position:sticky; top:0;                           height:var(--ckHeaderH); z-index:8}
  #tblCkpn thead .ck-r2 th{position:sticky; top:calc(var(--ckHeaderH) * 1);  height:var(--ckHeaderH); z-index:7}
  #tblCkpn thead .ck-r3 th{position:sticky; top:calc(var(--ckHeaderH) * 2);  height:var(--ckHeaderH); z-index:6}
  #tblCkpn thead .ck-r4 th{position:sticky; top:calc(var(--ckHeaderH) * 3);  height:var(--ckHeaderH); z-index:5}

  /* Title bar */
  .th-ck-title{background:#fde047; color:#1f2937; text-align:center; font-weight:800; border:1px solid #e5e7eb}

  /* Group bars */
  .thg-plan-ck{background:#facc15; color:#1f2937}
  .thg-actual-ck{background:#f59e0b; color:#1f2937}

  /* Sub-bucket colors */
  .tbg-btc{background:#dbeafe}     /* biru muda */
  .tbg-back{background:#dcfce7}    /* hijau muda */
  .tbg-stay{background:#ffe4d6}    /* peach */
  .tbg-flow{background:#fcd9b6}    /* oranye muda */
  .tbg-lunas{background:#f3f4f6}   /* abu/putih */
  .tbg-total{background:#fecaca; font-weight:700} /* merah muda total */

  /* pastikan kolom kiri CKPN selalu di atas */
  #tblCkpn .sticky-left{z-index:120;}
  #tblCkpn .th-ao{z-index:150}
</style>

<script>
  /* ======== KONFIGURASI ======== */
  const BUCKETS = [
    {k:'A', label:'A_DPD 0', group:'soft'},
    {k:'B', label:'B_DPD 1–30', group:'soft'},
    {k:'C', label:'C_DPD 31–60', group:'acct'},
    {k:'D', label:'D_DPD 61–90', group:'acct'},
    {k:'E', label:'E_DPD 91–120', group:'acct'},
    {k:'F', label:'F_DPD 121–150', group:'acct'},
    {k:'G', label:'G_DPD 151–180', group:'acct'},
    {k:'H', label:'H_DPD 181–210', group:'acct'},
    {k:'I', label:'I_DPD 211–240', group:'acct'},
    {k:'J', label:'J_DPD 241–270', group:'acct'},
    {k:'K', label:'K_DPD 271–300', group:'acct'},
    {k:'L', label:'L_DPD 301–330', group:'acct'},
    {k:'M', label:'M_DPD 331–360', group:'acct'},
    {k:'N', label:'N_DPD >360', group:'acct'},
  ];
  const SUMMARY = [
    {k:'RE', label:'BUCKET RE', group:'re'},   // agregat C..F
    {k:'BE', label:'BUCKET BE', group:'be'},   // agregat G..N
    {k:'T',  label:'TOTAL',     group:'total'} // agregat A..N
  ];

  /* ======== DUMMY DATA (bisa ganti ke API) ======== */
  const DUMMY_ALL = [
    { kode_kantor:'001', ao:'RMD-001 / Andi',
      buckets:{ A:{noa:12,os:120_000_000},B:{noa:18,os:210_000_000},C:{noa:6,os:95_000_000},
                D:{noa:4,os:80_000_000}, E:{noa:3,os:120_000_000},F:{noa:2,os:70_000_000},
                G:{noa:1,os:45_000_000}, H:{noa:0,os:0}, I:{noa:1,os:25_000_000}, J:{noa:0,os:0},
                K:{noa:1,os:65_000_000}, L:{noa:0,os:0}, M:{noa:0,os:0}, N:{noa:1,os:110_000_000} }
    },
    { kode_kantor:'002', ao:'RMD-002 / Budi',
      buckets:{ A:{noa:8,os:75_000_000}, B:{noa:12,os:130_000_000},C:{noa:7,os:100_000_000},
                D:{noa:2,os:35_000_000}, E:{noa:4,os:95_000_000}, F:{noa:1,os:40_000_000},
                G:{noa:2,os:80_000_000}, H:{noa:1,os:30_000_000}, I:{noa:0,os:0},  J:{noa:1,os:18_000_000},
                K:{noa:0,os:0},          L:{noa:0,os:0},          M:{noa:1,os:22_000_000}, N:{noa:0,os:0} }
    },
    { kode_kantor:'003', ao:'RMD-003 / Chika',
      buckets:{ A:{noa:10,os:95_000_000},B:{noa:15,os:160_000_000},C:{noa:5,os:90_000_000},
                D:{noa:3,os:55_000_000}, E:{noa:2,os:60_000_000}, F:{noa:2,os:55_000_000},
                G:{noa:1,os:25_000_000}, H:{noa:1,os:22_000_000}, I:{noa:1,os:20_000_000}, J:{noa:0,os:0},
                K:{noa:0,os:0},          L:{noa:1,os:35_000_000}, M:{noa:0,os:0},           N:{noa:0,os:0} }
    },
  ];

  /* ======== BUILD HEADER 3-LAPIS ======== */
  (function buildHeader(){
    const r1 = document.getElementById('hdrRow1');
    const r2 = document.getElementById('hdrRow2');
    const r3 = document.getElementById('hdrRow3');

    // Kolom AO kiri (rowspan=3)
    const thAO = document.createElement('th');
    thAO.className = 'sticky-left th-ao';
    thAO.rowSpan = 3;
    thAO.innerHTML = '<span class="vtext">AO REMEDIAL</span>';
    r1.appendChild(thAO);

    const countBy = g => BUCKETS.filter(x=>x.group===g).length * 2;

    // Baris-1: Judul grup
    [['soft','thg-soft','SOFT COLL', countBy('soft')],
     ['acct','thg-acct','ACCOUNT MAPPING', countBy('acct')],
     ['re','thg-re','BUCKET RE', 2],
     ['be','thg-be','BUCKET BE', 2],
     ['total','thg-total','TOTAL', 2]
    ].forEach(([key,cls,title,span])=>{
      const th=document.createElement('th');
      th.className = `th-group ${cls}`;
      th.colSpan = span;
      th.textContent = title;
      r1.appendChild(th);
    });

    // Baris-2: Sub bucket (A..N) + ringkasan RE/BE/TOTAL
    BUCKETS.forEach(b=>{
      const th=document.createElement('th');
      th.className=`th-bucket tbg-${b.group}`;
      th.colSpan=2;
      th.textContent=b.label;
      r2.appendChild(th);
    });
    SUMMARY.forEach(s=>{
      const th=document.createElement('th');
      th.className=`th-bucket tbg-${s.group}`;
      th.colSpan=2;
      th.textContent=s.label;
      r2.appendChild(th);
    });

    // Baris-3: NOA / OSC (untuk setiap bucket + ringkasan)
    const pushNOAOSC = parent=>{
      const th1=document.createElement('th'); th1.className='th-sub'; th1.textContent='NOA';
      const th2=document.createElement('th'); th2.className='th-sub'; th2.textContent='OSC';
      parent.appendChild(th1); parent.appendChild(th2);
    };
    BUCKETS.forEach(()=> pushNOAOSC(r3));
    SUMMARY.forEach(()=> pushNOAOSC(r3));
  })();

  /* ======== RENDER BODY + FOOTER ======== */
  const nfID = new Intl.NumberFormat('id-ID');
  const fmt = n => nfID.format(Number(n||0));
  const num = v => Number(v||0);
  const tbody = document.getElementById('aoBody');

  function sumBuckets(bks, keys){
    return keys.reduce((a,k)=>{
      const x=bks[k]||{noa:0,os:0};
      a.noa+=num(x.noa); a.os+=num(x.os);
      return a;
    },{noa:0,os:0});
  }

  function render(rows){
    tbody.innerHTML='';
    const gt={}; // grand totals

    for(const row of rows){
      const tr=document.createElement('tr');

      // kolom AO (hitam)
      const tdAO=document.createElement('td');
      tdAO.className='sticky-left ao-name';
      tdAO.textContent=row.ao;
      tr.appendChild(tdAO);

      const pushPair=(noa,os)=>{
        const t1=document.createElement('td'); t1.className='num'; t1.textContent=fmt(noa);
        const t2=document.createElement('td'); t2.className='num'; t2.textContent=fmt(os);
        tr.appendChild(t1); tr.appendChild(t2);
      };

      // A..N
      BUCKETS.forEach(b=>{
        const v=row.buckets[b.k]||{noa:0,os:0};
        pushPair(v.noa,v.os);
        gt[`${b.k}_noa`]=(gt[`${b.k}_noa`]||0)+num(v.noa);
        gt[`${b.k}_os`] =(gt[`${b.k}_os`] ||0)+num(v.os);
      });

      // RE (C..F), BE (G..N), T (A..N)
      const re=sumBuckets(row.buckets,['C','D','E','F']);
      const be=sumBuckets(row.buckets,['G','H','I','J','K','L','M','N']);
      const tt=sumBuckets(row.buckets,['A','B','C','D','E','F','G','H','I','J','K','L','M','N']);

      pushPair(re.noa,re.os); pushPair(be.noa,be.os); pushPair(tt.noa,tt.os);

      Object.assign(gt,{
        RE_noa:(gt.RE_noa||0)+re.noa, RE_os:(gt.RE_os||0)+re.os,
        BE_noa:(gt.BE_noa||0)+be.noa, BE_os:(gt.BE_os||0)+be.os,
        T_noa :(gt.T_noa ||0)+tt.noa,  T_os :(gt.T_os ||0)+tt.os
      });

      tbody.appendChild(tr);
    }

    // footer grand total
    const tfootRow=document.querySelector('tfoot tr.grand');
    while (tfootRow.children.length>1) tfootRow.removeChild(tfootRow.lastChild);
    const pushGT=(noa,os)=>{
      const t1=document.createElement('td'); t1.className='num'; t1.textContent=fmt(noa);
      const t2=document.createElement('td'); t2.className='num'; t2.textContent=fmt(os);
      tfootRow.appendChild(t1); tfootRow.appendChild(t2);
    };
    BUCKETS.forEach(b=> pushGT(gt[`${b.k}_noa`]||0, gt[`${b.k}_os`]||0));
    pushGT(gt.RE_noa||0, gt.RE_os||0);
    pushGT(gt.BE_noa||0, gt.BE_os||0);
    pushGT(gt.T_noa ||0, gt.T_os ||0);
  }

  /* ======== DROPDOWN CABANG (dummy; ganti ke fetch ./api/kode/ kalau mau) ======== */
  const optKantor=document.getElementById('opt_kantor');
  (function initCabang(){
    // Dummy isi; kalau mau pakai API:
    // fetch('./api/kode/', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({type:'kode_kantor'})})
    //   .then(r=>r.json()).then(j=>{ ... isi select ... });
    const list=[{kode_kantor:'001',nama:'KC Pusat'}, {kode_kantor:'002',nama:'KC A'}, {kode_kantor:'003',nama:'KC B'}];
    optKantor.innerHTML = `<option value="">Konsolidasi (Semua Cabang)</option>` +
      list.map(x=>`<option value="${x.kode_kantor}">${x.kode_kantor} — ${x.nama}</option>`).join('');
  })();

  document.getElementById('formAoFilter').addEventListener('submit',e=>{
    e.preventDefault();
    const kode=optKantor.value||'';
    const rows = kode ? DUMMY_ALL.filter(x=>x.kode_kantor===kode) : DUMMY_ALL.slice();
    render(rows);
  });

  // initial render (konsolidasi)
  render(DUMMY_ALL);
</script>

<script>
(function(){
  // ===== Dummy PLAN vs ACTUAL (ikut kode_kantor & ao) =====
  const DUMMY_PA_ALL = [
    { kode_kantor:'001', ao:'RMD-001 / Andi',
      plan:   { btc:{noa:5, os:50_000_000}, back:{noa:2, os:20_000_000}, stay:{noa:10, os:95_000_000}, flow:{noa:3, os:30_000_000}, lunas:{noa:1, os:8_000_000} },
      actual: { btc:{noa:6, os:62_000_000}, back:{noa:1, os:10_000_000}, stay:{noa:9,  os:90_000_000}, flow:{noa:4, os:38_000_000}, lunas:{noa:1, os:9_000_000} }
    },
    { kode_kantor:'002', ao:'RMD-002 / Budi',
      plan:   { btc:{noa:4, os:35_000_000}, back:{noa:1, os:12_000_000}, stay:{noa:8,  os:70_000_000}, flow:{noa:2, os:22_000_000}, lunas:{noa:1, os:6_000_000} },
      actual: { btc:{noa:5, os:40_000_000}, back:{noa:2, os:15_000_000}, stay:{noa:7,  os:66_000_000}, flow:{noa:3, os:28_000_000}, lunas:{noa:1, os:7_000_000} }
    },
    { kode_kantor:'003', ao:'RMD-003 / Chika',
      plan:   { btc:{noa:3, os:28_000_000}, back:{noa:1, os:9_000_000},  stay:{noa:9,  os:88_000_000}, flow:{noa:2, os:18_000_000}, lunas:{noa:1, os:5_000_000} },
      actual: { btc:{noa:3, os:29_000_000}, back:{noa:1, os:8_000_000},  stay:{noa:10, os:92_000_000}, flow:{noa:1, os:12_000_000}, lunas:{noa:1, os:6_000_000} }
    }
  ];

  const nf = new Intl.NumberFormat('id-ID');
  const fmt = n => nf.format(Number(n||0));
  const num = v => Number(v||0);

  const paBody = document.getElementById('paBody');

  function renderPA(rows){
    paBody.innerHTML = '';

    // grand totals per cell, urutan sama dengan header: PLAN[btc,back,stay,flow,lunas] x (noa,osc), ACTUAL [...]
    const gtKeys = [
      'p_btc_noa','p_btc_os','p_back_noa','p_back_os','p_stay_noa','p_stay_os','p_flow_noa','p_flow_os','p_lunas_noa','p_lunas_os',
      'a_btc_noa','a_btc_os','a_back_noa','a_back_os','a_stay_noa','a_stay_os','a_flow_noa','a_flow_os','a_lunas_noa','a_lunas_os'
    ];
    const GT = Object.fromEntries(gtKeys.map(k=>[k,0]));

    const pushPair = (tr, noa, os, collectKeyPrefix) => {
      const c1=document.createElement('td'); c1.className='num'; c1.textContent=fmt(noa);
      const c2=document.createElement('td'); c2.className='num'; c2.textContent=fmt(os);
      tr.appendChild(c1); tr.appendChild(c2);
      if (collectKeyPrefix){
        GT[collectKeyPrefix+'_noa'] += num(noa);
        GT[collectKeyPrefix+'_os']  += num(os);
      }
    };

    for(const r of rows){
      const tr=document.createElement('tr');

      const tdAO=document.createElement('td');
      tdAO.className='sticky-left ao-name';
      tdAO.textContent=r.ao;
      tr.appendChild(tdAO);

      // PLAN
      pushPair(tr, r.plan.btc.noa,   r.plan.btc.os,   'p_btc');
      pushPair(tr, r.plan.back.noa,  r.plan.back.os,  'p_back');
      pushPair(tr, r.plan.stay.noa,  r.plan.stay.os,  'p_stay');
      pushPair(tr, r.plan.flow.noa,  r.plan.flow.os,  'p_flow');
      pushPair(tr, r.plan.lunas.noa, r.plan.lunas.os, 'p_lunas');

      // ACTUAL
      pushPair(tr, r.actual.btc.noa,   r.actual.btc.os,   'a_btc');
      pushPair(tr, r.actual.back.noa,  r.actual.back.os,  'a_back');
      pushPair(tr, r.actual.stay.noa,  r.actual.stay.os,  'a_stay');
      pushPair(tr, r.actual.flow.noa,  r.actual.flow.os,  'a_flow');
      pushPair(tr, r.actual.lunas.noa, r.actual.lunas.os, 'a_lunas');

      paBody.appendChild(tr);
    }

    // footer
    const tfootRow = document.querySelector('#tblPlanActual tfoot tr');
    while (tfootRow.children.length>1) tfootRow.removeChild(tfootRow.lastChild);

    const addGT=(k)=>{ const td1=document.createElement('td'); td1.className='num'; td1.textContent=fmt(GT[k+'_noa']);
                       const td2=document.createElement('td'); td2.className='num'; td2.textContent=fmt(GT[k+'_os']); tfootRow.appendChild(td1); tfootRow.appendChild(td2); };

    ['p_btc','p_back','p_stay','p_flow','p_lunas','a_btc','a_back','a_stay','a_flow','a_lunas'].forEach(addGT);
  }

  // initial render (konsolidasi)
  renderPA(DUMMY_PA_ALL);

  // ikut dropdown cabang yang sama
  const form = document.getElementById('formAoFilter');
  form && form.addEventListener('submit', (e)=>{
    e.preventDefault();
    const kode = (document.getElementById('opt_kantor')?.value)||'';
    const rows = kode ? DUMMY_PA_ALL.filter(x=>x.kode_kantor===kode) : DUMMY_PA_ALL.slice();
    renderPA(rows);
  });
})();
</script>

<script>
(() => {
  // ===== Buckets CKPN (urutan harus sama dgn header) =====
  const BUCKS = [
    {k:'btc',  label:'BTC'},
    {k:'back', label:'BACK FLOW'},
    {k:'stay', label:'STAY'},
    {k:'flow', label:'FLOW'},
    {k:'lunas',label:'LUNAS'},
  ];

  // ===== Dummy CKPN data (ikut kode_kantor) =====
  const DUMMY_CKPN_ALL = [
    { kode_kantor:'001', ao:'RMD-001 / Andi',
      plan:{  btc:{noa:5, ck:5_000_000},  back:{noa:2, ck:2_000_000},  stay:{noa:10, ck:35_000_000}, flow:{noa:3, ck:3_000_000},  lunas:{noa:1, ck:1_000_000} },
      actual:{btc:{noa:6, ck:6_200_000},  back:{noa:1, ck:1_000_000},  stay:{noa:9,  ck:33_000_000}, flow:{noa:4, ck:3_800_000},  lunas:{noa:1, ck:1_100_000} }
    },
    { kode_kantor:'002', ao:'RMD-002 / Budi',
      plan:{  btc:{noa:4, ck:3_500_000},  back:{noa:1, ck:1_200_000},  stay:{noa:8,  ck:27_000_000}, flow:{noa:2, ck:2_200_000},  lunas:{noa:1, ck:600_000} },
      actual:{btc:{noa:5, ck:4_000_000},  back:{noa:2, ck:1_500_000},  stay:{noa:7,  ck:26_000_000}, flow:{noa:3, ck:2_800_000},  lunas:{noa:1, ck:700_000} }
    },
    { kode_kantor:'003', ao:'RMD-003 / Chika',
      plan:{  btc:{noa:3, ck:2_800_000},  back:{noa:1, ck:900_000},   stay:{noa:9,  ck:25_000_000}, flow:{noa:2, ck:1_800_000},  lunas:{noa:1, ck:500_000} },
      actual:{btc:{noa:3, ck:2_900_000},  back:{noa:1, ck:800_000},   stay:{noa:10, ck:26_000_000}, flow:{noa:1, ck:1_200_000},  lunas:{noa:1, ck:600_000} }
    }
  ];

  const nfID = new Intl.NumberFormat('id-ID');
  const fmt = n => nfID.format(Number(n||0));
  const num = v => Number(v||0);

  const tbody = document.getElementById('ckBody');

  function sumCk(obj){ return BUCKS.reduce((a,b)=> a + num(obj[b.k]?.ck || 0), 0); }

  function renderCKPN(rows){
    tbody.innerHTML = '';

    // Grand totals: plan bucket pairs + plan total, actual bucket pairs + actual total
    const GT = {
      // plan pairs
      p_btc_noa:0,p_btc_ck:0, p_back_noa:0,p_back_ck:0, p_stay_noa:0,p_stay_ck:0, p_flow_noa:0,p_flow_ck:0, p_lunas_noa:0,p_lunas_ck:0, p_total_ck:0,
      // actual pairs
      a_btc_noa:0,a_btc_ck:0, a_back_noa:0,a_back_ck:0, a_stay_noa:0,a_stay_ck:0, a_flow_noa:0,a_flow_ck:0, a_lunas_noa:0,a_lunas_ck:0, a_total_ck:0,
    };

    const pushPair = (tr, noa, ck) => {
      const t1=document.createElement('td'); t1.className='num'; t1.textContent=fmt(noa);
      const t2=document.createElement('td'); t2.className='num'; t2.textContent=fmt(ck);
      tr.appendChild(t1); tr.appendChild(t2);
    };

    for(const r of rows){
      const tr=document.createElement('tr');

      const tdAO=document.createElement('td');
      tdAO.className='sticky-left ao-name';
      tdAO.textContent=r.ao;
      tr.appendChild(tdAO);

      // PLAN buckets
      BUCKS.forEach(b=>{
        const v=r.plan[b.k]||{noa:0,ck:0};
        pushPair(tr, v.noa, v.ck);
        GT[`p_${b.k}_noa`]+=num(v.noa);
        GT[`p_${b.k}_ck` ]+=num(v.ck);
      });
      const pTotal = sumCk(r.plan);
      const tdPT=document.createElement('td'); tdPT.className='num'; tdPT.textContent=fmt(pTotal);
      tr.appendChild(tdPT);
      GT.p_total_ck += pTotal;

      // ACTUAL buckets
      BUCKS.forEach(b=>{
        const v=r.actual[b.k]||{noa:0,ck:0};
        pushPair(tr, v.noa, v.ck);
        GT[`a_${b.k}_noa`]+=num(v.noa);
        GT[`a_${b.k}_ck` ]+=num(v.ck);
      });
      const aTotal = sumCk(r.actual);
      const tdAT=document.createElement('td'); tdAT.className='num'; tdAT.textContent=fmt(aTotal);
      tr.appendChild(tdAT);
      GT.a_total_ck += aTotal;

      tbody.appendChild(tr);
    }

    // Footer
    const tf = document.querySelector('#tblCkpn tfoot tr');
    while (tf.children.length>1) tf.removeChild(tf.lastChild);

    const addPair = (n, c) => {
      const t1=document.createElement('td'); t1.className='num'; t1.textContent=fmt(n);
      const t2=document.createElement('td'); t2.className='num'; t2.textContent=fmt(c);
      tf.appendChild(t1); tf.appendChild(t2);
    };

    // plan pairs + total
    addPair(GT.p_btc_noa,  GT.p_btc_ck);
    addPair(GT.p_back_noa, GT.p_back_ck);
    addPair(GT.p_stay_noa, GT.p_stay_ck);
    addPair(GT.p_flow_noa, GT.p_flow_ck);
    addPair(GT.p_lunas_noa,GT.p_lunas_ck);
    { const td=document.createElement('td'); td.className='num'; td.textContent=fmt(GT.p_total_ck); tf.appendChild(td); }

    // actual pairs + total
    addPair(GT.a_btc_noa,  GT.a_btc_ck);
    addPair(GT.a_back_noa, GT.a_back_ck);
    addPair(GT.a_stay_noa, GT.a_stay_ck);
    addPair(GT.a_flow_noa, GT.a_flow_ck);
    addPair(GT.a_lunas_noa,GT.a_lunas_ck);
    { const td=document.createElement('td'); td.className='num'; td.textContent=fmt(GT.a_total_ck); tf.appendChild(td); }
  }

  // initial render (konsolidasi)
  renderCKPN(DUMMY_CKPN_ALL);

  // ikut dropdown cabang yang sama (#formAoFilter)
  const form = document.getElementById('formAoFilter');
  form && form.addEventListener('submit', (e)=>{
    e.preventDefault();
    const kode = (document.getElementById('opt_kantor')?.value)||'';
    const rows = kode ? DUMMY_CKPN_ALL.filter(x=>x.kode_kantor===kode) : DUMMY_CKPN_ALL.slice();
    renderCKPN(rows);
  });
})();
</script>