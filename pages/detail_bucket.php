<!-- detail_bucket.php -->
<div class="max-w-7xl mx-auto px-4">
  <div class="flex items-center justify-between mt-6 mb-2">
    <h1 id="judulHalaman" class="text-2xl font-bold">📌 Detail Debitur DPD</h1>
    <a href="./bucket_dpd" class="text-sm text-blue-600 hover:underline">← Kembali ke Rekap Bucket</a>
  </div>
  <p id="judul_kantor" class="text-gray-600 ml-1 mb-4 font-semibold"></p>
  <p id="ringkas_bucket" class="text-gray-600 ml-1 mb-6"></p>

  <!-- Tabel debitur -->
  <div class="overflow-x-auto max-h-[70vh] overflow-y-auto">
    <table class="min-w-full text-sm text-left text-gray-800 bg-white rounded shadow">
      <thead class="bg-gray-100 text-gray-700">
        <tr>
          <th class="px-4 py-2 sticky top-0 bg-gray-100 z-10">No. Rekening</th>
          <th class="px-4 py-2 sticky top-0 bg-gray-100 z-10">Nama Nasabah</th>
          <th class="px-4 py-2 sticky top-0 bg-gray-100 z-10 text-right">OSC (Baki Debet)</th>
          <th class="px-4 py-2 sticky top-0 bg-gray-100 z-10 text-right">Hari Menunggak</th>
          <th class="px-4 py-2 sticky top-0 bg-gray-100 z-10">Tgl Jatuh Tempo</th>
          <th class="px-4 py-2 sticky top-0 bg-gray-100 z-10 text-center">Aksi</th>
        </tr>
      </thead>
      <tbody id="bucketBody"></tbody>
    </table>
  </div>
</div>

<!-- Modal: Input Komitmen / Janji Bayar -->
<div id="komitmenModal" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
    <h2 class="text-xl font-bold mb-4">📝 Input Komitmen Janji Bayar</h2>

    <input type="hidden" id="modal_rekening">

    <div class="mb-3">
      <label class="block text-sm font-medium">Rekening</label>
      <div id="modal_rekening_text" class="bg-gray-100 px-3 py-2 rounded text-sm"></div>
    </div>

    <div class="mb-3">
      <label class="block text-sm font-medium">Nama Debitur</label>
      <div id="modal_nama" class="bg-gray-100 px-3 py-2 rounded text-sm"></div>
    </div>

    <div class="mb-3">
      <label class="block text-sm font-medium">Baki Debet</label>
      <div id="modal_baki" class="bg-gray-100 px-3 py-2 rounded text-sm"></div>
    </div>

    <div class="mb-3">
      <label class="block text-sm font-medium">Komitmen</label>
      <select id="modal_komitmen" class="w-full px-3 py-2 border rounded">
        <option value="">-- Pilih Komitmen --</option>
        <option value="BTC">BTC</option>
        <option value="Flow">Flow</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="block text-sm font-medium">Tanggal Pembayaran</label>
      <input type="date" id="modal_tanggal" class="w-full px-3 py-2 border rounded">
    </div>

    <div class="mb-4">
      <label class="block text-sm font-medium">Alasan Keterlambatan</label>
      <textarea id="modal_alasan" rows="3" class="w-full px-3 py-2 border rounded"></textarea>
    </div>

    <div class="flex justify-end gap-3">
      <button onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Batal</button>
      <button id="btnSaveKomitmen" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan</button>
    </div>
  </div>
</div>

<script>
  /* ========= Helpers ========= */
  const nfID = new Intl.NumberFormat('id-ID');
  const fmtNum = n => nfID.format(Number(n || 0));
  const fmtDate = (s) => s ? new Date(s).toISOString().slice(0,10) : '-';

  // Ubah min/max jadi label "DPD ..."
  function rangeToLabel(min, max) {
    if (min === 0 && (max === 0 || max === '0')) return 'DPD 0';
    if (max === null || max === undefined) return `DPD >${min}`;
    if (min === 0 && max > 0) return `DPD 0–${max}`;
    return `DPD ${min}–${max}`;
  }

  // Ambil param dari localStorage (di-set dari halaman rekap)
  function getStoredParams() {
    try {
      const raw = localStorage.getItem('bucket_detail_params');
      if (!raw) return null;
      const j = JSON.parse(raw);
      return j;
    } catch { return null; }
  }

  // Render header judul
  function setHeaderInfo({ kode_kantor, closing_date, min, max }) {
    document.getElementById('judulHalaman').textContent = `📌 Detail Debitur ${rangeToLabel(min, max)}`;
    document.getElementById('judul_kantor').textContent = `Kode Kantor ${kode_kantor} • Closing: ${closing_date}`;
  }

  // Fetch detail bucket
  async function fetchDetailBucket(params) {
    const body = {
      type: 'detail_bucket',
      closing_date: params.closing_date,
      kode_kantor: params.kode_kantor
    };
    if (params.min !== undefined && params.min !== null) body.min = params.min;
    if (params.max !== undefined && params.max !== null) body.max = params.max;

    const res = await fetch('./api/bucket/', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    });
    return res.json();
  }

  // Render ringkasan (NOA & OSC) kalau meta tersedia
  function renderRingkasan(meta){
    const el = document.getElementById('ringkas_bucket');
    if (!meta) { el.textContent = ''; return; }
    const noa = meta.noa ?? null;
    const osc = meta.baki_total ?? null;
    if (noa !== null || osc !== null) {
      el.innerHTML = `Ringkasan: <span class="font-semibold">${fmtNum(noa || 0)} NOA</span> • <span class="font-semibold">OSC ${fmtNum(osc || 0)}</span>`;
    } else {
      el.textContent = '';
    }
  }

  // Render baris tabel
  function renderRows(rows) {
    const tbody = document.getElementById('bucketBody');
    tbody.innerHTML = '';

    if (!rows || rows.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6" class="px-4 py-4 text-center text-gray-500">Tidak ada data debitur pada bucket ini.</td></tr>`;
      return;
    }

    for (const item of rows) {
      const tr = document.createElement('tr');
      tr.className = "border-b hover:bg-gray-50";

      const rekening = item.no_rekening || '-';
      const nama     = item.nama_nasabah || '-';
      const baki     = Number(item.baki_debet || 0);
      const hm       = Number(item.hari_menunggak || 0);
      const tjt      = item.tgl_jatuh_tempo || null;

      tr.innerHTML = `
        <td class="px-4 py-2">${rekening}</td>
        <td class="px-4 py-2">${nama}</td>
        <td class="px-4 py-2 text-right">${fmtNum(baki)}</td>
        <td class="px-4 py-2 text-right">${fmtNum(hm)}</td>
        <td class="px-4 py-2">${fmtDate(tjt)}</td>
        <td class="px-4 py-2 text-center">
          <button
            class="px-2 py-1 rounded bg-blue-600 text-white hover:bg-blue-700 open-modal-btn"
            data-rekening="${rekening}"
            data-nama="${nama}"
            data-baki="${baki}">
            ✏️
          </button>
        </td>
      `;
      tbody.appendChild(tr);
    }
  }

  // ===== Modal handlers =====
  function openModalKomitmen({ rekening, nama, baki_debet }) {
    document.getElementById('modal_rekening').value = rekening;
    document.getElementById('modal_rekening_text').innerText = rekening;
    document.getElementById('modal_nama').innerText = nama || '-';
    document.getElementById('modal_baki').innerText = fmtNum(baki_debet || 0);
    document.getElementById('modal_komitmen').value = "";
    document.getElementById('modal_tanggal').value = "";
    document.getElementById('modal_alasan').value = "";
    document.getElementById('komitmenModal').classList.remove('hidden');
  }
  function closeModal() { document.getElementById('komitmenModal').classList.add('hidden'); }

  // Simpan komitmen (opsional – mengikuti contoh)
  document.getElementById("btnSaveKomitmen").addEventListener("click", async () => {
    const rekening = document.getElementById('modal_rekening').value;
    const komitmen = document.getElementById('modal_komitmen').value;
    const tanggal  = document.getElementById('modal_tanggal').value;
    const alasan   = document.getElementById('modal_alasan').value;

    if (!rekening || !komitmen || !tanggal || !alasan) {
      alert("Mohon lengkapi semua data.");
      return;
    }

    try {
      const res = await fetch('./api/flow_par/', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          type: "Update KL Baru",
          rekening,
          komitmen,
          tgl_pembayaran: tanggal,
          alasan
        })
      });
      const result = await res.json();

      if (result.status == 200) {
        alert("✅ Komitmen berhasil disimpan.");
        closeModal();
      } else {
        alert("❌ Gagal menyimpan: " + (result.message || "unknown error"));
      }
    } catch (err) {
      console.error("❌ Gagal simpan:", err);
      alert("Terjadi kesalahan saat menyimpan.");
    }
  });

  // Delegasi klik tombol modal
  document.addEventListener('click', (e) => {
    if (e.target.classList.contains('open-modal-btn')) {
      const btn = e.target;
      openModalKomitmen({
        rekening: btn.dataset.rekening,
        nama: btn.dataset.nama,
        baki_debet: btn.dataset.baki
      });
    }
  });

  // ============ INIT ============ //
  document.addEventListener("DOMContentLoaded", async () => {
    const params = getStoredParams();
    if (!params) {
      document.getElementById('judul_kantor').textContent = 'Parameter tidak ditemukan. Buka dari halaman Rekap Bucket.';
      return;
    }

    setHeaderInfo(params);

    try {
      const resp = await fetchDetailBucket(params);
      const rows = resp.data || [];
      const meta = resp.meta || null;

      renderRingkasan(meta);
      renderRows(rows);
    } catch (e) {
      console.error('Gagal memuat detail bucket:', e);
      const tbody = document.getElementById('bucketBody');
      tbody.innerHTML = `<tr><td colspan="6" class="px-4 py-4 text-center text-red-600">Gagal memuat data.</td></tr>`;
    }
  });
</script>
