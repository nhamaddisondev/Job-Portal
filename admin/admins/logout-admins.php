<?php
session_start();
session_unset();
session_destroy();

// Determine the base URL for redirection
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

// Determine the project folder
$project_folder = explode('/', $_SERVER['REQUEST_URI'])[1];

// Redirect to the login page
$redirect_url = $base_url . '/' . $project_folder . '/admin/admins/login-admins.php';

header('Location: ' . $redirect_url);
exit();
?>