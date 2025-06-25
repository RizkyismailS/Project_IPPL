<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class EnrollmentSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nim_mahasiswa' => '41155050210023',
                'kode_kelas_enrolled' => 'KLS001',
                'tanggal_enroll' => date('Y-m-d'),
                'status_enrollment' => 'aktif',
            ],
            [
                'nim_mahasiswa' => '41155050210023',
                'kode_kelas_enrolled' => 'KLS002',
                'tanggal_enroll' => date('Y-m-d'),
                'status_enrollment' => 'aktif',
            ],
            [
                'nim_mahasiswa' => '41155050210024',
                'kode_kelas_enrolled' => 'KLS001',
                'tanggal_enroll' => date('Y-m-d'),
                'status_enrollment' => 'aktif',
            ],
            [
                'nim_mahasiswa' => '41155050210025',
                'kode_kelas_enrolled' => 'KLS001',
                'tanggal_enroll' => date('Y-m-d'),
                'status_enrollment' => 'aktif',
            ],
            [
                'nim_mahasiswa' => '41155050210025',
                'kode_kelas_enrolled' => 'KLS003',
                'tanggal_enroll' => date('Y-m-d'),
                'status_enrollment' => 'aktif',
            ],
            // Add more enrollments as needed
        ];

        // Insert data
        $this->db->table('enrollment')->insertBatch($data);
    }
}