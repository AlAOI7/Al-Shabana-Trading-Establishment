<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// جلب جميع المنتجات
try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
    $error = "خطأ في جلب البيانات: " . $e->getMessage();
}

// معالجة إضافة/تعديل المنتجات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $category = $_POST['category'];
        $stock = $_POST['stock_quantity'];
        
        // معالجة رفع الصورة
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $upload_dir = '../uploads/products/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
                $image_path = 'uploads/products/' . $file_name;
            }
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image_path, category, stock_quantity) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $image_path, $category, $stock]);
            
            header("Location: products.php?success=1");
            exit();
        } catch (PDOException $e) {
            $error = "خطأ في إضافة المنتج: " . $e->getMessage();
        }
    }
    
    // معالجة تحديث المنتج
    if (isset($_POST['update_product'])) {
        $product_id = $_POST['product_id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $category = $_POST['category'];
        $stock = $_POST['stock_quantity'];
        
        // معالجة رفع الصورة الجديدة
        $image_path = $_POST['current_image'] ?? '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $upload_dir = '../uploads/products/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
                $image_path = 'uploads/products/' . $file_name;
                // حذف الصورة القديمة إذا كانت موجودة
                if (!empty($_POST['current_image']) && file_exists('../' . $_POST['current_image'])) {
                    unlink('../' . $_POST['current_image']);
                }
            }
        }
        
        try {
            $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, image_path = ?, category = ?, stock_quantity = ? WHERE id = ?");
            $stmt->execute([$name, $description, $price, $image_path, $category, $stock, $product_id]);
            
            header("Location: products.php?success=1");
            exit();
        } catch (PDOException $e) {
            $error = "خطأ في تحديث المنتج: " . $e->getMessage();
        }
    }
    
    // معالجة حذف المنتج
    if (isset($_POST['delete_product'])) {
        $product_id = $_POST['product_id'];
        
        try {
            // جلب مسار الصورة لحذفها
            $stmt = $pdo->prepare("SELECT image_path FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if ($product && $product['image_path'] && file_exists('../' . $product['image_path'])) {
                unlink('../' . $product['image_path']);
            }
            
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            
            header("Location: products.php?success=1");
            exit();
        } catch (PDOException $e) {
            $error = "خطأ في حذف المنتج: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-translate="products_management">إدارة المنتجات</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a6cf7;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        .main-content {
            padding: 20px;
            background-color: #f5f7fb;
            min-height: 100vh;
        }

        .header {
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, var(--primary-color), #6a11cb);
            border-radius: var(--border-radius);
            color: white;
            box-shadow: var(--box-shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            margin: 0;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            padding: 20px 25px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            background: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h3 {
            margin: 0;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--dark-color);
        }

        .card-body {
            padding: 25px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            gap: 8px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: #3a5bd9;
            transform: translateY(-2px);
        }

        .btn-success {
            background-color: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }

        .btn-info {
            background-color: var(--info-color);
            color: white;
        }

        .btn-info:hover {
            background-color: #138496;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        /* النوافذ المنبثقة */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
            box-sizing: border-box;
        }

        .modal-content {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalFadeIn 0.3s ease-out;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.4rem;
            color: var(--dark-color);
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--secondary-color);
            transition: var(--transition);
        }

        .close-btn:hover {
            color: var(--danger-color);
        }

        .modal-body {
            padding: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-color);
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: var(--transition);
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(74, 108, 247, 0.2);
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-start;
            gap: 10px;
        }

        /* جدول المنتجات */
        .table-container {
            overflow-x: auto;
            border-radius: var(--border-radius);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        table thead {
            background-color: #f8f9fa;
        }

        table th {
            padding: 15px 12px;
            text-align: right;
            font-weight: 600;
            color: var(--dark-color);
            border-bottom: 2px solid #dee2e6;
            font-size: 0.9rem;
        }

        table td {
            padding: 15px 12px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }

        table tbody tr {
            transition: var(--transition);
        }

        table tbody tr:hover {
            background-color: rgba(74, 108, 247, 0.05);
        }

        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #eee;
        }

        .no-image {
            width: 60px;
            height: 60px;
            background: #f8f9fa;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--secondary-color);
            font-size: 0.8rem;
            text-align: center;
            border: 1px solid #eee;
        }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .product-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .product-image-large {
            width: 100%;
            max-width: 300px;
            height: 250px;
            object-fit: cover;
            border-radius: 8px;
            margin: 0 auto;
            display: block;
        }

        .product-info {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .info-label {
            font-weight: 600;
            color: var(--dark-color);
        }

        .info-value {
            color: var(--secondary-color);
        }

        .description-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-right: 4px solid var(--primary-color);
        }

        .success-message {
            background: rgba(40, 167, 69, 0.15);
            color: #155724;
            padding: 15px 20px;
            border-radius: var(--border-radius);
            border: 1px solid rgba(40, 167, 69, 0.3);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error-message {
            background: rgba(220, 53, 69, 0.15);
            color: #721c24;
            padding: 15px 20px;
            border-radius: var(--border-radius);
            border: 1px solid rgba(220, 53, 69, 0.3);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-info {
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 0.9rem;
        }

        /* زر الترجمة */
        .translate-btn {
            position: fixed;
            bottom: 20px;
            left: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            z-index: 1000;
            transition: var(--transition);
            border: none;
        }

        .translate-btn:hover {
            transform: scale(1.1);
            background: #3a5bd9;
        }

        .translate-btn i {
            font-size: 1.2rem;
        }

        /* نمط للغة الإنجليزية */
        body[dir="ltr"] {
            text-align: left;
        }

        body[dir="ltr"] .header {
            flex-direction: row;
        }

        body[dir="ltr"] .card-header {
            flex-direction: row;
        }

        body[dir="ltr"] table th, 
        body[dir="ltr"] table td {
            text-align: left;
        }

        body[dir="ltr"] .info-item {
            flex-direction: row;
        }

        body[dir="ltr"] .description-box {
            border-right: none;
            border-left: 4px solid var(--primary-color);
        }

        body[dir="ltr"] .modal-footer {
            justify-content: flex-end;
        }

        /* تحسينات التصميم المتجاوب */
        @media (max-width: 1200px) {
            .product-details-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }
            
            .header {
                padding: 15px;
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .header h1 {
                font-size: 1.5rem;
            }
            
            .card-body {
                padding: 20px;
            }
            
            table th, table td {
                padding: 10px 8px;
                font-size: 0.85rem;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .modal-content {
                margin: 10px;
                max-width: calc(100% - 20px);
            }
        }

        @media (max-width: 576px) {
            .modal-body {
                padding: 15px;
            }
            
            .modal-footer {
                padding: 15px;
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        /* تأثيرات إضافية */
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 15px;
            background: #f8f9fa;
            border: 2px dashed #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
        }

        .file-input-label:hover {
            border-color: var(--primary-color);
            background: #e9ecef;
        }

        .file-name {
            margin-top: 5px;
            font-size: 0.85rem;
            color: var(--secondary-color);
        }

        .stock-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="dashboard">
         <?php include 'sidebar.php'; ?>
        
        <!-- المحتوى الرئيسي -->
        <main class="main-content">
            <?php include 'admin_navbar.php'; ?>
            <div class="header fade-in">
                <h1><i class="fas fa-boxes"></i> <span data-translate="products_management">إدارة المنتجات</span></h1>
                <div class="table-">
                    <span data-translate="total_products">إجمالي المنتجات:</span> <strong><?php echo count($products); ?></strong>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="success-message fade-in">
                    <i class="fas fa-check-circle"></i> <span data-translate="operation_success">تمت العملية بنجاح</span>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="error-message fade-in">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- قائمة المنتجات -->
            <div class="card fade-in">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> <span data-translate="products_list">قائمة المنتجات</span></h3>
                    <button class="btn btn-success" id="addProductBtn">
                        <i class="fas fa-plus"></i> <span data-translate="add_new_product">إضافة منتج جديد</span>
                    </button>
                </div>
                <div class="card-body">
                    <?php if (empty($products)): ?>
                        <div style="text-align: center; padding: 40px; color: var(--secondary-color);">
                            <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 15px;"></i>
                            <h3 data-translate="no_products">لا توجد منتجات</h3>
                            <p data-translate="no_products_desc">لم يتم إضافة أي منتجات بعد.</p>
                            <button class="btn btn-success" id="addFirstProductBtn">
                                <i class="fas fa-plus"></i> <span data-translate="add_first_product">إضافة أول منتج</span>
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th data-translate="image">الصورة</th>
                                        <th data-translate="product_name">اسم المنتج</th>
                                        <th data-translate="price">السعر</th>
                                        <th data-translate="category">الفئة</th>
                                        <th data-translate="stock">المخزن</th>
                                        <th data-translate="created_date">تاريخ الإضافة</th>
                                        <th data-translate="actions">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td>
                                            <?php if ($product['image_path']): ?>
                                                <img src="../<?php echo $product['image_path']; ?>" 
                                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                     class="product-image"
                                                     data-product-image="<?php echo $product['image_path']; ?>">
                                            <?php else: ?>
                                                <div class="no-image" data-translate="no_image">
                                                    لا توجد صورة
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong data-product-name="<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></strong>
                                            <?php if ($product['description']): ?>
                                                <br><small style="color: #666;" data-product-description="<?php echo $product['id']; ?>"><?php echo substr($product['description'], 0, 50); ?>...</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span style="font-weight: bold; color: var(--primary-color);" data-product-price="<?php echo $product['id']; ?>">
                                                <?php echo number_format($product['price'], 2); ?> <span data-translate="currency">ر.س</span>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge" style="background: #e9ecef; padding: 4px 8px; border-radius: 12px; font-size: 0.8rem;" data-product-category="<?php echo $product['id']; ?>">
                                                <?php echo htmlspecialchars($product['category']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $stock_class = '';
                                            if ($product['stock_quantity'] > 10) {
                                                $stock_class = 'in-stock';
                                                $stock_style = 'background: rgba(40, 167, 69, 0.2); color: #155724;';
                                            } else if ($product['stock_quantity'] > 0) {
                                                $stock_class = 'low-stock';
                                                $stock_style = 'background: rgba(255, 193, 7, 0.2); color: #856404;';
                                            } else {
                                                $stock_class = 'out-of-stock';
                                                $stock_style = 'background: rgba(220, 53, 69, 0.2); color: #721c24;';
                                            }
                                            ?>
                                            <span class="stock-badge <?php echo $stock_class; ?>" style="<?php echo $stock_style; ?>" data-product-stock="<?php echo $product['id']; ?>">
                                                <?php echo $product['stock_quantity']; ?>
                                            </span>
                                        </td>
                                        <td data-product-date="<?php echo $product['id']; ?>">
                                            <?php echo date('Y-m-d', strtotime($product['created_at'])); ?>
                                        </td>
                                        <td>
                                            <div class="actions">
                                                <button class="btn btn-info view-product-btn" 
                                                        data-product-id="<?php echo $product['id']; ?>"
                                                        data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                                                        data-product-description="<?php echo htmlspecialchars($product['description']); ?>"
                                                        data-product-price="<?php echo $product['price']; ?>"
                                                        data-product-category="<?php echo htmlspecialchars($product['category']); ?>"
                                                        data-product-stock="<?php echo $product['stock_quantity']; ?>"
                                                        data-product-image="<?php echo $product['image_path']; ?>"
                                                        data-product-date="<?php echo date('Y-m-d', strtotime($product['created_at'])); ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-primary edit-product-btn" 
                                                        data-product-id="<?php echo $product['id']; ?>"
                                                        data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                                                        data-product-description="<?php echo htmlspecialchars($product['description']); ?>"
                                                        data-product-price="<?php echo $product['price']; ?>"
                                                        data-product-category="<?php echo htmlspecialchars($product['category']); ?>"
                                                        data-product-stock="<?php echo $product['stock_quantity']; ?>"
                                                        data-product-image="<?php echo $product['image_path']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger delete-product-btn" 
                                                        data-product-id="<?php echo $product['id']; ?>"
                                                        data-product-name="<?php echo htmlspecialchars($product['name']); ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- نافذة إضافة منتج جديد -->
    <div class="modal" id="addProductModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-plus"></i> <span data-translate="add_new_product">إضافة منتج جديد</span></h3>
                <button class="close-btn">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="addProductForm">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name" data-translate="product_name">اسم المنتج</label> *
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="price" data-translate="price">السعر</label> (<span data-translate="currency">ر.س</span>) *
                            <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="category" data-translate="category">الفئة</label> *
                            <input type="text" class="form-control" id="category" name="category" required>
                        </div>
                        <div class="form-group">
                            <label for="stock_quantity" data-translate="stock_quantity">الكمية في المخزن</label> *
                            <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" data-translate="description">وصف المنتج</label> *
                        <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="image" data-translate="product_image">صورة المنتج</label>
                        <div class="file-input-wrapper">
                            <div class="file-input-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span style="margin-right: 8px;" data-translate="choose_image">اختر صورة للمنتج</span>
                            </div>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                        <div class="file-name" id="fileName"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close-btn" data-translate="cancel">إلغاء</button>
                    <button type="submit" class="btn btn-success" name="add_product">
                        <i class="fas fa-save"></i> <span data-translate="add_product">إضافة المنتج</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- نافذة عرض بيانات المنتج -->
    <div class="modal" id="viewProductModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-eye"></i> <span data-translate="product_details">بيانات المنتج</span></h3>
                <button class="close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <div id="productDetailsContent">
                    <!-- سيتم تحميل المحتوى هنا عبر JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-btn" data-translate="close">إغلاق</button>
                <button type="button" class="btn btn-primary" id="editFromViewBtn">
                    <i class="fas fa-edit"></i> <span data-translate="edit_product">تعديل المنتج</span>
                </button>
            </div>
        </div>
    </div>

    <!-- نافذة تعديل المنتج -->
    <div class="modal" id="editProductModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> <span data-translate="edit_product">تعديل المنتج</span></h3>
                <button class="close-btn">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="editProductForm">
                <input type="hidden" id="edit_product_id" name="product_id">
                <input type="hidden" id="current_image" name="current_image">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_name" data-translate="product_name">اسم المنتج</label> *
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_price" data-translate="price">السعر</label> (<span data-translate="currency">ر.س</span>) *
                            <input type="number" step="0.01" class="form-control" id="edit_price" name="price" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_category" data-translate="category">الفئة</label> *
                            <input type="text" class="form-control" id="edit_category" name="category" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_stock_quantity" data-translate="stock_quantity">الكمية في المخزن</label> *
                            <input type="number" class="form-control" id="edit_stock_quantity" name="stock_quantity" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_description" data-translate="description">وصف المنتج</label> *
                        <textarea class="form-control" id="edit_description" name="description" rows="4" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_image" data-translate="product_image">صورة المنتج</label>
                        <div class="file-input-wrapper">
                            <div class="file-input-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span style="margin-right: 8px;" data-translate="change_image">تغيير صورة المنتج</span>
                            </div>
                            <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
                        </div>
                        <div class="file-name" id="editFileName"></div>
                        <div id="currentImagePreview" style="margin-top: 10px;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close-btn" data-translate="cancel">إلغاء</button>
                    <button type="submit" class="btn btn-primary" name="update_product">
                        <i class="fas fa-save"></i> <span data-translate="save_changes">حفظ التغييرات</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- نافذة تأكيد الحذف -->
    <div class="modal" id="deleteProductModal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3><i class="fas fa-trash"></i> <span data-translate="confirm_delete">تأكيد الحذف</span></h3>
                <button class="close-btn">&times;</button>
            </div>
            <form method="POST" id="deleteProductForm">
                <input type="hidden" id="delete_product_id" name="product_id">
                <div class="modal-body">
                    <p data-translate="confirm_delete_message">هل أنت متأكد من حذف هذا المنتج؟</p>
                    <p style="color: var(--danger-color); font-weight: bold;" data-translate="delete_warning">هذا الإجراء لا يمكن التراجع عنه!</p>
                    <p id="deleteProductName" style="font-weight: bold;"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close-btn" data-translate="cancel">إلغاء</button>
                    <button type="submit" class="btn btn-danger" name="delete_product">
                        <i class="fas fa-trash"></i> <span data-translate="yes_delete">نعم، احذف المنتج</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- زر الترجمة العائم -->
    <button class="translate-btn" id="translateBtn">
        <i class="fas fa-language"></i>
    </button>

    <script>
        // نصوص الترجمة
        const translations = {
            ar: {
                // العناوين الرئيسية
                "products_management": "إدارة المنتجات",
                "total_products": "إجمالي المنتجات",
                "products_list": "قائمة المنتجات",
                "add_new_product": "إضافة منتج جديد",
                "product_details": "بيانات المنتج",
                "edit_product": "تعديل المنتج",
                "confirm_delete": "تأكيد الحذف",
                
                // الرسائل
                "operation_success": "تمت العملية بنجاح",
                "no_products": "لا توجد منتجات",
                "no_products_desc": "لم يتم إضافة أي منتجات بعد.",
                "add_first_product": "إضافة أول منتج",
                "confirm_delete_message": "هل أنت متأكد من حذف هذا المنتج؟",
                "delete_warning": "هذا الإجراء لا يمكن التراجع عنه!",
                
                // أعمدة الجدول
                "image": "الصورة",
                "product_name": "اسم المنتج",
                "price": "السعر",
                "category": "الفئة",
                "stock": "المخزن",
                "created_date": "تاريخ الإضافة",
                "actions": "الإجراءات",
                "no_image": "لا توجد صورة",
                "currency": "ر.س",
                
                // النماذج
                "description": "وصف المنتج",
                "stock_quantity": "الكمية في المخزن",
                "product_image": "صورة المنتج",
                "choose_image": "اختر صورة للمنتج",
                "change_image": "تغيير صورة المنتج",
                
                // الأزرار
                "cancel": "إلغاء",
                "close": "إغلاق",
                "add_product": "إضافة المنتج",
                "save_changes": "حفظ التغييرات",
                "yes_delete": "نعم، احذف المنتج",
                
                // تفاصيل المنتج
                "current_image": "الصورة الحالية",
                "no_current_image": "لا توجد صورة حالية"
            },
            en: {
                // العناوين الرئيسية
                "products_management": "Products Management",
                "total_products": "Total Products",
                "products_list": "Products List",
                "add_new_product": "Add New Product",
                "product_details": "Product Details",
                "edit_product": "Edit Product",
                "confirm_delete": "Confirm Delete",
                
                // الرسائل
                "operation_success": "Operation completed successfully",
                "no_products": "No Products",
                "no_products_desc": "No products have been added yet.",
                "add_first_product": "Add First Product",
                "confirm_delete_message": "Are you sure you want to delete this product?",
                "delete_warning": "This action cannot be undone!",
                
                // أعمدة الجدول
                "image": "Image",
                "product_name": "Product Name",
                "price": "Price",
                "category": "Category",
                "stock": "Stock",
                "created_date": "Created Date",
                "actions": "Actions",
                "no_image": "No Image",
                "currency": "SAR",
                
                // النماذج
                "description": "Description",
                "stock_quantity": "Stock Quantity",
                "product_image": "Product Image",
                "choose_image": "Choose product image",
                "change_image": "Change product image",
                
                // الأزرار
                "cancel": "Cancel",
                "close": "Close",
                "add_product": "Add Product",
                "save_changes": "Save Changes",
                "yes_delete": "Yes, Delete Product",
                
                // تفاصيل المنتج
                "current_image": "Current Image",
                "no_current_image": "No current image"
            }
        };

        // حالة اللغة الحالية
        let currentLang = localStorage.getItem('language') || 'ar';

        // دالة لتطبيق الترجمة
        function applyLanguage(lang) {
            // تحديث النصوص في الصفحة
            document.querySelectorAll('[data-translate]').forEach(element => {
                const key = element.getAttribute('data-translate');
                if (translations[lang][key]) {
                    element.textContent = translations[lang][key];
                }
            });

            // تحديث اتجاه الصفحة
            if (lang === 'ar') {
                document.documentElement.dir = 'rtl';
                document.documentElement.lang = 'ar';
                document.title = 'إدارة المنتجات';
            } else {
                document.documentElement.dir = 'ltr';
                document.documentElement.lang = 'en';
                document.title = 'Products Management';
            }

            // حفظ اللغة في localStorage
            localStorage.setItem('language', lang);
            currentLang = lang;
        }

        // حدث النقر على زر الترجمة
        document.getElementById('translateBtn').addEventListener('click', function() {
            const newLang = currentLang === 'ar' ? 'en' : 'ar';
            applyLanguage(newLang);
        });

        // التحكم في النوافذ المنبثقة
        document.addEventListener('DOMContentLoaded', function() {
            // تطبيق اللغة عند تحميل الصفحة
            applyLanguage(currentLang);

            const addProductBtn = document.getElementById('addProductBtn');
            const addFirstProductBtn = document.getElementById('addFirstProductBtn');
            const addProductModal = document.getElementById('addProductModal');
            const viewProductModal = document.getElementById('viewProductModal');
            const editProductModal = document.getElementById('editProductModal');
            const deleteProductModal = document.getElementById('deleteProductModal');
            const closeBtns = document.querySelectorAll('.close-btn');
            const viewProductBtns = document.querySelectorAll('.view-product-btn');
            const editProductBtns = document.querySelectorAll('.edit-product-btn');
            const deleteProductBtns = document.querySelectorAll('.delete-product-btn');
            const editFromViewBtn = document.getElementById('editFromViewBtn');
            const fileInput = document.getElementById('image');
            const editFileInput = document.getElementById('edit_image');
            const fileName = document.getElementById('fileName');
            const editFileName = document.getElementById('editFileName');

            // فتح نافذة إضافة منتج
            if (addProductBtn) {
                addProductBtn.addEventListener('click', function() {
                    addProductModal.style.display = 'flex';
                });
            }

            // فتح نافذة إضافة أول منتج
            if (addFirstProductBtn) {
                addFirstProductBtn.addEventListener('click', function() {
                    addProductModal.style.display = 'flex';
                });
            }

            // فتح نافذة عرض المنتج
            viewProductBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const productId = this.getAttribute('data-product-id');
                    loadProductDetails(this);
                    viewProductModal.style.display = 'flex';
                });
            });

            // فتح نافذة تعديل المنتج
            editProductBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const productId = this.getAttribute('data-product-id');
                    loadProductForEdit(this);
                    editProductModal.style.display = 'flex';
                });
            });

            // فتح نافذة حذف المنتج
            deleteProductBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const productId = this.getAttribute('data-product-id');
                    const productName = this.getAttribute('data-product-name');
                    document.getElementById('delete_product_id').value = productId;
                    document.getElementById('deleteProductName').textContent = productName;
                    deleteProductModal.style.display = 'flex';
                });
            });

            // الانتقال من العرض إلى التعديل
            editFromViewBtn.addEventListener('click', function() {
                const productId = document.getElementById('edit_product_id').value;
                if (productId) {
                    viewProductModal.style.display = 'none';
                    editProductModal.style.display = 'flex';
                }
            });

            // إغلاق النوافذ المنبثقة
            closeBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    addProductModal.style.display = 'none';
                    viewProductModal.style.display = 'none';
                    editProductModal.style.display = 'none';
                    deleteProductModal.style.display = 'none';
                });
            });

            // إغلاق النافذة عند النقر خارجها
            window.addEventListener('click', function(event) {
                if (event.target === addProductModal) addProductModal.style.display = 'none';
                if (event.target === viewProductModal) viewProductModal.style.display = 'none';
                if (event.target === editProductModal) editProductModal.style.display = 'none';
                if (event.target === deleteProductModal) deleteProductModal.style.display = 'none';
            });

            // عرض اسم الملف المختار
            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    fileName.textContent = this.files[0] ? this.files[0].name : '';
                });
            }

            if (editFileInput) {
                editFileInput.addEventListener('change', function() {
                    editFileName.textContent = this.files[0] ? this.files[0].name : '';
                });
            }

            // تحميل بيانات المنتج للعرض
            function loadProductDetails(button) {
                const productId = button.getAttribute('data-product-id');
                const productName = button.getAttribute('data-product-name');
                const productDescription = button.getAttribute('data-product-description');
                const productPrice = button.getAttribute('data-product-price');
                const productCategory = button.getAttribute('data-product-category');
                const productStock = button.getAttribute('data-product-stock');
                const productImage = button.getAttribute('data-product-image');
                const productDate = button.getAttribute('data-product-date');

                const stockClass = productStock > 10 ? 'in-stock' : 
                                 productStock > 0 ? 'low-stock' : 'out-of-stock';
                const stockStyle = productStock > 10 ? 'background: rgba(40, 167, 69, 0.2); color: #155724;' :
                                 productStock > 0 ? 'background: rgba(255, 193, 7, 0.2); color: #856404;' :
                                 'background: rgba(220, 53, 69, 0.2); color: #721c24;';

                const content = `
                    <div class="product-details-grid">
                        <div>
                            ${productImage ? 
                                `<img src="../${productImage}" alt="${productName}" class="product-image-large">` :
                                `<div class="no-image" style="width: 100%; height: 250px; font-size: 1rem;">${translations[currentLang]['no_image']}</div>`
                            }
                        </div>
                        <div class="product-info">
                            <div class="info-item">
                                <span class="info-label">${translations[currentLang]['product_name']}:</span>
                                <span class="info-value">${productName}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">${translations[currentLang]['price']}:</span>
                                <span class="info-value" style="color: var(--primary-color); font-weight: bold;">${parseFloat(productPrice).toFixed(2)} ${translations[currentLang]['currency']}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">${translations[currentLang]['category']}:</span>
                                <span class="info-value">${productCategory}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">${translations[currentLang]['stock_quantity']}:</span>
                                <span class="stock-badge ${stockClass}" style="${stockStyle}">${productStock}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">${translations[currentLang]['created_date']}:</span>
                                <span class="info-value">${productDate}</span>
                            </div>
                        </div>
                    </div>
                    <div class="description-box">
                        <h4 style="margin-top: 0;">${translations[currentLang]['description']}:</h4>
                        <p>${productDescription || translations[currentLang]['no_description']}</p>
                    </div>
                `;
                document.getElementById('productDetailsContent').innerHTML = content;
                
                // تعيين معرف المنتج لزر التعديل
                document.getElementById('edit_product_id').value = productId;
            }

            // تحميل بيانات المنتج للتعديل
            function loadProductForEdit(button) {
                const productId = button.getAttribute('data-product-id');
                const productName = button.getAttribute('data-product-name');
                const productDescription = button.getAttribute('data-product-description');
                const productPrice = button.getAttribute('data-product-price');
                const productCategory = button.getAttribute('data-product-category');
                const productStock = button.getAttribute('data-product-stock');
                const productImage = button.getAttribute('data-product-image');

                document.getElementById('edit_product_id').value = productId;
                document.getElementById('edit_name').value = productName;
                document.getElementById('edit_price').value = productPrice;
                document.getElementById('edit_category').value = productCategory;
                document.getElementById('edit_stock_quantity').value = productStock;
                document.getElementById('edit_description').value = productDescription || '';
                document.getElementById('current_image').value = productImage || '';
                
                // عرض الصورة الحالية
                const preview = document.getElementById('currentImagePreview');
                if (productImage) {
                    preview.innerHTML = `
                        <p>${translations[currentLang]['current_image']}:</p>
                        <img src="../${productImage}" alt="${productName}" style="max-width: 200px; max-height: 150px; border-radius: 5px;">
                    `;
                } else {
                    preview.innerHTML = `<p>${translations[currentLang]['no_current_image']}</p>`;
                }
            }
        });
    </script>
</body>
</html>