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
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <?php if (empty($sesi_list)): ?>
            <div class="alert alert-light-info">
                Belum ada sesi absensi yang dibuat untuk kelas ini.
            </div>
            <div class="row row-cols-2 row-cols-lg-5 g-2 g-lg-3">
            <?php else: ?>
                <?php foreach ($sesi_list as $sesi): ?>
                    <div class="col">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5><?= esc($sesi['topik_perkuliahan']) ?></h5>
                                <p><?= date('l, d F Y H:i', strtotime($sesi['waktu_mulai_aktual'])) ?> WIB</p>
                                <?php if ($sesi['status_final'] === 'ABSEN_SEKARANG'): ?>
                                    <form action="<?= base_url('mahasiswa/submitAbsensi') ?>" method="post"
                                        enctype="multipart/form-data">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id_sesi" value="<?= esc($sesi['id_sesi']) ?>">
                                        <div class="mb-2">
                                            <label for="status_absen_<?= $sesi['id_sesi'] ?>" class="form-label">Status
                                                Kehadiran</label>
                                            <select name="status_absen" id="status_absen_<?= $sesi['id_sesi'] ?>"
                                                class="form-select" required>
                                                <option value="hadir">Hadir</option>
                                                <option value="sakit">Sakit</option>
                                                <option value="izin">Izin</option>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label for="keterangan_<?= $sesi['id_sesi'] ?>" class="form-label">Keterangan
                                                (opsional)</label>
                                            <input type="text" name="keterangan" id="keterangan_<?= $sesi['id_sesi'] ?>"
                                                class="form-control">
                                        </div>
                                        <?php if ($sesi['perlu_bukti_foto']): ?>
                                            <div class="mb-2">
                                                <label for="bukti_foto_<?= $sesi['id_sesi'] ?>" class="form-label">Bukti Foto
                                                    (wajib)</label>
                                                <input type="file" name="bukti_foto" id="bukti_foto_<?= $sesi['id_sesi'] ?>"
                                                    class="form-control" accept="image/*" required>
                                            </div>
                                        <?php endif; ?>
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-check-circle"></i> Absen Sekarang
                                        </button>
                                    </form>
                                <?php elseif ($sesi['status_final'] === 'Alpa'): ?>
                                    <span class="badge bg-danger">Alpa</span>
                                <?php elseif ($sesi['status_final'] === 'Hadir'): ?>
                                    <span class="badge bg-success">Hadir</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= esc($sesi['status_final']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?= $this->endSection() ?>