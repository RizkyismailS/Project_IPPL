<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<div class="page-heading">
    <div class="d-flex justify-content-between">
        <div>
            <h3>Daftar Sesi Absensi</h3>
            <p class="text-subtitle text-muted">Kelas: <?= esc($kelas['nama_kelas']) ?></p>
        </div>
        <a href="/mahasiswa/kelas" class="btn btn-secondary align-self-start">
            <i class="bi bi-arrow-left"></i> Kembali ke Kelas Saya
        </a>
    </div>
</div>

<section class="section">
    <div class="row">
        <div class="col-12">
            <?php if (session()->getFlashdata('success')) : ?>
                <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')) : ?>
                <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <?php if (empty($sesi_list)) : ?>
                <div class="alert alert-light-info">
                    Belum ada sesi absensi yang dibuat untuk kelas ini.
                </div>
            <?php else : ?>
                <?php foreach ($sesi_list as $sesi) : ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title"><?= esc($sesi['topik_perkuliahan'] ?: 'Perkuliahan Rutin') ?></h5>
                                    <p class="text-muted mb-0">
                                        <?= date('l, d F Y', strtotime($sesi['tanggal_sesi'])) ?> 
                                        pukul <?= date('H:i', strtotime($sesi['waktu_mulai_aktual'])) ?> WIB
                                    </p>
                                </div>
                                <div class="text-end" style="min-width: 140px;">
                                    
                                    <?php
                                        $status = $sesi['status_final'];
                                        
                                        if ($status == 'ABSEN_SEKARANG') :
                                    ?>
                                        <form action="<?= base_url('mahasiswa/submitAbsensi') ?>" method="post">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id_sesi" value="<?= $sesi['id_sesi'] ?>">
                                            <button type="submit" class="btn btn-success">
                                                <i class="bi bi-check-circle"></i> Absen Sekarang
                                            </button>
                                        </form>
                                    <?php 
                                        else :
                                            $badgeClass = 'bg-secondary'; // Default untuk 'Akan Datang'
                                            if ($status == 'hadir') $badgeClass = 'bg-success';
                                            if ($status == 'sakit') $badgeClass = 'bg-warning';
                                            if ($status == 'izin') $badgeClass = 'bg-info';
                                            if ($status == 'Alpa' || $status == 'alpa') $badgeClass = 'bg-danger';
                                    ?>
                                        <span class="badge <?= $badgeClass ?> fs-6"><?= esc(ucfirst($status)) ?></span>
                                    <?php endif; ?>
                                    </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?= $this->endSection() ?>