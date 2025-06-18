<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<div class="page-heading">
    <h3><?= esc($title) ?></h3>
</div>
<div class="page-content">
    <?php if (session('validation')): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach (session('validation')->getErrors() as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif ?>
    <form action="<?= base_url('admin/matakuliah/store') ?>" method="post">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label for="kode_matakuliah" class="form-label">Kode Mata Kuliah</label>
            <input type="text" class="form-control" id="kode_matakuliah" name="kode_matakuliah" required value="<?= old('kode_matakuliah') ?>">
        </div>
        <div class="mb-3">
            <label for="nama_matakuliah" class="form-label">Nama Mata Kuliah</label>
            <input type="text" class="form-control" id="nama_matakuliah" name="nama_matakuliah" required value="<?= old('nama_matakuliah') ?>">
        </div>
        <div class="mb-3">
            <label for="sks" class="form-label">SKS</label>
            <input type="number" class="form-control" id="sks" name="sks" required value="<?= old('sks') ?>">
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="<?= base_url('admin/matakuliah') ?>" class="btn btn-secondary">Kembali</a>
    </form>
</div>
<?= $this->endSection() ?>