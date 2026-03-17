<nav class="bottom-nav" aria-label="Mobile navigation">
    <a href="index.php?page=home" class="bottom-item <?php echo $page == 'home' ? 'active' : ''; ?>" aria-label="Home">
        <i class="fas fa-home" aria-hidden="true"></i>
        <span>Home</span>
    </a>
    <a href="index.php?page=services" class="bottom-item <?php echo $page == 'services' ? 'active' : ''; ?>" aria-label="Services">
        <i class="fas fa-concierge-bell" aria-hidden="true"></i>
        <span>Services</span>
    </a>
    <?php if ($page !== 'contact'): ?>
    <a href="javascript:void(0)" class="bottom-item cta-item open-popup-enquiry" aria-label="Get Quote">
        <i class="fas fa-comment-dots" aria-hidden="true"></i>
        <span>Get Quote</span>
    </a>
    <?php else: ?>
    <a href="index.php?page=contact" class="bottom-item cta-item active" aria-label="Get Quote">
        <i class="fas fa-comment-dots" aria-hidden="true"></i>
        <span>Get Quote</span>
    </a>
    <?php endif; ?>
    <a href="index.php?page=projects" class="bottom-item <?php echo $page == 'projects' ? 'active' : ''; ?>" aria-label="Projects">
        <i class="fas fa-drafting-compass" aria-hidden="true"></i>
        <span>Projects</span>
    </a>
    <a href="index.php?page=about" class="bottom-item <?php echo $page == 'about' ? 'active' : ''; ?>" aria-label="About">
        <i class="fas fa-info-circle" aria-hidden="true"></i>
        <span>About</span>
    </a>
</nav>
