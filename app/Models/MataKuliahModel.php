<?php

namespace App\Models;

use CodeIgniter\Model;

class MataKuliahModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'matakuliah';
    protected $primaryKey       = 'kode_matakuliah';
    protected $useAutoIncrement = false; 
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'kode_matakuliah', 
        'nama_matakuliah', 
        'sks',
        'created_at', // Jika diisi manual, jika tidak biarkan CI4
        'updated_at'  // Jika diisi manual, jika tidak biarkan CI4
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'kode_matakuliah' => 'required|alpha_numeric|max_length[15]|is_unique[matakuliah.kode_matakuliah,kode_matakuliah,{kode_matakuliah}]',
        'nama_matakuliah' => 'required|string|max_length[100]',
        'sks'             => 'permit_empty|integer|greater_than_equal_to[0]',
    ];
    protected $validationMessages = [
        'kode_matakuliah' => [
            'required'          => 'Kode mata kuliah wajib diisi.',
            'alpha_numeric_dash'=> 'Kode mata kuliah hanya boleh berisi huruf, angka, underscore, atau dash.',
            'max_length'        => 'Kode mata kuliah maksimal 15 karakter.',
            'is_unique'         => 'Kode mata kuliah ini sudah ada. Silakan gunakan kode lain.'
        ],
        'nama_matakuliah' => [
            'required'   => 'Nama mata kuliah wajib diisi.',
            'max_length' => 'Nama mata kuliah maksimal 100 karakter.'
        ],
        'sks' => [
            'integer'               => 'SKS harus berupa angka (bilangan bulat).',
            'greater_than_equal_to' => 'SKS tidak boleh bernilai negatif.'
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
}