# PharmaCare Modern - Pharmacy Management System

A modern, beautiful pharmacy management system built with Laravel 10, Vue.js 3, Inertia.js, and Tailwind CSS.

## Features

### 🏥 Core Functionality
- **Dashboard** - Real-time analytics, charts, and key metrics
- **Medicine Management** - Complete CRUD operations with barcode/QR generation
- **Customer Management** - Customer information and credit tracking
- **Point of Sale (POS)** - Modern, intuitive sales interface
- **Invoice Management** - Generate and manage sales invoices
- **Purchase Management** - Supplier purchases and inventory tracking
- **Account Management** - Chart of accounts and financial transactions
- **Reports** - Comprehensive financial and inventory reports

### 🎨 Modern UI/UX
- **Responsive Design** - Works perfectly on desktop, tablet, and mobile
- **Dark/Light Theme** - Beautiful theme switching capability
- **Real-time Updates** - Live data updates without page refresh
- **Interactive Charts** - Beautiful data visualization with Chart.js
- **Modern Components** - Clean, professional interface design

### 🔧 Technical Features
- **Laravel 10** - Latest PHP framework with modern features
- **Vue.js 3** - Reactive frontend with Composition API
- **Inertia.js** - SPA-like experience without API complexity
- **Tailwind CSS** - Utility-first CSS framework
- **MySQL Database** - Robust data storage with relationships
- **Role-based Permissions** - Secure access control
- **CSV Import/Export** - Bulk data management
- **PDF Generation** - Professional invoice and report printing

## Installation

### Prerequisites
- PHP 8.1 or higher
- Composer
- Node.js and NPM
- MySQL 5.7 or higher

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone <repository-url>
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

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database configuration**
   - Update your `.env` file with database credentials
   - Create a MySQL database for the application

6. **Run migrations and seeders**
   ```bash
   php artisan migrate --seed
   ```

7. **Build assets**
   ```bash
   npm run build
   ```

8. **Start the development server**
   ```bash
   php artisan serve
   ```

9. **Access the application**
   - Open your browser and go to `http://localhost:8000`
   - Register a new account or use the default admin credentials

## Default Login Credentials

- **Email**: admin@pharmacare.com
- **Password**: password

## Project Structure

```
pharmacare-modern/
├── app/
│   ├── Http/Controllers/     # API Controllers
│   ├── Models/              # Eloquent Models
│   └── ...
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/            # Database seeders
├── resources/
│   ├── js/
│   │   ├── Pages/          # Vue.js pages
│   │   ├── Components/     # Reusable Vue components
│   │   └── Layouts/        # Page layouts
│   └── views/              # Blade templates
└── routes/
    └── web.php              # Web routes
```

## Key Technologies Used

- **Backend**: Laravel 10, PHP 8.1+
- **Frontend**: Vue.js 3, Inertia.js, Tailwind CSS
- **Database**: MySQL with Eloquent ORM
- **Authentication**: Laravel Breeze with Sanctum
- **Permissions**: Spatie Laravel Permission
- **Charts**: Chart.js
- **PDF**: DomPDF
- **Excel**: Maatwebsite Excel

## Features Comparison with Original

| Feature | Original (CodeIgniter) | Modern (Laravel) |
|---------|----------------------|------------------|
| Framework | CodeIgniter 4 | Laravel 10 |
| Frontend | jQuery/Bootstrap | Vue.js 3 + Tailwind |
| Database | Raw SQL | Eloquent ORM |
| Authentication | Custom | Laravel Breeze |
| API | None | RESTful APIs |
| Real-time | No | Yes (Inertia.js) |
| Mobile | Limited | Fully Responsive |
| Performance | Good | Excellent |
| Maintainability | Moderate | High |

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support, email support@pharmacare.com or create an issue in the repository.

## Screenshots

### Dashboard
![Dashboard](screenshots/dashboard.png)

### POS Interface
![POS](screenshots/pos.png)

### Medicine Management
![Medicines](screenshots/medicines.png)

### Invoice Management
![Invoices](screenshots/invoices.png)

---

**PharmaCare Modern** - Transforming pharmacy management with modern technology.
