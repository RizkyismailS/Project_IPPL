<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>


    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">pages</a></li>
            <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
        </ol>
    </nav>

<div class="page-heading">
        <h3>Main Dashboard</h3>
    </div>

    <div class="page-content">
        <section class="row">
            <!-- Cards: Jumlah Kelas, Mahasiswa, Total Absen -->
            <div class="col-12 col-lg-12">
                <div class="row">
                    <div class="col-6 col-lg-4 col-md-6">
                        <div class="card">
                            <div class="card-body px-3 py-4-5">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="stats-icon purple">
                                            <i class="iconly-boldHome"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="text-muted font-semibold">Jumlah Kelas</h6>
                                        <h6 class="font-extrabold mb-0">5</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-4 col-md-6">
                        <div class="card">
                            <div class="card-body px-3 py-4-5">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="stats-icon blue">
                                            <i class="iconly-boldUser"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="text-muted font-semibold">Jumlah Mahasiswa</h6>
                                        <h6 class="font-extrabold mb-0">154</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-4 col-md-6">
                        <div class="card">
                            <div class="card-body px-3 py-4-5">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="stats-icon red">
                                            <i class="iconly-boldPaper"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="text-muted font-semibold">Total Absen</h6>
                                        <h6 class="font-extrabold mb-0">140</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistik dan Chart -->
                <!-- CHART ROW: Line + Bar chart -->
<div class="row">
    <!-- Line Chart -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4>Statistik Kehadiran</h4>
            </div>
            <div class="card-body">
                <div id="line"></div>
            </div>
        </div>
    </div>

    <!-- Bar Chart -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4>Absensi Mingguan</h4>
            </div>
            <div class="card-body">
                <div id="bar"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Tabel Absensi -->
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header">
                <h4>Absensi Kelas</h4>
            </div>
            <div class="card-body">
                <table class="table table-striped" id="table1">
                    <thead>
                        <tr>
                            <th>Mata Kuliah</th>
                            <th>Belum Absen</th>
                            <th>Sudah Absen</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Implementasi & Pengujian</td>
                            <td>10</td>
                            <td>20</td>
                            <td>24 Jan 2025</td>
                            <td><span class="badge bg-success">Aktif</span></td>
                        </tr>
                        <tr>
                            <td>Manajemen Sistem</td>
                            <td>8</td>
                            <td>18</td>
                            <td>12 Jun 2025</td>
                            <td><span class="badge bg-danger">Tidak Aktif</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Pie Chart + Kalender -->
    <div class="col-lg-3">
        <!-- Pie Chart -->
        <div class="card">
            <div class="card-header">
                <h4>Pie Chart Absensi</h4>
            </div>
            <div class="card-body">
                <div id="chart-visitors-profile"></div>
            </div>
        </div>

        <!-- Kalender -->
        <div class="card">
            <div class="card-header">
                <h4>Kalender</h4>
            </div>
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>

                
        </section>
    </div>
</div>

<?= $this->endSection() ?>
