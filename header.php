<?php
require_once 'config.php';
include 'config/database.php';
// تحديد اللغة الحالية
// تحديد اللغة الحالية
$current_lang = isset($_COOKIE['site_language']) ? $_COOKIE['site_language'] : 'ar';


// تحديد نص زر اللغة
$lang_toggle_text = $current_lang == 'ar' ? 'EN' : 'AR';
?>


<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مؤسسة عبدالرحمن محمد الشبانات التجارية - الصفحة الرئيسية</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<style>
                /* قسم الخدمات */
            .services-section {
                padding: 80px 0;
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                position: relative;
            }

            .services-section::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 2px;
                background: linear-gradient(90deg, transparent, #2c5aa0, transparent);
            }

            .services-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 2rem;
                margin-top: 3rem;
            }

            .service-card {
                background: white;
                border-radius: 20px;
                padding: 2.5rem 2rem;
                text-align: center;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                transition: all 0.3s ease;
                border: 1px solid #f0f0f0;
                position: relative;
                overflow: hidden;
            }

            .service-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 4px;
                background: linear-gradient(90deg, #2c5aa0, #f8b500);
                transform: scaleX(0);
                transition: transform 0.3s ease;
            }

            .service-card:hover {
                transform: translateY(-10px);
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            }

            .service-card:hover::before {
                transform: scaleX(1);
            }

            .service-icon {
                width: 80px;
                height: 80px;
                margin: 0 auto 1.5rem;
                background: linear-gradient(135deg, #2c5aa0, #3a6fd9);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
            }

            .service-icon::before {
                content: '';
                position: absolute;
                width: 90px;
                height: 90px;
                border: 2px solid #f8b500;
                border-radius: 50%;
                animation: pulse 2s infinite;
            }

            .service-icon i {
                font-size: 2rem;
                color: white;
                z-index: 2;
                position: relative;
            }

            .service-content {
                position: relative;
                z-index: 2;
            }

            .service-title {
                font-size: 1.4rem;
                font-weight: 700;
                color: #2c5aa0;
                margin-bottom: 1rem;
                line-height: 1.3;
            }

            .service-description {
                color: #666;
                line-height: 1.6;
                font-size: 1rem;
            }

            .no-services, .error-message {
                grid-column: 1 / -1;
                text-align: center;
                padding: 3rem;
                background: white;
                border-radius: 15px;
                color: #666;
                font-size: 1.1rem;
            }

            /* تأثيرات الحركة */
            @keyframes pulse {
                0% {
                    transform: scale(0.8);
                    opacity: 1;
                }
                50% {
                    transform: scale(1);
                    opacity: 0.5;
                }
                100% {
                    transform: scale(0.8);
                    opacity: 1;
                }
            }

            /* تصميم متجاوب */
            @media (max-width: 768px) {
                .services-grid {
                    grid-template-columns: 1fr;
                    gap: 1.5rem;
                }
                
                .service-card {
                    padding: 2rem 1.5rem;
                }
                
                .service-icon {
                    width: 70px;
                    height: 70px;
                }
                
                .service-icon i {
                    font-size: 1.8rem;
                }
                
                .service-title {
                    font-size: 1.3rem;
                }
            }

            @media (max-width: 480px) {
                .services-section {
                    padding: 60px 0;
                }
                
                .service-card {
                    padding: 1.5rem 1rem;
                }
            }
                /* تحسينات لعرض صور المنتجات */
            .product-image {
                position: relative;
                width: 100%;
                height: 200px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #f8f9fa;
                border-radius: 8px;
                overflow: hidden;
                margin-bottom: 15px;
            }

            .product-img {
                width: 100%;
                height: 100%;
                object-fit: contain;
                transition: transform 0.3s ease;
            }

            .product-card:hover .product-img {
                transform: scale(1.05);
            }

            .product-icon {
                font-size: 3rem;
                color: #6c757d;
                display: flex;
                align-items: center;
                justify-content: center;
                width: 100%;
                height: 100%;
            }

            .fallback-icon {
                background: #e9ecef;
            }

            .product-code {
                background: #007bff;
                color: white;
                padding: 2px 8px;
                border-radius: 4px;
                font-size: 0.8rem;
                display: inline-block;
                margin-bottom: 8px;
            }

            .product-name {
                font-size: 1.1rem;
                margin: 8px 0;
                color: #333;
                line-height: 1.4;
            }

            .product-group, .product-brand {
                font-size: 0.9rem;
                color: #666;
                margin: 4px 0;
            }

            .no-products {
                text-align: center;
                padding: 40px;
                font-size: 1.1rem;
                color: #666;
                grid-column: 1 / -1;
            }
</style>

<body>
    <!-- شريط التنقل العلوي -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-content">
                 <div class="welcome-text">
                                           <?php echo getTranslatedText('welcome_text'); ?>


                    
                </div>
                <!-- أضف داخل <head> -->

<style>
        /* تصميم الأيقونة والقائمة */
        .user-menu {
            position: relative;
            display: inline-block;
        }

        .user-icon {
            cursor: pointer;
            color: #fff;
            font-size: 22px;
            background-color: #007bff;
            border-radius: 50%;
            padding: 10px 12px;
            transition: background 0.3s;
            z-index: 99999;
            position: relative;
        }
        .user-icon:hover {
            background-color: #0056b3;
        }

        /* القائمة المنسدلة */
        .dropdown-menu {
            display: none;
            position: fixed; /* ثابتة فوق كل الصفحة */
            top: 60px; /* المسافة من أعلى الصفحة */
            right: 20px; /* تبتعد قليلاً عن اليمين */
            background-color: #fff;
            min-width: 180px;
            border-radius: 10px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.3);
            z-index: 999999; /* فوق كل العناصر */
            overflow: hidden;
        }

        .dropdown-menu a,
        .dropdown-menu span {
            color: #333;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: background 0.2s;
            font-size: 15px;
        }

        .dropdown-menu a:hover {
            background-color: #f0f0f0;
        }

        .dropdown-menu span {
            font-weight: bold;
            background-color: #f8f9fa;
        }

        /* سهم صغير فوق القائمة */
        .dropdown-menu::before {
            content: "";
            position: absolute;
            top: -10px;
            right: 25px;
            border-width: 5px;
            border-style: solid;
            border-color: transparent transparent #fff transparent;
        }
</style>

<!-- HTML + PHP -->
<li class="user-menu">
    <i class="fas fa-user user-icon" id="userIcon"></i>

    <div class="dropdown-menu" id="userDropdown">
        <?php if (isLoggedIn()): ?>
            <span>مرحباً، <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
            <?php if (isAdmin()): ?>
                <a href="admin/dashboard.php">لوحة التحكم</a>
            <?php else: ?>
                <a href="client/dashboard.php">حسابي</a>
            <?php endif; ?>
            <a href="logout.php">تسجيل الخروج</a>
        <?php else: ?>
            <a href="login.php">تسجيل الدخول</a>
            <a href="register.php">إنشاء حساب</a>
        <?php endif; ?>
    </div>
</li>

<!-- جافاسكريبت لإظهار/إخفاء القائمة عند النقر -->
<script>
        document.addEventListener("DOMContentLoaded", function() {
            const icon = document.getElementById("userIcon");
            const dropdown = document.getElementById("userDropdown");

            icon.addEventListener("click", function(e) {
                e.stopPropagation();
                dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
            });

            // إغلاق القائمة عند النقر خارجها
            document.addEventListener("click", function(e) {
                if (!dropdown.contains(e.target) && e.target !== icon) {
                    dropdown.style.display = "none";
                }
            });
        });
</script>

                <div class="top-bar-actions">
                  <button id="languageToggle" class="lang-btn">
                        <i class="fas fa-globe"></i>
                        <span class="lang-text"><?php echo $lang_toggle_text; ?></span>
                    </button>
                    
                    <div class="contact-top">
                       <a href="tel:<?php echo getSetting('contact_top_phone'); ?>">
                            <i class="fas fa-phone"></i> 
                            <?php echo getTranslatedText('contact_top_phone_display'); ?>
                        </a>
                      </div>
                </div>
            </div>
        </div>
    </div>

    <!-- الهيدر الرئيسي -->
      <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo-container">
                    <img src="2.png" alt="شعار مؤسسة الشبانات" class="logo-image">
                    <div class="logo-text">
                        <span class="logo-main">
                            <?php echo getTranslatedText('header_logo_text'); ?>
                        </span>
                        <span class="logo-sub">
                            <?php echo getSetting('header_logo_subtext'); ?>
                        </span>
                    </div>
                </div>
                
                <nav class="main-nav">
                    <ul class="nav-list">
                        <li><a href="index.php" class="nav-link active">
                            <i class="fas fa-home"></i> 
                            <span data-ar="الرئيسية" data-en="Home">الرئيسية</span>
                        </a></li>
                        <li><a href="about.php" class="nav-link">
                            <i class="fas fa-building"></i> 
                            <span data-ar="من نحن" data-en="About">عنا</span>
                        </a></li>
                        <li><a href="products.php" class="nav-link">
                            <i class="fas fa-box-open"></i> 
                            <span data-ar="المنتجات" data-en="Products">المنتجات</span>
                        </a></li>
                         
                        <li><a href="#stats" class="nav-link">
                            <i class="fas fa-chart-bar"></i> 
                            <span data-ar="الإحصائيات" data-en="Statistics">الإحصائيات</span>
                        </a></li>
                        <li><a href="#brands" class="nav-link">
                            <i class="fas fa-tags"></i> 
                            <span data-ar="العلامات" data-en="Brands">العلامات</span>
                        </a></li>
                        <li><a href="contact.php" class="nav-link">
                            <i class="fas fa-envelope"></i> 
                            <span data-ar="اتصل بنا" data-en="Contact">اتصل بنا</span>
                        </a></li>
                    </ul>
                </nav>

                <div class="header-actions">
                    <button class="search-btn" aria-label="بحث">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="menu-toggle" aria-label="قائمة التنقل">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>
            </div>
        </div>
    </header>