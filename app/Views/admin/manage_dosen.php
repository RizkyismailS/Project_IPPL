<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Manage Dosen</h3>
                <p class="text-subtitle text-muted">Daftar semua dosen yang terdaftar dalam sistem.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= base_url('/admin/dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Manage Dosen</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="card-title">Daftar Dosen</h4>
                <a href="<?= base_url('admin/dosen/create') ?>" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Tambah Dosen Baru
                </a>
            </div>
        </div>
        <div class="card-body">
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

            <div class="d-flex justify-content-between mb-3">
                <form class="d-flex w-100" method="get" action="<?= base_url('admin/dosen/list') ?>">
                    <input type="text" name="search" class="form-control w-50" placeholder="Cari Dosen..." value="<?= esc($search ?? '') ?>">
                    <select name="status" class="form-select w-auto ms-2">
                        <option value="">Semua Status</option>
                        <option value="1" <?= (isset($status) && $status === '1') ? 'selected' : '' ?>>Aktif</option>
                        <option value="0" <?= (isset($status) && $status === '0') ? 'selected' : '' ?>>Tidak Aktif</option>
                    </select>
                    <button type="submit" class="btn btn-primary ms-2"><i class="bi bi-search"></i> Cari</button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped" id="dosenTable">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>NIP</th>
                            <th>Nama Dosen</th>
                            <th>Email</th>
                            <th>Jabatan</th>
                            <th>Username Login</th>
                            <th>Status Akun</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($dosen_list) && is_array($dosen_list)): ?>
                            <?php $no = 1 + ($perPage * ($currentPage - 1)); ?>
                            <?php foreach ($dosen_list as $dosen): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= esc($dosen['nip']) ?></td>
                                    <td><?= esc($dosen['nama_dosen']) // Sesuaikan dengan key dari controller ?></td>
                                    <td><?= esc($dosen['email_dosen']) // Sesuaikan dengan key dari controller ?></td>
                                    <td><?= esc($dosen['jabatan'] ?? '-') // Sesuaikan dengan key dari controller ?></td>
                                    <td><?= esc($dosen['username'] ?? '-') // Dari join dengan tabel users ?></td>
                                    <td>
                                        <?php if (isset($dosen['is_active'])): ?>
                                            <?php if ($dosen['is_active'] == 1): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Tidak Aktif</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= base_url('admin/dosen/edit/' . esc($dosen['nip'])) ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus" 
                                                onclick="confirmDelete('<?= esc($dosen['nip']) ?>', '<?= esc($dosen['nama_dosen']) ?>')">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                        
                                        <?php if (isset($dosen['is_active'])): ?>
                                            <?php if ($dosen['is_active'] == 1): ?>
                                                <a href="<?= base_url('admin/dosen/deactivate/' . esc($dosen['nip'])) ?>" class="btn btn-sm btn-outline-warning" title="Nonaktifkan Akun">
                                                    <i class="bi bi-slash-circle"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= base_url('admin/dosen/activate/' . esc($dosen['nip'])) ?>" class="btn btn-sm btn-outline-success" title="Aktifkan Akun">
                                                    <i class="bi bi-check-circle"></i>
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">Belum ada data dosen.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
                <nav aria-label="Page navigation">
<?= $pager->appends(['search' => $search, 'status' => $status])->links('default', 'bootstrap_mazer_template') ?>                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<form action="" method="post" id="deleteForm" class="d-none">
    <?= csrf_field() ?>
    <input type="hidden" name="_method" value="POST"> {/* Atau DELETE jika route-nya DELETE */}
</form>

<script>
// Fungsi untuk konfirmasi hapus
function confirmDelete(nip, namaDosen) {
    if (confirm(`Apakah Anda yakin ingin menghapus dosen "${namaDosen}" dengan NIP "${nip}"? Tindakan ini juga akan menghapus akun login terkait.`)) {
        const form = document.getElementById('deleteForm');
        form.action = `<?= base_url('admin/dosen/delete') ?>/${nip}`;
        form.submit();
    }
}
</script>

<?= $this->endSection() ?>