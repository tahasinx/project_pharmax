# PharmaCare Modern - Installation Guide

## 🚀 Quick Installation

### Option 1: Web-Based Installation (Recommended)

1. **Upload Files**: Upload all files to your web server
2. **Set Permissions**: Ensure proper file permissions
3. **Visit Installation**: Go to `https://your-domain.com/install`
4. **Follow Wizard**: Complete the 5-step installation process
5. **Login**: Use your admin credentials to access the system

### Option 2: Command Line Installation

```bash
# 1. Clone/Download the application
git clone https://github.com/your-username/pharmacare-modern.git
cd pharmacare-modern

# 2. Install dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build

# 3. Set up environment
cp .env.example .env
php artisan key:generate

# 4. Configure database in .env file
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=pharmacare
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# 5. Run installation
php artisan migrate --force
php artisan db:seed --force

# 6. Set permissions
chmod -R 755 storage bootstrap/cache

# 7. Create installation flag
echo "$(date)" > storage/app/installed
```

### Option 3: Automated Installation Script

```bash
# Make script executable
chmod +x install.sh

# Run installation
./install.sh
```

---

## 📋 System Requirements

### Server Requirements
- **PHP**: 8.1 or higher
- **MySQL**: 8.0 or higher
- **Web Server**: Apache/Nginx
- **Memory**: 256MB minimum (512MB recommended)
- **Disk Space**: 100MB minimum

### PHP Extensions Required
- ✅ **OpenSSL**: For secure connections
- ✅ **PDO**: For database operations
- ✅ **Mbstring**: For string handling
- ✅ **Tokenizer**: For PHP parsing
- ✅ **XML**: For XML processing
- ✅ **Ctype**: For character type checking
- ✅ **JSON**: For JSON processing
- ✅ **BCMath**: For arbitrary precision mathematics

### Directory Permissions
- ✅ **storage/**: Must be writable
- ✅ **bootstrap/cache/**: Must be writable
- ✅ **public/**: Must be writable

---

## 🔧 Installation Steps

### Step 1: System Requirements Check
The installer automatically checks:
- PHP version compatibility
- Required PHP extensions
- Directory permissions
- File system access

### Step 2: Database Configuration
Configure your MySQL database:
- **Host**: Usually `localhost` or `127.0.0.1`
- **Port**: Default `3306`
- **Database Name**: Create a new database
- **Username**: Database username
- **Password**: Database password

### Step 3: Application Configuration
Set up your application:
- **Application Name**: Your pharmacy name
- **Application URL**: Your domain URL
- **Admin Account**: Administrator credentials
- **Company Information**: Business details

### Step 4: Installation Process
The installer will:
- Update configuration files
- Run database migrations
- Create admin user account
- Seed initial data
- Set up permissions
- Create installation flag

### Step 5: Complete
Installation is complete! You can now:
- Login with admin credentials
- Configure additional settings
- Start using the system

---

## 🌐 Web Server Configuration

### Apache Configuration

Create `.htaccess` file in public directory:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/pharmacare-modern/public;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 🔒 Security Configuration

### File Permissions
```bash
# Set proper permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Make specific directories writable
chmod -R 755 storage bootstrap/cache
chmod -R 755 public/uploads
```

### Environment Security
```env
# Production settings
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database security
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pharmacare_production
DB_USERNAME=secure_username
DB_PASSWORD=strong_password

# Session security
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=your-domain.com
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict
```

---

## 📧 Email Configuration

### SMTP Configuration
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="PharmaCare"
```

### Mailgun Configuration
```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your-domain.mailgun.org
MAILGUN_SECRET=your-mailgun-secret
```

---

## 📱 SMS Configuration

### Twilio Configuration
```env
SMS_PROVIDER=twilio
TWILIO_SID=your-twilio-sid
TWILIO_TOKEN=your-twilio-token
TWILIO_FROM=+1234567890
SMS_COUNTRY_CODE=1
```

### Nexmo Configuration
```env
SMS_PROVIDER=nexmo
NEXMO_KEY=your-nexmo-key
NEXMO_SECRET=your-nexmo-secret
NEXMO_FROM=YourApp
SMS_COUNTRY_CODE=44
```

---

## 🗄️ Database Setup

### Create Database
```sql
CREATE DATABASE pharmacare CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'pharmacare_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON pharmacare.* TO 'pharmacare_user'@'localhost';
FLUSH PRIVILEGES;
```

### Backup Database
```bash
# Create backup
mysqldump -u username -p pharmacare > backup.sql

# Restore backup
mysql -u username -p pharmacare < backup.sql
```

---

## 🔧 Troubleshooting

### Common Issues

#### 1. Permission Errors
```bash
# Fix permissions
sudo chown -R www-data:www-data /path/to/pharmacare-modern
sudo chmod -R 755 /path/to/pharmacare-modern
sudo chmod -R 775 storage bootstrap/cache
```

#### 2. Database Connection Errors
- Check database credentials
- Ensure MySQL service is running
- Verify database exists
- Check firewall settings

#### 3. Composer Issues
```bash
# Clear composer cache
composer clear-cache

# Reinstall dependencies
rm -rf vendor
composer install --no-dev --optimize-autoloader
```

#### 4. NPM Issues
```bash
# Clear npm cache
npm cache clean --force

# Reinstall dependencies
rm -rf node_modules package-lock.json
npm install
npm run build
```

#### 5. File Upload Issues
```bash
# Check PHP upload settings
php -i | grep upload

# Update php.ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
```

### Error Logs
Check these locations for errors:
- `storage/logs/laravel.log`
- Web server error logs
- PHP error logs

---

## 🚀 Post-Installation

### 1. Initial Setup
- Login with admin credentials
- Configure company settings
- Set up email/SMS notifications
- Configure backup settings

### 2. User Management
- Create additional user accounts
- Assign roles and permissions
- Configure menu visibility

### 3. Data Import
- Import existing medicine data
- Import customer information
- Set up initial stock levels

### 4. System Configuration
- Configure tax rates
- Set currency settings
- Configure low stock alerts
- Set expiry notification days

---

## 📞 Support

### Installation Support
- **Email**: install@pharmacare.com
- **Documentation**: https://docs.pharmacare.com/install
- **Video Guide**: https://youtube.com/pharmacare-install

### Technical Support
- **Email**: support@pharmacare.com
- **Phone**: +1-800-PHARMACARE
- **Live Chat**: Available on website

---

## ✅ Installation Checklist

- [ ] System requirements met
- [ ] File permissions set correctly
- [ ] Database created and configured
- [ ] Environment file configured
- [ ] Dependencies installed
- [ ] Database migrated
- [ ] Admin user created
- [ ] Initial data seeded
- [ ] Installation flag created
- [ ] Web server configured
- [ ] SSL certificate installed
- [ ] Email/SMS configured
- [ ] Backup system configured
- [ ] Security settings applied

**🎉 Congratulations! PharmaCare Modern is now installed and ready to use!**
