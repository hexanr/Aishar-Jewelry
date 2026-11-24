<?php
require_once '../includes/config.php';
$pageTitle = 'الكوبونات';
$db = getDB();
$action = sanitize($_GET['action'] ?? 'list');
$id     = (int)($_GET['id'] ?? 0);

// حذف
if ($action === 'delete' && $id && verifyCsrf()) {
    $db->prepare("DELETE FROM coupons WHERE id=?")->execute([$id]);
    setFlash('success', 'تم حذف الكوبون');
    redirect(SITE_URL . '/admin/coupons.php');
}

// حفظ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    $data = [
        'code'           => strtoupper(sanitize($_POST['code'] ?? '')),
        'discount_type'  => sanitize($_POST['discount_type'] ?? 'percentage'),
        'discount_value' => (float)($_POST['discount_value'] ?? 0),
        'min_order'      => (float)($_POST['min_order'] ?? 0),
        'max_uses'       => $_POST['max_uses'] ? (int)$_POST['max_uses'] : null,
        'expires_at'     => $_POST['expires_at'] ?: null,
        'is_active'      => isset($_POST['is_active']) ? 1 : 0,
    ];
    if ($action === 'add') {
        $db->prepare("INSERT INTO coupons (code,discount_type,discount_value,min_order,max_uses,expires_at,is_active) VALUES (:code,:discount_type,:discount_value,:min_order,:max_uses,:expires_at,:is_active)")->execute($data);
        setFlash('success', 'تمت إضافة الكوبون');
    } else {
        $sets = implode(',', array_map(fn($k) => "$k=:$k", array_keys($data)));
        $data['id'] = $id;
        $db->prepare("UPDATE coupons SET $sets WHERE id=:id")->execute($data);
        setFlash('success', 'تم تحديث الكوبون');
    }
    redirect(SITE_URL . '/admin/coupons.php');
}

$editCoupon = null;
if ($action === 'edit' && $id) {
    $s = $db->prepare("SELECT * FROM coupons WHERE id=?"); $s->execute([$id]);
    $editCoupon = $s->fetch();
    $pageTitle = 'تعديل كوبون';
}
if ($action === 'add') $pageTitle = 'إضافة كوبون جديد';

$coupons = $db->query("SELECT * FROM coupons ORDER BY created_at DESC")->fetchAll();
include 'includes/admin_header.php';

if (in_array($action, ['add', 'edit'])): ?>
<div style="max-width:560px">
    <div style="margin-bottom:1.5rem">
        <a href="coupons.php" class="btn btn-outline btn-sm"><i class="fa fa-arrow-right"></i> العودة</a>
    </div>
    <div style="background:var(--white);border-radius:var(--radius-lg);padding:1.75rem;box-shadow:var(--shadow)">
        <form method="POST">
            <?= csrfField() ?>
            <div class="form-group">
                <label class="form-label">كود الخصم *</label>
                <input type="text" name="code" class="form-control" dir="ltr" required
                       value="<?= e($editCoupon['code'] ?? '') ?>"
                       placeholder="AISHAR10"
                       oninput="this.value=this.value.toUpperCase()"
                       style="font-weight:700;letter-spacing:.1em;font-size:1.1rem">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">نوع الخصم</label>
                    <select name="discount_type" class="form-control">
                        <option value="percentage" <?= ($editCoupon['discount_type'] ?? '') === 'percentage' ? 'selected' : '' ?>>نسبة مئوية %</option>
                        <option value="fixed"      <?= ($editCoupon['discount_type'] ?? '') === 'fixed'      ? 'selected' : '' ?>>مبلغ ثابت (ريال)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">قيمة الخصم *</label>
                    <input type="number" name="discount_value" class="form-control" step="0.01" required
                           value="<?= $editCoupon['discount_value'] ?? '' ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">الحد الأدنى للطلب (ريال)</label>
                    <input type="number" name="min_order" class="form-control" step="0.01"
                           value="<?= $editCoupon['min_order'] ?? 0 ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">الحد الأقصى للاستخدام</label>
                    <input type="number" name="max_uses" class="form-control"
                           value="<?= $editCoupon['max_uses'] ?? '' ?>" placeholder="بلا حد">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">تاريخ الانتهاء</label>
                <input type="date" name="expires_at" class="form-control" dir="ltr"
                       value="<?= $editCoupon['expires_at'] ?? '' ?>">
            </div>
            <div class="form-group">
                <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
                    <input type="checkbox" name="is_active" <?= ($editCoupon['is_active'] ?? 1) ? 'checked' : '' ?> style="accent-color:var(--gold)">
                    كوبون نشط
                </label>
            </div>
            <button type="submit" class="btn btn-gold w-100">
                <i class="fa fa-save"></i> <?= $action === 'add' ? 'إضافة الكوبون' : 'حفظ التعديلات' ?>
            </button>
        </form>
    </div>
</div>

<?php else: ?>
<div style="display:flex;justify-content:space-between;margin-bottom:1.5rem">
    <div></div>
    <a href="coupons.php?action=add" class="btn btn-gold"><i class="fa fa-plus"></i> إضافة كوبون</a>
</div>

<div class="data-table-wrap">
    <div class="table-header"><h3>الكوبونات (<?= count($coupons) ?>)</h3></div>
    <table class="data-table">
        <thead><tr>
            <th>الكود</th><th>نوع الخصم</th><th>القيمة</th><th>الحد الأدنى</th>
            <th>الاستخدام</th><th>ينتهي</th><th>الحالة</th><th>إجراءات</th>
        </tr></thead>
        <tbody>
            <?php foreach ($coupons as $c):
                $isExpired = $c['expires_at'] && $c['expires_at'] < date('Y-m-d');
            ?>
                <tr>
                    <td style="font-weight:700;letter-spacing:.1em;color:var(--gold);direction:ltr"><?= e($c['code']) ?></td>
                    <td><?= $c['discount_type'] === 'percentage' ? 'نسبة %' : 'مبلغ ثابت' ?></td>
                    <td style="font-weight:600">
                        <?= $c['discount_type'] === 'percentage'
                            ? $c['discount_value'] . '%'
                            : formatPrice((float)$c['discount_value']) ?>
                    </td>
                    <td><?= $c['min_order'] > 0 ? formatPrice((float)$c['min_order']) : '—' ?></td>
                    <td>
                        <?= $c['used_count'] ?>
                        <?= $c['max_uses'] ? ' / ' . $c['max_uses'] : '' ?>
                    </td>
                    <td style="font-size:.82rem;<?= $isExpired ? 'color:#C0392B' : '' ?>">
                        <?= $c['expires_at'] ? date('d/m/Y', strtotime($c['expires_at'])) : '—' ?>
                        <?= $isExpired ? ' <span style="color:#C0392B">(منتهي)</span>' : '' ?>
                    </td>
                    <td>
                        <span class="status-badge <?= ($c['is_active'] && !$isExpired) ? 'status-active' : 'status-inactive' ?>">
                            <?= ($c['is_active'] && !$isExpired) ? 'نشط' : 'غير نشط' ?>
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:.5rem">
                            <a href="coupons.php?action=edit&id=<?= $c['id'] ?>" class="btn btn-outline btn-sm"><i class="fa fa-edit"></i></a>
                            <form method="POST" action="coupons.php?action=delete&id=<?= $c['id'] ?>" onsubmit="return confirmDelete('حذف الكوبون؟')">
                                <?= csrfField() ?>
                                <button type="submit" class="btn btn-sm" style="background:rgba(192,57,43,.1);color:#C0392B;border:1px solid rgba(192,57,43,.2)"><i class="fa fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($coupons)): ?>
                <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted)">لا توجد كوبونات</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php include 'includes/admin_footer.php'; ?>
