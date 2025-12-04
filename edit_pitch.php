<?php
// edit_pitch.php - نموذج ومعالجة تعديل عرض تمويل موجود

require_once 'session_manager.php';
require_once 'db_config.php';

// 1. التأكد من تسجيل الدخول
require_login(); 
$user_id = $_SESSION["user_id"];

// 2. التحقق من وجود ID العرض
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("location: my_pitches.php?error=invalid_pitch_id");
    exit;
}

$pitch_id = $_GET['id'];
$pitch = null;
$pitch_err = $success_msg = "";

// 3. جلب بيانات العرض الحالية والتأكد من الملكية
$sql_fetch = "SELECT * FROM pitches WHERE pitch_id = ? AND user_id = ?";
if ($stmt_fetch = mysqli_prepare($link, $sql_fetch)) {
    mysqli_stmt_bind_param($stmt_fetch, "ii", $pitch_id, $user_id);
    mysqli_stmt_execute($stmt_fetch);
    $result_fetch = mysqli_stmt_get_result($stmt_fetch);
    
    if (mysqli_num_rows($result_fetch) == 1) {
        $pitch = mysqli_fetch_assoc($result_fetch);
    }
    mysqli_stmt_close($stmt_fetch);
}

// إذا لم يتم العثور على العرض أو كان المستخدم ليس مالكه
if (!$pitch) {
    header("location: my_pitches.php?error=unauthorized_or_not_found");
    exit;
}

// 4. معالجة تحديث البيانات عند الإرسال
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // أ. جمع البيانات الجديدة وتنقيتها
    $title = trim($_POST["title"] ?? '');
    $description = trim($_POST["description"] ?? '');
    $category = trim($_POST["category"] ?? '');
    $required_amount = filter_var($_POST["required_amount"] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $equity_offered = filter_var($_POST["equity_offered"] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $status = trim($_POST["status"] ?? 'open');
    
    // ب. التحقق من صحة الإدخالات
    if(empty($title) || empty($description) || empty($category) || $required_amount <= 0 || $equity_offered <= 0 || $equity_offered > 100){
        $pitch_err = "الرجاء ملء جميع الحقول والتأكد من أن المبالغ والنسب صحيحة.";
    }

    // ج. تحديث البيانات في قاعدة البيانات
    if(empty($pitch_err)){
        
        $sql_update = "UPDATE pitches SET title = ?, description = ?, category = ?, required_amount = ?, equity_offered = ?, status = ? WHERE pitch_id = ? AND user_id = ?";
         
        if($stmt_update = mysqli_prepare($link, $sql_update)){
            
            // الربط (sssdssii: string, string, string, double, double, string, integer, integer)
            mysqli_stmt_bind_param($stmt_update, "sssddssi", $param_title, $param_description, $param_category, $param_amount, $param_equity, $param_status, $param_pitch_id, $param_user_id);
            
            // تعيين المعاملات
            $param_title = $title;
            $param_description = $description;
            $param_category = $category;
            $param_amount = $required_amount;
            $param_equity = $equity_offered;
            $param_status = $status;
            $param_pitch_id = $pitch_id;
            $param_user_id = $user_id;
            
            if(mysqli_stmt_execute($stmt_update)){
                $success_msg = "✅ تم تحديث عرض التمويل بنجاح!";
                
                // إعادة جلب البيانات المحدثة للعرض لملء النموذج مرة أخرى
                $pitch = array_merge($pitch, compact('title', 'description', 'category', 'required_amount', 'equity_offered', 'status'));

                // توجيه المستخدم لصفحة العروض الخاصة به برسالة نجاح (Redirect-After-Post)
                header("location: my_pitches.php?status=updated");
                exit();
            } else{
                $pitch_err = "حدث خطأ في قاعدة البيانات أثناء التحديث.";
            }

            mysqli_stmt_close($stmt_update);
        }
    }
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INVESTOR | تعديل: <?php echo htmlspecialchars($pitch['title'] ?? 'عرض التمويل'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css"> 
    <style>
        .pitch-form-card {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background-color: var(--bg-card);
            border: 1px solid var(--border-dark);
            border-left: 5px solid var(--color-warning);
            border-radius: 8px;
        }
    </style>
</head>
<body>

    <header class="navbar navbar-expand-lg navbar-dark bg-dark">...</header>

    <div class="pitch-form-card">
        <h2 class="text-center mb-4" style="color: var(--color-warning);">✏️ تعديل عرض التمويل</h2>
        <p class="text-muted text-center">أنت تعدل العرض: **<?php echo htmlspecialchars($pitch['title'] ?? 'غير محدد'); ?>**</p>

        <?php 
        if(!empty($pitch_err)){
            echo '<div class="alert alert-danger text-center">' . $pitch_err . '</div>';
        } elseif(!empty($success_msg)){
            echo '<div class="alert alert-success text-center">' . $success_msg . '</div>';
        }
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $pitch_id; ?>" method="POST">
            
            <div class="mb-3">
                <label for="title" class="form-label text-muted">عنوان عرض التمويل</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($pitch['title'] ?? ''); ?>" required>
            </div>

            <div class="mb-3">
                <label for="category" class="form-label text-muted">القطاع/الفئة</label>
                <select class="form-select" id="category" name="category" required>
                    <?php 
                    $categories = ['Technology', 'Fintech', 'Healthcare', 'E-commerce', 'Real Estate', 'Other'];
                    foreach ($categories as $cat) {
                        $selected = ($pitch['category'] ?? '') === $cat ? 'selected' : '';
                        echo "<option value=\"{$cat}\" {$selected}>{$cat}</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label text-muted">الوصف التفصيلي للمشروع</label>
                <textarea class="form-control" id="description" name="description" rows="6" required><?php echo htmlspecialchars($pitch['description'] ?? ''); ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="required_amount" class="form-label text-muted">المبلغ المطلوب للتمويل ($)</label>
                    <input type="number" step="1000" min="1000" class="form-control" id="required_amount" name="required_amount" value="<?php echo htmlspecialchars($pitch['required_amount'] ?? ''); ?>" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="equity_offered" class="form-label text-muted">حصة الملكية المعروضة (%)</label>
                    <input type="number" step="0.5" min="1" max="100" class="form-control" id="equity_offered" name="equity_offered" value="<?php echo htmlspecialchars($pitch['equity_offered'] ?? ''); ?>" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="status" class="form-label text-muted">حالة العرض</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="open" <?php echo ($pitch['status'] ?? '') === 'open' ? 'selected' : ''; ?>>مفتوح للتمويل</option>
                        <option value="funded" <?php echo ($pitch['status'] ?? '') === 'funded' ? 'selected' : ''; ?>>تم تمويله</option>
                        <option value="closed" <?php echo ($pitch['status'] ?? '') === 'closed' ? 'selected' : ''; ?>>مغلق</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-warning w-100 mt-3">حفظ التعديلات</button>
            <a href="my_pitches.php" class="btn btn-outline-secondary w-100 mt-2">إلغاء والعودة</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

</body>
</html>
