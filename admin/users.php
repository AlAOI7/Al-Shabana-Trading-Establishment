<?php
require_once '../config.php';
requireAdmin();

// جلب جميع المستخدمين
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

// معالجة حذف المستخدم
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];
    if ($user_id != $_SESSION['user_id']) { // منع حذف النفس
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        header("Location: users.php?success=user_deleted");
        exit();
    }
}

// معالجة تغيير نوع المستخدم
if (isset($_POST['change_user_type'])) {
    $user_id = $_POST['user_id'];
    $new_type = $_POST['user_type'];
    
    $stmt = $pdo->prepare("UPDATE users SET user_type = ? WHERE id = ?");
    $stmt->execute([$new_type, $user_id]);
    header("Location: users.php?success=user_updated");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المستخدمين</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="header">
                <h1><i class="fas fa-users"></i> إدارة المستخدمين</h1>
                <p>إدارة حسابات العملاء والأدمن</p>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> 
                    <?php 
                    if ($_GET['success'] == 'user_deleted') echo 'تم حذف المستخدم بنجاح';
                    elseif ($_GET['success'] == 'user_updated') echo 'تم تحديث المستخدم بنجاح';
                    ?>
                </div>
            <?php endif; ?>

            <!-- إحصائيات سريعة -->
            <div class="row">
                <div class="col-3">
                    <div class="card">
                        <div class="card-body" style="text-align: center;">
                            <h3><?php echo count($users); ?></h3>
                            <p>إجمالي المستخدمين</p>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="card">
                        <div class="card-body" style="text-align: center;">
                            <h3><?php echo count(array_filter($users, fn($u) => $u['user_type'] === 'admin')); ?></h3>
                            <p>مسؤولين</p>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="card">
                        <div class="card-body" style="text-align: center;">
                            <h3><?php echo count(array_filter($users, fn($u) => $u['user_type'] === 'client')); ?></h3>
                            <p>عملاء</p>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="card">
                        <div class="card-body" style="text-align: center;">
                            <h3><?php echo count(array_filter($users, fn($u) => !empty($u['phone']))); ?></h3>
                            <p>مثبت رقم الهاتف</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- جدول المستخدمين -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> قائمة المستخدمين</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المستخدم</th>
                                    <th>البريد الإلكتروني</th>
                                    <th>الهاتف</th>
                                    <th>النوع</th>
                                    <th>تاريخ التسجيل</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $index => $user): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                                <?php echo strtoupper(substr($user['full_name'] ?: $user['username'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <strong><?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?></strong><br>
                                                <small style="color: #666;">@<?php echo $user['username']; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td><?php echo $user['phone'] ?: '<span style="color: #999;">غير متوفر</span>'; ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <select name="user_type" onchange="this.form.submit()" style="padding: 5px; border: 1px solid #ddd; border-radius: 5px;" <?php echo $user['id'] == $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                                                <option value="client" <?php echo $user['user_type'] == 'client' ? 'selected' : ''; ?>>عميل</option>
                                                <option value="admin" <?php echo $user['user_type'] == 'admin' ? 'selected' : ''; ?>>أدمن</option>
                                            </select>
                                            <input type="hidden" name="change_user_type" value="1">
                                        </form>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div style="display: flex; gap: 5px;">
                                            <a href="user_profile.php?id=<?php echo $user['id']; ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.8rem;">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <a href="users.php?delete_user=<?php echo $user['id']; ?>" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem;" onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم؟')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>