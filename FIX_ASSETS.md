# حل مشكلة ملفات الستايل (CSS/JS) على الاستضافة

## المشكلة
عند رفع مشروع Laravel على الاستضافة في مجلد `public_Html` (أو `public_html`)، ملفات الستايل لا تعمل.

## الحلول المحتملة:

### الحل الأول (الأفضل - إذا كان الدومين يشير إلى `public_Html` مباشرة):

1. **في الاستضافة (`public_Html`):**
   - نقل **جميع محتويات** مجلد `public` من المشروع إلى `public_Html` مباشرة
   - نقل باقي المشروع (app, bootstrap, config, etc.) إلى مجلد خارج `public_Html` (مثل `rased_app`)

2. **تعديل `public_Html/index.php`:**
   ```php
   <?php

   use Illuminate\Foundation\Application;
   use Illuminate\Http\Request;

   define('LARAVEL_START', microtime(true));

   // تعديل المسار هنا - يجب أن يشير إلى مجلد المشروع خارج public_Html
   require __DIR__.'/../rased_app/vendor/autoload.php';
   
   $app = require_once __DIR__.'/../rased_app/bootstrap/app.php';

   $app->handleRequest(Request::capture());
   ```

3. **تعديل `APP_URL` في ملف `.env`:**
   ```env
   APP_URL=https://gazarased.com
   ```

---

### الحل الثاني (إذا كان المشروع في مجلد فرعي مثل `public_Html/rased`):

1. **رفع المشروع كاملاً في `public_Html/rased/`**

2. **تأكد من البنية:**
   ```
   public_Html/
   └── rased/
       ├── app/
       ├── bootstrap/
       ├── config/
       ├── public/
       │   ├── assets/
       │   ├── index.php
       │   └── .htaccess
       └── vendor/
   ```

3. **تعديل `.htaccess` في `public_Html/rased/`:**
   ```apache
   <IfModule mod_rewrite.c>
       RewriteEngine On
       
       # توجيه جميع الطلبات إلى مجلد public
       RewriteCond %{REQUEST_URI} !^/rased/public/
       RewriteRule ^(.*)$ /rased/public/index.php [L]
   </IfModule>
   ```

4. **تعديل `APP_URL` في ملف `.env`:**
   ```env
   APP_URL=https://gazarased.com/rased/public
   ```
   أو
   ```env
   APP_URL=https://gazarased.com/rased
   ```

5. **تعديل `public/index.php` (اختياري - إذا لزم الأمر):**
   التأكد من أن المسارات النسبية صحيحة

---

### الحل الثالث (الأسهل - إعداد .htaccess في الجذر):

إذا رفعت المشروع في `public_Html/rased/`:

1. **إنشاء أو تعديل `.htaccess` في `public_Html/rased/`:**
   ```apache
   <IfModule mod_rewrite.c>
       RewriteEngine On
       
       # إذا كان الطلب لملف موجود في public، اخدمه مباشرة
       RewriteCond %{REQUEST_FILENAME} !-f
       RewriteCond %{REQUEST_FILENAME} !-d
       
       # توجيه باقي الطلبات إلى public/index.php
       RewriteRule ^(.*)$ public/index.php [L]
   </IfModule>
   ```

2. **تعديل `APP_URL` في `.env`:**
   ```env
   APP_URL=https://gazarased.com/rased
   ```

---

## خطوات التحقق:

1. **التحقق من وجود ملفات الستايل:**
   - افتح: `https://gazarased.com/rased/assets/admin/css/styles.min.css`
   - إذا ظهر الملف، المشكلة في المسارات النسبية
   - إذا لم يظهر، المشكلة في رفع الملفات

2. **التحقق من `.env`:**
   - تأكد من `APP_URL` صحيح
   - تأكد من `APP_DEBUG=false` في الإنتاج

3. **التحقق من الصلاحيات:**
   ```bash
   chmod -R 755 public/assets
   chmod -R 755 storage bootstrap/cache
   ```

4. **مسح الكاش:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear
   ```

---

## ملاحظات مهمة:

1. **ملف `.htaccess` في مجلد `public`:**
   - يجب أن يبقى كما هو (لا تعدله)

2. **مسار `asset()` helper:**
   - Laravel يستخدم `asset()` لتوليد مسارات الملفات
   - المسارات تعتمد على `APP_URL`

3. **إذا استمرت المشكلة:**
   - تحقق من console المتصفح (F12) لرؤية أخطاء تحميل الملفات
   - تحقق من Network tab لرؤية المسارات الفعلية المطلوبة
