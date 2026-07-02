<?php
require '../config/config.php';
require '../includes/public-helpers.php';

if (!isset($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'employer') {
    header('Location: ' . BASEURL . '/auth/login.php');
    exit();
}

$jobId = (int) ($_GET['id'] ?? 0);

if ($jobId > 0 && db_available($conn) && table_exists($conn, 'jobs')) {
    try {
        $stmt = $conn->prepare("DELETE FROM jobs WHERE id = :id AND company_id = :company_id");
        $stmt->execute([
            ':id' => $jobId,
            ':company_id' => (int) $_SESSION['id'],
        ]);
    } catch (Throwable $e) {
        // Ignore
    }
}

header('Location: ' . BASEURL . '/users/postedJobs.php');
exit();