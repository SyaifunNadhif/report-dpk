<!-- <script src="../path/to/flowbite/dist/flowbite.min.js"></script> -->

<!-- Auth -->

<script>

// script.js
(() => {
    const TOKEN_KEY = 'dpk_token';
    const USER_KEY = 'dpk_user';
    const API_WHOAMI = './api/auth/whoami';

    function getUser() {
        try {
            const raw = localStorage.getItem(USER_KEY);
            return raw ? JSON.parse(raw) : null;
        } catch { return null; }
    }

    function doLogout() {
        console.log("Proses Logout...");
        localStorage.removeItem(TOKEN_KEY);
        localStorage.removeItem(USER_KEY);
        window.location.href = './login';
    }

    // --- LOGIKA PERMISSION (Saring Menu tapi AMANKAN Logout) ---
    function applyMenuPermissions(u) {
        if (!u) return;

        // 1. BYPASS DEV ROLE
        if (u.role === 'dev') return;

        const pos = u.job_position || "";
        const lvl = u.level || "";

        // 🛡️ Daftar sub-menu yang dibatasi (Sesuai ID di HTML kamu)
        const accessRules = {
            'sub-realisasi-kredit': ['AO Kredit', 'Kepala Bidang', 'Kepala Cabang'],
            'sub-realisasi-promo': ['AO Kredit', 'Kepala Bidang'],
            'sub-realisasi-ao': ['AO Kredit', 'Kepala Bidang'],
            'sub-otp': ['AO Kredit', 'Kepala Bidang'],
            'sub-rekap-rr': ['AO Kredit', 'Kepala Bidang', 'Kepala Divisi'],
            'sub-migrasi-bucket-sc': ['AO Kredit', 'Kepala Bidang'],
            'sub-mob': ['AO Kredit', 'Kepala Bidang'],
            'sub-pipelane-ao': ['AO Kredit', 'Kepala Bidang'],
            'sub-jatuh-tempo': ['AO Kredit', 'Kepala Bidang'],
            'sub-npl': ['AO Remedial', 'Kepala Bidang', 'Kepala Cabang'],
            'sub-perbandingan-npl': ['AO Remedial', 'Kepala Bidang'],
            'sub-recovery-npl': ['AO Remedial', 'Kepala Bidang', 'Kepala Cabang'],
            'sub-flow-par': ['AO Remedial', 'Kepala Bidang'],
            'sub-npl-25': ['AO Remedial', 'Kepala Bidang', 'Kepala Cabang'],
            'sub-potensi-npl': ['AO Remedial', 'Kepala Bidang'],
            'sub-recovery-ph': ['AO Remedial', 'Kepala Bidang', 'Kepala Cabang'],
            'sub-lgd': ['Kepala Bidang', 'Kepala Divisi'],
            'sub-actual-kredit': ['Kepala Bidang', 'Kepala Cabang'],
            'sub-migrasi-bucket': ['AO Remedial', 'AO Kredit', 'Kepala Bidang'],
            'sub-search-debitur': ['AO Remedial', 'AO Kredit', 'Kepala Bidang', 'Kepala Cabang'],
            'sub-migrasi-kolek': ['AO Remedial', 'Kepala Bidang']
        };

        // 2. FILTER SUB-MENU (KECUALIKAN LOGOUT & PROFILE)
        Object.keys(accessRules).forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                // Keamanan tambahan: Jangan hapus jika elemen ini berkaitan dengan Profile atau Logout
                if (id.includes('Profile') || id.includes('Logout')) return;

                const allowed = accessRules[id];
                const hasAccess = allowed.some(p => pos.includes(p)) || (lvl === 'PE');
                
                if (!hasAccess) {
                    el.remove();
                }
            }
        });

        // 3. CLEANUP PARENT (KECUALIKAN PROFILE MOBILE)
        const groups = [
            { panelId: 'dropdownPemasaran', parentId: 'parent-pemasaran' },
            { panelId: 'dropdownNPL', parentId: 'parent-npl' },
            { panelId: 'dropdownPH', parentId: 'parent-ph' },
            { panelId: 'dropdownPenagihan', parentId: 'parent-collection' }
            // dropdownProfileMobile TIDAK dimasukkan ke sini agar tidak tersembunyi otomatis
        ];

        groups.forEach(g => {
            const panel = document.getElementById(g.panelId);
            if (panel) {
                const items = panel.querySelectorAll('li');
                if (items.length === 0) {
                    const parent = document.getElementById(g.parentId);
                    if (parent) parent.style.display = 'none';
                }
            }
        });
    }

    // --- INITIALIZE ---
    async function initNavbar() {
        const token = localStorage.getItem(TOKEN_KEY);
        if (!token) return;

        let user = getUser();
        try {
            const r = await fetch(API_WHOAMI, { headers: { 'Authorization': token } });
            const j = await r.json();
            if (r.ok && j.status === 200) {
                user = j.data;
                localStorage.setItem(USER_KEY, JSON.stringify(user));
            }
        } catch (e) { console.error("Sync user gagal", e); }

        if (user) {
            // Pasang Nama & Cabang di Navbar
            if(document.getElementById('navUserName')) document.getElementById('navUserName').textContent = user.full_name || '-';
            if(document.getElementById('navBranch')) document.getElementById('navBranch').textContent = user.branch_name || '-';
            
            // Terapkan filter menu
            applyMenuPermissions(user);
        }

        // --- HANDLE CLICKS (Logout & Toggle Dropdown) ---
        document.addEventListener('click', (e) => {
            // 1. Detect Click Logout (Desktop & Mobile)
            const logoutTarget = e.target.closest('#linkLogout, #linkLogoutDesk, #linkLogoutMobile');
            if (logoutTarget) {
                e.preventDefault();
                doLogout();
                return;
            }

            // 2. Detect Click Profile Toggle (Pastikan ID-nya sesuai dengan button di HTML kamu)
            const profileToggle = e.target.closest('#dropdownProfileButton');
            if (profileToggle) {
                e.preventDefault();
                const dropdown = document.getElementById('dropdownProfile');
                if (dropdown) {
                    dropdown.classList.toggle('is-open');
                }
            }
        });
    }

    // Jalankan Init
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNavbar);
    } else {
        initNavbar();
    }
})();


document.addEventListener('click', (e) => {
    // 1. LOGIKA LOGOUT (Sangat Penting)
    // Gunakan selectors yang mencakup semua kemungkinan ID logout Anda
    const logoutTarget = e.target.closest('#linkLogout, #linkLogoutDesk, #linkLogoutMobile');
    
    if (logoutTarget) {
        console.log("Tombol logout diklik!"); // Cek di console browser
        e.preventDefault();
        e.stopPropagation();
        doLogout();
        return;
    }

    // 2. LOGIKA TOGGLE DROPDOWN (Desktop)
    const btnDesk = e.target.closest('#dropdownProfileButton');
    if (btnDesk) {
        const panelDesk = document.getElementById('dropdownProfile');
        if (panelDesk) {
            panelDesk.classList.toggle('hidden');
            // Jika Anda pakai class 'is-open' seperti di script lama:
            panelDesk.classList.toggle('is-open'); 
        }
    }

    // 3. LOGIKA TOGGLE DROPDOWN (Mobile - Jika library dropdown tidak jalan)
    const btnMob = e.target.closest('#btnProfileMobile');
    if (btnMob) {
        const panelMob = document.getElementById('dropdownProfileMobile');
        if (panelMob) {
            panelMob.classList.toggle('hidden');
        }
    }
});
</script>



