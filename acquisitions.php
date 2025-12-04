<?php
// تفعيل عرض الأخطاء (مؤقت للاختبار)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// acquisitions.php - عرض قائمة عروض الاستحواذ والفرص

require_once 'session_manager.php';
require_once 'db_config.php';

require_login(); 

$user_id = $_SESSION["user_id"];
$acquisitions = [];
$error_message = "";

$sql = "SELECT 
            a.acquisition_id, 
            a.company_name, 
            a.industry, 
            a.valuation, 
            a.equity_offered, 
            a.reason, 
            a.status,
            a.created_at,
            u.full_name AS entrepreneur_name,
            u.user_id AS entrepreneur_id
        FROM 
            acquisitions a  
        JOIN 
            users u ON a.user_id = u.user_id 
        WHERE 
            a.status = 'available'
        ORDER BY 
            a.created_at DESC";

if ($result = mysqli_query($link, $sql)) {
    while ($row = mysqli_fetch_assoc($result)) {
        
        // استخدام substr الأبسط
        $row['short_reason'] = substr(strip_tags($row['reason']), 0, 150) . (strlen($row['reason']) > 150 ? '...' : '');
        
        $acquisitions[] = $row;
    }
    mysqli_free_result($result);
} else {
    $error_message = "خطأ في قاعدة البيانات أثناء جلب العروض: " . mysqli_error($link);
}

// إغلاق آمن للاتصال
if (isset($link) && $link) {
    mysqli_close($link);
}

function format_currency($value) {
    return number_format($value, 0) . ' USD'; 
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INVESTOR | عروض الاستحواذ والفرص</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css"> 
    <style>
        .acquisition-card {
            border-left: 5px solid var(--color-danger); 
            transition: transform 0.3s;
        }
        .acquisition-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.2); 
        }
    </style>
</head>
<body>

    <header class="navbar navbar-expand-lg navbar-dark bg-dark">...</header>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 style="color: var(--color-danger);">🎯 عروض الاستحواذ والفرص</h1>
            <?php if ($_SESSION['user_role'] === 'entrepreneur'): ?>
            <a href="submit_acquisition.php" class="btn btn-danger">
                ➕ اعرض فرصة استحواذ
            </a>
            <?php endif; ?>
        </div>
        <p class="lead text-muted mb-5">
            استعرض الشركات والمشاريع المتاحة للاستحواذ أو الشراكة الاستراتيجية.
        </p>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger text-center"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if (!empty($acquisitions)): ?>
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <?php foreach ($acquisitions as $offer): ?>
                <div class="col">
                    <div class="card h-100 p-4 acquisition-card">
                        <div class="card-body d-flex flex-column">
                            <h4 class="card-title mb-2" style="color: var(--color-danger);">
                                <?php echo htmlspecialchars($offer['company_name']); ?>
                            </h4>
                            <p class="card-subtitle mb-3 text-muted small">
                                مقدم من: <span class="fw-bold text-success"><?php echo htmlspecialchars($offer['entrepreneur_name']); ?></span>
                            </p>
                            
                            <hr class="my-2 text-secondary">

                            <p class="card-text text-white">
                                **التقييم المطلوب:** <span class="fw-bold text-warning"><?php echo format_currency($offer['valuation']); ?></span>
                            </p>
                            <p class="card-text text-white">
                                **نسبة الأسهم المعروضة:** <span class="fw-bold text-warning"><?php echo htmlspecialchars($offer['equity_offered']); ?>%</span>
                            </p>
                            
                            <p class="card-text text-muted mt-3 flex-grow-1">
                                **الدافع:** <?php echo htmlspecialchars($offer['short_reason']); ?>
                            </p>
                            
                            <div class="mt-3">
                                <span class="badge bg-secondary me-3">
                                    القطاع: <?php echo htmlspecialchars($offer['industry']); ?>
                                </span>
                            </div>

                            <a href="acquisition_details.php?id=<?php echo $offer['acquisition_id']; ?>" class="btn btn-outline-danger mt-3 mt-auto">
                                عرض التفاصيل والتقديم
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center mt-5" role="alert">
                لا توجد عروض استحواذ متاحة حالياً.
            </div>
        <?php endif; ?>

    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

</body>
</html>
