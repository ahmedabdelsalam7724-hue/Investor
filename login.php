<?php
/**
 * ob_start() يمنع مشكلة "Headers already sent" التي تسبب خطأ 500.
 */
ob_start();

/**
 * تفعيل عرض الأخطاء (لأغراض التطوير والاختبار فقط!)
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// login.php - صفحة تسجيل دخول المستخدمين

require_once 'session_manager.php'; 

// **1. معلومات الاتصال بقاعدة البيانات (مدمجة هنا):**
define('DB_SERVER', 'sql105.infinityfree.com');
define('DB_USERNAME', 'if0_40574048');
define('DB_PASSWORD', 'KMYgcShxDGdmjN');
define('DB_NAME', 'if0_40574048_investor');

// **2. الاتصال بقاعدة البيانات (مدمج هنا):**
global $link;
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// التحقق من الاتصال
if($link === false){
    die("خطأ: تعذر الاتصال بقاعدة البيانات MySQL. " . mysqli_connect_error());
}
mysqli_set_charset($link, "utf8mb4");

// متغيرات لتخزين البيانات والرسائل
$email = $password = "";
$email_err = $password_err = $login_err = "";

// معالجة بيانات النموذج عند الإرسال
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // 1. التحقق من صحة البريد الإلكتروني
    if(empty(trim($_POST["email"] ?? ''))){
        $email_err = "الرجاء إدخال البريد الإلكتروني.";
    } else{
        $email = trim($_POST["email"]);
    }

    // 2. التحقق من صحة كلمة المرور
    if(empty(trim($_POST["password"] ?? ''))){
        $password_err = "الرجاء إدخال كلمة المرور.";
    } else{
        $password = trim($_POST["password"]);
    }

    // 3. التحقق من صحة المدخلات قبل محاولة تسجيل الدخول
    if(empty($email_err) && empty($password_err)){
        
        // تهيئة استعلام لجلب كلمة المرور المشفرة وبيانات المستخدم
        $sql = "SELECT user_id, password, full_name, user_role FROM users WHERE email = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = $email;
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){                    
                    mysqli_stmt_bind_result($stmt, $user_id, $hashed_password, $full_name, $user_role);
                    
                    if(mysqli_stmt_fetch($stmt)){
                        
                        if(password_verify($password, $hashed_password)){
                            
                            // كلمة المرور صحيحة
                            
                            // تخزين البيانات في متغيرات الجلسة
                            $_SESSION["loggedin"] = true;
                            $_SESSION["user_id"] = $user_id;
                            $_SESSION["full_name"] = $full_name;
                            $_SESSION["user_role"] = $user_role;
                            
                            // التوجيه إلى لوحة التحكم
                            header("location: dashboard.php");
                            exit;
                        } else{
                            $login_err = "البريد الإلكتروني أو كلمة المرور غير صحيحة.";
                        }
                    }
                } else{
                    $login_err = "البريد الإلكتروني أو كلمة المرور غير صحيحة.";
                }
            } else{
                $login_err = "حدث خطأ غير متوقع. يرجى مراجعة سجلات الخادم (Logs)."; 
            }

            mysqli_stmt_close($stmt);
        }
    }
    
    // إغلاق آمن للاتصال
    if (isset($link) && $link) {
        mysqli_close($link);
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INVESTOR | تسجيل الدخول</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css"> 
    <style>
        .login-form-container {
            max-width: 450px;
            margin: 100px auto;
            padding: 40px;
            border-left: 5px solid var(--color-primary);
            border-radius: 8px;
        }
    </style>
</head>
<body>

    <header class="navbar navbar-expand-lg navbar-dark bg-dark">...</header>

    <div class="login-form-container bg-dark text-white">
        <h2 class="text-center mb-4" style="color: var(--color-primary);">تسجيل الدخول 🚀</h2>
        
        <?php 
        // رسائل حالات التسجيل الناجح
        if(isset($_GET['status']) && $_GET['status'] == 'registered'){
            echo '<div class="alert alert-success text-center">✅ تم إنشاء حسابك بنجاح. يرجى تسجيل الدخول.</div>';
        }

        // رسالة الخطأ العامة
        if(!empty($login_err)){
            echo '<div class="alert alert-danger text-center">' . $login_err . '</div>';
        } 
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            
            <div class="mb-3">
                <label for="email" class="form-label text-muted">البريد الإلكتروني</label>
                <input type="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                <div class="invalid-feedback"><?php echo $email_err; ?></div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label text-muted">كلمة المرور</label>
                <input type="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" id="password" name="password" required>
                <div class="invalid-feedback"><?php echo $password_err; ?></div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-3">تسجيل الدخول</button>
            <p class="text-center mt-3 text-muted">
                ليس لديك حساب؟ <a href="register.php" style="color: var(--color-info);">أنشئ حساباً جديداً</a>
            </p>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

</body>
</html>
