# تعليمات الرفع على Hostinger

## الخطوات المطلوبة:

### 1. رفع الملفات
- ارفع جميع ملفات المشروع في المجلد `public_html/rased/`
- تأكد من رفع الملفات التالية:
  - `.htaccess` (في الجذر)
  - `public/.htaccess` (موجود بالفعل)
  - جميع ملفات Laravel الأخرى

### 2. إعداد قاعدة البيانات
- أنشئ قاعدة بيانات جديدة من cPanel
- احفظ معلومات الاتصال (اسم القاعدة، اسم المستخدم، كلمة المرور، الخادم)

### 3. إعداد ملف `.env`
- انسخ ملف `.env.example` إلى `.env` (إذا لم يكن موجوداً)
- قم بتحديث المتغيرات التالية:

```env
APP_NAME=Rased
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com/rased/public

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

### 4. توليد مفتاح التطبيق
```bash
php artisan key:generate
```

### 5. إنشاء رابط Storage
```bash
php artisan storage:link
```

### 6. تشغيل Migrations
```bash
php artisan migrate --force
```

### 7. إعداد الصلاحيات
```bash
chmod -R 775 storage bootstrap/cache
chmod -R 775 public/storage (إذا كان موجوداً)
```

### 8. إعداد المسار في `public/index.php`
تأكد من أن المسارات في `public/index.php` صحيحة:
- يجب أن تكون المسارات النسبية صحيحة

### 9. تحقق من PHP Version
- تأكد من أن إصدار PHP هو 8.1 أو أحدث
- يمكنك تغيير إصدار PHP من cPanel

## ملاحظات مهمة:
1. ملف `.htaccess` في الجذر سيوجه جميع الطلبات إلى مجلد `public`
2. تأكد من أن `APP_DEBUG=false` في الإنتاج
3. تأكد من أن `APP_URL` يحتوي على المسار الصحيح
4. إذا كان لديك مشاكل في المسارات، قد تحتاج لتعديل `APP_URL`

