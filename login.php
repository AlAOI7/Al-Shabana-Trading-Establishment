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
        try {
            // البحث عن المستخدم في قاعدة البيانات
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // التحقق من كلمة السر
                if (password_verify($password, $user['password'])) {
                    // تسجيل بيانات المستخدم في الجلسة
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_type'] = $user['user_type'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['email'] = $user['email'];
                    
                    // تحديث وقت آخر دخول
                    $update_stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $update_stmt->execute([$user['id']]);
                    
                    // توجيه المستخدم حسب نوعه
                    if ($user['user_type'] === 'admin') {
                        header("Location: admin/dashboard.php");
                    } else {
                        header("Location: client/dashboard.php");
                    }
                    exit();
                } else {
                    $error = 'كلمة المرور غير صحيحة';
                }
            } else {
                $error = 'اسم المستخدم غير موجود';
            }
        } catch (PDOException $e) {
            $error = 'خطأ في النظام: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام الشعبانات - تسجيل الدخول</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --error-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --text-color: #333;
            --border-color: #ddd;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            line-height: 1.6;
        }

        .auth-container {
            width: 100%;
            max-width: 450px;
        }

        .auth-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            animation: fadeIn 0.6s ease-out;
        }

        .auth-header {
            background: linear(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }

        .auth-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: cover;
        }

        .auth-header h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
        }

        .auth-header p {
            opacity: 0.9;
            font-size: 0.95rem;
            position: relative;
        }

        .auth-body {
            padding: 2.5rem;
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.95rem;
            border: 1px solid;
            animation: slideDown 0.3s ease;
        }

        .alert-error {
            background: #fee;
            color: var(--error-color);
            border-color: #f5c6cb;
        }

        .alert-success {
            background: #efffed;
            color: var(--success-color);
            border-color: #c3e6cb;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.8rem;
            font-weight: 600;
            color: var(--dark-color);
            font-size: 0.95rem;
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-group-icon {
            position: absolute;
            right: 15px;
            color: #666;
            z-index: 2;
        }

        .form-control {
            width: 100%;
            padding: 15px 50px 15px 15px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            transform: translateY(-2px);
        }

        .password-toggle {
            position: absolute;
            left: 15px;
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 5px;
            z-index: 2;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 15px 25px;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            width: 100%;
            font-family: inherit;
        }

        .btn-primary {
            background: linear(135deg, var(--secondary-color), #2980b9);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.3);
        }

        .btn-primary:active {
            transform: translateY(-1px);
        }

        .auth-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        .auth-footer p {
            margin-bottom: 1rem;
            color: #666;
        }

        .auth-footer a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .auth-footer a:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }

        .demo-accounts {
            text-align: center;
            margin-top: 2rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px solid var(--border-color);
        }

        .demo-accounts h4 {
            margin-bottom: 1rem;
            color: var(--dark-color);
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .account-list {
            display: grid;
            gap: 1rem;
            font-size: 0.9rem;
        }

        .account-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            transition: transform 0.2s ease;
        }

        .account-item:hover {
            transform: translateX(-5px);
        }

        .account-type {
            font-weight: 600;
            color: var(--dark-color);
        }

        .account-credentials {
            color: #666;
            direction: ltr;
            font-family: monospace;
            background: #f8f9fa;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
        }

        /* التجاوب مع الشاشات الصغيرة */
        @media (max-width: 480px) {
            .auth-container {
                max-width: 100%;
            }
            
            .auth-body {
                padding: 2rem 1.5rem;
            }
            
            .auth-header {
                padding: 1.5rem;
            }
            
            .auth-header h1 {
                font-size: 1.5rem;
            }
        }

        /* تأثيرات الحركة */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* حالة التحميل */
        .btn-loading {
            position: relative;
            color: transparent;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* تحسينات إضافية */
        .logo {
            text-align: center;
            margin-bottom: 1rem;
        }

        .logo-text {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary-color);
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo">
                    <div class="logo-text">نظام الشعبانات</div>
                </div>
                <h1><i class="fas fa-sign-in-alt"></i> تسجيل الدخول</h1>
                <p>مرحباً بعودتك! يرجى تسجيل الدخول إلى حسابك</p>
            </div>
            
            <div class="auth-body">
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> 
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> 
                        <span><?php echo $success; ?></span>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="loginForm">
                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user"></i> اسم المستخدم أو البريد الإلكتروني
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="username" name="username" 
                                   placeholder="أدخل اسم المستخدم أو البريد الإلكتروني" 
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                   required>
                            <div class="input-group-icon">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> كلمة المرور
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="أدخل كلمة المرور" required>
                            <div class="input-group-icon">
                                <i class="fas fa-lock"></i>
                            </div>
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" id="loginBtn">
                            <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
                        </button>
                    </div>
                </form>
                
                <div class="auth-footer">
                    <p>ليس لديك حساب؟ <a href="register.php">إنشاء حساب جديد</a></p>
                    <p><a href="forgot-password.php">نسيت كلمة المرور؟</a></p>
                </div>
                
                <div class="demo-accounts">
                    <h4><i class="fas fa-info-circle"></i> حسابات تجريبية</h4>
                    <div class="account-list">
                        <div class="account-item">
                            <span class="account-type">مدير النظام:</span>
                            <span class="account-credentials">alaoi / alaoi123</span>
                        </div>
                        <div class="account-item">
                            <span class="account-type">عميل:</span>
                            <span class="account-credentials">user1 / user123</span>
                        </div>
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
                icon.style.color = '#3498db';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
                icon.style.color = '#666';
            }
        }
        
        // إضافة تأثيرات عند التحميل
        document.addEventListener('DOMContentLoaded', function() {
            const authCard = document.querySelector('.auth-card');
            authCard.style.opacity = '0';
            authCard.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                authCard.style.transition = 'all 0.6s ease';
                authCard.style.opacity = '1';
                authCard.style.transform = 'translateY(0)';
            }, 100);
            
            // إضافة تأثير التحميل عند الإرسال
            const loginForm = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            
            loginForm.addEventListener('submit', function() {
                loginBtn.classList.add('btn-loading');
                loginBtn.disabled = true;
                loginBtn.innerHTML = '<i class="fas fa-spinner"></i> جاري التسجيل...';
            });

            // تأثيرات عند التركيز على الحقول
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                input.addEventListener('blur', function() {
                    if (!this.value) {
                        this.parentElement.classList.remove('focused');
                    }
                });
            });
        });

        // تأثيرات كتابة كلمة السر
        document.getElementById('password').addEventListener('input', function() {
            const strength = this.value.length;
            const strengthMeter = document.getElementById('password-strength');
            if (strengthMeter) {
                if (strength < 4) {
                    strengthMeter.style.width = '25%';
                    strengthMeter.style.background = '#e74c3c';
                } else if (strength < 8) {
                    strengthMeter.style.width = '50%';
                    strengthMeter.style.background = '#f39c12';
                } else {
                    strengthMeter.style.width = '100%';
                    strengthMeter.style.background = '#27ae60';
                }
            }
        });
    </script>
</body>
</html>