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
$total_services = $stmt->fetch()['total_services'];

$stmt = $pdo->query("SELECT COUNT(*) as total_orders FROM orders");
$total_orders = $stmt->fetch()['total_orders'];

$stmt = $pdo->query("SELECT SUM(total_amount) as total_revenue FROM orders WHERE status = 'completed'");
$total_revenue = $stmt->fetch()['total_revenue'] ?: 0;

// آخر الطلبات مع معلومات أكثر
$stmt = $pdo->query("SELECT o.*, u.full_name, u.email 
                     FROM orders o 
                     JOIN users u ON o.user_id = u.id 
                     ORDER BY o.created_at DESC LIMIT 5");
$recent_orders = $stmt->fetchAll();

// آخر المستخدمين المسجلين
$stmt = $pdo->query("SELECT * FROM users WHERE user_type = 'client' ORDER BY created_at DESC LIMIT 5");
$recent_users = $stmt->fetchAll();

// بيانات إضافية للنشاطات
$stmt = $pdo->query("SELECT 'order' as type, created_at, 'طلب جديد' as activity, id FROM orders 
                     UNION ALL 
                     SELECT 'user' as type, created_at, 'مستخدم جديد' as activity, id FROM users 
                     WHERE user_type = 'client'
                     ORDER BY created_at DESC LIMIT 10");
$recent_activities = $stmt->fetchAll();
?>


<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - الأدمن</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- إضافة مكتبة Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
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
            
            .grid-2, .grid-3 {
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

        /* تنسيقات الرسوم البيانية */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
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

        body[dir="ltr"] .stat-icon {
            margin-left: 0;
            margin-right: 1rem;
        }

        body[dir="ltr"] .table th, 
        body[dir="ltr"] .table td {
            text-align: left;
        }

        body[dir="ltr"] .user-dropdown {
            left: auto;
            right: 0;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'sidebar.php'; ?>
        
        <!-- المحتوى الرئيسي -->
        <main class="main-content">
            <?php include 'admin_navbar.php'; ?>
           

            <!-- بطاقات الإحصائيات -->
            <div class="row">
                <div class="card stat-card fade-in">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $total_users; ?></h3>
                        <p data-translate="total_clients">إجمالي العملاء</p>
                    </div>
                </div>
                
                <div class="card stat-card fade-in">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $total_products; ?></h3>
                        <p data-translate="total_products">إجمالي المنتجات</p>
                    </div>
                </div>
                
                <div class="card stat-card fade-in">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-concierge-bell"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $total_services; ?></h3>
                        <p data-translate="total_services">إجمالي الخدمات</p>
                    </div>
                </div>
                
                <div class="card stat-card fade-in">
                    <div class="stat-icon pulse" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $total_orders; ?></h3>
                        <p data-translate="total_orders">إجمالي الطلبات</p>
                    </div>
                </div>
            </div>

            <!-- المحتوى الرئيسي -->
            <div class="grid-3">
                <!-- قسم سريع للإجراءات -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-bolt"></i> <span data-translate="quick_actions">إجراءات سريعة</span></h3>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; grid-template-columns: 1fr; gap: 1rem;">
                            <a href="products.php?action=add" class="btn btn-primary">
                                <i class="fas fa-plus"></i> <span data-translate="add_new_product">إضافة منتج جديد</span>
                            </a>
                            <a href="services.php?action=add" class="btn btn-success">
                                <i class="fas fa-plus"></i> <span data-translate="add_new_service">إضافة خدمة جديدة</span>
                            </a>
                            <a href="users.php" class="btn btn-outline">
                                <i class="fas fa-users"></i> <span data-translate="view_all_users">عرض جميع المستخدمين</span>
                            </a>
                            <a href="settings.php" class="btn btn-outline">
                                <i class="fas fa-cog"></i> <span data-translate="edit_settings">تعديل الإعدادات</span>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- آخر النشاطات -->
              
                    <!-- آخر النشاطات - نسخة محسنة -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-history"></i> <span data-translate="recent_activities">آخر النشاطات</span></h3>
                    </div>
                    <div class="card-body">
                        <?php if(count($recent_activities) > 0): ?>
                            <div style="max-height: 300px; overflow-y: auto;">
                                <?php foreach($recent_activities as $activity): ?>
                                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.8rem 0; border-bottom: 1px solid #f1f5f9;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 40px; height: 40px; border-radius: 50%; background: <?php echo $activity['type'] == 'order' ? '#e3f2fd' : '#f3e5f5'; ?>; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas <?php echo $activity['type'] == 'order' ? 'fa-shopping-cart' : 'fa-user-plus'; ?>" 
                                                style="color: <?php echo $activity['type'] == 'order' ? '#1976d2' : '#7b1fa2'; ?>;"></i>
                                            </div>
                                            <div>
                                                <strong data-translate="<?php echo $activity['type'] == 'order' ? 'new_order' : 'new_user'; ?>">
                                                    <?php echo $activity['activity']; ?>
                                                </strong>
                                                <p style="margin: 0.2rem 0; font-size: 0.9rem; color: #64748b;">
                                                    <?php if($activity['type'] == 'order'): ?>
                                                        رقم الطلب: #<?php echo $activity['id']; ?>
                                                    <?php else: ?>
                                                        رقم المستخدم: #<?php echo $activity['id']; ?>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div style="text-align: left;">
                                            <span class="badge badge-<?php echo $activity['type'] == 'order' ? 'success' : 'info'; ?>" 
                                                data-translate="<?php echo $activity['type'] == 'order' ? 'new' : 'active'; ?>">
                                                <?php echo $activity['type'] == 'order' ? 'جديد' : 'نشط'; ?>
                                            </span>
                                            <p style="margin: 0.2rem 0; font-size: 0.8rem; color: #64748b;">
                                                <?php echo date('Y-m-d H:i', strtotime($activity['created_at'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="text-align: center; color: #64748b; padding: 1rem;" data-translate="no_recent_activities">
                                لا توجد نشاطات حديثة
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <!-- الرسوم البيانية -->
            <div class="grid-2">
                <!-- رسم بياني دائري لتوزيع الطلبات -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-pie"></i> <span data-translate="order_distribution">توزيع الطلبات حسب الحالة</span></h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="ordersChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- رسم بياني عمودي للإيرادات -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-bar"></i> <span data-translate="monthly_revenue">الإيرادات الشهرية</span></h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- رسم بياني خطي لنمو المستخدمين -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-line"></i> <span data-translate="user_growth">نمو المستخدمين</span></h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="usersChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
                <!-- قسم إضافي للمستخدمين الجدد -->
       
                            <!-- قسم إضافي للمستخدمين الجدد - نسخة محسنة -->
                <div class="card" style="margin-top: 2.5rem;">
                    <div class="card-header">
                        <h3><i class="fas fa-user-plus"></i> <span data-translate="recent_clients">آخر العملاء المسجلين</span></h3>
                    </div>
                    <div class="card-body">
                        <?php if(count($recent_users) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th data-translate="name">الاسم</th>
                                            <th data-translate="email">البريد الإلكتروني</th>
                                            <th data-translate="phone">الهاتف</th>
                                            <th data-translate="registration_date">تاريخ التسجيل</th>
                                            <th data-translate="status">الحالة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($recent_users as $user): ?>
                                            <tr>
                                                <td>
                                                    <div style="display: flex; align-items: center; gap: 8px;">
                                                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #e3f2fd; display: flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-user" style="color: #1976d2; font-size: 0.8rem;"></i>
                                                        </div>
                                                        <?php echo htmlspecialchars($user['full_name']); ?>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td><?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : '<span style="color: #999;">غير محدد</span>'; ?></td>
                                                <td>
                                                    <span style="font-size: 0.85rem;">
                                                        <?php echo date('Y-m-d', strtotime($user['created_at'])); ?>
                                                    </span>
                                                    <br>
                                                    <span style="font-size: 0.75rem; color: #64748b;">
                                                        <?php echo date('H:i', strtotime($user['created_at'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $status_class = 'success';
                                                    $status_text = 'نشط';
                                                    
                                                    if(isset($user['is_active']) && $user['is_active'] == 0) {
                                                        $status_class = 'secondary';
                                                        $status_text = 'غير نشط';
                                                    } elseif(isset($user['email_verified']) && $user['email_verified'] == 0) {
                                                        $status_class = 'warning';
                                                        $status_text = 'بانتظار التفعيل';
                                                    }
                                                    ?>
                                                    <span class="badge badge-<?php echo $status_class; ?>">
                                                        <?php echo $status_text; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div style="text-align: left; margin-top: 1rem;">
                                <a href="users.php" class="btn btn-outline-primary btn-sm" data-translate="view_all">
                                    عرض جميع العملاء
                                </a>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; padding: 2rem;">
                                <i class="fas fa-users" style="font-size: 3rem; color: #e2e8f0; margin-bottom: 1rem;"></i>
                                <p style="color: #64748b; margin-bottom: 1rem;" data-translate="no_recent_clients">
                                    لا يوجد عملاء مسجلين حديثاً
                                </p>
                                <a href="users.php?action=add" class="btn btn-primary" data-translate="add_first_client">
                                    إضافة أول عميل
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <!-- تذييل الصفحة -->
            <div class="footer">
                <p>جميع الحقوق محفوظة &copy; <?php echo date('Y'); ?> - نظام الإدارة</p>
            </div>
        </main>
    </div>

    <!-- زر الترجمة العائم -->
    <button class="translate-btn" id="translateBtn">
        <i class="fas fa-language"></i>
    </button>

        <script src="script.js"></script>
</body>
</html>
 