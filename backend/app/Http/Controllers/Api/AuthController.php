<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Contrôleur Authentification : Gère l'inscription, la connexion et la vérification d'identité.
 */
class AuthController extends Controller
{
    /**
     * Inscription (Register)
     * Permet à un utilisateur de créer un compte en choisissant son rôle.
     */
    public function register(Request $request)
    {
        // Validation des données entrantes
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_name' => 'required|in:client,freelancer', // 2: Client, 3: Freelancer
        ]);

        // Récupération du rôle
        $role = Role::where('name', $request->role_name)->first();
        if (!$role) {
            return response()->json(['error' => 'Rôle invalide'], 400);
        }

        // Création de l'utilisateur en base de données
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Chiffrage du mot de passe
            'role_id' => $role->id,
            'verification_status' => 'pending', // Statut initial en attente de vérification
        ]);

        // Création d'un token (jeton) pour l'authentification immédiate
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    /**
     * Connexion (Login)
     * Vérifie les identifiants et retourne un token si ils sont valides.
     */
    public function login(Request $request)
    {
        // Debug pour vérifier si l'utilisateur existe avant la tentative
        $userExists = User::where('email', $request->email)->exists();
        if (!$userExists) {
            \Illuminate\Support\Facades\Log::warning("Connexion échouée : Utilisateur non trouvé [" . $request->email . "]");
        }

        // Tentative de connexion via Laravel Auth
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Identifiants invalides (Vérifiez votre email ou mot de passe)'], 401);
        }

        // Récupération de l'utilisateur avec sa relation Rôle
        $user = User::where('email', $request['email'])->firstOrFail();
        $user->load('role'); // Charger le rôle pour que role_name soit disponible

        // Vérifier si l'utilisateur est vérifié
        if ($user->verification_status !== 'verified') {
            return response()->json(['message' => 'Votre compte est en attente de validation par un administrateur.'], 403);
        }

        // Création du token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    /**
     * Vérification d'identité (Upload de document)
     * Le client ou freelance doit uploader sa CIN ou son Passeport pour validation.
     */
    public function verifyIdentity(Request $request)
    {
        // Validation que le fichier est bien une image ou un PDF
        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'id_number' => 'required|string|max:50',
            'birthplace' => 'required|string|max:100',
        ]);

        $user = $request->user();
        
        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('id_documents', 'public');
            
            $user->update([
                'id_document_path' => $path,
                'id_number' => $request->id_number,
                'birthplace' => $request->birthplace,
                'verification_status' => 'pending',
            ]);
        }

        return response()->json([
            'message' => 'Document envoyé avec succès. En attente de validation par l’administrateur.',
            'user' => $user
        ]);
    }

    /**
     * Déconnexion (Logout)
     */
    public function logout(Request $request)
    {
        // Suppression du token actuel pour invalider la session
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté avec succès']);
    }
}
