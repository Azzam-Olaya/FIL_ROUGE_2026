<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PortfolioLike extends Model {
    protected $fillable = ['portfolio_id', 'user_id'];

    public function user() { return $this->belongsTo(User::class); }
    public function portfolio() { return $this->belongsTo(Portfolio::class); }
}
