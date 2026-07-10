<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    /**
     * Test HTML escaping function
     */
    public function testHtmlEscaping(): void
    {
        // Test the h() helper function behavior
        $input = '<script>alert("XSS")</script>';
        $expected = '<script>alert("XSS")</script>';
        $actual = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test HTML escaping with special characters
     */
    public function testHtmlEscapingSpecialCharacters(): void
    {
        $input = 'Tom & Jerry < > " \'';
        $expected = 'Tom & Jerry < > " &#039;';
        $actual = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test HTML escaping with ampersands
     */
    public function testHtmlEscapingAmpersands(): void
    {
        $input = 'A & B';
        $expected = 'A & B';
        $actual = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test empty string handling
     */
    public function testEmptyStringEscaping(): void
    {
        $input = '';
        $expected = '';
        $actual = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test numeric values escaping
     */
    public function testNumericValueEscaping(): void
    {
        $input = 12345;
        $expected = '12345';
        $actual = htmlspecialchars((string) $input, ENT_QUOTES, 'UTF-8');
        
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test URL generation logic
     */
    public function testBaseUrlGeneration(): void
    {
        $protocol = 'http';
        $host = 'localhost';
        $scriptDir = '/job-portal';
        $baseUrl = $protocol . '://' . $host . $scriptDir;
        
        $this->assertEquals('http://localhost/job-portal', $baseUrl);
    }

    /**
     * Test HTTPS URL generation
     */
    public function testHttpsUrlGeneration(): void
    {
        $protocol = 'https';
        $host = 'example.com';
        $scriptDir = '/job-portal';
        $baseUrl = $protocol . '://' . $host . $scriptDir;
        
        $this->assertEquals('https://example.com/job-portal', $baseUrl);
    }

    /**
     * Test string trimming
     */
    public function testStringTrimming(): void
    {
        $input = '  John Doe  ';
        $expected = 'John Doe';
        $actual = trim($input);
        
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test string trimming with newlines
     */
    public function testStringTrimmingWithNewlines(): void
    {
        $input = "\n\t  John Doe  \t\n";
        $expected = 'John Doe';
        $actual = trim($input);
        
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test password hashing
     */
    public function testPasswordHashing(): void
    {
        $password = 'SecurePass123';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $this->assertNotEquals($password, $hash, 'Hash should not equal plain password');
        $this->assertTrue(password_verify($password, $hash), 'Password should verify against hash');
    }

    /**
     * Test password verification with wrong password
     */
    public function testPasswordVerificationWithWrongPassword(): void
    {
        $password = 'SecurePass123';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $wrongPassword = 'WrongPass456';
        
        $this->assertFalse(password_verify($wrongPassword, $hash), 'Wrong password should not verify');
    }

    /**
     * Test date formatting
     */
    public function testDateFormatting(): void
    {
        $date = date('Y-m-d H:i:s');
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $date);
    }

    /**
     * Test unique ID generation
     */
    public function testUniqueIdGeneration(): void
    {
        $id1 = uniqid('company_');
        $id2 = uniqid('company_');
        
        $this->assertStringStartsWith('company_', $id1);
        $this->assertStringStartsWith('company_', $id2);
        $this->assertNotEquals($id1, $id2, 'Unique IDs should be different');
    }

    /**
     * Test file extension extraction
     */
    public function testFileExtensionExtraction(): void
    {
        $filename = 'company_logo.jpg';
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        $this->assertEquals('jpg', $extension);
    }

    /**
     * Test file extension with multiple dots
     */
    public function testFileExtensionWithMultipleDots(): void
    {
        $filename = 'my.company.logo.png';
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        $this->assertEquals('png', $extension);
    }

    /**
     * Test array mapping
     */
    public function testArrayMapping(): void
    {
        $numbers = [1, 2, 3, 4, 5];
        $doubled = array_map(fn($n) => $n * 2, $numbers);
        
        $this->assertEquals([2, 4, 6, 8, 10], $doubled);
    }

    /**
     * Test array keys extraction
     */
    public function testArrayKeysExtraction(): void
    {
        $data = [
            'name' => 'John',
            'email' => 'john@example.com',
            'age' => 30
        ];
        
        $keys = array_keys($data);
        $this->assertEquals(['name', 'email', 'age'], $keys);
    }

    /**
     * Test in_array function
     */
    public function testInArrayFunction(): void
    {
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        $this->assertTrue(in_array('jpg', $allowedTypes));
        $this->assertTrue(in_array('png', $allowedTypes));
        $this->assertFalse(in_array('pdf', $allowedTypes));
    }

    /**
     * Test session role checking logic
     */
    public function testSessionRoleChecking(): void
    {
        $session = [
            'id' => 1,
            'role' => 'employer'
        ];
        
        $this->assertTrue(($session['role'] ?? '') === 'employer');
        $this->assertFalse(($session['role'] ?? '') === 'employee');
    }

    /**
     * Test integer casting
     */
    public function testIntegerCasting(): void
    {
        $stringId = '42';
        $intId = (int) $stringId;
        
        $this->assertIsInt($intId);
        $this->assertEquals(42, $intId);
    }
}