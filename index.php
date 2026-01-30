<?php
/**
 * Home Page
 * Blood Bank Management System
 */

require_once 'config/session.php';
require_once 'config/database.php';
require_once 'config/functions.php';

// Handle feedback submission
$feedbackSuccess = false;
$feedbackError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $name = sanitize($_POST['feedback_name']);
    $email = sanitize($_POST['feedback_email']);
    $subject = sanitize($_POST['feedback_subject']);
    $message = sanitize($_POST['feedback_message']);

    if (empty($name) || empty($email) || empty($message)) {
        $feedbackError = 'Please fill in all required fields.';
    } else if (!isValidEmail($email)) {
        $feedbackError = 'Please enter a valid email address.';
    } else {
        $insertQuery = "INSERT INTO feedback (name, email, subject, message) 
                        VALUES ('$name', '$email', '$subject', '$message')";
        if (mysqli_query($conn, $insertQuery)) {
            $feedbackSuccess = true;
        } else {
            $feedbackError = 'Failed to submit feedback. Please try again.';
        }
    }
}

// Get blood stock for display
$bloodStock = getBloodStockSummary();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Bank Management System - Save Lives, Donate Blood</title>
    <meta name="description"
        content="Blood Bank Management System - Register as a donor or patient. Save lives by donating blood.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <!-- Header -->
    <header class="public-header">
        <div class="container">
            <a href="index.php" class="logo">
                <i class="fas fa-tint"></i>
                <span>Blood Bank</span>
            </a>

            <nav class="public-nav">
                <a href="#home">Home</a>
                <a href="#about">About</a>
                <a href="#blood-stock">Blood Stock</a>
                <a href="#contact">Contact</a>
                <?php if (isLoggedIn()): ?>
                    <a href="<?php echo getUserRole(); ?>/dashboard.php" class="btn btn-primary">Dashboard</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline">Login</a>
                    <a href="register.php" class="btn btn-primary">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1>Donate Blood, Save Lives</h1>
                <p>Every drop counts. Join our community of blood donors and help save millions of lives. Your single
                    donation can save up to three lives.</p>
                <div class="hero-buttons">
                    <a href="register.php" class="btn btn-light btn-lg">
                        <i class="fas fa-hand-holding-heart"></i> Become a Donor
                    </a>
                    <a href="register.php?role=patient" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-search"></i> Request Blood
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="about" class="features-section">
        <div class="container">
            <div class="section-title">
                <h2>Why Choose Us?</h2>
                <p>We provide a complete blood bank management solution to connect donors with patients in need.</p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-tint"></i>
                    <h3>Easy Blood Donation</h3>
                    <p>Register as a donor and schedule your blood donation at your convenience.</p>
                </div>

                <div class="feature-card">
                    <i class="fas fa-search"></i>
                    <h3>Quick Blood Search</h3>
                    <p>Find available blood units in real-time and request blood when you need it.</p>
                </div>

                <div class="feature-card">
                    <i class="fas fa-hospital"></i>
                    <h3>Multiple Blood Banks</h3>
                    <p>Connected with multiple blood banks for better availability and faster service.</p>
                </div>

                <div class="feature-card">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Safe & Secure</h3>
                    <p>All blood units are tested and verified to ensure safety and quality.</p>
                </div>

                <div class="feature-card">
                    <i class="fas fa-clock"></i>
                    <h3>24/7 Availability</h3>
                    <p>Access our system anytime to check blood availability or make requests.</p>
                </div>

                <div class="feature-card">
                    <i class="fas fa-chart-line"></i>
                    <h3>Real-time Tracking</h3>
                    <p>Track your donation history and request status in real-time.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Blood Stock Section -->
    <section id="blood-stock" style="padding: 80px 0;">
        <div class="container">
            <div class="section-title">
                <h2>Current Blood Availability</h2>
                <p>Check the current blood stock availability across all blood banks.</p>
            </div>

            <div class="blood-group-grid">
                <?php foreach ($bloodStock as $stock): ?>
                    <div class="blood-card">
                        <div class="blood-type"><?php echo $stock['group_name']; ?></div>
                        <div class="blood-quantity"><?php echo number_format($stock['total_quantity']); ?> ml</div>
                        <div class="blood-label">Available</div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section"
        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 60px 0; color: white;">
        <div class="container">
            <div class="stats-grid"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px; text-align: center;">
                <div class="stat-item">
                    <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.9;"></i>
                    <h3 style="font-size: 2.5rem; margin: 10px 0; font-weight: bold;">5000+</h3>
                    <p style="font-size: 1.1rem; opacity: 0.9;">Registered Donors</p>
                </div>
                <div class="stat-item">
                    <i class="fas fa-tint" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.9;"></i>
                    <h3 style="font-size: 2.5rem; margin: 10px 0; font-weight: bold;">12000+</h3>
                    <p style="font-size: 1.1rem; opacity: 0.9;">Lives Saved</p>
                </div>
                <div class="stat-item">
                    <i class="fas fa-hospital" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.9;"></i>
                    <h3 style="font-size: 2.5rem; margin: 10px 0; font-weight: bold;">50+</h3>
                    <p style="font-size: 1.1rem; opacity: 0.9;">Blood Banks</p>
                </div>
                <div class="stat-item">
                    <i class="fas fa-heartbeat" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.9;"></i>
                    <h3 style="font-size: 2.5rem; margin: 10px 0; font-weight: bold;">24/7</h3>
                    <p style="font-size: 1.1rem; opacity: 0.9;">Emergency Support</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Donation Process Section -->
    <section class="process-section" style="padding: 80px 0; background: #f8f9fa;">
        <div class="container">
            <div class="section-title">
                <h2>How Blood Donation Works</h2>
                <p>Simple steps to become a life-saving hero</p>
            </div>

            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; margin-top: 40px;">
                <div
                    style="text-align: center; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <div
                        style="width: 70px; height: 70px; background: #dc3545; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 1.8rem; font-weight: bold;">
                        1</div>
                    <h3 style="color: #dc3545; margin-bottom: 15px;">Register</h3>
                    <p style="color: #666;">Create your account and complete your donor profile with basic information.
                    </p>
                </div>

                <div
                    style="text-align: center; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <div
                        style="width: 70px; height: 70px; background: #dc3545; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 1.8rem; font-weight: bold;">
                        2</div>
                    <h3 style="color: #dc3545; margin-bottom: 15px;">Health Check</h3>
                    <p style="color: #666;">Quick health screening to ensure you're eligible to donate blood safely.</p>
                </div>

                <div
                    style="text-align: center; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <div
                        style="width: 70px; height: 70px; background: #dc3545; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 1.8rem; font-weight: bold;">
                        3</div>
                    <h3 style="color: #dc3545; margin-bottom: 15px;">Donate</h3>
                    <p style="color: #666;">The donation process takes about 10-15 minutes. Relax and save lives!</p>
                </div>

                <div
                    style="text-align: center; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <div
                        style="width: 70px; height: 70px; background: #dc3545; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 1.8rem; font-weight: bold;">
                        4</div>
                    <h3 style="color: #dc3545; margin-bottom: 15px;">Refresh</h3>
                    <p style="color: #666;">Enjoy refreshments and rest for a few minutes before you leave.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Eligibility Section -->
    <section class="eligibility-section" style="padding: 80px 0;">
        <div class="container">
            <div class="section-title">
                <h2>Who Can Donate Blood?</h2>
                <p>Check if you're eligible to become a blood donor</p>
            </div>

            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px; margin-top: 40px;">
                <div>
                    <h3 style="color: #28a745; margin-bottom: 20px;"><i class="fas fa-check-circle"></i> You Can Donate
                        If:</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li style="padding: 10px 0; border-bottom: 1px solid #eee;"><i class="fas fa-check"
                                style="color: #28a745; margin-right: 10px;"></i> Age between 18-65 years</li>
                        <li style="padding: 10px 0; border-bottom: 1px solid #eee;"><i class="fas fa-check"
                                style="color: #28a745; margin-right: 10px;"></i> Weight 50 kg or more</li>
                        <li style="padding: 10px 0; border-bottom: 1px solid #eee;"><i class="fas fa-check"
                                style="color: #28a745; margin-right: 10px;"></i> Hemoglobin level is normal</li>
                        <li style="padding: 10px 0; border-bottom: 1px solid #eee;"><i class="fas fa-check"
                                style="color: #28a745; margin-right: 10px;"></i> In good health condition</li>
                        <li style="padding: 10px 0; border-bottom: 1px solid #eee;"><i class="fas fa-check"
                                style="color: #28a745; margin-right: 10px;"></i> No recent illness or surgery</li>
                        <li style="padding: 10px 0;"><i class="fas fa-check"
                                style="color: #28a745; margin-right: 10px;"></i> At least 3 months since last donation
                        </li>
                    </ul>
                </div>

                <div>
                    <h3 style="color: #dc3545; margin-bottom: 20px;"><i class="fas fa-times-circle"></i> You Cannot
                        Donate If:</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li style="padding: 10px 0; border-bottom: 1px solid #eee;"><i class="fas fa-times"
                                style="color: #dc3545; margin-right: 10px;"></i> Currently pregnant or breastfeeding
                        </li>
                        <li style="padding: 10px 0; border-bottom: 1px solid #eee;"><i class="fas fa-times"
                                style="color: #dc3545; margin-right: 10px;"></i> Have HIV, Hepatitis B or C</li>
                        <li style="padding: 10px 0; border-bottom: 1px solid #eee;"><i class="fas fa-times"
                                style="color: #dc3545; margin-right: 10px;"></i> Recent tattoo or piercing (within 6
                            months)</li>
                        <li style="padding: 10px 0; border-bottom: 1px solid #eee;"><i class="fas fa-times"
                                style="color: #dc3545; margin-right: 10px;"></i> Taking certain medications</li>
                        <li style="padding: 10px 0; border-bottom: 1px solid #eee;"><i class="fas fa-times"
                                style="color: #dc3545; margin-right: 10px;"></i> Had major surgery recently</li>
                        <li style="padding: 10px 0;"><i class="fas fa-times"
                                style="color: #dc3545; margin-right: 10px;"></i> Chronic health conditions</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="benefits-section" style="padding: 80px 0; background: #f8f9fa;">
        <div class="container">
            <div class="section-title">
                <h2>Benefits of Donating Blood</h2>
                <p>Giving blood is good for you too!</p>
            </div>

            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px; margin-top: 40px;">
                <div
                    style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <i class="fas fa-heart" style="font-size: 2.5rem; color: #dc3545; margin-bottom: 15px;"></i>
                    <h3 style="margin-bottom: 15px;">Reduces Heart Disease Risk</h3>
                    <p style="color: #666;">Regular blood donation helps reduce iron levels, lowering the risk of heart
                        attacks.</p>
                </div>

                <div
                    style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <i class="fas fa-weight" style="font-size: 2.5rem; color: #dc3545; margin-bottom: 15px;"></i>
                    <h3 style="margin-bottom: 15px;">Burns Calories</h3>
                    <p style="color: #666;">Donating blood burns approximately 650 calories per donation.</p>
                </div>

                <div
                    style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <i class="fas fa-sync-alt" style="font-size: 2.5rem; color: #dc3545; margin-bottom: 15px;"></i>
                    <h3 style="margin-bottom: 15px;">Stimulates Blood Cell Production</h3>
                    <p style="color: #666;">Your body replenishes the donated blood, creating fresh new blood cells.</p>
                </div>

                <div
                    style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <i class="fas fa-stethoscope" style="font-size: 2.5rem; color: #dc3545; margin-bottom: 15px;"></i>
                    <h3 style="margin-bottom: 15px;">Free Health Checkup</h3>
                    <p style="color: #666;">Get a mini health screening including blood pressure, hemoglobin, and
                        disease testing.</p>
                </div>

                <div
                    style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <i class="fas fa-smile" style="font-size: 2.5rem; color: #dc3545; margin-bottom: 15px;"></i>
                    <h3 style="margin-bottom: 15px;">Emotional Well-being</h3>
                    <p style="color: #666;">The satisfaction of saving lives boosts mental health and happiness.</p>
                </div>

                <div
                    style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <i class="fas fa-shield-alt" style="font-size: 2.5rem; color: #dc3545; margin-bottom: 15px;"></i>
                    <h3 style="margin-bottom: 15px;">Reduces Cancer Risk</h3>
                    <p style="color: #666;">Regular donation may help reduce the risk of certain types of cancer.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Impact Stories Section -->
    <section class="impact-section"
        style="padding: 80px 0; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
        <div class="container">
            <div class="section-title">
                <h2 style="color: white;">Real Impact, Real Lives</h2>
                <p style="color: rgba(255,255,255,0.9);">See how blood donation makes a difference</p>
            </div>

            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-top: 40px;">
                <div
                    style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); padding: 30px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.2);">
                    <i class="fas fa-quote-left" style="font-size: 2rem; opacity: 0.5; margin-bottom: 15px;"></i>
                    <p style="font-size: 1.1rem; line-height: 1.6; margin-bottom: 20px;">"Thanks to blood donors, my
                        daughter survived a critical surgery. We are forever grateful to those who donate blood."</p>
                    <p style="font-weight: bold; opacity: 0.9;">- Rajesh Kumar, Father</p>
                </div>

                <div
                    style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); padding: 30px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.2);">
                    <i class="fas fa-quote-left" style="font-size: 2rem; opacity: 0.5; margin-bottom: 15px;"></i>
                    <p style="font-size: 1.1rem; line-height: 1.6; margin-bottom: 20px;">"I've been donating blood for 5
                        years. Knowing that I'm helping save lives gives me immense satisfaction."</p>
                    <p style="font-weight: bold; opacity: 0.9;">- Priya Sharma, Regular Donor</p>
                </div>

                <div
                    style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); padding: 30px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.2);">
                    <i class="fas fa-quote-left" style="font-size: 2rem; opacity: 0.5; margin-bottom: 15px;"></i>
                    <p style="font-size: 1.1rem; line-height: 1.6; margin-bottom: 20px;">"During my accident, I needed 4
                        units of blood. The quick availability saved my life. Thank you to all donors!"</p>
                    <p style="font-weight: bold; opacity: 0.9;">- Amit Patel, Survivor</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section" style="padding: 80px 0;">
        <div class="container">
            <div class="section-title">
                <h2>Frequently Asked Questions</h2>
                <p>Everything you need to know about blood donation</p>
            </div>

            <div style="max-width: 800px; margin: 40px auto 0;">
                <div
                    style="background: white; border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 15px; overflow: hidden;">
                    <div style="padding: 20px; background: #f8f9fa; border-bottom: 1px solid #e0e0e0;">
                        <h3 style="margin: 0; color: #dc3545;"><i class="fas fa-question-circle"></i> How long does
                            blood donation take?</h3>
                    </div>
                    <div style="padding: 20px;">
                        <p style="margin: 0; color: #666;">The entire process takes about 45 minutes to 1 hour,
                            including registration, health screening, and refreshments. The actual blood donation takes
                            only 10-15 minutes.</p>
                    </div>
                </div>

                <div
                    style="background: white; border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 15px; overflow: hidden;">
                    <div style="padding: 20px; background: #f8f9fa; border-bottom: 1px solid #e0e0e0;">
                        <h3 style="margin: 0; color: #dc3545;"><i class="fas fa-question-circle"></i> Is blood donation
                            safe?</h3>
                    </div>
                    <div style="padding: 20px;">
                        <p style="margin: 0; color: #666;">Yes, absolutely! All equipment used is sterile and
                            disposable. There is no risk of contracting any disease through blood donation.</p>
                    </div>
                </div>

                <div
                    style="background: white; border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 15px; overflow: hidden;">
                    <div style="padding: 20px; background: #f8f9fa; border-bottom: 1px solid #e0e0e0;">
                        <h3 style="margin: 0; color: #dc3545;"><i class="fas fa-question-circle"></i> How often can I
                            donate blood?</h3>
                    </div>
                    <div style="padding: 20px;">
                        <p style="margin: 0; color: #666;">You can donate whole blood every 3 months (90 days). Your
                            body completely replenishes the donated blood within this time.</p>
                    </div>
                </div>

                <div
                    style="background: white; border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 15px; overflow: hidden;">
                    <div style="padding: 20px; background: #f8f9fa; border-bottom: 1px solid #e0e0e0;">
                        <h3 style="margin: 0; color: #dc3545;"><i class="fas fa-question-circle"></i> Will I feel weak
                            after donating?</h3>
                    </div>
                    <div style="padding: 20px;">
                        <p style="margin: 0; color: #666;">Most donors feel fine after donating. You may feel slightly
                            lightheaded, but this passes quickly. We provide refreshments to help you recover. Avoid
                            strenuous activity for 24 hours.</p>
                    </div>
                </div>

                <div
                    style="background: white; border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 15px; overflow: hidden;">
                    <div style="padding: 20px; background: #f8f9fa; border-bottom: 1px solid #e0e0e0;">
                        <h3 style="margin: 0; color: #dc3545;"><i class="fas fa-question-circle"></i> What should I do
                            before donating?</h3>
                    </div>
                    <div style="padding: 20px;">
                        <p style="margin: 0; color: #666;">Eat a healthy meal, drink plenty of water, get good sleep,
                            and avoid fatty foods. Bring a valid ID and your donor card if you have one.</p>
                    </div>
                </div>

                <div
                    style="background: white; border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 15px; overflow: hidden;">
                    <div style="padding: 20px; background: #f8f9fa; border-bottom: 1px solid #e0e0e0;">
                        <h3 style="margin: 0; color: #dc3545;"><i class="fas fa-question-circle"></i> Can I donate if I
                            have a tattoo?</h3>
                    </div>
                    <div style="padding: 20px;">
                        <p style="margin: 0; color: #666;">You must wait 6 months after getting a tattoo or piercing
                            before donating blood to ensure there's no risk of infection.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Emergency Contact Section -->
    <section class="emergency-section" style="padding: 60px 0; background: #dc3545; color: white;">
        <div class="container" style="text-align: center;">
            <i class="fas fa-phone-volume" style="font-size: 3rem; margin-bottom: 20px;"></i>
            <h2 style="margin-bottom: 15px;">Need Blood Urgently?</h2>
            <p style="font-size: 1.2rem; margin-bottom: 25px; opacity: 0.9;">Our 24/7 emergency helpline is always
                available</p>
            <div style="font-size: 2rem; font-weight: bold; margin-bottom: 20px;">
                <i class="fas fa-phone"></i> 1800-123-4567
            </div>
            <p style="opacity: 0.9;">Or email us at: <a href="mailto:emergency@bloodbank.com"
                    style="color: white; text-decoration: underline;">emergency@bloodbank.com</a></p>
        </div>
    </section>


    <!-- Contact Section -->
    <section id="contact" class="contact-section">
        <div class="container">
            <div class="section-title">
                <h2>Contact Us</h2>
                <p>Have questions? Send us a message and we'll get back to you soon.</p>
            </div>

            <div class="contact-form">
                <?php if ($feedbackSuccess): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span>Thank you for contacting us! We'll get back to you soon.</span>
                    </div>
                <?php endif; ?>

                <?php if ($feedbackError): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo $feedbackError; ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="#contact" data-validate>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="feedback_name" class="required">Your Name</label>
                            <input type="text" id="feedback_name" name="feedback_name" class="form-control"
                                placeholder="Enter your name" required>
                        </div>

                        <div class="form-group">
                            <label for="feedback_email" class="required">Email Address</label>
                            <input type="email" id="feedback_email" name="feedback_email" class="form-control"
                                placeholder="Enter your email" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="feedback_subject">Subject</label>
                        <input type="text" id="feedback_subject" name="feedback_subject" class="form-control"
                            placeholder="Enter subject">
                    </div>

                    <div class="form-group">
                        <label for="feedback_message" class="required">Message</label>
                        <textarea id="feedback_message" name="feedback_message" class="form-control"
                            placeholder="Enter your message" rows="4" required></textarea>
                    </div>

                    <div class="form-group">
                        <button type="submit" name="submit_feedback" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="public-footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Blood Bank Management System. All rights reserved.</p>
            <p style="margin-top: 10px; font-size: 0.9rem;">
                <a href="login.php" style="color: rgba(255,255,255,0.7);">Admin Login</a>
            </p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script>
        // Smooth scroll for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>

</html>