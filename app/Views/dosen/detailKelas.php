<?= $this->extend('layout/template') ?> 

<?= $this->section('content') ?>
<?php
$breadcrumb = 'Detail Kelas';
$pageTitle = $title ?? 'Detail Kelas';
echo view('layout/dosen_header', compact('breadcrumb', 'pageTitle')); 
?>

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
             
                <?php if (isset($kelas['nama_kelas'])): ?>
                    <p class="text-subtitle text-muted">Informasi lengkap untuk kelas <?= esc($kelas['nama_kelas']) ?>.</p>
                <?php endif; ?>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= base_url('dosen/dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('dosen/kelas') ?>">Kelola Kelas</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detail Kelas</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<section class="section">
    <?php if (empty($kelas)): ?>
        <div class="alert alert-danger">Detail kelas tidak ditemukan atau Anda tidak memiliki akses.</div>
    <?php else: ?>
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Informasi Kelas: <?= esc($kelas['nama_kelas']) ?></h4>
                    <div>
                        <a href="<?= base_url('dosen/kelas/edit/' . esc($kelas['kode_kelas'], 'url')) ?>" class="btn btn-primary btn-sm">
                            <i class="bi bi-pencil-square"></i> Edit Kelas
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tbody>
                                <tr><th style="width: 40%;">Kode Kelas</th><td>: <?= esc($kelas['kode_kelas']) ?></td></tr>
                                <tr><th>Nama Kelas</th><td>: <?= esc($kelas['nama_kelas']) ?></td></tr>
                                <tr><th>Mata Kuliah</th><td>: <?= esc($kelas['nama_matakuliah']) ?> (<?= esc($kelas['sks']) ?> SKS)</td></tr>
                                <tr><th>Dosen Pengampu</th><td>: <?= esc($kelas['nama_dosen']) ?> (<?= esc($kelas['dosen_nip']) ?>)</td></tr>
                                <tr><th>Kode Enrollment</th><td>: <code><?= esc($kelas['kode_enrollment']) ?></code></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tbody>
                                <tr><th style="width: 40%;">Jadwal</th><td>: <?= esc($kelas['hari']) ?>, <?= esc(date('H:i', strtotime($kelas['waktu_mulai_kelas']))) ?> - <?= esc(date('H:i', strtotime($kelas['waktu_selesai_kelas']))) ?></td></tr>
                                <tr><th>Ruangan</th><td>: <?= esc($kelas['ruangan'] ?? '-') ?></td></tr>
                                <tr><th>Tahun Ajaran</th><td>: <?= esc($kelas['tahun_ajaran']) ?></td></tr>
                                <tr><th>Semester</th><td>: <?= esc($kelas['semester']) ?></td></tr>
                                <tr><th>Dibuat Pada</th><td>: <?= esc(CodeIgniter\I18n\Time::parse($kelas['created_at'])->toLocalizedString('dd MMMM yyyy HH:mm')) ?></td></tr>
                                <tr><th>Diupdate Pada</th><td>: <?= esc(CodeIgniter\I18n\Time::parse($kelas['updated_at'])->toLocalizedString('dd MMMM yyyy HH:mm')) ?></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <hr>
                <h5>Mahasiswa Terdaftar (<?= $jumlah_mahasiswa_terdaftar ?? 0 ?>)</h5>
                <?php if (!empty($mahasiswa_terdaftar) && is_array($mahasiswa_terdaftar)): ?>
                <?php else: ?>
                    <p class="text-muted">Belum ada mahasiswa yang terdaftar di kelas ini.</p>
                <?php endif; ?>

                <hr>
                <h5>Sesi Absensi</h5>
                <a href="<?= base_url('dosen/sesi-absensi/create/' . esc($kelas['kode_kelas'], 'url')) ?>" class="btn btn-outline-success btn-sm mb-3">
                    <i class="bi bi-plus-lg"></i> Tambah Sesi Absensi Baru
                </a>
                <?php if (!empty($sesi_absensi_list) && is_array($sesi_absensi_list)): ?>
                    {/* Tampilkan tabel sesi absensi */}
                <?php else: ?>
                    <p class="text-muted">Belum ada sesi absensi yang dibuat untuk kelas ini.</p>
                <?php endif; ?>
                
            </div>
            <div class="card-footer">
                <a href="<?= base_url('dosen/kelas') ?>" class="btn btn-light">Kembali ke Daftar Kelas</a>
            </div>
        </div>
    <?php endif; ?>
</section>

<?= $this->endSection() ?>