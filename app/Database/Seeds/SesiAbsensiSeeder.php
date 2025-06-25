<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SesiAbsensiSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        $today = date('Y-m-d');
        
        // Start time 30 minutes ago
        $startTime = date('Y-m-d H:i:s', strtotime('-30 minutes'));
        
        // End time 1 hour from now
        $endTime = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Past session (yesterday)
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $pastStartTime = $yesterday . ' 08:00:00';
        $pastEndTime = $yesterday . ' 10:30:00';
        
        // Future session (tomorrow)
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $futureStartTime = $tomorrow . ' 08:00:00';
        $futureEndTime = $tomorrow . ' 10:30:00';

        $data = [
            // Active session now
            [
                'kode_kelas' => 'KLS001',
                'tanggal_sesi' => $today,
                'waktu_mulai_aktual' => $startTime,
                'waktu_selesai_aktual' => $endTime,
                'status' => 'aktif',
                'topik_perkuliahan' => 'Intro to HTML and CSS',
                'token_sesi' => 'ABC123',
                'perlu_bukti_foto' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Completed session
            [
                'kode_kelas' => 'KLS001',
                'tanggal_sesi' => $yesterday,
                'waktu_mulai_aktual' => $pastStartTime,
                'waktu_selesai_aktual' => $pastEndTime,
                'status' => 'selesai',
                'topik_perkuliahan' => 'Introduction to Web Development',
                'token_sesi' => 'XYZ456',
                'perlu_bukti_foto' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Future session
            [
                'kode_kelas' => 'KLS001',
                'tanggal_sesi' => $tomorrow,
                'waktu_mulai_aktual' => $futureStartTime,
                'waktu_selesai_aktual' => $futureEndTime,
                'status' => 'aktif',
                'topik_perkuliahan' => 'JavaScript Fundamentals',
                'token_sesi' => 'DEF789',
                'perlu_bukti_foto' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Active session for another class
            [
                'kode_kelas' => 'KLS002',
                'tanggal_sesi' => $today,
                'waktu_mulai_aktual' => $startTime,
                'waktu_selesai_aktual' => $endTime,
                'status' => 'aktif',
                'topik_perkuliahan' => 'SQL Basics',
                'token_sesi' => 'GHI012',
                'perlu_bukti_foto' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Insert data
        $this->db->table('sesi_absensi')->insertBatch($data);
    }
}