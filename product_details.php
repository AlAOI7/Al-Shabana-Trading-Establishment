<?php
require_once 'config.php';

// التحقق من وجود معرف المنتج
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = intval($_GET['id']);

// جلب بيانات المنتج
$stmt = $pdo->prepare("
    SELECT * FROM products WHERE id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// التحقق من وجود المنتج
if (!$product) {
    header("Location: products.php");
    exit();
}

// جلب صور المنتج
$stmt = $pdo->prepare("
    SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, id ASC
");
$stmt->execute([$product_id]);
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// إذا لم يكن هناك صور، نستخدم صورة افتراضية
if (empty($images)) {
    $images[] = ['image_name' => null];
}
?>

    <title><?php echo htmlspecialchars($product['Item_Name']); ?> - تفاصيل المنتج</title>
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
        
        .product-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }
        
        @media (max-width: 992px) {
            .product-details {
                grid-template-columns: 1fr;
            }
        }
        
        .product-gallery {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .main-image {
            width: 100%;
            height: 400px;
            object-fit: contain;
            border-radius: 10px;
            margin-bottom: 20px;
            background: #f8f9fa;
        }
        
        .image-thumbnails {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding: 10px 0;
        }
        
        .thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s;
        }
        
        .thumbnail:hover, .thumbnail.active {
            border-color: var(--primary);
        }
        
        .product-info {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .product-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .product-title {
            font-size: 1.8rem;
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        .product-code {
            color: var(--primary);
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .featured-badge {
            background: var(--warning);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .product-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .meta-icon {
            width: 40px;
            height: 40px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .meta-content h4 {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .meta-content p {
            font-weight: 600;
            color: var(--dark);
        }
        
        .product-description {
            margin-bottom: 30px;
        }
        
        .description-title {
            font-size: 1.3rem;
            color: var(--dark);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .description-text {
            line-height: 1.8;
            color: #555;
        }
        
        .product-actions {
            display: flex;
            gap: 15px;
        }
        
        .btn-order {
            flex: 2;
            background: var(--success);
            font-size: 1.1rem;
            padding: 15px 25px;
        }
        
        .btn-back {
            flex: 1;
            background: var(--info);
        }
        
        .related-products {
            margin-top: 60px;
        }
        
        .section-title {
            font-size: 1.8rem;
            color: var(--dark);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        
        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .product-content {
            padding: 20px;
        }
        
        .product-name {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
            height: 50px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        
        @media (max-width: 768px) {
            .product-meta {
                grid-template-columns: 1fr;
            }
            
            .product-actions {
                flex-direction: column;
            }
            
            .header-content {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
        }
    </style>

<?php include 'header.php'; ?>

    <div class="container">
        <header>
            <div class="header-content">
                <div>
                    <h1><i class="fas fa-box-open"></i> تفاصيل المنتج</h1>
                    <p>استكشف المواصفات الكاملة للمنتج</p>
                </div>
                <div>
                    <a href="products.php" class="btn btn-warning">
                        <i class="fas fa-arrow-right"></i> العودة للمنتجات
                    </a>
                </div>
            </div>
        </header>
        
        <div class="product-details">
            <!-- معرض الصور -->
            <div class="product-gallery">
                <img id="mainImage" src="<?php echo $images[0]['image_name'] ? 'uploads/'.$images[0]['image_name'] : 'https://via.placeholder.com/600x400/e0e0e0/666666?text=لا+توجد+صورة'; ?>" alt="<?php echo htmlspecialchars($product['Item_Name']); ?>" class="main-image">
                
                <?php if(count($images) > 1): ?>
                <div class="image-thumbnails">
                    <?php foreach($images as $index => $image): ?>
                        <img src="<?php echo $image['image_name'] ? 'uploads/'.$image['image_name'] : 'https://via.placeholder.com/80x80/e0e0e0/666666?text=لا+توجد+صورة'; ?>" alt="<?php echo htmlspecialchars($product['Item_Name']); ?>" class="thumbnail <?php echo $index == 0 ? 'active' : ''; ?>" data-image="<?php echo $image['image_name'] ? 'uploads/'.$image['image_name'] : 'https://via.placeholder.com/600x400/e0e0e0/666666?text=لا+توجد+صورة'; ?>">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- معلومات المنتج -->
            <div class="product-info">
                <div class="product-header">
                    <div>
                        <h2 class="product-title"><?php echo $product['Item_Name']; ?></h2>
                        <div class="product-code">كود المنتج: <?php echo $product['Item_Code']; ?></div>
                    </div>
                    <?php if($product['featured']): ?>
                        <div class="featured-badge">منتج مميز</div>
                    <?php endif; ?>
                </div>
                
                <div class="product-meta">
                    <div class="meta-item">
                        <div class="meta-icon">
                            <i class="fas fa-hashtag"></i>
                        </div>
                        <div class="meta-content">
                            <h4>رقم المنتج</h4>
                            <p>#<?php echo $product['S_NO']; ?></p>
                        </div>
                    </div>
                    
                    <div class="meta-item">
                        <div class="meta-icon">
                            <i class="fas fa-tag"></i>
                        </div>
                        <div class="meta-content">
                            <h4>العلامة التجارية</h4>
                            <p><?php echo $product['Brand']; ?></p>
                        </div>
                    </div>
                    
                    <div class="meta-item">
                        <div class="meta-icon">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div class="meta-content">
                            <h4>المجموعة</h4>
                            <p><?php echo $product['Item_Group']; ?></p>
                        </div>
                    </div>
                    
                    <div class="meta-item">
                        <div class="meta-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="meta-content">
                            <h4>التغليف</h4>
                            <p><?php echo $product['Packing']; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="product-description">
                    <h3 class="description-title">وصف المنتج</h3>
                    <div class="description-text">
                        <?php echo nl2br(htmlspecialchars($product['Item_Name'])); ?>
                    </div>
                </div>
                
                <div class="product-actions">
                    <a href="order.php?product_id=<?php echo $product['id']; ?>" class="btn btn-order">
                        <i class="fas fa-shopping-cart"></i> طلب المنتج
                    </a>
                    <a href="products.php" class="btn btn-back">
                        <i class="fas fa-arrow-right"></i> العودة
                    </a>
                </div>
            </div>
        </div>
        
        <!-- منتجات ذات صلة -->
        <?php
        // جلب منتجات ذات صلة (من نفس المجموعة أو العلامة التجارية)
        $stmt = $pdo->prepare("
            SELECT p.*, 
                   (SELECT image_name FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
            FROM products p 
            WHERE (p.Item_Group = ? OR p.Brand = ?) AND p.id != ?
            ORDER BY p.featured DESC, p.id DESC 
            LIMIT 4
        ");
        $stmt->execute([$product['Item_Group'], $product['Brand'], $product_id]);
        $related_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($related_products)):
        ?>
        <div class="related-products">
            <h2 class="section-title">منتجات ذات صلة</h2>
            <div class="products-grid">
                <?php foreach($related_products as $related): ?>
                <div class="product-card">
                    <?php if($related['primary_image']): ?>
                        <img src="uploads/<?php echo $related['primary_image']; ?>" alt="<?php echo htmlspecialchars($related['Item_Name']); ?>" class="product-image">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/280x200/e0e0e0/666666?text=لا+توجد+صورة" alt="لا توجد صورة" class="product-image">
                    <?php endif; ?>
                    
                    <div class="product-content">
                        <h3 class="product-name"><?php echo $related['Item_Name']; ?></h3>
                        <div class="product-code"><?php echo $related['Item_Code']; ?></div>
                        
                        <div style="margin-top: 15px; display: flex; gap: 10px;">
                            <a href="product_details.php?id=<?php echo $related['id']; ?>" class="btn" style="flex: 1; padding: 8px 15px; font-size: 14px;">
                                <i class="fas fa-eye"></i> التفاصيل
                            </a>
                            <a href="order.php?product_id=<?php echo $related['id']; ?>" class="btn btn-success" style="flex: 1; padding: 8px 15px; font-size: 14px;">
                                <i class="fas fa-shopping-cart"></i> طلب
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        // تبديل الصور في المعرض
        document.querySelectorAll('.thumbnail').forEach(thumb => {
            thumb.addEventListener('click', function() {
                // تحديث الصورة الرئيسية
                document.getElementById('mainImage').src = this.dataset.image;
                
                // تحديث الصورة النشطة
                document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
  <?php include 'footer.php'; ?>
