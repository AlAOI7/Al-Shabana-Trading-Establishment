<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// معالجة إضافة/تعديل الخدمات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_service'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $icon = $_POST['icon'];
        
        $stmt = $pdo->prepare("INSERT INTO services (title, description, icon) VALUES (?, ?, ?)");
        $stmt->execute([$title, $description, $icon]);
        
        header("Location: services.php?success=service_added");
        exit();
    }
    
    if (isset($_POST['update_service'])) {
        $service_id = $_POST['service_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $icon = $_POST['icon'];
        
        $stmt = $pdo->prepare("UPDATE services SET title = ?, description = ?, icon = ? WHERE id = ?");
        $stmt->execute([$title, $description, $icon, $service_id]);
        
        header("Location: services.php?success=service_updated");
        exit();
    }
}

// معالجة حذف الخدمة
if (isset($_GET['delete_service'])) {
    $service_id = $_GET['delete_service'];
    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
    $stmt->execute([$service_id]);
    header("Location: services.php?success=service_deleted");
    exit();
}

// جلب جميع الخدمات
$stmt = $pdo->query("SELECT * FROM services ORDER BY created_at DESC");
$services = $stmt->fetchAll();

// جلب خدمة للتعديل
$edit_service = null;
if (isset($_GET['edit_service'])) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$_GET['edit_service']]);
    $edit_service = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-translate="services_management">إدارة الخدمات</title>
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

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            color: white;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: #d32f2f;
            color: white;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
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

        /* تنسيقات الخدمات */
        .service-card {
            text-align: center;
            height: 100%;
            transition: var(--transition);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .service-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin: 1.5rem 0;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .service-actions {
            margin-top: 1rem;
            display: flex;
            justify-content: center;
            gap: 0.5rem;
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
            
            .service-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
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
                <h1><i class="fas fa-concierge-bell"></i> <span data-translate="services_management">إدارة الخدمات</span></h1>
                <p data-translate="services_management_desc">إضافة وتعديل وحذف الخدمات المقدمة</p>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> 
                    <span data-translate="<?php echo $_GET['success']; ?>">
                        <?php 
                        if ($_GET['success'] == 'service_added') echo 'تم إضافة الخدمة بنجاح';
                        elseif ($_GET['success'] == 'service_updated') echo 'تم تعديل الخدمة بنجاح';
                        elseif ($_GET['success'] == 'service_deleted') echo 'تم حذف الخدمة بنجاح';
                        ?>
                    </span>
                </div>
            <?php endif; ?>

            <!-- نموذج إضافة/تعديل الخدمة -->
            <div class="card">
                <div class="card-header">
                    <h3>
                        <i class="fas fa-<?php echo $edit_service ? 'edit' : 'plus'; ?>"></i> 
                        <span data-translate="<?php echo $edit_service ? 'edit_service' : 'add_new_service'; ?>">
                            <?php echo $edit_service ? 'تعديل الخدمة' : 'إضافة خدمة جديدة'; ?>
                        </span>
                    </h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?php if ($edit_service): ?>
                            <input type="hidden" name="service_id" value="<?php echo $edit_service['id']; ?>">
                            <input type="hidden" name="update_service" value="1">
                        <?php else: ?>
                            <input type="hidden" name="add_service" value="1">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="title" data-translate="service_title">عنوان الخدمة</label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="<?php echo $edit_service ? htmlspecialchars($edit_service['title']) : ''; ?>" 
                                           required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="icon" data-translate="service_icon">أيقونة الخدمة (Font Awesome)</label>
                                    <input type="text" class="form-control" id="icon" name="icon" 
                                           value="<?php echo $edit_service ? htmlspecialchars($edit_service['icon']) : ''; ?>" 
                                           placeholder="مثال: fas fa-home" required>
                                    <small style="color: #666;">
                                        <span data-translate="use_icons_from">استخدم أيقونات من</span> 
                                        <a href="https://fontawesome.com/icons" target="_blank">Font Awesome</a>
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description" data-translate="service_description">وصف الخدمة</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required><?php echo $edit_service ? htmlspecialchars($edit_service['description']) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-<?php echo $edit_service ? 'save' : 'plus'; ?>"></i>
                            <span data-translate="<?php echo $edit_service ? 'save_changes' : 'add_service'; ?>">
                                <?php echo $edit_service ? 'حفظ التعديلات' : 'إضافة الخدمة'; ?>
                            </span>
                        </button>
                        
                        <?php if ($edit_service): ?>
                            <a href="services.php" class="btn btn-secondary" data-translate="cancel">إلغاء</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- قائمة الخدمات -->
            <div class="card">
                <div class="card-header">
                    <h3>
                        <i class="fas fa-list"></i> 
                        <span data-translate="services_list">قائمة الخدمات</span> 
                        (<?php echo count($services); ?>)
                    </h3>
                </div>
                <div class="card-body">
                    <?php if (empty($services)): ?>
                        <div style="text-align: center; padding: 2rem; color: #666;">
                            <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p data-translate="no_services">لا توجد خدمات مضافة حتى الآن</p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($services as $service): ?>
                            <div class="col-4">
                                <div class="service-card">
                                    <div class="card-body">
                                        <div class="service-icon">
                                            <?php if ($service['icon']): ?>
                                                <i class="<?php echo $service['icon']; ?>"></i>
                                            <?php else: ?>
                                                <i class="fas fa-concierge-bell"></i>
                                            <?php endif; ?>
                                        </div>
                                        <h4><?php echo htmlspecialchars($service['title']); ?></h4>
                                        <p style="color: #666; font-size: 0.9rem;"><?php echo htmlspecialchars($service['description']); ?></p>
                                        <div class="service-actions">
                                            <a href="services.php?edit_service=<?php echo $service['id']; ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.8rem;">
                                                <i class="fas fa-edit"></i> <span data-translate="edit">تعديل</span>
                                            </a>
                                            <a href="services.php?delete_service=<?php echo $service['id']; ?>" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem;" 
                                               onclick="return confirm(currentLang === 'ar' ? 'هل أنت متأكد من حذف هذه الخدمة؟' : 'Are you sure you want to delete this service?')">
                                                <i class="fas fa-trash"></i> <span data-translate="delete">حذف</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
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
                "services_management": "إدارة الخدمات",
                "services_management_desc": "إضافة وتعديل وحذف الخدمات المقدمة",
                "add_new_service": "إضافة خدمة جديدة",
                "edit_service": "تعديل الخدمة",
                "services_list": "قائمة الخدمات",
                "no_services": "لا توجد خدمات مضافة حتى الآن",
                
                // النماذج
                "service_title": "عنوان الخدمة",
                "service_icon": "أيقونة الخدمة (Font Awesome)",
                "service_description": "وصف الخدمة",
                "use_icons_from": "استخدم أيقونات من",
                
                // الأزرار والإجراءات
                "save_changes": "حفظ التعديلات",
                "add_service": "إضافة الخدمة",
                "cancel": "إلغاء",
                "edit": "تعديل",
                "delete": "حذف",
                
                // رسائل النجاح
                "service_added": "تم إضافة الخدمة بنجاح",
                "service_updated": "تم تعديل الخدمة بنجاح",
                "service_deleted": "تم حذف الخدمة بنجاح"
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
                "services_management": "Services Management",
                "services_management_desc": "Add, edit and delete offered services",
                "add_new_service": "Add New Service",
                "edit_service": "Edit Service",
                "services_list": "Services List",
                "no_services": "No services added yet",
                
                // النماذج
                "service_title": "Service Title",
                "service_icon": "Service Icon (Font Awesome)",
                "service_description": "Service Description",
                "use_icons_from": "Use icons from",
                
                // الأزرار والإجراءات
                "save_changes": "Save Changes",
                "add_service": "Add Service",
                "cancel": "Cancel",
                "edit": "Edit",
                "delete": "Delete",
                
                // رسائل النجاح
                "service_added": "Service added successfully",
                "service_updated": "Service updated successfully",
                "service_deleted": "Service deleted successfully"
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

            // تحديث اتجاه الصفحة
            if (lang === 'ar') {
                document.documentElement.dir = 'rtl';
                document.documentElement.lang = 'ar';
                document.title = 'إدارة الخدمات';
            } else {
                document.documentElement.dir = 'ltr';
                document.documentElement.lang = 'en';
                document.title = 'Services Management';
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