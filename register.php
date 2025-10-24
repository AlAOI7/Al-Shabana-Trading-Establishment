<?php
require_once 'config.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // التحقق من البيانات
    if (empty($full_name) || empty($username) || empty($email) || empty($password)) {
        $error = 'جميع الحقول المطلوبة يجب ملؤها';
    } elseif ($password !== $confirm_password) {
        $error = 'كلمتا المرور غير متطابقتين';
    } elseif (strlen($password) < 6) {
        $error = 'كلمة المرور يجب أن تكون至少 6 أحرف';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'البريد الإلكتروني غير صالح';
    } else {
        // التحقق من عدم وجود اسم مستخدم أو بريد إلكتروني مكرر
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            $error = 'اسم المستخدم أو البريد الإلكتروني موجود مسبقاً';
        } else {
            // إضافة المستخدم الجديد
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, user_type, full_name, phone) VALUES (?, ?, ?, 'client', ?, ?)");
            
            if ($stmt->execute([$username, $email, $hashed_password, $full_name, $phone])) {
                $success = 'تم إنشاء الحساب بنجاح! يمكنك الآن تسجيل الدخول.';
                
                // إرسال بريد ترحيبي (محاكاة)
                sendWelcomeEmail($email, $full_name);
                
                // تفريغ الحقول بعد التسجيل الناجح
                $_POST = array();
            } else {
                $error = 'حدث خطأ أثناء إنشاء الحساب. يرجى المحاولة مرة أخرى.';
            }
        }
    }
}

function sendWelcomeEmail($email, $name) {
    // في التطبيق الحقيقي، هنا سيتم إرسال بريد ترحيبي
    // هذه مجرد محاكاة
    error_log("Welcome email sent to: $email - Name: $name");
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب جديد - الشبانات</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color: #f093fb;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
            --light-color: #f8fafc;
            --dark-color: #1e293b;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
            --border-radius: 12px;
            --border-radius-lg: 16px;
            --border-radius-xl: 20px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            --gradient-primary: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            --gradient-accent: linear-gradient(135deg, var(--accent-color) 0%, #f5576c 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--gradient-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            line-height: 1.6;
            color: var(--gray-700);
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
            max-width: 520px;
            position: relative;
            z-index: 2;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius-xl);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            animation: cardAppear 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(255, 255, 255, 0.3);
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
            background: var(--gradient-primary);
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
            animation: wave 15s linear infinite;
        }

        @keyframes wave {
            0% { transform: translateX(0); }
            100% { transform: translateX(-10px); }
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            animation: logoFloat 3s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .auth-header h1 {
            margin: 0 0 0.5rem 0;
            font-size: 1.8rem;
            font-weight: 700;
            position: relative;
        }

        .auth-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 1rem;
            position: relative;
        }

        .auth-body {
            padding: 2.5rem 2rem;
        }

        .alert {
            padding: 1rem 1.25rem;
            border-radius: var(--border-radius);
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
            right: 0;
            width: 5px;
            height: 100%;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
            border-color: rgba(239, 68, 68, 0.2);
        }

        .alert-error::before {
            background: var(--danger-color);
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
            border-color: rgba(16, 185, 129, 0.2);
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
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--gray-700);
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid var(--gray-200);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            font-family: inherit;
            box-shadow: var(--shadow);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .input-with-icon {
            position: relative;
        }

        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            transition: all 0.3s ease;
        }

        .form-control:focus + .input-icon {
            color: var(--primary-color);
            transform: translateY(-50%) scale(1.1);
        }

        .password-toggle {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray-400);
            cursor: pointer;
            padding: 0.5rem;
            transition: all 0.3s ease;
        }

        .password-toggle:hover {
            color: var(--primary-color);
            transform: translateY(-50%) scale(1.1);
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 480px) {
            .row {
                grid-template-columns: 1fr;
            }
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 1rem 1.5rem;
            border: none;
            border-radius: var(--border-radius);
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
            right: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: right 0.7s;
        }

        .btn:hover::before {
            right: 100%;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: var(--shadow-lg);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-xl);
        }

        .btn-primary:active {
            transform: translateY(-1px);
        }

        .password-strength {
            margin-bottom: 1.5rem;
        }

        .progress-bar {
            height: 6px;
            background: var(--gray-200);
            border-radius: 3px;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .progress-fill {
            height: 100%;
            width: 0%;
            border-radius: 3px;
            transition: all 0.4s ease;
        }

        .auth-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--gray-200);
        }

        .auth-footer p {
            color: var(--gray-600);
            margin-bottom: 1rem;
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

        .benefits-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            font-size: 0.85rem;
            margin-top: 1rem;
        }

        .benefit-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            background: var(--gray-50);
            border-radius: var(--border-radius);
            transition: all 0.3s ease;
        }

        .benefit-item:hover {
            background: var(--gray-100);
            transform: translateX(-5px);
        }

        .benefits-card {
            background: var(--gray-50);
            padding: 1.25rem;
            border-radius: var(--border-radius);
            margin-top: 1.5rem;
        }

        .benefits-card h5 {
            margin: 0 0 0.75rem 0;
            color: var(--gray-700);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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

        /* تأثير النبض */
        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.4); }
            70% { box-shadow: 0 0 0 15px rgba(102, 126, 234, 0); }
            100% { box-shadow: 0 0 0 0 rgba(102, 126, 234, 0); }
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

        small {
            color: var(--gray-500);
            display: block;
            margin-top: 0.25rem;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <!-- عناصر عائمة في الخلفية -->
    <div class="floating-elements" id="floatingElements"></div>

    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h1>انضم إلينا</h1>
                <p>أنشئ حسابك الجديد واستمتع بتجربة تسوق فريدة</p>
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
                        <div style="margin-top: 1rem;">
                            <a href="login.php" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                <i class="fas fa-sign-in-alt"></i> الانتقال لتسجيل الدخول
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" id="registerForm">
                    <div class="row">
                        <div class="form-group">
                            <label for="full_name">
                                <i class="fas fa-user" style="color: var(--primary-color);"></i>
                                الاسم الكامل *
                            </label>
                            <div class="input-with-icon">
                                <input type="text" 
                                       class="form-control" 
                                       id="full_name" 
                                       name="full_name" 
                                       placeholder="أدخل الاسم الكامل"
                                       value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                                       required>
                                <i class="fas fa-user input-icon"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="username">
                                <i class="fas fa-at" style="color: var(--primary-color);"></i>
                                اسم المستخدم *
                            </label>
                            <div class="input-with-icon">
                                <input type="text" 
                                       class="form-control" 
                                       id="username" 
                                       name="username" 
                                       placeholder="اختر اسم مستخدم فريد"
                                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                       required>
                                <i class="fas fa-at input-icon"></i>
                            </div>
                            <small>سيستخدم لتسجيل الدخول</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope" style="color: var(--primary-color);"></i>
                            البريد الإلكتروني *
                        </label>
                        <div class="input-with-icon">
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   placeholder="أدخل بريدك الإلكتروني"
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                   required>
                            <i class="fas fa-envelope input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="phone">
                            <i class="fas fa-phone" style="color: var(--primary-color);"></i>
                            رقم الهاتف
                        </label>
                        <div class="input-with-icon">
                            <input type="tel" 
                                   class="form-control" 
                                   id="phone" 
                                   name="phone" 
                                   placeholder="أدخل رقم هاتفك (اختياري)"
                                   value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                            <i class="fas fa-phone input-icon"></i>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <label for="password">
                                <i class="fas fa-lock" style="color: var(--primary-color);"></i>
                                كلمة المرور *
                            </label>
                            <div class="input-with-icon">
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       placeholder="كلمة المرور (6 أحرف على الأقل)"
                                       minlength="6"
                                       required>
                                <i class="fas fa-lock input-icon"></i>
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">
                                <i class="fas fa-lock" style="color: var(--primary-color);"></i>
                                تأكيد كلمة المرور *
                            </label>
                            <div class="input-with-icon">
                                <input type="password" 
                                       class="form-control" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       placeholder="أعد إدخال كلمة المرور"
                                       required>
                                <i class="fas fa-lock input-icon"></i>
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- مؤشر قوة كلمة المرور -->
                    <div class="password-strength">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <span style="font-size: 0.9rem; color: var(--gray-600);">قوة كلمة المرور:</span>
                            <span id="password-strength-text" style="font-size: 0.8rem; font-weight: 600;">ضعيفة</span>
                        </div>
                        <div class="progress-bar">
                            <div id="password-strength-bar" class="progress-fill"></div>
                        </div>
                    </div>

                    <!-- الشروط والأحكام -->
                    <div class="form-group">
                        <label style="display: flex; align-items: start; gap: 8px; cursor: pointer; font-size: 0.9rem;">
                            <input type="checkbox" name="agree_terms" required style="margin-top: 0.25rem;">
                            <span style="color: var(--gray-700); line-height: 1.4;">
                                أوافق على 
                                <a href="terms.php" style="color: var(--primary-color); text-decoration: none;">الشروط والأحكام</a>
                                و
                                <a href="privacy.php" style="color: var(--primary-color); text-decoration: none;">سياسة الخصوصية</a>
                            </span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary pulse" id="registerBtn">
                        <i class="fas fa-user-plus"></i>
                        إنشاء الحساب
                    </button>
                </form>

                <div class="auth-footer">
                    <p>
                        لديك حساب بالفعل؟ 
                        <a href="login.php">تسجيل الدخول</a>
                    </p>
                    
                    <!-- فوائد التسجيل -->
                    <div class="benefits-card">
                        <h5>
                            <i class="fas fa-gift" style="color: var(--success-color);"></i>
                            مزايا التسجيل:
                        </h5>
                        <div class="benefits-grid">
                            <div class="benefit-item">
                                <i class="fas fa-shopping-cart" style="color: var(--primary-color);"></i>
                                <span>تتبع الطلبات</span>
                            </div>
                            <div class="benefit-item">
                                <i class="fas fa-tags" style="color: var(--success-color);"></i>
                                <span>عروض حصرية</span>
                            </div>
                            <div class="benefit-item">
                                <i class="fas fa-heart" style="color: var(--danger-color);"></i>
                                <span>قائمة المفضلة</span>
                            </div>
                            <div class="benefit-item">
                                <i class="fas fa-bolt" style="color: var(--warning-color);"></i>
                                <span>دفع أسرع</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // إنشاء العناصر العائمة في الخلفية
        document.addEventListener('DOMContentLoaded', function() {
            const floatingContainer = document.getElementById('floatingElements');
            const colors = ['rgba(255,255,255,0.1)', 'rgba(255,255,255,0.15)', 'rgba(255,255,255,0.2)'];
            
            for (let i = 0; i < 12; i++) {
                const element = document.createElement('div');
                element.classList.add('floating-element');
                
                const size = Math.random() * 80 + 20;
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
        });

        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.parentNode.querySelector('.password-toggle i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
                icon.style.color = 'var(--primary-color)';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
                icon.style.color = 'var(--gray-400)';
            }
        }

        // التحقق من قوة كلمة المرور
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('password-strength-bar');
            const strengthText = document.getElementById('password-strength-text');
            
            let strength = 0;
            let color = '';
            let text = '';
            
            if (password.length >= 6) strength += 25;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 25;
            if (password.match(/\d/)) strength += 25;
            if (password.match(/[^a-zA-Z\d]/)) strength += 25;
            
            if (strength <= 25) {
                color = 'var(--danger-color)';
                text = 'ضعيفة';
            } else if (strength <= 50) {
                color = 'var(--warning-color)';
                text = 'متوسطة';
            } else if (strength <= 75) {
                color = 'var(--info-color)';
                text = 'جيدة';
            } else {
                color = 'var(--success-color)';
                text = 'قوية';
            }
            
            strengthBar.style.width = strength + '%';
            strengthBar.style.background = color;
            strengthText.textContent = text;
            strengthText.style.color = color;
        });

        // التحقق من تطابق كلمات المرور
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.style.borderColor = 'var(--danger-color)';
                this.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.1)';
            } else {
                this.style.borderColor = '';
                this.style.boxShadow = '';
            }
        });

        // التحقق من النموذج قبل الإرسال
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const agreeTerms = document.querySelector('input[name="agree_terms"]').checked;
            const registerBtn = document.getElementById('registerBtn');
            
            if (password !== confirmPassword) {
                e.preventDefault();
                showMessage('كلمتا المرور غير متطابقتين!', 'error');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                showMessage('كلمة المرور يجب أن تكون 6 أحرف على الأقل!', 'error');
                return false;
            }
            
            if (!agreeTerms) {
                e.preventDefault();
                showMessage('يجب الموافقة على الشروط والأحكام!', 'error');
                return false;
            }
            
            // تأثير التحميل
            registerBtn.classList.add('btn-loading');
            registerBtn.disabled = true;
            registerBtn.innerHTML = 'جاري إنشاء الحساب...';
        });

        function showMessage(message, type) {
            // يمكنك إضافة رسائل تنبيهية هنا
            alert(message);
        }

        // تأثيرات عند التركيز على الحقول
        document.addEventListener('DOMContentLoaded', function() {
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
    </script>
</body>
</html>