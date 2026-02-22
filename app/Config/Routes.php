<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */


// User Acounts routes

$routes->delete('users/delete/(:num)', 'Users::delete/$1');

// Person routes

$routes->delete('person/delete/(:num)', 'Person::delete/$1');

// Person routes

$routes->delete('person/delete/(:num)', 'Person::delete/$1');


// Profiling routes

$routes->delete('profiling/delete/(:num)', 'Profiling::delete/$1');


// Student routes

$routes->delete('student/delete/(:num)', 'Student::delete/$1');


// Logs routes for admin
