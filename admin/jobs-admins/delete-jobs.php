<?php require '../../config/config.php'; ?>


<?php require '../layouts/header.php'; ?> 

<?php

if(isset($_SESSION['adminname'])) {
    header("location : " . ADMINURL . "/admins/login-admins.php");
    exit();
}

if(isset($_GET['id'])) {
    $id = $_GET['id'];

    $delete = $conn->prepare("DELETE FROM jobs WHERE id = :id");
    $delete->execute([':id' => $id]);

    if($delete) {
        $_SESSION['success'] = "Job deleted successfully";
        header("location: " . ADMINURL . "/jobs-admins/jobs.php");
        exit();
    } else {
        header ("location: ".ADMINURL."/404.php");
    }
}