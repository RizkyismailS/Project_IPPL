<?= $this->extend('layout/main_template') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="card shadow-sm p-4" style="border-radius: 15px;">
        <h4 class="mb-4"><strong>Gabung ke kelas</strong></h4>

        <!-- Info akun -->
        <div class="border rounded mb-4 p-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-secondary" style="width: 45px; height: 45px;"></div>
                <div>
                    <div class="text-muted">Akun yang digunakan</div>
                    <div class="fw-bold">Jhon doe</div>
                    <small class="text-muted">Jhondoe234@gmail.com</small>
                </div>
            </div>
            <button class="btn btn-outline-primary">Ganti Akun</button>
        </div>

        <!-- Input kode kelas -->
        <div class="border rounded mb-4 p-3">
            <label for="kodeKelas" class="fw-bold mb-1">Kode kelas</label>
            <p class="text-muted mb-2">Mintalah kode kelas kepada pengajar, dan masukan di sini.</p>
            <input type="text" class="form-control" id="kodeKelas" placeholder="Kode kelas">
        </div>

        <!-- Petunjuk -->
        <div class="border rounded p-3">
            <div class="fw-bold mb-2">PETUNJUK</div>
            <ul class="mb-0">
                <li>Gunakan akun yang diberi otorisasi</li>
                <li>Gunakan kode kelas yang terdiri dari 5–8 huruf atau angka, tanpa spasi atau simbol</li>
            </ul>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
