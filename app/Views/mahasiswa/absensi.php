<!-- absen_kelas.php -->
<?= $this->extend('layout/main_template') ?>
<?= $this->section('content') ?>

<div class="page-content">
    <section class="row">
        <div class="col-12">
            <h3>Absensi Kelas <span class="text-primary">24 Mei 2025</span></h3>

            <div class="row mt-4">
                <?php foreach ($kelas as $k): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-header text-white <?= $k['warna'] ?>">
                                <strong><?= $k['nama_matkul'] ?></strong><br>
                                <small><?= $k['kelas'] ?></small>
                            </div>
                            <div class="card-body" style="height: 200px;">
                                <div class="mb-4" style="padding: 20px 0 5px 0;">
                                    <p class="text-muted mb-1">Batas waktu absensi</p>
                                    <p class="fw-bold"><?= $k['jam_mulai'] ?> - <?= $k['jam_selesai'] ?></p>
                                </div>

                                <?php if ($k['status'] == 'hadir'): ?>
                                    <div class="alert alert-primary text-center mb-0">Hadir kelas</div>
                                <?php else: ?>
                                    <div class="alert alert-danger text-center mb-0">Tidak Hadir</div>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
<?php endforeach; ?>


            </div>
        </div>
    </section>
</div>

<?= $this->endSection() ?>
