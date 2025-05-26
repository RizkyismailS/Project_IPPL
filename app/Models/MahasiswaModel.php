<?php

namespace App\Models;

use CodeIgniter\Model;

class MahasiswaModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'mahasiswa';
    protected $primaryKey       = 'nim'; // Primary key adalah 'nim'
    protected $useAutoIncrement = false;   // 'nim' bukan auto-increment
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    // Kolom yang diizinkan untuk diisi atau diubah
    protected $allowedFields    = [
        'nim', 
        'nama', 
        'email', 
        'foto_wajah',
        'created_at', // Jika diisi manual
        'updated_at'  // Jika diisi manual
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation Rules
    protected $validationRules = [
         'nim'        => 'required|alpha_numeric|max_length[20]|is_unique[mahasiswa.nim]',
        'nama'       => 'required|string|max_length[100]',
        'email'      => 'required|valid_email|max_length[100]|is_unique[mahasiswa.email]',
        'foto_wajah' => 'permit_empty|string|max_length[255]', // Asumsi path, sesuaikan
    ];

    // Pesan kustom untuk error validasi (opsional)
    protected $validationMessages = [
        'nim' => [
            'required'  => 'NIM wajib diisi.',
            'is_unique' => 'NIM ini sudah terdaftar.',
            'max_length'=> 'NIM maksimal 20 karakter.'
        ],
        'nama' => [
            'required'   => 'Nama mahasiswa wajib diisi.',
            'max_length' => 'Nama mahasiswa maksimal 100 karakter.'
        ],
        'email' => [
            'required'    => 'Email wajib diisi.',
            'valid_email' => 'Format email tidak valid.',
            'is_unique'   => 'Email ini sudah digunakan.',
            'max_length'  => 'Email maksimal 100 karakter.'
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

    // Contoh fungsi kustom:
    /**
     * Mendapatkan detail mahasiswa beserta informasi user login jika ada.
     *
     * @param string $nim
     * @return array|object|null
     */
    public function getMahasiswaWithUser(string $nim)
    {
        return $this->select('mahasiswa.*, users.username, users.is_active')
                    ->join('users', 'users.reference_id = mahasiswa.nim AND users.role = \'mahasiswa\'', 'left')
                    ->where('mahasiswa.nim', $nim)
                    ->first();
    }

    /**
     * Mendapatkan daftar kelas yang di-enroll oleh mahasiswa.
     *
     * @param string $nim
     * @return array
     */
    public function getEnrolledKelas(string $nim)
    {
        // Ini memerlukan join dengan tabel enrollment dan kelas
        // Contoh sederhana, bisa dikembangkan
        $builder = $this->db->table('enrollment e');
        $builder->select('k.kode_kelas, k.nama_kelas, k.kode_matakuliah, mk.nama_matakuliah, d.nama as nama_dosen');
        $builder->join('kelas k', 'k.kode_kelas = e.kode_kelas_enrolled');
        $builder->join('matakuliah mk', 'mk.kode_matakuliah = k.kode_matakuliah');
        $builder->join('dosen d', 'd.nip = k.dosen_nip', 'left');
        $builder->where('e.nim_mahasiswa', $nim);
        $builder->where('e.status_enrollment', 'aktif'); // Hanya kelas yang status enrollmentnya aktif
        $query = $builder->get();
        return $query->getResultArray();
    }
}