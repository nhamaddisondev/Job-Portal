<?php
session_start();
session_unset();
session_destroy();

// Determine the base URL for redirection
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

// Determine the project folder (full path up to 'admin')
$requestPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$parts = explode('/', $requestPath);
$adminIndex = array_search('admin', $parts);
$projectPath = $adminIndex !== false ? implode('/', array_slice($parts, 0, $adminIndex)) : $parts[0];

// Redirect to the login page
$redirect_url = $base_url . '/' . $projectPath . '/admin/admins/login-admins.php';

header('Location: ' . $redirect_url);
exit();
?>
