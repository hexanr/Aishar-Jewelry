<?php
require_once 'includes/config.php';

$slug = sanitize($_GET['slug'] ?? '');
if (!$slug) { header('Location: shop.php'); exit; }

$db = getDB();
$stmt = $db->prepare("
    SELECT p.*, c.name_ar AS cat_ar, c.name_en AS cat_en, c.slug AS cat_slug
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.slug = ? AND p.is_active = 1
");
$stmt->execute([$slug]);
$product = $stmt->fetch();
if (!$product) { header('Location: shop.php'); exit; }

// سجّل زيارة
$db->prepare("INSERT INTO page_views (page, product_id, ip_address) VALUES (?,?,?)")
   ->execute(['product', $product['id'], $_SERVER['REMOTE_ADDR']]);
$db->prepare("UPDATE products SET views = views+1 WHERE id=?")->execute([$product['id']]);

// منتجات مشابهة
$related = $db->prepare("
    SELECT p.*, c.name_ar AS cat_ar, c.name_en AS cat_en
    FROM products p LEFT JOIN categories c ON p.category_id=c.id
    WHERE p.category_id=? AND p.id!=? AND p.is_active=1 LIMIT 4
");
$related->execute([$product['category_id'], $product['id']]);
$related = $related->fetchAll();

$images  = json_decode($product['images'] ?? '[]', true);
$mainImg = !empty($images[0]) ? UPLOAD_URL . $images[0]
         : 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=800&q=80';

$hasDiscount = $product['original_price'] && $product['original_price'] > $product['price'];
$discPct     = $hasDiscount ? round((1 - $product['price']/$product['original_price'])*100) : 0;

$sizes = parseSizes($product['sizes'] ?? '');
$ogImage = $mainImg;
$pageTitle = t($product, 'name');
$pageDesc  = t($product, 'description');

include 'includes/header.php';
?>

<div class="container">
    <nav class="breadcrumb">
        <a href="index.php"><?= LANG==='ar'?'الرئيسية':'Home' ?></a>
        <span class="breadcrumb-sep"><i class="fa fa-chevron-left fa-xs"></i></span>
        <a href="shop.php"><?= LANG==='ar'?'المتجر':'Shop' ?></a>
        <span class="breadcrumb-sep"><i class="fa fa-chevron-left fa-xs"></i></span>
        <a href="shop.php?category=<?= e($product['cat_slug']??'') ?>"><?= e(t($product,'cat')) ?></a>
        <span class="breadcrumb-sep"><i class="fa fa-chevron-left fa-xs"></i></span>
        <span><?= e(t($product,'name')) ?></span>
    </nav>
</div>

<section>
    <div class="container">
        <div class="product-detail">

            <!-- Gallery -->
            <div class="product-gallery">
                <div class="gallery-main" id="galleryMain">
                    <img src="<?= e($mainImg) ?>" alt="<?= e(t($product,'name')) ?>" id="mainImg"
                         onerror="this.src='https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=800&q=80'">
                </div>

                <?php if (count($images) > 1): ?>
                <div class="gallery-thumbs">
                    <?php foreach ($images as $i => $img): ?>
                        <div class="gallery-thumb <?= $i===0?'active':'' ?>"
                             onclick="changeGalleryImage('<?= e(UPLOAD_URL.$img) ?>', this)">
                            <img src="<?= e(UPLOAD_URL.$img) ?>" alt="">
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div class="product-info">
                <div class="product-info-category">
                    <a href="shop.php?category=<?= e($product['cat_slug']??'') ?>"><?= e(t($product,'cat')) ?></a>
                </div>

                <h1 class="product-info-title"><?= e(t($product,'name')) ?></h1>

                <div class="product-info-price">
                    <span class="price-current"><?= formatPrice((float)$product['price']) ?></span>
                    <?php if ($hasDiscount): ?>
                        <span class="price-original"><?= formatPrice((float)$product['original_price']) ?></span>
                        <span class="price-discount">خصم <?= $discPct ?>%</span>
                    <?php endif; ?>
                </div>

                <!-- Shipping badge -->
                <div style="display:flex;gap:.75rem;margin-bottom:1.5rem;flex-wrap:wrap">
                    <?php if ($product['price'] >= (float)getSetting('free_shipping_min','300')): ?>
                        <span style="background:rgba(39,174,96,.1);color:#27AE60;padding:4px 12px;border-radius:20px;font-size:.78rem;font-weight:600">
                            <i class="fa fa-truck"></i> <?= LANG==='ar'?'شحن مجاني':'Free Shipping' ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($product['stock'] > 0): ?>
                        <span style="background:rgba(52,152,219,.1);color:#3498DB;padding:4px 12px;border-radius:20px;font-size:.78rem;font-weight:600">
                            <i class="fa fa-check"></i> <?= LANG==='ar'?'متوفر في المخزن':'In Stock' ?>
                        </span>
                    <?php else: ?>
                        <span style="background:rgba(192,57,43,.1);color:#C0392B;padding:4px 12px;border-radius:20px;font-size:.78rem;font-weight:600">
                            <?= LANG==='ar'?'نفذ المخزون':'Out of Stock' ?>
                        </span>
                    <?php endif; ?>
                </div>

                <p class="product-info-desc"><?= e(t($product,'description')) ?></p>

                <?php if ($product['stock'] > 0 && $sizes): ?>
                <!-- Ring size -->
                <div class="size-picker" id="sizePicker">
                    <div class="size-picker-head">
                        <span class="size-picker-label"><?= LANG==='ar'?'اختاري قياس الخاتم':'Select ring size' ?></span>
                        <span class="size-picker-chosen" id="sizeChosen"></span>
                    </div>
                    <div class="size-options">
                        <?php foreach ($sizes as $s): ?>
                            <button type="button" class="size-opt" data-size="<?= e($s) ?>" onclick="pickSize(this)"><?= e($s) ?></button>
                        <?php endforeach; ?>
                    </div>
                    <div class="size-error" id="sizeError"><?= LANG==='ar'?'من فضلك اختاري القياس أولاً':'Please choose a size first' ?></div>
                    <a class="size-help" target="_blank"
                       href="https://wa.me/<?= getSetting('site_whatsapp') ?>?text=<?= urlencode('أحتاج مساعدة في معرفة قياس الخاتم: '.t($product,'name')) ?>">
                        <i class="fab fa-whatsapp"></i> <?= LANG==='ar'?'ما أعرف قياسي؟ راسلينا':'Not sure of your size? Message us' ?>
                    </a>
                </div>
                <?php elseif ($product['stock'] > 0 && $product['cat_slug'] === 'rings'): ?>
                <div class="size-free"><i class="fa fa-circle-check"></i> <?= LANG==='ar'?'فري سايز — مقاس واحد يناسب الجميع':'Free size — fits all' ?></div>
                <?php endif; ?>

                <!-- Qty -->
                <?php if ($product['stock'] > 0): ?>
                <div class="qty-selector">
                    <button class="qty-btn" onclick="changeQty(this,-1)">−</button>
                    <input type="number" class="qty-input" value="1" min="1" max="<?= $product['stock'] ?>" id="qtyInput">
                    <button class="qty-btn" onclick="changeQty(this,1)">+</button>
                </div>

                <div class="product-add-actions">
                    <button class="btn btn-gold btn-lg" onclick="addToCartWithQty(<?= $product['id'] ?>)">
                        <i class="fa fa-shopping-bag"></i>
                        <?= LANG==='ar'?'أضيفي للسلة':'Add to Cart' ?>
                    </button>
                    <button class="btn btn-outline btn-lg" onclick="toggleWishlist(this,<?= $product['id'] ?>)">
                        <i class="far fa-heart"></i>
                    </button>
                    <a href="https://wa.me/<?= getSetting('site_whatsapp') ?>?text=<?= urlencode('أريد الاستفسار عن: '.t($product,'name').' - '.SITE_URL.'/product.php?slug='.$product['slug']) ?>"
                       class="btn btn-black btn-lg" target="_blank">
                        <i class="fab fa-whatsapp"></i>
                        <?= LANG==='ar'?'استفسار':'Inquire' ?>
                    </a>
                </div>
                <?php endif; ?>

                <!-- Meta -->
                <div class="product-meta">
                    <?php if ($product['sku']): ?>
                    <div class="meta-item">
                        <span class="meta-label"><?= LANG==='ar'?'كود المنتج':'SKU' ?>:</span>
                        <span class="meta-value"><?= e($product['sku']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (t($product,'material')): ?>
                    <div class="meta-item">
                        <span class="meta-label"><?= LANG==='ar'?'المادة':'Material' ?>:</span>
                        <span class="meta-value"><?= e(t($product,'material')) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($product['weight']): ?>
                    <div class="meta-item">
                        <span class="meta-label"><?= LANG==='ar'?'الوزن':'Weight' ?>:</span>
                        <span class="meta-value"><?= $product['weight'] ?> g</span>
                    </div>
                    <?php endif; ?>
                    <div class="meta-item">
                        <span class="meta-label"><?= LANG==='ar'?'المشاهدات':'Views' ?>:</span>
                        <span class="meta-value"><?= number_format($product['views']) ?></span>
                    </div>
                </div>

                <!-- Share -->
                <div style="margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid var(--border)">
                    <span style="font-size:.8rem;color:var(--text-muted);margin-left:.75rem">
                        <?= LANG==='ar'?'شاركي:':'Share:' ?>
                    </span>
                    <?php $shareUrl = urlencode(SITE_URL.'/product.php?slug='.$product['slug']); ?>
                    <a href="https://wa.me/?text=<?= $shareUrl ?>" target="_blank" class="btn-icon" style="display:inline-flex">
                        <i class="fab fa-whatsapp" style="color:#25D366"></i>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?= $shareUrl ?>" target="_blank" class="btn-icon" style="display:inline-flex">
                        <i class="fab fa-twitter" style="color:#1DA1F2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Products -->
<?php if (!empty($related)): ?>
<section class="section-pad" style="background:var(--bg-2)">
    <div class="container">
        <h2 class="section-title" style="margin-bottom:2.5rem">
            <?= LANG==='ar'?'منتجات مشابهة':'Related Products' ?>
        </h2>
        <div class="products-grid">
            <?php foreach ($related as $p):
                $imgs = json_decode($p['images']??'[]',true);
                $img = !empty($imgs[0]) ? UPLOAD_URL.$imgs[0] : 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=400&q=80';
            ?>
                <div class="product-card">
                    <div class="product-card-image">
                        <a href="product.php?slug=<?= e($p['slug']) ?>">
                            <img src="<?= e($img) ?>" alt="<?= e(t($p,'name')) ?>"
                                 onerror="this.src='https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=400&q=80'">
                        </a>
                        <div class="product-card-actions">
                            <button class="product-action-btn" onclick="toggleWishlist(this,<?= $p['id'] ?>)">
                                <i class="far fa-heart"></i>
                            </button>
                            <a href="product.php?slug=<?= e($p['slug']) ?>" class="product-action-btn">
                                <i class="fa fa-eye"></i>
                            </a>
                        </div>
                    </div>
                    <div class="product-card-info">
                        <div class="product-card-category"><?= e(t($p,'cat')) ?></div>
                        <h3 class="product-card-name"><a href="product.php?slug=<?= e($p['slug']) ?>"><?= e(t($p,'name')) ?></a></h3>
                        <div class="product-card-price">
                            <span class="price-current"><?= formatPrice((float)$p['price']) ?></span>
                        </div>
                    </div>
                    <div class="product-card-footer">
                        <button onclick="addToCart(<?= $p['id'] ?>)" class="btn btn-gold w-100">
                            <i class="fa fa-shopping-bag"></i> <?= LANG==='ar'?'أضيفي للسلة':'Add to Cart' ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<script>
let selectedSize = '';
const hasSizes = <?= $sizes ? 'true' : 'false' ?>;
function pickSize(btn) {
    document.querySelectorAll('.size-opt').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    selectedSize = btn.dataset.size;
    document.getElementById('sizeChosen').textContent = '<?= LANG==='ar'?'القياس':'Size' ?>: ' + selectedSize;
    document.getElementById('sizePicker').classList.remove('has-error');
}
function addToCartWithQty(pid) {
    if (hasSizes && !selectedSize) {
        const p = document.getElementById('sizePicker');
        p.classList.add('has-error');
        p.scrollIntoView({behavior:'smooth', block:'center'});
        return;
    }
    const qty = parseInt(document.getElementById('qtyInput')?.value) || 1;
    addToCart(pid, qty, selectedSize);
}
</script>

<?php include 'includes/footer.php'; ?>
