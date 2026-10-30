<?php

use App\Http\Controllers\Api\DemoRouteController;
use App\Http\Controllers\Api\MiddlewareDemoController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\ResponseDemoController;
use App\Http\Controllers\DemoController;
use Illuminate\Support\Facades\Route;

/*
| Semua URI di file ini otomatis mendapat prefix /api dari bootstrap/app.php.
| Grup api tidak memakai session/cookie/CSRF.
*/

Route::get('/demo/hello', [DemoRouteController::class, 'hello'])->name('api.demo.hello');
Route::match(['get', 'post'], '/demo/echo', [DemoRouteController::class, 'echo'])->name('api.demo.echo');
Route::match(['get', 'post'], '/demo/products/search', [DemoRouteController::class, 'matchSearch'])->name('api.demo.products.search');
Route::any('/demo/webhook', [DemoRouteController::class, 'webhook'])->name('api.demo.webhook');

Route::get('/demo/order/special', [DemoRouteController::class, 'orderSpecial'])->name('api.demo.order.special');
Route::get('/demo/order/{slug}', [DemoRouteController::class, 'orderSlug'])
    ->where('slug', '[a-z0-9-]+')
    ->name('api.demo.order.slug');

Route::get('/demo/where/{id}', [DemoRouteController::class, 'where'])
    ->where('id', '[^/]+')
    ->name('api.demo.where');
Route::get('/demo/where-numeric/{id}', [DemoRouteController::class, 'whereNumeric'])
    ->where('id', '[0-9]+')
    ->name('api.demo.where.numeric');

Route::get('/demo/products-by-id/{id}', [DemoRouteController::class, 'byIdString'])
    ->where('id', '[^/]+')
    ->name('api.demo.products.by-id');
Route::get('/demo/products-by-category/{category?}', [ProductApiController::class, 'byCategory'])
    ->name('api.demo.products.by-category');

Route::get('/demo/search', [ProductApiController::class, 'search'])->name('api.demo.search');
Route::get('/demo/di', [ProductApiController::class, 'di'])->name('api.demo.di');
Route::put('/demo/semantic-put/{product}', [DemoRouteController::class, 'semanticPut'])->name('api.demo.semantic.put');
Route::patch('/demo/semantic-patch/{product}', [DemoRouteController::class, 'semanticPatch'])->name('api.demo.semantic.patch');
Route::post('/demo/mass-assignment', [DemoRouteController::class, 'massAssignment'])->name('api.demo.mass-assignment');
Route::get('/demo/array-response', [DemoRouteController::class, 'arrayResponse'])->name('api.demo.array-response');
Route::get('/demo/envelope', [DemoRouteController::class, 'envelope'])->name('api.demo.envelope');

// Reset data dari terminal/API (tanpa CSRF karena grup api).
Route::post('/demo/reset', [DemoController::class, 'reset'])->name('api.demo.reset');

Route::apiResource('products', ProductApiController::class)->names('api.products');

// [CODE-REVIEW:mw-03] Middleware pada route group dengan prefix admin
// Baca : auth memastikan user ada, role membatasi admin/owner.
// Coba : bandingkan /api/admin/dashboard (admin) dan /api/admin/reports (admin,owner).
// Lihat: Modul B -> "Middleware pada Route Group"
// region demo:mw-03
Route::middleware('auth')->prefix('admin')->name('api.admin.')->group(function () {
    Route::get('/dashboard', [MiddlewareDemoController::class, 'dashboard'])
        ->middleware('role:admin')
        ->name('dashboard');

    Route::get('/reports', [MiddlewareDemoController::class, 'reports'])
        ->middleware('role:admin,owner')
        ->name('reports');

    Route::get('/area/info', [MiddlewareDemoController::class, 'areaInfo'])
        ->middleware('role:admin,owner')
        ->name('area.info');
});
// endregion demo:mw-03

Route::get('/demo/middleware-order/auth-first', [MiddlewareDemoController::class, 'orderAuthFirst'])
    ->middleware(['auth', 'role:admin'])
    ->name('api.demo.middleware-order.auth-first');
Route::get('/demo/middleware-order/role-first', [MiddlewareDemoController::class, 'orderRoleFirst'])
    ->middleware(['role:admin', 'auth'])
    ->name('api.demo.middleware-order.role-first');

Route::get('/demo/middleware/inspect', [MiddlewareDemoController::class, 'inspectRoles'])
    ->middleware(['auth', 'role:admin,owner,kasir'])
    ->name('api.demo.middleware.inspect');
Route::get('/demo/middleware/stop', [MiddlewareDemoController::class, 'dashboard'])
    ->middleware('role:admin')
    ->name('api.demo.middleware.stop');
Route::get('/demo/middleware/missing-alias', [MiddlewareDemoController::class, 'missingAlias'])
    ->name('api.demo.middleware.missing-alias');
Route::get('/demo/global-public', [MiddlewareDemoController::class, 'globalPublic'])
    ->name('api.demo.global-public');
Route::get('/demo/throttle', [MiddlewareDemoController::class, 'throttle'])
    ->middleware('throttle:5,1')
    ->name('api.demo.throttle');
Route::get('/demo/signed/generate', [MiddlewareDemoController::class, 'signedGenerate'])
    ->name('api.demo.signed.generate');
Route::get('/demo/inspect-api', [MiddlewareDemoController::class, 'inspectApi'])
    ->name('api.demo.inspect-api');

Route::get('/demo/response/view', [ResponseDemoController::class, 'view'])->name('api.demo.response.view');
Route::get('/demo/response/json', [ResponseDemoController::class, 'json'])->name('api.demo.response.json');
Route::get('/demo/response/redirect', [ResponseDemoController::class, 'redirect'])->name('api.demo.response.redirect');
Route::get('/demo/response/download', [ResponseDemoController::class, 'download'])->name('api.demo.response.download');
Route::get('/demo/response/no-content', [ResponseDemoController::class, 'noContent'])->name('api.demo.response.no-content');
Route::get('/demo/response/inspect/{type}', [ResponseDemoController::class, 'inspect'])
    ->where('type', 'view|json|redirect|download|no-content')
    ->name('api.demo.response.inspect');
