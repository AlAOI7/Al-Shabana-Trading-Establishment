<?php
// admin_navbar.php - Top Navigation Bar for Admin Dashboard
// Assuming config.php is already included in the main file (dashboard.php)
// يُفترض أن ملف config.php تم تضمينه بالفعل في الملف الرئيسي (dashboard.php)
?>
<?php
// admin_navbar.php - شريط التنقل العلوي
// نصوص الترجمة
$navbar_translations = [
    'ar' => [
        'dashboard' => 'لوحة التحكم الرئيسية',
        'admin' => 'Admin',
        'profile' => 'الملف الشخصي',
        'settings' => 'الإعدادات',
        'logout' => 'تسجيل الخروج'
    ],
    'en' => [
        'dashboard' => 'Main Dashboard',
        'admin' => 'Admin',
        'profile' => 'Profile',
        'settings' => 'Settings',
        'logout' => 'Logout'
    ]
];

// تحديد اللغة الحالية
$currentLang = isset($_SESSION['language']) ? $_SESSION['language'] : 'ar';
?>

<header class="top-navbar">
    <div class="header-left">
        <h1 data-translate="dashboard"><?php echo $navbar_translations[$currentLang]['dashboard']; ?> <span style="font-size: 0.9rem; font-weight: 300; color: #64748b;">(<?php echo $navbar_translations[$currentLang]['admin']; ?>)</span></h1>
    </div>

    <div class="header-right">
        <a href="dashboard.php"><i class="fas fa-home"></i></a>  
        <div class="notifications-icon">
            <i class="fas fa-bell"></i>
            <span class="badge badge-danger notification-count">5</span>
        </div>

        <div class="user-menu">
            <button class="user-info-btn">
                <img src="https://via.placeholder.com/40/4361ee/ffffff?text=AD" alt="User Avatar" class="user-avatar">
                <span class="user-name"><?php echo $_SESSION['full_name'] ?? 'Admin User'; ?></span>
                <i class="fas fa-chevron-down dropdown-arrow"></i>
            </button>
            
            <div class="user-dropdown">
                <a href="profile.php"><i class="fas fa-user-circle"></i> <span data-translate="profile"><?php echo $navbar_translations[$currentLang]['profile']; ?></span></a>
                <a href="settings.php"><i class="fas fa-cog"></i> <span data-translate="settings"><?php echo $navbar_translations[$currentLang]['settings']; ?></span></a>
                <div class="dropdown-divider"></div>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> <span data-translate="logout"><?php echo $navbar_translations[$currentLang]['logout']; ?></span></a>
            </div>
        </div>

        <div class="date-display" id="current-date-navbar">تحميل التاريخ...</div>
    </div>
</header>

<br><br>
<style>
/* CSS for the Top Navbar - تنسيقات شريط التنقل العلوي */

.top-navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    background-color: #ffffff; /* White background - خلفية بيضاء */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    border-bottom: 1px solid #e2e8f0;
    position: sticky; /* Sticky at the top - تثبيته في الأعلى */
    top: 0;
    z-index: 999; /* Ensure it's above other elements - التأكد من ظهوره فوق العناصر الأخرى */
}

.header-left h1 {
    margin: 0;
    font-size: 1.5rem;
    color: var(--primary);
    font-weight: 600;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 1.5rem; /* Space between elements - مسافة بين العناصر */
}

/* Notifications Icon - أيقونة الإشعارات */
.notifications-icon {
    position: relative;
    cursor: pointer;
    color: #64748b;
    transition: color 0.3s ease;
}

.notifications-icon:hover {
    color: var(--primary);
}

.notifications-icon i {
    font-size: 1.25rem;
}

.notification-count {
    position: absolute;
    top: -8px;
    right: -10px;
    background-color: var(--danger);
    color: white;
    padding: 2px 6px;
    border-radius: 50%;
    font-size: 0.7rem;
    line-height: 1;
}

/* User Menu - قائمة المستخدم */
.user-info-btn {
    display: flex;
    align-items: center;
    padding: 0.5rem 0.8rem;
    background: #f5f7fb;
    border: 1px solid #e2e8f0;
    border-radius: 25px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.user-info-btn:hover {
    background: #e2e8f0;
}

.user-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    object-fit: cover;
    margin-left: 0.5rem;
    border: 2px solid var(--primary);
}

.user-name {
    font-weight: 500;
    color: var(--dark);
    font-size: 0.95rem;
}

.dropdown-arrow {
    font-size: 0.7rem;
    margin-right: 0.5rem;
}

/* Dropdown Menu - القائمة المنسدلة */
.user-menu {
    position: relative;
}

.user-dropdown {
    display: none;
    position: absolute;
    top: 100%; /* Position below the button - وضعه أسفل الزر */
    left: 0;
    background: white;
    min-width: 180px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    border-radius: 8px;
    z-index: 10;
    overflow: hidden;
    margin-top: 10px; /* Space from the button - مسافة عن الزر */
    transform: translateX(-5%); /* Adjust for RTL and visual balance - تعديل لليمين واليسار والموازنة البصرية */
}

.user-menu:hover .user-dropdown,
.user-info-btn:focus + .user-dropdown { /* Show on hover or focus - إظهاره عند التمرير أو التركيز */
    display: block;
}

.user-dropdown a {
    padding: 10px 15px;
    color: #333;
    display: flex;
    align-items: center;
    transition: background 0.3s ease;
}

.user-dropdown a:hover {
    background: #f5f7fb;
    color: var(--primary);
}

.user-dropdown a i {
    margin-left: 10px;
    width: 20px;
    text-align: center;
    color: var(--primary);
}

.dropdown-divider {
    height: 1px;
    background-color: #e2e8f0;
    margin: 5px 0;
}

/* Date Display - عرض التاريخ */
.date-display {
    padding: 0.5rem 0.8rem;
    border-radius: 8px;
    background-color: #eef1f5;
    color: #64748b;
    font-size: 0.9rem;
}

/* Responsive adjustments - تعديلات التجاوب */
@media (max-width: 992px) {
    .top-navbar {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .header-right {
        width: 100%;
        justify-content: space-between;
    }
    
    .date-display {
        display: none; /* Hide date on small screens - إخفاء التاريخ على الشاشات الصغيرة */
    }

    .user-dropdown {
        left: auto;
        right: 0;
        transform: translateX(0); 
    }
}
</style>

<script>
// Function to update the date for the new navbar element - دالة لتحديث التاريخ للعنصر الجديد في شريط التنقل
function updateDateForNavbar() {
    const now = new Date();
    // Options for Arabic display - خيارات للعرض باللغة العربية
    const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        timeZone: 'Asia/Riyadh' // Adjust time zone as needed - اضبط المنطقة الزمنية حسب الحاجة
    };
    const dateString = now.toLocaleDateString('ar-SA', options);
    const dateElement = document.getElementById('current-date-navbar');
    if (dateElement) {
        dateElement.textContent = dateString;
    }
}

// Update the date when the navbar script is loaded - تحديث التاريخ عند تحميل سكريبت شريط التنقل
document.addEventListener('DOMContentLoaded', updateDateForNavbar);
</script>