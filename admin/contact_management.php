
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
    <title>إدارة معلومات الاتصال</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

<!-- في ملف sidebar.php -->
<li>
    <a href="contact_management.php">
        <i class="fas fa-address-book"></i>
        <span>إدارة الاتصالات</span>
    </a>
</li>
<li>
    <a href="faq_management.php">
        <i class="fas fa-question-circle"></i>
        <span>إدارة الأسئلة الشائعة</span>
    </a>
</li>
```
