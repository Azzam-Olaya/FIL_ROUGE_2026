<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\PortfolioFavorite;
use App\Models\Portfolio;
use App\Models\PortfolioLike;
use App\Models\PortfolioComment;
use App\Models\MissionFavorite;
use App\Models\Notification;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
            PortfolioFavorite::where('user_id', $request->user()->id)
                ->with(['portfolio.category', 'portfolio.freelancer'])
                ->get()
                ->pluck('portfolio')
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
        $query = \App\Models\Portfolio::with(['freelancer', 'category', 'likes', 'comments.user']);

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
        $portfolio = Portfolio::findOrFail($id);
        $existing = PortfolioLike::where(['portfolio_id' => $id, 'user_id' => $request->user()->id])->first();
        if ($existing) {
            $existing->delete();
            return response()->json(['liked' => false]);
        }
        PortfolioLike::create(['portfolio_id' => $id, 'user_id' => $request->user()->id]);

        // Notify Freelancer
        Notification::create([
            'user_id' => $portfolio->freelancer_id,
            'type'    => 'like',
            'title'   => '❤️ Nouveau like sur votre brief',
            'message' => $request->user()->name . ' a aimé votre brief "' . $portfolio->title . '"',
            'portfolio_id' => $id // Use mission_id if we want to reuse the column or add one
        ]);

        return response()->json(['liked' => true]);
    }

    public function addComment(Request $request, $id)
    {
        $request->validate(['body' => 'required|string|max:500']);
        $portfolio = Portfolio::findOrFail($id);
        $comment = PortfolioComment::create([
            'portfolio_id' => $id,
            'user_id'      => $request->user()->id,
            'body'         => $request->body,
        ]);

        // Notify Freelancer
        Notification::create([
            'user_id' => $portfolio->freelancer_id,
            'type'    => 'comment',
            'title'   => '💬 Nouveau commentaire sur votre brief',
            'message' => $request->user()->name . ' : "' . \Str::limit($request->body, 60) . '" sur "' . $portfolio->title . '"',
        ]);

        return response()->json($comment->load('user'), 201);
    }

    public function getComments($id)
    {
        return response()->json(
            \App\Models\PortfolioComment::where('portfolio_id', $id)->with('user')->latest()->get()
        );
    }

    public function toggleFavorite(Request $request, $id)
    {
        $existing = \App\Models\PortfolioFavorite::where(['portfolio_id' => $id, 'user_id' => $request->user()->id])->first();
        if ($existing) { $existing->delete(); return response()->json(['favorited' => false]); }
        \App\Models\PortfolioFavorite::create(['portfolio_id' => $id, 'user_id' => $request->user()->id]);
        return response()->json(['favorited' => true]);
    }

    public function getPayments(Request $request)
    {
        return response()->json(
            \App\Models\Contract::where('client_id', $request->user()->id)
                ->with(['mission', 'freelancer'])
                ->latest()
                ->get()
        );
    }

    public function getStats(Request $request)
    {
        $user = $request->user();

        $totalMissions = \App\Models\Mission::where('client_id', $user->id)->count();
        $totalContracts = \App\Models\Contract::where('client_id', $user->id)->count();
        $totalSpent = \App\Models\Contract::where('client_id', $user->id)->where('status', 'completed')->sum('amount');
        $openMissions = \App\Models\Mission::where('client_id', $user->id)->where('status', 'open')->count();

        return response()->json([
            'total_missions'  => $totalMissions,
            'total_contracts' => $totalContracts,
            'total_spent'     => round($totalSpent, 2),
            'open_missions'   => $openMissions,
            'balance'         => $user->balance,
        ]);
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
            if (!\Illuminate\Support\Facades\Schema::hasColumn('portfolios', 'price')) {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE portfolios ADD COLUMN price DECIMAL(10,2) NULL');
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('portfolios', 'duration')) {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE portfolios ADD COLUMN duration VARCHAR(100) NULL');
            }
        } catch (\Exception $e) {}
    }
}
