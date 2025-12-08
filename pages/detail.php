<?php
// detail.php (contoh)
// $id = $_GET['id'] ?? null;


// if ($id) {
//     $apiUrl = "./api/asset/detail/?id=" . $id;
//     $response = file_get_contents($apiUrl);
//     $result = json_decode($response, true);

//     $asset = $result['data'] ?? null;

//     echo "<pre>";
//     print_r($asset); // Menampilkan seluruh isi respons
//     echo "</pre>";
// } else {
//     echo "ID tidak ditemukan.";
// }



function formatRupiah($angka, $prefix = 'Rp') {
    if (!is_numeric($angka)) {
        return $prefix . ' 0';
    }
    return $prefix . ' ' . number_format($angka, 0, ',', '.');
}




?>

<!-- <style>
    * {
  outline: 1px solid red;

  body {
    overflow-x: hidden; /* Hindari horizontal scroll */
  }
  
}

</style> -->

<script>
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');

    if (id) {
        fetch(`./api/asset/detail/?id=${id}`)
            .then(res => res.json())
            .then(data => {
                console.log("Hasil dari API:", data);

                // Contoh: akses datanya
                if (data.status === 200) {
                    const asset = data.data;
                    console.log("Detail asset:", asset);
                } else {
                    console.warn("Asset tidak ditemukan.");
                }
            })
            .catch(err => {
                console.error("Gagal fetch data:", err);
            });
    } else {
        console.warn("ID tidak ditemukan di URL.");
    }
</script>





<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />



    <!-- Kontainer Utama -->
    <div class="max-w-6xl mx-auto mt-32 flex flex-col-reverse md:flex-row gap-6 px-4">

    <?php
        $id = $_GET['id'] ?? null;

        if (!$id) {
            echo "ID tidak ditemukan";
            exit;
        }

        $response = @file_get_contents(BASE_URL . "/api/asset/detail/?id=$id");
        if (!$response) {
            echo "Gagal mengambil data asset.";
            exit;
        }

        $data = json_decode($response, true);

        if (!isset($data['data'])) {
            echo "Data asset tidak tersedia.";
            exit;
        }

        $asset = $data['data']; // sekarang kita yakin $asset ada
    ?>

 


        <!-- Bagian Kiri -->
        <div id="leftSection" class="w-full md:w-2/3">
            
        
            <!-- Foto -->
            <div id="fotoSection">
    <div id="fotoContainer" class="bg-white shadow-md rounded-lg overflow-hidden">
        <img id="mainImage" src="img/agunan/<?= $asset['foto1'] ?? '-'; ?>" class="w-full h-[350px] object-cover transition-all duration-300">
    </div>

    <!-- Galeri Thumbnail -->
    <div class="flex overflow-x-auto space-x-2 p-2">
        <?php for ($i = 1; $i <= 4; $i++): ?>
            <?php if (!empty($asset["foto$i"])): ?>
                <img 
                    src="img/agunan/<?= $asset["foto$i"]; ?>" 
                    class="thumbnail w-24 h-20 object-cover rounded-lg shrink-0 cursor-pointer hover:opacity-80 border-2 border-transparent hover:border-blue-500 transition"
                    data-img="img/agunan/<?= $asset["foto$i"]; ?>"
                >
            <?php endif; ?>
        <?php endfor; ?>
    </div>

    <p class="text-gray-600 text-sm mt-2">
        Dipublikasikan dan dikelola oleh <?= $asset['nama_kantor'] ?? '-'; ?> (<?= $asset['kode_kantor'] ?? '-'; ?>)
    </p>
</div>

<script>
    // Ambil semua elemen thumbnail
    const thumbnails = document.querySelectorAll('.thumbnail');
    const mainImage = document.getElementById('mainImage');

    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', () => {
            const imgSrc = thumb.getAttribute('data-img');
            mainImage.setAttribute('src', imgSrc);
        });
    });
</script>




            <!-- Menu Sticky -->
            <div id="stickyMenu" class="sticky top-0 bg-white p-4 shadow-md mt-4 rounded-md flex justify-around z-20">
                <a href="#deskripsi" class="text-blue-500 font-semibold">Deskripsi</a>
                <a href="#spesifikasi" class="text-blue-500 font-semibold">Spesifikasi</a>
                <a href="#lokasi" class="text-blue-500 font-semibold">Lokasi</a>
                <a href="#pembelian" class="text-blue-500 font-semibold">Pembelian</a>
                <a href="#kalkulator" class="text-blue-500 font-semibold">Kalkulator</a>
            </div>

            <?php
            // Data dari database (contoh)
            // $deskripsi_aset = "Tanah strategis di Sragen"; 
            // $jadwal_lelang = "18 Februari 2025 11:00 WIB"; // Jika tidak ada jadwal, kosongkan string ini
            // $lokasi_lelang = "KPKNL Surakarta";
            ?>

            <!-- Layout Deskripsi & Jadwal -->
            <div class="flex flex-col <?= !empty($jadwal_lelang) ? 'md:flex-row gap-4' : '' ?> mt-6">
                <!-- Deskripsi Aset -->
                <div class="<?= empty($jadwal_lelang) ? 'w-full' : 'w-full md:w-1/2' ?> bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-lg font-bold">Deskripsi Aset</h2>
                    <p class="text-gray-600 mt-2"><?= $asset['deskripsi'] ?? '-'; ?></p>
                </div>

                <!-- Jadwal Lelang (Muncul hanya jika ada) -->
                <?php if (!empty($jadwal_lelang)) : ?>
                <div class="w-full md:w-1/2 bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-lg font-bold flex items-center">
                        Jadwal Lelang <span class="ml-1">📅</span>
                    </h2>
                    <p class="flex items-center text-gray-600 mt-2">
                        <span class="mr-2">⏰</span> <?= $jadwal_lelang ?>
                    </p>
                    <p class="flex items-center text-gray-600 mt-2">
                        <span class="mr-2">📍</span> <?= $lokasi_lelang ?>
                    </p>
                    <button class="mt-4 px-4 py-2 border border-blue-500 text-blue-500 rounded-lg hover:bg-blue-500 hover:text-white">
                        Ikut Lelang
                    </button>
                </div>
                <?php endif; ?>
            </div>




            <!-- Spesifikasi -->
            <div id="spesifikasi" class="mt-6 bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-bold">Spesifikasi Aset</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mt-4">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 flex items-center justify-center bg-blue-100 text-blue-500 rounded-full text-sm sm:text-base font-bold">LT</span>
                        <p class="text-gray-600 text-sm sm:text-base"><?= $asset['luas_tanah'] ?? '-'; ?> m²</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 flex items-center justify-center bg-blue-100 text-blue-500 rounded-full text-sm sm:text-base font-bold">LB</span>
                        <p class="text-gray-600 text-sm sm:text-base"><?= $asset['luas_bangunan'] ?? '-'; ?>  m²</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 flex items-center justify-center bg-blue-100 text-blue-500 rounded-full text-sm sm:text-base font-bold"><?= $asset['lantai'] ?? '-'; ?> </span>
                        <p class="text-gray-600 text-sm sm:text-base">Lantai</p>
                    </div>
                    <!-- <div class="flex items-center gap-3">
                        <span class="w-10 h-10 flex items-center justify-center bg-blue-100 text-blue-500 rounded-full text-lg sm:text-xl">🛏</span>
                        <p class="text-gray-600 text-sm sm:text-base">4 Kamar</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 flex items-center justify-center bg-blue-100 text-blue-500 rounded-full text-lg sm:text-xl">🚿</span>
                        <p class="text-gray-600 text-sm sm:text-base">2 Kamar Mandi</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 flex items-center justify-center bg-blue-100 text-blue-500 rounded-full text-lg sm:text-xl">🚘</span>
                        <p class="text-gray-600 text-sm sm:text-base">1 Carport</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 flex items-center justify-center bg-blue-100 text-blue-500 rounded-full text-sm sm:text-base font-bold">🔌</span>
                        <p class="text-gray-600 text-sm sm:text-base">1300 kWh</p>
                    </div> -->
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 flex items-center justify-center bg-blue-100 text-blue-500 rounded-full text-sm sm:text-base font-bold"><?= $asset['jenis_surat'] ?? '-'; ?> </span>
                        <p class="text-gray-600 text-sm sm:text-base"><?= $asset['nomor_surat'] ?? '-'; ?> </p>
                    </div>
                    <!-- <div class="flex items-center gap-3">
                        <span class="w-10 h-10 flex items-center justify-center bg-blue-100 text-blue-500 rounded-full text-lg sm:text-xl">📅</span>
                        <p class="text-gray-600 text-sm sm:text-base">Dibangun Tahun 2012</p>
                    </div> -->
                </div>
            </div>

            <?php
            // Ambil data dari database (contoh)
            $google_maps_link = $asset['link_maps'] ?? "https://maps.app.goo.gl/UChgvYGzTLqDQyeb7";
            
            $latitude = (!empty($asset['latitude'])) ? $asset['latitude'] : '-7.004966136697902';
            $longitude = (!empty($asset['longitude'])) ? $asset['longitude'] : '110.47268348264556';

            $koordinatKosong = empty($asset['latitude']) || empty($asset['longitude']);
            

            
            ?>

            <div id="lokasi" class="mt-6 bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-bold">Lokasi Aset</h2>
                <div class="mt-4">
                    <!-- Peta akan dimuat di sini -->
                    <div id="map" style="height: 300px;"></div>

                    <!-- <p class="text-gray-600 mt-2">
                        Alamat: <span id="alamat-text">Mengambil alamat...</span><br>
                        Koordinat: <span id="koordinat"><?= $latitude ?>, <?= $longitude ?></span>
                    </p> -->
                    <p class="text-gray-600 mt-2">
                        Alamat: <span id="alamat-texnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbvbbbbbbbbbbbbbbbcvvvvvvvvvvvvvvvvvvbb"><?= $asset['alamat_asset'] ?? '-'; ?></span><br>
                        Koordinat: <span id="koordinat"><?= $latitude ?>, <?= $longitude ?></span>
                        
                    </p>

                    <!-- Tautan Google Maps -->
                    <a id="google-maps-link" href="<?= $google_maps_link ?>" target="_blank" 
                        class="text-blue-500 hover:underline flex items-center">
                        View on Google Maps
                        <svg class="w-5 h-5 ml-1 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" 
                            viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Tambahkan JS Leaflet -->
            <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
            <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    var latitude = <?= $latitude ?>;
                    var longitude = <?= $longitude ?>;

                    // Tampilkan peta menggunakan Leaflet
                    var map = L.map('map').setView([latitude, longitude], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(map);

                    var marker = L.marker([latitude, longitude]).addTo(map)
                        .bindPopup("Lokasi Aset")
                        .openPopup();

                    // Ambil alamat dari koordinat menggunakan OpenStreetMap API
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`)
                        .then(response => response.json())
                        .then(data => {
                            var alamat = data.display_name || "Alamat tidak ditemukan";
                            document.getElementById("alamat-text").textContent = alamat;
                        })
                        .catch(error => {
                            console.error("Gagal mendapatkan alamat:", error);
                        });
                });
            </script>




            
            <div id="skema" class="bg-white p-6 mt-4 shadow-md rounded-md">
                <h2 class="text-lg font-semibold">Skema Pembelian</h2>

                <!-- Tombol Pilihan -->
                <div class="flex gap-4 mt-4">
                    <button id="btnLelang" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 border border-blue-600 rounded-lg">
                        Lelang
                    </button>
                    <button id="btnKPR" class="px-4 py-2 text-sm font-semibold text-blue-600 border border-blue-600 rounded-lg hover:bg-blue-100">
                        Pembiayaan dengan BKK Joglo
                    </button>
                </div>

                <!-- Konten Skema Lelang (Default Tampil) -->
                <div id="contentLelang" class="mt-4">
                    <p class="text-gray-600">
                        Proses lelang dilakukan <strong>1 (satu) pintu</strong> melalui website dan aplikasi dengan ketentuan <strong>Direktorat Jenderal Kekayaan Negara (DJKN)</strong>. Pastikan sudah melakukan registrasi pada <a href="#" class="text-blue-500">link</a> sesuai jadwal yang tersedia.
                    </p>
                    <div class="flex justify-center mt-4">
                    <img src="img/Group 6 (4).png" 
                        alt="Skema Lelang" 
                        class="w-full max-w-xs min-h-[250px] h-auto md:max-w-lg md:h-[500px] lg:max-w-xl lg:h-[350px]">

                    </div>
                </div>

                <!-- Konten Skema KPR/KI (Default Tersembunyi) -->
                <div id="contentKPR" class="mt-4 hidden">
                    <p class="text-gray-600">
                        BKK Lelang menyediakan fasilitas pembelian dengan pembiayaan  Solusi. Pengajuan dapat dilakukan melalui PT BPR BKK Jateng (PERSERODA).
                    </p>

                    <div class="mt-4">
                        <h3 class="font-semibold text-lg text-blue-600">BKK Joglo</h3>
                        <p class="text-gray-600">Fasilitas kredit untuk pembelian tanah atau bangunan.</p>
                        <ul class="list-disc pl-5 text-gray-700 mt-2">
                            <li>Rumah</li>
                            <li>Ruko</li>
                            <li>Apartemen</li>
                            <li>Tanah Kavling</li>
                        </ul>
                    </div>

                    <!-- <div class="mt-4">
                        <h3 class="font-semibold text-lg text-blue-600">KI</h3>
                        <p class="text-gray-600">Fasilitas kredit untuk pembiayaan barang modal/aktiva tetap.</p>
                        <ul class="list-disc pl-5 text-gray-700 mt-2">
                            <li>Bangunan</li>
                            <li>Tanah</li>
                            <li>Aset Produktif</li>
                        </ul>
                    </div> -->

                    <div class="mt-4">
                        <h3 class="font-semibold text-lg">Dokumen yang Diperlukan untuk BKK Joglo:</h3>
                        <ul class="list-disc pl-5 text-gray-700 mt-2">
                            <li>Dokumen KTP</li>
                            <li>Dokumen NPWP</li>
                            <li>Buku Nikah</li>
                            <li>Kartu Keluarga</li>
                            <li>Surat keterangan gaji</li>
                            <li>Kutipan rekening tabungan terakhir</li>
                        </ul>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="font-semibold text-lg text-blue-600">Dokumen Tambahan BKK Joglo</h3>
                            <ul class="list-disc pl-5 text-gray-700 mt-2">
                                <li><strong>Fixed Income:</strong> Cash flow gaji</li>
                                <li><strong>Non-fixed Income:</strong> Identitas Usaha SIUP/SITU/TDP/Surat Keterangan</li>
                                <li>Usaha lainnya</li>
                                <li>Cash flow usaha</li>
                                <li>Laporan keuangan</li>
                            </ul>
                        </div>
                        <!-- <div>
                            <h3 class="font-semibold text-lg text-blue-600">Dokumen Tambahan KI</h3>
                            <ul class="list-disc pl-5 text-gray-700 mt-2">
                                <li>Identitas Usaha SIUP/SITU/TDP/Surat Keterangan</li>
                                <li>Usaha lainnya</li>
                                <li>Cash flow usaha</li>
                                <li>Laporan keuangan</li>
                            </ul>
                        </div> -->
                    </div>
                </div>
            </div>

            <script>
                document.getElementById('btnLelang').addEventListener('click', function () {
                    document.getElementById('contentLelang').classList.remove('hidden');
                    document.getElementById('contentKPR').classList.add('hidden');

                    // Update tampilan tombol
                    this.classList.add('bg-blue-600', 'text-white');
                    this.classList.remove('text-blue-600', 'hover:bg-blue-100');

                    const btnKPR = document.getElementById('btnKPR');
                    btnKPR.classList.remove('bg-blue-600', 'text-white');
                    btnKPR.classList.add('text-blue-600', 'hover:bg-blue-100');
                });

                document.getElementById('btnKPR').addEventListener('click', function () {
                    document.getElementById('contentKPR').classList.remove('hidden');
                    document.getElementById('contentLelang').classList.add('hidden');

                    // Update tampilan tombol
                    this.classList.add('bg-blue-600', 'text-white');
                    this.classList.remove('text-blue-600', 'hover:bg-blue-100');

                    const btnLelang = document.getElementById('btnLelang');
                    btnLelang.classList.remove('bg-blue-600', 'text-white');
                    btnLelang.classList.add('text-blue-600', 'hover:bg-blue-100');
                });

            </script>



            <!-- Tambahkan Lucide Icons -->
                <!-- <script src="https://unpkg.com/lucide@latest"></script>

                <div id="Akses" class="bg-white p-6 mt-4 shadow-md rounded-md">
                    <h2 class="text-lg font-semibold mb-4">Akses</h2> -->

                    <!-- Grid Akses & Fasilitas -->
                    <!-- <div class="grid grid-cols-4 gap-4 items-center">
                        
                        <i data-lucide="shopping-cart" class="w-5 h-5 text-blue-500">Pasar</i> 
                        <span class="font-semibold text-gray-500">Tidak Ada</span>

                        <i data-lucide="hospital" class="w-5 h-5 text-red-500">Rumah Sakit</i> 
                        <span class="font-semibold text-gray-500">Tidak Ada</span>

                    
                        <i data-lucide="credit-card" class="w-5 h-5 text-green-500">Bank/Atm</i> 
                        <span class="font-semibold text-gray-500">Tidak Ada</span>

                    
                        <i data-lucide="school" class="w-5 h-5 text-yellow-500">Sekolah</i> 
                        <span class="font-semibold text-gray-500">Tidak Ada</span>

                    
                        <i data-lucide="road" class="w-5 h-5 text-gray-500">Jalan TOL</i> 
                        <span class="font-semibold text-gray-500">Tidak Ada</span>

                        
                        <i data-lucide="fuel" class="w-5 h-5 text-orange-500">SPBU</i> 
                        <span class="font-semibold text-gray-500">Tidak Ada</span>
                    </div>
                </div> -->

                <!-- Inisialisasi Lucide Icons -->
                <!-- <script>
                    lucide.createIcons();
            </script> -->



            <div id="kalkulator" class="bg-white p-6 mt-4 shadow-md rounded-md pb-8">
                <h2 class="text-lg font-semibold text-gray-800">Kalkulator KPR</h2>
                <p class="text-gray-600 text-sm">Hitung cicilan Anda di sini...</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <!-- Harga Aset -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Harga Aset</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                            <input id="harga" type="text" 
                                value="<?= isset($asset['harga_jual']) ? number_format($asset['harga_jual'], 0, ',', '.') : '-'; ?>" 
                                readonly
                                class="pl-10 w-full border border-gray-300 rounded-md p-2 text-right bg-gray-100 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>

                    <!-- Uang Muka -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Uang Muka</label>
                        <div class="flex flex-wrap items-center bg-gray-100 p-2 rounded-md gap-2">
                            <input id="dp" type="number" value="10" 
                                class="w-20 border border-gray-300 rounded-md p-2 text-right focus:ring-2 focus:ring-blue-500 outline-none">  
                            <span class="text-gray-500">%</span>
                            <span id="dpAmount" class="text-gray-800 font-medium ml-auto">Rp 47.500.000</span>
                        </div>
                    </div>

                    <!-- Suku Bunga -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Suku Bunga</label>
                        <input id="bunga" type="number" value="10.5" step="0.01" min="10.5" max="12"
                            class="w-full border border-gray-300 rounded-md p-2 text-right focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <!-- Jangka Waktu -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jangka Waktu</label>
                        <select id="tenor" 
                            class="w-full border border-gray-300 rounded-md p-2 bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="5">5 Tahun</option>
                            <option value="10">10 Tahun</option>
                            <option value="15">15 Tahun</option>
                            <!-- <option value="20">20 Tahun</option> -->
                        </select>
                    </div>
                </div>

                <!-- Hasil Cicilan -->
                <div class="text-center mt-6 text-gray-700">
                    <p class="text-sm">Cicilan per bulan:</p>
                    <p id="cicilan" class="text-3xl font-bold text-blue-600 mt-2">Rp 7.729.201</p>
                </div>

                <!-- Catatan -->
                <p class="text-gray-500 text-xs mt-3">
                    *Catatan: Perhitungan ini hanya perkiraan. Untuk informasi lebih lanjut, silakan hubungi bank.
                </p>
            </div>


            <script>
                function formatRupiah(angka) {
                    return new Intl.NumberFormat("id-ID", { style: "decimal" }).format(angka);
                }

                function hitungKPR() {
                    let harga = parseFloat(document.getElementById("harga").value.replace(/\D/g, '')) || 0;
                    let dpPersen = parseFloat(document.getElementById("dp").value) || 0;
                    let bunga = parseFloat(document.getElementById("bunga").value) || 0;
                    let tenor = parseInt(document.getElementById("tenor").value) || 5;

                    let dp = harga * (dpPersen / 100);
                    let pinjaman = harga - dp;
                    let bungaBulanan = bunga / 100 / 12;
                    let bulan = tenor * 12;
                    
                    let cicilan = (pinjaman * bungaBulanan) / (1 - Math.pow(1 + bungaBulanan, -bulan));

                    document.getElementById("dpAmount").innerText = "Rp " + formatRupiah(dp);
                    document.getElementById("cicilan").innerText = "Rp " + formatRupiah(Math.round(cicilan));
                }

                // Format harga aset saat halaman dimuat
                // document.getElementById("harga").value = formatRupiah("input", harga);

                // Otomatis hitung KPR saat input berubah
                document.getElementById("dp").addEventListener("input", hitungKPR);
                document.getElementById("bunga").addEventListener("input", hitungKPR);
                document.getElementById("tenor").addEventListener("change", hitungKPR);

                // Hitung KPR saat halaman pertama kali dimuat
                hitungKPR();
            </script>



        </div>

        <!-- Right Section -->

        <div id="rightSectionWrapper" class="w-full md:w-1/3 relative ">

            <!-- ✅ CARD Content -->
            <div id="rightSection" class="bg-white p-4 shadow-md rounded-lg mt-[5px] md:mt-0">
                <span class="text-green-500 text-sm font-semibold"><?= $asset['proses_penjualan'] ?? '-'; ?></span>
                <h2 class="text-lg font-bold mt-1"><?= $asset['jenis_surat'] ?? '-'; ?> no. <?= $asset['nomor_surat'] ?? '-'; ?></h2>
                <p class="text-blue-600 text-xl font-bold mt-2"><?= formatRupiah($asset['harga_jual']) ?? '-'; ?></p>

                <!-- Spesifikasi -->
                <div class="flex gap-4 mt-3">
                    <div class="bg-gray-100 px-3 py-3 rounded-md">
                        <p class="text-sm font-semibold"><?= $asset['luas_tanah'] ?? '-'; ?> m²</p>
                        
                    </div>
                    <div class="bg-gray-100 px-3 py-3 rounded-md">
                        <p class="text-sm font-semibold"><?= $asset['luas_bangunan'] ?? '-'; ?> m²</p>
                        
                    </div>
                </div>

                
                <!-- ✅ Tombol versi Desktop (Desktop only: ≥1024px) -->
                <div class="hidden lg:block mt-6 space-y-2">
                    <button class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition duration-200">
                        Ajukan KPR
                    </button>
                    <a href="https://wa.me/6288228659668" target="_blank" 
                    class="w-full flex items-center justify-center gap-2 border border-gray-400 text-gray-700 py-3 rounded-lg hover:bg-gray-200 transition duration-200">
                        <img src="https://img.icons8.com/?size=100&id=BkugfgmBwtEI&format=png&color=4B5563" alt="WhatsApp" class="w-5 h-5">
                        Hubungi Kami
                    </a>
                </div>





                <!-- Info tambahan -->
                <p class="text-gray-500 text-xs mt-4">Jam operasional: Senin - Jumat, 08:00 - 16:00 WIB</p>

                <!-- Menu Simpan, Bagikan, Brosur
                <div class="flex justify-between mt-4">
                    <button class="flex items-center gap-1 text-gray-600 hover:text-blue-500">
                        <span>💾</span> Simpan
                    </button>
                    <button class="flex items-center gap-1 text-gray-600 hover:text-blue-500">
                        <span>🔗</span> Bagikan
                    </button>
                    <button class="flex items-center gap-1 text-gray-600 hover:text-blue-500">
                        <span>📄</span> Brosur
                    </button>
                </div> -->
            </div>
        </div>

        <!-- ✅ Tombol versi Mobile (Mobile only: <1024px) -->
        <div id="mobileButtons"
            class="fixed inset-x-0 bottom-0 z-50 lg:hidden bg-white border-t shadow-md p-4">
            <div class="flex gap-3">
                <button class="w-1/2 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700">
                    Ajukan KPR
                </button>
                <a href="https://wa.me/6288228659668" target="_blank"
                class="w-1/2 flex items-center justify-center gap-2 border border-gray-400 text-gray-700 py-3 rounded-lg hover:bg-gray-100">
                    <img src="https://img.icons8.com/?size=100&id=BkugfgmBwtEI&format=png&color=4B5563"
                        alt="WhatsApp" class="w-5 h-5">
                    Hubungi Kami
                </a>
            </div>
        </div>

        






    </div>

    <div id="rekomendasiAset" class="max-w-[90%] mx-auto mt-10">
    <h3 class="text-lg font-semibold mb-4">Rekomendasi Aset untuk Anda</h3>
    <div class="swiper mySwiper">
        <div class="swiper-wrapper" id="rekomendasiWrapper">
            <!-- Data dari API akan dimasukkan di sini -->
        </div>
    </div>
</div>

<script>
    fetch("./api/asset/")
        .then(res => res.json())
        .then(res => {
            const wrapper = document.getElementById("rekomendasiWrapper");

            if (res.status === 200 && Array.isArray(res.data)) {
                res.data.forEach(asset => {
                    const div = document.createElement("div");
                    div.className = "swiper-slide";

                    const imageSrc = `./img/agunan/${asset.foto1}`;

                    div.innerHTML = `
                        <a href="./detail?id=${asset.id}" class="block">
                            <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transform hover:-translate-y-1 transition-transform duration-200 relative w-[280px]">
                                <div class="relative">
                                    <img src="${imageSrc}" alt="Asset" class="w-full h-40 object-cover"
                                        onerror="this.onerror=null;this.src='./img/agunan/byl.jpg';">
                                    <span class="absolute top-2 left-2 bg-green-500 text-white text-xs px-2 py-1 rounded">${asset.proses_penjualan}</span>

                                    <!-- Tombol WhatsApp -->
                                    <a href="https://wa.me/6288228659668" target="_blank" 
                                        class="absolute -bottom-5 right-4 bg-green-500 hover:bg-green-600 text-white rounded-full w-12 h-12 flex items-center justify-center shadow-lg transition duration-200 z-10">
                                        <img src="https://img.icons8.com/?size=100&id=BkugfgmBwtEI&format=png&color=ffffff" alt="WhatsApp" class="w-6 h-6">
                                    </a>
                                </div>

                                <div class="p-4 pt-6">
                                    <p class="text-lg font-bold text-blue-600">Rp ${formatRupiah(asset.harga_jual)}</p>
                                    <p class="text-gray-800 font-semibold">${asset.jenis_surat} - ${asset.nomor_surat}</p>
                                    <p class="text-gray-600 text-sm line-clamp-3 min-h-[4.5rem]">${asset.alamat_asset}</p>
                                    <p class="text-gray-500 text-xs">LT: ${asset.luas_tanah} m² • LB: ${asset.luas_bangunan} m²</p>
                                </div>
                            </article>
                        </a>
                    `;

                    wrapper.appendChild(div);
                });

                // Inisialisasi Swiper
                if (window.swiperInstance) {
                    window.swiperInstance.update();
                } else {
                    window.swiperInstance = new Swiper(".mySwiper", {
                        slidesPerView: 1,
                        spaceBetween: 10,
                        breakpoints: {
                            640: { slidesPerView: 2 },
                            768: { slidesPerView: 3 },
                            1024: { slidesPerView: 4 }
                        },
                        loop: true,
                    });
                }
            } else {
                wrapper.innerHTML = `<p class="text-red-500">Tidak ada data aset ditemukan.</p>`;
            }
        })
        .catch(err => {
            console.error("Gagal fetch API:", err);
            document.getElementById("rekomendasiWrapper").innerHTML = `<p class="text-red-500">Gagal memuat data rekomendasi.</p>`;
        });

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', { style: 'decimal' }).format(angka);
    }
</script>




        <!-- Script Card -->
        <script>


        const mobileButtons = document.getElementById('mobileButtons');
        const rekomendasi = document.getElementById('rekomendasiAset');
        const footer = document.querySelector('footer'); // pastikan kamu punya elemen footer

        window.addEventListener('scroll', () => {
            const btnHeight = mobileButtons.offsetHeight;
            const rekomTop = rekomendasi.getBoundingClientRect().top;
            const footerTop = footer?.getBoundingClientRect().top ?? Infinity;

            // Jika sudah menyentuh rekomendasi atau footer, sembunyikan tombol
            if (rekomTop < btnHeight + 40 || footerTop < btnHeight + 20) {
                mobileButtons.style.transform = 'translateY(100%)';
            } else {
                mobileButtons.style.transform = 'translateY(0)';
            }
        });

        

        function changeImage(element) {
            document.getElementById('mainImage').src = element.src;
        }

        document.addEventListener("DOMContentLoaded", function () {
            const rightSection = document.getElementById("rightSection");
            const rightSectionWrapper = document.getElementById("rightSectionWrapper");
            const leftSection = document.getElementById("leftSection");
            const fotoContainer = document.getElementById("fotoContainer");
            const rekomendasiAset = document.getElementById("rekomendasiAset");

            let originalWidth = rightSectionWrapper.offsetWidth; // Simpan ukuran awal

            function updateStickyPosition() {
                const scrollY = window.scrollY;
                const fotoBottom = fotoContainer.offsetTop + fotoContainer.offsetHeight;
                const rekomendasiTop = rekomendasiAset.offsetTop;
                const rightSectionHeight = rightSection.offsetHeight;
                const leftSectionRight = leftSection.getBoundingClientRect().right;

                if (scrollY > fotoBottom) {
                    if (scrollY + rightSectionHeight + 20 >= rekomendasiTop) {
                        // Berhenti sebelum menyentuh rekomendasiAset
                        rightSection.style.position = "absolute";
                        rightSection.style.top = `${rekomendasiTop - rightSectionHeight - 20}px`;
                        rightSection.style.left = `${leftSectionRight + 20}px`;
                    } else {
                        // Ikuti scroll setelah foto
                        rightSection.style.position = "fixed";
                        rightSection.style.top = "20px";
                        rightSection.style.left = `${leftSectionRight + 20}px`;
                    }
                    rightSection.style.width = `${originalWidth}px`; // Gunakan ukuran awal
                } else {
                    // Awalnya tidak sticky
                    rightSection.style.position = "static";
                    rightSection.style.width = `${originalWidth}px`;
                }
            }

            function setInitialWidth() {
                originalWidth = rightSectionWrapper.offsetWidth; // Perbarui ukuran awal jika layar diresize
            }

            setInitialWidth();
            window.addEventListener("scroll", updateStickyPosition);
            window.addEventListener("resize", setInitialWidth);
        });


    </script>

    <!-- SwiperJS -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script>
        var swiper = new Swiper('.swiper', {
            slidesPerView: 'auto',
            spaceBetween: 20, // Jarak antar card lebih rapat
            freeMode: true, // Geser bebas tanpa tombol navigasi
        });
    </script>

    <!-- Map -->
    <style>
        #map {
            width: 100%;
            height: 400px; /* Sesuaikan tinggi peta */
            border-radius: 8px;
            z-index: 1;
        }

        @media (max-width: 768px) {
            #map {
                height: 300px; /* Ukuran lebih kecil di layar mobile */
            }
        }
    </style>

    <style>
        .sticky-menu {
            transition: all 0.3s ease-in-out;
        }
        @media (max-width: 768px) {
            #stickyMenu {
                display: none; /* Sembunyikan menu di mobile */
            }
            #rightSection {
                order: -1; /* Pindahkan bagian kanan ke atas pada mobile */
            }
            #actionButtons {
                position: fixed;
                bottom: 0;
                left: 0;
                width: 100%;
                background: white;
                z-index: 100;
                box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
                padding: 10px;
                display: flex;
                justify-content: space-around;
            }
        }

        #rightSection {
            transition: all 0.3s ease-in-out;
        }

        #rightSection.sticky {
            position: fixed;
            top: 20px;
            width: 100%;
            max-width: 300px; /* Sesuaikan dengan kebutuhan */
        }


        @media (min-width: 768px) {
            #actionButtons {
                position: static !important;
                box-shadow: none !important;
                background-color: transparent !important;
                padding: 0 !important;
            }
        }

    </style>




 

    
    
    
    
    


    
