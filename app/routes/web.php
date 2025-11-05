<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('/admin/{path?}', 'welcome')
    ->where('path', '.*');
