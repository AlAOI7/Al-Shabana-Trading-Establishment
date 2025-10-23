
        // نصوص الترجمة
        const translations = {
            ar: {
                  // نصوص جديدة للشريط الجانبي وشريط التنقل
    
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