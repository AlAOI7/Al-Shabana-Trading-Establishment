<?php
// تأكد من أن ملف config.php موجود ويتضمن اتصال قاعدة البيانات ($pdo)
require_once '../config.php';

// ابدأ الجلسة إذا لم تكن قد بدأت بالفعل (يفترض أن config.php يقوم بذلك)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. التحقق من صلاحية الوصول (مدير فقط)
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// ===============================================
// 2. معالجة الإجراءات الخلفية (Back-End Actions)
// ===============================================

// معالجة طلب جلب بيانات مستخدم واحد (عبر AJAX لـ "عرض/تعديل")
if (isset($_GET['action']) && $_GET['action'] === 'get_user_data' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $user_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($user_id) {
        try {
            $stmt = $pdo->prepare("SELECT id, full_name, username, email, phone, user_type, created_at, last_login FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // تنسيق البيانات لتناسب الحقول في النافذة المنبثقة
                $user['created_at'] = date('Y-m-d H:i', strtotime($user['created_at']));
                $user['last_login'] = $user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : 'لم يسجل دخول بعد';
                echo json_encode($user);
                exit();
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
            exit();
        }
    }
    http_response_code(404);
    echo json_encode(['error' => 'User not found or invalid ID']);
    exit();
}

// معالجة حذف المستخدم
if (isset($_GET['delete_user'])) {
    $user_id = filter_var($_GET['delete_user'], FILTER_VALIDATE_INT);
    if ($user_id && $user_id != $_SESSION['user_id']) { // منع حذف النفس
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            header("Location: users.php?success=user_deleted");
            exit();
        } catch (PDOException $e) {
            // يمكن إضافة معالجة خطأ أكثر تفصيلاً هنا
            header("Location: users.php?error=db_delete_fail");
            exit();
        }
    }
    header("Location: users.php?error=cannot_delete_self");
    exit();
}

// معالجة الإضافة/التعديل/تغيير النوع (عبر POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // معالجة تغيير نوع المستخدم من الجدول (الـ SELECT)
    if (isset($_POST['change_user_type'])) {
        $user_id = filter_var($_POST['user_id'], FILTER_VALIDATE_INT);
        $new_type = in_array($_POST['user_type'], ['admin', 'client']) ? $_POST['user_type'] : 'client';
        
        if ($user_id && $user_id != $_SESSION['user_id']) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET user_type = ? WHERE id = ?");
                $stmt->execute([$new_type, $user_id]);
                header("Location: users.php?success=user_updated");
                exit();
            } catch (PDOException $e) {
                 header("Location: users.php?error=db_update_fail");
                 exit();
            }
        }
    }

    // معالجة إضافة مستخدم جديد (من نافذة الإضافة المنبثقة)
    if (isset($_POST['add_user'])) {
        $full_name = htmlspecialchars(trim($_POST['full_name']));
        $username = htmlspecialchars(trim($_POST['username']));
        $email = htmlspecialchars(trim($_POST['email']));
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $phone = htmlspecialchars(trim($_POST['phone'])) ?: null;
        $user_type = in_array($_POST['user_type'], ['admin', 'client']) ? $_POST['user_type'] : 'client';
        
        if ($password !== $confirm_password) {
            header("Location: users.php?error=password_mismatch");
            exit();
        }
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            // تحقق من وجود البريد الإلكتروني أو اسم المستخدم مسبقاً
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR username = ?");
            $check_stmt->execute([$email, $username]);
            if ($check_stmt->fetchColumn() > 0) {
                header("Location: users.php?error=user_exists");
                exit();
            }

            $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, password, phone, user_type) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$full_name, $username, $email, $hashed_password, $phone, $user_type]);
            
            header("Location: users.php?success=user_created");
            exit();
        } catch (PDOException $e) {
            header("Location: users.php?error=db_create_fail");
            exit();
        }
    }

    // معالجة تعديل مستخدم موجود (من نافذة العرض/التعديل المنبثقة)
    if (isset($_POST['update_user'])) {
        $user_id = filter_var($_POST['user_id'], FILTER_VALIDATE_INT);
        $full_name = htmlspecialchars(trim($_POST['full_name']));
        $username = htmlspecialchars(trim($_POST['username']));
        $email = htmlspecialchars(trim($_POST['email']));
        $phone = htmlspecialchars(trim($_POST['phone'])) ?: null;
        $user_type = in_array($_POST['user_type'], ['admin', 'client']) ? $_POST['user_type'] : 'client';

        if ($user_id) {
             try {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, email = ?, phone = ?, user_type = ? WHERE id = ?");
                $stmt->execute([$full_name, $username, $email, $phone, $user_type, $user_id]);
                
                header("Location: users.php?success=user_updated");
                exit();
            } catch (PDOException $e) {
                header("Location: users.php?error=db_update_fail");
                exit();
            }
        }
    }
}

// 3. جلب جميع المستخدمين للعرض
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ===============================================
// 4. الجزء الأمامي (HTML/CSS)
// ===============================================
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المستخدمين</title>
    <link rel="stylesheet" href="style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* التنسيقات الإضافية التي أرفقتها */
        :root {
            --primary-color: #4a6cf7;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        .main-content {
            padding: 20px;
            background-color: #f5f7fb;
            min-height: 100vh;
        }

        .header {
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, var(--primary-color), #6a11cb);
            border-radius: var(--border-radius);
            color: white;
            box-shadow: var(--box-shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-content h1 {
            margin: 0 0 10px 0;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-content p {
            margin: 0;
            opacity: 0.9;
            font-size: 1rem;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px 30px -10px;
        }

        .col-3 {
            flex: 0 0 25%;
            max-width: 25%;
            padding: 0 10px;
            margin-bottom: 20px;
        }

        @media (max-width: 992px) {
            .col-3 {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }

        @media (max-width: 576px) {
            .col-3 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }

        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            overflow: hidden;
            height: 100%;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .card-body {
            padding: 25px;
        }

        .card-body h3 {
            font-size: 2.2rem;
            margin: 0 0 10px 0;
            color: var(--primary-color);
            font-weight: 700;
        }

        .card-body p {
            margin: 0;
            color: var(--secondary-color);
            font-size: 0.95rem;
        }

        .card-header {
            padding: 20px 25px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            background-color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h3 {
            margin: 0;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--dark-color);
        }

        .table-responsive {
            overflow-x: auto;
            border-radius: var(--border-radius);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead {
            background-color: #f8f9fa;
        }

        table th {
            padding: 15px 12px;
            text-align: right;
            font-weight: 600;
            color: var(--dark-color);
            border-bottom: 2px solid #dee2e6;
            font-size: 0.9rem;
        }

        table td {
            padding: 15px 12px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }

        table tbody tr {
            transition: var(--transition);
        }

        table tbody tr:hover {
            background-color: rgba(74, 108, 247, 0.05);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-color), #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-details strong {
            display: block;
            margin-bottom: 3px;
            color: var(--dark-color);
        }

        .user-details small {
            color: var(--secondary-color);
            font-size: 0.85rem;
        }

        .user-type-select {
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: white;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .user-type-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(74, 108, 247, 0.2);
        }

        .user-type-select:disabled {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: #3a5bd9;
            transform: translateY(-2px);
        }

        .btn-success {
            background-color: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }

        .btn-info {
            background-color: var(--info-color);
            color: white;
        }

        .btn-info:hover {
            background-color: #138496;
            transform: translateY(-2px);
        }

        .alert {
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.15);
            color: #155724;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        
        .alert-danger {
             background-color: rgba(220, 53, 69, 0.15);
             color: #721c24;
             border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .no-phone {
            color: #999;
            font-style: italic;
        }

        /* النافذة المنبثقة */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
            box-sizing: border-box;
        }

        .modal-content {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalFadeIn 0.3s ease-out;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.4rem;
            color: var(--dark-color);
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--secondary-color);
            transition: var(--transition);
        }

        .close-btn:hover {
            color: var(--danger-color);
        }

        .modal-body {
            padding: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-color);
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: var(--transition);
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(74, 108, 247, 0.2);
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-start;
            gap: 10px;
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        /* تحسينات للتصميم المتجاوب */
        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }
            
            .header {
                padding: 15px;
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .header-content h1 {
                font-size: 1.5rem;
            }
            
            .card-body {
                padding: 20px;
            }
            
            table th, table td {
                padding: 10px 8px;
                font-size: 0.85rem;
            }
            
            .user-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .actions {
                flex-direction: column;
                gap: 5px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }

        /* تأثيرات إضافية */
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'sidebar.php'; ?> 
      
             
          
        <main class="main-content">
              <?php include 'admin_navbar.php'; ?>
            
            <div class="header" style="display: none;">
                <h1>لوحة التحكم الرئيسية</h1>
                <div class="date-display" id="current-date">تحميل التاريخ...</div>
            </div>
            <div class="header fade-in">
                <div class="header-content">
                    <h1><i class="fas fa-users"></i> إدارة المستخدمين</h1>
                    <p>إدارة حسابات العملاء والأدمن</p>
                </div>
                <button class="btn btn-success" id="addUserBtn">
                    <i class="fas fa-plus"></i> إضافة مستخدم جديد
                </button>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> 
                    <?php 
                    if ($_GET['success'] == 'user_deleted') echo 'تم حذف المستخدم بنجاح';
                    elseif ($_GET['success'] == 'user_updated') echo 'تم تحديث المستخدم بنجاح';
                    elseif ($_GET['success'] == 'user_created') echo 'تم إنشاء المستخدم بنجاح';
                    ?>
                </div>
            <?php elseif (isset($_GET['error'])): ?>
                 <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <?php 
                    if ($_GET['error'] == 'cannot_delete_self') echo 'لا يمكن حذف حسابك الحالي';
                    elseif ($_GET['error'] == 'password_mismatch') echo 'كلمتا المرور غير متطابقتين';
                    elseif ($_GET['error'] == 'user_exists') echo 'البريد الإلكتروني أو اسم المستخدم موجود بالفعل';
                    else echo 'حدث خطأ في عملية قاعدة البيانات.';
                    ?>
                </div>
            <?php endif; ?>

            <div class="row fade-in">
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

            <div class="card fade-in">
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
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <?php echo strtoupper(substr($user['full_name'] ?: $user['username'], 0, 1)); ?>
                                            </div>
                                            <div class="user-details">
                                                <strong><?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?></strong>
                                                <small>@<?php echo htmlspecialchars($user['username']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone']) ?: '<span class="no-phone">غير متوفر</span>'; ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <select name="user_type" onchange="this.form.submit()" class="user-type-select" <?php echo $user['id'] == $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                                                <option value="client" <?php echo $user['user_type'] == 'client' ? 'selected' : ''; ?>>عميل</option>
                                                <option value="admin" <?php echo $user['user_type'] == 'admin' ? 'selected' : ''; ?>>أدمن</option>
                                            </select>
                                            <input type="hidden" name="change_user_type" value="1">
                                        </form>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="actions">
                                            <button class="btn btn-info view-user-btn" data-user-id="<?php echo $user['id']; ?>">
                                                <i class="fas fa-eye"></i> عرض/تعديل
                                            </button>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <a href="users.php?delete_user=<?php echo $user['id']; ?>" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم؟ لا يمكن التراجع عن هذا الإجراء.')">
                                                    <i class="fas fa-trash"></i> حذف
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

    <div class="modal" id="addUserModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user-plus"></i> إضافة مستخدم جديد</h3>
                <button type="button" class="close-btn">&times;</button>
            </div>
            <form method="POST" id="addUserForm">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="full_name">الاسم الكامل</label>
                            <input type="text" id="full_name" name="full_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="username">اسم المستخدم</label>
                            <input type="text" id="username" name="username" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">البريد الإلكتروني</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">كلمة المرور</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">تأكيد كلمة المرور</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">رقم الهاتف (اختياري)</label>
                            <input type="tel" id="phone" name="phone" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="user_type_add">نوع المستخدم</label>
                            <select id="user_type_add" name="user_type" class="form-control">
                                <option value="client">عميل</option>
                                <option value="admin">أدمن</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close-btn">إلغاء</button>
                    <button type="submit" class="btn btn-success" name="add_user">إضافة المستخدم</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="viewUserModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user-edit"></i> بيانات المستخدم</h3>
                <button type="button" class="close-btn">&times;</button>
            </div>
            <form method="POST" id="editUserForm">
                <input type="hidden" id="edit_user_id" name="user_id">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_full_name">الاسم الكامل</label>
                            <input type="text" id="edit_full_name" name="full_name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="edit_username">اسم المستخدم</label>
                            <input type="text" id="edit_username" name="username" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_email">البريد الإلكتروني</label>
                        <input type="email" id="edit_email" name="email" class="form-control">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_phone">رقم الهاتف</label>
                            <input type="tel" id="edit_phone" name="phone" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="edit_user_type">نوع المستخدم</label>
                            <select id="edit_user_type" name="user_type" class="form-control">
                                <option value="client">عميل</option>
                                <option value="admin">أدمن</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_created_at">تاريخ التسجيل</label>
                        <input type="text" id="edit_created_at" class="form-control" disabled>
                    </div>
                    <div class="form-group">
                        <label for="edit_last_login">آخر تسجيل دخول</label>
                        <input type="text" id="edit_last_login" class="form-control" disabled>
                    </div>
                    </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close-btn">إلغاء</button>
                    <button type="submit" class="btn btn-primary" name="update_user">حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ===============================================
        // 5. وظائف JavaScript (للتحكم في النوافذ المنبثقة وتحميل البيانات)
        // ===============================================

        document.addEventListener('DOMContentLoaded', function() {
            const addUserBtn = document.getElementById('addUserBtn');
            const addUserModal = document.getElementById('addUserModal');
            const viewUserModal = document.getElementById('viewUserModal');
            // نستخدم querySelectorAll للحصول على جميع أزرار الإغلاق في النوافذ المنبثقة
            const closeBtns = document.querySelectorAll('.close-btn'); 
            const viewUserBtns = document.querySelectorAll('.view-user-btn');
            const addUserForm = document.getElementById('addUserForm');

            // وظيفة فتح نافذة إضافة مستخدم
            addUserBtn.addEventListener('click', function() {
                addUserModal.style.display = 'flex';
            });

            // وظيفة إغلاق النوافذ المنبثقة
            closeBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    // إغلاق النافذة الأبوية للزر
                    const modal = this.closest('.modal');
                    if (modal) {
                         modal.style.display = 'none';
                    }
                });
            });

            // إغلاق النافذة عند النقر خارجها
            window.addEventListener('click', function(event) {
                if (event.target === addUserModal) {
                    addUserModal.style.display = 'none';
                }
                if (event.target === viewUserModal) {
                    viewUserModal.style.display = 'none';
                }
            });

            // وظيفة تحميل بيانات المستخدم للنافذة المنبثقة (باستخدام AJAX)
            function loadUserData(userId) {
                // إرسال طلب AJAX إلى نفس الملف users.php ولكن مع action=get_user_data
                fetch(`users.php?action=get_user_data&id=${userId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(user => {
                        // ملء حقول نموذج التعديل
                        document.getElementById('edit_user_id').value = user.id;
                        document.getElementById('edit_full_name').value = user.full_name || '';
                        document.getElementById('edit_username').value = user.username;
                        document.getElementById('edit_email').value = user.email;
                        document.getElementById('edit_phone').value = user.phone || '';
                        document.getElementById('edit_user_type').value = user.user_type;
                        document.getElementById('edit_created_at').value = user.created_at;
                        document.getElementById('edit_last_login').value = user.last_login || 'لم يسجل دخول بعد';
                    })
                    .catch(error => {
                        console.error('Error loading user data:', error);
                        alert('حدث خطأ أثناء تحميل بيانات المستخدم: ' + error.message);
                    });
            }
            
            // ربط وظيفة تحميل البيانات بأزرار "عرض/تعديل"
            viewUserBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const userId = this.getAttribute('data-user-id');
                    loadUserData(userId);
                    viewUserModal.style.display = 'flex';
                });
            });

            // التحقق من تطابق كلمات المرور عند إضافة مستخدم
            addUserForm.addEventListener('submit', function(e) {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                
                if (password.length < 6) {
                    e.preventDefault();
                    alert('يجب أن لا تقل كلمة المرور عن 6 أحرف.');
                    return;
                }

                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('كلمات المرور غير متطابقة.');
                }
            });
        });
    </script>
</body>
</html>