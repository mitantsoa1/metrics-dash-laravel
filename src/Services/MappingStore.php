<?php

namespace MetricsDash\Laravel\Services;

/**
 * Persiste le mappage choisi dans /metrics-dash dans un fichier JSON (aucune migration requise).
 * Par défaut : storage/app/metrics-dash/mapping.json (dossier ignoré par git dans un projet Laravel standard).
 */
class MappingStore
{
    public function path(): string
    {
        return config('metrics-dash.mapping_file') ?: storage_path('app/metrics-dash/mapping.json');
    }

    /** @return array<string, array<string, mixed>> Mappage enregistré, ou tableau vide. */
    public function load(): array
    {
        $path = $this->path();
        if (! is_file($path)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($path), true);

        return is_array($data) ? $data : [];
    }

    public function save(array $mapping): void
    {
        $path = $this->path();
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }

        file_put_contents($path, json_encode($mapping, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    public function forget(): void
    {
        if (is_file($path = $this->path())) {
            unlink($path);
        }
    }
}
