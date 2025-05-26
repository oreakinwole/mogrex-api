<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('v1/', function () {
    return Inertia::render('welcome');
});
