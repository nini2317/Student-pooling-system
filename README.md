# Student Polling System

A comprehensive web-based polling and survey system designed for educational institutions to gather student feedback and opinions.

## Features

### 🔐 Authentication System
- User registration and login with secure password hashing
- Role-based access control (Admin/Student)
- Session management for secure access

### 🎨 User Interface
- **Responsive Design**: Works seamlessly on desktop, tablet, and mobile devices
- **Dark/Light Theme Toggle**: Users can switch between light and dark modes
- **Modern UI**: Clean, intuitive interface with smooth animations
- **Logo Placeholder**: Space reserved for institutional branding

### 👨‍🎓 Student Features
- **Dashboard**: Personalized welcome screen with activity overview
- **Participate in Polls**: Vote on active polls with real-time results
- **Take Surveys**: Provide detailed feedback through open-ended surveys
- **View Results**: See poll results after participation
- **Edit Profile**: Update personal information and change password
- **Submit Feedback**: Share suggestions and report issues

### 👨‍💼 Admin Features
- **Dashboard**: Comprehensive overview with statistics and analytics
- **Create Polls**: Design multiple-choice polls with customizable options
- **View All Polls**: Manage existing polls, view statistics, and delete
- **Create Surveys**: Design open-ended surveys for detailed feedback
- **View All Surveys**: Manage surveys and view student responses
- **Manage Users**: View user statistics, change roles, and manage accounts
- **Manage Feedback**: Review and respond to student feedback

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5, Font Awesome
- **Backend**: PHP 8.2
- **Database**: MySQL/MariaDB
- **Server**: XAMPP (Apache)

## Installation

### Prerequisites
- XAMPP (or similar web server with PHP and MySQL)
- Web browser (Chrome, Firefox, Safari, Edge)

### Setup Instructions

1. **Download/Clone the Project**
   ```bash
   git clone <repository-url>
   # or extract the ZIP file to your htdocs directory
   ```

2. **Database Setup**
   - Start XAMPP and launch phpMyAdmin
   - Create a new database named `student_polling_system`
   - Import the provided `student_polling_system.sql` file
   - Verify all tables are created correctly

3. **Configure Database Connection**
   - Open `includes/config.php`
   - Update database credentials if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'student_polling_system');
   ```

4. **Start the Server**
   - Start Apache and MySQL from XAMPP control panel
   - Navigate to `http://localhost/S/` in your web browser

## Default Login Credentials

### Admin Account
- **Email**: admin@gmail.com
- **Password**: password

### Student Accounts
- **Email**: example@gmail.com
- **Password**: password

- **Email**: test@gmail.com
- **Password**: password

- **Email**: ram@gmail.com
- **Password**: password

## Project Structure

```
S/
├── admin/                  # Admin-specific pages
│   ├── dashboard.php
│   ├── create_poll.php
│   ├── view_polls.php
│   ├── create_survey.php
│   ├── view_surveys.php
│   ├── manage_users.php
│   └── manage_feedback.php
├── student/                # Student-specific pages
│   ├── dashboard.php
│   ├── vote_poll.php
│   ├── take_survey.php
│   ├── profile.php
│   └── feedback.php
├── includes/               # Core functionality
│   ├── config.php         # Database configuration
│   └── functions.php      # Helper functions
├── assets/                 # Static assets
│   ├── css/
│   │   └── style.css      # Main stylesheet
│   ├── js/
│   │   └── theme.js       # JavaScript functionality
│   └── images/            # Image assets (logo placeholder)
├── index.php               # Login/Registration page
├── logout.php              # Logout handler
├── student_polling_system.sql  # Database schema
└── README.md               # This file
```

## Database Schema

The system uses the following main tables:

- **users**: Stores user information and authentication data
- **polls**: Contains poll questions and metadata
- **poll_options**: Stores individual poll options
- **votes**: Records user votes on polls
- **surveys**: Contains survey questions and descriptions
- **survey_responses**: Stores student survey responses
- **feedback**: Collects user feedback and suggestions

## Usage Guide

### For Students

1. **Registration/Login**: Use the login page to access the system
2. **Dashboard**: View available polls and surveys
3. **Participate**: Click on polls to vote, surveys to respond
4. **Results**: View poll results after voting
5. **Profile**: Update personal information
6. **Feedback**: Submit suggestions or report issues

### For Administrators

1. **Login**: Use admin credentials to access admin dashboard
2. **Create Polls**: Design multiple-choice questions with options
3. **Create Surveys**: Set up open-ended feedback forms
4. **Manage Users**: View statistics and manage user accounts
5. **Review Feedback**: Monitor and respond to student feedback
6. **Analytics**: Track participation and engagement metrics

## Customization

### Adding Your Logo
1. Place your logo file in `assets/images/`
2. Update the logo placeholder in the HTML files:
   ```html
   <img src="assets/images/your-logo.png" alt="Institution Logo" class="logo">
   ```

### Theme Customization
- Modify CSS variables in `assets/css/style.css`:
  ```css
  :root {
      --primary-color: #your-color;
      --secondary-color: #your-color;
      /* ... other variables */
  }
  ```

### Database Configuration
- Update connection settings in `includes/config.php`
- Modify table structure as needed in the SQL file

## Security Features

- **Password Hashing**: Uses PHP's `password_hash()` for secure password storage
- **SQL Injection Prevention**: Prepared statements for all database queries
- **XSS Protection**: Input sanitization and output escaping
- **Session Management**: Secure session handling with proper timeout
- **Role-Based Access**: Proper authorization checks for admin functions

## Browser Compatibility

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Mobile Responsiveness

The system is fully responsive and works on:
- Smartphones (iOS/Android)
- Tablets
- Desktop computers

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify XAMPP services are running
   - Check database credentials in `config.php`
   - Ensure database exists and is properly imported

2. **Login Issues**
   - Verify user exists in database
   - Check password hashing compatibility
   - Clear browser cache and cookies

3. **Theme Toggle Not Working**
   - Check JavaScript console for errors
   - Verify `theme.js` is properly loaded
   - Check browser localStorage support

4. **Poll/Survey Not Saving**
   - Check database table permissions
   - Verify form validation
   - Check PHP error logs

### Getting Help

For technical support:
1. Check browser console for JavaScript errors
2. Review XAMPP/Apache error logs
3. Verify database connectivity
4. Test with different browsers

## Contributing

To contribute to this project:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is open source and available under the MIT License.

## Version History

- **v1.0.0**: Initial release with core functionality
  - User authentication and role management
  - Poll creation and participation
  - Survey system
  - Feedback management
  - Responsive design with theme toggle

---

**Note**: This system is designed for educational purposes and should be reviewed for production use. Always ensure proper security measures are in place when deploying in a live environment.
