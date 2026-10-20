
<?php
require_once 'koneksi.php';

$username = 'admin';
$passwordAwal = 'Admin123!';

$stmt = $pdo->prepare(
    'SELECT id FROM admin WHERE username = ?'
);
$stmt->execute([$username]);

if ($stmt->fetch()) {
    exit('Akun admin sudah tersedia. Silakan login.');
}

$hash = password_hash($passwordAwal, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    'INSERT INTO admin (username, password) VALUES (?, ?)'
);
$stmt->execute([$username, $hash]);

echo 'Akun admin berhasil dibuat. ';
echo 'Username: admin. Password awal: Admin123!. ';
echo 'Hapus file buat_admin.php setelah selesai.';
