# AISHAR Jewelry — تعليمات التثبيت

## هيكل المجلدات
```
aishar/
├── index.php, shop.php, ...   ← صفحات الموقع
├── includes/
│   ├── config.php             ← الإعدادات الرئيسية ★
│   ├── header.php
│   └── footer.php
├── admin/                     ← لوحة التحكم
│   ├── includes/
│   │   ├── admin_header.php
│   │   └── admin_footer.php
│   └── *.php
├── ajax/cart.php              ← AJAX للسلة
├── assets/
│   ├── css/style.css          ← الـ CSS الرئيسي ★
│   └── images/uploads/        ← مجلد الصور
├── .htaccess
└── database.sql               ← قاعدة البيانات
```

## خطوات التثبيت

### 1. قاعدة البيانات
```sql
CREATE DATABASE aishar_db CHARACTER SET utf8mb4;
-- ثم استورد ملف database.sql
```

### 2. إعدادات config.php
افتحي `includes/config.php` وعدّلي:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');
define('SITE_URL', 'http://localhost/aishar'); // ← غيري هذا
```

### 3. صلاحيات المجلدات
```bash
chmod 755 assets/images/uploads/
chmod 755 assets/images/uploads/products/
```

### 4. تسجيل الدخول للأدمن
- الرابط: `/admin/login.php`
- اسم المستخدم: `aishar`
- كلمة المرور: `CHANGE_ME_password`

## ملاحظة مهمة
غيري كلمة مرور الأدمن فور التثبيت من لوحة التحكم!
