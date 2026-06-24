<?php
session_start();
require '../vendor/autoload.php'; // jika ada autoload, atau hapus jika tidak

// File proker dan users
$file = __DIR__ . '/proker.json';
$usersFile = $_SERVER['DOCUMENT_ROOT'] . '/Users/users.json';

// Ambil data proker
$data = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

// Ambil data users untuk mengetahui jabatan current
$allUsers = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];
$currentUser = null;
foreach ($allUsers as $u) {
    if ($u['username'] === ($_SESSION['username'] ?? '')) {
        $currentUser = $u;
        break;
    }
}

// Tentukan seksi yang boleh diakses
$availableSeksi = array_keys($data);
if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin') {
    // Jika user biasa, filter berdasarkan jabatan PJ Seksi
    $jabatanArr = (array)($currentUser['jabatan'] ?? []);
    $pjSeksiNames = [];
    foreach ($jabatanArr as $jab) {
        if (stripos($jab, 'PJ Seksi ') === 0) {
            // Hanya seksi setelah prefix
            $pjSeksiNames[] = substr($jab, strlen('PJ Seksi '));
        }
    }
    // Intersect dengan seksi existing
    $availableSeksi = array_values(array_intersect($availableSeksi, $pjSeksiNames));
}

// Tambah Program Kerja
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seksi = $_POST['seksi'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $waktu = $_POST['waktu'] ?? '';

    // Validasi: seksi harus di availableSeksi
    if (!in_array($seksi, $availableSeksi)) {
        die('Unauthorized section.');
    }

    if (!isset($data[$seksi])) {
        $data[$seksi] = [];
    }
    $no = count($data[$seksi]) + 1;
    $data[$seksi][] = ["no" => $no, "nama" => $nama, "waktu" => $waktu];

    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    header("Location: tambah_proker.php?sukses=1");
    exit;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Program Kerja - PERMADI</title>
  <link rel="icon" href="/title.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>
<main class="flex-fill">
  <div class="container py-5">
    <h2 class="mb-4 fw-bold">Tambah Program Kerja</h2>
    <form method="POST" class="mb-5">
      <div class="mb-3">
        <label>Seksi</label>
        <select name="seksi" class="form-select" required>
          <option value="">Pilih Seksi</option>
          <?php foreach ($availableSeksi as $sek): ?>
            <option value="<?= htmlspecialchars($sek) ?>"><?= htmlspecialchars($sek) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3">
        <label>Nama Program</label>
        <input type="text" name="nama" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Waktu Pelaksanaan</label>
        <input type="text" name="waktu" class="form-control" required placeholder="Contoh: Feb 2025/Triwulan/Tahunan">
      </div>
      <button type="submit" class="btn btn-success">Simpan</button>
    </form>

    <h3 class="mb-3">Daftar Program Kerja</h3>
    <?php foreach ($availableSeksi as $seksi => $itemsKey): ?>
      <?php $items = $data[$itemsKey] ?? []; ?>
      <h5><?= htmlspecialchars($itemsKey) ?></h5>
      <table class="table table-bordered table-sm mb-4">
        <thead class="table-success text-center">
          <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Waktu</th>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <th>Aksi</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $index => $row): ?>
          <tr>
            <td><?= $row['no'] ?></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= htmlspecialchars($row['waktu']) ?></td>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <td class="text-center">
              <a href="edit_proker.php?seksi=<?= urlencode($itemsKey) ?>&index=<?= $index ?>" class="btn btn-sm btn-warning">Edit</a>
              <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalHapus<?= md5($itemsKey . $index) ?>">
                Hapus
              </button>
            </td>
            <?php endif; ?>
          </tr>

          <!-- Modal Hapus hanya untuk admin -->
          <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
          <div class="modal fade" id="modalHapus<?= md5($itemsKey . $index) ?>" tabindex="-1" aria-labelledby="hapusLabel<?= md5($itemsKey . $index) ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                  <h5 class="modal-title" id="hapusLabel<?= md5($itemsKey . $index) ?>">Konfirmasi Hapus</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                  Yakin ingin menghapus program kerja <strong><?= htmlspecialchars($row['nama']) ?></strong>?
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                  <a href="hapus_proker.php?seksi=<?= urlencode($itemsKey) ?>&index=<?= $index ?>" class="btn btn-danger">Ya, Hapus</a>
                </div>
              </div>
            </div>
          </div>
          <?php endif; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endforeach; ?>

  </div>
</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
