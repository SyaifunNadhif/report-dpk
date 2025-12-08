<!-- ================== REKAP CKPN (Bucket) — thead sticky, tinggi tabel dinamis ================== -->
<div class="max-w-[100vw] px-4 py-4">
  <h2 class="page-title text-xl font-bold mb-3">📊 Rekap CKPN — Bucket vs Plan / Actual</h2>

  <!-- Toolbar Filter -->
  <form id="formCkpnRekap" class="toolbar mb-2">
    <div class="toolbar-row">
      <label for="closing_date">Closing Date</label>
      <input type="date" id="closing_date" required title="Closing Date">
    </div>
    <div class="toolbar-row">
      <label for="harian_date">Harian Date</label>
      <input type="date" id="harian_date" required title="Harian Date">
    </div>
    <div class="toolbar-row">
      <label for="kode_kantor">Cabang</label>
      <select id="kode_kantor" class="min-w-[180px]" title="Pilih Cabang">
        <option value="">Konsolidasi (Semua Cabang)</option>
      </select>
    </div>
    <button class="btn-primary" type="submit">Tampilkan</button>
  </form>

  <div class="text-sm text-slate-600 mb-3" id="subtitleInfo"></div>

  <!-- SCROLLER: tinggi dinamis, hanya di sini yang scroll -->
  <div id="ckpnWrap" class="table-wrap">
    <table class="ao-table" id="tblCkpnRekap">
      <thead>
        <!-- Row-1: Title -->
        <tr class="cr-r1">
          <!-- BUCKET: rowspan=3 (tidak freeze kiri), hanya freeze header -->
          <th rowspan="3" class="th-bucket-merged">BUCKET</th>

          <th class="th-cr-title" id="openTitle" colspan="2">Data M-1 s/d …</th>
          <th class="th-group thg-plan-ck"   colspan="16" id="planTitle">PLAN CKPN</th>
          <th class="th-group thg-actual-ck" colspan="16" id="actualTitle">ACTUAL CKPN</th>
        </tr>

        <!-- Row-2: Groups -->
        <tr class="cr-r2">
          <th class="th-bucket tbg-open" colspan="2">OPENING</th>

          <th class="th-bucket tbg-btc"   colspan="3">BTC</th>
          <th class="th-bucket tbg-back"  colspan="3">BACK FLOW</th>
          <th class="th-bucket tbg-stay"  colspan="3">STAY</th>
          <th class="th-bucket tbg-flow"  colspan="3">FLOW</th>
          <th class="th-bucket tbg-lunas" colspan="3">LUNAS</th>
          <th class="th-bucket tbg-total" colspan="1">TOTAL CKPN</th>

          <th class="th-bucket tbg-btc"   colspan="3">BTC</th>
          <th class="th-bucket tbg-back"  colspan="3">BACK FLOW</th>
          <th class="th-bucket tbg-stay"  colspan="3">STAY</th>
          <th class="th-bucket tbg-flow"  colspan="3">FLOW</th>
          <th class="th-bucket tbg-lunas" colspan="3">LUNAS</th>
          <th class="th-bucket tbg-total" colspan="1">TOTAL CKPN</th>
        </tr>

        <!-- Row-3: Sub labels -->
        <tr class="cr-r3">
          <!-- OPENING -->
          <th class="th-sub">NOA</th><th class="th-sub">CKPN</th>

          <!-- PLAN -->
          <th class="th-sub">NOA</th><th class="th-sub">CKPN</th><th class="th-sub">%</th>
          <th class="th-sub">NOA</th><th class="th-sub">CKPN</th><th class="th-sub">%</th>
          <th class="th-sub">NOA</th><th class="th-sub">CKPN</th><th class="th-sub">%</th>
          <th class="th-sub">NOA</th><th class="th-sub">CKPN</th><th class="th-sub">%</th>
          <th class="th-sub">NOA</th><th class="th-sub">CKPN</th><th class="th-sub">%</th>
          <th class="th-sub">CKPN</th>

          <!-- ACTUAL -->
          <th class="th-sub">NOA</th><th class="th-sub">CKPN</th><th class="th-sub">%</th>
          <th class="th-sub">NOA</th><th class="th-sub">CKPN</th><th class="th-sub">%</th>
          <th class="th-sub">NOA</th><th class="th-sub">CKPN</th><th class="th-sub">%</th>
          <th class="th-sub">NOA</th><th class="th-sub">CKPN</th><th class="th-sub">%</th>
          <th class="th-sub">NOA</th><th class="th-sub">CKPN</th><th class="th-sub">%</th>
          <th class="th-sub">CKPN</th>
        </tr>
      </thead>

      <tbody id="ckpnRekapBody"></tbody>

      <tfoot>
        <tr class="band band-red">
          <td class="font-semibold">TOTAL FE (C–F)</td>
        </tr>
        <tr class="band band-red">
          <td class="font-semibold">TOTAL BE (G–N)</td>
        </tr>
        <tr class="grand">
          <td class="font-bold">GRAND TOTAL</td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<style>
  /* Tabel sticky + freeze */
  body{ overflow:hidden; }

  :root{
    --bd:#e5e7eb; --txt:#111827; --crH:34px;

    /* lebar kolom angka (dipersempit) */
    --w-noa: 6.2ch;
    --w-ck:  10.5ch;
    --w-pct: 5.6ch;
    --w-tot: 10.5ch;
  }

  .btn-primary{ background:#16a34a; color:#fff; padding:.5rem .85rem; border-radius:.6rem; border:1px solid #15803d; }
  .btn-primary:hover{ background:#15803d; }

  .toolbar{ display:flex; flex-wrap:wrap; gap:.6rem 1rem; align-items:end; }
  .toolbar-row{ display:flex; flex-direction:column; gap:.25rem; }
  .toolbar-row label{ font-size:.8rem; color:#475569; }
  .toolbar-row input,.toolbar-row select{ border:1px solid var(--bd); border-radius:.55rem; padding:.38rem .6rem; font-size:.9rem; }

  .table-wrap{
    overflow:auto;                     /* scroll di sini */
    border:1px solid var(--bd);
    border-radius:10px;
    background:#fff;
    height: 60vh;                      /* fallback, diubah via JS */
  }

  .ao-table{ border-collapse:separate; border-spacing:0; min-width:1200px; width:max-content; }
  th,td{
    border:1px solid var(--bd);
    padding:4px 6px;
    background:#fff; color:var(--txt);
    font:12.5px/1.25 system-ui,Segoe UI,Roboto,Arial;
    white-space:nowrap;
    font-variant-numeric: tabular-nums;
  }
  .num{ text-align:right; font-variant-numeric: tabular-nums; }

  /* ===== Sticky THEAD (3 baris) — relatif ke #ckpnWrap (top:0) ===== */
  #tblCkpnRekap thead th{ position:sticky; z-index:30; background-clip:padding-box; }
  #tblCkpnRekap thead .cr-r1 th{ top:0;                 height:var(--crH); }
  #tblCkpnRekap thead .cr-r2 th{ top:var(--crH);        height:var(--crH); }
  #tblCkpnRekap thead .cr-r3 th{ top:calc(var(--crH)*2);height:var(--crH); }

  /* BUCKET (header) — hanya freeze thead-nya, bukan kolom kiri body */
  #tblCkpnRekap .th-bucket-merged{
    top:0; height:calc(var(--crH)*3);
    width:150px; min-width:150px;
    background:#16a34a; color:#fff; text-align:center; font-weight:800;
  }

  /* Warna header */
  .th-cr-title{ background:#d9ead3; color:#0f5132; text-align:center; font-weight:800; }
  .th-group{ text-align:center; font-weight:800; }
  .thg-plan-ck{   background:#bfe7bf; color:#0f5132; }
  .thg-actual-ck{ background:#9dd49d; color:#0f5132; }
  .th-bucket{ font-weight:700; text-align:center; }
  .tbg-open{  background:#eef7ff; }
  .tbg-btc{   background:#e6f0ff; }
  .tbg-back{  background:#dcfce7; }
  .tbg-stay{  background:#ffe6d8; }
  .tbg-flow{  background:#fdebd2; }
  .tbg-lunas{ background:#f3f4f6; }
  .tbg-total{ background:#fde3e3; font-weight:800; }
  .th-sub{ background:#f8fafc; text-align:center; font-size:12px; }

  tbody tr:hover td{ background:#f9fafb; }
  .band td{ background:#fffbea; font-weight:700; }
  .band-red td{ background:#fde8e8; }
  tfoot .grand td{ background:#fef3c7; font-weight:800; }

  /* ===== PERSEMPIT kolom angka (sinkron header & body) ===== */
  /* OPEN NOA/CK */
  #tblCkpnRekap .cr-r3 th:nth-child(1),
  #tblCkpnRekap tbody td:nth-child(2),
  #tblCkpnRekap tfoot td:nth-child(2){ width:var(--w-noa); min-width:var(--w-noa); }
  #tblCkpnRekap .cr-r3 th:nth-child(2),
  #tblCkpnRekap tbody td:nth-child(3),
  #tblCkpnRekap tfoot td:nth-child(3){ width:var(--w-ck); min-width:var(--w-ck); text-align:right; }

  /* PLAN (NOA, CK, %) × 5 + TOTAL */
  #tblCkpnRekap tbody td:nth-child(4),
  #tblCkpnRekap tbody td:nth-child(7),
  #tblCkpnRekap tbody td:nth-child(10),
  #tblCkpnRekap tbody td:nth-child(13),
  #tblCkpnRekap tbody td:nth-child(16),
  #tblCkpnRekap tfoot td:nth-child(4),
  #tblCkpnRekap tfoot td:nth-child(7),
  #tblCkpnRekap tfoot td:nth-child(10),
  #tblCkpnRekap tfoot td:nth-child(13),
  #tblCkpnRekap tfoot td:nth-child(16){ width:var(--w-noa); min-width:var(--w-noa); }

  #tblCkpnRekap tbody td:nth-child(5),
  #tblCkpnRekap tbody td:nth-child(8),
  #tblCkpnRekap tbody td:nth-child(11),
  #tblCkpnRekap tbody td:nth-child(14),
  #tblCkpnRekap tbody td:nth-child(17),
  #tblCkpnRekap tfoot td:nth-child(5),
  #tblCkpnRekap tfoot td:nth-child(8),
  #tblCkpnRekap tfoot td:nth-child(11),
  #tblCkpnRekap tfoot td:nth-child(14),
  #tblCkpnRekap tfoot td:nth-child(17){ width:var(--w-ck); min-width:var(--w-ck); text-align:right; }

  #tblCkpnRekap tbody td:nth-child(6),
  #tblCkpnRekap tbody td:nth-child(9),
  #tblCkpnRekap tbody td:nth-child(12),
  #tblCkpnRekap tbody td:nth-child(15),
  #tblCkpnRekap tbody td:nth-child(18),
  #tblCkpnRekap tfoot td:nth-child(6),
  #tblCkpnRekap tfoot td:nth-child(9),
  #tblCkpnRekap tfoot td:nth-child(12),
  #tblCkpnRekap tfoot td:nth-child(15),
  #tblCkpnRekap tfoot td:nth-child(18){ width:var(--w-pct); min-width:var(--w-pct); text-align:right; }

  #tblCkpnRekap tbody td:nth-child(19),
  #tblCkpnRekap tfoot td:nth-child(19){ width:var(--w-tot); min-width:var(--w-tot); text-align:right; font-weight:700; }

  /* ACTUAL (kolom ke-20..35) */
  #tblCkpnRekap tbody td:nth-child(20),
  #tblCkpnRekap tbody td:nth-child(23),
  #tblCkpnRekap tbody td:nth-child(26),
  #tblCkpnRekap tbody td:nth-child(29),
  #tblCkpnRekap tbody td:nth-child(32),
  #tblCkpnRekap tfoot td:nth-child(20),
  #tblCkpnRekap tfoot td:nth-child(23),
  #tblCkpnRekap tfoot td:nth-child(26),
  #tblCkpnRekap tfoot td:nth-child(29),
  #tblCkpnRekap tfoot td:nth-child(32){ width:var(--w-noa); min-width:var(--w-noa); }

  #tblCkpnRekap tbody td:nth-child(21),
  #tblCkpnRekap tbody td:nth-child(24),
  #tblCkpnRekap tbody td:nth-child(27),
  #tblCkpnRekap tbody td:nth-child(30),
  #tblCkpnRekap tbody td:nth-child(33),
  #tblCkpnRekap tfoot td:nth-child(21),
  #tblCkpnRekap tfoot td:nth-child(24),
  #tblCkpnRekap tfoot td:nth-child(27),
  #tblCkpnRekap tfoot td:nth-child(30),
  #tblCkpnRekap tfoot td:nth-child(33){ width:var(--w-ck); min-width:var(--w-ck); text-align:right; }

  #tblCkpnRekap tbody td:nth-child(22),
  #tblCkpnRekap tbody td:nth-child(25),
  #tblCkpnRekap tbody td:nth-child(28),
  #tblCkpnRekap tbody td:nth-child(31),
  #tblCkpnRekap tbody td:nth-child(34),
  #tblCkpnRekap tfoot td:nth-child(22),
  #tblCkpnRekap tfoot td:nth-child(25),
  #tblCkpnRekap tfoot td:nth-child(28),
  #tblCkpnRekap tfoot td:nth-child(31),
  #tblCkpnRekap tfoot td:nth-child(34){ width:var(--w-pct); min-width:var(--w-pct); text-align:right; }

  #tblCkpnRekap tbody td:nth-child(35),
  #tblCkpnRekap tfoot td:nth-child(35){ width:var(--w-tot); min-width:var(--w-tot); text-align:right; font-weight:700; }

  /* ===== Mobile: sembunyikan teks judul & label, padatkan control ===== */
  @media (max-width:640px){
    .page-title{ display:none; }
    #subtitleInfo{ display:none; }
    .toolbar-row label{ display:none; }
    .toolbar{ gap:.5rem .6rem; }
    .toolbar-row input,.toolbar-row select{ font-size:.85rem; padding:.35rem .5rem; }
    .btn-primary{ padding:.45rem .7rem; font-size:.85rem; }
    .ao-table{ min-width:1000px; }
    .th-bucket-merged{ width:130px; min-width:130px; }
    :root{ --crH:32px; --w-noa:5.2ch; --w-ck:9ch; --w-pct:5ch; --w-tot:9ch; }
  }
</style>

<script>
(function(){
  // ===== helpers =====
  const nf = new Intl.NumberFormat('id-ID');
  const fmt = n => nf.format(Number(n||0));
  const pct = (x,base) => base>0 ? ((100*Number(x)/base).toFixed(2)+'%') : '0,00%';

  const pad3 = n => String(n).padStart(3,'0');
  const lastDayPrevMonth = () => { const t=new Date(); return new Date(t.getFullYear(), t.getMonth(), 0); };
  const yyyy_mm_dd = d => { const dt=(d instanceof Date)? d:new Date(d);
    return `${dt.getFullYear()}-${String(dt.getMonth()+1).padStart(2,'0')}-${String(dt.getDate()).padStart(2,'0')}`; };
  const prettyID = (d,word=false)=>new Intl.DateTimeFormat('id-ID',{day:'2-digit',month:word?'long':'2-digit',year:'numeric'}).format(new Date(d));

  const BUCKETS = [
    {k:'A', label:'A_DPD 0'},{k:'B', label:'B_DPD 1–30'},{k:'C', label:'C_DPD 31–60'},{k:'D', label:'D_DPD 61–90'},
    {k:'E', label:'E_DPD 91–120'},{k:'F', label:'F_DPD 121–150'},{k:'G', label:'G_DPD 151–180'},{k:'H', label:'H_DPD 181–210'},
    {k:'I', label:'I_DPD 211–240'},{k:'J', label:'J_DPD 241–270'},{k:'K', label:'K_DPD 271–300'},{k:'L', label:'L_DPD 301–330'},
    {k:'M', label:'M_DPD 331–360'},{k:'N', label:'N_DPD >360'}
  ];

  // RNG supaya dummy konsisten
  function seedFrom(str){ let h=2166136261>>>0; for(let i=0;i<str.length;i++){ h^=str.charCodeAt(i); h=Math.imul(h,16777619);} return h>>>0;}
  function makeRand(seed){ let s=seed>>>0; return ()=>{ s=(Math.imul(48271,s)&0x7fffffff); return s/0x7fffffff; }; }
  const rBetween=(rnd,a,b)=> a+rnd()*(b-a);

  function buildDummy({closing, harian, kode}){
    const seed=seedFrom(`${closing}|${harian}|${kode||'ALL'}`), rnd=makeRand(seed);
    const data={};
    BUCKETS.forEach((b,i)=>{
      const baseNoa=Math.max(0,Math.round(rBetween(rnd,5,80)-i*1.2));
      const baseCk =Math.max(0,Math.round(rBetween(rnd,5_000_000,150_000_000)-i*1_100_000));
      const mk=(nRate,cRate)=>({noa:Math.round(baseNoa*(nRate*rBetween(rnd,.7,1.2))), ck:Math.round(baseCk*(cRate*rBetween(rnd,.7,1.2)))});
      data[b.k]={ opening:{noa:baseNoa, ck:baseCk},
                  plan:{btc:mk(.10,.08), back:mk(.06,.07), stay:mk(.18,.20), flow:mk(.05,.06), lunas:mk(.02,.02)},
                  actual:{btc:mk(.11,.09), back:mk(.05,.05), stay:mk(.20,.19), flow:mk(.06,.05), lunas:mk(.02,.02)} };
    });
    return data;
  }

  const body  = document.getElementById('ckpnRekapBody');
  const tfoot = document.querySelector('#tblCkpnRekap tfoot');
  const [trFE,trBE,trGT] = tfoot.querySelectorAll('tr');

  function pushCells(tr, arr){ arr.forEach(v=>{ const td=document.createElement('td'); td.className='num'; td.textContent=v; tr.appendChild(td); }); }
  function rowValues(D){
    const p=D.plan,a=D.actual, base=D.opening.ck||0;
    const pTot=p.btc.ck+p.back.ck+p.stay.ck+p.flow.ck+p.lunas.ck;
    const aTot=a.btc.ck+a.back.ck+a.stay.ck+a.flow.ck+a.lunas.ck;
    return [
      fmt(D.opening.noa), fmt(D.opening.ck),

      fmt(p.btc.noa),fmt(p.btc.ck),pct(p.btc.ck,base),
      fmt(p.back.noa),fmt(p.back.ck),pct(p.back.ck,base),
      fmt(p.stay.noa),fmt(p.stay.ck),pct(p.stay.ck,base),
      fmt(p.flow.noa),fmt(p.flow.ck),pct(p.flow.ck,base),
      fmt(p.lunas.noa),fmt(p.lunas.ck),pct(p.lunas.ck,base),
      fmt(pTot),

      fmt(a.btc.noa),fmt(a.btc.ck),pct(a.btc.ck,base),
      fmt(a.back.noa),fmt(a.back.ck),pct(a.back.ck,base),
      fmt(a.stay.noa),fmt(a.stay.ck),pct(a.stay.ck,base),
      fmt(a.flow.noa),fmt(a.flow.ck),pct(a.flow.ck,base),
      fmt(a.lunas.noa),fmt(a.lunas.ck),pct(a.lunas.ck,base),
      fmt(aTot)
    ];
  }
  function accumulate(keys, DATA){
    const acc={openNoa:0,openCk:0,p:{btc:{n:0,c:0},back:{n:0,c:0},stay:{n:0,c:0},flow:{n:0,c:0},lunas:{n:0,c:0},total:0},
                                    a:{btc:{n:0,c:0},back:{n:0,c:0},stay:{n:0,c:0},flow:{n:0,c:0},lunas:{n:0,c:0},total:0}};
    keys.forEach(k=>{ const d=DATA[k]; acc.openNoa+=d.opening.noa; acc.openCk+=d.opening.ck;
      ['btc','back','stay','flow','lunas'].forEach(g=>{ acc.p[g].n+=d.plan[g].noa; acc.p[g].c+=d.plan[g].ck; acc.a[g].n+=d.actual[g].noa; acc.a[g].c+=d.actual[g].ck; });
    });
    acc.p.total=['btc','back','stay','flow','lunas'].reduce((s,g)=>s+acc.p[g].c,0);
    acc.a.total=['btc','back','stay','flow','lunas'].reduce((s,g)=>s+acc.a[g].c,0);
    return acc;
  }
  function render(DATA){
    body.innerHTML='';
    BUCKETS.forEach(b=>{
      const tr=document.createElement('tr');
      const tdL=document.createElement('td'); tdL.textContent=b.label; tr.appendChild(tdL);
      pushCells(tr, rowValues(DATA[b.k])); body.appendChild(tr);
    });

    const bands=[['C','D','E','F'], ['G','H','I','J','K','L','M','N'], BUCKETS.map(x=>x.k)];
    [trFE,trBE,trGT].forEach((host,idx)=>{
      const acc=accumulate(bands[idx], DATA), base=acc.openCk||0;
      const r=[
        fmt(acc.openNoa),fmt(acc.openCk),
        fmt(acc.p.btc.n),fmt(acc.p.btc.c),pct(acc.p.btc.c,base),
        fmt(acc.p.back.n),fmt(acc.p.back.c),pct(acc.p.back.c,base),
        fmt(acc.p.stay.n),fmt(acc.p.stay.c),pct(acc.p.stay.c,base),
        fmt(acc.p.flow.n),fmt(acc.p.flow.c),pct(acc.p.flow.c,base),
        fmt(acc.p.lunas.n),fmt(acc.p.lunas.c),pct(acc.p.lunas.c,base), fmt(acc.p.total),
        fmt(acc.a.btc.n),fmt(acc.a.btc.c),pct(acc.a.btc.c,base),
        fmt(acc.a.back.n),fmt(acc.a.back.c),pct(acc.a.back.c,base),
        fmt(acc.a.stay.n),fmt(acc.a.stay.c),pct(acc.a.stay.c,base),
        fmt(acc.a.flow.n),fmt(acc.a.flow.c),pct(acc.a.flow.c,base),
        fmt(acc.a.lunas.n),fmt(acc.a.lunas.c),pct(acc.a.lunas.c,base), fmt(acc.a.total)
      ];
      while (host.children.length>1) host.removeChild(host.lastChild);
      r.forEach(v=>{ const td=document.createElement('td'); td.className='num'; td.textContent=v; host.appendChild(td); });
    });
  }

  // dropdown 001–028
  const sel=document.getElementById('kode_kantor');
  for(let i=1;i<=28;i++){ const v=pad3(i); const o=document.createElement('option'); o.value=v; o.textContent=`${v} — Cabang ${v}`; sel.appendChild(o); }

  // defaults
  const closingDefault=yyyy_mm_dd(lastDayPrevMonth());
  const harianDefault=yyyy_mm_dd(new Date());
  closing_date.value=closingDefault; harian_date.value=harianDefault;

  updateTitles(); rerender();

  formCkpnRekap.addEventListener('submit', e=>{ e.preventDefault(); updateTitles(); rerender(); });

  function updateTitles(){
    const closing=closing_date.value, harian=harian_date.value, kode=kode_kantor.value || 'ALL';
    openTitle.textContent   = 'Data M-1 s/d ' + prettyID(closing, true);
    actualTitle.textContent = 'ACTUAL CKPN Per ' + prettyID(harian, true);
    subtitleInfo.textContent = `Menampilkan: ${(kode==='ALL'?'Konsolidasi':`Cabang ${kode}`)} — Closing: ${prettyID(closing,true)} | Harian: ${prettyID(harian,true)}`;
  }
  function rerender(){
    const DATA = buildDummy({closing:closing_date.value, harian:harian_date.value, kode:kode_kantor.value||''});
    render(DATA);
    sizeWrap();                           // pastikan tinggi scroller pas
    setTimeout(sizeWrap, 30);
  }

  // ===== Atur tinggi scroller agar baris terbawah kelihatan =====
  function sizeWrap(){
    const wrap=document.getElementById('ckpnWrap'); if(!wrap) return;
    const top = wrap.getBoundingClientRect().top;   // posisi dari atas viewport
    const pad = 10;                                  // jarak bawah
    wrap.style.height = Math.max(260, window.innerHeight - top - pad) + 'px';
  }
  window.addEventListener('load', sizeWrap);
  window.addEventListener('resize', sizeWrap);
})();
</script>
