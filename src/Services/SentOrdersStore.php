<?php

namespace MetricsDash\Laravel\Services;

/**
 * Mémorise les commandes déjà transmises à MetricsDash (aucune migration requise), pour que
 * ni un rejeu de webhook ni une nouvelle synchronisation ne les envoient deux fois.
 * Par défaut : storage/app/metrics-dash/sent-orders.json.
 */
class SentOrdersStore
{
    public function path(): string
    {
        return config('metrics-dash.sent_orders_file') ?: storage_path('app/metrics-dash/sent-orders.json');
    }

    public function has(string $orderId): bool
    {
        return isset($this->load()[$orderId]);
    }

    public function mark(string ...$orderIds): void
    {
        if ($orderIds === []) {
            return;
        }

        $path = $this->path();
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }

        // Lecture et écriture sous le même verrou : deux envois simultanés ne s'écrasent pas.
        $handle = fopen($path, 'c+');
        if ($handle === false) {
            return;
        }

        try {
            flock($handle, LOCK_EX);
            $current = json_decode((string) stream_get_contents($handle), true);
            $current = is_array($current) ? $current : [];
            foreach ($orderIds as $id) {
                $current[$id] = now()->toIso8601String();
            }
            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, json_encode($current, JSON_UNESCAPED_UNICODE));
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    public function forget(): void
    {
        if (is_file($path = $this->path())) {
            unlink($path);
        }
    }

    /** @return array<string, string> orderId => date d'envoi */
    private function load(): array
    {
        $path = $this->path();
        if (! is_file($path)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($path), true);

        return is_array($data) ? $data : [];
    }
}
