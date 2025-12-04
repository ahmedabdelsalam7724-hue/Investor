<?php
// conversation.php - عرض محادثة فردية محددة وإرسال ردود

require_once 'session_manager.php';
require_once 'db_config.php';
require_once 'notification_helper.php'; // لإنشاء إشعار للرد

require_login(); 

$user_id = $_SESSION["user_id"];
$partner_id = null;
$partner_name = "مستخدم غير معروف";
$messages = [];
$error_message = "";

// 1. التحقق من وجود ID الشريك في الرابط (GET)
if (isset($_GET['partner_id']) && is_numeric($_GET['partner_id'])) {
    $partner_id = filter_var($_GET['partner_id'], FILTER_SANITIZE_NUMBER_INT);
}

if (!$partner_id || $partner_id == $user_id) {
    header("location: messaging.php?error=invalid_partner");
    exit;
}

// 2. جلب اسم الشريك
$sql_partner = "SELECT full_name FROM users WHERE user_id = ?";
if ($stmt_partner = mysqli_prepare($link, $sql_partner)) {
    mysqli_stmt_bind_param($stmt_partner, "i", $partner_id);
    mysqli_stmt_execute($stmt_partner);
    mysqli_stmt_bind_result($stmt_partner, $name);
    if (mysqli_stmt_fetch($stmt_partner)) {
        $partner_name = $name;
    }
    mysqli_stmt_close($stmt_partner);
}

// 3. معالجة إرسال الرد (POST)
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    $body = trim($_POST["body"] ?? '');
    
    if (empty($body)) {
        $error_message = "الرجاء كتابة محتوى الرسالة قبل الإرسال.";
    } else {
        // إدراج الرسالة الجديدة
        $sql_insert = "INSERT INTO messages (sender_id, receiver_id, body, status) VALUES (?, ?, ?, 'unread')";
        
        if($stmt_insert = mysqli_prepare($link, $sql_insert)){
            mysqli_stmt_bind_param($stmt_insert, "iis", $user_id, $partner_id, $body);
            
            if(mysqli_stmt_execute($stmt_insert)){
                
                // إنشاء إشعار للشريك
                $notification_content = "لديك رد جديد من {$_SESSION['full_name']} في المحادثة.";
                $target_url = "conversation.php?partner_id={$user_id}"; 
                create_notification($partner_id, 'new_reply', $notification_content, $target_url, $link);

                // إعادة توجيه المستخدم لنفس الصفحة لتجنب إرسال النموذج مرة أخرى (Post/Redirect/Get)
                header("location: conversation.php?partner_id={$partner_id}&status=sent");
                exit();
            } else {
                $error_message = "حدث خطأ في قاعدة البيانات أثناء الإرسال.";
            }
            mysqli_stmt_close($stmt_insert);
        }
    }
}

// 4. جلب جميع الرسائل في المحادثة
$sql_messages = "SELECT 
                    message_id, 
                    sender_id, 
                    body, 
                    created_at 
                 FROM 
                    messages
                 WHERE 
                    (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
                 ORDER BY 
                    created_at ASC"; // ASC لعرض الرسائل بترتيب زمني من الأقدم للأحدث

if ($stmt_msg = mysqli_prepare($link, $sql_messages)) {
    mysqli_stmt_bind_param($stmt_msg, "iiii", $user_id, $partner_id, $partner_id, $user_id);
    mysqli_stmt_execute($stmt_msg);
    $result_msg = mysqli_stmt_get_result($stmt_msg);
    
    if ($result_msg) {
        while ($row = mysqli_fetch_assoc($result_msg)) {
            $messages[] = $row;
        }
    }
    mysqli_stmt_close($stmt_msg);
}

// 5. تحديث حالة الرسائل الواردة إلى "مقروءة"
$sql_update_status = "UPDATE messages SET status = 'read' WHERE sender_id = ? AND receiver_id = ? AND status = 'unread'";
if ($stmt_update = mysqli_prepare($link, $sql_update_status)) {
    mysqli_stmt_bind_param($stmt_update, "ii", $partner_id, $user_id); // الرسائل التي أرسلها الشريك واستقبلتها أنت
    mysqli_stmt_execute($stmt_update);
    mysqli_stmt_close($stmt_update);
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INVESTOR | محادثة مع <?php echo htmlspecialchars($partner_name); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css"> 
    <style>
        .conversation-container {
            max-width: 800px;
            margin: 50px auto;
        }
        .messages-box {
            height: 50vh; /* ارتفاع ثابت لعرض الرسائل */
            overflow-y: auto; /* تمكين التمرير */
            border: 1px solid var(--border-dark);
            padding: 15px;
            border-radius: 8px;
            background-color: var(--bg-card-darker);
        }
        .message {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 15px;
            max-width: 80%;
            word-wrap: break-word;
        }
        .sent {
            background-color: var(--color-info);
            color: white;
            margin-right: auto; /* لترتيبها على اليمين في النص العربي */
            border-bottom-left-radius: 0;
        }
        .received {
            background-color: var(--color-secondary);
            color: white;
            margin-left: auto; /* لترتيبها على اليسار في النص العربي */
            border-bottom-right-radius: 0;
        }
        .message-time {
            display: block;
            text-align: right;
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.5);
        }
    </style>
</head>
<body>

    <header class="navbar navbar-expand-lg navbar-dark bg-dark">...</header>

    <div class="container py-5">
        <div class="conversation-container">
            <h1 class="text-center mb-4" style="color: var(--color-info);">
                💬 محادثة مع: <span class="fw-bold text-warning"><?php echo htmlspecialchars($partner_name); ?></span>
            </h1>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger text-center"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['status']) && $_GET['status'] == 'sent'): ?>
                <div class="alert alert-success text-center">✅ تم إرسال ردك بنجاح.</div>
            <?php endif; ?>

            <div class="messages-box mb-4" id="messagesBox">
                <?php if (!empty($messages)): ?>
                    <?php foreach ($messages as $msg): 
                        $is_sent = ($msg['sender_id'] == $user_id);
                        $class = $is_sent ? 'sent' : 'received';
                        $alignment = $is_sent ? 'text-end' : 'text-start';
                        $sender_name = $is_sent ? 'أنت' : htmlspecialchars($partner_name);
                    ?>
                    <div class="d-flex <?php echo $is_sent ? 'justify-content-end' : 'justify-content-start'; ?>">
                        <div class="message <?php echo $class; ?>">
                            <span class="fw-bold small d-block mb-1 <?php echo $is_sent ? 'text-white' : 'text-info'; ?>">
                                <?php echo $sender_name; ?>
                            </span>
                            <?php echo nl2br(htmlspecialchars($msg['body'])); ?>
                            <span class="message-time"><?php echo date('H:i', strtotime($msg['created_at'])); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted mt-5">لم تبدأ هذه المحادثة بعد. أرسل رسالتك الأولى!</p>
                <?php endif; ?>
            </div>

            <div class="card p-3 bg-dark border-info">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?partner_id=' . $partner_id; ?>" method="POST">
                    <div class="input-group">
                        <textarea class="form-control" name="body" rows="2" placeholder="اكتب ردك هنا..." required></textarea>
                        <button type="submit" class="btn btn-info px-4">إرسال</button>
                    </div>
                </form>
            </div>
            
            <div class="text-center mt-3">
                 <a href="messaging.php" class="btn btn-outline-secondary">العودة إلى صندوق الرسائل</a>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var messagesBox = document.getElementById("messagesBox");
            // التمرير لأسفل عند تحميل الصفحة
            messagesBox.scrollTop = messagesBox.scrollHeight;
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

</body>
</html>
