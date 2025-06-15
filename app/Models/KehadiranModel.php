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
     * Menghitung statistik kehadiran untuk seorang mahasiswa.
     *
     * @param string $nim NIM dari mahasiswa
     * @return array Array berisi 'hadir' dan 'tidak_hadir'
     */
    public function getAttendanceStats(string $nim): array
    {
        $totalHadir = $this->where('nim', $nim)
                           ->where('status_absen', 'hadir')
                           ->countAllResults(false); // `false` agar query builder tidak di-reset

        $totalTidakHadir = $this->where('nim', $nim)
                                ->whereIn('status_absen', ['alpa', 'sakit', 'izin'])
                                ->countAllResults();

        return [
            'hadir'       => $totalHadir,
            'tidak_hadir' => $totalTidakHadir,
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