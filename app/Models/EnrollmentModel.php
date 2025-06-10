<?php

namespace App\Models;

use CodeIgniter\Model;

class EnrollmentModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'enrollment';
    protected $primaryKey       = 'id_enrollment';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nim_mahasiswa',
        'kode_kelas_enrolled',
        'tanggal_enroll',
        'status_enrollment'
        // created_at and updated_at are handled by useTimestamps
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'nim_mahasiswa'       => 'required[mahasiswa.nim]',
        'kode_kelas_enrolled' => 'required[kelas.kode_kelas]',
        'status_enrollment'   => 'required|in_list[aktif,selesai_lulus,selesai_gagal,mengundurkan_diri,menunggu_persetujuan,dinonaktifkan]'
    ];
    protected $validationMessages   = [
        'nim_mahasiswa' => [
            'required' => 'Student NIM is required.',
        ],
        'kode_kelas_enrolled' => [
            'required' => 'Class Code is required.',
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Get the students enrolled in a specific class.
     *
     * @param string $kodeKelas
     * @return array
     */
    public function getMahasiswaByKelas(string $kodeKelas): array
    {
        // PASTIKAN ANDA MENAMBAHKAN "enrollment.id_enrollment" DI BARIS INI
        return $this->select('enrollment.id_enrollment, mahasiswa.nim, mahasiswa.nama, mahasiswa.email, enrollment.tanggal_enroll, enrollment.status_enrollment')
                    ->join('mahasiswa', 'mahasiswa.nim = enrollment.nim_mahasiswa')
                    ->where('enrollment.kode_kelas_enrolled', $kodeKelas)
                    ->orderBy('mahasiswa.nama', 'ASC')
                    ->findAll();
    }

    /**
     * Check if a student is already enrolled in a class.
     *
     * @param string $nimMahasiswa
     * @param string $kodeKelas
     * @return bool
     */
    public function isEnrolled(string $nimMahasiswa, string $kodeKelas): bool
    {
        return $this->where('nim_mahasiswa', $nimMahasiswa)
                    ->where('kode_kelas_enrolled', $kodeKelas)
                    ->countAllResults() > 0;
    }

}