<footer class="bg-dark text-white py-4 mt-5">
  <div class="container text-center">
    <p class="mb-0">© <?= date('Y') ?> ILYASA for PERMADI</p>
  </div>
</footer>

<!-- Tambahkan ini agar fungsi toggle navbar bekerja -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const navbarCollapse = document.getElementById('navbarNav');
    const navbarToggler = document.querySelector('.navbar-toggler');

    // Fungsi untuk menutup navbar
    function closeNavbar() {
      if (navbarCollapse.classList.contains('show')) {
        navbarToggler.click(); // Menutup navbar
      }
    }

    // Tutup saat klik di luar navbar
    document.addEventListener('click', function (event) {
      const isClickInside = navbarCollapse.contains(event.target) || navbarToggler.contains(event.target);
      if (!isClickInside) {
        closeNavbar();
      }
    });

    // Tutup saat scroll
    let lastScrollTop = 0;
    window.addEventListener('scroll', function () {
      const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
      if (scrollTop !== lastScrollTop) {
        closeNavbar();
        lastScrollTop = scrollTop;
      }
    });
  });
</script>
</body>
</html>
