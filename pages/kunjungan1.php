
<div class="kun-wrap">
  <div class="kun-card">
    <h2 class="kun-title">📍 Create Kunjungan</h2>
    <p class="kun-desc">Lengkapi data, ambil foto/unggah, dan sistem akan menempel waktu & lokasi ke foto.</p>

    <!-- <div id="kun-status" class="kun-status hidden"></div> -->

    <!-- DATA DEBITUR -->
    <fieldset class="kun-fieldset">
      <legend>Data Debitur</legend>
      <div class="kun-grid2">
        <div><label>No. Rekening</label><input id="f_no_rek" type="text" readonly></div>
        <div><label>Kolektabilitas</label><input id="f_kolek" type="text" readonly></div>
        <div><label>Baki Debet</label><input id="f_bd" type="text" readonly></div>
        <div><label>Hari Menunggak</label><input id="f_hm" type="text" readonly></div>
        <div><label>Tunggakan Pokok</label><input id="f_tp" type="text" readonly></div>
        <div><label>Tunggakan Bunga</label><input id="f_tb" type="text" readonly></div>
      </div>
    </fieldset>

    <!-- TINDAKAN -->
    <fieldset class="kun-fieldset">
      <legend>Tindakan</legend>
      <div class="kun-grid2">
        <div>
          <label>Kode Tindakan</label>
          <select id="kode_tindakan" required>
            <option value="">— Pilih —</option>
            <!-- Contacted -->
            <option value="ALM">ALM - Almarhum</option><option value="PBD">PBD - Pasang Badan</option>
            <option value="KSS">KSS - Kasus</option><option value="BCN">BCN - Bencana</option>
            <option value="SKT">SKT - Sakit</option><option value="JJA">JJA - Janji Jual Aset</option>
            <option value="JJJ">JJJ - Janji Jual Jaminan</option><option value="RES">RES - Restruktur</option>
            <option value="HPR">HPR - Proses Hukum</option><option value="PTP">PTP - Promise to Pay</option>
            <option value="PET">PET - Pick up Promise Taken</option><option value="PPK">PPK - Pick up Payment Collected</option>
            <option value="LNS">LNS - Pelunasan</option>
            <!-- No-contacted -->
            <option value="FRD">FRD - Fraud</option><option value="ARA">ARA - Alamat Salah sejak Awal</option>
            <option value="CRA">CRA - Cerai</option><option value="PHD">PHD - Pindah</option>
            <option value="RKS">RKS - Rumah Kosong</option><option value="SKP">SKP - Skip</option>
          </select>
        </div>
        <div>
          <label>Jenis Tindakan</label>
          <select id="jenis_tindakan" required>
            <option value="">— Pilih —</option>
            <option value="Kunjungan">Kunjungan</option><option value="Telepon">Telepon</option><option value="Lainnya">Lainnya</option>
          </select>
        </div>
        <div>
          <label>Lokasi Tindakan</label>
          <select id="lokasi_tindakan" required>
            <option value="">— Pilih —</option>
            <option value="Rumah">Rumah</option><option value="Kantor">Kantor</option>
            <option value="Handphone">Handphone</option><option value="Lainnya">Lainnya</option>
          </select>
        </div>
        <div>
          <label>Orang Ditemui</label>
          <select id="orang_ditemui">
            <option value="">— Pilih —</option>
            <option value="Debitur">Debitur</option><option value="Ibu">Ibu</option>
            <option value="Bapak">Bapak</option><option value="Pasangan">Pasangan</option>
            <option value="Anak">Anak</option><option value="Lainnya">Lainnya</option>
          </select>
        </div>

        <div>
          <label>Nominal Janji Bayar (PTP)</label>
          <input id="nominal_janji_bayar" type="number" min="0" step="1000" placeholder="0" disabled>
        </div>
        <div>
          <label>Tanggal Janji Bayar (PTP)</label>
          <input id="tanggal_janji_bayar" type="date" disabled>
        </div>

        <div>
          <label>Status Kunjungan</label>
          <input id="status_kunjungan" type="text" readonly placeholder="-">
        </div>
        <div>
          <label>Keterangan</label>
          <textarea id="keterangan" rows="3" placeholder="Catatan singkat"></textarea>
        </div>
      </div>
    </fieldset>

    <!-- LOKASI & FOTO -->
    <fieldset class="kun-fieldset">
      <legend>Lokasi & Foto</legend>
      <div class="kun-grid2">
        <div>
          <label>Koordinat</label>
          <div class="kun-row">
            <input id="lat" type="text" placeholder="Lat" readonly>
            <input id="lng" type="text" placeholder="Lng" readonly>
            <button id="btnLoc" type="button" class="kbtn kbtn-secondary" title="Ambil Koordinat">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"></circle><circle cx="12" cy="12" r="10"></circle>
              </svg>
            </button>
          </div>
        </div>
        <div>
          <label>Alamat (otomatis dari koordinat)</label>
          <input id="alamat_gps" type="text" placeholder="Menunggu koordinat…" readonly>
        </div>

        <div>
          <label>Foto Kunjungan</label>
          <div class="kun-row">
            <input id="nama_foto" type="text" placeholder="Belum dipilih" readonly>
            <button id="btnUpload" type="button" class="kbtn" title="Upload dari Galeri">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line>
              </svg><span>Upload</span>
            </button>
            <button id="btnCamera" type="button" class="kbtn kbtn-primary" title="Ambil Foto (Kamera Live)">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="5" width="18" height="14" rx="2" ry="2"></rect><circle cx="12" cy="12" r="3"></circle>
              </svg><span>Kamera</span>
            </button>
            <button id="btnDownload" type="button" class="kbtn" title="Unduh foto bertanda" disabled>⬇️ Unduh</button>
          </div>
          <input id="fileFoto" type="file" accept="image/*" class="hidden">
        </div>

        <div>
          <label>Waktu Buat</label>
          <input id="created" type="text" readonly>
        </div>

        <div class="kun-col-full">
          <label>Preview</label>
          <div class="kun-preview"><canvas id="canvas" width="0" height="0"></canvas></div>
        </div>
      </div>
    </fieldset>

    <div class="kun-actions">
      <button id="btnSimpan" class="kbtn kbtn-primary" type="button" title="Simpan">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M19 21H5a2 2 0 0 1-2-2V7l4-4h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2z"></path>
          <polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline>
        </svg><span>Simpan</span>
      </button>
      <button id="btnBatal" class="kbtn" type="button" onclick="history.back()">Batal</button>
    </div>
  </div>
</div>

<!-- Kamera Modal -->
<div class="kun-cam-backdrop" id="camPanel" hidden>
  <div class="kun-cam-sheet">
    <div class="kun-cam-head">
      <strong>🎥 Kamera</strong>
      <div class="kun-row">
        <button id="btnSwitch" type="button" class="kbtn">Switch</button>
        <button id="btnClose" type="button" class="kbtn">Tutup</button>
      </div>
    </div>
    <video id="video" autoplay playsinline muted></video>
    <div class="kun-actions" style="margin-top:10px">
      <button id="btnSnap" type="button" class="kbtn kbtn-secondary">📸 Capture</button>
    </div>
    <div id="camStatus" class="kun-desc" style="margin-top:6px"></div>
  </div>
</div>

<style>
  /* Scoped */
  .kun-wrap{ max-width:1080px; margin:0 auto; padding:16px; }
  .kun-card{ background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:16px; box-shadow:0 6px 18px rgba(0,0,0,.06); }
  .kun-title{ margin:0 0 4px; font-size:20px; font-weight:700; color:#0f172a; }
  .kun-desc{ margin:0 0 10px; color:#475569; font-size:14px; }

  .kun-status{ margin:0 0 12px; padding:10px 12px; border-radius:10px; font-size:14px; }
  .kun-status.error{ background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
  .kun-status.ok{ background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
  .hidden{ display:none !important; }

  .kun-fieldset{ border:1px solid #e5e7eb; border-radius:12px; padding:12px; margin:10px 0; }
  .kun-fieldset > legend{ padding:0 6px; color:#1f2937; font-weight:600; }

  .kun-grid2{ display:grid; grid-template-columns:1fr 1fr; gap:10px; }
  .kun-col-full{ grid-column:1 / -1; }
  .kun-row{ display:flex; gap:8px; align-items:center; flex-wrap:wrap; }

  label{ display:block; font-size:13px; color:#334155; margin-bottom:6px; }
  input[type="text"], input[type="date"], input[type="number"], textarea, select{
    width:100%; background:#fff; border:1px solid #cbd5e1; color:#0f172a; padding:10px 12px; border-radius:10px; font-size:14px;
  }
  textarea{ min-height:70px; resize:vertical; }

  .kbtn{ display:inline-flex; gap:8px; align-items:center; justify-content:center; padding:10px 14px; border-radius:12px;
         background:#f1f5f9; border:1px solid #cbd5e1; color:#0f172a; font-weight:600; }
  .kbtn:hover{ background:#e2e8f0; }
  .kbtn-primary{ background:#2563eb; border-color:#1d4ed8; color:#fff; box-shadow:0 6px 14px rgba(37,99,235,.25); }
  .kbtn-primary:hover{ background:#1e40af; }
  .kbtn-secondary{ background:#10b981; border-color:#059669; color:#fff; box-shadow:0 6px 14px rgba(16,185,129,.25); }
  .kbtn-secondary:hover{ background:#047857; }

  .kun-actions{ display:flex; gap:10px; flex-wrap:wrap; margin-top:12px; }

  .kun-preview{ background:#f8fafc; border:1px dashed #cbd5e1; border-radius:12px; min-height:220px; display:flex; align-items:center; justify-content:center; }
  #canvas{ max-width:100%; height:auto; border-radius:10px; display:block; }

  /* Kamera modal */
  .kun-cam-backdrop{ position:fixed; inset:0; background:rgba(15,23,42,.5); display:grid; place-items:center; z-index:1000; }
  .kun-cam-sheet{ width:min(96vw,560px); background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:12px; }
  .kun-cam-head{ display:flex; align-items:center; justify-content:space-between; margin-bottom:8px; }
  #video{ width:100%; height:auto; max-height:65vh; object-fit:contain; background:#000; border-radius:12px; }

  @media (max-width:900px){ .kun-grid2{ grid-template-columns:1fr; } }
  @media (max-width:640px){
    .kun-wrap{ padding:12px; }
    input, select, textarea{ font-size:13px; }
    .kbtn{ width:100%; }
    .kun-preview{ min-height:180px; }
  }
</style>

<script>
(() => {
  // ====== KONFIG KEY LOCALSTORAGE
  const TOKEN_KEY = 'dpk_token';
  const USER_KEY  = 'dpk_user';

  // ====== Ambil user dari LS / JWT ======
  function getUserFromLS() {
    try {
      if (window.__USER) return window.__USER;
      const raw = localStorage.getItem(USER_KEY);
      return raw ? JSON.parse(raw) : null;
    } catch { return null; }
  }
  function parseJwt(token) {
    try {
      const parts = String(token).split('.');
      if (parts.length < 2) return null;
      const b64 = parts[1].replace(/-/g, '+').replace(/_/g, '/');
      const json = decodeURIComponent(atob(b64).split('').map(c => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)).join(''));
      return JSON.parse(json);
    } catch { return null; }
  }
  function getUserFallbackFromJWT() {
    const t = (localStorage.getItem(TOKEN_KEY) || '').trim();
    if (!t) return null;
    const p = parseJwt(t) || {};
    // map klaim yang umum → field yang dipakai navbar
    return {
      full_name   : p.full_name || p.name || p.nama || null,
      branch_name : p.branch_name || p.branch || p.cabang || null,
      employee_id : p.employee_id || p.emp_id || p.nik || null,
      id          : p.sub || p.user_id || null,
      kode        : p.kode || p.branch_code || null,
      account_handle: p.handle || p.username || p.email || null
    };
  }

  // ====== Cat ke Navbar (aman bila elems muncul belakangan) ======
  function paintUser(u) {
    if (!u) return false;

    const nameEl   = document.getElementById('navUserName');
    const branchEl = document.getElementById('navBranch');
    const navEl    = document.getElementById('mainNavbar');
    const accEl    = document.getElementById('accHandle');

    // Kalau memang halaman ini cuma icon (tanpa label), wajar tidak ada nameEl/branchEl.
    // Kita tetap return true kalau setidaknya salah satu elem ada.
    let touched = false;

    if (nameEl)   { nameEl.textContent   = u.full_name   || '-'; touched = true; }
    if (branchEl) { branchEl.textContent = u.branch_name || '-'; touched = true; }
    if (navEl) {
      navEl.dataset.userId     = u.id ?? '';
      navEl.dataset.employeeId = u.employee_id ?? '';
      navEl.dataset.kode       = u.kode ?? '';
      touched = true;
    }
    if (accEl) {
      const acc = u.account_handle || u.handle || u.username || u.email || u.employee_id || u.kode || '-';
      accEl.textContent = acc; touched = true;
    }
    return touched;
  }

  // 1) coba langsung pakai dpk_user
  let user = getUserFromLS();
  if (!user) {
    // 2) fallback decode JWT (FE-only)
    user = getUserFallbackFromJWT();
  }

  // 3) jika elemen belum ada (karena navbar dirender belakangan), tunggu & retry
  function tryPaint() { return paintUser(user); }

  if (!tryPaint()) {
    // DOMContentLoaded fallback
    document.addEventListener('DOMContentLoaded', tryPaint, { once:true });

    // Polling singkat 4 detik
    let n = 0;
    const t = setInterval(() => {
      if (tryPaint() || ++n > 40) clearInterval(t);
    }, 100);

    // MutationObserver (kalau navbar di-inject setelahnya)
    const mo = new MutationObserver(() => {
      if (tryPaint()) mo.disconnect();
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  }

  // 4) Highlight menu aktif (opsional, aman untuk FE-only)
  function markActive() {
    const slug = location.pathname.split('/').pop() || new URLSearchParams(location.search).get('url') || 'home';
    document.querySelectorAll('#navbar-default a[href]').forEach(a => {
      const href = a.getAttribute('href');
      if (href && href === slug) a.classList.add('nb-active');
    });
  }
  markActive();
  document.addEventListener('DOMContentLoaded', markActive, { once:true });
})();
</script>
