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
}
