<?php
$file = __DIR__ . '/proker.json';
$data = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

$seksi = $_GET['seksi'] ?? '';
$index = $_GET['index'] ?? '';

if (isset($data[$seksi][$index])) {
    unset($data[$seksi][$index]);
    $data[$seksi] = array_values($data[$seksi]); // Re-index ulang
    foreach ($data[$seksi] as $i => &$item) {
        $item['no'] = $i + 1;
    }
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

header("Location: tambah_proker.php?hapus_sukses=1");
exit;
