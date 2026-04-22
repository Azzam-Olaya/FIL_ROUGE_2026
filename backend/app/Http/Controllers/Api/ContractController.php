<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Contrôleur Contrat & Paiement : Gère les fonds bloqués, les commissions et les remboursements.
 */
class ContractController extends Controller
{
    /**
     * Création d'un contrat (Validation du devis par le client)
     * Le client verse la somme totale qui est "bloquée" dans l'application.
     */
    public function store(Request $request)
    {
        // Validation des données
        $request->validate([
            'mission_id' => 'required|exists:missions,id',
            'freelancer_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1', // Montant total du contrat
        ]);

        $client = $request->user();
        
        // Vérification que le client a assez d'argent sur son solde
        if ($client->balance < $request->amount) {
            return response()->json(['message' => 'Solde insuffisant pour valider ce contrat'], 400);
        }

        /**
         * LOGIQUE DE PAIEMENT CLIENT :
         * On décrémente le solde du client immédiatement.
         * L'argent est maintenant "dans l'app" (fond bloqué).
         */
        $client->decrement('balance', $request->amount);

        // Calcul de la commission de la plateforme (5%)
        $commission = $request->amount * 0.05;

        // Création de l'enregistrement du contrat
        $contract = Contract::create([
            'mission_id' => $request->mission_id,
            'client_id' => $client->id,
            'freelancer_id' => $request->freelancer_id,
            'amount' => $request->amount,
            'commission' => $commission,
            'status' => 'active', // Le contrat est en cours d'exécution
        ]);

        return response()->json([
            'message' => 'Contrat créé et fonds bloqués avec succès.',
            'contract' => $contract
        ], 201);
    }

    /**
     * Finalisation du contrat (Validation de livraison par le client)
     * Le freelance reçoit ses fonds (Montant - Commission).
     */
    public function complete($id)
    {
        $contract = Contract::findOrFail($id);
        
        if ($contract->status !== 'active') {
            return response()->json(['message' => 'Ce contrat n’est pas actif.'], 400);
        }

        // Mise à jour du statut
        $contract->update(['status' => 'completed']);
        
        /**
         * LOGIQUE DE PAIEMENT FREELANCE :
         * Le freelance reçoit le montant net (Moins les 5% de commission).
         */
        $netAmount = $contract->amount - $contract->commission;
        
        $freelancer = User::find($contract->freelancer_id);
        $freelancer->increment('balance', $netAmount);

        return response()->json([
            'message' => 'Projet validé ! Le freelance a reçu ses fonds (net de commission).',
            'net_amount' => $netAmount,
            'commission_platforme' => $contract->commission
        ]);
    }

    /**
     * Remboursement (En cas de litige ou insatisfaction)
     * Le client est remboursé de 97.5% de la somme (2.5% de frais de service retenus).
     */
    public function refund($id)
    {
        $contract = Contract::findOrFail($id);

        if ($contract->status !== 'active') {
            return response()->json(['message' => 'Impossible de rembourser un contrat non actif.'], 400);
        }

        // Calcul du remboursement (97.5%)
        $refundAmount = $contract->amount * 0.975;
        $serviceFees = $contract->amount - $refundAmount;

        // Mise à jour du contrat
        $contract->update(['status' => 'refunded']);

        // Créditer le client
        $client = User::find($contract->client_id);
        $client->increment('balance', $refundAmount);

        return response()->json([
            'message' => 'Remboursement effectué avec succès (97.5% du montant).',
            'amount_refunded' => $refundAmount,
            'service_fees_kept' => $serviceFees
        ]);
    }
}
