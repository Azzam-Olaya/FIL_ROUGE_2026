<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Envoyer un message
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'contract_id' => 'required|exists:contracts,id',
            'receiver_id' => 'required|exists:users,id',
            'content' => 'required|string',
        ]);

        $message = \App\Models\Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $request->receiver_id,
            'content' => $request->content,
            'contract_id' => $request->contract_id,
        ]);

        return response()->json($message, 201);
    }

    /**
     * Voir la conversation d'un contrat
     */
    public function getConversation($contractId)
    {
        $user = auth()->user();
        $contract = \App\Models\Contract::findOrFail($contractId);

        // Seuls les participants ou l'admin peuvent voir
        if ($user->role_id !== 1 && $user->id !== $contract->client_id && $user->id !== $contract->freelancer_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messages = \App\Models\Message::where('contract_id', $contractId)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }
}
