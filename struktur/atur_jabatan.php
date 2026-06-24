<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$usersFile = "../Users/users.json";
$users = json_decode(file_get_contents($usersFile), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($users as &$user) {
        $uname = $user['username'];
        if (isset($_POST['jabatan'][$uname])) {
            $oldJabatan = is_array($user['jabatan']) ? $user['jabatan'] : [];
            $newSelected = $_POST['jabatan'][$uname];
        
            // Gabungkan jabatan lama dengan baru, tapi jaga urutan lama
            $combined = $oldJabatan;
        
            // Tambahkan jabatan baru (yang tidak ada di jabatan lama)
            foreach ($newSelected as $jab) {
                if (!in_array($jab, $combined)) {
                    $combined[] = $jab;
                }
            }
        
            // Hapus jabatan yang tidak dicentang (dibatalkan user)
            $user['jabatan'] = array_values(array_filter($combined, function ($jab) use ($newSelected) {
                return in_array($jab, $newSelected);
            }));
        } else {
            $user['jabatan'] = [];
        }
    }
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
    header("Location: atur_jabatan.php");
    exit;
}
?>

<?php include("../includes/header.php"); ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<main class="flex-fill">
    <div class="container mx-auto px-4 py-8">
        <h2 class="text-3xl fw-bold text-center mb-6 mt-4">Atur Jabatan Pengguna</h2>

        <!-- Form pencarian -->
        <div class="mb-2 text-end">
            <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Cari nama..." class="w-full max-w-sm border border-gray-300 rounded px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <form method="post">
            <div class="overflow-x-auto">
                <table id="userTable" class="table table-bordered bg-white shadow rounded-lg">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">Nama</th>
                            <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">Jabatan</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php foreach ($users as $user): if ($user['role'] === 'admin') continue; ?>
                            <?php
                                $username = $user['username'];
                                $nama = $user['nama'];
                                $modalId = 'hapusModal_' . md5($username);
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-semibold"><?= htmlspecialchars($user['nama']) ?> </br>
                                    <!-- Tombol Hapus -->
                                    <button type="button" class="btn btn-sm btn-danger mt-2" data-bs-toggle="modal" data-bs-target="#<?= $modalId ?>">
                                        <i class="bi bi-trash"></i> Hapus User
                                    </button>

                                    <!-- Modal Konfirmasi -->
                                    <div class="modal fade" id="<?= $modalId ?>" tabindex="-1" aria-labelledby="<?= $modalId ?>Label" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title" id="<?= $modalId ?>Label">Konfirmasi Hapus</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                            </div>
                                            <div class="modal-body">
                                                Yakin ingin menghapus user <strong><?= htmlspecialchars($nama) ?></strong>?
                                            </div>
                                            <div class="modal-footer">
                                                <form method="post" action="/Users/hapus_user.php">
                                                <input type="hidden" name="username" value="<?= htmlspecialchars($username) ?>">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                                                </form>
                                            </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                        <?php
                                        $jabatanList = [
                                            "Pembina",
                                            "Penasihat",
                                            "Ketua", 
                                            "Wakil Ketua",
                                            "Sekretaris 1", 
                                            "Sekretaris 2",
                                            "Bendahara 1", 
                                            "Bendahara 2",
                                            "PJ Seksi Perkap dan Lingkup", 
                                            "PJ Seksi Keputrian", 
                                            "PJ Seksi Sosial dan Kemasyarakatan",
                                            "PJ Seksi Seni Budaya dan Olahraga", 
                                            "PJ Seksi Pendidikan dan Kaderisasi",
                                            "Seksi Perkap dan Lingkup", 
                                            "Seksi Keputrian", 
                                            "Seksi Sosial dan Kemasyarakatan",
                                            "Seksi Seni Budaya dan Olahraga", 
                                            "Seksi Pendidikan dan Kaderisasi",
                                            "Anggota PERMADI"
                                        ];

                                        $userJabatan = is_array($user['jabatan']) ? $user['jabatan'] : [$user['jabatan']];

                                        foreach ($jabatanList as $jab) {
                                            $checked = in_array($jab, $userJabatan) ? 'checked' : '';
                                            echo "<label class='inline-flex items-center space-x-2'>
                                                    <input type='checkbox' name='jabatan[{$user['username']}][]' value='$jab' class='form-checkbox text-blue-600' $checked>
                                                    <span class='text-xs sm:text-sm'>$jab</span>
                                                </label>";
                                        }
                                        ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="
                position: fixed;
                bottom: 20px;
                left: 50%;
                transform: translateX(-50%);
                background: rgba(255, 255, 255, 0.6);
                backdrop-filter: blur(10px);
                border: 1px solid #ccc;
                padding: 12px 24px;
                border-radius: 12px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                z-index: 1000;
            ">
                <button type="submit" class="btn btn-primary px-4 py-2">💾 Simpan</button>
            </div>
        </form>
    </div>
    <?php if (isset($_GET['hapus_sukses']) && isset($_GET['nama'])): 
        $namaDihapus = htmlspecialchars($_GET['nama']);
    ?>
        <script>
        document.addEventListener("DOMContentLoaded", function () {
            var modal = new bootstrap.Modal(document.getElementById('hapusSuksesModal'));
            modal.show();

            // Bersihkan URL setelah ditampilkan
            setTimeout(() => {
            const url = new URL(window.location);
            url.searchParams.delete('hapus_sukses');
            url.searchParams.delete('nama');
            window.history.replaceState({}, document.title, url.pathname);
            }, 500);
        });
        </script>

        <!-- Modal Sukses Hapus -->
        <div class="modal fade" id="hapusSuksesModal" tabindex="-1" aria-labelledby="hapusSuksesLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="hapusSuksesLabel">Berhasil!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body text-center">
                ✅ User <strong>'<?= $namaDihapus ?>'</strong> berhasil dihapus.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Tutup</button>
            </div>
            </div>
        </div>
        </div>
    <?php endif; ?>
</main>
<script>
// Fitur filter nama
function filterTable() {
    const input = document.getElementById("searchInput");
    const filter = input.value.toLowerCase();
    const rows = document.querySelectorAll("#userTable tbody tr");

    rows.forEach(row => {
        const nama = row.querySelector("td").textContent.toLowerCase();
        row.style.display = nama.includes(filter) ? "" : "none";
    });
}
</script>
<?php include("../includes/footer.php"); ?>