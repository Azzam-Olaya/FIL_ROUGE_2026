<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BriefComment extends Model {
    protected $table = 'brief_comments';
    protected $fillable = ['brief_id', 'user_id', 'body'];
    public function user() { return $this->belongsTo(User::class); }
    public function brief() { return $this->belongsTo(Brief::class, 'brief_id'); }
}
