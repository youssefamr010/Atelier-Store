@php
    $newsletterEnabled = ($settings['newsletter_popup_enabled'] ?? '1') === '1';
    $delaySec = (int)($settings['newsletter_popup_delay_sec'] ?? 5);
    $promoCode = $settings['newsletter_discount_code'] ?? 'WELCOME10';
    $popupTitle = $settings['newsletter_popup_title'] ?? ($isArabicStore ? 'انضم إلى مجتمع Atelier الفاخر' : 'Join the Atelier Private Circle');
    $popupSub = $settings['newsletter_popup_subtitle'] ?? ($isArabicStore ? 'اشترك الآن واحصل على كود خصم 10% فوري على أول طلب، بالإضافة إلى وصول حصري لأحدث القطع.' : 'Subscribe to receive an exclusive 10% welcome privilege code and early access to bespoke releases.');
@endphp

@if($newsletterEnabled)
<div 
    x-data="{ 
        open: false, 
        email: '', 
        submitting: false, 
        success: false, 
        code: '{{ $promoCode }}', 
        copied: false,
        errorMessage: '',
        init() {
            if (!localStorage.getItem('atelier_newsletter_dismissed')) {
                setTimeout(() => {
                    if (!localStorage.getItem('atelier_newsletter_dismissed')) {
                        this.open = true;
                    }
                }, {{ $delaySec * 1000 }});
            }
        },
        dismiss() {
            this.open = false;
            localStorage.setItem('atelier_newsletter_dismissed', 'true');
        },
        copyCode() {
            navigator.clipboard.writeText(this.code).then(() => {
                this.copied = true;
                setTimeout(() => { this.copied = false; }, 3000);
            });
        },
        async submitNewsletter() {
            if (!this.email || !this.email.includes('@')) return;
            this.submitting = true;
            this.errorMessage = '';
            try {
                const res = await fetch('{{ route('newsletter.subscribe') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']')?.content || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email: this.email })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.code = data.promo_code || '{{ $promoCode }}';
                    this.success = true;
                    localStorage.setItem('atelier_newsletter_dismissed', 'true');
                } else {
                    this.errorMessage = data.message || '{{ $isArabicStore ? 'حدث خطأ، يرجى المحاولة لاحقاً.' : 'An error occurred. Please try again.' }}';
                }
            } catch (err) {
                this.errorMessage = '{{ $isArabicStore ? 'تعذر الاتصال بالخادم.' : 'Could not connect to server.' }}';
            } finally {
                this.submitting = false;
            }
        }
    }"
    x-show="open" 
    x-cloak
    class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs transition-opacity duration-300"
    style="display: none;"
    @keydown.escape.window="dismiss()"
>
    <div 
        @click.away="dismiss()"
        class="bg-[#F8F7F3] border-2 border-black max-w-lg w-full p-6 sm:p-8 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] relative text-center space-y-5"
    >
        <!-- Close Button -->
        <button 
            type="button" 
            @click="dismiss()"
            class="absolute top-4 right-4 text-black hover:opacity-60 transition-opacity p-1 cursor-pointer"
            aria-label="Close"
        >
            <x-icon name="close" class="w-5 h-5" />
        </button>

        <div class="mx-auto w-12 h-12 rounded-full border border-black flex items-center justify-center bg-white shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
            <x-icon name="mail" class="w-6 h-6 text-black" />
        </div>

        <template x-if="!success">
            <div class="space-y-4">
                <div class="space-y-2">
                    <span class="text-[10px] font-editorial font-bold uppercase tracking-[0.25em] text-black/60 block">ATELIER PRIVILEGE</span>
                    <h3 class="font-display text-2xl sm:text-3xl text-black leading-tight">{{ $popupTitle }}</h3>
                    <p class="text-xs font-sans text-black/75 leading-relaxed max-w-md mx-auto">{{ $popupSub }}</p>
                </div>

                <form @submit.prevent="submitNewsletter()" class="space-y-3 pt-2">
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input 
                            type="email" 
                            x-model="email" 
                            required 
                            placeholder="{{ $isArabicStore ? 'أدخل بريدك الإلكتروني...' : 'Enter your email address...' }}"
                            class="flex-1 bg-white border-2 border-black px-4 py-3 text-xs font-sans placeholder:text-black/40 focus:outline-none shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]"
                        >
                        <button 
                            type="submit" 
                            :disabled="submitting"
                            class="btn-luxury px-6 py-3 text-xs font-editorial font-bold uppercase tracking-wider whitespace-nowrap cursor-pointer disabled:opacity-50"
                        >
                            <span x-show="!submitting">{{ $isArabicStore ? 'تفعيل الخصم' : 'Claim 10% Off' }}</span>
                            <span x-show="submitting">...</span>
                        </button>
                    </div>
                    <template x-if="errorMessage">
                        <p class="text-[11px] text-red-600 font-bold" x-text="errorMessage"></p>
                    </template>
                </form>

                <p class="text-[10px] text-black/40 font-mono">
                    {{ $isArabicStore ? 'نحترم خصوصيتك. يمكنك إلغاء الاشتراك في أي وقت.' : 'We respect your privacy. Unsubscribe at any time.' }}
                </p>
            </div>
        </template>

        <template x-if="success">
            <div class="space-y-5 py-2">
                <div class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-300 px-3 py-1 rounded-full">
                    <span>✓</span>
                    <span>{{ $isArabicStore ? 'تم الاشتراك بنجاح!' : 'Successfully Subscribed!' }}</span>
                </div>

                <div class="space-y-1">
                    <h3 class="font-display text-2xl text-black">{{ $isArabicStore ? 'أهلاً بك في Atelier' : 'Welcome to Atelier' }}</h3>
                    <p class="text-xs text-black/75">{{ $isArabicStore ? 'استخدم كود الخصم التالي عند إتمام الطلب للحصول على خصم 10%:' : 'Use this exclusive promo code at checkout for 10% off your order:' }}</p>
                </div>

                <div class="bg-white border-2 border-dashed border-black p-4 flex items-center justify-between gap-3 shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
                    <span class="font-mono font-black text-xl tracking-widest text-black" x-text="code"></span>
                    <button 
                        type="button" 
                        @click="copyCode()"
                        class="bg-black text-white hover:bg-neutral-800 px-4 py-2 text-xs font-editorial font-bold uppercase tracking-wider transition-all cursor-pointer"
                    >
                        <span x-show="!copied">{{ $isArabicStore ? 'نسخ الكود' : 'Copy Code' }}</span>
                        <span x-show="copied" class="text-emerald-400">{{ $isArabicStore ? 'تم النسخ ✓' : 'Copied ✓' }}</span>
                    </button>
                </div>

                <button 
                    type="button" 
                    @click="dismiss()"
                    class="text-xs font-editorial font-bold uppercase tracking-wider text-black underline hover:opacity-70 cursor-pointer pt-2"
                >
                    {{ $isArabicStore ? 'متابعة التسوق' : 'Continue Shopping' }}
                </button>
            </div>
        </template>
    </div>
</div>
@endif
