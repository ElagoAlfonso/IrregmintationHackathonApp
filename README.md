# Faculty Evaluation System

A web-based application for managing faculty evaluations in an academic institution. This system allows students, program heads, deans, and administrators to participate in the evaluation process with role-based access control.

## Features

- **Role-based Access**: Different interfaces for students, program heads, deans, and administrators
- **Evaluation Roles**: Only students, program heads, and deans can evaluate faculty members
- **Program Head Interface**: Program heads have a dedicated interface to evaluate professors and manage program-level evaluations
- **Evaluation Forms**: Structured forms for collecting feedback on faculty performance
- **User Management**: Registration, login, and user role management
- **Database Integration**: MySQL database for storing users, evaluations, and audit logs
- **Audit Logging**: Tracks all evaluation activities for transparency

## Technology Stack

- **Backend**: PHP
- **Frontend**: HTML, CSS, JavaScript
- **Database**: MySQL
- **Server**: Apache (via XAMPP)

## Installation and Setup

### Prerequisites
- XAMPP (or similar PHP/MySQL environment)
- Web browser

### Steps
1. **Clone/Download the project** into your XAMPP htdocs directory:
   ```
   c:\xampp\htdocs\hackathon\
   ```

2. **Start XAMPP**:
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

3. **Set up the database**:
   - Open your browser and go to: `http://localhost/hackathon/backend/setup_database.php`
   - This creates the necessary database tables

4. **Seed test users** (optional):
   - Go to: `http://localhost/hackathon/backend/seed_users.php`
   - This adds sample users for testing

5. **Access the application**:
   - Main URL: `http://localhost/hackathon/frontend/public/`
   - Login page: `http://localhost/hackathon/frontend/views/auth/login.php`

### Test Credentials
After seeding users, you can log in with:
- **Admin**: admin@test.com / admin123
- **Student**: student@test.com / student123
- **Program Head**: program_head@test.com / ph123

## Project Structure

```
hackathon/
├── backend/                 # PHP backend files
│   ├── config/             # Database configuration
│   ├── controllers/        # Business logic controllers
│   ├── models/            # Data models
│   ├── database/          # SQL schema
│   └── logs/              # Audit logging
├── frontend/               # Web frontend
│   ├── public/            # Public assets (CSS, JS)
│   └── views/             # PHP view templates
│       ├── auth/          # Authentication pages
│       ├── admin/         # Admin dashboard
│       ├── dean/          # Dean evaluation form
│       ├── program_head/  # Program head form
│       └── student/       # Student evaluation form
```

## Usage

1. **Login** with appropriate credentials based on your role
2. **Students** can submit evaluations for faculty members
3. **Program Heads** can evaluate professors using the dedicated program head interface and manage evaluations for their program
4. **Deans** can evaluate faculty and oversee evaluations across departments
5. **Administrators** have full access to manage users, view reports, and system settings

- Evaluations require comments and show a success confirmation after submission.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is for educational purposes. Please check with your institution for licensing requirements.

## Support

For issues or questions, please check the backend logs or contact the development team.