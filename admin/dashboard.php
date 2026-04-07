<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

checkLogin();

$stats = getDashboardStats();
$pengaduan_list = getAllPengaduan();

// Get distribution by status
$status_dist = $conn->query("SELECT status, COUNT(*) as count FROM pengaduan GROUP BY status");
$status_data = [];
while ($row = $status_dist->fetch_assoc()) {
    $status_data[$row['status']] = $row['count'];
}

// Get distribution by priority
$priority_dist = $conn->query("SELECT prioritas, COUNT(*) as count FROM pengaduan GROUP BY prioritas");
$priority_data = [];
while ($row = $priority_dist->fetch_assoc()) {
    $priority_data[$row['prioritas']] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Pengaduan Masyarakat</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">Dashboard - Admin </div>
            <div class="navbar-menu">
                <a href="dashboard.php">Dashboard</a>
                <a href="pengaduan.php">Pengaduan</a>
                <a href="laporan.php">Laporan</a>
                <span style="color: rgba(255,255,255,0.7);">Halo, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?> (<?php echo $_SESSION['role']; ?>)</span>
                <form method="POST" action="logout.php" style="display: inline;">
                    <button type="submit" style="background: none; border: none; color: white; cursor: pointer; font-size: 0.9rem; padding: 0.5rem 1rem; transition: 0.3s;">Logout</button>
                </form>
            </div>
        </div>
    </div>

    <div class="container">
        <h1 style="margin-bottom: 2rem;">Dashboard</h1>

        <!-- Statistics Grid -->
        <div class="grid grid-3" style="margin-bottom: 2rem;">
            <div class="stat-box">
                <div style="font-size: 2rem;"></div>
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Pengaduan</div>
            </div>
            <div class="stat-box">
                <div style="font-size: 2rem;"></div>
                <div class="stat-number"><?php echo $stats['diterima']; ?></div>
                <div class="stat-label">Diterima</div>
            </div>
            <div class="stat-box">
                <div style="font-size: 2rem;"></div>
                <div class="stat-number"><?php echo $stats['diproses']; ?></div>
                <div class="stat-label">Sedang Diproses</div>
            </div>
            <div class="stat-box">
                <div style="font-size: 2rem;"></div>
                <div class="stat-number"><?php echo $stats['selesai']; ?></div>
                <div class="stat-label">Selesai</div>
            </div>
        </div>

        <!-- Status Distribution -->
        <div class="grid grid-2" style="margin-bottom: 2rem;">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Distribusi Berdasarkan Status</div>
                </div>
                <div style="padding: 1rem;">
                    <?php foreach ($status_data as $status => $count): ?>
                        <div style="margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span><?php echo ucfirst(str_replace('_', ' ', $status)); ?></span>
                                <span style="font-weight: bold;"><?php echo $count; ?></span>
                            </div>
                            <div style="background: #e0e0e0; height: 8px; border-radius: 4px; overflow: hidden;">
                                <div style="background: linear-gradient(90deg, #667eea, #764ba2); height: 100%; width: <?php echo ($count / $stats['total']) * 100; ?>%;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">Distribusi Berdasarkan Prioritas</div>
                </div>
                <div style="padding: 1rem;">
                    <?php 
                    $priority_labels = ['rendah' => 'Rendah', 'sedang' => 'Sedang', 'tinggi' => 'Tinggi', 'urgen' => 'Urgen'];
                    foreach ($priority_labels as $key => $label): 
                        $count = $priority_data[$key] ?? 0;
                    ?>
                        <div style="margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span><?php echo $label; ?></span>
                                <span style="font-weight: bold;"><?php echo $count; ?></span>
                            </div>
                            <div style="background: #e0e0e0; height: 8px; border-radius: 4px; overflow: hidden;">
                                <div style="background: linear-gradient(90deg, #667eea, #764ba2); height: 100%; width: <?php echo $stats['total'] > 0 ? ($count / $stats['total']) * 100 : 0; ?>%;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Recent Complaints -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Pengaduan Terbaru</div>
            </div>
            <?php if (!empty($pengaduan_list)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nomor Laporan</th>
                            <th>Pelapor</th>
                            <th>Judul</th>
                            <th>Status</th>
                            <th>Prioritas</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($pengaduan_list, 0, 10) as $laporan): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($laporan['no_laporan']); ?></strong></td>
                                <td><?php echo htmlspecialchars($laporan['nama_pelapor']); ?></td>
                                <td><?php echo htmlspecialchars(substr($laporan['judul_pengaduan'], 0, 40)); ?>...</td>
                                <td><span class="badge badge-<?php echo $laporan['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $laporan['status'])); ?></span></td>
                                <td><span class="badge badge-<?php echo $laporan['prioritas']; ?>"><?php echo ucfirst($laporan['prioritas']); ?></span></td>
                                <td><?php echo date('d-m-Y', strtotime($laporan['created_at'])); ?></td>
                                <td><a href="detail_pengaduan.php?id=<?php echo $laporan['id']; ?>" class="btn btn-primary btn-small">Lihat</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; padding: 2rem; color: #666;">Belum ada pengaduan</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
