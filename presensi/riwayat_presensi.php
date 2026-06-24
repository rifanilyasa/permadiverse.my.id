<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: /login.php");
    exit;
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];

$presensiFile = $_SERVER['DOCUMENT_ROOT'] . '/data/presensi.json';
$eventFile = $_SERVER['DOCUMENT_ROOT'] . '/data/event_presensi.json';
$usersFile = $_SERVER['DOCUMENT_ROOT'] . '/Users/users.json';

$presensiList = file_exists($presensiFile) ? json_decode(file_get_contents($presensiFile), true) : [];
$events = file_exists($eventFile) ? json_decode(file_get_contents($eventFile), true) : [];
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

// Ambil data filter dari query string
$filterNama = $_GET['username'] ?? 'all';
$filterYear = $_GET['year'] ?? 'all';
$filterMonth = $_GET['month'] ?? 'all';

// Ambil user hanya dengan role "user", urutan mengikuti users.json
$filteredUsers = array_values(array_filter($users, fn($u) => $u['role'] === 'user'));

// Ambil semua event ID & tanggal
$eventIds = array_column($events, 'id');
$eventDate = array_column($events, 'tanggal', 'id');

// Mapping user presensi
$dataPresensi = [];
foreach ($presensiList as $p) {
    $dataPresensi[$p['username']][$p['event_id']] = true;
}

// Mapping username => nama
$userMap = [];
foreach ($filteredUsers as $u) {
    $userMap[$u['username']] = $u['nama'];
}

// Ambil username yang tampil (hanya role user dan sesuai filter jika ada)
$usernames = array_map(fn($u) => $u['username'], $filteredUsers);
if ($role !== 'admin') {
    $usernames = [$username];
}
if ($filterNama !== 'all') {
    $usernames = array_filter($usernames, fn($u) => $userMap[$u] === $filterNama);
    $usernames = array_values($usernames); // reindex
}

// Filter event berdasarkan tahun dan bulan
if ($filterYear !== 'all' || $filterMonth !== 'all') {
    $eventIds = array_filter($eventIds, function($id) use ($eventDate, $filterYear, $filterMonth) {
        [$y, $m] = explode('-', $eventDate[$id]);
        return ($filterYear === 'all' || $filterYear == $y) &&
               ($filterMonth === 'all' || $filterMonth == $m);
    });
    $eventIds = array_values($eventIds); // reindex
}

// Hitung hadir
$eventCount = [];
foreach ($eventIds as $eventId) {
    $eventCount[$eventId] = 0;
    foreach ($usernames as $u) {
        if (isset($dataPresensi[$u][$eventId])) {
            $eventCount[$eventId]++;
        }
    }
}

// Ambil semua tahun dan bulan untuk dropdown
$years = $months = [];
foreach ($events as $e) {
    [$y, $m] = explode('-', $e['tanggal']);
    $years[] = $y;
    $months[] = $m;
}
$years = array_unique($years);
$months = array_unique($months);
sort($years);
sort($months);

function monthName($m) {
    return date("M", mktime(0, 0, 0, $m, 10));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat Presensi - PERMADI</title>
  <link rel="icon" href="/title.png" type="image/png">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <style>
    td, th { text-align: center; vertical-align: middle; white-space: nowrap; }
    .sticky-col { position: sticky; left: 0; z-index: 10; background-color: #fff; }
    .table-responsive { overflow-x: auto; }
  </style>
</head>
<body>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>
<main class="flex-fill">
  <div class="container mt-4">
    <h3 class="text-center mb-4 fw-bold">Riwayat Presensi</h3>
    <?php if ($role === 'admin'): ?>
      <form id="filterForm" action="riwayat_presensi.php" method="GET" class="row g-3 align-items-end mb-4">
        <div class="col-md-3">
          <label for="username" class="form-label">Pilih Pengguna:</label>
          <select name="username" id="username" class="form-select select2">
            <option value="all">Semua</option>
            <?php foreach ($filteredUsers as $u): ?>
              <option value="<?= htmlspecialchars($u['nama']) ?>" <?= ($filterNama === $u['nama']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($u['nama']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-3">
          <label for="year" class="form-label">Tahun:</label>
          <select name="year" id="year" class="form-select">
            <option value="all">Semua Tahun</option>
            <?php foreach ($years as $y): ?>
              <option value="<?= $y ?>" <?= ($filterYear == $y) ? 'selected' : '' ?>><?= $y ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-3">
          <label for="month" class="form-label">Bulan:</label>
          <select name="month" id="month" class="form-select">
            <option value="all">Semua Bulan</option>
            <?php foreach ($months as $m): ?>
              <option value="<?= $m ?>" <?= ($filterMonth == $m) ? 'selected' : '' ?>><?= monthName($m) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-1 text-end">
          <a href="riwayat_presensi.php" class="btn btn-outline-secondary w-100" title="Reset Filter">
            <i class="bi bi-arrow-clockwise"></i>
          </a>
        </div>

        <div class="col-md-1">
          <button type="submit" formaction="export_presensi.php" name="format" value="excel" class="btn btn-success w-100">Excel</button>
        </div>

        <div class="col-md-1">
          <button type="submit" formaction="export_presensi.php" name="format" value="pdf" class="btn btn-danger w-100">PDF</button>
        </div>
      </form>
      <!-- Loading spinner -->
      <div id="loadingSpinner" class="text-center my-3" style="display:none;">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
    <?php endif; ?>

    <div class="table-responsive">
      <table class="table table-bordered">
        <thead class="table-dark text-center">
          <tr>
            <th class="sticky-col bg-dark">Nama</th>
            <?php foreach ($eventIds as $i => $eid): ?>
              <th class="event-col page-<?= $i ?>" style="display:none" data-date="<?= $eventDate[$eid] ?>">
                <?= $eventDate[$eid] ?>
              </th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($usernames as $u): ?>
            <tr>
              <td class="sticky-col bg-light"><?= htmlspecialchars($userMap[$u] ?? $u) ?></td>
              <?php foreach ($eventIds as $i => $eid): ?>
                <td class="event-col page-<?= $i ?> <?= isset($dataPresensi[$u][$eid]) ? 'text-success fw-bold' : 'text-danger fw-bold' ?>" style="display:none">
                  <?= isset($dataPresensi[$u][$eid]) ? '✅' : '❌' ?>
                </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($usernames)): ?>
            <tr><td colspan="<?= count($eventIds)+1 ?>" class="text-center">Tidak ada data presensi.</td></tr>
          <?php endif; ?>

          <?php if ($_SESSION['role'] === 'admin'): ?>
            <tr class="fw-bold table-warning">
              <td colspan="1" class="sticky-col">Total Hadir</td>
              <?php foreach ($events as $event): ?>
                <td><?= $total_presensi[$event['id']] ?? 0 ?></td>
              <?php endforeach; ?>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-center align-items-center mt-3">
      <button class="btn btn-secondary me-2" id="prevBtn">< Back</button>
      <span id="pageInfo"></span>
      <button class="btn btn-secondary ms-2" id="nextBtn">Next ></button>
    </div>
  </div>

  <!-- Modal Loading -->
  <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content bg-dark text-white text-center border-0">
        <div class="modal-body py-5">
          <div class="spinner-border text-light mb-3" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mb-0">Memuat data...</p>
        </div>
      </div>
    </div>
  </div>
</main>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  let currentPage = 0;
  let maxPerPage = window.innerWidth <= 576 ? 4 : 12;
  const totalCols = <?= count($eventIds) ?>;
  let totalPages = Math.ceil(totalCols / maxPerPage);

  function showPage(page) {
    $('.event-col').hide();
    const start = page * maxPerPage;
    const end = Math.min(start + maxPerPage, totalCols);
    for (let i = start; i < end; i++) $('.page-' + i).show();
    $('#pageInfo').text(`${page + 1} - ${totalPages}`);
    $('#prevBtn').prop('disabled', page === 0);
    $('#nextBtn').prop('disabled', page === totalPages - 1);
    updateHeaderText();
  }

  function updateHeaderText() {
    const isMobile = window.innerWidth <= 576;
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $('th[data-date]').each(function() {
      const fullDate = $(this).data('date');
      const mIndex = parseInt(fullDate.slice(5,7)) - 1;
      $(this).text(isMobile ? monthNames[mIndex] : fullDate);
    });
  }

  $(document).ready(function() {
    showPage(currentPage);

    $(window).on('resize', () => {
      const newMax = window.innerWidth <= 576 ? 4 : 12;
      if (newMax !== maxPerPage) {
        maxPerPage = newMax;
        totalPages = Math.ceil(totalCols / maxPerPage);
        currentPage = 0;
        showPage(currentPage);
      } else updateHeaderText();
    });

    $('#prevBtn').click(() => { if (currentPage > 0) showPage(--currentPage); });
    $('#nextBtn').click(() => { if (currentPage < totalPages - 1) showPage(++currentPage); });

    $('.select2, #year, #month').on('change', function() {
      $('#filterForm').submit();
    });

    $('.select2').select2({ placeholder: "Cari nama...", allowClear: true, width: '100%' });

    // Tampilkan spinner saat form dikirim
    $('#filterForm').on('submit', function () {
      $('#loadingSpinner').show();
    });
  });
</script>
<script>
  const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'), {
    backdrop: 'static',
    keyboard: false
  });

  $('#filterForm').on('submit', function () {
    loadingModal.show();
  });
</script>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
