<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// إحصائيات سريعة
$stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users WHERE user_type = 'client'");
$total_users = $stmt->fetch()['total_users'];

$stmt = $pdo->query("SELECT COUNT(*) as total_products FROM products");
$total_products = $stmt->fetch()['total_products'];

$stmt = $pdo->query("SELECT COUNT(*) as total_services FROM services");
$total_services = $stmt->fetch()['total_products'];

$stmt = $pdo->query("SELECT COUNT(*) as total_orders FROM orders");
$total_orders = $stmt->fetch()['total_orders'];

$stmt = $pdo->query("SELECT SUM(total_amount) as total_revenue FROM orders WHERE status = 'completed'");
$total_revenue = $stmt->fetch()['total_revenue'] ?: 0;

// آخر الطلبات
$stmt = $pdo->query
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - الأدمن</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="dashboard">
        <!-- الشريط الجانبي -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>لوحة التحكم</h3>
                <p>مرحباً، <?php echo $_SESSION['full_name']; ?></p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active">الرئيسية</a></li>
                <li><a href="users.php">إدارة المستخدمين</a></li>
                <li><a href="products.php">إدارة المنتجات</a></li>
                <li><a href="services.php">إدارة الخدمات</a></li>
                <li><a href="about.php">من نحن</a></li>
                <li><a href="contact_info.php">بيانات التواصل</a></li>
                <li><a href="settings.php">الإعدادات</a></li>
                <li><a href="../logout.php">تسجيل الخروج</a></li>
            </ul>
        </aside>

        <!-- المحتوى الرئيسي -->
        <main class="main-content">
            <div class="header">
                <h1>لوحة التحكم الرئيسية</h1>
            </div>

            <!-- بطاقات الإحصائيات -->
            <div class="row">
                <div class="col-3">
                    <div class="card">
                        <div class="card-body">
                            <h3><?php echo $total_users; ?></h3>
                            <p>إجمالي العملاء</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-3">
                    <div class="card">
                        <div class="card-body">
                            <h3><?php echo $total_products; ?></h3>
                            <p>إجمالي المنتجات</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-3">
                    <div class="card">
                        <div class="card-body">
                            <h3><?php echo $total_services; ?></h3>
                            <p>إجمالي الخدمات</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-3">
                    <div class="card">
                        <div class="card-body">
                            <h3>0</h3>
                            <p>الطلبات الجديدة</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- قسم سريع للإجراءات -->
            <div class="row">
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>إجراءات سريعة</h3>
                        </div>
                        <div class="card-body">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <a href="products.php?action=add" class="btn btn-primary">إضافة منتج جديد</a>
                                <a href="services.php?action=add" class="btn btn-success">إضافة خدمة جديدة</a>
                                <a href="users.php" class="btn btn-primary">عرض جميع المستخدمين</a>
                                <a href="settings.php" class="btn btn-success">تعديل الإعدادات</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>آخر النشاطات</h3>
                        </div>
                        <div class="card-body">
                            <p>لا توجد نشاطات حديثة</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>