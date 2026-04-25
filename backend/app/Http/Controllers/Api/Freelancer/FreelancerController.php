<?php
namespace App\Http\Controllers\Api\Freelancer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Portfolio;
use App\Models\PortfolioLike;
use App\Models\PortfolioFavorite;
use App\Models\PortfolioComment;
use App\Models\Contract;
use App\Models\MissionFavorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class FreelancerController extends Controller
{
    public function getAvailableMissions(Request $request)
    {
        $query = \App\Models\Mission::where('status', 'open')
            ->with(['client', 'category', 'categories', 'likes', 'comments.user']);

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

        return response()->json($query->latest()->get()->map(fn($m) => [
            'id'          => $m->id,
            'title'       => $m->title,
            'description' => $m->description,
            'budget'      => $m->budget,
            'deadline'    => $m->deadline,
            'category'    => $m->category?->name,
            'categories'  => $m->categories->pluck('name'),
            'clientName'  => $m->client?->name,
            'clientId'    => $m->client_id,
            'likes'       => $m->likes->count(),
            'comments'    => $m->comments->count(),
            'commentList' => $m->comments->map(fn($c) => [
                'id'       => $c->id,
                'author'   => $c->user?->name,
                'authorId' => $c->user_id,
                'text'     => $c->body,
                'role'     => $c->user?->role?->name,
            ]),
            'createdAt'   => $m->created_at,
        ]));
    }

    public function getPublishedMissions(Request $request)
    {
        return $this->getAvailableMissions($request);
    }

    public function toggleMissionLike(Request $request, $id)
    {
        $existing = \App\Models\MissionLike::where(['mission_id' => $id, 'user_id' => $request->user()->id])->first();
        if ($existing) {
            $existing->delete();
            return response()->json(['liked' => false]);
        }
        \App\Models\MissionLike::create(['mission_id' => $id, 'user_id' => $request->user()->id]);

        // Notifier le client propriétaire de la mission
        $mission = \App\Models\Mission::find($id);
        if ($mission && $mission->client_id !== $request->user()->id) {
            \App\Models\Notification::create([
                'user_id'    => $mission->client_id,
                'type'       => 'like',
                'title'      => '❤️ Nouveau like sur votre mission',
                'message'    => $request->user()->name . ' a aimé votre mission "' . $mission->title . '"',
                'mission_id' => $mission->id,
                'read'       => false,
            ]);
        }
        return response()->json(['liked' => true]);
    }

    public function toggleMissionFavorite(Request $request, $id)
    {
        $this->ensureTablesExist();
        $existing = MissionFavorite::where(['mission_id' => $id, 'user_id' => $request->user()->id])->first();
        if ($existing) {
            $existing->delete();
            return response()->json(['favorited' => false]);
        }
        MissionFavorite::create(['mission_id' => $id, 'user_id' => $request->user()->id]);
        return response()->json(['favorited' => true]);
    }

    public function addMissionComment(Request $request, $id)
    {
        $request->validate(['body' => 'required|string|max:500']);
        $comment = \App\Models\MissionComment::create([
            'mission_id' => $id,
            'user_id'    => $request->user()->id,
            'body'       => $request->body,
        ]);

        // Notifier le client propriétaire de la mission
        $mission = \App\Models\Mission::find($id);
        if ($mission && $mission->client_id !== $request->user()->id) {
            \App\Models\Notification::create([
                'user_id'    => $mission->client_id,
                'type'       => 'comment',
                'title'      => '💬 Nouveau commentaire sur votre mission',
                'message'    => $request->user()->name . ' : "' . \Str::limit($request->body, 60) . '"',
                'mission_id' => $mission->id,
                'read'       => false,
            ]);
        }
        return response()->json($comment->load('user'), 201);
    }

    public function getMissionComments($id)
    {
        return response()->json(
            \App\Models\MissionComment::where('mission_id', $id)->with('user.role')->latest()->get()
        );
    }

    // Briefs (portfolios)
    public function getBriefs()
    {
        return response()->json(
            Portfolio::with(['freelancer', 'categories', 'comments.user'])
                ->withCount(['likes', 'comments'])
                ->latest()->get()
        );
    }

    public function getMyBriefs(Request $request)
    {
        return response()->json(
            $request->user()->portfolios()
                ->with(['categories', 'likes.user'])
                ->withCount(['likes', 'comments'])
                ->latest()->get()
        );
    }

    public function storeBrief(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
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
            'price'         => $request->price,
            'duration'      => $request->duration,
            'images'        => $paths,
        ]);

        if ($request->category_ids) {
            $brief->categories()->sync($request->category_ids);
        }

        return response()->json($brief->load('categories', 'freelancer'), 201);
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

    public function getMyMissionFavorites(Request $request)
    {
        return response()->json(
            MissionFavorite::where('user_id', $request->user()->id)
                ->with(['mission.category', 'mission.client'])
                ->get()
                ->pluck('mission')
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
        $this->ensureTablesExist();
        $userId = $request->user()->id;
        $myBriefIds = Portfolio::where('freelancer_id', $userId)->pluck('id');

        $likes = PortfolioLike::whereIn('portfolio_id', $myBriefIds)
            ->where('user_id', '!=', $userId)
            ->with(['user', 'portfolio'])
            ->latest()->take(10)->get()
            ->map(fn($l) => [
                'id'           => 'like_' . $l->id,
                'portfolio_id' => $l->portfolio_id,
                'type'         => 'like',
                'title'        => '❤️ Nouveau like',
                'message'      => $l->user->name . ' a aimé votre brief "' . $l->portfolio->title . '"',
                'created_at'   => $l->created_at,
                'read'         => false
            ]);

        $comments = PortfolioComment::whereIn('portfolio_id', $myBriefIds)
            ->where('user_id', '!=', $userId)
            ->with(['user', 'portfolio'])
            ->latest()->take(10)->get()
            ->map(fn($c) => [
                'id'           => 'comment_' . $c->id,
                'portfolio_id' => $c->portfolio_id,
                'type'         => 'comment',
                'title'        => '💬 Nouveau commentaire',
                'message'      => $c->user->name . ' : "' . \Str::limit($c->body, 50) . '" sur "' . $c->portfolio->title . '"',
                'created_at'   => $c->created_at,
                'read'         => false
            ]);

        return response()->json(
            $likes->concat($comments)->sortByDesc('created_at')->values()
        );
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
            if (!\Illuminate\Support\Facades\Schema::hasTable('mission_favorites')) {
                \Illuminate\Support\Facades\DB::statement("CREATE TABLE mission_favorites (id SERIAL PRIMARY KEY, mission_id BIGINT NOT NULL REFERENCES missions(id) ON DELETE CASCADE, user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE(mission_id, user_id))");
            }
        } catch (\Exception $e) {}
    }
}
