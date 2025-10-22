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
// $total_services = $stmt->fetch()['total_products'];

$stmt = $pdo->query("SELECT COUNT(*) as total_orders FROM orders");
$total_orders = $stmt->fetch()['total_orders'];

$stmt = $pdo->query("SELECT SUM(total_amount) as total_revenue FROM orders WHERE status = 'completed'");
$total_revenue = $stmt->fetch()['total_revenue'] ?: 0;

// آخر الطلبات
$stmt = $pdo->query("SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
$recent_orders = $stmt->fetchAll();

// آخر المستخدمين المسجلين
$stmt = $pdo->query("SELECT * FROM users WHERE user_type = 'client' ORDER BY created_at DESC LIMIT 5");
$recent_users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - الأدمن</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .header h1 {
            color: var(--dark);
            font-weight: 600;
        }

        .date-display {
            color: #64748b;
            font-size: 0.9rem;
        }

        /* بطاقات الإحصائيات */
        .row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: var(--transition);
            border: none;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
        }

        .stat-card {
            display: flex;
            align-items: center;
            padding: 1.5rem;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 1rem;
            font-size: 1.5rem;
            color: white;
        }

        .stat-content {
            flex: 1;
        }

        .stat-content h3 {
            font-size: 1.8rem;
            margin-bottom: 0.2rem;
            font-weight: 700;
        }

        .stat-content p {
            color: #64748b;
            font-size: 0.9rem;
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

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-success:hover {
            background: #38b2d6;
            color: white;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }

        /* الجداول */
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

        .badge {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        /* تخطيط الشبكة */
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        /* القائمة المنسدلة للمستخدم */
        .user-menu {
            position: relative;
            display: inline-block;
        }

        .user-dropdown {
            display: none;
            position: absolute;
            left: 0;
            top: 100%;
            background: white;
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border-radius: 8px;
            z-index: 1;
            overflow: hidden;
        }

        .user-menu:hover .user-dropdown {
            display: block;
        }

        .user-dropdown a {
            display: block;
            padding: 0.8rem 1rem;
            text-decoration: none;
            color: #333;
            transition: var(--transition);
        }

        .user-dropdown a:hover {
            background: #f5f7fb;
        }

        /* تذييل الصفحة */
        .footer {
            text-align: center;
            padding: 1.5rem;
            margin-top: 2rem;
            color: #64748b;
            font-size: 0.9rem;
            border-top: 1px solid #e2e8f0;
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
            
            .row {
                grid-template-columns: 1fr;
            }
            
            .grid-2 {
                grid-template-columns: 1fr;
            }
            
            .stat-card {
                flex-direction: column;
                text-align: center;
            }
            
            .stat-icon {
                margin-left: 0;
                margin-bottom: 1rem;
            }
        }

        /* تأثيرات إضافية */
        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(67, 97, 238, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(67, 97, 238, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(67, 97, 238, 0);
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
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
                <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> الرئيسية</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> إدارة المستخدمين</a></li>
                <li><a href="products.php"><i class="fas fa-box"></i> إدارة المنتجات</a></li>
                <li><a href="services.php"><i class="fas fa-concierge-bell"></i> إدارة الخدمات</a></li>
                <li><a href="about.php"><i class="fas fa-info-circle"></i> من نحن</a></li>
                <li><a href="contact_info.php"><i class="fas fa-address-book"></i> بيانات التواصل</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> الإعدادات</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
            </ul>
        </aside>

        <!-- المحتوى الرئيسي -->
        <main class="main-content">
            <div class="header">
                <h1>لوحة التحكم الرئيسية</h1>
                <div class="date-display" id="current-date">تحميل التاريخ...</div>
            </div>

            <!-- بطاقات الإحصائيات -->
            <div class="row">
                <div class="card stat-card fade-in">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $total_users; ?></h3>
                        <p>إجمالي العملاء</p>
                    </div>
                </div>
                
                <div class="card stat-card fade-in">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $total_products; ?></h3>
                        <p>إجمالي المنتجات</p>
                    </div>
                </div>
                
                <div class="card stat-card fade-in">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-concierge-bell"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $total_services; ?></h3>
                        <p>إجمالي الخدمات</p>
                    </div>
                </div>
                
                <div class="card stat-card fade-in">
                    <div class="stat-icon pulse" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $total_orders; ?></h3>
                        <p>إجمالي الطلبات</p>
                    </div>
                </div>
            </div>

            <!-- المحتوى الرئيسي -->
            <div class="grid-2">
                <!-- قسم سريع للإجراءات -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-bolt"></i> إجراءات سريعة</h3>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; grid-template-columns: 1fr; gap: 1rem;">
                            <a href="products.php?action=add" class="btn btn-primary">
                                <i class="fas fa-plus"></i> إضافة منتج جديد
                            </a>
                            <a href="services.php?action=add" class="btn btn-success">
                                <i class="fas fa-plus"></i> إضافة خدمة جديدة
                            </a>
                            <a href="users.php" class="btn btn-outline">
                                <i class="fas fa-users"></i> عرض جميع المستخدمين
                            </a>
                            <a href="settings.php" class="btn btn-outline">
                                <i class="fas fa-cog"></i> تعديل الإعدادات
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- آخر النشاطات -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-history"></i> آخر النشاطات</h3>
                    </div>
                    <div class="card-body">
                        <?php if(count($recent_orders) > 0): ?>
                            <div style="max-height: 300px; overflow-y: auto;">
                                <?php foreach($recent_orders as $order): ?>
                                    <div style="display: flex; justify-content: space-between; padding: 0.8rem 0; border-bottom: 1px solid #f1f5f9;">
                                        <div>
                                            <strong>طلب جديد</strong>
                                            <p style="margin: 0.2rem 0; font-size: 0.9rem;">من <?php echo $order['full_name']; ?></p>
                                        </div>
                                        <div style="text-align: left;">
                                            <span class="badge badge-success">جديد</span>
                                            <p style="margin: 0.2rem 0; font-size: 0.8rem; color: #64748b;"><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="text-align: center; color: #64748b; padding: 1rem;">لا توجد نشاطات حديثة</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- قسم إضافي للمستخدمين الجدد -->
            <div class="card" style="margin-top: 1.5rem;">
                <div class="card-header">
                    <h3><i class="fas fa-user-plus"></i> آخر العملاء المسجلين</h3>
                </div>
                <div class="card-body">
                    <?php if(count($recent_users) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>الاسم</th>
                                        <th>البريد الإلكتروني</th>
                                        <th>تاريخ التسجيل</th>
                                        <th>الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recent_users as $user): ?>
                                        <tr>
                                            <td><?php echo $user['full_name']; ?></td>
                                            <td><?php echo $user['email']; ?></td>
                                            <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                            <td><span class="badge badge-success">نشط</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p style="text-align: center; color: #64748b; padding: 1rem;">لا يوجد عملاء مسجلين حديثاً</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- تذييل الصفحة -->
            <div class="footer">
                <p>جميع الحقوق محفوظة &copy; <?php echo date('Y'); ?> - نظام الإدارة</p>
            </div>
        </main>
    </div>

    <script>
        // عرض التاريخ الحالي
        function updateDate() {
            const now = new Date();
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                timeZone: 'Asia/Riyadh'
            };
            const dateString = now.toLocaleDateString('ar-SA', options);
            document.getElementById('current-date').textContent = dateString;
        }
        
        // تحديث التاريخ عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            updateDate();
            
            // إضافة تأثيرات للبطاقات عند التمرير
            const cards = document.querySelectorAll('.card');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });
            
            cards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                observer.observe(card);
            });
        });
    </script>
</body>
</html>