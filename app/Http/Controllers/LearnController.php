<?php

namespace App\Http\Controllers;

use App\Demo\Support\RouteCatalog;
use App\Services\ProductService;
use Illuminate\View\View;

class LearnController extends Controller
{
    public function route(RouteCatalog $catalog): View
    {
        return view('learn.route', [
            'routes' => $catalog->demoRoutes(),
            'faqs' => config('demo_faqs.route', []),
        ]);
    }

    public function middleware(): View
    {
        return view('learn.middleware', [
            'faqs' => config('demo_faqs.middleware', []),
        ]);
    }

    public function controller(ProductService $products, RouteCatalog $catalog): View
    {
        return view('learn.controller', [
            'summary' => $products->summary(),
            'webResourceRoutes' => $catalog->forNamePrefix('web.products.'),
            'apiResourceRoutes' => $catalog->forNamePrefix('api.products.'),
            'faqs' => config('demo_faqs.controller', []),
        ]);
    }
}
