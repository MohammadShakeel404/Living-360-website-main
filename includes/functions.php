<?php
// Database connection
// Holds the last database error for debugging
$GLOBALS['LAST_DB_ERROR'] = null;

function dbConnect() {
    $host = 'localhost';
    $db_name = 'u934543595_living360';
    $username = 'u934543595_living360';
    $password = 'Living@360@#';
    
    try {
        $conn = new PDO("mysql:host={$host};dbname={$db_name};charset=utf8mb4", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $exception) {
        $GLOBALS['LAST_DB_ERROR'] = $exception->getMessage();
        error_log('DB connection error: ' . $exception->getMessage());
    }
}

// Get setting value
function getSetting($key) {
    $conn = dbConnect();
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = :key");
    $stmt->bindParam(':key', $key);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['setting_value'] : '';
}

// Get active services
function getActiveServices() {
    $conn = dbConnect();
    $stmt = $conn->prepare("SELECT * FROM services WHERE status = 1 ORDER BY id ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get service by ID
function getServiceById($id) {
    $conn = dbConnect();
    $stmt = $conn->prepare("SELECT * FROM services WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get active projects
function getActiveProjects($limit = null) {
    $conn = dbConnect();
    $query = "SELECT p.*, s.title as service_name FROM projects p 
              JOIN services s ON p.service_id = s.id 
              WHERE p.status = 1 ORDER BY p.id DESC";
    
    if ($limit) {
        $query .= " LIMIT " . (int)$limit;
    }
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get project by ID
function getProjectById($id) {
    $conn = dbConnect();
    $stmt = $conn->prepare("SELECT p.*, s.title as service_name FROM projects p 
                           JOIN services s ON p.service_id = s.id 
                           WHERE p.id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get active blogs
function getActiveBlogs($limit = null) {
    $conn = dbConnect();
    $query = "SELECT * FROM blogs WHERE status = 1 ORDER BY created_at DESC";
    
    if ($limit) {
        $query .= " LIMIT " . (int)$limit;
    }
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get blog by slug
function getBlogBySlug($slug) {
    $conn = dbConnect();
    $stmt = $conn->prepare("SELECT * FROM blogs WHERE slug = :slug");
    $stmt->bindParam(':slug', $slug);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get active offer
function getActiveOffer() {
    $conn = dbConnect();
    $stmt = $conn->prepare("SELECT * FROM offers WHERE active = 1 ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Create enquiry
function createEnquiry($data) {
    try {
        $conn = dbConnect();
        // Discover available columns in the enquiries table
        $columns = getTableColumns($conn, 'enquiries');
        $available = array_change_key_case(array_flip($columns), CASE_LOWER);

        // Coerce optional inputs to NULL when empty
        $status     = isset($data['status']) ? $data['status'] : 'new';
        $newsletter = isset($data['newsletter']) ? (int)$data['newsletter'] : 0;
        $phone      = (isset($data['phone']) && $data['phone'] !== '') ? $data['phone'] : null;
        $space_size = (isset($data['space_size']) && $data['space_size'] !== '') ? $data['space_size'] : null;
        $referral   = (isset($data['referral']) && $data['referral'] !== '') ? $data['referral'] : null;

        // Map of possible fields => value providers
        $fieldValues = [
            'name'         => $data['name'] ?? null,
            'email'        => $data['email'] ?? null,
            'phone'        => $phone,
            'project_type' => $data['project_type'] ?? null,
            'space_size'   => $space_size,
            'budget'       => $data['budget'] ?? null,
            'timeline'     => $data['timeline'] ?? null,
            'message'      => $data['message'] ?? null,
            'referral'     => $referral,
            'newsletter'   => $newsletter,
            'status'       => $status,
        ];

        $cols = [];
        $placeholders = [];
        $binds = [];
        foreach ($fieldValues as $col => $val) {
            if (isset($available[strtolower($col)])) {
                $cols[] = $col;
                $placeholders[] = ':' . $col;
                $binds[$col] = $val;
            }
        }

        // Handle created_at as NOW() if column exists
        $includeCreatedAt = isset($available['created_at']);
        if ($includeCreatedAt) {
            $cols[] = 'created_at';
        }

        if (empty($cols)) {
            throw new Exception('No matching columns found in enquiries table.');
        }

        $sql = 'INSERT INTO enquiries (' . implode(', ', $cols) . ') VALUES (';
        $sql .= implode(', ', $placeholders);
        if ($includeCreatedAt) {
            // Replace last placeholder for created_at with NOW()
            // Since created_at was appended after placeholders, add NOW() separately
            $sql .= (empty($placeholders) ? '' : ', ') . 'NOW()';
        }
        $sql .= ')';

        // If created_at is included, we added NOW() not a placeholder, so remove its placeholder counterpart
        if ($includeCreatedAt) {
            // We appended created_at without placeholder; do not add to binds
        }

        $stmt = $conn->prepare($sql);

        // Bind values dynamically
        foreach ($binds as $bCol => $bVal) {
            $param = ':' . $bCol;
            if ($bVal === null) {
                $stmt->bindValue($param, null, PDO::PARAM_NULL);
            } elseif ($bCol === 'newsletter') {
                $stmt->bindValue($param, (int)$bVal, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($param, $bVal, PDO::PARAM_STR);
            }
        }

        if ($stmt->execute()) {
            return $conn->lastInsertId();
        }
        $err = $stmt->errorInfo();
        error_log('createEnquiry execute failed: ' . print_r($err, true));
        $GLOBALS['LAST_DB_ERROR'] = $err;
        error_log('createEnquiry data snapshot: ' . print_r([
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'project_type' => $data['project_type'] ?? null,
            'space_size' => $space_size,
            'budget' => $data['budget'] ?? null,
            'timeline' => $data['timeline'] ?? null,
            'message' => isset($data['message']) ? substr($data['message'], 0, 100) . '...' : null,
            'referral' => $referral,
            'newsletter' => $newsletter,
            'status' => $status,
        ], true));
        return false;
    } catch (Throwable $e) {
        error_log('createEnquiry exception: ' . $e->getMessage());
        $GLOBALS['LAST_DB_ERROR'] = $e->getMessage();
        return false;
    }
}

// Get column names for a table from INFORMATION_SCHEMA for the current database
function getTableColumns(PDO $conn, $tableName) {
    try {
        $stmt = $conn->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t");
        $stmt->bindParam(':t', $tableName);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        return is_array($rows) ? $rows : [];
    } catch (Throwable $e) {
        error_log('getTableColumns error: ' . $e->getMessage());
        return [];
    }
}

// For debugging: get last DB error captured
function getLastDbError() {
    return $GLOBALS['LAST_DB_ERROR'] ?? null;
}

// Send email notification
function sendEmailNotification($to, $subject, $message) {
    $headers = "From: design@living360.in\r\n";
    $headers .= "Reply-To: design@living360.in\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    return mail($to, $subject, $message, $headers);
}

// Sanitize input
function sanitizeInput($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    return $input;
}

// Check if admin is logged in
function isAdminLoggedIn() {
    if (session_status() === PHP_SESSION_NONE) {
        // Attempt to start session if not already started
        @session_start();
    }
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Require admin login (redirects to admin login if not authenticated)
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        // Build path relative to the executing script (e.g., /admin/dashboard.php -> /admin/auth/login.php)
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        $loginPath = $base . '/auth/login.php';
        header('Location: ' . $loginPath);
        exit;
    }
}

// Create URL-friendly slug from text
function createSlug($text) {
    $text = trim((string)$text);
    // Convert to lowercase UTF-8 safe
    $text = mb_strtolower($text, 'UTF-8');
    // Replace non letters/digits with hyphens
    $text = preg_replace('/[^\p{L}\p{Nd}]+/u', '-', $text);
    // Trim hyphens
    $text = trim($text, '-');
    // Collapse multiple hyphens
    $text = preg_replace('/-+/', '-', $text);
    // Fallback if empty
    if ($text === '' || $text === null) {
        $text = 'item-' . time();
    }
    return $text;
}

// Get active sliders with safe fallback
function getActiveSliders() {
    try {
        $conn = dbConnect();
        if (!$conn) { throw new Exception('No DB connection'); }
        $stmt = $conn->prepare("SELECT id, image, badge, title, subtitle, status, sort_order FROM sliders WHERE status = 1 ORDER BY sort_order ASC, id ASC");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($rows && count($rows) > 0) {
            return $rows;
        }
    } catch (Throwable $e) {
        error_log('getActiveSliders fallback: ' . $e->getMessage());
    }
    return [[
        'id' => 1,
        'image' => 'assets/images/about-image.jpg',
        'badge' => 'Professional Interior Design',
        'title' => 'Transform Your Space with<br> Living 360 Interiors',
        'subtitle' => 'We create beautiful, functional spaces that inspire and delight. From residential homes to commercial spaces, we bring your vision to life.',
        'status' => 1,
        'sort_order' => 1,
    ]];
}
?>