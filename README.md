# Web Portal for Healthcare Staff

This is a simple PHP-based web portal designed for corporate healthcare personnel. User information is stored in a MySQL database rather than `users.json`.

- **Çalışma Listesi** (`shift.php`)
- **Trainings** (`training.php`)
- **Exams** (`exam.php`)
- **Procedure Documents** (`procedure.php`)
- **User Login/Registration** (`pages/login.php`, `pages/register.php`)
- **Admin Panel** (`pages/admin.php`)
- **User Profiles** (`pages/profile.php`)
- **Role-based Home Page** with widgets for Doctor, Nurse and Secretary

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

CREATE TABLE experiences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    exp_date DATE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    file VARCHAR(50) NOT NULL,
    icon VARCHAR(50),
    description VARCHAR(255),
    color VARCHAR(20),
    badge VARCHAR(50),
    badge_class VARCHAR(20),
    enabled TINYINT(1) NOT NULL DEFAULT 1
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

CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    publish_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE settings (
    name VARCHAR(50) PRIMARY KEY,
    value VARCHAR(50) NOT NULL
);

CREATE TABLE site_pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) UNIQUE NOT NULL,
    title VARCHAR(100) NOT NULL,
    content TEXT NOT NULL
);

CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    details TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO settings (name, value) VALUES
    ('registrations_open','1'),
    ('hide_register_button','0'),
    ('site_name','Sağlık Personeli Portalı'),
    -- SMTP defaults
    ('smtp_host',''),
    ('smtp_port',''),
    ('smtp_user',''),
    ('smtp_pass',''),
    ('smtp_secure',''),
    ('smtp_from',''),
    ('smtp_from_name','');

-- Example initial modules
INSERT INTO modules (name, file, icon, description, color, badge, badge_class, enabled) VALUES
    ('Çalışma Listesi','shift','fa-solid fa-calendar','Vardiyalarınızı ve mesai planınızı anında görün.','#3fa7ff','Güncel','badge-green',1),
    ('Eğitimler','training','fa-solid fa-graduation-cap','Kariyerinizi geliştirecek eğitimlere katılın.','#3fa7ff','8 Aktif','badge-blue',1),
    ('Sınavlar','exam','fa-solid fa-clipboard-check','Sınavlarınızı takip edin, başarınızı ölçün.','#ff5555','3 Bekleyen','badge-orange',1),
    ('Prosedürler','procedure','fa-solid fa-book','Güncel prosedürlere hızla erişin, bilgilenin.','#0dd4a3','12 Yeni','badge-blue',1);

-- Example landing pages
INSERT INTO site_pages (slug, title, content) VALUES
    ('home','Ana Sayfa','<h2>Hoş geldiniz</h2>'),
    ('hakkimizda','Hakkımızda','<p>Hakkımızda içerik</p>'),
    ('biz-kimiz','Biz Kimiz','<p>Biz Kimiz içerik</p>');
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
Additional roles such as `Normal Personel`, `Sorumlu Hemşire` and `Klinik Eğitim Hemşiresi` can also be assigned to users via the admin panel.

The "Şifremi Unuttum" feature uses SMTP to send reset links. Configure your mail server details under **Ayarlar** in the admin panel.
