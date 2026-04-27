<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BriefFavorite extends Model {
    protected $table = 'portfolio_favorites';
    protected $fillable = ['portfolio_id', 'user_id'];

    public function brief() { return $this->belongsTo(Brief::class, 'portfolio_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
