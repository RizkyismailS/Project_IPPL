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
        'status_enrollment',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation (Tambahkan jika diperlukan, terutama untuk create/update enrollment)
    protected $validationRules      = [
        'nim_mahasiswa'       => 'required[mahasiswa.nim]',
        'kode_kelas_enrolled' => 'required[kelas.kode_kelas]',
        'status_enrollment'   => 'required|in_list[aktif,selesai_lulus,selesai_gagal,mengundurkan_diri,menunggu_persetujuan]'
        // Aturan is_unique untuk kombinasi nim_mahasiswa & kode_kelas_enrolled
        // Sebaiknya ditambahkan dengan placeholder untuk update:
        // 'nim_mahasiswa' => 'is_unique[enrollment.nim_mahasiswa,id_enrollment,{id_enrollment},kode_kelas_enrolled,{kode_kelas_enrolled}]'
        // Tapi untuk countAllResults() tidak perlu validasi ini.
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Anda akan menambahkan fungsi lain di sini nanti, seperti getMahasiswaByKelas(), getKelasByMahasiswa(), dll.
}