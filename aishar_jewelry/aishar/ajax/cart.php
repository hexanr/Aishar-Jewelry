<?php
// ajax/cart.php
require_once '../includes/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'msg'=>'Method not allowed']);
    exit;
}

$action = sanitize($_POST['action'] ?? '');

if ($action === 'add') {
    $pid = (int)($_POST['product_id'] ?? 0);
    $qty = max(1, (int)($_POST['qty'] ?? 1));
    $size = isset($_POST['size']) ? sanitize($_POST['size']) : null;
    $ok  = addToCart($pid, $qty, $size);
    if ($ok === 'need_size') {
        $st = getDB()->prepare("SELECT slug FROM products WHERE id=?");
        $st->execute([$pid]);
        echo json_encode([
            'success'   => false,
            'need_size' => true,
            'url'       => SITE_URL . '/product.php?slug=' . urlencode((string)$st->fetchColumn()),
            'msg'       => LANG==='ar' ? 'اختاري قياس الخاتم أولاً' : 'Please choose a ring size first',
        ]);
        exit;
    }
    echo json_encode([
        'success'    => $ok === true,
        'msg'        => $ok === true ? (LANG==='ar'?'تمت الإضافة للسلة':'Added to cart') : (LANG==='ar'?'المنتج غير متوفر':'Product not available'),
        'cart_count' => getCartCount(),
        'cart_total' => formatPrice(getCartTotal()),
    ]);
    exit;
}

if ($action === 'count') {
    echo json_encode(['count' => getCartCount()]);
    exit;
}

echo json_encode(['success'=>false,'msg'=>'Unknown action']);
