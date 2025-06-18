<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="container-fluid px-4">
    <h1 class="mt-4">Activity Logs</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item active">Activity Logs</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter me-1"></i>
            Filter Logs
        </div>
        <div class="card-body">
            <form action="<?= base_url('admin/logs') ?>" method="get">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="role">Role:</label>
                            <select class="form-select" name="role" id="role">
                                <option value="">All Roles</option>
                                <option value="admin" <?= isset($filters['role']) && $filters['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="dosen" <?= isset($filters['role']) && $filters['role'] == 'dosen' ? 'selected' : '' ?>>Dosen</option>
                                <option value="mahasiswa" <?= isset($filters['role']) && $filters['role'] == 'mahasiswa' ? 'selected' : '' ?>>Mahasiswa</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="action">Action:</label>
                            <select class="form-select" name="action" id="action">
                                <option value="">All Actions</option>
                                <option value="login" <?= isset($filters['action']) && $filters['action'] == 'login' ? 'selected' : '' ?>>Login</option>
                                <option value="logout" <?= isset($filters['action']) && $filters['action'] == 'logout' ? 'selected' : '' ?>>Logout</option>
                                <option value="create_absensi_session" <?= isset($filters['action']) && $filters['action'] == 'create_absensi_session' ? 'selected' : '' ?>>Create Absensi Session</option>
                                <option value="update_absensi_session" <?= isset($filters['action']) && $filters['action'] == 'update_absensi_session' ? 'selected' : '' ?>>Update Absensi Session</option>
                                <option value="submit_attendance" <?= isset($filters['action']) && $filters['action'] == 'submit_attendance' ? 'selected' : '' ?>>Submit Attendance</option>
                                <option value="create_class" <?= isset($filters['action']) && $filters['action'] == 'create_class' ? 'selected' : '' ?>>Create Class</option>
                                <option value="enroll_class" <?= isset($filters['action']) && $filters['action'] == 'enroll_class' ? 'selected' : '' ?>>Enroll Class</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="reference_id">Reference ID:</label>
                            <input type="text" class="form-control" name="reference_id" id="reference_id" value="<?= isset($filters['reference_id']) ? $filters['reference_id'] : '' ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
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
            Activity Log List
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>User</th>
                            <th>Role</th>
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
                                    <td><?= esc($log['reference_id']) ?></td>
                                    <td><?= ucfirst(esc($log['role'])) ?></td>
                                    <td><?= str_replace('_', ' ', ucwords(esc($log['action']))) ?></td>
                                    <td><?= esc($log['description']) ?></td>
                                    <td><?= esc($log['ip_address']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No activity logs found</td>
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
                                <a class="page-link" href="<?= base_url('admin/logs') ?>?page=<?= $i ?>&role=<?= isset($filters['role']) ? $filters['role'] : '' ?>&action=<?= isset($filters['action']) ? $filters['action'] : '' ?>&reference_id=<?= isset($filters['reference_id']) ? $filters['reference_id'] : '' ?>">
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