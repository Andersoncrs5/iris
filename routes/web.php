<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
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

Route::prefix("/category")->middleware("auth")->controller(CategoryController::class)->group(function () {
    Route::get("/create", "create")->name("category.create")->middleware("can:category.create");
    Route::post("/store", "store")->name("category.store")->middleware("can:category.create");
    Route::get("/list", "index")->name("category.list")->middleware("can:category.list");

    Route::get("/edit/{id}", "edit")->name("category.edit")->middleware("can:category.update");
    
    Route::put("/update/{id}", "update")->name("category.update")->middleware("can:category.update");
    
    Route::patch("/toggle-active/{id}", "toggleActive")->name("category.toggle-active")->middleware("can:category.update");
    
    Route::delete("/delete/{id}", "destroy")->name("category.destroy")->middleware("can:category.delete");
});


Route::prefix("/tag")->middleware("auth")->controller(TagController::class)->group(function () {
    Route::get("/", "index")->name("tag.list")->middleware("can:tag.list");

    Route::get("/create", "create")->name("tag.create")->middleware("can:tag.create");
    Route::post("store", "store")->name("tag.store")->middleware("can:tag.create");

    Route::get("/edit/{id}", "edit")->name("tag.edit")->middleware("can:tag.update");
    Route::put("/update/{id}", "update")->name("tag.update")->middleware("can:tag.update");
    Route::patch("/toggle-active/{id}", "toggleActive")->name("tag.toggle-active")->middleware("can:tag.update");
    Route::delete("/delete/{id}", "destroy")->name("tag.destroy")->middleware("can:tag.delete");
});

Route::prefix("/user")->middleware("auth")->controller(UserController::class)->group(function () {
    Route::get("/profile", "profile")->name("user.profile");

    Route::get("/edit", "edit")->name("user.edit")->middleware("can:user.update");
    Route::put("/edit", "editForm")->name("user.update")->middleware("can:user.update");

    Route::delete("/delete", "delete")->name("user.delete")->middleware("can:user.delete");

    Route::get("/edit-password", "editPassword")->name("user.edit.password")->middleware("can:user.update");
    Route::put("/edit-password", "editPasswordForm")->name("user.update.password")->middleware("can:user.update");

    Route::get("/list", "list")->name("user.list")->middleware("can:user.read");
});