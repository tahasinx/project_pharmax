#!/bin/bash

# PharmaCare Modern Installation Script
# This script automates the installation process for PharmaCare Modern

set -e

echo "🚀 PharmaCare Modern Installation Script"
echo "========================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   print_error "This script should not be run as root for security reasons"
   exit 1
fi

# Check system requirements
print_status "Checking system requirements..."

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;" 2>/dev/null || echo "0")
if [[ $(echo "$PHP_VERSION 8.1" | awk '{print ($1 >= $2)}') -eq 0 ]]; then
    print_error "PHP 8.1 or higher is required. Current version: $PHP_VERSION"
    exit 1
fi
print_success "PHP version: $PHP_VERSION"

# Check Composer
if ! command -v composer &> /dev/null; then
    print_error "Composer is not installed. Please install Composer first."
    exit 1
fi
print_success "Composer is installed"

# Check Node.js
if ! command -v node &> /dev/null; then
    print_error "Node.js is not installed. Please install Node.js 18+ first."
    exit 1
fi
NODE_VERSION=$(node --version)
print_success "Node.js version: $NODE_VERSION"

# Check NPM
if ! command -v npm &> /dev/null; then
    print_error "NPM is not installed. Please install NPM first."
    exit 1
fi
print_success "NPM is installed"

# Check MySQL
if ! command -v mysql &> /dev/null; then
    print_error "MySQL is not installed. Please install MySQL 8.0+ first."
    exit 1
fi
print_success "MySQL is installed"

print_success "All system requirements met!"

# Installation steps
print_status "Starting installation process..."

# Step 1: Install PHP dependencies
print_status "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader
print_success "PHP dependencies installed"

# Step 2: Install Node.js dependencies
print_status "Installing Node.js dependencies..."
npm install
print_success "Node.js dependencies installed"

# Step 3: Environment setup
print_status "Setting up environment..."
if [ ! -f .env ]; then
    cp .env.example .env
    print_success "Environment file created"
else
    print_warning "Environment file already exists, skipping..."
fi

# Generate application key
print_status "Generating application key..."
php artisan key:generate
print_success "Application key generated"

# Step 4: Database setup
print_status "Setting up database..."
read -p "Enter your database name: " DB_NAME
read -p "Enter your database username: " DB_USER
read -s -p "Enter your database password: " DB_PASS
echo

# Update .env file with database credentials
sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env

print_status "Testing database connection..."
if php artisan migrate:status &> /dev/null; then
    print_success "Database connection successful"
else
    print_error "Database connection failed. Please check your credentials."
    exit 1
fi

# Step 5: Run migrations
print_status "Running database migrations..."
php artisan migrate --force
print_success "Database migrations completed"

# Step 6: Seed database
print_status "Seeding database with initial data..."
php artisan db:seed --force
print_success "Database seeded successfully"

# Step 7: Build assets
print_status "Building frontend assets..."
npm run build
print_success "Frontend assets built"

# Step 8: Set permissions
print_status "Setting file permissions..."
chmod -R 755 storage bootstrap/cache
print_success "File permissions set"

# Step 9: Create storage links
print_status "Creating storage links..."
php artisan storage:link
print_success "Storage links created"

# Step 10: Clear caches
print_status "Clearing application caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
print_success "Caches cleared"

# Step 11: Run tests
print_status "Running tests..."
if php artisan test --parallel; then
    print_success "All tests passed"
else
    print_warning "Some tests failed, but installation can continue"
fi

# Installation complete
echo ""
echo "🎉 Installation completed successfully!"
echo "======================================"
echo ""
echo "Default login credentials:"
echo "Email: admin@pharmacare.com"
echo "Password: password"
echo ""
echo "Next steps:"
echo "1. Update your web server configuration"
echo "2. Configure SSL certificate"
echo "3. Set up email/SMS notifications"
echo "4. Configure backup settings"
echo "5. Review security settings"
echo ""
echo "For more information, visit: https://docs.pharmacare.com"
echo ""
print_success "PharmaCare Modern is ready to use!"
