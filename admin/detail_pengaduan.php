<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

checkLogin();

if (empty($_GET['id'])) {
    redirect(BASE_URL . 'admin/pengaduan.php');
}

$pengaduan_id = (int)$_GET['id'];
$pengaduan = getPengaduan($pengaduan_id);

if (!$pengaduan) {
    redirect(BASE_URL . 'admin/pengaduan.php');
}

$tracking_history = getTrackingHistory($pengaduan_id);
$bukti_list = getBuktiPendukung($pengaduan_id);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $new_status = sanitize($_POST['status']);
        $respons = sanitize($_POST['respons']);
        $prioritas = sanitize($_POST['prioritas']);
        
        // Update status
        $query = "UPDATE pengaduan SET status = '$new_status', respons_admin = '$respons', prioritas = '$prioritas' 
                  WHERE id = $pengaduan_id";
        
        if ($conn->query($query)) {
            logActivity($pengaduan_id, $new_status, $respons, $_SESSION['user_id']);
            // Refresh pengaduan data
            $pengaduan = getPengaduan($pengaduan_id);
            $tracking_history = getTrackingHistory($pengaduan_id);
            echo '<div class="alert alert-success">Status pengaduan berhasil diperbarui!</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengaduan - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">Admin Panel - Pengaduan Masyarakat</div>
            <div class="navbar-menu">
                <a href="dashboard.php">Dashboard</a>
                <a href="pengaduan.php">Pengaduan</a>
                <a href="laporan.php">Laporan</a>
                <span style="color: rgba(255,255,255,0.7);">Halo, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div style="margin-bottom: 1rem;">
            <a href="pengaduan.php" class="btn btn-secondary">← Kembali</a>
        </div>

        <div class="grid grid-2" style="gap: 2rem;">
            <!-- Left: Detail Pengaduan -->
            <div>
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Detail Pengaduan</div>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label style="font-weight: 600;">Nomor Laporan</label>
                        <p><?php echo htmlspecialchars($pengaduan['no_laporan']); ?></p>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label style="font-weight: 600;">Pelapor</label>
                        <p><?php echo htmlspecialchars($pengaduan['nama_pelapor']); ?></p>
                        <p style="margin: 0.25rem 0 0 0; font-size: 0.9rem; color: #666;">Email: <?php echo htmlspecialchars($pengaduan['email_pelapor']); ?></p>
                        <p style="margin: 0.25rem 0 0 0; font-size: 0.9rem; color: #666;">Telepon: <?php echo htmlspecialchars($pengaduan['telepon_pelapor']); ?></p>
                        <p style="margin: 0.25rem 0 0 0; font-size: 0.9rem; color: #666;">Alamat: <?php echo htmlspecialchars($pengaduan['alamat_pelapor']); ?></p>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label style="font-weight: 600;">Kategori</label>
                        <p><?php echo htmlspecialchars($pengaduan['nama_kategori']); ?></p>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label style="font-weight: 600;">Judul</label>
                        <p><?php echo htmlspecialchars($pengaduan['judul_pengaduan']); ?></p>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label style="font-weight: 600;">Lokasi Kejadian</label>
                        <p><?php echo htmlspecialchars($pengaduan['lokasi_kejadian']); ?></p>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label style="font-weight: 600;">Tanggal Kejadian</label>
                        <p><?php echo date('d-m-Y', strtotime($pengaduan['tanggal_kejadian'])); ?></p>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label style="font-weight: 600;">Uraian</label>
                        <p style="background: #f9f9f9; padding: 1rem; border-radius: 4px; line-height: 1.8;">
                            <?php echo nl2br(htmlspecialchars($pengaduan['isi_pengaduan'])); ?>
                        </p>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label style="font-weight: 600;">Tanggal Pengaduan</label>
                        <p><?php echo date('d-m-Y H:i', strtotime($pengaduan['created_at'])); ?></p>
                    </div>
                </div>

                <!-- Bukti Pendukung -->
                <?php if ($bukti_list): ?>
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">📎 Bukti Pendukung</div>
                        </div>
                        <div class="file-list">
                            <?php foreach ($bukti_list as $bukti): ?>
                                <div class="file-item">
                                    <div>
                                        <p style="margin: 0;"><strong><?php echo htmlspecialchars($bukti['nama_file']); ?></strong></p>
                                        <?php if ($bukti['keterangan']): ?>
                                            <p style="margin: 0.25rem 0 0 0; color: #666; font-size: 0.9rem;"><?php echo htmlspecialchars($bukti['keterangan']); ?></p>
                                        <?php endif; ?>
                                        <p style="margin: 0.25rem 0 0 0; color: #999; font-size: 0.85rem;">Upload: <?php echo date('d-m-Y H:i', strtotime($bukti['created_at'])); ?></p>
                                    </div>
                                    <a href="<?php echo BASE_URL; ?>uploads/<?php echo htmlspecialchars($bukti['path_file']); ?>" target="_blank" class="btn btn-primary btn-small">Unduh</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right: Update Status -->
            <div>
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Update Status</div>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="action" value="update_status">

                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select id="status" name="status" required>
                                <option value="diterima" <?php echo $pengaduan['status'] === 'diterima' ? 'selected' : ''; ?>>Diterima</option>
                                <option value="sedang_diproses" <?php echo $pengaduan['status'] === 'sedang_diproses' ? 'selected' : ''; ?>>Sedang Diproses</option>
                                <option value="ditangguhkan" <?php echo $pengaduan['status'] === 'ditangguhkan' ? 'selected' : ''; ?>>Ditangguhkan</option>
                                <option value="selesai" <?php echo $pengaduan['status'] === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                <option value="ditolak" <?php echo $pengaduan['status'] === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="prioritas">Prioritas *</label>
                            <select id="prioritas" name="prioritas" required>
                                <option value="rendah" <?php echo $pengaduan['prioritas'] === 'rendah' ? 'selected' : ''; ?>>Rendah</option>
                                <option value="sedang" <?php echo $pengaduan['prioritas'] === 'sedang' ? 'selected' : ''; ?>>Sedang</option>
                                <option value="tinggi" <?php echo $pengaduan['prioritas'] === 'tinggi' ? 'selected' : ''; ?>>Tinggi</option>
                                <option value="urgen" <?php echo $pengaduan['prioritas'] === 'urgen' ? 'selected' : ''; ?>>Urgen</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="respons">Respons/Tindakan *</label>
                            <textarea id="respons" name="respons" required placeholder="Jelaskan tindakan atau respons terhadap pengaduan ini..." style="min-height: 150px;"><?php echo htmlspecialchars($pengaduan['respons_admin']); ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-success" style="width: 100%; padding: 0.875rem;">Update Status</button>
                    </form>
                </div>

                <!-- Timeline -->
                <?php if ($tracking_history): ?>
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">📍 Timeline</div>
                        </div>
                        <div class="timeline">
                            <?php foreach ($tracking_history as $track): ?>
                                <div class="timeline-item">
                                    <div class="timeline-date"><?php echo date('d-m-Y H:i', strtotime($track['created_at'])); ?></div>
                                    <div class="timeline-content">
                                        <strong><?php echo ucfirst(str_replace('_', ' ', $track['status_baru'])); ?></strong>
                                        <?php if ($track['keterangan']): ?>
                                            <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem;"><?php echo htmlspecialchars($track['keterangan']); ?></p>
                                        <?php endif; ?>
                                        <?php if ($track['nama_lengkap']): ?>
                                            <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: #666;">Oleh: <?php echo htmlspecialchars($track['nama_lengkap']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
