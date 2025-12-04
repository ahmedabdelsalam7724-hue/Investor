<?php
// index.php
require_once 'session_manager.php'; // يتضمن session_start()
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INVESTOR | المنصة الرائدة لربط المستثمرين ورواد الأعمال</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css"> 
    <style>
        /* إضافة ستايل بسيط للصفحة الرئيسية */
        .hero-section {
            background: linear-gradient(135deg, #0f4c75, #1d2d50); /* لون داكن جذاب */
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        .cta-button {
            font-size: 1.25rem;
            padding: 10px 30px;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">INVESTOR</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="pitches_list.php">العروض والفرص</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="acquisitions.php">الاستحواذ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="problem_forum.php">المنتدى</a>
                    </li>
                </ul>
                
                <div class="d-flex">
                    <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <a href="dashboard.php" class="btn btn-sm btn-outline-success me-2">لوحة التحكم</a>
                        <a href="messaging.php" class="btn btn-sm btn-outline-info me-2">الرسائل</a>
                        <a href="notifications.php" class="btn btn-sm btn-outline-warning me-2">الإشعارات</a>
                        <a href="logout.php" class="btn btn-sm btn-danger">تسجيل الخروج</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary me-2">تسجيل الدخول</a>
                        <a href="register.php" class="btn btn-success">التسجيل</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="hero-section">
        <div class="container">
            <h1 class="display-3 fw-bold mb-4">اكتشف، استثمر، شارك.</h1>
            <p class="lead mb-5">
                منصة INVESTOR هي بوابتك لربط الأفكار الريادية بالتمويل والاستشارات اللازمة للنجاح.
            </p>
            
            <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                <a href="dashboard.php" class="btn btn-success cta-button">انتقل إلى لوحة التحكم</a>
            <?php else: ?>
                <a href="register.php" class="btn btn-warning cta-button me-3">ابدأ رحلتك مجاناً!</a>
                <a href="pitches_list.php" class="btn btn-outline-light cta-button">تصفح العروض الحالية</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container py-5">
        <h2 class="text-center mb-5" style="color: var(--color-primary);">لماذا تختار INVESTOR؟</h2>
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <h3 style="color: #4CAF50;">تمويل العروض</h3>
                <p class="text-muted">نشر أو تصفح عروض التمويل الموثوقة والمقيّمة من قبل المجتمع.</p>
            </div>
            <div class="col-md-4 mb-4">
                <h3 style="color: #2196F3;">مطابقة الخبراء</h3>
                <p class="text-muted">ابحث عن شركاء أو مستشارين يمتلكون المهارات الدقيقة التي تحتاجها (Team Matching).</p>
            </div>
            <div class="col-md-4 mb-4">
                <h3 style="color: #FF9800;">حل المشكلات</h3>
                <p class="text-muted">اطرح تحدياتك الإدارية أو التقنية واحصل على حلول من المستثمرين ورواد الأعمال.</p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

</body>
</html>
