# حل مشكلة 403 Forbidden على الصفحة الرئيسية

## المشكلة
- ✅ ملفات الستايل تعمل الآن (`/assets/admin/css/styles.min.css`)
- ❌ الصفحة الرئيسية (`/`) تظهر 403 Forbidden

## الحل

### 1. تحقق من الصلاحيات على الاستضافة

قم بتنفيذ الأوامر التالية عبر SSH أو Terminal في cPanel:

```bash
# إصلاح صلاحيات public/index.php
chmod 644 public/index.php

# إصلاح صلاحيات المجلدات
chmod 755 public
chmod 755 bootstrap
chmod 755 storage
chmod 755 bootstrap/cache

# إصلاح صلاحيات ملفات Laravel
chmod -R 755 app
chmod -R 755 config
chmod -R 755 routes
```

### 2. تحقق من `.htaccess`

تأكد من أن ملف `.htaccess` موجود في:
- ✅ الجذر (`.htaccess`)
- ✅ `public/.htaccess`

### 3. تحقق من `public/index.php`

افتح ملف `public/index.php` على الاستضافة وتأكد من أن المسارات صحيحة:

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
```

### 4. تحقق من خطأ PHP

افتح ملف `.env` على الاستضافة وتأكد من:
```env
APP_DEBUG=true
```

**مهم:** بعد التأكد من المشكلة، عدّل `APP_DEBUG=false` للأمان.

### 5. تحقق من logs

تحقق من ملفات السجلات:
- `storage/logs/laravel.log`
- logs الخادم في cPanel

### 6. إذا استمرت المشكلة

جرب الحل البديل - إنشاء `.htaccess` أبسط:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # توجيه جميع الطلبات إلى public/index.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ public/index.php [L]
</IfModule>
```

**ملاحظة:** هذا الحل أبسط لكن قد لا يعمل مع ملفات الستايل. إذا استخدمته، قد تحتاج لإصلاح مسارات الستايل.

---

## الحل الأفضل (الموصى به)

استخدم `.htaccess` المحدث الذي تم إصلاحه في المشروع. هذا الملف:
- ✅ يدعم ملفات الستايل (`/assets/...`)
- ✅ يوجه الطلبات إلى `public/index.php`
- ✅ يحمي المجلدات الحساسة

لكن تأكد من الصلاحيات أولاً!

---

## ملخص الخطوات السريعة

1. ✅ إصلاح الصلاحيات: `chmod 644 public/index.php` و `chmod 755 public`
2. ✅ تحقق من `.env`: `APP_DEBUG=true` مؤقتاً لرؤية الأخطاء
3. ✅ تحقق من logs في `storage/logs/laravel.log`
4. ✅ إذا استمرت المشكلة، جرب `.htaccess` البسيط أعلاه
