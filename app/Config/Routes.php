<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
// 1. Existing Root / Dashboard Endpoint Setup
$routes->get('/', 'Home::index');
$routes->get('dashboard', 'Home::index'); // Maps /dashboard to your dashboard index too

// 2. Add These Explicit Rules For Your Authentication Controller
$routes->get('auth/login', 'Auth::login');
$routes->post('auth/process_login', 'Auth::process_login');
$routes->get('auth/logout', 'Auth::logout');
