<?php

namespace App\Http\Controllers;

use App\Demo\Support\RouteCatalog;
use App\Services\ProductService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(ProductService $products, RouteCatalog $routes): View
    {
        return view('home', [
            'summary' => $products->summary(),
            'productCount' => $products->count(),
            'routeCount' => $routes->count(),
            'reviewCount' => count(config('demo_review.points', [])),
        ]);
    }
}
