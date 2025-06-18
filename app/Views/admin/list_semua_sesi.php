<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<?php
  $breadcrumb = 'Manajemen Sesi';
  $pageTitle = 'Daftar Semua Sesi';
  // Jika Anda punya header admin, bisa di-include di sini
  // echo view('layout/admin_header', compact('breadcrumb', 'pageTitle'));
?>

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Manajemen Sesi Absensi</h3>
                <p class="text-subtitle text-muted">Daftar semua sesi absensi dari seluruh kelas.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= base_url('/admin/dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Sesi Absensi</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Kode Kelas</th>
                            <th>Nama Kelas</th>
                            <th>Topik Perkuliahan</th>
                            <th>Dosen</th>
                            <th class="text-center">Tanggal Sesi</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sesi)) : ?>
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data sesi absensi</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($sesi as $s) : ?>
                                <tr>
                                    <td><?= esc($s['kode_kelas']) ?></td>
                                    <td><?= esc($s['nama_kelas']) ?></td>
                                    <td><?= esc($s['topik_perkuliahan']) ?></td>
                                    <td><?= esc($s['nama_dosen']) ?></td>
                                    <td class="text-center">
                                        <?= date('d M Y', strtotime($s['tanggal_sesi'])) ?>
                                        <small class="d-block text-muted">
                                            <?= date('H:i', strtotime($s['waktu_mulai_aktual'])) ?> - 
                                            <?= date('H:i', strtotime($s['waktu_selesai_aktual'])) ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($s['status'] == 'aktif'): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php elseif ($s['status'] == 'selesai'): ?>
                                            <span class="badge bg-secondary">Selesai</span>
                                        <?php elseif ($s['status'] == 'batal'): ?>
                                            <span class="badge bg-danger">Dibatalkan</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning"><?= ucfirst(esc($s['status'])) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= base_url('/admin/sesi/detail/' . $s['id_sesi']) ?>" 
                                           class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($pager) : ?>
                <div class="mt-4">
                    <?= $pager->links('sesi_group', 'default_full') ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>