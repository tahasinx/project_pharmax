# Terminal Interface - Hosting Environment Guide

This guide explains how to configure the terminal interface for different hosting environments.

## 🌐 **Cloud/cPanel Hosting**

### **Common Paths for cPanel/Shared Hosting:**

```php
// Typical cPanel paths
'composer' => '/usr/local/bin/composer',
'php' => '/usr/local/bin/php',
'node' => '/usr/local/bin/node',
'npm' => '/usr/local/bin/npm',
'git' => '/usr/bin/git',
```

### **Popular Hosting Providers:**

#### **1. cPanel Shared Hosting (Bluehost, HostGator, etc.)**
```php
'composer' => '/usr/local/bin/composer',
'php' => '/usr/local/bin/php',
'node' => '/usr/local/bin/node',
'npm' => '/usr/local/bin/npm',
```

#### **2. Cloudflare Pages**
```php
'composer' => '/usr/local/bin/composer',
'php' => '/usr/local/bin/php',
'node' => '/usr/local/bin/node',
'npm' => '/usr/local/bin/npm',
```

#### **3. AWS EC2/Elastic Beanstalk**
```php
'composer' => '/usr/local/bin/composer',
'php' => '/usr/bin/php',
'node' => '/usr/local/bin/node',
'npm' => '/usr/local/bin/npm',
'git' => '/usr/bin/git',
```

#### **4. DigitalOcean Droplets**
```php
'composer' => '/usr/local/bin/composer',
'php' => '/usr/bin/php',
'node' => '/usr/local/bin/node',
'npm' => '/usr/local/bin/npm',
'git' => '/usr/bin/git',
```

#### **5. Heroku**
```php
'composer' => '/app/vendor/bin/composer',
'php' => '/usr/bin/php',
'node' => '/usr/local/bin/node',
'npm' => '/usr/local/bin/npm',
```

## 🔧 **Configuration Steps**

### **Step 1: Update Configuration File**

Edit `config/terminal.php` and update the paths for your environment:

```php
'environments' => [
    'cloud' => [
        'composer' => '/path/to/your/composer',
        'php' => '/path/to/your/php',
        'node' => '/path/to/your/node',
        'npm' => '/path/to/your/npm',
        // ... other commands
    ],
],
```

### **Step 2: Find Your Command Paths**

#### **Method 1: Using PHP**
Create a test file to find paths:

```php
<?php
// test_paths.php
echo "PHP: " . PHP_BINARY . "\n";
echo "Composer: " . shell_exec('which composer') . "\n";
echo "Node: " . shell_exec('which node') . "\n";
echo "NPM: " . shell_exec('which npm') . "\n";
echo "Git: " . shell_exec('which git') . "\n";
?>
```

#### **Method 2: Using Terminal Commands**
```bash
which php
which composer
which node
which npm
which git
```

#### **Method 3: Using cPanel Terminal**
If your hosting provider offers cPanel terminal:
```bash
whereis php
whereis composer
whereis node
whereis npm
```

### **Step 3: Test Commands**

After updating paths, test the commands:

```bash
php --version
composer --version
node --version
npm --version
```

## 🚫 **Common Limitations**

### **Shared Hosting Restrictions:**
- **No shell access** - Some shared hosts don't allow shell commands
- **Limited permissions** - May not be able to execute certain commands
- **Path restrictions** - Commands may be in different locations
- **Security restrictions** - Some commands may be blocked

### **Solutions:**
1. **Use hosting provider's terminal** if available
2. **Contact support** for command paths
3. **Use alternative methods** (FTP, file manager)
4. **Upgrade to VPS** for full control

## 🔒 **Security Considerations**

### **Production Environment:**
```php
// Disable terminal in production
'allowed_commands' => [
    // Only allow safe commands
    'php' => ['--version', '-v'],
    'ls' => ['-la'],
    'pwd' => [],
    'date' => []
],
```

### **Add Authentication:**
```php
// In TerminalController
public function __construct()
{
    $this->middleware('auth');
    $this->middleware('can:execute_commands'); // If using permissions
}
```

## 📋 **Environment Detection**

The system automatically detects:
- **Windows/XAMPP** - Local development
- **Cloud/cPanel** - Shared hosting
- **Linux/Mac** - VPS or local development

### **Manual Override:**
```php
// Force environment type
'force_environment' => 'cloud', // or 'windows', 'linux'
```

## 🛠️ **Troubleshooting**

### **Command Not Found:**
1. Check if command exists: `which command_name`
2. Verify path in configuration
3. Check file permissions
4. Contact hosting support

### **Permission Denied:**
1. Check file permissions
2. Verify user has execute rights
3. Check hosting restrictions
4. Use alternative paths

### **Timeout Issues:**
1. Increase timeout in config
2. Check hosting limits
3. Optimize command execution
4. Use background processing

## 📞 **Hosting Provider Support**

### **Common Questions to Ask:**
1. "What are the paths to PHP, Composer, Node.js, and NPM?"
2. "Do you allow shell command execution?"
3. "Are there any restrictions on command execution?"
4. "Can I access the terminal/SSH?"

### **Alternative Solutions:**
1. **FTP/SFTP** - Upload files manually
2. **File Manager** - Use hosting provider's file manager
3. **Git Integration** - Use Git for deployment
4. **CI/CD** - Use automated deployment

## 🎯 **Best Practices**

1. **Test locally first** - Ensure commands work in development
2. **Use relative paths** when possible
3. **Implement fallbacks** for missing commands
4. **Log command execution** for debugging
5. **Monitor resource usage** on shared hosting
6. **Use caching** to reduce command execution
7. **Implement rate limiting** for production

## 📚 **Additional Resources**

- [Laravel Process Documentation](https://laravel.com/docs/processes)
- [Symfony Process Component](https://symfony.com/doc/current/components/process.html)
- [cPanel Documentation](https://documentation.cpanel.net/)
- [Hosting Provider Guides](https://laravel.com/docs/deployment)
