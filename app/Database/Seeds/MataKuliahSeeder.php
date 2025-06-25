<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MataKuliahSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'kode_matakuliah' => 'MK001',
                'nama_matakuliah' => 'Pemrograman Web',
                'sks' => 4,
            ],
            [
                'kode_matakuliah' => 'MK002',
                'nama_matakuliah' => 'Basis Data',
                'sks' => 3,
            ],
            [
                'kode_matakuliah' => 'MK003',
                'nama_matakuliah' => 'Algoritma dan Struktur Data',
                'sks' => 4,
            ],
            [
                'kode_matakuliah' => 'MK004',
                'nama_matakuliah' => 'Pemrograman Mobile',
                'sks' => 3,
            ],
            [
                'kode_matakuliah' => 'MK005',
                'nama_matakuliah' => 'Jaringan Komputer',
                'sks' => 3,
            ],
        ];

        // Insert data
        $this->db->table('matakuliah')->insertBatch($data);
    }
}