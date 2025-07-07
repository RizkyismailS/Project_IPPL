<?php

namespace App\Models;

use CodeIgniter\Model;

class MahasiswaModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'mahasiswa';
    protected $primaryKey       = 'nim';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'nim', 
        'nama', 
        'email', 
        'foto_wajah',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Remove static validation rules since we'll handle them dynamically
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert   = ['validateForInsert'];
    protected $beforeUpdate   = ['validateForUpdate'];

    protected function validateForInsert(array $data)
    {
        $rules = [
            'nim'        => 'required|alpha_numeric|max_length[20]|is_unique[mahasiswa.nim]',
            'nama'       => 'required|string|max_length[100]',
            'email'      => 'required|valid_email|max_length[100]|is_unique[mahasiswa.email]',
            'foto_wajah' => 'permit_empty|string|max_length[255]',
        ];
        
        $this->setValidationRules($rules);
        return $data;
    }

    protected function validateForUpdate(array $data)
    {
        // Get the current record to compare values
        $nim = $data['id'][0] ?? null; // Primary key value
        if (!$nim) {
            return $data;
        }

        $currentRecord = $this->find($nim);
        if (!$currentRecord) {
            return $data;
        }

        $rules = [
            'nama' => 'required|string|max_length[100]',
            'foto_wajah' => 'permit_empty|string|max_length[255]',
        ];

        // Only validate email uniqueness if it has changed
        if (isset($data['data']['email']) && $data['data']['email'] !== $currentRecord['email']) {
            $rules['email'] = "required|valid_email|max_length[100]|is_unique[mahasiswa.email,nim,{$nim}]";
        } else if (isset($data['data']['email'])) {
            $rules['email'] = 'required|valid_email|max_length[100]';
        }

        $this->setValidationRules($rules);
        return $data;
    }

    // Rest of your existing methods...
    public function getMahasiswaWithUser(string $nim)
    {
        return $this->select('mahasiswa.*, users.username, users.is_active')
                    ->join('users', 'users.reference_id = mahasiswa.nim AND users.role = \'mahasiswa\'', 'left')
                    ->where('mahasiswa.nim', $nim)
                    ->first();
    }

    public function getEnrolledKelas(string $nim)
    {
        $builder = $this->db->table('enrollment e');
        $builder->select('k.kode_kelas, k.nama_kelas, k.kode_matakuliah, mk.nama_matakuliah, d.nama as nama_dosen');
        $builder->join('kelas k', 'k.kode_kelas = e.kode_kelas_enrolled');
        $builder->join('matakuliah mk', 'mk.kode_matakuliah = k.kode_matakuliah');
        $builder->join('dosen d', 'd.nip = k.dosen_nip', 'left');
        $builder->where('e.nim_mahasiswa', $nim);
        $builder->where('e.status_enrollment', 'aktif');
        $query = $builder->get();
        return $query->getResultArray();
    }
}