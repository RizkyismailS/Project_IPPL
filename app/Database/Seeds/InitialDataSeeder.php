<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitialDataSeeder extends Seeder
{
    public function run()
    {
        // Call individual seeders in the correct order to respect foreign key constraints
        $this->call('MataKuliahSeeder');
        $this->call('DosenSeeder');
        $this->call('MahasiswaSeeder');
        $this->call('UserSeeder');
        $this->call('KelasSeeder');
        $this->call('EnrollmentSeeder');
        $this->call('SesiAbsensiSeeder');
        $this->call('KehadiranSeeder');
        $this->call('ActivityLogSeeder');
    }
}