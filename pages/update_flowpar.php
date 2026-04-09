<div class="max-w-7xl mx-auto px-4">
  <h1 id="judulHalaman" class="text-2xl font-bold mt-4 mb-1">📌 Update Progres Flow PAR</h1>
  <p id="judul_kantor" class="text-gray-700 ml-1 mb-4 font-semibold"></p>

  <div id="upWrap" class="hide-scrollbar overflow-auto rounded border border-gray-200 bg-white"
       style="--colName: 18rem;">
    <table id="tblUpdate" class="min-w-full text-sm text-left text-gray-800">
      <thead class="uppercase">
        <tr id="upHead1" class="text-xs">
          <th class="px-4 py-2 th sticky top-0 freeze-1 col-name">NAMA NASABAH</th>
          <th class="px-4 py-2 th sticky top-0 text-center">KOLEK</th>
          <th class="px-4 py-2 th sticky top-0 text-right">BAKI DEBET</th>
          <th class="px-4 py-2 th sticky top-0 text-right">TUNGG. POKOK</th>
          <th class="px-4 py-2 th sticky top-0 text-right">TUNGG. BUNGA</th>
          <th class="px-4 py-2 th sticky top-0 text-center">DPD TP</th>
          <th class="px-4 py-2 th sticky top-0 text-center">DPD TB</th>
          <th class="px-4 py-2 th sticky top-0 text-center">JATUH TEMPO</th>
          <th class="px-4 py-2 th sticky top-0">KOMITMEN</th>
          <th class="px-4 py-2 th sticky top-0">JANJI BAYAR</th>
          <th class="px-4 py-2 th sticky top-0 text-center">AKSI</th>
        </tr>
      </thead>

      <tbody id="upTotalRow"></tbody>

      <tbody id="flowparBody"></tbody>
    </table>
  </div>
</div>

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
        <div id="modal_rekening_text" class="bg-gray-100 px-3 py-2 rounded text-sm font-mono"></div>
      </div>

      <div class="mb-3 grid grid-cols-1 gap-3 md:grid-cols-2">
        <div>
          <label class="block text-sm font-medium">Nama Debitur</label>
          <div id="modal_nama" class="bg-gray-100 px-3 py-2 rounded text-sm font-semibold text-gray-700"></div>
        </div>
        <div>
          <label class="block text-sm font-medium">Baki Debet</label>
          <div id="modal_baki" class="bg-gray-100 px-3 py-2 rounded text-sm font-semibold text-blue-700"></div>
        </div>
      </div>

      <div class="mb-3">
        <label class="block text-sm font-medium">Komitmen DPD</label>
          <select id="modal_komitmen"  name="dpd_bucket" class="border border-gray-300 rounded px-3 py-2 w-full focus:ring-2 focus:ring-blue-500 focus:outline-none">
          </select>
      </div>

      <div class="mb-3 grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium">Tanggal Pembayaran</label>
          <input type="date" id="modal_tanggal" class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium">Nominal Janji Bayar</label>
          <input type="number" id="modal_nominal" class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Contoh: 500000">
        </div>
      </div>

      <div class="mb-2">
        <label class="block text-sm font-medium">Alasan Keterlambatan</label>
        <textarea id="modal_alasan" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Masukkan alasan keterlambatan..."></textarea>
      </div>
    </div>

    <div class="px-4 py-3 border-t bg-gray-50 flex justify-end gap-2">
      <button onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded font-semibold hover:bg-gray-400 transition">Batal</button>
      <button id="btnSaveKomitmen" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700 transition">Simpan</button>
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

/* ==== WARNA UNTUK ROW & SEL MERAH ==== */
.row-merah td { background-color: #fef2f2 !important; }
.row-merah td.freeze-1 { background-color: #fef2f2 !important; }

.cell-merah { background-color: #fee2e2 !important; color: #991b1b !important; font-weight: bold; }

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

function startOfDay(d){ const x=new Date(d); x.setHours(0,0,0,0); return x; }
function endOfMonth(dateLike){ const d = new Date(dateLike); if (isNaN(d)) return null; return startOfDay(new Date(d.getFullYear(), d.getMonth()+1, 0)); }

function setUpSticky(){
  const h = document.getElementById('upHead1')?.offsetHeight || 40;
  document.getElementById('upWrap')?.style.setProperty('--up_head', h + 'px');
}

/* === Atur tinggi wrapper tabel secara DINAMIS === */
function sizeUpWrap(){
  const wrap = document.getElementById('upWrap');
  if(!wrap) return;
  const rectTop = wrap.getBoundingClientRect().top;
  const SAFE_PX = 10;
  const h = Math.max(260, window.innerHeight - rectTop - SAFE_PX);
  wrap.style.height = h + 'px';
  wrap.style.setProperty('--up_safe', (SAFE_PX + 26) + 'px');
}

/* ===== INIT ===== */
document.addEventListener("DOMContentLoaded", async () => {
  const storedData = sessionStorage.getItem("flowpar_update");
  if (!storedData) return;

  const req = JSON.parse(storedData);
  
  let judulTeks = `Kode Kantor: ${req.kode_kantor || '-'}`;
  if (req.kode_kankas) {
      judulTeks += ` | Kankas: ${req.kode_kankas}`;
  }
  document.getElementById("judul_kantor").textContent = judulTeks;

  try{
    const payloadAPI = { 
        type: "KL Baru", 
        kode_kantor: req.kode_kantor, 
        kode_kankas: req.kode_kankas || "", 
        closing_date: req.closing_date, 
        harian_date: req.harian_date 
    };

    const r = await fetch("./api/flow_par/", {
      method:"POST",
      headers:{ "Content-Type":"application/json" },
      body: JSON.stringify(payloadAPI)
    });
    const j = await r.json();
    const list = Array.isArray(j.data) ? j.data : [];
    
    // Kirim harian_date untuk kalkulasi EOM
    renderRows(list, req.harian_date);
    
    setUpSticky();
    sizeUpWrap();
    setTimeout(()=>{ setUpSticky(); sizeUpWrap(); }, 50);
  }catch(e){ console.error(e); }
});

window.addEventListener('resize', () => { setUpSticky(); sizeUpWrap(); });

function renderRows(list, harian_date){
  const body = document.getElementById("flowparBody");
  const tot  = document.getElementById("upTotalRow");
  body.innerHTML = ""; tot.innerHTML = "";

  if(list.length === 0){
      body.innerHTML = `<tr><td colspan="11" class="text-center py-8 text-gray-500 font-medium">Tidak ada data debitur ditemukan.</td></tr>`;
      return;
  }

  const sum = k => list.reduce((s,d)=> s + Number(d[k]||0), 0);
  const tBaki=sum('baki_debet'), tTP=sum('tunggakan_pokok'), tTB=sum('tunggakan_bunga');

  tot.innerHTML = `
    <tr class="sticky-total font-semibold">
      <td class="px-4 py-2 freeze-1 col-name" colspan="2">TOTAL (${list.length} Debitur)</td>
      <td class="px-4 py-2 text-right">${fmt(tBaki)}</td>
      <td class="px-4 py-2 text-right">${fmt(tTP)}</td>
      <td class="px-4 py-2 text-right">${fmt(tTB)}</td>
      <td class="px-4 py-2 text-center">-</td>
      <td class="px-4 py-2 text-center">-</td>
      <td class="px-4 py-2 text-center">-</td>
      <td class="px-4 py-2"></td>
      <td class="px-4 py-2"></td>
      <td class="px-4 py-2"></td>
    </tr>`;

  // Untuk menghitung Jatuh Tempo Bulan Kemarin dan Bulan Ini
  const refDate = harian_date ? new Date(harian_date) : new Date();
  const refMonth = refDate.getMonth();
  const refYear = refDate.getFullYear();
  let prevMonth = refMonth - 1;
  let prevYear = refYear;
  if(prevMonth < 0) { prevMonth = 11; prevYear -= 1; }

  for(const d of list){
    const dpdTP = Number(d.hari_menunggak_pokok || 0);
    const dpdTB = Number(d.hari_menunggak_bunga || 0);
    const tglJT = d.tgl_jatuh_tempo ? d.tgl_jatuh_tempo : '-';
    const kolek = d.kolek_harian || d.kolek_closing || '-'; // Ambil Kolek

    // Deteksi warna merah untuk cell DPD
    const classTP = dpdTP >= 90 ? 'cell-merah' : '';
    const classTB = dpdTB >= 90 ? 'cell-merah' : '';

    // Deteksi warna merah untuk seluruh Row
    let rowClass = "";
    if (d.tgl_jatuh_tempo) {
        const jt = startOfDay(new Date(d.tgl_jatuh_tempo));
        const jtMonth = jt.getMonth();
        const jtYear = jt.getFullYear();

        // Jika jatuh tempo ada di bulan ini ATAU bulan kemarin
        if ((jtYear === refYear && jtMonth === refMonth) || (jtYear === prevYear && jtMonth === prevMonth)) {
            rowClass = "row-merah";
        }
    }

    body.insertAdjacentHTML('beforeend', `
      <tr class="${rowClass}" data-rekening="${d.no_rekening}">
        <td class="px-4 py-2 freeze-1 col-name"><span class="ellipsis" title="${safe(d.nama_nasabah)}">${safe(d.nama_nasabah)}</span></td>
        <td class="px-4 py-2 text-center font-bold text-gray-600">${kolek}</td>
        <td class="px-4 py-2 text-right">${fmt(d.baki_debet)}</td>
        <td class="px-4 py-2 text-right">${fmt(d.tunggakan_pokok)}</td>
        <td class="px-4 py-2 text-right">${fmt(d.tunggakan_bunga)}</td>
        <td class="px-4 py-2 text-center ${classTP}">${dpdTP}</td>
        <td class="px-4 py-2 text-center ${classTB}">${dpdTB}</td>
        <td class="px-4 py-2 text-center">${tglJT}</td>
        <td class="px-4 py-2">${d.komitmen ?? "-"}</td>
        <td class="px-4 py-2">${d.tgl_pembayaran ?? "-"}</td>
        <td class="px-4 py-2 text-center">
          <button class="btn-edit open-modal-btn bg-blue-100 text-blue-700 hover:bg-blue-600 hover:text-white px-2 py-1.5 rounded font-medium transition shadow-sm"
            data-rekening="${d.no_rekening}"
            data-nama="${escapeAttr(d.nama_nasabah)}"
            data-baki="${d.baki_debet}"
            data-tp="${d.tunggakan_pokok || 0}" 
            data-tb="${d.tunggakan_bunga || 0}" 
            data-komitmen="${escapeAttr(d.komitmen ?? '')}"
            data-tgl_pembayaran="${escapeAttr(d.tgl_pembayaran ?? '')}"
            data-tgl_jt="${escapeAttr(tglJT)}"
            data-nominal="${escapeAttr(d.nominal ?? '')}" 
            data-alasan="${escapeAttr(d.alasan ?? '')}">✏️ Update</button>
        </td>
      </tr>`);
  }
}

/* ===== Modal controls & State ===== */
let currentTP = 0;
let currentTB = 0;

document.body.addEventListener("click", (e) => {
  const btn = e.target.closest(".open-modal-btn");
  if (!btn) return;
  openModalKomitmen({
    rekening: btn.dataset.rekening,
    nama: btn.dataset.nama,
    baki_debet: btn.dataset.baki,
    tp: btn.dataset.tp,
    tb: btn.dataset.tb,
    komitmen: btn.dataset.komitmen,
    tgl_pembayaran: btn.dataset.tgl_pembayaran,
    tgl_jt: btn.dataset.tgl_jt,
    nominal: btn.dataset.nominal,
    alasan: btn.dataset.alasan
  });
});

function openModalKomitmen(d){
  // Simpan nilai TP dan TB untuk auto-calculate Lunas
  currentTP = Number(d.tp);
  currentTB = Number(d.tb);

  id('modal_rekening').value = d.rekening || '';
  id('modal_rekening_text').innerText = d.rekening || '-';
  id('modal_nama').innerText = d.nama || '-';
  id('modal_baki').innerText = fmt(Number(d.baki_debet||0));
  
  id('modal_tanggal').value  = d.tgl_pembayaran || '';
  id('modal_nominal').value  = d.nominal || ''; 
  id('modal_alasan').value   = d.alasan || '';

  const selectKomitmen = id('modal_komitmen');
  selectKomitmen.innerHTML = ''; 
  
  // Dapatkan bulan dan tahun dari JT nasabah
  let isBulanIni = false;
  if (d.tgl_jt && d.tgl_jt !== '-') {
      const jtDate = new Date(d.tgl_jt);
      const today = new Date();
      if (jtDate.getMonth() === today.getMonth() && jtDate.getFullYear() === today.getFullYear()) {
          isBulanIni = true;
      }
  }

  let optionsHTML = `<option value="">-- Pilih DPD --</option>`;
  if (isBulanIni) {
      optionsHTML += `
        <option value="A_DPD 0">Flow (A_DPD 0)</option>
        <option value="O_Lunas">O_Lunas</option>
      `;
  } else {
      optionsHTML += `
        <option value="A_DPD 0">A_DPD 0</option>
        <option value="B_DPD 1-30">B_DPD 1-30</option>
        <option value="C_DPD 31-60">C_DPD 31-60</option>
        <option value="D_DPD 61-90">D_DPD 61-90</option>
        <option value="E_DPD 91-120">E_DPD 91-120</option>
        <option value="O_Lunas">O_Lunas</option>
      `;
  }
  selectKomitmen.innerHTML = optionsHTML;
  selectKomitmen.value = d.komitmen || '';

  const m = id('komitmenModal');
  m.classList.remove('hidden'); m.classList.add('flex');

  m.addEventListener('click', overlayCloseOnce);
  document.addEventListener('keydown', escCloseOnce);
}

// ==== LISTENER AUTO-FILL LUNAS ====
document.getElementById('modal_komitmen').addEventListener('change', function(e) {
    if(e.target.value === 'O_Lunas') {
        const totalLunas = currentTP + currentTB;
        document.getElementById('modal_nominal').value = totalLunas;
    }
});

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
  const nominal  = id('modal_nominal').value; 
  const alasan   = id('modal_alasan').value;

  if (!rekening || !komitmen || !tanggal || !nominal || !alasan) { 
      alert("Mohon lengkapi semua data (termasuk Nominal)."); 
      return; 
  }

  const btn = id("btnSaveKomitmen"); 
  btn.disabled = true; 
  btn.textContent = 'Menyimpan...';
  
  try{
    const res = await fetch('./api/flow_par/', {
      method:'POST', 
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ 
          type: "Update KL Baru", 
          rekening: rekening, 
          komitmen: komitmen, 
          tgl_pembayaran: tanggal, 
          nominal: nominal, 
          alasan: alasan 
      })
    });
    
    const result = await res.json();
    if (Number(result.status) === 200){ 
        alert("✅ Komitmen berhasil disimpan."); 
        closeModal(); 
        location.reload(); 
    } else { 
        alert("❌ Gagal menyimpan: " + (result.message || "unknown error")); 
    }
  }catch(e){ 
      console.error(e); 
      alert("Terjadi kesalahan saat menyimpan."); 
  } finally { 
      btn.disabled = false; 
      btn.textContent = 'Simpan'; 
  }
});

/* hitung tinggi sticky & tinggi scroller saat load/resize */
window.addEventListener('load', () => { setUpSticky(); sizeUpWrap(); });
window.addEventListener('resize', () => { setUpSticky(); sizeUpWrap(); });
</script>