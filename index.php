<?php
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الصفحة الرئيسية</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        
        .hero-section h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .hero-section p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .features-section {
            padding: 80px 0;
            background: #f8f9fa;
        }
        
        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
        }
        
        .feature-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- شريط التنقل -->
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <div class="logo">
                    <i class="fas fa-store"></i> متجرنا
                </div>
                <ul class="nav-links">
                    <?php if (isLoggedIn()): ?>
                        <li><span style="color: #fff;">مرحباً، <?php echo $_SESSION['full_name']; ?></span></li>
                        <?php if (isAdmin()): ?>
                            <li><a href="admin/dashboard.php">لوحة التحكم</a></li>
                        <?php else: ?>
                            <li><a href="client/dashboard.php">حسابي</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php">تسجيل الخروج</a></li>
                    <?php else: ?>
                        <li><a href="login.php">تسجيل الدخول</a></li>
                        <li><a href="register.php">إنشاء حساب</a></li>
                    <?php endif; ?>
                    <li><a href="#features">المميزات</a></li>
                    <li><a href="#about">من نحن</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- قسم البطل -->
    <section class="hero-section">
        <div class="container">
            <h1>مرحباً بك في متجرنا الإلكتروني</h1>
            <p>اكتشف أحدث المنتجات والعروض الحصرية</p>
            <?php if (!isLoggedIn()): ?>
                <a href="register.php" class="btn btn-primary" style="margin: 0 10px;">
                    <i class="fas fa-user-plus"></i> انضم إلينا الآن
                </a>
                <a href="login.php" class="btn btn-secondary">
                    <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
                </a>
            <?php else: ?>
                <a href="<?php echo isAdmin() ? 'admin/dashboard.php' : 'client/dashboard.php'; ?>" class="btn btn-primary">
                    <i class="fas fa-tachometer-alt"></i> الانتقال إلى لوحة التحكم
                </a>
            <?php endif; ?>
        </div>
    </section>

    <!-- قسم المميزات -->
    <section id="features" class="features-section">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 3rem;">مميزات منصتنا</h2>
            <div class="row">
                <div class="col-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h3>تسوق سهل</h3>
                        <p>تجربة تسوق سلسة وسهلة مع واجهة مستخدم بديهية</p>
                    </div>
                </div>
                <div class="col-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>آمن ومضمون</h3>
                        <p>نحن نحمي بياناتك ونضمن معاملات آمنة</p>
                    </div>
                </div>
                <div class="col-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3>دعم فني</h3>
                        <p>فريق دعم فني متاح لمساعدتك على مدار الساعة</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- الفوتر -->
    <footer style="background: var(--dark-color); color: white; padding: 2rem 0; text-align: center;">
        <div class="container">
            <p>&copy; 2024 متجرنا الإلكتروني. جميع الحقوق محفوظة.</p>
        </div>
    </footer>
</body>
</html>