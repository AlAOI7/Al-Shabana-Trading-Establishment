<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// جلب جميع الإعدادات
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value, setting_type FROM settings");
    $settings_data = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings_data[$row['setting_key']] = $row['setting_value'];
    }
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
    <title>الإعدادات - الإدارة</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
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

        /* تبويبات */
        .tabs {
            display: flex;
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            margin-bottom: 1.5rem;
            overflow-x: auto;
        }

        .tab-btn {
            padding: 1rem 1.5rem;
            background: none;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 500;
            color: #64748b;
            border-bottom: 3px solid transparent;
            white-space: nowrap;
        }

        .tab-btn:hover {
            color: var(--primary);
            background: #f8f9fa;
        }

        .tab-btn.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
            background: #f8f9fa;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
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

        .col-12 {
            flex: 0 0 100%;
            max-width: 100%;
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
            
            .tabs {
                flex-direction: column;
            }
            
            .tab-btn {
                border-bottom: 1px solid #e2e8f0;
                border-left: 3px solid transparent;
            }
            
            .tab-btn.active {
                border-left-color: var(--primary);
            }
        }
    </style>
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

            <!-- تبويبات الإعدادات -->
            <div class="tabs">
                <button class="tab-btn active" data-tab="general">
                    <i class="fas fa-cog"></i> البيانات العامة
                </button>
                     <button class="tab-btn" data-tab="hero">
            <i class="fas fa-home"></i> الهيرو المتحرك
        </button>
                <button class="tab-btn" data-tab="header">
                    <i class="fas fa-heading"></i> الهيدر والشعار
                </button>
                <button class="tab-btn" data-tab="about">
                    <i class="fas fa-info-circle"></i> من نحن
                </button>
                <button class="tab-btn" data-tab="stats">
                    <i class="fas fa-chart-bar"></i> الإحصائيات
                </button>
                <button class="tab-btn" data-tab="products">
                    <i class="fas fa-boxes"></i> المنتجات
                </button>
                <button class="tab-btn" data-tab="brands">
                    <i class="fas fa-tags"></i> العلامات التجارية
                </button>
                <button class="tab-btn" data-tab="contact">
                    <i class="fas fa-phone"></i> اتصل بنا
                </button>
                <button class="tab-btn" data-tab="footer">
                    <i class="fas fa-shoe-prints"></i> الفوتر
                </button>
            </div>

            <form method="POST">
                <!-- تبويب البيانات العامة -->
                <div class="tab-content active" id="general-tab">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-sliders-h"></i> الإعدادات العامة</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="site_title">عنوان الموقع</label>
                                        <input type="text" class="form-control" id="site_title" name="settings[site_title]" 
                                               value="<?php echo htmlspecialchars($settings_data['site_title'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="maintenance_mode">وضع الصيانة</label>
                                        <select class="form-control" id="maintenance_mode" name="settings[maintenance_mode]">
                                            <option value="0" <?php echo ($settings_data['maintenance_mode'] ?? '0') == '0' ? 'selected' : ''; ?>>معطل</option>
                                            <option value="1" <?php echo ($settings_data['maintenance_mode'] ?? '0') == '1' ? 'selected' : ''; ?>>مفعل</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        <!-- تبويب الهيرو المتحرك -->
        <div class="tab-content" id="hero-tab">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-home"></i> إعدادات قسم الهيرو المتحرك</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="hero_title_main_ar">العنوان الرئيسي (عربي)</label>
                                <input type="text" class="form-control" id="hero_title_main_ar" name="settings[hero_title_main_ar]" 
                                       value="<?php echo htmlspecialchars($settings_data['hero_title_main_ar'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="hero_title_main_en">العنوان الرئيسي (إنجليزي)</label>
                                <input type="text" class="form-control" id="hero_title_main_en" name="settings[hero_title_main_en]" 
                                       value="<?php echo htmlspecialchars($settings_data['hero_title_main_en'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="hero_title_sub_ar">العنوان الفرعي (عربي)</label>
                                <input type="text" class="form-control" id="hero_title_sub_ar" name="settings[hero_title_sub_ar]" 
                                       value="<?php echo htmlspecialchars($settings_data['hero_title_sub_ar'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="hero_title_sub_en">العنوان الفرعي (إنجليزي)</label>
                                <input type="text" class="form-control" id="hero_title_sub_en" name="settings[hero_title_sub_en]" 
                                       value="<?php echo htmlspecialchars($settings_data['hero_title_sub_en'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="hero_subtitle_ar">الشعار (عربي)</label>
                                <input type="text" class="form-control" id="hero_subtitle_ar" name="settings[hero_subtitle_ar]" 
                                       value="<?php echo htmlspecialchars($settings_data['hero_subtitle_ar'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="hero_subtitle_en">الشعار (إنجليزي)</label>
                                <input type="text" class="form-control" id="hero_subtitle_en" name="settings[hero_subtitle_en]" 
                                       value="<?php echo htmlspecialchars($settings_data['hero_subtitle_en'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="hero_description_ar">الوصف (عربي)</label>
                        <textarea class="form-control" id="hero_description_ar" name="settings[hero_description_ar]" rows="3"><?php echo htmlspecialchars($settings_data['hero_description_ar'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="hero_description_en">الوصف (إنجليزي)</label>
                        <textarea class="form-control" id="hero_description_en" name="settings[hero_description_en]" rows="3"><?php echo htmlspecialchars($settings_data['hero_description_en'] ?? ''); ?></textarea>
                    </div>
                    
                    <h4 class="section-subtitle">أزرار القسم</h4>
                    <div class="row">
                        <div class="col-4">
                            <div class="form-group">
                                <label for="hero_button_products_ar">زر المنتجات (عربي)</label>
                                <input type="text" class="form-control" id="hero_button_products_ar" name="settings[hero_button_products_ar]" 
                                       value="<?php echo htmlspecialchars($settings_data['hero_button_products_ar'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <label for="hero_button_about_ar">زر من نحن (عربي)</label>
                                <input type="text" class="form-control" id="hero_button_about_ar" name="settings[hero_button_about_ar]" 
                                       value="<?php echo htmlspecialchars($settings_data['hero_button_about_ar'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <label for="hero_button_contact_ar">زر اتصل بنا (عربي)</label>
                                <input type="text" class="form-control" id="hero_button_contact_ar" name="settings[hero_button_contact_ar]" 
                                       value="<?php echo htmlspecialchars($settings_data['hero_button_contact_ar'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-4">
                            <div class="form-group">
                                <label for="hero_button_products_en">زر المنتجات (إنجليزي)</label>
                                <input type="text" class="form-control" id="hero_button_products_en" name="settings[hero_button_products_en]" 
                                       value="<?php echo htmlspecialchars($settings_data['hero_button_products_en'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <label for="hero_button_about_en">زر من نحن (إنجليزي)</label>
                                <input type="text" class="form-control" id="hero_button_about_en" name="settings[hero_button_about_en]" 
                                       value="<?php echo htmlspecialchars($settings_data['hero_button_about_en'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <label for="hero_button_contact_en">زر اتصل بنا (إنجليزي)</label>
                                <input type="text" class="form-control" id="hero_button_contact_en" name="settings[hero_button_contact_en]" 
                                       value="<?php echo htmlspecialchars($settings_data['hero_button_contact_en'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <h4 class="section-subtitle">فئات المنتجات</h4>
                    <div class="row">
                        <div class="col-3">
                            <div class="form-group">
                                <label for="hero_category_energy_ar">فئة الطاقة (عربي)</label>
                                <input type="text" class="form-control" id="hero_category_energy_ar" name="settings[hero_category_energy_ar]" 
                                       value="<?php echo htmlspecialchars($settings_data['hero_category_energy_ar'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-group">
                                <label for="hero_category_purification_ar">فئة التنقية (عربي)</label>
                                <input type="text" class="form-control" id="hero_category_purification_ar" name="settings[hero_category_purification_ar]" 
                                       value="<?php echo htmlspecialchars($settings_data['hero_category_purification_ar'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-group">
                                <label for="hero_category_cleaning_ar">فئة التنظيف (عربي)</label>
                                <input type="text" class="form-control" id="hero_category_cleaning_ar" name="settings[hero_category_cleaning_ar]" 
                                       value="<?php echo htmlspecialchars($settings_data['hero_category_cleaning_ar'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-group">
                                <label for="hero_category_supplies_ar">فئة المستلزمات (عربي)</label>
                                <input type="text" class="form-control" id="hero_category_supplies_ar" name="settings[hero_category_supplies_ar]" 
                                       value="<?php echo htmlspecialchars($settings_data['hero_category_supplies_ar'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-3">
                            <div class="form-group">
                                <label for="hero_category_energy_en">فئة الطاقة (إنجليزي)</label>
                                <input type="text" class="form-control" id="hero_category_energy_en" name="settings[hero_category_energy_en]" 
                                       value="<?php echo htmlspecialchars($settings_data['hero_category_energy_en'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-group">
                                <label for="hero_category_purification_en">فئة التنقية (إنجليزي)</label>
                                <input type="text" class="form-control" id="hero_category_purification_en" name="settings[hero_category_purification_en]" 
                                       value="<?php echo htmlspecialchars($settings_data['hero_category_purification_en'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-group">
                                <label for="hero_category_cleaning_en">فئة التنظيف (إنجليزي)</label>
                                <input type="text" class="form-control" id="hero_category_cleaning_en" name="settings[hero_category_cleaning_en]" 
                                       value="<?php echo htmlspecialchars($settings_data['hero_category_cleaning_en'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-group">
                                <label for="hero_category_supplies_en">فئة المستلزمات (إنجليزي)</label>
                                <input type="text" class="form-control" id="hero_category_supplies_en" name="settings[hero_category_supplies_en]" 
                                       value="<?php echo htmlspecialchars($settings_data['hero_category_supplies_en'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
                <!-- تبويب الهيدر والشعار -->
                <div class="tab-content" id="header-tab">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-heading"></i> إعدادات الهيدر والشعار</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="company_name_ar">اسم الشركة (عربي)</label>
                                        <input type="text" class="form-control" id="company_name_ar" name="settings[company_name_ar]" 
                                               value="<?php echo htmlspecialchars($settings_data['company_name_ar'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="company_name_en">اسم الشركة (إنجليزي)</label>
                                        <input type="text" class="form-control" id="company_name_en" name="settings[company_name_en]" 
                                               value="<?php echo htmlspecialchars($settings_data['company_name_en'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="tagline_ar">الشعار (عربي)</label>
                                        <input type="text" class="form-control" id="tagline_ar" name="settings[tagline_ar]" 
                                               value="<?php echo htmlspecialchars($settings_data['tagline_ar'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="tagline_en">الشعار (إنجليزي)</label>
                                        <input type="text" class="form-control" id="tagline_en" name="settings[tagline_en]" 
                                               value="<?php echo htmlspecialchars($settings_data['tagline_en'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="header_logo_text_ar">نص الشعار (عربي)</label>
                                        <input type="text" class="form-control" id="header_logo_text_ar" name="settings[header_logo_text_ar]" 
                                               value="<?php echo htmlspecialchars($settings_data['header_logo_text_ar'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="header_logo_subtext">نص الشعار الفرعي</label>
                                        <input type="text" class="form-control" id="header_logo_subtext" name="settings[header_logo_subtext]" 
                                               value="<?php echo htmlspecialchars($settings_data['header_logo_subtext'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="welcome_text_ar">نص الترحيب (عربي)</label>
                                        <input type="text" class="form-control" id="welcome_text_ar" name="settings[welcome_text_ar]" 
                                               value="<?php echo htmlspecialchars($settings_data['welcome_text_ar'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="header_phone_display">رقم الهاتف (عرض)</label>
                                        <input type="text" class="form-control" id="header_phone_display" name="settings[header_phone_display]" 
                                               value="<?php echo htmlspecialchars($settings_data['header_phone_display'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- تبويب من نحن -->
                <div class="tab-content" id="about-tab">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-info-circle"></i> إعدادات قسم من نحن</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="about_title_ar">عنوان القسم (عربي)</label>
                                        <input type="text" class="form-control" id="about_title_ar" name="settings[about_title_ar]" 
                                               value="<?php echo htmlspecialchars($settings_data['about_title_ar'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="about_title_en">عنوان القسم (إنجليزي)</label>
                                        <input type="text" class="form-control" id="about_title_en" name="settings[about_title_en]" 
                                               value="<?php echo htmlspecialchars($settings_data['about_title_en'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="about_description_ar">وصف الشركة (عربي)</label>
                                <textarea class="form-control" id="about_description_ar" name="settings[about_description_ar]" rows="4"><?php echo htmlspecialchars($settings_data['about_description_ar'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="about_description_en">وصف الشركة (إنجليزي)</label>
                                <textarea class="form-control" id="about_description_en" name="settings[about_description_en]" rows="4"><?php echo htmlspecialchars($settings_data['about_description_en'] ?? ''); ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="mission_title_ar">عنوان الرسالة (عربي)</label>
                                        <input type="text" class="form-control" id="mission_title_ar" name="settings[mission_title_ar]" 
                                               value="<?php echo htmlspecialchars($settings_data['mission_title_ar'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="mission_description_ar">نص الرسالة (عربي)</label>
                                        <textarea class="form-control" id="mission_description_ar" name="settings[mission_description_ar]" rows="3"><?php echo htmlspecialchars($settings_data['mission_description_ar'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="vision_title_ar">عنوان الرؤية (عربي)</label>
                                        <input type="text" class="form-control" id="vision_title_ar" name="settings[vision_title_ar]" 
                                               value="<?php echo htmlspecialchars($settings_data['vision_title_ar'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="vision_description_ar">نص الرؤية (عربي)</label>
                                        <textarea class="form-control" id="vision_description_ar" name="settings[vision_description_ar]" rows="3"><?php echo htmlspecialchars($settings_data['vision_description_ar'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="values_title_ar">عنوان القيم (عربي)</label>
                                        <input type="text" class="form-control" id="values_title_ar" name="settings[values_title_ar]" 
                                               value="<?php echo htmlspecialchars($settings_data['values_title_ar'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="values_description_ar">نص القيم (عربي)</label>
                                        <textarea class="form-control" id="values_description_ar" name="settings[values_description_ar]" rows="3"><?php echo htmlspecialchars($settings_data['values_description_ar'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- تبويب الإحصائيات -->
                <div class="tab-content" id="stats-tab">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-chart-bar"></i> إعدادات الإحصائيات</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="stats_title_ar">عنوان القسم (عربي)</label>
                                        <input type="text" class="form-control" id="stats_title_ar" name="settings[stats_title_ar]" 
                                               value="<?php echo htmlspecialchars($settings_data['stats_title_ar'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="stats_title_en">عنوان القسم (إنجليزي)</label>
                                        <input type="text" class="form-control" id="stats_title_en" name="settings[stats_title_en]" 
                                               value="<?php echo htmlspecialchars($settings_data['stats_title_en'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-3">
                                    <div class="form-group">
                                        <label for="products_count">عدد المنتجات</label>
                                        <input type="number" class="form-control" id="products_count" name="settings[products_count]" 
                                               value="<?php echo htmlspecialchars($settings_data['products_count'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="products_label_ar">تسمية المنتجات (عربي)</label>
                                        <input type="text" class="form-control" id="products_label_ar" name="settings[products_label_ar]" 
                                               value="<?php echo htmlspecialchars($settings_data['products_label_ar'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        <label for="customers_count">عدد العملاء</label>
                                        <input type="number" class="form-control" id="customers_count" name="settings[customers_count]" 
                                               value="<?php echo htmlspecialchars($settings_data['customers_count'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="customers_label_ar">تسمية العملاء (عربي)</label>
                                        <input type="text" class="form-control" id="customers_label_ar" name="settings[customers_label_ar]" 
                                               value="<?php echo htmlspecialchars($settings_data['customers_label_ar'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        <label for="experience_count">سنوات الخبرة</label>
                                        <input type="number" class="form-control" id="experience_count" name="settings[experience_count]" 
                                               value="<?php echo htmlspecialchars($settings_data['experience_count'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="experience_label_ar">تسمية الخبرة (عربي)</label>
                                        <input type="text" class="form-control" id="experience_label_ar" name="settings[experience_label_ar]" 
                                               value="<?php echo htmlspecialchars($settings_data['experience_label_ar'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        <label for="deliveries_count">عدد التوصيلات</label>
                                        <input type="number" class="form-control" id="deliveries_count" name="settings[deliveries_count]" 
                                               value="<?php echo htmlspecialchars($settings_data['deliveries_count'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="deliveries_label_ar">تسمية التوصيل (عربي)</label>
                                        <input type="text" class="form-control" id="deliveries_label_ar" name="settings[deliveries_label_ar]" 
                                               value="<?php echo htmlspecialchars($settings_data['deliveries_label_ar'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- تبويب المنتجات -->
                <div class="tab-content" id="products-tab">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-boxes"></i> إعدادات المنتجات</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="products_title_ar">عنوان القسم (عربي)</label>
                                        <input type="text" class="form-control" id="products_title_ar" name="settings[products_title_ar]" 
                                               value="<?php echo htmlspecialchars($settings_data['products_title_ar'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="products_title_en">عنوان القسم (إنجليزي)</label>
                                        <input type="text" class="form-control" id="products_title_en" name="settings[products_title_en]" 
                                               value="<?php echo htmlspecialchars($settings_data['products_title_en'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="filter_all_ar">زر الكل (عربي)</label>
                                        <input type="text" class="form-control" id="filter_all_ar" name="settings[filter_all_ar]" 
                                               value="<?php echo htmlspecialchars($settings_data['filter_all_ar'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="filter_cleaning_ar">زر التنظيف (عربي)</label>
                                        <input type="text" class="form-control" id="filter_cleaning_ar" name="settings[filter_cleaning_ar]" 
                                               value="<?php echo htmlspecialchars($settings_data['filter_cleaning_ar'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="filter_energy_ar">زر الطاقة (عربي)</label>
                                        <input type="text" class="form-control" id="filter_energy_ar" name="settings[filter_energy_ar]" 
                                               value="<?php echo htmlspecialchars($settings_data['filter_energy_ar'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- تبويب العلامات التجارية -->
                <div class="tab-content" id="brands-tab">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-tags"></i> إعدادات العلامات التجارية</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="brands_title_ar">عنوان القسم (عربي)</label>
                                        <input type="text" class="form-control" id="brands_title_ar" name="settings[brands_title_ar]" 
                                               value="<?php echo htmlspecialchars($settings_data['brands_title_ar'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="brands_title_en">عنوان القسم (إنجليزي)</label>
                                        <input type="text" class="form-control" id="brands_title_en" name="settings[brands_title_en]" 
                                               value="<?php echo htmlspecialchars($settings_data['brands_title_en'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-3">
                                    <div class="form-group">
                                        <label for="brand_double_class">Double Class</label>
                                        <input type="text" class="form-control" id="brand_double_class" name="settings[brand_double_class]" 
                                               value="<?php echo htmlspecialchars($settings_data['brand_double_class'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        <label for="brand_gator">Gator</label>
                                        <input type="text" class="form-control" id="brand_gator" name="settings[brand_gator]" 
                                               value="<?php echo htmlspecialchars($settings_data['brand_gator'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        <label for="brand_premium">Premium</label>
                                        <input type="text" class="form-control" id="brand_premium" name="settings[brand_premium]" 
                                               value="<?php echo htmlspecialchars($settings_data['brand_premium'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        <label for="brand_shield">Shield</label>
                                        <input type="text" class="form-control" id="brand_shield" name="settings[brand_shield]" 
                                               value="<?php echo htmlspecialchars($settings_data['brand_shield'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- تبويب اتصل بنا -->
                <div class="tab-content" id="contact-tab">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-phone"></i> إعدادات اتصل بنا</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="contact_title_ar">عنوان القسم (عربي)</label>
                                        <input type="text" class="form-control" id="contact_title_ar" name="settings[contact_title_ar]" 
                                               value="<?php echo htmlspecialchars($settings_data['contact_title_ar'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="contact_title_en">عنوان القسم (إنجليزي)</label>
                                        <input type="text" class="form-control" id="contact_title_en" name="settings[contact_title_en]" 
                                               value="<?php echo htmlspecialchars($settings_data['contact_title_en'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="address_ar">العنوان (عربي)</label>
                                        <input type="text" class="form-control" id="address_ar" name="settings[address_ar]" 
                                               value="<?php echo htmlspecialchars($settings_data['address_ar'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="phone">رقم الهاتف</label>
                                        <input type="text" class="form-control" id="phone" name="settings[phone]" 
                                               value="<?php echo htmlspecialchars($settings_data['phone'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="email">البريد الإلكتروني</label>
                                        <input type="email" class="form-control" id="email" name="settings[email]" 
                                               value="<?php echo htmlspecialchars($settings_data['email'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="contact_top_phone_display">رقم الهاتف العلوي</label>
                                        <input type="text" class="form-control" id="contact_top_phone_display" name="settings[contact_top_phone_display]" 
                                               value="<?php echo htmlspecialchars($settings_data['contact_top_phone_display'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="working_hours_ar">أوقات العمل (عربي)</label>
                                <input type="text" class="form-control" id="working_hours_ar" name="settings[working_hours_ar]" 
                                       value="<?php echo htmlspecialchars($settings_data['working_hours_ar'] ?? ''); ?>">
                            </div>
                            <div class="row">
                                <div class="col-3">
                                    <div class="form-group">
                                        <label for="form_name_ar">حقل الاسم (عربي)</label>
                                        <input type="text" class="form-control" id="form_name_ar" name="settings[form_name_ar]" 
                                               value="<?php echo htmlspecialchars($settings_data['form_name_ar'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        <label for="form_email_ar">حقل البريد (عربي)</label>
                                        <input type="text" class="form-control" id="form_email_ar" name="settings[form_email_ar]" 
                                               value="<?php echo htmlspecialchars($settings_data['form_email_ar'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        <label for="form_phone_ar">حقل الهاتف (عربي)</label>
                                        <input type="text" class="form-control" id="form_phone_ar" name="settings[form_phone_ar]" 
                                               value="<?php echo htmlspecialchars($settings_data['form_phone_ar'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        <label for="form_message_ar">حقل الرسالة (عربي)</label>
                                        <input type="text" class="form-control" id="form_message_ar" name="settings[form_message_ar]" 
                                               value="<?php echo htmlspecialchars($settings_data['form_message_ar'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="submit_btn_ar">زر الإرسال (عربي)</label>
                                <input type="text" class="form-control" id="submit_btn_ar" name="settings[submit_btn_ar]" 
                                       value="<?php echo htmlspecialchars($settings_data['submit_btn_ar'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- تبويب الفوتر -->
                <div class="tab-content" id="footer-tab">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-shoe-prints"></i> إعدادات الفوتر</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="footer_logo_text_ar">نص الشعار (عربي)</label>
                                        <input type="text" class="form-control" id="footer_logo_text_ar" name="settings[footer_logo_text_ar]" 
                                               value="<?php echo htmlspecialchars($settings_data['footer_logo_text_ar'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="footer_description_ar">وصف الفوتر (عربي)</label>
                                        <input type="text" class="form-control" id="footer_description_ar" name="settings[footer_description_ar]" 
                                               value="<?php echo htmlspecialchars($settings_data['footer_description_ar'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="copyright_ar">حقوق النشر (عربي)</label>
                                <textarea class="form-control" id="copyright_ar" name="settings[copyright_ar]" rows="2"><?php echo htmlspecialchars($settings_data['copyright_ar'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- زر الحفظ -->
                <div class="card">
                    <div class="card-body">
                        <button type="submit" name="update_settings" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> حفظ جميع الإعدادات
                        </button>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <!-- زر الترجمة العائم -->
    <button class="translate-btn" id="translateBtn">
        <i class="fas fa-language"></i>
    </button>

    <script>
        // إدارة التبويبات
        document.querySelectorAll('.tab-btn').forEach(button => {
            button.addEventListener('click', () => {
                // إزالة النشاط من جميع الأزرار والمحتويات
                document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                
                // إضافة النشاط للزر والمحتوى المحدد
                button.classList.add('active');
                const tabId = button.getAttribute('data-tab');
                document.getElementById(`${tabId}-tab`).classList.add('active');
            });
        });

        // نصوص الترجمة
        const translations = {
            ar: {
                "site_settings": "الإعدادات - الإدارة",
                "site_settings_desc": "تعديل الإعدادات العامة للموقع",
                "settings_updated": "تم تحديث الإعدادات بنجاح"
            },
            en: {
                "site_settings": "Site Settings - Admin",
                "site_settings_desc": "Modify general site settings",
                "settings_updated": "Settings updated successfully"
            }
        };

        let currentLang = localStorage.getItem('language') || 'ar';

        function applyLanguage(lang) {
            document.querySelectorAll('[data-translate]').forEach(element => {
                const key = element.getAttribute('data-translate');
                if (translations[lang][key]) {
                    element.textContent = translations[lang][key];
                }
            });

            if (lang === 'ar') {
                document.documentElement.dir = 'rtl';
                document.documentElement.lang = 'ar';
                document.title = 'الإعدادات - الإدارة';
            } else {
                document.documentElement.dir = 'ltr';
                document.documentElement.lang = 'en';
                document.title = 'Site Settings - Admin';
            }

            localStorage.setItem('language', lang);
            currentLang = lang;
        }

        document.getElementById('translateBtn').addEventListener('click', function() {
            const newLang = currentLang === 'ar' ? 'en' : 'ar';
            applyLanguage(newLang);
        });

        document.addEventListener('DOMContentLoaded', function() {
            applyLanguage(currentLang);
        });
    </script>
</body>
</html>