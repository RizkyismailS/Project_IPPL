<?php

namespace App\Models;

use CodeIgniter\Model;

class DosenModel extends Model
{
    protected $DBGroup          = 'default'; // Sesuaikan jika grup database berbeda
    protected $table            = 'dosen';
    protected $primaryKey       = 'nip'; // Primary key adalah 'nip'
    protected $useAutoIncrement = false;  // 'nip' bukan auto-increment
    protected $returnType       = 'array'; // Atau 'object' atau class Entity Dosen jika ada
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    // Kolom yang diizinkan untuk diisi atau diubah
    protected $allowedFields    = [
        'nip', 
        'nama', 
        'email', 
        'jabatan',
        'created_at', // Jika diisi manual
        'updated_at'  // Jika diisi manual
    ];

    // Dates
    protected $useTimestamps = true;         // Menggunakan created_at dan updated_at
    protected $dateFormat    = 'datetime';   // Sesuaikan dengan format di database jika perlu (DATETIME2)
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at'; // Tidak digunakan

    // Validation Rules
    // Sesuaikan aturan validasi ini dengan kebutuhanmu
    protected $validationRules = [
        'nip'     => 'required|alpha_numeric|max_length[20]|is_unique[dosen.nip]',
        'nama'    => 'required|string|max_length[100]',
        'email'   => 'required|valid_email|max_length[100]|is_unique[dosen.email]',
        'jabatan' => 'permit_empty|string|max_length[50]',
    ];

    // Pesan kustom untuk error validasi (opsional)
    protected $validationMessages = [
        'nip' => [
            'required'  => 'NIP wajib diisi.',
            'is_unique' => 'NIP ini sudah terdaftar.',
            'max_length'=> 'NIP maksimal 20 karakter.'
        ],
        'nama' => [
            'required'   => 'Nama dosen wajib diisi.',
            'max_length' => 'Nama dosen maksimal 100 karakter.'
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

    // Kamu bisa menambahkan fungsi kustom di sini jika diperlukan
    // Misalnya, untuk mengambil data dosen beserta kelas yang diajar, dll.

    /**
     * Mendapatkan detail dosen beserta informasi user login jika ada.
     * Ini adalah contoh, implementasi join bisa bervariasi.
     *
     * @param string $nip
     * @return array|object|null
     */
    public function getDosenWithUser(string $nip)
    {
        return $this->select('dosen.*, users.username, users.is_active')
                    ->join('users', 'users.reference_id = dosen.nip AND users.role = \'dosen\'', 'left')
                    ->where('dosen.nip', $nip)
                    ->first();
    }
}