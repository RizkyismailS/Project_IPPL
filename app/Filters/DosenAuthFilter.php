<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class DosenAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session(); // Dapatkan instance session

        // Cek apakah request kemungkinan besar dari API client (seperti Postman)
        $wantsJson = $request->isAJAX() ||
                     strpos($request->getHeaderLine('Accept'), 'application/json') !== false ||
                     $request->getHeaderLine('Content-Type') === 'application/json';

        // 1. Cek apakah pengguna sudah login
        if (!$session->get('isLoggedIn')) {
            log_message('info', '[DosenAuthFilter] Akses ditolak: Pengguna belum login. Target URL: ' . (string)current_url() . '. Wants JSON: ' . ($wantsJson ? 'yes' : 'no'));
            if ($wantsJson) {
                return service('response')->setStatusCode(401)->setJSON([
                    'status'  => 'error',
                    'message' => 'Akses ditolak. Anda harus login terlebih dahulu.'
                ]);
            }
            // Jika bukan request JSON (dari browser), redirect ke halaman login
            return redirect()->to(base_url('/'))->with('error', 'Anda harus login untuk mengakses halaman ini.');
        }

        // 2. Cek apakah peran pengguna adalah 'dosen'
        if ($session->get('role') !== 'dosen') {
            log_message('warning', '[DosenAuthFilter] Akses ditolak: Peran bukan dosen. Role saat ini: ' . $session->get('role') . '. Target URL: ' . (string)current_url() . '. Wants JSON: ' . ($wantsJson ? 'yes' : 'no'));
            if ($wantsJson) {
                return service('response')->setStatusCode(403)->setJSON([
                    'status'  => 'error',
                    'message' => 'Akses ditolak. Anda tidak memiliki hak sebagai Dosen.'
                ]);
            }
            // Untuk browser, redirect ke dashboard peran masing-masing jika mereka sudah login dengan peran lain
            $role = $session->get('role');
            if ($role === 'admin') {
                return redirect()->to(base_url('admin/dashboard'))->with('warning', 'Anda tidak diizinkan mengakses area dosen.');
            } elseif ($role === 'mahasiswa') {
                return redirect()->to(base_url('mahasiswa/dashboard'))->with('warning', 'Anda tidak diizinkan mengakses area dosen.');
            }
            // Jika peran tidak dikenal atau kasus lain, fallback ke halaman login dengan error
            return redirect()->to(base_url('/'))->with('error', 'Akses ditolak. Peran tidak valid.');
        }

        log_message('info', '[DosenAuthFilter] Akses diizinkan untuk dosen. Target URL: ' . (string)current_url());
        // Jika lolos semua pengecekan, jangan return apa-apa atau return $request agar request dilanjutkan
        // return $request; // Untuk CI 4.3+ jika Anda ingin memodifikasi request
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada aksi spesifik setelah controller untuk filter ini
    }
}