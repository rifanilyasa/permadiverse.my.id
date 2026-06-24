<?php
require_once __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

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

// Mulai membuat dokumen
$phpWord = new PhpWord();

$section = $phpWord->addSection([
    'marginTop'    => Converter::cmToTwip(1.5),
    'marginBottom' => Converter::cmToTwip(1.5),
    'marginLeft'   => Converter::cmToTwip(1.5),
    'marginRight'  => Converter::cmToTwip(1.5),
    'pageSizeW'    => Converter::cmToTwip(14.8),
    'pageSizeH'    => Converter::cmToTwip(21),
]);

// Gaya teks
$phpWord->addFontStyle('judul', ['bold' => true, 'size' => 12, 'name' => 'Times New Roman']);
$phpWord->addFontStyle('subjudul', ['bold' => true, 'size' => 11, 'name' => 'Times New Roman']);
$phpWord->addFontStyle('isi', ['size' => 11, 'name' => 'Times New Roman']);
$phpWord->addFontStyle('tabelacara', ['size' => 11, 'name' => 'Times New Roman']);
$phpWord->addParagraphStyle('center', ['alignment' => Jc::CENTER]);
$phpWord->addParagraphStyle('right', ['alignment' => Jc::RIGHT]);
$phpWord->addParagraphStyle('justify', ['alignment' => Jc::BOTH]);

$table = $section->addTable([
    'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
    'width' => 100 * 50, // 100% dari lebar halaman
]);

$table->addRow();

$cellLogo = $table->addCell(Converter::cmToTwip(3)); // Lebar kolom kiri untuk logo
$cellLogo->addImage(__DIR__ . '/../uploads/logo_hitam.png', [
    'width' => 60,
    'height' => 60,
    'wrappingStyle' => 'inline',
]);

$cellText = $table->addCell(8000);
$cellText->addText('"PERMADI"', 'judul', 'center', ['bold' => true, 'size' => 12]);
$cellText->addText('Perkumpulan Pemuda Pemudi Dukuh XII Sumbersari', 'subjudul', 'center', ['alignment' => 'center']);
$cellText->addText('Dukuh XII Sumbersari Moyudan Sleman DI Yogyakarta 55563', 'subjudul', 'center', ['alignment' => 'center']);
$section->addText("____________________________________________________________", null, 'center', ['bold' => true]);
$section->addTextBreak(0.5);

$section->addText('Hal     : Undangan', 'tabelacara', ['bold' => true]);
$section->addText('Kepada  : ___________', 'tabelacara');
$section->addTextBreak(1);

$section->addText("Assalamu’alaikum Wr. Wb.", 'isi', 'justify');
$section->addText("Dengan hormat,", 'isi', 'justify');
$section->addText("Bersama ini, kami mengharap kehadiran Pemuda Pemudi Dukuh XII untuk menghadiri kegiatan pada :", 'isi', 'justify');
$section->addTextBreak(0.5);

// Tabel detail acara
    $detailTable = $section->addTable(['cellMarginLeft' => 100]);

    $detailTable->addRow();
    $detailTable->addCell(3000)->addText('Hari/Tanggal', 'tabelacara');
    $detailTable->addCell(6000)->addText(': ' . $tanggal, ['bold' => true, 'size' => 11, 'name' => 'Times New Roman']);

    $detailTable->addRow();
    $detailTable->addCell(3000)->addText('Waktu', 'tabelacara');
    $detailTable->addCell(6000)->addText(': ' . $waktu, ['bold' => true, 'size' => 11, 'name' => 'Times New Roman']);

    $detailTable->addRow();
    $detailTable->addCell(3000)->addText('Tempat', 'tabelacara');
    $detailTable->addCell(6000)->addText(': ' . $tempat, ['bold' => true, 'size' => 11, 'name' => 'Times New Roman']);

    $detailTable->addRow();
    $detailTable->addCell(3000)->addText('Acara', 'tabelacara');
    $detailTable->addCell(6000)->addText(': ' . $event['nama'], ['bold' => true, 'size' => 11, 'name' => 'Times New Roman']);


$section->addText("Demikian undangan ini kami sampaikan. Atas perhatian dan kehadirannya kami ucapkan terima kasih.", 'isi', 'justify');
$section->addText("Wassalamu'alaikum Wr. Wb.", 'isi', 'justify');
$section->addTextBreak(1);

// Tanda tangan
    $ttd = $section->addTable();
    $ttd->addRow();
    $ttd->addCell(4000)->addText('Ketua', null, ['alignment' => 'center']);
    $ttd->addCell(4000)->addText('Sekretaris', null, ['alignment' => 'center']);
    $ttd->addRow();
    $ttd->addCell()->addTextBreak(2);
    $ttd->addCell()->addTextBreak(2);
    $ttd->addRow();
    $ttd->addCell()->addText($ketua, null, ['alignment' => 'center']);
    $ttd->addCell()->addText($sekretaris, null, ['alignment' => 'center']);
    $section->addTextBreak(1);

$table = $section->addTable([
    'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
    'width' => 100 * 50, // 100% dari lebar halaman
]);

$table->addRow();

$cellLogo = $table->addCell(Converter::cmToTwip(3)); // Lebar kolom kiri untuk logo
$cellLogo->addImage(__DIR__ . '/../uploads/logo_hitam.png', [
    'width' => 60,
    'height' => 60,
    'wrappingStyle' => 'inline',
]);

$cellText = $table->addCell(8000);
$cellText->addText('"PERMADI"', 'judul', 'center', ['bold' => true, 'size' => 12]);
$cellText->addText('Perkumpulan Pemuda Pemudi Dukuh XII Sumbersari', 'subjudul', 'center', ['alignment' => 'center']);
$cellText->addText('Dukuh XII Sumbersari Moyudan Sleman DI Yogyakarta 55563', 'subjudul', 'center', ['alignment' => 'center']);
$section->addText("____________________________________________________________", null, 'center', ['bold' => true]);
$section->addTextBreak(0.5);

$section->addText('Hal     : Undangan', 'tabelacara', ['bold' => true]);
$section->addText('Kepada  : ___________', 'tabelacara');
$section->addTextBreak(1);

$section->addText("Assalamu’alaikum Wr. Wb.", 'isi', 'justify');
$section->addText("Dengan hormat,", 'isi', 'justify');
$section->addText("Bersama ini, kami mengharap kehadiran Pemuda Pemudi Dukuh XII untuk menghadiri kegiatan pada :", 'isi', 'justify');
$section->addTextBreak(0.5);

// Tabel detail acara
    $detailTable = $section->addTable(['cellMarginLeft' => 100]);

    $detailTable->addRow();
    $detailTable->addCell(3000)->addText('Hari/Tanggal', 'tabelacara');
    $detailTable->addCell(6000)->addText(': ' . $tanggal, ['bold' => true, 'size' => 11, 'name' => 'Times New Roman']);

    $detailTable->addRow();
    $detailTable->addCell(3000)->addText('Waktu', 'tabelacara');
    $detailTable->addCell(6000)->addText(': ' . $waktu, ['bold' => true, 'size' => 11, 'name' => 'Times New Roman']);

    $detailTable->addRow();
    $detailTable->addCell(3000)->addText('Tempat', 'tabelacara');
    $detailTable->addCell(6000)->addText(': ' . $tempat, ['bold' => true, 'size' => 11, 'name' => 'Times New Roman']);

    $detailTable->addRow();
    $detailTable->addCell(3000)->addText('Acara', 'tabelacara');
    $detailTable->addCell(6000)->addText(': ' . $event['nama'], ['bold' => true, 'size' => 11, 'name' => 'Times New Roman']);


$section->addText("Demikian undangan ini kami sampaikan. Atas perhatian dan kehadirannya kami ucapkan terima kasih.", 'isi', 'justify');
$section->addText("Wassalamu'alaikum Wr. Wb.", 'isi', 'justify');
$section->addTextBreak(1);

// Tanda tangan
    $ttd = $section->addTable();
    $ttd->addRow();
    $ttd->addCell(4000)->addText('Ketua', null, ['alignment' => 'center']);
    $ttd->addCell(4000)->addText('Sekretaris', null, ['alignment' => 'center']);
    $ttd->addRow();
    $ttd->addCell()->addTextBreak(2);
    $ttd->addCell()->addTextBreak(2);
    $ttd->addRow();
    $ttd->addCell()->addText($ketua, null, ['alignment' => 'center']);
    $ttd->addCell()->addText($sekretaris, null, ['alignment' => 'center']);
    
// Output file
header("Content-Description: File Transfer");
header('Content-Disposition: attachment; filename="Undangan_' . preg_replace('/[^a-zA-Z0-9]/', '_', $event['nama']) . '.docx"');
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');

$writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
$writer->save("php://output");
exit;
