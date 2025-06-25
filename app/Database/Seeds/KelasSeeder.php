<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class KelasSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'kode_kelas' => 'KLS001',
                'nama_kelas' => 'Pemrograman Web A',
                'kode_matakuliah' => 'MK001',
                'dosen_nip' => 'DSN001',
                'hari' => 'Senin',
                'waktu_mulai_kelas' => '08:00:00',
                'waktu_selesai_kelas' => '10:30:00',
                'ruangan' => 'Lab Komputer 1',
                'tahun_ajaran' => '2023/2024',
                'semester' => 'Ganjil',
                'kode_enrollment' => 'ENRKLS001',
            ],
            [
                'kode_kelas' => 'KLS002',
                'nama_kelas' => 'Basis Data B',
                'kode_matakuliah' => 'MK002',
                'dosen_nip' => 'DSN002',
                'hari' => 'Selasa',
                'waktu_mulai_kelas' => '13:00:00',
                'waktu_selesai_kelas' => '15:30:00',
                'ruangan' => 'Lab Komputer 2',
                'tahun_ajaran' => '2023/2024',
                'semester' => 'Ganjil',
                'kode_enrollment' => 'ENRKLS002',
            ],
            [
                'kode_kelas' => 'KLS003',
                'nama_kelas' => 'Algoritma C',
                'kode_matakuliah' => 'MK003',
                'dosen_nip' => 'DSN003',
                'hari' => 'Rabu',
                'waktu_mulai_kelas' => '09:30:00',
                'waktu_selesai_kelas' => '12:00:00',
                'ruangan' => 'Ruang 303',
                'tahun_ajaran' => '2023/2024',
                'semester' => 'Ganjil',
                'kode_enrollment' => 'ENRKLS003',
            ],
        ];

        // Insert data
        $this->db->table('kelas')->insertBatch($data);
    }
}