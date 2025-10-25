<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$success = '';
$error = '';

// دالة لتنظيف الترميز
function cleanEncoding($string) {
    $string = str_replace("\xEF\xBB\xBF", '', $string);
    $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $string);
    return trim($string);
}

// دالة لإنشاء قالب CSV
function generateTemplate() {
    $template_data = [
        ['S_NO', 'Item_Code', 'Item_Name', 'Packing', 'Item_Group', 'Brand', 'featured', 'image_name'],
        ['2', 'PROD001', 'Milk Chocolate', '100g', 'Sweets', 'Chocolate Park', 'Yes', '1.jpg'],
        ['3', 'PROD002', 'Chocolate Biscuit', '150g', 'Bakery', 'Biscuita', 'No', '2.jpg'],
        ['4', 'PROD003', 'Orange Juice', '1L', 'Beverages', 'Natural Juices', 'Yes', '3.jpg'],
        ['5', 'PROD004', 'Toothpaste', '75ml', 'Personal Care', 'Sinan', 'No', 'toothpaste.jpg'],
        ['6', 'PROD005', 'Basmati Rice', '5kg', 'Food', 'Golden Rice', 'Yes', 'basmati-rice.jpg']
    ];
    
    return $template_data;
}

// تحميل قالب CSV
if (isset($_GET['download_template'])) {
    $template_data = generateTemplate();
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=products_template.csv');
    
    $output = fopen('php://output', 'w');
    fputs($output, "\xEF\xBB\xBF");
    
    foreach ($template_data as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

// معالجة تصدير المنتجات
if (isset($_POST['export_products'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=products_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputs($output, "\xEF\xBB\xBF");
    
    fputcsv($output, ['S_NO', 'Item_Code', 'Item_Name', 'Packing', 'Item_Group', 'Brand', 'featured', 'image_name']);
    
    try {
        $stmt = $pdo->query("
            SELECT p.S_NO, p.Item_Code, p.Item_Name, p.Packing, p.Item_Group, p.Brand, p.featured,
                pi.image_name 
            FROM products p 
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
            ORDER BY p.S_NO
        ");
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row = array_map('cleanEncoding', $row);
            $row['featured'] = $row['featured'] ? 'Yes' : 'No';
            $row['image_name'] = $row['image_name'] ?? '';
            fputcsv($output, $row);
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "خطأ في التصدير: " . $e->getMessage();
        header("Location: import_export.php");
        exit();
    }
    
    fclose($output);
    exit();
}

// معالجة رفع الصور المتعددة - منفصلة ومستقلة
if (isset($_POST['upload_images'])) {
    if (isset($_FILES['product_images']) && !empty($_FILES['product_images']['name'][0])) {
        $uploaded_files = [];
        $errors = [];
        $success_count = 0;
        $error_count = 0;
        
        $max_file_size = 20 * 1024 * 1024;
        $max_total_size = 100 * 1024 * 1024;
        $max_files = 50;
        
        $upload_dir = '../uploads/products/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $total_size = 0;
        foreach ($_FILES['product_images']['size'] as $size) {
            $total_size += $size;
        }
        
        if ($total_size > $max_total_size) {
            $error = "❌ الحجم الإجمالي للصور كبير جداً. الحد الأقصى: " . round($max_total_size / (1024 * 1024)) . "MB";
        } else {
            // بدء معالجة كل صورة
            foreach ($_FILES['product_images']['name'] as $key => $name) {
                $file_tmp = $_FILES['product_images']['tmp_name'][$key];
                $file_size = $_FILES['product_images']['size'][$key];
                $file_error = $_FILES['product_images']['error'][$key];
                
                if ($file_error === UPLOAD_ERR_OK) {
                    $file_ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
                    
                    if (in_array($file_ext, $allowed_ext)) {
                        if ($file_size <= $max_file_size) {
                            $original_name = pathinfo($name, PATHINFO_FILENAME);
                            $new_filename = $original_name . '_' . uniqid() . '.' . $file_ext;
                            $file_destination = $upload_dir . $new_filename;
                            
                            if (!file_exists($file_destination)) {
                                if (move_uploaded_file($file_tmp, $file_destination)) {
                                    // ✅ حفظ الصورة في قاعدة البيانات مباشرة
                                    try {
                                        $stmt = $pdo->prepare("INSERT INTO product_images (image_name, is_primary) VALUES (?, 0)");
                                        $stmt->execute([$new_filename]);
                                        
                                        $uploaded_files[] = [
                                            'original_name' => $name,
                                            'saved_name' => $new_filename,
                                            'file_size' => round($file_size / 1024, 2) . ' KB',
                                            'db_id' => $pdo->lastInsertId()
                                        ];
                                        $success_count++;
                                    } catch (PDOException $e) {
                                        $errors[] = "❌ فشل في حفظ الصورة في قاعدة البيانات: $name - " . $e->getMessage();
                                        $error_count++;
                                    }
                                } else {
                                    $errors[] = "❌ فشل في رفع الملف: $name";
                                    $error_count++;
                                }
                            } else {
                                $errors[] = "⚠️ الملف موجود مسبقاً: $name";
                                $error_count++;
                            }
                        } else {
                            $errors[] = "❌ حجم الملف كبير جداً: $name (" . round($file_size / (1024 * 1024), 2) . "MB)";
                            $error_count++;
                        }
                    } else {
                        $errors[] = "❌ نوع الملف غير مسموح به: $name";
                        $error_count++;
                    }
                } else {
                    $error_messages = [
                        UPLOAD_ERR_INI_SIZE => "حجم الملف يتجاوز الحد المسموح",
                        UPLOAD_ERR_FORM_SIZE => "حجم الملف يتجاوز الحد المسموح",
                        UPLOAD_ERR_PARTIAL => "تم رفع جزء من الملف فقط",
                        UPLOAD_ERR_NO_FILE => "لم يتم اختيار ملف",
                        UPLOAD_ERR_NO_TMP_DIR => "مجلد التخزين المؤقت غير موجود",
                        UPLOAD_ERR_CANT_WRITE => "فشل في كتابة الملف على القرص",
                        UPLOAD_ERR_EXTENSION => "رفع الملف متوقف بسبب امتداد غير مسموح"
                    ];
                    $error_msg = $error_messages[$file_error] ?? "خطأ غير معروف";
                    $errors[] = "❌ خطأ في رفع الملف: $name - $error_msg";
                    $error_count++;
                }
            }
            
            // ✅ عرض النتائج النهائية
            if ($success_count > 0) {
                $success = "✅ تم رفع $success_count صورة بنجاح وحفظها في قاعدة البيانات!";
                
                // حفظ معلومات الصور في الجلسة للاستيراد لاحقاً
                $_SESSION['uploaded_images'] = $uploaded_files;
                $_SESSION['upload_stats'] = [
                    'success' => $success_count,
                    'errors' => $error_count,
                    'total' => count($_FILES['product_images']['name'])
                ];
            }
            
            if (!empty($errors)) {
                $error = "📊 إحصائيات الرفع:<br>";
                $error .= "✅ ناجح: $success_count<br>";
                $error .= "❌ فاشل: $error_count<br>";
                $error .= "📁 الإجمالي: " . count($_FILES['product_images']['name']) . "<br><br>";
                $error .= "الأخطاء:<br>" . implode('<br>', array_slice($errors, 0, 10));
                if (count($errors) > 10) {
                    $error .= "<br>... و " . (count($errors) - 10) . " أخطاء أخرى";
                }
            }
        }
    } else {
        $error = "❌ لم يتم اختيار أي ملفات للرفع";
    }
}

// معالجة استيراد المنتجات
if (isset($_POST['import_products']) && isset($_FILES['csv_file'])) {
    if ($_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['csv_file']['tmp_name'];
        
        // قراءة الملف كاملاً والتعامل مع الترميز
        $csv_content = file_get_contents($file);
        if (!mb_detect_encoding($csv_content, 'UTF-8', true)) {
            $csv_content = mb_convert_encoding($csv_content, 'UTF-8', 'ISO-8859-1');
        }
        
        // تقسيم المحتوى إلى أسطر
        $lines = explode("\n", $csv_content);
        
        $imported = 0;
        $updated = 0;
        $errors = [];
        $uploaded_images = $_SESSION['uploaded_images'] ?? [];
        
        // تخطي الصف الأول (العناوين)
        array_shift($lines);
        
        $line_number = 1;
        
        // بدء transaction
        $pdo->beginTransaction();
        
        try {
            foreach ($lines as $line) {
                $line_number++;
                $line = trim($line);
                
                if (empty($line)) continue;
                
                // استخدام str_getcsv للتعامل مع الاقتباسات بشكل صحيح
                $data = str_getcsv($line);
                
                // إذا كان عدد الأعمدة أقل من 7، تخطى هذا السطر
                if (count($data) < 7) {
                    $errors[] = "سطر $line_number: عدد أعمدة غير كافي";
                    continue;
                }
                
                // تنظيف البيانات
                $S_NO = cleanEncoding($data[0] ?? '');
                $Item_Code = cleanEncoding($data[1] ?? '');
                $Item_Name = cleanEncoding($data[2] ?? '');
                $Packing = cleanEncoding($data[3] ?? '');
                $Item_Group = cleanEncoding($data[4] ?? '');
                $Brand = cleanEncoding($data[5] ?? '');
                $featured = isset($data[6]) ? (strtolower(cleanEncoding($data[6])) == 'yes' || cleanEncoding($data[6]) == '1' ? 1 : 0) : 0;
                $image_name = isset($data[7]) ? cleanEncoding($data[7]) : '';
                
                if (!empty($Item_Code) && !empty($Item_Name)) {
                    // البحث عن الصورة المرفوعة مسبقاً
                    $actual_image_name = '';
                    $image_db_id = null;
                    
                    if (!empty($image_name)) {
                        foreach ($uploaded_images as $uploaded) {
                            if ($uploaded['original_name'] === $image_name) {
                                $actual_image_name = $uploaded['saved_name'];
                                $image_db_id = $uploaded['db_id'];
                                break;
                            }
                        }
                    }
                    
                    // التحقق من وجود المنتج
                    $stmt = $pdo->prepare("SELECT id FROM products WHERE Item_Code = ?");
                    $stmt->execute([$Item_Code]);
                    $existing_product = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($existing_product) {
                        // تحديث المنتج الموجود
                        $stmt = $pdo->prepare("UPDATE products SET S_NO = ?, Item_Name = ?, Packing = ?, Item_Group = ?, Brand = ?, featured = ? WHERE Item_Code = ?");
                        $stmt->execute([$S_NO, $Item_Name, $Packing, $Item_Group, $Brand, $featured, $Item_Code]);
                        $product_id = $existing_product['id'];
                        $updated++;
                    } else {
                        // إضافة منتج جديد
                        $stmt = $pdo->prepare("INSERT INTO products (S_NO, Item_Code, Item_Name, Packing, Item_Group, Brand, featured) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$S_NO, $Item_Code, $Item_Name, $Packing, $Item_Group, $Brand, $featured]);
                        $product_id = $pdo->lastInsertId();
                        $imported++;
                    }
                    
                    // ربط الصورة بالمنتج إذا كانت موجودة
                    if (!empty($actual_image_name) && isset($product_id) && $image_db_id) {
                        $stmt = $pdo->prepare("UPDATE product_images SET product_id = ?, is_primary = 1 WHERE id = ?");
                        $stmt->execute([$product_id, $image_db_id]);
                    }
                } else {
                    $errors[] = "سطر $line_number: بيانات ناقصة - Item_Code أو Item_Name فارغ";
                }
            }
            
            $pdo->commit();
            
            // ✅ رسالة نجاح مفصلة
            if ($imported > 0 || $updated > 0) {
                $image_count = count($uploaded_images);
                $success = "🎉 تم الانتهاء من الاستيراد بنجاح!<br>";
                $success .= "✅ المنتجات المضافة: $imported<br>";
                $success .= "✏️ المنتجات المحدثة: $updated<br>";
                $success .= "🖼️ الصور المرتبطة: $image_count";
            }
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "❌ خطأ في قاعدة البيانات: " . $e->getMessage();
        }
        
        // مسح بيانات الجلسة بعد الاستيراد
        unset($_SESSION['uploaded_images']);
        unset($_SESSION['upload_stats']);
        
        if (!empty($errors)) {
            $error = "حدثت أخطاء في بعض السجلات: " . implode('; ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $error .= " ... والمزيد";
            }
        }
    } else {
        $error = "❌ حدث خطأ في رفع الملف";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-translate="import_export_products">استيراد وتصدير المنتجات</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --info: #4895ef;
            --warning: #f72585;
            --danger: #e63946;
            --light: #f8f9fa;
            --dark: #212529;
            --sidebar-bg: #1e293b;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fb;
            color: #333;
            line-height: 1.6;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* الشريط الجانبي */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            color: white;
            transition: var(--transition);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .sidebar-header h3 {
            margin-bottom: 0.5rem;
            font-size: 1.4rem;
        }

        .sidebar-header p {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .sidebar-menu {
            list-style: none;
            padding: 1rem 0;
        }

        .sidebar-menu li {
            margin-bottom: 0.2rem;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.8rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--transition);
            border-right: 3px solid transparent;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-right-color: var(--primary);
        }

        .sidebar-menu i {
            margin-left: 0.5rem;
            width: 20px;
            text-align: center;
        }

        /* المحتوى الرئيسي */
        .main-content {
            flex: 1;
            padding: 1.5rem;
            overflow-y: auto;
        }

        .header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .header h1 {
            color: var(--dark);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: #64748b;
        }

        /* بطاقات */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: var(--transition);
            border: none;
            margin-bottom: 1.5rem;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            background: white;
        }

        .card-header h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark);
        }

        .card-body {
            padding: 1.5rem;
        }

        /* الأزرار */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.7rem 1.2rem;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--secondary);
            color: white;
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-success:hover {
            background: #38b2d6;
            color: white;
        }

        .btn-warning {
            background: var(--warning);
            color: white;
        }

        .btn-warning:hover {
            background: #e11568;
            color: white;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid #d1d5db;
            color: #374151;
        }

        .btn-outline:hover {
            background: #f8f9fa;
            border-color: #9ca3af;
        }

        /* نماذج */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        input[type="file"].form-control {
            padding: 0.5rem;
        }

        /* شبكة الصفوف والأعمدة */
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -0.75rem;
        }

        .col-6 {
            flex: 0 0 50%;
            max-width: 50%;
            padding: 0 0.75rem;
        }

        /* تنبيهات */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert i {
            margin-left: 0.5rem;
        }

        /* بطاقات الإجراءات */
        .action-card {
            text-align: center;
            height: 100%;
            transition: var(--transition);
        }

        .action-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .info-box {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }

        /* الجداول */
        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th, .table td {
            padding: 0.8rem 1rem;
            text-align: right;
            border: 1px solid #e2e8f0;
        }

        .table th {
            background: #f8fafc;
            font-weight: 600;
            color: #475569;
        }

        .table tr:hover {
            background: #f8fafc;
        }

        /* زر الترجمة */
        .translate-btn {
            position: fixed;
            bottom: 20px;
            left: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary);
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
            background: var(--secondary);
        }

        .translate-btn i {
            font-size: 1.2rem;
        }

        /* نمط للغة الإنجليزية */
        body[dir="ltr"] {
            text-align: left;
        }

        body[dir="ltr"] .sidebar {
            text-align: left;
        }

        body[dir="ltr"] .sidebar-menu i {
            margin-left: 0;
            margin-right: 0.5rem;
        }

        body[dir="ltr"] .alert i {
            margin-left: 0;
            margin-right: 0.5rem;
        }

        body[dir="ltr"] .table th, 
        body[dir="ltr"] .table td {
            text-align: left;
        }

        /* تصميم متجاوب */
        @media (max-width: 992px) {
            .dashboard {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
            }
            
            .sidebar-menu {
                display: flex;
                overflow-x: auto;
                padding: 0.5rem;
            }
            
            .sidebar-menu li {
                flex: 0 0 auto;
                margin-bottom: 0;
            }
            
            .sidebar-menu a {
                padding: 0.8rem 1rem;
                border-right: none;
                border-bottom: 3px solid transparent;
            }
            
            .sidebar-menu a:hover, .sidebar-menu a.active {
                border-right-color: transparent;
                border-bottom-color: var(--primary);
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }
            
            .col-6 {
                flex: 0 0 100%;
                max-width: 100%;
                margin-bottom: 1rem;
            }
            
            .table-responsive {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
       <?php include 'sidebar.php'; ?>
        
        <!-- المحتوى الرئيسي -->
        <main class="main-content">
            <?php include 'admin_navbar.php'; ?>
            <div class="header">
                <h1><i class="fas fa-file-import"></i> <span data-translate="import_export_products">استيراد وتصدير المنتجات</span></h1>
                <p data-translate="import_export_desc">إدارة نقل البيانات من وإلى النظام</p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- تصدير المنتجات -->
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-file-export"></i> <span data-translate="export_products">تصدير المنتجات</span></h3>
                        </div>
                        <div class="card-body">
                            <div class="action-card">
                                <div class="action-icon" style="color: var(--success);">
                                    <i class="fas fa-file-csv"></i>
                                </div>
                                <h4 data-translate="export_to_csv">تصدير إلى CSV</h4>
                                <p style="color: #666; margin-bottom: 2rem;" data-translate="export_description">قم بتحميل جميع المنتجات في ملف CSV</p>
                                
                                <form method="POST">
                                    <button type="submit" name="export_products" class="btn btn-success">
                                        <i class="fas fa-download"></i> <span data-translate="download_csv">تحميل ملف CSV</span>
                                    </button>
                                </form>
                            </div>
                            
                            <div class="info-box">
                                <h5><i class="fas fa-info-circle"></i> <span data-translate="export_info">معلومات التصدير:</span></h5>
                                <ul style="text-align: right; margin-right: 1rem;">
                                    <li data-translate="export_info_1">سيتم تصدير جميع المنتجات</li>
                                    <li data-translate="export_info_2">التنسيق: CSV (UTF-8)</li>
                                    <li data-translate="export_info_3">الحقول: S_NO, Item_Code, Item_Name, Packing, Item_Group, Brand, featured,image_name</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
               <!-- رفع الصور -->
                 <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-images"></i> رفع الصور أولاً</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                                <div class="action-card">
                                    <div class="action-icon" style="color: var(--info);">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </div>
                                    <h4>رفع صور المنتجات إلى قاعدة البيانات</h4>
                                    
                                    <div class="form-group">
                                        <label for="product_images">اختر صور المنتجات</label>
                                        <input type="file" class="form-control" id="product_images" name="product_images[]" multiple accept="image/*" required>
                                        <small class="form-text text-muted">
                                            ✅ سيتم رفع الصور فوراً وحفظها في قاعدة البيانات
                                        </small>
                                    </div>
                                    
                                    <!-- معلومات الملفات المختارة -->
                                    <div id="fileInfo" class="file-info">
                                        <strong>📁 الملفات المختارة:</strong>
                                        <div id="fileList" class="file-list"></div>
                                        <div id="totalSize" style="font-weight: bold; color: #0d6efd;"></div>
                                    </div>
                                    
                                    <button type="submit" name="upload_images" class="btn btn-info btn-lg" id="uploadBtn" style="padding: 12px 30px; font-size: 16px;">
                                        <i class="fas fa-upload"></i> <span>رفع الصور الآن</span>
                                    </button>
                                </div>
                            </form>
                            
                            <!-- إحصائيات الرفع -->
                            <?php if (isset($_SESSION['upload_stats'])): ?>
                            <div class="upload-stats mt-3">
                                <h5><i class="fas fa-chart-bar"></i> إحصائيات الرفع:</h5>
                                <?php $stats = $_SESSION['upload_stats']; ?>
                                <div class="stats-grid">
                                    <div class="stat-item stat-success">
                                        <i class="fas fa-check-circle fa-2x"></i>
                                        <div class="mt-2">ناجح</div>
                                        <h4><?php echo $stats['success']; ?></h4>
                                    </div>
                                    <div class="stat-item stat-error">
                                        <i class="fas fa-times-circle fa-2x"></i>
                                        <div class="mt-2">فاشل</div>
                                        <h4><?php echo $stats['errors']; ?></h4>
                                    </div>
                                    <div class="stat-item stat-total">
                                        <i class="fas fa-file-alt fa-2x"></i>
                                        <div class="mt-2">الإجمالي</div>
                                        <h4><?php echo $stats['total']; ?></h4>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="info-box mt-3">
                                <h5><i class="fas fa-lightbulb"></i> كيف يعمل:</h5>
                                <ol style="text-align: right; margin-right: 1rem;">
                                    <li>📸 <strong>ارفع الصور أولاً</strong> (سيتم حفظها فوراً في قاعدة البيانات)</li>
                                    <li>📁 <strong>استورد ملف CSV</strong> بعد رفع الصور</li>
                                    <li>🔗 <strong>سيتم الربط تلقائياً</strong> بين المنتجات والصور</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- استيراد المنتجات -->
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h3><i class="fas fa-file-import"></i> <span data-translate="import_products">استيراد المنتجات</span></h3>
                            </div>
                            
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="action-card">
                                        <div class="action-icon" style="color: var(--primary);">
                                            <i class="fas fa-upload"></i>
                                        </div>
                                        <h4 data-translate="upload_csv">رفع ملف CSV</h4>
                                        
                                        <div class="form-group">
                                            <label for="csv_file" data-translate="choose_csv_file">اختر ملف CSV</label>
                                            <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                                        </div>
                                        
                                        <button type="submit" name="import_products" class="btn btn-primary">
                                            <i class="fas fa-upload"></i> <span data-translate="import_products_btn">استيراد المنتجات</span>
                                        </button>
                                    </div>
                                </form>
                                
                                <div class="info-box">
                                    <h5><i class="fas fa-download"></i> <span data-translate="csv_template">نموذج ملف CSV:</span></h5>
                                    <p style="text-align: center; margin: 1rem 0;">
                                        <a href="?download_template=1" class="btn btn-warning" style="padding: 0.5rem 1rem;">
                                            <i class="fas fa-file-download"></i> <span data-translate="download_template">تحميل نموذج</span>
                                        </a>
                                    </p>
                                    <small style="color: #666;" data-translate="template_note">تأكد من تطابق تنسيق الملف مع النموذج</small>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>

            <!-- تعليمات الاستيراد -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-graduation-cap"></i> <span data-translate="import_instructions">تعليمات الاستيراد</span></h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <h4><i class="fas fa-check-circle" style="color: var(--success);"></i> <span data-translate="requirements">المتطلبات:</span></h4>
                            <ul style="text-align: right; margin-right: 1rem;">
                                <li data-translate="req_1">يجب أن يكون الملف بصيغة CSV</li>
                                <li data-translate="req_2">التشفير: UTF-8</li>
                                <li data-translate="req_3">الفاصل: comma (,) أو semicolon (;)</li>
                                <li data-translate="req_4">الصف الأول يجب أن يحتوي على العناوين</li>
                                <li data-translate="req_5">الحقول المطلوبة: Item_Code, Item_Name</li>
                                 <li data-translate="req_6">حقل image_name: اسم ملف الصورة (مثال: product.jpg)</li>
                            </ul>
                        </div>
                        <div class="col-6">
                            <h4><i class="fas fa-exclamation-triangle" style="color: var(--warning);"></i> <span data-translate="notes">ملاحظات:</span></h4>
                            <ul style="text-align: right; margin-right: 1rem;">
                                <li data-translate="note_1">المنتجات المكررة سيتم تحديثها</li>
                                <li data-translate="note_2">featured: نعم/لا أو 1/0</li>
                                <li data-translate="note_3">S_NO يمكن أن يكون فارغاً</li>
                                  <li data-translate="note_4">image_name: اسم ملف الصورة فقط</li>
                                <li data-translate="note_">احتفظ بنسخة احتياطية قبل الاستيراد</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- نموذج جدول -->
                  <!-- في قسم معلومات التصدير -->
                <div class="info-box">
                    <h5><i class="fas fa-info-circle"></i> <span data-translate="export_info">معلومات التصدير:</span></h5>
                    <ul style="text-align: right; margin-right: 1rem;">
                        <li data-translate="export_info_1">سيتم تصدير جميع المنتجات</li>
                        <li data-translate="export_info_2">التنسيق: CSV (UTF-8)</li>
                        <li data-translate="export_info_3">الحقول: S_NO, Item_Code, Item_Name, Packing, Item_Group, Brand, featured, image_name </li>
                    </ul>
                </div>

                <!-- في قسم هيكل الملف -->
                <div style="margin-top: 2rem;">
                    <h4 data-translate="file_structure">هيكل الملف المطلوب:</h4>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>S_NO</th>
                                    <th data-translate="item_code">Item_Code</th>
                                    <th data-translate="item_name">Item_Name</th>
                                    <th data-translate="packing">Packing</th>
                                    <th data-translate="item_group">Item_Group</th>
                                    <th data-translate="brand">Brand</th>
                                    <th data-translate="featured">featured</th>
                                    <th data-translate="image_name">image_name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>PROD001</td>
                                    <td data-translate="sample_product">منتج مثال</td>
                                    <td>100 جرام</td>
                                    <td data-translate="sample_category">حلويات</td>
                                    <td data-translate="sample_brand">علامة تجارية</td>
                                    <td>نعم</td>
                                    <td>product1.jpg</td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>PROD002</td>
                                    <td data-translate="sample_product2">منتج مثال 2</td>
                                    <td>150 جرام</td>
                                    <td data-translate="sample_category2">مخبوزات</td>
                                    <td data-translate="sample_brand2">علامة تجارية 2</td>
                                    <td>لا</td>
                                    <td>product2.jpg</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                </div>
            </div>
        </main>
    </div>

    <!-- زر الترجمة العائم -->
    <button class="translate-btn" id="translateBtn">
        <i class="fas fa-language"></i>
    </button>
   <script>
        // التحقق من الملفات قبل الرفع
        document.getElementById('product_images').addEventListener('change', function(e) {
            const files = e.target.files;
            const fileInfo = document.getElementById('fileInfo');
            const fileList = document.getElementById('fileList');
            const totalSize = document.getElementById('totalSize');
            const uploadBtn = document.getElementById('uploadBtn');
            
            if (files.length > 0) {
                let totalSizeBytes = 0;
                let fileListHTML = '';
                
                // الحدود
                const maxFileSize = 20 * 1024 * 1024; // 20MB
                const maxTotalSize = 100 * 1024 * 1024; // 100MB
                const maxFiles = 50;
                
                // التحقق من عدد الملفات
                if (files.length > maxFiles) {
                    alert(`لا يمكن رفع أكثر من ${maxFiles} صورة مرة واحدة`);
                    this.value = '';
                    fileInfo.style.display = 'none';
                    uploadBtn.disabled = true;
                    return;
                }
                
                // معالجة كل ملف
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    totalSizeBytes += file.size;
                    
                    // التحقق من حجم الملف الفردي
                    if (file.size > maxFileSize) {
                        alert(`الملف ${file.name} كبير جداً (${(file.size / (1024 * 1024)).toFixed(2)}MB). الحد الأقصى 20MB`);
                        this.value = '';
                        fileInfo.style.display = 'none';
                        uploadBtn.disabled = true;
                        return;
                    }
                    
                    fileListHTML += `<div>${file.name} (${(file.size / 1024).toFixed(2)} KB)</div>`;
                }
                
                // التحقق من الحجم الإجمالي
                if (totalSizeBytes > maxTotalSize) {
                    alert(`الحجم الإجمالي للصور كبير جداً (${(totalSizeBytes / (1024 * 1024)).toFixed(2)}MB). الحد الأقصى 100MB`);
                    this.value = '';
                    fileInfo.style.display = 'none';
                    uploadBtn.disabled = true;
                    return;
                }
                
                // عرض المعلومات
                fileList.innerHTML = fileListHTML;
                totalSize.innerHTML = `<strong>الحجم الإجمالي:</strong> ${(totalSizeBytes / (1024 * 1024)).toFixed(2)} MB`;
                fileInfo.style.display = 'block';
                uploadBtn.disabled = false;
                
            } else {
                fileInfo.style.display = 'none';
                uploadBtn.disabled = true;
            }
        });

        // منع إرسال النموذج إذا كان هناك أخطاء
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            const files = document.getElementById('product_images').files;
            
            if (files.length === 0) {
                e.preventDefault();
                alert('يرجى اختيار ملفات للرفع');
                return;
            }
            
            // إظهار مؤشر التحميل
            const uploadBtn = document.getElementById('uploadBtn');
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الرفع...';
            uploadBtn.disabled = true;
        });
    </script>
<script>

                        document.getElementById('product_images').addEventListener('change', function(e) {
                            const files = e.target.files;
                            const fileInfo = document.getElementById('fileInfo');
                            const fileList = document.getElementById('fileList');
                            const totalSize = document.getElementById('totalSize');
                            const uploadBtn = document.getElementById('uploadBtn');
                            
                            if (files.length > 0) {
                                let totalSizeBytes = 0;
                                let fileListHTML = '';
                                
                                const maxFileSize = 20 * 1024 * 1024;
                                const maxTotalSize = 100 * 1024 * 1024;
                                const maxFiles = 50;
                                
                                if (files.length > maxFiles) {
                                    alert(`❌ لا يمكن رفع أكثر من ${maxFiles} صورة مرة واحدة`);
                                    this.value = '';
                                    fileInfo.style.display = 'none';
                                    return;
                                }
                                
                                for (let i = 0; i < files.length; i++) {
                                    const file = files[i];
                                    totalSizeBytes += file.size;
                                    
                                    if (file.size > maxFileSize) {
                                        alert(`❌ الملف ${file.name} كبير جداً (${(file.size / (1024 * 1024)).toFixed(2)}MB)`);
                                        this.value = '';
                                        fileInfo.style.display = 'none';
                                        return;
                                    }
                                    
                                    fileListHTML += `<div>✅ ${file.name} (${(file.size / 1024).toFixed(2)} KB)</div>`;
                                }
                                
                                if (totalSizeBytes > maxTotalSize) {
                                    alert(`❌ الحجم الإجمالي كبير جداً (${(totalSizeBytes / (1024 * 1024)).toFixed(2)}MB)`);
                                    this.value = '';
                                    fileInfo.style.display = 'none';
                                    return;
                                }
                                
                                fileList.innerHTML = fileListHTML;
                                totalSize.innerHTML = `<strong>الحجم الإجمالي:</strong> ${(totalSizeBytes / (1024 * 1024)).toFixed(2)} MB / ${(maxTotalSize / (1024 * 1024)).toFixed(0)} MB`;
                                fileInfo.style.display = 'block';
                                
                            } else {
                                fileInfo.style.display = 'none';
                            }
                        });

                        // إظهار مؤثر التحميل
                        document.getElementById('uploadForm').addEventListener('submit', function(e) {
                            const files = document.getElementById('product_images').files;
                            
                            if (files.length === 0) {
                                e.preventDefault();
                                alert('❌ يرجى اختيار ملفات للرفع');
                                return;
                            }
                            
                            const uploadBtn = document.getElementById('uploadBtn');
                            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الرفع...';
                            uploadBtn.disabled = true;
                        });
                        // تحديث معاينة CSV لتظهر الهيكل الصحيح
                function previewCSV(input) {
                    const preview = document.getElementById('csvPreview');
                    const previewBody = document.getElementById('previewBody');
                    const previewInfo = document.getElementById('previewInfo');
                    
                    if (input.files && input.files[0]) {
                        const file = input.files[0];
                        const reader = new FileReader();
                        
                        reader.onload = function(e) {
                            const content = e.target.result;
                            const lines = content.split('\n');
                            let tableHTML = '';
                            let validRows = 0;
                            
                            for (let i = 1; i < Math.min(lines.length, 6); i++) {
                                const line = lines[i].trim();
                                if (line) {
                                    const cells = parseCSVLine(line);
                                    if (cells.length >= 7) {
                                        tableHTML += `
                                            <tr>
                                                <td>${cells[0] || ''}</td>
                                                <td>${cells[1] || ''}</td>
                                                <td>${cells[2] || ''}</td>
                                                <td>${cells[3] || ''}</td>
                                                <td>${cells[4] || ''}</td>
                                                <td>${cells[5] || ''}</td>
                                                <td>${cells[6] || ''}</td>
                                                <td>${cells[7] || ''}</td>
                                            </tr>
                                        `;
                                        validRows++;
                                    }
                                }
                            }
                            
                            previewBody.innerHTML = tableHTML;
                            previewInfo.innerHTML = `
                                <i class="fas fa-info-circle"></i>
                                <strong>ملاحظة:</strong> الصور سيتم حفظها في جدول منفصل (product_images) وربطها مع المنتجات تلقائياً<br>
                                الملف: ${file.name} (${(file.size / 1024).toFixed(2)} KB) - الأسطر: ${lines.length - 1}
                            `;
                            preview.style.display = 'block';
                        };
                        
                        reader.readAsText(file, 'UTF-8');
                    } else {
                        preview.style.display = 'none';
                    }
                }
              
               
        // نصوص الترجمة
        const translations = {
            ar: {
                "import_export_products": "استيراد وتصدير المنتجات",
                "import_export_desc": "إدارة نقل البيانات من وإلى النظام",
                "export_products": "تصدير المنتجات",
                "import_products": "استيراد المنتجات",
                "import_instructions": "تعليمات الاستيراد",
                
                // تصدير المنتجات
                "export_to_csv": "تصدير إلى CSV",
                "export_description": "قم بتحميل جميع المنتجات في ملف CSV",
                "download_csv": "تحميل ملف CSV",
                "export_info": "معلومات التصدير:",
                "export_info_1": "سيتم تصدير جميع المنتجات",
                "export_info_2": "التنسيق: CSV (UTF-8)",
                "export_info_3": "الحقول: S_NO, Item_Code, Item_Name, Packing, Item_Group, Brand, featured,image_name",
                "image_name": "image_name",
                // استيراد المنتجات
                "upload_csv": "رفع ملف CSV",
                "choose_csv_file": "اختر ملف CSV",
                "import_products_btn": "استيراد المنتجات",
                "csv_template": "نموذج ملف CSV:",
                "download_template": "تحميل نموذج",
                "template_note": "تأكد من تطابق تنسيق الملف مع النموذج",
                
                // تعليمات الاستيراد
                "requirements": "المتطلبات:",
                "req_1": "يجب أن يكون الملف بصيغة CSV",
                "req_2": "التشفير: UTF-8",
                "req_3": "الفاصل: comma (,) أو semicolon (;)",
                "req_4": "الصف الأول يجب أن يحتوي على العناوين",
                "req_5": "الحقول المطلوبة: Item_Code, Item_Name",
                
                "notes": "ملاحظات:",
                "note_1": "المنتجات المكررة سيتم تحديثها",
                "note_2": "featured: نعم/لا أو 1/0",
                "note_3": "S_NO يمكن أن يكون فارغاً",
                "note_4": "احتفظ بنسخة احتياطية قبل الاستيراد",
                
                "file_structure": "هيكل الملف المطلوب:",
                "item_code": "Item_Code",
                "item_name": "Item_Name",
                "packing": "Packing",
                "item_group": "Item_Group",
                "brand": "Brand",
                "featured": "featured",
                "sample_product": "منتج مثال",
                "sample_product2": "منتج مثال 2",
                "sample_category": "حلويات",
                "sample_category2": "مخبوزات",
                "sample_brand": "علامة تجارية",
                "sample_brand2": "علامة تجارية 2",
                    "upload_images": "رفع الصور",
                    "upload_product_images": "رفع صور المنتجات",
                    "choose_images": "اختر صور المنتجات",
                    "images_note": "يمكنك اختيار عدة صور مرة واحدة (الحد الأقصى 20MB لكل صورة، 100MB إجمالي)",
                    "upload_images_btn": "رفع الصور",
                    "upload_instructions": "تعليمات الرفع:",
                    "upload_inst_1": "ارفع الصور أولاً قبل استيراد ملف CSV",
                    "upload_inst_2": "استخدم نفس أسماء الملفات في عمود image_name في ملف CSV",
                    "upload_inst_3": "المسموح: JPG, PNG, GIF, WebP, BMP, SVG,jpeg",
                    "upload_inst_4": "الحد الأقصى لحجم الصورة: 20MB",
                    "upload_inst_5": "الحد الأقصى الإجمالي: 100MB",
                    "upload_inst_6": "الحد الأقصى لعدد الملفات: 50 صورة",
                    "images_uploaded_success": "الصور المرفوعة جاهزة للاستيراد:",
                    "selected_files": "الملفات المختارة:",
                    "original_name": "الاسم الأصلي",
                    "saved_name": "الاسم المحفوظ",
                    "file_size": "الحجم",
                    "images_ready_note": "سيتم ربط هذه الصور تلقائياً مع المنتجات أثناء الاستيراد"
            },
            en: {
                "import_export_products": "Import & Export Products",
                "import_export_desc": "Manage data transfer to and from the system",
                "export_products": "Export Products",
                "import_products": "Import Products",
                "import_instructions": "Import Instructions",
                
                // تصدير المنتجات
                "export_to_csv": "Export to CSV",
                "export_description": "Download all products to a CSV file",
                "download_csv": "Download CSV File",
                "export_info": "Export Information:",
                "export_info_1": "All products will be exported",
                "export_info_2": "Format: CSV (UTF-8)",
                "export_info_3": "Fields: S_NO, Item_Code, Item_Name, Packing, Item_Group, Brand, featured,image_name",
                 "image_name": "image_name",
                // استيراد المنتجات
                "upload_csv": "Upload CSV File",
                "choose_csv_file": "Choose CSV File",
                "import_products_btn": "Import Products",
                "csv_template": "CSV Template:",
                "download_template": "Download Template",
                "template_note": "Make sure the file format matches the template",
                
                // تعليمات الاستيراد
                "requirements": "Requirements:",
                "req_1": "File must be in CSV format",
                "req_2": "Encoding: UTF-8",
                "req_3": "Separator: comma (,) or semicolon (;)",
                "req_4": "First row must contain headers",
                "req_5": "Required fields: Item_Code, Item_Name",
                
                "notes": "Notes:",
                "note_1": "Duplicate products will be updated",
                "note_2": "featured: yes/no or 1/0",
                "note_3": "S_NO can be empty",
                "note_4": "Keep a backup before importing",
                
                "file_structure": "Required File Structure:",
                "item_code": "Item_Code",
                "item_name": "Item_Name",
                "packing": "Packing",
                "item_group": "Item_Group",
                "brand": "Brand",
                "featured": "featured",
                "sample_product": "Sample Product",
                "sample_product2": "Sample Product 2",
                "sample_category": "Sweets",
                "sample_category2": "Bakery",
                "sample_brand": "Brand Name",
                "sample_brand2": "Brand Name 2",
                       "upload_images": "Upload Images",
                "upload_product_images": "Upload Product Images",
                "choose_images": "Choose Product Images",
                "images_note": "You can select multiple images at once (Max 20MB per image, 100MB total)",
                "upload_images_btn": "Upload Images",
                "upload_instructions": "Upload Instructions:",
                "upload_inst_1": "Upload images first before importing CSV file",
                "upload_inst_2": "Use the same file names in image_name column in CSV file",
                "upload_inst_3": "Allowed: JPG, PNG, GIF, WebP, BMP, SVG,jpeg",
                "upload_inst_4": "Maximum image size: 20MB",
                "upload_inst_5": "Maximum total size: 100MB",
                "upload_inst_6": "Maximum files: 50 images",
                "images_uploaded_success": "Uploaded images ready for import:",
                "selected_files": "Selected Files:",
                "original_name": "Original Name",
                "saved_name": "Saved Name",
                "file_size": "File Size",
                "images_ready_note": "These images will be automatically linked with products during import"
   
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
                document.title = 'استيراد وتصدير المنتجات';
            } else {
                document.documentElement.dir = 'ltr';
                document.documentElement.lang = 'en';
                document.title = 'Import & Export Products';
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

        // تطبيق اللغة عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            applyLanguage(currentLang);
        });
    </script>
</body>
</html>