<?php

namespace App\Controllers;

use App\Models\DosenModel;
use App\Models\UserModel;
use App\Models\MahasiswaModel;
use App\Models\KelasModel;
use App\Models\SesiAbsensiModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use DateTime;

class AdminController extends BaseController
{
    protected $dosenModel;
    protected $userModel;
    protected $mahasiswaModel;
    protected $kelasModel;
    protected $sesiModel;
    protected $session;

    public function __construct()
    {
        $this->dosenModel = new DosenModel();
        $this->userModel = new UserModel();
        $this->mahasiswaModel = new MahasiswaModel();
        $this->kelasModel = new KelasModel();
        $this->sesiModel = new SesiAbsensiModel();
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
        return redirect()->to(base_url('login'))->with('error', 'Akses ditolak. Hanya admin.');
    }

    $activityLogModel = new \App\Models\ActivityLogModel();
    $recentLogs = $activityLogModel->getActivityLogs(5);

    $total_dosen = $this->dosenModel->countAll() ?? 0;
    $total_mahasiswa = $this->mahasiswaModel->countAll() ?? 0;
    $total_kelas_aktif = $this->kelasModel->countAllResults() ?? 0;
    $total_sesi_aktif = $this->sesiModel->where('status', 'aktif')->countAllResults() ?? 0;

    // Improved active sessions query
    $aktifitas_sesi = $this->sesiModel->builder()
        ->select('sesi_absensi.id_sesi, sesi_absensi.waktu_mulai_aktual, sesi_absensi.waktu_selesai_aktual, kelas.nama_kelas, kelas.waktu_mulai_kelas, kelas.waktu_selesai_kelas')
        ->join('kelas', 'kelas.kode_kelas = sesi_absensi.kode_kelas')
        ->where('sesi_absensi.status', 'aktif')
        ->where('sesi_absensi.waktu_selesai_aktual >', date('Y-m-d H:i:s'))
        ->orderBy('sesi_absensi.waktu_selesai_aktual', 'ASC')
        ->limit(5)
        ->get()
        ->getResultArray();
    
    // Get system notifications
    $notifications = [
        [
            'icon' => 'fas fa-user-plus text-success me-2',
            'message' => 'New lecturer account created at ' . date('H:i')
        ],
        [
            'icon' => 'fas fa-check-circle text-info me-2',
            'message' => 'Database backup completed successfully'
        ],
        [
            'icon' => 'fas fa-exclamation-triangle text-warning me-2',
            'message' => $total_sesi_aktif . ' active attendance sessions'
        ]
    ];

    $data = [
        'total_dosen' => $total_dosen,
        'total_mahasiswa' => $total_mahasiswa,
        'total_kelas_aktif' => $total_kelas_aktif,
        'total_sesi_aktif' => $total_sesi_aktif,
        'aktifitas_sesi' => $aktifitas_sesi,
        'title' => 'Admin Dashboard',
        'nama_user' => $this->session->get('nama_lengkap') ?? $this->session->get('username'),
        'recent_logs' => $recentLogs,
        'notifications' => $notifications,
    ];

    return view('admin/dashboard', $data);
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
            'nip' => [
                'label' => 'NIP',
                'rules' => 'required|alpha_numeric|max_length[20]|is_unique[dosen.nip]', // Model Dosen akan validasi is_unique juga
                'errors' => [
                    'is_unique' => '{field} ini sudah terdaftar.'
                ]
            ],
            'nama_dosen' => ['label' => 'Nama Dosen', 'rules' => 'required|string|max_length[100]'],
            'email_dosen' => [
                'label' => 'Email Dosen',
                'rules' => 'required|valid_email|max_length[100]|is_unique[dosen.email]|is_unique[users.username]', // Cek unik di dosen.email dan users.username
                'errors' => [
                    'is_unique' => '{field} ini sudah digunakan oleh dosen lain atau sebagai username pengguna.'
                ]
            ],
            'jabatan_dosen' => ['label' => 'Jabatan Dosen', 'rules' => 'permit_empty|string|max_length[50]'],
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
            'nip' => $this->request->getVar('nip'),
            'nama' => $this->request->getVar('nama_dosen'),
            'email' => $this->request->getVar('email_dosen'),
            'jabatan' => $this->request->getVar('jabatan_dosen'),
        ];

        // UserModel akan hash password via callback $beforeInsert
        $userData = [
            'username' => $this->request->getVar('username_dosen'),
            'password' => $this->request->getVar('password_dosen'),
            'role' => 'dosen',
            'reference_id' => $this->request->getVar('nip'), // Hubungkan ke NIP dosen
            'is_active' => 1, // Akun dosen yang dibuat admin langsung aktif
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
                log_message('error', '[AdminController] Transaksi GAGAL dan di-rollback. Dosen saved: ' . ($dosenSaved ? 'true' : 'false') . ', User saved: ' . ($userSaved ? 'true' : 'false'));

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

    /**
     * Menampilkan form untuk mengedit data dosen.
     * @param string $nip NIP dosen yang akan diedit
     */
    public function editDosenForm(string $nip)
    {
        $dosenProfil = $this->dosenModel->find($nip);
        $userAkun = $this->userModel->where('reference_id', $nip)
            ->where('role', 'dosen')
            ->first();

        $data['title'] = "Edit Data Dosen";
        $data['dosen_profil'] = $dosenProfil;
        $data['user_akun'] = $userAkun; // Bisa null jika tidak ada akun user terkait (seharusnya tidak terjadi jika dibuat via storeUserDosen)
        $data['errors'] = session()->getFlashdata('errors'); // Untuk validasi controller jika redirect
        $data['errors_dosen_update'] = session()->getFlashdata('errors_dosen_update');
        $data['errors_user_update'] = session()->getFlashdata('errors_user_update');

        return view('admin/edit', $data);
    }

    /**
     * Memproses update data dosen dan akun loginnya.
     * @param string $nip NIP dosen yang akan diupdate
     */
    public function updateDosen(string $nip)
    {

        // 1. Cek apakah dosen dengan NIP tersebut ada
        $dosenExists = $this->dosenModel->find($nip);
        if (!$dosenExists) {
            return redirect()->to(base_url('admin/dosen/list'))->with('error', 'Data dosen tidak ditemukan.');
        }

        // Dapatkan user ID terkait jika ada
        $userAccount = $this->userModel->where('reference_id', $nip)->where('role', 'dosen')->first();
        $userIdToIgnore = $userAccount ? $userAccount['id_user'] : null;
        log_message('critical', "[DEBUG_UPDATE_DOSEN] NIP dari URL: " . $nip);
        log_message('critical', "[DEBUG_UPDATE_DOSEN] User Account ditemukan: " . ($userAccount ? json_encode($userAccount) : 'Tidak ada'));
        log_message('critical', "[DEBUG_UPDATE_DOSEN] User ID untuk diabaikan (userIdToIgnore): " . $userIdToIgnore);
        log_message('critical', "[DEBUG_UPDATE_DOSEN] Data request (email_dosen): " . $this->request->getVar('email_dosen'));
        log_message('critical', "[DEBUG_UPDATE_DOSEN] Data request (username_dosen): " . $this->request->getVar('username_dosen'));

        // 2. Aturan Validasi untuk Update
        // is_unique perlu mengabaikan record saat ini
        $validationRules = [
            // NIP tidak diupdate, jadi tidak perlu validasi NIP di sini kecuali Anda mengizinkannya (tidak umum untuk PK)
            'nama_dosen' => ['label' => 'Nama Dosen', 'rules' => 'required|string|max_length[100]'],
            'email_dosen' => [
                'label' => 'Email Dosen',
                // Abaikan email saat ini untuk dosen ini, tapi cek unik terhadap yang lain
                // Abaikan juga jika email ini dipakai sebagai username oleh user ini, tapi cek unik terhadap username lain
                'rules' => "required|valid_email|max_length[100]|is_unique[dosen.email,nip,{$nip}]|is_unique[users.username,id_user,{$userIdToIgnore}]",
                'errors' => ['is_unique' => '{field} ini sudah digunakan oleh dosen lain atau sebagai username pengguna lain.']
            ],
            'jabatan_dosen' => ['label' => 'Jabatan Dosen', 'rules' => 'permit_empty|string|max_length[50]'],
            'username_dosen' => [
                'label' => 'Username Dosen',
                // Abaikan username saat ini untuk user ini, tapi cek unik terhadap yang lain
                'rules' => "required|alpha_numeric_space|min_length[3]|max_length[50]|is_unique[users.username,id_user,{$userIdToIgnore}]",
                'errors' => ['is_unique' => '{field} ini sudah digunakan oleh pengguna lain.']
            ],
            'password_dosen' => ['label' => 'Password Dosen Baru', 'rules' => 'permit_empty|min_length[8]'], // Boleh kosong
            'password_confirm_dosen' => [
                'label' => 'Konfirmasi Password Dosen Baru',
                // Hanya required jika password_dosen diisi
                'rules' => 'matches[password_dosen]',
                'errors' => ['matches' => '{field} tidak cocok dengan Password Dosen Baru.']
            ],
            'is_active' => ['label' => 'Status Akun', 'rules' => 'required|in_list[0,1]']
        ];

        // Jika password diisi, maka konfirmasi password juga wajib
        if (!empty($this->request->getVar('password_dosen'))) {
            $validationRules['password_confirm_dosen']['rules'] = 'required|matches[password_dosen]';
        }


        if (!$this->validate($validationRules)) {
            log_message('error', '[AdminController] Validasi update akun dosen gagal (controller): ' . json_encode($this->validator->getErrors()));
            return redirect()->to(base_url('admin/dosen/edit/' . $nip))
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // 3. Siapkan Data
        $dosenData = [
            'nama' => $this->request->getVar('nama_dosen'),
            'email' => $this->request->getVar('email_dosen'),
            'jabatan' => $this->request->getVar('jabatan_dosen'),
        ];

        $userData = [
            'username' => $this->request->getVar('username_dosen'),
            'is_active' => $this->request->getVar('is_active'),
        ];
        // Hanya update password jika diisi
        if (!empty($this->request->getVar('password_dosen'))) {
            $userData['password'] = $this->request->getVar('password_dosen'); // Akan di-hash oleh UserModel
        }

        // 4. Transaksi Database
        $db = \Config\Database::connect();
        $db->transBegin();

        $dosenUpdated = false;
        $userUpdated = false;

        try {
            $dosenUpdated = $this->dosenModel->update($nip, $dosenData);
            if ($dosenUpdated === false && !empty($this->dosenModel->errors())) {
                // Validasi model DosenModel gagal
                log_message('error', '[AdminController] Gagal update dosenModel. NIP: ' . $nip . '. Errors: ' . json_encode($this->dosenModel->errors()));
                // Tidak perlu rollback di sini, akan dihandle di akhir
            } else if ($dosenUpdated === false) {
                // Gagal update karena alasan lain (jarang terjadi jika validasi lolos)
                log_message('error', '[AdminController] Gagal update dosenModel tanpa error validasi. NIP: ' . $nip);
            }


            if ($userAccount) { // Hanya update jika akun user ada
                $userUpdated = $this->userModel->update($userAccount['id_user'], $userData);
                if ($userUpdated === false && !empty($this->userModel->errors())) {
                    log_message('error', '[AdminController] Gagal update userModel. UserID: ' . $userAccount['id_user'] . '. Errors: ' . json_encode($this->userModel->errors()));
                } else if ($userUpdated === false) {
                    log_message('error', '[AdminController] Gagal update userModel tanpa error validasi. UserID: ' . $userAccount['id_user']);
                }
            } else {
                // Seharusnya tidak terjadi jika dosen dibuat dengan storeUserDosen
                // Tapi jika terjadi, kita anggap user update "berhasil" (tidak ada yang diupdate)
                // atau buat akun user baru jika logikanya begitu (tapi ini lebih cocok di create)
                log_message('warning', '[AdminController] Tidak ditemukan akun user terkait untuk dosen NIP: ' . $nip . ' saat update.');
                $userUpdated = true; // Anggap berhasil karena tidak ada yang perlu diupdate untuk user
            }


            if ($db->transStatus() === false || $dosenUpdated === false || ($userAccount && $userUpdated === false)) {
                $db->transRollback();
                log_message('error', '[AdminController] Transaksi UPDATE GAGAL dan di-rollback. NIP: ' . $nip . '. Dosen updated: ' . ($dosenUpdated ? 'true' : 'false') . ', User updated: ' . ($userUpdated ? 'true' : 'false'));

                $combinedErrors = array_merge($this->dosenModel->errors() ?: [], $this->userModel->errors() ?: []);

                return redirect()->to(base_url('admin/dosen/edit/' . $nip))
                    ->withInput()
                    ->with('error', 'Gagal mengupdate data dosen. Periksa kembali data Anda.')
                    ->with('errors_dosen_update', $this->dosenModel->errors())
                    ->with('errors_user_update', $this->userModel->errors());
            } else {
                $db->transCommit();
                log_message('info', '[AdminController] Data dosen berhasil diupdate. NIP: ' . $nip);
                return redirect()->to(base_url('admin/dosen/list'))
                    ->with('success', 'Data dosen ' . esc($dosenData['nama']) . ' berhasil diupdate.');
            }

        } catch (DatabaseException $e) {
            $db->transRollback();
            log_message('error', '[AdminController] DatabaseException saat update akun dosen: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', '[AdminController] Exception umum saat update akun dosen: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
        }
    }

    public function listDosen()
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }
        $perPage = 10;
        $currentPage = $this->request->getVar('page') ?? 1;
        $search = $this->request->getVar('search');
        $status = $this->request->getVar('status');

        $dosenModel = $this->dosenModel
            ->select('dosen.nip, dosen.nama as nama_dosen, dosen.email as email_dosen, dosen.jabatan, users.username, users.is_active')
            ->join('users', 'users.reference_id = dosen.nip AND users.role = \'dosen\'', 'left');

        // Search by name or NIP
        if ($search) {
            $dosenModel->groupStart()
                ->like('dosen.nama', $search)
                ->orLike('dosen.nip', $search)
                ->groupEnd();
        }

        // Filter by status
        if ($status !== null && $status !== '') {
            $dosenModel->where('users.is_active', $status);
        }

        $dosenData = $dosenModel->paginate($perPage, 'default');
        $pager = $this->dosenModel->pager;

        return view('admin/manage_dosen', [
            'dosen_list' => $dosenData,
            'perPage' => $perPage,
            'currentPage' => $currentPage,
            'pager' => $pager,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function deleteDosen(string $nip)
    {

        // 1. Cek apakah dosen dengan NIP tersebut ada
        $dosenProfil = $this->dosenModel->find($nip);
        if (!$dosenProfil) {
            return redirect()->to(base_url('admin/dosen/list'))
                ->with('error', 'Data dosen dengan NIP ' . esc($nip) . ' tidak ditemukan.');
        }

        // Dapatkan user ID terkait jika ada (untuk dihapus dari tabel users)
        $userAccount = $this->userModel->where('reference_id', $nip)
            ->where('role', 'dosen')
            ->first();

        // 2. Mulai Transaksi Database
        $db = \Config\Database::connect();
        $db->transBegin();

        $userDeleted = false;
        $dosenDeleted = false;

        try {
            // Hapus dari tabel users terlebih dahulu (jika ada akun terkait)
            if ($userAccount) {
                $userDeleted = $this->userModel->delete($userAccount['id_user']);
                if ($userDeleted === false) {
                    log_message('error', '[AdminController] Gagal menghapus dari userModel. UserID: ' . $userAccount['id_user'] . '. Errors: ' . json_encode($this->userModel->errors()));
                    // Tidak perlu rollback di sini, akan dihandle di akhir
                } else {
                    log_message('info', '[AdminController] Akun user untuk dosen NIP: ' . $nip . ' (UserID: ' . $userAccount['id_user'] . ') berhasil dihapus.');
                }
            } else {
                // Tidak ada akun user terkait, jadi anggap penghapusan user "berhasil" (tidak ada yang dilakukan)
                $userDeleted = true;
                log_message('info', '[AdminController] Tidak ada akun user terkait untuk dosen NIP: ' . $nip . ' saat proses delete.');
            }

            // Hapus dari tabel dosen HANYA JIKA penghapusan user (jika ada) dianggap berhasil
            // atau jika tidak ada user account terkait.
            if ($userDeleted) { // Jika user berhasil dihapus atau memang tidak ada user
                $dosenDeleted = $this->dosenModel->delete($nip);
                if ($dosenDeleted === false) {
                    log_message('error', '[AdminController] Gagal menghapus dari dosenModel. NIP: ' . $nip . '. Errors: ' . json_encode($this->dosenModel->errors()));
                } else {
                    log_message('info', '[AdminController] Data profil dosen NIP: ' . $nip . ' berhasil dihapus.');
                }
            }


            // Cek status transaksi dan hasil kedua operasi delete
            if ($db->transStatus() === false || $dosenDeleted === false || $userDeleted === false) {
                $db->transRollback();
                log_message('error', '[AdminController] Transaksi DELETE GAGAL dan di-rollback. NIP: ' . $nip . '. Dosen deleted: ' . ($dosenDeleted ? 'true' : 'false') . ', User deleted: ' . ($userDeleted ? 'true' : 'false'));

                $message = 'Gagal menghapus data dosen.';
                if (!$dosenDeleted && !empty($this->dosenModel->errors())) {
                    $message .= ' Error profil: ' . implode(', ', $this->dosenModel->errors());
                }
                if (!$userDeleted && !empty($this->userModel->errors()) && $userAccount) { // Hanya jika ada user account dan gagal
                    $message .= ' Error akun: ' . implode(', ', $this->userModel->errors());
                }



                return redirect()->to(base_url('admin/dosen/list'))
                    ->with('error', $message);

            } else {
                $db->transCommit();
                log_message('info', '[AdminController] Data dosen dan akun terkait (jika ada) untuk NIP: ' . $nip . ' berhasil dihapus.');

                return redirect()->to(base_url('admin/dosen/list'))
                    ->with('success', 'Data dosen ' . esc($dosenProfil['nama']) . ' (NIP: ' . esc($nip) . ') dan akun terkait berhasil dihapus.');
            }

        } catch (DatabaseException $e) {
            $db->transRollback();
            log_message('error', '[AdminController] DatabaseException saat delete akun dosen NIP: ' . $nip . ' - ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            $errorMessage = 'Terjadi kesalahan fatal pada database saat menghapus data.';
            return redirect()->to(base_url('admin/dosen/list'))->with('error', $errorMessage);
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', '[AdminController] Exception umum saat delete akun dosen NIP: ' . $nip . ' - ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            $errorMessage = 'Terjadi kesalahan tidak terduga saat menghapus data.';
            return redirect()->to(base_url('admin/dosen/list'))->with('error', $errorMessage);
        }
    }

    public function activateDosen(string $nip)
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }

        $userAccount = $this->userModel->where('reference_id', $nip)
            ->where('role', 'dosen')
            ->first();
        if (!$userAccount) {
            return redirect()->to(base_url('admin/dosen/list'))
                ->with('error', 'Akun login untuk dosen dengan NIP ' . esc($nip) . ' tidak ditemukan.');
        }

        try {
            if ($this->userModel->update($userAccount['id_user'], ['is_active' => 1])) {
                log_message('info', '[AdminController] Akun dosen NIP: ' . $nip . ' (UserID: ' . $userAccount['id_user'] . ') berhasil diaktifkan.');
                return redirect()->to(base_url('admin/dosen/list'))
                    ->with('success', 'Akun login untuk dosen NIP ' . esc($nip) . ' berhasil diaktifkan.');
            } else {
                log_message('error', '[AdminController] Gagal mengaktifkan akun dosen NIP: ' . $nip . '. Errors: ' . json_encode($this->userModel->errors()));
                return redirect()->to(base_url('admin/dosen/list'))
                    ->with('error', 'Gagal mengaktifkan akun dosen. Error: ' . json_encode($this->userModel->errors()));
            }
        } catch (\Exception $e) {
            log_message('error', '[AdminController] Exception saat aktivasi akun dosen NIP: ' . $nip . ' - ' . $e->getMessage());
            return redirect()->to(base_url('admin/dosen/list'))
                ->with('error', 'Terjadi kesalahan server saat mencoba mengaktifkan akun.');
        }
    }

    public function deactivateDosen(string $nip)
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }

        $userAccount = $this->userModel->where('reference_id', $nip)
            ->where('role', 'dosen')
            ->first();
        if (!$userAccount) {
            return redirect()->to(base_url('admin/dosen/list'))
                ->with('error', 'Akun login untuk dosen dengan NIP ' . esc($nip) . ' tidak ditemukan.');
        }

        try {
            if ($this->userModel->update($userAccount['id_user'], ['is_active' => 0])) {
                log_message('info', '[AdminController] Akun dosen NIP: ' . $nip . ' (UserID: ' . $userAccount['id_user'] . ') berhasil dinonaktifkan.');
                return redirect()->to(base_url('admin/dosen/list'))
                    ->with('success', 'Akun login untuk dosen NIP ' . esc($nip) . ' berhasil dinonaktifkan.');
            } else {
                log_message('error', '[AdminController] Gagal menonaktifkan akun dosen NIP: ' . $nip . '. Errors: ' . json_encode($this->userModel->errors()));
                return redirect()->to(base_url('admin/dosen/list'))
                    ->with('error', 'Gagal menonaktifkan akun dosen. Error: ' . json_encode($this->userModel->errors()));
            }
        } catch (\Exception $e) {
            log_message('error', '[AdminController] Exception saat menonaktifkan akun dosen NIP: ' . $nip . ' - ' . $e->getMessage());
            return redirect()->to(base_url('admin/dosen/list'))
                ->with('error', 'Terjadi kesalahan server saat mencoba menonaktifkan akun.');
        }
    }

    public function listMahasiswa()
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }

        $perPage = 10;
        $currentPage = $this->request->getVar('page') ?? 1;
        $search = $this->request->getVar('search');
        $status = $this->request->getVar('status');

        $mahasiswaModel = new \App\Models\MahasiswaModel();

        $mahasiswaModel->select('mahasiswa.nim, mahasiswa.nama, mahasiswa.email, users.username, users.is_active')
            ->join('users', 'users.reference_id = mahasiswa.nim AND users.role = \'mahasiswa\'', 'left');


        // Search by name or NIM
        if ($search) {
            $mahasiswaModel->groupStart()
                ->like('nama', $search)
                ->orLike('nim', $search)
                ->groupEnd();
        }

        // Filter by status
        if ($status !== null && $status !== '') {
            $mahasiswaModel->where('is_active', $status);
        }

        $mahasiswaData = $mahasiswaModel->paginate($perPage, 'default');
        $pager = $mahasiswaModel->pager;

        return view('admin/manage_mhs', [
            'mahasiswa_list' => $mahasiswaData,
            'perPage' => $perPage,
            'currentPage' => $currentPage,
            'pager' => $pager,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function createUserMahasiswaForm()
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }
        $data['title'] = "Tambah Mahasiswa Baru";
        $data['errors'] = session()->getFlashdata('errors');
        $data['errors_mahasiswa'] = session()->getFlashdata('errors_mahasiswa');
        $data['errors_user'] = session()->getFlashdata('errors_user');

        return view('admin/create_mhs', $data);
    }

    public function storeUserMahasiswa()
    {
        $wantsJson = $this->request->isAJAX() ||
            strpos($this->request->getHeaderLine('Accept'), 'application/json') !== false ||
            $this->request->getHeaderLine('Content-Type') === 'application/json';

        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak. Hanya admin.']);
        }

        // Aturan validasi di Controller
        $validationRules = [
            'nim' => [
                'label' => 'NIM',
                'rules' => 'required|alpha_numeric|max_length[20]|is_unique[mahasiswa.nim]',
                'errors' => [
                    'is_unique' => '{field} ini sudah terdaftar.'
                ]
            ],
            'nama_mahasiswa' => ['label' => 'Nama Mahasiswa', 'rules' => 'required|string|max_length[100]'],
            'email_mahasiswa' => [
                'label' => 'Email Mahasiswa',
                'rules' => 'required|valid_email|max_length[100]|is_unique[mahasiswa.email]|is_unique[users.username]',
                'errors' => [
                    'is_unique' => '{field} ini sudah digunakan oleh mahasiswa lain atau sebagai username pengguna.'
                ]
            ],
            'username_mahasiswa' => [
                'label' => 'Username Mahasiswa',
                'rules' => 'required|alpha_numeric_space|min_length[3]|max_length[50]|is_unique[users.username]',
                'errors' => [
                    'is_unique' => '{field} ini sudah digunakan.'
                ]
            ],
            'password_mahasiswa' => ['label' => 'Password Mahasiswa', 'rules' => 'required|min_length[8]'],
        ];

        if (!$this->validate($validationRules)) {
            log_message('error', '[AdminController] Validasi pembuatan akun mahasiswa gagal (controller): ' . json_encode($this->validator->getErrors()));
            if ($wantsJson) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'validation_error',
                    'errors' => $this->validator->getErrors()
                ]);
            }
            // Untuk browser, redirect kembali dengan error dan input lama
            return redirect()->to(base_url('admin/mahasiswa/create'))
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $mahasiswaData = [
            'nim' => $this->request->getVar('nim'),
            'nama' => $this->request->getVar('nama_mahasiswa'),
            'email' => $this->request->getVar('email_mahasiswa')
        ];

        $userData = [
            'username' => $this->request->getVar('username_mahasiswa'),
            'password' => $this->request->getVar('password_mahasiswa'),
            'role' => 'mahasiswa',
            'reference_id' => $this->request->getVar('nim'),
            'is_active' => 1, // Akun mahasiswa yang dibuat admin langsung aktif
        ];

        $db = \Config\Database::connect();
        $db->transBegin();

        $mahasiswaSaved = false;
        $userSaved = false;
        $userInsertID = null;

        try {
            $mahasiswaSaved = $this->mahasiswaModel->insert($mahasiswaData);
            if (!$mahasiswaSaved) {
                log_message('error', '[AdminController] Gagal insert ke mahasiswaModel. Errors: ' . json_encode($this->mahasiswaModel->errors()));
            } else {
                $userSaved = $this->userModel->insert($userData);
                if (!$userSaved) {
                    log_message('error', '[AdminController] Gagal insert ke userModel. Errors: ' . json_encode($this->userModel->errors()));
                } else {
                    $userInsertID = $this->userModel->getInsertID();
                }
            }

            if ($db->transStatus() === false || $mahasiswaSaved === false || $userSaved === false) {
                $db->transRollback();
                log_message('error', '[AdminController] Transaksi GAGAL dan di-rollback. Mahasiswa saved: ' . ($mahasiswaSaved ? 'true' : 'false') . ', User saved: ' . ($userSaved ? 'true' : 'false'));

                $combinedErrors = array_merge($this->mahasiswaModel->errors() ?: [], $this->userModel->errors() ?: []);

                if ($wantsJson) {
                    return $this->response->setStatusCode(400)->setJSON([
                        'status' => 'error',
                        'message' => 'Penyimpanan data mahasiswa gagal.',
                        'errors' => $combinedErrors
                    ]);
                }
                return redirect()->to(base_url('/admin/mahasiswa/create'))
                    ->withInput()
                    ->with('error', 'Gagal membuat akun mahasiswa. Periksa kembali data Anda.')
                    ->with('errors_mahasiswa', $this->mahasiswaModel->errors())
                    ->with('errors_user', $this->userModel->errors());
            } else {
                $db->transCommit();
                log_message('info', '[AdminController] Akun mahasiswa berhasil dibuat. NIM: ' . $mahasiswaData['nim'] . ', UserID: ' . $userInsertID);
                if ($wantsJson) {
                    return $this->response->setStatusCode(201)->setJSON([
                        'status' => 'success',
                        'message' => 'Akun mahasiswa berhasil dibuat.',
                        'data' => ['nim' => $mahasiswaData['nim'], 'user_id' => $userInsertID]
                    ]);
                }
                return redirect()->to(base_url('admin/mahasiswa/list'))
                    ->with('success', 'Akun mahasiswa ' . esc($mahasiswaData['nama']) . ' berhasil ditambahkan.');
            }

        } catch (DatabaseException $e) {
            $db->transRollback();
            log_message('error', 'DatabaseException saat pembuatan akun mahasiswa: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan database saat pembuatan akun mahasiswa.',
                'detail' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Internal Server Error'
            ]);
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Exception umum saat pembuatan akun mahasiswa: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan tidak terduga saat pembuatan akun mahasiswa.',
                'detail' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Internal Server Error'
            ]);
        }
    }

    public function editMahasiswaForm(string $nim)
    {
        $mahasiswaProfil = $this->mahasiswaModel->find($nim);
        $userAkun = $this->userModel->where('reference_id', $nim)
            ->where('role', 'mahasiswa')
            ->first();

        $data['title'] = "Edit Data Mahasiswa";
        $data['mahasiswa_profil'] = $mahasiswaProfil;
        $data['user_akun'] = $userAkun;
        $data['errors'] = session()->getFlashdata('errors');
        $data['errors_mahasiswa_update'] = session()->getFlashdata('errors_mahasiswa_update');
        $data['errors_user_update'] = session()->getFlashdata('errors_user_update');

        return view('admin/edit_mhs', $data);
    }

    public function updateMahasiswa(string $nim)
    {
        // Cek apakah mahasiswa dengan NIM tersebut ada
        $mahasiswaExists = $this->mahasiswaModel->find($nim);
        if (!$mahasiswaExists) {
            return redirect()->to(base_url('admin/mahasiswa/list'))->with('error', 'Data mahasiswa tidak ditemukan.');
        }

        // Dapatkan user ID terkait jika ada
        $userAccount = $this->userModel->where('reference_id', $nim)->where('role', 'mahasiswa')->first();
        $userIdToIgnore = $userAccount ? $userAccount['id_user'] : null;

        // Aturan Validasi untuk Update
        $validationRules = [
            'nama_mahasiswa' => ['label' => 'Nama Mahasiswa', 'rules' => 'required|string|max_length[100]'],
            'email_mahasiswa' => [
                'label' => 'Email Mahasiswa',
                'rules' => "required|valid_email|max_length[100]|is_unique[mahasiswa.email,nim,{$nim}]|is_unique[users.username,id_user,{$userIdToIgnore}]",
                'errors' => ['is_unique' => '{field} ini sudah digunakan oleh mahasiswa lain atau sebagai username pengguna lain.']
            ],
            'username_mahasiswa' => [
                'label' => 'Username Mahasiswa',
                'rules' => "required|alpha_numeric_space|min_length[3]|max_length[50]|is_unique[users.username,id_user,{$userIdToIgnore}]",
                'errors' => ['is_unique' => '{field} ini sudah digunakan oleh pengguna lain.']
            ],
            'password_mahasiswa' => ['label' => 'Password Mahasiswa Baru', 'rules' => 'permit_empty|min_length[8]'],
            'password_confirm_mahasiswa' => [
                'label' => 'Konfirmasi Password Mahasiswa Baru',
                'rules' => 'matches[password_mahasiswa]',
                'errors' => ['matches' => '{field} tidak cocok dengan Password Mahasiswa Baru.']
            ],
            'is_active' => ['label' => 'Status Akun', 'rules' => 'required|in_list[0,1]']
        ];

        // Jika password diisi, maka konfirmasi password juga wajib
        if (!empty($this->request->getVar('password_mahasiswa'))) {
            $validationRules['password_confirm_mahasiswa']['rules'] = 'required|matches[password_mahasiswa]';
        }

        if (!$this->validate($validationRules)) {
            log_message('error', '[AdminController] Validasi update akun mahasiswa gagal (controller): ' . json_encode($this->validator->getErrors()));
            return redirect()->to(base_url('admin/mahasiswa/edit/' . $nim))
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Siapkan Data
        $mahasiswaData = [
            'nama' => $this->request->getVar('nama_mahasiswa'),
            'email' => $this->request->getVar('email_mahasiswa'),
        ];

        $userData = [
            'username' => $this->request->getVar('username_mahasiswa'),
            'is_active' => $this->request->getVar('is_active'),
        ];
        // Hanya update password jika diisi
        if (!empty($this->request->getVar('password_mahasiswa'))) {
            $userData['password'] = $this->request->getVar('password_mahasiswa'); // Akan di-hash oleh UserModel
        }

        // Transaksi Database
        $db = \Config\Database::connect();
        $db->transBegin();

        $mahasiswaUpdated = false;
        $userUpdated = false;

        try {
            $mahasiswaUpdated = $this->mahasiswaModel->update($nim, $mahasiswaData);
            if ($mahasiswaUpdated === false && !empty($this->mahasiswaModel->errors())) {
                log_message('error', '[AdminController] Gagal update mahasiswaModel. NIM: ' . $nim . '. Errors: ' . json_encode($this->mahasiswaModel->errors()));
            }

            if ($userAccount) {
                $userUpdated = $this->userModel->update($userAccount['id_user'], $userData);
                if ($userUpdated === false && !empty($this->userModel->errors())) {
                    log_message('error', '[AdminController] Gagal update userModel. UserID: ' . $userAccount['id_user'] . '. Errors: ' . json_encode($this->userModel->errors()));
                }
            } else {
                log_message('warning', '[AdminController] Tidak ditemukan akun user terkait untuk mahasiswa NIM: ' . $nim . ' saat update.');
                $userUpdated = true; // Anggap berhasil
            }

            if ($db->transStatus() === false || $mahasiswaUpdated === false || ($userAccount && $userUpdated === false)) {
                $db->transRollback();
                log_message('error', '[AdminController] Transaksi UPDATE GAGAL dan di-rollback. NIM: ' . $nim . '. Mahasiswa updated: ' . ($mahasiswaUpdated ? 'true' : 'false') . ', User updated: ' . ($userUpdated ? 'true' : 'false'));

                $combinedErrors = array_merge($this->mahasiswaModel->errors() ?: [], $this->userModel->errors() ?: []);

                return redirect()->to(base_url('admin/mahasiswa/edit/' . $nim))
                    ->withInput()
                    ->with('error', 'Gagal mengupdate data mahasiswa. Periksa kembali data Anda.')
                    ->with('errors_mahasiswa_update', $this->mahasiswaModel->errors())
                    ->with('errors_user_update', $this->userModel->errors());
            } else {
                $db->transCommit();
                log_message('info', '[AdminController] Data mahasiswa berhasil diupdate. NIM: ' . $nim);
                return redirect()->to(base_url('admin/mahasiswa/list'))
                    ->with('success', 'Data mahasiswa ' . esc($mahasiswaData['nama']) . ' berhasil diupdate.');
            }

        } catch (DatabaseException $e) {
            $db->transRollback();
            log_message('error', '[AdminController] DatabaseException saat update akun mahasiswa: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return redirect()->to(base_url('admin/mahasiswa/edit/' . $nim))
                ->with('error', 'Terjadi kesalahan database: ' . (ENVIRONMENT === 'development' ? $e->getMessage() : 'Kesalahan internal server'));
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', '[AdminController] Exception umum saat update akun mahasiswa: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return redirect()->to(base_url('admin/mahasiswa/edit/' . $nim))
                ->with('error', 'Terjadi kesalahan: ' . (ENVIRONMENT === 'development' ? $e->getMessage() : 'Kesalahan internal server'));
        }
    }

    public function activateMahasiswa(string $nim)
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }

        $userAccount = $this->userModel->where('reference_id', $nim)
            ->where('role', 'mahasiswa')
            ->first();
        if (!$userAccount) {
            return redirect()->to(base_url('admin/mahasiswa/list'))
                ->with('error', 'Akun login untuk mahasiswa dengan NIM ' . esc($nim) . ' tidak ditemukan.');
        }

        try {
            if ($this->userModel->update($userAccount['id_user'], ['is_active' => 1])) {
                log_message('info', '[AdminController] Akun mahasiswa NIM: ' . $nim . ' (UserID: ' . $userAccount['id_user'] . ') berhasil diaktifkan.');
                return redirect()->to(base_url('admin/mahasiswa/list'))
                    ->with('success', 'Akun login untuk mahasiswa NIM ' . esc($nim) . ' berhasil diaktifkan.');
            } else {
                log_message('error', '[AdminController] Gagal mengaktifkan akun mahasiswa NIM: ' . $nim . '. Errors: ' . json_encode($this->userModel->errors()));
                return redirect()->to(base_url('admin/mahasiswa/list'))
                    ->with('error', 'Gagal mengaktifkan akun mahasiswa. Error: ' . json_encode($this->userModel->errors()));
            }
        } catch (\Exception $e) {
            log_message('error', '[AdminController] Exception saat aktivasi akun mahasiswa NIM: ' . $nim . ' - ' . $e->getMessage());
            return redirect()->to(base_url('admin/mahasiswa/list'))
                ->with('error', 'Terjadi kesalahan server saat mencoba mengaktifkan akun.');
        }
    }

    public function deactivateMahasiswa(string $nim)
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }

        $userAccount = $this->userModel->where('reference_id', $nim)
            ->where('role', 'mahasiswa')
            ->first();
        if (!$userAccount) {
            return redirect()->to(base_url('admin/mahasiswa/list'))
                ->with('error', 'Akun login untuk mahasiswa dengan NIM ' . esc($nim) . ' tidak ditemukan.');
        }

        try {
            if ($this->userModel->update($userAccount['id_user'], ['is_active' => 0])) {
                log_message('info', '[AdminController] Akun mahasiswa NIM: ' . $nim . ' (UserID: ' . $userAccount['id_user'] . ') berhasil dinonaktifkan.');
                return redirect()->to(base_url('admin/mahasiswa/list'))
                    ->with('success', 'Akun login untuk mahasiswa NIM ' . esc($nim) . ' berhasil dinonaktifkan.');
            } else {
                log_message('error', '[AdminController] Gagal menonaktifkan akun mahasiswa NIM: ' . $nim . '. Errors: ' . json_encode($this->userModel->errors()));
                return redirect()->to(base_url('admin/mahasiswa/list'))
                    ->with('error', 'Gagal menonaktifkan akun mahasiswa. Error: ' . json_encode($this->userModel->errors()));
            }
        } catch (\Exception $e) {
            log_message('error', '[AdminController] Exception saat menonaktifkan akun mahasiswa NIM: ' . $nim . ' - ' . $e->getMessage());
            return redirect()->to(base_url('admin/mahasiswa/list'))
                ->with('error', 'Terjadi kesalahan server saat mencoba menonaktifkan akun.');
        }
    }

    // Add this method to your AdminController
/**
 * Updates session statuses based on current time.
 * Can be called manually from admin dashboard
 */
public function updateSessionStatus()
{
    if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
        return redirect()->to(base_url('login'))->with('error', 'Not authorized');
    }
    
    $now = date('Y-m-d H:i:s');
    $updatedCount = 0;
    $skippedCount = 0;
    
    try {
        // Update sessions with attendances to 'selesai'
        $expiredWithAttendanceSessions = $this->sesiModel->builder()
            ->where('status', 'aktif')
            ->where('waktu_selesai_aktual <', $now)
            ->where("EXISTS (SELECT 1 FROM kehadiran WHERE kehadiran.id_sesi = sesi_absensi.id_sesi)")
            ->get()
            ->getResultArray();
            
        foreach ($expiredWithAttendanceSessions as $session) {
            $this->sesiModel->update($session['id_sesi'], ['status' => 'selesai']);
            $updatedCount++;
        }
        
        // Update sessions without attendances to 'terlewat'
        $expiredWithoutAttendanceSessions = $this->sesiModel->builder()
            ->where('status', 'aktif')
            ->where('waktu_selesai_aktual <', $now)
            ->where("NOT EXISTS (SELECT 1 FROM kehadiran WHERE kehadiran.id_sesi = sesi_absensi.id_sesi)")
            ->get()
            ->getResultArray();
            
        foreach ($expiredWithoutAttendanceSessions as $session) {
            $this->sesiModel->update($session['id_sesi'], ['status' => 'terlewat']);
            $skippedCount++;
        }
        
        // Log the action
        $activityLogModel = new \App\Models\ActivityLogModel();
        $activityLogModel->logActivity(
            $this->session->get('id_user'),
            $this->session->get('reference_id'),
            'admin',
            'update_sessions',
            "Updated {$updatedCount} completed sessions and {$skippedCount} skipped sessions",
            'sesi_absensi',
            null
        );
        
        return redirect()->to(base_url('admin/dashboard'))
            ->with('success', "Status sesi berhasil diperbarui: {$updatedCount} sesi selesai, {$skippedCount} sesi terlewat");
            
    } catch (\Exception $e) {
        log_message('error', 'Error updating session status: ' . $e->getMessage());
        return redirect()->to(base_url('admin/dashboard'))
            ->with('error', 'Terjadi kesalahan saat memperbarui status sesi');
    }
}

    public function deleteMhs(string $nim)
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }

        // 1. Cek apakah mahasiswa dengan NIM tersebut ada
        $mahasiswaProfil = $this->mahasiswaModel->find($nim);
        if (!$mahasiswaProfil) {
            return redirect()->to(base_url('admin/mahasiswa/list'))
                ->with('error', 'Data mahasiswa tidak ditemukan.');
        }

        // Dapatkan user ID terkait jika ada (untuk dihapus dari tabel users)
        $userAccount = $this->userModel->where('reference_id', $nim)
            ->where('role', 'mahasiswa')
            ->first();

        // 2. Mulai Transaksi Database
        $db = \Config\Database::connect();
        $db->transBegin();

        $userDeleted = false;
        $mahasiswaDeleted = false;

        try {
            // 3. Hapus data user terlebih dahulu (jika ada)
            if ($userAccount) {
                $userDeleted = $this->userModel->delete($userAccount['id_user']);
                if (!$userDeleted) {
                    log_message('error', '[AdminController] Gagal menghapus user untuk mahasiswa NIM: ' . $nim . '. Errors: ' . json_encode($this->userModel->errors()));
                }
            } else {
                $userDeleted = true; // Tidak ada user yang perlu dihapus
            }

            // 4. Hapus data mahasiswa
            $mahasiswaDeleted = $this->mahasiswaModel->delete($nim);
            if (!$mahasiswaDeleted) {
                log_message('error', '[AdminController] Gagal menghapus mahasiswa NIM: ' . $nim . '. Errors: ' . json_encode($this->mahasiswaModel->errors()));
            }

            // 5. Commit atau Rollback transaksi berdasarkan hasil operasi
            if ($db->transStatus() === false || !$mahasiswaDeleted || ($userAccount && !$userDeleted)) {
                $db->transRollback();
                $errorMessage = 'Gagal menghapus data mahasiswa.';
                if (!$userDeleted && !$mahasiswaDeleted) {
                    $errorMessage = 'Gagal menghapus data mahasiswa dan akun login terkait.';
                } else if (!$userDeleted) {
                    $errorMessage = 'Gagal menghapus akun login mahasiswa.';
                }
                log_message('error', '[AdminController] DELETE GAGAL: ' . $errorMessage . ' NIM: ' . $nim);
                return redirect()->to(base_url('admin/mahasiswa/list'))->with('error', $errorMessage);
            } else {
                $db->transCommit();
                log_message('info', '[AdminController] Berhasil menghapus mahasiswa NIM: ' . $nim);
                return redirect()->to(base_url('admin/mahasiswa/list'))
                    ->with('success', 'Data mahasiswa ' . esc($mahasiswaProfil['nama']) . ' berhasil dihapus.');
            }
        } catch (DatabaseException $e) {
            $db->transRollback();
            $errorMessage = 'Terjadi kesalahan database saat menghapus data mahasiswa.';
            if (ENVIRONMENT === 'development') {
                $errorMessage .= ' Detail: ' . $e->getMessage();
            }
            log_message('error', '[AdminController] DatabaseException saat delete mahasiswa: ' . $e->getMessage());
            return redirect()->to(base_url('admin/mahasiswa/list'))->with('error', $errorMessage);
        } catch (\Exception $e) {
            $db->transRollback();
            $errorMessage = 'Terjadi kesalahan tidak terduga saat menghapus data mahasiswa.';
            if (ENVIRONMENT === 'development') {
                $errorMessage .= ' Detail: ' . $e->getMessage();
            }
            log_message('error', '[AdminController] Exception umum saat delete mahasiswa: ' . $e->getMessage());
            return redirect()->to(base_url('admin/mahasiswa/list'))->with('error', $errorMessage);
        }
    }


    /**
     * Menampilkan halaman daftar semua sesi absensi untuk Admin.
     */
    public function listSesi()
    {
        // Inisialisasi model
        $sesiAbsensiModel = new \App\Models\SesiAbsensiModel();

        // Get the current page from request
        $page = $this->request->getVar('page_sesi_group') ?? 1;

        // Fetch all results using get()->getResultArray() instead of findAll()
        $allSesiData = $sesiAbsensiModel->getAllSesiWithDetails()->get()->getResultArray();

        // Set up pagination manually
        $perPage = 10;
        $totalItems = count($allSesiData);
        $totalPages = ceil($totalItems / $perPage);

        // Calculate offset for current page
        $offset = ($page - 1) * $perPage;

        // Get only the items for current page
        $currentPageItems = array_slice($allSesiData, $offset, $perPage);

        // Create a custom pager
        $pager = service('pager');
        $pager->setPath('admin/sesi');  // Set the path for pagination links
        $pager->makeLinks($page, $perPage, $totalItems, 'default_full', 1, 'sesi_group');

        $data = [
            'title' => 'Manajemen Sesi Absensi',
            'sesi' => $currentPageItems,
            'pager' => $pager,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ];

        // Memuat view
        return view('admin/list_semua_sesi', $data);
    }
}