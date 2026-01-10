# حل مشكلة ملفات الستايل (CSS/JS) على الاستضافة

## المشكلة
رفعت المشروع على الدومين `gazarased` في مجلد `public_Html`، لكن ملفات الستايل (CSS/JS) لا تعمل.

## الحل السريع (الأفضل)

### الخطوة 1: تحقق من بنية المشروع على الاستضافة

يجب أن تكون البنية كالتالي:

```
public_Html/
├── app/
├── bootstrap/
├── config/
├── public/
│   ├── assets/          ← ملفات الستايل هنا
│   │   ├── admin/
│   │   └── front/
│   ├── index.php
│   └── .htaccess
├── routes/
├── vendor/
└── .htaccess            ← ملف في الجذر
```

### الخطوة 2: تأكد من إعدادات `.env`

افتح ملف `.env` على الاستضافة وتأكد من:

```env
APP_URL=https://gazarased.com

# إذا كان المشروع في مجلد فرعي مثل rased:
# APP_URL=https://gazarased.com/rased
```

### الخطوة 3: مسح الكاش

بعد تعديل `.env`، قم بتنفيذ الأوامر التالية عبر SSH أو Terminal في cPanel:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

## الحلول البديلة

### الحل الأول: إذا كان المشروع في مجلد فرعي (مثل `public_Html/rased`)

1. **تعديل `APP_URL` في `.env`:**
   ```env
   APP_URL=https://gazarased.com/rased
   ```
   أو
   ```env
   APP_URL=https://gazarased.com/rased/public
   ```

2. **تعديل `.htaccess` في الجذر (`public_Html/rased/.htaccess`):**
   ```apache
   <IfModule mod_rewrite.c>
       RewriteEngine On
       
       # إذا كان الطلب لملف موجود، اخدمه مباشرة
       RewriteCond %{REQUEST_FILENAME} -f
       RewriteRule ^ - [L]
       
       # توجيه باقي الطلبات إلى public/index.php
       RewriteCond %{REQUEST_URI} !^/public/
       RewriteRule ^(.*)$ public/index.php [L]
   </IfModule>
   ```

---

### الحل الثاني: إذا كان الدومين يشير مباشرة إلى `public_Html`

1. **نقل محتويات `public` إلى `public_Html`:**
   - انسخ جميع محتويات مجلد `public` إلى `public_Html`
   - نقل باقي المشروع خارج `public_Html` (مثل `rased_app/`)

2. **تعديل `public_Html/index.php`:**
   ```php
   <?php
   
   use Illuminate\Foundation\Application;
   use Illuminate\Http\Request;
   
   define('LARAVEL_START', microtime(true));
   
   if (file_exists($maintenance = __DIR__.'/../rased_app/storage/framework/maintenance.php')) {
       require $maintenance;
   }
   
   // تعديل المسار هنا
   require __DIR__.'/../rased_app/vendor/autoload.php';
   
   $app = require_once __DIR__.'/../rased_app/bootstrap/app.php';
   
   $app->handleRequest(Request::capture());
   ```

3. **تعديل `APP_URL` في `.env`:**
   ```env
   APP_URL=https://gazarased.com
   ```

---

## خطوات التحقق

### 1. تحقق من وجود ملفات الستايل

افتح المتصفح وانتقل إلى:
- `https://gazarased.com/assets/admin/css/styles.min.css`
- أو `https://gazarased.com/rased/assets/admin/css/styles.min.css` (إذا كان في مجلد فرعي)

**إذا ظهر الملف:** المشكلة في `APP_URL` أو في `.htaccess`  
**إذا لم يظهر الملف:** المشكلة في رفع الملفات - تأكد من رفع مجلد `public/assets` كاملاً

### 2. تحقق من Console المتصفح

افتح المتصفح واضغط F12، ثم:
- انتقل إلى تبويب **Console** - تحقق من وجود أخطاء JavaScript
- انتقل إلى تبويب **Network** - تحقق من الطلبات الفاشلة (404)

### 3. تحقق من `.env`

تأكد من:
- `APP_URL` يحتوي على الدومين الصحيح بدون `/` في النهاية
- `APP_ENV=production`
- `APP_DEBUG=false`

---

## ملاحظات مهمة

1. **ملف `.htaccess` في مجلد `public`:**
   - لا تعدله - يجب أن يبقى كما هو

2. **مسار `asset()` helper:**
   - Laravel يستخدم `asset()` لتوليد مسارات الملفات
   - المسارات تعتمد على `APP_URL`
   - تأكد من أن `APP_URL` صحيح

3. **إذا استمرت المشكلة:**
   - تحقق من صلاحيات الملفات: `chmod -R 755 public/assets`
   - تحقق من أن ملفات `.htaccess` موجودة
   - تأكد من تفعيل `mod_rewrite` على الاستضافة

---

## الأوامر المهمة

```bash
# مسح الكاش
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# إنشاء رابط Storage
php artisan storage:link

# إصلاح الصلاحيات
chmod -R 755 public/assets
chmod -R 775 storage bootstrap/cache
```

---

## الملفات التي تم تعديلها

1. ✅ `.htaccess` - تم تحديثه لدعم ملفات الستايل بشكل أفضل

---

إذا واجهت أي مشاكل، تحقق من:
- ملف `.env` - تأكد من `APP_URL` صحيح
- Console المتصفح - تحقق من الأخطاء
- Network tab - تحقق من الطلبات الفاشلة
