<?php
require_once 'includes/config.php';

$pageTitle = LANG === 'ar' ? 'إكسسوارات عصرية بطلاء ذهب 18k' : 'Modern 18K Gold-Plated Accessories';
$pageDesc  = getSetting('site_description_' . LANG);

$db = getDB();

// المنتجات المميزة
$featured = $db->query("
    SELECT p.*, c.name_ar AS cat_ar, c.name_en AS cat_en
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.is_featured = 1 AND p.is_active = 1
    ORDER BY p.created_at DESC
    LIMIT 8
")->fetchAll();

// الفئات
$categories = $db->query("
    SELECT *, (SELECT COUNT(*) FROM products WHERE category_id=categories.id AND is_active=1) AS product_count
    FROM categories WHERE is_active=1 ORDER BY sort_order LIMIT 8
")->fetchAll();

// المجموعات

// منتجات جديدة
$newArrivals = $db->query("
    SELECT p.*, c.name_ar AS cat_ar, c.name_en AS cat_en
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.is_new = 1 AND p.is_active = 1
    ORDER BY p.created_at DESC LIMIT 4
")->fetchAll();

include 'includes/header.php';
?>

<!-- ============================================
     HERO
     ============================================ -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="container">
        <div class="hero-inner">

            <div class="hero-content">
                <div class="hero-tag">
                    <?= LANG === 'ar' ? 'إكسسوارات عصرية بطلاء ذهب 18k' : 'Modern 18K Gold-Plated Accessories' ?>
                </div>

                <h1 class="hero-title">
                    <?php if (LANG === 'ar'): ?>
                        لأنكِ تستحقين
                        <span class="highlight">الأفضل دائماً</span>
                    <?php else: ?>
                        Because You Deserve
                        <span class="highlight">The Very Best</span>
                    <?php endif; ?>
                </h1>

                <p class="hero-desc">
                    <?= LANG === 'ar'
                        ? 'إكسسوارات آيشار تُجسّد الأناقة العصرية — قطع بطلاء ذهب 18k وتصاميم مميزة لتُرافقك في أجمل لحظاتك.'
                        : 'AISHAR accessories embody modern elegance — 18K gold-plated pieces with distinctive designs for your most beautiful moments.' ?>
                </p>

                <div class="hero-actions">
                    <a href="shop.php" class="btn btn-gold btn-lg">
                        <i class="fa fa-shopping-bag"></i>
                        <?= LANG === 'ar' ? 'تسوّقي الآن' : 'Shop Now' ?>
                    </a>
                </div>

                <div class="hero-stats">
                    <div>
                        <span class="hero-stat-num">٥٠٠+</span>
                        <span class="hero-stat-label"><?= LANG === 'ar' ? 'تصميم فريد' : 'Unique Designs' ?></span>
                    </div>
                    <div>
                        <span class="hero-stat-num">18k</span>
                        <span class="hero-stat-label"><?= LANG === 'ar' ? 'طلاء ذهب' : 'Gold Plated' ?></span>
                    </div>
                    <div>
                        <span class="hero-stat-num">١٠ك+</span>
                        <span class="hero-stat-label"><?= LANG === 'ar' ? 'عميلة سعيدة' : 'Happy Clients' ?></span>
                    </div>
                </div>
            </div>

            <!-- Hero Image -->
            <div class="hero-image-wrap">
                <img src="assets/images/hero-jewelry.jpg"
                     alt="AISHAR Jewelry"
                     class="hero-image-main"
                     onerror="this.src='https://images.unsplash.com/photo-1617038260897-41a1f14a8ca0?w=600&q=80'">
                <div class="hero-image-badge">
                    <strong>18k</strong>
                    <span><?= LANG === 'ar' ? 'طلاء ذهب' : 'Gold Plated' ?></span>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ============================================
     TRUST BADGES
     ============================================ -->
<section class="band-gold" style="padding:2rem 0">
    <div class="container">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:1.5rem;text-align:center">
            <?php
            $badges = [['fa-certificate', LANG==='ar' ? 'طلاء ذهب 18k' : '18K Gold Plated']];
            foreach (orderPerks(1e12) as $pk) {
                $badges[] = [$pk['icon'], $pk['label'] . (LANG==='ar' ? ' فوق ' : ' over ') . formatPrice($pk['min'])];
            }
            foreach ($badges as [$icon, $label]):
            ?>
                <div style="display:flex;align-items:center;justify-content:center;gap:.75rem;color:#fff">
                    <i class="fa <?= $icon ?>" style="color:#fff;font-size:1.1rem"></i>
                    <span style="font-size:.95rem;font-weight:600"><?= $label ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================
     CATEGORIES
     ============================================ -->
<?php if (!empty($categories)): ?>
<section class="section-pad">
    <div class="container">
        <div style="text-align:center;margin-bottom:3rem">
            <h2 class="section-title" style="display:inline-block;text-align:center">
                <?= LANG === 'ar' ? 'تسوّقي حسب الفئة' : 'Shop by Category' ?>
            </h2>
            <div class="gold-divider" style="margin-top:1.5rem"></div>
        </div>

        <div class="categories-grid">
            <?php foreach ($categories as $cat):
                $catImg = $cat['image'] ?: "https://images.unsplash.com/photo-1611591437281-460bfbe1220a?w=400&q=80";
            ?>
                <a href="shop.php?category=<?= e($cat['slug']) ?>" class="category-card">
                    <img src="<?= e($catImg) ?>" alt="<?= e(t($cat, 'name')) ?>"
                         onerror="this.src='https://images.unsplash.com/photo-1611591437281-460bfbe1220a?w=400&q=80'">
                    <div class="category-card-overlay">
                        <div>
                            <span class="category-card-label"><?= e(t($cat, 'name')) ?></span>
                            <span class="category-card-count">
                                <?= $cat['product_count'] ?>
                                <?= LANG === 'ar' ? ' قطعة' : ' pieces' ?>
                            </span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================================
     FEATURED PRODUCTS
     ============================================ -->
<?php if (!empty($featured)): ?>
<section class="section-pad" style="background:var(--bg-2)">
    <div class="container">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:3rem;flex-wrap:wrap;gap:1rem">
            <div>
                <h2 class="section-title">
                    <?= LANG === 'ar' ? 'المنتجات المميزة' : 'Featured Products' ?>
                </h2>
            </div>
            <a href="shop.php" class="btn btn-outline">
                <?= LANG === 'ar' ? 'عرض الكل' : 'View All' ?>
                <i class="fa fa-arrow-<?= LANG === 'ar' ? 'left' : 'right' ?>"></i>
            </a>
        </div>

        <div class="products-grid">
            <?php foreach ($featured as $p):
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
                                <span class="badge badge-new"><?= LANG === 'ar' ? 'جديد' : 'New' ?></span>
                            <?php endif; ?>
                            <?php if ($hasDiscount): ?>
                                <span class="badge badge-sale">-<?= $discountPct ?>%</span>
                            <?php endif; ?>
                        </div>

                        <div class="product-card-actions">
                            <button class="product-action-btn" onclick="toggleWishlist(this, <?= $p['id'] ?>)" title="المفضلة">
                                <i class="far fa-heart"></i>
                            </button>
                            <a href="product.php?slug=<?= e($p['slug']) ?>" class="product-action-btn" title="التفاصيل">
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
                            <?= LANG === 'ar' ? 'أضيفي للسلة' : 'Add to Cart' ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================================
     NEW ARRIVALS
     ============================================ -->
<?php if (!empty($newArrivals)): ?>
<section class="section-pad" style="background:var(--bg-2)">
    <div class="container">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:3rem;flex-wrap:wrap;gap:1rem">
            <h2 class="section-title">
                <?= LANG === 'ar' ? 'وصل حديثاً' : 'New Arrivals' ?>
            </h2>
            <a href="shop.php?sort=newest" class="btn btn-outline btn-sm">
                <?= LANG === 'ar' ? 'كل الجديد' : 'All New' ?>
            </a>
        </div>

        <div class="products-grid cols-3" style="grid-template-columns:repeat(4,1fr)">
            <?php foreach ($newArrivals as $p):
                $images = json_decode($p['images'] ?? '[]', true);
                $img = !empty($images[0]) ? UPLOAD_URL . $images[0] : "https://images.unsplash.com/photo-1596944924616-7b38e7cfac36?w=400&q=80";
            ?>
                <div class="product-card">
                    <div class="product-card-image">
                        <a href="product.php?slug=<?= e($p['slug']) ?>">
                            <img src="<?= e($img) ?>" alt="<?= e(t($p, 'name')) ?>"
                                 onerror="this.src='https://images.unsplash.com/photo-1596944924616-7b38e7cfac36?w=400&q=80'">
                        </a>
                        <div class="product-badges">
                            <span class="badge badge-new"><?= LANG === 'ar' ? 'جديد' : 'New' ?></span>
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
                        </div>
                    </div>
                    <div class="product-card-footer">
                        <button onclick="addToCart(<?= $p['id'] ?>)" class="btn btn-gold w-100">
                            <i class="fa fa-shopping-bag"></i>
                            <?= LANG === 'ar' ? 'أضيفي للسلة' : 'Add to Cart' ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================================
     BRAND BANNER (تعريف البراند + التواصل الاجتماعي)
     ============================================ -->
<section class="band-gold" style="padding:5rem 0;position:relative;overflow:hidden">
    <div style="position:absolute;inset:0;background:radial-gradient(ellipse at 50% 0%,rgba(255,255,255,.18),transparent 65%)"></div>
    <div class="container" style="position:relative;text-align:center">
        <div style="max-width:760px;margin:0 auto">
                        <h2 style="font-size:clamp(1.5rem,4vw,2.4rem);color:#fff;margin-bottom:1.5rem;line-height:1.5">
                <?= LANG === 'ar'
                    ? 'براند إكسسوارات ذات جودة عالية مع تصاميم مميزة وعصرية، صُممت لتناسب جميع تفاصيل الحياة اليومية'
                    : 'High-quality accessories with distinctive, modern designs, made to suit every detail of your daily life' ?>
            </h2>
            <p style="color:rgba(255,255,255,.92);font-size:1.05rem;line-height:1.9;margin-bottom:2rem">
                <?= LANG === 'ar'
                    ? 'توازن مثالي بين الموضة العصرية والجودة العالية والأسعار التي تجعل الأناقة متاحة للجميع.'
                    : 'The perfect balance between modern fashion, high quality, and prices that make elegance accessible to everyone.' ?>
            </p>

            <?php
            $socials = [
                ['fa-facebook-f',  'Facebook',  'facebook'],
                ['fa-instagram',   'Instagram', 'instagram'],
                ['fa-tiktok',      'TikTok',    'tiktok'],
                ['fa-pinterest-p', 'Pinterest', 'pinterest'],
            ];
            ?>
            <div class="social-card">
                <div class="social-card-title">AISHAR JEWELRY</div>
                <div class="social-card-text"><?= LANG === 'ar' ? 'أضيفي بريقًا خاصًا لإطلالتك!' : 'Add a sparkle to your look!' ?></div>
                <div class="social-card-icons">
                    <?php foreach ($socials as [$ic, $nm, $key]): ?>
                        <a href="<?= e(socialUrl($key)) ?>" target="_blank" rel="noopener" aria-label="<?= $nm ?>">
                            <i class="fab <?= $ic ?>"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <a href="about.php" class="btn btn-outline" style="border-color:#fff;color:#fff;margin-top:2rem">
                <?= LANG === 'ar' ? 'اقرئي قصتنا' : 'Read Our Story' ?>
            </a>
        </div>
    </div>
</section>

<!-- ============================================
     INSTAGRAM SECTION
     ============================================ -->
<section class="section-pad">
    <div class="container">
        <div style="text-align:center;margin-bottom:2.5rem">
            <h2 class="section-title" style="display:inline-block">
                <?= LANG === 'ar' ? 'تابعينا على إنستجرام' : 'Follow Us on Instagram' ?>
            </h2>
            <p style="margin-top:1.5rem;color:var(--text-muted)">
                @<?= e(instagramHandle()) ?>
            </p>
        </div>

        <!-- Instagram Grid — صور التغليف والمنتجات: assets/images/instagram/ig-1..6.jpg -->
        <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:.5rem">
            <?php
            $instaImgs = ['ig-1','ig-2','ig-3','ig-4','ig-5','ig-6'];
            foreach ($instaImgs as $imgId):
            ?>
                <a href="<?= e(socialUrl('instagram')) ?>" target="_blank" rel="noopener"
                   style="aspect-ratio:1;overflow:hidden;border-radius:var(--radius);display:block;position:relative;background:var(--bg-2)">
                    <img src="assets/images/instagram/<?= $imgId ?>.jpg"
                         alt="AISHAR Instagram"
                         style="width:100%;height:100%;object-fit:cover;transition:transform .5s"
                         onmouseover="this.style.transform='scale(1.08)'"
                         onmouseout="this.style.transform='scale(1)'">
                    <div style="position:absolute;inset:0;background:rgba(0,0,0,0);transition:background .3s;display:flex;align-items:center;justify-content:center"
                         onmouseover="this.style.background='rgba(0,0,0,.4)'"
                         onmouseout="this.style.background='rgba(0,0,0,0)'">
                        <i class="fab fa-instagram" style="color:#fff;font-size:1.5rem;opacity:0;transition:opacity .3s"
                           onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0'"></i>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
