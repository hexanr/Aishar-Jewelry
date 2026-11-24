<?php
// admin/settings.php
require_once '../includes/config.php';
$pageTitle = 'الإعدادات';
$db = getDB();

if ($_SERVER['REQUEST_METHOD']==='POST' && verifyCsrf()) {
    foreach ($_POST as $key => $value) {
        if ($key === 'csrf_token') continue;
        $db->prepare("INSERT INTO settings (setting_key, setting_value, setting_group) VALUES (?,?, 'general')
                      ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)")
           ->execute([$key, sanitize($value)]);
    }
    setFlash('success','تم حفظ الإعدادات');
    redirect(SITE_URL.'/admin/settings.php');
}

$settingsList = $db->query("SELECT * FROM settings ORDER BY setting_group, id")->fetchAll();
$settingsMap  = [];
foreach ($settingsList as $s) $settingsMap[$s['setting_key']] = $s['setting_value'];

include 'includes/admin_header.php';
?>

<form method="POST" style="max-width:800px">
    <?= csrfField() ?>

    <!-- General -->
    <div style="background:var(--white);border-radius:var(--radius-lg);padding:1.75rem;box-shadow:var(--shadow);margin-bottom:1.5rem">
        <h3 style="font-size:1rem;margin-bottom:1.5rem;padding-bottom:.75rem;border-bottom:1px solid var(--border)">
            <i class="fa fa-globe" style="color:var(--gold)"></i> معلومات الموقع
        </h3>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">اسم الموقع (عربي)</label>
                <input type="text" name="site_name_ar" class="form-control" value="<?= e($settingsMap['site_name_ar']??'') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Site Name (English)</label>
                <input type="text" name="site_name_en" class="form-control" dir="ltr" value="<?= e($settingsMap['site_name_en']??'') ?>">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">وصف الموقع (عربي)</label>
            <textarea name="site_description_ar" class="form-control" rows="2"><?= e($settingsMap['site_description_ar']??'') ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">البريد الإلكتروني</label>
                <input type="email" name="site_email" class="form-control" dir="ltr" value="<?= e($settingsMap['site_email']??'') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">رقم الهاتف</label>
                <input type="text" name="site_phone" class="form-control" dir="ltr" value="<?= e($settingsMap['site_phone']??'') ?>">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">واتساب (أرقام فقط بدون +)</label>
            <input type="text" name="site_whatsapp" class="form-control" dir="ltr" value="<?= e($settingsMap['site_whatsapp']??'') ?>">
        </div>
    </div>

    <!-- Social -->
    <div style="background:var(--white);border-radius:var(--radius-lg);padding:1.75rem;box-shadow:var(--shadow);margin-bottom:1.5rem">
        <h3 style="font-size:1rem;margin-bottom:1.5rem;padding-bottom:.75rem;border-bottom:1px solid var(--border)">
            <i class="fa fa-share-alt" style="color:var(--gold)"></i> التواصل الاجتماعي
        </h3>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label"><i class="fab fa-instagram" style="color:#E1306C"></i> Instagram</label>
                <input type="text" name="site_instagram" class="form-control" dir="ltr" placeholder="aisharjewelry أو الرابط كامل" value="<?= e($settingsMap['site_instagram']??'') ?>">
            </div>
            <div class="form-group">
                <label class="form-label"><i class="fab fa-snapchat" style="color:#FFFC00"></i> Snapchat</label>
                <input type="text" name="site_snapchat" class="form-control" dir="ltr" value="<?= e($settingsMap['site_snapchat']??'') ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label"><i class="fab fa-tiktok"></i> TikTok</label>
                <input type="text" name="site_tiktok" class="form-control" dir="ltr" value="<?= e($settingsMap['site_tiktok']??'') ?>">
            </div>
            <div class="form-group">
                <label class="form-label"><i class="fab fa-facebook" style="color:#1877F2"></i> Facebook</label>
                <input type="text" name="site_facebook" class="form-control" dir="ltr" placeholder="الصقي رابط الصفحة كامل" value="<?= e($settingsMap['site_facebook']??'') ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label"><i class="fab fa-pinterest" style="color:#E60023"></i> Pinterest</label>
                <input type="text" name="site_pinterest" class="form-control" dir="ltr" placeholder="الصقي الرابط كامل" value="<?= e($settingsMap['site_pinterest']??'') ?>">
            </div>
        </div>
    </div>

    <!-- Shop -->
    <div style="background:var(--white);border-radius:var(--radius-lg);padding:1.75rem;box-shadow:var(--shadow);margin-bottom:1.5rem">
        <h3 style="font-size:1rem;margin-bottom:1.5rem;padding-bottom:.75rem;border-bottom:1px solid var(--border)">
            <i class="fa fa-store" style="color:var(--gold)"></i> إعدادات المتجر
        </h3>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">رمز العملة</label>
                <input type="text" name="currency_symbol" class="form-control" dir="ltr" value="<?= e($settingsMap['currency_symbol']??'د.ع') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">التوصيل المجاني من (د.ع)</label>
                <input type="number" name="free_shipping_min" class="form-control" value="<?= e($settingsMap['free_shipping_min']??'75000') ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">تغليف مميز مجاني من (د.ع)</label>
                <input type="number" name="premium_pack_min" class="form-control" value="<?= e($settingsMap['premium_pack_min']??'30000') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">هدية مجانية من (د.ع)</label>
                <input type="number" name="gift_min" class="form-control" value="<?= e($settingsMap['gift_min']??'125000') ?>">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">رقم زين كاش (يظهر للزبونة بعد الطلب)</label>
            <input type="text" name="zaincash_number" class="form-control" dir="ltr" value="<?= e($settingsMap['zaincash_number']??'') ?>">
        </div>
        <div class="form-group" style="max-width:200px">
            <label class="form-label">تكلفة التوصيل (د.ع)</label>
            <input type="number" name="shipping_cost" class="form-control" value="<?= e($settingsMap['shipping_cost']??'5000') ?>">
        </div>
        <div class="form-group">
            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.9rem">
                <input type="checkbox" name="maintenance_mode" value="1" <?= ($settingsMap['maintenance_mode']??'0')==='1'?'checked':'' ?> style="accent-color:var(--gold)">
                <span style="color:#C0392B;font-weight:600"><i class="fa fa-exclamation-triangle"></i> وضع الصيانة (سيُخفي الموقع عن الزوار)</span>
            </label>
        </div>
    </div>

    <button type="submit" class="btn btn-gold btn-lg">
        <i class="fa fa-save"></i> حفظ كل الإعدادات
    </button>
</form>

<?php include 'includes/admin_footer.php'; ?>
