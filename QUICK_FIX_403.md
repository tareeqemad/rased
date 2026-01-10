# حل سريع لمشكلة 403 Forbidden على الصفحة الرئيسية

## المشكلة
✅ ملفات الستايل تعمل (`/assets/admin/css/styles.min.css`)  
❌ الصفحة الرئيسية (`/`) تظهر 403 Forbidden

## الحل السريع (3 خطوات)

### الخطوة 1: إصلاح الصلاحيات (الأهم!)

قم بتنفيذ هذه الأوامر على الاستضافة عبر SSH أو Terminal في cPanel:

```bash
# إصلاح صلاحيات public/index.php
chmod 644 public/index.php

# إصلاح صلاحيات المجلدات
chmod 755 public
chmod 755 bootstrap
chmod 755 storage
chmod 755 bootstrap/cache

# إصلاح صلاحيات ملفات Laravel الأساسية
chmod -R 755 app
chmod -R 755 config
chmod -R 755 routes
chmod -R 755 bootstrap
```

### الخطوة 2: تحقق من `.env`

افتح ملف `.env` على الاستضافة وتأكد من:

```env
APP_DEBUG=true
APP_URL=https://gazarased.com
```

**ملاحظة:** بعد حل المشكلة، عدّل `APP_DEBUG=false` للأمان.

### الخطوة 3: مسح الكاش

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

## إذا استمرت المشكلة

### الحل البديل: تحقق من logs

افتح ملف السجلات لرؤية الخطأ الفعلي:
```bash
tail -n 50 storage/logs/laravel.log
```

أو من cPanel:
- File Manager → `storage/logs/laravel.log`

### الحل البديل: اختبار مباشر

افتح المتصفح وانتقل إلى:
- `https://gazarased.com/public/index.php`

إذا ظهرت الصفحة ✅ → المشكلة في `.htaccess`  
إذا ظهر 403 ❌ → المشكلة في الصلاحيات أو `public/index.php`

---

## الحل البديل: `.htaccess` بسيط

إذا استمرت المشكلة، جرب هذا `.htaccess` البسيط:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # توجيه جميع الطلبات إلى public/index.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ public/index.php [L]
</IfModule>
```

**ملاحظة:** هذا الحل أبسط لكن قد لا يعمل مع ملفات الستايل. إذا استخدمته، قد تحتاج لإضافة قاعدة إضافية لملفات الستايل.

---

## السبب الأكثر شيوعاً

**99% من حالات 403 Forbidden تكون بسبب الصلاحيات!**

تأكد من:
- ✅ `public/index.php` لديه صلاحية 644
- ✅ مجلدات Laravel لديها صلاحية 755
- ✅ `storage` و `bootstrap/cache` لديهما صلاحية 775

---

## ملخص سريع

```bash
# 1. إصلاح الصلاحيات
chmod 644 public/index.php
chmod 755 public bootstrap storage bootstrap/cache

# 2. مسح الكاش
php artisan config:clear
php artisan cache:clear

# 3. تحقق من .env
# APP_URL=https://gazarased.com
```

بعد تنفيذ هذه الخطوات، يجب أن تعمل الصفحة الرئيسية! 🎉
