
<?php include 'header.php'; ?>
<?php 

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


// جلب صور المنتج من جدول product_images
$stmt = $pdo->prepare("
    SELECT * FROM product_images 
    WHERE product_id = ? 
    ORDER BY is_primary DESC, id ASC
");
$stmt->execute([$product_id]);
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>
<!-- قسم الهيرو المتحرك -->
<section id="hero" class="hero-slider">
    <div class="hero-background">
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
            <div class="shape shape-4"></div>
        </div>
    </div>
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title animate-fade-in">
                    <span class="title-main">
                        <?php echo getTranslatedText('hero_title_main'); ?>
                    </span>
                    <span class="title-sub">
                        <?php echo getTranslatedText('hero_title_sub'); ?>
                    </span>
                </h1>
                <h2 class="hero-subtitle animate-fade-in-delay">
                    <?php echo getTranslatedText('hero_subtitle'); ?>
                </h2>
                <p class="hero-description animate-fade-in-delay-2">
                    <?php echo getTranslatedText('hero_description'); ?>
                </p>
                <div class="hero-actions animate-fade-in-delay-3">
                    <a href="products.html" class="cta-button primary">
                        <span><?php echo getTranslatedText('hero_button_products'); ?></span>
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <a href="about.html" class="cta-button secondary">
                        <span><?php echo getTranslatedText('hero_button_about'); ?></span>
                        <i class="fas fa-user"></i>
                    </a>
                    <a href="contact.html" class="cta-button secondary">
                        <span><?php echo getTranslatedText('hero_button_contact'); ?></span>
                        <i class="fas fa-phone"></i>
                    </a>
                </div>
            </div>
            <div class="hero-visual">
                <div class="product-showcase">
                    <div class="showcase-item item-1">
                        <i class="fas fa-fire"></i>
                        <span><?php echo getTranslatedText('hero_category_energy'); ?></span>
                    </div>
                    <div class="showcase-item item-2">
                        <i class="fas fa-tint"></i>
                        <span><?php echo getTranslatedText('hero_category_purification'); ?></span>
                    </div>
                    <div class="showcase-item item-3">
                        <i class="fas fa-spray-can"></i>
                        <span><?php echo getTranslatedText('hero_category_cleaning'); ?></span>
                    </div>
                    <div class="showcase-item item-4">
                        <i class="fas fa-box"></i>
                        <span><?php echo getTranslatedText('hero_category_supplies'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="scroll-indicator">
        <div class="scroll-arrow"></div>
    </div>
</section>
    <main>
<style>
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
        <!-- قسم من نحن -->
                
            <!-- قسم من نحن -->
            <section id="about" class="about-section">
                <div class="container">
                    <h2 class="section-title">
                            <p><?php echo getTranslatedText('about_description'); ?></p>
                  </h2>
                    <div class="about-content">
                        <div class="about-text">
                            <div class="company-description">
                                <p><?php echo getTranslatedText('about_description'); ?></p>
                            </div>
                            
                            <div class="mission-vision-grid">
                                <div class="mission-card">
                                    <div class="card-icon">
                                        <i class="fas fa-bullseye"></i>
                                    </div>
                                    <h3><?php echo getTranslatedText('mission_title'); ?></h3>
                                    <p><?php echo getTranslatedText('mission_description'); ?></p>
                                </div>
                                <div class="vision-card">
                                    <div class="card-icon">
                                        <i class="fas fa-eye"></i>
                                    </div>
                                    <h3><?php echo getTranslatedText('vision_title'); ?></h3>
                                    <p><?php echo getTranslatedText('vision_description'); ?></p>
                                </div>
                                <div class="values-card">
                                    <div class="card-icon">
                                        <i class="fas fa-heart"></i>
                                    </div>
                                    <h3><?php echo getTranslatedText('values_title'); ?></h3>
                                    <p><?php echo getTranslatedText('values_description'); ?></p>
                                </div>
                            </div>
                        </div>
                           <div class="about-visual">
                        <div class="floating-cards">
                            <div class="floating-card card-1">
                                <i class="fas fa-award"></i>
                                <span data-ar="جودة عالية" data-en="High Quality">جودة عالية</span>
                            </div>
                            <div class="floating-card card-2">
                                <i class="fas fa-users"></i>
                                <span data-ar="ثقة العملاء" data-en="Customer Trust">ثقة العملاء</span>
                            </div>
                            <div class="floating-card card-3">
                                <i class="fas fa-shipping-fast"></i>
                                <span data-ar="توصيل سريع" data-en="Fast Delivery">توصيل سريع</span>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </section>

            
            <!-- قسم الخدمات -->
            <section id="services" class="services-section">
                <div class="container">
                    <h2 class="section-title">
                        <span class="title-ar"><?php echo getTranslatedText('services_title'); ?></span>
                    </h2>
                    
                    <div class="services-grid">
                        <?php
                        // جلب الخدمات من قاعدة البيانات
                        try {
                            $stmt = $pdo->query("SELECT * FROM services ORDER BY created_at DESC");
                            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (count($services) > 0) {
                                foreach ($services as $service) {
                                    // تحديد النص حسب اللغة
                                    $title = $current_lang == 'ar' ? $service['title'] : $service['title_en'];
                                    $description = $current_lang == 'ar' ? $service['description'] : $service['description_en'];
                                    
                                    // إذا لم يكن هناك نص بالإنجليزية، نستخدم النص العربي
                                    if (empty($title)) $title = $service['title'];
                                    if (empty($description)) $description = $service['description'];
                                    
                                    echo '
                                    <div class="service-card">
                                        <div class="service-icon">
                                            <i class="' . htmlspecialchars($service['icon']) . '"></i>
                                        </div>
                                        <div class="service-content">
                                            <h3 class="service-title">' . htmlspecialchars($title) . '</h3>
                                            <p class="service-description">' . htmlspecialchars($description) . '</p>
                                        </div>
                                    </div>';
                                }
                            } else {
                                echo '<div class="no-services">' . ($current_lang == 'ar' ? 'لا توجد خدمات مضافة حالياً' : 'No services added yet') . '</div>';
                            }
                        } catch (PDOException $e) {
                            echo '<div class="error-message">' . ($current_lang == 'ar' ? 'خطأ في تحميل الخدمات' : 'Error loading services') . '</div>';
                        }
                        ?>
                    </div>
                </div>
            </section>
                    <!-- قسم الإحصائيات والرسوم البيانية -->
                <!-- قسم الإحصائيات -->
                    <h2 class="section-title">
                        <span class="title"><?php echo getTranslatedText('stats_title'); ?></span>
                      </h2>
            <section id="stats" class="stats-section">
               
                <div class="container">
                    
                 
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" data-count="<?php echo getTranslatedText('products_count'); ?>">
                                    <?php echo getTranslatedText('products_count'); ?>
                                </div>
                                <div class="stat-label"><?php echo getTranslatedText('products_label'); ?></div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" data-count="<?php echo getTranslatedText('customers_count'); ?>">
                                    <?php echo getTranslatedText('customers_count'); ?>
                                </div>
                                <div class="stat-label"><?php echo getTranslatedText('customers_label'); ?></div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" data-count="<?php echo getTranslatedText('experience_count'); ?>">
                                    <?php echo getTranslatedText('experience_count'); ?>
                                </div>
                                <div class="stat-label"><?php echo getTranslatedText('experience_label'); ?></div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" data-count="<?php echo getTranslatedText('deliveries_count'); ?>">
                                    <?php echo getTranslatedText('deliveries_count'); ?>
                                </div>
                                <div class="stat-label"><?php echo getTranslatedText('deliveries_label'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="charts-container">
                        <div class="chart-wrapper">
                            <canvas id="salesChart"></canvas>
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="productsChart"></canvas>
                        </div>
                    </div>
                    
                </div>
            </section>


            <!-- قسم المنتجات -->
            <section id="products" class="products-section">
                <div class="container">
                    <h2 class="section-title">
                    <span class="title-ar"><?php echo getTranslatedText('products_title'); ?></span>
                <span class="title-en"><?php echo getTranslatedText('products_title_en'); ?></span>
        
                    </h2>
                    <div class="container">
  
        
                        <div class="search-container">
                                <form method="GET" class="search-form">
                                    <input type="text" name="search" class="search-input" placeholder="ابحث عن منتج بالاسم، الكود أو العلامة التجارية..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
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
                                                              <?php
                                    // جلب صور المنتج لكل بطاقة
                                    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, id ASC");
                                    $stmt->execute([$product['id']]);
                                    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                    // في حال لا توجد صور
                                    if (empty($images)) {
                                        $images[] = ['image_name' => null];
                                    }
                                    ?>
                                    <div class="product-card" onclick="openProductModal(<?php echo htmlspecialchars(json_encode(['id' => $product['id'],'Item_Name' => $product['Item_Name'],  'Item_Code' => $product['Item_Code'], 'Item_Group' => $product['Item_Group'],  'Brand' => $product['Brand'], 'Packing' => $product['Packing'], 'featured' => $product['featured'], 'primary_image' => $product['primary_image'],'S_NO' => $product['S_NO']])); ?>)">

                

     

    
                                        <?php if($product['featured']): ?>
                                            <div class="product-badge">منتج مميز</div>
                                        <?php endif; ?>
                                        
                                       <div class="product-gallery">
                                                <img id="mainImage<?php echo $product['id']; ?>" 
                                                    src="<?php echo $images[0]['image_name'] ? 'uploads/'.$images[0]['image_name'] : 'https://via.placeholder.com/600x400/e0e0e0/666666?text=لا+توجد+صورة'; ?>" 
                                                    alt="<?php echo htmlspecialchars($product['Item_Name']); ?>" 
                                                    class="main-image">

                                                <?php if(count($images) > 1): ?>
                                                <div class="image-thumbnails">
                                                    <?php foreach($images as $index => $image): ?>
                                                        <img src="<?php echo $image['image_name'] ? 'uploads/'.$image['image_name'] : 'https://via.placeholder.com/80x80/e0e0e0/666666?text=لا+توجد+صورة'; ?>" 
                                                            alt="صورة المنتج" 
                                                            class="thumbnail <?php echo $index == 0 ? 'active' : ''; ?>" 
                                                            data-image="<?php echo $image['image_name'] ? 'uploads/'.$image['image_name'] : 'https://via.placeholder.com/600x400/e0e0e0/666666?text=لا+توجد+صورة'; ?>">
                                                    <?php endforeach; ?>
                                                </div>
                                                <?php endif; ?>
                                            </div>


                                        
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
                                                <button class="btn btn-details" onclick="event.stopPropagation(); openProductModal(<?php echo htmlspecialchars(json_encode([
                                                    'id' => $product['id'],
                                                    'Item_Name' => $product['Item_Name'],
                                                    'Item_Code' => $product['Item_Code'],
                                                    'Item_Group' => $product['Item_Group'],
                                                    'Brand' => $product['Brand'],
                                                    'Packing' => $product['Packing'],
                                                    'featured' => $product['featured'],
                                                    'primary_image' => $product['primary_image'],
                                                    'S_NO' => $product['S_NO']
                                                ])); ?>)">
                                                    <i class="fas fa-eye"></i> التفاصيل
                                                </button>
                                                <a href="order.php?product_id=<?php echo $product['id']; ?>" class="btn btn-order" onclick="event.stopPropagation();">
                                                    <i class="fas fa-shopping-cart"></i> طلب
                                                </a>
                                                
                                                        <a href="product_details.php?id=<?php echo $product['id']; ?>" class="btn btn-warning">
                                                            <i class="fas fa-eye"></i> التفاصيل
                                                        </a>
                                                       
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <!-- الترقيم -->
                                <?php if($total_pages > 1): ?>
                                <div class="pagination">
                                    <ul class="pagination-list">
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
                                    </ul>
                                </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                    <!-- <div class="products-filter">
                                    <button class="filter-btn active" data-filter="all">
                                <?php echo getTranslatedText('filter_all'); ?>
                            </button>
                            <button class="filter-btn" data-filter="Double Class">
                                <?php echo getTranslatedText('brand_double_class'); ?>
                            </button>
                            <button class="filter-btn" data-filter="Handles">Handles</button>
                            <button class="filter-btn" data-filter="Cleaning">
                                <?php echo getTranslatedText('filter_cleaning'); ?>
                            </button>
                            <button class="filter-btn" data-filter="Energy">
                                <?php echo getTranslatedText('filter_energy'); ?>
                            </button>
                        </div>
                    
                    <div class="products-grid" id="productsContainer">
                     
                    </div>
                    
                    <div class="products-loading">
                        <div class="loading-spinner"></div>
                        <p data-ar="جاري تحميل المنتجات..." data-en="Loading products...">جاري تحميل المنتجات...</p>
                    </div> -->
                </div>
            </section>

            <!-- قسم العلامات التجارية -->
            <section id="brands" class="brands-section">
                <div class="container">
                    <h2 class="section-title">
                        <span class="title-ar"><?php echo getTranslatedText('brands_title'); ?></span>
                    </h2>
                    
                    <div class="brands-slider">
                        <div class="brands-track">
                            <div class="brand-item">
                                <div class="brand-logo">
                                    <i class="fas fa-crown"></i>
                                </div>
                                <span class="brand-name"><?php echo getTranslatedText('brand_double_class'); ?></span>
                            </div>
                            <div class="brand-item">
                                <div class="brand-logo">
                                    <i class="fas fa-gem"></i>
                                </div>
                                <span class="brand-name"><?php echo getTranslatedText('brand_gator'); ?></span>
                            </div>
                            <div class="brand-item">
                                <div class="brand-logo">
                                    <i class="fas fa-star"></i>
                                </div>
                                <span class="brand-name"><?php echo getTranslatedText('brand_premium'); ?></span>
                            </div>
                            <div class="brand-item">
                                <div class="brand-logo">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <span class="brand-name"><?php echo getTranslatedText('brand_shield'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>


                    <!-- قسم اتصل بنا -->
                <!-- قسم اتصل بنا -->
            <section id="contact" class="contact-section">
                <div class="container">
                    <h2 class="section-title">
                        <span class="title-ar"><?php echo getTranslatedText('contact_title'); ?></span>
                        <span class="title-en"><?php echo getTranslatedText('contact_title_en'); ?></span>
                    </h2>
                    
                    <div class="contact-grid">
                        <div class="contact-info">
                            <div class="contact-card">
                                <div class="contact-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="contact-details">
                                     <h3 data-ar="العنوان" data-en="Address">العنوان</h3>
                                    <p><?php echo getTranslatedText('address'); ?></p>
                                </div>
                            </div>
                            
                            <div class="contact-card">
                                <div class="contact-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="contact-details">
                                  <h3 data-ar="هاتف" data-en="Phone">هاتف</h3>
                                    <p><?php echo getTranslatedText('phone'); ?></p>
                                </div>
                            </div>
                            
                            <div class="contact-card">
                                <div class="contact-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="contact-details">
                                       <h3 data-ar="البريد الإلكتروني" data-en="Email">البريد الإلكتروني</h3>
                                    <p><?php echo getTranslatedText('email'); ?></p>
                                </div>
                            </div>
                            
                            <div class="contact-card">
                                <div class="contact-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="contact-details">
                                   <h3 data-ar="أوقات العمل" data-en="Working Hours">أوقات العمل</h3>
                                    <p><?php echo getTranslatedText('working_hours'); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <form class="contact-form" id="contactForm">
                            <div class="form-group">
                                <input type="text" id="name" name="name" required>
                                <label for="name"><?php echo getTranslatedText('form_name'); ?></label>
                            </div>
                            
                            <div class="form-group">
                                <input type="email" id="email" name="email" required>
                                <label for="email"><?php echo getTranslatedText('form_email'); ?></label>
                            </div>
                            
                            <div class="form-group">
                                <input type="tel" id="phone" name="phone">
                                <label for="phone"><?php echo getTranslatedText('form_phone'); ?></label>
                            </div>
                            
                            <div class="form-group">
                                <textarea id="message" name="message" rows="5" required></textarea>
                                <label for="message"><?php echo getTranslatedText('form_message'); ?></label>
                            </div>
                            
                            <button type="submit" class="submit-btn">
                                <span><?php echo getTranslatedText('submit_btn'); ?></span>
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </section>

     </main>
  <?php include 'footer.php'; ?>
      <script>
        // نظام الترجمة
        document.addEventListener('DOMContentLoaded', function() {
            // تهيئة وظائف الترجمة
            initLanguageToggle();
            
            // تهيئة زر العودة للأعلى
            initScrollToTop();
            
            // تهيئة القائمة المتنقلة
            initMobileMenu();
            
            // تهيئة عداد الإحصائيات
            initStatsCounter();
        });

        // وظيفة الترجمة
        function initLanguageToggle() {
            const languageToggle = document.getElementById('languageToggle');
            const langText = document.querySelector('.lang-text');
            
            // التحقق من اللغة المحفوظة في التخزين المحلي
            const savedLanguage = localStorage.getItem('siteLanguage') || 'ar';
            setLanguage(savedLanguage);
            
            // إضافة حدث النقر على زر الترجمة
            if (languageToggle) {
                languageToggle.addEventListener('click', function() {
                    const currentLang = document.documentElement.lang;
                    const newLang = currentLang === 'ar' ? 'en' : 'ar';
                    
                    setLanguage(newLang);
                    localStorage.setItem('siteLanguage', newLang);
                });
            }
        }

        // تعيين اللغة للموقع
        function setLanguage(lang) {
            // تحديث سمة اللغة في العنصر الرئيسي
            document.documentElement.lang = lang;
            document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
            
            // تحديث نص زر الترجمة
            const langText = document.querySelector('.lang-text');
            if (langText) {
                langText.textContent = lang === 'ar' ? 'EN' : 'AR';
            }
            
            // تحديث جميع النصوص ذات سمة البيانات
            updateTextsByLanguage(lang);
            
            // تحديث العناصر الخاصة بالنماذج
            updateFormElementsLanguage(lang);
            
            // تحديث العناوين المزدوجة
            updateDualTitles(lang);
        }

        // تحديث النصوص بناءً على اللغة
        function updateTextsByLanguage(lang) {
            // تحديث النصوص مع سمة data-ar و data-en
            const elements = document.querySelectorAll('[data-ar], [data-en]');
            
            elements.forEach(element => {
                const text = lang === 'ar' ? element.getAttribute('data-ar') : element.getAttribute('data-en');
                if (text) {
                    element.textContent = text;
                }
            });
            
            // تحديث العناصر النائبة في حقول الإدخال
            const inputElements = document.querySelectorAll('input[data-ar-placeholder], input[data-en-placeholder]');
            inputElements.forEach(input => {
                const placeholder = lang === 'ar' ? input.getAttribute('data-ar-placeholder') : input.getAttribute('data-en-placeholder');
                if (placeholder) {
                    input.placeholder = placeholder;
                }
            });
            
            // تحديث خيارات القائمة المنسدلة
            const optionElements = document.querySelectorAll('option[data-ar], option[data-en]');
            optionElements.forEach(option => {
                const text = lang === 'ar' ? option.getAttribute('data-ar') : option.getAttribute('data-en');
                if (text) {
                    option.textContent = text;
                }
            });
        }

        // تحديث عناصر النماذج بناءً على اللغة
        function updateFormElementsLanguage(lang) {
            // تحديث تسميات النموذج
            const labels = document.querySelectorAll('label[data-ar], label[data-en]');
            labels.forEach(label => {
                const text = lang === 'ar' ? label.getAttribute('data-ar') : label.getAttribute('data-en');
                if (text) {
                    label.textContent = text;
                }
            });
            
            // تحديث نص أزرار الإرسال
            const buttons = document.querySelectorAll('button[data-ar], button[data-en]');
            buttons.forEach(button => {
                const text = lang === 'ar' ? button.getAttribute('data-ar') : button.getAttribute('data-en');
                if (text) {
                    button.textContent = text;
                }
            });
        }

        // تحديث العناوين المزدوجة (العربية والإنجليزية)
        function updateDualTitles(lang) {
            const sectionTitles = document.querySelectorAll('.section-title');
            
            sectionTitles.forEach(title => {
                const arabicTitle = title.querySelector('.title-ar');
                const englishTitle = title.querySelector('.title-en');
                
                if (arabicTitle && englishTitle) {
                    if (lang === 'ar') {
                        arabicTitle.style.display = 'block';
                        englishTitle.style.display = 'none';
                    } else {
                        arabicTitle.style.display = 'none';
                        englishTitle.style.display = 'block';
                    }
                }
            });
        }

        // تهيئة زر العودة للأعلى
        function initScrollToTop() {
            const scrollButton = document.querySelector('.scroll-to-top');
            
            // إظهار/إخفاء الزر عند التمرير
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    scrollButton.classList.add('visible');
                } else {
                    scrollButton.classList.remove('visible');
                }
            });
            
            // النقر للعودة للأعلى
            scrollButton.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }

        // تهيئة القائمة المتنقلة
        function initMobileMenu() {
            const menuToggle = document.querySelector('.menu-toggle');
            const mainNav = document.querySelector('.main-nav');
            
            if (menuToggle && mainNav) {
                menuToggle.addEventListener('click', function() {
                    mainNav.classList.toggle('active');
                    menuToggle.classList.toggle('active');
                });
            }
        }

        // تهيئة عداد الإحصائيات
        function initStatsCounter() {
            const statNumbers = document.querySelectorAll('.stat-number');
            
            // التحقق مما إذا كانت العدادات موجودة في الصفحة
            if (statNumbers.length > 0) {
                // إنشاء Intersection Observer لتشغيل العدادات عند ظهورها
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            startCounters();
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.5 });
                
                // مراقبة قسم الإحصائيات
                const statsSection = document.querySelector('.stats-section');
                if (statsSection) {
                    observer.observe(statsSection);
                }
            }
        }

        // تشغيل العدادات
        function startCounters() {
            const counters = document.querySelectorAll('.stat-number');
            
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-count'));
                const duration = 2000; // 2 ثانية
                const step = target / (duration / 16); // 60 إطار في الثانية
                let current = 0;
                
                const timer = setInterval(() => {
                    current += step;
                    if (current >= target) {
                        counter.textContent = target;
                        clearInterval(timer);
                    } else {
                        counter.textContent = Math.floor(current);
                    }
                }, 16);
            });
        }
    </script>



<!-- مودال المنتج -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <div class="modal-body">
            <div class="modal-image">
                <img id="modal-img" src="" alt="صورة المنتج">
            </div>
            <div class="modal-details">
                <h3 id="modal-name">اسم المنتج</h3>
                <p id="modal-description" class="modal-description">وصف المنتج</p>
                <div class="product-specs">
                    <div class="spec-item">
                        <strong data-ar="الكود:" data-en="Code:">الكود:</strong>
                        <span id="modal-code"></span>
                    </div>
                    <div class="spec-item">
                        <strong data-ar="المجموعة:" data-en="Group:">المجموعة:</strong>
                        <span id="modal-group"></span>
                    </div>
                    <div class="spec-item">
                        <strong data-ar="العلامة:" data-en="Brand:">العلامة:</strong>
                        <span id="modal-brand"></span>
                    </div>
                    <div class="spec-item">
                        <strong data-ar="التغليف:" data-en="Packing:">التغليف:</strong>
                        <span id="modal-packing"></span>
                    </div>
                    <div class="spec-item">
                        <strong data-ar="الرقم التسلسلي:" data-en="S.NO:">الرقم التسلسلي:</strong>
                        <span id="modal-sno"></span>
                    </div>
                </div>
                <div class="modal-actions">
                    <a href="#" id="modal-order-btn" class="btn btn-order">
                        <i class="fas fa-shopping-cart"></i> طلب المنتج
                    </a>
                    <button class="btn btn-secondary close-modal">
                        <i class="fas fa-times"></i> إغلاق
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
        // دالة فتح مودال المنتج
        function openProductModal(product) {
            // تعبئة البيانات في المودال
            document.getElementById('modal-name').textContent = product.Item_Name;
            document.getElementById('modal-code').textContent = product.Item_Code;
            document.getElementById('modal-group').textContent = product.Item_Group;
            document.getElementById('modal-brand').textContent = product.Brand;
            document.getElementById('modal-packing').textContent = product.Packing;
            document.getElementById('modal-sno').textContent = product.S_NO;
            
            // تعيين صورة المنتج
            const modalImg = document.getElementById('modal-img');
            if (product.primary_image) {
                modalImg.src = 'uploads/products/' + product.primary_image;
                modalImg.alt = product.Item_Name;
            } else {
                modalImg.src = 'https://via.placeholder.com/400x300/e0e0e0/666666?text=لا+توجد+صورة';
                modalImg.alt = 'لا توجد صورة';
            }
            
            // تحديث رابط الطلب
            const orderBtn = document.getElementById('modal-order-btn');
            orderBtn.href = 'order.php?product_id=' + product.id;
            
            // إظهار المودال
            const modal = document.getElementById('productModal');
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        // إغلاق المودال
        function closeProductModal() {
            const modal = document.getElementById('productModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // إضافة event listeners
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('productModal');
            const closeBtn = document.querySelector('.close-btn');
            const closeModalBtn = document.querySelector('.close-modal');
            
            // إغلاق عند النقر على ×
            closeBtn.addEventListener('click', closeProductModal);
            
            // إغلاق عند النقر على زر الإغلاق
            closeModalBtn.addEventListener('click', closeProductModal);
            
            // إغلاق عند النقر خارج المودال
            modal.addEventListener('click', function(event) {
                if (event.target === modal) {
                    closeProductModal();
                }
            });
            
            // إغلاع عند الضغط على زر ESC
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    closeProductModal();
                }
            });
        });
</script>

<style>
        /* تنسيقات المودال */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: #fff;
            margin: 2% auto;
            padding: 0;
            border-radius: 12px;
            width: 90%;
            max-width: 800px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .close-btn {
            position: absolute;
            right: 15px;
            top: 15px;
            font-size: 28px;
            font-weight: bold;
            color: #666;
            cursor: pointer;
            z-index: 1001;
            background: rgba(255,255,255,0.9);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .close-btn:hover {
            background: #ff4757;
            color: white;
            transform: rotate(90deg);
        }

        .modal-body {
            display: flex;
            flex-direction: column;
            padding: 0;
        }

        .modal-image {
            width: 100%;
            height: 300px;
            overflow: hidden;
            border-radius: 12px 12px 0 0;
        }

        .modal-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .modal-details {
            padding: 25px;
            flex: 1;
        }

        .modal-details h3 {
            margin: 0 0 15px 0;
            color: #2c3e50;
            font-size: 1.5em;
            font-weight: 600;
        }

        .modal-description {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .product-specs {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 25px;
        }

        .spec-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }

        .spec-item strong {
            color: #2c3e50;
            font-weight: 600;
        }

        .spec-item span {
            color: #555;
            font-weight: 500;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        /* تحسينات للشاشات الصغيرة */
        @media (min-width: 768px) {
            .modal-body {
                flex-direction: row;
                min-height: 400px;
            }
            
            .modal-image {
                width: 40%;
                height: auto;
                border-radius: 12px 0 0 12px;
            }
            
            .modal-details {
                width: 60%;
                padding: 30px;
            }
            
            .product-specs {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 767px) {
            .modal-content {
                width: 95%;
                margin: 5% auto;
            }
            
            .modal-actions {
                flex-direction: column;
            }
            
            .modal-actions .btn {
                width: 100%;
                text-align: center;
            }
        }

        /* تحسينات للبطاقات */
        .product-card {
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .product-actions .btn {
            margin: 2px;
            padding: 8px 12px;
            font-size: 0.85em;
        }

        /* تحسينات الترقيم */
        .pagination {
            margin-top: 30px;
            display: flex;
            justify-content: center;
        }

        .pagination-list {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
            gap: 5px;
        }

        .page-item.active .page-link {
            background-color: #3498db;
            border-color: #3498db;
            color: white;
        }

        .page-link {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            color: #3498db;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .page-link:hover {
            background-color: #f8f9fa;
            border-color: #3498db;
        }

        .page-item.disabled .page-link {
            color: #6c757d;
            pointer-events: none;
            background-color: #f8f9fa;
        }
</style>