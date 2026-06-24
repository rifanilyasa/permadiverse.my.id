<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: /login.php");
    exit;
}

$usersFile = __DIR__ . '/users.json';
$users = file_exists($usersFile)
    ? json_decode(file_get_contents($usersFile), true)
    : [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Daftar User (Hash) - PERMADI</title>
  <link rel="icon" href="/title.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <?php include $_SERVER['DOCUMENT_ROOT'].'/includes/header.php'; ?>
  <main class="container py-5">
    <h3 class="mb-4">Daftar User & Password Hash</h3>
    <?php
    $nonAdminUsers = array_filter($users, fn($u) => ($u['role'] ?? 'user') !== 'admin');
    if (empty($nonAdminUsers)): ?>
      <div class="alert alert-info">Tidak ada pengguna non-admin yang tersedia.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-striped table-bordered">
          <thead class="table-dark">
            <tr>
              <th>Nama</th>
              <th>Username</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($nonAdminUsers as $u): ?>
              <tr>
                <td><?= htmlspecialchars($u['nama']) ?></td>
                <td><?= htmlspecialchars($u['username']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
    <a href="/index.php" class="btn btn-secondary mt-3">Kembali</a>
  </main>
<?php include $_SERVER['DOCUMENT_ROOT'].'/includes/footer.php'; ?>