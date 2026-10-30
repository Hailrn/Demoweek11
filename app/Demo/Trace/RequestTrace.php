<?php

namespace App\Demo\Trace;

use Illuminate\Http\Response;

/*
|--------------------------------------------------------------------------
| RequestTrace
|--------------------------------------------------------------------------
| Mencatat jalur request: global -> grup -> route middleware -> controller
| -> response. Hasilnya dikirim di header X-Demo-Trace (base64 JSON) agar
| body response tetap sesuai contoh materi.
*/
final class RequestTrace
{
    private array $entries = [];

    private float $startedAt = 0.0;

    public function reset(): void
    {
        $this->entries = [];
        $this->startedAt = microtime(true);
    }

    public function add(
        string $stage,
        string $label,
        string $status = 'lolos',
        string $message = '',
        string $phase = 'sebelum',
        array $meta = [],
    ): void {
        $now = microtime(true);

        $this->entries[] = [
            'stage' => $stage,
            'label' => $label,
            'status' => $status,
            'message' => $message,
            'phase' => $phase,
            'at_ms' => $this->startedAt > 0 ? round(($now - $this->startedAt) * 1000, 2) : 0,
            'meta' => $meta,
        ];
    }

    public function isEmpty(): bool
    {
        return $this->entries === [];
    }

    public function hasStop(): bool
    {
        foreach ($this->entries as $entry) {
            if ($entry['status'] === 'berhenti') {
                return true;
            }
        }

        return false;
    }

    public function toArray(): array
    {
        return [
            'entries' => $this->entries,
            'total_ms' => $this->startedAt > 0 ? round((microtime(true) - $this->startedAt) * 1000, 2) : 0,
        ];
    }

    public function toHeader(): string
    {
        return rtrim(strtr(base64_encode(json_encode($this->toArray(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)), '+/', '-_'), '=');
    }

    public function attachTo(Response|\Symfony\Component\HttpFoundation\Response $response): void
    {
        if (! $this->isEmpty() && ! $response->headers->has('X-Demo-Trace')) {
            $response->headers->set('X-Demo-Trace', $this->toHeader());
        }
    }
}
