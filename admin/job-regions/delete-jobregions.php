<?php require '../../config/config.php'; ?>

<?php
if (isset($_SESSION['adminname'])) {
    header("location : " . ADMINURL . "/admins/login-admins.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . ADMINURL . '/categories-admins/show-categories.php?error=' . urlencode('Invalid category ID.'));
    exit();
}

$id = (int) $_GET['id'];

try {
    // Optional: verify it exists first (gives nicer error)
    $chk = $conn->prepare("SELECT 1 FROM job_regions WHERE id = :id LIMIT 1");
    $chk->execute([':id' => $id]);
    if (!$chk->fetch()) {
        header("Location: " . ADMINURL . "/job-regions/show-jobregions.php?error=" . urlencode("Job region not found."));
        exit;
    }

    $del = $conn->prepare("DELETE FROM job_regions WHERE id = :id");
    $del->execute([':id' => $id]);

    // Success
    header("Location: " . ADMINURL . "/job-regions/show-jobregions.php?deleted=1", true, 303);
    exit;
} catch (Exception $e) {
    header("Location: " . ADMINURL . "/job-regions/show-jobregions.php?error=" . urlencode("Unexpected error."), true, 303);
    exit;
}