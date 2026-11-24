<?php
// order-success.php
require_once 'includes/config.php';

$orderNumber = sanitize($_GET['order'] ?? '');
$order = null;

if ($orderNumber) {
    $stmt = getDB()->prepare("SELECT * FROM orders WHERE order_number=?");
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch();
}

$pageTitle = LANG==='ar' ? 'تم الطلب بنجاح' : 'Order Placed Successfully';
include 'includes/header.php';
?>

<section style="min-height:70vh;display:flex;align-items:center">
    <div class="container" style="text-align:center;max-width:600px;margin:0 auto;padding:4rem 1.5rem">
        <div style="width:80px;height:80px;background:rgba(39,174,96,.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 2rem;font-size:2rem;color:#27AE60">
            <i class="fa fa-check-circle"></i>
        </div>

        <h1 style="font-family:var(--font-display);font-size:2.2rem;font-style:italic;margin-bottom:1rem">
            <?= LANG==='ar' ? 'شكراً لكِ! 💛' : 'Thank You! 💛' ?>
        </h1>

        <?php if ($order): ?>
            <p style="color:var(--text-muted);margin-bottom:2rem;line-height:1.8">
                <?= LANG==='ar'
                    ? "تم استلام طلبك بنجاح. سنتواصل معك قريباً لتأكيد الطلب وترتيب التوصيل."
                    : "Your order has been received successfully. We'll contact you soon to confirm and arrange delivery." ?>
            </p>

            <div style="background:var(--bg-2);border-radius:var(--radius-lg);padding:2rem;margin-bottom:2rem;border:1px solid var(--border)">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;text-align:right">
                    <div>
                        <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:.25rem"><?= LANG==='ar'?'رقم الطلب':'Order Number' ?></div>
                        <div style="font-weight:700;color:var(--gold)"><?= e($order['order_number']) ?></div>
                    </div>
                    <div>
                        <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:.25rem"><?= LANG==='ar'?'الإجمالي':'Total' ?></div>
                        <div style="font-weight:700"><?= formatPrice((float)$order['total']) ?></div>
                    </div>
                    <div>
                        <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:.25rem"><?= LANG==='ar'?'طريقة الدفع':'Payment' ?></div>
                        <div><?= e(['cod'=>(LANG==='ar'?'عند الاستلام':'Cash on Delivery'),'zaincash'=>(LANG==='ar'?'زين كاش':'Zain Cash'),'mastercard'=>(LANG==='ar'?'ماستر كارد':'Mastercard')][$order['payment_method']] ?? $order['payment_method']) ?></div>
                    </div>
                    <div>
                        <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:.25rem"><?= LANG==='ar'?'الحالة':'Status' ?></div>
                        <span class="status-badge status-pending"><?= LANG==='ar'?'قيد المعالجة':'Processing' ?></span>
                    </div>
                </div>
            </div>

            <?php if ($order['payment_method'] === 'zaincash'): $zc = getSetting('zaincash_number',''); ?>
                <div class="pay-note">
                    <strong><?= LANG==='ar'?'خطوة الدفع عبر زين كاش':'Zain Cash payment step' ?></strong>
                    <p><?= LANG==='ar'
                        ? 'حوّلي ' . formatPrice((float)$order['total']) . ($zc ? ' إلى الرقم: ' . e($zc) : '') . ' ثم أرسلي صورة التحويل مع رقم الطلب على واتساب لنؤكد طلبك.'
                        : 'Please transfer ' . formatPrice((float)$order['total']) . ($zc ? ' to: ' . e($zc) : '') . ' then send the receipt with your order number on WhatsApp.' ?></p>
                </div>
            <?php elseif ($order['payment_method'] === 'mastercard'): ?>
                <div class="pay-note">
                    <strong><?= LANG==='ar'?'الدفع بماستر كارد':'Paying by Mastercard' ?></strong>
                    <p><?= LANG==='ar'
                        ? 'سنرسل لك رابط الدفع الآمن عبر واتساب خلال وقت قصير لإتمام طلبك.'
                        : 'We will send you the secure payment link on WhatsApp shortly to complete your order.' ?></p>
                </div>
            <?php endif; ?>

            <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
                <a href="index.php" class="btn btn-gold btn-lg">
                    <?= LANG==='ar'?'العودة للرئيسية':'Back to Home' ?>
                </a>
                <a href="https://wa.me/<?= getSetting('site_whatsapp') ?>?text=<?= urlencode('رقم طلبي: '.$order['order_number']) ?>"
                   target="_blank" class="btn btn-outline btn-lg">
                    <i class="fab fa-whatsapp"></i>
                    <?= LANG==='ar'?'تابعي طلبك':'Track Order' ?>
                </a>
            </div>
        <?php else: ?>
            <p style="color:var(--text-muted);margin-bottom:2rem"><?= LANG==='ar'?'شكراً لشرائك من آيشار!':'Thank you for shopping with AISHAR!' ?></p>
            <a href="index.php" class="btn btn-gold btn-lg"><?= LANG==='ar'?'العودة للرئيسية':'Back to Home' ?></a>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
