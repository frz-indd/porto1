<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pengaduan_masyarakat');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Session configuration
session_start();

// Base URL
define('BASE_URL', 'http://localhost/uts/');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

// File upload limits
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx']);

// Helper function untuk generate nomor laporan
function generateNoLaporan() {
    $date = date('Ymd');
    $random = strtoupper(substr(uniqid(), -6));
    return 'LAP-' . $date . '-' . $random;
}

// Helper function untuk redirect
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

// Helper function untuk JSON response
function jsonResponse($success, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Helper function untuk log activity
function logActivity($pengaduan_id, $status, $keterangan, $admin_id = null) {
    global $conn;
    $status = $conn->real_escape_string($status);
    $keterangan = $conn->real_escape_string($keterangan);
    
    $query = "INSERT INTO tracking_pengaduan (pengaduan_id, status_baru, keterangan, admin_id) 
              VALUES ($pengaduan_id, '$status', '$keterangan', " . ($admin_id ? $admin_id : 'NULL') . ")";
    return $conn->query($query);
}

// Sanitize input
function sanitize($text) {
    global $conn;
    return $conn->real_escape_string(strip_tags(trim($text)));
}
?>
