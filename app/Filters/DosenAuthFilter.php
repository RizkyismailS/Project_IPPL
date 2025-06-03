<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class DosenAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Check if user is logged in
        if (!session()->has('isLoggedIn')) {
            return redirect()->to('/login/auth')->with('error', 'Silakan login terlebih dahulu');
        }
        
        // Check if user role is dosen
        if (session()->get('role') !== 'dosen') {
            return redirect()->to('dosen/dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini');
            log_message('info', 'DOSEN_FILTER: Akses ditolak untuk dosen. Role: ' . session()->get('role'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing after the controller execution
    }
}