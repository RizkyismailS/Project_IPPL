
<!DOCTYPE html>
<html lang="id">

<?= view('layout/header_login'); ?>

<body>
    <div class="login-container">
        <!-- Header -->
        <div class="login-header">
            <h2>Project IPPL</h2>
            <p class="text-muted">Sistem Informasi Pembelajaran</p>
        </div>

        <!-- Content -->
        <div class="login-box">
            <h3 class="text-center mb-4">LOGIN</h3>
            
            <?php if (session()->getFlashdata('error')) : ?>
                <div class="alert alert-danger">
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>
            
            <?php if (session()->getFlashdata('success')) : ?>
                <div class="alert alert-success">
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>
            
            <form action="<?= base_url('login/process') ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="Enter your username/NIM/NIP" value="<?= old('username') ?>" required>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn btn-login">Log in</button>
            </form>
            
            <div class="login-footer mt-4">
                <p>Mahasiswa baru? <a href="<?= base_url('register/mahasiswa') ?>">Daftar disini</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>