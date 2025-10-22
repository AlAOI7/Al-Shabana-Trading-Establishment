<?php
require_once 'config.php';

// بيانات المستخدمين للإضافة
$users = [
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

foreach ($users as $user) {
    // التحقق إذا كان المستخدم موجوداً
    $check_sql = "SELECT id FROM users WHERE username = ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$user['username']]);
    
    if (!$check_stmt->fetch()) {
        // تشفير كلمة السر
        $hashed_password = password_hash($user['password'], PASSWORD_DEFAULT);
        
        // إضافة المستخدم
        $sql = "INSERT INTO users (username, email, password, user_type, full_name) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([
            $user['username'],
            $user['email'],
            $hashed_password,
            $user['user_type'],
            $user['full_name']
        ])) {
            echo "تم إضافة المستخدم: " . $user['username'] . " - كلمة السر: " . $user['password'] . "<br>";
        } else {
            echo "خطأ في إضافة المستخدم: " . $user['username'] . "<br>";
        }
    } else {
        echo "المستخدم موجود مسبقاً: " . $user['username'] . "<br>";
    }
}

echo "<h3>تم الانتهاء من إضافة المستخدمين</h3>";
?>