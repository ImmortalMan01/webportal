# Web Portal for Healthcare Staff

This is a simple PHP-based web portal designed for corporate healthcare personnel. User information is stored in a MySQL database rather than `users.json`.

- **Shift Management** (`shift.php`)
- **Trainings** (`training.php`)
- **Exams** (`exam.php`)
- **Procedure Documents** (`procedure.php`)
- **User Login/Registration** (`pages/login.php`, `pages/register.php`)
- **Admin Panel** (`pages/admin.php`)
- **User Profiles** (`pages/profile.php`)

The main scripts are placed under the `pages/` directory while reusable code
resides in `includes/`. Static assets such as the stylesheet live in
`assets/`.

## Usage

Serve the project through a PHP-enabled web server. The entry point is `index.php`.
Create a MySQL database (e.g. `webportal`) with a table named `users`:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL,
    last_active TIMESTAMP NULL DEFAULT NULL
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
    birthdate DATE,
    picture VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    file VARCHAR(50) NOT NULL
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Example initial modules
INSERT INTO modules (name, file) VALUES
    ('Vardiya Sistemi','shift'),
    ('Eğitimler','training'),
    ('Sınavlar','exam'),
    ('Prosedürler','procedure');
```

Edit `includes/db.php` if your database credentials differ from the defaults.

The interface includes a minimal style sheet (`assets/style.css`) for a cleaner look.

Example using PHP's built-in server:

```bash
php -S localhost:8000
```

The messaging interface uses a small WebSocket server written in Node.js.
Install the dependencies once and start it in a separate terminal:

```bash
npm install
```

Then run the WebSocket server:

```bash
node ws-server.js
```

Navigate to `http://localhost:8000` in your browser. Register a user and log in to access the modules. Insert an admin account in the `users` table (for example `admin` / `admin123`).
