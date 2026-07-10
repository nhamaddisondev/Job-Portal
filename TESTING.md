# Testing Guide for Job Portal

This document provides comprehensive instructions for testing the Job Portal application.

## Table of Contents
- [Overview](#overview)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Running Tests](#running-tests)
- [Test Structure](#test-structure)
- [Writing Tests](#writing-tests)
- [Database Testing](#database-testing)
- [Continuous Integration](#continuous-integration)

## Overview

The Job Portal uses **PHPUnit** for testing with two main test types:

1. **Unit Tests** - Test individual functions and business logic in isolation
2. **Integration Tests** - Test database operations and workflows

## Prerequisites

- PHP 7.4 or higher
- Composer (PHP package manager)
- MySQL database (for integration tests)
- XAMPP or similar local development environment

## Installation

### 1. Install Composer Dependencies

```bash
composer install
```

This will install PHPUnit and all required dependencies.

### 2. Set Up Test Database (Optional)

For integration tests, create a test database:

```sql
CREATE DATABASE online_jobs_portal_test;
```

Or use your existing database by updating `phpunit.xml` environment variables.

## Running Tests

### Run All Tests

```bash
composer test
```

Or directly with PHPUnit:

```bash
vendor/bin/phpunit
```

### Run Only Unit Tests

```bash
vendor/bin/phpunit --testsuite "Unit Tests"
```

### Run Only Integration Tests

```bash
vendor/bin/phpunit --testsuite "Integration Tests"
```

### Run Specific Test File

```bash
vendor/bin/phpunit tests/Unit/ValidationTest.php
```

### Run Specific Test Method

```bash
vendor/bin/phpunit --filter testValidEmailAddresses
```

### Run with Coverage Report

```bash
vendor/bin/phpunit --coverage-html coverage/
```

Then open `coverage/index.html` in your browser.

## Test Structure

```
tests/
├── bootstrap.php              # Test setup and configuration
├── Unit/
│   ├── ValidationTest.php    # Form validation tests
│   └── HelpersTest.php       # Helper function tests
└── Integration/
    └── JobPostingTest.php    # Database operation tests
```

## Writing Tests

### Unit Test Example

```php
<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
    public function testSomething(): void
    {
        $result = someFunction();
        $this->assertEquals('expected', $result);
    }
}
```

### Integration Test Example

```php
<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PDO;

class MyIntegrationTest extends TestCase
{
    private static ?PDO $pdo = null;

    public static function setUpBeforeClass(): void
    {
        // Database connection setup
        self::$pdo = new PDO(/* ... */);
    }

    public function testDatabaseOperation(): void
    {
        // Test database operations
        $this->assertTrue(true);
    }
}
```

## Database Testing

### Test Database Configuration

The test database configuration is in `phpunit.xml`:

```xml
<php>
    <env name="DB_HOST" value="localhost"/>
    <env name="DB_NAME" value="online_jobs_portal_test"/>
    <env name="DB_USER" value="root"/>
    <env name="DB_PASS" value=""/>
    <env name="DB_PORT" value="3307"/>
</php>
```

### Using a Separate Test Database

It's recommended to use a separate test database to avoid affecting production data:

```bash
# Create test database
mysql -u root -p -e "CREATE DATABASE online_jobs_portal_test;"

# Import schema (if you have one)
mysql -u root -p online_jobs_portal_test < database/schema.sql
```

### Test Data Cleanup

Integration tests automatically clean up test data after each test to maintain database integrity.

## Test Coverage

### Current Test Coverage

- **Validation Tests**: Email, password, required fields
- **Helper Tests**: HTML escaping, URL generation, password hashing
- **Integration Tests**: Database operations, job posting, user creation

### Areas to Test Next

- [ ] Authentication (login/logout)
- [ ] Job application workflow
- [ ] File upload validation
- [ ] Admin operations
- [ ] API endpoints
- [ ] Search functionality

## Common Test Commands

```bash
# Run all tests
composer test

# Run with verbose output
vendor/bin/phpunit --verbose

# Run specific test suite
vendor/bin/phpunit --testsuite "Unit Tests"

# Stop on first failure
vendor/bin/phpunit --stop-on-failure

# Run in parallel (requires PHPUnit 9+)
vendor/bin/phpunit --parallel

# Generate coverage report
vendor/bin/phpunit --coverage-html coverage/
```

## Continuous Integration

### GitHub Actions Example

Create `.github/workflows/tests.yml`:

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: online_jobs_portal_test
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.0
          extensions: mbstring, pdo_mysql
          
      - name: Install Composer dependencies
        run: composer install --no-progress --no-suggest
        
      - name: Run tests
        run: vendor/bin/phpunit
        env:
          DB_HOST: 127.0.0.1
          DB_NAME: online_jobs_portal_test
          DB_USER: root
          DB_PASS: root
```

## Troubleshooting

### Database Connection Issues

If integration tests are skipped:
1. Verify MySQL is running
2. Check database credentials in `phpunit.xml`
3. Ensure test database exists

### Composer Issues

If `composer install` fails:
1. Update Composer: `composer self-update`
2. Clear cache: `composer clear-cache`
3. Try again: `composer install`

### PHP Version Issues

Ensure you're running PHP 7.4 or higher:
```bash
php --version
```

## Best Practices

1. **Write tests before code** (TDD approach)
2. **Keep tests independent** - each test should run in isolation
3. **Use meaningful test names** - describe what the test verifies
4. **Clean up test data** - always clean up after integration tests
5. **Mock external dependencies** - use mocks for APIs, file systems, etc.
6. **Test edge cases** - don't just test the happy path
7. **Run tests frequently** - integrate testing into your development workflow

## Next Steps

1. Run the existing tests: `composer test`
2. Add tests for critical features (authentication, job applications)
3. Set up a CI/CD pipeline for automated testing
4. Aim for 80%+ code coverage
5. Add E2E tests with Cypress or Playwright

## Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Composer Documentation](https://getcomposer.org/doc/)
- [Test-Driven Development Guide](https://en.wikipedia.org/wiki/Test-driven_development)

## Support

For issues or questions about testing, refer to the project documentation or create an issue in the repository.