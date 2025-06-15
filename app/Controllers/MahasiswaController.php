<?php

namespace App\Controllers;

use App\Models\MahasiswaModel; // Kita sudah punya ini
use App\Models\EnrollmentModel; // Model untuk enrollment
use App\Models\KelasModel; // Model untuk kelas
use App\Libraries\RuleExist; // Pastikan RuleExist sudah ada
// Mungkin perlu model lain seperti EnrollmentModel, KelasModel, SesiAbsensiModel, KehadiranModel

class MahasiswaController extends BaseController
{
    protected $mahasiswaModel;
    protected $session;
    protected $enrollmentModel;
    protected $kelasModel; // Tambahkan model kelas jika diperlukan

    public function __construct()
    {
        $this->mahasiswaModel = new MahasiswaModel();
        $this->enrollmentModel = new EnrollmentModel();
        $this->kelasModel = new KelasModel(); // Pastikan model kelas sudah ada
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
        log_message('critical', 'MAHASISWA_CONTROLLER: Masuk MahasiswaController::dashboard. Sesi: ' . json_encode($this->session->get()));
        // Cek sesi mahasiswa
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'mahasiswa') {
            return redirect()->to(base_url('/'))->with('error', 'Silakan login terlebih dahulu');
        }
        $nimMahasiswa = $this->session->get('reference_id');
        $mahasiswaInfo = $this->mahasiswaModel->find($nimMahasiswa);

        $data['title'] = 'Mahasiswa Dashboard';
        $data['mahasiswa'] = $mahasiswaInfo;
        $data['nama_user'] = $this->session->get('nama_lengkap');


        return view('mahasiswa/dashboard', $data);
        // Untuk tes backend:
        return $this->response->setJSON($data);
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
     * Memproses submit absensi dari mahasiswa.
     */
    public function submitAbsensi()
    {
        // Hanya bisa diakses via POST
        if ($this->request->getMethod() !== 'post') {
            return redirect()->back();
        }

        // Inisialisasi model
        $kehadiranModel = new KehadiranModel();
        $sesiAbsensiModel = new SesiAbsensiModel();

        // Ambil data dari form dan session
        $id_sesi = $this->request->getPost('id_sesi');
        $nim_mahasiswa = session()->get('reference_id');

        // --- Lakukan serangkaian validasi dan security check ---

        // 1. Cek apakah sesi ada dan valid
        $sesi = $sesiAbsensiModel->find($id_sesi);
        if (!$sesi) {
            return redirect()->back()->with('error', 'Sesi absensi tidak valid.');
        }

        // 2. Cek apakah sesi sedang berlangsung
        $now = new \CodeIgniter\I18n\Time('now', 'Asia/Jakarta');
        $start = new \CodeIgniter\I18n\Time($sesi['waktu_mulai_aktual']);
        $end = new \CodeIgniter\I18n\Time($sesi['waktu_selesai_aktual']);

        if (!$now->isBetween($start, $end)) {
            return redirect()->back()->with('error', 'Waktu absensi untuk sesi ini sudah berakhir atau belum dimulai.');
        }

        // 3. Cek apakah mahasiswa sudah absen sebelumnya di sesi ini
        if ($kehadiranModel->sudahAbsen($id_sesi, $nim_mahasiswa)) {
             return redirect()->back()->with('error', 'Anda sudah melakukan absensi untuk sesi ini.');
        }
        
        // --- Jika semua validasi lolos, simpan data ---
        $dataToSave = [
            'id_sesi' => $id_sesi,
            'nim_mahasiswa' => $nim_mahasiswa,
            'waktu_absensi' => $now->toDateTimeString(),
            'status_kehadiran' => 'hadir', // Status default
        ];

        if ($kehadiranModel->save($dataToSave)) {
            return redirect()->back()->with('success', 'Kehadiran berhasil dicatat!');
        } else {
            return redirect()->back()->with('error', 'Terjadi kesalahan. Gagal mencatat kehadiran.');
        }
    }
}