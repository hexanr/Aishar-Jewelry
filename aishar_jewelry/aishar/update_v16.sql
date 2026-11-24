-- شغّليه في phpMyAdmin (تبويب SQL) داخل قاعدة aishar_db
-- 1) التوصيل المجاني فوق 75,000
INSERT INTO settings (setting_key, setting_value, setting_group) VALUES
('free_shipping_min','75000','shop'),
('site_facebook','aishar.jewelry','social'),
('site_pinterest','aishar.jewelry','social')
ON DUPLICATE KEY UPDATE setting_value = IF(setting_key='free_shipping_min', VALUES(setting_value), setting_value);

-- 2) تغيير "أطواق" إلى "قلائد"
UPDATE categories
SET name_ar = 'قلائد', description_ar = 'قلائد أنيقة لكل مناسبة'
WHERE slug = 'necklaces';
