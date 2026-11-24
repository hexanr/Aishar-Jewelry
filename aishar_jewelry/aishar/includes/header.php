<?php
// includes/header.php
require_once __DIR__ . '/config.php';

// اسم البراند يظهر أولاً في تاب المتصفح وفي معاينة الرابط
$siteName  = 'AISHAR Jewelry';
$pageTitle = isset($pageTitle) && $pageTitle !== '' ? $siteName . ' | ' . $pageTitle : $siteName;
$ogImage   = $ogImage ?? (SITE_URL . '/assets/images/hero-jewelry.jpg');
$ogUrl     = SITE_URL . '/' . ltrim(basename($_SERVER['PHP_SELF']), '/') . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
$pageDesc  = $pageDesc ?? getSetting('site_description_' . LANG, '');
$cartCount = getCartCount();
$theme     = $_COOKIE['theme'] ?? 'light';

$navLinks = LANG === 'ar' ? [
    'index.php'       => 'الرئيسية',
    'shop.php'        => 'المتجر',
    'about.php'       => 'عن آيشار',
    'contact.php'     => 'تواصلي',
] : [
    'index.php'       => 'Home',
    'shop.php'        => 'Shop',
    'about.php'       => 'About',
    'contact.php'     => 'Contact',
];

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="<?= LANG ?>" dir="<?= DIR ?>" data-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDesc) ?>">
    <meta name="theme-color" content="#0E0E0E">
    <meta property="og:site_name" content="<?= e($siteName) ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDesc) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= e($ogUrl) ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <meta property="og:locale" content="<?= LANG === 'ar' ? 'ar_AR' : 'en_US' ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($pageTitle) ?>">
    <meta name="twitter:image" content="<?= e($ogImage) ?>">
    <meta name="application-name" content="AISHAR Jewelry">
    <meta name="apple-mobile-web-app-title" content="AISHAR">
    <link rel="icon" type="image/svg+xml" href="<?= SITE_URL ?>/assets/images/favicon.svg">
    <link rel="apple-touch-icon" href="<?= SITE_URL ?>/assets/images/favicon.svg">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- CSS — مسار نسبي يعمل في أي مكان -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body class="<?= LANG === 'en' ? 'ltr' : '' ?>">

<header class="site-header">
    <div class="container">
        <div class="header-inner">
            <a href="<?= SITE_URL ?>/index.php" class="logo" aria-label="AISHAR Jewelry"><img class="logo-img on-light" src="<?= SITE_URL ?>/assets/images/logo-dark.png" alt="AISHAR Jewelry"><img class="logo-img on-dark" src="<?= SITE_URL ?>/assets/images/logo-light.png" alt=""></a>

            <nav class="main-nav">
                <?php foreach ($navLinks as $url => $label): ?>
                    <a href="<?= SITE_URL ?>/<?= $url ?>" class="<?= $currentPage===$url?'active':'' ?>">
                        <?= $label ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="header-actions">
                <button class="btn-icon" onclick="toggleSearch()" title="بحث">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                </button>
                <a href="<?= SITE_URL ?>/cart.php" class="btn-icon" title="السلة" style="position:relative">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    <?php if ($cartCount > 0): ?>
                        <span class="cart-badge"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= SITE_URL ?>/lang.php?lang=<?= switchLang(LANG) ?>&return=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="lang-btn">
                    <?= LANG==='ar' ? 'EN' : 'ع' ?>
                </a>
                <button class="theme-toggle" onclick="toggleTheme()" aria-label="<?= LANG==='ar'?'تبديل الوضع الليلي':'Toggle dark mode' ?>" title="<?= LANG==='ar'?'الوضع الليلي':'Dark mode' ?>">
                    <span class="knob"></span>
                    <svg class="ico-sun" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
                    <svg class="ico-moon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                </button>
                <button class="hamburger" onclick="toggleMobileNav()">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </div>
</header>

<nav class="mobile-nav" id="mobileNav">
    <?php foreach ($navLinks as $url => $label): ?>
        <a href="<?= SITE_URL ?>/<?= $url ?>" class="<?= $currentPage===$url?'active':'' ?>"><?= $label ?></a>
    <?php endforeach; ?>
</nav>

<!-- Search Overlay -->
<div class="modal-overlay" id="searchOverlay" onclick="if(event.target===this)toggleSearch()">
    <div class="modal" style="max-width:600px">
        <div class="modal-header">
            <h3 class="modal-title"><?= LANG==='ar'?'ابحثي عن إكسسواراتك':'Search Accessories' ?></h3>
            <button onclick="toggleSearch()"><i class="fa fa-times"></i></button>
        </div>
        <form action="<?= SITE_URL ?>/shop.php" method="GET">
            <div style="display:flex;gap:.75rem">
                <input type="text" name="q" class="form-control"
                       placeholder="<?= LANG==='ar'?'خاتم، سوار، طوق...':'Ring, bracelet, necklace...' ?>" autofocus>
                <button type="submit" class="btn btn-gold"><i class="fa fa-search"></i></button>
            </div>
        </form>
    </div>
</div>

<?php $flash = getFlash(); if ($flash): ?>
<div class="flash flash-<?= $flash['type'] ?>"
     style="position:fixed;top:calc(var(--header-h)+1rem);right:1rem;left:1rem;max-width:500px;margin:0 auto;z-index:9000">
    <i class="fa fa-<?= $flash['type']==='success'?'check-circle':'exclamation-circle' ?>"></i>
    <?= e($flash['msg']) ?>
</div>
<script>setTimeout(()=>document.querySelector('.flash')?.remove(),4000);</script>
<?php endif; ?>

<div class="toast-container" id="toastContainer"></div>

<main style="padding-top:var(--header-h)">
