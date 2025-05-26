<?php

namespace App\Controllers;

use App\Models\DosenModel; // Kita sudah punya ini
// Mungkin perlu model lain seperti KelasModel, SesiAbsensiModel di masa depan

class DosenController extends BaseController
{
    protected $dosenModel;
    protected $session;

    public function __construct()
    {
        $this->dosenModel = new DosenModel();
        $this->session = \Config\Services::session();
        helper(['url']);

        // Filter untuk dosen
        // if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'dosen') {
        //     // Redirect atau tampilkan error (lebih baik via Filter)
        //     echo 'Akses ditolak untuk dosen!';
        //     exit;
        // }
    }

    public function dashboard()
    {
        // Cek sesi dosen
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'dosen') {
            return redirect()->to(base_url('login'));
        }
        $nipDosen = $this->session->get('reference_id');
        $dosenInfo = $this->dosenModel->find($nipDosen);

        $data['title'] = 'Dosen Dashboard';
        $data['dosen'] = $dosenInfo;
        $data['nama_user'] = $this->session->get('nama_lengkap');

        // return view('dosen/dashboard', $data);
        // Untuk tes backend:
        return $this->response->setJSON($data);
    }

    // Contoh: Menampilkan profil dosen yang sedang login
    public function profile()
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'dosen') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Akses ditolak']);
        }
        $nipDosen = $this->session->get('reference_id');
        $dosen = $this->dosenModel->getDosenWithUser($nipDosen); // Menggunakan fungsi dari DosenModel

        if ($dosen) {
            return $this->response->setJSON($dosen);
        } else {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Profil dosen tidak ditemukan.']);
        }
    }
    
    // Fungsionalitas lain seperti buat kelas, buat sesi absensi akan ditambahkan di sini nanti
    // saat Model KelasModel dan SesiAbsensiModel sudah dibuat.
}