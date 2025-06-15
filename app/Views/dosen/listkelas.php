<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>
<?php
  $breadcrumb = 'list kelas';
  $pageTitle = 'List kelas';
  echo view('layout/dosen_header', compact('breadcrumb', 'pageTitle'));
?>

<section class="section">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title">List Kelas</h5>
                <a href="kelas/create" class="btn btn-warning">Buat Kelas Baru</a>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
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
                        <?php foreach ($kelas_list as $k): ?>
                        <tr>
                            <td><?= esc($k['nama_kelas']) ?></td>
                            <td><?= esc($k['kode_kelas']) ?></td>
                            <td><?= esc($k['hari']) ?></td>
                            <td><?= esc($k['jumlah_mahasiswa']) ?></td>
                            <td><?= esc($k['semester']) ?>,<?= esc($k['tahun']) ?></td>
                            <td><span class="badge bg-success">Aktif</span></td>
                            <td class="text-nowrap">    
                                <a href="<?= base_url('dosen/kelas/detail/' . $k['kode_kelas']) ?>" class="btn btn-info btn-sm">Detail Kelas</a>
                                <a href="/dosen/list-sesi/<?= esc($k['kode_kelas']) ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-card-list"></i> Sesi
                                </a>
                                <form action="<?= base_url('dosen/kelas/delete/' . $k['kode_kelas']) ?>" method="post" style="display:inline">
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
