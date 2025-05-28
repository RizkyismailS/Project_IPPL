
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
</head>

<?=$this->include($navbar)?>
<body>
    
    <div class="layout-wrapper">

        <!-- Main Body -->
        <div class="main-wrapper">
            <div class="sidebar">
                <?= $this->include($sidebar) ?>
            </div>
            <div class="content">
                <?= $this->renderSection('content') ?>
            </div>
        </div>
    </div>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script>
function cekKodeKelas() {
    const kode = document.getElementById('kodeKelas').value.trim();

    // Contoh validasi sederhana (panjang kode antara 5–8)
    if (kode.length >= 5 && kode.length <= 8) {
        const modal = new bootstrap.Modal(document.getElementById('modalEnrolKelas'));
        modal.show();
    } else {
        alert('Kode kelas tidak valid. Masukkan 5–8 huruf atau angka.');
    }
}
</script>

</body>
</html>
