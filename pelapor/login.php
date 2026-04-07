<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['pelapor_id'])) {
    redirect(BASE_URL . 'pelapor/dashboard.php');
}

$error = '';
$active_tab = isset($_GET['tab']) && $_GET['tab'] === 'register' ? 'register' : 'login';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $result = loginPelapor($_POST['email'], $_POST['password']);
    
    if ($result['success']) {
        $_SESSION['pelapor_id'] = $result['user']['id'];
        $_SESSION['pelapor_email'] = $result['user']['email'];
        $_SESSION['pelapor_nama'] = $result['user']['nama_lengkap'];
        redirect(BASE_URL . 'pelapor/dashboard.php');
    } else {
        $error = $result['error'];
        $active_tab = 'login';
    }
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $required = ['nama', 'email', 'password', 'password_confirm', 'telepon'];
    $all_filled = true;
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $all_filled = false;
            break;
        }
    }
    
    if (!$all_filled) {
        $error = 'Semua field yang wajib harus diisi!';
        $active_tab = 'register';
    } elseif ($_POST['password'] !== $_POST['password_confirm']) {
        $error = 'Password tidak cocok!';
        $active_tab = 'register';
    } elseif (strlen($_POST['password']) < 6) {
        $error = 'Password minimal 6 karakter!';
        $active_tab = 'register';
    } else {
        $result = registerPelapor($_POST);
        if ($result['success']) {
            $success_msg = 'Registrasi berhasil! Silakan login.';
            $active_tab = 'login';
        } else {
            $error = $result['error'];
            $active_tab = 'register';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Sistem Pengaduan Masyarakat</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="../assets/css/pelapor.css">
    <script src="../assets/js/modal.js"></script>
</head>
<body>
    <div class="login-container">
        <div class="login-box large">
            <div class="login-header">
                <div class="login-icon">📋</div>
                <h1 class="login-title">Pelapor Pengaduan</h1>
                <p class="login-subtitle">Sistem Pengaduan Masyarakat</p>
            </div>

            <?php if (!empty($error)): ?>
                <div id="errorNotification" style="display: none;"></div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showDangerModal('Error', <?php echo json_encode($error); ?>);
                    });
                </script>
            <?php endif; ?>

            <?php if (isset($success_msg) && !empty($success_msg)): ?>
                <div id="successNotification" style="display: none;"></div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showSuccessModal('Berhasil', <?php echo json_encode($success_msg); ?>);
                    });
                </script>
            <?php endif; ?>

            <!-- Tab Navigation -->
            <div class="tab-navigation">
                <button class="tab-btn <?php echo $active_tab === 'login' ? 'active' : ''; ?>" 
                        onclick="switchTab('login')">Login</button>
                <button class="tab-btn <?php echo $active_tab === 'register' ? 'active' : ''; ?>" 
                        onclick="switchTab('register')">Daftar</button>
            </div>

            <!-- Login Tab -->
            <div id="login-tab" class="tab-content <?php echo $active_tab === 'login' ? 'active' : ''; ?>">
                <form method="POST">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="form-group">
                        <label for="login-email">Email</label>
                        <input type="email" id="login-email" name="email" required 
                               placeholder="Masukkan email" autofocus>
                    </div>

                    <div class="form-group">
                        <label for="login-password">Password</label>
                        <input type="password" id="login-password" name="password" required 
                               placeholder="Masukkan password">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.875rem;">Login</button>
                </form>

                <div class="login-footer">
                    <p>Belum punya akun? <button type="button" onclick="switchTab('register')" class="link-btn">Daftar di sini</button></p>
                </div>
            </div>

            <!-- Register Tab -->
            <div id="register-tab" class="tab-content <?php echo $active_tab === 'register' ? 'active' : ''; ?>">
                <form method="POST">
                    <input type="hidden" name="action" value="register">
                    
                    <div class="form-group">
                        <label for="register-nama">Nama Lengkap *</label>
                        <input type="text" id="register-nama" name="nama" required 
                               placeholder="Masukkan nama lengkap"
                               value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="register-email">Email *</label>
                        <input type="email" id="register-email" name="email" required 
                               placeholder="Masukkan email"
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="register-telepon">Nomor Telepon *</label>
                        <input type="tel" id="register-telepon" name="telepon" required 
                               placeholder="Masukkan nomor telepon"
                               value="<?php echo isset($_POST['telepon']) ? htmlspecialchars($_POST['telepon']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="register-kota">Kota (Opsional)</label>
                        <input type="text" id="register-kota" name="kota" 
                               placeholder="Masukkan kota/kabupaten"
                               value="<?php echo isset($_POST['kota']) ? htmlspecialchars($_POST['kota']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="register-password">Password *</label>
                        <input type="password" id="register-password" name="password" required 
                               placeholder="Minimal 6 karakter">
                    </div>

                    <div class="form-group">
                        <label for="register-password-confirm">Konfirmasi Password *</label>
                        <input type="password" id="register-password-confirm" name="password_confirm" required 
                               placeholder="Ulangi password">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.875rem;">Daftar</button>
                </form>

                <div class="login-footer">
                    <p>Sudah punya akun? <button type="button" onclick="switchTab('login')" class="link-btn">Login di sini</button></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(el => {
                el.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(el => {
                el.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tab + '-tab').classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
