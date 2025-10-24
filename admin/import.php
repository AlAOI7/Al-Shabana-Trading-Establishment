<?php
require_once '../config.php';

// التحقق من صلاحية المدير
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// دالة إنشاء قالب CSV منظم
function generateTemplate() {
    $template_data = [
        // العناوين الرئيسية (يجب أن تكون باللغة الإنجليزية فقط)
        ['S_NO', 'Item_Code', 'Item_Name', 'Packing', 'Item_Group', 'Brand', 'Featured', 'Image_Name'],
        
        // أمثلة توضيحية للبيانات
        ['1', 'PROD001', 'شوكولاتة حليب', '100 جرام', 'حلويات', 'شوكولاتا بارك', 'نعم', 'chocolate.jpg'],
        ['2', 'PROD002', 'بسكويت شوكولاتة', '150 جرام', 'مخبوزات', 'بسكويتا', 'لا', 'biscuit.jpg'],
        ['3', 'PROD003', 'عصير برتقال', '1 لتر', 'مشروبات', 'عصائر طبيعية', 'نعم', 'juice.jpg'],
        ['4', 'PROD004', 'معجون أسنان', '75 مل', 'العناية الشخصية', 'سنان', 'لا', 'toothpaste.jpg'],
        ['5', 'PROD005', 'أرز بسمتي', '5 كجم', 'أطعمة', 'أرز الذهب', 'نعم', 'rice.jpg']
    ];
    
    return $template_data;
}

// دالة لتحميل القالب
if (isset($_GET['download_template'])) {
    $template_data = generateTemplate();
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=products_template.csv');
    header('Cache-Control: max-age=0');
    
    $output = fopen('php://output', 'w');
    
    // إضافة BOM للتعرف على الترميز UTF-8 في Excel
    fputs($output, "\xEF\xBB\xBF");
    
    foreach ($template_data as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

// معالجة رفع الصور المسبق
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_images'])) {
    uploadImagesBeforeImport($pdo);
}

// معالجة استيراد CSV
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['import_excel'])) {
    importFromCSV($pdo);
}

// دالة رفع الصور المسبق
function uploadImagesBeforeImport($pdo) {
    $uploadDir = '../uploads/temp_uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $uploaded_files = [];
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (!empty($_FILES['pre_upload_images']['name'][0])) {
        foreach ($_FILES['pre_upload_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['pre_upload_images']['error'][$key] === 0) {
                $original_name = $_FILES['pre_upload_images']['name'][$key];
                $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                
                // التحقق من نوع الملف
                if (!in_array($file_extension, $allowed_types)) {
                    continue;
                }
                
                // تنظيف اسم الملف
                $file_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $original_name);
                $file_path = $uploadDir . $file_name;
                
                if (move_uploaded_file($tmp_name, $file_path)) {
                    $uploaded_files[] = $file_name;
                }
            }
        }
        
        if (!empty($uploaded_files)) {
            $_SESSION['message'] = "تم رفع " . count($uploaded_files) . " صورة بنجاح! يمكنك الآن استيراد ملف CSV.";
            $_SESSION['uploaded_images'] = $uploaded_files;
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = "فشل في رفع الصور! تأكد من أنواع الملفات المسموحة (JPG, PNG, GIF, WEBP)";
            $_SESSION['message_type'] = 'error';
        }
    } else {
        $_SESSION['message'] = "يرجى اختيار صور لرفعها!";
        $_SESSION['message_type'] = 'error';
    }
    header("Location: import.php");
    exit();
}

// دالة استيراد من CSV مع مطابقة الصور
function importFromCSV($pdo) {
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] != 0) {
        $_SESSION['message'] = "يرجى اختيار ملف CSV صحيح";
        $_SESSION['message_type'] = 'error';
        header("Location: import.php");
        exit();
    }
    
    $uploadDir = '../uploads/products/';
    $tempDir = '../uploads/temp_uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $file_name = $_FILES['csv_file']['name'];
    $file_tmp = $_FILES['csv_file']['tmp_name'];
    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // التحقق من أن الملف CSV
    if ($file_extension !== 'csv') {
        $_SESSION['message'] = "يرجى اختيار ملف CSV فقط";
        $_SESSION['message_type'] = 'error';
        header("Location: import.php");
        exit();
    }
    
    try {
        // فتح ملف CSV
        $handle = fopen($file_tmp, 'r');
        if (!$handle) {
            throw new Exception("لا يمكن فتح الملف");
        }
        
        // قراءة العناوين والتحقق منها
        $headers = fgetcsv($handle);
        $expected_headers = ['S_NO', 'Item_Code', 'Item_Name', 'Packing', 'Item_Group', 'Brand', 'Featured', 'Image_Name'];
        
        // التحقق من صحة العناوين
        if ($headers !== $expected_headers) {
            fclose($handle);
            throw new Exception("ترتيب الأعمدة غير صحيح. يرجى استخدام القالب المرفق.");
        }
        
        $imported = 0;
        $updated = 0;
        $errors = 0;
        $products_map = [];
        $uploaded_images = $_SESSION['uploaded_images'] ?? [];
        
        // قراءة البيانات من CSV
        while (($row = fgetcsv($handle)) !== FALSE) {
            // تخطي الصفوف الفارغة
            if (empty($row[0]) || count($row) < 2) continue;
            
            // تنظيف البيانات
            $S_NO = trim($row[0]);
            $Item_Code = trim($row[1]);
            $Item_Name = trim($row[2] ?? '');
            $Packing = trim($row[3] ?? '');
            $Item_Group = trim($row[4] ?? '');
            $Brand = trim($row[5] ?? '');
            $featured = isset($row[6]) ? (strtolower(trim($row[6])) == 'نعم' || trim($row[6]) == '1' ? 1 : 0) : 0;
            $image_name = trim($row[7] ?? '');
            
            // التحقق من البيانات الأساسية
            if (empty($S_NO) || empty($Item_Code)) {
                $errors++;
                continue;
            }
            
            // إنشاء مفتاح فريد للمنتج
            $product_key = $S_NO . '_' . $Item_Code;
            
            if (!isset($products_map[$product_key])) {
                // التحقق من وجود المنتج مسبقاً
                $stmt = $pdo->prepare("SELECT id FROM products WHERE S_NO = ? AND Item_Code = ?");
                $stmt->execute([$S_NO, $Item_Code]);
                $existing_product = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existing_product) {
                    // تحديث البيانات الحالية
                    $stmt = $pdo->prepare("UPDATE products SET Item_Name = ?, Packing = ?, Item_Group = ?, Brand = ?, featured = ? WHERE id = ?");
                    $stmt->execute([$Item_Name, $Packing, $Item_Group, $Brand, $featured, $existing_product['id']]);
                    $product_id = $existing_product['id'];
                    $updated++;
                } else {
                    // إضافة المنتج الجديد
                    $stmt = $pdo->prepare("INSERT INTO products (S_NO, Item_Code, Item_Name, Packing, Item_Group, Brand, featured) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$S_NO, $Item_Code, $Item_Name, $Packing, $Item_Group, $Brand, $featured]);
                    $product_id = $pdo->lastInsertId();
                    $imported++;
                }
                
                $products_map[$product_key] = $product_id;
                
                // معالجة الصور
                if (!empty($image_name)) {
                    processProductImage($pdo, $product_id, $image_name, $tempDir, $uploadDir);
                }
            }
        }
        
        fclose($handle);
        
        // تنظيف المجلد المؤقت بعد الاستيراد
        cleanupTempDirectory($tempDir);
        
        if (!empty($_SESSION['uploaded_images'])) {
            unset($_SESSION['uploaded_images']);
        }
        
        $_SESSION['message'] = "تم استيراد $imported منتج جديد وتحديث $updated منتج بنجاح!";
        $_SESSION['message_type'] = 'success';
        
    } catch (Exception $e) {
        $_SESSION['message'] = "حدث خطأ أثناء استيراد الملف: " . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }
    
    header("Location: import.php");
    exit();
}

// دالة معالجة صور المنتج
function processProductImage($pdo, $product_id, $image_name, $tempDir, $uploadDir) {
    $temp_image_path = $tempDir . $image_name;
    $final_image_path = $uploadDir . $image_name;
    
    // البحث عن الصورة في المجلد المؤقت
    if (file_exists($temp_image_path)) {
        // نسخ الصورة إلى المجلد النهائي
        if (copy($temp_image_path, $final_image_path)) {
            // التحقق من عدم وجود الصورة مسبقاً
            $stmt = $pdo->prepare("SELECT id FROM product_images WHERE product_id = ? AND image_name = ?");
            $stmt->execute([$product_id, $image_name]);
            
            if ($stmt->rowCount() == 0) {
                // تحديد إذا كانت هذه الصورة الأولى
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM product_images WHERE product_id = ?");
                $stmt->execute([$product_id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $is_primary = ($result['count'] == 0) ? 1 : 0;
                
                $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_name, is_primary) VALUES (?, ?, ?)");
                if ($stmt->execute([$product_id, $image_name, $is_primary])) {
                    // حذف الصورة من المجلد المؤقت بعد نسخها بنجاح
                    unlink($temp_image_path);
                    return true;
                }
            }
        }
    }
    return false;
}

// دالة تنظيف المجلد المؤقت
function cleanupTempDirectory($tempDir) {
    if (is_dir($tempDir)) {
        $files = glob($tempDir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>استيراد المنتجات</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        
        .main-content {
            padding: 20px;
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .header-content {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        h1 {
            font-size: 2.5rem;
            color: var(--secondary);
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
        
        .btn-info {
            background-color: var(--info);
        }
        
        .btn-warning {
            background-color: var(--warning);
        }
        
        .btn-danger {
            background-color: var(--danger);
        }
        
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            color: white;
            padding: 20px 30px;
            font-size: 1.4rem;
            font-weight: 600;
        }
        
        .card-body {
            padding: 30px;
        }
        
        .steps {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .step {
            flex: 1;
            min-width: 250px;
            background: var(--light);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .step:hover {
            background: var(--primary);
            color: white;
        }
        
        .step-number {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            line-height: 40px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .step:hover .step-number {
            background: white;
            color: var(--primary);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--dark);
            font-size: 1.1rem;
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
        
        .image-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 15px;
        }
        
        .preview-item {
            position: relative;
            width: 120px;
            height: 120px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }
        
        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .preview-item .remove {
            position: absolute;
            top: 5px;
            right: 5px;
            background: var(--danger);
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
        }
        
        .instructions {
            background: #f8f9fa;
            border-left: 5px solid var(--info);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        
        .instructions h3 {
            color: var(--info);
            margin-bottom: 15px;
        }
        
        .instructions ul {
            padding-right: 20px;
        }
        
        .instructions li {
            margin-bottom: 10px;
            line-height: 1.6;
        }
        
        .template-download {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        
        .template-download h3 {
            margin-bottom: 15px;
        }
        
        .uploaded-images-list {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .uploaded-images-list h4 {
            color: var(--success);
            margin-bottom: 10px;
        }
        
        .uploaded-images-list ul {
            list-style-type: none;
        }
        
        .uploaded-images-list li {
            padding: 5px 0;
            border-bottom: 1px solid #c8e6c9;
        }
        
        .message {
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 10px;
            font-weight: 500;
            text-align: center;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .message.info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .section-title {
            font-size: 1.5rem;
            color: var(--secondary);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light);
        }
        
        @media (max-width: 768px) {
            .steps {
                flex-direction: column;
            }
            
            .header-content {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- <?php include 'sidebar.php'; ?> -->
        
        <!-- المحتوى الرئيسي -->
        <main class="main-content">
            <?php include 'admin_navbar.php'; ?>
            
            <div class="container">
                <div class="header-content">
                    <div>
                        <h1><i class="fas fa-file-import"></i> استيراد المنتجات</h1>
                        <p>منصة شاملة لاستيراد المنتجات والصور من ملف Excel</p>
                    </div>
                    <div>
                        <a href="products.php" class="btn btn-warning">
                            <i class="fas fa-arrow-right"></i> العودة للمنتجات
                        </a>
                    </div>
                </div>
                
                <?php if(isset($_SESSION['message'])): ?>
                    <div class="message <?php echo strpos($_SESSION['message'], 'نجاح') !== false ? 'success' : (strpos($_SESSION['message'], 'خطأ') !== false ? 'error' : 'info'); ?>">
                        <?php 
                        echo $_SESSION['message']; 
                        unset($_SESSION['message']);
                        ?>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-info-circle"></i> تعليمات الاستيراد
                    </div>
                    <div class="card-body">
                        <div class="instructions">
                            <h3><i class="fas fa-lightbulb"></i> كيف تعمل عملية الاستيراد؟</h3>
                            <ul>
                                <li>قم أولاً بتحميل قالب Excel واضبط بيانات المنتجات والصور فيه</li>
                                <li>ارفع الصور التي ستستخدمها في المنتجات باستخدام الزر المخصص أدناه</li>
                                <li>استخدم <strong>نفس أسماء الصور</strong> في ملف Excel التي قمت بتحميلها</li>
                                <li>أخيراً قم برفع ملف Excel ليتم استيراد البيانات والصور تلقائياً</li>
                                <li>سيتم مطابقة أسماء الصور في Excel مع الصور التي قمت بتحميلها مسبقاً</li>
                            </ul>
                        </div>
                        
                        <div class="steps">
                            <div class="step">
                                <div class="step-number">1</div>
                                <h3>تحميل القالب</h3>
                                <p>قم بتحميل قالب Excel واضبط بيانات المنتجات</p>
                            </div>
                            <div class="step">
                                <div class="step-number">2</div>
                                <h3>رفع الصور</h3>
                                <p>ارفع جميع الصور التي ستستخدمها في المنتجات</p>
                            </div>
                            <div class="step">
                                <div class="step-number">3</div>
                                <h3>استيراد البيانات</h3>
                                <p>ارفع ملف Excel ليتم استيراد البيانات تلقائياً</p>
                            </div>
                        </div>
                        
                      <div class="template-download">
                            <h3><i class="fas fa-download"></i> قالب Excel الجاهز</h3>
                            <p>قم بتحميل قالب Excel مسبق الإعداد لضمان التنسيق الصحيح</p>
                            <a href="import.php?download_template=1" class="btn btn-success" style="margin-top: 15px;">
                                <i class="fas fa-file-excel"></i> تحميل القالب
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-images"></i> الخطوة 1: رفع الصور مسبقاً
                    </div>
                    <div class="card-body">
                        <form action="" method="POST" enctype="multipart/form-data" id="uploadImagesForm">
                            <div class="form-group">
                                <label for="pre_upload_images"><i class="fas fa-upload"></i> اختيار الصور (يمكن اختيار أكثر من صورة)</label>
                                <input type="file" id="pre_upload_images" name="pre_upload_images[]" multiple accept="image/*">
                                <p class="help-text" style="margin-top: 10px; color: #666;">
                                    <i class="fas fa-info-circle"></i> سيتم حفظ هذه الصور مؤقتاً حتى تقوم باستيراد ملف Excel
                                </p>
                            </div>
                            
                            <div class="image-preview" id="imagePreview"></div>
                            
                            <?php if(isset($_SESSION['uploaded_images']) && !empty($_SESSION['uploaded_images'])): ?>
                                <div class="uploaded-images-list">
                                    <h4><i class="fas fa-check-circle"></i> الصور التي تم رفعها:</h4>
                                    <ul>
                                        <?php foreach($_SESSION['uploaded_images'] as $image): ?>
                                            <li><i class="fas fa-image"></i> <?php echo $image; ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <button type="submit" class="btn btn-info" name="upload_images">
                                <i class="fas fa-cloud-upload-alt"></i> رفع الصور
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-file-excel"></i> الخطوة 2: استيراد ملف Excel
                    </div>
                    <div class="card-body">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="csv_file"><i class="fas fa-file-import"></i> اختيار ملف CSV</label>
                                <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
                                <p class="help-text" style="margin-top: 10px; color: #666;">
                                    <i class="fas fa-exclamation-triangle"></i> تأكد من أن أسماء الصور في ملف CSV تطابق الصور التي قمت برفعها مسبقاً
                                </p>
                            </div>
                            
                            <button type="submit" class="btn btn-success" name="import_excel">
                                <i class="fas fa-file-import"></i> بدء الاستيراد
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // معاينة الصور قبل الرفع
        document.getElementById('pre_upload_images').addEventListener('change', function(e) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            
            for (let i = 0; i < this.files.length; i++) {
                const file = this.files[i];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'preview-item';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = file.name;
                    
                    const removeBtn = document.createElement('button');
                    removeBtn.className = 'remove';
                    removeBtn.innerHTML = '×';
                    removeBtn.type = 'button';
                    removeBtn.onclick = function() {
                        previewItem.remove();
                    };
                    
                    previewItem.appendChild(img);
                    previewItem.appendChild(removeBtn);
                    preview.appendChild(previewItem);
                };
                
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>