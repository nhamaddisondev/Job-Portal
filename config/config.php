<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}

$baseUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$scriptDir = rtrim($scriptDir, '/');
// Go up to project root (parent of current script directory)
$projectRoot = dirname($scriptDir);
$projectRoot = rtrim($projectRoot, '/');
define('BASEURL', $baseUrl . $projectRoot);

//Database Config
$conn = null;

try{
    $host = 'localhost';
    $dbname = 'online_jobs_portal';
    $port = '3307';
    $username = 'root';
    $password = '';

    $conn = new PDO("mysql:host=$host;dbname=$dbname;port=$port", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e){
    $db_error = $e->getMessage();
}

//Pending Applications Employee
$pendingApplications = 0;
if($conn instanceof PDO && isset($_SESSION['id']) && ($_SESSION['role'] ?? '') === 'employee'){
    $stmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE employee_id = :employee_id AND status = 'pending'");
    $stmt->bindParam(':employee_id', $_SESSION['id']);
    $stmt->execute();
    $pendingApplications = $stmt->fetchColumn();
}

if(!defined('ADMINURL')){
    $protocol      = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host          = $_SERVER['HTTP_HOST'];
    $scriptDir     = dirname($_SERVER['SCRIPT_NAME']);
    $parts         = explode('/', trim($scriptDir, '/'));
    // Find the position of 'admin' in the path
    $adminIndex = array_search('admin', $parts);
    if ($adminIndex !== false) {
        // Take everything before 'admin' (excluding 'admin' itself and anything after it)
        $adminPath = implode('/', array_slice($parts, 0, $adminIndex));
    } else {
        $adminPath = '';
    }
    // Remove leading/trailing slashes
    $adminPath = trim($adminPath, '/');
    define('ADMINURL', "$protocol://$host" . ($adminPath ? "/$adminPath" : '') . "/admin");
}
