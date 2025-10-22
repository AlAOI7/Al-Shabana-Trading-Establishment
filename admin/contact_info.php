<?php
require_once '../config.php';
requireAdmin();

// جلب بيانات التواصل
$stmt = $pdo->query("SELECT * FROM contact_info LIMIT 1");
$contact_info = $stmt->fetch();

// معالجة تحديث البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_contact'])) {
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $social_facebook = $_POST['social_facebook'];
    $social_twitter = $_POST['social_twitter'];
    $social_instagram = $_POST['social_instagram'];
    
    if ($contact_info) {
        // تحديث البيانات الموجودة
        $stmt = $pdo->prepare("UPDATE contact_info SET address = ?, phone = ?, email = ?, social_facebook = ?, social_twitter = ?, social_instagram = ? WHERE id = ?");
        $stmt->execute([$address, $phone, $email, $social_facebook, $social_twitter, $social_instagram, $contact_info['id']]);
    } else {
        // إضافة بيانات جديدة
        $stmt = $pdo->prepare("INSERT INTO contact_info (address, phone, email, social_facebook, social_twitter, social_instagram) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$address, $phone, $email, $social_facebook, $social_twitter, $social_instagram]);
    }
    
    header("Location: contact_info.php?success=updated");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بيانات التواصل - الإدارة</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="header">
                <h1><i class="fas fa-address-book"></i> إدارة بيانات التواصل</h1>
                <p>تحديث معلومات التواصل والروابط الاجتماعية</p>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> تم تحديث بيانات التواصل بنجاح
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-edit"></i> معلومات التواصل</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="address"><i class="fas fa-map-marker-alt"></i> العنوان</label>
                                    <textarea class="form-control" id="address" name="address" rows="3" 
                                              placeholder="أدخل العنوان الكامل"><?php echo $contact_info ? htmlspecialchars($contact_info['address']) : ''; ?></textarea>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="phone"><i class="fas fa-phone"></i> رقم الهاتف</label>
                                    <input type="text" class="form-control" id="phone" name="phone" 
                                           value="<?php echo $contact_info ? htmlspecialchars($contact_info['phone']) : ''; ?>" 
                                           placeholder="أدخل رقم الهاتف">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="email"><i class="fas fa-envelope"></i> البريد الإلكتروني</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo $contact_info ? htmlspecialchars($contact_info['email']) : ''; ?>" 
                                           placeholder="أدخل البريد الإلكتروني">
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h4><i class="fas fa-share-alt"></i> الروابط الاجتماعية</h4>
                        
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="social_facebook" style="color: #1877f2;"><i class="fab fa-facebook"></i> فيسبوك</label>
                                    <input type="url" class="form-control" id="social_facebook" name="social_facebook" 
                                           value="<?php echo $contact_info ? htmlspecialchars($contact_info['social_facebook']) : ''; ?>" 
                                           placeholder="رابط الصفحة على فيسبوك">
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="social_twitter" style="color: #1da1f2;"><i class="fab fa-twitter"></i> تويتر</label>
                                    <input type="url" class="form-control" id="social_twitter" name="social_twitter" 
                                           value="<?php echo $contact_info ? htmlspecialchars($contact_info['social_twitter']) : ''; ?>" 
                                           placeholder="رابط الحساب على تويتر">
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="social_instagram" style="color: #e4405f;"><i class="fab fa-instagram"></i> إنستجرام</label>
                                    <input type="url" class="form-control" id="social_instagram" name="social_instagram" 
                                           value="<?php echo $contact_info ? htmlspecialchars($contact_info['social_instagram']) : ''; ?>" 
                                           placeholder="رابط الحساب على إنستجرام">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" name="update_contact" class="btn btn-primary">
                            <i class="fas fa-save"></i> حفظ البيانات
                        </button>
                    </form>
                </div>
            </div>

            <!-- معاينة بيانات التواصل -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-eye"></i> معاينة بيانات التواصل</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <h4>معلومات الاتصال</h4>
                            <div style="padding: 1rem; background: #f8f9fa; border-radius: 10px;">
                                <?php if ($contact_info): ?>
                                    <p><strong><i class="fas fa-map-marker-alt"></i> العنوان:</strong><br>
                                    <?php echo $contact_info['address'] ? nl2br(htmlspecialchars($contact_info['address'])) : '<span style="color: #999;">غير محدد</span>'; ?></p>
                                    
                                    <p><strong><i class="fas fa-phone"></i> الهاتف:</strong><br>
                                    <?php echo $contact_info['phone'] ?: '<span style="color: #999;">غير محدد</span>'; ?></p>
                                    
                                    <p><strong><i class="fas fa-envelope"></i> البريد الإلكتروني:</strong><br>
                                    <?php echo $contact_info['email'] ?: '<span style="color: #999;">غير محدد</span>'; ?></p>
                                <?php else: ?>
                                    <p style="color: #999; text-align: center;">لا توجد بيانات لعرضها</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-6">
                            <h4>وسائل التواصل الاجتماعي</h4>
                            <div style="padding: 1rem; background: #f8f9fa; border-radius: 10px; text-align: center;">
                                <?php if ($contact_info && ($contact_info['social_facebook'] || $contact_info['social_twitter'] || $contact_info['social_instagram'])): ?>
                                    <div style="display: flex; justify-content: center; gap: 1rem; font-size: 1.5rem;">
                                        <?php if ($contact_info['social_facebook']): ?>
                                            <a href="<?php echo $contact_info['social_facebook']; ?>" target="_blank" style="color: #1877f2;">
                                                <i class="fab fa-facebook"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($contact_info['social_twitter']): ?>
                                            <a href="<?php echo $contact_info['social_twitter']; ?>" target="_blank" style="color: #1da1f2;">
                                                <i class="fab fa-twitter"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($contact_info['social_instagram']): ?>
                                            <a href="<?php echo $contact_info['social_instagram']; ?>" target="_blank" style="color: #e4405f;">
                                                <i class="fab fa-instagram"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <p style="margin-top: 1rem; font-size: 0.9rem; color: #666;">انقر على الأيقونات لزيارة الصفحات</p>
                                <?php else: ?>
                                    <p style="color: #999;">لا توجد روابط اجتماعية مضافة</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>