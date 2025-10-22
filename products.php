<?php
require_once 'config.php';

// جلب جميع المنتجات النشطة
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

$query = "SELECT * FROM products WHERE is_active = 1";
$params = [];

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category)) {
    $query .= " AND category = ?";
    $params[] = $category;
}

$query .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// جلب الفئات المختلفة
$stmt = $pdo->query("SELECT DISTINCT category FROM products WHERE is_active = 1 AND category IS NOT NULL");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>جميع المنتجات - متجرنا</title>
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
                    <li><a href="products.php" class="active"><i class="fas fa-box"></i> المنتجات</a></li>
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

    <!-- قسم الهيرو -->
    <section style="
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 80px 0;
        text-align: center;
    ">
        <div class="container">
            <h1 style="font-size: 3rem; margin-bottom: 1rem; font-weight: 800;">منتجاتنا</h1>
            <p style="font-size: 1.2rem; opacity: 0.9;">اكتشف تشكيلتنا الكاملة من المنتجات المميزة</p>
        </div>
    </section>

    <!-- قسم البحث والتصفية -->
    <section style="padding: 2rem 0; background: var(--light-color);">
        <div class="container">
            <div class="filters" style="
                background: white;
                padding: 1.5rem;
                border-radius: var(--border-radius-lg);
                box-shadow: var(--shadow-md);
            ">
                <form method="GET" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 1rem; align-items: end;">
                    <div class="form-group">
                        <label for="search">بحث في المنتجات</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               placeholder="ابحث عن منتج..."
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="category">الفئة</label>
                        <select class="form-control" id="category" name="category">
                            <option value="">جميع الفئات</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" 
                                        <?php echo $category == $cat ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="sort">ترتيب حسب</label>
                        <select class="form-control" id="sort" name="sort">
                            <option value="newest">الأحدث</option>
                            <option value="price_low">السعر: من الأقل</option>
                            <option value="price_high">السعر: من الأعلى</option>
                            <option value="name">الاسم</option>
                        </select>
                    </div>
                    
                    <div>
                        <button type="submit" class="btn btn-primary" style="height: 42px;">
                            <i class="fas fa-search"></i> بحث
                        </button>
                        <a href="products.php" class="btn btn-outline" style="height: 42px; margin-right: 0.5rem;">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- قسم المنتجات -->
    <section style="padding: 3rem 0;">
        <div class="container">
            <!-- نتائج البحث -->
            <?php if (!empty($search) || !empty($category)): ?>
            <div style="margin-bottom: 2rem;">
                <h3 style="color: var(--gray-700);">
                    <?php
                    $results_text = "عرض " . count($products) . " منتج";
                    if (!empty($search)) {
                        $results_text .= " للبحث: \"$search\"";
                    }
                    if (!empty($category)) {
                        $results_text .= " في فئة: \"$category\"";
                    }
                    echo $results_text;
                    ?>
                </h3>
            </div>
            <?php endif; ?>

            <!-- شبكة المنتجات -->
            <?php if (empty($products)): ?>
                <div style="text-align: center; padding: 4rem;">
                    <i class="fas fa-search" style="font-size: 4rem; color: var(--gray-400); margin-bottom: 1rem;"></i>
                    <h3 style="color: var(--gray-600); margin-bottom: 1rem;">لم نعثر على منتجات</h3>
                    <p style="color: var(--gray-500); margin-bottom: 2rem;">جرب تعديل كلمات البحث أو الفلاتر</p>
                    <a href="products.php" class="btn btn-primary">
                        <i class="fas fa-undo"></i> عرض جميع المنتجات
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($products as $product): ?>
                    <div class="col-4" style="margin-bottom: 2rem;">
                        <div class="product-card" style="
                            background: white;
                            border-radius: var(--border-radius-lg);
                            box-shadow: var(--shadow-md);
                            overflow: hidden;
                            transition: all 0.3s ease;
                            height: 100%;
                            position: relative;
                        ">
                            <!-- شارة جديدة -->
                            <?php 
                            $is_new = (time() - strtotime($product['created_at'])) < (7 * 24 * 60 * 60); // منتج جديد إذا أقل من أسبوع
                            ?>
                            <?php if ($is_new): ?>
                            <div style="
                                position: absolute;
                                top: 1rem;
                                left: 1rem;
                                background: var(--gradient-danger);
                                color: white;
                                padding: 0.25rem 0.75rem;
                                border-radius: 20px;
                                font-size: 0.8rem;
                                font-weight: 600;
                                z-index: 2;
                            ">
                                <i class="fas fa-star"></i> جديد
                            </div>
                            <?php endif; ?>

                            <!-- صورة المنتج -->
                            <div class="product-image" style="
                                height: 220px;
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
                                
                                <!-- طبقة التفاعل -->
                                <div class="product-actions" style="
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
                                        <button class="btn btn-primary view-details" 
                                                data-product='<?php echo json_encode($product); ?>'
                                                style="margin: 0.25rem;">
                                            <i class="fas fa-eye"></i> عرض التفاصيل
                                        </button>
                                        <button class="btn btn-success add-to-cart" 
                                                data-product-id="<?php echo $product['id']; ?>"
                                                style="margin: 0.25rem;">
                                            <i class="fas fa-cart-plus"></i> أضف للسلة
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- محتوى المنتج -->
                            <div class="product-content" style="padding: 1.5rem;">
                                <!-- الفئة -->
                                <div style="margin-bottom: 0.75rem;">
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
                                
                                <!-- الاسم -->
                                <h3 style="
                                    margin: 0 0 1rem 0; 
                                    color: var(--dark-color); 
                                    font-size: 1.2rem;
                                    line-height: 1.4;
                                    height: 2.8rem;
                                    overflow: hidden;
                                ">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </h3>
                                
                                <!-- الوصف -->
                                <p style="
                                    color: var(--gray-600); 
                                    margin-bottom: 1rem; 
                                    line-height: 1.5; 
                                    font-size: 0.9rem;
                                    height: 4.5rem;
                                    overflow: hidden;
                                ">
                                    <?php echo htmlspecialchars($product['description']); ?>
                                </p>
                                
                                <!-- السعر والمخزون -->
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <span style="font-size: 1.5rem; font-weight: 700; color: var(--success-color);">
                                            <?php echo number_format($product['price'], 2); ?> ر.س
                                        </span>
                                    </div>
                                    <div style="text-align: left;">
                                        <span style="color: <?php echo $product['stock_quantity'] > 0 ? 'var(--success-color)' : 'var(--danger-color)'; ?>; font-size: 0.9rem;">
                                            <i class="fas fa-box"></i> 
                                            <?php echo $product['stock_quantity'] > 0 ? $product['stock_quantity'] . ' متوفر' : 'غير متوفر'; ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- تقييم النجوم -->
                                <div style="margin-top: 1rem; text-align: center;">
                                    <div class="product-rating" style="color: #ffc107;">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                        <span style="color: var(--gray-500); font-size: 0.8rem; margin-right: 0.5rem;">(4.5)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- ترقيم الصفحات -->
                <div style="text-align: center; margin-top: 3rem;">
                    <div class="pagination" style="display: inline-flex; gap: 0.5rem;">
                        <a href="#" class="btn btn-outline" style="padding: 0.5rem 1rem;"><i class="fas fa-chevron-right"></i></a>
                        <a href="#" class="btn btn-primary" style="padding: 0.5rem 1rem;">1</a>
                        <a href="#" class="btn btn-outline" style="padding: 0.5rem 1rem;">2</a>
                        <a href="#" class="btn btn-outline" style="padding: 0.5rem 1rem;">3</a>
                        <a href="#" class="btn btn-outline" style="padding: 0.5rem 1rem;"><i class="fas fa-chevron-left"></i></a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- نافذة عرض التفاصيل -->
    <div id="productModal" style="
        display: none;
        position: fixed;
        top: 0;
        right: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    ">
        <div class="modal-content" style="
            background: white;
            border-radius: var(--border-radius-lg);
            max-width: 800px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        ">
            <button id="closeModal" style="
                position: absolute;
                top: 1rem;
                left: 1rem;
                background: var(--danger-color);
                color: white;
                border: none;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                cursor: pointer;
                z-index: 2;
            ">
                <i class="fas fa-times"></i>
            </button>
            
            <div id="modalBody" style="padding: 2rem;">
                <!-- سيتم ملؤه بالجافاسكريبت -->
            </div>
        </div>
    </div>

    <!-- الفوتر -->
    <footer style="background: var(--dark-color); color: white; padding: 2rem 0; text-align: center;">
        <div class="container">
            <p>&copy; 2024 متجرنا الإلكتروني. جميع الحقوق محفوظة.</p>
        </div>
    </footer>

    <script>
        // تأثيرات البطاقات
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px)';
                this.style.boxShadow = '0 20px 40px rgba(0,0,0,0.15)';
                this.querySelector('.product-actions').style.opacity = '1';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'var(--shadow-md)';
                this.querySelector('.product-actions').style.opacity = '0';
            });
        });

        // عرض تفاصيل المنتج
        document.querySelectorAll('.view-details').forEach(button => {
            button.addEventListener('click', function() {
                const product = JSON.parse(this.dataset.product);
                showProductModal(product);
            });
        });

        // إضافة للسلة
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.productId;
                addToCart(productId);
            });
        });

        // نافذة العرض
        const modal = document.getElementById('productModal');
        const closeModal = document.getElementById('closeModal');

        closeModal.addEventListener('click', function() {
            modal.style.display = 'none';
        });

        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });

        function showProductModal(product) {
            const modalBody = document.getElementById('modalBody');
            
            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-6">
                        <div style="
                            background: linear-gradient(135deg, var(--gray-100), var(--gray-200));
                            border-radius: var(--border-radius);
                            height: 300px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        ">
                            ${product.image_path ? 
                                `<img src="${product.image_path}" alt="${product.name}" style="max-width: 100%; max-height: 100%;">` :
                                `<i class="fas fa-box" style="font-size: 4rem; color: var(--gray-400);"></i>`
                            }
                        </div>
                    </div>
                    <div class="col-6">
                        <h2 style="margin-bottom: 1rem; color: var(--dark-color);">${product.name}</h2>
                        <div style="margin-bottom: 1rem;">
                            <span style="
                                background: var(--gradient-primary);
                                color: white;
                                padding: 0.5rem 1rem;
                                border-radius: 20px;
                                font-size: 0.9rem;
                                font-weight: 600;
                            ">
                                ${product.category}
                            </span>
                        </div>
                        <p style="color: var(--gray-600); line-height: 1.6; margin-bottom: 1.5rem;">
                            ${product.description}
                        </p>
                        <div style="margin-bottom: 1.5rem;">
                            <h3 style="color: var(--success-color); font-size: 2rem; margin-bottom: 0.5rem;">
                                ${parseFloat(product.price).toFixed(2)} ر.س
                            </h3>
                            <p style="color: ${product.stock_quantity > 0 ? 'var(--success-color)' : 'var(--danger-color)'};">
                                <i class="fas fa-box"></i>
                                ${product.stock_quantity > 0 ? product.stock_quantity + ' متوفر في المخزن' : 'غير متوفر حالياً'}
                            </p>
                        </div>
                        <div style="display: flex; gap: 1rem;">
                            <button class="btn btn-primary add-to-cart" data-product-id="${product.id}" style="flex: 1;">
                                <i class="fas fa-cart-plus"></i> أضف إلى السلة
                            </button>
                            <button class="btn btn-outline" style="flex: 1;">
                                <i class="fas fa-heart"></i> المفضلة
                            </button>
                        </div>
                    </div>
                </div>
            `;

            // إعادة ربط حدث إضافة للسلة
            modalBody.querySelector('.add-to-cart').addEventListener('click', function() {
                addToCart(product.id);
            });

            modal.style.display = 'flex';
        }

        function addToCart(productId) {
            if (!<?php echo isLoggedIn() ? 'true' : 'false'; ?>) {
                alert('يجب تسجيل الدخول لإضافة منتجات إلى السلة');
                window.location.href = 'login.php';
                return;
            }

            // محاكاة إضافة للسلة
            const btn = event.target;
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الإضافة...';
            btn.disabled = true;

            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-check"></i> تم الإضافة';
                btn.className = 'btn btn-success';
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.className = 'btn btn-primary';
                    btn.disabled = false;
                }, 2000);
            }, 1000);
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

        document.querySelectorAll('.col-4').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'all 0.6s ease-out';
            observer.observe(el);
        });
    </script>
</body>
</html>