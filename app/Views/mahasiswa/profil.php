<?php
?>
<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Profil Mahasiswa</h3>
                <p class="text-subtitle text-muted">Kelola informasi profil dan password Anda.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= base_url('mahasiswa/dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Profil</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<section class="section">
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Profil Mahasiswa -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Informasi Profil</h4>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('mahasiswa/profile/update') ?>" method="post">
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label for="nim" class="form-label">NIM</label>
                            <input type="text" class="form-control" id="nim" name="nim" value="<?= esc($mahasiswa['nim']) ?>" readonly>
                            <small class="text-muted">NIM tidak dapat diubah</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control <?= (isset($validation) && $validation->hasError('nama')) ? 'is-invalid' : '' ?>" 
                                   id="nama" name="nama" value="<?= old('nama', $mahasiswa['nama']) ?>" required>
                            <?php if (isset($validation) && $validation->hasError('nama')) : ?>
                                <div class="invalid-feedback"><?= $validation->getError('nama') ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control <?= (isset($validation) && $validation->hasError('email')) ? 'is-invalid' : '' ?>" 
                                   id="email" name="email" value="<?= old('email', $mahasiswa['email']) ?>" required>
                            <?php if (isset($validation) && $validation->hasError('email')) : ?>
                                <div class="invalid-feedback"><?= $validation->getError('email') ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Update Profil</button>
                    </form>
                </div>
            </div>

            <?php if(isset($mahasiswa['foto_wajah']) && !empty($mahasiswa['foto_wajah'])): ?>
                <div class="card mt-3">
                    <div class="card-header">
                        <h4 class="card-title">Foto Profil</h4>
                    </div>
                    <div class="card-body text-center">
                        <img src="<?= base_url('uploads/foto_wajah/' . $mahasiswa['foto_wajah']) ?>" alt="Foto Profil" class="img-thumbnail" style="max-width: 200px;">
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Akun Login -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Informasi Akun</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($user) && $user) : ?>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" value="<?= esc($user['username']) ?>" readonly>
                            <small class="text-muted">Username tidak dapat diubah</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status Akun</label>
                            <?php if ($user['is_active'] == 1) : ?>
                                <p><span class="badge bg-success">Aktif</span></p>
                            <?php else : ?>
                                <p><span class="badge bg-danger">Tidak Aktif</span></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Terakhir Login</label>
                            <p><?= isset($last_login) ? date('d M Y H:i', strtotime($last_login)) : 'Belum pernah login' ?></p>
                        </div>
                        
                        <hr>
                        <h5>Ubah Password</h5>
                        <form action="<?= base_url('mahasiswa/profile/change-password') ?>" method="post">
                            <?= csrf_field() ?>
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Password Saat Ini</label>
                                <input type="password" class="form-control <?= (isset($validation) && $validation->hasError('current_password')) ? 'is-invalid' : '' ?>" 
                                       id="current_password" name="current_password" required>
                                <?php if (isset($validation) && $validation->hasError('current_password')) : ?>
                                    <div class="invalid-feedback"><?= $validation->getError('current_password') ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Password Baru</label>
                                <input type="password" class="form-control <?= (isset($validation) && $validation->hasError('new_password')) ? 'is-invalid' : '' ?>" 
                                       id="new_password" name="new_password" required>
                                <small class="text-muted">Minimal 8 karakter</small>
                                <?php if (isset($validation) && $validation->hasError('new_password')) : ?>
                                    <div class="invalid-feedback"><?= $validation->getError('new_password') ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control <?= (isset($validation) && $validation->hasError('confirm_password')) ? 'is-invalid' : '' ?>" 
                                       id="confirm_password" name="confirm_password" required>
                                <?php if (isset($validation) && $validation->hasError('confirm_password')) : ?>
                                    <div class="invalid-feedback"><?= $validation->getError('confirm_password') ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <button type="submit" class="btn btn-warning">Ubah Password</button>
                        </form>
                    <?php else : ?>
                        <div class="alert alert-warning mb-0">
                            <h4 class="alert-heading">Akun Login Tidak Ditemukan</h4>
                            <p>Akun login Anda tidak ditemukan. Harap hubungi administrator untuk informasi lebih lanjut.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>