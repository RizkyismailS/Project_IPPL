<!-- absen_kelas.php -->
<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<div class="page-content">
    <section class="row">
        <div class="col-12">
            <h3>Absensi Kelas <span class="text-primary">24 Mei 2025</span></h3>

            <div class="row mt-4">
                <?php foreach ($kelas as $k): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-header text-white <?= $k['warna'] ?>">
                                <strong><?= $k['nama_matkul'] ?></strong><br>
                                <small><?= $k['kelas'] ?></small>
                            </div>
                            <div class="card-body" style="height: 200px;">
                                <div class="mb-4" style="padding: 20px 0 5px 0;">
                                    <p class="text-muted mb-1">Batas waktu absensi</p>
                                    <p class="fw-bold"><?= $k['jam_mulai'] ?> - <?= $k['jam_selesai'] ?></p>
                                </div>

                                <?php if ($k['status'] == 'hadir'): ?>
                                    <div class="alert alert-primary text-center mb-0">Hadir kelas</div>
                                <?php else: ?>
                                    <button class="btn btn-outline-primary w-100 text-lg" data-bs-toggle="modal" data-bs-target="#absenModal">Absen kelas</button>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
<?php endforeach; ?>


            </div>
        </div>
    </section>
</div>
<!-- Modal Absensi Kelas -->
<div class="modal fade" id="absenModal" tabindex="-1" aria-labelledby="absenModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="absenModalLabel">ABSENSI KELAS</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Masukan kode unik yang diberikan disaat pembelajaran, oleh pengajar</p>
        <input type="text" class="form-control mb-3" placeholder="Kode Absen" id="kodeAbsen">

        <div class="upload-area border border-2 border-secondary rounded p-4 text-center" style="cursor:pointer;" onclick="document.getElementById('fileUpload').click()">
            <i class="bi bi-upload" style="font-size: 2rem;"></i>
            <p class="mt-2">Drag file to upload or Click to add</p>
            <input type="file" id="fileUpload" hidden>
            <small class="text-muted d-block mt-2">* Bukti Gambar berupa foto di kelas</small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save Changes</button>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
