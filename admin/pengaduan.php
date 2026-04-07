<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

checkLogin();

// Get filters
$status_filter = $_GET['status'] ?? '';
$kategori_filter = $_GET['kategori'] ?? '';
$search = $_GET['search'] ?? '';

// Build filter array
$filters = [];
if ($status_filter) $filters['status'] = $status_filter;
if ($kategori_filter) $filters['kategori'] = $kategori_filter;
if ($search) $filters['search'] = $search;

$pengaduan_list = getAllPengaduan($filters);
$categories = getCategories();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengaduan - Admin</title>
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
        <h1 style="margin-bottom: 2rem;">Kelola Pengaduan</h1>

        <!-- Filters -->
        <div class="card" style="margin-bottom: 2rem;">
            <form method="GET">
                <div class="grid grid-3" style="gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="search">Cari Laporan</label>
                        <input type="text" id="search" name="search" placeholder="Nomor atau judul pengaduan..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="status">Filter Status</label>
                        <select id="status" name="status">
                            <option value="">-- Semua Status --</option>
                            <option value="diterima" <?php echo $status_filter === 'diterima' ? 'selected' : ''; ?>>Diterima</option>
                            <option value="sedang_diproses" <?php echo $status_filter === 'sedang_diproses' ? 'selected' : ''; ?>>Sedang Diproses</option>
                            <option value="ditangguhkan" <?php echo $status_filter === 'ditangguhkan' ? 'selected' : ''; ?>>Ditangguhkan</option>
                            <option value="selesai" <?php echo $status_filter === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                            <option value="ditolak" <?php echo $status_filter === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="kategori">Filter Kategori</label>
                        <select id="kategori" name="kategori">
                            <option value="">-- Semua Kategori --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $kategori_filter == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nama_kategori']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div style="margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="pengaduan.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>

        <!-- Results -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Daftar Pengaduan (<?php echo count($pengaduan_list); ?> laporan)</div>
            </div>
            <?php if (!empty($pengaduan_list)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nomor</th>
                            <th>Pelapor</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th>Prioritas</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pengaduan_list as $laporan): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($laporan['no_laporan']); ?></strong></td>
                                <td><?php echo htmlspecialchars($laporan['nama_pelapor']); ?></td>
                                <td><?php echo htmlspecialchars(substr($laporan['judul_pengaduan'], 0, 50)); ?></td>
                                <td><?php echo htmlspecialchars($laporan['nama_kategori']); ?></td>
                                <td><span class="badge badge-<?php echo $laporan['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $laporan['status'])); ?></span></td>
                                <td><span class="badge badge-<?php echo $laporan['prioritas']; ?>"><?php echo ucfirst($laporan['prioritas']); ?></span></td>
                                <td><?php echo date('d-m-Y', strtotime($laporan['created_at'])); ?></td>
                                <td>
                                    <a href="detail_pengaduan.php?id=<?php echo $laporan['id']; ?>" class="btn btn-primary btn-small">Detail</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; padding: 2rem; color: #666;">Tidak ada pengaduan yang sesuai dengan filter</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
