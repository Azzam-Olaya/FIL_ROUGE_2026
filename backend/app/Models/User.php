<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Modèle User : Représente tous les utilisateurs de la plateforme (Admin, Client, Freelancer).
 */
class User extends Authenticatable
{
    // HasApiTokens : Permet de gérer l'authentification par jetons (token) via Sanctum.
    // HasFactory : Permet de générer des données de test facilement.
    // Notifiable : Permet d'envoyer des notifications (email, etc.) à l'utilisateur.
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Les attributs qui peuvent être remplis via un formulaire (Mass Assignment).
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'verification_status', // 'pending', 'verified', 'rejected'
        'id_document_path',    // Chemin vers l'image de la CIN/Passeport
        'balance'              // Solde actuel de l'utilisateur (en MAD)
    ];

    /**
     * Les attributs chargés automatiquement dans le JSON.
     */
    protected $appends = ['role_name'];

    /**
     * Les attributs qui doivent être cachés lors des réponses API (Sécurité).
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Accesseur pour récupérer le nom du rôle facilement.
     */
    public function getRoleNameAttribute()
    {
        return $this->role?->name;
    }

    /**
     * Relation : Un utilisateur appartient à un Rôle.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Relation (Client) : Un utilisateur peut avoir publié plusieurs Missions.
     */
    public function missions()
    {
        return $this->hasMany(Mission::class, 'client_id');
    }

    /**
     * Relation (Freelancer) : Un utilisateur peut avoir plusieurs éléments dans son Portfolio.
     */
    public function portfolios()
    {
        return $this->hasMany(Portfolio::class, 'freelancer_id');
    }

    /**
     * Conversion automatique des types de données.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // Hashage automatique du mot de passe
            'balance' => 'decimal:2',
        ];
    }
}
