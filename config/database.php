<?php
// ملف: config/database.php
class Database {
    private $host = "localhost";
    private $db_name = "alshabanat";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

// ملف: models/Setting.php
class Setting {
    private $conn;
    private $table_name = "settings";
    
    public $settings = array();

    public function __construct($db) {
        $this->conn = $db;
    }

    public function loadAllSettings() {
        $query = "SELECT setting_key, setting_value FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->settings[$row['setting_key']] = $row['setting_value'];
        }
        
        return $this->settings;
    }
    
    public function getSetting($key) {
        return isset($this->settings[$key]) ? $this->settings[$key] : '';
    }
}

// تهيئة الاتصال وجلب الإعدادات
$database = new Database();
$db = $database->getConnection();
$setting = new Setting($db);
$allSettings = $setting->loadAllSettings();

// دالة مساعدة للحصول على الإعدادات
function getSetting($key) {
    global $allSettings;
    return isset($allSettings[$key]) ? $allSettings[$key] : '';
}
// دالة للحصول على النص المترجم
function getTranslatedText($key_base, $lang = null) {
    global $current_lang;
    if ($lang === null) {
        $lang = $current_lang;
    }
    
    // محاولة جلب النص باللغة المحددة
    $lang_key = $key_base . '_' . $lang;
    $text = getSetting($lang_key);
    
    // إذا لم يوجد نص باللغة المطلوبة، نعود للنص الأساسي
    if (empty($text)) {
        $text = getSetting($key_base);
    }
    
    return $text ?: '';
}


?>
