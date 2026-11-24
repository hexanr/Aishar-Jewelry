<?php // includes/footer.php ?>
</main>

<!-- ===== FOOTER ===== -->
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">

            <!-- Brand -->
            <div class="footer-brand">
                <div class="logo" style="margin-bottom:1rem"><img class="logo-img" src="<?= SITE_URL ?>/assets/images/logo-light.png" alt="AISHAR Jewelry"></div>
                <p class="footer-desc">
                    <?= LANG === 'ar'
                        ? 'إكسسوارات عصرية بطلاء ذهب 18k تعكس أناقتك وتُعبّر عن لحظاتك الأجمل.'
                        : 'Modern 18K gold-plated accessories that reflect your elegance and celebrate your most beautiful moments.' ?>
                </p>
                <div class="social-links">
                    <?php foreach ([
                        ['fa-facebook-f',  'Facebook',  'facebook'],
                        ['fa-instagram',   'Instagram', 'instagram'],
                        ['fa-tiktok',      'TikTok',    'tiktok'],
                        ['fa-pinterest-p', 'Pinterest', 'pinterest'],
                    ] as [$ic, $nm, $key]): ?>
                        <a href="<?= e(socialUrl($key)) ?>" class="social-link" target="_blank" rel="noopener" aria-label="<?= $nm ?>">
                            <i class="fab <?= $ic ?>"></i>
                        </a>
                    <?php endforeach; ?>
                    <a href="https://wa.me/<?= getSetting('site_whatsapp') ?>" class="social-link" target="_blank" aria-label="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="footer-title"><?= LANG === 'ar' ? 'روابط سريعة' : 'Quick Links' ?></h4>
                <nav class="footer-links">
                    <a href="<?= SITE_URL ?>/index.php"><?= LANG === 'ar' ? 'الرئيسية' : 'Home' ?></a>
                    <a href="<?= SITE_URL ?>/shop.php"><?= LANG === 'ar' ? 'المتجر' : 'Shop' ?></a>
                    <a href="<?= SITE_URL ?>/about.php"><?= LANG === 'ar' ? 'عن آيشار' : 'About' ?></a>
                    <a href="<?= SITE_URL ?>/contact.php"><?= LANG === 'ar' ? 'تواصلي معنا' : 'Contact Us' ?></a>
                </nav>
            </div>

            <!-- Categories -->
            <div>
                <h4 class="footer-title"><?= LANG === 'ar' ? 'الفئات' : 'Categories' ?></h4>
                <nav class="footer-links">
                    <?php
                    try {
                        $cats = getDB()->query("SELECT slug, name_ar, name_en FROM categories WHERE is_active=1 ORDER BY sort_order LIMIT 8")->fetchAll();
                        foreach ($cats as $c):
                    ?>
                        <a href="<?= SITE_URL ?>/shop.php?category=<?= e($c['slug']) ?>">
                            <?= e(t($c, 'name')) ?>
                        </a>
                    <?php endforeach; } catch(Exception $e) {} ?>
                </nav>
            </div>

            <!-- Contact -->
            <div>
                <h4 class="footer-title"><?= LANG === 'ar' ? 'تواصلي معنا' : 'Get In Touch' ?></h4>
                <nav class="footer-links">
                    <?php if (trim(getSetting('site_email')) !== ''): ?>
                    <a href="mailto:<?= getSetting('site_email') ?>">
                        <i class="fa fa-envelope" style="color:var(--gold);margin-left:.5rem"></i>
                        <?= getSetting('site_email') ?>
                    </a>
                    <?php endif; ?>
                    <a href="tel:<?= getSetting('site_phone') ?>">
                        <i class="fa fa-phone" style="color:var(--gold);margin-left:.5rem"></i>
                        <?= getSetting('site_phone') ?>
                    </a>
                    <a href="https://wa.me/<?= getSetting('site_whatsapp') ?>" target="_blank">
                        <i class="fab fa-whatsapp" style="color:var(--gold);margin-left:.5rem"></i>
                        <?= LANG === 'ar' ? 'واتساب' : 'WhatsApp' ?>
                    </a>
                </nav>

                <div style="margin-top:1.25rem;padding:1rem;background:rgba(201,168,76,0.08);border-radius:var(--radius);border:1px solid rgba(201,168,76,0.2)">
                    <div style="font-size:.75rem;color:var(--gold);letter-spacing:.1em;text-transform:uppercase;margin-bottom:.4rem">
                        <?= LANG === 'ar' ? 'مزايا طلباتك' : 'Order Perks' ?>
                    </div>
                    <div style="font-size:.85rem">
                        <?php foreach (orderPerks(1e12) as $pk): ?><div><?= e($pk['label']) ?> — <?= LANG==='ar' ? 'من ' : 'from ' ?><?= formatPrice($pk['min']) ?></div><?php endforeach; ?>
                        <div style="margin-top:.6rem;opacity:.8"><?= LANG==='ar' ? 'الدفع: ماستر كارد · زين كاش · عند الاستلام' : 'Pay by: Mastercard · Zain Cash · Cash on delivery' ?></div>
                    </div>
                </div>
            </div>

        </div>

        <div class="footer-bottom">
            <p>© <?= date('Y') ?> AISHAR Jewelry. <?= LANG === 'ar' ? 'جميع الحقوق محفوظة.' : 'All rights reserved.' ?></p>
            <div style="display:flex;gap:1.5rem">
                <a href="#"><?= LANG === 'ar' ? 'سياسة الخصوصية' : 'Privacy Policy' ?></a>
                <a href="#"><?= LANG === 'ar' ? 'سياسة الإرجاع' : 'Return Policy' ?></a>
            </div>
        </div>
        <div class="footer-credit">
            <?= LANG === 'ar' ? 'تم تصميم وتطوير هذا الموقع بواسطة' : 'This website was designed & developed by' ?>
            <a href="https://www.linkedin.com/in/nasra-abdalhafiz" target="_blank" rel="noopener">Nasra Abdalhafiz</a>
        </div>
    </div>
</footer>

<!-- ===== JavaScript ===== -->
<script>
/* ---- Theme Toggle ---- */
function toggleTheme() {
    const html = document.documentElement;
    const current = html.getAttribute('data-theme');
    const next = current === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    document.cookie = `theme=${next};path=/;max-age=31536000`;
}

/* ---- Mobile Nav ---- */
function toggleMobileNav() {
    const nav = document.getElementById('mobileNav');
    nav.classList.toggle('open');
}

/* ---- Search Overlay ---- */
function toggleSearch() {
    const overlay = document.getElementById('searchOverlay');
    overlay.classList.toggle('open');
    if (overlay.classList.contains('open')) {
        setTimeout(() => overlay.querySelector('input')?.focus(), 100);
    }
}

/* ---- Toast Notification ---- */
function showToast(msg, type = 'success') {
    const container = document.getElementById('toastContainer');
    const icons = { success: 'check-circle', error: 'exclamation-circle', info: 'info-circle' };
    const colors = { success: '#27AE60', error: '#C0392B', info: '#3498DB' };
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.style.borderColor = colors[type] || colors.success;
    toast.innerHTML = `<i class="fa fa-${icons[type]}" style="color:${colors[type]}"></i> ${msg}`;
    container.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 3500);
}

/* ---- Add to Cart via AJAX ---- */
function addToCart(productId, qty = 1, size = '') {
    fetch('<?= SITE_URL ?>/ajax/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=add&product_id=${productId}&qty=${qty}&size=${encodeURIComponent(size)}&csrf_token=<?= csrfToken() ?>`
    })
    .then(r => r.json())
    .then(data => {
        if (data.need_size && data.url) {          // خاتم له قياسات -> نودّيها لصفحة المنتج تختار القياس
            showToast(data.msg, 'error');
            setTimeout(() => { location.href = data.url; }, 900);
            return;
        }
        if (data.success) {
            showToast(data.msg || '<?= LANG === "ar" ? "تمت الإضافة للسلة" : "Added to cart" ?>');
            const badge = document.querySelector('.cart-badge');
            if (badge) badge.textContent = data.cart_count;
            else {
                const cartIcon = document.querySelector('a[href*="cart"] .btn-icon, a[href*="cart"]');
                if (cartIcon) {
                    const b = document.createElement('span');
                    b.className = 'cart-badge'; b.textContent = data.cart_count;
                    cartIcon.style.position = 'relative';
                    cartIcon.appendChild(b);
                }
            }
        } else {
            showToast(data.msg || '<?= LANG === "ar" ? "حدث خطأ" : "Error occurred" ?>', 'error');
        }
    })
    .catch(() => showToast('<?= LANG === "ar" ? "حدث خطأ في الاتصال" : "Connection error" ?>', 'error'));
}

/* ---- Wishlist Toggle ---- */
function toggleWishlist(btn, productId) {
    const icon = btn.querySelector('i');
    const isActive = icon.classList.contains('fas');
    icon.className = isActive ? 'far fa-heart' : 'fas fa-heart';
    icon.style.color = isActive ? '' : '#C0392B';
    showToast(isActive
        ? '<?= LANG === "ar" ? "تمت الإزالة من المفضلة" : "Removed from wishlist" ?>'
        : '<?= LANG === "ar" ? "تمت الإضافة للمفضلة" : "Added to wishlist" ?>'
    );
    // يمكن إضافة AJAX request هنا
}

/* ---- Scroll: hide/show header ---- */
(function() {
    const header = document.querySelector('.site-header');
    let lastY = 0;
    window.addEventListener('scroll', () => {
        const y = window.scrollY;
        if (y > 100) {
            header.style.transform = y > lastY ? 'translateY(-100%)' : 'translateY(0)';
        } else {
            header.style.transform = 'translateY(0)';
        }
        header.style.transition = 'transform 0.3s ease, background 0.3s ease';
        lastY = y;
    }, { passive: true });
})();

/* ---- Gallery Thumbnails (product page) ---- */
function changeGalleryImage(src, thumb) {
    const main = document.querySelector('.gallery-main img');
    if (main) main.src = src;
    document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
    if (thumb) thumb.classList.add('active');
}

/* ---- Quantity Selector ---- */
function changeQty(btn, delta) {
    const input = btn.parentElement.querySelector('.qty-input');
    const min = parseInt(input.min) || 1;
    const max = parseInt(input.max) || 999;
    const newVal = Math.min(max, Math.max(min, parseInt(input.value) + delta));
    input.value = newVal;
}
</script>

</body>
</html>
