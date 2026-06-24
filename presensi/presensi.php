<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: /login.php");
    exit;
}

$username = $_SESSION['username'];
$eventFile = __DIR__ . '/../data/event_presensi.json';
$presensiFile = __DIR__ . '/../data/presensi.json';
$eventList = file_exists($eventFile) ? array_reverse(json_decode(file_get_contents($eventFile), true)) : [];
$presensiList = file_exists($presensiFile) ? json_decode(file_get_contents($presensiFile), true) : [];

date_default_timezone_set("Asia/Jakarta");

$activeEvent = null;
$now = new DateTime();

foreach ($eventList as $event) {
    if (!isset($event['tanggal'], $event['durasi'])) continue;

    // Parse waktu dari string durasi, misal: "13:00 - 19:00 WIB"
    if (preg_match('/(\d{2}:\d{2})\s*-\s*(\d{2}:\d{2})/', $event['durasi'], $matches)) {
        $startTime = $matches[1];
        $endTime = $matches[2];

        $start = new DateTime($event['tanggal'] . ' ' . $startTime);
        $end = new DateTime($event['tanggal'] . ' ' . $endTime);

        if ($now >= $start && $now <= $end) {
            // Inject waktu_mulai dan waktu_selesai agar dipakai di tampilan
            $event['waktu_mulai'] = $startTime;
            $event['waktu_selesai'] = $endTime;
            $activeEvent = $event;
            break;
        }
    }
}

$hasPresensi = false;
if ($activeEvent) {
    foreach ($presensiList as $p) {
        if ($p['event_id'] === $activeEvent['id'] && $p['username'] === $username) {
            $hasPresensi = true;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Presensi - PERMADI</title>
  <link rel="icon" href="/title.png" type="image/png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>#map { height: 300px; }</style>
</head>
<body>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>
<main class="flex-fill">
  <div class="container mt-4">
    <h3 class="mb-4 text-center fw-bold">Presensi Kehadiran</h3>

    <?php if (!$activeEvent): ?>
      <div class="alert alert-warning text-center">Tidak ada event aktif saat ini.</div>
    <?php else: ?>
      <div class="card mb-4">
        <div class="card-body text-center">
          <h5 class="card-title"><?= htmlspecialchars($activeEvent['nama']) ?></h5>
          <p class="card-text">
            <?= $activeEvent['tanggal'] ?> | <?= $activeEvent['waktu_mulai'] ?> - <?= $activeEvent['waktu_selesai'] ?>
          </p>
          <p class="text-muted" id="jarakInfo">Menghitung jarak...</p>
          <p id="countdown"></p>

          <?php if ($hasPresensi): ?>
            <button class="btn btn-success" disabled>Anda sudah melakukan presensi</button>
          <?php else: ?>
            <form id="formPresensi" method="POST" action="simpan_presensi.php">
              <input type="hidden" name="event_id" value="<?= $activeEvent['id'] ?>">
              <button type="submit" id="btnPresensi" class="btn btn-primary" disabled>Presensi Sekarang</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
      <div id="map"></div>
    <?php endif; ?>
  </div>
</main>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
<?php if ($activeEvent): ?>
  const eventLat = <?= $activeEvent['latitude'] ?>;
  const eventLng = <?= $activeEvent['longitude'] ?>;
  const eventRadius = <?= $activeEvent['radius'] ?? 30 ?>;

const map = L.map('map').setView([eventLat, eventLng], 20);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: 'Map data © OpenStreetMap contributors'
}).addTo(map);

// Marker lokasi event
L.marker([eventLat, eventLng]).addTo(map).bindPopup("Lokasi Event").openPopup();

// Tambahkan lingkaran radius 30 meter di sekitar event
L.circle([eventLat, eventLng], {
  radius: eventRadius,
  color: 'green',
  fillColor: '#0f03',
  fillOpacity: 0.3
}).addTo(map);

navigator.geolocation.getCurrentPosition(pos => {
  const userLat = pos.coords.latitude;
  const userLng = pos.coords.longitude;

  // Marker untuk user
  const userMarker = L.marker([userLat, userLng]).addTo(map).bindPopup("Lokasi Anda").openPopup();

  // Garis dari user ke event
  L.polyline(
    [
      [eventLat, eventLng],
      [userLat, userLng]
    ],
    {
      color: 'blue',
      weight: 3,
      opacity: 0.7,
      dashArray: '6, 6'
    }
  ).addTo(map);

  // Zoom agar kedua titik terlihat
  const bounds = L.latLngBounds(
    [eventLat, eventLng],
    [userLat, userLng]
  );
  map.fitBounds(bounds, { padding: [30, 30], maxZoom: 20 });

  // Hitung jarak
  const R = 6371e3;
  const φ1 = userLat * Math.PI/180;
  const φ2 = eventLat * Math.PI/180;
  const Δφ = (eventLat-userLat) * Math.PI/180;
  const Δλ = (eventLng-userLng) * Math.PI/180;
  const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
            Math.cos(φ1) * Math.cos(φ2) *
            Math.sin(Δλ/2) * Math.sin(Δλ/2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
  const distance = R * c;

  const jarakMeter = Math.round(distance);
  const info = document.getElementById("jarakInfo");
  const btn = document.getElementById("btnPresensi");

  if (jarakMeter <= eventRadius) {
    info.textContent = `Jarak Anda ke lokasi: ${jarakMeter} meter (✅ dalam jangkauan).`;
    btn.disabled = false;
  } else {
    info.textContent = `Jarak Anda: ${jarakMeter} meter (❌ terlalu jauh).`;
    btn.disabled = true;
  }
});

// Hitung mundur waktu tersisa
const countdownEl = document.getElementById('countdown');
const endTime = new Date("<?= $activeEvent['tanggal'] . ' ' . $activeEvent['waktu_selesai'] ?>").getTime();

const timer = setInterval(() => {
  const now = new Date().getTime();
  const distance = endTime - now;

  if (distance <= 0) {
    clearInterval(timer);
    countdownEl.innerHTML = '<span class="text-danger">Waktu presensi sudah berakhir</span>';
    document.getElementById("btnPresensi").disabled = true;
    return;
  }

  const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
  const seconds = Math.floor((distance % (1000 * 60)) / 1000);
  countdownEl.innerHTML = `Sisa waktu presensi: ${hours}j ${minutes}m ${seconds}d`;
}, 1000);

// Kirim presensi pakai fetch + SweetAlert
const formPresensi = document.getElementById("formPresensi");
if (formPresensi) {
  formPresensi.addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(formPresensi);

    fetch("simpan_presensi.php", {
      method: "POST",
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        Swal.fire({
          icon: 'success',
          title: 'Presensi Berhasil!',
          text: data.message,
          confirmButtonText: 'OK'
        }).then(() => location.reload());
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: data.message
        });
      }
    })
    .catch(err => {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Gagal mengirim data presensi.'
      });
    });
  });
}
<?php endif; ?>
</script>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
