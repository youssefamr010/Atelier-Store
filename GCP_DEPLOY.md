# نشر ATELIER على Google Cloud Platform (Free Tier)

هذه الوصفة تستخدم Ubuntu على GCP Free Tier مع Docker، وتُشغّل Laravel وMySQL وRedis وNginx وHTTPS تلقائيًا عبر Caddy، بالإضافة إلى Worker وScheduler. نفس إعداد Oracle بدون أي تغيير في الكود.

## 1. إنشاء حساب GCP

1. اذهب إلى [cloud.google.com/free](https://cloud.google.com/free) وسجّل بحساب Google.
2. أدخل بطاقة الدفع (لن يتم خصم أي مبلغ — للتحقق فقط).
3. ستحصل على **$300 رصيد مجاني لمدة 90 يوم**.

## 2. إنشاء VM Instance

1. اذهب إلى **Compute Engine → VM Instances → Create Instance**
2. استخدم الإعدادات التالية:

| الإعداد | القيمة |
| --- | --- |
| **Name** | `atelier-server` |
| **Region** | `me-west1` (تل أبيب) أو `europe-west1` (بلجيكا) — الأقرب لمصر |
| **Machine type** | `e2-medium` (2 vCPU, 4 GB) أثناء الفترة التجريبية |
| **Boot disk** | Ubuntu 24.04 LTS, **30 GB SSD** |
| **Firewall** | ✅ Allow HTTP traffic, ✅ Allow HTTPS traffic |

3. اضغط **Create** — الخادم يعمل خلال 30 ثانية.

## 3. حجز Static IP

1. اذهب إلى **VPC Network → IP Addresses → Reserve External Static Address**
2. سمّه `atelier-ip` واربطه بالـ VM.
3. انسخ عنوان الـ IP.

## 4. DNS في Hostinger

أضف السجلات بعد حصولك على الـ Static IP من GCP:

| Type | Name | Value |
| --- | --- | --- |
| A | `@` | GCP static IPv4 |
| A | `www` | GCP static IPv4 |

انتظر 5-15 دقيقة لانتشار DNS. بعدها Caddy يصدر SSL تلقائيًا.

## 5. تثبيت Docker على الخادم

```bash
# ادخل على الخادم — اضغط زر SSH في GCP Console أو استخدم:
gcloud compute ssh atelier-server --zone=YOUR_ZONE

# تحديث النظام
sudo apt update && sudo apt upgrade -y

# تثبيت Docker
curl -fsSL https://get.docker.com | sudo sh
sudo usermod -aG docker $USER

# اخرج وادخل مرة ثانية
exit
# ادخل مرة ثانية عبر SSH

# تأكد
docker --version
docker compose version
```

## 6. رفع وتشغيل المشروع

```bash
git clone YOUR_PRIVATE_REPOSITORY atelier
cd atelier
cp .env.production.example .env.production
nano .env.production
```

تأكد من تعيين هذه القيم في `.env.production`:

```
APP_URL=https://atelier404.store
APP_ENV=production
APP_DEBUG=false
DB_DATABASE=atelier
DB_USERNAME=atelier_user
DB_PASSWORD=<كلمة-مرور-قوية>
DB_ROOT_PASSWORD=<كلمة-مرور-قوية-أخرى>
```

ثم:

```bash
# توليد APP_KEY
docker compose -f docker-compose.production.yml --env-file .env.production \
  run --rm --entrypoint php app artisan key:generate --show

# انسخ الناتج إلى APP_KEY في .env.production ثم:

# بناء وتشغيل كل الحاويات
docker compose -f docker-compose.production.yml --env-file .env.production up -d --build

# تأكد من حالة الحاويات
docker compose -f docker-compose.production.yml --env-file .env.production ps
```

## 7. بعد النشر

```bash
docker compose -f docker-compose.production.yml --env-file .env.production logs -f caddy
curl -I https://atelier404.store/up
```

اختبر: الصفحة الرئيسية، رفع صورة، السلة مع لون، Checkout، Telegram، وPaymob test mode. لا تفعّل `PAYMOB_SANDBOX=false` قبل نجاح الاختبارات.

## نسخ احتياطي وتحديث

- قاعدة البيانات في Docker volume `mysql-data`؛ صدّر نسخة بانتظام عبر `mysqldump` داخل حاوية `db`.
- الصور في `app-storage`. انسخها قبل أي تغيير كبير.
- للتحديث: `git pull` ثم `docker compose ... up -d --build`.

## بعد انتهاء الفترة التجريبية (90 يوم)

- يمكنك تخفيض الخادم إلى `e2-micro` (1 vCPU, 1 GB) — **مجاني للأبد** في مناطق أمريكا (`us-west1`, `us-central1`, `us-east1`).
- لو تحتاج أداء أفضل: `e2-small` بحوالي $15/شهر.
- بديل رخيص: Hetzner ($4/شهر, 2 GB RAM, أوروبا).

## ملاحظة الأمان

لا ترفع `.env.production` أو مفاتيح Paymob أو مفاتيح SSH إلى Git أو Telegram. غيّر كلمات مرور قاعدة البيانات إلى قيم طويلة وفريدة قبل أول تشغيل.
