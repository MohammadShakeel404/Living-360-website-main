<header class="main-header">
    <!-- Announcement Bar -->
    <div class="announcement-bar">
        <div class="container">
            <p><i class="fas fa-bolt"></i> <strong>Limited Offer:</strong> Book a free consultation this month &amp; get 10% off your first project! <a href="javascript:void(0)" class="open-popup-enquiry">Claim Now &rarr;</a></p>
        </div>
    </div>
    <div class="header-main">
        <div class="container header-inner">
            <div class="logo">
                <a href="index.php">
                    <img src="assets/images/logo.png" alt="Living 360 Interiors">
                </a>
            </div>

            <?php 
                $phone = function_exists('getSetting') ? (getSetting('contact_phone') ?: '+91-98450-61004') : '+91-98450-61004';
                $email = function_exists('getSetting') ? (getSetting('contact_email') ?: 'design@living360.in') : 'design@living360.in';
                $telHref = 'tel:' . preg_replace('/[^0-9+]/', '', $phone);
                $mailHref = 'mailto:' . $email;
            ?>

            <!-- Desktop Navigation -->
            <nav class="main-nav desktop-nav" id="mainNav">
                <a href="index.php?page=home" class="nav-link <?php echo $page == 'home' ? 'active' : ''; ?>">Home</a>
                <a href="index.php?page=services" class="nav-link <?php echo $page == 'services' ? 'active' : ''; ?>">Services</a>
                <a href="index.php?page=projects" class="nav-link <?php echo $page == 'projects' ? 'active' : ''; ?>">Projects</a>
                <a href="index.php?page=about" class="nav-link <?php echo $page == 'about' ? 'active' : ''; ?>">About</a>
                <a href="index.php?page=blogs" class="nav-link <?php echo $page == 'blogs' ? 'active' : ''; ?>">Blog</a>
                <a href="index.php?page=contact" class="nav-link <?php echo $page == 'contact' ? 'active' : ''; ?>">Contact</a>
            </nav>

            <div class="header-actions">
                <!-- Desktop -->
                <a href="<?php echo $telHref; ?>" class="header-phone desktop" aria-label="Call us">
                    <i class="fas fa-phone"></i>
                    <span><?php echo htmlspecialchars($phone); ?></span>
                </a>
                <a href="javascript:void(0)" class="btn btn-cta desktop open-popup-enquiry" aria-label="Get free consultation">Get Free Quote</a>

                <!-- Mobile hamburger -->
                <button class="hamburger mobile" id="hamburgerBtn" aria-label="Open menu" aria-expanded="false">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Nav Drawer -->
    <div class="mobile-nav-drawer" id="mobileNavDrawer">
        <nav class="mobile-nav-links">
            <a href="index.php?page=home" class="<?php echo $page == 'home' ? 'active' : ''; ?>">Home</a>
            <a href="index.php?page=services" class="<?php echo $page == 'services' ? 'active' : ''; ?>">Services</a>
            <a href="index.php?page=projects" class="<?php echo $page == 'projects' ? 'active' : ''; ?>">Projects</a>
            <a href="index.php?page=about" class="<?php echo $page == 'about' ? 'active' : ''; ?>">About</a>
            <a href="index.php?page=blogs" class="<?php echo $page == 'blogs' ? 'active' : ''; ?>">Blog</a>
            <a href="index.php?page=contact" class="<?php echo $page == 'contact' ? 'active' : ''; ?>">Contact</a>
        </nav>
        <div class="mobile-nav-contact">
            <a href="<?php echo $telHref; ?>" class="btn btn-outline"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($phone); ?></a>
            <a href="<?php echo $mailHref; ?>" class="btn btn-outline"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($email); ?></a>
            <a href="javascript:void(0)" class="btn btn-cta open-popup-enquiry">Get Free Consultation</a>
        </div>
    </div>
</header>