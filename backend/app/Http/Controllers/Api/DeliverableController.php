<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deliverable;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;
use App\Http\Controllers\Api\ContractController;

class DeliverableController extends Controller
{
    /**
     * Freelancer submitting work.
     */
    public function store(Request $request)
    {
        $request->validate([
            'contract_id' => 'required|exists:contracts,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'file' => 'nullable|file|max:10240', // 10MB max
        ]);

        $contract = Contract::findOrFail($request->contract_id);
        
        if ($contract->freelancer_id !== $request->user()->id) {
            return response()->json(['message' => 'Vous n’êtes pas autorisé à soumettre un livrable pour ce contrat.'], 403);
        }

        if ($contract->status !== 'active') {
             return response()->json(['message' => 'Le contrat doit être actif pour soumettre un livrable.'], 400);
        }

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('deliverables', 'public');
        }

        $version = Deliverable::where('contract_id', $contract->id)->max('version') + 1;

        $deliverable = Deliverable::create([
            'contract_id' => $contract->id,
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $filePath,
            'status' => 'pending',
            'version' => $version
        ]);

        // Notify client
        NotificationService::deliverableSubmitted($deliverable);

        return response()->json([
            'message' => 'Livrable soumis avec succès. En attente de validation par le client.',
            'deliverable' => $deliverable
        ], 201);
    }

    /**
     * Client reviewing work.
     */
    public function review(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:accept,request_revision',
            'feedback' => 'nullable|string'
        ]);

        $deliverable = Deliverable::with('contract')->findOrFail($id);
        $contract = $deliverable->contract;

        if ($contract->client_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        if ($request->action === 'accept') {
            return DB::transaction(function () use ($deliverable, $contract) {
                $deliverable->update(['status' => 'accepted', 'feedback' => $request->feedback]);
                
                // Notify freelancer
                NotificationService::deliverableAccepted($deliverable);

                // Trigger contract completion logic (Escrow release)
                $contractController = new ContractController();
                return $contractController->complete($contract->id);
            });
        } else {
            $deliverable->update([
                'status' => 'revision_requested',
                'feedback' => $request->feedback
            ]);

            // Notify freelancer
            NotificationService::revisionRequested($deliverable);

            return response()->json([
                'message' => 'Demande de révision envoyée au freelancer.',
                'deliverable' => $deliverable
            ]);
        }
    }

    /**
     * Get deliverables for a contract.
     */
    public function listByContract($contractId)
    {
        $deliverables = Deliverable::where('contract_id', $contractId)
            ->latest()
            ->get();
            
        return response()->json($deliverables);
    }
}
