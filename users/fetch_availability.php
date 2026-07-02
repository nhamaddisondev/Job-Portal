<?php
require '../config/config.php';
require '../includes/public-helpers.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode([]);
    exit();
}

$userId = (int) $_SESSION['id'];
$availability = [];

if (db_available($conn) && table_exists($conn, 'availability')) {
    try {
        $stmt = $conn->prepare("SELECT * FROM availability WHERE user_id = :id ORDER BY day_of_week ASC, start_time ASC");
        $stmt->execute([':id' => $userId]);
        $availability = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $availability = [];
    }
}

echo json_encode($availability);