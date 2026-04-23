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
            'total_missions'      => Mission::count(),
            'total_clients'       => User::where('role_id', 2)->count(),
            'total_freelancers'   => User::where('role_id', 3)->count(),
            'total_contracts'     => Contract::count(),
            'sum_revenue_platform'=> Contract::where('status', 'completed')->sum('commission'),
        ]);
    }

    public function getPendingUsers()
    {
        return response()->json(User::where('verification_status', 'pending')->with('role')->latest()->get());
    }

    public function getUsers(Request $request)
    {
        $query = User::with('role');
        if ($request->role)   $query->whereHas('role', fn($q) => $q->where('name', $request->role));
        if ($request->status) $query->where('verification_status', $request->status);
        return response()->json($query->latest()->get());
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

    // Categories
    public function getCategories()
    {
        return response()->json(
            Category::whereNull('parent_id')->with('children')->orderBy('name')->get()
        );
    }

    public function createCategory(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:100|unique:categories',
            'parent_id' => 'nullable|exists:categories,id',
        ]);
        $category = Category::create(['name' => $request->name, 'parent_id' => $request->parent_id]);
        return response()->json($category->load('children'), 201);
    }

    public function updateCategory(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $request->validate([
            'name'      => 'required|string|max:100|unique:categories,name,' . $id,
            'parent_id' => 'nullable|exists:categories,id',
        ]);
        $category->update(['name' => $request->name, 'parent_id' => $request->parent_id]);
        return response()->json($category->fresh('children'));
    }

    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);
        // Réassigner les enfants au parent du parent (ou null)
        Category::where('parent_id', $id)->update(['parent_id' => $category->parent_id]);
        $category->delete();
        return response()->json(['message' => 'Catégorie supprimée.']);
    }
}
