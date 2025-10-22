<?php
require_once 'config.php';

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
    
    if (empty($username) || empty($password)) {
        $error = 'جميع الحقول مطلوبة';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            
            if ($user['user_type'] === 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: index.php");
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
    <title>تسجيل الدخول - نظام الإدارة</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="auth-container" style="
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2rem;
        position: relative;
        overflow: hidden;
    ">
        <!-- خلفية متحركة -->
        <div class="background-animation">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>

        <div class="auth-card" style="
            width: 100%;
            max-width: 440px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius-xl);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            animation: fadeInUp 0.8s ease-out;
            border: 1px solid rgba(255, 255, 255, 0.3);
        ">
            <!-- رأس البطاقة -->
            <div class="auth-header" style="
                background: var(--gradient-primary);
                color: white;
                padding: 2.5rem 2rem;
                text-align: center;
                position: relative;
                overflow: hidden;
            ">
                <div class="header-background">
                    <div class="circle circle-1"></div>
                    <div class="circle circle-2"></div>
                </div>
                <div style="position: relative; z-index: 2;">
                    <div style="
                        width: 80px;
                        height: 80px;
                        background: rgba(255, 255, 255, 0.2);
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin: 0 auto 1rem;
                        font-size: 2rem;
                    ">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h1 style="margin: 0 0 0.5rem 0; font-size: 1.8rem; font-weight: 700;">مرحباً بعودتك</h1>
                    <p style="margin: 0; opacity: 0.9; font-size: 1rem;">سجل الدخول إلى حسابك للمتابعة</p>
                </div>
            </div>

            <!-- جسم البطاقة -->
            <div class="auth-body" style="padding: 2.5rem 2rem;">
                <?php if ($error): ?>
                    <div class="alert alert-error" style="margin-bottom: 1.5rem;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-exclamation-circle"></i>
                            <span><?php echo $error; ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success" style="margin-bottom: 1.5rem;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-check-circle"></i>
                            <span><?php echo $success; ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" id="loginForm">
                    <div class="form-group">
                        <label for="username" style="color: var(--gray-700);">
                            <i class="fas fa-user" style="color: var(--primary-color);"></i>
                            اسم المستخدم أو البريد الإلكتروني
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="username" 
                               name="username" 
                               placeholder="أدخل اسم المستخدم أو البريد الإلكتروني"
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                               required
                               style="padding: 1rem 1.25rem; font-size: 1rem;">
                    </div>
                    
                    <div class="form-group">
                        <label for="password" style="color: var(--gray-700);">
                            <i class="fas fa-lock" style="color: var(--primary-color);"></i>
                            كلمة المرور
                        </label>
                        <div style="position: relative;">
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   placeholder="أدخل كلمة المرور"
                                   required
                                   style="padding: 1rem 1.25rem; font-size: 1rem; padding-left: 3rem;">
                            <button type="button" 
                                    class="password-toggle" 
                                    onclick="togglePassword('password')"
                                    style="
                                        position: absolute;
                                        left: 15px;
                                        top: 50%;
                                        transform: translateY(-50%);
                                        background: none;
                                        border: none;
                                        color: var(--gray-500);
                                        cursor: pointer;
                                        padding: 0.5rem;
                                    ">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group" style="display: flex; justify-content: space-between; align-items: center; margin: 1.5rem 0;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" name="remember" style="margin: 0;">
                            <span style="color: var(--gray-600); font-size: 0.9rem;">تذكرني</span>
                        </label>
                        <a href="forgot-password.php" style="color: var(--primary-color); text-decoration: none; font-size: 0.9rem;">
                            نسيت كلمة المرور؟
                        </a>
                    </div>
                    
                    <button type="submit" 
                            class="btn btn-primary btn-block"
                            style="padding: 1rem; font-size: 1.1rem; font-weight: 600;">
                        <i class="fas fa-sign-in-alt"></i>
                        تسجيل الدخول
                    </button>
                </form>

                <div class="auth-footer" style="
                    text-align: center;
                    margin-top: 2rem;
                    padding-top: 2rem;
                    border-top: 1px solid var(--gray-200);
                ">
                    <p style="color: var(--gray-600); margin-bottom: 1rem;">
                        ليس لديك حساب؟ 
                        <a href="register.php" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">
                            إنشاء حساب جديد
                        </a>
                    </p>
                    
                    <!-- حسابات تجريبية -->
                    <div style="
                        background: var(--gray-50);
                        padding: 1rem;
                        border-radius: var(--border-radius);
                        margin-top: 1rem;
                    ">
                        <h5 style="margin: 0 0 0.5rem 0; color: var(--gray-700); font-size: 0.9rem;">
                            <i class="fas fa-info-circle" style="color: var(--info-color);"></i>
                            حسابات تجريبية:
                        </h5>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.8rem;">
                            <div style="text-align: right;">
                                <strong>الأدمن:</strong><br>
                                <span style="color: var(--gray-600);">admin / admin123</span>
                            </div>
                            <div style="text-align: right;">
                                <strong>العميل:</strong><br>
                                <span style="color: var(--gray-600);">user1 / user123</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .background-animation {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            right: 0;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }

        .shape-1 {
            width: 200px;
            height: 200px;
            top: -50px;
            right: -50px;
            animation: float 6s ease-in-out infinite;
        }

        .shape-2 {
            width: 150px;
            height: 150px;
            bottom: 100px;
            left: -50px;
            animation: float 8s ease-in-out infinite;
        }

        .shape-3 {
            width: 100px;
            height: 100px;
            top: 50%;
            left: 10%;
            animation: float 10s ease-in-out infinite;
        }

        .header-background {
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
        }

        .circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }

        .circle-1 {
            width: 100px;
            height: 100px;
            top: 20px;
            left: 20px;
        }

        .circle-2 {
            width: 150px;
            height: 150px;
            bottom: -50px;
            right: -50px;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }
    </style>

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

        // تأثيرات عند التحميل
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const inputs = form.querySelectorAll('.form-control');
            
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
    </script>
</body>
</html>