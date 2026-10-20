
<?php
require_once 'koneksi.php';
require_once 'auth.php';
wajibLogin();

$totalMahasiswa = (int) $pdo
    ->query('SELECT COUNT(*) FROM mahasiswa')
    ->fetchColumn();

$totalProdi = (int) $pdo
    ->query('SELECT COUNT(DISTINCT program_studi) FROM mahasiswa')
    ->fetchColumn();

$semesterTertinggi = (int) $pdo
    ->query('SELECT COALESCE(MAX(semester), 0) FROM mahasiswa')
    ->fetchColumn();

$namaAdmin = htmlspecialchars(
    $_SESSION['admin_username'] ?? 'Admin'
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SIMA</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <a class="brand" href="index.php">
            <span class="brand-icon">S</span>
            <span>SIMA<small>Data Mahasiswa</small></span>
        </a>

        <p class="nav-label">MENU UTAMA</p>
        <a class="nav-link active" href="index.php">▦ Dashboard</a>
        <a class="nav-link" href="mahasiswa.php">▤ Data Mahasiswa</a>

        <div class="sidebar-bottom">
            <div class="admin-info">
                <div class="avatar">A</div>
                <div><?= $namaAdmin ?><small>Administrator</small></div>
            </div>
            <a class="nav-link logout-link" href="logout.php">↪ Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div>
                <p class="eyebrow">SISTEM INFORMASI AKADEMIK</p>
                <h1>Dashboard</h1>
            </div>
            <span class="admin-badge">Admin</span>
        </header>

        <section class="welcome">
            <div>
                <p class="eyebrow">SELAMAT DATANG</p>
                <h2>Halo, <?= $namaAdmin ?>!</h2>
                <p>Kelola data mahasiswa dengan lebih mudah di sini.</p>
            </div>
            <div class="welcome-symbol">▤</div>
        </section>

        <section class="stats-grid">
            <article class="stat-card">
                <div class="stat-icon icon-blue">♙</div>
                <p>Total Mahasiswa</p>
                <h2><?= $totalMahasiswa ?></h2>
                <span>Data mahasiswa terdaftar</span>
            </article>

            <article class="stat-card">
                <div class="stat-icon icon-purple">▦</div>
                <p>Jumlah Program Studi</p>
                <h2><?= $totalProdi ?></h2>
                <span>Program studi berbeda</span>
            </article>

            <article class="stat-card">
                <div class="stat-icon icon-orange">◷</div>
                <p>Semester Tertinggi</p>
                <h2><?= $semesterTertinggi ?></h2>
                <span>Semester dari data terdaftar</span>
            </article>
        </section>

        <section class="panel dashboard-panel">
            <div class="panel-heading">
                <div>
                    <h2>Pengelolaan Data</h2>
                    <p>Pilih menu untuk mulai mengelola mahasiswa.</p>
                </div>
            </div>
            <div class="quick-actions">
                <div class="quick-action">
                    <div class="quick-icon">▤</div>
                    <h3>Data Mahasiswa</h3>
                    <p>Lihat, cari, tambah, edit, dan hapus data.</p>
                    <a class="btn btn-primary"
                       href="mahasiswa.php">Buka Data Mahasiswa →</a>
                </div>
            </div>
        </section>
    </main>
</div>
</body>
</html>
