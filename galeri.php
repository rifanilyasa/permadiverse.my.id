<?php
session_start();
$galeri = [];
$gambarTerdaftar = []; // Untuk menyimpan nama file yang sudah tercatat

// Ambil semua gambar dari data kegiatan
if (file_exists('data/kegiatan.json')) {
    $data = json_decode(file_get_contents('data/kegiatan.json'), true);
    foreach ($data as $kegiatan) {
        $gambarList = is_array($kegiatan['gambar']) ? $kegiatan['gambar'] : [$kegiatan['gambar']];
        foreach ($gambarList as $gambar) {
            $galeri[] = [
                'file' => $gambar,
                'judul' => $kegiatan['judul'],
                'tanggal' => $kegiatan['tanggal']
            ];
            $gambarTerdaftar[] = $gambar;
        }
    }
}

// Tambahkan sisa gambar dari folder uploads/ yang belum tercatat
$folder = 'uploads/';
$files = array_diff(scandir($folder), ['.', '..']);

foreach ($files as $file) {
    if (
        is_file($folder . $file) &&
        preg_match('/\.(jpg|jpeg|png|gif)$/i', $file) &&
        !in_array($file, $gambarTerdaftar)
    ) {
        $galeri[] = [
            'file' => $file,
            'judul' => 'Gambar Assets',
            'tanggal' => date('Y-m-d', filemtime($folder . $file))
        ];
    }
}

usort($galeri, function ($a, $b) {
  return strtotime($b['tanggal']) - strtotime($a['tanggal']);
});

// Konfigurasi Paging
$perPage = 12;
$totalData = count($galeri);
$totalPage = ceil($totalData / $perPage);
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$start = ($page - 1) * $perPage;

// Ambil data untuk halaman saat ini
$galeriPage = array_slice($galeri, $start, $perPage);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Galeri - PERMADI</title>
  <link rel="icon" href="/title.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #ffffff;
      font-family: 'Poppins', sans-serif;
    }
    .gallery-img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 8px;
      transition: 0.3s;
      border: 3px solid #ffd05e;
    }
    .gallery-img:hover {
      transform: scale(1.02);
      cursor: pointer;
    }
    .judul-section {
      text-align: center;
      padding-top: 1px;
      padding-bottom: 20px;
    }
  </style>
</head>
<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>

<main class="flex-fill">
  <div class="container mt-4">
    <h2 class="fw-bold judul-section">Galeri Kegiatan</h2>

    <?php if (empty($galeri)): ?>
      <div class="alert alert-warning">Belum ada gambar yang diunggah.</div>
    <?php else: ?>
      <div class="row">
        <?php foreach ($galeriPage as $index => $g): ?>
          <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
            <img src="uploads/<?= htmlspecialchars($g['file']) ?>"
                class="gallery-img"
                alt="Gambar Galeri"
                data-bs-toggle="modal"
                data-bs-target="#modalGaleri<?= $index ?>">
          </div>

          <!-- Modal Zoom Gambar -->
          <div class="modal fade" id="modalGaleri<?= $index ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
              <div class="modal-content text-center p-3">
                <h5><?= htmlspecialchars($g['judul']) ?></h5>
                <p class="text-muted"><?= date('d M Y', strtotime($g['tanggal'])) ?></p>
                <img src="uploads/<?= htmlspecialchars($g['file']) ?>" class="img-fluid rounded mb-3">
                <a href="uploads/<?= htmlspecialchars($g['file']) ?>" download class="btn btn-outline-dark btn-sm">Download Gambar</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</main>
<div class="container mb-4">
  <nav>
    <ul class="pagination justify-content-center">
      <?php if ($page > 1): ?>
        <li class="page-item">
          <a class="page-link" href="?page=<?= $page - 1 ?>">&laquo; Sebelumnya</a>
        </li>
      <?php endif; ?>

      <?php for ($i = 1; $i <= $totalPage; $i++): ?>
        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
          <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>

      <?php if ($page < $totalPage): ?>
        <li class="page-item">
          <a class="page-link" href="?page=<?= $page + 1 ?>">Berikutnya &raquo;</a>
        </li>
      <?php endif; ?>
    </ul>
  </nav>
</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>