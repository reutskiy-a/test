<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::any('/', function () {
    return redirect()->away(config('app.front_url'));
});
