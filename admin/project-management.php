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
            $client_name = isset($_POST['client_name']) ? sanitizeInput($_POST['client_name']) : '';
            $location = isset($_POST['location']) ? sanitizeInput($_POST['location']) : '';
            $service_id = isset($_POST['service_id']) ? (int)$_POST['service_id'] : 0;
            $status = isset($_POST['status']) ? 1 : 0;
            
            // Handle image uploads
            $images = [];
            
            if ($action === 'edit') {
                // Get existing images
                $conn = dbConnect();
                $stmt = $conn->prepare("SELECT images FROM projects WHERE id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $project = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!empty($project['images'])) {
                    $images = json_decode($project['images'], true);
                }
            }
            
            // Handle new image uploads
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                $uploadDir = '../assets/images/uploads/';
                
                foreach ($_FILES['images']['name'] as $key => $name) {
                    if ($_FILES['images']['error'][$key] === 0) {
                        $fileName = time() . '_' . basename($name);
                        $targetFile = $uploadDir . $fileName;
                        
                        if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $targetFile)) {
                            $images[] = $fileName;
                        }
                    }
                }
            }
            
            // Convert images array to JSON
            $imagesJson = json_encode($images);
            
            $conn = dbConnect();
            
            if ($action === 'add') {
                // Add new project
                $stmt = $conn->prepare("INSERT INTO projects (title, description, client_name, location, images, service_id, status) 
                                       VALUES (:title, :description, :client_name, :location, :images, :service_id, :status)");
                
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':client_name', $client_name);
                $stmt->bindParam(':location', $location);
                $stmt->bindParam(':images', $imagesJson);
                $stmt->bindParam(':service_id', $service_id);
                $stmt->bindParam(':status', $status);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = 'Project added successfully.';
                } else {
                    $_SESSION['error'] = 'Failed to add project.';
                }
            } else {
                // Update existing project
                $stmt = $conn->prepare("UPDATE projects SET 
                                       title = :title, 
                                       description = :description, 
                                       client_name = :client_name, 
                                       location = :location, 
                                       images = :images, 
                                       service_id = :service_id, 
                                       status = :status 
                                       WHERE id = :id");
                
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':client_name', $client_name);
                $stmt->bindParam(':location', $location);
                $stmt->bindParam(':images', $imagesJson);
                $stmt->bindParam(':service_id', $service_id);
                $stmt->bindParam(':status', $status);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = 'Project updated successfully.';
                } else {
                    $_SESSION['error'] = 'Failed to update project.';
                }
            }
            
            header('Location: project-management.php');
            exit;
        } elseif ($action === 'delete') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            
            if ($id > 0) {
                $conn = dbConnect();
                $stmt = $conn->prepare("DELETE FROM projects WHERE id = :id");
                $stmt->bindParam(':id', $id);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = 'Project deleted successfully.';
                } else {
                    $_SESSION['error'] = 'Failed to delete project.';
                }
            }
            
            header('Location: project-management.php');
            exit;
        } elseif ($action === 'delete-image') {
            $projectId = isset($_POST['project_id']) ? (int)$_POST['project_id'] : 0;
            $imageName = isset($_POST['image_name']) ? $_POST['image_name'] : '';
            
            if ($projectId > 0 && !empty($imageName)) {
                $conn = dbConnect();
                $stmt = $conn->prepare("SELECT images FROM projects WHERE id = :id");
                $stmt->bindParam(':id', $projectId);
                $stmt->execute();
                $project = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!empty($project['images'])) {
                    $images = json_decode($project['images'], true);
                    
                    // Remove the image from the array
                    if (($key = array_search($imageName, $images)) !== false) {
                        unset($images[$key]);
                        
                        // Convert back to JSON and update the database
                        $imagesJson = json_encode(array_values($images));
                        
                        $stmt = $conn->prepare("UPDATE projects SET images = :images WHERE id = :id");
                        $stmt->bindParam(':images', $imagesJson);
                        $stmt->bindParam(':id', $projectId);
                        
                        if ($stmt->execute()) {
                            // Delete the file from the server
                            $filePath = '../assets/images/uploads/' . $imageName;
                            if (file_exists($filePath)) {
                                unlink($filePath);
                            }
                            
                            $_SESSION['success'] = 'Image deleted successfully.';
                        } else {
                            $_SESSION['error'] = 'Failed to delete image.';
                        }
                    }
                }
            }
            
            header('Location: project-management.php?action=edit&id=' . $projectId);
            exit;
        }
    }
}

// Get action and ID from URL
 $action = isset($_GET['action']) ? $_GET['action'] : '';
 $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get project data for editing
 $project = null;
if ($action === 'edit' && $id > 0) {
    $conn = dbConnect();
    $stmt = $conn->prepare("SELECT * FROM projects WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($project && !empty($project['images'])) {
        $project['images'] = json_decode($project['images'], true);
    } else {
        $project['images'] = [];
    }
}

// Get all projects
 $conn = dbConnect();
 $stmt = $conn->prepare("SELECT p.*, s.title as service_name FROM projects p 
                       JOIN services s ON p.service_id = s.id 
                       ORDER BY p.created_at DESC");
 $stmt->execute();
 $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all services for dropdown
 $stmt = $conn->prepare("SELECT * FROM services WHERE status = 1 ORDER BY title ASC");
 $stmt->execute();
 $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Management - Living 360 Interiors Admin</title>
    
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
                    <li><a href="project-management.php" class="active"><i class="fas fa-drafting-compass"></i> Project Management</a></li>
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
                <h1>Project Management</h1>
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
                    <!-- Add/Edit Project Form -->
                    <div class="admin-section">
                        <div class="section-header">
                            <h2><?php echo $action === 'add' ? 'Add New Project' : 'Edit Project'; ?></h2>
                            <a href="project-management.php" class="btn btn-outline">Cancel</a>
                        </div>
                        
                        <form method="post" action="project-management.php" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="<?php echo $action; ?>">
                            <?php if ($action === 'edit'): ?>
                                <input type="hidden" name="id" value="<?php echo $project['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="title">Title *</label>
                                <input type="text" id="title" name="title" required value="<?php echo $action === 'edit' ? $project['title'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description *</label>
                                <textarea id="description" name="description" required><?php echo $action === 'edit' ? $project['description'] : ''; ?></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="client_name">Client Name *</label>
                                    <input type="text" id="client_name" name="client_name" required value="<?php echo $action === 'edit' ? $project['client_name'] : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="location">Location *</label>
                                    <input type="text" id="location" name="location" required value="<?php echo $action === 'edit' ? $project['location'] : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="service_id">Service *</label>
                                <select id="service_id" name="service_id" required>
                                    <option value="">Select a service</option>
                                    <?php foreach ($services as $service): ?>
                                        <option value="<?php echo $service['id']; ?>" <?php echo ($action === 'edit' && $project['service_id'] == $service['id']) ? 'selected' : ''; ?>>
                                            <?php echo $service['title']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="images">Images</label>
                                <input type="file" id="images" name="images[]" multiple accept="image/*">
                                
                                <?php if ($action === 'edit' && !empty($project['images'])): ?>
                                    <div class="current-images">
                                        <h4>Current Images</h4>
                                        <div class="image-grid">
                                            <?php foreach ($project['images'] as $image): ?>
                                                <div class="image-item">
                                                    <img src="../assets/images/uploads/<?php echo $image; ?>" alt="Project Image">
                                                    <form method="post" action="project-management.php" class="delete-image-form">
                                                        <input type="hidden" name="action" value="delete-image">
                                                        <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                                        <input type="hidden" name="image_name" value="<?php echo $image; ?>">
                                                        <button type="submit" class="btn-icon" title="Delete Image">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label class="checkbox-option">
                                    <input type="checkbox" name="status" value="1" <?php echo ($action === 'edit' && $project['status'] == 1) ? 'checked' : ''; ?>>
                                    <span>Active</span>
                                </label>
                            </div>
                            
                            <div class="form-buttons">
                                <button type="submit" class="btn btn-primary"><?php echo $action === 'add' ? 'Add Project' : 'Update Project'; ?></button>
                                <a href="project-management.php" class="btn btn-outline">Cancel</a>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <!-- Project List -->
                    <div class="admin-section">
                        <div class="section-header">
                            <h2>All Projects</h2>
                            <a href="project-management.php?action=add" class="btn btn-primary">Add New Project</a>
                        </div>
                        
                        <div class="data-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Client</th>
                                        <th>Service</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($projects as $project): ?>
                                    <tr>
                                        <td><?php echo $project['id']; ?></td>
                                        <td><?php echo $project['title']; ?></td>
                                        <td><?php echo $project['client_name']; ?></td>
                                        <td><?php echo $project['service_name']; ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $project['status'] ? 'active' : 'inactive'; ?>">
                                                <?php echo $project['status'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="project-management.php?action=edit&id=<?php echo $project['id']; ?>" class="btn-icon" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="javascript:void(0);" class="btn-icon delete-btn" title="Delete" data-id="<?php echo $project['id']; ?>">
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
                <p>Are you sure you want to delete this project? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form method="post" action="project-management.php">
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