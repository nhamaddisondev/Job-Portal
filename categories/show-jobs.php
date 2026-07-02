<?php
require '../config/config.php';
require '../includes/public-helpers.php';

$category = trim($_GET['category'] ?? $_GET['name'] ?? '');

if ($category === '' && isset($_GET['id']) && db_available($conn) && table_exists($conn, 'categories')) {
    try {
        $stmt = $conn->prepare('SELECT name FROM categories WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => (int) $_GET['id']]);
        $category = (string) $stmt->fetchColumn();
    } catch (Throwable $e) {
        $category = '';
    }
}

$target = BASEURL . '/findjobs.php';
if ($category !== '') {
    $target .= '?category=' . urlencode($category);
}

header('Location: ' . $target);
exit();
