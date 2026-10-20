
<?php
require_once 'koneksi.php';
require_once 'auth.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        'SELECT id, username, password FROM admin WHERE username = ?'
    );
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];

        header('Location: index.php');
        exit;
    }

    $error = 'Username atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - SIMA</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">
    <div class="login-card">
        <div class="brand-icon">S</div>
        <h1>SIMA</h1>
        <p class="muted">Sistem Informasi Data Mahasiswa</p>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label for="username">Username</label>
            <input type="text" id="username" name="username"
                   placeholder="Masukkan username" required
                   autocomplete="username">

            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   placeholder="Masukkan password" required
                   autocomplete="current-password">

            <button class="btn btn-primary btn-full" type="submit">
                Login
            </button>
        </form>

        <p class="login-footer">Gunakan akun admin untuk melanjutkan.</p>
    </div>
</body>
</html>
