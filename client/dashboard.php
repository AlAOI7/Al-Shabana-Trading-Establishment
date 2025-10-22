<?php
require_once '../config.php';
requireClient();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم العميل</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <!-- الشريط الجانبي -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>حسابي</h3>
                <p>مرحباً، <?php echo $_SESSION['full_name']; ?></p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active">الرئيسية</a></li>
                <li><a href="profile.php">الملف الشخصي</a></li>
                <li><a href="orders.php">طلباتي</a></li>
                <li><a href="settings.php">الإعدادات</a></li>
                <li><a href="../logout.php">تسجيل الخروج</a></li>
            </ul>
        </aside>

        <!-- المحتوى الرئيسي -->
        <main class="main-content">
            <div class="header">
                <h1>لوحة تحكم العميل</h1>
                <p>مرحباً بك في حسابك الشخصي</p>
            </div>

            <!-- بطاقات الإحصائيات -->
            <div class="row">
                <div class="col-3">
                    <div class="card">
                        <div class="card-body" style="text-align: center;">
                            <div class="feature-icon">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <h3>0</h3>
                            <p>الطلبات النشطة</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-3">
                    <div class="card">
                        <div class="card-body" style="text-align: center;">
                            <div class="feature-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <h3>0</h3>
                            <p>المفضلة</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-3">
                    <div class="card">
                        <div class="card-body" style="text-align: center;">
                            <div class="feature-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <h3>0</h3>
                            <p>التقييمات</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-3">
                    <div class="card">
                        <div class="card-body" style="text-align: center;">
                            <div class="feature-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h3>0</h3>
                            <p>آخر نشاط</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- محتوى إضافي -->
            <div class="row">
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>معلومات حسابي</h3>
                        </div>
                        <div class="card-body">
                            <p><strong>الاسم:</strong> <?php echo $_SESSION['full_name']; ?></p>
                            <p><strong>اسم المستخدم:</strong> <?php echo $_SESSION['username']; ?></p>
                            <p><strong>البريد الإلكتروني:</strong> <?php echo $_SESSION['email']; ?></p>
                            <a href="profile.php" class="btn btn-primary">تعديل الملف الشخصي</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>آخر النشاطات</h3>
                        </div>
                        <div class="card-body">
                            <p>لا توجد نشاطات حديثة</p>
                            <a href="../index.php" class="btn btn-success">استعرض المنتجات</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>