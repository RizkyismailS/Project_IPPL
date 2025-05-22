<?php
// app/Controllers/Mahasiswa/Dashboard.php
namespace App\Controllers\Mahasiswa;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        return view('deosen/dashboard', [
            'title' => 'Dashboard Mahasiswa',
            'sidebar' => 'layout/dosen_sidebar',
        ]);
    }
}
