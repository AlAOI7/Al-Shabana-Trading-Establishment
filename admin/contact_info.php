<?php 
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// جلب بيانات التواصل
$stmt = $pdo->query("SELECT * FROM contact_info LIMIT 1");
$contact_info = $stmt->fetch();

// معالجة تحديث البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_contact'])) {
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $social_facebook = $_POST['social_facebook'];
    $social_twitter = $_POST['social_twitter'];
    $social_instagram = $_POST['social_instagram'];
    
    if ($contact_info) {
        // تحديث البيانات الموجودة
        $stmt = $pdo->prepare("UPDATE contact_info SET address = ?, phone = ?, email = ?, social_facebook = ?, social_twitter = ?, social_instagram = ? WHERE id = ?");
        $stmt->execute([$address, $phone, $email, $social_facebook, $social_twitter, $social_instagram, $contact_info['id']]);
    } else {
        // إضافة بيانات جديدة
        $stmt = $pdo->prepare("INSERT INTO contact_info (address, phone, email, social_facebook, social_twitter, social_instagram) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$address, $phone, $email, $social_facebook, $social_twitter, $social_instagram]);
    }
    
    header("Location: contact_info.php?success=updated");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-translate="contact_info_management">بيانات التواصل - الإدارة</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --info: #4895ef;
            --warning: #f72585;
            --danger: #e63946;
            --light: #f8f9fa;
            --dark: #212529;
            --sidebar-bg: #1e293b;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fb;
            color: #333;
            line-height: 1.6;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* الشريط الجانبي */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            color: white;
            transition: var(--transition);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .sidebar-header h3 {
            margin-bottom: 0.5rem;
            font-size: 1.4rem;
        }

        .sidebar-header p {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .sidebar-menu {
            list-style: none;
            padding: 1rem 0;
        }

        .sidebar-menu li {
            margin-bottom: 0.2rem;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.8rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--transition);
            border-right: 3px solid transparent;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-right-color: var(--primary);
        }

        .sidebar-menu i {
            margin-left: 0.5rem;
            width: 20px;
            text-align: center;
        }

        /* المحتوى الرئيسي */
        .main-content {
            flex: 1;
            padding: 1.5rem;
            overflow-y: auto;
        }

        .header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .header h1 {
            color: var(--dark);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: #64748b;
        }

        /* بطاقات */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: var(--transition);
            border: none;
            margin-bottom: 1.5rem;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            background: white;
        }

        .card-header h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark);
        }

        .card-body {
            padding: 1.5rem;
        }

        /* الأزرار */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.7rem 1.2rem;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--secondary);
            color: white;
        }

        /* نماذج */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        /* شبكة الصفوف والأعمدة */
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -0.75rem;
        }

        .col-6 {
            flex: 0 0 50%;
            max-width: 50%;
            padding: 0 0.75rem;
        }

        .col-4 {
            flex: 0 0 33.333%;
            max-width: 33.333%;
            padding: 0 0.75rem;
        }

        /* تنبيهات */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert i {
            margin-left: 0.5rem;
        }

        /* معاينة البيانات */
        .preview-box {
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 1rem;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .social-links a {
            transition: var(--transition);
        }

        .social-links a:hover {
            transform: scale(1.2);
        }

        /* ألوان وسائل التواصل */
        .facebook { color: #1877f2; }
        .twitter { color: #1da1f2; }
        .instagram { color: #e4405f; }

        /* زر الترجمة */
        .translate-btn {
            position: fixed;
            bottom: 20px;
            left: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            z-index: 1000;
            transition: var(--transition);
            border: none;
        }

        .translate-btn:hover {
            transform: scale(1.1);
            background: var(--secondary);
        }

        .translate-btn i {
            font-size: 1.2rem;
        }

        /* نمط للغة الإنجليزية */
        body[dir="ltr"] {
            text-align: left;
        }

        body[dir="ltr"] .sidebar {
            text-align: left;
        }

        body[dir="ltr"] .sidebar-menu i {
            margin-left: 0;
            margin-right: 0.5rem;
        }

        body[dir="ltr"] .alert i {
            margin-left: 0;
            margin-right: 0.5rem;
        }

        body[dir="ltr"] .social-links {
            flex-direction: row;
        }

        /* تصميم متجاوب */
        @media (max-width: 992px) {
            .dashboard {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
            }
            
            .sidebar-menu {
                display: flex;
                overflow-x: auto;
                padding: 0.5rem;
            }
            
            .sidebar-menu li {
                flex: 0 0 auto;
                margin-bottom: 0;
            }
            
            .sidebar-menu a {
                padding: 0.8rem 1rem;
                border-right: none;
                border-bottom: 3px solid transparent;
            }
            
            .sidebar-menu a:hover, .sidebar-menu a.active {
                border-right-color: transparent;
                border-bottom-color: var(--primary);
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }
            
            .col-6, .col-4 {
                flex: 0 0 100%;
                max-width: 100%;
                margin-bottom: 1rem;
            }
            
            .social-links {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
           <?php include 'sidebar.php'; ?>
        
        <!-- المحتوى الرئيسي -->
        <main class="main-content">
            <?php include 'admin_navbar.php'; ?>
            <div class="header">
                <h1><i class="fas fa-address-book"></i> <span data-translate="contact_info_management">إدارة بيانات التواصل</span></h1>
                <p data-translate="contact_info_desc">تحديث معلومات التواصل والروابط الاجتماعية</p>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> 
                    <span data-translate="contact_info_updated">تم تحديث بيانات التواصل بنجاح</span>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-edit"></i> <span data-translate="contact_information">معلومات التواصل</span></h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="address">
                                        <i class="fas fa-map-marker-alt"></i> 
                                        <span data-translate="address">العنوان</span>
                                    </label>
                                    <textarea class="form-control" id="address" name="address" rows="3" 
                                              placeholder="أدخل العنوان الكامل"><?php echo $contact_info ? htmlspecialchars($contact_info['address']) : ''; ?></textarea>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="phone">
                                        <i class="fas fa-phone"></i> 
                                        <span data-translate="phone">رقم الهاتف</span>
                                    </label>
                                    <input type="text" class="form-control" id="phone" name="phone" 
                                           value="<?php echo $contact_info ? htmlspecialchars($contact_info['phone']) : ''; ?>" 
                                           placeholder="أدخل رقم الهاتف">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="email">
                                        <i class="fas fa-envelope"></i> 
                                        <span data-translate="email">البريد الإلكتروني</span>
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo $contact_info ? htmlspecialchars($contact_info['email']) : ''; ?>" 
                                           placeholder="أدخل البريد الإلكتروني">
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h4><i class="fas fa-share-alt"></i> <span data-translate="social_links">الروابط الاجتماعية</span></h4>
                        
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="social_facebook" class="facebook">
                                        <i class="fab fa-facebook"></i> 
                                        <span data-translate="facebook">فيسبوك</span>
                                    </label>
                                    <input type="url" class="form-control" id="social_facebook" name="social_facebook" 
                                           value="<?php echo $contact_info ? htmlspecialchars($contact_info['social_facebook']) : ''; ?>" 
                                           placeholder="رابط الصفحة على فيسبوك">
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="social_twitter" class="twitter">
                                        <i class="fab fa-twitter"></i> 
                                        <span data-translate="twitter">تويتر</span>
                                    </label>
                                    <input type="url" class="form-control" id="social_twitter" name="social_twitter" 
                                           value="<?php echo $contact_info ? htmlspecialchars($contact_info['social_twitter']) : ''; ?>" 
                                           placeholder="رابط الحساب على تويتر">
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="social_instagram" class="instagram">
                                        <i class="fab fa-instagram"></i> 
                                        <span data-translate="instagram">إنستجرام</span>
                                    </label>
                                    <input type="url" class="form-control" id="social_instagram" name="social_instagram" 
                                           value="<?php echo $contact_info ? htmlspecialchars($contact_info['social_instagram']) : ''; ?>" 
                                           placeholder="رابط الحساب على إنستجرام">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" name="update_contact" class="btn btn-primary">
                            <i class="fas fa-save"></i> 
                            <span data-translate="save_data">حفظ البيانات</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- معاينة بيانات التواصل -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-eye"></i> <span data-translate="contact_preview">معاينة بيانات التواصل</span></h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <h4 data-translate="contact_info">معلومات الاتصال</h4>
                            <div class="preview-box">
                                <?php if ($contact_info): ?>
                                    <p><strong><i class="fas fa-map-marker-alt"></i> <span data-translate="address">العنوان</span>:</strong><br>
                                    <?php echo $contact_info['address'] ? nl2br(htmlspecialchars($contact_info['address'])) : '<span style="color: #999;" data-translate="not_specified">غير محدد</span>'; ?></p>
                                    
                                    <p><strong><i class="fas fa-phone"></i> <span data-translate="phone">الهاتف</span>:</strong><br>
                                    <?php echo $contact_info['phone'] ?: '<span style="color: #999;" data-translate="not_specified">غير محدد</span>'; ?></p>
                                    
                                    <p><strong><i class="fas fa-envelope"></i> <span data-translate="email">البريد الإلكتروني</span>:</strong><br>
                                    <?php echo $contact_info['email'] ?: '<span style="color: #999;" data-translate="not_specified">غير محدد</span>'; ?></p>
                                <?php else: ?>
                                    <p style="color: #999; text-align: center;" data-translate="no_data">لا توجد بيانات لعرضها</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-6">
                            <h4 data-translate="social_media">وسائل التواصل الاجتماعي</h4>
                            <div class="preview-box" style="text-align: center;">
                                <?php if ($contact_info && ($contact_info['social_facebook'] || $contact_info['social_twitter'] || $contact_info['social_instagram'])): ?>
                                    <div class="social-links">
                                        <?php if ($contact_info['social_facebook']): ?>
                                            <a href="<?php echo $contact_info['social_facebook']; ?>" target="_blank" class="facebook">
                                                <i class="fab fa-facebook"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($contact_info['social_twitter']): ?>
                                            <a href="<?php echo $contact_info['social_twitter']; ?>" target="_blank" class="twitter">
                                                <i class="fab fa-twitter"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($contact_info['social_instagram']): ?>
                                            <a href="<?php echo $contact_info['social_instagram']; ?>" target="_blank" class="instagram">
                                                <i class="fab fa-instagram"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <p style="margin-top: 1rem; font-size: 0.9rem; color: #666;" data-translate="click_icons">
                                        انقر على الأيقونات لزيارة الصفحات
                                    </p>
                                <?php else: ?>
                                    <p style="color: #999;" data-translate="no_social_links">لا توجد روابط اجتماعية مضافة</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- زر الترجمة العائم -->
    <button class="translate-btn" id="translateBtn">
        <i class="fas fa-language"></i>
    </button>

    <script>
        // نصوص الترجمة
        const translations = {
            ar: {
                    "dashboard": "لوحة التحكم الرئيسية",
                                "welcome": "مرحباً،",
                "home": "الرئيسية",
                "user_management": "إدارة المستخدمين",
                "product_management": "إدارة المنتجات",
                "service_management": "إدارة الخدمات",
                "order_management": "إدارة الطلبات",
                "about_us": "من نحن",
                "contact_info": "بيانات التواصل",
                "settings": "الإعدادات",
                "logout": "تسجيل الخروج",
                "profile": "الملف الشخصي",

                // العناوين الرئيسية
                "contact_info_management": "بيانات التواصل - الإدارة",
                "contact_info_desc": "تحديث معلومات التواصل والروابط الاجتماعية",
                "contact_information": "معلومات التواصل",
                "contact_preview": "معاينة بيانات التواصل",
                
                // نماذج البيانات
                "address": "العنوان",
                "phone": "رقم الهاتف",
                "email": "البريد الإلكتروني",
                "social_links": "الروابط الاجتماعية",
                "facebook": "فيسبوك",
                "twitter": "تويتر",
                "instagram": "إنستجرام",
                
                // معاينة البيانات
                "contact_info": "معلومات الاتصال",
                "social_media": "وسائل التواصل الاجتماعي",
                "not_specified": "غير محدد",
                "no_data": "لا توجد بيانات لعرضها",
                "no_social_links": "لا توجد روابط اجتماعية مضافة",
                "click_icons": "انقر على الأيقونات لزيارة الصفحات",
                
                // أزرار
                "save_data": "حفظ البيانات",
                
                // رسائل النجاح
                "contact_info_updated": "تم تحديث بيانات التواصل بنجاح"
            },
            en: {
                 "dashboard": "Main Dashboard",
                     "welcome": "Welcome,",
                    "home": "Home",
                    "user_management": "User Management",
                    "product_management": "Product Management",
                    "service_management": "Service Management",
                    "order_management": "Order Management",
                    "about_us": "About Us",
                    "contact_info": "Contact Info",
                    "settings": "Settings",
                    "logout": "Logout",
                    "profile": "Profile",
                // العناوين الرئيسية
                "contact_info_management": "Contact Information - Admin",
                "contact_info_desc": "Update contact information and social links",
                "contact_information": "Contact Information",
                "contact_preview": "Contact Information Preview",
                
                // نماذج البيانات
                "address": "Address",
                "phone": "Phone Number",
                "email": "Email",
                "social_links": "Social Links",
                "facebook": "Facebook",
                "twitter": "Twitter",
                "instagram": "Instagram",
                
                // معاينة البيانات
                "contact_info": "Contact Information",
                "social_media": "Social Media",
                "not_specified": "Not Specified",
                "no_data": "No data to display",
                "no_social_links": "No social links added",
                "click_icons": "Click on icons to visit pages",
                
                // أزرار
                "save_data": "Save Data",
                
                // رسائل النجاح
                "contact_info_updated": "Contact information updated successfully"
            }
        };

        // حالة اللغة الحالية
        let currentLang = localStorage.getItem('language') || 'ar';

        // دالة لتطبيق الترجمة
        function applyLanguage(lang) {
            // تحديث النصوص في الصفحة
            document.querySelectorAll('[data-translate]').forEach(element => {
                const key = element.getAttribute('data-translate');
                if (translations[lang][key]) {
                    element.textContent = translations[lang][key];
                }
            });

            // تحديث النصوص في العناصر الأخرى
            document.querySelectorAll('input[placeholder], textarea[placeholder]').forEach(element => {
                const placeholder = element.getAttribute('placeholder');
                if (placeholder && translations[lang][placeholder]) {
                    element.setAttribute('placeholder', translations[lang][placeholder]);
                }
            });

            // تحديث اتجاه الصفحة
            if (lang === 'ar') {
                document.documentElement.dir = 'rtl';
                document.documentElement.lang = 'ar';
                document.title = 'بيانات التواصل - الإدارة';
            } else {
                document.documentElement.dir = 'ltr';
                document.documentElement.lang = 'en';
                document.title = 'Contact Information - Admin';
            }

            // حفظ اللغة في localStorage
            localStorage.setItem('language', lang);
            currentLang = lang;
        }

        // حدث النقر على زر الترجمة
        document.getElementById('translateBtn').addEventListener('click', function() {
            const newLang = currentLang === 'ar' ? 'en' : 'ar';
            applyLanguage(newLang);
        });

        // تطبيق اللغة عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            applyLanguage(currentLang);
        });
    </script>
</body>
</html>