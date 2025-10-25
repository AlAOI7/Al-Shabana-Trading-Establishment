<?php
require_once '../config.php';

// 📁 إعدادات رفع الصور
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/');
define('UPLOAD_URL', '../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif']);

// التحقق من صلاحية المدير
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// إعدادات التقسيم
$products_per_page = 20;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $products_per_page;

// جلب إجمالي عدد المنتجات
try {
    $count_stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $total_products = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_products / $products_per_page);
} catch (PDOException $e) {
    $total_products = 0;
    $total_pages = 1;
}

// جلب المنتجات للصفحة الحالية
try {
    $stmt = $pdo->prepare("
        SELECT p.*, 
               pi.image_name as primary_image
        FROM products p 
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        ORDER BY p.featured DESC, p.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $products_per_page, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
    $error = "خطأ في جلب البيانات: " . $e->getMessage();
}

// معالجة العمليات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... (الكود السابق لمعالجة العمليات يبقى كما هو)
    // إضافة منتج جديد
    if (isset($_POST['add_product'])) {
        $S_NO = $_POST['S_NO'];
        $Item_Code = $_POST['Item_Code'];
        $Item_Name = $_POST['Item_Name'];
        $Packing = $_POST['Packing'];
        $Item_Group = $_POST['Item_Group'];
        $Brand = $_POST['Brand'];
        $featured = isset($_POST['featured']) ? 1 : 0;
        
        try {
            // التحقق من عدم تكرار رمز المنتج
            $check_stmt = $pdo->prepare("SELECT id FROM products WHERE Item_Code = ?");
            $check_stmt->execute([$Item_Code]);
            
            if ($check_stmt->fetch()) {
                $error = "رمز المنتج موجود مسبقاً";
            } else {
                // إضافة المنتج
                $stmt = $pdo->prepare("INSERT INTO products (S_NO, Item_Code, Item_Name, Packing, Item_Group, Brand, featured) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$S_NO, $Item_Code, $Item_Name, $Packing, $Item_Group, $Brand, $featured]);
                
                $product_id = $pdo->lastInsertId();
                
                // رفع الصور إذا وجدت
                if (!empty($_FILES['images']['name'][0])) {
                    uploadProductImages($pdo, $product_id);
                }
                
                $_SESSION['success'] = "تمت إضافة المنتج بنجاح!";
                header("Location: products.php?page=" . $current_page);
                exit();
            }
        } catch (PDOException $e) {
            $error = "خطأ في إضافة المنتج: " . $e->getMessage();
        }
    }
    
    // تحديث المنتج
    if (isset($_POST['update_product'])) {
        $product_id = $_POST['product_id'];
        $S_NO = $_POST['S_NO'];
        $Item_Code = $_POST['Item_Code'];
        $Item_Name = $_POST['Item_Name'];
        $Packing = $_POST['Packing'];
        $Item_Group = $_POST['Item_Group'];
        $Brand = $_POST['Brand'];
        $featured = isset($_POST['featured']) ? 1 : 0;
        
        try {
            // التحقق من عدم تكرار رمز المنتج (استثناء المنتج الحالي)
            $check_stmt = $pdo->prepare("SELECT id FROM products WHERE Item_Code = ? AND id != ?");
            $check_stmt->execute([$Item_Code, $product_id]);
            
            if ($check_stmt->fetch()) {
                $error = "رمز المنتج موجود مسبقاً";
            } else {
                // تحديث المنتج
                $stmt = $pdo->prepare("UPDATE products SET S_NO = ?, Item_Code = ?, Item_Name = ?, Packing = ?, Item_Group = ?, Brand = ?, featured = ? WHERE id = ?");
                $stmt->execute([$S_NO, $Item_Code, $Item_Name, $Packing, $Item_Group, $Brand, $featured, $product_id]);
                
                // رفع الصور جديدة إذا وجدت
                if (!empty($_FILES['images']['name'][0])) {
                    uploadProductImages($pdo, $product_id);
                }
                
                $_SESSION['success'] = "تم تحديث المنتج بنجاح!";
                header("Location: products.php?page=" . $current_page);
                exit();
            }
        } catch (PDOException $e) {
            $error = "خطأ في تحديث المنتج: " . $e->getMessage();
        }
    }
    
    // حذف المنتج
    if (isset($_POST['delete_product'])) {
        $product_id = $_POST['product_id'];
        
        try {
            // جلب الصور المرتبطة
            $stmt = $pdo->prepare("SELECT image_name FROM product_images WHERE product_id = ?");
            $stmt->execute([$product_id]);
            $images = $stmt->fetchAll();
            
            // حذف الملفات من السيرفر
            foreach ($images as $image) {
                $file_path = UPLOAD_DIR . $image['image_name'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            
            // الحذف من قاعدة البيانات
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            
            $_SESSION['success'] = "تم حذف المنتج بنجاح!";
            header("Location: products.php?page=" . $current_page);
            exit();
        } catch (PDOException $e) {
            $error = "خطأ في حذف المنتج: " . $e->getMessage();
        }
    }
    
    // تبديل حالة المميز
    if (isset($_POST['toggle_featured'])) {
        $product_id = $_POST['product_id'];
        
        try {
            $stmt = $pdo->prepare("UPDATE products SET featured = NOT featured WHERE id = ?");
            $stmt->execute([$product_id]);
            
            $_SESSION['success'] = "تم تغيير حالة المنتج المميز!";
            header("Location: products.php?page=" . $current_page);
            exit();
        } catch (PDOException $e) {
            $error = "خطأ في تغيير الحالة: " . $e->getMessage();
        }
    }
}

// دالة رفع الصور
function uploadProductImages($pdo, $product_id) {
    // ... (الكود السابق يبقى كما هو)
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    
    $is_first = true;
    
    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) {
            continue;
        }
        
        // التحقق من حجم الملف
        if ($_FILES['images']['size'][$key] > MAX_FILE_SIZE) {
            throw new Exception("حجم الملف كبير جداً");
        }
        
        // التحقق من نوع الملف
        $file_info = pathinfo($_FILES['images']['name'][$key]);
        $extension = strtolower($file_info['extension'] ?? '');

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_mime = mime_content_type($tmp_name);

        if (!in_array($extension, ALLOWED_IMAGE_TYPES) || !in_array($file_mime, $allowed_types)) {
            throw new Exception("نوع الملف غير مسموح به ($extension - $file_mime)");
        }

        // إنشاء اسم فريد للملف
        $file_name = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '', $file_info['filename']) . '.' . $extension;
        $file_path = UPLOAD_DIR . $file_name;
        
        // التحقق من أن الملف صورة حقيقية
        if (!getimagesize($tmp_name)) {
            throw new Exception("الملف ليس صورة صالحة");
        }
        
        if (move_uploaded_file($tmp_name, $file_path)) {
            $is_primary = $is_first ? 1 : 0;
            $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_name, is_primary) VALUES (?, ?, ?)");
            $stmt->execute([$product_id, $file_name, $is_primary]);
            $is_first = false;
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
        /* تنسيقات النماذج */
          
            /* تنسيقات عرض المنتج */
            .product-view-container {
                font-family: inherit;
            }

            .product-view-header {
                display: flex;
                gap: 20px;
                margin-bottom: 25px;
                align-items: flex-start;
            }

            .product-main-image {
                width: 150px;
                height: 150px;
                border-radius: 8px;
                overflow: hidden;
                flex-shrink: 0;
            }

            .product-main-image img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .no-image-large {
                width: 100%;
                height: 100%;
                background: #f8f9fa;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                color: #6c757d;
            }

            .product-basic-info {
                flex: 1;
            }

            .product-title {
                margin: 0 0 15px 0;
                color: var(--primary-color);
                font-size: 1.4rem;
            }

            .product-code-section {
                margin-bottom: 8px;
            }

            .product-code-section .label {
                font-weight: bold;
                color: #555;
            }

            .product-code-section .value {
                margin-right: 8px;
            }

            .product-code-section .value.code {
                background: #e9ecef;
                padding: 2px 8px;
                border-radius: 4px;
                font-family: monospace;
            }

            .featured-badge {
                background: var(--warning-color);
                color: white;
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 0.9rem;
                display: inline-flex;
                align-items: center;
                gap: 5px;
                margin-top: 10px;
            }

            .product-details-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 15px;
                margin-bottom: 25px;
            }

            .detail-item {
                display: flex;
                justify-content: space-between;
                padding: 10px 0;
                border-bottom: 1px solid #f0f0f0;
            }

            .detail-item .label {
                font-weight: bold;
                color: #555;
            }

            .detail-item .value {
                color: #333;
            }

            /* تنسيقات الإدخال */
           
            .file-input-wrapper {
                position: relative;
                border: 2px dashed #ddd;
                border-radius: 4px;
                padding: 20px;
                text-align: center;
                transition: border-color 0.3s;
            }

            .file-input-wrapper:hover {
                border-color: var(--primary-color);
            }

            .file-input-label {
                color: #666;
                cursor: pointer;
            }

            .file-input-wrapper input[type="file"] {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                opacity: 0;
                cursor: pointer;
            }

            .file-name {
                margin-top: 8px;
                font-size: 0.9rem;
                color: #666;
                display: none;
            }

            /* تنسيقات الأزرار */
            .btn {
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 1rem;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                transition: background-color 0.3s;
            }

            .btn-success {
                background: var(--success-color);
                color: white;
            }

            .btn-primary {
                background: var(--primary-color);
                color: white;
            }

            .btn-danger {
                background: var(--danger-color);
                color: white;
            }

            .btn-secondary {
                background: #6c757d;
                color: white;
            }

            .btn:hover {
                opacity: 0.9;
            }

            /* تنسيقات الشبكات */
            .product-images-grid,
            .current-images-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
                gap: 10px;
                margin-top: 10px;
            }

            .current-image-item {
                position: relative;
                width: 100px;
                height: 100px;
                border-radius: 4px;
                overflow: hidden;
            }

            .current-image-item img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .remove-image-btn {
                position: absolute;
                top: 5px;
                left: 5px;
                background: rgba(220, 53, 69, 0.8);
                color: white;
                border: none;
                border-radius: 50%;
                width: 25px;
                height: 25px;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            /* تحسينات للشاشات الصغيرة */
            @media (max-width: 768px) {
                .modal-content {
                    width: 95%;
                    margin: 20px;
                }
                
                .product-view-header {
                    flex-direction: column;
                    text-align: center;
                }
                
                .product-main-image {
                    align-self: center;
                }
                
                .product-details-grid {
                    grid-template-columns: 1fr;
                }
                
                .form-row {
                    flex-direction: column;
                    gap: 0;
                }
            }
    </style>
</head>
    <style>
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        
        .no-image {
            width: 50px;
            height: 50px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            color: #6c757d;
            font-size: 0.8rem;
            border: 1px dashed #ddd;
        }
        
        .actions {
            display: flex;
            gap: 5px;
        }
        
        .actions .btn {
            padding: 4px 8px;
            font-size: 0.8rem;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        /* تحسينات التصميم */
        .featured-star {
            color: #ffc107;
        }
        
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
        }
        
        .btn-warning:hover {
            background-color: #e0a800;
            border-color: #d39e00;
        }
    </style>
        <style>
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        
        .no-image {
            width: 50px;
            height: 50px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            color: #6c757d;
            font-size: 0.8rem;
            border: 1px dashed #ddd;
        }
        
        .actions {
            display: flex;
            gap: 5px;
            flex-wrap: nowrap;
        }
        
        .actions .btn {
            padding: 4px 8px;
            font-size: 0.8rem;
            white-space: nowrap;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .featured-star {
            color: #ffc107;
        }
        
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
        }
        
        .btn-warning:hover {
            background-color: #e0a800;
            border-color: #d39e00;
        }
        
        /* تنسيقات التقسيم */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .pagination-info {
            color: #6c757d;
            font-size: 0.9rem;
            white-space: nowrap;
        }
        
        .pagination {
            display: flex;
            gap: 5px;
            margin: 0;
            flex-wrap: wrap;
        }
        
        .page-item {
            list-style: none;
        }
        
        .page-link {
            display: block;
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            color: #007bff;
            text-decoration: none;
            transition: all 0.3s;
            white-space: nowrap;
        }
        
        .page-link:hover {
            background-color: #e9ecef;
            border-color: #dee2e6;
        }
        
        .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
        }
        
        .page-item.disabled .page-link {
            color: #6c757d;
            pointer-events: none;
            background-color: #fff;
            border-color: #dee2e6;
        }
        
        /* تنسيقات حاوية الجدول مع شريط التمرير */
        .table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            position: relative;
        }
        
        .table {
            width: 100%;
            min-width: 1000px;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        
        .table td {
            padding: 12px 8px;
            vertical-align: middle;
            border-bottom: 1px solid #dee2e6;
            white-space: nowrap;
        }
        
        .table th {
            padding: 15px 8px;
            vertical-align: middle;
        }
        
        /* تخصيص شريط التمرير */
        .table-wrapper::-webkit-scrollbar {
            height: 8px;
        }
        
        .table-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        .table-wrapper::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        .table-wrapper::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        /* مؤشر التمرير */
        .scroll-indicator {
            position: absolute;
            bottom: 5px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none;
        }
        
        .table-wrapper:hover .scroll-indicator {
            opacity: 1;
        }
        
        /* تنسيقات للشاشات الصغيرة */
        @media (max-width: 1200px) {
            .table {
                min-width: 1200px;
            }
        }
        
        @media (max-width: 768px) {
            .pagination-container {
                flex-direction: column;
                text-align: center;
            }
            
            .pagination-info {
                order: 2;
            }
            
            .pagination {
                order: 1;
                justify-content: center;
            }
            
            .table td, .table th {
                padding: 8px 6px;
                font-size: 0.85rem;
            }
            
            .actions {
                flex-direction: column;
                gap: 3px;
            }
            
            .actions .btn {
                padding: 3px 6px;
                font-size: 0.75rem;
            }
            
            .product-image, .no-image {
                width: 40px;
                height: 40px;
            }
        }
        
        @media (max-width: 480px) {
            .table td, .table th {
                padding: 6px 4px;
                font-size: 0.8rem;
            }
            
            .badge {
                font-size: 0.7rem;
                padding: 3px 6px;
            }
            
            .page-link {
                padding: 6px 8px;
                font-size: 0.8rem;
            }
        }
        
        /* تأثيرات عند التمرير */
        .table-wrapper {
            scroll-behavior: smooth;
        }
        
        /* تظليل الصفوف */
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0,0,0,.02);
        }
        
        .table-striped tbody tr:hover {
            background-color: rgba(0,0,0,.04);
        }
        
        /* تحسين عرض الأعمدة */
        .table th:nth-child(1), .table td:nth-child(1) { /* الصورة */
            width: 70px;
            min-width: 70px;
        }
        
        .table th:nth-child(2), .table td:nth-child(2) { /* الرقم التسلسلي */
            width: 100px;
            min-width: 100px;
        }
        
        .table th:nth-child(3), .table td:nth-child(3) { /* رمز المنتج */
            width: 120px;
            min-width: 120px;
        }
        
        .table th:nth-child(4), .table td:nth-child(4) { /* اسم المنتج */
            width: 200px;
            min-width: 200px;
            white-space: normal !important;
            max-width: 200px;
        }
        
        .table th:nth-child(5), .table td:nth-child(5) { /* التغليف */
            width: 120px;
            min-width: 120px;
        }
        
        .table th:nth-child(6), .table td:nth-child(6) { /* المجموعة */
            width: 120px;
            min-width: 120px;
        }
        
        .table th:nth-child(7), .table td:nth-child(7) { /* العلامة التجارية */
            width: 120px;
            min-width: 120px;
        }
        
        .table th:nth-child(8), .table td:nth-child(8) { /* مميز */
            width: 80px;
            min-width: 80px;
            text-align: center;
        }
        
        .table th:nth-child(9), .table td:nth-child(9) { /* تاريخ الإضافة */
            width: 120px;
            min-width: 120px;
        }
        
        .table th:nth-child(10), .table td:nth-child(10) { /* الإجراءات */
            width: 150px;
            min-width: 150px;
        }
    </style>
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
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
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
                                  <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-boxes"></i>
                                                إدارة المنتجات
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-container">
                                                <div class="table-wrapper">
                                                    
                                                    <table class="table table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th data-translate="image">الصورة</th>
                                                                <th data-translate="serial_number">الرقم التسلسلي</th>
                                                                <th data-translate="item_code">رمز المنتج</th>
                                                                <th data-translate="item_name">اسم المنتج</th>
                                                                <th data-translate="packing">التغليف</th>
                                                                <th data-translate="item_group">المجموعة</th>
                                                                <th data-translate="brand">العلامة التجارية</th>
                                                                <th data-translate="featured">مميز</th>
                                                                <th data-translate="created_date">تاريخ الإضافة</th>
                                                                <th data-translate="actions">الإجراءات</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($products as $product): ?>
                                                            <tr>
                                                                <td>
                                                                    <?php if (!empty($product['primary_image'])): ?>
                                                                        <?php
                                                                        $image_path = UPLOAD_DIR . $product['primary_image'];
                                                                        $image_url = UPLOAD_URL . $product['primary_image'];
                                                                        ?>
                                                                        <?php if (file_exists($image_path)): ?>
                                                                            <img src="<?php echo $image_url; ?>" 
                                                                                alt="<?php echo htmlspecialchars($product['Item_Name']); ?>" 
                                                                                class="product-image"
                                                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                                            <div class="no-image" style="display: none;">
                                                                                <i class="fas fa-image"></i>
                                                                            </div>
                                                                        <?php else: ?>
                                                                            <div class="no-image">
                                                                                <i class="fas fa-image"></i>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    <?php else: ?>
                                                                        <div class="no-image">
                                                                            <i class="fas fa-image"></i>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <span data-product-sno="<?php echo $product['id']; ?>">
                                                                        <?php echo htmlspecialchars($product['S_NO']); ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <span data-product-code="<?php echo $product['id']; ?>">
                                                                        <?php echo htmlspecialchars($product['Item_Code']); ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <strong data-product-name="<?php echo $product['id']; ?>">
                                                                        <?php echo htmlspecialchars($product['Item_Name']); ?>
                                                                    </strong>
                                                                    <?php if ($product['featured']): ?>
                                                                        <br><span class="badge badge-warning mt-1" style="font-size: 0.7rem;">
                                                                            <i class="fas fa-star featured-star"></i> مميز
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <span data-product-packing="<?php echo $product['id']; ?>">
                                                                        <?php echo htmlspecialchars($product['Packing']); ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <span class="badge" style="background: #e9ecef; color: #495057;" data-product-group="<?php echo $product['id']; ?>">
                                                                        <?php echo htmlspecialchars($product['Item_Group']); ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <span data-product-brand="<?php echo $product['id']; ?>">
                                                                        <?php echo htmlspecialchars($product['Brand']); ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <form method="POST" style="display: inline;">
                                                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                                        <button type="submit" name="toggle_featured" class="btn btn-sm <?php echo $product['featured'] ? 'btn-warning' : 'btn-outline-warning'; ?>" title="<?php echo $product['featured'] ? 'إلغاء التميز' : 'تعيين كمميز'; ?>">
                                                                            <i class="fas fa-star <?php echo $product['featured'] ? 'featured-star' : ''; ?>"></i>
                                                                        </button>
                                                                    </form>
                                                                </td>
                                                                <td data-product-date="<?php echo $product['id']; ?>">
                                                                    <?php echo date('Y-m-d', strtotime($product['created_at'])); ?>
                                                                </td>
                                                                <td>
                                                                    <div class="actions">
                                                                        <button class="btn btn-info btn-sm view-product-btn" 
                                                                                data-product-id="<?php echo $product['id']; ?>"
                                                                                data-product-sno="<?php echo htmlspecialchars($product['S_NO']); ?>"
                                                                                data-product-code="<?php echo htmlspecialchars($product['Item_Code']); ?>"
                                                                                data-product-name="<?php echo htmlspecialchars($product['Item_Name']); ?>"
                                                                                data-product-packing="<?php echo htmlspecialchars($product['Packing']); ?>"
                                                                                data-product-group="<?php echo htmlspecialchars($product['Item_Group']); ?>"
                                                                                data-product-brand="<?php echo htmlspecialchars($product['Brand']); ?>"
                                                                                data-product-featured="<?php echo $product['featured']; ?>"
                                                                                data-product-image="<?php echo $product['primary_image']; ?>"
                                                                                data-product-date="<?php echo date('Y-m-d', strtotime($product['created_at'])); ?>"
                                                                                title="عرض التفاصيل">
                                                                            <i class="fas fa-eye"></i>
                                                                        </button>
                                                                        <button class="btn btn-primary btn-sm edit-product-btn" 
                                                                                data-product-id="<?php echo $product['id']; ?>"
                                                                                data-product-sno="<?php echo htmlspecialchars($product['S_NO']); ?>"
                                                                                data-product-code="<?php echo htmlspecialchars($product['Item_Code']); ?>"
                                                                                data-product-name="<?php echo htmlspecialchars($product['Item_Name']); ?>"
                                                                                data-product-packing="<?php echo htmlspecialchars($product['Packing']); ?>"
                                                                                data-product-group="<?php echo htmlspecialchars($product['Item_Group']); ?>"
                                                                                data-product-brand="<?php echo htmlspecialchars($product['Brand']); ?>"
                                                                                data-product-featured="<?php echo $product['featured']; ?>"
                                                                                title="تعديل المنتج">
                                                                            <i class="fas fa-edit"></i>
                                                                        </button>
                                                                        <button class="btn btn-danger btn-sm delete-product-btn" 
                                                                                data-product-id="<?php echo $product['id']; ?>"
                                                                                data-product-name="<?php echo htmlspecialchars($product['Item_Name']); ?>"
                                                                                title="حذف المنتج">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                            
                                                            <?php if (empty($products)): ?>
                                                            <tr>
                                                                <td colspan="10" class="text-center text-muted py-4">
                                                                    <i class="fas fa-box-open fa-2x mb-3"></i>
                                                                    <br>
                                                                    لا توجد منتجات
                                                                </td>
                                                            </tr>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <!-- نظام التقسيم -->
                                            <?php if ($total_pages > 1): ?>
                                            <div class="pagination-container">
                                                <div class="pagination-info">
                                                    عرض <?php echo count($products); ?> من أصل <?php echo $total_products; ?> منتج
                                                    - الصفحة <?php echo $current_page; ?> من <?php echo $total_pages; ?>
                                                </div>
                                                
                                                <ul class="pagination">
                                                    <!-- زر الصفحة الأولى -->
                                                    <li class="page-item <?php echo $current_page == 1 ? 'disabled' : ''; ?>">
                                                        <a class="page-link" href="products.php?page=1" title="الصفحة الأولى">
                                                            <i class="fas fa-angle-double-right"></i>
                                                        </a>
                                                    </li>
                                                    
                                                    <!-- زر الصفحة السابقة -->
                                                    <li class="page-item <?php echo $current_page == 1 ? 'disabled' : ''; ?>">
                                                        <a class="page-link" href="products.php?page=<?php echo $current_page - 1; ?>" title="الصفحة السابقة">
                                                            <i class="fas fa-angle-right"></i>
                                                        </a>
                                                    </li>
                                                    
                                                    <!-- أرقام الصفحات -->
                                                    <?php
                                                    // عرض 5 صفحات حول الصفحة الحالية
                                                    $start_page = max(1, $current_page - 2);
                                                    $end_page = min($total_pages, $current_page + 2);
                                                    
                                                    for ($page = $start_page; $page <= $end_page; $page++):
                                                    ?>
                                                        <li class="page-item <?php echo $page == $current_page ? 'active' : ''; ?>">
                                                            <a class="page-link" href="products.php?page=<?php echo $page; ?>">
                                                                <?php echo $page; ?>
                                                            </a>
                                                        </li>
                                                    <?php endfor; ?>
                                                    
                                                    <!-- زر الصفحة التالية -->
                                                    <li class="page-item <?php echo $current_page == $total_pages ? 'disabled' : ''; ?>">
                                                        <a class="page-link" href="products.php?page=<?php echo $current_page + 1; ?>" title="الصفحة التالية">
                                                            <i class="fas fa-angle-left"></i>
                                                        </a>
                                                    </li>
                                                    
                                                    <!-- زر الصفحة الأخيرة -->
                                                    <li class="page-item <?php echo $current_page == $total_pages ? 'disabled' : ''; ?>">
                                                        <a class="page-link" href="products.php?page=<?php echo $total_pages; ?>" title="الصفحة الأخيرة">
                                                            <i class="fas fa-angle-double-left"></i>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <?php endif; ?>
                                        </div>
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
                        <label for="S_NO">الرقم التسلسلي</label> *
                        <input type="number" class="form-control" id="S_NO" name="S_NO" required>
                    </div>
                    <div class="form-group">
                        <label for="Item_Code">رمز المنتج</label> *
                        <input type="text" class="form-control" id="Item_Code" name="Item_Code" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="Item_Name">اسم المنتج</label> *
                    <textarea class="form-control" id="Item_Name" name="Item_Name" rows="2" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="Packing">التغليف</label>
                        <input type="text" class="form-control" id="Packing" name="Packing">
                    </div>
                    <div class="form-group">
                        <label for="Item_Group">المجموعة</label>
                        <input type="text" class="form-control" id="Item_Group" name="Item_Group">
                    </div>
                    <div class="form-group">
                        <label for="Brand">العلامة التجارية</label>
                        <input type="text" class="form-control" id="Brand" name="Brand">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="images">صور المنتج</label>
                    <div class="file-input-wrapper">
                        <div class="file-input-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span style="margin-right: 8px;">اختر صور للمنتج (يمكن اختيار أكثر من صورة)</span>
                        </div>
                        <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                    </div>
                    <div class="file-name" id="addFileNames"></div>
                    <small class="form-text text-muted">الصورة الأولى ستكون الصورة الرئيسية للمنتج</small>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="featured" name="featured" value="1">
                    <label class="form-check-label" for="featured">منتج مميز</label>
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
                        <label for="S_NO">الرقم التسلسلي</label> *
                        <input type="number" class="form-control" id="S_NO" name="S_NO" required>
                    </div>
                    <div class="form-group">
                        <label for="Item_Code">رمز المنتج</label> *
                        <input type="text" class="form-control" id="Item_Code" name="Item_Code" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="Item_Name">اسم المنتج</label> *
                    <textarea class="form-control" id="Item_Name" name="Item_Name" rows="2" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="Packing">التغليف</label>
                        <input type="text" class="form-control" id="Packing" name="Packing">
                    </div>
                    <div class="form-group">
                        <label for="Item_Group">المجموعة</label>
                        <input type="text" class="form-control" id="Item_Group" name="Item_Group">
                    </div>
                    <div class="form-group">
                        <label for="Brand">العلامة التجارية</label>
                        <input type="text" class="form-control" id="Brand" name="Brand">
                    </div>
                </div>
                
                <!-- حقل رفع الصور المحسن -->
                <div class="form-group">
                    <label for="images" class="form-label">صور المنتج</label>
                    <div class="image-upload-container">
                        <div class="upload-area" id="uploadArea">
                            <div class="upload-icon">
                                <i class="fas fa-images"></i>
                            </div>
                            <div class="upload-text">
                                <h4>اسحب وأفلت الصور هنا</h4>
                                <p>أو انقر لاختيار الصور</p>
                            </div>
                            <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*" hidden>
                            <button type="button" class="btn btn-outline-primary" id="browseBtn">
                                <i class="fas fa-folder-open"></i> تصفح الصور
                            </button>
                        </div>
                        <div class="upload-requirements">
                            <small>
                                <i class="fas fa-info-circle"></i>
                                الصيغ المسموحة: JPG, PNG, GIF, WEBP | الحد الأقصى: 5MB للصورة
                            </small>
                        </div>
                        <div class="selected-images" id="selectedImages">
                            <!-- سيتم عرض الصور المختارة هنا -->
                        </div>
                    </div>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="featured" name="featured" value="1">
                    <label class="form-check-label" for="featured">منتج مميز</label>
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

<style>
        .image-upload-container {
            border: 2px dashed #ddd;
            border-radius: 12px;
            padding: 20px;
            background: #fafafa;
            transition: all 0.3s ease;
        }

        .image-upload-container.dragover {
            border-color: var(--primary-color);
            background: rgba(0, 123, 255, 0.05);
        }

        .upload-area {
            text-align: center;
            padding: 30px 20px;
            cursor: pointer;
        }

        .upload-icon {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 15px;
        }

        .upload-text h4 {
            margin: 0 0 8px 0;
            color: #495057;
            font-weight: 600;
        }

        .upload-text p {
            margin: 0 0 20px 0;
            color: #6c757d;
        }

        #browseBtn {
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 500;
        }

        .upload-requirements {
            text-align: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .upload-requirements small {
            color: #6c757d;
        }

        .selected-images {
            margin-top: 20px;
            display: none;
        }

        .selected-images.active {
            display: block;
        }

        .images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .image-preview {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }

        .image-preview:hover {
            transform: translateY(-2px);
        }

        .image-preview img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            display: block;
        }

        .image-actions {
            position: absolute;
            top: 5px;
            left: 5px;
            display: flex;
            gap: 5px;
        }

        .btn-sm {
            padding: 4px 8px;
            font-size: 0.75rem;
            border-radius: 4px;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            border: none;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .image-info {
            padding: 8px;
            background: white;
            border-top: 1px solid #eee;
        }

        .image-name {
            font-size: 0.75rem;
            color: #495057;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .image-size {
            font-size: 0.7rem;
            color: #6c757d;
        }

        .no-images {
            text-align: center;
            padding: 20px;
            color: #6c757d;
        }

        .no-images i {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
        }

        /* تحسينات للشاشات الصغيرة */
        @media (max-width: 768px) {
            .images-grid {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
                gap: 10px;
            }
            
            .image-preview img {
                height: 100px;
            }
            
            .upload-area {
                padding: 20px 15px;
            }
            
            .upload-icon {
                font-size: 2.5rem;
            }
        }
</style>
    <script>
        // معالجة أخطاء تحميل الصور
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('.product-image');
            images.forEach(img => {
                img.addEventListener('error', function() {
                    this.style.display = 'none';
                    const noImageDiv = this.nextElementSibling;
                    if (noImageDiv && noImageDiv.classList.contains('no-image')) {
                        noImageDiv.style.display = 'flex';
                    }
                });
            });
        });

    
    </script>
<script>
        document.addEventListener('DOMContentLoaded', function() {
            const uploadArea = document.getElementById('uploadArea');
            const fileInput = document.getElementById('images');
            const browseBtn = document.getElementById('browseBtn');
            const selectedImages = document.getElementById('selectedImages');
            const uploadContainer = document.querySelector('.image-upload-container');

            // فتح نافذة اختيار الملفات عند النقر على الزر
            browseBtn.addEventListener('click', function() {
                fileInput.click();
            });

            // فتح نافذة اختيار الملفات عند النقر على منطقة الرفع
            uploadArea.addEventListener('click', function(e) {
                if (e.target !== browseBtn) {
                    fileInput.click();
                }
            });

            // دعم سحب وإفلات الملفات
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                uploadContainer.classList.add('dragover');
            });

            uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                if (!uploadArea.contains(e.relatedTarget)) {
                    uploadContainer.classList.remove('dragover');
                }
            });

            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                uploadContainer.classList.remove('dragover');
                const files = e.dataTransfer.files;
                handleFiles(files);
            });

            // التعامل مع اختيار الملفات
            fileInput.addEventListener('change', function() {
                handleFiles(this.files);
            });

            function handleFiles(files) {
                if (files.length > 0) {
                    selectedImages.classList.add('active');
                    selectedImages.innerHTML = '<h5>الصور المختارة:</h5><div class="images-grid" id="imagesGrid"></div>';
                    const imagesGrid = document.getElementById('imagesGrid');
                    
                    Array.from(files).forEach((file, index) => {
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            
                            reader.onload = function(e) {
                                const imagePreview = document.createElement('div');
                                imagePreview.className = 'image-preview';
                                imagePreview.innerHTML = `
                                    <img src="${e.target.result}" alt="${file.name}">
                                    <div class="image-actions">
                                        <button type="button" class="btn btn-danger btn-sm remove-image" data-index="${index}">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="image-info">
                                        <div class="image-name">${file.name}</div>
                                        <div class="image-size">${formatFileSize(file.size)}</div>
                                    </div>
                                `;
                                imagesGrid.appendChild(imagePreview);
                            };
                            
                            reader.readAsDataURL(file);
                        }
                    });

                    // إضافة حدث إزالة الصور
                    document.querySelectorAll('.remove-image').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const index = parseInt(this.dataset.index);
                            removeImage(index);
                        });
                    });
                }
            }

            function removeImage(index) {
                // إنشاء DataTransfer جديد لإدارة الملفات
                const dt = new DataTransfer();
                const files = fileInput.files;
                
                // إضافة جميع الملفات ما عدا الملف المراد إزالته
                for (let i = 0; i < files.length; i++) {
                    if (i !== index) {
                        dt.items.add(files[i]);
                    }
                }
                
                // تحديث input الملفات
                fileInput.files = dt.files;
                
                // إعادة تحميل المعاينات
                if (fileInput.files.length > 0) {
                    handleFiles(fileInput.files);
                } else {
                    selectedImages.classList.remove('active');
                    selectedImages.innerHTML = '';
                }
            }

            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            // التحقق من الملفات قبل الإرسال
            document.getElementById('addProductForm').addEventListener('submit', function(e) {
                const files = fileInput.files;
                let hasError = false;
                
                // التحقق من حجم الملفات
                Array.from(files).forEach(file => {
                    if (file.size > 5 * 1024 * 1024) { // 5MB
                        alert(`حجم ملف ${file.name} كبير جداً. الحد الأقصى المسموح به هو 5MB`);
                        hasError = true;
                    }
                });
                
                if (hasError) {
                    e.preventDefault();
                }
            });
        });
</script>
<!-- نافذة عرض بيانات المنتج -->
<div class="modal" id="viewProductModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-eye"></i> <span data-translate="product_details">بيانات المنتج</span></h3>
            <button class="close-btn">&times;</button>
        </div>
        <div class="modal-body">
            <div id="productDetailsContent">
                <div class="product-view-container">
                    <div class="product-view-header">
                        <div class="product-image-section">
                            <div id="viewProductImage" class="product-main-image">
                                <!-- سيتم تحميل الصورة هنا -->
                            </div>
                        </div>
                        <div class="product-basic-info">
                            <h2 id="viewItemName" class="product-title"></h2>
                            <div class="product-code-section">
                                <span class="label">الرقم التسلسلي:</span>
                                <span id="viewS_NO" class="value"></span>
                            </div>
                            <div class="product-code-section">
                                <span class="label">رمز المنتج:</span>
                                <span id="viewItemCode" class="value code"></span>
                            </div>
                            <div id="viewFeaturedBadge" class="featured-badge" style="display: none;">
                                <i class="fas fa-star"></i> منتج مميز
                            </div>
                        </div>
                    </div>
                    
                    <div class="product-details-grid">
                        <div class="detail-item">
                            <span class="label">التغليف:</span>
                            <span id="viewPacking" class="value"></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">المجموعة:</span>
                            <span id="viewItemGroup" class="value"></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">العلامة التجارية:</span>
                            <span id="viewBrand" class="value"></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">تاريخ الإضافة:</span>
                            <span id="viewCreatedAt" class="value"></span>
                        </div>
                    </div>
                    
                    <div class="product-images-section" id="productImagesSection" style="display: none;">
                        <h4>صور المنتج</h4>
                        <div class="product-images-grid" id="productImagesGrid">
                            <!-- سيتم تحميل الصور الإضافية هنا -->
                        </div>
                    </div>
                </div>
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
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_S_NO">الرقم التسلسلي</label> *
                        <input type="number" class="form-control" id="edit_S_NO" name="S_NO" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_Item_Code">رمز المنتج</label> *
                        <input type="text" class="form-control" id="edit_Item_Code" name="Item_Code" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_Item_Name">اسم المنتج</label> *
                    <textarea class="form-control" id="edit_Item_Name" name="Item_Name" rows="2" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_Packing">التغليف</label>
                        <input type="text" class="form-control" id="edit_Packing" name="Packing">
                    </div>
                    <div class="form-group">
                        <label for="edit_Item_Group">المجموعة</label>
                        <input type="text" class="form-control" id="edit_Item_Group" name="Item_Group">
                    </div>
                    <div class="form-group">
                        <label for="edit_Brand">العلامة التجارية</label>
                        <input type="text" class="form-control" id="edit_Brand" name="Brand">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_images">إضافة صور جديدة</label>
                    <div class="file-input-wrapper">
                        <div class="file-input-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span style="margin-right: 8px;">إضافة صور جديدة للمنتج</span>
                        </div>
                        <input type="file" class="form-control" id="edit_images" name="images[]" multiple accept="image/*">
                    </div>
                    <div class="file-name" id="editFileNames"></div>
                    <small class="form-text text-muted">سيتم إضافة الصور الجديدة إلى الصور الحالية</small>
                </div>
                
                <div class="current-images-section" id="currentImagesSection" style="display: none;">
                    <label>الصور الحالية:</label>
                    <div class="current-images-grid" id="currentImagesGrid">
                        <!-- سيتم تحميل الصور الحالية هنا -->
                    </div>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="edit_featured" name="featured" value="1">
                    <label class="form-check-label" for="edit_featured">منتج مميز</label>
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
                <div class="delete-warning">
                    <i class="fas fa-exclamation-triangle" style="color: var(--danger-color); font-size: 2rem; margin-bottom: 15px;"></i>
                    <p data-translate="confirm_delete_message">هل أنت متأكد من حذف هذا المنتج؟</p>
                    <p style="color: var(--danger-color); font-weight: bold;" data-translate="delete_warning">هذا الإجراء لا يمكن التراجع عنه!</p>
                    <div class="product-to-delete">
                        <strong>المنتج المراد حذفه:</strong>
                        <p id="deleteProductName" style="font-weight: bold; margin-top: 5px;"></p>
                    </div>
                </div>
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
        // JavaScript لإدارة النماذج والعروض
        document.addEventListener('DOMContentLoaded', function() {
            // عناصر النماذج
            const addProductModal = document.getElementById('addProductModal');
            const viewProductModal = document.getElementById('viewProductModal');
            const editProductModal = document.getElementById('editProductModal');
            const deleteProductModal = document.getElementById('deleteProductModal');
            
            // أزرار الفتح والإغلاق
            const closeButtons = document.querySelectorAll('.close-btn');
            const addProductBtn = document.getElementById('addProductBtn');
            const addFirstProductBtn = document.getElementById('addFirstProductBtn');
            
            // أزرار الإجراءات في الجدول
            const viewProductBtns = document.querySelectorAll('.view-product-btn');
            const editProductBtns = document.querySelectorAll('.edit-product-btn');
            const deleteProductBtns = document.querySelectorAll('.delete-product-btn');
            
            // فتح نافذة الإضافة
            if (addProductBtn) {
                addProductBtn.addEventListener('click', () => openModal(addProductModal));
            }
            if (addFirstProductBtn) {
                addFirstProductBtn.addEventListener('click', () => openModal(addProductModal));
            }
            
            // إغلاق النماذج
            closeButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const modal = this.closest('.modal');
                    closeModal(modal);
                });
            });
            
            // إغلاق النماذج عند النقر خارجها
            window.addEventListener('click', function(event) {
                if (event.target.classList.contains('modal')) {
                    closeModal(event.target);
                }
            });
            
            // إدارة عرض الملفات
            const addFileInput = document.getElementById('images');
            const editFileInput = document.getElementById('edit_images');
            
            if (addFileInput) {
                addFileInput.addEventListener('change', function() {
                    updateFileNames(this, 'addFileNames');
                });
            }
            
            if (editFileInput) {
                editFileInput.addEventListener('change', function() {
                    updateFileNames(this, 'editFileNames');
                });
            }
            
            // عرض بيانات المنتج
            viewProductBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const productData = {
                        id: this.dataset.productId,
                        S_NO: this.dataset.productSno,
                        Item_Code: this.dataset.productCode,
                        Item_Name: this.dataset.productName,
                        Packing: this.dataset.productPacking,
                        Item_Group: this.dataset.productGroup,
                        Brand: this.dataset.productBrand,
                        featured: this.dataset.productFeatured,
                        image: this.dataset.productImage,
                        date: this.dataset.productDate
                    };
                    showProductDetails(productData);
                    openModal(viewProductModal);
                });
            });
            
            // تعديل المنتج
            editProductBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const productData = {
                        id: this.dataset.productId,
                        S_NO: this.dataset.productSno,
                        Item_Code: this.dataset.productCode,
                        Item_Name: this.dataset.productName,
                        Packing: this.dataset.productPacking,
                        Item_Group: this.dataset.productGroup,
                        Brand: this.dataset.productBrand,
                        featured: this.dataset.productFeatured
                    };
                    fillEditForm(productData);
                    openModal(editProductModal);
                });
            });
            
            // حذف المنتج
            deleteProductBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const productId = this.dataset.productId;
                    const productName = this.dataset.productName;
                    setupDeleteModal(productId, productName);
                    openModal(deleteProductModal);
                });
            });
            
            // الانتقال من العرض إلى التعديل
            const editFromViewBtn = document.getElementById('editFromViewBtn');
            if (editFromViewBtn) {
                editFromViewBtn.addEventListener('click', function() {
                    closeModal(viewProductModal);
                    // هنا يمكنك إعادة تعبئة نموذج التعديل بالبيانات الحالية
                    openModal(editProductModal);
                });
            }
        });

        // الدوال المساعدة
        function openModal(modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function updateFileNames(fileInput, containerId) {
            const container = document.getElementById(containerId);
            if (fileInput.files.length > 0) {
                const fileNames = Array.from(fileInput.files).map(file => file.name).join(', ');
                container.textContent = `الملفات المختارة: ${fileNames}`;
                container.style.display = 'block';
            } else {
                container.style.display = 'none';
            }
        }

        function showProductDetails(product) {
            // الصورة الرئيسية
            const imageContainer = document.getElementById('viewProductImage');
            if (product.image) {
                imageContainer.innerHTML = `
                    <img src="../uploads/products/${product.image}" 
                        alt="${product.Item_Name}" 
                        class="product-main-img">
                `;
            } else {
                imageContainer.innerHTML = `
                    <div class="no-image-large">
                        <i class="fas fa-image"></i>
                        <span>لا توجد صورة</span>
                    </div>
                `;
            }
            
            // المعلومات الأساسية
            document.getElementById('viewItemName').textContent = product.Item_Name;
            document.getElementById('viewS_NO').textContent = product.S_NO;
            document.getElementById('viewItemCode').textContent = product.Item_Code;
            document.getElementById('viewPacking').textContent = product.Packing || 'غير محدد';
            document.getElementById('viewItemGroup').textContent = product.Item_Group || 'غير محدد';
            document.getElementById('viewBrand').textContent = product.Brand || 'غير محدد';
            document.getElementById('viewCreatedAt').textContent = product.date;
            
            // حالة المنتج المميز
            const featuredBadge = document.getElementById('viewFeaturedBadge');
            if (product.featured === '1') {
                featuredBadge.style.display = 'block';
            } else {
                featuredBadge.style.display = 'none';
            }
            
            // حفظ معرف المنتج للاستخدام لاحقاً
            document.getElementById('editFromViewBtn').dataset.productId = product.id;
        }

        function fillEditForm(product) {
            document.getElementById('edit_product_id').value = product.id;
            document.getElementById('edit_S_NO').value = product.S_NO;
            document.getElementById('edit_Item_Code').value = product.Item_Code;
            document.getElementById('edit_Item_Name').value = product.Item_Name;
            document.getElementById('edit_Packing').value = product.Packing || '';
            document.getElementById('edit_Item_Group').value = product.Item_Group || '';
            document.getElementById('edit_Brand').value = product.Brand || '';
            document.getElementById('edit_featured').checked = product.featured === '1';
            
            // هنا يمكنك إضافة كود لجلب وعرض الصور الحالية
            loadCurrentImages(product.id);
        }

        function setupDeleteModal(productId, productName) {
            document.getElementById('delete_product_id').value = productId;
            document.getElementById('deleteProductName').textContent = productName;
        }

        function loadCurrentImages(productId) {
            // هذه الدالة تحتاج إلى تنفيذ AJAX لجلب الصور الحالية للمنتج
            // مثال:
            /*
            fetch(`get_product_images.php?product_id=${productId}`)
                .then(response => response.json())
                .then(images => {
                    const container = document.getElementById('currentImagesGrid');
                    container.innerHTML = '';
                    
                    images.forEach(image => {
                        const imgElement = document.createElement('div');
                        imgElement.className = 'current-image-item';
                        imgElement.innerHTML = `
                            <img src="../uploads/products/${image.image_name}" alt="صورة المنتج">
                            <button type="button" class="remove-image-btn" data-image-id="${image.id}">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                        container.appendChild(imgElement);
                    });
                    
                    document.getElementById('currentImagesSection').style.display = 'block';
                });
            */
        }
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