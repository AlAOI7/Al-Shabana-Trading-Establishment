<?php 
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
// جلب بيانات "من نحن" من الإعدادات
$stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'about_us'");
$about_us = $stmt->fetchColumn();

// معالجة تحديث البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_about'])) {
    $about_content = $_POST['about_content'];
    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'about_us'");
    $stmt->execute([$about_content]);
    
    header("Location: about.php?success=updated");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-translate="about_us_management">من نحن - الإدارة</title>
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
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 300px;
            line-height: 1.6;
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

        /* معاينة الصفحة */
        .preview-box {
            padding: 2rem;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            line-height: 1.8;
        }

        .preview-box p {
            margin-bottom: 1rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #666;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

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

        body[dir="ltr"] .empty-state {
            text-align: center;
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
            
            .preview-box {
                padding: 1rem;
            }
            
            textarea.form-control {
                min-height: 200px;
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
                <h1><i class="fas fa-info-circle"></i> <span data-translate="about_us_management">إدارة صفحة "من نحن"</span></h1>
                <p data-translate="about_us_desc">تعديل محتوى صفحة من نحن المعروضة للزوار</p>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> 
                    <span data-translate="about_us_updated">تم تحديث محتوى "من نحن" بنجاح</span>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-edit"></i> <span data-translate="about_us_content">محتوى صفحة "من نحن"</span></h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label for="about_content" data-translate="page_content">محتوى الصفحة</label>
                            <textarea class="form-control" id="about_content" name="about_content" rows="15" 
                                      placeholder="اكتب محتوى صفحة 'من نحن' هنا..."><?php echo htmlspecialchars($about_us ?: ''); ?></textarea>
                        </div>
                        
                        <button type="submit" name="update_about" class="btn btn-primary">
                            <i class="fas fa-save"></i> 
                            <span data-translate="save_changes">حفظ التغييرات</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- معاينة الصفحة -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-eye"></i> <span data-translate="page_preview">معاينة الصفحة</span></h3>
                </div>
                <div class="card-body">
                    <?php if ($about_us): ?>
                        <div class="preview-box">
                            <?php echo nl2br(htmlspecialchars($about_us)); ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-info-circle"></i>
                            <p data-translate="no_content">لا يوجد محتوى لعرضه. يرجى إضافة محتوى لصفحة "من نحن".</p>
                        </div>
                    <?php endif; ?>
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
                "about_us_management": "من نحن - الإدارة",
                "about_us_desc": "تعديل محتوى صفحة من نحن المعروضة للزوار",
                "about_us_content": "محتوى صفحة \"من نحن\"",
                "page_preview": "معاينة الصفحة",
                
                // نماذج البيانات
                "page_content": "محتوى الصفحة",
                
                // معاينة الصفحة
                "no_content": "لا يوجد محتوى لعرضه. يرجى إضافة محتوى لصفحة \"من نحن\".",
                
                // أزرار
                "save_changes": "حفظ التغييرات",
                
                // رسائل النجاح
                "about_us_updated": "تم تحديث محتوى \"من نحن\" بنجاح"
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
                "about_us_management": "About Us - Admin",
                "about_us_desc": "Edit the content of the About Us page displayed to visitors",
                "about_us_content": "About Us Page Content",
                "page_preview": "Page Preview",
                
                // نماذج البيانات
                "page_content": "Page Content",
                
                // معاينة الصفحة
                "no_content": "No content to display. Please add content to the About Us page.",
                
                // أزرار
                "save_changes": "Save Changes",
                
                // رسائل النجاح
                "about_us_updated": "About Us content updated successfully"
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
                document.title = 'من نحن - الإدارة';
            } else {
                document.documentElement.dir = 'ltr';
                document.documentElement.lang = 'en';
                document.title = 'About Us - Admin';
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

        // تحسين تجربة الكتابة
        document.getElementById('about_content').addEventListener('focus', function() {
            this.style.minHeight = '300px';
        });

        // تطبيق اللغة عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            applyLanguage(currentLang);
        });
    </script>
</body>
</html>