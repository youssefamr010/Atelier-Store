# 🚀 دليل رفع تطبيق ATELIER على متجر Google Play Store (TWA / PWA)

تم تجهيز وتحديث ملفات المشروع بالكامل (Manifest, Service Worker, Digital Asset Links, Maskable Icons, Standalone Mode) ليعمل التطبيق كـ **Trusted Web Activity (TWA)** أو **PWA Native App** بدون شريط متصفح وبأداء فائق السرعة على جميع هواتف Android.

---

## 🛠️ الملفات المجهزة داخل المشروع:
1. `public/manifest.json`: يحتوي على اسم التطبيق، الأيقونات الدائرية والقابلة للتكيف (Maskable)، ألوان الثيم الفاخر، وروابط الاختصارات السريعة (Catalog, Bag, Account, Track).
2. `public/.well-known/assetlinks.json`: ملف ربط الدومين بالـ Android Package لإخفاء شريط العنوان في كروم تلقائياً.
3. `public/sw.js`: Service Worker للتخزين المؤقت والعمل بدون إنترنت (Offline).
4. `resources/views/partials/offline-modal.blade.php`: شاشة انقطاع الإنترنت التفاعلية بالأنيميشن ولعبة البكسل.

---

## 📱 الطريقة الأولى (الأسرع والأسهل عبر PWABuilder - موصى بها) 🌟

### الخطوة 1: توليد ملف الـ Android App Bundle (.aab)
1. تأكد من أن موقعك مرفوع وله دومين HTTPS شغال (مثل `https://atelier-store.com`).
2. افتح موقع: **[PWABuilder.com](https://www.pwabuilder.com/)**
3. ضع رابط موقعك واضغط **Start**.
4. سيقوم الموقع بفحص الـ Manifest والـ Service Worker (ستجد التقييم ممتاز 100%).
5. اضغط على زر **Package for Stores** ثم اختر **Google Play (Android)**.
6. في إعدادات حزمة الأندرويد:
   - **Package ID**: `com.atelier.store`
   - **App Name**: `ATELIER`
   - **Theme Color**: `#000000`
   - **Background Color**: `#121214`
   - **Signing Key**: اختر "Generate a new key" إذا لم يكن لديك مفتاح قديم، وقم بتحميل وحفظ ملف الـ keystore وكلمة المرور في مكان آمن.
7. اضغط **Generate Package** وقم بتحميل ملف الـ ZIP.

---

## 💻 الطريقة الثانية (عبر سطر الأوامر باستخدام Google Bubblewrap CLI)

إذا كنت تفضل بناء التطبيق محلياً على جهازك:

1. تثبيت أداة Bubblewrap الرسمية من جوجل:
```bash
npm i -g @bubblewrap/cli
```

2. تهيئة وبناء التطبيق:
```bash
bubblewrap init --manifest="https://your-domain.com/manifest.json"
```

3. بناء حزمة الإنتاج الموقعة (Signed AAB):
```bash
bubblewrap build
```
سينتج لك ملف باسم: `app-release-signed.aab`.

---

## 🔑 تحديث بصمة الأمان (SHA-256 Fingerprint) في `assetlinks.json`

لكي يتم إخفاء شريط المتصفح ويعمل التطبيق بملء الشاشة 100%:
1. بعد توليد المفتاح (Keystore) أو رفعه على Google Play، احصل على بصمة الـ SHA-256 من:
   - وحدة تحكم Google Play Console -> قسم **App Integrity** -> **App signing key certificate (SHA-256 fingerprint)**.
2. افتح ملف `public/.well-known/assetlinks.json` في المشروع.
3. استبدل الأصفار بالبصمة الحقيقية الخاصة بك:
```json
[
  {
    "relation": ["delegate_permission/common.handle_all_urls"],
    "target": {
      "namespace": "android_app",
      "package_name": "com.atelier.store",
      "sha256_cert_fingerprints": [
        "14:6D:E9:83:C5:73:06:50:D8:EE:B9:95:2F:34:FC:64:16:A0:83:42:E6:1D:BE:A8:8A:04:96:B2:3F:CF:44:E5"
      ]
    }
  }
]
```

---

## 🌐 خطوات الرفع على Google Play Console:

1. ادخل إلى **[Google Play Console](https://play.google.com/console)**.
2. اضغط **Create App** (إنشاء تطبيق):
   - اسم التطبيق: `ATELIER — Luxury Accessories`
   - اللغة الافتراضية: العربية (ar) أو الإنجليزية (en).
   - نوع التطبيق: `App`، مجاني `Free`.
3. أكمل قائمة إعداد التطبيق (App Setup Dashboard):
   - **Privacy Policy**: رابط سياسة الخصوصية بموقعك (مثل `https://your-domain.com/privacy-policy`).
   - **App Access**: اختر "All functionality is available without special access".
   - **Ads**: اختر "No, my app does not contain ads".
   - **Content Rating**: استبيان تقييم المحتوى (Shopping -> اختر مناسب لجميع الأعمار).
   - **Target Audience**: 18 سنة فما فوق.
   - **Category**: Shopping (تسوق).
4. تجهيز صفحة المتجر (Store Listing):
   - **أيقونة التطبيق**: 512x512 PNG (موجودة في `public/icons/icon-512.png`).
   - **صورة الغلاف (Feature Graphic)**: 1024x500 PNG.
   - **لقطات شاشة الهاتف (Screenshots)**: التقط صورتين أو أكثر من المتجر على الهاتف.
5. رفع ملف التطبيق:
   - اذهب إلى **Production** -> **Create new release**.
   - ارفع ملف `app-release-signed.aab`.
   - أضف ملاحظات الإصدار (Release Notes).
   - اضغط **Save** ثم **Review and rollout release**.

مبروك! سيتم فحص التطبيق من قبل فريق جوجل ونشره على المتجر خلال 24 - 48 ساعة. 🚀
