<?php
?>
<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<div class="page-heading">
    <h3>Manage Mahasiswa</h3>
</div>

<div class="page-content">
    <div class="card">
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
                <form action="<?= base_url('admin/mahasiswa/list') ?>" method="get" class="d-flex">
                    <input type="text" name="search" class="form-control w-50" placeholder="Cari Mahasiswa" value="<?= isset($_GET['search']) ? esc($_GET['search']) : '' ?>">
                    <select name="status" class="form-select w-auto ms-2" onchange="this.form.submit()">
                        <option value="" <?= !isset($_GET['status']) || $_GET['status'] === '' ? 'selected' : '' ?>>All status</option>
                        <option value="1" <?= isset($_GET['status']) && $_GET['status'] === '1' ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= isset($_GET['status']) && $_GET['status'] === '0' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </form>
                <a href="<?= base_url('admin/mahasiswa/create') ?>" class="btn btn-primary ms-auto"><i class="bi bi-plus-lg"></i> Tambah Mahasiswa Baru</a>
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NIM</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($mahasiswa_list) && is_array($mahasiswa_list)): ?>
                        <?php $no = 1 + ($perPage * (($currentPage ?? 1) - 1)); ?>
                        <?php foreach ($mahasiswa_list as $mahasiswa): ?>
                            <tr>
                                <td><?= esc($mahasiswa['nama']) ?></td>
                                <td><?= esc($mahasiswa['nim']) ?></td>
                                <td><?= esc($mahasiswa['email']) ?></td>
                                <td>
                                    <?php if (isset($mahasiswa['is_active'])): ?>
                                        <?php if ($mahasiswa['is_active'] == 1): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Tidak Aktif</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="<?= base_url('admin/mahasiswa/edit/' . esc($mahasiswa['nim'])) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-fill"></i></a>
                                        
                                        <?php if (isset($mahasiswa['is_active']) && $mahasiswa['is_active'] == 1): ?>
                                            <a href="<?= base_url('admin/mahasiswa/deactivate/' . esc($mahasiswa['nim'])) ?>" class="btn btn-sm btn-outline-warning" title="Deactivate"><i class="bi bi-slash-circle"></i></a>
                                        <?php else: ?>
                                            <a href="<?= base_url('admin/mahasiswa/activate/' . esc($mahasiswa['nim'])) ?>" class="btn btn-sm btn-outline-success" title="Activate"><i class="bi bi-check-circle"></i></a>
                                        <?php endif; ?>
                                        
                                        <!-- Button trigger modal -->
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $mahasiswa['nim'] ?>">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                        
                                        <!-- Modal for delete confirmation -->
                                        <div class="modal fade" id="deleteModal<?= $mahasiswa['nim'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Apakah Anda yakin ingin menghapus data mahasiswa <strong><?= esc($mahasiswa['nama']) ?></strong> dengan NIM <strong><?= esc($mahasiswa['nim']) ?></strong>?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <a href="<?= base_url('admin/mahasiswa/delete/' . esc($mahasiswa['nim'])) ?>" class="btn btn-danger">Hapus</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada data mahasiswa</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if (isset($pager)): ?>
            <div class="mt-3">
                <?= $pager->links() ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>