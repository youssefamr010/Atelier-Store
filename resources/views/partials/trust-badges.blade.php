@php
    $trustEnabled = ($settings['trust_badges_enabled'] ?? '1') === '1';
    $isAr = ($settings['storefront_lang'] ?? 'en') === 'ar';

    $badge1Icon = $settings['trust_badge_1_icon'] ?? 'truck';
    $badge1Title = $settings['trust_badge_1_title'] ?? ($isAr ? 'شحن سريع لجميع المحافظات' : 'Express Delivery Nationwide');
    $badge1Sub = $settings['trust_badge_1_sub'] ?? ($isAr ? 'توصيل خلال ٢-٤ أيام عمل' : 'Fast 2-4 business days dispatch');

    $badge2Icon = $settings['trust_badge_2_icon'] ?? 'return-arrow';
    $badge2Title = $settings['trust_badge_2_title'] ?? ($isAr ? 'استبدال واسترجاع سهل' : '14-Day Seamless Return');
    $badge2Sub = $settings['trust_badge_2_sub'] ?? ($isAr ? 'خلال ١٤ يوماً من الاستلام' : 'Hassle-free doorstep exchanges');

    $badge3Icon = $settings['trust_badge_3_icon'] ?? 'shield';
    $badge3Title = $settings['trust_badge_3_title'] ?? ($isAr ? 'منتجات أصلية ومضمونة' : '100% Guaranteed Luxury');
    $badge3Sub = $settings['trust_badge_3_sub'] ?? ($isAr ? 'خامات فاخرة وصناعة راقية' : 'Mastercrafted premium materials');
@endphp

@if($trustEnabled)
<div class="border-2 border-black bg-white p-4 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] space-y-3">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 divide-y sm:divide-y-0 sm:divide-x divide-black/10 {{ $isAr ? 'sm:divide-x-reverse' : '' }}">
        <!-- Badge 1 -->
        <div class="flex items-center gap-3 pt-2 sm:pt-0 sm:px-2 first:pt-0 first:px-0">
            <div class="w-8 h-8 rounded-full border border-black flex items-center justify-center bg-[#F8F7F3] shrink-0">
                <x-icon :name="$badge1Icon" class="w-4 h-4 text-black" />
            </div>
            <div>
                <span class="font-editorial font-bold text-[11px] uppercase tracking-wider text-black block leading-tight">{{ $badge1Title }}</span>
                <span class="text-[9px] font-sans text-black/60 block">{{ $badge1Sub }}</span>
            </div>
        </div>

        <!-- Badge 2 -->
        <div class="flex items-center gap-3 pt-2 sm:pt-0 sm:px-2">
            <div class="w-8 h-8 rounded-full border border-black flex items-center justify-center bg-[#F8F7F3] shrink-0">
                <x-icon :name="$badge2Icon" class="w-4 h-4 text-black" />
            </div>
            <div>
                <span class="font-editorial font-bold text-[11px] uppercase tracking-wider text-black block leading-tight">{{ $badge2Title }}</span>
                <span class="text-[9px] font-sans text-black/60 block">{{ $badge2Sub }}</span>
            </div>
        </div>

        <!-- Badge 3 -->
        <div class="flex items-center gap-3 pt-2 sm:pt-0 sm:px-2">
            <div class="w-8 h-8 rounded-full border border-black flex items-center justify-center bg-[#F8F7F3] shrink-0">
                <x-icon :name="$badge3Icon" class="w-4 h-4 text-black" />
            </div>
            <div>
                <span class="font-editorial font-bold text-[11px] uppercase tracking-wider text-black block leading-tight">{{ $badge3Title }}</span>
                <span class="text-[9px] font-sans text-black/60 block">{{ $badge3Sub }}</span>
            </div>
        </div>
    </div>
</div>
@endif
