<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BriefLike extends Model {
    protected $table = 'portfolio_likes';
    protected $fillable = ['portfolio_id', 'user_id'];

    public function user() { return $this->belongsTo(User::class); }
    public function brief() { return $this->belongsTo(Brief::class, 'portfolio_id'); }
}
