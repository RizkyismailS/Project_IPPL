<?= $this->extend('layout/main_template') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="card shadow-sm p-4" style="border-radius: 15px;">
        <h4 class="mb-4"><strong>Gabung ke kelas</strong></h4>

        <!-- Info akun -->
        <div class="border rounded mb-4 p-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-secondary" style="width: 45px; height: 45px;"></div>
                <div>
                    <div class="text-muted">Akun yang digunakan</div>
                    <div class="fw-bold">Jhon doe</div>
                    <small class="text-muted">Jhondoe234@gmail.com</small>
                </div>
            </div>
            <button class="btn btn-outline-primary">Ganti Akun</button>
        </div>

        <!-- Input kode kelas -->
        <div class="border rounded mb-4 p-3">
            <label for="kodeKelas" class="fw-bold mb-1">Kode kelas</label>
            <p class="text-muted mb-2">Mintalah kode kelas kepada pengajar, dan masukan di sini.</p>
            <input type="text" class="form-control" id="kodeKelas" placeholder="Kode kelas">
        </div>

        <!-- Petunjuk -->
        <div class="border rounded p-3">
            <div class="fw-bold mb-2">PETUNJUK</div>
            <ul class="mb-0">
                <li>Gunakan akun yang diberi otorisasi</li>
                <li>Gunakan kode kelas yang terdiri dari 5–8 huruf atau angka, tanpa spasi atau simbol</li>
            </ul>
        </div>
    </div>
</div>
        <!-- modal pop up kelas dan dosen -->
<div class="modal fade" id="modalEnrolKelas" tabindex="-1" aria-labelledby="modalEnrolKelasLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content rounded-4 overflow-hidden">

      <!-- Header Hijau -->
      <div class="modal-header-green px-4 py-3">
        <h5 class="mb-0">Manajemen Sistem Informasi</h5>
        <small>A1 - IF</small>
      </div>

      <!-- Body Modal -->
      <div class="modal-body px-5 py-4 d-flex flex-column align-items-center justify-content-center">
        <div class="d-flex align-items-center w-100 mb-4" style="gap: 20px;">
          <!-- Foto dosen di kiri mentok -->
          <img src="/assets/images/faces/1.jpg" alt="Dosen" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover; margin-left: 0;">

          <!-- Nama & tombol tetap proporsional -->
          <div class="text-center flex-fill">
            <h5 class="mb-3" style="font-size: 1.2rem;">prof. Dr. Paul Morrison S.Pd. M.Pd</h5>
            <button class="btn btn-primary px-5 py-2" style="font-size: 1.1rem;">Enrol Kelas</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>



<?= $this->endSection() ?>
