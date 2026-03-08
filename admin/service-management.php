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
            $icon = isset($_POST['icon']) ? sanitizeInput($_POST['icon']) : '';
            $status = isset($_POST['status']) ? 1 : 0;
            
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
                $stmt = $conn->prepare("SELECT image FROM services WHERE id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $service = $stmt->fetch(PDO::FETCH_ASSOC);
                $image = $service['image'];
            }
            
            $conn = dbConnect();
            
            if ($action === 'add') {
                // Add new service
                $stmt = $conn->prepare("INSERT INTO services (title, description, icon, image, status) 
                                       VALUES (:title, :description, :icon, :image, :status)");
                
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':icon', $icon);
                $stmt->bindParam(':image', $image);
                $stmt->bindParam(':status', $status);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = 'Service added successfully.';
                } else {
                    $_SESSION['error'] = 'Failed to add service.';
                }
            } else {
                // Update existing service
                $stmt = $conn->prepare("UPDATE services SET 
                                       title = :title, 
                                       description = :description, 
                                       icon = :icon, 
                                       image = :image, 
                                       status = :status 
                                       WHERE id = :id");
                
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':icon', $icon);
                $stmt->bindParam(':image', $image);
                $stmt->bindParam(':status', $status);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = 'Service updated successfully.';
                } else {
                    $_SESSION['error'] = 'Failed to update service.';
                }
            }
            
            header('Location: service-management.php');
            exit;
        } elseif ($action === 'delete') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            
            if ($id > 0) {
                $conn = dbConnect();
                $stmt = $conn->prepare("DELETE FROM services WHERE id = :id");
                $stmt->bindParam(':id', $id);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = 'Service deleted successfully.';
                } else {
                    $_SESSION['error'] = 'Failed to delete service.';
                }
            }
            
            header('Location: service-management.php');
            exit;
        }
    }
}

// Get action and ID from URL
 $action = isset($_GET['action']) ? $_GET['action'] : '';
 $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get service data for editing
 $service = null;
if ($action === 'edit' && $id > 0) {
    $conn = dbConnect();
    $stmt = $conn->prepare("SELECT * FROM services WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $service = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get all services
 $conn = dbConnect();
 $stmt = $conn->prepare("SELECT * FROM services ORDER BY id ASC");
 $stmt->execute();
 $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Management - Living 360 Interiors Admin</title>
    
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
                    <li><a href="service-management.php" class="active"><i class="fas fa-concierge-bell"></i> Service Management</a></li>
                    <li><a href="project-management.php"><i class="fas fa-drafting-compass"></i> Project Management</a></li>
                    <li><a href="offer-management.php"><i class="fas fa-tags"></i> Offer Management</a></li>
                    <li><a href="enquiry-management.php"><i class="fas fa-envelope"></i> Enquiry Management</a></li>
                    <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <h1>Service Management</h1>
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
                
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <!-- Add/Edit Service Form -->
                    <div class="admin-section">
                        <div class="section-header">
                            <h2><?php echo $action === 'add' ? 'Add New Service' : 'Edit Service'; ?></h2>
                            <a href="service-management.php" class="btn btn-outline">Cancel</a>
                        </div>
                        
                        <form method="post" action="service-management.php" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="<?php echo $action; ?>">
                            <?php if ($action === 'edit'): ?>
                                <input type="hidden" name="id" value="<?php echo $service['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="title">Title *</label>
                                <input type="text" id="title" name="title" required value="<?php echo $action === 'edit' ? $service['title'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description *</label>
                                <textarea id="description" name="description" required><?php echo $action === 'edit' ? $service['description'] : ''; ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="icon">Icon Class *</label>
                                <input type="text" id="icon" name="icon" required value="<?php echo $action === 'edit' ? $service['icon'] : ''; ?>">
                                <small>Example: fas fa-couch</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="image">Image</label>
                                <input type="file" id="image" name="image" accept="image/*">
                                <?php if ($action === 'edit' && !empty($service['image'])): ?>
                                    <div class="current-image">
                                        <img src="../assets/images/uploads/<?php echo $service['image']; ?>" alt="Current Image">
                                        <p>Current Image</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label class="checkbox-option">
                                    <input type="checkbox" name="status" value="1" <?php echo ($action === 'edit' && $service['status'] == 1) ? 'checked' : ''; ?>>
                                    <span>Active</span>
                                </label>
                            </div>
                            
                            <div class="form-buttons">
                                <button type="submit" class="btn btn-primary"><?php echo $action === 'add' ? 'Add Service' : 'Update Service'; ?></button>
                                <a href="service-management.php" class="btn btn-outline">Cancel</a>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <!-- Service List -->
                    <div class="admin-section">
                        <div class="section-header">
                            <h2>All Services</h2>
                            <a href="service-management.php?action=add" class="btn btn-primary">Add New Service</a>
                        </div>
                        
                        <div class="data-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Icon</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($services as $service): ?>
                                    <tr>
                                        <td><?php echo $service['id']; ?></td>
                                        <td><?php echo $service['title']; ?></td>
                                        <td><i class="<?php echo $service['icon']; ?>"></i></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $service['status'] ? 'active' : 'inactive'; ?>">
                                                <?php echo $service['status'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="service-management.php?action=edit&id=<?php echo $service['id']; ?>" class="btn-icon" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="javascript:void(0);" class="btn-icon delete-btn" title="Delete" data-id="<?php echo $service['id']; ?>">
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
                <p>Are you sure you want to delete this service? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form method="post" action="service-management.php">
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