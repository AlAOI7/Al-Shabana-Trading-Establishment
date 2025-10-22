<?php
require_once 'config.php';

// إذا كان المستخدم مسجل دخول بالفعل، نوجهه للصفحة المناسبة
if (isLoggedIn()) {
    if (isAdmin()) {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: client/dashboard.php");
    }
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // التحقق من البيانات المدخلة
    if (empty($username) || empty($password)) {
        $error = 'جميع الحقول مطلوبة';
    } else {
        // البحث عن المستخدم في قاعدة البيانات
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // تسجيل بيانات المستخدم في الجلسة
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            
            // توجيه المستخدم حسب نوعه
            if ($user['user_type'] === 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: index.php"); // الصفحة الرئيسية للعميل
            }
            exit();
        } else {
            $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول</title>
    <link rel="stylesheet" href="auth-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1><i class="fas fa-sign-in-alt"></i> تسجيل الدخول</h1>
                <p>مرحباً بعودتك! يرجى تسجيل الدخول إلى حسابك</p>
            </div>
            
            <div class="auth-body">
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="loginForm">
                    <div class="form-group with-icon">
                        <i class="fas fa-user"></i>
                        <label for="username">اسم المستخدم أو البريد الإلكتروني</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="أدخل اسم المستخدم أو البريد الإلكتروني" 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group with-icon">
                        <i class="fas fa-lock"></i>
                        <label for="password">كلمة المرور</label>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="أدخل كلمة المرور" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
                        </button>
                    </div>
                </form>
                
                <div class="auth-footer">
                    <p>ليس لديك حساب؟ <a href="register.php">إنشاء حساب جديد</a></p>
                    <p><a href="forgot-password.php" style="font-size: 0.9rem;">نسيت كلمة المرور؟</a></p>
                </div>
                
                <div style="text-align: center; margin-top: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 10px;">
                    <h4 style="margin-bottom: 0.5rem; color: var(--dark-color);">حسابات تجريبية:</h4>
                    <div style="font-size: 0.8rem; color: #6c757d;">
                        <div><strong>الأدمن:</strong> admin / admin123</div>
                        <div><strong>العميل:</strong> user1 / user123</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.parentNode.querySelector('.password-toggle i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }
        
        // إضافة تأثير عند التحميل
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.auth-card').style.opacity = '0';
            document.querySelector('.auth-card').style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                document.querySelector('.auth-card').style.transition = 'all 0.5s ease';
                document.querySelector('.auth-card').style.opacity = '1';
                document.querySelector('.auth-card').style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>