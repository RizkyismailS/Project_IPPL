<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>


<nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">pages</a></li>
            <li class="breadcrumb-item"><a href="#">AbsensiBaru</a></li>
        </ol>
    </nav>
<div class="page-heading">
    <h3>Buat Kelas Baru</h3>
</div>
<section class="section">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title">List Kelas</h5>
                <a href="/dosen/kelasBaru" class="btn btn-warning">Buat Kelas Baru</a>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th><input type="checkbox" /></th>
                            <th>Nama Kelas</th>
                            <th>Kode Kelas</th>
                            <th>Hari & Jam Kuliah</th>
                            <th>Jumlah Mahasiswa</th>
                            <th>Semester & Tahun Ajaran</th>
                            <th>Status Kelas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kelas as $k): ?>
                        <tr>
                            <td><input type="checkbox" /></td>
                            <td><?= esc($k['nama_kelas']) ?></td>
                            <td><?= esc($k['kode_kelas']) ?></td>
                            <td><?= esc($k['hari_jam']) ?></td>
                            <td><?= esc($k['jumlah']) ?></td>
                            <td><?= esc($k['semester']) ?>,<?= esc($k['tahun']) ?></td>
                            <td><span class="badge bg-success">Aktif</span></td>
                            <td>
                                <a href="<?= base_url('kelas/' . $k['id']) ?>" class="btn btn-info btn-sm">Detail</a>
                                <form action="<?= base_url('kelas/' . $k['id']) ?>" method="post" style="display:inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
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
