<?php

namespace App\Http\Controllers;

use App\Demo\Trace\RequestTrace;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    // [CODE-REVIEW:ct-01] Controller biasa: view + JSON
    // Baca : satu controller boleh punya method dengan jenis response berbeda.
    // Coba : buka /dashboard (view) lalu /dashboard/summary (JSON).
    // Lihat: Modul C -> "Controller biasa"
    // region demo:ct-01
    public function index(ProductService $products, RequestTrace $trace): View
    {
        $trace->add('controller', 'DashboardController@index', 'lolos', 'Mengembalikan view dashboard.');

        return view('dashboard', [
            'summary' => $products->summary(),
            'products' => array_slice($products->all(), 0, 5),
        ]);
    }

    public function summary(ProductService $products, RequestTrace $trace): JsonResponse
    {
        $trace->add('controller', 'DashboardController@summary', 'lolos', 'Mengembalikan JSON dari data produk nyata.');

        return response()->json([
            'status' => 'success',
            'message' => 'Ringkasan dashboard',
            'data' => $products->summary(),
        ]);
    }
    // endregion demo:ct-01
}
