<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Edit Data Dosen</h3>
                <p class="text-subtitle text-muted">Formulir untuk mengubah data dosen: <?= esc($dosen['nama_dosen'] ?? ($dosen_profil['nama'] ?? '')) ?></p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= base_url('/admin/dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('/admin/dosen') ?>">Manage Dosen</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Dosen</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<section class="section">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Form Edit Data Dosen</h4>
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
                        $errors_dosen_update = session()->getFlashdata('errors_dosen_update');
                        if (!empty($errors_dosen_update)) {
                            echo '<p>Error Profil Dosen:</p><ul>';
                            foreach ($errors_dosen_update as $err) {
                                echo '<li>' . esc($err) . '</li>';
                            }
                            echo '</ul>';
                        }

                        $errors_user_update = session()->getFlashdata('errors_user_update');
                        if (!empty($errors_user_update)) {
                            echo '<p>Error Akun Login:</p><ul>';
                            foreach ($errors_user_update as $err) {
                                echo '<li>' . esc($err) . '</li>';
                            }
                            echo '</ul>';
                        }
                    ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('admin/dosen/update/' . esc($dosen_profil['nip'] ?? '', 'attr')) ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="PUT"> 
                
                <h5>Data Profil Dosen</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nip" class="form-label">NIP <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" 
                                   id="nip" name="nip" value="<?= old('nip', esc($dosen_profil['nip'] ?? '')) ?>" readonly>
                            <small class="form-text text-muted">NIP tidak dapat diubah.</small>
                        </div>
                        <div class="mb-3">
                            <label for="nama_dosen" class="form-label">Nama Lengkap Dosen <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= (isset($errors['nama_dosen']) || isset(session()->getFlashdata('errors_dosen_update')['nama'])) ? 'is-invalid' : '' ?>" 
                                   id="nama_dosen" name="nama_dosen" value="<?= old('nama_dosen', esc($dosen_profil['nama'] ?? '')) ?>" required>
                            <?php if (isset($errors['nama_dosen'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['nama_dosen']) ?></div>
                            <?php endif; ?>
                            <?php if (isset(session()->getFlashdata('errors_dosen_update')['nama'])): ?>
                                <div class="invalid-feedback d-block"><?= esc(session()->getFlashdata('errors_dosen_update')['nama']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="email_dosen" class="form-label">Email Dosen <span class="text-danger">*</span></label>
                            <input type="email" class="form-control <?= (isset($errors['email_dosen']) || isset(session()->getFlashdata('errors_dosen_update')['email'])) ? 'is-invalid' : '' ?>" 
                                   id="email_dosen" name="email_dosen" value="<?= old('email_dosen', esc($dosen_profil['email'] ?? '')) ?>" required>
                            <?php if (isset($errors['email_dosen'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['email_dosen']) ?></div>
                            <?php endif; ?>
                            <?php if (isset(session()->getFlashdata('errors_dosen_update')['email'])): ?>
                                <div class="invalid-feedback d-block"><?= esc(session()->getFlashdata('errors_dosen_update')['email']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="jabatan_dosen" class="form-label">Jabatan</label>
                            <input type="text" class="form-control <?= isset($errors['jabatan_dosen']) ? 'is-invalid' : '' ?>" 
                                   id="jabatan_dosen" name="jabatan_dosen" value="<?= old('jabatan_dosen', esc($dosen_profil['jabatan'] ?? '')) ?>">
                            <?php if (isset($errors['jabatan_dosen'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['jabatan_dosen']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <hr>
                <h5>Data Akun Login Dosen</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="username_dosen" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= (isset($errors['username_dosen']) || isset(session()->getFlashdata('errors_user_update')['username'])) ? 'is-invalid' : '' ?>" 
                                   id="username_dosen" name="username_dosen" value="<?= old('username_dosen', esc($user_akun['username'] ?? '')) ?>" required>
                            <?php if (isset($errors['username_dosen'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['username_dosen']) ?></div>
                            <?php endif; ?>
                            <?php if (isset(session()->getFlashdata('errors_user_update')['username'])): ?>
                                <div class="invalid-feedback d-block"><?= esc(session()->getFlashdata('errors_user_update')['username']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="password_dosen" class="form-label">Password Baru</label>
                            <input type="password" class="form-control <?= (isset($errors['password_dosen']) || isset(session()->getFlashdata('errors_user_update')['password'])) ? 'is-invalid' : '' ?>" 
                                   id="password_dosen" name="password_dosen">
                            <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password. Minimal 8 karakter jika diisi.</small>
                            <?php if (isset($errors['password_dosen'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['password_dosen']) ?></div>
                            <?php endif; ?>
                            <?php if (isset(session()->getFlashdata('errors_user_update')['password'])): ?>
                                <div class="invalid-feedback d-block"><?= esc(session()->getFlashdata('errors_user_update')['password']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="password_confirm_dosen" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control <?= isset($errors['password_confirm_dosen']) ? 'is-invalid' : '' ?>" 
                                   id="password_confirm_dosen" name="password_confirm_dosen">
                             <?php if (isset($errors['password_confirm_dosen'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['password_confirm_dosen']) ?></div>
                            <?php endif; ?>
                        </div>
                         <div class="mb-3">
                            <label for="is_active" class="form-label">Status Akun</label>
                            <select class="form-select <?= (isset($errors['is_active']) || isset(session()->getFlashdata('errors_user_update')['is_active'])) ? 'is-invalid' : '' ?>" id="is_active" name="is_active">
                                <option value="1" <?= (old('is_active', $user_akun['is_active'] ?? '1') == '1') ? 'selected' : '' ?>>Aktif</option>
                                <option value="0" <?= (old('is_active', $user_akun['is_active'] ?? '1') == '0') ? 'selected' : '' ?>>Tidak Aktif</option>
                            </select>
                            <?php if (isset($errors['is_active'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['is_active']) ?></div>
                            <?php endif; ?>
                             <?php if (isset(session()->getFlashdata('errors_user_update')['is_active'])): ?>
                                <div class="invalid-feedback d-block"><?= esc(session()->getFlashdata('errors_user_update')['is_active']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update Data Dosen</button>
                    <a href="<?= base_url('admin/dosen/list') ?>" class="btn btn-light">Batal</a>
                </div>
            </form>
        </div>
    </div>
</section>
<?= $this->endSection() ?>