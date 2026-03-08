<?php
session_start();
require_once '../includes/functions.php';

// Check if admin is logged in
requireAdminLogin();

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'update-settings') {
            $site_title = isset($_POST['site_title']) ? sanitizeInput($_POST['site_title']) : '';
            $site_description = isset($_POST['site_description']) ? sanitizeInput($_POST['site_description']) : '';
            $privacy_policy = isset($_POST['privacy_policy']) ? $_POST['privacy_policy'] : '';
            $terms_conditions = isset($_POST['terms_conditions']) ? $_POST['terms_conditions'] : '';
            
            $conn = dbConnect();
            
            // Update site title
            $stmt = $conn->prepare("UPDATE settings SET setting_value = :value WHERE setting_key = 'site_title'");
            $stmt->bindParam(':value', $site_title);
            $stmt->execute();
            
            // Update site description
            $stmt = $conn->prepare("UPDATE settings SET setting_value = :value WHERE setting_key = 'site_description'");
            $stmt->bindParam(':value', $site_description);
            $stmt->execute();
            
            // Update privacy policy
            $stmt = $conn->prepare("UPDATE settings SET setting_value = :value WHERE setting_key = 'privacy_policy'");
            $stmt->bindParam(':value', $privacy_policy);
            $stmt->execute();
            
            // Update terms and conditions
            $stmt = $conn->prepare("UPDATE settings SET setting_value = :value WHERE setting_key = 'terms_conditions'");
            $stmt->bindParam(':value', $terms_conditions);
            $stmt->execute();
            
            $_SESSION['success'] = 'Settings updated successfully.';
            header('Location: settings.php');
            exit;
        } elseif ($action === 'change-password') {
            $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
            $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
            $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
            
            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                $_SESSION['error'] = 'Please fill in all password fields.';
            } elseif ($new_password !== $confirm_password) {
                $_SESSION['error'] = 'New password and confirm password do not match.';
            } elseif (strlen($new_password) < 6) {
                $_SESSION['error'] = 'New password must be at least 6 characters long.';
            } else {
                $conn = dbConnect();
                $stmt = $conn->prepare("SELECT password FROM admin WHERE id = :id");
                $stmt->bindParam(':id', $_SESSION['admin_id']);
                $stmt->execute();
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($admin && password_verify($current_password, $admin['password'])) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    $stmt = $conn->prepare("UPDATE admin SET password = :password WHERE id = :id");
                    $stmt->bindParam(':password', $hashed_password);
                    $stmt->bindParam(':id', $_SESSION['admin_id']);
                    
                    if ($stmt->execute()) {
                        $_SESSION['success'] = 'Password changed successfully.';
                    } else {
                        $_SESSION['error'] = 'Failed to change password.';
                    }
                } else {
                    $_SESSION['error'] = 'Current password is incorrect.';
                }
            }
            
            header('Location: settings.php');
            exit;
        }
    }
}

// Get current settings
 $site_title = getSetting('site_title');
 $site_description = getSetting('site_description');
 $privacy_policy = getSetting('privacy_policy');
 $terms_conditions = getSetting('terms_conditions');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Living 360 Interiors Admin</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Afacad:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/admin.css">
    
    <!-- TinyMCE Editor -->
    <script src="https://cdn.tiny.cloud/1/joy76qekevovieharasdx3t30ogkcmrbdvikouphuu0ux9uz/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <img src="../assets/images/logo.png" alt="Living 360 Interiors">
                <h2>Admin Panel</h2>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="blog-management.php"><i class="fas fa-blog"></i> Blog Management</a></li>
                    <li><a href="service-management.php"><i class="fas fa-concierge-bell"></i> Service Management</a></li>
                    <li><a href="project-management.php"><i class="fas fa-drafting-compass"></i> Project Management</a></li>
                    <li><a href="offer-management.php"><i class="fas fa-tags"></i> Offer Management</a></li>
                    <li><a href="enquiry-management.php"><i class="fas fa-envelope"></i> Enquiry Management</a></li>
                    <li><a href="settings.php" class="active"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <h1>Settings</h1>
                <div class="admin-info">
                    <span>Welcome, <?php echo $_SESSION['admin_username']; ?></span>
                    <a href="auth/logout.php" class="btn btn-outline">Logout</a>
                </div>
            </header>
            
            <div class="admin-content">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                
                <!-- General Settings -->
                <div class="admin-section">
                    <div class="section-header">
                        <h2>General Settings</h2>
                    </div>
                    
                    <form method="post" action="settings.php">
                        <input type="hidden" name="action" value="update-settings">
                        
                        <div class="form-group">
                            <label for="site_title">Site Title *</label>
                            <input type="text" id="site_title" name="site_title" required value="<?php echo $site_title; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="site_description">Site Description *</label>
                            <textarea id="site_description" name="site_description" required><?php echo $site_description; ?></textarea>
                        </div>
                        
                        <div class="form-buttons">
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </div>
                    </form>
                </div>
                
                <!-- Privacy Policy -->
                <div class="admin-section">
                    <div class="section-header">
                        <h2>Privacy Policy</h2>
                    </div>
                    
                    <form method="post" action="settings.php">
                        <input type="hidden" name="action" value="update-settings">
                        
                        <div class="form-group">
                            <label for="privacy_policy">Privacy Policy Content *</label>
                            <textarea id="privacy_policy" name="privacy_policy" required><?php echo $privacy_policy; ?></textarea>
                        </div>
                        
                        <div class="form-buttons">
                            <button type="submit" class="btn btn-primary">Save Privacy Policy</button>
                        </div>
                    </form>
                </div>
                
                <!-- Terms & Conditions -->
                <div class="admin-section">
                    <div class="section-header">
                        <h2>Terms & Conditions</h2>
                    </div>
                    
                    <form method="post" action="settings.php">
                        <input type="hidden" name="action" value="update-settings">
                        
                        <div class="form-group">
                            <label for="terms_conditions">Terms & Conditions Content *</label>
                            <textarea id="terms_conditions" name="terms_conditions" required><?php echo $terms_conditions; ?></textarea>
                        </div>
                        
                        <div class="form-buttons">
                            <button type="submit" class="btn btn-primary">Save Terms & Conditions</button>
                        </div>
                    </form>
                </div>
                
                <!-- Change Password -->
                <div class="admin-section">
                    <div class="section-header">
                        <h2>Change Password</h2>
                    </div>
                    
                    <form method="post" action="settings.php">
                        <input type="hidden" name="action" value="change-password">
                        
                        <div class="form-group">
                            <label for="current_password">Current Password *</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password *</label>
                            <input type="password" id="new_password" name="new_password" required minlength="6">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password *</label>
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                        </div>
                        
                        <div class="form-buttons">
                            <button type="submit" class="btn btn-primary">Change Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
        // Initialize TinyMCE for privacy policy
        tinymce.init({
            selector: '#privacy_policy',
            height: 400,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount'
            ],
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
            content_style: 'body { font-family: \'Afacad\', sans-serif; font-size: 14px }'
        });
        
        // Initialize TinyMCE for terms & conditions
        tinymce.init({
            selector: '#terms_conditions',
            height: 400,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount'
            ],
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
            content_style: 'body { font-family: \'Afacad\', sans-serif; font-size: 14px }'
        });
    </script>
</body>
</html>