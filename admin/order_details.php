<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}


if (!isset($_GET['id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = $_GET['id'];

// جلب بيانات الطلب
$stmt = $pdo->prepare("
    SELECT o.*, u.username, u.full_name, u.email, u.phone 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: orders.php");
    exit();
}

// جلب عناصر الطلب
$stmt = $pdo->prepare("
    SELECT oi.*, p.name as product_name, p.image_path 
    FROM order_items oi 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل الطلب #<?php echo $order_id; ?></title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="header">
                <h1><i class="fas fa-file-invoice"></i> تفاصيل الطلب #<?php echo $order_id; ?></h1>
                <p>عرض التفاصيل الكاملة للطلب</p>
            </div>

            <div class="row">
                <!-- معلومات العميل -->
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-user"></i> معلومات العميل</h3>
                        </div>
                        <div class="card-body">
                            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 1rem;">
                                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; font-weight: bold;">
                                    <?php echo strtoupper(substr($order['full_name'] ?: $order['username'], 0, 1)); ?>
                                </div>
                                <div>
                                    <h4 style="margin: 0;"><?php echo htmlspecialchars($order['full_name'] ?: $order['username']); ?></h4>
                                    <p style="margin: 0; color: #666;">@<?php echo $order['username']; ?></p>
                                </div>
                            </div>
                            
                            <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px;">
                                <p><strong><i class="fas fa-envelope"></i> البريد الإلكتروني:</strong><br>
                                <?php echo $order['email']; ?></p>
                                
                                <?php if ($order['phone']): ?>
                                <p><strong><i class="fas fa-phone"></i> الهاتف:</strong><br>
                                <?php echo $order['phone']; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- معلومات الطلب -->
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-info-circle"></i> معلومات الطلب</h3>
                        </div>
                        <div class="card-body">
                            <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px;">
                                <p><strong><i class="fas fa-hashtag"></i> رقم الطلب:</strong> #<?php echo $order['id']; ?></p>
                                <p><strong><i class="fas fa-calendar"></i> تاريخ الطلب:</strong> <?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></p>
                                <p><strong><i class="fas fa-money-bill-wave"></i> المبلغ الإجمالي:</strong> 
                                    <span style="font-size: 1.2rem; font-weight: bold; color: var(--success-color);">
                                        <?php echo number_format($order['total_amount'], 2); ?> ر.س
                                    </span>
                                </p>
                                <p><strong><i class="fas fa-tag"></i> الحالة:</strong> 
                                    <span style="padding: 5px 10px; border-radius: 15px; font-size: 0.8rem; font-weight: bold; 
                                          background: <?php 
                                          switch($order['status']) {
                                              case 'pending': echo '#fff3cd'; break;
                                              case 'processing': echo '#d1ecf1'; break;
                                              case 'completed': echo '#d4edda'; break;
                                              case 'cancelled': echo '#f8d7da'; break;
                                          } ?>;">
                                        <?php 
                                        switch($order['status']) {
                                            case 'pending': echo 'قيد الانتظار'; break;
                                            case 'processing': echo 'قيد المعالجة'; break;
                                            case 'completed': echo 'مكتمل'; break;
                                            case 'cancelled': echo 'ملغي'; break;
                                        }
                                        ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- عناصر الطلب -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-boxes"></i> عناصر الطلب</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($order_items)): ?>
                        <div style="text-align: center; padding: 2rem; color: #666;">
                            <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>لا توجد عناصر في هذا الطلب</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>المنتج</th>
                                        <th>السعر</th>
                                        <th>الكمية</th>
                                        <th>المجموع</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_items as $index => $item): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <?php if ($item['image_path']): ?>
                                                    <img src="../<?php echo $item['image_path']; ?>" alt="<?php echo $item['product_name']; ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 5px;">
                                                <?php else: ?>
                                                    <div style="width: 40px; height: 40px; background: #eee; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-box" style="color: #999;"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo number_format($item['price'], 2); ?> ر.س</td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><strong><?php echo number_format($item['price'] * $item['quantity'], 2); ?> ر.س</strong></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr style="background: #f8f9fa;">
                                        <td colspan="4" style="text-align: left; font-weight: bold;">المجموع الإجمالي:</td>
                                        <td style="font-weight: bold; font-size: 1.1rem; color: var(--success-color);">
                                            <?php echo number_format($order['total_amount'], 2); ?> ر.س
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- عنوان الشحن -->
            <?php if ($order['shipping_address']): ?>
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-truck"></i> عنوان الشحن</h3>
                </div>
                <div class="card-body">
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px;">
                        <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- الملاحظات -->
            <?php if ($order['notes']): ?>
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-sticky-note"></i> ملاحظات الطلب</h3>
                </div>
                <div class="card-body">
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px;">
                        <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- أزرار الإجراءات -->
            <div style="display: flex; gap: 10px; margin-top: 2rem;">
                <a href="orders.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-right"></i> العودة إلى الطلبات
                </a>
                <a href="print_order.php?id=<?php echo $order_id; ?>" target="_blank" class="btn btn-primary">
                    <i class="fas fa-print"></i> طباعة الطلب
                </a>
            </div>
        </main>
    </div>
</body>
</html>