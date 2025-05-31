<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Mahasiswa - Project IPPL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }
        .register-container {
            max-width: 600px;
            width: 100%;
            padding: 15px;
        }
        .register-box {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .btn-register {
            width: 100%;
            padding: 10px 0;
            background: #4e73df;
            border: none;
            color: white;
            font-weight: 600;
            border-radius: 4px;
        }
        .register-footer {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <!-- Header -->
        <div class="register-header">
            <h2>Project IPPL</h2>
            <p class="text-muted">Pendaftaran Akun Mahasiswa</p>
        </div>

        <!-- Content -->
        <div class="register-box">
            <h3 class="text-center mb-4">REGISTER</h3>
            
            <div id="alert-container"></div>
            
            <form id="registerForm" action="<?= base_url('register/mahasiswa/process') ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="nim" class="form-label">NIM</label>
                    <input type="text" class="form-control" id="nim" name="nim" 
                           placeholder="Masukkan NIM anda" value="<?= old('nim') ?>" required>
                    <div class="invalid-feedback" id="nim-error"></div>
                </div>
                
                <div class="mb-3">
                    <label for="nama_mahasiswa" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" id="nama_mahasiswa" name="nama_mahasiswa" 
                           placeholder="Masukkan nama lengkap anda" value="<?= old('nama_mahasiswa') ?>" required>
                    <div class="invalid-feedback" id="nama_mahasiswa-error"></div>
                </div>
                
                <div class="mb-3">
                    <label for="email_mahasiswa" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email_mahasiswa" name="email_mahasiswa" 
                           placeholder="Masukkan email anda" value="<?= old('email_mahasiswa') ?>" required>
                    <div class="invalid-feedback" id="email_mahasiswa-error"></div>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Minimal 8 karakter" required>
                    <div class="invalid-feedback" id="password-error"></div>
                </div>
                
                <div class="mb-4">
                    <label for="password_confirm" class="form-label">Konfirmasi Password</label>
                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" 
                           placeholder="Ulangi password" required>
                    <div class="invalid-feedback" id="password_confirm-error"></div>
                </div>
                
                <button type="submit" class="btn btn-register">Daftar</button>
            </form>
            
            <div class="register-footer mt-4">
                <p>Sudah punya akun? <a href="<?= base_url('login') ?>">Login disini</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Reset previous errors
                document.querySelectorAll('.is-invalid').forEach(el => {
                    el.classList.remove('is-invalid');
                });
                document.getElementById('alert-container').innerHTML = '';
                
                // Get form data
                const formData = new FormData(form);
                
                // Submit via AJAX
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'validation_error' || data.status === 'error') {
                        // Show validation errors
                        if (data.errors) {
                            for (const [field, message] of Object.entries(data.errors)) {
                                const input = document.getElementById(field);
                                if (input) {
                                    input.classList.add('is-invalid');
                                    document.getElementById(`${field}-error`).textContent = message;
                                }
                            }
                        } else {
                            // General error message
                            document.getElementById('alert-container').innerHTML = `
                                <div class="alert alert-danger">
                                    ${data.message || 'Terjadi kesalahan. Silakan coba lagi.'}
                                </div>
                            `;
                        }
                    } else if (data.status === 'success') {
                        // Show success message and redirect
                        document.getElementById('alert-container').innerHTML = `
                            <div class="alert alert-success">
                                ${data.message || 'Registrasi berhasil! Redirecting...'}
                            </div>
                        `;
                        
                        // Redirect to login after 2 seconds
                        setTimeout(() => {
                            window.location.href = '<?= base_url('login') ?>';
                        }, 2000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('alert-container').innerHTML = `
                        <div class="alert alert-danger">
                            Terjadi kesalahan saat mengirim data. Silakan coba lagi.
                        </div>
                    `;
                });
            });
        });
    </script>
</body>

</html>