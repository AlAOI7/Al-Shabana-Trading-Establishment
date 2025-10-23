<?php
require_once '../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// السماح فقط للمستخدمين المسجلين بالدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// جلب بيانات المستخدم الحالي
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id, full_name, username, email, phone, user_type, created_at, last_login FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("حدث خطأ: لم يتم العثور على المستخدم.");
}

// معالجة تحديث البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = htmlspecialchars(trim($_POST['full_name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $password = $_POST['password'] ?? '';

    try {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, password = ? WHERE id = ?");
            $update->execute([$full_name, $email, $phone, $hashed_password, $user_id]);
        } else {
            $update = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
            $update->execute([$full_name, $email, $phone, $user_id]);
        }

        $_SESSION['full_name'] = $full_name;
        header("Location: profile.php?success=1");
        exit();

    } catch (PDOException $e) {
        header("Location: profile.php?error=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>الملف الشخصي</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f9f9f9;
            color: #333;
        }
        .profile-container {
            max-width: 700px;
            margin: 3rem auto;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .profile-header i {
            font-size: 60px;
            color: #4cc9f0;
        }
        .profile-header h2 {
            margin-top: 10px;
            color: #333;
        }
        .profile-info label {
            font-weight: bold;
        }
        .profile-info input {
            width: 100%;
            padding: 10px;
            margin: 8px 0 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .profile-info input[readonly] {
            background-color: #f0f0f0;
        }
        .profile-actions {
            text-align: center;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            background: #4cc9f0;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn:hover {
            background: #4895ef;
        }
        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 15px;
        }
        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
      <div class="dashboard">

      
             
          
        <main class="main-content">
              <?php include 'admin_navbar.php'; ?>
                <div class="profile-container">
                    <div class="profile-header">
                        <i class="fas fa-user-circle"></i>
                        <h2>الملف الشخصي</h2>
                        <p><?php echo htmlspecialchars($user['username']); ?> (<?php echo $user['user_type'] === 'admin' ? 'أدمن' : 'مستخدم'; ?>)</p>
                    </div>

                    <?php if (isset($_GET['success'])): ?>
                        <div class="success-msg">تم تحديث الملف الشخصي بنجاح ✅</div>
                    <?php elseif (isset($_GET['error'])): ?>
                        <div class="error-msg">حدث خطأ أثناء التحديث ❌</div>
                    <?php endif; ?>

                    <form method="POST" class="profile-info">
                        <label>الاسم الكامل</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>

                        <label>البريد الإلكتروني</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

                        <label>رقم الهاتف</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">

                        <label>تاريخ التسجيل</label>
                        <input type="text" value="<?php echo date('Y-m-d', strtotime($user['created_at'])); ?>" readonly>

                        <label>آخر تسجيل دخول</label>
                        <input type="text" value="<?php echo $user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : 'لم يسجل دخول بعد'; ?>" readonly>

                        <label>كلمة المرور (اتركها فارغة إن لم ترغب بتغييرها)</label>
                        <input type="password" name="password" placeholder="********">

                        <div class="profile-actions">
                            <button type="submit" name="update_profile" class="btn">
                                <i class="fas fa-save"></i> حفظ التعديلات
                            </button>
                        </div>
                    </form>
                </div>
                </main>
    </div>

</body>
</html>
