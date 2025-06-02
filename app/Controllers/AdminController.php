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
        
        return view('admin/dashboard', $data);

        return $this->response->setJSON($data);
    }

    public function createUserDosenForm()
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
             return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }
        $data['title'] = "Tambah Dosen Baru";
        $data['errors'] = session()->getFlashdata('errors');
        $data['errors_dosen'] = session()->getFlashdata('errors_dosen');
        $data['errors_user'] = session()->getFlashdata('errors_user');

        return view('admin/create', $data);
        return $this->response->setJSON(['status' => 'info', 'message' => 'Endpoint untuk menampilkan form pembuatan akun dosen (via GET).']);
    }

    public function storeUserDosen()
    {
        $wantsJson = $this->request->isAJAX() || 
             strpos($this->request->getHeaderLine('Accept'), 'application/json') !== false ||
             $this->request->getHeaderLine('Content-Type') === 'application/json';

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
            log_message('error', '[AdminController] Validasi pembuatan akun dosen gagal (controller): ' . json_encode($this->validator->getErrors()));
            if ($wantsJson) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'validation_error',
                    'errors' => $this->validator->getErrors()
                ]);
            }
            // Untuk browser, redirect kembali dengan error dan input lama
            return redirect()->to(base_url('admin/dosen/create')) // Arahkan kembali ke form create
                             ->withInput()
                             ->with('errors', $this->validator->getErrors());
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

        $dosenSaved = false;
        $userSaved = false;
        $userInsertID = null;

        try {
            $dosenSaved = $this->dosenModel->insert($dosenData);
             if (!$dosenSaved) {
                log_message('error', '[AdminController] Gagal insert ke dosenModel. Errors: ' . json_encode($this->dosenModel->errors()));
            } else {
                $userSaved = $this->userModel->insert($userData);
                if (!$userSaved) {
                    log_message('error', '[AdminController] Gagal insert ke userModel. Errors: ' . json_encode($this->userModel->errors()));
                } else {
                    $userInsertID = $this->userModel->getInsertID();
                }
            }


            if ($db->transStatus() === false || $dosenSaved === false || $userSaved === false) {
                $db->transRollback();
                log_message('error', '[AdminController] Transaksi GAGAL dan di-rollback. Dosen saved: ' . ($dosenSaved ? 'true':'false') . ', User saved: ' . ($userSaved ? 'true':'false'));
                
                $combinedErrors = array_merge($this->dosenModel->errors() ?: [], $this->userModel->errors() ?: []);

                if ($wantsJson) {
                  
                    return $this->response->setStatusCode(400)->setJSON([ // Bisa 400 jika karena validasi model
                        'status' => 'error',
                        'message' => 'Penyimpanan data dosen gagal.',
                        'errors' => $combinedErrors // Gabungkan error dari kedua model
                    ]);
                }
                return redirect()->to(base_url('/admin/dosen/create')) // Kembali ke form create
                                 ->withInput()
                                 ->with('error', 'Gagal membuat akun dosen. Periksa kembali data Anda.')
                                 ->with('errors_dosen', $this->dosenModel->errors()) // Kirim error spesifik model
                                 ->with('errors_user', $this->userModel->errors());
            } else {
                $db->transCommit();
                log_message('info', '[AdminController] Akun dosen berhasil dibuat. NIP: ' . $dosenData['nip'] . ', UserID: ' . $userInsertID);
                if ($wantsJson) {
                    return $this->response->setStatusCode(201)->setJSON([
                        'status' => 'success',
                        'message' => 'Akun dosen berhasil dibuat.',
                        'data' => ['nip' => $dosenData['nip'], 'user_id' => $userInsertID]
                    ]);
                }
                return redirect()->to(base_url('admin/dosen/list')) // Arahkan ke halaman daftar dosen (manageDosen)
                                 ->with('success', 'Akun dosen ' . esc($dosenData['nama']) . ' berhasil ditambahkan.');
            }

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
        $perPage = 10; // Atur jumlah per halaman
        $currentPage = $this->request->getVar('page') ?? 1; // Ambil halaman saat ini dari query string, default ke 1
        // Di AdminController saat mengambil data untuk list
        $dosenData = $this->dosenModel
                  ->select('dosen.nip, dosen.nama as nama_dosen, dosen.email as email_dosen, dosen.jabatan, users.username, users.is_active')
                  ->join('users', 'users.reference_id = dosen.nip AND users.role = \'dosen\'', 'left')
                  ->paginate($perPage, 'group_name'); // atau findAll() jika tanpa pagination
        $pager = $this->dosenModel->pager;

        $data = [
            'dosen_list' => $dosenData,
            'perPage' => $perPage,
            'currentPage' => $currentPage,
            'pager' => $pager,
        ];
        return view('admin/manage_dosen', $data);
        return $this->response->setJSON(['status' => 'success', 'data' => $data['dosen']]);
    }
}