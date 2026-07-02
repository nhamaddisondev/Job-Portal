<?php
require '../config/config.php';
require '../includes/public-helpers.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode([]);
    exit();
}

$userId = (int) $_SESSION['id'];
$notifications = [];

if (db_available($conn) && table_exists($conn, 'notifications')) {
    try {
        $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = :id ORDER BY created_at DESC LIMIT 20");
        $stmt->execute([':id' => $userId]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $notifications = [];
    }
}

echo json_encode($notifications);