تشغيل موقع آيشار على جهازك ثم نشره عبر ngrok
=============================================
المطلوب: PHP 8.1+ (مع pdo_mysql) و MySQL/MariaDB، مثلاً عبر XAMPP.

1) قاعدة البيانات
   - افتحي phpMyAdmin واستوردي الملف database_fixed.sql
   - ثم نفذي:
     CREATE USER 'aishar'@'localhost' IDENTIFIED BY 'CHANGE_ME_password';
     GRANT ALL ON aishar_db.* TO 'aishar'@'localhost';
   (غيّري كلمة السر ثم عدليها في includes/config.php)

2) تشغيل الموقع (من داخل مجلد aishar)
   php -S localhost:8080
   (أو انسخي المجلد إلى htdocs في XAMPP)

3) نشره
   ngrok http 8080
   انسخي الرابط https الذي يظهر. الموقع يكتشف رابطه تلقائياً، فلا تحتاجين تعديل أي شيء.

4) لوحة التحكم:  <رابط ngrok>/admin/login.php
   اسم المستخدم: aishar
   كلمة السر الجديدة: انظري رسالة المحادثة، وغيّريها بعد أول دخول.

ملاحظة: عند الاستضافة الحقيقية ضعي رابط موقعك في $FIXED_SITE_URL داخل includes/config.php.
