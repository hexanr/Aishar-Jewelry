<?php
// lang.php
require_once 'includes/config.php';
$lang   = sanitize($_GET['lang'] ?? 'ar');
$return = sanitize($_GET['return'] ?? '/');
if (!in_array($lang, ['ar','en'])) $lang = 'ar';
$_SESSION['lang'] = $lang;
setcookie('lang', $lang, time()+31536000, '/');
// إزالة بارامتر lang من الـ return URL لتجنب loop
$return = preg_replace('/([?&])lang=[^&]+(&|$)/', '$1', $return);
$return = rtrim($return, '?&');
header('Location: ' . ($return ?: '/'));
exit;
