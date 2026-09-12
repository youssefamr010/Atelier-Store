{{-- Connection Lost Modal with Crumpled Warning Tape --}}
@php
    $isArabicStore = ($settings['storefront_lang'] ?? 'en') === 'ar';
@endphp

<div id="atelier-offline-overlay" 
     class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/80 backdrop-blur-md transition-all duration-300 opacity-0 pointer-events-none p-4"
     dir="{{ $isArabicStore ? 'rtl' : 'ltr' }}"
     role="dialog" 
     aria-modal="true"
     aria-hidden="true">

    {{-- Luxury Brutalist Error Modal Card --}}
    <div class="relative z-10 w-full max-w-xl bg-[#F5F5F0] border-2 border-black p-6 sm:p-8 shadow-[12px_12px_0px_0px_rgba(0,0,0,1)] text-center text-black overflow-hidden transform transition-transform duration-300 scale-95" id="atelier-offline-card">
        
        {{-- Brand Tag --}}
        <div class="font-editorial font-bold text-[10px] uppercase tracking-[0.25em] text-black/60 pb-3 border-b border-black/10 mb-4 flex items-center justify-between">
            <span>{{ $settings['storeName'] ?? 'ATELIER STUDIO EGYPT' }}</span>
            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-red-100 text-red-700 text-[9px] font-mono font-bold tracking-normal border border-red-300 rounded">
                <span class="w-1.5 h-1.5 rounded-full bg-red-600 animate-pulse"></span>
                <span>{{ $isArabicStore ? 'انقطع الاتصال' : 'OFFLINE' }}</span>
            </span>
        </div>

        {{-- Crumpled Warning Tape Illustration Component --}}
        <div class="my-2">
            @include('components.crumpled-tape', [
                'variant' => 'black',
                'text' => 'ATELIER · CONNECTION LOST · RETRYING LINK · SYSTEM STANDBY · '
            ])
        </div>

        {{-- Heading with strictly uniform typography discipline --}}
        <h2 class="font-editorial font-bold text-xl sm:text-2xl uppercase tracking-wider text-black mt-4 mb-2">
            {{ $isArabicStore ? 'انقطع الاتصال بالإنترنت' : 'Connection Lost' }}
        </h2>
        
        <p class="font-sans text-xs sm:text-sm text-black/70 max-w-md mx-auto leading-relaxed mb-6">
            {{ $isArabicStore 
                ? 'تعذر الوصول إلى الخادم. يرجى التحقق من اتصالك بالإنترنت وسنعيد ربطك فور استقرار الشبكة.' 
                : "We're having trouble reaching the server. Please check your connection and try again." }}
        </p>

        {{-- Action Buttons --}}
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <button type="button" 
                    id="btn-retry-connection"
                    class="btn-luxury w-full sm:w-auto px-6 py-3.5 text-xs font-editorial font-bold uppercase tracking-[0.2em] flex items-center justify-center gap-2 cursor-pointer">
                <svg id="retry-spinner" class="w-4 h-4 animate-spin hidden" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span id="retry-btn-text">{{ $isArabicStore ? 'إعادة المحاولة الآن' : '↻ Retry Connection' }}</span>
            </button>

            <button type="button" 
                    id="btn-dismiss-offline"
                    class="btn-luxury-outline w-full sm:w-auto px-5 py-3 text-xs font-editorial font-bold uppercase tracking-[0.2em] cursor-pointer">
                {{ $isArabicStore ? 'متابعة التصفح' : 'Continue' }}
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    const isArabic = {{ $isArabicStore ? 'true' : 'false' }};
    const overlay = document.getElementById('atelier-offline-overlay');
    const card = document.getElementById('atelier-offline-card');
    const retryBtn = document.getElementById('btn-retry-connection');
    const dismissBtn = document.getElementById('btn-dismiss-offline');
    const retrySpinner = document.getElementById('retry-spinner');
    const retryBtnText = document.getElementById('retry-btn-text');

    function showOfflineScreen() {
        if (!overlay) return;
        overlay.classList.remove('opacity-0', 'pointer-events-none');
        overlay.classList.add('opacity-100', 'pointer-events-auto');
        if (card) {
            card.classList.remove('scale-95');
            card.classList.add('scale-100');
        }
        overlay.setAttribute('aria-hidden', 'false');
    }

    function hideOfflineScreen() {
        if (!overlay) return;
        overlay.classList.remove('opacity-100', 'pointer-events-auto');
        overlay.classList.add('opacity-0', 'pointer-events-none');
        if (card) {
            card.classList.remove('scale-100');
            card.classList.add('scale-95');
        }
        overlay.setAttribute('aria-hidden', 'true');
    }

    async function checkConnection() {
        if (retrySpinner) retrySpinner.classList.remove('hidden');
        if (retryBtnText) retryBtnText.textContent = isArabic ? 'جارِ الفحص...' : 'Checking...';

        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 4000);
            const response = await fetch('/manifest.json?_t=' + Date.now(), {
                method: 'HEAD',
                cache: 'no-store',
                signal: controller.signal
            });
            clearTimeout(timeoutId);

            if (response.ok || response.type === 'opaque') {
                hideOfflineScreen();
            } else {
                throw new Error('Unreachable');
            }
        } catch (e) {
            if (retryBtnText) retryBtnText.textContent = isArabic ? 'لم يتم الاتصال بعد — إعادة...' : 'Still Offline, Retrying...';
            setTimeout(() => {
                if (retryBtnText) retryBtnText.textContent = isArabic ? 'إعادة المحاولة الآن' : '↻ Retry Connection';
            }, 1800);
        } finally {
            if (retrySpinner) retrySpinner.classList.add('hidden');
        }
    }

    if (retryBtn) {
        retryBtn.addEventListener('click', () => {
            if (navigator.onLine) {
                window.location.reload();
            } else {
                checkConnection();
            }
        });
    }

    if (dismissBtn) {
        dismissBtn.addEventListener('click', hideOfflineScreen);
    }

    window.addEventListener('offline', showOfflineScreen);
    window.addEventListener('online', hideOfflineScreen);

    if (!navigator.onLine) {
        showOfflineScreen();
    }

    // Developer Test Shortcut: Press Ctrl + Shift + O
    window.addEventListener('keydown', (e) => {
        if (e.ctrlKey && e.shiftKey && (e.key === 'O' || e.key === 'o')) {
            e.preventDefault();
            if (overlay && overlay.classList.contains('opacity-100')) {
                hideOfflineScreen();
            } else {
                showOfflineScreen();
            }
        }
    });
})();
</script>
