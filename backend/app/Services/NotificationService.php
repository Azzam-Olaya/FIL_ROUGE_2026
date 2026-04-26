<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    /**
     * Send a notification to a user.
     */
    public static function send($userId, $type, $title, $message, $missionId = null)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'mission_id' => $missionId,
            'read' => false
        ]);
    }

    // Specialized notifications
    public static function contractCreated($contract)
    {
        self::send(
            $contract->freelancer_id,
            'contract',
            '📜 Nouveau contrat !',
            "Le client a validé votre devis pour la mission : {$contract->mission->title}",
            $contract->mission_id
        );
    }

    public static function deliverableSubmitted($deliverable)
    {
        $contract = $deliverable->contract;
        self::send(
            $contract->client_id,
            'deliverable',
            '📦 Nouveau livrable soumis',
            "Le freelancer a soumis une nouvelle version ({$deliverable->version}) pour : {$contract->mission->title}",
            $contract->mission_id
        );
    }

    public static function deliverableAccepted($deliverable)
    {
        $contract = $deliverable->contract;
        self::send(
            $contract->freelancer_id,
            'payment',
            '💰 Paiement reçu !',
            "Le client a validé votre dernier livrable. Les fonds ont été libérés dans votre portefeuille.",
            $contract->mission_id
        );
    }

    public static function revisionRequested($deliverable)
    {
        $contract = $deliverable->contract;
        self::send(
            $contract->freelancer_id,
            'revision',
            '⚠️ Révision demandée',
            "Le client demande une révision pour le livrable : {$deliverable->title}",
            $contract->mission_id
        );
    }
}
