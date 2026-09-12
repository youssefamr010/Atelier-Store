@extends('layouts.admin')

@section('title', 'Storefront Features & Customer Experience')

@section('content')
<div class="space-y-8" x-data="{ activeTab: 'trust', adjustPointsModal: false, selectedUserId: null, selectedUserName: '' }">

    <!-- Header -->
    <div class="border-b-2 border-black pb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span class="text-2xl"><x-icon name="shield" class="w-6 h-6 inline-block" /></span>
                <h1 class="text-2xl font-black uppercase tracking-tight">Storefront Features & Customer Experience</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1">
                إدارة مميزات تجربة العميل (شارات الثقة، نقاط الولاء، نافذة النشرة البريدية، مؤشر نفاذ الكمية، وقصة البراند).
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button 
                type="submit" 
                form="storefront-features-form"
                class="bg-black text-white hover:bg-gray-800 border-2 border-black px-6 py-2.5 text-xs font-bold uppercase tracking-wider shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-transform active:translate-y-0.5 flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>حفظ التغييرات</span>
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white border-2 border-black p-4 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
            <span class="text-[10px] font-mono uppercase tracking-widest text-gray-500 block">Newsletter Subscribers</span>
            <span class="text-2xl font-black text-black font-mono block mt-1">{{ number_format($subscribersCount) }}</span>
            <span class="text-[10px] text-gray-400 mt-1 block">عملاء مسجلين بالنشرة البريدية</span>
        </div>

        <div class="bg-white border-2 border-black p-4 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
            <span class="text-[10px] font-mono uppercase tracking-widest text-gray-500 block">Total Loyalty Points Awarded</span>
            <span class="text-2xl font-black text-amber-600 font-mono block mt-1">{{ number_format($totalPointsAwarded) }}</span>
            <span class="text-[10px] text-gray-400 mt-1 block">إجمالي النقاط المكتسبة من الطلبات</span>
        </div>

        <div class="bg-white border-2 border-black p-4 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
            <span class="text-[10px] font-mono uppercase tracking-widest text-gray-500 block">Points Redeemed (Discounts)</span>
            <span class="text-2xl font-black text-emerald-600 font-mono block mt-1">{{ number_format($totalPointsRedeemed) }}</span>
            <span class="text-[10px] text-gray-400 mt-1 block">نقاط تم استخدامها كخصومات</span>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="flex border-b-2 border-black gap-2 overflow-x-auto">
        <button 
            type="button" 
            @click="activeTab = 'trust'" 
            :class="activeTab === 'trust' ? 'bg-black text-white' : 'bg-white text-black hover:bg-gray-100'"
            class="px-5 py-3 text-xs font-black uppercase tracking-wider border-t-2 border-x-2 border-black -mb-[2px] transition-colors flex items-center gap-2 whitespace-nowrap"
        >
            <x-icon name="shield" class="w-4 h-4" />
            <span>1. شارات الثقة (Trust Badges)</span>
        </button>

        <button 
            type="button" 
            @click="activeTab = 'loyalty'" 
            :class="activeTab === 'loyalty' ? 'bg-black text-white' : 'bg-white text-black hover:bg-gray-100'"
            class="px-5 py-3 text-xs font-black uppercase tracking-wider border-t-2 border-x-2 border-black -mb-[2px] transition-colors flex items-center gap-2 whitespace-nowrap"
        >
            <x-icon name="gift" class="w-4 h-4" />
            <span>2. برنامج نقاط الولاء (Loyalty Points)</span>
        </button>

        <button 
            type="button" 
            @click="activeTab = 'newsletter'" 
            :class="activeTab === 'newsletter' ? 'bg-black text-white' : 'bg-white text-black hover:bg-gray-100'"
            class="px-5 py-3 text-xs font-black uppercase tracking-wider border-t-2 border-x-2 border-black -mb-[2px] transition-colors flex items-center gap-2 whitespace-nowrap"
        >
            <x-icon name="mail" class="w-4 h-4" />
            <span>3. النشرة البريدية (Newsletter Popup)</span>
        </button>

        <button 
            type="button" 
            @click="activeTab = 'urgency'" 
            :class="activeTab === 'urgency' ? 'bg-black text-white' : 'bg-white text-black hover:bg-gray-100'"
            class="px-5 py-3 text-xs font-black uppercase tracking-wider border-t-2 border-x-2 border-black -mb-[2px] transition-colors flex items-center gap-2 whitespace-nowrap"
        >
            <x-icon name="flame" class="w-4 h-4" />
            <span>4. نفاذ الكمية (Stock Urgency)</span>
        </button>

        <button 
            type="button" 
            @click="activeTab = 'story'" 
            :class="activeTab === 'story' ? 'bg-black text-white' : 'bg-white text-black hover:bg-gray-100'"
            class="px-5 py-3 text-xs font-black uppercase tracking-wider border-t-2 border-x-2 border-black -mb-[2px] transition-colors flex items-center gap-2 whitespace-nowrap"
        >
            <x-icon name="star" class="w-4 h-4" />
            <span>5. قصة البراند (Our Story)</span>
        </button>
    </div>

    <!-- Main Settings Form -->
    <form id="storefront-features-form" action="{{ route('admin.storefront-features.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- TAB 1: TRUST BADGES -->
        <div x-show="activeTab === 'trust'" class="space-y-6">
            <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-6">
                <div class="flex items-center justify-between border-b pb-4">
                    <div>
                        <h2 class="text-base font-black uppercase">إعدادات شارات الثقة والضمان (Trust Badges Strip)</h2>
                        <p class="text-xs text-gray-500">تظهر أسفل زر إضافة للسلة في صفحة المنتج وفي مرحلة إتمام الشراء.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="trust_badges_enabled" value="1" {{ ($settings['trust_badges_enabled'] ?? '1') === '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-black"></div>
                        <span class="mr-3 text-xs font-bold uppercase">تفعيل الشارات</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Badge 1 -->
                    <div class="border-2 border-gray-200 p-4 rounded bg-gray-50 space-y-3">
                        <div class="flex items-center gap-2">
                            <x-icon name="truck" class="w-5 h-5 text-black" />
                            <span class="text-xs font-black uppercase">الشارة الأولى (الشحن والتوصيل)</span>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-gray-700 mb-1">الأيقونة</label>
                            <select name="trust_badge_1_icon" class="w-full border-2 border-black p-2 text-xs">
                                <option value="truck" {{ ($settings['trust_badge_1_icon'] ?? 'truck') === 'truck' ? 'selected' : '' }}>Truck (شاحنة توصيل)</option>
                                <option value="shield" {{ ($settings['trust_badge_1_icon'] ?? '') === 'shield' ? 'selected' : '' }}>Shield (درع حماية)</option>
                                <option value="return-arrow" {{ ($settings['trust_badge_1_icon'] ?? '') === 'return-arrow' ? 'selected' : '' }}>Return Arrow (سهم استرجاع)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-gray-700 mb-1">العنوان الرئيسي</label>
                            <input type="text" name="trust_badge_1_title" value="{{ $settings['trust_badge_1_title'] ?? 'شحن سريع لجميع المحافظات' }}" class="w-full border-2 border-black p-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-gray-700 mb-1">النص الفرعي</label>
                            <input type="text" name="trust_badge_1_sub" value="{{ $settings['trust_badge_1_sub'] ?? 'توصيل خلال ٢-٤ أيام عمل' }}" class="w-full border-2 border-black p-2 text-xs">
                        </div>
                    </div>

                    <!-- Badge 2 -->
                    <div class="border-2 border-gray-200 p-4 rounded bg-gray-50 space-y-3">
                        <div class="flex items-center gap-2">
                            <x-icon name="return-arrow" class="w-5 h-5 text-black" />
                            <span class="text-xs font-black uppercase">الشارة الثانية (الاستبدال والاسترجاع)</span>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-gray-700 mb-1">الأيقونة</label>
                            <select name="trust_badge_2_icon" class="w-full border-2 border-black p-2 text-xs">
                                <option value="return-arrow" {{ ($settings['trust_badge_2_icon'] ?? 'return-arrow') === 'return-arrow' ? 'selected' : '' }}>Return Arrow (سهم استرجاع)</option>
                                <option value="shield" {{ ($settings['trust_badge_2_icon'] ?? '') === 'shield' ? 'selected' : '' }}>Shield (درع حماية)</option>
                                <option value="truck" {{ ($settings['trust_badge_2_icon'] ?? '') === 'truck' ? 'selected' : '' }}>Truck (شاحنة توصيل)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-gray-700 mb-1">العنوان الرئيسي</label>
                            <input type="text" name="trust_badge_2_title" value="{{ $settings['trust_badge_2_title'] ?? 'استبدال واسترجاع سهل' }}" class="w-full border-2 border-black p-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-gray-700 mb-1">النص الفرعي</label>
                            <input type="text" name="trust_badge_2_sub" value="{{ $settings['trust_badge_2_sub'] ?? 'خلال ١٤ يوماً من الاستلام' }}" class="w-full border-2 border-black p-2 text-xs">
                        </div>
                    </div>

                    <!-- Badge 3 -->
                    <div class="border-2 border-gray-200 p-4 rounded bg-gray-50 space-y-3">
                        <div class="flex items-center gap-2">
                            <x-icon name="shield" class="w-5 h-5 text-black" />
                            <span class="text-xs font-black uppercase">الشارة الثالثة (جودة أصلية ١٠٠٪)</span>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-gray-700 mb-1">الأيقونة</label>
                            <select name="trust_badge_3_icon" class="w-full border-2 border-black p-2 text-xs">
                                <option value="shield" {{ ($settings['trust_badge_3_icon'] ?? 'shield') === 'shield' ? 'selected' : '' }}>Shield (درع حماية)</option>
                                <option value="gift" {{ ($settings['trust_badge_3_icon'] ?? '') === 'gift' ? 'selected' : '' }}>Gift (هدية ومكافأة)</option>
                                <option value="star" {{ ($settings['trust_badge_3_icon'] ?? '') === 'star' ? 'selected' : '' }}>Star (نجمة تميز)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-gray-700 mb-1">العنوان الرئيسي</label>
                            <input type="text" name="trust_badge_3_title" value="{{ $settings['trust_badge_3_title'] ?? 'منتجات أصلية ومضمونة' }}" class="w-full border-2 border-black p-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-gray-700 mb-1">النص الفرعي</label>
                            <input type="text" name="trust_badge_3_sub" value="{{ $settings['trust_badge_3_sub'] ?? 'خامات فاخرة وصناعة راقية' }}" class="w-full border-2 border-black p-2 text-xs">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: LOYALTY PROGRAM -->
        <div x-show="activeTab === 'loyalty'" class="space-y-6">
            <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-6">
                <div class="flex items-center justify-between border-b pb-4">
                    <div>
                        <h2 class="text-base font-black uppercase">برنامج مكافآت ونقاط الولاء (Loyalty Points & Rewards)</h2>
                        <p class="text-xs text-gray-500">منح العملاء نقاطاً تلقائياً عند تسليم الطلبات واستبدالها بخصومات فورية أثناء الشراء.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="loyalty_enabled" value="1" {{ ($settings['loyalty_enabled'] ?? '1') === '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-black"></div>
                        <span class="mr-3 text-xs font-bold uppercase">تفعيل برنامج الولاء</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">معدل كسب النقاط (جنيه لكل نقطة)</label>
                        <div class="flex items-center">
                            <span class="bg-gray-100 border-2 border-l-0 border-black px-3 py-2 text-xs font-mono">1 نقطة لكل</span>
                            <input type="number" step="1" min="1" name="loyalty_earn_rate_egp" value="{{ $settings['loyalty_earn_rate_egp'] ?? '10' }}" class="w-full border-2 border-black p-2 text-xs font-mono font-bold">
                            <span class="bg-gray-100 border-2 border-r-0 border-black px-3 py-2 text-xs font-bold">ج.م</span>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">افتراضي: 10 ج.م = 1 نقطة (طلب بـ 1000 ج.م يمنح 100 نقطة عند التوصيل).</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">وحدة استبدال النقاط (Points Step)</label>
                        <div class="flex items-center">
                            <input type="number" step="10" min="10" name="loyalty_redeem_pts_unit" value="{{ $settings['loyalty_redeem_pts_unit'] ?? '100' }}" class="w-full border-2 border-black p-2 text-xs font-mono font-bold">
                            <span class="bg-gray-100 border-2 border-r-0 border-black px-3 py-2 text-xs font-bold">نقطة</span>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">الحد الأدنى أو مضاعفات الاستبدال (مثلاً كل 100 نقطة).</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">قيمة الخصم لكل وحدة استبدال</label>
                        <div class="flex items-center">
                            <input type="number" step="5" min="1" name="loyalty_redeem_discount_egp" value="{{ $settings['loyalty_redeem_discount_egp'] ?? '50' }}" class="w-full border-2 border-black p-2 text-xs font-mono font-bold">
                            <span class="bg-gray-100 border-2 border-r-0 border-black px-3 py-2 text-xs font-bold">ج.م خصم</span>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">افتراضي: 100 نقطة = 50 ج.م خصم مباشر في سلة المشتريات.</p>
                    </div>
                </div>

                <!-- Top Points Holders & Admin Adjust -->
                <div class="border-t-2 border-black pt-6 space-y-4">
                    <h3 class="text-xs font-black uppercase tracking-wider">أعلى العملاء امتلاكاً للنقاط وتعديل الأرصدة يدوياً</h3>
                    
                    <div class="overflow-x-auto border-2 border-black">
                        <table class="w-full text-right text-xs">
                            <thead class="bg-black text-white text-[11px] uppercase">
                                <tr>
                                    <th class="p-3">العميل</th>
                                    <th class="p-3">البريد الإلكتروني</th>
                                    <th class="p-3">رصيد النقاط الحالي</th>
                                    <th class="p-3">القيمة المعادلة كخصم</th>
                                    <th class="p-3 text-left">إجراء</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($topPointHolders as $holder)
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-3 font-bold">{{ $holder->name }}</td>
                                        <td class="p-3 font-mono text-gray-500">{{ $holder->email }}</td>
                                        <td class="p-3 font-mono font-black text-amber-600">{{ number_format((int)$holder->points_sum) }} pts</td>
                                        <td class="p-3 font-mono font-bold text-emerald-600">
                                            {{ number_format(floor(((int)$holder->points_sum / max(1, (int)($settings['loyalty_redeem_pts_unit'] ?? 100)))) * (int)($settings['loyalty_redeem_discount_egp'] ?? 50)) }} ج.م
                                        </td>
                                        <td class="p-3 text-left">
                                            <button 
                                                type="button" 
                                                @click="adjustPointsModal = true; selectedUserId = {{ $holder->id }}; selectedUserName = '{{ addslashes($holder->name) }}'"
                                                class="bg-gray-100 hover:bg-black hover:text-white border border-black px-3 py-1 text-[11px] font-bold uppercase transition-colors"
                                            >
                                                تعديل الرصيد
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-6 text-center text-gray-400">لا يوجد عملاء يمتلكون نقاطاً حالياً. تمنح النقاط تلقائياً عند اكتمال توصيل الطلب.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: NEWSLETTER POPUP -->
        <div x-show="activeTab === 'newsletter'" class="space-y-6">
            <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-6">
                <div class="flex items-center justify-between border-b pb-4">
                    <div>
                        <h2 class="text-base font-black uppercase">نافذة النشرة البريدية والعرض الترحيبي (Newsletter Popup)</h2>
                        <p class="text-xs text-gray-500">نافذة منبثقة بتصميم أنيق تظهر للزائر مع كود خصم فوري عند الاشتراك.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="newsletter_popup_enabled" value="1" {{ ($settings['newsletter_popup_enabled'] ?? '1') === '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-black"></div>
                        <span class="mr-3 text-xs font-bold uppercase">تفعيل النافذة</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">وقت الظهور بعد فتح المتجر (بالثواني)</label>
                        <input type="number" min="1" max="60" name="newsletter_popup_delay_sec" value="{{ $settings['newsletter_popup_delay_sec'] ?? '5' }}" class="w-full border-2 border-black p-2 text-xs font-mono font-bold">
                        <p class="text-[10px] text-gray-400 mt-1">يتم حفظ خيار الإغلاق في متصفح الزائر حتى لا تتكرر بشكل مزعج.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">كود الخصم الممنوح للمشترك الجديد</label>
                        <input type="text" name="newsletter_discount_code" value="{{ $settings['newsletter_discount_code'] ?? 'WELCOME10' }}" class="w-full border-2 border-black p-2 text-xs font-mono uppercase font-black">
                        <p class="text-[10px] text-gray-400 mt-1">يظهر كود الخصم فوراً للمشترك مع إمكانية نسخه بضغطة زر.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">العنوان الرئيسي للنافذة</label>
                        <input type="text" name="newsletter_popup_title" value="{{ $settings['newsletter_popup_title'] ?? 'انضم إلى مجتمع Atelier الفاخر' }}" class="w-full border-2 border-black p-2 text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">النص الترويجي للنافذة</label>
                        <input type="text" name="newsletter_popup_subtitle" value="{{ $settings['newsletter_popup_subtitle'] ?? 'اشترك الآن واحصل على خصم 10% فوري على طلبك الأول، بالإضافة إلى وصول حصري لأحدث التشكيلات.' }}" class="w-full border-2 border-black p-2 text-xs">
                    </div>
                </div>

                <!-- Recent Subscribers -->
                <div class="border-t-2 border-black pt-6 space-y-4">
                    <h3 class="text-xs font-black uppercase tracking-wider">آخر المشتركين في القائمة البريدية</h3>
                    <div class="overflow-x-auto border-2 border-black">
                        <table class="w-full text-right text-xs">
                            <thead class="bg-black text-white text-[11px] uppercase">
                                <tr>
                                    <th class="p-3">البريد الإلكتروني</th>
                                    <th class="p-3">كود الخصم الممنوح</th>
                                    <th class="p-3">تاريخ الاشتراك</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($recentSubscribers as $sub)
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-3 font-mono font-bold">{{ $sub->email }}</td>
                                        <td class="p-3 font-mono"><span class="bg-gray-100 border border-gray-300 px-2 py-0.5 rounded text-[11px]">{{ $sub->promo_code_given ?: 'WELCOME10' }}</span></td>
                                        <td class="p-3 font-mono text-gray-500">{{ $sub->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="p-6 text-center text-gray-400">لا يوجد مشتركون في النشرة حتى الآن.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 4: STOCK URGENCY -->
        <div x-show="activeTab === 'urgency'" class="space-y-6">
            <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-6">
                <div class="flex items-center justify-between border-b pb-4">
                    <div>
                        <h2 class="text-base font-black uppercase">مؤشر نفاذ الكمية وإلحاح الشراء (Low-Stock Urgency Badge)</h2>
                        <p class="text-xs text-gray-500">ينبه العميل عندما ينخفض مخزون المنتج/المقاس عن الحد المحدد لتحفيز إتمام الطلب سريعاً.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="urgency_indicator_enabled" value="1" {{ ($settings['urgency_indicator_enabled'] ?? '1') === '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-black"></div>
                        <span class="mr-3 text-xs font-bold uppercase">تفعيل المؤشر</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">الحد الأقصى للكمية المتبقية لإظهار التنبيه</label>
                        <div class="flex items-center">
                            <input type="number" min="1" max="20" name="urgency_stock_threshold" value="{{ $settings['urgency_stock_threshold'] ?? '5' }}" class="w-full border-2 border-black p-2 text-xs font-mono font-bold">
                            <span class="bg-gray-100 border-2 border-r-0 border-black px-3 py-2 text-xs font-bold">قطع أو أقل</span>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">يظهر التنبيه إذا كان المخزون المتاح أكبر من صفر وأقل أو يساوي هذه القيمة.</p>
                    </div>

                    <div class="border-2 border-dashed border-gray-300 p-4 rounded bg-gray-50 text-center">
                        <span class="text-[11px] font-bold text-gray-500 block mb-2">معاينة شكل الشارة في المتجر:</span>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-900 border border-amber-300 rounded-full text-xs font-bold animate-pulse">
                            <x-icon name="flame" class="w-3.5 h-3.5 text-amber-600" />
                            <span>متبقي ٣ قطع فقط في المخزون — اطلب الآن!</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 5: OUR STORY -->
        <div x-show="activeTab === 'story'" class="space-y-6">
            <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-6">
                <div class="flex items-center justify-between border-b pb-4">
                    <div>
                        <h2 class="text-base font-black uppercase">قصة وهوية البراند في الصفحة الرئيسية (Our Story / Brand Heritage)</h2>
                        <p class="text-xs text-gray-500">قسم أنيق ومميز يروي قصة Atelier وحرفية التصميم لبناء الثقة والارتباط بالبراند.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="homepage_story_enabled" value="1" {{ ($settings['homepage_story_enabled'] ?? '1') === '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-black"></div>
                        <span class="mr-3 text-xs font-bold uppercase">تفعيل قسم القصة</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-700 mb-1">العنوان الفرعي (Subtitle / Eyebrow)</label>
                            <input type="text" name="homepage_story_subtitle" value="{{ $settings['homepage_story_subtitle'] ?? 'حرفية وفخامة بلا مساومة' }}" class="w-full border-2 border-black p-2 text-xs font-bold">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-700 mb-1">العنوان الرئيسي (Title)</label>
                            <input type="text" name="homepage_story_title" value="{{ $settings['homepage_story_title'] ?? 'عن Atelier — فلسفة التصميم والأناقة الخالدة' }}" class="w-full border-2 border-black p-2 text-xs font-black">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-700 mb-1">نص القصة (Body Paragraph)</label>
                            <textarea name="homepage_story_body" rows="6" class="w-full border-2 border-black p-3 text-xs leading-relaxed">{{ $settings['homepage_story_body'] ?? 'انطلقت Atelier برؤية لإعادة تعريف الأزياء الراقية، حيث تلتقي أجود الخامات العالمية بأعلى مستويات الحرفية والتفصيل الدقيق. كل قطعة في تشكيلاتنا صُممت بعناية فائقة لتمنحك حضوراً فريداً وأناقة تدوم طويلاً.' }}</textarea>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">صورة قصة البراند</label>
                        @if(!empty($settings['homepage_story_image']))
                            <div class="border-2 border-black p-2 bg-gray-50">
                                <img src="{{ $settings['homepage_story_image'] }}" alt="Our Story" class="w-full h-48 object-cover">
                            </div>
                        @endif
                        <input type="file" name="homepage_story_image_file" accept="image/*" class="w-full border-2 border-black p-2 text-xs bg-white">
                        <p class="text-[10px] text-gray-400">يفضل صورة عمودية أو مربعة عالية الجودة تعكس أجواء المشغل أو المواد الخام.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Adjust Points Modal -->
    <div x-show="adjustPointsModal" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white border-2 border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] max-w-md w-full p-6 space-y-4" @click.away="adjustPointsModal = false">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-sm font-black uppercase">تعديل رصيد نقاط العميل</h3>
                <button type="button" @click="adjustPointsModal = false" class="text-gray-400 hover:text-black font-mono font-bold text-lg">&times;</button>
            </div>

            <p class="text-xs text-gray-600">
                تعديل رصيد العميل: <span class="font-bold text-black" x-text="selectedUserName"></span>
            </p>

            <form :action="'/admin/customers/' + selectedUserId + '/points'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">عدد النقاط (موجب للإضافة / سالب للخصم)</label>
                    <input type="number" name="points" required placeholder="+100 أو -50" class="w-full border-2 border-black p-2 text-xs font-mono font-bold">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">سبب التعديل / ملاحظات للأرشيف</label>
                    <input type="text" name="notes" required placeholder="مثال: تعويض عن تأخير أو مكافأة خاصة" class="w-full border-2 border-black p-2 text-xs">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="adjustPointsModal = false" class="border border-black px-4 py-2 text-xs font-bold">إلغاء</button>
                    <button type="submit" class="bg-black text-white px-5 py-2 text-xs font-bold uppercase shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">تأكيد التعديل</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
