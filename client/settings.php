<?php
require_once '../config.php';
requireClient();

$success = '';
$error = '';

// معالجة تحديث الإعدادات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_notifications'])) {
        $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
        $sms_notifications = isset($_POST['sms_notifications']) ? 1 : 0;
        $newsletter = isset($_POST['newsletter']) ? 1 : 0;
        
        // هنا يمكنك حفظ إعدادات الإشعارات في قاعدة البيانات
        $success = 'تم تحديث إعدادات الإشعارات بنجاح';
    }
    
    if (isset($_POST['update_privacy'])) {
        $profile_visibility = $_POST['profile_visibility'];
        $data_sharing = isset($_POST['data_sharing']) ? 1 : 0;
        
        // هنا يمكنك حفظ إعدادات الخصوصية في قاعدة البيانات
        $success = 'تم تحديث إعدادات الخصوصية بنجاح';
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الإعدادات</title>
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
                <li><a href="profile.php"><i class="fas fa-user"></i> الملف الشخصي</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> طلباتي</a></li>
                <li><a href="settings.php" class="active"><i class="fas fa-cog"></i> الإعدادات</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="header">
                <h1><i class="fas fa-cogs"></i> الإعدادات</h1>
                <p>إدارة إعدادات حسابك وتفضيلاتك</p>
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
                    <!-- إعدادات الإشعارات -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-bell"></i> إعدادات الإشعارات</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="form-group">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 10px;">
                                        <div>
                                            <h5 style="margin: 0;">الإشعارات البريدية</h5>
                                            <small style="color: #666;">استلام إشعارات على البريد الإلكتروني</small>
                                        </div>
                                        <label class="switch">
                                            <input type="checkbox" name="email_notifications" checked>
                                            <span class="slider round"></span>
                                        </label>
                                    </div>

                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 10px;">
                                        <div>
                                            <h5 style="margin: 0;">إشعارات SMS</h5>
                                            <small style="color: #666;">استلام إشعارات على الجوال</small>
                                        </div>
                                        <label class="switch">
                                            <input type="checkbox" name="sms_notifications">
                                            <span class="slider round"></span>
                                        </label>
                                    </div>

                                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f8f9fa; border-radius: 10px;">
                                        <div>
                                            <h5 style="margin: 0;">النشرة البريدية</h5>
                                            <small style="color: #666;">استلام عروض وأخبار حصرية</small>
                                        </div>
                                        <label class="switch">
                                            <input type="checkbox" name="newsletter" checked>
                                            <span class="slider round"></span>
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" name="update_notifications" class="btn btn-primary">
                                    <i class="fas fa-save"></i> حفظ إعدادات الإشعارات
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-6">
                    <!-- إعدادات الخصوصية -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-shield-alt"></i> الخصوصية</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="form-group">
                                    <label for="profile_visibility">ظهور الملف الشخصي</label>
                                    <select class="form-control" id="profile_visibility" name="profile_visibility">
                                        <option value="public">عام (الجميع يمكنهم رؤية ملفك)</option>
                                        <option value="private">خاص (فقط يمكنك رؤية ملفك)</option>
                                        <option value="friends">الأصدقاء فقط</option>
                                    </select>
                                </div>

                                <div style="display: flex; justify-content: space-between; align-items: center; margin: 1.5rem 0; padding: 1rem; background: #f8f9fa; border-radius: 10px;">
                                    <div>
                                        <h5 style="margin: 0;">مشاركة البيانات</h5>
                                        <small style="color: #666;">السماح بمشاركة بياناتك لأغراض تحسين الخدمة</small>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" name="data_sharing">
                                        <span class="slider round"></span>
                                    </label>
                                </div>

                                <button type="submit" name="update_privacy" class="btn btn-primary">
                                    <i class="fas fa-save"></i> حفظ إعدادات الخصوصية
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- إعدادات الحساب -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-user-cog"></i> إعدادات الحساب</h3>
                        </div>
                        <div class="card-body">
                            <div style="text-align: center;">
                                <div style="margin-bottom: 1.5rem;">
                                    <a href="profile.php" class="btn btn-outline" style="border: 1px solid #ddd; padding: 0.75rem 1.5rem; margin: 0.5rem; display: inline-block; border-radius: 10px; text-decoration: none; color: var(--dark-color);">
                                        <i class="fas fa-user-edit"></i><br>
                                        <small>تعديل الملف الشخصي</small>
                                    </a>
                                    <a href="change_password.php" class="btn btn-outline" style="border: 1px solid #ddd; padding: 0.75rem 1.5rem; margin: 0.5rem; display: inline-block; border-radius: 10px; text-decoration: none; color: var(--dark-color);">
                                        <i class="fas fa-key"></i><br>
                                        <small>تغيير كلمة المرور</small>
                                    </a>
                                </div>

                                <div style="border-top: 1px solid #eee; padding-top: 1rem;">
                                    <a href="delete_account.php" class="btn btn-danger" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                        <i class="fas fa-trash"></i> حذف الحساب
                                    </a>
                                    <small style="display: block; margin-top: 0.5rem; color: #666;">هذا الإجراء لا يمكن التراجع عنه</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
        /* تنسيق الـ Switch */
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: var(--success-color);
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }
    </style>
</body>
</html>