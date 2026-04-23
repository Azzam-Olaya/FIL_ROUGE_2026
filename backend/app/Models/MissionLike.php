<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MissionLike extends Model
{
    protected $fillable = ['mission_id', 'user_id'];

    public function user()    { return $this->belongsTo(User::class); }
    public function mission() { return $this->belongsTo(Mission::class); }
}
