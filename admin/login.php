<?php
require_once __DIR__ . '/../includes/config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    redirect(BASE_URL . 'admin/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    $result = $conn->query("SELECT * FROM users WHERE username = '$username'");
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (hash('sha256', $password) === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            redirect(BASE_URL . 'admin/dashboard.php');
        } else {
            $error = 'Username atau password salah!';
        }
    } else {
        $error = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Sistem Pengaduan Masyarakat</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/login.css">
    <script src="../assets/js/modal.js"></script>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <div class="login-icon">🔐</div>
                <h1 class="login-title">Admin Panel</h1>
                <p class="login-subtitle">Sistem Pengaduan Masyarakat</p>
            </div>

            <?php if ($error): ?>
                <div id="errorNotification" style="display: none;"></div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showDangerModal('Login Gagal', <?php echo json_encode($error); ?>);
                    });
                </script>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required 
                           placeholder="Masukkan username" autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Masukkan password">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.875rem;">Login</button>
            </form>

            <div class="login-demo">
                <p>
                    <strong>Demo Account:</strong><br>
                    Username: admin<br>
                    Password: admin123
                </p>
            </div>
        </div>
    </div>
</body>
</html>
