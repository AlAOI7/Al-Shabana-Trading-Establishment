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
    die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// دالة التحقق من تسجيل الدخول
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// دالة التحقق إذا كان المستخدم أدمن
function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

// إنشاء الجدول والمستخدمين الافتراضيين
function initializeDatabase() {
    global $pdo;
    
    // إنشاء جدول المستخدمين إذا لم يكن موجوداً
    $create_table_sql = "
    CREATE TABLE IF NOT EXISTS `users` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `username` varchar(50) NOT NULL,
        `email` varchar(100) NOT NULL,
        `password` varchar(255) NOT NULL,
        `user_type` enum('admin','client') DEFAULT 'client',
        `full_name` varchar(100) DEFAULT NULL,
        `phone` varchar(20) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `last_login` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `username` (`username`),
        UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ";
    
    $pdo->exec($create_table_sql);
    
    // إضافة المستخدمين الافتراضيين
    $default_users = [
        [
            'username' => 'alaoi',
            'email' => 'alaoi@company.com',
            'password' => 'alaoi123',
            'user_type' => 'admin',
            'full_name' => 'Alaoi Administrator'
        ],
        [
            'username' => 'user1', 
            'email' => 'user1@example.com',
            'password' => 'user123',
            'user_type' => 'client',
            'full_name' => 'أحمد محمد'
        ]
    ];
    
    foreach ($default_users as $user) {
        $check_sql = "SELECT id FROM users WHERE username = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$user['username']]);
        
        if (!$check_stmt->fetch()) {
            $hashed_password = password_hash($user['password'], PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, password, user_type, full_name) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $user['username'],
                $user['email'],
                $hashed_password,
                $user['user_type'],
                $user['full_name']
            ]);
        }
    }
}

// استدعاء الدالة لتهيئة قاعدة البيانات
initializeDatabase();
?>