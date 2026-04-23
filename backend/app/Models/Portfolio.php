<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Portfolio extends Model {
    protected $fillable = ['freelancer_id', 'category_id', 'title', 'description', 'images'];
    protected $casts = ['images' => 'array'];
    protected $appends = ['likes_count', 'comments_count', 'is_liked', 'is_favorited'];

    public function freelancer() { return $this->belongsTo(User::class, 'freelancer_id'); }
    public function category() { return $this->belongsTo(Category::class); }
    public function likes() { return $this->hasMany(PortfolioLike::class); }
    public function comments() { return $this->hasMany(PortfolioComment::class); }
    public function favorites() { return $this->hasMany(PortfolioFavorite::class); }

    public function getLikesCountAttribute() { return $this->likes()->count(); }
    public function getCommentsCountAttribute() { return $this->comments()->count(); }
    public function getIsLikedAttribute() {
        $userId = auth()->id();
        return $userId ? $this->likes()->where('user_id', $userId)->exists() : false;
    }
    public function getIsFavoritedAttribute() {
        $userId = auth()->id();
        return $userId ? $this->favorites()->where('user_id', $userId)->exists() : false;
    }
}
