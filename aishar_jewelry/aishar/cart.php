<?php
// cart.php — صفحة السلة
require_once 'includes/config.php';

$db = getDB();

// ---- معالجة الإجراءات ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) { setFlash('error', LANG==='ar' ? 'طلب غير صالح' : 'Invalid request'); redirect('cart.php'); }
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'update') {
        $qtys = $_POST['qty'] ?? [];
        $ids  = array_values(array_unique(array_map(fn($i) => (int)$i['id'], getCart())));
        $stock = [];
        if ($ids) {
            $in = implode(',', array_fill(0, count($ids), '?'));
            $st = $db->prepare("SELECT id, stock FROM products WHERE id IN ($in)");
            $st->execute($ids);
            foreach ($st->fetchAll() as $r) $stock[(int)$r['id']] = (int)$r['stock'];
        }
        foreach ($qtys as $key => $q) {
            $key = (string)$key;
            if (!isset($_SESSION['cart'][$key])) continue;
            $q = (int)$q;
            if ($q < 1) { removeFromCart($key); continue; }
            $pid = (int)$_SESSION['cart'][$key]['id'];
            if (isset($stock[$pid])) $q = min($q, max(1, $stock[$pid]));
            $_SESSION['cart'][$key]['qty'] = $q;
        }
        setFlash('success', LANG==='ar' ? 'تم تحديث السلة' : 'Cart updated');
    }

    if ($action === 'remove') {
        removeFromCart((string)($_POST['cart_key'] ?? ''));
        setFlash('success', LANG==='ar' ? 'تم حذف المنتج' : 'Item removed');
    }

    if ($action === 'apply_coupon') {
        $code = strtoupper(sanitize($_POST['coupon_code'] ?? ''));
        $res  = validateCoupon($code, getCartTotal());
        if (!empty($res['valid'])) {
            $_SESSION['coupon'] = ['code' => $code, 'discount' => $res['discount']];
            setFlash('success', LANG==='ar' ? 'تم تطبيق كود الخصم' : 'Coupon applied');
        } else {
            unset($_SESSION['coupon']);
            setFlash('error', $res['msg'] ?? (LANG==='ar' ? 'كود الخصم غير صالح' : 'Invalid coupon'));
        }
    }

    if ($action === 'remove_coupon') {
        unset($_SESSION['coupon']);
        setFlash('success', LANG==='ar' ? 'تم إلغاء كود الخصم' : 'Coupon removed');
    }

    redirect('cart.php');
}

// ---- بيانات العرض ----
$cart     = getCart();
$subtotal = getCartTotal();
$coupon   = $_SESSION['coupon'] ?? null;
$discount = 0;
if ($coupon) {
    // إعادة التحقق لأن إجمالي السلة قد يتغير
    $res = validateCoupon($coupon['code'], $subtotal);
    if (!empty($res['valid'])) {
        $discount = (float)$res['discount'];
        $_SESSION['coupon']['discount'] = $discount;
    } else {
        unset($_SESSION['coupon']);
        $coupon = null;
    }
}
$shipping = $cart ? calcShipping(max(0, $subtotal - $discount)) : 0;
$total    = max(0, $subtotal - $discount + $shipping);
$freeMin  = (float)getSetting('free_shipping_min', '0');

$pageTitle = LANG==='ar' ? 'سلة المشتريات' : 'Shopping Cart';
include 'includes/header.php';
?>

<div class="container">
    <nav class="breadcrumb">
        <a href="index.php"><?= LANG==='ar'?'الرئيسية':'Home' ?></a>
        <span class="breadcrumb-sep"><i class="fa fa-chevron-left fa-xs"></i></span>
        <span><?= LANG==='ar'?'السلة':'Cart' ?></span>
    </nav>
</div>

<section class="section-pad-sm">
    <div class="container">
        <h1 style="font-family:var(--font-display);font-size:2rem;margin-bottom:2rem;font-style:italic">
            <?= LANG==='ar'?'سلة المشتريات':'Shopping Cart' ?>
        </h1>

        <?php if (empty($cart)): ?>
            <div style="text-align:center;padding:4rem 1rem">
                <i class="fa fa-shopping-bag" style="font-size:3rem;color:var(--gold);margin-bottom:1rem"></i>
                <p style="color:var(--text-muted);margin-bottom:1.5rem"><?= LANG==='ar'?'سلتك فارغة حالياً':'Your cart is empty' ?></p>
                <a href="shop.php" class="btn btn-gold btn-lg"><?= LANG==='ar'?'تسوّقي الآن':'Shop Now' ?></a>
            </div>
        <?php else: ?>
        <div class="cart-layout">

            <div>
                <form method="POST" id="cartForm">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="update">
                    <div class="cart-table">
                        <?php foreach ($cart as $pid => $item):
                            $img = !empty($item['image']) ? UPLOAD_URL . $item['image'] : 'assets/images/placeholder.svg';
                        ?>
                            <div class="cart-item">
                                <a href="<?= !empty($item['slug']) ? 'product.php?slug=' . e($item['slug']) : 'shop.php' ?>">
                                    <img class="cart-item-img" src="<?= e($img) ?>" alt="<?= e(t($item, 'name')) ?>"
                                         onerror="this.src='assets/images/placeholder.svg'">
                                </a>
                                <div>
                                    <div class="cart-item-name"><?= e(t($item, 'name')) ?></div>
                                    <?php if (!empty($item['size'])): ?>
                                        <div style="font-size:.82rem;color:var(--gold);font-weight:600"><?= LANG==='ar'?'القياس':'Size' ?>: <?= e($item['size']) ?></div>
                                    <?php endif; ?>
                                    <div class="cart-item-price"><?= formatPrice((float)$item['price']) ?></div>
                                </div>
                                <div>
                                    <input type="number" class="qty-input" name="qty[<?= e((string)$pid) ?>]" value="<?= (int)$item['qty'] ?>"
                                           min="0" max="99" style="border:1px solid var(--border);border-radius:var(--radius)"
                                           onchange="document.getElementById('cartForm').submit()">
                                </div>
                                <div style="text-align:center">
                                    <strong style="display:block;margin-bottom:.4rem"><?= formatPrice((float)$item['price'] * (int)$item['qty']) ?></strong>
                                    <button type="submit" form="rm<?= e(md5((string)$pid)) ?>" class="btn-icon" title="<?= LANG==='ar'?'حذف':'Remove' ?>"
                                            style="color:#C0392B"><i class="fa fa-trash"></i></button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-top:1rem">
                            <button type="submit" class="btn btn-outline"><?= LANG==='ar'?'تحديث السلة':'Update Cart' ?></button>
                            <a href="shop.php" class="btn btn-outline"><?= LANG==='ar'?'متابعة التسوق':'Continue Shopping' ?></a>
                        </div>
                    </div>
                </form>

                <?php foreach ($cart as $pid => $item): ?>
                    <form method="POST" id="rm<?= e(md5((string)$pid)) ?>">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="cart_key" value="<?= e((string)$pid) ?>">
                    </form>
                <?php endforeach; ?>
            </div>

            <aside class="cart-summary">
                <h3 class="summary-title"><?= LANG==='ar'?'ملخص الطلب':'Order Summary' ?></h3>

                <div class="summary-row"><span><?= LANG==='ar'?'المجموع':'Subtotal' ?></span><span><?= formatPrice($subtotal) ?></span></div>
                <?php if ($discount > 0): ?>
                    <div class="summary-row" style="color:#27AE60">
                        <span><?= LANG==='ar'?'الخصم':'Discount' ?> (<?= e($coupon['code']) ?>)</span>
                        <span>- <?= formatPrice($discount) ?></span>
                    </div>
                <?php endif; ?>
                <div class="summary-row">
                    <span><?= LANG==='ar'?'التوصيل':'Shipping' ?></span>
                    <span><?= $shipping > 0 ? formatPrice($shipping) : (LANG==='ar'?'مجاني':'Free') ?></span>
                </div>
                <?php $net = max(0, $subtotal - $discount); $perks = orderPerks($net); include 'includes/perks.php'; ?>
                <div class="summary-row total"><span><?= LANG==='ar'?'الإجمالي':'Total' ?></span><span><?= formatPrice($total) ?></span></div>

                <?php if ($coupon): ?>
                    <form method="POST" style="margin-top:1rem">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="remove_coupon">
                        <button type="submit" class="btn btn-outline w-100"><?= LANG==='ar'?'إلغاء كود الخصم':'Remove coupon' ?></button>
                    </form>
                <?php else: ?>
                    <form method="POST" style="display:flex;gap:.5rem;margin-top:1rem">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="apply_coupon">
                        <input type="text" name="coupon_code" class="form-control" dir="ltr"
                               placeholder="<?= LANG==='ar'?'كود الخصم':'Coupon code' ?>">
                        <button type="submit" class="btn btn-outline"><?= LANG==='ar'?'تطبيق':'Apply' ?></button>
                    </form>
                <?php endif; ?>

                <a href="checkout.php" class="btn btn-gold btn-lg w-100" style="margin-top:1.25rem;text-align:center">
                    <?= LANG==='ar'?'إتمام الطلب':'Checkout' ?>
                </a>
            </aside>

        </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
