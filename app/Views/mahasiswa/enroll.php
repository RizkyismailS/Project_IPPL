<?= $this->extend('layout/template') ?> {/* Make sure this matches your main layout file */}

<?= $this->section('content') ?>

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3><?= esc($title ?? 'Enroll in Class') ?></h3>
                <p class="text-subtitle text-muted">Enter the unique enrollment code to join a class.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= base_url('mahasiswa/dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Enroll</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h4 class="mb-4 text-center"><strong>Join a Class</strong></h4>

                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
                    <?php endif; ?>
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
                    <?php endif; ?>
                    <?php if (session()->getFlashdata('warning')): ?>
                        <div class="alert alert-warning"><?= session()->getFlashdata('warning') ?></div>
                    <?php endif; ?>

                    <div class="border rounded mb-4 p-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" style="width: 45px; height: 45px; font-size: 1.2rem;">
                                <?= esc(strtoupper(substr($nama_user ?? 'U', 0, 1))) ?>
                            </div>
                            <div>
                                <div class="text-muted small">Logged in as</div>
                                <div class="fw-bold"><?= esc($nama_user ?? 'Student') ?></div>
                                <small class="text-muted"><?= esc($email_user ?? 'student@example.com') ?></small>
                            </div>
                        </div>
                        <a href="<?= base_url('logout') ?>" class="btn btn-outline-primary btn-sm">Switch Account</a>
                    </div>

                    <form action="<?= base_url('mahasiswa/enroll/process') ?>" method="post">
                        <?= csrf_field() ?>
                        
                        <div class="border rounded mb-4 p-3">
                            <label for="kode_enrollment" class="fw-bold mb-1">Class Code</label>
                            <p class="text-muted small mb-2">Ask your lecturer for the class code, then enter it here.</p>
                            <input type="text" 
                                   class="form-control form-control-lg <?= (isset($errors['kode_enrollment'])) ? 'is-invalid' : '' ?>" 
                                   id="kode_enrollment" 
                                   name="kode_enrollment" 
                                   placeholder="Class code" 
                                   value="<?= old('kode_enrollment') ?>"
                                   required>
                            <?php if (isset($errors['kode_enrollment'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['kode_enrollment']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Join</button>
                        </div>
                    </form>

                    <div class="border rounded p-3 mt-4">
                        <div class="fw-bold mb-2 small text-uppercase">Instructions</div>
                        <ul class="mb-0 small text-muted ps-3">
                            <li>Use the account authorized by your institution.</li>
                            <li>Use a class code with 5-8 letters or numbers, with no spaces or symbols.</li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>