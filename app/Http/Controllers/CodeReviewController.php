<?php

namespace App\Http\Controllers;

use App\Demo\CodeReview\SnippetReader;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CodeReviewController extends Controller
{
    public function index(): View
    {
        $points = collect(config('demo_review.points', []));
        $grouped = $points->groupBy('modul');

        return view('code-review.index', [
            'grouped' => $grouped,
            'points' => $points,
            'readingOrder' => config('demo_review.reading_order', []),
        ]);
    }

    public function show(string $id, SnippetReader $reader): View|RedirectResponse
    {
        $point = collect(config('demo_review.points', []))->firstWhere('id', $id);

        if (! $point) {
            abort(404, 'Titik Code Review tidak ditemukan.');
        }

        try {
            $snippet = $reader->readPoint($point['file'], $point['id']);
        } catch (\Throwable $exception) {
            report($exception);

            abort(404, 'Kode untuk titik review ini tidak bisa dibaca.');
        }

        return view('code-review.show', [
            'point' => $point,
            'snippet' => $snippet,
            'points' => config('demo_review.points', []),
        ]);
    }
}
