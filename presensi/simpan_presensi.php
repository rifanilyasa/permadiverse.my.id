<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Anda belum login.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $eventId = $_POST['event_id'];
    $username = $_SESSION['username'];
    $tanggal = date("Y-m-d H:i:s");

    $filePath = __DIR__ . '/../data/presensi.json';
    $presensiData = file_exists($filePath) ? json_decode(file_get_contents($filePath), true) : [];

    // Cek apakah user sudah absen pada event ini
    foreach ($presensiData as $p) {
        if ($p['event_id'] === $eventId && $p['username'] === $username) {
            echo json_encode(['success' => false, 'message' => 'Anda sudah melakukan presensi.']);
            exit;
        }
    }

    // Ambil nama asli dari users.json
    $usersFile = __DIR__ . '/../Users/users.json'; // Sesuaikan dengan path kamu
    $nama = $username; // default jika nama tidak ditemukan

    if (file_exists($usersFile)) {
        $users = json_decode(file_get_contents($usersFile), true);

        foreach ($users as $user) {
            if (isset($user['username']) && $user['username'] === $username) {
                $nama = $user['nama']; // Ambil nama dari user yang cocok
                break;
            }
        }
    }

    // Tambahkan presensi
    $presensiData[] = [
        'event_id' => $eventId,
        'nama' => $nama,
        'username' => $username,
        'waktu' => $tanggal
    ];

    file_put_contents($filePath, json_encode($presensiData, JSON_PRETTY_PRINT));
    echo json_encode(['success' => true, 'message' => 'Presensi berhasil dicatat.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Permintaan tidak valid.']);
}
?>
