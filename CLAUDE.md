# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 application with authentication, user management, wallet system, and transaction tracking features. The application uses Laravel Fortify for authentication (including two-factor authentication), Spatie Laravel Permission for role-based access control, and includes a comprehensive wallet/transaction system with fee management.

## Development Commands

### Running the Application
```bash
# Start full development environment (server, queue, logs, vite)
composer dev

# Or run individually:
php artisan serve                    # Start development server
php artisan queue:listen --tries=1   # Start queue worker
php artisan pail --timeout=0        # Start log viewer
npm run dev                          # Start Vite development server
```

### Testing
```bash
# Run all tests
composer test
# or
php artisan test

# Run specific test file
php artisan test tests/Feature/ExampleTest.php

# Run with coverage
php artisan test --coverage
```

### Build Commands
```bash
# Build frontend assets
npm run build

# Code formatting
vendor/bin/pint                      # PHP code style fixer (Laravel Pint)
```

### Database Operations
```bash
# Run migrations
php artisan migrate

# Fresh migration with seeding
php artisan migrate:fresh --seed

# Rollback migrations
php artisan migrate:rollback

# Create new migration
php artisan make:migration create_table_name
```

## Application Architecture

### Authentication & Authorization
- **Laravel Fortify**: Handles authentication including two-factor authentication, password resets, email verification, and profile updates
- **Spatie Laravel Permission**: Role and permission management with caching enabled
- **Email Verification**: Configurable via SystemSetting model
- **Custom User Actions**: Located in `app/Actions/Fortify/` for user creation, profile updates, and password management
- Custom User model with wallet relationship and enhanced email verification logic

### Core Models
- **User**: Extended with wallet relationships, two-factor auth, roles/permissions
- **Wallet**: One-to-one relationship with User
- **Transaction**: Belongs to User, tracks financial transactions
- **SystemSetting**: Key-value configuration storage

### Key Directories
- `app/Actions/Fortify/`: Custom Fortify action classes
- `app/Http/Controllers/`: Standard Laravel controllers
- `app/Http/Middleware/`: Custom middleware
- `app/Models/`: Eloquent models with relationships
- `app/Console/Commands/`: Custom Artisan commands
- `config/fortify.php`: Fortify authentication configuration
- `config/permission.php`: Spatie permission configuration

### Frontend
- **Vite**: Asset bundling and development server (configured in `vite.config.js`)
- **Tailwind CSS 4.0**: Utility-first CSS framework (via @tailwindcss/vite plugin)
- **Axios**: HTTP client for API requests
- **Concurrently**: Runs multiple development processes simultaneously

### Testing
- **PHPUnit**: Primary testing framework
- SQLite in-memory database for testing
- Test suites: Unit and Feature tests in `tests/` directory

## Common Development Tasks

### Creating New Features
1. Create migration: `php artisan make:migration create_table_name`
2. Create model: `php artisan make:model ModelName -m` (with migration)
3. Create controller: `php artisan make:controller ControllerName`
4. Add routes in `routes/web.php`
5. Create tests: `php artisan make:test FeatureNameTest`

### Working with Roles and Permissions
The application includes a custom Artisan command for role management:

```bash
# Assign member roles to users
php artisan assign:member-roles
```

Note: The system uses Spatie Laravel Permission. Permission and role management is handled through the package's standard methods.

### Queue Management
```bash
# Process queue jobs
php artisan queue:work

# List failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

## Important Configuration Details

### Fortify Configuration (`config/fortify.php`)
- Home redirect path: `/dashboard`
- Features enabled: Registration, password reset, email verification, profile updates, 2FA
- Uses web middleware with throttling (60 requests per minute)
- Two-factor authentication with confirmation and password confirmation enabled

### Permission System Configuration (`config/permission.php`)
- 24-hour cache for permissions
- Teams feature disabled
- No wildcard permissions enabled
- Events disabled for performance

### Testing Environment
- Uses SQLite in-memory database for tests
- Array drivers for cache, mail, session
- Bcrypt rounds reduced to 4 for faster tests
- Test database automatically configured in `phpunit.xml`