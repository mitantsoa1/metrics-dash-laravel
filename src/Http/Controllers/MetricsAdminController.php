<?php

namespace MetricsDash\Laravel\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use MetricsDash\Laravel\Services\DatabaseMappingService;

class MetricsAdminController extends Controller
{
    protected DatabaseMappingService $mappingService;

    public function __construct(DatabaseMappingService $mappingService)
    {
        $this->mappingService = $mappingService;
    }

    /**
     * Rendu de l'interface d'administration de mappage (/metrics-dash).
     */
    public function index()
    {
        $tables = $this->mappingService->getTables();
        $currentMapping = config('metrics-dash.mapping', []);

        return view('metrics-dash::mapping', [
            'tables' => $tables,
            'mapping' => $currentMapping,
        ]);
    }

    /**
     * API : Liste les tables BDD.
     */
    public function getTables()
    {
        return response()->json([
            'tables' => $this->mappingService->getTables(),
        ]);
    }

    /**
     * API : Liste les colonnes d'une table BDD.
     */
    public function getColumns(Request $request)
    {
        $table = $request->query('table');
        if (!$table) {
            return response()->json(['columns' => []]);
        }

        return response()->json([
            'columns' => $this->mappingService->getColumns($table),
        ]);
    }

    /**
     * API : Sauvegarde le mappage personnalisé des 3 tables (Paiements, Packs, Users).
     */
    public function saveMapping(Request $request)
    {
        $data = $request->validate([
            'payments.table' => 'required|string',
            'payments.amount' => 'required|string',
            'payments.currency' => 'nullable|string',
            'payments.status' => 'nullable|string',
            'payments.valid_status_value' => 'nullable|string',
            'payments.created_at' => 'nullable|string',
            'payments.foreign_user_id' => 'nullable|string',
            'payments.foreign_pack_id' => 'nullable|string',

            'packs.table' => 'nullable|string',
            'packs.primary_id' => 'nullable|string',
            'packs.name' => 'nullable|string',
            'packs.price' => 'nullable|string',

            'users.table' => 'nullable|string',
            'users.primary_id' => 'nullable|string',
            'users.name' => 'nullable|string',
            'users.email' => 'nullable|string',
        ]);

        // Prévisualiser les transactions extraites
        $preview = $this->mappingService->extractMappedTransactions($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Configuration de mappage sauvegardée avec succès !',
            'preview_count' => count($preview),
            'sample' => array_slice($preview, 0, 3),
        ]);
    }

    /**
     * API : Déclenche la synchronisation manuelle des transactions vers MetricsDash.
     */
    public function triggerSync(Request $request)
    {
        $mapping = $request->input('mapping', config('metrics-dash.mapping', []));
        $res = $this->mappingService->syncToMetricsDash($mapping);

        return response()->json([
            'status' => 'success',
            'message' => 'Synchronisation effectuée avec succès !',
            'result' => $res,
        ]);
    }

    /**
     * API Proxy Intermédiaire : Calcule et renvoie les métriques analytiques calculées en temps réel.
     */
    public function getAnalyticsMetrics(Request $request)
    {
        $mapping = config('metrics-dash.mapping', []);
        $payload = $this->mappingService->getAnalyticsPayload($mapping);

        return response()->json($payload);
    }
}

