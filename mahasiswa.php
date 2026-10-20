<?php
require_once 'koneksi.php';
require_once 'auth.php';
wajibLogin();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function validasiCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) ||
        !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        exit('Permintaan tidak valid. Silakan muat ulang halaman.');
    }
}

function kembali(string $pesan, string $jenis = 'success'): void
{
    $_SESSION['flash'] = ['pesan' => $pesan, 'jenis' => $jenis];
    header('Location: mahasiswa.php');
    exit;
}

$pesanForm = '';
$jenisForm = 'error';
$editData = null;

/* PROSES TAMBAH, EDIT, DAN HAPUS */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validasiCsrf();
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'hapus') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            kembali('ID mahasiswa tidak valid.', 'error');
        }

        $stmt = $pdo->prepare('DELETE FROM mahasiswa WHERE id = ?');
        $stmt->execute([$id]);

        kembali(
            $stmt->rowCount() ? 'Data mahasiswa berhasil dihapus.'
                : 'Data mahasiswa tidak ditemukan.',
            $stmt->rowCount() ? 'success' : 'error'
        );
    }

    if ($aksi === 'simpan' || $aksi === 'update') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $nim = trim($_POST['nim'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $prodi = trim($_POST['program_studi'] ?? '');
        $semesterInput = $_POST['semester'] ?? '';

        $semester = filter_var(
            $semesterInput,
            FILTER_VALIDATE_INT
        );

        if (
            $nim === '' || strlen($nim) > 20 ||
            $nama === '' || strlen($nama) > 100 ||
            $prodi === '' || strlen($prodi) > 100 ||
            !preg_match('/^[a-zA-Z0-9\/-]+$/', $nim) ||
            !$semester || $semester < 1 || $semester > 8
        ) {
            $pesanForm = 'Periksa kembali data. NIM hanya boleh berisi huruf, angka, garis miring, atau tanda hubung. Semester harus 1–8.';
        } elseif ($aksi === 'update' && (!$id || $id < 1)) {
            $pesanForm = 'ID mahasiswa tidak valid.';
        } else {
            try {
                if ($aksi === 'simpan') {
                    $stmt = $pdo->prepare(
                        'INSERT INTO mahasiswa
                         (nim, nama, program_studi, semester)
                         VALUES (?, ?, ?, ?)'
                    );
                    $stmt->execute([$nim, $nama, $prodi, $semester]);
                    kembali('Data mahasiswa berhasil ditambahkan.');
                } else {
                    $stmt = $pdo->prepare(
                        'UPDATE mahasiswa
                         SET nim = ?, nama = ?, program_studi = ?,
                             semester = ?
                         WHERE id = ?'
                    );
                    $stmt->execute([
                        $nim, $nama, $prodi, $semester, $id
                    ]);

                    kembali(
                        'Data mahasiswa berhasil diperbarui.',
                        'success'
                    );
                }
            } catch (PDOException $ex) {
                if ($ex->getCode() === '23000') {
                    $pesanForm = 'NIM sudah digunakan. Silakan masukkan NIM lain.';
                } else {
                    error_log($ex->getMessage());
                    $pesanForm = 'Terjadi kesalahan saat menyimpan data.';
                }
            }

            if ($aksi === 'update') {
                $editData = [
                    'id' => $id,
                    'nim' => $nim,
                    'nama' => $nama,
                    'program_studi' => $prodi,
                    'semester' => $semesterInput
                ];
            }
        }
    }
}

/* AMBIL PESAN SETELAH PROSES */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

/* MODE EDIT */
if (isset($_GET['edit']) && !$editData) {
    $idEdit = filter_var($_GET['edit'], FILTER_VALIDATE_INT);

    if ($idEdit && $idEdit > 0) {
        $stmt = $pdo->prepare('SELECT * FROM mahasiswa WHERE id = ?');
        $stmt->execute([$idEdit]);
        $editData = $stmt->fetch() ?: null;
    }
}

/* PENCARIAN DAN DAFTAR MAHASISWA */
$cari = trim($_GET['cari'] ?? '');

if ($cari !== '') {
    $stmt = $pdo->prepare(
        'SELECT * FROM mahasiswa
         WHERE nim LIKE ? OR nama LIKE ?
         ORDER BY id DESC'
    );
    $kata = '%' . $cari . '%';
    $stmt->execute([$kata, $kata]);
    $mahasiswa = $stmt->fetchAll();
} else {
    $mahasiswa = $pdo->query(
        'SELECT * FROM mahasiswa ORDER BY id DESC'
    )->fetchAll();
}

$totalMahasiswa = (int) $pdo
    ->query('SELECT COUNT(*) FROM mahasiswa')
    ->fetchColumn();

$namaAdmin = e($_SESSION['admin_username'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mahasiswa - SIMA</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .action-buttons {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .action-buttons a.btn-edit {
            display: inline-block;
            text-decoration: none;
            cursor: pointer;
            position: relative;
            z-index: 2;
        }
        .action-buttons form.delete-form {
            margin: 0;
            padding: 0;
            display: inline-block;
        }
    </style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <a class="brand" href="index.php">
            <span class="brand-icon">S</span>
            <span>SIMA<small>Data Mahasiswa</small></span>
        </a>

        <p class="nav-label">MENU UTAMA</p>
        <a class="nav-link" href="index.php">▦ Dashboard</a>
        <a class="nav-link active" href="mahasiswa.php">▤ Data Mahasiswa</a>

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
                <p class="eyebrow">PENGELOLAAN AKADEMIK</p>
                <h1>Data Mahasiswa</h1>
            </div>
            <span class="admin-badge">Admin</span>
        </header>

        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['jenis']) ?>">
                <?= e($flash['pesan']) ?>
            </div>
        <?php endif; ?>

        <?php if ($pesanForm): ?>
            <div class="alert alert-<?= e($jenisForm) ?>">
                <?= e($pesanForm) ?>
            </div>
        <?php endif; ?>

        <section class="panel">
            <div class="panel-heading">
                <div>
                    <h2><?= $editData ? 'Edit Mahasiswa' : 'Tambah Mahasiswa' ?></h2>
                    <p>Isi informasi mahasiswa pada kolom berikut.</p>
                </div>
            </div>

            <form method="POST" action="mahasiswa.php" class="student-form">
                <input type="hidden" name="csrf_token"
                       value="<?= e($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="aksi"
                       value="<?= $editData ? 'update' : 'simpan' ?>">

                <?php if ($editData): ?>
                    <input type="hidden" name="id"
                           value="<?= e($editData['id']) ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="nim">NIM</label>
                        <input type="text" id="nim" name="nim"
                               maxlength="20" required
                               value="<?= e($editData['nim'] ?? '') ?>"
                               placeholder="Contoh: 2301001"
                               <?= $editData ? 'autofocus' : '' ?>>
                    </div>

                    <div class="form-group">
                        <label for="nama">Nama Lengkap</label>
                        <input type="text" id="nama" name="nama"
                               maxlength="100" required
                               value="<?= e($editData['nama'] ?? '') ?>"
                               placeholder="Nama mahasiswa">
                    </div>

                    <div class="form-group">
                        <label for="program_studi">Program Studi</label>
                        <select id="program_studi" name="program_studi" required>
                            <option value="">Pilih program studi</option>
                            <?php
                            $daftarProdi = [
                                'Sistem Informasi',
                                'Manajemen Informatika',
                                'Bisnis Digital'
                            ];
                            
                            foreach ($daftarProdi as $prodi): ?>
                                <option value="<?= e($prodi) ?>"
                                    <?= ($editData['program_studi'] ?? '') === $prodi ? 'selected' : '' ?>>
                                    <?= e($prodi) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="semester">Semester</label>
                        <select id="semester" name="semester" required>
                            <option value="">Pilih semester</option>
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                                <option value="<?= $i ?>"
                                    <?= (string)($editData['semester'] ?? '') === (string)$i ? 'selected' : '' ?>>
                                    Semester <?= $i ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?= $editData ? 'Simpan Perubahan' : '+ Tambah Mahasiswa' ?>
                    </button>

                    <?php if ($editData): ?>
                        <a class="btn btn-secondary" href="mahasiswa.php">
                            Batal
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <section class="panel table-panel">
            <div class="panel-heading">
                <div>
                    <h2>Daftar Mahasiswa</h2>
                    <p><?= $totalMahasiswa ?> mahasiswa tersimpan di database.</p>
                </div>
                <span class="count-badge"><?= count($mahasiswa) ?> hasil</span>
            </div>

            <form method="GET" action="mahasiswa.php" class="search-form">
                <input type="search" name="cari"
                       value="<?= e($cari) ?>"
                       placeholder="Cari berdasarkan NIM atau nama...">
                <button class="btn btn-primary" type="submit">Cari</button>
                <?php if ($cari !== ''): ?>
                    <a class="btn btn-secondary" href="mahasiswa.php">Reset</a>
                <?php endif; ?>
            </form>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>Program Studi</th>
                            <th>Semester</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$mahasiswa): ?>
                        <tr>
                            <td colspan="6" class="empty-state">
                                Data mahasiswa tidak ditemukan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($mahasiswa as $no => $m): ?>
                            <tr>
                                <td><?= $no + 1 ?></td>
                                <td><strong><?= e($m['nim']) ?></strong></td>
                                <td><?= e($m['nama']) ?></td>
                                <td><?= e($m['program_studi']) ?></td>
                                <td><span class="semester-badge">
                                    Semester <?= e($m['semester']) ?>
                                </span></td>
                                <td>
                                    <div class="action-buttons">
                                        <a class="btn btn-edit" href="mahasiswa.php?edit=<?= (int)$m['id'] ?>">
                                            Edit
                                        </a>

                                        <form method="POST" action="mahasiswa.php"
                                              class="delete-form"
                                              onsubmit="return konfirmasiHapus('<?= e($m['nim']) ?>')">
                                            <input type="hidden" name="csrf_token"
                                                   value="<?= e($_SESSION['csrf_token']) ?>">
                                            <input type="hidden" name="aksi" value="hapus">
                                            <input type="hidden" name="id"
                                                   value="<?= (int)$m['id'] ?>">
                                            <button class="btn btn-delete"
                                                    type="submit">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<script>
function konfirmasiHapus(nim) {
    return confirm('Apakah kamu yakin ingin menghapus mahasiswa dengan NIM ' + nim + '?');
}

/* Otomatis scroll ke paling atas saat berada dalam mode edit */
<?php if ($editData): ?>
    window.onload = function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        var mainContent = document.querySelector('.main-content');
        if (mainContent) mainContent.scrollTop = 0;
    };
<?php endif; ?>
</script>
</body>
</html>