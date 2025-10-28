# Pharmax - Pharmacy Management System

A modern, comprehensive pharmacy management system built with Laravel 10, Vue.js 3, Inertia.js, and Tailwind CSS. Pharmax provides everything you need to manage your pharmacy operations efficiently.

## 🚀 Version 1.0 Features

### 🏥 Core Management Systems
- **Dashboard** - Real-time analytics, charts, and key metrics
- **Medicine Management** - Complete CRUD operations with category and manufacturer relationships
- **Customer Management** - Customer information and credit tracking
- **Manufacturer Management** - Supplier and manufacturer database
- **Category Management** - Medicine categorization system
- **Bank Management** - Financial institution management
- **Account Management** - Chart of accounts and financial transactions

### 💰 Sales & Inventory
- **Point of Sale (POS)** - Modern, intuitive sales interface with discount functionality
- **Invoice Management** - Generate and manage sales invoices with due tracking
- **Purchase Management** - Supplier purchases and inventory tracking
- **Stock Management** - Complete inventory control with batch tracking
- **Stock Reports** - Comprehensive inventory analytics
- **Stock Alerts** - Low stock and expiry notifications

### 👥 User & Access Control
- **User Management** - Complete user administration system
- **Role-based Permissions** - Secure access control with Spatie Laravel Permission
- **Menu Control System** - Dynamic navigation based on user roles
- **Admin Controls** - Full administrative capabilities

### 🎨 Modern UI/UX
- **Responsive Design** - Works perfectly on desktop, tablet, and mobile
- **Mobile Navigation** - Smooth overlay mobile menu
- **Dynamic App Branding** - Configurable app name and logo
- **Interactive Alerts** - SweetAlert2 integration for better user experience
- **Real-time Updates** - Live data updates without page refresh
- **Modern Components** - Clean, professional interface design

### 🔧 Technical Features
- **Laravel 10** - Latest PHP framework with modern features
- **Vue.js 3** - Reactive frontend with Composition API
- **Inertia.js** - SPA-like experience without API complexity
- **Tailwind CSS** - Utility-first CSS framework
- **MySQL Database** - Robust data storage with relationships
- **Code Structure** - Aligned and organized codebase for better maintainability
- **Database Migrations** - Comprehensive database schema
- **Seeders** - Sample data for testing and development

## 🚀 Installation

### Quick Installation (Recommended)

1. **Upload Files**: Upload all files to your web server
2. **Set Permissions**: Ensure proper file permissions
3. **Visit Installation**: Go to `https://your-domain.com/install`
4. **Follow Wizard**: Complete the 5-step installation process
5. **Login**: Use your admin credentials to access the system

### Manual Installation

1. Clone the repository
2. Install dependencies: `composer install && npm install`
3. Copy `.env.example` to `.env` and configure your database
4. Generate application key: `php artisan key:generate`
5. Run migrations: `php artisan migrate`
6. Seed the database: `php artisan db:seed`
7. Build assets: `npm run build`
8. Set proper permissions: `chmod -R 755 storage bootstrap/cache`

### System Requirements

- **PHP**: 8.1 or higher
- **MySQL**: 8.0 or higher
- **Web Server**: Apache/Nginx
- **Memory**: 256MB minimum (512MB recommended)
- **Disk Space**: 100MB minimum

### Detailed Installation Guide

For complete installation instructions, see [INSTALLATION_GUIDE.md](INSTALLATION_GUIDE.md)

## 📋 Prerequisites
- PHP 8.1 or higher
- Composer
- Node.js and NPM
- MySQL 5.7 or higher

### Quick Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd pharmacare-modern
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database configuration**
   - Update your `.env` file with database credentials
   - Create a MySQL database for the application

5. **Run migrations and seeders**
   ```bash
   php artisan migrate --seed
   ```

6. **Build assets**
   ```bash
   npm run build
   ```

7. **Start the development server**
   ```bash
   php artisan serve
   npm run dev
   ```

8. **Access the application**
   - Open your browser and go to `http://localhost:8000`
   - Register a new account or use the default admin credentials

## 🔐 Default Login Credentials

- **Email**: admin@pharmacare.com
- **Password**: password

## 📁 Project Structure

```
pharmacare-modern/
├── app/
│   ├── Http/Controllers/     # API Controllers
│   │   ├── ManufacturerController.php
│   │   ├── UserController.php
│   │   ├── MenuController.php
│   │   ├── StockController.php
│   │   └── ...
│   ├── Models/              # Eloquent Models
│   │   ├── Menu.php
│   │   ├── Stock.php
│   │   ├── StockTransaction.php
│   │   └── ...
│   └── Http/Middleware/     # Custom middleware
├── database/
│   ├── migrations/          # Database migrations
│   │   ├── create_menus_table.php
│   │   ├── create_stocks_table.php
│   │   └── ...
│   └── seeders/            # Database seeders
│       ├── MenuSeeder.php
│       ├── StockSeeder.php
│       └── ...
├── resources/
│   ├── js/
│   │   ├── Pages/          # Vue.js pages
│   │   │   ├── User/       # User management pages
│   │   │   ├── Stock/      # Stock management pages
│   │   │   └── ...
│   │   ├── Components/     # Reusable Vue components
│   │   ├── Layouts/        # Page layouts
│   │   └── app.js          # Main Vue application
│   └── views/              # Blade templates
└── routes/
    └── web.php              # Web routes
```

## 🛠️ Key Technologies Used

- **Backend**: Laravel 10, PHP 8.1+
- **Frontend**: Vue.js 3, Inertia.js, Tailwind CSS
- **Database**: MySQL with Eloquent ORM
- **Authentication**: Laravel Breeze with Sanctum
- **Permissions**: Spatie Laravel Permission
- **UI Components**: SweetAlert2 for interactive alerts
- **Build Tool**: Vite for fast development and building

## 🆕 Version 1.0 New Features

### User Management System
- Complete CRUD operations for users
- Role assignment and management
- Password management
- User profile management

### Menu Control System
- Dynamic navigation based on user roles
- Admin can assign menus to specific roles
- Menu ordering and status management
- Permission-based menu visibility

### Stock Management System
- Complete inventory tracking
- Batch number and expiry date management
- Stock level alerts and notifications
- Comprehensive stock reports
- Stock transaction history

### Enhanced POS System
- Discount functionality
- Improved calculation accuracy
- Better user interface
- Mobile-responsive design

### Mobile Navigation
- Responsive hamburger menu
- Overlay mobile navigation
- Touch-friendly interface
- Mobile-optimized user experience

## 🔄 Version History

### Version 1.0 (Current)
- Complete manufacturer management system
- User management with role-based permissions
- Menu control system for role-based navigation
- Stock management system with reports and alerts
- Enhanced POS system with discount functionality
- Dynamic app name and logo configuration
- Responsive mobile navigation menu
- SweetAlert2 integration for interactive alerts
- Code structure alignment across all controllers
- Complete Vue.js components for all modules
- Database migrations and seeders
- Role-based access control with Spatie Laravel Permission

## 🚀 Getting Started

1. **Clone and setup** the application following the installation guide
2. **Run migrations** to create the database structure
3. **Seed the database** with sample data
4. **Login** with the default admin credentials
5. **Explore** the various management modules
6. **Configure** menus and permissions as needed

## 📱 Mobile Support

The application is fully responsive and includes:
- Mobile-optimized navigation
- Touch-friendly interfaces
- Responsive data tables
- Mobile POS interface
- Adaptive layouts for all screen sizes

## 🔒 Security Features

- Role-based access control
- Permission-based menu system
- Secure authentication
- CSRF protection
- SQL injection prevention
- XSS protection

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

For support, email support@pharmacare.com or create an issue in the repository.

## 📸 Screenshots

### Dashboard
![Dashboard](screenshots/dashboard.png)

### POS Interface
![POS](screenshots/pos.png)

### Medicine Management
![Medicines](screenshots/medicines.png)

### User Management
![Users](screenshots/users.png)

### Stock Management
![Stock](screenshots/stock.png)

---

**PharmaCare Modern v1.0** - A comprehensive, modern pharmacy management solution built with cutting-edge technology.

## 🏷️ Git Tags

- **v1.0** - Complete pharmacy management system with all core features
