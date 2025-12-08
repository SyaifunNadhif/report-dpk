

<div class="container mx-auto p-4 mt-32">
    <!-- Bagian Judul -->
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Info Asset</h2>

    <!-- Bagian Search dan Dropdown -->
    <!-- <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        
        <div class="relative w-full md:w-1/2">
            <input type="text" id="searchInput" placeholder="Cari aset/kabupaten/kota/alamat"
                class="w-full p-3 pl-10 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring focus:border-blue-400">
            <svg class="absolute left-3 top-3 w-5 h-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 2a8 8 0 018 8v1.5a1.5 1.5 0 11-3 0V10a5 5 0 00-5-5H9.5a1.5 1.5 0 110-3H10z"></path>
            </svg>
        </div>


    </div> -->



    <!-- Daftar Aset Lelang -->
    <section class="bg-white py-12">
    <div class="container mx-auto px-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-bold text-gray-800">Asset Terbaik ✨</h3>
            <a href="#" class="text-blue-600 font-semibold hover:underline">Lihat Semua</a>
        </div>

        <!-- Grid Aset -->
        <!-- <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"> -->
            
        <div id="asset-list" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6"></div>
            <!-- Asset cards will be inserted here -->
        </div>

        <!-- </div> -->
    </div>
</section>



<script>
    fetch("./api/asset/home")
        .then(res => res.json())
        .then(res => {
            const container = document.getElementById("asset-list");

            if (res.status === 200 && Array.isArray(res.data)) {
                res.data.forEach(asset => {
                    const cardLink = document.createElement("a");
                    cardLink.href = `./detail?id=${asset.id}`;
                    cardLink.className = "block";

                    const imageSrc = `./img/agunan/${asset.foto1}`;

                    cardLink.innerHTML = `
                        <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transform hover:-translate-y-1 transition-transform duration-200 relative">
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
                    `;

                    container.appendChild(cardLink);
                });
            } else {
                container.innerHTML = `<p class="text-red-500">Data asset tidak ditemukan.</p>`;
            }
        })
        .catch(error => {
            console.error("Error fetching assets:", error);
            document.getElementById("asset-list").innerHTML = `<p class="text-red-500">Gagal memuat data asset.</p>`;
        });

    // Format harga ke Rupiah
    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', { style: 'decimal' }).format(angka);
    }
</script>








</div>

<!-- JavaScript untuk Pencarian -->
<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let cards = document.getElementById('propertyList').getElementsByClassName('bg-white');

    for (let i = 0; i < cards.length; i++) {
        let title = cards[i].getElementsByTagName('h3')[0].innerText.toLowerCase();
        let location = cards[i].getElementsByTagName('p')[1].innerText.toLowerCase();
        
        if (title.views(filter) || location.views(filter)) {
            cards[i].style.display = "";
        } else {
            cards[i].style.display = "none";
        }
    }
});
</script>

