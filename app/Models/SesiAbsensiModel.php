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
}