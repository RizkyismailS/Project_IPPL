<?php
?>
<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Edit Data Mahasiswa</h3>
                <p class="text-subtitle text-muted">Form untuk memperbarui data mahasiswa dan akun loginnya.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= base_url('/admin/dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('/admin/mahasiswa/list') ?>">Manage Mahasiswa</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Mahasiswa</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<section class="section">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Form Edit Mahasiswa</h4>
        </div>
        <div class="card-body">
            <?php if (session()->getFlashdata('error') || (isset($errors) && !empty($errors))): ?>
                <div class="alert alert-danger">
                    <h4 class="alert-heading">Terjadi Kesalahan!</h4>
                    <?php if (session()->getFlashdata('error')): ?>
                        <p><?= session()->getFlashdata('error') ?></p>
                    <?php endif; ?>
                    <?php if (isset($errors) && !empty($errors)): ?>
                        <ul>
                            <?php foreach ($errors as $error_item): ?>
                                <li><?= esc($error_item) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <?php
                        $errors_mahasiswa_update = session()->getFlashdata('errors_mahasiswa_update');
                        if (!empty($errors_mahasiswa_update)) {
                            echo '<p>Error Mahasiswa Model:</p><ul>';
                            foreach ($errors_mahasiswa_update as $err) {
                                echo '<li>' . esc($err) . '</li>';
                            }
                            echo '</ul>';
                        }

                        $errors_user_update = session()->getFlashdata('errors_user_update');
                        if (!empty($errors_user_update)) {
                            echo '<p>Error User Model:</p><ul>';
                            foreach ($errors_user_update as $err) {
                                echo '<li>' . esc($err) . '</li>';
                            }
                            echo '</ul>';
                        }
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($mahasiswa_profil) && !empty($mahasiswa_profil)): ?>
                <form action="<?= base_url('admin/mahasiswa/update/' . esc($mahasiswa_profil['nim'])) ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="PUT">
                    
                    <h5>Data Profil Mahasiswa</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nim" class="form-label">NIM</label>
                                <input type="text" class="form-control" id="nim" value="<?= esc($mahasiswa_profil['nim']) ?>" readonly disabled>
                                <small class="text-muted">NIM tidak dapat diubah.</small>
                            </div>
                            <div class="mb-3">
                                <label for="nama_mahasiswa" class="form-label">Nama Lengkap Mahasiswa <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= (isset($errors['nama_mahasiswa']) || isset(session()->getFlashdata('errors_mahasiswa_update')['nama'])) ? 'is-invalid' : '' ?>" 
                                       id="nama_mahasiswa" name="nama_mahasiswa" value="<?= old('nama_mahasiswa', $mahasiswa_profil['nama']) ?>" required>
                                <?php if (isset($errors['nama_mahasiswa'])): ?>
                                    <div class="invalid-feedback"><?= esc($errors['nama_mahasiswa']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label for="email_mahasiswa" class="form-label">Email Mahasiswa <span class="text-danger">*</span></label>
                                <input type="email" class="form-control <?= (isset($errors['email_mahasiswa']) || isset(session()->getFlashdata('errors_mahasiswa_update')['email'])) ? 'is-invalid' : '' ?>" 
                                       id="email_mahasiswa" name="email_mahasiswa" value="<?= old('email_mahasiswa', $mahasiswa_profil['email']) ?>" required>
                                <?php if (isset($errors['email_mahasiswa'])): ?>
                                    <div class="invalid-feedback"><?= esc($errors['email_mahasiswa']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5>Data Akun Login Mahasiswa</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <?php if (isset($user_akun) && !empty($user_akun)): ?>
                                <div class="mb-3">
                                    <label for="username_mahasiswa" class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?= isset($errors['username_mahasiswa']) ? 'is-invalid' : '' ?>" 
                                           id="username_mahasiswa" name="username_mahasiswa" value="<?= old('username_mahasiswa', $user_akun['username']) ?>" required>
                                    <?php if (isset($errors['username_mahasiswa'])): ?>
                                        <div class="invalid-feedback"><?= esc($errors['username_mahasiswa']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-3">
                                    <label for="password_mahasiswa" class="form-label">Password Baru</label>
                                    <input type="password" class="form-control <?= isset($errors['password_mahasiswa']) ? 'is-invalid' : '' ?>" 
                                           id="password_mahasiswa" name="password_mahasiswa">
                                    <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah password.</small>
                                    <?php if (isset($errors['password_mahasiswa'])): ?>
                                        <div class="invalid-feedback"><?= esc($errors['password_mahasiswa']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-3">
                                    <label for="password_confirm_mahasiswa" class="form-label">Konfirmasi Password Baru</label>
                                    <input type="password" class="form-control <?= isset($errors['password_confirm_mahasiswa']) ? 'is-invalid' : '' ?>" 
                                           id="password_confirm_mahasiswa" name="password_confirm_mahasiswa">
                                    <?php if (isset($errors['password_confirm_mahasiswa'])): ?>
                                        <div class="invalid-feedback"><?= esc($errors['password_confirm_mahasiswa']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-3">
                                    <label for="is_active" class="form-label">Status Akun</label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="is_active" id="status_active" value="1" <?= (old('is_active', $user_akun['is_active']) == 1) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="status_active">Aktif</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="is_active" id="status_inactive" value="0" <?= (old('is_active', $user_akun['is_active']) == 0) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="status_inactive">Tidak Aktif</label>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i> Tidak ditemukan akun login untuk mahasiswa ini. Hubungi administrator.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Update Data Mahasiswa</button>
                        <a href="<?= base_url('admin/mahasiswa/list') ?>" class="btn btn-light">Batal</a>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-danger">
                    <h4 class="alert-heading">Data Tidak Ditemukan!</h4>
                    <p>Mahasiswa yang Anda cari tidak ditemukan dalam database.</p>
                    <a href="<?= base_url('admin/mahasiswa/list') ?>" class="btn btn-primary">Kembali ke Daftar Mahasiswa</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?= $this->endSection() ?>