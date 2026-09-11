# نشر ATELIER على AWS Free Tier (مجاني 12 شهر)

هذه الوصفة تستخدم Ubuntu على AWS Free Tier مع Docker، وتُشغّل Laravel وMySQL وRedis وNginx وHTTPS تلقائيًا عبر Caddy. **نفس إعداد Oracle بالظبط — بدون أي تغيير في الكود.**

> ⚠️ يحتاج بطاقة ائتمان/خصم للتحقق فقط — لن يتم خصم أي مبلغ خلال الـ 12 شهر المجانية.

## 1. إنشاء حساب AWS

1. اذهب إلى [aws.amazon.com/free](https://aws.amazon.com/free)
2. اضغط **Create a Free Account**
3. أدخل الإيميل وكلمة المرور
4. أدخل بيانات بطاقة الخصم/الائتمان (للتحقق فقط — خصم مؤقت $1 ثم يرجع)
5. اختر **Basic Support (Free)**
6. تم! حسابك جاهز

## 2. إنشاء EC2 Instance (الخادم)

1. اذهب إلى **AWS Console → EC2 → Launch Instance**
2. استخدم الإعدادات التالية:

| الإعداد | القيمة |
| --- | --- |
| **Name** | `atelier-server` |
| **AMI** | Ubuntu Server 24.04 LTS (Free tier eligible) ✅ |
| **Instance type** | `t2.micro` (Free tier eligible) ✅ |
| **Key pair** | اضغط **Create new key pair** → سمّه `atelier-key` → نوع `.pem` → حمّله واحفظه |
| **Storage** | **30 GB gp3** (Free tier يسمح حتى 30 GB) |

3. في **Network settings** اضغط **Edit**:
   - ✅ Allow SSH traffic (port 22)
   - ✅ Allow HTTPS traffic (port 443)
   - ✅ Allow HTTP traffic (port 80)

4. اضغط **Launch Instance**

## 3. تثبيت Elastic IP (عنوان ثابت)

> مهم! بدون Elastic IP العنوان يتغير كل ما تعيد تشغيل الخادم.

1. اذهب إلى **EC2 → Elastic IPs → Allocate Elastic IP address**
2. اضغط **Allocate**
3. اختر الـ IP الجديد → **Actions → Associate Elastic IP address**
4. اربطه بـ `atelier-server`
5. انسخ عنوان الـ IP

> ⚠️ Elastic IP مجاني **فقط** لو مربوط بخادم شغال. لو وقّفت الخادم وسبت الـ IP يتحاسب.

## 4. DNS في Hostinger

أضف السجلات بعد حصولك على الـ Elastic IP:

| Type | Name | Value |
| --- | --- | --- |
| A | `@` | AWS Elastic IP |
| A | `www` | AWS Elastic IP |

انتظر 5-15 دقيقة لانتشار DNS.

## 5. الدخول على الخادم عبر SSH

### من Windows (PowerShell):

```powershell
# حدد صلاحيات المفتاح (مرة واحدة)
icacls "C:\Users\hp\Downloads\atelier-key.pem" /inheritance:r /grant:r "%USERNAME%:R"

# ادخل على الخادم
ssh -i "C:\Users\hp\Downloads\atelier-key.pem" ubuntu@YOUR_ELASTIC_IP
```

### أو من AWS Console:
اضغط على الـ Instance → **Connect** → **EC2 Instance Connect** → **Connect** (يفتح terminal في المتصفح)

## 6. تثبيت Docker على الخادم

```bash
# تحديث النظام
sudo apt update && sudo apt upgrade -y

# تثبيت Docker
curl -fsSL https://get.docker.com | sudo sh
sudo usermod -aG docker $USER

# اخرج وادخل مرة ثانية لتفعيل الصلاحيات
exit
# ادخل مرة ثانية عبر SSH

# تأكد
docker --version
docker compose version
```

## 7. رفع وتشغيل المشروع

```bash
# انسخ المشروع
git clone YOUR_PRIVATE_REPOSITORY atelier
cd atelier

# انسخ وعدّل ملف الإنتاج
cp .env.production.example .env.production
nano .env.production
```

عدّل القيم التالية في `.env.production`:

```
APP_URL=https://atelier404.store
APP_ENV=production
APP_DEBUG=false
DB_DATABASE=atelier
DB_USERNAME=atelier
DB_PASSWORD=<كلمة-مرور-قوية-عشوائية>
DB_ROOT_PASSWORD=<كلمة-مرور-قوية-ثانية>
```

ثم:

```bash
# توليد APP_KEY
docker compose -f docker-compose.production.yml --env-file .env.production \
  run --rm --entrypoint php app artisan key:generate --show

# انسخ الناتج إلى APP_KEY في .env.production
nano .env.production

# بناء وتشغيل كل الحاويات
docker compose -f docker-compose.production.yml --env-file .env.production up -d --build

# تأكد من حالة الحاويات
docker compose -f docker-compose.production.yml --env-file .env.production ps
```

## 8. بعد النشر — اختبار

```bash
# شوف logs الـ Caddy (لازم يطلع SSL certificate obtained)
docker compose -f docker-compose.production.yml --env-file .env.production logs -f caddy

# اختبر
curl -I https://atelier404.store/up
```

اختبر: الصفحة الرئيسية، رفع صورة، السلة مع لون، Checkout، Telegram، وPaymob test mode.

## 9. إعداد Swap (مهم جداً لـ t2.micro)

> الـ t2.micro فيه 1 GB RAM فقط — لازم Swap عشان MySQL + Redis + Laravel ما يقعوش.

```bash
sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
```

تأكد:
```bash
free -h
# لازم تشوف Swap: 2.0Gi
```

## نسخ احتياطي وتحديث

- قاعدة البيانات في Docker volume `mysql-data`؛ صدّر نسخة بانتظام:
  ```bash
  docker compose -f docker-compose.production.yml --env-file .env.production \
    exec db mysqldump -u root -p atelier > backup_$(date +%Y%m%d).sql
  ```
- الصور في `app-storage`. انسخها قبل أي تغيير كبير.
- للتحديث: `git pull` ثم `docker compose ... up -d --build`.

## ⚠️ حدود AWS Free Tier — انتبه!

| المورد | المجاني | لو تعدّيت |
| --- | --- | --- |
| EC2 t2.micro | **750 ساعة/شهر** (= خادم واحد 24/7) | ~$8.50/شهر |
| Storage (gp3) | **30 GB** | $0.08/GB/شهر |
| Elastic IP | **مجاني لو مربوط بخادم شغال** | $3.65/شهر لو مش مربوط |
| Data Transfer OUT | **100 GB/شهر** | $0.09/GB |
| المدة | **12 شهر** من تاريخ إنشاء الحساب | كل حاجة تتحاسب |

> **نصيحة:** فعّل **Billing Alerts** في AWS Console عشان تاخد إشعار لو قربت تتحاسب:
> AWS Console → Billing → Budgets → Create Budget → $0.01 threshold

## ملاحظة الأمان

- لا ترفع `.env.production` أو مفاتيح Paymob أو `atelier-key.pem` إلى Git أو Telegram.
- غيّر كلمات مرور قاعدة البيانات إلى قيم طويلة وفريدة قبل أول تشغيل.
- لا تفتح منافذ MySQL (3306) أو Redis (6379) في Security Group.
