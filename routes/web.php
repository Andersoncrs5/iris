<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketMessageController;
use App\Http\Controllers\TicketPriorityController;
use App\Http\Controllers\TicketStatusController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->middleware("guest");

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
});

Route::prefix('/dashboard')->controller(DashboardController::class)->middleware('auth')->group(function () {
    Route::get('/', 'index')->name('dashboard');
});

Route::prefix("/ticket")->middleware("auth")->controller(TicketController::class)->group(function () {
    Route::get('/', 'list')->name('ticket.list')->middleware('can:ticket.read');

    Route::get('/my-tickets', 'listMyTicket')->name('ticket.listMyTicket');

    Route::get("/create", "create")->name("ticket.create")->middleware("can:ticket.create");

    Route::post('/create', 'createForm')->name('ticket.create.form')->middleware('can:ticket.create');

    Route::get('/show-details/{id}', 'showDetails')->whereNumber('id')->name('ticket.show-details');


    Route::get('/{id}/edit', 'edit')->whereNumber('id')->name('ticket.edit')->middleware('can:ticket.update');

    Route::put('/{id}', 'editForm')->whereNumber('id')->name('ticket.update')->middleware('can:ticket.update');

    Route::get('/{id}/edit-own', 'editMyTicket')
        ->whereNumber('id')
        ->name('ticket.own.edit')
        ->middleware('can:ticket.own.update');

    Route::put('/{id}/edit-own', 'editMyTicketForm')
        ->whereNumber('id')
        ->name('ticket.own.update')
        ->middleware('can:ticket.own.update');

    Route::delete("/delete/{id}", "delete")
        ->name("ticket.delete")
        ->whereNumber('id')
        ->middleware("can:ticket.delete");
    
    Route::delete("/delete/{id}/own", "deleteOwn")
        ->whereNumber('id')
        ->name("ticket.own.destroy")
        ->middleware("can:ticket.own.delete");

    Route::patch('/close/{id}', 'close')
        ->whereNumber('id')
        ->name('ticket.close')
        ->middleware('can:ticket.close');

    Route::patch('/resolve/{id}', 'resolvedAt')
        ->whereNumber('id')
        ->name('ticket.resolve')
        ->middleware('can:ticket.resolve');

    // Reopen ticket
    Route::patch('/reopen/{id}', 'reopen')
        ->whereNumber('id')
        ->name('ticket.reopen')
        ->middleware('can:ticket.reopen');
});

Route::prefix("/category")->middleware("auth")->controller(CategoryController::class)->group(function () {
    Route::get("/create", "create")->name("category.create")->middleware("can:category.create");
    Route::post("/store", "store")->name("category.store")->middleware("can:category.create");
    Route::get("/list", "index")->name("category.list")->middleware("can:category.list");

    Route::get("/edit/{id}", "edit")->whereNumber('id')->name("category.edit")->middleware("can:category.update");
    
    Route::put("/update/{id}", "update")->whereNumber('id')->name("category.update")->middleware("can:category.update");
    
    Route::patch("/toggle-active/{id}", "toggleActive")->whereNumber('id')->name("category.toggle-active")->middleware("can:category.update");
    
    Route::delete("/delete/{id}", "destroy")->whereNumber('id')->name("category.destroy")->middleware("can:category.delete");
});

Route::prefix("/tag")->middleware("auth")->controller(TagController::class)->group(function () {
    Route::get("/", "index")->name("tag.list")->middleware("can:tag.list");

    Route::get("/create", "create")->name("tag.create")->middleware("can:tag.create");
    Route::post("store", "store")->name("tag.store")->middleware("can:tag.create");

    Route::get("/edit/{id}", "edit")->whereNumber('id')->name("tag.edit")->middleware("can:tag.update");
    Route::put("/update/{id}", "update")->whereNumber('id')->name("tag.update")->middleware("can:tag.update");
    Route::patch("/toggle-active/{id}", "toggleActive")->whereNumber('id')->name("tag.toggle-active")->middleware("can:tag.update");
    Route::delete("/delete/{id}", "destroy")->whereNumber('id')->name("tag.destroy")->middleware("can:tag.delete");
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

Route::prefix("/permission")->middleware("auth")->controller(PermissionController::class)->group(function () {
    Route::get("", "list")->name("permission.list")->middleware("can:permission.read");
    Route::get("create", "create")->name("permission.create")->middleware("can:permission.create");
    Route::post("/create", "createForm")->name("permission.create.form")->middleware("can:permission.create");

    Route::delete("/delete/{id}", "delete")->name("permission.destroy")->middleware("can:permission.delete");

    Route::get("/show-details/{id}", "showDetails")->name("permission.show");

    Route::get("/edit/{id}", "edit")->name("permission.update")->middleware("can:permission.update");
    Route::put('/edit/{id}', 'editForm')->name('permission.update')->middleware('can:permission.update');
});

Route::prefix("/ticket-status")->middleware("auth")->controller(TicketStatusController::class)->group(function () {
    Route::get("/", "index")->name("ticket.status.list")->middleware("can:ticket.status.read");
    Route::get("/create", "create")->name("ticket.status.create")->middleware("can:ticket.status.create");
    
    Route::post('/create', 'createForm')->name('ticket.status.create.form')->middleware('can:ticket.status.create');

    Route::delete("/delete/{id}", "delete")->name("ticket.status.destroy")->middleware("can:ticket.status.delete");

    Route::get("/edit/{id}", "edit")->name("ticket.status.update")->middleware("can:ticket.status.update");
    Route::put('/edit/{id}', 'editForm')->name('ticket.status.update')->middleware('can:ticket.status.update');

    Route::patch("/toggle-active/{id}", "toggleActive")->name("ticket.status.toggle-active")->middleware("can:ticket.status.update");

    Route::get("/show-details/{id}", "showDetails")->name("ticket.status.show-details");
});

Route::prefix("/ticket-priority")->middleware("auth")->controller(TicketPriorityController::class)->group(function () {
    Route::get('/', 'list')->name('ticket.priority.list')->middleware('can:ticket.priority.read');

    Route::get('/create', 'create')->name('ticket.priority.create')->middleware('can:ticket.priority.create');

    Route::post('/create', 'createForm')->name('ticket.priority.create.form')->middleware('can:ticket.priority.create');

    Route::get('/edit/{id}', 'edit')->name('ticket.priority.edit')->middleware('can:ticket.priority.update');

    Route::put('/edit/{id}', 'editForm')->name('ticket.priority.update')->middleware('can:ticket.priority.update');

    Route::delete('/delete/{id}', 'delete')->name('ticket.priority.destroy')->middleware('can:ticket.priority.delete');

    Route::get('/show-details/{id}', 'showDetails')->name('ticket.priority.show-details')->middleware('can:ticket.priority.read');
});

Route::prefix("ticket-message")->middleware("auth")->controller(TicketMessageController::class)->group(function () {
    Route::get("create/{id}", "create")->whereNumber('id')->name("ticket.message.create");
    Route::post("create/{id}", "createForm")->name("ticket.message.create.form");
});