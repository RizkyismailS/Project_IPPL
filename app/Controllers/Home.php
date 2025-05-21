<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('admin/dashboard', [
            'header' => 'layout/header',
            'sidebar' => 'layout/admin_sidebar'
        ]);
    }
}
