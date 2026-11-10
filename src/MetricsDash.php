<?php

namespace MetricsDash\Laravel;

use MetricsDash\Laravel\Services\DatabaseMappingService;

/**
 * Envoi d'une commande à MetricsDash dès qu'elle est payée (sans attendre metrics-dash:sync).
 *
 *   MetricsDash::trackOrder($order->id);
 */
class MetricsDash
{
    public const SENT = 'sent';
    public const ALREADY_SENT = 'already_sent';
    public const NOT_FOUND = 'not_found';
    public const NOT_CONFIGURED = 'not_configured';
    public const FAILED = 'failed';

    public static function isConfigured(): bool
    {
        return (string) config('metrics-dash.push_key') !== ''
            && ! empty(config('metrics-dash.mapping.payments.table'));
    }

    /**
     * Envoie la commande dont l'identifiant est celui de la table des paiements mappée.
     * Ne lève jamais d'exception : le résultat est l'une des constantes ci-dessus
     * (FAILED = à réessayer ; les autres ne servent à rien de réessayer).
     */
    public static function trackOrder(string|int $paymentId): string
    {
        if (! self::isConfigured()) {
            return self::NOT_CONFIGURED;
        }

        try {
            return app(DatabaseMappingService::class)->trackOrder($paymentId);
        } catch (\Throwable $e) {
            logger()->warning('[MetricsDash SDK] Envoi de la commande '.$paymentId.' échoué : '.$e->getMessage());

            return self::FAILED;
        }
    }
}
