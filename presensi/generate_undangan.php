<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit;
}

setlocale(LC_TIME, 'id_ID.UTF-8');

$eventFile = $_SERVER['DOCUMENT_ROOT'] . '/data/event_presensi.json';
$userFile = $_SERVER['DOCUMENT_ROOT'] . '/Users/users.json';

$events = file_exists($eventFile) ? json_decode(file_get_contents($eventFile), true) : [];
$users = file_exists($userFile) ? json_decode(file_get_contents($userFile), true) : [];

$selected = null;
if (isset($_GET['event'])) {
    foreach ($events as $ev) {
        if ($ev['id'] == $_GET['event']) {
            $selected = $ev;
            break;
        }
    }
}


$ketua = '....................';
foreach ($users as $u) {
    $jabatans = (array)($u['jabatan'] ?? []);
    if (in_array('Ketua', $jabatans)) {
        $ketua = $u['nama'];
        break;
    }
}

// Ambil sekretaris dari data event jika tersedia
$sekretaris = '....................';
if ($selected && isset($selected['sekretaris']) && !empty($selected['sekretaris'])) {
    $sekretaris = $selected['sekretaris'];
}


$formatter = new IntlDateFormatter('id_ID', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
$tanggal = $selected ? $formatter->format(strtotime($selected['tanggal'])) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Generate Undangan - PERMADI</title>
    <link rel="icon" href="/title.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .preview-wrapper {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1cm;
        }
        .preview {
            width: 14.8cm;
            height: auto;
            padding: 1.5cm;
            background: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border: 1px solid #ccc;
            position: relative;
            page-break-inside: avoid;
        }
        .kop {
            text-align: center;
            border-bottom: 3px solid black;
            margin-bottom: 2rem;
        }
        .kop img {
            height: 85px;
            margin-bottom: 1rem;
        }
        @media (max-width: 768px) {
            .preview-wrapper {
                flex-direction: column;
                align-items: center;
            }
            .preview {
                width: 100%;
                padding: 1rem;
                box-shadow: none;
                border: none;
            }
        }
        @media print {
            body {
                background: none;
            }
            .preview-wrapper {
                flex-wrap: wrap;
                gap: 0;
                padding: 0;
                margin: 0;
            }
            .preview {
                width: 10.5cm;
                height: 14.8cm;
                padding: 1cm;
                margin: 0;
                box-shadow: none;
                border: none;
                page-break-inside: avoid;
            }
            @page {
                size: A4 landscape;
                margin: 0;
            }
        }

        .isi-undangan table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .isi-undangan table td {
            padding: 4px;
            vertical-align: top;
        }
    </style>
</head>
<body class="bg-light">
<?php include $_SERVER['DOCUMENT_ROOT'] . "/includes/header.php"; ?>
<main class="container py-3">
    <h3 class="mb-4 text-center">Generate Undangan Kegiatan</h3>

    <form method="GET">
        <div class="mb-3">
            <label for="event" class="form-label fw-semibold">Pilih Event Kegiatan</label>
            <select name="event" id="event" class="form-select" onchange="this.form.submit()">
                <option value="">-- Pilih Event --</option>
                <?php foreach ($events as $e): ?>
                    <option value="<?= htmlspecialchars($e['id']) ?>" <?= (isset($_GET['event']) && $_GET['event'] == $e['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($e['nama']) ?> (<?= $e['tanggal'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <?php if ($selected): ?>
        <div class="preview-wrapper mt-4">
            <div class="preview">
                <div class="kop">
                    <img src="/uploads/logo_hitam.png" alt="Logo PERMADI">
                    <h5 class="mb-0 fw-bold">Perkumpulan Pemuda Pemudi Dukuh XII Sumbersari</h5>
                    <p class="mb-0">Dukuh XII Sumbersari Moyudan Sleman DI Yogyakarta 55563</p>
                </div>

                <div class="isi-undangan">
                    <table>
                        <tr>
                            <td>Hal</td>
                            <td>:</td>
                            <td>Undangan</td>
                        </tr>
                        <tr>
                            <td>Kepada</td>
                            <td>:</td>
                            <td style="text-decoration: underline">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        </tr>
                    </table>
                    <br>

                    <p>Assalamu’alaikum Wr. Wb.</p>
                    <p>Dengan hormat,</p>
                    <p>
                        Bersama ini, kami mengharap kehadiran Pemuda Pemudi Dukuh XII untuk menghadiri kegiatan pada :
                    </p>

                    <table>
                        <tr>
                            <td>Hari/Tanggal</td>
                            <td>:</td>
                            <td><?= $tanggal ?></td>
                        </tr>
                        <tr>
                            <td>Waktu</td>
                            <td>:</td>
                            <td><?= htmlspecialchars($selected['waktu'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td>Tempat</td>
                            <td>:</td>
                            <td><?= htmlspecialchars($selected['tempat'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td>Acara</td>
                            <td>:</td>
                            <td><?= htmlspecialchars($selected['nama']) ?></td>
                        </tr>
                    </table>

                    <p class="mt-3">
                        Demikian undangan ini kami sampaikan. Atas perhatian dan kehadirannya kami ucapkan terima kasih.
                    </p>

                    <p>Wassalamu'alaikum Wr. Wb.</p>

                    <br>
                    <table style="width: 100%;">
                        <tr>
                            <td style="width: 50%; text-align: center;">
                                Ketua<br><br><br>
                                <?= $ketua ?>
                            </td>
                            <td style="width: 50%; text-align: center;">
                                Sekretaris<br><br><br>
                                <?= $sekretaris ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="text-center">
        <a href="export_undangandocx.php?event=<?= $selected['id'] ?>" class="btn btn-success mt-3" target="_blank" onclick="generateUndangan()">
            Download Undangan (DOCX)
        </a>
        <a href="export_undanganpdf.php?event=<?= $selected['id'] ?>" class="btn btn-success mt-3 disabled" target="_blank">
            Download Undangan (PDF)
        </a>
    </div>    
</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . "/includes/footer.php"; ?>