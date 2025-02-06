<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Handle SPA routes
Route::get('/', function () {
    return view('app');
});

// Catch all routes for SPA, excluding /api paths
Route::get('/{path}', function () {
    return view('app');
})->where('path', '^(?!api|api\/.*$).*$');
