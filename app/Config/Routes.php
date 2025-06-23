<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/hello', function () {
    return 'Hello from Laragon CI4!';
});

// Auth Routes

$routes->get('/', 'AuthController::login');
$routes->post('/login/process', 'AuthController::processLogin');
$routes->get('/register/mahasiswa', 'AuthController::registerMahasiswa');
$routes->post('/register/mahasiswa/process', 'AuthController::processRegisterMahasiswa');
$routes->get('/logout', 'AuthController::logout');
$routes->cli('cron/update-session-statuses', 'CronController::updateSessionStatuses');


// Admin Routes (Gunakan grup dengan filter untuk keamanan)
$routes->group('admin', ['filter' => 'adminAuthFilter'], static function ($routes) { // Buat filter adminAuthFilter
    $routes->get('dashboard', 'AdminController::dashboard');
    $routes->get('dosen/create', 'AdminController::createUserDosenForm'); // Form
    $routes->post('dosen/store', 'AdminController::storeUserDosen'); // Proses simpan
    $routes->get('dosen/list', 'AdminController::listDosen');
    $routes->get('dosen/edit/(:segment)', 'AdminController::editDosenForm/$1');
    $routes->put('dosen/update/(:segment)', 'AdminController::updateDosen/$1');
    $routes->post('dosen/delete/(:segment)', 'AdminController::deleteDosen/$1'); // Menggunakan POST
    $routes->get('dosen/activate/(:segment)', 'AdminController::activateDosen/$1');
    $routes->get('dosen/deactivate/(:segment)', 'AdminController::deactivateDosen/$1');
    $routes->get('mahasiswa/list', 'AdminController::listMahasiswa');
    $routes->get('mahasiswa/create', 'AdminController::createUserMahasiswaForm'); // Form
    $routes->post('mahasiswa/store', 'AdminController::storeUserMahasiswa');
    $routes->get('mahasiswa/edit/(:segment)', 'AdminController::editMahasiswaForm/$1');
    $routes->get('mahasiswa/delete/(:segment)', 'AdminController::deleteMhs/$1'); // Menggunakan GET untuk menghapus mahasiswa
    $routes->put('mahasiswa/update/(:segment)', 'AdminController::updateMahasiswa/$1');
    $routes->get('mahasiswa/activate/(:segment)', 'AdminController::activateMahasiswa/$1');
    $routes->get('mahasiswa/deactivate/(:segment)', 'AdminController::deactivateMahasiswa/$1');
    $routes->get('sesi', 'AdminController::listSesi');
    $routes->get('matakuliah', 'MatakuliahController::index');
    $routes->get('matakuliah/create', 'MatakuliahController::create');
    $routes->post('matakuliah/store', 'MatakuliahController::store');
    $routes->get('matakuliah/edit/(:segment)', 'MatakuliahController::edit/$1');
    $routes->post('matakuliah/update/(:segment)', 'MatakuliahController::update/$1');
    $routes->post('matakuliah/delete/(:segment)', 'MatakuliahController::delete/$1');
    $routes->get('logs', 'LogController::index');
    $routes->get('logs/user/(:num)', 'LogController::userLogs/$1');
});

// Dosen Routes (Gunakan grup dengan filter)
$routes->group('dosen', ['filter' => 'dosenAuthFilter'], static function ($routes) { // Buat filter dosenAuthFilter
    $routes->get('dashboard', 'DosenController::dashboard');
    $routes->get('profile', 'DosenController::profile');
    $routes->get('kelas', 'DosenController::listKelas'); // Menampilkan daftar kelas
    $routes->get('kelas/create', 'DosenController::createKelasForm'); // Menampilkan form tambah kelas
    $routes->post('kelas/store', 'DosenController::storeKelas');    // Memproses penyimpanan kelas baru
    $routes->get('kelas/detail/(:segment)', 'DosenController::detailKelas/$1'); // <--- ROUTE INI
    $routes->get('kelas/edit/(:segment)', 'DosenController::editKelasForm/$1');   // Untuk form edit kelas
    $routes->put('kelas/update/(:segment)', 'DosenController::updateKelas/$1'); // Untuk proses update kelas
    $routes->delete('kelas/delete/(:segment)', 'DosenController::deleteKelas/$1'); // Untuk hapus kelas
    $routes->post('enrollment/manage', 'DosenController::manageEnrollment'); // Untuk mengelola enrollment mahasiswa
    $routes->get('sesi-absensi/create/(:segment)', 'SesiAbsensiController::create/$1');
    $routes->post('sesi-absensi/store', 'SesiAbsensiController::store');
    $routes->get('sesi-absensi/edit/(:num)', 'SesiAbsensiController::edit/$1');
    $routes->post('sesi-absensi/update/(:num)', 'SesiAbsensiController::update/$1');
    $routes->get('list-sesi/(:segment)', 'DosenController::listSesi/$1');
    $routes->get('laporan-sesi/(:num)', 'DosenController::laporanSesi/$1');
    $routes->get('logs', 'LogController::userLogs');
});

// Mahasiswa Routes (Gunakan grup dengan filter)
$routes->group('mahasiswa', ['filter' => 'mahasiswaAuthFilter'], static function ($routes) { // Buat filter mahasiswaAuthFilter
    $routes->get('profile', 'MahasiswaController::profile');
    $routes->get('dashboard', 'MahasiswaController::dashboard');
    $routes->post('absensi/submit', 'MahasiswaController::submitAbsensi');
    $routes->get('enroll', 'MahasiswaController::enrollForm');
    $routes->post('enroll/process', 'MahasiswaController::processEnrollment');
    $routes->get('kelas', 'MahasiswaController::listKelas');
    $routes->get('sesi/(:segment)', 'MahasiswaController::listSesi/$1');
    $routes->post('submitAbsensi', 'MahasiswaController::submitAbsensi');
    $routes->get('logs', 'LogController::userLogs');

});