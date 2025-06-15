<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>
<?php

  $breadcrumb = 'Absensi';
  $pageTitle = 'List Absensi';
  echo view('layout/dosen_header', compact('breadcrumb', 'pageTitle'));
?>

<section class="section">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title">List Sesi Absensi</h5>
                <a href="/dosen/kelas" class="btn btn-secondary">Kembali ke List Kelas</a>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Topik Perkuliahan</th>
                            <th>Tanggal</th>
                            <th>Waktu Mulai</th>
                            <th>Status Sesi</th>
                            <th>Aksi</th> </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($Absensi as $A): ?>
                            <tr>
                                <td><?= esc($A['topik_perkuliahan']) ?></td>
                                <td><?= esc(date('d M Y', strtotime($A['tanggal_sesi']))) ?></td>
                                <td><?= esc(date('H:i', strtotime($A['waktu_mulai_aktual']))) ?> WIB</td>
                                <td>
                                    <?php if ($A['status'] == 'aktif') : ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php elseif ($A['status'] == 'selesai') : ?>
                                        <span class="badge bg-secondary">Selesai</span>
                                    <?php else : ?>
                                        <span class="badge bg-warning"><?= esc(ucfirst($A['status'])) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/dosen/laporan-sesi/<?= $A['id_sesi'] ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-file-earmark-text"></i> Laporan
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
            </div>
    </div>
</section>

<?= $this->endSection() ?>