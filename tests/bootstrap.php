<?php

declare(strict_types=1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoload Composer dependencies
require_once __DIR__ . '/../vendor/autoload.php';

// Set testing environment variables
$_ENV['APP_ENV'] = 'testing';
$_SERVER['APP_ENV'] = 'testing';

// Mock $_SERVER variables for testing
$_SERVER['HTTPS'] = 'off';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/job-portal/index.php';

// Define constants for testing
if (!defined('BASEURL')) {
    define('BASEURL', 'http://localhost/job-portal');
}

if (!defined('ADMINURL')) {
    define('ADMINURL', 'http://localhost/job-portal/admin');
}

// Set up test database connection (optional - for integration tests)
$testDbHost = getenv('DB_HOST') ?: 'localhost';
$testDbName = getenv('DB_NAME') ?: 'online_jobs_portal_test';
$testDbUser = getenv('DB_USER') ?: 'root';
$testDbPass = getenv('DB_PASS') ?: '';
$testDbPort = getenv('DB_PORT') ?: '3307';

// Create PDO connection for testing
try {
    $pdo = new PDO(
        "mysql:host={$testDbHost};dbname={$testDbName};port={$testDbPort}",
        $testDbUser,
        $testDbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    // Database might not exist for unit tests - that's okay
    $pdo = null;
}

// Helper function for HTML escaping (from your project)
if (!function_exists('h')) {
    function h($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

// Mock database helper functions for unit testing
if (!function_exists('db_available')) {
    function db_available($conn): bool
    {
        return $conn instanceof PDO;
    }
}

if (!function_exists('table_exists')) {
    function table_exists(PDO $conn, string $table): bool
    {
        try {
            $stmt = $conn->query("SHOW TABLES LIKE '{$table}'");
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
}

if (!function_exists('table_columns')) {
    function table_columns(PDO $conn, string $table): array
    {
        $columns = [];
        try {
            $stmt = $conn->query("DESCRIBE {$table}");
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $column) {
                $columns[$column['Field']] = $column;
            }
        } catch (PDOException $e) {
            // Table doesn't exist
        }
        return $columns;
    }
}

// Error reporting for testing
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Set timezone
date_default_timezone_set('Asia/Bangkok');