-- v31: روابط السوشيال الرسمية (شغّليه في phpMyAdmin > SQL، اختياري — الموقع يستخدمها تلقائياً لو القيمة القديمة)
INSERT INTO settings (setting_key, setting_value, setting_group) VALUES
('site_instagram','https://www.instagram.com/aisharjewelry','social'),
('site_tiktok','https://www.tiktok.com/@aisharjewelry','social'),
('site_facebook','https://www.facebook.com/share/1DAp7nBRPd/?mibextid=wwXIfr','social'),
('site_pinterest','https://pin.it/3S2xQx42e','social')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
