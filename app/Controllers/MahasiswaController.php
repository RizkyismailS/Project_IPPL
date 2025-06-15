<?php

namespace App\Controllers;

use App\Models\MahasiswaModel; // Kita sudah punya ini
use App\Models\EnrollmentModel; // Model untuk enrollment
use App\Models\KelasModel; // Model untuk kelas
use App\Libraries\RuleExist; // Pastikan RuleExist sudah ada
use App\Models\SesiAbsensiModel; // Model untuk sesi absensi
use App\Models\KehadiranModel; // Model untuk kehadiran
// Mungkin perlu model lain seperti EnrollmentModel, KelasModel, SesiAbsensiModel, KehadiranModel

class MahasiswaController extends BaseController
{
    protected $mahasiswaModel;
    protected $session;
    protected $enrollmentModel;
    protected $kelasModel; // Tambahkan model kelas jika diperlukan
    protected $sesiAbsensiModel;
    protected $kehadiranModel; // Model untuk kehadiran

    public function __construct()
    {
        $this->mahasiswaModel = new MahasiswaModel();
        $this->enrollmentModel = new EnrollmentModel();
        $this->kelasModel = new KelasModel(); // Pastikan model kelas sudah ada
        $this->sesiAbsensiModel = new SesiAbsensiModel(); // Model untuk sesi absensi
        $this->kehadiranModel = new KehadiranModel(); // Model untuk kehadiran
        $this->session = \Config\Services::session();
        helper(['url']);

        // Filter untuk mahasiswa
        // if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'mahasiswa') {
        //     // Redirect atau tampilkan error (lebih baik via Filter)
        //     echo 'Akses ditolak untuk mahasiswa!';
        //     exit;
        // }
    }

    public function dashboard()
    {
        // Inisialisasi semua model
        $mahasiswaModel = new MahasiswaModel();
        $sesiAbsensiModel = new SesiAbsensiModel();
        $kehadiranModel = new KehadiranModel();
        
        // Ambil NIM dari session
        $nim_mahasiswa = session()->get('reference_id');

        if (!$nim_mahasiswa) {
            return redirect()->to('/login')->with('error', 'Sesi Anda telah berakhir, silakan login kembali.');
        }

        // Ambil data profil mahasiswa menggunakan find() karena nim adalah Primary Key
        $mahasiswa = $mahasiswaModel->find($nim_mahasiswa);

        if (!$mahasiswa) {
            return redirect()->to('/logout')->with('error', 'Profil mahasiswa tidak ditemukan.');
        }

        // --- Mengumpulkan Semua Data untuk Dasbor ---
        $activeSession = $sesiAbsensiModel->findActiveSessionForMahasiswa($nim_mahasiswa);
        $stats = $kehadiranModel->getAttendanceStats($nim_mahasiswa);
        $history = $kehadiranModel->getAttendanceHistory($nim_mahasiswa, 5);

        // Kumpulkan semua data untuk dikirim ke view
        $data = [
            'title'         => 'Dashboard Mahasiswa',
            'mahasiswa'     => $mahasiswa,
            'activeSession' => $activeSession,
            'stats'         => $stats,
            'history'       => $history,
        ];

        return view('mahasiswa/dashboard', $data);
    }

    // Contoh: Menampilkan profil mahasiswa yang sedang login
    public function profile()
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'mahasiswa') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Akses ditolak']);
        }
        $nimMahasiswa = $this->session->get('reference_id');
        $mahasiswa = $this->mahasiswaModel->getMahasiswaWithUser($nimMahasiswa); // Fungsi dari MahasiswaModel

        if ($mahasiswa) {
            return $this->response->setJSON($mahasiswa);
        } else {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Profil mahasiswa tidak ditemukan.']);
        }
    }

    public function enrollForm()
    {
        $nim = $this->session->get('reference_id');
        $mahasiswaData = $this->mahasiswaModel->find($nim);

        $data['title'] = "Enroll in a New Class";
        // Get user's full name from session, fallback to mahasiswa data, fallback to generic
        $data['nama_user'] = $this->session->get('nama_lengkap') ?? ($mahasiswaData['nama'] ?? 'Student');
        // Get user's email from mahasiswa data
        $data['email_user'] = $mahasiswaData['email'] ?? ''; 
        $data['errors'] = session()->getFlashdata('errors');

        return view('mahasiswa/enroll', $data);
    }

    public function processEnrollment()
    {
        $nimMahasiswa = $this->session->get('reference_id');
        if (empty($nimMahasiswa)) {
            log_message('error', '[MahasiswaController] NIM not found in session during enrollment process.');
            return redirect()->to(base_url('login'))->with('error', 'Your session is invalid. Please log in again.');
        }

        $validation = $this->validate([
            'kode_enrollment' => [
                'label'  => 'Enrollment Code',
                'rules'  => 'required|alpha_numeric|max_length[20]',
                'errors' => [
                    'required' => '{field} is required.',
                ],
            ],
        ]);

        if (!$validation) {
            return redirect()->to(base_url('mahasiswa/enroll'))
                             ->withInput()
                             ->with('errors', $this->validator->getErrors());
        }

        $kodeEnrollment = $this->request->getVar('kode_enrollment');

        $kelas = $this->kelasModel->findByKodeEnrollment($kodeEnrollment);

        if (!$kelas) {
            return redirect()->to(base_url('mahasiswa/enroll'))
                             ->withInput()
                             ->with('error', 'Invalid Enrollment Code. The class was not found.');
        }

        $kodeKelas = $kelas['kode_kelas'];

        if ($this->enrollmentModel->isEnrolled($nimMahasiswa, $kodeKelas)) {
            return redirect()->to(base_url('mahasiswa/enroll'))
                             ->with('warning', 'You are already enrolled in class "' . esc($kelas['nama_kelas']) . '".');
        }

        $enrollmentData = [
            'nim_mahasiswa'       => $nimMahasiswa,
            'kode_kelas_enrolled' => $kodeKelas,
            'status_enrollment'   => 'aktif', 
        ];

        try {
            if ($this->enrollmentModel->insert($enrollmentData)) {
                log_message('info', "Student NIM '{$nimMahasiswa}' successfully enrolled in class '{$kodeKelas}'.");
                return redirect()->to(base_url('mahasiswa/dashboard')) 
                                 ->with('success', 'You have successfully enrolled in class "' . esc($kelas['nama_kelas']) . '"!');
            } else {
                log_message('error', '[MahasiswaController] EnrollmentModel insert failed. Errors: ' . json_encode($this->enrollmentModel->errors()));
                return redirect()->to(base_url('mahasiswa/enroll'))
                                 ->withInput()
                                 ->with('error', 'Failed to enroll in the class due to a validation error.');
            }
        } catch (DatabaseException $e) {
            log_message('error', '[MahasiswaController] DatabaseException during enrollment: ' . $e->getMessage());
            return redirect()->to(base_url('mahasiswa/enroll'))
                             ->withInput()
                             ->with('error', 'A database error occurred. Please try again later.');
        }
    }

    /**
     * Memproses data saat mahasiswa menekan tombol absen.
     */
    public function submitAbsensi()
    {
        // 1. Inisialisasi Model dan Helper yang dibutuhkan
        $kehadiranModel = new \App\Models\KehadiranModel();
        helper('date'); // Memuat helper untuk fungsi now()

        // 2. Validasi input dari form
        $id_sesi = $this->request->getPost('id_sesi');
        if (empty($id_sesi)) {
            // Jika tidak ada id_sesi, kembalikan dengan pesan error
            return redirect()->back()->with('error', 'Sesi absensi tidak valid atau tidak ditemukan.');
        }

        // 3. Ambil data NIM mahasiswa dari session
        $nim = session()->get('reference_id');
        if (empty($nim)) {
            // Jika tidak ada nim di session, paksa login ulang
            return redirect()->to('/login')->with('error', 'Sesi Anda tidak valid, silakan login kembali.');
        }

        // 4. Langkah Keamanan: Cek apakah mahasiswa sudah pernah absen untuk sesi ini
        $cekSudahAbsen = $kehadiranModel->where('nim', $nim)
                                       ->where('id_sesi', $id_sesi)
                                       ->first();

        if ($cekSudahAbsen) {
            // Jika data sudah ada, jangan proses dan beri pesan
            return redirect()->to('/mahasiswa/dashboard')->with('error', 'Anda sudah melakukan absensi untuk sesi ini.');
        }

        // 5. Siapkan data untuk dimasukkan ke tabel `kehadiran`
        // Pastikan nama kolom sesuai dengan skema database Anda
        $dataToSave = [
            'nim'           => $nim,
            'id_sesi'       => $id_sesi,
            'status_absen'  => 'hadir', // Saat mahasiswa klik tombol, statusnya otomatis 'hadir'
            'waktu_absen'   => date('Y-m-d H:i:s'), // Mengambil waktu saat ini (WIB)
        ];

        // 6. Simpan data dan berikan feedback
        if ($kehadiranModel->save($dataToSave)) {
            // Jika penyimpanan berhasil
            return redirect()->to('/mahasiswa/dashboard')->with('success', 'Kehadiran Anda berhasil dicatat!');
        } else {
            // Jika penyimpanan gagal karena alasan lain
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan halaman "Kelas Saya" yang berisi daftar
     * semua kelas yang telah di-enroll oleh mahasiswa.
     */
    public function listKelas()
    {
        // Inisialisasi model
        $enrollmentModel = new \App\Models\EnrollmentModel();

        // Ambil NIM dari session
        $nim = session()->get('reference_id');

        // Ambil data kelas dari metode yang baru kita buat di EnrollmentModel
        $enrolledClasses = $enrollmentModel->getEnrolledClassesByNim($nim);

        $data = [
            'title' => 'Kelas Saya',
            'kelas' => $enrolledClasses,
        ];

        // Memuat view baru yang akan kita buat sekarang
        return view('mahasiswa/list_kelas', $data);
    }

    /**
     * Menampilkan daftar sesi untuk satu kelas spesifik.
     *
     * @param string $kode_kelas Kode kelas dari URL.
     */
    public function listSesi(string $kode_kelas)
    {
        // Inisialisasi model
        $sesiAbsensiModel = new \App\Models\SesiAbsensiModel();
        $kelasModel = new \App\Models\KelasModel();
        $enrollmentModel = new \App\Models\EnrollmentModel();
        helper('date'); // Memuat Date Helper
        
        $nim = session()->get('reference_id');

        // Keamanan: Pastikan mahasiswa terdaftar di kelas ini
        $isEnrolled = $enrollmentModel
            ->where('nim_mahasiswa', $nim)
            ->where('kode_kelas_enrolled', $kode_kelas)
            ->where('status_enrollment', 'aktif')
            ->first();

        if (!$isEnrolled) {
            return redirect()->to('/mahasiswa/kelas')->with('error', 'Anda tidak memiliki akses ke kelas ini.');
        }

        $kelas = $kelasModel->find($kode_kelas);
        
        // Ambil daftar sesi mentah dari model
        $sesi_list_raw = $sesiAbsensiModel->getSesiWithStatusForMahasiswa($kode_kelas, $nim);

        // ================== LOGIKA BARU DI SINI ==================
        // Proses daftar sesi untuk menentukan status final yang akan ditampilkan
        $sesi_list_processed = [];
        $waktu_sekarang = now('Asia/Jakarta'); // Ambil waktu WIB saat ini

        usort($sesi_list_processed, function($a, $b) {
            return strtotime($a['waktu_mulai_aktual']) <=> strtotime($b['waktu_mulai_aktual']);
        });
        helper('session_status');

        // In your listSesi method, replace the status calculation with:
        foreach ($sesi_list_raw as $sesi) {
            $sesi['status_final'] = calculate_session_status(
                [
                    'status' => $sesi['status_sesi'],
                    'waktu_mulai_aktual' => $sesi['waktu_mulai_aktual'],
                    'waktu_selesai_aktual' => $sesi['waktu_selesai_aktual']
                ],
                $sesi['status_absen'],
                'mahasiswa'
            );
            
            $sesi_list_processed[] = $sesi;
        }

        $data = [
            'title'     => 'Daftar Sesi',
            'kelas'     => $kelas,
            'sesi_list' => $sesi_list_processed // Kirim data yang sudah diproses
        ];

        return view('mahasiswa/list_sesi', $data);
    }
}