<?php

namespace App\Controllers;

use App\Models\DosenModel;
use App\Models\KelasModel;
use App\Models\MataKuliahModel;
use App\Models\EnrollmentModel; 
use App\Models\SesiAbsensiModel; 
use CodeIgniter\Database\Exceptions\DatabaseException;
use App\Libraries\RuleExist;

class DosenController extends BaseController
{
    // Add these helper methods at the end of your DosenController class

    private function validateMatakuliah($kode_matakuliah) {
        return $this->mataKuliahModel->find($kode_matakuliah) !== null;
    }

    private function validateTimeFormat($time) {
        return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $time);
    }

    private function validateTimeSequence($start, $end) {
        return strtotime($end) > strtotime($start);
    }

    protected $dosenModel;
    protected $kelasModel;
    protected $mataKuliahModel;
    protected $session;

    public function __construct()
    {
        $this->dosenModel = new DosenModel();
        $this->kelasModel = new KelasModel();
        $this->mataKuliahModel = new MataKuliahModel();
        $this->session = \Config\Services::session();
        helper(['form', 'url']);
        // PENTING: Terapkan DosenAuthFilter melalui Routes
    }

    public function dashboard()
    {
        return view('dosen/dashboard', [
            'title' => 'Buat Kelas Baru',
            'sidebar' => 'layout/dosen_sidebar',
        ]);
    }

    public function listKelas()
    {
        $dosenNip = $this->session->get('reference_id');
        if (!$dosenNip) {
            return redirect()->to(base_url('login'))->with('error', 'Sesi tidak valid atau NIP tidak ditemukan.');
        }

        $perPage = 10;
        $currentPage = $this->request->getVar('page_kelas_dosen') ? (int) $this->request->getVar('page_kelas_dosen') : 1;
        
        $data['kelas_list'] = $this->kelasModel->getAllKelasWithDetail(
            $perPage,
            'kelas_dosen', // Nama grup pager
            $dosenNip
        );
        $data['pager'] = $this->kelasModel->pager;
        $data['currentPage'] = $currentPage;
        $data['perPage'] = $perPage;
        $data['title'] = "Daftar Kelas Saya";
        $data['nama_user'] = $this->session->get('nama_lengkap') ?? $this->session->get('username');

        return view('dosen/listkelas', $data);
    }

    public function createKelasForm()
    {
        $data['title'] = "Buat Kelas Baru";
        $data['nama_user'] = $this->session->get('nama_lengkap') ?? $this->session->get('username');
        
        // Ambil daftar mata kuliah untuk dropdown
        $data['mata_kuliah_list'] = $this->mataKuliahModel->orderBy('nama_matakuliah', 'ASC')->findAll();
        
        // Ambil NIP dan nama dosen yang sedang login
        $dosenNipLogin = $this->session->get('reference_id');
        $dosenInfo = $this->dosenModel->find($dosenNipLogin);
        
        $data['dosen_nip_login'] = $dosenNipLogin;
        $data['nama_dosen_login'] = $dosenInfo ? $dosenInfo['nama'] : 'N/A';

        // Untuk menampilkan error validasi jika ada redirect dari storeKelas
        $data['errors'] = session()->getFlashdata('errors'); 
        $data['errors_kelas'] = session()->getFlashdata('errors_kelas');

        return view('dosen/kelasBaru', $data); // Mengarahkan ke view form
    }

    public function storeKelas()
    {
        $wantsJson = $this->requestIsJson();
        $dosenNipLogin = $this->session->get('reference_id');

        if (empty($dosenNipLogin)) {
            // Handle jika NIP dosen tidak ada di sesi (seharusnya tidak terjadi jika filter aktif)
            log_message('error', '[DosenController] NIP Dosen tidak ada di sesi saat storeKelas.');
            if ($wantsJson) {
                return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Sesi tidak valid.']);
            }
            return redirect()->to(base_url('login'))->with('error', 'Sesi Anda tidak valid, silakan login kembali.');
        }
        // Get the input first for validation
        $kode_matakuliah = $this->request->getVar('kode_matakuliah');
        $waktu_mulai = $this->request->getVar('waktu_mulai_kelas');
        $waktu_selesai = $this->request->getVar('waktu_selesai_kelas');
        
        // Modify validation rules to use built-in rules only
        $validationRules = [
            'kode_kelas' => [
                'label' => 'Kode Kelas',
                'rules' => 'required|alpha_dash|max_length[20]|is_unique[kelas.kode_kelas]',
                'errors' => [
                    'required' => '{field} wajib diisi.',
                    'alpha_dash' => '{field} hanya boleh huruf, angka, underscore dan dash.',
                    'max_length' => '{field} maksimal 20 karakter.',
                    'is_unique' => '{field} ini sudah digunakan.'
                ]
            ],
            'nama_kelas' => ['label' => 'Nama Kelas', 'rules' => 'required|string|max_length[100]'],
            'kode_matakuliah' => [
                'label' => 'Mata Kuliah', 
                'rules' => 'required',
                'errors' => ['required' => '{field} wajib dipilih.']
            ],
            'hari' => ['label' => 'Hari', 'rules' => 'required|in_list[Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu]'],
            'waktu_mulai_kelas' => ['label' => 'Waktu Mulai', 'rules' => 'required'],
            'waktu_selesai_kelas' => ['label' => 'Waktu Selesai', 'rules' => 'required'],
            'ruangan' => ['label' => 'Ruangan', 'rules' => 'permit_empty|string|max_length[50]'],
            'tahun_ajaran' => ['label' => 'Tahun Ajaran', 'rules' => 'required|string|max_length[10]'],
            'semester' => ['label' => 'Semester', 'rules' => 'required|string|max_length[20]'],
            'kode_enrollment' => [
                'label' => 'Kode Enrollment', 
                'rules' => 'permit_empty|alpha_numeric|max_length[20]|is_unique[kelas.kode_enrollment]',
                'errors' => ['is_unique' => '{field} ini sudah digunakan.']
            ],
        ];
        
        $validationFailed = false;
        $errors = $this->validator ? $this->validator->getErrors() : [];

        // Validate matakuliah
        if (!$this->validateMatakuliah($kode_matakuliah)) {
            $errors['kode_matakuliah'] = 'Mata Kuliah yang dipilih tidak valid.';
            $validationFailed = true;
        }

        // Validate time formats
        if (!$this->validateTimeFormat($waktu_mulai)) {
            $errors['waktu_mulai_kelas'] = 'Format Waktu Mulai tidak valid.';
            $validationFailed = true;
        }

        if (!$this->validateTimeFormat($waktu_selesai)) {
            $errors['waktu_selesai_kelas'] = 'Format Waktu Selesai tidak valid.';
            $validationFailed = true;
        }

        // Validate time sequence
        if ($this->validateTimeFormat($waktu_mulai) && $this->validateTimeFormat($waktu_selesai) && 
            !$this->validateTimeSequence($waktu_mulai, $waktu_selesai)) {
            $errors['waktu_selesai_kelas'] = 'Waktu Selesai harus setelah Waktu Mulai.';
            $validationFailed = true;
        }

        // Handle validation failures at once
        if ($validationFailed) {
            return redirect()->to(base_url('dosen/kelas/create'))
                            ->withInput()
                            ->with('errors', $errors);
        }

        $kelasData = [
            'kode_kelas'        => $this->request->getVar('kode_kelas'),
            'nama_kelas'        => $this->request->getVar('nama_kelas'),
            'kode_matakuliah'   => $this->request->getVar('kode_matakuliah'),
            'dosen_nip'         => $dosenNipLogin,
            'hari'              => $this->request->getVar('hari'),
            'waktu_mulai_kelas' => $this->request->getVar('waktu_mulai_kelas'),
            'waktu_selesai_kelas' => $this->request->getVar('waktu_selesai_kelas'),
            'ruangan'           => $this->request->getVar('ruangan'),
            'tahun_ajaran'      => $this->request->getVar('tahun_ajaran'),
            'semester'          => $this->request->getVar('semester'),
            'kode_enrollment'   => $this->request->getVar('kode_enrollment'),
        ];

        if (empty($kelasData['kode_enrollment'])) {
            $isUnique = false;
            while(!$isUnique) {
                $newEnrollCode = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)); // Generate 8 char random
                if (!$this->kelasModel->findByKodeEnrollment($newEnrollCode)) {
                    $kelasData['kode_enrollment'] = $newEnrollCode;
                    $isUnique = true;
                }
            }
        }
        
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            if (!$this->kelasModel->insert($kelasData)) {
                $db->transRollback();
                log_message('error', '[DosenController] Gagal insert ke kelasModel. Errors: ' . json_encode($this->kelasModel->errors()));
                if ($wantsJson) { /* ... */ }
                return redirect()->to(base_url('dosen/kelas/create'))
                                 ->withInput()
                                 ->with('errors_kelas', $this->kelasModel->errors())
                                 ->with('error', 'Gagal menyimpan data kelas. Periksa error model.');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                log_message('error', '[DosenController] Status transaksi DB gagal post-insert kelas.');
                if ($wantsJson) { /* ... */ }
                return redirect()->to(base_url('dosen/kelas/create'))->withInput()->with('error', 'Kesalahan database saat menyimpan.');
            }

            $db->transCommit();
            log_message('info', '[DosenController] Kelas baru berhasil dibuat. Kode Kelas: ' . $kelasData['kode_kelas'] . '. Kode Enrollment: ' . $kelasData['kode_enrollment']);
            if ($wantsJson) { /* ... */ }
            return redirect()->to(base_url('dosen/kelas'))
                             ->with('success', 'Kelas "' . esc($kelasData['nama_kelas']) . '" dengan kode enrollment "' . esc($kelasData['kode_enrollment']) . '" berhasil ditambahkan.');

        } catch (DatabaseException $e) {
            $db->transRollback();
            log_message('error', '[DosenController] DatabaseException: ' . $e->getMessage());
            if ($wantsJson) { /* ... */ }
            return redirect()->to(base_url('dosen/kelas/create'))->withInput()->with('error', 'Kesalahan database.');
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', '[DosenController] General Exception: ' . $e->getMessage());
            if ($wantsJson) { /* ... */ }
            return redirect()->to(base_url('dosen/kelas/create'))->withInput()->with('error', 'Kesalahan tidak terduga.');
        }
    }

    /**
     * Menampilkan detail kelas yang diajar oleh dosen.
     * Dosen hanya bisa melihat detail kelas yang memang dia ampu.
     * @param string $kodeKelas
     */
    public function detailKelas(string $kodeKelas)
    {
        $dosenNipLogin = $this->session->get('reference_id');

        $kelas = $this->kelasModel->getKelasDetail($kodeKelas);

        if (!$kelas) {
            log_message('error', "[DosenController] Kelas dengan kode $kodeKelas tidak ditemukan saat dosen $dosenNipLogin mencoba melihat detail.");
            return redirect()->to(base_url('dosen/kelas'))->with('error', 'Kelas tidak ditemukan.');
        }

       
        if ($kelas['dosen_nip'] !== $dosenNipLogin) {
            log_message('warning', "[DosenController] Dosen $dosenNipLogin mencoba akses detail kelas $kodeKelas yang bukan miliknya.");
           
            return redirect()->to(base_url('dosen/kelas'))->with('error', 'Anda tidak memiliki hak untuk melihat detail kelas ini.');
        }

        $data['title'] = 'Detail Kelas: ' . esc($kelas['nama_kelas']);
        $data['kelas'] = $kelas; 
        $data['nama_user'] = $this->session->get('nama_lengkap') ?? $this->session->get('username');

        // Di sini Anda juga bisa mengambil data lain terkait kelas ini, misalnya:
        // 1. Daftar mahasiswa yang terdaftar (memerlukan EnrollmentModel)
        //    $enrollmentModel = new \App\Models\EnrollmentModel();
        //    $data['mahasiswa_terdaftar'] = $enrollmentModel->getMahasiswaByKelas($kodeKelas);

        // 2. Daftar sesi absensi yang sudah dibuat untuk kelas ini (memerlukan SesiAbsensiModel)
        //    $sesiAbsensiModel = new \App\Models\SesiAbsensiModel();
        //    $data['sesi_absensi_list'] = $sesiAbsensiModel->getSesiByKelas($kodeKelas);

        return view('dosen/detailKelas', $data);
    }

    /**
     * Menampilkan form untuk mengedit data kelas yang diajar oleh dosen.
     * @param string $kodeKelas Kode kelas yang akan diedit
     */
    public function editKelasForm(string $kodeKelas)
    {
        // Filter 'dosenAuthFilter' seharusnya sudah memastikan user adalah dosen dan sudah login
        $dosenNipLogin = $this->session->get('reference_id');
        if (!$dosenNipLogin) {
            // Ini seharusnya tidak terjadi jika filter bekerja
            return redirect()->to(base_url('login'))->with('error', 'Sesi tidak valid atau NIP tidak ditemukan.');
        }

        // Ambil detail kelas yang akan diedit
        // Menggunakan getKelasDetail untuk mendapatkan info join dengan mata kuliah dan dosen (meskipun dosen pengampu adalah diri sendiri)
        $kelas = $this->kelasModel->getKelasDetail($kodeKelas);

        // Cek apakah kelas ditemukan
        if (!$kelas) {
            log_message('error', "[DosenController] Edit Form: Kelas dengan kode $kodeKelas tidak ditemukan.");
            return redirect()->to(base_url('dosen/kelas'))->with('error', 'Kelas yang akan diedit tidak ditemukan.');
        }

        // VALIDASI PENTING: Pastikan dosen yang login adalah pengampu kelas ini
        if ($kelas['dosen_nip'] !== $dosenNipLogin) {
            log_message('warning', "[DosenController] Edit Form: Dosen $dosenNipLogin mencoba akses edit kelas $kodeKelas yang bukan miliknya.");
            return redirect()->to(base_url('dosen/kelas'))->with('error', 'Anda tidak memiliki hak untuk mengedit kelas ini.');
        }

        $data['title'] = 'Edit Kelas: ' . esc($kelas['nama_kelas']);
        $data['nama_user'] = $this->session->get('nama_lengkap') ?? $this->session->get('username');
        $data['kelas'] = $kelas; // Data kelas yang akan diedit

        // Ambil daftar mata kuliah untuk dropdown
        $data['mata_kuliah_list'] = $this->mataKuliahModel->orderBy('nama_matakuliah', 'ASC')->findAll();
        
        // Ambil NIP dan nama dosen yang sedang login (untuk ditampilkan, meskipun NIP dosen_nip di kelas tidak diubah)
        $data['dosen_nip_login'] = $dosenNipLogin;
        $data['nama_dosen_login'] = $this->session->get('nama_lengkap') ?? $this->session->get('username'); // Bisa juga diambil dari DosenModel

        // Untuk menampilkan error validasi jika ada redirect dari proses update yang gagal
        $data['errors'] = session()->getFlashdata('errors'); 
        $data['errors_kelas_update'] = session()->getFlashdata('errors_kelas_update'); // Flashdata khusus untuk error update

        return view('dosen/kelas_edit', $data); // Mengarahkan ke view form edit
    }

    /**
     * Memproses update data kelas yang diajar oleh dosen.
     * @param string $kodeKelas Kode kelas yang akan diupdate
     */
    public function updateKelas(string $kodeKelas)
    {
        $dosenNipLogin = $this->session->get('reference_id');
        $wantsJson = $this->requestIsJson();

        // Early returns for common validations
        if (empty($dosenNipLogin)) {
            log_message('error', '[DosenController] NIP Dosen tidak ada di sesi saat updateKelas.');
            return $this->errorResponse(
                'Sesi Anda tidak valid, silakan login kembali.',
                403,
                'Sesi tidak valid.',
                'login',
                $wantsJson
            );
        }

        $kelasToUpdate = $this->kelasModel->find($kodeKelas);
        if (!$kelasToUpdate) {
            log_message('error', "[DosenController] Update: Kelas dengan kode $kodeKelas tidak ditemukan.");
            return $this->errorResponse(
                'Kelas yang akan diupdate tidak ditemukan.',
                404,
                'Kelas yang akan diupdate tidak ditemukan.',
                'dosen/kelas',
                $wantsJson
            );
        }

        if ($kelasToUpdate['dosen_nip'] !== $dosenNipLogin) {
            log_message('warning', "[DosenController] Update: Dosen $dosenNipLogin mencoba update kelas $kodeKelas yang bukan miliknya.");
            return $this->errorResponse(
                'Anda tidak memiliki hak untuk mengupdate kelas ini.',
                403,
                'Anda tidak memiliki hak untuk mengupdate kelas ini.',
                'dosen/kelas',
                $wantsJson
            );
        }

        // Get inputs for custom validation
        $kode_matakuliah = $this->request->getVar('kode_matakuliah');
        $waktu_mulai = $this->request->getVar('waktu_mulai_kelas');
        $waktu_selesai = $this->request->getVar('waktu_selesai_kelas');

        // Perform validation
        $validationRules = $this->getUpdateKelasValidationRules($kodeKelas);
        
        // Combined validation approach
        if (!$this->validate($validationRules)) {
            $errors = $this->validator->getErrors();
            
            // Custom validations
            $validationFailed = false;
            
            // Validate matakuliah
            if (!$this->validateMatakuliah($kode_matakuliah)) {
                $errors['kode_matakuliah'] = 'Mata Kuliah yang dipilih tidak valid.';
                $validationFailed = true;
            }
            
            // Validate time formats
            if (!$this->validateTimeFormat($waktu_mulai)) {
                $errors['waktu_mulai_kelas'] = 'Format Waktu Mulai tidak valid.';
                $validationFailed = true;
            }
            
            if (!$this->validateTimeFormat($waktu_selesai)) {
                $errors['waktu_selesai_kelas'] = 'Format Waktu Selesai tidak valid.';
                $validationFailed = true;
            }
            
            // Validate time sequence
            if ($this->validateTimeFormat($waktu_mulai) && $this->validateTimeFormat($waktu_selesai) && 
                !$this->validateTimeSequence($waktu_mulai, $waktu_selesai)) {
                $errors['waktu_selesai_kelas'] = 'Waktu Selesai harus setelah Waktu Mulai.';
                $validationFailed = true;
            }
            
            if ($validationFailed || count($errors) > 0) {
                log_message('error', '[DosenController] Validasi update kelas gagal. Kode Kelas: '.$kodeKelas.'. Errors: ' . json_encode($errors));
                
                if ($wantsJson) {
                    return $this->response->setStatusCode(400)->setJSON([
                        'status' => 'validation_error',
                        'errors' => $errors
                    ]);
                }
                
                return redirect()->to(base_url('dosen/kelas/edit/' . $kodeKelas))
                                ->withInput()
                                ->with('errors', $errors);
            }
        }

        // Prepare update data
        $updateData = $this->prepareKelasUpdateData();
        
        // Handle kode_enrollment special case
        $this->handleKodeEnrollmentUpdate($updateData, $kelasToUpdate);

        // Database transaction
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            if (!$this->kelasModel->update($kodeKelas, $updateData)) {
                $db->transRollback();
                log_message('error', '[DosenController] Gagal update ke kelasModel. Kode Kelas: '.$kodeKelas.'. Errors: ' . json_encode($this->kelasModel->errors()));
                
                return $this->errorResponse(
                    'Gagal mengupdate data kelas. Periksa kembali input Anda.',
                    400,
                    'Update data kelas gagal.',
                    'dosen/kelas/edit/' . $kodeKelas,
                    $wantsJson,
                    ['errors_kelas_update' => $this->kelasModel->errors()]
                );
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->errorResponse(
                    'Kesalahan database saat update.',
                    500,
                    'Database error during update.',
                    'dosen/kelas/edit/' . $kodeKelas,
                    $wantsJson
                );
            }

            $db->transCommit();
            log_message('info', '[DosenController] Kelas berhasil diupdate. Kode Kelas: ' . $kodeKelas);
            
            if ($wantsJson) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Kelas berhasil diupdate.'
                ]);
            }
            
            return redirect()->to(base_url('dosen/kelas/detail/' . $kodeKelas))
                            ->with('success', 'Kelas "' . esc($updateData['nama_kelas']) . '" berhasil diupdate.');

        } catch (DatabaseException $e) {
            return $this->handleException($e, $db, $kodeKelas, 'DatabaseException', $wantsJson);
        } catch (\Exception $e) {
            return $this->handleException($e, $db, $kodeKelas, 'General Exception', $wantsJson);
        }
    }

    private function getUpdateKelasValidationRules(string $kodeKelas): array
    {
        return [
            'nama_kelas'        => ['label' => 'Nama Kelas', 'rules' => 'required|string|max_length[100]'],
            'kode_matakuliah'   => ['label' => 'Mata Kuliah', 'rules' => 'required'],
            'hari'              => ['label' => 'Hari', 'rules' => 'required|in_list[Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu]'],
            'waktu_mulai_kelas' => ['label' => 'Waktu Mulai', 'rules' => 'required'],
            'waktu_selesai_kelas' => ['label' => 'Waktu Selesai', 'rules' => 'required'],
            'ruangan'           => ['label' => 'Ruangan', 'rules' => 'permit_empty|string|max_length[50]'],
            'tahun_ajaran'      => ['label' => 'Tahun Ajaran', 'rules' => 'required|string|max_length[10]'],
            'semester'          => ['label' => 'Semester', 'rules' => 'required|string|max_length[20]'],
            'kode_enrollment'   => [
                'label' => 'Kode Enrollment',
                'rules' => "permit_empty|alpha_numeric|max_length[100]|is_unique[kelas.kode_enrollment,kode_kelas,{$kodeKelas}]",
                'errors' => ['{field} ini sudah digunakan oleh kelas lain.']
            ],
        ];
    }

    private function handleKodeEnrollmentUpdate(array &$updateData, array $kelasToUpdate): void
    {
        $newKodeEnrollment = $this->request->getVar('kode_enrollment');
        
        // If same as existing, don't update it at all
        if ($newKodeEnrollment === $kelasToUpdate['kode_enrollment']) {
            // Remove from update data to avoid unnecessary validation
            unset($updateData['kode_enrollment']);
            return;
        }
        
        // If empty, set to null
        if (empty($newKodeEnrollment)) {
            $updateData['kode_enrollment'] = null;
            return;
        }
        
        // If new code, check manually for uniqueness
        if ($this->kelasModel->where('kode_enrollment', $newKodeEnrollment)
                            ->where('kode_kelas !=', $kelasToUpdate['kode_kelas'])
                            ->first()) {
            throw new \Exception('Kode enrollment ini sudah digunakan oleh kelas lain.');
        }
        
        // If we got here, the new code is unique, so set it
        $updateData['kode_enrollment'] = $newKodeEnrollment;
    }

    private function prepareKelasUpdateData(): array
    {
        return [
            'nama_kelas'        => $this->request->getVar('nama_kelas'),
            'kode_matakuliah'   => $this->request->getVar('kode_matakuliah'),
            'hari'              => $this->request->getVar('hari'),
            'waktu_mulai_kelas' => $this->request->getVar('waktu_mulai_kelas'),
            'waktu_selesai_kelas' => $this->request->getVar('waktu_selesai_kelas'),
            'ruangan'           => $this->request->getVar('ruangan'),
            'tahun_ajaran'      => $this->request->getVar('tahun_ajaran'),
            'semester'          => $this->request->getVar('semester'),
        ];
    }

    /**
     * Handle exceptions in controller methods
     */
    private function handleException(\Exception $e, $db, string $kodeKelas, string $type, bool $wantsJson)
    {
        $db->transRollback();
        log_message('error', "[DosenController] $type saat update kelas: $kodeKelas - " . $e->getMessage());
        
        if ($wantsJson) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => "Terjadi kesalahan $type saat update."
            ]);
        }
        
        return redirect()->to(base_url('dosen/kelas/edit/' . $kodeKelas))
                        ->withInput()
                        ->with('error', 'Kesalahan tidak terduga saat update.');
    }

    private function errorResponse(string $redirectMessage, int $statusCode, string $jsonMessage, string $redirectUrl, bool $wantsJson, array $flashData = [])
    {
        if ($wantsJson) {
            return $this->response->setStatusCode($statusCode)->setJSON([
                'status' => 'error',
                'message' => $jsonMessage
            ]);
        }
        
        $redirect = redirect()->to(base_url($redirectUrl))->with('error', $redirectMessage);
        
        // Add additional flash data if provided
        foreach ($flashData as $key => $value) {
            $redirect->with($key, $value);
        }
        
        return $redirect;
    }

    /**
     * Memproses penghapusan kelas yang diajar oleh dosen.
     * @param string $kodeKelas Kode kelas yang akan dihapus
     */
    public function deleteKelas(string $kodeKelas)
    {
        // Filter 'dosenAuthFilter' seharusnya sudah memastikan user adalah dosen dan sudah login
        $dosenNipLogin = $this->session->get('reference_id');
        $wantsJson = $this->requestIsJson();

        if (empty($dosenNipLogin)) {
            // Logika error jika NIP tidak ada di sesi
            log_message('error', '[DosenController] NIP Dosen tidak ada di sesi saat deleteKelas.');
            if ($wantsJson) { /* ... response JSON 403 ... */ }
            return redirect()->to(base_url('/'))->with('error', 'Sesi Anda tidak valid.');
        }

        // 1. Cek apakah kelas dengan $kodeKelas tersebut ada
        $kelasToDelete = $this->kelasModel->find($kodeKelas);

        if (!$kelasToDelete) {
            log_message('error', "[DosenController] Delete: Kelas dengan kode $kodeKelas tidak ditemukan.");
            if ($wantsJson) { /* ... response JSON 404 ... */ }
            return redirect()->to(base_url('dosen/kelas'))->with('error', 'Kelas yang akan dihapus tidak ditemukan.');
        }

        // 2. VALIDASI KEPEMILIKAN KELAS
        if ($kelasToDelete['dosen_nip'] !== $dosenNipLogin) {
            log_message('warning', "[DosenController] Delete: Dosen $dosenNipLogin mencoba hapus kelas $kodeKelas yang bukan miliknya.");
            if ($wantsJson) { /* ... response JSON 403 ... */ }
            return redirect()->to(base_url('dosen/kelas'))->with('error', 'Anda tidak memiliki hak untuk menghapus kelas ini.');
        }

        // 3. PERTIMBANGAN SEBELUM DELETE: Cek data terkait
        // Cek apakah ada mahasiswa yang terdaftar di kelas ini
        $jumlahMahasiswaTerdaftar = $this->enrollmentModel->where('kode_kelas_enrolled', $kodeKelas)->countAllResults();
        
        // Cek apakah ada sesi absensi yang sudah dibuat untuk kelas ini
        $jumlahSesiAbsensi = $this->sesiAbsensiModel->where('kode_kelas', $kodeKelas)->countAllResults();

        if ($jumlahMahasiswaTerdaftar > 0 || $jumlahSesiAbsensi > 0) {
            $pesanError = 'Kelas tidak dapat dihapus karena sudah memiliki ';
            if ($jumlahMahasiswaTerdaftar > 0) {
                $pesanError .= $jumlahMahasiswaTerdaftar . ' mahasiswa terdaftar';
            }
            if ($jumlahMahasiswaTerdaftar > 0 && $jumlahSesiAbsensi > 0) {
                $pesanError .= ' dan ';
            }
            if ($jumlahSesiAbsensi > 0) {
                $pesanError .= $jumlahSesiAbsensi . ' sesi absensi';
            }
            $pesanError .= '. Harap hapus data terkait terlebih dahulu atau arsipkan kelas jika memungkinkan.';
            
            log_message('warning', "[DosenController] Delete: Gagal hapus kelas $kodeKelas karena ada data terkait. " . $pesanError);
            if ($wantsJson) {
                return $this->response->setStatusCode(409)->setJSON(['status' => 'error', 'message' => $pesanError]); // 409 Conflict
            }
            return redirect()->to(base_url('dosen/kelas/detail/' . $kodeKelas))->with('error', $pesanError);
        }

        // 4. Mulai Transaksi Database (Meskipun hanya satu operasi delete utama,
        // ini baik untuk jika ada operasi terkait lain di masa depan atau event)
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $deleted = $this->kelasModel->delete($kodeKelas);

            if ($deleted === false) { // delete() mengembalikan false jika gagal karena error atau tidak ada baris yang terpengaruh
                $db->transRollback();
                log_message('error', '[DosenController] Gagal delete dari kelasModel. Kode Kelas: '.$kodeKelas.'. Errors: ' . json_encode($this->kelasModel->errors()));
                $errorMessage = 'Gagal menghapus kelas.';
                if(!empty($this->kelasModel->errors())) {
                    $errorMessage .= ' Error: ' . implode(', ', $this->kelasModel->errors());
                }
                if ($wantsJson) { /* ... response JSON 400/500 ... */ }
                return redirect()->to(base_url('dosen/kelas'))->with('error', $errorMessage);
            }
            
            if ($db->transStatus() === false) {
                $db->transRollback();
                log_message('error', '[DosenController] Status transaksi database gagal setelah mencoba delete kelas: ' . $kodeKelas);
                if ($wantsJson) { /* ... response JSON 500 ... */ }
                return redirect()->to(base_url('dosen/kelas'))->with('error', 'Kesalahan database saat menghapus.');
            }

            $db->transCommit();
            log_message('info', '[DosenController] Kelas berhasil dihapus. Kode Kelas: ' . $kodeKelas);
            if ($wantsJson) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Kelas "' . esc($kelasToDelete['nama_kelas']) . '" berhasil dihapus.'
                ]);
            }
            return redirect()->to(base_url('dosen/kelas'))
                             ->with('success', 'Kelas "' . esc($kelasToDelete['nama_kelas']) . '" berhasil dihapus.');

        } catch (DatabaseException $e) {
            $db->transRollback();
            log_message('error', '[DosenController] DatabaseException saat delete kelas: ' . $kodeKelas . ' - ' . $e->getMessage());
            // ... (response error 500) ...
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', '[DosenController] General Exception saat delete kelas: ' . $kodeKelas . ' - ' . $e->getMessage());
            // ... (response error 500) ...
        }
    }

    // Helper method
    protected function requestIsJson(): bool
    {
        return $this->request->isAJAX() ||
               strpos($this->request->getHeaderLine('Accept'), 'application/json') !== false ||
               $this->request->getHeaderLine('Content-Type') === 'application/json';
    }
}