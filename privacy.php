<?php
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سياسة الخصوصية - متجرنا</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- شريط التنقل -->
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <div class="logo">
                    <i class="fas fa-store"></i>
                    متجرنا
                </div>
                <ul class="nav-links">
                    <li><a href="index.php"><i class="fas fa-home"></i> الرئيسية</a></li>
                    <li><a href="products.php"><i class="fas fa-box"></i> المنتجات</a></li>
                    <li><a href="index.php#services"><i class="fas fa-concierge-bell"></i> الخدمات</a></li>
                    <li><a href="index.php#about"><i class="fas fa-info-circle"></i> من نحن</a></li>
                    <li><a href="index.php#contact"><i class="fas fa-phone"></i> اتصل بنا</a></li>
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <li><a href="admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> لوحة التحكم</a></li>
                        <?php else: ?>
                            <li><a href="client/dashboard.php"><i class="fas fa-user"></i> حسابي</a></li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> تسجيل الدخول</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- محتوى الصفحة -->
    <section style="padding: 4rem 0; background: var(--light-color);">
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h1 style="margin: 0; text-align: center;">
                        <i class="fas fa-shield-alt"></i>
                        سياسة الخصوصية
                    </h1>
                </div>
                <div class="card-body">
                    <div style="max-width: 800px; margin: 0 auto; line-height: 1.8; color: var(--gray-700);">
                        <!-- آخر تحديث -->
                        <div style="background: var(--gray-50); padding: 1.5rem; border-radius: var(--border-radius); margin-bottom: 2rem;">
                            <p style="margin: 0; color: var(--gray-600);">
                                <strong>آخر تحديث:</strong> <?php echo date('Y-m-d'); ?>
                            </p>
                        </div>

                        <!-- المقدمة -->
                        <div style="margin-bottom: 2rem;">
                            <h2 style="color: var(--dark-color); margin-bottom: 1rem;">مقدمة</h2>
                            <p>
                                نحن في متجرنا نعتبر خصوصيتك من الأولويات القصوى. تشرح سياسة الخصوصية هذه كيفية جمعنا واستخدامنا وحماية معلوماتك الشخصية عندما تزور موقعنا أو تستخدم خدماتنا.
                            </p>
                        </div>

                        <!-- المعلومات التي نجمعها -->
                        <div style="margin-bottom: 2rem;">
                            <h2 style="color: var(--dark-color); margin-bottom: 1rem;">المعلومات التي نجمعها</h2>
                            <p>نجمع عدة أنواع من المعلومات لتوفير خدمة أفضل لك:</p>
                            <ul style="margin-right: 2rem;">
                                <li><strong>المعلومات الشخصية:</strong> الاسم، البريد الإلكتروني، رقم الهاتف، العنوان</li>
                                <li><strong>معلومات الدفع:</strong> تفاصيل بطاقة الائتمان (مشفرة بالكامل)</li>
                                <li><strong>معلومات التقنية:</strong> عنوان IP، نوع المتصفح، صفحات الزيارة</li>
                                <li><strong>معلومات الاستخدام:</strong> عادات التسوق، المنتجات المفضلة</li>
                            </ul>
                        </div>

                        <!-- كيفية استخدام المعلومات -->
                        <div style="margin-bottom: 2rem;">
                            <h2 style="color: var(--dark-color); margin-bottom: 1rem;">كيفية استخدام المعلومات</h2>
                            <p>نستخدم المعلومات التي نجمعها للأغراض التالية:</p>
                            <ul style="margin-right: 2rem;">
                                <li>معالجة الطلبات والمدفوعات</li>
                                <li>تحسين تجربة المستخدم وتخصيصها</li>
                                <li>إرسال عروض وتحديثات تهمك</li>
                                <li>تحليل أداء الموقع وتحسين الخدمات</li>
                                <li>الامتثال للقوانين واللوائح</li>
                            </ul>
                        </div>

                        <!-- مشاركة المعلومات -->
                        <div style="margin-bottom: 2rem;">
                            <h2 style="color: var(--dark-color); margin-bottom: 1rem;">مشاركة المعلومات</h2>
                            <p>لا نبيع أو نؤجر معلوماتك الشخصية لأطراف ثالثة. قد نشارك المعلومات في الحالات التالية فقط:</p>
                            <ul style="margin-right: 2rem;">
                                <li>مع مقدمي الخدمات الذين يساعدوننا في تشغيل الموقع</li>
                                <li>عندما يكون ذلك مطلوباً بموجب القانون</li>
                                <li>لحماية حقوقنا وممتلكاتنا</li>
                                <li>مع موافقتك الصريحة</li>
                            </ul>
                        </div>

                        <!-- حماية المعلومات -->
                        <div style="margin-bottom: 2rem;">
                            <h2 style="color: var(--dark-color); margin-bottom: 1rem;">حماية المعلومات</h2>
                            <p>نحن نستخدم إجراءات أمنية متعددة لحماية معلوماتك:</p>
                            <ul style="margin-right: 2rem;">
                                <li>تشفير البيانات أثناء النقل (SSL)</li>
                                <li>تخزين آمن للمعلومات في خوادم مؤمنة</li>
                                <li>مراقبة مستمرة للأنظمة</li>
                                <li>تدريب الموظفين على أمن المعلومات</li>
                            </ul>
                        </div>

                        <!-- حقوقك -->
                        <div style="margin-bottom: 2rem;">
                            <h2 style="color: var(--dark-color); margin-bottom: 1rem;">حقوقك</h2>
                            <p>لديك الحق في:</p>
                            <ul style="margin-right: 2rem;">
                                <li>الوصول إلى معلوماتك الشخصية</li>
                                <li>تصحيح المعلومات غير الدقيقة</li>
                                <li>طلب حذف معلوماتك الشخصية</li>
                                <li>معارضة معالجة معلوماتك</li>
                                <li>طلب نقل بياناتك</li>
                            </ul>
                        </div>

                        <!-- الكوكيز -->
                        <div style="margin-bottom: 2rem;">
                            <h2 style="color: var(--dark-color); margin-bottom: 1rem;">الكوكيز</h2>
                            <p>نستخدم ملفات تعريف الارتباط (كوكيز) لتحسين تجربتك على موقعنا. يمكنك التحكم في إعدادات الكوكيز من خلال متصفحك.</p>
                        </div>

                        <!-- التغييرات على السياسة -->
                        <div style="margin-bottom: 2rem;">
                            <h2 style="color: var(--dark-color); margin-bottom: 1rem;">التغييرات على السياسة</h2>
                            <p>قد نقوم بتحديث سياسة الخصوصية هذه من وقت لآخر. سنخطرك بأي تغييرات جوهرية عن طريق نشر الإشعار على موقعنا.</p>
                        </div>

                        <!-- الاتصال بنا -->
                        <div style="background: var(--gray-50); padding: 1.5rem; border-radius: var(--border-radius);">
                            <h2 style="color: var(--dark-color); margin-bottom: 1rem;">الاتصال بنا</h2>
                            <p>إذا كان لديك أي أسئلة حول سياسة الخصوصية هذه، يرجى الاتصال بنا:</p>
                            <div style="display: flex; gap: 2rem; margin-top: 1rem;">
                                <div style="flex: 1;">
                                    <h4 style="color: var(--dark-color); margin-bottom: 0.5rem;">
                                        <i class="fas fa-envelope" style="color: var(--primary-color);"></i>
                                        البريد الإلكتروني
                                    </h4>
                                    <p style="margin: 0; color: var(--gray-600);">privacy@mystore.com</p>
                                </div>
                                <div style="flex: 1;">
                                    <h4 style="color: var(--dark-color); margin-bottom: 0.5rem;">
                                        <i class="fas fa-phone" style="color: var(--success-color);"></i>
                                        الهاتف
                                    </h4>
                                    <p style="margin: 0; color: var(--gray-600);">+966500000000</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- الفوتر -->
    <footer style="background: var(--dark-color); color: white; padding: 2rem 0; text-align: center;">
        <div class="container">
            <p>&copy; 2024 متجرنا الإلكتروني. جميع الحقوق محفوظة.</p>
        </div>
    </footer>

    <script>
        // تأثيرات التمرير السلس
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>