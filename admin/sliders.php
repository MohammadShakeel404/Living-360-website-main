<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$uploadDir = __DIR__ . '/../assets/images/uploads/';
if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0755, true); }

$conn = dbConnect();
$message = '';

function s($v){ return htmlspecialchars(trim((string)$v), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

// Handle Create/Update/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';
    try {
        if ($postAction === 'create' || $postAction === 'update') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $badge = $_POST['badge'] ?? null;
            $title = $_POST['title'] ?? null;
            $subtitle = $_POST['subtitle'] ?? null;
            $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;
            $sort   = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;

            // Handle file upload (optional)
            $imageName = null;
            if (isset($_FILES['image']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','gif','webp'];
                if (!in_array($ext, $allowed, true)) {
                    throw new Exception('Invalid image type. Allowed: JPG, JPEG, PNG, GIF, WEBP');
                }
                $safeName = 'slider_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
                $dest = rtrim($uploadDir, '/\\') . '/' . $safeName;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    $imageName = $safeName;
                } else {
                    throw new Exception('Failed to save uploaded image');
                }
            }

            if ($postAction === 'create') {
                $stmt = $conn->prepare("INSERT INTO sliders (image, badge, title, subtitle, status, sort_order) VALUES (:image, :badge, :title, :subtitle, :status, :sort_order)");
                $stmt->bindValue(':image', $imageName ?: '');
                $stmt->bindValue(':badge', $badge);
                $stmt->bindValue(':title', $title);
                $stmt->bindValue(':subtitle', $subtitle);
                $stmt->bindValue(':status', $status, PDO::PARAM_INT);
                $stmt->bindValue(':sort_order', $sort, PDO::PARAM_INT);
                $stmt->execute();
                $_SESSION['success'] = 'Slide created.';
                header('Location: sliders.php'); exit;
            } else {
                if ($id <= 0) { throw new Exception('Invalid slide id'); }
                if ($imageName) {
                    $stmt = $conn->prepare("UPDATE sliders SET image=:image, badge=:badge, title=:title, subtitle=:subtitle, status=:status, sort_order=:sort_order WHERE id=:id");
                    $stmt->bindValue(':image', $imageName);
                } else {
                    $stmt = $conn->prepare("UPDATE sliders SET badge=:badge, title=:title, subtitle=:subtitle, status=:status, sort_order=:sort_order WHERE id=:id");
                }
                $stmt->bindValue(':badge', $badge);
                $stmt->bindValue(':title', $title);
                $stmt->bindValue(':subtitle', $subtitle);
                $stmt->bindValue(':status', $status, PDO::PARAM_INT);
                $stmt->bindValue(':sort_order', $sort, PDO::PARAM_INT);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $_SESSION['success'] = 'Slide updated.';
                header('Location: sliders.php'); exit;
            }
        } elseif ($postAction === 'delete') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id > 0) {
                $stmt = $conn->prepare("DELETE FROM sliders WHERE id=:id");
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $_SESSION['success'] = 'Slide deleted.';
            }
            header('Location: sliders.php'); exit;
        }
    } catch (Throwable $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
        header('Location: sliders.php'); exit;
    }
}

// Determine action & load data
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editSlide = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $conn->prepare("SELECT * FROM sliders WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $editSlide = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch all slides for list page
try {
    $slides = $conn->query("SELECT * FROM sliders ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $slides = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sliders - Living 360 Interiors Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Afacad:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
          <li><a href="sliders.php" class="active"><i class="fas fa-images"></i> Sliders</a></li>
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

    <!-- Main -->
    <main class="admin-main">
      <header class="admin-header">
        <h1>Sliders</h1>
        <div class="admin-info">
          <span>Welcome, <?php echo s($_SESSION['admin_username'] ?? ''); ?></span>
          <a href="auth/logout.php" class="btn btn-outline">Logout</a>
        </div>
      </header>

      <div class="admin-content">
        <?php if (isset($_SESSION['success'])): ?>
          <div class="alert alert-success"><?php echo s($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
          <div class="alert alert-danger"><?php echo s($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <?php if ($action === 'add' || $action === 'edit'): ?>
          <div class="admin-section">
            <div class="section-header">
              <h2><?php echo $action === 'add' ? 'Add New Slide' : 'Edit Slide'; ?></h2>
              <a href="sliders.php" class="btn btn-outline">Cancel</a>
            </div>
            <form method="post" action="sliders.php" enctype="multipart/form-data">
              <input type="hidden" name="action" value="<?php echo $action === 'add' ? 'create' : 'update'; ?>">
              <?php if ($action === 'edit' && $editSlide): ?>
                <input type="hidden" name="id" value="<?php echo (int)$editSlide['id']; ?>">
              <?php endif; ?>

              <div class="form-row">
                <div class="form-group">
                  <label for="image">Image (JPG/PNG/WebP)</label>
                  <input type="file" id="image" name="image" accept="image/*">
                  <?php if ($action === 'edit' && !empty($editSlide['image'])): ?>
                    <div class="current-image"><img src="../assets/images/uploads/<?php echo s($editSlide['image']); ?>" alt="Current Image"></div>
                  <?php endif; ?>
                </div>
                <div class="form-group">
                  <label for="badge">Badge (small text above title)</label>
                  <input type="text" id="badge" name="badge" value="<?php echo $action==='edit' ? s($editSlide['badge']) : ''; ?>">
                </div>
              </div>

              <div class="form-group">
                <label for="title">Title (supports <br>)</label>
                <textarea id="title" name="title" rows="2"><?php echo $action==='edit' ? s($editSlide['title']) : ''; ?></textarea>
              </div>

              <div class="form-group">
                <label for="subtitle">Subtitle</label>
                <textarea id="subtitle" name="subtitle" rows="3"><?php echo $action==='edit' ? s($editSlide['subtitle']) : ''; ?></textarea>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label for="status">Status</label>
                  <select id="status" name="status">
                    <option value="1" <?php echo ($action==='edit' && (int)$editSlide['status']===1)?'selected':''; ?>>Active</option>
                    <option value="0" <?php echo ($action==='edit' && (int)$editSlide['status']===0)?'selected':''; ?>>Hidden</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="sort_order">Sort Order</label>
                  <input type="number" id="sort_order" name="sort_order" value="<?php echo $action==='edit' ? (int)$editSlide['sort_order'] : 0; ?>">
                </div>
              </div>

              <div class="form-buttons">
                <button type="submit" class="btn btn-primary"><?php echo $action==='add'?'Add Slide':'Update Slide'; ?></button>
                <a href="sliders.php" class="btn btn-outline">Cancel</a>
              </div>
            </form>
          </div>
        <?php else: ?>
          <div class="admin-section">
            <div class="section-header">
              <h2>All Slides</h2>
              <a href="sliders.php?action=add" class="btn btn-primary">Add New Slide</a>
            </div>
            <div class="data-table">
              <table>
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Badge</th>
                    <th>Title</th>
                    <th>Subtitle</th>
                    <th>Status</th>
                    <th>Sort</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                <?php if ($slides) { foreach ($slides as $s) { ?>
                  <tr>
                    <td><?php echo (int)$s['id']; ?></td>
                    <td>
                      <?php if (!empty($s['image'])) { $src = (strpos($s['image'],'http')===0||strpos($s['image'],'assets/')===0) ? $s['image'] : ('../assets/images/uploads/'.$s['image']); ?>
                        <img style="width:100px;height:60px;object-fit:cover;border-radius:6px;border:1px solid var(--medium-gray);" src="<?php echo s($src); ?>" alt="thumb">
                      <?php } ?>
                    </td>
                    <td><?php echo s($s['badge']); ?></td>
                    <td><?php echo $s['title']; ?></td>
                    <td><?php echo s(mb_strimwidth(strip_tags($s['subtitle']),0,70,'…')); ?></td>
                    <td><span class="status-badge <?php echo ((int)$s['status']===1?'status-active':'status-inactive'); ?>"><?php echo ((int)$s['status']===1?'Active':'Hidden'); ?></span></td>
                    <td><?php echo (int)$s['sort_order']; ?></td>
                    <td>
                      <a class="btn-icon" href="sliders.php?action=edit&id=<?php echo (int)$s['id']; ?>" title="Edit"><i class="fas fa-edit"></i></a>
                      <form method="post" action="sliders.php" style="display:inline" onsubmit="return confirm('Delete this slide?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>">
                        <button type="submit" class="btn-icon" title="Delete"><i class="fas fa-trash"></i></button>
                      </form>
                    </td>
                  </tr>
                <?php } } else { ?>
                  <tr><td colspan="8">No slides found.</td></tr>
                <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="../assets/js/admin.js"></script>
</body>
</html>
