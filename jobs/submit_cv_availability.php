<?php
require '../config/config.php';
require '../includes/public-helpers.php';

$jobId = (int) ($_POST['job_id'] ?? $_GET['job_id'] ?? 0);

if (!isset($_SESSION['id'])) {
    header('Location: ' . BASEURL . '/auth/login.php');
    exit();
}

if (($_SESSION['role'] ?? '') !== 'employee' && ($_SESSION['type'] ?? '') !== 'Job Seeker') {
    header('Location: ' . BASEURL . '/jobs/job-single.php?id=' . $jobId . '&error=' . urlencode('Only job seekers can apply for jobs.'));
    exit();
}

if ($jobId <= 0) {
    header('Location: ' . BASEURL . '/findjobs.php');
    exit();
}

if (!db_available($conn) || !table_exists($conn, 'applications')) {
    header('Location: ' . BASEURL . '/jobs/job-single.php?id=' . $jobId . '&error=' . urlencode('Applications are not available yet.'));
    exit();
}

try {
    $columns = table_columns($conn, 'applications');
    $employeeColumn = isset($columns['employee_id']) ? 'employee_id' : (isset($columns['user_id']) ? 'user_id' : null);

    if (!$employeeColumn || !isset($columns['job_id'])) {
        throw new RuntimeException('Missing application columns.');
    }

    $check = $conn->prepare("SELECT id FROM applications WHERE $employeeColumn = :employee_id AND job_id = :job_id LIMIT 1");
    $check->execute([
        ':employee_id' => (int) $_SESSION['id'],
        ':job_id' => $jobId,
    ]);

    if ($check->fetch()) {
        header('Location: ' . BASEURL . '/jobs/job-single.php?id=' . $jobId . '&message=' . urlencode('You already applied for this job.'));
        exit();
    }

    $data = [
        $employeeColumn => (int) $_SESSION['id'],
        'job_id' => $jobId,
    ];

    if (isset($columns['status'])) {
        $data['status'] = 'pending';
    }
    if (isset($columns['created_at'])) {
        $data['created_at'] = date('Y-m-d H:i:s');
    }
    if (isset($columns['updated_at'])) {
        $data['updated_at'] = date('Y-m-d H:i:s');
    }

    $fields = array_keys($data);
    $placeholders = array_map(fn($field) => ':' . $field, $fields);
    $stmt = $conn->prepare('INSERT INTO applications (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $placeholders) . ')');
    $stmt->execute($data);

    header('Location: ' . BASEURL . '/jobs/job-single.php?id=' . $jobId . '&message=' . urlencode('Application submitted successfully.'));
    exit();
} catch (Throwable $e) {
    header('Location: ' . BASEURL . '/jobs/job-single.php?id=' . $jobId . '&error=' . urlencode('Unable to submit your application.'));
    exit();
}
