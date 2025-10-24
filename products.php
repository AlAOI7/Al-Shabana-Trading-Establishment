<?php
require_once 'config.php';


// معالجة البحث والترقيم
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// بناء استعلام البحث
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(Item_Name LIKE ? OR Item_Code LIKE ? OR Brand LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_sql = '';
if (!empty($where_conditions)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_conditions);
}

// جلب إجمالي عدد المنتجات
$count_sql = "SELECT COUNT(*) as total FROM products $where_sql";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_products = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_products / $limit);

// جلب المنتجات للصفحة الحالية
$products_sql = "
    SELECT p.*, 
           (SELECT image_name FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
    FROM products p 
    $where_sql 
    ORDER BY p.featured DESC, p.id DESC 
    LIMIT $limit OFFSET $offset
";

$stmt = $pdo->prepare($products_sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>



    <style>
        :root {
            --primary: #3498db;
            --secondary: #2c3e50;
            --success: #2ecc71;
            --danger: #e74c3c;
            --warning: #f39c12;
            --info: #17a2b8;
            --light: #ecf0f1;
            --dark: #34495e;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            width: 95%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            color: white;
            padding: 25px 0;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 30px;
        }
        
        h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn-success {
            background-color: var(--success);
        }
        
        .btn-success:hover {
            background-color: #27ae60;
        }
        
        .btn-danger {
            background-color: var(--danger);
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        .btn-warning {
            background-color: var(--warning);
        }
        
        .btn-warning:hover {
            background-color: #d35400;
        }
        
        .btn-info {
            background-color: var(--info);
        }
        
        .search-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .search-form {
            display: flex;
            gap: 15px;
        }
        
        .search-input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .search-input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }
        
        .product-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-bottom: 1px solid #eee;
        }
        
        .product-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--warning);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .product-content {
            padding: 20px;
        }
        
        .product-code {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .product-name {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark);
            height: 60px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        
        .product-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 14px;
            color: #666;
        }
        
        .product-brand {
            font-weight: 600;
            color: var(--secondary);
        }
        
        .product-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-details {
            flex: 1;
            background: var(--info);
        }
        
        .btn-order {
            flex: 1;
            background: var(--success);
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 40px;
        }
        
        .page-item {
            display: inline-block;
        }
        
        .page-link {
            padding: 10px 18px;
            border: 1px solid #ddd;
            border-radius: 8px;
            color: var(--dark);
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 600;
        }
        
        .page-link:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .page-item.active .page-link {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .page-item.disabled .page-link {
            color: #ccc;
            pointer-events: none;
        }
        
        .results-info {
            text-align: center;
            margin-bottom: 20px;
            color: #666;
            font-size: 1.1rem;
        }
        
        .no-products {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .no-products i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #ddd;
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .pagination {
                flex-wrap: wrap;
            }
        }
    </style>

<?php include 'header.php'; ?>


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
    <br><br>

    <!-- قسم البحث والتصفية -->
    <!-- <section style="padding: 2rem 0; background: var(--light-color);">
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
    </section> -->

     <div class="container">
        <header>
            <div class="header-content">
                <div>
                    <h1><i class="fas fa-boxes"></i> معرض المنتجات</h1>
                    <p>اكتشف مجموعتنا المميزة من المنتجات عالية الجودة</p>
                </div>
              
            </div>
        </header>
        
        <div class="search-container">
            <form method="GET" class="search-form">
                <input type="text" name="search" class="search-input" placeholder="ابحث عن منتج بالاسم، الكود أو العلامة التجارية..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-info">
                    <i class="fas fa-search"></i> بحث
                </button>
                <?php if(!empty($search)): ?>
                    <a href="products.php" class="btn btn-danger">
                        <i class="fas fa-times"></i> إلغاء البحث
                    </a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="results-info">
            <p>
                <?php if(!empty($search)): ?>
                    عرض <?php echo count($products); ?> من أصل <?php echo $total_products; ?> منتج للبحث "<?php echo htmlspecialchars($search); ?>"
                <?php else: ?>
                    عرض <?php echo count($products); ?> من أصل <?php echo $total_products; ?> منتج
                <?php endif; ?>
            </p>
        </div>
        
        <?php if(empty($products)): ?>
            <div class="no-products">
                <i class="fas fa-box-open"></i>
                <h3>لا توجد منتجات</h3>
                <p>لم نعثر على أي منتج يطابق معايير البحث</p>
                <?php if(!empty($search)): ?>
                    <a href="products.php" class="btn btn-primary" style="margin-top: 15px;">
                        <i class="fas fa-undo"></i> عرض جميع المنتجات
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach($products as $product): ?>
                <div class="product-card">
                    <?php if($product['featured']): ?>
                        <div class="product-badge">منتج مميز</div>
                    <?php endif; ?>
                    
                    <?php if($product['primary_image']): ?>
                        <img src="uploads/products<?php echo $product['primary_image']; ?>" alt="<?php echo htmlspecialchars($product['Item_Name']); ?>" class="product-image">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/300x220/e0e0e0/666666?text=لا+توجد+صورة" alt="لا توجد صورة" class="product-image">
                    <?php endif; ?>
                    
                    <div class="product-content">
                        <div class="product-code"><?php echo $product['Item_Code']; ?></div>
                        <h3 class="product-name"><?php echo $product['Item_Name']; ?></h3>
                        
                        <div class="product-meta">
                            <div class="product-brand">
                                <i class="fas fa-tag"></i> <?php echo $product['Brand']; ?>
                            </div>
                            <div class="product-group">
                                <i class="fas fa-layer-group"></i> <?php echo $product['Item_Group']; ?>
                            </div>
                        </div>
                        
                        <div class="product-meta">
                            <div class="product-packing">
                                <i class="fas fa-box"></i> <?php echo $product['Packing']; ?>
                            </div>
                            <div class="product-sno">
                                #<?php echo $product['S_NO']; ?>
                            </div>
                        </div>
                        
                        <div class="product-actions">
                            <a href="product_details.php?id=<?php echo $product['id']; ?>" class="btn btn-details">
                                <i class="fas fa-eye"></i> التفاصيل
                            </a>
                            <a href="order.php?product_id=<?php echo $product['id']; ?>" class="btn btn-order">
                                <i class="fas fa-shopping-cart"></i> طلب
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- الترقيم -->
            <?php if($total_pages > 1): ?>
            <div class="pagination">
                <!-- زر الصفحة السابقة -->
                <?php if($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>">
                            <i class="fas fa-chevron-right"></i> السابق
                        </a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled">
                        <span class="page-link"><i class="fas fa-chevron-right"></i> السابق</span>
                    </li>
                <?php endif; ?>
                
                <!-- أرقام الصفحات -->
                <?php 
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                for($i = $start_page; $i <= $end_page; $i++): 
                ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
                
                <!-- زر الصفحة التالية -->
                <?php if($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>">
                            التالي <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled">
                        <span class="page-link">التالي <i class="fas fa-chevron-left"></i></span>
                    </li>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

   
       
       



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
  <?php include 'footer.php'; ?>