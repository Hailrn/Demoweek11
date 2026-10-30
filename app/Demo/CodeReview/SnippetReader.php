<?php

namespace App\Demo\CodeReview;

use InvalidArgumentException;
use RuntimeException;

/*
|--------------------------------------------------------------------------
| SnippetReader
|--------------------------------------------------------------------------
| Membaca file kode asli dari allowlist. Semua path divalidasi dengan
| realpath agar path traversal dan symlink keluar root ditolak.
*/
class SnippetReader
{
    private const ALLOWED_DIRECTORIES = [
        'app',
        'routes',
        'resources/views',
        'docs',
        'tests',
    ];

    private const ALLOWED_FILES = [
        'bootstrap/app.php',
        'config/demo_review.php',
    ];

    private const ALLOWED_EXTENSIONS = ['php', 'md'];

    /**
     * @return array{path: string, lines: string, start_line: int, end_line: int, total_lines: int}
     */
    public function readPoint(string $file, string $id): array
    {
        return $this->readRegion($file, $id);
    }

    /**
     * @return array{path: string, lines: string, start_line: int, end_line: int, total_lines: int}
     */
    public function readFile(string $file): array
    {
        $absolute = $this->resolveAllowedPath($file);
        $lines = file($absolute, FILE_IGNORE_NEW_LINES);

        if ($lines === false) {
            throw new RuntimeException('File tidak bisa dibaca.');
        }

        return [
            'path' => $this->relativePath($absolute),
            'lines' => implode("\n", $lines),
            'start_line' => 1,
            'end_line' => count($lines),
            'total_lines' => count($lines),
        ];
    }

    /**
     * @return array{path: string, lines: string, start_line: int, end_line: int, total_lines: int}
     */
    public function readRegion(string $file, string $id): array
    {
        $absolute = $this->resolveAllowedPath($file);
        $lines = file($absolute, FILE_IGNORE_NEW_LINES);

        if ($lines === false) {
            throw new RuntimeException('File tidak bisa dibaca.');
        }

        $start = null;
        $end = null;

        foreach ($lines as $index => $line) {
            if ($start === null && (
                str_contains($line, '[CODE-REVIEW:'.$id.']')
                || str_contains($line, 'region demo:'.$id)
            )) {
                $start = $index;
                continue;
            }

            if ($start !== null && str_contains($line, 'endregion demo:'.$id)) {
                $end = $index;
                break;
            }
        }

        if ($start === null || $end === null) {
            throw new InvalidArgumentException("Region demo:{$id} tidak ditemukan di {$file}.");
        }

        $slice = array_slice($lines, $start, $end - $start + 1);

        return [
            'path' => $this->relativePath($absolute),
            'lines' => implode("\n", $slice),
            'start_line' => $start + 1,
            'end_line' => $end + 1,
            'total_lines' => count($lines),
        ];
    }

    public function resolveAllowedPath(string $file): string
    {
        if ($file === '' || str_contains($file, "\0")) {
            throw new InvalidArgumentException('Path kode tidak valid.');
        }

        $normalized = str_replace('\\', '/', trim($file));
        $normalized = ltrim($normalized, '/');

        if (preg_match('/^[A-Za-z]:\//', $normalized)) {
            throw new InvalidArgumentException('Path absolut tidak diizinkan.');
        }

        $segments = array_filter(explode('/', $normalized), fn (string $segment) => $segment !== '');

        if (in_array('..', $segments, true) || in_array('.', $segments, true)) {
            throw new InvalidArgumentException('Path traversal tidak diizinkan.');
        }

        $lower = mb_strtolower($normalized);

        if (str_starts_with($lower, '.env') || str_starts_with($lower, 'storage/') || str_starts_with($lower, 'vendor/')) {
            throw new InvalidArgumentException('File tersebut tidak termasuk allowlist.');
        }

        $allowed = in_array($normalized, self::ALLOWED_FILES, true);

        foreach (self::ALLOWED_DIRECTORIES as $directory) {
            if (str_starts_with($normalized, $directory.'/')) {
                $allowed = true;
                break;
            }
        }

        if (! $allowed) {
            throw new InvalidArgumentException('File berada di luar allowlist Code Review.');
        }

        $absolute = realpath(base_path($normalized));

        if ($absolute === false || ! is_file($absolute)) {
            throw new InvalidArgumentException('File kode tidak ditemukan.');
        }

        $base = realpath(base_path());

        if ($base === false || ! str_starts_with($absolute, $base.DIRECTORY_SEPARATOR)) {
            throw new InvalidArgumentException('File berada di luar root project.');
        }

        $extension = strtolower(pathinfo($absolute, PATHINFO_EXTENSION));

        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new InvalidArgumentException('Ekstensi file tidak diizinkan.');
        }

        return $absolute;
    }

    private function relativePath(string $absolute): string
    {
        return str_replace('\\', '/', substr($absolute, strlen(base_path()) + 1));
    }
}
