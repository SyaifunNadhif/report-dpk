<!-- ================== CKPN DASHBOARD - layout mirip Excel ================== -->
<div class="ckpn-wrap">
  <h2 class="page-title">📊 CKPN — Plan, Actual & KOL</h2>

  <!-- Toolbar -->
  <form id="ckpnToolbar" class="toolbar">
    <div class="trow">
      <label>Closing Date</label>
      <input type="date" id="closing_date" required />
    </div>
    <div class="trow">
      <label>Harian Date</label>
      <input type="date" id="harian_date" required />
    </div>
    <div class="trow">
      <label>Cabang</label>
      <select id="kode_kantor" class="min-w-[200px]">
        <option value="">Konsolidasi (Semua Cabang)</option>
      </select>
    </div>
    <button class="btn-primary">Tampilkan</button>
  </form>

  <div id="subtitle" class="subtitle"></div>

  <!-- ===== GRID ===== -->
  <div class="grid">
    <!-- PLAN + CKPN (atas) -->
    <section class="card card-wide">
      <header class="card-h">
        <div class="card-title">
          <div class="badge">PLAN</div>
          <div>
            <div class="tt">PLAN & CKPN</div>
            <div class="st" id="planNote">M-1 vs Harian</div>
          </div>
        </div>
      </header>
      <div class="card-b">
        <div class="table-wrap">
          <table class="tb" id="tblPlanCkpn">
            <thead>
              <tr class="r1">
                <th class="sticky-left th-bucket">BUCKET</th>
                <th colspan="2" class="th y" id="hdrM1os">Data M-1</th>
                <th colspan="2" class="th y" id="hdrHarOs">Harian</th>
                <th colspan="3" class="th yl">INC OS</th>
                <th colspan="3" class="th r">CKPN</th>
              </tr>
              <tr class="r2">
                <th class="sticky-left"></th>
                <th>NOA</th><th>OS</th>
                <th>NOA</th><th>OS</th>
                <th>NOA</th><th>OS</th><th>%</th>
                <th>M-1</th><th>Harian</th><th>INC +/-</th>
              </tr>
            </thead>
            <tbody id="planCkpnBody"></tbody>
            <tfoot>
              <tr class="band">
                <td class="sticky-left">TOTAL FE (C–F)</td>
              </tr>
              <tr class="band">
                <td class="sticky-left">TOTAL BE (G–N)</td>
              </tr>
              <tr class="grand">
                <td class="sticky-left">GRAND TOTAL</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </section>

    <!-- FLOW RATE (kanan atas) -->
    <section class="card">
      <header class="card-h">
        <div class="card-title"><div class="badge or">FR</div><div class="tt">Flow Rate</div></div>
      </header>
      <div class="card-b">
        <div class="table-wrap small">
          <table class="tb" id="tblFR">
            <thead><tr><th>Bucket</th><th>Harian</th><th>FR Appetite</th><th>Δ</th></tr></thead>
            <tbody id="frBody"></tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- KOL (kanan bawah) -->
    <section class="card">
      <header class="card-h">
        <div class="card-title"><div class="badge bl">KOL</div><div class="tt">Kualitas Aset</div></div>
      </header>
      <div class="card-b">
        <div class="table-wrap small">
          <table class="tb" id="tblKOL">
            <thead>
              <tr>
                <th>Segment</th>
                <th id="kolM1Hdr">M-1</th>
                <th id="kolHarHdr">Harian</th>
                <th>Inc OS</th>
              </tr>
            </thead>
            <tbody id="kolBody"></tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- ACTUAL (bawah lebar) -->
    <section class="card card-wide">
      <header class="card-h">
        <div class="card-title">
          <div class="badge gr">ACT</div>
          <div>
            <div class="tt">ACTUAL Per <span id="actualDt">…</span></div>
            <div class="st">BTC, Back Flow, Stay, Flow, Lunas</div>
          </div>
        </div>
      </header>
      <div class="card-b">
        <div class="table-wrap">
          <table class="tb" id="tblActual">
            <thead>
              <tr class="r1">
                <th class="sticky-left th-bucket">BUCKET</th>
                <th colspan="3" class="th b">BTC</th>
                <th colspan="3" class="th g">BACK FLOW</th>
                <th colspan="3" class="th p">STAY</th>
                <th colspan="3" class="th o">FLOW</th>
                <th colspan="3" class="th w">LUNAS</th>
              </tr>
              <tr class="r2">
                <th class="sticky-left"></th>
                <th>NOA</th><th>OS</th><th>%</th>
                <th>NOA</th><th>OS</th><th>%</th>
                <th>NOA</th><th>OS</th><th>%</th>
                <th>NOA</th><th>OS</th><th>%</th>
                <th>NOA</th><th>OS</th><th>%</th>
              </tr>
            </thead>
            <tbody id="actualBody"></tbody>
            <tfoot>
              <tr class="band"><td class="sticky-left">TOTAL FE (C–F)</td></tr>
              <tr class="band"><td class="sticky-left">TOTAL BE (G–N)</td></tr>
              <tr class="grand"><td class="sticky-left">GRAND TOTAL</td></tr>
            </tfoot>
          </table>
        </div>
      </div>
    </section>
  </div>
</div>

<style>
  /* ===== THEME ===== */
  :root{
    --bd:#e5e7eb; --muted:#64748b; --ink:#0f172a; --bg:#f8fafc; --card:#fff;
    --y:#facc15; --yl:#fde68a; --b:#dbeafe; --g:#dcfce7; --p:#ffe4d6; --o:#fcd9b6; --w:#f3f4f6;
    --green:#16a34a; --blue:#2563eb; --orange:#fb923c;
  }
  *{ box-sizing:border-box }
  .ckpn-wrap{ padding:16px; background:var(--bg); color:var(--ink); font:14px/1.35 system-ui,Segoe UI,Roboto,Arial }
  .page-title{ margin:0 0 10px; font-weight:800 }
  .subtitle{ color:var(--muted); margin:6px 0 12px }

  /* Toolbar */
  .toolbar{ display:flex; flex-wrap:wrap; gap:10px 16px; align-items:end; margin:8px 0 6px }
  .trow{ display:flex; flex-direction:column; gap:4px }
  .trow label{ font-size:12px; color:var(--muted) }
  .trow input,.trow select{ border:1px solid var(--bd); border-radius:10px; padding:8px 10px; min-height:36px; background:#fff }
  .btn-primary{ background:var(--blue); color:#fff; border:1px solid #1d4ed8; border-radius:10px; padding:9px 14px; height:36px }
  .btn-primary:hover{ filter:brightness(.95) }

  /* Grid */
  .grid{ display:grid; gap:16px; grid-template-columns: 2fr 1fr; grid-auto-rows:minmax(120px,auto) }
  .card{ background:var(--card); border:1px solid var(--bd); border-radius:12px; overflow:hidden; display:flex; flex-direction:column }
  .card-wide{ grid-column:1 / span 2 }
  .card-h{ padding:8px 12px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid var(--bd); background:#fff }
  .card-title{ display:flex; gap:10px; align-items:center }
  .badge{ background:#111827; color:#fff; font-weight:800; border-radius:8px; padding:4px 8px; font-size:12px }
  .badge.or{ background:var(--orange) } .badge.bl{ background:var(--blue) } .badge.gr{ background:var(--green) }
  .card-title .tt{ font-weight:800 }
  .card-title .st{ color:var(--muted); font-size:12px }

  /* Tables */
  .table-wrap{ overflow:auto; max-height:420px }
  .table-wrap.small{ max-height:300px }
  .tb{ border-collapse:separate; border-spacing:0; min-width: 1100px; width:max-content }
  th,td{ border:1px solid var(--bd); padding:6px 8px; background:#fff; white-space:nowrap }
  thead th{ position:sticky; top:0; z-index:1; font-weight:800 }
  .tb .r1 th{ background:var(--y) }
  .tb .r2 th{ background:#f8fafc; font-weight:700 }
  .th{ text-align:center; font-weight:800 }
  .y{ background:#fff7c2 } .yl{ background:var(--yl) }
  .b{ background:var(--b) } .g{ background:var(--g) } .p{ background:var(--p) }
  .o{ background:var(--o) } .w{ background:var(--w) } .r{ background:#fecaca }
  .th-bucket{ background:#16a34a !important; color:#fff; min-width:170px; text-align:center }
  .sticky-left{ position:sticky; left:0; z-index:2; background:#fff; font-weight:700 }
  .num{ text-align:right }
  tbody tr:hover td{ background:#f9fafb }
  .band td{ background:#fffbea; font-weight:700 }
  .grand td{ background:#fef3c7; font-weight:800 }

  /* Responsive */
  @media (max-width: 1100px){
    .grid{ grid-template-columns: 1fr }
    .card-wide{ grid-column:auto }
  }
</style>

<script>
(() => {
  // ===== Helpers
  const nf = new Intl.NumberFormat('id-ID');
  const fmt = n => nf.format(Math.round(Number(n||0)));
  const pct = (x,base) => base>0 ? ((100*+x/base).toFixed(2)+'%') : '0,00%';
  const pad3 = n => String(n).padStart(3,'0');
  const lastDayPrevMonth = () => { const t=new Date(); return new Date(t.getFullYear(), t.getMonth(), 0); };
  const ymd = d => { const dt = new Date(d); const m=String(dt.getMonth()+1).padStart(2,'0'); const dd=String(dt.getDate()).padStart(2,'0'); return `${dt.getFullYear()}-${m}-${dd}`; };
  const pretty = (d, withWord=false) => new Intl.DateTimeFormat('id-ID',{day:'2-digit',month:withWord?'long':'2-digit',year:'numeric'}).format(new Date(d));
  const BUCKETS = [
    'A_DPD 0','B_DPD 1–30','C_DPD 31–60','D_DPD 61–90','E_DPD 91–120','F_DPD 121–150',
    'G_DPD 151–180','H_DPD 181–210','I_DPD 211–240','J_DPD 241–270','K_DPD 271–300',
    'L_DPD 301–330','M_DPD 331–360','N_DPD >360'
  ];
  const FE = ['C_DPD 31–60','D_DPD 61–90','E_DPD 91–120','F_DPD 121–150'];
  const BE = ['G_DPD 151–180','H_DPD 181–210','I_DPD 211–240','J_DPD 241–270','K_DPD 271–300','L_DPD 301–330','M_DPD 331–360','N_DPD >360'];

  // seeded rng (stabil per filter)
  function seedFrom(str){ let h=2166136261>>>0; for (const ch of str){ h^=ch.charCodeAt(0); h=Math.imul(h,16777619); } return h>>>0; }
  function makeRand(seed){ let s=seed>>>0; return ()=>{ s=(Math.imul(48271,s)&0x7fffffff); return s/0x7fffffff; }; }
  const between = (r,a,b)=> a + r()*(b-a);

  // generate dummy
  function gen({closing, harian, kode}){
    const rnd = makeRand(seedFrom(`${closing}|${harian}|${kode||'ALL'}`));
    const plan=[], actual=[];
    BUCKETS.forEach((nm,idx)=>{
      const baseN  = Math.max(0, Math.round(between(rnd, 5, 90) - idx*1.4));
      const baseOS = Math.max(0, between(rnd, 1e7, 2e8) - idx*1.2e6);

      // simulate movement to "harian"
      const harN  = Math.max(0, Math.round(baseN  + between(rnd,-5,5)));
      const harOS = Math.max(0, Math.round(baseOS + between(rnd,-2e7,2e7)));

      // CKPN assumed 8–22% of OS with slight changes
      const ckRateM1 = between(rnd,.10,.18);
      const ckRateH  = ckRateM1 + between(rnd,-.01,.01);

      const mk = (nRate,oRate)=>({
        n: Math.round(baseN * (nRate * between(rnd,.7,1.2))),
        o: Math.round(baseOS * (oRate * between(rnd,.7,1.2)))
      });

      plan.push({
        name:nm,
        m1:{n:baseN, o:baseOS},
        har:{n:harN, o:harOS},
        inc:{ n: harN-baseN, o: harOS-baseOS },
        incPct: pct(harOS-baseOS, baseOS),
        ck:{ m1: Math.round(baseOS*ckRateM1), har: Math.round(harOS*ckRateH) }
      });

      actual.push({
        name:nm, baseOS,
        btc: mk(.11,.09), back: mk(.05,.05), stay: mk(.20,.20), flow: mk(.06,.05), lunas: mk(.02,.02)
      });
    });

    // Flow rate table
    const frBuckets = ['FR C-X','FR 1-30','FR 31-60','FR 61-90','FR 91-120','FR 121-150','FR 150-180','FR C-30'];
    const fr = frBuckets.map((label,i)=>{
      const today = between(rnd, 0.2, 2.5) + (i<2?0.3:0);
      const appetite = [5,10,20,30,40,60,85,2.5][i] || 10;
      const delta = today - appetite/10;
      return {label, today: (today).toFixed(2)+'%', appetite: appetite+'%', inc: (delta).toFixed(2)+'%'};
    });

    // KOL mini
    const kolSeg = ['A Lancar','B DPK','C Kurang Lancar','D Diragukan','E Macet','% Lunas'];
    const kol = kolSeg.map((s)=>{
      const m1 = between(rnd, 30, 150);
      const har = m1 + between(rnd,-20,20);
      return {seg:s, m1:fmt(m1*1e6), har:fmt(har*1e6), inc:fmt((har-m1)*1e6)};
    });

    return {plan, actual, fr, kol};
  }

  // ===== DOM helpers
  const q = sel => document.querySelector(sel);
  function tdNum(v){ const td=document.createElement('td'); td.className='num'; td.textContent=v; return td; }
  function fillRow(tr, arr){ arr.forEach(v=> tr.appendChild(tdNum(v))); }

  // ===== RENDER: Plan+CKPN
  function renderPlanCkpn(rows){
    const tb = q('#planCkpnBody'); tb.innerHTML='';
    rows.forEach(r=>{
      const tr=document.createElement('tr');
      const tdL=document.createElement('td'); tdL.className='sticky-left'; tdL.textContent=r.name; tr.appendChild(tdL);
      fillRow(tr, [fmt(r.m1.n), fmt(r.m1.o), fmt(r.har.n), fmt(r.har.o), fmt(r.inc.n), fmt(r.inc.o), r.incPct, fmt(r.ck.m1), fmt(r.ck.har), fmt(r.ck.har-r.ck.m1)]);
      tb.appendChild(tr);
    });

    // totals (FE, BE, GRAND)
    const makeTotal = list => {
      const s = {m1:{n:0,o:0}, har:{n:0,o:0}, inc:{n:0,o:0}, ck:{m1:0,har:0}};
      rows.filter(r=> list.includes(r.name)).forEach(r=>{
        s.m1.n+=r.m1.n; s.m1.o+=r.m1.o; s.har.n+=r.har.n; s.har.o+=r.har.o;
        s.inc.n+=r.inc.n; s.inc.o+=r.inc.o; s.ck.m1+=r.ck.m1; s.ck.har+=r.ck.har;
      });
      const base=s.m1.o||0; return [
        fmt(s.m1.n), fmt(s.m1.o),
        fmt(s.har.n), fmt(s.har.o),
        fmt(s.inc.n), fmt(s.inc.o), pct(s.inc.o, base),
        fmt(s.ck.m1), fmt(s.ck.har), fmt(s.ck.har - s.ck.m1)
      ];
    };

    const [trFE,trBE,trGT] = q('#tblPlanCkpn tfoot').querySelectorAll('tr');
    // map FE/BE name sets from BUCKETS arrays above
    const feNames = ['C_DPD 31–60','D_DPD 61–90','E_DPD 91–120','F_DPD 121–150'];
    const beNames = ['G_DPD 151–180','H_DPD 181–210','I_DPD 211–240','J_DPD 241–270','K_DPD 271–300','L_DPD 301–330','M_DPD 331–360','N_DPD >360'];
    const allNames = rows.map(r=>r.name);

    [trFE,trBE,trGT].forEach(tr=>{ while(tr.children.length>1) tr.removeChild(tr.lastChild); });
    fillRow(trFE, makeTotal(feNames));
    fillRow(trBE, makeTotal(beNames));
    fillRow(trGT, makeTotal(allNames));
  }

  // ===== RENDER: Actual
  function renderActual(rows){
    const tb=q('#actualBody'); tb.innerHTML='';
    rows.forEach(r=>{
      const base=r.baseOS||0;
      const tr=document.createElement('tr');
      const tdL=document.createElement('td'); tdL.className='sticky-left'; tdL.textContent=r.name; tr.appendChild(tdL);
      [['btc'],['back'],['stay'],['flow'],['lunas']].forEach(([g])=>{
        tr.appendChild(tdNum(fmt(r[g].n)));
        tr.appendChild(tdNum(fmt(r[g].o)));
        tr.appendChild(tdNum(pct(r[g].o, base)));
      });
      tb.appendChild(tr);
    });

    const groupTotal = list=>{
      const s={btc:{n:0,o:0},back:{n:0,o:0},stay:{n:0,o:0},flow:{n:0,o:0},lunas:{n:0,o:0}, base:0};
      rows.filter(r=> list.includes(r.name)).forEach(r=>{
        ['btc','back','stay','flow','lunas'].forEach(k=>{ s[k].n+=r[k].n; s[k].o+=r[k].o; });
        s.base+=r.baseOS;
      });
      const base=s.base||0;
      return [
        fmt(s.btc.n),fmt(s.btc.o),pct(s.btc.o,base),
        fmt(s.back.n),fmt(s.back.o),pct(s.back.o,base),
        fmt(s.stay.n),fmt(s.stay.o),pct(s.stay.o,base),
        fmt(s.flow.n),fmt(s.flow.o),pct(s.flow.o,base),
        fmt(s.lunas.n),fmt(s.lunas.o),pct(s.lunas.o,base),
      ];
    };

    const [trFE,trBE,trGT] = q('#tblActual tfoot').querySelectorAll('tr');
    const feNames = ['C_DPD 31–60','D_DPD 61–90','E_DPD 91–120','F_DPD 121–150'];
    const beNames = ['G_DPD 151–180','H_DPD 181–210','I_DPD 211–240','J_DPD 241–270','K_DPD 271–300','L_DPD 301–330','M_DPD 331–360','N_DPD >360'];
    const allNames = rows.map(r=>r.name);
    [trFE,trBE,trGT].forEach(tr=>{ while(tr.children.length>1) tr.removeChild(tr.lastChild); });
    fillRow(trFE, groupTotal(feNames));
    fillRow(trBE, groupTotal(beNames));
    fillRow(trGT, groupTotal(allNames));
  }

  // ===== RENDER: FR & KOL
  function renderFR(rows){
    const tb=q('#frBody'); tb.innerHTML='';
    rows.forEach(r=>{
      const tr=document.createElement('tr');
      tr.innerHTML = `<td>${r.label}</td><td class="num">${r.today}</td><td class="num">${r.appetite}</td><td class="num">${r.inc}</td>`;
      tb.appendChild(tr);
    });
  }
  function renderKOL(rows){
    const tb=q('#kolBody'); tb.innerHTML='';
    rows.forEach(r=>{
      const tr=document.createElement('tr');
      tr.innerHTML = `<td>${r.seg}</td><td class="num">${r.m1}</td><td class="num">${r.har}</td><td class="num">${r.inc}</td>`;
      tb.appendChild(tr);
    });
  }

  // ===== Dropdown cabang 001–028
  const sel = document.getElementById('kode_kantor');
  for(let i=1;i<=28;i++){ const v=pad3(i); const o=document.createElement('option'); o.value=v; o.textContent=`${v} — Kc. ${v}`; sel.appendChild(o); }

  // Defaults
  document.getElementById('closing_date').value = ymd(lastDayPrevMonth());
  document.getElementById('harian_date').value  = ymd(new Date());

  // First render
  rerender();

  // Toolbar
  document.getElementById('ckpnToolbar').addEventListener('submit', e=>{ e.preventDefault(); rerender(); });

  function rerender(){
    const closing = document.getElementById('closing_date').value;
    const harian  = document.getElementById('harian_date').value;
    const kode    = document.getElementById('kode_kantor').value || '';

    const data = gen({closing,harian,kode});

    // subtitle & headers
    document.getElementById('subtitle').textContent =
      `Menampilkan: ${kode?('Cabang '+kode):'Konsolidasi'} — Closing: ${pretty(closing,true)} | Harian: ${pretty(harian,true)}`;
    document.getElementById('planNote').textContent = `M-1 s/d ${pretty(closing,true)} vs ${pretty(harian,true)}`;
    document.getElementById('hdrM1os').textContent = `Data M-1 s/d ${pretty(closing,false)}`;
    document.getElementById('hdrHarOs').textContent = `${pretty(harian,false)}`;
    document.getElementById('kolM1Hdr').textContent = `M-1 s/d ${pretty(closing,false)}`;
    document.getElementById('kolHarHdr').textContent = `${pretty(harian,false)}`;
    document.getElementById('actualDt').textContent = pretty(harian,true);

    renderPlanCkpn(data.plan);
    renderActual(data.actual);
    renderFR(data.fr);
    renderKOL(data.kol);
  }
})();
</script>
