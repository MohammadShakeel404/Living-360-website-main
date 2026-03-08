<?php
session_start();
require_once '../includes/functions.php';

// Check if admin is logged in
requireAdminLogin();

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add' || $action === 'edit') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $title = isset($_POST['title']) ? sanitizeInput($_POST['title']) : '';
            $description = isset($_POST['description']) ? $_POST['description'] : '';
            $cta_text = isset($_POST['cta_text']) ? sanitizeInput($_POST['cta_text']) : '';
            $cta_link = isset($_POST['cta_link']) ? sanitizeInput($_POST['cta_link']) : '';
            $active = isset($_POST['active']) ? 1 : 0;
            
            // Handle image upload
            $image = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $uploadDir = '../assets/images/uploads/';
                $fileName = time() . '_' . basename($_FILES['image']['name']);
                $targetFile = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    $image = $fileName;
                }
            } elseif ($action === 'edit' && empty($image)) {
                // Keep existing image if not uploading a new one
                $conn = dbConnect();
                $stmt = $conn->prepare("SELECT image FROM offers WHERE id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $offer = $stmt->fetch(PDO::FETCH_ASSOC);
                $image = $offer['image'];
            }
            
            $conn = dbConnect();
            
            if ($action === 'add') {
                // Add new offer
                $stmt = $conn->prepare("INSERT INTO offers (title, description, image, cta_text, cta_link, active) 
                                       VALUES (:title, :description, :image, :cta_text, :cta_link, :active)");
                
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':image', $image);
                $stmt->bindParam(':cta_text', $cta_text);
                $stmt->bindParam(':cta_link', $cta_link);
                $stmt->bindParam(':active', $active);
                
                if ($stmt->execute()) {
                    // Update offer status setting if this offer is active
                    if ($active) {
                        // Deactivate all other offers
                        $stmt = $conn->prepare("UPDATE offers SET active = 0 WHERE id != :id");
                        $stmt->bindParam(':id', $conn->lastInsertId());
                        $stmt->execute();
                        
                        // Update offer status setting
                        $stmt = $conn->prepare("UPDATE settings SET setting_value = '1' WHERE setting_key = 'offer_status'");
                        $stmt->execute();
                    }
                    
                    $_SESSION['success'] = 'Offer added successfully.';
                } else {
                    $_SESSION['error'] = 'Failed to add offer.';
                }
            } else {
                // Update existing offer
                $stmt = $conn->prepare("UPDATE offers SET 
                                       title = :title, 
                                       description = :description, 
                                       image = :image, 
                                       cta_text = :cta_text, 
                                       cta_link = :cta_link, 
                                       active = :active 
                                       WHERE id = :id");
                
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':image', $image);
                $stmt->bindParam(':cta_text', $cta_text);
                $stmt->bindParam(':cta_link', $cta_link);
                $stmt->bindParam(':active', $active);
                
                if ($stmt->execute()) {
                    // Update offer status setting if this offer is active
                    if ($active) {
                        // Deactivate all other offers
                        $stmt = $conn->prepare("UPDATE offers SET active = 0 WHERE id != :id");
                        $stmt->bindParam(':id', $id);
                        $stmt->execute();
                        
                        // Update offer status setting
                        $stmt = $conn->prepare("UPDATE settings SET setting_value = '1' WHERE setting_key = 'offer_status'");
                        $stmt->execute();
                    } else {
                        // Check if any offer is active
                        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM offers WHERE active = 1");
                        $stmt->execute();
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($result['count'] == 0) {
                            // Update offer status setting
                            $stmt = $conn->prepare("UPDATE settings SET setting_value = '0' WHERE setting_key = 'offer_status'");
                            $stmt->execute();
                        }
                    }
                    
                    $_SESSION['success'] = 'Offer updated successfully.';
                } else {
                    $_SESSION['error'] = 'Failed to update offer.';
                }
            }
            
            header('Location: offer-management.php');
            exit;
        } elseif ($action === 'delete') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            
            if ($id > 0) {
                $conn = dbConnect();
                
                // Check if this offer is active
                $stmt = $conn->prepare("SELECT active FROM offers WHERE id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $offer = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Delete the offer
                $stmt = $conn->prepare("DELETE FROM offers WHERE id = :id");
                $stmt->bindParam(':id', $id);
                
                if ($stmt->execute()) {
                    // If the deleted offer was active, update the offer status setting
                    if ($offer['active']) {
                        $stmt = $conn->prepare("UPDATE settings SET setting_value = '0' WHERE setting_key = 'offer_status'");
                        $stmt->execute();
                    }
                    
                    $_SESSION['success'] = 'Offer deleted successfully.';
                } else {
                    $_SESSION['error'] = 'Failed to delete offer.';
                }
            }
            
            header('Location: offer-management.php');
            exit;
        } elseif ($action === 'toggle-status') {
            $status = isset($_POST['status']) ? (int)$_POST['status'] : 0;
            
            // Update offer status setting
            $conn = dbConnect();
            $stmt = $conn->prepare("UPDATE settings SET setting_value = :status WHERE setting_key = 'offer_status'");
            $stmt->bindParam(':status', $status);
            
            if ($stmt->execute()) {
                // If turning on offers, activate the most recent offer
                if ($status) {
                    $stmt = $conn->prepare("UPDATE offers SET active = 0");
                    $stmt->execute();
                    
                    $stmt = $conn->prepare("UPDATE offers SET active = 1 ORDER BY id DESC LIMIT 1");
                    $stmt->execute();
                } else {
                    // If turning off offers, deactivate all offers
                    $stmt = $conn->prepare("UPDATE offers SET active = 0");
                    $stmt->execute();
                }
                
                $_SESSION['success'] = 'Offer status updated successfully.';
            } else {
                $_SESSION['error'] = 'Failed to update offer status.';
            }
            
            header('Location: offer-management.php');
            exit;
        }
    }
}

// Get action and ID from URL
 $action = isset($_GET['action']) ? $_GET['action'] : '';
 $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get offer data for editing
 $offer = null;
if ($action === 'edit' && $id > 0) {
    $conn = dbConnect();
    $stmt = $conn->prepare("SELECT * FROM offers WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $offer = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get all offers
 $conn = dbConnect();
 $stmt = $conn->prepare("SELECT * FROM offers ORDER BY created_at DESC");
 $stmt->execute();
 $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get offer status
 $offerStatus = getSetting('offer_status');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offer Management - Living 360 Interiors Admin</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Afacad:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/admin.css">
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
                    <li><a href="offer-management.php" class="active"><i class="fas fa-tags"></i> Offer Management</a></li>
                    <li><a href="enquiry-management.php"><i class="fas fa-envelope"></i> Enquiry Management</a></li>
                    <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <h1>Offer Management</h1>
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
                
                <!-- Offer Status Toggle -->
                <div class="admin-section">
                    <div class="section-header">
                        <h2>Offer Status</h2>
                    </div>
                    
                    <div class="toggle-container">
                        <div class="toggle-info">
                            <h3>Show Offer Modal</h3>
                            <p>Turn on to display the offer modal to website visitors.</p>
                        </div>
                        
                        <form method="post" action="offer-management.php">
                            <input type="hidden" name="action" value="toggle-status">
                            <input type="hidden" name="status" value="<?php echo $offerStatus == '1' ? '0' : '1'; ?>">
                            
                            <div class="toggle-switch">
                                <input type="checkbox" id="offerStatus" <?php echo $offerStatus == '1' ? 'checked' : ''; ?> onchange="this.form.submit()">
                                <label for="offerStatus"></label>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <!-- Add/Edit Offer Form -->
                    <div class="admin-section">
                        <div class="section-header">
                            <h2><?php echo $action === 'add' ? 'Add New Offer' : 'Edit Offer'; ?></h2>
                            <a href="offer-management.php" class="btn btn-outline">Cancel</a>
                        </div>
                        
                        <form method="post" action="offer-management.php" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="<?php echo $action; ?>">
                            <?php if ($action === 'edit'): ?>
                                <input type="hidden" name="id" value="<?php echo $offer['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="title">Title *</label>
                                <input type="text" id="title" name="title" required value="<?php echo $action === 'edit' ? $offer['title'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description *</label>
                                <textarea id="description" name="description" required><?php echo $action === 'edit' ? $offer['description'] : ''; ?></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="cta_text">CTA Button Text *</label>
                                    <input type="text" id="cta_text" name="cta_text" required value="<?php echo $action === 'edit' ? $offer['cta_text'] : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="cta_link">CTA Button Link *</label>
                                    <input type="text" id="cta_link" name="cta_link" required value="<?php echo $action === 'edit' ? $offer['cta_link'] : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="image">Image *</label>
                                <input type="file" id="image" name="image" accept="image/*" <?php echo $action === 'add' ? 'required' : ''; ?>>
                                <?php if ($action === 'edit' && !empty($offer['image'])): ?>
                                    <div class="current-image">
                                        <img src="../assets/images/uploads/<?php echo $offer['image']; ?>" alt="Current Image">
                                        <p>Current Image</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label class="checkbox-option">
                                    <input type="checkbox" name="active" value="1" <?php echo ($action === 'edit' && $offer['active'] == 1) ? 'checked' : ''; ?>>
                                    <span>Active (only one offer can be active at a time)</span>
                                </label>
                            </div>
                            
                            <div class="form-buttons">
                                <button type="submit" class="btn btn-primary"><?php echo $action === 'add' ? 'Add Offer' : 'Update Offer'; ?></button>
                                <a href="offer-management.php" class="btn btn-outline">Cancel</a>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <!-- Offer List -->
                    <div class="admin-section">
                        <div class="section-header">
                            <h2>All Offers</h2>
                            <a href="offer-management.php?action=add" class="btn btn-primary">Add New Offer</a>
                        </div>
                        
                        <div class="data-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>CTA Text</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($offers as $offer): ?>
                                    <tr>
                                        <td><?php echo $offer['id']; ?></td>
                                        <td><?php echo $offer['title']; ?></td>
                                        <td><?php echo $offer['cta_text']; ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $offer['active'] ? 'active' : 'inactive'; ?>">
                                                <?php echo $offer['active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="offer-management.php?action=edit&id=<?php echo $offer['id']; ?>" class="btn-icon" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="javascript:void(0);" class="btn-icon delete-btn" title="Delete" data-id="<?php echo $offer['id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Delete</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this offer? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form method="post" action="offer-management.php">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteId">
                    <button type="button" class="btn btn-outline close-modal-btn">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>