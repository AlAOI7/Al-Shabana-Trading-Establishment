<?php
require_once 'config.php';

// التحقق من وجود معرف المنتج
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if ($product_id > 0) {
    // جلب بيانات المنتج
    $stmt = $pdo->prepare("
        SELECT * FROM products WHERE id = ?
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}

// معالجة نموذج الطلب
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $quantity = $_POST['quantity'];
    $notes = $_POST['notes'];
    $product_id = $_POST['product_id'];
    
    // حفظ الطلب في قاعدة البيانات - معدل ليناسب هيكل جدول الطلبات
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, total_amount, status, shipping_address, notes) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    // في حالة عدم وجود نظام مستخدمين، يمكن استخدام 0 أو NULL
    $user_id = NULL;
    $total_amount = 0; // يمكنك حساب المبلغ بناءً على سعر المنتج والكمية
    $status = 'pending';
    $shipping_address = "طلب منتج: " . ($product['name'] ?? 'منتج');
    
    $stmt->execute([$user_id, $total_amount, $status, $shipping_address, $notes]);
    
    $_SESSION['order_success'] = "تم تقديم طلبك بنجاح! سنتواصل معك قريباً.";
    header("Location: " . $_SERVER['PHP_SELF'] . "?product_id=" . $product_id . "&success=1");
    exit();
}

// جلب جميع الطلبات - بدون تصفية حسب المنتج لأن الجدول لا يحتوي على product_id
$orders_stmt = $pdo->query("
    SELECT * FROM orders 
    ORDER BY created_at DESC
");
$orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);
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
            max-width: 1200px;
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
        
        .btn-warning {
            background-color: var(--warning);
        }
        
        .order-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }
        
        @media (max-width: 992px) {
            .order-container {
                grid-template-columns: 1fr;
            }
        }
        
        .product-summary {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .product-image {
            width: 100%;
            max-height: 300px;
            object-fit: contain;
            border-radius: 10px;
            margin-bottom: 20px;
            background: #f8f9fa;
        }
        
        .product-title {
            font-size: 1.5rem;
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        .product-code {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .product-details {
            margin-top: 20px;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .detail-label {
            color: #666;
        }
        
        .detail-value {
            font-weight: 600;
        }
        
        .order-form {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .form-title {
            font-size: 1.5rem;
            color: var(--dark);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }
        
        input, select, textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        input:focus, select:focus, textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn-submit {
            flex: 2;
            background: var(--success);
            font-size: 1.1rem;
            padding: 15px 25px;
        }
        
        .btn-cancel {
            flex: 1;
            background: var(--info);
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    
<?php include 'header.php'; ?>

    <div class="container">
        <header>
            <div class="header-content">
                <div>
                    <h1><i class="fas fa-shopping-cart"></i> طلب المنتج</h1>
                    <p>املأ النموذج أدناه لتقديم طلبك</p>
                </div>
                <div>
                    <a href="products.php" class="btn btn-warning">
                        <i class="fas fa-arrow-right"></i> العودة للمنتجات
                    </a>
                </div>
            </div>
        </header>
        
        <?php if(isset($product) && $product): ?>
        <div class="order-container">
            <!-- ملخص المنتج -->
            <div class="product-summary">
                <h2 class="form-title">المنتج المطلوب</h2>
                
                <?php
                // جلب الصورة الرئيسية للمنتج
                $stmt = $pdo->prepare("
                    SELECT image_name FROM product_images WHERE product_id = ? AND is_primary = 1 LIMIT 1
                ");
                $stmt->execute([$product_id]);
                $image = $stmt->fetch(PDO::FETCH_ASSOC);
                ?>
                
                <?php if($image && $image['image_name']): ?>
                    <img src="uploads/<?php echo $image['image_name']; ?>" alt="<?php echo htmlspecialchars($product['Item_Name']); ?>" class="product-image">
                <?php else: ?>
                    <img src="https://via.placeholder.com/400x300/e0e0e0/666666?text=لا+توجد+صورة" alt="لا توجد صورة" class="product-image">
                <?php endif; ?>
                
                <h3 class="product-title"><?php echo $product['Item_Name']; ?></h3>
                <div class="product-code">كود المنتج: <?php echo $product['Item_Code']; ?></div>
                
                <div class="product-details">
                    <div class="detail-item">
                        <span class="detail-label">العلامة التجارية:</span>
                        <span class="detail-value"><?php echo $product['Brand']; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">المجموعة:</span>
                        <span class="detail-value"><?php echo $product['Item_Group']; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">التغليف:</span>
                        <span class="detail-value"><?php echo $product['Packing']; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">رقم المنتج:</span>
                        <span class="detail-value">#<?php echo $product['S_NO']; ?></span>
                    </div>
                </div>
            </div>
            
            <!-- نموذج الطلب -->
            <!-- نموذج الطلب -->
            <div class="order-form">
                <h2 class="form-title"><i class="fas fa-clipboard-list"></i> معلومات الطلب</h2>
                
                <form method="POST" action="">
                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    
                    <div class="form-group">
                        <label for="name"><i class="fas fa-user"></i> الاسم الكامل *</label>
                        <input type="text" id="name" name="name" required placeholder="أدخل اسمك الكامل">
                    </div>
                    
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> البريد الإلكتروني *</label>
                        <input type="email" id="email" name="email" required placeholder="example@email.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone"><i class="fas fa-phone"></i> رقم الهاتف *</label>
                        <input type="tel" id="phone" name="phone" required placeholder="05XXXXXXXX">
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity"><i class="fas fa-box"></i> الكمية المطلوبة *</label>
                        <select id="quantity" name="quantity" required>
                            <option value="">اختر الكمية</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="20">20</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes"><i class="fas fa-sticky-note"></i> ملاحظات إضافية</label>
                        <textarea id="notes" name="notes" rows="4" placeholder="أي ملاحظات إضافية حول الطلب..."></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-submit" name="place_order">
                            <i class="fas fa-paper-plane"></i> تقديم الطلب
                        </button>
                        <a href="product_details.php?id=<?php echo $product_id; ?>" class="btn btn-cancel">
                            <i class="fas fa-arrow-right"></i> إلغاء
                        </a>
                    </div>
                </form>
            </div>
        </div>

        </div>
        <?php else: ?>
            <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
                <i class="fas fa-exclamation-triangle" style="font-size: 4rem; color: #f39c12; margin-bottom: 20px;"></i>
                <h2>المنتج غير موجود</h2>
                <p>المنتج الذي تحاول طلبه غير موجود أو تم حذفه.</p>
                <a href="products.php" class="btn btn-primary" style="margin-top: 20px;">
                    <i class="fas fa-arrow-right"></i> العودة لقائمة المنتجات
                </a>
            </div>
        <?php endif; ?>
    </div>
      <?php include 'footer.php'; ?>
