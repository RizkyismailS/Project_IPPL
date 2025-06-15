<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>
<div class="page-heading">
    <h3>Edit Sesi Absensi</h3>
    <p class="text-subtitle text-muted">Untuk Kelas: <?= esc($kelas['nama_kelas']); ?></p>
</div>

<section class="section">
    <div class="card">
        <div class="card-body">
            <form action="<?= base_url('dosen/sesi-absensi/update/' . $sesi['id_sesi']); ?>" method="post">
                <?= csrf_field(); ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="topik_perkuliahan" class="form-label">Topik Perkuliahan</label>
                            <textarea class="form-control <?= ($validation->hasError('topik_perkuliahan')) ? 'is-invalid' : ''; ?>" id="topik_perkuliahan" name="topik_perkuliahan" rows="3" required><?= old('topik_perkuliahan', $sesi['topik_perkuliahan']); ?></textarea>
                            <div class="invalid-feedback"><?= $validation->getError('topik_perkuliahan'); ?></div>
                        </div>

                         <div class="mb-3">
                            <label for="perlu_bukti_foto" class="form-label">Perlu Bukti Foto?</label>
                            <select class="form-select <?= ($validation->hasError('perlu_bukti_foto')) ? 'is-invalid' : ''; ?>" name="perlu_bukti_foto" id="perlu_bukti_foto">
                                <option value="1" <?= old('perlu_bukti_foto', $sesi['perlu_bukti_foto']) == '1' ? 'selected' : '' ?>>Ya</option>
                                <option value="0" <?= old('perlu_bukti_foto', $sesi['perlu_bukti_foto']) == '0' ? 'selected' : '' ?>>Tidak</option>
                            </select>
                            <div class="invalid-feedback"><?= $validation->getError('perlu_bukti_foto'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                         <div class="mb-3">
                            <label for="tanggal_sesi" class="form-label">Tanggal Sesi</label>
                            <input type="date" class="form-control <?= ($validation->hasError('tanggal_sesi')) ? 'is-invalid' : ''; ?>" id="tanggal_sesi" name="tanggal_sesi" value="<?= old('tanggal_sesi', $sesi['tanggal_sesi']); ?>" required>
                            <div class="invalid-feedback"><?= $validation->getError('tanggal_sesi'); ?></div>
                        </div>
                        <div class="mb-3">
                            <label for="waktu_mulai_aktual" class="form-label">Waktu Buka Absen</label>
                            <input type="time" class="form-control <?= ($validation->hasError('waktu_mulai_aktual')) ? 'is-invalid' : ''; ?>" id="waktu_mulai_aktual" name="waktu_mulai_aktual" value="<?= old('waktu_mulai_aktual', date('H:i', strtotime($sesi['waktu_mulai_aktual']))); ?>" required>
                            <div class="invalid-feedback"><?= $validation->getError('waktu_mulai_aktual'); ?></div>
                        </div>
                        <div class="mb-3">
                            <label for="waktu_selesai_aktual" class="form-label">Waktu Tutup Absen</label>
                            <input type="time" class="form-control <?= ($validation->hasError('waktu_selesai_aktual')) ? 'is-invalid' : ''; ?>" id="waktu_selesai_aktual" name="waktu_selesai_aktual" value="<?= old('waktu_selesai_aktual', date('H:i', strtotime($sesi['waktu_selesai_aktual']))); ?>" required>
                            <div class="invalid-feedback"><?= $validation->getError('waktu_selesai_aktual'); ?></div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Update Sesi</button>
                <a href="<?= base_url('dosen/kelas/detail/' . $kelas['kode_kelas']) ?>" class="btn btn-light">Batal</a>
            </form>
        </div>
    </div>
</section>
<?= $this->endSection(); ?>