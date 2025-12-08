<?php /* pages/login.php */ ?>
<div class="min-h-screen flex items-center justify-center bg-gray-100 py-10">
  <div class="w-full max-w-md bg-white rounded-lg shadow p-6">
    <div class="mb-6 text-center">
      <h1 class="text-2xl font-bold">Masuk ke Sistem</h1>
      <p class="text-gray-500 text-sm">Gunakan Employee ID dan password</p>
    </div>

    <!-- Info bila sudah login -->
    <div id="alreadyBox" class="hidden border rounded p-4 mb-5 bg-green-50">
      <p class="text-sm text-gray-700">
        Kamu sudah login sebagai <b id="alName"></b> (<span id="alBranch"></span>).
      </p>
      <div class="mt-3 flex gap-2">
        <a id="btnGoHome" class="px-3 py-2 rounded bg-blue-600 text-white text-sm cursor-pointer hover:bg-blue-700">Ke Home</a>
        <a id="btnSwitch" class="px-3 py-2 rounded bg-gray-200 text-sm cursor-pointer hover:bg-gray-300">Ganti Akun</a>
      </div>
    </div>

    <!-- Form login -->
    <form id="formLogin" class="space-y-4">
      <div>
        <label for="employee_id" class="text-sm block mb-1">Employee ID</label>
        <input type="text" id="employee_id" class="w-full border rounded px-3 py-2"
               placeholder="mis. 102-119" required autocomplete="username">
      </div>

      <div>
        <label for="password" class="text-sm block mb-1">Password</label>
        <div class="relative">
          <input type="password" id="password"
                 class="w-full border rounded px-3 py-2 pr-10"
                 placeholder="••••••••" required autocomplete="current-password">
          <button type="button" id="togglePwd"
                  class="absolute inset-y-0 right-0 px-3 text-gray-600"
                  title="Tampilkan/Sembunyikan" aria-pressed="false">
            <!-- default: tertutup -->
            <svg id="iconEyeClosed" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
              <path d="M3 3l18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              <path d="M10.585 10.585a3 3 0 104.243 4.243" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              <path d="M2 12s3.5-7 10-7c2.035 0 3.855.555 5.4 1.5M22 12s-3.5 7-10 7c-2.035 0-3.855-.555-5.4-1.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <svg id="iconEyeOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" viewBox="0 0 24 24" fill="none">
              <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z" stroke="currentColor" stroke-width="2"/>
              <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
            </svg>
          </button>
        </div>
      </div>

      <button type="submit" id="btnLogin"
              class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700
                     flex items-center justify-center gap-2">
        <svg id="spin" class="hidden animate-spin h-5 w-5" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10"
                  stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        <span>Masuk</span>
      </button>

      <p id="err" class="text-red-600 text-sm hidden"></p>
    </form>

    <p class="mt-6 text-center text-xs text-gray-500">© <?= date('Y') ?> BPR BKK Jateng</p>
  </div>
</div>

<script>
  // Base PATH saja: '' (root) atau '/e-pipelane'
  function getBasePath() {
    // 1) Hormati <base href="...">
    const baseTag = document.querySelector('base')?.getAttribute('href');
    if (baseTag) {
      const u = new URL(baseTag, location.origin);
      return u.pathname.replace(/\/+$/, '') || '';
    }

    // 2) Hormati window.BASE_APP kalau ada
    if (window.BASE_APP) {
      const u = new URL(window.BASE_APP, location.origin);
      return u.pathname.replace(/\/+$/, '') || '';
    }

    // 3) Deteksi prefix yang kita kenal (ubah/daftarkan jika perlu)
    //    Kalau URL sekarang berada di bawah /e-pipelane → pakai itu, selain itu anggap root.
    if (location.pathname === '/e-pipelane' || location.pathname.startsWith('/e-pipelane/')) {
      return '/e-pipelane';
    }

    // Default: root
    return '';
  }

  // Base URL lengkap (origin + base path)
  function getBaseApp() {
    return location.origin + getBasePath();
  }

  // Join helper: pastikan selalu absolute (diawali '/'), auto-prepend base path
  function absUrl(path = '/') {
    const base = getBasePath();
    const p = ('/' + String(path)).replace(/\/{2,}/g, '/'); // pastikan leading '/', kompres //
    return base + p;
  }


  const BASE_APP = window.BASE_APP || getBaseApp();
  const API_LOGIN   = '/api/auth/login/';   // tetap relatif ke app
  const API_WHOAMI  = '/api/auth/whoami';
  const FORCE_REDIRECT = new URLSearchParams(location.search).get('auto') === '1';

  // Storage keys
  const STORAGE_TOKEN_KEY = 'dpk_token';
  const STORAGE_USER_KEY  = 'dpk_user';

  // Utils storage
  const getToken  = () => localStorage.getItem(STORAGE_TOKEN_KEY) || '';
  const saveToken = (t) => localStorage.setItem(STORAGE_TOKEN_KEY, t);
  const saveUser  = (u) => localStorage.setItem(STORAGE_USER_KEY, JSON.stringify(u));
  const clearAuth = () => { localStorage.removeItem(STORAGE_TOKEN_KEY); localStorage.removeItem(STORAGE_USER_KEY); };

  // ====== Validasi token saat halaman login dibuka ======
  (async function checkExisting() {
    const token = getToken();
    if (!token) return; // tidak ada token -> tampilkan form login

    const me = await validateToken(token);
    if (!me) { // token invalid -> bersihkan & tetap di halaman login
      clearAuth();
      return;
    }

    // Token valid: tampilkan box "sudah login", sembunyikan form.
    showAlreadyLogged(me);

    // Hanya auto-redirect jika diminta (?auto=1)
    if (FORCE_REDIRECT) {
      location.href = `${BASE_APP}/home`;
    }
  })();

  async function validateToken(token) {
    try {
      const r = await fetch(API_WHOAMI, { headers: { 'Authorization': token } });
      if (!r.ok) return null;
      const j = await r.json();
      return (j && j.status === 200 && j.data) ? j.data : null;
    } catch { return null; }
  }

  function showAlreadyLogged(me) {
    const already = document.getElementById('alreadyBox');
    const form    = document.getElementById('formLogin');
    document.getElementById('alName').textContent   = me.full_name || '-';
    document.getElementById('alBranch').textContent = me.branch_name || '-';
    already.classList.remove('hidden');
    form.classList.add('hidden');

    document.getElementById('btnGoHome').addEventListener('click', () => {
      location.href = `${BASE_APP}/home`;
    });
    document.getElementById('btnSwitch').addEventListener('click', () => {
      clearAuth();
      // tampilkan form login lagi
      already.classList.add('hidden');
      form.classList.remove('hidden');
      // fokus ke employee_id
      document.getElementById('employee_id').focus();
    });
  }

  // ====== Toggle show/hide password + ikon ======
  const toggleBtn = document.getElementById('togglePwd');
  const inputPwd  = document.getElementById('password');
  const eyeOpen   = document.getElementById('iconEyeOpen');
  const eyeClosed = document.getElementById('iconEyeClosed');

  toggleBtn.addEventListener('click', () => {
    const showing = inputPwd.type === 'text';
    if (showing) {
      // switch ke hidden
      inputPwd.type = 'password';
      toggleBtn.setAttribute('aria-pressed', 'false');
      eyeOpen.classList.add('hidden');
      eyeClosed.classList.remove('hidden');
    } else {
      // switch ke visible
      inputPwd.type = 'text';
      toggleBtn.setAttribute('aria-pressed', 'true');
      eyeOpen.classList.remove('hidden');
      eyeClosed.classList.add('hidden');
    }
  });

  // ====== Submit login ======
  document.getElementById('formLogin').addEventListener('submit', async (e) => {
    e.preventDefault();

    const btn  = document.getElementById('btnLogin');
    const spin = document.getElementById('spin');
    const err  = document.getElementById('err');
    err.classList.add('hidden'); err.textContent = '';

    btn.disabled = true;
    btn.classList.add('opacity-70','cursor-not-allowed');
    spin.classList.remove('hidden');

    const payload = {
      employee_id: document.getElementById('employee_id').value.trim(),
      password: document.getElementById('password').value
    };

    try {
      // 1) Login -> ambil token
      const res  = await fetch(API_LOGIN, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const json = await res.json();

      if (json?.status !== 200 || !json?.data?.token) {
        throw new Error(json?.message || 'Login gagal');
      }

      const token = json.data.token;
      saveToken(token);

      // 2) Whoami -> simpan profil user
      const me = await validateToken(token);
      if (me) saveUser(me);

      // 3) Redirect ke /home
      location.href = `${BASE_APP}/home`;

    } catch (e2) {
      err.textContent = e2.message || 'Terjadi kesalahan. Coba lagi.';
      err.classList.remove('hidden');
    } finally {
      btn.disabled = false;
      btn.classList.remove('opacity-70','cursor-not-allowed');
      spin.classList.add('hidden');
    }
  });
</script>
