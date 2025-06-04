# Web Portal for Healthcare Staff

This is a simple PHP-based web portal designed for corporate healthcare personnel. User information is stored in a MySQL database rather than `users.json`.

- **Shift Management** (`shift.php`)
- **Trainings** (`training.php`)
- **Exams** (`exam.php`)
- **Procedure Documents** (`procedure.php`)
- **User Login/Registration** (`login.php`, `register.php`)
- **Admin Panel** (`admin.php`)
- **User Profiles** (`profile.php`)

## Usage

Serve the project through a PHP-enabled web server. The entry point is `index.php`.
Create a MySQL database (e.g. `webportal`) with a table named `users`:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL
);
```

Additionally create tables for the application data:

```sql
CREATE TABLE shifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    time VARCHAR(50) NOT NULL
);

CREATE TABLE trainings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL
);

CREATE TABLE exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    date DATE NOT NULL
);

CREATE TABLE procedures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    file VARCHAR(100) NOT NULL
);

CREATE TABLE profiles (
    user_id INT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

Edit `db.php` if your database credentials differ from the defaults.

The interface includes a minimal style sheet (`style.css`) for a cleaner look.

Example using PHP's built-in server:

```bash
php -S localhost:8000
```

Navigate to `http://localhost:8000` in your browser. Register a user and log in to access the modules. Insert an admin account in the `users` table (for example `admin` / `admin123`).
