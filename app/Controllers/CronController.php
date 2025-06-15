<?php
// In your Routes.php file

// Create a CronController
class CronController extends BaseController
{
    public function updateSessionStatuses()
    {
        $sesiAbsensiModel = new \App\Models\SesiAbsensiModel();
        
        // Update sessions that have passed their end time
        $result1 = $sesiAbsensiModel->where('status', 'aktif')
                                   ->where('waktu_selesai_aktual <', date('Y-m-d H:i:s'))
                                   ->set(['status' => 'selesai'])
                                   ->update();
        
        // Update sessions that were missed
        $result2 = $sesiAbsensiModel->where('status', 'aktif')
                                   ->where('waktu_selesai_aktual <', date('Y-m-d H:i:s'))
                                   ->where("NOT EXISTS (SELECT 1 FROM kehadiran WHERE kehadiran.id_sesi = sesi_absensi.id_sesi)")
                                   ->set(['status' => 'terlewat'])
                                   ->update();
        
        log_message('info', "Session status update: {$result1} sessions marked complete, {$result2} sessions marked missed");
        return "Updated {$result1} + {$result2} sessions";
    }
}