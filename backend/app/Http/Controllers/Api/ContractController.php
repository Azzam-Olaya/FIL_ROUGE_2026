<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    /**
     * Créer un contrat et bloquer les fonds
     */
    public function store(Request $request)
    {
        $request->validate([
            'mission_id' => 'required|exists:missions,id',
            'freelancer_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1',
        ]);

        $client = $request->user();
        
        if ($client->balance < $request->amount) {
            return response()->json(['message' => 'Insufficient balance'], 400);
        }

        // Simuler le blocage des fonds
        $client->decrement('balance', $request->amount);

        $commission = $request->amount * 0.05;

        $contract = \App\Models\Contract::create([
            'mission_id' => $request->mission_id,
            'client_id' => $client->id,
            'freelancer_id' => $request->freelancer_id,
            'amount' => $request->amount,
            'commission' => $commission,
            'status' => 'active',
        ]);

        return response()->json($contract, 201);
    }

    /**
     * Finaliser le contrat et payer le freelance
     */
    public function complete($id)
    {
        $contract = \App\Models\Contract::findOrFail($id);
        
        if ($contract->status !== 'active') {
            return response()->json(['message' => 'Contract is not active'], 400);
        }

        $contract->update(['status' => 'completed']);
        
        $netAmount = $contract->amount - $contract->commission;
        
        $freelancer = \App\Models\User::find($contract->freelancer_id);
        $freelancer->increment('balance', $netAmount);

        return response()->json(['message' => 'Contract completed. Freelancer paid.', 'net_amount' => $netAmount]);
    }
}
