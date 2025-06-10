<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class MahasiswaAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session(); // Get the session instance

        // Check if the client expects a JSON response (like Postman or an AJAX call)
        $wantsJson = $request->isAJAX() ||
                     strpos($request->getHeaderLine('Accept'), 'application/json') !== false ||
                     $request->getHeaderLine('Content-Type') === 'application/json';

        // 1. Check if the user is logged in
        if (!$session->get('isLoggedIn')) {
            log_message('info', '[MahasiswaAuthFilter] Access denied: User not logged in. Target URL: ' . (string)current_url());
            if ($wantsJson) {
                return service('response')->setStatusCode(401)->setJSON([
                    'status'  => 'error',
                    'message' => 'Access denied. You must be logged in first.'
                ]);
            }
            // For browser requests, redirect to the login page
            return redirect()->to(base_url('/'))->with('error', 'You must log in to access this page.');
        }

        // 2. Check if the user's role is 'mahasiswa'
        if ($session->get('role') !== 'mahasiswa') {
            log_message('warning', '[MahasiswaAuthFilter] Access denied: Role is not mahasiswa. Current role: ' . $session->get('role') . '. Target URL: ' . (string)current_url());
            if ($wantsJson) {
                return service('response')->setStatusCode(403)->setJSON([
                    'status'  => 'error',
                    'message' => 'Access denied. You do not have permission as a Student.'
                ]);
            }
            // For browser requests, redirect to the dashboard of their actual role
            $role = $session->get('role');
            if ($role === 'admin') {
                return redirect()->to(base_url('admin/dashboard'))->with('warning', 'You are not allowed to access the student area.');
            } elseif ($role === 'dosen') {
                return redirect()->to(base_url('dosen/dashboard'))->with('warning', 'You are not allowed to access the student area.');
            }
            // Fallback for unknown roles
            return redirect()->to(base_url('/'))->with('error', 'Access denied. Invalid role.');
        }

        log_message('info', '[MahasiswaAuthFilter] Access granted for mahasiswa. Target URL: ' . (string)current_url());
        // If all checks pass, do nothing and let the request continue to the controller
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after the controller runs for this filter
    }
}