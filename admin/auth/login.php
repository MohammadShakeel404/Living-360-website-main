<?php
session_start();
require_once '../../includes/functions.php';

// If admin is already logged in, redirect to dashboard
if (isAdminLoggedIn()) {
    header('Location: ../dashboard.php');
    exit;
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? sanitizeInput($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $conn = dbConnect();
        $stmt = $conn->prepare("SELECT * FROM admin WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin && password_verify($password, $admin['password'])) {
            // Set session variables
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            
            // Redirect to dashboard
            header('Location: ../dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Living 360 Interiors</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Afacad:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body class="login-page">
    <div class="auth-wrap">
        <div class="auth-card">
            <div class="auth-left">
                <img src="../../assets/images/logo.png" alt="Living 360 Interiors" class="brand-mark">
                <div class="brand-name">Living 360 Interiors</div>
            </div>
            <div class="auth-divider" aria-hidden="true"></div>
            <div class="auth-right" role="form" aria-labelledby="adminLoginTitle">
                <h2 id="adminLoginTitle" class="welcome-title">Welcome</h2>
                <p class="welcome-sub">Please login to Admin Dashboard.</p>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="post" action="">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required placeholder="Username">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-field">
                            <input type="password" id="password" name="password" required aria-describedby="loginHelp" placeholder="Password">
                            <button type="button" class="toggle-pass" aria-label="Show password"><i class="fa fa-eye"></i></button>
                        </div>
                        <small id="loginHelp" class="form-hint">Use your administrator credentials to sign in.</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-login">Login</button>
                        <a href="#" class="forgot-link">Forgotten your password?</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
      (function(){
        const btn = document.querySelector('.toggle-pass');
        const input = document.getElementById('password');
        if(btn && input){
          btn.addEventListener('click', function(){
            const isPwd = input.getAttribute('type') === 'password';
            input.setAttribute('type', isPwd ? 'text' : 'password');
            this.innerHTML = isPwd ? '<i class="fa fa-eye-slash"></i>' : '<i class="fa fa-eye"></i>';
            this.setAttribute('aria-label', isPwd ? 'Hide password' : 'Show password');
          });
        }
      })();
    </script>
</body>
</html>