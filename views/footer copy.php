</main>
    <!-- TUTUP AREA KONTEN -->
  </div>
  <!-- TUTUP BUNGKUS KANAN -->
</div>
<!-- TUTUP WRAPPER UTAMA -->

<!-- FLOATING HELPDESK BUTTON -->
<!-- z-[60] supaya aman dari header tabel, tapi di bawah overlay sidebar -->
<div class="fixed bottom-4 right-4 z-[60] flex flex-col items-end gap-1">
    <div id="helpdeskContainer" 
         class="flex items-center bg-[#0056b3] text-white shadow-lg rounded-full overflow-hidden transition-all duration-300 ease-in-out cursor-pointer"
         style="max-width: 48px; padding: 12px;"
         onclick="handleHelpdeskClick()">
        <div class="flex items-center gap-3 whitespace-nowrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 11h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-5Zm0 0a9 9 0 1 1 18 0m0 0v5a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3Z"/><path d="M21 16v2a4 4 0 0 1-4 4h-5"/>
            </svg>
            <div id="helpdeskText" class="hidden flex flex-col leading-none pr-4">
                <span class="text-[11px] font-bold uppercase tracking-tight">Helpdesk</span>
                <span class="text-[9px] opacity-90">Sambatan</span>
            </div>
        </div>
    </div>
</div>

<script>
    // --- 1. SCRIPT HELPDESK ---
    let isExpanded = false;
    function handleHelpdeskClick() {
        const container = document.getElementById('helpdeskContainer');
        const text = document.getElementById('helpdeskText');
        if (!isExpanded) {
            container.style.maxWidth = '200px';
            text.classList.remove('hidden');
            isExpanded = true;
            setTimeout(() => { if(isExpanded) closeHelpdesk(); }, 5000);
        } else {
            window.open('https://helpdesk.bkkjateng.co.id/', '_blank');
            closeHelpdesk();
        }
    }
    function closeHelpdesk() {
        const container = document.getElementById('helpdeskContainer');
        const text = document.getElementById('helpdeskText');
        container.style.maxWidth = '48px';
        text.classList.add('hidden');
        isExpanded = false;
    }

    // --- 2. SCRIPT DROPDOWN PROFILE ---
    const btnProfile = document.getElementById('btnProfileMenu');
    const menuProfile = document.getElementById('dropdownProfileMenu');
    if(btnProfile && menuProfile) {
        btnProfile.addEventListener('click', (e) => {
            e.stopPropagation();
            menuProfile.classList.toggle('hidden');
        });
        document.addEventListener('click', (e) => {
            if (!menuProfile.contains(e.target) && !btnProfile.contains(e.target)) {
                menuProfile.classList.add('hidden');
            }
        });
    }

    // --- 3. SCRIPT MENU SIDEBAR DESKTOP & MOBILE ---
    document.addEventListener('DOMContentLoaded', () => {
      
      const accordions = document.querySelectorAll('.accordion-btn');
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('sidebarOverlay');
      const btnToggle = document.getElementById('btnToggleSidebar');

      // Logic Klik Accordion
      accordions.forEach(btn => {
        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          const content = btn.nextElementSibling;
          const caret = btn.querySelector('.caret');
          
          document.querySelectorAll('.accordion-content').forEach(otherContent => {
            if (otherContent !== content && !otherContent.classList.contains('hidden')) {
              otherContent.classList.add('hidden');
              otherContent.previousElementSibling.querySelector('.caret').classList.remove('rotate-180');
            }
          });
          
          content.classList.toggle('hidden');
          caret.classList.toggle('rotate-180');
        });
      });

      // Logic Tutup otomatis saat mouse keluar (Hanya untuk Desktop)
      if(sidebar) {
          sidebar.addEventListener('mouseleave', () => {
              if (window.innerWidth >= 768) { 
                  document.querySelectorAll('.accordion-content').forEach(content => { content.classList.add('hidden'); });
                  document.querySelectorAll('.caret').forEach(caret => { caret.classList.remove('rotate-180'); });
              }
          });
      }

      // Logic Toggle Mobile Sidebar
      function toggleSidebar() {
        if(sidebar) sidebar.classList.toggle('-translate-x-full');
        if(overlay) overlay.classList.toggle('hidden');
      }

      if(btnToggle) btnToggle.addEventListener('click', toggleSidebar);
      if(overlay) overlay.addEventListener('click', toggleSidebar);

    });

    // --- 4. SCRIPT RENDER USER & ROLE (JWT) ---
    (() => {
      const TOKEN_KEY='dpk_token', USER_KEY='dpk_user';
      function parseJwt(t){ 
        try{
          const p=String(t).split('.'); 
          if(p.length<2) return null;
          const b64=p[1].replace(/-/g,'+').replace(/_/g,'/'); 
          const json=decodeURIComponent(atob(b64).split('').map(c=>'%'+('00'+c.charCodeAt(0).toString(16)).slice(-2)).join('')); 
          return JSON.parse(json);
        } catch { return null; } 
      }
      function getUser(){
        try { 
          if(window.__USER) return window.__USER; 
          const raw=localStorage.getItem(USER_KEY); 
          if(raw) return JSON.parse(raw);
        } catch {}
        const tok=(localStorage.getItem(TOKEN_KEY)||'').trim(); 
        if(!tok) return null;
        const p=parseJwt(tok)||{}; 
        return { 
          full_name:p.full_name||p.name||p.nama||null, 
          branch_name:p.branch_name||p.branch||p.cabang||null,
          employee_id:p.employee_id||p.emp_id||p.nik||null, 
          id:p.sub||p.user_id||null, kode:p.kode||p.branch_code||null,
          account_handle:p.handle||p.username||p.email||null,
          role: p.role || null
        };
      }
      function paint(u){
        if(!u) return false;
        const name=document.getElementById('navUserName'), br=document.getElementById('navBranch'), acc=document.getElementById('accHandle');
        const menuMonevDev = document.getElementById('menuMonevDev');
        let ok=false;

        if(name){ name.textContent=u.full_name||'-'; ok=true; }
        if(br){ br.textContent=u.branch_name||'-'; ok=true; }
        if(acc){ acc.textContent=(u.account_handle||u.username||u.email||u.employee_id||u.kode||'-'); ok=true; }
        
        const isDev = (u.role === 'dev');
        if(menuMonevDev) {
          if(isDev) menuMonevDev.style.setProperty('display', 'block', 'important'); 
          else menuMonevDev.style.setProperty('display', 'none', 'important');
          ok=true;
        }
        return ok;
      }
      const user=getUser(); 
      if(!paint(user)){
        let n=0; 
        const t=setInterval(()=>{ if(paint(user)||++n>40) clearInterval(t); },100);
        document.addEventListener('DOMContentLoaded', ()=>paint(user), {once:true});
        const mo=new MutationObserver(()=>{ if(paint(user)) mo.disconnect(); });
        mo.observe(document.documentElement,{childList:true,subtree:true});
      }
    })();
</script>