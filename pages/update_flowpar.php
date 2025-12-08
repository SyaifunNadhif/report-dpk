<div class="max-w-7xl mx-auto px-4">
  <h1 id="judulHalaman" class="text-2xl font-bold mt-4 mb-1">📌 Update Progres Flow PAR</h1>
  <p id="judul_kantor" class="text-gray-700 ml-1 mb-4 font-semibold"></p>

  <!-- WRAP SCROLLER (tinggi di-set dinamis via JS) -->
  <div id="upWrap" class="hide-scrollbar overflow-auto rounded border border-gray-200 bg-white"
       style="--colName: 18rem;">
    <table id="tblUpdate" class="min-w-full text-sm text-left text-gray-800">
      <thead class="uppercase">
        <tr id="upHead1" class="text-xs">
          <th class="px-4 py-2 th sticky top-0 freeze-1 col-name">NAMA NASABAH</th>
          <th class="px-4 py-2 th sticky top-0 text-right">BAKI DEBET</th>
          <th class="px-4 py-2 th sticky top-0 text-right">TUNGG. POKOK</th>
          <th class="px-4 py-2 th sticky top-0 text-right">TUNGG. BUNGA</th>
          <th class="px-4 py-2 th sticky top-0 text-right">ANGS. POKOK</th>
          <th class="px-4 py-2 th sticky top-0 text-right">ANGS. BUNGA</th>
          <th class="px-4 py-2 th sticky top-0">KOMITMEN</th>
          <th class="px-4 py-2 th sticky top-0">JANJI BAYAR</th>
          <th class="px-4 py-2 th sticky top-0 text-center">AKSI</th>
        </tr>
      </thead>

      <!-- TOTAL sticky -->
      <tbody id="upTotalRow"></tbody>

      <!-- BODY -->
      <tbody id="flowparBody"></tbody>
    </table>
  </div>
</div>

<!-- Modal -->
<div id="komitmenModal"
     class="fixed inset-0 hidden items-center justify-center"
     style="z-index:2147483647; background:rgba(0,0,0,.5); backdrop-filter:blur(2px);">
  <div id="modalCard"
       class="bg-white rounded-xl shadow-xl w-[94vw] max-w-[560px] md:max-w-[640px] max-h-[90vh] overflow-hidden">
    <div class="flex items-center justify-between px-4 py-3 border-b bg-gray-50">
      <h2 class="text-lg md:text-xl font-bold">📝 Input Komitmen Janji Bayar</h2>
      <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 text-xl" aria-label="Tutup">✕</button>
    </div>

    <div class="p-4 overflow-auto" style="-webkit-overflow-scrolling:touch; max-height: calc(90vh - 56px);">
      <input type="hidden" id="modal_rekening">

      <div class="mb-3">
        <label class="block text-sm font-medium">Rekening</label>
        <div id="modal_rekening_text" class="bg-gray-100 px-3 py-2 rounded text-sm"></div>
      </div>

      <div class="mb-3 grid grid-cols-1 gap-3 md:grid-cols-2">
        <div>
          <label class="block text-sm font-medium">Nama Debitur</label>
          <div id="modal_nama" class="bg-gray-100 px-3 py-2 rounded text-sm"></div>
        </div>
        <div>
          <label class="block text-sm font-medium">Baki Debet</label>
          <div id="modal_baki" class="bg-gray-100 px-3 py-2 rounded text-sm"></div>
        </div>
      </div>

      <div class="mb-3">
        <label class="block text-sm font-medium">Komitmen</label>
        <!-- <select id="modal_komitmen" class="w-full px-3 py-2 border rounded"> -->
          <select id="modal_komitmen"  name="dpd_bucket" class="border rounded px-2 py-1">
          <option value="">-- Pilih DPD --</option>
          <option value="A_DPD 0">A_DPD 0</option>
          <option value="B_DPD 1-30">B_DPD 1-30</option>
          <option value="C_DPD 31-60">C_DPD 31-60</option>
          <option value="D_DPD 61-90">D_DPD 61-90</option>
          <option value="E_DPD 91-120">E_DPD 91-120</option>
          <option value="F_DPD 121-150">F_DPD 121-150</option>
          <option value="G_DPD 151-180">G_DPD 151-180</option>
          <option value="H_DPD 181-210">H_DPD 181-210</option>
          <option value="I_DPD 211-240">I_DPD 211-240</option>
          <option value="J_DPD 241-270">J_DPD 241-270</option>
          <option value="K_DPD 271-300">K_DPD 271-300</option>
          <option value="L_DPD 301-330">L_DPD 301-330</option>
          <option value="M_DPD 331-360">M_DPD 331-360</option>
          <option value="N_DPD &gt;360">N_DPD &gt;360</option>
          <option value="O_Lunas">O_Lunas</option>
        </select>

        
      </div>

      <div class="mb-3 grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium">Tanggal Pembayaran</label>
          <input type="date" id="modal_tanggal" class="w-full px-3 py-2 border rounded">
        </div>
        <div>
          <label class="block text-sm font-medium">Alasan Keterlambatan</label>
          <textarea id="modal_alasan" rows="3" class="w-full px-3 py-2 border rounded"></textarea>
        </div>
      </div>
    </div>

    <div class="px-4 py-3 border-t bg-gray-50 flex justify-end gap-2">
      <button onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Batal</button>
      <button id="btnSaveKomitmen" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan</button>
    </div>
  </div>
</div>

<style>
/* ====== Sembunyikan SCROLLBAR HANYA DI TABEL (wrapper) ====== */
.hide-scrollbar{ scrollbar-width:none; -ms-overflow-style:none; }
.hide-scrollbar::-webkit-scrollbar{ width:0 !important; height:0 !important; }

/* ====== Table ====== */
#tblUpdate{ border-collapse:separate; border-spacing:0; table-layout:fixed; }
#tblUpdate .th{ background:#d9ead3; color:#1f2937; letter-spacing:.02em; border-bottom:1px solid #cfe3c8; }
#tblUpdate th, #tblUpdate td{ border-bottom:1px solid #eef2f7; }
#tblUpdate tbody tr:hover td{ background:#f9fafb; }

/* Freeze kolom 1 (Nama) */
#tblUpdate .col-name{ width:var(--colName); min-width:var(--colName); }
#tblUpdate thead th.freeze-1{ position:sticky; left:0; z-index:30; }
#tblUpdate tbody td.freeze-1{ position:sticky; left:0; z-index:15; background:#fff; box-shadow:1px 0 0 rgba(0,0,0,.06); }
/* Ellipsis nama */
#tblUpdate .col-name .ellipsis{ white-space:nowrap; overflow:hidden; text-overflow:ellipsis; display:block; }

/* TOTAL sticky di bawah header */
#tblUpdate tbody tr.sticky-total td{
  position:sticky; top:var(--up_head, 40px);
  background:#eaf2ff; color:#1e40af; z-index:20; border-bottom:1px solid #c7d2fe;
}
#tblUpdate tbody tr.sticky-total td.freeze-1{ z-index:31; background:#eaf2ff; box-shadow:1px 0 0 rgba(0,0,0,.08); }

/* Spacer bawah agar baris akhir tidak ketutup apa pun */
#flowparBody::after{
  content:""; display:block;
  height: var(--up_safe, 36px);
}

/* Mobile: kolom nama lebih ramping */
@media (max-width:640px){ #upWrap{ --colName:10rem; } }

body{ overflow:hidden; } /* halaman tetap tanpa scroll, hanya tabel yang scroll */
</style>

<script>
const nfID = new Intl.NumberFormat('id-ID');
const fmt = (n)=> nfID.format(Number(n||0));

function setUpSticky(){
  const h = document.getElementById('upHead1')?.offsetHeight || 40;
  document.getElementById('upWrap')?.style.setProperty('--up_head', h + 'px');
}

/* === Atur tinggi wrapper tabel secara DINAMIS (agar selalu muat sampai bawah) === */
function sizeUpWrap(){
  const wrap = document.getElementById('upWrap');
  if(!wrap) return;
  const rectTop = wrap.getBoundingClientRect().top;
  // SAFE memberi ruang kecil di bawah agar tidak mentok komponen lain
  const SAFE_PX = 10;
  const h = Math.max(260, window.innerHeight - rectTop - SAFE_PX);
  wrap.style.height = h + 'px';
  // spacer untuk baris terakhir
  wrap.style.setProperty('--up_safe', (SAFE_PX + 26) + 'px');
}

/* ===== INIT ===== */
document.addEventListener("DOMContentLoaded", async () => {
  const storedData = sessionStorage.getItem("flowpar_update");
  if (!storedData) return;

  const req = JSON.parse(storedData);
  document.getElementById("judul_kantor").textContent = `Kode Kantor ${req.kode_kantor}`;

  try{
    const r = await fetch("./api/flow_par/", {
      method:"POST",
      headers:{ "Content-Type":"application/json" },
      body: JSON.stringify({ type:"KL Baru", kode_kantor:req.kode_kantor, closing_date:req.closing_date, harian_date:req.harian_date })
    });
    const j = await r.json();
    const list = Array.isArray(j.data) ? j.data : [];
    renderRows(list);
    // hitung tinggi & sticky setelah render
    setUpSticky();
    sizeUpWrap();
    setTimeout(()=>{ setUpSticky(); sizeUpWrap(); }, 50);
  }catch(e){ console.error(e); }
});

/* Reflow saat resize/rotate */
window.addEventListener('resize', () => { setUpSticky(); sizeUpWrap(); });

function renderRows(list){
  const body = document.getElementById("flowparBody");
  const tot  = document.getElementById("upTotalRow");
  body.innerHTML = ""; tot.innerHTML = "";

  const sum = k => list.reduce((s,d)=> s + Number(d[k]||0), 0);
  const tBaki=sum('baki_debet'), tTP=sum('tunggakan_pokok'), tTB=sum('tunggakan_bunga'),
        tAP=sum('angsuran_pokok'), tAB=sum('angsuran_bunga');

  tot.innerHTML = `
    <tr class="sticky-total font-semibold">
      <td class="px-4 py-2 freeze-1 col-name">TOTAL</td>
      <td class="px-4 py-2 text-right">${fmt(tBaki)}</td>
      <td class="px-4 py-2 text-right">${fmt(tTP)}</td>
      <td class="px-4 py-2 text-right">${fmt(tTB)}</td>
      <td class="px-4 py-2 text-right">${fmt(tAP)}</td>
      <td class="px-4 py-2 text-right">${fmt(tAB)}</td>
      <td class="px-4 py-2"></td>
      <td class="px-4 py-2"></td>
      <td class="px-4 py-2"></td>
    </tr>`;

  for(const d of list){
    body.insertAdjacentHTML('beforeend', `
      <tr data-rekening="${d.no_rekening}">
        <td class="px-4 py-2 freeze-1 col-name"><span class="ellipsis" title="${safe(d.nama_nasabah)}">${safe(d.nama_nasabah)}</span></td>
        <td class="px-4 py-2 text-right">${fmt(d.baki_debet)}</td>
        <td class="px-4 py-2 text-right">${fmt(d.tunggakan_pokok)}</td>
        <td class="px-4 py-2 text-right">${fmt(d.tunggakan_bunga)}</td>
        <td class="px-4 py-2 text-right">${d.angsuran_pokok ? fmt(d.angsuran_pokok) : "0"}</td>
        <td class="px-4 py-2 text-right">${d.angsuran_bunga ? fmt(d.angsuran_bunga) : "0"}</td>
        <td class="px-4 py-2">${d.komitmen ?? "-"}</td>
        <td class="px-4 py-2">${d.tgl_pembayaran ?? "-"}</td>
        <td class="px-4 py-2 text-center">
          <button class="btn-edit open-modal-btn"
            data-rekening="${d.no_rekening}"
            data-nama="${escapeAttr(d.nama_nasabah)}"
            data-baki="${d.baki_debet}"
            data-komitmen="${escapeAttr(d.komitmen ?? '')}"
            data-tgl_pembayaran="${escapeAttr(d.tgl_pembayaran ?? '')}"
            data-alasan="${escapeAttr(d.alasan ?? '')}">✏️</button>
        </td>
      </tr>`);
  }
}

/* ===== Modal controls ===== */
document.body.addEventListener("click", (e) => {
  const btn = e.target.closest(".open-modal-btn");
  if (!btn) return;
  openModalKomitmen({
    rekening: btn.dataset.rekening,
    nama: btn.dataset.nama,
    baki_debet: btn.dataset.baki,
    komitmen: btn.dataset.komitmen,
    tgl_pembayaran: btn.dataset.tgl_pembayaran,
    alasan: btn.dataset.alasan
  });
});

function openModalKomitmen(d){
  id('modal_rekening').value = d.rekening || '';
  id('modal_rekening_text').innerText = d.rekening || '-';
  id('modal_nama').innerText = d.nama || '-';
  id('modal_baki').innerText = fmt(Number(d.baki_debet||0));
  id('modal_komitmen').value = d.komitmen || '';
  id('modal_tanggal').value  = d.tgl_pembayaran || '';
  id('modal_alasan').value   = d.alasan || '';

  const m = id('komitmenModal');
  m.classList.remove('hidden'); m.classList.add('flex');

  m.addEventListener('click', overlayCloseOnce);
  document.addEventListener('keydown', escCloseOnce);
}
function overlayCloseOnce(e){ if(!e.target.closest('#modalCard')) closeModal(); }
function escCloseOnce(e){ if(e.key==='Escape') closeModal(); }
function closeModal(){
  const m = id('komitmenModal');
  m.classList.add('hidden'); m.classList.remove('flex');
  m.removeEventListener('click', overlayCloseOnce);
  document.removeEventListener('keydown', escCloseOnce);
}

/* Helpers */
function id(x){ return document.getElementById(x); }
function safe(v){ return (v==null||v==='')?'-':String(v); }
function escapeAttr(s){ return String(s ?? '').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

/* Save */
id("btnSaveKomitmen").addEventListener("click", async () => {
  const rekening = id('modal_rekening').value;
  const komitmen = id('modal_komitmen').value;
  const tanggal  = id('modal_tanggal').value;
  const alasan   = id('modal_alasan').value;

  if (!rekening || !komitmen || !tanggal || !alasan) { alert("Mohon lengkapi semua data."); return; }

  const btn = id("btnSaveKomitmen"); btn.disabled = true; btn.textContent = 'Menyimpan...';
  try{
    const res = await fetch('./api/flow_par/', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ type:"Update KL Baru", rekening, komitmen, tgl_pembayaran:tanggal, alasan })
    });
    const result = await res.json();
    if (Number(result.status) === 200){ alert("✅ Komitmen berhasil disimpan."); closeModal(); location.reload(); }
    else { alert("❌ Gagal menyimpan: " + (result.message||"unknown error")); }
  }catch(e){ console.error(e); alert("Terjadi kesalahan saat menyimpan."); }
  finally{ btn.disabled=false; btn.textContent='Simpan'; }
});

/* hitung tinggi sticky & tinggi scroller saat load/resize */
window.addEventListener('load', () => { setUpSticky(); sizeUpWrap(); });
window.addEventListener('resize', () => { setUpSticky(); sizeUpWrap(); });
</script>
