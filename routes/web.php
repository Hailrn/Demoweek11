<?php

use App\Http\Controllers\CodeReviewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemoAuthController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LearnController;
use App\Http\Controllers\ProductWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/belajar/route', [LearnController::class, 'route'])->name('learn.route');
Route::get('/belajar/middleware', [LearnController::class, 'middleware'])->name('learn.middleware');
Route::get('/belajar/controller', [LearnController::class, 'controller'])->name('learn.controller');

Route::get('/faq', [FaqController::class, 'index'])->name('faq');
Route::get('/code-review', [CodeReviewController::class, 'index'])->name('code-review.index');
Route::get('/code-review/{id}', [CodeReviewController::class, 'show'])
    ->where('id', '[a-z0-9-]+')
    ->name('code-review.show');

Route::post('/reset-demo', [DemoController::class, 'reset'])->name('demo.reset');
Route::post('/demo/role', [DemoAuthController::class, 'update'])->name('demo.role');

// [CODE-REVIEW:rt-05] Route::resource vs Route::apiResource
// Baca : resource web membuat 7 route termasuk create/edit; apiResource hanya 5.
// Coba : bandingkan tabel resource di Modul C dan php artisan route:list.
// Lihat: Modul C -> "Resource vs API Resource"
// region demo:rt-05
Route::resource('web/products', ProductWebController::class)
    ->names('web.products')
    ->parameters(['products' => 'product']);
// endregion demo:rt-05

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/summary', [DashboardController::class, 'summary'])->name('dashboard.summary');

// Route web untuk skenario CSRF dan method spoofing.
Route::post('/demo/csrf-test', [DemoController::class, 'csrfTest'])
    ->middleware('demo.csrf')
    ->name('demo.csrf-test');
Route::put('/demo/spoof', [DemoController::class, 'spoof'])->name('demo.spoof');
Route::get('/demo/inspect-web', [DemoController::class, 'inspectWeb'])->name('demo.inspect-web');

// Role dari session (GUI). Route ini sengaja berada di grup web agar session
// benar-benar dipakai, bukan header X-Demo-Role seperti pada grup API.
Route::get('/demo/session-admin', [DemoController::class, 'sessionAdmin'])
    ->middleware(['auth', 'role:admin,owner'])
    ->name('demo.session-admin');

// Middleware signed bawaan Laravel.
Route::get('/demo/signed', [DemoController::class, 'signed'])
    ->middleware('signed')
    ->name('demo.signed');
