<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\SesiAbsensiModel;

class UpdateSessionStatus extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'session:update-status';
    protected $description = 'Updates the status of attendance sessions based on time';

    public function run(array $params)
    {
        $sesiModel = new SesiAbsensiModel();
        $now = date('Y-m-d H:i:s');
        CLI::write('Starting session status update at: ' . $now, 'yellow');

        // Sessions that have passed their end time -> 'selesai'
        $endedActiveSessions = $sesiModel->where('status', 'aktif')
            ->where('waktu_selesai_aktual <', $now)
            ->findAll();
            
        if (count($endedActiveSessions) > 0) {
            $sesiModel->where('status', 'aktif')
                ->where('waktu_selesai_aktual <', $now)
                ->set(['status' => 'selesai'])
                ->update();
                
            CLI::write('Updated ' . count($endedActiveSessions) . ' ended sessions to "selesai" status', 'green');
        } else {
            CLI::write('No ended sessions to update', 'blue');
        }

        // Sessions past their time without any attendance records -> 'terlewat'
        $db = \Config\Database::connect();
        $subquery = $db->table('kehadiran')
            ->select('1')
            ->where('kehadiran.id_sesi = sesi_absensi.id_sesi', null, false);
            
        $skippedSessions = $sesiModel->builder()
            ->where('status', 'aktif')
            ->where('waktu_selesai_aktual <', $now)
            ->where("NOT EXISTS ({$subquery->getCompiledSelect()})", null, false)
            ->get()
            ->getResultArray();
            
        if (count($skippedSessions) > 0) {
            $sesiModel->builder()
                ->where('status', 'aktif')
                ->where('waktu_selesai_aktual <', $now)
                ->where("NOT EXISTS ({$subquery->getCompiledSelect()})", null, false)
                ->set(['status' => 'terlewat'])
                ->update();
                
            CLI::write('Updated ' . count($skippedSessions) . ' skipped sessions to "terlewat" status', 'green');
        } else {
            CLI::write('No skipped sessions to update', 'blue');
        }
        
        CLI::write('Session status update completed successfully!', 'green');
        return 0;
    }
}