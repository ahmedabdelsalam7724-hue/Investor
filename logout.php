<?php
// logout.php - معالجة تسجيل خروج المستخدم

// تضمين ملف إدارة الجلسات الذي يحتوي على دالة logout()
require_once 'session_manager.php';

// استدعاء دالة تسجيل الخروج لإنهاء الجلسة
logout();

// لا حاجة لأي كود آخر هنا، لأن دالة logout() تقوم بالتوجيه (header("location: index.php")) ثم الخروج (exit).
?>
