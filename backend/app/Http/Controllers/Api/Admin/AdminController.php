<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Statistiques globales
     */
    public function getStats()
    {
        return response()->json([
            'total_missions' => \App\Models\Mission::count(),
            'total_clients' => \App\Models\User::where('role_id', 2)->count(),
            'total_freelancers' => \App\Models\User::where('role_id', 3)->count(),
            'total_contracts' => \App\Models\Contract::count(),
        ]);
    }

    /**
     * Liste des utilisateurs en attente de vérification
     */
    public function getPendingUsers()
    {
        return response()->json(
            \App\Models\User::where('verification_status', 'pending')->with('role')->get()
        );
    }

    /**
     * Valider l'identité d'un utilisateur
     */
    public function approveUser($id)
    {
        $user = \App\Models\User::findOrFail($id);
        $user->update(['verification_status' => 'verified']);
        return response()->json(['message' => 'User approved successfully']);
    }

    /**
     * Bannir un utilisateur
     */
    public function banUser($id)
    {
        $user = \App\Models\User::findOrFail($id);
        // On pourrait ajouter une colonne 'is_banned'
        $user->update(['verification_status' => 'banned']); 
        return response()->json(['message' => 'User banned successfully']);
    }

    /**
     * Gestion des catégories
     */
    public function createCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|unique:categories']);
        $category = \App\Models\Category::create($request->only('name', 'parent_id'));
        return response()->json($category, 201);
    }
}
