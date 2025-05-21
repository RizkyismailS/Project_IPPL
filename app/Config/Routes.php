<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/admin/dashboard', 'Admin\Dashboard::index');
$routes->get('/mahasiswa/dashboard', 'Mahasiswa\Dashboard::index');

