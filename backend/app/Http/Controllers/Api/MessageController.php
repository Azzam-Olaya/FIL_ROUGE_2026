<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /** Liste des conversations de l'utilisateur connecté */
    public function getConversations(Request $request)
    {
        $me = $request->user()->id;

        $messages = Message::where('sender_id', $me)
            ->orWhere('receiver_id', $me)
            ->with(['sender:id,name', 'receiver:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        $conversations = [];
        foreach ($messages as $msg) {
            $otherId = $msg->sender_id === $me ? $msg->receiver_id : $msg->sender_id;
            if (isset($conversations[$otherId])) continue;
            $other = $msg->sender_id === $me ? $msg->receiver : $msg->sender;
            $conversations[$otherId] = [
                'user_id'     => $otherId,
                'name'        => $other?->name ?? 'Utilisateur',
                'lastMessage' => \Str::limit($msg->content, 50),
                'time'        => $msg->created_at->format('H:i'),
                'unread'      => $msg->receiver_id === $me && is_null($msg->read_at),
            ];
        }

        return response()->json(array_values($conversations));
    }

    /** Messages entre l'utilisateur connecté et un autre */
    public function getMessages(Request $request, $userId)
    {
        $me = $request->user()->id;

        Message::where('sender_id', $userId)
            ->where('receiver_id', $me)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = Message::conversation($me, (int)$userId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($m) => [
                'id'         => $m->id,
                'sender'     => $m->sender_id === $me ? 'me' : 'other',
                'text'       => $m->content,
                'time'       => $m->created_at->format('H:i'),
                'created_at' => $m->created_at,
            ]);

        return response()->json($messages);
    }

    /** Envoyer un message */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'content'     => 'required|string|max:2000',
        ]);

        $msg = Message::create([
            'sender_id'   => $request->user()->id,
            'receiver_id' => $request->receiver_id,
            'content'     => $request->content,
        ]);

        return response()->json([
            'id'     => $msg->id,
            'sender' => 'me',
            'text'   => $msg->content,
            'time'   => $msg->created_at->format('H:i'),
        ], 201);
    }

    /** Recherche d'utilisateurs pour démarrer une conversation */
    public function searchUsers(Request $request)
    {
        $q = $request->query('q', '');
        if (strlen($q) < 2) return response()->json([]);

        return response()->json(
            User::where('id', '!=', $request->user()->id)
                ->where('name', 'ilike', "%{$q}%")
                ->with('role:id,name')
                ->select('id', 'name', 'role_id')
                ->limit(8)
                ->get()
        );
    }

    /** Conversation d'un contrat (legacy) */
    public function getConversation($contractId)
    {
        $user     = auth()->user();
        $contract = \App\Models\Contract::findOrFail($contractId);

        if ($user->role_id !== 1 && $user->id !== $contract->client_id && $user->id !== $contract->freelancer_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(
            Message::conversation($contract->client_id, $contract->freelancer_id)
                ->with(['sender:id,name', 'receiver:id,name'])
                ->orderBy('created_at', 'asc')
                ->get()
        );
    }
}
