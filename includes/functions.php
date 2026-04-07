<?php
require_once __DIR__ . '/config.php';

// Check if user is logged in (for admin)
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        redirect(BASE_URL . 'admin/login.php');
    }
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Get user info
function getUserInfo($user_id) {
    global $conn;
    $result = $conn->query("SELECT * FROM users WHERE id = $user_id");
    return $result ? $result->fetch_assoc() : null;
}

// Get all categories
function getCategories() {
    global $conn;
    $result = $conn->query("SELECT * FROM kategori_pengaduan ORDER BY nama_kategori");
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

// Get single pengaduan
function getPengaduan($id) {
    global $conn;
    $query = "SELECT p.*, k.nama_kategori FROM pengaduan p 
              LEFT JOIN kategori_pengaduan k ON p.kategori_id = k.id 
              WHERE p.id = $id";
    $result = $conn->query($query);
    return $result ? $result->fetch_assoc() : null;
}

// Get bukti pendukung for pengaduan
function getBuktiPendukung($pengaduan_id) {
    global $conn;
    $query = "SELECT * FROM bukti_pendukung WHERE pengaduan_id = $pengaduan_id ORDER BY created_at DESC";
    $result = $conn->query($query);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

// Get tracking history
function getTrackingHistory($pengaduan_id) {
    global $conn;
    $query = "SELECT t.*, u.nama_lengkap FROM tracking_pengaduan t
              LEFT JOIN users u ON t.admin_id = u.id
              WHERE t.pengaduan_id = $pengaduan_id 
              ORDER BY t.created_at DESC";
    $result = $conn->query($query);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

// Get all pengaduan with filters
function getAllPengaduan($filters = []) {
    global $conn;
    $query = "SELECT p.*, k.nama_kategori FROM pengaduan p 
              LEFT JOIN kategori_pengaduan k ON p.kategori_id = k.id WHERE 1=1";
    
    if (!empty($filters['status'])) {
        $status = $conn->real_escape_string($filters['status']);
        $query .= " AND p.status = '$status'";
    }
    if (!empty($filters['kategori'])) {
        $kategori = (int)$filters['kategori'];
        $query .= " AND p.kategori_id = $kategori";
    }
    if (!empty($filters['search'])) {
        $search = $conn->real_escape_string($filters['search']);
        $query .= " AND (p.judul_pengaduan LIKE '%$search%' OR p.no_laporan LIKE '%$search%')";
    }
    
    $query .= " ORDER BY p.created_at DESC";
    
    $result = $conn->query($query);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

// Submit pengaduan baru
function submitPengaduan($data) {
    global $conn;
    
    $no_laporan = generateNoLaporan();
    $nama = sanitize($data['nama']);
    $email = sanitize($data['email']);
    $telepon = sanitize($data['telepon']);
    $alamat = sanitize($data['alamat']);
    $kategori_id = (int)$data['kategori'];
    $judul = sanitize($data['judul']);
    $isi = sanitize($data['isi']);
    $lokasi = sanitize($data['lokasi']);
    $tanggal = sanitize($data['tanggal']);
    $user_id = isset($data['user_id']) ? (int)$data['user_id'] : null;
    
    $query = "INSERT INTO pengaduan (no_laporan, user_id, nama_pelapor, email_pelapor, telepon_pelapor, 
              alamat_pelapor, kategori_id, judul_pengaduan, isi_pengaduan, lokasi_kejadian, 
              tanggal_kejadian, status) 
              VALUES ('$no_laporan', " . ($user_id ? $user_id : "NULL") . ", '$nama', '$email', '$telepon', '$alamat', $kategori_id, 
              '$judul', '$isi', '$lokasi', '$tanggal', 'diterima')";
    
    if ($conn->query($query)) {
        $pengaduan_id = $conn->insert_id;
        logActivity($pengaduan_id, 'Pengaduan diterima', 'Pengaduan baru telah diterima oleh sistem');
        return ['success' => true, 'id' => $pengaduan_id, 'no_laporan' => $no_laporan];
    } else {
        return ['success' => false, 'error' => $conn->error];
    }
}

// Upload bukti
function uploadBukti($pengaduan_id, $file, $keterangan = '') {
    if (!file_exists(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0777, true);
    }
    
    $fileName = $file['name'];
    $fileTmp = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileErr = $file['error'];
    
    // Check file size
    if ($fileSize > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'Ukuran file terlalu besar (max 5MB)'];
    }
    
    // Check file extension
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if (!in_array($fileExt, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'error' => 'Tipe file tidak diizinkan'];
    }
    
    // Generate unique filename
    $newFileName = uniqid() . '_' . time() . '.' . $fileExt;
    $filePath = UPLOAD_DIR . $newFileName;
    
    // Move file
    if (move_uploaded_file($fileTmp, $filePath)) {
        global $conn;
        $keterangan = sanitize($keterangan);
        $query = "INSERT INTO bukti_pendukung (pengaduan_id, nama_file, tipe_file, ukuran_file, path_file, keterangan)
                  VALUES ($pengaduan_id, '$fileName', '$fileExt', $fileSize, '$newFileName', '$keterangan')";
        
        if ($conn->query($query)) {
            return ['success' => true, 'file' => $newFileName];
        } else {
            return ['success' => false, 'error' => $conn->error];
        }
    } else {
        return ['success' => false, 'error' => 'Gagal upload file'];
    }
}

// Update status pengaduan
function updateStatusPengaduan($pengaduan_id, $status, $keterangan, $admin_id = null) {
    global $conn;
    $status = $conn->real_escape_string($status);
    $keterangan = $conn->real_escape_string($keterangan);
    
    $query = "UPDATE pengaduan SET status = '$status', respons_admin = '$keterangan' 
              WHERE id = $pengaduan_id";
    
    if ($conn->query($query)) {
        logActivity($pengaduan_id, $status, $keterangan, $admin_id);
        return true;
    }
    return false;
}

// Get dashboard statistics
function getDashboardStats() {
    global $conn;
    $stats = [];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM pengaduan");
    $stats['total'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM pengaduan WHERE status = 'diterima'");
    $stats['diterima'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM pengaduan WHERE status = 'sedang_diproses'");
    $stats['diproses'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM pengaduan WHERE status = 'selesai'");
    $stats['selesai'] = $result->fetch_assoc()['total'];
    
    return $stats;
}

// ===== PELAPOR USER MANAGEMENT =====

// Check if pelapor is logged in
function checkPelaporLogin() {
    if (!isset($_SESSION['pelapor_id'])) {
        redirect(BASE_URL . 'pelapor/login.php');
    }
}

// Register pelapor baru
function registerPelapor($data) {
    global $conn;
    
    $email = sanitize($data['email']);
    $nama = sanitize($data['nama']);
    $password = hash('sha256', $data['password']);
    $telepon = sanitize($data['telepon']);
    $alamat = sanitize($data['alamat'] ?? '');
    $kota = sanitize($data['kota'] ?? '');
    $provinsi = sanitize($data['provinsi'] ?? '');
    $no_identitas = sanitize($data['no_identitas'] ?? '');
    $tipe_identitas = sanitize($data['tipe_identitas'] ?? 'KTP');
    
    // Check if email already exists
    $result = $conn->query("SELECT id FROM pelapor_users WHERE email = '$email'");
    if ($result && $result->num_rows > 0) {
        return ['success' => false, 'error' => 'Email sudah terdaftar'];
    }
    
    // Check if identitas already exists (if provided)
    if (!empty($no_identitas)) {
        $result = $conn->query("SELECT id FROM pelapor_users WHERE no_identitas = '$no_identitas'");
        if ($result && $result->num_rows > 0) {
            return ['success' => false, 'error' => 'Nomor identitas sudah terdaftar'];
        }
    }
    
    $query = "INSERT INTO pelapor_users (nama_lengkap, email, password, telepon, alamat, kota, provinsi, no_identitas, tipe_identitas) 
              VALUES ('$nama', '$email', '$password', '$telepon', '$alamat', '$kota', '$provinsi', " . 
              (!empty($no_identitas) ? "'$no_identitas'" : "NULL") . ", '$tipe_identitas')";
    
    if ($conn->query($query)) {
        return ['success' => true, 'id' => $conn->insert_id];
    } else {
        return ['success' => false, 'error' => $conn->error];
    }
}

// Login pelapor
function loginPelapor($email, $password) {
    global $conn;
    
    $email = sanitize($email);
    $password_hash = hash('sha256', $password);
    
    $result = $conn->query("SELECT * FROM pelapor_users WHERE email = '$email'");
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($password_hash === $user['password']) {
            return ['success' => true, 'user' => $user];
        } else {
            return ['success' => false, 'error' => 'Email atau password salah'];
        }
    } else {
        return ['success' => false, 'error' => 'Email atau password salah'];
    }
}

// Get pelapor info
function getPelaporInfo($pelapor_id) {
    global $conn;
    $result = $conn->query("SELECT * FROM pelapor_users WHERE id = $pelapor_id");
    return $result ? $result->fetch_assoc() : null;
}

// Get pelapor pengaduan list
function getPelaporPengaduan($pelapor_id, $filters = []) {
    global $conn;
    $query = "SELECT p.*, k.nama_kategori FROM pengaduan p 
              LEFT JOIN kategori_pengaduan k ON p.kategori_id = k.id 
              WHERE p.user_id = $pelapor_id";
    
    if (!empty($filters['status'])) {
        $status = $conn->real_escape_string($filters['status']);
        $query .= " AND p.status = '$status'";
    }
    
    $query .= " ORDER BY p.created_at DESC";
    
    $result = $conn->query($query);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

// Get pelapor pengaduan statistics
function getPelaporStats($pelapor_id) {
    global $conn;
    $stats = [];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM pengaduan WHERE user_id = $pelapor_id");
    $stats['total'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM pengaduan WHERE user_id = $pelapor_id AND status = 'diterima'");
    $stats['diterima'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM pengaduan WHERE user_id = $pelapor_id AND status = 'sedang_diproses'");
    $stats['diproses'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM pengaduan WHERE user_id = $pelapor_id AND status = 'selesai'");
    $stats['selesai'] = $result->fetch_assoc()['total'];
    
    return $stats;
}

// Update pelapor profile
function updatePelaporProfile($pelapor_id, $data) {
    global $conn;
    
    $nama = sanitize($data['nama']);
    $telepon = sanitize($data['telepon'] ?? '');
    $alamat = sanitize($data['alamat'] ?? '');
    $kota = sanitize($data['kota'] ?? '');
    $provinsi = sanitize($data['provinsi'] ?? '');
    
    $query = "UPDATE pelapor_users SET nama_lengkap = '$nama', telepon = '$telepon', 
              alamat = '$alamat', kota = '$kota', provinsi = '$provinsi' WHERE id = $pelapor_id";
    
    return $conn->query($query);
}
?>
