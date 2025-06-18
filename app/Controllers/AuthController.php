<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\MahasiswaModel; // Diperlukan untuk registrasi mahasiswa

class AuthController extends BaseController
{
    protected $userModel;
    protected $mahasiswaModel;
    protected $session;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->mahasiswaModel = new MahasiswaModel();
        $this->session = \Config\Services::session(); // Load session service
        helper(['form', 'url']); // Load form dan URL helper
    }

    public function login()
    {
        // Jika sudah login, redirect ke dashboard masing-masing
        if ($this->session->get('isLoggedIn')) {
            $role = $this->session->get('role');
            if ($role === 'admin')
                return redirect()->to(base_url('admin/dashboard'));
            if ($role === 'dosen')
                return redirect()->to(base_url('dosen/dashboard'));
            if ($role === 'mahasiswa')
                return redirect()->to(base_url('mahasiswa/dashboard'));
        }
        return view('auth/login'); // Asumsi ada view login di app/Views/auth/login.php
    }

    public function processLogin()
    {
        $rules = [
            'username' => 'required',
            'password' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $user = $this->userModel->getUserByUsername($username);
        if ($user) {
            log_message('critical', 'LOGIN_VERIFY: Username Input: ' . $username .
                ', Password Input: ' . $password . // Hati-hati log password plaintext, hanya untuk debug singkat
                ', Hashed PW dari DB: ' . $user['password']);

            if (password_verify($password, $user['password'])) {
                log_message('critical', 'LOGIN_VERIFY: password_verify() SUKSES');
                // ...
            } else {
                log_message('error', 'LOGIN_VERIFY: password_verify() GAGAL');
                // ...
            }
        } else {
            log_message('error', 'LOGIN_VERIFY: User tidak ditemukan: ' . $username);
            // ...
        }

        if ($user && password_verify($password, $user['password'])) {
            if (!$user['is_active']) {
                return redirect()->back()->withInput()->with('error', 'Akun Anda belum aktif atau telah dinonaktifkan.');
            }

            $userData = [
                'id_user' => $user['id_user'],
                'username' => $user['username'],
                'role' => $user['role'],
                'reference_id' => $user['reference_id'], // NIM atau NIP
                'isLoggedIn' => true,
            ];

            $activityLogModel = new \App\Models\ActivityLogModel();
            $activityLogModel->logActivity(
                $user['id_user'],
                $user['reference_id'],
                $user['role'],
                'login',
                'User logged in successfully',
                'users',
                $user['id_user']
            );

            // Ambil nama untuk ditampilkan (dari tabel mahasiswa/dosen)
            if ($user['role'] === 'mahasiswa' && $user['reference_id']) {
                $mahasiswa = $this->mahasiswaModel->find($user['reference_id']);
                $userData['nama_lengkap'] = $mahasiswa ? $mahasiswa['nama'] : $user['username'];
            } elseif ($user['role'] === 'dosen' && $user['reference_id']) {
                $dosen = new \App\Models\DosenModel(); // Instance baru jika belum ada di property
                $dosenData = $dosen->find($user['reference_id']);
                $userData['nama_lengkap'] = $dosenData ? $dosenData['nama'] : $user['username'];
            } else {
                $userData['nama_lengkap'] = $user['username']; // Untuk admin
            }
            log_message('info', 'AUTH_LOGIN: User data untuk sesi: ' . json_encode($userData));
            $this->session->set($userData);
            log_message('info', 'AUTH_LOGIN: Sesi diset untuk username: ' . $userData['username'] . ', role: ' . $userData['role'] . ', isLoggedIn: ' . ($userData['isLoggedIn'] ? 'true' : 'false') . ', All session data: ' . json_encode($this->session->get()));

            if ($user['role'] === 'admin') {
                log_message('info', 'AUTH_LOGIN: Redirecting admin ke admin/dashboard');
                return redirect()->to(base_url('admin/dashboard'))->with('success', 'Login berhasil!');
            } elseif ($user['role'] === 'dosen') {
                log_message('info', 'AUTH_LOGIN: Redirecting dosen ke dosen/dashboard');
                return redirect()->to(base_url('dosen/dashboard'))->with('success', 'Login berhasil!');
            } elseif ($user['role'] === 'mahasiswa') {
                log_message('info', 'AUTH_LOGIN: Redirecting mahasiswa ke mahasiswa/dashboard');
                return redirect()->to(base_url('mahasiswa/dashboard'))->with('success', 'Login berhasil!');
            }
        } else {
            return redirect()->back()->withInput()->with('error', 'Username atau password salah.');
        }
    }

    public function processRegisterMahasiswa()
    {
        // 1. Aturan Validasi Langsung di Controller
        // Validasi ini akan memeriksa input dari form sebelum data coba disimpan ke model.
        $validationRules = [
            'nim' => [
                'label' => 'NIM', // Nama field yang lebih ramah untuk pesan error
                'rules' => 'required|alpha_numeric|max_length[20]|is_unique[mahasiswa.nim]|is_unique[users.username]',
                'errors' => [
                    'required' => '{field} wajib diisi.',
                    'alpha_numeric' => '{field} hanya boleh berisi huruf dan angka.',
                    'max_length' => '{field} maksimal 20 karakter.',
                    'is_unique' => '{field} ini sudah terdaftar sebagai NIM atau Username. Silakan gunakan NIM lain.'
                ]
            ],
            'nama_mahasiswa' => [
                'label' => 'Nama Mahasiswa',
                'rules' => 'required|string|max_length[100]',
                'errors' => [
                    'required' => '{field} wajib diisi.',
                    'max_length' => '{field} maksimal 100 karakter.'
                ]
            ],
            'email_mahasiswa' => [
                'label' => 'Email Mahasiswa',
                'rules' => 'required|valid_email|max_length[100]|is_unique[mahasiswa.email]',
                'errors' => [
                    'required' => '{field} wajib diisi.',
                    'valid_email' => '{field} tidak valid.',
                    'max_length' => '{field} maksimal 100 karakter.',
                    'is_unique' => 'Email ini sudah terdaftar untuk mahasiswa lain.'
                ]
            ],
            'password' => [
                'label' => 'Password',
                'rules' => 'required|min_length[8]',
                'errors' => [
                    'required' => '{field} wajib diisi.',
                    'min_length' => '{field} minimal harus 8 karakter.'
                ]
            ],
            'password_confirm' => [
                'label' => 'Konfirmasi Password',
                'rules' => 'required|matches[password]',
                'errors' => [
                    'required' => '{field} wajib diisi.',
                    'matches' => '{field} tidak cocok dengan Password.'
                ]
            ],
        ];

        if (!$this->validate($validationRules)) {
            // Jika validasi dari controller gagal
            log_message('error', 'Validasi registrasi mahasiswa gagal: ' . json_encode($this->validator->getErrors()));
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'validation_error',
                'errors' => $this->validator->getErrors()
            ]);
        }

        // 2. Siapkan Data untuk Model
        // Data untuk tabel mahasiswa
        $mahasiswaData = [
            'nim' => $this->request->getVar('nim'), // Gunakan getVar untuk konsistensi
            'nama' => $this->request->getVar('nama_mahasiswa'),
            'email' => $this->request->getVar('email_mahasiswa'),
            // 'foto_wajah' => (handle file upload jika ada)
        ];

        // Data untuk tabel users
        // UserModel akan menghash password melalui callback $beforeInsert
        $userData = [
            'username' => $this->request->getVar('nim'), // Menggunakan NIM sebagai username
            'password' => $this->request->getVar('password'),
            'role' => 'mahasiswa',
            'reference_id' => $this->request->getVar('nim'),
            'is_active' => 1, // Asumsi langsung aktif. Bisa diubah jika ada alur verifikasi email.
        ];

        // 3. Gunakan Transaksi Database
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Insert ke tabel mahasiswa
            // Model akan menjalankan validasi internalnya (termasuk is_unique)
            if (!$this->mahasiswaModel->insert($mahasiswaData)) {
                // Jika insert mahasiswa gagal (kemungkinan karena validasi model mahasiswa)
                $db->transRollback();
                log_message('error', 'Gagal insert ke mahasiswaModel. Errors: ' . json_encode($this->mahasiswaModel->errors()));
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => 'Registrasi data mahasiswa gagal.',
                    'errors' => $this->mahasiswaModel->errors()
                ]);
            }
            $mahasiswaInsertID = $this->mahasiswaModel->getInsertID(); // Meskipun PK bukan auto-increment, ini bisa berguna atau diabaikan jika PK-nya nim

            // Insert ke tabel users
            // Model akan menjalankan validasi internalnya dan callback hashPassword
            if (!$this->userModel->insert($userData)) {
                // Jika insert user gagal (kemungkinan karena validasi model user)
                $db->transRollback();
                log_message('error', 'Gagal insert ke userModel. Errors: ' . json_encode($this->userModel->errors()));
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => 'Registrasi akun pengguna gagal.',
                    'errors' => $this->userModel->errors()
                ]);
            }
            $userInsertID = $this->userModel->getInsertID();

            // Jika semua berhasil
            if ($db->transStatus() === false) {
                // Ini untuk menangkap jika ada masalah database lain di dalam transaksi
                $db->transRollback();
                log_message('error', 'Status transaksi database gagal setelah mencoba insert.');
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan internal saat menyimpan data.'
                ]);
            }

            $db->transCommit();
            log_message('info', 'Registrasi mahasiswa berhasil. NIM: ' . $mahasiswaData['nim'] . ', UserID: ' . $userInsertID);
            return $this->response->setStatusCode(201)->setJSON([ // 201 Created
                'status' => 'success',
                'message' => 'Registrasi berhasil! Silakan login.',
                'data' => [
                    'nim' => $mahasiswaData['nim'],
                    'user_id' => $userInsertID,
                    'nama' => $mahasiswaData['nama'],
                    'email' => $mahasiswaData['email']
                ]
            ]);

        } catch (DatabaseException $e) {
            $db->transRollback();
            log_message('error', 'DatabaseException saat registrasi: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan database saat registrasi.',
                'detail' => $e->getMessage() // Jangan tampilkan ini di produksi
            ]);
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Exception umum saat registrasi: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan tidak terduga saat registrasi.',
                'detail' => $e->getMessage() // Jangan tampilkan ini di produksi
            ]);
        }
    }

    public function registerMahasiswa()
    {
        if ($this->session->get('isLoggedIn')) {
            // Redirect ke dashboard yang sesuai jika sudah login
            $role = $this->session->get('role');
            if ($role === 'admin')
                return redirect()->to(base_url('admin/dashboard'));
            if ($role === 'dosen')
                return redirect()->to(base_url('dosen/dashboard'));
            if ($role === 'mahasiswa')
                return redirect()->to(base_url('mahasiswa/dashboard'));
            return redirect()->to(base_url('/')); // Fallback jika peran tidak dikenal
        }

        $data['title'] = 'Registrasi Akun Mahasiswa';
        // Ini untuk menangani error jika ada redirect dari processRegisterMahasiswa (submit form non-AJAX)
        $data['errors'] = session()->getFlashdata('errors');
        $data['error_umum'] = session()->getFlashdata('error_umum'); // Menggunakan 'error_umum'

        return view('auth/register', $data);
    }

    public function logout()
    {
        $activityLogModel = new \App\Models\ActivityLogModel();
        $activityLogModel->logActivity(
            session()->get('id_user'),
            session()->get('reference_id'),
            session()->get('role'),
            'logout',
            'User logged out',
            'users',
            session()->get('id_user')
        );
        $this->session->destroy();
        return redirect()->to(base_url('/'))->with('success', 'Anda telah berhasil logout.');
    }
}