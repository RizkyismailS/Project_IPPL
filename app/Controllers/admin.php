<?php
// app/Controllers/Admin/Dashboard.php
namespace App\Controllers;

use App\Controllers\BaseController;

class Admin extends BaseController
{
    public function index()
    {
        return view('admin/dashboard', [
            'title' => 'Dashboard Admin',
            'sidebar' => 'layout/admin_sidebar'
        ]);
    }
    public function manageDosen()
    {
        return view('admin/manage_dosen', [
            'title' => 'Manage Dosen',
            'sidebar' => 'layout/admin_sidebar'
        ]);
    }
    public function manageMahasiswa()
    {
        return view('admin/manage_mhs', [
            'title' => 'Manage Mahasiswa',
            'sidebar' => 'layout/admin_sidebar'
        ]);
    }
}
