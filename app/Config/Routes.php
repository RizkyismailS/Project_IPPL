<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'mahasiswa::enroll');
$routes->get('dosen/dashboard', 'Dosen::index');
$routes->get('dosen/listkelas', 'Dosen::listkelas');
$routes->get('dosen/listAbsensi', 'Dosen::listAbsensi');
$routes->get('dosen/kelasBaru', 'Dosen::kelasBaru');
$routes->get('dosen/absensi', 'Dosen::absensi');
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
    $routes->get('dosen/create', 'AdminController::createUserDosen'); // Form
    $routes->post('dosen/store', 'AdminController::storeUserDosen'); // Proses simpan
    $routes->get('dosen/list', 'AdminController::listDosen');
});

// Dosen Routes (Gunakan grup dengan filter)
$routes->group('dosen', ['filter' => 'dosenAuthFilter'], static function ($routes) { // Buat filter dosenAuthFilter
    $routes->get('dosen/dashboard', 'DosenController::dashboard');
    $routes->get('profile', 'DosenController::profile');
});

// Mahasiswa Routes (Gunakan grup dengan filter)
$routes->group('mahasiswa', ['filter' => 'mahasiswaAuthFilter'], static function ($routes) { // Buat filter mahasiswaAuthFilter
    $routes->get('profile', 'MahasiswaController::profile');
    $routes->get('dashboard', 'MahasiswaController::dashboard');
});