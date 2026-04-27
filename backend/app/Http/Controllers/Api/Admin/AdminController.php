<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Contract;
use App\Models\Mission;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function getStats()
    {
        $now = Carbon::now();
        $thisMonth = $now->month;
        $lastMonth = $now->copy()->subMonth()->month;
        $thisYear  = $now->year;

        // Users
        $totalUsers       = User::whereIn('role_id', [2,3])->count();
        $usersThisMonth   = User::whereIn('role_id', [2,3])->whereMonth('created_at', $thisMonth)->whereYear('created_at', $thisYear)->count();
        $usersLastMonth   = User::whereIn('role_id', [2,3])->whereMonth('created_at', $lastMonth)->count();
        $usersTrend       = $usersLastMonth > 0 ? round((($usersThisMonth - $usersLastMonth) / $usersLastMonth) * 100) : ($usersThisMonth > 0 ? 100 : 0);

        // Missions
        $totalMissions     = Mission::count();
        $missionsThisMonth = Mission::whereMonth('created_at', $thisMonth)->whereYear('created_at', $thisYear)->count();
        $missionsLastMonth = Mission::whereMonth('created_at', $lastMonth)->count();
        $missionsTrend     = $missionsLastMonth > 0 ? round((($missionsThisMonth - $missionsLastMonth) / $missionsLastMonth) * 100) : ($missionsThisMonth > 0 ? 100 : 0);

        // Contracts
        $totalContracts     = Contract::count();
        $contractsThisMonth = Contract::whereMonth('created_at', $thisMonth)->whereYear('created_at', $thisYear)->count();
        $contractsLastMonth = Contract::whereMonth('created_at', $lastMonth)->count();
        $contractsTrend     = $contractsLastMonth > 0 ? round((($contractsThisMonth - $contractsLastMonth) / $contractsLastMonth) * 100) : ($contractsThisMonth > 0 ? 100 : 0);

        // Revenue
        $totalRevenue     = Contract::where('status', 'completed')->sum('commission');
        $revenueThisMonth = Contract::where('status', 'completed')->whereMonth('updated_at', $thisMonth)->whereYear('updated_at', $thisYear)->sum('commission');
        $revenueLastMonth = Contract::where('status', 'completed')->whereMonth('updated_at', $lastMonth)->sum('commission');
        $revenueTrend     = $revenueLastMonth > 0 ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100) : ($revenueThisMonth > 0 ? 100 : 0);

        // Pending
        $pendingUsers = User::where('verification_status', 'pending')->count();

        // Clients / Freelancers
        $totalClients     = User::where('role_id', 2)->count();
        $totalFreelancers = User::where('role_id', 3)->count();

        // Inscriptions par semaine (4 dernières semaines)
        $weeklyRegistrations = [];
        for ($i = 3; $i >= 0; $i--) {
            $start = $now->copy()->subWeeks($i)->startOfWeek();
            $end   = $now->copy()->subWeeks($i)->endOfWeek();
            $weeklyRegistrations[] = [
                'label' => 'S' . $start->weekOfYear,
                'count' => User::whereIn('role_id', [2,3])->whereBetween('created_at', [$start, $end])->count(),
            ];
        }

        // ── Contrats stats ──────────────────────────────────────────────────
        $contractsByStatus = Contract::selectRaw('status, count(*) as total, sum(amount) as volume')
            ->groupBy('status')->get()
            ->keyBy('status');

        $contractsActive    = $contractsByStatus->get('active')?->total ?? 0;
        $contractsPending   = $contractsByStatus->get('pending_freelancer')?->total ?? 0;
        $contractsCompleted = $contractsByStatus->get('completed')?->total ?? 0;
        $contractsRefunded  = $contractsByStatus->get('refunded')?->total ?? 0;
        $contractsCancelled = $contractsByStatus->get('cancelled')?->total ?? 0;

        $volumeTotal     = Contract::sum('amount');
        $volumeCompleted = $contractsByStatus->get('completed')?->volume ?? 0;
        $volumeEscrow    = Contract::whereIn('status', ['pending_freelancer', 'active'])->sum('amount');

        // Contrats par semaine (4 dernières semaines)
        $weeklyContracts = [];
        for ($i = 3; $i >= 0; $i--) {
            $start = $now->copy()->subWeeks($i)->startOfWeek();
            $end   = $now->copy()->subWeeks($i)->endOfWeek();
            $weeklyContracts[] = [
                'label' => 'S' . $start->weekOfYear,
                'count' => Contract::whereBetween('created_at', [$start, $end])->count(),
            ];
        }

        // Top freelancers par contrats complétés
        $topFreelancers = User::whereHas('role', fn($q) => $q->where('name', 'freelancer'))
            ->withCount(['contracts as completed_contracts' => fn($q) => $q->where('status', 'completed')])
            ->orderByDesc('completed_contracts')
            ->limit(5)
            ->get(['id', 'name'])
            ->map(fn($u) => ['name' => $u->name, 'count' => $u->completed_contracts]);

        // ── Paiements stats ─────────────────────────────────────────────────
        $totalDeposits      = \App\Models\Transaction::where('type', 'deposit')->where('status', 'completed')->sum('amount');
        $depositsThisMonth  = \App\Models\Transaction::where('type', 'deposit')->where('status', 'completed')->whereMonth('created_at', $thisMonth)->whereYear('created_at', $thisYear)->sum('amount');
        $depositsLastMonth  = \App\Models\Transaction::where('type', 'deposit')->where('status', 'completed')->whereMonth('created_at', $lastMonth)->sum('amount');
        $depositsTrend      = $depositsLastMonth > 0 ? round((($depositsThisMonth - $depositsLastMonth) / $depositsLastMonth) * 100) : ($depositsThisMonth > 0 ? 100 : 0);

        $weeklyDeposits = [];
        for ($i = 3; $i >= 0; $i--) {
            $start = $now->copy()->subWeeks($i)->startOfWeek();
            $end   = $now->copy()->subWeeks($i)->endOfWeek();
            $weeklyDeposits[] = [
                'label' => 'S' . $start->weekOfYear,
                'amount' => round(\App\Models\Transaction::where('type', 'deposit')->where('status', 'completed')->whereBetween('created_at', [$start, $end])->sum('amount'), 2),
            ];
        }

        $recentTransactions = \App\Models\Transaction::with('user')
            ->where('status', 'completed')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn($t) => [
                'user'   => $t->user?->name,
                'type'   => $t->type,
                'amount' => $t->amount,
                'date'   => $t->created_at,
            ]);

        return response()->json([
            'total_users'          => $totalUsers,
            'total_clients'        => $totalClients,
            'total_freelancers'    => $totalFreelancers,
            'total_missions'       => $totalMissions,
            'total_contracts'      => $totalContracts,
            'total_revenue'        => round($totalRevenue, 2),
            'pending_users'        => $pendingUsers,
            'users_trend'          => $usersTrend,
            'missions_trend'       => $missionsTrend,
            'contracts_trend'      => $contractsTrend,
            'revenue_trend'        => $revenueTrend,
            'weekly_registrations' => $weeklyRegistrations,
            // contrats
            'contracts_active'     => $contractsActive,
            'contracts_pending'    => $contractsPending,
            'contracts_completed'  => $contractsCompleted,
            'contracts_refunded'   => $contractsRefunded,
            'contracts_cancelled'  => $contractsCancelled,
            'volume_total'         => round($volumeTotal, 2),
            'volume_completed'     => round($volumeCompleted, 2),
            'volume_escrow'        => round($volumeEscrow, 2),
            'weekly_contracts'     => $weeklyContracts,
            'top_freelancers'      => $topFreelancers,
            // paiements
            'total_deposits'       => round($totalDeposits, 2),
            'deposits_this_month'  => round($depositsThisMonth, 2),
            'deposits_trend'       => $depositsTrend,
            'weekly_deposits'      => $weeklyDeposits,
            'recent_transactions'  => $recentTransactions,
        ]);
    }

    public function getPendingUsers()
    {
        return response()->json(User::where('verification_status', 'pending')->with('role')->latest()->get());
    }

    public function getUsers(Request $request)
    {
        $query = User::with('role')->select(
            'id', 'name', 'email', 'verification_status', 'created_at',
            'id_number', 'birthplace', 'id_document_path', 'role_id'
        );
        if ($request->role)   $query->whereHas('role', fn($q) => $q->where('name', $request->role));
        if ($request->status) $query->where('verification_status', $request->status);
        return response()->json($query->latest()->get()->map(fn($u) => array_merge($u->toArray(), [
            'role_name' => $u->role?->name,
        ])));
    }

    public function approveUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['verification_status' => 'verified']);
        return response()->json(['message' => 'Utilisateur validé.', 'user' => $user]);
    }

    public function rejectUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['verification_status' => 'rejected']);
        return response()->json(['message' => 'Utilisateur rejeté.', 'user' => $user]);
    }

    public function banUser($id)
    {
        if ((int)$id === auth()->id()) {
            return response()->json(['message' => 'Vous ne pouvez pas vous bannir vous-même.'], 403);
        }
        $user = User::findOrFail($id);
        $user->update(['verification_status' => 'banned']);
        return response()->json(['message' => 'Utilisateur banni.']);
    }

    public function getCategories()
    {
        return response()->json(Category::whereNull('parent_id')->with('children')->orderBy('name')->get());
    }

    public function createCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100|unique:categories', 'parent_id' => 'nullable|exists:categories,id']);
        $category = Category::create(['name' => $request->name, 'parent_id' => $request->parent_id]);
        return response()->json($category->load('children'), 201);
    }

    public function updateCategory(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $request->validate(['name' => 'required|string|max:100|unique:categories,name,'.$id, 'parent_id' => 'nullable|exists:categories,id']);
        $category->update(['name' => $request->name, 'parent_id' => $request->parent_id]);
        return response()->json($category->fresh('children'));
    }

    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);
        // Rattacher les sous-catégories au parent de la catégorie supprimée
        Category::where('parent_id', $id)->update(['parent_id' => $category->parent_id]);
        $category->delete();
        return response()->json(['message' => 'Catégorie supprimée.']);
    }
}
