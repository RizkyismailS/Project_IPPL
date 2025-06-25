<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DevResetSeeder extends Seeder
{
    public function run()
    {
        // Empty all tables in reverse order to avoid foreign key constraints
        $this->db->query('DELETE FROM activity_logs');
        $this->db->query('DELETE FROM kehadiran');
        $this->db->query('DELETE FROM sesi_absensi');
        $this->db->query('DELETE FROM enrollment');
        $this->db->query('DELETE FROM kelas');
        $this->db->query('DELETE FROM users');
        $this->db->query('DELETE FROM mahasiswa');
        $this->db->query('DELETE FROM dosen');
        $this->db->query('DELETE FROM matakuliah');

        // Reset auto-increment counters (if applicable)
        $this->db->query('ALTER TABLE activity_logs AUTO_INCREMENT = 1');
        $this->db->query('ALTER TABLE kehadiran AUTO_INCREMENT = 1');
        $this->db->query('ALTER TABLE sesi_absensi AUTO_INCREMENT = 1');
        $this->db->query('ALTER TABLE enrollment AUTO_INCREMENT = 1');
        $this->db->query('ALTER TABLE users AUTO_INCREMENT = 1');

        // Call the initial data seeder
        $this->call('InitialDataSeeder');
        
        echo "Database reset and reseeded successfully!\n";
    }
}