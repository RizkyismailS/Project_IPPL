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
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak. Hanya admin.']);
        }

        $total_dosen = $this->dosenModel->countAll() ?? 0;
        $total_mahasiswa = $this->mahasiswaModel->countAll() ?? 0;
        $total_kelas_aktif = $this->kelasModel->countAllResults() ?? 0;
        $total_sesi_aktif = $this->sesiModel->where('status', 'aktif')->countAllResults() ?? 0;

        // Pastikan aktifitas_sesi selalu array
        $aktifitas_sesi = $this->sesiModel->where('status', 'aktif')
            ->join('kelas', 'kelas.kode_kelas = sesi_absensi.kode_kelas')
            ->select('nama_kelas, waktu_mulai_kelas, waktu_selesai_kelas')
            ->findAll() ?? [];

        // Hitung waktu tersisa untuk setiap sesi (jika ada data)
        foreach ($aktifitas_sesi as &$sesi) {
            $waktuSelesai = $sesi['waktu_selesai_kelas'];
            $waktuSekarang = new DateTime();
            $menit = $waktuSekarang->format('i');
            $waktuMulai = new DateTime($sesi['waktu_mulai_kelas']);
            $sesi['hitung_waktu'] = date_add($waktuMulai, date_interval_create_from_date_string($menit . ' minutes'));
        }

        $data = [
            'total_dosen' => $total_dosen,
            'total_mahasiswa' => $total_mahasiswa,
            'total_kelas_aktif' => $total_kelas_aktif,
            'total_sesi_aktif' => $total_sesi_aktif,
            'aktifitas_sesi' => $aktifitas_sesi,
            'title' => 'Admin Dashboard',
            'nama_user' => $this->session->get('nama_lengkap') ?? $this->session->get('username'),
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