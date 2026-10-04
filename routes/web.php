<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix("/auth")->controller(AuthController::class)->group(function() {

    Route::middleware('guest')->group(function () {
        Route::get("login", "login")->name('login');
        Route::get("register", "register")->name('register');
        
        Route::post("login-handle", "loginHandle")->name('loginHandle');
        Route::post("register-handle", "registerHandle")->name('registerHandle');
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'Logout'])->name('logout');
        
    });

    // Route::middleware(IsNotLogged::class)->group(function () {
    //     Route::get('login', "login")->name('login');
    //     Route::post('logining', "logining")->name('logining');

    //     Route::get('register', "register")->name('register');
    //     Route::post('registering', "registering")->name('registering');
    // });
        
});

Route::prefix("/dashboard")->controller(DashboardController::class)->middleware("auth")->group(function () {
    Route::get("/", "index")->name("dashboard");
});

Route::prefix("/ticket")->middleware("auth")->controller(TicketController::class)->group(function () {
    Route::get("/create", "create")->name("ticket.create")->middleware("can:ticket.create");
});


