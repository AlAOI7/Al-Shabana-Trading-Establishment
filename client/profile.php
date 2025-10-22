<?php
require_once '../config.php';
requireClient();

// جلب بيانات المستخدم الحالي
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$success = '';
$error = '';

// معالجة تحديث الملف الشخصي
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // تحديث البيانات الأساسية
    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
    $stmt->execute([$full_name, $phone, $_SESSION['user_id']]);
    
    // تحديث كلمة المرور إذا تم إدخالها
    if (!empty($current_password) && !empty($new_password)) {
        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                $success .= ' تم تحديث كلمة المرور بنجاح.';
            } else {
                $error = 'كلمات المرور الجديدة غير متطابقة';
            }
        } else {
            $error = 'كلمة المرور الحالية غير صحيحة';
        }
    }
    
    if (empty($error)) {
        $success = 'تم تحديث الملف الشخصي بنجاح' . $success;
        // تحديث الجلسة
        $_SESSION['full_name'] = $full_name;
        // إعادة جلب البيانات
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الملف الشخصي</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <!-- شريط جانبي للعميل -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-user-circle"></i> حسابي</h3>
                <p>مرحباً، <?php echo $_SESSION['full_name']; ?></p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-home"></i> الرئيسية</a></li>
                <li><a href="profile.php" class="active"><i class="fas fa-user"></i> الملف الشخصي</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> طلباتي</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> الإعدادات</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="header">
                <h1><i class="fas fa-user-edit"></i> الملف الشخصي</h1>
                <p>إدارة معلومات حسابك الشخصي</p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-6">
                    <!-- معلومات الحساب -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-user-circle"></i> معلومات الحساب</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="form-group">
                                    <label for="full_name">الاسم الكامل</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="username">اسم المستخدم</label>
                                    <input type="text" class="form-control" id="username" value="<?php echo $user['username']; ?>" disabled>
                                    <small style="color: #666;">لا يمكن تغيير اسم المستخدم</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">البريد الإلكتروني</label>
                                    <input type="email" class="form-control" id="email" value="<?php echo $user['email']; ?>" disabled>
                                    <small style="color: #666;">لا يمكن تغيير البريد الإلكتروني</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone">رقم الهاتف</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($user['phone']); ?>" 
                                           placeholder="أدخل رقم هاتفك">
                                </div>
                                
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="fas fa-save"></i> حفظ التغييرات
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-6">
                    <!-- تغيير كلمة المرور -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-lock"></i> تغيير كلمة المرور</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="form-group">
                                    <label for="current_password">كلمة المرور الحالية</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" 
                                           placeholder="أدخل كلمة المرور الحالية">
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_password">كلمة المرور الجديدة</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" 
                                           placeholder="كلمة المرور الجديدة (6 أحرف على الأقل)">
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password">تأكيد كلمة المرور الجديدة</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           placeholder="أعد إدخال كلمة المرور الجديدة">
                                </div>
                                
                                <button type="submit" name="update_profile" class="btn btn-warning">
                                    <i class="fas fa-key"></i> تغيير كلمة المرور
                                </button>
                                
                                <small style="display: block; margin-top: 1rem; color: #666;">
                                    اترك الحقول فارغة إذا كنت لا تريد تغيير كلمة المرور
                                </small>
                            </form>
                        </div>
                    </div>

                    <!-- معلومات إضافية -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-info-circle"></i> معلومات الحساب</h3>
                        </div>
                        <div class="card-body">
                            <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px;">
                                <p><strong>تاريخ إنشاء الحساب:</strong><br>
                                <?php echo date('Y-m-d', strtotime($user['created_at'])); ?></p>
                                
                                <p><strong>نوع الحساب:</strong><br>
                                <?php echo $user['user_type'] == 'client' ? 'عميل' : 'مسؤول'; ?></p>
                                
                                <p><strong>حالة الحساب:</strong><br>
                                <span style="color: var(--success-color);">نشط</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>