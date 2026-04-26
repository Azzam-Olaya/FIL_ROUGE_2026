<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = [
        'mission_id', 'client_id', 'freelancer_id', 
        'amount', 'commission', 'status',
        'specifications', 'technologies', 'deadline'
    ];

    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function freelancer()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
