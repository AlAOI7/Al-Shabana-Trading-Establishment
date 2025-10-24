<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// معالجة تحديث البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_about'])) {
    try {
        foreach ($_POST['sections'] as $id => $data) {
            $stmt = $pdo->prepare("UPDATE about_page SET 
                title_ar = ?, title_en = ?, content_ar = ?, content_en = ?, 
                image = ?, updated_at = NOW() WHERE id = ?");
            
            $stmt->execute([
                $data['title_ar'],
                $data['title_en'],
                $data['content_ar'],
                $data['content_en'],
                $data['image'],
                $id
            ]);
        }
        
        // معالجة رفع الصور
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['name'] as $section_id => $filename) {
                if (!empty($filename)) {
                    $target_dir = "../uploads/about/";
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    
                    $file_extension = pathinfo($filename, PATHINFO_EXTENSION);
                    $new_filename = "about_" . $section_id . "_" . time() . "." . $file_extension;
                    $target_file = $target_dir . $new_filename;
                    
                    if (move_uploaded_file($_FILES['images']['tmp_name'][$section_id], $target_file)) {
                        $stmt = $pdo->prepare("UPDATE about_page SET image = ? WHERE id = ?");
                        $stmt->execute([$new_filename, $section_id]);
                    }
                }
            }
        }
        
        $success = "تم تحديث بيانات من نحن بنجاح";
    } catch (PDOException $e) {
        $error = "خطأ في تحديث البيانات: " . $e->getMessage();
    }
}

// جلب بيانات صفحة من نحن
try {
    $stmt = $pdo->query("SELECT * FROM about_page ORDER BY display_order ASC");
    $about_sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $about_sections = [];
    $error = "خطأ في جلب البيانات: " . $e->getMessage();
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
        <style>
        .about-management {
            padding: 20px;
        }
        
        .section-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-left: 4px solid #2c5aa0;
        }
        
        .section-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .section-title {
            color: #2c5aa0;
            font-size: 1.4rem;
            margin: 0;
        }
        
        .section-type {
            background: #f8b500;
            color: white;
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        
        .language-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .language-tab {
            padding: 0.5rem 1rem;
            background: none;
            border: none;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            color: #666;
        }
        
        .language-tab.active {
            color: #2c5aa0;
            border-bottom-color: #2c5aa0;
        }
        
        .language-content {
            display: none;
        }
        
        .language-content.active {
            display: block;
        }
        
        .image-upload {
            border: 2px dashed #ddd;
            padding: 2rem;
            text-align: center;
            border-radius: 10px;
            margin-top: 1rem;
        }
        
        .current-image {
            max-width: 200px;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <?php include 'admin_navbar.php'; ?>
            
            <div class="about-management">
                <div class="header">
                    <h1><i class="fas fa-info-circle"></i> إدارة صفحة من نحن</h1>
                    <p>تعديل محتوى صفحة من نحن والترجمة</p>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <?php foreach ($about_sections as $section): ?>
                        <div class="section-card">
                            <div class="section-header">
                                <h3 class="section-title">
                                    <?php 
                                    $section_names = [
                                        'intro' => 'المقدمة',
                                        'story' => 'القصة',
                                        'official_details' => 'البيانات الرسمية',
                                        'mission' => 'الرسالة',
                                        'vision' => 'الرؤية',
                                        'values' => 'القيم'
                                    ];
                                    echo $section_names[$section['section_type']] ?? $section['section_type'];
                                    ?>
                                </h3>
                                <span class="section-type"><?php echo $section['section_type']; ?></span>
                            </div>

                            <!-- تبويبات اللغة -->
                            <div class="language-tabs">
                                <button type="button" class="language-tab active" data-lang="ar">العربية</button>
                                <button type="button" class="language-tab" data-lang="en">English</button>
                            </div>

                            <!-- المحتوى العربي -->
                            <div class="language-content active" data-lang="ar">
                                <div class="form-group">
                                    <label for="title_ar_<?php echo $section['id']; ?>">العنوان (عربي)</label>
                                    <input type="text" class="form-control" id="title_ar_<?php echo $section['id']; ?>" 
                                           name="sections[<?php echo $section['id']; ?>][title_ar]" 
                                           value="<?php echo htmlspecialchars($section['title_ar']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="content_ar_<?php echo $section['id']; ?>">المحتوى (عربي)</label>
                                    <textarea class="form-control" id="content_ar_<?php echo $section['id']; ?>" 
                                              name="sections[<?php echo $section['id']; ?>][content_ar]" 
                                              rows="4"><?php echo htmlspecialchars($section['content_ar']); ?></textarea>
                                </div>
                            </div>

                            <!-- المحتوى الإنجليزي -->
                            <div class="language-content" data-lang="en">
                                <div class="form-group">
                                    <label for="title_en_<?php echo $section['id']; ?>">Title (English)</label>
                                    <input type="text" class="form-control" id="title_en_<?php echo $section['id']; ?>" 
                                           name="sections[<?php echo $section['id']; ?>][title_en]" 
                                           value="<?php echo htmlspecialchars($section['title_en']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="content_en_<?php echo $section['id']; ?>">Content (English)</label>
                                    <textarea class="form-control" id="content_en_<?php echo $section['id']; ?>" 
                                              name="sections[<?php echo $section['id']; ?>][content_en]" 
                                              rows="4"><?php echo htmlspecialchars($section['content_en']); ?></textarea>
                                </div>
                            </div>

                            <!-- رفع الصور (للقصة فقط) -->
                            <?php if ($section['section_type'] === 'story'): ?>
                                <div class="form-group">
                                    <label>صورة القسم</label>
                                    <?php if (!empty($section['image'])): ?>
                                        <div>
                                            <img src="../uploads/about/<?php echo htmlspecialchars($section['image']); ?>" 
                                                 alt="الصورة الحالية" class="current-image">
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" name="images[<?php echo $section['id']; ?>]" 
                                           class="form-control" accept="image/*">
                                    <input type="hidden" name="sections[<?php echo $section['id']; ?>][image]" 
                                           value="<?php echo htmlspecialchars($section['image']); ?>">
                                </div>
                            <?php else: ?>
                                <input type="hidden" name="sections[<?php echo $section['id']; ?>][image]" 
                                       value="<?php echo htmlspecialchars($section['image']); ?>">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <div class="card">
                        <div class="card-body">
                            <button type="submit" name="update_about" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> حفظ جميع التغييرات
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // إدارة تبويبات اللغة
        document.querySelectorAll('.language-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const sectionCard = this.closest('.section-card');
                const lang = this.getAttribute('data-lang');
                
                // تحديد التبويب النشط
                sectionCard.querySelectorAll('.language-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // إظهار المحتوى المناسب
                sectionCard.querySelectorAll('.language-content').forEach(content => {
                    content.classList.remove('active');
                    if (content.getAttribute('data-lang') === lang) {
                        content.classList.add('active');
                    }
                });
            });
        });
    </script>
</body>
</html>