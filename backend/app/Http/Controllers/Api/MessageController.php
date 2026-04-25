<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Liste des conversations de l'utilisateur connecté
     * (derniers messages groupés par interlocuteur)
     */
    public function getConversations(Request $request)
    {
        $userId = $request->user()->id;

        // Tous les messages impliquant cet utilisateur
        $messages = Message::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Grouper par interlocuteur et garder le dernier message
        $conversations = [];
        foreach ($messages as $msg) {
            $otherId = $msg->sender_id === $userId ? $msg->receiver_id : $msg->sender_id;
            if (!isset($conversations[$otherId])) {
                $other = $msg->sender_id === $userId ? $msg->receiver : $msg->sender;
                $conversations[$otherId] = [
                    'user_id'     => (int) $otherId,
                    'name'        => $other?->name ?? 'Utilisateur',
                    'initials'    => $this->initials($other?->name),
                    'lastMessage' => $msg->content,
                    'time'        => $msg->created_at->format('H:i'),
                    'unread'      => $msg->receiver_id === $userId && is_null($msg->read_at),
                ];
            }
        }

        return response()->json(array_values($conversations));
    }

    /**
     * Messages entre l'utilisateur connecté et un autre utilisateur
     */
    public function getMessages(Request $request, $userId)
    {
        $me     = (int) $request->user()->id;
        $userId = (int) $userId;

        // Marquer comme lus les messages reçus
        Message::where('sender_id', $userId)
            ->where('receiver_id', $me)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = Message::conversation($me, $userId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($m) => [
                'id'     => $m->id,
                'sender' => ((int)$m->sender_id === $me) ? 'me' : 'other',
                'text'   => $m->content,
                'time'   => $m->created_at->format('H:i'),
            ]);

        return response()->json($messages);
    }

    /**
     * Envoyer un message à un utilisateur
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'content'     => 'required|string|max:2000',
        ]);

        $message = Message::create([
            'sender_id'   => $request->user()->id,
            'receiver_id' => $request->receiver_id,
            'content'     => $request->content,
        ]);

        return response()->json([
            'id'     => $message->id,
            'sender' => 'me',
            'text'   => $message->content,
            'time'   => $message->created_at->format('H:i'),
        ], 201);
    }

    /**
     * Conversation d'un contrat (legacy)
     */
    public function getConversation($contractId)
    {
        $user     = auth()->user();
        $contract = \App\Models\Contract::findOrFail($contractId);

        if ($user->role_id !== 1 && $user->id !== $contract->client_id && $user->id !== $contract->freelancer_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(
            Message::conversation($contract->client_id, $contract->freelancer_id)
                ->with(['sender', 'receiver'])
                ->orderBy('created_at', 'asc')
                ->get()
        );
    }

    private function initials(?string $name): string
    {
        if (!$name) return 'U';
        return collect(explode(' ', $name))->map(fn($n) => strtoupper($n[0]))->join('');
    }
}
