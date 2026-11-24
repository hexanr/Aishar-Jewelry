<?php
require_once '../includes/config.php';
$pageTitle = 'المجموعات';
$db = getDB();
$action = sanitize($_GET['action'] ?? 'list');
$id     = (int)($_GET['id'] ?? 0);

if ($action === 'delete' && $id && verifyCsrf()) {
    $db->prepare("DELETE FROM collections WHERE id=?")->execute([$id]);
    setFlash('success', 'تم حذف المجموعة');
    redirect(SITE_URL . '/admin/collections.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    $data = [
        'name_ar'        => sanitize($_POST['name_ar'] ?? ''),
        'name_en'        => sanitize($_POST['name_en'] ?? ''),
        'slug'           => makeSlug(sanitize($_POST['slug'] ?? $_POST['name_en'] ?? '')),
        'description_ar' => sanitize($_POST['description_ar'] ?? ''),
        'description_en' => sanitize($_POST['description_en'] ?? ''),
        'sort_order'     => (int)($_POST['sort_order'] ?? 0),
        'is_active'      => isset($_POST['is_active']) ? 1 : 0,
    ];
    if ($action === 'add') {
        $db->prepare("INSERT INTO collections (name_ar,name_en,slug,description_ar,description_en,sort_order,is_active) VALUES (:name_ar,:name_en,:slug,:description_ar,:description_en,:sort_order,:is_active)")->execute($data);
        setFlash('success', 'تمت الإضافة');
    } else {
        $sets = implode(',', array_map(fn($k) => "$k=:$k", array_keys($data)));
        $data['id'] = $id;
        $db->prepare("UPDATE collections SET $sets WHERE id=:id")->execute($data);
        setFlash('success', 'تم التحديث');
    }
    redirect(SITE_URL . '/admin/collections.php');
}

$editCol = null;
if ($action === 'edit' && $id) {
    $s = $db->prepare("SELECT * FROM collections WHERE id=?"); $s->execute([$id]);
    $editCol = $s->fetch();
    $pageTitle = 'تعديل: ' . ($editCol['name_ar'] ?? '');
}
if ($action === 'add') $pageTitle = 'إضافة مجموعة';

$collections = $db->query("SELECT * FROM collections ORDER BY sort_order")->fetchAll();
include 'includes/admin_header.php';

if (in_array($action, ['add', 'edit'])): ?>
<div style="max-width:600px">
    <a href="collections.php" class="btn btn-outline btn-sm" style="margin-bottom:1.5rem"><i class="fa fa-arrow-right"></i> العودة</a>
    <div style="background:var(--white);border-radius:var(--radius-lg);padding:1.75rem;box-shadow:var(--shadow)">
        <form method="POST">
            <?= csrfField() ?>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">الاسم (عربي) *</label>
                    <input type="text" name="name_ar" class="form-control" required value="<?= e($editCol['name_ar'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Name (English)</label>
                    <input type="text" name="name_en" class="form-control" dir="ltr" value="<?= e($editCol['name_en'] ?? '') ?>"
                           oninput="this.form.slug.value=this.value.toLowerCase().replace(/[^a-z0-9]+/g,'-')">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-control" dir="ltr" value="<?= e($editCol['slug'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">الوصف</label>
                <textarea name="description_ar" class="form-control" rows="3"><?= e($editCol['description_ar'] ?? '') ?></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">ترتيب العرض</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= $editCol['sort_order'] ?? 0 ?>">
                </div>
                <div class="form-group" style="display:flex;align-items:end;padding-bottom:.4rem">
                    <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
                        <input type="checkbox" name="is_active" <?= ($editCol['is_active'] ?? 1) ? 'checked' : '' ?> style="accent-color:var(--gold)">
                        نشطة
                    </label>
                </div>
            </div>
            <button type="submit" class="btn btn-gold w-100">
                <i class="fa fa-save"></i> <?= $action === 'add' ? 'إضافة' : 'حفظ' ?>
            </button>
        </form>
    </div>
</div>

<?php else: ?>
<div style="display:flex;justify-content:space-between;margin-bottom:1.5rem">
    <div></div>
    <a href="collections.php?action=add" class="btn btn-gold"><i class="fa fa-plus"></i> إضافة مجموعة</a>
</div>
<div class="data-table-wrap">
    <div class="table-header"><h3>المجموعات (<?= count($collections) ?>)</h3></div>
    <table class="data-table">
        <thead><tr><th>الاسم</th><th>Slug</th><th>الحالة</th><th>الترتيب</th><th>إجراءات</th></tr></thead>
        <tbody>
            <?php foreach ($collections as $col): ?>
                <tr>
                    <td><strong><?= e($col['name_ar']) ?></strong><div style="font-size:.75rem;color:var(--text-muted)"><?= e($col['name_en']) ?></div></td>
                    <td style="direction:ltr;text-align:right;font-size:.82rem;color:var(--text-muted)"><?= e($col['slug']) ?></td>
                    <td><span class="status-badge <?= $col['is_active'] ? 'status-active' : 'status-inactive' ?>"><?= $col['is_active'] ? 'نشطة' : 'مخفية' ?></span></td>
                    <td><?= $col['sort_order'] ?></td>
                    <td>
                        <div style="display:flex;gap:.5rem">
                            <a href="collections.php?action=edit&id=<?= $col['id'] ?>" class="btn btn-outline btn-sm"><i class="fa fa-edit"></i></a>
                            <form method="POST" action="collections.php?action=delete&id=<?= $col['id'] ?>" onsubmit="return confirmDelete('حذف المجموعة؟')">
                                <?= csrfField() ?>
                                <button type="submit" class="btn btn-sm" style="background:rgba(192,57,43,.1);color:#C0392B;border:1px solid rgba(192,57,43,.2)"><i class="fa fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php include 'includes/admin_footer.php'; ?>
