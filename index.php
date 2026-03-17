<?php
session_start();
require_once 'includes/database.php';
require_once 'includes/functions.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$validPages = ['home', 'services', 'about', 'contact', 'privacy-policy', 'blogs', 'projects', 'terms-conditions'];

if (!in_array($page, $validPages)) {
    $page = 'home';
}

// Get offer status
$offerStatus = getSetting('offer_status');
$offer = $offerStatus == '1' ? getActiveOffer() : null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo getSetting('site_description'); ?>">
    <title><?php echo ucfirst($page) . ' - ' . getSetting('site_title'); ?></title>

    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Afacad:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
</head>

<body>
    <?php include 'includes/header.php'; ?>
    <div class="main-container">
        <div class="content-area full-width<?php echo ($page === 'home') ? ' content-home' : ''; ?>">
            <?php include 'pages/' . $page . '.php'; ?>
        </div>
    </div>

    <?php include 'includes/bottom-nav.php'; ?>

    <?php include 'includes/footer.php'; ?>

    <!-- Offer Modal -->
    <?php if ($offer): ?>
        <div class="modal offer-modal" id="offerModal">
            <div class="modal-content offer-modal-content">
                <span class="close-modal" id="closeOfferModal">&times;</span>
                <div class="offer-container-new">
                    <div class="offer-image-side">
                        <img src="assets/images/uploads/<?php echo $offer['image']; ?>"
                            alt="<?php echo htmlspecialchars($offer['title']); ?>">
                        <div class="offer-image-overlay">
                            <div class="offer-badge"><i class="fas fa-gift"></i> Exclusive Offer</div>
                        </div>
                    </div>
                    <div class="offer-details-side">
                        <div class="offer-tag"><i class="fas fa-bolt"></i> Limited Time Offer</div>
                        <h2><?php echo $offer['title']; ?></h2>
                        <p><?php echo $offer['description']; ?></p>
                        <ul class="offer-benefits">
                            <li><i class="fas fa-check-circle"></i> Free Design Consultation</li>
                            <li><i class="fas fa-check-circle"></i> No Hidden Costs</li>
                            <li><i class="fas fa-check-circle"></i> Customized to Your Budget</li>
                        </ul>
                        <div class="offer-cta-group">
                            <a href="<?php echo $offer['cta_link'] ?: 'index.php?page=contact'; ?>"
                                class="btn btn-cta btn-lg offer-main-btn">
                                <i class="fas fa-calendar-check"></i>
                                <?php echo $offer['cta_text'] ?: 'Claim This Offer'; ?>
                            </a>
                            <a href="tel:+919845061004" class="btn btn-outline offer-call-btn">
                                <i class="fas fa-phone"></i> Call Now
                            </a>
                        </div>
                        <p class="offer-trust"><i class="fas fa-shield-alt"></i> 1000+ happy clients | On-time delivery
                            guaranteed</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Popup Enquiry Modal (not on contact page) -->
    <?php if ($page !== 'contact'): ?>
    <div class="modal popup-enquiry-modal" id="popupEnquiryModal">
        <div class="modal-content popup-enquiry-content">
            <span class="close-modal" id="closePopupEnquiry">&times;</span>

            <!-- Thank You Screen (hidden by default) -->
            <div class="popup-thankyou" id="popupThankyou" style="display:none;">
                <div class="popup-thankyou-inner">
                    <div class="thankyou-icon"><i class="fas fa-check-circle"></i></div>
                    <h2>Thank You!</h2>
                    <p>Your enquiry has been submitted successfully.</p>
                    <p class="small">We'll get back to you within 24 hours. A confirmation email has been sent to your inbox.</p>
                    <button type="button" class="btn btn-primary" id="popupSubmitAnother">Submit Another Enquiry</button>
                </div>
            </div>

            <!-- Form Screen -->
            <div class="popup-form-screen" id="popupFormScreen">
                <div class="popup-form-header">
                    <h2>Get Your <span class="text-gradient">Free Consultation</span></h2>
                    <p>Fill out the form and our design team will reach out within 24 hours.</p>
                    <div class="popup-trust-badges">
                        <span><i class="fas fa-shield-alt"></i> 100% Free</span>
                        <span><i class="fas fa-lock"></i> Info Secure</span>
                        <span><i class="fas fa-clock"></i> 24hr Response</span>
                    </div>
                    <div class="popup-progress">
                        <div class="popup-progress-bar" id="popupProgressBar" style="width:25%;"></div>
                    </div>
                    <div class="popup-step-label" id="popupStepLabel">Step 1 of 4</div>
                </div>

                <form id="popupEnquiryForm" class="popup-enquiry-form">
                    <!-- Step 1: Project Details -->
                    <div class="popup-step" data-step="1">
                        <h3>Project Details</h3>
                        <div class="form-group">
                            <label>Project Type *</label>
                            <div class="radio-group">
                                <label class="radio-option"><input type="radio" name="popup_project_type" value="residential" required><span>Residential</span></label>
                                <label class="radio-option"><input type="radio" name="popup_project_type" value="commercial" required><span>Commercial</span></label>
                                <label class="radio-option"><input type="radio" name="popup_project_type" value="hospitality" required><span>Hospitality</span></label>
                                <label class="radio-option"><input type="radio" name="popup_project_type" value="other" required><span>Other</span></label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="popup_space_size">Space Size (sq ft)</label>
                            <select id="popup_space_size" name="popup_space_size">
                                <option value="">Select size</option>
                                <option value="less-1000">Less than 1,000 sq ft</option>
                                <option value="1000-2000">1,000 - 2,000 sq ft</option>
                                <option value="2000-3000">2,000 - 3,000 sq ft</option>
                                <option value="3000-5000">3,000 - 5,000 sq ft</option>
                                <option value="more-5000">More than 5,000 sq ft</option>
                            </select>
                        </div>
                        <div class="form-buttons">
                            <button type="button" class="btn btn-primary popup-next-step">Next <i class="fas fa-arrow-right"></i></button>
                        </div>
                    </div>

                    <!-- Step 2: Budget & Timeline -->
                    <div class="popup-step" data-step="2" style="display:none;">
                        <h3>Budget & Timeline</h3>
                        <div class="form-group">
                            <label>Budget Range *</label>
                            <div class="radio-group">
                                <label class="radio-option"><input type="radio" name="popup_budget" value="under-1L" required><span>Under ₹1,00,000</span></label>
                                <label class="radio-option"><input type="radio" name="popup_budget" value="1L-2.5L" required><span>₹1L - ₹2.5L</span></label>
                                <label class="radio-option"><input type="radio" name="popup_budget" value="2.5L-5L" required><span>₹2.5L - ₹5L</span></label>
                                <label class="radio-option"><input type="radio" name="popup_budget" value="5L-10L" required><span>₹5L - ₹10L</span></label>
                                <label class="radio-option"><input type="radio" name="popup_budget" value="over-10L" required><span>Over ₹10L</span></label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Timeline *</label>
                            <div class="radio-group">
                                <label class="radio-option"><input type="radio" name="popup_timeline" value="asap" required><span>ASAP</span></label>
                                <label class="radio-option"><input type="radio" name="popup_timeline" value="1-3-months" required><span>1-3 months</span></label>
                                <label class="radio-option"><input type="radio" name="popup_timeline" value="3-6-months" required><span>3-6 months</span></label>
                                <label class="radio-option"><input type="radio" name="popup_timeline" value="6-12-months" required><span>6-12 months</span></label>
                                <label class="radio-option"><input type="radio" name="popup_timeline" value="flexible" required><span>Flexible</span></label>
                            </div>
                        </div>
                        <div class="form-buttons">
                            <button type="button" class="btn btn-outline popup-prev-step"><i class="fas fa-arrow-left"></i> Previous</button>
                            <button type="button" class="btn btn-primary popup-next-step">Next <i class="fas fa-arrow-right"></i></button>
                        </div>
                    </div>

                    <!-- Step 3: Additional Info -->
                    <div class="popup-step" data-step="3" style="display:none;">
                        <h3>Additional Information</h3>
                        <div class="form-group">
                            <label for="popup_message">Tell us more about your project</label>
                            <textarea id="popup_message" name="popup_message" rows="3" placeholder="Describe your dream space..."></textarea>
                        </div>
                        <div class="form-group">
                            <label>How did you hear about us?</label>
                            <select id="popup_referral" name="popup_referral">
                                <option value="">Select an option</option>
                                <option value="google">Google Search</option>
                                <option value="social-media">Social Media</option>
                                <option value="referral">Referral from a friend</option>
                                <option value="advertisement">Advertisement</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-buttons">
                            <button type="button" class="btn btn-outline popup-prev-step"><i class="fas fa-arrow-left"></i> Previous</button>
                            <button type="button" class="btn btn-primary popup-next-step">Next <i class="fas fa-arrow-right"></i></button>
                        </div>
                    </div>

                    <!-- Step 4: Contact Details -->
                    <div class="popup-step" data-step="4" style="display:none;">
                        <h3>Your Contact Details</h3>
                        <div class="form-group">
                            <label for="popup_name">Full Name *</label>
                            <input type="text" id="popup_name" name="popup_name" placeholder="Your full name" required>
                        </div>
                        <div class="form-group">
                            <label for="popup_email">Email Address *</label>
                            <input type="email" id="popup_email" name="popup_email" placeholder="your@email.com" required>
                        </div>
                        <div class="form-group">
                            <label for="popup_phone">Phone Number *</label>
                            <input type="tel" id="popup_phone" name="popup_phone" placeholder="+91 XXXXX XXXXX" required>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-option">
                                <input type="checkbox" name="popup_newsletter" value="1">
                                <span>Send me design tips & updates</span>
                            </label>
                        </div>
                        <div class="form-buttons">
                            <button type="button" class="btn btn-outline popup-prev-step"><i class="fas fa-arrow-left"></i> Previous</button>
                            <button type="submit" class="btn btn-cta popup-submit-btn">Submit Enquiry <i class="fas fa-paper-plane"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Chatbot -->
    <div class="chatbot-container" aria-live="polite" aria-label="Chat assistant">
        <button class="chatbot-toggle" id="chatbotToggle" aria-label="Open chat" aria-controls="chatbotWindow"
            aria-expanded="false">
            <i class="fas fa-comments" aria-hidden="true"></i>
        </button>
        <div class="chatbot-window" id="chatbotWindow" role="dialog" aria-modal="true" aria-labelledby="chatbotTitle">
            <div class="chatbot-header">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span
                        style="width:34px;height:34px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background: var(--primary-gradient); color:#fff;"><i
                            class="fas fa-feather"></i></span>
                    <div>
                        <h3 id="chatbotTitle">Living 360 Assistant</h3>
                        <small style="display:block;color: var(--dark-gray);">Digital chatbot interface</small>
                    </div>
                </div>
                <button class="close-chatbot" type="button" aria-label="Close chat">&times;</button>
            </div>
            <div class="chatbot-messages" id="chatbotMessages"></div>
            <div class="chatbot-input">
                <button class="input-action" id="chatbotPlus" type="button" title="More options"
                    aria-label="More options"><i class="fas fa-plus"></i></button>
                <input type="text" id="chatbotInput" placeholder="Chat here.." aria-label="Message input">
                <button class="input-action" id="chatbotVoice" type="button" title="Voice" aria-label="Voice input"><i
                        class="fas fa-microphone"></i></button>
                <button class="input-action send" id="sendMessage" type="button" aria-label="Send message"><i
                        class="fas fa-paper-plane" aria-hidden="true"></i></button>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/main.js?v=20251118-1"></script>
    <script src="assets/js/chatbot.js"></script>
</body>

</html>