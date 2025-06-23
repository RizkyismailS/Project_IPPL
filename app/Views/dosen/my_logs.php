<?php
?>
<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid px-4">
    <h1 class="mt-4">My Activity Logs</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= base_url(session()->get('role') . '/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item active">My Activity Logs</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter me-1"></i>
            Filter Logs
        </div>
        <div class="card-body">
            <form action="<?= base_url(session()->get('role') . '/logs') ?>" method="get">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="action">Action:</label>
                            <select class="form-select" name="action" id="action">
                                <option value="">All Actions</option>
                                <option value="login">Login</option>
                                <option value="logout">Logout</option>
                                <?php if(session()->get('role') === 'dosen'): ?>
                                <option value="create_absensi_session">Create Absensi Session</option>
                                <option value="update_absensi_session">Update Absensi Session</option>
                                <option value="create_class">Create Class</option>
                                <?php endif; ?>
                                <?php if(session()->get('role') === 'mahasiswa'): ?>
                                <option value="submit_attendance">Submit Attendance</option>
                                <option value="enroll_class">Enroll Class</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block w-100">Filter</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Your Activity Log History
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($logs)): ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?></td>
                                    <td><?= str_replace('_', ' ', ucwords(esc($log['action']))) ?></td>
                                    <td><?= esc($log['description']) ?></td>
                                    <td><?= esc($log['ip_address']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No activity logs found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($pager['totalPages'] > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php for($i = 1; $i <= $pager['totalPages']; $i++): ?>
                            <li class="page-item <?= $i == $pager['currentPage'] ? 'active' : '' ?>">
                                <a class="page-link" href="<?= base_url(session()->get('role') . '/logs') ?>?page=<?= $i ?>&action=<?= isset($filters['action']) ? $filters['action'] : '' ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>