<?php
session_start();
require_once '../includes/functions.php';

// Check if admin is logged in
requireAdminLogin();

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'update-status') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : '';
            
            if ($id > 0 && !empty($status)) {
                $conn = dbConnect();
                $stmt = $conn->prepare("UPDATE enquiries SET status = :status WHERE id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':status', $status);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = 'Enquiry status updated successfully.';
                } else {
                    $_SESSION['error'] = 'Failed to update enquiry status.';
                }
            }
            
            header('Location: enquiry-management.php');
            exit;
        } elseif ($action === 'delete') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            
            if ($id > 0) {
                $conn = dbConnect();
                $stmt = $conn->prepare("DELETE FROM enquiries WHERE id = :id");
                $stmt->bindParam(':id', $id);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = 'Enquiry deleted successfully.';
                } else {
                    $_SESSION['error'] = 'Failed to delete enquiry.';
                }
            }
            
            header('Location: enquiry-management.php');
            exit;
        }
    }
}

// Get action and ID from URL
 $action = isset($_GET['action']) ? $_GET['action'] : '';
 $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get enquiry data for viewing
 $enquiry = null;
if ($action === 'view' && $id > 0) {
    $conn = dbConnect();
    $stmt = $conn->prepare("SELECT * FROM enquiries WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $enquiry = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get all enquiries
 $conn = dbConnect();
 $stmt = $conn->prepare("SELECT * FROM enquiries ORDER BY created_at DESC");
 $stmt->execute();
 $enquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get status counts
 $stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM enquiries GROUP BY status");
 $stmt->execute();
 $statusCounts = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $statusCounts[$row['status']] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enquiry Management - Living 360 Interiors Admin</title>
    
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
                    <li><a href="offer-management.php"><i class="fas fa-tags"></i> Offer Management</a></li>
                    <li><a href="enquiry-management.php" class="active"><i class="fas fa-envelope"></i> Enquiry Management</a></li>
                    <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <h1>Enquiry Management</h1>
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
                
                <?php if ($action === 'view' && $enquiry): ?>
                    <!-- View Enquiry -->
                    <div class="admin-section">
                        <div class="section-header">
                            <h2>Enquiry Details</h2>
                            <a href="enquiry-management.php" class="btn btn-outline">Back to List</a>
                        </div>
                        
                        <div class="enquiry-details">
                            <div class="detail-row">
                                <div class="detail-label">ID:</div>
                                <div class="detail-value"><?php echo $enquiry['id']; ?></div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Name:</div>
                                <div class="detail-value"><?php echo $enquiry['name']; ?></div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Email:</div>
                                <div class="detail-value"><?php echo $enquiry['email']; ?></div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Phone:</div>
                                <div class="detail-value"><?php echo $enquiry['phone'] ?: 'Not provided'; ?></div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Project Type:</div>
                                <div class="detail-value"><?php echo ucfirst($enquiry['project_type']); ?></div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Budget:</div>
                                <div class="detail-value"><?php echo str_replace('-', ' to ', $enquiry['budget']); ?></div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Timeline:</div>
                                <div class="detail-value"><?php echo str_replace('-', ' to ', $enquiry['timeline']); ?></div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Message:</div>
                                <div class="detail-value"><?php echo $enquiry['message'] ?: 'No message provided'; ?></div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Status:</div>
                                <div class="detail-value">
                                    <form method="post" action="enquiry-management.php">
                                        <input type="hidden" name="action" value="update-status">
                                        <input type="hidden" name="id" value="<?php echo $enquiry['id']; ?>">
                                        
                                        <select name="status" onchange="this.form.submit()">
                                            <option value="new" <?php echo $enquiry['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                                            <option value="contacted" <?php echo $enquiry['status'] === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                                            <option value="converted" <?php echo $enquiry['status'] === 'converted' ? 'selected' : ''; ?>>Converted</option>
                                            <option value="closed" <?php echo $enquiry['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                                        </select>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Date:</div>
                                <div class="detail-value"><?php echo date('F d, Y H:i', strtotime($enquiry['created_at'])); ?></div>
                            </div>
                            
                            <div class="detail-actions">
                                <a href="mailto:<?php echo $enquiry['email']; ?>" class="btn btn-primary">
                                    <i class="fas fa-envelope"></i> Send Email
                                </a>
                                
                                <form method="post" action="enquiry-management.php" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $enquiry['id']; ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this enquiry?');">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Enquiry List -->
                    <div class="admin-section">
                        <div class="section-header">
                            <h2>All Enquiries</h2>
                            <div class="status-summary">
                                <div class="status-item">
                                    <span class="status-count"><?php echo isset($statusCounts['new']) ? $statusCounts['new'] : '0'; ?></span>
                                    <span class="status-label">New</span>
                                </div>
                                <div class="status-item">
                                    <span class="status-count"><?php echo isset($statusCounts['contacted']) ? $statusCounts['contacted'] : '0'; ?></span>
                                    <span class="status-label">Contacted</span>
                                </div>
                                <div class="status-item">
                                    <span class="status-count"><?php echo isset($statusCounts['converted']) ? $statusCounts['converted'] : '0'; ?></span>
                                    <span class="status-label">Converted</span>
                                </div>
                                <div class="status-item">
                                    <span class="status-count"><?php echo isset($statusCounts['closed']) ? $statusCounts['closed'] : '0'; ?></span>
                                    <span class="status-label">Closed</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="data-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Project Type</th>
                                        <th>Budget</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($enquiries as $enquiry): ?>
                                    <tr>
                                        <td><?php echo $enquiry['id']; ?></td>
                                        <td><?php echo $enquiry['name']; ?></td>
                                        <td><?php echo $enquiry['email']; ?></td>
                                        <td><?php echo ucfirst($enquiry['project_type']); ?></td>
                                        <td><?php echo str_replace('-', ' to ', $enquiry['budget']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $enquiry['status']; ?>">
                                                <?php echo ucfirst($enquiry['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($enquiry['created_at'])); ?></td>
                                        <td>
                                            <a href="enquiry-management.php?action=view&id=<?php echo $enquiry['id']; ?>" class="btn-icon" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="mailto:<?php echo $enquiry['email']; ?>" class="btn-icon" title="Email">
                                                <i class="fas fa-envelope"></i>
                                            </a>
                                            <a href="javascript:void(0);" class="btn-icon delete-btn" title="Delete" data-id="<?php echo $enquiry['id']; ?>">
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
                <p>Are you sure you want to delete this enquiry? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form method="post" action="enquiry-management.php">
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