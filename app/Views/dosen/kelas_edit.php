<?= $this->extend('layout/template') ?> {/* Sesuaikan dengan layout dosen Anda */}

<?= $this->section('content') ?>
<?php
$breadcrumb = 'Edit Kelas';
$pageTitle = $title ?? 'Edit Kelas';
echo view('layout/dosen_header', compact('breadcrumb', 'pageTitle')); 
?>

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <?php if (isset($kelas['nama_kelas'])): ?>
                    <p class="text-subtitle text-muted">Mengubah detail untuk kelas: <?= esc($kelas['nama_kelas']) ?> (<?= esc($kelas['kode_kelas']) ?>)</p>
                <?php endif; ?>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= base_url('dosen/dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('dosen/kelas') ?>">Kelola Kelas</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Kelas</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<section class="section">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Formulir Edit Kelas</h4>
        </div>
        <div class="card-body">
            <?php 
            $form_errors = session()->getFlashdata('errors') ?? (isset($errors) ? $errors : []);
            $model_errors = session()->getFlashdata('errors_kelas_update') ?? (isset($errors_kelas_update) ? $errors_kelas_update : []);
            
            if (!empty($form_errors) || !empty($model_errors) || session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h4 class="alert-heading">Terjadi Kesalahan!</h4>
                    <?php if (session()->getFlashdata('error')): ?>
                        <p><?= session()->getFlashdata('error') ?></p>
                    <?php endif; ?>
                    <?php if (!empty($form_errors)): ?>
                        <ul>
                            <?php foreach ($form_errors as $field => $error_item): ?>
                                <li><?= esc($error_item) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <?php if (!empty($model_errors)): ?>
                        <p>Detail Error Model:</p>
                        <ul>
                            <?php foreach ($model_errors as $field => $error_item): ?>
                                <li><?= esc($field) ?>: <?= esc($error_item) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (empty($kelas)): ?>
                <div class="alert alert-warning">Data kelas tidak ditemukan atau tidak bisa diedit.</div>
            <?php else: ?>
                <form action="<?= base_url('/dosen/kelas/update/' . esc($kelas['kode_kelas'], 'attr')) ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="PUT"> 

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="kode_kelas_display" class="form-label">Kode Kelas</label>
                                <input type="text" class="form-control" id="kode_kelas_display" 
                                       value="<?= esc($kelas['kode_kelas']) ?>" readonly>
                                <small class="text-muted">Kode kelas tidak dapat diubah.</small>
                                </div>

                            <div class="mb-3">
                                <label for="nama_kelas" class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= isset($form_errors['nama_kelas']) ? 'is-invalid' : '' ?>" 
                                       id="nama_kelas" name="nama_kelas" 
                                       value="<?= old('nama_kelas', esc($kelas['nama_kelas'])) ?>" required>
                                <?php if (isset($form_errors['nama_kelas'])): ?><div class="invalid-feedback"><?= esc($form_errors['nama_kelas']) ?></div><?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="kode_matakuliah" class="form-label">Mata Kuliah <span class="text-danger">*</span></label>
                                <select class="form-select <?= isset($form_errors['kode_matakuliah']) ? 'is-invalid' : '' ?>" id="kode_matakuliah" name="kode_matakuliah" required>
                                    <option value="">-- Pilih Mata Kuliah --</option>
                                    <?php if (!empty($mata_kuliah_list)): ?>
                                        <?php foreach ($mata_kuliah_list as $mk): ?>
                                            <option value="<?= esc($mk['kode_matakuliah']) ?>" 
                                                <?= old('kode_matakuliah', $kelas['kode_matakuliah']) == $mk['kode_matakuliah'] ? 'selected' : '' ?>>
                                                <?= esc($mk['nama_matakuliah']) ?> (<?= esc($mk['sks']) ?> SKS)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <?php if (isset($form_errors['kode_matakuliah'])): ?><div class="invalid-feedback"><?= esc($form_errors['kode_matakuliah']) ?></div><?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="dosen_pengampu_display" class="form-label">Dosen Pengampu</label>
                                <input type="text" class="form-control" id="dosen_pengampu_display" 
                                       value="<?= esc($nama_dosen_login ?? ($kelas['nama_dosen'] ?? 'Tidak Ditemukan')) ?> (<?= esc($dosen_nip_login ?? ($kelas['dosen_nip'] ?? '')) ?>)" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label for="tahun_ajaran" class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= isset($form_errors['tahun_ajaran']) ? 'is-invalid' : '' ?>" 
                                       id="tahun_ajaran" name="tahun_ajaran" 
                                       value="<?= old('tahun_ajaran', esc($kelas['tahun_ajaran'])) ?>" 
                                       placeholder="Contoh: <?= date('Y') ?>/<?= date('Y')+1 ?>" required>
                                <?php if (isset($form_errors['tahun_ajaran'])): ?><div class="invalid-feedback"><?= esc($form_errors['tahun_ajaran']) ?></div><?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="semester" class="form-label">Semester <span class="text-danger">*</span></label>
                                <select class="form-select <?= isset($form_errors['semester']) ? 'is-invalid' : '' ?>" id="semester" name="semester" required>
                                    <option value="">-- Pilih Semester --</option>
                                    <option value="Ganjil" <?= old('semester', $kelas['semester']) == 'Ganjil' ? 'selected' : '' ?>>Ganjil</option>
                                    <option value="Genap" <?= old('semester', $kelas['semester']) == 'Genap' ? 'selected' : '' ?>>Genap</option>
                                    <option value="Pendek" <?= old('semester', $kelas['semester']) == 'Pendek' ? 'selected' : '' ?>>Pendek</option>
                                    <option value="Antara" <?= old('semester', $kelas['semester']) == 'Antara' ? 'selected' : '' ?>>Antara</option>
                                </select>
                                <?php if (isset($form_errors['semester'])): ?><div class="invalid-feedback"><?= esc($form_errors['semester']) ?></div><?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="hari" class="form-label">Hari Kuliah <span class="text-danger">*</span></label>
                                <select class="form-select <?= isset($form_errors['hari']) ? 'is-invalid' : '' ?>" id="hari" name="hari" required>
                                    <option value="">-- Pilih Hari --</option>
                                    <option value="Senin" <?= old('hari', $kelas['hari']) == 'Senin' ? 'selected' : '' ?>>Senin</option>
                                    <option value="Selasa" <?= old('hari', $kelas['hari']) == 'Selasa' ? 'selected' : '' ?>>Selasa</option>
                                    <option value="Rabu" <?= old('hari', $kelas['hari']) == 'Rabu' ? 'selected' : '' ?>>Rabu</option>
                                    <option value="Kamis" <?= old('hari', $kelas['hari']) == 'Kamis' ? 'selected' : '' ?>>Kamis</option>
                                    <option value="Jumat" <?= old('hari', $kelas['hari']) == 'Jumat' ? 'selected' : '' ?>>Jumat</option>
                                    <option value="Sabtu" <?= old('hari', $kelas['hari']) == 'Sabtu' ? 'selected' : '' ?>>Sabtu</option>
                                    <option value="Minggu" <?= old('hari', $kelas['hari']) == 'Minggu' ? 'selected' : '' ?>>Minggu</option>
                                </select>
                                <?php if (isset($form_errors['hari'])): ?><div class="invalid-feedback"><?= esc($form_errors['hari']) ?></div><?php endif; ?>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="waktu_mulai_kelas" class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control <?= isset($form_errors['waktu_mulai_kelas']) ? 'is-invalid' : '' ?>" 
                                           id="waktu_mulai_kelas" name="waktu_mulai_kelas" 
                                           value="<?= old('waktu_mulai_kelas', esc(date('H:i', strtotime($kelas['waktu_mulai_kelas'])))) ?>" required>
                                    <?php if (isset($form_errors['waktu_mulai_kelas'])): ?><div class="invalid-feedback"><?= esc($form_errors['waktu_mulai_kelas']) ?></div><?php endif; ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="waktu_selesai_kelas" class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control <?= isset($form_errors['waktu_selesai_kelas']) ? 'is-invalid' : '' ?>" 
                                           id="waktu_selesai_kelas" name="waktu_selesai_kelas" 
                                           value="<?= old('waktu_selesai_kelas', esc(date('H:i', strtotime($kelas['waktu_selesai_kelas'])))) ?>" required>
                                    <?php if (isset($form_errors['waktu_selesai_kelas'])): ?><div class="invalid-feedback"><?= esc($form_errors['waktu_selesai_kelas']) ?></div><?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="ruangan" class="form-label">Ruangan <small class="text-muted">(opsional)</small></label>
                                <input type="text" class="form-control <?= isset($form_errors['ruangan']) ? 'is-invalid' : '' ?>" 
                                       id="ruangan" name="ruangan" value="<?= old('ruangan', esc($kelas['ruangan'])) ?>">
                                <?php if (isset($form_errors['ruangan'])): ?><div class="invalid-feedback"><?= esc($form_errors['ruangan']) ?></div><?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="kode_enrollment" class="form-label">Kode Enrollment</label>
                                <input type="text" class="form-control <?= isset($form_errors['kode_enrollment']) || isset($model_errors['kode_enrollment']) ? 'is-invalid' : '' ?>" 
                                       id="kode_enrollment" name="kode_enrollment" 
                                       value="<?= old('kode_enrollment', esc($kelas['kode_enrollment'])) ?>">
                                <small class="text-muted">Bisa diubah. Harus unik jika diisi. Kosongkan untuk tidak mengubah jika sudah ada.</small>
                                <?php if (isset($form_errors['kode_enrollment'])): ?><div class="invalid-feedback"><?= esc($form_errors['kode_enrollment']) ?></div><?php endif; ?>
                                <?php if (isset($model_errors['kode_enrollment'])): ?><div class="invalid-feedback d-block"><?= esc($model_errors['kode_enrollment']) ?></div><?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary me-1 mb-1">Update Kelas</button>
                        <a href="<?= base_url('dosen/kelas/detail/' . esc($kelas['kode_kelas'], 'url')) ?>" class="btn btn-light-secondary me-1 mb-1">Batal</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<?= $this->endSection() ?>