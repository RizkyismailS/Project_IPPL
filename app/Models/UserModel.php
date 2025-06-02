<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $DBGroup          = 'default'; // sesuai dengan grup database di app/Config/Database.php
    protected $table            = 'users';
    protected $primaryKey       = 'id_user';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array'; // Mengembalikan data sebagai array, bisa juga 'object' atau class Entity
    protected $useSoftDeletes   = false;   // Kita tidak menggunakan soft delete
    protected $protectFields    = true;    // Penting untuk keamanan mass assignment
    
    // Kolom yang diizinkan untuk diisi melalui insert() atau update()
    // 'password' akan di-handle oleh callback untuk hashing
    protected $allowedFields    = [
        'username', 
        'password', // Meskipun di-hash, tetap perlu ada di sini agar bisa diterima dari input
        'role', 
        'reference_id', 
        'is_active',
        'created_at', // Jika diisi manual, jika tidak, biarkan CI4 yang handle
        'updated_at'  // Jika diisi manual, jika tidak, biarkan CI4 yang handle
    ];

    // Dates
    protected $useTimestamps = true;         // Aktifkan timestamp otomatis
    protected $dateFormat    = 'datetime';   // Tipe data di database: DATETIME2 di SQL Server (CI4 akan handle)
                                             // Untuk SQL Server, CI4 akan menggunakan format 'Y-m-d H:i:s.u'
                                             // Namun, CI4 secara internal pintar menangani ini. 'datetime' cukup.
    protected $createdField  = 'created_at'; // Nama kolom timestamp pembuatan
    protected $updatedField  = 'updated_at'; // Nama kolom timestamp pembaruan
    // protected $deletedField  = 'deleted_at'; // Tidak digunakan

    // Validation Rules
    // Aturan ini akan digunakan sebelum data disimpan.
    // Kamu bisa menambahkan lebih banyak aturan atau menyesuaikannya.
   protected $validationRules = [
        'username'     => 'required|alpha_numeric_space|min_length[3]|max_length[50]',
        'password'     => 'required|min_length[8]',
        'role'         => 'required|in_list[admin,dosen,mahasiswa]',
        'reference_id' => 'permit_empty|max_length[20]',
        'is_active'    => 'permit_empty|in_list[0,1]',
    ];

    // Pesan kustom untuk error validasi (opsional)
    protected $validationMessages = [
        'username' => [
            'required'   => 'Username wajib diisi.',
            'is_unique'  => 'Username ini sudah digunakan. Silakan pilih username lain.',
            'min_length' => 'Username minimal harus 3 karakter.',
            'max_length' => 'Username maksimal 50 karakter.'
        ],
        'password' => [
            'required'   => 'Password wajib diisi.',
            'min_length' => 'Password minimal harus 8 karakter.'
        ],
        'role' => [
            'required' => 'Peran pengguna wajib dipilih.',
            'in_list'  => 'Peran pengguna tidak valid.'
        ]
    ];
    protected $skipValidation       = false; // Jangan lewati validasi
    protected $cleanValidationRules = true;  // Bersihkan aturan validasi

    // Callbacks (fungsi yang dijalankan pada event tertentu)
    protected $allowCallbacks = true;    // Izinkan penggunaan callbacks
    protected $beforeInsert   = ['hashPassword']; // Panggil hashPassword sebelum insert
    protected $beforeUpdate   = ['hashPassword']; // Panggil hashPassword sebelum update

    /**
     * Fungsi callback untuk melakukan hashing password.
     * Akan dipanggil sebelum operasi insert atau update.
     *
     * @param array $data Data yang akan disimpan
     * @return array Data yang sudah dimodifikasi (dengan password yang di-hash)
     */
    protected function hashPassword(array $data): array
    {
        // Cek apakah ada field 'password' di data yang dikirim
        // dan apakah passwordnya tidak kosong
        if (!isset($data['data']['password']) || empty($data['data']['password'])) {
            return $data; // Kembalikan data apa adanya jika tidak ada password baru
        }

        // Lakukan hashing password menggunakan fungsi password_hash() bawaan PHP
        // PASSWORD_BCRYPT adalah algoritma yang direkomendasikan
        $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_BCRYPT);
        
        return $data;
    }

    // Kamu bisa menambahkan fungsi-fungsi kustom lainnya di sini
    // Misalnya, fungsi untuk verifikasi password saat login,
    // fungsi untuk mendapatkan detail pengguna beserta profilnya, dll.

    /**
     * Mencari user berdasarkan username.
     *
     * @param string $username
     * @return array|object|null
     */
    public function getUserByUsername(string $username)
    {
        return $this->where('username', $username)->first();
    }
}