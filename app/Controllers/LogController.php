<?php
// app/Controllers/LogController.php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ActivityLogModel;

class LogController extends BaseController
{
    protected $activityLogModel;
    
    public function __construct()
    {
        $this->activityLogModel = new ActivityLogModel();
    }
    
    public function index()
    {
        // Check if user is admin
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to(base_url('login'))->with('error', 'You do not have permission to view logs');
        }
        
        $page = $this->request->getVar('page') ?? 1;
        $perPage = 20;
        
        // Get filters from request
        $filters = [
            'role' => $this->request->getVar('role'),
            'action' => $this->request->getVar('action'),
            'reference_id' => $this->request->getVar('reference_id'),
        ];
        
        // Remove null values
        $filters = array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });
        
        $offset = ($page - 1) * $perPage;
        
        $logs = $this->activityLogModel->getActivityLogs($perPage, $offset, $filters);
        $totalLogs = $this->activityLogModel->countLogs($filters);
        
        $data = [
            'title' => 'Activity Logs',
            'logs' => $logs,
            'pager' => [
                'currentPage' => $page,
                'perPage' => $perPage,
                'total' => $totalLogs,
                'totalPages' => ceil($totalLogs / $perPage)
            ],
            'filters' => $filters,
            'nama_user' => session()->get('nama_lengkap') ?? session()->get('username'),
        ];
        
        return view('admin/logs', $data);
    }
    
    public function userLogs($userId = null)
    {
        // For user-specific logs (can be used for dosen/mahasiswa to view their own logs)
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('login'));
        }
        
        $currentUserId = session()->get('id_user');
        $currentUserRole = session()->get('role');
        
        // Only admin can view other users' logs
        if ($userId !== null && $userId != $currentUserId && $currentUserRole !== 'admin') {
            return redirect()->to(base_url('dashboard'))->with('error', 'You do not have permission to view these logs');
        }
        
        // If userId is not specified, use current user's ID
        $userId = $userId ?? $currentUserId;
        
        $page = $this->request->getVar('page') ?? 1;
        $perPage = 20;
        
        $filters = [
            'id_user' => $userId,
            'action' => $this->request->getVar('action'),
        ];
        
        $offset = ($page - 1) * $perPage;
        
        $logs = $this->activityLogModel->getActivityLogs($perPage, $offset, $filters);
        $totalLogs = $this->activityLogModel->countLogs($filters);
        
        $data = [
            'title' => 'My Activity Logs',
            'logs' => $logs,
            'pager' => [
                'currentPage' => $page,
                'perPage' => $perPage,
                'total' => $totalLogs,
                'totalPages' => ceil($totalLogs / $perPage)
            ],
            'nama_user' => session()->get('nama_lengkap') ?? session()->get('username'),
        ];
        
        $viewPath = ($currentUserRole === 'admin') ? 'admin/user_logs' : $currentUserRole . '/my_logs';
        return view($viewPath, $data);
    }
}