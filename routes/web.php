<?php

use Illuminate\Support\Facades\Route;

// SPA Catch-All Route
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*')->name('spa');
