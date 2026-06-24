<?php
session_start();
$kegiatan = [];

// Baca data kegiatan dari file JSON di folder data
if (file_exists('data/kegiatan.json')) {
    $kegiatan = json_decode(file_get_contents('data/kegiatan.json'), true);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Daftar Kegiatan - PERMADI</title>
  <link rel="icon" href="/title.png" type="image/png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f4f6f9;
      font-family: 'Poppins', sans-serif;
    }
    .card-img-top {
      max-height: 250px;
      object-fit: cover;
      transition: 0.3s;
    }
    .card-img-top:hover {
      transform: scale(1.02);
      cursor: pointer;
    }
    .card {
      border: none;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .judul-section {
      text-align: center;
      padding-top: 1px;
      padding-bottom: 20px;
    }
    .modal-content {
      background-color: #fff;
      border-radius: 10px;
      padding: 15px;
      text-align: center;
    }
    .modal-content img {
      max-height: 80vh;
      width: auto;
    }
  </style>
</head>
<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>

<main class="flex-fill">
  <div class="container mt-4">
    <h2 class="fw-bold judul-section">Daftar Kegiatan</h2>

    <?php if (empty($kegiatan)): ?>
      <div class="alert alert-warning">Belum ada kegiatan yang ditambahkan.</div>
    <?php else: ?>
      <div class="row">
        <?php $loopIndex = 0; ?>
        <?php foreach (array_reverse($kegiatan) as $k): ?>
          <?php
            $gambarArray = is_array($k['gambar']) ? $k['gambar'] : [$k['gambar']];
            $thumbnail = !empty($gambarArray[0]) ? $gambarArray[0] : '';
          ?>
          <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 d-flex flex-column">
              <?php if (!empty($thumbnail)): ?>
                <img src="uploads/<?= htmlspecialchars($thumbnail) ?>"
                    class="card-img-top"
                    alt="Foto Kegiatan"
                    data-bs-toggle="modal"
                    data-bs-target="#modalGambar<?= $loopIndex ?>">
              <?php endif; ?>

              <div class="card-body d-flex flex-column">
                <h5 class="card-title fw-bold"><?= htmlspecialchars($k['judul']) ?></h5>
                <p class="card-text flex-grow-1">
                  <?= htmlspecialchars(substr($k['deskripsi'], 0, 120)) ?>...
                </p>
                <a href="kegiatan_detail.php?id=<?= $k['id'] ?>" class="btn btn-sm btn-outline-dark mt-auto">Lihat Selengkapnya</a>
              </div>

              <div class="card-footer text-muted small">
                <?= date('d M Y', strtotime($k['tanggal'])) ?>
                <?php if (!empty($k['oleh'])): ?>
                  <div>Diunggah oleh: <strong><?= htmlspecialchars($k['oleh']) ?></strong></div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Modal untuk gambar ukuran penuh + download -->
          <div class="modal fade" id="modalGambar<?= $loopIndex ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
              <div class="modal-content">
                <?php if (count($gambarArray) > 1): ?>
                  <div id="carousel<?= $loopIndex ?>" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                      <?php foreach ($gambarArray as $gIdx => $g): ?>
                        <div class="carousel-item <?= $gIdx === 0 ? 'active' : '' ?>">
                          <img src="uploads/<?= htmlspecialchars($g) ?>" class="d-block w-100 rounded mb-3" style="max-height: 500px; object-fit: contain;">
                          <a href="uploads/<?= htmlspecialchars($g) ?>" download class="btn btn-outline-dark btn-sm">
                            Download
                          </a>
                        </div>
                      <?php endforeach; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carousel<?= $loopIndex ?>" data-bs-slide="prev">
                      <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carousel<?= $loopIndex ?>" data-bs-slide="next">
                      <span class="carousel-control-next-icon"></span>
                    </button>
                  </div>
                <?php else: ?>
                  <img src="uploads/<?= htmlspecialchars($thumbnail) ?>" class="img-fluid rounded shadow mb-3">
                  <br>
                  <a href="uploads/<?= htmlspecialchars($thumbnail) ?>" download class="btn btn-outline-dark btn-sm">
                    Download Gambar
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <?php $loopIndex++; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
