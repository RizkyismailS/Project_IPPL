<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'mahasiswa::enroll');
$routes->get('/hello', function() {
    return 'Hello from Laragon CI4!';
});

// Auth Routes
$routes->get('/login/auth', 'AuthController::login');
$routes->post('/login/process', 'AuthController::processLogin');
$routes->get('/register/mahasiswa', 'AuthController::registerMahasiswa');
$routes->post('/register/mahasiswa/process', 'AuthController::processRegisterMahasiswa');
$routes->get('/logout', 'AuthController::logout');

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
    $routes->post('kelas/update/(:segment)', 'DosenController::updateKelas/$1'); // Untuk proses update kelas
    $routes->post('kelas/delete/(:segment)', 'DosenController::deleteKelas/$1'); // Untuk hapus kelas
});

// Mahasiswa Routes (Gunakan grup dengan filter)
$routes->group('mahasiswa', ['filter' => 'mahasiswaAuthFilter'], static function ($routes) { // Buat filter mahasiswaAuthFilter
    $routes->get('profile', 'MahasiswaController::profile');
    $routes->get('dashboard', 'MahasiswaController::dashboard');
});