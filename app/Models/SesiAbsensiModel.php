<?php

namespace App\Models;

use CodeIgniter\Model;

class SesiAbsensiModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'sesi_absensi';
    protected $primaryKey       = 'id_sesi';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kode_kelas',
        'tanggal_sesi',
        'waktu_mulai_aktual',
        'waktu_selesai_aktual',
        'status',
        'topik_perkuliahan',
        'token_sesi',
        'perlu_bukti_foto',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules      = [
        'kode_kelas' => 'required[kelas.kode_kelas]',
        'tanggal_sesi' => 'required|valid_date',
        'waktu_mulai_aktual' => 'required', 
        'status' => 'required|in_list[aktif,selesai,dibatalkan,terlewat]',
        'token_sesi' => 'permit_empty|alpha_numeric|max_length[10]|is_unique[sesi_absensi.token_sesi,id_sesi,{id_sesi}]',
        'perlu_bukti_foto' => 'required|in_list[0,1]'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Mengambil semua sesi absensi untuk sebuah kelas tertentu.
     * Diurutkan berdasarkan tanggal sesi.
     *
     * @param string $kodeKelas
     * @return array
     */
    public function getSesiByKelas(string $kodeKelas): array
    {
        // UBAH orderBy KE 'tanggal_sesi'
        return $this->where('kode_kelas', $kodeKelas)
                    ->orderBy('tanggal_sesi', 'ASC')
                    ->orderBy('waktu_mulai_aktual', 'ASC')
                    ->findAll();
    }

     public function findActiveSessionForMahasiswa(string $nim)
    {
        $now = date('Y-m-d H:i:s');

        return $this->select('sesi_absensi.id_sesi, sesi_absensi.topik_perkuliahan, sesi_absensi.waktu_selesai_aktual, kelas.nama_kelas, matakuliah.nama_matakuliah')
            ->join('kelas', 'kelas.kode_kelas = sesi_absensi.kode_kelas')
            ->join('matakuliah', 'matakuliah.kode_matakuliah = kelas.kode_matakuliah')
            ->join('enrollment', 'enrollment.kode_kelas_enrolled = kelas.kode_kelas')
            ->where('enrollment.nim_mahasiswa', $nim)
            ->where('enrollment.status_enrollment', 'aktif') // Menggunakan nama kolom yang benar
            ->where('sesi_absensi.status', 'aktif') // Menggunakan status sesi yang benar
            ->where('sesi_absensi.waktu_mulai_aktual <=', $now)
            ->where("NOT EXISTS (
                SELECT 1 FROM kehadiran 
                WHERE kehadiran.id_sesi = sesi_absensi.id_sesi 
                AND kehadiran.nim = " . $this->db->escape($nim) . "
            )")
            ->first();
    }

    /**
     * Mengambil data laporan kehadiran lengkap untuk satu sesi.
     * Menggabungkan data mahasiswa yang terdaftar dengan status kehadiran mereka.
     * Jika mahasiswa tidak memiliki record kehadiran, statusnya dianggap 'Alpa'.
     *
     * @param int $id_sesi ID dari sesi absensi
     * @return array Data laporan kehadiran
     */
    public function getLaporanKehadiran(int $id_sesi): array
    {
        // 1. Dapatkan dulu kode kelas dari id_sesi yang diberikan
        $sesi = $this->find($id_sesi);
        if (!$sesi) {
            return []; // Kembalikan array kosong jika sesi tidak ditemukan
        }
        $kode_kelas = $sesi['kode_kelas'];

        // 2. Buat query utama menggunakan Query Builder
        $builder = $this->db->table('enrollment');
        
        $builder->select('mahasiswa.nim, mahasiswa.nama, IFNULL(kehadiran.status_absen, "Alpa") as status_kehadiran');
        $builder->join('mahasiswa', 'mahasiswa.nim = enrollment.nim_mahasiswa');
        
        // LEFT JOIN ke tabel kehadiran dengan DUA kondisi
        $builder->join('kehadiran', 'kehadiran.nim = enrollment.nim_mahasiswa AND kehadiran.id_sesi = ' . $this->db->escape($id_sesi), 'left');
        
        $builder->where('enrollment.kode_kelas_enrolled', $kode_kelas);
        $builder->orderBy('mahasiswa.nim', 'ASC');

        return $builder->get()->getResultArray();
    }
}