<!-- Wrapper Utama: Full Screen -->
<div class="flex h-screen bg-slate-50 font-sans overflow-hidden relative">

  <!-- ================= OVERLAY MOBILE SIDEBAR ================= -->
  <!-- z-[90] di atas tabel, di bawah sidebar -->
  <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/50 z-[90] hidden md:hidden"></div>

  <!-- ================= 1. SIDEBAR (Slide Mobile & Hover Desktop) ================= -->
  <!-- z-[100] Jalan Tengah: Menang telak dari Tabel, tapi tetap di bawah Modal aplikasi (z-1050) -->
  <aside id="sidebar" class="absolute md:relative z-[100] h-full flex flex-col bg-white border-r border-slate-200 shrink-0 transition-all duration-300 ease-in-out -translate-x-full md:translate-x-0 w-64 md:w-[4.5rem] md:hover:w-64 group">
    
    <!-- Bagian Logo (Di Sidebar) -->
    <div class="h-16 flex items-center px-4 border-b border-slate-200 shrink-0 whitespace-nowrap">
      <img src="./img/logodpk.png" class="h-8 w-8 object-contain shrink-0" alt="Logo">
      <span class="text-slate-800 text-xl font-bold tracking-tight opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300 ml-3">Monbis</span>
    </div>

    <!-- Navigasi Menu -->
    <nav class="flex-1 overflow-y-auto overflow-x-hidden py-4 px-2 space-y-1 custom-scrollbar">
      
      <!-- Menu Single -->
      <a href="dashboard" class="flex items-center px-3 py-2.5 text-blue-600 bg-blue-50 rounded-lg font-medium transition-colors whitespace-nowrap">
        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 001 1m-6 0h6"></path></svg>
        <span class="ml-3 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300">Dashboard</span>
      </a>

      <!-- Parent Pemasaran -->
      <div class="accordion-group">
        <button class="accordion-btn w-full flex items-center justify-between px-3 py-2.5 text-slate-700 rounded-lg hover:bg-slate-100 font-medium transition-colors whitespace-nowrap focus:outline-none">
          <div class="flex items-center shrink-0">
            <svg class="w-6 h-6 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            <span class="ml-3 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300">Pemasaran</span>
          </div>
          <svg class="caret w-4 h-4 shrink-0 transition-transform text-slate-400 opacity-100 md:opacity-0 md:group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </button>
        <div class="accordion-content hidden pl-[3.25rem] pr-2 py-1 space-y-1">
          <a href="realisasi_kredit" class="block px-2 py-2 text-[11px] truncate text-slate-600 rounded-md hover:text-blue-600 hover:bg-blue-50">Realisasi Kredit</a>
          <a href="realisasi_promo" class="block px-2 py-2 text-[11px] truncate text-slate-600 rounded-md hover:text-blue-600 hover:bg-blue-50">Realisasi Kredit & Promo</a>
          <a href="realisasi_ao" class="block px-2 py-2 text-[11px] truncate text-slate-600 rounded-md hover:text-blue-600 hover:bg-blue-50">Realisasi Kredit AO</a>
          <a href="otp" class="block px-2 py-2 text-[11px] truncate text-slate-600 rounded-md hover:text-blue-600 hover:bg-blue-50">Ontime Payment</a>
          <a href="rekap_rr" class="block px-2 py-2 text-[11px] truncate text-slate-600 rounded-md hover:text-blue-600 hover:bg-blue-50">Rekap Repayment Rate</a>
          <a href="migrasi_bucket_sc" class="block px-2 py-2 text-[11px] truncate text-slate-600 rounded-md hover:text-blue-600 hover:bg-blue-50">Migrasi Bucket SC</a>
          <a href="mob" class="block px-2 py-2 text-[11px] truncate text-slate-600 rounded-md hover:text-blue-600 hover:bg-blue-50">MOB 6 Bulan</a>
          <a href="pipelane_ao_jt" class="block px-2 py-2 text-[11px] truncate text-slate-600 rounded-md hover:text-blue-600 hover:bg-blue-50">Pipelane AO Kredit</a>
          <a href="jatuh_tempo" class="block px-2 py-2 text-[11px] truncate text-slate-600 rounded-md hover:text-blue-600 hover:bg-blue-50">Jatuh Tempo & Refinancing</a>
        </div>
      </div>

      <!-- Parent NPL -->
      <div class="accordion-group">
        <button class="accordion-btn w-full flex items-center justify-between px-3 py-2.5 text-slate-700 rounded-lg hover:bg-slate-100 font-medium transition-colors whitespace-nowrap focus:outline-none">
          <div class="flex items-center shrink-0">
            <svg class="w-6 h-6 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            <span class="ml-3 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300">NPL</span>
          </div>
          <svg class="caret w-4 h-4 shrink-0 transition-transform text-slate-400 opacity-100 md:opacity-0 md:group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </button>
        <div class="accordion-content hidden pl-[3.25rem] pr-2 py-1 space-y-1">
          <a href="npl" class="block px-2 py-2 text-[11px] truncate text-slate-600 rounded-md hover:text-blue-600 hover:bg-blue-50">NPL</a>
          <a href="perbandingan_npl" class="block px-2 py-2 text-[11px] truncate text-slate-600 rounded-md hover:text-blue-600 hover:bg-blue-50">Perbandingan NPL</a>
          <a href="recovery_npl" class="block px-2 py-2 text-[11px] truncate text-slate-600 rounded-md hover:text-blue-600 hover:bg-blue-50">Recovery NPL</a>
          <a href="flow_par" class="block px-2 py-2 text-[11px] truncate text-slate-600 rounded-md hover:text-blue-600 hover:bg-blue-50">Flow Par</a>
          <a href="npl_25_besar" class="block px-2 py-2 text-[11px] truncate text-slate-600 rounded-md hover:text-blue-600 hover:bg-blue-50">25 NPL Besar</a>
          <a href="potensi_npl" class="block px-2 py-2 text-[11px] truncate text-slate-600 rounded-md hover:text-blue-600 hover:bg-blue-50">Potensi NPL</a>
        </div>
      </div>

      <!-- Parent PH -->
      <div class="accordion-group">
        <button class="accordion-btn w-full flex items-center justify-between px-3 py-2.5 text-slate-700 rounded-lg hover:bg-slate-100 font-medium transition-colors whitespace-nowrap focus:outline-none">
          <div class="flex items-center shrink-0">
            <svg class="w-6 h-6 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="ml-3 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300">PH</span>
          </div>
          <svg class="caret w-4 h-4 shrink-0 transition-transform text-slate-400 opacity-100 md:opacity-0 md:group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </button>
        <div class="accordion-content hidden pl-[3.25rem] pr-2 py-1 space-y-1">
          <a href="recovery_ph" class="block px-2 py-2 text-[11px] truncate text-slate-600 rounded-md hover:text-blue-600 hover:bg-blue-50">Recovery PH</a>
          <a href="lgd" class="block px-2 py-2 text-[11px] truncate text-slate-600 rounded-md hover:text-blue-600 hover:bg-blue-50">Rekap Recovery (LGD)</a>
        </div>
      </div>

      <!-- Parent Collection -->
      <div class="accordion-group">
        <button class="accordion-btn w-full flex items-center justify-between px-3 py-2.5 text-slate-700 rounded-lg hover:bg-slate-100 font-medium transition-colors whitespace-nowrap focus:outline-none">
          <div class="flex items-center shrink-0">
            <svg class="w-6 h-6 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <span class="ml-3 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300">Collection</span>
          </div>
          <svg class="caret w-4 h-4 shrink-0 transition-transform text-slate-400 opacity-100 md:opacity-0 md:group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </button>
        <div class="accordion-content hidden pl-[3.25rem] pr-2 py-1 space-y-1">
          <a href="migrasi_kolek" class="block px-2 py-2 text-[11px] truncate text-slate-600 rounded-md hover:text-blue-600 hover:bg-blue-50">Migrasi Kolek</a>
          <a href="actual_kredit" class="block px-2 py-2 text-[11px] truncate text-slate-600 rounded-md hover:text-blue-600 hover:bg-blue-50">Bucket DPD & Kolek</a>
          <a href="migrasi_bucket" class="block px-2 py-2 text-[11px] truncate text-slate-600 rounded-md hover:text-blue-600 hover:bg-blue-50">Migrasi Bucket</a>
          <a href="search_debitur" class="block px-2 py-2 text-[11px] truncate text-slate-600 rounded-md hover:text-blue-600 hover:bg-blue-50">Search Debitur Kredit</a>
          <a href="otp_bucket_fe" class="block px-2 py-2 text-[11px] truncate text-slate-600 rounded-md hover:text-blue-600 hover:bg-blue-50">Otp Bucket FE (31-90)</a>
        </div>
      </div>

      <!-- Parent Laporan (Khusus Dev) -->
      <div id="menuMonevDev" class="accordion-group" style="display: none;">
        <button class="accordion-btn w-full flex items-center justify-between px-3 py-2.5 text-slate-700 rounded-lg hover:bg-slate-100 font-medium transition-colors whitespace-nowrap focus:outline-none">
          <div class="flex items-center shrink-0">
            <svg class="w-6 h-6 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <span class="ml-3 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300">Laporan</span>
          </div>
          <svg class="caret w-4 h-4 shrink-0 transition-transform text-slate-400 opacity-100 md:opacity-0 md:group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </button>
        <div class="accordion-content hidden pl-[3.25rem] pr-2 py-1 space-y-1">
          <a href="lapkeu_kantor" class="block px-2 py-2 text-[11px] truncate text-slate-600 rounded-md hover:text-blue-600 hover:bg-blue-50">Laporan Keuangan</a>
          <a href="aging_kredit" class="block px-2 py-2 text-[11px] truncate text-slate-600 rounded-md hover:text-blue-600 hover:bg-blue-50">Rekap Aging Kredit</a>
          <a href="layanan_digital" class="block px-2 py-2 text-[11px] truncate text-slate-600 rounded-md hover:text-blue-600 hover:bg-blue-50">Layanan Digital</a>
          <a href="prospek" class="block px-2 py-2 text-[11px] truncate text-slate-600 rounded-md hover:text-blue-600 hover:bg-blue-50">Pipelane Prospek</a>
        </div>
      </div>
    </nav>
  </aside>

  <!-- ================= KONTEN KANAN ================= -->
  <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">
    
    <!-- HEADER ATAS KONTEN -->
    <!-- z-[80] biar Navbar Header aman nutupin konten, tapi di bawah Overlay & Sidebar -->
    <header class="h-16 bg-white border-b border-slate-200 flex items-center px-4 sm:px-6 z-[80] shadow-sm shrink-0">
      
      <!-- Tombol Hamburger Mobile -->
      <button id="btnToggleSidebar" class="md:hidden text-slate-500 hover:text-slate-800 focus:outline-none mr-3">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path></svg>
      </button>

      <!-- Logo Monbis Mobile -->
      <div class="flex items-center md:hidden">
        <img src="./img/logodpk.png" class="h-8 w-8 object-contain mr-2" alt="Logo">
        <span class="text-slate-800 text-lg font-bold tracking-tight">Monbis</span>
      </div>

      <div class="flex-1"></div>
      
      <!-- Area Lonceng & Profile -->
      <div class="flex items-center gap-4 sm:gap-6 ml-auto">
        <button class="relative text-slate-500 hover:text-slate-800 transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
          <span class="absolute top-0 right-0 block h-2.5 w-2.5 rounded-full ring-2 ring-white bg-red-500"></span>
        </button>
        
        <div class="h-8 border-l border-slate-200"></div>
        
        <div class="relative flex items-center gap-3">
          <div class="hidden sm:flex flex-col leading-tight text-right select-none">
            <span id="navUserName" class="text-slate-800 text-sm font-semibold truncate max-w-[120px]">—</span>
            <span id="navBranch" class="text-slate-500 text-[11px] truncate max-w-[120px]">—</span>
          </div>
          
          <button id="btnProfileMenu" class="w-9 h-9 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center ring-2 ring-white border border-slate-200 shadow-sm hover:ring-blue-200 transition-all focus:outline-none">
             <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
          </button>

          <!-- Dropdown Profile di set ke z-[90] biar gak kalah sama header tabel -->
          <div id="dropdownProfileMenu" class="hidden absolute right-0 top-[2.75rem] mt-2 w-40 bg-white border border-slate-100 rounded-lg shadow-lg py-1 z-[90]">
            <a href="#" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-blue-600 font-medium">My Profile</a>
            <div class="border-t border-slate-100 my-1"></div>
            <a href="#" id="linkLogoutDesk" onclick="logoutSSO(event)" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-medium">Logout</a>
          </div>
        </div>
      </div>
    </header>

    <!-- BUKA AREA KONTEN UTAMA -->
    <main class="flex-1 overflow-y-auto overflow-x-hidden py-4 px-0 sm:px-6 bg-slate-50">
      <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar:hover::-webkit-scrollbar-thumb { background: #94a3b8; }
      </style>