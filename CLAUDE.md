# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 application with authentication, user management, wallet system, transaction tracking features, and a comprehensive e-commerce system. The application uses Laravel Fortify for authentication (including two-factor authentication), Spatie Laravel Permission for role-based access control, and includes a comprehensive wallet/transaction system with fee management.

**E-Commerce Status**: Phase 2 Complete ✅ (Package Management + Shopping Cart System)

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
- **Email Verification**: Dual-level configurable via SystemSetting model (registration + ongoing verification)
- **Custom User Actions**: Located in `app/Actions/Fortify/` for user creation, profile updates, and password management
- Custom User model with wallet relationship and enhanced email verification logic

### Core Models
- **User**: Extended with wallet relationships, two-factor auth, roles/permissions
- **Wallet**: One-to-one relationship with User
- **Transaction**: Belongs to User, tracks financial transactions
- **SystemSetting**: Key-value configuration storage
- **Package**: E-commerce packages with pricing, points, inventory, and media management

### Key Directories
- `app/Actions/Fortify/`: Custom Fortify action classes
- `app/Http/Controllers/`: Standard Laravel controllers
- `app/Http/Controllers/Admin/`: Admin-specific controllers (packages, etc.)
- `app/Http/Middleware/`: Custom middleware (including CartMiddleware)
- `app/Models/`: Eloquent models with relationships
- `app/Services/`: Business logic services (CartService, etc.)
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

### E-Commerce System
**Current Status**: Phase 2 Complete ✅ (Package Management + Shopping Cart)

#### Package Management System ✅
- **Admin Interface**: Full CRUD operations for packages via `/admin/packages`
- **Package Model**: Comprehensive model with pricing, inventory, points, metadata
- **Public Browsing**: Package catalog with search, filtering, and sorting at `/packages`
- **SEO-Friendly URLs**: Slug-based routing for individual packages
- **Image Management**: Upload handling with fallback placeholder system
- **Inventory Tracking**: Quantity management with availability checking

#### Shopping Cart System ✅
- **Session-Based Cart**: Persistent cart with real-time updates
- **CartService**: Comprehensive service class for all cart operations
- **AJAX Operations**: Add, update, remove cart items without page reload
- **Cart UI**: Header dropdown, full cart page with professional design
- **Validation**: Inventory checking and quantity validation
- **Tax Calculation**: Fully configurable tax system via admin settings (auto-hides when 0%)

#### Available URLs
- **Admin Package Management**: `/admin/packages` (full CRUD)
- **Admin Application Settings**: `/admin/application-settings` (tax rate, email verification)
- **Public Package Browsing**: `/packages` (listing with search/sort)
- **Individual Package Pages**: `/packages/{slug}` (SEO-friendly)
- **Shopping Cart**: `/cart` (full cart management)
- **Cart API**: AJAX endpoints for cart operations

#### Next Phase
- **Phase 3**: Checkout process with order management system
- **Phase 4**: Wallet payment integration with existing transaction system

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

### E-Commerce Development Tasks

#### Application Settings Management
```bash
# Seed application settings
php artisan db:seed --class=SystemSettingSeeder

# Access application settings
# Navigate to: /admin/application-settings

# Configure tax rate and email verification
# Settings take effect immediately
```

#### Package Management
```bash
# Create new packages via seeder
php artisan db:seed --class=PackageSeeder

# Access admin package management
# Navigate to: /admin/packages

# Generate package test data
php artisan tinker
# >>> Package::factory(10)->create()
```

#### Shopping Cart System
- **Cart Service**: Use `app/Services/CartService.php` for cart operations
- **Cart Middleware**: Global cart data available via `CartMiddleware`
- **AJAX Operations**: All cart operations support AJAX with JSON responses
- **Session Storage**: Cart persists across browser sessions

#### Development URLs for Testing
- **Admin Package CRUD**: `http://localhost:8000/admin/packages`
- **Admin Application Settings**: `http://localhost:8000/admin/application-settings`
- **Public Package Catalog**: `http://localhost:8000/packages`
- **Shopping Cart**: `http://localhost:8000/cart`
- **Package Details**: `http://localhost:8000/packages/{slug}`

## Important Configuration Details

### Application Settings System
**Location**: `/admin/application-settings`
**Controller**: `app/Http/Controllers/Admin/AdminSettingsController.php`

#### Configurable Settings:
- **Tax Rate**: E-commerce tax rate (0.0 to 1.0 decimal)
  - When set to 0: Tax calculation and display are completely hidden
  - Dynamic percentage display with real-time updates
  - Affects all cart calculations immediately

- **Email Verification After Registration**: Controls new user verification requirements
  - When enabled: New users must verify email before first login
  - When disabled: Users can login immediately after registration (auto-verified)
  - Separate from ongoing login verification in System Settings

#### Email Verification System (Dual-Level):
1. **Registration Verification** (`email_verification_required`):
   - Controlled via Application Settings
   - Affects new user registration workflow
   - Handled by `CreateNewUser` action and `ConditionalEmailVerification` middleware

2. **Ongoing Login Verification** (`email_verification_enabled`):
   - Controlled via System Settings (`/admin/system-settings#security`)
   - Affects existing users during login sessions
   - Independent from registration verification

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

### E-Commerce Configuration Details

#### Package System
- **Package Model**: Located at `app/Models/Package.php`
- **Soft Deletes**: Enabled to protect packages with existing orders
- **Image Storage**: Uses Laravel's storage system with fallback placeholder
- **SEO URLs**: Automatic slug generation from package names
- **Metadata Storage**: JSON fields for flexible package information

#### Cart System
- **Storage**: Session-based (upgradeable to database later)
- **Tax System**: Fully configurable via Application Settings
  - Dynamic tax rate (0% to 100%)
  - Auto-hides tax display when rate is 0%
  - Real-time calculations with `show_tax` flag
- **UI Improvements**: Enhanced desktop layout with aligned remove buttons
- **Mobile Optimization**: Proper spacing between cart cards
- **Validation**: Real-time inventory checking and quantity limits
- **Global Access**: Cart data available in all views via middleware
- **AJAX API**: RESTful endpoints for all cart operations

#### Key Files Created
**Models & Services:**
- `app/Models/Package.php` - Package model with business logic
- `app/Services/CartService.php` - Comprehensive cart management service

**Controllers:**
- `app/Http/Controllers/Admin/AdminPackageController.php` - Admin package CRUD
- `app/Http/Controllers/Admin/AdminSettingsController.php` - Application settings management
- `app/Http/Controllers/PackageController.php` - Public package browsing
- `app/Http/Controllers/CartController.php` - Cart operations and API

**Middleware:**
- `app/Http/Middleware/CartMiddleware.php` - Global cart data injection

**Database:**
- `database/migrations/*_create_packages_table.php` - Package database structure
- `database/seeders/PackageSeeder.php` - Sample package data
- `database/seeders/SystemSettingSeeder.php` - Application settings seeder

**Views:**
- `resources/views/admin/packages/` - Complete admin interface
- `resources/views/admin/settings/index.blade.php` - Application settings interface
- `resources/views/packages/` - Public package browsing
- `resources/views/cart/index.blade.php` - Full cart management page

#### Next Development Steps
For continuing e-commerce development, refer to `ecommerce_roadmap.md` for detailed Phase 3-8 implementation plans including checkout process, wallet payment integration, and order management system.