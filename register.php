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
    <title>إنشاء حساب جديد - متجرنا</title>
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
            max-width: 500px;
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
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h1 style="margin: 0 0 0.5rem 0; font-size: 1.8rem; font-weight: 700;">انضم إلينا</h1>
                    <p style="margin: 0; opacity: 0.9; font-size: 1rem;">أنشئ حسابك الجديد واستمتع بتجربة تسوق فريدة</p>
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
                        <div style="margin-top: 1rem;">
                            <a href="login.php" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                <i class="fas fa-sign-in-alt"></i> الانتقال لتسجيل الدخول
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" id="registerForm">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="full_name">
                                    <i class="fas fa-user" style="color: var(--primary-color);"></i>
                                    الاسم الكامل *
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="full_name" 
                                       name="full_name" 
                                       placeholder="أدخل الاسم الكامل"
                                       value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                                       required
                                       style="padding: 1rem 1.25rem;">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="username">
                                    <i class="fas fa-at" style="color: var(--primary-color);"></i>
                                    اسم المستخدم *
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="username" 
                                       name="username" 
                                       placeholder="اختر اسم مستخدم فريد"
                                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                       required
                                       style="padding: 1rem 1.25rem;">
                                <small style="color: var(--gray-500); display: block; margin-top: 0.25rem;">
                                    سيستخدم لتسجيل الدخول
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope" style="color: var(--primary-color);"></i>
                            البريد الإلكتروني *
                        </label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               placeholder="أدخل بريدك الإلكتروني"
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                               required
                               style="padding: 1rem 1.25rem;">
                    </div>

                    <div class="form-group">
                        <label for="phone">
                            <i class="fas fa-phone" style="color: var(--primary-color);"></i>
                            رقم الهاتف
                        </label>
                        <input type="tel" 
                               class="form-control" 
                               id="phone" 
                               name="phone" 
                               placeholder="أدخل رقم هاتفك (اختياري)"
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                               style="padding: 1rem 1.25rem;">
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="password">
                                    <i class="fas fa-lock" style="color: var(--primary-color);"></i>
                                    كلمة المرور *
                                </label>
                                <div style="position: relative;">
                                    <input type="password" 
                                           class="form-control" 
                                           id="password" 
                                           name="password" 
                                           placeholder="كلمة المرور (6 أحرف على الأقل)"
                                           minlength="6"
                                           required
                                           style="padding: 1rem 1.25rem; padding-left: 3rem;">
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
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="confirm_password">
                                    <i class="fas fa-lock" style="color: var(--primary-color);"></i>
                                    تأكيد كلمة المرور *
                                </label>
                                <div style="position: relative;">
                                    <input type="password" 
                                           class="form-control" 
                                           id="confirm_password" 
                                           name="confirm_password" 
                                           placeholder="أعد إدخال كلمة المرور"
                                           required
                                           style="padding: 1rem 1.25rem; padding-left: 3rem;">
                                    <button type="button" 
                                            class="password-toggle" 
                                            onclick="togglePassword('confirm_password')"
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
                        </div>
                    </div>

                    <!-- مؤشر قوة كلمة المرور -->
                    <div class="password-strength" style="margin-bottom: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <span style="font-size: 0.9rem; color: var(--gray-600);">قوة كلمة المرور:</span>
                            <span id="password-strength-text" style="font-size: 0.8rem; font-weight: 600;">ضعيفة</span>
                        </div>
                        <div class="progress-bar">
                            <div id="password-strength-bar" class="progress-fill" style="width: 0%; background: var(--danger-color);"></div>
                        </div>
                    </div>

                    <!-- الشروط والأحكام -->
                    <div class="form-group" style="margin: 1.5rem 0;">
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

                    <button type="submit" 
                            class="btn btn-primary btn-block"
                            style="padding: 1rem; font-size: 1.1rem; font-weight: 600;">
                        <i class="fas fa-user-plus"></i>
                        إنشاء الحساب
                    </button>
                </form>

                <div class="auth-footer" style="
                    text-align: center;
                    margin-top: 2rem;
                    padding-top: 2rem;
                    border-top: 1px solid var(--gray-200);
                ">
                    <p style="color: var(--gray-600); margin-bottom: 1rem;">
                        لديك حساب بالفعل؟ 
                        <a href="login.php" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">
                            تسجيل الدخول
                        </a>
                    </p>
                    
                    <!-- فوائد التسجيل -->
                    <div style="
                        background: var(--gray-50);
                        padding: 1rem;
                        border-radius: var(--border-radius);
                        margin-top: 1rem;
                    ">
                        <h5 style="margin: 0 0 0.5rem 0; color: var(--gray-700); font-size: 0.9rem;">
                            <i class="fas fa-gift" style="color: var(--success-color);"></i>
                            مزايا التسجيل:
                        </h5>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.8rem;">
                            <div style="text-align: right;">
                                <i class="fas fa-shopping-cart" style="color: var(--primary-color);"></i>
                                تتبع الطلبات
                            </div>
                            <div style="text-align: right;">
                                <i class="fas fa-tags" style="color: var(--success-color);"></i>
                                عروض حصرية
                            </div>
                            <div style="text-align: right;">
                                <i class="fas fa-heart" style="color: var(--danger-color);"></i>
                                قائمة المفضلة
                            </div>
                            <div style="text-align: right;">
                                <i class="fas fa-bolt" style="color: var(--warning-color);"></i>
                                دفع أسرع
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

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
                this.style.boxShadow = '0 0 0 3px rgba(245, 101, 101, 0.1)';
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
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('كلمتا المرور غير متطابقتين!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('كلمة المرور يجب أن تكون 6 أحرف على الأقل!');
                return false;
            }
            
            if (!agreeTerms) {
                e.preventDefault();
                alert('يجب الموافقة على الشروط والأحكام!');
                return false;
            }
        });

        // تأثيرات عند التحميل
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
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