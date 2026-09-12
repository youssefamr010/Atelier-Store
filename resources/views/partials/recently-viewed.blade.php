{{-- Recently Viewed Products Strip --}}
@php
    $isArabicRv = ($settings['storefront_lang'] ?? 'en') === 'ar';
@endphp
<section 
    x-data="{
        recentItems: [],
        init() {
            try {
                // Purge deprecated v1 cache containing deleted/dummy products
                if (localStorage.getItem('atelier_recently_viewed')) {
                    localStorage.removeItem('atelier_recently_viewed');
                }
                const stored = JSON.parse(localStorage.getItem('atelier_recently_viewed_v2') || '[]');
                const currentPath = window.location.pathname;
                // Only keep items that have valid title and a non-empty image and not on current product page
                this.recentItems = stored.filter(item => item && item.id && item.title && item.image && item.url !== currentPath).slice(0, 4);
            } catch(e) {
                this.recentItems = [];
            }
        },
        removeBrokenItem(id) {
            this.recentItems = this.recentItems.filter(item => item.id !== id);
            try {
                const stored = JSON.parse(localStorage.getItem('atelier_recently_viewed_v2') || '[]');
                const updated = stored.filter(item => item.id !== id);
                localStorage.setItem('atelier_recently_viewed_v2', JSON.stringify(updated));
            } catch(e) {}
        }
    }" 
    x-show="recentItems.length > 0" 
    x-cloak 
    class="border-t border-black/10 py-10 bg-[#FAFAFA]"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 space-y-6">
        <div class="flex items-center justify-between border-b border-black/10 pb-3">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-2 bg-black inline-block"></span>
                <h3 class="font-editorial font-bold text-xs uppercase tracking-[0.2em] text-black">
                    {{ $isArabicRv ? 'شاهدتها مؤخراً' : 'Recently Viewed' }}
                </h3>
            </div>
            <span class="text-[10px] font-mono text-black/40 uppercase">Your browsing history</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            <template x-for="item in recentItems" :key="item.id">
                <a 
                    :href="item.url" 
                    class="group border-2 border-black bg-white p-2.5 shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] hover:shadow-[5px_5px_0px_0px_rgba(0,0,0,1)] hover:-translate-y-0.5 transition-all flex flex-col justify-between"
                >
                    <div class="aspect-square border border-black/10 bg-[#F5F5F0] overflow-hidden mb-2 p-1 flex items-center justify-center">
                        <img :src="item.image" :alt="item.title" @error="removeBrokenItem(item.id)" class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-200">
                    </div>
                    <div>
                        <h4 class="font-editorial font-bold text-xs uppercase tracking-tight text-black truncate group-hover:underline" x-text="item.title"></h4>
                        <div class="flex items-center justify-between mt-1 text-[11px] font-mono">
                            <span class="font-bold text-black" x-text="item.price"></span>
                            <span class="text-[9px] font-editorial uppercase text-black/50 group-hover:text-black">View →</span>
                        </div>
                    </div>
                </a>
            </template>
        </div>
    </div>
</section>
