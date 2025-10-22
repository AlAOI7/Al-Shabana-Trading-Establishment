<?php
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>متجرنا الإلكتروني - الرئيسية</title>
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
                    <?php if (isLoggedIn()): ?>
                        <li><span style="color: #fff;">مرحباً، <?php echo $_SESSION['full_name']; ?></span></li>
                        <?php if (isAdmin()): ?>
                            <li><a href="admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> لوحة التحكم</a></li>
                        <?php else: ?>
                            <li><a href="client/dashboard.php"><i class="fas fa-user"></i> حسابي</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
                    <?php else: ?>
                        <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> تسجيل الدخول</a></li>
                        <li><a href="register.php"><i class="fas fa-user-plus"></i> إنشاء حساب</a></li>
                    <?php endif; ?>
                    <li><a href="#products"><i class="fas fa-box"></i> المنتجات</a></li>
                    <li><a href="#services"><i class="fas fa-concierge-bell"></i> الخدمات</a></li>
                    <li><a href="#about"><i class="fas fa-info-circle"></i> من نحن</a></li>
                    <li><a href="#contact"><i class="fas fa-phone"></i> اتصل بنا</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- قسم الهيرو -->
    <section class="hero-section" style="
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.9), rgba(118, 75, 162, 0.9)),
                    url('https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80');
        background-size: cover;
        background-position: center;
        color: white;
        padding: 120px 0;
        text-align: center;
        position: relative;
        overflow: hidden;
    ">
        <div class="container">
            <div class="hero-content" style="max-width: 800px; margin: 0 auto;">
                <h1 style="font-size: 3.5rem; margin-bottom: 1.5rem; font-weight: 800; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                    مرحباً بك في متجرنا الإلكتروني
                </h1>
                <p style="font-size: 1.3rem; margin-bottom: 2.5rem; opacity: 0.9; line-height: 1.6;">
                    اكتشف عالماً من المنتجات الرائعة والعروض الحصرية. نحن نقدم أفضل جودة وأسعار تنافسية مع خدمة عملاء متميزة.
                </p>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <?php if (!isLoggedIn()): ?>
                        <a href="register.php" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">
                            <i class="fas fa-user-plus"></i> انضم إلينا الآن
                        </a>
                        <a href="login.php" class="btn btn-outline" style="border-color: white; color: white; padding: 1rem 2rem; font-size: 1.1rem;">
                            <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
                        </a>
                    <?php else: ?>
                        <a href="<?php echo isAdmin() ? 'admin/dashboard.php' : 'client/dashboard.php'; ?>" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">
                            <i class="fas fa-tachometer-alt"></i> الانتقال إلى لوحة التحكم
                        </a>
                    <?php endif; ?>
                    <a href="#products" class="btn btn-success" style="padding: 1rem 2rem; font-size: 1.1rem;">
                        <i class="fas fa-shopping-bag"></i> تسوق الآن
                    </a>
                </div>
            </div>
        </div>
        
        <!-- موجات الخلفية -->
        <div class="wave" style="
            position: absolute;
            bottom: 0;
            right: 0;
            width: 100%;
            height: 100px;
            background: url('data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 1200 120\" preserveAspectRatio=\"none\"><path d=\"M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z\" fill=\"%23f8fafc\"></path></svg>');
            background-size: cover;
        "></div>
    </section>

    <!-- قسم المميزات -->
    <section id="features" style="padding: 80px 0; background: var(--light-color);">
        <div class="container">
            <div class="section-header" style="text-align: center; margin-bottom: 4rem;">
                <h2 style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--dark-color);">لماذا تختار متجرنا؟</h2>
                <p style="font-size: 1.2rem; color: var(--gray-600); max-width: 600px; margin: 0 auto;">
                    نقدم لكم أفضل الخدمات والمميزات التي تجعل تجربة التسوق لدينا فريدة من نوعها
                </p>
            </div>

            <div class="row">
                <?php
                $features = [
                    ['fas fa-shipping-fast', 'توصيل سريع', 'نوصل طلباتك خلال 24 ساعة في جميع أنحاء المملكة'],
                    ['fas fa-shield-alt', 'آمن ومضمون', 'معاملات آمنة وبيانات محمية بأحدث تقنيات التشفير'],
                    ['fas fa-headset', 'دعم فني', 'فريق دعم فني متاح 24/7 لمساعدتك في أي استفسار'],
                    ['fas fa-tags', 'عروض حصرية', 'احصل على أفضل العروض والخصومات الحصرية للمشتركين'],
                    ['fas fa-undo', 'إرجاع سهل', 'سياسة إرجاع مرنة خلال 14 يوم من تاريخ الاستلام'],
                    ['fas fa-award', 'جودة عالية', 'منتجات أصلية بجودة عالية وضمان لمدة عام']
                ];

                foreach ($features as $feature):
                ?>
                <div class="col-4 fade-in-up">
                    <div class="feature-card" style="
                        background: white;
                        padding: 2.5rem 2rem;
                        border-radius: var(--border-radius-lg);
                        text-align: center;
                        box-shadow: var(--shadow-md);
                        transition: all 0.3s ease;
                        height: 100%;
                    ">
                        <div class="feature-icon" style="
                            width: 80px;
                            height: 80px;
                            background: var(--gradient-primary);
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            margin: 0 auto 1.5rem;
                            font-size: 2rem;
                            color: white;
                        ">
                            <i class="<?php echo $feature[0]; ?>"></i>
                        </div>
                        <h3 style="margin-bottom: 1rem; color: var(--dark-color);"><?php echo $feature[1]; ?></h3>
                        <p style="color: var(--gray-600); line-height: 1.6;"><?php echo $feature[2]; ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- قسم المنتجات -->
    <section id="products" style="padding: 80px 0; background: white;">
        <div class="container">
            <div class="section-header" style="text-align: center; margin-bottom: 4rem;">
                <h2 style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--dark-color);">أحدث المنتجات</h2>
                <p style="font-size: 1.2rem; color: var(--gray-600); max-width: 600px; margin: 0 auto;">
                    اكتشف تشكيلتنا المميزة من المنتجات ذات الجودة العالية
                </p>
            </div>

            <?php
            // جلب أحدث المنتجات
            $stmt = $pdo->query("SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC LIMIT 6");
            $products = $stmt->fetchAll();
            ?>

            <div class="row">
                <?php if (empty($products)): ?>
                    <div class="col-12" style="text-align: center; padding: 3rem;">
                        <i class="fas fa-box-open" style="font-size: 4rem; color: var(--gray-400); margin-bottom: 1rem;"></i>
                        <h3 style="color: var(--gray-600);">لا توجد منتجات حالياً</h3>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                    <div class="col-4 fade-in-up">
                        <div class="product-card" style="
                            background: white;
                            border-radius: var(--border-radius-lg);
                            box-shadow: var(--shadow-md);
                            overflow: hidden;
                            transition: all 0.3s ease;
                            height: 100%;
                        ">
                            <div class="product-image" style="
                                height: 200px;
                                background: linear-gradient(135deg, var(--gray-100), var(--gray-200));
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                position: relative;
                                overflow: hidden;
                            ">
                                <?php if ($product['image_path']): ?>
                                    <img src="<?php echo $product['image_path']; ?>" 
                                         alt="<?php echo $product['name']; ?>" 
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <i class="fas fa-box" style="font-size: 3rem; color: var(--gray-400);"></i>
                                <?php endif; ?>
                                
                                <div class="product-overlay" style="
                                    position: absolute;
                                    top: 0;
                                    right: 0;
                                    width: 100%;
                                    height: 100%;
                                    background: rgba(0,0,0,0.7);
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    opacity: 0;
                                    transition: all 0.3s ease;
                                ">
                                    <div style="text-align: center;">
                                        <button class="btn btn-primary" style="margin: 0.25rem;">
                                            <i class="fas fa-eye"></i> عرض التفاصيل
                                        </button>
                                        <button class="btn btn-success" style="margin: 0.25rem;">
                                            <i class="fas fa-cart-plus"></i> أضف للسلة
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="product-content" style="padding: 1.5rem;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                    <h3 style="margin: 0; color: var(--dark-color); font-size: 1.2rem;">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </h3>
                                    <span style="
                                        background: var(--gradient-primary);
                                        color: white;
                                        padding: 0.25rem 0.75rem;
                                        border-radius: 20px;
                                        font-size: 0.8rem;
                                        font-weight: 600;
                                    ">
                                        <?php echo htmlspecialchars($product['category']); ?>
                                    </span>
                                </div>
                                
                                <p style="color: var(--gray-600); margin-bottom: 1rem; line-height: 1.5; font-size: 0.9rem;">
                                    <?php echo mb_substr($product['description'], 0, 100) . '...'; ?>
                                </p>
                                
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 1.5rem; font-weight: 700; color: var(--success-color);">
                                        <?php echo number_format($product['price'], 2); ?> ر.س
                                    </span>
                                    <span style="color: var(--gray-500); font-size: 0.9rem;">
                                        <i class="fas fa-box"></i> <?php echo $product['stock_quantity']; ?> متوفر
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if (!empty($products)): ?>
            <div style="text-align: center; margin-top: 3rem;">
                <a href="products.php" class="btn btn-primary" style="padding: 1rem 2rem;">
                    <i class="fas fa-list"></i> عرض جميع المنتجات
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- قسم الخدمات -->
    <section id="services" style="padding: 80px 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <div class="container">
            <div class="section-header" style="text-align: center; margin-bottom: 4rem;">
                <h2 style="font-size: 2.5rem; margin-bottom: 1rem;">خدماتنا المميزة</h2>
                <p style="font-size: 1.2rem; opacity: 0.9; max-width: 600px; margin: 0 auto;">
                    نقدم مجموعة متنوعة من الخدمات لتلبية جميع احتياجاتك
                </p>
            </div>

            <?php
            // جلب الخدمات
            $stmt = $pdo->query("SELECT * FROM services ORDER BY created_at DESC LIMIT 3");
            $services = $stmt->fetchAll();
            ?>

            <div class="row">
                <?php if (empty($services)): ?>
                    <div class="col-12" style="text-align: center; padding: 3rem;">
                        <i class="fas fa-concierge-bell" style="font-size: 4rem; opacity: 0.5; margin-bottom: 1rem;"></i>
                        <h3>لا توجد خدمات حالياً</h3>
                    </div>
                <?php else: ?>
                    <?php foreach ($services as $service): ?>
                    <div class="col-4 fade-in-up">
                        <div class="service-card" style="
                            background: rgba(255, 255, 255, 0.1);
                            backdrop-filter: blur(20px);
                            border: 1px solid rgba(255, 255, 255, 0.2);
                            padding: 2.5rem 2rem;
                            border-radius: var(--border-radius-lg);
                            text-align: center;
                            transition: all 0.3s ease;
                            height: 100%;
                        ">
                            <div style="font-size: 3rem; margin-bottom: 1.5rem; opacity: 0.9;">
                                <?php if ($service['icon']): ?>
                                    <i class="<?php echo $service['icon']; ?>"></i>
                                <?php else: ?>
                                    <i class="fas fa-concierge-bell"></i>
                                <?php endif; ?>
                            </div>
                            <h3 style="margin-bottom: 1rem; font-size: 1.5rem;"><?php echo htmlspecialchars($service['title']); ?></h3>
                            <p style="opacity: 0.9; line-height: 1.6;"><?php echo htmlspecialchars($service['description']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- قسم من نحن -->
    <section id="about" style="padding: 80px 0; background: white;">
        <div class="container">
            <div class="row align-center">
                <div class="col-6">
                    <div class="about-content">
                        <h2 style="font-size: 2.5rem; margin-bottom: 1.5rem; color: var(--dark-color);">من نحن</h2>
                        <?php
                        // جلب محتوى من نحن
                        $stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'about_us'");
                        $about_content = $stmt->fetchColumn();
                        ?>
                        
                        <?php if ($about_content): ?>
                            <div style="color: var(--gray-700); line-height: 1.8; font-size: 1.1rem;">
                                <?php echo nl2br(htmlspecialchars($about_content)); ?>
                            </div>
                        <?php else: ?>
                            <div style="color: var(--gray-700); line-height: 1.8; font-size: 1.1rem;">
                                <p>نحن متجر إلكتروني رائد نهدف إلى توفير أفضل المنتجات والخدمات لعملائنا الكرام. نسعى دائماً لتحقيق رضاكم من خلال تقديم منتجات عالية الجودة بأسعار تنافسية.</p>
                                <p>فريقنا من المحترفين يعمل بجد لتطوير وتحسين خدماتنا بشكل مستمر، ونسعى لأن نكون الخيار الأول لعملائنا في مجال التسوق الإلكتروني.</p>
                            </div>
                        <?php endif; ?>
                        
                        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                            <div class="stat" style="text-align: center;">
                                <h3 style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 0.5rem;">+500</h3>
                                <p style="color: var(--gray-600);">عميل سعيد</p>
                            </div>
                            <div class="stat" style="text-align: center;">
                                <h3 style="font-size: 2.5rem; color: var(--success-color); margin-bottom: 0.5rem;">+1000</h3>
                                <p style="color: var(--gray-600);">طلب ناجح</p>
                            </div>
                            <div class="stat" style="text-align: center;">
                                <h3 style="font-size: 2.5rem; color: var(--warning-color); margin-bottom: 0.5rem;">+50</h3>
                                <p style="color: var(--gray-600);">منتج مميز</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="about-image" style="
                        background: linear-gradient(135deg, var(--gray-100), var(--gray-200));
                        border-radius: var(--border-radius-lg);
                        height: 400px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    ">
                        <i class="fas fa-store" style="font-size: 8rem; color: var(--gray-400);"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- قسم اتصل بنا -->
    <section id="contact" style="padding: 80px 0; background: var(--light-color);">
        <div class="container">
            <div class="section-header" style="text-align: center; margin-bottom: 4rem;">
                <h2 style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--dark-color);">اتصل بنا</h2>
                <p style="font-size: 1.2rem; color: var(--gray-600); max-width: 600px; margin: 0 auto;">
                نحن هنا لمساعدتك! تواصل معنا لأي استفسار أو دعم
                </p>
            </div>

            <div class="row">
                <div class="col-6">
                    <?php
                    // جلب بيانات التواصل
                    $stmt = $pdo->query("SELECT * FROM contact_info LIMIT 1");
                    $contact_info = $stmt->fetch();
                    ?>
                    
                    <div class="contact-info" style="background: white; padding: 2rem; border-radius: var(--border-radius-lg); box-shadow: var(--shadow-md);">
                        <h3 style="margin-bottom: 1.5rem; color: var(--dark-color);">معلومات التواصل</h3>
                        
                        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                            <?php if ($contact_info && $contact_info['address']): ?>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 50px; height: 50px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <h4 style="margin: 0; color: var(--dark-color);">العنوان</h4>
                                    <p style="margin: 0.25rem 0 0 0; color: var(--gray-600);"><?php echo nl2br(htmlspecialchars($contact_info['address'])); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($contact_info && $contact_info['phone']): ?>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 50px; height: 50px; background: var(--gradient-success); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div>
                                    <h4 style="margin: 0; color: var(--dark-color);">الهاتف</h4>
                                    <p style="margin: 0.25rem 0 0 0; color: var(--gray-600);"><?php echo htmlspecialchars($contact_info['phone']); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($contact_info && $contact_info['email']): ?>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 50px; height: 50px; background: var(--gradient-warning); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <h4 style="margin: 0; color: var(--dark-color);">البريد الإلكتروني</h4>
                                    <p style="margin: 0.25rem 0 0 0; color: var(--gray-600);"><?php echo htmlspecialchars($contact_info['email']); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- وسائل التواصل الاجتماعي -->
                        <?php if ($contact_info && ($contact_info['social_facebook'] || $contact_info['social_twitter'] || $contact_info['social_instagram'])): ?>
                        <div style="margin-top: 2rem;">
                            <h4 style="margin-bottom: 1rem; color: var(--dark-color);">تابعنا على</h4>
                            <div style="display: flex; gap: 1rem;">
                                <?php if ($contact_info['social_facebook']): ?>
                                <a href="<?php echo $contact_info['social_facebook']; ?>" target="_blank" style="
                                    width: 45px;
                                    height: 45px;
                                    background: #1877f2;
                                    border-radius: 50%;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    color: white;
                                    text-decoration: none;
                                    transition: all 0.3s ease;
                                ">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if ($contact_info['social_twitter']): ?>
                                <a href="<?php echo $contact_info['social_twitter']; ?>" target="_blank" style="
                                    width: 45px;
                                    height: 45px;
                                    background: #1da1f2;
                                    border-radius: 50%;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    color: white;
                                    text-decoration: none;
                                    transition: all 0.3s ease;
                                ">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if ($contact_info['social_instagram']): ?>
                                <a href="<?php echo $contact_info['social_instagram']; ?>" target="_blank" style="
                                    width: 45px;
                                    height: 45px;
                                    background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
                                    border-radius: 50%;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    color: white;
                                    text-decoration: none;
                                    transition: all 0.3s ease;
                                ">
                                    <i class="fab fa-instagram"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-6">
                    <div class="contact-form" style="background: white; padding: 2rem; border-radius: var(--border-radius-lg); box-shadow: var(--shadow-md);">
                        <h3 style="margin-bottom: 1.5rem; color: var(--dark-color);">أرسل رسالة</h3>
                        <form id="contactForm">
                            <div class="form-group">
                                <label for="name">الاسم الكامل</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">البريد الإلكتروني</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="subject">الموضوع</label>
                                <input type="text" class="form-control" id="subject" name="subject" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="message">الرسالة</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-paper-plane"></i> إرسال الرسالة
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- الفوتر -->
    <footer style="background: var(--dark-color); color: white; padding: 3rem 0 1rem;">
        <div class="container">
            <div class="row">
                <div class="col-4">
                    <div class="footer-section">
                        <h4 style="margin-bottom: 1rem; color: white;">متجرنا</h4>
                        <p style="color: var(--gray-400); line-height: 1.6;">
                            نحن نقدم أفضل المنتجات والخدمات مع ضمان الجودة والرضا التام للعملاء.
                        </p>
                    </div>
                </div>
                <div class="col-4">
                    <div class="footer-section">
                        <h4 style="margin-bottom: 1rem; color: white;">روابط سريعة</h4>
                        <ul style="list-style: none; padding: 0;">
                            <li><a href="#products" style="color: var(--gray-400); text-decoration: none; display: block; padding: 0.25rem 0;">المنتجات</a></li>
                            <li><a href="#services" style="color: var(--gray-400); text-decoration: none; display: block; padding: 0.25rem 0;">الخدمات</a></li>
                            <li><a href="#about" style="color: var(--gray-400); text-decoration: none; display: block; padding: 0.25rem 0;">من نحن</a></li>
                            <li><a href="#contact" style="color: var(--gray-400); text-decoration: none; display: block; padding: 0.25rem 0;">اتصل بنا</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-4">
                    <div class="footer-section">
                        <h4 style="margin-bottom: 1rem; color: white;">القائمة البريدية</h4>
                        <p style="color: var(--gray-400); margin-bottom: 1rem;">اشترك في نشرتنا البريدية لتصلك آخر العروض</p>
                        <form style="display: flex; gap: 0.5rem;">
                            <input type="email" placeholder="بريدك الإلكتروني" style="
                                flex: 1;
                                padding: 0.75rem;
                                border: none;
                                border-radius: var(--border-radius);
                                background: rgba(255,255,255,0.1);
                                color: white;
                            ">
                            <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1rem;">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div style="border-top: 1px solid var(--gray-700); margin-top: 2rem; padding-top: 1.5rem; text-align: center;">
                <p style="color: var(--gray-400); margin: 0;">
                    &copy; 2024 متجرنا الإلكتروني. جميع الحقوق محفوظة.
                </p>
            </div>
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

        // تأثيرات ظهور العناصر
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, {
            threshold: 0.1
        });

        document.querySelectorAll('.fade-in-up').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'all 0.6s ease-out';
            observer.observe(el);
        });

        // تأثيرات البطاقات
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.querySelector('.product-overlay').style.opacity = '1';
            });
            
            card.addEventListener('mouseleave', function() {
                this.querySelector('.product-overlay').style.opacity = '0';
            });
        });

        // نموذج الاتصال
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('شكراً لك! سنقوم بالرد على رسالتك في أقرب وقت ممكن.');
            this.reset();
        });
    </script>

    <style>
        .align-center {
            align-items: center;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-xl);
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .service-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
        }
        
        .footer-section a:hover {
            color: white !important;
            padding-right: 10px !important;
            transition: all 0.3s ease;
        }
    </style>
</body>
</html>