<?php
session_start();
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized access.");
}

$dateNow = date('Ymd_His'); // Format: 20250409_153021

// Ambil parameter filter
$namaFilter = $_GET['username'] ?? 'all';
$yearFilter = $_GET['year'] ?? 'all';
$monthFilter = $_GET['month'] ?? 'all';
$format = $_GET['format'] ?? 'excel';

$filename = "rekap_absensi_" . ($namaFilter !== 'all' ? preg_replace('/[^a-z0-9_]/i', '_', strtolower($namaFilter)) : "all") . "_{$dateNow}";

// File paths
$presensiFile = $_SERVER['DOCUMENT_ROOT'] . '/data/presensi.json';
$eventFile = $_SERVER['DOCUMENT_ROOT'] . '/data/event_presensi.json';
$usersFile = $_SERVER['DOCUMENT_ROOT'] . '/Users/users.json';

// Ambil data JSON
$presensiList = file_exists($presensiFile) ? json_decode(file_get_contents($presensiFile), true) : [];
$events = file_exists($eventFile) ? json_decode(file_get_contents($eventFile), true) : [];
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

// Buat mapping nama <=> username
$userMap = []; // nama => username
$usernameToNama = []; // username => nama
foreach ($users as $user) {
    $userMap[$user['nama']] = $user['username'];
    $usernameToNama[$user['username']] = $user['nama'];
}

// Ubah nama jadi username jika perlu
$usernameFilter = 'all';
if ($namaFilter !== 'all' && isset($userMap[$namaFilter])) {
    $usernameFilter = $userMap[$namaFilter];
}

// Filter events sesuai tahun & bulan
$filteredEvents = array_filter($events, function ($event) use ($yearFilter, $monthFilter) {
    [$y, $m] = explode('-', $event['tanggal']);
    return ($yearFilter === 'all' || $y === $yearFilter) &&
           ($monthFilter === 'all' || $m === str_pad($monthFilter, 2, '0', STR_PAD_LEFT));
});

// Urutkan event berdasarkan tanggal
usort($filteredEvents, fn($a, $b) => strcmp($a['tanggal'], $b['tanggal']));

// Ambil hanya ID dari event yang terpilih
$filteredEventIds = array_column($filteredEvents, 'id');

// Buat data presensi yang sudah difilter
$dataPresensi = [];
foreach ($presensiList as $p) {
    if ($usernameFilter !== 'all' && $p['username'] !== $usernameFilter) continue;
    if (!in_array($p['event_id'], $filteredEventIds)) continue;

    $dataPresensi[$p['username']][$p['event_id']] = $p['waktu'];
}

// Ambil semua username dari users.json, bukan hanya yang ada di presensi
$usernames = [];
foreach ($users as $u) {
    if ($u['role'] === 'user') {
        $usernames[] = $u['username'];
    }
}

// Siapkan header nama event
$eventHeaders = array_map(fn($e) => $e['nama'], $filteredEvents);

// === EXPORT ===
if ($format === 'excel') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Header baris
    $sheet->setCellValue('A1', 'Nama');
    foreach ($eventHeaders as $i => $header) {
        $col = chr(66 + $i); // B, C, D...
        $sheet->setCellValue("{$col}1", $header);
    }

    // Data presensi
    foreach ($usernames as $rowIndex => $user) {
        $namaAsli = $usernameToNama[$user] ?? $user;
        $sheet->setCellValue("A" . ($rowIndex + 2), $namaAsli);
        foreach ($filteredEvents as $i => $event) {
            $col = chr(66 + $i);
            $value = isset($dataPresensi[$user][$event['id']]) ? 'Hadir' : 'X';
            $sheet->setCellValue("{$col}" . ($rowIndex + 2), $value);
        }
    }

    // Tambahkan baris Total
    $sheet->setCellValue('A' . ($rowIndex + 3), 'Total');
    foreach ($filteredEvents as $i => $event) {
        $col = chr(66 + $i);
        $total = 0;
        foreach ($usernames as $user) {
            if (isset($dataPresensi[$user][$event['id']])) {
                $total++;
            }
        }
        $sheet->setCellValue("{$col}" . ($rowIndex + 3), $total);
    }

    // Output file Excel
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment;filename=\"{$filename}.xlsx\"");
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} elseif ($format === 'pdf') {
    $html = '<h3>Riwayat Presensi</h3>';
    $html .= '<table border="1" cellpadding="5" cellspacing="0" style="width:100%; font-size:12px;">';
    $html .= '<thead><tr><th>Nama</th>';

    foreach ($eventHeaders as $h) {
        $html .= '<th>' . htmlspecialchars($h) . '</th>';
    }
    $html .= '</tr></thead><tbody>';

    foreach ($usernames as $user) {
        $namaAsli = $usernameToNama[$user] ?? $user;
        $html .= '<tr><td>' . htmlspecialchars($namaAsli) . '</td>';
        foreach ($filteredEvents as $event) {
            $val = isset($dataPresensi[$user][$event['id']]) ? 'Hadir' : 'X';
            $html .= '<td style="text-align:center">' . $val . '</td>';
        }
        $html .= '</tr>';
    }

    $html .= '<tr><td><strong>Total</strong></td>';
    foreach ($filteredEvents as $event) {
        $total = 0;
        foreach ($usernames as $user) {
            if (isset($dataPresensi[$user][$event['id']])) {
                $total++;
            }
        }
        $html .= '<td style="text-align:center"><strong>' . $total . '</strong></td>';
    }
    $html .= '</tr>';

    $html .= '</tbody></table>';

    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream("{$filename}.pdf", ["Attachment" => true]);
    exit;

} else {
    die("Format export tidak dikenali.");
}
