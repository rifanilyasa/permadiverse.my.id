<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized access.");
}

$usersFile = "users.json";
if (!file_exists($usersFile)) {
    die("File users.json tidak ditemukan.");
}

$users = json_decode(file_get_contents($usersFile), true);
$usernameToDelete = $_POST['username'] ?? '';

if (!$usernameToDelete) {
    die("Username tidak valid.");
}

// Cari user yang cocok
$namaUser = '';
foreach ($users as $u) {
    if ($u['username'] === $usernameToDelete) {
        if ($u['role'] === 'admin') {
            die("Tidak bisa menghapus user dengan role admin.");
        }
        $namaUser = $u['nama'] ?? $usernameToDelete;
        break;
    }
}

// Hapus user dari array
$users = array_filter($users, fn($u) => $u['username'] !== $usernameToDelete);

// Simpan ulang file JSON
file_put_contents($usersFile, json_encode(array_values($users), JSON_PRETTY_PRINT));

// Redirect dengan notifikasi sukses dan nama
header("Location: /struktur/atur_jabatan.php?hapus_sukses=1&nama=" . urlencode($namaUser));
exit;
