<?php
require_once 'includes/config.php';

$db = getDB();

// ---- Filters ----
$categorySlug = sanitize($_GET['category'] ?? '');
$minPrice     = (float)($_GET['min_price'] ?? 0);
$maxPrice     = (float)($_GET['max_price'] ?? 10000);
$sort         = sanitize($_GET['sort'] ?? 'newest');
$search       = sanitize($_GET['q'] ?? '');
$page         = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 12;

// بناء الاستعلام
$where  = ["p.is_active = 1"];
$params = [];

if ($categorySlug) {
    $where[]  = "c.slug = ?";
    $params[] = $categorySlug;
}
if ($search) {
    $where[]  = "(p.name_ar LIKE ? OR p.name_en LIKE ? OR p.description_ar LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($minPrice > 0) { $where[] = "p.price >= ?"; $params[] = $minPrice; }
if ($maxPrice < 10000) { $where[] = "p.price <= ?"; $params[] = $maxPrice; }

$whereSQL = implode(' AND ', $where);

$orderMap = [
    'newest'    => 'p.created_at DESC',
    'oldest'    => 'p.created_at ASC',
    'price_asc' => 'p.price ASC',
    'price_desc'=> 'p.price DESC',
    'featured'  => 'p.is_featured DESC, p.created_at DESC',
];
$orderSQL = $orderMap[$sort] ?? 'p.created_at DESC';

// عدد الكل
$countStmt = $db->prepare("
    SELECT COUNT(*) FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE $whereSQL
");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$pag = paginate($total, $perPage, $page);

// المنتجات
$stmt = $db->prepare("
    SELECT p.*, c.name_ar AS cat_ar, c.name_en AS cat_en, c.slug AS cat_slug
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE $whereSQL
    ORDER BY $orderSQL
    LIMIT $perPage OFFSET {$pag['offset']}
");
$stmt->execute($params);
$products = $stmt->fetchAll();

// الفئات للسايدبار
$categories = $db->query("
    SELECT c.*, COUNT(p.id) AS cnt
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
    WHERE c.is_active = 1
    GROUP BY c.id ORDER BY c.sort_order
")->fetchAll();

// الفئة الحالية
$currentCat = null;
if ($categorySlug) {
    foreach ($categories as $cat) {
        if ($cat['slug'] === $categorySlug) { $currentCat = $cat; break; }
    }
}

$pageTitle = $search ? ($search . ' — ' . (LANG === 'ar' ? 'نتائج البحث' : 'Search Results'))
           : ($currentCat ? t($currentCat, 'name') : (LANG === 'ar' ? 'المتجر' : 'Shop'));

include 'includes/header.php';
?>

<!-- Breadcrumb -->
<div class="container">
    <nav class="breadcrumb">
        <a href="index.php"><?= LANG === 'ar' ? 'الرئيسية' : 'Home' ?></a>
        <span class="breadcrumb-sep"><i class="fa fa-chevron-left fa-xs"></i></span>
        <?php if ($currentCat): ?>
            <a href="shop.php"><?= LANG === 'ar' ? 'المتجر' : 'Shop' ?></a>
            <span class="breadcrumb-sep"><i class="fa fa-chevron-left fa-xs"></i></span>
            <span><?= e(t($currentCat, 'name')) ?></span>
        <?php else: ?>
            <span><?= $search ? e($search) : (LANG === 'ar' ? 'المتجر' : 'Shop') ?></span>
        <?php endif; ?>
    </nav>
</div>

<section class="section-pad-sm">
    <div class="container">
        <div class="shop-layout">

            <!-- ============ SIDEBAR ============ -->
            <aside class="sidebar">

                <!-- فلتر الفئات -->
                <div class="sidebar-section">
                    <div class="sidebar-title"><?= LANG === 'ar' ? 'الفئات' : 'Categories' ?></div>
                    <div class="filter-list">
                        <a href="shop.php<?= $search ? '?q='.urlencode($search) : '' ?>"
                           class="filter-item <?= !$categorySlug ? 'active' : '' ?>">
                            <i class="fa fa-th" style="color:var(--gold);width:16px"></i>
                            <?= LANG === 'ar' ? 'الكل' : 'All' ?>
                            <span style="margin-right:auto;color:var(--text-muted);font-size:.8rem"><?= $total ?></span>
                        </a>
                        <?php foreach ($categories as $cat): ?>
                            <a href="shop.php?category=<?= e($cat['slug']) ?><?= $sort !== 'newest' ? '&sort='.$sort : '' ?>"
                               class="filter-item <?= $categorySlug === $cat['slug'] ? 'active' : '' ?>">
                                <i class="fa fa-circle" style="color:var(--gold);font-size:.5rem;width:16px"></i>
                                <?= e(t($cat, 'name')) ?>
                                <span style="margin-right:auto;color:var(--text-muted);font-size:.8rem"><?= $cat['cnt'] ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- فلتر السعر -->
                <div class="sidebar-section">
                    <div class="sidebar-title"><?= LANG === 'ar' ? 'نطاق السعر' : 'Price Range' ?></div>
                    <form method="GET" id="priceForm">
                        <?php if ($categorySlug): ?>
                            <input type="hidden" name="category" value="<?= e($categorySlug) ?>">
                        <?php endif; ?>
                        <?php if ($search): ?>
                            <input type="hidden" name="q" value="<?= e($search) ?>">
                        <?php endif; ?>
                        <input type="hidden" name="sort" value="<?= e($sort) ?>">
                        <div class="price-range-wrap">
                            <div class="range-values">
                                <span id="priceMin"><?= number_format($minPrice) ?> <?= getSetting('currency_symbol','د.ع') ?></span>
                                <span id="priceMax"><?= number_format($maxPrice) ?> <?= getSetting('currency_symbol','د.ع') ?></span>
                            </div>
                            <input type="range" name="min_price" min="0" max="10000" step="50"
                                   value="<?= $minPrice ?>"
                                   oninput="document.getElementById('priceMin').textContent=this.value+' <?= getSetting('currency_symbol','د.ع') ?>'">
                            <input type="range" name="max_price" min="0" max="10000" step="50"
                                   value="<?= $maxPrice ?>" style="margin-top:.5rem"
                                   oninput="document.getElementById('priceMax').textContent=this.value+' <?= getSetting('currency_symbol','د.ع') ?>'">
                            <button type="submit" class="btn btn-outline btn-sm w-100" style="margin-top:1rem">
                                <?= LANG === 'ar' ? 'تطبيق' : 'Apply' ?>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- منتجات مميزة -->
                <div class="sidebar-section">
                    <div class="sidebar-title"><?= LANG === 'ar' ? 'فلاتر سريعة' : 'Quick Filters' ?></div>
                    <div class="filter-list">
                        <a href="shop.php?sort=featured" class="filter-item <?= $sort==='featured'&&!$categorySlug ? 'active' : '' ?>">
                            <i class="fa fa-star" style="color:var(--gold);width:16px"></i>
                            <?= LANG === 'ar' ? 'المميزة' : 'Featured' ?>
                        </a>
                        <a href="shop.php?is_new=1" class="filter-item">
                            <i class="fa fa-sparkles" style="color:var(--gold);width:16px"></i>
                            <?= LANG === 'ar' ? 'وصل حديثاً' : 'New Arrivals' ?>
                        </a>
                        <a href="shop.php?on_sale=1" class="filter-item">
                            <i class="fa fa-tag" style="color:var(--gold);width:16px"></i>
                            <?= LANG === 'ar' ? 'عروض وتخفيضات' : 'On Sale' ?>
                        </a>
                    </div>
                </div>

            </aside>

            <!-- ============ PRODUCTS AREA ============ -->
            <div>

                <!-- Search Result Header -->
                <?php if ($search): ?>
                    <div class="flash flash-info" style="margin-bottom:1.5rem">
                        <i class="fa fa-search"></i>
                        <?= LANG === 'ar'
                            ? "نتائج البحث عن: <strong>" . e($search) . "</strong> — وُجد $total منتج"
                            : "Search results for: <strong>" . e($search) . "</strong> — $total found" ?>
                    </div>
                <?php endif; ?>

                <!-- Controls Bar -->
                <div class="shop-bar">
                    <span class="shop-bar-count">
                        <?= LANG === 'ar'
                            ? "<strong>$total</strong> منتج"
                            : "<strong>$total</strong> products" ?>
                    </span>

                    <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
                        <!-- View Toggle -->
                        <div class="view-toggle">
                            <button class="view-btn active" id="gridViewBtn" onclick="setView('grid')" title="شبكة">
                                <i class="fa fa-th"></i>
                            </button>
                            <button class="view-btn" id="listViewBtn" onclick="setView('list')" title="قائمة">
                                <i class="fa fa-list"></i>
                            </button>
                        </div>

                        <!-- Sort -->
                        <div class="shop-bar-sort">
                            <select onchange="applySortFilter(this.value)">
                                <option value="newest"     <?= $sort==='newest'     ? 'selected' : '' ?>><?= LANG==='ar' ? 'الأحدث' : 'Newest' ?></option>
                                <option value="featured"   <?= $sort==='featured'   ? 'selected' : '' ?>><?= LANG==='ar' ? 'المميزة' : 'Featured' ?></option>
                                <option value="price_asc"  <?= $sort==='price_asc'  ? 'selected' : '' ?>><?= LANG==='ar' ? 'السعر: الأقل' : 'Price: Low' ?></option>
                                <option value="price_desc" <?= $sort==='price_desc' ? 'selected' : '' ?>><?= LANG==='ar' ? 'السعر: الأعلى' : 'Price: High' ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <?php if (!empty($products)): ?>
                    <div class="products-grid" id="productsGrid">
                        <?php foreach ($products as $p):
                            $images = json_decode($p['images'] ?? '[]', true);
                            $img = !empty($images[0]) ? UPLOAD_URL . $images[0] : "https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=400&q=80";
                            $hasDiscount = $p['original_price'] && $p['original_price'] > $p['price'];
                            $discountPct = $hasDiscount ? round((1 - $p['price']/$p['original_price'])*100) : 0;
                        ?>
                            <div class="product-card">
                                <div class="product-card-image">
                                    <a href="product.php?slug=<?= e($p['slug']) ?>">
                                        <img src="<?= e($img) ?>" alt="<?= e(t($p, 'name')) ?>"
                                             onerror="this.src='https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=400&q=80'">
                                    </a>
                                    <div class="product-badges">
                                        <?php if ($p['is_new']): ?>
                                            <span class="badge badge-new"><?= LANG==='ar' ? 'جديد' : 'New' ?></span>
                                        <?php endif; ?>
                                        <?php if ($hasDiscount): ?>
                                            <span class="badge badge-sale">-<?= $discountPct ?>%</span>
                                        <?php endif; ?>
                                    </div>
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
                                    <div class="product-card-category"><?= e(t($p, 'cat')) ?></div>
                                    <h3 class="product-card-name">
                                        <a href="product.php?slug=<?= e($p['slug']) ?>"><?= e(t($p, 'name')) ?></a>
                                    </h3>
                                    <div class="product-card-price">
                                        <span class="price-current"><?= formatPrice((float)$p['price']) ?></span>
                                        <?php if ($hasDiscount): ?>
                                            <span class="price-original"><?= formatPrice((float)$p['original_price']) ?></span>
                                            <span class="price-discount">-<?= $discountPct ?>%</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="product-card-footer">
                                    <button onclick="addToCart(<?= $p['id'] ?>)" class="btn btn-gold w-100">
                                        <i class="fa fa-shopping-bag"></i>
                                        <?= LANG==='ar' ? 'أضيفي للسلة' : 'Add to Cart' ?>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pag['pages'] > 1): ?>
                        <div class="pagination">
                            <?php if ($pag['prev']): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $pag['prev']])) ?>" class="page-link">
                                    <i class="fa fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $pag['pages']; $i++): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                                   class="page-link <?= $i === $pag['current'] ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($pag['next']): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $pag['next']])) ?>" class="page-link">
                                    <i class="fa fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fa fa-search"></i></div>
                        <h3 class="empty-title"><?= LANG==='ar' ? 'لا توجد منتجات' : 'No products found' ?></h3>
                        <p class="empty-desc"><?= LANG==='ar' ? 'جربي تغيير الفلاتر أو البحث بكلمات مختلفة' : 'Try changing filters or search with different keywords' ?></p>
                        <a href="shop.php" class="btn btn-gold"><?= LANG==='ar' ? 'عرض الكل' : 'View All' ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
function applySortFilter(val) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', val);
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

function setView(type) {
    const grid = document.getElementById('productsGrid');
    const gridBtn = document.getElementById('gridViewBtn');
    const listBtn = document.getElementById('listViewBtn');
    if (type === 'list') {
        grid.style.gridTemplateColumns = '1fr';
        gridBtn.classList.remove('active');
        listBtn.classList.add('active');
    } else {
        grid.style.gridTemplateColumns = '';
        gridBtn.classList.add('active');
        listBtn.classList.remove('active');
    }
}
</script>

<?php include 'includes/footer.php'; ?>
