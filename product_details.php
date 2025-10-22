<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = $_GET['id'];

// جلب بيانات المنتج
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: products.php");
    exit();
}

// جلب منتجات مشابهة
$stmt = $pdo->prepare("SELECT * FROM products WHERE category = ? AND id != ? AND is_active = 1 ORDER BY RAND() LIMIT 4");
$stmt->execute([$product['category'], $product_id]);
$related_products = $stmt->fetchAll();

// زيادة عدد المشاهدات
$stmt = $pdo->prepare("UPDATE products SET views = COALESCE(views, 0) + 1 WHERE id = ?");
$stmt->execute([$product_id]);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - متجرنا</title>
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

    <!-- مسار التنقل -->
    <section style="background: var(--light-color); padding: 1.5rem 0;">
        <div class="container">
            <nav style="display: flex; align-items: center; gap: 0.5rem; color: var(--gray-600);">
                <a href="index.php" style="color: var(--primary-color); text-decoration: none;">الرئيسية</a>
                <i class="fas fa-chevron-left" style="font-size: 0.8rem;"></i>
                <a href="products.php" style="color: var(--primary-color); text-decoration: none;">المنتجات</a>
                <i class="fas fa-chevron-left" style="font-size: 0.8rem;"></i>
                <a href="products.php?category=<?php echo urlencode($product['category']); ?>" style="color: var(--primary-color); text-decoration: none;">
                    <?php echo htmlspecialchars($product['category']); ?>
                </a>
                <i class="fas fa-chevron-left" style="font-size: 0.8rem;"></i>
                <span style="color: var(--gray-600);"><?php echo htmlspecialchars($product['name']); ?></span>
            </nav>
        </div>
    </section>

    <!-- قسم تفاصيل المنتج -->
    <section style="padding: 3rem 0;">
        <div class="container">
            <div class="row">
                <!-- صور المنتج -->
                <div class="col-6">
                    <div class="product-gallery">
                        <!-- الصورة الرئيسية -->
                        <div style="
                            background: linear-gradient(135deg, var(--gray-100), var(--gray-200));
                            border-radius: var(--border-radius-lg);
                            height: 400px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            margin-bottom: 1rem;
                            overflow: hidden;
                        ">
                            <?php if ($product['image_path']): ?>
                                <img src="<?php echo $product['image_path']; ?>" 
                                     alt="<?php echo $product['name']; ?>" 
                                     id="mainImage"
                                     style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            <?php else: ?>
                                <i class="fas fa-box" style="font-size: 4rem; color: var(--gray-400);"></i>
                            <?php endif; ?>
                        </div>

                        <!-- الصور المصغرة -->
                        <div style="display: flex; gap: 1rem; justify-content: center;">
                            <?php if ($product['image_path']): ?>
                                <div class="thumbnail active" 
                                     style="
                                        width: 80px;
                                        height: 80px;
                                        border: 2px solid var(--primary-color);
                                        border-radius: var(--border-radius);
                                        overflow: hidden;
                                        cursor: pointer;
                                     "
                                     onclick="changeImage('<?php echo $product['image_path']; ?>')">
                                    <img src="<?php echo $product['image_path']; ?>" 
                                         alt="صورة مصغرة" 
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                            <?php endif; ?>
                            
                            <!-- صور إضافية (يمكن إضافتها من قاعدة البيانات) -->
                            <div class="thumbnail" 
                                 style="
                                    width: 80px;
                                    height: 80px;
                                    border: 2px solid var(--gray-300);
                                    border-radius: var(--border-radius);
                                    background: var(--gray-100);
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    cursor: pointer;
                                 "
                                 onclick="changeImage('')">
                                <i class="fas fa-box" style="color: var(--gray-400);"></i>
                            </div>
                            
                            <div class="thumbnail" 
                                 style="
                                    width: 80px;
                                    height: 80px;
                                    border: 2px solid var(--gray-300);
                                    border-radius: var(--border-radius);
                                    background: var(--gray-100);
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    cursor: pointer;
                                 "
                                 onclick="changeImage('')">
                                <i class="fas fa-box" style="color: var(--gray-400);"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- معلومات المنتج -->
                <div class="col-6">
                    <div class="product-info" style="padding-right: 2rem;">
                        <!-- الفئة والعنوان -->
                        <div style="margin-bottom: 1.5rem;">
                            <span style="
                                background: var(--gradient-primary);
                                color: white;
                                padding: 0.5rem 1rem;
                                border-radius: 20px;
                                font-size: 0.9rem;
                                font-weight: 600;
                                margin-bottom: 1rem;
                                display: inline-block;
                            ">
                                <?php echo htmlspecialchars($product['category']); ?>
                            </span>
                            <h1 style="font-size: 2.2rem; margin: 1rem 0; color: var(--dark-color); line-height: 1.3;">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </h1>
                        </div>

                        <!-- التقييم -->
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                            <div class="rating" style="color: #ffc107;">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                            <span style="color: var(--gray-600);">(4.5) - 120 تقييم</span>
                            <span style="color: var(--gray-500);">
                                <i class="fas fa-eye"></i> 
                                <?php echo ($product['views'] ?? 0) + 1; ?> مشاهدة
                            </span>
                        </div>

                        <!-- السعر -->
                        <div style="margin-bottom: 2rem;">
                            <h2 style="font-size: 2.5rem; color: var(--success-color); margin-bottom: 0.5rem;">
                                <?php echo number_format($product['price'], 2); ?> ر.س
                            </h2>
                            <?php if ($product['price'] > 1000): ?>
                                <p style="color: var(--gray-600); margin: 0;">
                                    <i class="fas fa-truck"></i> توصيل مجاني
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- المخزون -->
                        <div style="margin-bottom: 2rem;">
                            <p style="color: <?php echo $product['stock_quantity'] > 0 ? 'var(--success-color)' : 'var(--danger-color)'; ?>; font-weight: 600;">
                                <i class="fas fa-box"></i>
                                <?php if ($product['stock_quantity'] > 10): ?>
                                    <span style="color: var(--success-color);">متوفر في المخزن</span>
                                <?php elseif ($product['stock_quantity'] > 0): ?>
                                    <span style="color: var(--warning-color);">أقل من <?php echo $product['stock_quantity']; ?> قطعة متبقية</span>
                                <?php else: ?>
                                    <span style="color: var(--danger-color);">غير متوفر حالياً</span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <!-- الوصف -->
                        <div style="margin-bottom: 2rem;">
                            <h3 style="margin-bottom: 1rem; color: var(--dark-color);">وصف المنتج</h3>
                            <p style="color: var(--gray-700); line-height: 1.8; font-size: 1.1rem;">
                                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                            </p>
                        </div>

                        <!-- الخيارات -->
                        <div style="margin-bottom: 2rem;">
                            <div class="form-group">
                                <label for="quantity" style="font-weight: 600; margin-bottom: 0.5rem; display: block;">الكمية</label>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <div style="display: flex; align-items: center; border: 1px solid var(--gray-300); border-radius: var(--border-radius); overflow: hidden;">
                                        <button type="button" id="decreaseQty" 
                                                style="border: none; background: var(--gray-100); padding: 0.75rem 1rem; cursor: pointer;">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" 
                                               id="quantity" 
                                               value="1" 
                                               min="1" 
                                               max="<?php echo $product['stock_quantity']; ?>"
                                               style="width: 60px; text-align: center; border: none; padding: 0.75rem; outline: none;">
                                        <button type="button" id="increaseQty" 
                                                style="border: none; background: var(--gray-100); padding: 0.75rem 1rem; cursor: pointer;">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    <span style="color: var(--gray-600); font-size: 0.9rem;">
                                        الحد الأقصى: <?php echo $product['stock_quantity']; ?> قطعة
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- أزرار الشراء -->
                        <div style="display: flex; gap: 1rem; margin-bottom: 2rem;">
                            <button class="btn btn-primary" 
                                    id="addToCartBtn"
                                    style="flex: 2; padding: 1rem 2rem; font-size: 1.1rem;"
                                    <?php echo $product['stock_quantity'] == 0 ? 'disabled' : ''; ?>>
                                <i class="fas fa-cart-plus"></i>
                                <?php echo $product['stock_quantity'] > 0 ? 'أضف إلى عربة التسوق' : 'غير متوفر'; ?>
                            </button>
                            <button class="btn btn-outline" style="flex: 1; padding: 1rem;">
                                <i class="fas fa-heart"></i>
                            </button>
                            <button class="btn btn-outline" style="flex: 1; padding: 1rem;">
                                <i class="fas fa-share-alt"></i>
                            </button>
                        </div>

                        <!-- معلومات إضافية -->
                        <div style="background: var(--gray-50); padding: 1.5rem; border-radius: var(--border-radius);">
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                                <div style="text-align: center;">
                                    <i class="fas fa-shield-alt" style="color: var(--success-color); font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
                                    <p style="margin: 0; color: var(--gray-600); font-size: 0.9rem;">ضمان الجودة</p>
                                </div>
                                <div style="text-align: center;">
                                    <i class="fas fa-undo" style="color: var(--primary-color); font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
                                    <p style="margin: 0; color: var(--gray-600); font-size: 0.9rem;">إرجاع خلال 14 يوم</p>
                                </div>
                                <div style="text-align: center;">
                                    <i class="fas fa-truck" style="color: var(--warning-color); font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
                                    <p style="margin: 0; color: var(--gray-600); font-size: 0.9rem;">توصيل سريع</p>
                                </div>
                                <div style="text-align: center;">
                                    <i class="fas fa-headset" style="color: var(--info-color); font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
                                    <p style="margin: 0; color: var(--gray-600); font-size: 0.9rem;">دعم 24/7</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- التبويبات -->
            <div style="margin-top: 4rem;">
                <div class="tabs" style="border-bottom: 1px solid var(--gray-300);">
                    <button class="tab-btn active" data-tab="description">الوصف التفصيلي</button>
                    <button class="tab-btn" data-tab="specifications">المواصفات</button>
                    <button class="tab-btn" data-tab="reviews">التقييمات</button>
                    <button class="tab-btn" data-tab="shipping">الشحن والتوصيل</button>
                </div>

                <div class="tab-content">
                    <div id="description" class="tab-pane active" style="padding: 2rem 0;">
                        <h3 style="margin-bottom: 1rem; color: var(--dark-color);">تفاصيل المنتج</h3>
                        <div style="color: var(--gray-700); line-height: 1.8; font-size: 1.1rem;">
                            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                            
                            <div style="margin-top: 2rem;">
                                <h4 style="color: var(--dark-color); margin-bottom: 1rem;">مميزات إضافية:</h4>
                                <ul style="color: var(--gray-700); line-height: 2;">
                                    <li>جودة عالية ومواد أصلية</li>
                                    <li>ضمان لمدة سنة واحدة</li>
                                    <li>دعم فني متواصل</li>
                                    <li>إرجاع مجاني خلال 14 يوم</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div id="specifications" class="tab-pane" style="padding: 2rem 0; display: none;">
                        <h3 style="margin-bottom: 1rem; color: var(--dark-color);">المواصفات الفنية</h3>
                        <div class="specs-table" style="display: grid; grid-template-columns: 1fr 2fr; gap: 1rem;">
                            <div style="font-weight: 600; color: var(--dark-color);">العلامة التجارية</div>
                            <div style="color: var(--gray-700);">متجرنا</div>
                            
                            <div style="font-weight: 600; color: var(--dark-color);">النوع</div>
                            <div style="color: var(--gray-700);"><?php echo htmlspecialchars($product['category']); ?></div>
                            
                            <div style="font-weight: 600; color: var(--dark-color);">اللون</div>
                            <div style="color: var(--gray-700);">أسود</div>
                            
                            <div style="font-weight: 600; color: var(--dark-color);">الأبعاد</div>
                            <div style="color: var(--gray-700);">20 × 15 × 10 سم</div>
                            
                            <div style="font-weight: 600; color: var(--dark-color);">الوزن</div>
                            <div style="color: var(--gray-700);">500 جرام</div>
                            
                            <div style="font-weight: 600; color: var(--dark-color);">الضمان</div>
                            <div style="color: var(--success-color); font-weight: 600;">12 شهر</div>
                        </div>
                    </div>

                    <div id="reviews" class="tab-pane" style="padding: 2rem 0; display: none;">
                        <h3 style="margin-bottom: 1rem; color: var(--dark-color);">تقييمات العملاء</h3>
                        
                        <!-- ملخص التقييمات -->
                        <div style="display: flex; gap: 3rem; margin-bottom: 2rem; padding: 2rem; background: var(--gray-50); border-radius: var(--border-radius);">
                            <div style="text-align: center;">
                                <div style="font-size: 3rem; font-weight: 700; color: var(--success-color);">4.5</div>
                                <div class="rating" style="color: #ffc107; margin-bottom: 0.5rem;">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                                <div style="color: var(--gray-600); font-size: 0.9rem;">120 تقييم</div>
                            </div>
                            
                            <div style="flex: 1;">
                                <?php
                                $ratings = [5 => 80, 4 => 25, 3 => 10, 2 => 3, 1 => 2];
                                $total = array_sum($ratings);
                                ?>
                                
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                                    <span style="color: var(--gray-600); width: 20px;"><?php echo $i; ?></span>
                                    <i class="fas fa-star" style="color: #ffc107;"></i>
                                    <div style="flex: 1; background: var(--gray-300); height: 8px; border-radius: 4px;">
                                        <div style="
                                            background: #ffc107; 
                                            height: 100%; 
                                            border-radius: 4px; 
                                            width: <?php echo ($ratings[$i] / $total) * 100; ?>%;
                                        "></div>
                                    </div>
                                    <span style="color: var(--gray-600); font-size: 0.9rem; width: 40px;">
                                        <?php echo $ratings[$i]; ?>
                                    </span>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <!-- قائمة التقييمات -->
                        <div class="reviews-list">
                            <?php for ($i = 0; $i < 3; $i++): ?>
                            <div style="padding: 1.5rem; border-bottom: 1px solid var(--gray-200);">
                                <div style="display: flex; justify-content: between; align-items: start; margin-bottom: 1rem;">
                                    <div>
                                        <h4 style="margin: 0 0 0.5rem 0; color: var(--dark-color);">محمد أحمد</h4>
                                        <div class="rating" style="color: #ffc107;">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                        </div>
                                    </div>
                                    <span style="color: var(--gray-500); font-size: 0.9rem;">منذ 3 أيام</span>
                                </div>
                                <p style="color: var(--gray-700); line-height: 1.6; margin: 0;">
                                    منتج رائع وجودة ممتازة. التوصيل كان سريعاً والتغليف احترافي. أنصح الجميع بهذا المنتج.
                                </p>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div id="shipping" class="tab-pane" style="padding: 2rem 0; display: none;">
                        <h3 style="margin-bottom: 1rem; color: var(--dark-color);">معلومات الشحن والتوصيل</h3>
                        <div style="color: var(--gray-700); line-height: 1.8;">
                            <div style="margin-bottom: 2rem;">
                                <h4 style="color: var(--dark-color); margin-bottom: 1rem;">خيارات التوصيل:</h4>
                                <ul style="line-height: 2;">
                                    <li><strong>التوصيل السريع:</strong> خلال 24 ساعة - 25 ر.س</li>
                                    <li><strong>التوصيل العادي:</strong> خلال 2-3 أيام - مجاني للطلبات فوق 100 ر.س</li>
                                    <li><strong>الاستلام من المعرض:</strong> مجاني - خلال ساعات العمل</li>
                                </ul>
                            </div>
                            
                            <div style="margin-bottom: 2rem;">
                                <h4 style="color: var(--dark-color); margin-bottom: 1rem;">مناطق التوصيل:</h4>
                                <p>نقوم بالتوصيل لجميع مدن المملكة. قد تختلف مدة التوصيل حسب المنطقة.</p>
                            </div>
                            
                            <div>
                                <h4 style="color: var(--dark-color); margin-bottom: 1rem;">سياسة الإرجاع:</h4>
                                <p>يمكنك إرجاع المنتج خلال 14 يوم من تاريخ الاستلام بشروط:</p>
                                <ul style="line-height: 2;">
                                    <li>أن يكون المنتج في حالته الأصلية</li>
                                    <li>أن يكون غير مستخدم</li>
                                    <li>وجود الفاتورة الأصلية</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- منتجات مشابهة -->
            <?php if (!empty($related_products)): ?>
            <section style="margin-top: 4rem;">
                <h2 style="text-align: center; margin-bottom: 2rem; color: var(--dark-color);">منتجات مشابهة</h2>
                <div class="row">
                    <?php foreach ($related_products as $related_product): ?>
                    <div class="col-3">
                        <div class="product-card" style="
                            background: white;
                            border-radius: var(--border-radius-lg);
                            box-shadow: var(--shadow-md);
                            overflow: hidden;
                            transition: all 0.3s ease;
                            height: 100%;
                        ">
                            <div style="
                                height: 180px;
                                background: linear-gradient(135deg, var(--gray-100), var(--gray-200));
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                position: relative;
                                overflow: hidden;
                            ">
                                <?php if ($related_product['image_path']): ?>
                                    <img src="<?php echo $related_product['image_path']; ?>" 
                                         alt="<?php echo $related_product['name']; ?>" 
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <i class="fas fa-box" style="font-size: 2rem; color: var(--gray-400);"></i>
                                <?php endif; ?>
                            </div>
                            
                            <div style="padding: 1rem;">
                                <h3 style="
                                    margin: 0 0 0.5rem 0; 
                                    color: var(--dark-color); 
                                    font-size: 1rem;
                                    line-height: 1.4;
                                    height: 2.8rem;
                                    overflow: hidden;
                                ">
                                    <a href="product_details.php?id=<?php echo $related_product['id']; ?>" 
                                       style="color: inherit; text-decoration: none;">
                                        <?php echo htmlspecialchars($related_product['name']); ?>
                                    </a>
                                </h3>
                                
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 1.2rem; font-weight: 700; color: var(--success-color);">
                                        <?php echo number_format($related_product['price'], 2); ?> ر.س
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
        </div>
    </section>

    <!-- الفوتر -->
    <footer style="background: var(--dark-color); color: white; padding: 2rem 0; text-align: center; margin-top: 4rem;">
        <div class="container">
            <p>&copy; 2024 متجرنا الإلكتروني. جميع الحقوق محفوظة.</p>
        </div>
    </footer>

    <script>
        // تغيير الصورة الرئيسية
        function changeImage(src) {
            const mainImage = document.getElementById('mainImage');
            if (src) {
                mainImage.src = src;
            }
            
            // تحديث الصور المصغرة النشطة
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.style.borderColor = 'var(--gray-300)';
            });
            event.target.closest('.thumbnail').style.borderColor = 'var(--primary-color)';
        }

        // إدارة الكمية
        document.getElementById('increaseQty').addEventListener('click', function() {
            const input = document.getElementById('quantity');
            const max = parseInt(input.max);
            if (parseInt(input.value) < max) {
                input.value = parseInt(input.value) + 1;
            }
        });

        document.getElementById('decreaseQty').addEventListener('click', function() {
            const input = document.getElementById('quantity');
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        });

        // التبويبات
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // إزالة النشاط من جميع الأزرار
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                // إخفاء جميع المحتويات
                document.querySelectorAll('.tab-pane').forEach(pane => pane.style.display = 'none');
                
                // تفعيل الزر الحالي
                this.classList.add('active');
                // إظهار المحتوى المقابل
                const tabId = this.dataset.tab;
                document.getElementById(tabId).style.display = 'block';
            });
        });

        // إضافة إلى السلة
        document.getElementById('addToCartBtn').addEventListener('click', function() {
            if (!<?php echo isLoggedIn() ? 'true' : 'false'; ?>) {
                alert('يجب تسجيل الدخول لإضافة منتجات إلى السلة');
                window.location.href = 'login.php?return=' + encodeURIComponent(window.location.href);
                return;
            }

            const quantity = document.getElementById('quantity').value;
            const productId = <?php echo $product['id']; ?>;
            const btn = this;
            const originalText = btn.innerHTML;

            // محاكاة الإضافة
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الإضافة...';
            btn.disabled = true;

            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-check"></i> تم الإضافة إلى السلة';
                btn.className = 'btn btn-success';
                
                // إظهار تنبيه
                showNotification('تم إضافة المنتج إلى سلة التسوق بنجاح!');
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.className = 'btn btn-primary';
                    btn.disabled = false;
                }, 2000);
            }, 1500);
        });

        // وظيفة إظهار التنبيه
        function showNotification(message) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                left: 20px;
                background: var(--success-color);
                color: white;
                padding: 1rem 1.5rem;
                border-radius: var(--border-radius);
                box-shadow: var(--shadow-lg);
                z-index: 1000;
                animation: slideIn 0.3s ease;
            `;
            notification.innerHTML = `
                <i class="fas fa-check-circle" style="margin-left: 0.5rem;"></i>
                ${message}
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // تأثيرات التمرير
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        });

        document.querySelectorAll('.col-3, .col-6').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'all 0.6s ease-out';
            observer.observe(el);
        });
    </script>

    <style>
        @keyframes slideIn {
            from { transform: translateX(-100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(-100%); opacity: 0; }
        }
        
        .tab-btn {
            background: none;
            border: none;
            padding: 1rem 2rem;
            font-size: 1rem;
            color: var(--gray-600);
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }
        
        .tab-btn.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
            font-weight: 600;
        }
        
        .tab-btn:hover {
            color: var(--primary-color);
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .thumbnail {
            transition: all 0.3s ease;
        }
        
        .thumbnail:hover {
            transform: scale(1.05);
        }
    </style>
</body>
</html>