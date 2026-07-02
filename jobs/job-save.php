<?php
require '../config/config.php';
require '../includes/public-helpers.php';

$jobId = (int) ($_POST['job_id'] ?? $_GET['id'] ?? 0);

if (!isset($_SESSION['id'])) {
    header('Location: ' . BASEURL . '/auth/login.php');
    exit();
}

if ($jobId <= 0) {
    header('Location: ' . BASEURL . '/findjobs.php');
    exit();
}

if (!db_available($conn) || !table_exists($conn, 'saved_jobs')) {
    header('Location: ' . BASEURL . '/jobs/job-single.php?id=' . $jobId . '&error=' . urlencode('Saved jobs are not available yet.'));
    exit();
}

try {
    $columns = table_columns($conn, 'saved_jobs');
    $userColumn = isset($columns['user_id']) ? 'user_id' : (isset($columns['employee_id']) ? 'employee_id' : null);
    $jobColumn = isset($columns['job_id']) ? 'job_id' : null;

    if (!$userColumn || !$jobColumn) {
        throw new RuntimeException('Missing saved_jobs columns.');
    }

    $check = $conn->prepare("SELECT id FROM saved_jobs WHERE $userColumn = :user_id AND $jobColumn = :job_id LIMIT 1");
    $check->execute([
        ':user_id' => (int) $_SESSION['id'],
        ':job_id' => $jobId,
    ]);

    if (!$check->fetch()) {
        $data = [
            $userColumn => (int) $_SESSION['id'],
            $jobColumn => $jobId,
        ];
        if (isset($columns['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        $fields = array_keys($data);
        $placeholders = array_map(fn($field) => ':' . $field, $fields);
        $stmt = $conn->prepare('INSERT INTO saved_jobs (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $placeholders) . ')');
        $stmt->execute($data);
    }

    header('Location: ' . BASEURL . '/jobs/job-single.php?id=' . $jobId . '&message=' . urlencode('Job saved successfully.'));
    exit();
} catch (Throwable $e) {
    header('Location: ' . BASEURL . '/jobs/job-single.php?id=' . $jobId . '&error=' . urlencode('Unable to save this job.'));
    exit();
}
