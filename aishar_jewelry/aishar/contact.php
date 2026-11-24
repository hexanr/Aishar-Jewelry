<?php
require_once 'includes/config.php';
$pageTitle = LANG === 'ar' ? 'تواصلي معنا' : 'Contact Us';

$success = false;
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) { $errors[] = 'طلب غير صالح'; }
    else {
        $name    = sanitize($_POST['name'] ?? '');
        $email   = sanitize($_POST['email'] ?? '');
        $phone   = sanitize($_POST['phone'] ?? '');
        $subject = sanitize($_POST['subject'] ?? '');
        $message = sanitize($_POST['message'] ?? '');

        if (!$name)    $errors[] = LANG==='ar' ? 'الاسم مطلوب' : 'Name is required';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = LANG==='ar' ? 'البريد غير صالح' : 'Invalid email';
        if (!$message) $errors[] = LANG==='ar' ? 'الرسالة مطلوبة' : 'Message is required';

        if (empty($errors)) {
            getDB()->prepare("INSERT INTO messages (name,email,phone,subject,message) VALUES (?,?,?,?,?)")
                   ->execute([$name,$email,$phone,$subject,$message]);
            $success = true;
        }
    }
}

include 'includes/header.php';
?>

<section style="padding:4rem 0 2rem">
    <div class="container">
        <div style="text-align:center;margin-bottom:3rem">
            <div style="color:var(--gold);font-size:.75rem;letter-spacing:.3em;text-transform:uppercase;margin-bottom:.75rem">
                <?= LANG==='ar' ? 'نحن هنا لمساعدتك' : 'We\'re Here to Help' ?>
            </div>
            <h1 class="section-title" style="display:inline-block">
                <?= LANG==='ar' ? 'تواصلي معنا' : 'Contact Us' ?>
            </h1>
            <div class="gold-divider" style="margin-top:1.5rem"></div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:3rem;align-items:start">

            <!-- Info -->
            <div style="display:flex;flex-direction:column;gap:1.5rem">
                <?php
                $infos = LANG==='ar' ? [
                    ['fa-phone',    'اتصلي بنا',         getSetting('site_phone'), 'tel:'.getSetting('site_phone')],
                    ['fa-envelope', 'البريد الإلكتروني',  getSetting('site_email'), 'mailto:'.getSetting('site_email')],
                    ['fab fa-whatsapp', 'واتساب',         getSetting('site_phone'), 'https://wa.me/'.getSetting('site_whatsapp')],
                    ['fab fa-instagram','إنستجرام',       '@'.instagramHandle(), socialUrl('instagram')],
                ] : [
                    ['fa-phone',    'Call Us',     getSetting('site_phone'), 'tel:'.getSetting('site_phone')],
                    ['fa-envelope', 'Email',       getSetting('site_email'), 'mailto:'.getSetting('site_email')],
                    ['fab fa-whatsapp','WhatsApp', getSetting('site_phone'), 'https://wa.me/'.getSetting('site_whatsapp')],
                    ['fab fa-instagram','Instagram','@'.instagramHandle(), socialUrl('instagram')],
                ];
                foreach ($infos as [$icon, $label, $value, $href]):
                ?>
                    <a href="<?= $href ?>" target="_blank"
                       style="display:flex;align-items:center;gap:1rem;padding:1.25rem;background:var(--white);border-radius:var(--radius-lg);box-shadow:var(--shadow);transition:all var(--transition);text-decoration:none;color:var(--text)"
                       onmouseover="this.style.transform='translateY(-3px)';this.style.borderRight='3px solid var(--gold)'"
                       onmouseout="this.style.transform='';this.style.borderRight=''">
                        <div style="width:48px;height:48px;background:rgba(201,168,76,.1);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--gold);font-size:1.1rem;flex-shrink:0">
                            <i class="<?= $icon ?>"></i>
                        </div>
                        <div>
                            <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.2rem"><?= $label ?></div>
                            <div style="font-weight:600"><?= e($value) ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>

                <div class="band-gold" style="border-radius:var(--radius-lg);padding:1.5rem;text-align:center">
                    <div style="color:var(--gold);font-size:.75rem;letter-spacing:.15em;text-transform:uppercase;margin-bottom:.75rem">
                        <?= LANG==='ar' ? 'ساعات العمل' : 'Working Hours' ?>
                    </div>
                    <div style="color:#fff;font-weight:600">
                        <?= LANG==='ar' ? 'السبت – الخميس' : 'Sat – Thu' ?>
                    </div>
                    <div style="color:rgba(255,255,255,.92);font-size:.9rem;margin-top:.25rem">
                        <?= LANG==='ar' ? '٩ صباحاً – ١١ مساءً' : '9 AM – 11 PM' ?>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div style="background:var(--white);border-radius:var(--radius-lg);padding:2rem;box-shadow:var(--shadow)">
                <?php if ($success): ?>
                    <div style="text-align:center;padding:3rem 1rem">
                        <div style="width:72px;height:72px;background:rgba(39,174,96,.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;font-size:2rem;color:#27AE60">
                            <i class="fa fa-check-circle"></i>
                        </div>
                        <h3 style="font-family:var(--font-display);font-size:1.5rem;margin-bottom:.75rem">
                            <?= LANG==='ar' ? 'شكراً لتواصلك!' : 'Thank you for reaching out!' ?>
                        </h3>
                        <p style="color:var(--text-muted)">
                            <?= LANG==='ar' ? 'تم استلام رسالتك وسنرد عليكِ في أقرب وقت ممكن.' : 'Your message has been received and we\'ll reply as soon as possible.' ?>
                        </p>
                        <a href="contact.php" class="btn btn-outline" style="margin-top:1.5rem">
                            <?= LANG==='ar' ? 'إرسال رسالة أخرى' : 'Send Another Message' ?>
                        </a>
                    </div>
                <?php else: ?>
                    <h3 style="font-family:var(--font-display);font-size:1.3rem;margin-bottom:1.5rem;font-style:italic">
                        <?= LANG==='ar' ? 'أرسلي لنا رسالة' : 'Send Us a Message' ?>
                    </h3>

                    <?php if (!empty($errors)): ?>
                        <div class="flash flash-error">
                            <?php foreach ($errors as $e): ?><div><?= e($e) ?></div><?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <?= csrfField() ?>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label"><?= LANG==='ar'?'الاسم':'Name' ?> *</label>
                                <input type="text" name="name" class="form-control" required
                                       value="<?= e($_POST['name']??'') ?>" placeholder="<?= LANG==='ar'?'اسمكِ الكريم':'Your name' ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label"><?= LANG==='ar'?'رقم الهاتف':'Phone' ?></label>
                                <input type="tel" name="phone" class="form-control"
                                       value="<?= e($_POST['phone']??'') ?>" placeholder="07xxxxxxxxx">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= LANG==='ar'?'البريد الإلكتروني':'Email' ?> *</label>
                            <input type="email" name="email" class="form-control" required
                                   value="<?= e($_POST['email']??'') ?>" placeholder="example@email.com" dir="ltr">
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= LANG==='ar'?'الموضوع':'Subject' ?></label>
                            <input type="text" name="subject" class="form-control"
                                   value="<?= e($_POST['subject']??'') ?>"
                                   placeholder="<?= LANG==='ar'?'موضوع رسالتك':'Message subject' ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= LANG==='ar'?'الرسالة':'Message' ?> *</label>
                            <textarea name="message" class="form-control" rows="5" required
                                      placeholder="<?= LANG==='ar'?'اكتبي رسالتك هنا...':'Write your message here...' ?>"><?= e($_POST['message']??'') ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-gold btn-lg w-100">
                            <i class="fa fa-paper-plane"></i>
                            <?= LANG==='ar' ? 'إرسال الرسالة' : 'Send Message' ?>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
