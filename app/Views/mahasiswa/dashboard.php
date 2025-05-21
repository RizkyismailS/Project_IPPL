<?= $this->extend('layout/main_template') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Jhon doe - 411550502100xx</h4>
    <a href="#" class="btn btn-dark">Enrol Kelas</a>
  </div>

  <div class="row mb-4">
    <div class="col-md-6">
      <div class="p-4 rounded bg-dark text-white text-center">
        <h5 class="mb-2">Jumlah Kehadiran</h5>
        <h1 class="text-success">00</h1>
      </div>
    </div>
    <div class="col-md-6">
      <div class="p-4 rounded bg-dark text-white text-center">
        <h5 class="mb-2">Jumlah Tidak Hadir</h5>
        <h1 class="text-danger">00</h1>
      </div>
    </div>
  </div>

  <div class="bg-white rounded p-4 shadow-sm">
    <h5 class="mb-3"><strong>Aktivitas Kehadiran</strong></h5>
    <table class="table table-borderless">
      <tbody>
        <tr>
          <td>Manajemen Sistem Informasi A1 12:00 - 02 Mei 2025</td>
          <td class="text-success text-end">Hadir</td>
        </tr>
        <tr>
          <td>Jaringan Komputer A1 8:00 - 03 Mei 2025</td>
          <td class="text-success text-end">Hadir</td>
        </tr>
        <tr>
          <td>Praktikum KBPL A1 10:30 - 03 Mei 2025</td>
          <td class="text-success text-end">Hadir</td>
        </tr>
        <tr>
          <td>Sistem Terdistribusi A1 14:30 - 04 Mei 2025</td>
          <td class="text-danger text-end">Tidak Hadir</td>
        </tr>
        <tr>
          <td>Pengantar Rekayasa Perangkat Lunak 13:30 - 05 Mei 2025</td>
          <td class="text-success text-end">Hadir</td>
        </tr>
        <tr>
          <td>Manajemen Sistem Informasi 12:00 - 24 April 2025</td>
          <td class="text-danger text-end">Tidak Hadir</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
