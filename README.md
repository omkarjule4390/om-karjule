# AI Code Error Solver Website

An AI-powered full-stack web platform that automatically detects programming code errors, provides corrected code suggestions, and helps beginners learn coding.

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat&logo=bootstrap&logoColor=white)

## Features

- **User Authentication** — Register, login, logout, password encryption, sessions, forgot password
- **Dashboard** — Modern UI with sidebar, stats cards, recent activity
- **AI Error Detection** — Syntax & logic error detection with line highlighting
- **Code Correction** — Original vs corrected comparison with explanations
- **8 Languages** — Python, C, C++, Java, PHP, JavaScript, HTML, CSS
- **Code History** — Save, search, delete submissions with timestamps
- **Beginner Mode** — Simple explanations, tips, best practices
- **Admin Panel** — Manage users, activities, content moderation, feedback
- **Dark/Light Theme** — Glassmorphism futuristic UI with animations
- **Security** — PDO prepared statements, CSRF, XSS protection, password hashing

## Technology Stack

| Layer | Technology |
|-------|------------|
| Frontend | HTML5, CSS3, JavaScript, Bootstrap 5 |
| Backend | PHP 8+ |
| Database | MySQL |
| AI | OpenAI API / Google Gemini API (with demo fallback) |

## Project Structure

```
ai-code-error-solver-website/
├── admin/              # (reserved for extended admin modules)
├── api/
│   ├── analyze.php     # AI code analysis endpoint
│   ├── history.php     # Delete history API
│   └── theme.php       # Save theme preference
├── css/
│   └── style.css       # Main stylesheet (glassmorphism theme)
├── database/
│   └── schema.sql      # MySQL database schema
├── images/             # Static images
├── includes/
│   ├── config.php      # App configuration
│   ├── db.php          # Database connection
│   ├── auth.php        # Authentication
│   ├── functions.php   # Helpers & security
│   ├── ai_service.php  # OpenAI/Gemini integration
│   ├── header.php      # Navbar & layout start
│   ├── footer.php      # Footer & scripts
│   └── sidebar.php     # Dashboard sidebar
├── js/
│   ├── main.js         # Theme, loading, utilities
│   └── solver.js       # Error solver logic
├── index.php           # Landing page
├── login.php
├── register.php
├── forgot_password.php
├── dashboard.php
├── error_solver.php
├── history.php
├── profile.php
├── admin_panel.php
├── contact.php
└── logout.php
```

## Step-by-Step Setup Guide (XAMPP)

### Prerequisites

1. Download and install [XAMPP](https://www.apachefriends.org/) (PHP 8.0+ recommended)
2. Start **Apache** and **MySQL** from the XAMPP Control Panel

### Step 1: Copy Project Files

1. Copy the entire `ai-code-error-solver-website` folder to:
   ```
   C:\xampp\htdocs\ai-code-error-solver-website
   ```
2. Your project URL will be:
   ```
   http://localhost/ai-code-error-solver-website/
   ```

### Step 2: Import Database

**Option A — phpMyAdmin (Recommended for beginners)**

1. Open browser: `http://localhost/phpmyadmin`
2. Click **Import** tab
3. Choose file: `database/schema.sql`
4. Click **Go**
5. Database `ai_code_solver` will be created with all tables

**Option B — MySQL Command Line**

```bash
cd C:\xampp\mysql\bin
mysql -u root -p < "C:\xampp\htdocs\ai-code-error-solver-website\database\schema.sql"
```

### Step 3: Configure Database Connection

Edit `includes/config.php` if your MySQL credentials differ from XAMPP defaults:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ai_code_solver');
define('DB_USER', 'root');
define('DB_PASS', '');  // Default XAMPP has empty password
```

### Step 4: Enable PHP cURL (Required for AI API)

1. Open `C:\xampp\php\php.ini`
2. Find `;extension=curl` and remove the semicolon:
   ```
   extension=curl
   ```
3. Restart Apache in XAMPP

### Step 5: Configure AI API (Optional but Recommended)

**Without API key:** The app runs in **Demo Mode** with rule-based error detection.

**OpenAI:**

1. Get API key from [OpenAI Platform](https://platform.openai.com/api-keys)
2. Edit `includes/config.php`:
   ```php
   define('OPENAI_API_KEY', 'sk-your-key-here');
   define('AI_PROVIDER', 'openai');
   ```

**Google Gemini:**

```php
define('AI_PROVIDER', 'gemini');
define('GEMINI_API_KEY', 'your-gemini-api-key');
```

### Step 6: Access the Application

Open in browser:
```
http://localhost/ai-code-error-solver-website/
```

## Default Login Credentials

| Role | Username | Password |
|------|----------|----------|
| Admin | `admin` | `Admin@123` |
| Demo User | `demo` | `Demo@123` |

> Change these passwords after first login in production!

## XAMPP Configuration Notes

| Setting | Location | Value |
|---------|----------|-------|
| Document Root | `C:\xampp\htdocs\` | Place project here |
| Apache Port | XAMPP Control Panel | Default: 80 |
| MySQL Port | XAMPP Control Panel | Default: 3306 |
| PHP Version | `php.ini` | 8.0+ recommended |
| mod_rewrite | `httpd.conf` | Enable if using .htaccess |

### Enable mod_rewrite (if needed)

In `C:\xampp\apache\conf\httpd.conf`, ensure this line is uncommented:
```
LoadModule rewrite_module modules/mod_rewrite.so
```

And for your htdocs directory:
```
AllowOverride All
```

## Screenshots Description

When testing, capture these screens for documentation:

1. **Home Page** — Hero section with gradient background and feature cards
2. **Login/Register** — Glassmorphism auth cards
3. **Dashboard** — Sidebar, statistics cards, recent activity list
4. **Error Solver** — Code textarea, language dropdown, analysis results with error badges
5. **Code Comparison** — Original vs corrected code side-by-side
6. **History Page** — Searchable list with delete buttons
7. **Admin Panel** — User management table and activity log
8. **Dark/Light Mode** — Toggle theme button in navbar

## Testing Instructions

### 1. Authentication Tests

- [ ] Register a new account with valid data
- [ ] Try duplicate username/email (should show error)
- [ ] Login with correct credentials
- [ ] Login with wrong password (should fail)
- [ ] Logout and verify session cleared
- [ ] Test forgot password — copy demo reset link from page

### 2. Error Solver Tests

- [ ] Login as `demo` user
- [ ] Go to **Error Solver**
- [ ] Click **Load Sample** for Python
- [ ] Enable **Beginner Learning Mode**
- [ ] Click **Analyze & Fix Code**
- [ ] Verify: error badges, highlighted lines, corrected code, explanation typing effect
- [ ] Test **Copy** and **Download** buttons

### 3. History Tests

- [ ] Submit multiple code analyses
- [ ] Go to **History** — verify entries appear with dates
- [ ] Search by language or title keyword
- [ ] View detail of one entry
- [ ] Delete an entry and confirm removal

### 4. Profile Tests

- [ ] Update full name and email
- [ ] Switch theme (dark/light)
- [ ] Change password

### 5. Admin Tests

- [ ] Login as `admin`
- [ ] Open **Admin Panel**
- [ ] View user statistics
- [ ] Disable/enable a test user
- [ ] Delete inappropriate code content
- [ ] Mark feedback as resolved

### 6. Contact/Feedback

- [ ] Submit contact form (logged in and logged out)
- [ ] Verify entry in Admin Panel feedback section

### Sample Buggy Code for Testing

**Python:**
```python
def greet(name)
    print 'Hello ' + name
greet('World')
```

**JavaScript:**
```javascript
function add(a, b) {
  return a + b
}
console.log add(5, 3)
```

## Security Features Implemented

- **SQL Injection** — PDO prepared statements throughout
- **XSS** — `htmlspecialchars()` via `e()` helper on all output
- **CSRF** — Token validation on all forms and API requests
- **Passwords** — `password_hash()` / `password_verify()` with bcrypt
- **Sessions** — HttpOnly cookies, regeneration on login
- **Input Validation** — Server-side validation on all forms
- **Directory Protection** — `.htaccess` blocks `/includes` and `/database`

## Troubleshooting

| Problem | Solution |
|---------|----------|
| Database connection failed | Import `schema.sql`, check MySQL is running |
| Blank page / 500 error | Check `C:\xampp\php\logs\php_error_log` |
| AI not working | Enable cURL, add API key, or use Demo Mode |
| CSS not loading | Verify project is in `htdocs` folder |
| Session issues | Clear browser cookies, restart Apache |

## API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/analyze.php` | POST | Analyze code (JSON body) |
| `/api/history.php` | DELETE | Delete history item |
| `/api/theme.php` | POST | Save theme preference |

## License

This project is created for educational purposes. Feel free to modify and learn from it.

## Author Notes

Built for beginners learning full-stack web development with PHP, MySQL, and modern frontend practices. The codebase includes comments explaining key sections for easy understanding.
