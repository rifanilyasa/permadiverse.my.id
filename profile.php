<?php
$users_file = $_SERVER['DOCUMENT_ROOT'] . '/Users/users.json';
$users = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];
?>

<?php
// Urutan prioritas jabatan (urutan dari paling penting ke bawah)
$urutanJabatan = [
  'Pembina',
  'Penasihat',
  'Ketua',
  'Wakil Ketua',
  'Sekretaris 1',
  'Sekretaris 2',
  'Bendahara 1',
  'Bendahara 2',
  'PJ Seksi Perkap dan Lingkup',
  'PJ Seksi Sosial dan Kemasyarakatan',
  'PJ Seksi Seni Budaya dan Olahraga',
  'PJ Seksi Pendidikan dan Kaderisasi',
  'PJ Seksi Keputrian',
  'Seksi Perkap dan Lingkup',
  'Seksi Sosial dan Kemasyarakatan',
  'Seksi Seni Budaya dan Olahraga',
  'Seksi Pendidikan dan Kaderisasi',
  'Seksi Keputrian',
  'Anggota PERMADI'
];

// Ambil user non-admin
$pengurus = array_filter($users, function ($user) {
  return $user['role'] !== 'admin';
});

// Fungsi bantu untuk menentukan posisi jabatan
function getJabatanPriority($jabatanArray, $urutanJabatan) {
  foreach ($jabatanArray as $jabatan) {
    $jabatan = strtolower(trim($jabatan));
    foreach ($urutanJabatan as $index => $prioritas) {
      if (strtolower($jabatan) === strtolower($prioritas)) {
        return $index;
      }
    }
  }
  return count($urutanJabatan); // jika tidak ditemukan, beri prioritas paling bawah
}
// Urutkan array pengurus berdasarkan jabatan pertama yang terdaftar
usort($pengurus, function ($a, $b) use ($urutanJabatan) {
  $aJabatan = (array)($a['jabatan'] ?? []);
  $bJabatan = (array)($b['jabatan'] ?? []);
  return getJabatanPriority($aJabatan, $urutanJabatan) <=> getJabatanPriority($bJabatan, $urutanJabatan);
});
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Profile Organisasi - PERMADI</title>
  <link rel="icon" href="/title.png" type="image/png">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      background: #f5f5f5;
      font-family: 'Segoe UI', sans-serif;
    }
    .section-title {
      font-weight: 700;
      font-size: 2rem;
      margin-bottom: 1rem;
      color: #343a40;
    }
    .tentang {
      background: white;
      padding: 2rem;
      border-radius: 1rem;
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
      margin-bottom: 3rem;
    }
    .struktur {
      text-align: center;
      margin-bottom: 3rem;
    }
    .struktur .person {
      margin: 1rem auto;
      padding: 1rem;
      border-radius: 1rem;
      background-color: #d8a50c;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    .struktur img {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 50%;
      border: 3px solid #198754;
      margin-bottom: 0.5rem;
    }
    .line-vertical {
      width: 2px;
      background-color: #198754;
      height: 40px;
      margin: 0 auto;
    }
    .table-striped > tbody > tr:nth-of-type(odd) {
      background-color: #f8f9fa;
    }
    .carousel-item {
      text-align: center;
      padding: 2rem 0;
      min-height: 450px; /* Sesuaikan dengan tinggi konten maksimal */
    }

    .profile-photo {
      width: 250px;
      height: 300px;
      object-fit: cover;
      border-radius: 1rem;
      margin-bottom: 1rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .jabatan-list {
      min-height: 96px; /* 4 baris @24px */
      line-height: 24px;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>
<main class="flex-fill">
  <div class="container py-4">
    <!-- SLIDER PROFIL PENGURUS -->
    <div class="mb-3">
      <h2 class="text-center section-title">Profile Anggota</h2>
      <div id="profileSlider" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
        <div class="carousel-inner">
          <?php
          $active = 'active';
          foreach ($pengurus as $user):
            $username = $user['username'];
            $fotoFolder = $_SERVER['DOCUMENT_ROOT'] . "/Users/profile_photo/";
            $fotoName = $user['foto'] ?? "$username.jpg";
            $defaultFoto = "/Users/profile_photo/default.png";
            $fotoPath = file_exists($fotoFolder . $fotoName)
            ? "/Users/profile_photo/$fotoName?" . filemtime($fotoFolder . $fotoName)
            : $defaultFoto;
            $nama = $user['nama'] ?? '';
            $jabatans = (array)($user['jabatan'] ?? []);
          ?>
          <div class="carousel-item <?= $active ?>">
            <div class="d-flex flex-column align-items-center">
              <img src="<?= htmlspecialchars($fotoPath) ?>" class="profile-photo" alt="<?= htmlspecialchars($nama) ?>">
              <h5><?= htmlspecialchars($nama) ?></h5>
              <div class="jabatan-list text-muted">
                <?php
                $maxJabatan = 4;
                for ($i = 0; $i < $maxJabatan; $i++):
                  if (isset($jabatans[$i])) {
                    echo '<div>' . htmlspecialchars($jabatans[$i]) . '</div>';
                  } else {
                    echo '<div style="visibility: hidden;">placeholder</div>';
                  }
                endfor;
                ?>
              </div>
            </div>
          </div>
          <?php $active = ''; endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#profileSlider" data-bs-slide="prev">
          <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#profileSlider" data-bs-slide="next">
          <span class="carousel-control-next-icon"></span>
        </button>
      </div>
    </div>
    
    <!-- Tentang -->
    <div class="tentang">
      <h2 class="section-title">Tentang PERMADI</h2>
      <p>
      PERMADI (Perkumpulan Pemuda Pemudi Dukuh XII Sumbersari) adalah organisasi kepemudaan yang berada di dusun Dukuh XII RW 32 Nglahar Sumbersari Moyudan Sleman DI Yogyakarta. Didirikan sebagai wadah untuk menghimpun, membina, dan memberdayakan para pemuda desa. PERMADI hadir dengan semangat kebersamaan dan gotong royong. Organisasi ini menjadi tempat bagi generasi muda untuk mengembangkan potensi diri, berkontribusi dalam pembangunan desa, serta mempererat tali silaturahmi antarwarga. </br>
      </p>
      <p>
      Melalui berbagai kegiatan sosial, budaya, keagamaan, dan olahraga, PERMADI berkomitmen untuk berpartisipasi pada perubahan positif di lingkungan desa. Dengan semangat kolaborasi dan inovasi, kami percaya bahwa pemuda adalah pilar utama kemajuan masyarakat.
      </p>
    </div>

    <!-- Struktur Organisasi -->
    <div class="struktur mt-4">
      <h2 class="section-title mb-4">Struktur Organisasi</h2>

      <?php
      function cariUserBerdasarkanJabatan($users, $jabatanDicari) {
        foreach ($users as $u) {
          $jabatans = (array)($u['jabatan'] ?? []);
          if (in_array($jabatanDicari, $jabatans)) {
            $fotoFolder = $_SERVER['DOCUMENT_ROOT'] . "/Users/profile_photo/";
            $fotoName = $u['foto'] ?? ($u['username'] . '.jpg');
            $fotoPath = file_exists($fotoFolder . $fotoName)
            ? "/Users/profile_photo/$fotoName?" . filemtime($fotoFolder . $fotoName)
            : "/Users/profile_photo/default.png";
            return [
              'nama' => $u['nama'] ?? 'Tanpa Nama',
              'foto' => $fotoPath
            ];
          }
        }
        return [
          'nama' => 'Belum Ditentukan',
          'foto' => "/Users/profile_photo/default.png"
        ];
      }

      $dataKetua = cariUserBerdasarkanJabatan($users, 'Ketua');
      $dataWakil = cariUserBerdasarkanJabatan($users, 'Wakil Ketua');
      $dataSekretaris = cariUserBerdasarkanJabatan($users, 'Sekretaris 1');
      $dataBendahara = cariUserBerdasarkanJabatan($users, 'Bendahara 1');
      ?>
      
      <!-- Pembina -->
      <?php
      $pembina = array_filter($users, function ($u) {
        return in_array("Pembina", (array)($u['jabatan'] ?? []));
      });
      ?>
      <div class="mb-2">
        <div class="row justify-content-center">
          <?php if (count($pembina) > 0): ?>
            <?php foreach ($pembina as $p): 
              $nama = $p['nama'] ?? 'Tanpa Nama';
              $fotoName = $p['foto'] ?? ($p['username'] . ".jpg");
              $fotoPath = $_SERVER['DOCUMENT_ROOT'] . "/Users/profile_photo/$fotoName";
              $foto = file_exists($fotoPath)
                ? "/Users/profile_photo/$fotoName?" . filemtime($fotoPath)
                : "/Users/profile_photo/default.png";
            ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-2">
              <div class="person text-center p-3">
                <img src="<?= htmlspecialchars($foto) ?>" alt="<?= htmlspecialchars($nama) ?>" class="mb-2">
                <div><strong><?= htmlspecialchars($nama) ?></strong></div>
                <div class="text-muted">Pembina</div>
              </div>
            </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-2">
              <div class="person text-center p-3">
                <img src="/Users/profile_photo/default.png" alt="Belum Ditentukan" class="mb-2">
                <div><strong>Belum Ditentukan</strong></div>
                <div class="text-muted">Pembina</div>
              </div>
            </div>
          <?php endif; ?>
        </div>
        <div class="line-vertical"></div>
      </div>

      <div class="row justify-content-center">
        <!-- Ketua -->
        <div class="col-12 col-sm-10 col-md-6 col-lg-3 mb-2">
          <div class="person">
            <img src="<?= $dataKetua['foto'] ?>" alt="Ketua">
            <h5 class="mb-1"><strong><?= htmlspecialchars($dataKetua['nama']) ?></strong></h5>
            <p class="text-muted">Ketua</p>
          </div>
          <div class="line-vertical"></div>
        </div>
      </div>
      
      <div class="row justify-content-center">
        <!-- Wakil Ketua -->
        <div class="col-12 col-sm-10 col-md-6 col-lg-3 mb-2">
          <div class="person">
            <img src="<?= $dataWakil['foto'] ?>" alt="Wakil Ketua">
            <h5 class="mb-1"><strong><?= htmlspecialchars($dataWakil['nama']) ?></strong></h5>
            <p class="text-muted">Wakil Ketua</p>
          </div>
          <div class="line-vertical"></div>
        </div>
      </div>
      
      <!-- Sekretaris & Bendahara -->
      <div class="row justify-content-center">
        <!-- Sekretaris -->
        <div class="col-md-5 mb-4">
          <div class="person text-center p-3 rounded shadow-sm h-100">
            <h5 class="mb-4">Sekretaris</h5>
            <?php
            $sekretaris = array_filter($users, function ($u) {
              return in_array("Sekretaris 1", (array)($u['jabatan'] ?? [])) || in_array("Sekretaris 2", (array)($u['jabatan'] ?? []));
            });
            if (count($sekretaris) > 0): ?>
            <div class="d-flex flex-column flex-md-row justify-content-center gap-4 flex-wrap">
              <?php foreach ($sekretaris as $sekre):
                $nama = $sekre['nama'] ?? '';
                $foto = "/Users/profile_photo/" . ($sekre['foto'] ?? ($sekre['username'] . ".jpg"));
                if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $foto)) {
                  $foto = "/Users/profile_photo/default.png";
                }
                $jabatanLabel = implode(', ', array_filter((array)$sekre['jabatan'], fn($j) => str_contains($j, 'Sekretaris')));
              ?>
              <div class="text-center">
                <img src="<?= htmlspecialchars($foto) ?>" alt="<?= htmlspecialchars($nama) ?>" class="mb-2" style="width: 60px; height: 60px; object-fit: cover; border-radius: 50%; border: 2px solid #198754;">
                <div><strong><?= htmlspecialchars($nama) ?></strong></div>
                <div class="text-muted"><?= $jabatanLabel ?></div>
              </div>
              <?php endforeach; ?>
            </div>
            <?php else: ?>
            <img src="/Users/profile_photo/default.png" class="mb-2" alt="Belum Ditentukan" style="width: 80px; height: 80px; object-fit: cover; border-radius: 50%; border: 2px solid #198754;">
            <div><strong>Belum Ditentukan</strong></div>
            <div class="text-muted">Sekretaris 1 & 2</div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Bendahara -->
        <div class="col-md-5 mb-4">
          <div class="person text-center p-3 rounded shadow-sm h-100">
            <h5 class="mb-4">Bendahara</h5>
            <?php
            $bendahara = array_filter($users, function ($u) {
              return in_array("Bendahara 1", (array)($u['jabatan'] ?? [])) || in_array("Bendahara 2", (array)($u['jabatan'] ?? []));
            });
            if (count($bendahara) > 0): ?>
            <div class="d-flex flex-column flex-md-row justify-content-center gap-4 flex-wrap">
              <?php foreach ($bendahara as $bend):
                $nama = $bend['nama'] ?? '';
                $foto = "/Users/profile_photo/" . ($bend['foto'] ?? ($bend['username'] . ".jpg"));
                if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $foto)) {
                  $foto = "/Users/profile_photo/default.png";
                }
                $jabatanLabel = implode(', ', array_filter((array)$bend['jabatan'], fn($j) => str_contains($j, 'Bendahara')));
              ?>
              <div class="text-center">
                <img src="<?= htmlspecialchars($foto) ?>" alt="<?= htmlspecialchars($nama) ?>" class="mb-2" style="width: 60px; height: 60px; object-fit: cover; border-radius: 50%; border: 2px solid #198754;">
                <div><strong><?= htmlspecialchars($nama) ?></strong></div>
                <div class="text-muted"><?= $jabatanLabel ?></div>
              </div>
              <?php endforeach; ?>
            </div>
            <?php else: ?>
            <img src="/Users/profile_photo/default.png" class="mb-2" alt="Belum Ditentukan" style="width: 80px; height: 80px; object-fit: cover; border-radius: 50%; border: 2px solid #198754;">
            <div><strong>Belum Ditentukan</strong></div>
            <div class="text-muted">Bendahara 1 & 2</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="line-vertical"></div>

      <!-- Seksi-Seksi -->
      <div class="row">
        <?php
        $seksi = [
          "Perkap dan Lingkup",
          "Keputrian",
          "Sosial dan Kemasyarakatan",
          "Seni Budaya dan Olahraga",
          "Pendidikan dan Kaderisasi"
        ];
        $prokerFile = $_SERVER['DOCUMENT_ROOT'] . '/struktur/proker.json';
        $prokerData = file_exists($prokerFile) ? json_decode(file_get_contents($prokerFile), true) : [];

        foreach ($seksi as $index => $nama):
          $pj = cariUserBerdasarkanJabatan($users, "PJ Seksi $nama");
        ?>
          <div class="col-md-6 col-lg-4 mx-auto mb-4">
            <div class="person">
              <img src="<?= $pj['foto'] ?>" alt="PJ <?= $nama ?>">
              <h6 class="mb-1"><strong><?= htmlspecialchars($pj['nama']) ?></strong></h6>
              <p class="text-muted">PJ Seksi <?= $nama ?></p>
            </div>
            <div class="table-responsive">
              <table class="table table-bordered table-sm mt-3">
                <thead class="table-success text-center">
                  <tr>
                    <th>No</th>
                    <th>Program Kerja</th>
                    <th>Rencana</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $programs = $prokerData[$nama] ?? [];
                  if (count($programs) > 0):
                    foreach ($programs as $i => $prog): ?>
                      <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($prog['nama']) ?></td>
                        <td><?= htmlspecialchars($prog['waktu']) ?></td>
                      </tr>
                    <?php endforeach;
                  else: ?>
                    <tr>
                      <td colspan="3" class="text-center text-muted">Belum ada program kerja</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <!-- Tambahan di bawah struktur organisasi -->
    <div class="container">
      <h2 class="text-center mb-4">Daftar Seluruh PERMADI</h2>
      <div class="row row-cols-2 row-cols-md-5 g-4">
        <?php
        $usersstruktur = json_decode(file_get_contents("Users/users.json"), true);
        foreach ($usersstruktur as $userSt) {
            if (($userSt['role'] ?? '') === 'admin') continue;

            $name = $userSt['nama'] ?? 'Tanpa Nama';
            $jabatan = $userSt['jabatan'] ?? [];
            $fotoFile = $userSt['foto'] ?? null;
            $foto = $fotoFile ? 'Users/profile_photo/' . $fotoFile : 'Users/profile_photo/default.png';

            echo '<div class="col">';
            echo '<div class="card h-100 text-center">';
            echo '<img src="' . $foto . '" class="card-img-top mx-auto mt-3 rounded-circle" style="width:100px; height:100px; object-fit:cover;" alt="' . htmlspecialchars($name) . '">';
            echo '<div class="card-body">';
            echo '<h6 class="card-title mb-1">' . htmlspecialchars($name) . '</h6>';
            echo '</div></div></div>';
        }
        ?>
      </div>
    </div>
  </div>
</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
