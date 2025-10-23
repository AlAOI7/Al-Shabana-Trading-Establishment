<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}


// جلب جميع الطلبات مع معلومات المستخدمين
$stmt = $pdo->query("
    SELECT o.*, u.username, u.full_name, u.email 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll();

// معالجة تحديث حالة الطلب
if (isset($_POST['update_order_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);
    
    header("Location: orders.php?success=status_updated");
    exit();
}

// معالجة حذف الطلب
if (isset($_GET['delete_order'])) {
    $order_id = $_GET['delete_order'];
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    header("Location: orders.php?success=order_deleted");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-translate="orders_management">إدارة الطلبات</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --info: #4895ef;
            --warning: #f72585;
            --danger: #e63946;
            --light: #f8f9fa;
            --dark: #212529;
            --sidebar-bg: #1e293b;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fb;
            color: #333;
            line-height: 1.6;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* الشريط الجانبي */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            color: white;
            transition: var(--transition);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .sidebar-header h3 {
            margin-bottom: 0.5rem;
            font-size: 1.4rem;
        }

        .sidebar-header p {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .sidebar-menu {
            list-style: none;
            padding: 1rem 0;
        }

        .sidebar-menu li {
            margin-bottom: 0.2rem;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.8rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--transition);
            border-right: 3px solid transparent;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-right-color: var(--primary);
        }

        .sidebar-menu i {
            margin-left: 0.5rem;
            width: 20px;
            text-align: center;
        }

        /* المحتوى الرئيسي */
        .main-content {
            flex: 1;
            padding: 1.5rem;
            overflow-y: auto;
        }

        .header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .header h1 {
            color: var(--dark);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: #64748b;
        }

        /* بطاقات */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: var(--transition);
            border: none;
            margin-bottom: 1.5rem;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            background: white;
        }

        .card-header h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark);
        }

        .card-body {
            padding: 1.5rem;
        }

        /* الأزرار */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 0.8rem;
            border-radius: 6px;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            font-size: 0.8rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--secondary);
            color: white;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: #d32f2f;
            color: white;
        }

        /* شبكة الصفوف والأعمدة */
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -0.75rem;
            margin-bottom: 1.5rem;
        }

        .col-3 {
            flex: 0 0 25%;
            max-width: 25%;
            padding: 0 0.75rem;
            margin-bottom: 1rem;
        }

        /* إحصائيات الطلبات */
        .stat-card {
            text-align: center;
            padding: 1.5rem 1rem;
            border-radius: 12px;
            background: white;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
        }

        .stat-card h3 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        /* الجداول */
        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th, .table td {
            padding: 0.8rem 1rem;
            text-align: right;
            border-bottom: 1px solid #e2e8f0;
        }

        .table th {
            background: #f8fafc;
            font-weight: 600;
            color: #475569;
        }

        .table tr:hover {
            background: #f8fafc;
        }

        /* نماذج */
        .form-control {
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        select.form-control {
            cursor: pointer;
        }

        /* تنبيهات */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert i {
            margin-left: 0.5rem;
        }

        /* حالة الطلبات */
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #d1ecf1; color: #0c5460; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }

        /* زر الترجمة */
        .translate-btn {
            position: fixed;
            bottom: 20px;
            left: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            z-index: 1000;
            transition: var(--transition);
            border: none;
        }

        .translate-btn:hover {
            transform: scale(1.1);
            background: var(--secondary);
        }

        .translate-btn i {
            font-size: 1.2rem;
        }

        /* نمط للغة الإنجليزية */
        body[dir="ltr"] {
            text-align: left;
        }

        body[dir="ltr"] .sidebar {
            text-align: left;
        }

        body[dir="ltr"] .sidebar-menu i {
            margin-left: 0;
            margin-right: 0.5rem;
        }

        body[dir="ltr"] .alert i {
            margin-left: 0;
            margin-right: 0.5rem;
        }

        body[dir="ltr"] .table th, 
        body[dir="ltr"] .table td {
            text-align: left;
        }

        /* تصميم متجاوب */
        @media (max-width: 992px) {
            .dashboard {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
            }
            
            .sidebar-menu {
                display: flex;
                overflow-x: auto;
                padding: 0.5rem;
            }
            
            .sidebar-menu li {
                flex: 0 0 auto;
                margin-bottom: 0;
            }
            
            .sidebar-menu a {
                padding: 0.8rem 1rem;
                border-right: none;
                border-bottom: 3px solid transparent;
            }
            
            .sidebar-menu a:hover, .sidebar-menu a.active {
                border-right-color: transparent;
                border-bottom-color: var(--primary);
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }
            
            .col-3 {
                flex: 0 0 50%;
                max-width: 50%;
            }
            
            .table-responsive {
                font-size: 0.8rem;
            }
            
            .table th, .table td {
                padding: 0.5rem;
            }
        }

        @media (max-width: 576px) {
            .col-3 {
                flex: 0 0 100%;
                max-width: 100%;
            }
            
            .table-responsive {
                font-size: 0.7rem;
            }
            
            .btn {
                padding: 0.4rem 0.6rem;
                font-size: 0.7rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'sidebar.php'; ?>
        
        <!-- المحتوى الرئيسي -->
        <main class="main-content">
            <?php include 'admin_navbar.php'; ?>
           
            <div class="header">
                <h1><i class="fas fa-shopping-cart"></i> <span data-translate="orders_management">إدارة الطلبات</span></h1>
                <p data-translate="orders_management_desc">عرض وإدارة طلبات العملاء</p>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> 
                    <span data-translate="<?php echo $_GET['success']; ?>">
                        <?php 
                        if ($_GET['success'] == 'status_updated') echo 'تم تحديث حالة الطلب بنجاح';
                        elseif ($_GET['success'] == 'order_deleted') echo 'تم حذف الطلب بنجاح';
                        ?>
                    </span>
                </div>
            <?php endif; ?>

            <!-- إحصائيات الطلبات -->
            <div class="row">
                <?php
                $statuses = ['pending', 'processing', 'completed', 'cancelled'];
                $status_labels = ['pending', 'processing', 'completed', 'cancelled'];
                $status_colors = ['warning', 'info', 'success', 'danger'];
                
                foreach ($statuses as $index => $status) {
                    $count = count(array_filter($orders, fn($o) => $o['status'] === $status));
                    $color = $status_colors[$index];
                    $label = $status_labels[$index];
                ?>
                <div class="col-3">
                    <div class="stat-card">
                        <h3 style="color: var(--<?php echo $color; ?>);"><?php echo $count; ?></h3>
                        <p data-translate="<?php echo $label; ?>"><?php 
                            if ($status == 'pending') echo 'قيد الانتظار';
                            elseif ($status == 'processing') echo 'قيد المعالجة';
                            elseif ($status == 'completed') echo 'مكتمل';
                            elseif ($status == 'cancelled') echo 'ملغي';
                        ?></p>
                    </div>
                </div>
                <?php } ?>
            </div>

            <!-- جدول الطلبات -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> <span data-translate="orders_list">قائمة الطلبات</span></h3>
                </div>
                <div class="card-body">
                    <?php if (empty($orders)): ?>
                        <div style="text-align: center; padding: 3rem; color: #666;">
                            <i class="fas fa-shopping-cart" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p data-translate="no_orders">لا توجد طلبات حتى الآن</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th data-translate="order_id">#</th>
                                        <th data-translate="customer">العميل</th>
                                        <th data-translate="amount">المبلغ</th>
                                        <th data-translate="status">الحالة</th>
                                        <th data-translate="order_date">تاريخ الطلب</th>
                                        <th data-translate="actions">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><?php echo $order['id']; ?></td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($order['full_name'] ?: $order['username']); ?></strong><br>
                                                <small style="color: #666;"><?php echo $order['email']; ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?php echo number_format($order['total_amount'], 2); ?> <span data-translate="currency">ر.س</span></strong>
                                        </td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <select name="status" onchange="this.form.submit()" class="form-control status-<?php echo $order['status']; ?>">
                                                    <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?> data-translate="pending">قيد الانتظار</option>
                                                    <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?> data-translate="processing">قيد المعالجة</option>
                                                    <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?> data-translate="completed">مكتمل</option>
                                                    <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?> data-translate="cancelled">ملغي</option>
                                                </select>
                                                <input type="hidden" name="update_order_status" value="1">
                                            </form>
                                        </td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <div style="display: flex; gap: 5px;">
                                                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-primary">
                                                    <i class="fas fa-eye"></i> <span data-translate="view">عرض</span>
                                                </a>
                                                <a href="orders.php?delete_order=<?php echo $order['id']; ?>" class="btn btn-danger" 
                                                   onclick="return confirm(currentLang === 'ar' ? 'هل أنت متأكد من حذف هذا الطلب؟' : 'Are you sure you want to delete this order?')">
                                                    <i class="fas fa-trash"></i> <span data-translate="delete">حذف</span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- زر الترجمة العائم -->
    <button class="translate-btn" id="translateBtn">
        <i class="fas fa-language"></i>
    </button>

    <script>
        // نصوص الترجمة
        const translations = {
            ar: {
                    "dashboard": "لوحة التحكم الرئيسية",
                                "welcome": "مرحباً،",
                "home": "الرئيسية",
                "user_management": "إدارة المستخدمين",
                "product_management": "إدارة المنتجات",
                "service_management": "إدارة الخدمات",
                "order_management": "إدارة الطلبات",
                "about_us": "من نحن",
                "contact_info": "بيانات التواصل",
                "settings": "الإعدادات",
                "logout": "تسجيل الخروج",
                "profile": "الملف الشخصي",

                // العناوين الرئيسية
                "orders_management": "إدارة الطلبات",
                "orders_management_desc": "عرض وإدارة طلبات العملاء",
                "orders_list": "قائمة الطلبات",
                "no_orders": "لا توجد طلبات حتى الآن",
                
                // رؤوس الجدول
                "order_id": "#",
                "customer": "العميل",
                "amount": "المبلغ",
                "status": "الحالة",
                "order_date": "تاريخ الطلب",
                "actions": "الإجراءات",
                
                // حالات الطلبات
                "pending": "قيد الانتظار",
                "processing": "قيد المعالجة",
                "completed": "مكتمل",
                "cancelled": "ملغي",
                
                // الأزرار والإجراءات
                "view": "عرض",
                "delete": "حذف",
                "currency": "ر.س",
                
                // رسائل النجاح
                "status_updated": "تم تحديث حالة الطلب بنجاح",
                "order_deleted": "تم حذف الطلب بنجاح"
            },
            en: {
                
                  "dashboard": "Main Dashboard",
                     "welcome": "Welcome,",
                    "home": "Home",
                    "user_management": "User Management",
                    "product_management": "Product Management",
                    "service_management": "Service Management",
                    "order_management": "Order Management",
                    "about_us": "About Us",
                    "contact_info": "Contact Info",
                    "settings": "Settings",
                    "logout": "Logout",
                    "profile": "Profile",
                // العناوين الرئيسية
                "orders_management": "Orders Management",
                "orders_management_desc": "View and manage customer orders",
                "orders_list": "Orders List",
                "no_orders": "No orders yet",
                
                // رؤوس الجدول
                "order_id": "#",
                "customer": "Customer",
                "amount": "Amount",
                "status": "Status",
                "order_date": "Order Date",
                "actions": "Actions",
                
                // حالات الطلبات
                "pending": "Pending",
                "processing": "Processing",
                "completed": "Completed",
                "cancelled": "Cancelled",
                
                // الأزرار والإجراءات
                "view": "View",
                "delete": "Delete",
                "currency": "SAR",
                
                // رسائل النجاح
                "status_updated": "Order status updated successfully",
                "order_deleted": "Order deleted successfully"
            }
        };

        // حالة اللغة الحالية
        let currentLang = localStorage.getItem('language') || 'ar';

        // دالة لتطبيق الترجمة
        function applyLanguage(lang) {
            // تحديث النصوص في الصفحة
            document.querySelectorAll('[data-translate]').forEach(element => {
                const key = element.getAttribute('data-translate');
                if (translations[lang][key]) {
                    element.textContent = translations[lang][key];
                }
            });

            // تحديث خيارات التحديد
            document.querySelectorAll('select option').forEach(option => {
                const key = option.getAttribute('data-translate');
                if (key && translations[lang][key]) {
                    option.textContent = translations[lang][key];
                }
            });

            // تحديث اتجاه الصفحة
            if (lang === 'ar') {
                document.documentElement.dir = 'rtl';
                document.documentElement.lang = 'ar';
                document.title = 'إدارة الطلبات';
            } else {
                document.documentElement.dir = 'ltr';
                document.documentElement.lang = 'en';
                document.title = 'Orders Management';
            }

            // حفظ اللغة في localStorage
            localStorage.setItem('language', lang);
            currentLang = lang;
        }

        // حدث النقر على زر الترجمة
        document.getElementById('translateBtn').addEventListener('click', function() {
            const newLang = currentLang === 'ar' ? 'en' : 'ar';
            applyLanguage(newLang);
        });

        // تطبيق اللغة عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            applyLanguage(currentLang);
        });
    </script>
</body>
</html>