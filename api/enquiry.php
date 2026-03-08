<?php
require_once '../includes/functions.php';

// Set header to return JSON
header('Content-Type: application/json');

// Optional debug flag (?debug=1)
$__debug = isset($_GET['debug']) && $_GET['debug'] == '1';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitizeInput($_POST['phone']) : '';
    $project_type = isset($_POST['project_type']) ? sanitizeInput($_POST['project_type']) : '';
    $space_size = isset($_POST['space_size']) ? sanitizeInput($_POST['space_size']) : '';
    $budget = isset($_POST['budget']) ? sanitizeInput($_POST['budget']) : '';
    $timeline = isset($_POST['timeline']) ? sanitizeInput($_POST['timeline']) : '';
    $message = isset($_POST['message']) ? sanitizeInput($_POST['message']) : '';
    $referral = isset($_POST['referral']) ? sanitizeInput($_POST['referral']) : '';
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($project_type) || empty($budget) || empty($timeline)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please fill in all required fields.'
        ]);
        exit;
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please enter a valid email address.'
        ]);
        exit;
    }
    
    // Prepare data for database
    $enquiryData = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'project_type' => $project_type,
        'space_size' => $space_size,
        'budget' => $budget,
        'timeline' => $timeline,
        'message' => $message,
        'referral' => $referral,
        'newsletter' => $newsletter,
        'status' => 'new'
    ];
    
    // Create enquiry in database
    $enquiryId = createEnquiry($enquiryData);
    
    if ($enquiryId) {
        // Send confirmation email to the customer only
        $customerSubject = 'Thank you for your enquiry with Living 360 Interiors';
        $customerMessage = "
        <html>
        <head>
            <style>
                body { font-family: 'Afacad', sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px; }
                .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
                .email-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
                .email-header h1 { margin: 0; font-size: 28px; font-weight: 600; }
                .email-body { padding: 30px; color: #333333; }
                .email-body p { line-height: 1.6; margin-bottom: 15px; }
                .email-footer { background-color: #f8f9fa; padding: 20px; text-align: center; color: #666666; font-size: 14px; }
                .highlight { color: #667eea; font-weight: 600; }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <h1>Living 360 Interiors</h1>
                    <p>Professional Interior Design</p>
                </div>
                <div class='email-body'>
                    <p>Dear <span class='highlight'>{$name}</span>,</p>
                    <p>Thank you for contacting Living 360 Interiors. We have received your enquiry and our team will review it carefully.</p>
                    <p>We will get back to you as soon as possible to discuss your project requirements and next steps.</p>
                    <p>Best regards,<br><strong>The Living 360 Team</strong></p>
                </div>
                <div class='email-footer'>
                    <p>103/25, 2nd Cross, Puttenahalli Main Rd, J.P Nagar 7th Phase, Bengaluru - 560078</p>
                    <p>+91-98450-61004 | +91-80950-50360 | design@living360.in</p>
                    <p>&copy; 2024 Living 360 Interiors. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
        // Mail failures should not affect success response
        @sendEmailNotification($email, $customerSubject, $customerMessage);

        // If this was a non-AJAX submission requesting redirect, send user to contact page
        if (!empty($_POST['redirect'])) {
            header('Location: /pages/contact.php?success=1');
            exit;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Thank you for your enquiry! We will get back to you soon.'
        ]);
    } else {
        // On failure, if redirect requested, send back to contact page with error flag
        if (!empty($_POST['redirect'])) {
            $errFlag = $__debug ? ('&error=' . rawurlencode((string) getLastDbError())) : '';
            header('Location: /pages/contact.php?error=1' . $errFlag);
            exit;
        }

        $resp = [
            'success' => false,
            'message' => 'An error occurred while submitting your enquiry. Please try again later.'
        ];
        if ($__debug) {
            $resp['error'] = getLastDbError();
        }
        echo json_encode($resp);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}
?>