<?php
/**
 * تفعيل عرض الأخطاء (مؤقت للاختبار)
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// dashboard.php - لوحة التحكم الرئيسية للمستخدم

require_once 'session_manager.php';
require_once 'db_config.php';

// التأكد من أن المستخدم مسجل للدخول
require_login(); 

// جلب بيانات المستخدم من الجلسة
$user_id = $_SESSION["user_id"];
$full_name = $_SESSION["full_name"];
$user_role = $_SESSION["user_role"];

// تحديد ما إذا كان المستخدم رائد أعمال أو مستثمر
$is_entrepreneur = ($user_role === 'entrepreneur');
$role_display = $is_entrepreneur ? 'رائد أعمال' : 'مستثمر';
$role_emoji = $is_entrepreneur ? '💡' : '💰';

// هنا يمكن إضافة منطق لجلب عدد الرسائل غير المقروءة، أو العروض التي نشرها المستخدم، أو العروض المفضلة.

// إغلاق آمن للاتصال
if (isset($link) && $link) {
    mysqli_close($link);
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INVESTOR | لوحة التحكم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css"> 
    <style>
        .dashboard-header {
            background-color: var(--bg-card);
            padding: 30px;
            border-bottom: 5px solid var(--color-info);
            margin-bottom: 30px;
        }
        .feature-card {
            min-height: 180px;
            transition: transform 0.3s;
            border-left: 5px solid var(--color-primary);
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }
    </style>
</head>
<body>

    <header class="navbar navbar-expand-lg navbar-dark bg-dark">...</header>

    <div class="container py-5">
        
        <div class="dashboard-header text-center rounded-3">
            <h1 class="mb-1" style="color: var(--color-info);">أهلاً بك، <?php echo htmlspecialchars($full_name); ?>!</h1>
            <p class="lead text-muted">
                <?php echo $role_emoji; ?> دورك الحالي: **<?php echo $role_display; ?>**
            </p>
            <a href="profile_settings.php" class="btn btn-sm btn-outline-warning mt-2">⚙️ إعدادات ملفك الشخصي</a>
        </div>
        
        <div class="row text-center mb-5">
            <div class="col-md-4">
                <div class="card p-3 bg-secondary text-white">
                    <h3 class="fw-bold">5</h3>
                    <p class="small mb-0">رسائل غير مقروءة</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 bg-secondary text-white">
                    <h3 class="fw-bold">12</h3>
                    <p class="small mb-0">إشعارات جديدة</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 bg-secondary text-white">
                    <h3 class="fw-bold">4.5</h3>
                    <p class="small mb-0">متوسط تقييمك</p>
                </div>
            </div>
        </div>

        <h2 class="mb-4" style="color: var(--color-primary);">أدواتك الرئيسية:</h2>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            
            <?php if ($is_entrepreneur): ?>
            <div class="col">
                <a href="submit_pitch.php" class="text-decoration-none">
                    <div class="card p-4 feature-card">
                        <h4 class="card-title">🚀 نشر عرض تمويل</h4>
                        <p class="card-text text-muted">اطرح فكرتك ومشروعك للح
