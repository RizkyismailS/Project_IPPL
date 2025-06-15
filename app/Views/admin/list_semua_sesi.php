<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<?php
  $breadcrumb = 'Manajemen Sesi';
  $pageTitle = 'Daftar Semua Sesi';
  // Jika Anda punya header admin, bisa di-include di sini
  // echo view('layout/admin_header', compact('breadcrumb', 'pageTitle'));
?>

<section class="section">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Daftar Semua Sesi Absensi</h4>
            <p class="text-muted">Halaman ini menampilkan semua sesi absensi dari seluruh kelas dan dosen.</p>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Kelas</th>
                            <th>Topik Perkuliahan</th>
                            <th>Dosen</th>
                            <th class="text-center">Tanggal Sesi</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sesi)) : ?>
                            <tr>
                                <td colspan="5" class="text-center">Belum ada sesi absensi yang dibuat di sistem.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($sesi as $item) : ?>
                                <tr>
                                    <td class="text-bold-500"><?= esc($item->nama_kelas) ?></td>
                                    <td><?= esc($item->topik_perkuliahan) ?></td>
                                    <td><?= esc($item->nama_dosen) ?></td>
                                    <td class="text-center"><?= date('d M Y', strtotime($item->tanggal_sesi)) ?></td>
                                    <td class="text-center">
                                        <?php
                                            $status = $item->status;
                                            $badgeClass = 'bg-secondary';
                                            if ($status == 'aktif') $badgeClass = 'bg-success';
                                            if ($status == 'selesai') $badgeClass = 'bg-primary';
                                            if ($status == 'dibatalkan') $badgeClass = 'bg-danger';
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= esc(ucfirst($status)) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                <?= $pager->links('sesi_group', 'default_bootstrap') ?>
            </div>

        </div>
    </div>
</section>

<?= $this->endSection() ?>