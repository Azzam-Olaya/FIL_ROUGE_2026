<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\Contract;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    /**
     * Open a new dispute for a contract.
     */
    public function store(Request $request)
    {
        $request->validate([
            'contract_id' => 'required|exists:contracts,id',
            'title' => 'required|string|max:255',
            'reason' => 'required|string',
        ]);

        $contract = Contract::findOrFail($request->contract_id);
        $user = $request->user();

        // Check if user is part of the contract
        if ($user->id !== $contract->client_id && $user->id !== $contract->freelancer_id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        // Check if contract is active
        if ($contract->status !== 'active') {
            return response()->json(['message' => 'Litige possible uniquement sur un contrat actif.'], 400);
        }

        // Check if a dispute is already open
        if (Dispute::where('contract_id', $contract->id)->where('status', 'open')->exists()) {
            return response()->json(['message' => 'Un litige est déjà en cours pour ce contrat.'], 400);
        }

        $dispute = Dispute::create([
            'contract_id' => $contract->id,
            'user_id' => $user->id,
            'title' => $request->title,
            'reason' => $request->reason,
            'status' => 'open'
        ]);

        return response()->json([
            'message' => 'Litige ouvert. Un administrateur va examiner votre demande.',
            'dispute' => $dispute
        ], 201);
    }

    /**
     * Get disputes for a contract.
     */
    public function getByContract($contractId)
    {
        return response()->json(Dispute::where('contract_id', $contractId)->latest()->get());
    }
}
