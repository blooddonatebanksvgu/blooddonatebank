<?php
/**
 * Blood Bank Sidebar Include
 * Blood Bank Management System
 */

$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Get blood bank info
$bloodBankInfo = getBloodBankByUserId(getUserId());
?>
<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="/blood_bank/bloodbank/dashboard.php" class="logo">
            <i class="fas fa-tint"></i>
            <span class="logo-text">Blood Bank</span>
        </a>
    </div>
    
    <nav class="sidebar-menu">
        <div class="menu-item">
            <a href="/blood_bank/bloodbank/dashboard.php" class="menu-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        </div>
        
        <div class="menu-title">Operations</div>
        
        <div class="menu-item">
            <a href="/blood_bank/bloodbank/blood_requests.php" class="menu-link <?php echo $currentPage === 'blood_requests' ? 'active' : ''; ?>">
                <i class="fas fa-notes-medical"></i>
                <span>Blood Requests</span>
            </a>
        </div>
        
        <div class="menu-item">
            <a href="/blood_bank/bloodbank/donations.php" class="menu-link <?php echo $currentPage === 'donations' ? 'active' : ''; ?>">
                <i class="fas fa-syringe"></i>
                <span>Donations</span>
            </a>
        </div>
        
        <div class="menu-item">
            <a href="/blood_bank/bloodbank/blood_stock.php" class="menu-link <?php echo $currentPage === 'blood_stock' ? 'active' : ''; ?>">
                <i class="fas fa-boxes"></i>
                <span>Blood Stock</span>
            </a>
        </div>
        
        <div class="menu-divider"></div>
        <div class="menu-title">View</div>
        
        <div class="menu-item">
            <a href="/blood_bank/bloodbank/donors.php" class="menu-link <?php echo $currentPage === 'donors' ? 'active' : ''; ?>">
                <i class="fas fa-hand-holding-heart"></i>
                <span>Donors</span>
            </a>
        </div>
        
        <div class="menu-item">
            <a href="/blood_bank/bloodbank/request_history.php" class="menu-link <?php echo $currentPage === 'request_history' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i>
                <span>Request History</span>
            </a>
        </div>
        
        <div class="menu-divider"></div>
        
        <div class="menu-item">
            <a href="/blood_bank/bloodbank/profile.php" class="menu-link <?php echo $currentPage === 'profile' ? 'active' : ''; ?>">
                <i class="fas fa-user-cog"></i>
                <span>Profile</span>
            </a>
        </div>
        
        <div class="menu-item">
            <a href="/blood_bank/logout.php" class="menu-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>
</aside>

<!-- Main Content Area -->
<main class="main-content">
    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <button class="toggle-sidebar" type="button">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="page-title"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
        </div>
        
        <div class="header-right">
            <div class="user-dropdown">
                <div class="dropdown-toggle">
                    <div class="user-avatar"><?php echo $userInitial; ?></div>
                    <div class="user-info">
                        <div class="user-name"><?php echo $bloodBankInfo['name'] ?? getUserName(); ?></div>
                        <div class="user-role">Blood Bank</div>
                    </div>
                    <i class="fas fa-chevron-down"></i>
                </div>
                
                <div class="dropdown-menu">
                    <a href="/blood_bank/bloodbank/profile.php">
                        <i class="fas fa-user"></i> Profile
                    </a>
                    <a href="/blood_bank/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>" data-auto-hide>
                <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <span><?php echo $flash['message']; ?></span>
            </div>
        <?php endif; ?>
