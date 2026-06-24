<?php
// Mulai session jika belum
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);

$isPJ = false;

if (isset($_SESSION['username']) && $_SESSION['role'] === 'user') {
    $usersFile = $_SERVER['DOCUMENT_ROOT'] . '/Users/users.json';
    $users = json_decode(file_get_contents($usersFile), true);
    foreach ($users as $u) {
        if ($u['username'] === $_SESSION['username']) {
            $jabatanUser = (array)($u['jabatan'] ?? []);
            foreach ($jabatanUser as $jabatan) {
                if (stripos($jabatan, 'PJ ') === 0) {
                    $isPJ = true;
                    break 2;
                }
            }
        }
    }
}

$resetFile = $_SERVER['DOCUMENT_ROOT'] . '/Users/reset_requests.json';
$hasResetRequest = false;
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    if (file_exists($resetFile)) {
        $requests = json_decode(file_get_contents($resetFile), true);
        if (!empty($requests)) {
            $hasResetRequest = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PERMADI</title>
  <link rel="icon" href="/title.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    html, body {
      height: 100%;
      margin: 0;
      display: flex;
      flex-direction: column;
    }

    .mobile-badge {
      z-index: 999;
    }

    .mobile-badge.hidden {
      display: none !important;
    }
    
    main {
      flex: 1;
    }

    body { 
      font-family: 'Poppins', sans-serif;
    }

    .navbar-dark .navbar-nav .nav-link {
      transition: background-color 0.3s ease, color 0.3s ease;
      border-radius: 1rem;
      padding: 0.5rem 0.75rem;
      margin: 0 0.25rem;
    }

    .navbar-dark .navbar-nav .nav-link:hover {
      background-color: rgba(255, 255, 255, 0.2);
      color: #ffc107 !important;
    }

    .navbar-dark .navbar-nav .nav-link.active {
      background-color: rgba(255, 255, 255, 0.15); /* samar, semi-transparan */
      color: #ffc107 !important;
      font-weight: 600;
      box-shadow: 0 0 5px rgba(255, 255, 255, 0.1); /* efek mewah tapi halus */
    }

    .navbar-dark .navbar-nav .dropdown-menu .dropdown-item {
      border-radius: 0.5rem;
    }

    .navbar-dark .navbar-nav .dropdown-menu .dropdown-item:hover {
      background-color: #ffc107;
      color: #000 !important;
    }

    .navbar-dark .navbar-nav .nav-link.user-nama {
      background-color: #ffc107;
      color: #000;
      border-radius: 999px;
      padding: 0.5rem 1rem;
      transition: all 0.3s ease;
      font-weight: 600;
    }

    .navbar-dark .navbar-nav .nav-link.user-nama:hover {
      background-color: #222;
      color: #ffc107 !important;
    }

    /* Style umum nama user */
    .user-nama-wrapper {
      overflow: hidden;
      white-space: nowrap;
      display: inline-block;
      border-radius: 50rem;
    }

    /* Text di dalam nama */
    .user-nama-inner {
      display: inline-block;
    }

    .mobile-badge {
      z-index: 999;
    }

    /* Saat di desktop (min-width 992px = breakpoint lg) */
    @media (min-width: 992px) {
      .user-nama-wrapper {
        width: 100px; /* ukuran tetap */
        position: relative; */
        height: 30px; /* atur tinggi maksimal */
        padding: 0; /* hapus padding luar */
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .user-nama-inner {
        animation: scrollText 5s linear infinite;
        padding: 0; /* reset padding */
        /* line-height: 1; */
        white-space: nowrap;
      }

      .user-nama-wrapper:hover {
        background-color: #222 !important;
        color: #ffc107 !important;
      }

      .user-nama-wrapper:hover .user-nama-inner {
        color: #ffc107 !important;
      }

      @keyframes scrollText {
        0% { transform: translateX(100%); }
        100% { transform: translateX(-100%); }
      }
    }

    /* Saat di mobile (max-width 991px) */
    @media (max-width: 991.98px) {
      .user-nama-wrapper {
        width: auto; /* biarkan fleksibel */
        margin-top: 0.5rem;
      }

      .user-nama-inner {
        animation: none;
        transform: none;
        padding: 0;
        line-height: 1;
        font-size: 0.875rem; /* bisa diubah sesuai selera */
      }

      .navbar-toggler .mobile-badge {
        top: 0;
        right: 0;
        transform: translate(50%, -50%);
      }
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow sticky-top">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center gap-3" href="/index.php">
        <img src="/uploads/logo_putih.png" alt="Logo PERMADI" style="height: 50px;">
        <strong class="text-white d-inline-block align-middle">PERMADI</strong>
      </a>
      <button class="navbar-toggler position-relative" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link <?= $current_page == 'index.php' ? 'active' : '' ?>" href="/index.php"><i class="bi bi-house-door-fill me-1 d-inline-block d-lg-none"></i>Beranda</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $current_page == 'profile.php' ? 'active' : '' ?>" href="/profile.php"><i class="bi bi-person-circle me-1 d-inline-block d-lg-none"></i>Profile</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $current_page == 'kegiatan.php' ? 'active' : '' ?>" href="/kegiatan.php"><i class="bi bi-calendar-event-fill me-1 d-inline-block d-lg-none"></i>Kegiatan</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $current_page == 'galeri.php' ? 'active' : '' ?>" href="/galeri.php"><i class="bi bi-images me-1 d-inline-block d-lg-none"></i>Galeri</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $current_page == 'kontak.php' ? 'active' : '' ?>" href="/kontak.php"><i class="bi bi-telephone-fill me-1 d-inline-block d-lg-none"></i>Kontak</a>
          </li>

          <?php if (isset($_SESSION['role'])): ?>
            <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'user'): ?>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle <?= in_array($current_page, ['tambah_kegiatan.php', 'tambah_event.php', 'presensi.php', 'riwayat_presensi.php']) ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown">
                  <i class="bi bi-gear-fill me-1 d-inline-block d-lg-none"></i>Administrasi
                </a>
                <ul class="dropdown-menu dropdown-menu-dark">
                  <?php if ($_SESSION['role'] === 'admin' || $isPJ): ?>
                    <li><a class="dropdown-item <?= $current_page == 'tambah_proker.php' ? 'active' : '' ?>" href="/struktur/tambah_proker.php"><i class="bi bi-list-task me-1"></i>Program Kerja</a></li>
                  <?php endif; ?>
                  <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li><a class="dropdown-item <?= $current_page == 'daftar_user.php' ? 'active' : '' ?>" href="/Users/daftar_user.php"><i class="bi bi-file-earmark-text me-1"></i>Daftar Anggota</a></li>
                    <li><a class="dropdown-item <?= $current_page == 'tambah_user.php' ? 'active' : '' ?>" href="/Users/tambah_user.php"><i class="bi bi-person-plus-fill me-1"></i>Tambah Anggota</a></li>
                    <li><a class="dropdown-item <?= $current_page == 'atur_jabatan.php' ? 'active' : '' ?>" href="/struktur/atur_jabatan.php"><i class="bi bi-person-badge-fill me-1"></i>Atur Jabatan</a></li>
                    <li><a class="dropdown-item <?= $current_page == 'tambah_event.php' ? 'active' : '' ?>" href="/presensi/tambah_event.php"><i class="bi bi-calendar-check-fill me-1"></i>Tambah Presensi</a></li>
                    <li><a class="dropdown-item <?= $current_page == 'generate_undangan.php' ? 'active' : '' ?>" href="/presensi/generate_undangan.php"><i class="bi bi-card-heading me-1"></i>Generate Undangan</a></li>
                  <?php elseif ($_SESSION['role'] === 'user'): ?>
                    <li><a class="dropdown-item <?= $current_page == 'tambah_kegiatan.php' ? 'active' : '' ?>" href="/tambah_kegiatan.php"><i class="bi bi-calendar-plus-fill me-1"></i>Tambah Kegiatan</a></li>
                    <li><a class="dropdown-item <?= $current_page == 'presensi.php' ? 'active' : '' ?>" href="/presensi/presensi.php"><i class="bi bi-person-check-fill me-1"></i>Presensi</a></li>
                  <?php endif; ?>
                  <li><a class="dropdown-item <?= $current_page == 'riwayat_presensi.php' ? 'active' : '' ?>" href="/presensi/riwayat_presensi.php"><i class="bi bi-clock-history me-1"></i>Riwayat Presensi</a></li>
                </ul>
              </li>
            <?php endif; ?>

            <?php if ($_SESSION['role'] === 'admin'): ?>
            <li class="nav-item dropdown position-relative">
              <a class="nav-link dropdown-toggle <?= in_array($current_page, ['reset_request_admin.php', 'log.php']) ? 'active' : '' ?>" 
                href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell-fill me-1 d-inline-block d-lg-none"></i>Notifikasi
              </a>
              <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="notifDropdown">
                <li>
                  <a class="dropdown-item d-flex justify-content-between align-items-center <?= $current_page == 'reset_request_admin.php' ? 'active' : '' ?>" 
                    href="/Users/reset_request_admin.php">
                    <i class="bi bi-key-fill me-1"></i>Reset Password
                  </a>
                </li>
              </ul>
            </li>
            <?php endif; ?>

            <li class="nav-item dropdown">
              <a class="nav-link bg-warning text-dark rounded-pill px-3 d-block d-lg-inline-block text-center text-lg-start user-nama-wrapper"
                href="#"
                role="button"
                data-bs-toggle="dropdown"
                aria-expanded="false">
                <span class="user-nama-inner"><?= htmlspecialchars($_SESSION['nama']) ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                <li><a class="dropdown-item <?= $current_page == 'edit_profile.php' ? 'active' : '' ?>" href="/Users/edit_profile.php"><i class="bi bi-pencil-square me-1"></i>Edit Profile</a></li>
                <li><a class="dropdown-item text-danger" href="/logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a></li>
              </ul>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link text-warning <?= $current_page == 'login.php' ? 'active' : '' ?>" href="/login.php"><i class="bi bi-box-arrow-in-right me-1"></i>Login</a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>