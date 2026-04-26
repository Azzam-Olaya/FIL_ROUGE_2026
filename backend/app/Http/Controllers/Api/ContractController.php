<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Contrôleur Contrat & Paiement : Gère les fonds bloqués, les commissions et les remboursements.
 */
class ContractController extends Controller
{
    public function clientContracts(Request $request)
    {
        return response()->json(
            Contract::where('client_id', $request->user()->id)
                ->with(['mission', 'freelancer'])
                ->latest()->get()
        );
    }

    public function freelancerContracts(Request $request)
    {
        return response()->json(
            Contract::where('freelancer_id', $request->user()->id)
                ->with(['mission', 'client'])
                ->latest()->get()
        );
    }

    /**
     * Création d'un contrat (Offre envoyée par le client)
     * Le client verse la somme totale qui est mise en attente.
     */
    public function store(Request $request)
    {
        $request->validate([
            'mission_id' => 'nullable|exists:missions,id', // Optionnel si lancé via chat direct
            'freelancer_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1',
            'specifications' => 'required|string',
            'technologies' => 'required|string',
            'deadline' => 'required|date|after_or_equal:today',
        ]);

        $client = $request->user();
        
        if ($client->balance < $request->amount) {
            return response()->json(['message' => 'Solde insuffisant pour envoyer cette offre.'], 400);
        }

        return DB::transaction(function () use ($request, $client) {
            try {
                // Débiter le solde du client immédiatement (Escrow)
                $client->decrement('balance', $request->amount);

                $commission = $request->amount * 0.05;

                // Debug: Check if mission_id is null and find a fallback if necessary for testing
                $missionId = $request->mission_id;
                if (!$missionId) {
                    $missionId = \App\Models\Mission::first()?->id;
                }

                $contract = Contract::create([
                    'mission_id' => $missionId,
                    'client_id' => $client->id,
                    'freelancer_id' => $request->freelancer_id,
                    'amount' => $request->amount,
                    'commission' => $commission,
                    'specifications' => $request->specifications,
                    'technologies' => $request->technologies,
                    'deadline' => $request->deadline,
                    'status' => 'pending_freelancer',
                ]);

                return response()->json([
                    'message' => 'Offre de contrat envoyée au freelancer. Les fonds sont sécurisés.',
                    'contract' => $contract
                ], 201);

            } catch (\Illuminate\Database\QueryException $e) {
                // Re-créditer le client en cas d'erreur DB
                $client->increment('balance', $request->amount);
                
                return response()->json([
                    'message' => 'Erreur technique lors de la création du contrat.',
                    'debug' => 'Vérifiez si mission_id est obligatoire dans votre base. Détail: ' . $e->getMessage()
                ], 400);
            }
        });
    }

    /**
     * Modification du contrat par le client (Avant acceptation)
     */
    public function update(Request $request, $id)
    {
        $contract = Contract::findOrFail($id);
        
        if ($contract->client_id !== auth()->id() || $contract->status !== 'pending_freelancer') {
            return response()->json(['message' => 'Action non autorisée ou contrat déjà validé.'], 403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'specifications' => 'required|string',
            'technologies' => 'required|string',
            'deadline' => 'required|date|after_or_equal:today',
        ]);

        return DB::transaction(function () use ($request, $contract) {
            $client = auth()->user();
            $diff = $request->amount - $contract->amount;

            if ($diff > 0) {
                // Le nouveau prix est plus élevé, vérifier le solde
                if ($client->balance < $diff) {
                    throw new \Exception('Solde insuffisant pour augmenter le prix du contrat.');
                }
                $client->decrement('balance', $diff);
            } elseif ($diff < 0) {
                // Le nouveau prix est plus bas, rembourser la différence
                $client->increment('balance', abs($diff));
            }

            $contract->update([
                'amount' => $request->amount,
                'commission' => $request->amount * 0.05,
                'specifications' => $request->specifications,
                'technologies' => $request->technologies,
                'deadline' => $request->deadline,
            ]);

            return response()->json(['message' => 'Contrat mis à jour avec succès.', 'contract' => $contract]);
        });
    }

    /**
     * Acceptation par le freelancer
     */
    public function acceptByFreelancer($id)
    {
        $contract = Contract::findOrFail($id);
        
        if ($contract->freelancer_id !== auth()->id()) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if ($contract->status !== 'pending_freelancer') {
            return response()->json(['message' => 'Ce contrat ne peut plus être accepté.'], 400);
        }

        $contract->update(['status' => 'active']);

        return response()->json(['message' => 'Contrat accepté ! Vous pouvez commencer le travail.', 'contract' => $contract]);
    }

    /**
     * Refus par le freelancer
     */
    public function rejectByFreelancer($id)
    {
        $contract = Contract::findOrFail($id);
        
        if ($contract->freelancer_id !== auth()->id()) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        return DB::transaction(function () use ($contract) {
            // Rembourser le client intégralement
            $client = User::find($contract->client_id);
            $client->increment('balance', $contract->amount);

            $contract->update(['status' => 'cancelled']);

            return response()->json(['message' => 'Contrat refusé. Le client a été remboursé.']);
        });
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
