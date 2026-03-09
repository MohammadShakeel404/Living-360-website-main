<?php
$showSuccess = isset($_GET['success']) && $_GET['success'] == '1';
$error = isset($_GET['error']) ? 'An error occurred while submitting your enquiry. Please try again.' : null;
?>

<div class="page-hero">
    <div class="container">
        <span class="section-tag">Let's Talk</span>
        <h1>Get Your <span class="text-gradient">Free Consultation</span></h1>
        <p>Tell us about your project and our design experts will get back to you within 24 hours.</p>
    </div>
</div>

<div class="section contact-info-section">
    <div class="container">
        <div class="contact-info-grid">
            <div class="contact-info-card animate-on-scroll">
                <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                <h3>Visit Our Studio</h3>
                <p>103/25, 2nd Cross, Puttenahalli Main Rd,<br>J.P Nagar 7th Phase, Bengaluru - 560078</p>
            </div>
            <div class="contact-info-card animate-on-scroll">
                <div class="contact-icon"><i class="fas fa-phone"></i></div>
                <h3>Call Us</h3>
                <p><a href="tel:+919845061004">+91-98450-61004</a><br><a href="tel:+918095050360">+91-80950-50360</a>
                </p>
            </div>
            <div class="contact-info-card animate-on-scroll">
                <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                <h3>Email Us</h3>
                <p><a href="mailto:design@living360.in">design@living360.in</a></p>
            </div>
        </div>
    </div>
</div>

<div class="section contact-form-section">
    <div class="container">
        <div id="successMessage" class="success-message"
            style="display: <?php echo $showSuccess ? 'block' : 'none'; ?>; margin-bottom: 16px;">
            <div class="success-icon"><i class="fas fa-check-circle"></i></div>
            <h2>Thank You!</h2>
            <p>Your enquiry has been submitted successfully.</p>
            <p class="small">We'll get back to you within 24 hours. A confirmation email has been sent to your inbox.
            </p>
            <div style="display:flex; gap:10px; flex-wrap:wrap; justify-content:center;">
                <a href="index.php?page=contact" class="btn btn-primary">Submit Another Enquiry</a>
            </div>
        </div>

        <div class="contact-form-container">
            <div class="contact-form-text" id="formContent">
                <h2>Let's Discuss <span class="text-gradient">Your Project</span></h2>
                <p>Fill out the form and our design team will reach out within 24 hours with ideas and a rough estimate.
                </p>
                <div class="contact-form-trust">
                    <div class="trust-badge"><i class="fas fa-shield-alt"></i> <span>100% Free Consultation</span></div>
                    <div class="trust-badge"><i class="fas fa-lock"></i> <span>Your Info is Secure</span></div>
                    <div class="trust-badge"><i class="fas fa-clock"></i> <span>Response Within 24hrs</span></div>
                </div>
                <div class="contact-form-steps">
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 25%;"></div>
                    </div>
                    <div class="step-indicator">Step 1 of 4</div>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form class="enquiry-form" id="enquiryForm" method="post" action="api/enquiry.php"
                onsubmit="return contactSubmit(this);">
                <input type="hidden" name="redirect" value="1">

                <div class="form-step">
                    <h3>Project Details</h3>
                    <div class="form-group">
                        <label>Project Type *</label>
                        <div class="radio-group">
                            <label class="radio-option"><input type="radio" name="project_type" value="residential"
                                    required><span>Residential</span></label>
                            <label class="radio-option"><input type="radio" name="project_type" value="commercial"
                                    required><span>Commercial</span></label>
                            <label class="radio-option"><input type="radio" name="project_type" value="hospitality"
                                    required><span>Hospitality</span></label>
                            <label class="radio-option"><input type="radio" name="project_type" value="other"
                                    required><span>Other</span></label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="space_size">Space Size (sq ft)</label>
                        <select id="space_size" name="space_size">
                            <option value="">Select size</option>
                            <option value="less-1000">Less than 1,000 sq ft</option>
                            <option value="1000-2000">1,000 - 2,000 sq ft</option>
                            <option value="2000-3000">2,000 - 3,000 sq ft</option>
                            <option value="3000-5000">3,000 - 5,000 sq ft</option>
                            <option value="more-5000">More than 5,000 sq ft</option>
                        </select>
                    </div>
                    <div class="form-buttons">
                        <button type="button" class="btn btn-primary next-step">Next <i
                                class="fas fa-arrow-right"></i></button>
                    </div>
                </div>

                <div class="form-step">
                    <h3>Budget & Timeline</h3>
                    <div class="form-group">
                        <label>Budget Range *</label>
                        <div class="radio-group">
                            <label class="radio-option"><input type="radio" name="budget" value="under-1L"
                                    required><span>Under ₹1,00,000</span></label>
                            <label class="radio-option"><input type="radio" name="budget" value="1L-2.5L"
                                    required><span>₹1L - ₹2.5L</span></label>
                            <label class="radio-option"><input type="radio" name="budget" value="2.5L-5L"
                                    required><span>₹2.5L - ₹5L</span></label>
                            <label class="radio-option"><input type="radio" name="budget" value="5L-10L"
                                    required><span>₹5L - ₹10L</span></label>
                            <label class="radio-option"><input type="radio" name="budget" value="over-10L"
                                    required><span>Over ₹10L</span></label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Timeline *</label>
                        <div class="radio-group">
                            <label class="radio-option"><input type="radio" name="timeline" value="asap"
                                    required><span>ASAP</span></label>
                            <label class="radio-option"><input type="radio" name="timeline" value="1-3-months"
                                    required><span>1-3 months</span></label>
                            <label class="radio-option"><input type="radio" name="timeline" value="3-6-months"
                                    required><span>3-6 months</span></label>
                            <label class="radio-option"><input type="radio" name="timeline" value="6-12-months"
                                    required><span>6-12 months</span></label>
                            <label class="radio-option"><input type="radio" name="timeline" value="flexible"
                                    required><span>Flexible</span></label>
                        </div>
                    </div>
                    <div class="form-buttons">
                        <button type="button" class="btn btn-outline prev-step"><i class="fas fa-arrow-left"></i>
                            Previous</button>
                        <button type="button" class="btn btn-primary next-step">Next <i
                                class="fas fa-arrow-right"></i></button>
                    </div>
                </div>

                <div class="form-step">
                    <h3>Additional Information</h3>
                    <div class="form-group">
                        <label for="message">Tell us more about your project</label>
                        <textarea id="message" name="message" rows="4"
                            placeholder="Describe your dream space..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>How did you hear about us?</label>
                        <select id="referral" name="referral">
                            <option value="">Select an option</option>
                            <option value="google">Google Search</option>
                            <option value="social-media">Social Media</option>
                            <option value="referral">Referral from a friend</option>
                            <option value="advertisement">Advertisement</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-buttons">
                        <button type="button" class="btn btn-outline prev-step"><i class="fas fa-arrow-left"></i>
                            Previous</button>
                        <button type="button" class="btn btn-primary next-step">Next <i
                                class="fas fa-arrow-right"></i></button>
                    </div>
                </div>

                <div class="form-step">
                    <h3>Your Contact Details</h3>
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" placeholder="Your full name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" placeholder="your@email.com" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" placeholder="+91 XXXXX XXXXX" required>
                    </div>
                    <div class="form-group">
                        <label class="checkbox-option">
                            <input type="checkbox" name="newsletter" value="1">
                            <span>Send me design tips & updates</span>
                        </label>
                    </div>
                    <div class="form-buttons">
                        <button type="button" class="btn btn-outline prev-step"><i class="fas fa-arrow-left"></i>
                            Previous</button>
                        <button type="submit" class="btn btn-cta submit-form" style="display: none;">Submit Enquiry <i
                                class="fas fa-paper-plane"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="section map-section">
    <div class="container">
        <div class="section-title">
            <h2>Find <span class="text-gradient">Our Studio</span></h2>
        </div>
        <div class="map-container">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3889.1614871799584!2d77.57970307358768!3d12.89733561650514!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bae152ccaab89a7%3A0x812cad341d68deec!2sLiving%20360%20Interiors!5e0!3m2!1sen!2sin!4v1759567218385!5m2!1sen!2sin"
                width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>
</div>