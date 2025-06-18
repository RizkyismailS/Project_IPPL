<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SesiAbsensiModel;
use App\Models\KelasModel;

class SesiAbsensiController extends BaseController
{
    protected $sesiAbsensiModel;
    protected $kelasModel;

    public function __construct()
    {
        $this->sesiAbsensiModel = new SesiAbsensiModel();
        $this->kelasModel = new KelasModel();
    }

    public function create($kode_kelas)
    {
        $kelas = $this->kelasModel->find($kode_kelas);
        if (!$kelas || $kelas['dosen_nip'] !== session()->get('reference_id')) {
            return redirect()->to('dosen/kelas')->with('error', 'Kelas tidak ditemukan atau Anda tidak memiliki akses.');
        }

        $data = [
            'title' => 'Buat Sesi Absensi Baru',
            'kelas' => $kelas,
            'validation' => \Config\Services::validation()
        ];

        return view('dosen/create_absensi', $data);
    }

    public function store()
    {
        $kode_kelas = $this->request->getPost('kode_kelas');

        // Hapus 'pertemuan_ke' dari validasi
        $rules = [
            'topik_perkuliahan' => 'required|max_length[255]',
            'tanggal_sesi' => 'required|valid_date',
            'waktu_mulai_aktual' => 'required',
            'waktu_selesai_aktual' => 'required',
            'perlu_bukti_foto' => 'required|in_list[0,1]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // ... (Security check biarkan sama)
        $kelas = $this->kelasModel->find($kode_kelas);
        if (!$kelas || $kelas['dosen_nip'] !== session()->get('reference_id')) {
            return redirect()->to('dosen/kelas')->with('error', 'Aksi tidak diizinkan.');
        }

        // Gabungkan tanggal dan waktu
        $waktu_mulai = $this->request->getPost('tanggal_sesi') . ' ' . $this->request->getPost('waktu_mulai_aktual');
        $waktu_selesai = $this->request->getPost('tanggal_sesi') . ' ' . $this->request->getPost('waktu_selesai_aktual');

        // Hapus 'pertemuan_ke' dari data yang disimpan
        $dataToSave = [
            'kode_kelas' => $kode_kelas,
            'topik_perkuliahan' => $this->request->getPost('topik_perkuliahan'),
            'tanggal_sesi' => $this->request->getPost('tanggal_sesi'),
            'waktu_mulai_aktual' => $waktu_mulai,
            'waktu_selesai_aktual' => $waktu_selesai,
            'perlu_bukti_foto' => $this->request->getPost('perlu_bukti_foto'),
            'status' => 'aktif',
        ];

        $this->sesiAbsensiModel->save($dataToSave);
        $activityLogModel = new \App\Models\ActivityLogModel();
        $sessionId = $this->sesiAbsensiModel->getInsertID();
        $activityLogModel->logActivity(
            session()->get('id_user'),
            session()->get('reference_id'),
            'dosen',
            'create_absensi_session',
            'Created new absensi session for class ' . $kode_kelas . ': ' . $this->request->getPost('topik_perkuliahan'),
            'sesi_absensi',
            $sessionId
        );
        return redirect()->to('dosen/kelas/detail/' . $kode_kelas)->with('success', 'Sesi absensi baru berhasil dibuat.');
    }

    /**
     * Menampilkan form untuk mengedit sesi absensi yang ada.
     *
     * @param int $id_sesi
     */
    public function edit($id_sesi)
    {
        // Ambil data sesi yang akan diedit
        $sesi = $this->sesiAbsensiModel->find($id_sesi);
        if (!$sesi) {
            return redirect()->to('dosen/dashboard')->with('error', 'Sesi absensi tidak ditemukan.');
        }

        // Security check: pastikan sesi ini milik kelas dari dosen yang login
        $kelas = $this->kelasModel->find($sesi['kode_kelas']);
        if (!$kelas || $kelas['dosen_nip'] !== session()->get('reference_id')) {
            return redirect()->to('dosen/kelas')->with('error', 'Anda tidak memiliki akses untuk mengedit sesi ini.');
        }

        $data = [
            'title' => 'Edit Sesi Absensi',
            'sesi' => $sesi,
            'kelas' => $kelas,
            'validation' => \Config\Services::validation()
        ];

        return view('dosen/edit_absensi', $data);
    }

    /**
     * Memproses dan menyimpan perubahan data sesi absensi.
     *
     * @param int $id_sesi
     */
    public function update($id_sesi)
    {
        // Validasi sama seperti saat store
        $rules = [
            'topik_perkuliahan' => 'required|max_length[255]',
            'tanggal_sesi' => 'required|valid_date',
            'waktu_mulai_aktual' => 'required',
            'waktu_selesai_aktual' => 'required',
            'perlu_bukti_foto' => 'required|in_list[0,1]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // Security check (selalu lakukan untuk aksi yang mengubah data)
        $sesi = $this->sesiAbsensiModel->find($id_sesi);
        $kelas = $this->kelasModel->find($sesi['kode_kelas']);
        if (!$kelas || $kelas['dosen_nip'] !== session()->get('reference_id')) {
            return redirect()->to('dosen/kelas')->with('error', 'Aksi tidak diizinkan.');
        }

        // Gabungkan tanggal dan waktu
        $waktu_mulai = $this->request->getPost('tanggal_sesi') . ' ' . $this->request->getPost('waktu_mulai_aktual');
        $waktu_selesai = $this->request->getPost('tanggal_sesi') . ' ' . $this->request->getPost('waktu_selesai_aktual');

        // Siapkan data untuk diupdate
        $dataToUpdate = [
            'topik_perkuliahan' => $this->request->getPost('topik_perkuliahan'),
            'tanggal_sesi' => $this->request->getPost('tanggal_sesi'),
            'waktu_mulai_aktual' => $waktu_mulai,
            'waktu_selesai_aktual' => $waktu_selesai,
            'perlu_bukti_foto' => $this->request->getPost('perlu_bukti_foto'),
        ];

        $this->sesiAbsensiModel->update($id_sesi, $dataToUpdate);
        $activityLogModel = new \App\Models\ActivityLogModel();
        $activityLogModel->logActivity(
            session()->get('id_user'),
            session()->get('reference_id'),
            'dosen',
            'update_absensi_session',
            'Updated absensi session ID ' . $id_sesi,
            'sesi_absensi',
            $id_sesi
        );
        return redirect()->to('dosen/kelas/detail/' . $sesi['kode_kelas'])->with('success', 'Sesi absensi berhasil diperbarui.');
    }
}