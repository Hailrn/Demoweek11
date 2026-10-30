<?php

namespace App\Demo\Support;

use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Route as LaravelRoute;

/*
| Membaca route yang benar-benar terdaftar (Route::getRoutes()) untuk
| ditampilkan di GUI. Tidak menjalankan perintah shell dan tidak mengubah
| apa pun. Dipakai oleh penjelajah route dan tabel resource.
*/
class RouteCatalog
{
    public function all(): array
    {
        return array_map(
            fn (LaravelRoute $route) => $this->format($route),
            Route::getRoutes()->getRoutes()
        );
    }

    public function forNamePrefix(string $prefix): array
    {
        return array_values(array_filter(
            $this->all(),
            fn (array $route) => str_starts_with((string) $route['name'], $prefix)
        ));
    }

    public function demoRoutes(): array
    {
        return array_values(array_filter(
            $this->all(),
            fn (array $route) => str_starts_with($route['uri'], 'api/')
                || str_starts_with($route['uri'], 'web/')
                || str_starts_with($route['uri'], 'demo/')
                || in_array($route['uri'], ['dashboard', 'dashboard/summary'], true)
        ));
    }

    public function count(): int
    {
        return count(Route::getRoutes()->getRoutes());
    }

    private function format(LaravelRoute $route): array
    {
        return [
            'methods' => $route->methods(),
            'uri' => ltrim($route->uri(), '/'),
            'name' => $route->getName() ?? '',
            'action' => $this->action($route),
            'middleware' => $route->gatherMiddleware(),
            'domain' => $route->getDomain() ?? '',
        ];
    }

    private function action(LaravelRoute $route): string
    {
        $action = $route->getActionName();

        if ($action === 'Closure') {
            return 'Closure';
        }

        return str_replace('App\\Http\\Controllers\\', '', $action);
    }
}
