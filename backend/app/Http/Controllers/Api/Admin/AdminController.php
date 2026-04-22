<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Contract;
use App\Models\Mission;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function getStats()
    {
        return response()->json([
            'total_missions' => Mission::count(),
            'total_clients' => User::where('role_id', 2)->count(),
            'total_freelancers' => User::where('role_id', 3)->count(),
            'total_contracts' => Contract::count(),
            'sum_revenue_platform' => Contract::where('status', 'completed')->sum('commission'),
        ]);
    }

    public function getPendingUsers()
    {
        return response()->json(
            User::where('verification_status', 'pending')->with('role')->get()
        );
    }

    public function getUsers(Request $request)
    {
        $query = User::with('role');

        if ($request->role) {
            $query->whereHas('role', fn($q) => $q->where('name', $request->role));
        }
        if ($request->status) {
            $query->where('verification_status', $request->status);
        }

        return response()->json($query->get());
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
        $user = User::findOrFail($id);
        $user->update(['verification_status' => 'banned']);
        return response()->json(['message' => 'Utilisateur banni.']);
    }

    public function createCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:categories',
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        $category = Category::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id
        ]);

        return response()->json(['message' => 'Catégorie créée.', 'category' => $category], 201);
    }
}
