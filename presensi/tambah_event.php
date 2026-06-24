<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: /login.php");
    exit;
}

$userFile = $_SERVER['DOCUMENT_ROOT'] . '/Users/users.json';
$users = file_exists($userFile) ? json_decode(file_get_contents($userFile), true) : [];
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Event Presensi</title>
  <link rel="icon" href="/title.png" type="image/png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <style>
    #map { height: 300px; }
  </style>
</head>
<body>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>
<main class="flex-fill">
  <div class="container mt-4">
    <h3 class="mb-4 text-center fw-bold">Tambah Event Presensi</h3>
    <form action="/presensi/simpan_event.php" method="POST">
      <div class="mb-3">
        <label for="nama">Nama Event</label>
        <select name="nama" id="nama" class="form-select" required>
          <option value="">-- Pilih Nama Event --</option>
          <option value="Rapat Rutin Bulanan">Rapat Rutin Bulanan</option>
          <option value="Musyawarah Besar">Musyawarah Besar</option>
          <option value="Kegiatan Bakti Sosial">Kegiatan Bakti Sosial</option>
          <option value="Rapat Koordinasi Pengurus">Rapat Koordinasi Pengurus</option>
          <option value="Pelatihan Kepemimpinan">Pelatihan Kepemimpinan</option>
          <option value="Peringatan Hari Besar Islam">Peringatan Hari Besar Islam</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="tempat">Tempat Pertemuan</label>
        <input type="text" class="form-control" name="tempat" required>
      </div>
      <div class="mb-3">
        <label for="tanggal">Tanggal</label>
        <input type="date" class="form-control" name="tanggal" required>
      </div>
      <div class="mb-3">
        <label for="waktu">Waktu Pertemuan</label>
        <input type="time" class="form-control" name="waktu" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Durasi Presensi</label>
        <div class="row">
          <div class="col-5">
            <input type="time" class="form-control" name="durasi_mulai" id="durasi_mulai" required>
          </div>
          <div class="col-2 text-center">
            <label>-</label>
          </div>
          <div class="col-5">
            <input type="time" class="form-control" name="durasi_selesai" id="durasi_selesai" required>
          </div>
        </div>
      </div>
      <div class="mb-3">
        <label for="radius" class="form-label">Radius Presensi (dalam meter)</label>
        <input type="number" class="form-control" id="radius" name="radius" min="10" value="10" required>
      </div>
      <div class="mb-3">
        <label for="sekretaris">Pilih Sekretaris</label>
        <select name="sekretaris" id="sekretaris" class="form-select" required>
          <option value="">-- Pilih Sekretaris --</option>
          <?php foreach ($users as $u): 
            $jabatans = isset($u['jabatan']) ? (array)$u['jabatan'] : [];
            if (in_array("Sekretaris 1", $jabatans) || in_array("Sekretaris 2", $jabatans)):
          ?>
            <option value="<?= htmlspecialchars($u['nama']) ?>"><?= htmlspecialchars($u['nama']) ?></option>
          <?php endif; endforeach; ?>
        </select>
      </div>
      <div class="mb-3">
        <label for="map">Pilih Lokasi</label>
        <div id="map"></div>
      </div>
      <input type="hidden" name="latitude" id="latitude" required>
      <input type="hidden" name="longitude" id="longitude" required>
      <div class="text-center">
        <button type="submit" class="btn btn-success">Simpan Event</button>
      </div>
    </form>
  </div>
</main>
<?php if (isset($_GET['sukses']) && $_GET['sukses'] == '1'): ?>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    Swal.fire({
      title: 'Berhasil!',
      text: 'Event presensi berhasil ditambahkan.',
      icon: 'success',
      confirmButtonText: 'OK'
    }).then(() => {
      window.location.href = '/presensi/riwayat_presensi.php';
    });
  </script>
<?php endif; ?>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
navigator.geolocation.getCurrentPosition(pos => {
  const lat = pos.coords.latitude;
  const lng = pos.coords.longitude;

  const map = L.map('map').setView([lat, lng], 16);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: 'Map data © OpenStreetMap contributors'
  }).addTo(map);

  let marker = L.marker([lat, lng], { draggable: true }).addTo(map);
  let circle = null;

  document.getElementById("latitude").value = lat;
  document.getElementById("longitude").value = lng;

  function updateCircle() {
    const radius = parseFloat(document.getElementById("radius").value);
    const lat = parseFloat(document.getElementById("latitude").value);
    const lng = parseFloat(document.getElementById("longitude").value);

    if (!isNaN(radius) && radius > 0) {
      if (circle) map.removeLayer(circle);
      circle = L.circle([lat, lng], {
        radius: radius,
        color: 'blue',
        fillColor: '#3f9',
        fillOpacity: 0.2
      }).addTo(map);
    }
  }

  marker.on('dragend', function(e) {
    const pos = e.target.getLatLng();
    document.getElementById("latitude").value = pos.lat;
    document.getElementById("longitude").value = pos.lng;
    updateCircle();
  });

  document.getElementById("radius").addEventListener("input", updateCircle);
  updateCircle();
});
</script>
<script>
document.querySelector('form').addEventListener('submit', function(e) {
  const mulai = document.getElementById('durasi_mulai').value;
  const selesai = document.getElementById('durasi_selesai').value;

  if (mulai && selesai && mulai >= selesai) {
    e.preventDefault();
    Swal.fire({
      icon: 'error',
      title: 'Durasi Tidak Valid',
      text: 'Waktu selesai harus lebih besar dari waktu mulai.'
    });
  }
});
</script>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
