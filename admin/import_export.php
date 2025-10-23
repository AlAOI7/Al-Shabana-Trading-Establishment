<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}


$success = '';
$error = '';

// معالجة تصدير المنتجات
if (isset($_POST['export_products'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=products_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Name', 'Description', 'Price', 'Category', 'Stock', 'Image Path']);
    
    $stmt = $pdo->query("SELECT * FROM products");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

// معالجة استيراد المنتجات
if (isset($_POST['import_products']) && isset($_FILES['csv_file'])) {
    if ($_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        
        // تخطي الصف الأول (العناوين)
        fgetcsv($handle);
        
        $imported = 0;
        $errors = [];
        
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            if (count($data) >= 6) {
                $name = $data[1] ?? '';
                $description = $data[2] ?? '';
                $price = floatval($data[3] ?? 0);
                $category = $data[4] ?? '';
                $stock = intval($data[5] ?? 0);
                $image_path = $data[6] ?? '';
                
                if (!empty($name)) {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category, stock_quantity, image_path) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$name, $description, $price, $category, $stock, $image_path]);
                        $imported++;
                    } catch (PDOException $e) {
                        $errors[] = "خطأ في سطر: " . implode(',', $data);
                    }
                }
            }
        }
        fclose($handle);
        
        if ($imported > 0) {
            $success = "تم استيراد $imported منتج بنجاح";
        }
        if (!empty($errors)) {
            $error = "حدثت أخطاء في بعض السجلات: " . implode(', ', $errors);
        }
    } else {
        $error = "حدث خطأ في رفع الملف";
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
                                    <li data-translate="export_info_3">الحقول: الاسم، الوصف، السعر، الفئة، المخزون</li>
                                </ul>
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
                                    <a href="sample_products.csv" class="btn btn-outline" style="padding: 0.5rem 1rem;">
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
                                <li data-translate="req_3">الفاصل: comma (,)</li>
                                <li data-translate="req_4">الصف الأول يجب أن يحتوي على العناوين</li>
                                <li data-translate="req_5">الحقول المطلوبة: الاسم، السعر، الفئة</li>
                            </ul>
                        </div>
                        <div class="col-6">
                            <h4><i class="fas fa-exclamation-triangle" style="color: var(--warning);"></i> <span data-translate="notes">ملاحظات:</span></h4>
                            <ul style="text-align: right; margin-right: 1rem;">
                                <li data-translate="note_1">المنتجات المكررة سيتم تجاهلها</li>
                                <li data-translate="note_2">السعر يجب أن يكون رقماً</li>
                                <li data-translate="note_3">الكمية في المخزن رقماً صحيحاً</li>
                                <li data-translate="note_4">احتفظ بنسخة احتياطية قبل الاستيراد</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- نموذج جدول -->
                    <div style="margin-top: 2rem;">
                        <h4 data-translate="file_structure">هيكل الملف المطلوب:</h4>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th data-translate="name">Name</th>
                                        <th data-translate="description">Description</th>
                                        <th data-translate="price">Price</th>
                                        <th data-translate="category">Category</th>
                                        <th data-translate="stock">Stock</th>
                                        <th data-translate="image_path">Image Path</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td data-translate="auto">(تلقائي)</td>
                                        <td data-translate="sample_product">منتج مثال</td>
                                        <td data-translate="sample_description">وصف المنتج</td>
                                        <td>100.00</td>
                                        <td data-translate="sample_category">فئة المثال</td>
                                        <td>50</td>
                                        <td>uploads/products/image.jpg</td>
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
        // نصوص الترجمة
        const translations = {
            ar: {
                    "dashboard": "لوحة التحكم الرئيسية",
                                "welcome": "مرحباً،",
                "home": "الرئيسية",
                "user_management": "إدارة المستخدمين",
                "product_management": "إدارة المنتجات",
                "service_management": "إدارة الخدمات",
                "order_management": "إدارة الطلبات",
                "about_us": "من نحن",
                "contact_info": "بيانات التواصل",
                "settings": "الإعدادات",
                "logout": "تسجيل الخروج",
                "profile": "الملف الشخصي",

                // العناوين الرئيسية
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
                "export_info_3": "الحقول: الاسم، الوصف، السعر، الفئة، المخزون",
                
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
                "req_3": "الفاصل: comma (,)",
                "req_4": "الصف الأول يجب أن يحتوي على العناوين",
                "req_5": "الحقول المطلوبة: الاسم، السعر، الفئة",
                
                "notes": "ملاحظات:",
                "note_1": "المنتجات المكررة سيتم تجاهلها",
                "note_2": "السعر يجب أن يكون رقماً",
                "note_3": "الكمية في المخزن رقماً صحيحاً",
                "note_4": "احتفظ بنسخة احتياطية قبل الاستيراد",
                
                "file_structure": "هيكل الملف المطلوب:",
                "name": "Name",
                "description": "Description",
                "price": "Price",
                "category": "Category",
                "stock": "Stock",
                "image_path": "Image Path",
                "auto": "(تلقائي)",
                "sample_product": "منتج مثال",
                "sample_description": "وصف المنتج",
                "sample_category": "فئة المثال"
            },
            en: {
                  "dashboard": "Main Dashboard",
                     "welcome": "Welcome,",
                    "home": "Home",
                    "user_management": "User Management",
                    "product_management": "Product Management",
                    "service_management": "Service Management",
                    "order_management": "Order Management",
                    "about_us": "About Us",
                    "contact_info": "Contact Info",
                    "settings": "Settings",
                    "logout": "Logout",
                    "profile": "Profile",
                // العناوين الرئيسية
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
                "export_info_3": "Fields: Name, Description, Price, Category, Stock",
                
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
                "req_3": "Separator: comma (,)",
                "req_4": "First row must contain headers",
                "req_5": "Required fields: Name, Price, Category",
                
                "notes": "Notes:",
                "note_1": "Duplicate products will be ignored",
                "note_2": "Price must be a number",
                "note_3": "Stock quantity must be an integer",
                "note_4": "Keep a backup before importing",
                
                "file_structure": "Required File Structure:",
                "name": "Name",
                "description": "Description",
                "price": "Price",
                "category": "Category",
                "stock": "Stock",
                "image_path": "Image Path",
                "auto": "(Auto)",
                "sample_product": "Sample Product",
                "sample_description": "Product Description",
                "sample_category": "Sample Category"
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