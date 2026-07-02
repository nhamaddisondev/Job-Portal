<?php
require '../config/config.php';
require '../includes/public-helpers.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['available' => false, 'message' => 'Please log in first.']);
    exit();
}

$available = true;
$message = 'Ready to apply.';

if (db_available($conn) && table_exists($conn, 'applications') && isset($_GET['job_id'])) {
    try {
        $columns = table_columns($conn, 'applications');
        $employeeColumn = isset($columns['employee_id']) ? 'employee_id' : (isset($columns['user_id']) ? 'user_id' : null);
        if ($employeeColumn && isset($columns['job_id'])) {
            $stmt = $conn->prepare("SELECT id FROM applications WHERE $employeeColumn = :employee_id AND job_id = :job_id LIMIT 1");
            $stmt->execute([
                ':employee_id' => (int) $_SESSION['id'],
                ':job_id' => (int) $_GET['job_id'],
            ]);
            if ($stmt->fetch()) {
                $available = false;
                $message = 'You already applied for this job.';
            }
        }
    } catch (Throwable $e) {
        $available = false;
        $message = 'Unable to check application status.';
    }
}

echo json_encode(['available' => $available, 'message' => $message]);
