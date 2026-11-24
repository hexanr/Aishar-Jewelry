<?php
require_once '../includes/config.php';

if (isAdminLoggedIn()) { redirect(SITE_URL . '/admin/dashboard.php'); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) { $error = 'طلب غير صالح'; }
    else {
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = getDB()->prepare("SELECT * FROM admins WHERE (username=? OR email=?) AND is_active=1");
        $stmt->execute([$username, $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_role'] = $admin['role'];
            getDB()->prepare("UPDATE admins SET last_login=NOW() WHERE id=?")->execute([$admin['id']]);
            redirect(SITE_URL . '/admin/dashboard.php');
        } else {
            $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
        }
    }
}
$theme = $_COOKIE['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0E0E0E">
    <title>تسجيل الدخول — AISHAR Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <style>
        body{display:flex;align-items:center;justify-content:center;min-height:100vh;min-height:100dvh;padding:1.25rem;position:relative;overflow-x:hidden;
             background:linear-gradient(160deg,#FFFFFF 0%,#F6F5F2 55%,#ECEAE5 100%)}
        [data-theme="dark"] body{background:linear-gradient(160deg,#0B0B0C 0%,#121213 55%,#1A1A1C 100%)}
        body::before{content:"";position:fixed;inset:0;pointer-events:none;
             background-image:radial-gradient(circle at 78% 20%,rgba(168,130,46,.14) 0%,transparent 50%),radial-gradient(circle at 15% 85%,rgba(168,130,46,.08) 0%,transparent 45%)}
        .login-wrap{position:relative;width:100%;max-width:440px}
        .login-brand{text-align:center;margin-bottom:1.75rem}
        .login-brand .logo-img{height:64px;margin:0 auto}
        .login-brand small{display:block;margin-top:.6rem;font-size:.9rem;color:var(--text-muted);font-weight:600}
        .login-box{background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);
                   padding:clamp(1.5rem,5vw,2.5rem);box-shadow:var(--shadow-lg)}
        .login-box h2{font-family:var(--font-ar);font-size:1.4rem;font-weight:700;margin-bottom:1.5rem}
        .login-box .form-control{background:var(--bg-2)}
        .login-box .form-control:focus{border-color:var(--gold);box-shadow:0 0 0 3px rgba(168,130,46,.15)}
        .pass-wrap{position:relative}
        .pass-wrap .form-control{padding-inline-start:2.8rem}
        .pass-toggle{position:absolute;top:50%;inset-inline-start:.9rem;transform:translateY(-50%);color:var(--text-muted);background:none;border:0;cursor:pointer;font-size:1rem}
        .pass-toggle:hover{color:var(--gold)}
        .login-back{margin-top:1.5rem;text-align:center;font-size:.95rem}
        .login-back a{color:var(--gold);font-weight:600}
        .login-theme{position:fixed;top:1rem;inset-inline-end:1rem;z-index:5}
    </style>
</head>
<body>
<button class="theme-toggle login-theme" type="button" onclick="toggleTheme()" aria-label="تبديل الوضع الليلي">
    <span class="knob"></span><i class="fa fa-sun ico-sun"></i><i class="fa fa-moon ico-moon"></i>
</button>

<div class="login-wrap">
    <div class="login-brand">
        <img class="logo-img on-light" src="<?= SITE_URL ?>/assets/images/logo-dark.png" alt="AISHAR Jewelry">
        <img class="logo-img on-dark" src="<?= SITE_URL ?>/assets/images/logo-light.png" alt="">
        <small>لوحة التحكم</small>
    </div>

    <div class="login-box">
        <h2>تسجيل الدخول</h2>

        <?php if ($error): ?>
            <div class="flash flash-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <?= csrfField() ?>
            <div class="form-group">
                <label class="form-label">اسم المستخدم / البريد الإلكتروني</label>
                <input type="text" name="username" class="form-control" value="<?= e($_POST['username']??'') ?>"
                       placeholder="aishar" autocomplete="username" autofocus required>
            </div>
            <div class="form-group">
                <label class="form-label">كلمة المرور</label>
                <div class="pass-wrap">
                    <input type="password" name="password" id="passInput" class="form-control"
                           placeholder="••••••••" autocomplete="current-password" required>
                    <button type="button" class="pass-toggle" onclick="togglePass()" aria-label="إظهار كلمة المرور">
                        <i class="fa fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-gold w-100 btn-lg" style="margin-top:.5rem;justify-content:center">
                <i class="fa fa-sign-in-alt"></i> دخول
            </button>
        </form>

        <div class="login-back">
            <a href="<?= SITE_URL ?>"><i class="fa fa-arrow-right"></i> العودة للموقع</a>
        </div>
    </div>
</div>
<script>
function togglePass() {
    const inp = document.getElementById('passInput');
    const icon = document.getElementById('eyeIcon');
    if (inp.type==='password') { inp.type='text'; icon.className='fa fa-eye-slash'; }
    else { inp.type='password'; icon.className='fa fa-eye'; }
}
function toggleTheme() {
    const html = document.documentElement;
    const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    document.cookie = 'theme=' + next + ';path=/;max-age=31536000';
}
</script>
</body>
</html>
