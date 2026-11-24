-- شغّليه في phpMyAdmin (تبويب SQL) داخل قاعدة aishar_db إذا كنتي مستوردة القاعدة من قبل
INSERT INTO settings (setting_key, setting_value, setting_group) VALUES
('free_shipping_min','75000','shop'),
('gift_min','125000','shop'),
('premium_pack_min','30000','shop'),
('zaincash_number','','shop')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
