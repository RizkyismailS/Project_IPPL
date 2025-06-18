<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>


<div class="main-card">
    <h3 class="mb-4">Dashboard Admin</h3>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="p-3 bg-white rounded shadow-sm text-center">
                <div>Total Dosen</div>
                <h4><?= $total_dosen ?? 0 ?></h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white rounded shadow-sm text-center">
                <div>Total Mahasiswa</div>
                <h4><?= $total_mahasiswa ?? 0 ?></h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white rounded shadow-sm text-center">
                <div>Kelas Aktif</div>
                <h4><?= $total_kelas_aktif ?? 0 ?></h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white rounded shadow-sm text-center">
                <div>Sesi Aktif</div>
                <h4><?= $total_sesi_aktif ?? 0 ?></h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="bg-white p-3 rounded shadow-sm">
                <h5>Aktifitas Sesi Absensi</h5>
                <ul class="list-group">
                    <?php if (!empty($aktifitas_sesi)): ?>
                        <?php foreach ($aktifitas_sesi as $sesi): ?>
                            <?php if ($sesi['waktu_selesai_kelas'] != $sesi['hitung_waktu']->format('H:i:s')): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center bg-success text-white">
                                    <?= $sesi['nama_kelas'] ?> <span><?= $sesi['hitung_waktu']->format('i') ?></span>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item">Tidak ada sesi aktif.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="bg-white p-3 rounded shadow-sm">
                <h5>Notifikasi System</h5>
                <ul class="list-group">
                    <li class="list-group-item"><i class="bi bi-info-circle"></i> New lecturer account created for Dr.
                        Emily White</li>
                    <li class="list-group-item"><i class="bi bi-check-circle"></i> Database Systems class attendance
                        completed</li>
                    <li class="list-group-item"><i class="bi bi-exclamation-circle"></i> System backup scheduled for
                        tonight at 2 AM</li>
                </ul>
            </div>
        </div>
        <div class="col-md-12 mt-4">
            <div class="card mb-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div><i class="fas fa-history me-1"></i> Recent Activity</div>
            <a href="<?= base_url('admin/logs') ?>" class="btn btn-sm btn-primary">View All Logs</a>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recent_logs)): ?>
                    <?php foreach ($recent_logs as $log): ?>
                        <tr>
                            <td><?= date('H:i:s', strtotime($log['created_at'])) ?></td>
                            <td><?= esc($log['reference_id']) ?></td>
                            <td><?= ucfirst(esc($log['role'])) ?></td>
                            <td><?= str_replace('_', ' ', ucwords(esc($log['action']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No recent activity</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

        </div>
    </div>

    
</div>

<?= $this->endSection() ?>