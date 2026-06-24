<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<main class="flex-fill">

  <!-- Hero Section -->
  <section class="bg-dark text-white py-5">
    <div class="container text-center">
      <h2 class="display-4 fw-bold">PERKUMPULAN PEMUDA PEMUDI</h2>
      <h1 class="display-4 fw-bold text-warning">PERMADI</h1>
      <p class="lead">Muda, Mandiri, Berkarya!</p>
    </div>
  </section>

  <!-- Section: Highlight -->
  <section class="py-3 bg-light">
    <div class="container text-center">
      <h2 class="fw-bold mb-4">Kegiatan Terbaru</h2>
      <div class="row">
        <?php
        $data_file = 'data/kegiatan.json';
        if (file_exists($data_file)) {
            $json_data = file_get_contents($data_file);
            $kegiatan = json_decode($json_data, true);
            $terbaru = array_slice(array_reverse($kegiatan), 0, 3);
            $loopIndex = 0;

            foreach ($terbaru as $item):
                $gambarArray = is_array($item['gambar']) ? $item['gambar'] : [$item['gambar']];
                $thumbnail = !empty($gambarArray[0]) ? $gambarArray[0] : '';
        ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-lg border-0 d-flex flex-column">
                        <?php if (!empty($thumbnail)): ?>
                            <img src="uploads/<?= htmlspecialchars($thumbnail) ?>" 
                                class="card-img-top" 
                                style="height: 220px; object-fit: cover; cursor:pointer"
                                data-bs-toggle="modal" 
                                data-bs-target="#modalGambar<?= $loopIndex ?>">
                        <?php endif; ?>

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-semibold"><?= htmlspecialchars($item['judul']) ?></h5>
                            <p class="card-text flex-grow-1">
                                <?= htmlspecialchars(mb_strimwidth($item['deskripsi'], 0, 100, '...')) ?>
                            </p>
                            <div class="mt-auto">
                                <a href="kegiatan_detail.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-dark">Lihat Selengkapnya</a>
                            </div>
                        </div>
                        <div class="card-footer text-muted small">
                            <?= htmlspecialchars($item['tanggal']) ?>
                            <?php if (!empty($item['oleh'])): ?>
                                <div>Diunggah oleh: <strong><?= htmlspecialchars($item['oleh']) ?></strong></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Modal popup gambar (carousel) -->
                <div class="modal fade" id="modalGambar<?= $loopIndex ?>" tabindex="-1">
                  <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content p-3 text-center">
                      <?php if (count($gambarArray) > 1): ?>
                        <div id="carousel<?= $loopIndex ?>" class="carousel slide" data-bs-ride="carousel">
                          <div class="carousel-inner">
                            <?php foreach ($gambarArray as $gIdx => $g): ?>
                              <div class="carousel-item <?= $gIdx === 0 ? 'active' : '' ?>">
                                <img src="uploads/<?= htmlspecialchars($g) ?>" class="d-block w-100 rounded" style="max-height: 500px; object-fit: contain;">
                                <div class="mt-2">
                                <a href="uploads/<?= htmlspecialchars($g) ?>" download class="btn btn-outline-dark btn-sm">
                                  Download
                                </a>
                                </div>
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
                        <img src="uploads/<?= htmlspecialchars($thumbnail) ?>" class="img-fluid rounded shadow mb-3" alt="Preview Gambar">
                        <a href="uploads/<?= htmlspecialchars($thumbnail) ?>" download class="btn btn-outline-dark btn-sm">
                          Download
                        </a>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>

            <?php 
              $loopIndex++;
            endforeach;
        } else {
            echo "<p>Belum ada kegiatan yang ditambahkan.</p>";
        }
        ?>
      </div>
      <a href="kegiatan.php" class="btn btn-outline-dark mt-3">Lihat Semua Kegiatan</a>
    </div>
  </section>

    </main>
<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php';
?>
