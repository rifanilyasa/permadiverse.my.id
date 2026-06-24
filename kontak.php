<?php
session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kontak Kami - PERMADI</title>
  <link rel="icon" href="/title.png" type="image/png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    body {
      background: #f9f9f9;
    }
    .kontak-card {
      background: white;
      border-radius: 1rem;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      padding: 2rem;
    }
    .kontak-icon {
      font-size: 1.5rem;
      margin-right: 10px;
    }
    .kontak-link {
    text-decoration: none;
    color: inherit;
    }

    .kontak-link:hover {
    color: #198754; /* hijau seperti tombol Bootstrap */
    text-decoration: none;
    }
  </style>
</head>
<body>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>
<main class="flex-fill">
  <div class="container py-5">
    <div class="text-center mb-5">
      <h2 class="fw-bold">Hubungi Kami</h2>
      <p class="text-muted">Silakan hubungi kami melalui informasi berikut atau kirim pesan langsung ke tim kami.</p>
    </div>

    <div class="row g-4">
      <!-- Informasi Kontak -->
      <div class="col-md-6">
          <div class="kontak-card">
              <h5 class="fw-bold mb-4">Informasi Kontak</h5>

              <div class="row mb-3">
                  <div class="col-auto">
                      <i class="kontak-icon bi bi-geo-alt-fill text-danger"></i>
                  </div>
                  <div class="col">
                      Dukuh XII RW32, Nglahar, Sumbersari, Moyudan, Sleman, DI Yogyakarta, 55563.
                  </div>
              </div>

              <div class="row mb-3">
                  <div class="col-auto">
                      <i class="kontak-icon bi bi-envelope-fill text-primary"></i>
                  </div>
                  <div class="col">
                      <a href="mailto:permadixii@gmail.com" id="emailLink" class="kontak-link">permadixii@gmail.com</a>
                  </div>
              </div>

              <div class="row mb-3">
                  <div class="col-auto">
                      <i class="kontak-icon bi bi-globe2 text-success"></i>
                  </div>
                  <div class="col">
                      <a href="https://permadiverse.my.id" class="kontak-link" target="_blank">www.permadiverse.my.id</a>
                  </div>
              </div>

              <div class="row mb-3">
                  <div class="col-auto">
                      <i class="kontak-icon bi bi-tiktok text-dark"></i>
                  </div>
                  <div class="col">
                      <a href="https://www.tiktok.com/@permadiofficial" class="kontak-link" target="_blank">@permadiofficial</a>
                  </div>
              </div>

              <div class="row mb-3">
                  <div class="col-auto">
                      <i class="kontak-icon bi bi-instagram text-danger"></i>
                  </div>
                  <div class="col">
                      <a href="https://instagram.com/permadi.012" class="kontak-link" target="_blank">@permadi.012</a>
                  </div>
              </div>
          </div>
      </div>

      <!-- Form Kirim Pesan -->
      <div class="col-md-6">
      <div class="kontak-card">
          <h5 class="fw-bold mb-4">Kirim Pesan</h5>
          <form id="formKontak">
            <div class="mb-3">
              <label for="nama" class="form-label">Nama Anda</label>
              <input type="text" class="form-control" id="nama" name="nama" required>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Email Aktif</label>
              <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
              <label for="pesan" class="form-label">Pesan Anda</label>
              <textarea class="form-control" id="pesan" name="pesan" rows="5" required></textarea>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-dark">Kirim Sekarang</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Sukses -->
  <div class="modal fade" id="modalSukses" tabindex="-1" aria-labelledby="modalSuksesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content text-center">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="modalSuksesLabel">Pesan Terkirim</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
          <p class="mt-3">Pesan kamu berhasil dikirim! Kami akan segera membalas ya 🙌</p>
        </div>
      </div>
    </div>
  </div>
</main>
<!-- Bootstrap Icon -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<script>
  const email = "permadixii@gmail.com";
  const emailLink = document.getElementById("emailLink");

  function isMobileDevice() {
    return /Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
  }

  if (isMobileDevice()) {
    // Mobile: pakai mailto
    emailLink.setAttribute("href", "mailto:" + email);
  } else {
    // Desktop: pakai Gmail dan buka di tab baru
    emailLink.setAttribute("href", "https://mail.google.com/mail/?view=cm&to=" + email);
    emailLink.setAttribute("target", "_blank");
    emailLink.setAttribute("rel", "noopener noreferrer");
  }
</script>
<script>
  document.getElementById("formKontak").addEventListener("submit", function(e) {
    e.preventDefault();

    const form = e.target;
    const data = new FormData(form);

    fetch("https://formspree.io/f/moverpkg", {
      method: "POST",
      body: data,
      headers: {
        'Accept': 'application/json'
      }
    }).then(response => {
      if (response.ok) {
        // Tampilkan modal Bootstrap
        const modalSukses = new bootstrap.Modal(document.getElementById('modalSukses'));
        modalSukses.show();
        form.reset(); // reset form
      } else {
        alert("Oops! Terjadi kesalahan. Coba lagi ya.");
      }
    }).catch(error => {
      alert("Oops! Gagal mengirim. Periksa koneksi atau coba nanti.");
    });
  });
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>