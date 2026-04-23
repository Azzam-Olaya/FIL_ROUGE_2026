<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

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

        $mission = \App\Models\Mission::create([
            'client_id'   => $request->user()->id,
            'category_id' => $request->category_id,
            'title'       => $request->title,
            'description' => $request->description,
            'budget'      => $request->budget,
            'deadline'    => $request->deadline,
            'status'      => 'open',
        ]);

        return response()->json($mission, 201);
    }

    public function getMyMissions(Request $request)
    {
        return response()->json(
            $request->user()->missions()->with('category')->get()
        );
    }

    public function getStats(Request $request)
    {
        $user = $request->user();
        $now  = Carbon::now();

        $totalMissions     = \App\Models\Mission::where('client_id', $user->id)->count();
        $missionsThisMonth = \App\Models\Mission::where('client_id', $user->id)->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $missionsLastMonth = \App\Models\Mission::where('client_id', $user->id)->whereMonth('created_at', $now->copy()->subMonth()->month)->count();
        $missionsTrend     = $missionsLastMonth > 0 ? round((($missionsThisMonth - $missionsLastMonth) / $missionsLastMonth) * 100) : ($missionsThisMonth > 0 ? 100 : 0);

        $totalContracts     = \App\Models\Contract::where('client_id', $user->id)->count();
        $contractsThisMonth = \App\Models\Contract::where('client_id', $user->id)->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $contractsLastMonth = \App\Models\Contract::where('client_id', $user->id)->whereMonth('created_at', $now->copy()->subMonth()->month)->count();
        $contractsTrend     = $contractsLastMonth > 0 ? round((($contractsThisMonth - $contractsLastMonth) / $contractsLastMonth) * 100) : ($contractsThisMonth > 0 ? 100 : 0);

        $totalSpent     = \App\Models\Contract::where('client_id', $user->id)->where('status', 'completed')->sum('amount');
        $spentThisMonth = \App\Models\Contract::where('client_id', $user->id)->where('status', 'completed')->whereMonth('updated_at', $now->month)->whereYear('updated_at', $now->year)->sum('amount');
        $spentLastMonth = \App\Models\Contract::where('client_id', $user->id)->where('status', 'completed')->whereMonth('updated_at', $now->copy()->subMonth()->month)->sum('amount');
        $spentTrend     = $spentLastMonth > 0 ? round((($spentThisMonth - $spentLastMonth) / $spentLastMonth) * 100) : ($spentThisMonth > 0 ? 100 : 0);

        return response()->json([
            'total_missions'  => $totalMissions,
            'missions_trend'  => $missionsTrend,
            'total_contracts' => $totalContracts,
            'contracts_trend' => $contractsTrend,
            'total_spent'     => round($totalSpent, 2),
            'spent_trend'     => $spentTrend,
            'open_missions'   => \App\Models\Mission::where('client_id', $user->id)->where('status', 'open')->count(),
            'balance'         => $user->balance,
        ]);
    }
}
