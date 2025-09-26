# Laravel E-Wallet System

A comprehensive digital wallet solution built with Laravel 12, featuring secure transactions, admin controls, and user-friendly interfaces for managing digital payments.

## 🚀 Features

### Core Functionality
- **Digital Wallet Management**: Secure wallet creation and balance tracking
- **Multiple Transaction Types**: Deposits, withdrawals, transfers, and fee management
- **Real-time Processing**: Instant transfers with configurable fees
- **Admin Controls**: Complete transaction oversight and system configuration
- **Role-based Access**: Secure permission system using Spatie Laravel Permission
- **Custom Error Handling**: Professional error pages with user guidance

### Security Features
- **CSRF Protection**: All forms protected against cross-site request forgery
- **Permission-based Access**: Granular control over user capabilities
- **Session Management**: Secure authentication with Laravel Fortify
- **Data Validation**: Comprehensive input validation and sanitization
- **Transaction Logging**: Complete audit trail for all financial operations

### User Experience
- **Responsive Design**: Mobile-first design using Tailwind CSS
- **Real-time Updates**: Dynamic fee calculations and balance updates
- **Pagination**: Efficient handling of large transaction volumes
- **Custom Error Pages**: User-friendly error handling with clear guidance
- **Intuitive Navigation**: Easy-to-use interface for all user types

## 📋 Table of Contents

- [System Requirements](#system-requirements)
- [Installation Guide](#installation-guide)
- [Configuration](#configuration)
- [Admin Documentation](#admin-documentation)
- [User Documentation](#user-documentation)
- [API Reference](#api-reference)
- [Security Considerations](#security-considerations)
- [Troubleshooting](#troubleshooting)
- [FAQ](#faq)
- [Support](#support)

## 🛠 System Requirements

### Server Requirements
- **PHP**: 8.2 or higher
- **Composer**: Latest version
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **SSL Certificate**: Required for production deployment

### PHP Extensions
- BCMath PHP Extension
- Ctype PHP Extension
- Fileinfo PHP Extension
- JSON PHP Extension
- Mbstring PHP Extension
- OpenSSL PHP Extension
- PDO PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension

### Development Environment
- **Node.js**: 16.0+ (for asset compilation)
- **NPM/Yarn**: For frontend dependencies

## 🔧 Installation Guide

### 1. Clone the Repository

```bash
git clone <repository-url> laravel-ewallet
cd laravel-ewallet
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies (if applicable)
npm install
```

### 3. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup

Edit your `.env` file with database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_ewallet
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Run migrations and seeders:

```bash
# Run database migrations
php artisan migrate

# Seed the database with initial data
php artisan db:seed
```

### 5. Storage and Cache Configuration

```bash
# Create storage symlink
php artisan storage:link

# Clear and optimize caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Set Permissions

```bash
# Set proper permissions for storage and cache
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

### 7. Start the Application

```bash
# Development server
php artisan serve

# Or configure your web server to point to the public directory
```

## ⚙️ Configuration

### Environment Variables

Key configuration options in your `.env` file:

```env
# Application
APP_NAME="E-Wallet System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_ewallet
DB_USERNAME=username
DB_PASSWORD=password

# Mail Configuration (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Session Configuration
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Cache Configuration
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
```

### Initial Setup

After installation, configure the system:

1. **Create Admin User**:
```bash
php artisan tinker
```
```php
$user = App\Models\User::create([
    'name' => 'System Administrator',
    'email' => 'admin@yourdomain.com',
    'password' => bcrypt('secure_password'),
    'email_verified_at' => now()
]);
$user->assignRole('admin');
```

2. **Configure System Settings**:
   - Navigate to `/admin/system-settings`
   - Configure wallet settings
   - Set up transaction fees
   - Configure approval workflows

## 👤 Admin Documentation

### Admin Dashboard Access

Admins can access the administrative interface at `/admin/dashboard` after logging in with an admin account.

### Key Admin Features

#### 1. Transaction Approval System
**Location**: `/admin/transaction-approval`

**Capabilities**:
- View all pending transactions in real-time
- Approve or reject deposits and withdrawals
- Add administrative notes to transactions
- Bulk operations for multiple transactions
- Real-time statistics updates

**Workflow**:
1. Navigate to Transaction Approval page
2. Review pending transactions with user details
3. Click "Approve" or "Reject" for individual transactions
4. Add notes when rejecting transactions
5. Statistics update automatically upon action

#### 2. System Settings Management
**Location**: `/admin/system-settings`

**Transfer Fee Configuration**:
- Enable/disable transfer fees
- Set percentage or fixed fee structure
- Configure minimum and maximum fee limits
- Real-time fee calculation preview

**Withdrawal Fee Configuration**:
- Enable/disable withdrawal fees (non-refundable)
- Set percentage or fixed fee structure
- Configure minimum and maximum fee limits
- Withdrawal fees are deducted immediately upon request

**Settings Options**:
```
Transfer Fees:
- Enabled: Yes/No
- Type: Percentage (%) or Fixed Amount ($)
- Value: Fee amount or percentage
- Minimum Fee: $0.50
- Maximum Fee: $25.00

Withdrawal Fees:
- Enabled: Yes/No
- Type: Percentage (%) or Fixed Amount ($)
- Value: Fee amount or percentage
- Minimum Fee: $1.00
- Maximum Fee: $50.00
```

#### 3. User Management
**Location**: `/admin/users`

**Capabilities**:
- View all registered users
- Manage user roles and permissions
- View user wallet information
- Access user transaction history

#### 4. Wallet Management
**Location**: `/admin/wallet-management`

**Features**:
- Monitor all user wallets
- View wallet balances and status
- Freeze/unfreeze wallets
- Transaction volume analysis

### Admin Best Practices

1. **Transaction Approval**:
   - Review high-value transactions carefully
   - Always provide clear rejection reasons
   - Monitor for suspicious transaction patterns

2. **Fee Management**:
   - Test fee configurations before enabling
   - Consider user impact when setting fee amounts
   - Monitor fee revenue and user satisfaction

3. **Security**:
   - Regularly review admin access logs
   - Use strong passwords and enable 2FA
   - Monitor system for unusual activity

## 👥 User Documentation

### Getting Started

#### 1. Account Registration
1. Visit the registration page
2. Provide required information:
   - Full Name
   - Email Address
   - Secure Password
   - Password Confirmation
3. Verify email address (if email verification enabled)
4. Complete profile setup

#### 2. Dashboard Overview
**Location**: `/dashboard`

The user dashboard provides:
- Current wallet balance
- Wallet status (Active/Frozen)
- Recent transaction summary
- Quick action buttons
- Transaction history preview

### Wallet Features

#### 1. Deposit Funds
**Location**: `/wallet/deposit`

**Process**:
1. Navigate to Deposit page
2. Enter deposit amount ($1 - $10,000)
3. Select payment method:
   - Credit Card
   - Bank Transfer
   - PayPal
4. Submit deposit request
5. Wait for admin approval

**Important Notes**:
- Minimum deposit: $1.00
- Maximum deposit: $10,000.00
- All deposits require admin approval
- Processing time: 1-3 business days

#### 2. Transfer Money
**Location**: `/wallet/transfer`

**Process**:
1. Enter recipient email or username
2. Specify transfer amount
3. Add optional transfer note
4. Review transfer summary including fees
5. Confirm transfer

**Transfer Features**:
- Real-time fee calculation
- Instant processing
- Transfer confirmation
- Transaction reference numbers

**Transfer Limits**:
- Minimum transfer: $1.00
- Maximum transfer: $10,000.00 or wallet balance
- Transfers are irreversible once confirmed

#### 3. Withdraw Funds
**Location**: `/wallet/withdraw`

**Process**:
1. Enter withdrawal amount
2. Provide bank account details:
   - Account Number
   - Bank Name
   - Routing Number (if applicable)
3. Accept terms and conditions
4. Review withdrawal summary including fees
5. Submit withdrawal request

**Withdrawal Features**:
- Real-time fee calculation
- Non-refundable withdrawal fees
- Admin approval required
- Processing time: 1-3 business days

**Important Notes**:
- Withdrawal fees are deducted immediately
- Even if withdrawal is rejected, fees are not refunded
- Minimum withdrawal: $1.00
- Maximum withdrawal: $10,000.00

#### 4. Transaction History
**Location**: `/wallet/transactions`

**Features**:
- Complete transaction history
- Pagination controls (10, 20, 50, 100 per page)
- Transaction status indicators
- Reference number tracking
- Export capabilities

**Transaction Types**:
- **Deposits**: Incoming funds from external sources
- **Withdrawals**: Outgoing funds to bank accounts
- **Transfers In**: Money received from other users
- **Transfers Out**: Money sent to other users
- **Transfer Fees**: Charges for sending money
- **Withdrawal Fees**: Processing fees for withdrawals

**Transaction Status**:
- **Pending**: Awaiting admin approval
- **Approved**: Transaction completed successfully
- **Rejected**: Transaction declined by admin

### User Best Practices

1. **Security**:
   - Use strong, unique passwords
   - Log out when using shared computers
   - Verify recipient details before transfers
   - Keep transaction references for records

2. **Financial Management**:
   - Monitor your transaction history regularly
   - Understand fee structures before transactions
   - Plan for withdrawal processing times
   - Keep adequate balance for fees

3. **Troubleshooting**:
   - Contact support for rejected transactions
   - Save transaction reference numbers
   - Report suspicious activity immediately
   - Verify account details before withdrawals

## 🔒 Security Considerations

### Data Protection
- All sensitive data is encrypted
- Password hashing using Laravel's bcrypt
- CSRF protection on all forms
- SQL injection prevention through Eloquent ORM

### Transaction Security
- Double-entry bookkeeping principles
- Atomic database transactions
- Audit trail for all financial operations
- Real-time balance validation

### Access Control
- Role-based permission system
- Session management and timeout
- Failed login attempt monitoring
- Admin action logging

### Compliance
- Financial transaction logging
- Data retention policies
- Privacy protection measures
- Audit trail maintenance

## 🛠 Troubleshooting

### Common Issues

#### 1. Login Problems
**Symptoms**: Cannot access account
**Solutions**:
- Verify email and password
- Check if account is active
- Clear browser cache and cookies
- Contact admin if account is locked

#### 2. Transaction Failures
**Symptoms**: Transfers or deposits failing
**Solutions**:
- Verify sufficient balance
- Check recipient details
- Ensure wallet is not frozen
- Contact support with reference number

#### 3. Fee Calculation Issues
**Symptoms**: Unexpected fee amounts
**Solutions**:
- Check current fee settings in admin panel
- Verify minimum/maximum fee limits
- Review fee calculation logic
- Test with small amounts first

#### 4. Performance Issues
**Symptoms**: Slow page loading
**Solutions**:
- Clear application cache: `php artisan cache:clear`
- Optimize database: `php artisan migrate:refresh --seed`
- Check server resources
- Enable Redis caching

### Error Pages

The system includes custom error pages:
- **404**: Page not found with navigation options
- **419**: Session expired with refresh options
- **500**: Server errors with support information

### Log Files

Monitor these log files for issues:
- `storage/logs/laravel.log`: Application errors
- Web server access/error logs
- Database query logs (if enabled)

## ❓ FAQ

### General Questions

**Q: Is this system suitable for production use?**
A: Yes, the system includes production-ready security features, but ensure proper hosting, SSL certificates, and regular backups.

**Q: Can I customize the fee structure?**
A: Yes, admins can configure percentage or fixed fees for both transfers and withdrawals through the admin panel.

**Q: Are transactions reversible?**
A: Transfers between users are irreversible. Deposits and withdrawals can be rejected by admins during the approval process.

### Technical Questions

**Q: What databases are supported?**
A: MySQL 8.0+, PostgreSQL 13+, and SQLite (for development).

**Q: Can I integrate external payment gateways?**
A: The system is designed to be extensible. Payment gateway integration would require custom development.

**Q: Is there an API available?**
A: The current system uses web interfaces. API development would be a custom enhancement.

### Business Questions

**Q: How do I handle chargebacks?**
A: Implement manual processes for chargeback handling. The system maintains complete transaction records for dispute resolution.

**Q: Can I set different fee structures for different user types?**
A: Currently, fees are global. User-specific fee structures would require custom development.

**Q: How do I backup transaction data?**
A: Implement regular database backups and maintain offsite copies. All transaction data is stored in the database.

## 📞 Support

### Getting Help

1. **Documentation**: Review this README and inline help text
2. **Error Messages**: Check error pages for specific guidance
3. **Log Files**: Review application logs for technical issues
4. **Admin Tools**: Use admin dashboard for system monitoring

### Maintenance

#### Regular Tasks
- **Daily**: Monitor transaction approvals
- **Weekly**: Review system logs and performance
- **Monthly**: Update dependencies and security patches
- **Quarterly**: Database optimization and cleanup

#### Backup Strategy
- **Database**: Daily automated backups
- **Files**: Weekly full system backups
- **Configuration**: Version control for all customizations
- **Testing**: Regular backup restoration tests

### Updates and Patches

1. Test updates in staging environment
2. Backup production data before updates
3. Follow Laravel upgrade guidelines
4. Monitor system after updates

## 🌐 Live Server Deployment Guide - Hostinger Cloud Startup

This section provides a complete step-by-step guide for deploying the Laravel E-Wallet system on Hostinger Cloud Startup package. This guide is designed for technical teams and beginners who need detailed instructions.

### Prerequisites

Before starting, ensure you have:
- A Hostinger Cloud Startup account (or higher)
- A domain name (either purchased through Hostinger or external)
- Basic knowledge of command line operations
- SSH client (PuTTY for Windows, Terminal for Mac/Linux)

### Step 1: Initial Server Setup

#### 1.1 Access Your Hostinger Cloud Panel

1. **Login to Hostinger**:
   - Go to [https://hpanel.hostinger.com](https://hpanel.hostinger.com)
   - Enter your credentials
   - Navigate to "Cloud Hosting" in the dashboard

2. **Access Server Management**:
   - Click on your Cloud Startup instance
   - Note down your server IP address
   - Click "Manage" to access the control panel

#### 1.2 Initial Server Configuration

1. **Access SSH**:
   ```bash
   # Connect via SSH (replace with your server IP)
   ssh root@your-server-ip

   # If using key authentication (recommended)
   ssh -i /path/to/your/private-key root@your-server-ip
   ```

2. **Update System Packages**:
   ```bash
   # Update package lists
   apt update && apt upgrade -y

   # Install essential packages
   apt install -y curl wget git unzip software-properties-common
   ```

3. **Configure Timezone**:
   ```bash
   # Set timezone (adjust as needed)
   timedatectl set-timezone America/New_York

   # Verify timezone
   timedatectl status
   ```

### Step 2: Install Required Software Stack

#### 2.1 Install PHP 8.2

```bash
# Add PHP repository
add-apt-repository ppa:ondrej/php -y

# Update package lists
apt update

# Install PHP 8.2 and required extensions
apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-common php8.2-mysql \
php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml \
php8.2-bcmath php8.2-json php8.2-tokenizer php8.2-fileinfo \
php8.2-ctype php8.2-openssl php8.2-pdo php8.2-intl

# Verify PHP installation
php -v
```

#### 2.2 Install Composer

```bash
# Download and install Composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Verify Composer installation
composer --version

# Make composer globally accessible
chmod +x /usr/local/bin/composer
```

#### 2.3 Install and Configure Nginx

```bash
# Install Nginx
apt install -y nginx

# Start and enable Nginx
systemctl start nginx
systemctl enable nginx

# Check Nginx status
systemctl status nginx
```

#### 2.4 Install MySQL 8.0

```bash
# Install MySQL Server
apt install -y mysql-server

# Secure MySQL installation
mysql_secure_installation

# Follow the prompts:
# - Set root password: YES (choose a strong password)
# - Remove anonymous users: YES
# - Disallow root login remotely: YES
# - Remove test database: YES
# - Reload privilege tables: YES
```

### Step 3: Database Configuration

#### 3.1 Create Database and User

```bash
# Login to MySQL as root
mysql -u root -p

# Create database for the e-wallet system
CREATE DATABASE laravel_ewallet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Create dedicated user for the application
CREATE USER 'ewallet_user'@'localhost' IDENTIFIED BY 'strong_secure_password_here';

# Grant privileges to the user
GRANT ALL PRIVILEGES ON laravel_ewallet.* TO 'ewallet_user'@'localhost';

# Apply privileges
FLUSH PRIVILEGES;

# Exit MySQL
EXIT;
```

#### 3.2 Optimize MySQL Configuration

```bash
# Edit MySQL configuration
nano /etc/mysql/mysql.conf.d/mysqld.cnf

# Add/modify these settings under [mysqld] section:
```

Add the following configuration:
```ini
# Performance optimizations for Cloud Startup
innodb_buffer_pool_size = 512M
innodb_log_file_size = 128M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
max_connections = 100
query_cache_type = 1
query_cache_size = 64M
tmp_table_size = 64M
max_heap_table_size = 64M
```

```bash
# Restart MySQL to apply changes
systemctl restart mysql
```

### Step 4: Deploy Laravel E-Wallet Application

#### 4.1 Create Application Directory

```bash
# Create web directory
mkdir -p /var/www/html

# Navigate to web directory
cd /var/www/html

# Remove default files
rm -rf *
```

#### 4.2 Clone and Setup Application

```bash
# Clone the repository (replace with your actual repository URL)
git clone https://github.com/your-username/laravel-ewallet.git .

# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Copy environment file
cp .env.example .env
```

#### 4.3 Configure Environment Variables

```bash
# Edit environment file
nano .env
```

Update the `.env` file with these production settings:

```env
# Application Settings
APP_NAME="E-Wallet System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_KEY=

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_ewallet
DB_USERNAME=ewallet_user
DB_PASSWORD=strong_secure_password_here

# Cache Configuration
CACHE_DRIVER=file
QUEUE_CONNECTION=database
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Mail Configuration (configure based on your email provider)
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Security
BCRYPT_ROUNDS=12
```

#### 4.4 Complete Laravel Setup

```bash
# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate --force

# Seed the database with initial data
php artisan db:seed --force

# Create storage symlink
php artisan storage:link

# Cache configuration for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache
```

### Step 5: Nginx Configuration

#### 5.1 Create Nginx Virtual Host

```bash
# Create new site configuration
nano /etc/nginx/sites-available/ewallet
```

Add the following Nginx configuration:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/html/public;
    index index.php index.html index.htm;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Handle Laravel routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Security rules
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Asset caching
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    # Deny access to sensitive files
    location ~ /(\.env|\.git|composer\.(json|lock)|package\.json) {
        deny all;
        return 404;
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/javascript
        application/xml+rss
        application/json;
}
```

#### 5.2 Enable the Site

```bash
# Enable the site
ln -s /etc/nginx/sites-available/ewallet /etc/nginx/sites-enabled/

# Remove default site
rm /etc/nginx/sites-enabled/default

# Test Nginx configuration
nginx -t

# Reload Nginx
systemctl reload nginx
```

### Step 6: Domain Configuration

#### 6.1 Configure DNS (If using external domain)

If your domain is not managed by Hostinger:

1. **Get Hostinger IP Address**:
   ```bash
   # Check your server's IP
   curl ifconfig.me
   ```

2. **Update DNS Records**:
   - Login to your domain registrar's control panel
   - Add/Update A records:
     - `A` record: `@` → `your-server-ip`
     - `A` record: `www` → `your-server-ip`

#### 6.2 Configure DNS (Hostinger Managed Domain)

1. **Access Hostinger DNS**:
   - Go to Hostinger hPanel
   - Navigate to "Domains" → "DNS Zone"
   - Select your domain

2. **Update A Records**:
   - Edit the `A` record for `@` to point to your Cloud server IP
   - Edit the `A` record for `www` to point to your Cloud server IP
   - Save changes

### Step 7: SSL Certificate Setup

#### 7.1 Install Certbot

```bash
# Install snapd
apt install -y snapd

# Install certbot via snap
snap install --classic certbot

# Create symlink
ln -s /snap/bin/certbot /usr/bin/certbot
```

#### 7.2 Obtain SSL Certificate

```bash
# Get SSL certificate for your domain
certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Follow the prompts:
# - Enter email address for notifications
# - Agree to terms of service
# - Choose whether to share email with EFF
# - Select redirect option (recommended: 2)
```

#### 7.3 Setup Auto-renewal

```bash
# Test auto-renewal
certbot renew --dry-run

# Check certbot timer status
systemctl status snap.certbot.renew.timer
```

### Step 8: Production Optimizations

#### 8.1 Configure PHP-FPM

```bash
# Edit PHP-FPM pool configuration
nano /etc/php/8.2/fpm/pool.d/www.conf
```

Optimize these settings:

```ini
# Process management
pm = dynamic
pm.max_children = 20
pm.start_servers = 3
pm.min_spare_servers = 2
pm.max_spare_servers = 5
pm.max_requests = 500

# Performance tuning
request_terminate_timeout = 60
request_slowlog_timeout = 10
slowlog = /var/log/php8.2-fpm-slow.log
```

```bash
# Edit PHP configuration
nano /etc/php/8.2/fpm/php.ini
```

Update these PHP settings:

```ini
# Memory and execution limits
memory_limit = 256M
max_execution_time = 60
max_input_time = 60
post_max_size = 32M
upload_max_filesize = 32M

# Security settings
expose_php = Off
allow_url_fopen = Off
allow_url_include = Off

# Session security
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1

# OPcache optimization
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 60
opcache.fast_shutdown = 1
```

```bash
# Restart PHP-FPM
systemctl restart php8.2-fpm
```

#### 8.2 Configure Firewall

```bash
# Install UFW
apt install -y ufw

# Configure firewall rules
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow 'Nginx Full'

# Enable firewall
ufw --force enable

# Check firewall status
ufw status verbose
```

#### 8.3 Setup Monitoring and Logs

```bash
# Create log directories
mkdir -p /var/log/ewallet

# Setup log rotation for Laravel
nano /etc/logrotate.d/laravel
```

Add log rotation configuration:

```
/var/www/html/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
    postrotate
        systemctl reload php8.2-fpm
    endscript
}
```

### Step 9: Initial System Configuration

#### 9.1 Create Admin User

```bash
# Navigate to application directory
cd /var/www/html

# Create admin user using artisan tinker
php artisan tinker
```

In the tinker console:

```php
// Create admin user
$user = App\Models\User::create([
    'name' => 'System Administrator',
    'email' => 'admin@yourdomain.com',
    'password' => bcrypt('ChangeThisPassword123!'),
    'email_verified_at' => now()
]);

// Assign admin role
$user->assignRole('admin');

// Verify user creation
echo "Admin user created with email: " . $user->email;

// Exit tinker
exit;
```

#### 9.2 Configure System Settings

1. **Access Admin Panel**:
   - Visit `https://yourdomain.com/admin/dashboard`
   - Login with admin credentials

2. **Configure Wallet Settings**:
   - Navigate to System Settings
   - Configure transfer fees
   - Configure withdrawal fees
   - Set transaction limits

### Step 10: Security Hardening

#### 10.1 Additional Security Measures

```bash
# Disable root SSH login
nano /etc/ssh/sshd_config
```

Update SSH configuration:

```
# Disable root login
PermitRootLogin no

# Use key authentication only
PasswordAuthentication no
PubkeyAuthentication yes

# Change default SSH port (optional)
Port 2222
```

```bash
# Restart SSH service
systemctl restart sshd
```

#### 10.2 Setup Fail2Ban

```bash
# Install Fail2Ban
apt install -y fail2ban

# Configure Fail2Ban
nano /etc/fail2ban/jail.local
```

Add Fail2Ban configuration:

```ini
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 3

[nginx-http-auth]
enabled = true

[nginx-limit-req]
enabled = true

[sshd]
enabled = true
port = ssh
```

```bash
# Start and enable Fail2Ban
systemctl start fail2ban
systemctl enable fail2ban
```

### Step 11: Backup Configuration

#### 11.1 Setup Automated Backups

```bash
# Create backup script
nano /usr/local/bin/ewallet-backup.sh
```

Add backup script:

```bash
#!/bin/bash

# Configuration
APP_DIR="/var/www/html"
BACKUP_DIR="/var/backups/ewallet"
DB_NAME="laravel_ewallet"
DB_USER="ewallet_user"
DB_PASS="strong_secure_password_here"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/database_$DATE.sql

# Application backup
tar -czf $BACKUP_DIR/application_$DATE.tar.gz -C $APP_DIR .

# Keep only last 7 days of backups
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete

echo "Backup completed: $DATE"
```

```bash
# Make script executable
chmod +x /usr/local/bin/ewallet-backup.sh

# Setup daily backup cron job
crontab -e
```

Add to crontab:

```
# Daily backup at 2 AM
0 2 * * * /usr/local/bin/ewallet-backup.sh >> /var/log/ewallet/backup.log 2>&1
```

### Step 12: Testing and Verification

#### 12.1 System Health Check

```bash
# Check all services
systemctl status nginx php8.2-fpm mysql

# Check application status
cd /var/www/html
php artisan about

# Test database connection
php artisan migrate:status

# Check storage permissions
ls -la storage/
```

#### 12.2 Performance Testing

```bash
# Install Apache Bench for testing
apt install -y apache2-utils

# Test homepage performance
ab -n 100 -c 10 https://yourdomain.com/

# Test login page
ab -n 100 -c 10 https://yourdomain.com/login
```

### Step 13: Final Checklist

Before going live, verify:

- [ ] Domain resolves correctly to your server
- [ ] SSL certificate is active and valid
- [ ] Application loads without errors
- [ ] Admin login works properly
- [ ] Database connections are stable
- [ ] All Laravel caches are optimized
- [ ] Firewall rules are properly configured
- [ ] Backup system is operational
- [ ] Monitoring and logging are active
- [ ] Security headers are properly set

### Maintenance Commands

Here are essential commands for ongoing maintenance:

```bash
# Update application (use with caution in production)
cd /var/www/html
git pull origin main
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Monitor logs
tail -f /var/www/html/storage/logs/laravel.log
tail -f /var/log/nginx/error.log

# Check disk space
df -h

# Monitor processes
htop

# Check SSL certificate expiry
certbot certificates
```

### Troubleshooting Common Issues

#### Issue 1: 502 Bad Gateway
```bash
# Check PHP-FPM status
systemctl status php8.2-fpm

# Check PHP-FPM logs
tail -f /var/log/php8.2-fpm.log

# Restart services
systemctl restart php8.2-fpm nginx
```

#### Issue 2: Database Connection Errors
```bash
# Check MySQL status
systemctl status mysql

# Test database connection
mysql -u ewallet_user -p laravel_ewallet

# Check Laravel database configuration
php artisan config:show database
```

#### Issue 3: Permission Issues
```bash
# Fix Laravel permissions
cd /var/www/html
chown -R www-data:www-data .
chmod -R 755 .
chmod -R 775 storage bootstrap/cache
```

This completes the comprehensive deployment guide for Hostinger Cloud Startup. The system should now be fully operational and ready for production use.

---

## 📄 License

This project is licensed under the MIT License. See the LICENSE file for details.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Submit a pull request

## 📞 Contact

For technical support or business inquiries:
- Email: support@yourdomain.com
- Documentation: [System Documentation URL]
- Support Portal: [Support URL]

---

**Last Updated**: September 2025
**Version**: 1.0.0
**Laravel Version**: 12.x
