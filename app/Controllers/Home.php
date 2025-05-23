<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('dosen/dashboard', [
            'sidebar' => 'layout/dosen_sidebar'
        ]);
    }
    public function kelas_baru()
    {
    return view('dosen/kelas_baru', [
        'title' => 'Buat Kelas Baru',
        'sidebar' => 'layout/dosen_sidebar',
    ]);
}
}
