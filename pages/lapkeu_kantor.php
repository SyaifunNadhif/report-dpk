<style>
  :root { --primary: #2563eb; --bg: #f8fafc; --text: #334155; }
  
  /* Styling Baris Berdasarkan Level */
  .row-level-1 { background-color: #e2e8f0 !important; font-weight: 800; cursor: pointer; }
  .row-level-2 { background-color: #f1f5f9 !important; font-weight: 700; cursor: pointer; }
  .row-level-3 { background-color: #f8fafc !important; font-weight: 600; cursor: pointer; }
  .row-detail { display: table-row; }
  .hidden-row { display: none; }
  
  .caret { display: inline-block; transition: transform 0.2s; margin-right: 8px; color: #64748b; font-size: 10px; }
  .rotate { transform: rotate(90deg); }

  .rekap-card { background: white; border-radius: 10px; padding: 12px 16px; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
  .val-plus { color: #059669; font-weight: 800; font-family: 'JetBrains Mono', monospace; }
  .val-minus { color: #dc2626; font-weight: 800; font-family: 'JetBrains Mono', monospace; }
  
  .table-container { border: 1px solid #e2e8f0; border-radius: 12px; background: white; overflow: hidden; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
  
  /* Input & Button Styling */
  .inp-modern { border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 12px; font-size: 13px; font-weight: 600; background: #fff; width: 100%; height: 40px; color: #334155; transition: all 0.2s; outline: none; }
  .inp-modern:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
  .inp-modern:disabled { background-color: #f1f5f9; color: #64748b; cursor: not-allowed; }
</style>

<div class="max-w-7xl mx-auto px-4 py-4 h-screen flex flex-col space-y-4 bg-[#f8fafc]">
  
  <div class="flex flex-col md:flex-row justify-between items-end gap-4 shrink-0 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
    <div class="flex items-center gap-3">
      <div class="bg-blue-600 text-white p-2.5 rounded-xl shadow-md">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"></path><path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path><path d="M9 21v-4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v4"></path></svg>
      </div> 
      <div class="flex flex-col">
        <span class="text-xl font-black text-slate-800 tracking-tight leading-tight">LAPORAN KEUANGAN</span>
        <span id="badgeUnit" class="text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded w-fit uppercase font-bold tracking-wider mt-0.5">Memuat...</span>
      </div>
    </div>

    <form id="filterForm" class="flex flex-wrap items-end gap-3">
      <div class="flex flex-col w-[160px]">
        <label class="text-[10px] font-bold text-blue-900 uppercase ml-1 mb-1.5 tracking-wider">Laporan</label>
        <select id="type_report" class="inp-modern" onchange="fetchRekap()">
          <option value="neraca detail kantor">NERACA</option>
          <option value="laba rugi detail kantor">LABA RUGI</option>
        </select>
      </div>

      <div class="flex flex-col w-[220px]">
        <label class="text-[10px] font-bold text-blue-900 uppercase ml-1 mb-1.5 tracking-wider">Cabang</label>
        <select id="opt_kantor_rec" class="inp-modern"><option value="">Memuat...</option></select>
      </div>

      <div class="flex flex-col w-[150px]">
        <label class="text-[10px] font-bold text-blue-900 uppercase ml-1 mb-1.5 tracking-wider">Actual (Harian)</label>
        <input type="date" id="harian_date" class="inp-modern text-center">
      </div>
      
      <div class="flex gap-2">
        <button type="submit" class="h-[40px] px-5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-bold shadow-md transition flex items-center gap-2" title="Cari Data">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          CARI
        </button>
        <button type="button" onclick="exportToExcel()" class="h-[40px] w-[40px] bg-[#10b981] hover:bg-[#059669] text-white rounded-lg shadow-md transition flex items-center justify-center" title="Export Excel">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
        </button>
      </div>
    </form>
  </div>

  <div id="rekapContainer" class="grid grid-cols-1 md:grid-cols-3 gap-4 shrink-0"></div>

  <div class="flex-1 min-h-0 table-container relative bg-white">
    <div id="loadingFP" class="hidden absolute inset-0 bg-white/80 z-50 flex flex-col items-center justify-center">
       <div class="animate-spin h-10 w-10 border-4 border-blue-200 border-t-blue-600 rounded-full mb-2"></div>
       <span class="text-xs font-bold text-blue-600 tracking-wider">MENYUSUN DATA...</span>
    </div>

    <div class="h-full overflow-auto">
      <table class="w-full text-left border-separate border-spacing-0" id="tabelLapKeu">
        <thead class="sticky top-0 z-40 bg-slate-50">
          <tr>
            <th class="p-3.5 text-[11px] font-bold text-slate-500 uppercase border-b border-slate-200 w-[120px]">Kode</th>
            <th class="p-3.5 text-[11px] font-bold text-slate-500 uppercase border-b border-slate-200">Uraian Perkiraan</th>
            <th class="p-3.5 text-[11px] font-bold text-slate-500 uppercase border-b border-slate-200 text-right">Saldo (IDR)</th>
          </tr>
        </thead>
        <tbody id="lapBody"></tbody>
      </table>
    </div>
  </div>
</div>

<script>
  const API_LAP = './api/lapkeu'; // PASTIIN INI NAMA FILE ROUTER API LU YANG BENER!
  const API_KODE = './api/kode/';
  
  let rawDataResult = [];
  window.currentUser = { kode: '000' };

  const fmtNom = n => new Intl.NumberFormat('id-ID').format(Math.round(Number(n||0)));

  function getYesterdayDate() {
      const today = new Date();
      today.setDate(today.getDate() - 1); 
      const yyyy = today.getFullYear();
      const mm = String(today.getMonth() + 1).padStart(2, '0');
      const dd = String(today.getDate()).padStart(2, '0');
      return `${yyyy}-${mm}-${dd}`;
  }

  // ==========================================
  // 🔥 DICTIONARY WAJIB (JANGAN DIHAPUS) 🔥
  // ==========================================
  function getCoaClientSide() {
      return {
            '1': 'Aset', '101': 'Kas', '10101': 'Kas Besar', '10102': 'Kas Kecil', '10103': 'Kas Dalam Atm', '10104': 'Kas Dalam Perjalanan (Cash In Transit)', '10105': 'Kas Branchless', '10106': 'Kas Echannel', '10199': 'TDP', '102': 'Kas Dalam Valuta Asing', '10201': 'Kas Vallas - Dollar', '10202': 'Kas Vallas - Euro', '10203': 'Kas Vallas - Yen', '10299': 'Travel Cheque', '103': 'Surat Berharga', '10301': 'Sertifikat Bank Indonesia', '104': 'Penempatan Pada Bank Lain', '10401': 'Giro', '10402': 'Tabungan', '10403': 'Deposito Berjangka', '10404': 'Sertifikat Deposito', '105': 'Cadangan Kerugian Penurunan Nilai (ABA)', '10501': 'CKPN (ABA)', '106': 'Kredit Yang Diberikan', '10601': 'Kredit Yang Diberikan Baki Debet', '1060101': 'Kredit Modal Kerja', '106010101': 'Pertanian', '106010102': 'Multi Manfaat', '106010103': 'Sinden', '106010104': 'Bumdes', '106010105': 'Korporasi', '106010106': 'KMB', '1060102': 'Kredit Investasi', '1060103': 'Kredit Konsumsi', '106010301': 'Joglo', '106010302': 'Pegawai', '106010303': 'Karyawan', '106010304': 'Pensiunan', '106010305': 'Perangkat Desa', '106010306': 'Lainnya', '1060104': 'Kredit Mikro Bkk', '1060105': 'Kredit BKK Joglo', '1060106': 'Kredit BKK Sinden', '1060107': 'Kredit BKK Korporasi', '1060108': 'Kredit BKK Bumdes', '1060109': 'Kredit BKK Musiman', '1060110': 'Kredit Kolektif Karyawan (K3)', '1060111': 'Kredit KPP', '1060112': 'Kredit UMKM BKK (KUB)', '1060113': 'Kredit UMKM BKK (KUB) 6', '1060114': 'Kredit Koperasi', '1060115': 'Kredit Agro', '1060116': 'Kredit Bahari', '1060117': 'Kredit BKK Joglo Mitra', '1060118': 'Kredit BKK Joglo Khusus Pegawai', '10602': 'KYD Provisi / Administrasi', '1060201': 'Pendapatan Ditangguhkan - Provisi', '106020101': 'KYD Provisi Kredit Modal Kerja', '106020102': 'KYD Provisi Kredit Investasi', '106020103': 'KYD Provisi Kredit Konsumsi', '106020104': 'KYD Provisi Kredit Mikro Bkk', '106020105': 'KYD Provisi Kredit BKK Joglo', '106020106': 'KYD Provisi Kredit BKK Sinden', '106020107': 'KYD Provisi Kredit BKK Korporasi', '106020108': 'KYD Provisi Kredit BKK Bumdes', '106020109': 'KYD Provisi Kredit BKK Musiman', '106020110': 'KYD Provisi Kredit Kolektif Karyawan (K3)', '106020111': 'KYD Provisi Kredit KPP', '106020112': 'KYD Provisi Kredit KUB', '106020113': 'KYD Provisi Kredit KUB 6', '106020114': 'KYD Provisi Kredit Koperasi', '106020115': 'KYD Provisi Kredit Agro', '106020116': 'KYD Provisi Kredit Bahari', '106020117': 'KYD Provisi Kredit BKK Joglo Mitra', '106020118': 'KYD Provisi Kredit BKK Joglo Khusus Pegawai', '1060202': 'Pendapatan Ditangguhkan - Administrasi', '106020201': 'KYD Adm Kredit Modal Kerja', '106020202': 'KYD Adm Kredit Investasi', '106020203': 'KYD Adm Kredit Konsumsi', '106020204': 'KYD Adm Kredit Mikro BKK', '106020205': 'KYD Adm Kredit BKK Joglo', '106020206': 'KYD Adm Kredit BKK Sinden', '106020207': 'KYD Adm Kredit BKK Korporasi', '106020208': 'KYD Adm Kredit BKK Bumdes', '106020209': 'KYD Adm Kredit BKK Musiman', '106020210': 'KYD Adm Kredit Kolektif Karyawan (K3)', '106020211': 'KYD Adm Kredit KKP', '106020212': 'KYD Adm Kredit KUB', '106020213': 'KYD Adm Kredit KUB (Promo)', '106020214': 'KYD Adm Kredit Koperasi', '106020215': 'KYD Adm Kredit Agro', '106020216': 'KYD Adm Kredit Bahari', '106020217': 'KYD Adm Kredit Joglo Mitra', '106020218': 'KYD Adm Kredit BKK Joglo Khusus Pegawai', '10603': 'KYD Biaya Transaksi', '1060301': 'Biaya Di Tangguhkan - Biaya Transaksi', '106030101': 'KYD By Trans Kredit Modal Kerja', '106030102': 'KYD By Trans Kredit Investasi', '106030103': 'KYD By Trans Kredit Konsumsi', '106030104': 'KYD By Trans Kredit Mikro Bkk', '106030105': 'KYD By Trans Kredit BKK Joglo', '106030106': 'KYD By Trans Kredit BKK Sinden', '106030107': 'KYD By Trans Kredit BKK Korporasi', '106030108': 'KYD By Trans Kredit BKK Bumdes', '106030109': 'KYD By Trans Kredit BKK Musiman', '106030110': 'KYD By Trans Kredit Kolektif Karyawan (K3)', '106030111': 'KYD By Trans Kredit KPP', '106030112': 'KYD By Trans Kredit KUB', '106030113': 'KYD By Trans Kredit Koperasi', '106030114': 'KYD By Trans Kredit Agro', '106030115': 'KYD By Trans Kredit Bahari', '106030116': 'KYD By Trans Kredit BKK Joglo Mitra', '106030117': 'KYD By Trans Kredit BKK Joglo Khusus Pegawai', '10604': '-/- Pendapatan Yang Ditangguhkan Dalam Rangka', '10605': '-/- Cadangan Kerugian Restrukturisasi', '10606': 'Selisih Flat dan EIR', '107': 'Cadangan Kerugian Penurunan Nilai (Kredit)', '10701': 'CKPN Individual', '10702': 'CKPN Kolektif', '108': 'Agunan Yang Diambil Alih (AYDA)', '109': 'Aktiva Tetap & Inventaris', '10901': 'Tanah', '10902': 'Gedung', '10903': 'Peralatan Dan Perlengkapan', '10904': 'Kendaraan', '10905': 'Lainnya', '110': '(-/-) Akumulasi Penyusutan Dan Penurunan Nilai', '11001': 'Akumulasi Peny. Gedung', '1100101': 'Akum. Gedung -/-', '11002': 'Akumulasi Peny. Inventaris', '1100201': 'Akum. Kendaraan', '1100202': 'Akum. Peralatan Dan Perlengkapan', '1100203': 'Akum. Lainnya', '111': 'Aset Tidak Berwujud', '11101': 'Program Aplikasi Core Banking (Software)', '11102': 'Lainnya', '11103': '(-/-) Akumulasi Amortisasi Dan Penurunan Nilai', '1110301': 'Akum. Amortisasi Peny. Nilai -/-', '111030101': 'Program Aplikasi Core Banking -/-', '111030102': 'Lainnya -/-', '112': 'Aset Antar Kantor', '11200': 'AKA Kantor Pusat', '113': 'Aset Lain Lain', '11301': 'Pendapatan Bunga Yang Akan Diterima (Pyad)', '1130101': 'PYAD - Penempatan Pada Bank Lain', '1130102': 'PYAD - Kredit Yang Diberikan', '11302': 'Premi Penjamin Lps Dibayar Dimuka', '11303': 'Pajak Di Bayar Dimuka', '1130301': 'Pajak Dibayar Dimuka PPH', '1130302': 'Pajak Dibayar Dimuka PPN', '11304': 'Aset Pajak Tangguhan', '11305': 'Biaya Di Bayar Di Muka', '1130501': 'Biaya Dibayar Dimuka- Sewa', '1130502': 'Biaya Dibayar Dimuka- Sewa Gedung', '1130503': 'Biaya Dibayar Dimuka- Sewa Kendaraan', '1130504': 'Biaya Dibayar Dimuka- Sewa Inventaris', '1130505': 'Biaya Dibayar Dimuka- Sewa Lainnya', '1130506': 'Biaya Dibayar Dimuka - Asuransi Purjab', '1130507': 'Biaya Dibayar Dimuka - Bunga Deposito', '1130508': 'Biaya Dibayar Dimuka - BPJS TK', '1130509': 'Biaya Dibayar Dimuka - BPJS Kesehatan', '1130510': 'Biaya Dibayar Dimuka - Asuransi', '1130599': 'Biaya Dibayar Dimuka - Lainnya', '11306': 'Tagihan Kepada Perusahaan Asuransi', '11307': 'Uang Muka Kegiatan Operasional', '1130701': 'Uang Muka Akomodasi dan Rapat', '1130702': 'Uang Muka Pembelian', '1130703': 'Uang Muka Operasional Kantor Wilayah', '1130704': 'Uang Muka Jasa Pihak Ketiga', '1130705': 'Uang Muka Pendidikan dan Pelatihan', '1130706': 'Uang Muka Penanganan Kredit Bermasalah', '1130799': 'Uang Muka Kegiatan Operasional Lainnya', '11399': 'Aset lain-lain Lainnya', '1139901': 'Persediaan Barang Cetakan', '1139902': 'Persediaan Materai', '1139903': 'Mata Uang Yg Ditarik Peredaran', '1139904': 'Deposit PPOB', '1139905': 'Deposit Mobile Banking', '1139906': 'Kredit Dalam Penyelesaian', '1139907': 'Titipan QRIS', '1139999': 'Lain-Lain',
            '2': 'Kewajiban', '201': 'Kewajiban-Kewajiban Yang Segera', '20101': 'Deposito Jatuh Tempo Yg Blm Ditarik', '20102': 'Tabungan Berjangka Yg Jth Tmpo Yg Blm Ditarik', '20103': 'Kewajiban Kpd Pemerintah Yg Hrs Dibayar', '2010301': 'Pph Tabungan Final (Pasal 4 Ayat 2)', '2010302': 'Pph Deposito (Pasal 4 Ayat 2)', '2010303': 'PPh Pengurus Dan Pegawai (Ps 21 &/ 26)', '2010304': 'Pph Juru Bayar (Pasal 21 &/ 26)', '2010305': 'Ppn', '201030501': 'Ppn Barang (Pasal 22)', '201030502': 'Ppn Jasa (Pasal 23)', '2010306': 'Hutang Pajak Badan (Pasal 29)', '2010307': 'Pajak Lainnya', '20104': 'Sanksi Kewajiban Membayar Kepada Bi Yg Blm Dib', '20105': 'Titipan Nasabah', '2010501': 'Kiriman Uang', '2010502': 'Titipan Pln', '2010503': 'Titipan Pdam', '2010504': 'Kreditur / Simpanan', '2010505': 'Debitur / Angsuran Kredit', '2010506': 'Notaris', '2010507': 'Premi Asuransi', '201050701': 'Bumi Putra', '201050702': 'Askrida', '201050703': 'Jamkrida', '201050704': 'Jiwasraya', '201050705': 'BPJS TK KREDIT', '201050799': 'Lainnya', '20106': 'KYD Bersaldo Kredit', '20107': 'Deviden Yang Belum Di Bayarkan', '2010701': 'Pemerintah Provinsi', '2010702': 'Pemerintah Kabupaten', '20108': 'Selisih Hasil Penjualan Ayda', '20109': 'Imbalan Kerja', '2010901': 'Dana Kesejahteraan Yang Harus Dibayar', '2010902': 'Jasa Produksi Yang Harus Dibayar', '20110': 'Premi BPJS Kesehatan', '20111': 'Premi BPJS Ketenagakerjaan', '20199': 'Kewajiban Segera Lainnya', '2019901': 'Dana Kesejahteraan', '2019902': 'PPOB / EDC', '2019903': 'Dana Bergulir', '2019904': 'Subsidi Bunga', '2019905': 'Pembayaran Pbb', '2019906': 'Pembayaran Pdam', '2019907': 'Pembayaran Pajak Kendaraan', '2019908': 'ABA Dalam Penyelesaian', '2019909': 'Kewajiban Gaji', '2019999': 'KWS Lainnya Lain-lain', '202': 'Utang Bunga', '20201': 'Tabungan Berjangka', '20202': 'Deposito', '2020201': 'A. Sudah Jatuh Tempo', '2020202': 'B. Belum Jatuh Tempo', '20203': 'Simpanan Dari Bank Lain', '2020301': 'A. Sudah Jatuh Tempo', '2020302': 'B. Belum Jatuh Tempo', '20204': 'Pinjaman Yang Diterima', '2020401': 'A. Pinjaman Yang Diterima Sudah Jatuh Tempo', '2020402': 'B. Pinjaman Yang Diterima Belum Jatuh Tempo', '20299': 'Bunga Lainnya', '203': 'Utang Pajak', '20301': 'Taksiran Pajak Penghasilan Pph Badan', '204': 'Simpanan', '20401': 'Tabungan', '2040101': 'Tabungan Wajib', '2040102': 'Tabungan Tamades', '2040103': 'Tabungan Tamades 1 (Bunga Harian)', '2040104': 'Tabungan Tamades 2 (Tabungan Program)', '2040105': 'Tabungan Tamades 3', '2040106': 'Tabungan Tamades 4', '2040107': 'Tabungan Tamades 5', '2040108': 'Tabungan Pelajar', '2040109': 'TAMADES', '2040110': 'TAWA', '2040111': 'TAWA PLUS', '2040112': 'Tabungan Kredit BKK', '2040113': 'Tabungan Mitra BKK', '2040114': 'Tabungan BKK Prioritas', '20402': 'Deposito', '2040201': 'Deposito 1 Bulan', '2040202': 'Deposito 3 Bulan', '2040203': 'Deposito 6 Bulan', '2040204': 'Deposito 9 Bulan', '2040205': 'Deposito 12 Bulan', '205': 'Simpanan Dari Bank Lain', '20501': 'Bank Indonesia', '20502': 'Bank Lain', '2050201': 'Deposito', '2050202': 'Tabungan', '206': 'Pinjaman Diterima', '20601': 'Bank Indonesia', '20602': 'Bank Lain', '2060201': 'Bank Umum', '2060202': 'Bpr', '2060203': 'Terkait Apex', '2060204': 'Dalam Rangka Linkage', '20603': 'Dari Bukan Bank', '2060301': 'Kewajiban Sewa Pembiayaan', '2060399': 'Lainnya', '20699': 'Lainnya', '207': 'Dana Setoran Modal Kewajiban', '20701': 'Pemerintah Provinsi Jawa Tengah', '20702': 'Pemerintah Kabupaten', '2070201': 'Pemkab Semarang', '2070202': 'Pemkot Salatiga', '2070203': 'Pemkab Pati', '2070204': 'Pemkab Rembang', '2070205': 'Pemkab Kendal', '2070206': 'Pemkab Demak', '2070207': 'Pemkab Banjarnegara', '2070208': 'Pemkab Wonosobo', '2070209': 'Pemkab Purworejo', '2070210': 'Pemkab Magelang', '2070211': 'Pemkab Cilacap', '2070212': 'Pemkab Purbalingga', '2070213': 'Pemkab Banyumas', '2070214': 'Pemkab Temanggung', '2070215': 'Pemkab Boyolali', '2070216': 'Pemkab Karanganyar', '2070217': 'Pemkab Wonogiri', '2070218': 'Pemkab Klaten', '2070219': 'Pemkab Sukoharjo', '2070220': 'Pemkot Surakarta', '2070221': 'Pemkab Sragen', '2070222': 'Pemkot Pekalongan', '2070223': 'Pemkab Tegal', '2070224': 'Pemkab Batang', '2070225': 'Pemkab Pemalang', '2070226': 'Pemkab Pekalongan', '2070227': 'Pemkot Tegal', '2070228': 'Pemkab Brebes', '2070229': 'Pemkab Kebumen', '208': 'Kewajiban Imbalan Kerja', '20801': 'Jangka Pendek', '2080101': 'Thr', '2080102': 'Tunj. Bantuan Pendidikan', '2080103': 'Kinerja', '208010301': 'Kinerja 1', '208010302': 'Kinerja 2', '20802': 'Jangka Panjang', '2080201': 'Jasa Pengabdian Pengurus', '2080202': 'Jasa Pengabdian Pegawai', '2080203': 'Imbalan Pesangon Phk', '20899': 'Kewajiban Imbalan Kerja Lainnya', '209': 'Pinjaman Subordinasi', '20901': 'Modal Pinjaman', '210': 'Kewajiban Antar Kantor', '21000': 'AKP Kantor Pusat', '211': 'Kewajiban Lain Lain', '21101': 'Taksiran Pajak Penghasilan', '21102': 'Pendapatan Yang Ditangguhkan', '21103': 'Lainnya', '2110301': 'Pakaian Dinas', '2110302': 'Rekreasi', '2110303': 'Undian', '2110304': 'Olah Raga', '2110305': 'Dana Kesejahteraan', '2110306': 'Jasa Produksi', '2110307': 'Akomodasi KAP', '2110308': 'Titipan Angs. BKK Pingsurat', '2110310': 'CSR', '2110311': 'Tantiem', '2110399': 'Kewajiban Lain-lain Lainnya',
            '3': 'Ekuitas', '301': 'Modal Disetor', '30101': 'Modal Dasar', '3010101': 'Pemerintah Provinsi Jawa Tengah 51 %', '3010102': 'Pemerintah Kabupaten 49 %', '301010201': 'Pemkab Semarang', '301010202': 'Pemkot Salatiga', '301010203': 'Pemkab Pati', '301010204': 'Pemkab Rembang', '301010205': 'Pemkab Kendal', '301010206': 'Pemkab Demak', '301010207': 'Pemkab Banjarnegara', '301010208': 'Pemkab Wonosobo', '301010209': 'Pemkab Purworejo', '301010210': 'Pemkab Magelang', '301010211': 'Pemkab Cilacap', '301010212': 'Pemkab Purbalingga', '301010213': 'Pemkab Banyumas', '301010214': 'Pemkab Temanggung', '301010215': 'Pemkab Boyolali', '301010216': 'Pemkab Karanganyar', '301010217': 'Pemkab Wonogiri', '301010218': 'Pemkab Klaten', '301010219': 'Pemkab Sukoharjo', '301010220': 'Pemkot Surakarta', '301010221': 'Pemkab Sragen', '301010222': 'Pemkot Pekalongan', '301010223': 'Pemkab Tegal', '301010224': 'Pemkab Batang', '301010225': 'Pemkab Pemalang', '301010226': 'Pemkab Pekalongan', '301010227': 'Pemkot Tegal', '301010228': 'Pemkab Brebes', '301010229': 'Pemkab Kebumen', '30102': 'Modal Yang Belum Disetor -/-', '3010201': 'Pemerintah Provinsi Jawa Tengah', '3010202': 'Pemerintah Kabupaten / Kota', '30103': 'Agio', '3010301': 'Agio Saham', '30104': 'Disagio -/-', '3010401': 'Disagio Saham -/-', '30105': 'Modal Sumbangan', '30106': 'Modal Pinjaman', '30107': 'Dana Setoran Modal - Ekuitas', '3010701': 'Pemerintah Provinsi Jawa Tengah', '3010702': 'Pemerintah Kabupaten', '301070201': 'Pemkab Semarang', '301070202': 'Pemkot Salatiga', '301070203': 'Pemkab Pati', '301070204': 'Pemkab Rembang', '301070205': 'Pemkab Kendal', '301070206': 'Pemkab Demak', '301070207': 'Pemkab Banjarnegara', '301070208': 'Pemkab Wonosobo', '301070209': 'Pemkab Purworejo', '301070210': 'Pemkab Magelang', '301070211': 'Pemkab Cilacap', '301070212': 'Pemkab Purbalingga', '301070213': 'Pemkab Banyumas', '301070214': 'Pemkab Temanggung', '301070215': 'Pemkab Boyolali', '301070216': 'Pemkab Karanganyar', '301070217': 'Pemkab Wonogiri', '301070218': 'Pemkab Klaten', '301070219': 'Pemkab Sukoharjo', '301070220': 'Pemkot Surakarta', '301070221': 'Pemkab Sragen', '301070222': 'Pemkot Pekalongan', '301070223': 'Pemkab Tegal', '301070224': 'Pemkab Batang', '301070225': 'Pemkab Pemalang', '301070226': 'Pemkab Pekalongan', '301070227': 'Pemkot Tegal', '301070228': 'Pemkab Brebes', '301070229': 'Pemkab Kebumen', '302': 'Laba / Rugi Yang Blm Direalisasi', '30201': 'Surplus Revaluasi Aset Tetap', '303': 'Saldo Laba', '30301': 'Cadangan Umum', '30302': 'Cadangan Tujuan', '30303': 'Laba Rugi', '3030301': 'Laba / Rugi Tahun Lalu', '303030101': 'Laba / Rugi Tahun Lalu', '3030302': 'Laba / Rugi Tahun Berjalan',
            '4': 'Pendapatan', '401': 'Pendapatan Operasional', '40101': '1. Pendapatan Bunga', '4010101': 'A. Bunga Kontraktual', '401010101': 'Surat Berharga', '40101010101': 'Sertifikat Bank Indonesia', '401010102': 'Bunga Penempatan Dari Bank Lain', '40101010201': 'I. Giro', '40101010202': 'Ii. Tabungan', '40101010203': 'Iii. Deposito', '40101010204': 'Iv. Sertifikat Deposito', '401010103': 'Kredit Yang Diberikan', '40101010301': 'Kepada Bank Lain', '40101010302': 'Kepada Pihak Ketiga Bukan Bank', '4010101030201': 'Pend. Bg Kredit Modal Kerja', '4010101030202': 'Pend. Bg Kredit Investasi', '4010101030203': 'Pend. Bg Kredit Konsumtif', '4010101030204': 'Pend. Bg Kredit Mikro Bkk', '4010101030205': 'Pend. Bg Kredit BKK Joglo', '4010101030206': 'Pend. Bg Kredit BKK Sinden', '4010101030207': 'Pend. Bg Kredit BKK Korporasi', '4010101030208': 'Pend. Bg Kredit BKK Bumdes', '4010101030209': 'Pend. Bg Kredit BKK Musiman', '4010101030210': 'Pend. Bg Kredit Kolektif Karyawan (K3)', '4010101030211': 'Pend. Bg Kredit KPP', '4010101030212': 'Pend. Bg Krd KUB', '4010101030213': 'Pend. Bg Krd KUB 6', '4010101030214': 'Pend. Bg Krd Koperasi', '4010101030215': 'Pend. Bg Krd Agro', '4010101030216': 'Pend. Bg Krd Bahari', '4010101030217': 'Pend. Bg Krd BKK Joglo Bahari', '4010101030218': 'Pend. Bg Krd BKK Joglo Khusus Pegawai', '40102': 'B. Provisi Dan Administrasi', '4010201': '1. Provisi Kredit', '401020101': 'A. Kepada Bank Lain', '401020102': 'B. Kepada Pihak Ketiga Bukan Bank', '40102010201': 'Pend. Provisi Kredit Modal Kerja', '40102010202': 'Pend. Provisi Kredit Investasi', '40102010203': 'Pend. Provisi Kredit Konsumtif', '40102010204': 'Pend. Provisi Kredit Mikro Bkk', '40102010205': 'Pend. Provisi Kredit BKK Joglo', '40102010206': 'Pend. Provisi Kredit BKK Sinden', '40102010207': 'Pend. Provisi Kredit BKK Korporasi', '40102010208': 'Pend. Provisi Kredit BKK Bumdes', '40102010209': 'Pend. Provisi Kredit BKK Musiman', '40102010210': 'Pend. Provisi Kredit Kolektif Karyawan (K3)', '40102010211': 'Pend. Provisi Kredit KPP', '40102010212': 'Pend. Provisi Kredit KUB', '40102010213': 'Pend. Provisi Kredit KUB 6', '40102010214': 'Pend. Provisi Kredit Koperasi', '40102010215': 'Pend. Provisi Kredit Agro', '40102010216': 'Pend. Provisi Kredit Bahari', '40102010217': 'Pend. Provisi Kredit BKK Joglo Mitra', '40102010218': 'Pend. Provisi Kredit BKK Joglo Khusus Pegawai', '4010202': '2. Administrasi Kredit', '401020201': 'A. Kepada Bank Lain', '401020202': 'B. Kepada Pihak Ketiga Bukan Bank', '40102020201': 'Pend. Adm Kredit Modal Kerja', '40102020202': 'Pend. Adm Kredit Investasi', '40102020203': 'Pend. Adm Kredit Konsumtif', '40102020204': 'Pend. Adm Kredit Mikro Bkk', '40102020205': 'Pend. Adm Kredit BKK Joglo', '40102020206': 'Pend. Adm Kredit BKK Sinden', '40102020207': 'Pend. Adm Kredit BKK Korporasi', '40102020208': 'Pend. Adm Kredit BKK Bumdes', '40102020209': 'Pend. Adm Kredit BKK Musiman', '40102020210': 'Pend. Adm Kredit Kolektif Karyawan (K3)', '40102020211': 'Pend. Adm Kredit KKP', '40102020212': 'Pend. Adm Kredit KUB', '40102020213': 'Pend. Adm Kredit KUB (Promo)', '40102020214': 'Pend. Adm Kredit Koperasi', '40102020215': 'Pend. Adm Kredit Agro', '40102020216': 'Pend. Adm Kredit Bahari', '40102020217': 'Pend. Adm Kredit Joglo Mitra', '40102020218': 'Pend. Adm Kredit BKK Joglo Khusus Pegawai', '40103': 'C. Biaya Transaksi', '4010301': 'Surat Berharga', '4010302': 'Kredit Yang Diberikan', '401030201': 'Kepada Bank Lain', '401030202': 'Kepada Pihak Ketiga Bukan Bank', '40104': 'D. Pendapatan Bunga EIR', '402': '2. Lainnya', '40201': 'A. Pendapatan Jasa Transaksi', '4020101': '1. Pend. Fee PPOB (EDC) PLN,Jastel, Dll', '4020102': '2. Pend. Fee Biller Mobile Banking', '4020103': '3. Pend. Fee PBB', '4020104': '4. Pend. Fee PDAM', '4020105': '6. Pend. Fee Pajak Kendaraan', '4020106': '7. Pend. Fee Lainnya', '40202': 'B. Keuntungan Penjualan Valas', '40203': 'C. Keuntungan Penjualan Surat Berharga', '40204': 'D. Pendapatan Dari Kredit Yang Dihapus Buku', '4020401': '1. Pend. Angsuran PH - Pokok', '4020402': '2. Pend. Angsuran PH - Bunga', '4020403': '3. Pend. Denda Angsuran PH', '40205': 'E. Pendapatan Dari Pemulihan CKPN', '4020501': '1. Pend. Pemulihan CKPN ABA', '4020502': '2. Pend. Pemulihan CKPN Kredit', '40206': 'F. Lainnya', '4020601': '1. Pendapatan Administrasi', '402060101': 'A. Pend. Adm. Pengelolaan Rekening', '402060102': 'B. Pend. Adm. Penutupan Rekening', '402060103': 'C. Pend. Adm. Ganti Buku', '402060104': 'D. Pend. Adm. Tabungan Pasif', '402060105': 'E. Pend. Pinalty Dari Deposito', '402060106': 'F. Pend. Pinalty Kredit Pelunasan Belum Jatuh Tem', '402060107': 'G. Pend. Denda Dari Kredit', '402060108': 'H. Pend. Denda Dari Kredit Yg Melebihi Jangka Wak', '402060109': 'I. Pend. Amortisasi Restrukturisasi', '4020602': '2. Pendapatan Koreksi Penyusutan Inventaris', '4020603': '3. Pendapatan Fee', '402060301': 'A. Pend. Fee Asuransi', '402060302': 'B. Pend. Fee Notaris', '402060303': 'C. Pend. Fee Lainnya', '4020604': '4. Pendapatan Pembulatan Kas', '4020605': '5. Pendapatan Lainnya', '403': 'Pendapatan Non Operasional', '40301': '1. Keuntungan Penjualan', '4030101': 'A. Aset Tetap & Inventaris', '403010101': '1. Tanah', '403010102': '2. Bangunan', '403010103': '3. Inventaris', '4030102': 'B. AYDA', '403010201': '1. Tanah', '403010202': '2. Bangunan', '403010203': '3. Kendaraan', '40302': '2. Pemulihan Penurunan Nilai', '4030201': 'A. Aset Tetap & Inventaris', '403020101': '1. Tanah', '403020102': '2. Bangunan', '403020103': '3. Inventaris', '4030202': 'B. AYDA', '403020201': '1. Tanah', '403020202': '2. Bangunan', '403020203': '3. Kendaraan', '40303': '3. Pendapatan Ganti Rugi Asuransi', '40304': '4. Pend. Bunga Antar Kantor', '40399': '5. Lainnya', '4039999': 'Lainnya',
            '5': 'Biaya', '501': 'Beban Operasional', '50101': '1. Beban Bunga', '5010101': 'A. Beban Bunga Kontraktual', '501010101': 'I. Tabungan', '50101010101': 'Beban Bg Tabungan Wajib', '50101010102': 'Beban Bg Tabungan Tamades', '50101010103': 'Beban Bg Tabungan Tamades 1', '50101010104': 'Beban Bg Tabungan Tamades 2', '50101010105': 'Beban Bg Tabungan Tamades 3', '50101010106': 'Beban Bg Tabungan Tamades 4', '50101010107': 'Beban Bg Tabungan Tamades 5', '50101010108': 'Beban Bg Tabungan Pelajar', '50101010109': 'Beban Bg TAMADES', '50101010110': 'Beban Bg TAWA', '50101010111': 'Beban Bg TAWA PLUS', '50101010112': 'Beban Bg Tabungan Kredit BKK', '50101010113': 'Beban Bg Tabungan Mitra BKK', '50101010114': 'Beban Bg Tabungan BKK Prioritas', '501010102': 'II. Deposito Berjangka', '50101010201': 'Beban Bg Deposito 1 Bulan', '50101010202': 'Beban Bg Deposito 3 Bulan', '50101010203': 'Beban Bg Deposito 6 Bulan', '50101010204': 'Beban Bg Deposito 9 Bulan', '50101010205': 'Beban Bg Deposito 12 Bulan', '501010103': 'III. Simpanan Dari Bank Lain', '50101010301': 'Beban Bg Tabungan ABP', '50101010302': 'Beban Bg Deposito ABP', '501010104': 'IV. Pinjaman Yang Diterima', '50101010401': 'Dari Bank Indonesia', '50101010402': 'Dari Bank Lain', '501010403': 'Dari Pihak Ketiga Bukan Bank', '501010105': 'V. Pinjaman Subordinasi', '50101010501': 'A. Dari Bank Lain', '50101010502': 'B. Dari Pihak Ketiga Bukan Bank', '501010106': 'VI. Premi Penjaminan Simpanan (LPS)', '5010102': 'B. Biaya Transaksi', '501010201': 'Kepada Bank Lain', '501010202': 'Kepada Pihak Ketiga Bukan Bank', '50101020201': 'A. Cash Back', '50101020202': 'B. Asuransi', '50101020203': 'C. Lainnya', '5010103': 'C. Koreksi Atas Pendapatan Bunga', '501010301': '1. Tabungan', '501010302': '2. Deposito', '501010303': '3. Kredit Yang Diberikan', '501010399': '4. Lainnya', '50102': '2. Beban Kerugian Restrukturisasi Kredit', '50103': '3. Beban CKPN', '5010301': 'A. Surat Berharga', '5010302': 'B. Penempatan Pada Bank Lain', '5010303': 'C. Kredit Yang Diberikan', '501030301': 'i. Kepada Bank Lain', '501030302': 'ii. Kepada Pihak Ketiga Bukan Bank', '50104': '4. Beban Pemasaran', '5010401': 'A. Beban Inklusi dan Literasi Keuangan', '5010402': 'B. Beban Pemberian Hadiah', '5010403': 'C. Beban Iklan/Promosi', '5010404': 'D. Beban Edukasi & Sosialisasi Produk', '5010499': 'E. Sponsorship', '50105': '5. Beban Penelitian Dan Pengembangan', '5010501': 'A. Tekhnologi Informasi', '5010502': 'B. Pengembangan Produk Baru', '5010503': 'C. Pembukaan Kantor Kas / Cabang', '5010599': 'D. Lainnya', '50106': '6. Beban Administrasi Dan Umum', '5010601': 'A. Beban Tenaga Kerja', '501060101': 'I. Gaji Dan Upah', '50106010101': 'A. Gaji Direksi', '50106010102': 'B. Gaji Pokok', '50106010103': 'C. Tunjangan Suami / Istri', '50106010104': 'D. Tunjangan Anak', '50106010105': 'E. Tunjangan Pangan', '50106010106': 'F. Tunjangan Jabatan', '50106010107': 'G. Tunjangan Operasional', '50106010108': 'H. Tunjangan Kinerja', '50106010110': 'J. Tunjangan Fungsional', '50106010111': 'K. Tunjangan Masa Kerja', '50106010112': 'L. Honor Tenaga Kontrak', '50106010113': 'M. Honor Tenaga Outsourcing', '501060102': 'II. Honorarium', '50106010201': 'A. Honor Dewan Komisaris', '50106010202': 'B. Honor Kontrak', '501060103': 'III. Lainnya', '50106010301': 'A. Uang Makan', '50106010302': 'B. Uang Lembur', '50106010303': 'C. Uang Transport', '50106010304': 'D. Jasa Pengabdian Pengurus', '50106010305': 'E. Jasa Pengabdian Pegawai', '50106010306': 'F. Premi Jht', '50106010307': 'G. Dplk', '50106010308': 'H. Tunj. Bantuan Pendidikan', '50106010309': 'I. THR', '50106010310': 'J. Tunjangan Kinerja', '50106010311': 'K. Tunjangan PPh 21', '50106010312': 'L. Uang Pesangon', '50106010313': 'M. Uang Penghargaan Masa Kerja', '50106010314': 'N. Tenaga Harian Lepas', '5010602': 'B. Beban Pendidikan Dan Pelatihan', '501060201': '1. In House Training', '501060202': '2. Eksternal Training', '501060203': '3. Study Banding', '501060299': '4. Lainnya', '5010603': 'C. Beban Sewa', '501060301': '1. Sewa Tanah Dan Gedung', '50106030101': 'A. Kantor Pusat', '50106030102': 'B. Kantor Cabang', '50106030103': 'C. Kantor Kas', '501060302': '2. Lainnya', '50106030201': 'Sewa Aplikasi Core Banking', '50106030202': 'Sewa Koneksi Jaringan', '50106030203': 'Sewa Kendaraan', '50106030204': 'Sewa Peralatan Kantor', '50106030205': 'Sewa Pengganti Rumah Dinas', '50106030206': 'Sewa Layanan Teknologi Informasi', '50106030299': 'Sewa Lainnya', '5010604': 'D. Beban Penyusutan / Penghapusan Atas Ati', '501060401': '1. Penyusutan Gedung', '501060402': '2. Penyusutan Inventaris', '50106040201': 'A. Kendaraan', '50106040202': 'B. Inventaris', '5010605': 'E. Beban Amortisasi Aset Tidak Berwujud', '501060501': '1. Core Banking', '501060502': '2. Instalasi Listrik', '501060599': '3. Lainnya', '5010606': 'F. Beban Premi Asuransi', '501060601': 'Asuransi Aset Tetap Dan Inventaris', '50106060101': 'A. Asuransi Gedung', '50106060102': 'B. Asuransi Kendaraan', '5010606010201': 'Asuransi Kend. Roda 4', '5010606010202': 'Asuransi Kend. Roda 2', '50106060103': 'C. Asuransi Inventaris Lainya', '501060602': 'Asuransi Tenaga Kerja', '50106060201': 'A. Bpjs Ketenagakerjaan', '50106060202': 'B. Bpjs Kesehatan', '501060603': 'Asuransi Uang Kas', '50106060301': 'A. Cash In Save', '50106060302': 'B. Cash In Transit', '501060699': 'Lainnya', '50106069903': 'Asuransi Mesin Fotocopy', '50106069904': 'Asuransi Purna Jabatan Pengurus', '50106069999': 'Lainnya', '5010607': 'G. Beban Pemeliharaan Dan Perbaikan', '501060701': '1. Pemeliharaan Ti', '501060702': '2. Pemeliharaan Gedung Kantor', '501060703': '3. Pemeliharaan Perabot Kantor', '501060704': '4. Pemeliharaan Kendaraan', '50106070401': 'By Pemeliharaan Kend. Roda 4', '50106070402': 'By Pemeliharaan Kend. Roda 2', '501060799': '5. Lainnya', '5010608': 'H. Beban Barang Dan Jasa', '501060801': '1. Listrik', '501060802': '2. Air', '501060803': '3. Telepon', '501060804': '4. Materai', '501060805': '5. Alat Tulis Kantor', '501060806': '6. Percetakan', '501060807': '7. Koran & Majalah', '501060808': '8. Gas', '501060809': '9. Akomodasi Tamu', '501060810': '10. Perjalanan Dinas', '50106081001': 'Perj Dinas Komisaris', '50106081002': 'Perj Dinas Direksi', '50106081003': 'Perj Dinas Pegawai', '50106081004': 'Biaya Akomodasi dan Penginapan', '501060811': '11. Jasa Pihak Lain', '50106081101': 'A. Kantor Akuntan Publik (KAP)', '50106081102': 'B. Lawyer', '50106081103': 'C. Konsultan', '50106081104': 'D. Notaris', '50106081105': 'E. Keamanan', '50106081106': 'F. Pungutan OJK', '50106081107': 'G. Security (Outsourcing)', '50106081199': 'H. Lainnya', '501060812': '12. Pakaian Dinas', '501060813': '13. Bahan Bakar Minyak', '501060814': '14. Rapat Rapat', '501060815': '15. Rumah Tangga Kantor', '501060816': '16. Voucher Handphone', '501060817': '17. Catering / Makan', '501060818': '18. Perlengkapan IT', '501060819': '19. Perabot Kantor', '501060820': '20. Ekspedisi/Kurir', '501060899': '21. Lainnya', '5010609': 'I. Beban Pajak', '501060901': '1. Beban Pajak Kendaraan', '501060902': '2. Beban Pajak Bumi Dan Bangunan', '501060903': '3. Beban Ppn Barang (Pasal 22)', '501060904': '4. Beban Ppn Jasa (Pasal 23)', '501060999': '5. Beban Pajak Lainnya', '50107': '7. Beban Lainnya', '5010701': 'Kerugian Penjualan Valas', '5010702': 'Kerugian Penjualan Surat Berharga', '5010703': 'Kerugian Piutang Asuransi', '5010799': 'Lainnya', '501079901': '1. Representatif', '501079902': '2. Biaya Penagihan Kredit', '501079903': '3. Konsolidasi', '501079904': '4. Bingkisan/ Cinderamata', '501079905': '5. Fee Juru Bayar', '501079906': '6. By Adm PPBL', '501079907': '7. Pajak Atas Bunga PPBL', '501079908': '8. By. Pengadilan dan Gugatan Sederhana', '501079909': '9. Iuran OJK', '502': 'Beban Non Operasional', '50201': 'Kerugian Penjualan / Kehilangan', '5020101': 'Aset Tetap Dan Inventaris', '502010101': '1. Kendaraan', '502010102': '2. Inventaris', '5020102': 'Ayda', '50202': 'Kerugian Penurunan Nilai', '5020201': 'Aset Tetap Dan Inventaris', '502020101': '1. Kendaraan', '502020102': '2. Inventaris', '5020202': 'Ayda', '50203': 'Beban Bunga Antar Kantor', '50204': 'Selisih Kurs', '50299': 'Lainnya', '5029901': 'A. Rekreasi', '5029902': 'B. Olah Raga', '5029903': 'C. Iuran Asosiasi', '5029904': 'E. Sumbangan', '5029905': 'F. Denda', '5029907': 'H. Bingkisan-Bingkisan', '5029908': 'I. Lainnya'
      };
  }

  window.addEventListener('DOMContentLoaded', async () => {
      const user = (window.getUser && window.getUser()) || null;
      const uKode = (user?.kode ? String(user.kode).padStart(3,'0') : '000');
      window.currentUser.kode = uKode;

      document.getElementById('badgeUnit').innerText = (uKode === '000') ? 'KONSOLIDASI PUSAT' : `CABANG ${uKode}`;

      await populateKantorOptionsFP(uKode);
      document.getElementById('harian_date').value = getYesterdayDate();

      fetchRekap();
  });

  async function populateKantorOptionsFP(userKode) {
      const optKantor = document.getElementById('opt_kantor_rec');
      if (userKode && userKode !== '000') {
          try {
              const res = await fetch(API_KODE, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({type:'kode_kantor'}) });
              const j = await res.json();
              const myKantor = (j.data || []).find(k => String(k.kode_kantor).padStart(3,'0') === userKode);
              const nama = myKantor ? myKantor.nama_kantor : `CABANG ${userKode}`;
              optKantor.innerHTML = `<option value="${userKode}">${userKode} - ${nama}</option>`;
          } catch(e) {
              optKantor.innerHTML = `<option value="${userKode}">${userKode} - CABANG ${userKode}</option>`;
          }
          optKantor.value = userKode;
          optKantor.disabled = true;
          return; 
      }
      
      try {
          const res = await fetch(API_KODE, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({type:'kode_kantor'}) });
          const json = await res.json();
          let list = Array.isArray(json.data) ? json.data : [];
          
          let html = `<option value="konsolidasi">KONSOLIDASI (SEMUA)</option><option value="000">000 - PUSAT</option>`;
          list.filter(x => x.kode_kantor && x.kode_kantor !== '000')
              .sort((a,b) => String(a.kode_kantor).localeCompare(String(b.kode_kantor)))
              .forEach(it => {
                  const code = String(it.kode_kantor).padStart(3,'0');
                  const nama = it.nama_kantor || `CABANG ${code}`;
                  html += `<option value="${code}">${code} - ${nama}</option>`;
              });
          optKantor.innerHTML = html;
          optKantor.disabled = false;
      } catch(e) {
          optKantor.innerHTML = `<option value="konsolidasi">KONSOLIDASI (SEMUA)</option><option value="000">000 - PUSAT</option>`;
          optKantor.disabled = false;
      }
  }

  async function fetchRekap() {
      const loader = document.getElementById('loadingFP');
      const tbody = document.getElementById('lapBody');
      loader.classList.remove('hidden');
      tbody.innerHTML = '';

      const typeReport = document.getElementById('type_report').value;
      const payload = {
          type: typeReport,
          kode_kantor: document.getElementById('opt_kantor_rec').value,
          harian_date: document.getElementById('harian_date').value
      };

      try {
          const res = await fetch(API_LAP, {
              method: 'POST',
              headers: {'Content-Type':'application/json'},
              body: JSON.stringify(payload)
          });
          const json = await res.json();
          const dbData = json.data || [];
          
          const fullCoa = getCoaClientSide();
          let mergedData = [];
          
          const prefixes = typeReport.includes('neraca') ? ['1', '2', '3'] : ['4', '5'];
          
          let tempMap = {};
          Object.keys(fullCoa).forEach(kode => {
              if (prefixes.some(p => kode.startsWith(p))) {
                  const dbRecord = dbData.find(d => String(d.kode_perk) === kode);
                  tempMap[kode] = dbRecord ? Number(dbRecord.total_saldo) : 0;
              }
          });

          Object.keys(tempMap).forEach(kode => {
              const saldo = tempMap[kode];
              const hasActiveChild = Object.keys(tempMap).some(k => k.startsWith(kode) && k.length > kode.length && Math.abs(tempMap[k]) > 0);

              if (kode.length <= 3 || Math.abs(saldo) > 0 || hasActiveChild) {
                  mergedData.push({
                      kode_perk: kode,
                      nama_perkiraan: fullCoa[kode],
                      total_saldo: saldo
                  });
              }
          });

          rawDataResult = mergedData; 
          renderTable(mergedData);
          renderSummary(mergedData, typeReport);
      } catch (e) {
          console.error(e);
          tbody.innerHTML = `<tr><td colspan="3" class="text-center p-10 text-red-500 font-bold">Gagal memuat data laporan!</td></tr>`;
      } finally {
          loader.classList.add('hidden');
      }
  }

  function renderTable(data) {
    const tbody = document.getElementById('lapBody');
    tbody.innerHTML = data.map(d => {
      const kode = d.kode_perk;
      const len = kode.length;
      let cls = 'row-detail';
      let indent = `&nbsp;`.repeat((len - 1) * 3);
      
      const hasChild = data.some(child => child.kode_perk.startsWith(kode) && child.kode_perk.length > len);
      
      if (len === 1) cls = 'row-level-1';
      else if (len <= 3) cls = 'row-level-2';
      else cls = 'row-level-3';

      let hiddenCls = (len > 3) ? 'hidden-row' : '';
      
      let toggleIcon = hasChild 
          ? `<span class="caret">▶</span>` 
          : `<span style="display:inline-block; width:18px;"></span>`;

      return `
        <tr class="${cls} ${hiddenCls} transition-colors hover:bg-slate-50" data-kode="${kode}" onclick="toggleRow('${kode}')">
          <td class="p-3 font-mono text-[11.5px] border-b border-slate-100 text-slate-500">${kode}</td>
          <td class="p-3 text-[12px] border-b border-slate-100 text-slate-700 font-medium">${indent}${toggleIcon}${d.nama_perkiraan}</td>
          <td class="p-3 text-[13px] border-b border-slate-100 text-right font-bold ${d.total_saldo < 0 ? 'text-red-600' : 'text-slate-800'}">
            ${fmtNom(d.total_saldo)}
          </td>
        </tr>
      `;
    }).join('');
  }

  window.toggleRow = function(parentKode) {
    const allRows = document.querySelectorAll('#lapBody tr');
    const clickedRow = document.querySelector(`tr[data-kode="${parentKode}"]`);
    const clickedIcon = clickedRow.querySelector('.caret');
    
    if(!clickedIcon) return; 

    const isOpening = !clickedIcon.classList.contains('rotate');
    clickedIcon.classList.toggle('rotate');

    allRows.forEach(row => {
      const rowKode = row.getAttribute('data-kode');
      if (rowKode.startsWith(parentKode) && rowKode !== parentKode) {
         if (isOpening) {
             if (rowKode.length <= parentKode.length + 2) {
                 row.classList.remove('hidden-row');
             }
         } else {
             row.classList.add('hidden-row');
             const icon = row.querySelector('.caret');
             if(icon) icon.classList.remove('rotate');
         }
      }
    });
  }

  function renderSummary(data, type) {
    const container = document.getElementById('rekapContainer');
    const getVal = (p) => Math.round(Number(data.find(x => x.kode_perk === p)?.total_saldo || 0));
    
    if (type.includes('neraca')) {
      container.innerHTML = `
        <div class="rekap-card"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Aktiva (1)</p><p class="text-2xl mt-1 val-plus">Rp ${fmtNom(getVal('1'))}</p></div>
        <div class="rekap-card"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Kewajiban (2)</p><p class="text-2xl mt-1 text-blue-600 font-bold font-mono">Rp ${fmtNom(getVal('2'))}</p></div>
        <div class="rekap-card"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Ekuitas (3)</p><p class="text-2xl mt-1 text-orange-500 font-bold font-mono">Rp ${fmtNom(getVal('3'))}</p></div>
      `;
    } else {
      const pdt = getVal('4'); const bya = getVal('5');
      container.innerHTML = `
        <div class="rekap-card"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Pendapatan (4)</p><p class="text-2xl mt-1 val-plus">Rp ${fmtNom(pdt)}</p></div>
        <div class="rekap-card"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Biaya (5)</p><p class="text-2xl mt-1 val-minus">Rp ${fmtNom(bya)}</p></div>
        <div class="rekap-card"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Laba Rugi Berjalan</p><p class="text-2xl mt-1 ${(pdt-bya) >=0 ? 'val-plus' : 'val-minus'}">Rp ${fmtNom(pdt-bya)}</p></div>
      `;
    }
  }

  function exportToExcel() {
    if(rawDataResult.length === 0) return alert("Data kosong!");
    let table = `<table border="1"><thead><tr style="background:#f1f5f9"><th>Kode</th><th>Uraian</th><th>Saldo</th></tr></thead><tbody>`;
    rawDataResult.forEach(d => {
      let roundedSaldo = Math.round(Number(d.total_saldo || 0));
      table += `<tr><td style="mso-number-format:'\\@'">${d.kode_perk}</td><td>${d.nama_perkiraan}</td><td>${roundedSaldo}</td></tr>`;
    });
    table += `</tbody></table>`;
    const blob = new Blob([table], { type: 'application/vnd.ms-excel' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    
    const ddl = document.getElementById('opt_kantor_rec');
    const namaKantor = ddl.options[ddl.selectedIndex].text.replace(/ /g, '_');
    
    a.download = `Laporan_Keuangan_${namaKantor}_${document.getElementById('harian_date').value}.xls`;
    a.click();
  }

  document.getElementById('filterForm').onsubmit = e => { e.preventDefault(); fetchRekap(); };
</script>