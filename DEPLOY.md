# Deploy Blood Bank System to Live Server

Complete guide to deploy your Blood Bank Management System to a live server so you can access it via a URL.

---

## 🌟 Recommended: InfinityFree (100% FREE)

**Best for:** PHP + MySQL applications
**Cost:** FREE forever
**Features:** PHP, MySQL, cPanel, FTP, No Ads

---

## 📋 Step-by-Step Deployment Guide

### Step 1: Create InfinityFree Account

1. Go to: **https://infinityfree.net/**
2. Click **"Sign Up"**
3. Enter your email and create password
4. Verify your email

### Step 2: Create Hosting Account

1. Login to InfinityFree
2. Click **"Create Account"**
3. Choose a subdomain (e.g., `bloodbank.infinityfreeapp.com`)
   - Or use your own domain if you have one
4. Wait for account activation (2-5 minutes)

### Step 3: Access Control Panel (cPanel)

1. Click **"Control Panel"** for your account
2. You'll see cPanel dashboard

### Step 4: Upload Files

**Option A: File Manager (Easier)**

1. In cPanel, click **"File Manager"**
2. Navigate to `htdocs` folder
3. Delete default files (index.html, etc.)
4. Click **"Upload"** button
5. Upload ALL your project files
   - Or create a ZIP of your project and upload it
   - Then extract using "Extract" option

**Option B: FTP (Faster for large files)**

1. In cPanel, find **FTP credentials**
2. Download FileZilla: https://filezilla-project.org/
3. Connect using FTP credentials
4. Upload all files to `htdocs` folder

### Step 5: Create MySQL Database

1. In cPanel, click **"MySQL Databases"**
2. Create a new database:
   - Database name: `bloodbank_db` (or any name)
   - Click "Create Database"
3. Create a database user:
   - Username: `bloodbank_user`
   - Password: (create a strong password)
   - Click "Create User"
4. Add user to database:
   - Select your database and user
   - Grant ALL PRIVILEGES
   - Click "Add"

**Note down these details:**
- Database name: `epiz_XXXXX_bloodbank_db`
- Database user: `epiz_XXXXX_bloodbank_user`
- Database password: (your password)
- Database host: `sql300.infinityfree.com` (or similar)

### Step 6: Import Database

1. In cPanel, click **"phpMyAdmin"**
2. Select your database from left sidebar
3. Click **"Import"** tab
4. Choose file: `sql/blood_bank.sql`
5. Click **"Go"**
6. Wait for import to complete

### Step 7: Update Database Configuration

1. In File Manager, open `config/database.php`
2. Update with your database credentials:

```php
define('DB_HOST', 'sql300.infinityfree.com'); // Your DB host
define('DB_USER', 'epiz_XXXXX_bloodbank_user'); // Your DB user
define('DB_PASS', 'your_password_here'); // Your DB password
define('DB_NAME', 'epiz_XXXXX_bloodbank_db'); // Your DB name
```

3. Save the file

### Step 8: Test Your Website

1. Visit your URL: `http://yoursite.infinityfreeapp.com`
2. You should see your Blood Bank homepage!
3. Try logging in with demo credentials:
   - Admin: `admin@bloodbank.com` / `admin123`

---

## ✅ Your Site is Live!

**Your URL:** `http://yoursite.infinityfreeapp.com`

Share this link with anyone to access your Blood Bank Management System!

---

## 🔧 Troubleshooting

### Issue: "Database connection failed"
- Check database credentials in `config/database.php`
- Ensure database host is correct
- Verify user has privileges

### Issue: "Page not found"
- Check if files are in `htdocs` folder (not in a subfolder)
- Clear browser cache

### Issue: "500 Internal Server Error"
- Check file permissions (should be 644 for files, 755 for folders)
- Check PHP error logs in cPanel

---

## 🚀 Alternative Hosting Options

### 1. **000webhost** (FREE)
- Similar to InfinityFree
- https://www.000webhost.com/
- Same process as above

### 2. **Hostinger** (Paid, $2.99/month)
- Better performance
- 24/7 support
- https://www.hostinger.com/

### 3. **Railway** (FREE $5 credit/month)
- Deploy from GitHub
- https://railway.app/
- More complex setup

### 4. **Heroku** (FREE tier)
- Deploy from GitHub
- Requires ClearDB addon for MySQL
- https://www.heroku.com/

---

## 📱 Custom Domain (Optional)

If you want a custom domain like `bloodbank.com`:

1. Buy domain from:
   - Namecheap: https://www.namecheap.com/
   - GoDaddy: https://www.godaddy.com/
   - Hostinger: https://www.hostinger.com/

2. In InfinityFree:
   - Go to "Addon Domains"
   - Add your custom domain
   - Update nameservers in your domain registrar

---

## 🔒 Security Tips for Live Server

1. **Change default passwords:**
   - Update all demo user passwords
   - Use strong passwords

2. **Update database credentials:**
   - Don't use default credentials

3. **Enable HTTPS:**
   - InfinityFree provides free SSL
   - Enable in cPanel

4. **Regular backups:**
   - Download database backups weekly
   - Download file backups monthly

---

## 📊 Managing Your Live Site

### Update Code
1. Make changes locally
2. Upload changed files via FTP/File Manager
3. Test on live site

### Update Database
1. Export from phpMyAdmin
2. Make changes locally
3. Import back to phpMyAdmin

### Monitor Usage
- Check cPanel for:
  - Bandwidth usage
  - Database size
  - File storage

---

## 🎯 Quick Checklist

- [ ] Create InfinityFree account
- [ ] Create hosting account
- [ ] Upload all files to `htdocs`
- [ ] Create MySQL database
- [ ] Import `sql/blood_bank.sql`
- [ ] Update `config/database.php`
- [ ] Test login with demo credentials
- [ ] Change default passwords
- [ ] Share your live URL!

---

## 📞 Need Help?

- InfinityFree Support: https://forum.infinityfree.net/
- InfinityFree Docs: https://infinityfree.net/support/

---

**Your Blood Bank System will be live and accessible from anywhere! 🌍**
