<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

checkPelaporLogin();

$pelapor_id = $_SESSION['pelapor_id'];
$pelapor = getPelaporInfo($pelapor_id);
$stats = getPelaporStats($pelapor_id);
$pengaduan_list = getPelaporPengaduan($pelapor_id);

$page = isset($_GET['page']) ? sanitize($_GET['page']) : 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pengaduan Masyarakat</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/pelapor.css">
    <script src="../assets/js/modal.js"></script>
</head>
<body>
    <!-- Sidebar -->
    <div class="pelapor-sidebar">
        <div class="pelapor-sidebar-header">
            <div class="pelapor-sidebar-brand">Pelapor</div>
            <div class="pelapor-sidebar-email"><?php echo htmlspecialchars($pelapor ['nama_lengkap']); ?></div>
        </div>
        
        <ul class="pelapor-menu">
            <li>
                <a href="dashboard.php" class="<?php echo $page === 'dashboard' || empty($page) ? 'active' : ''; ?>"> Dashboard
                </a>
            </li>
            <li>
                <a href="?page=profil" class="<?php echo $page === 'profil' ? 'active' : ''; ?>"> Profil
                </a>
            </li>
            <li>
                <a href="?page=laporan" class="<?php echo $page === 'laporan' ? 'active' : ''; ?>">
                 Laporan Saya
                </a>
            </li>
            <li>
                <a href="../index.php">
                 Buat Laporan Baru
                </a>
            </li>
            <li>
                <a href="logout.php" style="color: #e74c3c;">
                 Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="pelapor-main">
        <div class="pelapor-navbar">
            <div class="pelapor-navbar-title">
                <?php 
                if ($page === 'profil') {
                    echo 'Profil Saya';
                } elseif ($page === 'laporan') {
                    echo ' Laporan Saya';
                } else {
                    echo ' Dashboard';
                }
                ?>
            </div>
            <div class="pelapor-navbar-menu">
                <span><?php echo htmlspecialchars($pelapor['email']); ?></span>
                <a href="logout.php">Logout</a>
            </div>
        </div>

        <div class="pelapor-content">
            <?php if ($page === 'dashboard' || empty($page)): ?>
                <!-- Dashboard Stats -->
                <div class="pelapor-stat-grid">
                    <div class="pelapor-stat">
                        <div style="font-size: 2rem;"></div>
                        <div class="pelapor-stat-number"><?php echo $stats['total']; ?></div>
                        <div class="pelapor-stat-label">Total Laporan</div>
                    </div>
                    <div class="pelapor-stat">
                        <div style="font-size: 2rem;"></div>
                        <div class="pelapor-stat-number"><?php echo $stats['diterima']; ?></div>
                        <div class="pelapor-stat-label">Diterima</div>
                    </div>
                    <div class="pelapor-stat">
                        <div style="font-size: 2rem;"></div>
                        <div class="pelapor-stat-number"><?php echo $stats['diproses']; ?></div>
                        <div class="pelapor-stat-label">Sedang Diproses</div>
                    </div>
                    <div class="pelapor-stat">
                        <div style="font-size: 2rem;"></div>
                        <div class="pelapor-stat-number"><?php echo $stats['selesai']; ?></div>
                        <div class="pelapor-stat-label">Selesai</div>
                    </div>
                </div>

                <!-- Recent Laporan -->
                <div class="pelapor-card">
                    <div class="pelapor-list-header">
                        <div class="pelapor-list-title">Laporan Terbaru</div>
                        <a href="index.php" class="btn btn-primary btn-small">+ Buat Baru</a>
                    </div>
                    
                    <?php if (!empty($pengaduan_list)): ?>
                        <div class="pelapor-list">
                            <?php foreach (array_slice($pengaduan_list, 0, 5) as $laporan): ?>
                                <div class="pelapor-list-item">
                                    <div class="pelapor-list-item-content">
                                        <div class="pelapor-list-item-no">
                                            <?php echo htmlspecialchars($laporan['no_laporan']); ?>
                                        </div>
                                        <div class="pelapor-list-item-title">
                                            <?php echo htmlspecialchars($laporan['judul_pengaduan']); ?>
                                        </div>
                                        <div class="pelapor-list-item-meta">
                                            <span><?php echo htmlspecialchars($laporan['nama_kategori']); ?></span>
                                            <span><?php echo date('d-m-Y', strtotime($laporan['created_at'])); ?></span>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge badge-<?php echo $laporan['status']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $laporan['status'])); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div style="padding: 2rem; text-align: center; color: #666;">
                            <p>Belum ada laporan</p>
                            <a href="../index.php" class="btn btn-primary" style="margin-top: 1rem;">Buat Laporan Pertama Anda</a>
                        </div>
                    <?php endif; ?>
                </div>

<?php elseif ($page === 'profil'): ?>
               <!-- Profil -->
                <div class="pelapor-card">
                    <h2 style="margin-top: 0; color: #333;">Informasi Profil</h2>
                    
                    <div style="background: #f9f9f9; padding: 1.5rem; border-radius: 8px; margin-top: 1.5rem;">
                        <div style="margin-bottom: 1.5rem;">
                            <label style="font-weight: 600; color: #666; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">Nama Lengkap</label>
                            <p style="margin: 0; font-size: 1rem;"><?php echo htmlspecialchars($pelapor['nama_lengkap']); ?></p>
                        </div>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label style="font-weight: 600; color: #666; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">Email</label>
                            <p style="margin: 0; font-size: 1rem;"><?php echo htmlspecialchars($pelapor['email']); ?></p>
                        </div>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label style="font-weight: 600; color: #666; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">Nomor Telepon</label>
                            <p style="margin: 0; font-size: 1rem;"><?php echo htmlspecialchars($pelapor['telepon'] ?? '-'); ?></p>
                        </div>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label style="font-weight: 600; color: #666; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">Kota</label>
                            <p style="margin: 0; font-size: 1rem;"><?php echo htmlspecialchars($pelapor['kota'] ?? '-'); ?></p>
                        </div>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label style="font-weight: 600; color: #666; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">Alamat</label>
                            <p style="margin: 0; font-size: 1rem;"><?php echo htmlspecialchars($pelapor['alamat'] ?? '-'); ?></p>
                        </div>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label style="font-weight: 600; color: #666; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">Status Verifikasi</label>
                            <span class="badge badge-<?php echo strtolower($pelapor['status_verifikasi']); ?>">
                                <?php 
                                $status = [
                                    'pending' => 'Menunggu Verifikasi',
                                    'verified' => 'Terverifikasi',
                                    'rejected' => 'Ditolak'
                                ];
                                echo $status[$pelapor['status_verifikasi']] ?? $pelapor['status_verifikasi'];
                                ?>
                            </span>
                        </div>
                        
                        <div style="margin-bottom: 0;">
                            <label style="font-weight: 600; color: #666; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">Tanggal Daftar</label>
                            <p style="margin: 0; font-size: 1rem;"><?php echo date('d-m-Y H:i', strtotime($pelapor['created_at'])); ?></p>
                        </div>
                    </div>
                </div>

            <?php elseif ($page === 'laporan'): ?>
                <!-- Laporan List -->
                <div class="pelapor-card">
                    <div class="pelapor-list-header">
                        <div class="pelapor-list-title">Semua Laporan (<?php echo count($pengaduan_list); ?>)</div>
                        <a href="../index.php" class="btn btn-primary btn-small">+ Buat Baru</a>
                    </div>
                    
                    <?php if (!empty($pengaduan_list)): ?>
                        <div class="pelapor-list">
                            <?php foreach ($pengaduan_list as $laporan): ?>
                                <div class="pelapor-list-item" style="cursor: pointer;" 
                                     onclick="window.location.href='../tracking.php?no_laporan=<?php echo htmlspecialchars($laporan['no_laporan']); ?>'">
                                    <div class="pelapor-list-item-content">
                                        <div class="pelapor-list-item-no">
                                            <?php echo htmlspecialchars($laporan['no_laporan']); ?>
                                        </div>
                                        <div class="pelapor-list-item-title">
                                            <?php echo htmlspecialchars($laporan['judul_pengaduan']); ?>
                                        </div>
                                        <div class="pelapor-list-item-meta">
                                            <span><?php echo htmlspecialchars($laporan['nama_kategori']); ?></span>
                                            <span><?php echo date('d-m-Y H:i', strtotime($laporan['created_at'])); ?></span>
                                            <span><?php echo htmlspecialchars($laporan['prioritas']); ?></span>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge badge-<?php echo $laporan['status']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $laporan['status'])); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div style="padding: 2rem; text-align: center; color: #666;">
                            <p>Belum ada laporan</p>
                            <a href="../index.php" class="btn btn-primary" style="margin-top: 1rem;">Buat Laporan Pertama Anda</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
