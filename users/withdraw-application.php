<?php
require '../config/config.php';
require '../includes/public-helpers.php';

if (!isset($_SESSION['id'])) {
    header('Location: ' . BASEURL . '/auth/login.php');
    exit();
}

$appId = (int) ($_GET['id'] ?? 0);

if ($appId <= 0) {
    header('Location: ' . BASEURL . '/users/applied_jobs.php');
    exit();
}

if (db_available($conn) && table_exists($conn, 'applications')) {
    try {
        $columns = table_columns($conn, 'applications');
        $empCol = isset($columns['employee_id']) ? 'employee_id' : (isset($columns['user_id']) ? 'user_id' : null);

        if ($empCol) {
            $stmt = $conn->prepare("DELETE FROM applications WHERE id = :id AND $empCol = :user_id");
            $stmt->execute([
                ':id' => $appId,
                ':user_id' => (int) $_SESSION['id'],
            ]);
        }
    } catch (Throwable $e) {
        // Ignore
    }
}

header('Location: ' . BASEURL . '/users/applied_jobs.php');
exit();