@extends('layouts.admin')

@section('title', 'WhatsApp Client Notifications')

@section('content')
<div class="space-y-8" x-data="{ showToken: false }">

    <!-- Header & Status -->
    <div class="border-b-2 border-black pb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span class="text-2xl"><svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg></span>
                <h1 class="text-2xl font-black uppercase tracking-tight">WhatsApp Client Notifications</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1">
                إرسال إشعارات فورية وتأكيدات الشحن وتحديثات الطلبات تلقائياً لأرقام هواتف العملاء عبر واتساب.
            </p>
        </div>

        <!-- Connection Status Badge -->
        <div>
            @if($connectionStatus['ok'])
                <div class="inline-flex items-center gap-2 bg-green-500 text-white text-xs font-mono font-bold px-3 py-1.5 border-2 border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
                    <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                    <span>CONNECTED & ACTIVE</span>
                </div>
            @else
                <div class="inline-flex items-center gap-2 bg-gray-100 text-gray-700 text-xs font-mono font-bold px-3 py-1.5 border-2 border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span>STATUS: {{ $connectionStatus['status'] }}</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Main Grid: Settings & Test Sender -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Configuration Form (2 cols) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">
                <div class="flex items-center justify-between border-b-2 border-black pb-3 mb-6">
                    <h2 class="text-sm font-black uppercase tracking-wider">1. بيانات الربط والبوابة (Gateway Credentials)</h2>
                    <span class="text-[10px] bg-black text-white px-2 py-0.5 font-bold uppercase">Settings</span>
                </div>

                <form action="{{ route('admin.whatsapp.settings') }}" method="POST" class="space-y-5">
                    @csrf

                    <!-- Enable / Disable Toggle -->
                    <div class="bg-gray-50 border border-black p-4 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-black uppercase tracking-wider block">تفعيل إرسال رسائل واتساب للعملاء</span>
                            <span class="text-[11px] text-gray-500 block mt-0.5">عند تفعيل هذا الخيار، سيتم إرسال إشعار تأكيد فوري برقم الطلب والتفاصيل بمجرد إتمام العميل للشراء.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="whatsapp_enabled" value="1" {{ $enabled ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none border-2 border-black peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-black after:border after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                        </label>
                    </div>

                    <!-- Provider Selector -->
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">
                            بوابة الإرسال (Provider) *
                        </label>
                        <select name="whatsapp_provider" class="w-full border-2 border-black p-2.5 text-xs font-bold focus:outline-none bg-white">
                            <option value="ultramsg" {{ $provider === 'ultramsg' ? 'selected' : '' }}>UltraMsg (الأسهل والأسرع - يدعم مسح QR Code في دقيقة)</option>
                            <option value="generic" {{ $provider === 'generic' ? 'selected' : '' }}>Generic WhatsApp HTTP API / Wasapi</option>
                        </select>
                    </div>

                    <!-- Instance ID -->
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">
                            معرّف الخدمة (Instance ID) *
                        </label>
                        <input 
                            type="text" 
                            name="whatsapp_instance_id" 
                            value="{{ old('whatsapp_instance_id', $instanceId) }}" 
                            placeholder="مثال: instance99234" 
                            class="w-full border-2 border-black p-2.5 text-xs font-mono focus:outline-none bg-white"
                        >
                        <span class="text-[10px] text-gray-500 block mt-1">تجد هذا المعرف في لوحة تحكم حسابك في UltraMsg فور إنشاء الـ Instance.</span>
                    </div>

                    <!-- API Token -->
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">
                            رمز المصادقة (API Token) *
                        </label>
                        <div class="flex gap-2">
                            <input 
                                :type="showToken ? 'text' : 'password'" 
                                name="whatsapp_api_token" 
                                value="{{ old('whatsapp_api_token', $apiToken) }}" 
                                placeholder="مثال: p7m2k9a1z4b..." 
                                class="flex-1 border-2 border-black p-2.5 text-xs font-mono focus:outline-none bg-white"
                            >
                            <button 
                                type="button" 
                                @click="showToken = !showToken" 
                                class="border-2 border-black px-3 text-xs font-bold uppercase bg-gray-100 hover:bg-gray-200"
                            >
                                <span x-text="showToken ? 'Hide' : 'Show'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Customer VIP Contact Number on Storefront -->
                    <div class="border-t-2 border-black/10 pt-4 mt-4 space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="text-base"><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/></svg></span>
                            <h3 class="text-xs font-black uppercase tracking-wider text-black">2. رقم واتساب الظاهر للعملاء في المتجر (Storefront VIP Button)</h3>
                        </div>
                        <p class="text-[11px] text-gray-600">
                            هذا هو الرقم الذي يفتح تلقائياً عند ضغط العميل على زر «خدمة العملاء VIP واتساب» في المتجر والقائمة والفوتر وتتبع الطلب.
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">
                                    رقم الهاتف مع كود الدولة *
                                </label>
                                <input 
                                    type="text" 
                                    name="social_whatsapp" 
                                    value="{{ old('social_whatsapp', $socialWhatsapp ?? '') }}" 
                                    placeholder="2010XXXXXXXX (بدون + أو مسافات)" 
                                    class="w-full border-2 border-black p-2.5 text-xs font-mono focus:outline-none bg-white"
                                >
                                <span class="text-[10px] text-gray-500 block mt-1">مثال لمصر: 201012345678 أو 2011XXXXXXXX</span>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">
                                    الرسالة التلقائية المسبقة
                                </label>
                                <input 
                                    type="text" 
                                    name="social_whatsapp_msg" 
                                    value="{{ old('social_whatsapp_msg', $socialWhatsappMsg ?? '') }}" 
                                    placeholder="مرحباً، أود الاستفسار عن منتجاتكم" 
                                    class="w-full border-2 border-black p-2.5 text-xs focus:outline-none bg-white"
                                >
                                <span class="text-[10px] text-gray-500 block mt-1">الرسالة المكتوبة مسبقاً في شات الواتساب عند فتح الرابط</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3">
                        <button type="submit" class="bg-black text-white px-8 py-3.5 text-xs font-bold uppercase tracking-wider hover:bg-gray-800 transition-colors shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
                            <svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M7.5 12l4.5 4.5m0 0l4.5-4.5M12 3v13.5"/></svg> حفظ كافة إعدادات واتساب
                        </button>
                    </div>
                </form>
            </div>

            <!-- Visual Arabic Guide for 1-minute Setup -->
            <div class="bg-[#F5F5F0] border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">
                <div class="flex items-center gap-2 border-b-2 border-black pb-2 mb-4">
                    <span class="text-lg"><svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg></span>
                    <h3 class="text-xs font-black uppercase tracking-wider">شرح كيفية الربط في دقيقة واحدة (خطوة بخطوة)</h3>
                </div>

                <div class="space-y-4 text-xs font-sans leading-relaxed text-gray-800">
                    <div class="flex items-start gap-3 bg-white p-3 border border-black">
                        <span class="font-mono font-bold bg-black text-white w-6 h-6 flex items-center justify-center shrink-0">1</span>
                        <div>
                            <p class="font-bold">افتح موقع UltraMsg وأنشئ حساباً مجانياً:</p>
                            <p class="text-gray-600 mt-0.5">ادخل على الرابط <a href="https://ultramsg.com" target="_blank" class="text-black font-bold underline">ultramsg.com</a> واضغط "Try Free" ثم اختر إنشاء Instance جديدة.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 bg-white p-3 border border-black">
                        <span class="font-mono font-bold bg-black text-white w-6 h-6 flex items-center justify-center shrink-0">2</span>
                        <div>
                            <p class="font-bold">امسح رمز الـ QR بكاميرا واتساب من هاتفك:</p>
                            <p class="text-gray-600 mt-0.5">سيظهر لك رمز QR في الشاشة، افتح تطبيق واتساب بهاتفك > الأجهزة المرتبطة (Linked Devices) > امسح الرمز لربط رقم واتساب المتجر.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 bg-white p-3 border border-black">
                        <span class="font-mono font-bold bg-black text-white w-6 h-6 flex items-center justify-center shrink-0">3</span>
                        <div>
                            <p class="font-bold">انسخ الـ Instance ID و Token والصقهما هنا:</p>
                            <p class="text-gray-600 mt-0.5">انسخ المعرفين والصقهما في الخانات في الأعلى، ثم اضغط على "حفظ إعدادات واتساب".</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 bg-white p-3 border border-black">
                        <span class="font-mono font-bold bg-black text-white w-6 h-6 flex items-center justify-center shrink-0">4</span>
                        <div>
                            <p class="font-bold">جرّب إرسال رسالة تيست:</p>
                            <p class="text-gray-600 mt-0.5">أدخل رقم هاتفك في الخانة المجاورة واضغط "إرسال رسالة تجريبية" لتتأكد من وصول الرسالة فوراً لهاتفك!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Test Message Sender & Sample Template (1 col) -->
        <div class="space-y-6">
            
            <!-- Live Test Message Sender -->
            <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">
                <div class="flex items-center justify-between border-b-2 border-black pb-3 mb-4">
                    <h3 class="text-xs font-black uppercase tracking-wider">2. تجربة الإرسال الحي (Live Test)</h3>
                    <span class="text-xs"><svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5m0 0l-1.09 1.09A2.25 2.25 0 005.5 19.5h13a2.25 2.25 0 001.59-3.91L19 14.5m-14 0h14m-4.25-11.396v5.714a2.25 2.25 0 00.659 1.591L19 14.5"/></svg></span>
                </div>

                <p class="text-[11px] text-gray-600 mb-4 leading-relaxed">
                    أدخل أي رقم هاتف محمول (مصري أو دولي) لإرسال رسالة اختبارية والتأكد من عمل البوابة.
                </p>

                <form action="{{ route('admin.whatsapp.test') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">
                            رقم الهاتف للتجربة *
                        </label>
                        <input 
                            type="text" 
                            name="test_phone" 
                            placeholder="01050417732" 
                            required 
                            class="w-full border-2 border-black p-2.5 text-xs font-mono focus:outline-none bg-gray-50 focus:bg-white"
                        >
                        <span class="text-[10px] text-gray-400 block mt-1">يمكنك كتابته كـ 010... أو 2010...</span>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">
                            نص رسالة التجربة (اختياري)
                        </label>
                        <textarea 
                            name="test_message" 
                            rows="3" 
                            placeholder="رسالة تجريبية من لوحة تحكم ATELIER..."
                            class="w-full border-2 border-black p-2 text-xs focus:outline-none bg-gray-50 focus:bg-white"
                        ></textarea>
                    </div>

                    <button type="submit" class="w-full bg-green-600 text-white hover:bg-green-700 border-2 border-black py-2.5 text-xs font-bold uppercase tracking-wider transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                        <svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.58-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/></svg> إرسال رسالة تجريبية الآن
                    </button>
                </form>
            </div>

            <!-- Message Preview Template -->
            <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">
                <div class="flex items-center justify-between border-b-2 border-black pb-2 mb-3">
                    <h3 class="text-xs font-black uppercase tracking-wider">نموذج الرسالة التلقائية للعميل</h3>
                    <span class="text-[9px] bg-green-100 text-green-800 border border-green-700 px-1.5 py-0.5 font-bold">Auto</span>
                </div>

                <div class="bg-gray-100 border border-black p-3 text-[11px] font-mono text-gray-800 space-y-2 whitespace-pre-line leading-relaxed">
مرحباً [اسم العميل] <svg class="w-3.5 h-3.5 inline-block text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>

شكراً لتسوقك من *ATELIER Studio Egypt* الفاخرة! <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z"/></svg>
تم استلام وتأكيد طلبك بنجاح رقم:
*#ORD-XXXXXX*

<svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg> *القطع المختارة:*
• ATELIER Slim MagSafe Leather Wallet × 1 (540.00 EGP)

<svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg> *الإجمالي:* 590.00 EGP
<svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg> *طريقة السداد:* الدفع عند الاستلام (COD)
<svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg> *عنوان التوصيل:* 14 Brazil St, Zamalek, Cairo
<svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg> *موقع الخريطة:* https://maps.google.com/?q=30.05,31.22

<svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg> *تتبع الشحنة:*
https://atelier.eg/track-order?order=ORD-XXXXXX
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
