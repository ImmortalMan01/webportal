# Web Portal for Healthcare Staff

This is a simple PHP-based web portal designed for corporate healthcare personnel. It now supports user accounts in addition to the basic modules.

- **Shift Management** (`shift.php`)
- **Trainings** (`training.php`)
- **Exams** (`exam.php`)
- **Procedure Documents** (`procedure.php`)
- **User Login/Registration** (`login.php`, `register.php`)
- **Admin Panel** (`admin.php`)

## Usage

Serve the project through a PHP-enabled web server. The entry point is `index.php`.

The interface includes a minimal style sheet (`style.css`) for a cleaner look.

Example using PHP's built-in server:

```bash
php -S localhost:8000
```

Navigate to `http://localhost:8000` in your browser. Register a user and log in to access the modules. The default admin account is `admin` with password `admin123`.
