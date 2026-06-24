<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$users_file = __DIR__ . '/users.json';
$photo_dir = __DIR__ . '/profile_photo/';
$users = json_decode(file_get_contents($users_file), true);
$username = $_SESSION['username'];

// Cari data user saat ini
$current_user = null;
foreach ($users as $u) {
    if ($u['username'] === $username) {
        $current_user = $u;
        break;
    }
}

if (!$current_user) {
    echo "User tidak ditemukan!";
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_nama = trim($_POST['nama'] ?? '');
    $new_password = $_POST['password'] ?? '';
    $password_lama = $_POST['password_lama'] ?? '';
    $new_username = trim($_POST['new_username'] ?? $username);
    $hapus_foto = isset($_POST['hapus_foto']);

    if ($new_nama === '') {
        $error = "Nama tidak boleh kosong.";
    }

    if ($new_username !== $username) {
        foreach ($users as $u) {
            if ($u['username'] === $new_username) {
                $error = "Username sudah digunakan oleh pengguna lain.";
                break;
            }
        }
    }

    if ($error === '') {
        foreach ($users as &$u) {
            if ($u['username'] === $username) {
                $u['nama'] = $new_nama;

                if ($new_password !== '') {
                    if (!password_verify($password_lama, $u['password'])) {
                        $error = "Password lama tidak sesuai.";
                        break;
                    } else {
                        $u['password'] = password_hash($new_password, PASSWORD_DEFAULT);
                    }
                }

                if ($hapus_foto && !empty($u['foto'])) {
                    $foto_path = $photo_dir . $u['foto'];
                    if (file_exists($foto_path)) {
                        unlink($foto_path);
                    }
                    $u['foto'] = null;
                }

                if (isset($_POST['cropped_image']) && !empty($_POST['cropped_image'])) {
                    $data = $_POST['cropped_image'];
                    list($type, $data) = explode(';', $data);
                    list(, $data) = explode(',', $data);
                    $data = base64_decode($data);
                    $filename = $new_username . '.jpg';
                    file_put_contents($photo_dir . $filename, $data);
                    $u['foto'] = $filename;
                }

                if ($new_username !== $username) {
                    $u['username'] = $new_username;
                    $_SESSION['username'] = $new_username;
                    update_username_references($username, $new_username);
                }

                $_SESSION['nama'] = $new_nama;
                break;
            }
        }

        if ($error === '' && file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT))) {
            $_SESSION['sukses'] = true;
            header("Location: edit_profile.php");
            exit;
        } elseif ($error === '') {
            $error = "Gagal menyimpan perubahan.";
        }
    }

    if ($error !== '') {
        $_SESSION['error'] = $error;
        header("Location: edit_profile.php");
        exit;
    }
}

function update_username_references($old, $new) {
    $files = [
        dirname(__DIR__) . '/data/presensi.json',
        dirname(__DIR__) . '/kegiatan.json',
        dirname(__DIR__) . '/proker.json',
    ];
    foreach ($files as $file) {
        if (!file_exists($file)) continue;
        $data = json_decode(file_get_contents($file), true);
        if (!is_array($data)) continue;

        $changed = false;
        foreach ($data as &$item) {
            if (isset($item['username']) && $item['username'] === $old) {
                $item['username'] = $new;
                $changed = true;
            }
        }

        array_walk_recursive($data, function (&$val) use ($old, $new, &$changed) {
            if ($val === $old) {
                $val = $new;
                $changed = true;
            }
        });

        if ($changed) {
            file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Profile - PERMADI</title>
  <link rel="icon" href="/title.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://unpkg.com/cropperjs/dist/cropper.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    #preview-container {
      width: 100%;
      max-width: 300px;
      height: auto;
      margin-bottom: 1rem;
    }
    #preview-container .overlay-box {
      width: 250px;
      height: 300px;
      border: 2px dashed #28a745;
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      pointer-events: none;
    }
    #preview-container img {
      max-width: 100%;
    }
    @media (max-width: 576px) {
      #preview-container {
        max-width: 100%;
      }
      #preview-container .overlay-box {
        width: 200px;
        height: 240px;
      }
    }
  </style>
</head>
<body class="bg-light">
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>
<main class="flex-fill">
<div class="container mt-4">
  <h2 class="text-center fw-bold mb-4">Edit Profil</h2>
  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Nama Lengkap</label>
      <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($current_user['nama']) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Password Baru</label>
      <input type="password" name="password" class="form-control" id="newPassword">
    </div>

    <div class="mb-3">
      <label class="form-label">Username Baru</label>
      <input type="text" name="new_username" class="form-control" value="<?= htmlspecialchars($current_user['username']) ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Foto Profil</label><br>
      <?php if (!empty($current_user['foto'])): ?>
        <?php
        $foto = !empty($current_user['foto']) ? $current_user['foto'] : 'default.png';
        $foto_url = "profile_photo/$foto?" . time(); // <- menambahkan timestamp
        ?>
        <img src="<?= $foto_url ?>" class="img-thumbnail mb-2" width="150"><br>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="hapus_foto" id="hapusFoto">
          <label class="form-check-label" for="hapusFoto">Hapus foto saat ini</label>
        </div>
      <?php endif; ?>
      <input type="file" class="form-control mt-2" accept="image/*" id="uploadFoto">
      <input type="hidden" name="cropped_image" id="croppedImage">
      <div id="preview-container" class="position-relative mt-3 d-none">
        <div class="overlay-box"></div>
        <img id="previewImage">
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Role</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($current_user['role']) ?>" disabled>
    </div>

    <div class="text-center">
      <button type="button" class="btn btn-primary" id="btnSubmit">Simpan Perubahan</button>
      <a href="/index.php" class="btn btn-secondary">Kembali</a>
    </div>
  </form>
</div>
</main>

<script src="https://unpkg.com/cropperjs/dist/cropper.min.js"></script>
<script>
let cropper;
document.getElementById('uploadFoto').addEventListener('change', function (e) {
  const file = e.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function (event) {
      const image = document.getElementById('previewImage');
      image.src = event.target.result;
      document.getElementById('preview-container').classList.remove('d-none');

      if (cropper) cropper.destroy();
      cropper = new Cropper(image, {
        aspectRatio: 5 / 6,
        viewMode: 1,
        minContainerWidth: 250,
        minContainerHeight: 300
      });
    };
    reader.readAsDataURL(file);
  }
});

document.getElementById('btnSubmit').addEventListener('click', () => {
  const newPass = document.getElementById('newPassword').value;
  if (newPass.trim() !== '') {
    Swal.fire({
      title: 'Konfirmasi Password Lama',
      input: 'password',
      inputLabel: 'Masukkan password lama Anda',
      inputAttributes: {
        autocomplete: 'current-password'
      },
      showCancelButton: true,
      confirmButtonText: 'Lanjutkan',
      preConfirm: value => {
        if (!value) {
          Swal.showValidationMessage('Password lama harus diisi!');
        }
        return value;
      }
    }).then(result => {
      if (result.isConfirmed) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'password_lama';
        input.value = result.value;
        document.querySelector('form').appendChild(input);

        if (cropper) {
          cropper.getCroppedCanvas({
            width: 250,
            height: 300
          }).toBlob(blob => {
            const reader = new FileReader();
            reader.onloadend = () => {
              document.getElementById('croppedImage').value = reader.result;
              document.querySelector('form').submit();
            };
            reader.readAsDataURL(blob);
          });
        } else {
          document.querySelector('form').submit();
        }
      }
    });
  } else {
    if (cropper) {
      cropper.getCroppedCanvas({
        width: 250,
        height: 300
      }).toBlob(blob => {
        const reader = new FileReader();
        reader.onloadend = () => {
          document.getElementById('croppedImage').value = reader.result;
          document.querySelector('form').submit();
        };
        reader.readAsDataURL(blob);
      });
    } else {
      document.querySelector('form').submit();
    }
  }
});

<?php if (isset($_SESSION['sukses'])): ?>
Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Profil berhasil diperbarui.' });
<?php unset($_SESSION['sukses']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
Swal.fire({ icon: 'error', title: 'Oops...', text: <?= json_encode($_SESSION['error']) ?> });
<?php unset($_SESSION['error']); endif; ?>
</script>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
