-- ============================================
-- AISHAR Jewelry — قاعدة البيانات الكاملة
-- DB: aishar_db | Charset: utf8mb4
-- ============================================

CREATE DATABASE IF NOT EXISTS aishar_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE aishar_db;

-- ----------------------------
-- جدول الفئات
-- ----------------------------
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_ar VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    slug VARCHAR(120) UNIQUE NOT NULL,
    description_ar TEXT,
    description_en TEXT,
    image VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------
-- جدول المنتجات
-- ----------------------------
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name_ar VARCHAR(200) NOT NULL,
    name_en VARCHAR(200) NOT NULL,
    slug VARCHAR(220) UNIQUE NOT NULL,
    description_ar TEXT,
    description_en TEXT,
    price DECIMAL(10,2) NOT NULL,
    original_price DECIMAL(10,2) DEFAULT NULL,
    stock INT DEFAULT 0,
    sku VARCHAR(100) UNIQUE,
    material_ar VARCHAR(150),
    material_en VARCHAR(150),
    weight DECIMAL(6,2),
    sizes VARCHAR(100) NULL DEFAULT NULL,
    images JSON,
    tags JSON,
    is_featured TINYINT(1) DEFAULT 0,
    is_new TINYINT(1) DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------
-- جدول الطلبات
-- ----------------------------
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    customer_name VARCHAR(150) NOT NULL,
    customer_email VARCHAR(200) NOT NULL,
    customer_phone VARCHAR(30),
    shipping_address TEXT NOT NULL,
    shipping_city VARCHAR(100),
    shipping_country VARCHAR(100) DEFAULT 'IQ',
    subtotal DECIMAL(10,2) NOT NULL,
    shipping_cost DECIMAL(10,2) DEFAULT 0,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    coupon_code VARCHAR(50),
    payment_method VARCHAR(50) DEFAULT 'cod',
    payment_status ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
    order_status ENUM('pending','confirmed','processing','shipped','delivered','cancelled','returned') DEFAULT 'pending',
    notes TEXT,
    tracking_number VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------
-- جدول عناصر الطلب
-- ----------------------------
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT,
    product_name_ar VARCHAR(200),
    product_name_en VARCHAR(200),
    product_sku VARCHAR(100),
    size VARCHAR(20) NULL DEFAULT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------
-- جدول الكوبونات
-- ----------------------------
CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount_type ENUM('percentage','fixed') DEFAULT 'percentage',
    discount_value DECIMAL(10,2) NOT NULL,
    min_order DECIMAL(10,2) DEFAULT 0,
    max_uses INT DEFAULT NULL,
    used_count INT DEFAULT 0,
    expires_at DATE,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------
-- جدول الرسائل / التواصل
-- ----------------------------
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(200) NOT NULL,
    phone VARCHAR(30),
    subject VARCHAR(250),
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    replied_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------
-- جدول الأدمن
-- ----------------------------
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) UNIQUE NOT NULL,
    email VARCHAR(200) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(150),
    role ENUM('super','admin','editor') DEFAULT 'admin',
    last_login TIMESTAMP NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------
-- جدول الإعدادات
-- ----------------------------
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_group VARCHAR(80) DEFAULT 'general',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------
-- جدول الصور المنزلقة (Slider)
-- ----------------------------
CREATE TABLE IF NOT EXISTS sliders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title_ar VARCHAR(200),
    title_en VARCHAR(200),
    subtitle_ar VARCHAR(300),
    subtitle_en VARCHAR(300),
    button_text_ar VARCHAR(80),
    button_text_en VARCHAR(80),
    button_url VARCHAR(255),
    image VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- ----------------------------
-- جدول سجل الزيارات (بسيط)
-- ----------------------------
CREATE TABLE IF NOT EXISTS page_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page VARCHAR(200),
    product_id INT DEFAULT NULL,
    ip_address VARCHAR(45),
    visited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- البيانات الافتراضية
-- ============================================

-- الأدمن
INSERT INTO admins (username, email, password, full_name, role) VALUES
('aishar', 'admin@example.com', 'REPLACE_WITH_BCRYPT_HASH', 'مديرة آيشار', 'super');
-- ضعي هاش كلمة السر بنفسك باستخدام password_hash() في PHP

-- الفئات
INSERT INTO categories (name_ar, name_en, slug, description_ar, description_en, image, sort_order) VALUES
('قلائد','Necklaces','necklaces','قلائد أنيقة لكل مناسبة','Elegant necklaces for every occasion','assets/images/uploads/products/pearl-heart-necklace.jpg',1),
('أساور','Bracelets','bracelets','أساور بطلاء ذهب 18k','18K gold-plated bracelets','assets/images/uploads/products/snake-cuff.jpg',2),
('خواتم','Rings','rings','خواتم زركون ولؤلؤ بتصاميم عصرية','Modern zircon and pearl rings','assets/images/uploads/products/zircon-pearl-rings.jpg',3),
('أقراط','Earrings','earrings','أقراط بأشكال متنوعة','Earrings in various styles','assets/images/uploads/products/long-zircon-earrings.jpg',4),
('طقم كامل','Sets','sets','سيتات متكاملة بتغليف أنيق','Complete sets in elegant packaging','assets/images/uploads/products/curb-chain-set.jpg',5),
('شباحيات','Hand Chains','hand-chains','شباحيات وسلاسل يد بالزركون واللؤلؤ','Hand chains with zircon and pearls','assets/images/uploads/products/sun-moon-handchain.jpg',6),
('ساعات','Watches','watches','ساعات أنيقة بلمسة ذهبية','Elegant watches with a golden touch',NULL,7);

-- المنتجات
INSERT INTO products (category_id, name_ar, name_en, slug, description_ar, description_en, price, original_price, stock, sku, material_ar, material_en, images, is_featured, is_new) VALUES
(2,'سوار أفعى مرصع بالزركون','Zircon Snake Cuff','zircon-snake-cuff','سوار مفتوح بتصميم الأفعى مرصع بالزركون اللامع، بطلاء ذهب 18k.','Open snake-design cuff set with sparkling zircon, 18K gold plated.',25000,NULL,10,'BR101','طلاء ذهب 18k','18K gold plated','["products/snake-cuff.jpg", "products/chain-ball-stack.jpg"]',1,1),
(6,'شباحية الشمس والقمر','Sun & Moon Hand Chain','sun-moon-hand-chain','شباحية ناعمة بتفاصيل الشمس والقمر والنجمة، تنسق مع الخواتم والأساور.','Delicate hand chain with sun, moon and star details.',25000,NULL,10,'HC101','طلاء ذهب 18k','18K gold plated','["products/sun-moon-handchain.jpg"]',1,1),
(2,'سوار سلسلة بكرات ذهبية','Gold Ball Charm Chain Bracelet','gold-ball-chain-bracelet','سوار سلسلة عريضة بتعليقات كروية ذهبية، بطلاء ذهب 18k.','Chunky chain bracelet with gold ball charms, 18K gold plated.',25000,NULL,10,'BR102','طلاء ذهب 18k','18K gold plated','["products/chain-ball-stack.jpg"]',0,1),
(5,'سيت سلسلة كوبية وأقراط','Curb Chain & Earrings Set','curb-chain-set','سيت أنيق يشمل سوار سلسلة كوبية وأقراط وسلسلة ناعمة بتغليف فاخر.','Elegant set with curb chain bracelet, earrings and fine chain in gift packaging.',25000,NULL,10,'SE101','طلاء ذهب 18k','18K gold plated','["products/curb-chain-set.jpg"]',1,1),
(5,'سيت قلادة وأقراط بالزركون','Zircon Necklace & Earrings Set','zircon-necklace-set','سيت قلادة سلسلة عريضة مع أقراط وسوار بقطع زركون مستطيلة.','Wide chain necklace with earrings and bracelet in rectangular zircon.',25000,NULL,10,'SE102','طلاء ذهب 18k','18K gold plated','["products/zircon-set.jpg"]',0,1),
(5,'سيت لؤلؤ وقلب','Pearl & Heart Set','pearl-heart-set','سيت يجمع سوار سلسلة بقلب ذهبي وسوار لؤلؤ بتفاصيل القمر والنجمة.','A set with a gold heart chain bracelet and pearl bracelet with moon and star charms.',25000,NULL,10,'SE103','طلاء ذهب 18k','18K gold plated','["products/pearl-heart-set.jpg"]',1,1),
(3,'خواتم زركون ولؤلؤ','Zircon & Pearl Rings','zircon-pearl-rings','خواتم مفتوحة بقطع زركون ولؤلؤ بتصاميم عصرية قابلة للتعديل.','Open rings with zircon and pearls in modern designs.',25000,NULL,10,'RI101','طلاء ذهب 18k','18K gold plated','["products/zircon-pearl-rings.jpg"]',1,0),
(4,'أقراط زركون طويلة','Long Zircon Drop Earrings','long-zircon-earrings','أقراط طويلة بقطع زركون مستطيلة تعطيك إطلالة سهرة.','Long drop earrings with rectangular zircon for an evening look.',25000,NULL,10,'EA101','طلاء ذهب 18k','18K gold plated','["products/long-zircon-earrings.jpg"]',1,1),
(6,'شباحية زركون','Zircon Hand Chain','zircon-hand-chain','شباحية ذهبية رقيقة بنقاط زركون لامعة.','Fine gold hand chain with sparkling zircon drops.',25000,NULL,10,'HC102','طلاء ذهب 18k','18K gold plated','["products/zircon-handchain.jpg"]',0,1),
(6,'شباحية الزمرد الأخضر','Emerald Green Hand Chain','emerald-hand-chain','شباحية ذهبية بحجارة خضراء مع خاتم عريض بالزركون.','Gold hand chain with green stones and a wide zircon ring.',25000,NULL,10,'HC103','طلاء ذهب 18k','18K gold plated','["products/emerald-handchain.jpg"]',0,1),
(6,'شباحية زركون لامعة','Sparkle Zircon Hand Chain','sparkle-hand-chain','شباحية من سلسلة الزركون اللامع تنسق مع أساور اللؤلؤ.','Sparkling zircon hand chain that pairs with pearl bracelets.',25000,NULL,10,'HC104','طلاء ذهب 18k','18K gold plated','["products/tennis-handchain.jpg"]',0,0),
(1,'قلادة قلب لؤلؤ وفراشة','Pearl Heart & Butterfly Necklace','pearl-heart-necklace','قلادة بقلب لؤلؤي مع فراشة ذهبية ولؤلؤة صغيرة.','Necklace with a pearl heart, gold butterfly and small pearl.',25000,NULL,10,'NE101','طلاء ذهب 18k','18K gold plated','["products/pearl-heart-necklace.jpg"]',1,1);

-- الإعدادات
INSERT INTO settings (setting_key, setting_value, setting_group) VALUES
('site_name_ar', 'آيشار للإكسسوارات', 'general'),
('site_name_en', 'AISHAR Jewelry', 'general'),
('site_description_ar', 'إكسسوارات عصرية بطلاء ذهب 18k تعكس أناقتك', 'general'),
('site_description_en', 'Modern 18K gold-plated accessories that reflect your elegance', 'general'),
('site_email', 'admin@example.com', 'general'),
('site_phone', '+964 772 485 0155', 'general'),
('site_whatsapp', '+000000000000', 'general'),
('site_instagram', 'https://www.instagram.com/aisharjewelry', 'social'),
('site_snapchat', 'aishar.jewelry', 'social'),
('site_tiktok', 'https://www.tiktok.com/@aisharjewelry', 'social'),
('site_facebook', 'https://www.facebook.com/share/1DAp7nBRPd/?mibextid=wwXIfr', 'social'),
('site_pinterest', 'https://pin.it/3S2xQx42e', 'social'),
('currency', 'IQD', 'shop'),
('currency_symbol', 'د.ع', 'shop'),
('free_shipping_min', '75000', 'shop'),
('gift_min', '125000', 'shop'),
('premium_pack_min', '30000', 'shop'),
('zaincash_number', '', 'shop'),
('shipping_cost', '5000', 'shop'),
('default_language', 'ar', 'general'),
('maintenance_mode', '0', 'general'),
('meta_keywords', 'إكسسوارات,طلاء ذهب,آيشار,خواتم,أساور', 'seo');

-- الكوبونات
INSERT INTO coupons (code, discount_type, discount_value, min_order, expires_at) VALUES
('AISHAR10', 'percentage', 10, 500, '2027-12-31'),
('WELCOME50', 'fixed', 50, 300, '2027-12-31');
