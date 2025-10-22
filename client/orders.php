<?php
require_once '../config.php';
requireClient();

// جلب طلبات المستخدم الحالي
$stmt = $pdo->prepare("
    SELECT o.*, 
           COUNT(oi.id) as items_count,
           SUM(oi.quantity * oi.price) as total_amount
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE o.user_id = ? 
    GROUP BY o.id 
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلباتي</title>
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
                <li><a href="orders.php" class="active"><i class="fas fa-shopping-cart"></i> طلباتي</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> الإعدادات</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="header">
                <h1><i class="fas fa-shopping-cart"></i> طلباتي</h1>
                <p>عرض وتتبع طلباتك السابقة</p>
            </div>

            <!-- إحصائيات سريعة -->
            <div class="row">
                <div class="col-3">
                    <div class="card">
                        <div class="card-body" style="text-align: center;">
                            <h3><?php echo count($orders); ?></h3>
                            <p>إجمالي الطلبات</p>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="card">
                        <div class="card-body" style="text-align: center;">
                            <h3><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'completed')); ?></h3>
                            <p>طلبات مكتملة</p>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="card">
                        <div class="card-body" style="text-align: center;">
                            <h3><?php echo count(array_filter($orders, fn($o) => in_array($o['status'], ['pending', 'processing']))); ?></h3>
                            <p>طلبات نشطة</p>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="card">
                        <div class="card-body" style="text-align: center;">
                            <h3>
                                <?php 
                                $total_spent = array_sum(array_column($orders, 'total_amount'));
                                echo number_format($total_spent, 0);
                                ?> ر.س
                            </h3>
                            <p>إجمالي المشتريات</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- قائمة الطلبات -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> قائمة طلباتي</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($orders)): ?>
                        <div style="text-align: center; padding: 3rem; color: #666;">
                            <i class="fas fa-shopping-cart" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <h4>لا توجد طلبات حتى الآن</h4>
                            <p>يمكنك بدء التسوق وإنشاء طلبك الأول</p>
                            <a href="../index.php" class="btn btn-primary" style="margin-top: 1rem;">
                                <i class="fas fa-shopping-bag"></i> ابدأ التسوق
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>رقم الطلب</th>
                                        <th>التاريخ</th>
                                        <th>عدد العناصر</th>
                                        <th>المبلغ</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?php echo $order['id']; ?></strong>
                                        </td>
                                        <td>
                                            <?php echo date('Y-m-d', strtotime($order['created_at'])); ?><br>
                                            <small style="color: #666;"><?php echo date('H:i', strtotime($order['created_at'])); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge" style="background: #667eea; color: white; padding: 5px 10px; border-radius: 15px;">
                                                <?php echo $order['items_count']; ?> عنصر
                                            </span>
                                        </td>
                                        <td>
                                            <strong style="color: var(--success-color);">
                                                <?php echo number_format($order['total_amount'], 2); ?> ر.س
                                            </strong>
                                        </td>
                                        <td>
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
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 5px;">
                                                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.8rem;">
                                                    <i class="fas fa-eye"></i> عرض
                                                </a>
                                                <?php if ($order['status'] == 'pending'): ?>
                                                    <a href="cancel_order.php?id=<?php echo $order['id']; ?>" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem;" onclick="return confirm('هل أنت متأكد من إلغاء هذا الطلب؟')">
                                                        <i class="fas fa-times"></i> إلغاء
                                                    </a>
                                                <?php endif; ?>
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

            <!-- تتبع الطلبات -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-truck"></i> تتبع الطلبات</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f8f9fa; border-radius: 10px;">
                                <div style="text-align: center; flex: 1;">
                                    <div style="width: 50px; height: 50px; background: var(--success-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; margin: 0 auto 10px;">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <small>تم استلام الطلب</small>
                                </div>
                                <div style="text-align: center; flex: 1;">
                                    <div style="width: 50px; height: 50px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; margin: 0 auto 10px;">
                                        <i class="fas fa-cog"></i>
                                    </div>
                                    <small>قيد المعالجة</small>
                                </div>
                                <div style="text-align: center; flex: 1;">
                                    <div style="width: 50px; height: 50px; background: #6c757d; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; margin: 0 auto 10px;">
                                        <i class="fas fa-shipping-fast"></i>
                                    </div>
                                    <small>قيد الشحن</small>
                                </div>
                                <div style="text-align: center; flex: 1;">
                                    <div style="width: 50px; height: 50px; background: #6c757d; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; margin: 0 auto 10px;">
                                        <i class="fas fa-home"></i>
                                    </div>
                                    <small>تم التوصيل</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>