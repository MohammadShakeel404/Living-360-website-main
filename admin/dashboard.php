<?php
session_start();
require_once '../includes/functions.php';

// Check if admin is logged in
requireAdminLogin();

// Get statistics
 $conn = dbConnect();

// Total enquiries
 $stmt = $conn->prepare("SELECT COUNT(*) as total FROM enquiries");
 $stmt->execute();
 $totalEnquiries = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// New enquiries
 $stmt = $conn->prepare("SELECT COUNT(*) as total FROM enquiries WHERE status = 'new'");
 $stmt->execute();
 $newEnquiries = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total projects
 $stmt = $conn->prepare("SELECT COUNT(*) as total FROM projects");
 $stmt->execute();
 $totalProjects = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total blogs
 $stmt = $conn->prepare("SELECT COUNT(*) as total FROM blogs");
 $stmt->execute();
 $totalBlogs = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Recent enquiries
 $stmt = $conn->prepare("SELECT * FROM enquiries ORDER BY created_at DESC LIMIT 5");
 $stmt->execute();
 $recentEnquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Living 360 Interiors Admin</title>
    
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
                    <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="sliders.php"><i class="fas fa-images"></i> Sliders</a></li>
                    <li><a href="blog-management.php"><i class="fas fa-blog"></i> Blog Management</a></li>
                    <li><a href="service-management.php"><i class="fas fa-concierge-bell"></i> Service Management</a></li>
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
                <h1>Dashboard</h1>
                <div class="admin-info">
                    <span>Welcome, <?php echo $_SESSION['admin_username']; ?></span>
                    <a href="auth/logout.php" class="btn btn-outline">Logout</a>
                </div>
            </header>
            
            <div class="admin-content">
                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $totalEnquiries; ?></h3>
                            <p>Total Enquiries</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-envelope-open-text"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $newEnquiries; ?></h3>
                            <p>New Enquiries</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-drafting-compass"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $totalProjects; ?></h3>
                            <p>Total Projects</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-blog"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $totalBlogs; ?></h3>
                            <p>Total Blogs</p>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Enquiries -->
                <div class="admin-section">
                    <div class="section-header">
                        <h2>Recent Enquiries</h2>
                        <a href="enquiry-management.php" class="btn btn-outline">View All</a>
                    </div>
                    
                    <div class="data-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Project Type</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentEnquiries as $enquiry): ?>
                                <tr>
                                    <td><?php echo $enquiry['id']; ?></td>
                                    <td><?php echo $enquiry['name']; ?></td>
                                    <td><?php echo $enquiry['email']; ?></td>
                                    <td><?php echo $enquiry['project_type']; ?></td>
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
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>