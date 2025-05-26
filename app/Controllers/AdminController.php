<?php

namespace App\Controllers;

use App\Models\DosenModel;
use App\Models\UserModel;
use CodeIgniter\Database\Exceptions\DatabaseException; // Tambahkan ini

class AdminController extends BaseController
{
    protected $dosenModel;
    protected $userModel;
    protected $session;

    public function __construct()
    {
        $this->dosenModel = new DosenModel();
        $this->userModel = new UserModel();
        $this->session = \Config\Services::session();
        helper(['form', 'url']);

        // PENTING: Implementasikan Filter untuk otorisasi admin!
        // Contoh: cek di BaseController atau gunakan fitur Filter CI4
        // if ($this->session->get('role') !== 'admin' &&ENVIRONMENT !== 'testing') {
        //     // Jika bukan admin dan bukan dalam mode testing, maka tolak akses
        //     // Ini hanya contoh sederhana, Filter CI4 lebih baik
        //     // throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(); 
        // }
    }

    public function dashboard()
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
            // Untuk Postman, bisa kembalikan 403 jika tidak pakai redirect
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak. Hanya admin.']);
        }
        $data['title'] = 'Admin Dashboard';
        $data['nama_user'] = $this->session->get('nama_lengkap') ?? $this->session->get('username');
        return $this->response->setJSON($data);
    }

    public function createUserDosenForm() // Mengganti nama agar lebih jelas ini form
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
             return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }
        // Ini hanya untuk endpoint GET jika ada form HTML. Untuk Postman, storeUserDosen lebih relevan.
        return $this->response->setJSON(['status' => 'info', 'message' => 'Endpoint untuk menampilkan form pembuatan akun dosen (via GET).']);
    }

    public function storeUserDosen()
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak. Hanya admin.']);
        }

        // Aturan validasi di Controller
        $validationRules = [
            'nip'            => [
                'label' => 'NIP',
                'rules' => 'required|alpha_numeric|max_length[20]|is_unique[dosen.nip]', // Model Dosen akan validasi is_unique juga
                'errors' => [
                    'is_unique' => '{field} ini sudah terdaftar.'
                ]
            ],
            'nama_dosen'     => ['label' => 'Nama Dosen', 'rules' => 'required|string|max_length[100]'],
            'email_dosen'    => [
                'label' => 'Email Dosen',
                'rules' => 'required|valid_email|max_length[100]|is_unique[dosen.email]|is_unique[users.username]', // Cek unik di dosen.email dan users.username
                'errors' => [
                    'is_unique' => '{field} ini sudah digunakan oleh dosen lain atau sebagai username pengguna.'
                ]
            ],
            'jabatan_dosen'  => ['label' => 'Jabatan Dosen', 'rules' => 'permit_empty|string|max_length[50]'],
            'username_dosen' => [
                'label' => 'Username Dosen',
                'rules' => 'required|alpha_numeric_space|min_length[3]|max_length[50]|is_unique[users.username]',
                'errors' => [
                    'is_unique' => '{field} ini sudah digunakan.'
                ]
            ],
            'password_dosen' => ['label' => 'Password Dosen', 'rules' => 'required|min_length[8]'],
        ];
        
        if (!$this->validate($validationRules)) {
            log_message('error', 'Validasi pembuatan akun dosen gagal: ' . json_encode($this->validator->getErrors()));
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'validation_error',
                'errors' => $this->validator->getErrors()
            ]);
        }
        
        $dosenData = [
            'nip'     => $this->request->getVar('nip'),
            'nama'    => $this->request->getVar('nama_dosen'),
            'email'   => $this->request->getVar('email_dosen'),
            'jabatan' => $this->request->getVar('jabatan_dosen'),
        ];

        // UserModel akan hash password via callback $beforeInsert
        $userData = [
            'username'     => $this->request->getVar('username_dosen'),
            'password'     => $this->request->getVar('password_dosen'),
            'role'         => 'dosen',
            'reference_id' => $this->request->getVar('nip'), // Hubungkan ke NIP dosen
            'is_active'    => 1, // Akun dosen yang dibuat admin langsung aktif
        ];

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            if (!$this->dosenModel->insert($dosenData)) {
                $db->transRollback();
                log_message('error', 'Gagal insert ke dosenModel. Errors: ' . json_encode($this->dosenModel->errors()));
                return $this->response->setStatusCode(400)->setJSON([ // Bisa 400 jika karena validasi model
                    'status' => 'error',
                    'message' => 'Penyimpanan data profil dosen gagal.',
                    'errors' => $this->dosenModel->errors()
                ]);
            }
            // $dosenInsertID = $this->dosenModel->getInsertID(); // nip adalah PK, bukan auto-increment

            if (!$this->userModel->insert($userData)) {
                $db->transRollback();
                log_message('error', 'Gagal insert ke userModel. Errors: ' . json_encode($this->userModel->errors()));
                return $this->response->setStatusCode(400)->setJSON([ // Bisa 400 jika karena validasi model
                    'status' => 'error',
                    'message' => 'Penyimpanan data login dosen gagal.',
                    'errors' => $this->userModel->errors()
                ]);
            }
            $userInsertID = $this->userModel->getInsertID();

            if ($db->transStatus() === false) {
                $db->transRollback();
                log_message('error', 'Status transaksi database gagal setelah mencoba insert dosen.');
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan internal saat menyimpan data dosen.'
                ]);
            }

            $db->transCommit();
            log_message('info', 'Akun dosen berhasil dibuat. NIP: ' . $dosenData['nip'] . ', UserID: ' . $userInsertID);
            return $this->response->setStatusCode(201)->setJSON([
                'status' => 'success',
                'message' => 'Akun dosen berhasil dibuat.',
                'data' => [
                    'nip' => $dosenData['nip'],
                    'user_id' => $userInsertID
                ]
            ]);

        } catch (DatabaseException $e) {
            $db->transRollback();
            log_message('error', 'DatabaseException saat pembuatan akun dosen: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan database saat pembuatan akun dosen.',
                'detail' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Internal Server Error'
            ]);
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Exception umum saat pembuatan akun dosen: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan tidak terduga saat pembuatan akun dosen.',
                'detail' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Internal Server Error'
            ]);
        }
    }

    public function listDosen() {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
             return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }
        $data['dosen'] = $this->dosenModel->findAll();
        return $this->response->setJSON(['status' => 'success', 'data' => $data['dosen']]);
    }
}