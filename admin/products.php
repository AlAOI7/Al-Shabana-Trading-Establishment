<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
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
        
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image_path, category, stock_quantity) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $image_path, $category, $stock]);
        
        header("Location: products.php?success=1");
        exit();
    }
}

// جلب جميع المنتجات
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المنتجات</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="header">
                <h1>إدارة المنتجات</h1>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div style="background: #dfd; color: #363; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                    تمت العملية بنجاح
                </div>
            <?php endif; ?>

            <!-- نموذج إضافة منتج -->
            <div class="card">
                <div class="card-header">
                    <h3>إضافة منتج جديد</h3>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="name">اسم المنتج</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="price">السعر</label>
                                    <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="category">الفئة</label>
                                    <input type="text" class="form-control" id="category" name="category" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="stock_quantity">الكمية في المخزن</label>
                                    <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">وصف المنتج</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="image">صورة المنتج</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                        
                        <button type="submit" name="add_product" class="btn btn-primary">إضافة المنتج</button>
                    </form>
                </div>
            </div>

            <!-- قائمة المنتجات -->
            <div class="card">
                <div class="card-header">
                    <h3>قائمة المنتجات</h3>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>الصورة</th>
                                <th>اسم المنتج</th>
                                <th>السعر</th>
                                <th>الفئة</th>
                                <th>المخزن</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <?php if ($product['image_path']): ?>
                                        <img src="../<?php echo $product['image_path']; ?>" alt="<?php echo $product['name']; ?>" style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php else: ?>
                                        <div style="width: 50px; height: 50px; background: #eee; display: flex; align-items: center; justify-content: center;">
                                            لا توجد صورة
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo number_format($product['price'], 2); ?> ر.س</td>
                                <td><?php echo htmlspecialchars($product['category']); ?></td>
                                <td><?php echo $product['stock_quantity']; ?></td>
                                <td>
                                    <a href="#" class="btn btn-primary">تعديل</a>
                                    <a href="#" class="btn btn-danger">حذف</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>