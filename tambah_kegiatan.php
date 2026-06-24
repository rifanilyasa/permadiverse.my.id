<?php
session_start();

// Cek role admin
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

// Handle submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul']);
    $tanggalInput = $_POST['tanggal'];
    $tanggal = date('d M Y', strtotime($tanggalInput));
    $deskripsi = trim($_POST['deskripsi']);
    $gambarList = [];

    // Upload banyak gambar
    if (!empty($_FILES['gambar']['name'][0])) {
        $targetDir = 'uploads/';
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        foreach ($_FILES['gambar']['name'] as $key => $name) {
            $tmpName = $_FILES['gambar']['tmp_name'][$key];
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $uniqueName = time() . '_' . $key . '.' . $ext;
            $targetFile = $targetDir . $uniqueName;

            if (move_uploaded_file($tmpName, $targetFile)) {
                $gambarList[] = $uniqueName;
            }
        }
    }

    if (empty($error)) {
        // Simpan ke file JSON
        $dataFile = 'data/kegiatan.json';
        if (!file_exists('data')) {
            mkdir('data', 0777, true);
        }

        $kegiatan = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];

        $kegiatan[] = [
            'id' => time(),
            'judul' => $judul,
            'tanggal' => $tanggal,
            'oleh' => $_SESSION['nama'],
            'deskripsi' => $deskripsi,
            'gambar' => $gambarList
        ];

        file_put_contents($dataFile, json_encode($kegiatan, JSON_PRETTY_PRINT));
        $success = 'Kegiatan berhasil ditambahkan!';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Kegiatan - PERMADI</title>
  <link rel="icon" href="/title.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f4f6f9; font-family: 'Poppins', sans-serif; }
  </style>
</head>
<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>
<main class="flex-fill">
  <div class="container mt-4">
    <h3 class="mb-4 text-center fw-bold">Tambah Kegiatan Baru</h3>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= $success ?></div>
    <?php elseif ($error): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Judul Kegiatan</label>
        <input type="text" name="judul" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Tanggal</label>
        <input type="date" name="tanggal" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Deskripsi</label>
        <textarea name="deskripsi" class="form-control" rows="4" required></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Upload Gambar (Bisa lebih dari satu)</label>
        <input type="file" name="gambar[]" class="form-control" multiple>
      </div>
      <button type="submit" class="btn btn-dark w-100 mt-2">Simpan Kegiatan</button>
    </form>
  </div>
</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>