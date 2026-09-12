@php
    $isAr = ($settings['storefront_lang'] ?? 'en') === 'ar';
    $reviews = $product->approvedReviews()->with('user')->latest()->get();
    $avgRating = $product->averageRating();
    $totalReviews = $reviews->count();

    $ratingCounts = [
        5 => $reviews->where('rating', 5)->count(),
        4 => $reviews->where('rating', 4)->count(),
        3 => $reviews->where('rating', 3)->count(),
        2 => $reviews->where('rating', 2)->count(),
        1 => $reviews->where('rating', 1)->count(),
    ];
@endphp

<section id="reviews-section" class="border-t-2 border-black bg-white py-12 lg:py-16" x-data="{
    reviewModalOpen: false,
    selectedRating: 5,
    hoverRating: 0,
    reviewTitle: '',
    reviewComment: '',
    submitting: false,
    reviewSubmitted: false,
    reviewError: '',
    async submitReview() {
        if (!this.reviewComment || this.reviewComment.length < 5) return;
        this.submitting = true;
        this.reviewError = '';
        try {
            const res = await fetch('{{ route('products.reviews.store', ['id' => $product->id]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']')?.content || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    rating: this.selectedRating,
                    title: this.reviewTitle,
                    comment: this.reviewComment
                })
            });
            const data = await res.json();
            if (res.ok && data.success) {
                this.reviewSubmitted = true;
            } else {
                this.reviewError = data.message || '{{ $isAr ? 'تعذر إرسال التقييم. تأكد من إتمام واستلام طلب سابق لهذا المنتج.' : 'Could not submit review. Please ensure you have a delivered order for this product.' }}';
            }
        } catch (e) {
            this.reviewError = '{{ $isAr ? 'حدث خطأ في الاتصال.' : 'A network error occurred.' }}';
        } finally {
            this.submitting = false;
        }
    }
}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 space-y-10">

        <!-- Section Header & Rating Breakdown -->
        <div class="border-b-2 border-black pb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-8">
            <div>
                <span class="text-[10px] font-editorial font-bold uppercase tracking-[0.25em] text-black/60 block mb-1">
                    {{ $isAr ? 'آراء وتجارب العملاء الموثقة' : 'CLIENT EXPERIENCES & FEEDBACK' }}
                </span>
                <h2 class="font-display text-fluid-title uppercase text-black" style="font-size: clamp(1.4rem, 4vw, 2.5rem);">
                    {{ $isAr ? 'تقييمات المنتج' : 'Verified Reviews' }}
                </h2>
                <div class="flex items-center gap-3 mt-2">
                    <div class="flex items-center text-amber-500">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-5 h-5 {{ $i <= round($avgRating) ? 'fill-amber-400 text-amber-400' : 'fill-gray-200 text-gray-200' }}" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        @endfor
                    </div>
                    <span class="font-mono font-black text-xl text-black">{{ $avgRating > 0 ? number_format($avgRating, 1) : '5.0' }}</span>
                    <span class="text-xs font-mono text-gray-500">/ 5.0 ({{ $totalReviews }} {{ $isAr ? 'تقييم' : 'reviews' }})</span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                @auth
                    <button 
                        type="button" 
                        @click="reviewModalOpen = true"
                        class="btn-luxury px-6 py-3.5 text-xs font-editorial font-bold uppercase tracking-wider flex items-center gap-2 cursor-pointer"
                    >
                        <span>★</span>
                        <span>{{ $isAr ? 'كتابة تقييم للمنتج' : 'Write a Verified Review' }}</span>
                    </button>
                @else
                    <a 
                        href="{{ route('account') }}?redirect={{ urlencode(url()->current()) }}"
                        class="btn-luxury-outline px-6 py-3.5 text-xs font-editorial font-bold uppercase tracking-wider block text-center"
                    >
                        {{ $isAr ? 'تسجيل الدخول لكتابة تقييم' : 'Sign in to write a review' }}
                    </a>
                @endauth
            </div>
        </div>

        <!-- Rating Distribution Bars -->
        @if($totalReviews > 0)
            <div class="max-w-md bg-[#F8F7F3] border-2 border-black p-4 space-y-2 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                @for($s = 5; $s >= 1; $s--)
                    @php 
                        $cnt = $ratingCounts[$s] ?? 0;
                        $pct = $totalReviews > 0 ? round(($cnt / $totalReviews) * 100) : 0;
                    @endphp
                    <div class="flex items-center gap-3 text-xs">
                        <span class="w-12 font-mono font-bold text-gray-700 shrink-0">{{ $s }} ★</span>
                        <div class="flex-1 bg-gray-200 h-2 rounded-full overflow-hidden border border-black/20">
                            <div class="bg-amber-400 h-full rounded-full" style="width: {{ $pct }}%;"></div>
                        </div>
                        <span class="w-8 text-right font-mono text-[11px] text-gray-500">{{ $cnt }}</span>
                    </div>
                @endfor
            </div>
        @endif

        <!-- Reviews Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($reviews as $rev)
                <div class="bg-white border-2 border-black p-5 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] flex flex-col justify-between space-y-4">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center text-amber-500">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-3.5 h-3.5 {{ $i <= $rev->rating ? 'fill-amber-400 text-amber-400' : 'fill-gray-200 text-gray-200' }}" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @endfor
                            </div>
                            <span class="text-[10px] font-mono text-gray-400">{{ $rev->created_at->format('M d, Y') }}</span>
                        </div>

                        @if(!empty($rev->title))
                            <h4 class="font-editorial font-bold text-sm text-black leading-snug">{{ $rev->title }}</h4>
                        @endif

                        <p class="font-sans text-xs text-black/80 leading-relaxed">{{ $rev->comment }}</p>
                    </div>

                    <div class="border-t border-black/10 pt-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-black text-white text-[10px] font-bold flex items-center justify-center font-mono">
                                {{ strtoupper(substr($rev->user?->name ?: 'Client', 0, 1)) }}
                            </span>
                            <span class="font-bold text-xs text-black">{{ $rev->user?->name ?: 'Verified Client' }}</span>
                        </div>

                        @if($rev->is_verified_purchase)
                            <span class="inline-flex items-center gap-1 text-[9px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 border border-emerald-300 px-2 py-0.5 rounded-full">
                                <span>✓</span>
                                <span>{{ $isAr ? 'مشتري موثق' : 'Verified Purchase' }}</span>
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full border-2 border-dashed border-black/20 p-8 text-center bg-[#F8F7F3] space-y-2">
                    <span class="text-2xl">✨</span>
                    <h4 class="font-editorial font-bold text-sm uppercase tracking-wider text-black">
                        {{ $isAr ? 'كن أول من يقيّم هذه القطعة' : 'Be the first to review this piece' }}
                    </h4>
                    <p class="text-xs font-sans text-black/60 max-w-sm mx-auto">
                        {{ $isAr ? 'التقييمات مقتصرة على العملاء الذين استلموا طلبهم لضمان تجربة حقيقية وموثوقة بنسبة ١٠٠٪.' : 'Reviews are exclusively open to clients who have ordered and received this piece.' }}
                    </p>
                </div>
            @endforelse
        </div>

    </div>

    <!-- Review Submission Modal -->
    <div 
        x-show="reviewModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
        @keydown.escape.window="reviewModalOpen = false"
    >
        <div 
            @click.away="reviewModalOpen = false"
            class="bg-white border-2 border-black max-w-lg w-full p-6 sm:p-8 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] space-y-5 relative"
        >
            <button 
                type="button" 
                @click="reviewModalOpen = false"
                class="absolute top-4 right-4 text-black hover:opacity-60 font-mono font-bold text-lg"
            >
                &times;
            </button>

            <template x-if="!reviewSubmitted">
                <div class="space-y-4">
                    <div class="border-b pb-3">
                        <span class="text-[10px] font-editorial font-bold uppercase tracking-[0.25em] text-black/60 block">VERIFIED CLIENT REVIEW</span>
                        <h3 class="font-display text-2xl text-black">{{ $isAr ? 'تقييم ' . $product->title : 'Review ' . $product->title }}</h3>
                        <p class="text-[11px] text-gray-500 mt-0.5">{{ $isAr ? 'شارك رأيك الصادق حول جودة الخامات والتصميم.' : 'Share your authentic experience with this piece.' }}</p>
                    </div>

                    <form @submit.prevent="submitReview()" class="space-y-4">
                        <!-- Star Selection -->
                        <div>
                            <label class="block text-xs font-editorial font-bold uppercase tracking-wider text-black mb-1.5">{{ $isAr ? 'التقييم العام' : 'Rating' }}</label>
                            <div class="flex items-center gap-2">
                                <template x-for="star in [1,2,3,4,5]" :key="star">
                                    <button 
                                        type="button" 
                                        @click="selectedRating = star"
                                        @mouseenter="hoverRating = star"
                                        @mouseleave="hoverRating = 0"
                                        class="p-1 cursor-pointer transition-transform hover:scale-110"
                                    >
                                        <svg class="w-7 h-7" :class="(hoverRating ? star <= hoverRating : star <= selectedRating) ? 'fill-amber-400 text-amber-400' : 'fill-gray-200 text-gray-200'" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    </button>
                                </template>
                                <span class="font-mono font-bold text-sm ml-2 text-black" x-text="selectedRating + ' / 5'"></span>
                            </div>
                        </div>

                        <!-- Review Title -->
                        <div>
                            <label class="block text-xs font-editorial font-bold uppercase tracking-wider text-black mb-1">{{ $isAr ? 'عنوان التقييم (اختياري)' : 'Headline' }}</label>
                            <input 
                                type="text" 
                                x-model="reviewTitle" 
                                placeholder="{{ $isAr ? 'مثال: خامات فاخرة وتفصيل دقيق' : 'e.g., Flawless leather and great feel' }}"
                                class="w-full border-2 border-black p-2.5 text-xs font-sans focus:outline-none"
                            >
                        </div>

                        <!-- Review Body -->
                        <div>
                            <label class="block text-xs font-editorial font-bold uppercase tracking-wider text-black mb-1">{{ $isAr ? 'تفاصيل التقييم' : 'Review Details' }}</label>
                            <textarea 
                                x-model="reviewComment" 
                                required 
                                rows="4" 
                                placeholder="{{ $isAr ? 'اكتب تجربتك بالتفصيل...' : 'Tell other clients what you loved about this piece...' }}"
                                class="w-full border-2 border-black p-3 text-xs font-sans leading-relaxed focus:outline-none"
                            ></textarea>
                        </div>

                        <template x-if="reviewError">
                            <div class="p-3 bg-red-50 border border-red-300 text-red-700 text-xs font-bold leading-relaxed">
                                <span x-text="reviewError"></span>
                            </div>
                        </template>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button 
                                type="button" 
                                @click="reviewModalOpen = false"
                                class="border border-black px-4 py-2.5 text-xs font-editorial font-bold uppercase tracking-wider"
                            >
                                {{ $isAr ? 'إلغاء' : 'Cancel' }}
                            </button>
                            <button 
                                type="submit" 
                                :disabled="submitting"
                                class="btn-luxury px-6 py-2.5 text-xs font-editorial font-bold uppercase tracking-wider disabled:opacity-50"
                            >
                                <span x-show="!submitting">{{ $isAr ? 'إرسال التقييم' : 'Submit Review' }}</span>
                                <span x-show="submitting">...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </template>

            <template x-if="reviewSubmitted">
                <div class="text-center py-6 space-y-4">
                    <div class="w-12 h-12 rounded-full bg-emerald-100 border-2 border-emerald-500 text-emerald-700 flex items-center justify-center mx-auto text-xl font-bold">
                        ✓
                    </div>
                    <h3 class="font-display text-2xl text-black">{{ $isAr ? 'شكراً لتقييمك!' : 'Thank You!' }}</h3>
                    <p class="text-xs text-black/75 max-w-sm mx-auto leading-relaxed">
                        {{ $isAr ? 'تم استلام تقييمك وسوف يظهر بعد مراجعته من قبل إدارة الجودة.' : 'Your review has been submitted and will be visible on the store after quality moderation.' }}
                    </p>
                    <button 
                        type="button" 
                        @click="reviewModalOpen = false; reviewSubmitted = false"
                        class="btn-luxury px-6 py-2.5 text-xs font-editorial font-bold uppercase tracking-wider mt-2"
                    >
                        {{ $isAr ? 'تم' : 'Done' }}
                    </button>
                </div>
            </template>
        </div>
    </div>
</section>
