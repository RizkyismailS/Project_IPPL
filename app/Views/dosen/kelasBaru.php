<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>
<?php
  $breadcrumb = 'kelas Baru';
  $pageTitle = 'Buat kelas Baru';
  echo view('layout/dosen_header', compact('breadcrumb', 'pageTitle'));
?>
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <p class="text-subtitle text-muted">Lengkapi formulir di bawah ini untuk membuat kelas baru.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= base_url('dosen/dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('dosen/kelas') ?>">Kelola Kelas</a></li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<section class="section">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Formulir Kelas Baru</h4>
        </div>
        <div class="card-body">
            <?php 
            // Menampilkan error validasi dari controller atau model
            $form_errors = session()->getFlashdata('errors') ?? (isset($errors) ? $errors : []);
            $model_errors = session()->getFlashdata('errors_kelas') ?? (isset($errors_kelas) ? $errors_kelas : []);
            
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


            <form action="<?= base_url('dosen/kelas/store') ?>" method="post">
                <?= csrf_field() ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="kode_kelas" class="form-label">Kode Kelas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($form_errors['kode_kelas']) || isset($model_errors['kode_kelas']) ? 'is-invalid' : '' ?>" 
                                   id="kode_kelas" name="kode_kelas" value="<?= old('kode_kelas') ?>" required>
                            <small class="text-muted">Contoh: IF21A, TI22B-Pagi. Harus unik.</small>
                            <?php if (isset($form_errors['kode_kelas'])): ?><div class="invalid-feedback"><?= esc($form_errors['kode_kelas']) ?></div><?php endif; ?>
                            <?php if (isset($model_errors['kode_kelas'])): ?><div class="invalid-feedback d-block"><?= esc($model_errors['kode_kelas']) ?></div><?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="nama_kelas" class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($form_errors['nama_kelas']) ? 'is-invalid' : '' ?>" 
                                   id="nama_kelas" name="nama_kelas" value="<?= old('nama_kelas') ?>" required>
                            <?php if (isset($form_errors['nama_kelas'])): ?><div class="invalid-feedback"><?= esc($form_errors['nama_kelas']) ?></div><?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="kode_matakuliah" class="form-label">Mata Kuliah <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($form_errors['kode_matakuliah']) ? 'is-invalid' : '' ?>" id="kode_matakuliah" name="kode_matakuliah" required>
                                <option value="">-- Pilih Mata Kuliah --</option>
                                <?php if (!empty($mata_kuliah_list)): ?>
                                    <?php foreach ($mata_kuliah_list as $mk): ?>
                                        <option value="<?= esc($mk['kode_matakuliah']) ?>" <?= old('kode_matakuliah') == $mk['kode_matakuliah'] ? 'selected' : '' ?>>
                                            <?= esc($mk['nama_matakuliah']) ?> (<?= esc($mk['sks']) ?> SKS)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <?php if (isset($form_errors['kode_matakuliah'])): ?><div class="invalid-feedback"><?= esc($form_errors['kode_matakuliah']) ?></div><?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="dosen_pengampu" class="form-label">Dosen Pengampu</label>
                            <input type="text" class="form-control" id="dosen_pengampu" 
                                   value="<?= esc($nama_dosen_login ?? 'Tidak Ditemukan') ?> (<?= esc($dosen_nip_login ?? '') ?>)" readonly>
                            {/* NIP Dosen akan dikirim secara otomatis dari sesi, tidak perlu input manual oleh dosen */}
                        </div>
                        
                        <div class="mb-3">
                            <label for="tahun_ajaran" class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($form_errors['tahun_ajaran']) ? 'is-invalid' : '' ?>" 
                                   id="tahun_ajaran" name="tahun_ajaran" value="<?= old('tahun_ajaran', date('Y').'/'.(date('Y')+1)) ?>" 
                                   placeholder="Contoh: <?= date('Y') ?>/<?= date('Y')+1 ?>" required>
                            <?php if (isset($form_errors['tahun_ajaran'])): ?><div class="invalid-feedback"><?= esc($form_errors['tahun_ajaran']) ?></div><?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="semester" class="form-label">Semester <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($form_errors['semester']) ? 'is-invalid' : '' ?>" id="semester" name="semester" required>
                                <option value="">-- Pilih Semester --</option>
                                <option value="Ganjil" <?= old('semester') == 'Ganjil' ? 'selected' : '' ?>>Ganjil</option>
                                <option value="Genap" <?= old('semester') == 'Genap' ? 'selected' : '' ?>>Genap</option>
                                <option value="Pendek" <?= old('semester') == 'Pendek' ? 'selected' : '' ?>>Pendek</option>
                                <option value="Antara" <?= old('semester') == 'Antara' ? 'selected' : '' ?>>Antara</option>
                                {/* Tambahkan opsi semester lain jika perlu */}
                            </select>
                            <?php if (isset($form_errors['semester'])): ?><div class="invalid-feedback"><?= esc($form_errors['semester']) ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="hari" class="form-label">Hari Kuliah <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($form_errors['hari']) ? 'is-invalid' : '' ?>" id="hari" name="hari" required>
                                <option value="">-- Pilih Hari --</option>
                                <option value="Senin" <?= old('hari') == 'Senin' ? 'selected' : '' ?>>Senin</option>
                                <option value="Selasa" <?= old('hari') == 'Selasa' ? 'selected' : '' ?>>Selasa</option>
                                <option value="Rabu" <?= old('hari') == 'Rabu' ? 'selected' : '' ?>>Rabu</option>
                                <option value="Kamis" <?= old('hari') == 'Kamis' ? 'selected' : '' ?>>Kamis</option>
                                <option value="Jumat" <?= old('hari') == 'Jumat' ? 'selected' : '' ?>>Jumat</option>
                                <option value="Sabtu" <?= old('hari') == 'Sabtu' ? 'selected' : '' ?>>Sabtu</option>
                                <option value="Minggu" <?= old('hari') == 'Minggu' ? 'selected' : '' ?>>Minggu</option>
                            </select>
                            <?php if (isset($form_errors['hari'])): ?><div class="invalid-feedback"><?= esc($form_errors['hari']) ?></div><?php endif; ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="waktu_mulai_kelas" class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                                <input type="time" class="form-control <?= isset($form_errors['waktu_mulai_kelas']) ? 'is-invalid' : '' ?>" 
                                       id="waktu_mulai_kelas" name="waktu_mulai_kelas" value="<?= old('waktu_mulai_kelas') ?>" required>
                                <?php if (isset($form_errors['waktu_mulai_kelas'])): ?><div class="invalid-feedback"><?= esc($form_errors['waktu_mulai_kelas']) ?></div><?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="waktu_selesai_kelas" class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
                                <input type="time" class="form-control <?= isset($form_errors['waktu_selesai_kelas']) ? 'is-invalid' : '' ?>" 
                                       id="waktu_selesai_kelas" name="waktu_selesai_kelas" value="<?= old('waktu_selesai_kelas') ?>" required>
                                <?php if (isset($form_errors['waktu_selesai_kelas'])): ?><div class="invalid-feedback"><?= esc($form_errors['waktu_selesai_kelas']) ?></div><?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="ruangan" class="form-label">Ruangan <small class="text-muted">(opsional)</small></label>
                            <input type="text" class="form-control <?= isset($form_errors['ruangan']) ? 'is-invalid' : '' ?>" 
                                   id="ruangan" name="ruangan" value="<?= old('ruangan') ?>">
                            <?php if (isset($form_errors['ruangan'])): ?><div class="invalid-feedback"><?= esc($form_errors['ruangan']) ?></div><?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="kode_enrollment" class="form-label">Kode Enrollment <small class="text-muted">(opsional)</small></label>
                            <input type="text" class="form-control <?= isset($form_errors['kode_enrollment']) || isset($model_errors['kode_enrollment']) ? 'is-invalid' : '' ?>" 
                                   id="kode_enrollment" name="kode_enrollment" value="<?= old('kode_enrollment') ?>">
                            <small class="text-muted">Jika dikosongkan, akan digenerate otomatis. Harus unik jika diisi.</small>
                            <?php if (isset($form_errors['kode_enrollment'])): ?><div class="invalid-feedback"><?= esc($form_errors['kode_enrollment']) ?></div><?php endif; ?>
                            <?php if (isset($model_errors['kode_enrollment'])): ?><div class="invalid-feedback d-block"><?= esc($model_errors['kode_enrollment']) ?></div><?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-12 d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary me-1 mb-1">Simpan Kelas</button>
                    <a href="<?= base_url('dosen/kelas') ?>" class="btn btn-light-secondary me-1 mb-1">Batal</a>
                </div>
            </form>
        </div>
    </div>
</section>

<?= $this->endSection() ?>