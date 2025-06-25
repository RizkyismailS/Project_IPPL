<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // Admin user
            [
                'username' => 'admin',
                'password' => password_hash('adminpass', PASSWORD_BCRYPT),
                'role' => 'admin',
                'reference_id' => 'ADMIN001',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],

            // Dosen users
            [
                'username' => 'budi_santoso',
                'password' => password_hash('dosenpass', PASSWORD_BCRYPT),
                'role' => 'dosen',
                'reference_id' => 'DSN001',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'username' => 'siti_rahayu',
                'password' => password_hash('dosenpass', PASSWORD_BCRYPT),
                'role' => 'dosen',
                'reference_id' => 'DSN002',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            
            // Mahasiswa users
            [
                'username' => 'albert_ambatukam',
                'password' => password_hash('mhspass', PASSWORD_BCRYPT),
                'role' => 'mahasiswa',
                'reference_id' => '41155050210023',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'username' => 'budi_setiawan',
                'password' => password_hash('mhspass', PASSWORD_BCRYPT),
                'role' => 'mahasiswa',
                'reference_id' => '41155050210024',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Add more users as needed
        ];

        // Insert data
        $this->db->table('users')->insertBatch($data);
    }
}