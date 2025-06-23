<?php
?>
<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<div class="main-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Dashboard Admin</h3>
        <a href="<?= base_url('admin/update-session-status') ?>" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-sync-alt"></i> Update Session Status
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="p-3 bg-white rounded shadow-sm text-center">
                <div class="mb-2"><i class="fas fa-chalkboard-teacher fa-2x text-primary"></i></div>
                <div class="text-muted">Total Dosen</div>
                <h4><?= $total_dosen ?? 0 ?></h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white rounded shadow-sm text-center">
                <div class="mb-2"><i class="fas fa-user-graduate fa-2x text-success"></i></div>
                <div class="text-muted">Total Mahasiswa</div>
                <h4><?= $total_mahasiswa ?? 0 ?></h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white rounded shadow-sm text-center">
                <div class="mb-2"><i class="fas fa-school fa-2x text-warning"></i></div>
                <div class="text-muted">Kelas Aktif</div>
                <h4><?= $total_kelas_aktif ?? 0 ?></h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white rounded shadow-sm text-center">
                <div class="mb-2"><i class="fas fa-clipboard-check fa-2x text-info"></i></div>
                <div class="text-muted">Sesi Aktif</div>
                <h4><?= $total_sesi_aktif ?? 0 ?></h4>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Active Sessions -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-broadcast-tower me-2"></i>Sesi Absensi Aktif</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($aktifitas_sesi)): ?>
                        <ul class="list-group">
                            <?php foreach ($aktifitas_sesi as $sesi): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= esc($sesi['nama_kelas']) ?></strong>
                                        <div class="text-muted small">
                                            <?= date('H:i', strtotime($sesi['waktu_mulai_kelas'])) ?> - 
                                            <?= date('H:i', strtotime($sesi['waktu_selesai_kelas'])) ?>
                                        </div>
                                    </div>
                                    <span class="badge bg-success rounded-pill">
                                        <?= floor((strtotime($sesi['waktu_selesai_kelas']) - time()) / 60) ?> menit tersisa
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>Tidak ada sesi aktif saat ini
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- System Notifications -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Notifikasi System</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($notifications)): ?>
                        <ul class="list-group">
                            <?php foreach ($notifications as $notification): ?>
                                <li class="list-group-item">
                                    <i class="<?= esc($notification['icon']) ?>"></i>
                                    <?= esc($notification['message']) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <ul class="list-group">
                            <li class="list-group-item">
                                <i class="fas fa-check-circle text-success me-2"></i> System running normally
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-info-circle text-info me-2"></i> Database backup scheduled for tonight
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-exclamation-circle text-warning me-2"></i> 
                                <?= $total_sesi_aktif ?> active attendance sessions
                            </li>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Activity Logs -->
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-history me-2"></i>Recent Activity</div>
                        <a href="<?= base_url('admin/logs') ?>" class="btn btn-sm btn-primary">View All Logs</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_logs)): ?>
                                    <?php foreach ($recent_logs as $log): ?>
                                        <tr>
                                            <td><?= date('H:i:s', strtotime($log['created_at'])) ?></td>
                                            <td><?= esc($log['reference_id']) ?></td>
                                            <td><span class="badge bg-<?= $log['role'] == 'admin' ? 'danger' : ($log['role'] == 'dosen' ? 'primary' : 'success') ?>">
                                                <?= ucfirst(esc($log['role'])) ?>
                                            </span></td>
                                            <td><?= str_replace('_', ' ', ucwords(esc($log['action']))) ?></td>
                                            <td class="text-truncate" style="max-width: 200px;">
                                                <?= esc($log['description'] ?? '') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No recent activity</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>