<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// جلب جميع الإعدادات
$stmt = $pdo->query("SELECT * FROM settings");
$settings_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // تحويل إلى مصفوفة关联

// معالجة تحديث الإعدادات
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    foreach ($_POST['settings'] as $key => $value) {
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->execute([$value, $key]);
    }
    
    header("Location: settings.php?success=updated");
    exit();
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
<body>
    <div class="dashboard">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="header">
                <h1><i class="fas fa-cogs"></i> إعدادات الموقع</h1>
                <p>تعديل الإعدادات العامة للموقع</p>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> تم تحديث الإعدادات بنجاح
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-sliders-h"></i> الإعدادات العامة</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="site_title"><i class="fas fa-heading"></i> عنوان الموقع</label>
                                    <input type="text" class="form-control" id="site_title" name="settings[site_title]" 
                                           value="<?php echo htmlspecialchars($settings_data['site_title'] ?? ''); ?>" 
                                           placeholder="أدخل عنوان الموقع" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="site_description"><i class="fas fa-align-left"></i> وصف الموقع</label>
                                    <textarea class="form-control" id="site_description" name="settings[site_description]" 
                                              rows="3" placeholder="أدخل وصف مختصر للموقع"><?php echo htmlspecialchars($settings_data['site_description'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="maintenance_mode"><i class="fas fa-tools"></i> وضع الصيانة</label>
                                    <select class="form-control" id="maintenance_mode" name="settings[maintenance_mode]">
                                        <option value="0" <?php echo ($settings_data['maintenance_mode'] ?? '0') == '0' ? 'selected' : ''; ?>>معطل</option>
                                        <option value="1" <?php echo ($settings_data['maintenance_mode'] ?? '0') == '1' ? 'selected' : ''; ?>>مفعل</option>
                                    </select>
                                    <small style="color: #666;">عند التفعيل، سيظهر للمستخدمين رسالة صيانة</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="update_settings" class="btn btn-primary">
                                <i class="fas fa-save"></i> حفظ الإعدادات
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- إعدادات متقدمة -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-tools"></i> أدوات متقدمة</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-4">
                            <div class="card" style="text-align: center; background: #f8f9fa;">
                                <div class="card-body">
                                    <div style="font-size: 2rem; color: var(--primary-color); margin-bottom: 1rem;">
                                        <i class="fas fa-database"></i>
                                    </div>
                                    <h4>نسخ احتياطي</h4>
                                    <p>إنشاء نسخة احتياطية من قاعدة البيانات</p>
                                    <button class="btn btn-primary" onclick="alert('سيتم تطوير هذه الميزة قريباً')">
                                        <i class="fas fa-download"></i> إنشاء نسخة
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-4">
                            <div class="card" style="text-align: center; background: #f8f9fa;">
                                <div class="card-body">
                                    <div style="font-size: 2rem; color: var(--success-color); margin-bottom: 1rem;">
                                        <i class="fas fa-file-export"></i>
                                    </div>
                                    <h4>تصدير البيانات</h4>
                                    <p>تصدير المنتجات والمستخدمين</p>
                                    <button class="btn btn-success" onclick="alert('سيتم تطوير هذه الميزة قريباً')">
                                        <i class="fas fa-file-export"></i> تصدير
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-4">
                            <div class="card" style="text-align: center; background: #f8f9fa;">
                                <div class="card-body">
                                    <div style="font-size: 2rem; color: var(--warning-color); margin-bottom: 1rem;">
                                        <i class="fas fa-broom"></i>
                                    </div>
                                    <h4>تنظيف النظام</h4>
                                    <p>حذف الملفات والبيانات المؤقتة</p>
                                    <button class="btn btn-warning" onclick="alert('سيتم تطوير هذه الميزة قريباً')">
                                        <i class="fas fa-broom"></i> تنظيف
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>