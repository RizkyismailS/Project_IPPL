<?php

namespace App\Models;

use CodeIgniter\Model;

class KehadiranModel extends Model
{
    protected $table            = 'kehadiran';
    protected $primaryKey       = 'id_kehadiran';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
   protected $allowedFields    = ['nim', 'id_sesi', 'status_absen', 'waktu_absen', 'keterangan', 'path_bukti_foto'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Memeriksa apakah mahasiswa sudah melakukan absensi untuk sesi tertentu.
     *
     * @param integer $idSesi
     * @param string  $nimMahasiswa
     * @return boolean
     */
    public function sudahAbsen(int $idSesi, string $nimMahasiswa): bool
    {
        return $this->where('id_sesi', $idSesi)
                    ->where('nim_mahasiswa', $nimMahasiswa)
                    ->countAllResults() > 0;
    }

    /**
     * Menghitung statistik kehadiran (total hadir, sakit, izin, dan alpa)
     * untuk seorang mahasiswa secara terpisah.
     *
     * @param string $nim NIM dari mahasiswa
     * @return array Array berisi 'hadir', 'sakit', 'izin', dan 'alpa'
     */
    public function getAttendanceStats(string $nim): array
    {
        $builder = $this->db->table($this->table);

        // Menghitung semua status dalam satu query dengan conditional SUM
        $builder->select("
            SUM(CASE WHEN status_absen = 'hadir' THEN 1 ELSE 0 END) as total_hadir,
            SUM(CASE WHEN status_absen = 'sakit' THEN 1 ELSE 0 END) as total_sakit,
            SUM(CASE WHEN status_absen = 'izin' THEN 1 ELSE 0 END) as total_izin,
            SUM(CASE WHEN status_absen = 'alpa' THEN 1 ELSE 0 END) as total_alpa
        ");
        $builder->where('nim', $nim);
        
        $result = $builder->get()->getRowArray();

        // Mengembalikan hasil dengan memastikan nilainya integer
        return [
            'hadir' => (int) ($result['total_hadir'] ?? 0),
            'sakit' => (int) ($result['total_sakit'] ?? 0),
            'izin'  => (int) ($result['total_izin']  ?? 0),
            'alpa'  => (int) ($result['total_alpa']  ?? 0),
        ];
    }

    /**
     * Mengambil riwayat 5 aktivitas kehadiran terakhir seorang mahasiswa.
     *
     * @param string $nim NIM dari mahasiswa
     * @param int $limit Jumlah riwayat yang ingin ditampilkan
     * @return array Daftar riwayat kehadiran
     */
    public function getAttendanceHistory(string $nim, int $limit = 5): array
    {
        return $this->select('kehadiran.status_absen, kehadiran.waktu_absen, sesi_absensi.topik_perkuliahan, kelas.nama_kelas')
            ->join('sesi_absensi', 'sesi_absensi.id_sesi = kehadiran.id_sesi')
            ->join('kelas', 'kelas.kode_kelas = sesi_absensi.kode_kelas')
            ->where('kehadiran.nim', $nim)
            ->orderBy('kehadiran.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}