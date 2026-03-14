<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| All routes serve the Vue SPA via the app Blade template.
| The Vue Router handles client-side routing for all paths.
|
*/

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
