<?php

namespace Tests\Feature;

use App\Demo\CodeReview\SnippetReader;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class CodeReviewTest extends TestCase
{
    public function test_registry_dan_marker_konsisten_dua_arah(): void
    {
        $points = config('demo_review.points');
        $ids = array_column($points, 'id');

        $this->assertCount(24, $points);
        $this->assertSame($ids, array_values(array_unique($ids)), 'ID Code Review harus unik.');

        $markers = $this->markersInCode();

        sort($ids);
        sort($markers);

        $this->assertSame($ids, $markers, 'Setiap marker harus terdaftar, dan sebaliknya.');
    }

    public function test_setiap_point_punya_region_file_dan_halaman(): void
    {
        $reader = app(SnippetReader::class);

        foreach (config('demo_review.points') as $point) {
            $this->assertFileExists(base_path($point['file']), "File {$point['file']} tidak ada.");
            $this->assertStringStartsWith('/', $point['demo_url']);

            $snippet = $reader->readPoint($point['file'], $point['id']);

            $this->assertStringContainsString('[CODE-REVIEW:'.$point['id'].']', $snippet['lines']);
            $this->assertStringContainsString('region demo:'.$point['id'], $snippet['lines']);
            $this->assertStringContainsString('endregion demo:'.$point['id'], $snippet['lines']);
            $this->assertGreaterThanOrEqual($snippet['start_line'], $snippet['end_line']);

            $this->get('/code-review/'.$point['id'])
                ->assertOk()
                ->assertSee($point['judul']);
        }
    }

    public function test_setiap_demo_url_di_registry_bisa_dibuka(): void
    {
        foreach (config('demo_review.points') as $point) {
            $path = Str::before($point['demo_url'], '#');
            $fragment = parse_url($point['demo_url'], PHP_URL_FRAGMENT);

            $response = $this->get($path)->assertOk();

            if (is_string($fragment) && $fragment !== '') {
                $response->assertSee('id="'.$fragment.'"', false);
            }
        }
    }

    public function test_index_dan_halaman_tidak_dikenal(): void
    {
        $this->get('/code-review')
            ->assertOk()
            ->assertSee('24 titik review');

        $this->get('/code-review/tidak-ada')->assertNotFound();
    }

    public function test_faq_konsisten_dengan_registry(): void
    {
        $pointIds = array_column(config('demo_review.points'), 'id');
        $faqIds = [];
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD'];

        foreach (config('demo_faqs') as $group => $items) {
            $this->assertNotEmpty($items, "FAQ modul {$group} kosong.");

            foreach ($items as $item) {
                $this->assertNotContains($item['id'], $faqIds, 'ID FAQ harus unik.');
                $faqIds[] = $item['id'];
                $this->assertContains($item['code'], $pointIds, 'FAQ menunjuk titik review yang tidak ada.');
                $this->assertArrayHasKey('scenario', $item);
                $this->assertStringStartsWith('/', $item['scenario']['path'] ?? '');
                $this->assertContains($item['scenario']['method'] ?? 'GET', $methods);
            }
        }

        $this->get('/faq')
            ->assertOk()
            ->assertSee('FAQ interaktif')
            ->assertSee('Kalau begini gimana?');
    }

    /**
     * @return array<int, string>
     */
    private function markersInCode(): array
    {
        $files = collect()
            ->merge(File::allFiles(app_path()))
            ->merge(File::allFiles(base_path('routes')))
            ->merge(File::allFiles(resource_path('views')))
            ->push(new \SplFileInfo(base_path('bootstrap/app.php')));

        $markers = [];

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            preg_match_all('/\[CODE-REVIEW:([a-z0-9-]+)\]/', (string) file_get_contents($file->getPathname()), $matches);

            foreach ($matches[1] as $id) {
                $markers[] = $id;
            }
        }

        return array_values(array_unique($markers));
    }
}
