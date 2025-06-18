<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\SesiAbsensiModel;
use App\Models\EnrollmentModel;
use App\Models\KehadiranModel;

class MarkAlpaCommand extends BaseCommand
{
    protected $group = 'Attendance';
    protected $name = 'attendance:mark-alpa';
    protected $description = 'Mark mahasiswa as alpa for sessions they missed.';

    public function run(array $params)
    {
        $sesiModel = new SesiAbsensiModel();
        $enrollModel = new EnrollmentModel();
        $kehadiranModel = new KehadiranModel();

        $now = date('Y-m-d H:i:s');
        $sessions = $sesiModel->where('waktu_selesai_aktual <', $now)
            ->where('status', 'selesai')
            ->findAll();

        CLI::write('Jumlah sesi ditemukan: ' . count($sessions));

        foreach ($sessions as $sesi) {
            CLI::write("Sesi: {$sesi['id_sesi']} ({$sesi['kode_kelas']})");

            $mahasiswaList = $enrollModel->where('kode_kelas_enrolled', $sesi['kode_kelas'])
                ->where('status_enrollment', 'aktif')
                ->findAll();

            CLI::write('  Jumlah mahasiswa: ' . count($mahasiswaList));

            foreach ($mahasiswaList as $mhs) {
                CLI::write("    Cek mahasiswa: {$mhs['nim_mahasiswa']}");

                $exists = $kehadiranModel->where('id_sesi', $sesi['id_sesi'])
                    ->where('nim', $mhs['nim_mahasiswa'])
                    ->first();
                if (!$exists) {
                    CLI::write("      Insert alpa untuk: {$mhs['nim_mahasiswa']}");
                    $kehadiranModel->insert([
                        'nim' => $mhs['nim_mahasiswa'],
                        'id_sesi' => $sesi['id_sesi'],
                        'status_absen' => 'alpa',
                        'waktu_absen' => $sesi['waktu_selesai_aktual'],
                        'keterangan' => 'Tidak absen',
                    ]);
                } else {
                    CLI::write("      Sudah ada kehadiran.");
                }
            }
            $sesiModel->update($sesi['id_sesi'], ['status' => 'selesai']);
        }

        CLI::write('Proses penandaan alpa selesai.', 'green');
    }
}