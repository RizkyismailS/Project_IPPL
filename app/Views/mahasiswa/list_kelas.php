<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<div class="page-heading">
    <h3>Kelas Saya</h3>
    <p class="text-subtitle text-muted">Daftar semua kelas yang Anda ikuti.</p>
</div>
<section class="section">
    <div class="row">
        <?php if (empty($kelas)) : ?>
            <div class="col-12">
                <div class="alert alert-warning text-center">
                    <h4 class="alert-heading">Belum Terdaftar</h4>
                    <p>Anda belum terdaftar di kelas manapun. Silakan enroll ke sebuah kelas terlebih dahulu.</p>
                </div>
            </div>
        <?php else : ?>
            <?php foreach ($kelas as $k) : ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <h5 class="card-title"><?= esc($k['nama_kelas']) ?></h5>
                                <p class="card-subtitle mb-2 text-muted"><?= esc($k['nama_matakuliah']) ?></p>
                                <hr>
                                <p class="card-text">
                                    <i class="bi bi-person-fill"></i> Dosen: <?= esc($k['nama_dosen']) ?>
                                </p>
                                <p class="card-text">
                                    <i class="bi bi-calendar-week-fill"></i> Jadwal: <?= esc($k['hari']) ?>, <?= esc(date('H:i', strtotime($k['waktu_mulai_kelas']))) ?> - <?= esc(date('H:i', strtotime($k['waktu_selesai_kelas']))) ?> WIB
                                </p>
                                <p class="card-text">
                                    <i class="bi bi-geo-alt-fill"></i> Ruangan: <?= esc($k['ruangan']) ?>
                                </p>
                            </div>
                        </div>
                        <div class="card-footer p-3">
                             <a href="#" class="btn btn-primary d-block">Lihat Sesi & Absensi</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?= $this->endSection() ?>