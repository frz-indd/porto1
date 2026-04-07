<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pengaduan = null;
$tracking_history = null;
$bukti_list = null;
$search_error = '';

// Handle tracking search
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['no_laporan'])) {
    $no_laporan = sanitize($_GET['no_laporan']);
    $result = $conn->query("SELECT * FROM pengaduan WHERE no_laporan = '$no_laporan'");
    
    if ($result && $result->num_rows > 0) {
        $pengaduan = $result->fetch_assoc();
        $tracking_history = getTrackingHistory($pengaduan['id']);
        $bukti_list = getBuktiPendukung($pengaduan['id']);
    } else {
        $search_error = 'Nomor laporan tidak ditemukan. Silakan cek kembali nomor laporan Anda.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Pengaduan - Sistem Pengaduan Masyarakat</title>
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
                <?php if (isset($_SESSION['pelapor_id'])): ?>
                    <span style="color: rgba(255,255,255,0.3);">|</span>
                    <a href="pelapor/dashboard.php">Profil</a>
                    <a href="pelapor/logout.php">Logout</a>
                <?php else: ?>
                    <span style="color: rgba(255,255,255,0.3);">|</span>
                    <a href="pelapor/login.php">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Tracking Status Pengaduan</div>
            </div>

            <!-- Search Form -->
            <form method="GET" style="margin-bottom: 2rem;">
                <div class="grid-2" style="align-items: flex-end;">
                    <div class="form-group">
                        <label for="no_laporan">Nomor Laporan</label>
                        <input type="text" id="no_laporan" name="no_laporan" placeholder="Contoh: LAP-20260406-ABC123" 
                               value="<?php echo isset($_GET['no_laporan']) ? htmlspecialchars($_GET['no_laporan']) : ''; ?>" required>
                        <small style="color: #666; margin-top: 0.25rem; display: block;">Nomor laporan diberikan saat Anda mengajukan pengaduan</small>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem;">Cari Laporan</button>
                    </div>
                </div>
            </form>

            <?php if ($search_error): ?>
                <div id="errorNotification" style="display: none;"></div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showWarningModal('Laporan Tidak Ditemukan', <?php echo json_encode($search_error); ?>);
                    });
                </script>
            <?php endif; ?>

            <!-- Result Display -->
            <?php if ($pengaduan): ?>
                <!-- Status Summary -->
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2rem; border-radius: 8px; margin-bottom: 2rem;">
                    <h2 style="margin-bottom: 1rem;">Status Pengaduan Anda</h2>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                        <div>
                            <div style="opacity: 0.8; margin-bottom: 0.5rem;">Nomor Laporan</div>
                            <div style="font-size: 1.3rem; font-weight: bold;"><?php echo htmlspecialchars($pengaduan['no_laporan']); ?></div>
                        </div>
                        <div>
                            <div style="opacity: 0.8; margin-bottom: 0.5rem;">Status Saat Ini</div>
                            <div style="font-size: 1.3rem; font-weight: bold;">
                                <?php 
                                $status_text = [
                                    'diterima' => 'Diterima',
                                    'sedang_diproses' => 'Sedang Diproses',
                                    'ditangguhkan' => 'Ditangguhkan',
                                    'selesai' => 'Selesai',
                                    'ditolak' => 'Ditolak'
                                ];
                                echo $status_text[$pengaduan['status']] ?? $pengaduan['status'];
                                ?>
                            </div>
                        </div>
                        <div>
                            <div style="opacity: 0.8; margin-bottom: 0.5rem;">Prioritas</div>
                            <div style="font-size: 1.3rem; font-weight: bold;">
                                <?php 
                                $prioritas = ['rendah' => 'Rendah', 'sedang' => 'Sedang', 'tinggi' => 'Tinggi', 'urgen' => 'Urgen'];
                                echo $prioritas[$pengaduan['prioritas']] ?? $pengaduan['prioritas'];
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Details -->
                <div style="margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 1rem; color: #667eea;">Detail Pengaduan</h3>
                    <div style="background: #f9f9f9; padding: 1.5rem; border-radius: 8px;">
                        <div style="margin-bottom: 1rem;">
                            <strong>Judul:</strong> <?php echo htmlspecialchars($pengaduan['judul_pengaduan']); ?>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>Kategori:</strong> <?php echo htmlspecialchars($pengaduan['nama_kategori']); ?>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>Tanggal Pengaduan:</strong> <?php echo date('d-m-Y H:i', strtotime($pengaduan['created_at'])); ?>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>Tanggal Kejadian:</strong> <?php echo date('d-m-Y', strtotime($pengaduan['tanggal_kejadian'])); ?>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>Lokasi Kejadian:</strong> <?php echo htmlspecialchars($pengaduan['lokasi_kejadian']); ?>
                        </div>
                        <div>
                            <strong>Uraian:</strong>
                            <p style="margin-top: 0.5rem; line-height: 1.8;"><?php echo nl2br(htmlspecialchars($pengaduan['isi_pengaduan'])); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Response from Admin -->
                <?php if ($pengaduan['respons_admin']): ?>
                    <div style="margin-bottom: 2rem;">
                        <h3 style="margin-bottom: 1rem; color: #667eea;">🗣️ Respons dari Admin</h3>
                        <div style="background: #e3f2fd; border-left: 4px solid #667eea; padding: 1.5rem; border-radius: 4px;">
                            <?php echo nl2br(htmlspecialchars($pengaduan['respons_admin'])); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Tracking Timeline -->
                <?php if ($tracking_history): ?>
                    <div style="margin-bottom: 2rem;">
                        <h3 style="margin-bottom: 1rem; color: #667eea;">📍 Timeline Pengaduan</h3>
                        <div class="timeline">
                            <?php foreach ($tracking_history as $track): ?>
                                <div class="timeline-item">
                                    <div class="timeline-date"><?php echo date('d-m-Y H:i', strtotime($track['created_at'])); ?></div>
                                    <div class="timeline-content">
                                        <strong>Status:</strong> 
                                        <span class="badge badge-<?php echo str_replace(' ', '_', $track['status_baru']); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $track['status_baru'])); ?>
                                        </span>
                                        <?php if ($track['keterangan']): ?>
                                            <p style="margin-top: 0.5rem;"><strong>Keterangan:</strong> <?php echo htmlspecialchars($track['keterangan']); ?></p>
                                        <?php endif; ?>
                                        <?php if ($track['nama_lengkap']): ?>
                                            <p style="margin-top: 0.5rem; font-size: 0.9rem; color: #666;">Oleh: <?php echo htmlspecialchars($track['nama_lengkap']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Supporting Documents -->
                <?php if ($bukti_list): ?>
                    <div>
                        <h3 style="margin-bottom: 1rem; color: #667eea;">📎 Dokumen Pendukung</h3>
                        <div class="file-list">
                            <?php foreach ($bukti_list as $bukti): ?>
                                <div class="file-item">
                                    <div>
                                        <p style="margin: 0;"><strong><?php echo htmlspecialchars($bukti['nama_file']); ?></strong></p>
                                        <?php if ($bukti['keterangan']): ?>
                                            <p style="margin: 0.25rem 0 0 0; color: #666; font-size: 0.9rem;"><?php echo htmlspecialchars($bukti['keterangan']); ?></p>
                                        <?php endif; ?>
                                        <p style="margin: 0.25rem 0 0 0; color: #999; font-size: 0.85rem;">Diupload: <?php echo date('d-m-Y H:i', strtotime($bukti['created_at'])); ?></p>
                                    </div>
                                    <a href="<?php echo BASE_URL; ?>uploads/<?php echo htmlspecialchars($bukti['path_file']); ?>" target="_blank" class="btn btn-primary btn-small">Lihat</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            <?php elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && empty($search_error)): ?>
                <!-- Initial message -->
                <div style="text-align: center; padding: 3rem 1rem;">
                    <!-- <div style="font-size: 3rem; margin-bottom: 1rem;"></div> -->
                    <p style="color: #666; font-size: 1.1rem;">Masukkan nomor laporan Anda untuk melacak status pengaduan</p>
                </div>
            <?php endif; ?>

        </div>

        <div style="text-align: center; margin-top: 2rem;">
            <p style="color: #666;">Tidak menemukan laporan Anda? <a href="index.php" style="color: #667eea; text-decoration: none; font-weight: 600;">Ajukan pengaduan baru</a></p>
        </div>
    </div>
</body>
</html>
