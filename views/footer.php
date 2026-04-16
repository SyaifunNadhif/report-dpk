<div class="fixed bottom-4 right-4 z-50 flex flex-col items-end gap-1">
    
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

    <!-- <div class="text-[7px] text-gray-400 pr-2">© 2025 BKK Jateng</div> -->
</div>

<script>
    let isExpanded = false;

    function handleHelpdeskClick() {
        const container = document.getElementById('helpdeskContainer');
        const text = document.getElementById('helpdeskText');

        if (!isExpanded) {
            // KLIK PERTAMA: Buka Kotak (Sesuai Foto Anda)
            container.style.maxWidth = '200px';
            text.classList.remove('hidden');
            isExpanded = true;
            
            // Opsional: Otomatis menutup lagi jika tidak diklik selama 5 detik
            setTimeout(() => {
                if(isExpanded) closeHelpdesk();
            }, 5000);

        } else {
            // KLIK KEDUA: Buka Link Web
            window.open('https://helpdesk.bkkjateng.co.id/', '_blank');
            // Kembalikan ke mode icon setelah buka link
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
</script>