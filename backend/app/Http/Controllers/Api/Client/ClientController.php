<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use App\Models\Portfolio;
use App\Models\PortfolioLike;
use App\Models\PortfolioComment;
use App\Models\PortfolioFavorite;
use App\Models\Contract;
use App\Models\Category;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function storeMission(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'budget'      => 'required|numeric|min:0',
            'deadline'    => 'required|date|after:today',
        ]);

        $mission = Mission::create([
            'client_id'   => $request->user()->id,
            'category_id' => $request->category_id,
            'title'       => $request->title,
            'description' => $request->description,
            'budget'      => $request->budget,
            'deadline'    => $request->deadline,
            'status'      => 'open',
        ]);

        return response()->json($mission->load('category'), 201);
    }

    public function getMyMissions(Request $request)
    {
        return response()->json(
            $request->user()->missions()->with('category')->latest()->get()
        );
    }

    public function getStats(Request $request)
    {
        $userId = $request->user()->id;
        return response()->json([
            'total_missions'  => Mission::where('client_id', $userId)->count(),
            'active_missions' => Mission::where('client_id', $userId)->where('status', 'open')->count(),
            'total_contracts' => Contract::whereHas('mission', fn($q) => $q->where('client_id', $userId))->count(),
            'total_spent'     => Contract::whereHas('mission', fn($q) => $q->where('client_id', $userId))
                                    ->where('status', 'completed')->sum('amount'),
        ]);
    }

    public function getPayments(Request $request)
    {
        $userId = $request->user()->id;
        return response()->json(
            Contract::whereHas('mission', fn($q) => $q->where('client_id', $userId))
                ->with(['mission.category', 'freelancer'])
                ->latest()->get()
        );
    }

    // ── Briefs (portfolios publiés par les freelancers) ────────────────────────

    public function getBriefs(Request $request)
    {
        $userId = $request->user()->id;
        $query  = Portfolio::with(['freelancer:id,name', 'category']);

        if ($request->search) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('title', 'like', "%{$s}%")
                ->orWhere('description', 'like', "%{$s}%")
            );
        }

        if ($request->category_id) {
            $catId  = $request->category_id;
            $subIds = Category::where('parent_id', $catId)->pluck('id')->push($catId);
            $query->whereIn('category_id', $subIds);
        }

        $briefs = $query->withCount([
            'likes as likes_count',
            'comments as comments_count',
            'favorites as favorites_count',
        ])->latest()->get()->map(function ($p) use ($userId) {
            $p->is_liked     = $p->likes()->where('user_id', $userId)->exists();
            $p->is_favorited = $p->favorites()->where('user_id', $userId)->exists();
            $p->price        = $p->price ?? null;
            $p->timeline     = $p->timeline ?? null;
            return $p;
        });

        return response()->json($briefs);
    }

    public function toggleLike(Request $request, $id)
    {
        $userId = $request->user()->id;
        Portfolio::findOrFail($id);
        $existing = PortfolioLike::where(['portfolio_id' => $id, 'user_id' => $userId])->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            PortfolioLike::create(['portfolio_id' => $id, 'user_id' => $userId]);
            $liked = true;
        }

        return response()->json(['liked' => $liked, 'count' => PortfolioLike::where('portfolio_id', $id)->count()]);
    }

    public function addComment(Request $request, $id)
    {
        $request->validate(['text' => 'required|string|max:500']);
        Portfolio::findOrFail($id);
        $comment = PortfolioComment::create([
            'portfolio_id' => $id,
            'user_id'      => $request->user()->id,
            'body'         => $request->text,
        ]);
        return response()->json($comment->load('user:id,name'), 201);
    }

    public function getComments($id)
    {
        Portfolio::findOrFail($id);
        return response()->json(
            PortfolioComment::where('portfolio_id', $id)->with('user:id,name')->latest()->get()
        );
    }

    public function toggleFavorite(Request $request, $id)
    {
        $userId = $request->user()->id;
        Portfolio::findOrFail($id);
        $existing = PortfolioFavorite::where(['portfolio_id' => $id, 'user_id' => $userId])->first();

        if ($existing) {
            $existing->delete();
            $favorited = false;
        } else {
            PortfolioFavorite::create(['portfolio_id' => $id, 'user_id' => $userId]);
            $favorited = true;
        }

        return response()->json(['favorited' => $favorited, 'count' => PortfolioFavorite::where('portfolio_id', $id)->count()]);
    }

    // ── Notifications ──────────────────────────────────────────────────────────

    public function getNotifications(Request $request)
    {
        $userId     = $request->user()->id;
        $missionIds = Mission::where('client_id', $userId)->pluck('id');

        $likes = PortfolioLike::whereIn('portfolio_id', $missionIds)
            ->where('user_id', '!=', $userId)
            ->with('user:id,name')
            ->latest()->take(15)->get()
            ->map(fn($l) => [
                'id'         => 'like_' . $l->id,
                'type'       => 'like',
                'title'      => '❤️ Nouveau like',
                'message'    => "{$l->user->name} a aimé votre mission",
                'read'       => false,
                'created_at' => $l->created_at,
            ]);

        return response()->json($likes->values());
    }

    public function markAllRead(Request $request)
    {
        return response()->json(['message' => 'Notifications marquées comme lues.']);
    }

    public function markOneRead(Request $request, $id)
    {
        return response()->json(['message' => 'Notification marquée comme lue.']);
    }
}
