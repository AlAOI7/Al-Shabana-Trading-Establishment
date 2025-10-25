<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}


if (!isset($_GET['id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = $_GET['id'];

// جلب بيانات الطلب
$stmt = $pdo->prepare("
    SELECT o.*, u.username, u.full_name, u.email, u.phone 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: orders.php");
    exit();
}

// جلب عناصر الطلب
$stmt = $pdo->prepare("
    SELECT oi.*, p.name as product_name, p.image_path 
    FROM order_items oi 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");

$order_items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <title data-translate="order_details">تفاصيل الطلب</title> <?php echo $order_id; ?></title> -->
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
            padding: 0.7rem 1.2rem;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--secondary);
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            color: white;
        }

        /* شبكة الصفوف والأعمدة */
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -0.75rem;
            margin-bottom: 1.5rem;
        }

        .col-6 {
            flex: 0 0 50%;
            max-width: 50%;
            padding: 0 0.75rem;
        }

        /* معلومات العميل */
        .customer-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .customer-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 1rem;
        }

        .info-box {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
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

        tfoot tr {
            background: #f8f9fa !important;
        }

        /* حالة الطلب */
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            display: inline-block;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #d1ecf1; color: #0c5460; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }

        /* عناصر الطلب */
        .order-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .product-image {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 5px;
        }

        .product-placeholder {
            width: 40px;
            height: 40px;
            background: #eee;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

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

        body[dir="ltr"] .table th, 
        body[dir="ltr"] .table td {
            text-align: left;
        }

        body[dir="ltr"] .customer-info {
            flex-direction: row;
        }

        body[dir="ltr"] tfoot td:first-child {
            text-align: right;
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
            
            .col-6 {
                flex: 0 0 100%;
                max-width: 100%;
                margin-bottom: 1rem;
            }
            
            .table-responsive {
                font-size: 0.8rem;
            }
            
            .table th, .table td {
                padding: 0.5rem;
            }
            
            .customer-info {
                flex-direction: column;
                text-align: center;
            }
        }

        @media (max-width: 576px) {
            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
            
            .table-responsive {
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
                <h1><i class="fas fa-file-invoice"></i> <span data-translate="order_details">تفاصيل الطلب</span> #<?php echo $order_id; ?></h1>
                <p data-translate="order_details_desc">عرض التفاصيل الكاملة للطلب</p>
            </div>

            <div class="row">
                <!-- معلومات العميل -->
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-user"></i> <span data-translate="customer_info">معلومات العميل</span></h3>
                        </div>
                        <div class="card-body">
                            <div class="customer-info">
                                <div class="customer-avatar">
                                    <?php echo strtoupper(substr($order['full_name'] ?: $order['username'], 0, 1)); ?>
                                </div>
                                <div>
                                    <!-- <h4 style="margin: 0;"><?php echo htmlspecialchars($order['full_name'] ?: $order['username']); ?></h4> -->
                                    <p style="margin: 0; color: #666;">@<?php echo $order['username']; ?></p>
                                </div>
                            </div>
                            
                            <div class="info-box">
                                <p><strong><i class="fas fa-envelope"></i> <span data-translate="email">البريد الإلكتروني:</span></strong><br>
                                <?php echo $order['email']; ?></p>
                                
                                <?php if ($order['phone']): ?>
                                <p><strong><i class="fas fa-phone"></i> <span data-translate="phone">الهاتف:</span></strong><br>
                                <?php echo $order['phone']; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- معلومات الطلب -->
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-info-circle"></i> <span data-translate="order_info">معلومات الطلب</span></h3>
                        </div>
                        <div class="card-body">
                            <div class="info-box">
                                <p><strong><i class="fas fa-hashtag"></i> <span data-translate="order_id">رقم الطلب:</span></strong> #<?php echo $order['id']; ?></p>
                                <p><strong><i class="fas fa-calendar"></i> <span data-translate="order_date">تاريخ الطلب:</span></strong> <?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></p>
                                <p><strong><i class="fas fa-money-bill-wave"></i> <span data-translate="total_amount">المبلغ الإجمالي:</span></strong> 
                                    <span style="font-size: 1.2rem; font-weight: bold; color: var(--success);">
                                        <?php echo number_format($order['total_amount'], 2); ?> <span data-translate="currency">ر.س</span>
                                    </span>
                                </p>
                                <p><strong><i class="fas fa-tag"></i> <span data-translate="status">الحالة:</span></strong> 
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <span data-translate="<?php echo $order['status']; ?>">
                                            <?php 
                                            switch($order['status']) {
                                                case 'pending': echo 'قيد الانتظار'; break;
                                                case 'processing': echo 'قيد المعالجة'; break;
                                                case 'completed': echo 'مكتمل'; break;
                                                case 'cancelled': echo 'ملغي'; break;
                                            }
                                            ?>
                                        </span>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- عناصر الطلب -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-boxes"></i> <span data-translate="order_items">عناصر الطلب</span></h3>
                </div>
                <div class="card-body">
                    <?php if (empty($order_items)): ?>
                        <div style="text-align: center; padding: 2rem; color: #666;">
                            <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p data-translate="no_order_items">لا توجد عناصر في هذا الطلب</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th data-translate="item_number">#</th>
                                        <th data-translate="product">المنتج</th>
                                        <th data-translate="price">السعر</th>
                                        <th data-translate="quantity">الكمية</th>
                                        <th data-translate="total">المجموع</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_items as $index => $item): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td>
                                            <div class="order-item">
                                                <?php if ($item['image_path']): ?>
                                                    <img src="../<?php echo $item['image_path']; ?>" alt="<?php echo $item['product_name']; ?>" class="product-image">
                                                <?php else: ?>
                                                    <div class="product-placeholder">
                                                        <i class="fas fa-box" style="color: #999;"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo number_format($item['price'], 2); ?> <span data-translate="currency">ر.س</span></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><strong><?php echo number_format($item['price'] * $item['quantity'], 2); ?> <span data-translate="currency">ر.س</span></strong></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" style="text-align: left; font-weight: bold;" data-translate="grand_total">المجموع الإجمالي:</td>
                                        <td style="font-weight: bold; font-size: 1.1rem; color: var(--success);">
                                            <?php echo number_format($order['total_amount'], 2); ?> <span data-translate="currency">ر.س</span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- عنوان الشحن -->
            <?php if ($order['shipping_address']): ?>
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-truck"></i> <span data-translate="shipping_address">عنوان الشحن</span></h3>
                </div>
                <div class="card-body">
                    <div class="info-box">
                        <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- الملاحظات -->
            <?php if ($order['notes']): ?>
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-sticky-note"></i> <span data-translate="order_notes">ملاحظات الطلب</span></h3>
                </div>
                <div class="card-body">
                    <div class="info-box">
                        <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- أزرار الإجراءات -->
            <div style="display: flex; gap: 10px; margin-top: 2rem;">
                <a href="orders.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-right"></i> <span data-translate="back_to_orders">العودة إلى الطلبات</span>
                </a>
                <a href="print_order.php?id=<?php echo $order_id; ?>" target="_blank" class="btn btn-primary">
                    <i class="fas fa-print"></i> <span data-translate="print_order">طباعة الطلب</span>
                </a>
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
                "order_details": "تفاصيل الطلب",
                "order_details_desc": "عرض التفاصيل الكاملة للطلب",
                "customer_info": "معلومات العميل",
                "order_info": "معلومات الطلب",
                "order_items": "عناصر الطلب",
                "shipping_address": "عنوان الشحن",
                "order_notes": "ملاحظات الطلب",
                
                // معلومات العميل
                "email": "البريد الإلكتروني",
                "phone": "الهاتف",
                
                // معلومات الطلب
                "order_id": "رقم الطلب",
                "order_date": "تاريخ الطلب",
                "total_amount": "المبلغ الإجمالي",
                "status": "الحالة",
                "currency": "ر.س",
                
                // حالات الطلب
                "pending": "قيد الانتظار",
                "processing": "قيد المعالجة",
                "completed": "مكتمل",
                "cancelled": "ملغي",
                
                // عناصر الطلب
                "item_number": "#",
                "product": "المنتج",
                "price": "السعر",
                "quantity": "الكمية",
                "total": "المجموع",
                "grand_total": "المجموع الإجمالي",
                "no_order_items": "لا توجد عناصر في هذا الطلب",
                
                // أزرار الإجراءات
                "back_to_orders": "العودة إلى الطلبات",
                "print_order": "طباعة الطلب"
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
                "order_details": "Order Details",
                "order_details_desc": "View complete order details",
                "customer_info": "Customer Information",
                "order_info": "Order Information",
                "order_items": "Order Items",
                "shipping_address": "Shipping Address",
                "order_notes": "Order Notes",
                
                // معلومات العميل
                "email": "Email",
                "phone": "Phone",
                
                // معلومات الطلب
                "order_id": "Order ID",
                "order_date": "Order Date",
                "total_amount": "Total Amount",
                "status": "Status",
                "currency": "SAR",
                
                // حالات الطلب
                "pending": "Pending",
                "processing": "Processing",
                "completed": "Completed",
                "cancelled": "Cancelled",
                
                // عناصر الطلب
                "item_number": "#",
                "product": "Product",
                "price": "Price",
                "quantity": "Quantity",
                "total": "Total",
                "grand_total": "Grand Total",
                "no_order_items": "No items in this order",
                
                // أزرار الإجراءات
                "back_to_orders": "Back to Orders",
                "print_order": "Print Order"
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

            // تحديث اتجاه الصفحة
            if (lang === 'ar') {
                document.documentElement.dir = 'rtl';
                document.documentElement.lang = 'ar';
                document.title = 'تفاصيل الطلب #<?php echo $order_id; ?>';
            } else {
                document.documentElement.dir = 'ltr';
                document.documentElement.lang = 'en';
                document.title = 'Order Details #<?php echo $order_id; ?>';
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