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
    <title>استيراد وتصدير المنتجات</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="header">
                <h1><i class="fas fa-file-import"></i> استيراد وتصدير المنتجات</h1>
                <p>إدارة نقل البيانات من وإلى النظام</p>
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
                            <h3><i class="fas fa-file-export"></i> تصدير المنتجات</h3>
                        </div>
                        <div class="card-body">
                            <div style="text-align: center; padding: 2rem;">
                                <div style="font-size: 3rem; color: var(--success-color); margin-bottom: 1rem;">
                                    <i class="fas fa-file-csv"></i>
                                </div>
                                <h4>تصدير إلى CSV</h4>
                                <p style="color: #666; margin-bottom: 2rem;">قم بتحميل جميع المنتجات في ملف CSV</p>
                                
                                <form method="POST">
                                    <button type="submit" name="export_products" class="btn btn-success">
                                        <i class="fas fa-download"></i> تحميل ملف CSV
                                    </button>
                                </form>
                            </div>
                            
                            <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px; margin-top: 1rem;">
                                <h5><i class="fas fa-info-circle"></i> معلومات التصدير:</h5>
                                <ul style="text-align: right; margin-right: 1rem;">
                                    <li>سيتم تصدير جميع المنتجات</li>
                                    <li>التنسيق: CSV (UTF-8)</li>
                                    <li>الحقول: الاسم، الوصف، السعر، الفئة، المخزون</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- استيراد المنتجات -->
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-file-import"></i> استيراد المنتجات</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div style="text-align: center; padding: 1rem;">
                                    <div style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;">
                                        <i class="fas fa-upload"></i>
                                    </div>
                                    <h4>رفع ملف CSV</h4>
                                    
                                    <div class="form-group">
                                        <label for="csv_file">اختر ملف CSV</label>
                                        <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                                    </div>
                                    
                                    <button type="submit" name="import_products" class="btn btn-primary">
                                        <i class="fas fa-upload"></i> استيراد المنتجات
                                    </button>
                                </div>
                            </form>
                            
                            <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px; margin-top: 1rem;">
                                <h5><i class="fas fa-download"></i> نموذج ملف CSV:</h5>
                                <p style="text-align: center; margin: 1rem 0;">
                                    <a href="sample_products.csv" class="btn btn-outline" style="border: 1px solid #ddd; padding: 0.5rem 1rem;">
                                        <i class="fas fa-file-download"></i> تحميل نموذج
                                    </a>
                                </p>
                                <small style="color: #666;">تأكد من تطابق تنسيق الملف مع النموذج</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- تعليمات الاستيراد -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-graduation-cap"></i> تعليمات الاستيراد</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <h4><i class="fas fa-check-circle" style="color: var(--success-color);"></i> المتطلبات:</h4>
                            <ul style="text-align: right; margin-right: 1rem;">
                                <li>يجب أن يكون الملف بصيغة CSV</li>
                                <li>التشفير: UTF-8</li>
                                <li>الفاصل: comma (,)</li>
                                <li>الصف الأول يجب أن يحتوي على العناوين</li>
                                <li>الحقول المطلوبة: الاسم، السعر، الفئة</li>
                            </ul>
                        </div>
                        <div class="col-6">
                            <h4><i class="fas fa-exclamation-triangle" style="color: var(--warning-color);"></i> ملاحظات:</h4>
                            <ul style="text-align: right; margin-right: 1rem;">
                                <li>المنتجات المكررة سيتم تجاهلها</li>
                                <li>السعر يجب أن يكون رقماً</li>
                                <li>الكمية في المخزن رقماً صحيحاً</li>
                                <li>احتفظ بنسخة احتياطية قبل الاستيراد</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- نموذج جدول -->
                    <div style="margin-top: 2rem;">
                        <h4>هيكل الملف المطلوب:</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Price</th>
                                        <th>Category</th>
                                        <th>Stock</th>
                                        <th>Image Path</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>(تلقائي)</td>
                                        <td>منتج مثال</td>
                                        <td>وصف المنتج</td>
                                        <td>100.00</td>
                                        <td>فئة المثال</td>
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
</body>
</html>