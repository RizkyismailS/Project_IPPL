<?= $this->extend('layout/main_template') ?>
<?= $this->section('content') ?>

<style>
    .main-card {
        background-color: #f7f7f7;
        border-radius: 16px;
        padding: 2rem;
    }
</style>

<div class="main-card">
    <h3 class="mb-4">Dashboard Admin</h3>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="p-3 bg-white rounded shadow-sm text-center">
                <div><i class="bi bi-person-video2"></i></div>
                <div>Total Dosen</div>
                <h4>40</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white rounded shadow-sm text-center">
                <div><i class="bi bi-mortarboard"></i></div>
                <div>Total Mahasiswa</div>
                <h4>1.204</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white rounded shadow-sm text-center">
                <div><i class="bi bi-clock"></i></div>
                <div>Kelas Aktif</div>
                <h4>12</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white rounded shadow-sm text-center">
                <div><i class="bi bi-phone"></i></div>
                <div>Sesi Aktif</div>
                <h4>8</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="bg-white p-3 rounded shadow-sm">
                <h5>Aktifitas Sesi Absensi</h5>
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-success text-white">
                        Pemrograman Web Dasar <span>25 Min left</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-success text-white">
                        Sistem Operasi <span>15 Min left</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-success text-white">
                        Statistika <span>03 Min left</span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="bg-white p-3 rounded shadow-sm">
                <h5>Notifikasi System</h5>
                <ul class="list-group">
                    <li class="list-group-item"><i class="bi bi-info-circle"></i> New lecturer account created for Dr. Emily White</li>
                    <li class="list-group-item"><i class="bi bi-check-circle"></i> Database Systems class attendance completed</li>
                    <li class="list-group-item"><i class="bi bi-exclamation-circle"></i> System backup scheduled for tonight at 2 AM</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
