<?php
require_once '../includes/config.php';
$pageTitle = 'إدارة الطلبات';

$db     = getDB();
$viewId = (int)($_GET['view'] ?? 0);

// تحديث حالة الطلب
if ($_SERVER['REQUEST_METHOD']==='POST' && verifyCsrf()) {
    $oid    = (int)$_POST['order_id'];
    $status = sanitize($_POST['order_status']??'');
    $track  = sanitize($_POST['tracking_number']??'');
    $valid  = ['pending','confirmed','processing','shipped','delivered','cancelled','returned'];
    if (in_array($status,$valid)) {
        $db->prepare("UPDATE orders SET order_status=?, tracking_number=?, updated_at=NOW() WHERE id=?")
           ->execute([$status,$track,$oid]);
        setFlash('success','تم تحديث حالة الطلب');
    }
    redirect("orders.php?view=$oid");
}

// عرض طلب واحد
if ($viewId) {
    $order = $db->prepare("SELECT * FROM orders WHERE id=?");
    $order->execute([$viewId]); $order = $order->fetch();
    if (!$order) { setFlash('error','الطلب غير موجود'); redirect(SITE_URL.'/admin/orders.php'); }

    $items = $db->prepare("SELECT * FROM order_items WHERE order_id=?");
    $items->execute([$viewId]); $items = $items->fetchAll();

    $pageTitle    = 'الطلب #' . $order['order_number'];
    $pageSubtitle = 'تفاصيل الطلب';
    include 'includes/admin_header.php';
?>
<div style="margin-bottom:1.5rem">
    <a href="orders.php" class="btn btn-outline btn-sm"><i class="fa fa-arrow-right"></i> العودة للطلبات</a>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:1.5rem;align-items:start">
    <div style="display:flex;flex-direction:column;gap:1.5rem">

        <!-- Order Items -->
        <div class="data-table-wrap">
            <div class="table-header">
                <h3>منتجات الطلب</h3>
                <span class="status-badge status-<?= $order['order_status'] ?>">
                    <?php $sm=['pending'=>'قيد الانتظار','confirmed'=>'مؤكد','processing'=>'قيد التجهيز','shipped'=>'تم الشحن','delivered'=>'تم التسليم','cancelled'=>'ملغي','returned'=>'مُرجع'];
                    echo $sm[$order['order_status']]??$order['order_status']; ?>
                </span>
            </div>
            <table class="data-table">
                <thead><tr><th>المنتج</th><th>الكمية</th><th>سعر الوحدة</th><th>الإجمالي</th></tr></thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <div style="font-weight:600"><?= e($item['product_name_ar']??$item['product_name_en']) ?></div>
                                <?php if (!empty($item['size'])): ?>
                                    <div style="color:var(--gold);font-size:.82rem;font-weight:700">القياس: <?= e($item['size']) ?></div>
                                <?php endif; ?>
                                <?php if ($item['product_sku']): ?>
                                    <div style="color:var(--text-muted);font-size:.75rem"><?= e($item['product_sku']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= formatPrice((float)$item['unit_price']) ?></td>
                            <td style="font-weight:600;color:var(--gold)"><?= formatPrice((float)$item['total_price']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="background:var(--bg-2)">
                        <td colspan="3" style="font-weight:700;text-align:right">المجموع الفرعي</td>
                        <td style="font-weight:700"><?= formatPrice((float)$order['subtotal']) ?></td>
                    </tr>
                    <?php if ($order['discount_amount']>0): ?>
                    <tr><td colspan="3" style="color:#27AE60">الخصم (<?= e($order['coupon_code']??'') ?>)</td>
                        <td style="color:#27AE60">−<?= formatPrice((float)$order['discount_amount']) ?></td></tr>
                    <?php endif; ?>
                    <tr><td colspan="3">الشحن</td>
                        <td><?= $order['shipping_cost']==0?'<span style="color:#27AE60">مجاني</span>':formatPrice((float)$order['shipping_cost']) ?></td></tr>
                    <tr style="font-size:1.1rem">
                        <td colspan="3" style="font-weight:700">الإجمالي</td>
                        <td style="font-weight:700;color:var(--gold)"><?= formatPrice((float)$order['total']) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Customer Info -->
        <div style="background:var(--white);border-radius:var(--radius-lg);padding:1.75rem;box-shadow:var(--shadow)">
            <h3 style="font-size:1rem;margin-bottom:1.25rem;padding-bottom:.75rem;border-bottom:1px solid var(--border)">معلومات العميلة</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;font-size:.9rem">
                <div><span style="color:var(--text-muted)">الاسم:</span> <strong><?= e($order['customer_name']) ?></strong></div>
                <div><span style="color:var(--text-muted)">الهاتف:</span>
                    <a href="tel:<?= e($order['customer_phone']) ?>" style="color:var(--gold)"><?= e($order['customer_phone']) ?></a>
                </div>
                <div><span style="color:var(--text-muted)">البريد:</span> <?= e($order['customer_email']) ?></div>
                <div><span style="color:var(--text-muted)">المدينة:</span> <?= e($order['shipping_city']) ?></div>
                <div style="grid-column:span 2"><span style="color:var(--text-muted)">العنوان:</span> <?= e($order['shipping_address']) ?></div>
                <?php if ($order['notes']): ?>
                <div style="grid-column:span 2"><span style="color:var(--text-muted)">ملاحظات:</span> <?= e($order['notes']) ?></div>
                <?php endif; ?>
            </div>
            <div style="margin-top:1rem;display:flex;gap:.75rem">
                <a href="https://wa.me/<?= preg_replace('/\D/','',$order['customer_phone']) ?>?text=<?= urlencode('مرحباً '.$order['customer_name'].' بخصوص طلبك رقم '.$order['order_number']) ?>"
                   target="_blank" class="btn btn-outline btn-sm">
                    <i class="fab fa-whatsapp" style="color:#25D366"></i> تواصل عبر واتساب
                </a>
                <a href="mailto:<?= e($order['customer_email']) ?>" class="btn btn-outline btn-sm">
                    <i class="fa fa-envelope"></i> إرسال بريد
                </a>
            </div>
        </div>
    </div>

    <!-- Update Status -->
    <div style="display:flex;flex-direction:column;gap:1.5rem">
        <div style="background:var(--white);border-radius:var(--radius-lg);padding:1.75rem;box-shadow:var(--shadow)">
            <h3 style="font-size:1rem;margin-bottom:1.25rem;padding-bottom:.75rem;border-bottom:1px solid var(--border)">تحديث الحالة</h3>
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                <div class="form-group">
                    <label class="form-label">حالة الطلب</label>
                    <select name="order_status" class="form-control">
                        <?php foreach(['pending'=>'قيد الانتظار','confirmed'=>'مؤكد','processing'=>'قيد التجهيز','shipped'=>'تم الشحن','delivered'=>'تم التسليم','cancelled'=>'ملغي'] as $v=>$l): ?>
                            <option value="<?= $v ?>" <?= $order['order_status']===$v?'selected':'' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">رقم التتبع</label>
                    <input type="text" name="tracking_number" class="form-control" dir="ltr"
                           value="<?= e($order['tracking_number']??'') ?>" placeholder="اختياري">
                </div>
                <button type="submit" class="btn btn-gold w-100">
                    <i class="fa fa-save"></i> حفظ التحديث
                </button>
            </form>
        </div>

        <!-- Order Info -->
        <div style="background:var(--white);border-radius:var(--radius-lg);padding:1.5rem;box-shadow:var(--shadow)">
            <h3 style="font-size:1rem;margin-bottom:1rem;padding-bottom:.75rem;border-bottom:1px solid var(--border)">معلومات الطلب</h3>
            <div style="font-size:.88rem;display:flex;flex-direction:column;gap:.6rem">
                <div class="summary-row"><span style="color:var(--text-muted)">رقم الطلب</span><strong style="color:var(--gold)"><?= e($order['order_number']) ?></strong></div>
                <div class="summary-row"><span style="color:var(--text-muted)">تاريخ الطلب</span><span><?= date('d/m/Y H:i',strtotime($order['created_at'])) ?></span></div>
                <div class="summary-row"><span style="color:var(--text-muted)">طريقة الدفع</span>
                    <span><?= ['cod'=>'عند الاستلام','zaincash'=>'زين كاش','mastercard'=>'ماستر كارد'][$order['payment_method']]??$order['payment_method'] ?></span>
                </div>
                <div class="summary-row"><span style="color:var(--text-muted)">حالة الدفع</span>
                    <span class="status-badge <?= $order['payment_status']==='paid'?'status-delivered':'status-pending' ?>">
                        <?= ['pending'=>'معلق','paid'=>'مدفوع','failed'=>'فشل','refunded'=>'مُسترد'][$order['payment_status']]??$order['payment_status'] ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
    include 'includes/admin_footer.php';
    exit;
}

// ===== قائمة الطلبات =====
$statusFilter = sanitize($_GET['status']??'');
$search = sanitize($_GET['q']??'');
$page   = max(1,(int)($_GET['page']??1));
$perPage= 20;

$where  = ['1=1']; $params = [];
if ($statusFilter) { $where[] = "order_status=?"; $params[] = $statusFilter; }
if ($search) { $where[] = "(order_number LIKE ? OR customer_name LIKE ? OR customer_phone LIKE ?)"; $s="%$search%"; $params=array_merge($params,[$s,$s,$s]); }
$whereSQL = implode(' AND ',$where);

$total   = (int)$db->prepare("SELECT COUNT(*) FROM orders WHERE $whereSQL")->execute($params) && false ?: (function() use ($db,$whereSQL,$params){ $s=$db->prepare("SELECT COUNT(*) FROM orders WHERE $whereSQL"); $s->execute($params); return (int)$s->fetchColumn(); })();
$pag     = paginate($total,$perPage,$page);
$orders  = $db->prepare("SELECT * FROM orders WHERE $whereSQL ORDER BY created_at DESC LIMIT $perPage OFFSET {$pag['offset']}");
$orders->execute($params); $orders = $orders->fetchAll();

$statusCounts = $db->query("SELECT order_status,COUNT(*) FROM orders GROUP BY order_status")->fetchAll(PDO::FETCH_KEY_PAIR);
$pageSubtitle = "إجمالي: $total طلب";
include 'includes/admin_header.php';
?>

<!-- Status Filter Tabs -->
<div style="display:flex;gap:.5rem;margin-bottom:1.5rem;flex-wrap:wrap">
    <a href="orders.php" class="btn <?= !$statusFilter?'btn-gold':'btn-outline' ?> btn-sm">الكل (<?= $total ?>)</a>
    <?php foreach(['pending'=>'قيد الانتظار','confirmed'=>'مؤكد','processing'=>'قيد التجهيز','shipped'=>'تم الشحن','delivered'=>'تم التسليم','cancelled'=>'ملغي'] as $s=>$l): ?>
        <a href="orders.php?status=<?= $s ?>" class="btn <?= $statusFilter===$s?'btn-gold':'btn-outline' ?> btn-sm">
            <?= $l ?><?= isset($statusCounts[$s]) ? ' ('.$statusCounts[$s].')' : '' ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="data-table-wrap">
    <div class="table-header">
        <h3>الطلبات</h3>
        <form style="display:flex;gap:.5rem">
            <input type="text" name="q" class="form-control" placeholder="بحث برقم الطلب أو الاسم..." value="<?= e($search) ?>" style="width:240px">
            <?php if ($statusFilter): ?><input type="hidden" name="status" value="<?= e($statusFilter) ?>"><?php endif; ?>
            <button type="submit" class="btn btn-outline btn-sm"><i class="fa fa-search"></i></button>
        </form>
    </div>
    <table class="data-table">
        <thead><tr>
            <th>رقم الطلب</th><th>العميلة</th><th>المدينة</th>
            <th>الإجمالي</th><th>الدفع</th><th>الحالة</th><th>التاريخ</th><th></th>
        </tr></thead>
        <tbody>
            <?php foreach ($orders as $o): ?>
                <tr data-href="orders.php?view=<?= $o['id'] ?>">
                    <td style="font-weight:700;color:var(--gold)"><?= e($o['order_number']) ?></td>
                    <td>
                        <div style="font-weight:500"><?= e($o['customer_name']) ?></div>
                        <div style="font-size:.75rem;color:var(--text-muted)"><?= e($o['customer_phone']??'') ?></div>
                    </td>
                    <td style="font-size:.85rem"><?= e($o['shipping_city']??'') ?></td>
                    <td style="font-weight:700"><?= formatPrice((float)$o['total']) ?></td>
                    <td><span class="status-badge <?= $o['payment_status']==='paid'?'status-delivered':'status-pending' ?>">
                        <?= ['pending'=>'معلق','paid'=>'مدفوع','failed'=>'فشل','refunded'=>'مُسترد'][$o['payment_status']]??$o['payment_status'] ?>
                    </span></td>
                    <td><span class="status-badge status-<?= $o['order_status'] ?>">
                        <?php $sm=['pending'=>'انتظار','confirmed'=>'مؤكد','processing'=>'تجهيز','shipped'=>'شحن','delivered'=>'تسليم','cancelled'=>'ملغي'];
                        echo $sm[$o['order_status']]??$o['order_status']; ?>
                    </span></td>
                    <td style="font-size:.8rem;color:var(--text-muted)"><?= date('d/m H:i',strtotime($o['created_at'])) ?></td>
                    <td><a href="orders.php?view=<?= $o['id'] ?>" class="btn btn-outline btn-sm">عرض</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($orders)): ?>
                <tr><td colspan="8" style="text-align:center;padding:3rem;color:var(--text-muted)">لا توجد طلبات</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php if ($pag['pages']>1): ?>
        <div class="pagination" style="padding:1rem">
            <?php for($i=1;$i<=$pag['pages'];$i++): ?>
                <a href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>"
                   class="page-link <?= $i===$pag['current']?'active':'' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/admin_footer.php'; ?>
