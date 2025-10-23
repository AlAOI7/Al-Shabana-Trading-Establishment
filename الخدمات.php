<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// معالجة إضافة خدمة جديدة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_service'])) {
    $title_ar = $_POST['title_ar'];
    $title_en = $_POST['title_en'];
    $description_ar = $_POST['description_ar'];
    $description_en = $_POST['description_en'];
    $icon = $_POST['icon'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO services (title, title_en, description, description_en, icon) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title_ar, $title_en, $description_ar, $description_en, $icon]);
        $success = "تم إضافة الخدمة بنجاح";
    } catch (PDOException $e) {
        $error = "خطأ في إضافة الخدمة: " . $e->getMessage();
    }
}

// معالجة حذف خدمة
if (isset($_GET['delete_id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$_GET['delete_id']]);
        $success = "تم حذف الخدمة بنجاح";
    } catch (PDOException $e) {
        $error = "خطأ في حذف الخدمة: " . $e->getMessage();
    }
}

// جلب جميع الخدمات
try {
    $stmt = $pdo->query("SELECT * FROM services ORDER BY created_at DESC");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $services = [];
    $error = "خطأ في جلب الخدمات: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة c:\Users\ALAOI\Downloads\alshabanat2.sql</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .services-management {
            padding: 20px;
        }
        
        .add-service-form {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .services-list {
            display: grid;
            gap: 1.5rem;
        }
        
        .service-item {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #2c5aa0;
        }
        
        .service-info {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .service-icon-preview {
            width: 50px;
            height: 50px;
            background: #2c5aa0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
        }
        
        .service-details {
            flex: 1;
        }
        
        .service-languages {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .language-section {
            padding: 1rem;
            background: white;
            border-radius: 5px;
            border: 1px solid #e9ecef;
        }
        
        .language-section h5 {
            color: #2c5aa0;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .language-section.ar h5 i {
            color: #28a745;
        }
        
        .language-section.en h5 i {
            color: #dc3545;
        }
        
        .service-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
            border-top: 1px solid #e9ecef;
            padding-top: 1rem;
            margin-top: 1rem;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <?php include 'admin_navbar.php'; ?>
            
            <div class="services-management">
                <div class="header">
                    <h1><i class="fas fa-concierge-bell"></i> إدارة الخدمات</h1>
                    <p>إضافة وتعديل وحذف الخدمات المعروضة في الموقع</p>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- نموذج إضافة خدمة -->
                <div class="add-service-form">
                    <h3><i class="fas fa-plus"></i> إضافة خدمة جديدة</h3>
                    <form method="POST">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="title_ar">عنوان الخدمة (عربي)</label>
                                    <input type="text" class="form-control" id="title_ar" name="title_ar" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="title_en">عنوان الخدمة (إنجليزي)</label>
                                    <input type="text" class="form-control" id="title_en" name="title_en" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description_ar">وصف الخدمة (عربي)</label>
                            <textarea class="form-control" id="description_ar" name="description_ar" rows="3" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="description_en">وصف الخدمة (إنجليزي)</label>
                            <textarea class="form-control" id="description_en" name="description_en" rows="3" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="icon">أيقونة الخدمة (Font Awesome)</label>
                            <input type="text" class="form-control" id="icon" name="icon" placeholder="مثال: fas fa-fire" required>
                            <small class="text-muted">يمكنك اختيار الأيقونات من <a href="https://fontawesome.com/icons" target="_blank">Font Awesome</a></small>
                        </div>
                        
                        <button type="submit" name="add_service" class="btn btn-primary">
                            <i class="fas fa-save"></i> إضافة الخدمة
                        </button>
                    </form>
                </div>

                <!-- قائمة الخدمات -->
                <div class="services-list">
                    <h3><i class="fas fa-list"></i> الخدمات الحالية</h3>
                    
                    <?php if (count($services) > 0): ?>
                        <?php foreach ($services as $service): ?>
                            <div class="service-item">
                                <div class="service-info">
                                    <div class="service-icon-preview">
                                        <i class="<?php echo htmlspecialchars($service['icon']); ?>"></i>
                                    </div>
                                    <div class="service-details">
                                        <h4><?php echo htmlspecialchars($service['title']); ?></h4>
                                        <p><?php echo htmlspecialchars($service['description']); ?></p>
                                        
                                        <div class="service-languages">
                                            <div class="language-section ar">
                                                <h5><i class="fas fa-language"></i> العربية</h5>
                                                <p><strong>العنوان:</strong> <?php echo htmlspecialchars($service['title']); ?></p>
                                                <p><strong>الوصف:</strong> <?php echo htmlspecialchars($service['description']); ?></p>
                                            </div>
                                            <div class="language-section en">
                                                <h5><i class="fas fa-language"></i> English</h5>
                                                <p><strong>Title:</strong> <?php echo htmlspecialchars($service['title_en']); ?></p>
                                                <p><strong>Description:</strong> <?php echo htmlspecialchars($service['description_en']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="service-actions">
                                    <a href="?delete_id=<?php echo $service['id']; ?>" class="btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذه الخدمة؟')">
                                        <i class="fas fa-trash"></i> حذف
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-services">لا توجد خدمات مضافة حالياً</div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>