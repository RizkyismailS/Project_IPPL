<?php
// app/Controllers/Mahasiswa/Dashboard.php
namespace App\Controllers\Mahasiswa;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        return view('mahasiswa/dashboard', [
            'title' => 'Dashboard Mahasiswa',
            'header' => 'layout/header',
            'sidebar' => 'layout/student_sidebar'
        ]);
    }
}
