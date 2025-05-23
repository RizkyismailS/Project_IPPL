<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>


<nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">pages</a></li>
            <li class="breadcrumb-item"><a href="#">KelasBaru</a></li>
        </ol>
    </nav>
<div class="page-heading">
    <h3>Buat Kelas Baru</h3>
</div>

<div class="page-content">
    <section class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    <form action="<?= base_url('kelas/simpan') ?>" method="post">
                        <div class="row">
                            <!-- Kolom kiri -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nama_kelas" class="form-label">Nama Kelas</label>
                                    <input type="text" class="form-control" id="nama_kelas" name="nama_kelas" required>
                                </div>
                                <div class="mb-3">
                                    <label for="hari" class="form-label">Hari Kuliah</label>
                                    <input type="text" class="form-control" id="hari" name="hari">
                                </div>
                                <div class="mb-3">
                                    <label for="kode_kelas" class="form-label">Kode Kelas</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="kode_kelas" name="kode_kelas">
                                        <button class="btn btn-outline-primary" type="button">Generate</button>
                                    </div>
                                    <small class="text-muted">Dapat membuat sendiri atau Generate untuk otomatis</small>
                                </div>
                                <div class="mb-3">
                                    <label for="jam_kuliah" class="form-label">Jam Kuliah</label>
                                    <input type="text" class="form-control" id="jam_kuliah" name="jam_kuliah">
                                </div>
                                <div class="mb-3">
                                    <label for="tahun_ajaran" class="form-label">Tahun Ajaran</label>
                                    <input type="text" class="form-control" id="tahun_ajaran" name="tahun_ajaran">
                                </div>
                            </div>

                            <!-- Kolom kanan -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="deskripsi" class="form-label">Deskripsi <small class="text-muted">(opsional)</small></label>
                                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="6"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="maks_mhs" class="form-label">Batas Maks. Mhs <small class="text-muted">(opsional)</small></label>
                                    <input type="number" class="form-control" id="maks_mhs" name="maks_mhs">
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-success px-4 py-2">Buat Kelas</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<?= $this->endSection() ?>
