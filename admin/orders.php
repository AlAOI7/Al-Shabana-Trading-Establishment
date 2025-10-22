<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}


// جلب جميع الطلبات مع معلومات المستخدمين
$stmt = $pdo->query("
    SELECT o.*, u.username, u.full_name, u.email 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll();

// معالجة تحديث حالة الطلب
if (isset($_POST['update_order_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);
    
    header("Location: orders.php?success=status_updated");
    exit();
}

// معالجة حذف الطلب
if (isset($_GET['delete_order'])) {
    $order_id = $_GET['delete_order'];
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    header("Location: orders.php?success=order_deleted");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الطلبات</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="header">
                <h1><i class="fas fa-shopping-cart"></i> إدارة الطلبات</h1>
                <p>عرض وإدارة طلبات العملاء</p>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> 
                    <?php 
                    if ($_GET['success'] == 'status_updated') echo 'تم تحديث حالة الطلب بنجاح';
                    elseif ($_GET['success'] == 'order_deleted') echo 'تم حذف الطلب بنجاح';
                    ?>
                </div>
            <?php endif; ?>

            <!-- إحصائيات الطلبات -->
            <div class="row">
                <?php
                $statuses = ['pending', 'processing', 'completed', 'cancelled'];
                $status_labels = ['قيد الانتظار', 'قيد المعالجة', 'مكتمل', 'ملغي'];
                $status_colors = ['warning', 'info', 'success', 'danger'];
                
                foreach ($statuses as $index => $status) {
                    $count = count(array_filter($orders, fn($o) => $o['status'] === $status));
                    $color = $status_colors[$index];
                    $label = $status_labels[$index];
                ?>
                <div class="col-3">
                    <div class="card">
                        <div class="card-body" style="text-align: center;">
                            <h3 style="color: var(--<?php echo $color; ?>-color);"><?php echo $count; ?></h3>
                            <p><?php echo $label; ?></p>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>

            <!-- جدول الطلبات -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> قائمة الطلبات</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($orders)): ?>
                        <div style="text-align: center; padding: 3rem; color: #666;">
                            <i class="fas fa-shopping-cart" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>لا توجد طلبات حتى الآن</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>العميل</th>
                                        <th>المبلغ</th>
                                        <th>الحالة</th>
                                        <th>تاريخ الطلب</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><?php echo $order['id']; ?></td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($order['full_name'] ?: $order['username']); ?></strong><br>
                                                <small style="color: #666;"><?php echo $order['email']; ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?php echo number_format($order['total_amount'], 2); ?> ر.س</strong>
                                        </td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <select name="status" onchange="this.form.submit()" 
                                                        style="padding: 5px; border: none; border-radius: 5px; 
                                                               background: <?php 
                                                               switch($order['status']) {
                                                                   case 'pending': echo '#fff3cd'; break;
                                                                   case 'processing': echo '#d1ecf1'; break;
                                                                   case 'completed': echo '#d4edda'; break;
                                                                   case 'cancelled': echo '#f8d7da'; break;
                                                               } ?>;">
                                                    <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>قيد الانتظار</option>
                                                    <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>قيد المعالجة</option>
                                                    <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>مكتمل</option>
                                                    <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>ملغي</option>
                                                </select>
                                                <input type="hidden" name="update_order_status" value="1">
                                            </form>
                                        </td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <div style="display: flex; gap: 5px;">
                                                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.8rem;">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="orders.php?delete_order=<?php echo $order['id']; ?>" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem;" onclick="return confirm('هل أنت متأكد من حذف هذا الطلب؟')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
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
</body>
</html>