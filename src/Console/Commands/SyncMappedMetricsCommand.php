<?php

namespace MetricsDash\Laravel\Console\Commands;

use Illuminate\Console\Command;
use MetricsDash\Laravel\Services\DatabaseMappingService;

class SyncMappedMetricsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrics-dash:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exécute l\'extraction des transactions BDD mappées et les synchronise vers MetricsDash Core';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseMappingService $mappingService): int
    {
        $this->info('🚀 Début de la synchronisation des données financières vers MetricsDash...');

        $mapping = config('metrics-dash.mapping', []);
        
        if (empty($mapping['payments']['table'])) {
            $this->error('❌ Aucune table de paiement configurée. Accédez à /metrics-dash pour paramétrer votre mappage BDD.');
            return self::FAILURE;
        }

        $result = $mappingService->syncToMetricsDash($mapping);

        $this->info("✅ Synchronisation terminée !");
        $this->table(
            ['Métrique', 'Valeur'],
            [
                ['Total transactions extraites', $result['total_extracted'] ?? 0],
                ['Transactions transmises avec succès', $result['synced_count'] ?? 0],
            ]
        );

        return self::SUCCESS;
    }
}
