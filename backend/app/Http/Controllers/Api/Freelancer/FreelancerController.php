<?php
namespace App\Http\Controllers\Api\Freelancer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Portfolio;
use App\Models\PortfolioLike;
use App\Models\PortfolioFavorite;
use App\Models\PortfolioComment;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class FreelancerController extends Controller
{
    public function getAvailableMissions(Request $request)
    {
        $query = \App\Models\Mission::where('status', 'open')->with(['client', 'category']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'ilike', '%' . $request->search . '%')
                  ->orWhere('description', 'ilike', '%' . $request->search . '%');
            });
        }

        if ($request->category_id) {
            $catId = $request->category_id;
            $subIds = \App\Models\Category::where('parent_id', $catId)->pluck('id');
            $ids = $subIds->push($catId);
            $query->whereIn('category_id', $ids);
        }

        if ($request->budget_max) {
            $query->where('budget', '<=', $request->budget_max);
        }

        return response()->json($query->latest()->get());
    }

    // Briefs (portfolios)
    public function getBriefs()
    {
        return response()->json(
            Portfolio::with(['freelancer', 'category', 'comments.user'])
                ->latest()->get()
        );
    }

    public function getMyBriefs(Request $request)
    {
        return response()->json(
            $request->user()->portfolios()->with('category')->latest()->get()
        );
    }

    public function storeBrief(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'images'      => 'nullable|array',
            'images.*'    => 'file|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $paths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $paths[] = $img->store('briefs', 'public');
            }
        }

        $brief = Portfolio::create([
            'freelancer_id' => $request->user()->id,
            'category_id'   => $request->category_id,
            'title'         => $request->title,
            'description'   => $request->description,
            'images'        => $paths,
        ]);

        return response()->json($brief->load('category', 'freelancer'), 201);
    }

    // Likes
    public function toggleLike(Request $request, $id)
    {
        $existing = PortfolioLike::where(['portfolio_id' => $id, 'user_id' => $request->user()->id])->first();
        if ($existing) {
            $existing->delete();
            return response()->json(['liked' => false]);
        }
        PortfolioLike::create(['portfolio_id' => $id, 'user_id' => $request->user()->id]);
        return response()->json(['liked' => true]);
    }

    // Comments
    public function addComment(Request $request, $id)
    {
        $request->validate(['body' => 'required|string|max:500']);
        $comment = PortfolioComment::create([
            'portfolio_id' => $id,
            'user_id'      => $request->user()->id,
            'body'         => $request->body,
        ]);
        return response()->json($comment->load('user'), 201);
    }

    public function getComments($id)
    {
        return response()->json(
            PortfolioComment::where('portfolio_id', $id)->with('user')->latest()->get()
        );
    }

    // Favorites
    public function toggleFavorite(Request $request, $id)
    {
        $existing = PortfolioFavorite::where(['portfolio_id' => $id, 'user_id' => $request->user()->id])->first();
        if ($existing) {
            $existing->delete();
            return response()->json(['favorited' => false]);
        }
        PortfolioFavorite::create(['portfolio_id' => $id, 'user_id' => $request->user()->id]);
        return response()->json(['favorited' => true]);
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

    // Profil
    public function getProfile(Request $request)
    {
        return response()->json($request->user()->load('role'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name'            => 'sometimes|string|max:255',
            'payment_method'  => 'sometimes|in:paypal,payoneer',
            'payment_account' => 'sometimes|string|max:255',
        ]);
        $request->user()->update($request->only('name', 'payment_method', 'payment_account'));
        return response()->json($request->user()->fresh());
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:8|confirmed',
        ]);
        if (!Hash::check($request->current_password, $request->user()->password)) {
            return response()->json(['message' => 'Mot de passe actuel incorrect.'], 422);
        }
        $request->user()->update(['password' => Hash::make($request->new_password)]);
        return response()->json(['message' => 'Mot de passe mis à jour.']);
    }

    // Paiements
    public function getPayments(Request $request)
    {
        return response()->json(
            Contract::where('freelancer_id', $request->user()->id)
                ->with('mission')
                ->latest()
                ->get()
        );
    }

    // Stats dashboard
    public function getStats(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'balance'        => $user->balance,
            'briefs_count'   => Portfolio::where('freelancer_id', $user->id)->count(),
            'contracts_count'=> Contract::where('freelancer_id', $user->id)->count(),
            'favorites_count'=> PortfolioFavorite::where('user_id', $user->id)->count(),
        ]);
    }

    // Get balance
    public function getBalance(Request $request)
    {
        return response()->json([
            'balance' => $request->user()->balance ?? 0
        ]);
    }

    // Get active missions for freelancer
    public function getActiveMissions(Request $request)
    {
        $userId = $request->user()->id;
        $missions = Contract::where('freelancer_id', $userId)
            ->where('status', '!=', 'completed')
            ->with(['mission.client', 'mission.category'])
            ->latest()
            ->get()
            ->map(function($contract) {
                return [
                    'id' => $contract->mission->id,
                    'title' => $contract->mission->title,
                    'client' => $contract->mission->client->name,
                    'budget' => $contract->mission->budget,
                    'progress' => $contract->progress ?? 0,
                    'urgent' => $contract->mission->deadline && \Carbon\Carbon::parse($contract->mission->deadline)->diffInDays(now()) <= 3,
                    'image' => 'https://images.unsplash.com/photo-1590483736622-39da8af7541c?auto=format&fit=crop&w=400&q=80',
                ];
            });

        return response()->json([
            'data' => $missions
        ]);
    }

    // Get suggested freelancers
    public function getSuggestedFreelancers(Request $request)
    {
        $freelancers = \App\Models\User::where('role_id', '!=', $request->user()->role_id)
            ->where('id', '!=', $request->user()->id)
            ->limit(10)
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'skill' => 'Spécialiste',
                    'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=150&q=80',
                ];
            });

        return response()->json([
            'data' => $freelancers
        ]);
    }

    // Categories
    public function getCategories()
    {
        return response()->json(Category::all());
    }

    // Notifications (likes + comments sur mes briefs)
    public function getNotifications(Request $request)
    {
        $userId = $request->user()->id;
        $myBriefIds = Portfolio::where('freelancer_id', $userId)->pluck('id');

        $likes = PortfolioLike::whereIn('portfolio_id', $myBriefIds)
            ->where('user_id', '!=', $userId)
            ->with(['user', 'portfolio'])
            ->latest()->take(10)->get()
            ->map(fn($l) => ['type' => 'like', 'user' => $l->user->name, 'brief' => $l->portfolio->title, 'at' => $l->created_at]);

        $comments = PortfolioComment::whereIn('portfolio_id', $myBriefIds)
            ->where('user_id', '!=', $userId)
            ->with(['user', 'portfolio'])
            ->latest()->take(10)->get()
            ->map(fn($c) => ['type' => 'comment', 'user' => $c->user->name, 'brief' => $c->portfolio->title, 'body' => $c->body, 'at' => $c->created_at]);

        return response()->json(
            $likes->concat($comments)->sortByDesc('at')->values()
        );
    }
}
