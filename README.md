# Gumamela Daycare Center Management System

A comprehensive web-based daycare management system built with HTML, CSS, JavaScript, Bootstrap, PHP, and MySQL.

## Features

### Parent Portal
- **Dashboard**: View announcements and parent resources
- **Messaging System**: Communicate with daycare administrators
- **Child Enrollment**: Submit enrollment requests (can be disabled by admin)
- **Profile Management**: Update personal information
- **Notifications**: Receive important updates
- **Resources**: Access learning activities, events, and FAQs

### Admin Portal
- **Dashboard**: Overview with statistics and quick actions
- **Student Management**: Add, edit, delete, and register children
- **Parent Management**: Approve enrollments, manage parent accounts
- **Attendance Tracking**: Record and monitor daily attendance with charts
- **Progress Monitoring**: Create and manage student progress reports
- **Announcements**: Post, edit, and delete announcements visible to parents
- **Learning Activities**: Manage educational activities and resources
- **Events Management**: Create and manage daycare events
- **FAQ Management**: Maintain frequently asked questions
- **Notifications System**: Send notifications to parents
- **Enrollment Control**: Toggle parent enrollment form visibility

## Technical Stack

- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5.3.0
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Charts**: Chart.js for attendance visualization
- **Icons**: Font Awesome 6.0.0

## Installation

### Prerequisites
- XAMPP, WAMP, or similar local server environment
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser (Chrome, Firefox, Safari, Edge)

### Setup Instructions

1. **Clone or Download the Project**
   ```
   Place the project files in your web server directory (e.g., htdocs for XAMPP)
   ```

2. **Database Setup**
   - Open phpMyAdmin or your preferred MySQL client
   - Import the database schema from `database/daycare_db.sql`
   - This will create the `gumamela_daycare` database with all required tables

3. **Database Configuration**
   - Open `config/database.php`
   - Update the database credentials if needed:
     ```php
     private $host = "localhost";
     private $db_name = "gumamela_daycare";
     private $username = "root";
     private $password = "";
     ```

4. **Start Your Web Server**
   - Start Apache and MySQL services
   - Navigate to `http://localhost/NewDaycare/` in your browser

## Default Login Credentials

### Admin Account
- **Username**: `admin`
- **Password**: `password` (default hash in database)
- **Email**: `admin@gumamela-daycare.com`

### Parent Registration
- Parents can register through the registration page
- Admin approval is required for new parent accounts

## Project Structure

```
NewDaycare/
├── admin/                  # Admin portal pages
│   ├── dashboard.php
│   ├── students.php
│   ├── parents.php
│   └── ...
├── assets/                 # Static assets
│   ├── css/               # Stylesheets
│   │   ├── admin.css
│   │   ├── parent.css
│   │   ├── login.css
│   │   └── register.css
│   └── js/                # JavaScript files
│       ├── admin.js
│       ├── parent.js
│       ├── login.js
│       └── register.js
├── auth/                  # Authentication
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── config/                # Configuration
│   └── database.php
├── database/              # Database schema
│   └── daycare_db.sql
├── parent/                # Parent portal pages
│   └── dashboard.php
├── index.php              # Login page
├── register.php           # Registration page
└── README.md
```

## Design Features

### Responsive Design
- Mobile-first approach with Bootstrap
- Collapsible sidebar navigation for mobile devices
- Responsive tables and cards
- Touch-friendly interface elements

### Color Scheme
- **Primary**: Pink gradient (#FF6B9D to #FF8E9B) for sidebars
- **Secondary**: Turquoise gradient (#4ECDC4 to #44A08D) for login/register pages
- **Accent Colors**: Green, Blue, Orange, Red for status indicators and statistics

### UI Components
- Modern card-based layout
- Gradient backgrounds and buttons
- Icon-based navigation with Font Awesome
- Interactive charts with Chart.js
- Modal dialogs for forms
- Toast notifications for user feedback

## Security Features

- **Password Hashing**: Uses PHP's `password_hash()` and `password_verify()`
- **Session Management**: Secure PHP sessions with proper validation
- **SQL Injection Protection**: Prepared statements with PDO
- **Input Validation**: Client-side and server-side validation
- **Role-Based Access Control**: Separate admin and parent access levels
- **CSRF Protection**: Form tokens for sensitive operations

## Database Schema

### Key Tables
- `users`: Admin and parent accounts
- `students`: Child information and enrollment data
- `attendance`: Daily attendance records
- `announcements`: System-wide announcements
- `messages`: Parent-admin communication
- `progress_reports`: Student progress tracking
- `learning_activities`: Educational resources
- `events`: Daycare events and activities
- `faqs`: Frequently asked questions
- `notifications`: User notifications
- `system_settings`: Application configuration

## Browser Compatibility

- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is created for educational purposes. Please ensure you have proper licensing for any production use.

## Support

For support or questions about this daycare management system, please contact the development team.

---

**Gumamela Daycare Center Management System**  
Version 1.0 - Built with ❤️ for better daycare management
