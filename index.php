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
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <div class="offer-container">
                    <div class="offer-image">
                        <img src="assets/images/uploads/<?php echo $offer['image']; ?>"
                            alt="<?php echo $offer['title']; ?>">
                    </div>
                    <div class="offer-details">
                        <h2><?php echo $offer['title']; ?></h2>
                        <p><?php echo $offer['description']; ?></p>
                        <a href="<?php echo $offer['cta_link']; ?>"
                            class="btn btn-primary"><?php echo $offer['cta_text']; ?></a>
                    </div>
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