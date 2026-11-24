<?php
require_once '../includes/config.php';
$pageTitle    = 'لوحة التحكم';
$pageSubtitle = 'مرحباً بكِ في نظام إدارة آيشار';
include 'includes/admin_header.php';

$db = getDB();

// الإحصائيات
$stats = [
    'orders_total'   => $db->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'orders_today'   => $db->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at)=CURDATE()")->fetchColumn(),
    'revenue_month'  => $db->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW()) AND order_status NOT IN ('cancelled')")->fetchColumn(),
    'revenue_total'  => $db->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE order_status NOT IN ('cancelled')")->fetchColumn(),
    'products_count' => $db->query("SELECT COUNT(*) FROM products WHERE is_active=1")->fetchColumn(),
    'messages_new'   => $db->query("SELECT COUNT(*) FROM messages WHERE is_read=0")->fetchColumn(),
];

// آخر الطلبات
$recentOrders = $db->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 8")->fetchAll();

// الطلبات حسب الحالة (للرسم البياني)
$statusCounts = $db->query("
    SELECT order_status, COUNT(*) as cnt FROM orders GROUP BY order_status
")->fetchAll(PDO::FETCH_KEY_PAIR);

// أكثر المنتجات مبيعاً
$topProducts = $db->query("
    SELECT p.name_ar, SUM(oi.quantity) AS sold, SUM(oi.total_price) AS revenue
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    GROUP BY oi.product_id
    ORDER BY sold DESC LIMIT 5
")->fetchAll();
?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon"><i class="fa fa-shopping-bag"></i></div>
        <div>
            <div class="stat-label">إجمالي الطلبات</div>
            <div class="stat-value"><?= number_format($stats['orders_total']) ?></div>
            <div class="stat-change"><i class="fa fa-arrow-up"></i> <?= $stats['orders_today'] ?> اليوم</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fa fa-coins"></i></div>
        <div>
            <div class="stat-label">إيرادات هذا الشهر</div>
            <div class="stat-value" style="font-size:1.3rem"><?= formatPrice((float)$stats['revenue_month']) ?></div>
            <div class="stat-change"><?= formatPrice((float)$stats['revenue_total']) ?> إجمالي</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fa fa-gem"></i></div>
        <div>
            <div class="stat-label">المنتجات النشطة</div>
            <div class="stat-value"><?= $stats['products_count'] ?></div>
            <div class="stat-change"><i class="fa fa-check"></i> متاحة للبيع</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fa fa-envelope"></i></div>
        <div>
            <div class="stat-label">رسائل جديدة</div>
            <div class="stat-value"><?= $stats['messages_new'] ?></div>
            <?php if ($stats['messages_new'] > 0): ?>
                <a href="messages.php" style="font-size:.75rem;color:var(--gold)">عرض الرسائل ←</a>
            <?php else: ?>
                <div class="stat-change">لا توجد رسائل جديدة</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Two Columns -->
<div style="display:grid;grid-template-columns:1fr 340px;gap:1.5rem;align-items:start">

    <!-- Recent Orders -->
    <div class="data-table-wrap">
        <div class="table-header">
            <h3>آخر الطلبات</h3>
            <a href="orders.php" class="btn btn-outline btn-sm">عرض الكل</a>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>رقم الطلب</th>
                    <th>العميلة</th>
                    <th>المبلغ</th>
                    <th>الحالة</th>
                    <th>التاريخ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentOrders as $o): ?>
                    <tr data-href="orders.php?view=<?= $o['id'] ?>">
                        <td style="font-weight:600;color:var(--gold)"><?= e($o['order_number']) ?></td>
                        <td><?= e($o['customer_name']) ?></td>
                        <td style="font-weight:600"><?= formatPrice((float)$o['total']) ?></td>
                        <td><span class="status-badge status-<?= $o['order_status'] ?>">
                            <?php
                            $statusMap = ['pending'=>'قيد الانتظار','confirmed'=>'مؤكد','processing'=>'قيد التجهيز','shipped'=>'تم الشحن','delivered'=>'تم التسليم','cancelled'=>'ملغي'];
                            echo $statusMap[$o['order_status']] ?? $o['order_status'];
                            ?>
                        </span></td>
                        <td style="color:var(--text-muted);font-size:.82rem"><?= date('d/m/Y', strtotime($o['created_at'])) ?></td>
                        <td><a href="orders.php?view=<?= $o['id'] ?>" class="btn btn-outline btn-sm">عرض</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($recentOrders)): ?>
                    <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted)">لا توجد طلبات بعد</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Sidebar Widgets -->
    <div style="display:flex;flex-direction:column;gap:1.5rem">

        <!-- Order Status -->
        <div class="data-table-wrap">
            <div class="table-header"><h3>الطلبات حسب الحالة</h3></div>
            <div style="padding:1.25rem">
                <?php
                $allStatuses = [
                    'pending'    => ['قيد الانتظار', '#F39C12'],
                    'confirmed'  => ['مؤكد',          '#3498DB'],
                    'processing' => ['قيد التجهيز',  '#9B59B6'],
                    'shipped'    => ['تم الشحن',      '#1ABC9C'],
                    'delivered'  => ['تم التسليم',   '#27AE60'],
                    'cancelled'  => ['ملغي',           '#C0392B'],
                ];
                $total_orders = max(1, array_sum($statusCounts));
                foreach ($allStatuses as $status => [$label, $color]):
                    $cnt = $statusCounts[$status] ?? 0;
                    $pct = round($cnt / $total_orders * 100);
                ?>
                    <div style="margin-bottom:1rem">
                        <div style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:.3rem">
                            <span><?= $label ?></span>
                            <span style="font-weight:600"><?= $cnt ?></span>
                        </div>
                        <div style="height:5px;background:var(--bg-2);border-radius:3px;overflow:hidden">
                            <div style="height:100%;width:<?= $pct ?>%;background:<?= $color ?>;border-radius:3px;transition:width .5s"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Top Products -->
        <div class="data-table-wrap">
            <div class="table-header"><h3>أكثر المنتجات مبيعاً</h3></div>
            <div style="padding:0 1.25rem 1.25rem">
                <?php if (!empty($topProducts)): ?>
                    <?php foreach ($topProducts as $i => $p): ?>
                        <div style="display:flex;align-items:center;gap:.75rem;padding:.75rem 0;border-bottom:1px solid var(--border)">
                            <span style="width:24px;height:24px;background:<?= $i===0?'var(--gold)':'var(--bg-2)' ?>;color:<?= $i===0?'#fff':'var(--text)' ?>;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;flex-shrink:0"><?= $i+1 ?></span>
                            <div style="flex:1;font-size:.85rem">
                                <div style="font-weight:500"><?= e($p['name_ar']) ?></div>
                                <div style="color:var(--text-muted);font-size:.75rem"><?= $p['sold'] ?> قطعة</div>
                            </div>
                            <span style="font-weight:600;color:var(--gold);font-size:.85rem"><?= formatPrice((float)$p['revenue']) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align:center;color:var(--text-muted);padding:1.5rem 0">لا توجد مبيعات بعد</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div style="background:var(--white);border-radius:var(--radius-lg);padding:1.5rem;box-shadow:var(--shadow)">
            <h3 style="font-size:.9rem;margin-bottom:1rem;color:var(--text-muted);letter-spacing:.1em;text-transform:uppercase">إجراءات سريعة</h3>
            <div style="display:flex;flex-direction:column;gap:.75rem">
                <a href="products.php?action=add" class="btn btn-gold btn-sm">
                    <i class="fa fa-plus"></i> إضافة منتج جديد
                </a>
                <a href="orders.php?status=pending" class="btn btn-outline btn-sm">
                    <i class="fa fa-clock"></i> الطلبات المعلقة (<?= $statusCounts['pending']??0 ?>)
                </a>
                <a href="messages.php?filter=unread" class="btn btn-outline btn-sm">
                    <i class="fa fa-envelope"></i> الرسائل غير المقروءة (<?= $stats['messages_new'] ?>)
                </a>
                <a href="<?= SITE_URL ?>" target="_blank" class="btn btn-outline btn-sm">
                    <i class="fa fa-external-link-alt"></i> معاينة الموقع
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>
