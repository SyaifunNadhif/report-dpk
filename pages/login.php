<?php /* pages/login.php */ ?>
<div class="min-h-screen flex bg-white text-gray-900 font-sans overflow-hidden">
  
  <style>
    /* Keyframes untuk pergerakan meteor */
    @keyframes meteor {
      0% {
        transform: rotate(215deg) translateX(0);
        opacity: 1;
      }
      70% {
        opacity: 1;
      }
      100% {
        transform: rotate(215deg) translateX(-500px);
        opacity: 0;
      }
    }

    /* Style dasar meteor */
    .meteor-effect {
      position: absolute;
      top: 50%;
      left: 50%;
      height: 2px;
      background: linear-gradient(-45deg, #5f91ff, rgba(0, 0, 255, 0));
      border-radius: 999px;
      filter: drop-shadow(0 0 6px #69a0ff);
      animation: meteor 3s linear infinite;
      opacity: 0;
      z-index: 10;
    }
    
    .meteor-effect::before {
      content: '';
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      width: 300px;
      height: 1px;
      background: linear-gradient(90deg, #fff, transparent);
    }
  </style>

  <div class="hidden lg:flex lg:w-1/2 relative bg-blue-900 text-white flex-col justify-center items-center overflow-hidden">
    
    <div class="absolute inset-0 z-0" style="background-image: url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&q=80'); background-size: cover; background-position: center;"></div>
    
    <div class="absolute inset-0 bg-gradient-to-br from-gray-900/80 via-blue-900/80 to-black/80 z-0"></div>

    <span class="meteor-effect w-[100px]" style="top: -10%; left: 20%; animation-duration: 4s; animation-delay: 0s;"></span>
    <span class="meteor-effect w-[150px]" style="top: 10%; left: 80%; animation-duration: 3s; animation-delay: 1.5s;"></span>
    <span class="meteor-effect w-[120px]" style="top: -20%; left: 50%; animation-duration: 5s; animation-delay: 2s;"></span>
    <span class="meteor-effect w-[180px]" style="top: 30%; left: 110%; animation-duration: 3.5s; animation-delay: 0.5s;"></span>
    <span class="meteor-effect w-[140px]" style="top: 5%; left: 60%; animation-duration: 6s; animation-delay: 3s;"></span>
    <span class="meteor-effect w-[200px]" style="top: -15%; left: 10%; animation-duration: 4.5s; animation-delay: 4s;"></span>
    <span class="meteor-effect w-[160px]" style="top: 15%; left: 90%; animation-duration: 5.5s; animation-delay: 1s;"></span>
    
    <div class="relative z-20 text-center px-10">
      <div class="mb-6 inline-flex p-4 bg-white/10 rounded-full backdrop-blur-sm border border-white/20 shadow-[0_0_15px_rgba(255,255,255,0.3)]">
         <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
         </svg>
      </div>
      <h2 class="text-4xl font-bold mb-4 tracking-tight text-white drop-shadow-lg">Secure Portal</h2>
      <p class="text-blue-100 text-lg drop-shadow-md">ATLAS</p>
      <p class="text-blue-200 text-sm mt-2 opacity-80">Account Tracking & Lending Analytics System</p>
    </div>
  </div>

  <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-gray-50 text-gray-900 relative">
    
    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-lg border border-gray-100">
      
      <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Login Pegawai</h1>
        <p class="text-gray-500 text-sm">Masukkan ID Pegawai dan Password Anda.</p>
      </div>

      <div id="alreadyBox" class="hidden border-l-4 border-green-500 rounded-r bg-green-50 p-4 mb-6">
        <div class="text-sm text-gray-800 mb-3">
          Login sebagai <b id="alName" class="text-black"></b>.
        </div>
        <button id="btnGoHome" class="text-sm font-bold text-green-700 hover:underline mr-4">Ke Dashboard</button>
        <button id="btnSwitch" class="text-sm text-gray-600 hover:text-gray-900">Ganti Akun</button>
      </div>

      <form id="formLogin" class="space-y-5">
        
        <div>
          <label class="block text-sm font-bold text-gray-700 mb-1">Employee ID</label>
          <input type="text" id="employee_id" 
                 class="w-full rounded-lg border-gray-300 bg-white text-gray-900 placeholder-gray-400 shadow-sm focus:border-blue-600 focus:ring-blue-600 py-3 px-4"
                 placeholder="ID Pegawai" required>
        </div>

        <div>
          <label class="block text-sm font-bold text-gray-700 mb-1">Password</label>
          <div class="relative">
            <input type="password" id="password" 
                   class="w-full rounded-lg border-gray-300 bg-white text-gray-900 placeholder-gray-400 shadow-sm focus:border-blue-600 focus:ring-blue-600 py-3 px-4 pr-10"
                   placeholder="Kata sandi" required>
            <button type="button" id="togglePwd" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                 <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                 <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.523 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
               </svg>
            </button>
          </div>
        </div>

        <button type="submit" id="btnLogin" 
            class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-lg shadow-md text-sm font-bold text-white bg-blue-700 hover:bg-blue-800 transition-all transform hover:-translate-y-0.5">
            <svg id="spin" class="hidden animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
            <span id="btnText">Verifikasi & Masuk</span>
        </button>
        
        <div id="err" class="hidden flex items-center p-4 text-sm text-red-800 border border-red-200 rounded-lg bg-red-50" role="alert">
          <svg class="flex-shrink-0 inline w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
          <span id="errMsg">Info error disini</span>
        </div>

      </form>
    </div>
  </div>
</div>

<script>
  // ... Config Base Path & Utils ...
  function getBasePath() {
    const baseTag = document.querySelector('base')?.getAttribute('href');
    if (baseTag) return new URL(baseTag, location.origin).pathname.replace(/\/+$/, '') || '';
    if (window.BASE_APP) return new URL(window.BASE_APP, location.origin).pathname.replace(/\/+$/, '') || '';
    if (location.pathname.startsWith('/report-dpk')) return '/report-dpk';
    return '';
  }
  const BASE_APP = window.BASE_APP || location.origin + getBasePath();
  const API_LOGIN = '/api/auth/login/';
  const API_WHOAMI = '/api/auth/whoami';

  // Utils
  const saveToken = (t) => localStorage.setItem('dpk_token', t);
  const saveUser = (u) => localStorage.setItem('dpk_user', JSON.stringify(u));
  
  // Toggle Password
  document.getElementById('togglePwd').addEventListener('click', () => {
    const inp = document.getElementById('password');
    inp.type = inp.type === 'password' ? 'text' : 'password';
  });

  // LOGIK LOGIN UTAMA
  document.getElementById('formLogin').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const btn = document.getElementById('btnLogin');
    const spin = document.getElementById('spin');
    const btnText = document.getElementById('btnText');
    const errBox = document.getElementById('err');
    const errMsg = document.getElementById('errMsg');

    // Reset UI
    errBox.classList.add('hidden');
    btn.disabled = true;
    spin.classList.remove('hidden');
    btnText.textContent = 'Memeriksa...';

    // Ambil Values (Hanya Emp ID dan Password)
    const empId = document.getElementById('employee_id').value.trim();
    const pass  = document.getElementById('password').value;

    try {
        const res = await fetch(API_LOGIN, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ employee_id: empId, password: pass })
        });
        const json = await res.json();

        if (json?.status !== 200 || !json?.data?.token) {
            throw new Error(json?.message || 'Employee ID atau Password salah.');
        }

        saveToken(json.data.token);
        
        try {
            const r2 = await fetch(API_WHOAMI, { headers: { 'Authorization': json.data.token }});
            const j2 = await r2.json();
            if(j2?.data) saveUser(j2.data);
        } catch {}

        location.href = `${BASE_APP}/home`;

    } catch (error) {
        errMsg.textContent = error.message;
        errBox.classList.remove('hidden');
        btn.disabled = false;
        spin.classList.add('hidden');
        btnText.textContent = 'Verifikasi & Masuk';
    }
  });
</script>