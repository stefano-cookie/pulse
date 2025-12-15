<?php

use App\Http\Controllers\DeviceController;
use Illuminate\Support\Facades\Route;

Route::post('/devices/register', [DeviceController::class, 'register']);
