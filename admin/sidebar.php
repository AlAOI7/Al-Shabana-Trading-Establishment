<?php
// sidebar.php - يمكن تضمينه في جميع صفحات الأدمن
// يجب أن تكون $_SESSION['full_name'] متوفرة
// يجب أن يكون $current_page_basename متوفرة لتعيين الحالة النشطة

// نصوص الترجمة
$sidebar_translations = [
    'ar' => [
        'dashboard' => 'لوحة التحكم',
        'welcome' => 'مرحباً،',
        'home' => 'الرئيسية',
        'user_management' => 'إدارة المستخدمين',
        'product_management' => 'إدارة المنتجات',
        'service_management' => 'إدارة الخدمات',
        'order_management' => 'إدارة الطلبات',
        'about_us' => 'من نحن',
        'contact_info' => 'بيانات التواصل',
        'import_export' => 'استيراد / تصدير البيانات', 
                'import' => 'استيراد  ', 

        'settings' => 'الإعدادات',
        'logout' => 'تسجيل الخروج',
        'faq' =>'إدارة الأسئلة الشائعة'
    ],
    'en' => [
        'dashboard' => 'Dashboard',
        'welcome' => 'Welcome,',
        'home' => 'Home',
        'user_management' => 'User Management',
        'product_management' => 'Product Management',
        'service_management' => 'Service Management',
        'order_management' => 'Order Management',
        'about_us' => 'About Us',
        'contact_info' => 'Contact Info',
           'import_export' => 'Import / Export Data',
                      'import' => 'Import ',
        'settings' => 'Settings',
        'logout' => 'Logout',
        'faq' => 'faq'
    ]
];

// تحديد اللغة الحالية
$currentLang = isset($_SESSION['language']) ? $_SESSION['language'] : 'ar';

// القائمة حسب اللغة
$menu_items = [
    'dashboard.php' => [
        'icon' => 'fas fa-home',
        'text_key' => 'home'
    ],
    'users.php' => [
        'icon' => 'fas fa-users',
        'text_key' => 'user_management'
    ],
    'products.php' => [
        'icon' => 'fas fa-box',
        'text_key' => 'product_management'
    ],
    'services.php' => [
        'icon' => 'fas fa-concierge-bell',
        'text_key' => 'service_management'
    ],
    'orders.php' => [
        'icon' => 'fas fa-shopping-cart',
        'text_key' => 'order_management'
    ],
    'about.php' => [
        'icon' => 'fas fa-info-circle',
        'text_key' => 'about_us'
    ],
        'import_export.php' => [
        'icon' => 'fas fa-file-import',
        'text_key' => 'import_export'
    ],

        'import.php' => [
        'icon' => 'fas fa-file-import',
        'text_key' => 'import'
    ],

    'contact_info.php' => [
        'icon' => 'fas fa-address-book',
        'text_key' => 'contact_info'
    ],
     'faq_management.php' => [
        'icon' => 'fas fa-question-circle',
        'text_key' => 'faq'
    ],
    'settings.php' => [
        'icon' => 'fas fa-cogs',
        'text_key' => 'settings'
    ]
];
?>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-tachometer-alt"></i> <span data-translate="dashboard"><?php echo $sidebar_translations[$currentLang]['dashboard']; ?></span></h3>
        <p><span data-translate="welcome"><?php echo $sidebar_translations[$currentLang]['welcome']; ?></span> <?php echo $_SESSION['full_name'] ?? 'Admin'; ?></p>
    </div>
    
    <ul class="sidebar-menu">
        <?php foreach ($menu_items as $file => $item): ?>
            <li>
                <a href="<?php echo $file; ?>" class="<?php echo basename($_SERVER['PHP_SELF']) == $file ? 'active' : ''; ?>">
                    <i class="<?php echo $item['icon']; ?>"></i> 
                    <span data-translate="<?php echo $item['text_key']; ?>"><?php echo $sidebar_translations[$currentLang][$item['text_key']]; ?></span>
                </a>
            </li>
        <?php endforeach; ?>
        
        <li>
            <a href="../logout.php">
                <i class="fas fa-sign-out-alt"></i> 
                <span data-translate="logout"><?php echo $sidebar_translations[$currentLang]['logout']; ?></span>
            </a>
        </li>
    </ul>
</aside>