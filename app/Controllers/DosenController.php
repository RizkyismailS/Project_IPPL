<?php

namespace App\Controllers;

use App\Models\DosenModel;
use App\Models\KelasModel;
use App\Models\MataKuliahModel;
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

    // Helper method
    protected function requestIsJson(): bool
    {
        return $this->request->isAJAX() ||
               strpos($this->request->getHeaderLine('Accept'), 'application/json') !== false ||
               $this->request->getHeaderLine('Content-Type') === 'application/json';
    }
}