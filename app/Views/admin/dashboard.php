<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>


<div class="main-card">
    <h3 class="mb-4">Dashboard Admin</h3>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="p-3 bg-white rounded shadow-sm text-center">
                <div>Total Dosen</div>
                <h4><?= $total_dosen?></h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white rounded shadow-sm text-center">
                <div>Total Mahasiswa</div>
                <h4><?= $total_mahasiswa?></h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white rounded shadow-sm text-center">
                <div>Kelas Aktif</div>
                <h4><?= $total_kelas_aktif?></h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white rounded shadow-sm text-center">
                <div>Sesi Aktif</div>
                <h4><?= $total_sesi_aktif?></h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="bg-white p-3 rounded shadow-sm">
                <h5>Aktifitas Sesi Absensi</h5>
                <ul class="list-group">
                    <?php foreach ($aktifitas_sesi as $sesi): ?>
                        <?php if($sesi['waktu_selesai_kelas'] != $sesi['hitung_waktu']->format('H:i:s')){?>
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-success text-white">
                            <?= $sesi['nama_kelas']?> <span><?= $sesi['hitung_waktu']->format('i') ?></span>
                        </li>
                        <?php } else ?>
                    <?php endforeach; ?>
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
