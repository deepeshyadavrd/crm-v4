<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', 'Home::index');

/* Authentication */
$routes->get('auth/login', 'Auth::login');
$routes->post('auth/process_login', 'Auth::process_login');
$routes->get('auth/logout', 'Auth::logout');


/* Leads */

$routes->get('leads', 'Leads::index');
$routes->get('leads/(:num)', 'Leads::index/$1');
$routes->get('leads/view/(:num)', 'Leads::view/$1');

$routes->post('leads/update_lead_status', 'Leads::update_lead_status');
$routes->post('leads/update_lead_stage', 'Leads::update_lead_stage');
$routes->post('leads/addReminder', 'Leads::addReminder');
$routes->post('leads/addRemark', 'Leads::addRemark');
$routes->post('leads/assign', 'Leads::assign');
$routes->post('leads/unassign', 'Leads::unassign');

$routes->get('leads/selfAssign/(:num)/(:num)', 'Leads::selfAssign/$1/$2');

$routes->post('leads/getwithdate', 'Leads::getwithdate');
$routes->get('leads/getmyreminder', 'Leads::getmyreminder');
$routes->get('leads/mark_seen/(:num)', 'Leads::mark_seen/$1');
$routes->post('leads/search', 'Leads::search');
$routes->get('leads/searchProduct', 'Leads::searchProduct');

/* Notification */
$routes->get('notifications/unread_notifications', 'Notifications::unread_notifications');

$routes->get('test-authorization', 'Home::testAuthorization');