<?php
/*
 * db_config.php
 * ملف اتصال قاعدة البيانات MySQL باستخدام mysqli.
 */

// إعلان $link كمتغير عام هنا أيضاً، لضمان توافقه مع الملفات الأخرى
global $link;

// بيانات الاتصال بقاعدة البيانات
define('DB_SERVER', 'sql105.infinityfree.com');
define('DB_USERNAME', 'if0_40574048');
define('DB_PASSWORD', 'KMYgcShxDGdmjN');
define('DB_NAME', 'if0_40574048_investor');

/* محاولة إنشاء الاتصال بقاعدة البيانات */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// التحقق من الاتصال
if($link === false){
    die("خطأ: تعذر الاتصال بقاعدة البيانات MySQL. " . mysqli_connect_error());
}

/* إعداد الترميز لدعم العربية */
mysqli_set_charset($link, "utf8mb4");

// هنا ينتهي الملف تماماً.
