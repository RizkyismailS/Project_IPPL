<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class KehadiranSeeder extends Seeder
{
    public function run()
    {
        // First, get the IDs of existing sessions with status 'selesai'
        $completedSessions = $this->db->table('sesi_absensi')
            ->select('id_sesi')
            ->where('status', 'selesai')
            ->get()
            ->getResultArray();

        if (empty($completedSessions)) {
            echo "No completed sessions found. Skipping attendance records.\n";
            return;
        }

        $now = date('Y-m-d H:i:s');
        $data = [];

        foreach ($completedSessions as $session) {
            $sessionId = $session['id_sesi'];
            
            // Get students enrolled in this class
            $classCode = $this->db->table('sesi_absensi')
                ->select('kode_kelas')
                ->where('id_sesi', $sessionId)
                ->get()
                ->getRowArray();
                
            // Check if we got a class code
            if (!$classCode || !isset($classCode['kode_kelas'])) {
                echo "No class code found for session ID $sessionId. Skipping.\n";
                continue;
            }
            
            $classCode = $classCode['kode_kelas'];
                
            $enrolledStudents = $this->db->table('enrollment')
                ->select('nim_mahasiswa')
                ->where('kode_kelas_enrolled', $classCode)
                ->where('status_enrollment', 'aktif')
                ->get()
                ->getResultArray();
                
            if (empty($enrolledStudents)) {
                echo "No enrolled students found for class $classCode. Skipping.\n";
                continue;
            }
            
            foreach ($enrolledStudents as $student) {
                $nim = $student['nim_mahasiswa'];
                
                // Randomly decide attendance status with weighted probability
                $statuses = ['hadir', 'izin', 'sakit', 'alpa'];
                $weightedOptions = [
                    0, 0, 0, 0, 0, 0, 0,  // 7 chances for 'hadir' (70%)
                    1,                     // 1 chance for 'izin' (10%)
                    2,                     // 1 chance for 'sakit' (10%) 
                    3                      // 1 chance for 'alpa' (10%)
                ];
                
                $randomIndex = array_rand($weightedOptions);
                $statusIndex = $weightedOptions[$randomIndex];
                $status = $statuses[$statusIndex];
                
                // Get session start time
                $sessionInfo = $this->db->table('sesi_absensi')
                    ->select('waktu_mulai_aktual')
                    ->where('id_sesi', $sessionId)
                    ->get()
                    ->getRowArray();
                    
                if (!$sessionInfo || !isset($sessionInfo['waktu_mulai_aktual'])) {
                    echo "No start time found for session ID $sessionId. Skipping attendance for $nim.\n";
                    continue;
                }
                
                $attendanceTime = $sessionInfo['waktu_mulai_aktual'];
                
                // Add some random minutes (0-20)
                $attendanceTime = date('Y-m-d H:i:s', strtotime($attendanceTime . ' +' . rand(0, 20) . ' minutes'));
                
                $data[] = [
                    'nim' => $nim,
                    'id_sesi' => $sessionId,
                    'status_absen' => $status,
                    'waktu_absen' => $attendanceTime,
                    'keterangan' => $status == 'hadir' ? null : 'Keterangan untuk ' . $status,
                    'path_bukti_foto' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        
        if (!empty($data)) {
            try {
                // Insert data
                $this->db->table('kehadiran')->insertBatch($data);
                echo count($data) . " attendance records created.\n";
            } catch (\Exception $e) {
                echo "Error inserting attendance records: " . $e->getMessage() . "\n";
            }
        } else {
            echo "No attendance records to insert.\n";
        }
    }
}