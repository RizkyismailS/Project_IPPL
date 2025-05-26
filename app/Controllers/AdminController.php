<?php

namespace App\Controllers;

use App\Models\DosenModel;
use App\Models\UserModel;

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

        // Filter untuk memastikan hanya admin yang bisa akses
        // Ini sebaiknya dihandle di Routes atau menggunakan Filter CI4
        // Contoh sederhana pengecekan di constructor:
        // if ($this->session->get('role') !== 'admin') {
        //     // Redirect atau tampilkan error
        //     // Note: ini tidak ideal di constructor, lebih baik via Filter
        //     echo 'Akses ditolak!';
        //     exit;
        // }
    }

    public function dashboard()
    {
        // Logika untuk menampilkan dashboard admin
        // Cek sesi admin di sini atau gunakan Filter
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
            return redirect()->to(base_url('login'));
        }
        $data['title'] = 'Admin Dashboard';
        $data['nama_user'] = $this->session->get('nama_lengkap'); // Ambil dari sesi jika ada
        // return view('admin/dashboard', $data); // Asumsi ada view admin/dashboard.php
        // Karena belum ada view, kita bisa return JSON untuk tes
        return $this->response->setJSON($data);
    }

    public function createUserDosen()
    {
        // Metode untuk menampilkan form pembuatan akun dosen oleh admin
        // Cek sesi admin
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
            return redirect()->to(base_url('login'));
        }
        // return view('admin/create_dosen'); // View form
        // Untuk tes backend:
        return $this->response->setJSON(['message' => 'Halaman form buat akun dosen (belum ada frontend)']);
    }

    public function storeUserDosen()
    {
        // Cek sesi admin
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Akses ditolak']);
        }

        // Validasi untuk input data dosen dan user
        // Aturan validasi sudah ada di masing-masing model
        // DosenModel: nip, nama, email, jabatan
        // UserModel: username, password, role, reference_id

        $validation = \Config\Services::validation();
        // Sesuaikan rules dengan field form yang akan dibuat
        $validation->setRules([
            'nip'            => 'required|alpha_numeric|max_length[20]|is_unique[dosen.nip]',
            'nama_dosen'     => 'required|string|max_length[100]',
            'email_dosen'    => 'required|valid_email|max_length[100]|is_unique[dosen.email]|is_unique[users.username,username,{username}]', // Jika username dosen = email
            'jabatan_dosen'  => 'permit_empty|string|max_length[50]',
            'username_dosen' => 'required|alpha_numeric_space|min_length[3]|max_length[50]|is_unique[users.username]',
            'password_dosen' => 'required|min_length[8]',
        ],
        [ // Custom messages
            'nip' => [
                'is_unique' => 'NIP ini sudah terdaftar.'
            ],
            'email_dosen' => [
                'is_unique' => 'Email ini sudah terdaftar.'
            ],
             'username_dosen' => [
                'is_unique' => 'Username ini sudah digunakan.'
            ]
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            // return redirect()->back()->withInput()->with('errors', $validation->getErrors());
            // Untuk tes backend:
            return $this->response->setStatusCode(400)->setJSON(['errors' => $validation->getErrors()]);
        }
        
        $dosenData = [
            'nip'     => $this->request->getPost('nip'),
            'nama'    => $this->request->getPost('nama_dosen'),
            'email'   => $this->request->getPost('email_dosen'),
            'jabatan' => $this->request->getPost('jabatan_dosen'),
        ];

        $userData = [
            'username'     => $this->request->getPost('username_dosen'),
            'password'     => $this->request->getPost('password_dosen'), // Akan di-hash oleh UserModel
            'role'         => 'dosen',
            'reference_id' => $this->request->getPost('nip'),
            'is_active'    => 1, // Dosen yang dibuat admin langsung aktif
        ];

        $db = \Config\Database::connect();
        $db->transStart();

        $dosenSaved = $this->dosenModel->insert($dosenData);
        $userSaved = $this->userModel->insert($userData);

        $db->transComplete();

        if ($db->transStatus() === false || $dosenSaved === false || $userSaved === false) {
            $errors = array_merge($this->dosenModel->errors() ?: [], $this->userModel->errors() ?: []);
            // return redirect()->back()->withInput()->with('error', 'Gagal membuat akun dosen.')->with('errors', $errors);
            // Untuk tes backend:
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Gagal membuat akun dosen.',
                'model_errors_dosen' => $this->dosenModel->errors(),
                'model_errors_user' => $this->userModel->errors()
            ]);
        }

        // return redirect()->to(base_url('admin/manage_dosen'))->with('success', 'Akun dosen berhasil dibuat.');
        // Untuk tes backend:
        return $this->response->setJSON(['success' => 'Akun dosen berhasil dibuat. NIP: ' . $dosenData['nip'] . ', UserID: ' . $this->userModel->insertID()]);
    }

    // Tambahkan metode lain untuk CRUD Dosen jika diperlukan (list, edit, update, delete)
    // Contoh list dosen:
    public function listDosen() {
        // Cek sesi admin
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
             return $this->response->setStatusCode(403)->setJSON(['error' => 'Akses ditolak']);
        }
        $data['dosen'] = $this->dosenModel->findAll();
        return $this->response->setJSON($data);
    }
}