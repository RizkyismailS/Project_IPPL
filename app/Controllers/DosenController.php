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

    protected $dosenModel;
    protected $kelasModel;
    protected $mataKuliahModel;
    protected $enrollmentModel;
    protected $sesiAbsensiModel;
    protected $session;

    public function __construct()
    {
        $this->dosenModel = new DosenModel();
        $this->kelasModel = new KelasModel();
        $this->mataKuliahModel = new MataKuliahModel();
        $this->enrollmentModel = new EnrollmentModel();
        $this->sesiAbsensiModel = new SesiAbsensiModel();
        $this->session = \Config\Services::session();
        helper(['form', 'url']);
        // PENTING: Terapkan DosenAuthFilter melalui Routes
    }


    private function validateMatakuliah($kode_matakuliah)
    {
        return $this->mataKuliahModel->find($kode_matakuliah) !== null;
    }

    private function validateTimeFormat($time)
    {
        return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $time);
    }

    private function validateTimeSequence($start, $end)
    {
        return strtotime($end) > strtotime($start);
    }

    public function profile()
    {
        $dosenNip = $this->session->get('reference_id');

        // Get dosen data
        $dosenModel = new DosenModel();
        $dosen = $dosenModel->find($dosenNip);

        if (!$dosen) {
            return redirect()->to(base_url('dosen/dashboard'))
                ->with('error', 'Data profil dosen tidak ditemukan.');
        }

        // Get user account data
        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('reference_id', $dosenNip)
            ->where('role', 'dosen')
            ->first();

        $activityLogModel = new \App\Models\ActivityLogModel();
        $lastLoginActivity = $activityLogModel->where('user_id', $this->session->get('id_user'))
            ->where('action', 'login')
            ->orderBy('created_at', 'DESC')
            ->first();
        $lastLogin = $lastLoginActivity ? $lastLoginActivity['created_at'] : null;

        // Prepare data for view
        $data = [
            'title' => 'Profil Dosen',
            'dosen' => $dosen,
            'user' => $user,
            'last_login' => $lastLogin,
            'validation' => \Config\Services::validation(),
            'sidebar' => 'layout/dosen_sidebar',
            'nama_user' => $this->session->get('nama_lengkap') ?? $this->session->get('username'),
        ];

        return view('dosen/profil', $data);
    }

    public function updateProfile()
    {
        $dosenNip = $this->session->get('reference_id');
        $dosenModel = new DosenModel();

        // Validation rules
        $rules = [
            'nama' => [
                'label' => 'Nama Lengkap',
                'rules' => 'required|string|max_length[100]'
            ],
            'email' => [
                'label' => 'Email',
                'rules' => "required|valid_email|max_length[100]|is_unique[dosen.email,nip,{$dosenNip}]"
            ],
            'jabatan' => [
                'label' => 'Jabatan',
                'rules' => 'permit_empty|string|max_length[50]'
            ]
        ];

        // Run validation
        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator)
                ->with('error', 'Terdapat kesalahan pada form. Silakan periksa kembali.');
        }

        // Prepare data for update
        $updateData = [
            'nama' => $this->request->getPost('nama'),
            'email' => $this->request->getPost('email'),
            'jabatan' => $this->request->getPost('jabatan'),
        ];

        // Start transaction
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Update dosen data
            $updated = $dosenModel->update($dosenNip, $updateData);

            if (!$updated) {
                $db->transRollback();
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Gagal mengupdate profil. ' . implode(', ', $dosenModel->errors()));
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Terjadi kesalahan database saat mengupdate profil.');
            }

            // All good, commit transaction
            $db->transCommit();

            // Update session name if changed
            if ($this->session->get('nama_lengkap') !== $updateData['nama']) {
                $this->session->set('nama_lengkap', $updateData['nama']);
            }

            // Log activity
            $activityLogModel = new \App\Models\ActivityLogModel();
            $activityLogModel->logActivity(
                $this->session->get('id_user'),
                $dosenNip,
                'dosen',
                'update_profile',
                'Updated profile information',
                'dosen',
                $dosenNip
            );

            return redirect()->to(base_url('dosen/profile'))
                ->with('success', 'Profil Anda berhasil diupdate.');

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', '[DosenController] Exception saat update profile: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function changePassword()
    {
        $userModel = new \App\Models\UserModel();
        $userId = $this->session->get('id_user');
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->back()
                ->with('error', 'Akun pengguna tidak ditemukan.');
        }

        // Validation rules
        $rules = [
            'current_password' => [
                'label' => 'Password Saat Ini',
                'rules' => 'required'
            ],
            'new_password' => [
                'label' => 'Password Baru',
                'rules' => 'required|min_length[8]'
            ],
            'confirm_password' => [
                'label' => 'Konfirmasi Password',
                'rules' => 'required|matches[new_password]'
            ],
        ];

        // Run validation
        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator)
                ->with('error', 'Terdapat kesalahan pada form password. Silakan periksa kembali.');
        }

        // Verify current password
        $currentPassword = $this->request->getPost('current_password');
        if (!password_verify($currentPassword, $user['password'])) {
            return redirect()->back()
                ->with('error', 'Password saat ini tidak cocok.');
        }

        // Prepare data for update
        $updateData = [
            'password' => $this->request->getPost('new_password'),
            // Don't need to hash, UserModel will handle it via beforeUpdate callback
        ];

        // Start transaction
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Update password
            $updated = $userModel->update($userId, $updateData);

            if (!$updated) {
                $db->transRollback();
                return redirect()->back()
                    ->with('error', 'Gagal mengubah password. ' . implode(', ', $userModel->errors()));
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return redirect()->back()
                    ->with('error', 'Terjadi kesalahan database saat mengubah password.');
            }

            // All good, commit transaction
            $db->transCommit();

            // Log activity
            $activityLogModel = new \App\Models\ActivityLogModel();
            $activityLogModel->logActivity(
                $userId,
                $this->session->get('reference_id'),
                'dosen',
                'change_password',
                'Changed account password',
                'users',
                $userId
            );

            return redirect()->to(base_url('dosen/profile'))
                ->with('success', 'Password Anda berhasil diubah.');

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', '[DosenController] Exception saat mengubah password: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function dashboard()
    {
        $dosenNip = $this->session->get('reference_id');
        $db = \Config\Database::connect();

        // Get basic statistics
        $totalKelas = $this->kelasModel->where('dosen_nip', $dosenNip)->countAllResults();

        // Get total students across all classes
        $totalMahasiswaQuery = $db->query("
            SELECT COUNT(DISTINCT e.nim_mahasiswa) as total 
            FROM enrollment e 
            JOIN kelas k ON e.kode_kelas_enrolled = k.kode_kelas 
            WHERE k.dosen_nip = ? AND e.status_enrollment = 'aktif'",
            [$dosenNip]
        );
        $totalMahasiswa = $totalMahasiswaQuery->getRow()->total ?? 0;

        // Get total absensi sessions
        $totalAbsensi = $this->sesiAbsensiModel
            ->join('kelas', 'kelas.kode_kelas = sesi_absensi.kode_kelas')
            ->where('kelas.dosen_nip', $dosenNip)
            ->countAllResults();

        // Get weekly attendance data (last 5 weeks)
        $weeklyAbsensiData = $db->query("
            SELECT 
                WEEK(sa.tanggal_sesi) as week_number,
                COUNT(DISTINCT sa.id_sesi) as total_sesi,
                SUM(CASE WHEN k.status_absen = 'hadir' THEN 1 ELSE 0 END) as total_hadir,
                SUM(CASE WHEN k.status_absen = 'tidak_hadir' THEN 1 ELSE 0 END) as total_absen,
                SUM(CASE WHEN k.status_absen = 'izin' THEN 1 ELSE 0 END) as total_izin
            FROM sesi_absensi sa
            JOIN kelas kl ON sa.kode_kelas = kl.kode_kelas
            LEFT JOIN kehadiran k ON sa.id_sesi = k.id_sesi
            WHERE kl.dosen_nip = ? 
            AND sa.tanggal_sesi >= DATE_SUB(CURRENT_DATE(), INTERVAL 5 WEEK)
            GROUP BY WEEK(sa.tanggal_sesi)
            ORDER BY week_number ASC",
            [$dosenNip]
        )->getResultArray();

        // Get class-specific attendance data
        $kelasAbsensiData = $db->query("
            SELECT 
                kl.kode_kelas,
                kl.nama_kelas,
                mk.nama_matakuliah,
                COUNT(DISTINCT sa.id_sesi) as total_sesi,
                MAX(sa.tanggal_sesi) as last_session,
                SUM(CASE WHEN k.status_absen = 'hadir' THEN 1 ELSE 0 END) as total_hadir,
                COUNT(DISTINCT e.nim_mahasiswa) - COUNT(DISTINCT CASE WHEN k.status_absen IS NOT NULL THEN k.nim END) as belum_absen
            FROM kelas kl
            JOIN matakuliah mk ON kl.kode_matakuliah = mk.kode_matakuliah
            LEFT JOIN sesi_absensi sa ON kl.kode_kelas = sa.kode_kelas
            LEFT JOIN enrollment e ON kl.kode_kelas = e.kode_kelas_enrolled AND e.status_enrollment = 'aktif'
            LEFT JOIN kehadiran k ON sa.id_sesi = k.id_sesi
            WHERE kl.dosen_nip = ?
            GROUP BY kl.kode_kelas, kl.nama_kelas, mk.nama_matakuliah
            ORDER BY last_session DESC
            LIMIT 5",
            [$dosenNip]
        )->getResultArray();

        // Get overall attendance status distribution
        $attendanceDistribution = $db->query("
            SELECT 
                k.status_absen,
                COUNT(*) as total
            FROM kehadiran k
            JOIN sesi_absensi sa ON k.id_sesi = sa.id_sesi
            JOIN kelas kl ON sa.kode_kelas = kl.kode_kelas
            WHERE kl.dosen_nip = ?
            GROUP BY k.status_absen",
            [$dosenNip]
        )->getResultArray();

        // Format data for charts
        $weekLabels = [];
        $hadirData = [];
        $absenData = [];
        $izinData = [];

        foreach ($weeklyAbsensiData as $week) {
            $weekLabels[] = 'Minggu ' . $week['week_number'];
            $hadirData[] = (int) $week['total_hadir'];
            $absenData[] = (int) $week['total_absen'];
            $izinData[] = (int) $week['total_izin'];
        }

        // Default values if no data
        if (empty($weekLabels)) {
            $weekLabels = ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4', 'Minggu 5'];
            $hadirData = [0, 0, 0, 0, 0];
            $absenData = [0, 0, 0, 0, 0];
            $izinData = [0, 0, 0, 0, 0];
        }

        // Pie chart data
        $pieLabels = [];
        $pieValues = [];

        foreach ($attendanceDistribution as $dist) {
            $status = match ($dist['status_absen']) {
                'hadir' => 'Hadir',
                'tidak_hadir' => 'Tidak Hadir',
                'izin' => 'Izin',
                default => ucfirst($dist['status_absen'])
            };

            $pieLabels[] = $status;
            $pieValues[] = (int) $dist['total'];
        }

        // Default values if no data
        if (empty($pieLabels)) {
            $pieLabels = ['Hadir', 'Tidak Hadir', 'Izin'];
            $pieValues = [0, 0, 0];
        }

        return view('dosen/dashboard', [
            'title' => 'Dashboard Dosen',
            'sidebar' => 'layout/dosen_sidebar',
            'nama_user' => $this->session->get('nama_lengkap') ?? $this->session->get('username'),
            // Statistics
            'totalKelas' => $totalKelas,
            'totalMahasiswa' => $totalMahasiswa,
            'totalAbsensi' => $totalAbsensi,
            // Chart data
            'weekLabels' => json_encode($weekLabels),
            'hadirData' => json_encode($hadirData),
            'absenData' => json_encode($absenData),
            'izinData' => json_encode($izinData),
            'pieLabels' => json_encode($pieLabels),
            'pieValues' => json_encode($pieValues),
            // Table data
            'kelasAbsensiData' => $kelasAbsensiData
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

    /**
     * Menampilkan daftar sesi absensi untuk sebuah kelas yang spesifik.
     *
     * @param string $kode_kelas Kode unik dari kelas yang didapat dari URL.
     */
    public function listSesi(string $kode_kelas)
    {
        // Inisialisasi model yang kita butuhkan
        $sesiAbsensiModel = new \App\Models\SesiAbsensiModel();
        $kelasModel = new \App\Models\KelasModel();

        // --- Langkah Keamanan (Penting) ---
        // 1. Dapatkan NIP dosen yang sedang login dari session
        $nip_dosen = session()->get('reference_id');

        // 2. Ambil data kelas dari database berdasarkan kode_kelas dari URL
        $kelas = $kelasModel->find($kode_kelas);

        // 3. Pastikan kelas ada dan dosen yang login adalah pemilik kelas tersebut
        if (!$kelas || $kelas['dosen_nip'] != $nip_dosen) {
            // Jika tidak, kembalikan ke halaman sebelumnya dengan pesan error
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke kelas ini.');
        }

        // Ambil semua sesi absensi yang berelasi dengan kode_kelas ini
        // Urutkan dari yang terbaru berdasarkan tanggal sesi
        $sesi_data = $sesiAbsensiModel
            ->where('kode_kelas', $kode_kelas)
            ->orderBy('tanggal_sesi', 'DESC')
            ->findAll();

        // Siapkan data untuk dikirim ke view
        $data = [
            'title' => 'List Sesi Absensi',
            'Absensi' => $sesi_data, // Nama variabel disesuaikan dengan view Anda ($Absensi)
            'kelas' => $kelas,     // Kirim juga data kelas untuk info tambahan jika perlu
        ];

        // Muat view 'dosen/listAbsensi' dan kirimkan datanya
        return view('dosen/listAbsensi', $data);
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
        if (
            $this->validateTimeFormat($waktu_mulai) && $this->validateTimeFormat($waktu_selesai) &&
            !$this->validateTimeSequence($waktu_mulai, $waktu_selesai)
        ) {
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
            'kode_kelas' => $this->request->getVar('kode_kelas'),
            'nama_kelas' => $this->request->getVar('nama_kelas'),
            'kode_matakuliah' => $this->request->getVar('kode_matakuliah'),
            'dosen_nip' => $dosenNipLogin,
            'hari' => $this->request->getVar('hari'),
            'waktu_mulai_kelas' => $this->request->getVar('waktu_mulai_kelas'),
            'waktu_selesai_kelas' => $this->request->getVar('waktu_selesai_kelas'),
            'ruangan' => $this->request->getVar('ruangan'),
            'tahun_ajaran' => $this->request->getVar('tahun_ajaran'),
            'semester' => $this->request->getVar('semester'),
            'kode_enrollment' => $this->request->getVar('kode_enrollment'),
        ];

        if (empty($kelasData['kode_enrollment'])) {
            $isUnique = false;
            while (!$isUnique) {
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
                if ($wantsJson) { /* ... */
                }
                return redirect()->to(base_url('dosen/kelas/create'))
                    ->withInput()
                    ->with('errors_kelas', $this->kelasModel->errors())
                    ->with('error', 'Gagal menyimpan data kelas. Periksa error model.');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                log_message('error', '[DosenController] Status transaksi DB gagal post-insert kelas.');
                if ($wantsJson) { /* ... */
                }
                return redirect()->to(base_url('dosen/kelas/create'))->withInput()->with('error', 'Kesalahan database saat menyimpan.');
            }

            $db->transCommit();
            log_message('info', '[DosenController] Kelas baru berhasil dibuat. Kode Kelas: ' . $kelasData['kode_kelas'] . '. Kode Enrollment: ' . $kelasData['kode_enrollment']);
            if ($wantsJson) { /* ... */
            }
            // Log activity
            $activityLogModel = new \App\Models\ActivityLogModel();
            $kelasId = $this->kelasModel->getInsertID();
            $activityLogModel->logActivity(
                session()->get('id_user'),
                $dosenNipLogin,
                'dosen',
                'create_class',
                'Created new class: ' . $kelasData['nama_kelas'],
                'kelas',
                $kelasData['kode_kelas']
            );
            return redirect()->to(base_url('dosen/kelas'))
                ->with('success', 'Kelas "' . esc($kelasData['nama_kelas']) . '" dengan kode enrollment "' . esc($kelasData['kode_enrollment']) . '" berhasil ditambahkan.');

        } catch (DatabaseException $e) {
            $db->transRollback();
            log_message('error', '[DosenController] DatabaseException: ' . $e->getMessage());
            if ($wantsJson) { /* ... */
            }
            return redirect()->to(base_url('dosen/kelas/create'))->withInput()->with('error', 'Kesalahan database.');
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', '[DosenController] General Exception: ' . $e->getMessage());
            if ($wantsJson) { /* ... */
            }
            return redirect()->to(base_url('dosen/kelas/create'))->withInput()->with('error', 'Kesalahan tidak terduga.');
        }
    }

    /**
     * Updates session statuses based on current time.
     * This should be called regularly via cron job or before displaying sessions.
     */
    public function updateSessionStatuses()
    {
        $now = date('Y-m-d H:i:s');

        // Update sessions that have passed their end time to 'selesai'
        $this->sesiAbsensiModel->where('status', 'aktif')
            ->where('waktu_selesai_aktual <', $now)
            ->set(['status' => 'selesai'])
            ->update();

        // Update sessions that have passed their scheduled time but weren't activated to 'terlewat'
        // The NOT EXISTS subquery needs to be properly formatted
        $subquery = $this->sesiAbsensiModel->db->table('kehadiran')
            ->select('1')
            ->where('kehadiran.id_sesi = sesi_absensi.id_sesi');

        $this->sesiAbsensiModel->where('status', 'aktif')
            ->where('waktu_mulai_aktual <', $now)
            ->where('waktu_selesai_aktual <', $now)
            ->where("NOT EXISTS ({$subquery->getCompiledSelect()})", null, false)
            ->set(['status' => 'terlewat'])
            ->update();

            return redirect()->to(base_url('admin/dashboard'))
            ->with('success', "Status sesi berhasil diperbarui.");
    }


    /**
     * Menampilkan detail kelas yang diajar oleh dosen.
     * Dosen hanya bisa melihat detail kelas yang memang dia ampu.
     * @param string $kodeKelas
     */
    public function detailKelas(string $kodeKelas)
    {
        $this->updateSessionStatuses();
        // --- Langkah Keamanan ---
        $nip_dosen = $this->session->get('reference_id');

        // Gunakan getKelasDetail untuk mendapatkan data lengkap dengan join ke mata kuliah dan dosen
        $kelas = $this->kelasModel->getKelasDetail($kodeKelas);

        if (!$kelas || $kelas['dosen_nip'] != $nip_dosen) {
            return redirect()->to('/dosen/kelas')->with('error', 'Anda tidak memiliki akses ke kelas ini.');
        }

        // --- Mempersiapkan Data Sesi ---
        // 1. Ambil data sesi mentah dari database
        $sesi_absensi_raw = $this->sesiAbsensiModel->where('kode_kelas', $kodeKelas)->orderBy('tanggal_sesi', 'DESC')->findAll();

        // 2. Proses data sesi untuk menambahkan status yang akan ditampilkan di view
        $sesi_absensi_processed = [];
        $waktu_sekarang = time(); // Mengambil waktu sekarang

        helper('session_status');

        foreach ($sesi_absensi_raw as $sesi) {
            $sesi['status_tampil'] = calculate_session_status($sesi, null, 'dosen');
            $sesi_absensi_processed[] = $sesi;
        }

        // Get mahasiswa data from enrollment
        $mahasiswa_terdaftar = $this->enrollmentModel->getMahasiswaByKelas($kodeKelas);
        $jumlah_mahasiswa_terdaftar = count($mahasiswa_terdaftar);

        // Siapkan semua data untuk dikirim ke view
        $data = [
            'title' => 'Detail Kelas',
            'kelas' => $kelas,
            'mahasiswa_terdaftar' => $mahasiswa_terdaftar,
            'jumlah_mahasiswa_terdaftar' => $jumlah_mahasiswa_terdaftar,
            'sesi_absensi' => $sesi_absensi_processed, // Mengirim data sesi yang sudah diproses
        ];

        return view('dosen/detailKelas', $data);
    }

    /**
     * Mengelola status pendaftaran mahasiswa (Aktifkan / Nonaktifkan).
     */
    public function manageEnrollment()
    {
        $enrollmentId = $this->request->getPost('id_enrollment');
        $action = $this->request->getPost('action');
        $dosenNipLogin = $this->session->get('reference_id');

        // Validate inputs
        if (empty($enrollmentId) || empty($action)) {
            log_message('error', '[DosenController] manageEnrollment: Missing required parameters');
            return redirect()->back()->with('error', 'Parameter tidak lengkap.');
        }

        // Check valid actions
        if (!in_array($action, ['activate', 'deactivate'])) {
            log_message('error', '[DosenController] manageEnrollment: Invalid action: ' . $action);
            return redirect()->back()->with('error', 'Aksi tidak valid.');
        }

        $enrollmentModel = new EnrollmentModel();

        // Get enrollment data
        $enrollment = $enrollmentModel->find($enrollmentId);
        if (!$enrollment) {
            log_message('error', '[DosenController] Enrollment data not found for ID: ' . $enrollmentId);
            return redirect()->back()->with('error', 'Data pendaftaran tidak ditemukan.');
        }

        // Debug enrollment data
        log_message('debug', '[DosenController] Enrollment data: ' . json_encode($enrollment));

        // Check if kelas exists and belongs to the current dosen
        $kelas = $this->kelasModel->find($enrollment['kode_kelas_enrolled']);
        if (!$kelas) {
            log_message('error', '[DosenController] Class not found: ' . $enrollment['kode_kelas_enrolled']);
            return redirect()->back()->with('error', 'Data kelas tidak ditemukan.');
        }

        // Check if dosen owns this class
        if ($kelas['dosen_nip'] !== $dosenNipLogin) {
            log_message('warning', "[DosenController] Unauthorized access: Dosen $dosenNipLogin tried to manage enrollment for class owned by {$kelas['dosen_nip']}");
            return redirect()->back()->with('error', 'Anda tidak memiliki hak untuk melakukan aksi ini.');
        }

        // Prepare update data based on action
        $newStatus = ($action === 'activate') ? 'aktif' : 'dinonaktifkan';

        // Use transaction for better data integrity
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Update the enrollment status and check result
            $updated = $enrollmentModel->update($enrollmentId, ['status_enrollment' => $newStatus]);

            if ($updated === false) {
                $db->transRollback();
                log_message('error', "[DosenController] Failed to update enrollment status: " . json_encode($enrollmentModel->errors()));
                return redirect()->back()->with('error', 'Gagal mengubah status pendaftaran.');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                log_message('error', '[DosenController] Database transaction status failed during enrollment update');
                return redirect()->back()->with('error', 'Kesalahan database saat memperbarui status.');
            }

            $db->transCommit();
            log_message('debug', "[DosenController] Enrollment ID: $enrollmentId successfully updated to status: $newStatus");

            $message = ($action === 'activate') ?
                'Status mahasiswa berhasil diaktifkan kembali.' :
                'Status mahasiswa berhasil dinonaktifkan.';

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', '[DosenController] Exception during enrollment update: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui status.');
        }
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
            if (
                $this->validateTimeFormat($waktu_mulai) && $this->validateTimeFormat($waktu_selesai) &&
                !$this->validateTimeSequence($waktu_mulai, $waktu_selesai)
            ) {
                $errors['waktu_selesai_kelas'] = 'Waktu Selesai harus setelah Waktu Mulai.';
                $validationFailed = true;
            }

            if ($validationFailed || count($errors) > 0) {
                log_message('error', '[DosenController] Validasi update kelas gagal. Kode Kelas: ' . $kodeKelas . '. Errors: ' . json_encode($errors));

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
                log_message('error', '[DosenController] Gagal update ke kelasModel. Kode Kelas: ' . $kodeKelas . '. Errors: ' . json_encode($this->kelasModel->errors()));

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
            'nama_kelas' => ['label' => 'Nama Kelas', 'rules' => 'required|string|max_length[100]'],
            'kode_matakuliah' => ['label' => 'Mata Kuliah', 'rules' => 'required'],
            'hari' => ['label' => 'Hari', 'rules' => 'required|in_list[Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu]'],
            'waktu_mulai_kelas' => ['label' => 'Waktu Mulai', 'rules' => 'required'],
            'waktu_selesai_kelas' => ['label' => 'Waktu Selesai', 'rules' => 'required'],
            'ruangan' => ['label' => 'Ruangan', 'rules' => 'permit_empty|string|max_length[50]'],
            'tahun_ajaran' => ['label' => 'Tahun Ajaran', 'rules' => 'required|string|max_length[10]'],
            'semester' => ['label' => 'Semester', 'rules' => 'required|string|max_length[20]'],
            'kode_enrollment' => [
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
        if (
            $this->kelasModel->where('kode_enrollment', $newKodeEnrollment)
                ->where('kode_kelas !=', $kelasToUpdate['kode_kelas'])
                ->first()
        ) {
            throw new \Exception('Kode enrollment ini sudah digunakan oleh kelas lain.');
        }

        // If we got here, the new code is unique, so set it
        $updateData['kode_enrollment'] = $newKodeEnrollment;
    }

    private function prepareKelasUpdateData(): array
    {
        return [
            'nama_kelas' => $this->request->getVar('nama_kelas'),
            'kode_matakuliah' => $this->request->getVar('kode_matakuliah'),
            'hari' => $this->request->getVar('hari'),
            'waktu_mulai_kelas' => $this->request->getVar('waktu_mulai_kelas'),
            'waktu_selesai_kelas' => $this->request->getVar('waktu_selesai_kelas'),
            'ruangan' => $this->request->getVar('ruangan'),
            'tahun_ajaran' => $this->request->getVar('tahun_ajaran'),
            'semester' => $this->request->getVar('semester'),
        ];
    }

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
            if ($wantsJson) { /* ... response JSON 403 ... */
            }
            return redirect()->to(base_url('/'))->with('error', 'Sesi Anda tidak valid.');
        }

        // 1. Cek apakah kelas dengan $kodeKelas tersebut ada
        $kelasToDelete = $this->kelasModel->find($kodeKelas);

        if (!$kelasToDelete) {
            log_message('error', "[DosenController] Delete: Kelas dengan kode $kodeKelas tidak ditemukan.");
            if ($wantsJson) { /* ... response JSON 404 ... */
            }
            return redirect()->to(base_url('dosen/kelas'))->with('error', 'Kelas yang akan dihapus tidak ditemukan.');
        }

        // 2. VALIDASI KEPEMILIKAN KELAS
        if ($kelasToDelete['dosen_nip'] !== $dosenNipLogin) {
            log_message('warning', "[DosenController] Delete: Dosen $dosenNipLogin mencoba hapus kelas $kodeKelas yang bukan miliknya.");
            if ($wantsJson) { /* ... response JSON 403 ... */
            }
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
                log_message('error', '[DosenController] Gagal delete dari kelasModel. Kode Kelas: ' . $kodeKelas . '. Errors: ' . json_encode($this->kelasModel->errors()));
                $errorMessage = 'Gagal menghapus kelas.';
                if (!empty($this->kelasModel->errors())) {
                    $errorMessage .= ' Error: ' . implode(', ', $this->kelasModel->errors());
                }
                if ($wantsJson) { /* ... response JSON 400/500 ... */
                }
                return redirect()->to(base_url('dosen/kelas'))->with('error', $errorMessage);
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                log_message('error', '[DosenController] Status transaksi database gagal setelah mencoba delete kelas: ' . $kodeKelas);
                if ($wantsJson) { /* ... response JSON 500 ... */
                }
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

    public function laporanSesi($id_sesi)
    {
        // Inisialisasi model yang dibutuhkan
        $sesiAbsensiModel = new \App\Models\SesiAbsensiModel();

        // Ambil info detail sesi untuk ditampilkan di judul halaman
        $sesi = $sesiAbsensiModel->find($id_sesi);

        // Pengaman: Jika sesi tidak ditemukan, tampilkan error
        if (!$sesi) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Ambil data laporan dari metode yang baru kita buat
        $laporan = $sesiAbsensiModel->getLaporanKehadiran($id_sesi);

        // Kirim data sesi dan data laporan ke view
        $data = [
            'title' => 'Laporan Kehadiran',
            'sesi' => $sesi,
            'laporan' => $laporan
        ];

        // Memuat view baru yang akan kita buat selanjutnya
        return view('dosen/laporan_sesi', $data);
    }
}