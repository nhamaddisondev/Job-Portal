<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PDO;

class JobPostingTest extends TestCase
{
    private static ?PDO $pdo = null;

    public static function setUpBeforeClass(): void
    {
        // Skip if no database connection
        $host = getenv('DB_HOST') ?: 'localhost';
        $dbname = getenv('DB_NAME') ?: 'online_jobs_portal_test';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        $port = getenv('DB_PORT') ?: '3307';

        try {
            self::$pdo = new PDO(
                "mysql:host={$host};dbname={$dbname};port={$port}",
                $user,
                $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            self::$pdo = null;
        }
    }

    protected function setUp(): void
    {
        if (self::$pdo === null) {
            $this->markTestSkipped('Database connection not available for integration tests');
        }
    }

    /**
     * Test database connection
     */
    public function testDatabaseConnection(): void
    {
        $this->assertNotNull(self::$pdo, 'Database connection should be established');
        $this->assertInstanceOf(PDO::class, self::$pdo);
    }

    /**
     * Test if jobs table exists
     */
    public function testJobsTableExists(): void
    {
        $stmt = self::$pdo->query("SHOW TABLES LIKE 'jobs'");
        $this->assertEquals(1, $stmt->rowCount(), 'Jobs table should exist');
    }

    /**
     * Test if users table exists
     */
    public function testUsersTableExists(): void
    {
        $stmt = self::$pdo->query("SHOW TABLES LIKE 'users'");
        $this->assertEquals(1, $stmt->rowCount(), 'Users table should exist');
    }

    /**
     * Test if categories table exists
     */
    public function testCategoriesTableExists(): void
    {
        $stmt = self::$pdo->query("SHOW TABLES LIKE 'categories'");
        $this->assertEquals(1, $stmt->rowCount(), 'Categories table should exist');
    }

    /**
     * Test if job_regions table exists
     */
    public function testJobRegionsTableExists(): void
    {
        $stmt = self::$pdo->query("SHOW TABLES LIKE 'job_regions'");
        $this->assertEquals(1, $stmt->rowCount(), 'Job regions table should exist');
    }

    /**
     * Test inserting a job into database
     */
    public function testInsertJob(): void
    {
        // First, create a test employer user
        $employerId = $this->createTestEmployer();
        
        $sql = "INSERT INTO jobs (
            job_title, 
            job_category, 
            job_region, 
            job_type, 
            company_name,
            company_id,
            status,
            created_at,
            updated_at
        ) VALUES (
            :job_title,
            :job_category,
            :job_region,
            :job_type,
            :company_name,
            :company_id,
            :status,
            :created_at,
            :updated_at
        )";

        $stmt = self::$pdo->prepare($sql);
        $result = $stmt->execute([
            ':job_title' => 'Test PHP Developer Position',
            ':job_category' => 'Technology',
            ':job_region' => 'Nairobi',
            ':job_type' => 'Full-time',
            ':company_name' => 'Test Company Ltd',
            ':company_id' => $employerId,
            ':status' => 0, // Pending
            ':created_at' => date('Y-m-d H:i:s'),
            ':updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->assertTrue($result, 'Job should be inserted successfully');

        // Verify the job was inserted
        $jobId = self::$pdo->lastInsertId();
        $stmt = self::$pdo->prepare("SELECT * FROM jobs WHERE id = :id");
        $stmt->execute([':id' => $jobId]);
        $job = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($job, 'Job should be found in database');
        $this->assertEquals('Test PHP Developer Position', $job['job_title']);
        $this->assertEquals('Technology', $job['job_category']);
        $this->assertEquals(0, $job['status']);

        // Clean up
        $stmt = self::$pdo->prepare("DELETE FROM jobs WHERE id = :id");
        $stmt->execute([':id' => $jobId]);
        
        // Clean up employer
        $stmt = self::$pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $employerId]);
    }

    /**
     * Test updating job status
     */
    public function testUpdateJobStatus(): void
    {
        // Create test employer and job
        $employerId = $this->createTestEmployer();
        $jobId = $this->createTestJob($employerId);

        // Update job status to approved
        $stmt = self::$pdo->prepare("UPDATE jobs SET status = 'active' WHERE id = :id");
        $result = $stmt->execute([':id' => $jobId]);

        $this->assertTrue($result, 'Job status should be updated');

        // Verify the update
        $stmt = self::$pdo->prepare("SELECT status FROM jobs WHERE id = :id");
        $stmt->execute([':id' => $jobId]);
        $status = $stmt->fetchColumn();

        $this->assertEquals(1, $status, 'Job status should be approved');

        // Clean up
        $stmt = self::$pdo->prepare("DELETE FROM jobs WHERE id = :id");
        $stmt->execute([':id' => $jobId]);
        
        $stmt = self::$pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $employerId]);
    }

    /**
     * Test inserting a new user
     */
    public function testInsertUser(): void
    {
        $sql = "INSERT INTO users (
            username, 
            email, 
            password, 
            fullname,
            role,
            created_at
        ) VALUES (
            :username,
            :email,
            :password,
            :fullname,
            :role,
            :created_at
        )";

        $stmt = self::$pdo->prepare($sql);
        $result = $stmt->execute([
            ':username' => 'testuser_' . uniqid(),
            ':email' => 'test_' . uniqid() . '@example.com',
            ':password' => password_hash('TestPass123', PASSWORD_DEFAULT),
            ':fullname' => 'Test User',
            ':role' => 'employee',
            ':created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->assertTrue($result, 'User should be inserted successfully');

        $userId = self::$pdo->lastInsertId();
        
        // Verify user was inserted
        $stmt = self::$pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($user, 'User should be found in database');
        $this->assertStringStartsWith('testuser_', $user['username']);
        $this->assertEquals('employee', $user['role']);

        // Clean up
        $stmt = self::$pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
    }

    /**
     * Test counting jobs by status
     */
    public function testCountJobsByStatus(): void
    {
        // Create test employer and jobs
        $employerId = $this->createTestEmployer();
        $jobId1 = $this->createTestJob($employerId, 0); // Pending
        $jobId2 = $this->createTestJob($employerId, 1); // Approved

        // Count pending jobs
        $stmt = self::$pdo->prepare("SELECT COUNT(*) FROM jobs WHERE status = 'pending'");
        $stmt->execute();
        $pendingCount = $stmt->fetchColumn();

        $this->assertGreaterThanOrEqual(1, $pendingCount, 'Should have at least 1 pending job');

        // Count approved jobs
        $stmt = self::$pdo->prepare("SELECT COUNT(*) FROM jobs WHERE status = 'active'");
        $stmt->execute();
        $approvedCount = $stmt->fetchColumn();

        $this->assertGreaterThanOrEqual(1, $approvedCount, 'Should have at least 1 approved job');

        // Clean up
        $stmt = self::$pdo->prepare("DELETE FROM jobs WHERE company_id = :company_id");
        $stmt->execute([':company_id' => $employerId]);
        
        $stmt = self::$pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $employerId]);
    }

    /**
     * Helper method to create a test employer
     */
    private function createTestEmployer(): int
    {
        $sql = "INSERT INTO users (username, email, password, fullname, role, created_at) 
                VALUES (:username, :email, :password, :fullname, :role, :created_at)";
        
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([
            ':username' => 'test_employer_' . uniqid(),
            ':email' => 'employer_' . uniqid() . '@test.com',
            ':password' => password_hash('TestPass123', PASSWORD_DEFAULT),
            ':fullname' => 'Test Employer',
            ':role' => 'employer',
            ':created_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) self::$pdo->lastInsertId();
    }

    /**
     * Helper method to create a test job
     */
    private function createTestJob(int $employerId, string $status = 'pending'): int
    {
        $sql = "INSERT INTO jobs (
            job_title, 
            job_category, 
            job_region, 
            job_type, 
            company_name,
            company_id,
            status,
            created_at,
            updated_at
        ) VALUES (
            :job_title,
            :job_category,
            :job_region,
            :job_type,
            :company_name,
            :company_id,
            :status,
            :created_at,
            :updated_at
        )";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([
            ':job_title' => 'Test Job ' . uniqid(),
            ':job_category' => 'Technology',
            ':job_region' => 'Nairobi',
            ':job_type' => 'Full-time',
            ':company_name' => 'Test Company',
            ':company_id' => $employerId,
            ':status' => $status,
            ':created_at' => date('Y-m-d H:i:s'),
            ':updated_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) self::$pdo->lastInsertId();
    }
}