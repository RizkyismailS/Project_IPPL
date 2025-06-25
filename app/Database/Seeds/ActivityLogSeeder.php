<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        $yesterday = date('Y-m-d H:i:s', strtotime('-1 day'));
        $twoDaysAgo = date('Y-m-d H:i:s', strtotime('-2 days'));
        
        $data = [
            [
                'user_id' => 1, // Admin user ID
                'reference_id' => 'ADMIN001',
                'role' => 'admin',
                'action' => 'login',
                'description' => 'Admin logged in',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'related_table' => null,
                'related_id' => null,
                'created_at' => $now,
            ],
            [
                'user_id' => 2, // Dosen user ID (adjust if different)
                'reference_id' => 'DSN001',
                'role' => 'dosen',
                'action' => 'login',
                'description' => 'Lecturer logged in',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'related_table' => null,
                'related_id' => null,
                'created_at' => $yesterday,
            ],
            [
                'user_id' => 4, // Mahasiswa user ID (adjust if different)
                'reference_id' => '41155050210023',
                'role' => 'mahasiswa',
                'action' => 'login',
                'description' => 'Student logged in',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'related_table' => null,
                'related_id' => null,
                'created_at' => $twoDaysAgo,
            ],
            [
                'user_id' => 2, // Dosen user ID (adjust if different)
                'reference_id' => 'DSN001',
                'role' => 'dosen',
                'action' => 'create_session',
                'description' => 'Created new attendance session for Pemrograman Web A',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'related_table' => 'sesi_absensi',
                'related_id' => '1', // Adjust to the actual session ID
                'created_at' => $yesterday,
            ],
        ];

        // Insert data
        $this->db->table('activity_logs')->insertBatch($data);
    }
}