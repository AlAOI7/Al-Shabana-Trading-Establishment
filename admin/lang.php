<?php
// lang.php
// تعريف لغة العمل الحالية (يمكن تغييرها بناءً على جلسة المستخدم أو إعدادات النظام)
// لنفترض أننا نريد الإنجليزية (en) كبديل للعربية (ar)
$current_lang = 'en'; 

// مصفوفة الترجمة
$lang = [
    'ar' => [
        'dashboard_title' => 'لوحة التحكم - الأدمن',
        'dashboard_main' => 'لوحة التحكم الرئيسية',
        'loading_date' => 'تحميل التاريخ...',
        'total_clients' => 'إجمالي العملاء',
        'total_products' => 'إجمالي المنتجات',
        'total_services' => 'إجمالي الخدمات',
        'total_orders' => 'إجمالي الطلبات',
        'quick_actions' => 'إجراءات سريعة',
        'add_new_product' => 'إضافة منتج جديد',
        'add_new_service' => 'إضافة خدمة جديدة',
        'view_all_users' => 'عرض جميع المستخدمين',
        'edit_settings' => 'تعديل الإعدادات',
        'recent_activity' => 'آخر النشاطات',
        'new_order' => 'طلب جديد',
        'from' => 'من',
        'new_badge' => 'جديد',
        'no_recent_activity' => 'لا توجد نشاطات حديثة',
        'order_distribution' => 'توزيع الطلبات حسب الحالة',
        'monthly_revenue' => 'الإيرادات الشهرية',
        'user_growth' => 'نمو المستخدمين',
        'recent_registered_clients' => 'آخر العملاء المسجلين',
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'registration_date' => 'تاريخ التسجيل',
        'status' => 'الحالة',
        'active_badge' => 'نشط',
        'no_recent_clients' => 'لا يوجد عملاء مسجلين حديثاً',
        'all_rights_reserved' => 'جميع الحقوق محفوظة',
        'management_system' => 'نظام الإدارة',
        'users_count' => 'عدد المستخدمين',
        'sar' => 'ر.س', // ريال سعودي
        'revenue' => 'الإيرادات',

        // حالات الطلب (يجب أن تتطابق مع قيم قاعدة البيانات)
        'completed' => 'مكتمل',
        'pending' => 'قيد الانتظار',
        'canceled' => 'ملغى',
        // أضف المزيد من الحالات هنا...
    ],
    'en' => [
        'dashboard_title' => 'Admin Dashboard',
        'dashboard_main' => 'Main Dashboard',
        'loading_date' => 'Loading Date...',
        'total_clients' => 'Total Clients',
        'total_products' => 'Total Products',
        'total_services' => 'Total Services',
        'total_orders' => 'Total Orders',
        'quick_actions' => 'Quick Actions',
        'add_new_product' => 'Add New Product',
        'add_new_service' => 'Add New Service',
        'view_all_users' => 'View All Users',
        'edit_settings' => 'Edit Settings',
        'recent_activity' => 'Recent Activity',
        'new_order' => 'New Order',
        'from' => 'From',
        'new_badge' => 'New',
        'no_recent_activity' => 'No recent activity',
        'order_distribution' => 'Order Distribution by Status',
        'monthly_revenue' => 'Monthly Revenue',
        'user_growth' => 'User Growth',
        'recent_registered_clients' => 'Recently Registered Clients',
        'name' => 'Name',
        'email' => 'Email',
        'registration_date' => 'Registration Date',
        'status' => 'Status',
        'active_badge' => 'Active',
        'no_recent_clients' => 'No recently registered clients',
        'all_rights_reserved' => 'All Rights Reserved',
        'management_system' => 'Management System',
        'users_count' => 'User Count',
        'sar' => 'SAR',
        'revenue' => 'Revenue',

        // حالات الطلب (يجب أن تتطابق مع قيم قاعدة البيانات)
        'completed' => 'Completed',
        'pending' => 'Pending',
        'canceled' => 'Canceled',
        // Add more statuses here...
    ]
];

// دالة للترجمة
function translate($key) {
    global $lang, $current_lang;
    return $lang[$current_lang][$key] ?? $lang['ar'][$key] ?? $key; // العودة للمفتاح إذا لم توجد ترجمة
}

// دالة لترجمة حالة الطلب
function translate_status($status) {
    global $lang, $current_lang;
    $status_key = strtolower($status); // تحويل الحالة إلى مفتاح صغير
    return $lang[$current_lang][$status_key] ?? $status; // العودة للحالة الأصلية إذا لم توجد ترجمة
}

?>