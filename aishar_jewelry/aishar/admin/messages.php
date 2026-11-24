<?php
// admin/messages.php
require_once '../includes/config.php';
$pageTitle = 'الرسائل';
$db = getDB();

if ($_SERVER['REQUEST_METHOD']==='POST' && verifyCsrf()) {
    $id = (int)$_POST['id'];
    $db->prepare("UPDATE messages SET is_read=1, replied_at=NOW() WHERE id=?")->execute([$id]);
    setFlash('success','تم تحديد الرسالة كمقروءة');
    redirect(SITE_URL.'/admin/messages.php');
}

if (isset($_GET['delete']) && verifyCsrf()) {
    $db->prepare("DELETE FROM messages WHERE id=?")->execute([(int)$_GET['delete']]);
    setFlash('success','تم حذف الرسالة');
    redirect(SITE_URL.'/admin/messages.php');
}

$filter  = sanitize($_GET['filter']??'');
$where   = $filter==='unread' ? 'WHERE is_read=0' : '';
$messages= $db->query("SELECT * FROM messages $where ORDER BY created_at DESC")->fetchAll();
$pageSubtitle = count($messages) . ' رسالة';

include 'includes/admin_header.php';
?>

<div style="display:flex;gap:.75rem;margin-bottom:1.5rem">
    <a href="messages.php" class="btn <?= !$filter?'btn-gold':'btn-outline' ?> btn-sm">كل الرسائل</a>
    <a href="messages.php?filter=unread" class="btn <?= $filter==='unread'?'btn-gold':'btn-outline' ?> btn-sm">
        غير المقروءة
        <?php $unread=$db->query("SELECT COUNT(*) FROM messages WHERE is_read=0")->fetchColumn();
        if ($unread): ?> <span class="admin-nav-badge"><?= $unread ?></span><?php endif; ?>
    </a>
</div>

<div style="display:flex;flex-direction:column;gap:1rem">
    <?php foreach ($messages as $msg): ?>
        <div style="background:var(--white);border-radius:var(--radius-lg);padding:1.5rem;box-shadow:var(--shadow);border-right:4px solid <?= $msg['is_read']?'var(--border)':'var(--gold)' ?>">
            <div style="display:flex;justify-content:space-between;align-items:start;flex-wrap:wrap;gap:1rem">
                <div>
                    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.5rem">
                        <strong><?= e($msg['name']) ?></strong>
                        <?php if (!$msg['is_read']): ?>
                            <span class="badge badge-new" style="font-size:.65rem">جديد</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size:.8rem;color:var(--text-muted);display:flex;gap:1rem;flex-wrap:wrap">
                        <a href="mailto:<?= e($msg['email']) ?>" style="color:var(--gold)"><?= e($msg['email']) ?></a>
                        <?php if ($msg['phone']): ?>
                            <a href="tel:<?= e($msg['phone']) ?>"><?= e($msg['phone']) ?></a>
                        <?php endif; ?>
                        <span><?= date('d/m/Y H:i',strtotime($msg['created_at'])) ?></span>
                    </div>
                    <?php if ($msg['subject']): ?>
                        <div style="font-weight:600;margin-top:.5rem;color:var(--gold)"><?= e($msg['subject']) ?></div>
                    <?php endif; ?>
                </div>
                <div style="display:flex;gap:.5rem">
                    <?php if (!$msg['is_read']): ?>
                        <form method="POST">
                            <?= csrfField() ?>
                            <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                            <button type="submit" class="btn btn-outline btn-sm"><i class="fa fa-check"></i> قراءة</button>
                        </form>
                    <?php endif; ?>
                    <a href="mailto:<?= e($msg['email']) ?>?subject=<?= urlencode('رد: '.($msg['subject']??'رسالتك')) ?>" class="btn btn-gold btn-sm">
                        <i class="fa fa-reply"></i> رد
                    </a>
                    <a href="messages.php?delete=<?= $msg['id'] ?>&csrf_token=<?= csrfToken() ?>"
                       onclick="return confirmDelete('حذف الرسالة؟')" class="btn btn-sm" style="background:rgba(192,57,43,.1);color:#C0392B">
                        <i class="fa fa-trash"></i>
                    </a>
                </div>
            </div>
            <p style="margin-top:1rem;color:var(--text-muted);font-size:.9rem;line-height:1.7;padding-top:1rem;border-top:1px solid var(--border)">
                <?= nl2br(e($msg['message'])) ?>
            </p>
        </div>
    <?php endforeach; ?>
    <?php if (empty($messages)): ?>
        <div class="empty-state">
            <div class="empty-icon"><i class="fa fa-envelope-open"></i></div>
            <h3 class="empty-title">لا توجد رسائل</h3>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/admin_footer.php'; ?>
