<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| The public landing page plus the role-segregated route groups. Each area
| lives in its own file and is loaded below to keep this manifest small and
| the route organization explicit.
|
*/

Route::view('/', 'welcome')->name('home');

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/customer.php';
