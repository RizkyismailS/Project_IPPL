<?php
// app/Controllers/Admin/Dashboard.php
namespace App\Controllers\Admin;

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
}
