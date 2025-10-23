
<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// معالجة إضافة سؤال جديد
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_faq'])) {
    $question_ar = $_POST['question_ar'];
    $question_en = $_POST['question_en'];
    $answer_ar = $_POST['answer_ar'];
    $answer_en = $_POST['answer_en'];
    $display_order = $_POST['display_order'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO faqs (question_ar, question_en, answer_ar, answer_en, display_order) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$question_ar, $question_en, $answer_ar, $answer_en, $display_order]);
        $success = "تم إضافة السؤال بنجاح";
    } catch (PDOException $e) {
        $error = "خطأ في إضافة السؤال: " . $e->getMessage();
    }
}

// معالجة تحديث سؤال
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_faq'])) {
    $id = $_POST['faq_id'];
    $question_ar = $_POST['question_ar'];
    $question_en = $_POST['question_en'];
    $answer_ar = $_POST['answer_ar'];
    $answer_en = $_POST['answer_en'];
    $display_order = $_POST['display_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    try {
        $stmt = $pdo->prepare("UPDATE faqs SET question_ar = ?, question_en = ?, answer_ar = ?, answer_en = ?, display_order = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$question_ar, $question_en, $answer_ar, $answer_en, $display_order, $is_active, $id]);
        $success = "تم تحديث السؤال بنجاح";
    } catch (PDOException $e) {
        $error = "خطأ في تحديث السؤال: " . $e->getMessage();
    }
}

// معالجة حذف سؤال
if (isset($_GET['delete_id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM faqs WHERE id = ?");
        $stmt->execute([$_GET['delete_id']]);
        $success = "تم حذف السؤال بنجاح";
    } catch (PDOException $e) {
        $error = "خطأ في حذف السؤال: " . $e->getMessage();
    }
}

// جلب جميع الأسئلة
try {
    $stmt = $pdo->query("SELECT * FROM faqs ORDER BY display_order ASC, created_at DESC");
    $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $faqs = [];
    $error = "خطأ في جلب الأسئلة: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الأسئلة الشائعة</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <?php include 'admin_navbar.php'; ?>
            
            <div class="faq-management">
                <div class="header">
                    <h1><i class="fas fa-question-circle"></i> إدارة الأسئلة الشائعة</h1>
                    <p>إضافة وتعديل وحذف الأسئلة الشائعة</p>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- نموذج إضافة سؤال جديد -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-plus"></i> إضافة سؤال جديد</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="question_ar">السؤال (عربي)</label>
                                        <input type="text" class="form-control" id="question_ar" name="question_ar" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="question_en">السؤال (إنجليزي)</label>
                                        <input type="text" class="form-control" id="question_en" name="question_en" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="answer_ar">الإجابة (عربي)</label>
                                        <textarea class="form-control" id="answer_ar" name="answer_ar" rows="3" required></textarea>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="answer_en">الإجابة (إنجليزي)</label>
                                        <textarea class="form-control" id="answer_en" name="answer_en" rows="3" required></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="display_order">ترتيب العرض</label>
                                <input type="number" class="form-control" id="display_order" name="display_order" value="0" min="0">
                            </div>
                            <button type="submit" name="add_faq" class="btn btn-primary">
                                <i class="fas fa-save"></i> إضافة السؤال
                            </button>
                        </form>
                    </div>
                </div>

                <!-- قائمة الأسئلة -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-list"></i> الأسئلة الحالية</h3>
                    </div>
                    <div class="card-body">
                        <?php if (count($faqs) > 0): ?>
                            <?php foreach ($faqs as $faq): ?>
                            <div class="faq-item-card">
                                <form method="POST" class="faq-form">
                                    <input type="hidden" name="faq_id" value="<?php echo $faq['id']; ?>">
                                    
                                    <div class="row">
                                        <div class="col-5">
                                            <div class="form-group">
                                                <label>السؤال (عربي)</label>
                                                <input type="text" class="form-control" name="question_ar" 
                                                       value="<?php echo htmlspecialchars($faq['question_ar']); ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label>الإجابة (عربي)</label>
                                                <textarea class="form-control" name="answer_ar" rows="3" required><?php echo htmlspecialchars($faq['answer_ar']); ?></textarea>
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <div class="form-group">
                                                <label>السؤال (إنجليزي)</label>
                                                <input type="text" class="form-control" name="question_en" 
                                                       value="<?php echo htmlspecialchars($faq['question_en']); ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label>الإجابة (إنجليزي)</label>
                                                <textarea class="form-control" name="answer_en" rows="3" required><?php echo htmlspecialchars($faq['answer_en']); ?></textarea>
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div class="form-group">
                                                <label>الترتيب</label>
                                                <input type="number" class="form-control" name="display_order" 
                                                       value="<?php echo $faq['display_order']; ?>" min="0">
                                            </div>
                                            <div class="form-group">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" name="is_active" 
                                                           id="active_<?php echo $faq['id']; ?>" <?php echo $faq['is_active'] ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="active_<?php echo $faq['id']; ?>">نشط</label>
                                                </div>
                                            </div>
                                            <div class="faq-actions">
                                                <button type="submit" name="update_faq" class="btn btn-success btn-sm">
                                                    <i class="fas fa-save"></i> تحديث
                                                </button>
                                                <a href="?delete_id=<?php echo $faq['id']; ?>" class="btn btn-danger btn-sm" 
                                                   onclick="return confirm('هل أنت متأكد من حذف هذا السؤال؟')">
                                                    <i class="fas fa-trash"></i> حذف
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-data">لا توجد أسئلة مضافة حالياً</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>