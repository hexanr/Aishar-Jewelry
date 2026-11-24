-- v27: قياسات الخواتم  (شغّليه في phpMyAdmin > SQL على قاعدة aishar_db)
-- ملاحظة: الموقع يضيف هذه الأعمدة تلقائياً أول مرة، فهذا الملف احتياطي فقط.
ALTER TABLE products    ADD COLUMN sizes VARCHAR(100) NULL DEFAULT NULL AFTER weight;
ALTER TABLE order_items ADD COLUMN size  VARCHAR(20)  NULL DEFAULT NULL AFTER product_sku;

-- مثال: خلّي كل الخواتم بقياسات 6,7,8,9
-- UPDATE products SET sizes='6,7,8,9' WHERE category_id=(SELECT id FROM categories WHERE slug='rings');
-- مثال: خاتم فري سايز
-- UPDATE products SET sizes=NULL WHERE slug='zircon-pearl-rings';
