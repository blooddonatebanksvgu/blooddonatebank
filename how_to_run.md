# How to Run Blood Bank Management System

## Technology Stack

| Layer        | Technology                      |
| ------------ | ------------------------------- |
| **Frontend** | HTML5, CSS3, Vanilla JavaScript |
| **Backend**  | PHP (No Framework)              |
| **Database** | MySQL                           |
| **Server**   | XAMPP (Apache + MySQL)          |

---

## Prerequisites

Before running this project, you need to install **XAMPP** on your computer.

### Download XAMPP

- Go to: https://www.apachefriends.org/download.html
- Download XAMPP for Windows
- Install with default settings

---

## Step-by-Step Installation Guide

### Step 1: Copy Project to XAMPP

1. Open File Explorer
2. Navigate to your XAMPP installation folder:
   ```
   C:\xampp\htdocs\
   ```
3. Copy the entire `blood_bank` folder here
4. Final path should be:
   ```
   C:\xampp\htdocs\blood_bank
   ```

### Step 2: Start XAMPP Services

1. Open **XAMPP Control Panel** (search "XAMPP" in Start menu)
2. Click **Start** button next to **Apache**
3. Click **Start** button next to **MySQL**
4. Both should turn **green** indicating they're running

![XAMPP Running](https://i.imgur.com/example.png)

> **Note:** If Apache doesn't start, port 80 might be in use. You can change the port in XAMPP settings.

### Step 3: Create Database

1. Open your web browser
2. Go to: **http://localhost/phpmyadmin**
3. Click on **"New"** in the left sidebar
4. Enter database name: `blood_bank_db`
5. Click **"Create"**

### Step 4: Import Database Tables

1. In phpMyAdmin, click on `blood_bank_db` database (left sidebar)
2. Click on **"Import"** tab (top menu)
3. Click **"Choose File"** button
4. Navigate to: `C:\xampp\htdocs\blood_bank\sql\`
5. Select `blood_bank.sql`
6. Scroll down and click **"Go"** button
7. Wait for success message: "Import has been successfully finished"

### Step 5: Open the Website

1. Open your web browser (Chrome, Firefox, Edge)
2. Type in address bar:
   ```
   http://localhost/blood_bank
   ```
3. Press Enter
4. You should see the Blood Bank homepage!

---

## Login Credentials

### Login
Admin: admin@bloodbank.com / admin123
Donor: rahul@gmail.com / admin123
Donor: priya@gmail.com / admin123
Patient: amit@gmail.com / admin123
Patient: sneha@gmail.com / admin123
Blood Bank: citybloodbank@gmail.com / admin123
Blood Bank: centralbb@gmail.com / admin123
---

## Quick Test Guide

### Test Admin Panel

1. Go to: http://localhost/blood_bank/login.php
2. Login with admin credentials
3. You'll see the Admin Dashboard with:
   - Blood stock statistics
   - Manage donors, patients, blood banks
   - Approve/reject donations and requests

### Test Donor Registration

1. Go to: http://localhost/blood_bank/register.php
2. Select "Donor" role
3. Fill in the registration form
4. Submit and login with your new account

### Test Patient Registration

1. Go to: http://localhost/blood_bank/register.php
2. Select "Patient" role
3. Fill in the registration form
4. Submit and login to request blood

---

## URL Reference

| Page                 | URL                                                 |
| -------------------- | --------------------------------------------------- |
| Homepage             | http://localhost/blood_bank                         |
| Login                | http://localhost/blood_bank/login.php               |
| Register             | http://localhost/blood_bank/register.php            |
| Admin Dashboard      | http://localhost/blood_bank/admin/dashboard.php     |
| Blood Bank Dashboard | http://localhost/blood_bank/bloodbank/dashboard.php |
| Donor Dashboard      | http://localhost/blood_bank/donor/dashboard.php     |
| Patient Dashboard    | http://localhost/blood_bank/patient/dashboard.php   |

---

## Troubleshooting

### Problem: "Database connection failed"

**Solution:**

- Make sure MySQL is running in XAMPP
- Check if database `blood_bank_db` exists in phpMyAdmin
- Verify database credentials in `config/database.php`

### Problem: Apache won't start

**Solution:**

- Port 80 might be used by Skype or other apps
- Open XAMPP > Config > Apache (httpd.conf)
- Change `Listen 80` to `Listen 8080`
- Access site at: http://localhost:8080/blood_bank

### Problem: Page shows PHP code instead of running

**Solution:**

- Apache is not running, start it from XAMPP
- Make sure you're accessing via http://localhost, not file://

### Problem: Blank white page

**Solution:**

- Check if all files are copied correctly
- Look for PHP errors in: `C:\xampp\apache\logs\error.log`

---

## Database Configuration

If you need to change database settings, edit this file:

```
blood_bank/config/database.php
```

Default settings:

```php
DB_HOST = 'localhost'
DB_USER = 'root'
DB_PASS = ''  (empty password)
DB_NAME = 'blood_bank_db'
```

---

## File Structure Overview

```
C:\xampp\htdocs\blood_bank\
│
├── index.php          ← Homepage
├── login.php          ← Login page
├── register.php       ← Registration page
├── logout.php         ← Logout handler
│
├── admin\             ← Admin panel pages
├── bloodbank\         ← Blood bank pages
├── donor\             ← Donor pages
├── patient\           ← Patient pages
│
├── config\            ← Database & session config
├── includes\          ← Shared header, footer, sidebars
├── assets\css\        ← Stylesheets
├── assets\js\         ← JavaScript files
│
└── sql\
    └── blood_bank.sql ← Database schema (import this)
```

---

## Need Help?

If you face any issues:

1. Make sure XAMPP Apache and MySQL are running (green status)
2. Make sure database is imported correctly
3. Clear browser cache and try again
4. Check the error logs in XAMPP

---

**Happy Testing! 🩸**

