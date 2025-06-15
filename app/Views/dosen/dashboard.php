<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>
<?php
  $breadcrumb = 'Dashboard';
  $pageTitle = 'Dashboard Dosen';
  echo view('layout/dosen_header', compact('breadcrumb', 'pageTitle'));
?>

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
                                        <i class="fas fa-book fa-lg text-white"></i>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <h6 class="text-muted font-semibold">Jumlah Kelas</h6>
                                    <h6 class="font-extrabold mb-0"><?= $totalKelas ?></h6>
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
                                        <i class="fas fa-user-check fa-lg text-white"></i>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <h6 class="text-muted font-semibold">Jumlah Mahasiswa</h6>
                                    <h6 class="font-extrabold mb-0"><?= $totalMahasiswa ?></h6>
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
                                        <i class="fas fa-clipboard fa-lg text-white"></i>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <h6 class="text-muted font-semibold">Total Sesi Absen</h6>
                                    <h6 class="font-extrabold mb-0"><?= $totalAbsensi ?></h6>
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
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h4>Statistik Kehadiran Mingguan</h4>
                        </div>
                        <div class="card-body">
                            <div id="attendance-chart"></div>
                        </div>
                    </div>
                </div>

                <!-- Pie Chart -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h4>Distribusi Status Kehadiran</h4>
                        </div>
                        <div class="card-body">
                            <div id="attendance-distribution"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Tabel Absensi -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <h4>Absensi Kelas Terkini</h4>
                            <a href="<?= base_url('dosen/kelas') ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-list"></i> Lihat Semua Kelas
                            </a>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped table-hover" id="table1">
                                <thead>
                                    <tr>
                                        <th>Mata Kuliah</th>
                                        <th>Nama Kelas</th>
                                        <th>Belum Absen</th>
                                        <th>Hadir</th>
                                        <th>Sesi Absensi</th>
                                        <th>Pertemuan Terakhir</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($kelasAbsensiData as $kelas): ?>
                                    <tr>
                                        <td><?= esc($kelas['nama_matakuliah'] ?? '-') ?></td>
                                        <td><?= esc($kelas['nama_kelas'] ?? '-') ?></td>
                                        <td><?= $kelas['belum_absen'] ?? 0 ?></td>
                                        <td><?= $kelas['total_hadir'] ?? 0 ?></td>
                                        <td><?= $kelas['total_sesi'] ?? 0 ?></td>
                                        <td>
                                            <?php if(isset($kelas['last_session']) && $kelas['last_session']): ?>
                                                <?= date('d M Y', strtotime($kelas['last_session'])) ?>
                                            <?php else: ?>
                                                Belum ada sesi
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?= base_url('dosen/kelas/detail/' . $kelas['kode_kelas']) ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($kelasAbsensiData)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Tidak ada data kelas</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Line Chart - Weekly Attendance
    var optionsLine = {
        chart: {
            type: 'line',
            height: 350,
            stacked: false,
            toolbar: {
                show: true
            },
            zoom: {
                enabled: true
            }
        },
        stroke: {
            width: [4, 4, 4],
            curve: 'smooth'
        },
        plotOptions: {
            bar: {
                columnWidth: '20%'
            }
        },
        colors: ['#435ebe', '#dc3545', '#ff9f43'],
        series: [
            {
                name: 'Hadir',
                type: 'line',
                data: <?= $hadirData ?>
            },
            {
                name: 'Tidak Hadir',
                type: 'line',
                data: <?= $absenData ?>
            },
            {
                name: 'Izin',
                type: 'line',
                data: <?= $izinData ?>
            }
        ],
        fill: {
            opacity: [0.85, 0.85, 0.85],
            gradient: {
                inverseColors: false,
                shade: 'light',
                type: "vertical",
                opacityFrom: 0.85,
                opacityTo: 0.55,
                stops: [0, 100, 100, 100]
            }
        },
        markers: {
            size: 0
        },
        xaxis: {
            categories: <?= $weekLabels ?>,
        },
        yaxis: {
            title: {
                text: 'Jumlah Mahasiswa'
            },
            min: 0
        },
        tooltip: {
            shared: true,
            intersect: false,
            y: {
                formatter: function(y) {
                    if (typeof y !== "undefined") {
                        return y.toFixed(0) + " mahasiswa";
                    }
                    return y;
                }
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'center',
            offsetY: 10
        }
    };

    var chartLine = new ApexCharts(
        document.querySelector("#attendance-chart"),
        optionsLine
    );
    chartLine.render();

    // Pie Chart - Attendance Distribution
    var optionsPie = {
        series: <?= $pieValues ?>,
        chart: {
            type: 'pie',
            height: 350,
            toolbar: {
                show: true,
            }
        },
        labels: <?= $pieLabels ?>,
        colors: ['#435ebe', '#dc3545', '#ff9f43'],
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }],
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + " kehadiran"
                }
            }
        }
    };

    var chartPie = new ApexCharts(
        document.querySelector("#attendance-distribution"), 
        optionsPie
    );
    chartPie.render();
});
</script>

<?= $this->endSection() ?>