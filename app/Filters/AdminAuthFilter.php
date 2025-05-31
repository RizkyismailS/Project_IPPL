<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AdminAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        // Cek apakah request kemungkinan besar dari API client (seperti Postman)
        $wantsJson = $request->isAJAX() ||
                    strpos($request->getHeaderLine('Accept'), 'application/json') !== false ||
                    $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest'; // Umum untuk AJAX

        if (!$session->get('isLoggedIn')) {
            log_message('info', 'ADMIN_FILTER: Not logged in. Wants JSON: ' . ($wantsJson ? 'yes' : 'no'));
            if ($wantsJson) {
                return service('response')->setStatusCode(401)->setJSON([
                    'status' => 'error',
                    'message' => 'Akses ditolak. Anda harus login terlebih dahulu.'
                ]);
            }
            return redirect()->to(base_url('login/auth'))->with('error', 'Anda harus login untuk mengakses halaman ini.');
        }

        if ($session->get('role') !== 'admin') {
            log_message('info', 'ADMIN_FILTER: Role not admin. Current role: ' . $session->get('role') . '. Wants JSON: ' . ($wantsJson ? 'yes' : 'no'));
            if ($wantsJson) {
                return service('response')->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Akses ditolak. Anda tidak memiliki hak sebagai Admin.'
                ]);
            }
            // Untuk browser, redirect ke dashboard peran masing-masing
            $role = $session->get('role');
            if ($role === 'mahasiswa') {
                return redirect()->to(base_url('mahasiswa/dashboard'))->with('warning', 'Anda tidak diizinkan mengakses area admin.');
            } elseif ($role === 'dosen') {
                return redirect()->to(base_url('dosen/dashboard'))->with('warning', 'Anda tidak diizinkan mengakses area admin.');
            }
            // Jika peran tidak dikenal, fallback ke halaman utama atau login
            return redirect()->to(base_url('/'))->with('error', 'Akses ditolak.');
        }

        log_message('info', 'ADMIN_FILTER: Access granted for admin.');
        // Jangan return apa-apa atau return $request agar request dilanjutkan ke controller
        // return $request; // Untuk CI 4.3+ jika Anda ingin memodifikasi request
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Kosongkan jika tidak ada aksi setelah controller
    }
}