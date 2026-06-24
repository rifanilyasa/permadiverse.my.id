<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak.");
}

$usersFile = __DIR__ . '/users.json';
$requestFile = __DIR__ . '/reset_requests.json';

$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];
$requests = file_exists($requestFile) ? json_decode(file_get_contents($requestFile), true) : [];

// Reset password jika diminta
if (isset($_GET['reset']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $resetUser = $_GET['reset'];
    foreach ($users as &$u) {
        if ($u['username'] === $resetUser) {
            $u['password'] = password_hash('123456', PASSWORD_DEFAULT);
            break;
        }
    }
    // Hapus permintaan dari daftar
    $requests = array_filter($requests, fn($r) => $r['username'] !== $resetUser);
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
    file_put_contents($requestFile, json_encode(array_values($requests), JSON_PRETTY_PRINT));
    $_SESSION['success'] = "User <strong>$resetUser</strong> berhasil direset. Gunakan password <code>123456</code>.";
    header("Location: reset_request_admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Permintaan Reset - Admin</title>
  <link rel="icon" href="/title.png" type="image/png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>
<main class="flex-fill">
  <div class="container py-4">
    <h3 class="mb-4">Permintaan Reset Password</h3>

    <?php if (isset($_SESSION['success'])): ?>
      <script>
        Swal.fire({
          icon: 'success',
          title: 'Berhasil!',
          html: <?= json_encode($_SESSION['success']) ?>,
          confirmButtonText: 'OK',
          scrollbarPadding: false
        });
      </script>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (empty($requests)): ?>
      <div class="alert alert-info">Tidak ada permintaan reset password saat ini.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>Nama</th>
              <th>Username</th>
              <th>Waktu Permintaan</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($requests as $req): ?>
              <tr>
                <td><?= htmlspecialchars($req['nama']) ?></td>
                <td><?= htmlspecialchars($req['username']) ?></td>
                <td><?= htmlspecialchars($req['waktu']) ?></td>
                <td class="text-center">
                  <a href="?reset=<?= urlencode($req['username']) ?>" class="btn btn-sm btn-danger">
                    Reset Password
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <a href="/index.php" class="btn btn-secondary mt-3">← Kembali ke Dashboard</a>
  </div>
</main>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
