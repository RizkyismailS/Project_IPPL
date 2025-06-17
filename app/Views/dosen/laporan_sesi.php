<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<?php
  // Menyiapkan data untuk header halaman
  $breadcrumb = 'Laporan Sesi';
  $pageTitle = 'Laporan Kehadiran';
  echo view('layout/dosen_header', compact('breadcrumb', 'pageTitle'));
?>

<section class="section">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="card-title">Laporan Sesi: <?= esc($sesi['topik_perkuliahan']) ?></h4>
                <a href="http://projectippl.test/dosen/kelas/detail/<?= esc($sesi['kode_kelas']) ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <p class="text-muted">
                Tanggal: <?= date('d F Y', strtotime($sesi['tanggal_sesi'])) ?>
            </p>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
    <tr>
        <th style="width: 5%;">No</th>
        <th>NIM</th>
        <th>Nama Mahasiswa</th>
        <th class="text-center">Status Kehadiran</th>
        <th class="text-center">Bukti Foto</th> <!-- Add this -->
    </tr>
</thead>
<tbody>
    <?php if (empty($laporan)) : ?>
        <tr>
            <td colspan="5" class="text-center">Tidak ada mahasiswa yang terdaftar di kelas ini.</td>
        </tr>
    <?php else : ?>
        <?php $no = 1; ?>
        <?php foreach ($laporan as $item) : ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= esc($item['nim']) ?></td>
                <td><?= esc($item['nama']) ?></td>
                <td class="text-center">
                    <?php
                        $status = $item['status_kehadiran'] ?? $item['status_absen'] ?? '';
                        $badgeClass = 'bg-secondary';
                        if ($status == 'Hadir') $badgeClass = 'bg-success';
                        if ($status == 'Sakit') $badgeClass = 'bg-warning';
                        if ($status == 'Izin') $badgeClass = 'bg-info';
                        if ($status == 'Alpa') $badgeClass = 'bg-danger';
                    ?>
                    <span class="badge <?= $badgeClass ?>"><?= esc($status) ?></span>
                </td>
                <td class="text-center">
                    <?php if (!empty($item['path_bukti_foto'])): ?>
                        <a href="<?= base_url($item['path_bukti_foto']) ?>" target="_blank">
                            <img src="<?= base_url($item['path_bukti_foto']) ?>" alt="Bukti Foto" style="max-width:60px;max-height:60px;">
                        </a>
                    <?php else: ?>
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>