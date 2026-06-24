<?php
session_start();

// Jika form dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');

    if (!$username) {
        $_SESSION['flash_error'] = 'Username wajib diisi.';
        header('Location: request_reset.php');
        exit;
    }

    $usersFile   = __DIR__ . '/users.json';
    $requestFile = __DIR__ . '/reset_requests.json';

    if (!file_exists($usersFile)) {
        $_SESSION['flash_error'] = 'File users.json tidak ditemukan.';
        header('Location: request_reset.php');
        exit;
    }

    $users = json_decode(file_get_contents($usersFile), true);
    $userFound = null;
    foreach ($users as $u) {
        if ($u['username'] === $username) {
            $userFound = $u;
            break;
        }
    }

    if (!$userFound) {
        $_SESSION['flash_error'] = 'Username tidak ditemukan.';
        header('Location: request_reset.php');
        exit;
    }

    $requests = file_exists($requestFile)
        ? json_decode(file_get_contents($requestFile), true)
        : [];

    // Hindari duplikasi permintaan
    foreach ($requests as $req) {
        if ($req['username'] === $username) {
            $_SESSION['flash_error'] = "Pengajuan reset password atas username '{$username}' sudah pernah diajukan, silakan konfirmasi ke admin.";
            header('Location: request_reset.php');
            exit;
        }
    }

    // Tambah permintaan baru
    $requests[] = [
        'username' => $username,
        'nama'     => $userFound['nama'],
        'waktu'    => date('Y-m-d H:i:s')
    ];
    file_put_contents($requestFile, json_encode($requests, JSON_PRETTY_PRINT));

    $_SESSION['flash_success'] = 'Permintaan reset dikirim ke admin.';
    header('Location: request_reset.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Lupa Password - PERMADI</title>
  <link rel="icon" href="/title.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>
<main class="flex-fill">
  <div class="container mt-5">
    <h3 class="text-center mb-4">Permintaan Reset Password</h3>
    <form method="POST" class="card p-4 shadow mx-auto" style="max-width: 400px;">
      <div class="mb-3">
        <label class="form-label">Masukkan Username Anda</label>
        <input type="text" name="username" class="form-control" required>
      </div>
      <button class="btn btn-primary w-100">Kirim Permintaan</button>
    </form>
    <p class="text-center mt-3"><a href="/login.php">Kembali ke Login</a></p>
  </div>
</main>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    <?php if (!empty($_SESSION['flash_success'])): ?>
    Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: '<?= addslashes($_SESSION['flash_success']); ?>'
    });
    <?php unset($_SESSION['flash_success']); endif; ?>

    <?php if (!empty($_SESSION['flash_error'])): ?>
    Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: '<?= addslashes($_SESSION['flash_error']); ?>'
    });
    <?php unset($_SESSION['flash_error']); endif; ?>
  });
</script>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
