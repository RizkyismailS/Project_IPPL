<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<div class="page-heading">
    <h3><?= esc($title) ?></h3>
</div>
<div class="page-content">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>
    <a href="<?= base_url('admin/matakuliah/create') ?>" class="btn btn-primary mb-3">Tambah Mata Kuliah</a>
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Mata Kuliah</th>
                        <th>SKS</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($matakuliah as $mk): ?>
                        <tr>
                            <td><?= esc($mk['kode_matakuliah']) ?></td>
                            <td><?= esc($mk['nama_matakuliah']) ?></td>
                            <td><?= esc($mk['sks']) ?></td>
                            <td>
                                <a href="<?= base_url('admin/matakuliah/edit/' . $mk['kode_matakuliah']) ?>" class="btn btn-warning btn-sm">Edit</a>
                                <form action="<?= base_url('admin/matakuliah/delete/' . $mk['kode_matakuliah']) ?>" method="post" style="display:inline;" onsubmit="return confirm('Yakin hapus?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>