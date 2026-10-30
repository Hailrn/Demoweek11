<?php

namespace Tests\Unit;

use App\Demo\CodeReview\SnippetReader;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SnippetReaderTest extends TestCase
{
    public function test_file_dalam_allowlist_bisa_dibaca(): void
    {
        $reader = app(SnippetReader::class);
        $file = $reader->readFile('app/Demo/Models/Product.php');

        $this->assertSame('app/Demo/Models/Product.php', $file['path']);
        $this->assertStringContainsString('class Product', $file['lines']);
        $this->assertGreaterThan(1, $file['total_lines']);
    }

    public function test_region_yang_diminta_saja_yang_ditampilkan(): void
    {
        $reader = app(SnippetReader::class);
        $region = $reader->readRegion('app/Demo/Models/Product.php', 'ct-04');

        $this->assertStringContainsString('[CODE-REVIEW:ct-04]', $region['lines']);
        $this->assertStringContainsString('resolveRouteBinding', $region['lines']);
        $this->assertStringNotContainsString('fromInput', $region['lines']);
        $this->assertSame(
            $region['end_line'] - $region['start_line'] + 1,
            substr_count($region['lines'], "\n") + 1,
        );
    }

    public function test_region_yang_tidak_ada_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(SnippetReader::class)->readRegion('app/Demo/Models/Product.php', 'ct-99');
    }

    /**
     * @dataProvider pathTraversalProvider
     */
    #[DataProvider('pathTraversalProvider')]
    public function test_path_di_luar_allowlist_ditolak(string $path): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(SnippetReader::class)->resolveAllowedPath($path);
    }

    public static function pathTraversalProvider(): array
    {
        return [
            'dotenv' => ['.env'],
            'dotenv lewat traversal' => ['../.env'],
            'app lalu traversal' => ['app/../.env'],
            'storage' => ['storage/app/demo/state.json'],
            'vendor' => ['vendor/autoload.php'],
            'path absolut unix' => ['/etc/passwd'],
            'path absolut windows' => ['C:/Windows/win.ini'],
            'backslash traversal' => ['app\\..\\..\\.env'],
            'nul byte' => ["app/Demo/Models/Product.php\0"],
            'ekstensi tidak diizinkan' => ['app/Demo/Models/Product.txt'],
        ];
    }

    public function test_symlink_keluar_root_ditolak(): void
    {
        $directory = base_path('tests/fixtures');
        File::ensureDirectoryExists($directory);

        $target = tempnam(sys_get_temp_dir(), 'demo-snippet-');
        $link = $directory.'/escape.php';

        file_put_contents($target, 'secret di luar project');
        @unlink($link);

        if (! @symlink($target, $link)) {
            @unlink($target);
            $this->markTestSkipped('Sistem tidak mendukung symlink.');
        }

        try {
            $this->expectException(InvalidArgumentException::class);
            app(SnippetReader::class)->resolveAllowedPath('tests/fixtures/escape.php');
        } finally {
            @unlink($link);
            @unlink($target);
        }
    }
}
