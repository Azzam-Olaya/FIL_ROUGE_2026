<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\BriefFavorite;
use App\Models\Brief;
use App\Models\BriefLike;
use App\Models\BriefComment;
use App\Models\Mission;
use App\Models\MissionFavorite;
use App\Models\Notification;
use App\Models\MissionApplication;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function storeMission(Request $request)
    {
        $this->ensureTablesExist();
        try {
            $request->validate([
                'category_id'  => 'required|exists:categories,id',
                'category_ids' => 'nullable|array',
                'category_ids.*' => 'exists:categories,id',
                'title'        => 'required|string|max:255',
                'description'  => 'required|string',
                'budget'       => 'required|numeric|min:0',
                'deadline'     => 'required|date|after:today',
            ]);

            $mission = \App\Models\Mission::create([
                'client_id'   => $request->user()->id,
                'category_id' => $request->category_id,
                'title'       => $request->title,
                'description' => $request->description,
                'budget'      => $request->budget,
                'deadline'    => $request->deadline,
                'status'      => 'open',
            ]);

            if ($request->category_ids) {
                $mission->categories()->sync($request->category_ids);
            }

            return response()->json($mission->load('categories'), 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getMyMissions(Request $request)
    {
        $this->ensureTablesExist();
        try {
            return response()->json(
                $request->user()->missions()
                    ->with(['category', 'categories', 'likes.user'])
                    ->withCount(['likes', 'comments'])
                    ->latest()
                    ->get()
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getMissionComments($id)
    {
        return response()->json(
            \App\Models\MissionComment::where('mission_id', $id)
                ->with('user.role')
                ->latest()
                ->get()
        );
    }

    public function getMissionLikes($id)
    {
        return response()->json(
            \App\Models\MissionLike::where('mission_id', $id)
                ->with('user.role')
                ->latest()
                ->get()
                ->pluck('user')
        );
    }

    public function getNotifications(Request $request)
    {
        return response()->json(
            \App\Models\Notification::where('user_id', $request->user()->id)
                ->latest()->take(30)->get()
        );
    }

    public function getMyFavorites(Request $request)
    {
        return response()->json(
            BriefFavorite::where('user_id', $request->user()->id)
                ->with(['brief.category', 'brief.freelancer'])
                ->get()
                ->pluck('brief')
        );
    }

    public function getMyMissionFavorites(Request $request)
    {
        return response()->json(
            MissionFavorite::where('user_id', $request->user()->id)
                ->with(['mission.category', 'mission.client'])
                ->get()
                ->pluck('mission')
        );
    }

    public function markOneRead(Request $request, $id)
    {
        \App\Models\Notification::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->update(['read' => true]);
        return response()->json(['ok' => true]);
    }

    public function markAllRead(Request $request)
    {
        \App\Models\Notification::where('user_id', $request->user()->id)
            ->where('read', false)
            ->update(['read' => true]);
        return response()->json(['ok' => true]);
    }

    public function getBriefs(Request $request)
    {
        $query = \App\Models\Brief::with(['freelancer', 'category', 'likes', 'comments.user']);

        if ($request->search) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        if ($request->category_id) {
            $catId = $request->category_id;
            // Include subcategories if any
            $childIds = \App\Models\Category::where('parent_id', $catId)->pluck('id')->toArray();
            $ids = array_merge([$catId], $childIds);
            $query->whereIn('category_id', $ids);
        }

        return response()->json(
            $query->latest()->get()
                ->map(fn($b) => [
                    'id'          => $b->id,
                    'title'       => $b->title,
                    'description' => $b->description,
                    'category'    => $b->category?->name,
                    'category_id' => $b->category_id,
                    'freelancerName' => $b->freelancer?->name,
                    'freelancerId'   => $b->freelancer_id,
                    'price'       => $b->price,
                    'timeline'    => $b->duration,
                    'likes'       => $b->likes->count(),
                    'comments'    => $b->comments->count(),
                    'commentList' => $b->comments->map(fn($c) => ['id' => $c->id, 'author' => $c->user?->name, 'text' => $c->body]),
                    'createdAt'   => $b->created_at,
                ])
        );
    }

    public function toggleLike(Request $request, $id)
    {
        $brief = Brief::findOrFail($id);
        $existing = BriefLike::where(['brief_id' => $id, 'user_id' => $request->user()->id])->first();
        if ($existing) {
            $existing->delete();
            return response()->json(['liked' => false]);
        }
        BriefLike::create(['brief_id' => $id, 'user_id' => $request->user()->id]);

        // Notify Freelancer
        Notification::create([
            'user_id' => $brief->freelancer_id,
            'type'    => 'like',
            'title'   => '❤️ Nouveau like sur votre brief',
            'message' => $request->user()->name . ' a aimé votre brief "' . $brief->title . '"',
            'brief_id' => $id // Use mission_id if we want to reuse the column or add one
        ]);

        return response()->json(['liked' => true]);
    }

    public function addComment(Request $request, $id)
    {
        $request->validate(['body' => 'required|string|max:500']);
        $brief = Brief::findOrFail($id);
        $comment = BriefComment::create([
            'brief_id' => $id,
            'user_id'      => $request->user()->id,
            'body'         => $request->body,
        ]);

        // Notify Freelancer
        Notification::create([
            'user_id' => $brief->freelancer_id,
            'type'    => 'comment',
            'title'   => '💬 Nouveau commentaire sur votre brief',
            'message' => $request->user()->name . ' : "' . \Str::limit($request->body, 60) . '" sur "' . $brief->title . '"',
        ]);

        return response()->json($comment->load('user'), 201);
    }

    public function getComments($id)
    {
        return response()->json(
            \App\Models\BriefComment::where('brief_id', $id)->with('user')->latest()->get()
        );
    }

    public function toggleFavorite(Request $request, $id)
    {
        $existing = \App\Models\BriefFavorite::where(['brief_id' => $id, 'user_id' => $request->user()->id])->first();
        if ($existing) { $existing->delete(); return response()->json(['favorited' => false]); }
        \App\Models\BriefFavorite::create(['brief_id' => $id, 'user_id' => $request->user()->id]);
        return response()->json(['favorited' => true]);
    }

    public function getPayments(Request $request)
    {
        $user = $request->user();

        // Get contracts
        $contracts = \App\Models\Contract::where('client_id', $user->id)
            ->with(['mission', 'freelancer'])
            ->latest()
            ->get()
            ->map(function($c) {
                return [
                    'id' => 'c_' . $c->id,
                    'type' => 'contract_payment',
                    'title' => $c->mission?->title ?? 'Contrat Direct',
                    'party' => $c->freelancer?->name,
                    'amount' => $c->amount,
                    'status' => $c->status,
                    'date' => $c->created_at,
                    'raw_id' => $c->id
                ];
            });

        // Get transactions (recharges, etc.)
        try {
            $transactions = \App\Models\Transaction::where('user_id', $user->id)
                ->latest()
                ->get()
                ->map(function($t) {
                    return [
                        'id' => 't_' . $t->id,
                        'type' => $t->type,
                        'title' => $t->description,
                        'party' => 'Système',
                        'amount' => $t->amount,
                        'status' => $t->status,
                        'date' => $t->created_at,
                        'raw_id' => $t->id
                    ];
                });
        } catch (\Exception $e) {
            $transactions = collect([]);
        }

        // Combine and sort
        $all = $contracts->concat($transactions)->sortByDesc('date')->values();

        return response()->json($all);
    }

    public function getStats(Request $request)
    {
        $user = $request->user();

        $totalMissions = \App\Models\Mission::where('client_id', $user->id)->count();
        $totalContracts = \App\Models\Contract::where('client_id', $user->id)->count();
        $totalSpent = \App\Models\Contract::where('client_id', $user->id)->whereIn('status', ['paid', 'completed'])->sum('amount');
        
        $totalDeposited = 0;
        try {
            $totalDeposited = \App\Models\Transaction::where('user_id', $user->id)->where('type', 'deposit')->where('status', 'completed')->sum('amount');
        } catch (\Exception $e) {}
        
        $blockedEscrow = \App\Models\Contract::where('client_id', $user->id)->whereIn('status', ['pending_freelancer', 'active'])->sum('amount');

        return response()->json([
            'total_missions'  => $totalMissions,
            'total_contracts' => $totalContracts,
            'total_spent'     => round($totalSpent, 2),
            'total_deposited' => round($totalDeposited, 2),
            'blocked_escrow'  => round($blockedEscrow, 2),
            'balance'         => $user->balance,
        ]);
    }

    /**
     * Voir les candidatures pour une mission spécifique
     */
    public function getApplications($mission_id)
    {
        $mission = Mission::where('client_id', auth()->id())->findOrFail($mission_id);
        $applications = MissionApplication::where('mission_id', $mission_id)
            ->with('freelancer')
            ->latest()
            ->get();
        
        return response()->json($applications);
    }

    /**
     * Accepter une candidature et CRÉER LE CONTRAT (Lance le projet)
     */
    public function acceptApplication(Request $request, $application_id)
    {
        $application = MissionApplication::with('mission')->findOrFail($application_id);
        $mission = $application->mission;

        if ($mission->client_id !== auth()->id()) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if ($application->status !== 'pending') {
            return response()->json(['message' => 'Cette candidature n’est plus en attente.'], 400);
        }

        return DB::transaction(function () use ($application, $mission, $request) {
            // Mettre à jour le statut de la candidature
            $application->update(['status' => 'accepted']);
            
            // Rejeter les autres candidatures
            MissionApplication::where('mission_id', $mission->id)
                ->where('id', '!=', $application->id)
                ->update(['status' => 'rejected']);

            // Mettre à jour le statut de la mission
            $mission->update(['status' => 'in_progress']);

            /**
             * Lancer le contrat via ContractController
             */
            $contractData = new Request([
                'mission_id' => $mission->id,
                'freelancer_id' => $application->freelancer_id,
                'amount' => $application->price,
            ]);
            
            $request->setUserResolver(fn() => auth()->user()); 
            
            $contractController = new \App\Http\Controllers\Api\ContractController();
            $contractResponse = $contractController->store($contractData);

            if ($contractResponse->getStatusCode() !== 201) {
                throw new \Exception($contractResponse->getData()->message);
            }

            return response()->json([
                'message' => 'Candidature acceptée ! Le projet est lancé et le contrat a été créé.',
                'contract' => $contractResponse->getData()->contract,
                'application' => $application
            ]);
        });
    }

    private function ensureTablesExist()
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('mission_category')) {
                \Illuminate\Support\Facades\DB::statement('CREATE TABLE mission_category (mission_id BIGINT NOT NULL REFERENCES missions(id) ON DELETE CASCADE, category_id BIGINT NOT NULL REFERENCES categories(id) ON DELETE CASCADE, PRIMARY KEY (mission_id, category_id))');
            }
            if (!\Illuminate\Support\Facades\Schema::hasTable('mission_comments')) {
                \Illuminate\Support\Facades\DB::statement("CREATE TABLE mission_comments (id SERIAL PRIMARY KEY, mission_id BIGINT NOT NULL REFERENCES missions(id) ON DELETE CASCADE, user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE, body TEXT NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL)");
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('briefs', 'price')) {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE briefs ADD COLUMN price DECIMAL(10,2) NULL');
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('briefs', 'duration')) {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE briefs ADD COLUMN duration VARCHAR(100) NULL');
            }
            if (!\Illuminate\Support\Facades\Schema::hasTable('mission_favorites')) {
                \Illuminate\Support\Facades\DB::statement("CREATE TABLE mission_favorites (id SERIAL PRIMARY KEY, mission_id BIGINT NOT NULL REFERENCES missions(id) ON DELETE CASCADE, user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE(mission_id, user_id))");
            }
        } catch (\Exception $e) {}
    }

    public function testCredit(Request $request) {
        $request->validate(['amount' => 'required|numeric|min:1']);
        $user = $request->user();
        $user->increment('balance', $request->amount);
        return response()->json(['message' => 'Crédit test ajouté.', 'balance' => $user->balance]);
    }
}
