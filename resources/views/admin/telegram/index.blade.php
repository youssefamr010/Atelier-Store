@extends('layouts.admin')

@section('title', 'Telegram Order Notifications')

@section('content')
<div class="max-w-5xl space-y-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 border-b-2 border-black pb-4">
        <div>
            <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-gray-500">INSTANT DISPATCH ALERTS</span>
            <h1 class="text-3xl font-black uppercase tracking-tight text-black">Telegram Order Notifications</h1>
            <p class="text-xs text-gray-500 mt-0.5">Receive full order receipts directly on Telegram in real-time as customers complete checkout</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <form action="{{ route('admin.telegram.send-dashboard') }}" method="POST">
                @csrf
                <button type="submit" class="bg-blue-700 text-white hover:bg-blue-800 border-2 border-black px-4 py-2.5 text-xs font-bold uppercase tracking-wider shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all flex items-center gap-2">
                    <span><svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/></svg></span>
                    <span>تفعيل أزرار المساعد الذكي</span>
                </button>
            </form>
            <form action="{{ route('admin.telegram.sync') }}" method="POST">
                @csrf
                <button type="submit" class="bg-gray-100 text-black hover:bg-gray-200 border-2 border-black px-4 py-2.5 text-xs font-bold uppercase tracking-wider shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all flex items-center gap-2">
                    <span><svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/></svg></span>
                    <span>فحص وتحديث الأوامر</span>
                </button>
            </form>
            <form action="{{ route('admin.telegram.test') }}" method="POST">
                @csrf
                <button type="submit" class="bg-black text-white hover:bg-gray-800 border-2 border-black px-4 py-2.5 text-xs font-bold uppercase tracking-wider shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all flex items-center gap-2">
                    <span><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg></span>
                    <span>إرسال تجربة</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Critical Notice Banner -->
    <div class="bg-amber-50 border-2 border-amber-400 p-4 text-xs text-amber-900 space-y-2 shadow-sm">
        <div class="flex items-center gap-2 font-bold uppercase tracking-wider text-[11px] text-amber-950">
            <span><svg class="w-5 h-5 inline-block text-amber-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg></span>
            <span>تنبيه هام جداً: لماذا قد تفشل الرسائل برسالة "Chat Not Found"؟</span>
        </div>
        <p class="leading-relaxed">
            تفرض تيليجرام قاعدة خصوصية صارمة: <b>البوت لا يستطيع إرسال أي رسالة لأي شخص حتى يقوم ذلك الشخص بفتح محادثة مع البوت أولاً والضغط على START</b>.
            قبل الضغط على إرسال، افتح تيليجرام وابحث عن اسم البوت الخاص بك، واضغط زر <b>START</b> أو أرسل له <code class="bg-white px-1 font-mono font-bold">/start</code> لمرة واحدة فقط.
        </p>
    </div>

    <!-- Smart Bot Capabilities Banner -->
    <div class="bg-black text-white border-2 border-black p-5 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-3">
        <div class="flex items-center justify-between flex-wrap gap-2 border-b border-white/20 pb-3">
            <div class="flex items-center gap-2">
                <span class="text-xl"><svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12h15m-15 3.75H3m18 0h-1.5M15.75 3v1.5M6 6.75h12a2.25 2.25 0 012.25 2.25v9a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18V9A2.25 2.25 0 016 6.75z"/></svg></span>
                <span class="font-editorial font-bold text-sm uppercase tracking-widest text-white">المساعد الذكي لبوت التيليجرام (Telegram AI Store Assistant)</span>
            </div>
            <span class="bg-green-500/20 border border-green-400 text-green-300 text-[10px] font-mono font-bold uppercase px-2 py-0.5">جاهز للتفاعل <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg></span>
        </div>

        <p class="text-xs text-white/80 leading-relaxed font-sans">
            تمت ترقية البوت ليصبح مساعداً تنفيذياً كاملاً للمتجر! يمكنك التحدث معه من هاتفك أو الضغط على الأزرار التفاعلية أسفل شاشة التيليجرام للحصول على تقارير لحظية تشمل:
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
            <div class="border border-white/20 bg-white/5 p-3 space-y-1">
                <span class="font-editorial font-bold text-xs text-white block"><svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg> إحصائيات وأرباح حية</span>
                <p class="text-[11px] text-white/60">إجمالي المبيعات، مبيعات اليوم والشهر، متوسط قيمة الطلب، ونسبة الدفع كاش vs إلكتروني.</p>
            </div>
            <div class="border border-white/20 bg-white/5 p-3 space-y-1">
                <span class="font-editorial font-bold text-xs text-white block"><svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg> أحدث الطلبات ومواقع GPS</span>
                <p class="text-[11px] text-white/60">أحدث 5 طلبات مع أزرار بنقرة واحدة لفتح خريطة جوجل أو مراسلة العميل واتساب.</p>
            </div>
            <div class="border border-white/20 bg-white/5 p-3 space-y-1">
                <span class="font-editorial font-bold text-xs text-white block"><svg class="w-5 h-5 inline-block text-amber-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg> نواقص المخزون وعملاء VIP</span>
                <p class="text-[11px] text-white/60">كشف فوري بالمنتجات التي أوشكت على النفاد، مع ترتيب أعلى عملاء إنفاقاً في المتجر.</p>
            </div>
        </div>

        <div class="pt-2 flex flex-wrap items-center justify-between gap-3 text-xs border-t border-white/10 font-mono">
            <span class="text-white/70"><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg> ميزة البحث الفوري: أرسل رقم أي هاتف أو كود طلب في الشات ليبحث عنه فوراً!</span>
            <form action="{{ route('admin.telegram.send-dashboard') }}" method="POST">
                @csrf
                <button type="submit" class="bg-white text-black hover:bg-gray-200 font-editorial font-bold text-[10px] uppercase tracking-wider px-3 py-1.5 transition-colors">
                    إرسال لوحة الأزرار لهاتفي الآن ←
                </button>
            </form>
        </div>
    </div>

    <!-- 1. Bot Token Configuration -->
    <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-4">
        <div class="flex items-center justify-between border-b pb-3">
            <h2 class="text-sm font-mono font-bold uppercase tracking-widest text-gray-700 flex items-center gap-2">
                <span><svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12h15m-15 3.75H3m18 0h-1.5M15.75 3v1.5M6 6.75h12a2.25 2.25 0 012.25 2.25v9a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18V9A2.25 2.25 0 016 6.75z"/></svg></span>
                <span>1. Telegram Bot Token</span>
            </h2>
            <div>
                @if(!empty($botInfo) && $botInfo['ok'])
                    <span class="bg-green-100 text-green-800 border border-green-800 text-[10px] font-bold uppercase px-2 py-0.5">
                        Connected: {{ $botInfo['username'] ? '@' . $botInfo['username'] : $botInfo['name'] }}
                    </span>
                @elseif(!empty($botToken))
                    <span class="bg-amber-100 text-amber-800 border border-amber-800 text-[10px] font-bold uppercase px-2 py-0.5">Token Saved (Unverified)</span>
                @else
                    <span class="bg-gray-100 text-gray-800 border border-gray-400 text-[10px] font-bold uppercase px-2 py-0.5">Token Required</span>
                @endif
            </div>
        </div>

        @if(!empty($botInfo) && $botInfo['ok'] && !empty($botInfo['username']))
            <div class="bg-green-50 border border-green-400 p-3 text-xs flex items-center justify-between text-green-900">
                <div class="flex items-center gap-2">
                    <span class="text-base"><svg class="w-4 h-4 inline-block text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
                    <span>Your bot is live: <b>@{{ $botInfo['username'] }}</b> ({{ $botInfo['name'] }})</span>
                </div>
                <a href="https://t.me/{{ $botInfo['username'] }}" target="_blank" class="bg-green-800 text-white px-3 py-1 text-[10px] font-bold uppercase tracking-wider hover:bg-green-900">
                    Open Bot in Telegram & Click START ↗
                </a>
            </div>
        @endif

        <!-- BotFather Instructions Card -->
        <div class="bg-gray-50 border border-black p-4 text-xs space-y-2">
            <p class="font-bold text-black uppercase tracking-wider text-[11px]">How to create a Telegram Bot:</p>
            <ol class="list-decimal list-inside space-y-1 text-gray-700 leading-relaxed">
                <li>Open Telegram and search for <a href="https://t.me/BotFather" target="_blank" class="font-mono font-bold text-black underline">@BotFather</a>.</li>
                <li>Send the command <code class="bg-white border px-1 font-mono font-bold">/newbot</code> and follow the prompts to choose a name and username.</li>
                <li>BotFather will reply with your API Token (e.g. <code class="bg-white border px-1 font-mono text-[11px]">123456789:ABCdefGhIJKlmNoPQRstuVwxyZ</code>).</li>
                <li>Paste the token below and click <b>Save Token</b>.</li>
            </ol>
        </div>

        <form action="{{ route('admin.telegram.settings') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Bot API Token *</label>
                <div class="flex gap-2" x-data="{ show: false }">
                    <input 
                        type="password" 
                        name="telegram_bot_token" 
                        value="{{ old('telegram_bot_token', $botToken) }}" 
                        placeholder="123456789:ABCdefGhIJKlmNoPQRstuVwxyZ"
                        required
                        autocomplete="new-password"
                        class="flex-1 border-2 border-black p-2.5 text-xs font-mono focus:outline-none bg-white"
                        :type="show ? 'text' : 'password'"
                    >
                    <button type="button" @click="show = !show" class="border-2 border-black px-3 text-xs font-bold uppercase bg-gray-100 hover:bg-gray-200">
                        <span x-text="show ? 'Hide' : 'Show'"></span>
                    </button>
                    <button type="submit" class="bg-black text-white px-5 py-2.5 text-xs font-bold uppercase tracking-wider hover:bg-gray-800">
                        Save Token
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- 2. Add New Recipient -->
    <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-4">
        <h2 class="text-sm font-mono font-bold uppercase tracking-widest text-gray-700 border-b pb-3 flex items-center gap-2">
            <span><svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/></svg></span>
            <span>2. Add Notification Recipient</span>
        </h2>

        <!-- Chat ID Instructions Card -->
        <div class="bg-gray-50 border border-black p-4 text-xs space-y-2">
            <p class="font-bold text-black uppercase tracking-wider text-[11px]">How to find your Telegram Chat ID:</p>
            <ol class="list-decimal list-inside space-y-1 text-gray-700 leading-relaxed">
                <li>Search Telegram for <a href="https://t.me/userinfobot" target="_blank" class="font-mono font-bold text-black underline">@userinfobot</a> or <a href="https://t.me/getmyid_bot" target="_blank" class="font-mono font-bold text-black underline">@getmyid_bot</a>.</li>
                <li>Send it any message — it will immediately reply with your numeric <b>Id</b> (e.g. <code class="bg-white border px-1 font-mono font-bold">8817257718</code>).</li>
                <li><b>Crucial:</b> Search for your store bot in Telegram and send it <code class="bg-white border px-1 font-mono font-bold">/start</code>.</li>
                <li>Enter your Label and Chat ID below to add to dispatch list.</li>
            </ol>
        </div>

        <form action="{{ route('admin.telegram.recipients.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            @csrf
            <div class="sm:col-span-5">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Recipient Label *</label>
                <input type="text" name="label" required placeholder="e.g. Owner Mobile / Store Manager" class="w-full border-2 border-black p-2.5 text-xs focus:outline-none">
            </div>
            <div class="sm:col-span-4">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Telegram Chat ID *</label>
                <input type="text" name="chat_id" required placeholder="e.g. 8817257718" class="w-full border-2 border-black p-2.5 text-xs font-mono focus:outline-none">
            </div>
            <div class="sm:col-span-3">
                <button type="submit" class="w-full bg-black text-white py-3 text-xs font-bold uppercase tracking-wider hover:bg-gray-800 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                    + Add Recipient
                </button>
            </div>
        </form>
    </div>

    <!-- 3. Connected Recipients List -->
    <div class="bg-white border-2 border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] overflow-hidden">
        <div class="p-4 bg-gray-50 border-b-2 border-black flex items-center justify-between">
            <h2 class="text-xs font-mono font-bold uppercase tracking-widest text-black">
                Connected Recipients ({{ $recipients->count() }})
            </h2>
            <span class="text-[10px] text-gray-500 font-mono">Real-time broadcast target</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b-2 border-black bg-gray-100 text-[10px] font-bold uppercase tracking-wider text-gray-600">
                        <th class="p-3">Label</th>
                        <th class="p-3">Chat ID</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Added Date</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 font-medium">
                    @forelse($recipients as $recipient)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="p-3 font-bold text-black flex items-center gap-2">
                                <span><svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/></svg></span>
                                <span>{{ $recipient->label }}</span>
                            </td>
                            <td class="p-3 font-mono text-gray-700">
                                {{ $recipient->chat_id }}
                            </td>
                            <td class="p-3">
                                @if($recipient->is_active)
                                    <span class="bg-green-100 text-green-800 border border-green-800 text-[9px] font-bold uppercase px-2 py-0.5">Active</span>
                                @else
                                    <span class="bg-gray-100 text-gray-600 border border-gray-400 text-[9px] font-bold uppercase px-2 py-0.5">Paused</span>
                                @endif
                            </td>
                            <td class="p-3 text-gray-500 font-mono text-[10px]">
                                {{ $recipient->created_at->format('M d, Y') }}
                            </td>
                            <td class="p-3 text-right space-x-2">
                                <form action="{{ route('admin.telegram.recipients.toggle', $recipient->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="border border-black px-2 py-1 text-[10px] font-bold uppercase hover:bg-gray-100">
                                        {{ $recipient->is_active ? 'Pause' : 'Activate' }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.telegram.recipients.delete', $recipient->id) }}" method="POST" class="inline" onsubmit="return confirm('Remove recipient {{ $recipient->label }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="border border-red-600 text-red-600 px-2 py-1 text-[10px] font-bold uppercase hover:bg-red-50">
                                        Remove
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-400 text-xs">
                                No recipients connected yet. Add your Telegram Chat ID above to start receiving instant order alerts.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
