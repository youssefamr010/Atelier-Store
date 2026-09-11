{{-- Luxury PWA Install Banner --}}
@php
    $isAr = ($settings['storefront_lang'] ?? 'en') === 'ar';
@endphp

<div id="atelier-install-banner" 
     class="fixed bottom-20 lg:bottom-6 left-4 right-4 sm:left-auto sm:right-6 sm:max-w-md z-40 bg-[#121214]/95 border border-amber-400/30 text-white p-4 rounded-2xl shadow-[0_15px_40px_rgba(0,0,0,0.6)] backdrop-blur-xl transform translate-y-28 opacity-0 transition-all duration-500 flex items-center justify-between gap-3 font-sans"
     dir="{{ $isAr ? 'rtl' : 'ltr' }}"
     style="display: none;">
    
    <div class="flex items-center gap-3 min-w-0">
        <img src="/icons/icon-192.png" alt="Atelier App" class="w-12 h-12 rounded-xl border border-white/20 shrink-0 bg-black object-cover">
        <div class="min-w-0">
            <div class="flex items-center gap-1.5">
                <p class="font-bold text-xs text-white truncate">{{ $isAr ? 'تطبيق ATELIER الفاخر' : 'ATELIER Official App' }}</p>
                <span class="px-1.5 py-0.2 bg-amber-400/20 text-amber-300 text-[9px] font-mono rounded font-bold">VIP</span>
            </div>
            <p class="text-[10px] text-gray-400 truncate">{{ $isAr ? 'تثبيت التطبيق لتجربة تصفح أسرع وإشعارات فورية' : 'Install for instant access & exclusive drops' }}</p>
        </div>
    </div>

    <div class="flex items-center gap-2 shrink-0">
        <button type="button" 
                id="btn-install-app"
                class="px-3 py-2 bg-gradient-to-r from-amber-400 to-amber-300 hover:from-amber-300 hover:to-white text-black font-bold text-[11px] rounded-xl shadow-sm transition-all active:scale-95 whitespace-nowrap">
            {{ $isAr ? 'تثبيت' : 'Install' }}
        </button>
        <button type="button" 
                id="btn-dismiss-install"
                class="w-7 h-7 rounded-lg bg-white/10 hover:bg-white/20 text-gray-400 hover:text-white flex items-center justify-center text-xs transition-colors"
                aria-label="Dismiss">
            ✕
        </button>
    </div>
</div>

<script>
(() => {
    let deferredPrompt = null;
    const banner = document.getElementById('atelier-install-banner');
    const installBtn = document.getElementById('btn-install-app');
    const dismissBtn = document.getElementById('btn-dismiss-install');

    // Check if dismissed recently
    const isDismissed = sessionStorage.getItem('atelier_install_dismissed');

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;

        if (!isDismissed && banner) {
            banner.style.display = 'flex';
            setTimeout(() => {
                banner.classList.remove('translate-y-28', 'opacity-0');
                banner.classList.add('translate-y-0', 'opacity-100');
            }, 2500); // polite delay after page load
        }
    });

    if (installBtn) {
        installBtn.addEventListener('click', async () => {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            deferredPrompt = null;
            hideBanner();
        });
    }

    if (dismissBtn) {
        dismissBtn.addEventListener('click', () => {
            sessionStorage.setItem('atelier_install_dismissed', 'true');
            hideBanner();
        });
    }

    function hideBanner() {
        if (!banner) return;
        banner.classList.remove('translate-y-0', 'opacity-100');
        banner.classList.add('translate-y-28', 'opacity-0');
        setTimeout(() => { banner.style.display = 'none'; }, 500);
    }
})();
</script>
