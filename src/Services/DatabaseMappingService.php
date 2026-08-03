<?php

namespace MetricsDash\Laravel\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Http;

class DatabaseMappingService
{
    /**
     * Liste toutes les tables de la base de données cliente.
     */
    public function getTables(): array
    {
        $tables = Schema::getTableListing();
        // Filtrer les tables système Laravel standard
        return array_values(array_filter($tables, function ($table) {
            return !in_array($table, ['migrations', 'failed_jobs', 'personal_access_tokens', 'password_reset_tokens', 'sessions', 'cache', 'jobs', 'job_batches']);
        }));
    }

    /**
     * Liste les colonnes d'une table BDD cliente.
     */
    public function getColumns(string $table): array
    {
        if (!Schema::hasTable($table)) {
            return [];
        }
        return Schema::getColumnListing($table);
    }

    /**
     * Extrait les transactions financières jointes (Payments + Packs + Users) d'après le mappage configuré.
     */
    public function extractMappedTransactions(array $mapping): array
    {
        $payTable = $mapping['payments']['table'] ?? null;
        $packTable = $mapping['packs']['table'] ?? null;
        $userTable = $mapping['users']['table'] ?? null;

        if (!$payTable || !Schema::hasTable($payTable)) {
            return [];
        }

        $query = DB::table($payTable);

        // Jointure avec la table Packages/Packs si configurée
        if ($packTable && Schema::hasTable($packTable)) {
            $query->leftJoin(
                $packTable,
                "{$payTable}." . ($mapping['payments']['foreign_pack_id'] ?? 'pack_id'),
                '=',
                "{$packTable}." . ($mapping['packs']['primary_id'] ?? 'id')
            );
        }

        // Jointure avec la table Users/Acheteurs si configurée
        if ($userTable && Schema::hasTable($userTable)) {
            $query->leftJoin(
                $userTable,
                "{$payTable}." . ($mapping['payments']['foreign_user_id'] ?? 'user_id'),
                '=',
                "{$userTable}." . ($mapping['users']['primary_id'] ?? 'id')
            );
        }

        // Filtre de statut valide si spécifié
        $statusCol = $mapping['payments']['status'] ?? null;
        $validStatusVal = $mapping['payments']['valid_status_value'] ?? null;
        if ($statusCol && $validStatusVal && Schema::hasColumn($payTable, $statusCol)) {
            $query->where("{$payTable}.{$statusCol}", $validStatusVal);
        }

        // Colonnes sélectionnées
        $amountCol = $mapping['payments']['amount'] ?? 'amount';
        $currCol = $mapping['payments']['currency'] ?? 'currency';
        $dateCol = $mapping['payments']['created_at'] ?? 'created_at';
        $packNameCol = $mapping['packs']['name'] ?? 'name';
        $userNameCol = $mapping['users']['name'] ?? 'name';
        $userEmailCol = $mapping['users']['email'] ?? 'email';

        $rawRows = $query->limit(500)->get();

        $normalized = [];
        foreach ($rawRows as $row) {
            $rowArr = (array) $row;
            $normalized[] = [
                'orderId' => 'PAY-' . ($rowArr['id'] ?? rand(1000, 9999)),
                'amount' => (float) ($rowArr[$amountCol] ?? 0.0),
                'currency' => strtoupper((string) ($rowArr[$currCol] ?? 'EUR')),
                'pack' => (string) ($rowArr[$packNameCol] ?? 'Pack Standard'),
                'userName' => (string) ($rowArr[$userNameCol] ?? 'Client Anonyme'),
                'userEmail' => (string) ($rowArr[$userEmailCol] ?? 'client@domaine.com'),
                'date' => (string) ($rowArr[$dateCol] ?? date('Y-m-d H:i:s')),
            ];
        }

        return $normalized;
    }

    /**
     * Extrait et envoie les transactions mappées à l'API MetricsDash Core.
     */
    public function syncToMetricsDash(array $mapping): array
    {
        $apiUrl = config('metrics-dash.api_url', 'http://localhost:8000') . '/api/v1/telemetry/push';
        $pushKey = config('metrics-dash.push_key');

        $transactions = $this->extractMappedTransactions($mapping);

        $successCount = 0;
        foreach ($transactions as $tx) {
            try {
                $res = Http::withHeaders([
                    'X-Push-Secret-Key' => $pushKey,
                ])->post($apiUrl, [
                    'eventName' => 'order_completed',
                    'eventData' => $tx,
                ]);

                if ($res->successful()) {
                    $successCount++;
                }
            } catch (\Throwable $e) {
                logger()->warning('[MetricsDash SDK] Synchronisation échouée pour ' . $tx['orderId']);
            }
        }

        return [
            'total_extracted' => count($transactions),
            'synced_count' => $successCount,
        ];
    }
}
