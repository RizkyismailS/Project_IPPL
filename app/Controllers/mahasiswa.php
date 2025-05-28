<?php
// app/Controllers/Mahasiswa.php
namespace App\Controllers;

use App\Controllers\BaseController;

class Mahasiswa extends BaseController
{
    public function index()
    {
        return view('mahasiswa/dashboard', [
            'title' => 'Dashboard Mahasiswa',
            'navbar' => 'layout/navbar',
            'sidebar' => 'layout/student_sidebar'
        ]);
    }

    public function enroll()
    {
        return view('mahasiswa/enroll', [
            'title' => 'Enroll Kelas',
            'navbar' => 'layout/navbar',
            'sidebar' => 'layout/student_sidebar'
        ]);
    }

    public function absensi()
    {
        // Data dummy sementara
        $kelas = [
            [
                'nama_matkul' => 'Implementasi & Pengembangan',
                'kelas' => 'A1 - 2025',
                'jam_mulai' => '13:00',
                'jam_selesai' => '13:15',
                'status' => 'hadir',
                'warna' => 'bg-warning'
            ],
            [
                'nama_matkul' => 'Sistem Operasi',
                'kelas' => 'A1 - IF',
                'jam_mulai' => '07:00',
                'jam_selesai' => '07:05',
                'status' => 'hadir',
                'warna' => 'bg-success'
            ],
            [
                'nama_matkul' => 'Sistem Operasi',
                'kelas' => 'A1 - IF',
                'jam_mulai' => '08:00',
                'jam_selesai' => '08:10',
                'status' => 'tidak',
                'warna' => 'bg-danger'
            ],
            [
                'nama_matkul' => 'Praktikum KBPL',
                'kelas' => 'A1 - IF',
                'jam_mulai' => '14:00',
                'jam_selesai' => '14:10',
                'status' => 'hadir',
                'warna' => 'bg-dark'
            ],
            [
                'nama_matkul' => 'Sistem Terdistribusi',
                'kelas' => 'A1 - IF',
                'jam_mulai' => '07:30',
                'jam_selesai' => '07:35',
                'status' => 'tidak',
                'warna' => 'bg-success'
            ],
            [
                'nama_matkul' => 'Praktikum PWD',
                'kelas' => 'A1 - IF',
                'jam_mulai' => '10:00',
                'jam_selesai' => '10:10',
                'status' => 'hadir',
                'warna' => 'bg-info'
            ]
        ];

        return view('mahasiswa/absensi',  [
            'title' => 'Absensi Kelas',
            'navbar' => 'layout/navbar',
            'sidebar' => 'layout/student_sidebar',
            'kelas' => $kelas
        ]);
    }
}
