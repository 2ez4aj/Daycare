# 🌸 Gumamela Daycare Management System - Beginner's Guide

## 📖 What is this system?
This is a web-based daycare management system built with PHP and MySQL. It helps daycare centers manage:
- Parent and child enrollment
- Student attendance tracking
- Communication between parents and staff
- Learning activities and progress reports
- File uploads (photos, documents)

## 🏗️ System Structure

### 📁 Main Folders
```
NewDaycare/
├── admin/          # Admin portal pages (for daycare staff)
├── parent/         # Parent portal pages (for parents)
├── auth/           # Login and registration pages
├── config/         # Database and upload configuration
├── database/       # Database schema and setup files
├── assets/         # CSS, JavaScript, and images
└── uploads/        # Uploaded files (photos, documents)
```

### 🔑 Key Files Explained

#### **Database Connection (`config/database.php`)**
- Connects to MySQL database
- Contains database credentials
- Used by all pages that need database access

#### **File Upload Handler (`config/upload.php`)**
- Handles photo and document uploads
- Validates file types and sizes
- Stores files securely

#### **Parent Portal (`parent/` folder)**
- `dashboard.php` - Parent's main page
- `enroll.php` - Child enrollment form
- `messages.php` - Communication with daycare
- `attendance.php` - View child's attendance
- `profile.php` - Manage parent profile

#### **Admin Portal (`admin/` folder)**
- `dashboard.php` - Admin's main page
- `students.php` - Manage enrolled children
- `parents.php` - Manage parent accounts
- `messages.php` - Communicate with parents

## 🔐 How Authentication Works

### Sessions
```php
session_start();  // Start tracking user login
$_SESSION['user_id']    // Stores logged-in user's ID
$_SESSION['user_type']  // 'parent' or 'admin'
$_SESSION['first_name'] // User's first name
```

### Security Checks
Every page checks if user is logged in:
```php
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'parent') {
    header('Location: ../index.php');  // Redirect to login
    exit();
}
```

## 🗄️ Database Structure

### Main Tables
1. **users** - Stores parent and admin accounts
2. **students** - Stores enrolled children information
3. **messages** - Communication between parents and staff
4. **attendance** - Daily attendance records
5. **schedules** - Available time slots for children

### Example Database Query
```php
// Get all messages for current user
$stmt = $conn->prepare("SELECT * FROM messages WHERE recipient_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

## 📝 Form Processing

### How Forms Work
1. **HTML Form** - User fills out information
2. **PHP Processing** - Server receives and validates data
3. **Database Storage** - Information saved to database
4. **Redirect** - User sent to success/error page

### Example Form Processing
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    
    // Validate data
    if (empty($first_name)) {
        $error = "First name is required";
    }
    
    // Save to database
    $stmt = $conn->prepare("INSERT INTO students (first_name, last_name) VALUES (?, ?)");
    $stmt->execute([$first_name, $last_name]);
}
```

## 📤 File Upload System

### How File Uploads Work
1. **HTML Form** - Must include `enctype="multipart/form-data"`
2. **PHP Processing** - Validates file type and size
3. **File Storage** - Saves file to `uploads/` folder
4. **Database Path** - Stores file path in database

### File Upload Example
```php
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $uploader = new FileUpload($uploadsDir);
    $result = $uploader->uploadFile($_FILES['photo'], 'students');
    
    if ($result['success']) {
        $photo_path = $result['path'];  // Save this path to database
    }
}
```

## 🎨 Frontend Structure

### CSS Framework
- **Bootstrap 5** - Responsive design framework
- **Font Awesome** - Icons
- **Custom CSS** - Daycare-specific styling

### JavaScript Features
- Form validation
- File upload previews
- Interactive elements
- AJAX for dynamic content

## 🚀 Getting Started

### 1. Setup Database
```sql
-- Run this in MySQL
CREATE DATABASE gumamela_daycare;
-- Import database/daycare_db.sql
```

### 2. Configure Database Connection
Edit `config/database.php`:
```php
$host = 'localhost';
$dbname = 'gumamela_daycare';
$username = 'your_username';
$password = 'your_password';
```

### 3. Set File Permissions
Make sure `uploads/` folder is writable:
```bash
chmod 755 uploads/
```

### 4. Create Admin Account
Run `setup.php` to create first admin account

## 🔧 Common Tasks

### Adding a New Page
1. Create PHP file in appropriate folder (`parent/` or `admin/`)
2. Include session check and database connection
3. Add navigation menu item
4. Style with Bootstrap classes

### Adding Database Field
1. Update `database/daycare_db.sql`
2. Create migration script if needed
3. Update forms to include new field
4. Update PHP processing code

### Debugging Tips
- Check browser console for JavaScript errors
- Look at PHP error logs
- Use `var_dump()` to inspect variables
- Test database queries in phpMyAdmin

## 📚 Learning Resources

### PHP Basics
- Variables: `$variable_name`
- Arrays: `$array = ['item1', 'item2']`
- Functions: `function myFunction() { }`
- Classes: `class MyClass { }`

### MySQL Basics
- SELECT: Get data from database
- INSERT: Add new data
- UPDATE: Modify existing data
- DELETE: Remove data

### Security Best Practices
- Always use prepared statements
- Validate user input
- Sanitize output with `htmlspecialchars()`
- Check user permissions

## 🆘 Troubleshooting

### Common Issues
1. **Database Connection Error** - Check credentials in `config/database.php`
2. **File Upload Fails** - Check folder permissions and file size limits
3. **Session Issues** - Make sure `session_start()` is called
4. **CSS Not Loading** - Check file paths and web server configuration

### Error Messages
- **500 Internal Server Error** - Check PHP error logs
- **404 Not Found** - Check file paths and URLs
- **Access Denied** - Check user permissions and session

## 💡 Tips for Beginners

1. **Start Small** - Understand one file at a time
2. **Use Comments** - Document your code changes
3. **Test Frequently** - Check each change works
4. **Learn by Doing** - Modify existing code to see what happens
5. **Ask Questions** - Don't hesitate to seek help

## 🔄 Development Workflow

1. **Plan** - What feature do you want to add?
2. **Design** - How will it work?
3. **Code** - Write the PHP/HTML/CSS
4. **Test** - Make sure it works
5. **Debug** - Fix any issues
6. **Deploy** - Put it live

Remember: Programming is about solving problems step by step. Take your time and don't be afraid to experiment!
