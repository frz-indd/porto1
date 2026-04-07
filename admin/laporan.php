<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

checkLogin();

// Get statistics by date
$date_filter = $_GET['bulan'] ?? date('Y-m');
$year = substr($date_filter, 0, 4);
$month = substr($date_filter, 5, 2);

$query = "SELECT DATE(created_at) as tanggal, COUNT(*) as jumlah FROM pengaduan 
          WHERE YEAR(created_at) = $year AND MONTH(created_at) = $month
          GROUP BY DATE(created_at) ORDER BY tanggal";
$daily_stats = $conn->query($query)->fetch_all(MYSQLI_ASSOC);

// Get statistics by category
$category_stats = $conn->query("SELECT k.nama_kategori, COUNT(p.id) as jumlah 
                               FROM kategori_pengaduan k 
                               LEFT JOIN pengaduan p ON k.id = p.kategori_id 
                               WHERE YEAR(p.created_at) = $year AND MONTH(p.created_at) = $month
                               GROUP BY k.id")->fetch_all(MYSQLI_ASSOC);

// Get statistics by status
$status_stats = $conn->query("SELECT status, COUNT(*) as jumlah FROM pengaduan 
                             WHERE YEAR(created_at) = $year AND MONTH(created_at) = $month
                             GROUP BY status")->fetch_all(MYSQLI_ASSOC);

$overall_stats = getDashboardStats();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Admin</title>
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
        <h1 style="margin-bottom: 2rem;">Laporan Pengaduan</h1>

        <!-- Filter by Month -->
        <div class="card" style="margin-bottom: 2rem;">
            <form method="GET" style="display: flex; gap: 1rem; align-items: flex-end;">
                <div class="form-group" style="margin-bottom: 0; flex: 1; max-width: 300px;">
                    <label for="bulan">Pilih Bulan</label>
                    <input type="month" id="bulan" name="bulan" value="<?php echo $date_filter; ?>">
                </div>
                <button type="submit" class="btn btn-primary">Tampilkan</button>
            </form>
        </div>

        <!-- Overall Stats -->
        <div class="grid grid-3" style="margin-bottom: 2rem;">
            <div class="stat-box">
                <div style="font-size: 2rem;"></div>
                <div class="stat-number"><?php echo $overall_stats['total']; ?></div>
                <div class="stat-label">Total Semua Waktu</div>
            </div>
            <div class="stat-box">
                <div style="font-size: 2rem;"></div>
                <div class="stat-number"><?php echo $overall_stats['selesai']; ?></div>
                <div class="stat-label">Selesai</div>
            </div>
            <div class="stat-box">
                <div style="font-size: 2rem;"></div>
                <div class="stat-number"><?php echo $overall_stats['diproses']; ?></div>
                <div class="stat-label">Sedang Diproses</div>
            </div>
        </div>

        <!-- Charts/Tables -->
        <div class="grid grid-2" style="margin-bottom: 2rem;">
            <!-- By Category -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Pengaduan Berdasarkan Kategori</div>
                </div>
                <?php if (!empty($category_stats)): ?>
                    <div style="padding: 1rem;">
                        <?php 
                        $total_category = array_sum(array_column($category_stats, 'jumlah'));
                        foreach ($category_stats as $stat): 
                        ?>
                            <div style="margin-bottom: 1rem;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span><?php echo htmlspecialchars($stat['nama_kategori']); ?></span>
                                    <span style="font-weight: bold;"><?php echo $stat['jumlah']; ?></span>
                                </div>
                                <div style="background: #e0e0e0; height: 8px; border-radius: 4px; overflow: hidden;">
                                    <div style="background: linear-gradient(90deg, #667eea, #764ba2); height: 100%; width: <?php echo ($stat['jumlah'] / ($total_category ?: 1)) * 100; ?>%;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="padding: 1rem; text-align: center; color: #666;">Tidak ada data</p>
                <?php endif; ?>
            </div>

            <!-- By Status -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Pengaduan Berdasarkan Status</div>
                </div>
                <?php if (!empty($status_stats)): ?>
                    <div style="padding: 1rem;">
                        <?php 
                        $total_status = array_sum(array_column($status_stats, 'jumlah'));
                        foreach ($status_stats as $stat): 
                        ?>
                            <div style="margin-bottom: 1rem;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $stat['status']))); ?></span>
                                    <span style="font-weight: bold;"><?php echo $stat['jumlah']; ?></span>
                                </div>
                                <div style="background: #e0e0e0; height: 8px; border-radius: 4px; overflow: hidden;">
                                    <div style="background: linear-gradient(90deg, #667eea, #764ba2); height: 100%; width: <?php echo ($stat['jumlah'] / ($total_status ?: 1)) * 100; ?>%;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="padding: 1rem; text-align: center; color: #666;">Tidak ada data</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Daily Stats Table -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Pengaduan Per Hari (<?php echo date('F Y', strtotime($date_filter . '-01')); ?>)</div>
            </div>
            <?php if (!empty($daily_stats)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jumlah Pengaduan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($daily_stats as $stat): ?>
                            <tr>
                                <td><?php echo date('d-m-Y', strtotime($stat['tanggal'])); ?></td>
                                <td><?php echo $stat['jumlah']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="padding: 2rem; text-align: center; color: #666;">Tidak ada data untuk bulan ini</p>
            <?php endif; ?>
        </div>

        <div style="margin-top: 2rem; padding: 1.5rem; background: #f9f9f9; border-radius: 8px;">
            <button onclick="window.print()" class="btn btn-primary">🖨️ Cetak Laporan</button>
        </div>
    </div>

    <style>
        @media print {
            .navbar, button { display: none; }
        }
    </style>
</body>
</html>
