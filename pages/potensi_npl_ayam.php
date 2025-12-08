<!-- Potensi NPL -->
<div class="max-w-7xl mx-auto px-4 py-6">
  <h1 class="text-2xl font-bold mb-4">⚠️ Potensi NPL</h1>

  <!-- Filter -->
  <form id="formFilterPotensi" class="flex flex-wrap items-center gap-3 mb-6">
    <label for="closing_date_potensi" class="text-sm">Tanggal Closing:</label>
    <input type="date" id="closing_date_potensi" class="border rounded px-3 py-1 text-sm" required>

    <label for="harian_date_potensi" class="text-sm">Tanggal Harian:</label>
    <input type="date" id="harian_date_potensi" class="border rounded px-3 py-1 text-sm" required>

    <button type="submit" class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
      🔍 Tampilkan
    </button>
  </form>

  <!-- Loading -->
  <div id="loadingPotensi" class="hidden flex items-center gap-2 text-sm text-gray-600 mb-4">
    <svg class="animate-spin h-5 w-5 text-blue-600" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span>Memuat rekap Potensi NPL...</span>
  </div>

  <!-- Tabel Rekap Potensi NPL -->
  <div class="overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-700 bg-white rounded shadow" id="tabelPotensi">
      <thead class="text-xs text-gray-700 uppercase bg-gray-100">
        <tr>
          <th class="px-4 py-2">Kode Cabang</th>
          <th class="px-4 py-2">Nama Cabang</th>
          <th class="px-4 py-2 text-right cursor-pointer" id="sortNoa">NOA ⬍</th>
          <th class="px-4 py-2 text-right cursor-pointer" id="sortBaki">Baki Debet ⬍</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<!-- Modal Detail Debitur Potensi -->
<div id="modalDebiturPotensi" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm flex items-center justify-center">
  <div class="bg-white rounded-lg shadow max-w-6xl w-full max-h-[90vh] overflow-hidden">
    <div class="flex items-center justify-between p-4 border-b">
      <h3 id="modalTitlePotensi" class="text-xl font-semibold">Detail Debitur Potensi NPL</h3>
      <button onclick="closeModalPotensi()" class="text-gray-500 hover:text-gray-700 text-xl">✕</button>
    </div>
    <div class="p-4 overflow-y-auto max-h-[75vh]" id="modalBodyPotensi">
      <p class="text-sm text-gray-500">Memuat data debitur...</p>
    </div>
  </div>
</div>

<script>
  // ====== INIT (prefill tanggal dari API date) ======
  (async () => {
    const d = await getLastHarianData();
    if (!d) return;
    document.getElementById("closing_date_potensi").value = d.last_closing; // ex: 2025-07-31
    document.getElementById("harian_date_potensi").value  = d.last_created; // ex: 2025-08-10
    fetchPotensiData(d.last_closing, d.last_created);
  })();

  async function getLastHarianData() {
    try {
      const r = await fetch('./api/date/', { method: 'GET' });
      const j = await r.json();
      return j.data || null;
    } catch (e) {
      console.error('Gagal ambil tanggal terakhir:', e);
      return null;
    }
  }

  // ====== STATE ======
  let potensiRows = [];
  let potensiTotal = null;
  let sortStatePotensi = { col: null, dir: 1 };

  // ====== EVENTS ======
  document.getElementById("formFilterPotensi").addEventListener("submit", function (e) {
    e.preventDefault();
    const closing = document.getElementById("closing_date_potensi").value;
    const harian  = document.getElementById("harian_date_potensi").value;
    fetchPotensiData(closing, harian);
  });

  document.getElementById("sortNoa").addEventListener("click", () => {
    const dir = sortStatePotensi.col === 'noa' ? -sortStatePotensi.dir : 1;
    sortStatePotensi = { col: 'noa', dir };
    potensiRows.sort((a, b) => dir * (Number(a.noa||0) - Number(b.noa||0)));
    renderPotensiTable(potensiRows);
  });

  document.getElementById("sortBaki").addEventListener("click", () => {
    const dir = sortStatePotensi.col === 'baki_debet' ? -sortStatePotensi.dir : 1;
    sortStatePotensi = { col: 'baki_debet', dir };
    potensiRows.sort((a, b) => dir * ((a.baki_debet||0) - (b.baki_debet||0)));
    renderPotensiTable(potensiRows);
  });

  // ====== FETCH REKAP ======
  function fetchPotensiData(closing_date, harian_date) {
    const loading = document.getElementById("loadingPotensi");
    loading.classList.remove("hidden");

    fetch("./api/npl/", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        type: "Potensi NPL",
        closing_date,
        harian_date
      })
    })
    .then(r => r.json())
    .then(res => {
      const data = res.data || [];
      potensiTotal = data.find(x => (x.nama_cabang || x.nama_kantor) === "TOTAL");
      potensiRows  = data.filter(x => (x.nama_cabang || x.nama_kantor) !== "TOTAL");
      // sort default: baki debet desc
      potensiRows.sort((a,b)=> (b.baki_debet||0) - (a.baki_debet||0));
      renderPotensiTable(potensiRows);
    })
    .catch(() => {
      const tbody = document.querySelector("#tabelPotensi tbody");
      tbody.innerHTML = `<tr><td colspan="4" class="px-4 py-3 text-red-600">Gagal memuat data.</td></tr>`;
    })
    .finally(() => loading.classList.add("hidden"));
  }

  // ====== RENDER REKAP ======
  function renderPotensiTable(rows) {
    const tbody = document.querySelector("#tabelPotensi tbody");
    tbody.innerHTML = "";

    const closing = document.getElementById("closing_date_potensi").value;
    const harian  = document.getElementById("harian_date_potensi").value;

    rows.forEach(r => {
      const kode = r.kode_cabang || "-";
      const nama = r.nama_cabang || r.nama_kantor || "-";
      const noa  = Number(r.noa || 0);
      const baki = Number(r.baki_debet || 0);

      tbody.innerHTML += `
        <tr class="border-b hover:bg-gray-50">
          <td class="px-4 py-3 text-center">${kode}</td>
          <td class="px-4 py-3">${nama}</td>
          <td class="px-4 py-3 text-right">
            <a href="#" class="text-blue-600 hover:underline"
               onclick="event.preventDefault(); loadDebiturPotensi('${kode}','${closing}','${harian}')">
              ${formatNumber(noa)}
            </a>
          </td>
          <td class="px-4 py-3 text-right">${formatRupiah(baki)}</td>
        </tr>`;
    });

    if (potensiTotal) {
      const totalNoa  = Number(potensiTotal.noa || 0);
      const totalBaki = Number(potensiTotal.baki_debet || 0);
      tbody.innerHTML += `
        <tr class="font-bold bg-blue-50 border-t-4 border-blue-200">
          <td></td>
          <td class="px-4 py-3 text-gray-800">TOTAL</td>
          <td class="px-4 py-3 text-right text-blue-800">${formatNumber(totalNoa)}</td>
          <td class="px-4 py-3 text-right text-blue-800">${formatRupiah(totalBaki)}</td>
        </tr>`;
    }
  }

  // ====== DETAIL (MODAL) ======
  function loadDebiturPotensi(kodeKantor, closingDate, harianDate) {
    const modal = document.getElementById("modalDebiturPotensi");
    const title = document.getElementById("modalTitlePotensi");
    const body  = document.getElementById("modalBodyPotensi");

    modal.classList.remove("hidden");
    title.textContent = `Debitur Potensi NPL - Kode Kantor ${kodeKantor}`;
    body.innerHTML = `<p class="text-sm text-gray-500">Memuat data debitur...</p>`;

    fetch("./api/npl/", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        type: "Debitur Potensi NPL",
        kode_kantor: kodeKantor,
        closing_date: closingDate,
        harian_date: harianDate
      })
    })
    .then(r => r.json())
    .then(res => {
      const list = res.data || [];
      if (!list.length) {
        body.innerHTML = `<p class="text-red-600 font-semibold">Tidak ada data debitur.</p>`;
        return;
      }

      let html = `
      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-800 bg-white rounded shadow">
          <thead class="bg-gray-100 text-gray-700">
            <tr>
              <th class="px-4 py-2">No Rekening</th>
              <th class="px-4 py-2">Nama Nasabah</th>
              <th class="px-4 py-2 text-right">Kolek Closing</th>
              <th class="px-4 py-2 text-right">Baki Debet Closing</th>
              <th class="px-4 py-2 text-right">Kolek Harian</th>
              <th class="px-4 py-2 text-right">Baki Debet Harian</th>
              <th class="px-4 py-2 text-right">HM</th>
              <th class="px-4 py-2 text-right">HMP</th>
              <th class="px-4 py-2 text-right">HMB</th>
              <th class="px-4 py-2">JT Closing</th>
              <th class="px-4 py-2">JT Harian</th>
              <th class="px-4 py-2">Tgl Realisasi</th>
              <th class="px-4 py-2">Tgl Bayar Terakhir</th>
              <th class="px-4 py-2 text-right">Angs Pokok</th>
              <th class="px-4 py-2 text-right">Angs Bunga</th>
              <th class="px-4 py-2 text-right">Angs Denda</th>
            </tr>
          </thead><tbody>`;

      list.forEach(d => {
        html += `
          <tr class="border-b">
            <td class="px-4 py-2">${d.no_rekening}</td>
            <td class="px-4 py-2">${d.nama_nasabah}</td>
            <td class="px-4 py-2 text-right">${d.kolek_closing || '-'}</td>
            <td class="px-4 py-2 text-right">${formatRupiah(d.baki_debet_closing || 0)}</td>
            <td class="px-4 py-2 text-right">${d.kolek_harian || 'Lunas'}</td>
            <td class="px-4 py-2 text-right">${formatRupiah(d.baki_debet_harian || 0)}</td>
            <td class="px-4 py-2 text-right">${d.hm_harian ?? '-'}</td>
            <td class="px-4 py-2 text-right">${d.hmp_harian ?? '-'}</td>
            <td class="px-4 py-2 text-right">${d.hmb_harian ?? '-'}</td>
            <td class="px-4 py-2">${formatTanggal(d.jt_closing)}</td>
            <td class="px-4 py-2">${formatTanggal(d.jt_harian)}</td>
            <td class="px-4 py-2">${formatTanggal(d.tgl_realisasi)}</td>
            <td class="px-4 py-2">${formatTanggal(d.tgl_trans_terakhir)}</td>
            <td class="px-4 py-2 text-right">${d.angsuran_pokok ? formatRupiah(d.angsuran_pokok) : '-'}</td>
            <td class="px-4 py-2 text-right">${d.angsuran_bunga ? formatRupiah(d.angsuran_bunga) : '-'}</td>
            <td class="px-4 py-2 text-right">${d.angsuran_denda ? formatRupiah(d.angsuran_denda) : '-'}</td>
          </tr>`;
      });

      html += `</tbody></table></div>`;
      body.innerHTML = html;
    })
    .catch(() => {
      body.innerHTML = `<p class='text-red-600'>Gagal mengambil data debitur.</p>`;
    });
  }

  function closeModalPotensi() {
    document.getElementById("modalDebiturPotensi").classList.add("hidden");
  }

  // ====== HELPERS ======
  function formatRupiah(n) {
    return new Intl.NumberFormat("id-ID").format(Number(n||0));
  }
  function formatNumber(n) {
    return Number(n||0).toLocaleString("id-ID");
  }
  function formatTanggal(tgl) {
    if (!tgl) return "-";
    const d = new Date(tgl);
    if (isNaN(d)) return tgl; // biar aman kalau sudah formatted yyyy-mm-dd
    return `${String(d.getDate()).padStart(2,'0')}-${String(d.getMonth()+1).padStart(2,'0')}-${d.getFullYear()}`;
  }
</script>
