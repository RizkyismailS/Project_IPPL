<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table            = 'activity_logs';
    protected $primaryKey       = 'id_log';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'user_id', 
        'reference_id', 
        'role', 
        'action', 
        'description', 
        'ip_address', 
        'user_agent', 
        'related_table', 
        'related_id', 
        'created_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';  // No need for updated_at in logs
    protected $deletedField  = '';  // No need for deleted_at in logs

    // Helper method to create activity log
    public function logActivity($userId, $referenceId, $role, $action, $description, $relatedTable = null, $relatedId = null)
    {
        $request = \Config\Services::request();
        
        return $this->insert([
            'user_id' => $userId,
            'reference_id' => $referenceId,
            'role' => $role,
            'action' => $action,
            'description' => $description,
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent()->__toString(),
            'related_table' => $relatedTable,
            'related_id' => $relatedId,
        ]);
    }
    
    // Get logs with pagination
    public function getActivityLogs($limit = 20, $offset = 0, $filters = [])
    {
        $builder = $this->builder();
        
        if (!empty($filters)) {
            foreach ($filters as $field => $value) {
                $builder->where($field, $value);
            }
        }
        
        return $builder->orderBy('created_at', 'DESC')
                      ->limit($limit, $offset)
                      ->get()
                      ->getResultArray();
    }
    
    // Count total logs for pagination
    public function countLogs($filters = [])
    {
        $builder = $this->builder();
        
        if (!empty($filters)) {
            foreach ($filters as $field => $value) {
                $builder->where($field, $value);
            }
        }
        
        return $builder->countAllResults();
    }
}