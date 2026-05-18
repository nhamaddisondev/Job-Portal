<?php require_once '../../config/config.php'; ?>

<?php
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASEURL . '/login.php');
    exit();
}

$pageTitle = "Admins";
$breadcrumb = "Systems";

require_once '../../includes/header.php';

$stmt = $conn->prepare("SELECT * FROM users WHERE role = 'admin' ORDER BY created_at DESC");
$stmt->execute();
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
function h($v)
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

// ---- Flash alerts via query flags ----
$flash = null; // ['class' => 'alert-success', 'msg' => '...']

if (isset($_GET['created']) && $_GET['created'] === '1') {
    $flash = ['class' => 'alert-success', 'msg' => 'Admin created successfully.'];
} elseif (isset($_GET['updated']) && $_GET['updated'] === '1') {
    $flash = ['class' => 'alert-info', 'msg' => 'Admin updated successfully.'];
} elseif (isset($_GET['deleted']) && $_GET['deleted'] === '1') {
    $flash = [
        'class' => 'alert-danger',
        'msg' => 'Admin deleted successfully.'
    ];
} elseif (!empty($_GET['error'])) {
    // Optional: pass a brief error reason as ?error=...
    $flash = ['class' => 'alert-warning', 'msg' => h($_GET['error'])];
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Document</title>
</head>
<body>
    
</body>
</html>