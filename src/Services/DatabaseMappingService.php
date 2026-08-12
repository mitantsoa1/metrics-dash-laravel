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

        // Filtre de statut valide si spécifié (supporte plusieurs valeurs séparées par des virgules, ex: paid, succeeded)
        $statusCol = $mapping['payments']['status'] ?? null;
        $validStatusVal = $mapping['payments']['valid_status_value'] ?? null;
        if ($statusCol && !empty($validStatusVal) && Schema::hasColumn($payTable, $statusCol)) {
            if (is_array($validStatusVal)) {
                $validStatuses = array_values(array_filter(array_map('trim', $validStatusVal), fn($val) => $val !== ''));
            } else {
                $validStatuses = array_values(array_filter(array_map('trim', explode(',', (string) $validStatusVal)), fn($val) => $val !== ''));
            }

            if (count($validStatuses) === 1) {
                $query->where("{$payTable}.{$statusCol}", $validStatuses[0]);
            } elseif (count($validStatuses) > 1) {
                $query->whereIn("{$payTable}.{$statusCol}", $validStatuses);
            }
        }

        // Sélection des colonnes avec alias pour éviter tout conflit de noms (ex: id, name, created_at)
        $selects = ["{$payTable}.*"];

        $primaryPayCol = $mapping['payments']['primary_id'] ?? 'id';
        if (Schema::hasColumn($payTable, $primaryPayCol)) {
            $selects[] = "{$payTable}.{$primaryPayCol} as _pay_id";
        }

        $amountCol = $mapping['payments']['amount'] ?? 'amount';
        if (Schema::hasColumn($payTable, $amountCol)) {
            $selects[] = "{$payTable}.{$amountCol} as _pay_amount";
        }

        $currCol = $mapping['payments']['currency'] ?? 'currency';
        if ($currCol && Schema::hasColumn($payTable, $currCol)) {
            $selects[] = "{$payTable}.{$currCol} as _pay_currency";
        }

        $dateCol = $mapping['payments']['created_at'] ?? 'created_at';
        if ($dateCol && Schema::hasColumn($payTable, $dateCol)) {
            $selects[] = "{$payTable}.{$dateCol} as _pay_created_at";
        }

        $statusCol = $mapping['payments']['status'] ?? 'status';
        if ($statusCol && Schema::hasColumn($payTable, $statusCol)) {
            $selects[] = "{$payTable}.{$statusCol} as _pay_status";
        }

        if ($packTable && Schema::hasTable($packTable)) {
            $packNameCol = $mapping['packs']['name'] ?? 'name';
            if (Schema::hasColumn($packTable, $packNameCol)) {
                $selects[] = "{$packTable}.{$packNameCol} as _pack_name";
            }
            $packPriceCol = $mapping['packs']['price'] ?? 'price';
            if ($packPriceCol && Schema::hasColumn($packTable, $packPriceCol)) {
                $selects[] = "{$packTable}.{$packPriceCol} as _pack_price";
            }
        }

        if ($userTable && Schema::hasTable($userTable)) {
            $userNameCol = $mapping['users']['name'] ?? 'name';
            if (Schema::hasColumn($userTable, $userNameCol)) {
                $selects[] = "{$userTable}.{$userNameCol} as _user_name";
            }
            $userEmailCol = $mapping['users']['email'] ?? 'email';
            if (Schema::hasColumn($userTable, $userEmailCol)) {
                $selects[] = "{$userTable}.{$userEmailCol} as _user_email";
            }
        }

        $rawRows = $query->select($selects)->limit(500)->get();

        $normalized = [];
        foreach ($rawRows as $row) {
            $rowArr = (array) $row;
            $orderId = 'PAY-' . ($rowArr['_pay_id'] ?? $rowArr['id'] ?? rand(1000, 9999));
            $amount = (float) ($rowArr['_pay_amount'] ?? $rowArr[$amountCol] ?? 0.0);
            $currency = strtoupper((string) ($rowArr['_pay_currency'] ?? $rowArr[$currCol] ?? 'EUR'));
            $pack = (string) ($rowArr['_pack_name'] ?? $rowArr['pack_name'] ?? $rowArr['name'] ?? 'Pack Standard');
            $userName = (string) ($rowArr['_user_name'] ?? $rowArr['user_name'] ?? $rowArr['name'] ?? 'Client Anonyme');
            $userEmail = (string) ($rowArr['_user_email'] ?? $rowArr['user_email'] ?? $rowArr['email'] ?? 'client@domaine.com');
            $date = (string) ($rowArr['_pay_created_at'] ?? $rowArr[$dateCol] ?? date('Y-m-d H:i:s'));
            $status = (string) ($rowArr['_pay_status'] ?? $rowArr['status'] ?? 'COMPLETED');

            $normalized[] = [
                'orderId' => $orderId,
                'amount' => $amount,
                'currency' => $currency,
                'pack' => $pack,
                'userName' => $userName,
                'userEmail' => $userEmail,
                'date' => $date,
                'status' => $status,
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

    /**
     * Calcule et formate l'ensemble de la charge utile analytique (KPIs, Packs, Transactions) en temps réel.
     */
    public function getAnalyticsPayload(array $mapping = []): array
    {
        if (empty($mapping)) {
            $mapping = config('metrics-dash.mapping', []);
        }

        $transactions = $this->extractMappedTransactions($mapping);

        $totalRevenue = 0.0;
        $totalTransactions = count($transactions);
        $packMap = [];
        $dailyRevenueMap = [];

        foreach ($transactions as $tx) {
            $amount = (float) ($tx['amount'] ?? 0.0);
            $packName = trim((string) ($tx['pack'] ?? 'Formule Standard'));
            $dateKey = substr((string) ($tx['date'] ?? date('Y-m-d')), 0, 10);

            $totalRevenue += $amount;

            if (! isset($dailyRevenueMap[$dateKey])) {
                $dailyRevenueMap[$dateKey] = ['revenue' => 0.0, 'transactions' => 0];
            }
            $dailyRevenueMap[$dateKey]['revenue'] += $amount;
            $dailyRevenueMap[$dateKey]['transactions']++;

            if ($packName !== '') {
                if (! isset($packMap[$packName])) {
                    $packMap[$packName] = [
                        'revenue' => 0.0,
                        'count' => 0,
                        'price' => $amount,
                    ];
                }
                $packMap[$packName]['revenue'] += $amount;
                $packMap[$packName]['count']++;
            }
        }

        // Pourcentages par pack
        foreach ($packMap as $k => $p) {
            $packMap[$k]['percentage'] = $totalRevenue > 0
                ? round(($p['revenue'] / $totalRevenue) * 100, 1)
                : 0;
            $packMap[$k]['revenue'] = round($packMap[$k]['revenue'], 2);
        }

        // Séries temporelles quotidiennes de chiffre d'affaires
        ksort($dailyRevenueMap);
        $revenueTimeSeries = [];
        foreach ($dailyRevenueMap as $dateStr => $revData) {
            $revenueTimeSeries[] = [
                'date' => $dateStr,
                'revenue' => round($revData['revenue'], 2),
                'transactions' => $revData['transactions'],
            ];
        }

        $mrr = round($totalRevenue * 0.85, 2);
        $averageOrderValue = $totalTransactions > 0 ? round($totalRevenue / $totalTransactions, 2) : 0;

        return [
            'kpis' => [
                'page_views' => 0,
                'unique_visitors' => 0,
                'sessions' => 0,
                'conversions' => $totalTransactions,
                'bounce_rate' => 0,
            ],
            'financialKpis' => [
                'total_revenue' => round($totalRevenue, 2),
                'mrr' => $mrr,
                'average_order_value' => $averageOrderValue,
                'total_transactions' => $totalTransactions,
                'conversion_rate' => 0,
            ],
            'packBreakdown' => $packMap,
            'timeSeries' => [],
            'revenueTimeSeries' => $revenueTimeSeries,
            'recentTransactions' => array_slice($transactions, 0, 50),
        ];
    }
}

