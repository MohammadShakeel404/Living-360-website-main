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
            // Preserve TinyMCE HTML content; only trim
            $content = isset($_POST['content']) ? trim($_POST['content']) : '';
            $excerpt = isset($_POST['excerpt']) ? sanitizeInput($_POST['excerpt']) : '';
            $author = isset($_POST['author']) ? sanitizeInput($_POST['author']) : '';
            $status = isset($_POST['status']) ? 1 : 0;

            // Basic server-side validation for required fields
            if ($title === '' || $excerpt === '' || $author === '' || $content === '') {
                $_SESSION['error'] = 'Please fill in all required fields: Title, Excerpt, Author, and Content.';
                if ($action === 'edit' && $id > 0) {
                    header('Location: blog-management.php?action=edit&id=' . $id);
                } else {
                    header('Location: blog-management.php?action=add');
                }
                exit;
            }
            
            // Handle featured image upload
            $featured_image = '';
            if (isset($_FILES['featured_image']) && is_array($_FILES['featured_image']) && $_FILES['featured_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES['featured_image']['error'] !== UPLOAD_ERR_OK) {
                    $_SESSION['error'] = 'Image upload failed. Please try again.';
                    if ($action === 'edit' && $id > 0) {
                        header('Location: blog-management.php?action=edit&id=' . $id);
                    } else {
                        header('Location: blog-management.php?action=add');
                    }
                    exit;
                }
                $uploadDir = '../assets/images/uploads/';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                }
                $original = basename($_FILES['featured_image']['name']);
                $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','gif','webp'];
                if (!in_array($ext, $allowed, true)) {
                    $_SESSION['error'] = 'Invalid image type. Allowed: JPG, JPEG, PNG, GIF, WEBP.';
                    if ($action === 'edit' && $id > 0) {
                        header('Location: blog-management.php?action=edit&id=' . $id);
                    } else {
                        header('Location: blog-management.php?action=add');
                    }
                    exit;
                }
                if (!empty($_FILES['featured_image']['size']) && $_FILES['featured_image']['size'] > 5 * 1024 * 1024) {
                    $_SESSION['error'] = 'Image too large. Max size 5MB.';
                    if ($action === 'edit' && $id > 0) {
                        header('Location: blog-management.php?action=edit&id=' . $id);
                    } else {
                        header('Location: blog-management.php?action=add');
                    }
                    exit;
                }
                $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $original);
                $targetFile = rtrim($uploadDir, '/\\') . '/' . $safeName;
                if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $targetFile)) {
                    $featured_image = $safeName;
                } else {
                    $_SESSION['error'] = 'Failed to save uploaded image.';
                    if ($action === 'edit' && $id > 0) {
                        header('Location: blog-management.php?action=edit&id=' . $id);
                    } else {
                        header('Location: blog-management.php?action=add');
                    }
                    exit;
                }
            } elseif ($action === 'edit' && empty($featured_image)) {
                // Keep existing image if not uploading a new one
                $conn = dbConnect();
                $stmt = $conn->prepare("SELECT featured_image FROM blogs WHERE id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $blog = $stmt->fetch(PDO::FETCH_ASSOC);
                $featured_image = $blog['featured_image'];
            }
            
            // Create slug from title
            $slug = createSlug($title);
            
            // Check if slug already exists
            $conn = dbConnect();
            $stmt = $conn->prepare("SELECT id FROM blogs WHERE slug = :slug AND id != :id");
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // Append random number to make slug unique
                $slug .= '-' . rand(1000, 9999);
            }
            
            if ($action === 'add') {
                // Add new blog
                $stmt = $conn->prepare("INSERT INTO blogs (title, content, excerpt, featured_image, author, slug, status, created_at, updated_at) 
                                       VALUES (:title, :content, :excerpt, :featured_image, :author, :slug, :status, NOW(), NOW())");
                
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':content', $content);
                $stmt->bindParam(':excerpt', $excerpt);
                $stmt->bindParam(':featured_image', $featured_image);
                $stmt->bindParam(':author', $author);
                $stmt->bindParam(':slug', $slug);
                $stmt->bindParam(':status', $status);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = 'Blog added successfully.';
                } else {
                    $err = $stmt->errorInfo();
                    $_SESSION['error'] = 'Failed to add blog. ' . (!empty($err[2]) ? $err[2] : '');
                    // Additional debugging
                    error_log('Blog insert failed: ' . print_r($err, true));
                    error_log('Data being inserted: ' . print_r([
                        'title' => $title,
                        'content' => substr($content, 0, 100) . '...',
                        'excerpt' => $excerpt,
                        'featured_image' => $featured_image,
                        'author' => $author,
                        'slug' => $slug,
                        'status' => $status
                    ], true));
                }
            } else {
                // Update existing blog
                $stmt = $conn->prepare("UPDATE blogs SET 
                                       title = :title, 
                                       content = :content, 
                                       excerpt = :excerpt, 
                                       featured_image = :featured_image, 
                                       author = :author, 
                                       slug = :slug, 
                                       status = :status,
                                       updated_at = NOW() 
                                       WHERE id = :id");
                
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':content', $content);
                $stmt->bindParam(':excerpt', $excerpt);
                $stmt->bindParam(':featured_image', $featured_image);
                $stmt->bindParam(':author', $author);
                $stmt->bindParam(':slug', $slug);
                $stmt->bindParam(':status', $status);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = 'Blog updated successfully.';
                } else {
                    $err = $stmt->errorInfo();
                    $_SESSION['error'] = 'Failed to update blog. ' . (!empty($err[2]) ? $err[2] : '');
                }
            }
            
            header('Location: blog-management.php');
            exit;
        } elseif ($action === 'delete') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            
            if ($id > 0) {
                $conn = dbConnect();
                $stmt = $conn->prepare("DELETE FROM blogs WHERE id = :id");
                $stmt->bindParam(':id', $id);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = 'Blog deleted successfully.';
                } else {
                    $_SESSION['error'] = 'Failed to delete blog.';
                }
            }
            
            header('Location: blog-management.php');
            exit;
        }
    }
}

// Get action and ID from URL
 $action = isset($_GET['action']) ? $_GET['action'] : '';
 $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get blog data for editing
 $blog = null;
if ($action === 'edit' && $id > 0) {
    $conn = dbConnect();
    $stmt = $conn->prepare("SELECT * FROM blogs WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $blog = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get all blogs
 $conn = dbConnect();
 $stmt = $conn->prepare("SELECT * FROM blogs ORDER BY created_at DESC");
 $stmt->execute();
 $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Management - Living 360 Interiors Admin</title>
    
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
                    <li><a href="blog-management.php" class="active"><i class="fas fa-blog"></i> Blog Management</a></li>
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
                <h1>Blog Management</h1>
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
                    <!-- Add/Edit Blog Form -->
                    <div class="admin-section">
                        <div class="section-header">
                            <h2><?php echo $action === 'add' ? 'Add New Blog' : 'Edit Blog'; ?></h2>
                            <a href="blog-management.php" class="btn btn-outline">Cancel</a>
                        </div>
                        
                        <form method="post" action="blog-management.php" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="<?php echo $action; ?>">
                            <?php if ($action === 'edit'): ?>
                                <input type="hidden" name="id" value="<?php echo $blog['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="title">Title *</label>
                                <input type="text" id="title" name="title" required value="<?php echo $action === 'edit' ? $blog['title'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="excerpt">Excerpt *</label>
                                <textarea id="excerpt" name="excerpt" required><?php echo $action === 'edit' ? $blog['excerpt'] : ''; ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="author">Author *</label>
                                <input type="text" id="author" name="author" required value="<?php echo $action === 'edit' ? $blog['author'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="featured_image">Featured Image</label>
                                <input type="file" id="featured_image" name="featured_image" accept="image/*">
                                <?php if ($action === 'edit' && !empty($blog['featured_image'])): ?>
                                    <div class="current-image">
                                        <img src="../assets/images/uploads/<?php echo $blog['featured_image']; ?>" alt="Current Image">
                                        <p>Current Image</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="content">Content *</label>
                                <textarea id="content" name="content"><?php echo $action === 'edit' ? $blog['content'] : ''; ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label class="checkbox-option">
                                    <input type="checkbox" name="status" value="1" <?php echo ($action === 'edit' && $blog['status'] == 1) ? 'checked' : ''; ?>>
                                    <span>Publish</span>
                                </label>
                            </div>
                            
                            <div class="form-buttons">
                                <button type="submit" class="btn btn-primary"><?php echo $action === 'add' ? 'Add Blog' : 'Update Blog'; ?></button>
                                <a href="blog-management.php" class="btn btn-outline">Cancel</a>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <!-- Blog List -->
                    <div class="admin-section">
                        <div class="section-header">
                            <h2>All Blogs</h2>
                            <a href="blog-management.php?action=add" class="btn btn-primary">Add New Blog</a>
                        </div>
                        
                        <div class="data-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($blogs as $blog): ?>
                                    <tr>
                                        <td><?php echo $blog['id']; ?></td>
                                        <td><?php echo $blog['title']; ?></td>
                                        <td><?php echo $blog['author']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($blog['created_at'])); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $blog['status'] ? 'active' : 'inactive'; ?>">
                                                <?php echo $blog['status'] ? 'Published' : 'Draft'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="blog-management.php?action=edit&id=<?php echo $blog['id']; ?>" class="btn-icon" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="javascript:void(0);" class="btn-icon delete-btn" title="Delete" data-id="<?php echo $blog['id']; ?>">
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
                <p>Are you sure you want to delete this blog? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form method="post" action="blog-management.php">
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
    <script>
        // Initialize TinyMCE
        tinymce.init({
            selector: '#content',
            height: 500,
            plugins: 'advlist autolink lists link image charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount',
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
            content_style: 'body { font-family: \'Afacad\', sans-serif; font-size: 14px }'
        });
    </script>
</body>