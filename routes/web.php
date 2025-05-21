<?php
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/otp/verify/{type?}', function ($type = 'email') {
        return view('otp-verify', ['type' => $type]);
    })->name('otp.verify');
});

if (file_exists(__DIR__.'/auth.php')) {
    require __DIR__.'/auth.php';
} else {
    Route::get('login', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
    Route::post('logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
}