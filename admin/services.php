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
    <title>إدارة الخدمات</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="header">
                <h1><i class="fas fa-concierge-bell"></i> إدارة الخدمات</h1>
                <p>إضافة وتعديل وحذف الخدمات المقدمة</p>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> 
                    <?php 
                    if ($_GET['success'] == 'service_added') echo 'تم إضافة الخدمة بنجاح';
                    elseif ($_GET['success'] == 'service_updated') echo 'تم تعديل الخدمة بنجاح';
                    elseif ($_GET['success'] == 'service_deleted') echo 'تم حذف الخدمة بنجاح';
                    ?>
                </div>
            <?php endif; ?>

            <!-- نموذج إضافة/تعديل الخدمة -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-<?php echo $edit_service ? 'edit' : 'plus'; ?>"></i> 
                        <?php echo $edit_service ? 'تعديل الخدمة' : 'إضافة خدمة جديدة'; ?>
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
                                    <label for="title">عنوان الخدمة</label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="<?php echo $edit_service ? htmlspecialchars($edit_service['title']) : ''; ?>" 
                                           required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="icon">أيقونة الخدمة (Font Awesome)</label>
                                    <input type="text" class="form-control" id="icon" name="icon" 
                                           value="<?php echo $edit_service ? htmlspecialchars($edit_service['icon']) : ''; ?>" 
                                           placeholder="مثال: fas fa-home" required>
                                    <small style="color: #666;">استخدم أيقونات من <a href="https://fontawesome.com/icons" target="_blank">Font Awesome</a></small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">وصف الخدمة</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required><?php echo $edit_service ? htmlspecialchars($edit_service['description']) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-<?php echo $edit_service ? 'save' : 'plus'; ?>"></i>
                            <?php echo $edit_service ? 'حفظ التعديلات' : 'إضافة الخدمة'; ?>
                        </button>
                        
                        <?php if ($edit_service): ?>
                            <a href="services.php" class="btn btn-secondary">إلغاء</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- قائمة الخدمات -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> قائمة الخدمات (<?php echo count($services); ?>)</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($services)): ?>
                        <div style="text-align: center; padding: 2rem; color: #666;">
                            <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>لا توجد خدمات مضافة حتى الآن</p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($services as $service): ?>
                            <div class="col-4">
                                <div class="card" style="margin-bottom: 1rem;">
                                    <div class="card-body" style="text-align: center;">
                                        <div style="font-size: 2rem; color: var(--primary-color); margin-bottom: 1rem;">
                                            <?php if ($service['icon']): ?>
                                                <i class="<?php echo $service['icon']; ?>"></i>
                                            <?php else: ?>
                                                <i class="fas fa-concierge-bell"></i>
                                            <?php endif; ?>
                                        </div>
                                        <h4><?php echo htmlspecialchars($service['title']); ?></h4>
                                        <p style="color: #666; font-size: 0.9rem;"><?php echo htmlspecialchars($service['description']); ?></p>
                                        <div style="margin-top: 1rem;">
                                            <a href="services.php?edit_service=<?php echo $service['id']; ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.8rem;">
                                                <i class="fas fa-edit"></i> تعديل
                                            </a>
                                            <a href="services.php?delete_service=<?php echo $service['id']; ?>" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem;" onclick="return confirm('هل أنت متأكد من حذف هذه الخدمة؟')">
                                                <i class="fas fa-trash"></i> حذف
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
</body>
</html>