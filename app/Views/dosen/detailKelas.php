<?= $this->extend('layout/template') ?> 

<?= $this->section('content') ?>
<?php
// Blok header ini bisa Anda simpan jika sudah sesuai
$breadcrumb = 'Detail Kelas';
$pageTitle = $title ?? 'Detail Kelas';
echo view('layout/dosen_header', compact('breadcrumb', 'pageTitle')); 
?>

<section class="section">
    <?php if (empty($kelas)): ?>
        <div class="alert alert-danger">Detail kelas tidak ditemukan atau Anda tidak memiliki akses.</div>
    <?php else: ?>
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Informasi Kelas: <?= esc($kelas['nama_kelas']) ?></h4>
                    <div>
                        <a href="<?= base_url('dosen/kelas/edit/' . esc($kelas['kode_kelas'], 'url')) ?>" class="btn btn-primary btn-sm">
                            <i class="bi bi-pencil-square"></i> Edit Kelas
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tbody>
                                <tr><th style="width: 40%;">Kode Kelas</th><td>: <?= esc($kelas['kode_kelas']) ?></td></tr>
                                <tr><th>Nama Kelas</th><td>: <?= esc($kelas['nama_kelas']) ?></td></tr>
                                <tr>
                                    <th>Mata Kuliah</th>
                                    <td>: 
                                        <?= esc($kelas['nama_matakuliah'] ?? 'Tidak Tersedia') ?> 
                                        <?php if(isset($kelas['sks'])): ?>
                                            (<?= esc($kelas['sks']) ?> SKS)
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr><th>Dosen Pengampu</th><td>: <?= esc($kelas['nama_dosen']) ?></td></tr>
                                <tr><th>Kode Enrollment</th><td>: <code><?= esc($kelas['kode_enrollment']) ?></code></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tbody>
                                <tr><th style="width: 40%;">Jadwal</th><td>: <?= esc($kelas['hari']) ?>, <?= esc(date('H:i', strtotime($kelas['waktu_mulai_kelas']))) ?> - <?= esc(date('H:i', strtotime($kelas['waktu_selesai_kelas']))) ?></td></tr>
                                <tr><th>Ruangan</th><td>: <?= esc($kelas['ruangan'] ?? '-') ?></td></tr>
                                <tr><th>Tahun Ajaran</th><td>: <?= esc($kelas['tahun_ajaran']) ?></td></tr>
                                <tr><th>Semester</th><td>: <?= esc($kelas['semester']) ?></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <hr>
                
                <h5>Mahasiswa Terdaftar (<?= $jumlah_mahasiswa_terdaftar ?? 0 ?>)</h5>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
                <?php endif; ?>

                <?php if (!empty($mahasiswa_terdaftar) && is_array($mahasiswa_terdaftar)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>NIM</th>
                                    <th>Nama</th>
                                    <th>Tgl Daftar</th>
                                    <th>Status</th>
                                    <th style="width: 20%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mahasiswa_terdaftar as $mhs): ?>
                                    <tr>
                                        <td><?= esc($mhs['nim']) ?></td>
                                        <td><?= esc($mhs['nama']) ?></td>
                                        <td><?= CodeIgniter\I18n\Time::parse($mhs['tanggal_enroll'])->toLocalizedString('dd MMM yyyy') ?></td>
                                        <td>
                                            <?php
                                            $status = $mhs['status_enrollment'];
                                            $badgeClass = 'secondary';
                                            if ($status == 'aktif') $badgeClass = 'success';
                                            if ($status == 'menunggu_persetujuan') $badgeClass = 'warning';
                                            if ($status == 'dinonaktifkan') $badgeClass = 'dark'; // Status baru
                                            if ($status == 'selesai_gagal' || $status == 'mengundurkan_diri') $badgeClass = 'danger';
                                            ?>
                                            <span class="badge bg-light-<?= $badgeClass ?>"><?= ucfirst(str_replace('_', ' ', $status)) ?></span>
                                        </td>
                                        <td>
                                            <form action="<?= base_url('dosen/enrollment/manage') ?>" method="post" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id_enrollment" value="<?= $mhs['id_enrollment'] ?>">
                                                
                                                <?php if ($mhs['status_enrollment'] == 'aktif'): ?>
                                                    <button type="submit" name="action" value="deactivate" class="btn btn-warning btn-sm" title="Nonaktifkan Mahasiswa">Nonaktifkan</button>
                                                <?php elseif ($mhs['status_enrollment'] == 'dinonaktifkan'): ?>
                                                    <button type="submit" name="action" value="activate" class="btn btn-success btn-sm" title="Aktifkan Kembali">Aktifkan</button>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">Belum ada mahasiswa yang terdaftar di kelas ini.</p>
                <?php endif; ?>

                <hr>
                
                <h5>Sesi Absensi</h5>
                <a href="<?= base_url('dosen/sesi-absensi/create/' . esc($kelas['kode_kelas'], 'url')) ?>" class="btn btn-outline-success btn-sm mb-3">
                    <i class="bi bi-plus-lg"></i> Tambah Sesi Absensi Baru
                </a>

                <div class="table-responsive mt-3">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Topik</th>
                                <th>Waktu Mulai</th>
                                <th>Waktu Selesai</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($sesi_absensi)) : ?>
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada sesi absensi yang dibuat untuk kelas ini.</td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($sesi_absensi as $sesi) : ?>
                                    <tr>
                                        <td><?= esc($sesi['topik_perkuliahan'] ?: 'Perkuliahan Rutin') ?></td>
                                        <td><?= date('d M Y, H:i', strtotime($sesi['waktu_mulai_aktual'])) ?></td>
                                        <td><?= $sesi['waktu_selesai_aktual'] ? date('d M Y, H:i', strtotime($sesi['waktu_selesai_aktual'])) : 'Belum Selesai' ?></td>
                                        <td class="text-center">
                                            <?php
                                                $status = $sesi['status_tampil'];
                                                $badgeClass = 'bg-light-secondary'; // Default
                                                if ($status == 'Aktif') $badgeClass = 'bg-success';
                                                if ($status == 'Selesai') $badgeClass = 'bg-dark';
                                                if ($status == 'Dibatalkan') $badgeClass = 'bg-danger';
                                                if ($status == 'Terlewat') $badgeClass = 'bg-warning';
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= esc($status) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= base_url('dosen/laporan-sesi/' . $sesi['id_sesi']) ?>" class="btn btn-sm btn-info">
                                                Laporan
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
            </div>
            <div class="card-footer">
                <a href="<?= base_url('dosen/kelas') ?>" class="btn btn-light">Kembali ke Daftar Kelas</a>
            </div>
        </div>
    <?php endif; ?>
</section>

<?= $this->endSection() ?>