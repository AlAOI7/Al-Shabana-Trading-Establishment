<?php
session_start();

// إعدادات الاتصال بقاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'alshabanat');

// إنشاء الاتصال
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("set names utf8");
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}


// إعدادات المسارات - تأكد من المسار الصحيح
define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/Al-Shabana-Trading-Establishment/uploads/products/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// دالة التحقق من تسجيل الدخول
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// دالة التحقق إذا كان المستخدم أدمن
function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

// دالة لمنع الوصول بدون صلاحية
function requireAdmin() {
    if (!isLoggedIn() || !isAdmin()) {
        header("Location: ../login.php");
        exit();
    }
}

// دالة لمنع الوصول للعملاء
function requireClient() {
    if (!isLoggedIn() || isAdmin()) {
        header("Location: ../login.php");
        exit();
    }
}
?>