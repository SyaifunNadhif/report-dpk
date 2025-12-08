<!-- <div class="container mx-auto px-4 mt-20 py-8" >
    <table border="1" id="tabelRecovery" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
            <th>Kode Kantor</th>
            <th>Nama Kantor</th>
            <th>Total Pokok</th>
            <th>Total Bunga</th>
            <th>Total PH</th>
            <th>NOA</th>
            </tr>
        </thead>
        <tbody>
            
        </tbody>
    </table>

<div id="daftarDebitur" style="margin-top: 20px;"></div>

</div>




<script>
fetch("./api/hapus_buku/", {
  method: "POST",
  headers: {
    "Content-Type": "application/json"
  },
  body: JSON.stringify({
    type: "recovery",
    start_date: "2025-01-01",
    end_date: "2025-01-24"
  })
})
.then(res => res.json())
.then(res => {
  const tbody = document.querySelector("#tabelRecovery tbody");
  tbody.innerHTML = "";

  const data = res.data;

  // Cari dan tampilkan baris TOTAL dulu
  const totalRow = data.find(d => d.kode_kantor === "TOTAL");
  if (totalRow) {
    const tr = document.createElement("tr");
    tr.style.fontWeight = "bold";
    tr.style.backgroundColor = "#f1f1f1";
    tr.innerHTML = `
      <td>${totalRow.kode_kantor}</td>
      <td>${totalRow.nama_kantor}</td>
      <td>${formatRupiah(totalRow.total_pokok)}</td>
      <td>${formatRupiah(totalRow.total_bunga)}</td>
      <td>${formatRupiah(totalRow.total_ph)}</td>
      <td>${totalRow.noa}</td>
    `;
    tbody.appendChild(tr);
  }

  // Tampilkan baris-baris kantor biasa
  data
    .filter(d => d.kode_kantor !== "TOTAL")
    .forEach(d => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${d.kode_kantor}</td>
        <td>${d.nama_kantor}</td>
        <td>${formatRupiah(d.total_pokok)}</td>
        <td>${formatRupiah(d.total_bunga)}</td>
        <td>${formatRupiah(d.total_ph)}</td>
        <td>
        <a href="#" onclick="event.preventDefault(); loadDebitur('${d.kode_kantor}')">
            ${d.noa}
        </a>
        </td>


      `;
      tbody.appendChild(tr);
    });
})
.catch(error => {
  console.error("Gagal fetch data:", error);
});

function formatRupiah(angka) {
  return new Intl.NumberFormat("id-ID", {
    // style: "currency",
    // currency: "IDR"
  }).format(angka);
}

function loadDebitur(kodeKantor) {
  const start = "2025-01-01";
  const end = "2025-01-24";
  const type = "debitur";

  const container = document.getElementById("daftarDebitur");
  container.innerHTML = "<p>Sedang mengambil data debitur...</p>";

  fetch("./api/hapus_buku/detail", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      type: type,
      kode_kantor: kodeKantor,
      start_date: start,
      end_date: end
    })
  })
  .then(res => res.json())
  .then(res => {
    const list = res.data;

    if (!list || list.length === 0) {
      container.innerHTML = `<p><strong>Tidak ada debitur yang ditemukan.</strong></p>`;
      return;
    }

    let html = `
      <h3>Daftar Debitur Kode Kantor ${kodeKantor}</h3>
      <table border="1" cellpadding="5" cellspacing="0">
        <thead>
          <tr>
            <th>No Rekening</th>
            <th>Nama Nasabah</th>
            <th>Tanggal Transaksi Terakhir</th>
            <th>Pokok</th>
            <th>Bunga</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
    `;

    list.forEach(d => {
      html += `
        <tr>
          <td>${d.no_rekening}</td>
          <td>${d.nama_nasabah}</td>
          <td>${d.tanggal_transaksi}</td>
          <td>${formatRupiah(d.pokok)}</td>
          <td>${formatRupiah(d.bunga)}</td>
          <td>${formatRupiah(d.total)}</td>
        </tr>
      `;
    });

    html += `</tbody></table>`;
    container.innerHTML = html;
    container.scrollIntoView({ behavior: "smooth" });
  })
  .catch(err => {
    container.innerHTML = "<p>Terjadi kesalahan saat mengambil data.</p>";
    console.error("Gagal ambil data debitur", err);
  });
}


</script>


 -->
