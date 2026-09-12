<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('wishlist', {
            items: [],
            isAuth: {{ Auth::check() ? 'true' : 'false' }},

            init() {
                if (this.isAuth) {
                    const localItems = JSON.parse(localStorage.getItem('atelier_wishlist_ids') || '[]');
                    if (localItems.length > 0) {
                        fetch('{{ route('wishlist.sync') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ product_ids: localItems })
                        }).then(r => r.json()).then(data => {
                            if (data.wishlist_ids) {
                                this.items = data.wishlist_ids.map(id => Number(id));
                                localStorage.removeItem('atelier_wishlist_ids');
                            }
                        }).catch(() => {});
                    } else {
                        fetch('{{ route('wishlist.ids') }}', {
                            headers: { 'Accept': 'application/json' }
                        }).then(r => r.json()).then(data => {
                            if (data.ids) {
                                this.items = data.ids.map(id => Number(id));
                            }
                        }).catch(() => {});
                    }
                } else {
                    const saved = JSON.parse(localStorage.getItem('atelier_wishlist_ids') || '[]');
                    this.items = saved.map(id => Number(id));
                }
            },

            has(id) {
                return this.items.includes(Number(id));
            },

            async toggle(id) {
                const numId = Number(id);
                const isAdding = !this.has(numId);
                if (!isAdding) {
                    this.items = this.items.filter(i => i !== numId);
                } else {
                    this.items.push(numId);
                }

                const msg = isAdding 
                    ? '{{ ($settings['storefront_lang'] ?? 'en') === 'ar' ? 'تم حفظ القطعة في المفضلة' : 'Saved to Wishlist' }}'
                    : '{{ ($settings['storefront_lang'] ?? 'en') === 'ar' ? 'تمت الإزالة من المفضلة' : 'Removed from Wishlist' }}';
                window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: msg } }));

                if (!this.isAuth) {
                    localStorage.setItem('atelier_wishlist_ids', JSON.stringify(this.items));
                    window.dispatchEvent(new CustomEvent('wishlist-changed', { detail: { items: this.items } }));
                    return;
                }

                try {
                    const res = await fetch('{{ route('wishlist.toggle') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ product_id: numId })
                    });
                    const data = await res.json();
                    if (data.wishlist_ids) {
                        this.items = data.wishlist_ids.map(i => Number(i));
                    }
                    window.dispatchEvent(new CustomEvent('wishlist-changed', { detail: { items: this.items } }));
                } catch (e) {
                    console.error('Wishlist sync error', e);
                }
            }
        });
    });
</script>
