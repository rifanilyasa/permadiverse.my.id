<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: /login.php");
    exit;
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];

$users_file = $_SERVER['DOCUMENT_ROOT'] . '/Users/users.json';
$users = json_decode(file_get_contents($users_file), true);

// Cari data user aktif
$current_user = null;
foreach ($users as $u) {
    if ($u['username'] === $username) {
        $current_user = $u;
        break;
    }
}

// Cek role admin atau PJ
$isPJ = false;
if ($current_user) {
    $jabatans = (array)($current_user['jabatan'] ?? []);
    foreach ($jabatans as $j) {
        if (stripos($j, 'PJ Seksi') !== false) {
            $isPJ = true;
            break;
        }
    }
}

if ($role !== 'admin' && !$isPJ) {
    echo "<h4 class='text-center mt-5 text-danger'>Akses ditolak. Hanya admin atau PJ yang dapat menambahkan program kerja.</h4>";
    exit;
}

$usersFile = 'users.json';
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];
$success = $error = '';

// Tambah user satuan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_satuan'])) {
  $nama = trim($_POST['nama']);
  $username = trim($_POST['username']);
  $password = $_POST['password'];
  $roleInput = $_POST['role'] ?? 'user';

  if (!$nama || !$username || !$password) {
    $_SESSION['popup_error'] = 'Semua kolom wajib diisi.';
    $_SESSION['form_input'] = $_POST;
    header('Location: tambah_user.php');
    exit;
  } else {
    if ($roleInput === 'admin' && $_SESSION['role'] !== 'admin') {
        $_SESSION['popup_error'] = 'Hanya admin yang bisa membuat akun admin.';
        $_SESSION['form_input'] = $_POST;
        header('Location: tambah_user.php');
        exit;
    }

    $exists = array_filter($users, fn($u) => $u['username'] === $username);
    if ($exists) {
        $_SESSION['popup_error'] = 'Username sudah digunakan.';
        $_SESSION['form_input'] = $_POST;
        header('Location: tambah_user.php');
        exit;
    } else {
      $users[] = [
        'nama' => $nama,
        'username' => $username,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'role' => $roleInput,
        'jabatan' => [],
        'foto' => null
      ];
      file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
      $_SESSION['popup_success'] = "User '$username' berhasil ditambahkan.";
      header('Location: tambah_user.php');
      exit;
    }
  }
}

// Upload massal dari .txt
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_massal'])) {
  if (isset($_FILES['user_file']) && is_uploaded_file($_FILES['user_file']['tmp_name'])) {
    $lines = file($_FILES['user_file']['tmp_name'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $added = 0;
    $errors = [];

    foreach ($lines as $i => $line) {
      $parts = explode('|', $line);
      if (count($parts) < 6) {
        $errors[] = "Baris " . ($i+1) . " tidak valid.";
        continue;
      }

      list($nama, $username, $password, $roleLine, $jabatan, $foto) = $parts;
      if (!$nama || !$username || !$password || !in_array($roleLine, ['user', 'admin'])) {
        $errors[] = "Baris " . ($i+1) . " data tidak lengkap / role tidak valid.";
        continue;
      }

      if ($roleLine === 'admin' && $_SESSION['role'] !== 'admin') {
        $errors[] = "Tidak diizinkan menambahkan admin (baris " . ($i+1) . ")";
        continue;
      }

      $exists = array_filter($users, fn($u) => $u['username'] === $username);
      if ($exists) {
        $errors[] = "Username '$username' sudah digunakan (baris " . ($i+1) . ")";
        continue;
      }

      $users[] = [
        'nama' => $nama,
        'username' => $username,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'role' => $roleLine,
        'jabatan' => [],
        'foto' => null
      ];
      $added++;
    }

    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
    $_SESSION['popup_success'] = "$added user berhasil ditambahkan.";
    $_SESSION['popup_error'] = $errors ? implode('<br>', $errors) : '';
    header('Location: tambah_user.php');
    exit;
  } else {
    $error = 'File gagal diunggah.';
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah User - PERMADI</title>
  <link rel="icon" href="/title.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>
<main class="flex-fill">
  <div class="container mt-4">
    <div class="text-center mt-4">
        <h2>Tambah User Baru</h2>
    </div>
    <?php
    $old = $_SESSION['form_input'] ?? [];
    unset($_SESSION['form_input']); // Hapus setelah digunakan
    ?>
    <!-- Form Tambah Satuan -->
    <form method="post" class="mb-5">
      <input type="hidden" name="submit_satuan" value="1">
      <div class="mb-3">
        <label class="form-label">Nama</label>
        <input type="text" name="nama" class="form-control" required value="<?= htmlspecialchars($old['nama'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($old['username'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required value="">
      </div>
      <div class="mb-3">
        <label class="form-label">Role</label>
        <select name="role" class="form-select" required>
          <option value="user">User</option>
          <option value="admin">Admin</option>
        </select>
      </div>
      <button class="btn btn-primary" type="submit">Tambah User</button>
    </form>

    <hr>

    <!-- Form Upload Massal -->
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="submit_massal" value="1">
      <h5>Upload Banyak User (.txt)</h5>
      <div class="mb-3">
        <label class="form-label">Pilih File TXT</label>
        <input type="file" name="user_file" class="form-control" accept=".txt" required>
      </div>
      <button class="btn btn-success" type="submit">Upload & Tambah</button>
      <a href="/Users/template_user.txt" class="btn btn-outline-secondary ms-2">Download Template</a>
    </form>

    <div class="mt-4">
      <h6>Format Baris TXT:</h6>
      <pre>nama|username|password|user||</pre>
    </div>
  </div>
</main>

<script>
<?php if (!empty($_SESSION['popup_success'])): ?>
  Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    html: <?= json_encode($_SESSION['popup_success']) ?>,
    confirmButtonText: 'OK'
  });
  <?php unset($_SESSION['popup_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['popup_error'])): ?>
  Swal.fire({
    icon: 'error',
    title: 'Gagal!',
    html: <?= json_encode($_SESSION['popup_error']) ?>,
    confirmButtonText: 'Tutup'
  });
  <?php unset($_SESSION['popup_error']); ?>
<?php endif; ?>
</script>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>