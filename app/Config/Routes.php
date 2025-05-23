<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Dosen::index');
$routes->get('/dosen/dashboard', 'Dosen::index');
$routes->get('/dosen/kelasBaru', 'Dosen::kelasBaru');
$routes->get('/dosen/absensi', 'Dosen::absensi');
$routes->get('/dosen/listkelas', 'Dosen::listkelas');
$routes->get('/dosen/listAbsensi', 'Dosen::listAbsensi');
$routes->get('/mahasiswa/dashboard', 'Mahasiswa\Dashboard::index');

