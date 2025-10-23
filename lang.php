# إضافة أيقونة الترجمة للوحة التحكم

لإضافة أيقونة الترجمة للتبديل بين اللغتين العربية والإنجليزية، سأقوم بالتعديلات التالية على الكود:

1. إضافة زر الترجمة في واجهة المستخدم
2. إضافة كود JavaScript للتعامل مع الترجمة
3. إضافة الأنماط اللازمة

إليك الكود المحدث:

```html
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
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-history"></i> <span data-translate="recent_activities">آخر النشاطات</span></h3>
                    </div>
                    <div class="card-body">
                        <?php if(count($recent_orders) > 0): ?>
                            <div style="max-height: 300px; overflow-y: auto;">
                                <?php foreach($recent_orders as $order): ?>
                                    <div style="display: flex; justify-content: space-between; padding: 0.8rem 0; border-bottom: 1px solid #f1f5f9;">
                                        <div>
                                            <strong data-translate="new_order">طلب جديد</strong>
                                            <p style="margin: 0.2rem 0; font-size: 0.9rem;" data-translate="from">من <?php echo $order['full_name']; ?></p>
                                        </div>
                                        <div style="text-align: left;">
                                            <span class="badge badge-success" data-translate="new">جديد</span>
                                            <p style="margin: 0.2rem 0; font-size: 0.8rem; color: #64748b;"><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="text-align: center; color: #64748b; padding: 1rem;" data-translate="no_recent_activities">لا توجد نشاطات حديثة</p>
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
                // العناوين الرئيسية
                "dashboard": "لوحة التحكم الرئيسية",
                "total_clients": "إجمالي العملاء",
                "total_products": "إجمالي المنتجات",
                "total_services": "إجمالي الخدمات",
                "total_orders": "إجمالي الطلبات",
                "quick_actions": "إجراءات سريعة",
                "add_new_product": "إضافة منتج جديد",
                "add_new_service": "إضافة خدمة جديدة",
                "view_all_users": "عرض جميع المستخدمين",
                "edit_settings": "تعديل الإعدادات",
                "recent_activities": "آخر النشاطات",
                "new_order": "طلب جديد",
                "from": "من",
                "new": "جديد",
                "no_recent_activities": "لا توجد نشاطات حديثة",
                "order_distribution": "توزيع الطلبات حسب الحالة",
                "monthly_revenue": "الإيرادات الشهرية",
                "user_growth": "نمو المستخدمين"
            },
            en: {
                // العناوين الرئيسية
                "dashboard": "Main Dashboard",
                "total_clients": "Total Clients",
                "total_products": "Total Products",
                "total_services": "Total Services",
                "total_orders": "Total Orders",
                "quick_actions": "Quick Actions",
                "add_new_product": "Add New Product",
                "add_new_service": "Add New Service",
                "view_all_users": "View All Users",
                "edit_settings": "Edit Settings",
                "recent_activities": "Recent Activities",
                "new_order": "New Order",
                "from": "From",
                "new": "New",
                "no_recent_activities": "No Recent Activities",
                "order_distribution": "Order Distribution by Status",
                "monthly_revenue": "Monthly Revenue",
                "user_growth": "User Growth"
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
                document.title = 'لوحة التحكم - الأدمن';
            } else {
                document.documentElement.dir = 'ltr';
                document.documentElement.lang = 'en';
                document.title = 'Admin Dashboard';
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
            
            // عرض التاريخ الحالي
            const now = new Date();
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            };
            const dateString = currentLang === 'ar' 
                ? now.toLocaleDateString('ar-SA', options)
                : now.toLocaleDateString('en-US', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
            document.getElementById('current-date').textContent = dateString;
        });

        // الرسوم البيانية
        document.addEventListener('DOMContentLoaded', function() {
            // رسم بياني دائري لتوزيع الطلبات
            const ordersCtx = document.getElementById('ordersChart').getContext('2d');
            const ordersChart = new Chart(ordersCtx, {
                type: 'pie',
                data: {
                    labels: ['مكتمل', 'قيد المعالجة', 'ملغي'],
                    datasets: [{
                        data: [60, 25, 15],
                        backgroundColor: [
                            '#43e97b',
                            '#4facfe',
                            '#f5576c'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // رسم بياني عمودي للإيرادات
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            const revenueChart = new Chart(revenueCtx, {
                type: 'bar',
                data: {
                    labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
                    datasets: [{
                        label: 'الإيرادات',
                        data: [12000, 19000, 15000, 25000, 22000, 30000],
                        backgroundColor: '#4361ee'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // رسم بياني خطي لنمو المستخدمين
            const usersCtx = document.getElementById('usersChart').getContext('2d');
            const usersChart = new Chart(usersCtx, {
                type: 'line',
                data: {
                    labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
                    datasets: [{
                        label: 'المستخدمين الجدد',
                        data: [65, 79, 90, 81, 96, 125],
                        borderColor: '#f093fb',
                        backgroundColor: 'rgba(240, 147, 251, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
```

## التغييرات التي تمت:

1. **إضافة زر الترجمة العائم**: تم إضافة زر عائم في الزاوية اليسرى السفلية للصفحة.

2. **إضافة نظام الترجمة**: 
   - تم إضافة كائن `translations` يحتوي على النصوص باللغتين العربية والإنجليزية.
   - تم إضافة خاصية `data-translate` للعناصر التي تحتاج إلى ترجمة.
   - تم إضافة وظيفة `applyLanguage` لتطبيق الترجمة المحددة.

3. **إضافة الأنماط للغة الإنجليزية**:
   - تم إضافة أنماط CSS للتعامل مع اتجاه النص من اليمين لليسار عند التبديل إلى الإنجليزية.

4. **تخزين التفضيلات**: 
   - يتم تخزين اللغة المفضلة للمستخدم في `localStorage` للحفاظ على التحديد عند إعادة تحميل الصفحة.

5. **تحديث التاريخ**: 
   - يتم عرض التاريخ الحالي باللغة المحددة.

الآن يمكن للمستخدم النقر على أيقونة الترجمة للتبديل بين اللغتين العربية والإنجليزية، وسيتم حفظ تفضيلاته تلقائيًا.