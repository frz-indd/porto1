<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Check if pelapor is logged in, jika tidak redirect ke login
if (!isset($_SESSION['pelapor_id'])) {
    redirect(BASE_URL . 'pelapor/login.php');
}

$pelapor_id = $_SESSION['pelapor_id'];
$pelapor = getPelaporInfo($pelapor_id);

$submitted = false;
$no_laporan = '';
$error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required_fields = ['kategori', 'judul', 'isi', 'lokasi', 'tanggal'];
    
    $all_filled = true;
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $all_filled = false;
            break;
        }
    }
    
    if (!$all_filled) {
        $error = 'Semua field harus diisi!';
    } else {
        // Add pelapor info to data
        $_POST['nama'] = $pelapor['nama_lengkap'];
        $_POST['email'] = $pelapor['email'];
        $_POST['telepon'] = $pelapor['telepon'];
        $_POST['alamat'] = $pelapor['alamat'];
        $_POST['user_id'] = $pelapor_id;
        
        $result = submitPengaduan($_POST);
        if ($result['success']) {
            $submitted = true;
            $no_laporan = $result['no_laporan'];
            // Handle file uploads if any
            if (isset($_FILES['bukti']) && !empty($_FILES['bukti']['name'][0])) {
                foreach ($_FILES['bukti']['name'] as $key => $name) {
                    if (!empty($name)) {
                        $file = [
                            'name' => $_FILES['bukti']['name'][$key],
                            'tmp_name' => $_FILES['bukti']['tmp_name'][$key],
                            'size' => $_FILES['bukti']['size'][$key],
                            'error' => $_FILES['bukti']['error'][$key]
                        ];
                        uploadBukti($result['id'], $file, $_POST['keterangan_bukti'][$key] ?? '');
                    }
                }
            }
        } else {
            $error = 'Gagal mengirim pengaduan: ' . $result['error'];
        }
    }
}

$categories = getCategories();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pengaduan Masyarakat - Form Pengaduan</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/modal.js"></script>
</head>
<body>
    <div class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">Pengaduan Masyarakat</div>
            <div class="navbar-menu">
                <a href="index.php">Beranda</a>
                <a href="tracking.php">Tracking</a>
                <span style="color: rgba(255,255,255,0.3);">|</span>
                <a href="pelapor/dashboard.php">Profil (<?php echo htmlspecialchars($pelapor['nama_lengkap']); ?>)</a>
                <a href="pelapor/logout.php">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if ($submitted): ?>
            <div id="successNotification" style="display: none;"></div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showSuccessModal(
                        '✓ Pengaduan Berhasil Dikirim',
                        '<strong>Terima kasih!</strong> Pengaduan Anda telah diterima oleh sistem.<br><br>' +
                        '<div style="background: #f0f5ff; padding: 1.5rem; border-radius: 8px; margin: 1rem 0; text-align: center;">' +
                        '<p style="margin: 0.5rem 0;"><strong>Nomor Laporan Anda:</strong></p>' +
                        '<p style="margin: 0.5rem 0; color: #667eea; font-weight: bold; font-size: 1.3rem;"><?php echo $no_laporan; ?></p>' +
                        '<p style="margin: 0.5rem 0;"><strong>Status:</strong> Diterima</p>' +
                        '</div>' +
                        '<p style="margin-top: 1rem;">Simpan nomor laporan di atas untuk melacak pengaduan Anda.</p>',
                        [
                            { text: 'Lacak Pengaduan', type: 'primary', onclick: "window.location.href = 'tracking.php'" },
                            { text: 'Ajukan Baru', type: 'secondary', onclick: "window.location.href = 'index.php'" }
                        ]
                    );
                });
            </script>
        <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Form Pengaduan Masyarakat</div>
                </div>

                <?php if ($error): ?>
                    <div id="errorNotification" style="display: none;"></div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showDangerModal('Validasi Gagal', <?php echo json_encode($error); ?>);
                        });
                    </script>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <h3 style="margin-bottom: 1.5rem; color: #667eea;">Data Pelapor</h3>
                    
                    <!-- Display pelapor info -->
                    <div class="grid-2" style="margin-bottom: 2rem; padding: 1.5rem; background: #f0f5ff; border-radius: 8px;">
                        <div>
                            <label style="color: #666; font-size: 0.9rem;">Nama Lengkap</label>
                            <p style="margin: 0.5rem 0 0 0; font-weight: 600;"><?php echo htmlspecialchars($pelapor['nama_lengkap']); ?></p>
                        </div>
                        <div>
                            <label style="color: #666; font-size: 0.9rem;">Email</label>
                            <p style="margin: 0.5rem 0 0 0; font-weight: 600;"><?php echo htmlspecialchars($pelapor['email']); ?></p>
                        </div>
                        <div>
                            <label style="color: #666; font-size: 0.9rem;">Nomor Telepon</label>
                            <p style="margin: 0.5rem 0 0 0; font-weight: 600;"><?php echo htmlspecialchars($pelapor['telepon']); ?></p>
                        </div>
                        <div>
                            <label style="color: #666; font-size: 0.9rem;">Alamat</label>
                            <p style="margin: 0.5rem 0 0 0; font-weight: 600;"><?php echo htmlspecialchars($pelapor['alamat'] ?? '-'); ?></p>
                        </div>
                    </div>

                    <h3 style="margin-bottom: 1.5rem; color: #667eea;">Rincian Pengaduan</h3>

                    <div class="grid-2">
                        <div class="form-group">
                            <label for="kategori">Kategori Pengaduan *</label>
                            <select id="kategori" name="kategori" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nama_kategori']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="tanggal">Tanggal Kejadian *</label>
                            <input type="date" id="tanggal" name="tanggal" required max="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="judul">Judul Pengaduan *</label>
                        <input type="text" id="judul" name="judul" required placeholder="Masukkan judul singkat pengaduan">
                    </div>

                    <div class="form-group">
                        <label for="lokasi">Lokasi Kejadian *</label>
                        <input type="text" id="lokasi" name="lokasi" required placeholder="Masukkan lokasi detail kejadian">
                    </div>

                    <div class="form-group">
                        <label for="isi">Uraian Pengaduan *</label>
                        <textarea id="isi" name="isi" required placeholder="Jelaskan secara rinci peristiwa/permasalahan yang ingin Anda laporkan..." style="min-height: 200px;"></textarea>
                    </div>

                    <h3 style="margin-top: 2rem; margin-bottom: 1.5rem; color: #667eea;">Bukti Pendukung (Opsional)</h3>

                    <div class="form-group">
                        <label>Upload File Bukti (Gambar/PDF/Document)</label>
                        <div class="file-upload" id="fileUploadArea">
                            <p>Klik atau drag file ke sini</p>
                            <p style="font-size: 0.9rem; color: #666; margin-top: 0.5rem;">Format: JPG, PNG, PDF, DOC, DOCX (Max 5MB per file)</p>
                            <input type="file" id="bukti" name="bukti[]" class="file-upload-input" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx">
                        </div>
                        <div class="file-list" id="fileList"></div>
                    </div>

                    <div id="buktiFields"></div>

                    <div style="margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary" style="padding: 1rem 3rem; font-size: 1.1rem;">Kirim Pengaduan</button>
                        <button type="reset" class="btn btn-secondary" style="padding: 1rem 3rem; font-size: 1.1rem; margin-left: 1rem;">Reset</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script src="assets/js/form.js"></script>
</body>
</html>
