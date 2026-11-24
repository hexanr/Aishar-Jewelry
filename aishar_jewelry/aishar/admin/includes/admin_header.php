<?php
require_once __DIR__ . '/../../includes/config.php';
requireAdmin();

$theme = $_COOKIE['theme'] ?? 'light';
$adminName = $_SESSION['admin_name'] ?? 'المدير';

$navItems = [
    ['dashboard.php',  'fa-tachometer-alt', 'لوحة التحكم', ''],
    ['products.php',   'fa-gem',            'المنتجات',    ''],
    ['categories.php', 'fa-tags',           'الفئات',      ''],
    ['orders.php',     'fa-shopping-bag',   'الطلبات',     ''],
    ['coupons.php',    'fa-ticket-alt',     'الكوبونات',   ''],
    ['messages.php',   'fa-envelope',       'الرسائل',     ''],
    ['settings.php',   'fa-cog',            'الإعدادات',   ''],
];

// عدد الرسائل غير المقروءة
try {
    $unreadMsg = (int)getDB()->query("SELECT COUNT(*) FROM messages WHERE is_read=0")->fetchColumn();
    $pendingOrders = (int)getDB()->query("SELECT COUNT(*) FROM orders WHERE order_status='pending'")->fetchColumn();
} catch(Exception $e) { $unreadMsg = 0; $pendingOrders = 0; }

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>AISHAR Admin | <?= e($pageTitle ?? '') ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= SITE_URL ?>/assets/images/favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <script>
    // القائمة الجانبية: مطوية افتراضياً، ونتذكر اختيارك
    try { if (localStorage.getItem('adminSidebar') !== 'open') document.documentElement.classList.add('sb-collapsed'); }
    catch (e) { document.documentElement.classList.add('sb-collapsed'); }
    </script>
</head>
<body>
<div class="admin-layout">

    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="admin-logo" style="display:flex;align-items:center;gap:.75rem"><img class="logo-img" src="<?= SITE_URL ?>/assets/images/logo-light.png" alt="AISHAR" style="height:34px"> <span style="font-size:.65rem;color:rgba(255,255,255,.4);letter-spacing:.05em">ADMIN</span><i class="fa fa-gem admin-logo-mini"></i></div>
        <nav class="admin-nav">
            <?php foreach ($navItems as [$page, $icon, $label, $section]): ?>
                <?php if ($section): ?><div class="admin-nav-section"><?= $section ?></div><?php endif; ?>
                <a href="<?= SITE_URL ?>/admin/<?= $page ?>" class="<?= $currentPage===$page?'active':'' ?>" title="<?= e($label) ?>">
                    <i class="fa <?= $icon ?>"></i>
                    <span class="nav-label"><?= $label ?></span>
                    <?php if ($page==='messages.php' && $unreadMsg>0): ?>
                        <span class="admin-nav-badge"><?= $unreadMsg ?></span>
                    <?php elseif ($page==='orders.php' && $pendingOrders>0): ?>
                        <span class="admin-nav-badge"><?= $pendingOrders ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
            <a href="<?= SITE_URL ?>" target="_blank" style="margin-top:auto" title="معاينة الموقع">
                <i class="fa fa-external-link-alt"></i> <span class="nav-label">معاينة الموقع</span>
            </a>
            <a href="<?= SITE_URL ?>/admin/logout.php" title="تسجيل الخروج">
                <i class="fa fa-sign-out-alt"></i> <span class="nav-label">تسجيل الخروج</span>
            </a>
        </nav>
    </aside>

    <!-- Main -->
    <div class="admin-main">
        <div class="admin-topbar">
            <div style="display:flex;align-items:center;gap:.9rem">
            <button type="button" class="sb-toggle" id="sbToggle" onclick="toggleSidebar()" aria-label="القائمة" title="فتح / طي القائمة">
                <i class="fa fa-bars"></i>
            </button>
            <div>
                <div class="admin-page-title"><?= e($pageTitle ?? '') ?></div>
                <?php if (!empty($pageSubtitle)): ?>
                    <div class="admin-page-subtitle"><?= e($pageSubtitle) ?></div>
                <?php endif; ?>
            </div>
            </div>
            <div style="display:flex;align-items:center;gap:1rem">
                <button class="theme-toggle" onclick="toggleAdminTheme()">
                    <i class="fa <?= $theme==='dark'?'fa-sun':'fa-moon' ?>" id="adminThemeIcon"></i>
                </button>
                <div style="font-size:.85rem;color:var(--text-muted)">
                    <i class="fa fa-user-circle" style="color:var(--gold)"></i>
                    <?= e($adminName) ?>
                </div>
            </div>
        </div>

        <?php $flash = getFlash(); if ($flash): ?>
        <div class="flash flash-<?= $flash['type'] ?>" style="margin:1rem 2rem 0">
            <i class="fa fa-<?= $flash['type']==='success'?'check-circle':'exclamation-circle' ?>"></i>
            <?= e($flash['msg']) ?>
        </div>
        <?php endif; ?>

        <div class="admin-content">
