# Testing Guide

## Running Tests

### Unit Tests Only (No Server Required)

```bash
composer test:unit
```

**Output:**

```
OK (13 tests, 31 assertions)
```

Unit tests include:

- ✅ UrlRepository CRUD operations
- ✅ HealthChecker service with mocked HTTP client
- ✅ All edge cases (errors, redirects, network failures)

---

### Integration Tests (Requires Running Server)

Integration tests need a live PHP server running. Here's how to run them:

**Terminal 1 - Start the server:**

```bash
php -S localhost:8000 -t public
```

**Terminal 2 - Run integration tests:**

```bash
composer test:integration
```

Or run all tests together:

```bash
composer test
```

**What happens if server isn't running?**

Integration tests will be **gracefully skipped** with this message:

```
Integration tests require a running server. Start with: php -S localhost:8000 -t public
```

---

### Full Test Suite

```bash
# Start server in background (macOS/Linux)
php -S localhost:8000 -t public > /dev/null 2>&1 &

# Run all tests
composer test

# Stop background server when done
pkill -f "php -S localhost:8000"
```

---

### Code Coverage

```bash
composer test -- --coverage-html coverage
open coverage/index.html  # macOS
```

---

### Code Quality

```bash
# Check code style
composer cs-check

# Auto-fix code style issues
composer cs-fix
```

---

## Test Structure

### Unit Tests (`tests/Unit/`)

- **UrlRepositoryTest.php** - Database operations
  - Create, read, update, delete URLs
  - Uses test PostgreSQL database
- **HealthCheckerTest.php** - HTTP health checking
  - Mocked Guzzle client (no real HTTP calls)
  - Tests success, errors, redirects, network failures

### Integration Tests (`tests/Integration/`)

- **UrlApiTest.php** - Full API endpoint testing
  - Real HTTP requests via cURL
  - Tests all 6 API endpoints
  - Validates JSON responses and status codes

---

## Continuous Integration

The GitHub Actions workflow automatically:

1. Starts PostgreSQL service
2. Runs migrations
3. Executes all tests on PHP 8.1, 8.2, 8.3
4. Checks code quality
5. Generates coverage reports

See [.github/workflows/ci.yml](file:///Users/suhailsolahuddin/Desktop/Personal/url-health-checker/.github/workflows/ci.yml) for details.
