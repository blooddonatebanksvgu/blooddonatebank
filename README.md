# Blood Bank Management System

A comprehensive web-based Blood Bank Management System built with PHP and MySQL. This system facilitates blood donation management, blood stock tracking, and connects donors with patients in need.

![Blood Bank System](https://img.shields.io/badge/PHP-7.4+-blue.svg)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

## 🩸 Features

### For Donors
- Easy registration and profile management
- Schedule blood donations
- Track donation history
- View total donations and impact

### For Patients
- Request blood based on blood group and urgency
- Track request status (pending/approved/rejected)
- View available blood stock in real-time

### For Blood Banks
- Manage blood inventory across multiple blood groups
- Approve/reject donation and blood requests
- Track blood stock levels with automatic updates
- Generate reports and analytics

### For Administrators
- Complete system oversight and management
- Manage users (donors, patients, blood banks)
- View system-wide statistics and reports
- Manage feedback and inquiries

## 📊 Statistics

- **5000+** Registered Donors
- **12000+** Lives Saved
- **50+** Connected Blood Banks
- **24/7** Emergency Support

## 🚀 Quick Start

### Prerequisites

- XAMPP (or any PHP 7.4+ and MySQL 5.7+ environment)
- Web browser
- Git (for cloning)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/blooddonatebanksxvgu/blooddonatebank.git
   cd blooddonatebank
   ```

2. **Move to XAMPP directory**
   ```bash
   # Windows
   copy -r * C:\xampp\htdocs\blood_bank\

   # Linux/Mac
   cp -r * /opt/lampp/htdocs/blood_bank/
   ```

3. **Start XAMPP Services**
   - Start Apache
   - Start MySQL

4. **Import Database**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create a new database or use existing
   - Import `sql/blood_bank.sql`

5. **Access the Application**
   ```
   http://localhost/blood_bank/
   ```

## 🔑 Demo Credentials

All demo accounts use password: **admin123**

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@bloodbank.com | admin123 |
| Donor | rahul@gmail.com | admin123 |
| Donor | priya@gmail.com | admin123 |
| Patient | amit@gmail.com | admin123 |
| Patient | sneha@gmail.com | admin123 |
| Blood Bank | citybloodbank@gmail.com | admin123 |
| Blood Bank | centralbb@gmail.com | admin123 |

## 📁 Project Structure

```
blood_bank/
├── admin/              # Admin dashboard and management
├── bloodbank/          # Blood bank dashboard
├── donor/              # Donor dashboard
├── patient/            # Patient dashboard
├── assets/             # CSS, JS, and images
│   ├── css/
│   └── js/
├── config/             # Configuration files
│   ├── database.php
│   ├── session.php
│   └── functions.php
├── sql/                # Database schema
│   └── blood_bank.sql
├── index.php           # Home page
├── login.php           # Login page
├── register.php        # Registration page
└── how_to_run.md       # Setup instructions
```

## 🛠️ Technologies Used

- **Frontend**: HTML5, CSS3, JavaScript
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Icons**: Font Awesome 6.4.0
- **Server**: Apache (XAMPP)

## 💡 Key Functionalities

### Blood Donation Management
- Donors can register and schedule donations
- Blood banks can approve/reject donations
- Automatic blood stock updates after approval
- Donation history tracking

### Blood Request Management
- Patients can request blood with urgency levels
- Real-time blood availability checking
- Request approval workflow
- Emergency request handling

### Blood Stock Management
- Real-time inventory tracking across 8 blood groups (A+, A-, B+, B-, O+, O-, AB+, AB-)
- Automatic stock updates on donations and requests
- Low stock alerts
- Multi-blood bank support

### User Management
- Role-based access control (Admin, Blood Bank, Donor, Patient)
- User status management (Active, Inactive, Pending)
- Profile management
- Secure authentication

## 📱 Responsive Design

The system is fully responsive and works seamlessly on:
- Desktop computers
- Tablets
- Mobile devices

## 🔒 Security Features

- Password hashing using bcrypt
- SQL injection prevention
- Session management
- Input validation and sanitization
- Role-based access control

## 📈 Future Enhancements

- [ ] Email notifications for requests and approvals
- [ ] SMS alerts for urgent blood requirements
- [ ] Mobile app (Android/iOS)
- [ ] Blood donation camps management
- [ ] Donor rewards and gamification
- [ ] Advanced analytics and reporting
- [ ] Multi-language support

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 👨‍💻 Author

**Bhavik**

## 🙏 Acknowledgments

- Font Awesome for icons
- All blood donors who save lives every day
- The open-source community

## 📞 Support

For support, email: emergency@bloodbank.com or call our 24/7 helpline: 1800-123-4567

---

**Made with ❤️ to save lives**
