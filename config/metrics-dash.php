<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MetricsDash Core API Configuration
    |--------------------------------------------------------------------------
    */
    'api_url' => env('METRICSDASH_API_URL', 'http://localhost:8000'),
    'push_key' => env('METRICSDASH_PUSH_KEY', ''),
    'sdk_key' => env('METRICSDASH_SDK_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Dynamic Database Table & Column Mapping Configuration
    |--------------------------------------------------------------------------
    | Stockage local de la configuration de mappage des 3 tables :
    | Payments, Packs, et Users (Acheteurs).
    */
    'mapping' => [
        // Table des paiements / transactions
        'payments' => [
            'table' => 'payments',
            'amount' => 'amount',
            'currency' => 'currency',
            'status' => 'status',
            'valid_status_value' => 'paid, succeeded',
            'created_at' => 'created_at',
            'foreign_user_id' => 'user_id',
            'foreign_pack_id' => 'pack_id',
        ],

        // Table des packages / abonnements
        'packs' => [
            'table' => 'packs',
            'primary_id' => 'id',
            'name' => 'name',
            'price' => 'price',
        ],

        // Table des utilisateurs / acheteurs
        'users' => [
            'table' => 'users',
            'primary_id' => 'id',
            'name' => 'name',
            'email' => 'email',
        ],
    ],
];
