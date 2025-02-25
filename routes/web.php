<?php


use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\PengaduanController;
use App\Http\Controllers\PengaduanUserController;


//Route untuk register
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);


// Route untuk halaman login
Route::get('/', function () {
    // Mengambil data pengguna dengan role
    $users = User::with('roles')->paginate(10);
    return view('pages.auth.auth-login');
});


// Rute yang hanya bisa diakses oleh pengguna yang terautentikasi

Route::middleware(['auth'])->group(function () {

    // Dashboard route
    Route::get('home', function () {
        return view('pages.dashboard');
    })->name('home');

    // Route manajemen user, hanya bisa diakses oleh admin
    Route::middleware(['role:admin|anggota'])->group(function () {
        Route::resource('user', UserController::class);
    });

    // Route manajemen pengaduan, bisa diakses oleh admin dan anggota
    Route::middleware(['role:anggota|user'])->group(function () {
        Route::resource('pengaduan', PengaduanController::class);
     });
    // Route::middleware(['role:user'])->group(function () {
    //     Route::resource('pengaduanuser', PengaduanUserController::class);
    //  });



    // // Route tambahan yang hanya bisa diakses oleh pengguna dengan permission 'edit users'
    // Route::group(['middleware' => ['permission:edit users']], function () {
    //     Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('user.edit');
    // });
});
