<?php
?>
<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Tambah Mahasiswa Baru</h3>
                <p class="text-subtitle text-muted">Formulir untuk menambahkan data mahasiswa baru beserta akun loginnya.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= base_url('/admin/dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('/admin/mahasiswa/list') ?>">Manage Mahasiswa</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Tambah Mahasiswa</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<section class="section">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Form Data Mahasiswa</h4>
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
                        $errors_mahasiswa = session()->getFlashdata('errors_mahasiswa');
                        if (!empty($errors_mahasiswa)) {
                            echo '<p>Error Mahasiswa Model:</p><ul>';
                            foreach ($errors_mahasiswa as $err) {
                                echo '<li>' . esc($err) . '</li>';
                            }
                            echo '</ul>';
                        }

                        $errors_user = session()->getFlashdata('errors_user');
                        if (!empty($errors_user)) {
                            echo '<p>Error User Model:</p><ul>';
                            foreach ($errors_user as $err) {
                                echo '<li>' . esc($err) . '</li>';
                            }
                            echo '</ul>';
                        }
                    ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('admin/mahasiswa/store') ?>" method="post">
                <?= csrf_field() ?>
                
                <h5>Data Profil Mahasiswa</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nim" class="form-label">NIM <span class="text-danger">*</span></label>
                            <small class="form-text text-muted">Ini akan digunakan untuk login.</small>
                            <input type="text" class="form-control <?= (isset($errors['nim']) || isset(session()->getFlashdata('errors_mahasiswa')['nim'])) ? 'is-invalid' : '' ?>" 
                                   id="nim" name="nim" value="<?= old('nim') ?>" required>
                            <?php if (isset($errors['nim'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['nim']) ?></div>
                            <?php endif; ?>
                            <?php if (isset(session()->getFlashdata('errors_mahasiswa')['nim'])): ?>
                                <div class="invalid-feedback d-block"><?= esc(session()->getFlashdata('errors_mahasiswa')['nim']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="nama_mahasiswa" class="form-label">Nama Lengkap Mahasiswa <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= (isset($errors['nama_mahasiswa']) || isset(session()->getFlashdata('errors_mahasiswa')['nama'])) ? 'is-invalid' : '' ?>" 
                                   id="nama_mahasiswa" name="nama_mahasiswa" value="<?= old('nama_mahasiswa') ?>" required>
                            <?php if (isset($errors['nama_mahasiswa'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['nama_mahasiswa']) ?></div>
                            <?php endif; ?>
                             <?php if (isset(session()->getFlashdata('errors_mahasiswa')['nama'])): ?>
                                <div class="invalid-feedback d-block"><?= esc(session()->getFlashdata('errors_mahasiswa')['nama']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="email_mahasiswa" class="form-label">Email Mahasiswa <span class="text-danger">*</span></label>
                            <input type="email" class="form-control <?= (isset($errors['email_mahasiswa']) || isset(session()->getFlashdata('errors_mahasiswa')['email'])) ? 'is-invalid' : '' ?>" 
                                   id="email_mahasiswa" name="email_mahasiswa" value="<?= old('email_mahasiswa') ?>" required>
                            <?php if (isset($errors['email_mahasiswa'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['email_mahasiswa']) ?></div>
                            <?php endif; ?>
                            <?php if (isset(session()->getFlashdata('errors_mahasiswa')['email'])): ?>
                                <div class="invalid-feedback d-block"><?= esc(session()->getFlashdata('errors_mahasiswa')['email']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <hr>
                <h5>Data Akun Login Mahasiswa</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="username_mahasiswa" class="form-label">Username <span class="text-danger">*</span></label>
                            
                            <input type="text" class="form-control <?= (isset($errors['username_mahasiswa']) || isset(session()->getFlashdata('errors_user')['username'])) ? 'is-invalid' : '' ?>" 
                                   id="username_mahasiswa" name="username_mahasiswa" value="<?= old('username_mahasiswa') ?>" required>
                            <?php if (isset($errors['username_mahasiswa'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['username_mahasiswa']) ?></div>
                            <?php endif; ?>
                            <?php if (isset(session()->getFlashdata('errors_user')['username'])): ?>
                                <div class="invalid-feedback d-block"><?= esc(session()->getFlashdata('errors_user')['username']) ?></div>
                            <?php endif; ?>

                        </div>
                        
                        <div class="mb-3">
                            <label for="password_mahasiswa" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control <?= (isset($errors['password_mahasiswa']) || isset(session()->getFlashdata('errors_user')['password'])) ? 'is-invalid' : '' ?>" 
                                   id="password_mahasiswa" name="password_mahasiswa" required>
                            <small class="form-text text-muted">Minimal 8 karakter.</small>
                            <?php if (isset($errors['password_mahasiswa'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['password_mahasiswa']) ?></div>
                            <?php endif; ?>
                             <?php if (isset(session()->getFlashdata('errors_user')['password'])): ?>
                                <div class="invalid-feedback d-block"><?= esc(session()->getFlashdata('errors_user')['password']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="password_confirm_mahasiswa" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control <?= isset($errors['password_confirm_mahasiswa']) ? 'is-invalid' : '' ?>" 
                                   id="password_confirm_mahasiswa" name="password_confirm_mahasiswa" required>
                            <?php if (isset($errors['password_confirm_mahasiswa'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['password_confirm_mahasiswa']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Simpan Data Mahasiswa</button>
                    <a href="<?= base_url('admin/mahasiswa/list') ?>" class="btn btn-light">Batal</a>
                </div>
            </form>
        </div>
    </div>
</section>

<?= $this->endSection() ?>