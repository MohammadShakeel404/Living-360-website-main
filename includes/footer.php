<footer class="main-footer">
    <!-- Footer CTA -->
    <div class="footer-cta-section">
        <div class="container">
            <div class="footer-cta-card">
                <div class="footer-cta-text">
                    <h2>Ready to Transform Your Space?</h2>
                    <p>Book a free consultation today and let's bring your vision to life.</p>
                </div>
                <a href="index.php?page=contact" class="btn btn-cta btn-lg">Book Free Consultation <i
                        class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <div class="footer-main">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section footer-brand">
                    <div class="footer-logo">
                        <img src="assets/images/logo.png" alt="Living 360 Interiors">
                    </div>
                    <p>Transforming spaces into beautiful, functional environments that reflect your unique style.
                        Trusted by 1000+ happy clients across Bangalore.</p>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" aria-label="Pinterest"><i class="fab fa-pinterest-p"></i></a>
                    </div>
                </div>

                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php?page=home">Home</a></li>
                        <li><a href="index.php?page=services">Services</a></li>
                        <li><a href="index.php?page=projects">Projects</a></li>
                        <li><a href="index.php?page=blogs">Blog</a></li>
                        <li><a href="index.php?page=about">About Us</a></li>
                        <li><a href="index.php?page=contact">Contact</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3>Our Services</h3>
                    <ul>
                        <?php
                        $services = getActiveServices();
                        foreach ($services as $service) {
                            echo '<li><a href="index.php?page=services#' . $service['id'] . '">' . $service['title'] . '</a></li>';
                        }
                        ?>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3>Get In Touch</h3>
                    <div class="contact-info">
                        <p><i class="fas fa-map-marker-alt"></i> 103/25, 2nd Cross, Puttenahalli Main Rd, J.P Nagar 7th
                            Phase, Bengaluru - 560078</p>
                        <p><i class="fas fa-phone"></i> +91-98450-61004</p>
                        <p><i class="fas fa-envelope"></i> design@living360.in</p>
                    </div>
                    <div class="footer-trust">
                        <div class="trust-item"><i class="fas fa-shield-alt"></i> <span>100% Satisfaction
                                Guarantee</span></div>
                        <div class="trust-item"><i class="fas fa-clock"></i> <span>On-Time Delivery</span></div>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <div class="copyright">
                    <p>&copy; <?php echo date('Y'); ?> Living 360 Interiors. All Rights Reserved.</p>
                </div>
                <div class="footer-links">
                    <a href="index.php?page=privacy-policy">Privacy Policy</a>
                    <a href="index.php?page=terms-conditions">Terms & Conditions</a>
                </div>
            </div>
        </div>
    </div>
</footer>