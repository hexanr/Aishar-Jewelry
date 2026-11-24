<?php
// includes/perks.php — عرض مزايا الطلب (يتوقع المتغيرين $perks و $net)
$next = null;
foreach ($perks as $pk) { if (!$pk['ok']) { $next = $pk; break; } }
?>
<div class="perks">
    <?php if ($next): ?>
        <div class="perks-hint">
            <?= LANG==='ar' ? 'أضيفي ' : 'Add ' ?><strong><?= formatPrice($next['left']) ?></strong>
            <?= LANG==='ar' ? 'لتحصلي على ' : 'to get ' ?><strong><?= e($next['label']) ?></strong>
        </div>
        <div class="perks-bar"><span style="width:<?= min(100, round($net / max(1,$next['min']) * 100)) ?>%"></span></div>
    <?php else: ?>
        <div class="perks-hint"><strong><?= LANG==='ar' ? 'مبروك! حصلتي على كل المزايا' : 'You unlocked every perk!' ?></strong></div>
    <?php endif; ?>
    <?php foreach ($perks as $pk): ?>
        <div class="perk <?= $pk['ok'] ? 'on' : '' ?>">
            <i class="fa <?= $pk['icon'] ?>"></i>
            <span><?= e($pk['label']) ?></span>
            <small><?= $pk['ok'] ? (LANG==='ar'?'مفعّل':'Unlocked') : (LANG==='ar'?'من ':'from ') . formatPrice($pk['min']) ?></small>
        </div>
    <?php endforeach; ?>
</div>
