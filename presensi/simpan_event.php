<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $tempat = $_POST['tempat'] ?? '';
    $tanggal = $_POST['tanggal'] ?? '';
    $waktu = $_POST['waktu'] ?? '';
    $durasi_mulai = $_POST['durasi_mulai'] ?? '';
    $durasi_selesai = $_POST['durasi_selesai'] ?? '';
    $latitude = $_POST['latitude'] ?? '';
    $longitude = $_POST['longitude'] ?? '';
    $radius = isset($_POST['radius']) ? intval($_POST['radius']) : 10;
    $sekretaris = $_POST['sekretaris'] ?? '';

    if (!$nama || !$tempat || !$tanggal || !$waktu || !$durasi_mulai || !$durasi_selesai || !$latitude || !$longitude || !$sekretaris) {
        echo "Semua data harus diisi.";
        exit;
    }

    // FIXED PATH
    $dataFile = $_SERVER['DOCUMENT_ROOT'] . '/data/event_presensi.json';
    $eventList = [];

    if (file_exists($dataFile)) {
        $eventList = json_decode(file_get_contents($dataFile), true);
        if (!is_array($eventList)) {
            $eventList = [];
        }
    }

    $eventBaru = [
        'id' => uniqid('permadi_'),
        'nama' => $nama,
        'tempat' => $tempat,
        'tanggal' => $tanggal,
        'waktu' => $waktu . ' WIB',
        'durasi' => $durasi_mulai . ' - ' . $durasi_selesai . ' WIB',
        'latitude' => floatval($latitude),
        'longitude' => floatval($longitude),
        'radius' => $radius,
        'sekretaris' => $sekretaris
    ];

    $eventList[] = $eventBaru;

    if (file_put_contents($dataFile, json_encode($eventList, JSON_PRETTY_PRINT))) {
        header("Location: tambah_event.php?sukses=1&id=" . $eventBaru['id']);
        exit;
    } else {
        echo "Gagal menyimpan data. Cek permission file.";
    }
} else {
    echo "Permintaan tidak valid.";
}
?>
