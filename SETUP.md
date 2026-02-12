# Quick Setup Guide

## Prerequisites Check

Before running the application, ensure you have:

1. **PHP 8.1+** installed

   ```bash
   php --version
   ```

2. **PostgreSQL** installed and running

   ```bash
   psql --version
   ```

3. **Composer** installed
   ```bash
   composer --version
   ```

## PostgreSQL Setup

### Install PostgreSQL (if not installed)

**macOS (using Homebrew):**

```bash
brew install postgresql@15
brew services start postgresql@15
```

**Ubuntu/Debian:**

```bash
sudo apt update
sudo apt install postgresql postgresql-contrib
sudo systemctl start postgresql
```

### Create Databases

```bash
# Create development database
createdb url_health_checker

# Create test database
createdb url_health_checker_test
```

### Configure PostgreSQL User (if needed)

If you need to set a password for the postgres user:

```bash
psql postgres
```

Then in the PostgreSQL prompt:

```sql
ALTER USER postgres WITH PASSWORD 'your_password';
\q
```

## Application Setup

1. **Configure environment**

   Edit the `.env` file and set your PostgreSQL password:

   ```
   DB_PASSWORD=your_password_here
   ```

2. **Run migrations**

   ```bash
   php scripts/migrate.php
   ```

3. **Start the server**
   ```bash
   php -S localhost:8000 -t public
   ```

## Verify Installation

### Test the API

```bash
# Create a URL
curl -X POST http://localhost:8000/api/urls \
  -H "Content-Type: application/json" \
  -d '{"url":"https://google.com","name":"Google"}'

# List all URLs
curl http://localhost:8000/api/urls

# Check health
curl -X POST http://localhost:8000/api/urls/1/check
```

### Run Tests

```bash
# Make sure test database exists
createdb url_health_checker_test

# Update phpunit.xml with your PostgreSQL password if needed
# Then run tests
composer test:unit
```

## Troubleshooting

### "connection to server failed: no password supplied"

Update your `.env` file with the correct PostgreSQL password:

```
DB_PASSWORD=your_actual_password
```

### "database does not exist"

Create the databases:

```bash
createdb url_health_checker
createdb url_health_checker_test
```

### Port 8000 already in use

Use a different port:

```bash
php -S localhost:8080 -t public
```

## Next Steps

1. ✅ Set up PostgreSQL
2. ✅ Run migrations
3. ✅ Test the API endpoints
4. ✅ Run the test suite
5. ✅ Set up GitHub Actions (push to GitHub)
6. ✅ Schedule background health checks with cron

See the main [README.md](README.md) for complete API documentation.
