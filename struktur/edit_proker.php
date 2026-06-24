<?php
$file = __DIR__ . '/proker.json';
$data = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

$seksi = $_GET['seksi'] ?? '';
$index = $_GET['index'] ?? '';

if (!isset($data[$seksi][$index])) {
    die("Data tidak ditemukan.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data[$seksi][$index]['nama'] = $_POST['nama'];
    $data[$seksi][$index]['waktu'] = $_POST['waktu'];
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    header("Location: tambah_proker.php?sukses=1");
    exit;
}

$proker = $data[$seksi][$index];
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Program Kerja</title>
  <link rel="icon" href="/title.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>
<main class="flex-fill">
  <div class="container py-5">
    <h2 class="mb-4">Edit Program Kerja</h2>
    <form method="POST">
      <div class="mb-3">
        <label>Nama Program</label>
        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($proker['nama']) ?>" required>
      </div>
      <div class="mb-3">
        <label>Waktu Pelaksanaan</label>
        <input type="text" name="waktu" class="form-control" value="<?= htmlspecialchars($proker['waktu']) ?>" required>
      </div>
      <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      <a href="tambah_proker.php" class="btn btn-secondary">Batal</a>
    </form>
  </div>
</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>