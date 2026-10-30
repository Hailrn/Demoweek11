<?php

namespace App\Console\Commands;

use App\Demo\Store\DemoStore;
use Illuminate\Console\Command;

class SetupDemo extends Command
{
    protected $signature = 'demo:setup
        {--keep-env : Jangan ubah nilai driver di .env yang sudah ada}';

    protected $description = 'Siapkan .env, APP_KEY, folder storage, dan data demo tanpa database/Node';

    public function handle(DemoStore $store): int
    {
        $createdEnv = false;

        if (! file_exists(base_path('.env'))) {
            copy(base_path('.env.example'), base_path('.env'));
            $createdEnv = true;
            $this->info('.env dibuat dari .env.example.');
        } else {
            $this->line('.env sudah ada; isi lain tidak ditimpa.');
        }

        if (! $this->option('keep-env')) {
            $changed = [];

            foreach ([
                'SESSION_DRIVER' => 'file',
                'CACHE_STORE' => 'file',
                'QUEUE_CONNECTION' => 'sync',
            ] as $key => $value) {
                if ($this->ensureEnvValue($key, $value)) {
                    $changed[] = $key.'='.$value;
                }
            }

            if ($changed !== []) {
                $this->warn(
                    'Driver disesuaikan agar demo berjalan tanpa database: '.implode(', ', $changed).
                    '. Isi .env lainnya tidak diubah.'
                );
            }
        } elseif ($createdEnv === false) {
            $this->warn('Opsi --keep-env dipakai; nilai driver di .env dibiarkan apa adanya.');
        }

        $this->callSilent('config:clear');

        $env = file_get_contents(base_path('.env'));

        if (preg_match('/^APP_KEY=\s*$/m', (string) $env)) {
            $this->call('key:generate', ['--force' => true]);
        }

        $directories = [
            storage_path('app/demo'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/testing'),
            storage_path('framework/views'),
            storage_path('logs'),
        ];

        foreach ($directories as $directory) {
            if (! is_dir($directory)) {
                mkdir($directory, 0775, true);
            }
        }

        $store->reset();
        $this->callSilent('config:clear');

        $this->newLine();
        $this->info('Setup demo selesai.');
        $this->line('Data seed direset ke storage/app/demo/state.json.');
        $this->line('Jalankan: php artisan serve');
        $this->line('Buka: http://localhost:8000');

        return self::SUCCESS;
    }

    private function ensureEnvValue(string $key, string $value): bool
    {
        $path = base_path('.env');
        $contents = (string) file_get_contents($path);
        $line = $key.'='.$value;
        $pattern = '/^'.preg_quote($key, '/').'=(.*)$/m';

        if (preg_match($pattern, $contents, $matches)) {
            if (trim($matches[1]) === $value) {
                return false;
            }

            $contents = (string) preg_replace($pattern, $line, $contents);
        } else {
            $contents = rtrim($contents).PHP_EOL.$line.PHP_EOL;
        }

        file_put_contents($path, $contents);

        return true;
    }
}
