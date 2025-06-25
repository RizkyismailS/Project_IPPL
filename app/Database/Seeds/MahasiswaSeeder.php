<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MahasiswaSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nim' => '41155050210023',
                'nama' => 'Albert Ambatukam',
                'email' => 'albert@example.com',
                'foto_wajah' => null,
            ],
            [
                'nim' => '41155050210024',
                'nama' => 'Budi Setiawan',
                'email' => 'budi.setiawan@example.com',
                'foto_wajah' => null,
            ],
            [
                'nim' => '41155050210025',
                'nama' => 'Citra Dewi',
                'email' => 'citra.dewi@example.com',
                'foto_wajah' => null,
            ],
            [
                'nim' => '41155050210026',
                'nama' => 'Dedi Kurniawan',
                'email' => 'dedi@example.com',
                'foto_wajah' => null,
            ],
            [
                'nim' => '41155050210027',
                'nama' => 'Eka Putri',
                'email' => 'eka@example.com',
                'foto_wajah' => null,
            ],
            // Add more as needed
        ];

        // Insert data
        $this->db->table('mahasiswa')->insertBatch($data);
    }
}