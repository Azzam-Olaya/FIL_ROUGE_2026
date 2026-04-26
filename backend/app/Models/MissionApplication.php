<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MissionApplication extends Model
{
    protected $fillable = [
        'mission_id',
        'freelancer_id',
        'proposal',
        'price',
        'status' // pending, accepted, rejected, cancelled
    ];

    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }

    public function freelancer()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }
}
