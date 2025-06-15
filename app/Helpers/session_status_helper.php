<?php

/**
 * Determine the display status of a session based on its database status,
 * time, and the user's role and attendance record.
 *
 * @param array $session The session data
 * @param string|null $attendanceStatus User's attendance status if available
 * @param string $userRole Either 'dosen' or 'mahasiswa'
 * @param int|null $currentTimestamp Optional custom timestamp for testing
 * @return string The calculated status to display
 */
function calculate_session_status(array $session, ?string $attendanceStatus = null, string $userRole = 'dosen', ?int $currentTimestamp = null) 
{
    $waktu_sekarang = $currentTimestamp ?? time();
    $waktu_mulai_sesi = strtotime($session['waktu_mulai_aktual']);
    $waktu_selesai_sesi = strtotime($session['waktu_selesai_aktual'] ?? date('Y-m-d H:i:s', $waktu_mulai_sesi + 3600)); // Default to 1 hour later
    
    // If student has already recorded attendance, show that status
    if ($userRole === 'mahasiswa' && !empty($attendanceStatus)) {
        return $attendanceStatus;
    }
    
    // Database status takes precedence
    if (in_array($session['status'], ['dibatalkan'])) {
        return ucfirst($session['status']); // 'Dibatalkan'
    }
    
    if ($session['status'] === 'terlewat') {
        return $userRole === 'mahasiswa' ? 'Alpa' : 'Terlewat';
    }
    
    if ($session['status'] === 'selesai') {
        return $userRole === 'mahasiswa' && empty($attendanceStatus) ? 'Alpa' : 'Selesai';
    }
    
    // Time-based status for active sessions
    if ($session['status'] === 'aktif') {
        if ($waktu_sekarang >= $waktu_mulai_sesi && $waktu_sekarang <= $waktu_selesai_sesi) {
            return $userRole === 'mahasiswa' && empty($attendanceStatus) ? 'ABSEN_SEKARANG' : 'Aktif';
        } else if ($waktu_sekarang < $waktu_mulai_sesi) {
            return 'Akan Datang';
        } else {
            // Session time has passed but database status is still 'aktif'
            return $userRole === 'mahasiswa' && empty($attendanceStatus) ? 'Alpa' : 'Selesai';
        }
    }
    
    return 'Tidak Diketahui';
}