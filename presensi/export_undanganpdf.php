<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit;
}

$eventId = $_GET['event'] ?? null;
if (!$eventId) die("ID event tidak ditemukan.");

$eventFile = $_SERVER['DOCUMENT_ROOT'] . '/data/event_presensi.json';
$userFile  = $_SERVER['DOCUMENT_ROOT'] . '/Users/users.json';

$events = file_exists($eventFile) ? json_decode(file_get_contents($eventFile), true) : [];
$users  = file_exists($userFile) ? json_decode(file_get_contents($userFile), true) : [];

$event = null;
foreach ($events as $e) {
    if ($e['id'] == $eventId) {
        $event = $e;
        break;
    }
}
if (!$event) die("Event tidak ditemukan.");

$ketua = '....................';
$sekretaris = $event['sekretaris'] ?? '....................';
foreach ($users as $u) {
    $jabatans = (array)($u['jabatan'] ?? []);
    if (in_array('Ketua', $jabatans)) $ketua = $u['nama'];
}

setlocale(LC_TIME, 'id_ID.UTF-8');
$tanggal = strftime('%A, %d %B %Y', strtotime($event['tanggal']));
$waktu = $event['waktu'] ?? '-';
$tempat = $event['tempat'] ?? '-';

// === Generate HTML Undangan ===
$logoPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/logo_hitam.png';
$logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));

$undanganHTML = function() use ($logoBase64, $event, $tanggal, $waktu, $tempat, $ketua, $sekretaris) {
    return <<<HTML
<div style="font-family: 'Times New Roman'; font-size: 11pt; width: 100%; margin-bottom: 20px;">
    <table style="width: 100%;">
        <tr>
            <td style="width: 70px;">
                <img src="$logoBase64" width="60" height="60" />
            </td>
            <td style="text-align: center;">
                <b>"PERMADI"</b><br>
                <b>Perkumpulan Pemuda Pemudi Dukuh XII Sumbersari</b><br>
                Dukuh XII Sumbersari Moyudan Sleman DI Yogyakarta 55563
            </td>
        </tr>
    </table>
    <hr>
    <p><b>Hal</b> : Undangan<br><b>Kepada</b> : ____________</p>
    <p>Assalamu’alaikum Wr. Wb.<br>
    Dengan hormat,<br>
    Bersama ini, kami mengharap kehadiran Pemuda Pemudi Dukuh XII untuk menghadiri kegiatan pada :</p>

    <table style="margin-left: 10px;">
        <tr><td style="width: 100px;">Hari/Tanggal</td><td>: $tanggal</td></tr>
        <tr><td>Waktu</td><td>: $waktu</td></tr>
        <tr><td>Tempat</td><td>: $tempat</td></tr>
        <tr><td>Acara</td><td>: {$event['nama']}</td></tr>
    </table>

    <p>Demikian undangan ini kami sampaikan. Atas perhatian dan kehadirannya kami ucapkan terima kasih.<br>
    Wassalamu’alaikum Wr. Wb.</p>

    <table style="width: 100%; margin-top: 30px;">
        <tr>
            <td style="text-align: center;">Ketua</td>
            <td style="text-align: center;">Sekretaris</td>
        </tr>
        <tr>
            <td style="height: 50px;"></td>
            <td></td>
        </tr>
        <tr>
            <td style="text-align: center;">$ketua</td>
            <td style="text-align: center;">$sekretaris</td>
        </tr>
    </table>
</div>
HTML;
};

$html = $undanganHTML() . '<div style="page-break-after: always;"></div>' . $undanganHTML(); // 2 kolom

// === Render ke PDF ===
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A5', 'portrait');
$dompdf->render();

$filename = 'Undangan_' . preg_replace('/[^a-zA-Z0-9]/', '_', $event['nama']) . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);
exit;
