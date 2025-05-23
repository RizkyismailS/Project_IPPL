<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<div class="page-heading">
    <h3>Buat Sesi Absensi</h3>
</div>

<section class="section">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title">List Sesi Absensi</h5>
                <a href="/dosen/absensi" class="btn btn-warning">Buat Sesi Absensi</a>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID_Sesi</th>
                            <th>ID_Kelas</th>
                            <th>Kode Sesi</th>
                            <th>Tanggal</th>
                            <th>Waktu_Mulai</th>
                            <th>Waktu_Selesai</th>
                            <th>Mode</th>
                            <th>Deskripsi</th>
                            <th>Status_Sesi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($Absensi as $A): ?>
                            <tr>
                                <td><?= esc($A['id_sesi']) ?></td>
                                <td><?= esc($A['id_kelas']) ?></td>
                                <td><a href="#"><?= esc($A['kode_sesi']) ?></a></td>
                                <td><?= esc($A['tanggal']) ?></td>
                                <td><?= esc($A['waktu_mulai']) ?></td>
                                <td><?= esc($A['waktu_selesai']) ?></td>
                                <td><?= esc($A['mode']) ?></td>
                                <td><?= esc($A['deskripsi']) ?></td>
                                <td><span class="badge bg-success"><?= esc($A['status_sesi']) ?></span></td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3 text-center">
                <nav>
                    <ul class="pagination pagination-sm justify-content-center">
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
