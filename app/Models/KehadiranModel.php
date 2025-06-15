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
    protected $allowedFields    = [
        'id_sesi',
        'nim_mahasiswa',
        'waktu_absensi',
        'status_kehadiran',
        'keterangan', // jika perlu
        'bukti_foto' // jika perlu
    ];

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
}