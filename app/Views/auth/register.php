<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Register Mahasiswa - Project IPPL') ?></title>
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
            /* Warna bisa disesuaikan dengan tema Mazer Anda jika ada */
            /* background: #4e73df; */ 
            /* border: none; */
            /* color: white; */
            /* font-weight: 600; */
            /* border-radius: 4px; */
        }
        .register-footer {
            text-align: center;
            margin-top: 20px;
        }
        /* Menambahkan style untuk pesan error validasi CI4 jika menggunakan redirect */
        .alert ul {
            margin-bottom: 0;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <div class="register-header">
            <h2>Project IPPL</h2>
            <p class="text-muted">Pendaftaran Akun Mahasiswa</p>
        </div>

        <div class="register-box">
            <h3 class="text-center mb-4">REGISTER</h3>
            
            <div id="alert-container">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error_umum')): // Diganti dari 'error' agar tidak bentrok dgn $errors array ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= session()->getFlashdata('error_umum') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php 
                // Menampilkan error validasi dari redirect (jika tidak pakai AJAX atau AJAX gagal)
                $validation_errors = session()->getFlashdata('errors') ?? (isset($errors) ? $errors : []);
                if (!empty($validation_errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h4 class="alert-heading">Oops! Ada yang salah:</h4>
                        <ul>
                        <?php foreach ($validation_errors as $field => $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
            </div>
            
            <form id="registerForm" action="<?= base_url('register/mahasiswa/process') ?>" method="post">
                 <?= csrf_field() ?> <!-- {/* PENTING untuk keamanan CI4 */} -->

                <div class="mb-3">
                    <label for="nim" class="form-label">NIM <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?= isset($validation_errors['nim']) ? 'is-invalid' : '' ?>" 
                           id="nim" name="nim" placeholder="Masukkan NIM Anda" 
                           value="<?= old('nim') ?>" required>
                    <div class="invalid-feedback" id="nim-error">
                        <?= isset($validation_errors['nim']) ? esc($validation_errors['nim']) : '' ?>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="nama_mahasiswa" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?= isset($validation_errors['nama_mahasiswa']) ? 'is-invalid' : '' ?>" 
                           id="nama_mahasiswa" name="nama_mahasiswa" placeholder="Masukkan nama lengkap Anda" 
                           value="<?= old('nama_mahasiswa') ?>" required>
                    <div class="invalid-feedback" id="nama_mahasiswa-error">
                         <?= isset($validation_errors['nama_mahasiswa']) ? esc($validation_errors['nama_mahasiswa']) : '' ?>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="email_mahasiswa" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control <?= isset($validation_errors['email_mahasiswa']) ? 'is-invalid' : '' ?>" 
                           id="email_mahasiswa" name="email_mahasiswa" placeholder="Masukkan email Anda" 
                           value="<?= old('email_mahasiswa') ?>" required>
                    <div class="invalid-feedback" id="email_mahasiswa-error">
                         <?= isset($validation_errors['email_mahasiswa']) ? esc($validation_errors['email_mahasiswa']) : '' ?>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control <?= isset($validation_errors['password']) ? 'is-invalid' : '' ?>" 
                           id="password" name="password" placeholder="Minimal 8 karakter" required>
                    <div class="invalid-feedback" id="password-error">
                         <?= isset($validation_errors['password']) ? esc($validation_errors['password']) : '' ?>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password_confirm" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control <?= isset($validation_errors['password_confirm']) ? 'is-invalid' : '' ?>" 
                           id="password_confirm" name="password_confirm" placeholder="Ulangi password" required>
                    <div class="invalid-feedback" id="password_confirm-error">
                         <?= isset($validation_errors['password_confirm']) ? esc($validation_errors['password_confirm']) : '' ?>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-register">Daftar</button>
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
            const alertContainer = document.getElementById('alert-container');
            
            form.addEventListener('submit', function(e) {
                e.preventDefault(); // Hentikan submit form HTML standar
                
                // Reset pesan error sebelumnya
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
                alertContainer.innerHTML = '';
                
                const formData = new FormData(form);
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest', // Tandai sebagai request AJAX
                        'Accept': 'application/json', // Minta response JSON
                        // CSRF token akan otomatis diambil dari field form oleh FormData jika namanya benar
                    }
                })
                .then(response => {
                    // Cek apakah response adalah JSON sebelum mencoba mem-parse
                    const contentType = response.headers.get("content-type");
                    if (contentType && contentType.indexOf("application/json") !== -1) {
                        return response.json().then(data => ({ status: response.status, body: data }));
                    } else {
                        // Jika bukan JSON, mungkin ada error HTML dari server atau redirect
                        return response.text().then(text => ({ status: response.status, body: text, isText: true }));
                    }
                })
                .then(result => {
                    const data = result.body;
                    const statusCode = result.status;

                    if (result.isText) { // Jika response bukan JSON (misal error HTML)
                        console.error('Server did not return JSON:', data);
                        alertContainer.innerHTML = `<div class="alert alert-danger">Terjadi kesalahan server. Coba lagi nanti. Response: ${data.substring(0,200)}</div>`;
                        return;
                    }

                    if (data.status === 'validation_error' || (statusCode === 400 && data.errors)) {
                        if (data.errors) {
                            for (const [field, message] of Object.entries(data.errors)) {
                                const inputElement = document.getElementById(field);
                                const errorElement = document.getElementById(`${field}-error`);
                                if (inputElement && errorElement) {
                                    inputElement.classList.add('is-invalid');
                                    errorElement.textContent = message;
                                } else {
                                    // Jika field tidak ditemukan, tampilkan sebagai error umum
                                    appendGeneralError(message);
                                }
                            }
                        } else if (data.message) {
                             appendGeneralError(data.message);
                        }
                    } else if (data.status === 'error' || statusCode >= 400) {
                         appendGeneralError(data.message || 'Registrasi gagal karena kesalahan server.');
                    } else if (data.status === 'success' && (statusCode === 200 || statusCode === 201)) {
                        alertContainer.innerHTML = `<div class="alert alert-success">${data.message || 'Registrasi berhasil! Mengarahkan ke halaman login...'}</div>`;
                        setTimeout(() => {
                            window.location.href = '<?= base_url('/') ?>';
                        }, 2000);
                    } else {
                        // Fallback jika struktur data tidak sesuai harapan
                        appendGeneralError('Response tidak dikenal dari server.');
                        console.log('Unknown server response:', data);
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    appendGeneralError('Tidak dapat terhubung ke server. Periksa koneksi Anda.');
                });
            });

            function appendGeneralError(message) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger';
                errorDiv.textContent = message;
                alertContainer.appendChild(errorDiv);
            }
        });
    </script>
</body>
</html>