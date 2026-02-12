# URL Health Checker API

[![CI/CD Pipeline](https://github.com/yourusername/url-health-checker/actions/workflows/ci.yml/badge.svg)](https://github.com/yourusername/url-health-checker/actions)

A lightweight PHP REST API for monitoring URL health status. Perfect for learning CI/CD pipelines with a practical, testable project.

## Features

- ✅ **CRUD Operations** - Create, read, update, and delete monitored URLs
- 🔍 **Health Checking** - Automated HTTP status checking with Guzzle
- 🗄️ **PostgreSQL Database** - Robust data persistence with migrations
- 🧪 **Comprehensive Testing** - Unit and integration tests with PHPUnit
- 🚀 **CI/CD Ready** - GitHub Actions pipeline with multi-version testing
- 📊 **Background Monitoring** - CLI script for batch health checks

## Requirements

- PHP 8.1 or higher
- PostgreSQL 12 or higher
- Composer

## Installation

1. **Clone the repository**

   ```bash
   git clone https://github.com/yourusername/url-health-checker.git
   cd url-health-checker
   ```

2. **Install dependencies**

   ```bash
   composer install
   ```

3. **Configure environment**

   ```bash
   cp .env.example .env
   # Edit .env with your PostgreSQL credentials
   ```

4. **Create database**

   ```bash
   createdb url_health_checker
   createdb url_health_checker_test  # For testing
   ```

5. **Run migrations**

   ```bash
   php scripts/migrate.php
   ```

6. **Start the development server**
   ```bash
   php -S localhost:8000 -t public
   ```

The API will be available at `http://localhost:8000/api`

## API Endpoints

### Create URL

```bash
POST /api/urls
Content-Type: application/json

{
  "url": "https://example.com",
  "name": "Example Site"  # optional
}
```

**Response (201)**

```json
{
  "message": "URL added successfully",
  "data": {
    "id": 1,
    "url": "https://example.com",
    "name": "Example Site",
    "status": "pending",
    "last_checked_at": null,
    "last_status_code": null,
    "created_at": "2024-02-12 10:30:00",
    "updated_at": "2024-02-12 10:30:00"
  }
}
```

### List All URLs

```bash
GET /api/urls
```

**Response (200)**

```json
{
  "data": [
    {
      "id": 1,
      "url": "https://example.com",
      "name": "Example Site",
      "status": "online",
      "last_checked_at": "2024-02-12 10:35:00",
      "last_status_code": 200,
      "created_at": "2024-02-12 10:30:00",
      "updated_at": "2024-02-12 10:35:00"
    }
  ],
  "count": 1
}
```

### Get Single URL

```bash
GET /api/urls/{id}
```

### Update URL

```bash
PUT /api/urls/{id}
Content-Type: application/json

{
  "name": "Updated Name",
  "url": "https://newurl.com"
}
```

### Delete URL

```bash
DELETE /api/urls/{id}
```

### Check URL Health

```bash
POST /api/urls/{id}/check
```

**Response (200)**

```json
{
  "message": "Health check completed",
  "data": {
    "id": 1,
    "url": "https://example.com",
    "status": "online",
    "last_status_code": 200,
    "last_checked_at": "2024-02-12 10:40:00"
  }
}
```

## Background Health Checker

Run health checks for all monitored URLs:

```bash
php scripts/check-health.php
```

**Example Output:**

```
Starting health check for all URLs...

Checking: https://google.com ✓ [online] (HTTP 200)
Checking: https://example.com ✓ [online] (HTTP 200)
Checking: https://invalid-url.xyz ✗ [offline] (HTTP N/A)

--------------------------------------------------
Summary:
  Total URLs: 3
  Online: 2
  Offline: 1
--------------------------------------------------
```

### Schedule with Cron

Add to your crontab to run every 5 minutes:

```bash
*/5 * * * * cd /path/to/url-health-checker && php scripts/check-health.php >> /var/log/health-check.log 2>&1
```

## Testing

### Run All Tests

```bash
composer test
```

### Run Unit Tests Only

```bash
composer test:unit
```

### Run Integration Tests Only

```bash
composer test:integration
```

### Generate Code Coverage

```bash
composer test -- --coverage-html coverage
```

### Code Quality Check

```bash
composer cs-check
```

### Auto-fix Code Style

```bash
composer cs-fix
```

## CI/CD Pipeline

The project includes a GitHub Actions workflow that:

- ✅ Tests on PHP 8.1, 8.2, and 8.3
- ✅ Runs unit and integration tests
- ✅ Generates code coverage reports
- ✅ Performs code quality checks with PHP CodeSniffer
- ✅ Uses PostgreSQL service container for testing

The pipeline runs automatically on:

- Push to `main` or `develop` branches
- Pull requests to `main` or `develop` branches

## Project Structure

```
url-health-checker/
├── src/
│   ├── Controllers/
│   │   └── UrlController.php      # API endpoint handlers
│   ├── Database/
│   │   ├── Connection.php         # Database connection
│   │   └── Migrations/
│   │       └── CreateUrlsTable.php
│   ├── Models/
│   │   └── Url.php                # URL entity
│   ├── Repositories/
│   │   └── UrlRepository.php      # Database operations
│   ├── Services/
│   │   └── HealthChecker.php      # HTTP health checking
│   └── Router.php                 # Request routing
├── public/
│   └── index.php                  # Application entry point
├── tests/
│   ├── Unit/                      # Unit tests
│   └── Integration/               # Integration tests
├── scripts/
│   ├── migrate.php                # Database migrations
│   └── check-health.php           # Background checker
└── .github/
    └── workflows/
        └── ci.yml                 # CI/CD pipeline
```

## Database Schema

### `urls` Table

| Column           | Type          | Description                      |
| ---------------- | ------------- | -------------------------------- |
| id               | SERIAL        | Primary key                      |
| url              | VARCHAR(2048) | URL to monitor (unique)          |
| name             | VARCHAR(255)  | Optional friendly name           |
| status           | VARCHAR(20)   | Status: pending, online, offline |
| last_checked_at  | TIMESTAMP     | Last health check time           |
| last_status_code | INTEGER       | Last HTTP status code            |
| created_at       | TIMESTAMP     | Record creation time             |
| updated_at       | TIMESTAMP     | Last update time                 |

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is open-source and available under the MIT License.

## Acknowledgments

Built as a practical example for learning CI/CD pipelines with PHP, focusing on:

- Database migrations
- Comprehensive testing strategies
- Mocking external HTTP calls
- Automated testing in CI/CD environments
