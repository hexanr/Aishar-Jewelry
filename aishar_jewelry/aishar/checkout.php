<?php
require_once 'includes/config.php';

$cart = getCart();
if (empty($cart)) { redirect('cart.php'); }

$subtotal = getCartTotal();
$coupon   = $_SESSION['coupon'] ?? null;
$discount = $coupon['discount'] ?? 0;
$shipping = calcShipping($subtotal - $discount);
$total    = max(0, $subtotal - $discount + $shipping);
$net      = max(0, $subtotal - $discount);
$perks    = orderPerks($net);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) { setFlash('error','طلب غير صالح'); redirect('checkout.php'); }

    $name    = sanitize($_POST['name'] ?? '');
    $email   = sanitize($_POST['email'] ?? '');
    $phone   = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city    = sanitize($_POST['city'] ?? '');
    $notes   = sanitize($_POST['notes'] ?? '');
    $payment = sanitize($_POST['payment'] ?? 'cod');
    if (!in_array($payment, ['cod','zaincash','mastercard'], true)) $payment = 'cod';

    if (!$name)              $errors[] = LANG==='ar'?'الاسم مطلوب':'Name is required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = LANG==='ar'?'البريد الإلكتروني غير صالح':'Invalid email';
    if (!$phone)             $errors[] = LANG==='ar'?'رقم الهاتف مطلوب':'Phone is required';
    if (!$address)           $errors[] = LANG==='ar'?'العنوان مطلوب':'Address is required';
    if (!$city)              $errors[] = LANG==='ar'?'المدينة مطلوبة':'City is required';

    if (empty($errors)) {
        $db     = getDB();
        ensureSizeColumns();
        $orderN = generateOrderNumber();
        $perkNames = [];
        foreach ($perks as $pk) { if ($pk['ok']) $perkNames[] = $pk['label_ar']; }
        if ($perkNames) $notes = trim($notes . "\n[مزايا الطلب: " . implode('، ', $perkNames) . ']');

        $db->prepare("
            INSERT INTO orders (order_number,customer_name,customer_email,customer_phone,
                shipping_address,shipping_city,subtotal,shipping_cost,discount_amount,total,
                coupon_code,payment_method,notes)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)
        ")->execute([$orderN,$name,$email,$phone,$address,$city,
            $subtotal,$shipping,$discount,$total,
            $coupon['code']??null,$payment,$notes]);

        $orderId = $db->lastInsertId();

        foreach ($cart as $item) {
            $db->prepare("
                INSERT INTO order_items (order_id,product_id,product_name_ar,product_name_en,size,quantity,unit_price,total_price)
                VALUES (?,?,?,?,?,?,?,?)
            ")->execute([$orderId,$item['id'],$item['name_ar'],$item['name_en'],$item['size'] ?? null,
                $item['qty'],$item['price'],$item['price']*$item['qty']]);
            $db->prepare("UPDATE products SET stock=stock-? WHERE id=? AND stock>0")->execute([$item['qty'],$item['id']]);
        }

        // رفع count الكوبون
        if ($coupon) {
            $db->prepare("UPDATE coupons SET used_count=used_count+1 WHERE code=?")->execute([$coupon['code']]);
        }

        clearCart();
        unset($_SESSION['coupon']);

        redirect('order-success.php?order=' . $orderN);
    }
}

$pageTitle = LANG==='ar' ? 'إتمام الطلب' : 'Checkout';
include 'includes/header.php';
?>

<div class="container">
    <nav class="breadcrumb">
        <a href="index.php"><?= LANG==='ar'?'الرئيسية':'Home' ?></a>
        <span class="breadcrumb-sep"><i class="fa fa-chevron-left fa-xs"></i></span>
        <a href="cart.php"><?= LANG==='ar'?'السلة':'Cart' ?></a>
        <span class="breadcrumb-sep"><i class="fa fa-chevron-left fa-xs"></i></span>
        <span><?= LANG==='ar'?'إتمام الطلب':'Checkout' ?></span>
    </nav>
</div>

<section class="section-pad-sm">
    <div class="container">
        <h1 style="font-family:var(--font-display);font-size:2rem;margin-bottom:2rem;font-style:italic">
            <?= LANG==='ar'?'إتمام الطلب':'Checkout' ?>
        </h1>

        <?php if (!empty($errors)): ?>
            <div class="flash flash-error" style="margin-bottom:1.5rem">
                <i class="fa fa-exclamation-circle"></i>
                <ul style="margin:0;padding-right:1rem">
                    <?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST">
            <?= csrfField() ?>
            <div class="cart-layout">

                <!-- Billing -->
                <div>
                    <div style="background:var(--white);border-radius:var(--radius-lg);padding:2rem;box-shadow:var(--shadow);margin-bottom:1.5rem">
                        <h3 style="font-family:var(--font-display);font-size:1.2rem;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:1px solid var(--border)">
                            <i class="fa fa-user" style="color:var(--gold);margin-left:.5rem"></i>
                            <?= LANG==='ar'?'معلومات التوصيل':'Delivery Information' ?>
                        </h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label"><?= LANG==='ar'?'الاسم الكامل':'Full Name' ?> *</label>
                                <input type="text" name="name" class="form-control"
                                       value="<?= e($_POST['name']??'') ?>"
                                       placeholder="<?= LANG==='ar'?'آيشار محمد':'Aishar Mohammed' ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label"><?= LANG==='ar'?'رقم الهاتف':'Phone Number' ?> *</label>
                                <input type="tel" name="phone" class="form-control"
                                       value="<?= e($_POST['phone']??'') ?>"
                                       placeholder="07xxxxxxxxx" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label"><?= LANG==='ar'?'البريد الإلكتروني':'Email' ?> *</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= e($_POST['email']??'') ?>"
                                   placeholder="example@email.com" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label"><?= LANG==='ar'?'العنوان':'Address' ?> *</label>
                            <input type="text" name="address" class="form-control"
                                   value="<?= e($_POST['address']??'') ?>"
                                   placeholder="<?= LANG==='ar'?'الحي، الشارع، رقم المبنى':'Neighborhood, Street, Building' ?>" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label"><?= LANG==='ar'?'المدينة':'City' ?> *</label>
                                <select name="city" class="form-control" required>
                                    <option value=""><?= LANG==='ar'?'اختاري المدينة':'Select City' ?></option>
                                    <?php
                                    $cities = ['بغداد','البصرة','نينوى','أربيل','السليمانية','دهوك','كركوك','النجف','كربلاء','بابل','ذي قار','ميسان','واسط','القادسية','المثنى','صلاح الدين','الأنبار','ديالى','حلبجة'];
                                    foreach ($cities as $city):
                                    ?>
                                        <option value="<?= e($city) ?>" <?= ($_POST['city']??'')===$city?'selected':'' ?>>
                                            <?= e($city) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label"><?= LANG==='ar'?'الدولة':'Country' ?></label>
                                <input type="text" class="form-control" value="<?= LANG==='ar'?'العراق':'Iraq' ?>" readonly
                                       style="background:var(--bg-2);cursor:not-allowed">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label"><?= LANG==='ar'?'ملاحظات (اختياري)':'Notes (optional)' ?></label>
                            <textarea name="notes" class="form-control" rows="3"
                                      placeholder="<?= LANG==='ar'?'أي تعليمات خاصة للتوصيل...':'Any special delivery instructions...' ?>"><?= e($_POST['notes']??'') ?></textarea>
                        </div>
                    </div>

                    <!-- Payment -->
                    <div style="background:var(--white);border-radius:var(--radius-lg);padding:2rem;box-shadow:var(--shadow)">
                        <h3 style="font-family:var(--font-display);font-size:1.2rem;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:1px solid var(--border)">
                            <i class="fa fa-credit-card" style="color:var(--gold);margin-left:.5rem"></i>
                            <?= LANG==='ar'?'طريقة الدفع':'Payment Method' ?>
                        </h3>

                        <?php
                        $payMethods = LANG==='ar' ? [
                            'cod'        => ['fa-money-bill-wave', 'الدفع عند الاستلام', 'ادفعي نقداً عند وصول طلبك'],
                            'zaincash'   => ['fa-wallet',          'زين كاش',            'حوّلي المبلغ عبر زين كاش وأرسلي إشعار التحويل على واتساب'],
                            'mastercard' => ['fa-credit-card',     'ماستر كارد',         'نرسل لك رابط دفع آمن عبر واتساب بعد تأكيد الطلب'],
                        ] : [
                            'cod'        => ['fa-money-bill-wave', 'Cash on Delivery', 'Pay cash when your order arrives'],
                            'zaincash'   => ['fa-wallet',          'Zain Cash',        'Transfer via Zain Cash and send the receipt on WhatsApp'],
                            'mastercard' => ['fa-credit-card',     'Mastercard',       'We send you a secure payment link on WhatsApp after confirming'],
                        ];
                        foreach ($payMethods as $val => [$icon, $label, $desc]):
                        ?>
                            <label style="display:flex;align-items:center;gap:1rem;padding:1rem;border:2px solid var(--border);border-radius:var(--radius);margin-bottom:.75rem;cursor:pointer;transition:border-color var(--transition)"
                                   onclick="this.style.borderColor='var(--gold)';document.querySelectorAll('.pay-label').forEach(el=>el!==this&&(el.style.borderColor='var(--border)'));" class="pay-label">
                                <input type="radio" name="payment" value="<?= $val ?>" style="accent-color:var(--gold)"
                                       <?= ($val==='cod')?'checked':'' ?>>
                                <i class="fa <?= $icon ?>" style="color:var(--gold);font-size:1.2rem;width:20px"></i>
                                <div>
                                    <div style="font-weight:600;font-size:.9rem"><?= $label ?></div>
                                    <div style="font-size:.78rem;color:var(--text-muted)"><?= $desc ?></div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Summary -->
                <div class="cart-summary">
                    <h3 class="summary-title"><?= LANG==='ar'?'ملخص الطلب':'Order Summary' ?></h3>

                    <?php foreach ($cart as $item): ?>
                        <div style="display:flex;gap:.75rem;align-items:center;margin-bottom:.75rem;font-size:.88rem">
                            <img src="<?= e(!empty($item['image'])?UPLOAD_URL.$item['image']:'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=60&q=80') ?>"
                                 style="width:48px;height:48px;object-fit:cover;border-radius:var(--radius);flex-shrink:0"
                                 onerror="this.src='https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=60&q=80'">
                            <div style="flex:1">
                                <div style="font-weight:500"><?= e(t($item,'name')) ?><?php if (!empty($item['size'])): ?> <span style="color:var(--gold)">(<?= LANG==='ar'?'قياس':'Size' ?> <?= e($item['size']) ?>)</span><?php endif; ?></div>
                                <div style="color:var(--text-muted)">× <?= $item['qty'] ?></div>
                            </div>
                            <span style="color:var(--gold);font-weight:600"><?= formatPrice($item['price']*$item['qty']) ?></span>
                        </div>
                    <?php endforeach; ?>

                    <div style="border-top:1px solid var(--border);padding-top:1rem;margin-top:.5rem">
                        <div class="summary-row">
                            <span><?= LANG==='ar'?'المجموع':'Subtotal' ?></span>
                            <span><?= formatPrice($subtotal) ?></span>
                        </div>
                        <?php if ($discount > 0): ?>
                        <div class="summary-row" style="color:#27AE60">
                            <span><?= LANG==='ar'?'خصم':'Discount' ?></span>
                            <span>−<?= formatPrice($discount) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="summary-row">
                            <span><?= LANG==='ar'?'الشحن':'Shipping' ?></span>
                            <span><?= $shipping==0 ? '<span style="color:#27AE60">'.( LANG==='ar'?'مجاني':'Free').'</span>' : formatPrice($shipping) ?></span>
                        </div>
                        <div class="summary-row total">
                            <span><?= LANG==='ar'?'الإجمالي':'Total' ?></span>
                            <span><?= formatPrice($total) ?></span>
                        </div>
                    </div>

                    <?php include 'includes/perks.php'; ?>

                    <button type="submit" class="btn btn-gold btn-lg w-100" style="margin-top:1rem">
                        <i class="fa fa-check-circle"></i>
                        <?= LANG==='ar'?'تأكيد الطلب':'Place Order' ?>
                    </button>

                    <div style="text-align:center;margin-top:1rem;font-size:.75rem;color:var(--text-muted)">
                        <i class="fa fa-lock" style="color:var(--gold)"></i>
                        <?= LANG==='ar'?'بياناتك محمية وآمنة':'Your data is protected and secure' ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
