<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Contract;
use App\Models\Mission;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Contrôleur Admin : Outils de gestion globale de la plateforme, modération et statistiques.
 */
class AdminController extends Controller
{
    /**
     * Statistiques globales (KPIs)
     * Permet à l'admin de voir l'état de santé de la plateforme.
     */
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

    /**
     * Liste des utilisateurs en attente de vérification
     * Récupère ceux qui ont envoyé leurs documents mais ne sont pas encore validés.
     */
    public function getPendingUsers()
    {
        return response()->json(
            User::where('verification_status', 'pending')
                ->with('role')
                ->get()
        );
    }

    /**
     * Valider l'identité d'un utilisateur
     */
    public function approveUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['verification_status' => 'verified']);
        
        return response()->json([
            'message' => 'Identité de l’utilisateur validée avec succès.',
            'user' => $user
        ]);
    }

    /**
     * Bannir un utilisateur (Signalement ou non-respect des règles)
     */
    public function banUser($id)
    {
        $user = User::findOrFail($id);
        
        // On marque l'utilisateur comme banni
        $user->update(['verification_status' => 'banned']); 
        
        return response()->json([
            'message' => 'L’utilisateur a été banni de la plateforme.'
        ]);
    }

    /**
     * Gestion des catégories & sous-catégories
     */
    public function createCategory(Request $request)
    {
        // Validation simple
        $request->validate([
            'name' => 'required|string|unique:categories',
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        $category = Category::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id
        ]);

        return response()->json([
            'message' => 'Catégorie créée avec succès.',
            'category' => $category
        ], 201);
    }
}
