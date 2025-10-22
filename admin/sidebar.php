<?php
// sidebar.php - يمكن تضمينه في جميع صفحات الأدمن
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-tachometer-alt"></i> لوحة التحكم</h3>
        <p>مرحباً، <?php echo $_SESSION['full_name']; ?></p>
    </div>
    
    <ul class="sidebar-menu">
        <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i> الرئيسية
        </a></li>
        <li><a href="users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> إدارة المستخدمين
        </a></li>
        <li><a href="products.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
            <i class="fas fa-box"></i> إدارة المنتجات
        </a></li>
        <li><a href="services.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'services.php' ? 'active' : ''; ?>">
            <i class="fas fa-concierge-bell"></i> إدارة الخدمات
        </a></li>
        <li><a href="orders.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart"></i> إدارة الطلبات
        </a></li>
        <li><a href="about.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">
            <i class="fas fa-info-circle"></i> من نحن
        </a></li>
        <li><a href="contact_info.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'contact_info.php' ? 'active' : ''; ?>">
            <i class="fas fa-address-book"></i> بيانات التواصل
        </a></li>
        <li><a href="settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
            <i class="fas fa-cogs"></i> الإعدادات
        </a></li>
        <li><a href="../logout.php">
            <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
        </a></li>
    </ul>
</aside>