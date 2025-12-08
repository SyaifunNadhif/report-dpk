

<div class="w-full pt-20"> <!-- Menambahkan padding atas agar tidak tertutup navbar -->
    <!-- Header FAQ Full Width -->
    <div class="text-center bg-gradient-to-b from-blue-500 to-blue-300 py-10 shadow-lg w-full relative z-10">
        <h2 class="text-4xl font-bold text-white">FAQ</h2>
        <p class="text-lg text-white mt-2">Ada yang bisa kami bantu?</p>
        <div class="mt-4 flex justify-center">
            <div class="relative w-full max-w-md">
                <input type="text" placeholder="Ketik info yang Anda cari..." 
                    class="w-full px-4 py-3 border rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                <span class="absolute right-4 top-3 text-gray-400">🔍</span>
            </div>
        </div>
    </div>

    <!-- Container Konten FAQ -->
    <div class="max-w-7xl mx-auto p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Kategori Pertanyaan -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Kategori Bantuan</h3>
            <button onclick="toggleFAQ('lelang')" class="flex items-center bg-blue-500 text-white p-4 rounded-lg shadow-md hover:bg-blue-600 transition w-full">
                <!-- <img src="icon-lelang.png" alt="Motor & Mobil" class="w-8 h-8 mr-3"> -->
                <span class="text-lg font-semibold">Lelang</span>
            </button>
            <div id="faq-lelang" class="hidden mt-2"></div>
            <button onclick="toggleFAQ('kpknl')" class="flex items-center bg-blue-500 text-white p-4 rounded-lg shadow-md hover:bg-blue-600 transition w-full mt-4">
                <!-- <img src="icon-kpknl.png" alt="kpknl" class="w-8 h-8 mr-3"> -->
                <span class="text-lg font-semibold">KPKNL</span>
            </button>
            <div id="faq-kpknl" class="hidden mt-2"></div>
        </div>

        <!-- Pertanyaan Populer (Konten Dinamis) -->
        <div class="md:col-span-2 bg-white p-6 rounded-lg shadow-lg" id="faq-content">
            <h3 class="text-2xl font-semibold text-gray-800">Pertanyaan</h3>
            <div id="faq-display" class="mt-4 border-t pt-4">
                <p class="text-gray-600">Silakan pilih kategori untuk melihat pertanyaan.</p>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFAQ(category) {
    let content = getFAQContent(category);
    let isMobile = window.innerWidth < 768;

    if (isMobile) {
        document.querySelectorAll("[id^='faq-']").forEach(el => el.classList.add("hidden"));
        let mobileTarget = document.getElementById(`faq-${category}`);
        mobileTarget.classList.toggle("hidden");
        if (!mobileTarget.classList.contains("hidden")) {
            mobileTarget.innerHTML = content;
        } else {
            mobileTarget.innerHTML = "";
        }
    } else {
        document.querySelectorAll("[id^='faq-']").forEach(el => el.classList.add("hidden")); // Sembunyikan semua FAQ di mobile
        let faqContent = document.getElementById("faq-content");
        faqContent.innerHTML = `<h2 class='text-2xl font-semibold text-gray-800'>Pertanyaan</h2><div class='mt-4'>` + content + "</div>"; // Tambahkan margin-bottom lebih besar dan spacing pada konten
        faqContent.dataset.activeCategory = category; // Simpan kategori yang aktif
    }
}

function getFAQContent(category) {
    if (category === "lelang") {
        return `
            <details class="mb-4 border-b pb-2 open">
                <summary class="cursor-pointer font-medium text-blue-600 flex justify-between items-center">
                    Informasi apa sajakah yang disajikan dalam website Catalog Lelang BKK ini
                    <span>▼</span>
                </summary>
                <p class="mt-4 text-gray-600">Website ini menyajikan informasi aset-aset yang dijual, baik melalui mekanisme lelang maupun jual damai. 
                Aset-aset yang diinformasikan pada Website Catalog Lelang BKK ini merupakan aset yang dijadikan jaminan kredit oleh nasabah pada Bank BPR BKK Jateng (Perseroda).</p>
            </details>
            <details class="mb-4 border-b pb-2 open">
                <summary class="cursor-pointer font-medium text-blue-600 flex justify-between items-center">
                    Siapakah pihak penjual dalam penjualan dengan mekanisme lelang ?
                    <span>▼</span>
                </summary>
                <p class="mt-4 text-gray-600">Penjual adalah orang, badan hukum/usaha atau instansi
                    yang berdasarkan peraturan perundang-undangan atau
                    perjanjian berwenang untuk menjual barang secara lelang.
                    Penjual dalam penjualan dengan mekanisme lelang adalah
                    Bank BPR BKK Jateng (Perseroda).
                </p>
            </details>
            `;
            
    } else if (category === "kpknl") {
        return `
            <details class="mb-4 border-b pb-2 open">
                <summary class="cursor-pointer font-medium text-blue-600 flex justify-between items-center">
                    Bagaimana melakukan pembelian asset melalui KPKNL?
                    <span>▼</span>
                </summary>
                <p class="mt-4 text-gray-600">Nanti akan ada beberapa asset kita yang muncul pada website KPKNL, pada website tersebut bisa melakukan penawaran</p>
            </details>`;
    }
    return "<p class='text-gray-600'>Data tidak ditemukan.</p>";
}


    function checkScreenSize() {
        let isMobile = window.innerWidth < 768;
        let faqContent = document.getElementById("faq-content");
        let faqDisplay = document.getElementById("faq-display");
        let activeFAQ = document.querySelector("[id^='faq-']:not(.hidden)"); // Ambil elemen FAQ yang sedang aktif di mobile

        if (isMobile) {
            faqContent.style.display = "none";
        } else {
            faqContent.style.display = "block";
            


            faqDisplay.innerHTML = activeFAQ.innerHTML;
            activeFAQ.classList.add("hidden"); // Sembunyikan FAQ yang ada di kategori
   

            // Jika faq-display kosong, tampilkan kategori default
            if (faqDisplay.innerHTML.trim() === "" || faqDisplay.innerHTML.views("Silakan pilih kategori")) {
                faqDisplay.innerHTML = getFAQContent("lelang");
            }
        }
    }


    window.addEventListener("load", () => {
        checkScreenSize();
        if (window.innerWidth >= 768) {
            document.getElementById("faq-display").innerHTML = getFAQContent("lelang");
        }
    });

    window.addEventListener("resize", checkScreenSize);
</script>

