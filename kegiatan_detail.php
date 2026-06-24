<?php
session_start();

// Cek jika ada parameter ID
if (!isset($_GET['id'])) {
    header("Location: kegiatan.php");
    exit;
}

$id = $_GET['id'];
$kegiatan = [];

if (file_exists('data/kegiatan.json')) {
    $data = json_decode(file_get_contents('data/kegiatan.json'), true);
    foreach ($data as $item) {
        if ($item['id'] == $id) {
            $kegiatan = $item;
            break;
        }
    }
}

// Jika kegiatan tidak ditemukan
if (!$kegiatan) {
    echo "<h3 class='text-center mt-5'>Kegiatan tidak ditemukan.</h3>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($kegiatan['judul']) ?> - PERMADI</title>
  <link rel="icon" href="/title.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f4f6f9; font-family: 'Poppins', sans-serif; }
    .carousel-inner img {
      max-height: 480px;
      object-fit: contain;
      border-radius: 10px;
    }
    .carousel-control-prev-icon,
    .carousel-control-next-icon {
      filter: invert(1);
    }
  </style>
</head>
<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>

<main class="flex-fill">
  <div class="container mt-3">
    <a href="kegiatan.php" class="btn btn-sm btn-danger mb-3">&larr; Kembali ke Daftar Kegiatan</a>

    <h2><?= htmlspecialchars($kegiatan['judul']) ?></h2>
    <div class="text-muted small mb-3">
      <?= date('d M Y', strtotime($kegiatan['tanggal'])) ?>
      <?php if (!empty($kegiatan['oleh'])): ?>
        <span class="ms-3">Diunggah oleh: <strong><?= htmlspecialchars($kegiatan['oleh']) ?></strong></span>
      <?php endif; ?>
    </div>

    <?php
      $gambarArray = is_array($kegiatan['gambar']) ? $kegiatan['gambar'] : [$kegiatan['gambar']];
    ?>

  <?php if (!empty($gambarArray[0])): ?>
    <div id="carouselGambar" class="carousel slide mb-3" data-bs-ride="carousel">
      <div class="carousel-inner">
        <?php foreach ($gambarArray as $index => $g): ?>
          <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
            <img src="uploads/<?= htmlspecialchars($g) ?>" class="d-block w-100 img-fluid" alt="Gambar <?= $index + 1 ?>">
          </div>
        <?php endforeach; ?>
      </div>
      <?php if (count($gambarArray) > 1): ?>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselGambar" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselGambar" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </button>
      <?php endif; ?>
    </div>

    <!-- Tombol download dinamis -->
    <div class="mb-4 text-center">
      <a id="downloadLink" href="uploads/<?= htmlspecialchars($gambarArray[0]) ?>" download class="btn btn-outline-dark btn-sm">
        Download Gambar
      </a>
    </div>
  <?php endif; ?>

    <div class="mt-4">
      <p><?= nl2br(htmlspecialchars($kegiatan['deskripsi'])) ?></p>
    </div>
  </div>
</main>
<script>
  const gambarList = <?= json_encode($gambarArray) ?>;
  const downloadLink = document.getElementById('downloadLink');
  const carousel = document.getElementById('carouselGambar');

  carousel.addEventListener('slid.bs.carousel', function (event) {
    const index = event.to;
    const namaFile = gambarList[index];
    downloadLink.href = 'uploads/' + encodeURIComponent(namaFile);
  });
</script>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
