
<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// معالجة إضافة سؤال جديد
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_faq'])) {
    $question_ar = $_POST['question_ar'];
    $question_en = $_POST['question_en'];
    $answer_ar = $_POST['answer_ar'];
    $answer_en = $_POST['answer_en'];
    $display_order = $_POST['display_order'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO faqs (question_ar, question_en, answer_ar, answer_en, display_order) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$question_ar, $question_en, $answer_ar, $answer_en, $display_order]);
        $success = "تم إضافة السؤال بنجاح";
    } catch (PDOException $e) {
        $error = "خطأ في إضافة السؤال: " . $e->getMessage();
    }
}

// معالجة تحديث سؤال
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_faq'])) {
    $id = $_POST['faq_id'];
    $question_ar = $_POST['question_ar'];
    $question_en = $_POST['question_en'];
    $answer_ar = $_POST['answer_ar'];
    $answer_en = $_POST['answer_en'];
    $display_order = $_POST['display_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    try {
        $stmt = $pdo->prepare("UPDATE faqs SET question_ar = ?, question_en = ?, answer_ar = ?, answer_en = ?, display_order = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$question_ar, $question_en, $answer_ar, $answer_en, $display_order, $is_active, $id]);
        $success = "تم تحديث السؤال بنجاح";
    } catch (PDOException $e) {
        $error = "خطأ في تحديث السؤال: " . $e->getMessage();
    }
}

// معالجة حذف سؤال
if (isset($_GET['delete_id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM faqs WHERE id = ?");
        $stmt->execute([$_GET['delete_id']]);
        $success = "تم حذف السؤال بنجاح";
    } catch (PDOException $e) {
        $error = "خطأ في حذف السؤال: " . $e->getMessage();
    }
}

// جلب جميع الأسئلة
try {
    $stmt = $pdo->query("SELECT * FROM faqs ORDER BY display_order ASC, created_at DESC");
    $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $faqs = [];
    $error = "خطأ في جلب الأسئلة: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الأسئلة الشائعة</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .header h1 {
            color: var(--dark);
            font-weight: 600;
        }

        .date-display {
            color: #64748b;
            font-size: 0.9rem;
        }

        /* بطاقات الإحصائيات */
        .row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: var(--transition);
            border: none;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
        }

        .stat-card {
            display: flex;
            align-items: center;
            padding: 1.5rem;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 1rem;
            font-size: 1.5rem;
            color: white;
        }

        .stat-content {
            flex: 1;
        }

        .stat-content h3 {
            font-size: 1.8rem;
            margin-bottom: 0.2rem;
            font-weight: 700;
        }

        .stat-content p {
            color: #64748b;
            font-size: 0.9rem;
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

        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }

        /* الجداول */
        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th, .table td {
            padding: 0.8rem 1rem;
            text-align: right;
            border-bottom: 1px solid #e2e8f0;
        }

        .table th {
            background: #f8fafc;
            font-weight: 600;
            color: #475569;
        }

        .table tr:hover {
            background: #f8fafc;
        }

        .badge {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        /* تخطيط الشبكة */
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        /* القائمة المنسدلة للمستخدم */
        .user-menu {
            position: relative;
            display: inline-block;
        }

        .user-dropdown {
            display: none;
            position: absolute;
            left: 0;
            top: 100%;
            background: white;
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border-radius: 8px;
            z-index: 1;
            overflow: hidden;
        }

        .user-menu:hover .user-dropdown {
            display: block;
        }

        .user-dropdown a {
            display: block;
            padding: 0.8rem 1rem;
            text-decoration: none;
            color: #333;
            transition: var(--transition);
        }

        .user-dropdown a:hover {
            background: #f5f7fb;
        }

        /* تذييل الصفحة */
        .footer {
            text-align: center;
            padding: 1.5rem;
            margin-top: 2rem;
            color: #64748b;
            font-size: 0.9rem;
            border-top: 1px solid #e2e8f0;
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
            
            .row {
                grid-template-columns: 1fr;
            }
            
            .grid-2, .grid-3 {
                grid-template-columns: 1fr;
            }
            
            .stat-card {
                flex-direction: column;
                text-align: center;
            }
            
            .stat-icon {
                margin-left: 0;
                margin-bottom: 1rem;
            }
        }

        /* تأثيرات إضافية */
        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(67, 97, 238, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(67, 97, 238, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(67, 97, 238, 0);
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* تنسيقات الرسوم البيانية */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
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

        body[dir="ltr"] .stat-icon {
            margin-left: 0;
            margin-right: 1rem;
        }

        body[dir="ltr"] .table th, 
        body[dir="ltr"] .table td {
            text-align: left;
        }

        body[dir="ltr"] .user-dropdown {
            left: auto;
            right: 0;
        }
    </style>
<body>
    <div class="dashboard">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <?php include 'admin_navbar.php'; ?>
            
            <div class="faq-management">
                <div class="header">
                    <h1><i class="fas fa-question-circle"></i> إدارة الأسئلة الشائعة</h1>
                    <p>إضافة وتعديل وحذف الأسئلة الشائعة</p>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- نموذج إضافة سؤال جديد -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-plus"></i> إضافة سؤال جديد</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="question_ar">السؤال (عربي)</label>
                                        <input type="text" class="form-control" id="question_ar" name="question_ar" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="question_en">السؤال (إنجليزي)</label>
                                        <input type="text" class="form-control" id="question_en" name="question_en" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="answer_ar">الإجابة (عربي)</label>
                                        <textarea class="form-control" id="answer_ar" name="answer_ar" rows="3" required></textarea>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="answer_en">الإجابة (إنجليزي)</label>
                                        <textarea class="form-control" id="answer_en" name="answer_en" rows="3" required></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="display_order">ترتيب العرض</label>
                                <input type="number" class="form-control" id="display_order" name="display_order" value="0" min="0">
                            </div>
                            <button type="submit" name="add_faq" class="btn btn-primary">
                                <i class="fas fa-save"></i> إضافة السؤال
                            </button>
                        </form>
                    </div>
                </div>

                <!-- قائمة الأسئلة -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-list"></i> الأسئلة الحالية</h3>
                    </div>
                    <div class="card-body">
                        <?php if (count($faqs) > 0): ?>
                            <?php foreach ($faqs as $faq): ?>
                            <div class="faq-item-card">
                                <form method="POST" class="faq-form">
                                    <input type="hidden" name="faq_id" value="<?php echo $faq['id']; ?>">
                                    
                                    <div class="row">
                                        <div class="col-5">
                                            <div class="form-group">
                                                <label>السؤال (عربي)</label>
                                                <input type="text" class="form-control" name="question_ar" 
                                                       value="<?php echo htmlspecialchars($faq['question_ar']); ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label>الإجابة (عربي)</label>
                                                <textarea class="form-control" name="answer_ar" rows="3" required><?php echo htmlspecialchars($faq['answer_ar']); ?></textarea>
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <div class="form-group">
                                                <label>السؤال (إنجليزي)</label>
                                                <input type="text" class="form-control" name="question_en" 
                                                       value="<?php echo htmlspecialchars($faq['question_en']); ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label>الإجابة (إنجليزي)</label>
                                                <textarea class="form-control" name="answer_en" rows="3" required><?php echo htmlspecialchars($faq['answer_en']); ?></textarea>
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div class="form-group">
                                                <label>الترتيب</label>
                                                <input type="number" class="form-control" name="display_order" 
                                                       value="<?php echo $faq['display_order']; ?>" min="0">
                                            </div>
                                            <div class="form-group">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" name="is_active" 
                                                           id="active_<?php echo $faq['id']; ?>" <?php echo $faq['is_active'] ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="active_<?php echo $faq['id']; ?>">نشط</label>
                                                </div>
                                            </div>
                                            <div class="faq-actions">
                                                <button type="submit" name="update_faq" class="btn btn-success btn-sm">
                                                    <i class="fas fa-save"></i> تحديث
                                                </button>
                                                <a href="?delete_id=<?php echo $faq['id']; ?>" class="btn btn-danger btn-sm" 
                                                   onclick="return confirm('هل أنت متأكد من حذف هذا السؤال؟')">
                                                    <i class="fas fa-trash"></i> حذف
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-data">لا توجد أسئلة مضافة حالياً</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>