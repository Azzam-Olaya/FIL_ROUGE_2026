<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Mission : Représente une offre de projet publiée par un client.
 */
class Mission extends Model
{
    /**
     * Attributs remplissables via un formulaire.
     */
    protected $fillable = [
        'client_id',    // ID de l'utilisateur qui a créé la mission
        'category_id',  // ID de la catégorie (ex: Design, Dév)
        'title',        // Titre de la mission
        'description',  // Détails du besoin
        'budget',       // Montant proposé (en MAD)
        'deadline',     // Date limite de réalisation
        'status'        // État : 'open', 'in_progress', 'completed', 'cancelled'
    ];

    /**
     * Relation : Une mission appartient à un Client (User).
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Relation : Une mission appartient à une Catégorie.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relation : Une mission peut être liée à un Contrat une fois acceptée.
     */
    public function contract()
    {
        return $this->hasOne(Contract::class);
    }

    public function likes()
    {
        return $this->hasMany(MissionLike::class);
    }

    public function comments()
    {
        return $this->hasMany(MissionComment::class);
    }
}
