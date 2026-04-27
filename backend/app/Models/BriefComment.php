<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BriefComment extends Model {
    protected $table = 'portfolio_comments';
    protected $fillable = ['portfolio_id', 'user_id', 'body'];
    public function user() { return $this->belongsTo(User::class); }
    public function brief() { return $this->belongsTo(Brief::class, 'portfolio_id'); }
}
