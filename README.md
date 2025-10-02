# Laravel E-Commerce Platform

A comprehensive e-commerce application built with Laravel 12, featuring complete order management, returns/refunds processing, dual delivery methods, and integrated payment processing through a built-in e-wallet system.

## 🚀 Features

### E-Commerce Core Features
- **Package Management**: Full CRUD operations for products with inventory tracking, pricing, and SEO-friendly URLs
- **Shopping Cart**: Session-based cart with real-time updates, AJAX operations, and configurable tax calculations
- **Checkout Process**: Multi-step checkout with order review, delivery address management, and instant payment confirmation
- **26-Status Order Lifecycle**: Complete order tracking from payment to delivery/completion with dual delivery methods
- **Return & Refund System**: Customer-initiated returns with admin approval, image uploads, and automatic wallet refunds
- **Delivery Management**: Office pickup (recommended) and home delivery with complete address tracking
- **Order Analytics**: Comprehensive admin dashboard with revenue metrics, status distribution, and fulfillment analytics

### Admin Management
- **Order Dashboard**: Advanced interface with filtering, bulk operations, and real-time updates
- **Timeline Management**: Visual order progression with editable notes and full audit trail
- **Return Approvals**: Complete return request management with custom responses and refund processing
- **Package CRUD**: Full product management with image uploads, inventory control, and metadata
- **Customer Management**: Integrated customer information and communication tools
- **Application Settings**: Configurable tax rates, email verification, and system-wide preferences

### Payment Integration
- **E-Wallet System**: Integrated digital wallet for seamless payment processing
- **Instant Payments**: Real-time balance validation and transaction-safe payment processing
- **Automatic Refunds**: Wallet-based refund processing for cancelled orders and approved returns
- **Transaction History**: Complete audit trail for all payments, refunds, and wallet operations

### Security & Authentication
- **Laravel Fortify**: Two-factor authentication, password resets, email verification
- **Role-based Access Control**: Spatie Laravel Permission for granular user permissions
- **CSRF Protection**: All forms protected against cross-site request forgery
- **Session Management**: Secure authentication with configurable timeouts

### User Experience
- **Responsive Design**: Mobile-first design using Tailwind CSS 4.0 and CoreUI
- **Real-time Updates**: AJAX-powered interface with instant feedback and toast notifications
- **Profile Management**: Centralized delivery address management with smart pre-filling
- **Order Tracking**: Complete order history with delivery information and return options
- **SEO-Friendly**: Slug-based routing for products and optimized page structures

## 📋 Table of Contents

- [System Requirements](#system-requirements)
- [Installation Guide](#installation-guide)
- [Configuration](#configuration)
- [E-Commerce Features](#e-commerce-features)
- [Admin Documentation](#admin-documentation)
- [Customer Documentation](#customer-documentation)
- [Payment System](#payment-system)
- [Security Considerations](#security-considerations)
- [Troubleshooting](#troubleshooting)
- [Deployment Guide](#deployment-guide)
- [Support](#support)

## 🛠 System Requirements

### Server Requirements
- **PHP**: 8.2 or higher
- **Composer**: Latest version
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **SSL Certificate**: Required for production deployment
- **Node.js**: 16.0+ (for frontend asset compilation)

### PHP Extensions
- BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

## 🔧 Installation Guide

### 1. Clone and Install Dependencies

```bash
git clone <repository-url> laravel-ecommerce
cd laravel-ecommerce

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 2. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure timezone (default: Asia/Manila)
# Edit .env file:
APP_TIMEZONE=Asia/Manila
```

### 3. Database Setup

Edit `.env` with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_ecommerce
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Run migrations and seeders:

```bash
# Run migrations (includes all e-commerce tables)
php artisan migrate

# Seed with initial data (packages, settings, roles)
php artisan db:seed
```

### 4. Storage and Cache Configuration

```bash
# Create storage symlink for uploaded images
php artisan storage:link

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. Set Permissions

```bash
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

### 6. Build Frontend Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 7. Start the Application

```bash
# Development server
composer dev  # Runs server, queue, logs, and vite concurrently

# Or individually:
php artisan serve                    # Server at http://localhost:8000
php artisan queue:listen --tries=1   # Queue worker
php artisan pail --timeout=0        # Log viewer
npm run dev                          # Vite dev server
```

## ⚙️ Configuration

### Initial Setup

After installation, create an admin user:

```bash
php artisan tinker
```

```php
$user = App\Models\User::create([
    'name' => 'System Administrator',
    'username' => 'admin',
    'email' => 'admin@yourdomain.com',
    'password' => bcrypt('secure_password'),
    'email_verified_at' => now()
]);
$user->assignRole('admin');

// Create wallet for admin
$user->wallet()->create(['balance' => 0]);
```

### Application Settings

Configure system-wide settings at `/admin/application-settings`:

- **Tax Rate**: E-commerce tax rate (0% to 100%, auto-hides when 0%)
- **Email Verification**: Toggle email verification for new registrations
- Settings take effect immediately without restart

### System Settings

Additional settings at `/admin/system-settings`:

- Transfer fees (for wallet-to-wallet transfers)
- Withdrawal fees (for wallet withdrawals)
- Security options and ongoing email verification

## 🛒 E-Commerce Features

### Package System

**Admin Management** (`/admin/packages`):
- Full CRUD operations for products
- Image uploads with fallback placeholders
- Inventory tracking and availability management
- SEO-friendly slug generation
- Points system integration
- Soft deletes to protect order history

**Customer Browsing** (`/packages`):
- Searchable product catalog
- Filter and sort capabilities
- Real-time cart status indicators
- Individual product pages (`/packages/{slug}`)

### Shopping Cart

**Features**:
- Session-based cart (persistent across sessions)
- AJAX add/remove operations without page reload
- Real-time button state updates ("Add to Cart" → "In Cart")
- Automatic inventory validation
- Configurable tax calculations
- Header dropdown and full cart page
- Service: `CartService` (`app/Services/CartService.php`)

### Checkout Process

**Flow** (`/checkout`):
1. Review cart items and totals
2. Enter/confirm delivery address (auto-filled from profile)
3. Add optional customer notes
4. Accept terms and conditions
5. Confirm payment (e-wallet balance validation)
6. Instant payment processing
7. Order confirmation page with order details

**Features**:
- Profile-based delivery address management
- Smart address pre-filling
- Inline address editing during checkout
- Order number generation (ORD-YYYY-MM-DD-XXXX)
- Package snapshots (preserves product details at purchase time)

### Order Management

**26-Status Order Lifecycle**:

**Universal Statuses:**
- `pending` → `paid` → `processing` → `confirmed` → `packing`

**Office Pickup Path:**
- → `ready_for_pickup` → `pickup_notified` → `received_in_office` → `completed`

**Home Delivery Path:**
- → `ready_to_ship` → `shipped` → `out_for_delivery` → `delivered` → `completed`

**Special Statuses:**
- `on_hold`, `cancelled`, `payment_failed`, `delivery_failed`

**Return/Refund Statuses:**
- `return_requested` → `return_approved`/`return_rejected` → `return_in_transit` → `return_received` → `refunded` → `returned`

**Customer Features** (`/orders`):
- Complete order history
- Order details with delivery tracking
- Return request submission (7-day window from delivery)
- Order cancellation (pending orders only)
- Invoice viewing

**Admin Features** (`/admin/orders`):
- Advanced filtering (status, date, customer)
- Bulk status updates
- Order analytics dashboard
- Timeline with editable notes
- Customer communication
- Status history audit trail

### Return & Refund System

**Customer Process** (`/orders/{order}`):
1. View delivered order details
2. Click "Request Return" (within 7-day window)
3. Select return reason (damaged, wrong item, quality issue, etc.)
4. Upload proof images
5. Provide detailed description
6. Submit return request
7. Track return status

**Admin Process** (`/admin/returns`):
1. Review pending return requests
2. View customer description and proof images
3. Approve with return shipping instructions OR reject with explanation
4. Track return shipment
5. Confirm receipt of returned item
6. Process automatic wallet refund
7. Order status updates to `refunded` and `returned`

**Return Features**:
- 7-day return window (configurable in `Order` model)
- Image upload support for proof
- Admin responses and notes
- Automatic e-wallet refunds
- Complete return status tracking
- Integration with main order lifecycle

## 👤 Admin Documentation

### Admin Dashboard Access

Access admin interface at `/admin/dashboard` with admin credentials.

### Key Admin Panels

#### 1. Package Management (`/admin/packages`)
- Create, read, update, delete products
- Upload product images
- Manage inventory and pricing
- Set points and product metadata
- View products in cart across site

#### 2. Order Management (`/admin/orders`)
- View all orders with advanced filtering
- Update order status (26-status lifecycle)
- Add/edit timeline notes
- View customer delivery information
- Track order progression
- Bulk operations support
- Real-time toast notifications

#### 3. Order Details (`/admin/orders/{order}`)
- Complete order information
- Status timeline with visual progression
- Editable timeline notes
- Customer details and delivery address
- Admin notes section
- Quick status updates
- Package details with snapshots

#### 4. Return Management (`/admin/returns`)
- Pending return requests dashboard
- Filter by status and customer
- Approve/reject return requests
- View proof images and descriptions
- Process refunds automatically
- Track return shipping
- Custom admin responses

#### 5. Order Analytics (`/admin/orders/analytics`)
- Revenue metrics
- Status distribution charts
- Fulfillment analytics
- Customer order patterns
- Period-based reporting

#### 6. Application Settings (`/admin/application-settings`)
- Configure tax rate (0-100%)
- Toggle email verification for registration
- Instant settings application

### Admin Best Practices

1. **Order Processing**:
   - Review new orders promptly
   - Add detailed timeline notes for transparency
   - Update status as soon as actions complete
   - Communicate delivery delays proactively

2. **Return Approvals**:
   - Review proof images carefully
   - Provide clear rejection reasons
   - Include return shipping instructions when approving
   - Process refunds promptly after return receipt

3. **Inventory Management**:
   - Keep stock levels updated
   - Set realistic delivery timeframes
   - Monitor popular products for restocking
   - Use soft deletes to maintain order history

## 👥 Customer Documentation

### Getting Started

#### 1. Account Registration
1. Visit registration page
2. Provide: Full Name, Username, Email, Password
3. Verify email (if enabled)
4. Wallet automatically created
5. Access dashboard

#### 2. Dashboard Overview (`/dashboard`)
- Current wallet balance
- Recent order summary
- Quick action buttons
- Transaction history preview
- Order status overview

### Shopping Experience

#### 1. Browse Products (`/packages`)
- Search by product name
- Sort by price, name, or date
- Filter by availability
- View product details
- Check cart status indicators

#### 2. Add to Cart
- Click "Add to Cart" on any product
- Button updates to "In Cart" with checkmark
- View cart count in header
- Access cart dropdown for quick review
- Navigate to full cart page

#### 3. Shopping Cart (`/cart`)
- Review all cart items
- Update quantities (inventory validated)
- Remove unwanted items
- View subtotal, tax, and total
- Proceed to checkout

#### 4. Checkout (`/checkout`)
- Review order summary
- Confirm/update delivery address
- Add customer notes (optional)
- Accept terms and conditions
- View wallet balance and payment total
- Confirm order (instant payment)

#### 5. Order Confirmation
- View order number and details
- Download/print invoice
- Track order status
- Access order from history

### Managing Orders

#### Order History (`/orders`)
- View all past orders
- Filter and search orders
- Check delivery status
- Access order details
- Request returns (eligible orders)

#### Order Details (`/orders/{order}`)
- Complete order information
- Delivery address
- Order timeline (visual progression)
- Package details
- Invoice access
- Return request option (delivered orders only)
- Order cancellation (pending orders only)

### Requesting Returns

**Eligibility**:
- Order must be delivered
- Within 7-day return window
- Return button visible on eligible orders

**Process**:
1. Navigate to delivered order
2. Click "Request Return"
3. Select return reason from dropdown
4. Upload proof images (optional but recommended)
5. Provide detailed description (minimum 20 characters)
6. Submit request
7. Wait for admin review (typically 24 hours)
8. Check return status on order details page
9. Ship item back per admin instructions (if approved)
10. Receive automatic wallet refund once admin confirms receipt

### Profile Management (`/profile`)

**Personal Information**:
- Update name, email, username
- Change password
- Enable two-factor authentication

**Delivery Address**:
- Set default delivery address
- Address auto-fills during checkout
- Update anytime from profile
- Inline editing available at checkout

## 💳 Payment System

### E-Wallet Overview

The integrated e-wallet system provides seamless payment processing for e-commerce transactions. It's a **supporting payment infrastructure** for the e-commerce platform, not a standalone wallet product.

### Wallet Features

**For Customers**:
- Automatic wallet creation with registration
- Real-time balance display
- Instant payment processing for orders
- Automatic refunds for cancelled orders and approved returns
- Complete transaction history
- Deposit and withdrawal capabilities (admin approval required)
- Transfer to other users (optional, with configurable fees)

**For Administrators**:
- Monitor all wallet balances
- Approve/reject deposits and withdrawals
- Transaction oversight and reporting
- Automatic refund processing for returns
- Complete audit trail

### Payment Flow

**Order Payment**:
1. Customer proceeds to checkout
2. System validates wallet balance ≥ order total
3. Customer confirms order
4. Payment instantly deducted from wallet
5. Order status updates to `paid`
6. Transaction recorded with reference number
7. Admin notified of new paid order

**Refund Flow**:
1. Customer cancels order OR return is approved and confirmed
2. System automatically credits wallet
3. Order status updates to `refunded`
4. Transaction recorded with reference
5. Customer notified of refund

### Wallet Operations

**Deposits** (`/wallet/deposit`):
- Add funds to wallet
- Requires admin approval
- Payment methods: Credit Card, Bank Transfer, PayPal
- Processing time: 1-3 business days

**Withdrawals** (`/wallet/withdraw`):
- Transfer funds to bank account
- Requires admin approval
- Non-refundable withdrawal fees (if configured)
- Processing time: 1-3 business days

**Transfers** (`/wallet/transfer`):
- Send money to other users
- Instant processing
- Optional configurable transfer fees
- Transaction reference numbers

### Transaction Types

- **Payment**: E-commerce order payments
- **Refund**: Order cancellations and approved returns
- **Deposit**: Adding funds from external sources
- **Withdrawal**: Transferring funds to bank
- **Transfer Out/In**: User-to-user transfers
- **Fees**: Transfer and withdrawal processing fees

## 🔒 Security Considerations

### Application Security
- CSRF protection on all forms
- SQL injection prevention via Eloquent ORM
- XSS protection with input sanitization
- Password hashing using bcrypt
- Session management and timeout
- Two-factor authentication support

### E-Commerce Security
- Atomic database transactions for orders
- Package snapshot preservation
- Order status validation and allowed transitions
- Inventory checking before order confirmation
- Delivery address encryption
- Transaction reference numbers for tracking

### Payment Security
- Real-time balance validation
- Transaction-safe payment processing
- Automatic rollback on payment failure
- Complete audit trail for all transactions
- Wallet freeze capabilities
- Admin oversight for deposits/withdrawals

### Access Control
- Role-based permissions (admin, member)
- Protected admin routes
- Customer order ownership validation
- Return request authorization
- Session security with httponly cookies

## 🛠 Troubleshooting

### Common Issues

#### 1. Cart Issues
**Problem**: Items not adding to cart
**Solutions**:
- Check inventory availability
- Clear session: `php artisan session:clear`
- Verify cart service is working
- Check browser console for errors

#### 2. Payment Failures
**Problem**: Order payment not processing
**Solutions**:
- Verify sufficient wallet balance
- Check wallet is not frozen
- Review transaction logs
- Contact admin for assistance

#### 3. Order Status Not Updating
**Problem**: Status changes not reflecting
**Solutions**:
- Refresh page
- Clear application cache: `php artisan cache:clear`
- Check admin has proper permissions
- Review status transition rules

#### 4. Return Request Issues
**Problem**: Cannot request return
**Solutions**:
- Verify order is delivered
- Check 7-day window hasn't expired
- Ensure no existing return request
- Review order eligibility

#### 5. Image Upload Failures
**Problem**: Product/return images not uploading
**Solutions**:
- Check file size (< 5MB per image)
- Verify storage symlink: `php artisan storage:link`
- Check storage permissions
- Review PHP upload limits

### Error Pages

Custom error pages with helpful guidance:
- **404**: Page not found
- **419**: Session expired
- **500**: Server error
- **403**: Unauthorized access

### Log Files

Monitor these logs:
- `storage/logs/laravel.log`: Application errors
- Transaction logs in database
- Order status change history
- Return request tracking

## 🌐 Deployment Guide

### Production Checklist

- [ ] SSL certificate installed and active
- [ ] Environment set to production (`APP_ENV=production`)
- [ ] Debug mode disabled (`APP_DEBUG=false`)
- [ ] Strong database credentials
- [ ] Application key generated
- [ ] Caches optimized (config, route, view)
- [ ] Storage symlink created
- [ ] Proper file permissions set
- [ ] Queue worker running
- [ ] Backup system configured
- [ ] Firewall configured
- [ ] Monitoring enabled

### Essential Commands

```bash
# Update application
git pull origin main
composer install --optimize-autoloader --no-dev
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Monitor application
php artisan queue:work  # Keep queue worker running
php artisan pail        # Real-time log viewer
tail -f storage/logs/laravel.log

# Database maintenance
php artisan migrate:status
php artisan db:seed --class=DatabaseResetSeeder  # Reset for testing

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Hostinger Cloud Deployment

For detailed Hostinger Cloud Startup deployment instructions, refer to the comprehensive deployment guide included in previous README sections or contact support.

## 📞 Support

### Getting Help

1. **Documentation**: Review this README and `CLAUDE.md`
2. **Error Messages**: Check error pages for specific guidance
3. **Log Files**: Review application logs for technical issues
4. **Admin Dashboard**: Use admin tools for system monitoring

### Maintenance Schedule

- **Daily**: Monitor order approvals and return requests
- **Weekly**: Review system logs and performance
- **Monthly**: Update dependencies and security patches
- **Quarterly**: Database optimization and cleanup

### Important Files

- `CLAUDE.md`: Developer guide with architecture details
- `ORDER_RETURN.md`: Business logic for returns/refunds
- `RETURN_PROCESS_IMPLEMENTATION.md`: Return system implementation details
- `RETURN_PROCESS_COMPLETE_TEST_GUIDE.md`: Testing guide for returns
- `ECOMMERCE_ROADMAP.md`: Future development phases

## 📄 License

This project is licensed under the MIT License.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes with tests
4. Follow coding standards (Laravel Pint)
5. Submit a pull request

---

**Project Type**: E-Commerce Platform with Integrated Payment System
**Primary Focus**: Online shopping, order management, returns/refunds
**Payment Method**: Integrated e-wallet system
**Last Updated**: October 2025
**Version**: 1.0.0
**Laravel Version**: 12.x
**PHP Version**: 8.2+
