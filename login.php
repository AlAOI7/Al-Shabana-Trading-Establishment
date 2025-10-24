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
    <title>نظام الشبانات - تسجيل الدخول</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color: #f093fb;
            --success-color: #4ecdc4;
            --warning-color: #ffd166;
            --error-color: #ef476f;
            --light-color: #f8f9fa;
            --dark-color: #2b2d42;
            --text-color: #333;
            --border-color: #e9ecef;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --gradient: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            line-height: 1.6;
            animation: backgroundShift 15s ease infinite;
            background-size: 200% 200%;
        }

        @keyframes backgroundShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .auth-container {
            width: 100%;
            max-width: 480px;
            position: relative;
            z-index: 2;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: var(--shadow);
            overflow: hidden;
            animation: cardAppear 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transform-style: preserve-3d;
            perspective: 1000px;
        }

        @keyframes cardAppear {
            from {
                opacity: 0;
                transform: translateY(50px) rotateX(10deg);
            }
            to {
                opacity: 1;
                transform: translateY(0) rotateX(0);
            }
        }

        .auth-header {
            background: var(--gradient);
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
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
            animation: wave 10s linear infinite;
        }

        @keyframes wave {
            0% { transform: translateX(0); }
            100% { transform: translateX(-10px); }
        }

        .logo {
            text-align: center;
            margin-bottom: 1.5rem;
            animation: logoFloat 3s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        .logo-text {
            font-size: 1.8rem;
            font-weight: bold;
            color: white;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            display: inline-block;
            background: linear-gradient(45deg, #fff, #f0f0f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .auth-header h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            position: relative;
        }

        .auth-header p {
            opacity: 0.9;
            font-size: 1rem;
            position: relative;
        }

        .auth-body {
            padding: 2.5rem;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.95rem;
            border: 1px solid;
            animation: slideDown 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .alert::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
        }

        .alert-error {
            background: rgba(239, 71, 111, 0.1);
            color: var(--error-color);
            border-color: rgba(239, 71, 111, 0.2);
        }

        .alert-error::before {
            background: var(--error-color);
        }

        .alert-success {
            background: rgba(78, 205, 196, 0.1);
            color: var(--success-color);
            border-color: rgba(78, 205, 196, 0.2);
        }

        .alert-success::before {
            background: var(--success-color);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .form-group {
            margin-bottom: 1.8rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.8rem;
            font-weight: 600;
            color: var(--dark-color);
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-group-icon {
            position: absolute;
            right: 18px;
            color: #666;
            z-index: 2;
            transition: all 0.3s ease;
        }

        .form-control {
            width: 100%;
            padding: 16px 55px 16px 50px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            background: white;
            font-family: inherit;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
            transform: translateY(-3px);
        }

        .form-control:focus + .input-group-icon {
            color: var(--primary-color);
            transform: scale(1.1);
        }

        .password-toggle {
            position: absolute;
            left: 18px;
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 5px;
            z-index: 2;
            transition: all 0.3s ease;
        }

        .password-toggle:hover {
            color: var(--primary-color);
            transform: scale(1.1);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 16px 28px;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-decoration: none;
            width: 100%;
            font-family: inherit;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.7s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: var(--gradient);
            color: white;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.5);
        }

        .btn-primary:active {
            transform: translateY(-2px);
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
            transition: color 0.3s ease;
        }

        .auth-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }

        .auth-footer a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            right: 0;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: width 0.3s ease;
        }

        .auth-footer a:hover {
            color: var(--secondary-color);
        }

        .auth-footer a:hover::after {
            width: 100%;
        }

        .demo-accounts {
            text-align: center;
            margin-top: 2.5rem;
            padding: 1.8rem;
            background: rgba(248, 249, 250, 0.7);
            border-radius: 15px;
            border: 1px solid var(--border-color);
            backdrop-filter: blur(5px);
            animation: fadeInUp 0.6s ease 0.3s both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .demo-accounts h4 {
            margin-bottom: 1.2rem;
            color: var(--dark-color);
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .account-list {
            display: grid;
            gap: 1.2rem;
            font-size: 0.9rem;
        }

        .account-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: white;
            border-radius: 10px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .account-item:hover {
            transform: translateX(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-color: var(--primary-color);
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
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            border: 1px solid #e9ecef;
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
                padding: 2rem 1.5rem;
            }
            
            .auth-header h1 {
                font-size: 1.5rem;
            }
            
            .account-item {
                flex-direction: column;
                gap: 10px;
                text-align: center;
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
            width: 22px;
            height: 22px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* عناصر عائمة في الخلفية */
        .floating-elements {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .floating-element {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 20s infinite linear;
        }

        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); }
            100% { transform: translateY(-1000px) rotate(720deg); }
        }

        /* تأثيرات إضافية */
        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.4); }
            70% { box-shadow: 0 0 0 15px rgba(102, 126, 234, 0); }
            100% { box-shadow: 0 0 0 0 rgba(102, 126, 234, 0); }
        }

        /* شريط تقدم كلمة المرور */
        .password-strength {
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
            position: relative;
        }

        .password-strength-bar {
            height: 100%;
            width: 0;
            border-radius: 2px;
            transition: all 0.4s ease;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- عناصر عائمة في الخلفية -->
    <div class="floating-elements" id="floatingElements"></div>

    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo">
                    <div class="logo-text">نظام الشبانات</div>
                </div>
                <h1><i class="fas fa-sign-in-alt"></i> تسجيل الدخول</h1>
                <p>مرحباً بعودتك! يرجى تسجيل الدخول إلى حسابك</p>
            </div>
            
            <div class="auth-body">
                <!-- <?php if ($error): ?>
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
                <?php endif; ?> -->
                
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
                        <div class="password-strength">
                            <div class="password-strength-bar" id="password-strength-bar"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary pulse" id="loginBtn">
                            <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
                        </button>
                    </div>
                </form>
                
                <div class="auth-footer">
                    <p>ليس لديك حساب؟ <a href="register.php">إنشاء حساب جديد</a></p>
                    <p><a href="forgot-password.php">نسيت كلمة المرور؟</a></p>
                </div>
                
                <!-- <div class="demo-accounts">
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
                </div> -->
            </div>
        </div>
    </div>

    <script>
        // إنشاء العناصر العائمة في الخلفية
        document.addEventListener('DOMContentLoaded', function() {
            const floatingContainer = document.getElementById('floatingElements');
            const colors = ['rgba(255,255,255,0.1)', 'rgba(255,255,255,0.15)', 'rgba(255,255,255,0.2)'];
            
            for (let i = 0; i < 15; i++) {
                const element = document.createElement('div');
                element.classList.add('floating-element');
                
                // أحجام وعناصر عشوائية
                const size = Math.random() * 100 + 20;
                const color = colors[Math.floor(Math.random() * colors.length)];
                const left = Math.random() * 100;
                const animationDuration = Math.random() * 20 + 15;
                
                element.style.width = `${size}px`;
                element.style.height = `${size}px`;
                element.style.background = color;
                element.style.left = `${left}%`;
                element.style.animationDuration = `${animationDuration}s`;
                element.style.animationDelay = `${Math.random() * 5}s`;
                
                floatingContainer.appendChild(element);
            }
            
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

        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.parentNode.querySelector('.password-toggle i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
                icon.style.color = '#667eea';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
                icon.style.color = '#666';
            }
        }
        
        // تأثيرات كتابة كلمة السر
        document.getElementById('password').addEventListener('input', function() {
            const strength = this.value.length;
            const strengthBar = document.getElementById('password-strength-bar');
            
            if (strength < 4) {
                strengthBar.style.width = '25%';
                strengthBar.style.background = '#ef476f';
            } else if (strength < 8) {
                strengthBar.style.width = '50%';
                strengthBar.style.background = '#ffd166';
            } else {
                strengthBar.style.width = '100%';
                strengthBar.style.background = '#4ecdc4';
            }
        });

        // إضافة تأثير التحميل عند الإرسال
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        
        loginForm.addEventListener('submit', function() {
            loginBtn.classList.add('btn-loading');
            loginBtn.disabled = true;
            loginBtn.innerHTML = 'جاري التسجيل...';
        });
    </script>
</body>
</html>