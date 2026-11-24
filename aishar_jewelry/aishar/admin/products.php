<?php
require_once '../includes/config.php';
ensureSizeColumns();
$pageTitle = 'إدارة المنتجات';

$db  = getDB();
$action = sanitize($_GET['action'] ?? 'list');
$id     = (int)($_GET['id'] ?? 0);

// ---- حذف ----
if ($action === 'delete' && $id) {
    requireAdmin();
    if (!verifyCsrf()) { setFlash('error','طلب غير صالح'); }
    else {
        $db->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
        setFlash('success','تم حذف المنتج');
    }
    redirect(SITE_URL . '/admin/products.php');
}

// ---- حفظ (إضافة / تعديل) ----
if ($_SERVER['REQUEST_METHOD']==='POST' && in_array($action,['add','edit'])) {
    requireAdmin();
    if (!verifyCsrf()) { setFlash('error','طلب غير صالح'); redirect(SITE_URL.'/admin/products.php'); }

    $data = [
        'category_id'    => (int)$_POST['category_id'] ?: null,
        'name_ar'        => sanitize($_POST['name_ar'] ?? ''),
        'name_en'        => sanitize($_POST['name_en'] ?? ''),
        'slug'           => makeSlug(sanitize($_POST['slug'] ?? $_POST['name_en'] ?? '')),
        'description_ar' => sanitize($_POST['description_ar'] ?? ''),
        'description_en' => sanitize($_POST['description_en'] ?? ''),
        'price'          => (float)($_POST['price'] ?? 0),
        'original_price' => $_POST['original_price'] ? (float)$_POST['original_price'] : null,
        'stock'          => (int)($_POST['stock'] ?? 0),
        'sku'            => sanitize($_POST['sku'] ?? '') ?: null,
        'material_ar'    => sanitize($_POST['material_ar'] ?? ''),
        'material_en'    => sanitize($_POST['material_en'] ?? ''),
        'weight'         => $_POST['weight'] ? (float)$_POST['weight'] : null,
        'sizes'          => (($_POST['size_mode'] ?? 'free') === 'sizes')
                              ? (implode(',', parseSizes(sanitize($_POST['sizes'] ?? ''))) ?: null)
                              : null,
        'is_featured'    => isset($_POST['is_featured']) ? 1 : 0,
        'is_new'         => isset($_POST['is_new']) ? 1 : 0,
        'is_active'      => isset($_POST['is_active']) ? 1 : 0,
    ];

    // رفع الصور
    $existingImages = json_decode($_POST['existing_images'] ?? '[]', true);
    $uploadedImages = $existingImages;
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['name'] as $k => $name) {
            if ($_FILES['images']['error'][$k] === 0) {
                $file = [
                    'name'     => $name,
                    'type'     => $_FILES['images']['type'][$k],
                    'tmp_name' => $_FILES['images']['tmp_name'][$k],
                    'size'     => $_FILES['images']['size'][$k],
                ];
                $uploaded = uploadImage($file, 'products');
                if ($uploaded) $uploadedImages[] = $uploaded;
            }
        }
    }
    $data['images'] = json_encode($uploadedImages);

    if ($action === 'add') {
        $db->prepare("
            INSERT INTO products (category_id,name_ar,name_en,slug,description_ar,description_en,
                price,original_price,stock,sku,material_ar,material_en,weight,sizes,images,
                is_featured,is_new,is_active)
            VALUES (:category_id,:name_ar,:name_en,:slug,:description_ar,:description_en,
                :price,:original_price,:stock,:sku,:material_ar,:material_en,:weight,:sizes,:images,
                :is_featured,:is_new,:is_active)
        ")->execute($data);
        setFlash('success','تم إضافة المنتج بنجاح');
    } else {
        $sets = implode(',', array_map(fn($k) => "$k=:$k", array_keys($data)));
        $data['id'] = $id;
        $db->prepare("UPDATE products SET $sets WHERE id=:id")->execute($data);
        setFlash('success','تم تحديث المنتج');
    }
    redirect(SITE_URL . '/admin/products.php');
}

// ---- جلب المنتج للتعديل ----
$product = null;
if ($action === 'edit' && $id) {
    $product = $db->prepare("SELECT * FROM products WHERE id=?");
    $product->execute([$id]);
    $product = $product->fetch();
    if (!$product) { setFlash('error','المنتج غير موجود'); redirect(SITE_URL.'/admin/products.php'); }
    $pageTitle = 'تعديل: ' . $product['name_ar'];
}
if ($action === 'add') $pageTitle = 'إضافة منتج جديد';

// ---- قائمة المنتجات ----
$categories = $db->query("SELECT * FROM categories WHERE is_active=1 ORDER BY sort_order")->fetchAll();

include 'includes/admin_header.php';

if (in_array($action, ['add','edit'])):
    $imgs = json_decode($product['images'] ?? '[]', true);
?>

<!-- ===== FORM ===== -->
<div style="max-width:900px">
    <div style="margin-bottom:1.5rem">
        <a href="products.php" class="btn btn-outline btn-sm">
            <i class="fa fa-arrow-right"></i> العودة للمنتجات
        </a>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        <input type="hidden" name="existing_images" value='<?= e(json_encode($imgs)) ?>'>

        <div style="display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start">

            <!-- Main Fields -->
            <div style="display:flex;flex-direction:column;gap:1.25rem">

                <div style="background:var(--white);border-radius:var(--radius-lg);padding:1.75rem;box-shadow:var(--shadow)">
                    <h3 style="font-size:1rem;margin-bottom:1.25rem;padding-bottom:.75rem;border-bottom:1px solid var(--border)">المعلومات الأساسية</h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">اسم المنتج (عربي) *</label>
                            <input type="text" name="name_ar" class="form-control" required
                                   value="<?= e($product['name_ar']??'') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Product Name (English)</label>
                            <input type="text" name="name_en" class="form-control"
                                   value="<?= e($product['name_en']??'') ?>"
                                   oninput="this.form.slug.value=this.value.toLowerCase().replace(/[^a-z0-9]+/g,'-')">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Slug (URL)</label>
                        <input type="text" name="slug" class="form-control"
                               value="<?= e($product['slug']??'') ?>" dir="ltr">
                    </div>

                    <div class="form-group">
                        <label class="form-label">الوصف (عربي)</label>
                        <textarea name="description_ar" class="form-control" rows="4"><?= e($product['description_ar']??'') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description (English)</label>
                        <textarea name="description_en" class="form-control" rows="4" dir="ltr"><?= e($product['description_en']??'') ?></textarea>
                    </div>
                </div>

                <!-- Pricing -->
                <div style="background:var(--white);border-radius:var(--radius-lg);padding:1.75rem;box-shadow:var(--shadow)">
                    <h3 style="font-size:1rem;margin-bottom:1.25rem;padding-bottom:.75rem;border-bottom:1px solid var(--border)">السعر والمخزون</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">السعر الحالي (ريال) *</label>
                            <input type="number" name="price" step="0.01" class="form-control" required
                                   value="<?= $product['price']??'' ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">السعر الأصلي (قبل الخصم)</label>
                            <input type="number" name="original_price" step="0.01" class="form-control"
                                   value="<?= $product['original_price']??'' ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">الكمية في المخزون</label>
                            <input type="number" name="stock" class="form-control"
                                   value="<?= $product['stock']??0 ?>" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">كود المنتج (SKU)</label>
                            <input type="text" name="sku" class="form-control" dir="ltr"
                                   value="<?= e($product['sku']??'') ?>">
                        </div>
                    </div>
                </div>

                <!-- Details -->
                <div style="background:var(--white);border-radius:var(--radius-lg);padding:1.75rem;box-shadow:var(--shadow)">
                    <h3 style="font-size:1rem;margin-bottom:1.25rem;padding-bottom:.75rem;border-bottom:1px solid var(--border)">تفاصيل إضافية</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">المادة (عربي)</label>
                            <input type="text" name="material_ar" class="form-control"
                                   value="<?= e($product['material_ar']??'') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Material (English)</label>
                            <input type="text" name="material_en" class="form-control" dir="ltr"
                                   value="<?= e($product['material_en']??'') ?>">
                        </div>
                    </div>
                    <div class="form-group" style="max-width:200px">
                        <label class="form-label">الوزن (جرام)</label>
                        <input type="number" name="weight" step="0.01" class="form-control"
                               value="<?= $product['weight']??'' ?>">
                    </div>

                    <?php $curSizes = trim((string)($product['sizes'] ?? '')); ?>
                    <div class="form-group" style="border:1px dashed var(--border);border-radius:var(--radius);padding:1rem">
                        <label class="form-label">قياس الخاتم</label>
                        <div style="display:flex;gap:1.5rem;flex-wrap:wrap;margin-bottom:.6rem">
                            <label style="cursor:pointer"><input type="radio" name="size_mode" value="free" <?= $curSizes===''?'checked':'' ?> onchange="document.getElementById('sizesBox').style.display='none'"> فري سايز (بدون اختيار قياس)</label>
                            <label style="cursor:pointer"><input type="radio" name="size_mode" value="sizes" <?= $curSizes!==''?'checked':'' ?> onchange="document.getElementById('sizesBox').style.display='block'"> له قياسات</label>
                        </div>
                        <div id="sizesBox" style="display:<?= $curSizes!==''?'block':'none' ?>">
                            <input type="text" name="sizes" class="form-control" dir="ltr" placeholder="6,7,8,9"
                                   value="<?= e($curSizes !== '' ? $curSizes : '6,7,8,9') ?>">
                            <div class="form-hint">اكتبي القياسات مفصولة بفاصلة، مثال: 6,7,8,9 — تظهر للزبونة كأزرار تختار منها.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Fields -->
            <div style="display:flex;flex-direction:column;gap:1.25rem">

                <!-- Publish -->
                <div style="background:var(--white);border-radius:var(--radius-lg);padding:1.5rem;box-shadow:var(--shadow)">
                    <h3 style="font-size:1rem;margin-bottom:1.25rem;padding-bottom:.75rem;border-bottom:1px solid var(--border)">النشر</h3>
                    <div style="display:flex;flex-direction:column;gap:.75rem;margin-bottom:1.25rem">
                        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.9rem">
                            <input type="checkbox" name="is_active" <?= ($product['is_active']??1)?'checked':'' ?> style="accent-color:var(--gold)">
                            نشط (مرئي في المتجر)
                        </label>
                        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.9rem">
                            <input type="checkbox" name="is_featured" <?= ($product['is_featured']??0)?'checked':'' ?> style="accent-color:var(--gold)">
                            منتج مميز
                        </label>
                        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.9rem">
                            <input type="checkbox" name="is_new" <?= ($product['is_new']??1)?'checked':'' ?> style="accent-color:var(--gold)">
                            وصل حديثاً
                        </label>
                    </div>
                    <button type="submit" class="btn btn-gold w-100">
                        <i class="fa fa-save"></i>
                        <?= $action==='add'?'إضافة المنتج':'حفظ التعديلات' ?>
                    </button>
                </div>

                <!-- Category -->
                <div style="background:var(--white);border-radius:var(--radius-lg);padding:1.5rem;box-shadow:var(--shadow)">
                    <h3 style="font-size:1rem;margin-bottom:1rem;padding-bottom:.75rem;border-bottom:1px solid var(--border)">الفئة</h3>
                    <select name="category_id" class="form-control">
                        <option value="">بدون فئة</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"
                                <?= ($product['category_id']??0)==$cat['id']?'selected':'' ?>>
                                <?= e($cat['name_ar']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Images -->
                <div style="background:var(--white);border-radius:var(--radius-lg);padding:1.5rem;box-shadow:var(--shadow)">
                    <h3 style="font-size:1rem;margin-bottom:1rem;padding-bottom:.75rem;border-bottom:1px solid var(--border)">الصور</h3>

                    <?php if (!empty($imgs)): ?>
                        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem;margin-bottom:1rem">
                            <?php foreach ($imgs as $img): ?>
                                <div style="aspect-ratio:1;border-radius:var(--radius);overflow:hidden;border:2px solid var(--border)">
                                    <img src="<?= e(UPLOAD_URL.$img) ?>" style="width:100%;height:100%;object-fit:cover"
                                         onerror="this.src='https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=100&q=60'">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <label style="display:block;border:2px dashed var(--border);border-radius:var(--radius);padding:1.5rem;text-align:center;cursor:pointer;transition:border-color var(--transition)"
                           onmouseover="this.style.borderColor='var(--gold)'" onmouseout="this.style.borderColor='var(--border)'">
                        <i class="fa fa-cloud-upload-alt" style="font-size:1.5rem;color:var(--gold);display:block;margin-bottom:.5rem"></i>
                        <span style="font-size:.85rem;color:var(--text-muted)">اختاري الصور (يمكن تعدد)</span>
                        <input type="file" name="images[]" multiple accept="image/*" style="display:none"
                               onchange="previewImages(this)">
                    </label>
                    <div id="imagePreview" style="display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem;margin-top:.75rem"></div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function previewImages(input) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    [...input.files].forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const div = document.createElement('div');
            div.style.cssText = 'aspect-ratio:1;border-radius:6px;overflow:hidden;border:2px solid var(--gold)';
            div.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover">`;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}
</script>

<?php else:
// ===== LIST VIEW =====
$search  = sanitize($_GET['q'] ?? '');
$catFilter = (int)($_GET['cat'] ?? 0);
$page    = max(1,(int)($_GET['page']??1));
$perPage = 15;

$where  = ['1=1'];
$params = [];
if ($search) { $where[] = "(name_ar LIKE ? OR name_en LIKE ? OR sku LIKE ?)"; $params = array_merge($params,["$search%","$search%","$search%"]); }
if ($catFilter) { $where[] = "category_id=?"; $params[] = $catFilter; }

$whereSQL = implode(' AND ',$where);
$total    = $db->prepare("SELECT COUNT(*) FROM products WHERE $whereSQL");
$total->execute($params); $total = (int)$total->fetchColumn();
$pag = paginate($total,$perPage,$page);

$products = $db->prepare("SELECT p.*, c.name_ar AS cat_ar FROM products p LEFT JOIN categories c ON p.category_id=c.id WHERE $whereSQL ORDER BY p.created_at DESC LIMIT $perPage OFFSET {$pag['offset']}");
$products->execute($params);
$products = $products->fetchAll();
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
    <form style="display:flex;gap:.75rem;flex-wrap:wrap">
        <input type="text" name="q" class="form-control" placeholder="بحث..." value="<?= e($search) ?>" style="width:220px">
        <select name="cat" class="form-control" style="width:160px">
            <option value="">كل الفئات</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $catFilter===$c['id']?'selected':'' ?>><?= e($c['name_ar']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-outline btn-sm"><i class="fa fa-search"></i></button>
        <?php if ($search||$catFilter): ?>
            <a href="products.php" class="btn btn-outline btn-sm"><i class="fa fa-times"></i></a>
        <?php endif; ?>
    </form>
    <a href="products.php?action=add" class="btn btn-gold">
        <i class="fa fa-plus"></i> إضافة منتج
    </a>
</div>

<div class="data-table-wrap">
    <div class="table-header">
        <h3>المنتجات <span style="color:var(--text-muted);font-size:.85rem">(<?= $total ?>)</span></h3>
    </div>
    <table class="data-table">
        <thead><tr>
            <th>المنتج</th>
            <th>الفئة</th>
            <th>السعر</th>
            <th>المخزون</th>
            <th>الحالة</th>
            <th>إجراءات</th>
        </tr></thead>
        <tbody>
            <?php foreach ($products as $p):
                $imgs = json_decode($p['images']??'[]',true);
                $img  = !empty($imgs[0]) ? UPLOAD_URL.$imgs[0] : 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=80&q=60';
            ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:.75rem">
                            <img src="<?= e($img) ?>" style="width:44px;height:44px;border-radius:var(--radius);object-fit:cover;flex-shrink:0"
                                 onerror="this.src='https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=80&q=60'">
                            <div>
                                <div style="font-weight:600;font-size:.88rem"><?= e($p['name_ar']) ?></div>
                                <div style="color:var(--text-muted);font-size:.75rem"><?= e($p['sku']??'') ?></div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:.85rem"><?= e($p['cat_ar']??'—') ?></td>
                    <td style="font-weight:600;color:var(--gold)"><?= formatPrice((float)$p['price']) ?></td>
                    <td>
                        <span style="<?= $p['stock']<5?'color:#C0392B;font-weight:700':'' ?>">
                            <?= $p['stock'] ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge <?= $p['is_active']?'status-active':'status-inactive' ?>">
                            <?= $p['is_active']?'نشط':'مخفي' ?>
                        </span>
                        <?php if ($p['is_featured']): ?>
                            <span class="badge badge-featured" style="margin-right:.35rem">مميز</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:.5rem">
                            <a href="products.php?action=edit&id=<?= $p['id'] ?>" class="btn btn-outline btn-sm" title="تعديل">
                                <i class="fa fa-edit"></i>
                            </a>
                            <a href="<?= SITE_URL ?>/product.php?slug=<?= e($p['slug']) ?>" target="_blank" class="btn btn-outline btn-sm" title="معاينة">
                                <i class="fa fa-eye"></i>
                            </a>
                            <form method="POST" action="products.php?action=delete&id=<?= $p['id'] ?>" onsubmit="return confirmDelete('حذف المنتج «<?= e($p['name_ar']) ?>»؟')">
                                <?= csrfField() ?>
                                <button type="submit" class="btn btn-sm" style="background:rgba(192,57,43,.1);color:#C0392B;border:1px solid rgba(192,57,43,.2)">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($products)): ?>
                <tr><td colspan="6" class="empty-state" style="padding:3rem">
                    <div class="empty-icon"><i class="fa fa-gem"></i></div>
                    <p>لا توجد منتجات</p>
                    <a href="products.php?action=add" class="btn btn-gold btn-sm">إضافة أول منتج</a>
                </td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($pag['pages'] > 1): ?>
        <div class="pagination" style="padding:1rem">
            <?php for ($i=1;$i<=$pag['pages'];$i++): ?>
                <a href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>"
                   class="page-link <?= $i===$pag['current']?'active':'' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php endif; ?>
<?php include 'includes/admin_footer.php'; ?>
