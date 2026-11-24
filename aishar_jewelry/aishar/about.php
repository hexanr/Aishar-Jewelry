<?php
require_once 'includes/config.php';
$pageTitle = LANG === 'ar' ? 'عن آيشار' : 'About AISHAR';
include 'includes/header.php';
?>

<!-- Hero -->
<section class="band-gold" style="padding:6rem 0 4rem;position:relative;overflow:hidden">
    <div style="position:absolute;inset:0;background:radial-gradient(ellipse at 30% 50%,rgba(201,168,76,.15),transparent 70%)"></div>
    <div class="container" style="position:relative">
        <div style="max-width:650px">
            <div style="color:var(--gold);font-size:.75rem;letter-spacing:.3em;text-transform:uppercase;margin-bottom:1rem">
                <?= LANG==='ar' ? 'قصتنا' : 'Our Story' ?>
            </div>
            <h1 style="font-family:var(--font-display);font-size:clamp(2.5rem,5vw,4rem);color:#fff;font-style:italic;line-height:1.2;margin-bottom:1.5rem">
                <?= LANG==='ar' ? 'آيشار — أكثر من مجرد إكسسوارات' : 'AISHAR — More Than Just Accessories' ?>
            </h1>
            <p style="color:rgba(255,255,255,.92);font-size:1.05rem;line-height:1.9">
                <?= LANG==='ar'
                    ? 'تأسست آيشار عام ٢٠٢٥ من شغف بالأناقة العصرية. نؤمن أن الجمال يبدأ بحسن الاختيار، فنجمع لكِ قطعاً مستوردة بعناية، بطلاء ذهب 18k وتصاميم تُكمل إطلالتكِ.'
                    : 'AISHAR was founded in 2025 from a passion for modern elegance. We believe beauty starts with choosing well, so we bring you carefully selected imported pieces in 18K gold plating, designed to complete your look.' ?>
            </p>
        </div>
    </div>
</section>

<!-- Values -->
<section class="section-pad">
    <div class="container">
        <div style="text-align:center;margin-bottom:3.5rem">
            <h2 class="section-title" style="display:inline-block">
                <?= LANG==='ar' ? 'قيمنا' : 'Our Values' ?>
            </h2>
            <div class="gold-divider" style="margin-top:1.5rem"></div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:2rem">
            <?php
            $values = LANG==='ar' ? [
                ['fa-gem',      'الجودة أولاً',       'نختار قطعاً بطلاء ذهب 18k بعناية لتدوم معكِ وتحافظ على بريقها'],
                ['fa-heart',    'اختيار بعناية',      'نستورد القطع جاهزة بعد اختيار دقيق، ونغلّفها بأناقة لتصل إليكِ كأجمل هدية'],
                ['fa-star',     'تصاميم عصرية',       'نتابع أحدث صيحات الإكسسوارات ونقدّم لكِ تصاميم راقية بأسعار مناسبة'],
            ] : [
                ['fa-gem',      'Quality First',      'We carefully choose 18K gold-plated pieces built to last and keep their shine'],
                ['fa-heart',    'Carefully Selected', 'We import ready-made pieces after careful selection and gift-wrap them elegantly'],
                ['fa-star',     'Modern Designs',     'We follow the latest accessory trends and bring you refined designs at fair prices'],
            ];
            foreach ($values as [$icon, $title, $desc]):
            ?>
                <div style="text-align:center;padding:2.5rem 1.5rem;background:var(--white);border-radius:var(--radius-lg);box-shadow:var(--shadow);border-bottom:3px solid var(--gold)">
                    <div style="width:64px;height:64px;background:rgba(201,168,76,.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;font-size:1.4rem;color:var(--gold)">
                        <i class="fa <?= $icon ?>"></i>
                    </div>
                    <h3 style="font-family:var(--font-display);font-size:1.3rem;margin-bottom:.75rem"><?= $title ?></h3>
                    <p style="color:var(--text-muted);font-size:.9rem;line-height:1.8"><?= $desc ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Story Section -->
<section class="section-pad" style="background:var(--bg-2)">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:center">
            <div>
                <div style="color:var(--gold);font-size:.75rem;letter-spacing:.2em;text-transform:uppercase;margin-bottom:1rem">
                    <?= LANG==='ar' ? 'من أين بدأنا' : 'Where We Started' ?>
                </div>
                <h2 style="font-family:var(--font-display);font-size:2.2rem;font-style:italic;margin-bottom:1.5rem;line-height:1.3">
                    <?= LANG==='ar' ? 'رحلة من الشغف إلى حسن الاختيار' : 'A Journey from Passion to Great Taste' ?>
                </h2>
                <p style="color:var(--text-muted);line-height:1.9;margin-bottom:1.25rem">
                    <?= LANG==='ar'
                        ? 'بدأت آيشار عام ٢٠٢٥ بفكرة بسيطة: أن تحصل كل امرأة على إكسسوارات أنيقة بطلاء ذهب 18k دون مبالغة في السعر. نتابع أحدث التصاميم، ونستورد القطع جاهزة بعد اختيار دقيق، ثم نفحصها ونغلّفها بعناية لتصلكِ كما تتمنين.'
                        : 'AISHAR began in 2025 with a simple idea: every woman deserves elegant 18K gold-plated accessories without overpaying. We follow the latest designs, import ready-made pieces after careful selection, then check and wrap them with care so they reach you just right.' ?>
                </p>
                <p style="color:var(--text-muted);line-height:1.9">
                    <?= LANG==='ar'
                        ? 'اليوم نقدّم خواتم وأساور وقلائد وأقراطاً وأطقماً وشباحيات تناسب كل مناسبة وكل ميزانية، ونضيف إليها قطعاً جديدة باستمرار.'
                        : 'Today we offer rings, bracelets, necklaces, earrings, sets and hand chains for every occasion and budget, and we keep adding new pieces all the time.' ?>
                </p>

                <div style="display:flex;gap:2rem;margin-top:2rem;padding-top:2rem;border-top:1px solid var(--border)">
                    <?php
                    $prodCount = (int)getDB()->query("SELECT COUNT(*) FROM products WHERE is_active=1")->fetchColumn();
                    $stats = [['٢٠٢٥','تأسست'],['18k','طلاء ذهب'],[strtr((string)$prodCount, ['0'=>'٠','1'=>'١','2'=>'٢','3'=>'٣','4'=>'٤','5'=>'٥','6'=>'٦','7'=>'٧','8'=>'٨','9'=>'٩']),'قطعة']];
                    if (LANG==='en') $stats=[['2025','Founded'],['18K','Gold Plated'],[(string)$prodCount,'Pieces']];
                    foreach ($stats as [$num,$label]): ?>
                        <div>
                            <div style="font-family:var(--font-display);font-size:1.8rem;color:var(--gold);font-weight:700"><?= $num ?></div>
                            <div style="font-size:.8rem;color:var(--text-muted)"><?= $label ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="position:relative">
                <img src="assets/images/about-story.jpg"
                     alt="AISHAR Jewelry"
                     loading="lazy"
                     style="width:100%;aspect-ratio:4/5;object-fit:cover;border-radius:var(--radius-lg);box-shadow:var(--shadow-lg)">
                <div style="position:absolute;bottom:-1.5rem;right:-1.5rem;background:var(--gold);color:#fff;padding:1.5rem;border-radius:var(--radius-lg);box-shadow:var(--shadow)">
                    <div style="font-family:var(--font-display);font-size:1.2rem;font-weight:700">18k</div>
                    <div style="font-size:.75rem;letter-spacing:.05em"><?= LANG==='ar'?'طلاء ذهب':'Gold Plated' ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="section-pad" style="text-align:center">
    <div class="container" style="max-width:600px">
        <h2 style="font-family:var(--font-display);font-size:2.2rem;font-style:italic;margin-bottom:1rem">
            <?= LANG==='ar' ? 'جاهزة للتسوق؟' : 'Ready to Shop?' ?>
        </h2>
        <p style="color:var(--text-muted);margin-bottom:2rem">
            <?= LANG==='ar' ? 'اكتشفي مجموعتنا الكاملة من الإكسسوارات' : 'Discover our full collection of accessories' ?>
        </p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
            <a href="shop.php" class="btn btn-gold btn-lg"><i class="fa fa-shopping-bag"></i> <?= LANG==='ar'?'تسوّقي الآن':'Shop Now' ?></a>
            <a href="contact.php" class="btn btn-outline btn-lg"><?= LANG==='ar'?'تواصلي معنا':'Contact Us' ?></a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
