<?php

namespace App\Controllers;

use App\Models\MahasiswaModel; // Kita sudah punya ini
// Mungkin perlu model lain seperti EnrollmentModel, KelasModel, SesiAbsensiModel, KehadiranModel

class MahasiswaController extends BaseController
{
    protected $mahasiswaModel;
    protected $session;

    public function __construct()
    {
        $this->mahasiswaModel = new MahasiswaModel();
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

    // Fungsionalitas lain seperti enroll kelas, isi absensi akan ditambahkan di sini nanti
    // saat Model terkait (EnrollmentModel, KehadiranModel, dll) sudah dibuat.
}