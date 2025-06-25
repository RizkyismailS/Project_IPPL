<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DosenSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nip' => 'DSN001',
                'nama' => 'Dr. Budi Santoso',
                'email' => 'budi.santoso@example.com',
                'jabatan' => 'Professor',
            ],
            [
                'nip' => 'DSN002',
                'nama' => 'Ir. Siti Rahayu, M.Kom',
                'email' => 'siti.rahayu@example.com',
                'jabatan' => 'Associate Professor',
            ],
            [
                'nip' => 'DSN003',
                'nama' => 'Hendro Wicaksono, Ph.D',
                'email' => 'hendro@example.com',
                'jabatan' => 'Assistant Professor',
            ],
            [
                'nip' => 'DSN004',
                'nama' => 'Dr. Anita Wijaya',
                'email' => 'anita.wijaya@example.com',
                'jabatan' => 'Senior Lecturer',
            ],
            [
                'nip' => 'DSN005',
                'nama' => 'Rudi Hartono, M.Sc',
                'email' => 'rudi.hartono@example.com',
                'jabatan' => 'Lecturer',
            ],
        ];

        // Insert data
        $this->db->table('dosen')->insertBatch($data);
    }
}