<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// جلب بيانات الاتصال
try {
    $stmt = $pdo->query("SELECT * FROM contact_info LIMIT 1");
    $contact_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$contact_info) {
        // إنشاء سجل افتراضي إذا لم يوجد
        $stmt = $pdo->prepare("INSERT INTO contact_info (id) VALUES (1)");
        $stmt->execute();
        $contact_info = ['id' => 1];
    }
} catch (PDOException $e) {
    $error = "خطأ في جلب بيانات الاتصال: " . $e->getMessage();
}

// معالجة تحديث البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_contact'])) {
    try {
        $stmt = $pdo->prepare("UPDATE contact_info SET 
            address = ?, address_en = ?, phone = ?, email = ?, 
            working_hours_ar = ?, working_hours_en = ?,
            social_facebook = ?, social_twitter = ?, 
            social_instagram = ?, social_whatsapp = ?
            WHERE id = ?");
        
        $stmt->execute([
            $_POST['address_ar'],
            $_POST['address_en'],
            $_POST['phone'],
            $_POST['email'],
            $_POST['working_hours_ar'],
            $_POST['working_hours_en'],
            $_POST['social_facebook'],
            $_POST['social_twitter'],
            $_POST['social_instagram'],
            $_POST['social_whatsapp'],
            $contact_info['id']
        ]);
        
        $success = "تم تحديث بيانات الاتصال بنجاح";
        // إعادة جلب البيانات المحدثة
        $stmt = $pdo->query("SELECT * FROM contact_info LIMIT 1");
        $contact_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $error = "خطأ في تحديث البيانات: " . $e->getMessage();
    }
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
        
        <main class="main-content">
            <?php include 'admin_navbar.php'; ?>
            
            <div class="contact-management">
                <div class="header">
                    <h1><i class="fas fa-address-book"></i> إدارة معلومات الاتصال</h1>
                    <p>تعديل معلومات الاتصال ووسائل التواصل الاجتماعي</p>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-map-marker-alt"></i> معلومات العنوان</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="address_ar">العنوان (عربي)</label>
                                        <textarea class="form-control" id="address_ar" name="address_ar" rows="3"><?php echo htmlspecialchars($contact_info['address'] ?? ''); ?></textarea>
                                        <small>استخدم Enter لفصل الأسطر</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="address_en">العنوان (إنجليزي)</label>
                                        <textarea class="form-control" id="address_en" name="address_en" rows="3"><?php echo htmlspecialchars($contact_info['address_en'] ?? ''); ?></textarea>
                                        <small>Use Enter to separate lines</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-phone"></i> معلومات الاتصال</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="phone">رقم الهاتف</label>
                                        <input type="text" class="form-control" id="phone" name="phone" 
                                               value="<?php echo htmlspecialchars($contact_info['phone'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="email">البريد الإلكتروني</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($contact_info['email'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-clock"></i> أوقات العمل</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="working_hours_ar">أوقات العمل (عربي)</label>
                                        <textarea class="form-control" id="working_hours_ar" name="working_hours_ar" rows="2"><?php echo htmlspecialchars($contact_info['working_hours_ar'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="working_hours_en">أوقات العمل (إنجليزي)</label>
                                        <textarea class="form-control" id="working_hours_en" name="working_hours_en" rows="2"><?php echo htmlspecialchars($contact_info['working_hours_en'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-share-alt"></i> وسائل التواصل الاجتماعي</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="social_facebook">فيسبوك (رابط)</label>
                                        <input type="url" class="form-control" id="social_facebook" name="social_facebook" 
                                               value="<?php echo htmlspecialchars($contact_info['social_facebook'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="social_twitter">تويتر (رابط)</label>
                                        <input type="url" class="form-control" id="social_twitter" name="social_twitter" 
                                               value="<?php echo htmlspecialchars($contact_info['social_twitter'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="social_instagram">انستغرام (رابط)</label>
                                        <input type="url" class="form-control" id="social_instagram" name="social_instagram" 
                                               value="<?php echo htmlspecialchars($contact_info['social_instagram'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="social_whatsapp">واتساب (رابط)</label>
                                        <input type="url" class="form-control" id="social_whatsapp" name="social_whatsapp" 
                                               value="<?php echo htmlspecialchars($contact_info['social_whatsapp'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <button type="submit" name="update_contact" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> حفظ التغييرات
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>