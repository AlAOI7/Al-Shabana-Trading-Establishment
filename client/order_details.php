<?php
require_once '../config.php';
requireClient();

if (!isset($_GET['id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = $_GET['id'];

// جلب بيانات الطلب مع التحقق من ملكية المستخدم
$stmt = $pdo->prepare("
    SELECT o.*, 
           COUNT(oi.id) as items_count,
           SUM(oi.quantity * oi.price) as total_amount
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE o.id = ? AND o.user_id = ?
    GROUP BY o.id
");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: orders.php");
    exit();
}

// جلب عناصر الطلب
$stmt = $pdo->prepare("
    SELECT oi.*, p.name as product_name, p.image_path, p.description as product_description
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
        <!-- شريط جانبي للعميل -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-user-circle"></i> حسابي</h3>
                <p>مرحباً، <?php echo $_SESSION['full_name']; ?></p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-home"></i> الرئيسية</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> الملف الشخصي</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> طلباتي</a></li>
                <li><a href="order_details.php?id=<?php echo $order_id; ?>" class="active"><i class="fas fa-file-invoice"></i> تفاصيل الطلب</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="header">
                <h1><i class="fas fa-file-invoice"></i> تفاصيل الطلب #<?php echo $order_id; ?></h1>
                <p>عرض التفاصيل الكاملة لطلبك</p>
            </div>

            <div class="row">
                <!-- معلومات الطلب -->
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-info-circle"></i> معلومات الطلب</h3>
                        </div>
                        <div class="card-body">
                            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px;">
                                <div class="row">
                                    <div class="col-6">
                                        <p><strong>رقم الطلب:</strong><br>#<?php echo $order['id']; ?></p>
                                        <p><strong>تاريخ الطلب:</strong><br><?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></p>
                                    </div>
                                    <div class="col-6">
                                        <p><strong>عدد العناصر:</strong><br><?php echo $order['items_count']; ?> عنصر</p>
                                        <p><strong>الحالة:</strong><br>
                                            <?php
                                            $status_info = [
                                                'pending' => ['قيد الانتظار', '#fff3cd', '#856404'],
                                                'processing' => ['قيد المعالجة', '#d1ecf1', '#0c5460'],
                                                'completed' => ['مكتمل', '#d4edda', '#155724'],
                                                'cancelled' => ['ملغي', '#f8d7da', '#721c24']
                                            ];
                                            $status = $status_info[$order['status']];
                                            ?>
                                            <span style="padding: 5px 15px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; 
                                                  background: <?php echo $status[1]; ?>; color: <?php echo $status[2]; ?>;">
                                                <?php echo $status[0]; ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #ddd;">
                                    <h4 style="color: var(--success-color); text-align: center;">
                                        <i class="fas fa-money-bill-wave"></i>
                                        المبلغ الإجمالي: <?php echo number_format($order['total_amount'], 2); ?> ر.س
                                    </h4>
                                </div>
                            </div>
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
                                <i class="fas fa-map-marker-alt" style="color: var(--primary-color); margin-left: 10px;"></i>
                                <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- تتبع الطلب -->
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-map-marked-alt"></i> تتبع الطلب</h3>
                        </div>
                        <div class="card-body">
                            <div style="position: relative; padding: 2rem 1rem;">
                                <!-- خط التتبع -->
                                <div style="position: absolute; right: 40px; top: 0; bottom: 0; width: 2px; background: #e9ecef;"></div>
                                
                                <?php
                                $tracking_steps = [
                                    ['pending', 'تم استلام الطلب', 'fas fa-check', true],
                                    ['processing', 'قيد المعالجة', 'fas fa-cog', $order['status'] != 'pending'],
                                    ['shipping', 'قيد الشحن', 'fas fa-shipping-fast', in_array($order['status'], ['completed', 'shipping'])],
                                    ['completed', 'تم التوصيل', 'fas fa-home', $order['status'] == 'completed']
                                ];
                                
                                foreach ($tracking_steps as $index => $step):
                                    list($step_status, $step_text, $step_icon, $is_active) = $step;
                                ?>
                                <div style="display: flex; align-items: center; margin-bottom: 2rem; position: relative;">
                                    <div style="width: 40px; height: 40px; border-radius: 50%; background: <?php echo $is_active ? 'var(--success-color)' : '#e9ecef'; ?>; display: flex; align-items: center; justify-content: center; color: white; margin-left: 1rem; z-index: 2;">
                                        <i class="<?php echo $step_icon; ?>"></i>
                                    </div>
                                    <div>
                                        <h5 style="margin: 0; color: <?php echo $is_active ? 'var(--success-color)' : '#666'; ?>;">
                                            <?php echo $step_text; ?>
                                        </h5>
                                        <?php if ($is_active): ?>
                                        <small style="color: #999;"><?php echo date('Y-m-d H:i'); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- الملاحظات -->
                    <?php if ($order['notes']): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-sticky-note"></i> ملاحظات الطلب</h3>
                        </div>
                        <div class="card-body">
                            <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px;">
                                <i class="fas fa-comment" style="color: var(--warning-color); margin-left: 10px;"></i>
                                <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- عناصر الطلب -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-boxes"></i> عناصر الطلب (<?php echo count($order_items); ?>)</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($order_items)): ?>
                        <div style="text-align: center; padding: 2rem; color: #666;">
                            <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>لا توجد عناصر في هذا الطلب</p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($order_items as $item): ?>
                            <div class="col-6">
                                <div class="card" style="margin-bottom: 1rem; border: 1px solid #eee;">
                                    <div class="card-body">
                                        <div style="display: flex; gap: 15px; align-items: center;">
                                            <?php if ($item['image_path']): ?>
                                                <img src="../<?php echo $item['image_path']; ?>" alt="<?php echo $item['product_name']; ?>" 
                                                     style="width: 80px; height: 80px; object-fit: cover; border-radius: 10px;">
                                            <?php else: ?>
                                                <div style="width: 80px; height: 80px; background: #f8f9fa; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-box" style="font-size: 2rem; color: #ccc;"></i>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div style="flex: 1;">
                                                <h5 style="margin: 0 0 5px 0;"><?php echo htmlspecialchars($item['product_name']); ?></h5>
                                                <?php if ($item['product_description']): ?>
                                                    <p style="color: #666; font-size: 0.9rem; margin: 0 0 5px 0;">
                                                        <?php echo mb_substr($item['product_description'], 0, 50) . '...'; ?>
                                                    </p>
                                                <?php endif; ?>
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <span style="font-weight: bold; color: var(--success-color);">
                                                        <?php echo number_format($item['price'], 2); ?> ر.س
                                                    </span>
                                                    <span style="background: #667eea; color: white; padding: 2px 10px; border-radius: 15px; font-size: 0.8rem;">
                                                        الكمية: <?php echo $item['quantity']; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- المجموع -->
                        <div style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 1.5rem; border-radius: 10px; text-align: center; margin-top: 1rem;">
                            <h4 style="margin: 0;">
                                <i class="fas fa-receipt"></i>
                                المجموع الإجمالي: <?php echo number_format($order['total_amount'], 2); ?> ر.س
                            </h4>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- أزرار الإجراءات -->
            <div style="display: flex; gap: 10px; margin-top: 2rem;">
                <a href="orders.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-right"></i> العودة إلى الطلبات
                </a>
                <?php if ($order['status'] == 'pending'): ?>
                    <a href="cancel_order.php?id=<?php echo $order_id; ?>" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من إلغاء هذا الطلب؟')">
                        <i class="fas fa-times"></i> إلغاء الطلب
                    </a>
                <?php endif; ?>
                <a href="print_order.php?id=<?php echo $order_id; ?>" target="_blank" class="btn btn-primary">
                    <i class="fas fa-print"></i> طباعة الفاتورة
                </a>
            </div>
        </main>
    </div>
</body>
</html>