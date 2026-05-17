<?php
session_start(); 

//Database Config
try{
    $host = 'localhost';
    $dbname = 'online_jobs_portal';
    $port = '3308';
    $username = 'root';
    $password = '';

    $conn = new PDO("mysql:host=$host;dbname=$dbname;port=$port", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e){
    echo "Connection failed: " . $e->getMessage();
}

//Pending Applications Employee
$pendingApplications = 0;
if(isset($_SESSION['id']) && $_SESSION['role'] === 'employee'){
    $stmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE employee_id = :employee_id AND status = 'pending'");
    $stmt->bindParam(':employee_id', $_SESSION['id']);
    $stmt->execute();
    $pendingApplications = $stmt->fetchColumn();
}

if(!defined('ADMINURL')){
    $protocol      = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host          = $_SERVER['HTTP_HOST'];
    $scriptDir     = dirname($_SERVER['SCRIPT_NAME']);      //  /online-job-portal-php-mysql/admin-panel/jobs-admins
    $projectFolder = explode('/', trim($scriptDir, '/'))[0]; // online-job-portal-php-mysql
    define('ADMINURL', "$protocol://$host/$projectFolder/admin");
}