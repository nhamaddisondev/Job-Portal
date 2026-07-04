<?php
// setup.php - Run this once to initialize the database
// Access it at: http://localhost/Cloner/job-portal/setup.php

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #f4f4f4; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Job Portal Database Setup</h1>

<?php
require_once 'config/config.php';

echo "<h2>Step 1: Creating tables...</h2>";

$tables = [];

// Users table
$tables[] = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(255) NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    contact VARCHAR(50),
    password VARCHAR(255) NOT NULL,
    type ENUM('employer', 'employee') NOT NULL DEFAULT 'employee',
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Employers table
$tables[] = "CREATE TABLE IF NOT EXISTS employers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    fullname VARCHAR(255) NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    contact VARCHAR(50),
    industry VARCHAR(255),
    company_name VARCHAR(255),
    address_line TEXT,
    img VARCHAR(255),
    established_year INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Categories table
$tables[] = "CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Job regions table
$tables[] = "CREATE TABLE IF NOT EXISTS job_regions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(10),
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Jobs table
$tables[] = "CREATE TABLE IF NOT EXISTS jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_title VARCHAR(255) NOT NULL,
    job_category VARCHAR(255),
    job_region VARCHAR(255),
    job_type VARCHAR(100),
    work_arrangement VARCHAR(100),
    vacancy INT DEFAULT 1,
    experience VARCHAR(255),
    salary VARCHAR(255),
    inclusivity_notes TEXT,
    application_deadline DATE,
    job_description TEXT,
    responsibilities TEXT,
    education_experience TEXT,
    other_benefits TEXT,
    company_name VARCHAR(255),
    company_email VARCHAR(255),
    company_id INT,
    company_image VARCHAR(255),
    status TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Applications table
$tables[] = "CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    employee_id INT NOT NULL,
    cover_letter TEXT,
    cv_path VARCHAR(255),
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Saved jobs table
$tables[] = "CREATE TABLE IF NOT EXISTS saved_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    user_id INT NOT NULL,
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_save (job_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Notifications table
$tables[] = "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Job specifications table
$tables[] = "CREATE TABLE IF NOT EXISTS job_specifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    question_text TEXT NOT NULL,
    qtype ENUM('text', 'textarea', 'radio', 'checkbox') DEFAULT 'text',
    options JSON,
    is_required TINYINT(1) DEFAULT 1,
    source ENUM('predefined', 'custom') DEFAULT 'custom',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Availability table
$tables[] = "CREATE TABLE IF NOT EXISTS availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    day_of_week TINYINT NOT NULL,
    start_time TIME,
    end_time TIME,
    is_available TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_availability (user_id, day_of_week)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$errors = [];
foreach ($tables as $sql) {
    try {
        $conn->exec($sql);
        echo "<p class='success'>✓ Table created successfully</p>";
    } catch (PDOException $e) {
        // Table might already exist, that's okay
        if (strpos($e->getMessage(), 'already exists') === false) {
            echo "<p class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            $errors[] = $e->getMessage();
        }
    }
}

echo "<h2>Step 2: Creating admin account...</h2>";

try {
    // Check if admin already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = 'admin@example.com' AND role = 'admin'");
    $stmt->execute();
    
    if ($stmt->fetch()) {
        echo "<p class='info'>Admin account already exists.</p>";
    } else {
        $password = password_hash('password', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Admin', 'admin@example.com', $password, 'admin']);
        echo "<p class='success'>✓ Admin account created successfully!</p>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>✗ Error creating admin: " . htmlspecialchars($e->getMessage()) . "</p>";
    $errors[] = $e->getMessage();
}

echo "<h2>Step 3: Creating sample categories...</h2>";

try {
    $categories = ['Engineering', 'Marketing', 'Sales', 'Design', 'Development', 'Customer Service'];
    foreach ($categories as $cat) {
        try {
            $stmt = $conn->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
            $stmt->execute([$cat]);
        } catch (PDOException $e) {
            // Ignore duplicate errors
        }
    }
    echo "<p class='success'>✓ Sample categories created</p>";
} catch (PDOException $e) {
    echo "<p class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>Step 4: Creating sample job regions...</h2>";

try {
    $regions = [
        ['New South Wales', 'NSW'],
        ['Victoria', 'VIC'],
        ['Queensland', 'QLD'],
        ['Western Australia', 'WA'],
        ['South Australia', 'SA']
    ];
    foreach ($regions as $region) {
        try {
            $stmt = $conn->prepare("INSERT IGNORE INTO job_regions (name, code) VALUES (?, ?)");
            $stmt->execute($region);
        } catch (PDOException $e) {
            // Ignore duplicate errors
        }
    }
    echo "<p class='success'>✓ Sample regions created</p>";
} catch (PDOException $e) {
    echo "<p class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<h2>Setup Complete!</h2>";

if (empty($errors)) {
    echo "<p class='success'>✓ All tables and sample data created successfully!</p>";
    echo "<h3>Next steps:</h3>";
    echo "<ol>";
    echo "<li><a href='auth/register.php'>Register a test user account</a> (as Job Seeker or Employer)</li>";
    echo "<li><a href='admin/admins/login-admins.php'>Login as admin</a> with:<br>";
    echo "<strong>Email:</strong> admin@example.com<br>";
    echo "<strong>Password:</strong> password</li>";
    echo "</ol>";
} else {
    echo "<p class='error'>Some errors occurred. Please check the messages above.</p>";
}

echo "<p><strong>Important:</strong> Delete this setup.php file after use for security!</p>";
?>

</body>
</html>