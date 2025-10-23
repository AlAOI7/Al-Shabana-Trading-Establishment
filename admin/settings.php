<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// جلب جميع الإعدادات
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $settings_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // الآن سيعمل لأننا اخترنا عمودين فقط
} catch (PDOException $e) {
    $settings_data = [];
    $error = "خطأ في جلب البيانات: " . $e->getMessage();
}

// معالجة تحديث الإعدادات
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    try {
        foreach ($_POST['settings'] as $key => $value) {
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->execute([$value, $key]);
        }
        
        header("Location: settings.php?success=updated");
        exit();
    } catch (PDOException $e) {
        $error = "خطأ في تحديث الإعدادات: " . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-translate="site_settings">الإعدادات - الإدارة</title>
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

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-success:hover {
            background: #38b2d6;
            color: white;
        }

        .btn-warning {
            background: var(--warning);
            color: white;
        }

        .btn-warning:hover {
            background: #e1156d;
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
            min-height: 80px;
        }

        select.form-control {
            cursor: pointer;
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

        /* أدوات متقدمة */
        .tool-card {
            text-align: center;
            height: 100%;
            transition: var(--transition);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            background: #f8f9fa;
        }

        .tool-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .tool-icon {
            font-size: 2.5rem;
            margin: 1.5rem 0;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
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
            
            .tool-card {
                margin-bottom: 1rem;
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
                <h1><i class="fas fa-cogs"></i> <span data-translate="site_settings">إعدادات الموقع</span></h1>
                <p data-translate="site_settings_desc">تعديل الإعدادات العامة للموقع</p>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> 
                    <span data-translate="settings_updated">تم تحديث الإعدادات بنجاح</span>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-sliders-h"></i> <span data-translate="general_settings">الإعدادات العامة</span></h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="site_title">
                                        <i class="fas fa-heading"></i> 
                                        <span data-translate="site_title">عنوان الموقع</span>
                                    </label>
                                    <input type="text" class="form-control" id="site_title" name="settings[site_title]" 
                                           value="<?php echo htmlspecialchars($settings_data['site_title'] ?? ''); ?>" 
                                           placeholder="أدخل عنوان الموقع" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="site_description">
                                        <i class="fas fa-align-left"></i> 
                                        <span data-translate="site_description">وصف الموقع</span>
                                    </label>
                                    <textarea class="form-control" id="site_description" name="settings[site_description]" 
                                              rows="3" placeholder="أدخل وصف مختصر للموقع"><?php echo htmlspecialchars($settings_data['site_description'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="maintenance_mode">
                                        <i class="fas fa-tools"></i> 
                                        <span data-translate="maintenance_mode">وضع الصيانة</span>
                                    </label>
                                    <select class="form-control" id="maintenance_mode" name="settings[maintenance_mode]">
                                        <option value="0" <?php echo ($settings_data['maintenance_mode'] ?? '0') == '0' ? 'selected' : ''; ?> data-translate="disabled">معطل</option>
                                        <option value="1" <?php echo ($settings_data['maintenance_mode'] ?? '0') == '1' ? 'selected' : ''; ?> data-translate="enabled">مفعل</option>
                                    </select>
                                    <small style="color: #666;">
                                        <span data-translate="maintenance_mode_desc">عند التفعيل، سيظهر للمستخدمين رسالة صيانة</span>
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="update_settings" class="btn btn-primary">
                                <i class="fas fa-save"></i> 
                                <span data-translate="save_settings">حفظ الإعدادات</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- إعدادات متقدمة -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-tools"></i> <span data-translate="advanced_tools">أدوات متقدمة</span></h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-4">
                            <div class="tool-card">
                                <div class="card-body">
                                    <div class="tool-icon" style="color: var(--primary);">
                                        <i class="fas fa-database"></i>
                                    </div>
                                    <h4 data-translate="backup">نسخ احتياطي</h4>
                                    <p data-translate="backup_desc">إنشاء نسخة احتياطية من قاعدة البيانات</p>
                                    <button class="btn btn-primary" onclick="alert(currentLang === 'ar' ? 'سيتم تطوير هذه الميزة قريباً' : 'This feature will be developed soon')">
                                        <i class="fas fa-download"></i> 
                                        <span data-translate="create_backup">إنشاء نسخة</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-4">
                            <div class="tool-card">
                                <div class="card-body">
                                    <div class="tool-icon" style="color: var(--success);">
                                        <i class="fas fa-file-export"></i>
                                    </div>
                                    <h4 data-translate="export_data">تصدير البيانات</h4>
                                    <p data-translate="export_data_desc">تصدير المنتجات والمستخدمين</p>
                                    <button class="btn btn-success" onclick="alert(currentLang === 'ar' ? 'سيتم تطوير هذه الميزة قريباً' : 'This feature will be developed soon')">
                                        <i class="fas fa-file-export"></i> 
                                        <span data-translate="export">تصدير</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-4">
                            <div class="tool-card">
                                <div class="card-body">
                                    <div class="tool-icon" style="color: var(--warning);">
                                        <i class="fas fa-broom"></i>
                                    </div>
                                    <h4 data-translate="system_cleanup">تنظيف النظام</h4>
                                    <p data-translate="system_cleanup_desc">حذف الملفات والبيانات المؤقتة</p>
                                    <button class="btn btn-warning" onclick="alert(currentLang === 'ar' ? 'سيتم تطوير هذه الميزة قريباً' : 'This feature will be developed soon')">
                                        <i class="fas fa-broom"></i> 
                                        <span data-translate="cleanup">تنظيف</span>
                                    </button>
                                </div>
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
                "site_settings": "الإعدادات - الإدارة",
                "site_settings_desc": "تعديل الإعدادات العامة للموقع",
                "general_settings": "الإعدادات العامة",
                "advanced_tools": "أدوات متقدمة",
                
                // نماذج الإعدادات
                "site_title": "عنوان الموقع",
                "site_description": "وصف الموقع",
                "maintenance_mode": "وضع الصيانة",
                "maintenance_mode_desc": "عند التفعيل، سيظهر للمستخدمين رسالة صيانة",
                "disabled": "معطل",
                "enabled": "مفعل",
                
                // أزرار
                "save_settings": "حفظ الإعدادات",
                
                // أدوات متقدمة
                "backup": "نسخ احتياطي",
                "backup_desc": "إنشاء نسخة احتياطية من قاعدة البيانات",
                "create_backup": "إنشاء نسخة",
                "export_data": "تصدير البيانات",
                "export_data_desc": "تصدير المنتجات والمستخدمين",
                "export": "تصدير",
                "system_cleanup": "تنظيف النظام",
                "system_cleanup_desc": "حذف الملفات والبيانات المؤقتة",
                "cleanup": "تنظيف",
                
                // رسائل النجاح
                "settings_updated": "تم تحديث الإعدادات بنجاح"
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
                "site_settings": "Site Settings - Admin",
                "site_settings_desc": "Modify general site settings",
                "general_settings": "General Settings",
                "advanced_tools": "Advanced Tools",
                
                // نماذج الإعدادات
                "site_title": "Site Title",
                "site_description": "Site Description",
                "maintenance_mode": "Maintenance Mode",
                "maintenance_mode_desc": "When enabled, users will see a maintenance message",
                "disabled": "Disabled",
                "enabled": "Enabled",
                
                // أزرار
                "save_settings": "Save Settings",
                
                // أدوات متقدمة
                "backup": "Backup",
                "backup_desc": "Create a database backup",
                "create_backup": "Create Backup",
                "export_data": "Export Data",
                "export_data_desc": "Export products and users",
                "export": "Export",
                "system_cleanup": "System Cleanup",
                "system_cleanup_desc": "Delete temporary files and data",
                "cleanup": "Cleanup",
                
                // رسائل النجاح
                "settings_updated": "Settings updated successfully"
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

            // تحديث خيارات التحديد
            document.querySelectorAll('select option').forEach(option => {
                const key = option.getAttribute('data-translate');
                if (key && translations[lang][key]) {
                    option.textContent = translations[lang][key];
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
                document.title = 'الإعدادات - الإدارة';
            } else {
                document.documentElement.dir = 'ltr';
                document.documentElement.lang = 'en';
                document.title = 'Site Settings - Admin';
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