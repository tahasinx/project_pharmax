# Installation System Setup

The installation system has been successfully ported from connect365_standalone to pharmacare-modern.

## What Was Added

### Controllers
- `app/Http/Controllers/InstallationController.php` - Main installation controller with 5-step wizard

### Middleware
- `app/Http/Middleware/CheckInstallation.php` - Checks if app is installed and redirects accordingly
- `app/Http/Middleware/ForceFileSessions.php` - Forces file-based sessions during installation

### Service Providers
- `app/Providers/InstallationServiceProvider.php` - Ensures file sessions during installation
- `app/Providers/InstallationBootstrapServiceProvider.php` - Handles bootstrap requirements

### Routes
- `routes/install.php` - All installation routes

### Views
- `resources/views/install/layout.blade.php` - Master layout
- `resources/views/install/pages/index.blade.php` - Step 1: Requirements check
- `resources/views/install/pages/database.blade.php` - Step 2: Database configuration
- `resources/views/install/pages/app.blade.php` - Step 3: Application configuration
- `resources/views/install/pages/admin.blade.php` - Step 4: Admin user creation
- `resources/views/install/pages/install.blade.php` - Step 5: Installation confirmation
- `resources/views/install/pages/complete.blade.php` - Installation complete

### CSS
- `public/app/root/css/install.css` - Installation page styles
- `public/app/root/css/welcome.css` - Welcome page styles

### Commands
- `app/Console/Commands/InstallReset.php` - Artisan command to reset installation

### Configuration Updates
- `routes/web.php` - Includes install routes
- `app/Http/Kernel.php` - Registers ForceFileSessions middleware
- `config/app.php` - Registers installation service providers
- `public/index.php` - Handles .env creation and APP_KEY generation before Laravel boots

## Required Assets

The installation system requires the following assets. You need to add them:

1. **Bootstrap CSS**: `public/app/backend/css/vendor/bootstrap.min.css`
   - Download from: https://getbootstrap.com/docs/5.3/getting-started/download/

2. **Select2 CSS**: `public/app/backend/css/vendor/select2.min.css`
   - Download from: https://select2.org/getting-started/installation

3. **Select2 JS**: `public/app/backend/js/vendor/select2.full.min.js`
   - Download from: https://select2.org/getting-started/installation

4. **jQuery**: Already loaded via CDN in admin.blade.php
   - Or download and place in: `public/app/backend/js/vendor/jquery.min.js`

5. **Fonts**: `public/app/fonts/instrument-sans/fonts.css`
   - Currently uses Google Fonts CDN as fallback
   - Add local font files if preferred

6. **Logo**: `public/app/root/logo/icon.svg`
   - Placeholder SVG created, replace with your actual logo

## Installation Flow

1. **Step 1**: System requirements check
2. **Step 2**: Database configuration (creates database if needed)
3. **Step 3**: Application configuration (.env file creation)
4. **Step 4**: Admin user creation (with Spatie permissions)
5. **Step 5**: Installation execution (migrations, admin user, lock file)

## Features

- ✅ Automatic database creation
- ✅ .env file generation from .env.example
- ✅ APP_KEY generation
- ✅ File-based sessions during installation
- ✅ Spatie permissions integration
- ✅ Admin role and permissions setup
- ✅ Installation lock file (`storage/install.lock`)
- ✅ Reset command: `php artisan install:reset`

## Usage

### Fresh Installation
1. Visit `/install` in your browser
2. Follow the 5-step wizard
3. Complete installation

### Reset Installation
```bash
php artisan install:reset
php artisan install:reset --backup-env  # Backup .env before reset
php artisan install:reset --db          # Also reset database
php artisan install:reset --force       # Skip confirmation
```

## Notes

- The installation system uses `storage/install.lock` to track installation status
- All routes redirect to `/install` if not installed (except install routes)
- File-based sessions are forced during installation to avoid database dependency
- Admin user is created with Spatie 'admin' role and all permissions
- The system automatically creates roles: admin, manager, cashier

