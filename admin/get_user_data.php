<?php
require_once '../config.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // تحويل التواريخ إلى تنسيق مقروء
        $user['created_at'] = date('Y-m-d H:i:s', strtotime($user['created_at']));
        $user['last_login'] = $user['last_login'] ? date('Y-m-d H:i:s', strtotime($user['last_login'])) : null;
        
        header('Content-Type: application/json');
        echo json_encode($user);
    } else {
        header("HTTP/1.1 404 Not Found");
        echo json_encode(['error' => 'User not found']);
    }
} else {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['error' => 'User ID is required']);
}
?>