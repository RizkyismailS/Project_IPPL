<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>
<div class="page-heading">
    <h3>Dashboard</h3>
</div>
<div class="page-content">
    <section class="row">

        <div class="col-12 col-lg-9">

            <?php if (session()->getFlashdata('success')) : ?>
                <div class="alert alert-success" role="alert">
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')) : ?>
                <div class="alert alert-danger" role="alert">
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body py-4 px-4">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-xl">
                            <img src="/assets/images/faces/1.jpg" alt="Face 1">
                        </div>
                        <div class="ms-3 name">
                            <h5 class="font-bold">Selamat Datang, <?= esc($mahasiswa['nama']) ?>!</h5>
                            <h6 class="text-muted mb-0">NIM: <?= esc($mahasiswa['nim']) ?></h6>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($activeSession)) : ?>
            <div class="card border-primary shadow-sm mb-4">
                <div class="card-header bg-primary mb-4">
                    <h4 class="card-title mb-0 text-white"><i class="bi bi-broadcast"></i> Sesi Absensi Sedang Berlangsung!</h4>
                </div>
                <div class="card-body">
                    <h5 class="card-title"><?= esc($activeSession['nama_kelas']) ?></h5>
                    <p class="mb-1"><strong>Mata Kuliah:</strong> <?= esc($activeSession['nama_matakuliah']) ?></p>
                    <p class="mb-3"><strong>Topik Hari Ini:</strong> <?= esc($activeSession['topik_perkuliahan']) ?></p>
                    <form action="<?= base_url('mahasiswa/submitAbsensi') ?>" method="post" class="mt-3">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id_sesi" value="<?= $activeSession['id_sesi'] ?>">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> Lakukan Absensi Sekarang
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h4>Riwayat Aktivitas Kehadiran</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-lg">
                            <thead>
                                <tr>
                                    <th>Kelas</th>
                                    <th>Topik</th>
                                    <th>Waktu Absen</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($history)) : ?>
                                    <?php foreach ($history as $item) : ?>
                                        <tr>
                                            <td class="text-bold-500"><?= esc($item['nama_kelas']) ?></td>
                                            <td><?= esc($item['topik_perkuliahan']) ?></td>
                                            <td class="text-bold-500"><?= $item['waktu_absen'] ? date('d M Y, H:i', strtotime($item['waktu_absen'])) : '-' ?></td>
                                            <td>
                                                <?php if ($item['status_absen'] == 'hadir') : ?>
                                                    <span class="badge bg-success">Hadir</span>
                                                <?php elseif ($item['status_absen'] == 'sakit') : ?>
                                                    <span class="badge bg-warning">Sakit</span>
                                                <?php elseif ($item['status_absen'] == 'izin') : ?>
                                                    <span class="badge bg-info">Izin</span>
                                                <?php else : ?>
                                                    <span class="badge bg-danger">Alpa</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="4" class="text-center">Belum ada riwayat kehadiran.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-12 col-lg-3">
             <div class="card">
                <div class="card-header">
                    <h4>Statistik Kehadiran</h4>
                </div>
                <div class="card-body">

                    <div class="row mb-3">
                        <div class="col-9">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-success fs-4"></i>
                                <h5 class="mb-0 ms-3">Hadir</h5>
                            </div>
                        </div>
                        <div class="col-3">
                            <h5 class="mb-0 text-end"><?= esc($stats['hadir']) ?></h5>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-9">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-envelope-fill text-info fs-4"></i>

                                <h5 class="mb-0 ms-3">Sakit</h5>
                            </div>
                        </div>
                        <div class="col-3">
                            <h5 class="mb-0 text-end"><?= esc($stats['sakit']) ?></h5>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-9">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-envelope-fill text-warning fs-4"></i>
                                <h5 class="mb-0 ms-3">Izin</h5>
                            </div>
                        </div>
                        <div class="col-3">
                            <h5 class="mb-0 text-end"><?= esc($stats['izin']) ?></h5>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-9">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-x-circle-fill text-danger fs-4"></i>
                                <h5 class="mb-0 ms-3">Alpa</h5>
                            </div>
                        </div>
                        <div class="col-3">
                            <h5 class="mb-0 text-end"><?= esc($stats['alpa']) ?></h5>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</div>
<?= $this->endSection(); ?>