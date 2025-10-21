# PharmaCare Modern - Complete Documentation

## Table of Contents
1. [Overview](#overview)
2. [Features](#features)
3. [Installation](#installation)
4. [Configuration](#configuration)
5. [User Guide](#user-guide)
6. [API Documentation](#api-documentation)
7. [Security](#security)
8. [Performance](#performance)
9. [Troubleshooting](#troubleshooting)
10. [Support](#support)

## Overview

PharmaCare Modern is a comprehensive pharmacy management system built with Laravel 10, Vue.js 3, and Inertia.js. It provides a complete solution for managing pharmacy operations including inventory, sales, customers, and reporting.

### Key Technologies
- **Backend**: Laravel 10 (PHP 8.1+)
- **Frontend**: Vue.js 3 with Inertia.js
- **Database**: MySQL 8.0+
- **Styling**: Tailwind CSS
- **Build Tool**: Vite
- **Authentication**: Laravel Breeze with Spatie Permissions

## Features

### Core Management Systems
- **Medicine Management**: Complete CRUD operations with categories, manufacturers, and stock tracking
- **Customer Management**: Customer database with contact information and purchase history
- **Invoice Management**: Sales invoicing with multiple payment methods and tax calculations
- **Stock Management**: Real-time inventory tracking with batch management and expiry alerts
- **Purchase Management**: Supplier purchase orders with automatic stock updates

### Advanced Features
- **Point of Sale (POS)**: Modern POS interface with barcode/QR scanning
- **External API Integration**: MedEx API integration for medicine search and details
- **Notification System**: Email and SMS notifications with multiple provider support
- **Backup & Restore**: Complete system backup and restore functionality
- **Data Export/Import**: Excel/CSV export and import capabilities
- **Terminal Interface**: Command-line interface for system management
- **Audit Logging**: Comprehensive activity logging for security and compliance
- **Role-Based Access Control**: Granular permissions system

### Reporting & Analytics
- **Sales Reports**: Daily, monthly, and custom date range sales analysis
- **Purchase Reports**: Supplier-wise purchase tracking and analysis
- **Profit/Loss Reports**: Revenue and cost analysis with gross profit calculations
- **Customer Dues**: Outstanding payment tracking
- **Stock Reports**: Inventory valuation, low stock alerts, and expiry tracking

## Installation

### System Requirements
- PHP 8.1 or higher
- MySQL 8.0 or higher
- Node.js 18+ and NPM
- Composer
- Web server (Apache/Nginx)

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/pharmacare-modern.git
   cd pharmacare-modern
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Build assets**
   ```bash
   npm run build
   ```

7. **Set permissions**
   ```bash
   chmod -R 755 storage bootstrap/cache
   ```

### Default Credentials
- **Email**: admin@pharmacare.com
- **Password**: password

## Configuration

### Database Configuration
Update your `.env` file with database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pharmacare
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Email Configuration
Configure email settings in the Settings page or `.env` file:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="PharmaCare"
```

### SMS Configuration
Configure SMS providers in Settings:
- **Twilio**: Requires Account SID, Auth Token, and From Number
- **Nexmo**: Requires API Key and Secret
- **Custom API**: Configure custom SMS gateway

### File Storage
Configure file storage in `config/filesystems.php`:
```php
'default' => env('FILESYSTEM_DISK', 'local'),
'disks' => [
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app'),
    ],
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],
],
```

## User Guide

### Getting Started

1. **Login**: Use the default credentials or create a new user account
2. **Dashboard**: View key statistics and quick actions
3. **Settings**: Configure company information, currency, and notification settings

### Medicine Management

1. **Add Medicine**:
   - Navigate to Medicines → Add New
   - Fill in medicine details (name, category, manufacturer, etc.)
   - Set pricing and stock levels
   - Save the medicine

2. **Import Medicines**:
   - Use CSV/Excel import feature
   - Download sample file for correct format
   - Upload file and configure import settings

3. **External Medicine Search**:
   - Use MedEx API integration
   - Search for medicines by name
   - Import medicine details automatically

### Stock Management

1. **Add Stock**:
   - Navigate to Stocks → Add New
   - Select medicine and enter batch details
   - Set expiry date and quantities
   - Configure pricing and supplier information

2. **Stock Alerts**:
   - View low stock medicines
   - Check expiring medicines
   - Monitor stock levels

### Sales Management

1. **Point of Sale**:
   - Navigate to POS interface
   - Search for customers and medicines
   - Add items to cart
   - Process payment and generate invoice

2. **Invoice Management**:
   - View all invoices
   - Print invoices
   - Track payments and dues

### Customer Management

1. **Add Customer**:
   - Navigate to Customers → Add New
   - Enter customer details
   - Save customer information

2. **Customer History**:
   - View customer purchase history
   - Track outstanding dues
   - Manage customer relationships

### Reporting

1. **Sales Reports**:
   - Generate daily/monthly sales reports
   - Filter by date, customer, or medicine
   - Export reports to Excel/CSV

2. **Stock Reports**:
   - View inventory valuation
   - Check stock alerts
   - Monitor expiry dates

## API Documentation

The application provides a comprehensive REST API for all operations. See [API_DOCUMENTATION.md](API_DOCUMENTATION.md) for detailed API reference.

### Key API Endpoints
- `GET /api/medicines` - List medicines
- `POST /api/medicines` - Create medicine
- `GET /api/customers` - List customers
- `POST /api/invoices` - Create invoice
- `GET /api/stocks/reports` - Stock reports

### Authentication
All API requests require authentication via Bearer token:
```
Authorization: Bearer {your-token}
```

### Rate Limiting
- API endpoints: 60 requests/minute
- POS endpoints: 120 requests/minute
- Default: 100 requests/minute

## Security

### Security Features
- **CSRF Protection**: All forms protected against CSRF attacks
- **SQL Injection Prevention**: Eloquent ORM with parameterized queries
- **XSS Protection**: Input sanitization and output escaping
- **Rate Limiting**: API rate limiting to prevent abuse
- **Audit Logging**: Complete activity logging
- **Role-Based Access**: Granular permission system
- **Security Headers**: Comprehensive security headers

### Security Headers
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `X-XSS-Protection: 1; mode=block`
- `Content-Security-Policy: default-src 'self'`
- `Referrer-Policy: strict-origin-when-cross-origin`

### Input Validation
All user inputs are validated and sanitized:
- Server-side validation using Laravel validation rules
- Client-side validation for better UX
- Input sanitization middleware
- File upload validation

## Performance

### Database Optimization
- **Indexes**: Comprehensive database indexes for fast queries
- **Query Optimization**: Optimized Eloquent queries
- **Pagination**: Efficient pagination for large datasets
- **Caching**: Redis/Memcached support for better performance

### Frontend Optimization
- **Code Splitting**: Dynamic imports for better loading
- **Asset Optimization**: Minified CSS and JavaScript
- **Image Optimization**: Optimized images and lazy loading
- **CDN Support**: CDN integration for static assets

### Performance Monitoring
- **Query Logging**: Database query performance monitoring
- **Response Time Tracking**: API response time monitoring
- **Memory Usage**: Memory usage optimization
- **Caching Strategy**: Multi-level caching implementation

## Troubleshooting

### Common Issues

1. **Installation Issues**
   - Ensure PHP 8.1+ is installed
   - Check MySQL connection
   - Verify file permissions

2. **Email Not Working**
   - Check SMTP configuration
   - Verify email credentials
   - Test email settings

3. **SMS Not Working**
   - Verify SMS provider credentials
   - Check phone number format
   - Test SMS configuration

4. **File Upload Issues**
   - Check file permissions
   - Verify upload limits
   - Check disk space

### Debug Mode
Enable debug mode in `.env`:
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

### Log Files
Check log files in `storage/logs/`:
- `laravel.log` - Application logs
- `audit.log` - Audit trail logs

### Performance Issues
1. **Slow Queries**: Check database indexes
2. **Memory Issues**: Increase PHP memory limit
3. **Slow Loading**: Enable caching and optimize assets

## Support

### Documentation
- **User Guide**: This documentation
- **API Reference**: [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
- **Code Comments**: Inline code documentation

### Support Channels
- **Email**: support@pharmacare.com
- **GitHub Issues**: [GitHub Issues](https://github.com/your-username/pharmacare-modern/issues)
- **Documentation**: [Documentation Site](https://docs.pharmacare.com)

### Community
- **Discord**: [Discord Server](https://discord.gg/pharmacare)
- **Forum**: [Community Forum](https://forum.pharmacare.com)
- **Stack Overflow**: Tag `pharmacare-modern`

### Professional Support
For professional support and customization:
- **Email**: enterprise@pharmacare.com
- **Phone**: +1-800-PHARMACARE
- **Website**: [PharmaCare Support](https://pharmacare.com/support)

---

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a list of changes and updates.

## Credits

- **Laravel Framework**: [Laravel](https://laravel.com)
- **Vue.js**: [Vue.js](https://vuejs.org)
- **Inertia.js**: [Inertia.js](https://inertiajs.com)
- **Tailwind CSS**: [Tailwind CSS](https://tailwindcss.com)
- **Spatie Permissions**: [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)
- **SweetAlert2**: [SweetAlert2](https://sweetalert2.github.io)
