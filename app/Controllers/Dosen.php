<?php
namespace App\Controllers;

use App\Controllers\BaseController;

class Dosen extends BaseController
{
    public function index()
    {
        return view('dosen/dashboard', [
            'title' => 'Dashboard Dosen',
            'sidebar' => 'layout/dosen_sidebar',
        ]);
    }

    public function kelasBaru()
    {
        return view('dosen/kelasBaru', [
            'title' => 'Buat Kelas Baru',
            'sidebar' => 'layout/dosen_sidebar',
        ]);
    }
    public function Absensi(){
        return view('dosen/absensi', [
            'title' => 'Absensi',
            'sidebar' => 'layout/dosen_sidebar',
        ]);
    }
    public function listkelas()
{
    $data = [
        'title'   => 'List Kelas',
        'sidebar' => 'layout/dosen_sidebar',
        'kelas'   => [
            [
                'id' => 1,
                'nama_kelas' => 'Pemrogramman Web',
                'kode_kelas' => 'WEB123-F2025',
                'hari_jam' => 'Senin, 08:00,09:40',
                'jumlah' => 30,
                'semester' => 'Ganjil',
                'tahun' => '2024/2025',
            ],
            [
                'id' => 2,
                'nama_kelas' => 'Statistika Dasar',
                'kode_kelas' => 'STAT101-G2025',
                'hari_jam' => 'Selasa, 10:00,11:40',
                'jumlah' => 28,
                'semester' => 'Ganjil',
                'tahun' => '2024/2025',
            ],
            [
                'id' => 3,
                'nama_kelas' => 'Basis Data',
                'kode_kelas' => 'BSD456-F2025',
                'hari_jam' => 'Rabu, 13:00,14:30',
                'jumlah' => 35,
                'semester' => 'Ganjil',
                'tahun' => '2024/2025',
            ],
            [
                'id' => 4,
                'nama_kelas' => 'Matematika Diskrit',
                'kode_kelas' => 'MAT789-G2025',
                'hari_jam' => 'Jumat,09:00,10:30',
                'jumlah' => 32,
                'semester' => 'Genap',
                'tahun' => '2024/2025',
            ],
        ]
    ];

    return view('dosen/listkelas', $data);
}
   public function listAbsensi()
{
    $data = [
        'title'   => 'List Absensi',
        'sidebar' => 'layout/dosen_sidebar',
        'Absensi' => [
            [
                'id_sesi' => 1,
                'id_kelas' => 1,
                'kode_sesi' => '123-3921-fc',
                'tanggal' => '2025-05-01',
                'waktu_mulai' => '08:00',
                'waktu_selesai' => '09:00',
                'mode' => 'Offline',
                'deskripsi' => 'Pengantar HTML',
                'status_sesi' => 'Aktif',
            ],
            [
                'id_sesi' => 2,
                'id_kelas' => 2,
                'kode_sesi' => 'Kelas-a1-202',
                'tanggal' => '2025-05-02',
                'waktu_mulai' => '10:00',
                'waktu_selesai' => '11:30',
                'mode' => 'Online',
                'deskripsi' => 'Regresi Linier',
                'status_sesi' => 'Aktif',
            ],
            [
                'id_sesi' => 3,
                'id_kelas' => 1,
                'kode_sesi' => 'kdo-203-ee',
                'tanggal' => '2025-05-08',
                'waktu_mulai' => '08:00',
                'waktu_selesai' => '09:00',
                'mode' => 'Offline',
                'deskripsi' => 'Latihan Form PHP',
                'status_sesi' => 'Aktif',
            ],
        ],
    ];

    return view('dosen/listAbsensi', $data);
}


}
