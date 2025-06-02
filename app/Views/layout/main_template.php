
<!-- template untuk dashboard admin dan mahasiswa -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Dashboard' ?></title>
    <link rel="stylesheet" href="/assets/css/bootstrap.css">
    <link rel="stylesheet" href="/assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/pages/dashboard.css">
    <link rel="stylesheet" href="/assets/css/pages/mahasiswa.css">
    <!-- Tambahkan perfect-scrollbar jika menggunakan template Mazer -->
    <link rel="stylesheet" href="/assets/vendors/perfect-scrollbar/perfect-scrollbar.css">
</head>



<body>
    <!-- Sidebar ditampilkan sesuai role -->
    
    
    <div id="main">
        <header class="mb-3">
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>

        <div class="page-heading">
            <h3><?= $title ?? 'Dashboard' ?></h3>
        </div>
        
        <div class="page-content">
            <?= $this->renderSection('content'); ?>
        </div>

        <footer>
            <div class="footer clearfix mb-0 text-muted">
                <div class="float-start">
                    <p><?= date('Y') ?> &copy; IPPL Project</p>
                </div>
                <div class="float-end">
                    <p>Crafted by <span class="text-danger">Your Team Name</span></p>
                </div>
            </div>
        </footer>
    </div>

    <script src="/assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/main.js"></script>
    
    <?php if (isset($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <script>
    function cekKodeKelas() {
        const kode = document.getElementById('kodeKelas').value.trim();
    
        if (kode.length >= 5 && kode.length <= 8) {
            const modal = new bootstrap.Modal(document.getElementById('modalEnrolKelas'));
            modal.show();
        } else {
            alert('Kode kelas tidak valid. Masukkan 5–8 huruf atau angka.');
        }
    }
    </script>

    <?= $this->renderSection('scripts') ?>
</body>
</html>